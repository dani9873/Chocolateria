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
    clientes: Array,
});

const showModal = ref(false);
const isViewMode = ref(false);
const editingCliente = ref(null);
const search = ref('');
const sortColumn = ref('nombre');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const fechaInicio = ref('');
const fechaFin = ref('');

const form = useForm({
    id: null,
    nombre: '',
    email: '',
    telefono: '',
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES");
};

const filteredClientes = computed(() => {
    let filtered = props.clientes;
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(c =>
           (c.nombre && c.nombre.toLowerCase().includes(searchLower)) ||
            (c.email && c.email.toLowerCase().includes(searchLower))
        );
    }
    if (fechaInicio.value && fechaFin.value) {
        filtered = filtered.filter(c => {
            const createdAt = new Date(c.created_at);
            return createdAt >= new Date(fechaInicio.value) && createdAt <= new Date(fechaFin.value);
        });
    }
    return filtered.sort((a, b) => {
        let modifier = sortDirection.value === 'asc' ? 1 : -1;
        if (a[sortColumn.value] < b[sortColumn.value]) return -1 * modifier;
        if (a[sortColumn.value] > b[sortColumn.value]) return 1 * modifier;
        return 0;
    });
});

const paginatedClientes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredClientes.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredClientes.value.length / itemsPerPage.value));

const changeSort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const openModal = (cliente = null, viewMode = false) => {
    isViewMode.value = viewMode;
    if (cliente) {
        editingCliente.value = cliente;
        form.id = cliente.id;
        form.nombre = cliente.nombre;
        form.email = cliente.email;
        form.telefono = cliente.telefono;
    } else {
        editingCliente.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingCliente.value = null;
};

const submitForm = () => {
    if (form.email && props.clientes.some(client => client.email === form.email && client.id !== form.id)) {
        form.errors.email = "Este correo electrónico ya está en uso.";
        return;
    }
    if (form.id) {
        form.put(route('clientes.update', form.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('clientes.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeModal(),
            onError: () => {
                console.error("Error al guardar el cliente");
            },
        });
    }
};

const deleteCliente = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
        form.delete(route('clientes.destroy', id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const exportClientes = () => {
    const worksheet = XLSX.utils.json_to_sheet(filteredClientes.value);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Clientes');
    XLSX.writeFile(workbook, 'clientes.xlsx');
};
</script>

<template>
    <AppLayout title="Gestión de Clientes">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Clientes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4 flex justify-between">
                        <button @click="openModal()"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Agregar Cliente
                        </button>
                        <button @click="exportClientes"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                            Exportar Clientes
                        </button>
                    </div>

                    <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input v-model="search" type="text" placeholder="Buscar cliente..."
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                        <input v-model="fechaInicio" type="date"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                        <input v-model="fechaFin" type="date"
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th v-for="column in ['nombre', 'email', 'telefono', 'Fecha de Creación', 'Fecha de Actualización']"
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
                                <tr v-for="cliente in paginatedClientes" :key="cliente.id"
                                    class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ cliente.nombre }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ cliente.email }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ cliente.telefono }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ formatDate(cliente.created_at)
                                        }}</td>
                                    <td class="py-3 px-6 text-left whitespace-nowrap">{{ formatDate(cliente.updated_at)
                                        }}</td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center">
                                            <button @click="openModal(cliente, true)"
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
                                            <button @click="openModal(cliente)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteCliente(cliente.id)"
                                                class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

        <JetDialogModal :show="showModal" @close="closeModal">
            <template #title>
                {{ isViewMode ? "Ver Cliente" : form.id ? "Editar Cliente" : "Agregar Cliente" }}
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
                        <JetInputLabel for="email" value="Email" />
                        <input id="email" v-model="form.email" type="email"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <JetInputLabel for="telefono" value="Teléfono" />
                        <input id="telefono" v-model="form.telefono" type="tel"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            :disabled="isViewMode" required />
                        <JetInputError :message="form.errors.telefono" class="mt-2" />
                    </div>

                    <div v-if="editingCliente" class="mb-4">
                        <p>
                            <strong>Fecha de Creación:</strong>
                            {{ formatDate(editingCliente.created_at) }}
                        </p>
                        <p>
                            <strong>Fecha de Actualización:</strong>
                            {{ formatDate(editingCliente.updated_at) }}
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