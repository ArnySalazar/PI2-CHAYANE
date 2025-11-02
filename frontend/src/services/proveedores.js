import api from './api'

export default {
  async getAll() {
    const response = await api.get('/proveedores')
    return response.data
  },

  async create(data) {
    const response = await api.post('/proveedores', data)
    return response.data
  },
}
