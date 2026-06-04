@component('mail::message')
# ¡Bienvenido a NexaCore, {{ $user->name }}!

Tu cuenta ha sido creada en **{{ $tenant->nombre_empresa }}**.

## Tus credenciales de acceso

@component('mail::panel')
**Email:** {{ $user->email }}  
**Contraseña temporal:** `{{ $password }}`
@endcomponent

@component('mail::button', ['url' => config('app.url') . '/login'])
Iniciar Sesión
@endcomponent

Al iniciar sesión por primera vez, el sistema te pedirá cambiar tu contraseña.

Si tienes alguna duda, contacta a tu administrador.

Saludos,  
Equipo NexaCore
@endcomponent
