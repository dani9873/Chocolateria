<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPrima extends Model
{
    use HasFactory;
    protected $table = 'materia_prima';

    protected $fillable = [
        'nombre', 'cantidadDisponible', 'unidadMedida', 'costoUnitario',
    ];

    public function productos()
    {
        return $this->hasMany(ProductoMateriaPrima::class, 'idmateriaPrima');
    }
}
