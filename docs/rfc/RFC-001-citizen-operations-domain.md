# RFC-001 — Citizen Operations Domain

- Estado: Accepted
- Fecha: 2026-07-11
- Release objetivo: v1.1

## Problema
Las interacciones que requieren atención llegan desde canales distintos. Si cada canal administra su propia cola, estados y asignaciones, CIAC duplicará lógica y aumentará el acoplamiento.

## Objetivo
Introducir un dominio operacional agnóstico al canal cuya unidad de trabajo sea el **Work Item**.

## Decisión
Toda interacción que requiera atención podrá convertirse en un Work Item. El Work Item conservará el tipo de origen, el identificador externo y el canal, pero tendrá un ciclo de atención común.

## Modelo inicial

```text
Channel Event
  → Interaction / Engagement
  → Work Item
  → Assignment / Workflow / Case
  → Event Engine
  → Runtime / Timeline / Analytics
```

## Reglas
- la creación será idempotente mediante `origin_type + origin_id`;
- los catálogos de estado, prioridad, canal y origen serán extensibles;
- los controladores no crearán Work Items directamente;
- las operaciones relevantes publicarán eventos `operations.work_item.*`;
- el payload original permanecerá en su módulo de captura y el Work Item guardará solo metadata operacional necesaria.

## Casos de uso iniciales
- crear Work Item desde un comentario de Facebook;
- asignar un operador;
- vincular un caso existente o nuevo;
- consultar una cola unificada;
- alimentar timeline y analytics mediante eventos.

## Fuera de alcance
- interfaz visual de Operations Queue;
- respuesta directa a comentarios;
- clasificación automática por IA;
- resolución de identidad Citizen 360;
- enriquecimiento de publicaciones y actores de Meta.

## Riesgos
- duplicar Work Items por reintentos del webhook;
- acoplar Operations a Facebook;
- utilizar metadata como almacenamiento sin estructura;
- crear ciclos de vida paralelos a Cases o Workflow.

## Mitigaciones
- índice único por origen;
- contratos y servicios agnósticos al canal;
- catálogos y reglas de transición explícitas;
- reutilización de Case Lifecycle, Assignment y Workflow existentes.
