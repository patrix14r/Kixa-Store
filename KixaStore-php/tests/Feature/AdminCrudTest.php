<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
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
            ->assertSee('data-filtro-marca="Jordan"', false)
            ->assertSee('Alertas de Inventario Crítico')
            ->assertDontSee('Jordan Jordan')
            ->assertDontSee('Nike Nike');
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

    // ---------- Módulos pendientes (placeholders del sidebar) ----------

    public function test_admin_ve_vistas_placeholder_de_modulos_pendientes(): void
    {
        $modulos = [
            'admin.proveedores' => 'Proveedores',
            'admin.tallas' => 'Tallas y Stock',
            'admin.abastecimiento' => 'Abastecimiento',
        ];

        foreach ($modulos as $ruta => $texto) {
            // [Actualizado: Módulos en blanco] Solo el título, sin cartel de "Próximamente"
            $this->actingAs($this->admin)->get(route($ruta))->assertOk()->assertSee($texto)->assertDontSee('Próximamente');
        }

        $this->actingAs($this->cliente)->get(route('admin.pedidos.index'))->assertRedirect();
    }
}
