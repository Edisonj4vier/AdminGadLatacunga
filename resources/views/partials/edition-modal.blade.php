<div class="modal fade" id="editionModal" tabindex="-1" aria-labelledby="editionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST" action="{{route('app-lector-ruta.update', ['username' => ':username', 'id_ruta' => ':id_ruta'])}}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editionModalLabel">Editar Ruta del Lector</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_new_username" class="form-label">Lector</label>
                        <select class="form-select select2" id="edit_new_username" name="new_username" required>
                            <option value="">Seleccione Lector</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_new_id_ruta" class="form-label">Ruta</label>
                        <select class="form-select select2" id="edit_new_id_ruta" name="new_id_ruta" required>
                            <option value="">Seleccione Ruta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_fecha" class="form-label">Fecha de Asignación</label>
                        <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
