<div class="modal fade" id="nuevaLecturaModal" tabindex="-1" aria-labelledby="nuevaLecturaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaLecturaModalLabel">Nueva Lectura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Paso 1: Validación de cuenta -->
                <div id="paso1">
                    <div class="mb-3">
                        <label for="cuenta" class="form-label">Cuenta</label>
                        <input type="text" class="form-control" id="cuenta" required>
                    </div>
                    <button type="button" class="btn btn-primary" id="validarCuenta">Validar Cuenta</button>
                </div>

                <!-- Paso 2: Formulario de lectura -->
                <div id="paso2" style="display: none;">
                    <form id="formNuevaLectura">
                        <div class="mb-3">
                            <label for="medidor" class="form-label">Medidor</label>
                            <input type="text" class="form-control" id="medidor" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Clave</label>
                            <input type="text" class="form-control" id="clave" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="abonado" class="form-label">Abonado</label>
                            <input type="text" class="form-control" id="abonado" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="lectura" class="form-label">Lectura</label>
                            <input type="number" class="form-control" id="lectura" required>
                        </div>
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="coordenadas" class="form-label">Coordenadas</label>
                            <input type="text" class="form-control" id="coordenadas" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Lectura</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
