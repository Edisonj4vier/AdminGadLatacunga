<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if(Session::has('access_token')) {
            return redirect()->route('app-lector-ruta.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required',
            'contrasena' => 'required',
        ]);

        try {
            $response = Http::post(config('services.api.url') . '/login/', [
                'nombre_usuario' => $request->nombre_usuario,
                'contrasena' => $request->contrasena,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Session::put('access_token', $data['access_token']);
                Session::put('user_id', $data['user_id']);
                return redirect()->route('app-lector-ruta.index')->with('success', 'Inicio de sesión exitoso');
            } else {
                return back()->withErrors(['error' => 'Credenciales incorrectas']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al conectar con el servidor']);
        }
    }

    public function logout()
    {
        Session::forget(['access_token', 'user_id']);
        return redirect()->route('login')->with('success', 'Sesión cerrada');
    }
}
