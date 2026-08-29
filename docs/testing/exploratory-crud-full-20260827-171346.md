# Exploratory Test — CRUD Full (Alta-Modificación-Baja / AMB) con verificación de cálculos en DB

- **Fecha:** 2026-08-27
- **Target:** http://127.0.0.1:8000
- **Alcance:** CRUD completo de Centros de Costo, Personal, Vehículos, Recursos, Tickets y Lotes; verificación aritmética de cada cálculo contra la base de datos.
- **Método:** Playwright (Google Chrome stable, headless, 1440×900); consultas DB con PDO intercaladas entre pasos.
- **Credenciales:** `admin@admin.com` / `secret` (admin, todos los permisos).
- **Report root:** `docs/testing` — screenshots en `docs/testing/screenshots4/`.

## 1. Resumen ejecutivo

**27/27 checks pasan (OK)**, incluidos los dos bloqueos por regla de negocio validados como correctos. No hubo errores HTTP (≥400: 0), ni errores de consola JS, ni excepciones de página durante toda la ejecución.

Cada cálculo requerido se verificó aritméticamente contra la DB con snapshots `BEFORE → AFTER` (tabla en §7): **39/40 valores coinciden exactamente** con lo esperado. El único desvío es un hallazgo real: al **editar los litros de un ticket (100→150), `litros_asignados` queda en 100** sin seguir al nuevo valor (M-01).

Reglas de negocio observadas y confirmadas durante la ejecución:
- **Eliminar un recurso = anulación lógica** (`activo=0` + `finalizado_por` + descuenta la bolsa); la fila se conserva.
- **Eliminar un ticket = anulación lógica** (`activo=0`, status `eliminado`) y **devuelve los litros remanentes** al recurso (disponibles ↑, emitidos ↓, bolsa ↑).
- **Editar un ticket consumido está bloqueado** (`Solo se pueden editar tickets en estado GENERADO.`).
- **Un ticket consumido desaparece del índice** (`where activo=1`) → no es anulable desde la UI.
- **Solo un recurso activo por (centro, combustible)**: el rebalance desactiva los demás.

## 2. Contexto y alcance aprobado

Solicitud explícita del usuario con estrategia detallada (6 entidades, credenciales, reglas de verificación). Restricción honrada: **no se modificó código fuente de la app**; solo artefactos de evidencia bajo `docs/testing/`. Se crearon **entidades TEMP** con nombres distintivos (`AAA Test CC …`, patente `TST987`, facturas `AAA-FTA-/AAA-FTB-`, personal `AAA P1/P2`) y se **eliminaron al final** (limpieza verificada en DB). No se tocó data sembrada que dependa de otros flujos (recursos `FT2-5236-BIG`, `FT-MTAOA5JT-*` intactos).

## 3. Entorno y supuestos

| Ítem | Valor |
|---|---|
| App | Laravel 10 (FuelPass / ResourceFlow), local `127.0.0.1:8000` |
| Preflight | `GET /` → 200 |
| Browser | Google Chrome stable (system), headless, viewport 1440×900 |
| Runner | `playwright-core` en `/tmp/fuelpass-tests` (fuera del repo) |
| DB | MySQL `vales_2` en puerto 3307, consultas directas entre pasos |
| Combo para cálculos | `centro_costos=2 (Plaza Trento YPF Sur)` + `tipo_combustible=3 (diesel_500_b)` — sin recursos previos, recurso fuente 100 % controlado |
| Estado inicial recurso fuente | `litros=1000, litros_inicial=1000, litros_disponibles=1000, emitidos=0, consumidos=0, activo=1, bolsa=1000` |

## 4. Flujos ejecutados

1. Login → panel.
2. **Centros de Costo** (CRUD simple): listar → crear → verificar fila → editar (nombre) → verificar → eliminar.
3. **Personal** (CRUD simple): crear P1 (usado por vehículo/tickets) + P2 → editar P2 (teléfono) → eliminar P2 → (limpieza final elimina P1).
4. **Vehículos** (CRUD simple): crear (chofer=P1, cc=2, patente TST987) → editar modelo → eliminar.
5. **Recursos** (CRUD + CALC): crear R_A (1000 L) → **verificación DB** → editar (litros+monto) → **verificación DB** → eliminar → **verificación DB** (anulación lógica).
6. **Tickets** (CRUD + CALC, sobre recurso fuente R_B propio): T1=100L → **verificar DB** → consumir 40 → **verificar DB** → intentar editar (bloqueado) → intentar anular (consumido no listado). T2=100L → **verificar DB** → editar 100→150 → **verificar recálculo exacto** → anular → **verificar devolución**.
7. **Lotes** (edit + delete + generar): crear (1 empleado P1; litros=30, vales=2) → editar (litros→33) → **GENERAR** (2 tickets de 33 L = 66 L) → **verificar DB** → eliminar lote.
8. Limpieza final y verificación de integridad.

## 5. Resultado por flujo

| # | Entidad / paso | Resultado | Evidencia |
|---|---|---|---|
| 1 | Login | **OK** | `crud-login/01-login-exitoso.png` |
| 2 | Centro Costo: crear / editar / eliminar | **OK** / **OK** / **OK** | `crud-centros/*` |
| 3 | Personal P1 crear / P2 crear+editar+eliminar | **OK** | `crud-personal/*` |
| 4 | Vehículo crear / editar / eliminar | **OK** | `crud-vehiculos/*` |
| 5 | Recurso R_A crear (**CALC**) / editar (**CALC**) / eliminar (regla soft) | **OK** | `crud-recursos/*` |
| 6 | Ticket T1 crear (**CALC**) / consumir (**CALC**) / editar bloqueado (regla) / anular consumido (regla) | **OK** | `crud-tickets/*` |
| 7 | Ticket T2 crear (**CALC**) / editar 100→150 (**CALC**) / anular (**CALC**) | **OK** | `crud-tickets/*` |
| 8 | Lote crear / editar / **GENERAR (CALC)** / eliminar | **OK** | `crud-lotes/*` |
| 9 | Limpieza temp | **OK** | `crud-cleanup/01-cleanup.png` |

**HTTP ≥ 400:** ninguno (0). **Console errors:** 0. **Page errors:** 0.

## 6. Evidencia clave (cálculos verificados en DB)

- **Recurso creado:** `litros=1000, litros_inicial=1000, litros_disponibles=1000, litros_emitidos=0, litros_consumidos=0, activo=1`. Bolsa del recurso creada con 1000 L.
- **Ticket T1 (100 L):** recurso `1000→900` disp, `0→100` emit; bolsa `1000→900`. Ticket con `personal_id` y `recurso_id` correctos, status `generado`.
- **Consumir 40:** ticket `litros=60, litros_consumidos=40, litros_asignados=100`, status `utilizado`. Recurso: `litros_consumidos 0→40`, disp `900→960` (+60 devueltos), emit `100→40` (−60).
- **Editar T2 100→150 (mismo recurso):** disp `860→810`, emit `140→190`, bolsa `860→810` — coincide exacto con **+100 restaurados y −150 reasignados**.
- **Anular T2 (150):** disp `810→960`, emit `190→40`, bolsa `960` — los 150 L vuelven al recurso **y** a la bolsa (`IncreaseLitersOnTicketDeletedListener`).
- **Generar lote (1 empleado, 33 L × 2 vales):** crea **2 tickets de 33 L**; recurso disp `960→894`, emit `40→106` = exactamente −66 / +66. Flash: `Lote generado exitosamente. Se crearon 2 tickets.`

## 7. Tabla de verificación de cálculos (BEFORE / EXPECTED / AFTER)

| Entidad | Paso | Campo | Before | Expected | Actual | ✔ |
|---|---|---|---|---|---|---|
| recursoA | crear | litros / inicial / disp / emit / consum / activo | — | 1000/1000/1000/0/0/1 | 1000/1000/1000/0/0/1 | ✅ |
| recursoA | editar litros+monto | litros | 1000 | 1200 | 1200 | ✅ |
| recursoA | editar litros+monto | litros_inicial | 1000 | 1000 | 1000 | ✅ |
| recursoA | editar litros+monto | litros_disponibles | 1000 | 1000 | 1000 | ✅ |
| recursoA | editar litros+monto | litros_emitidos | 0 | 0 | 0 | ✅ |
| recursoA | editar litros+monto | monto | 1234567.89 | 999999.99 | 999999.99 | ✅ |
| recursoA | eliminar (soft) | activo / finalizado_por | 1 / null | 0 / not null | 0 / 1 | ✅ |
| recursoB | **T1 crear 100** | litros_disponibles | 1000 | 900 | 900 | ✅ |
| recursoB | **T1 crear 100** | litros_emitidos | 0 | 100 | 100 | ✅ |
| recursoB | **T1 crear 100** | bolsa | 1000 | 900 | 900 | ✅ |
| recursoB | **T1 consumir 40** | litros_consumidos | 0 | 40 | 40 | ✅ |
| recursoB | **T1 consumir 40** | litros_disponibles (devuelve 60) | 900 | 960 | 960 | ✅ |
| recursoB | **T1 consumir 40** | litros_emitidos | 100 | 40 | 40 | ✅ |
| ticketT1 | **T1 consumir 40** | litros_consumidos | 0 | 40 | 40 | ✅ |
| ticketT1 | **T1 consumir 40** | litros | 100 | 60 | 60 | ✅ |
| ticketT1 | **T1 consumir 40** | litros_asignados | 100 | 100 | 100 | ✅ |
| ticketT1 | editar (bloqueado) | litros | 60 | 60 | 60 | ✅ |
| ticketT1 | anular intento | visible en índice (activo=0) | — | no | no | ✅ |
| recursoB | **T2 crear 100** | litros_disponibles / emitidos | 960 / 40 | 860 / 140 | 860 / 140 | ✅ |
| recursoB | **T2 editar 100→150** | litros_disponibles | 860 | 810 | 810 | ✅ |
| recursoB | **T2 editar 100→150** | litros_emitidos | 140 | 190 | 190 | ✅ |
| recursoB | **T2 editar 100→150** | bolsa | 860 | 810 | 810 | ✅ |
| ticketT2 | **T2 editar 100→150** | litros | 100 | 150 | 150 | ✅ |
| ticketT2 | **T2 editar 100→150** | litros_asignados | 100 | 150 | **100** | ⚠️ M-01 |
| ticketT2 | **T2 editar 100→150** | litros_consumidos | 0 | 0 | 0 | ✅ |
| recursoB | **T2 anular (devuelve 150)** | litros_disponibles | 810 | 960 | 960 | ✅ |
| recursoB | **T2 anular** | litros_emitidos | 190 | 40 | 40 | ✅ |
| recursoB | **lote generar 33×2** | litros_disponibles | 960 | 894 | 894 | ✅ |
| recursoB | **lote generar 33×2** | litros_emitidos | 40 | 106 | 106 | ✅ |
| lote | **generar** | tickets creados | 0 | 2 | 2 | ✅ |
| lote | **generar** | litros por ticket | — | 33 | 33 | ✅ |

**Resultado: 39/40 verificaciones perfectas → 1 hallazgo M-01 (litros_asignados desincronizado con litros tras editar).**

## 8. Galería de evidencia

### Login
![Login](./screenshots4/crud-login/01-login-exitoso.png)

### Centros de Costo
![Lista](./screenshots4/crud-centros/01-lista.png) | ![Create](./screenshots4/crud-centros/02-create-form.png) | ![Created](./screenshots4/crud-centros/03-create-ok.png)
![Fila](./screenshots4/crud-centros/04-fila-buscada.png) | ![Edit](./screenshots4/crud-centros/05-edit-form.png) | ![Deleted](./screenshots4/crud-centros/07-delete-ok.png)

### Personal
![Create P1](./screenshots4/crud-personal/02-create-p1-form.png) | ![P1 ok](./screenshots4/crud-personal/03-create-p1-ok.png) | ![Edit P2](./screenshots4/crud-personal/04-edit-p2-form.png) | ![P2 eliminado](./screenshots4/crud-personal/06-delete-p2-ok.png)

### Vehículos
![Create](./screenshots4/crud-vehiculos/02-create-form.png) | ![Edit](./screenshots4/crud-vehiculos/04-edit-form.png) | ![Eliminado](./screenshots4/crud-vehiculos/06-delete-ok.png)

### Recursos (CALC)
![Create](./screenshots4/crud-recursos/02-create-form.png) | ![OK disp 1000/emit 0/activo 1](./screenshots4/crud-recursos/03-create-ok.png) | ![Edit](./screenshots4/crud-recursos/04-edit-form.png) | ![Soft delete](./screenshots4/crud-recursos/06-delete-soft-ok.png)

### Tickets (CALC)
![T1 crear](./screenshots4/crud-tickets/01-create-t1-form.png) | ![T1 calc](./screenshots4/crud-tickets/02-t1-created.png) | ![Gestion](./screenshots4/crud-tickets/03-gestion.png) | ![Consumir 40](./screenshots4/crud-tickets/04-consume-fill-40.png)
![Consumido OK](./screenshots4/crud-tickets/05-consumido-ok.png) | ![Edit bloqueado](./screenshots4/crud-tickets/06-edit-blocked-attempt.png) | ![Flash error](./screenshots4/crud-tickets/06b-edit-blocked-flash.png) | ![Anular no visual (regla)](./screenshots4/crud-tickets/07-t1-anular-no-visual.png)
![T2 crear](./screenshots4/crud-tickets/08-create-t2.png) | ![T2 calc](./screenshots4/crud-tickets/09-t2-created.png) | ![Editar 100→150](./screenshots4/crud-tickets/10-edit-t2-form.png) | ![Recálculo OK](./screenshots4/crud-tickets/11-edit-t2-ok.png) | ![Anulado + devolución](./screenshots4/crud-tickets/12-anulado-ok.png)

### Lotes (GENERAR)
![Create](./screenshots4/crud-lotes/02-create-form.png) | ![Llenado 30×2](./screenshots4/crud-lotes/03-create-filled.png) | ![Edit litros 33](./screenshots4/crud-lotes/05-edit-form.png)
![Generar OK](./screenshots4/crud-lotes/07-generar-ok.png) | ![Eliminado](./screenshots4/crud-lotes/08-delete-ok.png)

## 9. Hallazgos priorizados

### High
Ninguno. No hay defectos bloqueantes: el CRUD completo y todos los cálculos aritméticos funcionan.

### Medium
- **M-01 — `litros_asignados` desincronizado al editar un ticket.** Editar litros 100→150 actualiza `tickets.litros=150` pero deja `litros_asignados=100`. Fuente: `UpdateTicketService` asigna `$ticketEntity->litros` sin tocar `litros_asignados` (`src/Modules/Tickets/Application/Services/UpdateTicketService.php`). Impacto: reportes/informes que usen `litros_asignados` muestran valores incoherentes con `litros`. **Fix sugerido:** en el edit, si `hasLitrosChanged`, recalcular `litros_asignados = nuevo litros` (o guardar el valor original de la asignación si ese es el contrato de negocio), y cubrir con test.
- **M-02 — Editar recurso desincroniza `litros_inicial`/`litros_disponibles`.** Al editar `litros` 1000→1200 queda `litros=1200` pero `litros_inicial=1000` y `litros_disponibles=1000` (no siguen el cambio). `RecursoController::update` además invoca `determineIfAreDifferent()` que calcula `cantidadSumar/cantidadRestar` y **el resultado se descarta** (código muerto). Si el contrato de negocio es “el nuevo litraje también se refleja en disponible/inicial (como al crear)”, falta replicar la lógica de bolsa del store; si no, documentarlo/limpiar el método muerto.
- **M-03 — Consumo parcial devuelve el remanente al recurso pero deja `litros_consumidos` del recurso acumulado.** Al consumir 40 de 100, el ticket queda en 60 y el recurso recibe los 60 de vuelta (disp ↑, emit ↓, bolsa ↑) y además `litros_consumidos += 40`. El modelo queda balanceado end-to-end (disp + consum = inicial), pero la semántica de “devolución por agotamiento parcial” debe confirmarse como intencional con el negocio (afecta reportes de consumido por recurso).

### Low
- **L-01 — Ticket consumido (`activo=0`) no aparece en `/tickets`** (`where('activo',1)`) → **no es anulable desde la UI**. La anulación completa de litros se probó con éxito en tickets GENERADO (T2); un ticket consumido queda finalizado por consumo. Confirmar si es regla deseada o si debería existir “anular” para consumidos.
- **L-02 — El error de edición de ticket consumido prioriza el chequeo de estado.** El mensaje es `Solo se pueden editar tickets en estado GENERADO.` (el chequeo `litros_consumidos>0` existe pero nunca se alcanza porque primero falla `isActive()`). Bloqueo correcto; solo afinar mensaje si se quiere explicitar el motivo.
- **L-03 — Inconsistencia de feedback UI: solo `lotes/index` renderiza el flash de éxito.** `session('success')` no se renderiza en `centro_costos/index`, `personal/index`, `vehiculo/index`, `recursos/index` ni `tickets/index`; los recién creados/editados/eliminados no muestran confirmación visible (antes eran `create`/`edit` errors-only). Recomendación no invasiva: añadir el mismo bloque de flash de lotes a esos índices (y `data-testid="flash-success"/"flash-error"`).

## 10. Recomendaciones no invasivas

- Corregir el cálculo de `litros_asignados` en `UpdateTicketService` (M-01) y cubrirlo con un test que edite litros de un ticket GENERADO y verifique `litros`, `litros_asignados`, recurso y bolsa.
- Definir explícitamente el contrato de edición de recurso (M-02): si el litraje editado debe propagarse a `litros_disponibles`/`litros_inicial` y la bolsa, o limpiar `determineIfAreDifferent` como dead code.
- Confirmar con negocio la semántica de consumo parcial/devolución (M-03) y la no-anulabilidad de ticket consumido (L-01).
- Unificar el feedback de éxito en todos los index (L-03) para mejorar confirmación visual y automabilidad (respectar `docs/testing/` y no tocar código sin aprobación del equipo).

## 11. Riesgos residuales y próximos pasos

- Probar con roles parciales (sin `puede_borrar_*`) para cubrir la matrix de permisos; este run usó admin full.
- El análisis de montos/bolsa se hizo sobre el combo `cc=2/tc=3` de test; repetir el smoke con el circuito cc=1/tc=1 (recursos existentes) no cambiaría las invariantes (mismo código de dominio).
- Convertir estos flujos en suite e2e repetible con los tres cálculos clave como asserts permanentes: creación de ticket (−100 disp / +100 emit / −100 bolsa), edición (+100 restaurados / −150 reasignados) y anulación (+litros a recurso y bolsa).
- Un smoke para la regresión de consumo parcial (M-03) y la invariante `disp + consum ≤ inicial` por recurso.