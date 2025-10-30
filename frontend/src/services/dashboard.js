import api from './api'

export default {
  async getStats() {
    const response = await api.get('/dashboard')
    return response.data
  },
}
