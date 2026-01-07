@extends('layouts.dashboard')

@section('title','Bodega')

@section('content')
<div class="container">
    <h3>Control de Bodega</h3>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total productos</h6>
                <h4>{{ $totalProductos }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total en stock</h6>
                <h4>{{ $totalStock }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Valor inventario</h6>
                <h4>${{ number_format($valorInventario,2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total vendidos</h6>
                <h4>{{ $totalVendido }}</h4>
            </div>
        </div>
    </div>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">Agregar producto</button>
            <a href="{{ route('admin.ordenescompra.create') }}" class="btn btn-outline-secondary">Orden de Compra</a>
        </div>

        <form class="d-flex" method="GET" action="{{ route('admin.bodega.index') }}">
            <input name="q" value="{{ request('q') }}" class="form-control me-2" placeholder="Buscar...">
            <button class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoria</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Iva</th>
                    <th>Valor total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->codigo }}</td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->categoria }}</td>
                    <td>{{ $p->stock ?? 0 }}</td>
                    <td>${{ number_format($p->precio,2) }}</td>
                    <td>{{ $p->iva }}%</td>
                    <td>${{ number_format(($p->stock ?? 0) * $p->precio,2) }}</td>
                    <td>
                        <button class="btn btn-sm btn-info btn-edit-producto" data-id="{{ $p->id }}" data-codigo="{{ $p->codigo }}" data-nombre="{{ $p->nombre }}" data-categoria="{{ $p->categoria }}" data-precio="{{ $p->precio }}" data-iva="{{ $p->iva }}" data-stock="{{ $p->stock }}" data-descripcion="{{ $p->descripcion }}">Editar</button>

                        <form action="{{ route('admin.bodega.destroy', $p->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Eliminar producto?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>

                        <button class="btn btn-sm btn-success btn-entrada" data-id="{{ $p->id }}" data-nombre="{{ $p->nombre }}">Entrada</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $productos->links() }}
    </div>
</div>

@include('admin.bodega.partials.modal_crear')
@include('admin.bodega.partials.modal_editar')
@include('admin.bodega.partials.modal_entrada')

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // editar - abrir modal con datos
    document.querySelectorAll('.btn-edit-producto').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_codigo').value = this.dataset.codigo;
            document.getElementById('edit_nombre').value = this.dataset.nombre;
            document.getElementById('edit_categoria').value = this.dataset.categoria;
            document.getElementById('edit_precio').value = this.dataset.precio;
            document.getElementById('edit_iva').value = this.dataset.iva;
            document.getElementById('edit_stock').value = this.dataset.stock;
            document.getElementById('edit_descripcion').value = this.dataset.descripcion;
            modal.show();
        });
    });

    // entrada
    document.querySelectorAll('.btn-entrada').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalEntrada'));
            document.getElementById('entrada_producto_id').value = this.dataset.id;
            document.getElementById('entrada_nombre').textContent = this.dataset.nombre;
            modal.show();
        });
    });

    // salida
    document.querySelectorAll('.btn-salida').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalSalida'));
            document.getElementById('salida_producto_id').value = this.dataset.id;
            document.getElementById('salida_nombre').textContent = this.dataset.nombre;
            modal.show();
        });
    });
});
</script>
@endsection
