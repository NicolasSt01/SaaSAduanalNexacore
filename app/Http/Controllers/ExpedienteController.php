<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Documento;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Response;
use ZipArchive;
use App\Models\Aduana;
use App\Models\Cliente;
use App\Models\Patente;
use App\Models\User;
use App\Services\DocumentoStorageService;
use Illuminate\Support\Facades\Log;

class ExpedienteController extends Controller
{
    protected DocumentoStorageService $storageService;

    public function __construct(DocumentoStorageService $storageService)
    {
        $this->middleware('auth');
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        /**
         * ----------------------------------------------------------
         * 1️⃣ INICIALIZAR CONSULTA
         * ----------------------------------------------------------
         * Se crea la consulta base del modelo Expediente
         * con sus relaciones para evitar N+1 queries.
         */
        $query = Expediente::with([
            'cliente.documentosMaestros',
            'aduana',
            'patente',
            'documentador',
            'registradoPor',
            'cerradoPor'
        ]);

        /**
         * ----------------------------------------------------------
         * 2️⃣ FILTRO: NÚMERO DE PEDIMENTO (INDEPENDIENTE DE FECHAS)
         * ----------------------------------------------------------
         * Permite buscar por coincidencia parcial.
         * Este filtro NO depende de fechas.
         */
        if ($request->filled('numero_pedimento')) {
            $query->where('numero_pedimento', 'like', '%' . $request->numero_pedimento . '%');
        }

        /**
         * ----------------------------------------------------------
         * 3️⃣ FILTRO: ESTADO
         * ----------------------------------------------------------
         * Puede ser: Abierto, Cerrado, En proceso, Cancelado.
         * Si viene vacío o es "Todos", no se aplica ningún filtro.
         */
        if ($request->filled('estado') && $request->estado !== 'Todos') {
            $query->where('estado', $request->estado);
        }

        /**
         * ----------------------------------------------------------
         * 4️⃣ FILTRO: CATEGORÍA
         * ----------------------------------------------------------
         * Importación / Exportación
         */
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        /**
         * ----------------------------------------------------------
         * 5️⃣ FILTRO: CLIENTE
         * ----------------------------------------------------------
         * Muestra solo expedientes del cliente seleccionado.
         */
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        /**
         * ----------------------------------------------------------
         * 6️⃣ FILTRO: ADUANA (OPCIONAL)
         * ----------------------------------------------------------
         */
        if ($request->filled('aduana_id')) {
            $query->where('aduana_id', $request->aduana_id);
        }

        /**
         * ----------------------------------------------------------
         * 7️⃣ FILTRO: RANGO DE FECHAS (fecha_apertura)
         * ----------------------------------------------------------
         * Aplica solo sobre la columna `fecha_apertura`.
         * Puede usarse con:
         *  - Solo fecha_desde
         *  - Solo fecha_hasta
         *  - Ambas
         */
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        /**
         * ----------------------------------------------------------
         * 8️⃣ FILTRO: CUMPLIMIENTO DIGITAL
         * ----------------------------------------------------------
         */
        if ($request->filled('cumplimiento') && $request->cumplimiento === 'incompleto') {
            $incompletosIds = Expediente::whereIn('estado', ['En proceso', 'Abierto'])
                ->get()
                ->filter(fn($e) => !$e->cumplimiento_completo)
                ->pluck('id');
            $query->whereIn('id', $incompletosIds);
        }

        /**
         * ----------------------------------------------------------
         * 8️⃣ ORDEN Y PAGINACIÓN
         * ----------------------------------------------------------
         * Se ordena por el más reciente y se pagina a 12 resultados.
         * withQueryString() mantiene los filtros al cambiar de página.
         */
        $expedientes = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        /**
         * ----------------------------------------------------------
         * 9️⃣ CATÁLOGOS PARA FILTROS (SELECTS)
         * ----------------------------------------------------------
         */
        $clientes = Cliente::orderBy('nombre')->get();
        $aduanas = Aduana::orderBy('nombre')->get();
        $patentes = Patente::orderBy('numero')->get();

        /**
         * ----------------------------------------------------------
         * 🔟 RETORNO A LA VISTA
         * ----------------------------------------------------------
         */
        return view('expedientes.index', compact('expedientes', 'clientes', 'aduanas', 'patentes'));
    }

    public function create()
    {
        // TODO: Implementar lógica para obtener clientes, patentes y aduanas disponibles
        //return view('expedientes.create');
        // Obtener datos para los select
        $clientes = Cliente::orderBy('nombre')->get();
        $patentes = Patente::orderBy('numero')->get();
        $aduanas = Aduana::orderBy('nombre')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('expedientes.create', compact('clientes', 'patentes', 'aduanas', 'documentadores'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:cliente,id',
                'patente_id' => 'required|exists:patentes,id',
                'aduana_id' => 'required|exists:aduanas,id',
                //'numero_pedimento' => 'required|unique:expedientes',
                'numero_pedimento' => [
                    'required',
                    Rule::unique('expedientes')->where(function ($query) use ($request) {
                return $query->where('patente_id', $request->patente_id);
            })
                ],
                'tipo_expediente' => 'required|in:Unico,Consolidado',
                'categoria' => 'required|in:Importacion,Exportacion,Rectificaciones',
                'observaciones' => 'nullable|string',
                'fecha_pago_pedimento' => 'nullable|date',
                'fecha_apertura' => 'required|date',
                'fecha_cierre' => 'nullable|date',
                'clave_pedimento' => 'required|in:H1,A1,RT',
            ]);

            // Definir valores por defecto
            $validated['usuario_registro_id'] = auth()->id();
            $validated['registrado_por'] = auth()->id();
            $validated['estado'] = 'En proceso';

            // Reglas según tipo de expediente
            if ($validated['tipo_expediente'] === 'Unico') {
                $validated['fecha_apertura'] = null;
                $validated['fecha_cierre'] = null;
                if (empty($validated['fecha_pago_pedimento'])) {
                    $validated['fecha_pago_pedimento'] = now()->format('Y-m-d');
                }
            }
            else { //Es consolidado
                $validated['fecha_pago_pedimento'] = null;
                if (empty($validated['fecha_apertura'])) {
                    $validated['fecha_apertura'] = now()->format('Y-m-d');
                }
            }

            $expediente = Expediente::create($validated);

            $this->processDocuments($request, $expediente);

            return redirect()->route('expedientes.index', $expediente)->with('success', 'Expediente creado con éxito.');
        }
        catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function show(Expediente $expediente)
    {
        $expediente->load([
            'documentos', 
            'cliente.documentosMaestros',
            'patente', 
            'aduana', 
            'documentador', 
            'registradoPor', 
            'cerradoPor',
            'operaciones.documentos'
        ]);
        
        return view('expedientes.show', compact('expediente'));
    }

    public function showclient(Expediente $expediente)
    {
        /*$expediente->load(['documentos', 'cliente', 'patente', 'aduana', 'documentador']);
         return view('expedientes.show', compact('expediente'));*/
        $expediente->load(['documentos', 'cliente', 'patente', 'aduana', 'documentador', 'registradoPor', 'cerradoPor']);
        return view('expedientes.showclient', compact('expediente'));
    }

    public function edit(Expediente $expediente)
    {
        // TODO: Implementar lógica para obtener clientes, patentes y aduanas disponibles
        //return view('expedientes.edit', compact('expediente'));
        // Obtener datos para los select
        $clientes = Cliente::orderBy('nombre')->get();
        $patentes = Patente::orderBy('numero')->get();
        $aduanas = Aduana::orderBy('nombre')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('expedientes.edit', compact('expediente', 'clientes', 'patentes', 'aduanas', 'documentadores'));
    }

    public function update(Request $request, Expediente $expediente)
    {

        /*
         $validated = $request->validate([
         'cliente_id' => 'required|exists:cliente,id',
         'patente_id' => 'required|exists:patentes,id',
         'aduana_id' => 'required|exists:aduanas,id',
         'numero_pedimento' => 'required|unique:expedientes,numero_pedimento,' . $expediente->id,
         'fecha_pago_pedimento' => 'required|date',
         'categoria' => 'required|in:Importacion,Exportacion,Rectificaciones',
         'observaciones' => 'nullable|string',
         'estado' => 'required|string',
         ]);
         //Actualizar Expediente
         $expediente->update($validated);
         //Procesar Nuevos Documentos
         $this->processDocuments($request, $expediente);
         return redirect()->route('expedientes.show', $expediente)->with('success', 'Expediente Actualizado con Exito');
         //return redirect()->route('expedientes.show', $expediente)->with('success', 'Expediente actualizado correctamente.');*/
        /*$validated = $request->validate([
         'cliente_id' => 'required|exists:cliente,id',
         'patente_id' => 'required|exists:patentes,id',
         'aduana_id' => 'required|exists:aduanas,id',
         'numero_pedimento' => 'required|unique:expedientes,numero_pedimento,' . $expediente->id,
         'tipo_expediente' => 'required|in:Unico,Consolidado',
         'categoria' => 'required|in:Importacion,Exportacion,Rectificaciones',
         'observaciones' => 'nullable|string',
         'estado' => 'required|string',
         'fecha_pago_pedimento' => 'nullable|date',
         'fecha_apertura' => 'nullable|date',
         'fecha_cierre' => 'nullable|date',
         ]);*/
        $validated = $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'patente_id' => 'required|exists:patentes,id',
            'aduana_id' => 'required|exists:aduanas,id',
            'tipo_expediente' => 'required|in:Unico,Consolidado',
            'observaciones' => 'nullable|string',
            'estado' => 'required|string',
            'fecha_apertura' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
        ]);
        //dd('paso la validacion');

        if ($validated['tipo_expediente'] === 'Unico') {
            $validated['fecha_apertura'] = null;
            $validated['fecha_cierre'] = null;
        }
        else {
            $validated['fecha_pago_pedimento'] = null;
        }

        // Si el expediente pasa a cerrado, registrar quién lo cerró
        if ($validated['estado'] === 'Cerrado' && $expediente->cerrado_por === null) {
            $validated['cerrado_por'] = auth()->id();
            if (empty($validated['fecha_cierre'])) {
                $validated['fecha_cierre'] = now()->format('Y-m-d');
            }
        }
        //dd('vamos a salir de update');


        $expediente->update($validated);

        //$this->processDocuments($request, $expediente);

        return redirect()->route('expedientes.show', $expediente)->with('success', 'Expediente actualizado con éxito.');
    }

    public function destroy(Expediente $expediente)
    {
        $expediente->delete();
        return redirect()->route('expedientes.index')->with('success', 'Expediente eliminado correctamente.');
    }
    protected function processDocuments(Request $request, Expediente $expediente)
    {
        if ($request->has('documentos')) {
            $tenantId = auth()->user()->tenant_id;
            foreach ($request->documentos as $docData) {
                if (isset($docData['archivo']) && $docData['archivo']->isValid()) {
                    $meta = $this->storageService->upload(
                        $docData['archivo'],
                        $tenantId,
                        null,
                        $docData['tipo'] ?? null,
                        $docData['nombre'] ?? null
                    );

                    Documento::create([
                        'tenant_id' => $tenantId,
                        'pedimento_id' => $expediente->id,
                        'nombre' => $docData['nombre'],
                        'ruta' => $meta['path'],
                        'url_archivo' => $meta['url'],
                        'peso' => $meta['peso'],
                        'extension' => $meta['extension'],
                        'tipo_documento' => $docData['tipo'] ?? null,
                    ]);
                }
            }
        }
    }

    public function downloadAllDocuments(Expediente $expediente)
    {
        try {
            if ($expediente->documentos->isEmpty()) {
                return back()->with('error', 'El expediente no contiene documentos.');
            }

            $zipFileName = 'expediente_' . $expediente->numero_pedimento . '.zip';

            // Crear stream de respuesta
            $headers = [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            ];

            return response()->stream(function () use ($expediente) {
                $zip = new ZipArchive;
                $tempFile = tempnam(sys_get_temp_dir(), 'zip');

                if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    foreach ($expediente->documentos as $documento) {
                        $disk = $documento->en_r2 ? 'r2' : 'local';
                        if (Storage::disk($disk)->exists($documento->ruta)) {
                            $fileContent = Storage::disk($disk)->get($documento->ruta);
                            $originalName = pathinfo($documento->ruta, PATHINFO_BASENAME);
                            $zip->addFromString($originalName, $fileContent);
                        }
                    }
                    $zip->close();

                    echo file_get_contents($tempFile);
                    unlink($tempFile);
                }
            }, 200, $headers);

        }
        catch (Exception $e) {
            \Log::error('Error al crear ZIP: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar la descarga.');
        }
    }

    /**
     * 📌 Método actualizado para cerrar firma con opción de adjuntar pedimento pagado (R2)
     */
    public function cerrarFirma(Request $request, Expediente $expediente)
    {
        try {
            if (!in_array(auth()->user()->role, ['admin', 'Documentador'])) {
                abort(403, 'No tienes permisos para cerrar este expediente.');
            }

            if ($expediente->estado === 'Cerrado') {
                return back()->with('error', 'Este expediente ya está cerrado.');
            }

            $validated = $request->validate([
                'estado' => 'required|in:En proceso,Abierto,Cerrado,Cancelado',
                'fecha_pago_pedimento' => 'nullable|date',
                'fecha_cierre' => 'nullable|date',
                'observaciones' => 'nullable|string|max:1000',
                'observaciones_cierre' => 'nullable|string|max:1000',
                'pedimento_pagado' => 'nullable|file|mimes:pdf,xml,xls,xlsx,doc,docx,jpg,png,jpeg|max:10240',
            ], [
                'estado.required' => 'El estado es obligatorio',
                'estado.in' => 'El estado seleccionado no es válido',
                'fecha_pago_pedimento.date' => 'La fecha de pago debe ser una fecha válida',
                'fecha_cierre.date' => 'La fecha de cierre debe ser una fecha válida',
                'pedimento_pagado.file' => 'El archivo debe ser un documento válido',
                'pedimento_pagado.mimes' => 'Formatos permitidos: PDF, XML, Excel, Word, Imagen',
                'pedimento_pagado.max' => 'El archivo no debe superar los 10MB',
            ]);

            $expediente->estado = $request->estado;
            $expediente->fecha_pago_pedimento = $request->fecha_pago_pedimento;
            $expediente->cerrado_por = auth()->id();
            $expediente->fecha_cierre = $request->fecha_cierre;

            if ($request->filled('observaciones_cierre')) {
                $expediente->observaciones = $request->observaciones_cierre;
            } elseif ($request->filled('observaciones')) {
                $expediente->observaciones = $request->observaciones;
            }

            $expediente->save();

            $archivoCargado = false;

            if ($request->hasFile('pedimento_pagado')) {
                $file = $request->file('pedimento_pagado');
                $tenantId = auth()->user()->tenant_id;
                $nombreArchivo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $meta = $this->storageService->upload(
                    $file,
                    $tenantId,
                    null,
                    'pedimento_pagado',
                    $nombreArchivo
                );

                Documento::create([
                    'tenant_id' => $tenantId,
                    'pedimento_id' => $expediente->id,
                    'nombre' => $nombreArchivo,
                    'ruta' => $meta['path'],
                    'url_archivo' => $meta['url'],
                    'peso' => $meta['peso'],
                    'extension' => $meta['extension'],
                    'tipo_documento' => 'pedimento_pagado',
                ]);

                $archivoCargado = true;
            }

            $mensaje = 'El expediente se cerró y firmó correctamente.';
            if ($archivoCargado) {
                $mensaje .= ' El documento "Pedimento Pagado" fue adjuntado exitosamente a R2.';
            }

            return redirect()
                ->route('expedientes.show', $expediente)
                ->with('success', $mensaje);
        } catch (\Throwable $e) {
            Log::error('Error al cerrar firma del expediente', [
                'pedimento_id' => $expediente->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al cerrar la firma: ' . $e->getMessage());
        }
    }

    public function updateChecklist(Request $request, Expediente $expediente)
    {
        try {
            $request->validate([
                'checklist' => 'nullable|array',
            ]);

            $expediente->update([
                'checklist_cumplimiento' => $request->checklist ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checklist actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /*private function calcularAlerta(Expediente $expediente)
     {
     // Solo aplica para expedientes consolidados abiertos
     if ($expediente->tipo_expediente !== 'Consolidado' || $expediente->estado !== 'Abierto') {
     return null;
     }
     if (!$expediente->fecha_apertura) {
     return 'sin_fecha';
     }
     $fechaApertura = \Carbon\Carbon::createFromFormat('Y-m-d', $expediente->fecha_apertura);
     $diasTranscurridos = $fechaApertura->diffInDays(now());
     if ($diasTranscurridos >= 6) {
     return 'urgente'; // 1 día o menos para cerrar
     } elseif ($diasTranscurridos >= 5) {
     return 'advertencia'; // 2 días para cerrar
     } elseif ($diasTranscurridos >= 4) {
     return 'info'; // 3 días para cerrar
     }
     return null; // Menos de 4 días, sin alerta
     }*/

    /**
     * Calcula los días restantes para cierre (solo para consolidados abiertos)
     */
    /*private function calcularDiasRestantes(Expediente $expediente)
     {
     if (
     $expediente->tipo_expediente === 'Consolidado' &&
     $expediente->estado === 'Abierto' &&
     $expediente->fecha_apertura
     ) {
     $fechaApertura = \Carbon\Carbon::createFromFormat('Y-m-d', $expediente->fecha_apertura);
     $diasTranscurridos = $fechaApertura->diffInDays(now());
     return max(0, 7 - $diasTranscurridos); // No negativo
     }
     return null;
     }*/


    public function indexCliente()
    {
        // Verificar que el usuario sea un cliente
        if (auth()->user()->rol == 'ClienteAdmin') {
            abort(403, 'Acceso no autorizado');
        }
        $clienteid = auth()->user()->cliente_id;
        //dd($clienteid);

        // Obtener el cliente asociado al usuario
        $cliente = Cliente::where('id', $clienteid)->first();

        if (!$cliente) {
            abort(404, 'Cliente no encontrado');
        }

        // Obtener expedientes del cliente con sus relaciones CORREGIDO
        $expedientes = Expediente::where('cliente_id', $cliente->id)
            ->with(['documentos', 'aduana']) // 
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('expedientes.indexcliente', compact('expedientes', 'cliente'));
    }




}