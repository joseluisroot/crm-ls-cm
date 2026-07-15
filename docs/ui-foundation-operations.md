# UI Foundation: Citizen Operations

## Alcance

- paginación desde servidor;
- búsqueda por ciudadano, contenido, título y responsable;
- filtros por estado y prioridad;
- selector de 10, 25, 50 o 100 registros;
- preservación de filtros en query string;
- componente compartido de paginación;
- SweetAlert global para éxito, error, confirmaciones y estados de carga.

## Validación

1. Abrir `/admin/operations` con más de 25 registros.
2. Cambiar de página y confirmar que se preserven cola, búsqueda y filtros.
3. Buscar por nombre de ciudadano o texto del comentario.
4. Cambiar el tamaño de página.
5. Ejecutar sincronización y confirmar SweetAlert + indicador de carga.
6. Confirmar que el alcance propio, de equipo o global siga aplicándose.

## Extensión

La misma arquitectura será reutilizada en publicaciones y engagement.
