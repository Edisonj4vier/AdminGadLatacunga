<table id="appLectorRutaTable" class="table table-bordered table-hover">
    <thead>
    <tr>
        <th>Editar</th>
        <th>Eliminar</th>
        <th>Lector</th>
        <th>Ruta</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($paginatedItems as $appLectorRuta)
        <tr>
            <td class="table-actions">
                <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="{{ $appLectorRuta['id_lectorruta'] }}">Editar</button>
            </td>
            <td class="table-actions">
                <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $appLectorRuta['id_lectorruta'] }}">Eliminar</button>
            </td>
            <td>{{ $appLectorRuta['nombre_usuario'] }}</td>
            <td>{{ $appLectorRuta['nombre_ruta']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@if(isset($pagination))
    <div class="d-flex justify-content-center">
        <nav>
            <ul class="pagination">
                @if($pagination['current_page'] > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ url()->current() }}?page={{ $pagination['current_page'] - 1 }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                @endif

                @for($i = 1; $i <= $pagination['last_page']; $i++)
                    <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ url()->current() }}?page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($pagination['current_page'] < $pagination['last_page'])
                    <li class="page-item">
                        <a class="page-link" href="{{ url()->current() }}?page={{ $pagination['current_page'] + 1 }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
