<script setup>
import { Link, Head } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bienvenido" />
    <div
        class="bg-cover bg-center min-h-screen flex flex-col items-center"
        style="background-image: url('/images/landing.jpeg')"
    >
        <div class="absolute inset-0 bg-black opacity-25"></div>

        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
            <header
                class="absolute top-0 left-0 right-0 flex justify-between items-center p-6"
            >
                <div class="flex lg:justify-center"></div>
                <nav v-if="canLogin" class="flex justify-end">
                    <Link :href="route('login')" class="button">
                        <span>Iniciar Sesión</span>
                    </Link>
                    <Link :href="route('register')" class="button ml-4">
                        <span>Registrarse</span>
                    </Link>
                </nav>
            </header>
            <div
                class="bg-cover bg-center min-h-screen flex flex-col items-center justify-center text-center"
            >
                <h1 class="title">BIENVENIDO</h1>
                <p class="subtitle1">Productos Artesanales Doña Alicia</p>
                <br />
                <p class="subtitle">
                    Deliciosos chocolates y granolas saludables
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Lovelace:wght@400;700&display=swap");
@import url("https://fontesk.com/cristal-elegance-font/");
@font-face {
    font-family: "Amelia Harper Script";
    src: url("/fonts/amelia_harper_script.ttf") format("truetype");
}

body {
    font-family: "Montserrat", sans-serif;
}

.title {
    font-size: 3.5rem;
    color: white;
    font-family: "Lovelace", serif;
    text-align: center;
    text-shadow: 3px 3px 10px rgb(0, 0, 0,255);
    line-height: 1;
}
.title1 {
    font-size: 2rem;
    color: white;
    font-family: "Lovelace", serif;
    text-align: center;
    text-shadow: 3px 3px 10px rgba(0, 0, 0, 255);
    line-height: 1;
}
.subtitle {
    font-size: 2rem;
    color: white;
    font-family: "Lovelace", serif;
    text-align: center;
    text-shadow: 3px 3px 10px rgba(0, 0, 0, 255);
    margin-top: 1rem;
    line-height: 1;
}
.subtitle1 {
    font-size: 6rem;
    color: white;
    font-family: "Amelia Harper Script", sans-serif;
    text-align: center;
    text-shadow: 3px 3px 10px rgba(0, 0, 0, 255);
    margin-top: 1rem;
    line-height: 1;
}
.bg-cover {
    filter: brightness(0.85);
}
@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
.title,
.title1,
.subtitle1,
.subtitle {
    animation: fadeIn 3s ease-in-out;
    user-select: none;
}
.button {
    background: rgba(0, 0, 0, 0.7);
    position: relative;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid #d7ccc8;
    border-radius: 17px;
    outline: none;
    overflow: hidden;
    color: rgb(255, 255, 255);
    transition: background-color 0.3s ease, color 0.3s ease,
        border-color 0.3s ease;
    text-align: center;
    user-select: none;
}

.button span {
    z-index: 1;
}

.button::before {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background-color: rgba(255, 255, 255, 0.1);
    transition: all 0.6s ease;
    border-radius: 50%;
    z-index: 0;
    transform: translate(-50%, -50%) scale(0);
}

.button:hover {
    color: #000;
    background-color: #ffffff;
    border: 1px solid #ffffff;
}

.button:hover::before {
    transform: translate(-50%, -50%) scale(1);
}
.button:hover span {
    position: relative;
    z-index: 1;
}
</style>
