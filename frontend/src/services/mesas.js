import api from './api'

export default {
  async getMesas() {
    const response = await api.get('/mesas')
    return response.data
  },

  async getMesa(id) {
    const response = await api.get(`/mesas/${id}`)
    return response.data
  },

  async createMesa(data) {
    const response = await api.post('/mesas', data)
    return response.data
  },

  async updateMesa(id, data) {
    const response = await api.put(`/mesas/${id}`, data)
    return response.data
  },

  async deleteMesa(id) {
    const response = await api.delete(`/mesas/${id}`)
    return response.data
  },

  async cambiarEstado(id, estado) {
    const response = await api.post(`/mesas/${id}/estado`, { estado })
    return response.data
  },

  async liberarMesa(id) {
    const response = await api.post(`/mesas/${id}/liberar`)
    return response.data
  },

  async transferirMesa(id, mesa_destino_id) {
    const response = await api.post(`/mesas/${id}/transferir`, { mesa_destino_id })
    return response.data
  },

  async getStats() {
    const response = await api.get('/mesas-stats')
    return response.data
  },
}
