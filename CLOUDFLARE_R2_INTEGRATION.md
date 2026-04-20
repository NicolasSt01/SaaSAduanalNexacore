# Integración de Almacenamiento Cloudflare R2 - NexaCore Aduanal

Este documento detalla la configuración y el flujo de implementación para migrar el almacenamiento de documentos a Cloudflare R2, permitiendo una gestión escalable, segura y aislada por Tenant.

## 1. Motivación
Para evitar la saturación del servidor local y mejorar la seguridad, utilizaremos Cloudflare R2 (compatible con S3) para almacenar los documentos. Esto nos permite separar los archivos por:
- **Tenant ID**: Cada cliente tiene su propio espacio lógico.
- **Operación ID**: Organización por flujo operativo.

## 2. Configuración del Entorno (.env)

Se deben agregar las siguientes variables al archivo `.env` para habilitar la conexión con R2:

```env
# Cloudflare R2 Configuration
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=tu_access_key_id
R2_SECRET_ACCESS_KEY=tu_secret_access_key
R2_BUCKET=nexacore-aduanal-docs
R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
R2_REGION=auto
R2_URL=https://pub-your-worker-or-custom-domain.com
```

## 3. Configuración de Laravel (config/filesystems.php)

En el archivo `config/filesystems.php`, dentro del array `disks`, se debe registrar el nuevo disco:

```php
'r2' => [
    'driver' => 's3',
    'key' => env('R2_ACCESS_KEY_ID'),
    'secret' => env('R2_SECRET_ACCESS_KEY'),
    'region' => env('R2_REGION', 'auto'),
    'bucket' => env('R2_BUCKET'),
    'url' => env('R2_URL'),
    'endpoint' => env('R2_ENDPOINT'),
    'use_path_style_endpoint' => true,
    'throw' => false,
],
```

## 4. Estructura de Datos (Base de Datos)

Para persistir la información de los archivos cargados, la tabla `documentos` (o equivalente) debe contener los siguientes campos:

| Campo | Tipo | Descripción |
| :--- | :--- | :--- |
| `id` | UUID/BigInt | Identificador único |
| `tipo_documento` | String | Ejemplo: 'Pedimento', 'Factura', 'BL' |
| `nombre_archivo` | String | Nombre original del archivo |
| `path_archivo` | String | Ruta relativa en el bucket (e.g., `tenant_1/op_102/archivo.pdf`) |
| `url_archivo` | Text | URL pública o firmada para visualización |
| `peso` | BigInt | Tamaño en bytes |
| `extension` | String | Extensión del archivo (pdf, jpg, xml) |
| `tenant_id` | Foreign Key | Relación con el tenant |
| `operacion_id`| Foreign Key | Relación con la operación específica |

No se eliminaran columnas existentes, solo se actualizaran o agregaran las existentes
## 5. Implementación del Flujo de Carga (Service Pattern)

### Ruta sugerida en R2:
`{tenant_id}/{operacion_id}/{tipo_documentos}/{timestamp}_{nombre_archivo}`

### Código de ejemplo para carga:

```php
use Illuminate\Support\Facades\Storage;

public function uploadDocument($file, $tenantId, $operacionId, $tipoDocumento) 
{
    $extension = $file->getClientOriginalExtension();
    $nombreOriginal = $file->getClientOriginalName();
    $peso = $file->getSize();
    
    // Generar ruta única
    $path = "tenant_{$tenantId}/op_{$operacionId}/" . time() . "_{$nombreOriginal}";
    
    // Subir a R2
    Storage::disk('r2')->put($path, file_get_contents($file));
    
    // Obtener URL
    $url = Storage::disk('r2')->url($path);
    
    // Guardar en DB
    return Documento::create([
        'tipo_documento' => $tipoDocumento,
        'nombre_archivo' => $nombreOriginal,
        'path_archivo'   => $path,
        'url_archivo'    => $url,
        'peso'           => $peso,
        'extension'      => $extension,
        'tenant_id'      => $tenantId,
        'operacion_id'   => $operacionId,
    ]);
}
```

## 6. Visualización y Seguridad

- **Archivos Públicos**: Si el bucket es público, `Storage::url($path)` devolverá la URL directa.
- **Archivos Privados (Recomendado)**: Usar URLs firmadas temporalmente para asegurar que solo usuarios autenticados vean los documentos:
  ```php
  $url = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(30));
  ```

## 7. Próximos Pasos
1. Ejecutar migración de la tabla `documentos`.
2. Configurar CORS en el panel de Cloudflare R2 para permitir peticiones desde el dominio de NexaCore.
3. Implementar componente Livewire/Vue para la carga interactiva con barra de progreso.
