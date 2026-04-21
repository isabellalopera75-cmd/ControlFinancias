{{-- Interfaz de inicio de sesión con diseño dinámico e interactivo --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImpulWeb — Iniciar sesión</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; margin:0; padding:0; overflow: hidden; background-color: #f8fafc; }
        .serif { font-family: 'Fraunces', serif; }

        /* Mouse Follower Glow */
        #glow {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, rgba(16,185,129,0) 70%);
            border-radius: 50%;
            pointer-events: none;
            transform: translate(-50%, -50%);
            z-index: 1;
            /* Se actualiza via js */
        }

        /* Animated Grid Background */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(16,185,129,0.06) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(16,185,129,0.06) 1px, transparent 1px);
            z-index: 0;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 100%);
            -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0));
        }

        /* Glassmorphism Panel on Left */
        .glass-panel {
            background: rgba(241, 245, 249, 0.5); /* slate-100 */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.4);
            z-index: 10;
        }

        /* Floating elements */
        .float-fast { animation: float 4.5s ease-in-out infinite; }
        .float-slow { animation: float 7s ease-in-out infinite reverse; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(1deg); }
        }

        /* Input styling */
        input:focus { outline: none; border-color: #34d399 !important; background: white !important; box-shadow: 0 0 0 4px rgba(52,211,153,0.15); }
        .btn-submit { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(15,23,42,0.3); }

        /* Fade in */
        .fade-up { opacity: 0; transform: translateY(20px); animation: fadeUpAnim 0.7s cubic-bezier(0.22,1,0.36,1) forwards; }
        .d-1 { animation-delay: 0.15s; }
        .d-2 { animation-delay: 0.3s; }
        .d-3 { animation-delay: 0.45s; }
        @keyframes fadeUpAnim { to { opacity: 1; transform: translateY(0); } }

    </style>
</head>
<body class="flex min-h-screen">

    <div id="glow"></div>
    <div class="bg-grid"></div>

    <!-- Left Side: Visual / Info -->
    <div class="hidden lg:flex lg:w-[55%] glass-panel flex-col justify-between p-14 relative overflow-hidden">
        
        <!-- Header -->
        <div class="relative z-20 fade-up">
            <div class="flex items-center gap-2 mb-16">
                <div class="w-3 h-3 bg-[#10b981] rounded-full"></div>
                <span class="text-[11px] tracking-[0.2em] uppercase text-[#10b981] font-bold">ImpulWeb</span>
            </div>
            
            <h1 class="serif text-5xl xl:text-[4rem] text-[#0f172a] leading-[1.1] mb-6 max-w-xl">
                Toma el <em class="italic text-[#10b981]">control</em> <br>de tu negocio.
            </h1>
            <p class="text-[1.1rem] text-[#64748b] max-w-md font-light leading-relaxed">
                Un panel interactivo que proyecta, analiza y te ayuda a alcanzar tus metas operativas sin estrés financiero.
            </p>
        </div>

        <!-- Floating Card -->
        <div class="relative z-20 fade-up d-1 mt-auto">
            <div class="bg-white/80 backdrop-blur-xl rounded-[24px] p-7 border border-white/50 max-w-[360px] float-fast shadow-[0_20px_40px_-15px_rgba(15,23,42,0.1)]">
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <div class="text-[10px] uppercase tracking-[0.15em] text-[#64748b] font-medium mb-1.5">PROYECCIÓN DE CIERRE</div>
                        <div class="serif text-[26px] text-[#0f172a] font-bold leading-none">✓ Meta superada</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-[#d1fae5] flex items-center justify-center text-[#059669] shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>
                <div class="h-2 w-full bg-[#e2e8f0] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#10b981] to-[#34d399] w-[85%] rounded-full relative">
                        <div class="absolute right-0 top-0 bottom-0 w-4 bg-white/30 rounded-full blur-[2px]"></div>
                    </div>
                </div>
                <div class="mt-3 flex justify-between items-center text-xs">
                    <span class="text-[#94a3b8]">Progreso actual</span>
                    <span class="font-bold text-[#10b981]">85%</span>
                </div>
            </div>
        </div>

        <!-- Decorative circles -->
        <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-[#a7f3d0] rounded-full mix-blend-multiply filter blur-[60px] opacity-60 float-slow"></div>
        <div class="absolute top-10 right-10 w-72 h-72 bg-[#d1fae5] rounded-full mix-blend-multiply filter blur-[50px] opacity-80 float-fast" style="animation-delay: -2s"></div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-[45%] flex items-center justify-center p-8 z-10 relative bg-[#f1f5f9]/40 backdrop-blur-sm lg:bg-transparent lg:backdrop-blur-none">
        
        <div class="w-full max-w-[400px]">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center justify-center gap-2 mb-12 fade-up">
                <div class="w-3 h-3 bg-[#10b981] rounded-full"></div>
                <span class="text-xs tracking-[0.2em] uppercase text-[#10b981] font-bold">ImpulWeb</span>
            </div>

            <div class="fade-up d-1 mb-10 text-center lg:text-left">
                <span class="block text-[10px] tracking-[0.18em] uppercase text-[#94a3b8] mb-3">Acceso al panel</span>
                <h2 class="serif text-4xl text-[#0f172a] mb-3">Bienvenido</h2>
                <p class="text-[#64748b] text-sm md:text-base font-light">Ingresa tus credenciales para continuar.</p>
            </div>

            <form method="POST" action="/login" class="fade-up d-2">
                @csrf

                <div class="mb-5">
                    <label for="email" class="block text-[10px] tracking-[0.15em] uppercase text-[#64748b] font-medium mb-2 pl-1">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" id="email" placeholder="tu@correo.com" required autocomplete="email"
                           class="w-full px-5 py-3.5 bg-white border border-[#cbd5e1] rounded-xl text-[#0f172a] transition-all text-sm">
                </div>

                <div class="mb-6 relative group">
                    <label for="password" class="block text-[10px] tracking-[0.15em] uppercase text-[#64748b] font-medium mb-2 pl-1">
                        Contraseña
                    </label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password"
                           class="w-full px-5 py-3.5 bg-white border border-[#cbd5e1] rounded-xl text-[#0f172a] transition-all text-sm">
                    
                </div>

                @if(session('error'))
                <div class="mb-6 px-4 py-3 bg-[#fef2f2] border border-[#fecaca] rounded-xl text-[#991b1b] text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
                @endif

                <button type="submit" class="group btn-submit w-full py-4 bg-[#0f172a] text-white rounded-xl text-sm font-medium flex justify-center items-center gap-2 overflow-hidden relative border border-[#1e293b] hover:bg-[#1e293b]">
                    <span class="relative z-10 transition-transform group-hover:-translate-x-1">Entrar al dashboard</span>
                    <svg class="w-4 h-4 opacity-50 group-hover:opacity-100 transition-all relative z-10 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <p class="fade-up d-3 mt-8 text-center text-sm text-[#64748b]">
                ¿No tienes cuenta? <a href="/configuracion-inicial" class="text-[#10b981] font-medium hover:underline transition-colors">Crea tu negocio gratis</a>
            </p>
        </div>
    </div>

    <!-- Mouse Interaction Script -->
    <script>
        const glow = document.getElementById('glow');
        let mouseX = window.innerWidth / 2;
        let mouseY = window.innerHeight / 2;
        let glowX = mouseX;
        let glowY = mouseY;
        
        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        // Smooth follow with easing
        function animate() {
            glowX += (mouseX - glowX) * 0.08;
            glowY += (mouseY - glowY) * 0.08;
            
            glow.style.left = glowX + 'px';
            glow.style.top = glowY + 'px';
            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>