@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva categoría')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva categoría</h1>
<form action="{{ route('admin.categorias.store') }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] create y store de Route::resource: el formulario hace POST a admin.categorias.store y reutiliza el parcial _formulario con @include. --}}
    @include('admin.categorias._formulario')
</form>
@endsection
