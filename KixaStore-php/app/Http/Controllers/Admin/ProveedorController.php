<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// [Sem 12-13 - Eloquent ORM y Route::resource]: CRUD 3 (Proveedores): Route::resource sin show ni destroy más estado() para la baja lógica.
class ProveedorController extends Controller
{
    // index (GET): buscador por razón social o RUC, withCount('productos') y paginación que conserva el filtro.
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $proveedores = Proveedor::withCount('productos')
            // when() aplica el where solo si hay texto; el grupo evita que el orWhere rompa otros filtros.
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('razon_social', 'like', "%{$buscar}%")
                ->orWhere('ruc', 'like', "%{$buscar}%")))
            ->orderBy('razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('admin.proveedores.index', compact('proveedores', 'buscar'));
    }

    // create (GET): manda un Proveedor vacío para que el parcial _formulario use old() y $proveedor->campo.
    public function create()
    {
        return view('admin.proveedores.create', ['proveedor' => new Proveedor(['activo' => true])]);
    }

    // store (POST): validar() y create(); solo se guardan los campos de #[Fillable].
    public function store(Request $request)
    {
        Proveedor::create($this->validar($request));

        return redirect()->route('admin.proveedores.index')->with('exito', 'Proveedor creado correctamente.');
    }

    // edit (GET): Laravel resuelve Proveedor $proveedor por el RUC (getRouteKeyName).
    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.edit', compact('proveedor'));
    }

    // update (PUT): el RUC único ignora al propio registro.
    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validar($request, $proveedor));

        return redirect()->route('admin.proveedores.index')->with('exito', 'Proveedor actualizado correctamente.');
    }

    // Baja lógica: un proveedor tiene productos y órdenes de abastecimiento (FK), así que se activa/desactiva en vez de borrar.
    public function estado(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        $texto = $proveedor->activo ? 'activado' : 'desactivado';

        return back()->with('exito', "Proveedor {$proveedor->razon_social} {$texto}.");
    }

    // Reglas compartidas por store y update.
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
