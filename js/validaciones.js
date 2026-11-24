document.addEventListener('DOMContentLoaded', () => {
    // 1. Validación en tiempo real del formulario de Productos
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', (e) => {
            const precio = parseFloat(formProducto.querySelector('[name="precio"]').value);
            const stockActual = parseInt(formProducto.querySelector('[name="stock_actual"]').value, 10);
            const stockMinimo = parseInt(formProducto.querySelector('[name="stock_minimo"]').value, 10);

            if (precio < 0 || stockActual < 0 || stockMinimo < 0) {
                e.preventDefault();
                alert('Error: El precio, el stock actual y el stock mínimo no pueden ser valores negativos.');
            }
        });
    }

    // 2. Validación en tiempo real del formulario de Movimientos
    const formMovimiento = document.getElementById('formMovimiento');
    if (formMovimiento) {
        formMovimiento.addEventListener('submit', (e) => {
            const selectProd = document.getElementById('id_producto');
            const tipo = document.getElementById('tipo').value;
            const cantidad = parseInt(document.getElementById('cantidad').value, 10);

            if (cantidad <= 0 || isNaN(cantidad)) {
                e.preventDefault();
                alert('Error: La cantidad del movimiento debe ser mayor a 0.');
                return;
            }

            if (tipo === 'SALIDA' && selectProd.value !== '') {
                const opcionSeleccionada = selectProd.options[selectProd.selectedIndex];
                const stockDisponible = parseInt(opcionSeleccionada.getAttribute('data-stock'), 10);

                if (cantidad > stockDisponible) {
                    e.preventDefault();
                    alert(`Stock insuficiente: Intentas retirar ${cantidad} unidades, pero solo hay ${stockDisponible} disponibles.`);
                }
            }
        });
    }
});