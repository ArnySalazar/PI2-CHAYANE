# ✅ CHECKLIST DE INICIO - EQUIPO CHAYANE

## 📋 PARA EL LÍDER DEL PROYECTO

### FASE 1: CONFIGURACIÓN DE GITHUB (30 minutos)

- [ ] 1. Descargar `chayane-erp-final.zip`
- [ ] 2. Descomprimir el archivo
- [ ] 3. Crear repositorio en GitHub
      - Nombre: `chayane-erp`
      - Privado o Público
      - NO inicializar con README
- [ ] 4. Copiar URL del repositorio
- [ ] 5. Abrir terminal en carpeta del proyecto
- [ ] 6. Ejecutar comandos de Git:
      ```bash
      git init
      git add .
      git commit -m "feat: estructura inicial"
      git branch -M main
      git remote add origin [TU-URL]
      git push -u origin main
      ```
- [ ] 7. Crear rama develop:
      ```bash
      git checkout -b develop
      git push -u origin develop
      ```
- [ ] 8. Configurar develop como rama por defecto
- [ ] 9. Proteger rama main (opcional)
- [ ] 10. Agregar descripción y topics
- [ ] 11. Invitar a los 3 colaboradores
- [ ] 12. Enviar mensaje al equipo con:
       - Link del repositorio
       - Instrucciones de inicio
       - Fecha de primera reunión

---

## 📋 PARA CADA MIEMBRO DEL EQUIPO

### FASE 2: PREPARACIÓN INDIVIDUAL (1-2 horas)

#### DÍA 1: ACCESO Y DOCUMENTACIÓN

- [ ] 1. Aceptar invitación de GitHub (revisar email)
- [ ] 2. Clonar repositorio:
      ```bash
      git clone [URL-DEL-REPO]
      cd chayane-erp
      ```
- [ ] 3. Leer documentos (EN ORDEN):
      - [ ] README.md (10 min)
      - [ ] INICIO_RAPIDO.md (15 min)
      - [ ] SETUP.md (según tu sistema operativo)
      - [ ] docs/GIT_FLOW.md (20 min)

#### DÍA 2: INSTALACIÓN DE HERRAMIENTAS

**Todos necesitan:**
- [ ] 4. Instalar PHP 8.1+ y Composer
      - [ ] Verificar: `php --version`
      - [ ] Verificar: `composer --version`
- [ ] 5. Instalar Node.js 18+ y npm
      - [ ] Verificar: `node --version`
      - [ ] Verificar: `npm --version`
- [ ] 6. Instalar PostgreSQL 14+
      - [ ] Verificar: `psql --version`
- [ ] 7. Instalar Git
      - [ ] Verificar: `git --version`
- [ ] 8. Configurar Git:
      ```bash
      git config --global user.name "Tu Nombre"
      git config --global user.email "tu@email.com"
      ```

#### DÍA 3: BASE DE DATOS

- [ ] 9. Crear base de datos:
      ```bash
      psql -U postgres
      CREATE DATABASE chayane_db;
      \q
      ```
- [ ] 10. Ejecutar script inicial:
      ```bash
      psql -U postgres -d chayane_db -f database/init.sql
      ```
- [ ] 11. Verificar que funcionó:
      ```bash
      psql -U postgres -d chayane_db
      \dt
      # Debe mostrar 14 tablas
      \q
      ```

#### DÍA 4: GIT Y BRANCHES

- [ ] 12. Cambiarse a rama develop:
      ```bash
      git checkout develop
      ```
- [ ] 13. Crear tu branch personal:
      **Dev 1:** `git checkout -b feature/modulo-inventario`
      **Dev 2:** `git checkout -b feature/modulo-ventas`
      **Dev 3:** `git checkout -b feature/modulo-dashboard`
      **Dev 4:** `git checkout -b feature/modulo-reservas`
- [ ] 14. Hacer commit de prueba:
      ```bash
      touch test.txt
      git add test.txt
      git commit -m "test: primer commit de prueba"
      git push origin feature/tu-modulo
      ```
- [ ] 15. Verificar en GitHub que aparece tu branch

---

## 📋 INSTALACIÓN DE LARAVEL Y VUE.JS

### BACKEND (Laravel) - Semana 2

- [ ] 16. Instalar Laravel en carpeta backend:
      ```bash
      cd backend
      composer create-project laravel/laravel .
      ```
- [ ] 17. Configurar .env:
      ```bash
      cp .env.example .env
      php artisan key:generate
      ```
- [ ] 18. Editar .env con datos de PostgreSQL
- [ ] 19. Probar conexión:
      ```bash
      php artisan migrate
      php artisan serve
      ```
- [ ] 20. Verificar: http://localhost:8000

### FRONTEND (Vue.js) - Semana 2

- [ ] 21. Instalar Vue.js en carpeta frontend:
      ```bash
      cd frontend
      npm create vue@latest .
      ```
      Seleccionar:
      - ✅ Vue Router: Yes
      - ✅ Pinia: Yes
      - ✅ ESLint: Yes
      - ❌ Todo lo demás: No
- [ ] 22. Instalar dependencias:
      ```bash
      npm install
      ```
- [ ] 23. Configurar .env.local:
      ```bash
      cp .env.example .env.local
      ```
- [ ] 24. Probar:
      ```bash
      npm run dev
      ```
- [ ] 25. Verificar: http://localhost:5173

---

## 📋 ASIGNACIÓN DE MÓDULOS

### DEV 1: INVENTARIO + PRODUCTOS
- [ ] Leíste tu asignación en README.md
- [ ] Entiendes qué tablas usarás:
      - productos
      - categorias
      - inventario_movimientos
- [ ] Branch: `feature/modulo-inventario`
- [ ] Archivos a crear:
      - Backend: `ProductoController.php`
      - Frontend: `views/Inventario/`

### DEV 2: VENTAS + FINANZAS
- [ ] Leíste tu asignación en README.md
- [ ] Entiendes qué tablas usarás:
      - ventas
      - detalle_ventas
      - cajas
      - gastos
- [ ] Branch: `feature/modulo-ventas`
- [ ] Archivos a crear:
      - Backend: `VentaController.php`
      - Frontend: `views/Ventas/`

### DEV 3: DASHBOARD + REPORTES
- [ ] Leíste tu asignación en README.md
- [ ] Entiendes qué datos mostrarás:
      - KPIs (ventas, gastos, utilidad)
      - Gráficos
      - Reportes
- [ ] Branch: `feature/modulo-dashboard`
- [ ] Archivos a crear:
      - Backend: `DashboardController.php`
      - Frontend: `views/Dashboard/`

### DEV 4: WEB PÚBLICA + RESERVAS
- [ ] Leíste tu asignación en README.md
- [ ] Entiendes qué tablas usarás:
      - reservas
      - mesas
      - productos (para menú)
- [ ] Branch: `feature/modulo-reservas`
- [ ] Archivos a crear:
      - Backend: `ReservaController.php`
      - Frontend: `views/Web/`, `views/Reservas/`

---

## 📋 PRIMERA REUNIÓN DEL EQUIPO

### AGENDA DE LA REUNIÓN (1 hora)

- [ ] Todos se presentan (5 min)
- [ ] Revisar el proyecto juntos (10 min)
      - Abrir README.md
      - Ver estructura en GitHub
- [ ] Confirmar asignación de módulos (5 min)
- [ ] Resolver dudas de instalación (15 min)
      - ¿Todos tienen las herramientas?
      - ¿Todos crearon la base de datos?
- [ ] Acordar horarios de trabajo (10 min)
      - ¿Cuándo se reunirán?
      - ¿Cómo se comunicarán?
- [ ] Crear grupo de WhatsApp/Discord (5 min)
- [ ] Planificar próxima reunión (5 min)
- [ ] Q&A libre (5 min)

---

## 📋 RUTINA SEMANAL

### LUNES
- [ ] Daily standup (15 min)
      - ¿Qué hice la semana pasada?
      - ¿Qué haré esta semana?
      - ¿Tengo algún bloqueador?

### MARTES - JUEVES
- [ ] Trabajar en tu módulo
- [ ] Hacer commits frecuentes
- [ ] Actualizar tu branch con develop:
      ```bash
      git checkout develop
      git pull origin develop
      git checkout feature/tu-modulo
      git merge develop
      ```

### VIERNES
- [ ] Code review (30 min)
      - Revisar PRs de compañeros
- [ ] Demo de avances (30 min)
      - Cada uno muestra lo que hizo
- [ ] Planificar siguiente semana (30 min)

---

## 📋 ANTES DE CADA COMMIT

- [ ] El código compila sin errores
- [ ] Probé mi funcionalidad
- [ ] No hay archivos innecesarios (.env, node_modules)
- [ ] Mensaje de commit descriptivo
- [ ] Formato: `tipo: descripción`
      Ejemplos:
      - `feat: agregar CRUD de productos`
      - `fix: corregir validación de stock`
      - `docs: actualizar README`

---

## 📋 ANTES DE CREAR UN PULL REQUEST

- [ ] Mi branch está actualizado con develop
- [ ] Todo funciona correctamente
- [ ] No hay conflictos
- [ ] Escribí descripción del PR
- [ ] Asigné revisor
- [ ] Agregué screenshots (si aplica)

---

## 🎯 METAS POR SEMANA

### SEMANA 1-2: SETUP
- [ ] Todos tienen herramientas instaladas
- [ ] Base de datos creada
- [ ] Laravel y Vue.js instalados
- [ ] Primer commit hecho

### SEMANA 3-4: DESARROLLO INICIAL
- [ ] Estructura básica de cada módulo
- [ ] Primeros CRUDs funcionando
- [ ] APIs básicas creadas

### SEMANA 5-6: DESARROLLO MEDIO
- [ ] Funcionalidades principales completas
- [ ] Validaciones implementadas
- [ ] Interfaces básicas listas

### SEMANA 7-8: DESARROLLO AVANZADO
- [ ] Todas las funcionalidades completadas
- [ ] Diseño aplicado
- [ ] Testing básico

### SEMANA 9-10: INTEGRACIÓN
- [ ] Todos los módulos integrados
- [ ] Sistema funciona de punta a punta
- [ ] Bugs corregidos

### SEMANA 11: TESTING Y PULIDO
- [ ] Testing completo
- [ ] Corrección de bugs finales
- [ ] Documentación actualizada

### SEMANA 12: DEPLOY Y PRESENTACIÓN
- [ ] Sistema deployado
- [ ] Presentación preparada
- [ ] Documentación final

---

## ✅ VERIFICACIÓN FINAL

### El proyecto está listo cuando:

- [ ] Sistema corre en producción
- [ ] Todos los módulos funcionan
- [ ] No hay bugs críticos
- [ ] Documentación completa
- [ ] Presentación preparada
- [ ] Video demo grabado
- [ ] Código en GitHub limpio
- [ ] README actualizado

---

## 📞 CONTACTOS DEL EQUIPO

| Rol | Nombre | Email | Teléfono | GitHub |
|-----|--------|-------|----------|--------|
| Líder | ______ | ______ | ______ | ______ |
| Dev 1 | ______ | ______ | ______ | ______ |
| Dev 2 | ______ | ______ | ______ | ______ |
| Dev 3 | ______ | ______ | ______ | ______ |

---

## 🔗 LINKS IMPORTANTES

- 🌐 Repositorio: _________________________
- 📱 Grupo WhatsApp: _________________________
- 📊 Trello/Board: _________________________
- 📝 Google Drive: _________________________

---

**IMPRIMIR ESTA HOJA Y LLEVARLA A LA PRIMERA REUNIÓN**

---

¡Éxito equipo! 🚀
