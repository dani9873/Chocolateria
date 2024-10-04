<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <div>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
            <ApplicationLogo class="block h-12 w-auto" />

            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                Bienvenidos a nuestra empresa de Chocolate y Granola
            </h1>

            <p class="mt-6 text-gray-500 leading-relaxed">
                Descubre nuestros deliciosos productos hechos con los mejores
                ingredientes. Desde chocolates artesanales hasta granolas
                saludables, tenemos algo para todos los gustos.
            </p>
        </div>

        <div
            class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 p-6 lg:p-8"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?chocolate"
                    alt="Chocolate"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    Nuestros Chocolates
                </h2>
                <p class="mt-2 text-gray-600">
                    Explora nuestra variedad de chocolates artesanales.
                </p>
                <a
                    href="/productos/chocolates"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?granola"
                    alt="Granola"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    Nuestra Granola
                </h2>
                <p class="mt-2 text-gray-600">
                    Descubre nuestras granolas saludables y deliciosas.
                </p>
                <a
                    href="/productos/granola"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?team"
                    alt="About Us"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    Sobre Nosotros
                </h2>
                <p class="mt-2 text-gray-600">
                    Conoce más sobre nuestra historia y misión.
                </p>
                <a
                    href="/about"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?contact"
                    alt="Contact"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    Contáctanos
                </h2>
                <p class="mt-2 text-gray-600">
                    ¿Tienes alguna pregunta? ¡Estamos aquí para ayudarte!
                </p>
                <a
                    href="/contact"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?blog"
                    alt="Blog"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    Nuestro Blog
                </h2>
                <p class="mt-2 text-gray-600">
                    Lee nuestros últimos artículos y noticias.
                </p>
                <a
                    href="/blog"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <img
                    src="https://source.unsplash.com/featured/?shop"
                    alt="Shop"
                    class="w-full h-48 object-cover rounded-lg"
                />
                <h2 class="mt-4 text-xl font-semibold text-gray-900">Tienda</h2>
                <p class="mt-2 text-gray-600">
                    Compra nuestros productos en línea.
                </p>
                <a
                    href="/shop"
                    class="mt-4 inline-block text-indigo-600 hover:text-indigo-900"
                    >Ver más</a
                >
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Puedes agregar estilos personalizados aquí si es necesario */
</style>
