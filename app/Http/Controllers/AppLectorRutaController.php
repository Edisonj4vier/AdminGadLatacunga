<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiHelper;

class AppLectorRutaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $response = ApiHelper::request('get', '/lectorruta');
            $appLectorRutas = $response->json();

            // Implementar paginación manual
            $page = $request->input('page', 1);
            $perPage = 5;
            $total = count($appLectorRutas);

            $paginatedItems = array_slice($appLectorRutas, ($page - 1) * $perPage, $perPage);

            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ];

            if ($request->ajax()) {
                return view('partials.table', compact('paginatedItems', 'pagination'));
            }

            $usuarios = ApiHelper::request('get', '/obtenerUsuarios/')->json();
            $rutas = ApiHelper::request('get', '/obtenerRutas/')->json();

            return view('app_lector_ruta.index', compact('usuarios', 'rutas', 'paginatedItems', 'pagination'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'ruta_id' => 'required|integer',
        ]);

        try {
            $response = ApiHelper::request('post', '/asignarRuta/', [
                'username' => $request->username,
                'ruta_id' => $request->ruta_id,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                if (strpos($data['mensaje'], 'ya está asignada') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => $data['mensaje']
                    ], 409);
                }

                return response()->json([
                    'success' => true,
                    'message' => $data['mensaje']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $data['detail'] ?? 'No se pudo asignar la ruta'
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($username, $id_ruta)
    {
        try {
            $response = ApiHelper::request('delete', "/lectorruta/{$username}/{$id_ruta}");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->json()['detail'] ?? 'No se pudo eliminar el registro'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($username, $id_ruta)
    {
        try {
            $appLectorRuta = ApiHelper::request('get', "/lectorruta/{$username}/{$id_ruta}")->json();
            $usuarios = ApiHelper::request('get', '/obtenerUsuarios/')->json();
            $rutas = ApiHelper::request('get', '/obtenerRutas/')->json();

            // Depuración
            \Log::info('appLectorRuta:', $appLectorRuta);
            \Log::info('usuarios:', $usuarios);
            \Log::info('rutas:', $rutas);

            // Asegurarse de que 'login' esté presente en appLectorRuta
            if (!isset($appLectorRuta['login'])) {
                $appLectorRuta['login'] = $username;
            }

            // Asegurarse de que 'id' esté presente en appLectorRuta para la ruta
            if (!isset($appLectorRuta['id'])) {
                $appLectorRuta['id'] = $id_ruta;
            }

            return response()->json([
                'appLectorRuta' => $appLectorRuta,
                'usuarios' => $usuarios,
                'rutas' => $rutas
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en edit:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $username, $id_ruta)
    {
        $request->validate([
            'new_username' => 'required',
            'new_id_ruta' => 'required|integer',
        ]);

        try {
            $response = ApiHelper::request('put', "/lectorruta/{$username}/{$id_ruta}", [
                'new_username' => $request->new_username,
                'new_id_ruta' => $request->new_id_ruta,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => $data['mensaje'] ?? 'Lector-ruta actualizado correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response->json()['detail'] ?? 'No se pudo actualizar el lector-ruta'
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
