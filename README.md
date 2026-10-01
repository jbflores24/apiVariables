# API Variables

API REST para la captura de variables físicas (temperatura, oxígeno, pH, turbidez) en estanques
acuícolas de la región del Istmo de Tehuantepec (Instituto Tecnológico de Salina Cruz).
Es el backend de la aplicación móvil `appVariables` (Ionic + Angular).

Construida con **Laravel 10** y **Laravel Sanctum** (autenticación por token).

## Instalación

Requisitos: PHP 8.1+, Composer y MySQL.

```bash
composer install
cp .env.example .env          # configurar DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Con `--host=0.0.0.0` la API queda accesible desde otros dispositivos de la red. En un celular
**no** se usa `localhost`: en la app configura la IP de la computadora, por ejemplo
`http://192.168.1.50:8000/api`.

### Usuarios de prueba (seeders)

Todos con contraseña `1234`:

| Usuario | Email | Roles |
|---|---|---|
| 1 | jbflores24@hotmail.com | Administrador, Técnico |
| 2 | maria@hotmail.com | Técnico |
| 3 | miguel@hotmail.com | Productor |
| 4 | mauricio@gmail.com | Administrador |

## Roles

| Rol | Puede |
|---|---|
| **Administrador** | Todo: usuarios, roles, productores, estanques, variables y registros de todos. |
| **Técnico** | Consultar todos los productores, estanques y registros; capturar mediciones. |
| **Productor** | Consultar solo su información y sus estanques; capturar mediciones en sus estanques. |

Cualquier usuario ve además los registros que él mismo capturó. Solo el autor de un registro
o un Administrador pueden editarlo o eliminarlo.

## Formato de respuesta

Todas las respuestas (incluidos los errores) tienen la misma forma:

```json
{ "message": "Texto", "statusCode": 200, "error": false, "data": { } }
```

| Código | Significado |
|---|---|
| 200 / 201 | Correcto / creado |
| 401 | Sin token o token inválido → volver al login |
| 403 | El rol del usuario no tiene permiso |
| 404 | No existe (o no es visible para el usuario) |
| 422 | Error de validación; `data` trae los errores por campo: `{ "email": ["..."] }` |
| 429 | Demasiados intentos de login (máx. 10 por minuto) |

Se recomienda enviar el body como **JSON** (`Content-Type: application/json`).

## Autenticación

1. `POST /api/login` con `{ "email", "password" }` devuelve:

   ```json
   { "data": { "token": "1|abc...", "user": { "id": 3, "name": "...", "roles": ["Productor"], "producer": { } } } }
   ```

2. En cada petición enviar el encabezado `Authorization: Bearer <token>`.
3. `POST /api/logout` invalida el token.

## Endpoints

Todas las rutas llevan el prefijo `/api` y, salvo `login`, requieren token.

### Sesión

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/login` | Inicia sesión; devuelve token y perfil |
| GET | `/me` | Perfil del usuario autenticado (roles y datos de productor) |
| PUT | `/me/password` | Cambiar contraseña: `password_actual`, `password`, `password_confirmation` |
| POST | `/logout` | Cierra la sesión actual |

### Consulta (cualquier rol; el Productor solo ve lo suyo)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/producer`, `/producer/{id}` | Productores con su usuario y estanques |
| GET | `/getProducerUserId/{user_id}` | Productor asociado a un usuario |
| GET | `/estanque`, `/estanque/{id}` | Estanques |
| GET | `/variable`, `/variable/{id}` | Variables que se miden |

### Registros de mediciones

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/register` | Listado paginado. Filtros: `estanque_id`, `variable_id`, `desde`, `hasta` (`Y-m-d`), `per_page` (máx. 200), `page` |
| GET | `/register/{id}` | Un registro |
| POST | `/register` | Un valor: `estanque_id`, `variable_id`, `valor` |
| POST | `/register/lote` | Varios valores de un estanque en una sola operación (todo o nada) |
| PUT | `/register/{id}` | Editar (autor o Administrador) |
| DELETE | `/register/{id}` | Eliminar (autor o Administrador) |
| GET | `/estadisticas` | Resumen por variable; mismos filtros que `/register` |

El usuario del registro siempre se toma del token; no hace falta enviar `user_id`.

Ejemplo de captura en lote:

```json
POST /api/register/lote
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

Respuesta del listado paginado (`data`):

```json
{
  "registros": [ { "id": 9, "valor": 23.1, "estanque_id": 3, "estanque": {}, "variable_id": 4, "variable": {}, "user_id": 1, "usuario": {}, "fecha": "2026-10-01T12:00:00.000000Z" } ],
  "paginacion": { "pagina_actual": 1, "por_pagina": 50, "total": 9, "ultima_pagina": 1 }
}
```

Respuesta de estadísticas (`data`, una fila por variable):

```json
[ { "variable_id": 1, "nombre": "Temperatura", "total": 12, "minimo": 19.5, "maximo": 27.1, "promedio": 23.4, "ultimo_valor": 24.0, "ultima_fecha": "..." } ]
```

### Administración (solo Administrador)

| Método | Ruta | Descripción |
|---|---|---|
| CRUD | `/user` | Usuarios. Al editar, si `password` va vacío se conserva la actual |
| CRUD | `/role` | Roles |
| CRUD | `/roleuser` | Asignación de roles (`role_id`, `user_id`); `GET /roleuser/{user_id}` lista los roles de un usuario |
| GET | `/getProducer` | Usuarios con rol Productor |
| POST/PUT/DELETE | `/producer`, `/estanque`, `/variable` | Alta, edición y baja |

## Pruebas

```bash
php artisan test
```

Usan SQLite en memoria (no tocan la base de datos MySQL).
