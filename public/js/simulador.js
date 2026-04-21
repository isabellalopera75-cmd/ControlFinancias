// ================= SIMULADOR =================

const margenSlider = document.getElementById('margen');
const gastosSlider = document.getElementById('gastos');
const sueldoSlider = document.getElementById('sueldo');

function calcularSimulador() {
    const margen = parseFloat(margenSlider.value);
    const gastos = parseFloat(gastosSlider.value);
    const sueldo = parseFloat(sueldoSlider.value);

    document.getElementById('valorMargen').textContent = margen + '%';
    document.getElementById('valorGastos').textContent = gastos.toLocaleString('es-CO');
    document.getElementById('valorSueldo').textContent = sueldo.toLocaleString('es-CO');

    let htmlResultado = '';

    if (typeof esServicios !== 'undefined' && esServicios) {
        // En servicios, ventas tienen 100% de margen bruto
        const utilidadNeta = ventasMes - gastos - sueldo;
        const colorUtilidad = utilidadNeta >= 0 ? '#4a7c59' : '#e07070';
        const textoUtilidad = utilidadNeta >= 0 ? 'Con tus ventas actuales' : 'Estás en pérdida';
        
        const pe = gastos + sueldo;
        
        // Venta necesaria para llegar a ese margen neto
        const ventaNecesaria = (gastos + sueldo) / (1 - (margen / 100));

        htmlResultado = `
        <div style="margin-bottom:8px">
            <span style="color:#9a9390; font-size:0.75rem">Punto de equilibrio</span><br>
            <span style="color:#2a2522; font-size:1.2rem; font-weight:500">$${pe.toLocaleString('es-CO', {maximumFractionDigits: 0})}</span>
        </div>
        <div>
            <span style="color:#9a9390; font-size:0.75rem">Venta meta (para margen del ${margen}%)</span><br>
            <span style="color:#2a2522; font-size:1.2rem; font-weight:500">$${ventaNecesaria.toLocaleString('es-CO', {maximumFractionDigits: 0})}</span>
        </div>
        `;
    } else {
        // Reventa
        const utilidadBruta = ventasMes * (margen / 100);
        const utilidadNeta = utilidadBruta - gastos - sueldo;
        const nuevoPE = (gastos + sueldo) / (margen / 100 > 0 ? margen / 100 : 0.01);

        const colorUtilidad = utilidadNeta >= 0 ? '#4a7c59' : '#e07070';
        const textoUtilidad = utilidadNeta >= 0 ? '↑ Estás generando ganancia' : '↓ Estás en pérdida';

        htmlResultado = `
        <div style="margin-bottom:8px">
            <span style="color:#9a9390; font-size:0.75rem">Punto de equilibrio proyectado</span><br>
            <span style="color:#2a2522; font-size:1.2rem; font-weight:500">$${nuevoPE.toLocaleString('es-CO', {maximumFractionDigits: 0})}</span>
        </div>
        <div>
            <span style="color:#9a9390; font-size:0.75rem">Utilidad proyectada</span><br>
            <span style="color:${colorUtilidad}; font-size:1.2rem; font-weight:500">$${utilidadNeta.toLocaleString('es-CO', {maximumFractionDigits: 0})}</span><br>
            <span style="color:${colorUtilidad}; font-size:0.75rem">${textoUtilidad}</span>
        </div>
        `;
    }

    document.getElementById('resultadoSimulador').innerHTML = htmlResultado;
}

margenSlider.addEventListener('input', calcularSimulador);
gastosSlider.addEventListener('input', calcularSimulador);
sueldoSlider.addEventListener('input', calcularSimulador);


// ================= MODAL EDITAR MOVIMIENTO =================

function abrirModal(id, descripcion, monto) {
    const modal = document.getElementById('modalEditar');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('editDescripcion').value = descripcion;
    document.getElementById('editMonto').value = monto;
    document.getElementById('formEditar').action = '/movimiento/' + id;
}

function cerrarModal() {
    const modal = document.getElementById('modalEditar');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('click', function (e) {
    const boton = e.target.closest('.btn-editar');
    if (boton) {
        abrirModal(boton.dataset.id, boton.dataset.descripcion, boton.dataset.monto);
    }
});


// ================= HISTORIAL =================

function abrirHistorial() {
    const modal = document.getElementById('modalHistorial');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarHistorial() {
    const modal = document.getElementById('modalHistorial');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}


// ================= FILTRO HISTORIAL =================

function filtrarMovimientos(tipo) {
    const filas = document.querySelectorAll('.fila-movimiento');
    filas.forEach(fila => {
        if (tipo === 'todos' || fila.dataset.tipo === tipo) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });

    ['todos', 'ventas', 'gastos'].forEach(t => {
        const btn = document.getElementById('btn-' + t);
        if (t === tipo) {
            btn.classList.remove('bg-[#f5f3ef]', 'text-[#9a9390]');
            btn.classList.add('bg-[#2d4a35]', 'text-white');
        } else {
            btn.classList.remove('bg-[#2d4a35]', 'text-white');
            btn.classList.add('bg-[#f5f3ef]', 'text-[#9a9390]');
        }
    });
}


// ================= FORMATO NÚMEROS =================

function formatearNumero(input, hiddenId) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
        document.getElementById(hiddenId).value = valor;
        if (valor) {
            this.value = parseInt(valor).toLocaleString('es-CO');
        }
    });
}

const elMontoVenta = document.getElementById('montoVenta');
const elMontoGasto = document.getElementById('montoGasto');
if (elMontoVenta) formatearNumero(elMontoVenta, 'montoVentaReal');
if (elMontoGasto) formatearNumero(elMontoGasto, 'montoGastoReal');


// ================= BANNER CIERRE DE MES =================

function cerrarBanner() {
    fetch('/banner/cerrar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(() => {
        document.getElementById('bannerCierreMes').style.display = 'none';
    });
}


// ================= RECOMENDACIONES =================

function abrirRecomendaciones() {
    const panel = document.getElementById('panelRecomendaciones');
    panel.classList.remove('hidden');
    panel.classList.add('flex');

    fetch('/recomendaciones/marcar-vistas', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    });
}

function cerrarRecomendaciones() {
    const panel = document.getElementById('panelRecomendaciones');
    panel.classList.add('hidden');
    panel.classList.remove('flex');
}

