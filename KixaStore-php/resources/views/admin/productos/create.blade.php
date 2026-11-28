@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo producto')

@section('contenido')
<h1 class="mb-6 text-2xl font-bold">Nuevo producto</h1>
<form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.productos._formulario')
</form>
@endsection
