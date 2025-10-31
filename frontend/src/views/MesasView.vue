<template>
  <LayoutMain>
    <div class="mesas-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>🍽️ Gestión de Mesas</h1>
          <p class="subtitle">Control visual del restaurante</p>
        </div>
        <div class="header-actions">
          <button @click="cargarMesas" class="btn-refresh" :disabled="loading">
            🔄 {{ loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
          <button @click="mostrarModalNuevaMesa" class="btn-primary">➕ Nueva Mesa</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card total">
          <div class="stat-icon">🍽️</div>
          <div class="stat-content">
            <h3>{{ stats.total_mesas || 0 }}</h3>
            <p>Total Mesas</p>
          </div>
        </div>

        <div class="stat-card libre">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <h3>{{ stats.mesas_libres || 0 }}</h3>
            <p>Libres</p>
          </div>
        </div>

        <div class="stat-card ocupada">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <h3>{{ stats.mesas_ocupadas || 0 }}</h3>
            <p>Ocupadas</p>
          </div>
        </div>

        <div class="stat-card reservada">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>{{ stats.mesas_reservadas || 0 }}</h3>
            <p>Reservadas</p>
          </div>
        </div>

        <div class="stat-card ocupacion">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3>{{ stats.ocupacion_porcentaje || 0 }}%</h3>
            <p>Ocupación</p>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtros">
          <button
            @click="filtroEstado = 'todos'"
            class="btn-filtro"
            :class="{ active: filtroEstado === 'todos' }"
          >
            Todas ({{ mesas.length }})
          </button>
          <button
            @click="filtroEstado = 'libre'"
            class="btn-filtro libre"
            :class="{ active: filtroEstado === 'libre' }"
          >
            ✅ Libres ({{ mesasPorEstado('libre').length }})
          </button>
          <button
            @click="filtroEstado = 'ocupada'"
            class="btn-filtro ocupada"
            :class="{ active: filtroEstado === 'ocupada' }"
          >
            👥 Ocupadas ({{ mesasPorEstado('ocupada').length }})
          </button>
          <button
            @click="filtroEstado = 'reservada'"
            class="btn-filtro reservada"
            :class="{ active: filtroEstado === 'reservada' }"
          >
            📅 Reservadas ({{ mesasPorEstado('reservada').length }})
          </button>
        </div>
      </div>

      <!-- Grid de Mesas -->
      <div v-if="loading && mesas.length === 0" class="loading-state">
        <span class="spinner">⏳</span>
        <p>Cargando mesas...</p>
      </div>

      <div v-else-if="mesasFiltradas.length === 0" class="empty-state">
        <span class="empty-icon">🍽️</span>
        <h3>No hay mesas {{ filtroEstado !== 'todos' ? filtroEstado + 's' : '' }}</h3>
        <button @click="mostrarModalNuevaMesa" class="btn-primary">➕ Crear Primera Mesa</button>
      </div>

      <div v-else class="mesas-grid">
        <div
          v-for="mesa in mesasFiltradas"
          :key="mesa.id"
          class="mesa-card"
          :class="[mesa.estado, { 'con-pedidos': mesa.pedidos_activos > 0 }]"
          @click="verDetalleMesa(mesa)"
        >
          <!-- Número de mesa -->
          <div class="mesa-header">
            <h3>Mesa {{ mesa.numero }}</h3>
            <span class="capacidad-badge"> 👥 {{ mesa.capacidad }} pers. </span>
          </div>

          <!-- Estado -->
          <div class="mesa-estado">
            <span class="badge-estado" :class="mesa.estado">
              {{ getTextoEstado(mesa.estado) }}
            </span>
          </div>

          <!-- Info de ubicación -->
          <div class="mesa-ubicacion" v-if="mesa.ubicacion">📍 {{ mesa.ubicacion }}</div>

          <!-- Info de pedidos activos -->
          <div v-if="mesa.pedidos_activos > 0" class="mesa-pedidos">
            <div class="pedidos-info">
              <span>📋 {{ mesa.pedidos_activos }} pedido(s)</span>
              <span class="total">S/ {{ formatNumber(mesa.total_cuenta) }}</span>
            </div>
            <div class="tiempo-ocupacion" v-if="mesa.hora_inicio">
              ⏱️ {{ calcularTiempo(mesa.hora_inicio) }}
            </div>
          </div>

          <!-- Acciones rápidas -->
          <div class="mesa-actions" @click.stop>
            <button
              v-if="mesa.estado === 'libre'"
              @click="cambiarEstado(mesa.id, 'ocupada')"
              class="btn-action ocupar"
              title="Marcar como ocupada"
            >
              👥
            </button>
            <button
              v-if="mesa.estado === 'libre'"
              @click="cambiarEstado(mesa.id, 'reservada')"
              class="btn-action reservar"
              title="Reservar"
            >
              📅
            </button>
            <button
              v-if="mesa.estado !== 'libre'"
              @click="cambiarEstado(mesa.id, 'libre')"
              class="btn-action liberar"
              title="Liberar mesa"
            >
              ✅
            </button>
            <button @click="verDetalleMesa(mesa)" class="btn-action detalles" title="Ver detalles">
              👁️
            </button>
          </div>
        </div>
      </div>

      <!-- Modal: Detalle de Mesa -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal modal-large">
          <div class="modal-header">
            <h2>🍽️ Mesa {{ mesaSeleccionada?.numero }}</h2>
            <button @click="cerrarModal" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="mesaSeleccionada">
            <!-- Info de la mesa -->
            <div class="detalle-grid">
              <div class="info-card">
                <h3>📋 Información</h3>
                <div class="info-row">
                  <span class="label">Estado:</span>
                  <span class="badge-estado" :class="mesaSeleccionada.estado">
                    {{ getTextoEstado(mesaSeleccionada.estado) }}
                  </span>
                </div>
                <div class="info-row">
                  <span class="label">Capacidad:</span>
                  <span>{{ mesaSeleccionada.capacidad }} personas</span>
                </div>
                <div class="info-row" v-if="mesaSeleccionada.ubicacion">
                  <span class="label">Ubicación:</span>
                  <span>{{ mesaSeleccionada.ubicacion }}</span>
                </div>
              </div>

              <div class="info-card" v-if="mesaDetalle.pedidos?.length > 0">
                <h3>💰 Cuenta</h3>
                <div class="info-row">
                  <span class="label">Pedidos:</span>
                  <span>{{ mesaDetalle.pedidos.length }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Total:</span>
                  <span class="total-cuenta">S/ {{ formatNumber(mesaDetalle.total_cuenta) }}</span>
                </div>
                <div class="info-row" v-if="mesaSeleccionada.hora_inicio">
                  <span class="label">Tiempo:</span>
                  <span>{{ calcularTiempo(mesaSeleccionada.hora_inicio) }}</span>
                </div>
              </div>
            </div>

            <!-- Pedidos de la mesa -->
            <div v-if="mesaDetalle.pedidos?.length > 0" class="pedidos-mesa">
              <h3>📋 Pedidos Activos</h3>
              <div class="pedidos-list">
                <div v-for="pedido in mesaDetalle.pedidos" :key="pedido.id" class="pedido-item">
                  <div class="pedido-header-item">
                    <strong>{{ pedido.numero_venta }}</strong>
                    <span class="badge-estado" :class="pedido.estado_cocina">
                      {{ getTextoEstadoCocina(pedido.estado_cocina) }}
                    </span>
                  </div>
                  <div class="pedido-productos">
                    <div v-for="detalle in pedido.detalles" :key="detalle.id" class="producto-line">
                      <span>{{ detalle.cantidad }}x {{ detalle.producto_nombre }}</span>
                      <span>S/ {{ formatNumber(detalle.precio * detalle.cantidad) }}</span>
                    </div>
                  </div>
                  <div class="pedido-footer-item">
                    <span>{{ formatDateTime(pedido.fecha) }}</span>
                    <strong>S/ {{ formatNumber(pedido.total) }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- Acciones -->
            <div class="modal-actions">
              <button
                v-if="mesaSeleccionada.estado === 'libre'"
                @click="cambiarEstado(mesaSeleccionada.id, 'ocupada')"
                class="btn-action-modal ocupar"
              >
                👥 Marcar como Ocupada
              </button>

              <button
                v-if="mesaSeleccionada.estado === 'libre'"
                @click="cambiarEstado(mesaSeleccionada.id, 'reservada')"
                class="btn-action-modal reservar"
              >
                📅 Reservar Mesa
              </button>

              <button
                v-if="mesaSeleccionada.estado !== 'libre' && mesaDetalle.pedidos?.length > 0"
                @click="mostrarModalTransferir"
                class="btn-action-modal transferir"
              >
                🔄 Transferir Pedidos
              </button>

              <button
                v-if="mesaSeleccionada.estado !== 'libre'"
                @click="liberarMesa(mesaSeleccionada.id)"
                class="btn-action-modal liberar"
              >
                ✅ Liberar Mesa
              </button>

              <button @click="cerrarModal" class="btn-action-modal cancel">Cerrar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal: Nueva Mesa -->
      <div v-if="showModalNuevaMesa" class="modal-overlay" @click.self="cerrarModalNuevaMesa">
        <div class="modal">
          <div class="modal-header">
            <h2>➕ Nueva Mesa</h2>
            <button @click="cerrarModalNuevaMesa" class="btn-close">✕</button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="crearMesa">
              <div class="form-group">
                <label>Número de Mesa *</label>
                <input
                  v-model="nuevaMesa.numero"
                  type="number"
                  required
                  placeholder="Ej: 16"
                  min="1"
                />
              </div>

              <div class="form-group">
                <label>Capacidad (personas) *</label>
                <input
                  v-model="nuevaMesa.capacidad"
                  type="number"
                  required
                  placeholder="Ej: 4"
                  min="1"
                />
              </div>

              <div class="form-group">
                <label>Ubicación</label>
                <select v-model="nuevaMesa.ubicacion">
                  <option value="">Seleccionar...</option>
                  <option value="Ventana">Ventana</option>
                  <option value="Centro">Centro</option>
                  <option value="Terraza">Terraza</option>
                  <option value="Salón VIP">Salón VIP</option>
                  <option value="Barra">Barra</option>
                </select>
              </div>

              <div class="form-group">
                <label>Notas</label>
                <textarea
                  v-model="nuevaMesa.notas"
                  placeholder="Notas adicionales..."
                  rows="3"
                ></textarea>
              </div>

              <div class="form-actions">
                <button type="button" @click="cerrarModalNuevaMesa" class="btn-cancel">
                  Cancelar
                </button>
                <button type="submit" class="btn-primary">Crear Mesa</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal: Transferir -->
      <div v-if="showModalTransferir" class="modal-overlay" @click.self="cerrarModalTransferir">
        <div class="modal">
          <div class="modal-header">
            <h2>🔄 Transferir Pedidos</h2>
            <button @click="cerrarModalTransferir" class="btn-close">✕</button>
          </div>

          <div class="modal-body">
            <p class="modal-text">
              Selecciona la mesa de destino para transferir todos los pedidos de la Mesa
              {{ mesaSeleccionada?.numero }}
            </p>

            <div class="form-group">
              <label>Mesa de Destino *</label>
              <select v-model="mesaDestinoId" required>
                <option value="">Seleccionar mesa...</option>
                <option
                  v-for="mesa in mesasDisponiblesParaTransferir"
                  :key="mesa.id"
                  :value="mesa.id"
                >
                  Mesa {{ mesa.numero }} - {{ mesa.ubicacion }} ({{ mesa.capacidad }} pers.)
                </option>
              </select>
            </div>

            <div class="form-actions">
              <button @click="cerrarModalTransferir" class="btn-cancel">Cancelar</button>
              <button @click="transferirPedidos" class="btn-primary" :disabled="!mesaDestinoId">
                Transferir
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import mesasService from '@/services/mesas'

export default {
  name: 'MesasView',
  components: {
    LayoutMain,
  },
  data() {
    return {
      mesas: [],
      stats: {},
      loading: false,
      filtroEstado: 'todos',
      showModalDetalle: false,
      showModalNuevaMesa: false,
      showModalTransferir: false,
      mesaSeleccionada: null,
      mesaDetalle: {},
      mesaDestinoId: null,
      nuevaMesa: {
        numero: null,
        capacidad: 4,
        ubicacion: '',
        notas: '',
      },
      autoRefreshInterval: null,
    }
  },
  computed: {
    mesasFiltradas() {
      if (this.filtroEstado === 'todos') {
        return this.mesas
      }
      return this.mesas.filter((m) => m.estado === this.filtroEstado)
    },
    mesasDisponiblesParaTransferir() {
      return this.mesas.filter(
        (m) => m.id !== this.mesaSeleccionada?.id && m.estado !== 'reservada',
      )
    },
  },
  mounted() {
    this.cargarDatos()
    // Auto-refresh cada 30 segundos
    this.autoRefreshInterval = setInterval(() => {
      this.cargarDatos(true)
    }, 30000)
  },
  beforeUnmount() {
    if (this.autoRefreshInterval) {
      clearInterval(this.autoRefreshInterval)
    }
  },
  methods: {
    async cargarDatos(silent = false) {
      if (!silent) this.loading = true

      try {
        await Promise.all([this.cargarMesas(), this.cargarStats()])
      } finally {
        if (!silent) this.loading = false
      }
    },

    async cargarMesas() {
      try {
        this.mesas = await mesasService.getMesas()
      } catch (error) {
        console.error('Error al cargar mesas:', error)
      }
    },

    async cargarStats() {
      try {
        this.stats = await mesasService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    async verDetalleMesa(mesa) {
      try {
        this.mesaSeleccionada = mesa
        this.mesaDetalle = await mesasService.getMesa(mesa.id)
        this.showModalDetalle = true
      } catch (error) {
        console.error('Error:', error)
        alert('Error al cargar detalles de la mesa')
      }
    },

    async cambiarEstado(id, estado) {
      if (estado === 'libre' && !confirm('¿Estás seguro de liberar esta mesa?')) {
        return
      }

      try {
        await mesasService.cambiarEstado(id, estado)
        await this.cargarDatos(true)
        if (this.showModalDetalle) {
          this.cerrarModal()
        }
      } catch (error) {
        console.error('Error:', error)
        alert('Error al cambiar estado')
      }
    },

    async liberarMesa(id) {
      if (!confirm('¿Cerrar la cuenta y liberar esta mesa?')) {
        return
      }

      try {
        await mesasService.liberarMesa(id)
        await this.cargarDatos(true)
        this.cerrarModal()
      } catch (error) {
        console.error('Error:', error)
        alert('Error al liberar mesa')
      }
    },

    async crearMesa() {
      try {
        await mesasService.createMesa(this.nuevaMesa)
        await this.cargarDatos(true)
        this.cerrarModalNuevaMesa()
        this.resetNuevaMesa()
      } catch (error) {
        console.error('Error:', error)
        alert('Error al crear mesa. Verifica que el número no esté duplicado.')
      }
    },

    mostrarModalTransferir() {
      this.showModalTransferir = true
      this.showModalDetalle = false
    },

    async transferirPedidos() {
      if (!this.mesaDestinoId) return

      try {
        await mesasService.transferirMesa(this.mesaSeleccionada.id, this.mesaDestinoId)
        await this.cargarDatos(true)
        this.cerrarModalTransferir()
        alert('Pedidos transferidos correctamente')
      } catch (error) {
        console.error('Error:', error)
        alert('Error al transferir pedidos')
      }
    },

    mostrarModalNuevaMesa() {
      this.showModalNuevaMesa = true
    },

    cerrarModal() {
      this.showModalDetalle = false
      this.mesaSeleccionada = null
      this.mesaDetalle = {}
    },

    cerrarModalNuevaMesa() {
      this.showModalNuevaMesa = false
      this.resetNuevaMesa()
    },

    cerrarModalTransferir() {
      this.showModalTransferir = false
      this.mesaDestinoId = null
    },

    resetNuevaMesa() {
      this.nuevaMesa = {
        numero: null,
        capacidad: 4,
        ubicacion: '',
        notas: '',
      }
    },

    mesasPorEstado(estado) {
      return this.mesas.filter((m) => m.estado === estado)
    },

    getTextoEstado(estado) {
      const estados = {
        libre: '✅ Libre',
        ocupada: '👥 Ocupada',
        reservada: '📅 Reservada',
      }
      return estados[estado] || estado
    },

    getTextoEstadoCocina(estado) {
      const estados = {
        pendiente: '⏳ Pendiente',
        en_preparacion: '👨‍🍳 Cocinando',
        listo: '✅ Listo',
      }
      return estados[estado] || estado
    },

    calcularTiempo(inicio) {
      if (!inicio) return '-'
      const ahora = new Date()
      const inicio_date = new Date(inicio)
      const diff = Math.floor((ahora - inicio_date) / 1000 / 60) // minutos

      if (diff < 60) return `${diff} min`
      const horas = Math.floor(diff / 60)
      const mins = diff % 60
      return `${horas}h ${mins}m`
    },

    formatNumber(num) {
      if (!num) return '0.00'
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num)
    },

    formatDateTime(date) {
      if (!date) return '-'
      return new Date(date).toLocaleString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
      })
    },
  },
}
</script>

<style scoped>
.mesas-page {
  padding: 30px;
  max-width: 1800px;
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

.header-actions {
  display: flex;
  gap: 10px;
}

.btn-refresh,
.btn-primary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s;
}

.btn-refresh {
  background: white;
  border: 2px solid #e9ecef;
  color: #2c3e50;
}

.btn-refresh:hover:not(:disabled) {
  border-color: #3498db;
  color: #3498db;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 15px;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.stat-card.total {
  border-left: 4px solid #95a5a6;
}
.stat-card.libre {
  border-left: 4px solid #27ae60;
}
.stat-card.ocupada {
  border-left: 4px solid #e67e22;
}
.stat-card.reservada {
  border-left: 4px solid #3498db;
}
.stat-card.ocupacion {
  border-left: 4px solid #9b59b6;
}

.stat-icon {
  font-size: 32px;
  line-height: 1;
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

/* Filtros */
.filtros-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.filtros {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-filtro {
  background: white;
  border: 2px solid #e9ecef;
  color: #7f8c8d;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-filtro:hover {
  border-color: #3498db;
  color: #3498db;
}

.btn-filtro.active {
  background: #3498db;
  border-color: #3498db;
  color: white;
}

.btn-filtro.libre.active {
  background: #27ae60;
  border-color: #27ae60;
}

.btn-filtro.ocupada.active {
  background: #e67e22;
  border-color: #e67e22;
}

.btn-filtro.reservada.active {
  background: #3498db;
  border-color: #3498db;
}

/* Grid de Mesas */
.mesas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.mesa-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #95a5a6;
  transition: all 0.3s;
  cursor: pointer;
  position: relative;
}

.mesa-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.mesa-card.libre {
  border-left-color: #27ae60;
  background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
}

.mesa-card.ocupada {
  border-left-color: #e67e22;
  background: linear-gradient(135deg, #ffffff 0%, #fff5f0 100%);
}

.mesa-card.reservada {
  border-left-color: #3498db;
  background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%);
}

.mesa-card.con-pedidos {
  border-left-width: 6px;
}

.mesa-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.mesa-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 22px;
  font-weight: 700;
}

.capacidad-badge {
  background: #e8f4f8;
  color: #2980b9;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.mesa-estado {
  margin-bottom: 12px;
}

.badge-estado {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-estado.libre {
  background: #d1f2eb;
  color: #0c7c59;
}

.badge-estado.ocupada {
  background: #fadbd8;
  color: #922b21;
}

.badge-estado.reservada {
  background: #d4e6f1;
  color: #21618c;
}

.badge-estado.pendiente {
  background: #fff3cd;
  color: #856404;
}

.badge-estado.en_preparacion {
  background: #cfe2ff;
  color: #084298;
}

.badge-estado.listo {
  background: #d1e7dd;
  color: #0f5132;
}

.mesa-ubicacion {
  color: #7f8c8d;
  font-size: 13px;
  margin-bottom: 12px;
}

.mesa-pedidos {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 12px;
}

.pedidos-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-weight: 600;
}

.pedidos-info .total {
  color: #27ae60;
  font-size: 18px;
}

.tiempo-ocupacion {
  color: #7f8c8d;
  font-size: 12px;
}

.mesa-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

.btn-action {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
  background: white;
  border: 2px solid #e9ecef;
}

.btn-action:hover {
  transform: scale(1.05);
}

.btn-action.ocupar {
  background: #fadbd8;
  border-color: #e67e22;
}

.btn-action.ocupar:hover {
  background: #e67e22;
  color: white;
}

.btn-action.reservar {
  background: #d4e6f1;
  border-color: #3498db;
}

.btn-action.reservar:hover {
  background: #3498db;
  color: white;
}

.btn-action.liberar {
  background: #d1f2eb;
  border-color: #27ae60;
}

.btn-action.liberar:hover {
  background: #27ae60;
  color: white;
}

.btn-action.detalles {
  background: #f8f9fa;
  border-color: #95a5a6;
}

.btn-action.detalles:hover {
  background: #95a5a6;
  color: white;
}

/* Loading y Empty States */
.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}

.spinner {
  font-size: 48px;
  display: block;
  margin-bottom: 20px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.empty-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 20px;
  opacity: 0.3;
}

.empty-state h3 {
  margin: 0 0 20px 0;
  color: #2c3e50;
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
  max-width: 800px;
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

.modal-text {
  color: #7f8c8d;
  margin-bottom: 20px;
}

.detalle-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.info-card {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.info-card h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
  border-bottom: none;
}

.info-row .label {
  font-weight: 600;
  color: #7f8c8d;
}

.info-row .total-cuenta {
  font-size: 20px;
  font-weight: 700;
  color: #27ae60;
}

.pedidos-mesa {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.pedidos-mesa h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
}

.pedidos-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.pedido-item {
  background: white;
  padding: 15px;
  border-radius: 8px;
  border-left: 3px solid #3498db;
}

.pedido-header-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.pedido-productos {
  margin-bottom: 10px;
}

.producto-line {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  font-size: 14px;
}

.pedido-footer-item {
  display: flex;
  justify-content: space-between;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
  font-size: 13px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.btn-action-modal {
  flex: 1;
  min-width: 150px;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
}

.btn-action-modal.ocupar {
  background: #e67e22;
  color: white;
}

.btn-action-modal.ocupar:hover {
  background: #d35400;
}

.btn-action-modal.reservar {
  background: #3498db;
  color: white;
}

.btn-action-modal.reservar:hover {
  background: #2980b9;
}

.btn-action-modal.transferir {
  background: #9b59b6;
  color: white;
}

.btn-action-modal.transferir:hover {
  background: #8e44ad;
}

.btn-action-modal.liberar {
  background: #27ae60;
  color: white;
}

.btn-action-modal.liberar:hover {
  background: #229954;
}

.btn-action-modal.cancel {
  background: white;
  border: 2px solid #e9ecef;
  color: #7f8c8d;
}

.btn-action-modal.cancel:hover {
  background: #f8f9fa;
}

/* Form */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #2c3e50;
  font-weight: 600;
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
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.form-group textarea {
  resize: vertical;
  font-family: inherit;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
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
}

/* Responsive */
@media (max-width: 768px) {
  .mesas-page {
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

  .mesas-grid {
    grid-template-columns: 1fr;
  }

  .modal-actions {
    flex-direction: column;
  }

  .btn-action-modal {
    width: 100%;
  }
}
</style>
