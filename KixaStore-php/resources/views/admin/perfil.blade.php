 @extends('layouts.admin')

@section('titulo', 'Kixa Admin - Mi Perfil')

@section('contenido')
{{-- Perfil del administrador dentro del layout oscuro del panel --}}
<h1 class="text-2xl font-bold">Mi Perfil</h1>
<p class="text-sm kx-muted">Datos de tu cuenta de administrador.</p>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <form id="form-admin-perfil" action="{{ route('admin.perfil.update') }}" method="POST" enctype="multipart/form-data" class="kx-card p-6">
        @csrf
        @method('PUT')
        <h2 class="font-bold">Información de la cuenta</h2>
        <div class="mt-5 flex items-center gap-4">
            <div class="relative h-20 w-20 shrink-0">
                <img src="{{ $usuario->urlAvatar() ?? '' }}" alt="Foto de perfil" id="vista-imagen" class="h-20 w-20 rounded-full object-cover {{ $usuario->urlAvatar() ? '' : 'hidden' }}">
                <span id="avatar-inicial" class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600 text-2xl font-bold text-white {{ $usuario->urlAvatar() ? 'hidden' : '' }}">{{ strtoupper(substr($usuario->name, 0, 1)) }}</span>
            </div>
            <div>
                <input type="file" id="imagen-input" name="avatar" accept="image/*" class="hidden">
                <label for="imagen-input" class="kx-btn-ghost cursor-pointer"><i class="fa-solid fa-camera"></i>Cambiar foto</label>
                <p id="imagen-nombre" class="mt-1 text-xs kx-muted">JPG, PNG o WEBP · máx. 2 MB</p>
                @error('avatar') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-5 space-y-4">
            <div>
                <label for="name" class="kx-label">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}" class="kx-input" required maxlength="100">
                @error('name') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <p class="kx-label">Correo</p>
                <p class="kx-input cursor-not-allowed kx-muted">{{ $usuario->email }}</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar perfil</button>
        </div>
    </form>

    <form action="{{ route('admin.perfil.password') }}" method="POST" class="kx-card p-6">
        @csrf
        @method('PUT')
        <h2 class="font-bold">Contraseña</h2>
        <div class="mt-5 space-y-4">
            <div>
                <label for="password_actual" class="kx-label">Contraseña actual *</label>
                <input type="password" id="password_actual" name="password_actual" class="kx-input" required autocomplete="current-password">
                @error('password_actual') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="kx-label">Nueva contraseña *</label>
                <input type="password" id="password" name="password" class="kx-input" required minlength="8" autocomplete="new-password">
                @error('password') <p class="kx-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="kx-label">Repite la nueva contraseña *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="kx-input" required autocomplete="new-password">
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="kx-btn-primary"><i class="fa-solid fa-key"></i>Actualizar contraseña</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('imagen-input')?.addEventListener('change', function(e) {
        const archivo = e.target.files[0];
        if (!archivo) return;

        const nombre = document.getElementById('imagen-nombre');
        const vista = document.getElementById('vista-imagen');
        const inicial = document.getElementById('avatar-inicial');
        const form = document.getElementById('form-admin-perfil');

        if (nombre) nombre.textContent = `Subiendo: ${archivo.name}...`;
        if (vista) {
            vista.src = URL.createObjectURL(archivo);
            vista.classList.remove('hidden');
        }
        if (inicial) {
            inicial.classList.add('hidden');
        }

        // Envía el formulario automáticamente para guardar la foto de perfil en la base de datos
        if (form) {
            form.requestSubmit();
        }
    });
</script>
@endpush
