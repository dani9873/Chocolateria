<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'productos', 'estados', 'usuario'])
            ->orderBy('fecha', 'desc')
            ->get();

        $clientes = Cliente::all();
        $productos = Producto::all();
        $estados = EstadoVenta::all();

        $ventasPorMes = Venta::selectRaw('MONTH(fecha) as mes, SUM(total) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return Inertia::render('Modules/Ventas', [
            'ventas' => $ventas,
            'clientes' => $clientes,
            'productos' => $productos,
            'estados' => $estados,
            'ventasPorMes' => $ventasPorMes,
        ]);
    }
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'productos', 'estados', 'usuario'])->findOrFail($id);

        return Inertia::render('Modules/Ventas', [
            'venta' => $venta,
            'cliente' => $venta->cliente,
            'productos' => $venta->productos,
            'estados' => $venta->estados,
            'total' => $venta->total,
            'fecha' => $venta->fecha,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'estado' => 'required|exists:estados_ventas,id',
        ]);

        try {
            // Crear la venta
            $venta = Venta::create([
                'fecha' => $request->fecha,
                'cliente_id' => $request->cliente_id,
                'usuario_id' => auth()->id(),
                'total' => 0,
            ]);

            $total = 0;
            foreach ($request->productos as $producto) {
                $productoModel = Producto::find($producto['id']);
                $subtotal = $productoModel->precio * $producto['cantidad'];
                $total += $subtotal;

                // Asociar productos a la venta
                $venta->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);

                // Actualizar la cantidad disponible del producto
                $productoModel->cantidadDisponible -= $producto['cantidad'];
                $productoModel->save();
            }

            // Actualizar el total de la venta
            $venta->total = $total;
            $venta->save();

            $venta->estados()->attach($request->estado);

            return redirect()->route('ventas.index')->with('success', 'Venta creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('ventas.index')->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'estado' => 'required|exists:estados_ventas,id',
        ]);

        try {
            $total = 0;

            // Restaurar el stock de los productos anteriores
            foreach ($venta->productos as $producto) {
                $producto->cantidadDisponible += $producto->pivot->cantidad;
                $producto->save();
            }

            // Desasociar los productos anteriores
            $venta->productos()->detach();

            // Asociar los nuevos productos y actualizar el stock
            foreach ($request->productos as $producto) {
                $productoModel = Producto::find($producto['id']);
                $subtotal = $productoModel->precio * $producto['cantidad'];
                $total += $subtotal;

                $venta->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);

                $productoModel->cantidadDisponible -= $producto['cantidad'];
                $productoModel->save();
            }

            // Actualizar la venta
            $venta->update([
                'fecha' => $request->fecha,
                'cliente_id' => $request->cliente_id,
                'total' => $total,
            ]);


            // Actualizar el estado de la venta
            $venta->estados()->sync($request->estado);   

            return redirect()->back()->with('success', 'Venta actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la venta: ' . $e->getMessage());
        }
    }

    public function updateEstado(Request $request,  $id)
    {
         $request->validate([
        'estado' => 'required|exists:estados_ventas,id',
    ]);

    try {
        $venta = Venta::findOrFail($id);

        $venta->estados()->sync($request->estado);  
        
        // Return the updated sale with relations
        $ventaActualizada = Venta::with(['cliente', 'productos', 'estados', 'usuario'])
            ->findOrFail($id);
         

        return response()->json([
            'success' => 'Estado de la venta actualizado exitosamente.',
            'venta' => $ventaActualizada
        ]);} catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el estado de la venta: ' . $e->getMessage()], 500);
        }
    }
    public function destroy(Venta $venta)
    {
        try {
            // Restaurar el stock de los productos
            foreach ($venta->productos as $producto) {
                $producto->cantidadDisponible += $producto->pivot->cantidad;
                $producto->save();
            }

            // Desasociar los productos y eliminar el estado de la venta
            $venta->productos()->detach();
            $venta->estados()->detach();
            $venta->delete();

            return redirect()->back()->with('success', 'Venta eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }
}
