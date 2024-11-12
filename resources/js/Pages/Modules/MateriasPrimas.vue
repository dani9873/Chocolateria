<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import JetDialogModal from '@/Components/DialogModal.vue';
import JetSecondaryButton from '@/Components/SecondaryButton.vue';
import JetDangerButton from '@/Components/DangerButton.vue';
import JetInputLabel from '@/Components/InputLabel.vue';
import JetInputError from '@/Components/InputError.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
  materiasPrimas: Array,
  productos: Array,
});

const showModal = ref(false);
const showInventoryModal = ref(false);
const showProductRelationModal = ref(false);
const isViewMode = ref(false);
const editingMateriaPrima = ref(null);
const search = ref('');
const sortColumn = ref('nombre');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const stockFilter = ref('all');

const form = useForm({
  id: null,
  nombre: '',
  cantidadDisponible: '',
  unidadMedida: '',
  costoUnitario: '',
});

const inventoryForm = useForm({
  materia_prima_id: '',
  cantidad: '',
  tipo: 'incremento',
});

const productRelationForm = useForm({
  materia_prima_id: '',
  producto_id: '',
  cantidad_por_unidad: '',
});

const filteredMateriasPrimas = computed(() => {
  let filtered = props.materiasPrimas;
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(mp => mp.nombre.toLowerCase().includes(searchLower));
  }
  if (stockFilter.value !== 'all') {
    filtered = filtered.filter(mp => {
      if (stockFilter.value === 'low') {
        return mp.cantidadDisponible < mp.stock_minimo;
      }
      return true;
    });
  }
  return filtered.sort((a, b) => {
    let modifier = sortDirection.value === 'asc' ? 1 : -1;
    if (a[sortColumn.value] < b[sortColumn.value]) return -1 * modifier;
    if (a[sortColumn.value] > b[sortColumn.value]) return 1 * modifier;
    return 0;
  });
});

const paginatedMateriasPrimas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredMateriasPrimas.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredMateriasPrimas.value.length / itemsPerPage.value));

const changeSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const openModal = (materiaPrima = null, viewMode = false) => {
  isViewMode.value = viewMode;
  if (materiaPrima) {
    editingMateriaPrima.value = materiaPrima;
    form.id = materiaPrima.id;
    form.nombre = materiaPrima.nombre;
    form.cantidadDisponible = materiaPrima.cantidadDisponible;
    form.unidadMedida = materiaPrima.unidadMedida;
    form.costoUnitario = materiaPrima.costoUnitario;
  } else {
    editingMateriaPrima.value = null;
    form.reset();
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
  editingMateriaPrima.value = null;
};

const submitForm = () => {
  if (form.id) {
    form.put(route('materiasPrimas.update', form.id), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('materiasPrimas.store'), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  }
};

const deleteMateriaPrima = (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta materia prima?')) {
    form.delete(route('materiasPrimas.destroy', id), {
      preserveScroll: true,
      preserveState: true,
    });
  }
};

const exportMateriasPrimas = () => {
  const worksheet = XLSX.utils.json_to_sheet(filteredMateriasPrimas.value);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Materias Primas');
  XLSX.writeFile(workbook, 'materias_primas.xlsx');
};

const openInventoryModal = (materiaPrima = null) => {
  if (materiaPrima) {
    inventoryForm.materia_prima_id = materiaPrima.id;
  } else {
    inventoryForm.reset();
  }
  showInventoryModal.value = true;
};

const closeInventoryModal = () => {
  showInventoryModal.value = false;
  inventoryForm.reset();
};

const submitInventoryForm = () => {
  inventoryForm.post(route('materiasPrimas.update-inventory'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => closeInventoryModal(),
  });
};

const openProductRelationModal = (materiaPrima = null) => {
  if (materiaPrima) {
    productRelationForm.materia_prima_id = materiaPrima.id;
  } else {
    productRelationForm.reset();
  }
  showProductRelationModal.value = true;
};

const closeProductRelationModal = () => {
  showProductRelationModal.value = false;
  productRelationForm.reset();
};

const submitProductRelationForm = () => {
  productRelationForm.post(route('materiasPrimas.add-product-relation'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => closeProductRelationModal(),
  });
};

const deleteProductRelation = (materiaPrimaId, productoId) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta relación con el producto?')) {
    form.delete(route('materiasPrimas.remove-product-relation', { materia_prima: materiaPrimaId, producto: productoId }), {
      preserveScroll: true,
      preserveState: true,
    });
  }
};
</script>

<template>
  <AppLayout title="Gestión de Materias Primas">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestión de Materias Primas
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="mb-4 flex justify-between">
            <button @click="openModal()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
              Agregar Materia Prima
            </button>
            <button @click="exportMateriasPrimas" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
              Exportar Materias Primas
            </button>
          </div>

          <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <input v-model="search" type="text" placeholder="Buscar materia prima..." class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
            <select v-model="stockFilter" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
              <option value="all">Todas las materias primas</option>
              <option value="low">Stock bajo</option>
            </select>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th v-for="column in ['nombre', 'cantidadDisponible', 'unidadMedida', 'costoUnitario']" :key="column" @click="changeSort(column)" class="py-3 px-6 text-left cursor-pointer">
                    {{ column.replace('_', ' ') }}
                    <span v-if="sortColumn === column">
                      {{ sortDirection === 'asc' ? '▲' : '▼' }}
                    </span>
                  </th>
                  <th class="py-3 px-6 text-center">Acciones</th>
                </tr>
              </thead>
              <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="materiaPrima in paginatedMateriasPrimas" :key="materiaPrima.id" class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ materiaPrima.nombre }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap" :class="{ 'text-red-600': materiaPrima.cantidadDisponible < materiaPrima.stock_minimo }">
                    {{ materiaPrima.cantidadDisponible }}
                  </td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ materiaPrima.unidadMedida }}</td>
                  <td class="py-3 px-6 text-left whitespace-nowrap">{{ materiaPrima.costoUnitario }}</td>
                  <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center">
                      <button @click="openModal(materiaPrima, true)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <button @click="openModal(materiaPrima)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button @click="deleteMateriaPrima(materiaPrima.id)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                      <button @click="openInventoryModal(materiaPrima)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                      </button>
                      <button @click="openProductRelationModal(materiaPrima)" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
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
        </div>
      </div>
    </div>

    <!-- Materia Prima Modal -->
    <JetDialogModal :show="showModal" @close="closeModal">
      <template #title>
        {{ isViewMode ? "Ver Materia Prima" : form.id ? "Editar Materia Prima" : "Agregar Materia Prima" }}
      </template>

      <template #content>
        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <JetInputLabel for="nombre" value="Nombre" />
            <input id="nombre" v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.nombre" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="cantidadDisponible" value="Cantidad Disponible" />
            <input id="cantidadDisponible" v-model="form.cantidadDisponible" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.cantidadDisponible" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="unidadMedida" value="Unidad de Medida" />
            <input id="unidadMedida" v-model="form.unidadMedida" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.unidadMedida" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="costoUnitario" value="Costo Unitario" />
            <input id="costoUnitario" v-model="form.costoUnitario" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" :disabled="isViewMode" required />
            <JetInputError :message="form.errors.costoUnitario" class="mt-2" />
          </div>

          <div v-if="editingMateriaPrima" class="mb-4">
            <h3 class="font-bold mb-2">Productos Relacionados:</h3>
            <ul>
              <li v-for="producto in editingMateriaPrima.productos" :key="producto.id" class="flex justify-between items-center mb-2">
                <span>{{ producto.nombre }} - {{ producto.pivot.cantidad_por_unidad }} {{ editingMateriaPrima.unidadMedida }} por unidad</span>
                <button @click="deleteProductRelation(editingMateriaPrima.id, producto.id)" class="text-red-600 hover:text-red-800" v-if="!isViewMode">
                  Eliminar
                </button>
              </li>
            </ul>
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

    <!-- Inventory Modal -->
    <JetDialogModal :show="showInventoryModal" @close="closeInventoryModal">
      <template #title>
        Actualizar Inventario
      </template>

      <template #content>
        <form @submit.prevent="submitInventoryForm">
          <div class="mb-4">
            <JetInputLabel for="inventario_materia_prima" value="Materia Prima" />
            <select id="inventario_materia_prima" v-model="inventoryForm.materia_prima_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required>
              <option v-for="mp in props.materiasPrimas" :key="mp.id" :value="mp.id">
                {{ mp.nombre }}
              </option>
            </select>
            <JetInputError :message="inventoryForm.errors.materia_prima_id" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="inventario_cantidad" value="Cantidad" />
            <input id="inventario_cantidad" v-model="inventoryForm.cantidad" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required />
            <JetInputError :message="inventoryForm.errors.cantidad" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="inventario_tipo" value="Tipo de Ajuste" />
            <select id="inventario_tipo" v-model="inventoryForm.tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required>
              <option value="incremento">Incremento</option>
              <option value="decremento">Decremento</option>
            </select>
            <JetInputError :message="inventoryForm.errors.tipo" class="mt-2" />
          </div>
        </form>
      </template>

      <template #footer>
        <JetSecondaryButton @click="closeInventoryModal">
          Cancelar
        </JetSecondaryButton>

        <button class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" :class="{ 'opacity-25': inventoryForm.processing }" :disabled="inventoryForm.processing" @click="submitInventoryForm">
          Actualizar Inventario
        </button>
      </template>
    </JetDialogModal>

    <!-- Product Relation Modal -->
    <JetDialogModal :show="showProductRelationModal" @close="closeProductRelationModal">
      <template #title>
        Agregar Relación con Producto
      </template>

      <template #content>
        <form @submit.prevent="submitProductRelationForm">
          <div class="mb-4">
            <JetInputLabel for="product_relation_materia_prima" value="Materia Prima" />
            <select id="product_relation_materia_prima" v-model="productRelationForm.materia_prima_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required>
              <option v-for="mp in props.materiasPrimas" :key="mp.id" :value="mp.id">
                {{ mp.nombre }}
              </option>
            </select>
            <JetInputError :message="productRelationForm.errors.materia_prima_id" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="product_relation_producto" value="Producto" />
            <select id="product_relation_producto" v-model="productRelationForm.producto_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required>
              <option v-for="producto in props.productos" :key="producto.id" :value="producto.id">
                {{ producto.nombre }}
              </option>
            </select>
            <JetInputError :message="productRelationForm.errors.producto_id" class="mt-2" />
          </div>

          <div class="mb-4">
            <JetInputLabel for="cantidad_por_unidad" value="Cantidad por Unidad" />
            <input id="cantidad_por_unidad" v-model="productRelationForm.cantidad_por_unidad" type="number" step="0.01" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" required />
            <JetInputError :message="productRelationForm.errors.cantidad_por_unidad" class="mt-2" />
          </div>
        </form>
      </template>

      <template #footer>
        <JetSecondaryButton @click="closeProductRelationModal">
          Cancelar
        </JetSecondaryButton>

        <button class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" :class="{ 'opacity-25': productRelationForm.processing }" :disabled="productRelationForm.processing" @click="submitProductRelationForm">
          Agregar Relación
        </button>
      </template>
    </JetDialogModal>
  </AppLayout>
</template>