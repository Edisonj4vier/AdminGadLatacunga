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
            <th>Lectura<br>actual</th>
            <th>Lectura<br>anterior</th>
            <th>Consumo</th>
            <th>Promedio<br>consumo</th>
            <th>Indicador</th>
            <th>Ubicación de registro</th>
            <th>Observaciones</th>
            <th>Detalles</th>
        </tr>
        </thead>
        <tbody id="lecturasBody">
        @foreach($paginator as $lectura)
            <tr class="{{ $lectura['coordenadas_duplicadas'] ?? false ? 'coordenadas-duplicadas' : '' }}">
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Acciones">
                        <button class="btn btn-warning edit-btn p-0" data-id="{{ $lectura['cuenta'] ?? '' }}" type="button" title="Modificar"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger delete-btn p-0" data-id="{{ $lectura['cuenta'] ?? '' }}" type="button" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
                <td>{{ $lectura['cuenta'] ?? '' }}</td>
                <td>{{ $lectura['medidor'] ?? '' }}</td>
                <td>{{ $lectura['clave'] ?? '' }}</td>
                <td>{{ $lectura['abonado'] ?? '' }}</td>
                <td>{{ $lectura['ruta'] ?? '' }}</td>
                <td class="text-end">{{ $lectura['lectura_actual'] ?? '' }}</td>
                <td class="text-end">{{ $lectura['lectura_aplectura'] ?? '' }}</td>
                <td class="text-end">{{ $lectura['diferencia'] ?? '' }}</td>
                <td class="text-end">{{ $lectura['promedio'] ?? '' }}</td>
                <td>
                    @php
                        $consumo = $lectura['diferencia'] ?? 0;
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
                <td>{{ $lectura['observacion_movil'] ?? '' }}</td>
                <td>
                    @php
                        $imagen = $lectura['imagen'] ?? '';
                        $motivo = $lectura['motivo'] ?? '';
                    @endphp
                    <button class="btn btn-sm btn-info show-details-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#detallesModal"
                            data-imagen="{{ $imagen }}"
                            data-motivo="{{ $motivo }}"
                        {{ (!$imagen || !$motivo) ? 'disabled' : '' }}>
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
            font-size: 0.85rem;
            width: 100%;
        }
        .table-custom th, .table-custom td {
            padding: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-custom th {
            background-color: #0067b2;
            color: white;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }
        .table-custom td {
            vertical-align: middle;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.1rem 0.3rem;
            font-size: 0.75rem;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.2em 0.4em;
        }
        .text-end {
            text-align: right;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .bg-warning {
            background-color: #FFF9C4 !important;
        }
        .bg-warning td {
            color: #000;
        }
    </style>
@endpush
