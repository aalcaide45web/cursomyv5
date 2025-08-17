# CursoMy LMS Lite

Un sistema de gestión de aprendizaje (LMS) ligero y moderno construido con PHP 8, SQLite y TailwindCSS.

## 🚀 Características

- **Dashboard moderno** con estilo glassmorphism y modo oscuro
- **Escaneo inteligente** de archivos de video (incremental y rebuild completo)
- **Player HTML5** con control de velocidad (0.50x - 10.00x)
- **Sistema de notas** con timestamps y salto automático
- **Comentarios** por lección con o sin timestamp
- **Valoraciones** por curso (1-5 estrellas)
- **Buscador global** con soporte FTS5
- **Reanudado automático** de lecciones
- **Miniaturas automáticas** generadas con ffmpeg

## 📋 Requisitos

- PHP 8.0 o superior
- SQLite3
- ffmpeg (opcional, para miniaturas y duración de videos)
- Servidor web (Apache/Nginx) o servidor de desarrollo PHP

## 🛠️ Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/aalcaide45web/cursomyv5.git
   cd cursomyv5
   ```

2. **Configurar la base de datos:**
   ```bash
   php scripts/init_db.php
   ```

3. **Configurar el entorno:**
   ```bash
   cp config/env.example.php config/env.php
   # Editar config/env.php según tus necesidades
   ```

4. **Iniciar el servidor de desarrollo:**
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Abrir en el navegador:**
   ```
   http://localhost:8000
   ```

## 📁 Estructura del Proyecto

```
cursomyV5/
├── public/                 # Archivos públicos (punto de entrada)
├── app/                    # Lógica de la aplicación
│   ├── Router.php         # Router minimalista
│   ├── Lib/               # Utilidades
│   ├── Services/          # Servicios (DB, Scanner, Media)
│   ├── Repositories/      # Acceso a datos
│   ├── Controllers/       # Controladores
│   └── Views/             # Vistas y componentes
├── config/                 # Configuración
├── database/               # Esquema de base de datos
├── scripts/                # Scripts de utilidad
├── cache/                  # Cache y miniaturas
└── uploads/                # Cursos y lecciones
```

## 📚 Uso

### Estructura de Carpetas para Cursos

Coloca tus cursos en la carpeta `/uploads` siguiendo esta estructura:

```
/uploads/
├── {tema}/
│   ├── {instructor}/
│   │   ├── {curso}/
│   │   │   ├── {seccion}/
│   │   │   │   ├── leccion1.mp4
│   │   │   │   ├── leccion2.mp4
│   │   │   │   └── leccion3.mp4
│   │   │   └── {otra-seccion}/
│   │   │       └── leccion4.mp4
│   │   └── {otro-curso}/
│   └── {otro-tema}/
```

### Escaneo de Archivos

1. **Escaneo Incremental:** Detecta solo archivos nuevos o modificados
2. **Rebuild Completo:** Reconstruye toda la base de datos respetando soft deletes

### Funcionalidades del Player

- **Velocidades:** 0.50x a 10.00x en pasos de 0.25x
- **Notas:** Clic en cualquier momento para crear nota con timestamp
- **Comentarios:** Agregar comentarios con o sin timestamp
- **Reanudado:** Continúa automáticamente desde donde lo dejaste

## 🔧 Desarrollo

### Fases de Implementación

- **FASE 0:** ✅ Bootstrap y estructura básica
- **FASE 1:** 🔄 Base de datos y repositorios
- **FASE 2:** 🔄 Escáner e importer
- **FASE 3:** 🔄 Dashboard completo
- **FASE 4:** 🔄 Páginas de curso y secciones
- **FASE 5:** 🔄 Player y funcionalidades
- **FASE 6:** 🔄 Sistema de valoraciones
- **FASE 7:** 🔄 Buscador global
- **FASE 8:** 🔄 Extras y pulido

### Comandos Útiles

```bash
# Inicializar base de datos
php scripts/init_db.php

# Servidor de desarrollo
php -S localhost:8000 -t public

# Verificar ffmpeg
ffmpeg -version
ffprobe -version
```

## 🎨 Personalización

El sistema usa TailwindCSS con configuración personalizada para el modo oscuro y efectos glassmorphism. Puedes modificar los estilos en:

- `app/Views/partials/layout.php` - Estilos globales
- `public/assets/js/main.js` - Configuración de Tailwind

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un issue o pull request para sugerencias y mejoras.

---

**Desarrollado con ❤️ usando PHP 8, SQLite y TailwindCSS**
