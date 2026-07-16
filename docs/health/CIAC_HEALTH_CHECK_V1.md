# CIAC Health Check v1

Fecha de revisión: 2026-07-16

## Objetivo

Evaluar el estado de preparación de CIAC antes de declarar la versión 1.0, priorizando seguridad, autorización, estabilidad en producción, rendimiento y consistencia operativa.

## Resumen ejecutivo

El sistema presenta una arquitectura funcional sólida, rutas administrativas agrupadas bajo autenticación, auto-routing deshabilitado y acciones de sistema protegidas mediante POST, HTTPS, encabezado secreto, lista blanca opcional de IP, lista explícita de seeders y bloqueo de ejecución.

Sin embargo, todavía no debe declararse v1.0 hasta cerrar los hallazgos críticos y altos descritos a continuación.

## Hallazgos

### HC-001 — Protección CSRF deshabilitada globalmente

**Severidad:** Crítica

`app/Config/Filters.php` mantiene el filtro `csrf` comentado en `globals.before`, mientras CIAC expone numerosas acciones POST administrativas: asignaciones, cambios de estado, respuestas, administración de usuarios, roles, equipos, workflows, replay de integraciones y resolución de participantes.

**Riesgo:** un usuario autenticado podría ser inducido desde otro sitio a ejecutar una acción no deseada usando su sesión activa.

**Remediación propuesta:**

1. Inventariar todos los formularios POST y solicitudes JavaScript.
2. Incorporar token CSRF en formularios y peticiones asíncronas.
3. Habilitar CSRF globalmente.
4. Excluir únicamente endpoints máquina-a-máquina que ya usan autenticación propia:
   - `webhooks/messenger`;
   - `system/migrate`;
   - `system/seed/*`.
5. Validar todos los flujos administrativos antes de desplegar.

### HC-002 — Cierre de sesión mediante GET

**Severidad:** Alta

La ruta `admin/logout` está definida como GET.

**Riesgo:** cierre involuntario de sesión mediante enlaces, recursos embebidos, prefetch del navegador o navegación automatizada.

**Remediación propuesta:** cambiar la ruta a POST, agregar CSRF y reemplazar el enlace del layout por un formulario compacto.

### HC-003 — Rutas administrativas con autenticación pero sin permiso explícito

**Severidad:** Alta

Varias rutas están dentro del grupo `adminAuth`, pero no declaran un filtro de permiso específico. Entre ellas se encuentran vistas de Operations, Citizens, Conversations, Cases, Notifications, Analytics, Performance, Engagement y Publications, además de algunas acciones POST.

**Riesgo:** la autorización queda distribuida entre controladores, servicios y ownership. Esto aumenta la posibilidad de que una ruta nueva o modificada quede accesible para cualquier usuario autenticado.

**Remediación propuesta:** crear una matriz ruta-permiso, verificar defensa en profundidad y aplicar filtros explícitos donde corresponda, sin sustituir las validaciones de ownership existentes.

### HC-004 — Acción de importación de comentarios sin permiso declarado en la ruta

**Severidad:** Alta

`POST admin/operations/import-facebook-comments` está protegida por autenticación, pero no declara un filtro de permiso específico.

**Riesgo:** cualquier usuario autenticado podría intentar ejecutar una sincronización costosa o sensible, dependiendo de las validaciones internas del controlador.

**Remediación propuesta:** revisar el controlador y asignar un permiso operativo o de integración dedicado.

### HC-005 — Acción de marcar notificación como leída sin ownership visible en la ruta

**Severidad:** Media

`POST admin/notifications/{id}/read` no declara permiso específico.

**Riesgo:** si el controlador no valida pertenencia, un usuario podría modificar una notificación ajena mediante enumeración de IDs.

**Remediación propuesta:** confirmar ownership en el controlador y agregar una prueba de acceso cruzado.

### HC-006 — Resolución de participantes sin permiso explícito

**Severidad:** Alta

`POST admin/publications/{id}/resolve-participants` no declara filtro de permiso.

**Riesgo:** operación potencialmente costosa y vinculada con integración externa disponible para cualquier sesión administrativa, salvo validación interna adicional.

**Remediación propuesta:** asignar permiso específico y conservar validación interna.

### HC-007 — Dos campos históricos de asignación de casos

**Severidad:** Media

El dominio de casos conserva `assigned_to` y `assigned_user_id`. Las pantallas recientes mantienen compatibilidad con ambos.

**Riesgo:** consultas divergentes, KPIs inconsistentes, asignaciones invisibles y mayor complejidad de autorización.

**Remediación propuesta:** documentar el campo canónico, ejecutar auditoría de datos, preparar migración reversible y eliminar gradualmente el campo legado.

### HC-008 — Debug Toolbar y métricas como filtros requeridos

**Severidad:** Media

`toolbar` y `performance` aparecen en filtros requeridos posteriores. Debe verificarse que el entorno de producción los desactive efectivamente y que no agreguen carga ni exposición de información.

**Remediación propuesta:** validar `CI_ENVIRONMENT=production`, revisar configuración de toolbar y medir respuesta con y sin instrumentación.

### HC-009 — Page cache como filtro requerido global

**Severidad:** Media

`pagecache` está configurado como filtro requerido antes y después de todas las solicitudes.

**Riesgo:** en áreas autenticadas podría provocar contenido obsoleto o, si la configuración no discrimina correctamente por sesión, respuestas inapropiadas entre usuarios.

**Remediación propuesta:** verificar comportamiento real del framework y excluir explícitamente `admin/*`, webhooks y acciones del sistema si existe cualquier posibilidad de almacenamiento de respuestas dinámicas.

## Controles positivos confirmados

- Auto-routing deshabilitado.
- Rutas administrativas agrupadas bajo `adminAuth`.
- Acciones sensibles principales usan POST.
- Permisos explícitos presentes en usuarios, roles, equipos, asignaciones, respuestas, replay y workflows.
- Acciones de sistema limitadas a POST.
- Acciones de sistema requieren HTTPS.
- Token de sistema enviado por encabezado y comparado con `hash_equals`.
- Interruptor `SYSTEM_ACTIONS_ENABLED`.
- Lista blanca opcional de IP.
- Lista explícita de seeders autorizados.
- Bloqueo de ejecución para migraciones y seeders.
- Logging de intentos no autorizados.

## Plan de remediación recomendado

### PR 75 — CSRF Foundation

- inventario de formularios;
- helper/meta global para token;
- soporte JavaScript;
- tokens en formularios prioritarios;
- pruebas manuales sin habilitar todavía el filtro global.

### PR 76 — Enable Global CSRF

- activar CSRF;
- excluir webhook y system actions;
- corregir formularios restantes;
- convertir logout a POST.

### PR 77 — Route Permission Hardening

- matriz ruta-permiso;
- permisos explícitos para importación, publicaciones, analytics y demás rutas;
- pruebas de acceso por rol y ownership.

### PR 78 — Production Hardening

- revisar page cache, toolbar y performance metrics;
- revisar encabezados seguros;
- checklist `.env` de producción;
- estrategia de logs y backups.

### PR 79 — Case Assignment Normalization

- elegir campo canónico;
- auditar datos;
- migración reversible;
- actualización de consultas y servicios.

### PR 80 — CIAC v1.0 Release Candidate

- smoke test integral;
- pruebas responsive;
- revisión de permisos;
- validación HostGator;
- CHANGELOG y release notes.

## Criterio de salida para v1.0

CIAC podrá considerarse listo para v1.0 cuando:

- CSRF esté habilitado y probado;
- logout utilice POST;
- las acciones administrativas relevantes tengan permisos explícitos y ownership validado;
- no exista riesgo de cachear contenido administrativo entre sesiones;
- producción tenga toolbar desactivada y configuración revisada;
- el modelo de asignación de casos tenga una estrategia documentada;
- el smoke test completo sea satisfactorio.
