<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// [Sem 09 - Controladores y Middleware]: Controlador invocable que genera la boleta PDF de un pedido con barryvdh/laravel-dompdf.
class BoletaController extends Controller
{
    // Datos fijos del emisor que se imprimen en la cabecera de la boleta (también los usa la Guía de Abastecimiento).
    public const EMPRESA = [
        'nombre'    => 'KixaStore',
        'direccion' => 'Av. Cascanueces 2221, Santa Anita, Lima',
        'web'       => 'kixastore.pe',
    ];

    // GET /pedidos/{pedido}/boleta: {pedido} se resuelve por codigo_orden (Pedido::getRouteKeyName), sin exponer IDs.
    // Sin parámetros abre la vista previa en el navegador; con ?descargar=1 baja el archivo.
    public function __invoke(Request $request, Pedido $pedido)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        // [Sem 09 - Controladores y Middleware]: solo el dueño del pedido o un administrador pueden verla; cualquier otro recibe 403.
        abort_unless($pedido->user_id === $usuario->id || $usuario->esAdmin(), 403);

        // [Sem 12 - Eloquent ORM]: load() trae cliente y líneas con su producto en pocas consultas (evita N+1 al recorrer la tabla).
        $pedido->load(['usuario', 'items.producto']);

        $empresa = self::EMPRESA;
        $logo = public_path('images/ui/web.jpg');

        // loadView() renderiza la vista Blade y dompdf la convierte a PDF.
        // isFontSubsettingEnabled embebe solo las letras usadas (~35 KB en vez de ~900 KB).
        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])
            ->loadView('comprobantes.boleta', compact('pedido', 'empresa', 'logo'))
            ->setPaper('a5');

        $archivo = "Boleta-{$pedido->codigo_orden}.pdf";

        // [Sem 09 - Controladores y Middleware]: download() envía "attachment" (descarga) y stream() envía "inline" (vista previa en la pestaña).
        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }
}
