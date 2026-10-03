# Arquitectura

apiVariables es un proyecto Laravel 10 con la estructura estándar del framework. Solo expone una API JSON (`routes/api.php`); no tiene vistas.

---

## Flujo de una captura

```
App móvil                               apiVariables                                   MySQL
─────────                               ────────────                                   ─────
Nueva captura → Guardar
POST /api/register/lote ──────────►  grupo de middleware "api"
  Authorization: Bearer <token>        ForceJsonResponse      (Accept: application/json)
  { estanque_id, valores[] }           ThrottleRequests:api   (60 por minuto)
                                     auth:sanctum             (token → usuario)
                                     RegisterController::lote()
                                       valida estanque visible, variables y valores
                                       DB::transaction ─────────────────────────────►  INSERT registers × N
                                         user_id = usuario del token        ◄──────────
App: aviso «Se guardaron 4 mediciones» ◄── 201 { data: [registros] }
```

Si cualquier valor es inválido, la validación responde `422` antes de abrir la transacción; si falla un `INSERT`, la transacción se revierte y no queda ninguna medición a medias.

## Flujo de una consulta con permisos

```
GET /api/estanque  (usuario Productor)
  auth:sanctum → $request->user()
  EstanqueController::index()
    Estanque::visiblePara($user)
      ¿Administrador o Técnico? → sin filtro
      si no → whereHas('producer', user_id = $user->id)
  ← 200 { data: [solo sus estanques] }
```

Las rutas de administración pasan además por el middleware `role:Administrador`, que responde `403` antes de llegar al controlador.

---

## Estructura

```
apiVariables/
├── app/
│   ├── Exceptions/
│   │   └── Handler.php                 ← errores de /api/* con el formato de ApiResponse
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      ← login, me, cambio de contraseña, logout
│   │   │   ├── UserController.php      ← usuarios y getProducer
│   │   │   ├── RoleController.php      ← roles
│   │   │   ├── RoleUserController.php  ← asignación de roles
│   │   │   ├── ProducerController.php  ← productores (dirección) y getProducerUserId
│   │   │   ├── EstanqueController.php  ← estanques
│   │   │   ├── VariableController.php  ← catálogo de variables
│   │   │   └── RegisterController.php  ← mediciones, lote y estadísticas
│   │   ├── Middleware/
│   │   │   ├── ForceJsonResponse.php   ← fuerza Accept: application/json
│   │   │   └── EnsureUserHasRole.php   ← alias "role" (role:Administrador)
│   │   ├── Resources/                  ← forma JSON de usuarios, productores, estanques y mediciones
│   │   ├── Responses/
│   │   │   └── ApiResponse.php         ← success() y error()
│   │   └── Kernel.php                  ← grupos y alias de middleware
│   └── Models/
│       ├── User.php                    ← hasRole, isAdmin, puedeVerTodo, perfil
│       ├── Role.php / RoleUser.php
│       ├── Producer.php                ← scopeVisiblePara
│       ├── Estanque.php                ← scopeVisiblePara
│       ├── Register.php                ← scopeVisiblePara (incluye lo capturado por el usuario)
│       └── Variable.php
├── config/
│   ├── cors.php                        ← orígenes permitidos
│   └── sanctum.php                     ← caducidad de tokens
├── database/
│   ├── migrations/                     ← tablas (ver DATABASE.md)
│   └── seeders/                        ← usuarios, roles, productores, estanques, variables y mediciones de ejemplo
├── routes/
│   └── api.php                         ← todas las rutas y sus permisos
└── tests/
    └── Feature/ApiTest.php             ← pruebas de integración
```

---

## Capas

| Capa | Responsabilidad |
|---|---|
| `routes/api.php` | Qué rutas existen y quién puede usarlas (`auth:sanctum`, `role:Administrador`, `throttle`) |
| Middleware | Formato JSON, autenticación, límites y rol |
| Controladores | Validación de la entrada y orquestación; cada uno responde con `ApiResponse` |
| Modelos | Relaciones de Eloquent, reglas de visibilidad (`scopeVisiblePara`) y ayudantes de rol en `User` |
| Resources | Forma del JSON de salida (por ejemplo, `RegisterResource` agrega `fecha` y `usuario`) |
| `Exceptions/Handler` | Convierte cualquier excepción no atrapada de `/api/*` al formato común |

No hay capa de servicios: la lógica es lo bastante simple para vivir en los controladores. Si crece (por ejemplo, alertas por valores fuera de rango), conviene extraerla a `app/Services`.

## Roles y visibilidad

Los permisos se resuelven en dos niveles:

1. **Por ruta** (`routes/api.php`): las rutas de administración están dentro de `Route::middleware('role:Administrador')`. `EnsureUserHasRole` consulta `User::hasRole()`.
2. **Por registro** (modelos): las consultas de productores, estanques y mediciones usan `visiblePara($user)`. Para un Administrador o Técnico (`User::puedeVerTodo()`) no filtran nada; para los demás, filtran por el `user_id` del productor.

Además, `RegisterController` comprueba que solo el autor o un Administrador editen o eliminen una medición, y que el estanque de una captura sea visible para quien captura.

Ver [ADR 0002](adr/0002-roles-y-middleware.md) y [ADR 0003](adr/0003-visibilidad-por-rol.md).

## Respuestas

`App\Http\Responses\ApiResponse` construye todas las respuestas:

```php
ApiResponse::success('Registro agregado', 201, $registro);
ApiResponse::error('No autorizado', 403);
```

Las excepciones que no atrapa un controlador (token inválido, validación, rutas inexistentes, límites, errores de base de datos) las convierte `Handler::respuestaApi()` al mismo formato. Ver [ADR 0004](adr/0004-formato-de-respuesta-unico.md).

## Agregar una variable nueva

No requiere código en la API: un Administrador la crea desde la app o con `POST /variable`. Aparece en el formulario de captura y en las estadísticas automáticamente. Para que la app le asigne ícono, unidad y rango orientativo, agrégala en `src/app/core/variables-meta.ts` de appVariables.
