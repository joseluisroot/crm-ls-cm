# ADR-003 — Public Engagement Engine

- Estado: Accepted
- Fecha: 2026-07-11

## Contexto
CIAC debía ampliar la atención desde Messenger hacia comentarios y reacciones públicas de Facebook.

## Decisión
Extender el webhook existente para procesar `entry.changes`, persistir publicaciones, comentarios y reacciones, y publicar eventos internos de engagement.

## Consecuencias
- reutilización del endpoint y del Core Event Engine;
- procesamiento idempotente;
- atención pública y medición de participación;
- la actividad no se interpreta automáticamente como afinidad política.