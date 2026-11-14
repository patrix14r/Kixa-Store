<?php

use App\Http\Controllers\Admin\CategoriaCrudController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\ProductoCrudController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web de KixaStore
|--------------------------------------------------------------------------
| [Sem 08 - Rutas y Vistas Blade]: Cada ruta tiene nombre (->name()) para llamarla con route() desde Blade.
| Orden: 1) Públicas  2) Autenticación  3) Cuenta del cliente  4) Proceso de compra  5) Panel Admin
*/

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS (tienda e informativas)
|--------------------------------------------------------------------------
*/
// [Sem 09 - Controladores y MVC]: Home con carrusel y "Lo Destacado"
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catálogo por categoría (hombre, mujer, ninos, deporte) y ficha del producto por SKU
Route::get('/catalogo/{categoria?}', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/producto/{sku?}', [CatalogoController::class, 'show'])->name('producto.detalle');
// JSON con las tallas en stock (botón "Seleccionar talla" de Favoritos)
Route::get('/producto/{sku}/tallas', [CatalogoController::class, 'tallas'])->name('producto.tallas');

// [Sem 08 - Rutas y Vistas Blade]: Páginas informativas con Route::view (sin controlador)
Route::view('/nosotros', 'informativas.nosotros')->name('nosotros');
Route::view('/contacto', 'informativas.contacto')->name('contacto');
// La antigua página de Ayuda ahora vive dentro de "Acerca de" (redirección 301)
Route::redirect('/ayuda', '/nosotros#centro-ayuda', 301)->name('ayuda');

/*
|--------------------------------------------------------------------------
| 2. AUTENTICACIÓN (login, registro y cierre de sesión)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registrar'])->name('registro.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 3. CUENTA DEL CLIENTE (requiere sesión: middleware auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Perfil con pestañas: /perfil, /perfil/favoritos, /perfil/pedidos, /perfil/direcciones
    Route::get('/perfil/{seccion?}', [AuthController::class, 'perfil'])
        ->whereIn('seccion', ['cuenta', 'favoritos', 'pedidos', 'direcciones'])
        ->name('perfil');
    Route::put('/perfil', [AuthController::class, 'actualizarPerfil'])->name('perfil.update');
    Route::put('/perfil/password', [AuthController::class, 'actualizarPassword'])->name('perfil.password');
    Route::put('/perfil/direccion', [AuthController::class, 'actualizarDireccion'])->name('perfil.direccion');
});

/*
|--------------------------------------------------------------------------
| 4. PROCESO DE COMPRA (carrito, favoritos y checkout)
|--------------------------------------------------------------------------
*/
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
Route::view('/favoritos', 'cliente.favoritos')->name('favoritos');
// Checkout: el invitado va a login?redirect=checkout; envía cabeceras anti-caché y un token de un solo uso
Route::get('/checkout', [CarritoController::class, 'checkout'])->name('checkout');
Route::post('/checkout/procesar', [CarritoController::class, 'procesar'])->middleware('auth')->name('checkout.procesar');

/*
|--------------------------------------------------------------------------
| 5. PANEL ADMINISTRADOR (prefijo /admin, nombres admin.*)
|--------------------------------------------------------------------------
| [Sem 11 - Migraciones, Seeders y Roles]: Protegido por los middleware auth y admin (control por rol)
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard "Resumen General"
    Route::get('/', [AdminController::class, 'home'])->name('home');

    // [Sem 09 - Controladores y MVC]: Mantenimiento 1 - Categorías (Resource sin show/destroy: se activa/desactiva)
    Route::resource('categorias', CategoriaCrudController::class)->except(['show', 'destroy'])
        ->parameters(['categorias' => 'categoria']);
    Route::patch('categorias/{categoria}/estado', [CategoriaCrudController::class, 'estado'])->name('categorias.estado');

    // [Sem 09 - Controladores y MVC]: Mantenimiento 2 - Productos (vista rápida en modal, SKU automático y galería)
    Route::resource('productos', ProductoCrudController::class)->except(['show', 'destroy'])
        ->parameters(['productos' => 'producto']);
    Route::patch('productos/{producto}/estado', [ProductoCrudController::class, 'estado'])->name('productos.estado');

    // Clientes (solo rol cliente): listado, historial de pedidos y edición de datos básicos (no gestiona roles, Regla 4.2)
    Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'edit', 'update'])
        ->parameters(['clientes' => 'cliente']);

    // [Sem 09 - Controladores y MVC]: Proceso 2 - Gestión y despacho de pedidos (cambio de estado con PATCH)
    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::patch('pedidos/{pedido}/estado', [PedidoController::class, 'estado'])->name('pedidos.estado');

    // Perfil propio del administrador dentro del panel oscuro
    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/password', [PerfilController::class, 'password'])->name('perfil.password');

    // [Sem 08 - Rutas y Vistas Blade]: Módulos pendientes con vista en blanco (ningún enlace del sidebar queda muerto)
    Route::view('proveedores', 'admin.pendientes.proveedores')->name('proveedores');
    Route::view('tallas', 'admin.pendientes.tallas')->name('tallas');
    Route::view('abastecimiento', 'admin.pendientes.abastecimiento')->name('abastecimiento');
});
