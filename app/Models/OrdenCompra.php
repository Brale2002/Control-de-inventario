<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';

    protected $fillable = [
        'proveedor',
        'estado',
        'total',
        'observaciones'
    ];

    public function items()
    {
        return $this->hasMany(OrdenCompraItem::class);
    }
}
