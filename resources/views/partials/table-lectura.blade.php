<div class="table-responsive">
    <table class="table table-bordered table-hover table-custom" id="lecturasTable" data-sort="cuenta" data-direction="asc">
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
            <th>Observación</th>
            <th>Coordenadas</th>
        </tr>
        </thead>
        <tbody id="lecturasBody">
        @foreach($lecturas as $lectura)
            <tr class="{{ $lectura->observacion === 'Alto consumo' ? 'table-danger' :
                     ($lectura->observacion === 'Bajo consumo' ? 'table-warning' :
                     ($lectura->observacion === 'Consumo normal' ? 'table-success' : '')) }}">
                <td class="text-center">
                    <div class="btn-group" role="group" aria-label="Acciones">
                        <button class="btn btn-sm btn-warning edit-btn"  data-id="{{ $lectura->cuenta }}" type="button" title="Modificar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $lectura->cuenta }}" type="button" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
                <td>{{ $lectura->cuenta }}</td>
                <td>{{ $lectura->medidor }}</td>
                <td>{{ $lectura->clave }}</td>
                <td>{{ $lectura->abonado }}</td>
                <td>{{ $lectura->ruta }}</td>
                <td class="text-end">{{ number_format($lectura->lectura_actual) }}</td>
                <td class="text-end">{{ number_format($lectura->lectura_anterior) }}</td>
                <td class="text-end">{{ number_format($lectura->diferencia, 2) }}</td>
                <td class="text-end">{{ number_format($lectura->promedio, 2) }}</td>
                <td>{{ $lectura->observacion }}</td>
                <td>{{ $lectura->mes }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
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
