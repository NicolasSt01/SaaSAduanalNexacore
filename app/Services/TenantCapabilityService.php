<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar y validar las capacidades y límites de cada tenant.
 * 
 * Este servicio centraliza toda la lógica de validación de límites
 * para que los controllers y jobs puedan usarla fácilmente.
 */
class TenantCapabilityService
{
    /**
     * Mapa de recursos y sus nombres legibles.
     */
    private const RESOURCE_NAMES = [
        'clientes' => 'clientes',
        'importadores' => 'importadores',
        'bodegas' => 'bodegas',
        'aduanas' => 'aduanas',
        'patentes' => 'patentes',
        'pedimentos_mes' => 'pedimentos por mes',
        'documentos_mes' => 'documentos por mes',
        'reportes_mes' => 'reportes por mes',
        'correos_dia' => 'correos por día',
        'whatsapp_mes' => 'mensajes de WhatsApp por mes',
    ];

    /**
     * Verifica si un tenant puede agregar un nuevo recurso.
     * 
     * @param Tenant $tenant
     * @param string $recurso Nombre del recurso (clientes, importadores, etc.)
     * @return array ['allowed' => bool, 'limite' => int|null, 'uso' => int, 'message' => string]
     */
    public function checkResourceLimit(Tenant $tenant, string $recurso): array
    {
        $limite = $tenant->getLimite($recurso);

        // Si no hay límite, permitir
        if (!$limite) {
            return [
                'allowed' => true,
                'limite' => null,
                'uso' => $tenant->getUso($recurso),
                'message' => '',
            ];
        }

        $uso = $tenant->getUso($recurso);
        $allowed = $uso < $limite;

        return [
            'allowed' => $allowed,
            'limite' => $limite,
            'uso' => $uso,
            'message' => $allowed
                ? ''
                : "Has alcanzado el límite de " . self::RESOURCE_NAMES[$recurso] . " ({$uso}/{$limite}). Contacta a tu administrador para aumentar tu límite.",
        ];
    }

    /**
     * Valida y lanza una excepción si se excede el límite.
     * 
     * @throws \RuntimeException
     */
    public function enforceResourceLimit(Tenant $tenant, string $recurso): void
    {
        $check = $this->checkResourceLimit($tenant, $recurso);

        if (!$check['allowed']) {
            Log::warning('Tenant excedió límite de recurso', [
                'tenant_id' => $tenant->id,
                'tenant' => $tenant->nombre_empresa,
                'recurso' => $recurso,
                'limite' => $check['limite'],
                'uso' => $check['uso'],
            ]);

            throw new \RuntimeException($check['message']);
        }
    }

    /**
     * Verifica si un tenant puede realizar una consulta al SOIA-Bot.
     */
    public function canMakeBotConsulta(Tenant $tenant): array
    {
        // Verificar si el bot está habilitado
        if (!$tenant->isBotEnabled()) {
            return [
                'allowed' => false,
                'message' => 'El SOIA-Bot no está habilitado para tu tenant. Contacta a soporte.',
            ];
        }

        // Verificar límite de consultas
        $limite = $tenant->getBotConsultasLimite();
        if (!$limite) {
            return [
                'allowed' => true,
                'message' => '',
            ];
        }

        $uso = $tenant->getBotConsultasUsadas();
        $allowed = $uso < $limite;

        return [
            'allowed' => $allowed,
            'limite' => $limite,
            'uso' => $uso,
            'disponible' => max(0, $limite - $uso),
            'message' => $allowed
                ? ''
                : "Has alcanzado tu límite de consultas al SOIA-Bot este mes ({$uso}/{$limite}).",
        ];
    }

    /**
     * Registra una consulta al SOIA-Bot (para tracking).
     */
    public function registerBotConsulta(Tenant $tenant): bool
    {
        $check = $this->canMakeBotConsulta($tenant);

        if (!$check['allowed']) {
            return false;
        }

        // Aquí podrías registrar en una tabla de auditoría
        // Por ahora solo retornamos true
        return true;
    }

    /**
     * Obtiene un resumen completo de uso de todos los recursos del tenant.
     */
    public function getTenantUsageSummary(Tenant $tenant): array
    {
        $summary = [];

        foreach (self::RESOURCE_NAMES as $key => $name) {
            $summary[$key] = [
                'nombre' => $name,
                ...$tenant->getRecursoInfo($key),
            ];
        }

        // Agregar info del bot
        $summary['bot'] = [
            'modo' => $tenant->getBotMode(),
            'enabled' => $tenant->isBotEnabled(),
            'automatic' => $tenant->isBotAutomatic(),
            'limite_consultas_mes' => $tenant->getBotConsultasLimite(),
            'consultas_usadas' => $tenant->getBotConsultasUsadas(),
            'can_make_query' => $tenant->canMakeBotConsulta(),
        ];

        return $summary;
    }

    /**
     * Obtiene recursos que están cerca de alcanzar el límite (>80%).
     */
    public function getNearLimitResources(Tenant $tenant, float $threshold = 80.0): array
    {
        $nearLimit = [];

        foreach (self::RESOURCE_NAMES as $key => $name) {
            $porcentaje = $tenant->getUsoPorcentaje($key);

            if ($porcentaje >= $threshold) {
                $nearLimit[$key] = [
                    'nombre' => $name,
                    'porcentaje' => $porcentaje,
                    'uso' => $tenant->getUso($key),
                    'limite' => $tenant->getLimite($key),
                ];
            }
        }

        return $nearLimit;
    }

    /**
     * Envía notificaciones de recursos cerca del límite.
     */
    public function notifyNearLimitResources(Tenant $tenant): void
    {
        $nearLimit = $this->getNearLimitResources($tenant);

        foreach ($nearLimit as $resource => $data) {
            Log::info('Tenant cerca del límite', [
                'tenant_id' => $tenant->id,
                'tenant' => $tenant->nombre_empresa,
                'recurso' => $resource,
                'porcentaje' => $data['porcentaje'],
                'uso' => $data['uso'],
                'limite' => $data['limite'],
            ]);

            // Aquí podrías enviar email al admin del tenant
            // Mail::to($tenant->correo_admin)->send(new ResourceNearLimitNotification($tenant, $resource, $data));
        }
    }

    /**
     * Configuración por defecto para nuevos tenants según el plan.
     * Ahora todo se almacena en el JSON 'configuracion'.
     */
    public static function getDefaultConfigForPlan(string $plan): array
    {
        return match ($plan) {
            'trial' => [
                'bot' => [
                    'mode' => 'manual',
                    'consultas_limite_mes' => 15,
                    'consultas_mes' => 0,
                    'consultas_mes_periodo' => now()->format('Y-m'),
                ],
                'max_usuarios' => 1,
                'max_operaciones_mes' => 15,
                'limites' => [
                    'recursos' => [
                        'clientes' => 2,
                        'importadores' => 1,
                        'bodegas' => 1,
                        'aduanas' => 1,
                        'patentes' => 1,
                        'pedimentos_mes' => 10,
                        'documentos_mes' => 20,
                    ],
                    'funcionalidades' => [
                        'reportes_mes' => 0,
                        'correos_dia' => 10,
                        'whatsapp_mes' => 0,
                    ],
                ],
                'features_enabled' => ['basic_dashboard'],
            ],
            'basico' => [
                'bot' => [
                    'mode' => 'manual',
                    'consultas_limite_mes' => 50,
                    'consultas_mes' => 0,
                    'consultas_mes_periodo' => now()->format('Y-m'),
                ],
                'max_usuarios' => 5,
                'max_operaciones_mes' => 100,
                'limites' => [
                    'recursos' => [
                        'clientes' => 10,
                        'importadores' => 5,
                        'bodegas' => 3,
                        'aduanas' => 2,
                        'patentes' => 3,
                        'pedimentos_mes' => 100,
                        'documentos_mes' => 200,
                    ],
                    'funcionalidades' => [
                        'reportes_mes' => 10,
                        'correos_dia' => 50,
                        'whatsapp_mes' => 100,
                    ],
                ],
                'features_enabled' => ['basic_dashboard', 'email_notifications', 'basic_reports'],
            ],
            'profesional' => [
                'bot' => [
                    'mode' => 'automatico',
                    'consultas_limite_mes' => 200,
                    'consultas_mes' => 0,
                    'consultas_mes_periodo' => now()->format('Y-m'),
                ],
                'max_usuarios' => 20,
                'max_operaciones_mes' => 500,
                'limites' => [
                    'recursos' => [
                        'clientes' => 50,
                        'importadores' => 20,
                        'bodegas' => 10,
                        'aduanas' => 5,
                        'patentes' => 10,
                        'pedimentos_mes' => 500,
                        'documentos_mes' => 1000,
                    ],
                    'funcionalidades' => [
                        'reportes_mes' => 50,
                        'correos_dia' => 200,
                        'whatsapp_mes' => 500,
                    ],
                ],
                'features_enabled' => ['basic_dashboard', 'email_notifications', 'advanced_reports', 'api_access', 'priority_support'],
            ],
            'enterprise' => [
                'bot' => [
                    'mode' => 'automatico',
                    'consultas_limite_mes' => null, // Ilimitado
                    'consultas_mes' => 0,
                    'consultas_mes_periodo' => now()->format('Y-m'),
                ],
                'max_usuarios' => 100,
                'max_operaciones_mes' => 5000,
                'limites' => [
                    'recursos' => [
                        'clientes' => null, // Ilimitado
                        'importadores' => null,
                        'bodegas' => null,
                        'aduanas' => null,
                        'patentes' => null,
                        'pedimentos_mes' => null,
                        'documentos_mes' => null,
                    ],
                    'funcionalidades' => [
                        'reportes_mes' => null,
                        'correos_dia' => null,
                        'whatsapp_mes' => null,
                    ],
                ],
                'features_enabled' => ['basic_dashboard', 'email_notifications', 'advanced_reports', 'api_access', 'priority_support', 'white_label', 'custom_integrations', 'dedicated_support'],
            ],
            default => self::getDefaultConfigForPlan('basico'),
        };
    }

    /**
     * Aplica la configuración por defecto según el plan a un tenant.
     * Ahora todo se almacena en el JSON 'configuracion'.
     */
    public static function applyPlanDefaults(Tenant $tenant, string $plan): void
    {
        $config = self::getDefaultConfigForPlan($plan);

        // Mezclar con la configuración existente
        $existingConfig = $tenant->configuracion ?? [];
        $mergedConfig = array_merge_recursive($existingConfig, $config);

        // Actualizar max_usuarios y max_operaciones_mes (columnas separadas)
        $tenant->max_usuarios = $config['max_usuarios'];
        $tenant->max_operaciones_mes = $config['max_operaciones_mes'];

        // Actualizar el JSON de configuración
        $tenant->configuracion = $mergedConfig;
        $tenant->save();
    }
}
