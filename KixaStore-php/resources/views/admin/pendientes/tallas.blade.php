@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Tallas y Stock')

@section('contenido')
@include('admin.pendientes._modulo', [
    'titulo' => 'Tallas y Stock',
    'subtitulo' => 'Inventario por talla de cada zapatilla.',
])
@endsection
