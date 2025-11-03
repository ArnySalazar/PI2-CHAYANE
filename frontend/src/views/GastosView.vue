<template>
  <LayoutMain>
    <div class="gastos-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>💸 Gestión de Gastos</h1>
          <p class="subtitle">Control de egresos y salidas de dinero</p>
        </div>
        <button v-if="puedeCrear" @click="abrirModalNuevo" class="btn-primary">
          ➕ Nuevo Gasto
        </button>
      </div>

      <!-- Estadísticas -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.gastos_hoy || 0) }}</h3>
            <p>Gastos Hoy</p>
          </div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon">📆</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.gastos_semana || 0) }}</h3>
            <p>Esta Semana</p>
          </div>
        </div>
        <div class="stat-card purple">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.gastos_mes || 0) }}</h3>
            <p>Este Mes</p>
          </div>
        </div>
        <div class="stat-card orange">
          <div class="stat-icon">📈</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.gastos_anio || 0) }}</h3>
            <p>Este Año</p>
          </div>
        </div>
      </div>

      <!-- Gráfica por categorías -->
      <div v-if="stats.por_categoria && stats.por_categoria.length > 0" class="categorias-card">
        <h3>📊 Gastos por Categoría (Este Mes)</h3>
        <div class="categorias-list">
          <div v-for="cat in stats.por_categoria" :key="cat.categoria" class="categoria-item">
            <div class="categoria-info">
              <span class="categoria-nombre">{{ cat.categoria }}</span>
              <span class="categoria-cantidad">({{ cat.cantidad }} gastos)</span>
            </div>
            <div class="categoria-monto">
              <strong>S/ {{ formatNumber(cat.total) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtros-row">
          <input
            v-model="filtros.busqueda"
            type="text"
            placeholder="🔍 Buscar por concepto, número o proveedor..."
            class="input-busqueda"
            @input="filtrar"
          />
          <select v-model="filtros.categoria_id" @change="filtrar" class="select-filtro">
            <option value="">Todas las categorías</option>
            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
              {{ cat.nombre }}
            </option>
          </select>
          <input v-model="filtros.fecha_desde" type="date" class="input-fecha" @change="filtrar" />
          <input v-model="filtros.fecha_hasta" type="date" class="input-fecha" @change="filtrar" />
        </div>
      </div>

      <!-- Tabla de gastos -->
      <div class="table-container">
        <table class="gastos-table">
          <thead>
            <tr>
              <th>N° Gasto</th>
              <th>Fecha</th>
              <th>Categoría</th>
              <th>Concepto</th>
              <th>Proveedor</th>
              <th>Monto</th>
              <th>Método Pago</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="text-center loading">
                <span class="spinner">⏳</span> Cargando gastos...
              </td>
            </tr>
            <tr v-else-if="gastos.length === 0">
              <td colspan="8" class="text-center empty">
                <div class="empty-state">
                  <span class="empty-icon">💸</span>
                  <p>No hay gastos registrados</p>
                </div>
              </td>
            </tr>
            <tr v-else v-for="gasto in gastos" :key="gasto.id">
              <td>
                <span class="numero-gasto">{{ gasto.numero_gasto }}</span>
              </td>
              <td>{{ formatDate(gasto.fecha) }}</td>
              <td>
                <span class="badge-categoria">{{ gasto.categoria_nombre }}</span>
              </td>
              <td>{{ gasto.concepto }}</td>
              <td>{{ gasto.proveedor_nombre || '-' }}</td>
              <td class="monto">S/ {{ formatNumber(gasto.monto) }}</td>
              <td>
                <span class="badge-metodo">{{ gasto.metodo_pago }}</span>
              </td>
              <td class="actions">
                <button @click="verDetalle(gasto)" class="btn-icon" title="Ver">👁️</button>
                <button v-if="puedeEditar" @click="editar(gasto)" class="btn-icon" title="Editar">
                  ✏️
                </button>
                <button
                  v-if="puedeEliminar"
                  @click="eliminar(gasto.id)"
                  class="btn-icon"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Nuevo/Editar Gasto -->
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ modoEdicion ? '✏️ Editar Gasto' : '➕ Nuevo Gasto' }}</h2>
            <button @click="cerrarModal" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardar" class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>Fecha: *</label>
                <input v-model="form.fecha" type="date" required />
              </div>
              <div class="form-group">
                <label>Categoría: *</label>
                <select v-model="form.categoria_gasto_id" required>
                  <option value="">Seleccionar...</option>
                  <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                    {{ cat.nombre }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Concepto: *</label>
              <input
                v-model="form.concepto"
                type="text"
                placeholder="Descripción del gasto"
                required
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Monto: *</label>
                <input
                  v-model="form.monto"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  required
                />
              </div>
              <div class="form-group">
                <label>Método de Pago: *</label>
                <select v-model="form.metodo_pago" required>
                  <option value="efectivo">💵 Efectivo</option>
                  <option value="transferencia">🏦 Transferencia</option>
                  <option value="tarjeta">💳 Tarjeta</option>
                  <option value="yape">📱 Yape/Plin</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Proveedor:</label>
                <select v-model="form.proveedor_id">
                  <option value="">Ninguno</option>
                  <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">
                    {{ prov.nombre_comercial }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Comprobante:</label>
                <input v-model="form.comprobante" type="text" placeholder="N° Factura/Boleta" />
              </div>
            </div>

            <div class="form-group">
              <label>Observaciones:</label>
              <textarea
                v-model="form.observaciones"
                rows="3"
                placeholder="Notas adicionales..."
              ></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" @click="cerrarModal" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-submit">
                💾 {{ modoEdicion ? 'Actualizar' : 'Registrar' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal Detalle -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="showModalDetalle = false">
        <div class="modal">
          <div class="modal-header">
            <h2>📄 Detalle del Gasto</h2>
            <button @click="showModalDetalle = false" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="gastoDetalle">
            <div class="detalle-info">
              <div class="info-row">
                <strong>N° Gasto:</strong>
                <span>{{ gastoDetalle.numero_gasto }}</span>
              </div>
              <div class="info-row">
                <strong>Fecha:</strong>
                <span>{{ formatDate(gastoDetalle.fecha) }}</span>
              </div>
              <div class="info-row">
                <strong>Categoría:</strong>
                <span>{{ gastoDetalle.categoria_nombre }}</span>
              </div>
              <div class="info-row">
                <strong>Concepto:</strong>
                <span>{{ gastoDetalle.concepto }}</span>
              </div>
              <div class="info-row">
                <strong>Monto:</strong>
                <span class="monto-grande">S/ {{ formatNumber(gastoDetalle.monto) }}</span>
              </div>
              <div class="info-row">
                <strong>Método de Pago:</strong>
                <span>{{ gastoDetalle.metodo_pago }}</span>
              </div>
              <div class="info-row" v-if="gastoDetalle.proveedor_nombre">
                <strong>Proveedor:</strong>
                <span>{{ gastoDetalle.proveedor_nombre }}</span>
              </div>
              <div class="info-row" v-if="gastoDetalle.comprobante">
                <strong>Comprobante:</strong>
                <span>{{ gastoDetalle.comprobante }}</span>
              </div>
              <div class="info-row" v-if="gastoDetalle.usuario_nombre">
                <strong>Registrado por:</strong>
                <span>{{ gastoDetalle.usuario_nombre }}</span>
              </div>
              <div class="info-row" v-if="gastoDetalle.observaciones">
                <strong>Observaciones:</strong>
                <span>{{ gastoDetalle.observaciones }}</span>
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
import gastosService from '@/services/gastos'
import proveedoresService from '@/services/proveedores'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'GastosView',
  components: {
    LayoutMain,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      gastos: [],
      proveedores: [],
      categorias: [],
      stats: {},
      loading: false,
      showModal: false,
      showModalDetalle: false,
      modoEdicion: false,
      gastoDetalle: null,
      filtros: {
        busqueda: '',
        categoria_id: '',
        fecha_desde: '',
        fecha_hasta: '',
      },
      form: {
        fecha: new Date().toISOString().split('T')[0],
        categoria_gasto_id: '',
        concepto: '',
        monto: '',
        metodo_pago: 'efectivo',
        proveedor_id: '',
        comprobante: '',
        observaciones: '',
      },
    }
  },
  computed: {
    puedeCrear() {
      return this.authStore.canCreate('gastos')
    },
    puedeEditar() {
      return this.authStore.canEdit('gastos')
    },
    puedeEliminar() {
      return this.authStore.canDelete('gastos')
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
          this.cargarGastos(),
          this.cargarStats(),
          this.cargarCategorias(),
          this.cargarProveedores(),
        ])
      } finally {
        this.loading = false
      }
    },

    async cargarGastos() {
      try {
        this.gastos = await gastosService.getAll(this.filtros)
      } catch (error) {
        console.error('Error al cargar gastos:', error)
        alert('Error al cargar gastos')
      }
    },

    async cargarStats() {
      try {
        this.stats = await gastosService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    async cargarCategorias() {
      try {
        this.categorias = await gastosService.getCategorias()
      } catch (error) {
        console.error('Error al cargar categorías:', error)
      }
    },

    async cargarProveedores() {
      try {
        this.proveedores = await proveedoresService.getAll()
      } catch (error) {
        console.error('Error al cargar proveedores:', error)
      }
    },

    filtrar() {
      this.cargarGastos()
    },

    abrirModalNuevo() {
      this.modoEdicion = false
      this.form = {
        fecha: new Date().toISOString().split('T')[0],
        categoria_gasto_id: '',
        concepto: '',
        monto: '',
        metodo_pago: 'efectivo',
        proveedor_id: '',
        comprobante: '',
        observaciones: '',
      }
      this.showModal = true
    },

    editar(gasto) {
      this.modoEdicion = true
      this.form = {
        id: gasto.id,
        fecha: gasto.fecha,
        categoria_gasto_id: gasto.categoria_gasto_id,
        concepto: gasto.concepto,
        monto: gasto.monto,
        metodo_pago: gasto.metodo_pago,
        proveedor_id: gasto.proveedor_id || '',
        comprobante: gasto.comprobante || '',
        observaciones: gasto.observaciones || '',
      }
      this.showModal = true
    },

    async guardar() {
      try {
        if (this.modoEdicion) {
          await gastosService.update(this.form.id, this.form)
          alert('✅ Gasto actualizado exitosamente')
        } else {
          await gastosService.create(this.form)
          alert('✅ Gasto registrado exitosamente')
        }
        this.cerrarModal()
        this.cargarDatos()
      } catch (error) {
        console.error('Error al guardar:', error)
        alert('❌ Error al guardar gasto')
      }
    },

    async verDetalle(gasto) {
      try {
        this.gastoDetalle = await gastosService.getById(gasto.id)
        this.showModalDetalle = true
      } catch (error) {
        console.error('Error al cargar detalle:', error)
        alert('Error al cargar detalle')
      }
    },

    async eliminar(id) {
      if (!confirm('⚠️ ¿Estás seguro de eliminar este gasto?')) return

      try {
        await gastosService.delete(id)
        alert('✅ Gasto eliminado exitosamente')
        this.cargarDatos()
      } catch (error) {
        console.error('Error al eliminar:', error)
        alert('❌ Error al eliminar gasto')
      }
    },

    cerrarModal() {
      this.showModal = false
      this.showModalDetalle = false
      this.gastoDetalle = null
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num || 0)
    },

    formatDate(date) {
      if (!date) return '-'
      return new Date(date + 'T00:00:00').toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      })
    },
  },
}
</script>

<style scoped>
.gastos-page {
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
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
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

/* Categorías */
.categorias-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.categorias-card h3 {
  margin: 0 0 20px 0;
  color: #2c3e50;
}

.categorias-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.categoria-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #3498db;
}

.categoria-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.categoria-nombre {
  font-weight: 600;
  color: #2c3e50;
  font-size: 15px;
}

.categoria-cantidad {
  font-size: 12px;
  color: #7f8c8d;
}

.categoria-monto strong {
  color: #e74c3c;
  font-size: 18px;
}

/* Filtros */
.filtros-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
}

.filtros-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 15px;
}

.input-busqueda,
.select-filtro,
.input-fecha {
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.input-busqueda:focus,
.select-filtro:focus,
.input-fecha:focus {
  outline: none;
  border-color: #e74c3c;
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.gastos-table {
  width: 100%;
  border-collapse: collapse;
}

.gastos-table thead {
  background: #f8f9fa;
}

.gastos-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
  font-size: 13px;
  text-transform: uppercase;
}

.gastos-table td {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
  color: #2c3e50;
}

.gastos-table tbody tr:hover {
  background: #f8f9ff;
}

.numero-gasto {
  font-family: 'Courier New', monospace;
  background: #ffe5e5;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #c0392b;
  font-weight: 600;
}

.badge-categoria {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-metodo {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.monto {
  font-weight: 700;
  color: #e74c3c;
  font-size: 16px;
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #e74c3c;
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
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

/* Detalle */
.detalle-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #dee2e6;
}

.info-row:last-child {
  border-bottom: none;
}

.monto-grande {
  font-size: 24px;
  font-weight: 700;
  color: #e74c3c;
}

/* Responsive */
@media (max-width: 768px) {
  .gastos-page {
    padding: 20px 15px;
  }

  .filtros-row {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
