@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva marca')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva marca</h1>
<form action="{{ route('admin.marcas.store') }}" method="POST" enctype="multipart/form-data" class="kx-card mt-6 p-6">
    @csrf
    @include('admin.marcas._formulario')
</form>
@endsection
