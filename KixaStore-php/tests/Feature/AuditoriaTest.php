<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Marca;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Tests de los 8 puntos de la auditoría técnica (medias tallas, perfil, estados de pedido, slugs, checkout y buscador).
class AuditoriaTest extends TestCase
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

    // ---------- Punto 2: ficha del producto ----------

    public function test_ficha_muestra_medias_tallas_ordenadas_y_usa_sku_en_data_id(): void
    {
        $producto = ProductoTalla::where('talla', 40)->first()->producto;
        // Se crea al final (id mayor): solo aparece entre 40 y 41 si la consulta ordena por talla.
        ProductoTalla::create(['producto_id' => $producto->id, 'talla' => 40.5, 'stock' => 3]);

        $this->get('/producto/' . $producto->sku)
            ->assertOk()
            ->assertSeeInOrder(['data-talla="40"', 'data-talla="40.5"', 'data-talla="41"'], false)
            ->assertSee('data-id="' . $producto->sku . '"', false)
            ->assertDontSee('data-id="' . $producto->id . '"', false)
            ->assertSee($producto->url_imagen, false);
    }

    // ---------- Punto 3: el perfil no cambia la contraseña ----------

    public function test_put_perfil_ignora_el_campo_password(): void
    {
        $hashOriginal = $this->cliente->password;

        $this->actingAs($this->cliente)->put(route('perfil.update'), [
            'name' => 'Cliente Editado',
            'email' => $this->cliente->email,
            'password' => 'claveInyectada123',
        ])->assertSessionHasNoErrors();

        $this->cliente->refresh();
        $this->assertSame('Cliente Editado', $this->cliente->name);
        $this->assertSame($hashOriginal, $this->cliente->password);
        $this->assertFalse(Hash::check('claveInyectada123', $this->cliente->password));
    }

    public function test_cambio_de_password_exige_la_contrasenia_actual(): void
    {
        $this->actingAs($this->cliente)->put(route('perfil.password'), [
            'password_actual' => 'incorrecta',
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ])->assertSessionHasErrors('password_actual');
        $this->assertTrue(Hash::check('cliente123', $this->cliente->fresh()->password));

        $this->actingAs($this->cliente)->put(route('perfil.password'), [
            'password_actual' => 'cliente123',
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('nuevaClave123', $this->cliente->fresh()->password));
    }

    // ---------- Punto 4: máquina de estados y stock al cancelar ----------

    private function crearPedido(string $estado, ProductoTalla $talla, int $cantidad = 2): Pedido
    {
        $pedido = Pedido::create([
            'user_id' => $this->cliente->id, 'codigo_orden' => 'KX-AUD-' . $estado, 'subtotal' => 100,
            'igv' => 18, 'total' => 118, 'estado' => $estado, 'direccion_envio' => 'Lima', 'metodo_pago' => 'Contraentrega',
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id, 'producto_id' => $talla->producto_id, 'talla' => $talla->talla,
            'cantidad' => $cantidad, 'precio_unitario' => 50, 'subtotal' => 50 * $cantidad,
        ]);

        return $pedido;
    }

    public function test_cancelar_pedido_devuelve_el_stock_una_sola_vez(): void
    {
        $talla = ProductoTalla::where('stock', '>', 5)->first();
        $stockInicial = $talla->stock;
        $pedido = $this->crearPedido('Pagado', $talla, 3);

        $this->actingAs($this->admin)->patch(route('admin.pedidos.estado', $pedido), ['estado' => 'Cancelado'])
            ->assertSessionHas('exito');
        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertSame($stockInicial + 3, $talla->fresh()->stock);

        // Cancelado es final: no vuelve a Pendiente ni suma stock otra vez.
        $this->actingAs($this->admin)->patch(route('admin.pedidos.estado', $pedido), ['estado' => 'Pendiente'])
            ->assertSessionHas('error');
        $this->actingAs($this->admin)->patch(route('admin.pedidos.estado', $pedido), ['estado' => 'Cancelado']);
        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertSame($stockInicial + 3, $talla->fresh()->stock);
    }

    public function test_transiciones_invalidas_de_pedido_son_rechazadas(): void
    {
        $talla = ProductoTalla::first();
        $casos = [
            ['Pagado', 'Pendiente'],
            ['Enviado', 'Cancelado'],
            ['Entregado', 'Enviado'],
        ];

        foreach ($casos as [$desde, $hacia]) {
            $pedido = $this->crearPedido($desde, $talla);

            $this->actingAs($this->admin)->patch(route('admin.pedidos.estado', $pedido), ['estado' => $hacia])
                ->assertSessionHas('error');
            $this->assertSame($desde, $pedido->fresh()->estado);
        }

        // Una transición válida sí pasa: Enviado -> Entregado.
        $enviado = Pedido::where('estado', 'Enviado')->first();
        $this->actingAs($this->admin)->patch(route('admin.pedidos.estado', $enviado), ['estado' => 'Entregado'])
            ->assertSessionHas('exito');
        $this->assertSame('Entregado', $enviado->fresh()->estado);
    }

    // ---------- Punto 5: slugs y validaciones de productos ----------

    public function test_categoria_rechaza_slug_vacio_largo_o_reservado(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => '!!!'])
            ->assertSessionHasErrors('slug');
        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => 'Todos'])
            ->assertSessionHasErrors('slug');
        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => 'Valida', 'slug' => str_repeat('a', 71)])
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, Categoria::whereIn('slug', ['', 'todos'])->count());
    }

    public function test_marca_rechaza_nombre_sin_letras_ni_numeros(): void
    {
        $total = Marca::count();

        $this->actingAs($this->admin)->post(route('admin.marcas.store'), ['nombre' => '¡¡¡***!!!'])
            ->assertSessionHasErrors('nombre');

        $this->assertSame($total, Marca::count());
    }

    public function test_producto_valida_precio_tallas_repetidas_y_stock_maximo(): void
    {
        $base = [
            'categoria_id' => Categoria::where('slug', 'hombre')->value('id'),
            'marca_id' => Marca::where('nombre', 'Nike')->value('id'),
            'nombre' => 'Nike Validacion',
            'precio' => 199.90,
            'tallas' => [['talla' => 40, 'stock' => 5]],
        ];
        $post = fn (array $cambios) => $this->actingAs($this->admin)->post(route('admin.productos.store'), array_merge($base, $cambios));

        $post(['precio' => 0])->assertSessionHasErrors('precio');
        $post(['precio' => 100000])->assertSessionHasErrors('precio');
        $post(['tallas' => []])->assertSessionHasErrors('tallas');
        $post(['tallas' => [['talla' => '', 'stock' => 3]]])->assertSessionHasErrors('tallas');
        $post(['tallas' => [['talla' => 40, 'stock' => 1], ['talla' => 40, 'stock' => 2]]])->assertSessionHasErrors('tallas.1.talla');
        $post(['tallas' => [['talla' => 40, 'stock' => 100001]]])->assertSessionHasErrors('tallas.0.stock');
        $this->assertFalse(Producto::where('nombre', 'Nike Validacion')->exists());

        $post(['tallas' => [['talla' => 40, 'stock' => 100000], ['talla' => 40.5, 'stock' => 1]]])->assertSessionHasNoErrors();
        $this->assertCount(2, Producto::where('nombre', 'Nike Validacion')->first()->tallas);
    }

    // ---------- Punto 6: zona horaria ----------

    public function test_zona_horaria_es_lima(): void
    {
        $this->assertSame('America/Lima', config('app.timezone'));
        $this->assertSame('America/Lima', now()->timezoneName);
    }

    // ---------- Punto 7: checkout y buscador ----------

    public function test_checkout_limita_10_pares_por_talla_sumando_lineas(): void
    {
        $talla = ProductoTalla::where('stock', '>', 0)->first();
        $talla->update(['stock' => 50]);
        $producto = $talla->producto;

        $this->actingAs($this->cliente)->get('/checkout')->assertOk();
        $linea = ['id' => $producto->sku, 'talla' => $talla->talla, 'cantidad' => 6];

        $this->actingAs($this->cliente)->postJson('/checkout/procesar', [
            'token_compra' => session('checkout_token'),
            'metodo_pago' => 'Contraentrega',
            'items' => [$linea, $linea],
        ])->assertStatus(422)->assertJson(['exito' => false]);

        $this->assertSame(50, $talla->fresh()->stock);
        $this->assertSame(0, Pedido::count());
    }

    public function test_error_500_del_checkout_no_expone_detalles_tecnicos(): void
    {
        $talla = ProductoTalla::where('stock', '>', 2)->first();
        Pedido::creating(fn () => throw new \RuntimeException('SQLSTATE[HY000] detalle secreto'));

        $this->actingAs($this->cliente)->get('/checkout')->assertOk();

        $this->actingAs($this->cliente)->postJson('/checkout/procesar', [
            'token_compra' => session('checkout_token'),
            'metodo_pago' => 'Contraentrega',
            'items' => [['id' => $talla->producto->sku, 'talla' => $talla->talla, 'cantidad' => 1]],
        ])->assertStatus(500)
            ->assertJson(['exito' => false])
            ->assertDontSee('SQLSTATE')
            ->assertDontSee('detalle secreto');
    }

    public function test_buscador_escapa_comodines_y_limita_palabras(): void
    {
        // % y _ ya no funcionan como comodines de LIKE (antes devolvían todo el catálogo).
        $this->assertCount(0, $this->get('/catalogo?buscar=%25')->assertOk()->viewData('productos'));
        $this->assertCount(0, $this->get('/catalogo?buscar=_')->assertOk()->viewData('productos'));

        // Solo se usan las 6 primeras palabras: la séptima ("Inexistente") se descarta.
        $respuesta = $this->get('/catalogo?buscar=' . urlencode('Jordan Jordan Jordan Jordan Jordan Jordan Inexistente'))->assertOk();
        $this->assertSame('Jordan Jordan Jordan Jordan Jordan Jordan', $respuesta->viewData('buscar'));
        $respuesta->assertSee('Jordan Retro 4 Black Cat');

        $largo = $this->get('/catalogo?buscar=' . str_repeat('x', 200))->assertOk();
        $this->assertSame(80, mb_strlen($largo->viewData('buscar')));
    }

    // ---------- Punto 8: FAQ coherente con el checkout ----------

    public function test_faq_de_nosotros_menciona_solo_los_metodos_de_pago_reales(): void
    {
        $this->get(route('nosotros'))
            ->assertOk()
            ->assertSee('Visa')
            ->assertSee('Mastercard')
            ->assertSee('Contraentrega')
            ->assertDontSee('Yape')
            ->assertDontSee('Plin')
            ->assertDontSee('transferencia bancaria')
            // Regla real de envío: gratis en la primera compra o desde S/ 200; si no, S/ 15.
            ->assertSee('El envío es gratis en tu primera compra')
            ->assertSee('S/ 200.00')
            ->assertSee('S/ 15.00')
            ->assertDontSee('El envío es gratis en todas tus compras');
    }
}
