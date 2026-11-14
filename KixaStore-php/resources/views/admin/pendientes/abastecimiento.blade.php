@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Abastecimiento')

@section('contenido')
@include('admin.pendientes._modulo', [
    'titulo' => 'Abastecimiento',
    'subtitulo' => 'Reposición de inventario con proveedores.',
])
@endsection
