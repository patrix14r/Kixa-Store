<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

// Perfil del administrador
class PerfilController extends Controller
{
    // Muestra el perfil del admin
    public function edit()
    {
        return view('admin.perfil', ['usuario' => Auth::user()]);
    }

    // Actualiza datos y foto del admin
    public function update(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'avatar.image' => 'La foto debe ser una imagen (JPG, PNG, WEBP...).',
            'avatar.max'   => 'La foto no debe pesar más de 2 MB.',
        ]);

        $usuario->name = $request->name;

        if ($request->hasFile('avatar')) {
            if ($usuario->avatar && str_starts_with($usuario->avatar, 'images/avatars/') && File::exists(public_path($usuario->avatar))) {
                File::delete(public_path($usuario->avatar));
            }
            $archivo = $request->file('avatar');
            $nombre = 'avatar-' . $usuario->id . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/avatars'), $nombre);
            $usuario->avatar = 'images/avatars/' . $nombre;
        }

        $usuario->save();

        return back()->with('exito', 'Perfil actualizado correctamente.');
    }

    // Cambia la contraseña del admin
    public function password(Request $request)
    {
        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('exito', 'Contraseña actualizada.');
    }
}
