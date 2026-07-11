# ADR-004 — Documentation as Code

- Estado: Accepted
- Fecha: 2026-07-11

## Contexto
CIAC alcanzó suficiente complejidad para requerir gobernanza técnica, memoria de decisiones y documentación viva.

## Decisión
Mantener documentación versionada dentro de `/docs` y considerarla parte del Definition of Done.

## Consecuencias
- roadmap, integraciones, releases y arquitectura evolucionan con el código;
- los PR deben actualizar documentación afectada;
- disminuye la dependencia de memoria informal;
- se requiere disciplina de revisión para evitar documentación obsoleta.