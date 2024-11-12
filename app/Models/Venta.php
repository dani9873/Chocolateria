<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas';

    protected $fillable = [
        'fecha',
        'total',
        'cliente_id',
        'usuario_id',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'venta_producto', 'idventa', 'idProducto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function estados()
    {
        return $this->belongsToMany(EstadoVenta::class, 'estado_venta_venta', 'idventa', 'estado_venta_id')
            ->withTimestamps();
    }
}
