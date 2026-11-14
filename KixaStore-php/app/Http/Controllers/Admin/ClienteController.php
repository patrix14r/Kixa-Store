<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// [Sem 09 - Controladores y MVC]: Clientes del admin: listado, historial de pedidos y edición de datos básicos (Regla 4.2: no gestiona roles)
class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));

        // [Sem 12 - Eloquent ORM]: whereHas('rol') deja solo a los clientes y withCount('pedidos') cuenta sus compras
        $clientes = User::whereHas('rol', fn ($r) => $r->where('nombre', 'cliente'))
            ->withCount('pedidos')
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$buscar}%")
                ->orWhere('email', 'like', "%{$buscar}%")
                ->orWhere('dni', 'like', "%{$buscar}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.clientes.index', compact('clientes', 'buscar'));
    }

    // Historial de pedidos del cliente con el detalle de zapatillas
    public function show(User $cliente)
    {
        abort_if($cliente->esAdmin(), 404);

        $pedidos = $cliente->pedidos()->with('items.producto')->latest()->get();

        return view('admin.clientes.show', compact('cliente', 'pedidos'));
    }

    public function edit(User $cliente)
    {
        abort_if($cliente->esAdmin(), 404);

        return view('admin.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, User $cliente)
    {
        abort_if($cliente->esAdmin(), 404);

        // [Sem 09 - Controladores y MVC]: Validación del Request (DNI 8 dígitos, celular 9 dígitos, correo único)
        $datos = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($cliente)],
            'dni'      => ['nullable', 'regex:/^[0-9]{8}$/'],
            'telefono' => ['nullable', 'regex:/^[0-9]{9}$/'],
        ], [
            'dni.regex'      => 'El DNI debe tener exactamente 8 dígitos.',
            'telefono.regex' => 'El celular debe tener exactamente 9 dígitos.',
        ]);

        $cliente->update($datos);

        return redirect()->route('admin.clientes.index')->with('exito', "Datos de {$cliente->name} actualizados.");
    }
}
