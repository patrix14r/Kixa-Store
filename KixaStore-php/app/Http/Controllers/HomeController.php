<?php

namespace App\Http\Controllers;

use App\Models\Producto;

// [Semana 9-10: Controladores, Request y Componentes] Controlador del home: arma las colecciones con scopes y las manda a la vista con compact().
class HomeController extends Controller
{
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