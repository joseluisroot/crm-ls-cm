# CSRF Readiness Matrix

Esta matriz registra la preparación de CIAC antes de activar el filtro CSRF global de CodeIgniter 4.

## Estado

| Módulo | Acción POST | Token CSRF | Ruta alineada | Estado |
|---|---|---:|---:|---|
| Auth | Login | Sí | Sí | Preparado |
| Auth | Logout | Sí | Sí | Preparado |
| Authorization | Crear usuario | Sí | Sí | Preparado |
| Authorization | Editar usuario | Sí | Sí | Preparado |
| Authorization | Cambiar estado de usuario | Sí | Sí | Preparado |
| Authorization | Restablecer contraseña | Sí | Sí | Preparado |
| Authorization | Sincronizar roles de usuario | Sí | Sí | Preparado |
| Authorization | Cambiar estado de rol | Sí | Sí | Preparado |
| Authorization | Actualizar permisos de rol | Sí | Sí | Preparado |
| Authorization | Crear equipo | Sí | Sí | Preparado |
| Authorization | Actualizar equipo | Sí | Sí | Preparado |
| Operations | Importar comentarios de Facebook | Sí | Sí | Preparado |
| Operations | Asignar responsable | Sí | Sí | Preparado |
| Operations | Cambiar estado | Sí | Sí | Preparado |
| Operations | Guardar borrador de respuesta | Sí | Sí | Preparado |
| Operations | Enviar respuesta | Sí | Sí | Preparado |
| Cases | Crear caso | Sí | Sí | Preparado |
| Cases | Cambiar estado | Sí | Sí | Preparado |
| Cases | Asignar responsable | Sí | Sí | Preparado |
| Cases | Retirar asignación | Sí | Sí | Preparado |
| Notifications | Marcar notificación como leída | Sí | Sí | Preparado |
| Integration Replay | Reproducir evento | Sí | Sí | Preparado |
| Publications | Resolver participantes | Sí | Sí | Preparado |
| Workflow | Crear workflow | Sí | Sí | Preparado |
| Workflow | Crear versión vacía | Sí | Sí | Preparado |
| Workflow | Clonar versión | Sí | Sí | Preparado |
| Workflow | Publicar versión | Sí | Sí | Preparado |
| Workflow Builder | Crear y editar nodo | Sí | Sí | Preparado |
| Workflow Builder | Eliminar nodo | Sí | Sí | Preparado |
| Workflow Builder | Crear y editar transición | Sí | Sí | Preparado |
| Workflow Builder | Eliminar transición | Sí | Sí | Preparado |
| Workflow Simulator | Iniciar simulación | Sí | Sí | Preparado |
| Workflow Simulator | Interactuar | Sí | Sí | Preparado |
| Workflow Simulator | Reiniciar simulación | Sí | Sí | Preparado |

## Hallazgos corregidos

- Las acciones sensibles de usuario y rol usan el sistema central de confirmación SweetAlert.
- Los botones sensibles auditados declaran explícitamente `type="submit"`.
- Las rutas internas modificadas usan `site_url()` en lugar de rutas absolutas escritas manualmente.
- Cases informa estados de carga al cambiar estado, asignar y retirar asignación.
- Retirar la asignación de un caso ya no depende de `onclick="confirm(...)"`.
- Reproducir un evento usa confirmación central y estado de carga.
- Resolver participantes de una publicación usa confirmación central, token CSRF y estado de carga.
- Publicar una versión de workflow usa confirmación central y estado de carga.
- Eliminar nodos y transiciones usa confirmación central.
- Crear y clonar versiones muestra un estado de carga durante el procesamiento.
- Workflow Builder y Workflow Simulator incluyen token CSRF en sus acciones POST auditadas.

## Acciones sin interfaz activa

La ruta POST `admin/workflows/(:num)/archive` está registrada, pero actualmente no existe un formulario visible que la invoque en las vistas principales de Workflow. Antes de exponer esa acción deberá agregarse una interfaz POST con `csrf_field()`, confirmación central y permiso `workflow.manage`.

## Revisión de JavaScript

Las acciones internas auditadas se ejecutan mediante formularios POST tradicionales. No se identificó una dependencia necesaria de `fetch()`, `$.ajax()`, `$.post()` o `axios.post()` para las operaciones incluidas en esta matriz.

Cualquier nueva petición JavaScript que modifique estado deberá:

- enviar el nombre y valor vigentes del token CSRF;
- manejar respuestas `403` sin reintentos automáticos inseguros;
- actualizar el token cuando la configuración de regeneración lo requiera;
- conservar controles de autorización del servidor.

## Exclusiones propuestas para activación global

Los endpoints externos legítimos no pueden enviar el token CSRF de la sesión administrativa. La activación global deberá usar exclusiones mínimas y explícitas:

- `webhooks/messenger`, porque recibe eventos externos de Meta y debe validarse mediante firma y token de verificación;
- `system/migrate` y `system/seed/*` solamente si se confirma que conservan autenticación independiente fuerte, HTTPS obligatorio y restricción operativa. En caso contrario, no deben excluirse y deberán migrarse a comandos CLI.

No deben excluirse rutas bajo `admin/*`.

## Estado de activación

La interfaz administrativa está preparada para habilitar el filtro CSRF global. El siguiente PR debe limitarse a:

1. activar `csrf` en `Config/Filters.php`;
2. configurar las exclusiones externas aprobadas;
3. ejecutar pruebas de regresión sobre login, logout y todas las acciones POST de la matriz;
4. comprobar que los webhooks y tareas de despliegue siguen operando únicamente bajo sus mecanismos de autenticación independientes.
