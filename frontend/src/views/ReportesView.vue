<template>
  <LayoutMain>
    <div class="reportes-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>📊 Reportes y Análisis</h1>
          <p class="subtitle">Visualiza estadísticas y genera reportes del negocio</p>
        </div>
      </div>

      <!-- Tabs de reportes -->
      <div class="tabs">
        <button
          @click="tabActual = 'resumen'"
          :class="{ active: tabActual === 'resumen' }"
          class="tab-btn"
        >
          📈 Resumen
        </button>
        <button
          @click="tabActual = 'ventas'"
          :class="{ active: tabActual === 'ventas' }"
          class="tab-btn"
        >
          💰 Ventas
        </button>
        <button
          @click="tabActual = 'productos'"
          :class="{ active: tabActual === 'productos' }"
          class="tab-btn"
        >
          📦 Productos
        </button>
        <button
          @click="tabActual = 'inventario'"
          :class="{ active: tabActual === 'inventario' }"
          class="tab-btn"
        >
          📋 Inventario
        </button>
      </div>

      <!-- Tab: Resumen -->
      <div v-if="tabActual === 'resumen'" class="tab-content">
        <div class="stats-grid">
          <div class="stat-card blue">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
              <h3>{{ dashboard.ventas_hoy?.cantidad || 0 }}</h3>
              <p>Ventas Hoy</p>
              <small class="stat-detail"
                >S/ {{ formatNumber(dashboard.ventas_hoy?.total || 0) }}</small
              >
            </div>
          </div>

          <div class="stat-card green">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
              <h3>{{ dashboard.ventas_mes?.cantidad || 0 }}</h3>
              <p>Ventas del Mes</p>
              <small class="stat-detail"
                >S/ {{ formatNumber(dashboard.ventas_mes?.total || 0) }}</small
              >
            </div>
          </div>

          <div class="stat-card orange">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
              <h3>{{ dashboard.stock_bajo || 0 }}</h3>
              <p>Stock Bajo</p>
              <small class="stat-detail">Productos con stock mínimo</small>
            </div>
          </div>

          <div class="stat-card purple">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
              <h3>S/ {{ formatNumber(dashboard.valor_inventario || 0) }}</h3>
              <p>Valor Inventario</p>
              <small class="stat-detail">Total en stock</small>
            </div>
          </div>
        </div>

        <!-- Top productos del mes -->
        <div class="section-card">
          <h2>🏆 Top 5 Productos del Mes</h2>
          <div class="top-productos">
            <div v-for="(prod, index) in dashboard.top_productos" :key="index" class="top-item">
              <span class="rank">{{ index + 1 }}</span>
              <span class="nombre">{{ prod.nombre }}</span>
              <span class="cantidad">{{ prod.cantidad }} vendidos</span>
            </div>
          </div>
          <div v-if="!dashboard.top_productos?.length" class="no-data">
            <p>No hay datos de productos vendidos</p>
          </div>
        </div>
      </div>

      <!-- Tab: Ventas -->
      <div v-if="tabActual === 'ventas'" class="tab-content">
        <div class="filtros-card">
          <h3>📅 Filtrar por Fecha</h3>
          <div class="filtros-form">
            <div class="form-group">
              <label>Desde:</label>
              <input v-model="filtrosVentas.fecha_inicio" type="date" />
            </div>
            <div class="form-group">
              <label>Hasta:</label>
              <input v-model="filtrosVentas.fecha_fin" type="date" />
            </div>
            <button @click="cargarReporteVentas" class="btn-filtrar">🔍 Buscar</button>
          </div>
        </div>

        <div v-if="reporteVentas.resumen" class="stats-grid">
          <div class="stat-card blue">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
              <h3>{{ reporteVentas.resumen.total_ventas || 0 }}</h3>
              <p>Total Ventas</p>
            </div>
          </div>

          <div class="stat-card green">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
              <h3>S/ {{ formatNumber(reporteVentas.resumen.total_subtotal || 0) }}</h3>
              <p>Ingresos Totales</p>
            </div>
          </div>

          <div class="stat-card purple">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <h3>S/ {{ formatNumber(reporteVentas.resumen.promedio_venta || 0) }}</h3>
              <p>Venta Promedio</p>
            </div>
          </div>
        </div>

        <!-- Gráfico de ventas por día -->
        <div v-if="reporteVentas.ventas_por_dia?.length" class="section-card">
          <h2>📈 Ventas por Día</h2>
          <div class="chart-bars">
            <div v-for="dia in reporteVentas.ventas_por_dia" :key="dia.fecha" class="bar-item">
              <div class="bar-label">{{ formatDateShort(dia.fecha) }}</div>
              <div class="bar-container">
                <div
                  class="bar"
                  :style="{
                    height: calcularAlturaBarra(dia.total, reporteVentas.ventas_por_dia) + '%',
                  }"
                  :title="`S/ ${formatNumber(dia.otal)}`"
                >
                  <span class="bar-value">{{ dia.cantidad }}</span>
                </div>
              </div>
              <div class="bar-total">S/ {{ formatNumber(dia.total) }}</div>
            </div>
          </div>
        </div>

        <!-- Ventas por método de pago -->
        <div v-if="reporteVentas.ventas_por_metodo?.length" class="section-card">
          <h2>💳 Ventas por Método de Pago</h2>
          <div class="metodos-list">
            <div
              v-for="metodo in reporteVentas.ventas_por_metodo"
              :key="metodo.metodo_pago"
              class="metodo-item"
            >
              <div class="metodo-info">
                <span class="metodo-icon">{{ getIconoMetodo(metodo.metodo_pago) }}</span>
                <span class="metodo-nombre">{{ metodo.metodo_pago }}</span>
              </div>
              <div class="metodo-stats">
                <span class="metodo-cantidad">{{ metodo.cantidad }} ventas</span>
                <span class="metodo-total">S/ {{ formatNumber(metodo.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Productos -->
      <div v-if="tabActual === 'productos'" class="tab-content">
        <div class="section-card">
          <div class="section-header">
            <h2>🏆 Productos Más Vendidos</h2>
            <button @click="cargarProductosMasVendidos" class="btn-refresh">🔄 Actualizar</button>
          </div>

          <div class="table-container">
            <table class="reportes-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th>Cantidad Vendida</th>
                  <th>Ingresos</th>
                  <th>Precio Promedio</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, index) in productosMasVendidos" :key="prod.id">
                  <td>
                    <span class="rank-badge">{{ index + 1 }}</span>
                  </td>
                  <td>
                    <span class="codigo">{{ prod.codigo }}</span>
                  </td>
                  <td>
                    <strong>{{ prod.nombre }}</strong>
                  </td>
                  <td>
                    <span class="badge-categoria">{{ prod.categoria }}</span>
                  </td>
                  <td class="text-center">
                    <strong>{{ prod.cantidad_vendida }}</strong>
                  </td>
                  <td class="text-success">S/ {{ formatNumber(prod.ingresos_totales) }}</td>
                  <td>S/ {{ formatNumber(prod.precio_promedio) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="!productosMasVendidos.length" class="no-data">
            <p>No hay datos de productos vendidos</p>
          </div>
        </div>
      </div>

      <!-- Tab: Inventario -->
      <div v-if="tabActual === 'inventario'" class="tab-content">
        <div v-if="inventario.resumen" class="stats-grid">
          <div class="stat-card blue">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
              <h3>{{ inventario.resumen.total_productos || 0 }}</h3>
              <p>Total Productos</p>
            </div>
          </div>

          <div class="stat-card green">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
              <h3>S/ {{ formatNumber(inventario.resumen.valor_total || 0) }}</h3>
              <p>Valor Total</p>
            </div>
          </div>

          <div class="stat-card orange">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
              <h3>{{ inventario.resumen.productos_stock_bajo || 0 }}</h3>
              <p>Stock Bajo</p>
            </div>
          </div>

          <div class="stat-card red">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
              <h3>{{ inventario.resumen.productos_sin_stock || 0 }}</h3>
              <p>Sin Stock</p>
            </div>
          </div>
        </div>

        <!-- Inventario por categoría -->
        <div v-if="inventario.por_categoria?.length" class="section-card">
          <h2>📊 Inventario por Categoría</h2>
          <div class="table-container">
            <table class="reportes-table">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th>Productos</th>
                  <th>Stock Total</th>
                  <th>Valor Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cat in inventario.por_categoria" :key="cat.categoria">
                  <td>
                    <strong>{{ cat.categoria }}</strong>
                  </td>
                  <td>{{ cat.cantidad_productos }}</td>
                  <td>{{ cat.total_stock }}</td>
                  <td class="text-success">S/ {{ formatNumber(cat.valor_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lista de productos -->
        <div v-if="inventario.productos?.length" class="section-card">
          <h2>📋 Detalle de Inventario</h2>
          <div class="table-container table-scroll">
            <table class="reportes-table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th>Stock</th>
                  <th>Stock Mín.</th>
                  <th>Precio Venta</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="prod in inventario.productos"
                  :key="prod.id"
                  :class="{ 'stock-bajo': prod.stock_actual < prod.stock_minimo }"
                >
                  <td>
                    <span class="codigo">{{ prod.codigo }}</span>
                  </td>
                  <td>
                    <strong>{{ prod.nombre }}</strong>
                  </td>
                  <td>
                    <span class="badge-categoria">{{ prod.categoria }}</span>
                  </td>
                  <td class="text-center">
                    <span
                      class="badge-stock"
                      :class="{ bajo: prod.stock_actual < prod.stock_minimo }"
                    >
                      {{ prod.stock_actual }}
                    </span>
                  </td>
                  <td class="text-center">{{ prod.stock_minimo }}</td>
                  <td>S/ {{ formatNumber(prod.precio_venta) }}</td>
                  <td class="text-success">S/ {{ formatNumber(prod.valor_inventario) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import reportesService from '@/services/reportes'

export default {
  name: 'ReportesView',
  components: {
    LayoutMain,
  },
  data() {
    return {
      tabActual: 'resumen',
      dashboard: {},
      reporteVentas: {},
      productosMasVendidos: [],
      inventario: {},
      filtrosVentas: {
        fecha_inicio: this.getPrimerDiaMes(),
        fecha_fin: this.getHoy(),
      },
    }
  },
  mounted() {
    this.cargarDatos()
  },
  methods: {
    async cargarDatos() {
      await this.cargarDashboard()
      await this.cargarProductosMasVendidos()
      await this.cargarInventario()
    },

    async cargarDashboard() {
      try {
        this.dashboard = await reportesService.getDashboard()
      } catch (error) {
        console.error('Error al cargar dashboard:', error)
      }
    },

    async cargarReporteVentas() {
      try {
        this.reporteVentas = await reportesService.getVentasPorFecha(
          this.filtrosVentas.fecha_inicio,
          this.filtrosVentas.fecha_fin,
        )
      } catch (error) {
        console.error('Error al cargar reporte de ventas:', error)
        alert('Error al cargar reporte de ventas')
      }
    },

    async cargarProductosMasVendidos() {
      try {
        this.productosMasVendidos = await reportesService.getProductosMasVendidos({
          limite: 10,
        })
      } catch (error) {
        console.error('Error al cargar productos más vendidos:', error)
      }
    },

    async cargarInventario() {
      try {
        this.inventario = await reportesService.getInventario()
      } catch (error) {
        console.error('Error al cargar inventario:', error)
      }
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num || 0)
    },

    formatDateShort(date) {
      return new Date(date).toLocaleDateString('es-PE', {
        day: '2-digit',
        month: 'short',
      })
    },

    calcularAlturaBarra(valor, datos) {
      const max = Math.max(...datos.map((d) => parseFloat(d.subtotal)))
      return (valor / max) * 100
    },

    getIconoMetodo(metodo) {
      const iconos = {
        efectivo: '💵',
        tarjeta: '💳',
        yape: '📱',
        plin: '📱',
      }
      return iconos[metodo]
    },

    getPrimerDiaMes() {
      return new Date(new Date().getFullYear(), new Date().getMonth(), 1)
        .toISOString()
        .split('T')[0]
    },

    getHoy() {
      return new Date().toISOString().split('T')[0]
    },
  },
}
</script>

<style scoped>
.reportes-page {
  padding: 30px;
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
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

/* Tabs */
.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  border-bottom: 2px solid #e9ecef;
  overflow-x: auto;
}

.tab-btn {
  background: none;
  border: none;
  padding: 12px 24px;
  cursor: pointer;
  font-weight: 600;
  color: #7f8c8d;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #2c3e50;
}

.tab-btn.active {
  color: #667eea;
  border-bottom-color: #667eea;
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

.stat-detail {
  display: block;
  color: #95a5a6;
  font-size: 11px;
  margin-top: 5px;
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
.stat-card.red {
  border-left: 4px solid #e74c3c;
}

/* Section Card */
.section-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
}

.section-card h2 {
  margin: 0 0 20px 0;
  color: #2c3e50;
  font-size: 18px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  margin: 0;
}

/* Top Productos */
.top-productos {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.top-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 8px;
}

.rank {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.nombre {
  flex: 1;
  font-weight: 600;
  color: #2c3e50;
}

.cantidad {
  color: #27ae60;
  font-weight: 600;
  font-size: 14px;
}

/* Filtros */
.filtros-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
}

.filtros-card h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 16px;
}

.filtros-form {
  display: flex;
  gap: 15px;
  align-items: flex-end;
}

.form-group {
  flex: 1;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.btn-filtrar {
  background: #667eea;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  white-space: nowrap;
}

.btn-filtrar:hover {
  background: #5568d3;
}

.btn-refresh {
  background: white;
  border: 2px solid #667eea;
  color: #667eea;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
}

.btn-refresh:hover {
  background: #667eea;
  color: white;
}

/* Gráfico de barras */
.chart-bars {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  height: 300px;
  padding: 20px 0;
  overflow-x: auto;
}

.bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 80px;
}

.bar-label {
  font-size: 12px;
  color: #7f8c8d;
  margin-bottom: 10px;
  font-weight: 600;
}

.bar-container {
  flex: 1;
  width: 60px;
  background: #f0f0f0;
  border-radius: 8px 8px 0 0;
  display: flex;
  align-items: flex-end;
  position: relative;
}

.bar {
  width: 100%;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px 8px 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: height 0.5s;
  min-height: 30px;
}

.bar-value {
  color: white;
  font-weight: 700;
  font-size: 14px;
}

.bar-total {
  font-size: 11px;
  color: #27ae60;
  font-weight: 600;
  margin-top: 8px;
}

/* Métodos de pago */
.metodos-list {
  display: grid;
  gap: 12px;
}

.metodo-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.metodo-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.metodo-icon {
  font-size: 24px;
}

.metodo-nombre {
  font-weight: 600;
  color: #2c3e50;
  text-transform: capitalize;
}

.metodo-stats {
  display: flex;
  gap: 20px;
  align-items: center;
}

.metodo-cantidad {
  color: #7f8c8d;
  font-size: 14px;
}

.metodo-total {
  color: #27ae60;
  font-weight: 700;
  font-size: 16px;
}

/* Tabla */
.table-container {
  overflow-x: auto;
}

.table-scroll {
  max-height: 500px;
  overflow-y: auto;
}

.reportes-table {
  width: 100%;
  border-collapse: collapse;
}

.reportes-table thead {
  background: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 10;
}

.reportes-table th {
  padding: 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
  border-bottom: 2px solid #e9ecef;
}

.reportes-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.reportes-table tbody tr:hover {
  background: #f8f9ff;
}

.reportes-table tr.stock-bajo {
  background: #fff3cd;
}

.rank-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.codigo {
  font-family: 'Courier New', monospace;
  background: #e3f2fd;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: #1976d2;
}

.badge-categoria {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-stock {
  background: #d4edda;
  color: #155724;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: 600;
}

.badge-stock.bajo {
  background: #f8d7da;
  color: #721c24;
}

.text-center {
  text-align: center;
}

.text-success {
  color: #27ae60;
  font-weight: 600;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #95a5a6;
}

/* Responsive */
@media (max-width: 768px) {
  .reportes-page {
    padding: 20px 15px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filtros-form {
    flex-direction: column;
  }

  .chart-bars {
    height: 250px;
  }
}
</style>
