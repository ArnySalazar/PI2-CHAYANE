<template>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <h2>🍽️ CHAYANE</h2>
        <p>ERP Restaurant</p>
      </div>

      <nav class="menu">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="menu-item"
          :class="{ disabled: item.disabled }"
        >
          <span class="icon">{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout-sidebar" title="Cerrar sesión">
          🚪 Salir
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Header con información del usuario -->
      <header class="top-header">
        <div class="header-left">
          <h1 class="page-title">Sistema de Gestión</h1>
        </div>

        <div class="header-right">
          <!-- Información del usuario destacada -->
          <div class="user-info-header">
            <div class="user-badge">
              <div class="user-avatar-large">{{ userInitial }}</div>
              <div class="user-text">
                <div class="user-name-large">{{ userName }}</div>
                <div class="user-role-badge" :class="roleClass">
                  <span class="role-icon">{{ roleIcon }}</span>
                  {{ userRole }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Contenido principal -->
      <div class="content-wrapper">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'LayoutMain',
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  computed: {
    userName() {
      return this.authStore.userName || 'Admin Sistema'
    },
    userRole() {
      return this.authStore.userRole || 'Administrador'
    },
    userInitial() {
      const name = this.userName || 'A'
      return name.charAt(0).toUpperCase()
    },
    roleClass() {
      // Clase CSS según el rol para diferentes colores
      const role = this.userRole.toLowerCase()
      if (role.includes('admin')) return 'role-admin'
      if (role.includes('gerente')) return 'role-gerente'
      if (role.includes('cajero')) return 'role-cajero'
      if (role.includes('cocina')) return 'role-cocina'
      if (role.includes('mozo') || role.includes('mesero')) return 'role-mozo'
      return 'role-default'
    },
    roleIcon() {
      // Icono según el rol
      const role = this.userRole.toLowerCase()
      if (role.includes('admin')) return '👑'
      if (role.includes('gerente')) return '💼'
      if (role.includes('cajero')) return '💰'
      if (role.includes('cocina')) return '👨‍🍳'
      if (role.includes('mozo') || role.includes('mesero')) return '🍽️'
      return '👤'
    },
    menuItems() {
      const auth = this.authStore

      // Si no hay permisos cargados, mostrar todos por defecto
      if (!auth.permissions) {
        return [
          { path: '/dashboard', icon: '📊', label: 'Dashboard', show: true },
          { path: '/productos', icon: '📦', label: 'Productos', show: true },
          { path: '/insumos', icon: '🥕', label: 'Insumos', show: true },
          { path: '/ventas', icon: '💵', label: 'Ventas', show: true },
          { path: '/mesas', icon: '🍽️', label: 'Mesas', show: true },
          { path: '/cocina', icon: '👨‍🍳', label: 'Cocina', show: true },
          { path: '/clientes', icon: '👥', label: 'Clientes', show: true },
          { path: '/reportes', icon: '📈', label: 'Reportes', show: true },
        ]
      }

      return [
        {
          path: '/dashboard',
          icon: '📊',
          label: 'Dashboard',
          show: auth.canView('dashboard'),
        },
        {
          path: '/productos',
          icon: '📦',
          label: 'Productos',
          show: auth.canView('productos'),
        },
        {
          path: '/insumos',
          icon: '🥕',
          label: 'Insumos',
          show: auth.canView('insumos'),
        },
        {
          path: '/ventas',
          icon: '💵',
          label: 'Ventas',
          show: auth.canView('ventas'),
        },
        {
          path: '/mesas',
          icon: '🍽️',
          label: 'Mesas',
          show: auth.canView('mesas'),
        },
        {
          path: '/cocina',
          icon: '👨‍🍳',
          label: 'Cocina',
          show: auth.canView('cocina'),
        },
        {
          path: '/clientes',
          icon: '👥',
          label: 'Clientes',
          show: auth.canView('clientes'),
        },
        {
          path: '/reportes',
          icon: '📈',
          label: 'Reportes',
          show: auth.canView('reportes'),
        },
      ].filter((item) => item.show)
    },
  },
  methods: {
    handleLogout() {
      if (confirm('¿Estás seguro de cerrar sesión?')) {
        console.log('Cerrando sesión...')

        // Limpiar store
        this.authStore.logout()

        // Limpiar localStorage completamente
        localStorage.clear()

        // Redirigir al login
        this.$router.push('/login')

        console.log('Sesión cerrada, redirigiendo a login...')
      }
    },
  },
  mounted() {
    // Restaurar sesión si existe
    this.authStore.restoreSession()
  },
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.logo {
  padding: 30px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.logo h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
}

.logo p {
  margin: 5px 0 0;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 300;
}

.menu {
  flex: 1;
  padding: 20px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px 25px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s;
  cursor: pointer;
  border-left: 3px solid transparent;
}

.menu-item:hover:not(.disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: #3498db;
}

.menu-item.router-link-active {
  background: rgba(52, 152, 219, 0.2);
  color: white;
  border-left-color: #3498db;
  font-weight: 600;
}

.menu-item.disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

.menu-item .icon {
  font-size: 20px;
  width: 24px;
  text-align: center;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout-sidebar {
  width: 100%;
  background: rgba(231, 76, 60, 0.2);
  border: 1px solid rgba(231, 76, 60, 0.5);
  color: white;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-logout-sidebar:hover {
  background: #e74c3c;
  border-color: #e74c3c;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-logout-sidebar:active {
  transform: translateY(0);
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 260px;
  background: #f5f7fa;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header Superior */
.top-header {
  background: white;
  padding: 20px 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 50;
  border-bottom: 3px solid #3498db;
}

.header-left .page-title {
  margin: 0;
  font-size: 24px;
  color: #2c3e50;
  font-weight: 600;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Badge de usuario mejorado */
.user-info-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 12px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-badge {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-avatar-large {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: white;
  color: #667eea;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 24px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-text {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.user-name-large {
  font-size: 18px;
  font-weight: 700;
  color: white;
  line-height: 1;
}

.user-role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  line-height: 1;
  width: fit-content;
}

.role-icon {
  font-size: 14px;
}

/* Colores por rol */
.role-admin {
  background: #ffd700;
  color: #2c3e50;
}

.role-gerente {
  background: #3498db;
  color: white;
}

.role-cajero {
  background: #27ae60;
  color: white;
}

.role-cocina {
  background: #e67e22;
  color: white;
}

.role-mozo {
  background: #9b59b6;
  color: white;
}

.role-default {
  background: rgba(255, 255, 255, 0.3);
  color: white;
}

/* Content Wrapper */
.content-wrapper {
  flex: 1;
  padding: 30px;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .sidebar .logo p,
  .menu-item span:not(.icon) {
    display: none;
  }

  .logo h2 {
    font-size: 28px;
  }

  .menu-item {
    justify-content: center;
    padding: 15px;
  }

  .main-content {
    margin-left: 70px;
  }

  .top-header {
    padding: 15px 20px;
  }

  .header-left .page-title {
    font-size: 18px;
  }

  .user-info-header {
    padding: 8px 12px;
  }

  .user-badge {
    gap: 10px;
  }

  .user-avatar-large {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  .user-name-large {
    font-size: 14px;
  }

  .user-role-badge {
    font-size: 11px;
    padding: 3px 8px;
  }

  .btn-logout-sidebar {
    font-size: 12px;
    padding: 10px;
  }
}

@media (max-width: 480px) {
  .sidebar {
    width: 60px;
  }

  .main-content {
    margin-left: 60px;
  }

  .user-text {
    display: none;
  }

  .user-info-header {
    padding: 8px;
  }
}
</style>
