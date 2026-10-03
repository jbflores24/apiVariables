# ADR 0004: Formato de respuesta único, también en errores

## Estado

Aceptada.

## Contexto

Los controladores respondían con `ApiResponse` (`{ message, statusCode, error, data }`), pero los errores que lanzaba Laravel por su cuenta tenían otra forma. Además, si el cliente no enviaba `Accept: application/json`, un token inválido intentaba redirigir a una página de login inexistente y terminaba en `500`.

## Decisión

- El middleware `ForceJsonResponse`, al inicio del grupo `api`, fija `Accept: application/json` en cada petición.
- `App\Exceptions\Handler` convierte las excepciones de `/api/*` al formato de `ApiResponse`: validación `422` con los errores por campo en `data`, autenticación `401`, autorización `403`, modelo o ruta inexistentes `404`, método `405`, límites `429` y cualquier otra `500`.

## Consecuencias

- La app maneja todos los errores igual: muestra `message` y, en un `422`, `data.<campo>[0]` junto al campo.
- Con `APP_DEBUG=false` un `500` no expone detalles internos.
- Los controladores existentes que atrapan sus propias excepciones siguen funcionando; algunos aún devuelven el mensaje interno de Laravel en los `404` (pendiente en la hoja de ruta).
