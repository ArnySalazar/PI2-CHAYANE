import api from './api'

export default {
  async getAll() {
    const response = await api.get('/clientes')
    return response.data
  },

  async create(cliente) {
    const response = await api.post('/clientes', cliente)
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/clientes/${id}`)
    return response.data
  },

  async update(id, cliente) {
    const response = await api.put(`/clientes/${id}`, cliente)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/clientes/${id}`)
    return response.data
  },

  async getStats() {
    const response = await api.get('/clientes-stats')
    return response.data
  },

  async getClientesFrecuentes() {
    const response = await api.get('/clientes-frecuentes')
    return response.data
  },

  async buscarPorDocumento(documento) {
    const response = await api.get(`/clientes/buscar/${documento}`)
    return response.data
  },
}
