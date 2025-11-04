/**
 * Navegación tipo Excel para tabla de tallas
 * Permite navegación con Enter, Tab y flechas
 */

class TablaTallasExcel {
    constructor(tableSelector) {
        this.table = document.querySelector(tableSelector);
        if (!this.table) {
            console.error('Tabla no encontrada:', tableSelector);
            return;
        }
        
        this.currentCell = null;
        this.init();
    }
    
    init() {
        // Agregar event listeners a todos los inputs de la tabla
        const inputs = this.table.querySelectorAll('input[type="number"], input[type="text"]');
        
        inputs.forEach((input, index) => {
            // Enter: bajar a la siguiente fila en la misma columna
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.moveToCell('down', input);
                } else if (e.key === 'Tab' && !e.shiftKey) {
                    e.preventDefault();
                    this.moveToCell('right', input);
                } else if (e.key === 'Tab' && e.shiftKey) {
                    e.preventDefault();
                    this.moveToCell('left', input);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.moveToCell('down', input);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.moveToCell('up', input);
                } else if (e.key === 'ArrowRight' && input.selectionStart === input.value.length) {
                    e.preventDefault();
                    this.moveToCell('right', input);
                } else if (e.key === 'ArrowLeft' && input.selectionStart === 0) {
                    e.preventDefault();
                    this.moveToCell('left', input);
                }
            });
            
            // Seleccionar todo al hacer focus
            input.addEventListener('focus', (e) => {
                setTimeout(() => e.target.select(), 0);
                this.currentCell = input;
            });
            
            // Validación en tiempo real
            if (input.type === 'number') {
                input.addEventListener('input', (e) => {
                    this.validateNumber(input);
                });
            }
        });
        
        // Copiar/Pegar desde Excel
        this.enableCopyPaste();
    }
    
    moveToCell(direction, currentInput) {
        const td = currentInput.closest('td');
        const tr = currentInput.closest('tr');
        const allInputsInRow = Array.from(tr.querySelectorAll('input'));
        const currentIndex = allInputsInRow.indexOf(currentInput);
        
        let nextInput = null;
        
        switch(direction) {
            case 'right':
                nextInput = allInputsInRow[currentIndex + 1];
                break;
                
            case 'left':
                nextInput = allInputsInRow[currentIndex - 1];
                break;
                
            case 'down':
                const nextRow = tr.nextElementSibling;
                if (nextRow && !nextRow.classList.contains('total-row')) {
                    const nextRowInputs = Array.from(nextRow.querySelectorAll('input'));
                    nextInput = nextRowInputs[currentIndex];
                }
                break;
                
            case 'up':
                const prevRow = tr.previousElementSibling;
                if (prevRow && prevRow.querySelector('input')) {
                    const prevRowInputs = Array.from(prevRow.querySelectorAll('input'));
                    nextInput = prevRowInputs[currentIndex];
                }
                break;
        }
        
        if (nextInput) {
            nextInput.focus();
            nextInput.select();
        }
    }
    
    validateNumber(input) {
        const value = input.value;
        
        // Eliminar clases previas
        input.classList.remove('validation-error', 'validation-success');
        
        if (value === '') {
            return; // Vacío es válido
        }
        
        const num = parseInt(value);
        
        if (isNaN(num) || num < 0) {
            input.classList.add('validation-error');
            input.setCustomValidity('Debe ser un número válido mayor o igual a 0');
        } else {
            input.classList.add('validation-success');
            input.setCustomValidity('');
            setTimeout(() => input.classList.remove('validation-success'), 1000);
        }
    }
    
    enableCopyPaste() {
        const table = this.table;
        
        // Permitir pegar datos desde Excel
        table.addEventListener('paste', (e) => {
            e.preventDefault();
            
            const clipboardData = e.clipboardData || window.clipboardData;
            const pastedData = clipboardData.getData('Text');
            
            // Si el foco está en un input
            const activeElement = document.activeElement;
            if (activeElement.tagName === 'INPUT') {
                this.pasteCellData(pastedData, activeElement);
            }
        });
    }
    
    pasteCellData(data, startInput) {
        const rows = data.split('\n');
        const tr = startInput.closest('tr');
        const allInputsInRow = Array.from(tr.querySelectorAll('input'));
        let currentInputIndex = allInputsInRow.indexOf(startInput);
        let currentRow = tr;
        
        rows.forEach((rowData, rowIndex) => {
            const cells = rowData.split('\t');
            
            if (!currentRow) return;
            
            const rowInputs = Array.from(currentRow.querySelectorAll('input'));
            
            cells.forEach((cellValue, cellIndex) => {
                const targetInput = rowInputs[currentInputIndex + cellIndex];
                if (targetInput) {
                    targetInput.value = cellValue.trim();
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
            
            // Mover a la siguiente fila
            currentRow = currentRow.nextElementSibling;
            if (currentRow && currentRow.classList.contains('total-row')) {
                currentRow = null; // No pegar en la fila de totales
            }
        });
    }
    
    // Copiar rango de celdas
    copyRange(startInput, endInput) {
        // Implementación futura para copiar rango
        console.log('Copiar rango', startInput, endInput);
    }
}

// Auto-inicializar si existe la tabla
document.addEventListener('DOMContentLoaded', function() {
    const tablaTallas = document.querySelector('.tabla-tallas');
    if (tablaTallas) {
        window.tablaTallasExcel = new TablaTallasExcel('.tabla-tallas');
        console.log('✅ Navegación tipo Excel activada para tabla de tallas');
    }
});
