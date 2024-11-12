<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\MateriaPrima;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['materiasPrimas'])->get();
        $materiasPrimas = MateriaPrima::all();

        return Inertia::render('Modules/Productos', [
            'productos' => $productos,
            'materiasPrimas' => $materiasPrimas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'cantidadDisponible' => 'required|integer|min:0',
        ]);

        Producto::create($validated);

        return redirect()->back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'cantidadDisponible' => 'required|integer|min:0',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->update($request->all());

        return redirect()->route('productos')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        Producto::destroy($id);

        return redirect()->route('productos')->with('success', 'Producto eliminado exitosamente.');
    }

    public function agregarMateriaPrima(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'materia_prima_id' => 'required|exists:materia_primas,id',
            'cantidad_por_unidad' => 'required|numeric|min:0',
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        $producto->materiasPrimas()->attach($validated['materia_prima_id'], ['cantidad_por_unidad' => $validated['cantidad_por_unidad']]);

        return redirect()->back()->with('success', 'Materia prima agregada exitosamente.');
    }

    public function eliminarMateriaPrima(Producto $producto, MateriaPrima $materiaPrima)
    {
        $producto->materiasPrimas()->detach($materiaPrima->id);

        return redirect()->back()->with('success', 'Materia prima eliminada exitosamente.');
    }
}