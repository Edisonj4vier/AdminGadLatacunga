@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Lecturas</h1>
    </div>

    <div class="content-section">
        <div class="row mb-3">
            <div class="col-md-6">
                <button class="btn btn-primary" id="sincronizar">Sincronizar</button>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-info">Fecha: {{ now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fecha_consulta" class="form-label">Fecha de consulta</label>
                        <input type="date" name="fecha_consulta" id="fechaConsulta" class="form-control"
                               value="{{ request('fecha_consulta', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" name="search" id="searchInput" class="form-control"
                               value="{{ request('search') }}" placeholder="Buscar en todas las columnas...">
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
    </style>
@endpush
