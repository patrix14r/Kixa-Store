@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar proveedor')

@section('contenido')
<h1 class="text-2xl font-bold">Editar proveedor: {{ $proveedor->razon_social }}</h1>
<form action="{{ route('admin.proveedores.update', $proveedor) }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Sem 12-13 - Eloquent ORM y Route::resource]: edit y update; @method('PUT') simula el verbo PUT. --}}
    @method('PUT')
    @include('admin.proveedores._formulario')
</form>
@endsection
