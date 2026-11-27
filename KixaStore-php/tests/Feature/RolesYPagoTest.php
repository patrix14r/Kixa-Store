<?php

namespace Tests\Feature;

use App\Http\Controllers\CarritoController;
use App\Models\Marca;
use App\Models\Pedido;
use App\Models\ProductoTalla;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Roles con Spatie Laravel Permission, simulador de pago con tarjeta (Luhn) y limpieza de imágenes.
class RolesYPagoTest extends TestCase
{
    use RefreshDatabase;

    private const TARJETA_VISA = '4242 4242 4242 4242';

    private User $admin;
    private User $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@kixa.com')->first();
        $this->cliente = User::where('email', 'cliente@kixa.com')->first();
    }

    // ---------- Roles con Spatie ----------

    public function test_roles_viven_en_tablas_de_spatie_y_users_ya_no_tiene_rol_id(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'rol_id'));
        $this->assertTrue(Schema::hasTable('model_has_roles'));
        $this->assertEqualsCanonicalizing(['admin', 'cliente'], Role::pluck('name')->all());

        $this->assertTrue($this->admin->hasRole('admin'));
        $this->assertTrue($this->admin->esAdmin());
        $this->assertTrue($this->cliente->hasRole('cliente'));
        $this->assertFalse($this->cliente->esAdmin());
        $this->assertDatabaseHas('model_has_roles', ['model_id' => $this->cliente->id, 'model_type' => User::class]);
    }

    public function test_scope_role_cuenta_solo_clientes_en_el_dashboard(): void
    {
        User::factory()->count(2)->create(); // la factory asigna el rol cliente

        // 3 clientes del seeder (cliente, junior, victor) + 2 de la factory; los 2 admins no cuentan como clientes.
        $this->assertSame(5, User::role('cliente')->count());
        $this->assertSame(2, User::role('admin')->count());

        $this->actingAs($this->admin)->get(route('admin.home'))
            ->assertOk()
            ->assertViewHas('totalClientes', 5)
            ->assertSee('admin'); // nombre del rol en el sidebar (getRoleNames)
    }

    public function test_seeder_crea_dos_admins_y_clientes_de_demostracion_que_pueden_ingresar(): void
    {
        $this->assertEqualsCanonicalizing(
            ['admin@kixa.com', 'chambergo@kixa.com'],
            User::role('admin')->pluck('email')->all()
        );
        $this->assertEqualsCanonicalizing(
            ['cliente@kixa.com', 'junior@kixa.com', 'victor@kixa.com'],
            User::role('cliente')->pluck('email')->all()
        );

        // El segundo admin entra al panel con su clave del seeder (aunque escriba el correo con mayúscula: Fortify lo pasa a minúsculas).
        $this->post('/login', ['email' => 'Chambergo@kixa.com', 'password' => 'admin123']);
        $this->assertAuthenticatedAs(User::where('email', 'chambergo@kixa.com')->first());
        $this->get(route('admin.home'))->assertOk();

        // El seeder es idempotente: volver a correrlo no duplica usuarios.
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->assertSame(5, User::count());
    }

    public function test_registro_con_fortify_asigna_rol_cliente(): void
    {
        $this->post('/register', [
            'name'                  => 'Nueva Clienta',
            'email'                 => 'nueva@kixa.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $usuario = User::where('email', 'nueva@kixa.com')->first();
        $this->assertTrue($usuario->hasRole('cliente'));
        $this->assertFalse($usuario->esAdmin());

        // AdminMiddleware lo sigue sacando del panel
        $this->actingAs($usuario)->get('/admin')->assertRedirect(route('home'));
    }

    public function test_cambiar_rol_con_spatie_da_o_quita_acceso_al_panel(): void
    {
        $this->actingAs($this->cliente)->get('/admin')->assertRedirect(route('home'));

        $this->cliente->syncRoles(['admin']);
        $this->actingAs($this->cliente->fresh())->get('/admin')->assertOk();
    }

    // ---------- Simulador de pago: Luhn, vencimiento y CVV ----------

    public function test_algoritmo_de_luhn(): void
    {
        $this->assertTrue(CarritoController::pasaLuhn('4242424242424242'));  // Visa de prueba
        $this->assertTrue(CarritoController::pasaLuhn('5555555555554444'));  // Mastercard de prueba
        $this->assertTrue(CarritoController::pasaLuhn('378282246310005'));   // Amex (15 dígitos)
        $this->assertFalse(CarritoController::pasaLuhn('4242424242424241'));
        $this->assertFalse(CarritoController::pasaLuhn('1234567812345678'));
        $this->assertFalse(CarritoController::pasaLuhn('4242abcd42424242'));
    }

    public function test_vencimiento_de_tarjeta(): void
    {
        $this->assertTrue(CarritoController::tarjetaVigente(now()->format('m/y'))); // vale hasta fin de este mes
        $this->assertTrue(CarritoController::tarjetaVigente(now()->addYears(2)->format('m/y')));
        $this->assertFalse(CarritoController::tarjetaVigente(now()->subMonthNoOverflow()->format('m/y')));
        $this->assertFalse(CarritoController::tarjetaVigente('13/30'));
        $this->assertFalse(CarritoController::tarjetaVigente('1/30'));
    }

    public function test_pago_con_tarjeta_valida_crea_pedido_pagado_y_guarda_solo_ultimos_4(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();
        $stockInicial = $talla->stock;

        $this->comprar($talla, $this->datosTarjeta())
            ->assertOk()->assertJson(['exito' => true]);

        $pedido = Pedido::where('user_id', $this->cliente->id)->first();
        $this->assertSame('Tarjeta', $pedido->metodo_pago);
        $this->assertSame('Pagado', $pedido->estado);
        $this->assertSame('4242', $pedido->tarjeta_ultimos4);
        $this->assertSame('Tarjeta •••• 4242', $pedido->metodo_pago_detalle);
        $this->assertSame($stockInicial - 1, $talla->fresh()->stock);

        // Ni el número completo ni el CVV quedan en la BD
        $fila = json_encode(\DB::table('pedidos')->where('id', $pedido->id)->first());
        $this->assertStringNotContainsString('4242424242424242', $fila);
        $this->assertStringNotContainsString('"123"', $fila);

        $this->actingAs($this->cliente)->get('/perfil/pedidos')->assertSee('Tarjeta •••• 4242');
    }

    public function test_tarjeta_que_no_pasa_luhn_es_rechazada(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_numero' => '4242 4242 4242 4241']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tarjeta_numero' => 'El número de tarjeta no es válido.']);

        $this->assertSame(0, Pedido::count());
    }

    public function test_tarjeta_vencida_cvv_invalido_o_sin_titular_son_rechazados(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_vencimiento' => now()->subMonthNoOverflow()->format('m/y')]))
            ->assertStatus(422)->assertJsonValidationErrors(['tarjeta_vencimiento' => 'La tarjeta está vencida.']);

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_vencimiento' => '2030-01']))
            ->assertStatus(422)->assertJsonValidationErrors('tarjeta_vencimiento');

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_cvv' => '12']))
            ->assertStatus(422)->assertJsonValidationErrors('tarjeta_cvv');

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_cvv' => '12345']))
            ->assertStatus(422)->assertJsonValidationErrors('tarjeta_cvv');

        $this->comprar($talla, $this->datosTarjeta(['tarjeta_titular' => '']))
            ->assertStatus(422)->assertJsonValidationErrors('tarjeta_titular');

        $this->assertSame(0, Pedido::count());
    }

    public function test_contraentrega_no_pide_tarjeta_y_queda_pendiente(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();

        $this->comprar($talla, ['metodo_pago' => 'Contraentrega'])->assertOk();

        $pedido = Pedido::first();
        $this->assertSame('Pendiente', $pedido->estado);
        $this->assertNull($pedido->tarjeta_ultimos4);
        $this->assertSame('Contraentrega', $pedido->metodo_pago_detalle);
    }

    public function test_yape_u_otros_metodos_ya_no_se_aceptan(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();

        $this->comprar($talla, ['metodo_pago' => 'Yape'])
            ->assertStatus(422)->assertJsonValidationErrors('metodo_pago');

        $this->assertSame(0, Pedido::count());
    }

    public function test_checkout_muestra_tarjeta_y_contraentrega_sin_yape(): void
    {
        $this->actingAs($this->cliente)->get(route('checkout'))
            ->assertOk()
            ->assertSee('Tarjeta de Crédito / Débito')
            ->assertSee('Contraentrega (Efectivo)')
            ->assertSee('id="procesandoPago"', false)
            ->assertDontSee('Yape');

        $this->assertFileDoesNotExist(public_path('images/payments/yape.jpg'));
    }

    // ---------- Imágenes, favicon y logos ----------

    public function test_favicon_en_ambos_layouts_y_archivos_muertos_eliminados(): void
    {
        $favicon = 'images/ui/favicon-32x32.png';

        $this->get(route('home'))->assertOk()->assertSee($favicon);
        $this->actingAs($this->admin)->get(route('admin.home'))->assertOk()->assertSee($favicon);
        $this->assertFileExists(public_path($favicon));

        $this->assertFileDoesNotExist(public_path('images/banners/fondo-contacto.jpg'));
        $this->assertDirectoryDoesNotExist(public_path('images/lanzamientos'));
    }

    public function test_seeder_asigna_logos_existentes_a_sus_marcas(): void
    {
        $esperados = [
            'Nike'   => 'images/brands/nike-logo.png',
            'Jordan' => 'images/brands/jordan-logo.webp',
            'Adidas' => 'images/brands/adidas-logo.png',
            'Puma'   => 'images/brands/puma-logo.png',
            'Umbro'  => 'images/brands/umbro-logo.png',
        ];

        foreach ($esperados as $nombre => $logo) {
            $this->assertSame($logo, Marca::where('nombre', $nombre)->value('logo'));
            $this->assertFileExists(public_path($logo));
        }

        $this->actingAs($this->admin)->get(route('admin.marcas.index'))
            ->assertOk()
            ->assertSee('bg-white/95', false)
            ->assertSee('images/brands/nike-logo.png');
    }

    // ---------- Ayudantes ----------

    private function datosTarjeta(array $extra = []): array
    {
        return array_merge([
            'metodo_pago'         => 'Tarjeta',
            'tarjeta_numero'      => self::TARJETA_VISA,
            'tarjeta_titular'     => 'Cliente Kixa',
            'tarjeta_vencimiento' => now()->addYear()->format('m/y'),
            'tarjeta_cvv'         => '123',
        ], $extra);
    }

    // Abre el checkout (genera el token de un solo uso) y envía la compra de 1 par.
    private function comprar(ProductoTalla $talla, array $datos)
    {
        $this->actingAs($this->cliente)->get(route('checkout'))->assertOk();

        return $this->postJson(route('checkout.procesar'), array_merge([
            'token_compra'    => session('checkout_token'),
            'direccion_envio' => 'Av. Los Frutales 123, Santa Anita',
            'items'           => [['id' => $talla->producto_id, 'talla' => $talla->talla, 'cantidad' => 1]],
        ], $datos));
    }
}
