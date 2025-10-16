const productosPorPagina = 9;
let productos = [];
let paginaActual = 1;

//funcion asincrona para hacer llamadas al servidor (async). 
async function cargarProductos() {
    try{
        //esperamos la respuesta del servidor(awit).
        const respuesta = await fetch(`api/get_productos_vendedor.php?id_vendedor=${idVendedor}`);
        const data = await respuesta.json();
        if(data.status === "success"){
            productos = data.data;
            mostrarPagina(1);
        }else{
            document.getElementById("contenedorProductos").innerHTML = `<p class="text-danger">${data.message}</p>`;
        }        
    }catch(error){
        console.error(error);
    }
}

function mostrarPagina(pagina){
    paginaActual = pagina;
    const inicio = (pagina - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;
    const productosPagina = productos.slice(inicio, fin) // Devuelve una copia de una sección de un array. Tanto para el inicio como para el final.

    const contenedor = document.getElementById("contenedorProductos");
    contenedor.innerHTML = "";//reeemplazamos el contenido interno del html con vacio si no hay carga de producto 
    let fila;

    productosPagina.forEach((prod, index) =>{
        if(index % 3 == 0){
            fila = document.createElement("div");//creamos in nuevo elemento div
            fila.className = "row g-3 mb-3";//cambiamos los atributos de la clase del div (contenedorProductos).
            contenedor.appendChilden(fila);//añadimos el nuevo hijo o nodo (al llegar al final) a la siguiente fila.
        }

        const col = document.createElement("div");
        col.className = "col-md-4";
        col.innerHTML = `
            <div class="product-card">
                <img src="${prod.rutas_imagenes[0]}" alt="${prod.nombre}">
                <div class="p-3">
                    <h6 class="fw-bold">${prod.nombre}</h6>
                    <p class="text-muted">${prod.descripcion}</p>
                    <p class="fw-bold text-success">Bs. ${prod.precio}</p>
                    <button class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-cart"></i> Agregar al carrito
                    </button>
                </div>
            </div>
        `;
        //redireccionamos al detalle de los productos.
        col.querySelector(".product-card").onclick = () => {
            window.location.href = `detalle_producto.html?id=${prod.id}`;
        }
        fila.appendChild(col);
    });
    crearPaginacion();
}       

function crearPaginacion(){
    const totalPaginas = Math.ceil(productos.length / productosPorPagina);//redondeamos el numero de pagina hacia arriba para evitar errores de decimales.
    const pagContainer = document.getElementById("paginacion");
    pagContainer.innerHTML = "";

    for(let i = 0; i <= totalPaginas; i++){
        const btn = document.createElement("button");
        btn.className = "btn btn-sm btn-outline-primary me-1";
        btn.textContent = i; //definimos el eltexto del boton donde i es el numero de pagina
        btn.onclick = () => mostrarPagina(i);//nos muestra la pagina selecionada.
        pagContainer.appendChild(btn);//agregamos el nuevo boton de la siguiente pagina.
    }
}
//inicializamos la carga del nuevo producto.
document.addEventListener("DOMContentLoaded", cargarProductos);