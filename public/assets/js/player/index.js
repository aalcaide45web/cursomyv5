/**
 * Módulo del Player - Maneja reproducción, notas y comentarios
 */
export class Player {
    constructor() {
        this.video = null;
        this.currentLessonId = null;
        this.progressInterval = null;
        this.notes = [];
        this.comments = [];
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initNoteForms();
        this.initCommentForms();
    }
    
    bindEvents() {
        // Eventos del video
        document.addEventListener('DOMContentLoaded', () => {
            this.video = document.getElementById('lesson-video');
            if (this.video) {
                this.setupVideoEvents();
            }
        });
        
        // Eventos de formularios
        this.bindFormEvents();
    }
    
    setupVideoEvents() {
        if (!this.video) return;
        
        // Evento de tiempo actualizado
        this.video.addEventListener('timeupdate', () => {
            this.updateProgressBar();
            this.updateCurrentTime();
        });
        
        // Evento de cambio de velocidad
        const speedSelect = document.getElementById('playback-speed');
        if (speedSelect) {
            speedSelect.addEventListener('change', (e) => {
                this.video.playbackRate = parseFloat(e.target.value);
            });
        }
        
        // Evento de botón play/pause
        const playPauseBtn = document.getElementById('play-pause-btn');
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', () => {
                this.togglePlayPause();
            });
        }
        
        // Evento de pantalla completa
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                this.toggleFullscreen();
            });
        }
        
        // Evento de progreso
        const progressBar = document.getElementById('progress-bar');
        if (progressBar) {
            progressBar.addEventListener('click', (e) => {
                this.seekToPosition(e);
            });
            
            progressBar.addEventListener('mousemove', (e) => {
                this.showProgressHover(e);
            });
            
            progressBar.addEventListener('mouseleave', () => {
                this.hideProgressHover();
            });
        }
        
        // Evento de finalización
        this.video.addEventListener('ended', () => {
            this.onVideoEnded();
        });
        
        // Evento de carga de metadatos
        this.video.addEventListener('loadedmetadata', () => {
            this.onMetadataLoaded();
        });
    }
    
    bindFormEvents() {
        // Botones de agregar nota/comentario
        const addNoteBtn = document.getElementById('add-note-btn');
        const addCommentBtn = document.getElementById('add-comment-btn');
        
        if (addNoteBtn) {
            addNoteBtn.addEventListener('click', () => {
                this.toggleNoteForm();
            });
        }
        
        if (addCommentBtn) {
            addCommentBtn.addEventListener('click', () => {
                this.toggleCommentForm();
            });
        }
        
        // Botones de guardar/cancelar
        this.bindSaveCancelEvents();
    }
    
    bindSaveCancelEvents() {
        // Notas
        const saveNoteBtn = document.getElementById('save-note-btn');
        const cancelNoteBtn = document.getElementById('cancel-note-btn');
        
        if (saveNoteBtn) {
            saveNoteBtn.addEventListener('click', () => {
                this.saveNote();
            });
        }
        
        if (cancelNoteBtn) {
            cancelNoteBtn.addEventListener('click', () => {
                this.toggleNoteForm();
            });
        }
        
        // Comentarios
        const saveCommentBtn = document.getElementById('save-comment-btn');
        const cancelCommentBtn = document.getElementById('cancel-comment-btn');
        
        if (saveCommentBtn) {
            saveCommentBtn.addEventListener('click', () => {
                this.saveComment();
            });
        }
        
        if (cancelCommentBtn) {
            cancelCommentBtn.addEventListener('click', () => {
                this.toggleCommentForm();
            });
        }
    }
    
    initNoteForms() {
        // Inicializar formularios de notas
        const noteForm = document.getElementById('note-form');
        if (noteForm) {
            // Configurar timestamp automático
            const timestampInput = document.getElementById('note-timestamp');
            if (timestampInput && this.video) {
                timestampInput.addEventListener('focus', () => {
                    if (this.video && !isNaN(this.video.currentTime)) {
                        timestampInput.value = Math.floor(this.video.currentTime);
                    }
                });
            }
        }
    }
    
    initCommentForms() {
        // Inicializar formularios de comentarios
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            // Configurar timestamp automático
            const timestampCheck = document.getElementById('comment-timestamp-check');
            if (timestampCheck && this.video) {
                timestampCheck.addEventListener('change', (e) => {
                    const timestampInput = document.getElementById('comment-timestamp');
                    if (e.target.checked && this.video && !isNaN(this.video.currentTime)) {
                        timestampInput.value = Math.floor(this.video.currentTime);
                        timestampInput.disabled = true;
                    } else {
                        timestampInput.value = '';
                        timestampInput.disabled = false;
                    }
                });
            }
        }
    }
    
    // Métodos del video
    togglePlayPause() {
        if (this.video.paused) {
            this.video.play();
            this.updatePlayPauseButton(true);
        } else {
            this.video.pause();
            this.updatePlayPauseButton(false);
        }
    }
    
    updatePlayPauseButton(isPlaying) {
        const btn = document.getElementById('play-pause-btn');
        if (!btn) return;
        
        if (isPlaying) {
            btn.innerHTML = `
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 00-1 1v2a1 1 0 001 1h6a1 1 0 001-1V9a1 1 0 00-1-1H7z" clip-rule="evenodd"/>
                </svg>
            `;
        } else {
            btn.innerHTML = `
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
            `;
        }
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.video.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    
    seekToPosition(event) {
        const progressBar = event.currentTarget;
        const rect = progressBar.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        const percentage = clickX / rect.width;
        
        if (this.video && !isNaN(this.video.duration)) {
            this.video.currentTime = percentage * this.video.duration;
        }
    }
    
    showProgressHover(event) {
        const progressBar = event.currentTarget;
        const hoverBar = document.getElementById('progress-hover');
        if (!hoverBar) return;
        
        const rect = progressBar.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        const percentage = clickX / rect.width;
        
        hoverBar.style.width = `${percentage * 100}%`;
        hoverBar.classList.remove('opacity-0');
    }
    
    hideProgressHover() {
        const hoverBar = document.getElementById('progress-hover');
        if (hoverBar) {
            hoverBar.classList.add('opacity-0');
        }
    }
    
    updateProgressBar() {
        if (!this.video || isNaN(this.video.duration) || isNaN(this.video.currentTime)) return;
        
        const progressFill = document.getElementById('progress-fill');
        if (progressFill) {
            const percentage = (this.video.currentTime / this.video.duration) * 100;
            progressFill.style.width = `${percentage}%`;
        }
    }
    
    updateCurrentTime() {
        if (!this.video || isNaN(this.video.currentTime)) return;
        
        const currentTimeSpan = document.getElementById('current-time');
        if (currentTimeSpan) {
            currentTimeSpan.textContent = this.formatTime(this.video.currentTime);
        }
    }
    
    onMetadataLoaded() {
        const totalTimeSpan = document.getElementById('total-time');
        if (totalTimeSpan && this.video && !isNaN(this.video.duration)) {
            totalTimeSpan.textContent = this.formatTime(this.video.duration);
        }
        
        // Configurar tiempo de reanudado si existe
        const resumeTime = this.video.dataset.resumeTime;
        if (resumeTime && !isNaN(resumeTime) && parseFloat(resumeTime) > 0) {
            this.video.currentTime = parseFloat(resumeTime);
        }
        
        // Iniciar guardado de progreso
        this.startProgressSaving();
    }
    
    onVideoEnded() {
        // Marcar como completado
        this.updateProgress(this.video.duration, this.video.duration);
        
        // Mostrar mensaje de completado
        this.showCompletionMessage();
    }
    
    startProgressSaving() {
        // Guardar progreso cada 5 segundos
        this.progressInterval = setInterval(() => {
            if (this.video && !this.video.paused && !isNaN(this.video.currentTime)) {
                this.updateProgress(this.video.currentTime, this.video.duration);
            }
        }, 5000);
    }
    
    stopProgressSaving() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }
    
    // Métodos de progreso
    async updateProgress(position, duration) {
        if (!this.currentLessonId) return;
        
        try {
            const response = await fetch(`/api/lessons/${this.currentLessonId}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    position: Math.floor(position),
                    duration: Math.floor(duration)
                })
            });
            
            const result = await response.json();
            if (result.success) {
                this.showProgressIndicator(true);
            } else {
                console.error('Error al actualizar progreso:', result.error);
            }
        } catch (error) {
            console.error('Error al actualizar progreso:', error);
        }
    }
    
    showProgressIndicator(show) {
        const indicator = document.getElementById('progress-indicator');
        if (indicator) {
            if (show) {
                indicator.classList.add('animate-pulse');
                setTimeout(() => {
                    indicator.classList.remove('animate-pulse');
                }, 1000);
            } else {
                indicator.classList.remove('animate-pulse');
            }
        }
    }
    
    // Métodos de notas
    toggleNoteForm() {
        const form = document.getElementById('note-form');
        if (form) {
            form.classList.toggle('hidden');
            
            if (!form.classList.contains('hidden')) {
                // Limpiar formulario
                document.getElementById('note-content').value = '';
                if (this.video && !isNaN(this.video.currentTime)) {
                    document.getElementById('note-timestamp').value = Math.floor(this.video.currentTime);
                }
            }
        }
    }
    
    async saveNote() {
        const timestamp = parseInt(document.getElementById('note-timestamp').value) || 0;
        const content = document.getElementById('note-content').value.trim();
        
        if (!content) {
            alert('Por favor, escribe el contenido de la nota');
            return;
        }
        
        if (!this.currentLessonId) {
            alert('No hay lección seleccionada');
            return;
        }
        
        try {
            const response = await fetch(`/api/lessons/${this.currentLessonId}/notes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ timestamp, content })
            });
            
            const result = await response.json();
            if (result.success) {
                this.toggleNoteForm();
                this.loadNotes();
                alert('Nota guardada exitosamente');
            } else {
                alert('Error al guardar nota: ' + result.error);
            }
        } catch (error) {
            console.error('Error al guardar nota:', error);
            alert('Error al guardar nota');
        }
    }
    
    // Métodos de comentarios
    toggleCommentForm() {
        const form = document.getElementById('comment-form');
        if (form) {
            form.classList.toggle('hidden');
            
            if (!form.classList.contains('hidden')) {
                // Limpiar formulario
                document.getElementById('comment-content').value = '';
                document.getElementById('comment-timestamp-check').checked = false;
                const timestampInput = document.getElementById('comment-timestamp');
                timestampInput.value = '';
                timestampInput.disabled = false;
            }
        }
    }
    
    async saveComment() {
        const content = document.getElementById('comment-content').value.trim();
        const includeTimestamp = document.getElementById('comment-timestamp-check').checked;
        let timestamp = null;
        
        if (includeTimestamp && this.video && !isNaN(this.video.currentTime)) {
            timestamp = Math.floor(this.video.currentTime);
        }
        
        if (!content) {
            alert('Por favor, escribe el contenido del comentario');
            return;
        }
        
        if (!this.currentLessonId) {
            alert('No hay lección seleccionada');
            return;
        }
        
        try {
            const response = await fetch(`/api/lessons/${this.currentLessonId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content, timestamp })
            });
            
            const result = await response.json();
            if (result.success) {
                this.toggleCommentForm();
                this.loadComments();
                alert('Comentario guardado exitosamente');
            } else {
                alert('Error al guardar comentario: ' + result.error);
            }
        } catch (error) {
            console.error('Error al guardar comentario:', error);
            alert('Error al guardar comentario');
        }
    }
    
    // Métodos de carga
    async loadNotes() {
        if (!this.currentLessonId) return;
        
        try {
            const response = await fetch(`/api/lessons/${this.currentLessonId}/notes`);
            const result = await response.json();
            
            if (result.success) {
                this.notes = result.data;
                this.displayNotes();
            }
        } catch (error) {
            console.error('Error al cargar notas:', error);
        }
    }
    
    async loadComments() {
        if (!this.currentLessonId) return;
        
        try {
            const response = await fetch(`/api/lessons/${this.currentLessonId}/comments`);
            const result = await response.json();
            
            if (result.success) {
                this.comments = result.data;
                this.displayComments();
            }
        } catch (error) {
            console.error('Error al cargar comentarios:', error);
        }
    }
    
    displayNotes() {
        const notesList = document.getElementById('notes-list');
        if (!notesList) return;
        
        if (this.notes.length === 0) {
            notesList.innerHTML = '<p class="text-gray-500 text-center py-4">No hay notas para esta lección</p>';
            return;
        }
        
        notesList.innerHTML = this.notes.map(note => `
            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <button onclick="player.jumpToTime(${note.timestamp})" 
                            class="text-blue-400 hover:text-blue-300 text-sm font-mono">
                        ${this.formatTime(note.timestamp)}
                    </button>
                    <div class="flex space-x-2">
                        <button onclick="player.editNote(${note.id})" class="text-gray-400 hover:text-white text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                        </button>
                        <button onclick="player.deleteNote(${note.id})" class="text-red-400 hover:text-red-300 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="text-gray-300 text-sm">${note.content}</p>
            </div>
        `).join('');
    }
    
    displayComments() {
        const commentsList = document.getElementById('comments-list');
        if (!commentsList) return;
        
        if (this.comments.length === 0) {
            commentsList.innerHTML = '<p class="text-gray-500 text-center py-4">No hay comentarios para esta lección</p>';
            return;
        }
        
        commentsList.innerHTML = this.comments.map(comment => `
            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        ${comment.timestamp ? `
                            <button onclick="player.jumpToTime(${comment.timestamp})" 
                                    class="text-blue-400 hover:text-blue-300 text-sm font-mono">
                                ${this.formatTime(comment.timestamp)}
                            </button>
                        ` : ''}
                        <span class="text-gray-500 text-xs">${new Date(comment.created_at).toLocaleDateString()}</span>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="player.editComment(${comment.id})" class="text-gray-400 hover:text-white text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                        </button>
                        <button onclick="player.deleteComment(${comment.id})" class="text-red-400 hover:text-red-300 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="text-gray-300 text-sm">${comment.content}</p>
            </div>
        `).join('');
    }
    
    // Métodos de utilidad
    jumpToTime(timestamp) {
        if (this.video && !isNaN(timestamp)) {
            this.video.currentTime = timestamp;
            this.video.focus();
        }
    }
    
    formatTime(seconds) {
        if (isNaN(seconds) || seconds === 0) return '0:00';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }
    
    showCompletionMessage() {
        // Mostrar mensaje de lección completada
        const playerArea = document.getElementById('player-area');
        if (playerArea) {
            playerArea.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-green-400 text-lg mb-4">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ¡Lección completada!
                    </div>
                    <p class="text-gray-500">Has terminado esta lección exitosamente</p>
                </div>
            `;
        }
    }
    
    // Métodos de edición y eliminación
    editNote(noteId) {
        // TODO: Implementar edición de notas
        console.log('Editar nota:', noteId);
    }
    
    deleteNote(noteId) {
        if (confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            this.performDeleteNote(noteId);
        }
    }
    
    async performDeleteNote(noteId) {
        try {
            const response = await fetch(`/api/notes/${noteId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                this.loadNotes();
                alert('Nota eliminada exitosamente');
            } else {
                alert('Error al eliminar nota: ' + result.error);
            }
        } catch (error) {
            console.error('Error al eliminar nota:', error);
            alert('Error al eliminar nota');
        }
    }
    
    editComment(commentId) {
        // TODO: Implementar edición de comentarios
        console.log('Editar comentario:', commentId);
    }
    
    deleteComment(commentId) {
        if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
            this.performDeleteComment(commentId);
        }
    }
    
    async performDeleteComment(commentId) {
        try {
            const response = await fetch(`/api/comments/${commentId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                this.loadComments();
                alert('Comentario eliminado exitosamente');
            } else {
                alert('Error al eliminar comentario: ' + result.error);
            }
        } catch (error) {
            console.error('Error al eliminar comentario:', error);
            alert('Error al eliminar comentario');
        }
    }
    
    // Método para establecer la lección actual
    setCurrentLesson(lessonId) {
        this.currentLessonId = lessonId;
        this.loadNotes();
        this.loadComments();
    }
    
    // Método de limpieza
    destroy() {
        this.stopProgressSaving();
        this.video = null;
        this.currentLessonId = null;
    }
}

// Crear instancia global
window.player = new Player();
