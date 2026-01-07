
@extends('layouts.dashboard')

@section('title', 'Inicio')

@section('content')
<div class="d-flex align-items-center justify-content-center vh-100 bg-white text-dark">
    <div class="text-center p-5">
        @if (Auth::user()->rol === 'admin')
        <h1 class="fw-bold mb-3">ADMIN <span class="text-primary">FH SAS</span></h1>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="{{ route('admin.usuarios') }}" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                    Crear usuarios
                </a>
                <a href="{{ route('admin.inventario') }}" class="btn btn-outline-dark btn-lg fw-semibold">
                    Cargar inventario
                </a>
            </div>
        @endif
    
        @if (Auth::user()->rol !== 'admin')
        <h1 class="fw-bold mb-3">Bienvenido a <span class="text-primary">FH SAS</span></h1>
        <p class="lead mb-4 text-muted">
            Gestión de cotización, producción y trazabilidad.
        </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="{{ route('propuestas.create') }}" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                    Crear Cotización
                </a>
                <a href="{{ route('propuestas.cotizacion') }}" class="btn btn-outline-dark btn-lg fw-semibold">
                    Ver Cotizaciones
                </a>
            </div>
        @endif

        <hr class="my-5 text-secondary w-50 mx-auto">

        <div class="small text-muted">
            <p>© {{ date('Y') }} FH SAS — Todos los derechos reservados.</p>
        </div>
    </div>
</div>
@endsection
