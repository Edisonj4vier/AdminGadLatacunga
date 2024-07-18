<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Lectura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editCuenta">
                    <div class="mb-3">
                        <label for="editLectura" class="form-label">Lectura</label>
                        <input type="number" class="form-control" id="editLectura" required>
                    </div>
                    <div class="mb-3">
                        <label for="editObservacion" class="form-label">Observación</label>
                        <input type="text" class="form-control" id="editObservacion">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveEdit">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
