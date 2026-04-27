{{-- Estructura principal base (Layout) que envuelve a la mayoría de las vistas del sistema con la navegación y scripts globales --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - ImpulWeb</title>
    @vite(['resources/css/app.css', 'resources/css/layout.css', 'resources/js/app.js'])
    @stack('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gradient-to-br from-[#fbfcfc] to-[#f0f4f8] min-h-screen selection:bg-[#4a7c59] selection:text-white text-slate-800">

    @if(isset($negocio))
    <nav class="bg-white/60 backdrop-blur-xl border-b border-white/40 shadow-sm px-4 md:px-8 py-3 flex flex-col md:flex-row justify-between items-center gap-3 md:gap-0 sticky top-0 z-50">

        {{-- Logo + nombre --}}
        <div class="flex items-center gap-2.5 hover:opacity-80 transition cursor-default">
            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-[#4a7c59] to-[#2d4a35] shadow-sm"></span>
            <h1 class="text-[#2d4a35] text-lg font-medium tracking-tight leading-none text-center md:text-left">
                {{ $negocio->nombre_comercial }}
            </h1>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-wrap items-center justify-center gap-2 md:gap-3">

            {{-- Botón Recomendaciones --}}
            @if(Route::currentRouteName() === 'dashboard')
            <button onclick="abrirRecomendaciones()"
                class="nav-btn-emerald relative">
                <i class="bi bi-lightbulb text-sm"></i>
                Recomendaciones
                @if(isset($hayRecomendacionesNuevas) && $hayRecomendacionesNuevas)
                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 shadow-sm shadow-red-500/40 rounded-full text-white text-[0.6rem] flex items-center justify-center animate-pulse">!</span>
                @endif
            </button>
            @endif

            @if($negocio->esReventa())
            <a href="{{ route('facturas.historial') }}"
                class="nav-btn-slate">
                <i class="bi bi-receipt text-sm"></i>
                Facturas
            </a>
            @endif

            <a href="/configuracion/editar"
            class="nav-btn-slate">
                <i class="bi bi-gear text-sm"></i>
                Configuración
            </a>
        <a href="{{ route('dashboard') }}" 
           class="nav-btn-slate">
            <i class="bi bi-grid-1x2"></i> <span class="hidden sm:inline">Dashboard</span>
        </a>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit"
                        class="nav-btn-rose">
                    <i class="bi bi-box-arrow-right text-sm"></i>
                    Cerrar sesión
                </button>
            </form>
        </div>

    </nav>
    @endif

    <main class="w-full max-w-7xl mx-auto px-5 md:px-8 lg:px-12 xl:px-16 py-8">

        @if(session('success'))
            <div class="flex items-center gap-3 bg-[#e8f5e2] text-[#2d5a35] border border-[#c0ddb8] px-4 py-3 rounded-xl mb-6 text-sm">
                <i class="bi bi-check-circle-fill text-[#4a7c59]"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 bg-[#fdecea] text-[#7a2d2d] border border-[#f0c0bc] px-4 py-3 rounded-xl mb-6 text-sm">
                <i class="bi bi-exclamation-circle-fill text-red-400"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="flex items-start gap-3 bg-[#fdecea] text-[#7a2d2d] border border-[#f0c0bc] px-4 py-3 rounded-xl mb-6 text-sm">
                <i class="bi bi-exclamation-circle-fill text-red-400 mt-0.5"></i>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
            const originalGet = descriptor.get;
            const originalSet = descriptor.set;

            Object.defineProperty(HTMLInputElement.prototype, 'value', {
                get: function() {
                    let val = originalGet.call(this);
                    if (this.classList.contains('format-currency') && val) {
                        return val.replace(/\./g, '').replace(',', '.');
                    }
                    return val;
                },
                set: function(val) {
                    originalSet.call(this, val);
                    if (this.classList.contains('format-currency')) {
                        formatVisible(this, true);
                    }
                }
            });

            function formatVisible(input, isInitial = false) {
                let val = originalGet.call(input);
                if (!val) return;
                
                if (isInitial && typeof val === 'string' && /^-?\d+(\.\d+)?$/.test(val)) {
                    val = val.replace('.', ',');
                }
                
                let isNegative = val.startsWith('-');
                let cleaned = val.replace(/[^0-9,]/g, '');
                let parts = cleaned.split(',');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                
                if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                originalSet.call(input, (isNegative ? '-' : '') + parts.join(','));
            }

            function initFormatCurrency(input) {
                if (!input.classList.contains('format-currency-initialized')) {
                    try { input.type = 'text'; } catch(e){}
                    input.inputMode = 'decimal';
                    input.classList.add('format-currency');
                    input.classList.add('format-currency-initialized');
                    formatVisible(input, true);
                }
            }

            document.querySelectorAll('input[type="number"]').forEach(initFormatCurrency);

            const observer = new MutationObserver(mutations => {
                mutations.forEach(m => m.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        if (node.tagName === 'INPUT' && node.type === 'number') initFormatCurrency(node);
                        if (node.querySelectorAll) {
                            node.querySelectorAll('input[type="number"]').forEach(initFormatCurrency);
                        }
                    }
                }));
            });
            observer.observe(document.body, { childList: true, subtree: true });

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('format-currency')) {
                    let cursor = e.target.selectionStart;
                    let oldLength = originalGet.call(e.target).length;
                    
                    formatVisible(e.target, false);
                    
                    let newLength = originalGet.call(e.target).length;
                    try {
                        e.target.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
                    } catch(err) {} 
                }
            });

            document.addEventListener('submit', function(e) {
                if (e.target && e.target.tagName === 'FORM') {
                    // Prevent submission with dots
                    e.target.querySelectorAll('.format-currency').forEach(i => {
                        let raw = i.value; // Getter returns unformatted (e.g. 1000.5)
                        i.classList.remove('format-currency');
                        originalSet.call(i, raw);
                        // Add class back after minimal delay so UI continues to look right if submit fails locally
                        setTimeout(() => {
                            i.classList.add('format-currency');
                            formatVisible(i, true);
                        }, 100);
                    });
                }
            }, true);
        });
    </script>
</body>
</html>