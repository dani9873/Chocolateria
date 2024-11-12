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
    productos: Array,
    materiasPrimas: Array,
});

const showModal = ref(false);
const showRawMaterialModal = ref(false);
const isViewMode = ref(false);
const editingProducto = ref(null);
const search = ref('');
const selectedCategoria = ref('');
const sortColumn = ref('nombre');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const stockFilter = ref('all');

const form = useForm({
    id: null,
    nombre: '',
    categoria: '',
    precio: '',
    stock_minimo: '',
    cantidadDisponible: '',
});

const rawMaterialForm = useForm({
    producto_id: '',
    materia_prima_id: '',
    cantidad_por_unidad: '',
});

const filteredProductos = computed(() => {
    let filtered = props.productos;
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(p =>
            p.nombre.toLowerCase().includes(searchLower) ||
            p.categoria.toLowerCase().includes(searchLower)
        );
    }
    if (selectedCategoria.value) {
        filtered = filtered.filter(p => p.categoria === selectedCategoria.value);
    }
    if (stockFilter.value !== 'all') {
        filtered = filtered.filter(p => {
            if (stockFilter.value === 'low') {
                return p.cantidadDisponible < p.stock_minimo;
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

const paginatedProductos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredProductos.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredProductos.value.length / itemsPerPage.value));

const categorias = computed(() => {
    return [...new Set(props.productos.map(p => p.categoria))];
});

const changeSort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const openModal = (producto = null, viewMode = false) => {
    isViewMode.value = viewMode;
    if (producto) {
        editingProducto.value = producto;
        form.id = producto.id;
        form.nombre = producto.nombre;
        form.categoria = producto.categoria;
        form.precio = producto.precio;
        form.stock_minimo = producto.stock_minimo;
        form.cantidadDisponible = producto.cantidadDisponible;
    } else {
        editingProducto.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingProducto.value = null;
};

const submitForm = () => {
    if (form.id) {
        form.put(route('productos.update', form.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('productos.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProducto = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        form.delete(route('productos.destroy', id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const exportProductos = () => {
    const worksheet = XLSX.utils.json_to_sheet(filteredProductos.value);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Productos');
    XLSX.writeFile(workbook, 'productos.xlsx');
};

const openRawMaterialModal = (producto = null) => {
    if (producto) {
        rawMaterialForm.producto_id = producto.id;
    } else {
        rawMaterialForm.reset();
    }
    showRawMaterialModal.value = true;
};

const closeRawMaterialModal = () => {
    showRawMaterialModal.value = false;
    rawMaterialForm.reset();
};

const submitRawMaterialForm = () => {
    rawMaterialForm.post(route('producto.materia-prima.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => closeRawMaterialModal(),
    });
};

const deleteRawMaterial = (productoId, materiaPrimaId) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta materia prima del producto?')) {
        form.delete(route('producto.materia-prima.destroy', { producto: productoId, materia_prima: materiaPrimaId }), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Gestión de Productos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Productos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4 flex justify-between">
                        <button @click="openModal()"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Agregar Producto
                        </button>
                        <button @click="exportProductos"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                            Exportar Productos
                        </button>
                    </div>

                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input v-model="search" type="text" placeholder="Buscar producto..."
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                        <select v-model="selectedCategoria"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option value="">Todas las categorías</option>
                            <option v-for="categoria in categorias" :key="categoria" :value="categoria">
                                {{ categoria }}
                            </option>
                        </select>
                        <select v-model="stockFilter"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option value="all">Todos los productos</option>
                            <option value="low">Stock bajo</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th v-for="column in ['nombre', 'categoria', 'precio', 'cantidadDisponible', 'stock_minimo']"
                                        :key="column" @click="changeSort(column)"
                                        class="py-3 px-6 text-left cursor-pointer">
                                        {{ column.replace('_', ' ') }}
                                        <span v-if="sortColumn === column">
                                            {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>
                                    <th class="py-3 px-6 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <tr v-for="producto in paginatedProductos" :key="producto.id"
                                    class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ producto.nombre }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ producto.categoria }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ producto.precio }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap"
                                        :class="{ 'text-red-600': producto.cantidadDisponible < producto.stock_minimo }">
                                        {{ producto.cantidadDisponible }}
                                    </td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ producto.stock_minimo }}</td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center">
                                            <button @click="openModal(producto, true)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button @click="openModal(producto)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteProducto(producto.id)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <button @click="openRawMaterialModal(producto)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <button @click="currentPage > 1 && currentPage--" :disabled="currentPage === 1"
                            class="px-4 py-2 bg-gray-300 rounded-md">Anterior</button>
                        <span>Página {{ currentPage }} de {{ totalPages }}</span>
                        <button @click="currentPage < totalPages && currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 bg-gray-300 rounded-md">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <JetDialogModal :show="showModal" @close="closeModal">
            <template #title>
                {{ isViewMode ? "Ver Producto" : form.id ? "Editar Producto" : "Agregar Producto" }}
            </template>

            <template #content>
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <JetInputLabel for="nombre" value="Nombre" />
                        <input id="nombre" v-model="form.nombre" type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.nombre" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="categoria" value="Categoría" />
                        <input id="categoria" v-model="form.categoria" type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.categoria" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="precio" value="Precio" />
                        <input id="precio" v-model="form.precio" type="number" step="0.01"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.precio" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="stock_minimo" value="Stock Mínimo" />
                        <input id="stock_minimo" v-model="form.stock_minimo" type="number"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.stock_minimo" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="cantidadDisponible" value="Cantidad Disponible" />
                        <input id="cantidadDisponible" v-model="form.cantidadDisponible" type="number"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.cantidadDisponible" class="mt-2" />
                    </div>

                    <div v-if="editingProducto" class="mb-4">
                        <h3 class="font-bold mb-2">Materias Primas:</h3>
                        <ul>
                            <li v-for="mp in editingProducto.materias_primas" :key="mp.id"
                                class="flex justify-between items-center mb-2">
                                <span>{{ mp.nombre }} - {{ mp.pivot.cantidad_por_unidad }} {{ mp.unidadMedida }}</span>
                                <button @click="deleteRawMaterial(editingProducto.id, mp.id)"
                                    class="text-red-600 hover:text-red-800" v-if="!isViewMode">
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

                <button v-if="!isViewMode"
                    class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="submitForm">
                    {{ form.id ? "Actualizar" : "Guardar" }}
                </button>
            </template>
        </JetDialogModal>

        <!-- Raw Material Modal -->
        <JetDialogModal :show="showRawMaterialModal" @close="closeRawMaterialModal">
            <template #title>
                Agregar Materia Prima
            </template>

            <template #content>
                <form @submit.prevent="submitRawMaterialForm">
                    <div class="mb-4">
                        <JetInputLabel for="raw_material_producto" value="Producto" />
                        <select id="raw_material_producto" v-model="rawMaterialForm.producto_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            required>
                            <option v-for="producto in props.productos" :key="producto.id" :value="producto.id">
                                {{ producto.nombre }}
                            </option>
                        </select>
                        <JetInputError :message="rawMaterialForm.errors.producto_id" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="raw_material" value="Materia Prima" />
                        <select id="raw_material" v-model="rawMaterialForm.materia_prima_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            required>
                            <option v-for="mp in props.materiasPrimas" :key="mp.id" :value="mp.id">
                                {{ mp.nombre }}
                            </option>
                        </select>
                        <JetInputError :message="rawMaterialForm.errors.materia_prima_id" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="cantidad_por_unidad" value="Cantidad por Unidad" />
                        <input id="cantidad_por_unidad" v-model="rawMaterialForm.cantidad_por_unidad" type="number"
                            step="0.01"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            required />
                        <JetInputError :message="rawMaterialForm.errors.cantidad_por_unidad" class="mt-2" />
                    </div>
                </form>
            </template>

            <template #footer>
                <JetSecondaryButton @click="closeRawMaterialModal">
                    Cancelar
                </JetSecondaryButton>

                <button
                    class="ml-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                    :class="{ 'opacity-25': rawMaterialForm.processing }" :disabled="rawMaterialForm.processing"
                    @click="submitRawMaterialForm">
                    Agregar Materia Prima
                </button>
            </template>
        </JetDialogModal>
    </AppLayout>
</template>