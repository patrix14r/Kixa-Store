<?php

namespace Tests\Feature;

use App\Models\DetalleAbastecimiento;
use App\Models\OrdenAbastecimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Semana 15: boleta con vista previa, stock en vivo, regla real de envío gratis, catálogo por fetch y abastecimiento por lotes.
class MejorasRealismoTest extends TestCase
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

    // ---------- Ayudantes ----------

    private function pedidoPrevio(string $estado, float $envio = 0): Pedido
    {
        return Pedido::create([
            'user_id' => $this->cliente->id, 'codigo_orden' => 'PED-' . strtoupper(substr(md5($estado . microtime()), 0, 6)),
            'subtotal' => 84.75, 'igv' => 15.25, 'costo_envio' => $envio, 'total' => 100, 'estado' => $estado,
            'direccion_envio' => 'Av. Lima 123', 'metodo_pago' => 'Contraentrega',
        ]);
    }

    // Talla con stock suficiente de un producto cuyo precio cumple la condición (p. ej. '<', 200).
    private function tallaDeProducto(string $operador, float $precio): ProductoTalla
    {
        $producto = Producto::activos()->where('precio', $operador, $precio)->has('tallas')->firstOrFail();
        $talla = $producto->tallas()->first();
        $talla->update(['stock' => 20]);

        return $talla->fresh('producto');
    }

    private function comprar(ProductoTalla $talla, int $cantidad)
    {
        $this->actingAs($this->cliente)->get(route('checkout'))->assertOk();

        return $this->postJson(route('checkout.procesar'), [
            'token_compra'    => session('checkout_token'),
            'metodo_pago'     => 'Contraentrega',
            'direccion_envio' => 'Av. Los Frutales 123, Santa Anita',
            'items'           => [['id' => $talla->producto->sku, 'talla' => $talla->talla, 'cantidad' => $cantidad]],
        ]);
    }

    // ---------- 1. Boleta: vista previa (inline) y descarga (attachment) ----------

    public function test_boleta_se_abre_en_el_navegador_por_defecto_y_se_descarga_con_el_parametro(): void
    {
        $pedido = $this->pedidoPrevio('Pagado');

        $vista = $this->actingAs($this->cliente)->get(route('pedidos.boleta', $pedido));
        $vista->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline', $vista->headers->get('Content-Disposition'));
        $this->assertStringContainsString("Boleta-{$pedido->codigo_orden}.pdf", $vista->headers->get('Content-Disposition'));

        $descarga = $this->actingAs($this->cliente)->get(route('pedidos.boleta', ['pedido' => $pedido, 'descargar' => 1]));
        $descarga->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('attachment', $descarga->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $descarga->getContent());
    }

    public function test_boleta_muestra_el_costo_de_envio_o_gratis(): void
    {
        $datos = ['empresa' => ['nombre' => 'KixaStore', 'direccion' => 'Lima', 'web' => 'kixa'], 'logo' => ''];

        $conEnvio = $this->pedidoPrevio('Pagado', 15)->load(['usuario', 'items.producto']);
        $html = view('comprobantes.boleta', $datos + ['pedido' => $conEnvio])->render();
        $this->assertStringContainsString('Envío', $html);
        $this->assertStringContainsString('S/ 15.00', $html);

        $gratis = $this->pedidoPrevio('Pagado', 0)->load(['usuario', 'items.producto']);
        $this->assertStringContainsString('Gratis', view('comprobantes.boleta', $datos + ['pedido' => $gratis])->render());
    }

    // ---------- 2. Regla única de envío ----------

    public function test_regla_de_envio_primera_compra_o_desde_200_soles(): void
    {
        $this->assertSame(0.0, Pedido::calcularEnvio(50, true));
        $this->assertSame(0.0, Pedido::calcularEnvio(200, false));
        $this->assertSame(0.0, Pedido::calcularEnvio(350.90, false));
        $this->assertSame(15.0, Pedido::calcularEnvio(199.99, false));
        $this->assertSame(15.0, Pedido::calcularEnvio(0.01, false));
    }

    public function test_primera_compra_tiene_envio_gratis_aunque_sea_menor_a_200(): void
    {
        $talla = $this->tallaDeProducto('<', 200);
        $precio = (float) $talla->producto->precio;

        $this->comprar($talla, 1)->assertOk()->assertJson(['exito' => true, 'envio' => '0.00']);

        $pedido = Pedido::where('user_id', $this->cliente->id)->firstOrFail();
        $this->assertEquals(0, $pedido->costo_envio);
        $this->assertEquals(round($precio, 2), (float) $pedido->total);
    }

    public function test_desde_la_segunda_compra_menor_a_200_se_cobran_15_soles_dentro_del_total_con_igv(): void
    {
        $this->pedidoPrevio('Entregado');
        $talla = $this->tallaDeProducto('<', 185);
        $precio = (float) $talla->producto->precio;

        $this->comprar($talla, 1)->assertOk()->assertJson(['envio' => '15.00']);

        $pedido = Pedido::where('user_id', $this->cliente->id)->latest('id')->firstOrFail();
        $total = round($precio + 15, 2);
        $this->assertEquals(15, (float) $pedido->costo_envio);
        $this->assertEquals($total, (float) $pedido->total);
        $this->assertEquals(round($total * 0.18 / 1.18, 2), (float) $pedido->igv);
        $this->assertEquals(round($total - round($total * 0.18 / 1.18, 2), 2), (float) $pedido->subtotal);
    }

    public function test_compra_de_200_soles_o_mas_tiene_envio_gratis_aunque_no_sea_la_primera(): void
    {
        $this->pedidoPrevio('Entregado');
        $talla = $this->tallaDeProducto('<', 200);
        $cantidad = (int) ceil(200 / (float) $talla->producto->precio);

        $this->comprar($talla, $cantidad)->assertOk()->assertJson(['envio' => '0.00']);

        $pedido = Pedido::where('user_id', $this->cliente->id)->latest('id')->firstOrFail();
        $this->assertEquals(0, (float) $pedido->costo_envio);
        $this->assertGreaterThanOrEqual(200, (float) $pedido->total);
    }

    public function test_pedidos_cancelados_no_cuentan_para_la_primera_compra(): void
    {
        $this->pedidoPrevio('Cancelado');
        $this->assertTrue($this->cliente->esPrimeraCompra());

        $this->pedidoPrevio('Pendiente');
        $this->assertFalse($this->cliente->fresh()->esPrimeraCompra());
    }

    public function test_carrito_checkout_y_faq_reciben_la_misma_regla_de_envio(): void
    {
        $this->get(route('carrito'))->assertOk()
            ->assertSee('data-regla-envio=\'{"costo":15,"gratis_desde":200,"primera_compra":null}\'', false)
            ->assertSee('id="envio"', false);

        $this->actingAs($this->cliente)->get(route('checkout'))->assertOk()
            ->assertSee('"primera_compra":true', false)
            ->assertSee('id="envio"', false);

        $this->pedidoPrevio('Entregado');
        $this->actingAs($this->cliente)->get(route('carrito'))->assertOk()->assertSee('"primera_compra":false', false);

        $this->get(route('nosotros'))->assertOk()->assertSee(Pedido::textoReglaEnvio());
    }

    // ---------- 3. Stock real en vivo (/carrito/validar) y escasez ----------

    public function test_validar_carrito_devuelve_stock_precio_y_tallas_reales_sin_ids(): void
    {
        $producto = Producto::activos()->has('tallas', '>=', 2)->with(['tallas' => fn ($q) => $q->orderBy('talla')])->firstOrFail();
        [$agotada, $escasa] = $producto->tallas->take(2)->all();
        $agotada->update(['stock' => 0]);
        $escasa->update(['stock' => 2]);

        $inactivo = Producto::activos()->where('id', '!=', $producto->id)->firstOrFail();
        $inactivo->update(['activo' => false]);

        $respuesta = $this->postJson(route('carrito.validar'), ['items' => [
            ['id' => $producto->sku], ['id' => $inactivo->sku], ['id' => 'NO-EXISTE-1'],
        ]]);

        $respuesta->assertOk()
            ->assertJsonPath('max_por_talla', 10)
            ->assertJsonPath('ultimas_unidades', ProductoTalla::ULTIMAS_UNIDADES)
            ->assertJsonPath("productos.{$producto->sku}.disponible", true)
            ->assertJsonPath("productos.{$producto->sku}.precio", fn ($precio) => (float) $precio === (float) $producto->precio)
            ->assertJsonPath("productos.{$producto->sku}.tallas.0", ['talla' => $agotada->talla_etiqueta, 'stock' => 0])
            ->assertJsonPath("productos.{$producto->sku}.tallas.1", ['talla' => $escasa->talla_etiqueta, 'stock' => 2])
            ->assertJsonPath("productos.{$inactivo->sku}.disponible", false)
            ->assertJsonPath('productos.NO-EXISTE-1.disponible', false);

        // Cero IDs expuestos: la respuesta identifica todo por SKU y talla.
        $this->assertStringNotContainsString('"id"', $respuesta->getContent());
    }

    public function test_validar_carrito_rechaza_carritos_enormes_o_ids_manipulados(): void
    {
        $this->postJson(route('carrito.validar'), ['items' => array_fill(0, 31, ['id' => 'NIK-AIR-001'])])
            ->assertStatus(422)->assertJsonValidationErrors('items');

        $this->postJson(route('carrito.validar'), ['items' => [['id' => "x' OR 1=1 --"]]])
            ->assertStatus(422)->assertJsonValidationErrors('items.0.id');

        $this->postJson(route('carrito.validar'), ['items' => []])->assertOk()->assertJsonPath('productos', []);
    }

    public function test_ficha_de_producto_trae_stock_por_talla_para_el_aviso_de_escasez(): void
    {
        $talla = ProductoTalla::with('producto')->whereHas('producto', fn ($q) => $q->where('activo', true))->firstOrFail();
        $talla->update(['stock' => 2]);

        $this->get(route('producto.detalle', $talla->producto->sku))->assertOk()
            ->assertSee('data-ultimas-unidades="' . ProductoTalla::ULTIMAS_UNIDADES . '"', false)
            ->assertSee('data-talla="' . $talla->talla_etiqueta . '"' . "\n" . '                                data-stock="2"', false)
            ->assertSee('id="aviso-escasez"', false)
            ->assertDontSee('Envío gratis en todas tus compras');
    }

    // ---------- 4. Catálogo por fetch, paginación y vistas ----------

    public function test_catalogo_por_fetch_devuelve_solo_el_fragmento_de_la_grilla(): void
    {
        $producto = Producto::activos()->whereHas('categoria', fn ($q) => $q->where('slug', 'hombre'))->orderByDesc('destacado')->orderBy('nombre')->firstOrFail();

        $fragmento = $this->get('/catalogo/hombre', ['X-Requested-With' => 'XMLHttpRequest']);
        $fragmento->assertOk()
            ->assertSee('kx-grid', false)
            ->assertSee('data-titulo-pagina="Zapatillas Hombre | KixaStore"', false)
            ->assertSee($producto->nombre)
            ->assertDontSee('<html', false)
            ->assertDontSee('KIXA MEN')
            ->assertDontSee('id="navbar-principal"', false);
        $this->assertStringContainsString('X-Requested-With', $fragmento->headers->get('Vary'));

        // Sin la cabecera llega la página completa con banner y layout.
        $this->get('/catalogo/hombre')->assertOk()->assertSee('<html', false)->assertSee('KIXA MEN')->assertSee('kx-grid', false);
    }

    public function test_catalogo_pagina_de_12_en_12_y_los_filtros_vuelven_a_la_pagina_1(): void
    {
        $total = Producto::activos()->whereHas('categoria', fn ($c) => $c->where('activo', true))->count();
        $this->assertGreaterThan(12, $total);

        $pagina1 = $this->get('/catalogo/todos')->assertOk();
        $this->assertCount(12, $pagina1->viewData('productos'));
        $this->assertSame($total, $pagina1->viewData('productos')->total());
        $pagina1->assertSee('aria-label="Paginación del catálogo"', false)->assertSee("({$total})");

        $pagina2 = $this->get('/catalogo/todos?page=2&orden=precio_asc')->assertOk();
        $this->assertSame(2, $pagina2->viewData('productos')->currentPage());
        $this->assertEmpty(array_intersect(
            $pagina1->viewData('productos')->pluck('sku')->all(),
            $this->get('/catalogo/todos?page=2')->viewData('productos')->pluck('sku')->all()
        ));

        $urls = collect($pagina2->viewData('filtrosMarca'))->pluck('url');
        $this->assertTrue($urls->every(fn ($url) => ! str_contains($url, 'page=') && str_contains($url, 'orden=precio_asc')));
    }

    public function test_home_perfil_footer_y_checkout_tienen_los_ajustes_visuales(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('id="destacado-puntos"', false)
            ->assertSee('js/carrusel-destacados.js', false)
            ->assertSee('js/movimiento.js', false)
            ->assertSee('js/sesion-storage.js', false)
            ->assertSee('js/tienda-ui.js', false)
            // Footer de 5 columnas con ancho intermedio de 1220px, botones redondos de Síguenos y franja inferior.
            ->assertSee('max-w-[1220px] mx-auto px-6 pt-12 pb-8 max-escritorio:px-4 max-escritorio:pt-8 max-escritorio:pb-6', false)
            ->assertSee('grid grid-cols-[1.4fr_repeat(3,1fr)_auto] gap-8 max-[1000px]:grid-cols-2 max-[520px]:grid-cols-1', false)
            ->assertSee('border-t border-white/10 mt-10 pt-5', false)
            ->assertSee('[&>a]:rounded-full', false)
            ->assertSee('Buscar tienda')
            ->assertSee('Síguenos')
            ->assertDontSee('Desarrollado por')
            ->assertDontSee('href="https://x.com"', false);

        $this->actingAs($this->cliente)->get(route('perfil', 'pedidos'))->assertOk()->assertSee('max-w-4xl', false);

        $this->actingAs($this->cliente)->get(route('checkout'))->assertOk()
            ->assertSee('id="confirmarSalida"', false)
            ->assertSee('¿Deseas salir del pago?')
            ->assertSee('Tus productos seguirán guardados en el carrito.');
    }

    // ---------- 5. Abastecimiento por lotes (varias tallas) ----------

    private function lote(array $filas, array $extra = []): array
    {
        return array_merge(['proveedor_id' => Proveedor::activos()->first()->id, 'items' => $filas], $extra);
    }

    public function test_lote_con_varias_tallas_guarda_detalles_inversion_y_suma_todo_el_stock_al_recibir(): void
    {
        $producto = Producto::activos()->has('tallas', '>=', 2)->with('tallas')->firstOrFail();
        [$t1, $t2] = $producto->tallas->take(2)->all();
        $otro = Producto::activos()->where('id', '!=', $producto->id)->has('tallas')->with('tallas')->firstOrFail();
        $t3 = $otro->tallas->first();
        $stock = [$t1->id => $t1->stock, $t2->id => $t2->stock, $t3->id => $t3->stock];

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([
            ['producto_talla_id' => $t1->id, 'cantidad' => 10, 'costo_unitario' => 100],
            ['producto_talla_id' => $t2->id, 'cantidad' => 5, 'costo_unitario' => 120.50],
            ['producto_talla_id' => $t3->id, 'cantidad' => 3, 'costo_unitario' => 80],
        ]))->assertRedirect(route('admin.abastecimiento.index'))->assertSessionHas('exito');

        $orden = OrdenAbastecimiento::with('detalles.talla.producto')->where('codigo_lote', 'LOT-0001')->firstOrFail();
        $this->assertSame('Pendiente', $orden->estado);
        $this->assertCount(3, $orden->detalles);
        $this->assertEquals(1000 + 602.50 + 240, (float) $orden->costo_total);
        $this->assertSame(18, $orden->unidades);
        $this->assertDatabaseHas('detalles_abastecimiento', ['producto_talla_id' => $t2->id, 'cantidad' => 5, 'subtotal' => 602.50]);

        // Ganancia estimada = (precio de venta actual - costo) × cantidad, sumada en todo el lote.
        $ganancia = round(((float) $producto->precio - 100) * 10 + ((float) $producto->precio - 120.50) * 5 + ((float) $otro->precio - 80) * 3, 2);
        $this->assertEquals($ganancia, $orden->ganancia_estimada);
        $this->actingAs($this->admin)->get(route('admin.abastecimiento.index'))->assertOk()
            ->assertSee('S/ ' . number_format(1842.50, 2))
            ->assertSee('S/ ' . number_format($ganancia, 2))
            ->assertSee('Confirmar recepción')
            ->assertSee($otro->nombre);

        // Confirmar recepción suma el stock de las 3 tallas una sola vez.
        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.recibir', $orden))->assertSessionHas('exito');
        $this->assertSame($stock[$t1->id] + 10, $t1->fresh()->stock);
        $this->assertSame($stock[$t2->id] + 5, $t2->fresh()->stock);
        $this->assertSame($stock[$t3->id] + 3, $t3->fresh()->stock);
        $this->assertSame('Recibido', $orden->fresh()->estado);

        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.recibir', $orden))->assertSessionHas('error');
        $this->assertSame($stock[$t1->id] + 10, $t1->fresh()->stock);
    }

    public function test_lote_exige_proveedor_tallas_distintas_y_productos_activos(): void
    {
        $talla = ProductoTalla::first();
        $fila = ['producto_talla_id' => $talla->id, 'cantidad' => 2, 'costo_unitario' => 50];

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), ['items' => [$fila]])
            ->assertSessionHasErrors('proveedor_id');

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([$fila, $fila]))
            ->assertSessionHasErrors('items.1.producto_talla_id');

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([]))
            ->assertSessionHasErrors('items');

        $talla->producto->update(['activo' => false]);
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([$fila]))
            ->assertSessionHasErrors('items.0.producto_talla_id');

        // Inversión que desbordaría decimal(10,2): 10 000 pares × S/ 99 999.99.
        $otra = ProductoTalla::whereHas('producto', fn ($q) => $q->where('activo', true))->firstOrFail();
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([
            ['producto_talla_id' => $otra->id, 'cantidad' => 10000, 'costo_unitario' => 99999.99],
        ]))->assertSessionHasErrors('items');

        $this->assertDatabaseCount('ordenes_abastecimiento', 0);
        $this->assertDatabaseCount('detalles_abastecimiento', 0);
    }

    public function test_formulario_de_lote_sin_proveedor_vacio_solo_productos_activos_y_con_resumen(): void
    {
        $inactivo = Producto::activos()->firstOrFail();
        $inactivo->update(['activo' => false]);

        $this->actingAs($this->admin)->get(route('admin.abastecimiento.create'))->assertOk()
            ->assertDontSee('Selecciona un proveedor')
            ->assertDontSee('value="' . $inactivo->sku . '"', false)
            ->assertSee('Agregar fila')
            ->assertSee('Filtrar por marca, modelo o SKU...')
            ->assertSee('data-filtro-productos', false)
            ->assertSee('id="plantilla-fila-lote"', false)
            ->assertSee('items[__I__][producto_talla_id]', false)
            ->assertSee('Inversión total del lote')
            ->assertSee('Ganancia estimada');
    }

    public function test_al_borrar_un_lote_se_borran_sus_detalles(): void
    {
        $talla = ProductoTalla::first();
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->lote([
            ['producto_talla_id' => $talla->id, 'cantidad' => 4, 'costo_unitario' => 30],
        ]));

        OrdenAbastecimiento::firstOrFail()->delete();
        $this->assertSame(0, DetalleAbastecimiento::count());
    }
}
