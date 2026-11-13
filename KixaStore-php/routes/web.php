<?php

// [CAMBIO: IMPORTACION DE CONTROLADORES AUTH, CATALOGO Y CARRITO]
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;

/*
| Rutas web de KixaStore
| Cada ruta cuenta con un nombre (->name()) para facilitar su llamado desde Blade.
*/

// [CAMBIO: RUTA PROTEGIDA DEL PANEL DE ADMINISTRACION CON MIDDLEWARE ADMIN Y METRICAS DE MYSQL]
Route::get('/admin', function () {
    $totalProductos = \App\Models\Producto::count();
    $totalCategorias = \App\Models\Categoria::count();
    $totalProveedores = \App\Models\Proveedor::count();
    $stockCritico = \App\Models\ProductoTalla::with('producto')->where('stock', '<=', 2)->get();

    return view('admin.home', compact('totalProductos', 'totalCategorias', 'totalProveedores', 'stockCritico'));
})->middleware(['auth', 'admin'])->name('admin.home');

// [RUTA]: Página principal de la tienda (Home con carrusel y novedades)
Route::get('/', function () {
    return view('cliente.home');
})->name('home');

// [CAMBIO: RUTAS DE CATALOGO Y PRODUCTO VINCULADAS A CATALOGOCONTROLLER]
Route::get('/catalogo/{categoria?}', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/producto/{sku?}', [CatalogoController::class, 'show'])->name('producto.detalle');

// [CAMBIO: RUTAS DE CARRITO Y CHECKOUT VINCULADAS A CARRITOCONTROLLER]
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
Route::get('/favoritos', function () {
    return view('cliente.favoritos');
})->name('favoritos');
Route::get('/checkout', [CarritoController::class, 'checkout'])->name('checkout');
Route::post('/checkout/procesar', [CarritoController::class, 'procesar'])->middleware('auth')->name('checkout.procesar');

// [RUTA]: Centro de ayuda y preguntas frecuentes
Route::get('/ayuda', function () {
    return view('informativas.ayuda');
})->name('ayuda');

// [RUTA]: Página de contacto y soporte al cliente
Route::get('/contacto', function () {
    return view('informativas.contacto');
})->name('contacto');

// [CAMBIO: RUTAS DE AUTENTICACION, REGISTRO, PERFIL Y LOGOUT CON AUTHCONTROLLER]
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registrar'])->name('registro.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/perfil', [AuthController::class, 'perfil'])->middleware('auth')->name('perfil');
Route::put('/perfil', [AuthController::class, 'actualizarPerfil'])->middleware('auth')->name('perfil.update');
