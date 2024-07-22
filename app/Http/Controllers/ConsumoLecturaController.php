<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class ConsumoLecturaController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            $params = [
                'fecha_consulta' => $request->input('fecha_consulta'),
                'limite_registros' => null, // Quitamos el límite para obtener todos los registros
                'rango_unidades' => $request->input('rango_unidades', 2),
            ];

            // Filtrar los parámetros nulos
            $params = array_filter($params, function ($value) {
                return $value !== null;
            });

            \Log::info('Parámetros de la solicitud:', $params);

            $response = ApiHelper::request('get', '/lecturas', $params);

            \Log::info('Respuesta de la API:', ['status' => $response->status(), 'body' => $response->body()]);


            $allData = $response->json();

            if (!is_array($allData)) {
                \Log::error('Respuesta inesperada del API:', ['response' => $allData]);
                throw new \Exception('Respuesta inesperada del API');
            }

            $collection = Collection::make($allData);

            // Procesar los datos
            $collection = $collection->map(function ($item) {
                // Asegurarse de que 'imagen' sea una URL o cadena base64 válida
                $item['imagen'] = $item['imagen'] ?? null;

                // Formatear campos numéricos
                $item['lectura_actual'] = number_format($item['lectura_actual'] ?? 0, 0, ',', '');
                $item['lectura_aplectura'] = number_format($item['lectura_aplectura'] ?? 0, 0, ',', '');
                $item['diferencia'] = number_format($item['diferencia'] ?? 0, 0, ',', '');
                $item['promedio'] = number_format($item['promedio'] ?? 0, 2, ',', '');

                // Calcular el indicador
                $consumo = $item['diferencia'] ?? 0;
                $rangoSuperior = $item['rango_superior'] ?? 0;
                $rangoInferior = $item['rango_inferior'] ?? 0;
                if ($consumo > $rangoSuperior) {
                    $item['indicador'] = 'Alto';
                } elseif ($consumo < $rangoInferior) {
                    $item['indicador'] = 'Bajo';
                } else {
                    $item['indicador'] = 'Normal';
                }

                return $item;
            });

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

            $fechaActual = now()->format('d/m/Y');
            return view('lecturas.index', compact('paginator', 'fechaActual'));
        } catch (\Exception $e) {
            \Log::error('Error en ConsumoLecturaController::index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->handleApiError($e);
        }
    }

    public function edit($cuenta): JsonResponse
    {
        try {
            $response = ApiHelper::request('get', "/lecturas/{$cuenta}");
            $data = $response->json();

            if (!is_array($data) || empty($data)) {
                throw new \Exception('No se encontraron datos para la cuenta especificada');
            }

            // Procesar los datos si es necesario
            $data['lectura_actual'] = number_format($data['lectura'] ?? 0, 0, ',', '');
            $data['lectura_aplectura'] = number_format($data['lectura_aplectura'] ?? 0, 0, ',', '');
            $data['diferencia'] = number_format($data['diferencia'] ?? 0, 0, ',', '');
            $data['promedio'] = number_format($data['promedio'] ?? 0, 2, ',', '');

            return response()->json($data);
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

    public function destroy($cuenta): JsonResponse
    {
        try {
            $response = ApiHelper::request('delete', "/lecturas/{$cuenta}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public function sincronizarLecturas(Request $request): JsonResponse
    {
        try {
            $response = ApiHelper::request('post', "/sincronizar_lecturas/{$request->login}", $request->lecturas);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public function copiarEvidencia(): JsonResponse
    {
        try {
            $response = ApiHelper::request('post', '/copiar_evidencia');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    public function actualizarLecturas(): JsonResponse
    {
        try {
            $response = ApiHelper::request('post', '/actualizar_lecturas');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }

    private function handleApiError(\Exception $e): JsonResponse
    {
        $statusCode = $e instanceof \Illuminate\Http\Client\RequestException
            ? $e->response->status()
            : ($e->getCode() ?: 500);

        $message = $e instanceof \Illuminate\Http\Client\RequestException
            ? $e->response->json()['detail'] ?? $e->response->body()
            : $e->getMessage();

        return response()->json(['error' => $message], $statusCode);
    }
}
