@extends('layouts.dashboard')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $factura->numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-size: 14px; }
        .factura-box { border: 1px solid #ccc; padding: 20px; border-radius: 10px; }
        .header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .totales { font-size: 18px; font-weight: bold; }
        .text-end { text-align: right; }
    </style>
</head>
<body>

<div class="container mt-4 factura-box">

    <div class="header">
        <h2>Factura Electrónica</h2>
        <p>Número: <strong>{{ $factura->numero }}</strong></p>
        <p>Fecha: {{ $factura->fecha }}</p>
    </div>

    <h5>Datos del Cliente</h5>
    <p><strong>{{ $factura->cliente->nombre }}</strong><br>
       CC/NIT: {{ $factura->cliente->documento }}<br>
       {{ $factura->cliente->direccion }}</p>

    <hr>

    <h5>Productos / Servicios</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>P. Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($factura->items as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>${{ number_format($item->precio_unitario, 0) }}</td>
                <td>${{ number_format($item->cantidad * $item->precio_unitario, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <div class="row">
        <div class="col-6"></div>
        <div class="col-6 text-end totales">
            <p>Total: ${{ number_format($factura->total, 0) }}</p>
        </div>
    </div>

</div>

<div class="container mt-3">
    <a href="{{ route('propuestas.trazabilidad', $factura->propuesta_id) }}"
        class="btn btn-sm btn-warning">
         ← Regresar a Trazabilidad
     </a>
</div>

</body>
</html>
@endsection