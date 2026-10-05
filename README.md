# Variables Acuícolas (API)

Autor: José Braulio Flores Martínez

Backend del sistema Variables Acuícolas, para el monitoreo de la calidad del agua en estanques de la región del Istmo de Tehuantepec (Tecnológico Nacional de México, Instituto Tecnológico de Salina Cruz).
Construido con Laravel 10. Recibe las mediciones que se capturan en la app móvil (temperatura, oxígeno disuelto, pH y turbidez), las guarda por estanque y devuelve historiales y estadísticas.

## El proyecto

En la acuicultura, la calidad del agua decide si un cultivo prospera o se pierde: un descenso del oxígeno disuelto durante la noche o un cambio brusco de temperatura bastan para causar mortandad en un estanque. Por eso los productores y técnicos miden varias veces al día un pequeño conjunto de variables físicas y químicas.

**Variables Acuícolas** reemplaza la libreta de campo: cada medición queda registrada con su estanque, su fecha y quien la tomó, y cualquier persona autorizada puede consultar la tendencia de un estanque desde el teléfono. La API es la parte central de ese registro: decide quién puede ver y capturar qué, valida cada valor y calcula los resúmenes.

## Qué incluye

- Inicio de sesión con token personal (Laravel Sanctum) y cierre de sesión que revoca el token
- Perfil del usuario autenticado con sus roles y datos de productor (`/me`) y cambio de contraseña
- Tres roles: **Administrador**, **Técnico** y **Productor**, con permisos distintos en cada ruta
- Visibilidad por rol: un productor solo ve su información, sus estanques y sus mediciones
- Catálogos de usuarios, roles, productores (dirección y teléfonos), estanques y variables
- Captura de mediciones una por una o en lote, en una transacción (todo o nada)
- El autor de cada medición se toma del token, nunca del cuerpo de la petición
- Historial de mediciones con filtros por estanque, variable y rango de fechas, paginado
- Estadísticas por variable: total, mínimo, máximo, promedio y última lectura
- Formato de respuesta único `{ message, statusCode, error, data }`, también en los errores
- Errores de validación con el detalle por campo, listos para mostrarse en los formularios
- Límite de 10 intentos de inicio de sesión por minuto y de 60 peticiones por minuto por usuario
- Datos de ejemplo (seeders) con usuarios de los tres roles
- 18 pruebas de integración con SQLite en memoria

## Qué no incluye

- Registro público de usuarios: las cuentas las crea un administrador
- Recuperación de contraseña por correo
- Rangos óptimos por variable en el servidor (los rangos orientativos viven en la app)
- Alertas automáticas por valores fuera de rango
- Panel web de administración (todo se administra desde la app)

## Requisitos

- PHP 8.1 o superior con las extensiones `pdo_mysql`, `mbstring`, `openssl` y `tokenizer`
- Composer 2
- MySQL 8 o MariaDB 10.4 o superior (o SQLite para pruebas)
- Servidor web con reescritura de URLs (Apache con `mod_rewrite` o Nginx)

## Instalación

```bash
git clone https://github.com/jbflores24/apiVariables.git
cd apiVariables
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Prueba rápida:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Demo12345"}'
```

## Documentación

- [Índice](docs/INDEX.md)
- [Inicio rápido](docs/QUICKSTART.md)
- [Configuración](docs/CONFIGURATION.md)
- [Referencia de la API](docs/API.md)
- [Arquitectura](docs/ARCHITECTURE.md)
- [Base de datos](docs/DATABASE.md)
- [Seguridad](docs/SECURITY.md)
- [Despliegue](docs/DEPLOYMENT.md)
- [Pruebas](docs/TESTING.md)
- [Hoja de ruta](docs/ROADMAP.md)

## Proyecto relacionado

- [appVariables](https://github.com/jbflores24/appVariables): aplicación móvil (Ionic + Angular + Capacitor)
