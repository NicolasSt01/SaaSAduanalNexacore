<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistroExitosoMail;
use App\Models\EmailVerificationToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicRegisterController extends Controller
{
    /**
     * Mostrar formulario de registro público.
     */
    public function showRegister()
    {
        return view('auth.public-register');
    }

    /**
     * Procesar registro público.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nombre_empresa' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'rfc' => 'nullable|string|max:13',
        ]);

        // Generar slug único para el tenant
        $slug = Str::slug($request->nombre_empresa) . '-' . Str::random(6);

        // Generar contraseña temporal aleatoria
        $passwordTemporal = Str::random(12);

        // Crear tenant con configuración de trial
        $tenant = new Tenant();
        $tenant->slug = $slug;
        $tenant->nombre_empresa = $request->nombre_empresa;
        $tenant->correo_admin = $request->email;
        $tenant->telefono = $request->telefono;
        $tenant->rfc = $request->rfc;

        // Aplicar configuración de trial (incluye fecha_inicio, fecha_vencimiento, etc.)
        $tenant->applyTrialConfig();
        $tenant->save();

        // Crear usuario administrador
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($passwordTemporal),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
            'active' => true,
            'must_change_password' => true,
        ]);

        // Crear token de verificación de correo
        $token = Str::uuid();
        $expiresAt = now()->addHours(24);

        EmailVerificationToken::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // Enviar correo de verificación
        $verificationUrl = route('public.verify-email', ['token' => $token]);

        Mail::to($user->email)->send(
            new RegistroExitosoMail($user, $tenant, $verificationUrl, $passwordTemporal)
        );

        return redirect()->route('public.register-success')
            ->with('success', '¡Registro exitoso! Hemos enviado un correo con tu contraseña temporal y link de verificación.');
    }

    /**
     * Mostrar página de éxito de registro.
     */
    public function registerSuccess()
    {
        return view('auth.register-success');
    }

    /**
     * Verificar correo electrónico con token.
     */
    public function verifyEmail($token)
    {
        $verificationToken = EmailVerificationToken::where('token', $token)
            ->notUsed()
            ->first();

        if (!$verificationToken) {
            return redirect()->route('login')
                ->with('error', 'El link de verificación no es válido o ya fue usado.');
        }

        if ($verificationToken->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'El link de verificación ha expirado. Por favor contacta a soporte.');
        }

        // Marcar email como verificado
        $user = $verificationToken->user;
        $user->email_verified_at = now();
        $user->save();

        // Marcar token como usado
        $verificationToken->markAsUsed();

        return redirect()->route('login')
            ->with('success', '¡Correo verificado exitosamente! Ahora puedes iniciar sesión con tu contraseña temporal.');
    }

    /**
     * Mostrar formulario de cambio de contraseña (primer login).
     */
    public function showChangePassword()
    {
        if (!auth()->check() || !auth()->user()->mustChangePassword()) {
            return redirect()->route('home');
        }

        return view('auth.change-first-password');
    }

    /**
     * Procesar cambio de contraseña.
     */
    public function changeFirstPassword(Request $request)
    {
        if (!auth()->check() || !auth()->user()->mustChangePassword()) {
            return redirect()->route('home');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->new_password);
        $user->markPasswordAsChanged();

        // Iniciar trial del tenant si no se ha iniciado
        if ($user->tenant) {
            $user->tenant->startTrial();
        }

        return redirect()->route('home')
            ->with('success', '¡Contraseña actualizada exitosamente! Bienvenido a NexaCore Aduanal.');
    }
}
