<?php

namespace Tests\Feature;

use App\Models\OrdenAbastecimiento;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// [Sem 13 - CRUDs, Query Builder y Paginación]: proceso secundario de abastecimiento: tallas sin duplicar en un lote,
// selector "Marca — Nombre (SKU)" y Guía de Abastecimiento / Orden de Compra en PDF.
class AbastecimientoGuiaTest extends TestCase
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

    // Registra un lote de una fila por la ruta real del panel y lo devuelve.
    private function crearLote(): OrdenAbastecimiento
    {
        $talla = ProductoTalla::first();

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), [
            'proveedor_id' => Proveedor::activos()->first()->id,
            'items' => [['producto_talla_id' => $talla->id, 'cantidad' => 6, 'costo_unitario' => 80]],
        ])->assertRedirect(route('admin.abastecimiento.index'));

        return OrdenAbastecimiento::firstOrFail();
    }

    public function test_store_rechaza_la_misma_talla_dos_veces_en_un_lote(): void
    {
        $talla = ProductoTalla::first();

        $this->actingAs($this->admin)->post(route('admin.abastecimiento.store'), [
            'proveedor_id' => Proveedor::activos()->first()->id,
            'items' => [
                ['producto_talla_id' => $talla->id, 'cantidad' => 5, 'costo_unitario' => 50],
                ['producto_talla_id' => $talla->id, 'cantidad' => 3, 'costo_unitario' => 50],
            ],
        ])->assertSessionHasErrors(['items.1.producto_talla_id' => 'Esta talla ya está en el lote: suma las cantidades en una sola fila.']);

        $this->assertDatabaseCount('ordenes_abastecimiento', 0);
        $this->assertDatabaseCount('detalles_abastecimiento', 0);
    }

    public function test_selector_muestra_marca_modelo_y_sku_y_aviso_de_duplicado(): void
    {
        $producto = Producto::activos()->orderBy('marca')->orderBy('nombre')->first();

        $respuesta = $this->actingAs($this->admin)->get(route('admin.abastecimiento.create'));

        $respuesta->assertOk()
            ->assertSee("{$producto->marca} — {$producto->modelo} ({$producto->sku})")
            ->assertSee('data-aviso-duplicado', false);
        // Ordenados por marca y luego por nombre para ubicar rápido cada modelo.
        $this->assertSame($producto->sku, $respuesta->viewData('productos')->first()->sku);
    }

    public function test_index_muestra_botones_ver_guia_y_descargar(): void
    {
        $orden = $this->crearLote();

        $this->actingAs($this->admin)->get(route('admin.abastecimiento.index'))
            ->assertOk()
            ->assertSee('Ver guía')
            ->assertSee('Descargar')
            ->assertSee(route('admin.abastecimiento.guia', $orden), false);
    }

    public function test_admin_ve_la_guia_pdf_en_vista_previa(): void
    {
        $orden = $this->crearLote();
        $this->assertStringEndsWith('/admin/abastecimiento/LOT-0001/guia', route('admin.abastecimiento.guia', $orden));

        $respuesta = $this->actingAs($this->admin)->get(route('admin.abastecimiento.guia', $orden));

        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Orden-LOT-0001.pdf', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_admin_descarga_la_guia_pdf_con_descargar_1(): void
    {
        $orden = $this->crearLote();

        $respuesta = $this->actingAs($this->admin)->get(route('admin.abastecimiento.guia', ['orden' => $orden, 'descargar' => 1]));

        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Orden-LOT-0001.pdf', $respuesta->headers->get('Content-Disposition'));
    }

    public function test_guia_renderiza_proveedor_items_e_inversion(): void
    {
        $orden = $this->crearLote()->load(['proveedor', 'detalles.talla.producto']);
        $detalle = $orden->detalles->first();

        $html = view('comprobantes.orden-abastecimiento', [
            'orden' => $orden,
            'empresa' => ['nombre' => 'KixaStore', 'direccion' => 'Lima', 'web' => 'kixastore.pe'],
            'logo' => public_path('images/ui/web.jpg'),
        ])->render();

        $this->assertStringContainsString('LOT-0001', $html);
        $this->assertStringContainsString(e($orden->proveedor->razon_social), $html);
        $this->assertStringContainsString($orden->proveedor->ruc, $html);
        $this->assertStringContainsString($detalle->talla->producto->sku, $html);
        $this->assertStringContainsString('S/ 480.00', $html);
        $this->assertStringContainsString('Ganancia estimada', $html);
    }

    public function test_cliente_recibe_403_e_invitado_va_al_login_al_pedir_la_guia(): void
    {
        $orden = $this->crearLote();
        auth()->logout();

        $this->actingAs($this->cliente)->get(route('admin.abastecimiento.guia', $orden))->assertForbidden();

        auth()->logout();
        $this->get(route('admin.abastecimiento.guia', $orden))->assertRedirect(route('login'));
    }
}
