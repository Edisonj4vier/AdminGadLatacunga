@extends('layouts.appLogin')

@section('content')

    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="content-wrapper">
                    <h2 class="text-center mb-4">Iniciar Sesión</h2>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input id="nombre_usuario" type="text" class="form-control @error('nombre_usuario') is-invalid @enderror" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autocomplete="nombre_usuario" autofocus>

                            @error('nombre_usuario')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input id="contrasena" type="password" class="form-control @error('contrasena') is-invalid @enderror" name="contrasena" required autocomplete="current-password">

                            @error('contrasena')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Ingresar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@endSection
