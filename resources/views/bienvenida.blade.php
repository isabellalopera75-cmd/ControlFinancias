<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ImpulWeb</title>
  @vite(['resources/css/app.css'])
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #f8fafc; /* slate-50 */
      color: #0f172a; /* slate-900 */
      min-height: 100vh;
      overflow-x: hidden;
    }
    .display { font-family: 'Fraunces', serif; }

    /* Fondo sutil con patrón */
    .bg-pattern {
      position: fixed; inset: 0; pointer-events: none; z-index: 0;
      background-image:
        radial-gradient(circle at 20% 20%, rgba(16,185,129,0.06) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(16,185,129,0.04) 0%, transparent 50%);
    }

    /* ── HERO ── */
    .hero {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 60px 24px 80px;
      text-align: center;
    }

    .logo {
      display: flex; align-items: center; gap: 8px;
      position: absolute; top: 40px; left: 48px;
    }
    .logo-dot {
      width: 8px; height: 8px; background: #059669; /* emerald-600 */ border-radius: 50%;
      animation: pulse 2.5s ease-in-out infinite;
    }
    @keyframes pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(5,150,105,0.4); }
      50% { box-shadow: 0 0 0 6px rgba(5,150,105,0); }
    }
    .logo-text { font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: #059669; font-weight: 500; }

    .tag {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase;
      color: #059669; border: 1px solid rgba(5,150,105,0.25);
      background: rgba(5,150,105,0.06);
      padding: 5px 14px; border-radius: 100px; margin-bottom: 32px;
    }
    .tag-dot { width: 5px; height: 5px; background: #059669; border-radius: 50%; }

    .hero-title {
      font-family: 'Fraunces', serif;
      font-size: clamp(3.2rem, 7vw, 6.5rem);
      font-weight: 300; line-height: 1.0;
      color: #1e293b; /* slate-800 */ margin-bottom: 24px;
      letter-spacing: -0.02em;
    }
    .hero-title em { font-style: italic; color: #10b981; /* emerald-500 */ }

    .hero-sub {
      font-size: 17px; color: #64748b; /* slate-500 */ line-height: 1.7; font-weight: 300;
      max-width: 440px; margin: 0 auto 48px;
    }

    /* CTAs */
    .ctas { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 64px; }

    .btn-primary {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 16px 32px; background: #0f172a; /* slate-900 */ color: #f8fafc;
      border-radius: 14px; text-decoration: none; font-size: 14px; font-weight: 500;
      transition: all 0.2s; letter-spacing: 0.02em;
    }
    .btn-primary:hover { background: #1e293b; transform: translateY(-2px); box-shadow: 0 12px 32px rgba(15,23,42,0.25); }

    .btn-secondary {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 16px 32px; background: white; color: #1e293b;
      border: 1.5px solid #cbd5e1; /* slate-300 */ border-radius: 14px;
      text-decoration: none; font-size: 14px; font-weight: 400;
      transition: all 0.2s;
    }
    .btn-secondary:hover { border-color: #94a3b8; background: #f1f5f9; transform: translateY(-1px); }

    /* Stats horizontales */
    .stats {
      display: flex; gap: 0; justify-content: center;
      border: 1px solid #e2e8f0; border-radius: 20px;
      background: rgba(255,255,255,0.8); backdrop-filter: blur(8px); overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .stat {
      padding: 20px 36px; border-right: 1px solid #f1f5f9;
      text-align: center;
    }
    .stat:last-child { border-right: none; }
    .stat-num {
      font-family: 'Fraunces', serif; font-size: 32px; font-weight: 700;
      color: #1e293b; line-height: 1;
    }
    .stat-num em { font-style: normal; color: #10b981; }
    .stat-label { font-size: 11px; color: #64748b; margin-top: 6px; letter-spacing: 0.05em; }

    /* ── SEPARADOR ── */
    .section-sep {
      display: flex; align-items: center; gap: 20px;
      max-width: 600px; margin: 0 auto 64px; padding: 0 24px;
    }
    .sep-line { flex: 1; height: 1px; background: #e2e8f0; }
    .sep-text { font-size: 11px; color: #94a3b8; letter-spacing: 0.1em; text-transform: uppercase; white-space: nowrap; }

    /* ── CARDS SECTION ── */
    .cards-section {
      position: relative; z-index: 1;
      padding: 0 24px 100px;
    }

    .cards-title {
      font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.6rem);
      font-weight: 300; color: #0f172a; text-align: center;
      margin-bottom: 48px; letter-spacing: -0.01em;
    }
    .cards-title em { font-style: italic; color: #10b981; }

    .cards-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 20px; max-width: 1000px; margin: 0 auto 40px;
    }

    .feature-card {
      background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); border: 1px solid #e2e8f0;
      border-radius: 20px; padding: 32px 28px;
      transition: all 0.25s; position: relative; overflow: hidden;
    }
    .feature-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, #10b981, #34d399);
      transform: scaleX(0); transform-origin: left; transition: transform 0.3s;
    }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px rgba(0,0,0,0.07); border-color: #cbd5e1; }
    .feature-card:hover::before { transform: scaleX(1); }

    .card-icon {
      width: 44px; height: 44px; border-radius: 12px;
      background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; margin-bottom: 18px;
    }
    .card-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 10px; }
    .card-text { font-size: 13px; color: #64748b; line-height: 1.65; font-weight: 300; }

    /* Segunda fila de cards — 2 centradas */
    .cards-grid-2 {
      display: grid; grid-template-columns: repeat(2, 1fr);
      gap: 20px; max-width: 660px; margin: 0 auto;
    }
    .feature-card.accent {
      background: #0f172a; border-color: #1e293b;
    }
    .feature-card.accent .card-icon { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.2); }
    .feature-card.accent .card-title { color: #f8fafc; }
    .feature-card.accent .card-text { color: rgba(248,250,252,0.6); }
    .feature-card.accent::before { background: linear-gradient(90deg, #34d399, #6ee7b7); }

    /* Footer */
    .footer {
      text-align: center; padding: 32px;
      font-size: 11px; color: #94a3b8; letter-spacing: 0.08em;
      border-top: 1px solid #e2e8f0; position: relative; z-index: 1;
    }

    /* Animaciones de entrada */
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    @media (max-width: 768px) {
      .logo { left: 24px; }
      .cards-grid { grid-template-columns: 1fr; }
      .cards-grid-2 { grid-template-columns: 1fr; }
      .stat { padding: 16px 20px; }
      .stats { flex-wrap: wrap; }
    }
  </style>
</head>
<body>

<div class="bg-pattern"></div>

{{-- HERO --}}
<section class="hero">

  <div class="logo">
    <div class="logo-dot"></div>
    <span class="logo-text">ImpulWeb</span>
  </div>

  <div class="tag">
    <div class="tag-dot"></div>
    Gestión financiera inteligente
  </div>

  <h1 class="display hero-title reveal">
    Controla tu negocio<br>
    <em>sin estrés.</em>
  </h1>

  <p class="hero-sub reveal">
    Ventas, inventario, punto de equilibrio y facturación en un solo lugar.
    Diseñado para las tiendas y negocios que mueven los barrios de Florencia, Caquetá.
  </p>

  <div class="ctas reveal">
    <a href="/configuracion-inicial" class="btn-primary">
      Crear mi negocio <span>→</span>
    </a>
    <a href="/login" class="btn-secondary">
      Iniciar sesión
    </a>
  </div>

  <div class="stats reveal">
    <div class="stat">
      <div class="stat-num">+500<em>.</em></div>
      <div class="stat-label">Movimientos registrados</div>
    </div>
    <div class="stat">
      <div class="stat-num">+50<em>.</em></div>
      <div class="stat-label">Negocios activos</div>
    </div>
    <div class="stat">
      <div class="stat-num">24<em>/7</em></div>
      <div class="stat-label">Control financiero</div>
    </div>
  </div>

</section>

{{-- CARDS --}}
<section class="cards-section">

  <div class="section-sep reveal">
    <div class="sep-line"></div>
    <span class="sep-text">¿Qué hace ImpulWeb?</span>
    <div class="sep-line"></div>
  </div>

  <h2 class="display cards-title reveal">
    Todo lo que tu negocio<br><em>necesita en un solo lugar.</em>
  </h2>

  <div class="cards-grid">
    <div class="feature-card reveal">
      <div class="card-icon">📊</div>
      <div class="card-title">Control financiero en tiempo real</div>
      <div class="card-text">Punto de equilibrio, utilidad del mes, días de supervivencia y proyección de cierre calculados automáticamente cada día.</div>
    </div>
    <div class="feature-card reveal">
      <div class="card-icon">📦</div>
      <div class="card-title">Inventario inteligente</div>
      <div class="card-text">Kardex automático, alertas de stock bajo, entradas de mercancía y ajustes de inventario. Importa desde Excel en segundos.</div>
    </div>
    <div class="feature-card reveal">
      <div class="card-icon">🧾</div>
      <div class="card-title">Facturación integrada</div>
      <div class="card-text">Genera facturas simuladas con PDF, envíalas por correo al comprador y consulta el historial de ventas con informes detallados.</div>
    </div>
  </div>

  <div class="cards-grid-2" style="margin-top: 20px;">
    <div class="feature-card accent reveal">
      <div class="card-icon">⚡</div>
      <div class="card-title">Simulador What-If</div>
      <div class="card-text">¿Qué pasa si subo el margen? ¿Si bajo los gastos? Simula escenarios financieros sin afectar tus datos reales.</div>
    </div>
    <div class="feature-card accent reveal">
      <div class="card-icon">💡</div>
      <div class="card-title">Recomendaciones automáticas</div>
      <div class="card-text">El sistema analiza tu negocio y te da sugerencias concretas para mejorar la rentabilidad mes a mes.</div>
    </div>
  </div>

</section>

<footer class="footer">© 2026 ImpulWeb · Todos los derechos reservados</footer>

<script>
  const reveals = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 80);
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  reveals.forEach(el => observer.observe(el));
</script>

</body>
</html>