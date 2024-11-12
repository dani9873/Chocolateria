<?php

namespace App\Http\Controllers;

use App\Models\EstadoVentaVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EstadoVentaVentaController extends Controller
{
    public function index()
    {
        $estadosVentasVentas = EstadoVentaVenta::all();
        return Inertia::render('Modules/EstadosVentasVentas', ['estadosVentasVentas' => $estadosVentasVentas]);
    }

    public function show($id)
    {
        $estadoVentaVenta = EstadoVentaVenta::find($id);
        return Inertia::render('Modules/EstadosVentasVentas', ['estadoVentaVenta' => $estadoVentaVenta]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idventa' => 'required|exists:ventas,id',
            'estado_venta_id' => 'required|exists:estados_ventas,id',
        ]);

        $estadoVentaVenta = EstadoVentaVenta::create($request->all());
        return redirect()->route('$estadosVentasVentas')->with('success', 'Estado de Venta asociado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'idventa' => 'required|exists:ventas,id',
            'estado_venta_id' => 'required|exists:estados_ventas,id',
        ]);

        $estadoVentaVenta = EstadoVentaVenta::findOrFail($id);
        $estadoVentaVenta->update($request->all());
        return redirect()->route('$estadosVentasVentas')->with('success', 'Estado de Venta actualizado exitosamente');
    }

    public function destroy($id)
    {
        EstadoVentaVenta::destroy($id);
        return redirect()->route('$estadosVentasVentas')->with('success', 'Estado de Venta desasociado exitosamente');
    }
}