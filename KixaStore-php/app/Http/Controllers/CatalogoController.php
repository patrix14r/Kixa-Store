<?php

namespace App\Http\Controllers;

// [CAMBIO: IMPORTACION DE MODELOS CATEGORIA Y PRODUCTO]
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    // [CAMBIO: METODO INDEX PARA LISTAR PRODUCTOS CON FILTROS Y BUSQUEDA GENERAL]
    public function index(Request $request, $categoria = 'todos')
    {
        $subcategoria = $request->query('sub');
        $coleccion    = $request->query('coleccion');
        $marca        = $request->query('marca');
        $talla        = $request->query('talla');
        $buscar       = $request->query('buscar');

        // [CAMBIO: CONSULTA BASE CON SCOPE ACTIVOS Y CARGA IMPACIENTE]
        $query = Producto::activos()->with(['categoria', 'tallas']);

        // [CAMBIO: BUSCADOR GENERAL SIN RESTRINGIR A UNA SOLA CATEGORIA SI BUSCO DESDE EL HEADER]
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('marca', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        } elseif ($categoria && $categoria !== 'todos') {
            // [CAMBIO: FILTRO POR CATEGORIA SLUG SOLO SI NO SE ESTA BUSCANDO GENERALMENTE]
            $catObj = Categoria::where('slug', $categoria)->first();
            if ($catObj) {
                $query->where('categoria_id', $catObj->id);
            }
        }

        // [CAMBIO: FILTRO OPCIONAL POR SUBCATEGORIA]
        if ($subcategoria) {
            $query->where('subcategoria', $subcategoria);
        }

        // [CAMBIO: FILTRO OPCIONAL POR MARCA]
        if ($marca) {
            $query->where('marca', $marca);
        }

        // [CAMBIO: FILTRO POR TALLA DISPONIBLE CON STOCK MAYOR A CERO]
        if ($talla) {
            $query->whereHas('tallas', fn($q) => $q->where('talla', $talla)->where('stock', '>', 0));
        }

        $productos = $query->get();

        return view('cliente.catalogo', compact('productos', 'categoria', 'subcategoria', 'coleccion', 'marca', 'talla', 'buscar'));
    }

    // [CAMBIO: METODO SHOW PARA EL DETALLE DEL PRODUCTO Y SUS RECOMENDADOS]
    public function show($sku = null)
    {
        // [CAMBIO: BUSQUEDA POR SKU O ID CON RELACIONES COMPLETAS]
        $producto = null;
        if ($sku) {
            $producto = Producto::with(['categoria', 'proveedor', 'tallas'])
                ->where('sku', $sku)
                ->orWhere('id', $sku)
                ->first();
        }

        // [CAMBIO: FALLBACK SEGURO SI NO EXISTE EL PRODUCTO]
        if (!$producto) {
            $producto = Producto::with(['categoria', 'proveedor', 'tallas'])->first();
        }

        // [CAMBIO: OBTENCION DE 4 RECOMENDADOS DE LA MISMA CATEGORIA]
        $recomendados = Producto::activos()
            ->where('id', '!=', $producto->id)
            ->where('categoria_id', $producto->categoria_id)
            ->take(4)
            ->get();

        // [CAMBIO: COMPLETAR RECOMENDADOS SI LA CATEGORIA TIENE MENOS DE 4]
        if ($recomendados->count() < 4) {
            $faltantes = 4 - $recomendados->count();
            $adicionales = Producto::activos()
                ->where('id', '!=', $producto->id)
                ->whereNotIn('id', $recomendados->pluck('id'))
                ->take($faltantes)
                ->get();
            $recomendados = $recomendados->merge($adicionales);
        }

        return view('cliente.producto', compact('producto', 'recomendados'));
    }
}
