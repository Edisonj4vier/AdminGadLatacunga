$(document).ready(function() {
    const TYPING_TIMER = 300; // milliseconds
    let typingTimer;
    let allData = []; // Will store all data from the API

    initializeEvents();
    setupSearch();
    loadInitialData();

    function initializeEvents() {
        $('#saveConfig').click(handleSaveConfig);
        $('#perPage').change(() => filterAndDisplayData());
        $(document).on('click', '.pagination a', handlePagination);
        $('#sincronizar').click(handleSyncronization);
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
    function handleSaveConfig() {
        loadInitialData();
        $('#configModal').modal('hide');
    }
    function loadInitialData() {
        const rangoUnidades = $('#rangoUnidades').val();
        const fechaConsulta = $('#fechaConsulta').val();

        $.ajax({
            url: '/lecturas',
            method: 'GET',
            data: {
                rango_unidades: rangoUnidades,
                fecha_consulta: fechaConsulta
            },
            success: function(response) {
                if (response.error) {
                    showErrorAlert('Error: ' + response.error);
                } else {
                    allData = Array.isArray(response.data) ? response.data : [];
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
        let coordenadasCount = {};

        // Primero, contamos las ocurrencias de cada coordenada
        lecturas.forEach(function(lectura) {
            const coordenadas = lectura.coordenadas || '';
            if (coordenadas) {
                coordenadasCount[coordenadas] = (coordenadasCount[coordenadas] || 0) + 1;
            }
        });

        lecturas.forEach(function(lectura) {
            const imagen = lectura.imagen || (lectura.imagen && lectura.imagen.imagen) || '';
            const motivo = lectura.motivo || (lectura.motivo && lectura.motivo.motivo) || '';
            const coordenadas = lectura.coordenadas || '';

            // Determinar el estilo de la fila basado en las coordenadas
            let rowStyle = '';
            if (coordenadas && coordenadasCount[coordenadas] > 1) {
                rowStyle = 'background-color: #FFF9C4;'; // Color pastel amarillo suave
            }

            html += `
        <tr style="${rowStyle}">
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
            <td>${coordenadas}</td>
            <td>${lectura.observacion_movil || ''}</td>
            <td>
                <button class="btn btn-sm btn-info show-details-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#detallesModal"
                        data-imagen="${imagen}"
                        data-motivo="${motivo}"
                        ${(!imagen || !motivo) ? 'disabled' : ''}>
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

    function handleSyncronization() {
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
                        loadInitialData(); // Change this line
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
                    $('#editLectura').val(response.lectura);
                    $('#editObservacion').val(response.observacion);
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
                loadInitialData(); // Change this line
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
                        loadInitialData(); // Change this line
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

        if (imagen && motivo) {
            $('#modalImagen').attr('src', 'data:image/jpeg;base64,' + imagen).show();
            $('#noImageMessage').hide();
            $('#modalMotivo').text(motivo);
            $('#modalObservacion').text(observacion || 'No especificado');
            $('#detallesModal').modal('show');
        } else {
            console.log('No hay suficiente información para mostrar detalles');
        }
    }

    function showErrorAlert(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    loadInitialData();
});
