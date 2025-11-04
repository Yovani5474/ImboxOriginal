<!-- Pantalla de Carga IMBOX -->
<div id="loading-screen">
    <div class="loading-logo">
        <i class="fas fa-box-open"></i>
    </div>
    <div class="loader"></div>
    <div class="loading-text"><?php echo $loading_message ?? 'Cargando...'; ?></div>
    <div class="loading-progress">
        <div class="loading-progress-bar"></div>
    </div>
    <div style="margin-top: 15px; color: rgba(255,255,255,0.8); font-size: 0.9em;">
        <i class="fas fa-warehouse"></i> Almacén 2 - Área de Empaque
    </div>
    <div style="margin-top: 8px; color: rgba(255,255,255,0.6); font-size: 0.85em;">
        Sistema IMBOX v1.0
    </div>
</div>

<style>
/* ================================================
   PANTALLA DE CARGA - ALMACÉN 2
   ================================================ */

#loading-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

#loading-screen.hidden {
    opacity: 0;
    visibility: hidden;
}

.loader {
    width: 80px;
    height: 80px;
    border: 8px solid rgba(255, 255, 255, 0.3);
    border-top: 8px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    margin-top: 30px;
    color: white;
    font-size: 1.5em;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    animation: fadeInOut 2s ease-in-out infinite;
}

@keyframes fadeInOut {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.loading-progress {
    width: 200px;
    height: 4px;
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    margin-top: 20px;
    overflow: hidden;
}

.loading-progress-bar {
    height: 100%;
    background: white;
    border-radius: 10px;
    animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

.loading-logo {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    animation: pulse 2s ease-in-out infinite;
}

.loading-logo i {
    font-size: 2.5em;
    color: #FF8C00;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>

<script>
// Ocultar pantalla de carga cuando la página termine de cargar
window.addEventListener('load', function() {
    setTimeout(function() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
            setTimeout(function() {
                loadingScreen.remove();
            }, 500);
        }
    }, 300);
});

// Timeout de seguridad (5 segundos máximo)
setTimeout(function() {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.classList.add('hidden');
        setTimeout(function() {
            loadingScreen.remove();
        }, 500);
    }
}, 5000);
</script>
