<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $allowedRoles = ['admin', 'documentador', 'trafico'];

            if (!auth()->check()) {
                return redirect()->route('login');
            }

            $user = auth()->user();

            if (!in_array($user->role, $allowedRoles)) {
                $route = config("dashboards.role_routes.{$user->role}", 'home');
                return redirect()->route($route)
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.config.index');
    }

    public function referencia()
    {
        $tenant = auth()->user()->tenant;
        return view('admin.config.referencias', compact('tenant'));
    }

    public function guardarReferencia(Request $request)
    {
        $request->validate([
            'referencia_prefijo' => 'required|string|max:10|alpha_dash',
        ]);

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('admin.config')
                ->with('error', 'No se encontró el tenant asociado.');
        }

        $tenant->update([
            'referencia_prefijo' => strtoupper($request->referencia_prefijo),
        ]);

        return redirect()->route('admin.config.referencia')
            ->with('success', 'Prefijo de referencia actualizado correctamente.');
    }

    public function analiticas()
    {
        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        return view('admin.config.analiticas', compact('config'));
    }

    public function guardarAnaliticas(Request $request)
    {
        $request->validate([
            'meta_ideal_diaria' => 'required|integer|min:1',
            'meta_buena_diaria' => 'required|integer|min:1',
            'meta_mala_diaria' => 'required|integer|min:1',
            'meta_ideal_mensual' => 'required|integer|min:1',
            'meta_buena_mensual' => 'required|integer|min:1',
            'meta_mala_mensual' => 'required|integer|min:1',
            'proyeccion_1' => 'required|integer|min:1',
            'proyeccion_2' => 'required|integer|min:1',
            'meta_media_diaria' => 'required|integer|min:1',
            'meta_alta_diaria' => 'required|integer|min:1',
        ]);

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('admin.config')
                ->with('error', 'No se encontró el tenant asociado.');
        }

        $config = $tenant->configuracion ?? [];

        $config['meta_ideal_diaria'] = (int) $request->meta_ideal_diaria;
        $config['meta_buena_diaria'] = (int) $request->meta_buena_diaria;
        $config['meta_mala_diaria'] = (int) $request->meta_mala_diaria;
        $config['meta_ideal_mensual'] = (int) $request->meta_ideal_mensual;
        $config['meta_buena_mensual'] = (int) $request->meta_buena_mensual;
        $config['meta_mala_mensual'] = (int) $request->meta_mala_mensual;
        $config['proyeccion_1'] = (int) $request->proyeccion_1;
        $config['proyeccion_2'] = (int) $request->proyeccion_2;
        $config['meta_media_diaria'] = (int) $request->meta_media_diaria;
        $config['meta_alta_diaria'] = (int) $request->meta_alta_diaria;

        $tenant->update(['configuracion' => $config]);

        return redirect()->route('admin.config.analiticas')
            ->with('success', 'Metas analíticas actualizadas correctamente.');
    }

    public function plantillas()
    {
        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];
        $plantilla_seleccionada = $config['plantilla_correo_modulacion'] ?? 'basica_azul';
        $plantilla_personalizada = $config['plantilla_correo_personalizada'] ?? null; // Si el superadmin le vendió una plantilla propia

        return view('admin.config.plantillas', compact('plantilla_seleccionada', 'plantilla_personalizada'));
    }

    public function guardarPlantillas(Request $request)
    {
        $request->validate([
            'plantilla_correo_modulacion' => 'required|string',
        ]);

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        $config['plantilla_correo_modulacion'] = $request->plantilla_correo_modulacion;

        $tenant->update(['configuracion' => $config]);

        return redirect()->route('admin.config.plantillas')
            ->with('success', 'La plantilla de correos se ha establecido exitosamente.');
    }

    public function previewPlantilla(Request $request, $tipo)
    {
        // Mock data for preview
        $operacion = (object) [
            'factura' => 'INV-2026-001',
            'nombre_producto' => 'Componentes Electrónicos Industriales',
            'fecha_pago' => date('Y-m-d H:i:s'),
            'aduana' => (object) ['nombre' => 'Aduana de Nuevo Laredo (240)'],
            'expediente' => (object) ['numero_pedimento' => '24 240 6011705']
        ];

        $estatus = 'DESADUANAMIENTO LIBRE';
        $contacto_nombre = 'Lic. Carlos García';
        $contacto_cliente = 'Logística Avanzada del Norte';
        $tenant_empresa = auth()->user()->tenant->nombre_empresa ?? 'Agencia Aduanal Demo';

        return view('emails.modulacion.' . $tipo, compact(
            'operacion',
            'estatus',
            'contacto_nombre',
            'contacto_cliente',
            'tenant_empresa'
        ));
    }

    public function smtp()
    {
        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        return view('admin.config.smtp', compact('config'));
    }

    public function guardarSmtp(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|numeric',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string',
        ]);

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        $config['smtp_host'] = $request->smtp_host;
        $config['smtp_port'] = $request->smtp_port;
        $config['smtp_encryption'] = $request->smtp_encryption;
        $config['smtp_username'] = $request->smtp_username;
        // Solo actualizar contraseña si se mandó algo (para no sobreescribir con vacío por error)
        if ($request->filled('smtp_password')) {
            $config['smtp_password'] = encrypt($request->smtp_password); // Encriptar por seguridad
        }
        $config['smtp_from_address'] = $request->smtp_from_address;
        $config['smtp_from_name'] = $request->smtp_from_name;

        $tenant->update(['configuracion' => $config]);

        return redirect()->route('admin.config.smtp')
            ->with('success', 'Configuración SMTP actualizada exitosamente.');
    }

    /**
     * Probar la conexión SMTP con la configuración actual del tenant.
     */
    public function probarSmtp()
    {
        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        if (empty($config['smtp_host']) || empty($config['smtp_username'])) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración SMTP guardada. Configura tus credenciales primero.',
            ], 400);
        }

        try {
            // Configurar temporalmente el mailer con las credenciales del tenant
            $smtpConfig = [
                'transport' => 'smtp',
                'host' => $config['smtp_host'],
                'port' => (int) $config['smtp_port'],
                'encryption' => $config['smtp_encryption'] ?? 'tls',
                'username' => $config['smtp_username'],
                'password' => decrypt($config['smtp_password']),
                'timeout' => 10,
                'local_domain' => 'localhost',
            ];

            \Config::set('mail.mailers.smtp', $smtpConfig);
            \Config::set('mail.from', [
                'address' => $config['smtp_from_address'] ?? $config['smtp_username'],
                'name' => $config['smtp_from_name'] ?? $tenant->nombre_empresa,
            ]);

            // Enviar correo de prueba usando closure
            \Illuminate\Support\Facades\Mail::send(
                'emails.test',
                [
                    'tenant' => $tenant->nombre_empresa,
                    'timestamp' => date('Y-m-d H:i:s'),
                ],
                function ($message) use ($config, $tenant) {
                    $to = $config['smtp_from_address'] ?? $config['smtp_username'];
                    $fromName = $config['smtp_from_name'] ?? $tenant->nombre_empresa;

                    $message->to($to)
                        ->subject('Prueba de conexión SMTP - ' . $tenant->nombre_empresa);
                }
            );

            return response()->json([
                'success' => true,
                'message' => '¡Conexión SMTP exitosa! Se envió un correo de prueba a ' . ($config['smtp_from_address'] ?? $config['smtp_username']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con el servidor SMTP: ' . $e->getMessage(),
            ], 500);
        }
    }
}
