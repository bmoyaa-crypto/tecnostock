<?php
require_once '../includes/auth.php';
require_once '../config/conexion.php';
require_once '../includes/header.php';

$sql = "SELECT m.*, p.codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC";
$movimientos = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-arrow-left-right"></i> Historial de Movimientos</h2>
    <a href="registrar.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Registrar Movimiento
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="table-responsive bg-white shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Fecha y Hora</th>
                <th>Tipo</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Observación</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Aún no hay movimientos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($m['fecha_hora'])) ?></td>
                        <td>
                            <?php if ($m['tipo'] === 'ENTRADA'): ?>
                                <span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> ENTRADA</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-circle"></i> SALIDA</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['codigo']) ?></strong> - <?= htmlspecialchars($m['producto_nombre']) ?>
                        </td>
                        <td class="fw-bold"><?= $m['cantidad'] ?></td>
                        <td><?= htmlspecialchars($m['observacion'] ?: '-') ?></td>
                        <td><i class="bi bi-person"></i> <?= htmlspecialchars($m['usuario_nombre']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>