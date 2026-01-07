<div>
    <h5 class="mb-3">Información de la Orden de Pedido</h5>
    <p><strong>Código:</strong> {{ $propuesta->codigo ?? 'Sin código' }}</p>
    <p><strong>Cliente:</strong> {{ $propuesta->cliente->nombre ?? 'Sin cliente' }}</p>
    <p><strong>Teléfono:</strong> {{ $propuesta->cliente->telefono ?? 'Sin cliente' }}</p>
    <p><strong>Fecha:</strong> {{ $propuesta->fecha ?? '-' }}</p>
    <p><strong>Fecha de Entrega:</strong> {{ $propuesta->fecha_entrega_estimada ?? '-' }}</p>
    <p><strong>Estado:</strong> {{ ucfirst($propuesta->estado ?? 'Pendiente') }}</p>
    {{-- <p><strong>Sede:</strong> {{ $propuesta->sede ?? '-' }}</p> --}}

    <hr>

    <h6>Detalle de Productos</h6>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Código</th>
                <th>Material</th>
                <th>Cantidad</th>
                <th>Talla</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($propuesta->detalles as $d)
                <tr>
                    <td>{{ $d->bodega->codigo }}</td>
                    <td>{{ $d->bodega->nombre }}</td>
                    <td>{{ $d->cantidad }}</td>
                    <td>{{ $d->talla }}</td>
                    <td>{{ $d->observacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- @if($orden->imagen)
        <div class="mt-3 text-center">
            <img src="{{ asset('storage/' . $orden->imagen) }}" alt="Referencia del producto" class="img-fluid rounded" style="max-width: 250px;">
        </div>
    @endif --}}
</div>
