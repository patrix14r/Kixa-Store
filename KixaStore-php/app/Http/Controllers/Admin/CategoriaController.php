<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] CRUD 1 (Categorías): Route::resource sin show ni destroy; los 5 métodos restantes más estado().
class CategoriaController extends Controller
{
    // index (GET): lista las categorías con withCount('productos') y sin columna ID.
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    // create (GET): formulario vacío; se manda una Categoria nueva para que el parcial _formulario use old() y $categoria->campo.
    public function create()
    {
        return view('admin.categorias.create', ['categoria' => new Categoria(['activo' => true])]);
    }

    // store (POST): validar() y Categoria::create(); solo se guardan los campos de #[Fillable].
    public function store(Request $request)
    {
        Categoria::create($this->validar($request));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría creada correctamente.');
    }

    // edit (GET): Laravel resuelve Categoria $categoria por el slug (getRouteKeyName) sin consultar a mano.
    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    // update (PUT): valida ignorando el propio registro en las reglas unique y actualiza.
    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validar($request, $categoria));

        return redirect()->route('admin.categorias.index')->with('exito', 'Categoría actualizada correctamente.');
    }

    // Activa o desactiva en vez de borrar: productos tiene FK restrictOnDelete hacia categorias.
    public function estado(Categoria $categoria)
    {
        $categoria->update(['activo' => ! $categoria->activo]);

        $texto = $categoria->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Categoría {$categoria->nombre} {$texto}.");
    }

    // Reglas compartidas por store y update; el slug se genera con Str::slug() si llega vacío.
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
