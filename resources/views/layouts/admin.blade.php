<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS NexaCore - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-900 text-white flex flex-col shadow-lg">
        <div class="h-20 flex items-center justify-center border-b border-indigo-800">
            <h1 class="text-2xl font-bold tracking-wider">
                <span class="text-indigo-300">Nexa</span><span class="text-white">Core</span> <span class="text-xs font-normal">SaaS</span>
            </h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="{{ route('admin.super_dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.super_dashboard') ? 'bg-indigo-700 text-white' : 'bg-indigo-800 text-indigo-100 hover:bg-indigo-700' }} rounded-lg transition">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('admin.tenants.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.tenants.*') ? 'bg-indigo-700 text-white' : 'bg-indigo-800 text-indigo-100 hover:bg-indigo-700' }} rounded-lg transition">
                <i class="fas fa-building w-6"></i>
                <span class="font-medium">Agencias (Tenants)</span>
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-700 flex items-center justify-center">
                    <i class="fas fa-user text-indigo-300"></i>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-indigo-300">Super Admin</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-indigo-800 hover:bg-red-600 text-white rounded transition text-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- Header -->
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 z-10">
            <h2 class="text-xl font-semibold text-gray-700">@yield('header_title', 'Dashboard')</h2>
        </header>

        <!-- Page Content -->
        <div class="p-8 flex-1">
            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
