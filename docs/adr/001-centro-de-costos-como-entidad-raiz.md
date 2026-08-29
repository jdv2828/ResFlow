# ADR-001: Centro de Costo como entidad raíz del dominio

**Estado:** Propuesto (pendiente de validación con stakeholder)  
**Fecha:** 2026-07-26  
**Decisor:** Desarrollador + Pendiente de validación con cliente

## Contexto

ResourceFlow gestiona vales de recursos para una organización. El sistema tiene una entidad "Centro de Costo" que representa las áreas/departamentos (por ejemplo: Obras, Logística, Administración, etc.).

`centro_costo_id` aparece en cuatro entidades:

| Entidad | Usa centro_costo_id para... |
|---------|---------------------------|
| **Personal** | Saber en qué área trabaja el empleado |
| **Vehículos** | Saber a qué área pertenece el vehículo |
| **Recursos** | Saber para qué área se compró el combustible |
| **Tickets** | Registrar de qué área se descontó el vale |

### El problema

El ticket obtiene su `centro_costo_id` del **vehículo** (no del personal). Esto significa que un empleado de "Obras Públicas" puede generar un vale que se descuenta del combustible de "Salud" si el vehículo que maneja está asignado a Salud.

**Escenario concreto:**
```
- Empleado: Juan (área: Obras Públicas)
- Vehículo: Camioneta XYZ (área: Salud)
- Resultado: El vale de Juan se descuenta de Salud
```

## Decisión

**Opción A (actual):** El centro de costo del ticket viene del vehículo. Es intencional porque los vehículos se mueven entre áreas.

**Opción B (alternativa):** El centro de costo del ticket viene del personal. Más coherente pero menos flexible.

**Opción C (alternativa):** Validar coherencia: el vehículo y el personal deben pertenecer al mismo centro de costo para emitir un ticket.

## Consecuencias de cada opción

| Opción | Beneficio | Riesgo |
|--------|-----------|--------|
| **A (vehículo)** | Permite reasignar vehículos entre áreas sin romper nada | Un empleado puede consumir combustible de otra área |
| **B (personal)** | Más intuitivo: cada uno consume de su área | Un vehículo reasignado hereda el área del conductor, no la suya |
| **C (validación)** | Previene inconsistencias | Complejiza el formulario y la lógica de emisión |

## Pendiente

Consultar con el stakeholder:
1. ¿Los vehículos se mueven entre áreas en la práctica?
2. Si un empleado de Obras Públicas usa un vehículo de Salud, ¿el vale debe descontarse de Salud o de Obras Públicas?
3. ¿Hay casos donde un empleado genere vales para múltiples áreas?

## Recomendación

La opción A está bien si los vehículos efectivamente se mueven entre áreas. La opción C es la más segura si no se mueven. La decisión debe venir del negocio, no del código.
