# CSRF Readiness Matrix

Esta matriz registra la preparación y activación del filtro CSRF global de CodeIgniter 4 en CIAC.

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

## Activación global

El filtro `csrf` está habilitado globalmente en `Config/Filters.php`. No existe ninguna exclusión bajo `admin/*`.

Las únicas exclusiones configuradas son:

- `webhooks/messenger`, porque recibe solicitudes POST externas de Meta y no comparte la sesión administrativa;
- `system/migrate`;
- `system/seed/*`.

Las acciones de sistema mantienen autenticación independiente mediante:

- método POST obligatorio;
- bandera `SYSTEM_ACTIONS_ENABLED`;
- HTTPS obligatorio;
- token secreto enviado en `X-System-Token` y validado con `hash_equals()`;
- allowlist opcional de direcciones IP;
- lista explícita de seeders permitidos;
- bloqueo de concurrencia y registro de intentos no autorizados.

## Riesgo pendiente del webhook

El webhook recibe y registra el encabezado `X-Hub-Signature-256`, pero la auditoría actual no encontró una validación criptográfica de esa firma antes del procesamiento.

La exclusión CSRF es necesaria para permitir llamadas externas de Meta, pero no sustituye la autenticación del origen. El siguiente hardening de seguridad debe validar `X-Hub-Signature-256` con el App Secret de Meta y rechazar la solicitud antes de capturar o procesar el payload cuando la firma sea inválida o falte.

## Pruebas de regresión requeridas

Después del despliegue deben validarse, como mínimo:

1. iniciar sesión con token válido;
2. confirmar rechazo del login POST sin token CSRF;
3. cerrar sesión mediante POST;
4. ejecutar las acciones POST de Authorization, Operations, Cases, Notifications, Integration Replay, Publications y Workflow;
5. confirmar que un POST administrativo sin token devuelve rechazo CSRF;
6. verificar recepción del webhook de Messenger;
7. verificar `system/migrate` con HTTPS, bandera habilitada y encabezado correcto;
8. confirmar rechazo de acciones de sistema sin token, con token incorrecto o desde IP no autorizada;
9. revisar logs por errores `403` inesperados después del despliegue.

## Estado de activación

**CSRF global activado en código.** La fase de Security Hardening queda lista para cierre después de fusionar este cambio y completar la regresión funcional en el entorno de despliegue.
