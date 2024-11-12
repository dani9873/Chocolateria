<?php

namespace App\Http\Controllers;

use App\Models\ProductoMateriaPrima;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoMateriaPrimaController extends Controller
{
    public function index()
    {
        $productosMateriasPrimas = ProductoMateriaPrima::all();
        return Inertia::render('Modules/ProductosMateriasPrimas', ['productosMateriasPrimas' => $productosMateriasPrimas]);
    }

    public function show($id)
    {
        $productoMateriaPrima = ProductoMateriaPrima::find($id);
        return Inertia::render('Modules/ProductosMateriasPrimas', ['productoMateriaPrima' => $productoMateriaPrima]);
    }

    public function store(Request $request)
    {
        $productoMateriaPrima = ProductoMateriaPrima::create($request->all());
        return redirect()->route('productosMateriasPrimas')->with('success', 'Producto Materia Prima creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $productoMateriaPrima = ProductoMateriaPrima::findOrFail($id);
        $productoMateriaPrima->update($request->all());
        return redirect()->route('productosMateriasPrimas')->with('success', 'Producto Materia Prima actualizado exitosamente');
    }

    public function destroy($id)
    {
        ProductoMateriaPrima::destroy($id);
        return redirect()->route('productosMateriasPrimas')->with('success', 'Producto Materia Prima eliminado exitosamente');
    }
}
