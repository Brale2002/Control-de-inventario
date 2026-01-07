@extends('layouts.dashboard')

@section('title', 'Órdenes de Compra')

@section('content')

<div class="container">

    <h3 class="mb-3">Órdenes de Compra</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('admin.ordenescompra.create') }}" class="btn btn-primary mb-3">
        Crear nueva orden
    </a>

    <div class="card p-3">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ordenes as $o)
                <tr>
            
                    {{-- Datos --}}
                    <td>{{ $o->id }}</td>
                    <td>{{ $o->proveedor ?? 'Sin proveedor' }}</td>
                    <td>${{ number_format($o->total, 0) }}</td>
                    <td>
                        @if($o->estado === 'confirmada')
                            <span class="badge bg-success">Confirmada</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @endif
                    </td>
                    <td>{{ $o->created_at }}</td>
            
                    {{-- Acciones --}}
                    <td>
                        <button class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalVerOrden"

                            data-id="{{ $o->id }}"
                            data-proveedor="{{ $o->proveedor ?? 'Sin proveedor' }}"
                            data-total="{{ $o->total }}"
                            data-estado="{{ $o->estado }}"
                            data-fecha="{{ $o->created_at }}"

                            data-items='@json($o->items->map(function($i){
                                return [
                                    "producto" => $i->producto->nombre ?? "Producto eliminado",
                                    "cantidad" => $i->cantidad,
                                    "precio_unitario" => $i->precio_unitario
                                ];
                            }))'
                        >
                            Ver
                        </button>

                       
            
                        {{-- BOTÓN CONFIRMAR --}}
                        @if($o->estado === 'pendiente')
                            <form action="{{ route('admin.ordenescompra.confirmar', $o->id) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    Confirmar
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary btn-sm" disabled>Ya confirmada</button>
                        @endif
            
            
                        {{-- BOTÓN ELIMINAR --}}
                        <form action="{{ route('ordenes.destroy', $o->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('¿Seguro que deseas eliminar esta orden?');">
            
                            @csrf
                            @method('DELETE')
            
                            <button class="btn btn-danger btn-sm">
                                Eliminar
                            </button>
                        </form>
            
                    </td>
            
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay órdenes registradas</td>
                </tr>
                @endforelse
            </tbody>
                        
        </table>

        {{-- PAGINACIÓN --}}
        <div>
            {{ $ordenes->links() }}
        </div>

    </div>

</div>

<div class="modal fade" id="modalVerOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detalle de Orden de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <h4>Orden #<span id="m-orden-id"></span></h4>

                <div class="card mb-4 mt-3">
                    <div class="card-body">
                        <p><strong>Proveedor:</strong> <span id="m-proveedor"></span></p>
                        <p><strong>Total:</strong> $<span id="m-total"></span></p>
                        <p><strong>Estado:</strong> <span id="m-estado"></span></p>
                        <p><strong>Fecha:</strong> <span id="m-fecha"></span></p>
                    </div>
                </div>

                <h5>Productos en esta Orden</h5>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="m-items">
                        <!-- Items se cargan con JS -->
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>


<script>
    document.getElementById('modalVerOrden').addEventListener('show.bs.modal', function (event) {
        let button = event.relatedTarget;
    
        // Datos generales
        document.getElementById('m-orden-id').textContent = button.getAttribute('data-id');
        document.getElementById('m-proveedor').textContent = button.getAttribute('data-proveedor');
        document.getElementById('m-total').textContent = new Intl.NumberFormat().format(button.getAttribute('data-total'));
        document.getElementById('m-fecha').textContent = button.getAttribute('data-fecha');
    
        // Estado
        let estado = button.getAttribute('data-estado');
        document.getElementById('m-estado').innerHTML =
            estado === 'confirmada'
                ? '<span class="badge bg-success">Confirmada</span>'
                : '<span class="badge bg-warning text-dark">Pendiente</span>';
    
        // ITEMS
        let items = JSON.parse(button.getAttribute('data-items'));
        let tbody = document.getElementById('m-items');
        tbody.innerHTML = ''; // limpiar
    
        items.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td>${item.producto}</td>
                    <td>${item.cantidad}</td>
                    <td>$${new Intl.NumberFormat().format(item.precio_unitario)}</td>
                    <td>$${new Intl.NumberFormat().format(item.cantidad * item.precio_unitario)}</td>
                </tr>
            `;
        });
    });
    </script>
    
    

@endsection
