# Frontend - CHAYANE ERP (Vue.js 3)

## Instalación

```bash
# Instalar dependencias
npm install

# Configurar variables de entorno
cp .env.example .env.local

# Iniciar servidor de desarrollo
npm run dev
```

## URL de desarrollo
http://localhost:5173

## Estructura de carpetas (se creará con Vue.js)

```
frontend/
├── src/
│   ├── assets/              ← CSS, imágenes, etc.
│   ├── components/          ← Componentes reutilizables
│   │   ├── common/          ← Botones, modales, etc.
│   │   └── layout/          ← Header, Sidebar, Footer
│   ├── views/               ← Páginas principales
│   │   ├── Dashboard/
│   │   ├── Inventario/
│   │   ├── Ventas/
│   │   ├── Reportes/
│   │   └── Reservas/
│   ├── router/              ← Configuración de rutas
│   ├── store/               ← Estado global (Pinia)
│   ├── services/            ← Llamadas API
│   ├── App.vue              ← Componente raíz
│   └── main.js              ← Punto de entrada
├── public/                  ← Archivos estáticos
└── index.html
```

## Instalación de Vue.js (Primera vez)

Si esta carpeta está vacía, instalar Vue.js:

```bash
npm create vue@latest .
# Seleccionar:
# ✔ Add Vue Router? Yes
# ✔ Add Pinia? Yes
# ✔ Add ESLint? Yes
# El resto: No
```

## Scripts disponibles

- `npm run dev` - Servidor de desarrollo
- `npm run build` - Build para producción
- `npm run preview` - Preview del build
- `npm run lint` - Linter de código

## Variables de entorno

Crear archivo `.env.local`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=CHAYANE
```
