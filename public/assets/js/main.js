// CursoMy LMS Lite - JavaScript Principal
console.log('🚀 CursoMy LMS Lite iniciando...');

// Importar módulos del dashboard
import { Dashboard } from './dashboard/index.js';

// Importar módulo de valoraciones
import './ratings/index.js';

// Importar módulo de búsqueda global
import './search/index.js';

// Funcionalidad global
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM cargado, inicializando aplicación...');
    
    // Inicializar funcionalidades globales
    initGlobalSearch();
    initScanButtons();
});

// Buscador global
function initGlobalSearch() {
    const searchInput = document.getElementById('global-search');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) return;
        
        searchTimeout = setTimeout(() => {
            performGlobalSearch(query);
        }, 300);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                performGlobalSearch(query);
            }
        }
    });
}

// Realizar búsqueda global
async function performGlobalSearch(query) {
    try {
        console.log(`🔍 Buscando: ${query}`);
        // TODO: Implementar en FASE 7
        console.log('Búsqueda global pendiente de implementación en FASE 7');
    } catch (error) {
        console.error('❌ Error en búsqueda global:', error);
    }
}

// Inicializar botones de escaneo
function initScanButtons() {
    const incrementalBtn = document.getElementById('incremental-scan');
    const rebuildBtn = document.getElementById('rebuild-scan');
    const firstScanBtn = document.getElementById('first-scan');
    
    // Botones del dashboard
    if (incrementalBtn) {
        incrementalBtn.addEventListener('click', () => startScan('incremental'));
    }
    
    if (rebuildBtn) {
        rebuildBtn.addEventListener('click', () => startScan('rebuild'));
    }
    
    // Botón del mensaje de bienvenida
    if (firstScanBtn) {
        firstScanBtn.addEventListener('click', () => startScan('incremental'));
    }
}

// Iniciar proceso de escaneo
async function startScan(type) {
    try {
        console.log(`🔄 Iniciando escaneo ${type}...`);
        
        // Mostrar barra de progreso
        showScanProgress();
        
        // Actualizar estado
        updateScanStatus(`Iniciando escaneo ${type}...`);
        updateScanPercentage(0);
        
        // Llamar a la API correspondiente
        const endpoint = type === 'incremental' ? '/api/scan/incremental' : '/api/scan/rebuild';
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            updateScanStatus('Escaneo completado exitosamente');
            updateScanPercentage(100);
            addScanLog('✅ Escaneo completado exitosamente');
            
            // Mostrar estadísticas del resultado
            if (result.data) {
                const scanResult = result.data;
                addScanLog(`📊 Archivos procesados: ${scanResult.files_processed || 0}`);
                addScanLog(`📝 Archivos importados: ${scanResult.files_imported || 0}`);
                addScanLog(`🎯 Lecciones creadas: ${scanResult.lessons_created || 0}`);
                addScanLog(`🔄 Lecciones actualizadas: ${scanResult.lessons_updated || 0}`);
                addScanLog(`🎬 Media procesado: ${scanResult.media_processed || 0}`);
                
                if (scanResult.errors && scanResult.errors.length > 0) {
                    addScanLog(`⚠️ Errores: ${scanResult.errors.length}`);
                }
            }
            
            // Recargar estadísticas del dashboard
            if (window.dashboard && typeof window.dashboard.reloadDashboardStats === 'function') {
                window.dashboard.reloadDashboardStats();
            }
            
        } else {
            updateScanStatus('Error en el escaneo');
            addScanLog(`❌ Error: ${result.error || 'Error desconocido'}`);
        }
        
    } catch (error) {
        console.error('❌ Error en escaneo:', error);
        updateScanStatus('Error en el escaneo');
        addScanLog(`❌ Error: ${error.message}`);
    }
}

// Mostrar barra de progreso del escaneo
function showScanProgress() {
    const progressDiv = document.getElementById('scan-progress');
    if (progressDiv) {
        progressDiv.classList.remove('hidden');
    }
}

// Actualizar estado del escaneo
function updateScanStatus(status) {
    const statusSpan = document.getElementById('scan-status');
    if (statusSpan) {
        statusSpan.textContent = status;
    }
}

// Actualizar porcentaje del escaneo
function updateScanPercentage(percentage) {
    const percentageSpan = document.getElementById('scan-percentage');
    const progressBar = document.getElementById('scan-progress-bar');
    
    if (percentageSpan) {
        percentageSpan.textContent = `${percentage}%`;
    }
    
    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
    }
}

// Agregar log al escaneo
function addScanLog(message) {
    const logsDiv = document.getElementById('scan-logs');
    if (logsDiv) {
        const logEntry = document.createElement('div');
        logEntry.className = 'py-1';
        logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        logsDiv.appendChild(logEntry);
        logsDiv.scrollTop = logsDiv.scrollHeight;
    }
}
