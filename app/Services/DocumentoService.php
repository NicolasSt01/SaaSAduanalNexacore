<?php

namespace App\Services;

use App\Models\Documento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentoService
{
    /**
     * Sube un documento al disco configurado (ej. R2) y guarda los metadatos en base de datos
     */
    public function uploadDocument(UploadedFile $file, $tenantId, $operacionId, $tipoDocumento, $pedimentoId = null)
    {
        $extension = $file->getClientOriginalExtension();
        $nombreOriginal = $file->getClientOriginalName();
        $peso = $file->getSize();
        
        // Generar una ruta unica por tenant y operacion
        $path = "tenant_{$tenantId}/op_{$operacionId}/" . time() . "_{$nombreOriginal}";
        
        // Tomar el disco por defecto (podría ser cambiado dinámicamente si es necesario)
        $disk = config('filesystems.default', 'r2');
        
        // Subir al disco configurado
        Storage::disk($disk)->put($path, file_get_contents($file));
        
        // Generar la URL de acceso
        $url = Storage::disk($disk)->url($path);
        
        // Crear y regresar el registro en base de datos
        return Documento::create([
            'tenant_id' => $tenantId,
            'operacion_id' => $operacionId,
            'pedimento_id' => $pedimentoId,
            'tipo_documento' => $tipoDocumento,
            'nombre' => $nombreOriginal,
            'ruta' => $path,
            'url_archivo' => $url,
            'peso' => $peso,
            'extension' => $extension,
        ]);
    }

    /**
     * Genera una URL firmada temporalmente (usado para archivos confidenciales)
     */
    public function getTemporaryUrl(Documento $documento, $minutes = 30)
    {
        $disk = config('filesystems.default', 'r2');
        
        if ($disk === 's3' || $disk === 'r2') {
            return Storage::disk($disk)->temporaryUrl($documento->ruta, now()->addMinutes($minutes));
        }

        // Si es storage local
        return asset('storage/' . $documento->ruta);
    }
}
