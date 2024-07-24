@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Configuración</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Rango de Unidades</h5>
                <form id="rangoUnidadesForm">
                    <div class="mb-3">
                        <label for="rangoUnidades" class="form-label">Rango de unidades</label>
                        <input type="number" name="rango_unidades" id="rangoUnidades" class="form-control"
                               value="{{ $rangoUnidades }}" min="0" step="0.1">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                </form>
            </div>
        </div>
    </div>
@endsection
