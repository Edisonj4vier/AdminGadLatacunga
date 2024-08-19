$(document).ready(function() {
    const TYPING_TIMER = 300; // milliseconds
    let typingTimer;
    let allData = []; // Will store all data from the API

    initializeEvents();
    setupSearch();
    loadInitialData();
    loadSavedConfig();

    function initializeEvents() {
        $('#saveConfig').click(handleSaveConfig);
        $('#perPage').change(() => filterAndDisplayData());
        $(document).on('click', '.pagination a', handlePagination);
        $('#sincronizar').click(handleSyncronization);
        $(document).on('click', '#lecturasTable .edit-btn', handleEdit);
        $(document).on('click', '#lecturasTable .delete-btn', handleDelete);
        $(document).on('click', '#lecturasTable .show-details-btn', handleShowDetails);
        $('#crearLectura').click(handleCrearLectura);
        $('#validarCuenta').click(handleValidarCuenta);
        $('#formNuevaLectura').submit(handleGuardarNuevaLectura);
    }

    function setupSearch() {
        $('#searchInput').on('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => filterAndDisplayData(), TYPING_TIMER);
        });
    }
    function handleSaveConfig() {
        const rangoUnidades = $('#rangoUnidades').val();
        const limitePromedio = $('#limitePromedio').val();

        // Guardar los valores en localStorage para persistencia
        localStorage.setItem('rangoUnidades', rangoUnidades);
        localStorage.setItem('limitePromedio', limitePromedio);

        loadInitialData();
        $('#configModal').modal('hide');
    }

    // Función para cargar los valores guardados al iniciar
    function loadSavedConfig() {
        const savedRangoUnidades = localStorage.getItem('rangoUnidades');
        const savedLimitePromedio = localStorage.getItem('limitePromedio');

        if (savedRangoUnidades) $('#rangoUnidades').val(savedRangoUnidades);
        if (savedLimitePromedio) $('#limitePromedio').val(savedLimitePromedio);
    }

    function loadInitialData() {
        const fechaConsulta = formatDate(new Date());
        const limiteRegistros = $('#limiteRegistros').val();

        $.ajax({
            url: '/lecturas',
            method: 'GET',
            data: {
                fecha_consulta: fechaConsulta,
                limite_registros: limiteRegistros
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
                if (xhr.status === 404) {
                    $('#lecturasBody').html('<tr><td colspan="14" class="text-center">No se encontraron registros.</td></tr>');
                } else {
                    let errorMessage = 'Error al cargar los datos.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage += ' ' + xhr.responseJSON.error;
                    }
                    showErrorAlert(errorMessage);
                }
            }
        });
    }
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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
        if (lecturas.length === 0) {
            $('#lecturasBody').html('<tr><td colspan="14" class="text-center">No hay datos disponibles. Las lecturas han sido sincronizadas y copiadas.</td></tr>');
            return;
        }

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
                rowStyle = 'background-color: #FFCCCB;'; // Color pastel amarillo suave
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
                <button class="btn btn-sm ${(!imagen || !motivo) ? 'btn-secondary' : 'btn-info'} show-details-btn"
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

    function checkAbnormalConsumption(lecturas) {
        return lecturas.some(lectura => {
            const consumo = lectura.diferencia || 0;
            const rangoSuperior = lectura.rango_superior || 0;
            const rangoInferior = lectura.rango_inferior || 0;
            return consumo > rangoSuperior || consumo < rangoInferior;
        });
    }

    function checkDuplicateCoordinates(lecturas) {
        const coordCounts = {};
        lecturas.forEach(lectura => {
            if (lectura.coordenadas) {
                coordCounts[lectura.coordenadas] = (coordCounts[lectura.coordenadas] || 0) + 1;
            }
        });
        return Object.values(coordCounts).some(count => count > 1);
    }
    function handleCrearLectura() {
        // Reiniciar el formulario y mostrar el paso 1
        $('#cuenta').val('');
        $('#paso1').show();
        $('#paso2').hide();
        $('#nuevaLecturaModal').modal('show');
    }

    function handleValidarCuenta() {
        const cuenta = $('#cuenta').val();

        $.ajax({
            url: `/obtener-datos-medidor/${cuenta}`,
            method: 'GET',
            success: function(response) {
                // Si llegamos aquí, tenemos datos del medidor y no hay lectura existente
                $('#paso1').hide();
                $('#paso2').show();

                $('#medidor').val(response.medidor || '');
                $('#clave').val(response.clave || '');
                $('#abonado').val(response.abonado || '');
                $('#direccion').val(response.direccion || '');
                $('#coordenadas').val(response.coordenadas || '0.0.0,0.0.0,0.0.0');

                // Limpiar campos de lectura y observación
                $('#lectura').val('');
                $('#observacion').val('');
            },
            error: function(xhr) {
                let errorMessage = 'Error al obtener los datos del medidor. Por favor, intente de nuevo.';
                let icon = 'error';
                let title = 'Error';

                if (xhr.status === 400 && xhr.responseJSON.lectura_existente) {
                    errorMessage = `Esta cuenta ya tiene una lectura registrada. Lectura actual: ${xhr.responseJSON.lectura_actual}`;
                    icon = 'warning';
                    title = 'Lectura Existente';
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: errorMessage
                });

                $('#cuenta').val('');  // Limpiar el campo de cuenta
                $('#paso2').hide();    // Asegurarse de que el formulario no se muestre
                $('#paso1').show();    // Mantener visible el paso de ingresar la cuenta
            }
        });
    }
    function handleGuardarNuevaLectura(e) {
        e.preventDefault();

        const lecturaData = {
            cuenta: $('#cuenta').val(),
            lectura: $('#lectura').val(),
            observacion: $('#observacion').val()
        };

        // Validación de campos requeridos
        if (!lecturaData.cuenta || !lecturaData.lectura || !lecturaData.observacion) {
            showErrorAlert('Todos los campos son obligatorios.');
            return;
        }

        $.ajax({
            url: '/movil-lectura',
            method: 'POST',
            data: JSON.stringify(lecturaData),
            contentType: 'application/json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.mensaje || 'La lectura se ha guardado correctamente.'
                });
                $('#nuevaLecturaModal').modal('hide');
                loadInitialData(); // Recargar los datos después de crear una nueva lectura
            },
            error: function(xhr) {
                let errorMessage = 'No se pudo guardar la lectura. Por favor, intente de nuevo.';
                let errorType = 'ERROR_GENERAL';

                if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.error || errorMessage;
                    errorType = xhr.responseJSON.errorType || errorType;
                }

                switch(errorType) {
                    case 'CUENTA_NO_EXISTE':
                        Swal.fire({
                            icon: 'error',
                            title: 'Cuenta no encontrada',
                            text: errorMessage
                        });
                        break;
                    case 'LECTURA_YA_EXISTE':
                        Swal.fire({
                            icon: 'warning',
                            title: 'Lectura existente',
                            text: errorMessage
                        });
                        break;
                    case 'VALIDACION_FALLIDA':
                        showErrorAlert('Por favor, verifique los datos ingresados: ' + errorMessage);
                        break;
                    default:
                        showErrorAlert(errorMessage);
                }
            }
        });
    }
    function handleSyncronization() {
        const hasAbnormalConsumption = checkAbnormalConsumption(allData);
        const hasDuplicateCoordinates = checkDuplicateCoordinates(allData);

        let warningMessage = "";
        if (hasAbnormalConsumption) {
            warningMessage += "Se han detectado lecturas con consumo anormal (alto o bajo).\n";
        }
        if (hasDuplicateCoordinates) {
            warningMessage += "Se han detectado coordenadas duplicadas.\n";
        }

        if (warningMessage) {
            warningMessage += "\n¿Está seguro de que desea continuar con la actualización de lecturas?";
        } else {
            warningMessage = "Se actualizarán los datos de lecturas. ¿Desea continuar?";
        }

        Swal.fire({
            title: '¿Está seguro?',
            text: warningMessage,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/lecturas/actualizar',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Actualizado!',
                            response.mensaje,
                            'success'
                        ).then(() => {
                            if (response.tabla_vaciada) {
                                allData = [];
                                updateTableContent([]);
                            }
                            copiarEvidencias();
                        });
                    },
                    error: function(xhr) {
                        showErrorAlert('Error al actualizar los datos de lecturas: ' + (xhr.responseJSON?.error || 'Error desconocido'));
                    }
                });
            }
        });
    }
    function copiarEvidencias() {
        Swal.fire({
            title: 'Copiando evidencias',
            text: 'Este proceso puede tardar unos momentos...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/lecturas/copiar-evidencias',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire(
                    'Evidencias copiadas',
                    response.mensaje,
                    'success'
                ).then(() => {
                    if (response.tabla_vaciada) {
                        allData = [];
                        updateTableContent([]);
                    }
                });
            },
            error: function(xhr) {
                showErrorAlert('Error al copiar evidencias: ' + (xhr.responseJSON?.error || 'Error desconocido'));
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
