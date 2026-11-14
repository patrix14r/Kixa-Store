@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar categoría')

@section('contenido')
<h1 class="text-2xl font-bold">Editar categoría: {{ $categoria->nombre }}</h1>
<form action="{{ route('admin.categorias.update', $categoria) }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    @method('PUT')
    @include('admin.categorias._formulario')
</form>
@endsection
