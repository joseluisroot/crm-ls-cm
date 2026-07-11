# ADR-001 — Core Event Engine

- Estado: Accepted
- Fecha: 2026-07-11

## Contexto
Los módulos necesitaban compartir cambios sin crear dependencias directas entre Workflow, Runtime, Analytics y futuras capacidades.

## Decisión
Adoptar un Event Engine central con eventos persistidos, registry, dispatcher y subscribers.

## Consecuencias
- desacoplamiento entre productores y consumidores;
- trazabilidad mediante `system_events`;
- base para Runtime, Analytics e inteligencia;
- necesidad de contratos y nombres de eventos estables.