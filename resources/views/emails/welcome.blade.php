@component('mail::message')
# ¡Bienvenido a NexaCore Aduanal, {{ $user->name }}!

Tu cuenta ha sido creada para gestionar operaciones de comercio exterior en la plataforma **NexaCore Aduanal**.

## Tus credenciales de acceso

@component('mail::panel')
**Email:** {{ $user->email }}  
**Contraseña temporal:** `{{ $password }}`
@endcomponent

@component('mail::button', ['url' => config('app.url') . '/login'])
Iniciar Sesión
@endcomponent

Al iniciar sesión por primera vez, el sistema te pedirá cambiar tu contraseña.

Si tienes alguna duda, contacta a tu administrador o escríbenos a **contacto@nexacore.com.mx**.

Saludos,  
Equipo NexaCore Aduanal
@endcomponent
