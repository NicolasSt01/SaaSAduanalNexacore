---
name: NexaCore Design Language
description: Guidelines and components for maintaining the premium, modern Tailwind CSS aesthetic across the NexaCore Aduanal SaaS platform.
---

# NexaCore Design Language

Este documento define los estándares visuales y componentes de UI para el ecosistema de **NexaCore Aduanal SaaS**. El objetivo es mantener una estética consistente, moderna y "premium" en todas las vistas de administración, usuarios y reportes.

## 1. Principios de Diseño
- **Estética Premium**: Uso de espacios amplios, bordes redondeados pronunciados (`rounded-2xl`, `rounded-3xl`) y sombras sutiles (`shadow-sm`, `shadow-xl`).
- **Enfoque en Datos**: La información crítica debe resaltar mediante tipografía `font-black` y colores de contraste.
- **Interactividad**: Uso de micro-animaciones (`hover:-translate-y-1`, `transition-all`) y estados claros de hover.
- **Modo Oscuro nativo**: Todas las vistas deben soportar `dark:bg-gray-900` y variantes.

## 2. Sistema de Color
- **Primario**: `indigo-600` (`#4f46e5`) para acciones principales, acentos y marca.
- **Fondo**: `gray-50` para el cuerpo, `white` para tarjetas. En dark mode: `gray-900` y `gray-800`.
- **Estados**:
  - **Éxito (Verdes/Libre)**: `emerald-500` / `emerald-600`.
  - **Alerta (Amarillos/Proceso)**: `amber-500` / `amber-600`.
  - **Peligro (Rojos/Reconocimiento)**: `rose-500` / `rose-600`.
  - **Info**: `blue-500` o `sky-500`.

## 3. Tipografía
- **Fuente Principal**: Nunito (definida en `layouts.app`).
- **Títulos de Página**: `text-3xl font-black text-gray-800 tracking-tight`.
- **Subtítulos/Labels**: `text-[10px] font-black uppercase tracking-widest text-gray-400`.
- **Cifras/Números**: `text-4xl font-black`.

## 4. Componentes Base (Tailwind)

### 4.1. Contenedor de Página Estándar
```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Título de <span class="text-indigo-600">Sección</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Descripción breve de la utilidad de esta vista.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Acciones secundarias o badges de contexto -->
        </div>
    </div>
    <!-- Contenido -->
</div>
```

### 4.2. Tarjeta de Acceso (Dashboard/Config)
```html
<a href="#" class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
    {{-- Icono de fondo decorativo --}}
    <div class="absolute -right-6 -top-6 text-indigo-50 dark:text-indigo-900/20 opacity-50 group-hover:scale-110 transition-transform duration-500">
        <i class="fas fa-users text-9xl"></i>
    </div>
    {{-- Icono principal --}}
    <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 w-12 h-12 rounded-xl flex justify-center items-center text-xl mb-4 shadow-inner relative z-10">
        <i class="fas fa-user-shield"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 dark:text-white relative z-10 group-hover:text-indigo-600 transition-colors">Título</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 relative z-10 mb-4 flex-grow">Descripción detallada.</p>
    <div class="w-full bg-gray-50 dark:bg-gray-700 p-2 rounded-lg border border-gray-100 dark:border-gray-600 text-center font-bold text-indigo-600 dark:text-indigo-400 text-xs mt-auto relative z-10 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
        INGRESAR <i class="fas fa-arrow-right ml-1"></i>
    </div>
</a>
```

### 4.3. KPI Card (Reportes)
```html
<div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-lg">
            <i class="fas fa-chart-line"></i>
        </div>
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Métrica Titulo</span>
    </div>
    <div class="text-4xl font-black text-gray-900 dark:text-white mb-1">1,234</div>
    <div class="flex items-center gap-2">
        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500" style="width: 75%"></div>
        </div>
        <span class="text-[10px] font-bold text-emerald-600">75%</span>
    </div>
</div>
```

### 4.4. Tabla Premium
- **Header**: fondo `gray-50`, texto `text-[10px] uppercase font-black`.
- **Row Hover**: `hover:bg-indigo-50/30 transition-colors`.
- **Badges de Estado**: 
  - Activo: `bg-green-100 text-green-700 border-green-200`.
  - Inactivo: `bg-gray-100 text-gray-700 border-gray-200`.

### 4.5. Alertas (Feedback)
```html
<div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm">
    <div class="flex">
        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
        <div class="ml-3">
            <p class="text-sm text-green-700 font-bold">Mensaje de éxito.</p>
        </div>
    </div>
</div>
```

## 5. Mejores Prácticas
1. **No usar colores genéricos**: Siempre usar la paleta de Tailwind configurada o extendida (ej: `indigo-600` en lugar de `blue`).
2. **Iconografía**: Usar FontAwesome 5/6 Pro, preferiblemente dentro de contenedores `rounded-xl` o `rounded-full` con fondos claros.
3. **Sombras**: Evitar sombras pesadas; usar `shadow-sm` para normal y `shadow-xl` para hover/destacados.
4. **Dark Mode**: Siempre verificar el contraste en `dark:text-gray-300` o `dark:text-white`.
