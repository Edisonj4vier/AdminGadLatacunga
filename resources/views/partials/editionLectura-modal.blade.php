<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel">Editar Lectura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" novalidate>
                    <input type="hidden" id="editCuenta" name="cuenta">
                    <div class="mb-3">
                        <label for="editLectura" class="form-label">Lectura</label>
                        <input type="number" class="form-control" id="editLectura" name="lectura" required min="0" step="0.01" aria-describedby="lecturaHelp lecturaFeedback">
                        <div id="lecturaHelp" class="form-text">Ingrese la nueva lectura del medidor.</div>
                        <div id="lecturaFeedback" class="invalid-feedback">Por favor, ingrese una lectura válida.</div>
                    </div>
                    <div class="mb-3">
                        <label for="editObservacion" class="form-label">Observación</label>
                        <textarea class="form-control" id="editObservacion" name="observacion" rows="2" maxlength="255" aria-describedby="observacionHelp observacionFeedback"></textarea>
                        <div id="observacionHelp" class="form-text">Máximo 255 caracteres.</div>
                        <div id="observacionFeedback" class="invalid-feedback">La observación no puede exceder los 255 caracteres.</div>
                    </div>
                    <div class="mb-3">
                        <label for="editMotivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="editMotivo" name="motivo" maxlength="100" aria-describedby="motivoHelp motivoFeedback">
                        <div id="motivoHelp" class="form-text">Máximo 100 caracteres.</div>
                        <div id="motivoFeedback" class="invalid-feedback">El motivo no puede exceder los 100 caracteres.</div>
                    </div>
                </form>
                <div id="editFeedback" class="alert d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="editForm" class="btn btn-primary" id="saveEdit">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
