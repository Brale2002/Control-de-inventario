
@extends('layouts.dashboard')

@section('title', 'Trazabilidad de la Propuesta')
<style>
    /* --- Estilos base --- */
.table-modern {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background: #fff;
}

.table-modern th, 
.table-modern td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

.table-modern thead tr {
    background: #111827;
    color: #fff;
}

.table-modern tbody tr:nth-child(even) {
    background: #f9fafb;
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .table-modern thead {
        display: none; /* Oculta encabezados */
    }

    .table-modern, 
    .table-modern tbody, 
    .table-modern tr, 
    .table-modern td {
        display: block;
        width: 100%;
    }

    .table-modern tr {
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .table-modern td {
        text-align: left;
        padding: 8px 10px;
        position: relative;
        border: none;
        border-bottom: 1px solid #eee;
    }

    /* Nombre del campo */
    .table-modern td::before {
        content: attr(data-label);
        font-weight: bold;
        display: inline-block;
        width: 45%;
        color: #111827;
        background: #f3f4f6;
        padding: 6px 8px;
        margin-right: 8px;
        border-radius: 4px;
    }

    /* Intercalado de color entre filas */
    .table-modern tbody tr:nth-child(even) td::before {
        background: #e5e7eb;
    }
}
    </style>
    
@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Trazabilidad de la Propuesta #{{ $propuesta->codigo }}</h3>
    <p><strong>Cliente:</strong> {{ $propuesta->cliente->nombre ?? 'Sin cliente' }}</p>
    <p><strong>Estado actual:</strong> {{ ucfirst($propuesta->estado) }}</p>

    <hr>

    @if($trazabilidad->count())
        <table  class="table-modern">
            <thead class="table-light">
                <tr>
                    <th>Paso</th>
                    <th>Nombre del paso</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Responsable</th>
                    <th>Taller</th>
                    <th>Acciones</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($trazabilidad as $t)
                <tr>
                    <td data-label="Paso">{{ $t->paso }}</td>
                    <td data-label="Nombre del paso">{{ $t->nombre_paso }}</td>
                    <td data-label="Descripción" class="text-start">{{ $t->descripcion }}</td>
                    <td data-label="Estado">
                        @if($t->estado == 'pendiente')
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @else
                            <span class="badge bg-success">Completado</span>
                        @endif
                    </td>
                    <td data-label="Fecha inicio">{{ $t->fecha_inicio ?? '-' }}</td>
                    <td data-label="Fecha fin">{{ $t->fecha_fin ?? '-' }}</td>
                    <td data-label="Responsable">{{ $t->responsable ?? '-' }}</td>
                    <td data-label="Taller">{{ $t->TallerAsignado ?? '-' }}</td>
                    <td data-label="Acciones">
                            @if($t->paso == 4 && $t->estado == 'pendiente')
                                <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRevisionTecnica">
                                    Revisión Técnica
                                </button>
                            @elseif($t->paso == 5 && $t->estado == 'pendiente')
                            <form action="{{ route('propuestas.corteRealizado', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" name="estado_revision" value="completado">Cortes realizados</button>
                            </form>
                            @elseif($t->paso == 6 && $t->estado == 'pendiente')
                            <form action="{{ route('propuestas.confeccionRealizada', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" name="estado_revision" value="completado">Confección realizada</button>
                            </form>
                            @elseif($t->paso == 7 && $t->estado == 'pendiente')
                            <form action="{{ route('propuestas.bordadoRealizado', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" name="estado_revision" value="completado">
                                    Paso realizado
                                </button>
                            </form>
                            <form action="{{ route('propuestas.bordadoNoAplica', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    No aplica
                                </button>
                            </form>
                            @elseif($t->paso == 8 && $t->estado == 'pendiente')
                            <form action="{{ route('propuestas.ControlRealizado', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" name="estado_revision" value="completado">
                                    Caldiad aprobada
                                </button>
                            </form>
                            @elseif($t->paso == 9 && $t->estado == 'pendiente')
                            <form action="{{ route('propuestas.EntregaRealizada', $propuesta->codigo) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" name="estado_revision" value="completado">
                                    Entrega realizada
                                </button>
                            </form>
                            @elseif($t->paso == 10 && $t->estado == 'finalizado')
                            <a href="{{ route('propuestas.facturar', $propuesta->codigo) }}"
                                class="btn btn-primary btn-sm">
                                Facturar
                             </a>                             
                            @else
                                <form action="{{ route('propuestas.borrarPaso', ['codigo' => $propuesta->codigo, 'paso' => $t->paso]) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este paso y todos los posteriores?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">No hay registros de trazabilidad para esta propuesta.</p>
    @endif

    {{-- FORMULARIO DE ASIGNACIÓN DE TALLER --}}
    @php
        $ultimoPaso = $trazabilidad->sortByDesc('paso')->first();
    @endphp

    @if($ultimoPaso && $ultimoPaso->paso == 3 && $ultimoPaso->estado == 'pendiente')
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Asignar Taller</h5>
            <form action="{{ route('propuestas.asignarTaller', $propuesta->codigo) }}" method="POST" class="row g-3 align-items-center">
                @csrf
                <div class="col-auto">
                    <label for="taller" class="col-form-label fw-bold">Seleccionar Taller:</label>
                </div>
                <div class="col-auto">
                    <select name="taller" id="taller" class="form-select form-select-sm" required>
                        <option value="">Selecciona...</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success btn-sm">
                        Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif


    <a href="{{ route('propuestas.cotizacion') }}" class="btn btn-secondary mt-4">
        Volver al listado
    </a>
</div>

<!-- MODAL Revisión Técnica (Paso 4) -->
<div class="modal fade" id="modalRevisionTecnica" tabindex="-1" aria-labelledby="revisionTecnicaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold" id="revisionTecnicaLabel">Revisión Técnica e Insumos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('propuestas.revisionTecnica', $propuesta->codigo) }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{--MENSAJES DE ERROR EN EL MODAL --}}
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Error:</strong> Debe llenar el formulario completo.
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                    {{--Script para reabrir automáticamente el modal si hubo error --}}
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var modal = new bootstrap.Modal(document.getElementById('modalRevisionTecnica'));
                            modal.show();
                        });
                    </script>
                    @endif

                    {{--VALIDACIÓN TÉCNICA--}}
                    <h5 class="mb-3">Validación Técnica</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observaciones técnicas</label>
                        <textarea name="observaciones_tecnicas" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="moldes_listos" id="moldes_listos" value="1">
                        <label class="form-check-label" for="moldes_listos">Moldes listos</label>
                    </div>

                    {{--ANÁLISIS DE INSUMOS--}}
                    <h5 class="mb-3">Análisis de Insumos según Detalle de Cotización</h5>

                    @if(isset($propuesta->detalles) && $propuesta->detalles->count())
                        <table class="table table-sm table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad Requerida</th>
                                    <th>Cantidad Disponible</th>
                                    <th>Validado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propuesta->detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle->bodega->nombre ?? 'Sin nombre' }}</td>
                                        <td>{{ $detalle->cantidad ?? '-' }}</td>
                                        <td>{{ $detalle->bodega->stock ?? 'N/A' }}</td>
                                        <td>
                                            <input type="checkbox" name="validado[{{ $detalle->id }}]" value="1">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No hay detalles de cotización disponibles.</p>
                    @endif
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="ficha_tecnica_ok" id="ficha_tecnica_ok" value="1">
                        <label class="form-check-label" for="ficha_tecnica_ok">Ficha técnica correcta</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="estado_revision" value="completado">Guardar revisión</button>
                </div>
            </form>
        </div>
    </div>
</div>
  

@endsection
