@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Nueva talla')

@section('contenido')
<h1 class="text-2xl font-bold">Nueva talla</h1>
<form action="{{ route('admin.tallas.store') }}" method="POST" class="kx-card mt-6 p-6">
    @csrf
    @include('admin.tallas._formulario')
</form>
@endsection
