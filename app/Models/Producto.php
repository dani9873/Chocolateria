<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'categoria',
        'precio',
        'stock_minimo',
        'cantidadDisponible',
    ];

    public function materiasPrimas()
    {
        return $this->belongsToMany(MateriaPrima::class, 'producto_materia_prima', 'idProducto', 'idmateriaPrima')
                    ->withPivot('cantidad_por_unidad')
                    ->withTimestamps();
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'venta_producto', 'idProducto', 'idventa')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
}
