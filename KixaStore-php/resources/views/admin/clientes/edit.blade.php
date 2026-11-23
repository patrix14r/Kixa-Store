@extends('layouts.admin')

@section('titulo', 'Kixa Admin - Editar cliente')

@section('contenido')
{{-- Edición de datos básicos del cliente (sin cambiar rol ni contraseña) --}}
<a href="{{ route('admin.clientes.index') }}" class="text-sm kx-muted hover:text-white"><i class="fa-solid fa-arrow-left"></i> Clientes</a>
<h1 class="mt-2 text-2xl font-bold">Editar cliente</h1>

<form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" class="kx-card mt-6 max-w-3xl p-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="kx-label">Nombre completo *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $cliente->name) }}" class="kx-input" required maxlength="100">
            @error('name') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="kx-label">Correo *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $cliente->email) }}" class="kx-input" required>
            @error('email') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="dni" class="kx-label">DNI</label>
            <input type="text" id="dni" name="dni" value="{{ old('dni', $cliente->dni) }}" class="kx-input" maxlength="8" inputmode="numeric" placeholder="8 dígitos">
            @error('dni') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="telefono" class="kx-label">Celular</label>
            <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="kx-input" maxlength="9" inputmode="numeric" placeholder="9 dígitos">
            @error('telefono') <p class="kx-error">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.clientes.index') }}" class="kx-btn-ghost">Cancelar</a>
        <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar cambios</button>
    </div>
</form>
@endsection
