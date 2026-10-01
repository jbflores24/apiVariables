<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Register;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // Según los seeders: 1 Administrador y Técnico, 2 Técnico, 3 Productor (dueño del estanque 5)
    private function admin() { return User::find(1); }
    private function tecnico() { return User::find(2); }
    private function productor() { return User::find(3); }

    private function olvidarSesion(): void
    {
        // Cada petición real llega sin sesión: se olvida el usuario del login
        $this->app['session']->flush();
        $this->app['auth']->forgetGuards();
    }

    public function test_login_devuelve_token_y_perfil_y_logout_lo_revoca(): void
    {
        $data = $this->postJson('/api/login', [
            'email' => 'miguel@hotmail.com',
            'password' => '1234',
        ])->assertOk()->json('data');

        $this->assertNotEmpty($data['token']);
        $this->assertSame(3, $data['user']['id']);
        $this->assertSame(['Productor'], $data['user']['roles']);
        $this->assertSame(3, $data['user']['producer']['id']);

        $this->olvidarSesion();
        $this->withToken($data['token'])->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_con_credenciales_invalidas(): void
    {
        $this->postJson('/api/login', [
            'email' => 'jbflores24@hotmail.com',
            'password' => 'incorrecta',
        ])->assertStatus(401)->assertJsonPath('error', true);
    }

    public function test_sin_token_responde_401_en_json_aunque_no_se_pida_json(): void
    {
        $this->get('/api/me')
            ->assertStatus(401)
            ->assertJson(['message' => 'No autenticado', 'statusCode' => 401, 'error' => true]);
    }

    public function test_errores_usan_el_formato_de_api_response(): void
    {
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/no-existe')->assertStatus(404)->assertJsonPath('error', true);
        $this->getJson('/api/estanque/999')->assertStatus(404)->assertJsonPath('error', true);
        $this->getJson('/api/register?desde=ayer')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['desde'], 'data');
    }

    public function test_me_devuelve_el_perfil_con_roles(): void
    {
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', 1)
            ->assertJsonPath('data.roles', ['Administrador', 'Técnico']);
    }

    public function test_cambio_de_password(): void
    {
        Sanctum::actingAs($this->productor());

        $this->putJson('/api/me/password', [
            'password_actual' => 'incorrecta',
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ])->assertStatus(422)->assertJsonValidationErrors(['password_actual'], 'data');

        $this->putJson('/api/me/password', [
            'password_actual' => '1234',
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ])->assertOk();

        $this->assertTrue(Hash::check('nueva123', $this->productor()->password));
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
        $this->deleteJson('/api/estanque/5')->assertForbidden();

        Sanctum::actingAs($this->admin());
        $this->getJson('/api/user')->assertOk();
    }

    public function test_productor_solo_ve_su_informacion(): void
    {
        Sanctum::actingAs($this->productor());

        $estanques = $this->getJson('/api/estanque')->assertOk()->json('data');
        $this->assertSame([5], array_column($estanques, 'id'));
        $this->getJson('/api/estanque/1')->assertNotFound();

        $productores = $this->getJson('/api/producer')->assertOk()->json('data');
        $this->assertSame([3], array_column($productores, 'id'));
        $this->getJson('/api/getProducerUserId/1')->assertOk()->assertJsonCount(0, 'data');

        // Solo los registros que capturó él mismo (no tiene registros en su estanque)
        $registros = $this->getJson('/api/register')->assertOk()->json('data.registros');
        $this->assertEqualsCanonicalizing([2, 3, 8], array_column($registros, 'id'));

        // No puede capturar en un estanque ajeno
        $this->postJson('/api/register', ['estanque_id' => 1, 'variable_id' => 1, 'valor' => 20])
            ->assertStatus(422)->assertJsonValidationErrors(['estanque_id'], 'data');

        // El técnico ve todo
        Sanctum::actingAs($this->tecnico());
        $this->assertCount(5, $this->getJson('/api/estanque')->json('data'));
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

    public function test_captura_en_lote(): void
    {
        Sanctum::actingAs($this->productor());
        $antes = Register::count();

        $this->postJson('/api/register/lote', [
            'estanque_id' => 5,
            'valores' => [
                ['variable_id' => 1, 'valor' => 26.4],
                ['variable_id' => 2, 'valor' => 6.1],
                ['variable_id' => 3, 'valor' => 7.2],
                ['variable_id' => 4, 'valor' => 12],
            ],
        ])->assertCreated()->assertJsonCount(4, 'data');

        $this->assertSame($antes + 4, Register::count());
        $this->assertSame(4, Register::where('estanque_id', 5)->where('user_id', 3)->count());

        // Si un valor es inválido no se guarda ninguno
        $this->postJson('/api/register/lote', [
            'estanque_id' => 5,
            'valores' => [
                ['variable_id' => 1, 'valor' => 26.4],
                ['variable_id' => 1, 'valor' => 'x'],
            ],
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['valores.0.variable_id', 'valores.1.valor'], 'data');
        $this->assertSame($antes + 4, Register::count());
    }

    public function test_listado_de_registros_filtrado_y_paginado(): void
    {
        Sanctum::actingAs($this->tecnico());

        $data = $this->getJson('/api/register?estanque_id=3&per_page=2')->assertOk()->json('data');

        $this->assertCount(2, $data['registros']);
        $this->assertSame(4, $data['paginacion']['total']);
        $this->assertSame(2, $data['paginacion']['ultima_pagina']);
        $this->assertSame(3, $data['registros'][0]['estanque_id']);
        $this->assertArrayHasKey('fecha', $data['registros'][0]);

        $hoy = now()->format('Y-m-d');
        $this->getJson("/api/register?desde={$hoy}&hasta={$hoy}")
            ->assertOk()->assertJsonPath('data.paginacion.total', 9);
        $this->getJson('/api/register?desde=2000-01-01&hasta=2000-01-02')
            ->assertOk()->assertJsonPath('data.paginacion.total', 0);
    }

    public function test_estadisticas_por_variable(): void
    {
        Sanctum::actingAs($this->tecnico());

        $data = $this->getJson('/api/estadisticas?estanque_id=1')->assertOk()->json('data');

        $this->assertCount(4, $data);
        $temperatura = $data[0];
        $this->assertSame('Temperatura', $temperatura['nombre']);
        $this->assertSame(1, $temperatura['total']);
        $this->assertEquals(20.1, $temperatura['ultimo_valor']);
        $this->assertSame(0, $data[3]['total']);
        $this->assertNull($data[3]['promedio']);
    }

    public function test_solo_el_autor_o_un_admin_modifican_un_registro(): void
    {
        // El registro 2 fue capturado por el usuario 3; el técnico lo ve pero no es suyo
        Sanctum::actingAs($this->tecnico());
        $this->deleteJson('/api/register/2')->assertForbidden();

        // El registro 1 sí es del técnico
        $this->putJson('/api/register/1', [
            'estanque_id' => 1,
            'variable_id' => 1,
            'valor' => 19,
        ])->assertOk();

        Sanctum::actingAs($this->admin());
        $this->deleteJson('/api/register/2')
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

    public function test_editar_usuario_sin_password_conserva_la_actual(): void
    {
        Sanctum::actingAs($this->admin());

        $this->putJson('/api/user/2', [
            'rfc' => 'AAAA770810411',
            'name' => 'Nombre editado',
            'email' => 'maria@hotmail.com',
            'password' => '',
        ])->assertOk();

        $usuario = User::find(2);
        $this->assertSame('Nombre editado', $usuario->name);
        $this->assertTrue(Hash::check('1234', $usuario->password));
    }

    public function test_no_se_asigna_el_mismo_rol_dos_veces(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/roleuser', ['role_id' => 3, 'user_id' => 3])
            ->assertStatus(422)->assertJsonValidationErrors(['user_id'], 'data');
        $this->postJson('/api/roleuser', ['role_id' => 9, 'user_id' => 3])
            ->assertStatus(422)->assertJsonValidationErrors(['role_id'], 'data');
        $this->postJson('/api/roleuser', ['role_id' => 1, 'user_id' => 3])->assertCreated();
    }
}
