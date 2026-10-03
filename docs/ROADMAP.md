# Hoja de ruta

## V0 — completada (2023)

- Catálogos de usuarios, roles, productores, estanques y variables
- Registro de mediciones por estanque
- Inicio de sesión con Laravel Sanctum

## V1 — actual

- Permisos por rol (Administrador, Técnico, Productor) en rutas y en datos
- El autor de cada medición se toma del token
- Perfil (`/me`), cambio de contraseña y cierre de sesión
- Captura en lote transaccional (`/register/lote`)
- Historial con filtros y paginación, y estadísticas por variable
- Respuestas y errores siempre en JSON con el mismo formato
- Correcciones de migraciones, seeders y validaciones
- Pruebas de integración y documentación

## Pendiente

- Rangos óptimos por variable en la base de datos (unidad, mínimo y máximo), para que la app no los tenga fijos en el código
- Alertas cuando una medición sale de rango (por ejemplo, oxígeno bajo de madrugada)
- Mensajes de validación en español (`lang/es`)
- Mensajes de `404` uniformes en todos los controladores
- Proteger los roles del sistema contra renombrado o borrado, e impedir quitar el último Administrador
- Índice único en `role_user (role_id, user_id)`
- Columna `valor` con más precisión si se agregan variables con más de dos decimales
- Zona horaria de México para los cortes por día
- Exportar el historial a CSV o Excel
- Recuperación de contraseña por correo
- Caducidad configurable de los tokens
- Integración continua con GitHub Actions (pruebas en cada pull request)
