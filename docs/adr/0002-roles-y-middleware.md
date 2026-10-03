# ADR 0002: Roles en tabla propia y middleware `role`

## Estado

Aceptada.

## Contexto

El sistema tiene tres perfiles: quien administra, quien hace trabajo de campo para varios productores y el productor mismo. Un usuario puede tener más de un perfil. El proyecto ya tenía las tablas `roles` y `role_user`, pero ninguna ruta revisaba los roles: cualquier usuario con sesión podía crear administradores.

## Decisión

Conservar las tablas existentes y agregar:

- `User::hasRole(...$nombres)`, `isAdmin()` y `puedeVerTodo()`, que comparan por **nombre** del rol;
- el middleware `EnsureUserHasRole` con el alias `role`, usado como `role:Administrador` en un grupo de rutas.

No se adoptó un paquete de permisos (por ejemplo spatie/laravel-permission).

## Consecuencias

- Los permisos de cada ruta se leen en un solo archivo, `routes/api.php`.
- Comparar por nombre evita depender de los ids, pero obliga a no renombrar los roles `Administrador`, `Técnico` y `Productor`. La API todavía no lo impide (pendiente en la hoja de ruta).
- Si en el futuro se necesitan permisos finos (por ejemplo «puede editar variables pero no usuarios»), conviene migrar a un paquete de permisos.
