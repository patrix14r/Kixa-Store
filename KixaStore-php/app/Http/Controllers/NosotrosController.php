<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\View\View;

// Página Acerca de (controlador invocable)
class NosotrosController extends Controller
{
    // Muestra historia, ayuda y ubicación
    public function __invoke(): View
    {
        return view('informativas.nosotros', ['reglaEnvio' => Pedido::textoReglaEnvio()]);
    }
}
