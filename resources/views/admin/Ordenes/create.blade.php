@extends('layouts.dashboard')

@section('title', 'Nueva Orden de Compra')

@section('content')

<div class="container">

    <h3 class="mb-3">Crear Orden de Compra</h3>

    <div class="card p-4">

        <form action="{{ route('admin.ordenescompra.store') }}" method="POST">
            @csrf
        
            {{-- PROVEEDOR --}}
            <div class="mb-3">
                <label class="form-label">Proveedor</label>
                <input type="text" name="proveedor" class="form-control" required>
            </div>
        
            {{-- PRODUCTO --}}
            <div class="mb-3">
                <label class="form-label">Producto</label>
                <select id="producto_id" class="form-control" required>
                    <option value="">Seleccione un producto</option>
                    @foreach ($productos as $p)
                        <option 
                            value="{{ $p->id }}"
                            data-precio="{{ $p->precio }}"
                            data-stock="{{ $p->stock }}"
                            data-iva="{{ $p->iva }}"
                        >
                            {{ $p->codigo }} - {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        
            {{-- INFO AUTOMÁTICA --}}
            <input type="hidden" name="items[0][producto_id]" id="item_producto">
            <input type="hidden" name="items[0][precio]" id="item_precio">
        
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Precio Unidad</label>
                    <input type="text" id="precio_unit" class="form-control" readonly>
                </div>
        
                <div class="col-md-4">
                    <label class="form-label">IVA %</label>
                    <input type="text" id="iva_unit" class="form-control" readonly>
                </div>
        
                <div class="col-md-4">
                    <label class="form-label">Stock Actual</label>
                    <input type="text" id="stock_actual" class="form-control" readonly>
                </div>
            </div>
        
            {{-- CANTIDAD --}}
            <div class="mb-3">
                <label class="form-label">Cantidad a Comprar</label>
                <input type="number" name="items[0][cantidad]" id="cantidad" class="form-control" min="1" required>
            </div>
        
            {{-- CÁLCULOS --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Subtotal</label>
                    <input type="text" id="subtotal" class="form-control" readonly>
                </div>
        
                <div class="col-md-4">
                    <label class="form-label">IVA</label>
                    <input type="text" id="iva_total" class="form-control" readonly>
                </div>
        
                <div class="col-md-4">
                    <label class="form-label">Total</label>
                    <input type="text" id="total" class="form-control" readonly>
                </div>
            </div>
        
            <button class="btn btn-primary mt-3">Guardar Orden</button>
        </form>
        
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    
        const select = document.getElementById("producto_id");
        const cantInput = document.getElementById("cantidad");
    
        let precio = 0;
        let iva = 0;
    
        select.addEventListener("change", function () {
    
            const op = select.options[select.selectedIndex];
    
            precio = parseFloat(op.dataset.precio || 0);
            iva = parseFloat(op.dataset.iva || 0);
            const stock = op.dataset.stock || 0;
    
            // asignar al formulario real
            document.getElementById("item_producto").value = op.value;
            document.getElementById("item_precio").value = precio;
    
            document.getElementById("precio_unit").value = precio.toFixed(2);
            document.getElementById("iva_unit").value = iva + "%";
            document.getElementById("stock_actual").value = stock;
    
            calcular();
        });
    
    
        cantInput.addEventListener("input", calcular);
    
        function calcular() {
            const cant = parseInt(cantInput.value) || 0;
    
            const sub = cant * precio;
            const ivaCalc = sub * (iva / 100);
            const total = sub + ivaCalc;
    
            document.getElementById("subtotal").value = sub.toFixed(2);
            document.getElementById("iva_total").value = ivaCalc.toFixed(2);
            document.getElementById("total").value = total.toFixed(2);
        }
    
    });
    </script>
    
@endsection
