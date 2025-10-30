import api from './api'

export default {
  async getAll() {
    const response = await api.get('/ventas')
    return response.data
  },

  async create(venta) {
    const response = await api.post('/ventas', venta)
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/ventas/${id}`)
    return response.data
  },

  async cancel(id) {
    const response = await api.post(`/ventas/${id}/cancel`)
    return response.data
  },

  async getStats() {
    const response = await api.get('/ventas-stats')
    return response.data
  },
}
