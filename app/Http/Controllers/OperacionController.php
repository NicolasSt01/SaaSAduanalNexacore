<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Operacion;
use App\Models\Cliente;
use App\Models\Importador;
use App\Models\Bodega;
use App\Models\Aduana;
use App\Models\Patente;
use App\Models\Expediente;
use App\Models\Referencia;
use App\Models\User;
use App\Services\NotificacionService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OperacionStatusMail; //Mail que vamos a crear
use App\Mail\EstatusModulacionMail;
use App\Jobs\EnviarCorreoModulacionJob;

class OperacionController extends Controller
{
    //
    protected $notificacionService; // 🔔 AGREGAR
    public function __construct(NotificacionService $notificacionService)
    {
        $this->middleware('auth')->except(['checkTrafico', 'checkTraficoBot']);
        $this->notificacionService = $notificacionService;
    }

    public function index(Request $request)
    {
        // Solo administradores del tenant (o superior) pueden ver esta gestión operacional
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            $route = config("dashboards.role_routes." . auth()->user()->role, 'home');
            return redirect()->route($route)
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        $hoy = Carbon::today();
        $query = Operacion::where('estado', '!=', 'cancelada')->with(["cliente", "importador", "bodega", "usuarioRegistro", "usuarioCierre"]);

        if ($request->filled("fecha_inicio")) {
            $query->whereDate("fecha_cruce_estimada", ">=", $request->fecha_inicio);
        }
        if ($request->filled("fecha_fin")) {
            $query->whereDate("fecha_cruce_estimada", "<=", $request->fecha_fin);
        }
        if (!$request->filled("fecha_inicio") && !$request->filled("fecha_fin") && !$request->filled("busqueda")) {
            $query->whereDate("fecha_cruce_estimada", $hoy);
        }

        if ($request->filled("estado")) {
            $query->where("estado", $request->estado);
        }

        if ($request->filled("prioridad")) {
            $query->where("prioridad", $request->prioridad);
        }

        if ($request->filled("busqueda")) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where("referencia", "like", "%" . $busqueda . "%")
                  ->orWhere("num_factura", "like", "%" . $busqueda . "%")
                  ->orWhereHas("cliente", function ($q2) use ($busqueda) {
                      $q2->where("nombre", "like", "%" . $busqueda . "%");
                  });
            });
        }

        $query->orderByRaw("
            CASE 
                WHEN prioridad = 'urgente' THEN 1
                WHEN prioridad = 'alta' THEN 2
                WHEN prioridad = 'media' THEN 3
                ELSE 4
            END,
            CASE 
                WHEN estado = 'pendiente' THEN 1
                WHEN estado = 'proceso' THEN 2
                ELSE 3
            END,
            fecha_cruce_estimada ASC
        ");

        $operaciones = $query->paginate(50)->withQueryString();

        $statsHoy = Operacion::whereDate("fecha_cruce_estimada", $hoy)
            ->where('estado', '!=', 'cancelada')
            ->get();
        $totalHoy = $statsHoy->count();
        $pendientesHoy = $statsHoy->where("estado", "pendiente")->count();
        $procesoHoy = $statsHoy->where("estado", "proceso")->count();
        $completadosHoy = $statsHoy->where("estado", "terminado")->count();

        $topRegistradores = Operacion::select("usuario_registro_id", DB::raw("count(*) as total"))
            ->whereDate("fecha_cruce_estimada", $hoy)
            ->where('estado', '!=', 'cancelada')
            ->groupBy("usuario_registro_id")
            ->with(["usuarioRegistro" => fn($q) => $q->select("id", "name")])
            ->orderByDesc("total")
            ->take(5)->get();

        $topCerradores = Operacion::select("usuario_cierre_id", DB::raw("count(*) as total"))
            ->whereDate("fecha_cruce_estimada", $hoy)
            ->whereNotNull("usuario_cierre_id")
            ->where('estado', '!=', 'cancelada')
            ->groupBy("usuario_cierre_id")
            ->with(["usuarioCierre" => fn($q) => $q->select("id", "name")])
            ->orderByDesc("total")
            ->take(5)->get();

        return view("operaciones.index", compact(
            "operaciones", "totalHoy", "pendientesHoy", "procesoHoy", "completadosHoy",
            "topRegistradores", "topCerradores"
        ));
    }

    public function updatePriority(Request $request, Operacion $operacion)
    {
        $request->validate(["prioridad" => "required|in:regular,media,alta,urgente"]);
        $operacion->update(["prioridad" => $request->prioridad]);
        return response()->json(["success" => true]);
    }

    public function destroy(Operacion $operacion)
    {
        $operacion->delete();
        return response()->json(['success' => true, 'message' => 'Operación eliminada']);
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre_empresa')->get();
        $importadores = Importador::orderBy('nombre')->get();
        $bodegas = Bodega::orderBy('nombre_bodega')->get();
        $aduanas = Aduana::orderBy('nombre_aduana')->get();
        $patentes = Patente::orderBy('numero_patente')->get();
        $expedientes = Expediente::whereIn('estado', ['Abierto', 'En proceso'])->orderBy('numero_pedimento')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('operaciones.create', compact(
            'clientes',
            'importadores',
            'bodegas',
            'aduanas',
            'patentes',
            'expedientes',
            'documentadores'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'fecha_registro' => 'nullable|date',
            'fecha_cruce_estimada' => 'required|date',
            'cliente_id' => 'required|exists:cliente,id',
            'importador_id' => 'required|exists:importadores,id',
            'nombre_producto' => 'required|string|max:500',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'num_factura' => 'required|string|max:50',
            'aduana_id' => 'required|exists:aduanas,id',
            'patente_id' => 'nullable|exists:patentes,id',
            'prioridad' => 'required|in:regular,media,alta,urgente',
            'estado' => 'required|in:pendiente,proceso,terminado',
            'num_thermo' => 'nullable|string|max:50',
            'codigo_alpha' => 'nullable|string|max:20',
            'num_doda' => 'nullable|string|max:50',
            'modulacion' => 'nullable|string|max:100',
            'pedimento_id' => [
                'nullable',
                'exists:expedientes,id',
                function ($attribute, $value, $fail) {
                    $exp = Expediente::find($value);
                    if ($exp && $exp->estado === 'Cerrado') {
                        $fail('No se pueden relacionar operaciones a un pedimento cerrado.');
                    }
                },
            ],
            'observaciones' => 'nullable|string',
        ];

        $request->validate($rules);

        $data = $request->all();
        // Si no se envía fecha_registro, usar hoy
        if (empty($data['fecha_registro'])) {
            $data['fecha_registro'] = Carbon::today();
        }
        
        // Asignar el usuario autenticado como registrador
        $data['usuario_registro_id'] = auth()->id();
        $data['tenant_id'] = auth()->user()->tenant_id;

        Operacion::create($data);

        return redirect()->route('operaciones.index')
            ->with('success', 'Operación registrada correctamente.');
    }
    public function storetrafico_old(Request $request)
    {

        try {
            $request->validate([
                'fecha_registro' => 'required|date',
                'cliente_id' => 'required|exists:cliente,id', // Tabla 'cliente'
                'importador_id' => 'required|exists:importadores,id',
                'nombre_producto' => 'required|string|max:255',
                'bodega_id' => 'required|exists:bodegas,id',
                'num_factura' => 'required|string|max:50',
                'aduana_id' => 'required|exists:aduanas,id',
                'patente_id' => 'nullable|exists:patentes,id',
                'pedimento_id' => 'nullable|exists:expedientes,id',
                'num_thermo' => 'nullable|string|max:50',
                'codigo_alpha' => 'nullable|string|max:20',
                'num_doda' => 'nullable|string|max:50',
                'modulacion' => 'nullable|string|max:100',
                'usuario_registro_id' => 'required|exists:users,id',

            ]);

            // Asignar el usuario autenticado como documentador si no se proporciona
            $data = $request->all();
            if (empty($data['usuario_registro_id'])) {
                $data['usuario_registro_id'] = auth()->id();
            }

            // ⚡ normalizamos el num_thermo
            if (!empty($data['num_thermo'])) {
                // Reemplaza cualquier espacio en blanco por guion
                $data['num_thermo'] = preg_replace('/\s+/', '-', $data['num_thermo']);
                // Convertir a mayúsculas o minúsculas si quieres consistencia
                 $data['num_thermo'] = strtoupper($data['num_thermo']);
            }


            $data['estado'] = 'pendiente';

            $operacion = Operacion::create($data);
            // 🔥 NUEVA LÓGICA: Notificar si falta código Alpha
            if(empty($operacion->codigo_alpha)){
                app(NotificacionService::class)->
                notificarAlphaPendiente($operacion);
            }
        



            return redirect()->route('trafico.operaciones.show', $operacion->id)
                ->with('success', 'Exportación registrada correctamente.');
        } catch (Exception $e) {
            //dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al crear la exportación: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function storetrafico_old2(Request $request)
    {

        try {
            $request->validate([
                'fecha_registro' => 'required|date',
                'cliente_id' => 'required|exists:cliente,id', // Tabla 'cliente'
                'importador_id' => 'required|exists:importadores,id',
                'nombre_producto' => 'required|string|max:255',
                'bodega_id' => 'required|exists:bodegas,id',
                'num_factura' => 'required|string|max:50',
                'aduana_id' => 'required|exists:aduanas,id',
                'patente_id' => 'required|exists:patentes,id',
                'pedimento_id' => 'nullable|exists:expedientes,id',
                'num_thermo' => 'nullable|string|max:50',
                'codigo_alpha' => 'nullable|string|max:20',
                'num_doda' => 'nullable|string|max:50',
                'modulacion' => 'nullable|string|max:100',
                'usuario_registro_id' => 'required|exists:users,id',

            ]);

            // Asignar el usuario autenticado como documentador si no se proporciona
            $data = $request->all();
            if (empty($data['usuario_registro_id'])) {
                $data['usuario_registro_id'] = auth()->id();
            }

            // ⚡ normalizamos el num_thermo
            if (!empty($data['num_thermo'])) {
                // Reemplaza cualquier espacio en blanco por guion
                $data['num_thermo'] = preg_replace('/\s+/', '-', $data['num_thermo']);
                // Convertir a mayúsculas o minúsculas si quieres consistencia
                $data['num_thermo'] = strtoupper($data['num_thermo']);
            }


            $data['estado'] = 'pendiente';


            // ================================================
            //   🔵 GENERACIÓN DE REFERENCIA SEGURA
            // ================================================
            DB::beginTransaction();

            $anio = now()->format('y'); // 25
            $mes = now()->format('m'); // 11

            // Buscar registro del año actual con bloqueo
            $ref = \App\Models\Referencia::where('anio', $anio)
                ->lockForUpdate()
                ->first();

            if (!$ref) {
                // Si no existe, lo creamos con contador en 1
                $ref = \App\Models\Referencia::create([
                    'anio' => $anio,
                    'contador' => 1
                ]);
            } else {
                // Incrementar contador
                $ref->contador = $ref->contador + 1;
                $ref->save();
            }

            // Construir referencia final
            $data['referencia'] = $anio . $mes . '-' . $ref->contador;








            //Crear el registro de operacion
            $operacion = Operacion::create($data);

            DB::commit();


            return redirect()->route('trafico.operaciones.show', $operacion->id)
                ->with('success', 'Exportación registrada correctamente.');
        } catch (Exception $e) {
            DB::rollback();
            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al crear la exportación: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function storetrafico_Funcional06122025(Request $request)
{
    try {
        // Validación adaptada a los nuevos campos
        $validated = $request->validate([
            // Campos obligatorios
            'fecha_registro' => 'required|date',
            'cliente_id' => 'required|exists:cliente,id',
            'importador_id' => 'required|exists:importadores,id',
            'nombre_producto' => 'required|string|max:255',
            'aduana_id' => 'required|exists:aduanas,id',
            'num_factura' => 'required|string|max:50',
            
            // Campos opcionales (nullable)
            'bodega_id' => 'nullable|exists:bodegas,id',
            'num_thermo' => 'nullable|string|max:50',
            'codigo_alpha' => 'nullable|string|max:20',
        ], [
            // Mensajes personalizados
            'fecha_registro.required' => 'La fecha_registro de cruce es obligatoria.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'importador_id.required' => 'Debe seleccionar un importador.',
            'importador_id.exists' => 'El importador seleccionado no es válido.',
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'aduana_id.required' => 'Debe seleccionar una aduana.',
            'aduana_id.exists' => 'La aduana seleccionada no es válida.',
            'num_factura.required' => 'El número de factura es obligatorio.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
        ]);

        $data = $validated;

        // Asignar el usuario autenticado como documentador
        $data['usuario_registro_id'] = auth()->id();

        // Normalizar num_thermo si existe
        if (!empty($data['num_thermo'])) {
            $data['num_thermo'] = strtoupper(preg_replace('/\s+/', '-', $data['num_thermo']));
        }

        // Normalizar codigo_alpha si existe
        if (!empty($data['codigo_alpha'])) {
            $data['codigo_alpha'] = strtoupper(trim($data['codigo_alpha']));
        }

        // Establecer valores por defecto para campos que no vienen del formulario
        $data['estado'] = 'pendiente';
        $data['patente_id'] = null; // Se asignará después por Documentación
        $data['pedimento_id'] = null;
        $data['num_doda'] = null;
        $data['modulacion'] = null;

        // ================================================
        //   🔵 GENERACIÓN DE REFERENCIA SEGURA
        // ================================================
        DB::beginTransaction();

        $anio = now()->format('y'); // 25
        $mes = now()->format('m');   // 12

        // Buscar registro del año actual con bloqueo
        $ref = \App\Models\Referencia::where('anio', $anio)
            ->lockForUpdate()
            ->first();

        if (!$ref) {
            // Si no existe, lo creamos con contador en 1
            $ref = \App\Models\Referencia::create([
                'anio' => $anio,
                'contador' => 1
            ]);
        } else {
            // Incrementar contador
            $ref->contador = $ref->contador + 1;
            $ref->save();
        }

        // Construir referencia final
        $data['referencia'] = $anio . $mes . '-' . $ref->contador;

        // Crear el registro de exportación
        $operacion = Operacion::create($data);

        DB::commit();

        return redirect()->route('trafico.operaciones.show', $operacion->id)
            ->with('success', 'Operación registrada correctamente con referencia: ' . $data['referencia']);
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
            
    } catch (Exception $e) {
        DB::rollback();
        \Log::error('Error al crear exportación: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Error al crear la operación: ' . $e->getMessage())
            ->withInput();
    }
}

    public function storetrafico_antesAntigravity(Request $request)
{
    try {
        // Validación adaptada a los nuevos campos
        $validated = $request->validate([
            // Campos obligatorios
            'fecha_registro' => 'required|date',
            'cliente_id' => 'required|exists:cliente,id',
            'importador_id' => 'required|exists:importadores,id',
            'nombre_producto' => 'required|string|max:255',
            'aduana_id' => 'required|exists:aduanas,id',
            'num_factura' => 'required|string|max:50',
            
            // Referencia manual - requerida y única
            'referencia' => 'required|string|max:50|unique:operaciones,referencia',
            
            // Campos opcionales (nullable)
            'bodega_id' => 'nullable|exists:bodegas,id',
            'num_thermo' => 'nullable|string|max:50',
            'codigo_alpha' => 'nullable|string|max:20',
        ], [
            // Mensajes personalizados
            'fecha_registro.required' => 'La fecha_registro de cruce es obligatoria.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'importador_id.required' => 'Debe seleccionar un importador.',
            'importador_id.exists' => 'El importador seleccionado no es válido.',
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'aduana_id.required' => 'Debe seleccionar una aduana.',
            'aduana_id.exists' => 'La aduana seleccionada no es válida.',
            'num_factura.required' => 'El número de factura es obligatorio.',
            'referencia.required' => 'La referencia es obligatoria.',
            'referencia.unique' => 'Esta referencia ya existe en el sistema.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
        ]);

        $data = $validated;

        // Asignar el usuario autenticado como documentador
        $data['usuario_registro_id'] = auth()->id();

        // Normalizar num_thermo si existe
        if (!empty($data['num_thermo'])) {
            $data['num_thermo'] = strtoupper(preg_replace('/\s+/', '-', $data['num_thermo']));
        }

        // Normalizar codigo_alpha si existe
        if (!empty($data['codigo_alpha'])) {
            $data['codigo_alpha'] = strtoupper(trim($data['codigo_alpha']));
        }

        // Normalizar referencia (opcional, según tus necesidades)
        if (!empty($data['referencia'])) {
            $data['referencia'] = strtoupper(trim($data['referencia']));
        }

        // Establecer valores por defecto para campos que no vienen del formulario
        $data['estado'] = 'pendiente';
        $data['patente_id'] = null; // Se asignará después por Documentación
        $data['pedimento_id'] = null;
        $data['num_doda'] = null;
        $data['modulacion'] = null;

        // ================================================
        //   🔵 CREACIÓN DEL REGISTRO
        // ================================================
        DB::beginTransaction();

        // Crear el registro de exportación con la referencia manual
        $operacion = Operacion::create($data);

        DB::commit();

        return redirect()->route('trafico.operaciones.show', $operacion->id)
            ->with('success', 'Operación registrada correctamente con referencia: ' . $data['referencia']);
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
            
    } catch (Exception $e) {
        DB::rollback();
        \Log::error('Error al crear exportación: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Error al crear la operación: ' . $e->getMessage())
            ->withInput();
    }
}

    public function storetrafico(Request $request)
    {
        try {
            // Validación — ya NO se pide 'referencia' al usuario
            $validated = $request->validate([
                // Campos obligatorios
                'fecha_registro' => 'required|date',
                'cliente_id' => 'required|exists:cliente,id',
                'importador_id' => 'required|exists:importadores,id',
                'nombre_producto' => 'required|string|max:255',
                'aduana_id' => 'required|exists:aduanas,id',
                'num_factura' => 'required|string|max:50',

                // Campos opcionales (nullable)
                'bodega_id' => 'nullable|exists:bodegas,id',
                'num_thermo' => 'nullable|string|max:50',
                'codigo_alpha' => 'nullable|string|max:20',
            ], [
                // Mensajes personalizados
                'fecha_registro.required' => 'La fecha_registro de cruce es obligatoria.',
                'cliente_id.required' => 'Debe seleccionar un cliente.',
                'cliente_id.exists' => 'El cliente seleccionado no es válido.',
                'importador_id.required' => 'Debe seleccionar un importador.',
                'importador_id.exists' => 'El importador seleccionado no es válido.',
                'nombre_producto.required' => 'El nombre del producto es obligatorio.',
                'aduana_id.required' => 'Debe seleccionar una aduana.',
                'aduana_id.exists' => 'La aduana seleccionada no es válida.',
                'num_factura.required' => 'El número de factura es obligatorio.',
                'bodega_id.exists' => 'La bodega seleccionada no es válida.',
            ]);

            $data = $validated;

            // Asignar el usuario autenticado como documentador
            $data['usuario_registro_id'] = auth()->id();

            // Normalizar num_thermo si existe
            if (!empty($data['num_thermo'])) {
                $data['num_thermo'] = strtoupper(preg_replace('/\s+/', '-', $data['num_thermo']));
            }

            // Normalizar codigo_alpha si existe
            if (!empty($data['codigo_alpha'])) {
                $data['codigo_alpha'] = strtoupper(trim($data['codigo_alpha']));
            }

            // Establecer valores por defecto para campos que no vienen del formulario
            $data['estado'] = 'pendiente';
            $data['patente_id'] = null; // Se asignará después por Documentación
            $data['pedimento_id'] = null;
            $data['num_doda'] = null;
            $data['modulacion'] = null;

            // ================================================
            //   🔵 GENERACIÓN AUTOMÁTICA DE REFERENCIA
            //   🔵 CREACIÓN DEL REGISTRO
            // ================================================
            DB::beginTransaction();

            // Generar referencia consecutiva automáticamente (con lockForUpdate)
            $data['referencia'] = Operacion::generarSiguienteReferencia();

            // Crear el registro de exportación
            $operacion = Operacion::create($data);

            DB::commit();

            // ================================================
            //   🟢 PREPARAR DATOS PARA EL MODAL DE CONFIRMACIÓN
            // ================================================
            // Cargar relaciones para mostrar nombres en el modal
            $operacion->load(['cliente', 'importador', 'bodega', 'aduana', 'patente']);

            $datosModal = [
                'referencia' => $operacion->referencia,
                'fecha_registro' => Carbon::parse($operacion->fecha_registro_registro)->format('m/d/Y'),
                'cliente' => $operacion->cliente->nombre_empresa ?? '',
                'importador' => $operacion->importador->nombre ?? '',
                'producto' => $operacion->nombre_producto ?? '',
                'bodega' => $operacion->bodega->nombre_bodega ?? '',
                'factura' => $operacion->num_factura ?? '',
                'aduana' => $operacion->aduana->nombre_aduana ?? '',
                'patente' => $operacion->patente->numero_patente ?? '',
                'pedimento' => $operacion->num_doda ?? '',
                'thermo' => $operacion->num_thermo ?? '',
                'alpha' => $operacion->codigo_alpha ?? '',
            ];

            return redirect()->route('trafico.nuevaexpo')
                ->with('success', 'Operación registrada correctamente con referencia: ' . $operacion->referencia)
                ->with('operacionCreada', $datosModal);

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        }
        catch (Exception $e) {
            DB::rollback();
            \Log::error('Error al crear exportación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la operación: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function storetraficoconDocumentos(Request $request)
    {

        try {
            $request->validate([
                'fecha_registro' => 'required|date',
                'cliente_id' => 'required|exists:cliente,id', // Tabla 'cliente'
                'importador_id' => 'required|exists:importadores,id',
                'nombre_producto' => 'required|string|max:255',
                'bodega_id' => 'required|exists:bodegas,id',
                'num_factura' => 'required|string|max:50',
                'aduana_id' => 'required|exists:aduanas,id',
                'patente_id' => 'required|exists:patentes,id',
                'pedimento_id' => 'nullable|exists:expedientes,id',
                'num_thermo' => 'nullable|string|max:50',
                'codigo_alpha' => 'nullable|string|max:20',
                'num_doda' => 'nullable|string|max:50',
                'modulacion' => 'nullable|string|max:100',
                'usuario_registro_id' => 'required|exists:users,id',

                //Validacion de documentos
                'archivos.*' => 'required|file||max:20480'



            ]);

            // Asignar el usuario autenticado como documentador si no se proporciona
            $data = $request->all();
            if (empty($data['usuario_registro_id'])) {
                $data['usuario_registro_id'] = auth()->id();
            }
            $data['estado'] = 'proceso';

            //Operacion::create($data);
            $operacion = Operacion::create($data);


            //2. Guardar Documentos Asociados
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    $path = $archivo->store('documentos');


                    Documento::create([
                        'operacion_id' => $operacion->id,
                        'nombre' => $archivo->getClientOriginalName(),
                        'ruta' => $path,
                        'tipo_documento' => 'otros',
                    ]);
                }
            }

            /*
            'pedimento_id'   => $expediente->id,
                        'operacion_id'  => $request->operacion_id, // 📌 Nuevo
                        'nombre_documento'=> $request->nombre_documento,
                        'ruta_archivo'    => $path,
                        'tipo_documento'  => $request->tipo_documento,
                        'fecha_registro_documento' => $request->fecha_registro_documento,
                        'observaciones'   => $request->observaciones,
            */



            return redirect()->route('operaciones.index')
                ->with('success', 'Exportación registrada correctamente.');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la exportación: ' . $e->getMessage())
                ->withInput();
        }

    }
    public function show($id)
    {
        try {
            $operacion = Operacion::findOrFail($id);
        return view('operaciones.show', compact('operacion'));
        }catch(Exception $e) {
            dd($e->getMessage());
        }
        
    }

    public function edit($id)
    {
        $operacion = Operacion::findOrFail($id);

        $clientes = Cliente::orderBy('nombre')->get();
        $importadores = Importador::orderBy('nombre')->get();
        $bodegas = Bodega::orderBy('nombre')->get();
        $aduanas = Aduana::orderBy('nombre')->get();
        $patentes = Patente::orderBy('numero')->get();
        $expedientes = Expediente::whereIn('estado', ['Abierto', 'En proceso'])
            ->orWhere('id', $operacion->pedimento_id) // Keep current even if closed
            ->orderBy('numero_pedimento')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('operaciones.edit', compact(
            'operacion',
            'clientes',
            'importadores',
            'bodegas',
            'aduanas',
            'patentes',
            'expedientes',
            'documentadores'
        ));
    }

    public function update(Request $request, $id)
    {
        $operacion = Operacion::findOrFail($id);

        $rules = [
            'fecha_registro' => 'nullable|date',
            'fecha_cruce_estimada' => 'required|date',
            'cliente_id' => 'required|exists:cliente,id',
            'importador_id' => 'required|exists:importadores,id',
            'nombre_producto' => 'required|string|max:500',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'num_factura' => 'required|string|max:50',
            'aduana_id' => 'required|exists:aduanas,id',
            'patente_id' => 'nullable|exists:patentes,id',
            'prioridad' => 'required|in:regular,media,alta,urgente',
            'estado' => 'required|in:pendiente,proceso,terminado',
            'num_thermo' => 'nullable|string|max:50',
            'codigo_alpha' => 'nullable|string|max:20',
            'num_doda' => 'nullable|string|max:50',
            'modulacion' => 'nullable|string|max:100',
            'pedimento_id' => [
                'nullable',
                'exists:expedientes,id',
                function ($attribute, $value, $fail) use ($operacion) {
                    if ($value == $operacion->expediente_id) return; // Permitir quedarse en el actual
                    $exp = Expediente::find($value);
                    if ($exp && $exp->estado === 'Cerrado') {
                        $fail('No se pueden relacionar operaciones a un pedimento cerrado.');
                    }
                },
            ],
            'observaciones' => 'nullable|string',
        ];

        $request->validate($rules);

        $data = $request->all();
        // Si no se envía fecha_registro, mantener la original
        if (empty($data['fecha_registro'])) {
            unset($data['fecha_registro']);
        }

        $operacion->update($data);

        return redirect()->route('operaciones.index')
            ->with('success', 'Operación actualizada correctamente.');
    }




    /*public function vistatrafico(){
        return view('trafico.index');
    }*/
    //Usuarios de Trafico.

    public function dashboardTrafico_OLD(Request $request)
    {
        
        // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
        if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
            $fecha_registroDesde = $request->fecha_registro_desde;
            $fecha_registroHasta = $request->fecha_registro_hasta;
        } else {
            // Por defecto mostrar solo los de hoy
            $fecha_registroDesde = now()->toDateString();
            $fecha_registroHasta = now()->toDateString();
        }

        //$hoy = now()->toDateString();

        //$query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente'])
        //    ->whereDate('fecha_registro', $hoy);
        $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos']) // 🔹 cargamos documentos
            //->whereDate('fecha_registro', $hoy);
            ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
            ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

        // filtros
        if ($request->filled('estado')) {
            $query->where('modulacion', $request->estado);
        }
        if ($request->filled('thermo')) {
            $query->where('num_thermo', 'like', "%{$request->thermo}%");
        }
        if ($request->filled('alpha')) {
            $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
        }
        if ($request->filled('doda')) {
            $query->where('num_doda', 'like', "%{$request->doda}%");
        }
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
            });
        }
        if ($request->filled('pedimento')) {
            $query->whereHas('expediente', function ($q) use ($request) {
                $q->where('num_pedimento', 'like', "%{$request->pedimento}%");
            });
        }

        $operaciones = $query->get();
        //$operaciones = $query->paginate(12);

        // agrupamos por número de thermo
        //$thermos = $operaciones->groupBy('num_thermo');
        // 🔥 NUEVO: Agrupar por thermo, pero separar los que NO tienen thermo
        $thermos = $operaciones->filter(function ($exp) {
            return !empty($exp->num_thermo); // Solo los que TIENEN thermo
        })->groupBy('num_thermo');

        // 🔥 NUEVO: Operaciones sin thermo (pendientes)
        $sinThermo = $operaciones->filter(function ($exp) {
            return empty($exp->num_thermo);
        });

        

        // estadísticas
        $stats = [
            'total' => $operaciones->count(),
            'frontera' => $operaciones->where('modulacion', 'frontera')->count(),
            'verde' => $operaciones->where('modulacion', 'verde')->count(),
            'rojo' => $operaciones->where('modulacion', 'rojo')->count(),
            'liberado' => $operaciones->where('modulacion', 'liberado')->count(),
            'transito' => $operaciones->where('modulacion', 'transito')->count(),
        ];



        // 🔹 Conteo para gráficos
        $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
            DB::raw("
            CASE 
                WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
                WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
                WHEN modulacion IS NULL THEN 'Pendiente de Modular'
                ELSE 'Pendiente de Modular'
            END AS estado_modulacion
        "),
            DB::raw('count(*) as total')
        )
            ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
            //->whereDate('fecha_registro', Carbon::today())
            ->whereNotNull('num_doda')
            ->groupBy('estado_modulacion')
            ->pluck('total', 'estado_modulacion');

        $verde = $modulacionCounts->get('verde', 0);
        $rojo = $modulacionCounts->get('rojo', 0);
        $amarillo = $modulacionCounts->get('Pendiente de Modular', 0);

        // Preparar arrays para Chart.js
        $modulacionOrder = [
            'verde' => '#28a745',
            'rojo' => '#dc3545',
            'Pendiente de Modular' => '#ffc107',
        ];

        $labelsModulacion = [];
        $dataModulacion = [];
        $backgroundColorsModulacion = [];
        $leyendaModulacion = '';

        // Verificar si la consulta devolvió algún resultado
        if ($modulacionCounts->isEmpty()) {
            $leyendaModulacion = 'No hay modulaciones pendientes hasta el momento.';
        } else {
            foreach ($modulacionOrder as $estado => $color) {
                if ($modulacionCounts->has($estado)) {
                    $labelsModulacion[] = ucfirst($estado);
                    $dataModulacion[] = $modulacionCounts->get($estado);
                    $backgroundColorsModulacion[] = $color;
                }
            }
        }

        $filtrosActuales = [
            'fecha_registro_desde' => $fecha_registroDesde,
            'fecha_registro_hasta' => $fecha_registroHasta
        ];






        return view('trafico.index', compact(
            'thermos',
            'stats',
            'verde',
            'rojo',
            //'Pendiente de Modular',//Agregamos para que este disponible en la vista
            'labelsModulacion',
            'dataModulacion',
            'backgroundColorsModulacion',
            'leyendaModulacion',
            'filtrosActuales',
            'sinThermo'
        ));
    }
    
        public function dashboardTrafico_old2(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        // Por defecto mostrar solo los de hoy
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    // Filtros
    if ($request->filled('estado')) {
        $query->where('modulacion', $request->estado);
    }
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('num_pedimento', 'like', "%{$request->pedimento}%");
        });
    }

    $operaciones = $query->get();

    // ============================================================
    // 🔥 SEPARAR REGISTROS COMPLETOS DE INCOMPLETOS
    // ============================================================
    
    // Registros INCOMPLETOS: Sin thermo, alpha o bodega
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id);
    });

    // Registros COMPLETOS: Con thermo, alpha y bodega
    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });

    // Agrupar COMPLETOS por número de thermo
    //$thermos = $registrosCompletos->groupBy('num_thermo');
    $thermos = $registrosCompletos->groupBy(function ($exp) {
            return $exp->num_thermo . '|' . $exp->codigo_alpha;
        });

    // ============================================================
    // ESTADÍSTICAS (solo de registros completos)
    // ============================================================
    $stats = [
        'total' => $operaciones->count(),
        'frontera' => $registrosCompletos->where('modulacion', 'frontera')->count(),
        'verde' => $registrosCompletos->where('modulacion', 'verde')->count(),
        'rojo' => $registrosCompletos->where('modulacion', 'rojo')->count(),
        'liberado' => $registrosCompletos->where('modulacion', 'liberado')->count(),
        'transito' => $registrosCompletos->where('modulacion', 'transito')->count(),
        'incompletos' => $registrosIncompletos->count(), // 🔥 NUEVO: contador de incompletos
    ];

    // ============================================================
    // GRÁFICOS DE MODULACIÓN
    // ============================================================
    $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
        DB::raw("
            CASE 
                WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
                WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
                WHEN modulacion IS NULL THEN 'Pendiente de Modular'
                ELSE 'Pendiente de Modular'
            END AS estado_modulacion
        "),
        DB::raw('count(*) as total')
    )
        ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
        ->whereNotNull('num_doda')
        ->groupBy('estado_modulacion')
        ->pluck('total', 'estado_modulacion');

    $verde = $modulacionCounts->get('verde', 0);
    $rojo = $modulacionCounts->get('rojo', 0);
    $amarillo = $modulacionCounts->get('Pendiente de Modular', 0);

    // Preparar arrays para Chart.js
    $modulacionOrder = [
        'verde' => '#28a745',
        'rojo' => '#dc3545',
        'Pendiente de Modular' => '#ffc107',
    ];

    $labelsModulacion = [];
    $dataModulacion = [];
    $backgroundColorsModulacion = [];
    $leyendaModulacion = '';

    // Verificar si la consulta devolvió algún resultado
    if ($modulacionCounts->isEmpty()) {
        $leyendaModulacion = 'No hay modulaciones pendientes hasta el momento.';
    } else {
        foreach ($modulacionOrder as $estado => $color) {
            if ($modulacionCounts->has($estado)) {
                $labelsModulacion[] = ucfirst($estado);
                $dataModulacion[] = $modulacionCounts->get($estado);
                $backgroundColorsModulacion[] = $color;
            }
        }
    }

    $filtrosActuales = [
        'fecha_registro_desde' => $fecha_registroDesde,
        'fecha_registro_hasta' => $fecha_registroHasta
    ];

    return view('trafico.index', compact(
        'thermos',
        'stats',
        'verde',
        'rojo',
        'labelsModulacion',
        'dataModulacion',
        'backgroundColorsModulacion',
        'leyendaModulacion',
        'filtrosActuales',
        'registrosIncompletos' // 🔥 NUEVO: pasamos los registros incompletos a la vista
    ));
}

    public function dashboardTrafico_old3(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        // Por defecto mostrar solo los de hoy
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    // Filtros
    if ($request->filled('estado')) {
        $query->where('modulacion', $request->estado);
    }
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('num_pedimento', 'like', "%{$request->pedimento}%");
        });
    }

    $operaciones = $query->get();

    // ============================================================
    // 🔥 SEPARAR REGISTROS COMPLETOS DE INCOMPLETOS
    // ============================================================
    
    // Registros INCOMPLETOS: Sin thermo, alpha, bodega O sin expediente válido
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return empty($exp->num_thermo) 
            || empty($exp->codigo_alpha) 
            || empty($exp->bodega_id)
            || is_null($exp->expediente)  // 🔥 NUEVO: Sin expediente
            || empty($exp->expediente->numero_pedimento); // 🔥 NUEVO: Sin pedimento
    });

    // Registros COMPLETOS: Con thermo, alpha, bodega Y expediente válido
    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) 
            && !empty($exp->codigo_alpha) 
            && !empty($exp->bodega_id)
            && !is_null($exp->expediente) // 🔥 NUEVO: Tiene expediente
            && !empty($exp->expediente->numero_pedimento); // 🔥 NUEVO: Tiene pedimento
    });

    // Agrupar COMPLETOS por número de thermo y código alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // ============================================================
    // ESTADÍSTICAS (solo de registros completos)
    // ============================================================
    $stats = [
        'total' => $operaciones->count(),
        'frontera' => $registrosCompletos->where('modulacion', 'frontera')->count(),
        'verde' => $registrosCompletos->where('modulacion', 'verde')->count(),
        'rojo' => $registrosCompletos->where('modulacion', 'rojo')->count(),
        'liberado' => $registrosCompletos->where('modulacion', 'liberado')->count(),
        'transito' => $registrosCompletos->where('modulacion', 'transito')->count(),
        'incompletos' => $registrosIncompletos->count(),
    ];

    // ============================================================
    // GRÁFICOS DE MODULACIÓN
    // ============================================================
    $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
        DB::raw("
            CASE 
                WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
                WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
                WHEN modulacion IS NULL THEN 'Pendiente de Modular'
                ELSE 'Pendiente de Modular'
            END AS estado_modulacion
        "),
        DB::raw('count(*) as total')
    )
        ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
        ->whereNotNull('num_doda')
        ->groupBy('estado_modulacion')
        ->pluck('total', 'estado_modulacion');

    $verde = $modulacionCounts->get('verde', 0);
    $rojo = $modulacionCounts->get('rojo', 0);
    $amarillo = $modulacionCounts->get('Pendiente de Modular', 0);

    // Preparar arrays para Chart.js
    $modulacionOrder = [
        'verde' => '#28a745',
        'rojo' => '#dc3545',
        'Pendiente de Modular' => '#ffc107',
    ];

    $labelsModulacion = [];
    $dataModulacion = [];
    $backgroundColorsModulacion = [];
    $leyendaModulacion = '';

    // Verificar si la consulta devolvió algún resultado
    if ($modulacionCounts->isEmpty()) {
        $leyendaModulacion = 'No hay modulaciones pendientes hasta el momento.';
    } else {
        foreach ($modulacionOrder as $estado => $color) {
            if ($modulacionCounts->has($estado)) {
                $labelsModulacion[] = ucfirst($estado);
                $dataModulacion[] = $modulacionCounts->get($estado);
                $backgroundColorsModulacion[] = $color;
            }
        }
    }

    $filtrosActuales = [
        'fecha_registro_desde' => $fecha_registroDesde,
        'fecha_registro_hasta' => $fecha_registroHasta
    ];

    return view('trafico.index', compact(
        'thermos',
        'stats',
        'verde',
        'rojo',
        'labelsModulacion',
        'dataModulacion',
        'backgroundColorsModulacion',
        'leyendaModulacion',
        'filtrosActuales',
        'registrosIncompletos'
    ));
}

    public function dashboardTrafico_antesajax(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        // Por defecto mostrar solo los de hoy
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    // Filtros
    if ($request->filled('estado')) {
        $query->where('modulacion', $request->estado);
    }
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('num_pedimento', 'like', "%{$request->pedimento}%");
        });
    }

    $operaciones = $query->get();

    // ============================================================
    // 🔥 SEPARAR REGISTROS COMPLETOS DE INCOMPLETOS
    // ============================================================
    
    // Registros INCOMPLETOS: SOLO sin thermo o alpha (bodega es opcional)
    // El expediente/pedimento puede estar vacío y aún así se agrupan
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return empty($exp->num_thermo) 
            || empty($exp->codigo_alpha);
        // 🔥 NO validamos bodega ni expediente aquí
    });

    // Registros COMPLETOS: Con thermo y alpha (lo demás puede faltar)
    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) 
            && !empty($exp->codigo_alpha);
        // 🔥 Se agrupan aunque falte bodega, expediente o pedimento
    });

    // Agrupar COMPLETOS por número de thermo y código alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // ============================================================
    // ESTADÍSTICAS (solo de registros completos)
    // ============================================================
    $stats = [
        'total' => $operaciones->count(),
        'frontera' => $registrosCompletos->where('modulacion', 'frontera')->count(),
        'verde' => $registrosCompletos->where('modulacion', 'verde')->count(),
        'rojo' => $registrosCompletos->where('modulacion', 'rojo')->count(),
        'liberado' => $registrosCompletos->where('modulacion', 'liberado')->count(),
        'transito' => $registrosCompletos->where('modulacion', 'transito')->count(),
        'incompletos' => $registrosIncompletos->count(),
    ];

    // ============================================================
    // GRÁFICOS DE MODULACIÓN
    // ============================================================
    $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
        DB::raw("
            CASE 
                WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
                WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
                WHEN modulacion IS NULL THEN 'Pendiente de Modular'
                ELSE 'Pendiente de Modular'
            END AS estado_modulacion
        "),
        DB::raw('count(*) as total')
    )
        ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
        ->whereNotNull('num_doda')
        ->groupBy('estado_modulacion')
        ->pluck('total', 'estado_modulacion');

    $verde = $modulacionCounts->get('verde', 0);
    $rojo = $modulacionCounts->get('rojo', 0);
    $amarillo = $modulacionCounts->get('Pendiente de Modular', 0);

    // Preparar arrays para Chart.js
    $modulacionOrder = [
        'verde' => '#28a745',
        'rojo' => '#dc3545',
        'Pendiente de Modular' => '#ffc107',
    ];

    $labelsModulacion = [];
    $dataModulacion = [];
    $backgroundColorsModulacion = [];
    $leyendaModulacion = '';

    // Verificar si la consulta devolvió algún resultado
    if ($modulacionCounts->isEmpty()) {
        $leyendaModulacion = 'No hay modulaciones pendientes hasta el momento.';
    } else {
        foreach ($modulacionOrder as $estado => $color) {
            if ($modulacionCounts->has($estado)) {
                $labelsModulacion[] = ucfirst($estado);
                $dataModulacion[] = $modulacionCounts->get($estado);
                $backgroundColorsModulacion[] = $color;
            }
        }
    }

    $filtrosActuales = [
        'fecha_registro_desde' => $fecha_registroDesde,
        'fecha_registro_hasta' => $fecha_registroHasta
    ];

    return view('trafico.index', compact(
        'thermos',
        'stats',
        'verde',
        'rojo',
        'labelsModulacion',
        'dataModulacion',
        'backgroundColorsModulacion',
        'leyendaModulacion',
        'filtrosActuales',
        'registrosIncompletos'
    ));
}

    public function dashboardTrafico_NofuncionaBienporaduana(Request $request)
    {
        // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
        if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
            $fecha_registroDesde = $request->fecha_registro_desde;
            $fecha_registroHasta = $request->fecha_registro_hasta;
        }
        else {
            // Por defecto mostrar solo los de hoy
            $fecha_registroDesde = now()->toDateString();
            $fecha_registroHasta = now()->toDateString();
        }

        $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador'])
            ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
            ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('modulacion', $request->estado);
        }
        if ($request->filled('thermo')) {
            $query->where('num_thermo', 'like', "%{$request->thermo}%");
        }
        if ($request->filled('alpha')) {
            $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
        }
        if ($request->filled('doda')) {
            $query->where('num_doda', 'like', "%{$request->doda}%");
        }
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
            });
        }
        if ($request->filled('pedimento')) {
            $query->whereHas('expediente', function ($q) use ($request) {
                $q->where('num_pedimento', 'like', "%{$request->pedimento}%");
            });
        }

        // Ya no filtramos por traffic_acknowledged aquí para poder ver el historial
        $operaciones = $query->get();

        // ============================================================
        // 🔥 SEPARAR REGISTROS
        // ============================================================

        // Incompletos (como antes)
        $registrosIncompletos = $operaciones->filter(function ($exp) {
            return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id))
            && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
        });

        // Completos (incluyendo los enterados para el historial)
        $registrosCompletos = $operaciones->filter(function ($exp) {
            return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
        });

        // Agrupar por thermo|alpha para la vista
        $gruposPorCamion = $registrosCompletos->groupBy(function ($exp) {
            return $exp->num_thermo . '|' . $exp->codigo_alpha;
        });

        // Filtrar los grupos que ya fueron enterados (solo para estadísticas activas si se desea, 
        // pero para progreso global es mejor contar todos los del día)
        $camionesModuladosVerde = 0;
        $camionesModuladosRojo = 0;

        foreach ($gruposPorCamion as $grupo) {
            $first = $grupo->first();
            $mod = strtoupper($first->modulacion ?? '');

            if ($mod === 'DESADUANAMIENTO LIBRE') {
                $camionesModuladosVerde++;
            }
            elseif (str_contains($mod, 'RECONOCIMIENTO')) {
                $camionesModuladosRojo++;
            }
        }

        $stats = [
            'total' => $gruposPorCamion->count(),
            'verde' => $camionesModuladosVerde,
            'rojo' => $camionesModuladosRojo,
            'incompletos' => $registrosIncompletos->count(),
        ];

        // Pasar variables a la vista
        $thermos = $gruposPorCamion;
        $verde = $camionesModuladosVerde;
        $rojo = $camionesModuladosRojo;

        // Gráficos de modulación (mantener para tendencias si es necesario)
        $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
            DB::raw("
            CASE 
                WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
                WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
                WHEN modulacion IS NULL THEN 'Pendiente de Modular'
                ELSE 'Pendiente de Modular'
            END AS estado_modulacion
        "),
            DB::raw('count(*) as total')
        )
            ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
            ->whereNotNull('num_doda')
            ->groupBy('estado_modulacion')
            ->pluck('total', 'estado_modulacion');

        $verde = $modulacionCounts->get('verde', 0);
        $rojo = $modulacionCounts->get('rojo', 0);
        $amarillo = $modulacionCounts->get('Pendiente de Modular', 0);

        // Preparar arrays para Chart.js
        $modulacionOrder = [
            'verde' => '#28a745',
            'rojo' => '#dc3545',
            'Pendiente de Modular' => '#ffc107',
        ];

        $labelsModulacion = [];
        $dataModulacion = [];
        $backgroundColorsModulacion = [];
        $leyendaModulacion = '';

        // Verificar si la consulta devolvió algún resultado
        if ($modulacionCounts->isEmpty()) {
            $leyendaModulacion = 'No hay modulaciones pendientes hasta el momento.';
        }
        else {
            foreach ($modulacionOrder as $estado => $color) {
                if ($modulacionCounts->has($estado)) {
                    $labelsModulacion[] = ucfirst($estado);
                    $dataModulacion[] = $modulacionCounts->get($estado);
                    $backgroundColorsModulacion[] = $color;
                }
            }
        }

        $filtrosActuales = [
            'fecha_registro_desde' => $fecha_registroDesde,
            'fecha_registro_hasta' => $fecha_registroHasta
        ];

        return view('trafico.index', compact(
            'thermos',
            'stats',
            'verde',
            'rojo',
            'labelsModulacion',
            'dataModulacion',
            'backgroundColorsModulacion',
            'leyendaModulacion',
            'filtrosActuales',
            'registrosIncompletos' // 🔥 NUEVO: pasamos los registros incompletos a la vista
        ));
    }
    
    public function dashboardTrafico_antigravityFail(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador', 'conceptosAdicionales.operacion'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    // 🔥 AGREGAR TODOS LOS FILTROS ADICIONALES
    if ($request->filled('estado')) {
        if ($request->estado == '0') {
            // Sin modulación
            $query->whereNull('modulacion');
        } else {
            $query->where('modulacion', $request->estado);
        }
    }
    
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', "%{$request->pedimento}%");
        });
    }

    $operaciones = $query->get();

    // Separar incompletos de completos
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id)) 
               && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
    });

    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });

    // Agrupar por thermo|alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // Estadísticas (basadas en camiones agrupados)
    $camionesModuladosVerde = 0;
    $camionesModuladosRojo = 0;
    
    foreach ($thermos as $grupo) {
        $first = $grupo->first();
        $mod = strtoupper($first->modulacion ?? '');
        if ($mod === 'DESADUANAMIENTO LIBRE') {
            $camionesModuladosVerde++;
        } elseif (str_contains($mod, 'RECONOCIMIENTO')) {
            $camionesModuladosRojo++;
        }
    }

    $stats = [
        'total' => $thermos->count(),
        'verde' => $camionesModuladosVerde,
        'rojo' => $camionesModuladosRojo,
        'incompletos' => $registrosIncompletos->count(),
    ];

    $verde = $camionesModuladosVerde;
    $rojo = $camionesModuladosRojo;

    // Datos para la gráfica de modulación
    $dataModulacion = [$verde, $rojo];
    $labelsModulacion = ['Verde (Libre)', 'Rojo (Reconocimiento)'];
    $backgroundColorsModulacion = ['#10b981', '#ef4444'];
    $leyendaModulacion = ($verde + $rojo) == 0;

    return view('trafico.index', compact(
        'thermos',
        'stats',
        'verde',
        'rojo',
        'registrosIncompletos',
        'dataModulacion',
        'labelsModulacion',
        'backgroundColorsModulacion',
        'leyendaModulacion'
    ));
}
public function dashboardTrafico(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador', 'conceptosAdicionales.operacion'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    // 🔥 AGREGAR TODOS LOS FILTROS ADICIONALES
    if ($request->filled('estado')) {
        if ($request->estado == '0') {
            // Sin modulación
            $query->whereNull('modulacion');
        } else {
            $query->where('modulacion', $request->estado);
        }
    }
    
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', "%{$request->pedimento}%");
        });
    }

    $operaciones = $query->get();

    // Separar incompletos de completos
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id)) 
               && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
    });

    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });

    // Agrupar por thermo|alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // Estadísticas (basadas en camiones agrupados)
    $camionesModuladosVerde = 0;
    $camionesModuladosRojo = 0;
    
    foreach ($thermos as $grupo) {
        $first = $grupo->first();
        $mod = strtoupper($first->modulacion ?? '');
        if ($mod === 'DESADUANAMIENTO LIBRE') {
            $camionesModuladosVerde++;
        } elseif (str_contains($mod, 'RECONOCIMIENTO')) {
            $camionesModuladosRojo++;
        }
    }

    $stats = [
        'total' => $thermos->count(),
        'verde' => $camionesModuladosVerde,
        'rojo' => $camionesModuladosRojo,
        'incompletos' => $registrosIncompletos->count(),
    ];

    $verde = $camionesModuladosVerde;
    $rojo = $camionesModuladosRojo;

    // Datos para la gráfica de modulación
    $dataModulacion = [$verde, $rojo];
    $labelsModulacion = ['Verde (Libre)', 'Rojo (Reconocimiento)'];
    $backgroundColorsModulacion = ['#10b981', '#ef4444'];
    $leyendaModulacion = ($verde + $rojo) == 0;

    return view('trafico.index', compact(
        'thermos',
        'stats',
        'verde',
        'rojo',
        'registrosIncompletos',
        'dataModulacion',
        'labelsModulacion',
        'backgroundColorsModulacion',
        'leyendaModulacion'
    ));
}
    // 🔥 NUEVO MÉTODO AJAX PARA ACTUALIZACIONES EN TIEMPO REAL

    public function dashboardTraficoAjax_OK(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador', 'conceptosAdicionales.operacion'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

    $operaciones = $query->get();

    // Separar incompletos de completos
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id)) 
               && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
    });

    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });

    // Agrupar por thermo|alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // Estadísticas (solo activas)
    $activasParaStats = $registrosCompletos->filter(fn($e) => !$e->traffic_acknowledged);
    $stats = [
        'total' => $activasParaStats->count() + $registrosIncompletos->count(),
        'frontera' => $activasParaStats->where('modulacion', 'frontera')->count(),
        'verde' => $activasParaStats->where('modulacion', 'verde')->count(),
        'rojo' => $activasParaStats->where('modulacion', 'rojo')->count(),
        'incompletos' => $registrosIncompletos->count(),
    ];

    // Gráficos de modulación
    $modulacionCounts = Operacion::where('estado', '!=', 'cancelada')->select(
        DB::raw("
        CASE 
            WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 'verde'
            WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 'rojo'
            WHEN modulacion IS NULL THEN 'Pendiente de Modular'
            ELSE 'Pendiente de Modular'
        END AS estado_modulacion
    "),
        DB::raw('count(*) as total')
    )
        ->whereBetween('fecha_registro', [$fecha_registroDesde, $fecha_registroHasta])
        ->whereNotNull('num_doda')
        ->groupBy('estado_modulacion')
        ->pluck('total', 'estado_modulacion');

    $verde = $modulacionCounts->get('verde', 0);
    $rojo = $modulacionCounts->get('rojo', 0);

    // Preparar datos por aduana
    $byAduana = [];
    foreach ($thermos as $grupoKey => $registros) {
        [$thermo, $alpha] = explode('|', $grupoKey);
        $first = $registros->first();
        $adu = $first->aduana->nombre_aduana ?? 'SIN ADUANA';
        
        if (!isset($byAduana[$adu])) {
            $byAduana[$adu] = [
                'activas' => [],
                'historial' => []
            ];
        }

        $estadoOperacion = strtolower($first->estado ?? 'pendiente');
        $modulacion = $first->modulacion;
        $isAcknowledged = (bool)$first->traffic_acknowledged;
        
        $estadoMostrar = ucfirst($estadoOperacion);
        $color = match($estadoOperacion) {
            'terminado' => 'green',
            'proceso', 'en proceso' => 'yellow',
            default => 'muted'
        };
        
        if ($estadoOperacion === 'terminado' && $modulacion) {
            $modulacionUpper = strtoupper($modulacion);
            if ($modulacionUpper === 'DESADUANAMIENTO LIBRE' || str_contains($modulacionUpper, 'RECONOCIMIENTO')) {
                $estadoMostrar = ucfirst($modulacion);
                $color = match(true) {
                    $modulacionUpper === 'DESADUANAMIENTO LIBRE' => 'green',
                    str_contains($modulacionUpper, 'RECONOCIMIENTO') => 'red',
                    default => 'yellow'
                };
            }
        }

        $facturas = $registros->pluck('num_factura')->take(3)->implode(', ');
        if($registros->count() > 3) $facturas .= '...';

        $opData = [
            'id' => $first->id,
            'thermo' => $thermo,
            'alpha' => $alpha,
            'modalId' => str_replace([' ', '-'], '_', $thermo . '_' . $alpha),
            'cliente' => $first->cliente->nombre_empresa ?? 'N/A',
            'facturas' => $facturas,
            'estado' => $estadoMostrar,
            'color' => $color,
            'num_facturas' => $registros->count(),
            'modulacion' => $modulacion
        ];

        if ($isAcknowledged) {
            $byAduana[$adu]['historial'][] = $opData;
        } else {
            $byAduana[$adu]['activas'][] = $opData;
        }
    }

    $incompletosData = $registrosIncompletos->map(function($inc) {
        return [
            'id' => $inc->id,
            'referencia' => $inc->referencia,
            'cliente' => $inc->cliente->nombre_empresa ?? 'N/A',
            'num_factura' => $inc->num_factura
        ];
    })->values();

    return response()->json([
        'success' => true,
        'stats' => $stats,
        'verde' => $verde,
        'rojo' => $rojo,
        'byAduana' => $byAduana,
        'incompletos' => $incompletosData,
        'timestamp' => now()->toDateTimeString()
    ]);
}
public function dashboardTraficoAjax_antigravityFail(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador', 'conceptosAdicionales.operacion'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

        // 🔥 AGREGAR TODOS LOS FILTROS ADICIONALES
    if ($request->filled('estado')) {
        if ($request->estado == '0') {
            // Sin modulación
            $query->whereNull('modulacion');
        } else {
            $query->where('modulacion', $request->estado);
        }
    }
    
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', "%{$request->pedimento}%");
        });
    }



    $operaciones = $query->get();

    // Separar incompletos de completos
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id)) 
               && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
    });

    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });
    // Agrupar por thermo|alpha
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha;
    });

    // Estadísticas (basadas en camiones agrupados)
    $camionesModuladosVerde = 0;
    $camionesModuladosRojo = 0;
    
    foreach ($thermos as $grupo) {
        $first = $grupo->first();
        $mod = strtoupper($first->modulacion ?? '');
        if ($mod === 'DESADUANAMIENTO LIBRE') {
            $camionesModuladosVerde++;
        } elseif (str_contains($mod, 'RECONOCIMIENTO')) {
            $camionesModuladosRojo++;
        }
    }

    $stats = [
        'total' => $thermos->count(),
        'verde' => $camionesModuladosVerde,
        'rojo' => $camionesModuladosRojo,
        'incompletos' => $registrosIncompletos->count(),
    ];

    $verde = $camionesModuladosVerde;
    $rojo = $camionesModuladosRojo;

    // Preparar datos por aduana
    $byAduana = [];
    foreach ($thermos as $grupoKey => $registros) {
        [$thermo, $alpha] = explode('|', $grupoKey);
        $first = $registros->first();
        $adu = $first->aduana->nombre_aduana ?? 'SIN ADUANA';
        
        if (!isset($byAduana[$adu])) {
            $byAduana[$adu] = [
                'activas' => [],
                'historial' => []
            ];
        }

        $estadoOperacion = strtolower($first->estado ?? 'pendiente');
        $modulacion = $first->modulacion;
        $isAcknowledged = (bool)$first->traffic_acknowledged;
        
        $estadoMostrar = ucfirst($estadoOperacion);
        $color = match($estadoOperacion) {
            'terminado' => 'green',
            'proceso', 'en proceso' => 'yellow',
            default => 'muted'
        };
        
        if ($estadoOperacion === 'terminado' && $modulacion) {
            $modulacionUpper = strtoupper($modulacion);
            if ($modulacionUpper === 'DESADUANAMIENTO LIBRE' || str_contains($modulacionUpper, 'RECONOCIMIENTO')) {
                $estadoMostrar = ucfirst($modulacion);
                $color = match(true) {
                    $modulacionUpper === 'DESADUANAMIENTO LIBRE' => 'green',
                    str_contains($modulacionUpper, 'RECONOCIMIENTO') => 'red',
                    default => 'yellow'
                };
            }
        }

        $facturas = $registros->pluck('num_factura')->take(3)->implode(', ');
        if($registros->count() > 3) $facturas .= '...';

        $opData = [
            'id' => $first->id,
            'thermo' => $thermo,
            'alpha' => $alpha,
            'modalId' => str_replace([' ', '-'], '_', $thermo . '_' . $alpha),
            'cliente' => $first->cliente->nombre_empresa ?? 'N/A',
            'facturas' => $facturas,
            'estado' => $estadoMostrar,
            'color' => $color,
            'num_facturas' => $registros->count(),
            'modulacion' => $modulacion
        ];

        if ($isAcknowledged) {
            $byAduana[$adu]['historial'][] = $opData;
        } else {
            $byAduana[$adu]['activas'][] = $opData;
        }
    }

    $incompletosData = $registrosIncompletos->map(function($inc) {
        return [
            'id' => $inc->id,
            'referencia' => $inc->referencia,
            'cliente' => $inc->cliente->nombre_empresa ?? 'N/A',
            'num_factura' => $inc->num_factura
        ];
    })->values();

    return response()->json([
        'success' => true,
        'stats' => $stats,
        'verde' => $verde,
        'rojo' => $rojo,
        'byAduana' => $byAduana,
        'incompletos' => $incompletosData,
        'timestamp' => now()->toDateTimeString()
    ]);
}
public function dashboardTraficoAjax(Request $request)
{
    // Filtro por rango de fechas - si no se especifica, usar hoy por defecto
    if ($request->filled('fecha_registro_desde') && $request->filled('fecha_registro_hasta')) {
        $fecha_registroDesde = $request->fecha_registro_desde;
        $fecha_registroHasta = $request->fecha_registro_hasta;
    } else {
        $fecha_registroDesde = now()->toDateString();
        $fecha_registroHasta = now()->toDateString();
    }

    $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente.documentos', 'bodega', 'importador', 'conceptosAdicionales.operacion'])
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta);

        // 🔥 AGREGAR TODOS LOS FILTROS ADICIONALES
    if ($request->filled('estado')) {
        if ($request->estado == '0') {
            // Sin modulación
            $query->whereNull('modulacion');
        } else {
            $query->where('modulacion', $request->estado);
        }
    }
    
    if ($request->filled('thermo')) {
        $query->where('num_thermo', 'like', "%{$request->thermo}%");
    }
    
    if ($request->filled('alpha')) {
        $query->where('codigo_alpha', 'like', "%{$request->alpha}%");
    }
    
    if ($request->filled('doda')) {
        $query->where('num_doda', 'like', "%{$request->doda}%");
    }
    
    if ($request->filled('cliente')) {
        $query->whereHas('cliente', function ($q) use ($request) {
            $q->where('nombre_empresa', 'like', "%{$request->cliente}%");
        });
    }
    
    if ($request->filled('pedimento')) {
        $query->whereHas('expediente', function ($q) use ($request) {
            $q->where('numero_pedimento', 'like', "%{$request->pedimento}%");
        });
    }



    $operaciones = $query->get();

    // Separar incompletos de completos
    $registrosIncompletos = $operaciones->filter(function ($exp) {
        return (empty($exp->num_thermo) || empty($exp->codigo_alpha) || empty($exp->bodega_id)) 
               && ($exp->traffic_acknowledged == 0 || is_null($exp->traffic_acknowledged));
    });

    $registrosCompletos = $operaciones->filter(function ($exp) {
        return !empty($exp->num_thermo) && !empty($exp->codigo_alpha) && !empty($exp->bodega_id);
    });
    // Agrupar por thermo|alpha|fecha_registro (PY_FIX)
    $thermos = $registrosCompletos->groupBy(function ($exp) {
        return $exp->num_thermo . '|' . $exp->codigo_alpha . '|' . substr((string)$exp->fecha_registro, 0, 10);
    });

    // Estadísticas (basadas en camiones agrupados)
    $camionesModuladosVerde = 0;
    $camionesModuladosRojo = 0;
    
    foreach ($thermos as $grupo) {
        $first = $grupo->first();
        $mod = strtoupper($first->modulacion ?? '');
        if ($mod === 'DESADUANAMIENTO LIBRE') {
            $camionesModuladosVerde++;
        } elseif (str_contains($mod, 'RECONOCIMIENTO')) {
            $camionesModuladosRojo++;
        }
    }

    $stats = [
        'total' => $thermos->count(),
        'verde' => $camionesModuladosVerde,
        'rojo' => $camionesModuladosRojo,
        'incompletos' => $registrosIncompletos->count(),
    ];

    $verde = $camionesModuladosVerde;
    $rojo = $camionesModuladosRojo;

    // Preparar datos por aduana
    $byAduana = [];
    foreach ($thermos as $grupoKey => $registros) {
        $parts = explode('|', $grupoKey);
        $thermo = $parts[0] ?? '';
        $alpha = $parts[1] ?? '';
        $fecha_registro = $parts[2] ?? date('Y-m-d');
        $first = $registros->first();
        $adu = $first->aduana->nombre_aduana ?? 'SIN ADUANA';
        
        if (!isset($byAduana[$adu])) {
            $byAduana[$adu] = [
                'activas' => [],
                'historial' => []
            ];
        }

        $estadoOperacion = strtolower($first->estado ?? 'pendiente');
        $modulacion = $first->modulacion;
        $isAcknowledged = (bool)$first->traffic_acknowledged;
        
        $estadoMostrar = ucfirst($estadoOperacion);
        $color = match($estadoOperacion) {
            'terminado' => 'green',
            'proceso', 'en proceso' => 'yellow',
            default => 'muted'
        };
        
        if ($estadoOperacion === 'terminado' && $modulacion) {
            $modulacionUpper = strtoupper($modulacion);
            if ($modulacionUpper === 'DESADUANAMIENTO LIBRE' || str_contains($modulacionUpper, 'RECONOCIMIENTO')) {
                $estadoMostrar = ucfirst($modulacion);
                $color = match(true) {
                    $modulacionUpper === 'DESADUANAMIENTO LIBRE' => 'green',
                    str_contains($modulacionUpper, 'RECONOCIMIENTO') => 'red',
                    default => 'yellow'
                };
            }
        }

        $facturas = $registros->pluck('num_factura')->take(3)->implode(', ');
        if($registros->count() > 3) $facturas .= '...';

        $opData = [
            'id' => $first->id,
            'thermo' => $thermo,
            'alpha' => $alpha,
            'modalId' => str_replace([' ', '-', ':'], '_', $thermo . '_' . $alpha . '_' . $fecha_registro),
            'cliente' => $first->cliente->nombre_empresa ?? 'N/A',
            'facturas' => $facturas,
            'estado' => $estadoMostrar,
            'color' => $color,
            'num_facturas' => $registros->count(),
            'modulacion' => $modulacion,
            'fecha_registro' => $fecha_registro
        ];

        if ($isAcknowledged) {
            $byAduana[$adu]['historial'][] = $opData;
        } else {
            $byAduana[$adu]['activas'][] = $opData;
        }
    }

    $incompletosData = $registrosIncompletos->map(function($inc) {
        return [
            'id' => $inc->id,
            'referencia' => $inc->referencia,
            'cliente' => $inc->cliente->nombre_empresa ?? 'N/A',
            'num_factura' => $inc->num_factura
        ];
    })->values();

    return response()->json([
        'success' => true,
        'stats' => $stats,
        'verde' => $verde,
        'rojo' => $rojo,
        'byAduana' => $byAduana,
        'incompletos' => $incompletosData,
        'timestamp' => now()->toDateTimeString()
    ]);
}

 public function acknowledgeOp(Request $request, $id)
    {
        // If it's a specific Operacion ID
        $op = Operacion::find($id);
        if ($op) {
            $op->traffic_acknowledged = 1;
            $op->save();
            return response()->json(['success' => true]);
        }

        // If we want to acknowledge by Thermo/Alpha grouping, we might receive a grouping key
        // For now, simpler to assume ID or user clicks one by one. 
        // Or if the UI sends the ID of the 'primary' operation.
        return response()->json(['error' => 'Not found'], 404);
    }
public function modalDetalle_antesdeconceptoadicional($thermo, $alpha)
{
    try {
        //code...
        $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

        $registros = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente', 'bodega', 'documentos', 'conceptosAdicionales'])
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7)) // Últimos 7 días
        ->get();
    
    if ($registros->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'No se encontraron registros']);
    }
    
    $first = $registros->first();
    $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
    $color = match(strtoupper($first->modulacion ?? '')) {
        'DESADUANAMIENTO LIBRE' => 'green',
        'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
        default => 'muted'
    };
    
    // Conceptos del camión
    $conceptosCamion = collect();
    foreach ($registros as $reg) {
        $conceptosCamion = $conceptosCamion->merge(
            $reg->conceptosAdicionales->where('ambito', 'camion')
        );
    }
    $conceptosCamion = $conceptosCamion->unique('id');
    
    $html = view('trafico.modals.detalle', compact('registros', 'first', 'estado', 'color', 'conceptosCamion', 'thermo', 'alpha'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);

    } catch (\Throwable $th) {
        //throw $th;
    }
    
}

public function modalDetalle($thermo, $alpha)
{
    try {
        $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        $fecha_registroDesde = request('fecha_registro_desde') ?: now()->toDateString();
        $fecha_registroHasta = request('fecha_registro_hasta') ?: now()->toDateString();

        $query = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'aduana', 'expediente', 'bodega', 'documentos', 'conceptosAdicionales.operacion'])
            ->where('num_thermo', $thermo)
            ->where('codigo_alpha', $alpha);

        $registros = (clone $query)->whereDate('fecha_registro', '>=', $fecha_registroDesde)
            ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
            ->get();

        // Si no hay en el rango exacto, buscamos lo más reciente de ese camión
        if ($registros->isEmpty()) {
            $registros = $query->orderBy('fecha_registro', 'desc')->take(10)->get();
        }

        if ($registros->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron registros']);
        }

        $first = $registros->first();
        $fecha_registro = substr((string)$first->fecha_registro, 0, 10); // Extraemos la fecha_registro real del registro
        
        $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
        $color = match(strtoupper($first->modulacion ?? '')) {
            'DESADUANAMIENTO LIBRE' => 'green',
            'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
            default => 'muted'
        };

        $conceptosCamion = collect(); // Para cargar los conceptos ya existentes en la vista
        foreach ($registros as $reg) {
            if ($reg->conceptosAdicionales) {
                $conceptosCamion = $conceptosCamion->merge($reg->conceptosAdicionales->where('ambito', 'camion'));
            }
        }
        $conceptosCamion = $conceptosCamion->unique('id');

        // IMPORTANTE: Verifica si tu vista es 'trafico.modals.detalle' o solo 'modals.detalle'
        $html = view('trafico.modals.detalle', compact('registros', 'first', 'estado', 'color', 'conceptosCamion', 'thermo', 'alpha', 'fecha_registro'))->render();

        return response()->json(['success' => true, 'html' => $html]);

    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $th->getMessage()]);
    }
}

public function modalUbicacion($thermo, $alpha)
{
    $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $first = Operacion::where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7))
        ->first();
    
    if (!$first) {
        return response()->json(['success' => false]);
    }
    
    $html = view('trafico.modals.ubicacion', compact('first', 'thermo'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);
}

public function modalModulacion_old($thermo, $alpha)
{
    $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $registros = Operacion::where('estado', '!=', 'cancelada')->with('cliente')
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7))
        ->get();
    
    if ($registros->isEmpty()) {
        return response()->json(['success' => false]);
    }
    
    $first = $registros->first();
    $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
    $color = match(strtoupper($first->modulacion ?? '')) {
        'DESADUANAMIENTO LIBRE' => 'green',
        'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
        default => 'muted'
    };
    
    $html = view('trafico.modals.modulacion', compact('registros', 'first', 'estado', 'color'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);
}
public function modalModulacion_old2($thermo, $alpha)
{
    $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $registros = Operacion::where('estado', '!=', 'cancelada')->with('cliente')
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7))
        ->get();
    
    if ($registros->isEmpty()) {
        return response()->json(['success' => false]);
    }
    
    $first = $registros->first();
    $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
    $color = match(strtoupper($first->modulacion ?? '')) {
        'DESADUANAMIENTO LIBRE' => 'green',
        'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
        default => 'muted'
    };
    
    $html = view('trafico.modals.modulacion', compact('registros', 'first', 'estado', 'color'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);
}
public function modalModulacion($thermo, $alpha)
{
    $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $registros = Operacion::where('estado', '!=', 'cancelada')->with('cliente')
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7))
        ->get();
    
    if ($registros->isEmpty()) {
        return response()->json(['success' => false]);
    }
    
    $first = $registros->first();
    $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
    $color = match(strtoupper($first->modulacion ?? '')) {
        'DESADUANAMIENTO LIBRE' => 'green',
        'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
        default => 'muted'
    };
    
    $html = view('trafico.modals.modulacion', compact('registros', 'first', 'estado', 'color'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);
}

public function printModulacion($thermo, $alpha)
{
    $thermo = urldecode($thermo);
    $alpha  = urldecode($alpha);

    $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
    $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $registros = Operacion::where('estado', '!=', 'cancelada')->with(['cliente', 'expediente', 'patente'])
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        ->get();

    if ($registros->isEmpty()) {
        abort(404, 'No se encontraron registros para este economico.');
    }

    $first  = $registros->first();
    $estado = ucfirst($first->modulacion ?? 'Sin Modulacion');
    $color  = match(strtoupper($first->modulacion ?? '')) {
        'DESADUANAMIENTO LIBRE' => 'green',
        'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
        default => 'muted'
    };

    return view('trafico.modals.modulacion_print', compact('registros', 'first', 'estado', 'color'));
}

public function modalConceptos_nofuncionaconceptoadicional($thermo, $alpha)
{
     $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        // 🔥 Usar el mismo rango de fechas que el dashboard principal
        $fecha_registroDesde = request('fecha_registro_desde', now()->toDateString());
        $fecha_registroHasta = request('fecha_registro_hasta', now()->toDateString());

    $registros = Operacion::where('estado', '!=', 'cancelada')->with('cliente')
        ->where('num_thermo', $thermo)
        ->where('codigo_alpha', $alpha)
        ->whereDate('fecha_registro', '>=', $fecha_registroDesde)
        ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
        //->whereDate('fecha_registro', '>=', now()->subDays(7))
        ->get();
    
    if ($registros->isEmpty()) {
        return response()->json(['success' => false]);
    }
    
    $html = view('trafico.modals.conceptos', compact('registros', 'thermo', 'alpha'))->render();
    
    return response()->json(['success' => true, 'html' => $html]);
}

public function modalConceptos($thermo, $alpha)
{
    try {
        $thermo = urldecode($thermo);
        $alpha = urldecode($alpha);
        
        $fecha_registroDesde = request('fecha_registro_desde') ?: now()->toDateString();
        $fecha_registroHasta = request('fecha_registro_hasta') ?: now()->toDateString();

        $query = Operacion::where('estado', '!=', 'cancelada')->with('cliente')
            ->where('num_thermo', $thermo)
            ->where('codigo_alpha', $alpha);

        $registros = (clone $query)->whereDate('fecha_registro', '>=', $fecha_registroDesde)
            ->whereDate('fecha_registro', '<=', $fecha_registroHasta)
            ->get();
        
        // Si no hay exactamente ese día, traemos las facturas recientes
        // para que puedas elegir una y asignarle el concepto
        if ($registros->isEmpty()) {
            $registros = $query->orderBy('fecha_registro', 'desc')->take(20)->get();
        }

        if ($registros->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay facturas para el económico ' . $thermo]);
        }
    
        // IMPORTANTE: Verifica si tu vista es 'trafico.modals.conceptos' o solo 'modals.conceptos'
        $html = view('trafico.modals.conceptos', compact('registros', 'thermo', 'alpha'))->render();
    
        return response()->json(['success' => true, 'html' => $html]);

    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $th->getMessage()]);
    }
}



    public function nuevaoperacion_old() //Desde usuario de trafico
    {
        $clientes = Cliente::orderBy('nombre_empresa')->get();
        $importadores = Importador::orderBy('nombre')->get();
        $bodegas = Bodega::orderBy('nombre_bodega')->get();
        $aduanas = Aduana::orderBy('nombre_aduana')->get();
        $patentes = Patente::orderBy('numero_patente')->get();
        $expedientes = Expediente::orderBy('id')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();
        $referenciaTentativa = $this->generarReferenciaTentativa();

        return view('operaciones.create_trafico', compact(
            'clientes',
            'importadores',
            'bodegas',
            'aduanas',
            'patentes',
            'expedientes',
            'documentadores',
            'referenciaTentativa'
        ));
    }
    
    public function nuevaoperacion() //Desde usuario de trafico

    {
        $clientes = Cliente::orderBy('nombre_empresa')->get();
        $importadores = Importador::orderBy('nombre')->get();
        $bodegas = Bodega::orderBy('nombre_bodega')->get();
        $aduanas = Aduana::orderBy('nombre_aduana')->get();
        $patentes = Patente::orderBy('numero_patente')->get();
        $expedientes = Expediente::orderBy('id')->get();
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('operaciones.create_trafico', compact(
            'clientes',
            'importadores',
            'bodegas',
            'aduanas',
            'patentes',
            'expedientes',
            'documentadores'
        ));
    }

    //Funciones para asignar operacion a usuario documentador.
    public function showAsignarForm($id)
    {
        $operacion = Operacion::with('cliente')->findOrFail($id);
        $documentadores = User::where('role', 'documentador')->orderBy('name')->get();

        return view('operaciones.asignar', compact('operacion', 'documentadores'));
    }

    public function asignar(Request $request, $id)
    {
        try {
            $request->validate([
                'usuario_cierre_id' => 'required|exists:users,id',
                'prioridad' => 'required|in:regular,media,urgente',
                'comentarios' => 'nullable|string',
            ]);

            $operacion = Operacion::findOrFail($id);
            $operacion->usuario_cierre_id = $request->usuario_cierre_id;
            $operacion->prioridad = $request->prioridad;
            //$operacion->estado = 'proceso';
            $operacion->save();

            return redirect()->route('operaciones.index')
                ->with('success', 'Exportación asignada correctamente al documentador.');

        } catch (Exception $e) {
            dd($e->getMessage());
            //return redirect()->back()
            //->with('error', 'Error al asignar la exportación: ' . $e->getMessage());
        }
    }







    //Seccion de Dashboard de documentadores.
    // Métodos exclusivos para el dashboard del documentador
    public function dashboardDocumentador()
    {
        $userId = auth()->id();
        $hoy = now()->format('Y-m-d');

        // Obtener operaciones asignadas al usuario
        $operaciones = Operacion::with(['cliente'])
            ->where('usuario_cierre_id', $userId)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_registro', $hoy)
                    ->orWhereIn('estado', ['pendiente', 'proceso']);
            })
            ->orderByRaw("
            CASE 
                WHEN prioridad = 'urgente' THEN 1
                WHEN prioridad = 'media' THEN 2  
                WHEN prioridad = 'regular' THEN 3
                ELSE 4
            END
        ")
            ->orderBy('fecha_registro', 'desc')
            ->get();

        // Estadísticas
        $stats = $this->getDocumentadorStats($userId);

        return view('documentador.dashboard', compact('operaciones', 'stats'));
    }

    private function getDocumentadorStats($userId)
    {
        $hoy = now()->format('Y-m-d');
        $ayer = now()->subDay()->format('Y-m-d');

        $totalHoy = Operacion::where('usuario_cierre_id', $userId)
            ->whereDate('fecha_registro', $hoy)
            ->where('estado', '!=', 'cancelada')
            ->count();

        $completadosHoy = Operacion::where('usuario_cierre_id', $userId)
            ->whereDate('fecha_registro', $hoy)
            ->where('estado', 'completado')
            ->count();

        $pendientes = Operacion::where('usuario_cierre_id', $userId)
            ->whereIn('estado', ['pendiente', 'proceso'])
            ->where('estado', '!=', 'cancelada')
            ->count();

        $efectividad = $totalHoy > 0 ? round(($completadosHoy / $totalHoy) * 100) : 0;

        return [
            'total_hoy' => $totalHoy,
            'completados_hoy' => $completadosHoy,
            'pendientes' => $pendientes,
            'efectividad' => $efectividad,
            'ranking' => $this->getDocumentadorRanking($userId)
        ];
    }

    private function getDocumentadorRanking($userId)
    {
        // Lógica para calcular ranking (puedes ajustar según tus necesidades)
        $inicioSemana = now()->startOfWeek()->format('Y-m-d');
        $finSemana = now()->endOfWeek()->format('Y-m-d');

        $completadosSemana = Operacion::where('usuario_cierre_id', $userId)
            ->whereBetween('fecha_registro', [$inicioSemana, $finSemana])
            ->where('estado', 'completado')
            ->where('estado', '!=', 'cancelada')
            ->count();

        // Esto es un ejemplo, deberías ajustar la lógica de ranking real
        return [
            'posicion' => 3,
            'total' => $completadosSemana,
            'variacion' => '+5'
        ];
    }

    public function trabajarOperacion($id)
    {
        $operacion = Operacion::findOrFail($id);

        // Verificar que pertenece al usuario logueado
        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para trabajar en esta exportación');
        }

        return view('documentador.trabajar', compact('operacion'));
    }

    public function updateEstado(Request $request, $id)
    {
        $operacion = Operacion::findOrFail($id);

        if ($operacion->usuario_cierre_id != auth()->id()) {
            abort(403, 'No tienes permisos para actualizar esta exportación');
        }

        $request->validate([
            'estado' => 'required|in:pendiente,proceso,completado',
            'comentarios' => 'nullable|string'
        ]);

        $operacion->estado = $request->estado;

        if ($request->comentarios) {
            // Aquí puedes guardar los comentarios en tu sistema
        }

        $operacion->save();

        return redirect()->route('documentador.dashboard')
            ->with('success', 'Estado actualizado correctamente');
    }


    //Consultar la modulacion, actualizar la tabla y notificar por correo.

    public function actualizarModulacion(Operacion $operacion)
    {
        if (!$operacion->num_doda) {
            return response()->json(['error' => 'Exportación sin número de DODA'], 400);
        }

        $api = env('PECEM_API_URL');
        if (!$api) {
            return response()->json(['error' => 'No está configurada la URL de PECEM'], 500);
        }

        try {
            $res = Http::withOptions([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ])->get($api . $operacion->num_doda);

            if ($res->status() !== 200) {
                return response()->json(['error' => 'Error en la consulta a PECEM. Código HTTP: ' . $res->status()], 500);
            }

            $html = $res->body();
            preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
            $status_txt = last($matches[1]) ?? null;

            if ($status_txt === null) {
                $status_txt = 'DODA no presentado al Mecanismo de Selección Automatizado';
                $status_code = 3;
            } else {
                $status_code = match ($status_txt) {
                    'DESADUANAMIENTO LIBRE' => 0,
                    'RECONOCIMIENTO ADUANERO' => 1,
                    'RECONOCIMIENTO ADUANERO CONCLUIDO' => 2,
                    default => 3,
                };
            }

            // 🔹 Guardar en DB
            $operacion->update([
                'modulacion' => $status_txt
            ]);

            // 🔹 Notificar al cliente SOLO si cambió el estatus
            /*if ($operacion->wasChanged('modulacion')) {
                $cliente = $operacion->cliente; // relación Cliente
                if ($cliente && $cliente->email) {
                    \Illuminate\Support\Facades\Mail::to($cliente->email)->send(new OperacionStatusMail($operacion));
                }
            }*/

            return response()->json([
                'status_code' => $status_code,
                'status_txt' => $status_txt,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción: ' . $e->getMessage()], 500);
        }
    }
    
    public function modulaciones()
    {
        $operaciones = Operacion::with('cliente')
            ->where('estado', '!=', 'cancelada')
            ->select('id', 'cliente_id', 'modulacion', 'estado', 'prioridad', 'num_factura', 'nombre_producto')
            ->get();

        return response()->json($operaciones);
    }

    public function checkModulacion(Request $request)
    {
        $request->validate([
            'petition_integration_number' => 'required|string',
        ]);

        // Buscar la exportación por el número DODA
        $operacion = Operacion::where('num_doda', $request->petition_integration_number)
            ->where('estado', '!=', 'cancelada')
            ->first();

        if (!$operacion) {
            return response()->json(['error' => 'Exportación no encontrada'], 404);
        }

        $api = env('PECEM_API_URL');
        if (!$api) {
            return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
        }

        try {
            $res = Http::withOptions([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ])->get($api . $request->petition_integration_number);

            if ($res->status() !== 200) {
                return response()->json(['error' => 'Error en la consulta a PECEM. Código HTTP: ' . $res->status()], 500);
            }

            $html = $res->body();
            preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
            $status_txt = last($matches[1]) ?? null;

            // Si no se encontró texto válido, asignamos el valor por defecto manualmente
            if ($status_txt === null) {
                $status_txt = 'DODA no presentado al Mecanismo de Selección Automatizado';
                $status_code = 3;
            } else {
                // Si hay texto, usamos el match como antes
                $status_code = match ($status_txt) {
                    'DESADUANAMIENTO LIBRE' => 0,
                    'RECONOCIMIENTO ADUANERO' => 1,
                    'RECONOCIMIENTO ADUANERO CONCLUIDO' => 2,
                    'DODA no presentado al Mecanismo de Selección Automatizado' => 3,
                    default => 3,
                };
            }

            // Actualizar la modulación en la base de datos
            $operacion->modulacion = $status_txt;
            $operacion->save();

            return response()->json([
                'status_code' => $status_code,
                'status_txt' => $status_txt,
                'operacion_id' => $operacion->id
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción: ' . $e->getMessage()], 500);
        }
    }

   public function check_FUNCIONALPortal()//Funcionando al 12 septiembre 2025
    {


        //dd('Entrando a la opcion de actualizar las modulaciones');
        $api = env('PECEM_API_URL');
        if (!$api) {
            return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
        }

        try {
            // 🔹 Obtenemos todos los registros que necesitan actualización
            $operaciones = Operacion::whereNotNull('num_doda')
                ->where('estado', '!=', 'cancelada')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion','DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();
                //dd($operaciones);

            //dd($operaciones);
            if ($operaciones->count() == 0) {
                //dd('No hay nada que consultar');
                return redirect()->route('operaciones.index')
                    ->with('success', 'No hay modulaciones por actualizar');

            } else {
                //dd('Hoy datos para buscar');
                //Agrupamos por num_doda para evitar consultar la API varias veces para el mismo doda.
                $dodasUnicos = $operaciones->pluck('num_doda')->unique();


                foreach ($dodasUnicos as $doda) {
                    //$doda = $operacion->num_doda;
                    //dd($doda);
                    $res = Http::withOptions([
                        'verify' => false,
                        'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
                    ])->get($api . $doda);
                    //dd($res);
                    if ($res->status() !== 200) {
                        return back()->withErrors(['error' => 'Error en la consulta a PECEM. Código HTTP: ' . $res->status()]);
                    }

                    $html = $res->body();
                    //dd($html);
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? null;
                    //dd($doda,$res,$html,$status_txt);
                    $status_code = match ($status_txt) {
                        'DESADUANAMIENTO LIBRE' => 0,
                        'RECONOCIMIENTO ADUANERO' => 1,
                        'RECONOCIMIENTO ADUANERO CONCLUIDO' => 2,
                        'DODA no presentado al Mecanismo de Selección Automatizado' => 3,
                        default => 3,
                    };

                    if ($status_txt === null) {
                        $status_txt = 'DODA no presentado al Mecanismo de Selección Automatizado';
                        //continue;
                    }
                    if ($status_txt === false) {
                        $status_txt = 'DODA no presentado al Mecanismo de Selección Automatizado';
                        //continue;
                    }

                    // 🔹 Actualizamos la exportación correspondiente
                    $operacion = Operacion::where('num_doda', $doda)
                        ->where('estado', '!=', 'cancelada')
                        ->first();

                    if ($operacion) {
                        //$operacion->estado = $status_code; // O el campo que uses para el estatus
                        $operacion->modulacion = $status_txt; // Opcional si quieres guardar también el texto
                        $operacion->save();

                        usleep(100000);



                    } else {
                        return back()->withErrors(['error' => 'No se encontró ninguna exportación con ese DODA']);
                    }
                    //return back();
                    //return back()->with('success', 'Estatus actualizado correctamente.');



                }
                return back();

            }





        } catch (Exception $e) {
            return redirect()->route('operaciones.index')
                ->with('error', 'No se actualizaron modulaciones');
            //return back()->withErrors(['error' => 'Excepción: ' . $e->getMessage()]);
        }
    }

    public function check_Demo()
{
    $api = env('PECEM_API_URL');
    if (!$api) {
        return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
    }

    try {
        $operaciones = Operacion::whereNotNull('num_doda')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', '0');
            })
            
            ->get();
            
        if ($operaciones->isEmpty()) {
            return redirect()->route('operaciones.index')
                ->with('success', 'No hay modulaciones por actualizar');
        }

        $dodasUnicos = $operaciones->pluck('num_doda')->unique();

        foreach ($dodasUnicos as $doda) {
            $res = Http::withOptions([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ])->get($api . $doda);

            if ($res->status() !== 200) {
                // si falla un doda, seguimos con los demás
                continue;
            }

            $html = $res->body();
            preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
            $status_txt = last($matches[1]) ?? null;

            // si no encontramos nada, saltar este DODA
            if ($status_txt === null) {
                continue;
            }

            $operacion = Operacion::where('num_doda', $doda)
                        ->where('estado', '!=', 'cancelada')
                        ->first();
            if ($operacion) {
                $operacion->modulacion = $status_txt;
                $operacion->save();
                usleep(100000); // pausa opcional
            }
        }

        return back()->with('success', 'Modulaciones actualizadas correctamente.');
    } catch (Exception $e) {
        return redirect()->route('operaciones.index')
            ->with('error', 'No se actualizaron modulaciones: ' . $e->getMessage());
    }
}

public function check()
{
    try{
        $api = env('PECEM_API_URL');
    if (!$api) {
        return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
    }

    $operaciones = Operacion::whereNotNull('num_doda')
        ->where(function ($query) {
            $query->whereNull('modulacion')
                ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                ->orWhere('modulacion', '0')
                ->orWhere('modulacion','DODA no presentado al Mecanismo de Selección Automatizado');
        })
        ->get();

    if ($operaciones->isEmpty()) {
        return redirect()->route('operaciones.index')
            ->with('success', 'No hay modulaciones por actualizar');
    }

    $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();

    $client = new Client([
        'verify' => false,
        'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
        'headers' => ['User-Agent' => 'Mozilla/5.0'],
    ]);

    $requests = function ($dodas) use ($client, $api) {
        foreach ($dodas as $doda) {
            yield $doda => function() use ($client, $api, $doda) {
                return $client->getAsync($api . $doda);
            };
        }
    };

    $pool = new Pool($client, $requests($dodasUnicos), [
        'concurrency' => 5, // máximo 5 peticiones a la vez
        'fulfilled' => function ($response, $doda) {
                    $html = (string) $response->getBody();
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                    $operacion = Operacion::where('num_doda', $doda)
                        ->where('estado', '!=', 'cancelada')
                        ->first();
                    if ($operacion) {
                        $operacion->modulacion = $status_txt;
                        $operacion->save();

                        if ($status_txt == 'RECONOCIMIENTO ADUANERO' ||
    $status_txt == 'DESADUANAMIENTO LIBRE' ||
    $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO') {
                            // 📧 Enviar correo al cliente
                            $cliente = $operacion->cliente; // suponiendo relación
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    //'carta_porte' => $operacion->complemento_carta_porte ? 'SI' : 'NO',
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                \Mail::to($cliente->correo_contacto_principal)
                                    ->send(new EstatusModulacionMail($cliente, $datosTramite, $status_txt));
                            }
                        }
                    }
                },
        'rejected' => function ($reason, $doda) {
            \Log::error("Error al consultar DODA {$doda}: {$reason}");
        },
    ]);

    $promise = $pool->promise();
    $promise->wait();

    return back()->with('success','Modulaciones actualizadas');
    }
    catch(Exception $e){
        return back()->withErrors(['error' => 'No fue posible actualizar las modulaciones']);
    }
}
    public function checktrafico()
    {
        try {
            $api = env('PECEM_API_URL');
            if (!$api) {
                return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
            }

            $operaciones = Operacion::whereNotNull('num_doda')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();
                //dd($operaciones);

            if ($operaciones->isEmpty()) {
                return redirect()->route('trafico.index')
                    ->with('success', 'No hay modulaciones por actualizar');
            }

            $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();

            //dd($dodasUnicos);
            $client = new Client([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $requests = function ($dodas) use ($client, $api) {
                foreach ($dodas as $doda) {
                    yield $doda => function () use ($client, $api, $doda) {
                        return $client->getAsync($api . $doda);
                    };
                }
            };

            $pool = new Pool($client, $requests($dodasUnicos), [
                'concurrency' => 5, // máximo 5 peticiones a la vez
                'fulfilled' => function ($response, $doda) {
                    $html = (string) $response->getBody();
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                    Operacion::where('num_doda', $doda)//->first();
                        ->update(['modulacion'=> $status_txt]);
                    
                    $operaciones = Operacion::where('num_doda', $doda)->get();
                    foreach ($operaciones as $operacion) {
                        //$operacion->modulacion = $status_txt;
                        //$operacion->save();

                        /*
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                        */
                        if (
                            $status_txt == 'DESADUANAMIENTO LIBRE' ||
                            $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                        ) {
                            // 🔔 Notificar al módulo de tráfico
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            // 📧 Enviar correo al cliente
                            $cliente = $operacion->cliente; // suponiendo relación
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    //'carta_porte' => $operacion->complemento_carta_porte ? 'SI' : 'NO',
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                //\Mail::to($cliente->correo_contacto_principal)}
                            //\Mail::to('operaciones@crosspoint.com.mx')
                            //->bcc(['sistemas@crosspoint.com.mx', 'trafico3@crosspoint.com.mx','operacionesreynosa@crosspoint.com.mx','trafico2@crosspoint.com.mx','practicante@crosspoint.com.mx','ventas2@crosspoint.com.mx'])
                            //->cc('alejandro@crosspoint.com.mx')
                            //    ->send(new EstatusModulacionMail($cliente, $datosTramite, $status_txt));
                            EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                            }
                        }else{
                            if (
                            $status_txt == 'RECONOCIMIENTO ADUANERO' 
                        ) {
                            // 📧 Enviar correo al cliente
                            $status_txt='TRAMITE EN PROCESO DE REVISION';
                            // 🔔 Notificar al módulo de tráfico
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            $cliente = $operacion->cliente; // suponiendo relación
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    //'carta_porte' => $operacion->complemento_carta_porte ? 'SI' : 'NO',
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];
                                EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);

                                //\Mail::to($cliente->correo_contacto_principal)}
                            //\Mail::to('operaciones@crosspoint.com.mx')
                            //->bcc(['sistemas@crosspoint.com.mx', 'trafico3@crosspoint.com.mx','operacionesreynosa@crosspoint.com.mx','trafico2@crosspoint.com.mx'])
                            //->cc('alejandro@crosspoint.com.mx')
                            //    ->send(new EstatusModulacionMail($cliente, $datosTramite, $status_txt));
                            
                                //EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                            }
                        }
                        }


                    }
                },
                'rejected' => function ($reason, $doda) {
                    \Log::error("Error al consultar DODA {$doda}: {$reason}");
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            return back()->with('success', 'Modulaciones actualizadas');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'No fue posible actualizar las modulaciones']);
        }
    }
    public function checkTraficoBot_Malo(Request $request)
{
    $token = $request->query('token');
    
    if ($token !== env('CHECK_TRAFICO_TOKEN')) {
        \Log::warning("Intento de acceso no autorizado a checkTraficoBot");
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        \Log::info("Bot iniciado - checkTraficoBot");
        
        $api = env('PECEM_API_URL');
        if (!$api) {
            \Log::error("URL de PECEM no configurada");
            return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
        }

        $operaciones = Operacion::whereNotNull('num_doda')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->get();

        if ($operaciones->isEmpty()) {
            \Log::info("Bot ejecutado - No hay modulaciones por actualizar");
            return response()->json([
                'success' => true,
                'message' => 'No hay modulaciones por actualizar'
            ], 200);
        }

        $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
        \Log::info("Bot procesará " . count($dodasUnicos) . " DODAs únicos");

        $client = new Client([
            'verify' => false,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);

        $requests = function ($dodas) use ($client, $api) {
            foreach ($dodas as $doda) {
                yield $doda => function () use ($client, $api, $doda) {
                    return $client->getAsync($api . $doda);
                };
            }
        };

        $pool = new Pool($client, $requests($dodasUnicos), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $doda) {
                $html = (string) $response->getBody();
                preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                \Log::info("Bot procesando DODA {$doda} con status: {$status_txt}");

                Operacion::where('num_doda', $doda)
                    ->update(['modulacion'=> $status_txt]);
                
                $operaciones = Operacion::where('num_doda', $doda)->get();
                \Log::info("Operaciones encontradas para DODA {$doda}: " . $operaciones->count());
                
                foreach ($operaciones as $operacion) {
                    if (
                        $status_txt == 'DESADUANAMIENTO LIBRE' ||
                        $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ) {
                        $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                        $cliente = $operacion->cliente;
                        
                        if ($cliente && $cliente->correo_contacto_principal) {
                            $datosTramite = [
                                'factura' => $operacion->num_factura,
                                'nombre_producto' => $operacion->nombre_producto,
                                'no_economico' => $operacion->num_thermo,
                                'no_alpha' => $operacion->codigo_alpha
                            ];

                            try{
                                EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                \Log::info("Job despachado exitosamente para cliente {$cliente->id}, DODA: {$doda}, status: {$status_txt}");
                            }catch(Exception $e){
                                \Log::error("Error al despachar job para cliente {$cliente->id}: " . $e->getMessage());
                            }
                        } else {
                            \Log::warning("No se envió correo - Cliente o correo faltante para exportación ID: {$operacion->id}");
                        }
                    } else {
                        if ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                            $status_txt = 'TRAMITE EN PROCESO DE REVISION';
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            $cliente = $operacion->cliente;
                            
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                try{
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                    \Log::info("Job despachado exitosamente para cliente {$cliente->id}, DODA: {$doda}, status: {$status_txt}");
                                }catch(Exception $e){
                                    \Log::error("Error al despachar job para cliente {$cliente->id}: " . $e->getMessage());
                                }
                            } else {
                                \Log::warning("No se envió correo - Cliente o correo faltante para exportación ID: {$operacion->id}");
                            }
                        }
                    }
                }
            },
            'rejected' => function ($reason, $doda) {
                \Log::error("Error al consultar DODA {$doda}: {$reason}");
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        \Log::info("Bot finalizó correctamente - Modulaciones actualizadas");
        return response()->json([
            'success' => true,
            'message' => 'Modulaciones actualizadas',
            'procesadas' => count($dodasUnicos)
        ], 200);
        
    } catch (Exception $e) {
        \Log::error("Error en checkTraficoBot: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'error' => 'No fue posible actualizar las modulaciones',
            'details' => $e->getMessage()
        ], 500);
    }
}
    
    public function checkTraficoBot_RevisionporLogs(Request $request)
{
    $token = $request->query('token');
    $executionId = uniqid('bot_', true); // ID único para esta ejecución
    
    // Log en archivo específico
    $logChannel = 'trafico_bot';
    \Log::channel($logChannel)->info("=== INICIO EJECUCIÓN BOT ===", [
        'execution_id' => $executionId,
        'timestamp' => now()->toDateTimeString(),
        'ip' => $request->ip()
    ]);
    
    if ($token !== env('CHECK_TRAFICO_TOKEN')) {
        \Log::channel($logChannel)->warning("Intento de acceso no autorizado", [
            'execution_id' => $executionId,
            'ip' => $request->ip(),
            'token_provided' => substr($token, 0, 5) . '***'
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        // Paso 1: Validar configuración
        \Log::channel($logChannel)->info("PASO 1: Validando configuración", [
            'execution_id' => $executionId
        ]);
        
        $api = env('PECEM_API_URL');
        if (!$api) {
            \Log::channel($logChannel)->error("URL de PECEM no configurada", [
                'execution_id' => $executionId
            ]);
            return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
        }
        
        \Log::channel($logChannel)->info("Configuración OK", [
            'execution_id' => $executionId,
            'api_url' => $api
        ]);

        // Paso 2: Obtener operaciones
        \Log::channel($logChannel)->info("PASO 2: Consultando operaciones pendientes", [
            'execution_id' => $executionId
        ]);
        
        $operaciones = Operacion::whereNotNull('num_doda')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->get();

        \Log::channel($logChannel)->info("Operaciones encontradas", [
            'execution_id' => $executionId,
            'total_operaciones' => $operaciones->count()
        ]);

        if ($operaciones->isEmpty()) {
            \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Sin datos) ===", [
                'execution_id' => $executionId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'No hay modulaciones por actualizar'
            ], 200);
        }

        $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
        \Log::channel($logChannel)->info("PASO 3: DODAs únicos identificados", [
            'execution_id' => $executionId,
            'total_dodas' => count($dodasUnicos),
            'dodas' => $dodasUnicos
        ]);

        // Paso 4: Configurar cliente HTTP
        \Log::channel($logChannel)->info("PASO 4: Configurando cliente HTTP", [
            'execution_id' => $executionId
        ]);
        
        $client = new Client([
            'verify' => false,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);

        $requests = function ($dodas) use ($client, $api) {
            foreach ($dodas as $doda) {
                yield $doda => function () use ($client, $api, $doda) {
                    return $client->getAsync($api . $doda);
                };
            }
        };

        // Paso 5: Procesar requests en pool
        \Log::channel($logChannel)->info("PASO 5: Iniciando procesamiento de DODAs", [
            'execution_id' => $executionId,
            'concurrency' => 5
        ]);

        $pool = new Pool($client, $requests($dodasUnicos), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->info("Request exitoso para DODA", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_code' => $response->getStatusCode()
                ]);
                
                $html = (string) $response->getBody();
                preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                \Log::channel($logChannel)->info("Status extraído del HTML", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_txt' => $status_txt,
                    'matches_found' => count($matches[1])
                ]);

                // Actualizar modulación
                $updated = Operacion::where('num_doda', $doda)
                    ->update(['modulacion'=> $status_txt]);
                
                \Log::channel($logChannel)->info("Modulación actualizada en BD", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'registros_actualizados' => $updated
                ]);
                
                $operaciones = Operacion::where('num_doda', $doda)->get();
                \Log::channel($logChannel)->info("Operaciones obtenidas para procesamiento", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'cantidad' => $operaciones->count(),
                    'ids' => $operaciones->pluck('id')->toArray()
                ]);
                
                foreach ($operaciones as $index => $operacion) {
                    \Log::channel($logChannel)->info("Procesando exportación individual", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'operacion_id' => $operacion->id,
                        'index' => $index + 1,
                        'total' => $operaciones->count(),
                        'num_factura' => $operacion->num_factura ?? 'N/A'
                    ]);
                    
                    // Verificar relación con cliente ANTES de usarlo
                    $cliente = null;
                    try {
                        $cliente = $operacion->cliente;
                        \Log::channel($logChannel)->info("Relación cliente cargada", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'cliente_exists' => !is_null($cliente),
                            'cliente_id' => $operacion->cliente_id ?? 'NULL',
                            'cliente_correo' => $cliente->correo_contacto_principal ?? 'NULL'
                        ]);
                    } catch (\Exception $e) {
                        \Log::channel($logChannel)->error("ERROR al cargar relación cliente", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                    
                    if (
                        $status_txt == 'DESADUANAMIENTO LIBRE' ||
                        $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ) {
                        \Log::channel($logChannel)->info("Status requiere notificación (Libre/Concluido)", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'status_txt' => $status_txt
                        ]);
                        
                        try {
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id
                            ]);
                        } catch (\Exception $e) {
                            \Log::channel($logChannel)->error("Error al enviar notificación", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                        
                        if ($cliente && $cliente->correo_contacto_principal) {
                            $datosTramite = [
                                'factura' => $operacion->num_factura,
                                'nombre_producto' => $operacion->nombre_producto,
                                'no_economico' => $operacion->num_thermo,
                                'no_alpha' => $operacion->codigo_alpha
                            ];

                            \Log::channel($logChannel)->info("Preparando dispatch de Job", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'cliente_id' => $cliente->id,
                                'correo' => $cliente->correo_contacto_principal,
                                'datos_tramite' => $datosTramite
                            ]);

                            try{
                                EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'doda' => $doda,
                                    'status' => $status_txt
                                ]);
                            }catch(Exception $e){
                                \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        } else {
                            \Log::channel($logChannel)->warning("No se envió correo - Validación falló", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'cliente_exists' => !is_null($cliente),
                                'cliente_id' => $cliente->id ?? 'NULL',
                                'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false,
                                'correo' => $cliente->correo_contacto_principal ?? 'NULL'
                            ]);
                        }
                    } else {
                        if ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                            $status_txt_modificado = 'TRAMITE EN PROCESO DE REVISION';
                            
                            \Log::channel($logChannel)->info("Status modificado para notificación", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_original' => $status_txt,
                                'status_modificado' => $status_txt_modificado
                            ]);
                            
                            try {
                                $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt_modificado);
                                \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id
                                ]);
                            } catch (\Exception $e) {
                                \Log::channel($logChannel)->error("Error al enviar notificación", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                            
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                \Log::channel($logChannel)->info("Preparando dispatch de Job (Revisión)", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'correo' => $cliente->correo_contacto_principal
                                ]);

                                try{
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt_modificado);
                                    \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'doda' => $doda,
                                        'status' => $status_txt_modificado
                                    ]);
                                }catch(Exception $e){
                                    \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->warning("No se envió correo - Validación falló (Revisión)", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_exists' => !is_null($cliente),
                                    'cliente_id' => $cliente->id ?? 'NULL',
                                    'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false
                                ]);
                            }
                        } else {
                            \Log::channel($logChannel)->info("Status no requiere acción", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_txt' => $status_txt
                            ]);
                        }
                    }
                }
            },
            'rejected' => function ($reason, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->error("✗ REQUEST RECHAZADO", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'reason' => (string) $reason,
                    'reason_class' => get_class($reason)
                ]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Exitosa) ===", [
            'execution_id' => $executionId,
            'dodas_procesadas' => count($dodasUnicos),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Modulaciones actualizadas',
            'procesadas' => count($dodasUnicos),
            'execution_id' => $executionId
        ], 200);
        
    } catch (Exception $e) {
        \Log::channel($logChannel)->critical("=== ERROR CRÍTICO EN BOT ===", [
            'execution_id' => $executionId ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'No fue posible actualizar las modulaciones',
            'details' => $e->getMessage(),
            'execution_id' => $executionId ?? null
        ], 500);
    }
}
    public function checkTraficoBot_Funcional(Request $request)
{
    $token = $request->query('token');
    $executionId = uniqid('bot_', true); // ID único para esta ejecución
    
    // Log en archivo específico
    $logChannel = 'trafico_bot';
    \Log::channel($logChannel)->info("=== INICIO EJECUCIÓN BOT ===", [
        'execution_id' => $executionId,
        'timestamp' => now()->toDateTimeString(),
        'ip' => $request->ip()
    ]);
    
    if ($token !== env('CHECK_TRAFICO_TOKEN')) {
        \Log::channel($logChannel)->warning("Intento de acceso no autorizado", [
            'execution_id' => $executionId,
            'ip' => $request->ip(),
            'token_provided' => substr($token, 0, 5) . '***'
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        // Paso 1: Validar configuración
        \Log::channel($logChannel)->info("PASO 1: Validando configuración", [
            'execution_id' => $executionId
        ]);
        
        $api = env('PECEM_API_URL');
        if (!$api) {
            \Log::channel($logChannel)->error("URL de PECEM no configurada", [
                'execution_id' => $executionId
            ]);
            return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
        }
        
        \Log::channel($logChannel)->info("Configuración OK", [
            'execution_id' => $executionId,
            'api_url' => $api
        ]);

        // Paso 2: Obtener operaciones
        \Log::channel($logChannel)->info("PASO 2: Consultando operaciones pendientes", [
            'execution_id' => $executionId
        ]);
        
        $operaciones = Operacion::whereNotNull('num_doda')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->get();

        \Log::channel($logChannel)->info("Operaciones encontradas", [
            'execution_id' => $executionId,
            'total_operaciones' => $operaciones->count()
        ]);

        if ($operaciones->isEmpty()) {
            \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Sin datos) ===", [
                'execution_id' => $executionId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'No hay modulaciones por actualizar'
            ], 200);
        }

        $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
        \Log::channel($logChannel)->info("PASO 3: DODAs únicos identificados", [
            'execution_id' => $executionId,
            'total_dodas' => count($dodasUnicos),
            'dodas' => $dodasUnicos
        ]);

        // Paso 4: Configurar cliente HTTP
        \Log::channel($logChannel)->info("PASO 4: Configurando cliente HTTP", [
            'execution_id' => $executionId
        ]);
        
        $client = new Client([
            'verify' => false,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);

        $requests = function ($dodas) use ($client, $api) {
            foreach ($dodas as $doda) {
                yield $doda => function () use ($client, $api, $doda) {
                    return $client->getAsync($api . $doda);
                };
            }
        };

        // Paso 5: Procesar requests en pool
        \Log::channel($logChannel)->info("PASO 5: Iniciando procesamiento de DODAs", [
            'execution_id' => $executionId,
            'concurrency' => 5
        ]);

        $pool = new Pool($client, $requests($dodasUnicos), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->info("Request exitoso para DODA", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_code' => $response->getStatusCode()
                ]);
                
                $html = (string) $response->getBody();
                preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                \Log::channel($logChannel)->info("Status extraído del HTML", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_txt' => $status_txt,
                    'matches_found' => count($matches[1])
                ]);

                // Actualizar modulación
                $updated = Operacion::where('num_doda', $doda)
                    ->update(['modulacion'=> $status_txt]);
                
                \Log::channel($logChannel)->info("Modulación actualizada en BD", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'registros_actualizados' => $updated
                ]);
                
                // CARGAR RELACIÓN CLIENTE CON EAGER LOADING
                $operaciones = Operacion::with('cliente')->where('num_doda', $doda)->get();
                \Log::channel($logChannel)->info("Operaciones obtenidas para procesamiento", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'cantidad' => $operaciones->count(),
                    'ids' => $operaciones->pluck('id')->toArray(),
                    'clientes_cargados' => $operaciones->pluck('cliente_id')->toArray()
                ]);
                
                foreach ($operaciones as $index => $operacion) {
                    \Log::channel($logChannel)->info("Procesando exportación individual", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'operacion_id' => $operacion->id,
                        'index' => $index + 1,
                        'total' => $operaciones->count(),
                        'num_factura' => $operacion->num_factura ?? 'N/A'
                    ]);
                    
                    // Verificar relación con cliente
                    $cliente = $operacion->cliente;
                    
                    \Log::channel($logChannel)->info("Relación cliente verificada", [
                        'execution_id' => $executionId,
                        'operacion_id' => $operacion->id,
                        'cliente_id_en_operacion' => $operacion->cliente_id,
                        'cliente_cargado' => !is_null($cliente),
                        'cliente_id' => $cliente->id ?? 'NULL',
                        'cliente_correo' => $cliente->correo_contacto_principal ?? 'NULL'
                    ]);
                    
                    if (
                        $status_txt == 'DESADUANAMIENTO LIBRE' ||
                        $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ) {
                        \Log::channel($logChannel)->info("Status requiere notificación (Libre/Concluido)", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'status_txt' => $status_txt
                        ]);
                        
                        // VERIFICAR QUE CLIENTE EXISTE ANTES DE NOTIFICAR
                        if ($cliente) {
                            try {
                                $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                                \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id
                                ]);
                            } catch (\Exception $e) {
                                \Log::channel($logChannel)->error("Error al enviar notificación", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        } else {
                            \Log::channel($logChannel)->warning("No se envió notificación - Cliente NULL", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'cliente_id_en_bd' => $operacion->cliente_id
                            ]);
                        }
                        
                        if ($cliente && $cliente->correo_contacto_principal) {
                            $datosTramite = [
                                'factura' => $operacion->num_factura,
                                'nombre_producto' => $operacion->nombre_producto,
                                'no_economico' => $operacion->num_thermo,
                                'no_alpha' => $operacion->codigo_alpha
                            ];

                            \Log::channel($logChannel)->info("Preparando dispatch de Job", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'cliente_id' => $cliente->id,
                                'correo' => $cliente->correo_contacto_principal,
                                'datos_tramite' => $datosTramite
                            ]);

                            try{
                                EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'doda' => $doda,
                                    'status' => $status_txt
                                ]);
                            }catch(Exception $e){
                                \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        } else {
                            \Log::channel($logChannel)->warning("No se envió correo - Validación falló", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'cliente_exists' => !is_null($cliente),
                                'cliente_id' => $cliente->id ?? 'NULL',
                                'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false,
                                'correo' => $cliente->correo_contacto_principal ?? 'NULL'
                            ]);
                        }
                    } else {
                        if ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                            $status_txt_modificado = 'TRAMITE EN PROCESO DE REVISION';
                            
                            \Log::channel($logChannel)->info("Status modificado para notificación", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_original' => $status_txt,
                                'status_modificado' => $status_txt_modificado
                            ]);
                            
                            // VERIFICAR QUE CLIENTE EXISTE ANTES DE NOTIFICAR
                            if ($cliente) {
                                try {
                                    $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt_modificado);
                                    \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id
                                    ]);
                                } catch (\Exception $e) {
                                    \Log::channel($logChannel)->error("Error al enviar notificación", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->warning("No se envió notificación - Cliente NULL (Revisión)", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id_en_bd' => $operacion->cliente_id
                                ]);
                            }
                            
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                \Log::channel($logChannel)->info("Preparando dispatch de Job (Revisión)", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'correo' => $cliente->correo_contacto_principal
                                ]);

                                try{
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt_modificado);
                                    \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'doda' => $doda,
                                        'status' => $status_txt_modificado
                                    ]);
                                }catch(Exception $e){
                                    \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->warning("No se envió correo - Validación falló (Revisión)", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_exists' => !is_null($cliente),
                                    'cliente_id' => $cliente->id ?? 'NULL',
                                    'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false
                                ]);
                            }
                        } else {
                            \Log::channel($logChannel)->info("Status no requiere acción", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_txt' => $status_txt
                            ]);
                        }
                    }
                }
            },
            'rejected' => function ($reason, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->error("✗ REQUEST RECHAZADO", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'reason' => (string) $reason,
                    'reason_class' => get_class($reason)
                ]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Exitosa) ===", [
            'execution_id' => $executionId,
            'dodas_procesadas' => count($dodasUnicos),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Modulaciones actualizadas',
            'procesadas' => count($dodasUnicos),
            'execution_id' => $executionId
        ], 200);
        
    } catch (Exception $e) {
        \Log::channel($logChannel)->critical("=== ERROR CRÍTICO EN BOT ===", [
            'execution_id' => $executionId ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'No fue posible actualizar las modulaciones',
            'details' => $e->getMessage(),
            'execution_id' => $executionId ?? null
        ], 500);
    }
}
    public function checkTraficoBot_FUNCIONAL_PERO_NO_AVISA_ENTRADA_A_ROJO(Request $request)
    {
        $token = $request->query('token');
        $executionId = uniqid('bot_', true); // ID único para esta ejecución

        // Log en archivo específico
        $logChannel = 'trafico_bot';
        \Log::channel($logChannel)->info("=== INICIO EJECUCIÓN BOT ===", [
            'execution_id' => $executionId,
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip()
        ]);

        if ($token !== env('CHECK_TRAFICO_TOKEN')) {
            \Log::channel($logChannel)->warning("Intento de acceso no autorizado", [
                'execution_id' => $executionId,
                'ip' => $request->ip(),
                'token_provided' => substr($token, 0, 5) . '***'
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Paso 1: Validar configuración
            \Log::channel($logChannel)->info("PASO 1: Validando configuración", [
                'execution_id' => $executionId
            ]);

            $api = env('PECEM_API_URL');
            if (!$api) {
                \Log::channel($logChannel)->error("URL de PECEM no configurada", [
                    'execution_id' => $executionId
                ]);
                return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
            }

            \Log::channel($logChannel)->info("Configuración OK", [
                'execution_id' => $executionId,
                'api_url' => $api
            ]);

            // Paso 2: Obtener operaciones
            \Log::channel($logChannel)->info("PASO 2: Consultando operaciones pendientes", [
                'execution_id' => $executionId
            ]);

            $operaciones = Operacion::whereNotNull('num_doda')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();

            \Log::channel($logChannel)->info("Operaciones encontradas", [
                'execution_id' => $executionId,
                'total_operaciones' => $operaciones->count()
            ]);

            if ($operaciones->isEmpty()) {
                \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Sin datos) ===", [
                    'execution_id' => $executionId
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'No hay modulaciones por actualizar'
                ], 200);
            }

            $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
            \Log::channel($logChannel)->info("PASO 3: DODAs únicos identificados", [
                'execution_id' => $executionId,
                'total_dodas' => count($dodasUnicos),
                'dodas' => $dodasUnicos
            ]);

            // Paso 4: Configurar cliente HTTP
            \Log::channel($logChannel)->info("PASO 4: Configurando cliente HTTP", [
                'execution_id' => $executionId
            ]);

            $client = new Client([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $requests = function ($dodas) use ($client, $api) {
                foreach ($dodas as $doda) {
                    yield $doda => function () use ($client, $api, $doda) {
                        return $client->getAsync($api . $doda);
                    };
                }
            };

            // Paso 5: Procesar requests en pool
            \Log::channel($logChannel)->info("PASO 5: Iniciando procesamiento de DODAs", [
                'execution_id' => $executionId,
                'concurrency' => 5
            ]);

            $pool = new Pool($client, $requests($dodasUnicos), [
                'concurrency' => 5,
                'fulfilled' => function ($response, $doda) use ($logChannel, $executionId) {
                    \Log::channel($logChannel)->info("Request exitoso para DODA", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'status_code' => $response->getStatusCode()
                    ]);

                    $html = (string) $response->getBody();
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                    \Log::channel($logChannel)->info("Status extraído del HTML", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'status_txt' => $status_txt,
                        'matches_found' => count($matches[1])
                    ]);

                    // 1️⃣ Obtener la modulación anterior ANTES del update
                    $modulacion_anterior = Operacion::where('num_doda', $doda)->value('modulacion');
                    
                    // Actualizar modulación
                    $updated = Operacion::where('num_doda', $doda)
                        ->update(['modulacion' => $status_txt]);

                    \Log::channel($logChannel)->info("Modulación actualizada en BD", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'registros_actualizados' => $updated
                    ]);

                    // CARGAR RELACIÓN CLIENTE CON EAGER LOADING
                    $operaciones = Operacion::with('cliente')->where('num_doda', $doda)->get();
                    \Log::channel($logChannel)->info("Operaciones obtenidas para procesamiento", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'cantidad' => $operaciones->count(),
                        'ids' => $operaciones->pluck('id')->toArray(),
                        'clientes_cargados' => $operaciones->pluck('cliente_id')->toArray()
                    ]);

                    foreach ($operaciones as $index => $operacion) {
                        \Log::channel($logChannel)->info("Procesando exportación individual", [
                            'execution_id' => $executionId,
                            'doda' => $doda,
                            'operacion_id' => $operacion->id,
                            'index' => $index + 1,
                            'total' => $operaciones->count(),
                            'num_factura' => $operacion->num_factura ?? 'N/A'
                        ]);

                        // Verificar relación con cliente
                        $cliente = $operacion->cliente;

                        \Log::channel($logChannel)->info("Relación cliente verificada", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'cliente_id_en_operacion' => $operacion->cliente_id,
                            'cliente_cargado' => !is_null($cliente),
                            'cliente_id' => $cliente->id ?? 'NULL',
                            'cliente_correo' => $cliente->correo_contacto_principal ?? 'NULL'
                        ]);

                        if (
                            $status_txt == 'DESADUANAMIENTO LIBRE' ||
                            $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                        ) {
                            \Log::channel($logChannel)->info("Status requiere notificación (Libre/Concluido)", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_txt' => $status_txt
                            ]);

                            // VERIFICAR QUE CLIENTE EXISTE ANTES DE NOTIFICAR
                            if ($cliente) {
                                try {
                                    $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                                    \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id
                                    ]);
                                } catch (\Exception $e) {
                                    \Log::channel($logChannel)->error("Error al enviar notificación", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->warning("No se envió notificación - Cliente NULL", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id_en_bd' => $operacion->cliente_id
                                ]);
                            }

                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                \Log::channel($logChannel)->info("Preparando dispatch de Job", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_id' => $cliente->id,
                                    'correo' => $cliente->correo_contacto_principal,
                                    'datos_tramite' => $datosTramite
                                ]);

                                try {
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                    \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'doda' => $doda,
                                        'status' => $status_txt
                                    ]);
                                } catch (Exception $e) {
                                    \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->warning("No se envió correo - Validación falló", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'cliente_exists' => !is_null($cliente),
                                    'cliente_id' => $cliente->id ?? 'NULL',
                                    'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false,
                                    'correo' => $cliente->correo_contacto_principal ?? 'NULL'
                                ]);
                            }
                        } else {
                            if ($status_txt == 'RECONOCIMIENTO ADUANERO') {

                                // ⛔ EVITA SPAM – NO NOTIFICAR SI SIGUE EN EL MISMO ROJO
                                if ($operacion->modulacion === $status_txt) {
                                    \Log::channel($logChannel)->info("SIN CAMBIO - NO SE NOTIFICA (Rojo)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'estatus' => $status_txt
                                    ]);
                                    continue;
                                }



                                $status_txt_modificado = 'TRAMITE EN PROCESO DE REVISION';

                                \Log::channel($logChannel)->info("Status modificado para notificación", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'status_original' => $status_txt,
                                    'status_modificado' => $status_txt_modificado
                                ]);

                                // VERIFICAR QUE CLIENTE EXISTE ANTES DE NOTIFICAR
                                if ($cliente) {
                                    try {
                                        $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt_modificado);
                                        \Log::channel($logChannel)->info("Notificación enviada exitosamente", [
                                            'execution_id' => $executionId,
                                            'operacion_id' => $operacion->id
                                        ]);
                                    } catch (\Exception $e) {
                                        \Log::channel($logChannel)->error("Error al enviar notificación", [
                                            'execution_id' => $executionId,
                                            'operacion_id' => $operacion->id,
                                            'error' => $e->getMessage(),
                                            'trace' => $e->getTraceAsString()
                                        ]);
                                    }
                                } else {
                                    \Log::channel($logChannel)->warning("No se envió notificación - Cliente NULL (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id_en_bd' => $operacion->cliente_id
                                    ]);
                                }

                                if ($cliente && $cliente->correo_contacto_principal) {
                                    $datosTramite = [
                                        'factura' => $operacion->num_factura,
                                        'nombre_producto' => $operacion->nombre_producto,
                                        'no_economico' => $operacion->num_thermo,
                                        'no_alpha' => $operacion->codigo_alpha
                                    ];

                                    \Log::channel($logChannel)->info("Preparando dispatch de Job (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_id' => $cliente->id,
                                        'correo' => $cliente->correo_contacto_principal
                                    ]);

                                    try {
                                        EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt_modificado);
                                        \Log::channel($logChannel)->info("✓ JOB DESPACHADO EXITOSAMENTE (Revisión)", [
                                            'execution_id' => $executionId,
                                            'operacion_id' => $operacion->id,
                                            'cliente_id' => $cliente->id,
                                            'doda' => $doda,
                                            'status' => $status_txt_modificado
                                        ]);
                                    } catch (Exception $e) {
                                        \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB (Revisión)", [
                                            'execution_id' => $executionId,
                                            'operacion_id' => $operacion->id,
                                            'cliente_id' => $cliente->id,
                                            'error' => $e->getMessage(),
                                            'trace' => $e->getTraceAsString()
                                        ]);
                                    }
                                } else {
                                    \Log::channel($logChannel)->warning("No se envió correo - Validación falló (Revisión)", [
                                        'execution_id' => $executionId,
                                        'operacion_id' => $operacion->id,
                                        'cliente_exists' => !is_null($cliente),
                                        'cliente_id' => $cliente->id ?? 'NULL',
                                        'correo_exists' => $cliente ? !empty($cliente->correo_contacto_principal) : false
                                    ]);
                                }
                            } else {
                                \Log::channel($logChannel)->info("Status no requiere acción", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'status_txt' => $status_txt
                                ]);
                            }
                        }
                    }
                },
                'rejected' => function ($reason, $doda) use ($logChannel, $executionId) {
                    \Log::channel($logChannel)->error("✗ REQUEST RECHAZADO", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'reason' => (string) $reason,
                        'reason_class' => get_class($reason)
                    ]);
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Exitosa) ===", [
                'execution_id' => $executionId,
                'dodas_procesadas' => count($dodasUnicos),
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modulaciones actualizadas',
                'procesadas' => count($dodasUnicos),
                'execution_id' => $executionId
            ], 200);

        } catch (Exception $e) {
            \Log::channel($logChannel)->critical("=== ERROR CRÍTICO EN BOT ===", [
                'execution_id' => $executionId ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'No fue posible actualizar las modulaciones',
                'details' => $e->getMessage(),
                'execution_id' => $executionId ?? null
            ], 500);
        }
    }
    public function checkTraficoBot_Funcinal30enero2026(Request $request)
{
    $token = $request->query('token');
    $executionId = uniqid('bot_', true);
    $logChannel = 'trafico_bot';
    
    \Log::channel($logChannel)->info("=== INICIO EJECUCIÓN BOT ===", [
        'execution_id' => $executionId,
        'timestamp' => now()->toDateTimeString(),
        'ip' => $request->ip()
    ]);

    if ($token !== env('CHECK_TRAFICO_TOKEN')) {
        \Log::channel($logChannel)->warning("Intento de acceso no autorizado", [
            'execution_id' => $executionId,
            'ip' => $request->ip(),
            'token_provided' => substr($token, 0, 5) . '***'
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        \Log::channel($logChannel)->info("PASO 1: Validando configuración", [
            'execution_id' => $executionId
        ]);

        $api = env('PECEM_API_URL');
        if (!$api) {
            \Log::channel($logChannel)->error("URL de PECEM no configurada", [
                'execution_id' => $executionId
            ]);
            return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
        }

        \Log::channel($logChannel)->info("Configuración OK", [
            'execution_id' => $executionId,
            'api_url' => $api
        ]);

        \Log::channel($logChannel)->info("PASO 2: Consultando operaciones pendientes", [
            'execution_id' => $executionId
        ]);

        $operaciones = Operacion::whereNotNull('num_doda')
            ->where(function ($query) {
                $query->whereNull('modulacion')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->get();

        \Log::channel($logChannel)->info("Operaciones encontradas", [
            'execution_id' => $executionId,
            'total_operaciones' => $operaciones->count()
        ]);

        if ($operaciones->isEmpty()) {
            \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Sin datos) ===", [
                'execution_id' => $executionId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'No hay modulaciones por actualizar'
            ], 200);
        }

        $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
        \Log::channel($logChannel)->info("PASO 3: DODAs únicos identificados", [
            'execution_id' => $executionId,
            'total_dodas' => count($dodasUnicos),
            'dodas' => $dodasUnicos
        ]);

        \Log::channel($logChannel)->info("PASO 4: Configurando cliente HTTP", [
            'execution_id' => $executionId
        ]);

        $client = new Client([
            'verify' => false,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);

        $requests = function ($dodas) use ($client, $api) {
            foreach ($dodas as $doda) {
                yield $doda => function () use ($client, $api, $doda) {
                    return $client->getAsync($api . $doda);
                };
            }
        };

        \Log::channel($logChannel)->info("PASO 5: Iniciando procesamiento de DODAs", [
            'execution_id' => $executionId,
            'concurrency' => 5
        ]);

        $pool = new Pool($client, $requests($dodasUnicos), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->info("Request exitoso para DODA", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_code' => $response->getStatusCode()
                ]);

                $html = (string) $response->getBody();
                preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                \Log::channel($logChannel)->info("Status extraído del HTML", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'status_txt' => $status_txt,
                    'matches_found' => count($matches[1])
                ]);

                // 🔥 GUARDAR ESTADOS ANTERIORES ANTES DE ACTUALIZAR
                $operacionesConEstadoAnterior = Operacion::where('num_doda', $doda)
                    ->get()
                    ->keyBy('id')
                    ->map(function ($exp) {
                        return $exp->modulacion; // Guardamos el estado anterior
                    });

                \Log::channel($logChannel)->info("Estados anteriores guardados", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'estados' => $operacionesConEstadoAnterior->toArray()
                ]);

                // Actualizar modulación en BD
                $updated = Operacion::where('num_doda', $doda)
                    ->update(['modulacion' => $status_txt]);

                \Log::channel($logChannel)->info("Modulación actualizada en BD", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'registros_actualizados' => $updated
                ]);

                // CARGAR RELACIÓN CLIENTE CON EAGER LOADING
                $operaciones = Operacion::with('cliente')->where('num_doda', $doda)->get();
                
                \Log::channel($logChannel)->info("Operaciones obtenidas para procesamiento", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'cantidad' => $operaciones->count()
                ]);

                foreach ($operaciones as $index => $operacion) {
                    // 🔥 OBTENER EL ESTADO ANTERIOR DE ESTA EXPORTACIÓN ESPECÍFICA
                    $modulacion_anterior = $operacionesConEstadoAnterior[$operacion->id] ?? null;

                    \Log::channel($logChannel)->info("Procesando exportación individual", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'operacion_id' => $operacion->id,
                        'modulacion_anterior' => $modulacion_anterior,
                        'modulacion_nueva' => $status_txt,
                        'hubo_cambio' => $modulacion_anterior !== $status_txt
                    ]);

                    $cliente = $operacion->cliente;

                    if (!$cliente) {
                        \Log::channel($logChannel)->warning("Cliente no encontrado para exportación", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id
                        ]);
                        continue;
                    }

                    // 🟢 CASO 1: DESADUANAMIENTO LIBRE o RECONOCIMIENTO CONCLUIDO
                    if (
                        $status_txt == 'DESADUANAMIENTO LIBRE' ||
                        $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ) {
                        // Solo notificar si hubo cambio de estado
                        if ($modulacion_anterior !== $status_txt) {
                            \Log::channel($logChannel)->info("✅ CAMBIO DETECTADO - Notificando Libre/Concluido", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'de' => $modulacion_anterior,
                                'a' => $status_txt
                            ]);

                            $this->enviarNotificacionYCorreo($operacion, $cliente, $status_txt, $logChannel, $executionId);
                        } else {
                            \Log::channel($logChannel)->info("⏭️ SIN CAMBIO - No se notifica", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'estatus' => $status_txt
                            ]);
                        }
                    }
                    // 🔴 CASO 2: RECONOCIMIENTO ADUANERO (ROJO)
                    elseif ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                        // Solo notificar si cambió de otro estado a RECONOCIMIENTO ADUANERO
                        if ($modulacion_anterior !== 'RECONOCIMIENTO ADUANERO') {
                            $status_txt_modificado = 'TRAMITE EN PROCESO DE REVISION';

                            \Log::channel($logChannel)->info("✅ CAMBIO DETECTADO - Notificando Revisión", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'de' => $modulacion_anterior,
                                'a' => 'RECONOCIMIENTO ADUANERO',
                                'mensaje_usuario' => $status_txt_modificado
                            ]);

                            $this->enviarNotificacionYCorreo($operacion, $cliente, $status_txt_modificado, $logChannel, $executionId);
                        } else {
                            \Log::channel($logChannel)->info("⏭️ SIN CAMBIO - Sigue en Rojo, no se notifica", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'estatus' => $status_txt
                            ]);
                        }
                    }
                    // ⚪ CASO 3: Otros estados
                    else {
                        \Log::channel($logChannel)->info("Status no requiere acción", [
                            'execution_id' => $executionId,
                            'operacion_id' => $operacion->id,
                            'status_txt' => $status_txt
                        ]);
                    }
                }
            },
            'rejected' => function ($reason, $doda) use ($logChannel, $executionId) {
                \Log::channel($logChannel)->error("✗ REQUEST RECHAZADO", [
                    'execution_id' => $executionId,
                    'doda' => $doda,
                    'reason' => (string) $reason
                ]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Exitosa) ===", [
            'execution_id' => $executionId,
            'dodas_procesadas' => count($dodasUnicos)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modulaciones actualizadas',
            'procesadas' => count($dodasUnicos),
            'execution_id' => $executionId
        ], 200);

    } catch (Exception $e) {
        \Log::channel($logChannel)->critical("=== ERROR CRÍTICO EN BOT ===", [
            'execution_id' => $executionId ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'No fue posible actualizar las modulaciones',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function checkTraficoBot_OriginalFuncionalconTokenyPython(Request $request)
    {
        $token = $request->query('token');
        $executionId = uniqid('bot_', true);
        $logChannel = 'trafico_bot';

        \Log::channel($logChannel)->info("=== INICIO EJECUCIÓN BOT ===", [
            'execution_id' => $executionId,
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip()
        ]);

        if ($token !== env('CHECK_TRAFICO_TOKEN')) {
            \Log::channel($logChannel)->warning("Intento de acceso no autorizado", [
                'execution_id' => $executionId,
                'ip' => $request->ip(),
                'token_provided' => substr($token, 0, 5) . '***'
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            \Log::channel($logChannel)->info("PASO 1: Validando configuración", [
                'execution_id' => $executionId
            ]);

            $api = env('PECEM_API_URL');
            if (!$api) {
                \Log::channel($logChannel)->error("URL de PECEM no configurada", [
                    'execution_id' => $executionId
                ]);
                return response()->json(['error' => 'No está configurada la URL de PECEM en el .env'], 500);
            }

            \Log::channel($logChannel)->info("Configuración OK", [
                'execution_id' => $executionId,
                'api_url' => $api
            ]);

            \Log::channel($logChannel)->info("PASO 2: Consultando operaciones pendientes", [
                'execution_id' => $executionId
            ]);

            $operaciones = Operacion::whereNotNull('num_doda')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();

            \Log::channel($logChannel)->info("Operaciones encontradas", [
                'execution_id' => $executionId,
                'total_operaciones' => $operaciones->count()
            ]);

            if ($operaciones->isEmpty()) {
                \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Sin datos) ===", [
                    'execution_id' => $executionId
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'No hay modulaciones por actualizar'
                ], 200);
            }

            $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();
            \Log::channel($logChannel)->info("PASO 3: DODAs únicos identificados", [
                'execution_id' => $executionId,
                'total_dodas' => count($dodasUnicos),
                'dodas' => $dodasUnicos
            ]);

            \Log::channel($logChannel)->info("PASO 4: Configurando cliente HTTP", [
                'execution_id' => $executionId
            ]);

            $client = new Client([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $requests = function ($dodas) use ($client, $api) {
                foreach ($dodas as $doda) {
                    yield $doda => function () use ($client, $api, $doda) {
                        return $client->getAsync($api . $doda);
                    };
                }
            };

            \Log::channel($logChannel)->info("PASO 5: Iniciando procesamiento de DODAs", [
                'execution_id' => $executionId,
                'concurrency' => 5
            ]);

            $pool = new Pool($client, $requests($dodasUnicos), [
                'concurrency' => 5,
                'fulfilled' => function ($response, $doda) use ($logChannel, $executionId) {
                    \Log::channel($logChannel)->info("Request exitoso para DODA", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'status_code' => $response->getStatusCode()
                    ]);

                    $html = (string) $response->getBody();

                    // 🔥 EXTRAER FECHA Y HORA DE MODULACIÓN
                    // Patrón: 29-01-2026 13:40:48 OPER:521-810546
                    preg_match('/(\d{2}-\d{2}-\d{4}\s+\d{2}:\d{2}:\d{2})\s+OPER:/', $html, $fecha_registroMatch);
                    $fecha_registroModulacion = null;

                    if (!empty($fecha_registroMatch[1])) {
                        try {
                            // Convertir formato DD-MM-YYYY HH:MM:SS a objeto Carbon
                            $fecha_registroModulacion = \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $fecha_registroMatch[1]);

                            \Log::channel($logChannel)->info("Fecha de modulación extraída", [
                                'execution_id' => $executionId,
                                'doda' => $doda,
                                'fecha_registro_original' => $fecha_registroMatch[1],
                                'fecha_registro_parsed' => $fecha_registroModulacion->toDateTimeString()
                            ]);
                        } catch (\Exception $e) {
                            \Log::channel($logChannel)->warning("Error al parsear fecha_registro de modulación", [
                                'execution_id' => $executionId,
                                'doda' => $doda,
                                'fecha_registro_original' => $fecha_registroMatch[1] ?? 'null',
                                'error' => $e->getMessage()
                            ]);
                        }
                    }




                    //Extraer modulacion
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                    \Log::channel($logChannel)->info("Status extraído del HTML", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'status_txt' => $status_txt,
                        'matches_found' => count($matches[1])
                    ]);

                    // 🔥 GUARDAR ESTADOS ANTERIORES ANTES DE ACTUALIZAR
                    $operacionesConEstadoAnterior = Operacion::where('num_doda', $doda)
                        ->get()
                        ->keyBy('id')
                        ->map(function ($exp) {
                        //return $exp->modulacion; // Guardamos el estado anterior
                        return [
                            'modulacion'=> $exp->modulacion,
                            'fecha_modulacion' => $exp->fecha_modulacion
                        ];//Nuevo se agrego para obtener la fecha_registro de modulacion
                    });

                    \Log::channel($logChannel)->info("Estados anteriores guardados", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'estados' => $operacionesConEstadoAnterior->toArray()
                    ]);

                    // Actualizar modulación en BD (SE AGREGA AHORA LA FECHA DE MODULACION)
                    $updated = Operacion::where('num_doda', $doda)
                        ->update(['modulacion' => $status_txt,'fecha_modulacion'=>$fecha_registroModulacion]);

                    \Log::channel($logChannel)->info("Modulación y Fecha actualizada en BD", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'registros_actualizados' => $updated,
                        'nueva_fecha_registro'=> $fecha_registroModulacion?->toDateTimeString()
                    ]);

                    // CARGAR RELACIÓN CLIENTE CON EAGER LOADING
                    $operaciones = Operacion::with('cliente')->where('num_doda', $doda)->get();

                    \Log::channel($logChannel)->info("Operaciones obtenidas para procesamiento", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'cantidad' => $operaciones->count()
                    ]);

                    foreach ($operaciones as $index => $operacion) {
                        // 🔥 OBTENER EL ESTADO ANTERIOR DE ESTA EXPORTACIÓN ESPECÍFICA
                        //$modulacion_anterior = $operacionesConEstadoAnterior[$operacion->id] ?? null;
                        //Nuevo metodo
                        $estadoAnterior = $operacionesConEstadoAnterior[$operacion->id] ?? null;
                        $modulacion_anterior = $estadoAnterior['modulacion'] ?? null;
                        $fecha_registro_anterior = $estadoAnterior['fecha_modulacion'] ?? null;

                        \Log::channel($logChannel)->info("Procesando exportación individual", [
                            'execution_id' => $executionId,
                            'doda' => $doda,
                            'operacion_id' => $operacion->id,
                            'modulacion_anterior' => $modulacion_anterior,
                            'modulacion_nueva' => $status_txt,
                            'fecha_registro_anterior' => $fecha_registro_anterior,
                            'fecha_registro_nueva' => $fecha_registroModulacion?->toDateTimeString(),
                            'hubo_cambio' => $modulacion_anterior !== $status_txt
                        ]);

                        $cliente = $operacion->cliente;

                        if (!$cliente) {
                            \Log::channel($logChannel)->warning("Cliente no encontrado para exportación", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id
                            ]);
                            continue;
                        }

                        // 🟢 CASO 1: DESADUANAMIENTO LIBRE o RECONOCIMIENTO CONCLUIDO
                        if (
                            $status_txt == 'DESADUANAMIENTO LIBRE' ||
                            $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                        ) {
                            // Solo notificar si hubo cambio de estado
                            if ($modulacion_anterior !== $status_txt) {
                                \Log::channel($logChannel)->info("✅ CAMBIO DETECTADO - Notificando Libre/Concluido", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'de' => $modulacion_anterior,
                                    'a' => $status_txt
                                ]);

                                $this->enviarNotificacionYCorreo($operacion, $cliente, $status_txt, $logChannel, $executionId);
                            } else {
                                \Log::channel($logChannel)->info("⏭️ SIN CAMBIO - No se notifica", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'estatus' => $status_txt
                                ]);
                            }
                        }
                        // 🔴 CASO 2: RECONOCIMIENTO ADUANERO (ROJO)
                        elseif ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                            // Solo notificar si cambió de otro estado a RECONOCIMIENTO ADUANERO
                            if ($modulacion_anterior !== 'RECONOCIMIENTO ADUANERO') {
                                $status_txt_modificado = 'TRAMITE EN PROCESO DE REVISION';

                                \Log::channel($logChannel)->info("✅ CAMBIO DETECTADO - Notificando Revisión", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'de' => $modulacion_anterior,
                                    'a' => 'RECONOCIMIENTO ADUANERO',
                                    'mensaje_usuario' => $status_txt_modificado
                                ]);

                                $this->enviarNotificacionYCorreo($operacion, $cliente, $status_txt_modificado, $logChannel, $executionId);
                            } else {
                                \Log::channel($logChannel)->info("⏭️ SIN CAMBIO - Sigue en Rojo, no se notifica", [
                                    'execution_id' => $executionId,
                                    'operacion_id' => $operacion->id,
                                    'estatus' => $status_txt
                                ]);
                            }
                        }
                        // ⚪ CASO 3: Otros estados
                        else {
                            \Log::channel($logChannel)->info("Status no requiere acción", [
                                'execution_id' => $executionId,
                                'operacion_id' => $operacion->id,
                                'status_txt' => $status_txt
                            ]);
                        }
                    }
                },
                'rejected' => function ($reason, $doda) use ($logChannel, $executionId) {
                    \Log::channel($logChannel)->error("✗ REQUEST RECHAZADO", [
                        'execution_id' => $executionId,
                        'doda' => $doda,
                        'reason' => (string) $reason
                    ]);
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            \Log::channel($logChannel)->info("=== FIN EJECUCIÓN BOT (Exitosa) ===", [
                'execution_id' => $executionId,
                'dodas_procesadas' => count($dodasUnicos)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modulaciones actualizadas',
                'procesadas' => count($dodasUnicos),
                'execution_id' => $executionId
            ], 200);

        } catch (Exception $e) {
            \Log::channel($logChannel)->critical("=== ERROR CRÍTICO EN BOT ===", [
                'execution_id' => $executionId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'No fue posible actualizar las modulaciones',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * ---------------------------------------------------------------
     * BOT ENDPOINT — checktraficobot
     * Llamado por check_trafico.py cada 5 minutos vía GET:
     *   /checktraficobot?token=<BOT_TRAFICO_TOKEN en .env>
     *
     * PASO 1: Consulta PECEM para actualizar modulaciones.
     *   - Si modula con estado final → guarda fecha_modulacion = now()
     *     y sobreescribe fecha_registro = día real de modulación.
     *   - Si modula → envía correo igual que el método original.
     *
     * PASO 2: Si son exactamente las 23:59 → avanza fecha_registro +1 día
     *   a cualquier operación que siga sin modulación final.
     * ---------------------------------------------------------------
     */
    public function checktraficobot(Request $request)
    {
        // — Validación del token original —
        $tokenEsperado = env('CHECK_TRAFICO_TOKEN');
        if ($request->input('token') !== $tokenEsperado) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $resultados = [
            'modulaciones_actualizadas' => [],
            'fechas_avanzadas'          => [],
            'errores'                   => [],
        ];

        $logChannel = 'trafico_bot';
        $executionId = uniqid('bot_', true);

        // ================================================================
        // PASO 1 — Actualizar modulaciones desde PECEM
        // ================================================================
        try {
            $api = env('PECEM_API_URL');

            if ($api) {
                $operaciones = Operacion::whereNotNull('num_doda')
                    ->where(function ($q) {
                        $q->whereNull('modulacion')
                          ->orWhereNotIn('modulacion', [
                              'DESADUANAMIENTO LIBRE',
                              'RECONOCIMIENTO ADUANERO CONCLUIDO',
                          ]);
                    })
                    ->get();

                if ($operaciones->isNotEmpty()) {
                    $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();

                    $client = new Client([
                        'verify'  => false,
                        'curl'    => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
                        'headers' => ['User-Agent' => 'Mozilla/5.0'],
                    ]);

                    $requests = function ($dodas) use ($client, $api) {
                        foreach ($dodas as $doda) {
                            yield $doda => function () use ($client, $api, $doda) {
                                return $client->getAsync($api . $doda);
                            };
                        }
                    };

                    $pool = new Pool($client, $requests($dodasUnicos), [
                        'concurrency' => 5,
                        'fulfilled' => function ($response, $doda) use (&$resultados, $logChannel, $executionId) {
                            $html = (string) $response->getBody();

                            // Extraer último estado entre *** ***
                            preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                            $status_txt = last($matches[1])
                                ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                            // Extraer fecha_registro de modulación (Soporta - y /)
                            $fecha_registroModulacion = null;
                            if (preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4}\s+\d{2}:\d{2}:\d{2})/', $html, $fecha_registroMatch)) {
                                try {
                                    $fmt = str_contains($fecha_registroMatch[1], '/') ? 'd/m/Y H:i:s' : 'd-m-Y H:i:s';
                                    $fecha_registroModulacion = \Carbon\Carbon::createFromFormat($fmt, $fecha_registroMatch[1]);
                                } catch (\Exception $e) {
                                    $fecha_registroModulacion = null;
                                }
                            }

                            $esModulacionFinal = in_array($status_txt, [
                                'DESADUANAMIENTO LIBRE',
                                'RECONOCIMIENTO ADUANERO CONCLUIDO',
                            ]);

                            // 🔥 GUARDAR ESTADOS ANTERIORES PARA LOGICA DE NOTIFICACION
                            $operacionesConEstadoAnterior = Operacion::where('num_doda', $doda)
                                ->get()
                                ->keyBy('id')
                                ->map(function ($exp) {
                                    return [
                                        'modulacion' => $exp->modulacion,
                                        'fecha_modulacion' => $exp->fecha_modulacion
                                    ];
                                });

                            // Actualizar registros del DODA
                            Operacion::where('num_doda', $doda)->update([
                                'modulacion' => $status_txt,
                                'fecha_modulacion' => $fecha_registroModulacion
                            ]);

                            // Sincronizar fecha_registro de cruce si es final
                            if ($esModulacionFinal && $fecha_registroModulacion) {
                                Operacion::where('num_doda', $doda)->update(['fecha_registro' => $fecha_registroModulacion->toDateString()]);
                            }

                            // Eager load para notificaciones
                            $ops = Operacion::with('cliente')->where('num_doda', $doda)->get();

                            foreach ($ops as $exp) {
                                $estadoAnterior = $operacionesConEstadoAnterior[$exp->id] ?? null;
                                $modulacion_anterior = $estadoAnterior['modulacion'] ?? null;

                                // Lógica de Notificación Original (Comparativa)
                                if ($esModulacionFinal) {
                                    if ($modulacion_anterior !== $status_txt) {
                                        $this->enviarNotificacionYCorreo($exp, $exp->cliente, $status_txt, $logChannel, $executionId);
                                    }
                                } elseif ($status_txt == 'RECONOCIMIENTO ADUANERO') {
                                    if ($modulacion_anterior !== 'RECONOCIMIENTO ADUANERO') {
                                        $this->enviarNotificacionYCorreo($exp, $exp->cliente, 'TRAMITE EN PROCESO DE REVISION', $logChannel, $executionId);
                                    }
                                }

                                $resultados['modulaciones_actualizadas'][] = [
                                    'id'     => $exp->id,
                                    'doda'   => $doda,
                                    'estado' => $status_txt
                                ];
                            }
                        },
                        'rejected' => function ($reason, $doda) use (&$resultados) {
                            $msg = "Error consultando DODA {$doda}: {$reason}";
                            \Log::error('checktraficobot: ' . $msg);
                            $resultados['errores'][] = $msg;
                        },
                    ]);

                    $pool->promise()->wait();
                }
            }
        } catch (\Exception $e) {
            \Log::error('checktraficobot PASO-1: ' . $e->getMessage());
            $resultados['errores'][] = 'PASO-1: ' . $e->getMessage();
        }

        // ================================================================
        // PASO 2 — A las 23:59 avanzar +1 día las operaciones sin modular
        // ================================================================
        try {
            $hora = (int) now()->format('H');
            $min  = (int) now()->format('i');

            if ($hora === 23 && $min === 59) {
                $sinModular = Operacion::whereDate('fecha_registro', '<=', now()->toDateString())
                    ->where(function ($q) {
                        $q->whereNull('modulacion')
                          ->orWhereNotIn('modulacion', [
                              'DESADUANAMIENTO LIBRE',
                              'RECONOCIMIENTO ADUANERO CONCLUIDO',
                          ]);
                    })
                    ->get();

                foreach ($sinModular as $exp) {
                    $fecha_registroVieja = \Carbon\Carbon::parse($exp->fecha_registro)->toDateString();
                    $fecha_registroNueva = \Carbon\Carbon::parse($exp->fecha_registro)->addDay()->toDateString();
                    $exp->update(['fecha_registro' => $fecha_registroNueva]);

                    $resultados['fechas_avanzadas'][] = [
                        'id'          => $exp->id,
                        'fecha_registro_vieja' => $fecha_registroVieja,
                        'fecha_registro_nueva' => $fecha_registroNueva,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('checktraficobot PASO-2: ' . $e->getMessage());
            $resultados['errores'][] = 'PASO-2: ' . $e->getMessage();
        }

        return response()->json([
            'ok'         => true,
            'timestamp'  => now()->toDateTimeString(),
            'resultados' => $resultados,
        ]);
    }

// 🔥 MÉTODO AUXILIAR PARA ENVIAR NOTIFICACIONES
private function enviarNotificacionYCorreo($operacion, $cliente, $status_txt, $logChannel, $executionId)
{
    // Enviar notificación al sistema
    try {
        $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
        \Log::channel($logChannel)->info("✓ Notificación enviada", [
            'execution_id' => $executionId,
            'operacion_id' => $operacion->id
        ]);
    } catch (\Exception $e) {
        \Log::channel($logChannel)->error("✗ Error al enviar notificación", [
            'execution_id' => $executionId,
            'operacion_id' => $operacion->id,
            'error' => $e->getMessage()
        ]);
    }

    // Enviar correo si el cliente tiene email
    if ($cliente->correo_contacto_principal) {
        $datosTramite = [
            'factura' => $operacion->num_factura,
            'nombre_producto' => $operacion->nombre_producto,
            'no_economico' => $operacion->num_thermo,
            'no_alpha' => $operacion->codigo_alpha
        ];

        try {
            EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
            \Log::channel($logChannel)->info("✓ JOB DESPACHADO", [
                'execution_id' => $executionId,
                'operacion_id' => $operacion->id,
                'cliente_id' => $cliente->id,
                'correo' => $cliente->correo_contacto_principal
            ]);
        } catch (Exception $e) {
            \Log::channel($logChannel)->error("✗ ERROR AL DESPACHAR JOB", [
                'execution_id' => $executionId,
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        \Log::channel($logChannel)->warning("No se envió correo - Sin email", [
            'execution_id' => $executionId,
            'operacion_id' => $operacion->id
        ]);
    }
}
    
    public function checkTraficoBot_OLD2(Request $request)
    {
        $token = $request->query('token');
        
            // Token secreto definido por ti
            if ($token !== env('CHECK_TRAFICO_TOKEN')) {
            return response()->json(['error' => 'Unauthorized'], 401);
            }


        try {
            $api = env('PECEM_API_URL');
            if (!$api) {
                return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
            }

            $operaciones = Operacion::whereNotNull('num_doda')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();
                //dd($operaciones);

            if ($operaciones->isEmpty()) {
                return redirect()->route('trafico.index')
                    ->with('success', 'No hay modulaciones por actualizar');
            }

            $dodasUnicos = $operaciones->pluck('num_doda')->unique()->values()->all();

            //dd($dodasUnicos);
            $client = new Client([
                'verify' => false,
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $requests = function ($dodas) use ($client, $api) {
                foreach ($dodas as $doda) {
                    yield $doda => function () use ($client, $api, $doda) {
                        return $client->getAsync($api . $doda);
                    };
                }
            };

            $pool = new Pool($client, $requests($dodasUnicos), [
                'concurrency' => 5, // máximo 5 peticiones a la vez
                'fulfilled' => function ($response, $doda) {
                    $html = (string) $response->getBody();
                    preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
                    $status_txt = last($matches[1]) ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

                    Operacion::where('num_doda', $doda)//->first();
                        ->update(['modulacion'=> $status_txt]);
                    
                    $operaciones = Operacion::where('num_doda', $doda)->get();
                    foreach ($operaciones as $operacion) {
                        //$operacion->modulacion = $status_txt;
                        //$operacion->save();

                        /*
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                        */
                        if (
                            $status_txt == 'DESADUANAMIENTO LIBRE' ||
                            $status_txt == 'RECONOCIMIENTO ADUANERO CONCLUIDO'
                        ) {
                            // 🔔 Notificar al módulo de tráfico
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            // 📧 Enviar correo al cliente
                            $cliente = $operacion->cliente; // suponiendo relación
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    //'carta_porte' => $operacion->complemento_carta_porte ? 'SI' : 'NO',
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                //\Mail::to($cliente->correo_contacto_principal)}
                            /*try {
                                       \Mail::to('operaciones@crosspoint.com.mx')
                                   ->bcc(['sistemas@crosspoint.com.mx'])
                                    ->send(new EstatusModulacionMail($cliente, $datosTramite, $status_txt));

                                   \Log::info("CORREO ENVIADO A {$cliente->correo_contacto_principal}");
                                } catch (\Throwable $ex) {
                                   \Log::error("ERROR AL ENVIAR CORREO: " . $ex->getMessage());
                                }*/
                                try{
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                }catch(Exception $e){
                                    \Log::error("Error en generar log de job: " . $ex->getMessage());
                                }
                                

                            }
                        }else{
                            if (
                            $status_txt == 'RECONOCIMIENTO ADUANERO' 
                        ) {
                            // 📧 Enviar correo al cliente
                            $status_txt='TRAMITE EN PROCESO DE REVISION';
                            // 🔔 Notificar al módulo de tráfico
                            $this->notificacionService->notificarModulacionActualizada($operacion, $status_txt);
                            $cliente = $operacion->cliente; // suponiendo relación
                            if ($cliente && $cliente->correo_contacto_principal) {
                                $datosTramite = [
                                    'factura' => $operacion->num_factura,
                                    //'carta_porte' => $operacion->complemento_carta_porte ? 'SI' : 'NO',
                                    'nombre_producto' => $operacion->nombre_producto,
                                    'no_economico' => $operacion->num_thermo,
                                    'no_alpha' => $operacion->codigo_alpha
                                ];

                                //\Mail::to($cliente->correo_contacto_principal)}
                            //\Mail::to('operaciones@crosspoint.com.mx')
                            //->bcc(['sistemas@crosspoint.com.mx', 'trafico3@crosspoint.com.mx','operacionesreynosa@crosspoint.com.mx','trafico2@crosspoint.com.mx'])
                            //->cc('alejandro@crosspoint.com.mx')
                            //    ->send(new EstatusModulacionMail($cliente, $datosTramite, $status_txt));
                               //EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                               try{
                                    EnviarCorreoModulacionJob::dispatch($cliente->id, $datosTramite, $status_txt);
                                }catch(Exception $e){
                                    \Log::error("Error en generar log de job: " . $ex->getMessage());
                                }
                            }
                        }
                        }


                    }
                },
                'rejected' => function ($reason, $doda) {
                    \Log::error("Error al consultar DODA {$doda}: {$reason}");
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            return back()->with('success', 'Modulaciones actualizadas');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'No fue posible actualizar las modulaciones']);
        }
    }

    public function checkTraficoBot_Old(Request $request)
    {
    $token = $request->query('token');

    // Token secreto definido por ti
    if ($token !== env('CHECK_TRAFICO_TOKEN')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Llamamos al método original que realiza la lógica real
    return $this->checkTrafico();
    }

    public function showDataExpo($id)
    {
        $operacion = Operacion::with('cliente', 'aduana', 'documentos','bodega')->findOrFail($id);
        $bodegas=Bodega::orderBy('nombre')->get();

        return view('trafico.showdataexpo', compact('operacion','bodegas'));
    }

    //Demo de graficos
    public function grafico()
    {
        $datos = Operacion::selectRaw('cliente_id, COUNT(*) as total')
            ->groupBy('cliente_id')
            ->with('cliente')
            ->get();

        // Preparamos los datos para el gráfico
        $labels = $datos->pluck('cliente.nombre');  // nombres de clientes
        $values = $datos->pluck('total');          // totales

        return view('graficos.index', compact('labels', 'values'));
    }

    public function updateSobrepeso(Request $request, $id)
    {
        $operacion = Operacion::findOrFail($id);
        $operacion->sobrepeso = $request->sobrepeso;
        $operacion->save();

        return response()->json(['success' => true]);
    }

    public function updateModulacion(Request $request, $num_thermo)
    {
        // Valida que el campo 'modulacion' esté presente y sea una de las opciones válidas
        /*$request->validate([
            'modulacion' => 'required|in:Verde,Rojo,Sin Módulo',
        ]);

        try {
            // Busca y actualiza todas las operaciones con el mismo número de thermo
            $registrosActualizados = Operacion::where('num_thermo', $num_thermo)
                ->update(['modulacion' => $request->modulacion]);

            if ($registrosActualizados > 0) {
                // Si se actualizó al menos un registro, devuelve un mensaje de éxito
                return back()->with('success', 'El estado de modulación se actualizó correctamente.');
            } else {
                // Si no se encontró ningún registro para actualizar
                return back()->with('error', 'No se encontraron registros para actualizar con ese número de Thermo.');
            }
        } catch (\Exception $e) {
            // Manejo de errores en caso de fallo de la base de datos
            return back()->with('error', 'Ocurrió un error al actualizar la modulación. ' . $e->getMessage());
        }*/
        return back();
    }


        /**
 * Mostrar formulario administrativo completo para crear exportaciónu
 * (Con todos los campos disponibles para el admin)
 */
public function createAdmin()
{
    // Verificar que sea admin
    if (!in_array(auth()->user()->role, ['admin'])) {
        return redirect()->route('home')
            ->with('error', 'No tienes permiso para acceder a esta sección.');
    }

    $clientes = Cliente::orderBy('nombre_empresa')->get();
    $importadores = Importador::orderBy('nombre')->get();
    $bodegas = Bodega::orderBy('nombre_bodega')->get();
    $aduanas = Aduana::orderBy('nombre_aduana')->get();
    $patentes = Patente::orderBy('numero_patente')->get();
    //$expedientes = Expediente::where('created_at','>=',now()->subMonth())-> orderBy('id')->get();
    // Filtrar expedientes excluyendo "Cancelado" y "Cerrado"
    $expedientes = Expediente::where('created_at', '>=', now()->subMonth())
        ->whereNotIn('estado', ['Cancelado', 'Cerrado'])
        ->orderBy('numero_pedimento')
        ->get();    
    $usuarios = User::where('active', true)
    ->where('role','Documentador')
    ->orderBy('name')->get();

    return view('operaciones.create_admin', compact(
        'clientes',
        'importadores',
        'bodegas',
        'aduanas',
        'patentes',
        'expedientes',
        'usuarios'
    ));
}

/**
 * Guardar exportación desde formulario administrativo completo
 */
public function storeAdmin(Request $request)
{
    // Verificar que sea admin
    if (!in_array(auth()->user()->role, ['admin'])) {
        return redirect()->route('home')
            ->with('error', 'No tienes permiso para realizar esta acción.');
    }

    try {
        $validated = $request->validate([
            'fecha_registro' => 'required|date',
            'referencia'=> 'nullable|string|max:100',
            'cliente_id' => 'required|exists:cliente,id',
            'importador_id' => 'required|exists:importadores,id',
            'nombre_producto' => 'required|string|max:255',
            'bodega_id' => 'required|exists:bodegas,id',
            'num_factura' => 'nullable|string|max:50',
            'aduana_id' => 'required|exists:aduanas,id',
            'patente_id' => 'nullable|exists:patentes,id',
            'pedimento_id' => 'required|exists:expedientes,id',
            'num_thermo' => 'nullable|string|max:50',
            'codigo_alpha' => 'nullable|string|max:20',
            'num_doda' => 'nullable|string|max:50',
            'modulacion' => 'required|in:Desaduanamiento Libre,Reconocimiento Aduanero Concluido',
            'usuario_registro_id' => 'required|exists:users,id',
            'usuario_cierre_id' => 'required|exists:users,id',
            'prioridad' => 'nullable|in:regular,media,alta,urgente',
            'estado' => 'nullable|in:pendiente,proceso,terminado',
            'observaciones' => 'nullable|string',
            'sobrepeso' => 'boolean',
        ], [
            'fecha_registro.required' => 'La fecha_registro es obligatoria',
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'importador_id.required' => 'Debe seleccionar un importador',
            'importador_id.exists' => 'El importador seleccionado no existe',
            'nombre_producto.required' => 'El nombre del producto es obligatorio',
            'bodega_id.required' => 'Debe seleccionar una bodega',
            'bodega_id.exists' => 'La bodega seleccionada no existe',
            'aduana_id.required' => 'Debe seleccionar una aduana',
            'aduana_id.exists' => 'La aduana seleccionada no existe',
            'pedimento_id.required' => 'Debe seleccionar un expediente',
            'pedimento_id.exists' => 'El expediente seleccionado no existe',
            'modulacion.required' => 'Debe seleccionar una modulación',
            'modulacion.in' => 'La modulación debe ser "Desaduanamiento Libre" o "Reconocimiento Aduanero Concluido"',
            'usuario_registro_id.required' => 'Debe seleccionar un documentador',
            'usuario_registro_id.exists' => 'El documentador seleccionado no existe',
            'usuario_cierre_id.required' => 'Debe asignar la exportación a un usuario',
            'usuario_cierre_id.exists' => 'El usuario asignado no existe',
            'prioridad.in' => 'La prioridad debe ser: regular, media, alta o urgente',
            'estado.in' => 'El estado debe ser: pendiente, proceso o terminado',
            //'sobrepeso.numeric' => 'El sobrepeso debe ser un número',
            //'sobrepeso.min' => 'El sobrepeso no puede ser negativo',
        ]);

        // Normalizar el num_thermo si existe (igual que en storetrafico)
        if (!empty($validated['num_thermo'])) {
            $validated['num_thermo'] = preg_replace('/\s+/', '-', $validated['num_thermo']);
            $validated['num_thermo'] = strtoupper($validated['num_thermo']);
        }

        // Normalizar la modulación para que coincida con el formato de la BD
        if ($validated['modulacion'] === 'Desaduanamiento Libre') {
            $validated['modulacion'] = 'DESADUANAMIENTO LIBRE';
        } elseif ($validated['modulacion'] === 'Reconocimiento Aduanero Concluido') {
            $validated['modulacion'] = 'RECONOCIMIENTO ADUANERO CONCLUIDO';
        }

        // Si no se proporciona estado, establecer por defecto
        if (empty($validated['estado'])) {
            $validated['estado'] = 'pendiente';
        }

        // Si no se proporciona prioridad, establecer por defecto
        if (empty($validated['prioridad'])) {
            $validated['prioridad'] = 'regular';
        }

        // Si no se proporciona sobrepeso, establecer por defecto
        if (empty($validated['sobrepeso'])) {
            $validated['sobrepeso'] = false;
        }

        // Crear la exportación
        $operacion = Operacion::create($validated);

        return redirect()->route('operaciones.index')
            ->with('success', 'Exportación registrada correctamente desde panel administrativo.');

    } catch (Exception $e) {
        
        return redirect()->back()
            ->with('error', 'Error al crear la exportación: ' . $e->getMessage())
            ->withInput();
    }
}

//Metodo para actualizar el codigo Alpha (Cuando no lo tiene)
    public function updateAlpha(Request $request, $id)
    {
        try {
            $request->validate([
                'codigo_alpha' => 'required|string|max:20',
            ]);

            $operacion = Operacion::findOrFail($id);

            // Verificar que el usuario sea de tráfico o tenga permisos
            // if (auth()->user()->role !== 'Trafico') {
            //     abort(403, 'No autorizado');
            // }

            $operacion->update([
                'codigo_alpha' => strtoupper($request->codigo_alpha)
            ]);

            // 🔥 Notificar a Documentación que ya está el Alpha
            app(NotificacionService::class)->notificarAlphaActualizado($operacion);

            return redirect()->back()
                ->with('success', 'Código Alpha actualizado correctamente.');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar código Alpha: ' . $e->getMessage());
        }
    }
    public function actualizarCampo(Request $request, NotificacionService $notificacionService)
{
    \Log::info('=== INICIANDO ACTUALIZAR CAMPO ===');
    \Log::info('Datos recibidos:', $request->all());
    \Log::info('Usuario autenticado:', ['id' => auth()->id(), 'name' => auth()->user()->name]);

    $request->validate([
        'operacion_id' => 'required|exists:operaciones,id',
        'campo' => 'required|in:num_thermo,codigo_alpha',
        'valor' => 'nullable|string|max:255',
        'observacion' => 'nullable|string|max:500'
    ]);

    try {
        $operacion = Operacion::findOrFail($request->operacion_id);
        $campo = $request->campo;
        $valorAnterior = $operacion->$campo;
        $nuevoValor = $request->valor ? trim($request->valor) : null;
        
        \Log::info("Valor anterior: '{$valorAnterior}', Nuevo valor: '{$nuevoValor}'");

        // Si el valor no cambió, no hacer nada
        if ($valorAnterior === $nuevoValor) {
            \Log::info('No hay cambios, saliendo...');
            return response()->json([
                'success' => true,
                'message' => 'No se detectaron cambios',
                'accion' => 'sin_cambios'
            ]);
        }
        
        // Determinar el tipo de acción
        if ($valorAnterior === null && $nuevoValor !== null) {
            $accion = 'agregado';
            $tipoNotificacion = 'success';
        } else if ($valorAnterior !== null && $nuevoValor !== null) {
            $accion = 'modificado';
            $tipoNotificacion = 'warning';
        } else if ($valorAnterior !== null && $nuevoValor === null) {
            $accion = 'eliminado';
            $tipoNotificacion = 'error';
        } else {
            $accion = 'sin_cambios';
            $tipoNotificacion = 'info';
        }
        
        \Log::info("Acción detectada: {$accion}");

        // Actualizar el campo
        $operacion->$campo = $nuevoValor;
        $operacion->save();
        
        \Log::info("Campo {$campo} actualizado en la base de datos");

        // Crear notificación si hubo cambio
        if ($accion !== 'sin_cambios') {
            $nombreCampo = $campo === 'num_thermo' ? 'número de thermo' : 'código alpha';
            $nombreCampoFormal = $campo === 'num_thermo' ? 'Número de Thermo' : 'Código Alpha';
            
            $mensaje = "El usuario " . auth()->user()->name . " ha {$accion} el {$nombreCampo} ";
            $mensaje .= "en la operación de la factura **{$operacion->num_factura}**";
            
            if ($request->observacion) {
                $mensaje .= ". Observación: {$request->observacion}";
            }
            
            if ($accion === 'modificado') {
                $mensaje .= " (de '{$valorAnterior}' a '{$nuevoValor}')";
            }
            
            \Log::info("Creando notificación: {$mensaje}");

            // Enviar notificación a usuarios de documentación
            $resultadoNotificacion = $notificacionService->crearNotificacionGlobal(
                roles: ['Documentador', 'Admin'],
                titulo: "{$nombreCampoFormal} {$accion}",
                mensaje: $mensaje,
                tipo: $tipoNotificacion,
                operacionId: $operacion->id
            );

            \Log::info("Resultado de crear notificación: " . ($resultadoNotificacion ? 'ÉXITO' : 'FALLÓ'));
        }
        
        \Log::info('=== FINALIZANDO ACTUALIZAR CAMPO ===');

        return response()->json([
            'success' => true,
            'message' => "Campo actualizado correctamente",
            'accion' => $accion
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error actualizando campo: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar: ' . $e->getMessage()
        ], 500);
    }
}

private function generarReferenciaTentativa(){
        $anioActual=(int) now()->format('y');
        $mesActual=now()->format('m');

        //Buscar el registro del año
        $ref = Referencia::where('anio',$anioActual)->first();
        //Si existe -> el siguiente numero es contador +1
        //Si no existe -> seria el 1
        $siguiente = $ref ? ($ref->contador+1):1;

        //Construccion: año + mes +consecutivo
        //Ejemplo: 25 11 102 -> 2511102
        return sprintf('%02d%02d-%d',$anioActual,$mesActual,$siguiente);
    }

public function asignarBodega(Request $request, NotificacionService $notificacionService)
{
    \Log::info('=== INICIANDO ASIGNAR BODEGA ===');
    \Log::info('Datos recibidos:', $request->all());
    \Log::info('Usuario autenticado:', ['id' => auth()->id(), 'name' => auth()->user()->name]);

    $request->validate([
        'operacion_id' => 'required|exists:operaciones,id',
        'campo' => 'required|in:bodega_id',
        'valor' => 'nullable|exists:bodegas,id', // Asegura que la bodega exista
        'observacion' => 'nullable|string|max:500'
    ]);

    try {
        $operacion = Operacion::with('bodega')->findOrFail($request->operacion_id);
        $campo = $request->campo; // 'bodega_id'
        $valorAnterior = $operacion->bodega_id;
        
        // Obtener nombres de bodegas para los logs
        $nombreBodegaAnterior = $operacion->bodega ? $operacion->bodega->nombre : null;
        $nuevaBodega = null;
        
        if ($request->valor) {
            $nuevaBodega = Bodega::find($request->valor);
        }
        
        $nuevoValor = $request->valor;
        $nombreBodegaNueva = $nuevaBodega ? $nuevaBodega->nombre : null;
        
        \Log::info("Bodega anterior ID: '{$valorAnterior}', Nombre: '{$nombreBodegaAnterior}'");
        \Log::info("Bodega nueva ID: '{$nuevoValor}', Nombre: '{$nombreBodegaNueva}'");

        // Si el valor no cambió, no hacer nada
        if ($valorAnterior == $nuevoValor) {
            \Log::info('No hay cambios en la bodega, saliendo...');
            return response()->json([
                'success' => true,
                'message' => 'No se detectaron cambios',
                'accion' => 'sin_cambios'
            ]);
        }
        
        // Determinar el tipo de acción
        if ($valorAnterior === null && $nuevoValor !== null) {
            $accion = 'asignada';
            $tipoNotificacion = 'success';
        } else if ($valorAnterior !== null && $nuevoValor !== null) {
            $accion = 'modificada';
            $tipoNotificacion = 'warning';
        } else if ($valorAnterior !== null && $nuevoValor === null) {
            $accion = 'removida';
            $tipoNotificacion = 'error';
        } else {
            $accion = 'sin_cambios';
            $tipoNotificacion = 'info';
        }
        
        \Log::info("Acción detectada: {$accion}");

        // Actualizar el campo
        $operacion->bodega_id = $nuevoValor;
        $operacion->save();
        
        \Log::info("Bodega actualizada en la base de datos");

        // Crear notificación si hubo cambio
        if ($accion !== 'sin_cambios') {
            $mensaje = "El usuario " . auth()->user()->name . " ha {$accion} la bodega ";
            $mensaje .= "en la operación de la factura **{$operacion->num_factura}**";
            
            // Agregar detalles de bodegas
            if ($accion === 'asignada') {
                $mensaje .= " a '{$nombreBodegaNueva}'";
            } else if ($accion === 'modificada') {
                $mensaje .= " (de '{$nombreBodegaAnterior}' a '{$nombreBodegaNueva}')";
            } else if ($accion === 'removida') {
                $mensaje .= " (se removió '{$nombreBodegaAnterior}')";
            }
            
            if ($request->observacion) {
                $mensaje .= ". Observación: {$request->observacion}";
            }
            
            \Log::info("Creando notificación: {$mensaje}");

            // Enviar notificación a usuarios de documentación
            $resultadoNotificacion = $notificacionService->crearNotificacionGlobal(
                roles: ['Documentador', 'admin'],
                titulo: "Bodega {$accion}",
                mensaje: $mensaje,
                tipo: $tipoNotificacion,
                operacionId: $operacion->id
            );

            \Log::info("Resultado de crear notificación: " . ($resultadoNotificacion ? 'ÉXITO' : 'FALLÓ'));
        }
        
        \Log::info('=== FINALIZANDO ASIGNAR BODEGA ===');

        return redirect()->back()
                ->with('success', 'Bodega actualizada correctamente.');
        return response()->json([
            'success' => true,
            'message' => "Bodega actualizada correctamente",
            'accion' => $accion,
            'bodega_nombre' => $nombreBodegaNueva
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error actualizando bodega: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la bodega: ' . $e->getMessage()
        ], 500);
    }
}


private function groupTrafficData($collection)
    {
        // 1. Group by Aduana
        $byAduana = $collection->groupBy(function ($item) {
            return $item->aduana->nombre_aduana ?? 'SIN ADUANA';
        });

        // 2. Sort Aduanas (Reynosa First)
        $sortedAduanas = $byAduana->sortBy(function ($items, $key) {
            // Priority 0 for Reynosa, 1 for others
            return stripos($key, 'REYNOSA') !== false ? 0 : 1;
        });

        $result = [];
        foreach ($sortedAduanas as $aduanaName => $items) {
            // 3. Group by Thermo within Aduana
            $byThermo = $items->groupBy(function ($item) {
                $t = $item->num_thermo ?? 'S/N';
                $a = $item->codigo_alpha ?? 'S/A';
                return $t . '|' . $a; // Unique Key
            });

            $result[$aduanaName] = $byThermo;
        }

        return $result;
    }



}