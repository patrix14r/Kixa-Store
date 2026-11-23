<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\OrdenAbastecimiento;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
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

    // ---------- Mantenimiento 1: Categorías ----------

    public function test_listado_de_categorias_muestra_nombres_y_conteo_sin_ids(): void
    {
        $categoria = Categoria::withCount('productos')->where('slug', 'hombre')->first();

        $this->actingAs($this->admin)->get(route('admin.categorias.index'))
            ->assertOk()
            ->assertSee('Hombre')
            ->assertSee('Productos')
            ->assertDontSee('<th class="kx-th">ID</th>', false)
            ->assertSee((string) $categoria->productos_count);
    }

    public function test_admin_crea_categoria_y_genera_slug(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categorias.store'), [
            'nombre' => 'Edición Limitada',
            'descripcion' => 'Lanzamientos exclusivos',
            'activo' => '1',
        ])->assertRedirect(route('admin.categorias.index'));

        $this->assertDatabaseHas('categorias', ['nombre' => 'Edición Limitada', 'slug' => 'edicion-limitada', 'activo' => true]);
    }

    public function test_crear_categoria_con_nombre_duplicado_falla(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => 'Hombre'])
            ->assertSessionHasErrors('nombre');
    }

    public function test_admin_edita_categoria(): void
    {
        $categoria = Categoria::where('slug', 'deporte')->first();

        $this->actingAs($this->admin)->get(route('admin.categorias.edit', $categoria))->assertOk()->assertSee('Deporte');

        $this->actingAs($this->admin)->put(route('admin.categorias.update', $categoria), [
            'nombre' => 'Deportes',
            'slug' => 'deporte',
            'descripcion' => 'Rendimiento',
            'activo' => '1',
        ])->assertRedirect(route('admin.categorias.index'));

        $this->assertDatabaseHas('categorias', ['slug' => 'deporte', 'nombre' => 'Deportes']);
    }

    public function test_admin_desactiva_categoria_sin_borrarla(): void
    {
        $categoria = Categoria::where('slug', 'mujer')->first();

        $this->actingAs($this->admin)->patch(route('admin.categorias.estado', $categoria))->assertRedirect();

        $this->assertFalse($categoria->fresh()->activo);
        $this->assertDatabaseHas('categorias', ['slug' => 'mujer']);
    }

    public function test_cliente_no_puede_entrar_a_los_mantenimientos(): void
    {
        $this->actingAs($this->cliente)->get(route('admin.categorias.index'))->assertRedirect(route('home'));
        $this->actingAs($this->cliente)->get(route('admin.productos.index'))->assertRedirect(route('home'));
    }

    // ---------- Mantenimiento 2: Productos ----------

    public function test_listado_de_productos_muestra_fk_por_nombre(): void
    {
        $producto = Producto::with(['categoria', 'proveedor'])->whereNotNull('proveedor_id')->orderBy('nombre')->first();

        $this->actingAs($this->admin)->get(route('admin.productos.index', ['buscar' => $producto->sku]))
            ->assertOk()
            ->assertSee($producto->categoria->nombre)
            ->assertSee($producto->proveedor->razon_social)
            ->assertSee($producto->sku);
    }

    public function test_buscador_y_filtro_de_productos(): void
    {
        $this->actingAs($this->admin)->get(route('admin.productos.index', ['marca' => 'Puma']))
            ->assertOk()
            ->assertDontSee('Retro 4 Black Cat');
    }

    public function test_formulario_de_producto_usa_selects_con_nombres(): void
    {
        $proveedor = Proveedor::first();

        $this->actingAs($this->admin)->get(route('admin.productos.create'))
            ->assertOk()
            ->assertSee('name="categoria_id"', false)
            ->assertSee('name="proveedor_id"', false)
            ->assertSee('Hombre')
            ->assertSee($proveedor->razon_social);
    }

    public function test_admin_crea_producto_con_tallas(): void
    {
        $categoria = Categoria::where('slug', 'hombre')->first();
        $proveedor = Proveedor::first();

        $this->actingAs($this->admin)->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'marca' => 'Nike',
            'nombre' => 'Nike Air Max Test',
            'sku' => 'nik-test-01',
            'precio' => 499.90,
            'imagen' => 'images/products/hombre/h1.jpg',
            'activo' => '1',
            'tallas' => [
                ['talla' => 40, 'stock' => 5],
                ['talla' => 41, 'stock' => 0],
                ['talla' => '', 'stock' => 3],
            ],
        ])->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', 'NIK-TEST-01')->first();
        $this->assertNotNull($producto);
        $this->assertSame('Air Max Test', $producto->modelo);
        $this->assertCount(2, $producto->tallas);
        $this->assertSame(5, $producto->tallas()->where('talla', 40)->value('stock'));
    }

    public function test_crear_producto_sin_categoria_falla(): void
    {
        $this->actingAs($this->admin)->post(route('admin.productos.store'), [
            'marca' => 'Nike',
            'nombre' => 'Sin categoría',
            'sku' => 'SIN-CAT',
            'precio' => 100,
        ])->assertSessionHasErrors('categoria_id');
    }

    public function test_admin_edita_producto_y_actualiza_stock(): void
    {
        $producto = Producto::with('tallas')->first();
        $tallas = $producto->tallas->map(fn ($t) => ['talla' => (float) $t->talla, 'stock' => 7])->all();
        array_pop($tallas);

        $this->actingAs($this->admin)->get(route('admin.productos.edit', $producto))->assertOk()->assertSee($producto->sku);

        $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), [
            'categoria_id' => $producto->categoria_id,
            'proveedor_id' => $producto->proveedor_id,
            'marca' => $producto->marca,
            'nombre' => $producto->nombre,
            'sku' => $producto->sku,
            'precio' => 999.00,
            'imagen' => $producto->imagen,
            'activo' => '1',
            'tallas' => $tallas,
        ])->assertRedirect(route('admin.productos.index'));

        $producto->refresh();
        $this->assertEquals(999.00, (float) $producto->precio);
        $this->assertCount(count($tallas), $producto->tallas);
        $this->assertTrue($producto->tallas->every(fn ($t) => $t->stock === 7));
    }

    public function test_admin_desactiva_producto(): void
    {
        $producto = Producto::first();

        $this->actingAs($this->admin)->patch(route('admin.productos.estado', $producto))->assertRedirect();

        $this->assertFalse($producto->fresh()->activo);
    }

    // ---------- Dashboard ----------

    public function test_dashboard_muestra_filtro_por_marca_y_nombre_sin_marca_repetida(): void
    {
        $this->actingAs($this->admin)->get(route('admin.home'))
            ->assertOk()
            ->assertSee('admin?marca=Jordan#alertas', false)
            ->assertSee('Alertas de Inventario Crítico')
            ->assertDontSee('Jordan Jordan')
            ->assertDontSee('Nike Nike');
    }

    public function test_dashboard_limita_alertas_a_8_y_filtra_por_marca_en_el_servidor(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.home', ['marca' => 'Jordan']));
        $respuesta->assertOk();
        $alertas = $respuesta->viewData('alertas');
        $this->assertLessThanOrEqual(8, $alertas->count());
        $this->assertTrue($alertas->every(fn ($t) => $t->producto->marca === 'Jordan'));
    }

    public function test_tallas_filtra_por_estado_marca_y_busqueda(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.tallas.index', ['estado' => 'agotado']));
        $respuesta->assertOk();
        $this->assertTrue($respuesta->viewData('tallas')->every(fn ($t) => $t->stock === 0));

        $producto = Producto::first();
        $porSku = $this->actingAs($this->admin)->get(route('admin.tallas.index', ['q' => $producto->sku, 'marca' => $producto->marca]));
        $this->assertTrue($porSku->viewData('tallas')->every(fn ($t) => $t->producto_id === $producto->id));
    }

    public function test_abastecimiento_muestra_kpis_y_reponer_preselecciona_talla(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.abastecimiento.index'));
        $respuesta->assertOk()->assertSee('Más urgentes');
        $this->assertLessThanOrEqual(5, $respuesta->viewData('urgentes')->count());

        $talla = ProductoTalla::with('producto')->first();
        $this->actingAs($this->admin)
            ->get(route('admin.abastecimiento.create', ['producto' => $talla->producto->sku, 'talla' => $talla->talla]))
            ->assertOk()->assertSee('value="' . $talla->id . '" selected', false);
    }

    // ---------- Restricción: el admin no compra ----------

    public function test_admin_no_puede_entrar_al_checkout(): void
    {
        $this->actingAs($this->admin)->get(route('checkout'))
            ->assertRedirect(route('carrito'))
            ->assertSessionHas('error');
    }

    public function test_admin_no_puede_procesar_compras(): void
    {
        $producto = Producto::first();

        $this->actingAs($this->admin)->postJson(route('checkout.procesar'), [
            'metodo_pago' => 'Contraentrega',
            'items' => [['id' => $producto->id, 'talla' => 40, 'cantidad' => 1]],
        ])->assertForbidden()->assertJson(['exito' => false]);

        $this->assertSame(0, Pedido::count());
    }

    public function test_admin_ve_boton_gestionar_en_ficha_y_carrito(): void
    {
        $producto = Producto::first();

        $this->actingAs($this->admin)->get(route('producto.detalle', $producto->sku))
            ->assertOk()
            ->assertSee('Gestionar este producto en Panel Admin')
            ->assertDontSee('id="addBtn"', false);

        $this->actingAs($this->admin)->get(route('carrito'))
            ->assertOk()
            ->assertSee('Gestionar productos en Panel Admin')
            ->assertDontSee('Continuar con la compra');
    }

    public function test_cliente_sigue_viendo_boton_de_compra(): void
    {
        $producto = Producto::first();

        $this->actingAs($this->cliente)->get(route('producto.detalle', $producto->sku))
            ->assertOk()
            ->assertSee('id="addBtn"', false)
            ->assertDontSee('Gestionar este producto en Panel Admin');
    }

    // ---------- Mantenimiento 3: Proveedores ----------

    private function datosProveedor(array $extra = []): array
    {
        return array_merge([
            'razon_social' => 'Calzados Andinos S.A.C.',
            'ruc' => '20999888777',
            'contacto_nombre' => 'Ana Torres',
            'telefono' => '955444333',
            'correo' => 'ventas@andinos.pe',
            'direccion' => 'Av. Lima 123',
            'activo' => 1,
        ], $extra);
    }

    public function test_listado_de_proveedores_muestra_datos_y_conteo_sin_ids(): void
    {
        $proveedor = Proveedor::withCount('productos')->first();

        $this->actingAs($this->admin)->get(route('admin.proveedores.index'))
            ->assertOk()
            ->assertSee($proveedor->razon_social)
            ->assertSee($proveedor->ruc)
            ->assertDontSee('<th class="kx-th">ID</th>', false);
    }

    public function test_buscador_de_proveedores_filtra_por_razon_social_y_ruc(): void
    {
        $this->actingAs($this->admin)->get(route('admin.proveedores.index', ['buscar' => 'Nike']))
            ->assertOk()->assertSee('Nike Perú')->assertDontSee('Adidas Perú');

        $this->actingAs($this->admin)->get(route('admin.proveedores.index', ['buscar' => '20345678912']))
            ->assertOk()->assertSee('Adidas Perú')->assertDontSee('Nike Perú');
    }

    public function test_admin_crea_proveedor(): void
    {
        $this->actingAs($this->admin)->post(route('admin.proveedores.store'), $this->datosProveedor())
            ->assertRedirect(route('admin.proveedores.index'));

        $this->assertDatabaseHas('proveedores', ['ruc' => '20999888777', 'razon_social' => 'Calzados Andinos S.A.C.', 'activo' => true]);
    }

    public function test_proveedor_valida_ruc_telefono_y_unicidad(): void
    {
        $existente = Proveedor::first();

        $this->actingAs($this->admin)->post(route('admin.proveedores.store'), $this->datosProveedor(['ruc' => '123']))
            ->assertSessionHasErrors('ruc');
        $this->actingAs($this->admin)->post(route('admin.proveedores.store'), $this->datosProveedor(['ruc' => $existente->ruc]))
            ->assertSessionHasErrors('ruc');
        $this->actingAs($this->admin)->post(route('admin.proveedores.store'), $this->datosProveedor(['telefono' => '12345']))
            ->assertSessionHasErrors('telefono');
        $this->actingAs($this->admin)->post(route('admin.proveedores.store'), $this->datosProveedor(['razon_social' => '']))
            ->assertSessionHasErrors('razon_social');
    }

    public function test_admin_edita_proveedor_por_ruc_en_la_url(): void
    {
        $proveedor = Proveedor::first();

        $this->actingAs($this->admin)->get(route('admin.proveedores.edit', $proveedor))
            ->assertOk()->assertSee($proveedor->razon_social);
        $this->assertStringContainsString($proveedor->ruc, route('admin.proveedores.edit', $proveedor));

        // Se conserva su propio RUC: la regla unique debe ignorarlo.
        $this->actingAs($this->admin)->put(route('admin.proveedores.update', $proveedor), $this->datosProveedor([
            'ruc' => $proveedor->ruc,
            'razon_social' => 'Nombre Nuevo S.A.C.',
        ]))->assertRedirect(route('admin.proveedores.index'));

        $this->assertDatabaseHas('proveedores', ['ruc' => $proveedor->ruc, 'razon_social' => 'Nombre Nuevo S.A.C.']);
    }

    public function test_admin_activa_y_desactiva_proveedor_sin_borrarlo(): void
    {
        $proveedor = Proveedor::where('activo', true)->first();

        $this->actingAs($this->admin)->patch(route('admin.proveedores.estado', $proveedor))->assertRedirect();
        $this->assertDatabaseHas('proveedores', ['ruc' => $proveedor->ruc, 'activo' => false]);

        $this->actingAs($this->admin)->patch(route('admin.proveedores.estado', $proveedor))->assertRedirect();
        $this->assertDatabaseHas('proveedores', ['ruc' => $proveedor->ruc, 'activo' => true]);
    }

    public function test_cliente_no_accede_a_proveedores(): void
    {
        $this->actingAs($this->cliente)->get(route('admin.proveedores.index'))->assertRedirect(route('home'));
    }

    public function test_ficha_de_producto_incluye_modal_guia_de_tallas(): void
    {
        $producto = Producto::activos()->first();

        $this->get(route('producto.detalle', $producto->sku))
            ->assertOk()
            ->assertSee('id="abrirGuiaTallas"', false)
            ->assertSee('id="modalGuiaTallas"', false)
            ->assertSee('Talla EU');
    }

    // ---------- Mantenimiento 4: Tallas y Stock ----------

    public function test_listado_de_tallas_muestra_nombre_de_producto_y_filtra(): void
    {
        $talla = ProductoTalla::with('producto')->first();
        $otra = ProductoTalla::with('producto')->where('producto_id', '!=', $talla->producto_id)->first();

        $this->actingAs($this->admin)->get(route('admin.tallas.index'))
            ->assertOk()->assertSee($talla->producto->nombre);

        $this->actingAs($this->admin)->get(route('admin.tallas.index', ['producto' => $talla->producto->sku]))
            ->assertOk()->assertSee($talla->producto->nombre)->assertDontSee('>' . $otra->producto->nombre . '</td>', false);
    }

    public function test_filtro_stock_bajo_solo_muestra_tallas_con_dos_o_menos(): void
    {
        $producto = Producto::first();
        ProductoTalla::where('producto_id', $producto->id)->delete();
        ProductoTalla::create(['producto_id' => $producto->id, 'talla' => 40, 'stock' => 1]);
        ProductoTalla::create(['producto_id' => $producto->id, 'talla' => 41, 'stock' => 50]);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.tallas.index', ['producto' => $producto->sku, 'stock' => 'bajo']));
        $respuesta->assertOk();
        $this->assertCount(1, $respuesta->viewData('tallas'));
    }

    public function test_admin_crea_edita_y_elimina_talla(): void
    {
        $producto = Producto::first();

        $this->actingAs($this->admin)->post(route('admin.tallas.store'), ['producto_id' => $producto->id, 'talla' => 48.5, 'stock' => 7])
            ->assertRedirect(route('admin.tallas.index'));
        $this->assertDatabaseHas('producto_tallas', ['producto_id' => $producto->id, 'talla' => 48.5, 'stock' => 7]);

        $talla = ProductoTalla::where('producto_id', $producto->id)->where('talla', 48.5)->first();
        $this->actingAs($this->admin)->get(route('admin.tallas.edit', [$producto, $talla->talla]))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.tallas.update', [$producto, $talla->talla]), ['producto_id' => $producto->id, 'talla' => 48.5, 'stock' => 20])
            ->assertRedirect(route('admin.tallas.index'));
        $this->assertDatabaseHas('producto_tallas', ['id' => $talla->id, 'stock' => 20]);

        $this->actingAs($this->admin)->delete(route('admin.tallas.destroy', [$producto, $talla->talla]))->assertRedirect(route('admin.tallas.index'));
        $this->assertDatabaseMissing('producto_tallas', ['id' => $talla->id]);
    }

    public function test_talla_valida_duplicado_y_stock_negativo(): void
    {
        $existente = ProductoTalla::first();

        $this->actingAs($this->admin)->post(route('admin.tallas.store'), ['producto_id' => $existente->producto_id, 'talla' => $existente->talla, 'stock' => 1])
            ->assertSessionHasErrors('talla');
        $this->actingAs($this->admin)->post(route('admin.tallas.store'), ['producto_id' => $existente->producto_id, 'talla' => 49, 'stock' => -1])
            ->assertSessionHasErrors('stock');
    }

    public function test_cliente_no_accede_a_tallas_ni_abastecimiento(): void
    {
        $this->actingAs($this->cliente)->get(route('admin.tallas.index'))->assertRedirect(route('home'));
        $this->actingAs($this->cliente)->get(route('admin.abastecimiento.index'))->assertRedirect(route('home'));
    }

    // ---------- Proceso secundario: Abastecimiento ----------

    private function datosOrden(ProductoTalla $talla, array $extra = []): array
    {
        return array_merge([
            'proveedor_id' => Proveedor::activos()->first()->id,
            'producto_talla_id' => $talla->id,
            'cantidad_solicitada' => 10,
            'costo_unitario' => 55.50,
        ], $extra);
    }

    public function test_admin_registra_orden_con_lote_automatico_costo_total_y_estado_pendiente(): void
    {
        $talla = ProductoTalla::first();

        $this->actingAs($this->admin)->get(route('admin.abastecimiento.create'))->assertOk()->assertSee('LOT-0001');
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla))
            ->assertRedirect(route('admin.abastecimiento.index'));
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla, ['cantidad_solicitada' => 2, 'costo_unitario' => 10]));

        $this->assertDatabaseHas('ordenes_abastecimiento', [
            'codigo_lote' => 'LOT-0001', 'estado' => 'Pendiente', 'costo_total' => 555.00, 'user_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('ordenes_abastecimiento', ['codigo_lote' => 'LOT-0002', 'costo_total' => 20.00]);

        $this->actingAs($this->admin)->get(route('admin.abastecimiento.index'))
            ->assertOk()->assertSee('LOT-0001')->assertSee($talla->producto->nombre);
    }

    public function test_abastecimiento_rechaza_proveedor_inactivo_y_cantidad_invalida(): void
    {
        $talla = ProductoTalla::first();
        $inactivo = Proveedor::first();
        $inactivo->update(['activo' => false]);

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla, ['proveedor_id' => $inactivo->id]))
            ->assertStatus(422);
        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla, ['cantidad_solicitada' => 0]))
            ->assertSessionHasErrors('cantidad_solicitada');
        $this->assertDatabaseCount('ordenes_abastecimiento', 0);
    }

    public function test_recibir_orden_suma_el_stock_real_una_sola_vez(): void
    {
        $talla = ProductoTalla::first();
        $stockInicial = $talla->stock;

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla, ['cantidad_solicitada' => 15]));
        $orden = OrdenAbastecimiento::where('codigo_lote', 'LOT-0001')->first();

        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.recibir', $orden))->assertRedirect();

        $this->assertSame($stockInicial + 15, $talla->fresh()->stock);
        $orden->refresh();
        $this->assertSame('Recibido', $orden->estado);
        $this->assertNotNull($orden->fecha_recepcion);

        // Recibir otra vez no debe sumar de nuevo.
        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.recibir', $orden))->assertSessionHas('error');
        $this->assertSame($stockInicial + 15, $talla->fresh()->stock);
    }

    public function test_cancelar_orden_no_modifica_el_stock(): void
    {
        $talla = ProductoTalla::first();
        $stockInicial = $talla->stock;

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), $this->datosOrden($talla));
        $orden = OrdenAbastecimiento::first();

        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.cancelar', $orden))->assertRedirect();
        $this->assertSame('Cancelado', $orden->fresh()->estado);
        $this->assertSame($stockInicial, $talla->fresh()->stock);

        $this->actingAs($this->admin)->patch(route('admin.abastecimiento.recibir', $orden))->assertSessionHas('error');
        $this->assertSame($stockInicial, $talla->fresh()->stock);
    }

    public function test_dashboard_incluye_grafico_de_ventas(): void
    {
        $this->actingAs($this->admin)->get(route('admin.home'))
            ->assertOk()
            ->assertSee('id="graficoVentas"', false)
            ->assertSee('chart.js', false);
    }

    public function test_cliente_no_entra_al_resto_del_panel(): void
    {
        $this->actingAs($this->cliente)->get(route('admin.pedidos.index'))->assertRedirect();
    }
}
