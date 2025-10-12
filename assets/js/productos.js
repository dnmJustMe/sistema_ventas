// app/js/productos.js
class ProductosManager {
    constructor() {
        this.productoAEliminar = null;
        this.dataTable = null;
        this.init();
    }

    init() {
        this.inicializarDataTables();
        this.cargarCategoriasParaModal();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Modal producto
        document.getElementById('btn-agregar-producto').addEventListener('click', () => this.mostrarModalProducto());
        document.getElementById('cerrar-modal-producto').addEventListener('click', () => this.ocultarModalProducto());
        document.getElementById('cancelar-producto').addEventListener('click', () => this.ocultarModalProducto());
        
        // Form producto
        document.getElementById('form-producto').addEventListener('submit', (e) => this.guardarProducto(e));
        
        // Modal detalles
        document.getElementById('cerrar-modal-detalles').addEventListener('click', () => this.ocultarModalDetalles());
        document.getElementById('cerrar-detalles').addEventListener('click', () => this.ocultarModalDetalles());
        
        // Modal eliminar
        document.getElementById('cancelar-eliminar').addEventListener('click', () => this.ocultarModalEliminar());
        document.getElementById('confirmar-eliminar').addEventListener('click', () => this.eliminarProductoConfirmado());
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', (e) => {
            const modalProducto = document.getElementById('modal-producto');
            const modalDetalles = document.getElementById('modal-detalles-producto');
            const modalEliminar = document.getElementById('modal-confirmar-eliminar');
            
            if (e.target === modalProducto) {
                this.ocultarModalProducto();
            }
            if (e.target === modalDetalles) {
                this.ocultarModalDetalles();
            }
            if (e.target === modalEliminar) {
                this.ocultarModalEliminar();
            }
        });
    }

    inicializarDataTables() {
        this.dataTable = $('#tabla-productos').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "get_productos",
                "type": "POST",
                "data": function(d) {
                    return {
                        accion: 'listar'
                    };
                },
                "dataSrc": function(json) {
                    if (json.success) {
                        return json.data.productos;
                    } else {
                        console.error('Error cargando productos:', json.data?.message);
                        return [];
                    }
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "nombre" },
                { 
                    "data": "descripcion",
                    "render": function(data) {
                        return data || '';
                    }
                },
                { 
                    "data": "costo",
                    "render": function(data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                { 
                    "data": "precio_venta",
                    "render": function(data) {
                        return `$${parseFloat(data).toFixed(2)}`;
                    }
                },
                { 
                    "data": "categoria_nombre",
                    "render": function(data) {
                        return data || 'Sin categoría';
                    }
                },
                { 
                    "data": "fecha_creado",
                    "render": function(data) {
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
                    "render": function(data, type, row) {
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
            console.log(data);
            
            if (data.success) {
                this.mostrarCategoriasEnSelect(data.categorias);
            } else {
                throw new Error(data.data?.message || 'Error al cargar categorías');
            }
        } catch (error) {
            console.error('Error cargando categorías:', error);
            await this.mostrarError('Error al cargar categoríasss: ' + error.message);
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
                this.mostrarModalDetalles(data.data.producto);
            } else {
                throw new Error(data.data?.message || 'Error al cargar detalles del producto');
            }
        } catch (error) {
            console.error('Error cargando detalles del producto:', error);
            await this.mostrarError('Error al cargar detalles del producto: ' + error.message);
        }
    }

    mostrarModalDetalles(producto) {
        const modal = document.getElementById('modal-detalles-producto');
        
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
        
        modal.style.display = 'block';
    }

    ocultarModalDetalles() {
        document.getElementById('modal-detalles-producto').style.display = 'none';
    }

    mostrarModalProducto(producto = null) {
        const modal = document.getElementById('modal-producto');
        const titulo = document.getElementById('titulo-modal-producto');
        const form = document.getElementById('form-producto');
        
        form.reset();
        
        if (producto) {
            titulo.textContent = 'Editar Producto';
            document.getElementById('producto-id').value = producto.id;
            document.getElementById('producto-nombre').value = producto.nombre;
            document.getElementById('producto-descripcion').value = producto.descripcion || '';
            document.getElementById('producto-costo').value = producto.costo;
            document.getElementById('producto-precio').value = producto.precio_venta;
            document.getElementById('producto-categoria').value = producto.categoria_id;
        } else {
            titulo.textContent = 'Agregar Producto';
            document.getElementById('producto-id').value = '';
        }
        
        modal.style.display = 'block';
    }

    ocultarModalProducto() {
        document.getElementById('modal-producto').style.display = 'none';
    }

    async guardarProducto(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const productoId = document.getElementById('producto-id').value;

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
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.mostrarExito(data.data.message || 'Operación exitosa');
                this.ocultarModalProducto();
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
        document.getElementById('modal-confirmar-eliminar').style.display = 'block';
    }

    ocultarModalEliminar() {
        this.productoAEliminar = null;
        document.getElementById('modal-confirmar-eliminar').style.display = 'none';
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
                this.ocultarModalEliminar();
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