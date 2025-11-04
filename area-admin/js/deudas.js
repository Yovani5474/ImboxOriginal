function resetForm() {
    document.getElementById('deudaForm').reset();
    document.getElementById('deudaId').value = '';
    document.getElementById('modalTitle').textContent = 'Nueva Deuda';
}

function editDeuda(deuda) {
    document.getElementById('deudaId').value = deuda.id;
    document.getElementById('tipo').value = deuda.tipo;
    document.getElementById('referencia_nombre').value = deuda.referencia_nombre;
    document.getElementById('monto_total').value = deuda.monto_total;
    document.getElementById('fecha_vencimiento').value = deuda.fecha_vencimiento;
    document.getElementById('descripcion').value = deuda.descripcion || '';
    document.getElementById('modalTitle').textContent = 'Editar Deuda';
    new bootstrap.Modal(document.getElementById('deudaModal')).show();
}

function registrarPago(deuda) {
    document.getElementById('pagoDeudaId').value = deuda.id;
    document.getElementById('montoPago').value = deuda.monto_pendiente;
    document.getElementById('montoPago').max = deuda.monto_pendiente;
    document.getElementById('deudaInfo').innerHTML = `
        <strong>${deuda.referencia_nombre}</strong><br>
        Total: $${parseFloat(deuda.monto_total).toFixed(2)}<br>
        Pagado: $${parseFloat(deuda.monto_pagado).toFixed(2)}<br>
        Pendiente: $${parseFloat(deuda.monto_pendiente).toFixed(2)}
    `;
    new bootstrap.Modal(document.getElementById('pagoModal')).show();
}

function deleteDeuda(id) {
    if (confirm('¿Eliminar esta deuda?')) {
        fetch('api/deudas.php', {
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

document.getElementById('deudaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    fetch('api/deudas.php', {
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

document.getElementById('pagoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    fetch('api/pagos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
