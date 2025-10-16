<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>e-market Bolivia - Perfil de Usuario</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://unpkg.com/heroicons@2.0.18/24/outline/index.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#02187D",
                        "background-light": "#f2f1f0",
                        "background-dark": "#0f1223",
                        "danger": "#F40009",
                        "success": "#1db954"
                    },
                    fontFamily: {
                        "display": ["Work Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        .group:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }

        #profile-photo-modal:not(:checked)~div[id="modal-backdrop"],
        #order-details-modal:not(:checked)~div[id="order-details-backdrop"] {
            display: none;
        }


        [data-tab-content] {
            display: none;
        }

        .active[data-tab-content] {
            display: block;
        }
    </style>
</head>

<body x-data="{ modalOpen: false, deleteModalOpen: false }"
    :class="{'overflow-hidden': modalOpen || deleteModalOpen }"
    class="font-display bg-background-light dark:bg-background-dark"
    x-init="Alpine.store('profile', { editing: false, loading: false })">
    <?php
    include 'navbar.php';
    ?>
    <div class="flex flex-col min-h-screen">

        <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-2 flex-grow">
            <div class="flex flex-col md:flex-row gap-8">
                <aside class="w-full md:w-64 lg:w-80 flex-shrink-0">
                    <div class="bg-white dark:bg-background-dark p-4 rounded-lg shadow-sm">
                        <nav class="flex flex-col gap-2" id="sidebar-nav">
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary" href="#"
                                data-tab="cuenta">
                                <svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M230.93,220a8,8,0,0,1-6.93,4H32a8,8,0,0,1-6.92-12c15.23-26.33,38.7-45.21,66.09-54.16a72,72,0,1,1,73.66,0c27.39,8.95,50.86,27.83,66.09,54.16A8,8,0,0,1,230.93,220Z">
                                    </path>
                                </svg>
                                <span class="font-medium text-sm">Mi Cuenta</span>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary"
                                href="#" data-tab="ordenes">
                                <svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M80,64a8,8,0,0,1,8-8H216a8,8,0,0,1,0,16H88A8,8,0,0,1,80,64Zm136,56H88a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Zm0,64H88a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16ZM44,52A12,12,0,1,0,56,64,12,12,0,0,0,44,52Zm0,64a12,12,0,1,0,12,12A12,12,0,0,0,44,116Zm0,64a12,12,0,1,0,12,12A12,12,0,0,0,44,180Z">
                                    </path>
                                </svg>
                                <span class="font-medium text-sm">Mis Ordenes</span>
                            </a>

                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary"
                                href="#" data-tab="notificaciones">
                                <svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M221.8,175.94C216.25,166.38,208,139.33,208,104a80,80,0,1,0-160,0c0,35.34-8.26,62.38-13.81,71.94A16,16,0,0,0,48,200H88.81a40,40,0,0,0,78.38,0H208a16,16,0,0,0,13.8-24.06ZM128,216a24,24,0,0,1-22.62-16h45.24A24,24,0,0,1,128,216ZM48,184c7.7-13.24,16-43.92,16-80a64,64,0,1,1,128,0c0,36.05,8.28,66.73,16,80Z">
                                    </path>
                                </svg>
                                <span class="font-medium text-sm">Notificaciones</span>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary"
                                data-tab="seguridad" href="#">
                                <span class="material-symbols-outlined">lock</span>

                                <span class="font-medium text-sm">Seguridad y Acceso</span>
                            </a>
                        </nav>
                        <hr class="my-4 border-gray-200 dark:border-gray-700" />
                        <button id="logout-btn"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg text-danger hover:bg-danger/10 w-full text-left">
                            <svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M224,128a8,8,0,0,1-8,8H59.31l58.35,58.34a8,8,0,0,1-11.32,11.32l-72-72a8,8,0,0,1,0-11.32l72-72a8,8,0,0,1,11.32,11.32L59.31,120H216A8,8,0,0,1,224,128Z"></path>
                            </svg>
                            <span class="font-medium text-sm">Cerrar Sesión</span>
                        </button>
                    </div>
                </aside>
                <div class="flex-1">
                    <div class="bg-white dark:bg-background-dark p-6 rounded-lg shadow-sm active" data-tab-content="cuenta" id="cuenta-content">
                        <h2 class="text-2xl font-bold text-primary mb-6">Mi Cuenta</h2>
                        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                            <div class="relative group">
                                <div id="profile-image-div" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full w-32 h-32"
                                    style='background-image: url("");'>
                                </div>
                                <button @click="modalOpen = true"
                                    class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <span class="material-symbols-outlined text-4xl">
                                        edit
                                    </span>
                                </button>

                                <div
                                    class="tooltip absolute bottom-full mb-2 w-max bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 invisible transition-opacity duration-300">
                                    Editar Foto
                                    <div
                                        class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-x-4 border-x-transparent border-t-4 border-t-gray-800">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 id="user-name" class="text-xl font-bold text-gray-800 dark:text-white">Isabella Rodriguez</h3>
                                <p id="user-email-display" class="text-gray-500 dark:text-gray-400">isabella.rodriguez@email.com</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Detalles de Cuenta</h4>

                                <!-- Input oculto para nombres: aparece solo en edición -->
                                <div x-show="$store.profile.editing" class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="nombres">Nombres</label>
                                    <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                                        id="nombres" name="nombres" type="text" placeholder="Ingresa tu nombre completo" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="email">Email</label>
                                        <input
                                            x-bind:disabled="!$store.profile.editing"
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                                            id="email" name="email" type="email" disabled />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="phone">Teléfono</label>
                                        <input
                                            x-bind:disabled="!$store.profile.editing"
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                                            id="phone" name="phone" type="tel" disabled />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="password">Contraseña</label>
                                        <input
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"
                                            id="password" name="password" type="password" value="••••••••" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones actualizados -->
                        <div class="flex justify-start gap-4 mt-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <!-- Botón principal: cambia label y acción -->
                            <button
                                x-bind:class="$store.profile.loading ? 'opacity-50 cursor-not-allowed' : ''"
                                x-bind:disabled="$store.profile.loading"
                                x-text="$store.profile.editing ? 'Guardar Cambios' : 'Editar Perfil'"
                                x-on:click="$store.profile.editing ? saveProfile() : startEditing()"
                                class="px-6 py-2.5 rounded-lg bg-primary text-white font-semibold text-sm shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:opacity-50"
                                type="button">
                            </button>

                            <!-- Botón Cancelar: solo en edición -->
                            <button
                                x-show="$store.profile.editing"
                                x-on:click="cancelEdit()"
                                class="px-6 py-2.5 rounded-lg bg-gray-500 text-white font-semibold text-sm shadow-sm hover:bg-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500"
                                type="button">
                                Cancelar
                            </button>

                            <!-- Botón Eliminar: solo fuera de edición -->
                            <button
                                x-show="!$store.profile.editing"
                                @click="deleteModalOpen = true"
                                class="px-6 py-2.5 rounded-lg bg-danger text-white font-semibold text-sm shadow-sm hover:bg-danger/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger"
                                type="button">
                                Eliminar Cuenta
                            </button>
                        </div>
                    </div>

                    <!-- Placeholder para ordenes -->

                    <div class="bg-white dark:bg-background-dark p-6 rounded-lg shadow-sm" data-tab-content="ordenes" id="ordenes-content">
                        <input class="hidden peer" id="order-details-modal" type="checkbox" />
                        <div class="fixed inset-0 bg-black/70 z-20 peer-checked:block hidden" id="order-details-backdrop">
                            <div class="flex h-full items-center justify-center p-4">
                                <div class="bg-white dark:bg-background-dark rounded-lg shadow-xl w-full max-w-2xl">
                                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                        <h3 class="text-2xl font-bold text-primary" id="modal-titulo">Detalles del Pedido</h3>
                                        <label class="cursor-pointer" for="order-details-modal">
                                            <span class="material-symbols-outlined text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">close</span>
                                        </label>
                                    </div>
                                    <div class="p-6 max-h-[70vh] overflow-y-auto" id="modal-body-detalles">
                                        <!-- Contenido dinámico se inyecta aquí -->
                                    </div>
                                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg flex justify-end gap-4">
                                        <label class="px-6 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold text-sm cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600" for="order-details-modal">Cerrar</label>
                                        <button id="btn-volver-comprar" class="px-6 py-2.5 rounded-lg bg-primary text-white font-semibold text-sm shadow-sm hover:bg-primary/90">Volver a Comprar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-2xl font-bold text-primary">Mis Pedidos</h2>
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <div class="relative w-full sm:w-64">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                    <input id="buscador-ordenes" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary placeholder:text-gray-400" placeholder="Buscar por pedido o producto" type="text" />
                                </div>
                                <div class="relative">
                                    <select id="filtro-estado" class="appearance-none w-full sm:w-auto pl-4 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">Filtrar por estado</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="enviado">Enviado</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                        </div>
                        <div id="contenedor-ordenes-lista" class="space-y-6">
                            <!-- Las órdenes se generarán dinámicamente aquí -->
                            <p class="text-center text-gray-500 py-8">Cargando órdenes...</p>
                        </div>
                    </div>

                    <!-- Placeholder para notificaciones -->
                    <div class="bg-white dark:bg-background-dark p-6 rounded-lg shadow-sm hidden" data-tab-content="notificaciones" id="notificaciones-content">
                        <h2 class="text-2xl font-bold text-primary mb-6">Notificaciones</h2>
                        <p>No hay notificaciones.</p>
                    </div>

                    <!-- Contenido de seguridad -->
                    <div class="bg-white dark:bg-background-dark p-6 rounded-lg shadow-sm" data-tab-content="seguridad"
                        id="seguridad-content">
                        <h2 class="text-2xl font-bold text-primary mb-6">Seguridad y Acceso</h2>
                        <div class="space-y-8 max-w-lg">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Cambiar Contraseña</h3>
                                <form class="space-y-6" method="POST" action="/cambiar-password">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            for="current_password">Contraseña Actual</label>
                                        <div class="relative">
                                            <input
                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary shadow-sm"
                                                id="current_password" name="current_password" placeholder="Ingrese su contraseña actual"
                                                type="password" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            for="new_password">Nueva Contraseña</label>
                                        <div class="relative">
                                            <input
                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary shadow-sm"
                                                id="new_password" name="new_password" placeholder="Ingrese su nueva contraseña"
                                                type="password" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            for="confirm_password">Confirmar Nueva Contraseña</label>
                                        <div class="relative">
                                            <input
                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary shadow-sm"
                                                id="confirm_password" name="confirm_password" placeholder="Confirme su nueva contraseña"
                                                type="password" required />
                                        </div>
                                    </div>
                                    <div class="pt-2">
                                        <button
                                            id="btn-cambiar-contrasena"
                                            class="w-full px-6 py-3 rounded-lg bg-primary text-white font-semibold text-sm shadow-lg hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors duration-200"
                                            type="button">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.away="modalOpen = false"
        class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">

        <div @click.stop
            class="bg-white dark:bg-background-dark rounded-lg shadow-xl w-full max-w-md p-8 text-center relative">

            <button @click="modalOpen = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>

            <h2 class="text-2xl font-bold text-primary mb-4">Actualizar Foto de Perfil</h2>

            <form id="form-upload-photo" enctype="multipart/form-data">

                <input type="file" id="file-upload-input" name="imagen" accept="image/png, image/jpeg" class="hidden">

                <div class="relative w-40 h-40 rounded-full my-4 border-2 border-gray-200 dark:border-gray-700 flex items-center justify-center mx-auto">
                    <img id="profile-preview" alt="Current profile picture" class="w-full h-full object-cover rounded-full"
                        src="" />
                    <button type="button"
                        id="trigger-file-upload"
                        class="absolute bottom-2 right-2 bg-black/70 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg hover:bg-black/90 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50 z-10">
                        <span class="material-symbols-outlined text-lg">camera_alt</span>
                    </button>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Sube una imagen JPG o PNG de hasta 5MB</p>
                <div class="w-full space-y-3">

                    <button
                        type="submit"
                        id="guardar-foto"
                        class="w-full px-6 py-3 rounded-lg bg-primary text-white font-semibold text-sm shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-xl">upload</span>
                        <span>Guardar Cambios</span>
                    </button>

                    <button
                        class="w-full px-6 py-3 rounded-lg bg-danger text-white font-semibold text-sm shadow-sm hover:bg-danger/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger flex items-center justify-center gap-2"
                        type="button"
                        id="eliminar-foto">
                        <span class="material-symbols-outlined text-xl">delete</span>
                        <span>Eliminar Foto</span>
                    </button>
                    <button @click="modalOpen = false"
                        class="w-full px-6 py-3 rounded-lg bg-background-light dark:bg-gray-700 text-primary dark:text-white font-semibold text-sm border border-primary hover:bg-primary/10 dark:hover:bg-gray-600"
                        type="button">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal de Confirmación Eliminar Cuenta -->
    <div x-show="deleteModalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="deleteModalOpen = false"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">

        <div @click.stop
            class="bg-white dark:bg-background-dark rounded-lg shadow-xl w-full max-w-sm p-6 text-center relative">

            <button @click="deleteModalOpen = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>

            <div class="mb-4">
                <span class="material-symbols-outlined text-4xl text-danger mb-2 block">warning</span>
            </div>

            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Eliminar Cuenta</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Esta acción es irreversible. ¿Estás seguro de que quieres eliminar tu cuenta permanentemente?</p>

            <div class="flex gap-3">
                <button @click="deleteModalOpen = false"
                    class="flex-1 px-4 py-2 rounded-lg bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white font-semibold text-sm hover:bg-gray-400 dark:hover:bg-gray-500">
                    Cancelar
                </button>
                <button @click="deleteAccount()"
                    class="flex-1 px-4 py-2 rounded-lg bg-danger text-white font-semibold text-sm hover:bg-danger/90">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script src="./js/perfil-usuario.js"></script>
    <script src="./js/ordenes.js"></script>
    <script src="./js/global.js"></script>
</body>

</html>