@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Clientes')

@section('contenido')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Clientes</h1>
        <p class="text-sm kx-muted">{{ $clientes->total() }} clientes registrados en la tienda.</p>
    </div>
    <form action="{{ route('admin.clientes.index') }}" method="GET" class="flex gap-2 sm:w-80">
        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo o DNI" class="kx-input">
        <button type="submit" class="kx-btn-primary px-3" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</div>

<div class="kx-card mt-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th class="kx-th">Nombre</th>
                <th class="kx-th">Correo</th>
                <th class="kx-th">DNI</th>
                <th class="kx-th">Teléfono</th>
                <th class="kx-th">Pedidos</th>
                <th class="kx-th">Registro</th>
                <th class="kx-th text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clientes as $cliente)
                <tr class="kx-row">
                    <td class="kx-td">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-xs font-bold">{{ strtoupper(substr($cliente->name, 0, 1)) }}</span>
                            <span class="font-medium">{{ $cliente->name }}</span>
                        </div>
                    </td>
                    <td class="kx-td kx-muted">{{ $cliente->email }}</td>
                    <td class="kx-td">{{ $cliente->dni ?? '—' }}</td>
                    <td class="kx-td">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="kx-td font-semibold">{{ $cliente->pedidos_count }}</td>
                    <td class="kx-td kx-muted">{{ $cliente->created_at?->format('d/m/Y') }}</td>
                    <td class="kx-td">
                        <div class="flex justify-end gap-1.5">
                            <a href="{{ route('admin.clientes.show', $cliente) }}" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5" title="Historial de pedidos"><i class="fa-solid fa-clock-rotate-left"></i><span class="hidden xl:inline">Historial</span></a>
                            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="kx-btn-ghost whitespace-nowrap px-3 py-1.5" title="Editar datos"><i class="fa-solid fa-pen"></i><span class="hidden xl:inline">Editar</span></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="kx-td py-8 text-center kx-muted">No se encontraron usuarios.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $clientes->links() }}</div>
@endsection
