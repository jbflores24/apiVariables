<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // Según los seeders: 1 Administrador, 2 Técnico, 3 Productor
    private function admin() { return User::find(1); }
    private function tecnico() { return User::find(2); }
    private function productor() { return User::find(3); }

    public function test_login_con_usuario_del_seeder_y_logout(): void
    {
        $token = $this->postJson('/api/login', [
            'email' => 'jbflores24@hotmail.com',
            'password' => '1234',
        ])->assertOk()->json('data');

        // Cada petición real llega sin sesión: se olvida el usuario del login
        $this->app['session']->flush();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_con_credenciales_invalidas(): void
    {
        $this->postJson('/api/login', [
            'email' => 'jbflores24@hotmail.com',
            'password' => 'incorrecta',
        ])->assertStatus(401);
    }

    public function test_campos_opcionales_aceptan_null(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/estanque', [
            'nombre' => 'Estanque sin descripción',
            'producer_id' => 1,
        ])->assertCreated();
    }

    public function test_get_producer_no_expone_password_y_devuelve_id_de_usuario(): void
    {
        Sanctum::actingAs($this->admin());

        $data = $this->getJson('/api/getProducer')->assertOk()->json('data');

        $this->assertCount(1, $data);
        $this->assertSame(3, $data[0]['id']);
        $this->assertSame(3, $data[0]['user_id']);
        $this->assertArrayNotHasKey('password', $data[0]);
    }

    public function test_solo_administrador_gestiona_usuarios_y_catalogos(): void
    {
        Sanctum::actingAs($this->productor());

        $this->getJson('/api/user')->assertForbidden();
        $this->getJson('/api/getProducer')->assertForbidden();
        $this->postJson('/api/role', ['nombre' => 'Otro'])->assertForbidden();
        $this->deleteJson('/api/estanque/1')->assertForbidden();
        $this->getJson('/api/estanque')->assertOk();

        Sanctum::actingAs($this->admin());
        $this->getJson('/api/user')->assertOk();
    }

    public function test_registro_toma_el_usuario_del_token_y_valida_datos(): void
    {
        Sanctum::actingAs($this->tecnico());

        $this->postJson('/api/register', [
            'estanque_id' => 1,
            'variable_id' => 1,
            'user_id' => 1,
            'valor' => 25.5,
        ])->assertCreated()->assertJsonPath('data.user_id', 2);

        $this->postJson('/api/register', [
            'estanque_id' => 999,
            'variable_id' => 1,
            'valor' => 'abc',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['estanque_id', 'valor'], 'data');
    }

    public function test_solo_el_autor_o_un_admin_modifican_un_registro(): void
    {
        // El registro 1 fue capturado por el usuario 2 (Técnico)
        Sanctum::actingAs($this->productor());
        $this->deleteJson('/api/register/1')->assertForbidden();

        Sanctum::actingAs($this->tecnico());
        $this->putJson('/api/register/1', [
            'estanque_id' => 1,
            'variable_id' => 1,
            'valor' => 19,
        ])->assertOk();

        Sanctum::actingAs($this->admin());
        $this->deleteJson('/api/register/1')
            ->assertOk()
            ->assertJsonPath('error', false);
    }

    public function test_alta_de_usuario_invalida_responde_422(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/user', ['name' => 'Sin datos'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rfc', 'email', 'password'], 'data');
    }
}
