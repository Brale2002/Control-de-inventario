<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'bodega';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria',
        'precio',
        'IVA',
        'stock',
    ];
}
