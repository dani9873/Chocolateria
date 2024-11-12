<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import JetDialogModal from '@/Components/DialogModal.vue';
import JetSecondaryButton from '@/Components/SecondaryButton.vue';
import JetDangerButton from '@/Components/DangerButton.vue';
import JetInputLabel from '@/Components/InputLabel.vue';
import JetInputError from '@/Components/InputError.vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale } from 'chart.js';
import * as XLSX from 'xlsx';
import moment from 'moment-timezone';

ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, PointElement, CategoryScale);

const props = defineProps({
  compras: Array,
  materiasPrimas: Array,
  usuarios: Array,
  categorias: Array,
});

const showModal = ref(false);
const isViewMode = ref(false);
const editingCompra = ref(null);
const search = ref('');
const selectedUsuario = ref('');
const selectedCategoria = ref('');
const selectedTipoTransaccion = ref('');
const fechaInicio = ref('');
const fechaFin = ref('');
const sortColumn = ref('fecha');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = ref(10);

const form = useForm({
  id: null,
  tipoTransaccion: '',
  monto: '',
  descripcion: '',
  fecha: '',
  categoria: '',
  usuario_id: '',
  materia_prima_id: '',
  cantidad: '',
});

const filteredCompras = computed(() => {
  let filtered = props.compras;
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(c => 
      c.tipoTransaccion.toLowerCase().includes(searchLower) ||
      c.materia_prima.nombre.toLowerCase().includes(searchLower) ||
      c.usuario.name.toLowerCase().includes(searchLower)
    );
  }
  if (selectedUsuario.value) {
    filtered = filtered.filter(c => c.usuario_id === selectedUsuario.value);
  }
  if (selectedCategoria.value) {
    filtered = filtered.filter(c => c.categoria === selectedCategoria.value);
  }
  if (selectedTipoTransaccion.value) {
    filtered = filtered.filter(c => c.tipoTransaccion === selectedTipoTransaccion.value);
  }
  if (fechaInicio.value && fechaFin.value) {
    filtered = filtered.filter(c => {
      const fecha = new Date(c.fecha);
      const inicio = new Date(fechaInicio.value);
      const fin = new Date(fechaFin.value);
      return fecha >= inicio && fecha <= fin;
    });
  }
  return filtered.sort((a, b) => {
    let modifier = sortDirection.value === 'asc' ? 1 : -1;
    if (a[sortColumn.value] < b[sortColumn.value]) return -1 * modifier;
    if (a[sortColumn.value] > b[sortColumn.value]) return 1 * modifier;
    return 0;
  });
});

const paginatedCompras = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredCompras.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredCompras.value.length / itemsPerPage.value));

const changeSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const openModal = (compra = null, viewMode = false) => {
  isViewMode.value = viewMode;
  if (compra) {
    editingCompra.value = compra;
    form.id = compra.id;
    form.tipoTransaccion = compra.tipoTransaccion;
    form.monto = compra.monto;
    form.descripcion = compra.descripcion;
    form.fecha = compra.fecha;
    form.categoria = compra.categoria;
    form.usuario_id = compra.usuario_id;
    form.materia_prima_id = compra.materia_prima_id;
    form.cantidad = compra.cantidad;
  } else {
    editingCompra.value = null;
    form.reset();
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
  editingCompra.value = null;
};

const submitForm = () => {
  if (form.id) {
    form.put(route('compras.update', form.id), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('compras.store'), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  }
};

const deleteCompra = (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta compra?')) {
    form.delete(route('compras.destroy', id), {
      preserveScroll: true,
      preserveState: true,
    });
  }
};

const exportCompras = () => {
  const worksheet = XLSX.utils.json_to_sheet(filteredCompras.value);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Compras');
  XLSX.writeFile(workbook, 'compras.xlsx');
};

const chartData = computed(() => {
  const comprasPorMes = {};
  filteredCompras.value.forEach(compra => {
    const fecha = new Date(compra.fecha);
    const mes = fecha.toLocaleString('default', { month: 'long' });
    if (!comprasPorMes[mes]) {
      comprasPorMes[mes] = 0;
    }
    comprasPorMes[mes] += parseFloat(compra.monto);
  });

  return {
    labels: Object.keys(comprasPorMes),
    datasets: [
      {
        label: 'Compras por Mes',
        data: Object.values(comprasPorMes),
        fill: false,
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
};
</script>

<template>
  <AppLayout title="Gestión de Compras">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Compras
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="mb-4 flex justify-between">
            <button @click="openModal()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
              Agregar Compra
            </button>
            <button @click="exportCompras" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
              Exportar Compras
            </button>
          </div>

          <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <input v-model="search" type="text" placeholder="Buscar compra..." class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
            <select v-model="selectedUsuario" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
              <option value="">Todos los usuarios</option>
              <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                {{ usuario.name }}
              </option>
            </select>
            <select v-model="selectedCategoria" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
              <option value="">Todas las categorías</option>
              <option v-for="categoria in categorias" :key="categoria" :value="categoria">
                {{ categoria }}
              </option>
            </select>
            <select v-model="selectedTipoTransaccion" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
              <option value="">Todos los tipos de transacción</option>
              <option value="compra">Compra</option>
              <option value="ajuste">Ajuste</option>
            </select>
            <input v-model="fechaInicio" type="date" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
            <input v-model="fechaFin" type="date" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th v-for="column in ['tipoTransaccion', 'monto', 'fecha', 'categoria', 'usuario.name', 'materia_prima.nombre']" :key="column" @click="changeSort(column)" class="py-3 px-6 text-left cursor-pointer">
                    {{ column.split('.').pop().replace('_', ' ') }}
                    <span v-if="sortColumn === column">
                      {{ sortDirection === 'asc' ? '▲' : '▼' }}
                    </span>
                  </th>
                  <th class="py-3 px-6 text-center">Acciones</th>
                </tr>
              </thead>
              <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="compra in paginatedCompras" :key="compra.id" class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ compra.tipoTransaccion }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">Q. {{ compra.monto }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ moment(compra.fecha).format('DD/MM/YYYY') }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ compra.categoria }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ compra.usuario.name }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ compra.materia_prima.nombre }}</td>
                  <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center">
                      <button @click="openModal(compra, true)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <button @click="openModal(compra)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button @click="deleteCompra(compra.id)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between items-center mt-4">
            <button @click="currentPage > 1 && currentPage--" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-300 rounded-md">Anterior</button>
            <span>Página {{ currentPage }} de {{ totalPages }}</span>
            <button @click="currentPage < totalPages && currentPage++" :disabled="currentPage === totalPages" class="px-4 py-2 bg-gray-300 rounded-md">Siguiente</button>
          </div>

          <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Gráfico de Compras por Mes</h3>
            <div style="height: 300px;">
              <Line :data="chartData" :options="chartOptions" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <JetDialogModal :show="showModal" @close="closeModal">
      <template #title>
        {{ isViewMode ? "Ver Compra" : form.id ? "Editar Compra" : "Agregar Compra" }}
      </template>

      <template #content>
        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <JetInputLabel for="tipoTransaccion" value="Tipo de Transacción" />
            <select id="tipoTransaccion" v-model="form.tipoTransaccion" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required>
              <option value="compra">Compra</option>
              <option value="ajuste">Ajuste</option>
            </select>
            <JetInputError :message="form.errors.tipoTransaccion" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="monto" value="Monto" />
            <input id="monto" v-model="form.monto" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.monto" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="descripcion" value="Descripción" />
            <textarea id="descripcion" v-model="form.descripcion" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required></textarea>
            <JetInputError :message="form.errors.descripcion" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="fecha" value="Fecha" />
            <input id="fecha" v-model="form.fecha" type="date" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.fecha" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="categoria" value="Categoría" />
            <select id="categoria" v-model="form.categoria" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required>
              <option v-for="categoria in categorias" :key="categoria" :value="categoria">
                {{ categoria }}
              </option>
            </select>
            <JetInputError :message="form.errors.categoria" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="usuario_id" value="Usuario" />
            <select id="usuario_id" v-model="form.usuario_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required>
              <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                {{ usuario.name }}
              </option>
            </select>
            <JetInputError :message="form.errors.usuario_id" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="materia_prima_id" value="Materia Prima" />
            <select id="materia_prima_id" v-model="form.materia_prima_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required>
              <option v-for="materiaPrima in materiasPrimas" :key="materiaPrima.id" :value="materiaPrima.id">
                {{ materiaPrima.nombre }}
              </option>
            </select>
            <JetInputError :message="form.errors.materia_prima_id" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="cantidad" value="Cantidad" />
            <input id="cantidad" v-model="form.cantidad" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.cantidad" class="mt-2" />
          </div>

          <div v-if="editingCompra" class="mb-4">
            <p>
              <strong>Fecha de Creación:</strong> {{ moment(editingCompra.created_at).format('DD/MM/YYYY HH:mm:ss') }}
            </p>
            <p>
              <strong>Fecha de Actualización:</strong> {{ moment(editingCompra.updated_at).format('DD/MM/YYYY HH:mm:ss') }}
            </p>
          </div>
        </form>
      </template>

      <template #footer>
        <JetSecondaryButton @click="closeModal">
          Cancelar
        </JetSecondaryButton>

        <button v-if="!isViewMode" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="submitForm">
          {{ form.id ? "Actualizar" : "Guardar" }}
        </button>
      </template>
    </JetDialogModal>
  </AppLayout>
</template>