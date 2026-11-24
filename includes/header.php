<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Control de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/tecnostock/productos/index.php">
            <i class="bi bi-cpu"></i> TecnoStock
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/productos/index.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/movimientos/index.php">Movimientos</a>
                </li>
            </ul>
            <span class="navbar-text text-white me-3">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
            </span>
            <a href="/tecnostock/logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>
<div class="container">