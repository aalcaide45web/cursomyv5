# PROMPT PARA CURSOR — PROYECTO “CursoMy LMS Lite” (PHP + SQLite + Tailwind)

Actúa como **arquitecto y coder senior**. Crea un **LMS ligero** en **PHP 8** + **SQLite** + **TailwindCSS** (estilo **glassmorphism**), **sin frameworks**, con **muchos ficheros pequeños (<200 líneas)** y **alta modularidad** (JS ESM por piezas). Nada de “mega-ficheros”. Cada **fase** debe **compilar/funcionar** antes de avanzar. Entrega **commits atómicos** y **tests manuales** por fase.

## 0) CONTEXTO Y FUNCIONALIDAD

**Estructura del FS (no hay subida por formulario):**  
`/uploads/{topic}/{instructor}/{course}/{section}/{lesson.(mp4|mkv|webm|mov)}`

**Requisitos clave:**
- **Dashboard** con tarjetas de curso: miniatura, nombre, instructor, temática, ⭐ media, y botones:
  - **Ver**, **Reanudar**, **Renombrar**, **Eliminar (soft delete)**.
- **Botón 1**: **Ingesta incremental** de `/uploads` (detectar cambios por mtime/size/hash).
- **Botón 2**: **Reconstrucción total** (rescan completo) **respetando soft delete**.
- **Player** HTML5 con **velocidades 0,50–10,00 en pasos de 0,25**, reanudado del punto exacto.
- **Notas por clase** con **timestamp** (clic → salto a ese tiempo).
- **Comentarios por clase** (texto; con o sin timestamp).
- **Valoraciones (1–5 estrellas)** por curso, con media y conteo.
- **Buscador global** por: temática, instructor, curso, sección, clase, nota, **minuto/segundo** (timestamp), comentario. Preferir **FTS5**; si no, índices normales.
- **Miniaturas + duración** con `ffprobe/ffmpeg` si está disponible; si no, fallback calculado en JS la primera reproducción.

**Convenciones y calidad:**
- PHP con `declare(strict_types=1)`, funciones puras donde aplique, validación de entrada, respuestas JSON limpias.
- **Router** propio minimalista.
- **Vistas** con componentes reutilizables (PHP + Tailwind).
- **JS** en módulos ESM pequeños (cada fichero idealmente <150 líneas).
- **Nombres claros y consistentes** (snake_case en DB, PascalCase para clases PHP, kebab-case para assets).
- **Seguridad**: no exponer FS real; servir rutas relativas validadas bajo `/uploads`; sanitizar inputs; CSP básica; no XSS.

**Asunciones por defecto (si no se dice lo contrario):**
- Extensiones vídeo válidas: `mp4,mkv,webm,mov`.
- `ffmpeg/ffprobe` disponibles (si no, habilitar fallback).
- Eliminado de curso = **soft delete** (`is_deleted=1`) para que **no** reaparezca en rebuild; añadir “Reactivar”.

---

## 1) ESTRUCTURA DE PROYECTO (carpetas/archivos)

Crea la siguiente estructura inicial:

```
/public/
  index.php
  /assets/
    /css/
      tailwind.css         # generado o CDN
    /js/
      main.js
      /dashboard/
        index.js
      /player/
        init.js
        speeds.js
        progress.js
        notes.js
        comments.js
        api.js
/app/
  Router.php
  /Lib/
    Str.php
    Time.php
    JsonResponse.php
    Validate.php
  /Services/
    DB.php
    /Scanner/
      FilesystemScanner.php
      Hasher.php
      Importer.php
    /Media/
      Probe.php
  /Repositories/
    TopicRepository.php
    InstructorRepository.php
    CourseRepository.php
    SectionRepository.php
    LessonRepository.php
    RatingRepository.php
    NoteRepository.php
    CommentRepository.php
    ProgressRepository.php
    ScanIndexRepository.php
  /Controllers/
    ScanController.php
    CourseController.php
    LessonController.php
    RatingController.php
    NoteController.php
    CommentController.php
    SearchController.php
  /Views/
    /partials/
      layout.php
      topbar.php
    /components/
      CourseCard.php
      SectionList.php
      LessonItem.php
      Stars.php
    /pages/
      dashboard.php
      course.php
      lesson.php
/config/
  .env.php.example
/database/
  schema.sql
/scripts/
  init_db.php
  scan_incremental.php
  rebuild.php
/cache/
  /thumbs/.gitkeep
/uploads/.gitkeep
```

`/config/.env.php.example`:
```php
<?php declare(strict_types=1);
return [
  'DB_PATH'       => __DIR__ . '/../database/app.db',
  'UPLOADS_PATH'  => __DIR__ . '/../uploads',
  'CACHE_PATH'    => __DIR__ . '/../cache',
  'USE_FFMPEG'    => true
];
```

---

## 2) MODELO DE DATOS (SQLite) + REPOS

`/database/schema.sql` con tablas e índices:

```sql
PRAGMA journal_mode=WAL;
PRAGMA synchronous=NORMAL;

CREATE TABLE topic (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE
);

CREATE TABLE instructor (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE
);

CREATE TABLE course (
  id INTEGER PRIMARY KEY,
  topic_id INTEGER NOT NULL,
  instructor_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  cover_path TEXT,
  avg_rating REAL DEFAULT 0,
  ratings_count INTEGER DEFAULT 0,
  is_deleted INTEGER DEFAULT 0,
  FOREIGN KEY(topic_id) REFERENCES topic(id),
  FOREIGN KEY(instructor_id) REFERENCES instructor(id)
);

CREATE TABLE section (
  id INTEGER PRIMARY KEY,
  course_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  order_index INTEGER DEFAULT 0,
  FOREIGN KEY(course_id) REFERENCES course(id)
);

CREATE TABLE lesson (
  id INTEGER PRIMARY KEY,
  section_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  file_path TEXT NOT NULL UNIQUE,
  duration_seconds REAL DEFAULT 0,
  thumb_path TEXT,
  order_index INTEGER DEFAULT 0,
  FOREIGN KEY(section_id) REFERENCES section(id)
);

CREATE TABLE rating (
  id INTEGER PRIMARY KEY,
  course_id INTEGER NOT NULL,
  stars INTEGER NOT NULL CHECK(stars BETWEEN 1 AND 5),
  created_at TEXT NOT NULL,
  FOREIGN KEY(course_id) REFERENCES course(id)
);

CREATE TABLE note (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  t_seconds REAL NOT NULL,
  text TEXT NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE comment (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  text TEXT NOT NULL,
  t_seconds REAL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE progress (
  id INTEGER PRIMARY KEY,
  lesson_id INTEGER NOT NULL,
  last_t_seconds REAL NOT NULL,
  updated_at TEXT NOT NULL,
  FOREIGN KEY(lesson_id) REFERENCES lesson(id)
);

CREATE TABLE scan_index (
  id INTEGER PRIMARY KEY,
  file_path TEXT NOT NULL UNIQUE,
  file_mtime INTEGER NOT NULL,
  file_size INTEGER NOT NULL,
  file_hash TEXT,
  last_seen_at TEXT NOT NULL
);

CREATE INDEX idx_section_course ON section(course_id);
CREATE INDEX idx_lesson_section ON lesson(section_id);
CREATE INDEX idx_progress_lesson ON progress(lesson_id);
CREATE INDEX idx_note_lesson ON note(lesson_id);
CREATE INDEX idx_comment_lesson ON comment(lesson_id);
CREATE INDEX idx_rating_course ON rating(course_id);
```

**Opcional FTS5** (si build lo soporta) para buscador:
```
CREATE VIRTUAL TABLE search_index USING fts5(
  entity_type,   -- topic | instructor | course | section | lesson | note | comment
  title,         -- nombre o título
  body,          -- texto descriptivo
  course_id UNINDEXED,
  lesson_id UNINDEXED,
  t_seconds UNINDEXED
);
```

Implementa **Repositorios** (CRUD esenciales) por entidad. Cada repo enfocado y breve (<200 líneas).

---

## 3) SERVICIOS DE ESCANEO + MEDIA

- **FilesystemScanner**: recorre `/uploads` (recursivo) y emite items con `{topic,instructor,course,section,lesson,file_path,mtime,size}`.
- **Hasher**: `xxh3` o `sha256` opcional para cambios fiables.
- **Importer**: sincroniza DB:
  - Resuelve/crea `topic`/`instructor`/`course` (respeta `is_deleted=1` si así se indica).
  - Crea `section` y `lesson` con `order_index` basado en orden en FS.
  - Actualiza `scan_index` (mtime,size,hash,last_seen_at).
- **Probe (Media)**:
  - Si `USE_FFMPEG`: usar `ffprobe` para `duration_seconds` y `ffmpeg` para **thumbs** en `/cache/thumbs/...`.
  - Si **no** hay ffmpeg: marcar pendientes y permitir que el **player JS** suba duración al reproducir por 1ª vez.

**Endpoints API (POST):**
- `/api/scan/incremental` → hace ingesta incremental (solo cambios).
- `/api/scan/rebuild` → reconstruye desde cero **manteniendo** `notes/comments/progress/ratings` y **respetando soft deletes** (no revivir `is_deleted=1` salvo parámetro explícito).

**UI**: botones en dashboard que muestren **progreso** (porcentaje + logs).

---

## 4) ROUTER, CONTROLADORES Y VISTAS

- **Router** simple en `/app/Router.php` con soporte GET/POST/PATCH y dispatch a controladores.
- **Controladores**:
  - `ScanController` (incremental/rebuild)
  - `CourseController` (listar, ver, reanudar, renombrar, eliminar/reactivar)
  - `LessonController` (mostrar player; set duration fallback)
  - `RatingController` (crear y consultar media)
  - `NoteController` (CRUD mínimo; listar por lesson)
  - `CommentController` (crear/listar por lesson)
  - `SearchController` (global search con FTS5 o consultas compuestas)
- **Vistas** con componentes:
  - `layout.php`, `topbar.php` (buscador + botones de escaneo)
  - `CourseCard.php`, `SectionList.php`, `LessonItem.php`, `Stars.php`
  - `pages/dashboard.php`, `pages/course.php`, `pages/lesson.php`

---

## 5) DASHBOARD

- **Tarjetas**: miniatura (`cover_path` o del primer `lesson`), nombre, instructor, temática, ⭐ media, botones:
  - **Ver** `/course/{slug}`
  - **Reanudar** (buscar `lesson` con `progress.updated_at` más reciente del curso)
  - **Renombrar** (PATCH `/api/course/{id}/rename`)
  - **Eliminar** (PATCH `/api/course/{id}/delete` → `is_deleted=1`)
  - **Reactivar** si `is_deleted=1`
- **Topbar** con buscador global + **Botón 1** (Incremental) + **Botón 2** (Rebuild).
- Estética **glassmorphism** con Tailwind (blur, transparencias, transiciones).

---

## 6) PÁGINA DE CURSO Y LISTADO DE CLASES

- `/course/{slug}`: lista de **secciones** (ordenadas) y **lessons** (ordenadas).
- Componentes reutilizables; cada lesson con acceso rápido a **Reproducir**.

---

## 7) PLAYER + VELOCIDADES + REANUDADO + NOTAS/COMENTARIOS

- Página `/lesson/{id}` con `<video>` HTML5.
- **Velocidades**: **0,50 → 10,00** en **pasos de 0,25**; selector + botones ±; mostrar etiqueta actual.
- **Atajos** (opcional): `J/K/L` (–10s/pausa/+10s), `M` (añadir nota), `.` y `,` (frame step si aplica).
- **Reanudado**:
  - En `timeupdate` (cada 3–5 s), `POST /api/progress` con `{lesson_id,last_t_seconds}`.
  - Al cargar, colocar `currentTime` con el valor guardado.
- **Notas**:
  - Botón “Añadir nota”: captura `currentTime`, muestra modal y guarda en `/api/notes`.
  - Listado de notas bajo el player; **clic** en nota → `currentTime = note.t_seconds` (+ reproducir).
- **Comentarios**:
  - Crear comentario con o sin timestamp (`t_seconds`).
  - Listado paginado si crecen.

**JS (módulos ESM, ficheros pequeños):**
- `/public/assets/js/player/speeds.js`: genera array 0.50..10.00 step 0.25; UI + `preservesPitch`.
- `/public/assets/js/player/progress.js`: lógica reanudado (debounce 3–5s).
- `/public/assets/js/player/notes.js`: CRUD notas y eventos de salto.
- `/public/assets/js/player/comments.js`: CRUD comentarios.
- `/public/assets/js/player/api.js`: helper `fetch` JSON.

**Endpoints mínimos:**
- `POST /api/progress`
- `POST /api/notes`  | `GET /api/notes?lesson_id=...`
- `POST /api/comments` | `GET /api/comments?lesson_id=...`

---

## 8) VALORACIONES (ESTRELLAS)

- **UI**: componente `Stars.php` reutilizable en tarjeta y en página de curso.
- **API**:
  - `POST /api/rating` `{course_id, stars}`
  - `GET /api/rating?course_id=...` → `{avg_rating, ratings_count}`
- **Lógica**: tras insertar, recalcular `avg_rating` y `ratings_count` (trigger SQL o en servidor).

---

## 9) BUSCADOR GLOBAL

- **Endpoint**: `GET /api/search?q=...`
- **Respuesta**: lista heterogénea con `entity_type` (`topic/instructor/course/section/lesson/note/comment`), `title`, `snippet`, `course_id`, `lesson_id`, `t_seconds?`.
- **Implementación**:
  - **Preferir FTS5**: indexar `title/body` por entidad.
  - Si no hay FTS5: combinar consultas con índices; soportar búsqueda por **minuto** (números → coincidencias en `note.t_seconds`).

---

## 10) REGLAS DE SEGURIDAD Y RENDIMIENTO

- Validar **todas** las rutas de archivo y asegurar que **siempre** están bajo `UPLOADS_PATH`.
- No interpolar rutas absolutas en HTML; servir por rutas relativas validadas o streams controlados.
- **Transacciones** en ingesta y rebuild.
- **Índices** creados según `schema.sql`.
- **CSP** simple y cabeceras (`X-Content-Type-Options: nosniff`).
- **Paginación** en notas/comentarios en caso de crecimiento.

---

## 11) FASES DE ENTREGA (PLAN DE OBRA)

### **FASE 0 — Bootstrap**
- Crear estructura de carpetas/archivos con contenido mínimo:
  - `public/index.php` (router + vista dashboard vacía)
  - `app/Router.php`, `Views/partials/layout.php`, `Views/pages/dashboard.php`
  - `config/.env.php.example`, `database/schema.sql`, `scripts/init_db.php`
  - Tailwind (CDN inicialmente), `public/assets/js/main.js`
- **Test manual**: `php -S localhost:8000 -t public` → se ve dashboard vacío.

### **FASE 1 — DB y Repos**
- Implementar `DB.php` (PDO SQLite, pragmas) y repos por entidad.
- `scripts/init_db.php` crea `app.db`.
- **Test manual**: inserts/selects básicos desde repos.

### **FASE 2 — Escáner + Importer + Probe**
- Implementar `FilesystemScanner`, `Hasher`, `Importer`, `Probe`.
- Endpoints `POST /api/scan/incremental` y `POST /api/scan/rebuild`.
- **UI**: botones en dashboard con barra de progreso.
- **Test manual**: colocar vídeos en `/uploads` y verificar ingesta.

### **FASE 3 — Dashboard completo**
- `CourseCard.php`, `topbar.php`, grid responsivo Tailwind.
- Acciones: Ver, Reanudar, Renombrar (PATCH), Eliminar (soft), Reactivar.
- **Test manual**: operar sobre cursos y ver reflejo en DB.

### **FASE 4 — Curso y Secciones**
- `/course/{slug}` con secciones y lessons ordenadas (`order_index`).
- **Test manual**: navegación fluida y correcta.

### **FASE 5 — Player + Velocidades + Reanudado + Notas/Comentarios**
- Player con **0,50–10,00 step 0,25**, `preservesPitch` si soporta.
- Reanudado (POST progresivo).
- Notas con salto por clic y comentarios con/ sin timestamp.
- **Test manual**: reproducir, cambiar velocidad, guardar nota, saltar.

### **FASE 6 — Valoraciones**
- Estrellas clicables; API rating; recálculo de `avg_rating/ratings_count`.
- **Test manual**: votar y ver promedio actualizado.

### **FASE 7 — Buscador Global**
- FTS5 si disponible, si no, consultas compuestas.
- **Test manual**: buscar por nombres, notas y por minuto (timestamps).

### **FASE 8 — Extras y Pulido**
- Historial reciente (últimas 10 lessons).
- Export/Import JSON de `notes/comments/progress/ratings`.
- Fallback sin ffmpeg (JS reporta duración a `/api/lesson/{id}/duration`).
- Accesibilidad básica y atajos de teclado.

---

## 12) CRITERIOS DE ACEPTACIÓN GENERALES

- **Ficheros pequeños y modulares** (ideal <150 líneas; máximo <200 salvo vistas largas).
- Cada fase **funciona de extremo a extremo** antes de seguir.
- **Logs** claros en escaneo/rebuild y barra de progreso en UI.
- Player con control de velocidad **0,50–10,00** en pasos de **0,25** totalmente operativo.
- Notas con salto instantáneo; comentarios visibles; reanudado persistente.
- Buscador devuelve entidades mixtas y navega correctamente.
- Soft delete respetado en rebuild; opción de reactivar.

---

## 13) INDICACIONES DE IMPLEMENTACIÓN DETALLADAS (PUNTOS FINOS)

- **Slugify**: normalizar tildes, espacios → `-`; estable para que no cambien slugs si cambian mínimamente los nombres.
- **Orden**: `order_index` por posición natural en FS (orden alfabético por defecto).
- **Miniatura de curso**: usar `cover.jpg` si existe en carpeta del curso; si no, primera miniatura de `lesson`.
- **Reanudar curso**: última `lesson` por `progress.updated_at` del curso; si empate, mayor `last_t_seconds`.
- **API JSON**: usar `JsonResponse::ok($data)` y `JsonResponse::error($message, $code)`.
- **Validación**: `Validate.php` con helpers para ints, floats, strings (longitud, patrones).
- **CORS**: no necesario si todo sirve desde el mismo host.

---

## 14) PRUEBAS MANUALES RÁPIDAS (CHECKLIST)

- [ ] Iniciar servidor: `php -S localhost:8000 -t public`.
- [ ] Colocar 2–3 cursos de ejemplo en `/uploads` y ejecutar **Incremental** → aparecen tarjetas.
- [ ] **Rebuild** → respeta cursos eliminados (no reviven).
- [ ] Player: cambiar velocidad (0,50…10,00), pausar, reanudar → persiste.
- [ ] Crear 3 notas en distintos tiempos y saltar con clic.
- [ ] Crear comentarios; listar; con timestamp opcional.
- [ ] Valorar un curso y ver media actualizada.
- [ ] Buscar por instructor/curso/sección/nota y por minuto (“120” → notas ~120s).

---

## 15) ENTREGABLES

- Código completo con estructura descrita.
- `README.md` minimal: instalación rápida, requisitos (ffmpeg opcional), comandos, rutas.
- Commits por fase, mensajes claros.
- Sin dependencias de frameworks externos (solo Tailwind vía CDN o build simple).

> **Comienza por la FASE 0** creando la estructura, `schema.sql`, `init_db.php`, el router básico y el dashboard vacío. Luego progresa fase por fase hasta completar todos los requisitos.
