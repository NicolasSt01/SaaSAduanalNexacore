<?php

namespace App\Http\Controllers;

use App\Models\Patente;
use App\Models\Aduana;
use Illuminate\Http\Request;

class PatenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $patentes = Patente::latest()->paginate(10);
        return view('patentes.index', compact('patentes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('patentes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'numero' => 'required|unique:patentes,numero|max:20',
            'nombre' => 'required|max:255',
            'rfc' => 'nullable|max:13'
        ]);

        $tenant = auth()->user()->tenant;
        if (!$tenant->canAddResource('patentes')) {
            $info = $tenant->getRecursoInfo('patentes');
            return redirect()->back()->withInput()
                ->with('error', "Has alcanzado el límite de patentes ({$info['uso']}/{$info['limite']}). Contacta a tu administrador para aumentar tu límite.");
        }

        Patente::create($validated);

        return redirect()->route('patentes.index')
            ->with('success', 'Patente registrada exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patente $patente)
    {
        //
        //return view('patentes.show', compact('patente'));
        $patente->load('aduanas');

        return view('patentes.show', compact('patente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patente $patente)
    {
        //
        //return view('patentes.edit', compact('patente'));
        $aduanas = Aduana::all();
        $aduanaSeleccionadas = $patente->aduanas->pluck('id')->toArray();

        return view('patentes.edit', compact('patente', 'aduanas', 'aduanaSeleccionadas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        /*$validated = $request->validate([
            'numero_patente' => 'required|max:20|unique:patentes,numero_patente,' . $patente->id,
            'nombre_agente_aduanal' => 'required|max:255',
            'rfc_agente' => 'nullable|max:13'
        ]);

        $patente->update($validated);

        return redirect()->route('patentes.index')
            ->with('success', 'Patente actualizada!');*/
        $patente = Patente::findOrFail($id);
        $validated = $request->validate([
            'numero' => 'required|max:20|unique:patentes,numero,' . $id,
            'nombre' => 'required|max:255',
            'rfc' => 'nullable|max:13',
            'aduanas' => 'sometimes|array', // Validamos que sea un array
            'aduanas.*' => 'exists:aduanas,id' // Cada ID debe existir en la tabla aduanas
        ]);

        // Actualizar los datos básicos
        $patente->update([
            'numero' => $validated['numero'],
            'nombre' => $validated['nombre'],
            'rfc' => $validated['rfc'] ?? null
        ]);

        // Sincronizar aduanas (si vienen en el request)
        if (isset($validated['aduanas'])) {
            $patente->aduanas()->sync($validated['aduanas']);
        } else {
            $patente->aduanas()->detach();
        }

        return redirect()->route('patentes.show', $patente)
            ->with('success', 'Patente actualizada exitosamente!');


        /*$patente->update($validated);

        // Sincronizar las aduanas seleccionadas
        $patente->aduanas()->sync($request->input('aduanas', []));

        return redirect()->route('patentes.index')
            ->with('success', 'Patente y aduanas asignadas actualizadas!');*/
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patente $patente)
    {
        //
        $patente->delete();
        return redirect()->route('patentes.index')
            ->with('success', 'Patente Eliminada!');
    }
    public function getAduanas($id)
{
    $patente = Patente::with('aduanas')->findOrFail($id);
    return response()->json($patente->aduanas);
}

}
