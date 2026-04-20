<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
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
        //
        $cliente = Cliente::findOrFail($id);
        return view('clientes.show', compact('cliente'));
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
}
