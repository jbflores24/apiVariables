# Seguridad

## Modelo de amenazas

Variables Acuícolas guarda datos de productores (nombre, RFC, dirección y teléfonos) y sus mediciones. Los riesgos que se cubren son:

- acceso a datos personales o de producción de otro productor;
- registro de mediciones falsas a nombre de otra persona;
- uso de las funciones de administración por quien no es administrador;
- adivinar contraseñas por fuerza bruta;
- filtración de detalles internos en los mensajes de error.

## Autenticación

- El inicio de sesión (`POST /login`) valida correo y contraseña con `Auth::attempt()` y emite un **token personal de Sanctum**. Ver [ADR 0001](adr/0001-tokens-de-sanctum.md).
- En la base de datos se guarda solo el hash SHA-256 del token, nunca el token en claro.
- Las contraseñas se guardan con bcrypt (cast `hashed` del modelo `User`). La API nunca devuelve la contraseña ni su hash: `password` está en `$hidden` y `/getProducer` usa Eloquent en lugar de `SELECT *`.
- `POST /logout` revoca el token de la sesión actual. Cambiar la contraseña (`PUT /me/password`) exige la contraseña actual y revoca los demás tokens del usuario.
- Los tokens no caducan solos (`sanctum.expiration = null`). Si el sistema se usará en teléfonos compartidos, configura una caducidad (ver [Configuración](CONFIGURATION.md#tokens-de-sesión)).

## Autorización

| Control | Dónde |
|---|---|
| Rutas de administración solo para el rol `Administrador` | `routes/api.php` + `EnsureUserHasRole` (`403`) |
| Un Productor solo ve sus productores, estanques y mediciones | `scopeVisiblePara()` en `Producer`, `Estanque` y `Register` (`404` para lo ajeno) |
| Un Productor solo captura en sus propios estanques | `RegisterController::reglaEstanque()` (`422`) |
| Solo el autor o un Administrador editan o eliminan una medición | `RegisterController::puedeModificar()` (`403`) |
| El autor de una medición es siempre el usuario del token | `RegisterController::store()` y `lote()`; el `user_id` del cuerpo se ignora. Ver [ADR 0006](adr/0006-autor-desde-el-token.md) |
| Altas y ediciones solo aceptan los campos esperados | `$request->only()` en usuarios y asignaciones de rol |

Lo ajeno responde `404` y no `403` para no confirmar que el recurso existe.

### Limitaciones conocidas

- Los nombres de los roles están fijos en el código. Renombrar `Administrador` desde `PUT /role/{id}` deja a todos sin acceso a la administración; eliminarlo, también. No lo hagas desde la app.
- Un Administrador puede quitarse a sí mismo el rol de Administrador. Asegúrate de que siempre quede al menos uno.
- Un Técnico ve los datos de todos los productores (es su función); asigna ese rol solo a personal de confianza.

## Límites de peticiones

- `POST /login`: 10 intentos por minuto por IP (`throttle:10,1`).
- Resto de la API: 60 peticiones por minuto por usuario.

Ambos responden `429`. Ver [Configuración](CONFIGURATION.md#límites-de-peticiones).

## Mensajes de error

- `Handler::respuestaApi()` responde los errores de `/api/*` con el formato común. Con `APP_DEBUG=false`, un error inesperado devuelve solo «Error del servidor»; con `APP_DEBUG=true`, el mensaje interno (puede incluir SQL). **En producción `APP_DEBUG` debe ser `false`.**
- Algunos controladores todavía devuelven el mensaje interno de Laravel en los `404` (por ejemplo «No query results for model [App\Models\Producer] 9»). No expone datos, pero sí nombres de clases.

## Datos personales

- RFC, dirección y teléfonos solo los ve el propio productor, los técnicos y los administradores.
- Eliminar un usuario borra en cascada su productor, sus estanques y sus mediciones. No hay papelera: respalda antes (ver [Base de datos](DATABASE.md#respaldo)).

## Contraseñas de ejemplo

Los seeders crean cuatro usuarios con la contraseña `Demo12345`. **Antes de usar el sistema con datos reales**, cambia esas contraseñas desde *Mi perfil* en la app o elimina esos usuarios y crea los reales.

## Transporte

- En producción publica la API **solo por HTTPS**. El token viaja en cada petición; por HTTP podría interceptarse en una red Wi-Fi compartida.
- Android bloquea por defecto las peticiones `http://` desde la app; para desarrollo en la red local ver [Despliegue](DEPLOYMENT.md#probar-desde-el-teléfono-en-la-red-local).
