<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// [Sem 09 - Controladores y MVC]: Mantenimiento 1 (Categorías): index, create, store, edit, update y cambio de estado
class CategoriaCrudController extends Controller
{
    public function index()
    {
        // [Sem 12 - Eloquent ORM]: withCount('productos') agrega la columna productos_count sin consultas extra
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categorias.create', ['categoria' => new Categoria(['activo' => true])]);
    }

    public function store(Request $request)
    {
        Categoria::create($this->validar($request));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validar($request, $categoria));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría actualizada correctamente.');
    }

    // En vez de borrar (rompería la FK restrictOnDelete de productos) se activa o desactiva la categoría
    public function estado(Categoria $categoria)
    {
        $categoria->update(['activo' => ! $categoria->activo]);

        $texto = $categoria->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Categoría {$categoria->nombre} {$texto}.");
    }

    // [Sem 09 - Controladores y MVC]: Validación del Request; el slug se genera desde el nombre si viene vacío
    private function validar(Request $request, ?Categoria $categoria = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('nombre', ''))]);

        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'max:50', Rule::unique('categorias', 'nombre')->ignore($categoria)],
            'slug'        => ['required', 'string', 'max:60', Rule::unique('categorias', 'slug')->ignore($categoria)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
