// Gestión de Clientes

function resetForm() {
    document.getElementById('clienteForm').reset();
    document.getElementById('clienteId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    document.getElementById('activo').checked = true;
}

function editCliente(cliente) {
    document.getElementById('clienteId').value = cliente.id;
    document.getElementById('nombre').value = cliente.nombre;
    document.getElementById('email').value = cliente.email || '';
    document.getElementById('telefono').value = cliente.telefono || '';
    document.getElementById('empresa').value = cliente.empresa || '';
    document.getElementById('rfc').value = cliente.rfc || '';
    document.getElementById('limite_credito').value = cliente.limite_credito;
    document.getElementById('direccion').value = cliente.direccion || '';
    document.getElementById('notas').value = cliente.notas || '';
    document.getElementById('premium').checked = cliente.premium == 1;
    document.getElementById('activo').checked = cliente.activo == 1;
    document.getElementById('modalTitle').textContent = 'Editar Cliente';
    
    const modal = new bootstrap.Modal(document.getElementById('clienteModal'));
    modal.show();
}

function deleteCliente(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar el cliente "${nombre}"?`)) {
        fetch('api/clientes.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cliente eliminado correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al eliminar el cliente');
            console.error(error);
        });
    }
}

// Submit form
document.getElementById('clienteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    formData.forEach((value, key) => {
        if (key === 'premium' || key === 'activo') {
            data[key] = document.getElementById(key).checked ? 1 : 0;
        } else {
            data[key] = value;
        }
    });
    
    const method = data.id ? 'PUT' : 'POST';
    
    fetch('api/clientes.php', {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al guardar el cliente');
        console.error(error);
    });
});
