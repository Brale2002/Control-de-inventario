<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propuesta;
use App\Models\Factura;
use App\Models\TrazabilidadPedido;
use Illuminate\Support\Facades\Auth;


class TrazabilidadController extends Controller
{
    //Ver trazabilidad de una propuesta
    public function vertrazabilidad($codigo)
    {
        $propuesta = Propuesta::where('codigo', $codigo)->firstOrFail();
        $trazabilidad = TrazabilidadPedido::where('codigo', $codigo)
                            ->orderBy('paso', 'asc')
                            ->get();

        $pasosTotales = [
            1 => 'Cotización creada',
            2 => 'Orden de Pedido generada',
            3 => 'Asignación de Taller',
            4 => 'Revisión previa',
            5 => 'Corte',
            6 => 'Confección',
            7 => 'Bordado / Estampado',
            8 => 'Control de Calidad Final',
            9 => 'Despacho y Entrega',
            10 => 'Facturación'
        ];

        $ultimoPaso = $trazabilidad->max('paso');

        //Si paso 7 fue marcado como NO APLICA
        $paso7NoAplica = session()->get("paso7_no_aplica_{$codigo}", false);

        //Calcular el siguiente paso normalmente
        $siguientePaso = ($ultimoPaso ?? 0) + 1;

        //Saltar paso 7 si es no aplica
        if ($siguientePaso == 7 && $paso7NoAplica) {
            $siguientePaso = 8;
        }

        if (isset($pasosTotales[$siguientePaso])) {
            $trazabilidad->push((object)[
                'paso' => $siguientePaso,
                'nombre_paso' => $pasosTotales[$siguientePaso],
                'descripcion' => 'Pendiente de ejecución...',
                'estado' => 'pendiente',
                'fecha_inicio' => '-',
                'fecha_fin' => '-',
                'responsable' => Auth::user()->nombre ?? 'Sistema'
            ]);
        }

        return view('propuestas.trazabilidad', compact('propuesta', 'trazabilidad'));
    }

//Asignar Taller
    public function asignarTaller(Request $request, $codigo)
{
    $request->validate([
        'taller' => 'required|integer|min:1|max:5',
    ]);

    $propuesta = Propuesta::where('codigo', $codigo)->firstOrFail();

    $existe = TrazabilidadPedido::where('codigo', $codigo)
    ->where('paso', 3)
    ->exists();

    if ($existe) {
        return redirect()
            ->back()
            ->with('warning', 'Ya existe una trazabilidad para el paso 3 (Asignación de Taller).');
    }
    
    $propuesta->trazabilidad()->create([
        'paso' => 3,
        'nombre_paso' => 'Asignación de Taller',
        'descripcion' => 'La orden de pedido fue asignada al taller ' . $request->taller,
        'estado' => 'completado',
        'fecha_inicio' => now(),
        'fecha_fin' => now(),
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => $request->taller,
    ]);

    return redirect()
        ->route('propuestas.trazabilidad', $codigo)
        ->with('success', 'El pedido fue asignado correctamente al taller ' . $request->taller);
}

//Revisión técnica
    public function revisionTecnica(Request $request, $codigo)
{
    $request->validate([
        'observaciones_tecnicas' => 'required|min:5',
        'estado_revision' => 'required',
    ],[
        'observaciones_tecnicas.required' => 'Debes ingresar observaciones técnicas.',
        'estado_revision.required' => 'Debes seleccionar el estado de revisión.',
    ]);

    // Si intenta aprobar pero hay datos faltantes, lo bloqueamos
    if ($request->estado_revision == 'completado') {

        // 1. Moldes y ficha técnica
        if (!$request->has('moldes_listos') || !$request->has('ficha_tecnica_ok')) {
            return back()->withErrors(['Debes validar moldes y ficha técnica antes de aprobar.']);
        }

        // 2. Validar insumos según detalle de cotización
        $propuesta = Propuesta::where('codigo', $codigo)->first();

        if (!$propuesta->detalles || $propuesta->detalles->count() == 0) {
            return back()->withErrors(['No hay detalles de la cotización para validar insumos.']);
        }

        foreach ($propuesta->detalles as $detalle) {

            // Debe marcarse el checkbox de validado
            if (!isset($request->validado[$detalle->id])) {
                return back()->withErrors(['Debes validar todos los productos del detalle antes de aprobar.']);
            }

            // Debe existir stock suficiente en bodega
            $stock = $detalle->bodega->stock ?? 0;

            if ($stock < $detalle->cantidad) {
                return back()->withErrors([
                    'El producto "' . ($detalle->bodega->nombre ?? 'Producto') .
                    '" no tiene stock suficiente. Se requiere ' . $detalle->cantidad .
                    ' y solo hay ' . $stock . '.'
                ]);
            }
            
            if (
                empty($request->observaciones_tecnicas) ||
                !$request->has('moldes_listos') ||
                !$request->has('ficha_tecnica_ok') ||
                empty($request->estado_revision)
            ) {
                return back()->withErrors(['Debe llenar el formulario completo.'])->withInput();
            }
        }
    }

    // Cargar propuesta con detalles y relaciones necesarias
    $propuesta = Propuesta::with('detalles.bodega')->where('codigo', $codigo)->firstOrFail();

    //Procesar los detalles
    foreach ($propuesta->detalles as $detalle) {
        $detalleId = $detalle->id; // o 'codigo' si ese es el campo clave
        $validado = isset($request->validado[$detalleId]) && $request->validado[$detalleId] == 1;

        // Si está validado, descontar stock
        if ($validado && $detalle->bodega) {
            $bodega = $detalle->bodega;
            $cantidadRequerida = $detalle->cantidad ?? 0;

            // Verifica que haya stock suficiente
            if ($bodega->stock >= $cantidadRequerida) {
                $bodega->stock -= $cantidadRequerida;
            } else {
                // Si no hay suficiente, se deja en cero (o puedes decidir rechazar)
                $bodega->stock = 0;
            }

            $bodega->save(); // Guardar actualización
        }
    }
    $taller = TrazabilidadPedido::where('codigo', $codigo)
    ->where('paso', 3)
    
    ->value('TallerAsignado');
    // Construir array de insumos tomando la info real de cada detalle
    $insumos = [];
    foreach ($propuesta->detalles as $detalle) {
        $detalleId = $detalle->codigo;
        $disponible = $detalle->bodega->stock ?? null;
        $insumos[] = [
            'codigo' => $detalleId,
            'producto' => $detalle->nombre ?? ($detalle->bodega->nombre ?? 'Sin referencia'),
            'cantidad_requerida' => $detalle->cantidad,
            'cantidad_disponible' => $disponible,
            'validado' => isset($request->validado[$detalleId]) && $request->validado[$detalleId] == 1 ? true : false,
        ];
    }

    // 2. Crear el paso 5 si fue completado
    if ($request->estado_revision == 'completado') {
        TrazabilidadPedido::create([
            'codigo' => $codigo,
            'paso' => 4,
            'nombre_paso' => 'Revisión previa',
            'descripcion' => 'Revisión técnica aprobada',
            'estado' => $request->estado_revision,
            'fecha_inicio' => now(),
            'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
            'responsable' => Auth::user()->nombre ?? 'Sistema',
            'TallerAsignado' => $taller,
        ]);
    }

    return back()->with('success', 'Revisión técnica registrada.');
}

//Crear paso 5 (corte)
public function corteRealizado(Request $request, $codigo)
{
    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 5,
        'nombre_paso' => 'Corte',
        'descripcion' => 'Corte finalizado',
        'estado' => $request->estado_revision,
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    return back()->with('success', 'Corte registrado correctamente.');
}

// crear paso 6 (confección)
public function confeccionRealizada(Request $request, $codigo)
{
    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 6,
        'nombre_paso' => 'Confección',
        'descripcion' => 'Confección finalizada',
        'estado' => $request->estado_revision,
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    return back()->with('success', 'Confección registrada correctamente.');
}

// crear paso 7 (Bordado/Estampado)
public function bordadoRealizado(Request $request, $codigo)
{
    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 7,
        'nombre_paso' => 'Bordado/Estampado',
        'descripcion' => 'Paso finalizado',
        'estado' => $request->estado_revision,
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    return back()->with('success', 'Bordado / Estampado registradp correctamente.');
}

public function bordadoNoAplica($codigo)
{
    //Marcar en sesión que el paso 7 no aplica
    session()->put("paso7_no_aplica_{$codigo}", true);

    return back()->with('success', 'El paso de Bordado / Estampado fue marcado como NO APLICA.');
}

// crear paso 8 (Calidad)
public function ControlRealizado(Request $request, $codigo)
{
    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 8,
        'nombre_paso' => 'Bordado/Estampado',
        'descripcion' => 'Paso finalizado',
        'estado' => $request->estado_revision,
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    return back()->with('success', 'Calidad correctamente.');
}

// crear paso 9 (Envio)
public function EntregaRealizada(Request $request, $codigo)
{
    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 9,
        'nombre_paso' => 'Despacho y Entrega',
        'descripcion' => 'Paso finalizado',
        'estado' => $request->estado_revision,
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    TrazabilidadPedido::create([
        'codigo' => $codigo,
        'paso' => 10,
        'nombre_paso' => 'Facturado',
        'descripcion' => 'Paso finalizado',
        'estado' => 'finalizado',
        'fecha_inicio' => now(),
        'fecha_fin' =>  $request->estado_revision == 'completado' ? now() : null,
        'responsable' => Auth::user()->nombre ?? 'Sistema',
        'TallerAsignado' => TrazabilidadPedido::where('codigo', $codigo)
                                    ->where('paso', 3)
                                    ->value('TallerAsignado'),
    ]);

    return back()->with('success', 'Despacho y Entrega y facturación correctamente.');
}

public function showFactura($id)
{
    $factura = Factura::with('cliente', 'items')->findOrFail($id);

    return view('facturas.show', compact('factura'));
}


// Elimina todos los pasos desde el seleccionado hacia adelante
public function borrarPaso($codigo, $paso)
{
    if ($paso <= 6) {
        session()->forget("paso7_no_aplica_{$codigo}");
    }

    TrazabilidadPedido::where('codigo', $codigo)
        ->where('paso', '>=', $paso)
        ->delete();

    return back()->with('success', "Paso $paso y los posteriores han sido eliminados.");
}
}
