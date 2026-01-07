<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario;
use Maatwebsite\Excel\Facades\Excel;

class AdminInventarioController extends Controller
{
    // Mostrar formulario
    public function index()
    {
        return view('admin.inventario');
    }

    public function plantilla()
    {
        $path = public_path('plantillas/plantillaProductos.csv');
        return response()->download($path, 'plantillaProductos.csv');
    }

    // Importar Excel
    public function importar(Request $request)
{
    $request->validate([
        'archivo' => 'required|mimes:csv,txt'
    ]);

    $path = $request->file('archivo')->getRealPath();
    $file = fopen($path, 'r');

    $first = true;

    while (($row = fgetcsv($file, 1000, ',')) !== false) {

        // Saltar encabezado
        if ($first) { 
            $first = false; 
            continue; 
        }

        Inventario::updateOrCreate(
            ['codigo' => $row[0]],
            [
                'nombre' => $row[1],
                'descripcion' => $row[2],
                'categoria' => $row[3],
                'precio' => $row[4],
                'IVA' => $row[5],
                'stock' => $row[6],
            ]
        );
    }

    fclose($file);

    return back()->with('success', 'Inventario cargado correctamente.');
}

}
