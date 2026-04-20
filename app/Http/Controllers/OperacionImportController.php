<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OperacionImportService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class OperacionImportController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }
    public function index()
    {
        return view('operaciones.importar');
    }

    public function import_OLD(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $service = new OperacionImportService();

            // Cargar el archivo Excel
            $archivo = $request->file('archivo');
            $spreadsheet = IOFactory::load($archivo->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            // Obtener todas las filas (empezando desde la fila 4, ya que la 3 son encabezados)
            $rows = $worksheet->toArray();

            // Los encabezados están en la fila 3 (índice 2)
            $encabezados = array_map('strtolower', array_map('trim', $rows[2]));

            // Procesar cada fila de datos (desde la fila 4 en adelante, índice 3)
            for ($i = 1; $i < count($rows); $i++) {
                $fila = $rows[$i];

                // Saltar filas vacías
                if (empty(array_filter($fila))) {
                    continue;
                }

                // Mapear los datos con los encabezados
                $datos = [];
                foreach ($encabezados as $index => $encabezado) {
                    $datos[$encabezado] = $fila[$index] ?? null;
                }

                // Mapear a los nombres que espera el service
                $filaProcessada = [
                    'referer' => $datos['#referencia'] ?? $datos['referencia'] ?? null,
                    'fecha_registro' => $datos['fecha_registro'] ?? null,
                    'cliente' => $datos['cliente'] ?? null,
                    'importador' => $datos['importador'] ?? null,
                    'producto' => $datos['producto'] ?? null,
                    'bodega' => $datos['bodega'] ?? null,
                    'factura' => $datos['factura'] ?? null,
                    'aduana' => $datos['aduana'] ?? null,
                    'patente' => $datos['patente'] ?? null,
                    'pedimen' => $datos['pedimento'] ?? null,
                    'thermo' => $datos['thermo'] ?? null,
                    'alfa' => $datos['alpha'] ?? null,
                    'num_doda' => $datos['numero de doda'] ?? null,
                    'asignar_a' => $datos['documentador'] ?? null,
                    'sobrepeso' => $datos['permiso de sobrepeso'] ?? null,
                ];

                // Procesar la fila
                $service->procesarFila($filaProcessada, $i);
            }

            // Obtener resultados
            $resultados = $service->getResultados();

            // Generar el archivo log
            $logContenido = $service->generarLog();
            $nombreLog = 'importacion_operaciones_' . now()->format('Y-m-d_His') . '.log';

            // Guardar el log temporalmente
            Storage::disk('local')->put('logs/' . $nombreLog, $logContenido);

            return response()->json([
                'success' => true,
                'mensaje' => 'Importación completada',
                'exitosos' => $resultados['exitosos'],
                'omitidos' => $resultados['omitidos'],
                'errores' => $resultados['errores'],
                'log_file' => $nombreLog,
            ]);

        } catch (\Exception $e) {
            // Log del error para debug
            \Log::error('Error en importación: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ], 500);
        }
    }
    public function import(Request $request)
{
    $request->validate([
        'archivo' => 'required|mimes:xlsx,xls|max:10240',
    ]);

    try {
        $service = new OperacionImportService();
        
        // Cargar el archivo Excel
        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension();
        
        // Crear el reader apropiado
        if ($extension === 'xls') {
            $reader = IOFactory::createReader('Xls');
        } else {
            $reader = IOFactory::createReader('Xlsx');
        }
        
        $spreadsheet = $reader->load($archivo->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Obtener todas las filas
        $rows = $worksheet->toArray();
        
        // Los encabezados están en la fila 1 (índice 0)
        $encabezados = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Procesar cada fila de datos (desde la fila 2 en adelante, índice 1)
        for ($i = 1; $i < count($rows); $i++) {
            $fila = $rows[$i];
            
            // Saltar filas vacías
            if (empty(array_filter($fila))) {
                continue;
            }
            
            // Mapear los datos con los encabezados
            $datos = [];
            foreach ($encabezados as $index => $encabezado) {
                $datos[$encabezado] = $fila[$index] ?? null;
            }
            
            // Mapear a los nombres que espera el service
            $filaProcessada = [
                'referer' => $datos['#referencia'] ?? null,
                'fecha_registro' => $datos['fecha_registro'] ?? null,
                'cliente' => $datos['cliente'] ?? null,
                'importador' => $datos['importador'] ?? null,
                'producto' => $datos['producto'] ?? null,
                'bodega' => $datos['bodega'] ?? null,
                'factura' => $datos['factura'] ?? null,
                'aduana' => $datos['aduana'] ?? null,
                'patente' => $datos['patente'] ?? null,
                'pedimen' => $datos['pedimento'] ?? null,
                'thermo' => $datos['thermo'] ?? null,
                'alfa' => $datos['alpha'] ?? null,
                'num_doda' => $datos['numero de doda'] ?? null,
                'asignar_a' => $datos['documentador'] ?? null,
                'sobrepeso' => $datos['permiso de sobrepeso'] ?? null,
            ];
            
            // Procesar la fila
            $service->procesarFila($filaProcessada, $datos['#referencia'] ?? "Fila " . ($i + 1));
        }

        // Obtener resultados
        $resultados = $service->getResultados();

        // Generar el archivo log
        $logContenido = $service->generarLog();
        $nombreLog = 'importacion_operaciones_' . now()->format('Y-m-d_His') . '.log';
        
        // Guardar el log temporalmente
        Storage::disk('local')->put('logs/' . $nombreLog, $logContenido);

        return response()->json([
            'success' => true,
            'mensaje' => 'Importación completada',
            'exitosos' => $resultados['exitosos'],
            'omitidos' => $resultados['omitidos'],
            'errores' => $resultados['errores'],
            'log_file' => $nombreLog,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al procesar el archivo: ' . $e->getMessage(),
        ], 500);
    }
}

    public function descargarLog($nombreArchivo)
    {
        $rutaArchivo = storage_path('app/logs/' . $nombreArchivo);

        if (!file_exists($rutaArchivo)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($rutaArchivo)->deleteFileAfterSend(true);
    }
}