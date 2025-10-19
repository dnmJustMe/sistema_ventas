// assets/js/ofertas.js
class OfertasManager {
    constructor() {
        this.ofertaAEliminar = null;
        this.dataTable = null;
        this.currentOfertaDetalles = null;
        this.productosAgregados = [];
        this.modalOferta = null;
        this.modalDetalles = null;
        this.modalEliminar = null;
        this.galleryImages = [];
        this.currentGalleryIndex = 0;
        this.init();
    }

    init() {
        this.inicializarModalesBootstrap();
        this.inicializarDataTables();
        this.setupEventListeners();
        this.setupDropzone();
        this.setupGallery();
    }

    inicializarModalesBootstrap() {
        this.modalOferta = new bootstrap.Modal(document.getElementById('modal-oferta'));
        this.modalDetalles = new bootstrap.Modal(document.getElementById('modal-detalles-oferta'));
        this.modalEliminar = new bootstrap.Modal(document.getElementById('modal-confirmar-eliminar'));
    }

    setupEventListeners() {
        document.getElementById('btn-agregar-oferta').addEventListener('click', () => this.mostrarModalOferta());
        document.getElementById('form-oferta').addEventListener('submit', (e) => this.guardarOferta(e));
        document.getElementById('editar-desde-detalles').addEventListener('click', () => this.editarDesdeDetalles());
        document.getElementById('confirmar-eliminar').addEventListener('click', () => this.eliminarOfertaConfirmado());
        document.getElementById('btn-buscar-producto').addEventListener('click', () => this.buscarProductos());
        document.getElementById('buscar-producto').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.buscarProductos();
            }
        });
    }

    setupDropzone() {
        const dropzone = document.getElementById('dropzone-principal');
        const input = document.getElementById('oferta-imagen-principal');
        const preview = document.getElementById('dz-preview-principal');

        const handleFiles = (files) => {
            if (files.length === 0) return;

            const file = files[0];
            if (!file.type.startsWith('image/')) {
                this.mostrarError('Por favor, selecciona un archivo de imagen válido');
                return;
            }

            preview.innerHTML = '';
            const url = URL.createObjectURL(file);
            const card = this.crearCardImagen(url, file.name);
            preview.appendChild(card);

            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        };

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
        input.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    setupGallery() {
        // Navegación de la galería
        document.getElementById('fs-prev').addEventListener('click', () => this.galleryPrev());
        document.getElementById('fs-next').addEventListener('click', () => this.galleryNext());
        document.getElementById('fs-close').addEventListener('click', () => this.cerrarGallery());

        // Cerrar haciendo clic fuera de la imagen
        document.getElementById('fs-backdrop').addEventListener('click', (e) => {
            if (e.target.id === 'fs-backdrop') {
                this.cerrarGallery();
            }
        });

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

    crearCardImagen(url, filename) {
        const card = document.createElement('div');
        card.className = 'dz-item imagen-principal';
        card.innerHTML = `
            <img src="${url}" alt="${filename}">
            <div class="dz-actions">
                <span class="dz-principal-badge">Principal</span>
                <button type="button" class="dz-remove">Eliminar</button>
            </div>
        `;

        card.querySelector('.dz-remove').addEventListener('click', (e) => {
            e.stopPropagation();
            card.remove();
            document.getElementById('oferta-imagen-principal').value = '';
        });

        return card;
    }

    inicializarDataTables() {
        this.dataTable = $('#tabla-ofertas').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "get_ofertas",
                "type": "POST",
                "data": function (d) {
                    return {
                        accion: 'listar'
                    };
                },
                "dataSrc": function (json) {
                    try {
                        if (json && json.success) {
                            return json.data.ofertas;
                        }
                        console.error('Error cargando ofertas:', json && (json.data?.message || json.message));
                    } catch (e) {
                        console.error('Respuesta inválida al listar ofertas', e);
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
                        return data || '<span class="text-muted">Sin descripción</span>';
                    }
                },
                {
                    "data": "precio_final_venta",
                    "render": function (data) {
                        return `<span class="fw-bold text-success">$${parseFloat(data).toFixed(2)}</span>`;
                    }
                },
                {
                    "data": "productos_count",
                    "render": function (data) {
                        return `<span class="badge bg-primary">${data} producto${data !== 1 ? 's' : ''}</span>`;
                    }
                },
                {
                    "data": "fecha_creado",
                    "render": function (data) {
                        return new Date(data).toLocaleString('es-ES');
                    }
                },
                {
                    "data": "id",
                    "render": function (data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info detalles-oferta" data-id="${data}" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning editar-oferta" data-id="${data}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger eliminar-oferta" data-id="${data}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
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
                }
            },
            "responsive": true,
            "order": [[0, "desc"]],
            "pageLength": 10
        });

        $('#tabla-ofertas').on('click', '.detalles-oferta', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarDetallesOferta(id);
        });

        $('#tabla-ofertas').on('click', '.editar-oferta', (e) => {
            const id = $(e.currentTarget).data('id');
            this.editarOferta(id);
        });

        $('#tabla-ofertas').on('click', '.eliminar-oferta', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarModalEliminar(id);
        });
    }

    mostrarModalOferta(oferta = null) {
        this.resetFormulario();
        
        if (oferta) {
            document.getElementById('modalOfertaLabel').textContent = 'Editar Oferta';
            document.getElementById('guardar-oferta').innerHTML = '<i class="fas fa-save"></i> Actualizar Oferta';
            this.cargarDatosEnFormulario(oferta);
        } else {
            document.getElementById('modalOfertaLabel').textContent = 'Agregar Oferta';
            document.getElementById('guardar-oferta').innerHTML = '<i class="fas fa-save"></i> Guardar Oferta';
        }
        
        this.modalOferta.show();
    }

    resetFormulario() {
        document.getElementById('form-oferta').reset();
        document.getElementById('oferta-id').value = '';
        document.getElementById('dz-preview-principal').innerHTML = '';
        document.getElementById('oferta-imagen-principal').value = '';
        document.getElementById('imagenes-principal-existente').innerHTML = '';
        document.getElementById('resultados-busqueda').style.display = 'none';
        document.getElementById('lista-resultados').innerHTML = '';
        document.getElementById('buscar-producto').value = '';
        this.productosAgregados = [];
        this.actualizarTablaProductos();
    }

    async buscarProductos() {
        const busqueda = document.getElementById('buscar-producto').value.trim();
        if (!busqueda) {
            this.mostrarError('Por favor, ingresa un término de búsqueda');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('accion', 'listar');
            formData.append('busqueda', busqueda);

            const response = await fetch('get_productos', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success && data.data.productos && data.data.productos.length > 0) {
                this.mostrarResultadosBusqueda(data.data.productos);
            } else {
                this.mostrarError('No se encontraron productos con ese término de búsqueda');
            }
        } catch (error) {
            console.error('Error buscando productos:', error);
            this.mostrarError('Error al buscar productos');
        }
    }

    mostrarResultadosBusqueda(productos) {
        const resultadosDiv = document.getElementById('resultados-busqueda');
        const listaResultados = document.getElementById('lista-resultados');
        
        listaResultados.innerHTML = productos.map(producto => `
            <div class="col-md-6">
                <div class="producto-resultado" data-producto='${JSON.stringify(producto)}'>
                    <div class="producto-info">
                        <div class="producto-nombre">${producto.nombre}</div>
                        <div class="producto-precio">$${parseFloat(producto.precio_venta).toFixed(2)} | ${producto.categoria_nombre}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success agregar-producto">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
            </div>
        `).join('');

        resultadosDiv.style.display = 'block';

        // Event listeners para agregar productos
        document.querySelectorAll('.agregar-producto').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productoDiv = e.target.closest('.producto-resultado');
                const producto = JSON.parse(productoDiv.dataset.producto);
                this.agregarProducto(producto);
                productoDiv.remove();
                
                // Ocultar resultados si no hay más
                if (document.querySelectorAll('.producto-resultado').length === 0) {
                    document.getElementById('resultados-busqueda').style.display = 'none';
                }
            });
        });
    }

    agregarProducto(producto) {
        // Verificar si el producto ya está agregado
        if (this.productosAgregados.find(p => p.id === producto.id)) {
            this.mostrarError('Este producto ya está en la oferta');
            return;
        }

        this.productosAgregados.push({
            id: producto.id,
            nombre: producto.nombre,
            precio_venta: producto.precio_venta,
            cantidad: 1
        });

        this.actualizarTablaProductos();
    }

    actualizarTablaProductos() {
        const tbody = document.getElementById('productos-agregados');
        const footer = document.getElementById('footer-productos');
        const contador = document.getElementById('contador-productos');
        
        // Actualizar campo hidden con los productos
        document.getElementById('productos_oferta').value = JSON.stringify(this.productosAgregados);

        // Actualizar contador
        contador.textContent = `${this.productosAgregados.length} producto${this.productosAgregados.length !== 1 ? 's' : ''}`;

        if (this.productosAgregados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                        No hay productos agregados
                    </td>
                </tr>
            `;
            footer.style.display = 'none';
            return;
        }

        let totalOferta = 0;
        
        tbody.innerHTML = this.productosAgregados.map((producto, index) => {
            const subtotal = producto.precio_venta * producto.cantidad;
            totalOferta += subtotal;
            
            return `
            <tr>
                <td>
                    <div class="producto-info">
                        <div class="producto-nombre">${producto.nombre}</div>
                    </div>
                </td>
                <td>$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm cantidad-producto" 
                           value="${producto.cantidad}" min="1" data-index="${index}">
                </td>
                <td class="fw-bold">$${subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger eliminar-producto" data-index="${index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `}).join('');

        // Actualizar total
        document.getElementById('total-oferta').textContent = `$${totalOferta.toFixed(2)}`;
        footer.style.display = 'table-row-group';

        // Event listeners para controles de cantidad
        document.querySelectorAll('.cantidad-producto').forEach(input => {
            input.addEventListener('change', (e) => {
                const index = parseInt(e.target.dataset.index);
                const cantidad = parseInt(e.target.value);
                if (cantidad > 0) {
                    this.productosAgregados[index].cantidad = cantidad;
                    this.actualizarTablaProductos(); // Para actualizar el campo hidden y los totales
                } else {
                    e.target.value = this.productosAgregados[index].cantidad;
                }
            });
        });

        // Event listeners para botones eliminar
        document.querySelectorAll('.eliminar-producto').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('button').dataset.index);
                this.productosAgregados.splice(index, 1);
                this.actualizarTablaProductos();
            });
        });
    }

    async guardarOferta(e) {
        e.preventDefault();

        // Validaciones básicas
        if (this.productosAgregados.length === 0) {
            this.mostrarError('Debe agregar al menos un producto a la oferta');
            return;
        }

        const tieneImagenPrincipal = document.getElementById('oferta-imagen-principal').files.length > 0 || 
                                   document.getElementById('imagenes-principal-existente').innerHTML !== '';

        if (!tieneImagenPrincipal) {
            this.mostrarError('Debe seleccionar una imagen principal para la oferta');
            return;
        }

        const formData = new FormData(e.target);
        const ofertaId = document.getElementById('oferta-id').value;

        if (ofertaId) {
            formData.append('accion', 'actualizar');
            formData.append('id', ofertaId);
        } else {
            formData.append('accion', 'crear');
        }

        // Los productos ya están en el campo hidden 'productos_oferta'

        try {
            const response = await fetch('get_ofertas', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarExito(data.data?.message || 'Operación exitosa');
                this.modalOferta.hide();
                this.dataTable.ajax.reload();
            } else {
                throw new Error(data.data?.message || data.message || 'Error en la operación');
            }

        } catch (error) {
            console.error('Error guardando oferta:', error);
            this.mostrarError('Error al guardar oferta: ' + error.message);
        }
    }

    async mostrarDetallesOferta(id) {
        try {
            const formData = new FormData();
            formData.append('accion', 'obtener');
            formData.append('id', id);

            const response = await fetch('get_ofertas', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.currentOfertaDetalles = data.data.oferta;
                this.mostrarDetallesEnModal();
                this.modalDetalles.show();
            } else {
                throw new Error(data.data?.message || 'Error al cargar detalles de la oferta');
            }
        } catch (error) {
            console.error('Error cargando detalles de la oferta:', error);
            this.mostrarError('Error al cargar detalles de la oferta: ' + error.message);
        }
    }

    mostrarDetallesEnModal() {
        const oferta = this.currentOfertaDetalles;
        
        document.getElementById('detalle-id').textContent = oferta.id;
        document.getElementById('detalle-nombre').textContent = oferta.nombre;
        document.getElementById('detalle-descripcion').textContent = oferta.descripcion || 'Sin descripción';
        document.getElementById('detalle-precio').textContent = `$${parseFloat(oferta.precio_final_venta).toFixed(2)}`;
        document.getElementById('detalle-fecha').textContent = new Date(oferta.fecha_creado).toLocaleString('es-ES');

        // Preparar imágenes para la galería
        this.galleryImages = [];
        this.currentGalleryIndex = 0;

        // Imagen principal
        const imagenContainer = document.getElementById('detalle-imagen');
        if (oferta.imagen_principal) {
            const rutaImagen = oferta.imagen_principal.startsWith('http') ? oferta.imagen_principal : (RUTA_IMG + oferta.imagen_principal);
            imagenContainer.innerHTML = `
                <img src="${rutaImagen}" 
                     alt="${oferta.nombre}" 
                     class="img-fluid"
                     onclick="ofertasManager.abrirGallery('${rutaImagen}', '${oferta.nombre}')">
            `;
            
            // Agregar a la galería
            this.galleryImages.push({
                url: rutaImagen,
                title: oferta.nombre
            });
        } else {
            imagenContainer.innerHTML = '<span class="text-muted">Sin imagen</span>';
        }

        // Productos
        const productosContainer = document.getElementById('detalle-productos');
        if (oferta.productos && oferta.productos.length > 0) {
            productosContainer.innerHTML = oferta.productos.map(producto => {
                const imagenProducto = producto.imagen_principal ? 
                    (producto.imagen_principal.startsWith('http') ? producto.imagen_principal : RUTA_IMG + producto.imagen_principal) : 
                    RUTA_IMG + 'placeholder.jpg';
                
                // Agregar imágenes de productos a la galería
                this.galleryImages.push({
                    url: imagenProducto,
                    title: producto.nombre
                });
                
                const galleryIndex = this.galleryImages.length - 1;
                
                return `
                <div class="producto-detalle">
                    <div style="display: flex; align-items: center;">
                        <img src="${imagenProducto}" 
                             alt="${producto.nombre}" 
                             class="producto-imagen-mini"
                             onclick="ofertasManager.abrirGalleryIndex(${galleryIndex})">
                        <div class="producto-detalle-info">
                            <div class="producto-nombre">${producto.nombre}</div>
                            <div class="producto-precio">Precio individual: $${parseFloat(producto.precio_venta).toFixed(2)}</div>
                        </div>
                    </div>
                    <div class="producto-detalle-cantidad">x${producto.cantidad}</div>
                </div>
            `}).join('');
        } else {
            productosContainer.innerHTML = '<span class="text-muted">No hay productos en esta oferta</span>';
        }
    }

    abrirGallery(src, title) {
        // Encontrar el índice de la imagen en el array
        const index = this.galleryImages.findIndex(img => img.url === src);
        if (index !== -1) {
            this.abrirGalleryIndex(index);
        }
    }

    abrirGalleryIndex(index) {
        this.currentGalleryIndex = index;
        this.actualizarVistaGallery();
        document.getElementById('fs-backdrop').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    actualizarVistaGallery() {
        if (this.galleryImages.length === 0) return;

        const currentImage = this.galleryImages[this.currentGalleryIndex];
        const imgElement = document.getElementById('fs-image');
        const counterElement = document.getElementById('fs-image-counter');
        const titleElement = document.getElementById('fs-image-title');

        imgElement.src = currentImage.url;
        imgElement.alt = currentImage.title;
        counterElement.textContent = `${this.currentGalleryIndex + 1} / ${this.galleryImages.length}`;
        titleElement.textContent = currentImage.title;

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

    cerrarGallery() {
        document.getElementById('fs-backdrop').classList.remove('active');
        document.body.style.overflow = '';
    }

    editarDesdeDetalles() {
        if (this.currentOfertaDetalles) {
            this.editarOferta(this.currentOfertaDetalles.id);
        }
        this.modalDetalles.hide();
    }

    async editarOferta(id) {
        try {
            const formData = new FormData();
            formData.append('accion', 'obtener');
            formData.append('id', id);

            const response = await fetch('get_ofertas', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarModalOferta(data.data.oferta);
            } else {
                throw new Error(data.data?.message || 'Error al cargar la oferta para editar');
            }
        } catch (error) {
            console.error('Error cargando oferta para editar:', error);
            this.mostrarError('Error al cargar la oferta: ' + error.message);
        }
    }

    cargarDatosEnFormulario(oferta) {
        document.getElementById('oferta-id').value = oferta.id;
        document.getElementById('oferta-nombre').value = oferta.nombre;
        document.getElementById('oferta-descripcion').value = oferta.descripcion || '';
        document.getElementById('oferta-precio').value = oferta.precio_final_venta;

        // Cargar productos
        this.productosAgregados = oferta.productos || [];
        this.actualizarTablaProductos();

        // Cargar imagen existente
        const existente = document.getElementById('imagenes-principal-existente');
        
        if (oferta.imagen_principal) {
            const rutaImagen = oferta.imagen_principal.startsWith('http') ? oferta.imagen_principal : (RUTA_IMG + oferta.imagen_principal);
            existente.innerHTML = `
                <div class="imagen-existente-info">
                    <small class="text-muted">Imagen actual:</small>
                    <div class="imagen-miniatura">
                        <img src="${rutaImagen}" alt="Imagen actual" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <small class="text-info">Selecciona una nueva imagen para reemplazar</small>
                </div>
            `;
        }
    }

    mostrarModalEliminar(id) {
        this.ofertaAEliminar = id;
        this.modalEliminar.show();
    }

    async eliminarOfertaConfirmado() {
        if (!this.ofertaAEliminar) return;

        try {
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', this.ofertaAEliminar);

            const response = await fetch('get_ofertas', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarExito(data.message || 'Oferta eliminada correctamente');
                this.modalEliminar.hide();
                this.dataTable.ajax.reload();
            } else {
                throw new Error(data.message || 'Error al eliminar la oferta');
            }
        } catch (error) {
            console.error('Error eliminando oferta:', error);
            this.mostrarError('Error al eliminar la oferta: ' + error.message);
        } finally {
            this.ofertaAEliminar = null;
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
    window.ofertasManager = new OfertasManager();
});