<template>
  <LayoutMain>
    <div class="dashboard-page">
      <!-- Header -->
      <div class="header">
        <div>
          <h1>📊 Dashboard</h1>
          <p class="subtitle">Resumen general del negocio</p>
        </div>
      </div>

      <!-- KPIs -->
      <div class="kpis-grid">
        <div class="kpi-card blue">
          <div class="kpi-icon">📦</div>
          <div class="kpi-content">
            <h3>{{ stats.kpis?.total_productos || 0 }}</h3>
            <p>Total Productos</p>
          </div>
        </div>

        <div class="kpi-card green">
          <div class="kpi-icon">💰</div>
          <div class="kpi-content">
            <h3>S/ {{ formatNumber(stats.kpis?.valor_inventario || 0) }}</h3>
            <p>Valor Inventario</p>
          </div>
        </div>

        <div class="kpi-card orange">
          <div class="kpi-icon">⚠️</div>
          <div class="kpi-content">
            <h3>{{ stats.kpis?.productos_stock_bajo || 0 }}</h3>
            <p>Stock Bajo</p>
          </div>
        </div>

        <div class="kpi-card purple">
          <div class="kpi-icon">📋</div>
          <div class="kpi-content">
            <h3>{{ stats.kpis?.total_categorias || 0 }}</h3>
            <p>Categorías</p>
          </div>
        </div>
      </div>

      <!-- Sección de Cocina -->
      <div class="section-header">
        <h2>👨‍🍳 Estado de Cocina</h2>
        <router-link to="/cocina" class="btn-link">Ver Cocina →</router-link>
      </div>

      <div class="cocina-grid">
        <div class="cocina-card pending">
          <div class="cocina-icon">⏳</div>
          <div class="cocina-info">
            <h3>{{ cocina.pedidos_pendientes || 0 }}</h3>
            <p>Pedidos Pendientes</p>
          </div>
        </div>

        <div class="cocina-card cooking">
          <div class="cocina-icon">👨‍🍳</div>
          <div class="cocina-info">
            <h3>{{ cocina.pedidos_en_preparacion || 0 }}</h3>
            <p>En Preparación</p>
          </div>
        </div>

        <div class="cocina-card" :class="{ alert: cocina.pedidos_atrasados > 0 }">
          <div class="cocina-icon">⚠️</div>
          <div class="cocina-info">
            <h3>{{ cocina.pedidos_atrasados || 0 }}</h3>
            <p>Pedidos Atrasados</p>
          </div>
        </div>

        <div class="cocina-card time">
          <div class="cocina-icon">⏱️</div>
          <div class="cocina-info">
            <h3>{{ formatTiempo(cocina.tiempo_promedio_hoy) }}</h3>
            <p>Tiempo Promedio Hoy</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2>⚡ Acciones Rápidas</h2>
        <div class="actions-grid">
          <button @click="$router.push('/productos')" class="action-btn blue">
            <span class="action-icon">📦</span>
            <span>Gestión de Productos</span>
          </button>
          <button @click="$router.push('/ventas')" class="action-btn green">
            <span class="action-icon">💵</span>
            <span>Nueva Venta</span>
          </button>
          <button @click="$router.push('/cocina')" class="action-btn orange">
            <span class="action-icon">👨‍🍳</span>
            <span>Vista de Cocina</span>
          </button>
          <button @click="$router.push('/reportes')" class="action-btn purple">
            <span class="action-icon">📊</span>
            <span>Reportes</span>
          </button>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="charts-container">
        <div class="chart-card">
          <h3>📊 Productos por Categoría</h3>
          <div class="chart-content">
            <div
              v-for="cat in stats.graficos?.productos_por_categoria"
              :key="cat.categoria"
              class="bar-chart-item"
            >
              <span class="bar-label">{{ cat.categoria }}</span>
              <div class="bar-container">
                <div class="bar" :style="{ width: (cat.cantidad / maxProductos) * 100 + '%' }">
                  <span class="bar-value">{{ cat.cantidad }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="chart-card">
          <h3>💎 Top 5 Productos Más Caros</h3>
          <div class="chart-content">
            <div
              v-for="(prod, index) in stats.graficos?.top_productos"
              :key="index"
              class="top-item"
            >
              <span class="top-rank">{{ index + 1 }}</span>
              <span class="top-name">{{ prod.nombre }}</span>
              <span class="top-price">S/ {{ prod.precio }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas -->
      <div v-if="stats.alertas?.stock_bajo?.length > 0" class="alerts-section">
        <h2>⚠️ Alertas de Stock Bajo</h2>
        <div class="alerts-list">
          <div v-for="alerta in stats.alertas.stock_bajo" :key="alerta.id" class="alert-item">
            <span class="alert-icon">📦</span>
            <div class="alert-content">
              <strong>{{ alerta.nombre }}</strong>
              <small>{{ alerta.categoria }}</small>
            </div>
            <div class="alert-stock">
              <span class="stock-current">{{ alerta.stock }}</span>
              <span class="stock-separator">/</span>
              <span class="stock-min">{{ alerta.stock_minimo }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import dashboardService from '@/services/dashboard'

export default {
  name: 'DashboardView',
  components: {
    LayoutMain,
  },
  data() {
    return {
      stats: {
        kpis: {},
        graficos: {},
        alertas: {},
      },
      cocina: {},
    }
  },
  computed: {
    maxProductos() {
      if (!this.stats.graficos?.productos_por_categoria) return 1
      return Math.max(...this.stats.graficos.productos_por_categoria.map((c) => c.cantidad))
    },
  },
  mounted() {
    this.loadStats()
  },
  methods: {
    async loadStats() {
      try {
        const response = await dashboardService.getStats()
        this.stats = response
        this.cocina = response.cocina || {}
        console.log('Datos de cocina:', this.cocina)
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num)
    },

    formatTiempo(minutos) {
      if (!minutos || minutos < 1) return '< 1 min'
      const mins = Math.floor(minutos)
      if (mins < 60) return `${mins} min`
      const horas = Math.floor(mins / 60)
      const minsRestantes = mins % 60
      return `${horas}h ${minsRestantes}m`
    },
  },
}
</script>

<style scoped>
.dashboard-page {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 32px;
}

.subtitle {
  color: #7f8c8d;
  margin: 5px 0 0 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 30px 0 20px 0;
}

.section-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 24px;
}

.btn-link {
  color: #3498db;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  padding: 8px 16px;
  border: 2px solid #3498db;
  border-radius: 8px;
  transition: all 0.3s;
}

.btn-link:hover {
  background: #3498db;
  color: white;
}

/* KPIs Grid */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.kpi-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: transform 0.3s;
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.kpi-card.blue {
  border-left: 4px solid #3498db;
}
.kpi-card.green {
  border-left: 4px solid #27ae60;
}
.kpi-card.orange {
  border-left: 4px solid #e67e22;
}
.kpi-card.purple {
  border-left: 4px solid #9b59b6;
}

.kpi-icon {
  font-size: 40px;
}

.kpi-content h3 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  color: #2c3e50;
}

.kpi-content p {
  margin: 5px 0 0 0;
  color: #7f8c8d;
  font-size: 14px;
}

/* Cocina Grid */
.cocina-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.cocina-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 15px;
  border-left: 4px solid #95a5a6;
  transition: all 0.3s;
}

.cocina-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.cocina-card.pending {
  border-left-color: #f39c12;
}

.cocina-card.cooking {
  border-left-color: #3498db;
}

.cocina-card.time {
  border-left-color: #9b59b6;
}

.cocina-card.alert {
  border-left-color: #e74c3c;
  background: #fff5f5;
  animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
  0%,
  100% {
    border-left-width: 4px;
  }
  50% {
    border-left-width: 6px;
  }
}

.cocina-icon {
  font-size: 32px;
  line-height: 1;
}

.cocina-info h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
}

.cocina-info p {
  margin: 5px 0 0 0;
  color: #7f8c8d;
  font-size: 12px;
}

/* Quick Actions */
.quick-actions {
  margin-bottom: 30px;
}

.quick-actions h2 {
  margin: 0 0 20px 0;
  color: #2c3e50;
  font-size: 24px;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.action-btn {
  background: white;
  border: 2px solid #e9ecef;
  padding: 20px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  transition: all 0.3s;
  font-weight: 600;
  font-size: 15px;
}

.action-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-icon {
  font-size: 32px;
}

.action-btn.blue:hover:not(:disabled) {
  border-color: #3498db;
  color: #3498db;
}
.action-btn.green:hover:not(:disabled) {
  border-color: #27ae60;
  color: #27ae60;
}
.action-btn.orange:hover:not(:disabled) {
  border-color: #e67e22;
  color: #e67e22;
}
.action-btn.purple:hover:not(:disabled) {
  border-color: #9b59b6;
  color: #9b59b6;
}

/* Charts */
.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.chart-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.chart-card h3 {
  margin: 0 0 20px 0;
  color: #2c3e50;
  font-size: 18px;
}

.bar-chart-item {
  margin-bottom: 15px;
}

.bar-label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.bar-container {
  background: #ecf0f1;
  border-radius: 8px;
  overflow: hidden;
  height: 30px;
}

.bar {
  background: linear-gradient(90deg, #3498db, #2980b9);
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 10px;
  transition: width 0.6s ease;
}

.bar-value {
  color: white;
  font-weight: 600;
  font-size: 12px;
}

.top-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px;
  border-bottom: 1px solid #ecf0f1;
}

.top-item:last-child {
  border-bottom: none;
}

.top-rank {
  background: #3498db;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

.top-name {
  flex: 1;
  font-weight: 500;
  color: #2c3e50;
}

.top-price {
  font-weight: 700;
  color: #27ae60;
  font-size: 16px;
}

/* Alerts */
.alerts-section {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.alerts-section h2 {
  margin: 0 0 20px 0;
  color: #e67e22;
  font-size: 20px;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #fff3cd;
  border-left: 4px solid #e67e22;
  border-radius: 8px;
  margin-bottom: 10px;
}

.alert-icon {
  font-size: 24px;
}

.alert-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.alert-content strong {
  color: #2c3e50;
  font-size: 15px;
}

.alert-content small {
  color: #7f8c8d;
  font-size: 12px;
}

.alert-stock {
  display: flex;
  align-items: center;
  gap: 5px;
  font-weight: 700;
}

.stock-current {
  color: #e74c3c;
  font-size: 18px;
}

.stock-separator {
  color: #95a5a6;
}

.stock-min {
  color: #95a5a6;
  font-size: 14px;
}

@media (max-width: 768px) {
  .dashboard-page {
    padding: 20px 15px;
  }

  .kpis-grid,
  .cocina-grid,
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .charts-container {
    grid-template-columns: 1fr;
  }
}
</style>
