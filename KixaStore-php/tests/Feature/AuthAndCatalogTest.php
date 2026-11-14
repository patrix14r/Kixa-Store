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

        // [Actualizado: Blindaje] El checkout entrega un token de un solo uso
        $this->actingAs($cliente)->get('/checkout')->assertOk();

        $response = $this->actingAs($cliente)->postJson('/checkout/procesar', [
            'token_compra' => session('checkout_token'),
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
        $guestCheckout->assertRedirect(route('login', ['redirect' => 'checkout'])); // [Actualizado: Login → Checkout]

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
        $favResp->assertSee('Favoritos'); // [Actualizado: Favoritos estilo Nike]

        // 6. Visita el carrito y checkout
        $carritoResp = $this->get('/carrito');
        $carritoResp->assertStatus(200);

        $checkoutResp = $this->get('/checkout');
        $checkoutResp->assertStatus(200);

        // 7. Confirma el pago en checkout.procesar
        $producto = \App\Models\Producto::where('sku', 'PUM-180-PRM')->first();
        $compraResp = $this->postJson('/checkout/procesar', [
            'token_compra' => session('checkout_token'),
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
        $perfilResp = $this->get('/perfil/pedidos');
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

    // ---------- FASE 4: Nosotros, buscador y foto de perfil ----------

    public function test_pagina_nosotros_muestra_mision_vision_y_valores(): void
    {
        $this->get(route('nosotros'))
            ->assertOk()
            ->assertSee('Misión')
            ->assertSee('Visión')
            ->assertSee('NUESTROS VALORES')
            ->assertSee(route('nosotros'));
    }

    public function test_buscador_filtra_por_varias_palabras_de_marca_y_modelo(): void
    {
        $this->get(route('catalogo', ['buscar' => 'jordan black cat']))
            ->assertOk()
            ->assertSee('Jordan Retro 4 Black Cat')
            ->assertDontSee('Puma-180 PRM');
    }

    public function test_cliente_sube_foto_de_perfil_y_se_muestra_en_el_header(): void
    {
        $cliente = User::where('email', 'cliente@kixa.com')->first();

        $this->actingAs($cliente)->put(route('perfil.update'), [
            'name'   => $cliente->name,
            'email'  => $cliente->email,
            'avatar' => \Illuminate\Http\UploadedFile::fake()->image('yo.jpg', 200, 200),
        ])->assertSessionHasNoErrors();

        $avatar = $cliente->fresh()->avatar;
        $this->assertStringStartsWith('images/avatars/', $avatar);
        $this->assertFileExists(public_path($avatar));

        $this->actingAs($cliente->fresh())->get(route('home'))
            ->assertSee('class="avatar-header"', false)
            ->assertDontSee('PANEL ADMIN');

        @unlink(public_path($avatar));
    }

    public function test_foto_de_perfil_rechaza_archivos_que_no_son_imagen(): void
    {
        $cliente = User::where('email', 'cliente@kixa.com')->first();

        $this->actingAs($cliente)->put(route('perfil.update'), [
            'name'   => $cliente->name,
            'email'  => $cliente->email,
            'avatar' => \Illuminate\Http\UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('avatar');
    }

    // [Actualizado: Lo Destacado] El home lista los productos con destacado = true
    public function test_home_muestra_lo_destacado_y_marca_kixastore(): void
    {
        $destacado = \App\Models\Producto::activos()->destacados()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Lo Destacado')
            ->assertSee($destacado->nombre)
            ->assertSee('KixaStore')
            ->assertDontSee('Tienda Kixa');
    }

    // [Actualizado: Filtros estilo Nike] Sidebar con marcas reales, contador en el título y orden por precio
    public function test_catalogo_muestra_sidebar_de_filtros_y_ordena_por_precio(): void
    {
        $respuesta = $this->get(route('catalogo', ['categoria' => 'hombre', 'orden' => 'precio_asc']))->assertOk();
        $total = \App\Models\Producto::activos()->whereHas('categoria', fn ($c) => $c->where('slug', 'hombre'))->count();

        $respuesta->assertSeeText('Zapatillas Hombre')
            ->assertSee('Ocultar filtros')
            ->assertSee('Marca')
            ->assertSee('Talla');

        $precios = $respuesta->viewData('productos')->pluck('precio')->map(fn ($p) => (float) $p)->all();
        $ordenados = $precios;
        sort($ordenados);
        $this->assertSame($ordenados, $precios);
    }

    // [Actualizado: Blindaje] Reenviar la compra (botón Atrás / doble clic) no duplica el pedido
    public function test_procesar_rechaza_reenvio_y_cantidades_invalidas(): void
    {
        $cliente = User::where('email', 'cliente@kixa.com')->first();
        $talla = \App\Models\ProductoTalla::where('stock', '>', 2)->first();
        $item = ['id' => $talla->producto_id, 'talla' => $talla->talla, 'cantidad' => 1];

        $this->actingAs($cliente)->get('/checkout')->assertOk()->assertHeader('Pragma', 'no-cache');
        $token = session('checkout_token');

        // Cantidad negativa o decimal: 422 y no se crea pedido
        $this->postJson('/checkout/procesar', ['token_compra' => $token, 'metodo_pago' => 'Yape', 'items' => [array_merge($item, ['cantidad' => -2])]])
            ->assertStatus(422);
        $this->postJson('/checkout/procesar', ['token_compra' => $token, 'metodo_pago' => 'Yape', 'items' => [array_merge($item, ['cantidad' => 1.5])]])
            ->assertStatus(422);

        // Primera compra válida
        $this->postJson('/checkout/procesar', ['token_compra' => $token, 'metodo_pago' => 'Yape', 'items' => [$item]])
            ->assertOk()->assertJson(['redirect_url' => route('perfil', 'pedidos')]);

        // Reenvío con el mismo token: 409 y sigue habiendo 1 solo pedido
        $this->postJson('/checkout/procesar', ['token_compra' => $token, 'metodo_pago' => 'Yape', 'items' => [$item]])
            ->assertStatus(409);
        $this->assertSame(1, \App\Models\Pedido::where('user_id', $cliente->id)->count());
    }

    // [Actualizado: Login → Checkout] Desde el carrito, tras iniciar sesión se vuelve directo al pago
    public function test_login_con_redirect_checkout_lleva_al_checkout(): void
    {
        $this->get(route('login', ['redirect' => 'checkout']))
            ->assertOk()
            ->assertSee('name="redirect" value="checkout"', false);

        $this->post(route('login.post'), [
            'email' => 'cliente@kixa.com',
            'password' => 'cliente123',
            'redirect' => 'checkout',
        ])->assertRedirect(route('checkout'));
    }

    // Un valor de redirect desconocido no se usa (evita redirecciones abiertas)
    public function test_login_ignora_redirect_desconocido(): void
    {
        $this->post(route('login.post'), [
            'email' => 'cliente@kixa.com',
            'password' => 'cliente123',
            'redirect' => 'https://sitio-malicioso.test',
        ])->assertRedirect(route('home'));
    }

    // [Actualizado: Panel Admin] Listado de Clientes sin columna ID y rol por nombre
    public function test_admin_ve_listado_de_clientes(): void
    {
        $admin = User::where('email', 'admin@kixa.com')->first();

        $this->actingAs($admin)->get(route('admin.clientes.index'))
            ->assertOk()
            ->assertSee('cliente@kixa.com')
            ->assertSee('Pedidos')
            ->assertDontSee('<th class="kx-th">ID</th>', false);
    }

    // [Actualizado: SKU automático] Si el SKU llega vacío el controlador lo genera (MARCA-MODELO-XXX)
    public function test_producto_sin_sku_se_autogenera(): void
    {
        $admin = User::where('email', 'admin@kixa.com')->first();
        $categoria = \App\Models\Categoria::first();

        $this->actingAs($admin)->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'marca'        => 'Adidas',
            'nombre'       => 'Campus 00s',
            'precio'       => 399,
            'activo'       => 1,
            'tallas'       => [['talla' => 40, 'stock' => 5]],
        ])->assertRedirect(route('admin.productos.index'));

        $producto = \App\Models\Producto::where('nombre', 'Campus 00s')->first();
        $this->assertMatchesRegularExpression('/^ADI-CAM-[A-Z0-9]{3}$/', $producto->sku);
    }

    // [Actualizado: Clientes] Solo se listan usuarios con rol cliente
    public function test_clientes_excluye_administradores(): void
    {
        $admin = User::where('email', 'admin@kixa.com')->first();

        $this->actingAs($admin)->get(route('admin.clientes.index'))
            ->assertOk()
            ->assertDontSee('admin@kixa.com</td>', false);
    }

    // [Actualizado: Despacho] El admin cambia el estado y el cliente lo ve en su historial
    public function test_admin_cambia_estado_de_pedido_y_cliente_lo_ve(): void
    {
        $admin = User::where('email', 'admin@kixa.com')->first();
        $cliente = User::where('email', 'cliente@kixa.com')->first();
        $pedido = \App\Models\Pedido::create([
            'user_id' => $cliente->id, 'codigo_orden' => 'KX-TEST-01', 'subtotal' => 100,
            'igv' => 18, 'total' => 118, 'estado' => 'Pagado', 'direccion_envio' => 'Lima', 'metodo_pago' => 'tarjeta',
        ]);

        $this->actingAs($admin)->get(route('admin.pedidos.index'))->assertOk()->assertSee('KX-TEST-01');
        $this->actingAs($admin)->patch(route('admin.pedidos.estado', $pedido), ['estado' => 'Enviado'])
            ->assertSessionHas('exito');
        $this->assertSame('Enviado', $pedido->fresh()->estado);

        $this->actingAs($admin)->get(route('admin.perfil'))->assertOk()->assertSee('Mi Perfil');
        $this->actingAs($admin)->get(route('admin.clientes.show', $cliente))->assertOk()->assertSee('KX-TEST-01');
    }
}
