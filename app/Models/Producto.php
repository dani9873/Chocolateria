<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'producto';

    protected $fillable = [
        'nombre', 'categoria', 'precio', 'cantidadDisponible', 'stock_minimo',
    ];

    public function ventasProducto()
    {
        return $this->hasMany(VentaProducto::class, 'idProducto');
    }

    public function materiaPrima()
    {
        return $this->hasMany(ProductoMateriaPrima::class, 'idProducto');
    }
}
