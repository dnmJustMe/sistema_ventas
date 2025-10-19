// servicios.js
class ServiciosManager {
    constructor() {
        this.tabla = null;
        this.servicioEditando = null;
        this.imagenesAEliminar = [];
        this.imagenPrincipalExistente = null;
        this.imagenesFullscreen = [];
        this.currentFullscreenIndex = 0;
        
        // Arrays para almacenar archivos seleccionados
        this.archivosPrincipales = [];
        this.archivosExtras = [];
        
        this.init();
    }

    init() {
        this.inicializarTabla();
        this.inicializarEventos();
        this.inicializarDropzones();
        this.inicializarFullscreenGallery();
    }

    inicializarTabla() {
        this.tabla = $('#tabla-servicios').DataTable({
            responsive: true,
            ajax: {
                url: 'get_servicios',
                type: 'POST',
                data: { accion: 'listar' },
                dataSrc: 'data.servicios'
            },
            columns: [
                { data: 'id' },
                { data: 'nombre' },
                { 
                    data: 'descripcion',
                    render: function(data) {
                        return data && data.length > 100 ? data.substring(0, 100) + '...' : data;
                    }
                },
                { 
                    data: 'fecha_creado',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString('es-ES') : '-';
                    }
                },
                {
                    data: null,
                    render: (data) => this.renderAcciones(data),
                    orderable: false
                }
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            pageLength: 10
        });
    }

    renderAcciones(data) {
        return `
            <div class="btn-group">
                <button class="btn btn-info btn-sm ver-servicio" data-id="${data.id}" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm editar-servicio" data-id="${data.id}" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm eliminar-servicio" data-id="${data.id}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }

    inicializarEventos() {
        // Botón agregar servicio
        $('#btn-agregar-servicio').click(() => this.mostrarModalAgregar());

        // Formulario servicio
        $('#form-servicio').submit((e) => this.guardarServicio(e));

        // Botón editar desde detalles
        $('#editar-desde-detalles').click(() => this.editarDesdeDetalles());

        // Delegación de eventos para la tabla
        $('#tabla-servicios').on('click', '.ver-servicio', (e) => {
            const id = $(e.currentTarget).data('id');
            this.verDetalles(id);
        });

        $('#tabla-servicios').on('click', '.editar-servicio', (e) => {
            const id = $(e.currentTarget).data('id');
            this.editarServicio(id);
        });

        $('#tabla-servicios').on('click', '.eliminar-servicio', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarModalEliminar(id);
        });

        // Confirmar eliminación
        $('#confirmar-eliminar').click(() => this.eliminarServicio());

        // Limpiar archivos cuando se cierre el modal
        $('#modal-servicio').on('hidden.bs.modal', () => {
            this.limpiarArchivosTemporales();
        });
    }

    inicializarDropzones() {
        this.inicializarDropzonePrincipal();
        this.inicializarDropzoneExtras();
    }

    inicializarDropzonePrincipal() {
        const dropzone = $('#dropzone-principal');
        const fileInput = $('#servicio-imagen-principal');
        const preview = $('#dz-preview-principal');

        // Click para seleccionar archivo
        dropzone.on('click', () => fileInput.click());

        // Cambio de archivo
        fileInput.on('change', (e) => {
            if (e.target.files.length > 0) {
                // Limpiar archivos anteriores
                this.archivosPrincipales = [];
                preview.empty();
                
                // Procesar el nuevo archivo
                Array.from(e.target.files).forEach(file => {
                    this.procesarImagenPrincipal(file, preview);
                });
            }
        });

        // Drag and drop
        this.configurarDragDrop(dropzone, fileInput, (files) => {
            if (files.length > 0) {
                // Limpiar archivos anteriores
                this.archivosPrincipales = [];
                preview.empty();
                
                // Procesar el nuevo archivo
                Array.from(files).forEach(file => {
                    this.procesarImagenPrincipal(file, preview);
                });
            }
        });
    }

    inicializarDropzoneExtras() {
        const dropzone = $('#dropzone-extras');
        const fileInput = $('#servicio-imagenes');
        const preview = $('#dz-preview-extras');

        // Click para seleccionar archivos
        dropzone.on('click', () => fileInput.click());

        // Cambio de archivos
        fileInput.on('change', (e) => {
            if (e.target.files.length > 0) {
                Array.from(e.target.files).forEach(file => {
                    this.procesarImagenExtra(file, preview);
                });
            }
        });

        // Drag and drop
        this.configurarDragDrop(dropzone, fileInput, (files) => {
            Array.from(files).forEach(file => {
                this.procesarImagenExtra(file, preview);
            });
        });
    }

    configurarDragDrop(dropzone, fileInput, callback) {
        dropzone.on('dragover', (e) => {
            e.preventDefault();
            dropzone.parent().addClass('dragover');
        });

        dropzone.on('dragleave', (e) => {
            e.preventDefault();
            dropzone.parent().removeClass('dragover');
        });

        dropzone.on('drop', (e) => {
            e.preventDefault();
            dropzone.parent().removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                callback(files);
            }
        });
    }

    procesarImagenPrincipal(file, preview) {
        if (!this.validarImagen(file)) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const imageId = 'img_principal_' + Date.now();
            
            // Guardar el archivo
            this.archivosPrincipales.push({
                id: imageId,
                file: file,
                preview: e.target.result
            });

            const previewHtml = `
                <div class="dz-item" data-id="${imageId}">
                    <img src="${e.target.result}" alt="Preview">
                    <div class="dz-actions">
                        <span class="dz-principal">
                            <i class="fas fa-star"></i> Principal
                        </span>
                        <button type="button" class="dz-remove" onclick="serviciosManager.removerImagenPreview('${imageId}', 'principal')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            preview.html(previewHtml);
        };
        reader.readAsDataURL(file);
    }

    procesarImagenExtra(file, preview) {
        if (!this.validarImagen(file)) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const imageId = 'img_extra_' + Date.now();
            
            // Guardar el archivo
            this.archivosExtras.push({
                id: imageId,
                file: file,
                preview: e.target.result
            });

            const previewHtml = `
                <div class="dz-item" data-id="${imageId}">
                    <img src="${e.target.result}" alt="Preview">
                    <div class="dz-actions">
                        <button type="button" class="dz-remove" onclick="serviciosManager.removerImagenPreview('${imageId}', 'extra')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            preview.append(previewHtml);
        };
        reader.readAsDataURL(file);
    }

    validarImagen(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (file.size > maxSize) {
            Swal.fire('Error', 'La imagen no puede ser mayor a 5MB', 'error');
            return false;
        }

        if (!allowedTypes.includes(file.type)) {
            Swal.fire('Error', 'Solo se permiten imágenes JPG, PNG, WEBP o GIF', 'error');
            return false;
        }

        return true;
    }

    removerImagenPreview(imageId, tipo) {
        $(`[data-id="${imageId}"]`).remove();
        
        if (tipo === 'principal') {
            this.archivosPrincipales = this.archivosPrincipales.filter(img => img.id !== imageId);
            $('#servicio-imagen-principal').val('');
        } else if (tipo === 'extra') {
            this.archivosExtras = this.archivosExtras.filter(img => img.id !== imageId);
        }
    }

    limpiarArchivosTemporales() {
        this.archivosPrincipales = [];
        this.archivosExtras = [];
    }

    mostrarModalAgregar() {
        this.limpiarFormulario();
        $('#modalServicioLabel').text('Agregar Servicio');
        $('#modal-servicio').modal('show');
    }

    limpiarFormulario() {
        $('#form-servicio')[0].reset();
        $('#servicio-id').val('');
        $('#dz-preview-principal').empty();
        $('#dz-preview-extras').empty();
        $('#imagenes-principal-existente').empty();
        $('#imagenes-extras-existente').empty();
        $('#imagenes_eliminar').val('');
        $('#imagen_principal_existente').val('');
        this.servicioEditando = null;
        this.imagenesAEliminar = [];
        this.imagenPrincipalExistente = null;
        this.limpiarArchivosTemporales();
    }

    async guardarServicio(e) {
        e.preventDefault();

        const formData = new FormData();
        const esEdicion = $('#servicio-id').val() !== '';

        // Datos básicos del servicio
        formData.append('accion', esEdicion ? 'actualizar' : 'crear');
        if (esEdicion) {
            formData.append('id', $('#servicio-id').val());
        }
        formData.append('nombre', $('#servicio-nombre').val());
        formData.append('descripcion', $('#servicio-descripcion').val());

        // Procesar imagen principal
        if (this.archivosPrincipales.length > 0) {
            const archivoPrincipal = this.archivosPrincipales[0];
            formData.append('imagen_principal_file', archivoPrincipal.file);
        } else if (!esEdicion) {
            // En creación, la imagen principal es obligatoria
            Swal.fire('Error', 'La imagen principal es obligatoria', 'error');
            return;
        }

        // Procesar imágenes extras
        this.archivosExtras.forEach((archivo, index) => {
            formData.append('imagenes[]', archivo.file);
        });

        // Para edición: imágenes a eliminar y principal existente
        if (esEdicion) {
            if (this.imagenesAEliminar.length > 0) {
                formData.append('imagenes_eliminar', this.imagenesAEliminar.join(','));
            }
            if (this.imagenPrincipalExistente) {
                formData.append('imagen_principal_existente', this.imagenPrincipalExistente);
            }
        }

        try {
            const response = await $.ajax({
                url: esEdicion ? 'actualizar_servicio' : 'crear_servicio',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.data.message || 'Servicio guardado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });

                $('#modal-servicio').modal('hide');
                this.tabla.ajax.reload();
                this.limpiarArchivosTemporales();
            } else {
                throw new Error(response.data?.message || 'Error al guardar el servicio');
            }
        } catch (error) {
            console.error('Error al guardar servicio:', error);
            Swal.fire('Error', error.responseJSON?.data?.message || error.message || 'Error desconocido', 'error');
        }
    }

    async verDetalles(id) {
        try {
            const response = await $.post('get_servicio', { 
                accion: 'obtener', 
                id: id 
            });

            if (response.success) {
                this.mostrarDetalles(response.data.servicio);
            } else {
                throw new Error(response.data?.message || 'Error al cargar los detalles');
            }
        } catch (error) {
            console.error('Error al ver detalles:', error);
            Swal.fire('Error', error.responseJSON?.data?.message || error.message || 'Error desconocido', 'error');
        }
    }

    mostrarDetalles(servicio) {
        $('#detalle-id').text(servicio.id);
        $('#detalle-nombre').text(servicio.nombre);
        $('#detalle-descripcion').text(servicio.descripcion || 'Sin descripción');
        $('#detalle-fecha').text(new Date(servicio.fecha_creado).toLocaleDateString('es-ES'));

        // Mostrar imágenes
        this.mostrarImagenesDetalles(servicio.imagenes);

        // Guardar referencia para editar
        this.servicioEditando = servicio;

        $('#modal-detalles-servicio').modal('show');
    }

    mostrarImagenesDetalles(imagenes) {
        const container = $('#detalle-imagenes');
        container.empty();

        if (!imagenes || imagenes.length === 0) {
            container.html(`
                <div class="sin-imagenes">
                    <i class="fas fa-image"></i>
                    <p>No hay imágenes para este servicio</p>
                </div>
            `);
            return;
        }

        imagenes.forEach((imagen, index) => {
            const esPrincipal = imagen.es_principal;
            const imagenHtml = `
                <div class="imagen-card ${esPrincipal ? 'principal' : ''}" data-index="${index}">
                    ${esPrincipal ? '<div class="imagen-badge">Principal</div>' : ''}
                    <img src="${RUTA_IMG}${imagen.path}" 
                         alt="Imagen del servicio" 
                         onclick="serviciosManager.abrirFullscreen(${index})">
                    <div class="imagen-info">
                        <p class="imagen-nombre">${imagen.path}</p>
                    </div>
                </div>
            `;
            container.append(imagenHtml);
        });

        // Guardar referencia para fullscreen gallery
        this.imagenesFullscreen = imagenes;
    }

    abrirFullscreen(index) {
        this.currentFullscreenIndex = index;
        this.actualizarFullscreenGallery();
        $('#fs-backdrop').addClass('active');
    }

    inicializarFullscreenGallery() {
        $('#fs-prev').click(() => this.navegarFullscreen(-1));
        $('#fs-next').click(() => this.navegarFullscreen(1));
        $('#fs-close').click(() => this.cerrarFullscreen());

        // Cerrar con ESC
        $(document).on('keydown', (e) => {
            if ($('#fs-backdrop').hasClass('active')) {
                if (e.key === 'Escape') this.cerrarFullscreen();
                if (e.key === 'ArrowLeft') this.navegarFullscreen(-1);
                if (e.key === 'ArrowRight') this.navegarFullscreen(1);
            }
        });

        // Cerrar haciendo clic fuera
        $('#fs-backdrop').click((e) => {
            if (e.target.id === 'fs-backdrop') {
                this.cerrarFullscreen();
            }
        });
    }

    navegarFullscreen(direction) {
        this.currentFullscreenIndex += direction;
        
        if (this.currentFullscreenIndex < 0) {
            this.currentFullscreenIndex = this.imagenesFullscreen.length - 1;
        } else if (this.currentFullscreenIndex >= this.imagenesFullscreen.length) {
            this.currentFullscreenIndex = 0;
        }
        
        this.actualizarFullscreenGallery();
    }

    actualizarFullscreenGallery() {
        const imagen = this.imagenesFullscreen[this.currentFullscreenIndex];
        const imgElement = $('#fs-image');
        const counterElement = $('#fs-image-counter');
        const badgeElement = $('#fs-image-principal-badge');

        imgElement.attr('src', RUTA_IMG + imagen.path);
        counterElement.text(`${this.currentFullscreenIndex + 1} / ${this.imagenesFullscreen.length}`);
        
        if (imagen.es_principal) {
            badgeElement.show();
        } else {
            badgeElement.hide();
        }
    }

    cerrarFullscreen() {
        $('#fs-backdrop').removeClass('active');
    }

    editarDesdeDetalles() {
        $('#modal-detalles-servicio').modal('hide');
        this.editarServicio(this.servicioEditando.id);
    }

    async editarServicio(id) {
        try {
            const response = await $.post('get_servicio', { 
                accion: 'obtener', 
                id: id 
            });

            if (response.success) {
                this.cargarDatosEnFormulario(response.data.servicio);
                $('#modalServicioLabel').text('Editar Servicio');
                $('#modal-servicio').modal('show');
            } else {
                throw new Error(response.data?.message || 'Error al cargar el servicio');
            }
        } catch (error) {
            console.error('Error al editar servicio:', error);
            Swal.fire('Error', error.responseJSON?.data?.message || error.message || 'Error desconocido', 'error');
        }
    }

    cargarDatosEnFormulario(servicio) {
        $('#servicio-id').val(servicio.id);
        $('#servicio-nombre').val(servicio.nombre);
        $('#servicio-descripcion').val(servicio.descripcion);

        // Limpiar previews
        $('#dz-preview-principal').empty();
        $('#dz-preview-extras').empty();
        $('#imagenes-principal-existente').empty();
        $('#imagenes-extras-existente').empty();
        this.limpiarArchivosTemporales();

        // Cargar imágenes existentes
        if (servicio.imagenes && servicio.imagenes.length > 0) {
            const imagenesPrincipales = servicio.imagenes.filter(img => img.es_principal);
            const imagenesExtras = servicio.imagenes.filter(img => !img.es_principal);

            // Imagen principal
            if (imagenesPrincipales.length > 0) {
                const imgPrincipal = imagenesPrincipales[0];
                this.mostrarImagenExistente(imgPrincipal, 'principal');
                this.imagenPrincipalExistente = imgPrincipal.id;
            }

            // Imágenes extras
            imagenesExtras.forEach(imagen => {
                this.mostrarImagenExistente(imagen, 'extra');
            });
        }

        this.servicioEditando = servicio;
        this.imagenesAEliminar = [];
    }

    mostrarImagenExistente(imagen, tipo) {
        const containerId = tipo === 'principal' ? 
            '#imagenes-principal-existente' : '#imagenes-extras-existente';
        
        const imagenHtml = `
            <div class="dz-item" data-id="${imagen.id}">
                <img src="${RUTA_IMG}${imagen.path}" alt="Imagen existente">
                <div class="dz-actions">
                    ${tipo === 'principal' ? 
                        '<span class="dz-principal"><i class="fas fa-star"></i> Principal</span>' : ''}
                    <button type="button" class="dz-remove" onclick="serviciosManager.marcarImagenAEliminar(${imagen.id}, '${tipo}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $(containerId).append(imagenHtml);
    }

    marcarImagenAEliminar(imagenId, tipo) {
        this.imagenesAEliminar.push(imagenId);
        $(`[data-id="${imagenId}"]`).remove();
        $('#imagenes_eliminar').val(this.imagenesAEliminar.join(','));

        if (tipo === 'principal') {
            this.imagenPrincipalExistente = null;
            $('#imagen_principal_existente').val('');
        }
    }

    mostrarModalEliminar(id) {
        this.servicioAEliminar = id;
        $('#modal-confirmar-eliminar').modal('show');
    }

    async eliminarServicio() {
        if (!this.servicioAEliminar) return;

        try {
            const response = await $.post('eliminar_servicio', {
                accion: 'eliminar',
                id: this.servicioAEliminar
            });

            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: response.data.message || 'Servicio eliminado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });

                $('#modal-confirmar-eliminar').modal('hide');
                this.tabla.ajax.reload();
            } else {
                throw new Error(response.data?.message || 'Error al eliminar el servicio');
            }
        } catch (error) {
            console.error('Error al eliminar servicio:', error);
            Swal.fire('Error', error.responseJSON?.data?.message || error.message || 'Error desconocido', 'error');
        } finally {
            this.servicioAEliminar = null;
        }
    }
}

// Inicializar cuando el documento esté listo
$(document).ready(() => {
    window.serviciosManager = new ServiciosManager();
});