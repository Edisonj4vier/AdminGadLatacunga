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
            $perPage = 5; // Puedes ajustar este número según tus necesidades
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
            'id_usuario' => 'required',
            'id_ruta' => 'required',
        ]);

        try {
            $response = ApiHelper::request('post', '/asignarRuta/', [
                'usuario_id' => $request->id_usuario,
                'ruta_id' => $request->id_ruta,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                // Verificar si el mensaje indica una asignación existente
                if (strpos($data['mensaje'], 'ya está asignada') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => $data['mensaje']
                    ], 409); // Código 409 Conflict
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
    public function destroy($id)
    {
        try {
            $response = ApiHelper::request('delete', "/lectorruta/{$id}");

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

    public function edit($id)
    {
        try {
            $appLectorRuta = ApiHelper::request('get', "/lectorruta/{$id}")->json();
            $usuarios = ApiHelper::request('get', '/obtenerUsuarios/')->json();
            $rutas = ApiHelper::request('get', '/obtenerRutas/')->json();

            return response()->json([
                'appLectorRuta' => $appLectorRuta,
                'usuarios' => $usuarios,
                'rutas' => $rutas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_usuario' => 'required|integer',
            'id_ruta' => 'required|integer',
        ]);

        try {
            $response = ApiHelper::request('put', "/lectorruta/{$id}", [
                'usuario_id' => $request->id_usuario,
                'ruta_id' => $request->id_ruta,
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
