<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import { Bar, Pie, Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, LineElement, PointElement } from 'chart.js';
import DateRangePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, LineElement, PointElement);

// Datos de ejemplo (en una aplicación real, estos vendrían de una API)
const stockData = ref([
  { name: 'Producto A', category: 'Electrónica', minStock: 100, currentStock: 150, status: 'OK' },
  { name: 'Producto B', category: 'Ropa', minStock: 50, currentStock: 30, status: 'Bajo' },
  // ... más datos
]);

const salesData = ref([
  { date: '2023-01-01', customer: 'Cliente 1', product: 'Producto A', quantity: 5, total: 500 },
  { date: '2023-01-02', customer: 'Cliente 2', product: 'Producto B', quantity: 3, total: 300 },
  // ... más datos
]);

const dateRange = ref([new Date(new Date().getFullYear(), 0, 1), new Date()]);
const selectedCustomer = ref('');
const selectedProduct = ref('');

const stockChartData = computed(() => ({
  labels: stockData.value.map(item => item.name),
  datasets: [
    {
      label: 'Stock Actual',
      data: stockData.value.map(item => item.currentStock),
      backgroundColor: 'rgba(75, 192, 192, 0.6)',
    },
    {
      label: 'Stock Mínimo',
      data: stockData.value.map(item => item.minStock),
      backgroundColor: 'rgba(255, 99, 132, 0.6)',
    }
  ]
}));

const salesByCategoryChartData = computed(() => {
  const categorySales = {};
  salesData.value.forEach(sale => {
    const product = stockData.value.find(item => item.name === sale.product);
    if (product) {
      categorySales[product.category] = (categorySales[product.category] || 0) + sale.total;
    }
  });
  return {
    labels: Object.keys(categorySales),
    datasets: [{
      data: Object.values(categorySales),
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
    }]
  };
});

const salesByCustomerChartData = computed(() => {
  const customerSales = {};
  salesData.value.forEach(sale => {
    customerSales[sale.customer] = (customerSales[sale.customer] || 0) + sale.total;
  });
  return {
    labels: Object.keys(customerSales),
    datasets: [{
      label: 'Ventas Totales',
      data: Object.values(customerSales),
      backgroundColor: 'rgba(54, 162, 235, 0.6)',
    }]
  };
});

const filteredSalesData = computed(() => {
  return salesData.value.filter(sale => {
    const saleDate = new Date(sale.date);
    return saleDate >= dateRange.value[0] && saleDate <= dateRange.value[1] &&
           (!selectedCustomer.value || sale.customer === selectedCustomer.value) &&
           (!selectedProduct.value || sale.product === selectedProduct.value);
  });
});

onMounted(() => {
  // Aquí podrías cargar los datos reales desde tu API
});
</script>

<template>
  <AppLayout title="Dashboard de Inteligencia de Negocios">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard de Inteligencia de Negocios
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Filtros -->
          <div class="mb-6 flex space-x-4">
            <DateRangePicker v-model="dateRange" />
            <select v-model="selectedCustomer" class="form-select">
              <option value="">Todos los clientes</option>
              <option v-for="customer in [...new Set(salesData.map(s => s.customer))]" :key="customer">
                {{ customer }}
              </option>
            </select>
            <select v-model="selectedProduct" class="form-select">
              <option value="">Todos los productos</option>
              <option v-for="product in stockData" :key="product.name">
                {{ product.name }}
              </option>
            </select>
          </div>

          <!-- Análisis de Stock -->
          <h3 class="text-lg font-semibold mb-4">Análisis de Stock</h3>
          <div class="mb-6">
            <Bar :data="stockChartData" />
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Categoría</th>
                  <th>Stock Mínimo</th>
                  <th>Stock Actual</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in stockData" :key="item.name">
                  <td>{{ item.name }}</td>
                  <td>{{ item.category }}</td>
                  <td>{{ item.minStock }}</td>
                  <td>{{ item.currentStock }}</td>
                  <td :class="item.status === 'Bajo' ? 'text-red-600' : 'text-green-600'">
                    {{ item.status }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Rendimiento de Ventas -->
          <h3 class="text-lg font-semibold mt-8 mb-4">Rendimiento de Ventas</h3>
          <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
              <h4 class="text-md font-medium mb-2">Ventas por Categoría</h4>
              <Pie :data="salesByCategoryChartData" />
            </div>
            <div>
              <h4 class="text-md font-medium mb-2">Ventas por Cliente</h4>
              <Bar :data="salesByCustomerChartData" />
            </div>
          </div>
          <div class="mb-6">
            <h4 class="text-md font-medium mb-2">Evolución de Ventas</h4>
            <Line :data="salesEvolutionChartData" />
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in filteredSalesData" :key="sale.date + sale.customer + sale.product">
                  <td>{{ sale.date }}</td>
                  <td>{{ sale.customer }}</td>
                  <td>{{ sale.product }}</td>
                  <td>{{ sale.quantity }}</td>
                  <td>{{ sale.total }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Puedes agregar estilos adicionales aquí si es necesario */
</style>
