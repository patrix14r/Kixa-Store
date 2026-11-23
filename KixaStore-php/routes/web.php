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
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NosotrosController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de la tienda.
// [Semana 8: Rutas y Herencia Blade] Cada ruta tiene ->name() para llamarla con route('nombre') desde Blade; {categoria?} es un parámetro opcional.
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalogo/{categoria?}', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/producto/{sku?}', [CatalogoController::class, 'show'])->name('producto.detalle');
Route::get('/producto/{sku}/tallas', [CatalogoController::class, 'tallas'])->name('producto.tallas');

// Controlador invocable: una sola acción, por eso la ruta recibe solo la clase.
Route::get('/nosotros', NosotrosController::class)->name('nosotros');
Route::redirect('/ayuda', '/nosotros#centro-ayuda', 301)->name('ayuda');

// Login, registro y logout los manejan las rutas de Laravel Fortify (GET /login, GET|POST /register, POST /logout); ver FortifyServiceProvider.
// Redirige el antiguo /registro al nuevo /register para no romper enlaces.
Route::redirect('/registro', '/register');

// Cuenta del cliente.
// El middleware auth deja entrar solo a usuarios con sesión; group() evita repetirlo en cada ruta.
Route::middleware('auth')->group(function () {
    Route::get('/perfil/{seccion?}', [AuthController::class, 'perfil'])
        ->whereIn('seccion', ['cuenta', 'favoritos', 'pedidos', 'direcciones'])
        ->name('perfil');
    Route::put('/perfil', [AuthController::class, 'actualizarPerfil'])->name('perfil.update');
    Route::put('/perfil/password', [AuthController::class, 'actualizarPassword'])->name('perfil.password');
    Route::put('/perfil/direccion', [AuthController::class, 'actualizarDireccion'])->name('perfil.direccion');
});

// Proceso de compra.
// [Semana 9-10: Controladores, Request y Componentes] El carrito vive en el navegador y procesar() recibe el Request y valida todo en el servidor.
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
Route::view('/favoritos', 'cliente.favoritos')->name('favoritos');
Route::get('/checkout', [CarritoController::class, 'checkout'])->name('checkout');
Route::post('/checkout/procesar', [CarritoController::class, 'procesar'])->middleware('auth')->name('checkout.procesar');

// Protege los CRUDs permitiendo acceso solo al rol admin.
// [Semana 11: Migraciones, FK y Seeders] 'admin' es el alias de AdminMiddleware (bootstrap/app.php) y se apoya en la tabla roles creada por migración y RolSeeder.
// prefix('admin') antepone /admin a las URLs y name('admin.') antepone admin. a los nombres de ruta.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () { //prefix es 
    Route::get('/', [AdminController::class, 'home'])->name('home');

    // [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource]
    // Route::resource crea index, create, store, show, edit, update y destroy; aquí se usa except() porque show y destroy no existen
    // (las categorías se activan o desactivan con el PATCH de estado) y parameters() cambia {categorias} por {categoria} para el binding por slug.
    Route::resource('categorias', CategoriaController::class)->except(['show', 'destroy'])
        ->parameters(['categorias' => 'categoria']);
    Route::patch('categorias/{categoria}/estado', [CategoriaController::class, 'estado'])->name('categorias.estado');

    // Mismo patrón de Route::resource para productos; el binding usa el SKU definido en Producto::getRouteKeyName().
    Route::resource('productos', ProductoController::class)->except(['show', 'destroy'])
        ->parameters(['productos' => 'producto']);
    Route::patch('productos/{producto}/estado', [ProductoController::class, 'estado'])->name('productos.estado');

    // CRUD 3: proveedores. El plural irregular obliga a parameters() para que el parámetro sea {proveedor}; el binding usa el RUC.
    Route::resource('proveedores', ProveedorController::class)->except(['show', 'destroy'])
        ->parameters(['proveedores' => 'proveedor']);
    Route::patch('proveedores/{proveedor}/estado', [ProveedorController::class, 'estado'])->name('proveedores.estado');

    // only() deja solo los métodos necesarios: no hay crear ni borrar clientes (la rúbrica prohíbe CRUD de usuarios y roles).
    Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'edit', 'update'])
        ->parameters(['clientes' => 'cliente']);

    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::patch('pedidos/{pedido}/estado', [PedidoController::class, 'estado'])->name('pedidos.estado');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/password', [PerfilController::class, 'password'])->name('perfil.password');

    // [Sem 13 - CRUDs, Query Builder y Paginación]: CRUD 4 (marcas) con Route::resource sin show ni destroy; la baja lógica va por PATCH .../estado.
    // El binding usa el slug (Marca::getRouteKeyName), así la URL queda /admin/marcas/new-balance/edit sin exponer IDs.
    Route::resource('marcas', MarcaController::class)->except(['show', 'destroy'])
        ->parameters(['marcas' => 'marca']);
    Route::patch('marcas/{marca}/estado', [MarcaController::class, 'estado'])->name('marcas.estado');

    // Proceso secundario: abastecimiento. El parámetro {orden} se resuelve por código de lote (OrdenAbastecimiento::getRouteKeyName).
    Route::get('abastecimiento', [AbastecimientoController::class, 'index'])->name('abastecimiento.index');
    Route::get('abastecimiento/create', [AbastecimientoController::class, 'create'])->name('abastecimiento.create');
    Route::post('abastecimiento', [AbastecimientoController::class, 'store'])->name('abastecimiento.store');
    Route::patch('abastecimiento/{orden}/recibir', [AbastecimientoController::class, 'recibir'])->name('abastecimiento.recibir');
    Route::patch('abastecimiento/{orden}/cancelar', [AbastecimientoController::class, 'cancelar'])->name('abastecimiento.cancelar');
});
