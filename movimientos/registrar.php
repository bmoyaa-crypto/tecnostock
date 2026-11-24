<?php
require_once '../includes/auth.php';
require_once '../config/conexion.php';

// Solo productos activos pueden recibir movimientos
$productos = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre")->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? '';
    $tipo        = $_POST['tipo'] ?? '';
    $cantidad    = $_POST['cantidad'] ?? '';
    $observacion = trim($_POST['observacion'] ?? '');
    $id_usuario  = $_SESSION['id_usuario'];

    if ($id_producto === '') $errores[] = "Debes seleccionar un producto.";
    if (!in_array($tipo, ['ENTRADA', 'SALIDA'])) $errores[] = "Tipo de movimiento inválido.";
    if (!is_numeric($cantidad) || (int)$cantidad <= 0) $errores[] = "La cantidad debe ser mayor a cero.";

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            // Bloqueamos la fila del producto para leer su stock real
            $stmt = $pdo->prepare("SELECT stock_actual, nombre FROM producto WHERE id_producto = ? FOR UPDATE");
            $stmt->execute([$id_producto]);
            $prod = $stmt->fetch();

            if (!$prod) {
                throw new Exception("El producto seleccionado no existe.");
            }

            $cantidad = (int)$cantidad;
            $nuevo_stock = ($tipo === 'ENTRADA') 
                ? $prod['stock_actual'] + $cantidad 
                : $prod['stock_actual'] - $cantidad;

            // Regla de negocio: una salida no puede dejar el stock bajo cero
            if ($nuevo_stock < 0) {
                throw new Exception("Stock insuficiente para '{$prod['nombre']}'. Stock disponible: {$prod['stock_actual']}.");
            }

            // 1. Insertar movimiento
            $stmtMov = $pdo->prepare("INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario) VALUES (?, ?, ?, ?, ?)");
            $stmtMov->execute([$tipo, $cantidad, $observacion, $id_producto, $id_usuario]);

            // 2. Actualizar stock del producto automáticamente
            $stmtUpd = $pdo->prepare("UPDATE producto SET stock_actual = ? WHERE id_producto = ?");
            $stmtUpd->execute([$nuevo_stock, $id_producto]);

            $pdo->commit();
            header("Location: index.php?msg=" . urlencode("Movimiento registrado y stock actualizado correctamente."));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Registrar Entrada o Salida</h5>
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

                <form method="POST" id="formMovimiento">
                    <div class="mb-3">
                        <label class="form-label">Producto *</label>
                        <select name="id_producto" id="id_producto" class="form-select" required>
                            <option value="">-- Selecciona un producto --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>" data-stock="<?= $p['stock_actual'] ?>" <?= (($_POST['id_producto'] ?? '') == $p['id_producto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre'] . ' (Stock actual: ' . $p['stock_actual'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Movimiento *</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="ENTRADA" <?= (($_POST['tipo'] ?? '') === 'ENTRADA') ? 'selected' : '' ?>>ENTRADA (Compra / Reposición)</option>
                                <option value="SALIDA" <?= (($_POST['tipo'] ?? '') === 'SALIDA') ? 'selected' : '' ?>>SALIDA (Venta / Retiro)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" name="cantidad" id="cantidad" min="1" class="form-control" value="<?= htmlspecialchars($_POST['cantidad'] ?? '1') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <input type="text" name="observacion" class="form-control" placeholder="Ej: Compra a proveedor, venta boleta #104..." value="<?= htmlspecialchars($_POST['observacion'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Confirmar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>