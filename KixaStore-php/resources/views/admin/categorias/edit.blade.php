@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar categoría')

@section('contenido')
<h1 class="text-2xl font-bold">Editar categoría: {{ $categoria->nombre }}</h1>
<form action="{{ route('admin.categorias.update', $categoria) }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] edit y update de Route::resource: @method('PUT') simula el verbo PUT que el formulario HTML no soporta. --}}
    @method('PUT')
    {{-- El mismo parcial _formulario que usa create; aquí $categoria ya trae los datos guardados. --}}
    @include('admin.categorias._formulario')
</form>
@endsection
