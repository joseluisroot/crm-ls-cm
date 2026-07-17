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

## Hallazgos corregidos

- Las acciones sensibles de usuario y rol ahora usan el sistema central de confirmación SweetAlert.
- Los botones sensibles declaran explícitamente `type="submit"`.
- Las confirmaciones nativas mediante `onclick="confirm(...)"` fueron eliminadas en las vistas auditadas.

## Pendientes antes de activar CSRF global

- Cases.
- Notifications.
- Publications.
- Integration Replay.
- Workflow Builder y Workflow Simulator.
- Peticiones AJAX o `fetch()` que modifiquen estado.
- Endpoints de sistema y webhooks, que deberán evaluarse para exclusión explícita cuando corresponda.

## Regla de activación

El filtro CSRF global solo debe activarse cuando todas las operaciones internas que modifican estado estén marcadas como preparadas y los endpoints externos legítimos tengan una exclusión documentada.