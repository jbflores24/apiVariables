# Referencia de la API

Todas las rutas usan el prefijo `/api` y responden JSON, aunque el cliente no envíe `Accept: application/json` (el middleware `ForceJsonResponse` lo fuerza).

## Autenticación

Todas las rutas, excepto `POST /login`, requieren el encabezado:

```
Authorization: Bearer <token>
```

El token se obtiene en `POST /login` y se revoca con `POST /logout`. No caduca solo (ver [Configuración](CONFIGURATION.md#tokens-de-sesión)).

## Permisos

Cada ruta indica quién puede usarla:

| Marca | Significado |
|---|---|
| **Pública** | Sin token |
| **Sesión** | Cualquier usuario con sesión, de cualquier rol |
| **Admin** | Solo usuarios con el rol `Administrador`; los demás reciben `403` |

Además, en las rutas de consulta con marca **Sesión**, un usuario que solo es **Productor** ve únicamente su propia información:

| Recurso | Lo que ve un Productor | Lo que ven Técnico y Administrador |
|---|---|---|
| Productores | El suyo | Todos |
| Estanques | Los de su unidad de producción | Todos |
| Mediciones | Las de sus estanques y las que él capturó | Todas |

Un recurso que existe pero no es visible responde `404`, igual que uno que no existe.

## Formato de respuesta

Todas las respuestas, correctas o de error, tienen la misma forma:

```json
{ "message": "Texto", "statusCode": 200, "error": false, "data": { } }
```

| Campo | Descripción |
|---|---|
| `message` | Texto en español para mostrar a la persona |
| `statusCode` | El mismo código HTTP de la respuesta |
| `error` | `false` si la operación salió bien, `true` si no |
| `data` | El resultado; en un error de validación, los errores por campo |

### Errores

| Código | `message` | Cuándo |
|---|---|---|
| `401` | No autenticado | Sin token, token inválido o revocado |
| `401` | Credenciales inválidas | Correo o contraseña incorrectos en `/login` |
| `403` | No autorizado | El rol no tiene permiso, o la medición es de otra persona |
| `404` | Varía según la ruta («No Encontrado», «Ruta no encontrada» o el mensaje interno de Laravel) | El recurso o la ruta no existe, o el recurso no es visible para el usuario |
| `405` | Método no permitido | Método HTTP equivocado para la ruta |
| `422` | Primer error de validación | Datos inválidos; `data` trae todos los errores por campo |
| `429` | Demasiados intentos, espera un momento | Se superó un límite de peticiones |
| `500` | Error del servidor | Error inesperado (con `APP_DEBUG=true` se muestra el detalle) |

Error de validación:

```json
{
  "message": "The email field is required. (and 1 more error)",
  "statusCode": 422,
  "error": true,
  "data": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

Los mensajes de validación salen en inglés (idioma por defecto de Laravel). Una app puede mostrar `data.<campo>[0]` junto al campo correspondiente.

### Fechas

Las fechas se devuelven en UTC con formato ISO 8601: `2026-10-03T14:38:00.000000Z`. Los filtros de fecha se envían como `Y-m-d` (`2026-10-03`).

### Cuerpo de las peticiones

Se recomienda JSON (`Content-Type: application/json`). También se acepta `application/x-www-form-urlencoded`.

---

## Resumen de rutas

| Método | Ruta | Permiso | Descripción |
|---|---|---|---|
| POST | `/login` | Pública | Inicia sesión; devuelve token y perfil |
| GET | `/me` | Sesión | Perfil del usuario autenticado |
| PUT | `/me/password` | Sesión | Cambia la contraseña propia |
| POST | `/logout` | Sesión | Revoca el token actual |
| GET | `/producer` | Sesión | Productores (con usuario y estanques) |
| GET | `/producer/{id}` | Sesión | Un productor |
| GET | `/getProducerUserId/{user_id}` | Sesión | Productor asociado a un usuario |
| GET | `/estanque` | Sesión | Estanques |
| GET | `/estanque/{id}` | Sesión | Un estanque con su productor y usuario |
| GET | `/variable` | Sesión | Variables que se miden |
| GET | `/variable/{id}` | Sesión | Una variable |
| GET | `/register` | Sesión | Mediciones, filtradas y paginadas |
| GET | `/register/{id}` | Sesión | Una medición |
| POST | `/register` | Sesión | Registra una medición |
| POST | `/register/lote` | Sesión | Registra varias mediciones de un estanque (todo o nada) |
| PUT | `/register/{id}` | Sesión | Edita una medición (autor o Administrador) |
| DELETE | `/register/{id}` | Sesión | Elimina una medición (autor o Administrador) |
| GET | `/estadisticas` | Sesión | Resumen por variable |
| GET | `/user` | Admin | Usuarios con roles y datos de productor |
| GET | `/user/{id}` | Admin | Un usuario |
| POST | `/user` | Admin | Crea un usuario |
| PUT | `/user/{id}` | Admin | Edita un usuario |
| DELETE | `/user/{id}` | Admin | Elimina un usuario y, en cascada, todo lo suyo |
| GET | `/role` | Admin | Roles |
| GET | `/role/{id}` | Admin | Un rol |
| POST | `/role` | Admin | Crea un rol |
| PUT | `/role/{id}` | Admin | Renombra un rol |
| DELETE | `/role/{id}` | Admin | Elimina un rol |
| GET | `/roleuser` | Admin | Todas las asignaciones rol-usuario |
| GET | `/roleuser/{user_id}` | Admin | Asignaciones de un usuario |
| POST | `/roleuser` | Admin | Asigna un rol a un usuario |
| PUT | `/roleuser/{id}` | Admin | Cambia una asignación |
| DELETE | `/roleuser/{id}` | Admin | Quita una asignación |
| GET | `/getProducer` | Admin | Usuarios con rol Productor |
| POST | `/producer` | Admin | Registra la dirección de un productor |
| PUT | `/producer/{id}` | Admin | Edita la dirección |
| DELETE | `/producer/{id}` | Admin | Elimina el productor, sus estanques y mediciones |
| POST | `/estanque` | Admin | Crea un estanque |
| PUT | `/estanque/{id}` | Admin | Edita un estanque |
| DELETE | `/estanque/{id}` | Admin | Elimina un estanque y sus mediciones |
| POST | `/variable` | Admin | Crea una variable |
| PUT | `/variable/{id}` | Admin | Renombra una variable |
| DELETE | `/variable/{id}` | Admin | Elimina una variable y sus mediciones |

`PATCH` funciona igual que `PUT` en todas las rutas de edición.

---

## Sesión

### POST /login

**Pública.** Máximo 10 intentos por minuto.

| Campo | Reglas |
|---|---|
| `email` | Obligatorio, correo válido |
| `password` | Obligatorio |

```json
{ "email": "miguel@example.com", "password": "Demo12345" }
```

Respuesta `200`:

```json
{
  "message": "Token creado",
  "statusCode": 200,
  "error": false,
  "data": {
    "token": "5|Qx8rVd...",
    "user": {
      "id": 3,
      "rfc": "BBBB770810411",
      "name": "Miguel Sánchez Ortiz",
      "email": "miguel@example.com",
      "roles": ["Productor"],
      "producer": {
        "id": 3, "calle": "Alvaro Obregón", "numero": 15, "colonia": "Refineria",
        "cp": "70650", "municipio": "Tehuantepec", "agencia": "", "estado": "Oaxaca",
        "telPrincipal": "9717143333", "telSecundario": "9717155555", "user_id": 3
      }
    }
  }
}
```

`producer` es `null` si el usuario aún no tiene dirección registrada. Credenciales incorrectas: `401` «Credenciales inválidas».

### GET /me

**Sesión.** Devuelve el mismo objeto `user` que `/login`, leído de nuevo de la base de datos. La app lo llama al abrir para saber si cambiaron los roles.

### PUT /me/password

**Sesión.**

| Campo | Reglas |
|---|---|
| `password_actual` | Obligatorio; debe coincidir con la contraseña actual |
| `password` | Obligatorio, mínimo 8 caracteres |
| `password_confirmation` | Igual a `password` |

Si `password_actual` no coincide responde `422` con el error en `data.password_actual`. Si todo sale bien, **revoca los demás tokens del usuario** (cierra la sesión en otros teléfonos) y conserva el actual.

### POST /logout

**Sesión.** Revoca el token con el que se hizo la petición. Responde `200` «Sesión cerrada».

---

## Consulta

### GET /producer

**Sesión.** Lista de productores visibles. Cada elemento:

```json
{
  "id": 1, "user_id": 1,
  "usuario": { "id": 1, "rfc": "DDDD770810411", "name": "Ana Torres Ramírez", "email": "admin@example.com" },
  "calle": "Donaji", "numero": 13, "colonia": "Istmeña", "cp": "70680",
  "municipio": "Salina Cruz", "agencia": "", "estado": "Oaxaca",
  "telPrincipal": "9711587416", "telSecundario": "9717163242",
  "estanques": [ { "id": 1, "nombre": "Estanque A", "descripcion": "Descripción A", "producer_id": 1 } ]
}
```

### GET /producer/{id}

**Sesión.** El mismo objeto. `404` si no existe o no es visible.

### GET /getProducerUserId/{user_id}

**Sesión.** Lista (de cero o un elemento) con el productor del usuario indicado, en el mismo formato. La app la usa para saber si un usuario ya tiene dirección y para mostrar sus estanques. Un Productor que consulta a otro usuario recibe una lista vacía.

### GET /estanque

**Sesión.** Lista de estanques visibles: `{ id, nombre, descripcion, producer_id }`.

### GET /estanque/{id}

**Sesión.**

```json
{
  "id": 1, "nombre": "Estanque A", "descripcion": "Descripción A", "producer_id": 1,
  "productor": { "id": 1, "calle": "Donaji", "...": "..." },
  "usuario": { "id": 1, "name": "Ana Torres Ramírez", "...": "..." }
}
```

### GET /variable y GET /variable/{id}

**Sesión.** `{ id, nombre }`. Los datos de ejemplo traen Temperatura, Oxígeno, PH y Turbidez.

---

## Mediciones

### GET /register

**Sesión.** Mediciones visibles, de la más reciente a la más antigua.

| Parámetro | Tipo | Descripción |
|---|---|---|
| `estanque_id` | entero | Solo ese estanque |
| `variable_id` | entero | Solo esa variable |
| `desde` | `Y-m-d` | Desde ese día (incluido) |
| `hasta` | `Y-m-d` | Hasta ese día (incluido); no puede ser anterior a `desde` |
| `per_page` | 1 a 200 | Elementos por página (50 por defecto) |
| `page` | entero | Página (1 por defecto) |

```
GET /api/register?estanque_id=1&desde=2026-09-01&per_page=2
```

```json
{
  "message": "Registro de valores",
  "statusCode": 200,
  "error": false,
  "data": {
    "registros": [
      {
        "id": 431, "valor": 26, "fecha": "2026-10-03T08:38:00.000000Z",
        "estanque_id": 1, "estanque": { "id": 1, "nombre": "Estanque A", "...": "..." },
        "variable_id": 1, "variable": { "id": 1, "nombre": "Temperatura" },
        "user_id": 2, "usuario": { "id": 2, "name": "María López Hernández", "...": "..." }
      }
    ],
    "paginacion": { "pagina_actual": 1, "por_pagina": 2, "total": 108, "ultima_pagina": 54 }
  }
}
```

### GET /register/{id}

**Sesión.** Un elemento con el mismo formato que en el listado.

### POST /register

**Sesión.** Registra una medición. El autor es siempre el usuario del token; si el cuerpo trae `user_id`, se ignora.

| Campo | Reglas |
|---|---|
| `estanque_id` | Obligatorio; el estanque debe existir y ser visible para el usuario (un Productor solo captura en los suyos) |
| `variable_id` | Obligatorio; debe existir |
| `valor` | Obligatorio, numérico |

Responde `201` con la medición creada.

### POST /register/lote

**Sesión.** Registra varias mediciones de un estanque en una sola transacción: si un valor es inválido no se guarda ninguno. Es lo que usa la pantalla de captura de la app.

| Campo | Reglas |
|---|---|
| `estanque_id` | Igual que en `POST /register` |
| `valores` | Lista con al menos un elemento |
| `valores.*.variable_id` | Obligatorio, debe existir y no repetirse en la lista |
| `valores.*.valor` | Obligatorio, numérico |

```json
{
  "estanque_id": 5,
  "valores": [
    { "variable_id": 1, "valor": 26.4 },
    { "variable_id": 2, "valor": 6.1 },
    { "variable_id": 3, "valor": 7.2 },
    { "variable_id": 4, "valor": 12 }
  ]
}
```

Responde `201` con la lista de mediciones creadas. Los errores de validación se indican por posición: `data["valores.1.valor"]`.

### PUT /register/{id}

**Sesión.** Mismos campos y reglas que `POST /register`. Solo el autor de la medición o un Administrador; los demás reciben `403`.

### DELETE /register/{id}

**Sesión.** Solo el autor o un Administrador.

### GET /estadisticas

**Sesión.** Una fila por cada variable del catálogo, calculada sobre las mediciones visibles. Acepta los filtros `estanque_id`, `variable_id`, `desde` y `hasta` de `/register`.

```
GET /api/estadisticas?estanque_id=1&desde=2026-09-03
```

```json
{
  "message": "Estadísticas de variables",
  "statusCode": 200,
  "error": false,
  "data": [
    {
      "variable_id": 1, "nombre": "Temperatura",
      "total": 27, "minimo": 24.5, "maximo": 30, "promedio": 27.23,
      "ultimo_valor": 26, "ultima_fecha": "2026-10-03T08:38:00.000000Z"
    },
    {
      "variable_id": 2, "nombre": "Oxígeno",
      "total": 27, "minimo": 4.8, "maximo": 7.4, "promedio": 5.96,
      "ultimo_valor": 6.4, "ultima_fecha": "2026-10-03T08:38:00.000000Z"
    }
  ]
}
```

Una variable sin mediciones en el periodo aparece con `total: 0` y los demás campos en `null`. `promedio` se redondea a dos decimales.

---

## Administración

Todas estas rutas son **Admin**.

### Usuarios

`GET /user` devuelve cada usuario con `rol` (lista de roles `{ id, nombre }`) y `producer` (o `null`). Nunca incluye la contraseña.

`POST /user` y `PUT /user/{id}`:

| Campo | Alta | Edición |
|---|---|---|
| `name` | Obligatorio, hasta 100 caracteres | Igual |
| `rfc` | Obligatorio, de 10 a 13 caracteres, único | Igual (puede repetir el suyo) |
| `email` | Obligatorio, correo válido, hasta 60 caracteres, único | Igual (puede repetir el suyo) |
| `password` | Obligatorio, mínimo 8 caracteres | Opcional: si va vacío o no se envía, se conserva la actual |

La contraseña se guarda cifrada (bcrypt). Cualquier otro campo del cuerpo se ignora. Un usuario nuevo no tiene roles: asígnalos con `/roleuser`.

`DELETE /user/{id}` borra en cascada su productor, sus estanques, las mediciones de esos estanques, las que capturó y sus asignaciones de rol.

### Roles y asignaciones

`POST /role` y `PUT /role/{id}`: `nombre` obligatorio y único. Ver la advertencia sobre renombrar roles en [Configuración](CONFIGURATION.md#roles).

`GET /roleuser/{user_id}` recibe el **id del usuario** y devuelve sus asignaciones `{ id, role_id, user_id }`. En cambio, `PUT` y `DELETE /roleuser/{id}` reciben el **id de la asignación**: para quitar un rol, primero consulta las asignaciones del usuario y usa su `id`.

`POST /roleuser`:

| Campo | Reglas |
|---|---|
| `role_id` | Obligatorio, debe existir |
| `user_id` | Obligatorio, debe existir; el usuario no puede tener ya ese rol |

### GET /getProducer

Usuarios que tienen el rol Productor: `{ id, user_id, rfc, name, email, ... }` (`user_id` es igual a `id`; se conserva por compatibilidad). No incluye la contraseña.

### Productores

`POST /producer` y `PUT /producer/{id}`:

| Campo | Reglas |
|---|---|
| `user_id` | Obligatorio, debe existir; un usuario solo puede tener un productor |
| `calle` | Obligatorio, hasta 50 |
| `numero` | Obligatorio, hasta 4 dígitos |
| `colonia` | Obligatorio, hasta 50 |
| `cp` | Obligatorio, hasta 5 |
| `municipio` | Obligatorio, hasta 100 |
| `agencia` | Opcional, hasta 50 |
| `estado` | Obligatorio, hasta 50 |
| `telPrincipal` | Obligatorio, hasta 10 |
| `telSecundario` | Opcional, hasta 10 |

`POST` responde `201`.

### Estanques

`POST /estanque` y `PUT /estanque/{id}`:

| Campo | Reglas |
|---|---|
| `nombre` | Obligatorio, hasta 255 |
| `descripcion` | Opcional, hasta 255 |
| `producer_id` | Obligatorio, debe existir |

### Variables

`POST /variable` y `PUT /variable/{id}`: `nombre` obligatorio y único. La app asigna ícono, color, unidad y rango orientativo según el nombre (Temperatura, Oxígeno, PH, Turbidez); otros nombres se muestran con un ícono genérico.

---

## Ejemplo completo con curl

```bash
API=http://127.0.0.1:8000/api

TOKEN=$(curl -s -X POST $API/login -H "Content-Type: application/json" \
  -d '{"email":"miguel@example.com","password":"Demo12345"}' | php -r 'echo json_decode(stream_get_contents(STDIN))->data->token;')

# Capturar las cuatro variables del estanque 5
curl -X POST $API/register/lote -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"estanque_id":5,"valores":[{"variable_id":1,"valor":27.1},{"variable_id":2,"valor":5.8},{"variable_id":3,"valor":7.4},{"variable_id":4,"valor":11}]}'

# Resumen de los últimos 7 días
curl -H "Authorization: Bearer $TOKEN" "$API/estadisticas?estanque_id=5&desde=$(date -d '-7 days' +%F)"

# Cerrar sesión
curl -X POST $API/logout -H "Authorization: Bearer $TOKEN"
```
