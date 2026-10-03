# ADR 0006: El autor de una medición sale del token

## Estado

Aceptada.

## Contexto

`POST /register` recibía `user_id` en el cuerpo. Cualquier usuario podía registrar mediciones a nombre de otro, y la app enviaba el id del productor, no el de quien capturaba.

## Decisión

Ignorar el `user_id` del cuerpo. El autor de cada medición es siempre `$request->user()`. Solo el autor o un Administrador pueden editar o eliminar una medición.

## Consecuencias

- `registers.user_id` significa «quién capturó», no «de quién es el estanque» (eso se obtiene por `estanque → producer`).
- Un técnico que captura en el estanque de un productor queda registrado como autor.
- Los clientes antiguos que envían `user_id` siguen funcionando: el campo se ignora.
