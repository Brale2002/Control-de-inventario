<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TallaController;


// Ruta por defecto para probar conexión
Route::get('/ping', function () {
    return response()->json(['message' => 'API activa']);
});

Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::get('/bodega', [BodegaController::class, 'Buscar']);
Route::get('/tallas', [TallaController::class, 'index']);


