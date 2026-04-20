<form action="{{ route('conceptos.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return handleConceptoSubmit(event)">
    @csrf
    <input type="hidden" name="ambito" value="camion">

    <div class="mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-file-invoice me-1 text-success"></i>Operación/Factura *
        </label>
        <select class="form-select" name="operacion_id" required>
            <option value="">Selecciona la factura...</option>
            @foreach($registros as $exp)
                <option value="{{ $exp->id }}">
                    {{ $exp->cliente->nombre_empresa ?? 'Sin cliente' }} - Factura #{{ $exp->num_factura }} - REF#{{ $exp->referencia }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-tag me-1 text-success"></i>Tipo de Concepto *
        </label>
        <input type="text" class="form-control" name="tipo_concepto" 
               placeholder="Ejemplo: Reacomodo Tarimas, Maniobras, etc." required>
    </div>

    <input type="hidden" name="monto" value="0">

    <div class="mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-comment-dots me-1 text-success"></i>Descripción
        </label>
        <textarea class="form-control" name="descripcion" rows="2" 
                  placeholder="Agrega detalles adicionales (opcional)"></textarea>
    </div>

    {{-- 🔥 NUEVO: Campo para subir archivo --}}
    <div class="mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-paperclip me-1 text-success"></i>Adjuntar Archivo (Opcional)
        </label>
        <input type="file" class="form-control" name="archivo" id="archivoConcepto"
               accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.doc,.docx">
        <small class="text-muted">Formatos permitidos: PDF, Imágenes, Excel, Word (máx. 50MB)</small>

        {{-- Vista previa del archivo --}}
        <div id="previewArchivo" class="mt-3" style="display: none;">
            <div class="card border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div id="iconoArchivo" class="me-3 fs-2"></div>
                            <div>
                                <div class="fw-bold" id="nombreArchivo"></div>
                                <small class="text-muted">
                                    <span id="tipoArchivo"></span> •
                                    <span id="tamanoArchivo"></span>
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                onclick="quitarArchivo()" title="Quitar archivo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info py-2 px-3 small mb-0">
        <i class="fas fa-info-circle me-1"></i>
        <strong>Nota:</strong> Este concepto se cobrará una sola vez por camión.
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button type="submit" class="btn btn-success rounded-pill px-4">
            <i class="fas fa-check me-1"></i>Guardar Concepto
        </button>
    </div>
</form>

<script>
    // Script para vista previa del archivo
    document.getElementById('archivoConcepto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            mostrarPreview(file);
        }
    });

    function mostrarPreview(file) {
        const preview = document.getElementById('previewArchivo');
        const icono = document.getElementById('iconoArchivo');
        const nombre = document.getElementById('nombreArchivo');
        const tipo = document.getElementById('tipoArchivo');
        const tamano = document.getElementById('tamanoArchivo');

        // Obtener extensión del archivo
        const extension = file.name.split('.').pop().toLowerCase();

        // Iconos según tipo de archivo
        const iconos = {
            'pdf': '<i class="fas fa-file-pdf text-danger"></i>',
            'jpg': '<i class="fas fa-file-image text-primary"></i>',
            'jpeg': '<i class="fas fa-file-image text-primary"></i>',
            'png': '<i class="fas fa-file-image text-primary"></i>',
            'xlsx': '<i class="fas fa-file-excel text-success"></i>',
            'xls': '<i class="fas fa-file-excel text-success"></i>',
            'doc': '<i class="fas fa-file-word text-info"></i>',
            'docx': '<i class="fas fa-file-word text-info"></i>',
            'default': '<i class="fas fa-file text-secondary"></i>'
        };

        icono.innerHTML = iconos[extension] || iconos['default'];
        nombre.textContent = file.name;
        tipo.textContent = extension.toUpperCase();
        tamano.textContent = formatBytes(file.size);

        preview.style.display = 'block';
    }

    function quitarArchivo() {
        document.getElementById('archivoConcepto').value = '';
        document.getElementById('previewArchivo').style.display = 'none';
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
</script>