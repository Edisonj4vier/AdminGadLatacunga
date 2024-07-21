<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ConsumoLecturaController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            $response = ApiHelper::request('get', '/lecturas', [
                'fecha_consulta' => $request->input('fecha_consulta', now()->toDateString()),
                'limite_registros' => $perPage,
                'rango_unidades' => $request->input('rango_unidades', 2),
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

            $fechaActual = now()->format('d/m/Y');
            return view('lecturas.index', compact('paginator', 'fechaActual'));
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
            ? $e->response->body()
            : $e->getMessage();

        return response()->json(['error' => $message], $statusCode);
    }
}
