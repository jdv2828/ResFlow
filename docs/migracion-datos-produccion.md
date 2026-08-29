# Migración de datos desde base producción hacia nueva estructura

## Objetivo

Respaldar los datos existentes de seguridad (usuarios, roles, permisos) y maestros (empleados, vehículos, áreas, cargos) desde la base `vales` y restaurarlos en la nueva base que se genere con las migraciones de la rama `refactor/change-core`.

---

## Tablas a respaldar (orden de exportación)

| # | Tabla | Registros (vales) | Depende de |
|---|-------|------------------|------------|
| 1 | `roles` | 7 | — |
| 2 | `permissions` | 36 | — |
| 3 | `users` | 30 | — |
| 4 | `estacion_servicios` | 6 | — |
| 5 | `tipo_combustibles` | 21 | estacion_servicios |
| 6 | `ticket_statuses` | 5 | — |
| 7 | `cargos` | 1 | — |
| 8 | `areas` | 193 | areas (auto-ref) |
| 9 | `empleados` | 848 | areas, cargos |
| 10 | `vehiculos` | 1378 | — |
| 11 | `role_has_permissions` | 79 | roles, permissions |
| 12 | `model_has_roles` | 33 | roles, users |
| 13 | `model_has_permissions` | 0 | permissions, users |
| 14 | `user_estacion_servicio` | 10 | users, estacion_servicios |
| 15 | `empleado_vehiculo` | 1408 | empleados, vehiculos |

**Total aproximado: ~4000 registros**

---

## Tablas que NO se respaldan para restauración

| Tabla | Motivo |
|-------|--------|
| `expedientes` | Se cargarán manualmente post-migración |
| `bolsa_combustibles` / `bolsas` | Se cargarán manualmente post-migración |
| `tickets` | Se cargarán manualmente post-migración |
| `movement_histories` | Migró a `activity_logs`, no mezclar |
| `user_activities` | Histórico de auditoría viejo; respaldar aparte si se requiere |
| `failed_jobs` | Propia de Laravel, no relevante |
| `migrations` | Se regenera al correr `php artisan migrate` |
| `password_reset_tokens` | Se regeneran al solicitar reseteo |
| `personal_access_tokens` | Se regeneran al loguearse |

---

## Paso a paso con DBeaver

### 1. Exportar CSV desde base `vales`

1. Conectarse a `vales` en DBeaver.
2. Click derecho sobre la tabla → **Export Data**.
3. Formato: **CSV**.
4. Opciones:
   - [x] Include column headers
   - [x] Encoding: **UTF-8**
   - Delimiter: **,** (coma) o **;** (punto y coma — usar siempre el mismo)
   - [x] Export all columns (incluyendo `id`)
   - [ ] No transformar fechas (formato nativo)
   - [ ] No omitir valores nulos (exportar vacío)
5. Destino: carpeta del proyecto, ej: `database/backups/csv/`.
6. Repetir para cada tabla en el **orden de exportación** de arriba.

**Alternativa rápida**: seleccionar todas las tablas, exportar todas juntas usando CSV y mapear una carpeta por tabla. DBeaver permite hacer exportación múltiple.

### 2. Preparar base destino

```bash
# En la rama refactor/change-core
php artisan migrate:fresh
```

> **Importante**: `migrate:fresh` borra todas las tablas y vuelve a crearlas. Asegurarse de estar en la base correcta y tener respaldo antes.

### 3. Desactivar foráneas durante importación

Antes de importar, ejecutar en DBeaver sobre la base destino:

```sql
SET FOREIGN_KEY_CHECKS = 0;
```

Esto evita errores por orden de importación (ej: `areas` que se referencia a sí misma).

### 4. Importar CSV en orden

Usar **Import Data** de DBeaver para cada CSV.

1. Click derecho sobre la base destino → **Import Data** → **CSV**.
2. Archivo fuente → seleccionar el CSV correspondiente.
3. Destino: tabla existente (la migración ya la creó).
4. Mapeo de columnas: verificar que cada columna del CSV coincida con una columna de la tabla.
   - **Crítico**: incluir `id` como columna destino. No dejar que la base asigne IDs nuevos.
5. Modo: **Insert** (no **Insert/Update** a menos que sepas que hay duplicados).
6. Repetir en el **orden de importación** de abajo.

### 5. Orden de importación

```
1. roles
2. permissions
3. users
4. estacion_servicios
5. tipo_combustibles
6. ticket_statuses
7. cargos
8. areas             ← ojo: áreas se referencia a sí misma, por eso FOREIGN_KEY_CHECKS=0
9. empleados
10. vehiculos
11. role_has_permissions
12. model_has_roles
13. model_has_permissions
14. user_estacion_servicio
15. empleado_vehiculo
```

### 6. Reactivar foráneas

```sql
SET FOREIGN_KEY_CHECKS = 1;
```

### 7. Verificar

```sql
-- Conteos vs CSV original
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM roles;
-- ... etc
```

---

## Precauciones con seeders

Al ejecutar migraciones en destino **NO** correr seeders que escriban roles, permisos o usuarios maestros sin antes haber importado los CSV. Especialmente:

- `RolesSeeder` → crearía roles duplicados con `firstOrCreate` (por nombre). Si ya se importaron los roles reales, este seeder no los duplicaría porque usa `firstOrCreate`, pero conviene revisar que los nombres coincidan.
- `PermissionsSeeder` → usaría `puede_ver_roles_y_permisos`, que ahora coincide con producción.
- `AdminUserSeeder` → usa `updateOrCreate` por email. Si `admin@admin.com` no existe en los datos importados, lo crearía. Si ya existe, lo actualizaría y le asignaría todos los permisos al rol admin (sobreescribiendo los permisos actuales del admin). Es preferible **no ejecutar este seeder** si se importaron los usuarios reales, y si se necesita el admin, crearlo manualmente o ejecutarlo antes de importar usuarios.

**Recomendación**: al hacer `migrate:fresh --seed`, correr seeders exceptuando `AdminUserSeeder`:

```bash
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=PermissionsSeeder
# ... otros seeders que no toquen usuarios/roles/permisos
# NO correr AdminUserSeeder
```

Luego importar los CSV. Si después se quiere agregar el admin seed, ejecutar `AdminUserSeeder` manualmente.

---

## Nota sobre datos inconsistentes detectados en `vales`

Hay una fila en `model_has_roles` con:

```
model_type = 'App\Models \User'   ← Tiene un espacio antes de \User
```

Esto probablemente ya no funciona. Antes de exportar, conviene corregirla:

```sql
UPDATE model_has_roles
SET model_type = 'App\Models\User'
WHERE model_type = 'App\Models \User';
```

Son 32 asignaciones correctas y 1 incorrecta.

---

## Resumen rápido para terminal (Linux/macOS)

Si se prefiere usar la terminal en vez de DBeaver para exportar:

```bash
# Exportar todos los CSVs
TABLAS="roles permissions users estacion_servicios tipo_combustibles ticket_statuses cargos areas empleados vehiculos role_has_permissions model_has_roles model_has_permissions user_estacion_servicio empleado_vehiculo"

for t in $TABLAS; do
  mysql -h 127.0.0.1 -P 3307 -u root -proot vales \
    -e "SELECT * FROM $t" \
    > "database/backups/csv/${t}.csv"
done
```

Para importar desde terminal:

```bash
for t in roles permissions users estacion_servicios tipo_combustibles ticket_statuses cargos areas empleados vehiculos role_has_permissions model_has_roles model_has_permissions user_estacion_servicio empleado_vehiculo; do
  mysql -h [host] -u [user] -p[pass] [db_destino] \
    -e "LOAD DATA LOCAL INFILE 'database/backups/csv/${t}.csv' INTO TABLE $t FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;"
done
```

Ajustar credenciales, host y opciones de formato según el CSV generado.
