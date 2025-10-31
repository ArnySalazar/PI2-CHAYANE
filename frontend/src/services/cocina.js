import api from './api'

export default {
  async getPedidos() {
    const response = await api.get('/cocina/pedidos')
    return response.data
  },

  async cambiarEstado(id, estado_cocina) {
    const response = await api.post(`/cocina/pedidos/${id}/estado`, { estado_cocina })
    return response.data
  },

  async iniciarPreparacion(id) {
    const response = await api.post(`/cocina/pedidos/${id}/iniciar`)
    return response.data
  },

  async marcarListo(id) {
    const response = await api.post(`/cocina/pedidos/${id}/listo`)
    return response.data
  },

  async getCompletados() {
    const response = await api.get('/cocina/completados')
    return response.data
  },

  async getStats() {
    const response = await api.get('/cocina/stats')
    return response.data
  },
}
