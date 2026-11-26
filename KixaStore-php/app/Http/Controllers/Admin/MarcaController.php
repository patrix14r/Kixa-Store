<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// [Sem 13 - CRUDs, Query Builder y Paginación]: CRUD 4 (Marcas): Route::resource sin show ni destroy, validate(), paginate() y baja lógica con estado().
class MarcaController extends Controller
{
    // index (GET): buscador por nombre, withCount('productos') para la columna "Productos" y paginación que conserva el filtro.
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $marcas = Marca::withCount('productos')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.marcas.index', compact('marcas', 'buscar'));
    }

    // create (GET): manda una Marca vacía para que el parcial _formulario use old() y $marca->campo.
    public function create()
    {
        return view('admin.marcas.create', ['marca' => new Marca(['activo' => true])]);
    }

    // store (POST): valida, genera el slug y guarda el logo si se subió.
    public function store(Request $request)
    {
        Marca::create($this->validar($request));

        return redirect()->route('admin.marcas.index')->with('exito', 'Marca creada correctamente.');
    }

    // edit (GET): Laravel resuelve Marca $marca por el slug (getRouteKeyName).
    public function edit(Marca $marca)
    {
        return view('admin.marcas.edit', compact('marca'));
    }

    // update (PUT): DB::transaction actualiza la marca y sincroniza el texto 'marca' de sus productos (lo usa el catálogo del cliente).
    public function update(Request $request, Marca $marca)
    {
        $datos = $this->validar($request, $marca);

        DB::transaction(function () use ($marca, $datos) {
            $marca->update($datos);
            $marca->productos()->update(['marca' => $marca->nombre]);
        });

        return redirect()->route('admin.marcas.index')->with('exito', 'Marca actualizada correctamente.');
    }

    // Baja lógica: una marca tiene productos (FK restrictOnDelete), así que se activa/desactiva en vez de borrarla.
    public function estado(Marca $marca)
    {
        $marca->update(['activo' => ! $marca->activo]);

        $texto = $marca->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Marca {$marca->nombre} {$texto}.");
    }

    // Reglas compartidas por store y update; el logo se guarda en public/images/brands.
    private function validar(Request $request, ?Marca $marca = null): array
    {
        $datos = $request->validate([
            'nombre'       => ['required', 'string', 'max:60', Rule::unique('marcas', 'nombre')->ignore($marca)],
            'logo_archivo' => ['nullable', 'image', 'max:2048'],
        ], [], ['logo_archivo' => 'logo']);

        $datos['slug'] = Str::slug($datos['nombre']);

        // Str::slug puede quedar vacío (nombre "!!!") o demasiado largo: ambos casos romperían la URL /admin/marcas/{slug}.
        if ($datos['slug'] === '' || mb_strlen($datos['slug']) > 70) {
            throw ValidationException::withMessages(['nombre' => $datos['slug'] === ''
                ? 'El nombre debe contener letras o números para generar el slug.'
                : 'El slug generado no puede superar los 70 caracteres.']);
        }

        // El slug también debe ser único (p. ej. "New-Balance" y "New Balance" darían el mismo).
        if (Marca::where('slug', $datos['slug'])->when($marca, fn ($q) => $q->whereKeyNot($marca->id))->exists()) {
            throw ValidationException::withMessages(['nombre' => 'Ya existe una marca con un nombre equivalente.']);
        }

        if ($request->hasFile('logo_archivo')) {
            $archivo = $request->file('logo_archivo');
            $nombre = $datos['slug'] . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/brands'), $nombre);
            $datos['logo'] = 'images/brands/' . $nombre;
        }

        unset($datos['logo_archivo']);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
