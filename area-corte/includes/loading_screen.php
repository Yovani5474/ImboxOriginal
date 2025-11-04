<!-- Pantalla de Carga IMBOX -->
<div id="loading-screen">
    <div class="loading-logo">
        <i class="fas fa-box-open"></i>
    </div>
    <div class="loader"></div>
    <div class="loading-text">Cargando...</div>
    <div class="loading-progress">
        <div class="loading-bar"></div>
    </div>
    <div class="loading-details">
        <p>Sistema de Gestión IMBOX</p>
        <p>Área de Corte</p>
    </div>
</div>

<style>
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
    
    .loading-logo {
        font-size: 4rem;
        color: white;
        animation: logoFloat 2s ease-in-out infinite;
        margin-bottom: 30px;
    }
    
    @keyframes logoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
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
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
    
    .loading-progress {
        width: 300px;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        margin-top: 20px;
        overflow: hidden;
    }
    
    .loading-bar {
        width: 0%;
        height: 100%;
        background: white;
        border-radius: 10px;
        animation: loadingBar 2s ease-in-out infinite;
    }
    
    @keyframes loadingBar {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }
    
    .loading-details {
        margin-top: 20px;
        text-align: center;
    }
    
    .loading-details p {
        color: rgba(255, 255, 255, 0.8);
        margin: 5px 0;
        font-size: 0.9em;
    }
</style>
