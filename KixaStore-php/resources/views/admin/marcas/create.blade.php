@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva marca')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva marca</h1>
{{-- enctype multipart/form-data es obligatorio para subir el logo. --}}
<form action="{{ route('admin.marcas.store') }}" method="POST" enctype="multipart/form-data" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Sem 13 - CRUDs, Query Builder y Paginación]: create y store; el formulario hace POST a admin.marcas.store y reutiliza el parcial. --}}
    @include('admin.marcas._formulario')
</form>
@endsection
