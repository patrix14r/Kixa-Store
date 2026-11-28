<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// CRUD 1: categorías
class CategoriaController extends Controller
{
    // Lista las categorías con su conteo
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    // Muestra el formulario vacío
    public function create()
    {
        return view('admin.categorias.create', ['categoria' => new Categoria(['activo' => true])]);
    }

    // Guarda una nueva categoría
    public function store(Request $request)
    {
        Categoria::create($this->validar($request));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría creada correctamente.');
    }

    // Muestra el formulario de edición
    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    // Actualiza la categoría
    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validar($request, $categoria));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría actualizada correctamente.');
    }

    // Activa o desactiva (baja lógica)
    public function estado(Categoria $categoria)
    {
        $categoria->update(['activo' => ! $categoria->activo]);

        $texto = $categoria->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Categoría {$categoria->nombre} {$texto}.");
    }

    // Reglas de validación compartidas
    private function validar(Request $request, ?Categoria $categoria = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('nombre', ''))]);

        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'max:50', Rule::unique('categorias', 'nombre')->ignore($categoria)],
            // Slug obligatorio, máximo 70, no "todos"
            'slug'        => ['required', 'string', 'max:70', Rule::notIn(['todos']), Rule::unique('categorias', 'slug')->ignore($categoria)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'slug.required' => 'El nombre debe contener letras o números para generar el slug.',
            'slug.max'      => 'El slug no puede superar los 70 caracteres.',
            'slug.not_in'   => 'El slug "todos" está reservado para el catálogo completo.',
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
