<?php

namespace App\Http\Controllers;

use App\Models\Producto;

// [Sem 09 - Controladores y MVC]: Controlador del Home; prepara la sección "Lo Destacado"
class HomeController extends Controller
{
    public function index()
    {
        // [Actualizado: Lo Destacado] [Sem 12 - Eloquent ORM]: scopes activos() y destacados() -> productos con destacado = true
        $destacados = Producto::activos()->destacados()
            ->whereHas('categoria', fn ($c) => $c->where('activo', true))
            ->with('categoria')
            ->latest('id')
            ->get();

        return view('cliente.home', compact('destacados'));
    }
}
