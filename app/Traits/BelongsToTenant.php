<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // 1. Scope global para filtrar registros por tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->hasUser() && auth()->user()->tenant_id) {
                // Si el usuario es super_admin, no aplicamos scope (ve todo)
                if (method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin()) {
                    return;
                }
                $builder->where($builder->getModel()->qualifyColumn('tenant_id'), auth()->user()->tenant_id);
            }
        });

        // 2. Evento creating para auto-asignar tenant_id al crear registros
        static::creating(function ($model) {
            if (empty($model->tenant_id) && auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
