<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class ConsumoLecturaController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);
            $fechaConsulta = $request->input('fecha_consulta', now()->toDateString());

            $response = ApiHelper::request('get', '/lecturas', [
                'fecha_consulta' => $fechaConsulta,
                'limite_registros' => $request->input('limite_registros'),
            ]);

            $allData = $response->json();

            $collection = Collection::make($allData);

            $paginator = new LengthAwarePaginator(
                $collection->forPage($page, $perPage),
                $collection->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            if ($request->ajax()) {
                return response()->json([
                    'data' => $paginator->items(),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem()
                    ]
                ]);
            }

            $parametrosConsumo = $this->obtenerParametrosConsumo()->getData(true);
            return view('lecturas.index', compact('paginator', 'fechaConsulta', 'parametrosConsumo'));
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
    public function show($cuenta): JsonResponse
    {
        try {
            $response = ApiHelper::request('get', "/lecturas/{$cuenta}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
    public function update(Request $request, $cuenta): JsonResponse
    {
        try {
            $response = ApiHelper::request('put', "/lecturas/{$cuenta}", [
                'nueva_lectura' => $request->input('nueva_lectura'),
                'nueva_observacion' => $request->input('nueva_observacion'),
                'nuevo_motivo' => $request->input('nuevo_motivo'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
    public function actualizarParametrosConsumo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rango_unidades' => 'required|numeric|gt:0',
                'limite_promedio' => 'required|integer|gt:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $queryParams = http_build_query([
                'rango_unidades' => $request->input('rango_unidades'),
                'limite_promedio' => $request->input('limite_promedio'),
            ]);

            $response = ApiHelper::request('post', "/parametros-consumo?{$queryParams}");

            if ($response->successful()) {
                return response()->json(['message' => 'Parámetros actualizados correctamente']);
            } else {
                $errorContent = $response->json();
                Log::error('Error en la respuesta del API', ['error' => $errorContent]);

                return response()->json(['error' => $errorContent], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Excepción en actualizarParametrosConsumo', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerParametrosConsumo(): JsonResponse
    {
        try {
            $response = ApiHelper::request('get', '/parametros-consumo');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public function obtenerDatosMedidor($cuenta): JsonResponse
    {
        try {
            // Primero, verificar si ya existe una lectura
            $responseLectura = ApiHelper::request('get', "/lecturas/{$cuenta}");
            $dataLectura = $responseLectura->json();

            if (isset($dataLectura['lectura']) && $dataLectura['lectura'] !== null) {
                // Ya existe una lectura
                return response()->json([
                    'error' => 'Esta cuenta ya tiene una lectura registrada.',
                    'lectura_existente' => true,
                    'lectura_actual' => $dataLectura['lectura']
                ], 400);
            }

            // Si no existe lectura, obtener datos del medidor
            $responseMedidor = ApiHelper::request('get', "/obtener_datos_medidor/{$cuenta}");

            if (!$responseMedidor->successful()) {
                return response()->json([
                    'error' => 'No se encontraron registros para la cuenta proporcionada.'
                ], $responseMedidor->status());
            }

            $dataMedidor = $responseMedidor->json();

            if (empty($dataMedidor)) {
                return response()->json([
                    'error' => 'No se encontraron datos del medidor para la cuenta proporcionada.'
                ], 404);
            }

            return response()->json([
                'medidor' => $dataMedidor['medidor'] ?? '',
                'clave' => $dataMedidor['clave'] ?? '',
                'abonado' => $dataMedidor['abonado'] ?? '',
                'direccion' => $dataMedidor['direccion'] ?? '',
                'coordenadas' => $dataMedidor['coordenadas'] ?? '0.0.0,0.0.0,0.0.0',
            ]);
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
    public function destroy($cuenta): JsonResponse
    {
        try {
            $response = ApiHelper::request('delete', "/lecturas/{$cuenta}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }


    public function actualizarLecturas(): JsonResponse
    {
        try {
            $response = ApiHelper::request('post', '/actualizar_lecturas');

            if ($response->successful()) {
                return response()->json([
                    'mensaje' => 'Los datos de lecturas han sido actualizados.',
                    'tabla_vaciada' => true
                ]);
            } else {
                return response()->json([
                    'error' => 'Error al actualizar los datos de lecturas.'
                ], $response->status());
            }
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }


    public function copiarEvidencias(): JsonResponse
    {
        try {
            $response = ApiHelper::request('post', '/lecturas/copiar-evidencias');

            if ($response->successful()) {
                return response()->json([
                    'mensaje' => 'Las evidencias han sido copiadas exitosamente.',
                    'tabla_vaciada' => true
                ]);
            } else {
                return response()->json([
                    'error' => 'Error al copiar evidencias.'
                ], $response->status());
            }
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public function crearLectura(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cuenta' => 'required|string',
                'lectura' => 'required|string',
                'observacion' => 'required|string',
            ]);

            $data = $request->only(['cuenta', 'lectura', 'observacion']);

            // Primero, verificamos si la cuenta existe
            $cuentaResponse = ApiHelper::request('get', "/obtener_datos_medidor/{$data['cuenta']}");

            if ($cuentaResponse->status() === 404) {
                return response()->json([
                    'error' => 'La cuenta proporcionada no existe en el sistema.',
                    'errorType' => 'CUENTA_NO_EXISTE'
                ], 404);
            }

            // Si la cuenta existe, procedemos a crear la lectura
            $response = ApiHelper::request('post', '/movil-lectura', $data);

            $responseData = $response->json();
            $statusCode = $response->status();

            // Manejar respuestas específicas del endpoint FastAPI
            if ($statusCode === 400 && isset($responseData['detail'])) {
                if (strpos($responseData['detail'], 'ya existe') !== false) {
                    return response()->json([
                        'error' => $responseData['detail'],
                        'errorType' => 'LECTURA_YA_EXISTE'
                    ], 400);
                }
            }

            return response()->json($responseData, $statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->errors(),
                'errorType' => 'VALIDACION_FALLIDA'
            ], 422);
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

private function handleApiError(\Exception $e): JsonResponse
    {
        $statusCode = $e instanceof \Illuminate\Http\Client\RequestException
            ? $e->response->status()
            : 500;

        $message = $e instanceof \Illuminate\Http\Client\RequestException
            ? $e->response->json('detail', 'Error desconocido')
            : $e->getMessage();

        return response()->json([
            'error' => $message,
            'errorType' => 'ERROR_GENERAL'
        ], $statusCode);
    }
}
