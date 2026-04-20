<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use Exception;
use Illuminate\Http\Request;

class RecorridoController extends Controller
{
    //
    public function store(Request $request)
    {
        try{
            $request->validate([
            'operacion_id' => 'required|exists:operaciones,id',
            'origen' => 'string|max:150',
            'destino' => 'string|max:150',
            'ubicacion' => 'required|string|max:150',
            'estatus' => 'required|in:transito,retraso,frontera',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'observacion' => 'nullable|string',
        ]);

        Recorrido::create($request->all());
        return redirect()->route('trafico.index')->with('success', 'Recorrido registrado');
        //return back()->with('success','Recorrido registrado');
        }
        catch(Exception $e){
            dd($e);
        }
        
    }
}
