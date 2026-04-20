<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexaCore Aduanal | Gestión Inteligente de Operaciones de Comercio Exterior</title>
    <meta name="description"
        content="Sustituye tus Excels por el ERP líder para agencias aduanales. Control total de expedientes, tráfico y cumplimiento legal Art. 36-A.">

    <!-- Fonts: Inter & Manrope -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Manrope', 'sans-serif'],
                    },
                    colors: {
                        nexa: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            200: '#ced9fd',
                            300: '#adc0fc',
                            400: '#8da7fa',
                            500: '#6d8ef9',
                            600: '#5771c7',
                            700: '#415595',
                            800: '#2b3964',
                            900: '#161c32',
                        },
                    }
                }
            }
        }
    </script>

    <style>
        .hero-mesh {
            background-color: #ffffff;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .section-tag {
            @apply inline-flex items-center px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-4;
        }

        .gradient-text {
            background: linear-gradient(to right, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-modern {
            @apply bg-white border border-gray-100 rounded-3xl p-8 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1;
        }

        .pricing-dot {
            @apply w-2 h-2 rounded-full bg-indigo-500 mr-3;
        }
    </style>
</head>

<body class="bg-white text-gray-900 overflow-x-hidden antialiased">

    <!-- Navigation -->
    <header class="fixed top-0 w-full z-50 py-4 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-xl shadow-indigo-600/30">
                    <i class="fas fa-microchip text-lg"></i>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-gray-900 group" id="brand-name">
                    NexaCore<span class="text-indigo-600">.Aduanal</span>
                </span>
            </a>

            <div class="hidden md:flex items-center gap-10">
                <a href="#proceso" class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">Operación</a>
                <a href="#compliance"
                    class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">Cumplimiento</a>
                <a href="#analiticas"
                    class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">Analítica</a>
                <a href="#precios" class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">Planes</a>
                <a href="{{ route('login') }}"
                    class="px-6 py-2 bg-indigo-600 rounded-full text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/20">
                    Acceso Portal
                </a>
            </div>

            <button class="md:hidden text-white"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-mesh min-h-screen relative flex items-center pt-24 pb-16">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-8">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    Sistema ERP Integral - 2026 Ready
                </div>
                <h1 class="text-6xl lg:text-7xl font-extrabold text-gray-900 font-display leading-[1.1] mb-8">
                    Adiós a los <span class="text-indigo-600">Excels</span>. <br>Hola a la Gestión Real.
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed max-w-lg mb-10 font-medium">
                    Centraliza tu operación aduanal en una plataforma en tiempo real. Automatiza la modulación, el
                    cumplimiento legal y la atención al cliente.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#precios"
                        class="px-8 py-4 bg-white text-indigo-900 rounded-2xl font-extrabold text-lg transition hover:bg-indigo-50">
                        Comenzar Ahora
                    </a>
                    <a href="#proceso"
                        class="px-8 py-4 bg-gray-100 border border-gray-200 text-gray-700 rounded-2xl font-extrabold text-lg transition hover:bg-gray-200">
                        Ver Funcionalidades
                    </a>
                </div>
            </div>

            <!-- Login Quick Card -->
            <div class="relative">
                <div class="absolute -top-10 -right-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-[100px]"></div>
                <div
                    class="bg-white border border-gray-100 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-5">
                        <i class="fas fa-fingerprint text-6xl text-indigo-900"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">Ingresar</h3>
                    <p class="text-gray-500 text-sm mb-8 font-medium">Gestión y monitoreo de operaciones corporativas.
                    </p>

                    <form action="{{ route('login.attempt') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 ml-1">Email
                                Corporativo</label>
                            <input type="email" name="email" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition shadow-inner font-medium"
                                placeholder="admin@tuagencia.com">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 ml-1">Contraseña</label>
                            <input type="password" name="password" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition shadow-inner"
                                placeholder="••••••••">
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold">
                            <label class="flex items-center gap-2 cursor-pointer text-gray-400">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 rounded bg-gray-50 border-gray-200 text-indigo-600 focus:ring-0">
                                Recordarme
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-indigo-400 hover:text-indigo-300">¿Olvidaste tu contraseña?</a>
                        </div>
                        <button type="submit"
                            class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-lg transition hover:bg-indigo-700 shadow-xl shadow-indigo-600/30">
                            Acceder al Sistema
                        </button>
                    </form>
                    <p class="text-center mt-8 text-xs text-gray-500 font-semibold">¿Nuevo en NexaCore? <a href="#"
                            class="text-indigo-400 underline underline-offset-4">Solicitar Demo</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Operational Process -->
    <section id="proceso" class="py-32 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="max-w-3xl mb-24">
                <div class="section-tag">Flujo Operativo</div>
                <h2 class="text-5xl lg:text-6xl font-black font-display tracking-tight text-gray-900 mb-6">Un proceso
                    <span class="text-indigo-600">impecable</span>.</h2>
                <p class="text-xl text-gray-600 font-medium leading-relaxed">Desde la captura hasta la modulación,
                    NexaCore asegura que cada dato y documento esté en su lugar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="card-modern">
                    <div
                        class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl mb-8">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <h3 class="text-xl font-extrabold mb-4">Captura Ágil</h3>
                    <p class="text-gray-500 text-sm leading-relaxed font-medium">Registro centralizado de clientes,
                        importadores y facturas con validación de datos en tiempo real.</p>
                </div>
                <!-- Step 2 -->
                <div class="card-modern">
                    <div
                        class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl mb-8">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <h3 class="text-xl font-extrabold mb-4">Clasificación Digital</h3>
                    <p class="text-gray-500 text-sm leading-relaxed font-medium">Subida y clasificación inteligente de
                        archivos asociada a cada operación bajo el Art. 36-A.</p>
                </div>
                <!-- Step 3 -->
                <div class="card-modern">
                    <div
                        class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl mb-8">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="text-xl font-extrabold mb-4">Bot de Modulación</h3>
                    <p class="text-gray-500 text-sm leading-relaxed font-medium">Automatización que monitorea estatus
                        SOIA y notifica a clientes vía Correo & WhatsApp de forma inmediata.</p>
                </div>
                <!-- Step 4 -->
                <div class="card-modern">
                    <div
                        class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl mb-8">
                        <i class="fas fa-tv"></i>
                    </div>
                    <h3 class="text-xl font-extrabold mb-4">War Room Ready</h3>
                    <p class="text-gray-500 text-sm leading-relaxed font-medium">Interfaz optimizada para pantallas de
                        monitoreo 24/7 en tu centro de operaciones.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Compliance Section -->
    <section id="compliance" class="py-32 relative overflow-hidden">
        <!-- Decoration -->
        <div
            class="absolute right-0 top-0 translate-x-1/3 -translate-y-1/3 w-96 h-96 bg-indigo-50 rounded-full blur-[120px]">
        </div>

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
            <div class="order-2 lg:order-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-8 bg-gray-900 rounded-3xl text-white">
                        <i class="fas fa-gavel text-indigo-400 text-3xl mb-6"></i>
                        <h4 class="text-lg font-bold mb-2 uppercase tracking-tight">Legalidad</h4>
                        <p class="text-gray-400 text-xs leading-relaxed font-medium">Total cumplimiento con las RGCE
                            2026 y Ley Aduanera.</p>
                    </div>
                    <div class="p-8 bg-indigo-600 rounded-3xl text-white translate-y-8">
                        <i class="fas fa-fingerprint text-white text-3xl mb-6"></i>
                        <h4 class="text-lg font-bold mb-2 uppercase tracking-tight">Auditoría</h4>
                        <p class="text-indigo-100 text-xs leading-relaxed font-medium">Trazabilidad completa: Quién
                            captura, quién monitorea y quién cierra.</p>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <div class="section-tag">Compliance Total</div>
                <h2
                    class="text-5xl lg:text-6xl font-black font-display tracking-tight text-gray-900 mb-8 leading-tight">
                    Blindaje ante la <span
                        class="text-indigo-600 underline decoration-indigo-200 underline-offset-8">Autoridad</span>.
                </h2>
                <p class="text-xl text-gray-600 font-medium mb-10 leading-relaxed">Olvida las multas por expedientes
                    incompletos. Nuestro sistema valida la existencia de cada documento mandatorio antes de permitir el
                    cierre del pedimento.</p>

                <ul class="space-y-6">
                    <li class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Validación Automática de Documentación de
                            Expediente</span>
                    </li>
                    <li class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Expediente Maestro y Operativo Integrado</span>
                    </li>
                    <li class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Acuses de Recepción y Archivos de
                            Clasificación</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Dashboards Info -->
    <section id="analiticas" class="py-32 bg-indigo-900 text-white relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-24">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 border border-white/20 rounded-full text-indigo-300 text-[10px] font-black uppercase tracking-[0.2em] mb-6">
                    Business Intelligence
                </div>
                <h2 class="text-5xl font-black font-display mb-6">Monitoreo de Próxima Generación.</h2>
                <p class="text-indigo-200/70 max-w-2xl mx-auto text-lg font-medium leading-relaxed">Vistas diferenciadas
                    que se adaptan a la necesidad de cada departamento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div
                    class="p-12 border border-white/10 rounded-[3rem] bg-white/5 backdrop-blur-sm group hover:bg-white/10 transition-all duration-500">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-4 block">Dashboard</span>
                    <h3 class="text-3xl font-black mb-6">Administración</h3>
                    <p class="text-indigo-200/60 leading-relaxed font-medium mb-8">Control total del volumen operativo,
                        métricas por cliente, remesas diarias y cumplimiento global. Perfecto para gerentes y dueños.
                    </p>
                    <div class="h-1 w-20 bg-indigo-500 rounded-full group-hover:w-full transition-all duration-700">
                    </div>
                </div>
                <div
                    class="p-12 border border-white/10 rounded-[3rem] bg-indigo-600/20 backdrop-blur-sm group hover:bg-white/10 transition-all duration-500">
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-4 block">Dashboard</span>
                    <h3 class="text-3xl font-black mb-6">Documentación</h3>
                    <p class="text-indigo-200/60 leading-relaxed font-medium mb-8">Enfoque operativo de alta velocidad.
                        Captura, monitoreo de estatus, validación de archivos y notificaciones en un solo panel.</p>
                    <div class="h-1 w-20 bg-white rounded-full group-hover:w-full transition-all duration-700"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precios" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-24">
                <div class="section-tag">Inversión</div>
                <h2 class="text-5xl font-black font-display text-gray-900 mb-6 tracking-tight">Elige el Motor de tu
                    <span class="text-indigo-600">Crecimiento</span>.</h2>
                <p class="text-gray-500 font-medium text-lg">Sin letras chiquitas. Planes escalables para cada volumen
                    de operación.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Basic -->
                <div
                    class="p-10 bg-gray-50 rounded-[3rem] border border-transparent hover:border-gray-200 transition-all flex flex-col">
                    <div class="flex justify-between items-start mb-8 font-display">
                        <div>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1 block">Básico</span>
                            <h3 class="text-2xl font-black text-gray-900">Control Inicial</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-500">$</span>
                            <span class="text-4xl font-black text-gray-900">4,999</span>
                            <span class="text-[10px] block font-bold text-gray-400">MXN/MES</span>
                        </div>
                    </div>
                    <ul class="space-y-5 flex-grow mb-12">
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            Hasta 5 usuarios</li>
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            100 operaciones mensuales</li>
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            Expediente Digital</li>
                        <li class="flex items-center text-sm font-bold text-gray-300 line-through"><span
                                class="pricing-dot bg-gray-200"></span> Bot de WhatsApp</li>
                    </ul>
                    <a href="#"
                        class="w-full py-5 bg-gray-900 text-white rounded-2xl font-black text-center hover:bg-black transition-all">
                        Suscribirse
                    </a>
                </div>

                <!-- Pro (Featured) -->
                <div
                    class="p-12 bg-white rounded-[3.5rem] border-2 border-indigo-600 shadow-2xl shadow-indigo-500/10 flex flex-col scale-105 relative z-10">
                    <div
                        class="absolute -top-5 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-6 py-2 rounded-full text-[10px] font-black tracking-widest uppercase">
                        Popular</div>
                    <div class="flex justify-between items-start mb-10 font-display">
                        <div>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1 block">Profesional</span>
                            <h3 class="text-3xl font-black text-gray-900 leading-tight">Escala Operativa</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-500">$</span>
                            <span class="text-5xl font-black text-gray-900">9,999</span>
                            <span class="text-[10px] block font-bold text-gray-400">MXN/MES</span>
                        </div>
                    </div>
                    <ul class="space-y-6 flex-grow mb-12">
                        <li class="flex items-center text-base font-bold text-gray-800 group"><i
                                class="fas fa-check-circle text-indigo-600 mr-3 transition group-hover:scale-110"></i>
                            Hasta 20 usuarios</li>
                        <li class="flex items-center text-base font-bold text-gray-800 group"><i
                                class="fas fa-check-circle text-indigo-600 mr-3 transition group-hover:scale-110"></i>
                            500 operaciones</li>
                        <li class="flex items-center text-base font-bold text-gray-800 group"><i
                                class="fas fa-check-circle text-indigo-600 mr-3 transition group-hover:scale-110"></i>
                            Bot Automático (WhatsApp)</li>
                        <li class="flex items-center text-base font-bold text-gray-800 group"><i
                                class="fas fa-check-circle text-indigo-600 mr-3 transition group-hover:scale-110"></i>
                            Reportes Ejecutivos Clientes</li>
                    </ul>
                    <a href="#"
                        class="w-full py-6 bg-indigo-600 text-white rounded-3xl font-black text-lg text-center hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/30">
                        Adquirir ahora
                    </a>
                </div>

                <!-- Enterprise -->
                <div
                    class="p-10 bg-gray-50 rounded-[3rem] border border-transparent hover:border-gray-200 transition-all flex flex-col">
                    <div class="flex justify-between items-start mb-8 font-display">
                        <div>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1 block">Enterprise</span>
                            <h3 class="text-2xl font-black text-gray-900">Limitless</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-500">$</span>
                            <span class="text-4xl font-black text-gray-900">19,999</span>
                            <span class="text-[10px] block font-bold text-gray-400">MXN/MES</span>
                        </div>
                    </div>
                    <ul class="space-y-5 flex-grow mb-12">
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            Usuarios ilimitados</li>
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            Operaciones ilimitadas</li>
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            API Access Individual</li>
                        <li class="flex items-center text-sm font-bold text-gray-700"><span class="pricing-dot"></span>
                            Soporte 24/7 VIP</li>
                    </ul>
                    <a href="#"
                        class="w-full py-5 bg-gray-900 text-white rounded-2xl font-black text-center hover:bg-black transition-all">
                        Contactar Soporte
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-24">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                            <i class="fas fa-microchip text-xs"></i>
                        </div>
                        <span class="text-lg font-black tracking-tight text-gray-900">NexaCore<span
                                class="text-indigo-600">.Aduanal</span></span>
                    </div>
                    <p class="text-gray-500 font-medium leading-relaxed max-w-sm mb-10">Reinventando la logística y el
                        cumplimiento legal mediante tecnología de datos e inteligencia operativa.</p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-indigo-600 hover:text-white transition-all"><i
                                class="fab fa-linkedin-in"></i></a>
                        <a href="#"
                            class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-indigo-600 hover:text-white transition-all"><i
                                class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                    <h5 class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-8">Explora</h5>
                    <ul class="space-y-4 text-sm font-bold text-gray-500">
                        <li><a href="#proceso" class="hover:text-indigo-600 transition">Nuestro Proceso</a></li>
                        <li><a href="#compliance" class="hover:text-indigo-600 transition">Cumplimiento Legal</a></li>
                        <li><a href="#precios" class="hover:text-indigo-600 transition">Planes Especiales</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-8">Legal</h5>
                    <ul class="space-y-4 text-sm font-bold text-gray-500">
                        <li><a href="#" class="hover:text-indigo-600 transition">Privacidad</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition">Términos del Servicio</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition">Contrato de Licencia</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center pt-10 border-t border-gray-200 gap-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">&copy; 2026 NexaCore Aduanal
                    Systems. Made with <i class="fas fa-heart text-red-500"></i> for Logistics.</p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sistemas Operativos
                        100%</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function () {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('glass-header', 'shadow-2xl', 'shadow-black/5');
                navbar.classList.remove('py-4');
                navbar.classList.add('py-2');
            } else {
                navbar.classList.remove('glass-header', 'shadow-2xl', 'shadow-black/5');
                navbar.classList.remove('py-2');
                navbar.classList.add('py-4');
            }
        });
    </script>

</body>

</html>