<form action="{{ route('recorridos.store') }}" method="POST">
    @csrf
    <input type="hidden" name="operacion_id" value="{{ $first->id }}">
    
    <div class="mb-3">
        <label for="estatus" class="form-label mb-1">
            <i class="fas fa-map-marker-alt me-1 text-warning"></i>Estatus
        </label>
        <select class="form-select form-select-sm" id="estatus" name="estatus" required>
            <option value="">Selecciona un estatus</option>
            <option value="transito">En Tránsito</option>
            <option value="retraso">Retrasado</option>
            <option value="frontera">En Frontera</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="ubicacion" class="form-label mb-1">
            <i class="fas fa-location-dot me-1 text-warning"></i>Ubicación
        </label>
        <input type="text" class="form-control form-control-sm" id="ubicacion" 
               name="ubicacion" placeholder="Ej: CD. Valles" required>
    </div>

    <div class="mb-2">
        <label for="observacion" class="form-label mb-1">
            <i class="fas fa-sticky-note me-1 text-warning"></i>Observación (Opcional)
        </label>
        <textarea class="form-control form-control-sm" id="observacion" 
                  name="observacion" rows="2"></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button type="submit" class="btn btn-sm btn-warning text-white">
            <i class="fas fa-check me-1"></i>Guardar
        </button>
    </div>
</form>