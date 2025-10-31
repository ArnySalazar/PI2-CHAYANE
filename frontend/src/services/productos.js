import api from './api'

export default {
  // Listar todos los productos
  async getAll() {
    const response = await api.get('/productos')
    return response.data
  },

  // Obtener un producto por ID
  async getById(id) {
    const response = await api.get(`/productos/${id}`)
    return response.data
  },

  // Crear nuevo producto
  async create(producto) {
    const response = await api.post('/productos', producto)
    return response.data
  },

  // Actualizar producto
  async update(id, producto) {
    const response = await api.put(`/productos/${id}`, producto)
    return response.data
  },

  // Eliminar producto
  async delete(id) {
    const response = await api.delete(`/productos/${id}`)
    return response.data
  },

  // Obtener categorías
  async getCategorias() {
    const response = await api.get('/categorias')
    return response.data
  },
}
