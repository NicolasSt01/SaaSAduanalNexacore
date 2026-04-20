<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Directorio;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class DirectorioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $directorios = Directorio::with('cliente')->orderBy('created_at', 'desc')->get();
        $clientes = Cliente::orderBy('nombre')->get(); // Global scope applies

        return view('admin.directorio.index', compact('directorios', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'nombre' => 'required|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'canal_preferido' => 'nullable|string|in:ambos,whatsapp,email',
        ]);

        $data = $request->all();
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['recibe_notificaciones'] = $request->boolean('recibe_notificaciones');
        $data['activo'] = true;

        Directorio::create($data);

        return redirect()->route('directorio.index')->with('success', 'Contacto agregado al directorio exitosamente.');
    }

    public function update(Request $request, string $id)
    {
        $directorio = Directorio::findOrFail($id);

        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'nombre' => 'required|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'canal_preferido' => 'nullable|string|in:ambos,whatsapp,email',
        ]);

        $data = $request->all();
        $data['recibe_notificaciones'] = $request->boolean('recibe_notificaciones');
        $data['activo'] = $request->boolean('activo');

        $directorio->update($data);

        return redirect()->route('directorio.index')->with('success', 'Contacto del directorio actualizado.');
    }

    public function destroy(string $id)
    {
        $directorio = Directorio::findOrFail($id);
        $directorio->delete();

        return redirect()->route('directorio.index')->with('success', 'Contacto eliminado exitosamente.');
    }

    /**
     * API: Obtener contactos de un cliente específico.
     * Usado por el formulario de envío de reportes.
     * Incluye contactos activos e inactivos para permitir selección flexible.
     */
    public function getContactosByCliente($clienteId)
    {
        $tenantId = auth()->user()->tenant_id;

        \Log::info('API Directorio - Buscando contactos', [
            'cliente_id' => $clienteId,
            'tenant_id' => $tenantId,
        ]);

        // Obtener todos los contactos (activos e inactivos) para permitir selección
        $contactos = Directorio::where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'puesto', 'correo', 'telefono', 'whatsapp', 'activo']);

        \Log::info('API Directorio - Resultados', [
            'count' => $contactos->count(),
        ]);

        return response()->json([
            'success' => true,
            'contactos' => $contactos,
        ]);
    }
}
