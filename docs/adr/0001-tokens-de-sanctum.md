# ADR 0001: Tokens personales de Sanctum

## Estado

Aceptada.

## Contexto

La API la consume una app móvil (Ionic + Capacitor), no un sitio web del mismo dominio. Varias personas, con roles distintos, usan la app desde su propio teléfono. Laravel ofrece Sanctum (tokens personales o cookies de sesión) y Passport (OAuth2).

## Decisión

Usar tokens personales de Laravel Sanctum. `POST /login` emite un token, la app lo envía en `Authorization: Bearer` y `POST /logout` lo revoca. No se usan las cookies de sesión de Sanctum (`EnsureFrontendRequestsAreStateful` sigue desactivado).

## Consecuencias

- Cada inicio de sesión crea un registro en `personal_access_tokens`; un usuario puede tener sesión en varios teléfonos a la vez.
- Se puede cerrar una sesión concreta (logout) o todas las demás (al cambiar la contraseña).
- No hay refresco de token: por defecto no caduca. Si se configura una caducidad, la app debe volver a iniciar sesión al recibir `401` (ya lo hace).
- Passport sería excesivo: no hay aplicaciones de terceros que necesiten OAuth.
