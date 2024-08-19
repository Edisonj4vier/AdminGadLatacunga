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
                Session::put('username', $data['username']);
                Session::put('token_type', $data['token_type']);
                return redirect()->route('app-lector-ruta.index')->with('success', 'Inicio de sesión exitoso');
            } else {
                return back()->withInput()->withErrors(['login' => 'Credenciales incorrectas']);
            }
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['login' => 'Error al conectar con el servidor: ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Session::forget(['access_token', 'username', 'token_type']);

        if ($request->ajax()) {
            return response()->json(['message' => 'Sesión cerrada exitosamente']);
        }

        return redirect()->route('login')->with('swal_success', 'Sesión cerrada exitosamente');
    }

    public function getUser()
    {
        if (!Session::has('access_token')) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        try {
            $response = Http::withToken(Session::get('access_token'))
                ->get(config('services.api.url') . '/user/');

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Error al obtener información del usuario'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectar con el servidor: ' . $e->getMessage()], 500);
        }
    }
}
