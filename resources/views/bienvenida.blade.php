<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ImpulWeb</title>
  @vite(['resources/css/app.css', 'resources/css/auth.css'])
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">


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

  {{-- Botón Demo --}}
  <div class="reveal" style="margin-top: 1.2rem; text-align: center;">
    <form action="/login-demo" method="POST" style="display: inline;">
      @csrf
      <button type="submit" class="btn-demo" style="
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.8rem;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        font-family: 'DM Sans', sans-serif;
      " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.3)'">
        🚀 Probar demo
      </button>
    </form>
    <p style="margin-top: 0.6rem; font-size: 0.8rem; color: #94a3b8; font-family: 'DM Sans', sans-serif;">
      Explora el panel con datos de ejemplo · Sin registro necesario
    </p>
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