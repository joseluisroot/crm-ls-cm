# CIAC Testing

## Ejecutar la suite

```bash
composer install
composer test
```

La configuración principal está en `phpunit.dist.xml` y usa el bootstrap oficial de pruebas de CodeIgniter 4.

## Estructura

```text
tests/
├── _support/    Helpers y clases base compartidas
├── unit/        Servicios y reglas sin infraestructura externa
├── feature/     Rutas, filtros, controladores y respuestas HTTP
└── integration/ Flujos que requieren base de datos o varios módulos
```

## Clase base

`Tests\Support\CiacTestCase` proporciona:

- `actingAsAdmin()` para preparar la sesión mínima esperada por `AdminAuthFilter`;
- `csrfPayload()` para obtener el nombre y valor vigentes del token CSRF;
- limpieza de sesión al terminar cada prueba.

La autorización por permiso continúa dependiendo de los datos configurados para la prueba. `actingAsAdmin()` no concede permisos de base de datos ni sustituye las comprobaciones de `PermissionFilter`.

## Convenciones

- Cada corrección de seguridad debe incluir una prueba que falle antes del cambio.
- Las pruebas unitarias no deben depender de red, servicios de Meta ni datos de producción.
- Las pruebas de integración deben usar una base de datos exclusiva para testing.
- No se deben desactivar CSRF, autenticación o permisos para simplificar una prueba funcional.
- Los payloads externos deben construirse con fixtures sanitizados.
- Las pruebas deben ser deterministas y poder ejecutarse repetidamente.

## Cobertura

La ejecución normal no exige un driver de cobertura. Cuando se necesite medir cobertura, debe habilitarse Xdebug o PCOV y ejecutarse PHPUnit con los parámetros de reporte correspondientes.

## CI

GitHub Actions ejecuta `composer validate`, instala dependencias y corre `composer test` en cada Pull Request y en los pushes a `main`. El resultado de PHPUnit se conserva durante siete días como artefacto de diagnóstico, incluso cuando la ejecución falla.

## Próximas suites prioritarias

1. autenticación, logout y rechazo CSRF;
2. permisos administrativos;
3. validación de firma del webhook de Meta;
4. captura y Replay de eventos;
5. identidad ciudadana;
6. ciclo de Operations y Cases;
7. validación y ejecución de Workflow.
