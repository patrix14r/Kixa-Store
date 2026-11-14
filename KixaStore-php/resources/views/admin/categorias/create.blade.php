@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva categoría')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva categoría</h1>
<form action="{{ route('admin.categorias.store') }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    @include('admin.categorias._formulario')
</form>
@endsection
