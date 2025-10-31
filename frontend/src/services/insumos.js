import api from './api'

export default {
  async getAll() {
    const response = await api.get('/insumos')
    return response.data
  },

  async create(insumo) {
    const response = await api.post('/insumos', insumo)
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/insumos/${id}`)
    return response.data
  },

  async update(id, insumo) {
    const response = await api.put(`/insumos/${id}`, insumo)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/insumos/${id}`)
    return response.data
  },

  async getCategorias() {
    const response = await api.get('/insumos-categorias')
    return response.data
  },

  async registrarMovimiento(movimiento) {
    const response = await api.post('/insumos/movimiento', movimiento)
    return response.data
  },

  async getMovimientos(insumoId) {
    const response = await api.get(`/insumos/${insumoId}/movimientos`)
    return response.data
  },

  async getStats() {
    const response = await api.get('/insumos-stats')
    return response.data
  },

  async getStockBajo() {
    const response = await api.get('/insumos-stock-bajo')
    return response.data
  },
}
