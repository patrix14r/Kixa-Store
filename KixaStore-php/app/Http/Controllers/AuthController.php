<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

// [Semana 9-10: Controladores, Request y Componentes] Perfil del cliente; login, registro y logout los maneja Laravel Fortify.
class AuthController extends Controller
{
    // Una sola vista con pestañas; la sección llega por la ruta /perfil/{seccion?}.
    public function perfil(?string $seccion = null)
    {
        $usuario = Auth::user();
        $seccion = $seccion ?? 'cuenta';
        $pedidos = Pedido::with('items.producto')->where('user_id', $usuario->id)->latest()->get();
        $pasos   = Pedido::PASOS;

        return view('auth.perfil', compact('usuario', 'pedidos', 'seccion', 'pasos'));
    }

    public function actualizarPassword(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.min'                     => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'               => 'La confirmación de la contraseña no coincide.',
        ]);

        $usuario->update(['password' => Hash::make($request->password)]);

        return redirect()->route('perfil')->with('exito', 'Tu contraseña se actualizó correctamente.');
    }

    public function actualizarDireccion(Request $request)
    {
        $datos = $request->validate([
            'direccion' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        Auth::user()->update($datos);

        return redirect()->route('perfil', 'direcciones')->with('exito', 'Dirección predeterminada guardada.');
    }

    public function actualizarPerfil(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:8'],
            'dni'      => ['nullable', 'regex:/^[0-9]{8}$/'],
            'telefono' => ['nullable', 'regex:/^[0-9]{9}$/'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
        ], [
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'avatar.image' => 'La foto de perfil debe ser una imagen (JPG, PNG, WEBP...).',
            'avatar.max'   => 'La foto de perfil no debe pesar más de 2 MB.',
        ]);

        $usuario->name  = $request->name;
        $usuario->email = $request->email;
        if ($request->has('dni')) {
            $usuario->dni = $request->dni ?: null;
        }
        if ($request->has('telefono')) {
            $usuario->telefono = $request->telefono ?: null;
        }

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

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

        return back()->with('exito', 'Tus datos se actualizaron en la base de datos.');
    }
}
