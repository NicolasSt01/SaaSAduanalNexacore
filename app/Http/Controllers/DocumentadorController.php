<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Operacion;
use App\Models\User;
use App\Services\DocumentoStorageService;
use App\Services\NotificacionService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentadorController extends Controller
{
    protected NotificacionService $notificacionService;
    protected DocumentoStorageService $storageService;

    public function __construct(NotificacionService $notificacionService, DocumentoStorageService $storageService)
    {
        $this->middleware("auth");
        $this->notificacionService = $notificacionService;
        $this->storageService = $storageService;
    }

    public function dashboardDocumentador()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // Obtener configuración del SOIA-Bot para el tenant actual
        $botMode = $tenant ? $tenant->getBotMode() : 'deshabilitado';
        $botEnabled = $tenant ? $tenant->isBotEnabled() : false;
        $botAutomatic = $tenant ? $tenant->isBotAutomatic() : false;

        return view('documentador.dashboard', compact('botMode', 'botEnabled', 'botAutomatic'));
    }
    public function trabajarOperacion($id)
    {
        $operacion = Operacion::findOrFail($id);

        // Verificar que pertenece al usuario logueado
        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para trabajar en esta exportación');
        }

        return view('documentador.trabajar', compact('operacion'));
        //return view('operaciones.dashboard', compact('operacion'));

    }

    public function updateEstado(Request $request, $id)
    {
        $operacion = Operacion::findOrFail($id);

        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para actualizar esta exportación');
        }

        $request->validate([
            'estado' => 'required|in:pendiente,proceso,completado,cancelada',
            'comentarios' => 'nullable|string'
        ]);

        $operacion->estado = $request->estado;

        if ($request->comentarios) {
            // Aquí se puede guardar los comentarios 
        }

        $operacion->save();

        return redirect()->route('documentador.dashboard')
            ->with('success', 'Estado actualizado correctamente');
    }

    public function cancelarOperacion(Request $request, $id)
    {
        try {
            $operacion = Operacion::findOrFail($id);

            if ($operacion->estado === 'cancelada') {
                return response()->json(['success' => false, 'message' => 'La operación ya está cancelada.'], 400);
            }

            if (!in_array(auth()->user()->role, ['admin', 'super_admin', 'Documentador'])) {
                return response()->json(['success' => false, 'message' => 'No tienes permisos para cancelar operaciones.'], 403);
            }

            $validated = $request->validate([
                'motivo_cancelacion' => 'required|string|max:1000',
                'eliminar_documentos' => 'nullable|boolean',
            ]);

            $operacion->estado = 'cancelada';
            $operacion->motivo_cancelacion = $validated['motivo_cancelacion'];
            $operacion->fecha_cancelacion = now();
            $operacion->usuario_cancelacion_id = auth()->id();
            $operacion->save();

            if (!empty($validated['eliminar_documentos'])) {
                $documentos = Documento::where('operacion_id', $operacion->id)->get();
                foreach ($documentos as $doc) {
                    try {
                        if ($doc->en_r2) {
                            $this->storageService->delete($doc->ruta);
                        } elseif (\Illuminate\Support\Facades\Storage::disk('local')->exists($doc->ruta)) {
                            \Illuminate\Support\Facades\Storage::disk('local')->delete($doc->ruta);
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Error al eliminar documento al cancelar operación', [
                            'doc_id' => $doc->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                    $doc->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Operación cancelada correctamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al cancelar operación', [
                'operacion_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Agrega estos métodos al OperacionController

    public function tomarTramite_malo_tomabaaleatorios(Request $request)
    {
        $userId = auth()->id();

        // Buscar trámites disponibles (sin asignar o reasignables)
        $tramiteDisponible = Operacion::where(function ($query) {
            $query->whereNull('usuario_cierre_id')
                ->orWhere('usuario_cierre_id', 0);
        })
            ->whereIn('estado', ['pendiente', 'proceso'])
            ->when($request->urgente, function ($query) {
                $query->where('prioridad', 'urgente');
            })
            ->when($request->cliente_id, function ($query, $clienteId) {
                $query->where('cliente_id', $clienteId);
            })
            ->inRandomOrder()
            ->first();

        if (!$tramiteDisponible) {
            return redirect()->route('documentador.dashboard')
                ->with('info', 'No hay trámites disponibles en este momento');
        }

        // Asignar el trámite al usuario
        $tramiteDisponible->usuario_cierre_id = $userId;
        $tramiteDisponible->estado = 'proceso';
        $tramiteDisponible->save();

        return redirect()->route('documentador.trabajar', $tramiteDisponible->id)
            ->with('success', 'Trámite asignado correctamente');
    }

    public function tomarTramite(Request $request)
    {
        $userId = auth()->id();
        $operacionId = $request->input('operacion_id');

        // 1. Identificar la exportación a tomar
        if ($operacionId) {
            // Caso: El usuario presionó un botón "Tomar" específico
            $tramiteDisponible = Operacion::where(function ($query) {
                $query->whereNull('usuario_cierre_id')
                    ->orWhere('usuario_cierre_id', 0);
            })->find($operacionId);
        } else {
            // Caso: Sin ID (por si acaso), toma el más antiguo del día
            $tramiteDisponible = Operacion::where(function ($query) {
                $query->whereNull('usuario_cierre_id')
                    ->orWhere('usuario_cierre_id', 0);
            })
                ->whereIn('estado', ['pendiente', 'proceso'])
                ->orderBy('fecha_registro', 'asc')
                ->orderBy('id', 'asc')
                ->first();
        }

        if (!$tramiteDisponible) {
            return redirect()->route('documentador.dashboard')
                ->with('error', 'El trámite seleccionado ya no está disponible o no existe.');
        }

        // 2. Asignar el trámite principal al usuario
        $tramiteDisponible->usuario_cierre_id = $userId;

        // Si estaba pendiente, lo pasamos a proceso
        if ($tramiteDisponible->estado === 'pendiente') {
            $tramiteDisponible->estado = 'proceso';
        }

        $tramiteDisponible->save();

        // 3. 🚛 CONSOLIDADOS: Buscar y asignar automáticamente las operaciones del mismo camión
        $consolidadosAsignados = 0;
        if ($tramiteDisponible->num_thermo && $tramiteDisponible->codigo_alpha) {
            $consolidados = Operacion::where('id', '!=', $tramiteDisponible->id)
                ->where('num_thermo', $tramiteDisponible->num_thermo)
                ->where('codigo_alpha', $tramiteDisponible->codigo_alpha)
                ->where(function ($query) {
                    $query->whereNull('usuario_cierre_id')
                        ->orWhere('usuario_cierre_id', 0);
                })
                ->whereIn('estado', ['pendiente', 'proceso'])
                ->get();

            foreach ($consolidados as $consolidado) {
                $consolidado->usuario_cierre_id = $userId;
                $consolidado->estado = 'proceso';
                $consolidado->save();
                $consolidadosAsignados++;
            }
        }

        $mensaje = 'Trámite #' . ($tramiteDisponible->referencia ?? $tramiteDisponible->id) . ' asignado correctamente.';
        if ($consolidadosAsignados > 0) {
            $mensaje .= " Además se asignaron automáticamente {$consolidadosAsignados} operaciones consolidadas del mismo camión.";
        }

        return redirect()->route('documentador.trabajar', $tramiteDisponible->id)
            ->with('success', $mensaje);
    }

    public function trabajarOperacion2_old($id)
    {
        $operacion = Operacion::with(['cliente', 'importador', 'bodega', 'aduana', 'patente'])
            ->findOrFail($id);

        // Verificar que pertenece al usuario logueado
        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para trabajar en esta exportación');
        }

        // Cambiar estado a "proceso" si está pendiente
        if ($operacion->estado == 'pendiente') {
            $operacion->estado = 'proceso';
            $operacion->save();
        }

        //$expedientes = Expediente::where('estado', 'En proceso','Abierto')->get();
        // Obtener solo expedientes activos del cliente de la exportación
        $expedientes = Expediente::whereIn('estado', ['En proceso', 'Abierto'])
            ->where('cliente_id', $operacion->cliente_id)
            ->get();

        // Obtener documentos asociados a esta exportación
        $documentos = Documento::where('operacion_id', $id)->get();


        return view('documentador.trabajar', compact('operacion', 'expedientes', 'documentos'));
    }

    public function trabajarOperacion2($id)
    {
        $operacion = Operacion::with([
            'cliente',
            'importador',
            'aduana',
            'bodega',
            'patente',
            'expediente'
        ])->findOrFail($id);

        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para trabajar en esta exportación');
        }

        if ($operacion->estado == 'pendiente') {
            $operacion->estado = 'proceso';
            $operacion->save();
        }

        $expedientes = Expediente::with('patente')
            ->whereIn('estado', ['En proceso', 'Abierto'])
            ->where('cliente_id', $operacion->cliente_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $documentos = Documento::where('operacion_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('documentador.trabajar', compact('operacion', 'expedientes', 'documentos'));
    }

    /**
     * API: Retorna solo los documentos de una operación (para modal details).
     */
    public function getDocumentosOperacion($id)
    {
        $operacion = Operacion::findOrFail($id);

        if ($operacion->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $documentos = Documento::where('operacion_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'nombre' => $doc->nombre,
                    'tipo' => $doc->tipo_documento,
                    'preview_url' => route('documentos.preview', $doc->id),
                    'download_url' => route('documentos.download', $doc->id),
                ];
            });

        return response()->json(['documentos' => $documentos]);
    }

    public function completarOperacion_old(Request $request, $id)
    {
        $operacion = Operacion::findOrFail($id);

        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para completar esta exportación');
        }

        $request->validate([
            'pedimento_id' => 'required|exists:expedientes,id',
            'num_doda' => 'required|string|max:50',
            'comentarios' => 'nullable|string'
        ]);

        $operacion->expediente_id = $request->pedimento_id;
        $operacion->num_doda = $request->num_doda;
        $operacion->estado = 'terminado';
        //$operacion->fecha_registro_completado = now();

        if ($request->comentarios) {
            // Guardar comentarios si es necesario
        }

        $operacion->save();


        // Actualizar todos los documentos relacionados con esta exportación
        Documento::where('operacion_id', $operacion->id)
            ->update(['pedimento_id' => $request->pedimento_id]);



        return redirect()->route('documentador.dashboard')
            ->with('success', 'Exportación completada correctamente');
    }
    public function completarOperacion(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $operacion = Operacion::findOrFail($id);

            // Verificar permisos
            if ($operacion->usuario_cierre_id != auth()->id()) {
                abort(403, 'No tienes permisos para completar esta exportación');
            }

            // Validación
            $request->validate([
                'pedimento_id' => 'required|exists:expedientes,id',
                'num_doda' => 'required|string|max:50',
                'comentarios' => 'nullable|string'
            ]);

            // Obtener el expediente para extraer patente
            $expediente = Expediente::with('patente')->findOrFail($request->pedimento_id);

            // Validar cliente
            if ($expediente->cliente_id !== $operacion->cliente_id) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'El pedimento seleccionado no pertenece al cliente de esta operación.')
                    ->withInput();
            }

            // Actualizar datos
            $operacion->expediente_id = $request->pedimento_id;
            $operacion->num_doda = strtoupper(trim($request->num_doda));
            $operacion->estado = 'terminado';

            // 🔥 ACTUALIZAR AUTOMÁTICAMENTE LA PATENTE
            if ($expediente->patente_id) {
                $operacion->patente_id = $expediente->patente_id;
            }

            // Guardar comentarios si existen
            if ($request->comentarios) {
                $operacion->observaciones = $request->comentarios;
            }

            $operacion->save();

            // Actualizar documentos relacionados
            Documento::where('operacion_id', $operacion->id)
                ->update(['pedimento_id' => $request->pedimento_id]);

            DB::commit();

            return redirect()->route('documentador.dashboard')
                ->with('success', 'Exportación completada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al completar exportación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al completar: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function actualizardatosoperacion_old(Request $request, $id)
    {
        try {
            $operacion = Operacion::findOrFail($id);

            if ($operacion->usuario_cierre_id != auth()->id()) {
                abort(403, 'No tienes permisos para completar esta exportación');
            }

            $request->validate([
                'pedimento_id' => 'exists:expedientes,id',
                'num_doda' => 'nullable|string|max:50',
                'comentarios' => 'nullable|string'
            ]);

            $operacion->expediente_id = $request->pedimento_id;
            $operacion->num_doda = $request->num_doda;
            $operacion->estado = 'proceso';
            //$operacion->fecha_registro_completado = now();

            if ($request->comentarios) {
                // Guardar comentarios si es necesario
            }

            $operacion->save();

            // Actualizar todos los documentos relacionados con esta exportación
            Documento::where('operacion_id', $operacion->id)
                ->update(['pedimento_id' => $request->pedimento_id]);

            return redirect()->route('documentador.dashboard')
                ->with('success', 'Exportación completada correctamente');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }
    public function actualizardatosoperacion(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $operacion = Operacion::findOrFail($id);

            // Verificar permisos
            if ($operacion->usuario_cierre_id != auth()->id()) {
                abort(403, 'No tienes permisos para actualizar esta exportación');
            }

            // Validación
            $request->validate([
                'pedimento_id' => 'required|exists:expedientes,id',
                //'num_doda' => 'required|string|max:50',
                'comentarios' => 'nullable|string'
            ], [
                'pedimento_id.required' => 'Debe seleccionar un expediente/pedimento',
                'pedimento_id.exists' => 'El expediente seleccionado no es válido',
                //'num_doda.required' => 'El número DODA es obligatorio'
            ]);

            // 🔥 Obtener el expediente para extraer la patente
            $expediente = Expediente::with('patente')->findOrFail($request->pedimento_id);

            // Verificar que el expediente pertenece al mismo cliente
            if ($expediente->cliente_id !== $operacion->cliente_id) {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'El pedimento seleccionado no pertenece al cliente de esta operación.')
                    ->withInput();
            }

            // Actualizar datos básicos
            $operacion->expediente_id = $request->pedimento_id;
            $operacion->num_doda = strtoupper(trim($request->num_doda)); // Normalizar DODA
            $operacion->estado = 'proceso';

            // 🔥 ACTUALIZAR AUTOMÁTICAMENTE LA PATENTE desde el expediente
            $patenteMsg = '';
            if ($expediente->patente_id) {
                $operacion->patente_id = $expediente->patente_id;
                $patenteMsg = ' | Patente actualizada: ' . $expediente->patente->numero_patente;
            } else {
                \Log::warning("El expediente {$expediente->numero_pedimento} no tiene patente asignada");
            }

            // Guardar comentarios si existen
            if ($request->comentarios) {
                $operacion->observaciones = $request->comentarios;
            }

            $operacion->save();

            // Actualizar todos los documentos relacionados
            Documento::where('operacion_id', $operacion->id)
                ->update(['pedimento_id' => $request->pedimento_id]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Información actualizada correctamente' . $patenteMsg);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Error al actualizar exportación: ' . $e->getMessage(), [
                'operacion_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar la información: ' . $e->getMessage())
                ->withInput();
        }

    }

    public function actualizar(Request $request, $id)
    {
        /*$request->validate([
         'pedimento_id' => 'required|exists:expedientes,id',
         'num_doda' => 'required|string|max:255',
         ]);
         try {
         $operacion = Operacion::findOrFail($id);
         $operacion->update([
         'expediente_id' => $request->pedimento_id,
         'num_doda' => $request->num_doda,
         ]);
         return response()->json([
         'success' => true,
         'message' => 'Información actualizada correctamente'
         ]);
         } catch (\Exception $e) {
         return response()->json([
         'success' => false,
         'message' => 'Error al actualizar: ' . $e->getMessage()
         ], 500);
         }*/
        $operacion = Operacion::findOrFail($id);

        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para completar esta exportación');
        }

        $request->validate([
            'pedimento_id' => 'exists:expedientes,id',
            'num_doda' => 'string|max:50',
            'comentarios' => 'nullable|string'
        ]);

        $operacion->expediente_id = $request->pedimento_id;
        $operacion->num_doda = $request->num_doda;
        $operacion->estado = 'proceso';
        //$operacion->fecha_registro_completado = now();

        if ($request->comentarios) {
            // Guardar comentarios si es necesario
        }

        $operacion->save();

        return redirect()->route('documentador.dashboard')
            ->with('success', 'Exportación completada correctamente');
    }

    //Nuevo para documentador.
    public function index2()
    {
        $user = Auth::user();

        // --- 1. Obtener Operaciones Asignadas ---
        $operaciones = Operacion::where('usuario_cierre_id', $user->id)
            ->orderBy('prioridad', 'desc')
            ->get();

        // --- 2. Calcular Estadísticas del Día ---
        $totalHoy = $operaciones->where('created_at', '>=', now()->startOfDay())->count();
        $completadosHoy = $operaciones->where('created_at', '>=', now()->startOfDay())
            ->where('estado', 'completado')->count();
        $pendientes = $totalHoy - $completadosHoy;

        $efectividad = $totalHoy > 0 ? number_format(($completadosHoy / $totalHoy) * 100, 2) : 0;

$stats = [
            'total_hoy' => $totalHoy,
            'completados_hoy' => $completadosHoy,
            'pendientes' => $pendientes,
            'efectividad' => $efectividad,
            'ranking_posicion' => $posicionUser,
            'canceladas_hoy' => $canceladasHoy,
        ];

        // --- 3. Obtener el Ranking Semanal ---
        $rankingSemanal = User::where('role', 'documentador')
            ->withCount([
                'operaciones' => function ($query) {
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->where('estado', 'completado');
                }
            ])
            ->orderByDesc('operaciones_count')
            ->get()
            ->map(function ($rankItem, $index) use ($user) {
                return (object) [
                    'name' => $rankItem->name,
                    'total_tramites' => $rankItem->operaciones_count,
                    'is_current_user' => $rankItem->id === Auth::id()
                ];
            });
        return view('documentador.dashboard', compact('stats', 'operaciones', 'rankingSemanal'));
    }

    // Método para tomar un trámite específico
    public function tomarTramite2($id)
    {
        $user = Auth::user();
        $operacion = Operacion::findOrFail($id);

        // Verificar que el trámite esté disponible
        if ($operacion->usuario_cierre_id !== null) {
            return redirect()->back()->with('error', 'Este trámite ya está asignado a otro documentador.');
        }

        // Asignar el trámite al usuario
        $operacion->usuario_cierre_id = $user->id;
        $operacion->estado = 'pendiente';
        $operacion->save();

        return redirect()->back()->with('success', 'Trámite tomado exitosamente.');
    }

    // Método para soltar un trámite
    public function soltarTramite(Request $request, $id)
    {
        $user = Auth::user();
        $operacion = Operacion::findOrFail($id);

        // Verificar que el trámite sea del usuario
        if ($operacion->usuario_cierre_id !== $user->id) {
            return redirect()->back()->with('error', 'No puedes soltar un trámite que no te pertenece.');
        }

        // Verificar que el trámite NO esté en proceso
        if ($operacion->estado === 'proceso') {
            return redirect()->back()->with('error', 'No puedes soltar un trámite que ya está en proceso.');
        }

        // Soltar el trámite
        $operacion->usuario_cierre_id = null;
        $operacion->estado = 'pendiente';
        $operacion->save();

        return redirect()->back()->with('success', 'Trámite liberado exitosamente.');
    }
    public function index_antesgravity(Request $request)
    {
        $user = Auth::user();

        // -------- 1. TODOS los trámites del día con ordenamiento especial --------
        /*$query = Operacion::whereDate('fecha_registro', now()->today())
         ->with('cliente')
         ->orderByRaw("CASE 
         WHEN usuario_cierre_id = ? THEN 0 
         WHEN usuario_cierre_id IS NULL THEN 1 
         ELSE 2 
         END", [$user->id])
         ->orderBy('prioridad', 'desc')
         ->orderBy('created_at', 'asc');*/

        $query = Operacion::whereDate('fecha_registro', '>=', now()->today())
            ->with('cliente')
            ->orderByRaw("
        CASE 
            WHEN DATE(fecha_registro) = CURDATE() THEN 0 
            ELSE 1 
        END
    ")
            ->orderByRaw("
        CASE 
            WHEN usuario_cierre_id = ? THEN 0 
            WHEN usuario_cierre_id IS NULL THEN 1 
            ELSE 2 
        END
    ", [$user->id])
            ->orderBy('fecha_registro', 'asc') // hoy → mañana → futuros
            ->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc');


        // Si NO está activo "mostrar terminadas", filtramos solo pendientes o en proceso
        if (!$request->boolean('show_completed')) {
            $query->whereIn('estado', ['pendiente', 'proceso']);
        }

        $operaciones = $query->get();

        // Separar trámites para la vista
        $tramitesPropios = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id == $user->id);
        $tramitesDisponibles = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id == null);
        $tramitesOtros = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id != null && $exp->usuario_cierre_id != $user->id);

        // -------- 2. Estadísticas del día (solo del usuario) --------
        $operacionesHoy = Operacion::where('usuario_cierre_id', $user->id)
            ->whereDate('fecha_registro', now()->today())
            ->get();

        $totalHoy = $operacionesHoy->count();
        $completadosHoy = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $totalHoy - $completadosHoy;
        $efectividad = $totalHoy > 0 ? number_format(($completadosHoy / $totalHoy) * 100, 2) : 0;

        // -------- 3. Ranking semanal --------
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $estadoCompletado = 'terminado';

        $rankingSemanal = User::where('role', 'documentador')
            ->where('active', 1)
            ->withCount([
                'operaciones as completadas_semana' => function ($query) use ($estadoCompletado, $inicioSemana, $finSemana) {
                    $query->whereBetween('fecha_registro', [$inicioSemana, $finSemana])
                        ->where('estado', $estadoCompletado);
                }
            ])
            ->orderByDesc('completadas_semana')
            ->get()
            ->map(function ($rankItem) use ($user) {
                return (object) [
                    'name' => $rankItem->name,
                    'total_tramites' => $rankItem->completadas_semana,
                    'is_current_user' => $rankItem->id === $user->id
                ];
            });

        // Posición del usuario actual en el ranking
        $posicionUser = $rankingSemanal->search(fn($r) => $r->is_current_user);
        $posicionUser = $posicionUser !== false ? $posicionUser + 1 : null;

        // Contar trámites disponibles para asignar
        $tramitesDisponiblesCount = Operacion::where('fecha_registro', '>=', today())
            ->whereNull('usuario_cierre_id')
            ->count();

        $stats = [
            'total_hoy' => $totalHoy,
            'completados_hoy' => $completadosHoy,
            'pendientes' => $pendientes,
            'efectividad' => $efectividad,
            'ranking_posicion' => $posicionUser
        ];

        return view('documentador.dashboard', compact(
            'stats',
            'operaciones',
            'rankingSemanal',
            'tramitesDisponiblesCount',
            'tramitesPropios',
            'tramitesDisponibles',
            'tramitesOtros'
        ));
    }

    public function liveData(Request $request)
    {
        $user = Auth::user();
        $hoy = now()->today();

        $query = Operacion::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['cliente', 'importador', 'aduana', 'bodega', 'expediente']);

        // Si NO hay búsqueda global, aplicar filtro de fecha por defecto (hoy en adelante)
        if (!$request->filled('q')) {
            $query->where(function ($q) use ($hoy) {
                $q->whereDate('fecha_cruce_estimada', '>=', $hoy)
                    ->orWhereNull('fecha_cruce_estimada');
            });
        }

        // INC-020: Incluir operación específica por ID sin importar filtro de fecha (para notificaciones)
        if ($request->filled('op')) {
            $opId = (int) $request->input('op');
            // Reemplazar el where de fecha: incluir hoy EN ADELANTE o la operación específica
            if (!$request->filled('q')) {
                // Reconstruir el query sin el filtro de fecha excluyente
                $query = Operacion::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->with(['cliente', 'importador', 'aduana', 'bodega', 'expediente'])
                    ->where(function ($q) use ($hoy, $opId) {
                        $q->whereDate('fecha_cruce_estimada', '>=', $hoy)
                            ->orWhereNull('fecha_cruce_estimada')
                            ->orWhere('id', $opId);
                    });
            }
        }

        $query->whereIn('estado', ['capturada', 'pendiente', 'proceso', 'terminado', 'cancelada']);

        // Búsqueda global por texto (ignora fechas)
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sq) use ($search) {
                $sq->where('referencia', 'like', '%' . $search . '%')
                    ->orWhere('num_factura', 'like', '%' . $search . '%')
                    ->orWhere('nombre_producto', 'like', '%' . $search . '%')
                    ->orWhere('codigo_alpha', 'like', '%' . $search . '%')
                    ->orWhere('num_thermo', 'like', '%' . $search . '%')
                    ->orWhere('num_doda', 'like', '%' . $search . '%')
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('bodega', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('aduana', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('importador', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('expediente', function ($q) use ($search) {
                        $q->where('numero_pedimento', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filtros dinámicos
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('referencia')) {
            $query->where('referencia', 'like', '%' . $request->referencia . '%');
        }
        if ($request->filled('aduana_id')) {
            $query->where('aduana_id', $request->aduana_id);
        }
        if ($request->filled('bodega_id')) {
            $query->where('bodega_id', $request->bodega_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_cruce_estimada', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_cruce_estimada', '<=', $request->fecha_hasta);
        }
        if ($request->filled('estado_filtro')) {
            $query->where('estado', $request->estado_filtro);
        }

        $operaciones = $query->orderByRaw("
            CASE 
                WHEN estado = 'cancelada' THEN 99
                WHEN DATE(fecha_cruce_estimada) = CURDATE() THEN 0 
                WHEN fecha_cruce_estimada IS NULL THEN 1
                ELSE 2 
            END
        ")
            ->orderBy('created_at', 'asc')
            ->get();

        // Métricas: excluir canceladas
        $operacionesActivas = $operaciones->where('estado', '!=', 'cancelada');
        $verdes = $operacionesActivas->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojas = $operacionesActivas->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count();
        $canceladasCount = $operaciones->where('estado', 'cancelada')->count();

        $clienteIds = $operaciones->pluck('cliente_id')->unique();

        $expedientes = Expediente::whereIn('cliente_id', $clienteIds)
            ->whereIn('estado', ['En proceso', 'Abierto'])
            ->select('id', 'numero_pedimento', 'cliente_id')
            ->get()
            ->groupBy('cliente_id');

        $opsData = $operaciones->map(function ($op) {
            return [
                'id' => $op->id,
                'referencia' => $op->referencia ?? 'S/R',
                'factura' => $op->num_factura ?? 'S/F',
                'doda' => $op->num_doda,
                'estado' => $op->estado,
                'cliente_id' => $op->cliente_id,
                'pedimento' => $op->expediente ? $op->expediente->numero_pedimento : null,
                'pedimento_id' => $op->expediente_id,
                'modulacion' => $op->modulacion,
                'bot_logs' => is_string($op->bot_logs_json) ? json_decode($op->bot_logs_json, true) : $op->bot_logs_json,
                'cliente_nombre' => $op->cliente ? ($op->cliente->nombre ?? $op->cliente->nombre_empresa) : 'N/A',
                'producto' => $op->nombre_producto,
                'aduana' => $op->aduana ? $op->aduana->nombre : 'N/A',
                'bodega' => $op->bodega ? $op->bodega->nombre : 'N/A',
                'importador' => $op->importador ? $op->importador->nombre : 'N/A',
                'thermo' => $op->num_thermo,
                'alpha' => $op->codigo_alpha,
                'fecha_cruce' => $op->fecha_cruce_estimada ? $op->fecha_cruce_estimada->format('Y-m-d') : null,
                'motivo_cancelacion' => $op->motivo_cancelacion,
                'documentos_count' => $op->documentos()->count(),
            ];
        });

        return response()->json([
            'operaciones' => $opsData,
            'expedientes' => $expedientes,
            'grafica' => [
                'verdes' => $verdes,
                'rojas' => $rojas,
                'canceladas' => $canceladasCount,
            ]
        ]);
    }

    public function updateDodaPedimento(Request $request, $id)
    {
        $user = Auth::user();
        $operacion = Operacion::findOrFail($id);

        // Permitimos que cualquier documentador del tenant actualice la info
        // (El scope global de tenant ya filtra que sea de su misma agencia)

        $request->validate([
            'num_doda' => 'nullable|string|max:50',
            'pedimento_id' => 'nullable|exists:expedientes,id'
        ]);

        $operacion->num_doda = strtoupper(trim($request->num_doda));
        $operacion->expediente_id = $request->pedimento_id;

        // Sincronizar patente si hay pedimento
        if ($request->pedimento_id) {
            $expediente = Expediente::find($request->pedimento_id);
            if ($expediente && $expediente->patente_id) {
                $operacion->patente_id = $expediente->patente_id;
            }
        }

        // ASIGNACIÓN DE "CRÉDITO" (usuario_cierre_id):
        // Solo se asigna la primera vez que alguien guarda información de cierre (DODA/Pedimento)
        if (!$operacion->usuario_cierre_id || $operacion->usuario_cierre_id == 0) {
            $operacion->usuario_cierre_id = $user->id;

            // Si el estado era 'capturada', lo pasamos a 'proceso' o 'pendiente' según tu flujo
            if ($operacion->estado === 'capturada') {
                $operacion->estado = 'proceso';
            }
        }

        $operacion->save();

        return response()->json(['success' => true, 'message' => 'Información actualizada correctamente.']);
    }

    public function storeOperacion(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'fecha_cruce_estimada' => 'required|date',
                'cliente_id' => 'required|exists:cliente,id',
                'importador_id' => 'required|exists:importadores,id',
                'nombre_producto' => 'required|string|max:255',
                'aduana_id' => 'required|exists:aduanas,id',
                'num_factura' => 'required|string|max:50',
                'bodega_id' => 'nullable|exists:bodegas,id',
                'num_thermo' => 'nullable|string|max:50',
                'codigo_alpha' => 'nullable|string|max:20',
                'archivos.*' => 'nullable|file|max:20480',
                'tipos_archivos.*' => 'nullable|string'
            ]);

            $data = $request->only([
                'fecha_cruce_estimada',
                'cliente_id',
                'importador_id',
                'nombre_producto',
                'aduana_id',
                'num_factura',
                'bodega_id',
                'num_thermo',
                'codigo_alpha'
            ]);

            $data['fecha_registro'] = now()->format('Y-m-d H:i:s');

            $data['usuario_registro_id'] = auth()->id();

            if (!empty($data['num_thermo'])) {
                $data['num_thermo'] = strtoupper(preg_replace('/\s+/', '-', $data['num_thermo']));
            }
            if (!empty($data['codigo_alpha'])) {
                $data['codigo_alpha'] = strtoupper(trim($data['codigo_alpha']));
            }

            $data['estado'] = 'pendiente';
            $data['referencia'] = Operacion::generarSiguienteReferencia();

            $operacion = Operacion::create($data);

            if ($request->hasFile('archivos')) {
                $tipos = $request->input('tipos_archivos', []);
                $tenantId = auth()->user()->tenant_id;
                foreach ($request->file('archivos') as $index => $archivo) {
                    $tipoDoc = $tipos[$index] ?? 'otros';
                    $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);

                    $meta = $this->storageService->upload(
                        $archivo,
                        $tenantId,
                        $operacion->id,
                        $tipoDoc,
                        $nombreOriginal
                    );

                    Documento::create([
                        'tenant_id' => $tenantId,
                        'operacion_id' => $operacion->id,
                        'nombre' => $nombreOriginal,
                        'ruta' => $meta['path'],
                        'url_archivo' => $meta['url'],
                        'peso' => $meta['peso'],
                        'extension' => $meta['extension'],
                        'tipo_documento' => $tipoDoc,
                    ]);
                }
            }

            // DB::commit() will be called after this
            // We removed the automatic assignment of usuario_cierre_id here
            // as it should be NULL until DODA/Pedimento are provided.
            // $operacion->usuario_cierre_id = auth()->id();
            // $operacion->estado = 'proceso';
            // $operacion->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Operación ' . $data['referencia'] . ' creada correctamente.',
                'referencia' => $data['referencia']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // -------- 1. TODOS los trámites del día con ordenamiento especial --------
        /*$query = Operacion::whereDate('fecha_registro', now()->today())
         ->with('cliente')
         ->orderByRaw("CASE 
         WHEN usuario_cierre_id = ? THEN 0 
         WHEN usuario_cierre_id IS NULL THEN 1 
         ELSE 2 
         END", [$user->id])
         ->orderBy('prioridad', 'desc')
         ->orderBy('created_at', 'asc');*/

        $query = Operacion::where(function ($q) {
            $q->whereDate('fecha_cruce_estimada', '>=', now()->today())
                ->orWhereNull('fecha_cruce_estimada');
        })
            ->with('cliente')
            ->orderByRaw("
        CASE 
            WHEN DATE(fecha_cruce_estimada) = CURDATE() THEN 0 
            WHEN fecha_cruce_estimada IS NULL THEN 1
            ELSE 2 
        END
    ")
            ->orderByRaw("
        CASE 
            WHEN usuario_cierre_id = ? THEN 0 
            WHEN usuario_cierre_id IS NULL THEN 1 
            ELSE 2 
        END
    ", [$user->id])
            ->orderBy('fecha_cruce_estimada', 'asc') // hoy → mañana → futuros
            ->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc');


        // Si NO está activo "mostrar terminadas", filtramos estados operativos
        if (!$request->boolean('show_completed')) {
            $query->whereIn('estado', ['capturada', 'pendiente', 'proceso']);
        }

        $operaciones = $query->get();

        $tramitesPropios = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id == $user->id);
        $tramitesDisponibles = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id == null);
        $tramitesOtros = $operaciones->filter(fn($exp) => $exp->usuario_cierre_id != null && $exp->usuario_cierre_id != $user->id);

        // 🚛 Precalcular consolidados en UNA sola query (elimina N+1)
        $thermoAlphas = $operaciones
            ->filter(fn($op) => $op->num_thermo && $op->codigo_alpha)
            ->map(fn($op) => $op->num_thermo . '|' . $op->codigo_alpha)
            ->unique()
            ->values();

        $consolidados = collect();
        if ($thermoAlphas->isNotEmpty()) {
            $grupos = Operacion::where(function ($q) use ($thermoAlphas) {
                foreach ($thermoAlphas as $ta) {
                    [$thermo, $alpha] = explode('|', $ta);
                    $q->orWhere(function ($sub) use ($thermo, $alpha) {
                        $sub->where('num_thermo', $thermo)
                            ->where('codigo_alpha', $alpha);
                    });
                }
            })
                ->whereDate('fecha_cruce_estimada', '>=', now()->today())
                ->select('id', 'num_thermo', 'codigo_alpha')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy(fn($op) => $op->num_thermo . '|' . $op->codigo_alpha);

            $consolidados = $grupos->map(fn($group) => [
                'count' => $group->count(),
                'first_id' => $group->first()->id,
            ]);
        }

        foreach ($operaciones as $operacion) {
            if ($operacion->num_thermo && $operacion->codigo_alpha) {
                $key = $operacion->num_thermo . '|' . $operacion->codigo_alpha;
                $operacion->consolidado_count = $consolidados[$key]['count'] ?? 1;
                $operacion->consolidado_first = ($consolidados[$key]['first_id'] ?? $operacion->id) == $operacion->id;
            } else {
                $operacion->consolidado_count = 1;
                $operacion->consolidado_first = true;
            }
        }

        // -------- 2. Estadísticas del día (solo del usuario, sin canceladas) --------
        $operacionesHoy = Operacion::where('usuario_cierre_id', $user->id)
            ->whereDate('fecha_cruce_estimada', now()->today())
            ->where('estado', '!=', 'cancelada')
            ->get();

        $totalHoy = $operacionesHoy->count();
        $completadosHoy = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $totalHoy - $completadosHoy;
        $efectividad = $totalHoy > 0 ? number_format(($completadosHoy / $totalHoy) * 100, 2) : 0;

        // KPI: Operaciones canceladas del día
        $canceladasHoy = Operacion::whereDate('fecha_cancelacion', now()->today())
            ->where('estado', 'cancelada')
            ->count();

        // -------- 3. Ranking semanal --------
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $estadoCompletado = 'terminado';

        $rankingSemanal = User::where('role', 'documentador')
            ->where('active', 1)
            ->withCount([
                'operaciones as completadas_semana' => function ($query) use ($estadoCompletado, $inicioSemana, $finSemana) {
                    $query->whereBetween('fecha_cruce_estimada', [$inicioSemana, $finSemana])
                        ->where('estado', $estadoCompletado);
                },
                'operaciones as canceladas_semana' => function ($query) use ($inicioSemana, $finSemana) {
                    $query->whereBetween('fecha_cancelacion', [$inicioSemana, $finSemana])
                        ->where('estado', 'cancelada');
                }
            ])
            ->orderByDesc('completadas_semana')
            ->get()
            ->map(function ($rankItem) use ($user) {
                return (object) [
                    'name' => $rankItem->name,
                    'total_tramites' => $rankItem->completadas_semana,
                    'is_current_user' => $rankItem->id === $user->id
                ];
            });

        // Posición del usuario actual en el ranking
        $posicionUser = $rankingSemanal->search(fn($r) => $r->is_current_user);
        $posicionUser = $posicionUser !== false ? $posicionUser + 1 : null;

        // Contar trámites disponibles para asignar
        $tramitesDisponiblesCount = Operacion::where('fecha_cruce_estimada', today())
            ->whereNull('usuario_cierre_id')
            ->count();

        $stats = [
            'total_hoy' => $totalHoy,
            'completados_hoy' => $completadosHoy,
            'pendientes' => $pendientes,
            'efectividad' => $efectividad,
            'ranking_posicion' => $posicionUser
        ];

        $tenantId = Auth::user()->tenant_id;
        $opFiltros = [
            'clientes' => \Illuminate\Support\Facades\DB::table('cliente')->where('tenant_id', $tenantId)->orderBy('nombre')->get(),
            'importadores' => \Illuminate\Support\Facades\DB::table('importadores')->where('tenant_id', $tenantId)->orderBy('nombre')->get(),
            'aduanas' => \Illuminate\Support\Facades\DB::table('aduanas')->orderBy('nombre')->get(),
            'bodegas' => \Illuminate\Support\Facades\DB::table('bodegas')->where('tenant_id', $tenantId)->orderBy('nombre')->get()
        ];

        // Obtener configuración del SOIA-Bot para el tenant actual
        $tenant = $user->tenant;
        $botMode = $tenant ? $tenant->getBotMode() : 'deshabilitado';
        $botEnabled = $tenant ? $tenant->isBotEnabled() : false;
        $botAutomatic = $tenant ? $tenant->isBotAutomatic() : false;

        return view('documentador.dashboard', compact(
            'stats',
            'operaciones',
            'rankingSemanal',
            'tramitesDisponiblesCount',
            'tramitesPropios',
            'tramitesDisponibles',
            'tramitesOtros',
            'opFiltros',
            'botMode',
            'botEnabled',
            'botAutomatic'
        ));
    }


    /**
     * Asigna un trámite sin asignar al usuario actual.
     * * @return \Illuminate\Http\Response
     */
    public function takeAssignment_antesgravity()
    {
        $user = Auth::user();

        // Encuentra el trámite sin asignar más antiguo
        $operacion = Operacion::where('fecha_registro', today())->whereNull('usuario_cierre_id')
            ->orderBy('fecha_registro', 'asc')
            ->first();

        if ($operacion) {
            $operacion->usuario_cierre_id = $user->id;
            $operacion->save();
            return back()->with('success', 'Trámite #' . $operacion->id . ' tomado con éxito.');
        }

        return back()->with('error', 'No hay trámites disponibles para tomar.');
    }

    public function takeAssignment_malo()
    {
        $user = Auth::user();

        // Encuentra el trámite sin asignar más antiguo
        $operacion = Operacion::where('fecha_registro', today())->whereNull('usuario_cierre_id')
            ->orderBy('fecha_registro', 'asc')
            ->first();

        if ($operacion) {
            // Asignar el trámite principal
            $operacion->usuario_cierre_id = $user->id;
            $operacion->save();

            // 🚛 CONSOLIDADOS: Buscar y asignar operaciones del mismo camión
            $consolidadosAsignados = 0;
            if ($operacion->num_thermo && $operacion->codigo_alpha) {
                $consolidados = Operacion::where('id', '!=', $operacion->id)
                    ->where('num_thermo', $operacion->num_thermo)
                    ->where('codigo_alpha', $operacion->codigo_alpha)
                    ->whereNull('usuario_cierre_id')
                    ->whereIn('estado', ['pendiente', 'proceso'])
                    ->get();

                foreach ($consolidados as $consolidado) {
                    $consolidado->usuario_cierre_id = $user->id;
                    $consolidado->save();
                    $consolidadosAsignados++;
                }
            }

            $mensaje = 'Trámite #' . $operacion->id . ' tomado con éxito.';
            if ($consolidadosAsignados > 0) {
                $mensaje .= " + {$consolidadosAsignados} operación(es) consolidada(s) del mismo camión";
            }

            return back()->with('success', $mensaje);
        }

        return back()->with('error', 'No hay trámites disponibles para tomar.');
    }

    public function takeAssignment(Request $request)
    {
        $user = Auth::user();
        $operacionId = $request->input('operacion_id');

        // 1. Identificar la exportación a tomar
        if ($operacionId) {
            $operacion = Operacion::whereNull('usuario_cierre_id')->find($operacionId);
        } else {
            // Encuentra el trámite sin asignar más antiguo
            $operacion = Operacion::where('fecha_registro', today())->whereNull('usuario_cierre_id')
                ->orderBy('fecha_registro', 'asc')
                ->first();
        }

        if ($operacion) {
            // Asignar el trámite principal
            $operacion->usuario_cierre_id = $user->id;

            $operacion->save();

            // 🚛 CONSOLIDADOS: Buscar y asignar operaciones del mismo camión
            $consolidadosAsignados = 0;
            if ($operacion->num_thermo && $operacion->codigo_alpha) {
                $consolidados = Operacion::where('id', '!=', $operacion->id)
                    ->where('num_thermo', $operacion->num_thermo)
                    ->where('codigo_alpha', $operacion->codigo_alpha)
                    ->whereNull('usuario_cierre_id')
                    ->whereIn('estado', ['pendiente', 'proceso'])
                    ->get();

                foreach ($consolidados as $consolidado) {
                    $consolidado->usuario_cierre_id = $user->id;
                    //$consolidado->estado = 'proceso';
                    $consolidado->save();
                    $consolidadosAsignados++;
                }
            }

            $mensaje = 'Trámite #' . ($operacion->referencia ?? $operacion->id) . ' tomado con éxito.';
            if ($consolidadosAsignados > 0) {
                $mensaje .= " + {$consolidadosAsignados} operación(es) consolidada(s) del mismo camión";
            }

            return back()->with('success', $mensaje);
        }

        return back()->with('error', 'No hay trámites disponibles para tomar.');
    }

    /**
     * Método auxiliar para obtener la posición del usuario en el ranking.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getWeeklyRanking($user)
    {
        $ranking = User::where('role', 'documentador')
            ->withCount([
                'operaciones' => function ($query) {
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->where('estado', 'completado');
                }
            ])
            ->orderByDesc('operaciones_count')
            ->get();

        $position = $ranking->search(function ($rankItem) use ($user) {
            return $rankItem->id === $user->id;
        });

        $totalTramites = $ranking->firstWhere('id', $user->id)->operaciones_count ?? 0;

        // Aquí podrías calcular la variación si tuvieras datos de la semana anterior,
        // por simplicidad, lo he dejado como un valor fijo.
        $variacion = 'n/a';

        return [
            'posicion' => ($position !== false) ? $position + 1 : 'N/A',
            'variacion' => $variacion,
            'total' => $totalTramites
        ];
    }



}