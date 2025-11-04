<?php
// Lightweight dashboard that links to the new JSON-backed apps
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Almacén</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{padding:20px}</style>
</head>
<body>
  <div class="container">
    <div class="d-flex align-items-center mb-3">
      <img src="img/logo.jpg" alt="logo" style="height:42px; margin-right:12px;">
      <h1 class="h4">Dashboard - Almacén</h1>
    </div>
    <p class="lead">Accesos rápidos</p>
    <div class="list-group">
      <a class="list-group-item list-group-item-action" href="index.php">Inventario básico</a>
      <a class="list-group-item list-group-item-action" href="trabajadores_ui.php">Trabajadores</a>
      <a class="list-group-item list-group-item-action" href="api/items.php">API - Items</a>
      <a class="list-group-item list-group-item-action" href="api/trabajadores.php">API - Trabajadores</a>
    </div>
  </div>
</body>
</html>
