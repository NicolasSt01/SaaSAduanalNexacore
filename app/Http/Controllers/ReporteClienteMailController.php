<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarReporteClienteJob;
use App\Models\Cliente;
use App\Models\Operacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\ReporteClienteService;
use DB;
use App\Models\ReporteAcceso;

class ReporteClienteMailController extends Controller
{
    protected $reporteService;
    public function index()
    {
        $clientes = Cliente::where('tenant_id', auth()->user()->tenant_id)->orderBy('nombre')->get();

        return view('reportes.cliente-mail', [
            'clientes' => $clientes,
            'desde' => Carbon::now()->startOfWeek()->format('Y-m-d'),
            'hasta' => Carbon::now()->endOfWeek()->format('Y-m-d'),
        ]);
    }

    public function enviarOLD(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        EnviarReporteClienteJob::dispatch(
            $request->cliente_id,
            $request->desde,
            $request->hasta
        );

        return back()->with('success', 'El reporte fue enviado correctamente.');
    }

    public function preview_old(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'cliente_id' => 'required|exists:cliente,id',
                'desde' => 'required|date',
                'hasta' => 'required|date|after_or_equal:desde'
            ]);

            // Generar el reporte
            $datos = $this->reporteService->generar(
                (int) $validated['cliente_id'],
                $validated['desde'],
                $validated['hasta']
            );

            // Preparar respuesta
            return response()->json([
                'success' => true,
                'datos' => [
                    'cliente' => [
                        'nombre' => $datos['cliente']->nombre_empresa,
                        'email' => $datos['cliente']->email ?? 'No especificado'
                    ],
                    'periodo' => [
                        'desde' => $datos['desde'],
                        'hasta' => $datos['hasta'],
                        'semana' => $datos['semana']
                    ],
                    'estadisticas' => [
                        'total' => $datos['total'],
                        'greens' => $datos['greens'],
                        'reds' => $datos['reds'],
                        'sobrepesos' => $datos['totalSobrepesos']
                    ],
                    'porAduana' => $datos['porAduana']->toArray(),
                    'historialMeses' => $datos['historialMeses'],
                    'topImportadores' => $datos['topImportadores']->toArray()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error en preview de reporte', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
        }
    }
    public function preview_OLD2(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'cliente_id' => 'required|exists:cliente,id',
                'desde' => 'required|date',
                'hasta' => 'required|date|after_or_equal:desde'
            ]);

            // Instanciar el servicio directamente
            $reporteService = new ReporteClienteService();

            // Generar el reporte
            $datos = $reporteService->generar(
                (int) $validated['cliente_id'],
                $validated['desde'],
                $validated['hasta']
            );

            // Preparar respuesta
            return response()->json([
                'success' => true,
                'datos' => [
                    'cliente' => [
                        'nombre' => $datos['cliente']->nombre_empresa,
                        'email' => $datos['cliente']->email ?? 'No especificado'
                    ],
                    'periodo' => [
                        'desde' => $datos['desde'],
                        'hasta' => $datos['hasta'],
                        'semana' => $datos['semana']
                    ],
                    'estadisticas' => [
                        'total' => $datos['total'],
                        'greens' => $datos['greens'],
                        'reds' => $datos['reds'],
                        'sobrepesos' => $datos['totalSobrepesos']
                    ],
                    'porAduana' => $datos['porAduana']->toArray(),
                    'historialMeses' => $datos['historialMeses'],
                    'topImportadores' => $datos['topImportadores']->toArray()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error en preview de reporte', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
        }
    }
    public function preview(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'cliente_id' => 'required|exists:cliente,id',
                'desde' => 'required|date',
                'hasta' => 'required|date|after_or_equal:desde'
            ]);

            $clienteId = (int) $validated['cliente_id'];
            $desde = $validated['desde'];
            $hasta = $validated['hasta'];

            // Obtener cliente
            $cliente = Cliente::findOrFail($clienteId);

            // ===================================
            // ESTADÍSTICAS GENERALES
            // ===================================

            $total = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->count();

            $greens = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->where('modulacion', 'DESADUANAMIENTO LIBRE')
                ->count();

            $reds = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
                ->count();

            // Sobrepesos (si tienes este campo)
            $totalSobrepesos = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->whereRaw('1=0')
                ->count();

            // ===================================
            // POR ADUANA
            // ===================================

            $porAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
                ->select('aduanas.nombre as nombre', DB::raw('count(*) as total'))
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->groupBy('aduanas.nombre')
                ->orderBy('total', 'desc')
                ->get();

            // Desglose por aduana (Verdes y Rojos)
            $verdesPorAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
                ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->where('modulacion', 'DESADUANAMIENTO LIBRE')
                ->groupBy('aduanas.nombre')
                ->get();

            $rojosPorAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
                ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
                ->groupBy('aduanas.nombre')
                ->get();

            // ===================================
            // HISTÓRICO POR MES (AÑO ACTUAL)
            // ===================================

            $historial = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
                DB::raw('YEAR(fecha_cruce_estimada) as anio'),
                DB::raw('MONTH(fecha_cruce_estimada) as mes'),
                DB::raw('count(*) as total')
            )
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear()
                ])
                ->groupBy('anio', 'mes')
                ->get();

            // Organizar para gráfica (12 meses)
            $meses = range(1, 12);
            $historialMeses = [];
            foreach ($meses as $m) {
                $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
            }

            // ===================================
            // TOP IMPORTADORES
            // ===================================

            /*$topImportadores = Operacion::select('importador_id', DB::raw('count(*) as total'))
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->whereNotNull('importador_id')
                ->groupBy('importador_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();*/

            $tramitesPorImportador = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join(
                'importadores',
                'operaciones.importador_id',
                '=',
                'importadores.id'
            )
                ->select(
                    'importadores.nombre as importador',
                    DB::raw('count(*) as total')
                )
                ->where('operaciones.cliente_id', $clienteId)
                ->whereBetween('operaciones.fecha_cruce_estimada', [$desde, $hasta])
                ->groupBy('importadores.nombre')
                ->orderByDesc('total')
                ->get();

            $topImportadores = $tramitesPorImportador->take(8);


            // ===================================
            // CALENDARIO - Trámites por día
            // ===================================

            $desdeCarbon = Carbon::parse($desde);
            $hastaCarbon = Carbon::parse($hasta);

            // Obtener conteo real por día en el rango
            $rawPorDia = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
                DB::raw('DATE(fecha_cruce_estimada) as fecha'),
                DB::raw('count(*) as total')
            )
                ->where('cliente_id', $clienteId)
                ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
                ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
                ->pluck('total', 'fecha');

            // Generar calendario completo
            $tramitesPorDia = [];
            $cursor = $desdeCarbon->copy();

            while ($cursor <= $hastaCarbon) {
                $fecha = $cursor->format('Y-m-d');
                $tramitesPorDia[] = [
                    'fecha_cruce_estimada' => $fecha,
                    'dia' => $cursor->day,
                    'mes' => $cursor->format('M'),
                    'total' => $rawPorDia[$fecha] ?? 0,
                    'dia_semana' => $cursor->locale('es')->dayName
                ];
                $cursor->addDay();
            }

            // ===================================
            // CALENDARIO MENSUAL (para vista tipo calendario)
            // ===================================

            // Usamos el mes de la fecha final del rango solicitado
            $inicioMes = $hastaCarbon->copy()->startOfMonth();
            $finMes = $hastaCarbon->copy()->endOfMonth();

            // Conteo por día del mes
            $rawCalendario = Operacion::where('tenant_id', auth()->user()->tenant_id)
                ->where('cliente_id', $clienteId)
                ->select(
                    DB::raw('DATE(fecha_cruce_estimada) as fecha'),
                    DB::raw('count(*) as total')
                )
                ->whereBetween('fecha_cruce_estimada', [$inicioMes, $finMes])
                ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
                ->pluck('total', 'fecha');

            // Estructura tipo calendario (por semanas)
            $calendario = [];
            $inicioCalendario = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
            $finCalendario = $finMes->copy()->endOfWeek(Carbon::SUNDAY);
            $cursor = $inicioCalendario->copy();

            while ($cursor <= $finCalendario) {
                $semana = [];
                for ($i = 0; $i < 7; $i++) {
                    $fecha = $cursor->format('Y-m-d');
                    $semana[] = [
                        'fecha_cruce_estimada' => $fecha,
                        'dia' => $cursor->day,
                        'mes' => $cursor->month,
                        'total' => $rawCalendario[$fecha] ?? 0,
                        'actual' => $cursor->month === $inicioMes->month,
                        'dia_semana' => $cursor->locale('es')->shortDayName
                    ];
                    $cursor->addDay();
                }
                $calendario[] = $semana;
            }

            // ===================================
            // PREPARAR RESPUESTA
            // ===================================

            return response()->json([
                'success' => true,
                'datos' => [
                    'cliente' => [
                        'nombre' => $cliente->nombre,
                        'nombre_empresa' => $cliente->nombre_empresa,
                        'email' => $cliente->email ?? 'No especificado'
                    ],
                    'periodo' => [
                        'desde' => $desde,
                        'hasta' => $hasta
                    ],
                    'estadisticas' => [
                        'total' => $total,
                        'greens' => $greens,
                        'reds' => $reds,
                        'sobrepesos' => $totalSobrepesos
                    ],
                    'porAduana' => $porAduana->toArray(),
                    'verdesPorAduana' => $verdesPorAduana->toArray(),
                    'rojosPorAduana' => $rojosPorAduana->toArray(),
                    'historialMeses' => $historialMeses,
                    'topImportadores' => $topImportadores->toArray(),
                    'tramitesPorDia' => $tramitesPorDia,
                    'calendario' => $calendario
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error en preview de reporte', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
        }
    }
    public function generarPDF(Request $request)
    {
        try {
            $datos = $request->input('datos');

            if (!$datos) {
                return response()->json(['success' => false, 'message' => 'Sin datos'], 400);
            }

            \Log::info('Generando PDF con datos', ['cliente' => $datos['cliente']['nombre']]);

            // Generar URLs de gráficos usando QuickChart.io
            $chartUrls = $this->generarChartUrls($datos);

            \Log::info('Charts generados', ['charts' => array_keys($chartUrls)]);

            $pdf = \PDF::loadView('reportes.pdf-reporte', [
                'datos' => $datos,
                'charts' => $chartUrls,
            ]);

            $pdf->setPaper('letter', 'portrait');

            return $pdf->download('reporte_' . str_replace(' ', '_', $datos['cliente']['nombre']) . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error generando PDF:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generarChartUrls_old($datos)
    {
        $urls = [];

        try {
            // 1. Gráfico Greens vs Reds
            $urls['greensReds'] = $this->quickChart([
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Greens', 'Reds'],
                    'datasets' => [
                        [
                            'data' => [
                                (int) $datos['estadisticas']['greens'],
                                (int) $datos['estadisticas']['reds']
                            ],
                            'backgroundColor' => ['#28a745', '#dc3545'],
                            'borderWidth' => 0
                        ]
                    ]
                ],
                'options' => [
                    'plugins' => [
                        'legend' => [
                            'display' => true,
                            'position' => 'bottom'
                        ],
                        'datalabels' => [
                            'color' => '#fff',
                            'font' => [
                                'weight' => 'bold',
                                'size' => 16
                            ]
                        ]
                    ]
                ]
            ]);

            // 2. Gráfico Por Aduana
            if (isset($datos['porAduana']) && !empty($datos['porAduana'])) {
                $urls['aduanas'] = $this->quickChart([
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => array_column($datos['porAduana'], 'nombre'),
                        'datasets' => [
                            [
                                'data' => array_column($datos['porAduana'], 'total'),
                                'backgroundColor' => [
                                    '#007bff',
                                    '#28a745',
                                    '#ffc107',
                                    '#dc3545',
                                    '#17a2b8',
                                    '#6f42c1',
                                    '#e83e8c',
                                    '#fd7e14'
                                ],
                                'borderWidth' => 0
                            ]
                        ]
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => [
                                'display' => true,
                                'position' => 'right'
                            ]
                        ]
                    ]
                ]);
            }

            // 3. Desglose Aduana (Verdes vs Rojos)
            if (isset($datos['verdesPorAduana']) && isset($datos['rojosPorAduana'])) {
                $aduanas = array_unique(array_merge(
                    array_column($datos['verdesPorAduana'], 'aduana'),
                    array_column($datos['rojosPorAduana'], 'aduana')
                ));

                $verdesData = [];
                $rojosData = [];

                foreach ($aduanas as $aduana) {
                    $verde = collect($datos['verdesPorAduana'])->firstWhere('aduana', $aduana);
                    $rojo = collect($datos['rojosPorAduana'])->firstWhere('aduana', $aduana);

                    $verdesData[] = isset($verde['total']) ? (int) $verde['total'] : 0;
                    $rojosData[] = isset($rojo['total']) ? (int) $rojo['total'] : 0;
                }

                $urls['desglose'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_values($aduanas),
                        'datasets' => [
                            [
                                'label' => 'Greens',
                                'data' => $verdesData,
                                'backgroundColor' => '#28a745'
                            ],
                            [
                                'label' => 'Reds',
                                'data' => $rojosData,
                                'backgroundColor' => '#dc3545'
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ]
                    ]
                ]);
            }

            // 4. Histórico Anual
            if (isset($datos['historialMeses']) && !empty($datos['historialMeses'])) {
                $mesesData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $mesesData[] = isset($datos['historialMeses'][$i]) ? (int) $datos['historialMeses'][$i] : 0;
                }

                $urls['historico'] = $this->quickChart([
                    'type' => 'line',
                    'data' => [
                        'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        'datasets' => [
                            [
                                'label' => 'Trámites',
                                'data' => $mesesData,
                                'borderColor' => '#007bff',
                                'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                                'fill' => true,
                                'tension' => 0.4,
                                'borderWidth' => 3
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ]
                    ]
                ]);
            }

            // 5. Trámites Diarios
            if (isset($datos['tramitesPorDia']) && !empty($datos['tramitesPorDia'])) {
                $labels = [];
                $values = [];

                foreach ($datos['tramitesPorDia'] as $dia) {
                    $fecha = \Carbon\Carbon::parse($dia['fecha_cruce_estimada']);
                    $labels[] = $fecha->format('d M');
                    $values[] = (int) $dia['total'];
                }

                $urls['diarios'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Operaciones',
                                'data' => $values,
                                'backgroundColor' => '#007bff'
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ]
                    ]
                ]);
            }

            // 6. Top Importadores
            if (isset($datos['topImportadores']) && !empty($datos['topImportadores'])) {
                $urls['importadores'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_column($datos['topImportadores'], 'importador'),
                        'datasets' => [
                            [
                                'label' => 'Trámites',
                                'data' => array_column($datos['topImportadores'], 'total'),
                                'backgroundColor' => '#007bff'
                            ]
                        ]
                    ],
                    'options' => [
                        'indexAxis' => 'y',
                        'scales' => [
                            'x' => [
                                'beginAtZero' => true
                            ]
                        ]
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error generando charts:', ['error' => $e->getMessage()]);
        }

        return $urls;
    }

    private function quickChart_old($config)
    {
        $config['width'] = 500;
        $config['height'] = 300;
        $config['backgroundColor'] = 'white';

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($config));

        try {
            // Descargar la imagen
            $imageData = file_get_contents($url);

            if ($imageData === false) {
                \Log::error('No se pudo descargar la imagen de QuickChart');
                return null;
            }

            // Convertir a base64
            $base64 = 'data:image/png;base64,' . base64_encode($imageData);

            \Log::info('Imagen descargada y convertida a base64');

            return $base64;

        } catch (\Exception $e) {
            \Log::error('Error descargando imagen:', ['error' => $e->getMessage()]);
            return null;
        }
    }
    private function quickChart($config, $width = 500, $height = 300)
    {
        $config['width'] = $width;
        $config['height'] = $height;
        $config['backgroundColor'] = 'white';
        $config['devicePixelRatio'] = 1.5; // Para mejor calidad

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($config));

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'Mozilla/5.0'
                ]
            ]);

            $imageData = file_get_contents($url, false, $context);

            if ($imageData === false) {
                \Log::error('No se pudo descargar la imagen de QuickChart');
                return null;
            }

            $base64 = 'data:image/png;base64,' . base64_encode($imageData);

            \Log::info('Imagen descargada y convertida a base64');

            return $base64;

        } catch (\Exception $e) {
            \Log::error('Error descargando imagen:', ['error' => $e->getMessage()]);
            return null;
        }
    }
    private function generarChartUrls($datos)
    {
        $urls = [];

        try {
            // 1. Gráfico Greens vs Reds (tamaño original)
            $urls['greensReds'] = $this->quickChart([
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Greens', 'Reds'],
                    'datasets' => [
                        [
                            'data' => [
                                (int) $datos['estadisticas']['greens'],
                                (int) $datos['estadisticas']['reds']
                            ],
                            'backgroundColor' => ['#28a745', '#dc3545'],
                            'borderWidth' => 0
                        ]
                    ]
                ],
                'options' => [
                    'plugins' => [
                        'legend' => [
                            'display' => true,
                            'position' => 'bottom'
                        ],
                        'datalabels' => [
                            'color' => '#fff',
                            'font' => [
                                'weight' => 'bold',
                                'size' => 16
                            ]
                        ]
                    ]
                ]
            ], 500, 300); // Tamaño: 500x300

            // 2. Gráfico Por Aduana (tamaño original)
            if (isset($datos['porAduana']) && !empty($datos['porAduana'])) {
                $urls['aduanas'] = $this->quickChart([
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => array_column($datos['porAduana'], 'nombre'),
                        'datasets' => [
                            [
                                'data' => array_column($datos['porAduana'], 'total'),
                                'backgroundColor' => [
                                    '#007bff',
                                    '#28a745',
                                    '#ffc107',
                                    '#dc3545',
                                    '#17a2b8',
                                    '#6f42c1',
                                    '#e83e8c',
                                    '#fd7e14'
                                ],
                                'borderWidth' => 0
                            ]
                        ]
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => [
                                'display' => true,
                                'position' => 'right',
                                'labels' => [
                                    'font' => ['size' => 10],
                                    'padding' => 8,
                                    'boxWidth' => 12
                                ]
                            ]
                        ]
                    ]
                ], 500, 300); // Tamaño: 500x300
            }

            // 3. Desglose Aduana (MÁS PEQUEÑO)
            if (isset($datos['verdesPorAduana']) && isset($datos['rojosPorAduana'])) {
                $aduanas = array_unique(array_merge(
                    array_column($datos['verdesPorAduana'], 'aduana'),
                    array_column($datos['rojosPorAduana'], 'aduana')
                ));

                $verdesData = [];
                $rojosData = [];

                foreach ($aduanas as $aduana) {
                    $verde = collect($datos['verdesPorAduana'])->firstWhere('aduana', $aduana);
                    $rojo = collect($datos['rojosPorAduana'])->firstWhere('aduana', $aduana);

                    $verdesData[] = isset($verde['total']) ? (int) $verde['total'] : 0;
                    $rojosData[] = isset($rojo['total']) ? (int) $rojo['total'] : 0;
                }

                $urls['desglose'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_values($aduanas),
                        'datasets' => [
                            [
                                'label' => 'Greens',
                                'data' => $verdesData,
                                'backgroundColor' => '#28a745'
                            ],
                            [
                                'label' => 'Reds',
                                'data' => $rojosData,
                                'backgroundColor' => '#dc3545'
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ],
                        'plugins' => [
                            'legend' => [
                                'display' => true,
                                'labels' => [
                                    'font' => ['size' => 11]
                                ]
                            ]
                        ]
                    ]
                ], 400, 200); // MÁS ANCHO Y MÁS BAJO: 700x250
            }

            // 4. Histórico Anual (MÁS PEQUEÑO)
            if (isset($datos['historialMeses']) && !empty($datos['historialMeses'])) {
                $mesesData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $mesesData[] = isset($datos['historialMeses'][$i]) ? (int) $datos['historialMeses'][$i] : 0;
                }

                $urls['historico'] = $this->quickChart([
                    'type' => 'line',
                    'data' => [
                        'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        'datasets' => [
                            [
                                'label' => 'Trámites',
                                'data' => $mesesData,
                                'borderColor' => '#007bff',
                                'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                                'fill' => true,
                                'tension' => 0.4,
                                'borderWidth' => 3,
                                'pointRadius' => 4,
                                'pointBackgroundColor' => '#007bff'
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ],
                        'plugins' => [
                            'legend' => [
                                'display' => false
                            ]
                        ]
                    ]
                ], 700, 250); // MÁS ANCHO Y MÁS BAJO: 700x250
            }

            // 5. Trámites Diarios (MÁS PEQUEÑO)
            if (isset($datos['tramitesPorDia']) && !empty($datos['tramitesPorDia'])) {
                $labels = [];
                $values = [];

                foreach ($datos['tramitesPorDia'] as $dia) {
                    $fecha = \Carbon\Carbon::parse($dia['fecha_cruce_estimada']);
                    $labels[] = $fecha->format('d M');
                    $values[] = (int) $dia['total'];
                }

                $urls['diarios'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => $labels,
                        'datasets' => [
                            [
                                'label' => 'Operaciones',
                                'data' => $values,
                                'backgroundColor' => '#007bff'
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true
                            ]
                        ],
                        'plugins' => [
                            'legend' => [
                                'display' => false
                            ]
                        ]
                    ]
                ], 700, 250); // MÁS ANCHO Y MÁS BAJO: 700x250
            }

            // 6. Top Importadores (MÁS PEQUEÑO pero más alto por ser horizontal)
            if (isset($datos['topImportadores']) && !empty($datos['topImportadores'])) {
                $urls['importadores'] = $this->quickChart([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_column($datos['topImportadores'], 'importador'),
                        'datasets' => [
                            [
                                'label' => 'Trámites',
                                'data' => array_column($datos['topImportadores'], 'total'),
                                'backgroundColor' => '#007bff'
                            ]
                        ]
                    ],
                    'options' => [
                        'indexAxis' => 'y',
                        'scales' => [
                            'x' => [
                                'beginAtZero' => true
                            ]
                        ],
                        'plugins' => [
                            'legend' => [
                                'display' => false
                            ]
                        ]
                    ]
                ], 700, 300); // 700x300 (un poco más alto por ser horizontal)
            }

        } catch (\Exception $e) {
            \Log::error('Error generando charts:', ['error' => $e->getMessage()]);
        }

        return $urls;
    }


    /**
     * Enviar reporte individual usando configuración SMTP del tenant.
     * Permite enviar a un contacto específico del directorio.
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
            'contacto_id' => 'nullable|exists:directorio,id',
        ]);

        $cliente = Cliente::where('tenant_id', auth()->user()->tenant_id)->findOrFail($request->cliente_id);

        // Determinar a quién enviar el correo
        $emailDestino = null;
        $nombreDestino = $cliente->nombre;

        if ($request->filled('contacto_id')) {
            // Enviar a contacto específico del directorio
            $contacto = \App\Models\Directorio::where('tenant_id', auth()->user()->tenant_id)
                ->where('cliente_id', $cliente->id)
                ->findOrFail($request->contacto_id);

            $emailDestino = $contacto->correo;
            $nombreDestino = $contacto->nombre;
        } else {
            // Enviar al correo principal del cliente
            $emailDestino = $cliente->correo_contacto_principal;
        }

        if (empty($emailDestino)) {
            return back()->with('error', 'No hay correo de destino configurado. Seleccione un contacto del directorio o configure el correo del cliente.');
        }

        // Generar token de acceso
        $reporteAcceso = ReporteAcceso::create([
            'cliente_id' => $request->cliente_id,
            'token' => ReporteAcceso::generarToken(),
            'fecha_desde' => $request->desde,
            'fecha_hasta' => $request->hasta,
            'expira_en' => now()->addDays(7),
        ]);

        // Generar URL pública
        $urlReporte = route('reporte.publico', ['token' => $reporteAcceso->token]);

        // Enviar email usando configuración SMTP del tenant
        try {
            \App\Services\TenantMailService::sendForTenant(
                auth()->user()->tenant_id,
                $emailDestino,
                new \App\Mail\ReporteClienteMail($cliente, $urlReporte, $request->desde, $request->hasta)
            );

            return back()->with('success', "Reporte enviado exitosamente a {$nombreDestino} ({$emailDestino})");
        } catch (\Exception $e) {
            \Log::error('Error enviando reporte por correo: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el reporte: ' . $e->getMessage());
        }
    }

    /**
     * NUEVO: Envío masivo a múltiples clientes con múltiples contactos usando SMTP del tenant.
     */
    public function enviarMasivo(Request $request)
    {
        $request->validate([
            'clientes' => 'required|array|min:1',
            'clientes.*' => 'exists:cliente,id',
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
            'contactos' => 'nullable|array',
            'contactos.*' => 'nullable|array',
        ]);

        $clientesIds = $request->clientes;
        $desde = $request->desde;
        $hasta = $request->hasta;
        $tenantId = auth()->user()->tenant_id;
        $contactosSeleccionados = $request->contactos ?? [];

        $enviados = 0;
        $errores = [];

        foreach ($clientesIds as $clienteId) {
            try {
                $cliente = Cliente::where('tenant_id', $tenantId)->findOrFail($clienteId);

                // Determinar destinatarios
                $destinatarios = [];

                if (!empty($contactosSeleccionados[$clienteId])) {
                    // Usar contactos seleccionados del directorio
                    $destinatarios = $contactosSeleccionados[$clienteId];
                } elseif (!empty($cliente->correo_contacto_principal)) {
                    // Fallback: correo principal del cliente
                    $destinatarios = [$cliente->correo_contacto_principal];
                }

                if (empty($destinatarios)) {
                    $errores[] = "{$cliente->nombre}: Sin contactos seleccionados ni correo configurado";
                    continue;
                }

                // Generar token de acceso
                $reporteAcceso = ReporteAcceso::create([
                    'cliente_id' => $clienteId,
                    'token' => ReporteAcceso::generarToken(),
                    'fecha_desde' => $desde,
                    'fecha_hasta' => $hasta,
                    'expira_en' => now()->addDays(7),
                ]);

                // Generar URL pública
                $urlReporte = route('reporte.publico', ['token' => $reporteAcceso->token]);

                // Enviar a cada destinatario
                foreach ($destinatarios as $emailDestino) {
                    \App\Services\TenantMailService::sendForTenant(
                        $tenantId,
                        $emailDestino,
                        new \App\Mail\ReporteClienteMail($cliente, $urlReporte, $desde, $hasta)
                    );
                }

                $enviados += count($destinatarios);

            } catch (\Exception $e) {
                $errores[] = "{$cliente->nombre}: {$e->getMessage()}";
            }
        }

        $mensaje = "✅ Reportes enviados exitosamente a {$enviados} destinatario(s)";

        if (count($errores) > 0) {
            $mensaje .= "\n\n⚠️ Errores:\n" . implode("\n", $errores);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * NUEVO: Ver reporte público (sin login)
     */
    public function verReportePublico($token)
    {
        // Buscar el token
        $reporteAcceso = ReporteAcceso::where('token', $token)->firstOrFail();

        // Verificar que no haya expirado
        if (!$reporteAcceso->estaVigente()) {
            abort(403, 'Este enlace ha expirado. Por favor contacta con Soporte Crosspoint para obtener un nuevo enlace.');
        }

        // Registrar el acceso
        $reporteAcceso->registrarAcceso();

        // Obtener datos del cliente
        $clienteId = $reporteAcceso->cliente_id;
        $desde = $reporteAcceso->fecha_desde->format('Y-m-d');
        $hasta = $reporteAcceso->fecha_hasta->format('Y-m-d');
        $cliente = $reporteAcceso->cliente;

        // Reutilizar la lógica del preview para obtener los datos
        $datos = $this->obtenerDatosReporte($clienteId, $desde, $hasta);

        return view('reportes.reporte-publico', compact('datos', 'cliente', 'desde', 'hasta'));
    }

    /**
     * NUEVO: Extraer lógica de obtención de datos (para reutilizar)
     */
    private function obtenerDatosReporte($clienteId, $desde, $hasta)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $tenantId = $cliente->tenant_id; // Asegurar que usamos el tenant del cliente

        // Total
        $total = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->count();

        // Greens
        $greens = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        // Reds
        $reds = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->count();

        // Sobrepesos
        $totalSobrepesos = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->whereRaw('1=0')
            ->count();

        // Por Aduana
        $porAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as nombre', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->groupBy('aduanas.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // Verdes por Aduana
        $verdesPorAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->groupBy('aduanas.nombre')
            ->get();

        // Rojos por Aduana
        $rojosPorAduana = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->groupBy('aduanas.nombre')
            ->get();

        // Histórico
        $historial = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('YEAR(fecha_cruce_estimada) as anio'),
            DB::raw('MONTH(fecha_cruce_estimada) as mes'),
            DB::raw('count(*) as total')
        )
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])
            ->groupBy('anio', 'mes')
            ->get();

        $meses = range(1, 12);
        $historialMeses = [];
        foreach ($meses as $m) {
            $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
        }

        // Top Importadores
        $tramitesPorImportador = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')
            ->select('importadores.nombre as importador', DB::raw('count(*) as total'))
            ->where('operaciones.cliente_id', $clienteId)
            ->whereBetween('operaciones.fecha_cruce_estimada', [$desde, $hasta])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->get();

        $topImportadores = $tramitesPorImportador->take(8);

        // Trámites por día
        $desdeCarbon = Carbon::parse($desde);
        $hastaCarbon = Carbon::parse($hasta);

        $rawPorDia = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('DATE(fecha_cruce_estimada) as fecha'),
            DB::raw('count(*) as total')
        )
            ->where('operaciones.cliente_id', $clienteId)
            ->whereBetween('operaciones.fecha_cruce_estimada', [$desde, $hasta])
            ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
            ->pluck('total', 'fecha');

        $tramitesPorDia = [];
        $cursor = $desdeCarbon->copy();

        while ($cursor <= $hastaCarbon) {
            $fecha = $cursor->format('Y-m-d');
            $tramitesPorDia[] = [
                'fecha_cruce_estimada' => $fecha,
                'dia' => $cursor->day,
                'mes' => $cursor->format('M'),
                'total' => $rawPorDia[$fecha] ?? 0,
                'dia_semana' => $cursor->locale('es')->dayName
            ];
            $cursor->addDay();
        }

        // Calendario mensual
        // Usamos el mes de la fecha final del rango solicitado
        $inicioMes = $hastaCarbon->copy()->startOfMonth();
        $finMes = $hastaCarbon->copy()->endOfMonth();

        // Calendario mensual
        $rawCalendario = Operacion::where('tenant_id', auth()->user()->tenant_id)
            ->where('cliente_id', $clienteId)
            ->select(
                DB::raw('DATE(fecha_cruce_estimada) as fecha'),
                DB::raw('count(*) as total')
            )
            ->whereBetween('fecha_cruce_estimada', [$inicioMes, $finMes])
            ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
            ->pluck('total', 'fecha');

        $calendario = [];
        $inicioCalendario = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finCalendario = $finMes->copy()->endOfWeek(Carbon::SUNDAY);
        $cursor = $inicioCalendario->copy();

        while ($cursor <= $finCalendario) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $fecha = $cursor->format('Y-m-d');
                $semana[] = [
                    'fecha_cruce_estimada' => $fecha,
                    'dia' => $cursor->day,
                    'mes' => $cursor->month,
                    'total' => $rawCalendario[$fecha] ?? 0,
                    'actual' => $cursor->month === $inicioMes->month,
                    'dia_semana' => $cursor->locale('es')->shortDayName
                ];
                $cursor->addDay();
            }
            $calendario[] = $semana;
        }

        return [
            'cliente' => [
                'nombre' => $cliente->nombre,
                'email' => $cliente->email ?? 'No especificado'
            ],
            'periodo' => [
                'desde' => $desde,
                'hasta' => $hasta
            ],
            'estadisticas' => [
                'total' => $total,
                'greens' => $greens,
                'reds' => $reds,
                'sobrepesos' => $totalSobrepesos
            ],
            'porAduana' => $porAduana->toArray(),
            'verdesPorAduana' => $verdesPorAduana->toArray(),
            'rojosPorAduana' => $rojosPorAduana->toArray(),
            'historialMeses' => $historialMeses,
            'topImportadores' => $topImportadores->toArray(),
            'tramitesPorDia' => $tramitesPorDia,
            'calendario' => $calendario
        ];
    }




}
