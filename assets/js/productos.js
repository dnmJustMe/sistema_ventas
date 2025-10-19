// app/js/productos.js
class ProductosManager {
    constructor() {
        this.productoAEliminar = null;
        this.dataTable = null;
        this.currentProductoDetalles = null;
        this.galleryImages = [];
        this.newExtraImages = [];
        this.currentGalleryIndex = 0;
        this.modalProducto = null;
        this.modalDetalles = null;
        this.modalEliminar = null;
        this.init();
    }

    init() {
        this.inicializarModalesBootstrap();
        this.inicializarDataTables();
        this.cargarCategoriasParaModal();
        this.setupEventListeners();
        this.setupDropzones();
        this.setupGallery();
    }

    inicializarModalesBootstrap() {
        // Inicializar modales de Bootstrap
        this.modalProducto = new bootstrap.Modal(document.getElementById('modal-producto'));
        this.modalDetalles = new bootstrap.Modal(document.getElementById('modal-detalles-producto'));
        this.modalEliminar = new bootstrap.Modal(document.getElementById('modal-confirmar-eliminar'));
    }

    setupEventListeners() {
        // Modal producto
        document.getElementById('btn-agregar-producto').addEventListener('click', () => this.mostrarModalProducto());

        // Form producto
        document.getElementById('form-producto').addEventListener('submit', (e) => this.guardarProducto(e));

        // Modal detalles
        document.getElementById('editar-desde-detalles').addEventListener('click', () => this.editarDesdeDetalles());

        // Modal eliminar
        document.getElementById('confirmar-eliminar').addEventListener('click', () => this.eliminarProductoConfirmado());
    }

    setupDropzones() {
        this.setupDropzonePrincipal();
        this.setupDropzoneExtras();
    }

    setupDropzonePrincipal() {
        const dropzone = document.getElementById('dropzone-principal');
        const input = document.getElementById('producto-imagen-principal');
        const preview = document.getElementById('dz-preview-principal');

        const handleFiles = (files) => {
            if (files.length === 0) return;

            // Solo procesar la primera imagen
            const file = files[0];
            if (!file.type.startsWith('image/')) {
                this.mostrarError('Por favor, selecciona un archivo de imagen válido');
                return;
            }

            // Limpiar preview anterior
            preview.innerHTML = '';

            const url = URL.createObjectURL(file);
            const card = this.crearCardImagen(url, file.name, true);
            preview.appendChild(card);

            // Actualizar el input file
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        };

        // Eventos del dropzone
        dropzone.addEventListener('click', () => input.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.parentElement.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.parentElement.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.parentElement.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // Evento del input file
        input.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    setupDropzoneExtras() {
        const dropzone = document.getElementById('dropzone-extras');
        const input = document.getElementById('producto-imagenes');
        const preview = document.getElementById('dz-preview-extras');

        const handleFiles = (files) => {
            console.log('handleFiles llamado con files:', Array.from(files).map(f => ({ name: f.name, size: f.size })));

            const fileArray = Array.from(files).filter(file => file.type.startsWith('image/'));

            console.log('fileArray filtrado:', fileArray.map(f => f.name));

            if (fileArray.length === 0) return;

            fileArray.forEach(file => {
                console.log('Procesando file:', file.name);

                if (!this.newExtraImages.some(existing => existing.name === file.name && existing.size === file.size)) {
                    console.log('Agregando nuevo file:', file.name);
                    this.newExtraImages.push(file);

                    const url = URL.createObjectURL(file);
                    const card = this.crearCardImagen(url, file.name, false);
                    preview.appendChild(card);
                } else {
                    console.log('File duplicado detectado:', file.name);
                    this.mostrarError(`La imagen "${file.name}" ya fue agregada.`);
                }
            });

            console.log('Archivos acumulados después de agregar:', this.newExtraImages.map(f => f.name));
        };

        // Eventos del dropzone
        dropzone.addEventListener('click', () => input.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.parentElement.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.parentElement.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.parentElement.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // Evento del input file
        input.addEventListener('change', (e) => {
            handleFiles(e.target.files);
            // Limpiar el input después de procesar
            input.files = new DataTransfer().files;
        });
    }

    setupGallery() {
        // Navegación de la galería
        document.getElementById('fs-prev').addEventListener('click', () => this.galleryPrev());
        document.getElementById('fs-next').addEventListener('click', () => this.galleryNext());
        document.getElementById('fs-close').addEventListener('click', () => this.cerrarGallery());

        // Navegación con teclado
        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('fs-backdrop').classList.contains('active')) return;

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.galleryPrev();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.galleryNext();
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.cerrarGallery();
                    break;
            }
        });
    }

    crearCardImagen(url, filename, esPrincipal = false) {
        const card = document.createElement('div');
        card.className = `dz-item ${esPrincipal ? 'imagen-principal' : ''}`;

        card.innerHTML = `
        <img src="${url}" alt="${filename}">
        <div class="dz-actions">
            ${esPrincipal ? '<span class="dz-principal-badge">Principal</span>' : ''}
            <button type="button" class="dz-remove">Eliminar</button>
        </div>
    `;

        // Evento para eliminar la imagen
        card.querySelector('.dz-remove').addEventListener('click', (e) => {
            e.stopPropagation();
            card.remove();

            if (esPrincipal) {
                // Limpiar el input file principal
                document.getElementById('producto-imagen-principal').value = '';
            } else {
                // Remover del input files
                this.removerNuevaImagenExtra(filename);
            }
        });

        return card;
    }

    crearCardImagenExistente(imagen, producto, esPrincipal = false) {
        let url = imagen.path || '';
        if (!(url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/'))) {
            url = (window.RUTA_IMG || '/assets/img/') + url;
        }

        const card = document.createElement('div');
        card.className = `dz-item ${esPrincipal ? 'imagen-principal' : ''}`;
        card.dataset.imagenId = imagen.id;

        card.innerHTML = `
            <img src="${url}" alt="${this.escapeHtml(producto.nombre)}">
            <div class="dz-actions">
                ${esPrincipal ? '<span class="dz-principal-badge">Principal</span>' : ''}
                <button type="button" class="dz-remove">Eliminar</button>
            </div>
        `;

        // Evento para eliminar la imagen existente
        card.querySelector('.dz-remove').addEventListener('click', (e) => {
            e.stopPropagation();

            if (esPrincipal) {
                this.mostrarError('No puedes eliminar la imagen principal. Debes reemplazarla por una nueva.');
                return;
            }

            card.remove();
            this.marcarImagenParaEliminacion(imagen.id);
        });

        return card;
    }

    removerNuevaImagenExtra(filename) {
        const previousLength = this.newExtraImages.length;

        // Remover de la lista acumulada
        this.newExtraImages = this.newExtraImages.filter(file => file.name !== filename);

        if (this.newExtraImages.length < previousLength) {
            console.log('Archivo removido:', filename);
            console.log('Archivos acumulados después de remover:', this.newExtraImages.map(f => f.name));
        } else {
            console.log('Intento de remover archivo no encontrado:', filename);
        }
    }

    marcarImagenParaEliminacion(id) {
        let input = document.getElementById('imagenes_eliminar');
        const ids = input.value ? input.value.split(',').filter(i => i !== '') : [];

        if (!ids.includes(String(id))) {
            ids.push(String(id));
            input.value = ids.join(',');
        }
    }

    inicializarDataTables() {
        this.dataTable = $('#tabla-productos').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "get_productos",
                "type": "POST",
                "data": function (d) {
                    return {
                        accion: 'listar'
                    };
                },
                "dataSrc": function (json) {
                    try {
                        if (json && json.success) {
                            return json.data.productos;
                        }
                        console.error('Error cargando productos:', json && (json.data?.message || json.message));
                    } catch (e) {
                        console.error('Respuesta inválida al listar productos', e);
                    }
                    return [];
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "nombre" },
                {
                    "data": "descripcion",
                    "render": function (data) {
                        return data || '';
                    }
                },
                {
                    "data": "costo",
                    "render": function (data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                {
                    "data": "precio_venta",
                    "render": function (data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                {
                    "data": "categoria_nombre",
                    "render": function (data) {
                        return data || 'Sin categoría';
                    }
                },
                {
                    "data": "fecha_creado",
                    "render": function (data) {
                        return new Date(data).toLocaleString('es-ES', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                    }
                },
                {
                    "data": "id",
                    "render": function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-info detalles-producto" data-id="${data}" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning editar-producto" data-id="${data}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger eliminar-producto" data-id="${data}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    },
                    "orderable": false
                }
            ],
            "language": {
                "decimal": ",",
                "thousands": ".",
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            "responsive": true,
            "order": [[0, "desc"]],
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50]
        });

        // Event listeners para botones dinámicos
        $('#tabla-productos').on('click', '.detalles-producto', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarDetallesProducto(id);
        });

        $('#tabla-productos').on('click', '.editar-producto', (e) => {
            const id = $(e.currentTarget).data('id');
            this.editarProducto(id);
        });

        $('#tabla-productos').on('click', '.eliminar-producto', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarModalEliminar(id);
        });
    }

    async cargarCategoriasParaModal() {
        try {
            const params = new URLSearchParams();
            params.append('accion', 'listar');

            const response = await fetch('get_categorias', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarCategoriasEnSelect(data.categorias);
            } else {
                throw new Error(data.data?.message || 'Error al cargar categorías');
            }
        } catch (error) {
            console.error('Error cargando categorías:', error);
            await this.mostrarError('Error al cargar categorías: ' + error.message);
        }
    }

    mostrarCategoriasEnSelect(categorias) {
        const select = document.getElementById('producto-categoria');
        select.innerHTML = '<option value="">Seleccionar categoría...</option>' +
            categorias.map(cat =>
                `<option value="${cat.id}">${this.escapeHtml(cat.nombre)}</option>`
            ).join('');
    }

    async mostrarDetallesProducto(id) {
        try {
            const params = new URLSearchParams();
            params.append('accion', 'obtener');
            params.append('id', id);

            const response = await fetch('get_productos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });

            const data = await response.json();

            if (data.success) {
                this.currentProductoDetalles = data.data.producto;
                this.mostrarModalDetalles(this.currentProductoDetalles);
            } else {
                throw new Error(data.data?.message || 'Error al cargar detalles del producto');
            }
        } catch (error) {
            console.error('Error cargando detalles del producto:', error);
            await this.mostrarError('Error al cargar detalles del producto: ' + error.message);
        }
    }

    mostrarModalDetalles(producto) {
        // Calcular margen de ganancia
        const costo = parseFloat(producto.costo);
        const precio = parseFloat(producto.precio_venta);
        const margen = precio - costo;
        const porcentajeMargen = costo > 0 ? ((margen / costo) * 100).toFixed(2) : 0;

        // Llenar los detalles
        document.getElementById('detalle-id').textContent = producto.id;
        document.getElementById('detalle-nombre').textContent = producto.nombre;
        document.getElementById('detalle-descripcion').textContent = producto.descripcion || 'Sin descripción';
        document.getElementById('detalle-costo').textContent = `$${costo.toFixed(2)}`;
        document.getElementById('detalle-precio').textContent = `$${precio.toFixed(2)}`;
        document.getElementById('detalle-margen').textContent = `$${margen.toFixed(2)} (${porcentajeMargen}%)`;
        document.getElementById('detalle-categoria').textContent = producto.categoria_nombre || 'Sin categoría';
        document.getElementById('detalle-fecha').textContent = new Date(producto.fecha_creado).toLocaleString('es-ES');

        // Renderizar imágenes en layout de 2 columnas
        this.renderizarImagenesDetalle(producto);

        // Mostrar modal
        this.modalDetalles.show();
    }

    renderizarImagenesDetalle(producto) {
        const contenedor = document.getElementById('detalle-imagenes');
        contenedor.innerHTML = '';

        if (!producto.imagenes || producto.imagenes.length === 0) {
            contenedor.innerHTML = `
                <div class="sin-imagenes">
                    <i class="fas fa-image"></i>
                    <p>No hay imágenes para este producto</p>
                </div>
            `;
            return;
        }

        // Preparar datos para la galería
        this.galleryImages = producto.imagenes.map((img, index) => {
            let url = img.path || '';
            if (!(url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/'))) {
                url = (window.RUTA_IMG || '/assets/img/') + url;
            }
            return {
                url,
                es_principal: img.es_principal,
                nombre: this.obtenerNombreArchivo(img.path),
                index
            };
        });

        // Ordenar: imagen principal primero
        const imagenesOrdenadas = [...producto.imagenes].sort((a, b) => {
            if (a.es_principal && !b.es_principal) return -1;
            if (!a.es_principal && b.es_principal) return 1;
            return 0;
        });

        imagenesOrdenadas.forEach((img, index) => {
            let url = img.path || '';
            if (!(url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/'))) {
                url = (window.RUTA_IMG || '/assets/img/') + url;
            }

            const card = document.createElement('div');
            card.className = `imagen-card ${img.es_principal ? 'principal' : ''}`;

            // Extraer nombre del archivo para mostrar
            const nombreArchivo = this.obtenerNombreArchivo(img.path);

            card.innerHTML = `
                ${img.es_principal ? '<div class="imagen-badge">Principal</div>' : ''}
                <img src="${url}" alt="${this.escapeHtml(producto.nombre)} - Imagen ${index + 1}" 
                     title="Haz clic para ver en tamaño completo">
                <div class="imagen-info">
                    <p class="imagen-nombre" title="${nombreArchivo}">${nombreArchivo}</p>
                </div>
            `;

            // Evento para abrir en galería
            const imgElement = card.querySelector('img');
            const galleryIndex = this.galleryImages.findIndex(gImg => gImg.url === url);
            imgElement.addEventListener('click', () => this.abrirGallery(galleryIndex));

            contenedor.appendChild(card);
        });
    }

    abrirGallery(index) {
        this.currentGalleryIndex = index;
        this.actualizarVistaGallery();
        document.getElementById('fs-backdrop').classList.add('active');

        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
    }

    cerrarGallery() {
        document.getElementById('fs-backdrop').classList.remove('active');
        document.body.style.overflow = '';
    }

    galleryPrev() {
        if (this.galleryImages.length <= 1) return;
        this.currentGalleryIndex = (this.currentGalleryIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
        this.actualizarVistaGallery();
    }

    galleryNext() {
        if (this.galleryImages.length <= 1) return;
        this.currentGalleryIndex = (this.currentGalleryIndex + 1) % this.galleryImages.length;
        this.actualizarVistaGallery();
    }

    actualizarVistaGallery() {
        const currentImage = this.galleryImages[this.currentGalleryIndex];
        const imgElement = document.getElementById('fs-image');
        const counterElement = document.getElementById('fs-image-counter');
        const badgeElement = document.getElementById('fs-image-principal-badge');

        imgElement.src = currentImage.url;
        imgElement.alt = `Imagen ${this.currentGalleryIndex + 1} de ${this.galleryImages.length}`;

        // Actualizar contador
        counterElement.textContent = `${this.currentGalleryIndex + 1} / ${this.galleryImages.length}`;

        // Mostrar/ocultar badge de imagen principal
        if (currentImage.es_principal) {
            badgeElement.style.display = 'flex';
        } else {
            badgeElement.style.display = 'none';
        }

        // Mostrar/ocultar botones de navegación según sea necesario
        const prevBtn = document.getElementById('fs-prev');
        const nextBtn = document.getElementById('fs-next');

        if (this.galleryImages.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }

    obtenerNombreArchivo(path) {
        if (!path) return 'imagen.jpg';

        // Extraer el nombre del archivo de la ruta
        const partes = path.split('/');
        let nombre = partes[partes.length - 1];

        // Limitar longitud del nombre para mostrar
        if (nombre.length > 20) {
            nombre = nombre.substring(0, 17) + '...';
        }

        return nombre;
    }

    editarDesdeDetalles() {
        if (!this.currentProductoDetalles) return;

        // Guardar referencia antes de cerrar
        const productoAEditar = this.currentProductoDetalles;

        // Cerrar modal de detalles
        this.modalDetalles.hide();

        // Limpiar la referencia después de cerrar
        this.currentProductoDetalles = null;

        // Abrir modal de edición
        setTimeout(() => {
            this.mostrarModalProducto(productoAEditar);
        }, 300);
    }

    mostrarModalProducto(producto = null) {
        const titulo = document.getElementById('modalProductoLabel');
        const form = document.getElementById('form-producto');

        // Reset del formulario
        form.reset();
        document.getElementById('imagenes_eliminar').value = '';
        document.getElementById('imagen_principal_existente').value = '';

        // Limpiar previews
        document.getElementById('dz-preview-principal').innerHTML = '';
        document.getElementById('dz-preview-extras').innerHTML = '';
        document.getElementById('imagenes-principal-existente').innerHTML = '';
        document.getElementById('imagenes-extras-existente').innerHTML = '';

        // Limpiar inputs de archivos
        document.getElementById('producto-imagen-principal').value = '';
        document.getElementById('producto-imagenes').value = '';

        this.newExtraImages = [];  // Resetear la lista de nuevas imágenes extras

        if (producto) {
            titulo.textContent = 'Editar Producto';
            document.getElementById('producto-id').value = producto.id;
            document.getElementById('producto-nombre').value = producto.nombre;
            document.getElementById('producto-descripcion').value = producto.descripcion || '';
            document.getElementById('producto-costo').value = producto.costo;
            document.getElementById('producto-precio').value = producto.precio_venta;
            document.getElementById('producto-categoria').value = producto.categoria_id;

            // Mostrar imágenes existentes
            this.mostrarImagenesExistentes(producto);
        } else {
            titulo.textContent = 'Agregar Producto';
            document.getElementById('producto-id').value = '';
        }

        this.modalProducto.show();
    }

    mostrarImagenesExistentes(producto) {
        if (!producto.imagenes || producto.imagenes.length === 0) return;

        const imagenPrincipal = producto.imagenes.find(img => img.es_principal == 1);
        const imagenesExtras = producto.imagenes.filter(img => img.es_principal != 1);

        // Mostrar imagen principal existente
        if (imagenPrincipal) {
            const contenedor = document.getElementById('imagenes-principal-existente');
            const card = this.crearCardImagenExistente(imagenPrincipal, producto, true);
            contenedor.appendChild(card);

            // Marcar que existe una imagen principal
            document.getElementById('imagen_principal_existente').value = imagenPrincipal.id;
        }

        // Mostrar imágenes extras existentes
        if (imagenesExtras.length > 0) {
            const contenedor = document.getElementById('imagenes-extras-existente');
            imagenesExtras.forEach(imagen => {
                const card = this.crearCardImagenExistente(imagen, producto, false);
                contenedor.appendChild(card);
            });
        }
    }

    async guardarProducto(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const productoId = document.getElementById('producto-id').value;

        console.log('Archivos acumulados antes de append:', this.newExtraImages.map(f => ({ name: f.name, size: f.size })));

        // Agregar manualmente las imágenes extras a formData
        this.newExtraImages.forEach(file => {
            console.log('Append file a formData:', file.name);
            formData.append('imagenes[]', file);
        });

        console.log('Número de imágenes extras a subir:', this.newExtraImages.length);

        try {
            let url;

            if (productoId) {
                formData.append('accion', 'actualizar');
                formData.append('id', productoId);
                url = 'actualizar_producto';
            } else {
                formData.append('accion', 'crear');
                url = 'crear_producto';
            }

            // Validar que haya al menos una imagen (principal) en creación
            const tieneImagenPrincipalExistente = document.getElementById('imagen_principal_existente').value !== '';
            const tieneNuevaImagenPrincipal = document.getElementById('producto-imagen-principal').files.length > 0;

            console.log('Validación imagen principal:', { tieneImagenPrincipalExistente, tieneNuevaImagenPrincipal });

            if (!productoId && !tieneNuevaImagenPrincipal) {
                await this.mostrarError('Debe seleccionar una imagen principal para el producto');
                return;
            }

            // Validar que en edición haya al menos una imagen principal (existente o nueva)
            if (productoId && !tieneImagenPrincipalExistente && !tieneNuevaImagenPrincipal) {
                await this.mostrarError('El producto debe tener al menos una imagen principal');
                return;
            }

            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            console.log('Respuesta del servidor status:', response.status);

            let data;
            try {
                data = await response.json();
                console.log('Respuesta JSON:', data);
            } catch (e) {
                const text = await response.text();
                console.log('Respuesta no JSON:', text);
                throw new Error('Respuesta no-JSON del servidor: ' + text.slice(0, 200));
            }

            if (data.success) {
                await this.mostrarExito(data.data.message || 'Operación exitosa');
                this.modalProducto.hide();
                this.dataTable.ajax.reload();
            } else {
                throw new Error(data.data?.message || 'Error en la operación');
            }

        } catch (error) {
            console.error('Error guardando producto:', error);
            await this.mostrarError('Error al guardar producto: ' + error.message);
        }
    }

    async editarProducto(id) {
        try {
            const params = new URLSearchParams();
            params.append('accion', 'obtener');
            params.append('id', id);

            const response = await fetch('get_productos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarModalProducto(data.data.producto);
            } else {
                throw new Error(data.data?.message || 'Error al cargar producto');
            }
        } catch (error) {
            console.error('Error cargando producto:', error);
            await this.mostrarError('Error al cargar producto: ' + error.message);
        }
    }

    mostrarModalEliminar(id) {
        this.productoAEliminar = id;
        this.modalEliminar.show();
    }

    async eliminarProductoConfirmado() {
        if (!this.productoAEliminar) {
            console.error('No hay producto seleccionado para eliminar');
            return;
        }

        try {
            const params = new URLSearchParams();
            params.append('accion', 'eliminar');
            params.append('id', this.productoAEliminar);

            const response = await fetch('eliminar_producto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });

            const data = await response.json();

            if (data.success) {
                await this.mostrarExito(data.message || 'Producto eliminado exitosamente');
                this.modalEliminar.hide();
                this.dataTable.ajax.reload();
            } else {
                throw new Error(data.message || 'Error al eliminar producto');
            }

        } catch (error) {
            console.error('Error eliminando producto:', error);
            await this.mostrarError('Error al eliminar producto: ' + error.message);
        }
    }

    async mostrarExito(mensaje) {
        await Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: mensaje,
            confirmButtonColor: '#3AC47D',
            timer: 2000,
            showConfirmButton: false
        });
    }

    async mostrarError(mensaje) {
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            confirmButtonColor: '#3AC47D'
        });
    }

    escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.productosManager = new ProductosManager();
});