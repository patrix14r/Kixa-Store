@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nuevo proveedor')

@section('contenido')
<h1 class="text-2xl font-bold">Nuevo proveedor</h1>
<form action="{{ route('admin.proveedores.store') }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Sem 12-13 - Eloquent ORM y Route::resource]: create y store; el formulario hace POST a admin.proveedores.store y reutiliza el parcial. --}}
    @include('admin.proveedores._formulario')
</form>
@endsection
