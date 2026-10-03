# Despliegue

## Requisitos del servidor

- PHP 8.1+ con `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`
- Composer 2
- MySQL 8 o MariaDB 10.4+
- Apache con `mod_rewrite` o Nginx
- Certificado HTTPS (Let's Encrypt o el del hosting)

## Pasos

```bash
git clone https://github.com/jbflores24/apiVariables.git
cd apiVariables
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edita `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://variables.tu-dominio.mx
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=apivariables
DB_USERNAME=usuario_variables
DB_PASSWORD=una-contraseña-segura
```

Crea las tablas. En producción no cargues los datos de ejemplo, salvo los catálogos:

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=VariableSeeder --force
```

Crea el primer administrador:

```bash
php artisan tinker
>>> $u = App\Models\User::create(['name' => 'Nombre Apellido', 'rfc' => 'XXXX000000XXX', 'email' => 'admin@tu-dominio.mx', 'password' => 'contraseña-segura']);
>>> App\Models\RoleUser::create(['role_id' => App\Models\Role::where('nombre', 'Administrador')->value('id'), 'user_id' => $u->id]);
```

El resto de usuarios, roles, productores y estanques se crean desde la app.

Optimiza y da permisos:

```bash
php artisan config:cache
php artisan route:cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Apache

La raíz del sitio debe apuntar a `public/`:

```apache
<VirtualHost *:443>
    ServerName variables.tu-dominio.mx
    DocumentRoot /var/www/apiVariables/public
    <Directory /var/www/apiVariables/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`public/.htaccess` ya incluye las reglas de reescritura y reenvía el encabezado `Authorization` a PHP.

En un hosting compartido sin acceso a la configuración del servidor, sube el proyecto fuera de `public_html` y apunta el subdominio a la carpeta `public`.

## Nginx

```nginx
server {
    listen 443 ssl;
    server_name variables.tu-dominio.mx;
    root /var/www/apiVariables/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}
```

## Comprobar

```bash
curl -i -X POST https://variables.tu-dominio.mx/api/login \
  -H "Content-Type: application/json" -d '{"email":"admin@tu-dominio.mx","password":"..."}'
```

Debe responder `200` con `data.token`. Un `404` en HTML indica que la raíz no apunta a `public/` o falta la reescritura. Un `401` en `/me` con el token correcto indica que el servidor no reenvía el encabezado `Authorization`.

## Actualizar

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

## Probar desde el teléfono en la red local

Para desarrollo, sin HTTPS:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

En la app, la URL de la API es `http://IP-DE-TU-COMPUTADORA:8000/api`. Android bloquea `http://` en apps publicadas; para pruebas en la red local se habilita en la app (ver la guía de compilación de appVariables). El teléfono y la computadora deben estar en la misma red y el firewall debe permitir el puerto 8000.
