$(document).ready(function() {
    initializeEvents();
    setTimeout(setTodayDate, 100);

    // Cerrar alertas automáticamente después de 5 segundos
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);

    // Función para manejar la respuesta de asignación/actualización de ruta
    // Función para manejar la respuesta de asignación/actualización de ruta
    function handleRouteAssignmentResponse(response, action) {
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: action === 'assign' ? '¡Asignación exitosa!' : 'Actualización exitosa',
                text: response.message,
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    actualizarTabla();
                    if (action === 'assign') {
                        $('#form-agregar-editar')[0].reset();
                        $('#username, #ruta_id').val(null).trigger('change');
                    } else {
                        $('#editionModal').modal('hide');
                    }
                }
            });
        } else {
            let errorMessage = response.message || 'Ha ocurrido un error.';
            if (response.validationMessages && response.validationMessages.length > 0) {
                errorMessage += '<br><br>' + response.validationMessages.join('<br>');
            } else if (response.errors) {
                errorMessage += '<br>' + Object.values(response.errors).flat().join('<br>');
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessage,
                confirmButtonText: 'Entendido'
            });
        }
    }
    // Manejo del formulario de agregar
    $('#form-agregar-editar').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method');
        const submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function(response) {
                handleRouteAssignmentResponse(response, 'assign');
            },
            error: function(xhr) {
                handleRouteAssignmentResponse(xhr.responseJSON, 'assign');
            },
            complete: function() {
                submitButton.prop('disabled', false);
            }
        });
    });

    // Manejo del formulario de edición
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': getCSRFToken()
            },
            success: function(response) {
                handleRouteAssignmentResponse(response, 'update');
            },
            error: function(xhr) {
                handleRouteAssignmentResponse(xhr.responseJSON, 'update');
            },
            complete: function() {
                submitButton.prop('disabled', false);
            }
        });
    });


    // Función para obtener el token CSRF
    function getCSRFToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    // Función para actualizar la tabla
    function actualizarTabla() {
        $.ajax({
            url: '/app-lector-ruta',
            method: 'GET',
            data: { ajax: true },
            success: function(data) {
                $('#table-container').html(data);
                reinicializarComponentes();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de actualización',
                    text: 'No se pudo actualizar la tabla. Por favor, recarga la página.',
                });
            }
        });
    }

// Manejo del formulario de edición
    $(document).on('click', '#appLectorRutaTable .edit-btn', function() {
        const username = $(this).data('username');
        const rutaId = $(this).data('ruta-id');
        $.ajax({
            url: `/app-lector-ruta/${username}/${rutaId}/edit`,
            method: 'GET',
            success: function(data) {
                if (data.appLectorRuta && data.usuarios && data.rutas) {
                    const appLectorRuta = data.appLectorRuta;
                    const usuarios = data.usuarios;
                    const rutas = data.rutas;

                    // Poblar el select de usuarios
                    $('#edit_new_username').empty().append('<option value="">Seleccione Lector</option>');
                    usuarios.forEach(usuario => {
                        $('#edit_new_username').append(`<option value="${usuario.login}">${usuario.login}</option>`);
                    });

                    // Poblar el select de rutas
                    $('#edit_new_id_ruta').empty().append('<option value="">Seleccione Ruta</option>');
                    rutas.forEach(ruta => {
                        $('#edit_new_id_ruta').append(`<option value="${ruta.id}">${ruta.nombreruta}</option>`);
                    });

                    // Establecer los valores seleccionados
                    const selectedUsername = appLectorRuta.login_usuario || username;
                    const selectedRutaId = appLectorRuta.id_ruta || rutaId;

                    // Manejar la fecha del endpoint
                    let selectedFecha = '';
                    if (appLectorRuta.fecha) {
                        // Asumimos que la fecha del endpoint viene en formato ISO
                        selectedFecha = appLectorRuta.fecha.split('T')[0];
                    }

                    $('#edit_new_username').val(selectedUsername).trigger('change');
                    $('#edit_new_id_ruta').val(selectedRutaId).trigger('change');
                    $('#edit_fecha').val(selectedFecha);


                    // Actualizar la acción del formulario
                    $('#editForm').attr('action', $('#editForm').attr('action').replace(':username', username).replace(':id_ruta', rutaId));

                    // Mostrar el modal
                    $('#editionModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los datos para la edición.',
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON ? xhr.responseJSON.message : 'Hubo un problema al obtener los datos.',
                });
            }
        });
    });

// Manejo del formulario de eliminación
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const username = $(this).data('username');
        const rutaId = $(this).data('ruta-id');
        const url = `/app-lector-ruta/${username}/${rutaId}`;

        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken()
                    },
                    success: function(response) {
                        if (response.success) {
                            $(e.target).closest('tr').remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                            });
                            if ($('#appLectorRutaTable tbody tr').length === 0) {
                                $('#appLectorRutaTable').hide();
                                $('<p class="text-center">No hay rutas registradas. Agregue una nueva ruta para mostrar la tabla.</p>').insertAfter('#appLectorRutaTable');
                            }
                            fetchData(1);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Hubo un problema al comunicarse con el servidor.',
                        });
                    }
                });
            }
        });
    });

    function fetchData(page) {
        $.ajax({
            url: '/app-lector-ruta?page=' + page,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': getCSRFToken()
            },
            success: function(data) {
                $('#table-container').html(data);
                // Reinicializar Select2 y otros eventos después de actualizar la tabla
                $('.select2').select2({
                    placeholder: "Seleccione una opción",
                    allowClear: true,
                    width: '100%'
                });
                initializeEvents();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON ? xhr.responseJSON.message : 'Hubo un problema al obtener los datos.',
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const logoutForm = document.querySelector('#sidebar form');
        if (logoutForm) {
            logoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¿Quieres cerrar la sesión?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }
    });
    // Manejo de la paginación
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetchData(page);
    });

    // Mostrar alertas de éxito o error con SweetAlert2
    if (typeof successMessage !== 'undefined' && successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: successMessage,
        });
    }

    if (typeof errorMessage !== 'undefined' && errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
        });
    }
});


function getTodayLocalDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Los meses en JS van de 0 a 11
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function setTodayDate() {
    const today = getTodayLocalDate();
    $('#fecha').val(today);
}
function initializeEvents() {
    // Inicialización de Select2
    $('.select2').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%'
    });
    setTodayDate();
}







function reinicializarComponentes() {
    $('.select2').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%'
    });
    setTodayDate();
}
function getCSRFToken() {
    return $('meta[name="csrf-token"]').attr('content');
}


(function() {
    let inactivityTime = 0;
    const maxInactivityTime = 15 * 60; // 15 minutos en segundos
    const warningTime = 60; // Mostrar advertencia 1 minuto antes

    function resetTimer() {
        inactivityTime = 0;
    }

    function checkInactivity() {
        inactivityTime++;
        if (inactivityTime === maxInactivityTime - warningTime) {
            showWarning();
        } else if (inactivityTime >= maxInactivityTime) {
            logout();
        }
    }

    function showWarning() {
        Swal.fire({
            title: 'Advertencia de inactividad',
            html: 'Su sesión se cerrará en <b></b> segundos.',
            timer: warningTime * 1000,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'Mantener sesión activa',
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                timerInterval = setInterval(() => {
                    b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                logout();
            } else {
                resetTimer();
            }
        });
    }

    function logout() {
        Swal.fire({
            title: 'Sesión expirada',
            text: "Su sesión ha expirado debido a inactividad.",
            icon: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/logout',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCSRFToken()
                    },
                    success: function(response) {
                        if (response.message) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sesión cerrada',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '/login';
                            });
                        } else {
                            window.location.href = '/login';
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cerrar la sesión. Por favor, intente nuevamente.',
                        }).then(() => {
                            window.location.href = '/login';
                        });
                    }
                });
            }
        });
    }

    // Eventos para resetear el temporizador
    ['click', 'touchstart', 'mousemove'].forEach(evt =>
        document.addEventListener(evt, resetTimer, false)
    );

    // Iniciar el temporizador
    setInterval(checkInactivity, 1000);
})();
