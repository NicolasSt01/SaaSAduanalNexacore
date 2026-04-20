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

class ExpedienteController extends Controller
{
    public function index_OLD(Request $request)
    {


        // Inicia la consulta del modelo Expediente con sus relaciones
        $query = Expediente::with(['cliente', 'aduana', 'patente', 'documentador', 'registradoPor', 'cerradoPor']);

        // Aplica los filtros de búsqueda según los parámetros de la petición
        $query->when($request->filled('numero_pedimento'), function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', '%' . $request->numero_pedimento . '%');
        });

        $query->when($request->filled('estado'), function ($q) use ($request) {
            $q->where('estado', $request->estado);
        });

        $query->when($request->filled('categoria'), function ($q) use ($request) {
            $q->where('categoria', 'like', '%' . $request->categoria . '%');
        });

        $query->when($request->filled('cliente_id'), function ($q) use ($request) {
            $q->where('cliente_id', $request->cliente_id);
        });

        $query->when($request->filled('aduana_id'), function ($q) use ($request) {
            $q->where('aduana_id', $request->aduana_id);
        });

        // Filtro por rango de fechas
        /*if ($request->filled('fecha_desde')) {
         $query->where(function ($q) use ($request) {
         $q->whereDate('fecha_apertura', '>=', $request->fecha_desde)
         ->orWhereDate('fecha_pago_pedimento', '>=', $request->fecha_desde);
         });
         }
         if ($request->filled('fecha_hasta')) {
         $query->where(function ($q) use ($request) {
         $q->whereDate('fecha_apertura', '<=', $request->fecha_hasta)
         ->orWhereDate('fecha_pago_pedimento', '<=', $request->fecha_hasta);
         });
         }*/
        // ✅ FILTRO POR FECHA DE APERTURA
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }
        // FILTRO ADICIONAL: Solo expedientes en proceso o abiertos (si no se especifica otro filtro de estado)
        if (!$request->filled('estado')) {
            $query->whereIn('estado', ['En proceso', 'Abierto']);
        }


        // Obtiene los expedientes filtrados
        //$expedientes = $query->latest()->get();
        // Obtiene los expedientes filtrados con paginación de 12 resultados por página
        $expedientes = $query->latest()->paginate(12);

        // A cada expediente le calculamos alerta y días restantes
        /*foreach ($expedientes as $expediente) {
         $expediente->alerta = $this->calcularAlerta($expediente);
         $expediente->dias_restantes = $this->calcularDiasRestantes($expediente);
         }*/

        // Obtiene las listas de clientes y aduanas para los menús desplegables del filtro
        //$clientes = Cliente::all();
        $clientes = Cliente::orderBy('nombre_empresa')->get();
        $aduanas = Aduana::all();




        /*$expedientes = Expediente::with(['cliente', 'patente', 'aduana', 'documentador', 'registradoPor', 'cerradoPor'])
         ->orderBy('created_at', 'desc')
         ->get();*/

        return view('expedientes.index', compact('expedientes', 'clientes', 'aduanas'));
    }

    public function index_Old2(Request $request)
    {
        // Inicia la consulta del modelo Expediente con sus relaciones
        $query = Expediente::with(['cliente', 'aduana', 'patente', 'documentador', 'registradoPor', 'cerradoPor']);

        // Aplica los filtros de búsqueda según los parámetros de la petición
        $query->when($request->filled('numero_pedimento'), function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', '%' . $request->numero_pedimento . '%');
        });

        $query->when($request->filled('estado'), function ($q) use ($request) {
            $q->where('estado', $request->estado);
        });

        $query->when($request->filled('categoria'), function ($q) use ($request) {
            $q->where('categoria', 'like', '%' . $request->categoria . '%');
        });

        $query->when($request->filled('cliente_id'), function ($q) use ($request) {
            $q->where('cliente_id', $request->cliente_id);
        });

        $query->when($request->filled('aduana_id'), function ($q) use ($request) {
            $q->where('aduana_id', $request->aduana_id);
        });

        // ✅ FILTRO CORREGIDO: Rango de fechas
        // Solo aplica si al menos una fecha está presente
        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            $query->where(function ($q) use ($request) {
                // Subquery para fecha_apertura
                $q->where(function ($subQ) use ($request) {
                        if ($request->filled('fecha_desde')) {
                            $subQ->whereDate('fecha_apertura', '>=', $request->fecha_desde);
                        }
                        if ($request->filled('fecha_hasta')) {
                            $subQ->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
                        }
                    }
                    )
                        // OR Subquery para fecha_pago_pedimento
                        ->orWhere(function ($subQ) use ($request) {
                    if ($request->filled('fecha_desde')) {
                        $subQ->whereDate('fecha_pago_pedimento', '>=', $request->fecha_desde);
                    }
                    if ($request->filled('fecha_hasta')) {
                        $subQ->whereDate('fecha_pago_pedimento', '<=', $request->fecha_hasta);
                    }
                }
                );
            });
        }

        // FILTRO ADICIONAL: Solo expedientes en proceso o abiertos (si no se especifica otro filtro de estado)
        if (!$request->filled('estado')) {
            $query->whereIn('estado', ['En proceso', 'Abierto']);
        }

        // Obtiene los expedientes filtrados con paginación de 12 resultados por página
        $expedientes = $query->latest()->paginate(12)->withQueryString(); // ← Añadido withQueryString()

        // Obtiene las listas de clientes y aduanas para los menús desplegables del filtro
        $clientes = Cliente::orderBy('nombre_empresa')->get();
        $aduanas = Aduana::all();

        return view('expedientes.index', compact('expedientes', 'clientes', 'aduanas'));
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
            'cliente',
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
            'cliente', 
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
            foreach ($request->documentos as $docData) {
                if (isset($docData['archivo']) && $docData['archivo']->isValid()) {
                    $path = $docData['archivo']->store('documentos');

                    Documento::create([
                        'pedimento_id' => $expediente->id,
                        'nombre' => $docData['nombre'],
                        'ruta' => $path,
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
                // Crear archivo ZIP en memoria
                $zip = new ZipArchive;
                $tempFile = tempnam(sys_get_temp_dir(), 'zip');

                if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    foreach ($expediente->documentos as $documento) {
                        if (Storage::exists($documento->ruta_archivo)) {
                            $fileContent = Storage::get($documento->ruta_archivo);
                            $originalName = pathinfo($documento->ruta_archivo, PATHINFO_BASENAME);
                            $zip->addFromString($originalName, $fileContent);
                        }
                    }
                    $zip->close();

                    // Enviar contenido del ZIP
                    echo file_get_contents($tempFile);
                    unlink($tempFile); // Limpiar
                }
            }, 200, $headers);

        }
        catch (Exception $e) {
            \Log::error('Error al crear ZIP: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar la descarga.');
        }
    }

    public function cerrarFirma_OLD(Request $request, Expediente $expediente)
    {
        try {
            //dd(auth()->id(),auth()->user()->role);
            // Solo usuarios con permisos adecuados
            if (!in_array(auth()->user()->role, ['admin', 'Documentador'])) {
                abort(403, 'No tienes permisos para cerrar este expediente.');
            }

            // Validar que no esté ya cerrado
            if ($expediente->estado === 'Cerrado') {
                return back()->with('error', 'Este expediente ya está cerrado.');
            }

            // Registrar datos de cierre
            //$expediente->estado = 'Cerrado';
            $expediente->estado = $request->estado;
            $expediente->fecha_pago_pedimento = $request->fecha_pago_pedimento;
            $expediente->cerrado_por = auth()->id();
            $expediente->fecha_cierre = $request->fecha_cierre;
            $expediente->observaciones = $request->observaciones;

            // Si quieres registrar que fue “firmado” con alguna observación:
            if ($request->filled('observaciones_cierre')) {
                $expediente->observaciones = $request->observaciones;
            }

            $expediente->save();

            return redirect()
                ->route('expedientes.show', $expediente)
                ->with('success', 'El expediente se cerró y firmó correctamente.');
        }
        catch (Exception $e) {
        //dd($e->getMessage());
        }

    }

    /**
     * 📌 Método actualizado para cerrar firma con opción de adjuntar pedimento pagado
     */
    public function cerrarFirma(Request $request, Expediente $expediente)
    {
        try {
            // Solo usuarios con permisos adecuados
            if (!in_array(auth()->user()->role, ['admin', 'Documentador'])) {
                abort(403, 'No tienes permisos para cerrar este expediente.');
            }

            // Validar que no esté ya cerrado
            if ($expediente->estado === 'Cerrado') {
                return back()->with('error', 'Este expediente ya está cerrado.');
            }

            // 📌 VALIDACIÓN DE DATOS (incluyendo el nuevo campo pedimento_pagado)
            $validated = $request->validate([
                'estado' => 'required|in:En proceso,Abierto,Cerrado,Cancelado',
                'fecha_pago_pedimento' => 'nullable|date',
                'fecha_cierre' => 'nullable|date',
                'observaciones' => 'nullable|string|max:1000',
                'observaciones_cierre' => 'nullable|string|max:1000',
                'pedimento_pagado' => 'nullable|file|mimes:pdf,xml,xls,xlsx,doc,docx,jpg,png,jpeg|max:10240', // 10MB máximo
            ], [
                'estado.required' => 'El estado es obligatorio',
                'estado.in' => 'El estado seleccionado no es válido',
                'fecha_pago_pedimento.date' => 'La fecha de pago debe ser una fecha válida',
                'fecha_cierre.date' => 'La fecha de cierre debe ser una fecha válida',
                'pedimento_pagado.file' => 'El archivo debe ser un documento válido',
                'pedimento_pagado.mimes' => 'Formatos permitidos: PDF, XML, Excel, Word, Imagen',
                'pedimento_pagado.max' => 'El archivo no debe superar los 10MB',
            ]);

            // Registrar datos de cierre
            $expediente->estado = $request->estado;
            $expediente->fecha_pago_pedimento = $request->fecha_pago_pedimento;
            $expediente->cerrado_por = auth()->id();
            $expediente->fecha_cierre = $request->fecha_cierre;

            // Si quieres registrar que fue "firmado" con alguna observación
            if ($request->filled('observaciones_cierre')) {
                $expediente->observaciones = $request->observaciones_cierre;
            }
            elseif ($request->filled('observaciones')) {
                $expediente->observaciones = $request->observaciones;
            }

            $expediente->save();

            // 📌 MANEJAR EL ARCHIVO DE PEDIMENTO PAGADO (usando tu patrón de store3)
            $archivoCargado = false;

            if ($request->hasFile('pedimento_pagado')) {
                $file = $request->file('pedimento_pagado');

                // Guardar usando el método store (igual que tu store3)
                $path = $file->store('documentos');

                // Obtener nombre sin extensión (igual que tu store3)
                $nombreArchivo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                // Crear el registro del documento en la base de datos
                Documento::create([
                    'pedimento_id' => $expediente->id,
                    'operacion_id' => null,
                    'factura_id' => null,
                    'concepto_adicional_id' => null,
                    'nombre' => $nombreArchivo,
                    'ruta' => $path,
                    'tipo_documento' => 'Pedimento Pagado',
                ]);

                $archivoCargado = true;
            }

            // Mensaje de éxito personalizado
            $mensaje = 'El expediente se cerró y firmó correctamente.';
            if ($archivoCargado) {
                $mensaje .= ' El documento "Pedimento Pagado" fue adjuntado exitosamente.';
            }

            return redirect()
                ->route('expedientes.show', $expediente)
                ->with('success', $mensaje);

        }
        catch (Exception $e) {
            // Log del error para debugging
            \Log::error('Error al cerrar firma del expediente: ' . $e->getMessage(), [
                'pedimento_id' => $expediente->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
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