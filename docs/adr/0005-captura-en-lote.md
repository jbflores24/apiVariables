# ADR 0005: Captura en lote transaccional

## Estado

Aceptada.

## Contexto

En cada visita a un estanque se miden las cuatro variables a la vez. La app enviaba cuatro peticiones independientes: si fallaba una, quedaban mediciones a medias y la app avisaba «guardado» de todos modos.

## Decisión

Agregar `POST /register/lote` con `{ estanque_id, valores: [{ variable_id, valor }] }`. Se valida todo antes de escribir y las inserciones ocurren dentro de `DB::transaction()`. `POST /register` se conserva para una medición suelta.

## Consecuencias

- O se guardan todas las mediciones de la visita o ninguna.
- Una sola petición en lugar de cuatro, útil con mala señal en campo.
- La validación indica el elemento con error por posición (`valores.1.valor`); la app traduce esa posición al campo correspondiente.
- Todas las mediciones de un lote comparten prácticamente la misma fecha, lo que facilita agruparlas.
