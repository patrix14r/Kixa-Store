<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*

|Cada ruta cuenta con un nombre (->name()) para facilitar su
| llamado desde las vistas Blade usando la función route('nombre').
*/

// [RUTA]: Panel de administración
Route::get('/admin', function () {
    return view('admin.home');
})->name('admin.home');

// [RUTA]: Página principal de la tienda (Home con carrusel y novedades)
Route::get('/', function () {
    return view('cliente.home');
})->name('home');

// [RUTA]: Catálogo general con filtros opcionales (categoría, subcategoría, colección, marca)
Route::get('/catalogo/{categoria?}', function (Request $request, $categoria = 'todos') {
    $subcategoria = $request->query('sub');
    $coleccion    = $request->query('coleccion');
    $marca        = $request->query('marca');
    return view('cliente.catalogo', compact('categoria', 'subcategoria', 'coleccion', 'marca'));
})->name('catalogo');

// [RUTA]: Detalle individual de un producto
Route::get('/producto/{id?}', function ($id = 1) {
    return view('cliente.producto', compact('id'));
})->name('producto.detalle');

// [RUTA]: Carrito de compras
Route::get('/carrito', function () {
    return view('cliente.carrito');
})->name('carrito');

// [RUTA]: Lista de favoritos
Route::get('/favoritos', function () {
    return view('cliente.favoritos');
})->name('favoritos');

// [RUTA]: Centro de ayuda y preguntas frecuentes hhh
Route::get('/ayuda', function () {
    return view('informativas.ayuda');
})->name('ayuda');

// [RUTA]: Página de contacto y soporte al cliente
Route::get('/contacto', function () {
    return view('informativas.contacto');
})->name('contacto');

// [RUTA]: Inicio de sesión de usuarios
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// [RUTA]: Registro de nuevos usuarios (separado de login)
Route::get('/registro', function () {
    return view('auth.registro');
})->name('registro');

// [RUTA]: Perfil y cuenta de usuario (separado de cuenta.html)
Route::get('/perfil', function () {
    return view('auth.perfil');
})->name('perfil');

// [RUTA]: Pasarela y confirmación de compra (checkout separado de carrito)
Route::get('/checkout', function () {
    return view('cliente.checkout');
})->name('checkout');

