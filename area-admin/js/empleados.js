function resetForm() {
    document.getElementById('empleadoForm').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Empleado';
}

function editEmpleado(empleado) {
    document.getElementById('empleadoId').value = empleado.id;
    document.getElementById('nombre').value = empleado.nombre;
    document.getElementById('apellidos').value = empleado.apellidos || '';
    document.getElementById('email').value = empleado.email || '';
    document.getElementById('telefono').value = empleado.telefono || '';
    document.getElementById('puesto').value = empleado.puesto || '';
    document.getElementById('departamento').value = empleado.departamento || '';
    document.getElementById('salario').value = empleado.salario;
    document.getElementById('fecha_contratacion').value = empleado.fecha_contratacion || '';
    document.getElementById('premium').checked = empleado.premium == 1;
    document.getElementById('activo').checked = empleado.activo == 1;
    document.getElementById('modalTitle').textContent = 'Editar Empleado';
    new bootstrap.Modal(document.getElementById('empleadoModal')).show();
}

function deleteEmpleado(id, nombre) {
    if (confirm(`¿Eliminar empleado "${nombre}"?`)) {
        fetch('api/empleados.php', {
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

document.getElementById('empleadoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = (key === 'premium' || key === 'activo') ? 
            (document.getElementById(key).checked ? 1 : 0) : value;
    });
    
    fetch('api/empleados.php', {
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
