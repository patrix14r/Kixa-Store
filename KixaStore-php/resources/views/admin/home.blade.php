@extends('layouts.admin')

@push('styles')
<style>
    .dashboard-header {
        margin-bottom: 25px;
    }
    .dashboard-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #111111;
    }
    .dashboard-header p {
        font-size: 14px;
        color: #666666;
    }

    /* TARJETAS RESUMEN */
    .metricas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 35px;
    }
    .metrica-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid #e5e7eb;
    }
    .metrica-info span {
        font-size: 13px;
        color: #666666;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .metrica-info h2 {
        font-size: 32px;
        font-weight: 800;
        color: #111111;
        margin-top: 5px;
    }
    .metrica-icono {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .icono-productos { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
    .icono-categorias { background: rgba(16, 185, 129, 0.1); color: #059669; }
    .icono-proveedores { background: rgba(245, 158, 11, 0.1); color: #d97706; }
    .icono-pedidos { background: rgba(237, 0, 27, 0.1); color: #ed001b; }

    /* TABLA DE STOCK CRITICO */
    .seccion-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid #e5e7eb;
    }
    .seccion-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .seccion-card-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #111111;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .tabla-responsive {
        overflow-x: auto;
    }
    .tabla-admin {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .tabla-admin th {
        background-color: #f9fafb;
        color: #4b5563;
        font-weight: 600;
        text-align: left;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    .tabla-admin td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }
    .badge-agotado {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
    .badge-critico {
        background-color: #fef3c7;
        color: #d97706;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
</style>
@endpush

@section('contenido')
    {{-- [CAMBIO: DASHBOARD INICIAL DEL MODO ADMINISTRADOR] --}}
    <div class="dashboard-header">
        <h1>Resumen General</h1>
        <p>Métricas y estado de inventario en tiempo real desde MySQL.</p>
    </div>

    <!-- TARJETAS DE METRICAS REALES -->
    <div class="metricas-grid">
        <div class="metrica-card">
            <div class="metrica-info">
                <span>Total Productos</span>
                <h2>{{ $totalProductos ?? 0 }}</h2>
            </div>
            <div class="metrica-icono icono-productos">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="metrica-card">
            <div class="metrica-info">
                <span>Categorías</span>
                <h2>{{ $totalCategorias ?? 0 }}</h2>
            </div>
            <div class="metrica-icono icono-categorias">
                <i class="fa-solid fa-tags"></i>
            </div>
        </div>

        <div class="metrica-card">
            <div class="metrica-info">
                <span>Proveedores</span>
                <h2>{{ $totalProveedores ?? 0 }}</h2>
            </div>
            <div class="metrica-icono icono-proveedores">
                <i class="fa-solid fa-truck-field"></i>
            </div>
        </div>

        <div class="metrica-card">
            <div class="metrica-info">
                <span>Stock Crítico (≤ 2)</span>
                <h2>{{ count($stockCritico ?? []) }}</h2>
            </div>
            <div class="metrica-icono icono-pedidos">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- TABLA DE ALERTAS DE STOCK CRITICO -->
    <div class="seccion-card">
        <div class="seccion-card-header">
            <h3>
                <i class="fa-solid fa-bell" style="color: #ed001b;"></i>
                Alertas de Inventario Crítico
            </h3>
            <span style="font-size: 13px; color: #666666;">Tallas con 2 o menos unidades disponibles</span>
        </div>

        <div class="tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Marca</th>
                        <th>Talla (EU)</th>
                        <th>Stock Actual</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockCritico ?? [] as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->producto->nombre ?? 'N/A' }}</strong>
                            </td>
                            <td><code>{{ $item->producto->sku ?? 'N/A' }}</code></td>
                            <td>{{ $item->producto->marca ?? 'N/A' }}</td>
                            <td><strong>{{ $item->talla }}</strong></td>
                            <td>
                                <strong>{{ $item->stock }}</strong> unid.
                            </td>
                            <td>
                                @if ($item->stock <= 0)
                                    <span class="badge-agotado">AGOTADO</span>
                                @else
                                    <span class="badge-critico">STOCK CRÍTICO</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #666666; padding: 25px;">
                                <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 18px;"></i>
                                ¡Todo el inventario está en niveles óptimos! No hay stock crítico.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

