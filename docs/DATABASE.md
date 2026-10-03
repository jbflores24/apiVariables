# Base de datos

MySQL 8 o MariaDB 10.4+ en producción; SQLite en las pruebas. Las tablas se crean con las migraciones de `database/migrations`:

```bash
php artisan migrate          # crea las tablas que falten
php artisan migrate --seed   # y carga los datos de ejemplo
php artisan migrate:fresh --seed   # borra todo y recrea (solo desarrollo)
```

## Diagrama

```
users ──1:N── role_user ──N:1── roles
  │
  ├──1:1── producers ──1:N── estanques ──1:N── registers ──N:1── variables
  │                                                │
  └────────────────────1:N─────────────────────────┘  (registers.user_id: quién capturó)

users ──1:N── personal_access_tokens   (tokens de Sanctum)
```

Todas las llaves foráneas tienen `ON DELETE CASCADE`: borrar un usuario borra su productor, sus estanques, las mediciones de esos estanques, las que capturó y sus asignaciones de rol.

## Tablas

### users

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| rfc | VARCHAR(255) | RFC; la API exige de 10 a 13 caracteres y que sea único |
| name | VARCHAR(255) | Nombre completo (la API acepta hasta 100 caracteres) |
| email | VARCHAR(255) UNIQUE | Correo con el que se inicia sesión |
| email_verified_at | TIMESTAMP NULL | Sin uso |
| password | VARCHAR(255) | Hash bcrypt (cast `hashed` del modelo) |
| remember_token | VARCHAR(100) NULL | Sin uso en la API |
| created_at, updated_at | TIMESTAMP NULL | |

### roles

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| nombre | VARCHAR(255) | `Administrador`, `Técnico` o `Productor` (los nombres están fijos en el código) |
| created_at, updated_at | TIMESTAMP NULL | |

### role_user

Asignación de roles. Un usuario puede tener varios.

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | Id de la asignación (el que usan `PUT` y `DELETE /roleuser/{id}`) |
| role_id | BIGINT UNSIGNED FK | `roles.id`, `ON DELETE CASCADE` |
| user_id | BIGINT UNSIGNED FK | `users.id`, `ON DELETE CASCADE` |
| created_at, updated_at | TIMESTAMP NULL | |

La API impide duplicar el par (`role_id`, `user_id`); la tabla no tiene índice único.

### producers

Datos de la unidad de producción de un usuario (dirección y teléfonos). Un usuario tiene como máximo uno.

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| calle | VARCHAR(255) | |
| numero | INTEGER | |
| colonia | VARCHAR(255) | |
| cp | VARCHAR(255) | Código postal |
| municipio | VARCHAR(255) | |
| agencia | VARCHAR(255) NULL | Agencia municipal, opcional |
| estado | VARCHAR(255) | |
| telPrincipal | VARCHAR(255) | |
| telSecundario | VARCHAR(255) NULL | |
| user_id | BIGINT UNSIGNED FK UNIQUE | `users.id`, `ON DELETE CASCADE` |
| created_at, updated_at | TIMESTAMP NULL | |

### estanques

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| nombre | VARCHAR(255) | |
| descripcion | VARCHAR(255) NULL | Tipo de cultivo, dimensiones, ubicación |
| producer_id | BIGINT UNSIGNED FK | `producers.id`, `ON DELETE CASCADE` |
| created_at, updated_at | TIMESTAMP NULL | |

### variables

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| nombre | VARCHAR(255) | Único (validado por la API) |
| created_at, updated_at | TIMESTAMP NULL | |

### registers

Cada fila es una medición.

| Columna | Tipo | Descripción |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| estanque_id | BIGINT UNSIGNED FK | `estanques.id`, `ON DELETE CASCADE` |
| variable_id | BIGINT UNSIGNED FK | `variables.id`, `ON DELETE CASCADE` |
| user_id | BIGINT UNSIGNED FK | Quién capturó (`users.id`, `ON DELETE CASCADE`); siempre el usuario del token |
| valor | DOUBLE(8,2) en MySQL | Valor medido, en la unidad de la variable. Se guarda con 2 decimales (máximo 999,999.99); SQLite no redondea |
| created_at | TIMESTAMP NULL | Momento de la captura (UTC); es la `fecha` que devuelve la API |
| updated_at | TIMESTAMP NULL | |

### personal_access_tokens

Tabla estándar de Laravel Sanctum: un registro por sesión iniciada. `POST /logout` borra el de la sesión actual; cambiar la contraseña borra los demás del usuario.

## Datos de ejemplo

`php artisan db:seed` (o `migrate --seed`) carga, en este orden:

| Seeder | Contenido |
|---|---|
| `UserSeeder` | 4 usuarios con contraseña `1234` |
| `RoleSeeder` | Administrador, Técnico y Productor |
| `RolUserSeeder` | Roles de los 4 usuarios (ver [Inicio rápido](QUICKSTART.md#usuarios-de-ejemplo)) |
| `ProducerSeeder` | Dirección de los 4 usuarios |
| `VariableSeeder` | Temperatura, Oxígeno, PH y Turbidez |
| `EstanqueSeeder` | Estanques A y B (productor 1), C y D (productor 2) y E (productor 3) |
| `RegisterSeeder` | 9 mediciones de ejemplo |

## Respaldo

```bash
mysqldump -u USUARIO -p apivariables > respaldo-$(date +%F).sql
mysql -u USUARIO -p apivariables < respaldo-2026-10-03.sql
```
