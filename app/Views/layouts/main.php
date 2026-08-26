<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars(isset($title) ? $title : 'COSMOL Reportes', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5.3 CSS Local -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons Local -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">

    <!-- Estilos Propios de la Aplicación -->
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Barra de Navegación Superior -->
        <?php require __DIR__ . '/partials/navbar.php'; ?>

        <!-- Backdrop para dispositivos móviles -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div class="app-body">
            <!-- Menú Lateral -->
            <?php require __DIR__ . '/partials/sidebar.php'; ?>

            <!-- Área de Contenido Principal -->
            <div class="app-main">
                <main class="app-content">
                    <?= isset($content) ? $content : '' ?>
                </main>

                <!-- Pie de Página -->
                <?php require __DIR__ . '/partials/footer.php'; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS Local -->
    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Script de la Aplicación -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
