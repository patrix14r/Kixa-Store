<?php

namespace App\Http\Controllers;

use App\Models\Producto;

// Página de inicio de la tienda
class HomeController extends Controller
{
    // Productos destacados del home
    public function index()
    {
        $destacados = Producto::activos()->destacados()
            ->whereHas('categoria', fn ($c) => $c->where('activo', true))
            ->with('categoria')
            ->latest('id')
            ->get();
        //ahora la vista home se la pasa a la carpeta cliente
        return view('cliente.home', compact('destacados'));
    }
}