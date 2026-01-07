<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla
    protected $table = 'bodega';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'codigo',
        'talla',
        'nombre',
        'descripcion',
        'categoria',
        'precio',
        'iva',
        'stock',
    ];
}
