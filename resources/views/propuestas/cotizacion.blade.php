@extends('layouts.dashboard')


@section('title', 'Cotizaciones')

@section('content')
<style>
/* --- vista móvil --- */
@media (max-width: 768px) {

/* ocultamos el encabezado */
.responsive-table thead {
  display: none;
}

/* hacemos que cada fila sea una “mini tabla” */
.responsive-table tbody tr {
  display: table;
  width: 100%;
  margin-bottom: 1rem;
  border-collapse: collapse;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

/* celdas dentro del cuerpo */
.responsive-table td {
  display: table-row;
  width: 100%;
  text-align: left;
}

/* estilo de pares e impares (mantiene bandas de color) */
.responsive-table tbody tr:nth-child(odd) td {
  background: #f9fafb; /* claro */
}
.responsive-table tbody tr:nth-child(even) td {
  background: #e5e7eb; /* un poco más oscuro */
}

/* título (izquierda) y valor (derecha) */
.responsive-table td::before {
  content: attr(data-label);
  display: table-cell;
  font-weight: bold;
  color: #111827;
  padding: 10px 8px;
  width: 40%;
  border-right: 1px solid #ddd;
}

.responsive-table td span {
  display: table-cell;
  padding: 10px 8px;
  width: 60%;
}

/* ajusta la estructura interna */
.responsive-table td {
  display: table-row;
  border-bottom: 1px solid #ddd;
}

.responsive-table td:last-child {
  border-bottom: none;
}
}
.modal {
    z-index: 1205 !important;
}

.modal-backdrop {
    z-index: 1200 !important;
}
</style>
<div class="container mt-4">
    <h2>Listado de Cotizaciones</h2>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Botón para crear una nueva cotización --}}
    <a href="{{ route('propuestas.create') }}" class="btn btn-primary mb-3">
        Nueva Cotización
    </a>

    {{-- Tabla de cotizaciones --}}
    <table class="table table-striped table-bordered align-middle text-center responsive-table">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Acciones</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($propuestas as $p)
                <tr>
                    <td data-label="ID">{{ $p->codigo }}</td>
                    <td data-label="Cliente">{{ $p->cliente->nombre ?? 'Sin cliente' }}</td>
                    <td data-label="Fecha">{{ $p->fecha ?? '-' }}</td>
                    <td data-label="Estado">{{ ucfirst($p->estado ?? 'Pendiente') }}</td>
                    <td data-label="Total">${{ number_format($p->total ?? 0, 2) }}</td>
                    <td data-label="Paso">
                        @if($p->ultimoPaso)
                            {{ $p->ultimoPaso->nombre_paso }} 
                            <br>
                            <small class="text-muted">(Paso {{ $p->ultimoPaso->paso }})</small>
                        @else
                            <span class="text-muted">Sin registro</span>
                        @endif
                    </td>
                    <td data-label="Acciones">
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            {{-- Botón Ver --}}
                            <button 
                                class="btn btn-info btn-sm btnVerPropuesta" 
                                data-id="{{ $p->codigo }}">
                                Ver
                            </button>
    
                            {{-- Botón Eliminar --}}
                            <form action="{{ route('propuestas.destroy', $p->codigo) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta propuesta?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Eliminar
                                </button>
                            </form>
    
                            {{-- Botón Aprobar / Ver trazabilidad --}}
                            @if($p->estado === 'aprobada')
                                <a href="{{ route('propuestas.trazabilidad', $p->codigo) }}" class="btn btn-sm btn-warning">
                                    Ver trazabilidad
                                </a>
                            @else
                                <form action="{{ route('propuestas.aprobar', $p->codigo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Aprobar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td data-label colspan="7" class="text-center">No hay cotizaciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
</div>
<!-- Modal para ver cotización -->
<div class="modal fade" id="modalVerPropuesta" tabindex="-1" aria-labelledby="modalVerPropuestaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalVerPropuestaLabel">Detalle de Cotización</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div id="contenidoPropuesta">
            <p class="text-center text-muted">Cargando datos...</p>
          </div>
        </div>
        <div class="modal-footer">
            <div class="text-end mt-3">
                <a id="btnEditarPropuesta" href="#" class="btn btn-sm btn-warning">
                    Editar
                </a>                
            
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalVerPropuesta');
        const modal = new bootstrap.Modal(modalElement);
        const contenido = document.getElementById('contenidoPropuesta');
    
        document.querySelectorAll('.btnVerPropuesta').forEach(btn => {
            btn.addEventListener('click', function() {
                
                const codigo = this.getAttribute('data-id');
                contenido.innerHTML = '<p class="text-center text-muted">Cargando datos...</p>';
                modal.show();
                
                const btnEditar = document.getElementById('btnEditarPropuesta');
                btnEditar.href = `/propuestas/${codigo}/edit`;
                fetch(`/propuestas/${codigo}/detalle`)
                    .then(response => {
                        if (!response.ok) throw new Error('Error al cargar los datos');
                        return response.text();
                    })
                    .then(html => {
                        contenido.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contenido.innerHTML = '<p class="text-danger text-center">No se pudieron cargar los datos.</p>';
                    });
            });
        });
    });
    </script>
    
    
    
@endsection
