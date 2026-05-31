<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio centralizado para el almacenamiento de documentos en Cloudflare R2.
 * 
 * INC-001: Implementación completa de R2 para aislamiento por tenant,
 * escalabilidad y eliminación de saturación de disco local.
 */
class DocumentoStorageService
{
    /**
     * Disco de almacenamiento configurado (r2).
     */
    protected string $disk = 'r2';

    /**
     * Sube un archivo a R2 y retorna los metadatos necesarios.
     *
     * @param UploadedFile $file
     * @param int $tenantId
     * @param int|null $operacionId
     * @param string|null $tipoDocumento
     * @param string|null $nombrePersonalizado
     * @param int|null $clienteId
     * @return array{path: string, url: string, peso: int, extension: string}
     */
    public function upload(
        UploadedFile $file,
        int $tenantId,
        ?int $operacionId = null,
        ?string $tipoDocumento = null,
        ?string $nombrePersonalizado = null,
        ?int $clienteId = null
    ): array {
        $extension = strtolower($file->getClientOriginalExtension());
        $nombreOriginal = $nombrePersonalizado 
            ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $nombreOriginal = Str::slug($nombreOriginal, '_');
        $peso = $file->getSize();

        // Sanitizar tipo_documento para la ruta
        $tipoFolder = $tipoDocumento ? Str::slug($tipoDocumento, '_') : 'general';

        // Generar ruta estructurada: tenant_X/{cliente_Y|op_Y}/tipo_doc/timestamp_nombre.ext
        $basePath = "tenant_{$tenantId}";
        if ($clienteId) {
            $basePath .= "/cliente_{$clienteId}";
        } elseif ($operacionId) {
            $basePath .= "/op_{$operacionId}";
        }
        if ($clienteId && $operacionId) {
            // Caso híbrido: documento de operación de un cliente
            $basePath = "tenant_{$tenantId}/op_{$operacionId}";
        }
        $basePath .= "/{$tipoFolder}";

        $fileName = time() . '_' . uniqid() . '_' . $nombreOriginal . '.' . $extension;
        $path = $basePath . '/' . $fileName;

        // Subir a R2 usando stream (evita cargar archivo completo en memoria)
        $stream = fopen($file->getPathname(), 'r');
        Storage::disk($this->disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Obtener URL pública
        $url = Storage::disk($this->disk)->url($path);

        return [
            'path' => $path,
            'url' => $url,
            'peso' => $peso,
            'extension' => $extension,
        ];
    }

    /**
     * Elimina un archivo de R2 dado su ruta.
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Genera una URL firmada temporal para descarga privada.
     * Si el bucket es público, la URL pública es suficiente.
     */
    public function temporaryUrl(string $path, int $expirationMinutes = 5): string
    {
        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($expirationMinutes));
    }

    /**
     * Verifica si un archivo existe en R2.
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Descarga un archivo desde R2.
     */
    public function download(string $path, ?string $name = null)
    {
        return Storage::disk($this->disk)->download($path, $name);
    }

    /**
     * Obtiene el contenido de un archivo desde R2.
     */
    public function get(string $path): string
    {
        return Storage::disk($this->disk)->get($path);
    }

    /**
     * Obtiene el mime type de un archivo.
     */
    public function mimeType(string $path): string
    {
        return Storage::disk($this->disk)->mimeType($path);
    }
}
