/**
 * IMBOX - Almacén Empaque
 * Aplicación TypeScript Principal
 */

interface APIResponse<T> {
    success: boolean;
    data?: T;
    message?: string;
    error?: string;
}

class AlmacenApp {
    private readonly apiBaseUrl: string;

    constructor() {
        this.apiBaseUrl = '/2/api';
        this.init();
    }

    private init(): void {
        console.log('🚀 IMBOX Almacén Empaque - Inicializado');
        this.setupEventListeners();
    }

    private setupEventListeners(): void {
        document.addEventListener('DOMContentLoaded', () => {
            this.loadData();
        });
    }

    private async loadData(): Promise<void> {
        try {
            // Ejemplo de carga de datos
            console.log('📊 Cargando datos del almacén...');
        } catch (error) {
            this.handleError(error);
        }
    }

    private handleError(error: unknown): void {
        console.error('❌ Error:', error);
        if (error instanceof Error) {
            this.showNotification(error.message, 'error');
        }
    }

    private showNotification(message: string, type: 'success' | 'error' | 'info' = 'info'): void {
        console.log(`${type.toUpperCase()}: ${message}`);
        // Implementar notificaciones visuales aquí
    }

    public async fetchAPI<T>(endpoint: string, options?: RequestInit): Promise<APIResponse<T>> {
        try {
            const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options?.headers,
                },
            });

            const data = await response.json();
            return data;
        } catch (error) {
            throw new Error(`Error en la petición: ${error}`);
        }
    }
}

// Inicializar aplicación
const app = new AlmacenApp();
export default app;
