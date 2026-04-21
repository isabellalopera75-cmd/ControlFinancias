{{-- Interfaz del formulario paso a paso donde el usuario configura las métricas base de su negocio por primera vez --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - ImpulWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body, * { font-family: 'DM Sans', sans-serif; }
        .serif { font-family: 'Playfair Display', serif; }
        
        /* Modern Glass Theme */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: #a8c8a0;
            box-shadow: 0 0 0 3px rgba(168, 200, 160, 0.3);
        }

        .form-section { display: none; }
        .form-section.active { display: block; animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .tipo-card { cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid rgba(255,255,255,0.6); }
        .tipo-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); border-color: #a8c8a0; }
        .tipo-card.selected { 
            border-color: #2d4a35; 
            background: linear-gradient(to bottom right, rgba(240,247,242,0.9), rgba(255,255,255,0.8));
            box-shadow: 0 4px 12px rgba(45, 74, 53, 0.08);
        }
        .tipo-card.selected .tipo-icon { background-color: #2d4a35; color: white; }
        .tipo-card.selected .tipo-title { color: #2d4a35; font-weight: 600; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4 py-12 relative overflow-x-hidden">
    {{-- Decorative Background Blobs --}}
    <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-emerald-200/30 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-blue-100/40 rounded-full blur-[120px] translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

    <div class="w-full max-w-2xl">

        {{-- Logo --}}
        <div class="flex items-center gap-2 justify-center mb-8">
            <span class="w-2.5 h-2.5 bg-[#4a7c59] rounded-full"></span>
            <span class="text-[#4a7c59] text-xs font-medium tracking-widest uppercase">ImpulWeb</span>
        </div>

        {{-- Título --}}
        <div class="text-center mb-8">
            <p class="text-[#b0a8a0] text-xs tracking-widest uppercase mb-2">Bienvenido</p>
            <h1 class="serif text-[#2d4a35] text-4xl">Configura tu <span class="text-[#4a7c59] italic">negocio</span></h1>
            <p class="text-[#8a8280] text-sm mt-2 font-light">Completa los pasos para comenzar a usar ImpulWeb</p>
        </div>

        {{-- Step indicators --}}
        <div class="flex items-center justify-center gap-0 mb-8" id="stepIndicator">
            @php
                $steps = ['Cuenta', 'Negocio', 'Producto', 'Ventas', 'Gastos', 'Finanzas', 'Metas'];
            @endphp
            @foreach($steps as $i => $label)
                <div class="flex items-center" data-step-label="{{ $i + 1 }}">
                    <div class="flex flex-col items-center">
                        <div id="circle-{{ $i + 1 }}"
                             class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-semibold transition-all duration-300
                             {{ $i === 0 ? 'bg-[#2d4a35] border-[#2d4a35] text-[#f0ede8]' : 'bg-white border-[#e0dbd4] text-[#b0a8a0]' }}">
                            <span id="circle-content-{{ $i + 1 }}">{{ $i + 1 }}</span>
                        </div>
                        <span id="label-{{ $i + 1 }}"
                              class="text-[0.6rem] mt-1 tracking-wide transition-colors duration-300
                              {{ $i === 0 ? 'text-[#2d4a35] font-medium' : 'text-[#b0a8a0]' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div id="line-{{ $i + 1 }}" class="w-8 h-px mb-4 transition-all duration-300 {{ $i === 0 ? 'bg-[#4a7c59]' : 'bg-[#e0dbd4]' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Card --}}
        <div class="bg-white border border-[#e8e4e0] rounded-2xl shadow-sm p-8">

            <form action="/configuracion-inicial" method="POST" id="setupForm">
                @csrf

                {{-- Campo oculto tipo_negocio --}}
                <input type="hidden" name="tipo_negocio" id="tipoNegocioInput" value="{{ old('tipo_negocio', 'servicios') }}">

                {{-- Advertencia financiera --}}
                @if(session('advertencia_financiera'))
                <div id="alertaFinanciera" style="background:#fff3cd; border:1px solid #ffc107; border-radius:12px; padding:20px; margin-bottom:20px;">
                    <h3 style="color:#856404; margin-bottom:10px;">⚠️ Tus números no son sostenibles</h3>
                    <p style="color:#856404; margin-bottom:15px;">
                        Tu punto de equilibrio es <strong>COP {{ number_format(session('pe_calculado'), 0, ',', '.') }}</strong>
                        pero tu promedio histórico de ventas es <strong>COP {{ number_format(session('promedio_ventas'), 0, ',', '.') }}</strong>.
                        Necesitarías vender <strong>{{ number_format(session('pe_calculado') / max(session('promedio_ventas'), 1), 1) }}x</strong>
                        más de lo que has vendido históricamente para ser rentable.
                    </p>
                    <h4 style="color:#856404; margin-bottom:10px;">Valores recomendados por el sistema:</h4>
                    <ul style="color:#856404; margin-bottom:15px;">
                        <li>Tope de gastos fijos recomendado: <strong>COP {{ number_format(session('gastos_recomendados'), 0, ',', '.') }}</strong>
                            {{ session('gastos_recomendados') < session('gastos_actuales') ? '(reducción recomendada)' : '(sin cambios)' }}
                        </li>
                        <li>Sueldo recomendado: <strong>COP {{ number_format(session('sueldo_recomendado'), 0, ',', '.') }}</strong></li>
                        <li>Reinversión recomendada: <strong>COP {{ number_format(session('reinversion_recomendada'), 0, ',', '.') }}</strong></li>
                        <li>Ventas proyectadas recomendadas: <strong>COP {{ number_format(session('proyeccion_recomendada'), 0, ',', '.') }}</strong></li>
                    </ul>
                    <div style="display:flex; gap:10px;">
                        <button type="button" onclick="usarValoresRecomendados()"
                            style="background:#2d4a35; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
                            ✅ Usar valores recomendados
                        </button>
                        <button type="button" onclick="ignorarAdvertencia()"
                            style="background:#8a3a3a; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
                            ⚠️ Continuar con mis datos
                        </button>
                    </div>
                </div>
                <input type="hidden" name="ignorar_advertencia" id="ignorarAdvertenciaInput" value="">
                @endif

                {{-- Errores --}}
                @if($errors->any())
                <div id="alertaErrores" class="flex items-start gap-3 bg-[#fdecea] text-[#7a2d2d] border border-[#f0c0bc] px-4 py-3 rounded-xl mb-6 text-sm">
                    <i class="bi bi-exclamation-circle-fill text-red-400 mt-0.5"></i>
                    <ul class="space-y-0.5">
                        @foreach($errors->unique() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- =====================================================
                     PASO 1: Cuenta
                ===================================================== --}}
                <div class="form-section active" id="step-1">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Tu cuenta</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Nombre completo</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Juan Pérez" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Correo electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="tu@impulweb.co" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                            <p id="emailError" class="hidden text-red-400 text-xs mt-1.5 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> Este correo ya está registrado, ingresa otro.
                            </p>
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Contraseña</label>
                            <input type="password" name="password" placeholder="••••••••" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 2: Negocio + TIPO DE NEGOCIO (NUEVO)
                ===================================================== --}}
                <div class="form-section" id="step-2">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Tu negocio</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Nombre del negocio</label>
                            <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" placeholder="Mi Negocio S.A.S" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">País</label>
                                <input type="text" name="pais" value="{{ old('pais') }}" placeholder="Colombia" required
                                    class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Moneda</label>
                                <input type="text" name="moneda" value="{{ old('moneda', 'COP') }}" placeholder="COP" required
                                    class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                            </div>
                        </div>
                        {{-- Dirección y Teléfono --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Dirección</label>
                                <input type="text" name="direccion" value="{{ old('direccion') }}"
                                    placeholder="Calle 10 # 5-20, Bogotá"
                                    class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}"
                                    placeholder="+57 300 123 4567"
                                    class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                            </div>
                        </div>
                        {{-- Tipo de negocio (NUEVO) --}}
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-3">¿Qué tipo de negocio tienes?</label>
                            <div class="grid grid-cols-2 gap-3">

                                {{-- Servicios --}}
                                <div class="tipo-card selected border-2 border-[#2d4a35] bg-[#f0f7f2] rounded-xl p-4 text-center"
                                     onclick="seleccionarTipo('servicios')">
                                    <div class="tipo-icon w-10 h-10 bg-[#2d4a35] rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="bi bi-person-gear text-white text-lg"></i>
                                    </div>
                                    <p class="tipo-title text-[#2d4a35] text-xs font-semibold tracking-wide">Servicios</p>
                                    <p class="text-[#9a9390] text-[0.65rem] mt-1 leading-tight">Consultoría, diseño, asesoría, etc.</p>
                                </div>

                                {{-- Reventa --}}
                                <div class="tipo-card border-2 border-[#e8e4e0] rounded-xl p-4 text-center"
                                     onclick="seleccionarTipo('reventa')">
                                    <div class="tipo-icon w-10 h-10 bg-[#f0ede8] rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="bi bi-shop text-[#8a8280] text-lg"></i>
                                    </div>
                                    <p class="tipo-title text-[#5a5250] text-xs font-semibold tracking-wide">Reventa</p>
                                    <p class="text-[#9a9390] text-[0.65rem] mt-1 leading-tight">Compras y vendes productos ya hechos.</p>
                                </div>

                            </div>
                            <p id="tipoNegocioError" class="hidden text-red-400 text-xs mt-2 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> Por favor selecciona un tipo de negocio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 3: Producto — cambia según tipo_negocio
                ===================================================== --}}
                <div class="form-section" id="step-3">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Tu producto</h3>
                    </div>

                    {{-- Solo para servicios --}}
                    <div id="step3_servicios">
                        <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Margen de ganancia (%)</label>
                        <input type="number" name="margen_operacional" id="margenInput" value="{{ old('margen_operacional', 100) }}" placeholder="100"
                            class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        <p class="text-[#b0a8a0] text-xs mt-2 font-light">
                            En un negocio de servicios normalmente es del <strong>100%</strong> a menos que incurras en costos directos (materiales o insumos específicos) en la prestación del mismo.
                        </p>
                    </div>

                    {{-- Para reventa --}}
                    <div id="step3_reventa" class="hidden">
                        <div class="bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl p-5">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-[#2d4a35] rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="bi bi-lightbulb text-white"></i>
                                </div>
                                <div>
                                    <p class="text-[#2d4a35] text-sm font-semibold mb-1">El margen se calcula automáticamente</p>
                                    <p class="text-[#5a7a60] text-xs leading-relaxed">
                                        Para negocios de <strong>reventa</strong>, ImpulWeb calculará tu margen real
                                        basándose en el costo de compra y precio de venta de cada producto que registres en el inventario.
                                    </p>
                                    <p class="text-[#5a7a60] text-xs leading-relaxed mt-2">
                                        En el siguiente paso podrás agregar tus primeros productos. Por ahora continuemos con el historial de ventas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Para producción --}}
                    <div id="step3_produccion" class="hidden">
                        <div class="bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl p-5">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 bg-[#2d4a35] rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="bi bi-lightbulb text-white"></i>
                                </div>
                                <div>
                                    <p class="text-[#2d4a35] text-sm font-semibold mb-1">El margen se calcula desde tus recetas</p>
                                    <p class="text-[#5a7a60] text-xs leading-relaxed">
                                        Para negocios de <strong>producción</strong>, ImpulWeb calculará tu margen real
                                        a partir del costo de los ingredientes o materias primas de cada receta y el precio de venta del producto terminado.
                                    </p>
                                    <p class="text-[#5a7a60] text-xs leading-relaxed mt-2">
                                        En el siguiente paso podrás agregar tus primeras materias primas. Por ahora continuemos con el historial de ventas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 4: Ventas
                ===================================================== --}}
                <div class="form-section" id="step-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Historial de ventas</h3>
                    </div>
                    <p class="text-[#b0a8a0] text-xs mb-6 font-light ml-4">Ingresa las ventas totales de los últimos 3 meses para calcular tu punto de equilibrio inicial.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Ventas del mes anterior</label>
                            <input type="number" name="ventas_mes1" value="{{ old('ventas_mes1') }}" placeholder="0" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Ventas de hace 2 meses</label>
                            <input type="number" name="ventas_mes2" value="{{ old('ventas_mes2') }}" placeholder="0" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Ventas de hace 3 meses</label>
                            <input type="number" name="ventas_mes3" value="{{ old('ventas_mes3') }}" placeholder="0" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 5: Gastos
                ===================================================== --}}
                <div class="form-section" id="step-5">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Gastos de operación</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Días de operación / mes</label>
                            <input type="number" name="dias_operacion" value="{{ old('dias_operacion') }}" placeholder="22" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Servicios al mes</label>
                            <input type="number" name="servicios_mes" value="{{ old('servicios_mes') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Renta del local</label>
                            <input type="number" name="renta_local" value="{{ old('renta_local') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div id="step5_otros_gastos">
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Otros gastos fijos</label>
                            <input type="number" name="otros_gastos_fijos" value="{{ old('otros_gastos_fijos') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div id="step5_presupuesto_compras" class="hidden">
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Gastos var. (Compras al mes) - Guía</label>
                            <input type="number" name="presupuesto_compras_mensual" value="{{ old('presupuesto_compras_mensual') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Nómina mensual (empleados)</label>
                            <input type="number" name="nomina_empleados" value="{{ old('nomina_empleados', 0) }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 6: Finanzas
                ===================================================== --}}
                <div class="form-section" id="step-6">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Tu situación financiera</h3>
                    </div>
                    <div>
                        <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Dinero disponible en caja / banco</label>
                        <input type="number" name="dinero_disponible" value="{{ old('dinero_disponible') }}" placeholder="0" required
                            class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        <p class="text-[#b0a8a0] text-xs mt-2 font-light">Dinero disponible en caja (no funciona como ahorro, sino como dinero actual de operación).</p>
                    </div>
                </div>

                {{-- =====================================================
                     PASO 7: Metas
                ===================================================== --}}
                <div class="form-section" id="step-7">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
                        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Define tus metas</h3>
                    </div>
                    <div class="space-y-4">

                        {{-- Indicador PE --}}
                        <div id="indicadorPE" class="bg-[#f5f3ef] border border-[#e8e4e0] rounded-xl px-4 py-3 mb-4 hidden">
                            <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Punto de equilibrio estimado</p>
                            <p id="valorPE" class="text-[#2a2522] text-xl font-medium">Completa los pasos anteriores</p>
                            <p id="mensajePE" class="text-xs mt-1 text-[#9a9390]"></p>
                        </div>

                        {{-- Ingresos proyectados (solo servicios) --}}
                        <div id="ingresosProyectadosField">
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Ingresos proyectados mensuales</label>
                            <input type="number" name="ingresos_proyectados" id="ingresosProyectadosInput" value="{{ old('ingresos_proyectados') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>

                        {{-- Mensaje para reventa/producción --}}
                        <div id="ingresosProyectadosMsg" class="hidden bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl px-4 py-3">
                            <p class="text-[#2d4a35] text-xs">
                                <i class="bi bi-info-circle mr-1"></i>
                                Los ingresos proyectados se calcularán automáticamente desde tu historial de ventas una vez configures tu inventario.
                            </p>
                        </div>

                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Sueldo deseado como propietario</label>
                            <input type="number" name="sueldo_dueno" value="{{ old('sueldo_dueno') }}" placeholder="0" required
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Utilidad para ahorro o reinversión</label>
                            <input type="number" name="utilidad_ahorro_reinversion" value="{{ old('utilidad_ahorro_reinversion') }}" placeholder="0"
                                class="glass-input w-full px-4 py-2.5 rounded-xl text-slate-800 text-sm focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Navegación --}}
                <div class="flex gap-3 mt-8 pt-6 border-t border-[#f0ede8]">
                    <button type="button" id="btnPrev"
                        class="hidden bg-white/60 text-slate-600 border border-slate-200 text-sm font-medium px-6 py-2.5 rounded-xl hover:bg-white hover:shadow-sm transition-all duration-300 focus:outline-none backdrop-blur-sm">
                        <i class="bi bi-arrow-left mr-1.5"></i> Anterior
                    </button>
                    <button type="button" id="btnNext"
                        class="flex-1 bg-gradient-to-r from-[#2d4a35] to-[#3d5e45] text-white text-sm font-medium px-8 py-3 rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        Siguiente <i class="bi bi-arrow-right ml-1.5"></i>
                    </button>
                    <button type="submit" id="btnSubmit"
                        class="hidden flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-medium px-8 py-3 rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        Finalizar e Ingresar <i class="bi bi-check2-circle ml-1.5"></i>
                    </button>
                </div>

            </form>
        </div>

        <p class="text-center text-[#b0a8a0] text-xs mt-6 font-light">
            Paso <span id="currentStepLabel">1</span> de 7
        </p>
    </div>

<script>
    const totalSteps = 7;
    let current = 1;
    let tipoNegocio = '{{ old("tipo_negocio", "servicios") }}';

    // =====================================================
    // SELECCIÓN TIPO DE NEGOCIO
    // =====================================================
    function seleccionarTipo(tipo) {
        tipoNegocio = tipo;
        document.getElementById('tipoNegocioInput').value = tipo;

        // Actualizar tarjetas
        document.querySelectorAll('.tipo-card').forEach(card => {
            card.classList.remove('selected', 'border-[#2d4a35]', 'bg-[#f0f7f2]');
            card.classList.add('border-[#e8e4e0]');
            card.querySelector('.tipo-icon').classList.remove('bg-[#2d4a35]', 'text-white');
            card.querySelector('.tipo-icon').classList.add('bg-[#f0ede8]', 'text-[#8a8280]');
            card.querySelector('.tipo-title').classList.remove('text-[#2d4a35]');
            card.querySelector('.tipo-title').classList.add('text-[#5a5250]');
        });

        const cards = { servicios: 0, reventa: 1, produccion: 2 };
        const selectedCard = document.querySelectorAll('.tipo-card')[cards[tipo]];
        selectedCard.classList.add('selected', 'border-[#2d4a35]', 'bg-[#f0f7f2]');
        selectedCard.classList.remove('border-[#e8e4e0]');
        selectedCard.querySelector('.tipo-icon').classList.add('bg-[#2d4a35]', 'text-white');
        selectedCard.querySelector('.tipo-icon').classList.remove('bg-[#f0ede8]', 'text-[#8a8280]');
        selectedCard.querySelector('.tipo-title').classList.add('text-[#2d4a35]');
        selectedCard.querySelector('.tipo-title').classList.remove('text-[#5a5250]');

        actualizarVistaPorTipo();
    }

    function actualizarVistaPorTipo() {
        const esServicios = tipoNegocio === 'servicios';

        // Paso 3: mostrar sección correcta
        document.getElementById('step3_servicios').classList.toggle('hidden', !esServicios);
        document.getElementById('step3_reventa').classList.toggle('hidden', tipoNegocio !== 'reventa');
        document.getElementById('step3_produccion').classList.toggle('hidden', tipoNegocio !== 'produccion');

        // Campo margen: requerido solo para servicios
        const margenInput = document.getElementById('margenInput');
        if (margenInput) {
            if (esServicios) {
                margenInput.setAttribute('required', 'required');
            } else {
                margenInput.removeAttribute('required');
                margenInput.value = '0';
            }
        }

        // Paso 5: Ocultar otros gastos fijos y mostrar presupuesto compras para reventa
        const divOtrosGastos = document.getElementById('step5_otros_gastos');
        const divPresupuesto = document.getElementById('step5_presupuesto_compras');
        if (divOtrosGastos && divPresupuesto) {
            if (tipoNegocio === 'reventa') {
                divOtrosGastos.classList.add('hidden');
                divPresupuesto.classList.remove('hidden');
            } else {
                divOtrosGastos.classList.remove('hidden');
                divPresupuesto.classList.add('hidden');
            }
        }

        // Paso 7: ingresos proyectados solo para servicios
        document.getElementById('ingresosProyectadosField').classList.toggle('hidden', !esServicios);
        document.getElementById('ingresosProyectadosMsg').classList.toggle('hidden', esServicios);

        // Indicador PE: texto diferente para inventario
        const mensajePE = document.getElementById('mensajePE');
        if (!esServicios && mensajePE) {
            mensajePE.textContent = 'El PE se calculará automáticamente desde tu inventario una vez registres ventas.';
        }
    }

    // =====================================================
    // NAVEGACIÓN ENTRE PASOS
    // =====================================================
    function goTo(step) {
        const alertaErrores = document.getElementById('alertaErrores');
        if (alertaErrores) alertaErrores.style.display = 'none';

        document.getElementById(`step-${current}`).classList.remove('active');

        const prevCircle = document.getElementById(`circle-${current}`);
        prevCircle.classList.remove('bg-[#2d4a35]', 'border-[#2d4a35]', 'text-[#f0ede8]');
        prevCircle.classList.add('bg-[#4a7c59]', 'border-[#4a7c59]', 'text-white');
        document.getElementById(`circle-content-${current}`).innerHTML = '<i class="bi bi-check text-xs"></i>';
        document.getElementById(`label-${current}`).classList.remove('text-[#2d4a35]', 'font-medium');
        document.getElementById(`label-${current}`).classList.add('text-[#4a7c59]');

        if (current < totalSteps) {
            document.getElementById(`line-${current}`).classList.remove('bg-[#e0dbd4]');
            document.getElementById(`line-${current}`).classList.add('bg-[#4a7c59]');
        }

        current = step;

        document.getElementById(`step-${current}`).classList.add('active');

        const newCircle = document.getElementById(`circle-${current}`);
        newCircle.classList.remove('bg-white', 'border-[#e0dbd4]', 'text-[#b0a8a0]', 'bg-[#4a7c59]', 'border-[#4a7c59]', 'text-white');
        newCircle.classList.add('bg-[#2d4a35]', 'border-[#2d4a35]', 'text-[#f0ede8]');
        document.getElementById(`circle-content-${current}`).innerHTML = current;
        document.getElementById(`label-${current}`).classList.remove('text-[#b0a8a0]', 'text-[#4a7c59]');
        document.getElementById(`label-${current}`).classList.add('text-[#2d4a35]', 'font-medium');

        document.getElementById('btnPrev').classList.toggle('hidden', current === 1);
        document.getElementById('btnNext').classList.toggle('hidden', current === totalSteps);
        document.getElementById('btnSubmit').classList.toggle('hidden', current !== totalSteps);
        document.getElementById('currentStepLabel').textContent = current;

        // Actualizar vista al llegar a pasos dependientes del tipo
        if (current === 3 || current === 7) {
            actualizarVistaPorTipo();
        }
    }

    document.getElementById('btnNext').addEventListener('click', () => {
        if (current < totalSteps) {
            // Validación especial paso 2: tipo de negocio
            if (current === 2 && !tipoNegocio) {
                document.getElementById('tipoNegocioError').classList.remove('hidden');
                return;
            }
            document.getElementById('tipoNegocioError')?.classList.add('hidden');

            // Validación especial paso 3: margen solo si es servicios
            if (current === 3 && tipoNegocio === 'servicios') {
                const margenInput = document.getElementById('margenInput');
                if (!margenInput.value || parseFloat(margenInput.value) <= 0) {
                    margenInput.value = '100'; // Default safety to 100 if empty
                }
            }

            // Validación general campos required visibles
            const inputs = document.querySelectorAll(`#step-${current} input[required]`);
            let valido = true;

            inputs.forEach(input => {
                if (input.offsetParent === null) return;
                if (input.closest('.hidden')) return;
                if (!input.value.trim()) {
                    input.style.borderColor = '#e07070';
                    valido = false;
                } else {
                    input.style.borderColor = '';
                }
            });

            if (!valido) {
                let msg = document.getElementById('mensajeValidacion');
                if (!msg) {
                    msg = document.createElement('div');
                    msg.id = 'mensajeValidacion';
                    msg.style.cssText = 'background:#fdecea;border:1px solid #f0c0bc;border-radius:12px;padding:12px 16px;margin-bottom:16px;color:#7a2d2d;font-size:0.8rem;display:flex;align-items:center;gap:8px;';
                    msg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Por favor completa todos los campos requeridos.';
                    const stepActual = document.getElementById(`step-${current}`);
                    stepActual.insertBefore(msg, stepActual.firstChild);
                    stepActual.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }

            const msgExistente = document.getElementById('mensajeValidacion');
            if (msgExistente) msgExistente.remove();
            goTo(current + 1);
        }
    });

    document.getElementById('btnPrev').addEventListener('click', () => {
        if (current > 1) goTo(current - 1);
    });

    // =====================================================
    // VERIFICAR EMAIL
    // =====================================================
    document.getElementById('email').addEventListener('blur', function () {
        const email = this.value;
        if (!email) return;
        fetch('/verificar-email?email=' + encodeURIComponent(email))
            .then(res => res.json())
            .then(data => {
                const error = document.getElementById('emailError');
                if (data.existe) {
                    error.classList.remove('hidden');
                    this.classList.add('border-red-400');
                } else {
                    error.classList.add('hidden');
                    this.classList.remove('border-red-400');
                }
            });
    });

    // =====================================================
    // ADVERTENCIA FINANCIERA
    // =====================================================
    function usarValoresRecomendados() {
        const sueldo      = parseFloat('{{ session("sueldo_recomendado", 0) }}') || 0;
        const reinversion = parseFloat('{{ session("reinversion_recomendada", 0) }}') || 0;
        const proyeccion  = parseFloat('{{ session("proyeccion_recomendada", 0) }}') || 0;
        document.querySelector('[name="sueldo_dueno"]').value               = sueldo;
        document.querySelector('[name="utilidad_ahorro_reinversion"]').value = reinversion;
        document.querySelector('[name="ingresos_proyectados"]').value        = proyeccion;
        document.getElementById('alertaFinanciera').style.display            = 'none';
        document.getElementById('ignorarAdvertenciaInput').value             = '1';
    }

    function ignorarAdvertencia() {
        document.getElementById('alertaFinanciera').style.display = 'none';
        document.getElementById('ignorarAdvertenciaInput').value = '1';
    }

    // =====================================================
    // CÁLCULO PE EN TIEMPO REAL (solo servicios)
    // =====================================================
    function calcularPEEnTiempoReal() {
        if (tipoNegocio !== 'servicios') return;

        const margen    = parseFloat(document.querySelector('[name="margen_operacional"]')?.value) || 0;
        const servicios = parseFloat(document.querySelector('[name="servicios_mes"]')?.value) || 0;
        const renta     = parseFloat(document.querySelector('[name="renta_local"]')?.value) || 0;
        const otros     = parseFloat(document.querySelector('[name="otros_gastos_fijos"]')?.value) || 0;
        const nomina    = parseFloat(document.querySelector('[name="nomina_empleados"]')?.value) || 0;
        const sueldo    = parseFloat(document.querySelector('[name="sueldo_dueno"]')?.value) || 0;

        if (margen <= 0) return;

        const margenContribucion = margen / (100 + margen);
        const gastosFijos        = servicios + renta + otros + nomina;
        const pe                 = (gastosFijos + sueldo) / margenContribucion;

        const valorPE   = document.getElementById('valorPE');
        const mensajePE = document.getElementById('mensajePE');

        if (valorPE) {
            valorPE.textContent = 'COP ' + pe.toLocaleString('es-CO', { maximumFractionDigits: 0 });
        }

        const ingresos = parseFloat(document.querySelector('[name="ingresos_proyectados"]')?.value) || 0;
        if (ingresos > 0 && mensajePE) {
            if (ingresos >= pe) {
                mensajePE.textContent = 'Tus ingresos proyectados cubren el punto de equilibrio ✓';
                mensajePE.style.color = '#4a7c59';
            } else {
                mensajePE.textContent = 'Tus ingresos proyectados no cubren el punto de equilibrio';
                mensajePE.style.color = '#e07070';
            }
        }
    }

    ['margen_operacional', 'servicios_mes', 'renta_local', 'otros_gastos_fijos',
     'nomina_empleados', 'sueldo_dueno', 'ingresos_proyectados'].forEach(nombre => {
        const input = document.querySelector(`[name="${nombre}"]`);
        if (input) input.addEventListener('input', calcularPEEnTiempoReal);
    });

    // Init: aplicar estado inicial
    seleccionarTipo(tipoNegocio);
</script>

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
                e.target.querySelectorAll('.format-currency').forEach(i => {
                    let raw = i.value; 
                    i.classList.remove('format-currency');
                    originalSet.call(i, raw);
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