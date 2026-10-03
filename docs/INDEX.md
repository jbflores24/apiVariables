# Documentación de Variables Acuícolas (API)

Autor: José Braulio Flores Martínez

Portada interna de la documentación técnica del backend. Los repositorios se llaman `apiVariables` (este) y `appVariables` (la app móvil).

## Primeros pasos

- [Inicio rápido](QUICKSTART.md)
- [Configuración](CONFIGURATION.md)
- [Arquitectura](ARCHITECTURE.md)

## Referencia

- [Referencia de la API](API.md)
- [Base de datos](DATABASE.md)
- [Seguridad](SECURITY.md)

## Operación

- [Despliegue](DEPLOYMENT.md)
- [Pruebas](TESTING.md)
- [Hoja de ruta](ROADMAP.md)

## Decisiones de arquitectura (ADR)

- [0001 Tokens personales de Sanctum](adr/0001-tokens-de-sanctum.md)
- [0002 Roles en tabla propia y middleware `role`](adr/0002-roles-y-middleware.md)
- [0003 Visibilidad por rol con scopes de Eloquent](adr/0003-visibilidad-por-rol.md)
- [0004 Formato de respuesta único, también en errores](adr/0004-formato-de-respuesta-unico.md)
- [0005 Captura en lote transaccional](adr/0005-captura-en-lote.md)
- [0006 El autor de una medición sale del token](adr/0006-autor-desde-el-token.md)
- [0007 Corregir las migraciones originales](adr/0007-corregir-migraciones-originales.md)

## Recomendación de lectura

Si es la primera vez que instalas el proyecto:

1. QUICKSTART.md
2. CONFIGURATION.md
3. DEPLOYMENT.md

Si vas a consumir la API desde otra aplicación:

1. API.md
2. SECURITY.md

Si vas a modificar el comportamiento:

1. ARCHITECTURE.md
2. DATABASE.md
3. `routes/api.php` y `app/Http/Controllers/RegisterController.php`
