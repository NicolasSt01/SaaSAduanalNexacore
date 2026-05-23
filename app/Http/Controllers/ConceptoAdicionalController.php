<?php

namespace App\Http\Controllers;

use App\Models\ConceptoAdicional;
use Illuminate\Http\Request;

class ConceptoAdicionalController extends Controller
{
    /**
     * Almacenar un nuevo concepto adicional
     */
    public function store_OLD(Request $request)
    {
        $validated = $request->validate([
            'operacion_id' => 'required|exists:operaciones,id',
            'tipo_concepto' => 'required|string|max:100',
            'ambito' => 'required|in:operacion,camion',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500'
        ], [
            'operacion_id.required' => 'La operación es requerida',
            'operacion_id.exists' => 'La operación no existe',
            'tipo_concepto.required' => 'El tipo de concepto es requerido',
            'ambito.required' => 'El ámbito es requerido',
            'ambito.in' => 'El ámbito debe ser operacion o camion',
            'monto.required' => 'El monto es requerido',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.min' => 'El monto debe ser mayor o igual a 0',
        ]);

        try {
            ConceptoAdicional::create($validated);

            return redirect()->back()->with('success', 'Concepto adicional registrado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al registrar el concepto: ' . $e->getMessage());
        }
    }
    /**
     * Almacenar un nuevo concepto adicional con archivo opcional
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operacion_id' => 'required|exists:operaciones,id',
            'tipo_concepto' => 'required|string|max:100',
            'ambito' => 'required|in:operacion,camion',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500',
            'archivo' => 'nullable|file|max:51200', // 50MB máximo, OPCIONAL
        ], [
            'operacion_id.required' => 'La operación es requerida',
            'operacion_id.exists' => 'La operación no existe',
            'tipo_concepto.required' => 'El tipo de concepto es requerido',
            'ambito.required' => 'El ámbito es requerido',
            'ambito.in' => 'El ámbito debe ser operacion o camion',
            'monto.required' => 'El monto es requerido',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.min' => 'El monto debe ser mayor o igual a 0',
            'archivo.file' => 'El archivo no es válido',
            'archivo.max' => 'El archivo no debe superar los 50MB',
        ]);

        try {
            // Crear el concepto adicional
            $concepto = ConceptoAdicional::create([
                'operacion_id' => $validated['operacion_id'],
                'tipo_concepto' => $validated['tipo_concepto'],
                'ambito' => $validated['ambito'],
                'monto' => $validated['monto'],
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            // Si viene archivo, guardarlo y asociarlo al concepto
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $path = $file->store('documentos/conceptos', 'r2');

                $nombreDocumento = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                \App\Models\Documento::create([
                    'concepto_adicional_id' => $concepto->id,
                    'nombre' => $nombreDocumento,
                    'ruta' => $path,
                    'tipo_documento' => 'concepto_adicional',
                ]);
            }

            return redirect()->back()->with('success', 'Concepto adicional registrado correctamente' .
                ($request->hasFile('archivo') ? ' con archivo adjunto' : ''));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al registrar el concepto: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un concepto adicional
     */
    public function destroy(ConceptoAdicional $concepto)
    {
        try {
            $concepto->delete();

            return redirect()->back()->with('success', 'Concepto adicional eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el concepto: ' . $e->getMessage());
        }
    }

    /**
     * Obtener conceptos de un camión específico (AJAX opcional)
     */
    public function getConceptosCamion(Request $request)
    {
        $request->validate([
            'num_thermo' => 'required|string',
            'codigo_alpha' => 'required|string',
            'fecha_cruce' => 'required|date'
        ]);

        // Buscar la primera operación del camión
        $operacion = \App\Models\Operacion::where('num_thermo', $request->num_thermo)
            ->where('codigo_alpha', $request->codigo_alpha)
            ->whereDate('fecha_cruce', $request->fecha_cruce)
            ->first();

        if (!$operacion) {
            return response()->json(['conceptos' => []]);
        }

        $conceptos = $operacion->conceptosAdicionales()
            ->where('ambito', 'camion')
            ->get();

        return response()->json(['conceptos' => $conceptos]);
    }

    /**
     * Actualizar un concepto adicional
     */
    public function update(Request $request, ConceptoAdicional $concepto)
    {
        $validated = $request->validate([
            'tipo_concepto' => 'sometimes|required|string|max:100',
            'monto' => 'sometimes|required|numeric|min:0',
            'descripcion' => 'nullable|string|max:500'
        ]);

        try {
            $concepto->update($validated);

            return redirect()->back()->with('success', 'Concepto actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el concepto: ' . $e->getMessage());
        }
    }

    /**
     * Obtener resumen de conceptos para finanzas
     */
    public function resumenFinanzas($expedienteId)
    {
        $operaciones = \App\Models\Operacion::where('pedimento_id', $expedienteId)
            ->with('conceptosAdicionales')
            ->get();

        // Agrupar por camión (thermo + alpha + fecha)
        $camiones = $operaciones->groupBy(function($op) {
            return $op->fecha_cruce . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
        });

        $resumen = [
            'total_operaciones' => $operaciones->count(),
            'rojos' => $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
            'verdes' => $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
            'conceptos_camion' => [],
            'conceptos_operacion' => [],
            'total_conceptos_camion' => 0,
            'total_conceptos_operacion' => 0
        ];

        // Contar conceptos de camión (sin duplicar por camión)
        foreach($camiones as $grupo) {
            $conceptosCamion = $grupo->first()->conceptosAdicionales
                ->where('ambito', 'camion');
            
            foreach($conceptosCamion as $concepto) {
                $key = $concepto->tipo_concepto;
                
                if (!isset($resumen['conceptos_camion'][$key])) {
                    $resumen['conceptos_camion'][$key] = [
                        'nombre' => ucfirst(str_replace('_', ' ', $key)),
                        'cantidad' => 0,
                        'monto_total' => 0
                    ];
                }
                
                $resumen['conceptos_camion'][$key]['cantidad']++;
                $resumen['conceptos_camion'][$key]['monto_total'] += $concepto->monto;
                $resumen['total_conceptos_camion'] += $concepto->monto;
            }
        }

        // Contar conceptos por operación
        foreach($operaciones as $operacion) {
            $conceptosOperacion = $operacion->conceptosAdicionales
                ->where('ambito', 'operacion');
            
            foreach($conceptosOperacion as $concepto) {
                $key = $concepto->tipo_concepto;
                
                if (!isset($resumen['conceptos_operacion'][$key])) {
                    $resumen['conceptos_operacion'][$key] = [
                        'nombre' => ucfirst(str_replace('_', ' ', $key)),
                        'cantidad' => 0,
                        'monto_total' => 0
                    ];
                }
                
                $resumen['conceptos_operacion'][$key]['cantidad']++;
                $resumen['conceptos_operacion'][$key]['monto_total'] += $concepto->monto;
                $resumen['total_conceptos_operacion'] += $concepto->monto;
            }
        }

        return $resumen;
    }
}