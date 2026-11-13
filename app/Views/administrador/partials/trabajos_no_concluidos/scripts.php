<?php
/**
 * Componente: Scripts de Filtrado por Servicio para Contingencias
 * Archivo: app/Views/administrador/partials/contingencias_scripts.php
 * 
 * Alterna dinámicamente la visibilidad de filas y tarjetas según el botón de servicio activo.
 */
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filtrado interactivo en la vista actual por categoría
    var botonesFiltro = document.querySelectorAll('#filtroTipoContingencias .filtro-contingencia-btn');
    var items = document.querySelectorAll('.fila-contingencia-item');

    botonesFiltro.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var objetivo = this.getAttribute('data-target');

            botonesFiltro.forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            items.forEach(function (item) {
                var cat = item.getAttribute('data-categoria');
                if (objetivo === 'todos' || cat === objetivo) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>
