<template>
  <LayoutMain>
    <div class="insumos-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>📦 Gestión de Insumos</h1>
          <p class="subtitle">Control de materia prima y almacén del restaurante</p>
        </div>
        <div class="header-actions">
          <button @click="abrirModalMovimiento" class="btn-secondary">
            📝 Registrar Movimiento
          </button>
          <button @click="abrirModalNuevoInsumo" class="btn-primary">➕ Nuevo Insumo</button>
        </div>
      </div>

      <!-- Stats rápidas -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon">📦</div>
          <div class="stat-content">
            <h3>{{ stats.total_insumos || 0 }}</h3>
            <p>Total Insumos</p>
          </div>
        </div>

        <div class="stat-card orange">
          <div class="stat-icon">⚠️</div>
          <div class="stat-content">
            <h3>{{ stats.insumos_stock_bajo || 0 }}</h3>
            <p>Stock Bajo</p>
          </div>
        </div>

        <div class="stat-card red">
          <div class="stat-icon">❌</div>
          <div class="stat-content">
            <h3>{{ stats.insumos_agotados || 0 }}</h3>
            <p>Agotados</p>
          </div>
        </div>

        <div class="stat-card green">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <h3>S/ {{ formatNumber(stats.valor_inventario || 0) }}</h3>
            <p>Valor Total</p>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtros">
          <input
            v-model="busqueda"
            type="text"
            placeholder="🔍 Buscar insumo..."
            class="input-busqueda"
          />
          <select v-model="filtroCategoria" class="select-filtro">
            <option value="">Todas las categorías</option>
            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
              {{ cat.nombre }}
            </option>
          </select>
          <select v-model="filtroEstadoStock" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="ok">Stock normal</option>
            <option value="bajo">Stock bajo</option>
            <option value="agotado">Agotados</option>
          </select>
        </div>
      </div>

      <!-- Tabla de insumos -->
      <div class="table-container">
        <table class="insumos-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Insumo</th>
              <th>Categoría</th>
              <th>Stock</th>
              <th>Stock Mín.</th>
              <th>Unidad</th>
              <th>Precio</th>
              <th>Proveedor</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="10" class="text-center loading">
                <span class="spinner">⏳</span> Cargando insumos...
              </td>
            </tr>
            <tr v-else-if="insumosFiltrados.length === 0">
              <td colspan="10" class="text-center empty">
                <div class="empty-state">
                  <span class="empty-icon">📦</span>
                  <p>No hay insumos registrados</p>
                </div>
              </td>
            </tr>
            <tr
              v-else
              v-for="insumo in insumosFiltrados"
              :key="insumo.id"
              :class="{
                'stock-bajo': insumo.estado_stock === 'bajo',
                'stock-agotado': insumo.estado_stock === 'agotado',
              }"
            >
              <td>
                <span class="codigo">{{ insumo.codigo }}</span>
              </td>
              <td>
                <strong>{{ insumo.nombre }}</strong>
              </td>
              <td>
                <span class="badge-categoria">{{ insumo.categoria_nombre }}</span>
              </td>
              <td class="stock-cell">
                <span class="stock-valor">{{ formatNumber(insumo.stock_actual) }}</span>
              </td>
              <td class="text-center">{{ formatNumber(insumo.stock_minimo) }}</td>
              <td class="text-center">{{ insumo.unidad_medida }}</td>
              <td>S/ {{ formatNumber(insumo.precio_compra) }}</td>
              <td>
                <small>{{ insumo.proveedor || '-' }}</small>
              </td>
              <td>
                <span class="badge-estado" :class="insumo.estado_stock">
                  {{ getTextoEstadoStock(insumo.estado_stock) }}
                </span>
              </td>
              <td class="actions">
                <button @click="verMovimientos(insumo)" class="btn-icon" title="Ver movimientos">
                  📋
                </button>
                <button @click="abrirModalEditar(insumo)" class="btn-icon" title="Editar">
                  ✏️
                </button>
                <button
                  @click="eliminarInsumo(insumo.id)"
                  class="btn-icon btn-delete"
                  title="Eliminar"
                >
                  🗑️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal: Nuevo/Editar Insumo -->
      <div v-if="showModalInsumo" class="modal-overlay" @click.self="cerrarModales">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ modoEdicion ? '✏️ Editar Insumo' : '➕ Nuevo Insumo' }}</h2>
            <button @click="cerrarModales" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardarInsumo" class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>Nombre: *</label>
                <input
                  v-model="formInsumo.nombre"
                  type="text"
                  required
                  placeholder="Ej: Papa blanca"
                />
              </div>
              <div class="form-group">
                <label>Categoría: *</label>
                <select v-model="formInsumo.categoria_id" required>
                  <option value="">Seleccionar...</option>
                  <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                    {{ cat.nombre }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Unidad de Medida: *</label>
                <select v-model="formInsumo.unidad_medida" required>
                  <option value="kg">Kilogramos (kg)</option>
                  <option value="gramos">Gramos (g)</option>
                  <option value="litros">Litros (L)</option>
                  <option value="ml">Mililitros (ml)</option>
                  <option value="unidad">Unidades</option>
                  <option value="paquete">Paquetes</option>
                  <option value="caja">Cajas</option>
                </select>
              </div>
              <div class="form-group">
                <label>Stock Actual: *</label>
                <input
                  v-model.number="formInsumo.stock_actual"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Stock Mínimo: *</label>
                <input
                  v-model.number="formInsumo.stock_minimo"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                />
              </div>
              <div class="form-group">
                <label>Stock Máximo:</label>
                <input v-model.number="formInsumo.stock_maximo" type="number" step="0.01" min="0" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Precio de Compra:</label>
                <input
                  v-model.number="formInsumo.precio_compra"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                />
              </div>
              <div class="form-group">
                <label>Proveedor:</label>
                <input
                  v-model="formInsumo.proveedor"
                  type="text"
                  placeholder="Nombre del proveedor"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Ubicación en Almacén:</label>
              <input v-model="formInsumo.ubicacion" type="text" placeholder="Ej: Estante A-3" />
            </div>

            <div class="form-group">
              <label>Descripción:</label>
              <textarea
                v-model="formInsumo.descripcion"
                rows="3"
                placeholder="Detalles adicionales..."
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

      <!-- Modal: Registrar Movimiento -->
      <div v-if="showModalMovimiento" class="modal-overlay" @click.self="cerrarModales">
        <div class="modal">
          <div class="modal-header">
            <h2>📝 Registrar Movimiento de Inventario</h2>
            <button @click="cerrarModales" class="btn-close">✕</button>
          </div>

          <form @submit.prevent="guardarMovimiento" class="modal-body">
            <div class="form-group">
              <label>Insumo: *</label>
              <select v-model="formMovimiento.insumo_id" required @change="onInsumoChange">
                <option value="">Seleccionar insumo...</option>
                <option v-for="ins in insumos" :key="ins.id" :value="ins.id">
                  {{ ins.nombre }} (Stock: {{ formatNumber(ins.stock_actual) }}
                  {{ ins.unidad_medida }})
                </option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Tipo de Movimiento: *</label>
                <select v-model="formMovimiento.tipo_movimiento" required>
                  <option value="entrada">➕ Entrada (Compra/Ingreso)</option>
                  <option value="salida">➖ Salida (Uso/Consumo)</option>
                </select>
              </div>
              <div class="form-group">
                <label>Cantidad: *</label>
                <input
                  v-model.number="formMovimiento.cantidad"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Motivo: *</label>
                <select v-model="formMovimiento.motivo" required>
                  <option value="">Seleccionar...</option>
                  <optgroup v-if="formMovimiento.tipo_movimiento === 'entrada'" label="Entradas">
                    <option value="compra">Compra</option>
                    <option value="devolucion">Devolución</option>
                    <option value="ajuste_positivo">Ajuste Positivo</option>
                    <option value="donacion">Donación</option>
                  </optgroup>
                  <optgroup v-if="formMovimiento.tipo_movimiento === 'salida'" label="Salidas">
                    <option value="uso_produccion">Uso en Producción</option>
                    <option value="merma">Merma/Desperdicio</option>
                    <option value="ajuste_negativo">Ajuste Negativo</option>
                    <option value="vencido">Producto Vencido</option>
                  </optgroup>
                </select>
              </div>
              <div class="form-group">
                <label>Precio Unitario:</label>
                <input
                  v-model.number="formMovimiento.precio_unitario"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Referencia (Factura, Orden, etc.):</label>
              <input v-model="formMovimiento.referencia" type="text" placeholder="Ej: FAC-001234" />
            </div>

            <div class="form-group">
              <label>Descripción:</label>
              <textarea
                v-model="formMovimiento.descripcion"
                rows="3"
                placeholder="Detalles del movimiento..."
              ></textarea>
            </div>

            <div v-if="insumoSeleccionado" class="info-box">
              <p>
                <strong>Stock actual:</strong> {{ formatNumber(insumoSeleccionado.stock_actual) }}
                {{ insumoSeleccionado.unidad_medida }}
              </p>
              <p v-if="formMovimiento.cantidad && formMovimiento.tipo_movimiento">
                <strong>Stock después del movimiento:</strong>
                <span :class="{ 'text-danger': calcularStockFinal() < 0 }">
                  {{ formatNumber(calcularStockFinal()) }} {{ insumoSeleccionado.unidad_medida }}
                </span>
              </p>
            </div>

            <div class="modal-actions">
              <button type="button" @click="cerrarModales" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-submit" :disabled="calcularStockFinal() < 0">
                💾 Registrar Movimiento
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal: Ver Movimientos -->
      <div v-if="showModalHistorial" class="modal-overlay" @click.self="cerrarModales">
        <div class="modal modal-large">
          <div class="modal-header">
            <h2>📋 Historial de Movimientos</h2>
            <button @click="cerrarModales" class="btn-close">✕</button>
          </div>

          <div class="modal-body" v-if="insumoDetalle">
            <div class="insumo-info">
              <h3>{{ insumoDetalle.nombre }}</h3>
              <p>
                <strong>Stock actual:</strong> {{ formatNumber(insumoDetalle.stock_actual) }}
                {{ insumoDetalle.unidad_medida }}
              </p>
            </div>

            <div class="movimientos-list">
              <div v-if="movimientos.length === 0" class="no-data">
                <p>No hay movimientos registrados</p>
              </div>
              <table v-else class="movimientos-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Stock Anterior</th>
                    <th>Stock Nuevo</th>
                    <th>Referencia</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="mov in movimientos" :key="mov.id">
                    <td>{{ formatDateTime(mov.fecha) }}</td>
                    <td>
                      <span class="badge-tipo" :class="mov.tipo_movimiento">
                        {{ mov.tipo_movimiento === 'entrada' ? '➕ Entrada' : '➖ Salida' }}
                      </span>
                    </td>
                    <td>{{ getMotivoTexto(mov.motivo) }}</td>
                    <td class="text-center">
                      <strong>{{ formatNumber(mov.cantidad) }}</strong>
                    </td>
                    <td class="text-center">{{ formatNumber(mov.stock_anterior) }}</td>
                    <td class="text-center">{{ formatNumber(mov.stock_nuevo) }}</td>
                    <td>
                      <small>{{ mov.referencia || '-' }}</small>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutMain>
</template>

<script>
import LayoutMain from '@/components/LayoutMain.vue'
import insumosService from '@/services/insumos'

export default {
  name: 'InsumosView',
  components: {
    LayoutMain,
  },
  data() {
    return {
      insumos: [],
      categorias: [],
      stats: {},
      movimientos: [],
      loading: false,
      showModalInsumo: false,
      showModalMovimiento: false,
      showModalHistorial: false,
      modoEdicion: false,
      insumoDetalle: null,
      insumoSeleccionado: null,
      busqueda: '',
      filtroCategoria: '',
      filtroEstadoStock: '',
      formInsumo: {
        nombre: '',
        descripcion: '',
        categoria_id: '',
        unidad_medida: 'kg',
        stock_actual: 0,
        stock_minimo: 10,
        stock_maximo: null,
        precio_compra: 0,
        proveedor: '',
        ubicacion: '',
      },
      formMovimiento: {
        insumo_id: '',
        tipo_movimiento: 'entrada',
        cantidad: 0,
        motivo: '',
        descripcion: '',
        precio_unitario: 0,
        referencia: '',
      },
    }
  },
  computed: {
    insumosFiltrados() {
      let resultado = this.insumos

      // Filtro de búsqueda
      if (this.busqueda) {
        const termino = this.busqueda.toLowerCase()
        resultado = resultado.filter(
          (i) =>
            i.nombre.toLowerCase().includes(termino) ||
            i.codigo.toLowerCase().includes(termino) ||
            (i.proveedor && i.proveedor.toLowerCase().includes(termino)),
        )
      }

      // Filtro de categoría
      if (this.filtroCategoria) {
        resultado = resultado.filter((i) => i.categoria_id == this.filtroCategoria)
      }

      // Filtro de estado de stock
      if (this.filtroEstadoStock) {
        resultado = resultado.filter((i) => i.estado_stock === this.filtroEstadoStock)
      }

      return resultado
    },
  },
  mounted() {
    this.cargarDatos()
  },
  methods: {
    async cargarDatos() {
      this.loading = true
      try {
        await Promise.all([this.cargarInsumos(), this.cargarCategorias(), this.cargarStats()])
      } finally {
        this.loading = false
      }
    },

    async cargarInsumos() {
      try {
        this.insumos = await insumosService.getAll()
      } catch (error) {
        console.error('Error al cargar insumos:', error)
        alert('Error al cargar insumos')
      }
    },

    async cargarCategorias() {
      try {
        this.categorias = await insumosService.getCategorias()
      } catch (error) {
        console.error('Error al cargar categorías:', error)
      }
    },

    async cargarStats() {
      try {
        this.stats = await insumosService.getStats()
      } catch (error) {
        console.error('Error al cargar estadísticas:', error)
      }
    },

    abrirModalNuevoInsumo() {
      this.modoEdicion = false
      this.formInsumo = {
        nombre: '',
        descripcion: '',
        categoria_id: '',
        unidad_medida: 'kg',
        stock_actual: 0,
        stock_minimo: 10,
        stock_maximo: null,
        precio_compra: 0,
        proveedor: '',
        ubicacion: '',
      }
      this.showModalInsumo = true
    },

    abrirModalEditar(insumo) {
      this.modoEdicion = true
      this.formInsumo = {
        id: insumo.id,
        nombre: insumo.nombre,
        descripcion: insumo.descripcion,
        categoria_id: insumo.categoria_id,
        unidad_medida: insumo.unidad_medida,
        stock_minimo: insumo.stock_minimo,
        stock_maximo: insumo.stock_maximo,
        precio_compra: insumo.precio_compra,
        proveedor: insumo.proveedor,
        ubicacion: insumo.ubicacion,
      }
      this.showModalInsumo = true
    },

    abrirModalMovimiento() {
      this.formMovimiento = {
        insumo_id: '',
        tipo_movimiento: 'entrada',
        cantidad: 0,
        motivo: '',
        descripcion: '',
        precio_unitario: 0,
        referencia: '',
      }
      this.insumoSeleccionado = null
      this.showModalMovimiento = true
    },

    async guardarInsumo() {
      try {
        if (this.modoEdicion) {
          await insumosService.update(this.formInsumo.id, this.formInsumo)
          alert('✅ Insumo actualizado exitosamente')
        } else {
          await insumosService.create(this.formInsumo)
          alert('✅ Insumo creado exitosamente')
        }
        this.cerrarModales()
        this.cargarDatos()
      } catch (error) {
        console.error('Error al guardar insumo:', error)
        alert('❌ Error al guardar insumo')
      }
    },

    async guardarMovimiento() {
      if (this.calcularStockFinal() < 0) {
        alert('❌ Stock insuficiente para realizar esta salida')
        return
      }

      try {
        await insumosService.registrarMovimiento(this.formMovimiento)
        alert('✅ Movimiento registrado exitosamente')
        this.cerrarModales()
        this.cargarDatos()
      } catch (error) {
        console.error('Error al registrar movimiento:', error)
        alert('❌ Error al registrar movimiento')
      }
    },

    async verMovimientos(insumo) {
      try {
        this.insumoDetalle = insumo
        this.movimientos = await insumosService.getMovimientos(insumo.id)
        this.showModalHistorial = true
      } catch (error) {
        console.error('Error al cargar movimientos:', error)
        alert('Error al cargar historial de movimientos')
      }
    },

    async eliminarInsumo(id) {
      if (!confirm('⚠️ ¿Estás seguro de eliminar este insumo?')) return

      try {
        await insumosService.delete(id)
        alert('✅ Insumo eliminado exitosamente')
        this.cargarDatos()
      } catch (error) {
        console.error('Error al eliminar insumo:', error)
        alert('❌ Error al eliminar insumo')
      }
    },

    onInsumoChange() {
      this.insumoSeleccionado = this.insumos.find((i) => i.id == this.formMovimiento.insumo_id)
    },

    calcularStockFinal() {
      if (!this.insumoSeleccionado || !this.formMovimiento.cantidad) return 0

      const stockActual = parseFloat(this.insumoSeleccionado.stock_actual)
      const cantidad = parseFloat(this.formMovimiento.cantidad)

      if (this.formMovimiento.tipo_movimiento === 'entrada') {
        return stockActual + cantidad
      } else {
        return stockActual - cantidad
      }
    },

    cerrarModales() {
      this.showModalInsumo = false
      this.showModalMovimiento = false
      this.showModalHistorial = false
      this.insumoDetalle = null
      this.insumoSeleccionado = null
    },

    getTextoEstadoStock(estado) {
      const textos = {
        ok: 'Normal',
        bajo: 'Stock Bajo',
        agotado: 'Agotado',
      }
      return textos[estado] || estado
    },

    getMotivoTexto(motivo) {
      const motivos = {
        compra: 'Compra',
        devolucion: 'Devolución',
        ajuste_positivo: 'Ajuste +',
        donacion: 'Donación',
        uso_produccion: 'Uso en Cocina',
        merma: 'Merma',
        ajuste_negativo: 'Ajuste -',
        vencido: 'Vencido',
        inicial: 'Stock Inicial',
      }
      return motivos[motivo] || motivo
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(num || 0)
    },

    formatDateTime(date) {
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
.insumos-page {
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

.header-actions {
  display: flex;
  gap: 12px;
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

.btn-secondary {
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #667eea;
  color: white;
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
.stat-card.orange {
  border-left: 4px solid #e67e22;
}
.stat-card.red {
  border-left: 4px solid #e74c3c;
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
  flex-wrap: wrap;
}

.input-busqueda,
.select-filtro {
  flex: 1;
  min-width: 200px;
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
}

.input-busqueda:focus,
.select-filtro:focus {
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

.insumos-table {
  width: 100%;
  border-collapse: collapse;
}

.insumos-table thead {
  background: #f8f9fa;
}

.insumos-table th {
  padding: 16px 12px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e9ecef;
  font-size: 12px;
  text-transform: uppercase;
}

.insumos-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.insumos-table tbody tr:hover {
  background: #f8f9ff;
}

.insumos-table tr.stock-bajo {
  background: #fff3cd;
}

.insumos-table tr.stock-agotado {
  background: #f8d7da;
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

.badge-categoria {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.stock-cell {
  text-align: center;
  font-weight: 700;
  font-size: 16px;
  color: #27ae60;
}

.badge-estado {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-estado.ok {
  background: #d4edda;
  color: #155724;
}

.badge-estado.bajo {
  background: #fff3cd;
  color: #856404;
}

.badge-estado.agotado {
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

.btn-delete:hover {
  background: #fee;
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

.info-box {
  background: #e8f4f8;
  border-left: 4px solid #3498db;
  padding: 15px;
  border-radius: 8px;
  margin: 20px 0;
}

.info-box p {
  margin: 5px 0;
  font-size: 14px;
}

.text-danger {
  color: #e74c3c;
  font-weight: 700;
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

/* Historial de movimientos */
.insumo-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.insumo-info h3 {
  margin: 0 0 10px 0;
  color: #2c3e50;
}

.insumo-info p {
  margin: 5px 0;
  color: #7f8c8d;
}

.movimientos-list {
  max-height: 500px;
  overflow-y: auto;
}

.movimientos-table {
  width: 100%;
  border-collapse: collapse;
}

.movimientos-table thead {
  background: #f8f9fa;
  position: sticky;
  top: 0;
}

.movimientos-table th {
  padding: 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
  border-bottom: 2px solid #e9ecef;
}

.movimientos-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}

.badge-tipo {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-tipo.entrada {
  background: #d4edda;
  color: #155724;
}

.badge-tipo.salida {
  background: #f8d7da;
  color: #721c24;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #95a5a6;
}

/* Responsive */
@media (max-width: 768px) {
  .insumos-page {
    padding: 20px 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filtros {
    flex-direction: column;
  }

  .input-busqueda,
  .select-filtro {
    min-width: 100%;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal {
    max-width: 100%;
    margin: 10px;
  }
}
</style>
