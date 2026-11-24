<?php
require_once '../includes/auth.php';
require_once '../config/conexion.php';

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    $stmt = $pdo->prepare("UPDATE producto SET estado = 0 WHERE id_producto = ?");
    $stmt->execute([$id]);
    header("Location: index.php?msg=" . urlencode("Producto desactivado correctamente."));
    exit;
}

header("Location: index.php");
exit;