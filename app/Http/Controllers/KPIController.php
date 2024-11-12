<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\MateriaPrima;
use App\Models\Compra;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    public function getKPIData(Request $request)
    {
        $periodo = $request->input('periodo', 'mensual');
        $fechaInicio = $request->input('fecha_inicio') ? Carbon::parse($request->input('fecha_inicio')) : Carbon::now()->startOfMonth();
        $fechaFin = $request->input('fecha_fin') ? Carbon::parse($request->input('fecha_fin')) : Carbon::now();

        $ventasTotales = $this->calcularVentasTotales($fechaInicio, $fechaFin);
        $crecimientoVentas = $this->calcularCrecimientoVentas($fechaInicio, $fechaFin);
        $ticketPromedio = $this->calcularTicketPromedio($fechaInicio, $fechaFin);
        $productosMasVendidos = $this->obtenerProductosMasVendidos($fechaInicio, $fechaFin);

        $clientesActivos = $this->contarClientesActivos($fechaInicio, $fechaFin);
        $clientesInactivos = $this->contarClientesInactivos($fechaInicio, $fechaFin);
        $frecuenciaCompra = $this->calcularFrecuenciaCompra($fechaInicio, $fechaFin);
        $retencionClientes = $this->calcularRetencionClientes($fechaInicio, $fechaFin);

        $rotacionInventario = $this->calcularRotacionInventario($fechaInicio, $fechaFin);
        $alertasStockBajo = $this->obtenerAlertasStockBajo();
        $diasInventarioRestante = $this->calcularDiasInventarioRestante();

        $ingresosBrutos = $ventasTotales;
        $ingresosNetos = $this->calcularIngresosNetos($fechaInicio, $fechaFin);
        $margenesGanancia = $this->calcularMargenesGanancia($fechaInicio, $fechaFin);
        $gastosMateriaPrima = $this->calcularGastosMateriaPrima($fechaInicio, $fechaFin);

        $tiempoPromedioVentas = $this->calcularTiempoPromedioVentas($fechaInicio, $fechaFin);
        $tiempoReposicionInventario = $this->calcularTiempoReposicionInventario($fechaInicio, $fechaFin);
        $eficienciaUsuarios = $this->calcularEficienciaUsuarios($fechaInicio, $fechaFin);

        $ventasPorMes = $this->obtenerVentasPorMes($fechaInicio, $fechaFin);

        return response()->json([
            'ventasTotales' => $ventasTotales,
            'crecimientoVentas' => $crecimientoVentas,
            'ticketPromedio' => $ticketPromedio,
            'productosMasVendidos' => $productosMasVendidos,
            'clientesActivos' => $clientesActivos,
            'clientesInactivos' => $clientesInactivos,
            'frecuenciaCompra' => $frecuenciaCompra,
            'retencionClientes' => $retencionClientes,
            'rotacionInventario' => $rotacionInventario,
            'alertasStockBajo' => $alertasStockBajo,
            'diasInventarioRestante' => $diasInventarioRestante,
            'ingresosBrutos' => $ingresosBrutos,
            'ingresosNetos' => $ingresosNetos,
            'margenesGanancia' => $margenesGanancia,
            'gastosMateriaPrima' => $gastosMateriaPrima,
            'tiempoPromedioVentas' => $tiempoPromedioVentas,
            'tiempoReposicionInventario' => $tiempoReposicionInventario,
            'eficienciaUsuarios' => $eficienciaUsuarios,
            'ventasPorMes' => $ventasPorMes,
        ]);
    }

    private function calcularVentasTotales($fechaInicio, $fechaFin)
    {
        return Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('total');
    }

    private function calcularCrecimientoVentas($fechaInicio, $fechaFin)
    {
        $ventasActuales = $this->calcularVentasTotales($fechaInicio, $fechaFin);
        $periodoAnterior = [
            $fechaInicio->copy()->subMonth(),
            $fechaFin->copy()->subMonth()
        ];
        $ventasAnteriores = $this->calcularVentasTotales($periodoAnterior[0], $periodoAnterior[1]);

        if ($ventasAnteriores == 0) {
            return 100;
        }

        return (($ventasActuales - $ventasAnteriores) / $ventasAnteriores) * 100;
    }

    private function calcularTicketPromedio($fechaInicio, $fechaFin)
    {
        $ventasTotales = $this->calcularVentasTotales($fechaInicio, $fechaFin);
        $numeroTransacciones = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])->count();

        if ($numeroTransacciones == 0) {
            return 0;
        }

        return $ventasTotales / $numeroTransacciones;
    }

    private function obtenerProductosMasVendidos($fechaInicio, $fechaFin)
    {
        return DB::table('productos')
            ->join('venta_producto', 'productos.id', '=', 'venta_producto.idProducto')
            ->join('ventas', 'ventas.id', '=', 'venta_producto.idventa')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->select('productos.nombre', DB::raw('SUM(venta_producto.cantidad) as cantidadVendida'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidadVendida')
            ->limit(5)
            ->get();
    }

    private function contarClientesActivos($fechaInicio, $fechaFin)
    {
        return Cliente::whereHas('ventas', function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->count();
    }

    private function contarClientesInactivos($fechaInicio, $fechaFin)
    {
        $totalClientes = Cliente::count();
        $clientesActivos = $this->contarClientesActivos($fechaInicio, $fechaFin);
        return $totalClientes - $clientesActivos;
    }

    private function calcularFrecuenciaCompra($fechaInicio, $fechaFin)
    {
        $diasPeriodo = $fechaFin->diffInDays($fechaInicio) + 1;
        $totalVentas = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])->count();
        $clientesActivos = $this->contarClientesActivos($fechaInicio, $fechaFin);

        if ($clientesActivos == 0) {
            return 0;
        }

        return $diasPeriodo / ($totalVentas / $clientesActivos);
    }

    private function calcularRetencionClientes($fechaInicio, $fechaFin)
    {
        $clientesPeriodoAnterior = Cliente::whereHas('ventas', function ($query) use ($fechaInicio) {
            $query->where('fecha', '<', $fechaInicio);
        })->pluck('id');

        $clientesRetenidos = Cliente::whereIn('id', $clientesPeriodoAnterior)
            ->whereHas('ventas', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            })->count();

        if ($clientesPeriodoAnterior->count() == 0) {
            return 0;
        }

        return ($clientesRetenidos / $clientesPeriodoAnterior->count()) * 100;
    }

    private function calcularRotacionInventario($fechaInicio, $fechaFin)
    {
        $costoVentas = DB::table('venta_producto')
            ->join('ventas', 'venta_producto.idventa', '=', 'ventas.id')
            ->join('productos', 'venta_producto.idProducto', '=', 'productos.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->sum(DB::raw('venta_producto.cantidad * productos.precio'));

        $inventarioPromedio = Producto::avg('cantidadDisponible') * Producto::avg('precio');

        if ($inventarioPromedio == 0) {
            return 0;
        }

        return $costoVentas / $inventarioPromedio;
    }

    private function obtenerAlertasStockBajo()
    {
        return Producto::whereRaw('cantidadDisponible <= stock_minimo')->count();
    }

    private function calcularDiasInventarioRestante()
    {
        $ventasDiariasPromedio = Venta::where('fecha', '>=', Carbon::now()->subDays(30))
            ->avg('total');

        $inventarioTotal = Producto::sum(DB::raw('cantidadDisponible * precio'));

        if ($ventasDiariasPromedio == 0) {
            return 0;
        }

        return $inventarioTotal / $ventasDiariasPromedio;
    }

    private function calcularIngresosNetos($fechaInicio, $fechaFin)
    {
        $ingresosBrutos = $this->calcularVentasTotales($fechaInicio, $fechaFin);
        $costoVentas = DB::table('venta_producto')
            ->join('ventas', 'venta_producto.idventa', '=', 'ventas.id')
            ->join('productos', 'venta_producto.idProducto', '=', 'productos.id')
            ->join('producto_materia_prima', 'productos.id', '=', 'producto_materia_prima.idProducto')
            ->join('materia_primas', 'producto_materia_prima.idmateriaPrima', '=', 'materia_primas.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->sum(DB::raw('venta_producto.cantidad * producto_materia_prima.cantidad_por_unidad * materia_primas.costoUnitario'));

        return $ingresosBrutos - $costoVentas;
    }

    private function calcularMargenesGanancia($fechaInicio, $fechaFin)
    {
        $ingresosBrutos = $this->calcularVentasTotales($fechaInicio, $fechaFin);
        $ingresosNetos = $this->calcularIngresosNetos($fechaInicio, $fechaFin);

        if ($ingresosBrutos == 0) {
            return 0;
        }

        return ($ingresosNetos / $ingresosBrutos) * 100;
    }

    private function calcularGastosMateriaPrima($fechaInicio, $fechaFin)
    {
        return Compra::where('tipoTransaccion', 'compra')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }

    private function calcularTiempoPromedioVentas($fechaInicio, $fechaFin)
    {
        return DB::table('estado_venta_venta')
            ->join('ventas', 'estado_venta_venta.idventa', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->where('estado_venta_id', function ($query) {
                $query->select('id')
                    ->from('estados_ventas')
                    ->where('nombre', 'Completada')
                    ->limit(1);
            })
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, ventas.created_at, estado_venta_venta.created_at)'));
    }

    private function calcularTiempoReposicionInventario($fechaInicio, $fechaFin)
    {
        // Esta es una implementación simplificada. Puede necesitar ajustes según tus necesidades específicas.
        return Compra::where('tipoTransaccion', 'compra')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->avg(DB::raw('DATEDIFF(fecha, created_at)'));
    }

    private function calcularEficienciaUsuarios($fechaInicio, $fechaFin)
    {
        $ventasPorUsuario = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->groupBy('usuario_id')
            ->selectRaw('usuario_id, COUNT(*) as total_ventas')
            ->get();

        $totalUsuarios = User::count();

        if ($totalUsuarios == 0) {
            return 0;
        }

        return $ventasPorUsuario->avg('total_ventas');
    }

    private function obtenerVentasPorMes($fechaInicio, $fechaFin)
    {
        return Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('YEAR(fecha) as año, MONTH(fecha) as mes, SUM(total) as total')
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();
    }
}
