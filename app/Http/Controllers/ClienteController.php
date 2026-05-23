<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Expediente;
use App\Services\DocumentoStorageService;
use App\Services\SistemaNotificacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    protected DocumentoStorageService $storageService;
    protected SistemaNotificacionesService $sistemaNotificaciones;

    public function __construct(
        DocumentoStorageService $storageService,
        SistemaNotificacionesService $sistemaNotificaciones
    ) {
        $this->middleware('auth');
        $this->storageService = $storageService;
        $this->sistemaNotificaciones = $sistemaNotificaciones;
    }
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        //Buscador de clientes.
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('rfc', $search)      // coincidencia exacta
                    ->orWhere('tax_id', $search);  // coincidencia exacta
            });
            $clientes = $query->latest()->paginate(5);
            return view('clientes.index', compact('clientes'));
        } else {

            $clientes = Cliente::latest()->paginate(10);
            return view('clientes.index', compact('clientes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $validated = $request->validate([
                'nombre' => 'required|max:255',
                'rfc' => 'nullable|unique:cliente|max:13',
                'tax_id' => 'nullable|unique:cliente|max:50',
                'correo' => 'required|email',
                'telefono' => 'nullable|max:20',
                'direccion' => 'nullable|max:255'
            ]);

            // Si RFC está vacío y Tax ID tiene valor → copiarlo a RFC
            if (empty($validated['rfc']) && !empty($validated['tax_id'])) {
                $validated['rfc'] = $validated['tax_id'];
            }

            // Si Tax ID está vacío y RFC tiene valor → copiarlo a Tax ID
            if (empty($validated['tax_id']) && !empty($validated['rfc'])) {
                $validated['tax_id'] = $validated['rfc'];
            }

            //dd($validated);
            Cliente::create($validated);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado exitosamente!');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with('documentosMaestros')->findOrFail($id);
        $maestroDocs = Expediente::MAESTRO_DOCS;
        return view('clientes.show', compact('cliente', 'maestroDocs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        //
        $validated = $request->validate([
            'nombre' => 'required|max:255',
            'rfc' => 'required|max:13|unique:cliente,rfc,' . $cliente->id,
            'tax_id' => 'nullable|string|max:50',
            'correo' => 'required|email',
            'telefono' => 'nullable|max:20',
            'direccion' => 'nullable|max:255'
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
        $cliente->delete();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado!');
    }
    public function admincliente()
    {
        return view('clientes.dashboard');
    }

    /**
     * Subir documento maestro Art. 36-A al perfil del cliente.
     */
    public function subirDocumento(Request $request, Cliente $cliente)
    {
        try {
            $request->validate([
                'archivo' => 'required|file|max:51200',
                'tipo_documento' => 'required|string|in:' . implode(',', array_keys(Expediente::MAESTRO_DOCS)),
            ]);

            $file = $request->file('archivo');
            $tenantId = auth()->user()->tenant_id;
            $tipoDocumento = $request->tipo_documento;
            $nombreArchivo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Eliminar documento existente del mismo tipo para este cliente
            $existente = Documento::where('cliente_id', $cliente->id)
                ->where('tipo_documento', $tipoDocumento)
                ->whereNull('pedimento_id')
                ->first();
            if ($existente) {
                if ($existente->en_r2) {
                    $this->storageService->delete($existente->ruta);
                }
                $existente->delete();
            }

            $meta = $this->storageService->upload(
                $file,
                $tenantId,
                null,
                $tipoDocumento,
                $nombreArchivo,
                $cliente->id
            );

            // Calcular fecha de vencimiento para CSF
            $fechaVencimiento = null;
            if ($tipoDocumento === 'rfc') {
                $fechaVencimiento = now()->addMonthNoOverflow()->startOfMonth()->addDays(4);
            }

            Documento::create([
                'tenant_id' => $tenantId,
                'cliente_id' => $cliente->id,
                'nombre' => $nombreArchivo,
                'ruta' => $meta['path'],
                'url_archivo' => $meta['url'],
                'peso' => $meta['peso'],
                'extension' => $meta['extension'],
                'tipo_documento' => $tipoDocumento,
                'fecha_vencimiento' => $fechaVencimiento,
            ]);

            return back()->with('success', Expediente::MAESTRO_DOCS[$tipoDocumento] . ' subido correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al subir documento de cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->with('error', 'Error al subir documento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar documento maestro del cliente.
     */
    public function eliminarDocumento(Request $request, Cliente $cliente, Documento $documento)
    {
        try {
            if ($documento->cliente_id !== $cliente->id) {
                abort(403);
            }

            if ($documento->en_r2) {
                $this->storageService->delete($documento->ruta);
            }

            $tipoDocumento = $documento->tipo_documento;
            $documento->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Documento eliminado correctamente.']);
            }

            return back()->with('success', Expediente::MAESTRO_DOCS[$tipoDocumento] . ' eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar documento de cliente', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Error al eliminar documento: ' . $e->getMessage());
        }
    }
}
