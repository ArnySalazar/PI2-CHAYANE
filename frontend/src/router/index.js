import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/productos',
      name: 'productos',
      component: () => import('../views/ProductosView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/ventas',
      name: 'ventas',
      component: () => import('../views/VentasView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/reportes',
      name: 'reportes',
      component: () => import('../views/ReportesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/insumos',
      name: 'insumos',
      component: () => import('../views/InsumosView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/clientes',
      name: 'clientes',
      component: () => import('../views/ClientesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/cocina',
      name: 'cocina',
      component: () => import('../views/CocinaView.vue'),
      meta: { requiresAuth: true, requiredPermission: { module: 'cocina', action: 'read' } },
    },
    {
      path: '/mesas',
      name: 'mesas',
      component: () => import('../views/MesasView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

// Guard simple - solo verifica token en localStorage
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Restaurar sesión si existe
  if (!authStore.user) {
    authStore.restoreSession()
  }

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const isAuthenticated = authStore.isAuthenticated

  // Si requiere auth y no está autenticado
  if (requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  // Si ya está autenticado e intenta ir al login
  if (to.path === '/login' && isAuthenticated) {
    return next('/dashboard')
  }

  // Verificar permisos específicos
  if (to.meta.requiredPermission) {
    const { module, action } = to.meta.requiredPermission

    let hasPermission = false
    if (action === 'read') hasPermission = authStore.canView(module)
    else if (action === 'create') hasPermission = authStore.canCreate(module)
    else if (action === 'edit') hasPermission = authStore.canEdit(module)
    else if (action === 'delete') hasPermission = authStore.canDelete(module)

    if (!hasPermission) {
      console.warn(`⚠️ Sin permiso para ${action} en ${module}`)
      return next('/dashboard')
    }
  }

  next()
})

export default router
