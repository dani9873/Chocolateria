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
    ventas: Array,
    clientes: Array,
    productos: Array,
    estados: Array,
    ventasPorMes: Array,
});

const showModal = ref(false);
const isViewMode = ref(false);
const editingVenta = ref(null);
const search = ref('');
const selectedCliente = ref('');
const selectedEstado = ref('');
const fechaInicio = ref('');
const fechaFin = ref('');
const sortColumn = ref('fecha');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = ref(5);

const form = useForm({
    id: null,
    fecha: '',
    cliente_id: '',
    productos: [{ id: '', cantidad: 1 }],
    estado: '',
});

const filteredVentas = computed(() => {
    let filtered = props.ventas;
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(v =>
            v.cliente.nombre.toLowerCase().includes(searchLower) ||
            v.productos.some(p => p.nombre.toLowerCase().includes(searchLower))
        );
    }
    if (selectedCliente.value) {
        filtered = filtered.filter(v => v.cliente_id === selectedCliente.value);
    }
    if (selectedEstado.value) {
        filtered = filtered.filter(v => v.estados.some(e => e.id === selectedEstado.value));
    }
    if (fechaInicio.value && fechaFin.value) {
        filtered = filtered.filter(v => {
            const fecha = new Date(v.fecha);
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

const changeSort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const openModal = (venta = null, viewMode = false) => {
    isViewMode.value = viewMode;
    if (venta) {
        editingVenta.value = venta;
        form.id = venta.id;
        form.fecha = venta.fecha;
        form.cliente_id = venta.cliente_id;
        form.productos = venta.productos.map(p => ({
            id: p.id,
            cantidad: p.pivot.cantidad,
        }));
        form.estado = venta.estados.length ? venta.estados[0].id : '';
    } else {
        editingVenta.value = null;
        form.reset();
        form.productos = [{ id: '', cantidad: 1 }];
        form.estado = '';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingVenta.value = null;
};

const submitForm = () => {
    if (form.id) {
        form.put(route('ventas.update', form.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('ventas.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
            onError: (errors) => {
                console.error(errors);
            }
        });
    }
};

const addProducto = () => {
    form.productos.push({ id: '', cantidad: 1 });
};

const removeProducto = (index) => {
    form.productos.splice(index, 1);
};

const deleteVenta = (id) => {
    if (confirm("¿Estás seguro de que deseas eliminar esta venta?")) {
        form.delete(route('ventas.destroy', id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                props.ventas = props.ventas.filter(venta => venta.id !== id);
            },
        });
    }
};

const exportVentas = () => {
    const worksheet = XLSX.utils.json_to_sheet(props.ventas);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Ventas");
    XLSX.writeFile(workbook, "ventas.xlsx");
};

const paginatedVentas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredVentas.value.slice(start, end);
});

const chartData = computed(() => ({
    labels: props.ventasPorMes.map(v => {
        const date = new Date(2023, v.mes - 1, 1);
        return date.toLocaleString('default', { month: 'long' });
    }),
    datasets: [
        {
            label: 'Ventas por Mes',
            data: props.ventasPorMes.map(v => v.total),
            fill: false,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
};

</script>

<template>
    <AppLayout title="Gestión de Ventas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Ventas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4 flex justify-between">
                        <button @click="openModal()"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Agregar Venta
                        </button>
                        <button @click="exportVentas"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                            Exportar Ventas
                        </button>
                    </div>

                    <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                        <input v-model="search" type="text" placeholder="Buscar venta..."
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                        <select v-model="selectedCliente"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option value="">Todos los clientes</option>
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                {{ cliente.nombre }}
                            </option>
                        </select>
                        <select v-model="selectedEstado"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option value="">Todos los estados</option>
                            <option v-for="estado in estados" :key="estado.id" :value="estado.id">
                                {{ estado.nombre }}
                            </option>
                        </select>

                        <input v-model="fechaInicio" type="date"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                        <input v-model="fechaFin" type="date"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th v-for="column in ['fecha', 'cliente.nombre', 'total', 'estados']" :key="column"
                                        @click="changeSort(column)" class="py-3 px-6 text-left cursor-pointer">
                                        {{ column.split('.').pop().replace('_', ' ') }}
                                        <span v-if="sortColumn === column">
                                            {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>
                                    <th class="py-3 px-6 text-center">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <tr v-for="venta in paginatedVentas" :key="venta.id"
                                    class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                        moment.tz(venta.fecha, 'America/Guatemala').format('DD/MM/YYYY')
                                    }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ venta.cliente ?
                                        venta.cliente.nombre :
                                        'N/A' }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">Q. {{ venta.total }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">
                                        {{ venta.estados[0].nombre }}
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center">
                                            <button @click="openModal(venta, true)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button @click="openModal(venta)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteVenta(venta.id)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path d="M6 18L18 6M6 6l12 12" />
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
                        <span>Página {{ currentPage }} de {{ Math.ceil(filteredVentas.length / itemsPerPage) }}</span>
                        <button @click="currentPage < Math.ceil(filteredVentas.length / itemsPerPage) && currentPage++"
                            :disabled="currentPage === Math.ceil(filteredVentas.length / itemsPerPage)"
                            class="px-4 py-2 bg-gray-300 rounded-md">Siguiente</button>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Gráfico de Ventas por Mes</h3>
                        <div style="height: 300px;">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <JetDialogModal :show="showModal" @close="closeModal">
            <template #title>
                {{ isViewMode ? "Ver Venta" : form.id ? "Editar Venta" : "Agregar Venta" }}
            </template>

            <template #content>
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <JetInputLabel for="fecha" value="Fecha" />
                        <input id="fecha" v-model="form.fecha" type="date"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.fecha" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="cliente" value="Cliente" />
                        <select id="cliente" v-model="form.cliente_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required>
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                {{ cliente.nombre }}
                            </option>
                        </select>
                        <JetInputError :message="form.errors.cliente_id" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="productos" value="Productos" />
                        <div v-for="(producto, index) in form.productos" :key="index" class="flex items-center mt-2">
                            <select v-model="producto.id"
                                class="w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                :disabled="isViewMode" required>
                                <option v-for="p in productos" :key="p.id" :value="p.id">
                                    {{ p.nombre }}
                                </option>
                            </select>
                            <input v-model.number="producto.cantidad" type="number" min="1"
                                class="w-1/4 ml-2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                :disabled="isViewMode" required />
                            <button @click="removeProducto(index)" type="button"
                                class="ml-2 text-red-600 hover:text-red-900" v-if="!isViewMode">
                                Eliminar
                            </button>
                        </div>
                        <button @click="addProducto" type="button" class="mt-2 text-indigo-600 hover:text-indigo-900"
                            v-if="!isViewMode">
                            Agregar Producto
                        </button>
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="estado" value="Estados" />
                        <select id="estado" v-model="form.estado"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required>
                            <option v-for="estado in estados" :key="estado.id" :value="estado.id">
                                {{ estado.nombre }}
                            </option>
                        </select>
                        <JetInputError :message="form.errors.estado" class="mt-2" />
                    </div>

                    <div v-if="editingVenta" class="mb-4">
                        <p>
                            <strong>Fecha de Creación:</strong>
                            {{ new Date(editingVenta.created_at).toLocaleString() }}
                        </p>
                        <p>
                            <strong>Fecha de Actualización:</strong>
                            {{ new Date(editingVenta.updated_at).toLocaleString() }}
                        </p>
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
    </AppLayout>
</template>
