<?php

namespace App\Http\Controllers;

use App\Models\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EstadosVentasController extends Controller
{
    public function index()
    {
        $estadosVentas = EstadoVenta::all();
        return Inertia::render('Modules/Ventas', ['estados' => $estadosVentas]);
    }

    public function show($id)
    {
        $estadoVenta = EstadoVenta::find($id);
        return Inertia::render('Modules/Ventas', ['estado' => $estadoVenta]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        EstadoVenta::create($request->all());
        return redirect()->route('estadosVentas')->with('success', 'Estado de Venta creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $estadoVenta = EstadoVenta::findOrFail($id);
        $estadoVenta->update($request->all());
        return redirect()->route('estadosVentas')->with('success', 'Estado de Venta actualizado exitosamente');
    }

    public function destroy($id)
    {
        EstadoVenta::destroy($id);
        return redirect()->route('estadosVentas')->with('success', 'Estado de Venta eliminado exitosamente');
    }
}
