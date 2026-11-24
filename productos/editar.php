<?php
require_once '../includes/auth.php';
require_once '../config/conexion.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header("Location: index.php");
    exit;
}

$categorias = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre")->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $precio       = $_POST['precio'] ?? '';
    $stock_actual = $_POST['stock_actual'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? '';
    $estado       = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

    if ($nombre === '') $errores[] = "El nombre es obligatorio.";
    if ($id_categoria === '') $errores[] = "Debes seleccionar una categoría.";
    if (!is_numeric($precio) || $precio < 0) $errores[] = "El precio no puede ser negativo.";
    if (!is_numeric($stock_actual) || $stock_actual < 0) $errores[] = "El stock actual no puede ser negativo.";
    if (!is_numeric($stock_minimo) || $stock_minimo < 0) $errores[] = "El stock mínimo no puede ser negativo.";

    if (empty($errores)) {
        $sql = "UPDATE producto 
                SET nombre = ?, descripcion = ?, precio = ?, stock_actual = ?, stock_minimo = ?, id_categoria = ?, estado = ? 
                WHERE id_producto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $precio, $stock_actual, $stock_minimo, $id_categoria, $estado, $id]);

        header("Location: index.php?msg=" . urlencode("Producto actualizado correctamente."));
        exit;
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Producto: <?= htmlspecialchars($producto['codigo']) ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="formProducto">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Código (No editable)</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($producto['codigo']) ?>" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre del producto *</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($_POST['nombre'] ?? $producto['nombre']) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Categoría *</label>
                            <select name="id_categoria" class="form-select" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= (($_POST['id_categoria'] ?? $producto['id_categoria']) == $cat['id_categoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="1" <?= $producto['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= $producto['estado'] == 0 ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($_POST['descripcion'] ?? $producto['descripcion']) ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars($_POST['precio'] ?? $producto['precio']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Actual *</label>
                            <input type="number" min="0" name="stock_actual" class="form-control" value="<?= htmlspecialchars($_POST['stock_actual'] ?? $producto['stock_actual']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Mínimo *</label>
                            <input type="number" min="0" name="stock_minimo" class="form-control" value="<?= htmlspecialchars($_POST['stock_minimo'] ?? $producto['stock_minimo']) ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>