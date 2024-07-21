$(document).ready(function() {
    const TYPING_TIMER = 300; // milisegundos
    let typingTimer;
    let allData = []; // Almacenará todos los datos de la API

    initializeEvents();
    setupSearch();
    loadInitialData();

    function initializeEvents() {
        $('#rangoUnidades').change(loadInitialData);
        $('#perPage').change(() => filterAndDisplayData());
        $(document).on('click', '.pagination a', handlePagination);
        $('#sincronizar').click(handleSincronization);
        $(document).on('click', '#lecturasTable .edit-btn', handleEdit);
        $(document).on('click', '#lecturasTable .delete-btn', handleDelete);
        $(document).on('click', '#lecturasTable .show-details-btn', handleShowDetails);
    }

    function setupSearch() {
        $('#searchInput').on('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => filterAndDisplayData(), TYPING_TIMER);
        });
    }

    function loadInitialData() {
        const rangoUnidades = $('#rangoUnidades').val();

        $.ajax({
            url: '/lecturas',
            method: 'GET',
            data: {
                rango_unidades: rangoUnidades
            },
            success: function(response) {
                if (response.error) {
                    showErrorAlert('Error: ' + response.error);
                } else {
                    // Asegurarse de que allData sea siempre un array
                    allData = Array.isArray(response) ? response : (response.data || []);
                    filterAndDisplayData();
                }
            },
            error: function(xhr, status, error) {
                showErrorAlert('Error al cargar los datos. Por favor, intenta de nuevo.');
            }
        });
    }

    function filterAndDisplayData(page = 1) {
        if (!Array.isArray(allData)) {
            showErrorAlert('Error en los datos. Por favor, recarga la página.');
            return;
        }

        const searchTerm = $('#searchInput').val().toLowerCase();
        const perPage = parseInt($('#perPage').val());

        let filteredData = allData.filter(item =>
            Object.values(item).some(val =>
                val !== null && String(val).toLowerCase().includes(searchTerm)
            )
        );

        const totalPages = Math.ceil(filteredData.length / perPage);
        page = Math.min(Math.max(1, page), totalPages || 1);

        const startIndex = (page - 1) * perPage;
        const paginatedData = filteredData.slice(startIndex, startIndex + perPage);

        updateTableContent(paginatedData);
        updatePagination({
            current_page: page,
            last_page: totalPages,
            total: filteredData.length,
            per_page: perPage
        });
    }

    function updateTableContent(lecturas) {
        let html = '';
        lecturas.forEach(function(lectura) {
            html += `
            <tr>
                <td class="text-center">
                    <div class="btn-group" role="group" aria-label="Acciones">
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${lectura.cuenta}" title="Modificar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${lectura.cuenta}" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
                <td>${lectura.cuenta}</td>
                <td>${lectura.medidor}</td>
                <td>${lectura.clave}</td>
                <td>${lectura.abonado || ''}</td>
                <td>${lectura.ruta || ''}</td>
                <td class="text-end">${Number(lectura.lectura_actual).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.lectura_aplectura|| 0).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.diferencia || 0).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.promedio || 0).toFixed(2)}</td>
                <td>${getConsumoIndicator(lectura)}</td>
                <td>${lectura.coordenadas || ''}</td>
                <td>
                    <button class="btn btn-sm btn-info show-details-btn" data-bs-toggle="modal" data-bs-target="#detallesModal"
                            data-imagen="${lectura.imagen || ''}" data-motivo="${lectura.motivo || ''}"
                            data-observacion="${lectura.observacion || ''}">
                        <i class="fas fa-info-circle"></i> Detalles
                    </button>
                </td>
            </tr>
        `;
        });
        $('#lecturasBody').html(html);
    }

    function getConsumoIndicator(lectura) {
        const consumo = lectura.diferencia || 0;
        const rangoSuperior = lectura.rango_superior || 0;
        const rangoInferior = lectura.rango_inferior || 0;

        if (consumo > rangoSuperior) {
            return '<span class="badge bg-danger">Alto consumo</span>';
        } else if (consumo < rangoInferior) {
            return '<span class="badge bg-warning">Bajo consumo</span>';
        } else {
            return '<span class="badge bg-success">Consumo normal</span>';
        }
    }

    function updatePagination(paginationData) {
        let paginationHtml = '';
        if (paginationData.last_page > 1) {
            paginationHtml += '<ul class="pagination">';
            for (let i = 1; i <= paginationData.last_page; i++) {
                paginationHtml += `
                    <li class="page-item ${i === paginationData.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            paginationHtml += '</ul>';
        }
        $('#paginationContainer').html(paginationHtml);
    }

    function handlePagination(e) {
        e.preventDefault();
        const page = $(this).data('page');
        filterAndDisplayData(parseInt(page));
    }

    function handleSincronization() {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Se sincronizarán los datos de lecturas",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/lecturas/sincronizar',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Sincronizado!',
                            'Los datos han sido sincronizados.',
                            'success'
                        );
                        actualizarLecturas();
                    },
                    error: function(xhr) {
                        showErrorAlert('Error al sincronizar los datos: ' + xhr.responseJSON.error);
                    }
                });
            }
        });
    }

    function handleEdit() {
        const cuenta = $(this).data('id');
        $.ajax({
            url: `/lecturas/${cuenta}`,
            method: 'GET',
            success: function(response) {
                if (response && response.cuenta) {
                    $('#editCuenta').val(response.cuenta);
                    $('#editLectura').val(response.lectura_actual);
                    $('#editObservacion').val(response.observacion_movil);
                    $('#editMotivo').val(response.motivo);
                    $('#editModal').modal('show');
                } else {
                    console.error('Datos incompletos:', response);
                    showErrorAlert('Datos incompletos recibidos del servidor.');
                }
            },
            error: function(xhr) {
                showErrorAlert('Error al cargar los datos para editar.');
            }
        });
    }

    $('#saveEdit').click(function() {
        const cuenta = $('#editCuenta').val();
        const nuevaLectura = $('#editLectura').val();
        const nuevaObservacion = $('#editObservacion').val();
        const nuevoMotivo = $('#editMotivo').val();

        $.ajax({
            url: `/lecturas/${cuenta}`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                nueva_lectura: nuevaLectura,
                nueva_observacion: nuevaObservacion,
                nuevo_motivo: nuevoMotivo
            },
            success: function(response) {
                $('#editModal').modal('hide');
                Swal.fire('Actualizado!', 'El registro ha sido actualizado.', 'success');
                actualizarLecturas();
            },
            error: function(xhr) {
                showErrorAlert('Error al actualizar el registro: ' + (xhr.responseJSON?.error || 'Error desconocido'));
            }
        });
    });

    function handleDelete() {
        const cuenta = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/lecturas/${cuenta}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Eliminado!',
                            'El registro ha sido eliminado.',
                            'success'
                        );
                        actualizarLecturas();
                    },
                    error: function(xhr) {
                        showErrorAlert('Error al eliminar el registro.');
                    }
                });
            }
        });
    }

    function handleShowDetails() {
        let imagen = $(this).data('imagen');
        let motivo = $(this).data('motivo');
        let observacion = $(this).data('observacion');

        if (imagen) {
            $('#modalImagen').attr('src', 'data:image/jpeg;base64,' + imagen).show();
            $('#noImageMessage').hide();
        } else {
            $('#modalImagen').hide();
            $('#noImageMessage').show();
        }

        $('#modalMotivo').text(motivo || 'No especificado');
        $('#modalObservacion').text(observacion || 'No especificado');
    }

    function showErrorAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }
});
