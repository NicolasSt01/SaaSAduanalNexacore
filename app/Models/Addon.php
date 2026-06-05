<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use SoftDeletes;

    protected $table = 'addons';

    protected $fillable = [
        'nombre', 'descripcion', 'tipo', 'identificador',
        'precio_mensual', 'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function contratados()
    {
        return $this->hasMany(AddonContratado::class, 'addon_id');
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'reporte' => 'Reporte',
            'plantilla_email' => 'Plantilla Email',
            'plantilla_whatsapp' => 'Plantilla WhatsApp',
            'feature' => 'Funcionalidad',
            'recurso_extra' => 'Recurso Extra',
            default => $this->tipo,
        };
    }

    public function getTipoIconAttribute(): string
    {
        return match($this->tipo) {
            'reporte' => 'fa-chart-bar',
            'plantilla_email' => 'fa-envelope',
            'plantilla_whatsapp' => 'fa-comment-dots',
            'feature' => 'fa-puzzle-piece',
            'recurso_extra' => 'fa-plus-circle',
            default => 'fa-box',
        };
    }

    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'reporte' => 'indigo',
            'plantilla_email' => 'blue',
            'plantilla_whatsapp' => 'emerald',
            'feature' => 'purple',
            'recurso_extra' => 'amber',
            default => 'gray',
        };
    }

    public function aplicarATenant(Tenant $tenant): void
    {
        $config = $tenant->configuracion ?? [];

        match($this->tipo) {
            'reporte' => $this->aplicarReporte($config),
            'feature' => $this->aplicarFeature($config),
            'recurso_extra' => $this->aplicarRecurso($config),
            'plantilla_email' => $this->aplicarPlantillaEmail($config),
            'plantilla_whatsapp' => $this->aplicarPlantillaWhatsapp($config),
            default => null,
        };

        $tenant->update(['configuracion' => $config]);
    }

    public function removerDeTenant(Tenant $tenant): void
    {
        $config = $tenant->configuracion ?? [];

        match($this->tipo) {
            'reporte' => $this->removerReporte($config),
            'feature' => $this->removerFeature($config),
            'recurso_extra' => $this->removerRecurso($config),
            default => null,
        };

        $tenant->update(['configuracion' => $config]);
    }

    private function aplicarReporte(array &$config): void
    {
        $enabled = $config['reportes']['enabled'] ?? [];
        if (!in_array($this->identificador, $enabled)) {
            $enabled[] = $this->identificador;
        }
        $config['reportes']['enabled'] = $enabled;
        $config['reportes']['disabled'] = array_values(array_diff(
            array_keys(Tenant::getAllAvailableReports()), $enabled
        ));
    }

    private function removerReporte(array &$config): void
    {
        $enabled = $config['reportes']['enabled'] ?? [];
        $config['reportes']['enabled'] = array_values(array_filter($enabled, fn($r) => $r !== $this->identificador));
    }

    private function aplicarFeature(array &$config): void
    {
        $features = $config['features_enabled'] ?? [];
        if (!in_array($this->identificador, $features)) {
            $features[] = $this->identificador;
        }
        $config['features_enabled'] = $features;
    }

    private function removerFeature(array &$config): void
    {
        $features = $config['features_enabled'] ?? [];
        $config['features_enabled'] = array_values(array_filter($features, fn($f) => $f !== $this->identificador));
    }

    private function aplicarRecurso(array &$config): void
    {
        $config['addons_recursos'][$this->identificador] = true;
    }

    private function removerRecurso(array &$config): void
    {
        unset($config['addons_recursos'][$this->identificador]);
    }

    private function aplicarPlantillaEmail(array &$config): void
    {
        $config['addons_plantillas_email'][$this->identificador] = true;
    }

    private function aplicarPlantillaWhatsapp(array &$config): void
    {
        $config['addons_plantillas_whatsapp'][$this->identificador] = true;
    }
}
