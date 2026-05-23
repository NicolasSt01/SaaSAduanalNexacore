<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Operacion;
use App\Services\DocumentoStorageService;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    protected NotificacionService $notificacionService;
    protected DocumentoStorageService $storageService;

    public function __construct(
        NotificacionService $notificacionService,
        DocumentoStorageService $storageService
    ) {
        $this->middleware('auth');
        $this->notificacionService = $notificacionService;
        $this->storageService = $storageService;
    }

    /**
     * Subir documento maestro vinculado a un expediente.
     * Ruta: POST expedientes/{expediente}/documentos
     */
    public function store(Request $request, Expediente $expediente)
    {
        try {
            $request->validate([
                'nombre_documento' => 'required|string|max:255',
                'archivo' => 'required|file|max:51200',
                'tipo_documento' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'nullable|exists:operaciones,id',
            ]);

            $file = $request->file('archivo');
            $tenantId = auth()->user()->tenant_id;

            $meta = $this->storageService->upload(
                $file,
                $tenantId,
                $request->operacion_id,
                $request->tipo_documento,
                $request->nombre_documento
            );

            Documento::create([
                'tenant_id' => $tenantId,
                'pedimento_id' => $expediente->id,
                'operacion_id' => $request->operacion_id,
                'nombre' => $request->nombre_documento,
                'ruta' => $meta['path'],
                'url_archivo' => $meta['url'],
                'peso' => $meta['peso'],
                'extension' => $meta['extension'],
                'tipo_documento' => $request->tipo_documento,
            ]);

            return back()->with('success', 'Documento subido correctamente a Cloudflare R2.');
        } catch (\Throwable $e) {
            Log::error('Error al subir documento maestro', [
                'error' => $e->getMessage(),
                'expediente_id' => $expediente->id,
                'user_id' => auth()->id(),
            ]);
            return back()->with('error', 'Error al subir documento: ' . $e->getMessage());
        }
    }

    /**
     * Subir múltiples documentos desde tráfico (store2).
     * Ruta: POST /documentos/savedoc
     */
    public function store2(Request $request)
    {
        try {
            $request->validate([
                'archivos.*' => 'required|file|max:51200',
                'tipo_documento' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'nullable|exists:operaciones,id',
            ]);

            $uploadedCount = 0;
            $errors = [];
            $tenantId = auth()->user()->tenant_id;

            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    try {
                        $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                        $meta = $this->storageService->upload(
                            $file,
                            $tenantId,
                            $request->operacion_id,
                            $request->tipo_documento,
                            $nombreOriginal
                        );

                        Documento::create([
                            'tenant_id' => $tenantId,
                            'pedimento_id' => $request->id,
                            'operacion_id' => $request->operacion_id,
                            'nombre' => $nombreOriginal,
                            'ruta' => $meta['path'],
                            'url_archivo' => $meta['url'],
                            'peso' => $meta['peso'],
                            'extension' => $meta['extension'],
                            'tipo_documento' => $request->tipo_documento ?? 'otros',
                        ]);

                        $uploadedCount++;
                    } catch (\Throwable $e) {
                        $errors[] = "Error al subir '{$file->getClientOriginalName()}': " . $e->getMessage();
                        Log::warning('Fallo al subir archivo en store2', ['error' => $e->getMessage()]);
                    }
                }

                if ($uploadedCount > 0 && $request->operacion_id) {
                    $operacion = Operacion::find($request->operacion_id);
                    if ($operacion) {
                        $this->notificacionService->notificarDocumentosSubidos($operacion, $uploadedCount);
                    }
                }
            }

            if ($uploadedCount > 0) {
                $message = $uploadedCount === 1
                    ? 'Documento subido correctamente.'
                    : "$uploadedCount documentos subidos correctamente.";

                if (!empty($errors)) {
                    $message .= ' Sin embargo, algunos archivos fallaron: ' . implode(', ', $errors);
                }

                return back()->with('success', $message);
            }

            return back()->with('error', 'No se pudo subir ningún documento. ' . implode(', ', $errors));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error de validación: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Throwable $e) {
            Log::error('Error general en store2', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }

    /**
     * Subir múltiples documentos con tipo individual por archivo (store3).
     * Ruta: POST /documentos/savedoctrafico
     */
    public function store3(Request $request)
    {
        try {
            $request->validate([
                'archivos' => 'required|array',
                'archivos.*' => 'file|max:51200',
                'tipos_documento' => 'nullable|array',
                'tipos_documento.*' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'required|exists:operaciones,id',
            ]);

            $archivos = $request->file('archivos');
            $tiposDocumento = $request->input('tipos_documento', []);
            $cantidadSubidos = 0;
            $tenantId = auth()->user()->tenant_id;

            foreach ($archivos as $index => $file) {
                $nombreArchivo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $tipoDocumento = $tiposDocumento[$index] ?? 'otros';

                $meta = $this->storageService->upload(
                    $file,
                    $tenantId,
                    $request->operacion_id,
                    $tipoDocumento,
                    $nombreArchivo
                );

                Documento::create([
                    'tenant_id' => $tenantId,
                    'operacion_id' => $request->operacion_id,
                    'nombre' => $nombreArchivo,
                    'ruta' => $meta['path'],
                    'url_archivo' => $meta['url'],
                    'peso' => $meta['peso'],
                    'extension' => $meta['extension'],
                    'tipo_documento' => $tipoDocumento,
                ]);

                $cantidadSubidos++;
            }

            return response()->json([
                'success' => true,
                'message' => "$cantidadSubidos documento(s) subido(s) correctamente.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en store3', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar documento (de R2 y de BD).
     */
    public function destroy(Documento $documento)
    {
        try {
            if ($documento->en_r2) {
                $this->storageService->delete($documento->ruta);
            } else {
                if (Storage::disk('local')->exists($documento->ruta)) {
                    Storage::disk('local')->delete($documento->ruta);
                }
            }
            $documento->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Documento eliminado correctamente.']);
            }

            return back()->with('success', 'Documento eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar documento: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }

    /**
     * Descargar documento desde R2 o local.
     */
    public function download(Documento $documento)
    {
        try {
            $fileName = ($documento->nombre ?? 'documento') . '.' . ($documento->extension ?? pathinfo($documento->ruta, PATHINFO_EXTENSION));

            if ($documento->en_r2) {
                return $this->storageService->download($documento->ruta, $fileName);
            }

            // Fallback local legacy
            if (!Storage::disk('local')->exists($documento->ruta)) {
                abort(404, 'Archivo no encontrado');
            }

            return Storage::disk('local')->download($documento->ruta, $fileName);
        } catch (\Throwable $e) {
            Log::error('Error al descargar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Archivo no disponible.');
        }
    }

    /**
     * Preview de documento (PDF, imágenes, etc.).
     */
    public function preview(Documento $documento)
    {
        try {
            if ($documento->en_r2) {
                $url = Storage::disk('r2')->temporaryUrl(
                    $documento->ruta,
                    now()->addMinutes(30)
                );
                return redirect()->away($url);
            }

            // Fallback local legacy
            $ruta = $documento->ruta;
            if (!Storage::disk('local')->exists($ruta)) {
                abort(404, 'Archivo no encontrado');
            }

            return response()->file(Storage::disk('local')->path($ruta));
        } catch (\Throwable $e) {
            Log::error('Error al previsualizar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Archivo no disponible para previsualización.');
        }
    }

    /**
     * Subir archivo para un concepto adicional.
     */
    public function storeConceptoAdicional(Request $request)
    {
        try {
            $request->validate([
                'concepto_adicional_id' => 'required|exists:conceptos_adicionales,id',
                'archivo' => 'required|file|max:51200',
                'nombre_documento' => 'nullable|string|max:255',
            ]);

            $file = $request->file('archivo');
            $tenantId = auth()->user()->tenant_id;
            $nombreDocumento = $request->nombre_documento
                ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $meta = $this->storageService->upload(
                $file,
                $tenantId,
                null,
                'concepto_adicional',
                $nombreDocumento
            );

            Documento::create([
                'tenant_id' => $tenantId,
                'concepto_adicional_id' => $request->concepto_adicional_id,
                'nombre' => $nombreDocumento,
                'ruta' => $meta['path'],
                'url_archivo' => $meta['url'],
                'peso' => $meta['peso'],
                'extension' => $meta['extension'],
                'tipo_documento' => 'concepto_adicional',
            ]);

            return back()->with('success', 'Archivo subido correctamente al concepto.');
        } catch (\Throwable $e) {
            Log::error('Error en storeConceptoAdicional', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al subir archivo: ' . $e->getMessage());
        }
    }
}
