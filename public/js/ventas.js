                const productosDisponibles = window.productosDisponibles || [];
                let contadorLinea = 0;

                function agregarLineaVenta() {
                    contadorLinea++;
                    const div = document.createElement('div');
                    div.className = 'flex gap-2 items-center linea-venta';
                    div.innerHTML = `
                        <select name="items[${contadorLinea}][item_id]"
                            onchange="onProductoChange(this)"
                            class="flex-1 px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
                            <option value="">-- Producto --</option>
                            ${productosDisponibles.map(p => `
                                <option value="${p.id}"
                                    data-precio="${p.precio_venta}"
                                    data-costo="${p.costo_real}"
                                    data-nombre="${p.nombre}">
                                    ${p.nombre} — $${Number(p.precio_venta).toLocaleString('es-CO')}
                                </option>`).join('')}
                        </select>
                        <input type="number" name="items[${contadorLinea}][cantidad]"
                            step="any" min="1" value="1" placeholder="Cant."
                            oninput="calcularTotalVenta()"
                            class="w-20 px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
                        <input type="hidden" name="items[${contadorLinea}][precio_unitario]" class="precio-hidden">
                        <span class="subtotal-label text-[#9a9390] text-xs w-16 text-right"></span>
                        <button type="button" onclick="this.closest('.linea-venta').remove(); calcularTotalVenta()"
                            class="w-7 h-7 flex items-center justify-center bg-[#f2d8d8] text-[#8a3a3a] rounded-lg hover:bg-[#e0b0b0] transition flex-shrink-0">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    const contenedor = document.getElementById('lineasVenta');
                    if (!contenedor) return;
                    contenedor.appendChild(div);
                }

                function onProductoChange(select) {
                    const opt    = select.options[select.selectedIndex];
                    const precio = parseFloat(opt?.dataset.precio) || 0;
                    const row    = select.closest('.linea-venta');
                    row.querySelector('.precio-hidden').value = precio;
                    calcularTotalVenta();
                }

                function calcularTotalVenta() {
                    let total = 0, pesoTotal = 0, margenPonderado = 0;

                    document.querySelectorAll('.linea-venta').forEach(row => {
                        const sel      = row.querySelector('select');
                        const opt      = sel.options[sel.selectedIndex];
                        const precio   = parseFloat(opt?.dataset.precio) || 0;
                        const costo    = parseFloat(opt?.dataset.costo) || 0;
                        const cantidad = parseFloat(row.querySelector('input[type="number"]')?.value) || 0;
                        const subtotal = precio * cantidad;
                        row.querySelector('.subtotal-label').textContent =
                            subtotal > 0 ? '$' + subtotal.toLocaleString('es-CO', {maximumFractionDigits: 0}) : '';
                        total += subtotal;
                        if (precio > 0) {
                            margenPonderado += ((precio - costo) / precio) * subtotal;
                            pesoTotal += subtotal;
                        }
                    });

                    const preview = document.getElementById('previewVenta');
                    preview.classList.toggle('hidden', total === 0);
                    document.getElementById('totalVenta').textContent =
                        '$' + total.toLocaleString('es-CO', {maximumFractionDigits: 0});

                    if (pesoTotal > 0) {
                        const m = ((margenPonderado / pesoTotal) * 100).toFixed(1);
                        const el = document.getElementById('margenVenta');
                        el.textContent = m + '%';
                        el.className = parseFloat(m) >= 30
                            ? 'text-sm font-medium text-[#4a7c59]'
                            : (parseFloat(m) >= 10 ? 'text-sm font-medium text-[#856404]' : 'text-sm font-medium text-red-400');
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                agregarLineaVenta();
                 });