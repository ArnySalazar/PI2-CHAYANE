<template>
  <LayoutMain>
    <div class="productos-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>📦 Gestión de Productos</h1>
          <p class="subtitle">Administra el inventario de productos</p>
        </div>
        <button v-if="puedeCrear" @click="abrirModalNuevo" class="btn-primary">
          ➕ Nuevo Producto
        </button>
      </div>

      <!-- Tabla de productos -->
      <div class="table-container">
        <table class="productos-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center loading">
                <span class="spinner">⏳</span> Cargando productos...
              </td>
            </tr>
            <tr v-else-if="productos.length === 0">
              <td colspan="6" class="text-center empty">
                <div class="empty-state">
                  <span class="empty-icon">📦</span>
                  <p>No hay productos registrados</p>
                  <button v-if="puedeCrear" @click="abrirModalNuevo" class="btn-secondary">
                    Agregar primer producto
                  </button>
                </div>
              </td>
            </tr>
            <tr v-else v-for="producto in productos" :key="producto.id">
              <td>
                <span class="codigo">{{ producto.codigo }}</span>
              </td>
              <td>
                <strong>{{ producto.nombre }}</strong>
                <small v-if="producto.descripcion" class="descripcion">
                  {{ producto.descripcion }}
                </small>
              </td>
              <td>
                <span class="badge-categoria">{{ producto.categoria_nombre }}</span>
              </td>
              <td class="precio">S/ {{ producto.precio }}</td>
              <td>
                <span
                  class="badge-stock"
                  :class="{ 'stock-bajo': producto.stock < producto.stock_minimo }"
                >
                  {{ producto.stock }}
                </span>
              </td>
              <td class="actions">
                <button
                  v-if="puedeEditar"
                  @click="editarProducto(producto)"
                  class="btn-icon btn-edit"
                  title="Editar"
                >
                  ✏️
                </button>
                <button
                  v-if="puedeEliminar"
                  @click="eliminarProducto(producto.id)"
                  class="btn-icon btn-delete"
                  title="Eliminar"
                >
                  🗑️
                </button>
                <span v-if="!puedeEditar && !puedeEliminar" class="sin-acciones">
                  👁️ Solo lectura
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal para crear/editar -->
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ editando ? '✏️ Editar Producto' : '➕ Nuevo Producto' }}</h2>
            <button @click="cerrarModal" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardarProducto" class="modal-body">
            <div class="form-group">
              <label>Nombre: <span class="required">*</span></label>
              <input v-model="form.nombre" type="text" placeholder="Ej: Lomo Saltado" required />
            </div>

            <div class="form-group">
              <label>Descripción:</label>
              <textarea
                v-model="form.descripcion"
                rows="3"
                placeholder="Descripción del producto (opcional)"
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Categoría: <span class="required">*</span></label>
                <select v-model="form.categoria_id" required>
                  <option value="">Seleccionar categoría...</option>
                  <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                    {{ cat.nombre }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Precio Venta: <span class="required">*</span></label>
                <div class="input-group">
                  <span class="input-prefix">S/</span>
                  <input
                    v-model="form.precio"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Stock Actual: <span class="required">*</span></label>
                <input v-model="form.stock" type="number" min="0" placeholder="0" required />
              </div>

              <div class="form-group">
                <label>Stock Mínimo:</label>
                <input v-model="form.stock_minimo" type="number" min="1" placeholder="10" />
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" @click="cerrarModal" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-submit">
                {{ editando ? '💾 Actualizar' : '➕ Crear' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import productosService from '@/services/productos'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'ProductosView',
  components: {
    LayoutMain,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      productos: [],
      categorias: [],
      loading: false,
      showModal: false,
      editando: false,
      form: {
        id: null,
        nombre: '',
        descripcion: '',
        categoria_id: '',
        precio: '',
        stock: '',
        stock_minimo: 10,
      },
    }
  },
  computed: {
    puedeCrear() {
      return this.authStore.canCreate('productos')
    },
    puedeEditar() {
      return this.authStore.canEdit('productos')
    },
    puedeEliminar() {
      return this.authStore.canDelete('productos')
    },
  },
  mounted() {
    this.cargarProductos()
    this.cargarCategorias()
  },
  methods: {
    async cargarProductos() {
      this.loading = true
      try {
        this.productos = await productosService.getAll()
      } catch (error) {
        console.error('Error al cargar productos:', error)
        alert('Error al cargar productos')
      } finally {
        this.loading = false
      }
    },

    async cargarCategorias() {
      try {
        this.categorias = await productosService.getCategorias()
      } catch (error) {
        console.error('Error al cargar categorías:', error)
      }
    },

    abrirModalNuevo() {
      this.editando = false
      this.form = {
        id: null,
        nombre: '',
        descripcion: '',
        categoria_id: '',
        precio: '',
        stock: '',
        stock_minimo: 10,
      }
      this.showModal = true
    },

    async guardarProducto() {
      try {
        if (this.editando) {
          await productosService.update(this.form.id, this.form)
          alert('✅ Producto actualizado exitosamente')
        } else {
          await productosService.create(this.form)
          alert('✅ Producto creado exitosamente')
        }
        this.cerrarModal()
        this.cargarProductos()
      } catch (error) {
        console.error('Error al guardar producto:', error)
        const mensaje = error.response?.data?.message || error.message
        alert('❌ ' + mensaje)
      }
    },

    editarProducto(producto) {
      if (!this.puedeEditar) {
        alert('⚠️ No tienes permisos para editar productos')
        return
      }
      this.editando = true
      this.form = { ...producto }
      this.showModal = true
    },

    async eliminarProducto(id) {
      if (!this.puedeEliminar) {
        alert('⚠️ No tienes permisos para eliminar productos')
        return
      }

      if (confirm('⚠️ ¿Estás seguro de eliminar este producto?')) {
        try {
          await productosService.delete(id)
          alert('✅ Producto eliminado exitosamente')
          this.cargarProductos()
        } catch (error) {
          console.error('Error al eliminar producto:', error)
          const mensaje = error.response?.data?.message || 'Error al eliminar producto'
          alert('❌ ' + mensaje)
        }
      }
    },

    cerrarModal() {
      this.showModal = false
      this.editando = false
      this.form = {
        id: null,
        nombre: '',
        descripcion: '',
        categoria_id: '',
        precio: '',
        stock: '',
        stock_minimo: 10,
      }
    },
  },
}
</script>

<style scoped>
.productos-page {
  padding: 30px;
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 32px;
}

.subtitle {
  color: #7f8c8d;
  margin: 5px 0 0 0;
  font-size: 14px;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.productos-table {
  width: 100%;
  border-collapse: collapse;
}

.productos-table thead {
  background: #f8f9fa;
}

.productos-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.productos-table td {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
  color: #2c3e50;
}

.productos-table tbody tr {
  transition: background 0.2s;
}

.productos-table tbody tr:hover {
  background: #f8f9ff;
}

.codigo {
  font-family: 'Courier New', monospace;
  background: #ecf0f1;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #555;
}

.descripcion {
  display: block;
  color: #95a5a6;
  font-size: 12px;
  margin-top: 4px;
}

.badge-categoria {
  background: #e3f2fd;
  color: #1976d2;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.precio {
  font-weight: 700;
  color: #27ae60;
  font-size: 16px;
}

.badge-stock {
  background: #d4edda;
  color: #155724;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 13px;
}

.badge-stock.stock-bajo {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  text-align: center;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  margin: 0 4px;
  padding: 6px;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-icon:hover {
  transform: scale(1.2);
  background: #f8f9fa;
}

.sin-acciones {
  color: #95a5a6;
  font-size: 13px;
  font-style: italic;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.text-center {
  text-align: center;
  padding: 40px 20px;
}

.loading {
  color: #7f8c8d;
  font-size: 16px;
}

.spinner {
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.empty-state {
  padding: 20px;
}

.empty-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 15px;
  opacity: 0.3;
}

.empty-state p {
  color: #7f8c8d;
  margin-bottom: 20px;
}

.btn-secondary {
  background: white;
  border: 2px solid #667eea;
  color: #667eea;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #667eea;
  color: white;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 30px;
  border-bottom: 1px solid #e9ecef;
}

.modal-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 22px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #95a5a6;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f8f9fa;
  color: #2c3e50;
}

.modal-body {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.required {
  color: #e74c3c;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 15px;
  transition: border-color 0.3s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.input-group {
  display: flex;
  align-items: center;
}

.input-prefix {
  background: #f8f9fa;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-right: none;
  border-radius: 8px 0 0 8px;
  font-weight: 600;
  color: #2c3e50;
}

.input-group input {
  border-radius: 0 8px 8px 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.btn-cancel {
  background: white;
  border: 2px solid #e9ecef;
  color: #7f8c8d;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-cancel:hover {
  background: #f8f9fa;
  border-color: #dee2e6;
}

.btn-submit {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
  .productos-page {
    padding: 20px 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .page-header h1 {
    font-size: 24px;
  }

  .productos-table {
    font-size: 13px;
  }

  .productos-table th,
  .productos-table td {
    padding: 10px 8px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal {
    margin: 10px;
  }

  .modal-header,
  .modal-body {
    padding: 20px;
  }
}
</style>
