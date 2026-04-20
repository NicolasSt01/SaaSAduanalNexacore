<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Operacion;
use App\Services\NotificacionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    protected $notificacionService; // 🔔 AGREGAR
    // 🔔 AGREGAR CONSTRUCTOR
    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }
    public function store(Request $request, Expediente $expediente)
    {
        try {
            $request->validate([
                'nombre_documento' => 'required|string|max:255',
                'archivo' => 'required|file|max:51200', // 20MB máximo, solo PDF
                'tipo_documento' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'nullable|exists:operaciones,id', // 📌 Nuevo
            ]);

            $file = $request->file('archivo');
            $path = $file->store('documentos');

            Documento::create([
                'pedimento_id' => $expediente->id,
                'operacion_id' => $request->operacion_id, // 📌 Nuevo
                'nombre' => $request->nombre_documento,
                'ruta' => $path,
                'tipo_documento' => $request->tipo_documento,
            ]);

            return back()->with('success', 'Documento subido correctamente.');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

    }

    public function store2(Request $request)
    {

        try {
            $request->validate([
                'archivos.*' => 'required|file|max:51200', // 50MB máximo por archivo
                'tipo_documento' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'nullable|exists:operaciones,id',
            ]);

            $uploadedCount = 0;
            $errors = [];
            //dd($request);
            // Verificar si hay archivos
            if ($request->hasFile('archivos')) {

                foreach ($request->file('archivos') as $index => $file) {
                    try {
                        // Guardar el archivo
                        $path = $file->store('documentos');

                        // Obtener el nombre original sin extensión
                        $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                        // Crear el documento

                        Documento::create([
                            'pedimento_id' => $request->id,
                            'operacion_id' => $request->operacion_id,
                            'nombre' => $nombreOriginal,
                            'ruta' => $path,
                            'tipo_documento' => $request->tipo_documento ?? 'otros',
                        ]);


                        $uploadedCount++;

                    } catch (Exception $e) {
                        // Registrar el error pero continuar con los demás archivos

                        $errors[] = "Error al subir '{$file->getClientOriginalName()}': " . $e->getMessage();
                    }
                }
                // 🔔 AGREGAR NOTIFICACIÓN DESPUÉS DE SUBIR TODOS LOS ARCHIVOS
                
                if ($uploadedCount > 0 && $request->operacion_id) {
                    $operacion = Operacion::find($request->operacion_id);
                    if ($operacion) {
                        $this->notificacionService->notificarDocumentosSubidos($operacion, $uploadedCount);
                    }
                }
            }

            // Preparar mensaje de respuesta
            if ($uploadedCount > 0) {
                $message = $uploadedCount === 1
                    ? 'Documento subido correctamente.'
                    : "$uploadedCount documentos subidos correctamente.";

                if (!empty($errors)) {
                    $message .= ' Sin embargo, algunos archivos fallaron: ' . implode(', ', $errors);
                }

                return back()->with('success', $message);
            } else {
                return back()->with('error', 'No se pudo subir ningún documento. ' . implode(', ', $errors));
            }

        } catch (Exception $e) {

            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error de validación: ' . implode(', ', $e->validator->errors()->all()));
        } catch (Exception $e) {

            return back()->with('error', 'Error al subir documentos: ' . $e->getMessage());
        }
    }

    public function store3(Request $request)
    {
        try {
            $request->validate([
                'archivos' => 'required|array',
                'archivos.*' => 'file|max:51200', // 50MB máximo por archivo
                'tipos_documento' => 'nullable|array',
                'tipos_documento.*' => 'nullable|string|max:255',
                'fecha_documento' => 'nullable|date',
                'observaciones' => 'nullable|string',
                'operacion_id' => 'required|exists:operaciones,id',
            ]);

            $archivos = $request->file('archivos');
            $tiposDocumento = $request->input('tipos_documento', []);
            $cantidadSubidos = 0;

            foreach ($archivos as $index => $file) {
                $path = $file->store('documentos');

                // Obtener nombre sin extensión
                $nombreArchivo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                // Tipo de documento individual o por defecto
                $tipoDocumento = $tiposDocumento[$index] ?? 'otros';

                Documento::create([
                    'operacion_id' => $request->operacion_id,
                    'nombre' => $nombreArchivo,
                    'ruta' => $path,
                    'tipo_documento' => $tipoDocumento,
                ]);

                $cantidadSubidos++;
            }

            return response()->json([
                'success' => true,
                'message' => "$cantidadSubidos documento(s) subido(s) correctamente."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }//Funcional para muchos archivos en trafico

    public function destroy(Documento $documento)
    {
        Storage::delete($documento->ruta);
        $documento->delete();
        return back()->with('success', 'Documento eliminado correctamente.');
    }

    public function download(Documento $documento)
    {
        $originalName = pathinfo($documento->ruta, PATHINFO_FILENAME);
    $extension = pathinfo($documento->ruta, PATHINFO_EXTENSION);

    return Storage::download($documento->ruta, $originalName . '.' . $extension);
        //return Storage::download($documento->ruta, $documento->nombre . '.pdf');
    }
public function preview(Documento $documento)
{
    // tu archivo está en $documento->ruta (como en download)
    $ruta = $documento->ruta;

    // primero revisa que exista
    if (!Storage::exists($ruta)) {
        abort(404, 'Archivo no encontrado');
    }

    // devuelve el archivo para que el navegador lo abra (no para descargar)
    return response()->file(Storage::path($ruta));
}

    /**
 * Subir archivo para un concepto adicional
 */
public function storeConceptoAdicional(Request $request)
{
    try {
        $request->validate([
            'concepto_adicional_id' => 'required|exists:conceptos_adicionales,id',
            'archivo' => 'required|file|max:51200', // 50MB máximo
            'nombre_documento' => 'nullable|string|max:255',
        ]);

        $file = $request->file('archivo');
        $path = $file->store('documentos/conceptos');

        // Si no viene nombre, usar el nombre original del archivo
        $nombreDocumento = $request->nombre_documento 
            ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        Documento::create([
            'concepto_adicional_id' => $request->concepto_adicional_id,
            'nombre' => $nombreDocumento,
            'ruta' => $path,
            'tipo_documento' => 'concepto_adicional',
        ]);

        return back()->with('success', 'Archivo subido correctamente al concepto.');
        
    } catch (Exception $e) {
        return back()->with('error', 'Error al subir archivo: ' . $e->getMessage());
    }
}

}