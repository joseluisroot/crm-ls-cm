# ADR-005 — Unified Work Queue

- Estado: Accepted
- Fecha: 2026-07-11

## Contexto
Las interacciones que requieren atención pueden originarse en Facebook, Messenger, WhatsApp, Instagram, correo, formularios o alertas internas. Crear una cola por canal aumentaría el acoplamiento y duplicaría la operación.

## Decisión
Introducir Work Items como unidad operativa omnicanal. Cada Work Item conservará canal, tipo de origen e identificador de origen, pero su ciclo de atención será común.

La creación será idempotente mediante la combinación única `origin_type + origin_id` y toda operación relevante publicará eventos `operations.work_item.*`.

## Consecuencias
- una sola cola para operadores;
- integración uniforme con asignaciones, SLA, casos y workflows;
- incorporación de nuevos canales sin rediseñar Citizen Operations;
- necesidad de mantener explícitas las reglas de transición, prioridad y conversión desde cada origen;
- separación estricta entre captura del canal y operación del dominio.
