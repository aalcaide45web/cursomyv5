<?php declare(strict_types=1);

/**
 * Controlador del Player - Maneja reproducción, notas y comentarios
 */
class PlayerController {
    private NoteRepository $noteRepository;
    private CommentRepository $commentRepository;
    private ProgressRepository $progressRepository;
    private LessonRepository $lessonRepository;
    
    public function __construct() {
        $this->noteRepository = new NoteRepository();
        $this->commentRepository = new CommentRepository();
        $this->progressRepository = new ProgressRepository();
        $this->lessonRepository = new LessonRepository();
    }
    
    /**
     * Obtener notas de una lección
     */
    public function getNotes(int $lessonId): array {
        try {
            $notes = $this->noteRepository->getByLesson($lessonId);
            
            return [
                'success' => true,
                'data' => $notes
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener notas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear nueva nota
     */
    public function createNote(int $lessonId, int $timestamp, string $content): array {
        try {
            // Validar datos
            if (empty(trim($content))) {
                return [
                    'success' => false,
                    'error' => 'El contenido de la nota no puede estar vacío'
                ];
            }
            
            if ($timestamp < 0) {
                return [
                    'success' => false,
                    'error' => 'El timestamp no puede ser negativo'
                ];
            }
            
            $success = $this->noteRepository->create($lessonId, $timestamp, trim($content));
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Nota creada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo crear la nota'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al crear nota: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar nota
     */
    public function updateNote(int $noteId, int $timestamp, string $content): array {
        try {
            // Validar datos
            if (empty(trim($content))) {
                return [
                    'success' => false,
                    'error' => 'El contenido de la nota no puede estar vacío'
                ];
            }
            
            if ($timestamp < 0) {
                return [
                    'success' => false,
                    'error' => 'El timestamp no puede ser negativo'
                ];
            }
            
            $success = $this->noteRepository->update($noteId, $timestamp, trim($content));
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Nota actualizada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo actualizar la nota'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al actualizar nota: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar nota
     */
    public function deleteNote(int $noteId): array {
        try {
            $success = $this->noteRepository->delete($noteId);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Nota eliminada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo eliminar la nota'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al eliminar nota: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener comentarios de una lección
     */
    public function getComments(int $lessonId): array {
        try {
            $comments = $this->commentRepository->getByLesson($lessonId);
            
            return [
                'success' => true,
                'data' => $comments
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener comentarios: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear nuevo comentario
     */
    public function createComment(int $lessonId, string $content, ?int $timestamp = null): array {
        try {
            // Validar datos
            if (empty(trim($content))) {
                return [
                    'success' => false,
                    'error' => 'El contenido del comentario no puede estar vacío'
                ];
            }
            
            if ($timestamp !== null && $timestamp < 0) {
                return [
                    'success' => false,
                    'error' => 'El timestamp no puede ser negativo'
                ];
            }
            
            $success = $this->commentRepository->create($lessonId, trim($content), $timestamp);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Comentario creado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo crear el comentario'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al crear comentario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar comentario
     */
    public function updateComment(int $commentId, string $content, ?int $timestamp = null): array {
        try {
            // Validar datos
            if (empty(trim($content))) {
                return [
                    'success' => false,
                    'error' => 'El contenido del comentario no puede estar vacío'
                ];
            }
            
            if ($timestamp !== null && $timestamp < 0) {
                return [
                    'success' => false,
                    'error' => 'El timestamp no puede ser negativo'
                ];
            }
            
            $success = $this->commentRepository->update($commentId, trim($content), $timestamp);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Comentario actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo actualizar el comentario'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al actualizar comentario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar comentario
     */
    public function deleteComment(int $commentId): array {
        try {
            $success = $this->commentRepository->delete($commentId);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Comentario eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo eliminar el comentario'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al eliminar comentario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar progreso de la lección
     */
    public function updateProgress(int $lessonId, int $position, int $duration): array {
        try {
            // Obtener información de la lección
            $lesson = $this->lessonRepository->findById($lessonId);
            if (!$lesson) {
                return [
                    'success' => false,
                    'error' => 'Lección no encontrada'
                ];
            }
            
            $success = $this->progressRepository->updateProgress(
                $lesson['course_id'],
                $lessonId,
                $position,
                $duration
            );
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Progreso actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo actualizar el progreso'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al actualizar progreso: ' . $e->getMessage()
            ];
        }
    }
}
