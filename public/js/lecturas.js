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
        $('#editForm').on('submit', handleEditSubmit);
    }

    function setupSearch() {
        $('#searchInput').on('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => filterAndDisplayData(), TYPING_TIMER);
        });
    }
    function loadInitialData() {
        const rangoUnidades = $('#rangoUnidades').val();
        const fechaConsulta = $('#fechaConsulta').val();
        const searchTerm = $('#searchInput').val();
        const perPage = $('#perPage').val();

        showLoadingMessage();

        ajaxRequest('/lecturas', 'GET',
            {
                rango_unidades: rangoUnidades,
                fecha_consulta: fechaConsulta,
                search: searchTerm,
                per_page: perPage
            },
            function(response) {
                hideLoadingMessage();
                if (response.error) {
                    showErrorAlert('Error: ' + response.error);
                } else {
                    updateTableContent(response.data);
                    updatePagination(response.pagination);
                }
            },
            function(xhr) {
                hideLoadingMessage();
                showErrorAlert('Error al cargar los datos: ' + (xhr.responseJSON?.error || 'Error desconocido'));
            }
        );
    }

    function filterAndDisplayData(page = 1, paginationData = null) {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const perPage = parseInt($('#perPage').val());

        let filteredData = allData.filter(item =>
            Object.values(item).some(val =>
                val !== null && String(val).toLowerCase().includes(searchTerm)
            )
        );

        if (paginationData) {
            updateTableContent(filteredData.slice(0, perPage));
            updatePagination(paginationData);
        } else {
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
                <td class="text-end">${lectura.lectura_actual}</td>
                <td class="text-end">${lectura.lectura_aplectura}</td>
                <td class="text-end">${lectura.diferencia}</td>
                <td class="text-end">${lectura.promedio}</td>
                <td>${getConsumoIndicator(lectura)}</td>
                <td>${lectura.coordenadas || ''}</td>
                <td>${lectura.observacion_movil || ''}</td>
                <td>
                    <button class="btn btn-sm btn-info show-details-btn" data-bs-toggle="modal" data-bs-target="#detallesModal"
                            data-imagen="${lectura.imagen || ''}" data-motivo="${lectura.motivo || ''}" data-observacion="${lectura.observacion_movil || ''}">
                        <i class="fas fa-info-circle"></i> Detalles
                    </button>
                </td>
            </tr>
        `;
        });
        $('#lecturasBody').html(html);
    }

    function getConsumoIndicator(lectura) {
        switch(lectura.indicador) {
            case 'Alto':
                return '<span class="badge bg-danger">Alto consumo</span>';
            case 'Bajo':
                return '<span class="badge bg-warning">Bajo consumo</span>';
            default:
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
                ajaxRequest('/lecturas/sincronizar', 'POST', null,
                    function(response) {
                        Swal.fire('Sincronizado!', 'Los datos han sido sincronizados.', 'success');
                        loadInitialData();
                    },
                    function(xhr) {
                        showErrorAlert('Error al sincronizar los datos: ' + (xhr.responseJSON?.error || 'Error desconocido'));
                    }
                );
            }
        });
    }
    function handleEdit() {
        const cuenta = $(this).data('id');
        showLoadingMessage('Cargando datos...');

        ajaxRequest(`/lecturas/${cuenta}/edit`, 'GET', null,
            function(response) {
                hideLoadingMessage();
                console.log('Respuesta del servidor:', response);
                if (response && response.cuenta) {
                    $('#editCuenta').val(response.cuenta);
                    $('#editLectura').val(response.lectura);
                    $('#editObservacion').val(response.observacion_movil);
                    $('#editMotivo').val(response.motivo);
                    $('#editModal').modal('show');
                } else {
                    console.error('Datos incompletos:', response);
                    showErrorAlert('Datos incompletos recibidos del servidor.');
                }
            },
            function(xhr, status, error) {
                hideLoadingMessage();
                console.error('Error en la solicitud AJAX:', status, error);
                console.error('Respuesta del servidor:', xhr.responseText);
                let errorMessage = `Error al cargar los datos para editar. Estado: ${xhr.status}`;
                if (xhr.responseJSON && xhr.responseJSON.detail) {
                    errorMessage += `. Detalle: ${xhr.responseJSON.detail}`;
                }
                showErrorAlert(errorMessage);
            }
        );
    }

    function handleEditSubmit(e) {
        e.preventDefault();
        const cuenta = $('#editCuenta').val();
        const nuevaLectura = $('#editLectura').val();
        const nuevaObservacion = $('#editObservacion').val();
        const nuevoMotivo = $('#editMotivo').val();

        if (!validateEditForm()) {
            return;
        }

        ajaxRequest(`/lecturas/${cuenta}`, 'PUT',
            {
                nueva_lectura: nuevaLectura,
                nueva_observacion: nuevaObservacion,
                nuevo_motivo: nuevoMotivo
            },
            function(response) {
                $('#editModal').modal('hide');
                Swal.fire('Actualizado!', 'El registro ha sido actualizado.', 'success');
                loadInitialData();
            },
            function(xhr) {
                showErrorAlert('Error al actualizar el registro: ' + (xhr.responseJSON?.error || 'Error desconocido'));
            }
        );
    }

    function validateEditForm() {
        let isValid = true;
        $('.is-invalid').removeClass('is-invalid');

        const lectura = $('#editLectura').val();
        if (!lectura || isNaN(lectura) || parseFloat(lectura) < 0) {
            $('#editLectura').addClass('is-invalid');
            $('#lecturaFeedback').text('Por favor, ingrese una lectura válida.');
            isValid = false;
        }

        const observacion = $('#editObservacion').val();
        if (observacion.length > 255) {
            $('#editObservacion').addClass('is-invalid');
            $('#observacionFeedback').text('La observación no puede exceder los 255 caracteres.');
            isValid = false;
        }

        const motivo = $('#editMotivo').val();
        if (motivo.length > 100) {
            $('#editMotivo').addClass('is-invalid');
            $('#motivoFeedback').text('El motivo no puede exceder los 100 caracteres.');
            isValid = false;
        }

        return isValid;
    }

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
                ajaxRequest(`/lecturas/${cuenta}`, 'DELETE', null,
                    function(response) {
                        Swal.fire(
                            'Eliminado!',
                            'El registro ha sido eliminado.',
                            'success'
                        );
                        loadInitialData();
                    },
                    function(xhr) {
                        showErrorAlert('Error al eliminar el registro: ' + (xhr.responseJSON?.error || 'Error desconocido'));
                    }
                );
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

    // Agregar una función para mostrar un mensaje de carga
    function showLoadingMessage(message) {
        Swal.fire({
            title: message || 'Cargando...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
    }

    function hideLoadingMessage() {
        Swal.close();
    }

    function showErrorAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Entendido'
        });
    }

    function ajaxRequest(url, method, data, successCallback, errorCallback) {
        $.ajax({
            url: url,
            method: method,
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: successCallback,
            error: errorCallback
        });
    }
});
