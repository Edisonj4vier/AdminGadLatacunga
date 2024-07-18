<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class ConsumoLecturaController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_consulta' => 'date',
            'search' => 'nullable|string',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'sort' => 'nullable|string|in:cuenta,medidor,abonado,lectura_actual,promedio,mes,anio',
            'direction' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fechaConsulta = $request->input('fecha_consulta', now()->toDateString());
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);
        $sort = $request->input('sort', 'cuenta');
        $direction = $request->input('direction', 'asc');

        $query = "SELECT * FROM obtener_datos_consumo(?, NULL)";
        $countQuery = "SELECT COUNT(*) FROM obtener_datos_consumo(?, NULL)";
        $params = [$fechaConsulta];

        if ($search) {
            $whereClause = " WHERE cuenta::text ILIKE ? OR medidor ILIKE ? OR clave ILIKE ? OR abonado ILIKE ? OR ruta ILIKE ? OR lectura_actual::text ILIKE ? OR promedio::text ILIKE ? OR observacion ILIKE ? OR mes::text ILIKE ? OR anio::text ILIKE ?";
            $query .= $whereClause;
            $countQuery .= $whereClause;
            $searchTerm = "%$search%";
            $params = array_merge($params, array_fill(0, 10, $searchTerm));
        }

        $totalResults = DB::selectOne($countQuery, $params)->count;

        $query .= " ORDER BY $sort $direction LIMIT ? OFFSET ?";
        $params = array_merge($params, [$perPage, ($page - 1) * $perPage]);

        $lecturas = DB::select($query, $params);

        $paginator = new LengthAwarePaginator(
            $lecturas,
            $totalResults,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return response()->json([
                'data' => $lecturas,
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
    }

    public function actualizarAAPPLectura(Request $request)
    {
        $fechaActualizacion = $request->input('fecha_actualizacion', now()->toDateString());

        try {
            $result = DB::select("SELECT * FROM actualizar_aapplectura(?)", [$fechaActualizacion]);
            return response()->json($result[0]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar lecturas: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $cuenta)
    {
        $request->validate([
            'lectura' => 'required|numeric',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            $result = DB::selectOne(
                "SELECT editar_lectura_movil(?, ?, ?) AS success",
                [$cuenta, $request->lectura, $request->observacion]
            );

            if ($result->success) {
                return response()->json(['message' => 'Registro actualizado con éxito']);
            } else {
                return response()->json(['error' => 'No se pudo actualizar el registro'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el registro: ' . $e->getMessage()], 500);
        }
    }

    public function edit($cuenta)
    {
        $lectura = DB::selectOne("SELECT * FROM aapmovillectura WHERE cuenta = ?", [$cuenta]);

        if (!$lectura) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        // Convertir el objeto stdClass a un array asociativo
        $lecturaArray = json_decode(json_encode($lectura), true);
        return response()->json($lecturaArray);
    }
    public function destroy($cuenta)
    {
        try {
            $result = DB::selectOne(
                "SELECT eliminar_lectura_movil(?) AS success",
                [$cuenta]
            );

            if ($result->success) {
                return response()->json(['message' => 'Registro eliminado con éxito']);
            } else {
                return response()->json(['error' => 'No se pudo eliminar el registro'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el registro: ' . $e->getMessage()], 500);
        }
    }
}
