<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_admin_redirige_a_admin_home(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@kixa.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.home'));
        $this->assertAuthenticated();
    }

    public function test_login_cliente_redirige_a_home(): void
    {
        $response = $this->post('/login', [
            'email'    => 'cliente@kixa.com',
            'password' => 'cliente123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_buscador_general_retorna_productos_jordan(): void
    {
        $response = $this->get('/catalogo?buscar=Jordan');

        $response->assertStatus(200);
        $response->assertSee('Jordan Retro 4 Black Cat');
        $response->assertSee('Jordan 1 Mid Sky Blue Kids');
    }

    public function test_perfil_requiere_autenticacion(): void
    {
        $guestResponse = $this->get('/perfil');
        $guestResponse->assertRedirect('/login');

        $user = User::where('email', 'cliente@kixa.com')->first();
        $authResponse = $this->actingAs($user)->get('/perfil');
        $authResponse->assertStatus(200);
        $authResponse->assertSee($user->name);
    }

    public function test_admin_middleware_bloquea_clientes_y_permite_admins(): void
    {
        // Invitado
        $guest = $this->get('/admin');
        $guest->assertRedirect('/login');

        // Cliente
        $cliente = User::where('email', 'cliente@kixa.com')->first();
        $clienteResp = $this->actingAs($cliente)->get('/admin');
        $clienteResp->assertRedirect(route('home'));

        // Admin
        $admin = User::where('email', 'admin@kixa.com')->first();
        $adminResp = $this->actingAs($admin)->get('/admin');
        $adminResp->assertStatus(200);
        $adminResp->assertSee('Resumen General');
        $adminResp->assertSee('Total Productos');
    }

    public function test_checkout_procesar_crea_pedido_y_descuenta_stock(): void
    {
        $cliente = User::where('email', 'cliente@kixa.com')->first();
        $producto = \App\Models\Producto::first();
        $tallaObj = \App\Models\ProductoTalla::where('producto_id', $producto->id)->where('stock', '>', 2)->first();

        $stockInicial = $tallaObj->stock;

        $response = $this->actingAs($cliente)->postJson('/checkout/procesar', [
            'metodo_pago' => 'Contraentrega',
            'direccion_envio' => 'Av. Javier Prado 1234, Lima',
            'items' => [
                [
                    'id' => $producto->id,
                    'talla' => $tallaObj->talla,
                    'cantidad' => 1,
                    'precio' => $producto->precio,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'exito' => true,
        ]);

        $this->assertDatabaseHas('pedidos', [
            'user_id' => $cliente->id,
            'metodo_pago' => 'Contraentrega',
        ]);

        $tallaObj->refresh();
        $this->assertEquals($stockInicial - 1, $tallaObj->stock);
    }

    public function test_flujo_completo_cliente_desde_catalogo_hasta_historial_en_perfil(): void
    {
        // 1. Invitado no puede acceder directo a checkout
        $guestCheckout = $this->get('/checkout');
        $guestCheckout->assertRedirect(route('login'));

        // 2. Cliente se autentica
        $cliente = User::where('email', 'cliente@kixa.com')->first();
        $this->actingAs($cliente);

        // 3. Navega por el catálogo de Mujer
        $catalogoResp = $this->get('/catalogo/mujer');
        $catalogoResp->assertStatus(200);
        $catalogoResp->assertSee('Puma-180 PRM');
        $catalogoResp->assertSee('KIXA WOMEN');

        // 4. Entra a la ficha de detalle del producto
        $detalleResp = $this->get('/producto/PUM-180-PRM');
        $detalleResp->assertStatus(200);
        $detalleResp->assertSee('Puma-180 PRM');
        $detalleResp->assertSee('PUMA');

        // 5. Visita la sección de Favoritos
        $favResp = $this->get('/favoritos');
        $favResp->assertStatus(200);
        $favResp->assertSee('MI LISTA DE DESEOS');

        // 6. Visita el carrito y checkout
        $carritoResp = $this->get('/carrito');
        $carritoResp->assertStatus(200);

        $checkoutResp = $this->get('/checkout');
        $checkoutResp->assertStatus(200);

        // 7. Confirma el pago en checkout.procesar
        $producto = \App\Models\Producto::where('sku', 'PUM-180-PRM')->first();
        $compraResp = $this->postJson('/checkout/procesar', [
            'metodo_pago' => 'Yape',
            'direccion_envio' => 'Calle Los Pinos 456, San Isidro',
            'items' => [
                [
                    'id' => $producto->id,
                    'sku' => $producto->sku,
                    'talla' => 39.0,
                    'cantidad' => 1,
                    'precio' => $producto->precio,
                ]
            ]
        ]);

        $compraResp->assertStatus(200);
        $codigo = $compraResp->json('codigo_orden');
        $this->assertNotNull($codigo);

        // 8. Visita /perfil y verifica que el pedido aparece en su historial
        $perfilResp = $this->get('/perfil');
        $perfilResp->assertStatus(200);
        $perfilResp->assertSee($codigo);
        $perfilResp->assertSee('Pagado');
        $this->assertDatabaseHas('pedidos', [
            'codigo_orden' => $codigo,
            'user_id' => $cliente->id,
            'estado' => 'Pagado',
            'metodo_pago' => 'Yape',
        ]);
    }

    public function test_validacion_contrasenia_minimo_8_caracteres_en_registro_y_perfil(): void
    {
        // 1. Registro con contraseña menor a 8 caracteres debe fallar
        $badRegistro = $this->post('/registro', [
            'name'                  => 'Usuario Prueba',
            'email'                 => 'prueba@kixa.com',
            'password'              => '1234567', // 7 caracteres
            'password_confirmation' => '1234567',
        ]);
        $badRegistro->assertSessionHasErrors(['password']);

        // 2. Registro con 8 o más caracteres debe ser exitoso
        $goodRegistro = $this->post('/registro', [
            'name'                  => 'Usuario Valido',
            'email'                 => 'valido@kixa.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'dni'                   => '72345678',
            'phone'                 => '987654321',
        ]);
        $goodRegistro->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', ['email' => 'valido@kixa.com']);

        // 3. Actualizar perfil con contraseña menor a 8 caracteres debe fallar
        $user = User::where('email', 'valido@kixa.com')->first();
        $badPerfil = $this->actingAs($user)->put('/perfil', [
            'name'     => 'Usuario Valido Mod',
            'email'    => 'valido@kixa.com',
            'password' => 'corta', // 5 caracteres
        ]);
        $badPerfil->assertSessionHasErrors(['password']);

        // 4. Actualizar perfil con contraseña de 8 o más caracteres debe ser exitoso
        $goodPerfil = $this->actingAs($user)->put('/perfil', [
            'name'     => 'Usuario Valido Mod',
            'email'    => 'valido@kixa.com',
            'password' => 'nuevaPassword123',
        ]);
        $goodPerfil->assertSessionHasNoErrors();
    }
}
