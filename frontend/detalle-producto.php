<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>e-market Bolivia</title>
  <link rel="icon" type="image/png" href="./img/icon.png">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="./css/detalle-producto.css">
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#02187D",
            "secondary": "#f2f1f0",
            "success": "#1db954",
            "danger": "#F40009",
            "warning": "#FEBD69",
            "content": "#0a0a0a",
            "content-subtle": "#4a4a4a"
          },
          fontFamily: {
            "display": ["Work Sans", "sans-serif"]
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
        'FILL' 1,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-white font-display text-content">
  <?php
  include 'navbar.php';
  ?>
  <header>

  </header>
  <div class="flex flex-col">

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div id="producto-detalle-principal" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
      </div>
      <div class="mt-16">
        <div class="border-b border-secondary dark:border-content/20">
          <nav aria-label="Tabs" class="-mb-px flex gap-6">
            <a id="tab-descripcion"
              class="whitespace-nowrap py-4 px-1 border-b-2 border-azul font-bold text-azul text-base cursor-pointer no-underline"
              data-tab="descripcion">Descripción</a>
            <a id="tab-especificaciones"
              class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-content-subtle hover:text-primary hover:border-primary/50 text-base cursor-pointer no-underline"
              data-tab="especificaciones">Especificaciones Técnicas</a>
            <a id="tab-opiniones"
              class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-content-subtle hover:text-primary hover:border-primary/50 text-base cursor-pointer no-underline"
              data-tab="opiniones">Opiniones de Clientes</a>
          </nav>
        </div>
        <div id="tabs-content" class="py-6 max-w-none">
        </div>
      </div>
      <div class="mt-16">
        <h2 class="text-2xl font-bold mb-6 text-content">Productos que podrían gustarte</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-secondary">
              <img alt="TechPro Y400"
                class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuArZ3i9SsvvGd5ggJgk4vykSOmqUHOWFnttoF-wMWbu1ETtZ1ZprlQHri1TUyaLoZnvhi71r8m2S9rP-2WjBPo9ZgeO05zP7gOtjsCeF5BV08cmToaakn2nAaD2zQIn-7l_Cmpg4RgZAH4FnE-cJuabnyDPnQ93BeALoPeaIZlkKGwaAV7DEAlU6GHPKUWQCue31_tkzvBvbHg-yCRiFF1v1_5ySB7YT0KcEf6EQl8WZBfDyPyVocmCSVsKF2wivfzbT9nALl5yoqU" />
            </div>
            <h3 class="mt-4 text-base font-semibold text-content ">TechPro Y400</h3>
            <p class="mt-1 text-lg font-bold text-primary">Bs 599</p>
          </div>
          <div class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-secondary">
              <img alt="TechPro Z600"
                class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3rxasYFekKlLFppJkOHE2dSuPwL_32tYZlYJRmGlMXzBOmGcN_18g9Clx-j_1P0awynQQlR7Cua870btcK1duxQtfMxfUmqF9_RqbAp2eoMDxmlLiSUhK1pyU69nOHn9NIg5t7_3z8Aud5iM1gEuCIHBAP9H-qzSDJb9ytnYY3R9DxFwOvTQMnDhz7cf-X7ynudSTgKOmn8cPrEDHduEpbYUrcoLsS2CMyEdQcnSOr_PM6OFKBXuXk8AMwOWgxDH9qwvg4-rgYDE" />
            </div>
            <h3 class="mt-4 text-base font-semibold text-content">TechPro Z600</h3>
            <p class="mt-1 text-lg font-bold text-primary">$899</p>
          </div>
          <div class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-secondary">
              <img alt="TechPro A300"
                class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBiMUP45f1AK57ICNAApZFbTqqcTa24e-PBTR61DIEdtPyxi7tRUq0ENt6tSLd3UjzOC9ZxCY3yApJHQmZU9hSkKnLwycJrkvIZFi-LpJrqNLYFKYvt1QF5WVK__28oV3GE6oH9j45hm4p05SzZO99G-J0bYLRZtwZ5oRcKadUYK08S5KDMdgMpt3jVTwgxUL4dSaJ5qswc8D1p_g9qnwkgEuhZdl77cWKHi-z5xT3Rlm2bNsloUsssVwKLbL3uT6xXHKY6wkZtmCg" />
            </div>
            <h3 class="mt-4 text-base font-semibold text-content">TechPro A300</h3>
            <p class="mt-1 text-lg font-bold text-primary">$399</p>
          </div>
          <div class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-secondary">
              <img alt="TechPro A300"
                class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDjwiYO_hVKNHXvaYYZJBSbCoprKod9CNDVdVP3EOwQBJzg6vrVhVU6-L0wpvBfua6zPIFG0EpNbxhWGqnhesvOoBxwxl5ut7kvKZ9lVLotlY1_W8YNfY-SuHSkgK9BZGaS5EwHm9C7H-H7NyJHv5ylzHIf34p2JTTh_mWRzmexDDNcIbT6oqSCciEOMLER6teCMEOC-HoKZ6vwfL0C5FoL0NugHUBHxmW6DQq21O0jHWIK8xD39GfGpUv68s-9d5jaRK26hi-TMqc" />
            </div>
            <h3 class="mt-4 text-base font-semibold text-content ">AeroBook Pro</h3>
            <p class="mt-1 text-lg font-bold text-primary">$1299</p>
          </div>
        </div>
      </div>
    </main>

    <div class="hidden modal fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" id="reviewModal">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 ease-out scale-95"
        id="modal-content">
        <div class="p-6 sm:p-8">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-primary" id="tituloModal">Escribe tu Opinión</h2>
            <button
              class="text-text-muted-light dark:text-text-muted-dark hover:text-primary "
              id="closeModalBtn">
              <span class="material-symbols-outlined text-3xl">close</span>
            </button>
          </div>
          <form action="#" class="space-y-6">
            <div>
              <label class="block text-lg font-medium text-primary mb-2">Tu Calificación</label>
              <div class="star-rating">
                <input id="5-stars" name="rating" type="radio" value="5" /><label
                  class="material-symbols-outlined" for="5-stars">star</label>
                <input id="4-stars" name="rating" type="radio" value="4" /><label
                  class="material-symbols-outlined" for="4-stars">star</label>
                <input id="3-stars" name="rating" type="radio" value="3" /><label
                  class="material-symbols-outlined" for="3-stars">star</label>
                <input id="2-stars" name="rating" type="radio" value="2" /><label
                  class="material-symbols-outlined" for="2-stars">star</label>
                <input id="1-star" name="rating" type="radio" value="1" /><label
                  class="material-symbols-outlined" for="1-star">star</label>
              </div>
              <p class="text-sm text-rating-red mt-2 hidden">Por favor, selecciona una calificación.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-primary mb-1" for="review-title">Título de tu
                opinión</label>
              <input
                class="w-full bg-surface-light  border border-surface-dark rounded-lg py-2 px-3 focus:ring-primary focus:border-primary transition duration-150 ease-in-out"
                id="review-title" name="review-title" placeholder="Ej: ¡Increíble producto!" type="text" />
              <p class="text-sm text-rating-red mt-1 hidden">El título es obligatorio.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-primary mb-1" for="review-body">Escribe tu opinión
                aquí...</label>
              <textarea
                class="w-full bg-surface-light dark:bg-background-dark border border-surface-dark dark:border-surface-light rounded-lg py-2 px-3 focus:ring-primary focus:border-primary transition duration-150 ease-in-out"
                id="review-body" name="review-body" placeholder="Cuéntanos más sobre tu experiencia..."
                rows="5"></textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4">
              <button
                class="text-text-muted-light dark:text-text-muted-dark hover:text-primary dark:hover:text-white font-medium py-2 px-4 rounded-lg transition-colors"
                id="cancelBtn" type="button">Cancelar</button>
              <button
                class="bg-primary text-white font-bold py-2.5 px-6 rounded-lg hover:bg-primary/90 transition-colors duration-300 flex items-center gap-2"
                type="submit">
                <span class="material-symbols-outlined">send</span>
                Publicar Reseña
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- En tu HTML (fuera de otros modales) -->
  <div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
      <h3 class="text-lg font-bold text-gray-800 mb-3">¿Eliminar reseña?</h3>
      <p class="text-gray-600 mb-4">Esta acción no se puede deshacer. ¿Estás seguro?</p>
      <div class="flex justify-end gap-2">
        <button id="cancelDeleteBtn" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
        <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
      </div>
    </div>
  </div>
  <?php
  // Incluir el pie de página 
  include 'footer.php';
  ?>
  <script src="./js/detalle-producto.js"></script>
</body>

</html>