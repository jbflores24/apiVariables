# ADR 0007: Corregir las migraciones originales

## Estado

Aceptada.

## Contexto

Tres columnas opcionales (`producers.agencia`, `producers.telSecundario` y `estanques.descripcion`) se declararon con `->nullable` sin paréntesis, que no tiene efecto: quedaron como `NOT NULL`. Corregirlas con una migración nueva requiere `doctrine/dbal` en Laravel 10. El proyecto estaba en desarrollo, con datos solo de ejemplo.

## Decisión

Corregir las migraciones originales (`->nullable()`) y recrear la base de datos con `php artisan migrate:fresh --seed`.

## Consecuencias

- Sin dependencias nuevas ni migraciones de corrección.
- Una base de datos creada antes de la corrección conserva las columnas `NOT NULL` hasta recrearla; `migrate:fresh` borra todos los datos.
- A partir de que haya datos reales, los cambios de esquema deben hacerse **siempre con migraciones nuevas**, nunca editando las existentes.
