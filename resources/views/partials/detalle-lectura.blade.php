<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesModalLabel">Detalles de la Lectura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Imagen</h6>
                        <img id="modalImagen" src="" alt="Imagen de la lectura" class="img-fluid mb-3">
                        <p id="noImageMessage" class="text-muted" style="display: none;">No hay imagen disponible</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Información Adicional</h6>
                        <p><strong>Motivo:</strong> <span id="modalMotivo"></span></p>
                        <p><strong>Observación:</strong> <span id="modalObservacion"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.show-details-btn').on('click', function() {
                var imagen = $(this).data('imagen');
                var motivo = $(this).data('motivo');
                var observacion = $(this).data('observacion');

                if (imagen) {
                    $('#modalImagen').attr('src', 'data:image/jpeg;base64,' + imagen).show();
                    $('#noImageMessage').hide();
                } else {
                    $('#modalImagen').hide();
                    $('#noImageMessage').show();
                }

                $('#modalMotivo').text(motivo || 'No especificado');
                $('#modalObservacion').text(observacion || 'No especificado');
            });
        });
    </script>
@endpush
