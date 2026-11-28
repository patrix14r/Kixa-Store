<?php
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\AbastecimientoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\MarcaController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoletaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NosotrosController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de la tienda
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalogo/{categoria?}', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/producto/{sku?}', [CatalogoController::class, 'show'])->name('producto.detalle');
Route::get('/producto/{sku}/tallas', [CatalogoController::class, 'tallas'])->name('producto.tallas');

// Controlador invocable de Nosotros
Route::get('/nosotros', NosotrosController::class)->name('nosotros');
Route::redirect('/ayuda', '/nosotros#centro-ayuda', 301)->name('ayuda');

// Login y registro los maneja Fortify
Route::redirect('/registro', '/register');

// Cuenta del cliente (requiere sesión)
Route::middleware('auth')->group(function () {
    Route::get('/perfil/{seccion?}', [AuthController::class, 'perfil'])
        ->whereIn('seccion', ['cuenta', 'favoritos', 'pedidos', 'direcciones'])
        ->name('perfil');
    Route::put('/perfil', [AuthController::class, 'actualizarPerfil'])->name('perfil.update');
    Route::put('/perfil/password', [AuthController::class, 'actualizarPassword'])->name('perfil.password');
    Route::put('/perfil/direccion', [AuthController::class, 'actualizarDireccion'])->name('perfil.direccion');

    // Boleta PDF del pedido
    Route::get('/pedidos/{pedido}/boleta', BoletaController::class)->name('pedidos.boleta');
});

// Carrito y checkout
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
// Valida stock real (60 por minuto)
Route::post('/carrito/validar', [CarritoController::class, 'validar'])->middleware('throttle:60,1')->name('carrito.validar');
Route::view('/favoritos', 'cliente.favoritos')->name('favoritos');
Route::get('/checkout', [CarritoController::class, 'checkout'])->name('checkout');
Route::post('/checkout/procesar', [CarritoController::class, 'procesar'])->middleware('auth')->name('checkout.procesar');

// Panel admin: solo rol admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () { //prefix es 
    Route::get('/', [AdminController::class, 'home'])->name('home');

    // CRUD 1: categorías
    Route::resource('categorias', CategoriaController::class)->except(['show', 'destroy'])
        ->parameters(['categorias' => 'categoria']);
    Route::patch('categorias/{categoria}/estado', [CategoriaController::class, 'estado'])->name('categorias.estado');

    // CRUD 2: productos
    Route::resource('productos', ProductoController::class)->except(['show', 'destroy'])
        ->parameters(['productos' => 'producto']);
    Route::patch('productos/{producto}/estado', [ProductoController::class, 'estado'])->name('productos.estado');

    // CRUD 3: proveedores
    Route::resource('proveedores', ProveedorController::class)->except(['show', 'destroy'])
        ->parameters(['proveedores' => 'proveedor']);
    Route::patch('proveedores/{proveedor}/estado', [ProveedorController::class, 'estado'])->name('proveedores.estado');

    // Clientes: solo ver y editar
    Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'edit', 'update'])
        ->parameters(['clientes' => 'cliente']);

    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::patch('pedidos/{pedido}/estado', [PedidoController::class, 'estado'])->name('pedidos.estado');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/password', [PerfilController::class, 'password'])->name('perfil.password');

    // CRUD 4: marcas
    Route::resource('marcas', MarcaController::class)->except(['show', 'destroy'])
        ->parameters(['marcas' => 'marca']);
    Route::patch('marcas/{marca}/estado', [MarcaController::class, 'estado'])->name('marcas.estado');

    // Proceso secundario: abastecimiento
    Route::get('abastecimiento', [AbastecimientoController::class, 'index'])->name('abastecimiento.index');
    Route::get('abastecimiento/create', [AbastecimientoController::class, 'create'])->name('abastecimiento.create');
    Route::post('abastecimiento', [AbastecimientoController::class, 'store'])->name('abastecimiento.store');
    Route::patch('abastecimiento/{orden}/recibir', [AbastecimientoController::class, 'recibir'])->name('abastecimiento.recibir');
    Route::patch('abastecimiento/{orden}/cancelar', [AbastecimientoController::class, 'cancelar'])->name('abastecimiento.cancelar');
});

// Guía PDF: responde 403 si no es admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin:403'])->group(function () {
    Route::get('abastecimiento/{orden}/guia', [AbastecimientoController::class, 'guia'])->name('abastecimiento.guia');
});
