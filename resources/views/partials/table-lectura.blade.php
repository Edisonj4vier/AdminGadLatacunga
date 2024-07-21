<div class="table-responsive">
    <table class="table table-bordered table-hover table-custom" id="lecturasTable">
        <thead class="table-dark">
        <tr>
            <th class="text-center">Acciones</th>
            <th>Cuenta</th>
            <th>Medidor</th>
            <th>Clave</th>
            <th>Abonado</th>
            <th>Ruta</th>
            <th>Lectura actual</th>
            <th>Lectura anterior</th>
            <th>Consumo</th>
            <th>Promedio consumo</th>
            <th>Indicador de Consumo</th>
            <th>Coordenadas</th>
            <th>Observaciones</th>
        </tr>
        </thead>
        <tbody id="lecturasBody">
        @foreach($paginator as $lectura)
            <tr>
                <td class="text-center">
                    <div class="btn-group" role="group" aria-label="Acciones">
                        <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $lectura['cuenta'] ?? '' }}" type="button" title="Modificar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $lectura['cuenta'] ?? '' }}" type="button" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
                <td>{{ $lectura['cuenta'] ?? '' }}</td>
                <td>{{ $lectura['medidor'] ?? '' }}</td>
                <td>{{ $lectura['clave'] ?? '' }}</td>
                <td>{{ $lectura['abonado'] ?? '' }}</td>
                <td>{{ $lectura['ruta'] ?? '' }}</td>
                <td class="text-end">{{ number_format($lectura['lectura_actual'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($lectura['lectura_aplectura'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($lectura['diferencia'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($lectura['promedio'] ?? 0, 2, ',', '.') }}</td>
                <td>
                    @php
                        $consumo = $lectura['consumo'] ?? 0;
                        $rangoSuperior = $lectura['rango_superior'] ?? 0;
                        $rangoInferior = $lectura['rango_inferior'] ?? 0;
                    @endphp
                    @if($consumo > $rangoSuperior)
                        <span class="badge bg-danger">Alto consumo</span>
                    @elseif($consumo < $rangoInferior)
                        <span class="badge bg-warning">Bajo consumo</span>
                    @else
                        <span class="badge bg-success">Consumo normal</span>
                    @endif
                </td>
                <td>{{ $lectura['coordenadas'] ?? '' }}</td>
                <td>
                    <button class="btn btn-sm btn-info show-details-btn" data-bs-toggle="modal" data-bs-target="#detallesModal"
                            data-imagen="{{ $lectura['imagen'] ?? '' }}" data-motivo="{{ $lectura['motivo'] ?? '' }}"
                            data-observacion="{{ $lectura['observacion'] ?? '' }}">
                        <i class="fas fa-info-circle"></i> Detalles
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div id="paginationContainer" class="mt-3">
    {{ $paginator->links() }}
</div>
@push('styles')
    <style>
        .table-custom {
            font-size: 0.9rem;
        }
        .table-custom th {
            background-color: #0067b2;
            color: white;
            white-space: nowrap;
        }
        .table-custom .sortable {
            color: white;
            text-decoration: none;
        }
        .table-custom .sortable:hover {
            text-decoration: underline;
        }
        .table-custom td {
            vertical-align: middle;
        }
        .btn-group {
            white-space: nowrap;
        }
    </style>
@endpush
