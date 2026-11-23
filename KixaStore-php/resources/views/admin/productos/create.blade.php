@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo producto')

@section('contenido')
<h1 class="mb-6 text-2xl font-bold">Nuevo producto</h1>
<form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    {{-- [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] create y store de Route::resource: enctype multipart permite subir archivos de imagen; el parcial _formulario se comparte con edit. --}}
    @include('admin.productos._formulario')
</form>
@endsection
