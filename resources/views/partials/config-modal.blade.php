<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalLabel">Configuración</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rangoUnidades" class="form-label">Rango de unidades</label>
                    <input type="number" name="rango_unidades" id="rangoUnidades" class="form-control"
                           value="{{ request('rango_unidades', 2) }}" min="0" step="0.1">
                </div>
                <div class="mb-3">
                    <label for="limitePromedio" class="form-label">Límite de promedio</label>
                    <input type="number" name="limite_promedio" id="limitePromedio" class="form-control"
                           value="{{ request('limite_promedio', 3) }}" min="1" step="1">
                </div>
                <input type="hidden" name="fecha_consulta" id="fechaConsulta"
                       value="{{ request('fecha_consulta', now()->toDateString()) }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveConfig">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
