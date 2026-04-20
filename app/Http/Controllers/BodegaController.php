<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    public function index()
    {
        $bodegas = Bodega::orderBy('nombre')->paginate(10);
        return view('bodegas.index', compact('bodegas'));
    }

    public function create()
    {
        return view('bodegas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'domicilio' => 'nullable|string'
        ]);

        Bodega::create($validated);

        return redirect()->route('bodegas.index')
            ->with('success', 'Bodega creada correctamente.');
    }

    public function show(Bodega $bodega)
    {
        return view('bodegas.show', compact('bodega'));
    }

    public function edit(Bodega $bodega)
    {
        return view('bodegas.edit', compact('bodega'));
    }

    public function update(Request $request, Bodega $bodega)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'domicilio' => 'nullable|string'
        ]);

        $bodega->update($validated);

        return redirect()->route('bodegas.show', $bodega)
            ->with('success', 'Bodega actualizada correctamente.');
    }

    public function destroy(Bodega $bodega)
    {
        $bodega->delete();

        return redirect()->route('bodegas.index')
            ->with('success', 'Bodega eliminada correctamente.');
    }
}