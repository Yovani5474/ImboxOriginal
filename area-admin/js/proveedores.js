function resetForm() {
    document.getElementById('proveedorForm').reset();
    document.getElementById('proveedorId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Proveedor';
}

function editProveedor(proveedor) {
    document.getElementById('proveedorId').value = proveedor.id;
    document.getElementById('nombre').value = proveedor.nombre;
    document.getElementById('empresa').value = proveedor.empresa || '';
    document.getElementById('email').value = proveedor.email || '';
    document.getElementById('telefono').value = proveedor.telefono || '';
    document.getElementById('tipo').value = proveedor.tipo || '';
    document.getElementById('credito_dias').value = proveedor.credito_dias;
    document.getElementById('premium').checked = proveedor.premium == 1;
    document.getElementById('activo').checked = proveedor.activo == 1;
    document.getElementById('modalTitle').textContent = 'Editar Proveedor';
    new bootstrap.Modal(document.getElementById('proveedorModal')).show();
}

function deleteProveedor(id, nombre) {
    if (confirm(`¿Eliminar proveedor "${nombre}"?`)) {
        fetch('api/proveedores.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    }
}

document.getElementById('proveedorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = (key === 'premium' || key === 'activo') ? 
            (document.getElementById(key).checked ? 1 : 0) : value;
    });
    
    fetch('api/proveedores.php', {
        method: data.id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
