# 🌿 GUÍA DE GIT FLOW - TRABAJO COLABORATIVO

Esta guía explica cómo trabajar en equipo usando Git y GitHub para el proyecto CHAYANE.

---

## 📊 ESTRATEGIA DE BRANCHES

```
main (producción)
  │
  └── develop (desarrollo)
       │
       ├── feature/modulo-inventario (Dev 1)
       ├── feature/modulo-ventas (Dev 2)
       ├── feature/modulo-dashboard (Dev 3)
       └── feature/modulo-reservas (Dev 4)
```

### Tipos de Branches

- **`main`**: Producción (solo código probado y funcional)
- **`develop`**: Integración (donde se juntan todas las features)
- **`feature/*`**: Nuevas funcionalidades individuales
- **`hotfix/*`**: Correcciones urgentes en producción
- **`release/*`**: Preparación de versiones

---

## 🚀 WORKFLOW COMPLETO

### 1. CONFIGURACIÓN INICIAL (Solo una vez)

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/chayane-erp.git
cd chayane-erp

# Configurar tu identidad
git config user.name "Tu Nombre"
git config user.email "tu.email@ejemplo.com"

# Ver todas las ramas
git branch -a

# Cambiar a develop
git checkout develop
```

---

### 2. CREAR TU BRANCH DE FEATURE

```bash
# Asegurarte de estar en develop actualizado
git checkout develop
git pull origin develop

# Crear tu branch (reemplaza 'nombre-modulo' con tu módulo)
git checkout -b feature/modulo-inventario

# Verificar en qué branch estás
git branch
# Debe aparecer con * tu branch
```

**Nomenclatura de branches:**
- `feature/modulo-inventario` - Desarrollador 1
- `feature/modulo-ventas` - Desarrollador 2
- `feature/modulo-dashboard` - Desarrollador 3
- `feature/modulo-reservas` - Desarrollador 4

---

### 3. TRABAJAR EN TU MÓDULO

#### Ciclo diario de trabajo:

```bash
# 1. Actualizar tu branch con lo último de develop
git checkout feature/tu-modulo
git pull origin develop  # Traer cambios de develop

# 2. Trabajar en tu código
# ... editar archivos ...

# 3. Ver qué archivos cambiaste
git status

# 4. Agregar archivos al staging
git add .                           # Agregar todos
# O específicos:
git add backend/app/Http/Controllers/ProductoController.php

# 5. Hacer commit
git commit -m "feat: implementar CRUD de productos"

# 6. Subir cambios a GitHub
git push origin feature/tu-modulo
```

---

### 4. CONVENCIONES DE COMMITS

**Formato:** `tipo: descripción breve`

**Tipos de commits:**
```bash
feat:      Nueva funcionalidad
fix:       Corrección de bug
docs:      Documentación
style:     Formato de código (sin cambios de lógica)
refactor:  Refactorización de código
test:      Agregar/modificar tests
chore:     Tareas de mantenimiento
```

**Ejemplos buenos:**
```bash
git commit -m "feat: agregar CRUD de productos"
git commit -m "fix: corregir validación de stock"
git commit -m "docs: actualizar README con instrucciones"
git commit -m "refactor: optimizar consulta de ventas"
```

**Ejemplos malos:**
```bash
git commit -m "cambios"           ❌
git commit -m "fix"               ❌
git commit -m "asdasd"            ❌
git commit -m "prueba 123"        ❌
```

---

### 5. CREAR PULL REQUEST (PR)

Cuando termines una funcionalidad:

1. **Subir tu branch actualizado:**
   ```bash
   git push origin feature/tu-modulo
   ```

2. **Ir a GitHub:**
   - https://github.com/tu-usuario/chayane-erp
   - Aparecerá un botón "Compare & pull request"
   - Click en el botón

3. **Llenar información del PR:**
   ```
   Título: [MODULO] Descripción breve
   
   Ejemplo:
   [INVENTARIO] Implementar CRUD de productos
   ```

   **Descripción del PR:**
   ```markdown
   ## Descripción
   Implementación completa del módulo de inventario con CRUD de productos.
   
   ## Cambios realizados
   - [x] CRUD de productos (crear, leer, actualizar, eliminar)
   - [x] Validaciones de formularios
   - [x] Control de stock mínimo
   - [x] Alertas de stock bajo
   
   ## Capturas de pantalla
   (opcional: adjuntar imágenes)
   
   ## Testing
   - [x] Probado en local
   - [x] Probado en PostgreSQL
   
   ## Checklist
   - [x] El código sigue los estándares del proyecto
   - [x] He probado mis cambios
   - [x] He actualizado la documentación si es necesario
   ```

4. **Asignar revisores:**
   - Asigna al menos a 1 compañero del equipo

5. **Labels:**
   - `enhancement`: Nueva funcionalidad
   - `bug`: Corrección de bug
   - `documentation`: Documentación

6. **Create pull request**

---

### 6. REVISAR PULL REQUESTS DE OTROS

Cuando un compañero te pida que revises su PR:

```bash
# 1. Ir a GitHub → Pull Requests → Abrir el PR

# 2. Revisar código en la pestaña "Files changed"
   - ¿El código es legible?
   - ¿Hay errores obvios?
   - ¿Sigue los estándares?

# 3. Dejar comentarios si es necesario
   - Click en la línea de código
   - Agregar comentario constructivo
   - Ejemplo: "Considera usar try-catch aquí para manejar errores"

# 4. Aprobar o solicitar cambios
   - "Approve" si está todo bien ✅
   - "Request changes" si necesita correcciones ⚠️
   - "Comment" solo para comentar 💬
```

---

### 7. MERGE DEL PULL REQUEST

**Solo el líder del equipo o el desarrollador hace el merge:**

1. **Esperar aprobación** de al menos 1 revisor
2. **Resolver conflictos** si los hay
3. **Click en "Merge pull request"**
4. **Confirmar merge**
5. **Eliminar branch** (opcional, GitHub lo sugiere)

---

### 8. ACTUALIZAR TU BRANCH LOCAL

Después de que se haga merge de un PR:

```bash
# 1. Cambiar a develop
git checkout develop

# 2. Traer últimos cambios
git pull origin develop

# 3. Volver a tu branch
git checkout feature/tu-modulo

# 4. Actualizar tu branch con develop
git merge develop

# 5. Si hay conflictos, resolverlos
# (ver sección de conflictos abajo)

# 6. Subir cambios
git push origin feature/tu-modulo
```

---

## 🔥 RESOLVER CONFLICTOS

### ¿Qué es un conflicto?

Ocurre cuando 2 personas modifican la misma línea de código.

### Ejemplo de conflicto:

```php
<<<<<<< HEAD
public function index()
{
    return view('productos.index');
}
=======
public function index()
{
    return view('productos.lista');
}
>>>>>>> develop
```

### Cómo resolver:

```bash
# 1. Git te dirá qué archivos tienen conflictos
git status

# 2. Abrir archivo con conflicto en tu editor

# 3. Buscar los marcadores:
#    <<<<<<< HEAD (tu código)
#    ======= (separador)
#    >>>>>>> develop (código de develop)

# 4. Decidir qué código mantener
#    - Puedes quedarte con uno
#    - O combinar ambos
#    - Elimina los marcadores <<<<<<, =======, >>>>>>>

# 5. Guardar archivo

# 6. Agregar archivo resuelto
git add archivo-con-conflicto.php

# 7. Continuar con merge
git commit -m "fix: resolver conflicto de merge"

# 8. Subir cambios
git push origin feature/tu-modulo
```

---

## 📋 COMANDOS ÚTILES

### Ver estado actual
```bash
git status              # Ver archivos modificados
git log --oneline       # Ver historial de commits
git branch              # Ver branches locales
git branch -a           # Ver todas las branches
```

### Deshacer cambios
```bash
git checkout -- archivo.php     # Descartar cambios de un archivo
git reset HEAD archivo.php      # Quitar archivo del staging
git reset --soft HEAD~1         # Deshacer último commit (mantener cambios)
git reset --hard HEAD~1         # Deshacer último commit (eliminar cambios)
```

### Actualizar
```bash
git fetch origin                # Traer cambios sin aplicarlos
git pull origin develop         # Traer y aplicar cambios
```

### Ver diferencias
```bash
git diff                        # Ver cambios no commiteados
git diff develop                # Ver diferencias con develop
```

### Eliminar branch
```bash
git branch -d feature/mi-branch     # Eliminar branch local
git push origin --delete feature/mi-branch  # Eliminar en GitHub
```

---

## 🛡️ MEJORES PRÁCTICAS

### ✅ HACER:

1. **Commits pequeños y frecuentes**
   ```bash
   # Cada funcionalidad chica = 1 commit
   git commit -m "feat: agregar validación de email"
   git commit -m "feat: agregar campo teléfono"
   ```

2. **Mensajes descriptivos**
   ```bash
   git commit -m "feat: implementar CRUD completo de productos con validaciones"
   ```

3. **Pull antes de push**
   ```bash
   git pull origin develop
   git push origin feature/mi-modulo
   ```

4. **Probar antes de hacer PR**
   - Verifica que tu código funcione
   - Prueba todas las funcionalidades

5. **Actualizar develop regularmente**
   ```bash
   # Cada día antes de empezar:
   git checkout develop
   git pull origin develop
   git checkout feature/mi-modulo
   git merge develop
   ```

### ❌ NO HACER:

1. **No hacer commit directamente a `main`**
2. **No hacer commit de archivos grandes** (videos, bases de datos)
3. **No hacer commit de `.env`** (credenciales sensibles)
4. **No hacer push forzado** (`git push -f`) sin consultar
5. **No mezclar múltiples funcionalidades** en un commit

---

## 🆘 PROBLEMAS COMUNES

### Problema: "Your branch is behind"
```bash
git pull origin develop
```

### Problema: "Merge conflict"
- Ver sección "Resolver Conflictos" arriba

### Problema: "Permission denied"
```bash
# Verificar acceso SSH
ssh -T git@github.com

# O cambiar a HTTPS
git remote set-url origin https://github.com/tu-usuario/chayane-erp.git
```

### Problema: "No puedo hacer push"
```bash
# Asegurarte de tener los últimos cambios
git pull origin feature/mi-modulo --rebase
git push origin feature/mi-modulo
```

### Problema: "Cambié a la branch equivocada"
```bash
# Guardar cambios temporalmente
git stash

# Cambiar a branch correcta
git checkout feature/mi-modulo

# Recuperar cambios
git stash pop
```

---

## 📅 RUTINA DIARIA RECOMENDADA

### Al empezar el día:
```bash
1. git checkout develop
2. git pull origin develop
3. git checkout feature/mi-modulo
4. git merge develop
5. # Empezar a trabajar
```

### Durante el día:
```bash
1. # Trabajar en tu código
2. git add .
3. git commit -m "feat: descripción"
4. git push origin feature/mi-modulo
5. # Repetir cada 1-2 horas
```

### Al terminar el día:
```bash
1. git add .
2. git commit -m "chore: progreso del día"
3. git push origin feature/mi-modulo
```

---

## 📚 RECURSOS ADICIONALES

- **Git Docs:** https://git-scm.com/doc
- **GitHub Docs:** https://docs.github.com/
- **Git Flow:** https://nvie.com/posts/a-successful-git-branching-model/
- **Interactive Tutorial:** https://learngitbranching.js.org/

---

## ✅ CHECKLIST SEMANAL

- [ ] He hecho al menos 1 commit por día
- [ ] Mis commits tienen mensajes descriptivos
- [ ] He actualizado mi branch con develop
- [ ] He revisado al menos 1 PR de un compañero
- [ ] He sincronizado mi código con el equipo
- [ ] No tengo conflictos pendientes

---

**¡Recuerda: La comunicación es clave en trabajo colaborativo!** 

Usa el grupo del equipo para coordinar y evitar conflictos. 🚀
