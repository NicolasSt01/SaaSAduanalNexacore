<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Operacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReporteClienteService
{
    public function generar_old(int $clienteId, string $desde, string $hasta): array
    {
        $cliente = Cliente::findOrFail($clienteId);
        $tenantId = $cliente->tenant_id;

        // Totales
        $total = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->count();

        $greens = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $reds = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANANERO CONCLUIDO')
            ->count();

        // Por aduana
        $porAduana = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre_aduana as nombre', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('aduanas.nombre_aduana')
            ->get();

        // Histórico anual
        $historial = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->select(
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('count(*) as total')
            )
            ->where('cliente_id', $clienteId)
            ->whereYear('fecha', now()->year)
            ->groupBy('mes')
            ->get();

        $historialMeses = [];
        foreach (range(1, 12) as $m) {
            $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
        }





        //Pruebas de Grafica.
        $chartGreensReds = Http::withoutVerifying()->get('https://quickchart.io/chart', [
            'c' => json_encode([
                'type' => 'pie',
                'data' => [
                    'labels' => ['Greens', 'Reds'],
                    'datasets' => [
                        [
                            'data' => [$greens, $reds],
                            'backgroundColor' => ['#28a745', '#dc3545'],
                        ]
                    ]
                ]
            ])
        ])->body();

        $pathGreensReds = storage_path('app/public/greens_reds_' . $clienteId . '.png');
        file_put_contents($pathGreensReds, $chartGreensReds);

        //Grafica Historica Anual

        $labels = array_map(
            fn($m) => \Carbon\Carbon::create()->month($m)->translatedFormat('F'),
            array_keys($historialMeses)
        );

        $chartHistorico = Http::withoutVerifying()->get('https://quickchart.io/chart', [
            'c' => json_encode([
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Trámites',
                            'data' => array_values($historialMeses),
                            'borderColor' => '#007bff',
                            'fill' => false,
                            'tension' => 0.3,
                        ]
                    ]
                ]
            ])
        ])->body();

        $pathHistorico = storage_path('app/public/historico_' . $clienteId . '.png');
        file_put_contents($pathHistorico, $chartHistorico);

















        return [
            'cliente' => $cliente,
            'desde' => $desde,
            'hasta' => $hasta,
            'semana' => Carbon::parse($desde)->weekOfYear,
            'total' => $total,
            'greens' => $greens,
            'reds' => $reds,
            'porAduana' => $porAduana,
            'historialMeses' => $historialMeses,
        ];
    }

    public function generar(int $clienteId, string $desde, string $hasta): array
    {
        $cliente = Cliente::findOrFail($clienteId);
        $tenantId = $cliente->tenant_id;

        $desdeCarbon = Carbon::parse($desde);
        $hastaCarbon = Carbon::parse($hasta);

        // =============================
        // TOTALES
        // =============================
        $total = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->count();

        $greens = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $reds = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->count();

        // =============================
        // MODULACIONES POR ADUANA
        // =============================
        $porAduana = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre_aduana as nombre', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('aduanas.nombre_aduana')
            ->get();

        // =============================
        // HISTÓRICO ANUAL
        // =============================
        $historial = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->select(
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('count(*) as total')
            )
            ->where('cliente_id', $clienteId)
            ->whereYear('fecha', now()->year)
            ->groupBy('mes')
            ->get();

        $historialMeses = [];
        foreach (range(1, 12) as $m) {
            $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
        }

        // ===============================
        // NUEVO: Total de sobrepesos
        // ===============================
        $totalSobrepesos = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('sobrepeso', true)
            ->count();

        // ===============================
        // NUEVO: Distribución de trámites por importador (relacional)
        // ===============================
        $tramitesPorImportador = Operacion::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->join(
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
            ->whereBetween('operaciones.fecha', [$desde, $hasta])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->get();

        $topImportadores = $tramitesPorImportador->take(8);


        // =============================
        // GENERAR GRÁFICAS
        // =============================
        $charts = [];

        // 1️⃣ Greens vs Reds
        $charts['greensReds'] = $this->crearGrafica(
            [
                'type' => 'pie',
                'data' => [
                    'labels' => ['Greens', 'Reds'],
                    'datasets' => [
                        [
                            'data' => [$greens, $reds],
                            'backgroundColor' => ['#28a745', '#dc3545'],
                        ]
                    ]
                ]
            ],
            "greens_reds_{$clienteId}"
        );

        // 2️⃣ Aduanas
        if ($porAduana->count()) {
            $charts['aduanas'] = $this->crearGrafica(
                [
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => $porAduana->pluck('nombre')->values(),
                        'datasets' => [
                            [
                                'data' => $porAduana->pluck('total')->values(),
                            ]
                        ]
                    ]
                ],
                "aduanas_{$clienteId}"
            );
        }

        // 3️⃣ Histórico anual
        $charts['historico'] = $this->crearGrafica(
            [
                'type' => 'line',
                'data' => [
                    'labels' => array_map(
                        fn($m) => Carbon::create()->month($m)->translatedFormat('F'),
                        array_keys($historialMeses)
                    ),
                    'datasets' => [
                        [
                            'label' => 'Trámites',
                            'data' => array_values($historialMeses),
                            'borderColor' => '#007bff',
                            'fill' => false,
                            'tension' => 0.3,
                        ]
                    ]
                ]
            ],
            "historico_{$clienteId}"
        );

        // 4️⃣ Importadores (Top)
        if ($topImportadores->count()) {
            $charts['importadores'] = $this->crearGrafica(
                [
                    'type' => 'pie',
                    'data' => [
                        'labels' => $topImportadores->pluck('importador')->values(),
                        'datasets' => [
                            [
                                'data' => $topImportadores->pluck('total')->values(),
                            ]
                        ]
                    ]
                ],
                "importadores_{$clienteId}"
            );
        }

        //$semana = Carbon::parse($desdeCarbon)->weekOfYear;
        $inicio = Carbon::parse($desde);
        $fin = Carbon::parse($hasta);
        $dias = $inicio->diffInDays($fin) + 1;
        if ($dias <= 7) {
            // Semana específica
            $semana = 'Semana ' . $inicio->weekOfYear;
        } else {
            // Periodo amplio
            $semana = 'Anual';
        }





        // =============================
        // RESPUESTA FINAL
        // =============================
        return [
            'cliente' => $cliente,
            'desde' => $desdeCarbon->format('d/m/Y'),
            'hasta' => $hastaCarbon->format('d/m/Y'),
            'semana' => $semana,
            'total' => $total,
            'greens' => $greens,
            'reds' => $reds,
            'porAduana' => $porAduana,
            'historialMeses' => $historialMeses,
            'charts' => $charts,
            'totalSobrepesos' => $totalSobrepesos,
            'tramitesPorImportador' => $tramitesPorImportador,
            'topImportadores' => $topImportadores,

        ];
    }

    // =====================================================
    // MÉTODO AUXILIAR PARA GENERAR IMÁGENES
    // =====================================================
    protected function crearGrafica(array $config, string $nombre): string
    {
        /*$response = Http::withoutVerifying()->get('https://quickchart.io/chart', [
            'c' => json_encode($config),
            'width' => 600,
            'height' => 300,
            'format' => 'png',
        ]);*/
        $response = Http::withoutVerifying()->get('https://quickchart.io/chart', [
            'c' => json_encode($config),
            'width' => 600,
            'height' => 300,
            'format' => 'png',
        ]);

        $filename = $nombre . '_' . Str::random(6) . '.png';
        $path = storage_path('app/public/reportes/' . $filename);

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $response->body());

        return $path;
    }




}
