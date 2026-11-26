<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// [Semana 9-10: Controladores, Request y Componentes] Clientes del admin (solo lectura y edición básica); no gestiona roles ni crea usuarios.
class ClienteController extends Controller
{
    // Lista solo usuarios con rol cliente usando el scope role() de Spatie y withCount('pedidos').
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));

        $clientes = User::role('cliente')
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
