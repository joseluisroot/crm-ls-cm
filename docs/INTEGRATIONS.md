# Integraciones

## Meta

### Estado actual
- Messenger webhook: activo.
- Page Webhooks `feed`: activo y validado.
- Comentarios y reacciones: recibidos en producción.
- Respuesta a comentarios desde CIAC: pendiente.

### Controles
- nunca documentar tokens, secretos o credenciales reales;
- usar variables de entorno;
- validar permisos vigentes antes de implementar escritura;
- probar primero con una página y usuarios administrados;
- registrar payloads y errores sin exponer secretos.

### Próxima configuración
Antes de responder, ocultar o moderar comentarios desde CIAC se verificará el acceso vigente requerido por Meta y se realizará una prueba controlada.

## OpenAI
Pendiente para v1.3. La IA asistirá clasificación y respuestas; no realizará perfilamiento político ni tomará decisiones sensibles de forma autónoma.

## WhatsApp, Instagram, Google y otros
Permanecen en roadmap o Parking Lot. Cada integración deberá adaptarse a Interaction/Operations y no crear una cola operativa paralela.