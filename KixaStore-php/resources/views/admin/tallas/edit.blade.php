@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar talla')

@section('contenido')
<h1 class="text-2xl font-bold">Editar talla {{ $talla->talla_etiqueta }}: {{ $talla->producto->nombre }}</h1>
<form action="{{ route('admin.tallas.update', [$talla->producto, $talla->talla]) }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    {{-- @method('PUT') simula el verbo PUT que el formulario HTML no soporta. --}}
    @method('PUT')
    @include('admin.tallas._formulario')
</form>
@endsection
