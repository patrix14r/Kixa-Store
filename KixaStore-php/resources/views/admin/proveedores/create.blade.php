@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo proveedor')

@section('contenido')
<h1 class="text-2xl font-bold">Nuevo proveedor</h1>
<form action="{{ route('admin.proveedores.store') }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    @include('admin.proveedores._formulario')
</form>
@endsection
