@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Proveedores')

@section('contenido')
@include('admin.pendientes._modulo', [
    'titulo' => 'Proveedores',
    'subtitulo' => 'Marcas y distribuidores oficiales de la tienda.',
])
@endsection
