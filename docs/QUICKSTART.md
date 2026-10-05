# Inicio rápido

## 1. Dependencias

```bash
git clone https://github.com/jbflores24/apiVariables.git
cd apiVariables
composer install
```

## 2. Entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita en `.env` la conexión a la base de datos:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apivariables
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos vacía (`CREATE DATABASE apivariables CHARACTER SET utf8mb4;`) antes del siguiente paso.

## 3. Tablas y datos de ejemplo

```bash
php artisan migrate --seed
```

Si ya tenías una versión anterior de la base de datos, recréala:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` borra todas las tablas. Úsalo solo en desarrollo o después de respaldar.

## 4. Levantar el servidor

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Con `--host=0.0.0.0` la API queda accesible desde otros dispositivos de la misma red, que es lo que necesita la app en el teléfono. Averigua la IP de tu computadora (`ipconfig` en Windows, `ip addr` en Linux) y úsala en la app: `http://192.168.1.50:8000/api`.

## 5. Probar

Iniciar sesión:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Demo12345"}'
```

La respuesta trae `data.token`. Úsalo en las demás rutas:

```bash
TOKEN="1|..."
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/me
curl -H "Authorization: Bearer $TOKEN" "http://127.0.0.1:8000/api/estadisticas?estanque_id=1"
```

## Usuarios de ejemplo

Todos con contraseña `Demo12345`:

| Id | Correo | Roles | Datos de productor |
|---|---|---|---|
| 1 | admin@example.com | Administrador, Técnico | Sí (estanques A y B) |
| 2 | maria@example.com | Técnico | Sí (estanques C y D) |
| 3 | miguel@example.com | Productor | Sí (estanque E) |
| 4 | mauricio@example.com | Administrador | Sí (sin estanques) |

Cambia estas contraseñas, o crea usuarios nuevos y borra estos, antes de usar el sistema con datos reales.

## Siguiente paso

Instala la app móvil: [appVariables](https://github.com/jbflores24/appVariables).
