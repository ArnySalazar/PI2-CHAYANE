<template>
  <LayoutMain>
    <div class="clientes-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>👥 Gestión de Clientes</h1>
          <p class="subtitle">Administra la base de datos de clientes del restaurante</p>
        </div>
        <button v-if="puedeCrear" @click="abrirModalNuevoCliente" class="btn-primary">
          ➕ Nuevo Cliente
        </button>
      </div>

      <!-- Stats rápidas -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <h3>{{ stats.total_clientes || 0 }}</h3>
            <p>Total Clientes</p>
          </div>
        </div>

        <div class="stat-card green">
          <div class="stat-icon">🆕</div>
          <div class="stat-content">
            <h3>{{ stats.clientes_nuevos_mes || 0 }}</h3>
            <p>Nuevos Este Mes</p>
          </div>
        </div>

        <div class="stat-card purple">
          <div class="stat-icon">🛒</div>
          <div class="stat-content">
            <h3>{{ stats.clientes_con_compras || 0 }}</h3>
            <p>Con Compras</p>
          </div>
        </div>

        <div class="stat-card orange">
          <div class="stat-icon">⭐</div>
          <div class="stat-content">
            <h3>{{ clientesFrecuentes.length || 0 }}</h3>
            <p>Frecuentes (3+ compras)</p>
          </div>
        </div>
      </div>

      <!-- Filtros y búsqueda -->
      <div class="filtros-card">
        <div class="filtros">
          <input
            v-model="busqueda"
            type="text"
            :placeholder="
              esAdminOGerente
                ? '🔍 Buscar por nombre, documento, teléfono...'
                : '🔍 Buscar por nombre...'
            "
            class="input-busqueda"
          />
        </div>
      </div>

      <!-- Tabla de clientes -->
      <div class="table-container">
        <table class="clientes-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre Completo</th>
              <th v-if="esAdminOGerente">Documento</th>
              <th v-if="esAdminOGerente">Teléfono</th>
              <th v-if="esAdminOGerente">Email</th>
              <th v-if="esAdminOGerente">Distrito</th>
              <th>Compras</th>
              <th v-if="esAdminOGerente">Total Gastado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td :colspan="esAdminOGerente ? 9 : 4" class="text-center loading">
                <span class="spinner">⏳</span> Cargando clientes...
              </td>
            </tr>
            <tr v-else-if="clientesFiltrados.length === 0">
              <td :colspan="esAdminOGerente ? 9 : 4" class="text-center empty">
                <div class="empty-state">
                  <span class="empty-icon">👥</span>
                  <p>No hay clientes registrados</p>
                  <button v-if="puedeCrear" @click="abrirModalNuevoCliente" class="btn-secondary">
                    Registrar primer cliente
                  </button>
                </div>
              </td>
            </tr>
            <tr v-else v-for="cliente in clientesFiltrados" :key="cliente.id">
              <td>
                <span class="codigo">{{ cliente.codigo }}</span>
              </td>
              <td>
                <strong>{{ cliente.nombre_completo }}</strong>
                <span v-if="cliente.total_compras >= 3" class="badge-frecuente">⭐ Frecuente</span>
              </td>
              <td v-if="esAdminOGerente">
                <span class="documento"
                  >{{ cliente.tipo_documento }}: {{ cliente.numero_documento || '-' }}</span
                >
              </td>
              <td v-if="esAdminOGerente">{{ cliente.telefono || '-' }}</td>
              <td v-if="esAdminOGerente">
                <small>{{ cliente.email || '-' }}</small>
              </td>
              <td v-if="esAdminOGerente">{{ cliente.distrito || '-' }}</td>
              <td class="text-center">
                <span class="badge-compras">{{ cliente.total_compras || 0 }}</span>
              </td>
              <td v-if="esAdminOGerente" class="total-gastado">
                S/ {{ formatNumber(cliente.total_gastado || 0) }}
              </td>
              <td class="actions">
                <button @click="verDetalle(cliente.id)" class="btn-icon" title="Ver detalle">
                  👁️
                </button>
                <button
                  v-if="puedeEditar"
                  @click="abrirModalEditar(cliente)"
                  class="btn-icon"
                  title="Editar"
                >
                  ✏️
                </button>
                <button
                  v-if="puedeEliminar"
                  @click="eliminarCliente(cliente.id)"
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

      <!-- Modal: Nuevo/Editar Cliente -->
      <div v-if="showModalCliente" class="modal-overlay" @click.self="cerrarModales">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ modoEdicion ? '✏️ Editar Cliente' : '➕ Nuevo Cliente' }}</h2>
            <button @click="cerrarModales" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardarCliente" class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>Tipo de Documento: *</label>
                <select v-model="formCliente.tipo_documento" required>
                  <option value="DNI">DNI</option>
                  <option value="RUC">RUC</option>
                  <option value="CE">Carnet de Extranjería</option>
                  <option value="Pasaporte">Pasaporte</option>
                </select>
              </div>
              <div class="form-group">
                <label>Número de Documento:</label>
                <input
                  v-model="formCliente.numero_documento"
                  type="text"
                  :placeholder="getPlaceholderDocumento()"
                  :maxlength="getMaxLengthDocumento()"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Nombres: *</label>
                <input
                  v-model="formCliente.nombres"
                  type="text"
                  required
                  placeholder="Ej: Juan Carlos"
                />
              </div>
              <div class="form-group">
                <label>Apellidos:</label>
                <input v-model="formCliente.apellidos" type="text" placeholder="Ej: García Pérez" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Teléfono:</label>
                <input
                  v-model="formCliente.telefono"
                  type="tel"
                  placeholder="987654321"
                  maxlength="15"
                />
              </div>
              <div class="form-group">
                <label>Email:</label>
                <input v-model="formCliente.email" type="email" placeholder="cliente@email.com" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Distrito:</label>
                <input v-model="formCliente.distrito" type="text" placeholder="Ej: San Isidro" />
              </div>
              <div class="form-group">
                <label>Fecha de Nacimiento:</label>
                <input v-model="formCliente.fecha_nacimiento" type="date" />
              </div>
            </div>

            <div class="form-group">
              <label>Dirección:</label>
              <input
                v-model="formCliente.direccion"
                type="text"
                placeholder="Calle, número, referencia..."
              />
            </div>

            <div class="form-group">
              <label>Observaciones:</label>
              <textarea
                v-model="formCliente.observaciones"
                rows="3"
                placeholder="Notas adicionales..."
              ></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" @click="cerrarModales" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-submit">
                💾 {{ modoEdicion ? 'Actualizar' : 'Guardar' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal: Detalle de Cliente -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="cerrarModales">
        <div class="modal modal-large">
          <div class="modal-header">
            <h2>📋 Detalle del Cliente</h2>
            <button @click="cerrarModales" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="clienteDetalle">
            <!-- Información del cliente -->
            <div class="cliente-info-card">
              <div class="info-header">
                <h3>{{ clienteDetalle.nombre_completo }}</h3>
                <span class="codigo">{{ clienteDetalle.codigo }}</span>
              </div>

              <div class="info-grid">
                <div v-if="esAdminOGerente" class="info-item">
                  <span class="label">📄 Documento:</span>
                  <span class="value"
                    >{{ clienteDetalle.tipo_documento }}:
                    {{ clienteDetalle.numero_documento || '-' }}</span
                  >
                </div>
                <div v-if="esAdminOGerente" class="info-item">
                  <span class="label">📱 Teléfono:</span>
                  <span class="value">{{ clienteDetalle.telefono || '-' }}</span>
                </div>
                <div v-if="esAdminOGerente" class="info-item">
                  <span class="label">📧 Email:</span>
                  <span class="value">{{ clienteDetalle.email || '-' }}</span>
                </div>
                <div v-if="esAdminOGerente" class="info-item">
                  <span class="label">📍 Distrito:</span>
                  <span class="value">{{ clienteDetalle.distrito || '-' }}</span>
                </div>
                <div v-if="esAdminOGerente" class="info-item full-width">
                  <span class="label">🏠 Dirección:</span>
                  <span class="value">{{ clienteDetalle.direccion || '-' }}</span>
                </div>
                <div v-if="!esAdminOGerente" class="info-item full-width">
                  <p class="info-limitada">
                    ℹ️ Información completa solo visible para administradores
                  </p>
                </div>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="stats-cards">
              <div class="mini-stat-card">
                <div class="mini-stat-icon">🛒</div>
                <div class="mini-stat-content">
                  <h4>{{ clienteDetalle.estadisticas?.total_compras || 0 }}</h4>
                  <p>Total Compras</p>
                </div>
              </div>
              <div v-if="esAdminOGerente" class="mini-stat-card">
                <div class="mini-stat-icon">💰</div>
                <div class="mini-stat-content">
                  <h4>S/ {{ formatNumber(clienteDetalle.estadisticas?.total_gastado || 0) }}</h4>
                  <p>Total Gastado</p>
                </div>
              </div>
              <div class="mini-stat-card">
                <div class="mini-stat-icon">📅</div>
                <div class="mini-stat-content">
                  <h4>{{ formatDate(clienteDetalle.estadisticas?.ultima_compra) || '-' }}</h4>
                  <p>Última Compra</p>
                </div>
              </div>
            </div>

            <!-- Historial de compras -->
            <div class="historial-section">
              <h3>🛒 Historial de Compras</h3>
              <div v-if="clienteDetalle.historial_compras?.length">
                <table class="historial-table">
                  <thead>
                    <tr>
                      <th>N° Venta</th>
                      <th>Fecha</th>
                      <th v-if="esAdminOGerente">Total</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="venta in clienteDetalle.historial_compras" :key="venta.id">
                      <td>
                        <span class="numero-venta">{{ venta.numero_venta }}</span>
                      </td>
                      <td>{{ formatDateTime(venta.fecha) }}</td>
                      <td v-if="esAdminOGerente" class="total">
                        S/ {{ formatNumber(venta.total) }}
                      </td>
                      <td>
                        <span class="badge-estado" :class="venta.estado">
                          {{ venta.estado }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="no-data">
                <p>Este cliente aún no tiene compras registradas</p>
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
import clientesService from '@/services/clientes'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'ClientesView',
  components: {
    LayoutMain,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      clientes: [],
      clientesFrecuentes: [],
      stats: {},
      clienteDetalle: null,
      loading: false,
      showModalCliente: false,
      showModalDetalle: false,
      modoEdicion: false,
      busqueda: '',
      formCliente: {
        tipo_documento: 'DNI',
        numero_documento: '',
        nombres: '',
        apellidos: '',
        email: '',
        telefono: '',
        direccion: '',
        distrito: '',
        fecha_nacimiento: '',
        observaciones: '',
      },
    }
  },
  computed: {
    esAdminOGerente() {
      const user = this.authStore.user
      return user?.role_id === 1 || user?.role_id === 2
    },
    puedeCrear() {
      return this.authStore.canCreate('clientes')
    },
    puedeEditar() {
      return this.authStore.canEdit('clientes')
    },
    puedeEliminar() {
      return this.authStore.canDelete('clientes')
    },
    clientesFiltrados() {
      if (!this.busqueda) return this.clientes

      const termino = this.busqueda.toLowerCase()
      return this.clientes.filter((c) => {
        const nombre = c.nombre_completo?.toLowerCase().includes(termino)

        if (!this.esAdminOGerente) {
          return nombre
        }

        return (
          nombre ||
          c.numero_documento?.toLowerCase().includes(termino) ||
          c.telefono?.toLowerCase().includes(termino) ||
          c.email?.toLowerCase().includes(termino)
        )
      })
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
          this.cargarClientes(),
          this.cargarStats(),
          this.cargarClientesFrecuentes(),
        ])
      } finally {
        this.loading = false
      }
    },

    async cargarClientes() {
      try {
        this.clientes = await clientesService.getAll()
      } catch (error) {
        console.error('Error al cargar clientes:', error)
        alert('Error al cargar clientes')
      }
    },

    async cargarStats() {
      try {
        this.stats = await clientesService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    async cargarClientesFrecuentes() {
      try {
        this.clientesFrecuentes = await clientesService.getClientesFrecuentes()
      } catch (error) {
        console.error('Error al cargar clientes frecuentes:', error)
      }
    },

    abrirModalNuevoCliente() {
      this.modoEdicion = false
      this.formCliente = {
        tipo_documento: 'DNI',
        numero_documento: '',
        nombres: '',
        apellidos: '',
        email: '',
        telefono: '',
        direccion: '',
        distrito: '',
        fecha_nacimiento: '',
        observaciones: '',
      }
      this.showModalCliente = true
    },

    abrirModalEditar(cliente) {
      if (!this.puedeEditar) {
        alert('⚠️ No tienes permisos para editar clientes')
        return
      }

      this.modoEdicion = true
      this.formCliente = {
        id: cliente.id,
        tipo_documento: cliente.tipo_documento,
        numero_documento: cliente.numero_documento,
        nombres: cliente.nombres,
        apellidos: cliente.apellidos,
        email: cliente.email,
        telefono: cliente.telefono,
        direccion: cliente.direccion,
        distrito: cliente.distrito,
        fecha_nacimiento: cliente.fecha_nacimiento,
        observaciones: cliente.observaciones,
      }
      this.showModalCliente = true
    },

    async guardarCliente() {
      try {
        if (this.modoEdicion) {
          await clientesService.update(this.formCliente.id, this.formCliente)
          alert('✅ Cliente actualizado exitosamente')
        } else {
          await clientesService.create(this.formCliente)
          alert('✅ Cliente creado exitosamente')
        }
        this.cerrarModales()
        this.cargarDatos()
      } catch (error) {
        console.error('Error al guardar cliente:', error)
        const mensaje = error.response?.data?.message || 'Error al guardar cliente'
        alert('❌ ' + mensaje)
      }
    },

    async verDetalle(id) {
      try {
        this.clienteDetalle = await clientesService.getById(id)
        this.showModalDetalle = true
      } catch (error) {
        console.error('Error al cargar detalle:', error)
        alert('Error al cargar detalle del cliente')
      }
    },

    async eliminarCliente(id) {
      if (!this.puedeEliminar) {
        alert('⚠️ No tienes permisos para eliminar clientes')
        return
      }

      if (!confirm('⚠️ ¿Estás seguro de eliminar este cliente?')) return

      try {
        await clientesService.delete(id)
        alert('✅ Cliente eliminado exitosamente')
        this.cargarDatos()
      } catch (error) {
        console.error('Error al eliminar cliente:', error)
        const mensaje = error.response?.data?.message || 'Error al eliminar cliente'
        alert('❌ ' + mensaje)
      }
    },

    cerrarModales() {
      this.showModalCliente = false
      this.showModalDetalle = false
      this.clienteDetalle = null
    },

    getPlaceholderDocumento() {
      const placeholders = {
        DNI: '12345678',
        RUC: '20123456789',
        CE: 'ABC123456',
        Pasaporte: 'ABC123456',
      }
      return placeholders[this.formCliente.tipo_documento] || ''
    },

    getMaxLengthDocumento() {
      const maxLengths = {
        DNI: 8,
        RUC: 11,
        CE: 12,
        Pasaporte: 12,
      }
      return maxLengths[this.formCliente.tipo_documento] || 20
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num || 0)
    },

    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      })
    },

    formatDateTime(date) {
      if (!date) return '-'
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
/* Todos los estilos se mantienen igual, solo agrega esto al final: */
.clientes-page {
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

/* Filtros */
.filtros-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
}

.filtros {
  display: flex;
  gap: 15px;
}

.input-busqueda {
  flex: 1;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.input-busqueda:focus {
  outline: none;
  border-color: #667eea;
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow-x: auto;
}

.clientes-table {
  width: 100%;
  border-collapse: collapse;
}

.clientes-table thead {
  background: #f8f9fa;
}

.clientes-table th {
  padding: 16px 12px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
  font-size: 12px;
  text-transform: uppercase;
}

.clientes-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.clientes-table tbody tr:hover {
  background: #f8f9ff;
}

.codigo {
  font-family: 'Courier New', monospace;
  background: #e3f2fd;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #1976d2;
  font-weight: 600;
}

.documento {
  font-family: 'Courier New', monospace;
  font-size: 13px;
  color: #666;
}

.badge-frecuente {
  background: #fff3cd;
  color: #856404;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  margin-left: 8px;
}

.badge-compras {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 12px;
}

.total-gastado {
  color: #27ae60;
  font-weight: 700;
  font-size: 15px;
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

.btn-delete:hover {
  background: #fee;
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
  overflow-y: auto;
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
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

.modal-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 20px;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
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

/* Detalle del cliente */
.cliente-info-card {
  background: #f8f9fa;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 25px;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #dee2e6;
}

.info-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 22px;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.info-item .label {
  font-size: 12px;
  color: #7f8c8d;
  font-weight: 600;
}

.info-item .value {
  font-size: 14px;
  color: #2c3e50;
}

.info-limitada {
  color: #95a5a6;
  font-size: 13px;
  font-style: italic;
  text-align: center;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 15px;
  margin-bottom: 25px;
}

.mini-stat-card {
  background: white;
  border: 2px solid #e9ecef;
  padding: 15px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.mini-stat-icon {
  font-size: 28px;
}

.mini-stat-content h4 {
  margin: 0;
  font-size: 18px;
  color: #2c3e50;
}

.mini-stat-content p {
  margin: 3px 0 0 0;
  font-size: 11px;
  color: #7f8c8d;
}

.historial-section h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 18px;
}

.historial-table {
  width: 100%;
  border-collapse: collapse;
}

.historial-table thead {
  background: #f8f9fa;
}

.historial-table th {
  padding: 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
  border-bottom: 2px solid #e9ecef;
}

.historial-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
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

.total {
  color: #27ae60;
  font-weight: 700;
}

.badge-estado {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-estado.completada {
  background: #d4edda;
  color: #155724;
}

.badge-estado.cancelada {
  background: #f8d7da;
  color: #721c24;
}

.no-data {
  text-align: center;
  padding: 30px;
  color: #95a5a6;
}

/* Responsive */
@media (max-width: 768px) {
  .clientes-page {
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

  .form-row,
  .info-grid,
  .stats-cards {
    grid-template-columns: 1fr;
  }
}
</style>
