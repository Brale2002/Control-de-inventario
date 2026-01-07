@extends('layouts.dashboard')

@section('content')

<h2 class="mb-4">Cargar Inventario desde Excel</h2>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Botón para descargar plantilla -->
<a href="{{ route('admin.inventario.plantilla') }}" class="btn btn-success mb-3">
    Descargar Plantilla Excel
</a>

<!-- Formulario de subida -->
<form action="{{ route('admin.inventario.importar') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label class="form-label">Seleccione un archivo Excel</label>
        <input type="file" name="archivo" class="form-control" accept=".csv,text/csv" required>
    </div>

    <button class="btn btn-primary">Importar Inventario</button>
</form>

@endsection
