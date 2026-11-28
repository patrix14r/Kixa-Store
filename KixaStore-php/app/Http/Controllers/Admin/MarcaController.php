<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CRUD 4: marcas
class MarcaController extends Controller
{
    // Lista marcas con buscador y paginación
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

    // Muestra el formulario vacío
    public function create()
    {
        return view('admin.marcas.create', ['marca' => new Marca(['activo' => true])]);
    }

    // Guarda una nueva marca
    public function store(Request $request)
    {
        Marca::create($this->validar($request));

        return redirect()->route('admin.marcas.index')->with('exito', 'Marca creada correctamente.');
    }

    // Muestra el formulario de edición
    public function edit(Marca $marca)
    {
        return view('admin.marcas.edit', compact('marca'));
    }

    // Actualiza la marca y sus productos
    public function update(Request $request, Marca $marca)
    {
        $datos = $this->validar($request, $marca);

        DB::transaction(function () use ($marca, $datos) {
            $marca->update($datos);
            $marca->productos()->update(['marca' => $marca->nombre]);
        });

        return redirect()->route('admin.marcas.index')->with('exito', 'Marca actualizada correctamente.');
    }

    // Activa o desactiva (baja lógica)
    public function estado(Marca $marca)
    {
        $marca->update(['activo' => ! $marca->activo]);

        $texto = $marca->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Marca {$marca->nombre} {$texto}.");
    }

    // Valida datos y guarda el logo
    private function validar(Request $request, ?Marca $marca = null): array
    {
        $datos = $request->validate([
            'nombre'       => ['required', 'string', 'max:50', Rule::unique('marcas', 'nombre')->ignore($marca)],
            'logo_archivo' => ['nullable', 'image', 'max:2048'],
        ], [], ['logo_archivo' => 'logo']);

        $datos['slug'] = Str::slug($datos['nombre']);

        // Slug no vacío ni muy largo
        if ($datos['slug'] === '' || mb_strlen($datos['slug']) > 70) {
            throw ValidationException::withMessages(['nombre' => $datos['slug'] === ''
                ? 'El nombre debe contener letras o números para generar el slug.'
                : 'El slug generado no puede superar los 70 caracteres.']);
        }

        // El slug también debe ser único
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
