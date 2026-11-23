@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar marca')

@section('contenido')
<h1 class="text-2xl font-bold">Editar marca: {{ $marca->nombre }}</h1>
<form action="{{ route('admin.marcas.update', $marca) }}" method="POST" enctype="multipart/form-data" class="kx-card mt-6 p-6">
    @csrf
    {{-- [Sem 13 - CRUDs, Query Builder y Paginación]: edit y update; @method('PUT') simula el verbo PUT. --}}
    @method('PUT')
    @include('admin.marcas._formulario')
</form>
@endsection
