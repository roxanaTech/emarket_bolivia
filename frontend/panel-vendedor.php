<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>e-market Bolivia - Dashboard</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js ya implementado -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#02187D",
                        "background-light": "#ffffff",
                        "background-dark": "#0f1223",
                        "sidebar-bg": "#f2f1f0",
                        "success": "#1db954",
                        "danger": "#F40009",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
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

        .ordenes-section {
            width: 80%;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#0d0f1c]">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex flex-col lg:flex-row h-full grow">
            <!-- Sidebar: Oculto en móvil, visible en desktop -->
            <aside id="sidebar" class="hidden lg:flex w-64 flex-col bg-sidebar-bg p-4 flex-shrink-0">
                <div class="flex items-center mb-8 px-2">
                    <a href="principal.php"><img src="./img/logoh.png" alt="Logo emarket-bolivia" class="hidden md:block h-[4rem] object-contain"></a>
                </div>
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10 active-section" href="#" data-section="dashboard" onclick="loadSection('dashboard')">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2 rounded-lg bg-primary text-white active-section" href="#" data-section="productos" onclick="loadSection('productos')">
                        <span class="material-symbols-outlined">sell</span>
                        <p class="text-sm font-medium">Productos</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="ordenes" onclick="loadSection('ordenes')">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <p class="text-sm font-medium">Ordenes</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="marketing" onclick="loadSection('marketing')">
                        <span class="material-symbols-outlined">campaign</span>
                        <p class="text-sm font-medium">Marketing &amp; Promociones</p>
                    </a>
                    <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="configuracion" onclick="loadSection('configuracion')">
                        <span class="material-symbols-outlined">settings</span>
                        <p class="text-sm font-medium">Configuración</p>
                    </a>
                </nav>
            </aside>
            <div class="flex-1">
                <!-- Navbar: Con botón hamburguesa para móvil que despliega menú de pestañas -->
                <header class="flex items-center justify-between p-4 border-b border-gray-200 lg:border-none" id="navbar">
                    <div class="flex items-center gap-4">
                        <button id="mobile-menu-toggle" class="lg:hidden p-2 rounded-md hover:bg-gray-100" onclick="toggleMobileMenu()">
                            <span class="material-symbols-outlined text-primary">menu</span>
                        </button>
                        <div class="flex items-center gap-2 lg:hidden">
                            <a href="principal.php"><img src="./img/logoh.png" alt="Logo emarket-bolivia" class="h-[2rem] object-contain"></a>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Perfil: Muestra imagen y nombre (cargado dinámicamente) -->
                        <div id="profile-nav" class="relative group flex items-center gap-2">
                            <button class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100">
                                <div id="profile-img" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-8" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDWz1oLiNOCqS3OD_xXGHqqQfwtYwwzdOLnSGU2hUqIsXjPVFJRunHci2SebbTBvVryfYGoflmPvZj-uYCK2OccSng6qrdZYWntc4Dz2Tx1FSkqMZOCYuZSmi-hmw3VooUy5mYYBwV0Qrw4fa60Cuizy7W3v8Xm-8-AMItZcY9xroD93mmV5yBKEra8ltkALw2FqN1z_unumjnzVW_Ct_DQjNLPGCk9pcoadkvEhEjgDAbJtYqhvG3xa7rp_F5EsU8lkN_2a2_hDw");'></div>
                                <span id="user-name" class="hidden md:block text-sm font-medium text-primary">Cargando...</span>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 hidden group-hover:block">
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="#" onclick="cerrarSesion();">Cerrar Sesión</a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Menú móvil desplegable: Lista de pestañas desde el botón toggle -->
                <div id="mobile-menu" class="lg:hidden fixed inset-0 z-40 bg-black bg-opacity-50 hidden" onclick="closeMobileMenu(event)">
                    <div class="fixed left-0 top-0 h-full w-64 bg-sidebar-bg p-4 transform -translate-x-full transition-transform duration-300 ease-in-out" id="mobile-sidebar">
                        <div class="flex items-center justify-between mb-8 px-2">
                            <img src="./img/logoh.png" alt="Logo emarket-bolivia" class="h-[4rem] object-contain">
                            <button onclick="closeMobileMenu()" class="p-2 rounded-md hover:bg-gray-100">
                                <span class="material-symbols-outlined text-primary">close</span>
                            </button>
                        </div>
                        <nav class="flex flex-col gap-2">
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10 active-section" href="#" data-section="dashboard" onclick="loadSection('dashboard'); closeMobileMenu()">
                                <span class="material-symbols-outlined">dashboard</span>
                                <p class="text-sm font-medium">Dashboard</p>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg bg-primary text-white active-section" href="#" data-section="productos" onclick="loadSection('productos'); closeMobileMenu()">
                                <span class="material-symbols-outlined">sell</span>
                                <p class="text-sm font-medium">Productos</p>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="ordenes" onclick="loadSection('ordenes'); closeMobileMenu()">
                                <span class="material-symbols-outlined">inventory_2</span>
                                <p class="text-sm font-medium">Ordenes</p>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="marketing" onclick="loadSection('marketing'); closeMobileMenu()">
                                <span class="material-symbols-outlined">campaign</span>
                                <p class="text-sm font-medium">Marketing &amp; Promociones</p>
                            </a>
                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg text-primary hover:bg-primary/10" href="#" data-section="configuracion" onclick="loadSection('configuracion'); closeMobileMenu()">
                                <span class="material-symbols-outlined">settings</span>
                                <p class="text-sm font-medium">Configuración</p>
                            </a>
                        </nav>
                    </div>
                </div>

                <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
                <!-- Main: Contenedor para contenido dinámico -->
                <main id="main-content" class="p-4 sm:p-6 lg:p-8">
                    <!-- Dashboard Section: KPIs y Gráficos -->
                    <div id="dashboard-section" class="section-content">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <h1 class="text-primary text-3xl lg:text-4xl font-black leading-tight tracking-tight mb-4 sm:mb-0">Dashboard</h1>
                        </div>
                        <!-- KPIs: Cards dinámicas -->
                        <div id="kpi-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <!-- Cards se renderizan con JS -->
                        </div>
                        <!-- Gráficos: Grid responsive -->
                        <!-- En #dashboard-section, mantén la grid como está (ya en 3 columnas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Ventas Mensuales: Línea -->
                            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventas Mensuales</h3>
                                <div class="h-64">
                                    <canvas id="ventas-chart"></canvas>
                                </div>
                            </div>
                            <!-- Órdenes por Categoría: Barras -->
                            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Órdenes por Categoría</h3>
                                <div class="h-64">
                                    <canvas id="categorias-chart"></canvas>
                                </div>
                            </div>
                            <!-- Distribución de Estados: Doughnut -->
                            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución de Estados de Órdenes</h3>
                                <div class="h-64">
                                    <canvas id="estados-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Otras secciones (sin cambios) -->
                    <div id="productos-section" class="section-content hidden">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <h1 class="text-primary text-3xl lg:text-4xl font-black leading-tight tracking-tight mb-4 sm:mb-0">Gestión de Productos</h1>
                            <button id="add-product-btn" class="bg-primary text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined">add</span>
                                Agregar Producto
                            </button>
                        </div>
                        <!-- Modal para agregar/editar producto -->
                        <div x-data="productoModal()" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <!-- Backdrop -->
                                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false"></div>

                                <!-- Modal panel -->
                                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle max-h-[90vh] overflow-y-auto">
                                    <!-- Header con steps -->
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium text-gray-900" x-text="isEdit ? 'Editar Producto' : 'Agregar Producto'"></h3>
                                            <button @click="open = false; if(isEdit) resetForm()" class="text-gray-400 hover:text-gray-500">
                                                <span class="material-symbols-outlined">close</span>
                                            </button>
                                        </div>
                                        <!-- Progress bar para steps -->
                                        <div class="mt-4 flex justify-between">
                                            <template x-for="(step, index) in steps" :key="index">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center" x-text="step.completed ? '✓' : index + 1"></div>
                                                    <span class="text-xs mt-1" x-text="step.name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Form por steps -->
                                    <form @submit.prevent="submitForm" class="bg-white px-4 pb-4 sm:px-6">
                                        <!-- Step 1: Categoría/Subcategoría (limpiado duplicado) -->
                                        <div x-show="currentStep === 1">
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                                                    <select x-model="formData.id_categoria" @change="loadSubcategorias" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                                        <option value="">Selecciona una categoría</option>
                                                        <template x-for="cat in categorias" :key="cat.id">
                                                            <option :value="cat.id" x-text="cat.nombre"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Subcategoría</label>
                                                    <select x-model="formData.id_subcategoria" @change="updateSelectedSubcategoria" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" :disabled="!formData.id_categoria">
                                                        <option value="">Primero selecciona categoría</option>
                                                        <template x-for="sub in subcategorias" :key="sub.id">
                                                            <option :value="sub.id" x-text="sub.nombre"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 2: Datos del Producto -->
                                        <div x-show="currentStep === 2">
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                                    <input x-model="formData.nombre" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Descripción *</label>
                                                    <textarea x-model="formData.descripcion" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"></textarea>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Marca *</label>
                                                        <input x-model="formData.marca" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Precio (Bs.) *</label>
                                                        <input x-model="formData.precio" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Stock *</label>
                                                        <input x-model="formData.stock" type="number" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">SKU (opcional)</label>
                                                        <input x-model="formData.sku" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Estado del Producto *</label>
                                                        <select x-model="formData.estado_producto" x-init="$el.value = formData.estado_producto" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                                            <option value="nuevo">Nuevo</option>
                                                            <option value="usado">Usado</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Color (opcional)</label>
                                                        <input x-model="formData.color" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Modelo (opcional)</label>
                                                        <input x-model="formData.modelo" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700">Peso (opcional, g)</label>
                                                        <input x-model="formData.peso" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Dimensiones (opcional, ej. 10x20x30 cm)</label>
                                                    <input x-model="formData.dimensiones" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 3: Upload Imágenes (1-6, primera principal) -->
                                        <div x-show="currentStep === 3">
                                            <div class="space-y-4">
                                                <p class="text-sm text-gray-600" x-text="isEdit ? 'Mantén o reemplaza imágenes existentes (1-6). La primera es principal.' : 'Sube 1-6 imágenes (máx. 2MB cada una). La primera será la principal.'"></p>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <template x-for="(imgData, index) in imagesData" :key="index">
                                                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary">
                                                            <img x-show="imgData.preview" :src="imgData.preview" class="mx-auto h-20 w-20 object-cover rounded" />
                                                            <input type="file" accept="image/*" @change="handleImageUpload($event, index)" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90" />
                                                            <button @click.stop="removeImage(index)" class="absolute top-1 right-1 text-danger text-xs" x-show="imgData.preview"
                                                                type="button">×</button>
                                                            <p x-text="index === 0 ? 'Principal' : 'Secundaria'" class="text-xs mt-1 text-gray-500"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                                <p class="text-xs text-gray-500">Formatos: JPG, PNG. Tamaño máx: 2MB.</p>
                                            </div>
                                        </div>

                                        <!-- Step 4: Confirmar -->
                                        <div x-show="currentStep === 4">
                                            <div class="space-y-4">
                                                <h4 class="text-lg font-medium">Resumen</h4>
                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <p><strong>Nombre:</strong> <span x-text="formData.nombre"></span></p>
                                                    <p><strong>Precio:</strong> Bs. <span x-text="formData.precio"></span></p>
                                                    <p><strong>Stock:</strong> <span x-text="formData.stock"></span></p>
                                                    <p><strong>Subcategoría:</strong> <span x-text="selectedSubcategoria"></span></p>
                                                    <p><strong>Imágenes:</strong> <span x-text="getNumImages()"></span> configuradas</p>
                                                    <!-- Más campos si quieres -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer con botones de steps -->
                                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                            <button type="button" @click="nextStep()" :disabled="!canNext() && currentStep !== 4" class="inline-flex w-full justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm" x-text="currentStep === 4 ? (isEdit ? 'Actualizar Producto' : 'Registrar Producto') : 'Siguiente'">
                                            </button>
                                            <button type="button" @click="prevStep()" :disabled="currentStep === 1" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Anterior</button>
                                            <button type="button" @click="open = false; if(isEdit) resetForm()" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="mb-6 space-y-4">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                <input id="search-input" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary" placeholder="Buscar por nombre o SKU..." type="text" />
                            </div>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <select id="estado-filter" class="border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                    <option value="">Estado: Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <select id="stock-filter" class="border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                    <option value="">Stock: Todos</option>
                                    <option value="bajo">Bajo Stock (&lt;10)</option>
                                </select>
                                <select id="subcategoria-filter" class="border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                    <option value="">Subcategoría: Todas</option>
                                    <option value="Teléfonos Móviles">Teléfonos Móviles</option>
                                    <option value="Audio y Video">Audio y Video</option>
                                    <option value="Cámaras">Cámaras</option>
                                    <option value="Muebles">Muebles</option>
                                    <option value="Decoración">Decoración</option>
                                    <option value="Herramientas">Herramientas</option>
                                    <option value="Electrodomésticos">Electrodomésticos</option>
                                    <option value="Ropa de Mujer">Ropa de Mujer</option>
                                    <option value="Ropa de Hombre">Ropa de Hombre</option>
                                    <option value="Calzado">Calzado</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <!-- <th class="px-6 py-3"><input type="checkbox" id="select-all" /></th>-->
                                        <th class="px-6 py-3" scope="col">Imagen</th>
                                        <th class="px-6 py-3" scope="col">Nombre</th>
                                        <th class="px-6 py-3" scope="col">SKU</th>
                                        <th class="px-6 py-3" scope="col">Precio</th>
                                        <th class="px-6 py-3" scope="col">Stock</th>
                                        <th class="px-6 py-3" scope="col">Estado</th>
                                        <th class="px-6 py-3" scope="col">Subcategoría</th>

                                        <th class="px-6 py-3" scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="productos-tbody">
                                    <!-- Se pobla dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <nav aria-label="Table navigation" class="flex items-center justify-between pt-4">
                            <span id="pagination-info" class="text-sm font-normal text-gray-500">Mostrando <span class="font-semibold text-gray-900">1-10</span> de <span class="font-semibold text-gray-900">0</span></span>
                            <ul id="pagination-nav" class="inline-flex -space-x-px text-sm h-8">
                                <!-- Se genera con JS -->
                            </ul>
                        </nav>
                    </div>
                    <!-- Sección de Órdenes -->
                    <div id="ordenes-section" class="section-content hidden">
                        <!-- Header con filtros -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4 md:mb-0">Mis Órdenes</h2>
                            <!-- Filtros -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <input type="text" id="search-ordenes" placeholder="Buscar por comprador o email..." class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <select id="estado-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="enviada">Enviada</option>
                                    <option value="entregada">Entregada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                                <button id="clear-filters" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Limpiar</button>
                            </div>
                        </div>

                        <!-- Tabla de Órdenes -->
                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Venta</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Bs.)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Entrega</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprador</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="ordenes-tbody" class="bg-white divide-y divide-gray-200">
                                    <!-- Se llena con JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="flex items-center justify-between mt-4">
                            <div id="pagination-info" class="text-sm text-gray-700"></div>
                            <nav id="pagination-nav" class="flex space-x-1">
                                <!-- Se llena con JS -->
                            </nav>
                        </div>
                    </div>
                    <!-- ... resto de secciones ... -->
                </main>
            </div>
        </div>
    </div>
    <!-- Scripts: Global + Modulares para Dashboard -->
    <!-- 1. Alpine.js PRIMERO (sin defer, o con defer pero antes que los tuyos) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>

    <!-- 2. Tus scripts DEPENDE de Alpine -->
    <script src="./js/global.js"></script>
    <script src="./js/dashboard-kpis.js"></script>
    <script src="./js/dashboard-graficos.js"></script>
    <script src="./js/dashboard-init.js"></script>
    <script src="./js/agregar-producto.js"></script>
    <script src="./js/productos.js"></script>
    <script src="./js/ordenes-vendedor.js"></script>
</body>

</html>