<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TallaController extends Controller
{
    public function index()
    {
        $type = DB::select("SHOW COLUMNS FROM propuesta_detalles WHERE Field = 'talla'")[0]->Type;

        // Extrae los valores ENUM con expresión regular
        preg_match('/enum\((.*)\)/', $type, $matches);
        $enumValues = [];
        if (isset($matches[1])) {
            $enumValues = str_getcsv(str_replace("'", '', $matches[1]));
        }

        return response()->json($enumValues);
    }
}
