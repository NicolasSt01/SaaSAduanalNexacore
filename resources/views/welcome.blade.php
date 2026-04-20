<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Crosspoint</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom right, #ffffff, #ffffff);
      overflow: hidden;
      position: relative;
      height: 100vh;

    }

    .floating-img {
      position: absolute;
      opacity: 0;
      object-fit: contain;
      animation: float 30s linear infinite;
    }

    /* Direcciones posibles */
    @keyframes float-up {
      0% {
        opacity: 0;
        transform: translateY(100vh) scale(1.9) rotate(0deg);
      }

      10% {
        opacity: 1;
      }

      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: translateY(-100vh) scale(2.2) rotate(360deg);
      }
    }

    @keyframes float-down {
      0% {
        opacity: 0;
        transform: translateY(-100vh) scale(1.5) rotate(0deg);
      }

      10% {
        opacity: 1;
      }

      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: translateY(100vh) scale(2.2) rotate(360deg);
      }
    }

    @keyframes float-left {
      0% {
        opacity: 0;
        transform: translateX(100vw) scale(3.5) rotate(0deg);
      }

      10% {
        opacity: 1;
      }

      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: translateX(-100vw) scale(2.2) rotate(360deg);
      }
    }

    @keyframes float-right {
      0% {
        opacity: 0;
        transform: translateX(-100vw) scale(0.5) rotate(0deg);
      }

      10% {
        opacity: 1;
      }

      90% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: translateX(100vw) scale(1.2) rotate(360deg);
      }
    }

    .centered-content {
      z-index: 2;
      position: relative;
    }

    footer {
      position: absolute;
      bottom: 10px;
      width: 100%;
      z-index: 2;
    }

    .btn-primary {
      background-color: #0056b3;
      border: none;
    }

    .btn-primary:hover {
      background-color: #003f88;
    }

    .bg-overlay {
      position: absolute;
      width: 100%;
      height: 100%;
      background-image: url('/images/fondo-abstracto.png');
      /* opcional */
      background-size: cover;
      background-repeat: no-repeat;
      opacity: 0.1;
      z-index: 1;
      pointer-events: none;
    }
  </style>
</head>

<body>
  {{-- Fondo decorativo opcional --}}
  <div class="bg-overlay"></div>

  {{-- Logos animados flotantes --}}
  @php
  use Illuminate\Support\Facades\File;
  $logos = File::files(public_path('logos'));
  $directions = ['float-up', 'float-down', 'float-left', 'float-right'];
  $minLogos = max(5, count($logos) * 5); // aseguramos al menos 5
  @endphp

  @for ($i = 0; $i < $minLogos; $i++)
    @php
    $logo = $logos[array_rand($logos)];
    $left = rand(0, 90);
    $top = rand(0, 80);
    $size = rand(40, 100);
    $delay = rand(0, 30);
    $direction = $directions[array_rand($directions)];
    $logoUrl = asset('logos/' . $logo->getFilename());
  @endphp
    <img src="{{ $logoUrl }}" class="floating-img"
    style="left: {{ $left }}%; top: {{ $top }}%; width: {{ $size }}px; animation-name: {{ $direction }}; animation-delay: {{ $delay }}s;" />
  @endfor

  {{-- Contenido principal --}}
<div class="container d-flex justify-content-center align-items-center h-100">
  <div class="card p-5 text-center shadow" style="max-width: 500px; width: 100%;">
    
    <img src="https://salassys.com/wp-content/uploads/2025/08/CROSSPOINT_MARCA_REGISTRADA_R-removebg-preview-1.png" alt="Logo Crosspoint" class="rounded-circle mb-4 mx-auto" width="280" height="150">
    
    <h1 class="h3 fw-bold mb-3">Bienvenido al portal Crosspoint</h1>
    
    <p class="lead mb-4">Consulta y gestiona tus expedientes de manera segura</p>
    
    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Iniciar Sesión</a>
    
  </div>
</div>


  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>