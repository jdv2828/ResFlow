# Normalización de vales_2 como base operativa final

## Objetivo

Explicar cómo se transformó la base `vales` (copia de producción) en `vales_2` para que funcione con la arquitectura nueva de la rama `refactor/change-core`.

`vales_2` no es una copia exacta de `vales`. Es una base adaptada para que el código nuevo pueda operar correctamente hacia adelante, preservando al máximo la información histórica.

---

## Criterio General

```
No inventar relaciones históricas que no existían.
No fabricar litros de combustible.
Preservar datos legacy originales.
Adaptar solo lo necesario para operación futura.
```

---

## Tickets Históricos

Los tickets que ya estaban cerrados en producción:

```
utilizados  (ticket_status_id = 4)
vencidos    (ticket_status_id = 3)
eliminados  (ticket_status_id = 5)
```

**No recibieron expediente_id.**

Motivo: la base de producción `vales` no guardaba la relación `tickets.expediente_id`. No podemos saber con certeza de qué expediente histórico salió cada ticket.

Estos tickets se conservan completos con todos sus datos originales (litros, empleado, combustible, estación, fechas, hash). Se pueden consultar, auditar y mostrar en timeline. Pero no están vinculados a expedientes nuevos.

---

## Tickets Pendientes (Generados)

Los tickets en estado `generado` (ticket_status_id = 1, activo = 1) representan vales emitidos pero aún no consumidos.

Para que el sistema nuevo pueda operarlos correctamente (consumo, vencimiento, devolución de litros), se les asignó un `expediente_id`.

### Regla De Asignación

1. Se agruparon por estación de servicio + tipo de combustible.
2. Se buscó el expediente activo disponible para esa combinación.
3. Si el expediente tenía saldo suficiente (`litros_disponibles >= ticket.litros`), se asignó el ticket y se descontaron los litros del expediente y su bolsa.
4. Si el expediente no tenía saldo suficiente, el ticket quedó **sin asignar** y se reportó como inconsistencia.

### ¿Por Qué Puede Quedar Sin Asignar?

En producción, los tickets se emitían contra una bolsa global (`bolsa_combustibles`). El saldo actual de esa bolsa puede ser menor que el total de tickets pendientes.

En ese caso:

```
No fabricamos litros.
No permitimos saldos negativos.
Reportamos el ticket como inconsistencia.
```

---

## Expedientes

En producción, podía haber múltiples expedientes activos al mismo tiempo para la misma estación y combustible.

En la nueva arquitectura, se espera:

```
1 expediente activo por estación + combustible.
```

### Normalización Aplicada

1. Se sincronizaron `bolsas.cantidad_disponible` con `expedientes.litros_disponibles`.
2. Se rebalancearon los expedientes:

```
El primer expediente con litros_disponibles > 0 (según orden) queda activo.
Todos los demás del mismo grupo quedan inactivos.
```

Si ningún expediente tiene saldo, todos quedan inactivos para ese grupo.

---

## Combustibles Por Estación

En producción, existía la columna `tipo_combustibles.estacion_servicio_id`. En la rama actual se había perdido.

Se restauró esa relación para que el sistema pueda validar qué combustible pertenece a qué estación usando la base de datos real, no un mapa hardcodeado.

El archivo `CheckCombustibleEstacion.php` se actualizó:

```
Antes: validación por mapa hardcodeado con nombres textuales.
Ahora: validación por JOIN en DB usando tipo_combustibles.estacion_servicio_id.
```

Esto evita errores por espacios, mayúsculas o nombres mal escritos.

---

## Datos Legacy Preservados

Se conservan intactos dentro de `vales_2`:

| Tabla Legacy | Contenido |
|---|---|
| `legacy_user_activities` | Historial de actividades de usuario del sistema viejo (76.734 registros) |
| `legacy_bolsa_combustibles` | Saldo global de combustible del sistema viejo (1 registro con saldos por tipo) |
| `movement_histories` | Historial de movimientos del sistema viejo (162.765 registros) |

Además, estos datos se proyectaron hacia `activity_logs` para que la nueva auditoría pueda mostrarlos.

---

## Resumen De Decisiones

| Aspecto | Decisión |
|---|---|
| Tickets cerrados sin expediente | Se preservan sin expediente |
| Tickets pendientes con saldo | Se asignan a expediente activo |
| Tickets pendientes sin saldo | Se reportan, no se fuerzan |
| Expedientes múltiples activos | Se normaliza a 1 por grupo |
| Bolsas nuevas | Se sincronizan con expedientes |
| Combustible-estación | Se restaura relación DB |
| Historial legacy | Se preserva completo |
| Litros inventados | No se agregan |
| Saldos negativos | No se permiten |

---

## Validaciones Esperadas

Después de la normalización, se espera:

```text
tickets_pendientes_sin_expediente = 0 (si alcanzó saldo)
expedientes_con_multiples_activos  = 0
bolsas_negativas                   = 0
expedientes_saldo_negativo          = 0
expedientes_sin_bolsa               = 0
bolsas_sin_expediente               = 0
```

Si no alcanzó saldo para todos los tickets pendientes, aparecerán en el reporte con la advertencia correspondiente.
