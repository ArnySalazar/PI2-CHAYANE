import api from './api'

export default {
  async getDashboard() {
    const response = await api.get('/reportes/dashboard')
    return response.data
  },

  async getVentasPorFecha(fechaInicio, fechaFin) {
    const response = await api.post('/reportes/ventas-por-fecha', {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
    })
    return response.data
  },

  async getProductosMasVendidos(params = {}) {
    const response = await api.get('/reportes/productos-mas-vendidos', { params })
    return response.data
  },

  async getInventario() {
    const response = await api.get('/reportes/inventario')
    return response.data
  },

  async getVentasPorCategoria(params = {}) {
    const response = await api.get('/reportes/ventas-por-categoria', { params })
    return response.data
  },
}
