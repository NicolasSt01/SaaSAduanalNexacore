@extends('layouts.app')

@section('title', 'Configuración SMTP - Servidor de Correo')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.config') }}"
                class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">Configuración <span
                        class="text-orange-600">SMTP</span></h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Configura tu propio servidor de correo para enviar
                    notificaciones a tus clientes.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
                    <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 text-lg"></i>
                <div>
                    <h3 class="text-sm font-bold text-blue-800 mb-1">¿Por qué configurar tu propio SMTP?</h3>
                    <p class="text-sm text-blue-700 leading-relaxed">
                        Como Agencia puedes configurar <strong>tus propias credenciales de correo</strong>. Esto garantiza
                        que los
                        correos se envíen desde tu dominio corporativo,
                        mejorando la deliverability y manteniendo tu branding. Los correos saldrán desde tu
                        cuenta
                        (ej: <code
                            class="bg-blue-100 px-1.5 py-0.5 rounded text-blue-800">notificaciones@tuagencia.com</code>).
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.config.guardar-smtp') }}" id="smtpForm">
            @csrf

            <!-- Quick Setup Buttons -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 text-lg">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Configuración Rápida</h2>
                        <p class="text-xs text-gray-400">Selecciona tu proveedor para autocompletar los datos del servidor
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" onclick="fillGmail()"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 hover:border-red-400 hover:bg-red-50 transition-all group">
                        <i class="fab fa-google text-red-500 text-xl"></i>
                        <span class="font-bold text-gray-700 group-hover:text-red-600">Gmail</span>
                    </button>

                    <button type="button" onclick="fillOutlook()"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <i class="fab fa-microsoft text-blue-500 text-xl"></i>
                        <span class="font-bold text-gray-700 group-hover:text-blue-600">Outlook/Hotmail</span>
                    </button>

                    <button type="button" onclick="fillHostGator()"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 hover:border-green-400 hover:bg-green-50 transition-all group">
                        <i class="fas fa-server text-green-500 text-xl"></i>
                        <span class="font-bold text-gray-700 group-hover:text-green-600">HostGator</span>
                    </button>
                </div>

                <div id="gmailHelp" class="hidden mt-4 bg-red-50 border border-red-100 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-question-circle text-red-500 mt-0.5"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-bold mb-2">¿Cómo obtener tu contraseña de aplicación en Gmail?</p>
                            <ol class="list-decimal list-inside space-y-1 text-red-700">
                                <li>Ve a tu <a href="https://myaccount.google.com/security" target="_blank"
                                        class="underline hover:text-red-900">Cuenta de Google</a></li>
                                <li>Activa la <strong>Verificación en 2 pasos</strong> (si no está activa)</li>
                                <li>Busca la opción <strong>"Contraseñas de aplicación"</strong></li>
                                <li>Genera una contraseña para "Correo" y "Otra aplicación"</li>
                                <li>Copia la contraseña de 16 caracteres y pégala abajo</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMTP Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 text-lg">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Datos del Servidor SMTP</h2>
                        <p class="text-xs text-gray-400">Ingresa las credenciales de tu servidor de correo saliente</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Host -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-server text-orange-500 mr-1"></i> Servidor SMTP (Host)
                        </label>
                        <input type="text" name="smtp_host" id="smtp_host" value="{{ $config['smtp_host'] ?? '' }}"
                            placeholder="ej: smtp.gmail.com"
                            class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 text-lg font-bold text-gray-800"
                            required>
                        <p class="text-xs text-gray-400 mt-1">Dirección del servidor SMTP de tu proveedor</p>
                    </div>

                    <!-- Port -->
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-plug text-orange-500 mr-1"></i> Puerto
                        </label>
                        <select name="smtp_port" id="smtp_port"
                            class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 text-lg font-bold text-gray-800"
                            required>
                            <option value="587" {{ ($config['smtp_port'] ?? '') == '587' ? 'selected' : '' }}>587 (TLS -
                                Recomendado)</option>
                            <option value="465" {{ ($config['smtp_port'] ?? '') == '465' ? 'selected' : '' }}>465 (SSL)
                            </option>
                            <option value="2525" {{ ($config['smtp_port'] ?? '') == '2525' ? 'selected' : '' }}>2525
                                (Alternativo)</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Puerto de conexión segura</p>
                    </div>

                    <!-- Encryption -->
                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-lock text-orange-500 mr-1"></i> Encriptación
                        </label>
                        <select name="smtp_encryption" id="smtp_encryption"
                            class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 text-lg font-bold text-gray-800">
                            <option value="tls" {{ ($config['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS
                            </option>
                            <option value="ssl" {{ ($config['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL
                            </option>
                            <option value="" {{ ($config['smtp_encryption'] ?? '') == '' ? 'selected' : '' }}>Ninguna</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Protocolo de seguridad</p>
                    </div>

                    <!-- Username -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-user text-orange-500 mr-1"></i> Usuario / Correo
                        </label>
                        <input type="email" name="smtp_username" id="smtp_username"
                            value="{{ $config['smtp_username'] ?? '' }}" placeholder="ej: notificaciones@tuagencia.com"
                            class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 text-lg font-bold text-gray-800"
                            required>
                        <p class="text-xs text-gray-400 mt-1">Correo o usuario para autenticación</p>
                    </div>

                    <!-- Password -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-key text-orange-500 mr-1"></i> Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" name="smtp_password" id="smtp_password"
                                placeholder="{{ isset($config['smtp_password']) ? '******** (ya configurada)' : 'Ingresa tu contraseña de aplicación' }}"
                                class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 text-lg font-bold text-gray-800 pr-12"
                                {{ isset($config['smtp_password']) ? '' : 'required' }}>
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @if(isset($config['smtp_password']))
                            <p class="text-xs text-emerald-600 mt-1">
                                <i class="fas fa-check-circle mr-1"></i> Ya tienes una contraseña configurada. Ingresa una nueva
                                solo si deseas cambiarla.
                            </p>
                        @else
                            <p class="text-xs text-gray-400 mt-1">Contraseña de aplicación (no tu contraseña normal)</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- From Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Remitente de los Correos</h2>
                        <p class="text-xs text-gray-400">Cómo se verán los correos que reciben tus clientes</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- From Address -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-envelope text-indigo-500 mr-1"></i> Correo de Remitente
                        </label>
                        <input type="email" name="smtp_from_address" id="smtp_from_address"
                            value="{{ $config['smtp_from_address'] ?? '' }}" placeholder="ej: notificaciones@tuagencia.com"
                            class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800"
                            required>
                        <p class="text-xs text-gray-400 mt-1">Este correo aparecerá como remitente en todos los envíos</p>
                    </div>

                    <!-- From Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-signature text-indigo-500 mr-1"></i> Nombre del Remitente
                        </label>
                        <input type="text" name="smtp_from_name" id="smtp_from_name"
                            value="{{ $config['smtp_from_name'] ?? '' }}"
                            placeholder="ej: {{ auth()->user()->tenant->nombre_empresa ?? 'Tu Agencia Aduanal' }}"
                            class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800"
                            required>
                        <p class="text-xs text-gray-400 mt-1">Nombre que verán tus clientes (recomendado: nombre de tu
                            empresa)</p>
                    </div>
                </div>
            </div>

            <!-- Preview Box -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-100 rounded-2xl p-5 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-eye text-orange-500 mt-0.5"></i>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-orange-800 mb-2">Así verán tus clientes los correos:</h3>
                        <div class="bg-white rounded-xl border border-orange-100 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-user text-orange-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800" id="previewFromName">
                                        {{ $config['smtp_from_name'] ?? (auth()->user()->tenant->nombre_empresa ?? 'Tu Agencia') }}
                                    </p>
                                    <p class="text-xs text-gray-500" id="previewFromAddress">
                                        {{ $config['smtp_from_address'] ?? 'correo@tuagencia.com' }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-info-circle mr-1"></i> Los correos de notificación de operaciones saldrán
                                con estos datos
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between items-center">
                <button type="button" onclick="probarConexion()"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 transition flex items-center gap-2">
                    <i class="fas fa-plug"></i>
                    Probar Conexión
                </button>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.config') }}"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 shadow-sm transition">
                        <i class="fas fa-save mr-1"></i> Guardar Configuración SMTP
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de resultado de prueba -->
    <div id="testModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md mx-4">
            <div id="testLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-600 mx-auto mb-4"></div>
                <p class="text-gray-600 font-bold">Probando conexión SMTP...</p>
                <p class="text-sm text-gray-400 mt-2">Esto puede tomar unos segundos</p>
            </div>
            <div id="testResult" class="hidden">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        // Quick setup presets
        function fillGmail() {
            document.getElementById('smtp_host').value = 'smtp.gmail.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
            document.getElementById('gmailHelp').classList.remove('hidden');

            // Update preview
            updatePreview('Gmail Notifications', document.getElementById('smtp_username').value || 'tuemail@gmail.com');
        }

        function fillOutlook() {
            document.getElementById('smtp_host').value = 'smtp-mail.outlook.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
            document.getElementById('gmailHelp').classList.add('hidden');

            updatePreview('Outlook Notifications', document.getElementById('smtp_username').value || 'tuemail@outlook.com');
        }

        function fillHostGator() {
            document.getElementById('smtp_host').value = 'smtp.tudominio.com';
            document.getElementById('smtp_port').value = '465';
            document.getElementById('smtp_encryption').value = 'ssl';
            document.getElementById('gmailHelp').classList.add('hidden');

            updatePreview('Hosting Notifications', document.getElementById('smtp_username').value || 'correo@tudominio.com');
        }

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('smtp_password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Update preview when typing
        document.getElementById('smtp_from_name').addEventListener('input', function (e) {
            document.getElementById('previewFromName').textContent = e.target.value || 'Tu Agencia';
        });

        document.getElementById('smtp_from_address').addEventListener('input', function (e) {
            document.getElementById('previewFromAddress').textContent = e.target.value || 'correo@tuagencia.com';
        });

        function updatePreview(name, email) {
            document.getElementById('previewFromName').textContent = name;
            document.getElementById('previewFromAddress').textContent = email;
        }

        // Test SMTP connection
        function probarConexion() {
            const form = document.getElementById('smtpForm');
            const formData = new FormData(form);

            // Mostrar modal
            document.getElementById('testModal').classList.remove('hidden');
            document.getElementById('testLoading').classList.remove('hidden');
            document.getElementById('testResult').classList.add('hidden');

            // Primero guardar la configuración
            fetch("{{ route('admin.config.guardar-smtp') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    // Ahora probar la conexión
                    return fetch("{{ route('admin.config.smtp.probar') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('testLoading').classList.add('hidden');
                    document.getElementById('testResult').classList.remove('hidden');

                    if (data.success) {
                        document.getElementById('testResult').innerHTML = `
                                    <div class="text-center">
                                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-check text-emerald-500 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-800 mb-2">¡Éxito!</h3>
                                        <p class="text-sm text-gray-600 mb-4">${data.message}</p>
                                        <button onclick="document.getElementById('testModal').classList.add('hidden')" 
                                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 text-sm font-bold">
                                            Cerrar
                                        </button>
                                    </div>
                                `;
                    } else {
                        document.getElementById('testResult').innerHTML = `
                                    <div class="text-center">
                                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-times text-red-500 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-800 mb-2">Error de Conexión</h3>
                                        <p class="text-sm text-gray-600 mb-4">${data.message}</p>
                                        <button onclick="document.getElementById('testModal').classList.add('hidden')" 
                                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-bold">
                                            Cerrar
                                        </button>
                                    </div>
                                `;
                    }
                })
                .catch(error => {
                    document.getElementById('testLoading').classList.add('hidden');
                    document.getElementById('testResult').classList.remove('hidden');
                    document.getElementById('testResult').innerHTML = `
                                <div class="text-center">
                                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-800 mb-2">Error</h3>
                                    <p class="text-sm text-gray-600 mb-4">Ocurrió un error al probar la conexión. Inténtalo de nuevo.</p>
                                    <button onclick="document.getElementById('testModal').classList.add('hidden')" 
                                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-bold">
                                        Cerrar
                                    </button>
                                </div>
                            `;
                });
        }
    </script>
@endsection