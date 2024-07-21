@extends('layouts.app')

@section('content')
    <h2 class="mb-4 text-center">Registro de ruta del lector</h2>
    <form id="form-agregar-editar" action="{{ route('app-lector-ruta.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Lector</label>
            <select class="form-select select2" id="username" name="username">
                <option value="">Seleccione Lector</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario['login'] }}">{{ $usuario['login'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="ruta_id" class="form-label">Ruta</label>
            <select class="form-select select2" id="ruta_id" name="ruta_id">
                <option value="">Seleccione Ruta</option>
                @foreach ($rutas as $ruta)
                    <option value="{{ $ruta['id'] }}">{{ $ruta['nombreruta'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Agregar</button>
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
