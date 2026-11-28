@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar producto')

@section('contenido')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-2xl font-bold">Editar: {{ $producto->modelo }} <span class="kx-badge-neutro align-middle">{{ $producto->marca }}</span></h1>
    <a href="{{ route('producto.detalle', $producto->sku) }}" class="kx-btn-ghost" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i>Ver en tienda</a>
</div>
<form action="{{ route('admin.productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.productos._formulario')
</form>
@endsection
