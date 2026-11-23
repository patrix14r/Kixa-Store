<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Models\ProductoTalla;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // View Composer que entrega menú y notificaciones al layout admin sin lógica en Blade
        View::composer('layouts.admin', function ($vista) {
            $vista->with('menuPrincipal', [
                ['texto' => 'Resumen', 'ruta' => route('admin.home'), 'icono' => 'fa-solid fa-house', 'activo' => request()->routeIs('admin.home')],
                ['texto' => 'Categorías', 'ruta' => route('admin.categorias.index'), 'icono' => 'fa-solid fa-tags', 'activo' => request()->routeIs('admin.categorias.*')],
                ['texto' => 'Productos', 'ruta' => route('admin.productos.index'), 'icono' => 'svg-paquete', 'activo' => request()->routeIs('admin.productos.*')],
                ['texto' => 'Proveedores', 'ruta' => route('admin.proveedores.index'), 'icono' => 'fa-solid fa-truck', 'activo' => request()->routeIs('admin.proveedores.*')],
                ['texto' => 'Tallas y Stock', 'ruta' => route('admin.tallas.index'), 'icono' => 'fa-solid fa-ruler', 'activo' => request()->routeIs('admin.tallas.*')],
            ]);
            $vista->with('menuOperaciones', [
                ['texto' => 'Abastecimiento', 'ruta' => route('admin.abastecimiento.index'), 'icono' => 'fa-solid fa-boxes-stacked', 'activo' => request()->routeIs('admin.abastecimiento.*')],
                ['texto' => 'Clientes', 'ruta' => route('admin.clientes.index'), 'icono' => 'fa-solid fa-users', 'activo' => request()->routeIs('admin.clientes.*')],
                ['texto' => 'Pedidos', 'ruta' => route('admin.pedidos.index'), 'icono' => 'fa-solid fa-receipt', 'activo' => request()->routeIs('admin.pedidos.*')],
            ]);

            // Alertas reales para la campanita (tallas agotadas, críticas y últimos pedidos)
            $agotadas = ProductoTalla::where('stock', 0)->count();
            $criticas = ProductoTalla::whereBetween('stock', [1, 2])->count();
            $ultimosPedidos = Pedido::with('usuario')->latest()->take(5)->get();

            $vista->with('notificaciones', [
                'agotadas' => $agotadas,
                'criticas' => $criticas,
                'pedidos'  => $ultimosPedidos,
                'total'    => ($agotadas > 0 ? 1 : 0) + ($criticas > 0 ? 1 : 0) + $ultimosPedidos->count(),
            ]);
        });
    }
}
