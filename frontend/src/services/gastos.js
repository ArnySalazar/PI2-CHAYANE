import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/gastos', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/gastos/${id}`)
    return response.data
  },

  async create(data) {
    const response = await api.post('/gastos', data)
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/gastos/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/gastos/${id}`)
    return response.data
  },

  async getStats() {
    const response = await api.get('/gastos/stats')
    return response.data
  },

  async getCategorias() {
    const response = await api.get('/gastos/categorias')
    return response.data
  },
}
