# Estructura de Carpetas de Ejemplo

Esta es la estructura esperada para organizar tus cursos:

```
uploads/
├── topic1/                    # Temática (ej: "Programación")
│   ├── instructor1/           # Instructor (ej: "Juan Pérez")
│   │   ├── curso1/            # Curso (ej: "PHP Básico")
│   │   │   ├── seccion1/      # Sección (ej: "Introducción")
│   │   │   │   ├── leccion1.mp4
│   │   │   │   └── leccion2.mp4
│   │   │   └── seccion2/      # Sección (ej: "Variables")
│   │   │       ├── leccion3.mp4
│   │   │       └── leccion4.mp4
│   │   └── curso2/            # Curso (ej: "PHP Avanzado")
│   │       └── seccion1/
│   │           └── leccion1.mp4
│   └── instructor2/           # Otro instructor
│       └── curso3/
│           └── seccion1/
│               └── leccion1.mp4
└── topic2/                    # Otra temática (ej: "Diseño")
    └── instructor3/
        └── curso4/
            └── seccion1/
                └── leccion1.mp4
```

## Formatos de Video Soportados

- MP4 (.mp4)
- MKV (.mkv)
- WebM (.webm)
- MOV (.mov)

## Notas Importantes

1. **Nombres de carpetas**: Usa nombres descriptivos sin caracteres especiales
2. **Estructura**: Respeta la jerarquía topic/instructor/curso/seccion/leccion
3. **Archivos de video**: Solo se procesarán archivos de video válidos
4. **Escaneo**: El sistema detectará automáticamente cambios en los archivos

## Ejemplo de Uso

1. Coloca tus archivos de video siguiendo la estructura anterior
2. Haz clic en "Incremental" para escanear solo cambios
3. Haz clic en "Rebuild" para reconstruir completamente la base de datos
4. El sistema generará automáticamente miniaturas y extraerá metadatos

## Comandos de Prueba

Puedes probar el sistema con estos endpoints:

- `GET /api/scan/system-info` - Información del sistema
- `GET /api/scan/stats` - Estadísticas de escaneo
- `GET /api/scan/ffmpeg-info` - Estado de ffmpeg
- `POST /api/scan/incremental` - Escaneo incremental
- `POST /api/scan/rebuild` - Reconstrucción completa
