<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaProducto extends Model
{
    use HasFactory;
    protected $table = 'venta_producto';

    protected $fillable = [
        'idventa', 'idProducto', 'cantidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idventa');
    }
}
