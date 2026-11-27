<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\ProductoTalla;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Semana 15: boleta PDF con dompdf, mejoras de navegación y fixes de estabilidad de la auditoría final.
class BoletaYNavegacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@kixa.com')->first();
        $this->cliente = User::where('email', 'cliente@kixa.com')->first();
    }

    private function crearPedido(int $cantidad = 1, string $codigo = 'PED-BOL001'): Pedido
    {
        $talla = ProductoTalla::where('stock', '>', 0)->first();

        $pedido = Pedido::create([
            'user_id' => $this->cliente->id, 'codigo_orden' => $codigo, 'subtotal' => 84.75,
            'igv' => 15.25, 'total' => 100, 'estado' => 'Pagado', 'direccion_envio' => 'Av. Lima 123',
            'metodo_pago' => 'Tarjeta', 'tarjeta_ultimos4' => '4242',
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id, 'producto_id' => $talla->producto_id, 'talla' => 40.5,
            'cantidad' => $cantidad, 'precio_unitario' => 100 / $cantidad, 'subtotal' => 100,
        ]);

        return $pedido;
    }

    // ---------- Boleta PDF ----------

    public function test_cliente_dueno_descarga_su_boleta_pdf(): void
    {
        $pedido = $this->crearPedido();

        $respuesta = $this->actingAs($this->cliente)->get(route('pedidos.boleta', $pedido));

        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Boleta-PED-BOL001.pdf', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_admin_descarga_la_boleta_de_cualquier_cliente(): void
    {
        $pedido = $this->crearPedido();

        $this->actingAs($this->admin)->get(route('pedidos.boleta', $pedido))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_otro_cliente_recibe_403_al_pedir_una_boleta_ajena(): void
    {
        $pedido = $this->crearPedido();
        $intruso = User::factory()->create();

        $this->actingAs($intruso)->get(route('pedidos.boleta', $pedido))->assertForbidden();
    }

    public function test_invitado_es_enviado_al_login_y_la_url_usa_codigo_orden(): void
    {
        $pedido = $this->crearPedido();

        $this->assertStringEndsWith('/pedidos/PED-BOL001/boleta', route('pedidos.boleta', $pedido));
        $this->get(route('pedidos.boleta', $pedido))->assertRedirect(route('login'));
    }

    public function test_boleta_renderiza_desglose_talla_y_metodo_de_pago(): void
    {
        $pedido = $this->crearPedido()->load(['usuario', 'items.producto']);

        $html = view('comprobantes.boleta', [
            'pedido' => $pedido, 'empresa' => ['nombre' => 'KixaStore', 'direccion' => 'Lima', 'web' => 'kixa'], 'logo' => '',
        ])->render();

        $this->assertStringContainsString('PED-BOL001', $html);
        $this->assertStringContainsString('Op. Gravada', $html);
        $this->assertStringContainsString('S/ 84.75', $html);
        $this->assertStringContainsString('IGV (18%)', $html);
        $this->assertStringContainsString('S/ 15.25', $html);
        $this->assertStringContainsString('40.5', $html);
        $this->assertStringContainsString('Tarjeta •••• 4242', $html);
        $this->assertStringContainsString('Cliente Kixa', $html);
    }

    public function test_ver_boleta_y_descargar_aparecen_en_perfil_y_en_admin_pedidos(): void
    {
        $pedido = $this->crearPedido();
        $vistaPrevia = 'href="' . route('pedidos.boleta', $pedido) . '" target="_blank"';
        $descarga = route('pedidos.boleta', ['pedido' => $pedido, 'descargar' => 1]);

        foreach ([[$this->cliente, route('perfil', 'pedidos')], [$this->admin, route('admin.pedidos.index')]] as [$usuario, $pagina]) {
            $this->actingAs($usuario)->get($pagina)
                ->assertOk()
                ->assertSee('Ver boleta')
                ->assertSee($vistaPrevia, false)
                ->assertSee('Descargar')
                ->assertSee($descarga, false)
                ->assertDontSee('Descargar Boleta PDF');
        }
    }

    // ---------- Navegación ----------

    public function test_barra_superior_muestra_panel_admin_solo_al_administrador(): void
    {
        $this->actingAs($this->admin)->get(route('home'))
            ->assertOk()->assertSee('Panel Admin')->assertSee(route('admin.home'), false);

        $this->actingAs($this->cliente)->get(route('home'))
            ->assertOk()->assertDontSee('Panel Admin')->assertSee('Cliente Kixa');
    }

    public function test_nombre_corto_toma_primer_nombre_y_primer_apellido(): void
    {
        $this->assertSame('Ana López', User::factory()->make(['name' => 'Ana María López García'])->nombre_corto);
        $this->assertSame('Juan Pérez', User::factory()->make(['name' => '  Juan   Pérez García '])->nombre_corto);
        $this->assertSame('Rosa', User::factory()->make(['name' => 'Rosa'])->nombre_corto);

        $cliente = User::factory()->create(['name' => 'Lucía Fernanda Quispe Mamani']);
        $this->actingAs($cliente)->get(route('home'))
            ->assertSee('<i class="fa-solid fa-user"></i> Lucía Quispe</a>', false);
    }

    public function test_menu_lateral_del_admin_dice_principal(): void
    {
        $this->actingAs($this->admin)->get(route('admin.home'))
            ->assertOk()->assertSee('Principal');
    }

    // ---------- Fixes de estabilidad ----------

    public function test_catalogo_devuelve_404_si_la_categoria_no_existe_o_esta_inactiva(): void
    {
        Categoria::create(['nombre' => 'Retro', 'slug' => 'retro', 'activo' => false]);

        $this->get('/catalogo/no-existe')->assertNotFound();
        $this->get('/catalogo/retro')->assertNotFound();
        $this->get('/catalogo/todos')->assertOk();
        $this->get('/catalogo')->assertOk();
        $this->get('/catalogo/hombre')->assertOk();
    }

    public function test_nombre_de_marca_no_supera_50_caracteres(): void
    {
        $this->actingAs($this->admin)->post(route('admin.marcas.store'), [
            'nombre' => str_repeat('a', 51), 'activo' => 1,
        ])->assertSessionHasErrors('nombre');

        $this->actingAs($this->admin)->post(route('admin.marcas.store'), [
            'nombre' => str_repeat('b', 50), 'activo' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('marcas', ['nombre' => str_repeat('b', 50)]);
    }

    public function test_formulario_crear_marca_siempre_incluye_la_vista_previa(): void
    {
        $this->actingAs($this->admin)->get(route('admin.marcas.create'))
            ->assertOk()->assertSee('id="vista-imagen"', false);
    }

    public function test_admin_pedidos_usa_singular_y_plural_de_zapatillas(): void
    {
        $this->crearPedido(1, 'PED-UNO001');

        $this->actingAs($this->admin)->get(route('admin.pedidos.index'))
            ->assertOk()->assertSee('1 zapatilla ·')->assertDontSee('1 zapatillas');

        $this->crearPedido(2, 'PED-DOS002');

        $this->actingAs($this->admin)->get(route('admin.pedidos.index'))
            ->assertSee('2 zapatillas ·');
    }
}
