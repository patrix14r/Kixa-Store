<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CRUD 3: proveedores
class ProveedorController extends Controller
{
    // Lista proveedores con buscador y paginación
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $proveedores = Proveedor::withCount('productos')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('razon_social', 'like', "%{$buscar}%")
                ->orWhere('ruc', 'like', "%{$buscar}%")))
            ->orderBy('razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('admin.proveedores.index', compact('proveedores', 'buscar'));
    }

    // Muestra el formulario vacío
    public function create()
    {
        return view('admin.proveedores.create', ['proveedor' => new Proveedor(['activo' => true])]);
    }

    // Guarda un nuevo proveedor
    public function store(Request $request)
    {
        Proveedor::create($this->validar($request));

        return redirect()->route('admin.proveedores.index')->with('exito', 'Proveedor creado correctamente.');
    }

    // Muestra el formulario de edición
    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.edit', compact('proveedor'));
    }

    // Actualiza el proveedor
    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validar($request, $proveedor));

        return redirect()->route('admin.proveedores.index')->with('exito', 'Proveedor actualizado correctamente.');
    }

    // Activa o desactiva (baja lógica)
    public function estado(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        $texto = $proveedor->activo ? 'activado' : 'desactivado';

        return back()->with('exito', "Proveedor {$proveedor->razon_social} {$texto}.");
    }

    // Reglas de validación compartidas
    private function validar(Request $request, ?Proveedor $proveedor = null): array
    {
        $datos = $request->validate([
            'razon_social'    => ['required', 'string', 'max:100'],
            'ruc'             => ['required', 'digits:11', Rule::unique('proveedores', 'ruc')->ignore($proveedor)],
            'contacto_nombre' => ['nullable', 'string', 'max:80'],
            'telefono'        => ['nullable', 'digits:9'],
            'correo'          => ['nullable', 'email', 'max:100'],
            'direccion'       => ['nullable', 'string', 'max:200'],
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
