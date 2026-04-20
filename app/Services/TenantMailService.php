<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\TransportManager;
use App\Models\Tenant;

/**
 * Servicio para manejar el envío de correos con configuración SMTP específica de cada tenant.
 * 
 * Cada tenant puede configurar su propio servidor SMTP (Gmail, Outlook, HostGator, etc.)
 * y este servicio se encarga de usar esas credenciales al enviar correos.
 */
class TenantMailService
{
    /**
     * Obtiene la configuración SMTP del tenant actual.
     * 
     * @return array Configuración SMTP del tenant o null si no está configurada
     */
    public static function getTenantSmtpConfig(): ?array
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return null;
        }

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        // Verificar si el tenant tiene configuración SMTP personalizada
        if (empty($config['smtp_host']) || empty($config['smtp_username'])) {
            return null;
        }

        return [
            'transport' => 'smtp',
            'host' => $config['smtp_host'],
            'port' => (int) $config['smtp_port'],
            'encryption' => $config['smtp_encryption'] ?? 'tls',
            'username' => $config['smtp_username'],
            'password' => decrypt($config['smtp_password']),
            'timeout' => null,
            'local_domain' => 'localhost',
        ];
    }

    /**
     * Obtiene la configuración del remitente del tenant.
     * 
     * @return array|null Configuración del remitente ['address' => ..., 'name' => ...]
     */
    public static function getTenantFromAddress(): ?array
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return null;
        }

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        if (empty($config['smtp_from_address'])) {
            return null;
        }

        return [
            'address' => $config['smtp_from_address'],
            'name' => $config['smtp_from_name'] ?? $tenant->nombre_empresa ?? 'Agencia Aduanal',
        ];
    }

    /**
     * Configura dinámicamente el mailer con la configuración SMTP del tenant.
     * 
     * @return \Illuminate\Mail\Mailer Instancia del mailer configurado
     */
    public static function configureTenantMailer(): Mailer
    {
        $smtpConfig = self::getTenantSmtpConfig();
        $fromAddress = self::getTenantFromAddress();

        // Si no hay configuración SMTP del tenant, usar la configuración global
        if (!$smtpConfig) {
            return Mail::mailer('smtp');
        }

        // Configurar dinámicamente el mailer con las credenciales del tenant
        Config::set('mail.mailers.smtp', array_merge(
            Config::get('mail.mailers.smtp', []),
            $smtpConfig
        ));

        // Configurar el from address
        if ($fromAddress) {
            Config::set('mail.from', $fromAddress);
        }

        return Mail::mailer('smtp');
    }

    /**
     * Envía un correo usando la configuración SMTP del tenant.
     * 
     * @param string $to Correo del destinatario
     * @param \Illuminate\Mail\Mailable $mailable Instancia del Mailable a enviar
     * @return bool true si se envió correctamente, false en caso contrario
     */
    public static function sendToTenant(string $to, $mailable): bool
    {
        try {
            $mailer = self::configureTenantMailer();
            $mailer->to($to)->send($mailable);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo con configuración del tenant: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía un correo a múltiples destinatarios usando la configuración del tenant.
     * 
     * @param array $to Array de correos destinatarios
     * @param \Illuminate\Mail\Mailable $mailable Instancia del Mailable a enviar
     * @return bool true si se enviaron correctamente, false en caso contrario
     */
    public static function sendToTenantMultiple(array $to, $mailable): bool
    {
        try {
            $mailer = self::configureTenantMailer();
            $mailer->to($to)->send($mailable);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo múltiple con configuración del tenant: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía un correo usando la configuración de un tenant específico (útil para jobs).
     * 
     * @param int $tenantId ID del tenant
     * @param string $to Correo del destinatario
     * @param \Illuminate\Mail\Mailable $mailable Instancia del Mailable a enviar
     * @return bool true si se envió correctamente, false en caso contrario
     */
    public static function sendForTenant(int $tenantId, string $to, $mailable): bool
    {
        try {
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                \Log::error("Tenant no encontrado: {$tenantId}");
                return false;
            }

            $config = $tenant->configuracion ?? [];

            // Si no hay configuración SMTP del tenant, usar la configuración global
            if (empty($config['smtp_host']) || empty($config['smtp_username'])) {
                Mail::to($to)->send($mailable);
                return true;
            }

            // Configurar mailer temporalmente con las credenciales del tenant
            $smtpConfig = [
                'transport' => 'smtp',
                'host' => $config['smtp_host'],
                'port' => (int) $config['smtp_port'],
                'encryption' => $config['smtp_encryption'] ?? 'tls',
                'username' => $config['smtp_username'],
                'password' => decrypt($config['smtp_password']),
                'timeout' => null,
                'local_domain' => 'localhost',
            ];

            $fromAddress = [
                'address' => $config['smtp_from_address'] ?? $config['smtp_username'],
                'name' => $config['smtp_from_name'] ?? $tenant->nombre_empresa ?? 'Agencia Aduanal',
            ];

            Config::set('mail.mailers.smtp', array_merge(
                Config::get('mail.mailers.smtp', []),
                $smtpConfig
            ));
            Config::set('mail.from', $fromAddress);

            Mail::to($to)->send($mailable);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo para tenant ' . $tenantId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si el tenant actual tiene configuración SMTP válida.
     * 
     * @return bool
     */
    public static function hasTenantSmtpConfig(): bool
    {
        $config = self::getTenantSmtpConfig();
        return $config !== null;
    }

    /**
     * Obtiene información de la configuración SMTP del tenant (sin la contraseña).
     * Útil para mostrar en UI.
     * 
     * @return array|null
     */
    public static function getTenantSmtpInfo(): ?array
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return null;
        }

        $tenant = auth()->user()->tenant;
        $config = $tenant->configuracion ?? [];

        if (empty($config['smtp_host'])) {
            return null;
        }

        return [
            'host' => $config['smtp_host'],
            'port' => $config['smtp_port'],
            'encryption' => $config['smtp_encryption'] ?? 'tls',
            'username' => $config['smtp_username'],
            'from_address' => $config['smtp_from_address'] ?? $config['smtp_username'],
            'from_name' => $config['smtp_from_name'] ?? $tenant->nombre_empresa,
            'configured' => !empty($config['smtp_password']),
        ];
    }
}
