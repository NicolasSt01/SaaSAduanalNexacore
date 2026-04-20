<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class BorderStatusController extends Controller
{
    public function index()
    {
        return view('border-status.index');
    }

    public function check(Request $request)
    {
        $request->validate([
            'petition_integration_number' => 'required|string',
        ]);

        $api = env('PECEM_API_URL');
        if (!$api) {
            return back()->withErrors(['error' => 'No está configurada la URL de PECEM en el .env']);
        }

        try {
            $res = Http::withOptions([
                'verify' => false, // 🔴 DESACTIVA validación SSL
                'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1']
            ])->get($api . $request->petition_integration_number);

            if ($res->status() !== 200) {
                return back()->withErrors(['error' => 'Error en la consulta a PECEM. Código HTTP: ' . $res->status()]);
            }

            $html = $res->body();
            preg_match_all('/\*\*\*([A-Z ]{21,33})\*\*\*/', $html, $matches);
            $status_txt = last($matches[1]) ?? null;

            // Si no se encontró texto válido, asignamos el valor por defecto manualmente
            if ($status_txt === null) {
                $status_txt = 'DODA no presentado al Mecanismo de Selección Automatizado';
                $status_code = 3;
            } else {
                // Si hay texto, usamos el match como antes
                $status_code = match ($status_txt) {
                    'DESADUANAMIENTO LIBRE' => 0,
                    'RECONOCIMIENTO ADUANERO' => 1,
                    'RECONOCIMIENTO ADUANERO CONCLUIDO' => 2,
                    'DODA no presentado al Mecanismo de Selección Automatizado' => 3,
                    default => 3, // Por si acaso, aunque ya manejamos null antes
                };
            }

            return view('border-status.index', [
                'status_code' => $status_code,
                'status_txt' => $status_txt
            ]);

        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Excepción: ' . $e->getMessage()]);
        }
    }
}
