<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrazabilidadController;
use App\Http\Controllers\PropuestaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AdminInventarioController;
use App\Http\Controllers\AdminUsuariosController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\FacturaController;

Route::get('/', function () {
    return view('home');
})->middleware('auth')->name('home');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout (solo si está logueado)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/perfil', function () {
        return view('perfil');
    })->name('perfil');
    //Crear la propuesta
    Route::get('/propuestas/create', [PropuestaController::class, 'create'])->name('propuestas.create');

    //Ver la lista de propuestas
    Route::post('/propuestas/store', [PropuestaController::class, 'store'])->name('propuestas.store');
    Route::get('/propuestas/cotizacion', [PropuestaController::class, 'cotizacion'])->name('propuestas.cotizacion');

    //Ver detalle
    Route::get('/propuestas/{codigo}/detalle', [PropuestaController::class, 'detalle'])
        ->name('propuestas.detalle');
    
    //Eliminar detalle
    Route::delete('/propuestas/detalle/{id}', [PropuestaController::class, 'eliminarDetalle'])
    ->name('propuestas.detalle.eliminar');

    // Editar una propuesta
    Route::get('/propuestas/{codigo}/edit', [PropuestaController::class, 'edit'])
        ->name('propuestas.edit');
        
    //Actualizar la propuesta
    Route::put('/propuestas/{codigo}', [PropuestaController::class, 'update'])->name('propuestas.update');

    //Eliminar la propuesta
    Route::delete('/propuestas/{codigo}', [PropuestaController::class, 'destroy'])->name('propuestas.destroy');

    //Aprobar la propuesta
    Route::post('/propuestas/{codigo}/aprobar', [PropuestaController::class, 'aprobar'])->name('propuestas.aprobar');

    //Ver la Trazabilidad
    Route::get('/trazabilidad/{codigo}', [TrazabilidadController::class, 'verTrazabilidad'])
    ->name('propuestas.trazabilidad');

    Route::post('/propuestas/{codigo}/asignar-taller', [TrazabilidadController::class, 'asignarTaller'])
    ->name('propuestas.asignarTaller');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('propuestas.dashboard');

    //Revisión trazabilidad
    Route::post('/propuestas/{codigo}/revisionTecnica', [TrazabilidadController::class, 'revisionTecnica'])
    ->name('propuestas.revisionTecnica');

    //Corte
    Route::post('/propuestas/{codigo}/corteRealizado', 
    [TrazabilidadController::class, 'corteRealizado']
    )->name('propuestas.corteRealizado');

    //Confección
    Route::post('/propuestas/{codigo}/confeccionRealizada', 
    [TrazabilidadController::class, 'confeccionRealizada']
    )->name('propuestas.confeccionRealizada');

    //Bordado
    Route::post('/propuestas/{codigo}/bordadoRealizado', 
    [TrazabilidadController::class, 'bordadoRealizado']
    )->name('propuestas.bordadoRealizado');

    Route::post('/propuestas/{codigo}/bordado-no-aplica',
    [TrazabilidadController::class, 'bordadoNoAplica']
    )->name('propuestas.bordadoNoAplica');

    //Control de calidad
    Route::post('/propuestas/{codigo}/ControlRealizado', 
    [TrazabilidadController::class, 'ControlRealizado']
    )->name('propuestas.ControlRealizado');

    //Entrega realizada
    Route::post('/propuestas/{codigo}/EntregaRealizada', 
    [TrazabilidadController::class, 'EntregaRealizada']
    )->name('propuestas.EntregaRealizada');

    //Facturación
    Route::get('/propuestas/{codigo}/facturar', [FacturaController::class, 'facturar'])
    ->name('propuestas.facturar');

    //Eliminar trazabilidad
    Route::delete('/propuestas/{codigo}/borrar-paso/{paso}', 
    [TrazabilidadController::class, 'borrarPaso']
    )->name('propuestas.borrarPaso');

    Route::get('/facturas/{id}', [FacturaController::class, 'ver'])->name('factura.ver');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {

    
    // Usuarios
    Route::get('/admin/usuarios', [AdminUsuariosController::class, 'index'])
    ->name('admin.usuarios');
    
    //Crear usuario
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');

    //Elimianar usuarios
    Route::delete('/admin/usuarios/{id}', [AdminUsuariosController::class, 'destroy'])
    ->name('admin.usuarios.destroy');
    
    // Formulario de importación
    Route::get('/admin/inventario', [AdminInventarioController::class, 'index'])->name('admin.inventario');

    // Subida de Excel
    Route::post('/admin/inventario/importar', [AdminInventarioController::class, 'importar'])->name('admin.inventario.importar');

    // Descargar plantilla
    Route::get('/admin/inventario/plantilla', [AdminInventarioController::class, 'plantilla'])->name('admin.inventario.plantilla');

    Route::get('bodega', [BodegaController::class, 'index'])->name('admin.bodega.index');

    // Productos
    Route::post('bodega/producto', [BodegaController::class, 'store'])->name('admin.bodega.store');
    Route::put('bodega/producto/{id}', [BodegaController::class, 'update'])->name('admin.bodega.update');
    Route::delete('bodega/producto/{id}', [BodegaController::class, 'destroy'])->name('admin.bodega.destroy');

    // Movimientos (entrada/salida)
    Route::post('bodega/entrada', [BodegaController::class, 'entrada'])->name('admin.bodega.entrada');
    Route::post('bodega/salida', [BodegaController::class, 'salida'])->name('admin.bodega.salida');

    // Ordenes de compra (resource minimal)
    Route::resource('ordenescompra', OrdenCompraController::class)->names('admin.ordenescompra');

    Route::post('ordenescompra/{id}/confirmar', [OrdenCompraController::class, 'confirmar'])
    ->name('admin.ordenescompra.confirmar');

    Route::delete('/admin/ordenes/{id}', [OrdenCompraController::class, 'destroy'])
    ->name('ordenes.destroy');

    Route::get('/admin/ordenescompra/{id}', 
        [OrdenCompraController::class, 'show']
    )->name('admin.ordenescompra.show');


});



