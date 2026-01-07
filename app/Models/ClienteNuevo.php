<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteNuevo extends Model
{
    use HasFactory;

    protected $table = 'clientes_nuevos';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'cedula',
        'telefono',
        'direccion',
    ];
}
