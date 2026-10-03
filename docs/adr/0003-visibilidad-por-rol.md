# ADR 0003: Visibilidad por rol con scopes de Eloquent

## Estado

Aceptada.

## Contexto

Un productor no debe ver los estanques ni las mediciones de otros productores. Técnicos y administradores sí necesitan verlo todo. Repetir la condición en cada controlador era propenso a olvidos.

## Decisión

Cada modelo con datos de un productor tiene un `scopeVisiblePara(User $user)`:

- `Producer`: su propio productor (`user_id`).
- `Estanque`: los estanques de su productor.
- `Register`: las mediciones de sus estanques **o las que él capturó**.

Para `puedeVerTodo()` (Administrador o Técnico) el scope no filtra. Los controladores consultan siempre a través del scope (`Estanque::visiblePara($user)->findOrFail($id)`).

## Consecuencias

- Lo ajeno responde `404`, igual que lo inexistente, sin revelar que existe.
- Las estadísticas y el historial respetan los permisos sin código adicional, porque parten del mismo scope.
- Incluir «lo que él capturó» en `Register` permite que alguien vea lo que registró aunque después cambie de estanque o de rol.
- Un modelo nuevo con datos de productor debe agregar su propio scope; no hay una política global que lo obligue.
