<?php
require_once '../includes/auth.php';
require_once '../config/conexion.php';
require_once '../includes/header.php';

$busqueda = trim($_GET['buscar'] ?? '');
$filtro_estado = $_GET['estado'] ?? '1'; // Por defecto muestra activos

$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria 
        WHERE 1=1";
$params = [];

if ($filtro_estado !== 'todos') {
    $sql .= " AND p.estado = ?";
    $params[] = $filtro_estado;
}

if ($busqueda !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR c.nombre LIKE ?)";
    $like = "%$busqueda%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.id_producto DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-box-seam"></i> Catálogo de Productos</h2>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Producto
    </a>
</div>

<!-- Buscador y filtro -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-6">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por código, nombre o categoría..." value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <div class="col-md-3">
        <select name="estado" class="form-select">
            <option value="1" <?= $filtro_estado === '1' ? 'selected' : '' ?>>Solo Activos</option>
            <option value="0" <?= $filtro_estado === '0' ? 'selected' : '' ?>>Desactivados</option>
            <option value="todos" <?= $filtro_estado === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-dark w-100">Buscar</button>
        <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

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
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock Actual</th>
                <th>Stock Mín.</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron productos.</td></tr>
            <?php else: ?>
                <?php foreach ($productos as $p): 
                    $bajo_stock = ($p['stock_actual'] < $p['stock_minimo'] && $p['estado'] == 1);
                ?>
                    <tr class="<?= $bajo_stock ? 'table-warning' : '' ?>">
                        <td class="fw-bold"><?= htmlspecialchars($p['codigo']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($p['nombre']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></small>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['categoria_nombre']) ?></span></td>
                        <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                        <td>
                            <span class="fw-bold <?= $bajo_stock ? 'text-danger' : '' ?>">
                                <?= $p['stock_actual'] ?>
                            </span>
                            <?php if ($bajo_stock): ?>
                                <span class="badge bg-danger ms-1" title="Stock bajo el mínimo">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Bajo stock
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= $p['stock_minimo'] ?></td>
                        <td>
                            <?php if ($p['estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Desactivado</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($p['estado'] == 1): ?>
                                <a href="desactivar.php?id=<?= $p['id_producto'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Desactivar"
                                   onclick="return confirm('¿Seguro que deseas desactivar este producto?');">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>