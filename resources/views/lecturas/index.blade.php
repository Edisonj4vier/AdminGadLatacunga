@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1>Lecturas</h1>
        <div class="text-end">
            <span class="fw-bold text-muted">Fecha: {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="content-section">
        <div class="row mb-3">
            <div class="col-md-6">
                <button class="btn btn-primary" id="sincronizar">Sincronizar Lecturas</button>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" id="crearLectura">Crear Nueva Lectura</button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" name="search" id="searchInput" class="form-control"
                               value="{{ request('search') }}" placeholder="Buscar en todas las columnas...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="perPage" class="form-label">Registros por página</label>
                        <select name="per_page" id="perPage" class="form-control">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    @include('partials.table-lectura', ['lecturas' => $paginator])
                </div>
                <div id="paginationContainer" class="mt-3">
                    {{ $paginator->links() }}
                </div>
            </div>
        </div>
    </div>
    @include('partials.editionLectura-modal')
    @include('partials.detalle-lectura')
    @include('partials.config-modal')
    @include('partials.nuevaLectura-modal')
@endsection

@push('styles')
    <style>
        .page-header {
            background-color: #0067b2;
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .content-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-danger {
            background-color: #f8d7da !important;
        }
        .table-warning {
            background-color: #fff3cd !important;
        }
        .table-success {
            background-color: #d4edda !important;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover, .btn-secondary:focus, .btn-secondary:active {
            background-color: #5a6268;
            border-color: #545b62;
            color: #fff;
        }
    </style>
@endpush
