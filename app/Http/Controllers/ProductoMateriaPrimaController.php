<?php

namespace App\Http\Controllers;

use App\Models\ProductoMateriaPrima;
use Illuminate\Http\Request;

class ProductoMateriaPrimaController extends Controller
{
    public function index()
    {
        return ProductoMateriaPrima::all();
    }

    public function show($id)
    {
        return ProductoMateriaPrima::find($id);
    }

    public function store(Request $request)
    {
        return ProductoMateriaPrima::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $productoMateriaPrima = ProductoMateriaPrima::findOrFail($id);
        $productoMateriaPrima->update($request->all());
        return $productoMateriaPrima;
    }

    public function destroy($id)
    {
        ProductoMateriaPrima::destroy($id);
        return response()->json(['message' => 'ProductoMateriaPrima eliminado']);
    }
}
