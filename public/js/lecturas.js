// lecturas.js
$(document).ready(function() {
    const TYPING_TIMER = 300; // milisegundos
    let typingTimer;

    initializeEvents();
    setupSearch();
    setupSorting();
    loadInitialData();

    function initializeEvents() {
        $('#fechaConsulta').change(actualizarLecturas);
        $(document).on('click', '.page-link', handlePagination);
        $('#sincronizar').click(handleSincronization);
        $(document).on('click', '#lecturasTable .edit-btn', handleEdit);
        $(document).on('click', '#lecturasTable .delete-btn', handleDelete);
    }

    function setupSearch() {
        $('#searchInput').on('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(actualizarLecturas, TYPING_TIMER);
        });
    }

    function setupSorting() {
        $('.sortable').click(function(e) {
            e.preventDefault();
            const sort = $(this).data('sort');
            const table = $('#lecturasTable');
            const currentDirection = table.data('direction');
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            table.data('sort', sort).data('direction', newDirection);
            actualizarLecturas();
        });
    }

    function loadInitialData() {
        actualizarLecturas();
    }

    function actualizarLecturas(page = 1) {
        const fechaConsulta = $('#fechaConsulta').val();
        const search = $('#searchInput').val();
        const table = $('#lecturasTable');
        const sort = table.data('sort');
        const direction = table.data('direction');

        $.ajax({
            url: '/lecturas',
            method: 'GET',
            data: {
                fecha_consulta: fechaConsulta,
                search: search,
                page: page,
                sort: sort,
                direction: direction
            },
            success: function(response) {
                if (response.error) {
                    showErrorAlert('Error: ' + response.error);
                } else {
                    updateTableContent(response.data);
                    updatePagination(response.pagination);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar lecturas:', status, error);
                let errorMessage = 'Error al cargar los datos. ';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage += xhr.responseJSON.error;
                } else {
                    errorMessage += 'Por favor, intenta de nuevo.';
                }
                showErrorAlert(errorMessage);
            }
        });
    }

    function updateTableContent(lecturas) {
        let html = '';
        lecturas.forEach(function(lectura) {
            let rowClass = '';
            if (lectura.observacion === 'Alto consumo') {
                rowClass = 'table-danger';
            } else if (lectura.observacion === 'Bajo consumo') {
                rowClass = 'table-warning';
            } else if (lectura.observacion === 'Consumo normal') {
                rowClass = 'table-success';
            }

            html += `
            <tr class="${rowClass}">
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
                <td>${lectura.abonado}</td>
                <td>${lectura.ruta}</td>
                <td class="text-end">${Number(lectura.lectura_actual).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.lectura_anterior).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.diferencia).toLocaleString()}</td>
                <td class="text-end">${Number(lectura.promedio).toFixed(2)}</td>
                <td>${lectura.observacion}</td>
                <td>${lectura.mes}</td>
            </tr>
        `;
        });
        $('#lecturasBody').html(html);
    }

    function updatePagination(paginationData) {
        // Implementa la lógica para actualizar la paginación
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
        actualizarLecturas($(this).data('page'));
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
                    data: {
                        fecha_actualizacion: $('#fechaConsulta').val()
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

    function handleEdit(e) {
        const cuenta = $(this).data('id');
        $.ajax({
            url: `/lecturas/${cuenta}/edit`,
            method: 'GET',
            success: function(response) {
                console.log('Respuesta completa:', response);
                // Cambiamos la lógica de validación
                if (response && response.cuenta) {
                    $('#editCuenta').val(response.cuenta);
                    $('#editLectura').val(response.lectura ? response.lectura.trim() : '');
                    $('#editObservacion').val(response.observacion || '');
                    $('#editModal').modal('show');
                } else {
                    console.error('Datos incompletos:', response);
                    showErrorAlert('Datos incompletos recibidos del servidor. Por favor, revisa la consola para más detalles.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición:', status, error);
                console.error('Respuesta del servidor:', xhr.responseText);
                let errorMessage = 'Error al cargar los datos para editar.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage += ' ' + xhr.responseJSON.error;
                }
                showErrorAlert(errorMessage);
            }
        });
    }

    $('#saveEdit').click(function() {
        const cuenta = $('#editCuenta').val();
        const lectura = $('#editLectura').val();
        const observacion = $('#editObservacion').val();

        $.ajax({
            url: `/lecturas/${cuenta}`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                lectura: lectura,
                observacion: observacion
            },
            success: function(response) {
                $('#editModal').modal('hide');
                Swal.fire('Actualizado!', 'El registro ha sido actualizado.', 'success');
                actualizarLecturas();
            },
            error: function(xhr) {
                showErrorAlert('Error al actualizar el registro: ' + xhr.responseJSON.error);
            }
        });
    });
    function handleDelete(e) {
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

    function showErrorAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }
    function getCSRFToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }
});
