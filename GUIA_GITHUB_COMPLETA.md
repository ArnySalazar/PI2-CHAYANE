# 🚀 GUÍA MAESTRA - CONFIGURAR GITHUB PARA TU EQUIPO

## ✅ TODO LO QUE NECESITAS HACER (PASO A PASO)

Esta es la guía definitiva para dejar GitHub 100% listo para tu equipo de la universidad.

---

## 📦 PASO 1: DESCARGAR EL PROYECTO

### Descarga este archivo:
📥 **chayane-erp-final.zip** 

Este ZIP contiene:
- ✅ Toda la estructura del proyecto
- ✅ Base de datos completa (init.sql)
- ✅ Documentación completa (100+ páginas)
- ✅ Configuración de Git
- ✅ Guías para tu equipo
- ✅ TODO listo para subir a GitHub

---

## 🌐 PASO 2: CREAR EL REPOSITORIO EN GITHUB

### 2.1 Ir a GitHub

1. Abre tu navegador
2. Ve a: https://github.com
3. Inicia sesión (o crea cuenta si no tienes)

### 2.2 Crear nuevo repositorio

1. Click en el botón **"+"** (arriba a la derecha)
2. Click en **"New repository"**
3. Llenar datos:

```
Repository name:        chayane-erp
                       (o el nombre que prefieras)

Description:           Sistema de gestión (ERP) para el Restaurante 
                       "La Sazón de Pilar" - Laravel + Vue.js + PostgreSQL

Public / Private:      ✅ Private (recomendado para proyecto universitario)
                       o Public (si quieres que sea visible)

Initialize:            ❌ NO marcar nada
                       (NO README, NO .gitignore, NO license)
```

4. Click en **"Create repository"**

### 2.3 Copiar la URL del repositorio

Verás algo como:
```
https://github.com/TU-USUARIO/chayane-erp.git
```

**¡COPIA ESTA URL!** La necesitarás en el siguiente paso.

---

## 💻 PASO 3: SUBIR EL PROYECTO A GITHUB

### 3.1 Descomprimir el ZIP

1. Descomprime `chayane-erp-final.zip` 
2. Te quedará una carpeta: `chayane-erp-completo`
3. Abre esa carpeta

### 3.2 Abrir terminal en la carpeta

**Windows:**
- Click derecho en la carpeta
- "Abrir en Terminal" o "Git Bash Here"
- O abre CMD y escribe: `cd ruta\a\chayane-erp-completo`

### 3.3 Ejecutar comandos de Git

**Copia y pega estos comandos UNO POR UNO:**

```bash
# 1. Inicializar Git en la carpeta
git init

# 2. Agregar todos los archivos
git add .

# 3. Hacer el primer commit
git commit -m "feat: estructura inicial del proyecto CHAYANE ERP"

# 4. Renombrar rama a main
git branch -M main

# 5. Conectar con tu repositorio de GitHub
# ⚠️ REEMPLAZA con TU URL que copiaste antes
git remote add origin https://github.com/TU-USUARIO/chayane-erp.git

# 6. Subir todo a GitHub
git push -u origin main
```

**Si te pide usuario y contraseña:**
- Usuario: tu usuario de GitHub
- Password: usa un **Personal Access Token** (no tu contraseña)
  - Ve a: GitHub → Settings → Developer settings → Personal access tokens
  - Generate new token (classic)
  - Marca "repo"
  - Copia el token y úsalo como password

### 3.4 Verificar que se subió

1. Refresca tu repositorio en GitHub
2. Deberías ver todos los archivos:
   ```
   ✅ README.md
   ✅ .gitignore
   ✅ backend/
   ✅ frontend/
   ✅ database/
   ✅ docs/
   ```

---

## 🌿 PASO 4: CREAR RAMA DEVELOP

```bash
# Crear rama develop
git checkout -b develop

# Subir develop a GitHub
git push -u origin develop
```

---

## ⚙️ PASO 5: CONFIGURAR GITHUB (En la web)

### 5.1 Configurar rama por defecto

1. Ve a tu repo en GitHub
2. Click en **"Settings"** (Configuración)
3. En el menú izquierdo: **"Branches"**
4. En "Default branch" → Click en el ícono de cambio
5. Selecciona **"develop"**
6. Click **"Update"**
7. Confirma

**¿Por qué develop?** Tu equipo trabajará aquí, main será solo para producción.

### 5.2 Proteger rama main (Opcional pero recomendado)

1. En la misma página "Branches"
2. Click en **"Add rule"**
3. Branch name pattern: `main`
4. Marca:
   - ✅ **"Require pull request before merging"**
   - ✅ **"Require approvals"** (mínimo 1)
5. Click **"Create"**

Esto evita que se suba código directo a main sin revisión.

### 5.3 Agregar descripción y topics

1. Ve a la página principal de tu repo
2. Click en el ⚙️ junto a "About"
3. Description: 
   ```
   Sistema de gestión (ERP) para restaurante - Laravel + Vue.js + PostgreSQL
   ```
4. Topics (etiquetas):
   ```
   erp, laravel, vuejs, postgresql, restaurant, pos, peru, upch
   ```
5. Click **"Save changes"**

---

## 👥 PASO 6: INVITAR A TU EQUIPO

### 6.1 Agregar colaboradores

1. Ve a **"Settings"** → **"Collaborators"**
2. Click en **"Add people"**
3. Busca por:
   - Usuario de GitHub
   - Email
4. Envía invitación
5. Repite para cada miembro del equipo

**Roles recomendados:**
- Todos: **"Write"** (pueden hacer push)

### 6.2 Crear equipo (Opcional - para organizaciones)

Si tienes una organización:
1. Ve a la organización
2. Teams → New team
3. Nombre: "Equipo CHAYANE"
4. Agregar miembros

---

## 📧 PASO 7: COMPARTIR CON TU EQUIPO

### 7.1 Enviar este mensaje a tu equipo:

```
¡Hola equipo! 👋

Ya está listo el repositorio del proyecto CHAYANE:
🔗 https://github.com/TU-USUARIO/chayane-erp

📋 PASOS PARA EMPEZAR:

1. Aceptar la invitación que les llegó por email
2. Clonar el repositorio:
   git clone https://github.com/TU-USUARIO/chayane-erp.git
   cd chayane-erp

3. Leer la documentación (EN ESTE ORDEN):
   - README.md (visión general)
   - SETUP.md (instalación de herramientas)
   - docs/GIT_FLOW.md (cómo trabajar con Git)
   - INICIO_RAPIDO.md (guía de 30 minutos)

4. Crear la base de datos:
   psql -U postgres -d chayane_db -f database/init.sql

5. Crear tu branch personal:
   git checkout develop
   git checkout -b feature/tu-modulo

📦 ASIGNACIÓN DE MÓDULOS:

Dev 1: Inventario + Productos
→ Branch: feature/modulo-inventario

Dev 2: Ventas + Finanzas
→ Branch: feature/modulo-ventas

Dev 3: Dashboard + Reportes
→ Branch: feature/modulo-dashboard

Dev 4: Web Pública + Reservas
→ Branch: feature/modulo-reservas

📅 Primera reunión: [FECHA Y HORA]
📍 Lugar: [LUGAR O ZOOM]

¡Nos vemos! 🚀
```

### 7.2 Compartir acceso a documentos

Envía también:
- 🔗 Link al repo
- 📄 Copia del README.md
- 📄 Copia del SETUP.md

---

## 📊 PASO 8: VERIFICAR QUE TODO ESTÁ LISTO

### ✅ Checklist final:

- [ ] ✅ Repositorio creado en GitHub
- [ ] ✅ Código subido (rama main)
- [ ] ✅ Rama develop creada y subida
- [ ] ✅ Develop configurado como rama por defecto
- [ ] ✅ Rama main protegida (opcional)
- [ ] ✅ Descripción y topics agregados
- [ ] ✅ Equipo invitado como colaboradores
- [ ] ✅ Mensaje enviado al equipo
- [ ] ✅ Estructura visible en GitHub:
  ```
  ✓ README.md
  ✓ .gitignore
  ✓ SETUP.md
  ✓ backend/
  ✓ frontend/
  ✓ database/init.sql
  ✓ docs/
  ```

---

## 🎯 ESTRUCTURA QUE VERÁ TU EQUIPO EN GITHUB

```
chayane-erp/
│
├── 📄 README.md                    ← Documentación principal
├── 📄 SETUP.md                     ← Guía de instalación
├── 📄 INICIO_RAPIDO.md            ← Guía rápida
├── 📄 COMO_SUBIR_A_GITHUB.md      ← Para referencia
├── 📄 .gitignore                   ← Configuración Git
│
├── 📁 backend/                     ← API Laravel
│   ├── README.md                   ← Instrucciones backend
│   └── .env.example                ← Variables de entorno
│
├── 📁 frontend/                    ← App Vue.js
│   ├── README.md                   ← Instrucciones frontend
│   ├── .env.example                ← Variables de entorno
│   └── package.json                ← Dependencias
│
├── 📁 database/                    ← Base de datos
│   └── init.sql                    ← Script PostgreSQL
│
└── 📁 docs/                        ← Documentación técnica
    ├── DATABASE.md                 ← Esquema BD
    ├── GIT_FLOW.md                 ← Guía de Git
    ├── ARQUITECTURA_WEB.md         ← Arquitectura
    ├── DEPLOYMENT.md               ← Deploy
    └── COMPARACION_PANCA.md        ← Inspiración
```

---

## 🔄 FLUJO DE TRABAJO DEL EQUIPO

### Cuando un desarrollador empiece:

```bash
# 1. Clonar repositorio
git clone https://github.com/TU-USUARIO/chayane-erp.git
cd chayane-erp

# 2. Ver ramas disponibles
git branch -a

# 3. Cambiarse a develop
git checkout develop

# 4. Crear su branch personal
git checkout -b feature/modulo-inventario

# 5. Trabajar en su código...
# (editar archivos)

# 6. Guardar cambios
git add .
git commit -m "feat: implementar CRUD de productos"

# 7. Subir a GitHub
git push origin feature/modulo-inventario

# 8. Crear Pull Request en GitHub
# (desde la web)
```

---

## 📱 COMUNICACIÓN DEL EQUIPO

### Crear canales de comunicación:

1. **WhatsApp / Discord / Telegram**
   - Para dudas rápidas
   - Coordinación diaria

2. **GitHub Issues**
   - Para reportar bugs
   - Para solicitar features
   - Para asignar tareas

3. **GitHub Projects** (Opcional)
   - Tablero Kanban
   - To Do / In Progress / Done

4. **Reuniones semanales**
   - Lunes: Planning
   - Viernes: Review

---

## 📚 DOCUMENTOS QUE TU EQUIPO DEBE LEER

### Prioridad ALTA (leer primero):
1. ⭐⭐⭐ **README.md** - Visión general del proyecto
2. ⭐⭐⭐ **INICIO_RAPIDO.md** - Guía de 30 minutos
3. ⭐⭐⭐ **SETUP.md** - Instalación de herramientas
4. ⭐⭐⭐ **docs/GIT_FLOW.md** - Cómo trabajar con Git

### Prioridad MEDIA (leer cuando sea necesario):
5. ⭐⭐ **docs/DATABASE.md** - Esquema de base de datos
6. ⭐⭐ **backend/README.md** - Configurar Laravel
7. ⭐⭐ **frontend/README.md** - Configurar Vue.js

### Prioridad BAJA (referencia):
8. ⭐ **docs/ARQUITECTURA_WEB.md** - Arquitectura técnica
9. ⭐ **docs/DEPLOYMENT.md** - Cómo publicar
10. ⭐ **docs/COMPARACION_PANCA.md** - Inspiración

---

## 🆘 PROBLEMAS COMUNES Y SOLUCIONES

### Problema: "Permission denied"
**Solución:** Verifica que invitaste al colaborador y que aceptó.

### Problema: "remote: Repository not found"
**Solución:** Verifica que la URL del remote es correcta:
```bash
git remote -v
git remote set-url origin https://github.com/TU-USUARIO/chayane-erp.git
```

### Problema: "Failed to push"
**Solución:** 
```bash
git pull origin develop --rebase
git push origin tu-branch
```

### Problema: No aparecen los archivos en GitHub
**Solución:** Verifica que hiciste push:
```bash
git status
git log --oneline
git push origin main
```

---

## 🎓 RECURSOS PARA TU EQUIPO

### Git y GitHub:
- **Tutorial interactivo:** https://learngitbranching.js.org/
- **Cheat Sheet:** https://education.github.com/git-cheat-sheet-education.pdf
- **GitHub Guides:** https://guides.github.com/

### Laravel:
- **Documentación oficial:** https://laravel.com/docs
- **Laracasts (videos):** https://laracasts.com/

### Vue.js:
- **Documentación oficial:** https://vuejs.org/guide/
- **Tutorial interactivo:** https://vuejs.org/tutorial/

### PostgreSQL:
- **Tutorial completo:** https://www.postgresqltutorial.com/

---

## ✅ RESUMEN EJECUTIVO

### Lo que acabas de hacer:

1. ✅ Creaste repositorio en GitHub
2. ✅ Subiste todo el código base
3. ✅ Configuraste ramas (main + develop)
4. ✅ Invitaste a tu equipo
5. ✅ Les diste acceso a toda la documentación

### Lo que tu equipo hará:

1. Aceptar invitación
2. Clonar repositorio
3. Leer documentación
4. Instalar herramientas
5. Crear base de datos
6. Empezar a programar

---

## 🎉 ¡LISTO!

Tu GitHub está **100% configurado** y listo para trabajo colaborativo.

**Siguiente paso:** Tu equipo debe seguir **INICIO_RAPIDO.md**

---

## 📞 CONTACTO

Si necesitas ayuda:
1. Lee la documentación primero
2. Busca en Google el error específico
3. Pregunta en el grupo del equipo
4. Crea un Issue en GitHub

---

**¡Éxito con el proyecto CHAYANE! 🚀**

*Creado: Octubre 2025*
*Universidad: UPCH*
*Equipo: 4 desarrolladores*
*Duración: 12 semanas*
