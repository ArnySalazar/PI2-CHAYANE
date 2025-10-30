<template>
  <LayoutMain>
    <div class="dashboard-page">
      <!-- Header -->
      <div class="header">
        <div>
          <h1>📊 Dashboard</h1>
          <p class="subtitle">Resumen general del negocio</p>
        </div>
        <div class="user-info">
          <span>👤 {{ user?.nombre }}</span>
          <span class="role-badge">{{ user?.rol }}</span>
          <button @click="handleLogout" class="btn-logout">🚪 Cerrar Sesión</button>
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

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2>⚡ Acciones Rápidas</h2>
        <div class="actions-grid">
          <button @click="$router.push('/productos')" class="action-btn blue">
            <span class="action-icon">📦</span>
            <span>Gestión de Productos</span>
          </button>
          <button class="action-btn green" disabled>
            <span class="action-icon">💵</span>
            <span>Nueva Venta</span>
            <small>(Próximamente)</small>
          </button>
          <button class="action-btn orange" disabled>
            <span class="action-icon">🍽️</span>
            <span>Gestión de Mesas</span>
            <small>(Próximamente)</small>
          </button>
          <button class="action-btn purple" disabled>
            <span class="action-icon">📊</span>
            <span>Reportes</span>
            <small>(Próximamente)</small>
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
        this.stats = await dashboardService.getStats()
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
  },
}
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
  background: #f5f7fa;
  min-height: 100vh;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header h1 {
  margin: 0;
  color: #333;
  font-size: 32px;
}

.subtitle {
  color: #777;
  margin: 5px 0 0 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.role-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

.btn-logout {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
}

.btn-logout:hover {
  background: #c0392b;
}

/* KPIs */
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
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.kpi-icon {
  font-size: 48px;
  opacity: 0.9;
}

.kpi-content h3 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.kpi-content p {
  margin: 5px 0 0 0;
  color: #777;
  font-size: 14px;
}

.kpi-card.blue {
  border-left: 5px solid #3498db;
}
.kpi-card.green {
  border-left: 5px solid #2ecc71;
}
.kpi-card.orange {
  border-left: 5px solid #e67e22;
}
.kpi-card.purple {
  border-left: 5px solid #9b59b6;
}

/* Quick Actions */
.quick-actions {
  background: white;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.quick-actions h2 {
  margin-top: 0;
  color: #333;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.action-btn {
  background: white;
  border: 2px solid #e0e0e0;
  padding: 20px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s;
}

.action-btn:not(:disabled):hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.blue:not(:disabled) {
  border-color: #3498db;
  color: #3498db;
}

.action-btn.green:not(:disabled) {
  border-color: #2ecc71;
  color: #2ecc71;
}

.action-btn.orange:not(:disabled) {
  border-color: #e67e22;
  color: #e67e22;
}

.action-btn.purple:not(:disabled) {
  border-color: #9b59b6;
  color: #9b59b6;
}

.action-icon {
  font-size: 32px;
}

.action-btn small {
  font-size: 11px;
  font-weight: normal;
  color: #999;
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
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
  margin-top: 0;
  color: #333;
}

.chart-content {
  margin-top: 20px;
}

.bar-chart-item {
  margin-bottom: 15px;
}

.bar-label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
  color: #555;
  font-weight: 600;
}

.bar-container {
  background: #f0f0f0;
  border-radius: 8px;
  height: 30px;
  position: relative;
  overflow: hidden;
}

.bar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  height: 100%;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 10px;
  transition: width 0.5s;
}

.bar-value {
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.top-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.top-rank {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  color: #333;
  font-weight: 600;
}

.top-price {
  color: #2ecc71;
  font-weight: 700;
  font-size: 16px;
}

/* Alertas */
.alerts-section {
  background: #fff3cd;
  padding: 25px;
  border-radius: 12px;
  border-left: 5px solid #e67e22;
}

.alerts-section h2 {
  margin-top: 0;
  color: #856404;
}

.alerts-list {
  display: grid;
  gap: 10px;
}

.alert-item {
  background: white;
  padding: 15px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.alert-icon {
  font-size: 24px;
}

.alert-content {
  flex: 1;
}

.alert-content strong {
  display: block;
  color: #333;
}

.alert-content small {
  color: #777;
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
  color: #999;
}

.stock-min {
  color: #2ecc71;
  font-size: 14px;
}
</style>
