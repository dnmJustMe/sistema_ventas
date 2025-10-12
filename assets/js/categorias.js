// app/js/categorias.js
class CategoriasManager {
    constructor() {
        this.categoriaAEliminar = null;
        this.dataTable = null;
        this.init();
    }

    init() {
        this.inicializarDataTables();
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.getElementById('btn-agregar-categoria').addEventListener('click', () => this.mostrarModalCategoria());
        document.getElementById('cerrar-modal-categoria').addEventListener('click', () => this.ocultarModalCategoria());
        document.getElementById('cancelar-categoria').addEventListener('click', () => this.ocultarModalCategoria());
        
        document.getElementById('form-categoria').addEventListener('submit', (e) => this.guardarCategoria(e));
        
        document.getElementById('cancelar-eliminar-categoria').addEventListener('click', () => this.ocultarModalEliminarCategoria());
        document.getElementById('confirmar-eliminar-categoria').addEventListener('click', () => this.eliminarCategoriaConfirmada());
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', (e) => {
            const modalCategoria = document.getElementById('modal-categoria');
            const modalEliminar = document.getElementById('modal-confirmar-eliminar-categoria');
            
            if (e.target === modalCategoria) {
                this.ocultarModalCategoria();
            }
            if (e.target === modalEliminar) {
                this.ocultarModalEliminarCategoria();
            }
        });
    }

    inicializarDataTables() {
        this.dataTable = $('#tabla-categorias').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "get_categorias",
                "type": "POST",
                "data": function(d) {
                    return {
                        accion: 'listar'
                    };
                },
                "dataSrc": function(json) {
                    if (json.success) {
                        return json.categorias;
                    } else {
                        console.error('Error cargando categorías:', json?.message);
                        return [];
                    }
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "nombre" },
                { 
                    "data": "fecha_creado",
                    "render": function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { 
                    "data": "total_productos",
                    "render": function(data) {
                        return data || 0;
                    }
                },
                {
                    "data": "id",
                    "render": function(data, type, row) {
                        const tieneProductos = (row.total_productos > 0);
                        const botonEliminar = tieneProductos ? 
                            `<button class="btn btn-sm btn-danger" disabled title="No se puede eliminar categoría con productos">
                                <i class="fas fa-trash"></i>
                            </button>` :
                            `<button class="btn btn-sm btn-danger eliminar-categoria" data-id="${data}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>`;
                        
                        return `
                            <button class="btn btn-sm btn-warning editar-categoria" data-id="${data}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${botonEliminar}
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
        $('#tabla-categorias').on('click', '.editar-categoria', (e) => {
            const id = $(e.currentTarget).data('id');
            this.editarCategoria(id);
        });
        
        $('#tabla-categorias').on('click', '.eliminar-categoria', (e) => {
            const id = $(e.currentTarget).data('id');
            this.mostrarModalEliminarCategoria(id);
        });
    }

    mostrarModalCategoria(categoria = null) {
        const modal = document.getElementById('modal-categoria');
        const titulo = document.getElementById('titulo-modal-categoria');
        
        document.getElementById('form-categoria').reset();
        
        if (categoria) {
            titulo.textContent = 'Editar Categoría';
            document.getElementById('categoria-id').value = categoria.id;
            document.getElementById('categoria-nombre').value = categoria.nombre;
        } else {
            titulo.textContent = 'Agregar Categoría';
            document.getElementById('categoria-id').value = '';
        }
        
        modal.style.display = 'block';
    }

    ocultarModalCategoria() {
        document.getElementById('modal-categoria').style.display = 'none';
    }

    async guardarCategoria(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const categoriaId = document.getElementById('categoria-id').value;

        try {
            let url;
            
            if (categoriaId) {
                formData.append('accion', 'actualizar');
                formData.append('id', categoriaId);
                url = 'actualizar_categoria';
            } else {
                formData.append('accion', 'crear');
                url = 'crear_categoria';
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.mostrarExito(data.data.message || 'Operación exitosa');
                this.ocultarModalCategoria();
                this.dataTable.ajax.reload(); // Recargar DataTables
            } else {
                throw new Error(data.data?.message || 'Error en la operación');
            }
            
        } catch (error) {
            console.error('Error guardando categoría:', error);
            await this.mostrarError('Error al guardar categoría: ' + error.message);
        }
    }

    async editarCategoria(id) {
        try {
            const params = new URLSearchParams();
            params.append('accion', 'obtener');
            params.append('id', id);

            const response = await fetch('actualizar_categoria', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarModalCategoria(data.data.categoria);
            } else {
                throw new Error(data.data?.message || 'Error al cargar categoría');
            }
        } catch (error) {
            console.error('Error cargando categoría:', error);
            await this.mostrarError('Error al cargar categoría: ' + error.message);
        }
    }

    mostrarModalEliminarCategoria(id) {
        this.categoriaAEliminar = id;
        document.getElementById('modal-confirmar-eliminar-categoria').style.display = 'block';
    }

    ocultarModalEliminarCategoria() {
        this.categoriaAEliminar = null;
        document.getElementById('modal-confirmar-eliminar-categoria').style.display = 'none';
    }

    async eliminarCategoriaConfirmada() {
        if (!this.categoriaAEliminar) return;

        try {
            const params = new URLSearchParams();
            params.append('accion', 'eliminar');
            params.append('id', this.categoriaAEliminar);

            const response = await fetch('eliminar_categoria', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.mostrarExito(data.message || 'Categoría eliminada exitosamente');
                this.ocultarModalEliminarCategoria();
                this.dataTable.ajax.reload(); // Recargar DataTables
            } else {
                throw new Error(data?.message || 'Error al eliminar categoría');
            }
            
        } catch (error) {
            console.error('Error eliminando categoría:', error);
            await this.mostrarError('Error al eliminar categoría: ' + error.message);
            this.ocultarModalEliminarCategoria();
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
    window.categoriasManager = new CategoriasManager();
});