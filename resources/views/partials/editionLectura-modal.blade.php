<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel">Editar Lectura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editCuenta">
                    <div class="mb-3">
                        <label for="editLectura" class="form-label">Lectura</label>
                        <input type="number" class="form-control" id="editLectura" required min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="editObservacion" class="form-label">Observación</label>
                        <textarea class="form-control" id="editObservacion" rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editMotivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="editMotivo" maxlength="100">
                    </div>
                </form>
                <div id="editFeedback" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveEdit">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
