# Pruebas

El proyecto usa PHPUnit 10 a través de Laravel. Las pruebas corren sobre **SQLite en memoria** (configurado en `phpunit.xml`), así que no tocan la base de datos MySQL.

```bash
php artisan test
php artisan test --filter=captura
vendor/bin/phpunit tests/Feature/ApiTest.php
```

Cada prueba recrea las tablas y carga los seeders (`RefreshDatabase` + `$seed = true`), por lo que trabaja con los mismos usuarios de ejemplo: 1 Administrador y Técnico, 2 Técnico, 3 Productor (dueño del estanque 5).

## Integración

`tests/Feature/ApiTest.php`:

| Prueba | Verifica |
|---|---|
| `test_login_devuelve_token_y_perfil_y_logout_lo_revoca` | El login devuelve token, roles y productor; el logout borra el token |
| `test_login_con_credenciales_invalidas` | `401` con contraseña incorrecta |
| `test_sin_token_responde_401_en_json_aunque_no_se_pida_json` | Sin token y sin `Accept` responde `401` en JSON (no redirige) |
| `test_errores_usan_el_formato_de_api_response` | `404` y `422` con el formato común |
| `test_me_devuelve_el_perfil_con_roles` | `/me` trae los roles |
| `test_cambio_de_password` | Rechaza la contraseña actual incorrecta y guarda la nueva |
| `test_campos_opcionales_aceptan_null` | Un estanque sin descripción se guarda (migración con `nullable()`) |
| `test_get_producer_no_expone_password_y_devuelve_id_de_usuario` | `/getProducer` sin contraseña y con el id correcto |
| `test_solo_administrador_gestiona_usuarios_y_catalogos` | Un Productor recibe `403` en administración |
| `test_productor_solo_ve_su_informacion` | Un Productor solo ve lo suyo y no captura en estanques ajenos; el Técnico ve todo |
| `test_registro_toma_el_usuario_del_token_y_valida_datos` | Se ignora el `user_id` del cuerpo; `exists` y `numeric` |
| `test_captura_en_lote` | Guarda varias mediciones; si una es inválida no guarda ninguna |
| `test_listado_de_registros_filtrado_y_paginado` | Filtros por estanque y fechas, paginación y campo `fecha` |
| `test_estadisticas_por_variable` | Total, último valor y variables sin datos |
| `test_solo_el_autor_o_un_admin_modifican_un_registro` | `403` para quien no es autor; el Administrador sí puede |
| `test_alta_de_usuario_invalida_responde_422` | Errores por campo al crear un usuario |
| `test_editar_usuario_sin_password_conserva_la_actual` | Editar sin contraseña no la borra |
| `test_no_se_asigna_el_mismo_rol_dos_veces` | Rol duplicado o inexistente responde `422` |

`tests/Unit/ExampleTest.php` y `tests/Feature/ExampleTest.php` son los ejemplos que trae Laravel.

## Agregar una prueba

```php
public function test_ejemplo(): void
{
    Sanctum::actingAs(User::find(2));   // inicia sesión como el Técnico

    $this->getJson('/api/estanque')
        ->assertOk()
        ->assertJsonPath('error', false);
}
```

Para probar la app contra la API, ver la sección de pruebas en la documentación de appVariables.
