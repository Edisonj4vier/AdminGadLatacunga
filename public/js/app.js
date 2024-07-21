$(document).ready(function() {
    initializeEvents();

    // Cerrar alertas automáticamente después de 5 segundos
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);

    $('#form-agregar-editar').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method');
        const username = $('#username').val();
        const rutaId = $('#ruta_id').val();

        // Validación de campos
        if (!username || !rutaId) {
            let errorMessage = '';
            if (!username && !rutaId) {
                errorMessage = 'Por favor, seleccione un lector y una ruta.';
            } else if (!username) {
                errorMessage = 'Por favor, seleccione un lector.';
            } else {
                errorMessage = 'Por favor, seleccione una ruta.';
            }
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: errorMessage,
            });
            return; // Detiene la ejecución si falta algún campo
        }

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Asignación exitosa!',
                        text: 'La ruta ha sido asignada correctamente al lector.',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            actualizarTabla();
                            form[0].reset();
                            $('#username, #ruta_id').val(null).trigger('change'); // Para Select2
                        }
                    });
                } else {
                    let icon = 'warning';
                    let title = 'Advertencia';

                    // Personalizar mensaje basado en la respuesta
                    if (response.message.includes('ya está asignada')) {
                        title = 'Ruta ya asignada';
                    } else if (response.message.includes('no existe')) {
                        title = 'Datos inválidos';
                        icon = 'error';
                    }

                    Swal.fire({
                        icon: icon,
                        title: title,
                        text: response.message,
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'Ha ocurrido un error inesperado.';

                if (response && response.message) {
                    if (response.message.includes('conexión')) {
                        errorMessage = 'Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.';
                    } else if (response.message.includes('autorización')) {
                        errorMessage = 'No tienes permiso para realizar esta acción. Por favor, contacta al administrador.';
                    } else {
                        errorMessage = response.message;
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });

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
                console.error('Error al actualizar la tabla:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de actualización',
                    text: 'No se pudo actualizar la tabla. Por favor, recarga la página.',
                });
            }
        });
    }
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

function initializeEvents() {
    // Inicialización de Select2
    $('.select2').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%'
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
            console.log('Datos recibidos:', data); // Depuración
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
                const selectedUsername = appLectorRuta.login || username;
                const selectedRutaId = appLectorRuta.id || rutaId;

                $('#edit_new_username').val(selectedUsername).trigger('change');
                $('#edit_new_id_ruta').val(selectedRutaId).trigger('change');

                // Actualizar la acción del formulario
                $('#editForm').attr('action', $('#editForm').attr('action').replace(':username', username).replace(':id_ruta', rutaId));

                // Mostrar el modal
                $('#editionModal').modal('show');
            } else {
                console.error('Datos incompletos recibidos del servidor:', data);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos para la edición.',
                });
            }
        },
        error: function(xhr) {
            console.error('Error en la solicitud AJAX:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON ? xhr.responseJSON.message : 'Hubo un problema al obtener los datos.',
            });
        }
    });
});

// Manejo del botón de edición
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');
    const data = form.serialize();

    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (response.success) {
                $('#editionModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetchData(1);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Hubo un problema al actualizar el registro.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
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
    console.log('Fetching data for page:', page);
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
function getCSRFToken() {
    return $('meta[name="csrf-token"]').attr('content');
}
