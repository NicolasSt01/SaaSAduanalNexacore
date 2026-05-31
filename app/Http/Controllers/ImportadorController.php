<?php

namespace App\Http\Controllers;

use App\Models\Importador;
use Illuminate\Http\Request;

class ImportadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $importadores = Importador::orderBy('nombre')->paginate(10);
        return view('importadores.index', compact('importadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('importadores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tax_id' => 'required|string|max:50|unique:importadores',
            'rfc' => 'nullable|string|max:20',
            'pais' => 'nullable|string|max:100',
        ]);

        $tenant = auth()->user()->tenant;
        if (!$tenant->canAddResource('importadores')) {
            $info = $tenant->getRecursoInfo('importadores');
            return redirect()->back()->withInput()
                ->with('error', "Has alcanzado el límite de importadores ({$info['uso']}/{$info['limite']}). Contacta a tu administrador para aumentar tu límite.");
        }

        Importador::create($request->all());

        return redirect()->route('importadores.index')
            ->with('success', 'Importador creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
         $importador = Importador::findOrFail($id);
        return view('importadores.show', compact('importador'));
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit( $id)
   {
    $importador = Importador::findOrFail($id);
    return view('importadores.edit', compact('importador'));
   }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $importador=Importador::findOrFail($id);
        $request->validate([
        'nombre' => 'required|string|max:255',
        'tax_id' => 'required|string|max:50|unique:importadores,tax_id,'.$id,
        'rfc' => 'nullable|string|max:20',
        'pais' => 'nullable|string|max:100',
    ]);

    $importador->update($request->all());

    return redirect()->route('importadores.show', $importador)
        ->with('success', 'Importador actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $importador = Importador::findOrFail($id);


        $importador->delete();

        return redirect()->route('importadores.index')
            ->with('success', 'Importador eliminado correctamente.');
    }
}
