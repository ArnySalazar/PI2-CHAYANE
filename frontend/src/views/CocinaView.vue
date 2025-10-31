<template>
  <LayoutMain>
    <div class="cocina-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>👨‍🍳 Cocina</h1>
          <p class="subtitle">Gestión de pedidos en tiempo real</p>
        </div>
        <div class="header-actions">
          <button @click="cargarPedidos" class="btn-refresh" :disabled="loading">
            🔄 {{ loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>
      </div>

      <!-- Stats rápidas -->
      <div class="stats-grid">
        <div class="stat-card yellow">
          <div class="stat-icon">⏳</div>
          <div class="stat-content">
            <h3>{{ stats.pedidos_pendientes || 0 }}</h3>
            <p>Pendientes</p>
          </div>
        </div>

        <div class="stat-card blue">
          <div class="stat-icon">👨‍🍳</div>
          <div class="stat-content">
            <h3>{{ stats.pedidos_en_preparacion || 0 }}</h3>
            <p>En Preparación</p>
          </div>
        </div>

        <div class="stat-card green">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <h3>{{ stats.pedidos_listos_hoy || 0 }}</h3>
            <p>Listos Hoy</p>
          </div>
        </div>

        <div class="stat-card purple">
          <div class="stat-icon">⏱️</div>
          <div class="stat-content">
            <h3>{{ formatTiempo(stats.tiempo_promedio_preparacion) }}</h3>
            <p>Tiempo Promedio</p>
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
            Todos ({{ pedidos.length }})
          </button>
          <button
            @click="filtroEstado = 'pendiente'"
            class="btn-filtro"
            :class="{ active: filtroEstado === 'pendiente' }"
          >
            ⏳ Pendientes ({{ pedidosPorEstado('pendiente').length }})
          </button>
          <button
            @click="filtroEstado = 'en_preparacion'"
            class="btn-filtro"
            :class="{ active: filtroEstado === 'en_preparacion' }"
          >
            👨‍🍳 En Preparación ({{ pedidosPorEstado('en_preparacion').length }})
          </button>
        </div>
      </div>

      <!-- Alerta para usuarios sin permisos de edición -->
      <div v-if="!puedeEditarCocina" class="alerta-permisos">
        <span class="icono-info">ℹ️</span>
        <div>
          <strong>Modo Solo Lectura</strong>
          <p>Solo puedes visualizar los pedidos. No tienes permisos para cambiar estados.</p>
        </div>
      </div>

      <!-- Grid de pedidos -->
      <div v-if="loading && pedidos.length === 0" class="loading-state">
        <span class="spinner">⏳</span>
        <p>Cargando pedidos...</p>
      </div>

      <div v-else-if="pedidosFiltrados.length === 0" class="empty-state">
        <span class="empty-icon">✨</span>
        <h3>
          No hay pedidos {{ filtroEstado !== 'todos' ? filtroEstado.replace('_', ' ') : 'activos' }}
        </h3>
        <p>Los nuevos pedidos aparecerán aquí automáticamente</p>
      </div>

      <div v-else class="pedidos-grid">
        <div
          v-for="pedido in pedidosFiltrados"
          :key="pedido.id"
          class="pedido-card"
          :class="[
            `estado-${pedido.estado_cocina}`,
            { urgente: pedido.minutos_espera > 30 },
            { prioritario: pedido.prioridad === 'urgente' || pedido.prioridad === 'alta' },
          ]"
        >
          <!-- Header del pedido -->
          <div class="pedido-header">
            <div class="pedido-info">
              <h3>{{ pedido.numero_venta }}</h3>
              <span class="mesa-badge" v-if="pedido.mesa_id"> 🍽️ Mesa {{ pedido.mesa_id }} </span>
              <span class="cliente-badge" v-if="pedido.cliente_nombre">
                👤 {{ pedido.cliente_nombre }}
              </span>
            </div>
            <div class="tiempo-espera" :class="getTiempoClass(pedido.minutos_espera)">
              ⏱️ {{ formatTiempo(pedido.minutos_espera) }}
            </div>
          </div>

          <!-- Estado actual -->
          <div class="estado-actual">
            <span class="badge-estado" :class="pedido.estado_cocina">
              {{ getTextoEstado(pedido.estado_cocina) }}
            </span>
            <span v-if="pedido.prioridad && pedido.prioridad !== 'normal'" class="badge-prioridad">
              🔥 {{ pedido.prioridad }}
            </span>
          </div>

          <!-- Productos del pedido -->
          <div class="pedido-productos">
            <div v-for="detalle in pedido.detalles" :key="detalle.id" class="producto-item">
              <span class="cantidad">{{ detalle.cantidad }}x</span>
              <span class="nombre">{{ detalle.producto_nombre }}</span>
            </div>
          </div>

          <!-- Notas especiales -->
          <div v-if="pedido.notas_cocina" class="pedido-notas">
            <strong>📝 Notas:</strong> {{ pedido.notas_cocina }}
          </div>

          <!-- Info adicional -->
          <div class="pedido-footer">
            <small> <strong>Mesero:</strong> {{ pedido.mesero_nombre || 'N/A' }} </small>
            <small>
              {{ formatDateTime(pedido.fecha) }}
            </small>
          </div>

          <!-- Acciones -->
          <div class="pedido-actions">
            <button
              v-if="pedido.estado_cocina === 'pendiente' && puedeEditarCocina"
              @click="iniciarPreparacion(pedido.id)"
              class="btn-action btn-iniciar"
            >
              👨‍🍳 Iniciar Preparación
            </button>

            <button
              v-if="pedido.estado_cocina === 'en_preparacion' && puedeEditarCocina"
              @click="marcarListo(pedido.id)"
              class="btn-action btn-listo"
            >
              ✅ Marcar como Listo
            </button>

            <button @click="verDetalle(pedido)" class="btn-action btn-detalle">
              👁️ Ver Detalle
            </button>

            <span
              v-if="!puedeEditarCocina && pedido.estado_cocina !== 'listo'"
              class="sin-acciones"
            >
              👁️ Solo lectura
            </span>
          </div>
        </div>
      </div>

      <!-- Modal: Detalle del pedido -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal">
          <div class="modal-header">
            <h2>📋 Detalle del Pedido</h2>
            <button @click="cerrarModal" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="pedidoDetalle">
            <div class="detalle-info">
              <div class="info-row">
                <span class="label">N° Pedido:</span>
                <span class="value">{{ pedidoDetalle.numero_venta }}</span>
              </div>
              <div class="info-row" v-if="pedidoDetalle.mesa_id">
                <span class="label">Mesa:</span>
                <span class="value">{{ pedidoDetalle.mesa_id }}</span>
              </div>
              <div class="info-row" v-if="pedidoDetalle.cliente_nombre">
                <span class="label">Cliente:</span>
                <span class="value">{{ pedidoDetalle.cliente_nombre }}</span>
              </div>
              <div class="info-row">
                <span class="label">Mesero:</span>
                <span class="value">{{ pedidoDetalle.mesero_nombre || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Hora:</span>
                <span class="value">{{ formatDateTime(pedidoDetalle.fecha) }}</span>
              </div>
              <div class="info-row">
                <span class="label">Tiempo de espera:</span>
                <span class="value">{{ formatTiempo(pedidoDetalle.minutos_espera) }}</span>
              </div>
              <div class="info-row">
                <span class="label">Estado:</span>
                <span class="badge-estado" :class="pedidoDetalle.estado_cocina">
                  {{ getTextoEstado(pedidoDetalle.estado_cocina) }}
                </span>
              </div>
            </div>

            <div class="detalle-productos">
              <h3>Productos:</h3>
              <table class="productos-table">
                <thead>
                  <tr>
                    <th>Cant.</th>
                    <th>Producto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detalle in pedidoDetalle.detalles" :key="detalle.id">
                    <td class="cantidad-col">{{ detalle.cantidad }}</td>
                    <td>{{ detalle.producto_nombre }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="pedidoDetalle.notas_cocina" class="detalle-notas">
              <h3>📝 Notas Especiales:</h3>
              <p>{{ pedidoDetalle.notas_cocina }}</p>
            </div>

            <div class="modal-actions">
              <button
                v-if="pedidoDetalle.estado_cocina === 'pendiente' && puedeEditarCocina"
                @click="iniciarPreparacion(pedidoDetalle.id)"
                class="btn-action btn-iniciar"
              >
                👨‍🍳 Iniciar Preparación
              </button>

              <button
                v-if="pedidoDetalle.estado_cocina === 'en_preparacion' && puedeEditarCocina"
                @click="marcarListo(pedidoDetalle.id)"
                class="btn-action btn-listo"
              >
                ✅ Marcar como Listo
              </button>

              <button @click="cerrarModal" class="btn-action btn-cancel">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import cocinaService from '@/services/cocina'
import { useAuthStore } from '@/stores/auth'
import echo from '@/plugins/echo'

export default {
  name: 'CocinaView',
  components: {
    LayoutMain,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      pedidos: [],
      stats: {},
      loading: false,
      filtroEstado: 'todos',
      showModalDetalle: false,
      pedidoDetalle: null,
      autoRefreshInterval: null,
      notificationAudio: null,
    }
  },
  computed: {
    puedeEditarCocina() {
      return this.authStore.canEdit('cocina')
    },
    pedidosFiltrados() {
      if (this.filtroEstado === 'todos') {
        return this.pedidos
      }
      return this.pedidos.filter((p) => p.estado_cocina === this.filtroEstado)
    },
  },
  mounted() {
    // Inicializar sonido de notificación
    this.notificationAudio = new Audio(
      'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3',
    )

    this.cargarDatos()

    // Auto-refresh cada 30 segundos
    this.autoRefreshInterval = setInterval(() => {
      this.cargarDatos(true)
    }, 30000)

    // 🔔 ESCUCHAR NOTIFICACIONES DE PUSHER
    this.escucharNotificaciones()
  },
  beforeUnmount() {
    if (this.autoRefreshInterval) {
      clearInterval(this.autoRefreshInterval)
    }

    // Desconectar de Pusher
    echo.leave('cocina')
  },
  methods: {
    async cargarDatos(silent = false) {
      if (!silent) this.loading = true

      try {
        await Promise.all([this.cargarPedidos(), this.cargarStats()])
      } finally {
        if (!silent) this.loading = false
      }
    },

    async cargarPedidos() {
      try {
        this.pedidos = await cocinaService.getPedidos()
      } catch (error) {
        console.error('Error al cargar pedidos:', error)
        if (!this.loading) {
          alert('Error al cargar pedidos')
        }
      }
    },

    async cargarStats() {
      try {
        this.stats = await cocinaService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    // 🔔 ESCUCHAR NOTIFICACIONES DE PUSHER
    escucharNotificaciones() {
      console.log('🎧 Conectando a Pusher...')
      console.log('📡 Canal: cocina')
      console.log('🔊 Evento: .nuevo-pedido')

      echo
        .channel('cocina')
        .listen('.nuevo-pedido', (data) => {
          console.log('🔔 Nuevo pedido recibido:', data)

          console.log('1️⃣ Intentando reproducir sonido...')
          this.notificationAudio.play().catch((e) => console.log('Error al reproducir sonido:', e))

          console.log('2️⃣ Llamando mostrarNotificacionNavegador...')
          this.mostrarNotificacionNavegador(data)

          console.log('3️⃣ Llamando mostrarToast...')
          this.mostrarToast(data)

          console.log('4️⃣ Recargando pedidos...')
          this.cargarPedidos()

          console.log('✅ Todo ejecutado')
        })
        .error((error) => {
          console.error('❌ Error en canal Pusher:', error)
        })

      console.log('✅ Listener configurado')
    },

    // 🔔 NOTIFICACIÓN DEL NAVEGADOR
    mostrarNotificacionNavegador(data) {
      if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('🍽️ Nuevo Pedido en Cocina', {
          body: `${data.numero_venta} - ${data.cliente_nombre}${data.mesa_id ? ' (Mesa ' + data.mesa_id + ')' : ''}`,
          icon: '/favicon.ico',
          tag: 'nuevo-pedido',
        })
      }
    },

    // 🔔 TOAST NOTIFICATION
    mostrarToast(data) {
      console.log('📢 Dentro de mostrarToast, data:', data)

      const toast = document.createElement('div')
      toast.className = 'toast-notification'
      toast.innerHTML = `
    <div class="toast-icon">🔔</div>
    <div class="toast-content">
      <strong>${data.mensaje}</strong>
      <p>${data.numero_venta} - ${data.cliente_nombre}</p>
    </div>
  `

      console.log('📢 Toast creado:', toast)

      document.body.appendChild(toast)

      console.log('📢 Toast agregado al body')

      setTimeout(() => {
        toast.classList.add('show')
        console.log('📢 Clase "show" agregada')
      }, 100)

      setTimeout(() => {
        toast.classList.remove('show')
        console.log('📢 Toast ocultándose...')
        setTimeout(() => {
          toast.remove()
          console.log('📢 Toast removido')
        }, 300)
      }, 5000)
    },

    async iniciarPreparacion(id) {
      if (!this.puedeEditarCocina) {
        alert('⚠️ No tienes permisos para cambiar estados de cocina')
        return
      }

      try {
        await cocinaService.iniciarPreparacion(id)
        await this.cargarDatos()
        if (this.showModalDetalle) {
          this.cerrarModal()
        }
      } catch (error) {
        console.error('Error al iniciar preparación:', error)
        const mensaje = error.response?.data?.message || 'Error al iniciar preparación'
        alert('❌ ' + mensaje)
      }
    },

    async marcarListo(id) {
      if (!this.puedeEditarCocina) {
        alert('⚠️ No tienes permisos para cambiar estados de cocina')
        return
      }

      try {
        await cocinaService.marcarListo(id)
        await this.cargarDatos()
        if (this.showModalDetalle) {
          this.cerrarModal()
        }
      } catch (error) {
        console.error('Error al marcar como listo:', error)
        const mensaje = error.response?.data?.message || 'Error al marcar como listo'
        alert('❌ ' + mensaje)
      }
    },

    verDetalle(pedido) {
      this.pedidoDetalle = pedido
      this.showModalDetalle = true
    },

    cerrarModal() {
      this.showModalDetalle = false
      this.pedidoDetalle = null
    },

    pedidosPorEstado(estado) {
      return this.pedidos.filter((p) => p.estado_cocina === estado)
    },

    getTextoEstado(estado) {
      const textos = {
        pendiente: '⏳ Pendiente',
        en_preparacion: '👨‍🍳 En Preparación',
        listo: '✅ Listo',
      }
      return textos[estado] || estado
    },

    getTiempoClass(minutos) {
      if (minutos > 30) return 'urgente'
      if (minutos > 15) return 'advertencia'
      return 'normal'
    },

    formatTiempo(minutos) {
      if (!minutos || minutos < 1) return '< 1 min'
      const mins = Math.floor(minutos)
      if (mins < 60) return `${mins} min`
      const horas = Math.floor(mins / 60)
      const minsRestantes = mins % 60
      return `${horas}h ${minsRestantes}m`
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
.cocina-page {
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

.btn-refresh {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s;
}

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.stat-card.yellow {
  border-left: 4px solid #f39c12;
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

/* Alerta de permisos */
.alerta-permisos {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  border-left: 4px solid #2196f3;
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.icono-info {
  font-size: 32px;
}

.alerta-permisos strong {
  display: block;
  color: #1565c0;
  font-size: 16px;
  margin-bottom: 5px;
}

.alerta-permisos p {
  margin: 0;
  color: #1976d2;
  font-size: 14px;
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

/* Grid de pedidos */
.pedidos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.pedido-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #95a5a6;
  transition: all 0.3s;
}

.pedido-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.pedido-card.estado-pendiente {
  border-left-color: #f39c12;
}

.pedido-card.estado-en_preparacion {
  border-left-color: #3498db;
}

.pedido-card.urgente {
  border-left-width: 6px;
  border-left-color: #e74c3c;
  background: #fff5f5;
}

.pedido-card.prioritario {
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
}

.pedido-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.pedido-info h3 {
  margin: 0 0 8px 0;
  color: #2c3e50;
  font-size: 20px;
}

.mesa-badge,
.cliente-badge {
  display: inline-block;
  background: #e8f4f8;
  color: #2980b9;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  margin-right: 5px;
}

.tiempo-espera {
  background: #e8f5e9;
  color: #27ae60;
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
}

.tiempo-espera.advertencia {
  background: #fff3cd;
  color: #856404;
}

.tiempo-espera.urgente {
  background: #f8d7da;
  color: #721c24;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.estado-actual {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.badge-estado {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
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

.badge-prioridad {
  background: #fee;
  color: #c33;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.pedido-productos {
  margin-bottom: 15px;
}

.producto-item {
  display: flex;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.producto-item:last-child {
  border-bottom: none;
}

.producto-item .cantidad {
  background: #667eea;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
  font-size: 14px;
  min-width: 35px;
  text-align: center;
}

.producto-item .nombre {
  flex: 1;
  color: #2c3e50;
  font-weight: 500;
}

.pedido-notas {
  background: #fff3cd;
  border-left: 3px solid #ffc107;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 4px;
  font-size: 13px;
}

.pedido-footer {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
}

.pedido-footer small {
  color: #7f8c8d;
  font-size: 12px;
}

.pedido-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-action {
  flex: 1;
  min-width: 120px;
  padding: 10px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
}

.btn-iniciar {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
}

.btn-iniciar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.btn-listo {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  color: white;
}

.btn-listo:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

.btn-detalle {
  background: white;
  border: 2px solid #e9ecef;
  color: #7f8c8d;
}

.btn-detalle:hover {
  border-color: #667eea;
  color: #667eea;
}

.sin-acciones {
  color: #95a5a6;
  font-size: 13px;
  font-style: italic;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 10px;
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
  margin: 0 0 10px 0;
  color: #2c3e50;
}

.empty-state p {
  color: #7f8c8d;
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
  border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
  border-bottom: none;
}

.info-row .label {
  font-weight: 600;
  color: #7f8c8d;
}

.info-row .value {
  color: #2c3e50;
}

.detalle-productos h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
}

.productos-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.productos-table th {
  background: #f8f9fa;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
}

.productos-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.cantidad-col {
  text-align: center;
  font-weight: 700;
  color: #667eea;
}

.detalle-notas {
  background: #fff3cd;
  border-left: 3px solid #ffc107;
  padding: 15px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.detalle-notas h3 {
  margin: 0 0 10px 0;
  font-size: 16px;
}

.detalle-notas p {
  margin: 0;
  color: #856404;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
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
}

.btn-cancel:hover {
  background: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
  .cocina-page {
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

  .pedidos-grid {
    grid-template-columns: 1fr;
  }
}

/* Toast Notifications */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  gap: 15px;
  z-index: 9999;
  min-width: 300px;
  transform: translateX(400px);
  opacity: 0;
  transition: all 0.3s ease;
}

.toast-notification.show {
  transform: translateX(0);
  opacity: 1;
}

.toast-icon {
  font-size: 32px;
  animation: ring 1s ease-in-out infinite;
}

@keyframes ring {
  0%,
  100% {
    transform: rotate(0deg);
  }
  10%,
  30% {
    transform: rotate(-10deg);
  }
  20%,
  40% {
    transform: rotate(10deg);
  }
}

.toast-content strong {
  display: block;
  font-size: 16px;
  margin-bottom: 5px;
}

.toast-content p {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}
</style>

<style>
/* Toast Notifications - SIN SCOPED para elementos dinámicos */
.toast-notification {
  position: fixed !important;
  top: 20px !important;
  right: 20px !important;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: white !important;
  padding: 20px !important;
  border-radius: 12px !important;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
  display: flex !important;
  align-items: center !important;
  gap: 15px !important;
  z-index: 99999 !important;
  min-width: 300px !important;
  transform: translateX(400px) !important;
  opacity: 0 !important;
  transition: all 0.3s ease !important;
}

.toast-notification.show {
  transform: translateX(0) !important;
  opacity: 1 !important;
}

.toast-notification .toast-icon {
  font-size: 32px !important;
  animation: toast-ring 1s ease-in-out infinite !important;
}

@keyframes toast-ring {
  0%,
  100% {
    transform: rotate(0deg);
  }
  10%,
  30% {
    transform: rotate(-10deg);
  }
  20%,
  40% {
    transform: rotate(10deg);
  }
}

.toast-notification .toast-content strong {
  display: block !important;
  font-size: 16px !important;
  margin-bottom: 5px !important;
}

.toast-notification .toast-content p {
  margin: 0 !important;
  font-size: 14px !important;
  opacity: 0.9 !important;
}
</style>
