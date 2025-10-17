function productoModal() {
    return {
        open: false,
        isEdit: false,
        editId: null,
        currentStep: 1,
        steps: [
            { name: 'Categoría', completed: false },
            { name: 'Datos', completed: false },
            { name: 'Imágenes', completed: false },
            { name: 'Confirmar', completed: false }
        ],
        formData: {
            id_categoria: '',
            id_subcategoria: '',
            nombre: '',
            descripcion: '',
            marca: '',
            precio: '',
            stock: '',
            sku: '',
            estado_producto: 'nuevo',
            color: '',
            modelo: '',
            peso: '',
            dimensiones: ''
        },
        selectedSubcategoria: '',
        imagesData: Array(6).fill(null).map(() => ({ file: null, preview: null, originalPath: null })),
        categorias: [],
        subcategorias: [],

        init() {
            this.loadCategorias();
            document.getElementById('add-product-btn')?.addEventListener('click', () => {
                this.isEdit = false;
                this.editId = null;
                this.open = true;
                this.resetImagesData();
            });
            window.productoModalInstance = this;
        },

        resetImagesData() {
            this.imagesData = Array(6).fill(null).map(() => ({ file: null, preview: null, originalPath: null }));
        },

        nextStep() {
            if (this.currentStep < 4 && this.canNext()) {
                this.steps[this.currentStep - 1].completed = true;
                this.currentStep++;
            } else if (this.currentStep === 4) {
                this.submitForm();
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        canNext() {
            const numImages = this.getNumImages();
            switch (this.currentStep) {
                case 1: return !!this.formData.id_subcategoria;
                case 2: return this.formData.nombre && this.formData.descripcion && this.formData.marca && this.formData.precio && this.formData.stock && this.formData.estado_producto;  // Agregado: valida estado (default 'nuevo')
                case 3: return numImages >= 1 && numImages <= 6;
                default: return true;
            }
        },
        getNumImages() {
            return this.imagesData.filter(d => d.preview).length;
        },

        updateSelectedSubcategoria(event) {
            const selectedOption = event.target.options[event.target.selectedIndex];
            this.selectedSubcategoria = selectedOption.text || '';
        },

        async loadForEdit(id) {
            this.isEdit = true;
            this.editId = id;
            this.open = true;
            this.currentStep = 1;
            this.steps.forEach(s => s.completed = false);
            this.resetImagesData();  // Limpia primero

            try {
                const token = localStorage.getItem('token');
                const res = await fetch(`${apiUrl}/productos/verProducto`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_producto: id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    const prod = data.data;
                    this.formData = {
                        id_categoria: prod.id_categoria || '',
                        id_subcategoria: '',
                        nombre: prod.nombre || '',
                        descripcion: prod.descripcion || '',
                        marca: prod.marca || '',
                        precio: prod.precio ? parseFloat(prod.precio) : '',
                        stock: prod.stock ? parseInt(prod.stock) : '',
                        sku: prod.sku || '',
                        estado_producto: prod.estado_producto || 'nuevo',
                        color: prod.color || '',
                        modelo: prod.modelo || '',
                        peso: prod.peso ? parseFloat(prod.peso.toString().replace(/[^0-9.]/g, '')) : '',
                        dimensiones: prod.dimensiones || ''
                    };
                    this.selectedSubcategoria = prod.nombre_subcategoria || '';

                    await this.loadCategorias();
                    if (this.formData.id_categoria) {
                        await this.loadSubcategorias();
                        this.formData.id_subcategoria = prod.id_subcategoria || '';
                        const sub = this.subcategorias.find(s => s.id == this.formData.id_subcategoria);
                        if (sub) this.selectedSubcategoria = sub.nombre;
                    }

                    // Cargar existentes con paths relativos consistentes
                    const numExisting = prod.rutas_imagenes ? prod.rutas_imagenes.length : 0;
                    // console.log('Cargando existentes:', prod.rutas_imagenes);  // Quita logs si quieres
                    for (let i = 0; i < numExisting && i < 6; i++) {
                        let fullUrl = prod.rutas_imagenes[i];
                        let relPath = fullUrl;
                        let previewUrl = fullUrl;

                        // Si es URL completa, extrae relativa y usa completa para preview
                        if (fullUrl.startsWith('http')) {
                            if (fullUrl.startsWith(apiUrl)) {
                                relPath = fullUrl.replace(apiUrl + '/', '');
                                previewUrl = fullUrl;  // Ya es completa
                            } else {
                                // URL externa: usa solo filename como relPath (ajusta si necesitas)
                                relPath = fullUrl.split('/').pop();
                                previewUrl = fullUrl;
                            }
                        } else {
                            // Relativa: prepend apiUrl para preview, relPath queda como está
                            previewUrl = apiUrl + '/' + fullUrl;
                            relPath = fullUrl;  // Ya relativa
                        }

                        this.imagesData[i] = { file: null, preview: previewUrl, originalPath: relPath };
                        // console.log(`Slot ${i} cargado con originalPath (relativo):`, relPath, `y preview (completo):`, previewUrl);  // Quita logs si quieres
                    }
                    // console.log('Estado imagesData después de load:', this.imagesData.map((d, i) => ({ index: i, originalPath: d.originalPath, preview: d.preview ? 'set' : null, file: !!d.file })));  // Quita logs si quieres
                } else {
                    alert('Error al cargar producto: ' + (data.mensaje || 'Inténtalo de nuevo'));
                    this.resetForm();
                }
            } catch (error) {
                console.error('Error en loadForEdit:', error);
                alert('Error de conexión al cargar producto');
                this.resetForm();
            }
        },

        async loadCategorias() {
            try {
                const res = await fetch(`${apiUrl}/categorias`);
                const data = await res.json();
                if (data.status === 'success') {
                    this.categorias = data.data.map(cat => ({
                        id: cat.id_categoria || cat.id,
                        nombre: cat.nombre
                    }));
                    // console.log('Categorías cargadas:', this.categorias);
                } else {
                    console.error('Error al cargar categorías:', data.mensaje);
                }
            } catch (error) {
                console.error('Error en fetch categorías:', error);
            }
        },

        async loadSubcategorias() {
            if (!this.formData.id_categoria) {
                this.subcategorias = [];
                this.formData.id_subcategoria = '';
                this.selectedSubcategoria = '';
                return;
            }
            try {
                const res = await fetch(`${apiUrl}/categorias/subcategorias?id_categoria=${this.formData.id_categoria}`);
                const data = await res.json();
                if (data.status === 'success') {
                    this.subcategorias = data.data.map(sub => ({
                        id: sub.id_subcategoria || sub.id,
                        nombre: sub.nombre
                    }));
                    this.formData.id_subcategoria = '';
                    this.selectedSubcategoria = '';
                    // console.log('Subcategorías cargadas:', this.subcategorias);
                } else {
                    console.error('Error al cargar subcategorías:', data.mensaje);
                }
            } catch (error) {
                console.error('Error en fetch subcategorías:', error);
            }
        },

        handleImageUpload(event, index) {
            // console.log('Iniciando upload para slot:', index);  // Quita logs si quieres
            // console.log('Estado antes:', this.imagesData.map((d, i) => ({ index: i, hasPreview: !!d.preview })));

            const file = event.target.files[0];
            if (file && file.size > 2 * 1024 * 1024) {
                alert('Imagen demasiado grande (máx 2MB)');
                event.target.value = '';
                return;
            }
            if (file) {
                this.imagesData[index].file = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagesData[index].preview = e.target.result;
                    // console.log('Estado después de setear preview en slot', index, ':', this.imagesData.map((d, i) => ({ index: i, hasPreview: !!d.preview })));
                };
                reader.readAsDataURL(file);
                event.target.value = '';
            }
        },

        removeImage(index) {
            // console.log('Removiendo slot:', index);  // Quita logs si quieres
            this.imagesData[index] = { file: null, preview: null, originalPath: null };
            // console.log('Estado después de remove:', this.imagesData.map((d, i) => ({ index: i, hasPreview: !!d.preview })));
        },

        async submitForm() {
            const formData = new FormData();
            Object.keys(this.formData).forEach(key => {
                if (this.formData[key] !== '' && this.formData[key] !== null) {
                    formData.append(key, this.formData[key]);
                }
            });
            formData.append('id_vendedor', window.currentUser?.id_usuario || localStorage.getItem('user_id'));

            if (!this.isEdit) {
                this.imagesData.forEach(imgData => {
                    if (imgData.file) {
                        formData.append('images[]', imgData.file);
                    }
                });
            } else {
                this.imagesData.forEach(imgData => {
                    if (imgData.file) {
                        formData.append('imagenes_nuevas[]', imgData.file);
                    }
                });
                const keptImages = this.imagesData.filter(imgData => !imgData.file && imgData.originalPath);
                // console.log('DEBUG SUBMIT - Kept images (relativas para imagenes_existentes[]):', keptImages.map(img => img.originalPath));  // Quita logs si quieres
                // console.log('DEBUG SUBMIT - Estado completo imagesData:', this.imagesData.map((d, i) => ({ index: i, file: !!d.file, originalPath: d.originalPath || 'none' })));
                keptImages.forEach(img => formData.append('imagenes_existentes[]', img.originalPath));
                formData.append('id_producto', this.editId);
            }

            const url = this.isEdit ? `${apiUrl}/productos/actualizar` : `${apiUrl}/productos/registro`;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` },
                    body: formData
                });
                const data = await res.json();
                // console.log('Respuesta backend:', data);  // Quita logs si quieres
                if (data.status === 'success') {
                    alert(this.isEdit ? 'Producto actualizado!' : 'Producto registrado!');
                    this.open = false;
                    this.resetForm();
                    if (typeof cargarProductos === 'function') {
                        cargarProductos(currentPage, currentFilters);
                    }
                } else {
                    alert('Error: ' + (data.mensaje || 'Inténtalo de nuevo'));
                }
            } catch (error) {
                console.error('Error submit:', error);
                alert('Error de conexión');
            }
        },

        resetForm() {
            this.currentStep = 1;
            this.steps.forEach(s => s.completed = false);
            Object.keys(this.formData).forEach(key => this.formData[key] = '');
            this.resetImagesData();
            this.selectedSubcategoria = '';
            this.subcategorias = [];
            this.isEdit = false;
            this.editId = null;
        }
    };
}