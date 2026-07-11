# ADR-002 — Workflow Runtime Inspector

- Estado: Accepted
- Fecha: 2026-07-11

## Contexto
El Workflow Engine requería observabilidad sobre ejecuciones, nodos, errores, variables, payloads y tiempos.

## Decisión
Construir un Runtime Inspector que consuma eventos del workflow y persista trazabilidad complementaria sin duplicar `workflow_executions`.

## Consecuencias
- depuración y auditoría centralizadas;
- soporte futuro para replay, resume e historial;
- el motor conserva su responsabilidad principal;
- la instrumentación debe evitar alterar el resultado funcional del workflow.