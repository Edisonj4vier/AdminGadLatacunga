@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4 text-center">Registro de ruta del lector</h2>
        <form id="form-agregar-editar" action="{{ route('app-lector-ruta.store') }}" method="POST" class="mb-4">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="username" class="form-label">Lector</label>
                    <select class="form-select select2" id="username" name="username" required>
                        <option value="">Seleccione Lector</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario['login'] }}">{{ $usuario['login'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ruta_id" class="form-label">Ruta</label>
                    <select class="form-select select2" id="ruta_id" name="ruta_id" required>
                        <option value="">Seleccione Ruta</option>
                        @foreach ($rutas as $ruta)
                            <option value="{{ $ruta['id'] }}">{{ $ruta['nombreruta'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="fecha" class="form-label">Fecha de Asignación</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Agregar</button>
                </div>
            </div>
        </form>

        @if(count($paginatedItems) > 0)
            <div id="table-container">
                @include('partials.table')
            </div>
        @else
            <p class="text-center">No hay rutas registradas. Agregue una nueva ruta para mostrar la tabla.</p>
        @endif

        @include('partials.confirmation-modal')
        @include('partials.edition-modal')
    </div>

    @if(session('success'))
        <script>
            let successMessage = "{{ session('success') }}";
        </script>
    @endif

    @if(session('error'))
        <script>
            let errorMessage = "{{ session('error') }}";
        </script>
    @endif
@endsection
