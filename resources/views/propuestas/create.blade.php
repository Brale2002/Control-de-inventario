
@extends('layouts.dashboard')

<style>
    /* ===== ESTILO BASE ===== */
    .table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 14px;
    }
    
    .table th, .table td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
    }
    
    .table thead tr {
        background: #1a1a1a;
        color: white;
    }
    
    .table tbody tr:nth-child(even) {
        background: #f9f9f9;
    }
    
    /* ===== RESPONSIVE (pantallas pequeñas) ===== */
    @media (max-width: 768px) {
        /* Mostrar las tablas en formato "tarjeta" vertical */
        .table thead {
            display: none;
        }
    
        .table, 
        .table tbody, 
        .table tr, 
        .table td {
            display: block;
            width: 100%;
        }
    
        .table tr {
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }
    
        .table td {
            text-align: left;
            padding: 10px 15px;
            position: relative;
        }
    
        /* Mostrar el nombre de la columna a la izquierda */
        .table td::before {
            content: attr(data-label);
            font-weight: bold;
            display: inline-block;
            width: 45%;
            color: #333;
        }
    
        /* Ajuste visual */
        .table td:last-child {
            border-bottom: none;
        }
    
        /* Centrar botones */
        .table td .btn {
            display: block;
            width: 100%;
            margin: 5px 0;
        }
    }
    </style>


@section('content')
<div class="container">
    <h3>Crear Cotización</h3>

    <form id="cotizacionForm" action="{{ route('propuestas.store') }}" method="POST">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @csrf

        {{-- Cliente existente o externo --}}
        <div class="mb-3" id="cliente_existente_container">
            <label for="cliente_id" class="form-label">Cliente</label>
    <select name="cliente_id" id="cliente_id" class="form-select select2">
        <option value="">Seleccione un cliente</option>
        @foreach($clientes as $cliente)
            <option value="{{ $cliente->id }}">
                {{ $cliente->nombre }} - {{ $cliente->cedula ?? $cliente->documento ?? 'Sin documento' }}
            </option>
        @endforeach
    </select>
</div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="cliente_externo" name="cliente_externo" value="1">
            <label for="cliente_externo" class="form-check-label">Cliente externo</label>
        </div>

        <div id="datos_cliente_externo" style="display:none;">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="nombre_externo" class="form-control" placeholder="Nombre del cliente externo">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="cedula_externo" class="form-control" placeholder="Cédula">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="num" id="telefono_externo" name="telefono_externo" class="form-control" minlength="10" maxlength="10" pattern="\d{10}" placeholder="Teléfono">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="direccion_externo" class="form-control" placeholder="Dirección">
                </div>
            </div>
        </div>
      

{{-- Agregar pedidos (observaciones con ítems) --}}
<div class="card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Pedidos y Observaciones</h5>
        <button type="button" id="agregarPedido" class="btn btn-outline-primary btn-sm">
            + Añadir Pedido
        </button>
    </div>
    
    {{-- Campo de observación --}}
    <div class="mb-3">
        <label for="observacion_actual">Observación actual</label>
        <textarea id="observacion_actual" class="form-control" rows="2" placeholder="Ejemplo: Uniformes grupo A"></textarea>
    </div>
    
    <div class="col-md-2">
        <label for="talla" class="form-label fw-semibold">Talla</label>
        <select id="talla" class="form-select shadow-sm">
            <option value="">Seleccionar talla...</option>
        </select>
    </div>
    
    {{-- Selección de ítem --}}
    <div class="row gy-3 align-items-end text-center mt-2">
        <div class="col-md-5">
            <label for="item" class="form-label fw-semibold">Seleccionar ítem</label>
            <select id="item" name="bodega_id" class="form-select shadow-sm" style="width: 100%;">
                <option value="">Buscar ítem...</option>
            </select>
        </div>
    
        <div class="col-md-2">
            <label for="cantidad">
                Cantidad 
            </label>
            <input type="number" id="cantidad" class="form-control shadow-sm text-center" min="1" disabled>
        </div>
    
        <div class="col-md-2 d-flex justify-content-center">
            <button type="button" id="agregarItem" class="btn btn-success px-4 py-2 shadow-sm rounded-3">
                Agregar ítem
            </button>
        </div>
        <small id="stockDisponible" class="text-muted"></small>

    </div>
    
</div>

{{-- Tabla --}}
<table class="table table-bordered" id="tablaItems">
    <thead>
        <tr>
            <th>Observación</th>
            <th>Cantidad</th>
            <th>Talla</th>
            <th>Nombre</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<input type="hidden" name="items" id="itemsSeleccionados">


        <div class="form-group">
            <label for="fecha_entrega_estimada">Fecha estimada de entrega:</label>
            <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar cotización</button>
    </form>
</div>
@endsection

@section('scripts')

<script>

    $(function() {
        // --- ELEMENTOS ---
        const $chkExterno = $('#cliente_externo');
        const $divExterno = $('#datos_cliente_externo');
        const $selectExistenteContainer = $('#cliente_existente_container');
        const $selectCliente = $('#cliente_id');
    
        const $itemSelect = $('#item');
        const $cantidad = $('#cantidad');
        const $stockDisponible = $('#stockDisponible');
        const $agregarItemBtn = $('#agregarItem');
        const $agregarPedidoBtn = $('#agregarPedido');
        const $observacionActual = $('#observacion_actual');
        const $tablaBody = $('#tablaItems tbody');
        const $itemsHidden = $('#itemsSeleccionados');
    
        // --- VALIDACIONES BÁSICAS ---
        if ($agregarPedidoBtn.length === 0) {
            console.error('Botón #agregarPedido no encontrado en el DOM.');
        }
    
        // --- CLIENTE EXTERNO ---
        $chkExterno.on('change', function() {
    if (this.checked) {
        // mostrar cliente externo
        $divExterno.show();
        $selectExistenteContainer.hide();

        // limpiar y deshabilitar cliente_id
        $selectCliente.val(null).trigger('change');
        $selectCliente.prop('required', false);
        $selectCliente.prop('disabled', true);
    } else {
        // volver a mostrar clientes normales
        $divExterno.hide();
        $selectExistenteContainer.show();

        $selectCliente.prop('disabled', false);
        $selectCliente.prop('required', true);
    }
});

    
        // --- SELECT2 CLIENTES ---
        if ($selectCliente.length) {
            $selectCliente.select2({
                placeholder: 'Buscar cliente...',
                allowClear: true,
                width: '100%'
            });
        }


        //TALLA
const selectTalla = document.getElementById('talla');

fetch('{{ url("api/tallas") }}')
    .then(response => {
        if (!response.ok) throw new Error('Error al obtener tallas');
        return response.json();
    })
    .then(data => {
        selectTalla.innerHTML = '<option value="">Seleccionar talla...</option>';
        data.forEach(talla => {
            const option = document.createElement('option');
            option.value = talla;
            option.textContent = talla;
            selectTalla.appendChild(option);
        });
        selectTalla.disabled = false;
    })
    .catch(error => {
        console.error('Error cargando tallas:', error);
        selectTalla.innerHTML = '<option value="">Error al cargar tallas</option>';
    });


    
        // --- SELECT2 ITEMS (AJAX) ---
        $itemSelect.select2({
            placeholder: 'Buscar ítem...',
            width: 'resolve',
            minimumInputLength: 0,
            ajax: {
                url: '/api/bodega',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term && params.term.length > 0 ? params.term : 'A' }),
                processResults: function(data) {
                    if (!Array.isArray(data)) return { results: [] };
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: `${item.codigo} - ${item.nombre}`,
                            codigo: item.codigo,
                            nombre: item.nombre,
                            stock: item.stock,
                            talla: item.talla
                        }))
                    };
                },
                cache: true
            },
            language: {
                inputTooShort: () => "Escribe para buscar un ítem...",
                noResults: () => "No se encontraron resultados.",
                searching: () => "Buscando..."
            }
        });
    
        // --- AL SELECCIONAR ITEM, MOSTRAR STOCK ---
        $itemSelect.on('select2:select', function(e) {
            const data = e.params.data || {};
            const stock = data.stock ?? 0;
            $cantidad.prop('disabled', false).attr('max', stock).val(1);
            $stockDisponible.text(`Disponible: ${stock}`);
        });
    
        // --- ESTRUCTURA: pedidos y pedidoActual ---
        let pedidos = [];
        let pedidoActual = { observacion: '', items: [] };
    
        // --- AGREGAR ÍTEM al pedido actual ---
        $agregarItemBtn.on('click', function() {
            const data = $itemSelect.select2('data')[0];
            const cantidadVal = parseInt($cantidad.val());
            const observacionTexto = $observacionActual.val().trim();
    
            if (!data) { alert('Seleccione un ítem válido.'); return; }
            if (isNaN(cantidadVal) || cantidadVal <= 0) { alert('Ingrese una cantidad válida.'); return; }
            if (!observacionTexto) { alert('Escribe la observación para este pedido antes de agregar ítems.'); return; }
    
            const stock = parseInt($cantidad.attr('max') || 0);
            if (cantidadVal > stock) { alert(`Solo hay ${stock} disponibles.`); $cantidad.val(stock); return; }
    
            pedidoActual.observacion = observacionTexto;
            // Evitar duplicados dentro del mismo pedido (mismo id)
            if (pedidoActual.items.find(it => it.id === data.id)) {
                alert('Este ítem ya fue agregado al pedido actual.');
                return;
            }
    
            const tallaSeleccionada = $('#talla').val() || '';
            if (!tallaSeleccionada) {
                alert('Selecciona una talla antes de agregar el ítem.');
                return;
            }

            pedidoActual.items.push({
                id: data.id,
                codigo: data.codigo,
                nombre: data.nombre,
                cantidad: cantidadVal,
                talla: tallaSeleccionada
            });
    
            // limpiar selección para añadir otro
            $itemSelect.val(null).trigger('change');
            $cantidad.val('').prop('disabled', true);
            $stockDisponible.text('');
            renderTabla();
        });
    
        // --- AÑADIR PEDIDO (finalizar pedidoActual y crear uno nuevo) ---
        $agregarPedidoBtn.on('click', function() {
            // Validar que haya observación y al menos 1 ítem
            if (!pedidoActual.observacion || pedidoActual.items.length === 0) {
                alert('Completa la observación e agrega al menos un ítem antes de crear un nuevo pedido.');
                return;
            }
    
            // Guardar copia del pedidoActual en pedidos
            pedidos.push(JSON.parse(JSON.stringify(pedidoActual))); // copia profunda
            // reset pedidoActual
            pedidoActual = { observacion: '', items: [] };
            $observacionActual.val('');
            // refrescar tabla / hidden
            renderTabla();
            // Mensaje opcional
            // alert('Pedido guardado. Puedes crear uno nuevo.');
        });
    
        // --- RENDERIZAR TABLA Y ACTUALIZAR hidden JSON ---
        function renderTabla() {
            $tablaBody.empty();
            
            // mostrar pedidos guardados
            pedidos.forEach((pedido, pIdx) => {
                pedido.items.forEach((it, iIdx) => {
                    $tablaBody.append(`
                        <tr>
                            <td>${escapeHtml(pedido.observacion)}</td>
                            <td>${it.cantidad}</td>
                            <td>${escapeHtml(it.talla)}</td>
                            <td>${escapeHtml(it.nombre)}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm eliminarItem" data-pedido="${pIdx}" data-index="${iIdx}">Eliminar</button>
                            </td>
                        </tr>
                    `);
                });
            });
    
            // mostrar items del pedido actual (en curso) con estilo
            pedidoActual.items.forEach((it, idx) => {
                $tablaBody.append(`
                    <tr class="table-info">
                        <td>${escapeHtml(pedidoActual.observacion || '(en curso)')}</td>
                        <td>${it.cantidad}</td>
                        <td>${escapeHtml(it.talla)}</td>
                        <td>${escapeHtml(it.nombre)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm eliminarItemActual" data-index="${idx}">Eliminar</button>
                        </td>
                    </tr>
                `);
            });
    
            // Construir JSON final: todos los pedidos +, si hay pedidoActual con items, agregarlo también
            const all = pedidos.map(p => ({ observacion: p.observacion, items: p.items }));
            if (pedidoActual.items.length > 0) {
                all.push({ observacion: pedidoActual.observacion, items: pedidoActual.items });
            }
            $itemsHidden.val(JSON.stringify(all));
        }
    
        // --- ELIMINAR ITEM de pedido guardado ---
        $(document).on('click', '.eliminarItem', function() {
            const p = parseInt($(this).data('pedido'));
            const i = parseInt($(this).data('index'));
            if (isNaN(p) || isNaN(i)) return;
            pedidos[p].items.splice(i,1);
            // si pedido quedó vacío, lo removemos
            if (pedidos[p].items.length === 0) pedidos.splice(p,1);
            renderTabla();
        });
    
        // --- ELIMINAR ITEM del pedido actual ---
        $(document).on('click', '.eliminarItemActual', function() {
            const i = parseInt($(this).data('index'));
            if (isNaN(i)) return;
            pedidoActual.items.splice(i,1);
            renderTabla();
        });
    
        // --- utilidad escape para evitar inyección en la tabla ---
        function escapeHtml(text) {
            if (!text && text !== 0) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    
        // --- Inicial render ---
        renderTabla();
    });
    </script>
    
@endsection

