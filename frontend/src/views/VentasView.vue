<template>
  <LayoutMain>
    <div class="ventas-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>💵 Gestión de Ventas</h1>
          <p class="subtitle">Registra y administra las ventas del restaurante</p>
        </div>
        <button @click="abrirModalNuevaVenta" class="btn-primary">➕ Nueva Venta</button>
      </div>

      <!-- Estadísticas rápidas (solo admin y gerente ven totales de dinero) -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3>{{ stats.total_hoy || 0 }}</h3>
            <p>Ventas Hoy</p>
          </div>
        </div>
        <div v-if="esAdminOGerente" class="stat-card green">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.total_hoy || 0) }}</h3>
            <p>Total Hoy</p>
          </div>
        </div>
        <div class="stat-card purple">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>{{ stats.total_mes || 0 }}</h3>
            <p>Ventas del Mes</p>
          </div>
        </div>
        <div v-if="esAdminOGerente" class="stat-card orange">
          <div class="stat-icon">💵</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.total_mes || 0) }}</h3>
            <p>Total del Mes</p>
          </div>
        </div>
      </div>

      <!-- Tabla de ventas -->
      <div class="table-container">
        <table class="ventas-table">
          <thead>
            <tr>
              <th>N° Venta</th>
              <th>Fecha</th>
              <th>Cliente</th>
              <th>Items</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center loading">
                <span class="spinner">⏳</span> Cargando ventas...
              </td>
            </tr>
            <tr v-else-if="ventas.length === 0">
              <td colspan="7" class="text-center empty">
                <div class="empty-state">
                  <span class="empty-icon">💵</span>
                  <p>No hay ventas registradas</p>
                  <button @click="abrirModalNuevaVenta" class="btn-secondary">
                    Registrar primera venta
                  </button>
                </div>
              </td>
            </tr>
            <tr v-else v-for="venta in ventas" :key="venta.id">
              <td>
                <span class="numero-venta">{{ venta.numero_venta }}</span>
              </td>
              <td>{{ formatDate(venta.fecha) }}</td>
              <td>{{ venta.cliente_nombre || 'Cliente General' }}</td>
              <td class="text-center">
                <span class="badge-items">{{ venta.total_items }}</span>
              </td>
              <td class="total">S/ {{ formatNumber(venta.subtotal) }}</td>
              <td>
                <span class="badge-estado" :class="{ cancelada: venta.estado === 'cancelada' }">
                  {{ venta.estado }}
                </span>
              </td>
              <td class="actions">
                <button @click="verDetalle(venta.id)" class="btn-icon btn-view" title="Ver detalle">
                  👁
                </button>
                <button
                  v-if="venta.estado !== 'cancelada' && puedeCancelar"
                  @click="cancelarVenta(venta.id)"
                  class="btn-icon btn-cancel"
                  title="Cancelar"
                >
                  ❌
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal para nueva venta -->
      <div v-if="showModalVenta" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal modal-large">
          <div class="modal-header">
            <h2>➕ Nueva Venta</h2>
            <button @click="cerrarModal" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardarVenta" class="modal-body">
            <!-- Datos del cliente -->
            <div class="section">
              <h3>👤 Cliente</h3>

              <!-- Toggle para activar búsqueda de cliente -->
              <div class="cliente-toggle">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="requiereCliente" />
                  <span>¿Asociar cliente a esta venta?</span>
                </label>
              </div>

              <!-- Si NO requiere cliente -->
              <div v-if="!requiereCliente" class="cliente-general-info">
                <p>✅ Venta como <strong>"Cliente General"</strong></p>
                <small>No se asociará a ningún cliente específico</small>
              </div>

              <!-- Si requiere cliente -->
              <div v-if="requiereCliente" class="cliente-form">
                <!-- Cliente seleccionado -->
                <div v-if="clienteSeleccionado" class="cliente-selected">
                  <div class="selected-info">
                    <span class="check-icon">✅</span>
                    <div>
                      <strong>{{ clienteSeleccionado.nombre_completo }}</strong>
                      <small v-if="clienteSeleccionado.numero_documento">
                        {{ clienteSeleccionado.numero_documento }}
                      </small>
                    </div>
                  </div>
                  <button type="button" @click="limpiarCliente" class="btn-clear">✕ Cambiar</button>
                </div>

                <!-- Buscar cliente existente -->
                <div v-else>
                  <!-- Buscador -->
                  <div class="buscar-cliente">
                    <input
                      v-model="busquedaCliente"
                      @input="buscarClientes"
                      type="text"
                      placeholder="🔍 Buscar por nombre o documento..."
                      class="input-buscar"
                    />
                  </div>

                  <!-- Resultados de búsqueda -->
                  <div v-if="clientesFiltrados.length > 0" class="resultados-busqueda">
                    <div
                      v-for="cliente in clientesFiltrados"
                      :key="cliente.id"
                      @click="seleccionarCliente(cliente)"
                      class="resultado-item"
                    >
                      <strong>{{ cliente.nombre_completo }}</strong>
                      <small v-if="cliente.numero_documento">
                        {{ cliente.numero_documento }}
                      </small>
                    </div>
                  </div>

                  <!-- Mensaje si no hay resultados -->
                  <div v-if="busquedaCliente && clientesFiltrados.length === 0" class="no-resultados">
                    <p>❌ No se encontraron clientes</p>
                    <small>El cliente debe estar previamente registrado en el sistema</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selección de productos -->
            <div class="section">
              <h3>🛒 Productos</h3>

              <!-- Agregar producto -->
              <div class="agregar-producto">
                <select v-model="productoSeleccionado" class="select-producto">
                  <option value="">Seleccionar producto...</option>
                  <option v-for="prod in productos" :key="prod.id" :value="prod">
                    {{ prod.nombre }} - S/ {{ prod.precio }} (Stock: {{ prod.stock_actual }})
                  </option>
                </select>
                <input
                  v-model.number="cantidadSeleccionada"
                  type="number"
                  min="1"
                  placeholder="Cant."
                  class="input-cantidad"
                />
                <button
                  type="button"
                  @click="agregarItem"
                  class="btn-add"
                  :disabled="!productoSeleccionado || cantidadSeleccionada < 1"
                >
                  ➕ Agregar
                </button>
              </div>

              <!-- Lista de items -->
              <div v-if="formVenta.items.length > 0" class="items-list">
                <table class="items-table">
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in formVenta.items" :key="index">
                      <td>{{ item.nombre }}</td>
                      <td>S/ {{ formatNumber(item.precio_unitario) }}</td>
                      <td>{{ item.cantidad }}</td>
                      <td>S/ {{ formatNumber(item.cantidad * item.precio_unitario) }}</td>
                      <td>
                        <button type="button" @click="eliminarItem(index)" class="btn-remove">
                          🗑
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-else class="no-items">
                <p>➕ Agrega productos a la venta</p>
              </div>
            </div>

            <!-- Totales -->
            <div v-if="formVenta.items.length > 0" class="section totales">
              <div class="total-row total-final">
                <span>TOTAL:</span>
                <strong>S/ {{ formatNumber(calcularTotal) }}</strong>
              </div>
            </div>

            <!-- Método de pago -->
            <div class="section">
              <div class="form-row">
                <div class="form-group">
                  <label>Método de Pago:</label>
                  <select v-model="formVenta.metodo_pago">
                    <option value="Efectivo">💵 Efectivo</option>
                    <option value="Tarjeta">💳 Tarjeta</option>
                    <option value="Yape">📱 Yape/Plin</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Acciones -->
            <div class="modal-actions">
              <button type="button" @click="cerrarModal" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-submit" :disabled="formVenta.items.length === 0">
                💾 Registrar Venta
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal detalle de venta -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="showModalDetalle = false">
        <div class="modal">
          <div class="modal-header">
            <h2>📄 Detalle de Venta</h2>
            <button @click="showModalDetalle = false" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="ventaDetalle">
            <div class="detalle-info">
              <div class="info-row">
                <strong>N° Venta:</strong>
                <span>{{ ventaDetalle.numero_venta }}</span>
              </div>
              <div class="info-row">
                <strong>Fecha:</strong>
                <span>{{ formatDate(ventaDetalle.fecha) }}</span>
              </div>
              <div class="info-row">
                <strong>Cliente:</strong>
                <span>{{ ventaDetalle.cliente_nombre || 'Cliente General' }}</span>
              </div>
              <div class="info-row">
                <strong>Método de Pago:</strong>
                <span>{{ ventaDetalle.metodo_pago }}</span>
              </div>
            </div>

            <h3>Productos:</h3>
            <table class="items-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="detalle in ventaDetalle.detalles" :key="detalle.id">
                  <td>{{ detalle.producto_nombre }}</td>
                  <td>{{ detalle.cantidad }}</td>
                  <td>S/ {{ formatNumber(detalle.precio_unitario) }}</td>
                  <td>S/ {{ formatNumber(detalle.subtotal) }}</td>
                </tr>
              </tbody>
            </table>

            <div class="totales">
              <div class="total-row total-final">
                <span>TOTAL:</span>
                <strong>S/ {{ formatNumber(ventaDetalle.subtotal) }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import ventasService from '@/services/ventas'
import productosService from '@/services/productos'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'VentasView',
  components: {
    LayoutMain,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      ventas: [],
      productos: [],
      clientes: [],
      stats: {},
      loading: false,
      showModalVenta: false,
      showModalDetalle: false,
      ventaDetalle: null,
      productoSeleccionado: '',
      cantidadSeleccionada: 1,

      // Cliente
      requiereCliente: false,
      busquedaCliente: '',
      clientesFiltrados: [],
      clienteSeleccionado: null,

      formVenta: {
        cliente_id: null,
        metodo_pago: 'efectivo',
        items: [],
      },
    }
  },
  computed: {
    esAdminOGerente() {
      return this.authStore.user?.rol === 'admin' || this.authStore.user?.rol === 'gerente'
    },
    puedeCancelar() {
      return this.authStore.user?.rol === 'admin' || this.authStore.user?.rol === 'gerente'
    },
    calcularTotal() {
      return this.formVenta.items.reduce(
        (sum, item) => sum + item.cantidad * item.precio_unitario,
        0
      )
    },
    calcularSubtotal() {
      return parseFloat((this.calcularTotal).toFixed(2))
    },
    calcularIGV() {
      return parseFloat((this.calcularSubtotal * 0.18).toFixed(2))
    },
  },
  mounted() {
    this.cargarDatos()
  },
  methods: {
    async cargarDatos() {
      this.loading = true
      try {
        await Promise.all([
          this.cargarVentas(),
          this.cargarProductos(),
          this.cargarStats(),
          this.cargarClientes(),
        ])
      } finally {
        this.loading = false
      }
    },

    async cargarVentas() {
      try {
        this.ventas = await ventasService.getAll()
      } catch (error) {
        console.error('Error al cargar ventas:', error)
        alert('Error al cargar ventas')
      }
    },

    async cargarProductos() {
      try {
        this.productos = await productosService.getAll()
      } catch (error) {
        console.error('Error al cargar productos:', error)
      }
    },

    async cargarStats() {
      try {
        this.stats = await ventasService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    async cargarClientes() {
      try {
        const response = await api.get('/clientes')
        this.clientes = response.data
      } catch (error) {
        console.error('Error al cargar clientes:', error)
      }
    },

    // Buscar clientes mientras escribe
    buscarClientes() {
      if (!this.busquedaCliente || this.busquedaCliente.length < 2) {
        this.clientesFiltrados = []
        return
      }

      const busqueda = this.busquedaCliente.toLowerCase()
      this.clientesFiltrados = this.clientes
        .filter(
          (cliente) =>
            cliente.nombre_completo.toLowerCase().includes(busqueda) ||
            (cliente.numero_documento && cliente.numero_documento.includes(busqueda)),
        )
        .slice(0, 5)
    },

    // Seleccionar cliente de la lista
    seleccionarCliente(cliente) {
      this.clienteSeleccionado = cliente
      // No necesitas guardar esto en formVenta, lo enviaremos directamente
      this.clientesFiltrados = []
      this.busquedaCliente = ''
    },

    // Limpiar selección de cliente
    limpiarCliente() {
      this.clienteSeleccionado = null
      this.formVenta.cliente_id = null
      this.busquedaCliente = ''
      this.clientesFiltrados = []
    },

    abrirModalNuevaVenta() {
      this.formVenta = {
        cliente_id: null,
        metodo_pago: 'efectivo',
        items: [],
      }
      this.productoSeleccionado = ''
      this.cantidadSeleccionada = 1
      this.requiereCliente = false
      this.clienteSeleccionado = null
      this.busquedaCliente = ''
      this.clientesFiltrados = []
      this.showModalVenta = true
    },

    agregarItem() {
      if (!this.productoSeleccionado || this.cantidadSeleccionada < 1) return

      const producto = this.productoSeleccionado

      // ✅ CORREGIDO: Template literal con backticks
      if (this.cantidadSeleccionada > producto.stock_actual) {
        alert(`Stock insuficiente. Disponible: ${producto.stock_actual}`)
        return
      }

      this.formVenta.items.push({
        producto_id: producto.id,
        nombre: producto.nombre,
        cantidad: this.cantidadSeleccionada,
        precio_unitario: parseFloat(producto.precio),
      })

      this.productoSeleccionado = ''
      this.cantidadSeleccionada = 1
    },

    eliminarItem(index) {
      this.formVenta.items.splice(index, 1)
    },

    async guardarVenta() {
      if (this.formVenta.items.length === 0) {
        alert('Agrega al menos un producto')
        return
      }

      try {
        // ✅ ESTRUCTURA CORRECTA para el controller
        const ventaData = {
          metodo_pago: this.formVenta.metodo_pago,
          // Los totales los calcula el backend, no los envíes
          // subtotal: this.calcularSubtotal,
          // impuesto: this.calcularIGV, 
          // total: this.calcularTotal,
          items: this.formVenta.items.map((item) => ({
            producto_id: item.producto_id,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario,
          })),
        }

        // ✅ AGREGAR DATOS DEL CLIENTE según lo que espera el backend
        if (this.clienteSeleccionado) {
          ventaData.cliente_id = this.clienteSeleccionado.id // Para la relación
          ventaData.cliente_nombre = this.clienteSeleccionado.nombre_completo // Para mostrar
          if (this.clienteSeleccionado.numero_documento) {
            ventaData.cliente_documento = this.clienteSeleccionado.numero_documento
          }
        } else {
          ventaData.cliente_nombre = 'Cliente General'
        }

        console.log('📤 ENVIANDO DATOS:', JSON.stringify(ventaData, null, 2))

        const response = await ventasService.create(ventaData)
        console.log('✅ RESPUESTA:', response)
        
        alert('✅ Venta registrada exitosamente')
        this.cerrarModal()
        this.cargarDatos()
      } catch (error) {
        console.error('❌ Error:', error)
        
        if (error.response?.data?.errors) {
          console.log('🔍 Errores:', error.response.data.errors)
          const errores = Object.values(error.response.data.errors).flat()
          alert('❌ Errores:\n• ' + errores.join('\n• '))
        } else {
          const mensaje = error.response?.data?.message || 'Error al registrar venta'
          alert('❌ ' + mensaje)
        }
      }
    },

    async verDetalle(id) {
      try {
        this.ventaDetalle = await ventasService.getById(id)
        this.showModalDetalle = true
      } catch (error) {
        console.error('Error al cargar detalle:', error)
        alert('Error al cargar detalle de venta')
      }
    },

    async cancelarVenta(id) {
      if (!this.puedeCancelar) {
        alert('⚠ No tienes permisos para cancelar ventas')
        return
      }

      if (!confirm('⚠ ¿Estás seguro de cancelar esta venta? El stock se devolverá.')) return

      try {
        await ventasService.cancel(id)
        alert('✅ Venta cancelada exitosamente')
        this.cargarDatos()
      } catch (error) {
        console.error('Error al cancelar venta:', error)
        const mensaje = error.response?.data?.message || 'Error al cancelar venta'
        alert('❌ ' + mensaje)
      }
    },

    cerrarModal() {
      this.showModalVenta = false
      this.showModalDetalle = false
      this.ventaDetalle = null
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num || 0)
    },

    formatDate(date) {
      return new Date(date).toLocaleString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      })
    },
  },
}
</script>

<style scoped>
/* Tus estilos existentes se mantienen igual */
.ventas-page {
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
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
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
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  font-size: 36px;
}

.stat-content h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
}

.stat-content p {
  margin: 5px 0 0 0;
  color: #7f8c8d;
  font-size: 12px;
}

.stat-card.blue {
  border-left: 4px solid #3498db;
}
.stat-card.green {
  border-left: 4px solid #27ae60;
}
.stat-card.purple {
  border-left: 4px solid #9b59b6;
}
.stat-card.orange {
  border-left: 4px solid #e67e22;
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.ventas-table {
  width: 100%;
  border-collapse: collapse;
}

.ventas-table thead {
  background: #f8f9fa;
}

.ventas-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
  font-size: 13px;
  text-transform: uppercase;
}

.ventas-table td {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
  color: #2c3e50;
}

.ventas-table tbody tr:hover {
  background: #f8f9ff;
}

.numero-venta {
  font-family: 'Courier New', monospace;
  background: #e3f2fd;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #1976d2;
  font-weight: 600;
}

.badge-items {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.total {
  font-weight: 700;
  color: #27ae60;
  font-size: 16px;
}

.badge-estado {
  background: #d4edda;
  color: #155724;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-estado.cancelada {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  text-align: center;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 18px;
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

.text-center {
  text-align: center;
}

.loading,
.empty {
  padding: 40px 20px;
  color: #7f8c8d;
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

.btn-secondary {
  background: white;
  border: 2px solid #27ae60;
  color: #27ae60;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #27ae60;
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

.modal-large {
  max-width: 900px;
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

.section {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.section:last-child {
  border-bottom: none;
}

.section h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #27ae60;
}

/* Cliente Toggle */
.cliente-toggle {
  margin-bottom: 20px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 15px;
  color: #2c3e50;
}

.checkbox-label input[type='checkbox'] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.cliente-general-info {
  background: #e8f5e9;
  border-left: 4px solid #27ae60;
  padding: 15px;
  border-radius: 8px;
}

.cliente-general-info p {
  margin: 0 0 5px 0;
  color: #27ae60;
  font-size: 14px;
}

.cliente-general-info small {
  color: #66bb6a;
  font-size: 12px;
}

/* Cliente Form */
.cliente-form {
  margin-top: 15px;
}

.cliente-selected {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #e3f2fd;
  border: 2px solid #2196f3;
  padding: 15px;
  border-radius: 8px;
}

.selected-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.check-icon {
  font-size: 24px;
}

.selected-info strong {
  display: block;
  color: #1565c0;
  font-size: 15px;
}

.selected-info small {
  display: block;
  color: #1976d2;
  font-size: 12px;
  margin-top: 2px;
}

.btn-clear {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-clear:hover {
  background: #c0392b;
}

/* Buscador */
.buscar-cliente {
  margin-bottom: 15px;
}

.input-buscar {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.input-buscar:focus {
  outline: none;
  border-color: #3498db;
}

/* Resultados de búsqueda */
.resultados-busqueda {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  max-height: 200px;
  overflow-y: auto;
  margin-bottom: 15px;
}

.resultado-item {
  padding: 12px 15px;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid #f0f0f0;
}

.resultado-item:last-child {
  border-bottom: none;
}

.resultado-item:hover {
  background: #f8f9fa;
}

.resultado-item strong {
  display: block;
  color: #2c3e50;
  font-size: 14px;
}

.resultado-item small {
  display: block;
  color: #7f8c8d;
  font-size: 12px;
  margin-top: 2px;
}

/* No resultados */
.no-resultados {
  text-align: center;
  padding: 20px;
  color: #e74c3c;
  background: #fdf2f2;
  border-radius: 8px;
  margin-top: 10px;
}

.no-resultados p {
  margin: 0 0 5px 0;
  font-weight: 600;
}

.no-resultados small {
  color: #c0392b;
  font-size: 12px;
}

/* Agregar producto */
.agregar-producto {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.select-producto {
  flex: 1;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.input-cantidad {
  width: 100px;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  text-align: center;
}

.btn-add {
  background: #27ae60;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  white-space: nowrap;
  transition: all 0.3s;
}

.btn-add:hover:not(:disabled) {
  background: #229954;
}

.btn-add:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Items table */
.items-list {
  margin-top: 20px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table thead {
  background: #f8f9fa;
}

.items-table th {
  padding: 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
}

.items-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.btn-remove {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
}

.btn-remove:hover {
  background: #c0392b;
}

.no-items {
  text-align: center;
  padding: 40px;
  color: #95a5a6;
  font-size: 14px;
}

/* Totales */
.totales {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-top: 20px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: #2c3e50;
  font-size: 14px;
}

.total-final {
  border-top: 2px solid #dee2e6;
  padding-top: 12px;
  margin-top: 8px;
  font-size: 18px;
  color: #27ae60;
}

/* Modal actions */
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
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  border: none;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Detalle info */
.detalle-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #dee2e6;
}

.info-row:last-child {
  border-bottom: none;
}

/* Responsive */
@media (max-width: 768px) {
  .ventas-page {
    padding: 20px 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .agregar-producto {
    flex-direction: column;
  }

  .input-cantidad {
    width: 100%;
  }
}
</style>