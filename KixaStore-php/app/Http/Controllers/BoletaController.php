<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Genera la boleta PDF del pedido
class BoletaController extends Controller
{
    // Datos de la empresa para los PDF
    public const EMPRESA = [
        'nombre'    => 'KixaStore',
        'direccion' => 'Av. Cascanueces 2221, Santa Anita, Lima',
        'web'       => 'kixastore.pe',
    ];

    // Muestra o descarga la boleta
    public function __invoke(Request $request, Pedido $pedido)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        // Solo el dueño o un admin
        abort_unless($pedido->user_id === $usuario->id || $usuario->esAdmin(), 403);

        // Carga cliente y productos del pedido
        $pedido->load(['usuario', 'items.producto']);

        $empresa = self::EMPRESA;
        $logo = public_path('images/ui/web.jpg');

        // Arma el PDF desde la vista Blade
        $pdf = Pdf::setOption(['isFontSubsettingEnabled' => true])
            ->loadView('comprobantes.boleta', compact('pedido', 'empresa', 'logo'))
            ->setPaper('a5');

        $archivo = "Boleta-{$pedido->codigo_orden}.pdf";

        // Descarga o vista previa en el navegador
        return $request->boolean('descargar') ? $pdf->download($archivo) : $pdf->stream($archivo);
    }
}
