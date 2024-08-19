<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use Carbon\Carbon;

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
        try {
            $validatedData = $request->validate([
                'username' => 'required',
                'ruta_id' => 'required|integer',
                'fecha' => 'required|date',
            ]);

            $validationMessages = [];

            // Verificar si el usuario ya tiene la ruta asignada
            $existingAssignment = ApiHelper::request('get', "/lectorruta/{$validatedData['username']}/{$validatedData['ruta_id']}");
            if ($existingAssignment->successful()) {
                $validationMessages[] = 'El usuario ya tiene esta ruta asignada.';
            }

            // Verificar si la ruta ya está asignada a otro usuario
            $allAssignments = ApiHelper::request('get', '/lectorruta')->json();
            foreach ($allAssignments as $assignment) {
                if ($assignment['id_ruta'] == $validatedData['ruta_id'] && $assignment['login_usuario'] != $validatedData['username']) {
                    $validationMessages[] = 'La ruta ya está asignada a otro usuario.';
                    break;
                }
            }

            if (!empty($validationMessages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede realizar la asignación.',
                    'validationMessages' => $validationMessages
                ], 422);
            }

            $response = ApiHelper::request('post', '/asignarRuta/', [
                'username' => $validatedData['username'],
                'ruta_id' => $validatedData['ruta_id'],
                'fecha' => Carbon::parse($validatedData['fecha'])->toDateString(),
            ]);

            $data = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => $data['mensaje'] ?? 'Ruta asignada correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $data['detail'] ?? 'No se pudo asignar la ruta'
                ], $response->status());
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
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

            // Asegurarse de que los campos necesarios estén presentes
            $appLectorRuta['login_usuario'] = $appLectorRuta['login_usuario'] ?? $username;
            $appLectorRuta['id_ruta'] = $appLectorRuta['id_ruta'] ?? $id_ruta;
            if (isset($appLectorRuta['fecha'])) {
                $appLectorRuta['fecha'] = date('Y-m-d', strtotime($appLectorRuta['fecha']));
            }

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

    public function update(Request $request, $username, $id_ruta)
    {
        try {
            $validatedData = $request->validate([
                'new_username' => 'required',
                'new_id_ruta' => 'required|integer',
                'fecha' => 'required|date',
            ]);

            $validationMessages = [];

            // Verificar si el usuario ya tiene la ruta asignada
            if ($validatedData['new_username'] != $username || $validatedData['new_id_ruta'] != $id_ruta) {
                $existingAssignment = ApiHelper::request('get', "/lectorruta/{$validatedData['new_username']}/{$validatedData['new_id_ruta']}");
                if ($existingAssignment->successful()) {
                    $validationMessages[] = 'El usuario ya tiene esta ruta asignada.';
                }
            }

            // Verificar si la ruta ya está asignada a otro usuario
            $allAssignments = ApiHelper::request('get', '/lectorruta')->json();
            foreach ($allAssignments as $assignment) {
                if ($assignment['id_ruta'] == $validatedData['new_id_ruta'] &&
                    $assignment['login_usuario'] != $validatedData['new_username'] &&
                    ($assignment['login_usuario'] != $username || $assignment['id_ruta'] != $id_ruta)) {
                    $validationMessages[] = 'La ruta ya está asignada a otro usuario.';
                    break;
                }
            }

            if (!empty($validationMessages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede realizar la actualización.',
                    'validationMessages' => $validationMessages
                ], 422);
            }

            $response = ApiHelper::request('put', "/lectorruta/{$username}/{$id_ruta}", [
                'new_username' => $validatedData['new_username'],
                'new_id_ruta' => $validatedData['new_id_ruta'],
                'fecha' => Carbon::parse($validatedData['fecha'])->toDateString(),
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
