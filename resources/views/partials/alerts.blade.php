@if(session('success'))
<div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm">
    <div class="flex">
        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
        <div class="ml-3">
            <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
    <div class="flex">
        <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
        <div class="ml-3">
            <p class="text-sm text-rose-700 font-bold">{{ session('error') }}</p>
        </div>
    </div>
</div>
@endif

@if(session('warning'))
<div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
    <div class="flex">
        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
        <div class="ml-3">
            <p class="text-sm text-amber-700 font-bold">{{ session('warning') }}</p>
        </div>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
    <div class="flex">
        <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
        <div class="ml-3">
            <p class="text-sm font-bold text-rose-700">Por favor corrige los siguientes errores:</p>
            <ul class="mt-1 text-sm text-rose-600 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif
