# 🚀 GUÍA: CÓMO SUBIR EL PROYECTO A GITHUB

## ✅ YA CREASTE EL REPOSITORIO - PERFECTO

Ahora sigue estos pasos para subir todo correctamente:

---

## 📋 PASO 1: PREPARAR LA CARPETA LOCAL

```bash
# 1. Ir a donde descargaste/descomprimiste chayane-erp
cd /ruta/a/chayane-erp

# 2. Verificar que tienes estos archivos y carpetas:
ls -la

# Deberías ver:
# ├── .gitignore
# ├── README.md
# ├── SETUP.md
# ├── INICIO_RAPIDO.md
# ├── backend/
# ├── frontend/
# ├── database/
# └── docs/
```

---

## 📋 PASO 2: INICIALIZAR GIT LOCALMENTE

```bash
# Inicializar repositorio Git
git init

# Verificar que .gitignore existe
cat .gitignore

# Agregar todos los archivos
git add .

# Ver qué se va a subir
git status

# Hacer el primer commit
git commit -m "feat: estructura inicial del proyecto CHAYANE"
```

---

## 📋 PASO 3: CONECTAR CON GITHUB

Reemplaza `TU-USUARIO` y `NOMBRE-REPO` con tus datos reales.

```bash
# Renombrar rama a main (si es necesario)
git branch -M main

# Conectar con tu repositorio de GitHub
git remote add origin https://github.com/TU-USUARIO/NOMBRE-REPO.git

# Verificar la conexión
git remote -v
# Debe mostrar:
# origin  https://github.com/TU-USUARIO/NOMBRE-REPO.git (fetch)
# origin  https://github.com/TU-USUARIO/NOMBRE-REPO.git (push)
```

---

## 📋 PASO 4: SUBIR A GITHUB

```bash
# Subir todo a GitHub
git push -u origin main

# Si te pide usuario y contraseña:
# Usuario: tu-usuario-github
# Password: usa un Personal Access Token (no tu contraseña)
```

### ⚠️ Si te pide contraseña (Token):

1. Ve a GitHub → Settings → Developer settings → Personal access tokens
2. Generate new token (classic)
3. Selecciona: `repo` (todos los permisos de repositorio)
4. Generate token
5. Copia el token
6. Úsalo como contraseña cuando git lo pida

---

## 📋 PASO 5: CREAR RAMA DEVELOP

```bash
# Crear rama develop desde main
git checkout -b develop

# Subir develop a GitHub
git push -u origin develop
```

---

## 📋 PASO 6: CONFIGURAR GITHUB (En la web)

1. **Ve a tu repositorio en GitHub**
   - https://github.com/TU-USUARIO/NOMBRE-REPO

2. **Configurar rama por defecto:**
   - Settings → Branches
   - Default branch: cambiar a `develop`
   - Update

3. **Proteger rama main:**
   - Settings → Branches → Add rule
   - Branch name pattern: `main`
   - ✅ Require pull request before merging
   - Save changes

4. **Agregar descripción:**
   - En la página principal del repo
   - Edit → About
   - Description: "ERP para Restaurante La Sazón de Pilar - Laravel + Vue.js + PostgreSQL"
   - Topics: `erp`, `laravel`, `vuejs`, `postgresql`, `restaurant`
   - Save

---

## ✅ VERIFICACIÓN

Deberías tener en GitHub:

```
Tu Repositorio/
├── .gitignore
├── README.md
├── SETUP.md
├── INICIO_RAPIDO.md
├── backend/
│   ├── .env.example
│   └── README.md
├── frontend/
│   ├── .env.example
│   ├── package.json
│   └── README.md
├── database/
│   └── init.sql
└── docs/
    ├── DATABASE.md
    └── GIT_FLOW.md
```

---

## 📢 PASO 7: COMPARTIR CON EL EQUIPO

### Opción A: Invitar colaboradores

1. Ve a Settings → Collaborators
2. Add people
3. Busca por usuario de GitHub
4. Invite

### Opción B: Compartir el link

Envía a tu equipo:
```
https://github.com/TU-USUARIO/NOMBRE-REPO
```

---

## 📝 INSTRUCCIONES PARA TU EQUIPO

Comparte este mensaje con tu equipo:

```
¡Hola equipo! 👋

Ya está listo el repositorio del proyecto CHAYANE:
🔗 https://github.com/TU-USUARIO/NOMBRE-REPO

📋 PASOS PARA EMPEZAR:

1. Clonar el repositorio:
   git clone https://github.com/TU-USUARIO/NOMBRE-REPO.git
   cd NOMBRE-REPO

2. Leer la documentación:
   - INICIO_RAPIDO.md (empezar aquí)
   - README.md (documentación completa)
   - SETUP.md (instalación)

3. Crear la base de datos:
   psql -U postgres -d chayane_db -f database/init.sql

4. Crear tu branch:
   git checkout develop
   git checkout -b feature/tu-modulo

5. Instalar dependencias (cuando tengamos Laravel/Vue):
   cd backend && composer install
   cd frontend && npm install

¡Nos vemos en la reunión! 🚀
```

---

## 🔄 COMANDOS ÚTILES DESPUÉS DE SUBIR

```bash
# Ver el estado
git status

# Ver ramas
git branch -a

# Cambiar a develop
git checkout develop

# Actualizar desde GitHub
git pull origin develop

# Ver historial
git log --oneline

# Ver remotes
git remote -v
```

---

## ❓ PROBLEMAS COMUNES

### Problema: "fatal: remote origin already exists"

```bash
git remote remove origin
git remote add origin https://github.com/TU-USUARIO/NOMBRE-REPO.git
```

### Problema: "error: failed to push"

```bash
# Primero traer cambios de GitHub
git pull origin main --rebase

# Luego subir
git push -u origin main
```

### Problema: "Permission denied"

- Asegúrate de estar usando el Personal Access Token correcto
- O configura SSH: https://docs.github.com/es/authentication/connecting-to-github-with-ssh

---

## ✅ CHECKLIST FINAL

- [ ] Repositorio creado en GitHub
- [ ] Git inicializado localmente
- [ ] Todos los archivos agregados
- [ ] Primer commit realizado
- [ ] Remote conectado a GitHub
- [ ] Push a main exitoso
- [ ] Rama develop creada y subida
- [ ] Develop configurado como rama por defecto
- [ ] Descripción del repo agregada
- [ ] Equipo invitado como colaboradores
- [ ] Instrucciones compartidas con el equipo

---

## 🎉 ¡LISTO!

Tu repositorio está configurado correctamente y listo para trabajo colaborativo.

**Siguiente paso:** Tu equipo debe clonar el repo y seguir INICIO_RAPIDO.md

---

## 📞 COMANDOS DE RESUMEN (COPIA Y PEGA)

```bash
# EJECUTA ESTO SI YA TIENES LA CARPETA LOCAL:
cd chayane-erp
git init
git add .
git commit -m "feat: estructura inicial del proyecto CHAYANE"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/NOMBRE-REPO.git
git push -u origin main
git checkout -b develop
git push -u origin develop
```

Reemplaza `TU-USUARIO` y `NOMBRE-REPO` con tus datos reales.

---

¡Éxito! 🚀
