# 🍽️ Chayane ERP - Sistema de Gestión para Restaurante

Sistema completo de gestión para restaurantes con notificaciones en tiempo real, control de cocina, ventas, inventario y reportes.

## 🚀 Características

- ✅ **Sistema de Ventas** con múltiples métodos de pago
- 👨‍🍳 **Módulo de Cocina** con notificaciones en tiempo real (Pusher)
- 📦 **Control de Inventario** (productos e insumos)
- 🍽️ **Gestión de Mesas**
- 👥 **Sistema de Permisos** por roles
- 📊 **Dashboard y Reportes**
- 🔔 **Notificaciones push** para nuevos pedidos

## 🛠️ Tecnologías

### Backend
- Laravel 11
- PostgreSQL
- Pusher (Broadcasting)

### Frontend
- Vue 3
- Vite
- Pinia (State Management)
- Axios
- Laravel Echo + Pusher

## 📋 Requisitos Previos

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL 14+
- Git

## ⚙️ Instalación

### 1️⃣ Clonar el repositorio
```bash
git clone https://github.com/TU_USUARIO/chayane-erp.git
cd chayane-erp
```

### 2️⃣ Configurar Backend
```bash
cd backend

# Instalar dependencias
composer install

# Copiar archivo de entorno
copy .env.example .env

# Generar key de aplicación
php artisan key:generate

# Configurar base de datos en .env
# DB_DATABASE=chayane_db
# DB_USERNAME=postgres
# DB_PASSWORD=tu_password

# Configurar Pusher en .env
# PUSHER_APP_ID=tu_app_id
# PUSHER_APP_KEY=tu_app_key
# PUSHER_APP_SECRET=tu_app_secret
# PUSHER_APP_CLUSTER=us2

# Crear base de datos
# Ejecutar en PostgreSQL: CREATE DATABASE chayane_db;

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (datos de prueba)
php artisan db:seed

# Iniciar servidor
php artisan serve
```

### 3️⃣ Configurar Frontend
```bash
cd ../frontend

# Instalar dependencias
npm install

# Copiar archivo de entorno
copy .env.example .env

# Configurar en .env:
# VITE_API_URL=http://localhost:8000/api
# VITE_PUSHER_APP_KEY=tu_pusher_key
# VITE_PUSHER_CLUSTER=us2

# Iniciar servidor de desarrollo
npm run dev
```

## 🔑 Usuarios de Prueba

| Email | Password | Rol |
|-------|----------|-----|
| admin@chayane.com | 123 | Administrador |
| gerente@chayane.com | 123 | Gerente |
| cajero@chayane.com | 123 | Cajero |
| cocinero@chayane.com | 123 | Cocinero |

## 📡 Configurar Pusher

1. Crear cuenta en [pusher.com](https://pusher.com)
2. Crear un nuevo canal (Channels)
3. Copiar credenciales en `.env` del backend y frontend
4. El canal `cocina` se crea automáticamente

## 🗄️ Base de Datos

### Migraciones importantes:
- `create_users_table` - Usuarios del sistema
- `create_roles_table` - Roles y permisos
- `create_productos_table` - Catálogo de productos
- `create_ventas_table` - Registro de ventas
- `create_detalle_ventas_table` - Items de cada venta
- `add_estado_cocina_to_ventas` - Estados de cocina

### Seeders:
- `RoleSeeder` - Crea roles y permisos base
- `UserSeeder` - Crea usuarios de prueba
- `CategoriaSeeder` - Categorías de productos
- `ProductoSeeder` - Productos de ejemplo

## 📱 Módulos del Sistema

### 🏠 Dashboard
- Estadísticas generales
- Ventas del día/mes
- Productos más vendidos

### 🛒 Ventas (POS)
- Interfaz de punto de venta
- Cálculo automático de totales
- Múltiples métodos de pago
- Asignación de mesas

### 👨‍🍳 Cocina
- Vista de pedidos pendientes
- Estados: Pendiente → En Preparación → Listo
- **Notificaciones en tiempo real** cuando llegan nuevos pedidos
- Toast animado + notificación del sistema + sonido

### 📦 Productos
- CRUD completo
- Control de stock
- Categorías

### 📊 Reportes
- Ventas por fecha
- Productos más vendidos
- Estado de inventario

## 🔐 Sistema de Permisos

Los permisos se manejan por rol y módulo con acciones específicas:
- `can_read` - Ver
- `can_create` - Crear
- `can_edit` - Editar
- `can_delete` - Eliminar

## 🐛 Troubleshooting

### Error de conexión a Pusher
- Verificar credenciales en `.env`
- Verificar que `BROADCAST_CONNECTION=pusher`
- Limpiar caché: `php artisan config:clear`

### Notificaciones no llegan
- Verificar permisos del navegador
- Hacer click en la página antes (para activar audio)
- Verificar consola del navegador (F12)

### Error 403 en cocina
- Verificar permisos en tabla `role_permissions`
- El cocinero necesita `can_edit=true` en módulo `cocina`

## 📝 TODO / Próximas Características

- [ ] Módulo de Reservas
- [ ] Reportes en PDF/Excel
- [ ] Integración con impresora térmica
- [ ] App móvil para meseros
- [ ] Panel de KDS (Kitchen Display System)

## 👥 Equipo de Desarrollo

- **Desarrollador Principal**: Arny Salazar, Stephany Toribio
- **Proyecto**: Sistema ERP para Restaurante Chayane

## 📄 Licencia

Este proyecto es privado y confidencial.

---

**¿Dudas o problemas?** Contacta al equipo de desarrollo.
