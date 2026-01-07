<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'direccion',
        'contacto',
        'email',
        'creacion',
        'actualizacion'
    ];

    public $timestamps = false; // si las columnas son 'creacion' y 'actualizacion' en vez de 'created_at' y 'updated_at'
}
