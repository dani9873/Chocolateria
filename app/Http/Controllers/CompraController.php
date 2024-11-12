<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\MateriaPrima;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with(['usuario', 'materiaPrima'])->get();
        $materiasPrimas = MateriaPrima::all();
        $usuarios = User::all();
        $categorias = Compra::distinct('categoria')->pluck('categoria');

        return Inertia::render('Modules/Compras', [
            'compras' => $compras,
            'materiasPrimas' => $materiasPrimas,
            'usuarios' => $usuarios,
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipoTransaccion' => 'required|in:compra,ajuste',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'categoria' => 'required|string',
            'usuario_id' => 'required|exists:users,id',
            'materia_prima_id' => 'required|exists:materia_primas,id',
            'cantidad' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $compra = Compra::create($validated);

            $materiaPrima = MateriaPrima::findOrFail($validated['materia_prima_id']);
            $materiaPrima->cantidadDisponible += $validated['cantidad'];
            $materiaPrima->save();

            // Check if inventory exceeds safe limit
            $safeLimit = $materiaPrima->stock_minimo * 2; // Example: twice the minimum stock
            if ($materiaPrima->cantidadDisponible > $safeLimit) {
                // Send notification or alert
                // You can implement this using Laravel's notification system
            }
        });

        return redirect()->back()->with('success', 'Compra registrada exitosamente.');
    }

    public function update(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'tipoTransaccion' => 'required|in:compra,ajuste',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'categoria' => 'required|string',
            'usuario_id' => 'required|exists:users,id',
            'materia_prima_id' => 'required|exists:materia_primas,id',
            'cantidad' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($compra, $validated) {
            // Revert the previous quantity
            $materiaPrima = MateriaPrima::findOrFail($compra->materia_prima_id);
            $materiaPrima->cantidadDisponible -= $compra->cantidad;
            
            // Update the purchase
            $compra->update($validated);

            // Add the new quantity
            $materiaPrima->cantidadDisponible += $validated['cantidad'];
            $materiaPrima->save();

            // Check if inventory exceeds safe limit
            $safeLimit = $materiaPrima->stock_minimo * 2; // Example: twice the minimum stock
            if ($materiaPrima->cantidadDisponible > $safeLimit) {
                // Send notification or alert
                // You can implement this using Laravel's notification system
            }
        });

        return redirect()->back()->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy(Compra $compra)
    {
        DB::transaction(function () use ($compra) {
            $materiaPrima = MateriaPrima::findOrFail($compra->materia_prima_id);
            $materiaPrima->cantidadDisponible -= $compra->cantidad;
            $materiaPrima->save();

            $compra->delete();
        });

        return redirect()->back()->with('success', 'Compra eliminada exitosamente.');
    }
}