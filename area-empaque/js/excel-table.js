/**
 * ExcelTable - Componente para edición de tablas tipo Excel
 * Permite edición inline, navegación con teclado y guardado automático
 */
class ExcelTable {
    constructor(config) {
        this.tableId = config.tableId;
        this.apiEndpoint = config.apiEndpoint;
        this.columns = config.columns; // Array de {field, editable, type}
        this.primaryKey = config.primaryKey || 'id';
        this.autoSave = config.autoSave !== false;
        this.onSave = config.onSave || null;
        this.onError = config.onError || null;
        
        this.currentCell = null;
        this.originalValue = null;
        this.saveTimeout = null;
        
        this.init();
    }
    
    init() {
        this.attachEvents();
    }
    
    attachEvents() {
        const table = document.getElementById(this.tableId);
        if (!table) return;
        
        // Hacer celdas editables clickeables
        table.addEventListener('click', (e) => {
            const cell = e.target.closest('td[data-editable="true"]');
            if (cell) {
                this.editCell(cell);
            }
        });
        
        // Navegación con teclado
        table.addEventListener('keydown', (e) => {
            this.handleKeyboard(e);
        });
        
        // Perder foco
        table.addEventListener('blur', (e) => {
            if (e.target.matches('input, select, textarea')) {
                this.saveCell(e.target);
            }
        }, true);
    }
    
    editCell(cell) {
        // Si ya hay una celda editándose, guardarla primero
        if (this.currentCell && this.currentCell !== cell) {
            const input = this.currentCell.querySelector('input, select, textarea');
            if (input) this.saveCell(input);
        }
        
        const field = cell.dataset.field;
        const column = this.columns.find(c => c.field === field);
        if (!column || !column.editable) return;
        
        const currentValue = cell.textContent.trim();
        this.originalValue = currentValue;
        this.currentCell = cell;
        
        // Crear input según el tipo
        let input;
        switch(column.type) {
            case 'number':
                input = document.createElement('input');
                input.type = 'number';
                input.step = column.step || '1';
                input.min = column.min || '0';
                input.value = currentValue || '0';
                break;
            case 'select':
                input = document.createElement('select');
                input.className = 'form-select form-select-sm';
                column.options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    if (opt.value == currentValue || opt.label == currentValue) {
                        option.selected = true;
                    }
                    input.appendChild(option);
                });
                break;
            case 'date':
                input = document.createElement('input');
                input.type = 'date';
                input.value = currentValue;
                break;
            case 'email':
                input = document.createElement('input');
                input.type = 'email';
                input.value = currentValue;
                break;
            case 'tel':
                input = document.createElement('input');
                input.type = 'tel';
                input.value = currentValue;
                break;
            default:
                input = document.createElement('input');
                input.type = 'text';
                input.value = currentValue;
        }
        
        input.className = 'form-control form-control-sm';
        input.style.width = '100%';
        input.dataset.field = field;
        
        cell.innerHTML = '';
        cell.appendChild(input);
        input.focus();
        input.select();
        
        // Guardar al presionar Enter
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.saveCell(input);
                this.moveToNextCell(cell, 'down');
            } else if (e.key === 'Escape') {
                this.cancelEdit(cell);
            } else if (e.key === 'Tab') {
                e.preventDefault();
                this.saveCell(input);
                this.moveToNextCell(cell, e.shiftKey ? 'left' : 'right');
            }
        });
    }
    
    async saveCell(input) {
        if (!input || !this.currentCell) return;
        
        const cell = this.currentCell;
        const newValue = input.value;
        const field = input.dataset.field;
        
        // Si no cambió, solo restaurar
        if (newValue === this.originalValue) {
            cell.textContent = this.originalValue;
            this.currentCell = null;
            return;
        }
        
        // Obtener el ID de la fila
        const row = cell.closest('tr');
        const id = row.dataset[this.primaryKey];
        
        if (!id) {
            this.showError('No se encontró el ID del registro');
            cell.textContent = this.originalValue;
            this.currentCell = null;
            return;
        }
        
        // Mostrar indicador de guardado
        cell.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        try {
            // Enviar al servidor
            const response = await fetch(`${this.apiEndpoint}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    [field]: newValue
                })
            });
            
            if (!response.ok) {
                throw new Error('Error al guardar');
            }
            
            const result = await response.json();
            
            // Actualizar celda con el nuevo valor
            cell.textContent = newValue;
            cell.classList.add('table-success');
            setTimeout(() => cell.classList.remove('table-success'), 1500);
            
            // Callback de éxito
            if (this.onSave) {
                this.onSave(id, field, newValue, result);
            }
            
        } catch (error) {
            console.error('Error al guardar:', error);
            cell.textContent = this.originalValue;
            cell.classList.add('table-danger');
            setTimeout(() => cell.classList.remove('table-danger'), 2000);
            
            this.showError('Error al guardar los cambios');
            
            if (this.onError) {
                this.onError(error);
            }
        }
        
        this.currentCell = null;
    }
    
    cancelEdit(cell) {
        if (cell && this.originalValue !== null) {
            cell.textContent = this.originalValue;
        }
        this.currentCell = null;
        this.originalValue = null;
    }
    
    moveToNextCell(currentCell, direction) {
        const row = currentCell.closest('tr');
        const cells = Array.from(row.querySelectorAll('td[data-editable="true"]'));
        const currentIndex = cells.indexOf(currentCell);
        
        let nextCell = null;
        
        switch(direction) {
            case 'right':
                nextCell = cells[currentIndex + 1];
                break;
            case 'left':
                nextCell = cells[currentIndex - 1];
                break;
            case 'down':
                const nextRow = row.nextElementSibling;
                if (nextRow) {
                    const nextCells = Array.from(nextRow.querySelectorAll('td[data-editable="true"]'));
                    nextCell = nextCells[currentIndex];
                }
                break;
            case 'up':
                const prevRow = row.previousElementSibling;
                if (prevRow) {
                    const prevCells = Array.from(prevRow.querySelectorAll('td[data-editable="true"]'));
                    nextCell = prevCells[currentIndex];
                }
                break;
        }
        
        if (nextCell) {
            this.editCell(nextCell);
        }
    }
    
    handleKeyboard(e) {
        const cell = e.target.closest('td[data-editable="true"]');
        if (!cell || this.currentCell) return;
        
        // Si presiona Enter en una celda no editada, editarla
        if (e.key === 'Enter' && !this.currentCell) {
            e.preventDefault();
            this.editCell(cell);
        }
        
        // Navegación con flechas cuando no hay celda editándose
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            e.preventDefault();
            const direction = {
                'ArrowUp': 'up',
                'ArrowDown': 'down',
                'ArrowLeft': 'left',
                'ArrowRight': 'right'
            }[e.key];
            
            // Simular que la celda actual está siendo editada para mover
            const tempCell = this.currentCell;
            this.currentCell = cell;
            this.moveToNextCell(cell, direction);
            if (!this.currentCell) {
                this.currentCell = tempCell;
            }
        }
    }
    
    showError(message) {
        // Crear toast de error
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Éxito</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }
}

// Función para añadir nuevo registro
async function addNewRow(tableId, apiEndpoint, defaultData = {}) {
    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(defaultData)
        });
        
        if (!response.ok) {
            throw new Error('Error al crear el registro');
        }
        
        const result = await response.json();
        
        // Recargar la tabla o agregar la fila
        if (result.success) {
            showSuccessToast('Registro creado correctamente');
            setTimeout(() => location.reload(), 1000);
        }
        
    } catch (error) {
        console.error('Error al crear:', error);
        showErrorToast('Error al crear el registro: ' + error.message);
    }
}

function showSuccessToast(message) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Éxito</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}

function showErrorToast(message) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
