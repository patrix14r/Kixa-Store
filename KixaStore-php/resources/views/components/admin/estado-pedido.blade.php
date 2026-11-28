@props(['estado'])
{{-- Etiqueta de color por estado --}}
<span {{ $attributes->class([
    'whitespace-nowrap',
    'kx-badge-bajo' => $estado === 'Pendiente',
    'kx-badge-neutro' => in_array($estado, ['Pagado', 'Enviado']),
    'kx-badge-ok' => $estado === 'Entregado',
    'kx-badge-agotado' => $estado === 'Cancelado',
]) }}>{{ $estado }}</span>
