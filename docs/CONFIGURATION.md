# Configuración

Toda la configuración vive en `.env` (a partir de `.env.example`) y en los archivos de `config/`. Después de cambiar el `.env` en producción ejecuta `php artisan config:cache`.

## Aplicación

| Variable | Valor recomendado | Descripción |
|---|---|---|
| `APP_NAME` | `"Variables Acuicolas"` | Nombre que usa Laravel en logs y correos |
| `APP_ENV` | `production` en el servidor | `local` en desarrollo |
| `APP_KEY` | generado con `php artisan key:generate` | Llave de cifrado de Laravel. Sin ella la API no arranca |
| `APP_DEBUG` | `false` en el servidor | Con `true`, los errores 500 incluyen el mensaje interno. Nunca en producción |
| `APP_URL` | `https://tu-dominio.mx` | URL pública del backend |
| `LOG_LEVEL` | `warning` en el servidor | `debug` en desarrollo |

## Base de datos

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `mysql` (también funciona `sqlite`) |
| `DB_HOST`, `DB_PORT` | Servidor MySQL o MariaDB |
| `DB_DATABASE` | Nombre de la base de datos (por defecto `apivariables`) |
| `DB_USERNAME`, `DB_PASSWORD` | Credenciales. En producción usa un usuario con permisos solo sobre esta base |

Para probar sin MySQL:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/ruta/absoluta/database.sqlite
```

```bash
touch /ruta/absoluta/database.sqlite
php artisan migrate --seed
```

## Zona horaria

`config/app.php` usa `'timezone' => 'UTC'`. Las fechas se guardan en UTC y la API las devuelve en formato ISO 8601 con `Z` (por ejemplo `2026-10-03T14:38:00.000000Z`); la app las convierte a la hora local del teléfono.

Los filtros `desde` y `hasta` de `/register` y `/estadisticas` comparan contra la fecha UTC. Si necesitas que el corte del día sea a medianoche de México, cambia la zona horaria antes de capturar datos reales; cambiarla después desplaza las fechas ya guardadas.

## Tokens de sesión

`config/sanctum.php`:

| Clave | Valor | Descripción |
|---|---|---|
| `expiration` | `null` | Los tokens no caducan; se revocan con `POST /logout` o al cambiar la contraseña (se cierran las demás sesiones) |

Para que caduquen, pon minutos (por ejemplo `60 * 24 * 30` para 30 días). La app regresa sola al inicio de sesión cuando recibe un 401.

## Límites de peticiones

| Dónde | Límite | Archivo |
|---|---|---|
| Todas las rutas de la API | 60 por minuto por usuario (o por IP sin sesión) | `app/Providers/RouteServiceProvider.php` |
| `POST /login` | 10 por minuto | `routes/api.php` (`throttle:10,1`) |

Al superarse, la API responde `429` con el mensaje «Demasiados intentos, espera un momento».

## CORS

`config/cors.php` permite cualquier origen (`'allowed_origins' => ['*']`) en las rutas `api/*`. Es necesario para probar la app en el navegador (`http://localhost:4200`) y no afecta a la app instalada en el teléfono. Si la API solo la usará la app nativa, puedes restringirlo a:

```php
'allowed_origins' => ['http://localhost', 'https://localhost', 'capacitor://localhost'],
```

## Roles

Los nombres de los roles están fijos en el código y deben existir en la tabla `roles` exactamente así:

| Nombre | Usado en |
|---|---|
| `Administrador` | `routes/api.php` (`role:Administrador`) y `User::isAdmin()` |
| `Técnico` | `User::puedeVerTodo()` |
| `Productor` | `UserController::getProducer()` |

Renombrar un rol desde `PUT /role/{id}` rompe los permisos. Ver [ADR 0002](adr/0002-roles-y-middleware.md).
