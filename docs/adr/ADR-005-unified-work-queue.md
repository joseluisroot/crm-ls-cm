# ADR-005 — Unified Work Queue

- Estado: Proposed
- Fecha: 2026-07-11

## Contexto
Las interacciones que requieren atención pueden originarse en Facebook, Messenger, WhatsApp, Instagram, correo, formularios o alertas internas. Crear una cola por canal aumentaría el acoplamiento y duplicaría la operación.

## Decisión propuesta
Introducir Work Items como unidad operativa omnicanal. Cada Work Item conservará canal, tipo de origen e identificador de origen, pero su ciclo de atención será común.

## Consecuencias esperadas
- una sola cola para operadores;
- integración uniforme con asignaciones, SLA, casos y workflows;
- incorporación de nuevos canales sin rediseñar Citizen Operations;
- necesidad de definir claramente idempotencia, estados, prioridad y reglas de conversión desde cada origen.