<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - NexaCore Aduanal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Manrope', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">

    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex flex-col justify-center px-16 text-white">
                <a href="/" class="flex items-center gap-3 mb-16">
                    <img src="https://nexacore.com.mx/LogoNexaCore.png" alt="NexaCore" class="h-12 w-auto brightness-0 invert">
                </a>
                <h1 class="text-5xl font-black font-display leading-tight mb-6">
                    Comienza tu prueba gratuita de 15 días
                </h1>
                <p class="text-xl text-indigo-100 font-medium mb-12 leading-relaxed">
                    Únete a las agencias aduanales que ya transformaron su operación con NexaCore.
                </p>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Acceso completo</h3>
                            <p class="text-indigo-200 text-sm">Dashboard, expedientes, tráfico y reportes sin restricciones</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Bot de modulación incluido</h3>
                            <p class="text-indigo-200 text-sm">20 consultas al portal SOIA/PECEM del SAT</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Sin tarjeta de crédito</h3>
                            <p class="text-indigo-200 text-sm">Regístrate y comienza a operar en minutos</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/30 rounded-full blur-[120px]"></div>
            <div class="absolute top-20 left-10 w-64 h-64 bg-indigo-400/20 rounded-full blur-[80px]"></div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-lg">
                <div class="lg:hidden mb-8">
                    <a href="/" class="flex items-center gap-3">
                        <img src="https://nexacore.com.mx/LogoNexaCore.png" alt="NexaCore" class="h-10 w-auto">
                        <span class="text-xl font-extrabold tracking-tight text-gray-900">NexaCore<span class="text-indigo-600">.Aduanal</span></span>
                    </a>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-black text-gray-900 font-display mb-2">Registra tu empresa</h2>
                    <p class="text-gray-500 font-medium">Completa los datos para crear tu cuenta de prueba.</p>
                </div>

                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center gap-2 text-red-700">
                        <i class="fas fa-exclamation-circle"></i>
                        <p class="text-sm font-bold">Corrige los siguientes errores:</p>
                    </div>
                    <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('public.register') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-indigo-600 mb-4 flex items-center gap-2">
                            <i class="fas fa-building"></i> Datos de la Empresa
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Empresa / Agencia Aduanal</label>
                                <input type="text" name="nombre_empresa" value="{{ old('nombre_empresa') }}" required
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="Ej: Agencia Aduanal del Norte, S.C.">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">RFC</label>
                                    <input type="text" name="rfc" value="{{ old('rfc') }}" maxlength="13"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition uppercase"
                                        placeholder="AAA010101AAA">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                        placeholder="+52 656 123 4567">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-xs font-black uppercase tracking-widest text-indigo-600 mb-4 flex items-center gap-2">
                            <i class="fas fa-user-shield"></i> Datos del Administrador
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="Tu nombre completo">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    placeholder="admin@tuagencia.com">
                                <p class="text-xs text-gray-400 mt-1">Recibirás tu contraseña temporal en este correo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-shield-halved text-indigo-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-indigo-800">Trial de 15 días — sin compromiso</p>
                                <p class="text-xs text-indigo-600 mt-1">Acceso completo al sistema. Sin tarjeta de crédito. Tu contraseña temporal se enviará por email.</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-base hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-2">
                        <i class="fas fa-rocket"></i>
                        Crear Mi Cuenta de Prueba
                    </button>
                </form>

                <p class="text-center mt-6 text-sm text-gray-500">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
