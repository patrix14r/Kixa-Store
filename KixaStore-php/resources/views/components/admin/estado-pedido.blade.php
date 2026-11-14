@props(['estado'])
{{-- [Sem 08 - Rutas y Vistas Blade]: Componente <x-admin.estado-pedido> con el color de cada estado --}}
<span {{ $attributes->class([
    'whitespace-nowrap',
    'kx-badge-bajo' => $estado === 'Pendiente',
    'kx-badge-neutro' => in_array($estado, ['Pagado', 'Enviado']),
    'kx-badge-ok' => $estado === 'Entregado',
    'kx-badge-agotado' => $estado === 'Cancelado',
]) }}>{{ $estado }}</span>
