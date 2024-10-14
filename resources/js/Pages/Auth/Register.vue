<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registrarse" />
    <div
        style="
            background-image: url('/images/logIn2.jpeg');
            background-size: 120%;
            background-position: -104px -117px;
            background-attachment: fixed;
            user-select: none;
            height: 100vh;
            overflow: hidden;
        "
    >
        <div class="bg-cover bg-center min-h-screen flex flex-col">
            <AuthenticationCard class="mt-[30px]">
                <template #logo>
                    <AuthenticationCardLogo />
                </template>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel
                            for="name"
                            value="Nombre"
                            class="text-white"
                        />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-white focus:ring focus:ring-white focus:ring-opacity-50"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mt-4">
                        <InputLabel
                            for="email"
                            value="Correo electrónico"
                            class="text-white"
                        />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-white focus:ring focus:ring-white focus:ring-opacity-50"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-4">
                        <InputLabel
                            for="password"
                            value="Contraseña"
                            class="text-white"
                        />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div class="mt-4">
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmar Contraseña"
                            class="text-white"
                        />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <div
                        v-if="
                            $page.props.jetstream
                                .hasTermsAndPrivacyPolicyFeature
                        "
                        class="mt-4"
                    >
                        <InputLabel for="terms">
                            <div class="flex items-center">
                                <Checkbox
                                    id="terms"
                                    v-model:checked="form.terms"
                                    name="terms"
                                    required
                                />
                                <div class="ms-2 text-white">
                                    Estoy de acuerdo con los
                                    <a
                                        target="_blank"
                                        :href="route('terms.show')"
                                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >Términos de Servicio</a
                                    >
                                    y la
                                    <a
                                        target="_blank"
                                        :href="route('policy.show')"
                                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >Política de Privacidad</a
                                    >
                                </div>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.terms"
                            />
                        </InputLabel>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Link
                            :href="route('login')"
                            class="underline text-sm text-white hover:text-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            ¿Ya estás registrado?
                        </Link>

                        <PrimaryButton
                            class="ms-4 mt-[-5px]"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Registrarse
                        </PrimaryButton>
                    </div>
                </form>
            </AuthenticationCard>
        </div>
    </div>
</template>
