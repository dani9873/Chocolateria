<?php

namespace App\Http\Controllers;

use App\Models\MateriaPrima;
use App\Models\Producto;
use App\Models\Compra;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MateriaPrimaController extends Controller
{
    public function index()
    {
        $materiasPrimas = MateriaPrima::with(['productos'])->get();
        $productos = Producto::all();

        return Inertia::render('Modules/MateriasPrimas', [
            'materiasPrimas' => $materiasPrimas,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cantidadDisponible' => 'required|numeric|min:0',
            'unidadMedida' => 'required|string|max:50',
            'costoUnitario' => 'required|numeric|min:0',
        ]);

        MateriaPrima::create($validated);

        return redirect()->back()->with('success', 'Materia prima creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'cantidadDisponible' => 'required|numeric|min:0',
            'unidadMedida' => 'required|string|max:50',
            'costoUnitario' => 'required|numeric|min:0',
        ]);

        $materiaPrima = MateriaPrima::findOrFail($id);
        $materiaPrima->update($request->all());

        return redirect()->route('materiasPrimas.index')->with('success', 'Materia prima actualizada exitosamente.');
    }

    public function destroy($id)
    {
        MateriaPrima::destroy($id);

        return redirect()->route('materiasPrimas.index')->with('success', 'Materia prima eliminada exitosamente.');
    }

    public function updateInventory(Request $request)
    {
        $validated = $request->validate([
            'materia_prima_id' => 'required|exists:materia_primas,id',
            'cantidad' => 'required|numeric',
            'tipo' => 'required|in:incremento,decremento',
        ]);

        $materiaPrima = MateriaPrima::findOrFail($validated['materia_prima_id']);

        if ($validated['tipo'] === 'incremento') {
            $materiaPrima->cantidadDisponible += $validated['cantidad'];
        } else {
            $materiaPrima->cantidadDisponible = max(0, $materiaPrima->cantidadDisponible - $validated['cantidad']);
        }

        $materiaPrima->save();

        Compra::create([
            'tipoTransaccion' => $validated['tipo'],
            'monto' => $validated['cantidad'] * $materiaPrima->costoUnitario,
            'descripcion' => "Ajuste de inventario: {$validated['tipo']} de {$validated['cantidad']} {$materiaPrima->unidadMedida}",
            'fecha' => now(),
            'categoria' => 'Inventario',
            'usuario_id' => Auth::id(),
            'materia_prima_id' => $materiaPrima->id,
        ]);

        return redirect()->back()->with('success', 'Inventario actualizado exitosamente.');
    }

    public function addProductRelation(Request $request)
    {
        $validated = $request->validate([
            'materia_prima_id' => 'required|exists:materia_primas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_por_unidad' => 'required|numeric|min:0',
        ]);

        $materiaPrima = MateriaPrima::findOrFail($validated['materia_prima_id']);
        $producto = Producto::findOrFail($validated['producto_id']);

        $materiaPrima->productos()->syncWithoutDetaching([
            $producto->id => ['cantidad_por_unidad' => $validated['cantidad_por_unidad']]
        ]);

        return redirect()->back()->with('success', 'Relación con producto agregada exitosamente.');
    }

    public function removeProductRelation(MateriaPrima $materiaPrima, Producto $producto)
    {
        $materiaPrima->productos()->detach($producto->id);

        return redirect()->back()->with('success', 'Relación con producto eliminada exitosamente.');
    }
}