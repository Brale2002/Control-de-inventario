@extends('layouts.dashboard')

@section('title', 'Editar Cotización')

@section('content')
<div class="container mt-4">
    <h2>Editar Cotización #{{ $propuesta->codigo }}</h2>

    <form action="{{ route('propuestas.update', $propuesta->codigo) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- CLIENTE (solo lectura) --}}
        <div class="mb-3">
            <label clas="form-label">Cliente</label>
            <input type="text" class="form-control" value="{{ $propuesta->cliente->nombre }}" readonly>
        </div>

        {{-- FECHA ENTREGA --}}
        <div class="mb-3">
            <label for="fecha_entrega_estimada" class="form-label">Fecha entrega estimada</label>
            <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada"
                   class="form-control" value="{{ $propuesta->fecha_entrega_estimada }}">
        </div>

        {{-- NOTAS --}}
        <div class="mb-3">
            <label for="notas" class="form-label">Nota</label>
            <textarea name="nota" id="nota" rows="3" class="form-control">{{ $propuesta->nota }}</textarea>
        </div>

        {{-- PRODUCTOS Y CANTIDADES --}}
        <h5 class="mt-4">Productos de la cotización</h5>
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tabla-productos">
                @foreach($propuesta->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->bodega->nombre ?? 'Sin nombre' }}</td>
                        <td>
                            <input type="number" 
                            name="cantidades[{{ $detalle->id }}]" 
                            class="form-control text-center" 
                            value="{{ $detalle->cantidad }}" 
                            min="1"
                            max="{{ $detalle->bodega->stock ?? 0 }}"
                            data-stock="{{ $detalle->bodega->stock ?? 0 }}">
                        <small class="text-muted">
                            Disponible: {{ $detalle->bodega->stock ?? 0 }}
                        </small>
                        </td>
                        <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td>${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-danger btnEliminarFila" 
                                data-id="{{ $detalle->id }}">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- BOTONES --}}
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('propuestas.cotizacion') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

{{-- SCRIPT PARA ELIMINAR FILAS --}}
<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btnEliminarFila')) {
            const fila = e.target.closest('tr');
            const detalleId = e.target.dataset.id;
    
            if (confirm('¿Seguro que deseas eliminar este producto de la cotización?')) {
                fetch(`/propuestas/detalle/${detalleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error al eliminar');
                    fila.remove(); // elimina visualmente
                })
                .catch(error => alert('No se pudo eliminar el detalle.'));
            }
        }
    });
    </script>
    
@endsection
