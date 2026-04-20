<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $table = 'email_verification_tokens';

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Relación con el usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica si el token ha expirado.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Verifica si el token ya fue usado.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Marca el token como usado.
     */
    public function markAsUsed(): void
    {
        $this->used_at = now();
        $this->save();
    }

    /**
     * Scope: Tokens no usados.
     */
    public function scopeNotUsed($query)
    {
        return $query->whereNull('used_at');
    }

    /**
     * Scope: Tokens no expirados.
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
