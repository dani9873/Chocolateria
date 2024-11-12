<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Line, Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale, BarElement, ArcElement } from 'chart.js';
import JetButton from '@/Components/PrimaryButton.vue';
import JetInput from '@/Components/TextInput.vue';
import JetLabel from '@/Components/InputLabel.vue';
import JetSelect from '@/Components/Select.vue';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale, BarElement, ArcElement);

const periodoSeleccionado = ref('mensual');
const fechaInicio = ref('');
const fechaFin = ref('');
const kpiData = ref({});

const toNumber = (value) => {
    const number = parseFloat(value);
    return isNaN(number) ? 0 : number;
};

const ventasTotales = computed(() => toNumber(kpiData.value.ventasTotales));
const crecimientoVentas = computed(() => toNumber(kpiData.value.crecimientoVentas));
const ticketPromedio = computed(() => toNumber(kpiData.value.ticketPromedio));
const productosMasVendidos = computed(() => kpiData.value.productosMasVendidos || []);

const clientesActivos = computed(() => toNumber(kpiData.value.clientesActivos));
const clientesInactivos = computed(() => toNumber(kpiData.value.clientesInactivos));
const frecuenciaCompra = computed(() => toNumber(kpiData.value.frecuenciaCompra));
const retencionClientes = computed(() => toNumber(kpiData.value.retencionClientes));

const rotacionInventario = computed(() => toNumber(kpiData.value.rotacionInventario));
const alertasStockBajo = computed(() => toNumber(kpiData.value.alertasStockBajo));
const diasInventarioRestante = computed(() => toNumber(kpiData.value.diasInventarioRestante));

const ingresosBrutos = computed(() => toNumber(kpiData.value.ingresosBrutos));
const ingresosNetos = computed(() => toNumber(kpiData.value.ingresosNetos));
const margenesGanancia = computed(() => toNumber(kpiData.value.margenesGanancia));
const gastosMateriaPrima = computed(() => toNumber(kpiData.value.gastosMateriaPrima));

const tiempoPromedioVentas = computed(() => toNumber(kpiData.value.tiempoPromedioVentas));
const tiempoReposicionInventario = computed(() => toNumber(kpiData.value.tiempoReposicionInventario));
const eficienciaUsuarios = computed(() => toNumber(kpiData.value.eficienciaUsuarios));

const ventasChartData = computed(() => ({
    labels: kpiData.value.ventasPorMes ? kpiData.value.ventasPorMes.map(v => `${v.mes}/${v.año}`) : [],
    datasets: [{
        label: 'Ventas por Mes',
        data: kpiData.value.ventasPorMes ? kpiData.value.ventasPorMes.map(v => v.total) : [],
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1
    }]
}));

const clientesChartData = computed(() => ({
    labels: ['Activos', 'Inactivos'],
    datasets: [{
        data: [clientesActivos.value, clientesInactivos.value],
        backgroundColor: ['#4CAF50', '#F44336']
    }]
}));

const productosMasVendidosChartData = computed(() => ({
    labels: productosMasVendidos.value.map(p => p.nombre),
    datasets: [{
        label: 'Cantidad Vendida',
        data: productosMasVendidos.value.map(p => p.cantidadVendida),
        backgroundColor: 'rgba(54, 162, 235, 0.5)'
    }]
}));

const fetchKPIData = async () => {
    try {
        const response = await axios.get(route('api.kpi-data'), {
            params: {
                periodo: periodoSeleccionado.value,
                fecha_inicio: fechaInicio.value,
                fecha_fin: fechaFin.value
            }
        });
        kpiData.value = response.data;
    } catch (error) {
        console.error('Error fetching KPI data:', error);
    }
};

const actualizarDatos = () => {
    fetchKPIData();
};

const exportarDatos = () => {
    // Implementar la lógica de exportación aquí
};

onMounted(() => {
    fetchKPIData();
});
</script>

<template>
    <AppLayout title="Análisis de KPI y Métricas Financieras">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Análisis de KPI y Métricas Financieras
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <div class="flex space-x-4">
                            <JetLabel for="periodo" value="Período" />
                            <JetSelect id="periodo" v-model="periodoSeleccionado">
                                <option value="diario">Diario</option>
                                <option value="mensual">Mensual</option>
                                <option value="anual">Anual</option>
                            </JetSelect>
                            <JetLabel for="fechaInicio" value="Fecha Inicio" />
                            <JetInput id="fechaInicio" type="date" v-model="fechaInicio" />
                            <JetLabel for="fechaFin" value="Fecha Fin" />
                            <JetInput id="fechaFin" type="date" v-model="fechaFin" />
                        </div>
                        <JetButton @click="actualizarDatos">Actualizar</JetButton>
                    </div>

                    <!-- KPIs de Ventas -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">KPIs de Ventas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Ventas Totales</h4>
                                <p class="text-2xl font-bold">Q{{ ventasTotales.toFixed(2) }}</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Crecimiento de Ventas Mensual</h4>
                                <p class="text-2xl font-bold">{{ crecimientoVentas.toFixed(2) }}%</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Ticket Promedio</h4>
                                <p class="text-2xl font-bold">Q{{ ticketPromedio.toFixed(2) }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h4 class="font-medium mb-2">Productos Más Vendidos</h4>
                            <Bar :data="productosMasVendidosChartData" />
                        </div>
                    </div>

                    <!-- KPIs de Clientes -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">KPIs de Clientes</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium mb-2">Clientes Activos vs. Inactivos</h4>
                                <Pie :data="clientesChartData" />
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Frecuencia de Compra</h4>
                                <p class="text-2xl font-bold">{{ frecuenciaCompra.toFixed(2) }} días</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Retención de Clientes</h4>
                                <p class="text-2xl font-bold">{{ retencionClientes.toFixed(2) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- KPIs de Inventario -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">KPIs de Inventario</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Rotación de Inventario</h4>
                                <p class="text-2xl font-bold">{{ rotacionInventario.toFixed(2) }}</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Alertas de Stock Bajo</h4>
                                <p class="text-2xl font-bold">{{ alertasStockBajo }}</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Días de Inventario Restante</h4>
                                <p class="text-2xl font-bold">{{ diasInventarioRestante.toFixed(0) }} días</p>
                            </div>
                        </div>
                    </div>

                    <!-- Métricas Financieras -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Métricas Financieras</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Ingresos Brutos</h4>
                                <p class="text-2xl font-bold">Q{{ ingresosBrutos.toFixed(2) }}</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Ingresos Netos</h4>
                                <p class="text-2xl font-bold">Q{{ ingresosNetos.toFixed(2) }}</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Márgenes de Ganancia</h4>
                                <p class="text-2xl font-bold">{{ margenesGanancia.toFixed(2) }}%</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Gastos en Materia Prima</h4>
                                <p class="text-2xl font-bold">Q{{ gastosMateriaPrima.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores de Eficiencia Operativa -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Indicadores de Eficiencia Operativa</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Tiempo Promedio de Procesamiento de Ventas</h4>
                                <p class="text-2xl font-bold">{{ tiempoPromedioVentas.toFixed(2) }} horas</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Tiempo de Reposición de Inventario</h4>
                                <p class="text-2xl font-bold">{{ tiempoReposicionInventario.toFixed(2) }} días</p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded">
                                <h4 class="font-medium">Eficiencia de Usuarios</h4>
                                <p class="text-2xl font-bold">{{ eficienciaUsuarios.toFixed(2) }} ventas/usuario</p>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Ventas -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Gráfico de Ventas</h3>
                        <Line :data="ventasChartData" />
                    </div>

                    <div class="mt-4">
                        <JetButton @click="exportarDatos">Exportar Datos</JetButton>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
