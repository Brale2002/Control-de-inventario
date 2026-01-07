@extends('layouts.admin')

@section('content')

<div class="container mt-4">

    <h3 class="mb-4">Detalle de Orden de Compra #{{ $orden->id }}</h3>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Proveedor:</strong> {{ $orden->proveedor ?? 'Sin proveedor' }}</p>
            <p><strong>Total:</strong> ${{ number_format($orden->total, 0) }}</p>
            <p><strong>Estado:</strong>
                @if($orden->estado === 'confirmada')
                    <span class="badge bg-success">Confirmada</span>
                @else
                    <span class="badge bg-warning text-dark">Pendiente</span>
                @endif
            </p>
            <p><strong>Fecha:</strong> {{ $orden->created_at }}</p>
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

        <tbody>
            @foreach($orden->items as $item)
            <tr>
                <td>{{ $item->producto->nombre ?? 'Producto eliminado' }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>${{ number_format($item->precio_unitario, 0) }}</td>
                <td>${{ number_format($item->cantidad * $item->precio_unitario, 0) }}</td>
            </tr>
            @endforeach
        </tbody>

    </table>

    <a href="{{ route('admin.ordenescompra.index') }}" class="btn btn-secondary mt-3">Volver</a>

</div>

@endsection
