<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\View\View;

// [Sem 09 - Controladores y MVC]: Controlador invocable (un solo método __invoke) conectado con Route::get('/nosotros', NosotrosController::class)
class NosotrosController extends Controller
{
    // Muestra la página "Acerca de" con historia, ayuda y el mapa "Ubícanos".
    // El FAQ de envíos recibe la regla desde Pedido (misma que el carrito y el checkout).
    public function __invoke(): View
    {
        return view('informativas.nosotros', ['reglaEnvio' => Pedido::textoReglaEnvio()]);
    }
}
