<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operacion;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.super.dashboard');
    }

    public function liveData()
    {
        $hoy = Carbon::today();
        
        // 1. Total operaciones en todo el CRM (histórico)
        $totalOperaciones = Operacion::withoutGlobalScopes()->count();
        
        // Operaciones de hoy
        $operacionesHoy = Operacion::withoutGlobalScopes()
            ->whereDate('created_at', $hoy)
            ->count();
            
        // 2. Usuarios Activos (Total)
        $usuariosActivos = User::withoutGlobalScopes()
            ->where('active', 1)
            ->count();
            
        // 3. Tenants Activos
        $tenantsActivos = Tenant::where('estado', 'activo')->count();

        // 4. Notificaciones de hoy
        // Suponiendo que la tabla 'notificaciones' tiene columnas 'tipo' (email, whatsapp)
        $notificacionesHoy = DB::table('notificaciones')
            ->whereDate('created_at', $hoy)
            ->select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();
            
        $emailsHoy = $notificacionesHoy['email'] ?? 0;
        $whatsappHoy = $notificacionesHoy['whatsapp'] ?? 0;

        // 5. Gráfico de operaciones de la semana actual (Lunes a Domingo)
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $opsSemana = Operacion::withoutGlobalScopes()
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$inicioSemana, $finSemana])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
            
        $graficoLabels = [];
        $graficoData = [];
        
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $fechaStr = $dia->format('Y-m-d');
            $graficoLabels[] = $dia->locale('es')->isoFormat('dddd'); // Lunes, Martes...
            
            $match = $opsSemana->firstWhere('fecha', $fechaStr);
            $graficoData[] = $match ? $match->total : 0;
        }

        return response()->json([
            'kpis' => [
                'total_operaciones' => number_format($totalOperaciones),
                'operaciones_hoy' => number_format($operacionesHoy),
                'usuarios_activos' => number_format($usuariosActivos),
                'tenants_activos' => number_format($tenantsActivos),
                'emails_hoy' => number_format($emailsHoy),
                'whatsapp_hoy' => number_format($whatsappHoy),
            ],
            'chart' => [
                'labels' => $graficoLabels,
                'data' => $graficoData
            ]
        ]);
    }
}
