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
| Publications | Resolver participantes | Pendiente de interfaz | Ruta POST registrada | Revisar |

## Hallazgos corregidos

- Las acciones sensibles de usuario y rol usan el sistema central de confirmación SweetAlert.
- Los botones sensibles auditados declaran explícitamente `type="submit"`.
- La creación de casos usa `site_url()` en lugar de una ruta absoluta escrita manualmente.
- La creación de casos informa el estado de carga durante el envío.
- Publicar una versión de workflow usa confirmación central y estado de carga.
- Eliminar nodos y transiciones usa confirmación central y elimina dependencias de `onclick="confirm(...)"`.
- Crear y clonar versiones muestra un estado de carga durante el procesamiento.
- El inicio del Workflow Simulator muestra un estado de carga.
- Builder y Simulator incluyen token CSRF en sus acciones POST auditadas.

## Pendientes antes de activar CSRF global

- Sustituir las confirmaciones nativas restantes en Cases e Integration Replay por el sistema central SweetAlert.
- Confirmar dónde se invoca la ruta `publications/(:num)/resolve-participants`; actualmente no aparece en las vistas principales de Publications.
- Revisar la acción de archivado de workflows y confirmar su interfaz.
- Revisar peticiones AJAX o `fetch()` que modifiquen estado.
- Evaluar endpoints de sistema y webhooks para exclusión explícita.

## Exclusiones esperadas

Los webhooks externos legítimos no pueden enviar el token CSRF de la sesión administrativa. Al activar el filtro global deberán documentarse y limitarse exclusiones como:

- `webhooks/messenger` para recepción de eventos firmados por Meta;
- endpoints de sistema únicamente cuando cuenten con autenticación independiente suficientemente fuerte.

## Regla de activación

El filtro CSRF global solo debe activarse cuando todas las operaciones internas que modifican estado estén marcadas como preparadas y los endpoints externos legítimos tengan una exclusión mínima, explícita y documentada.