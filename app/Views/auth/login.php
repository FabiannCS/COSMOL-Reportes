<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'COSMOL Reportes' ?></title>
    <!-- Favicon / Logo -->
    <link rel="icon" type="image/jpeg" href="/assets/img/logo.jpeg">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif; background: #f1f5f9; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .login-card { background: #ffffff; width: 100%; max-width: 400px; padding: 2.5rem; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); }
        .logo-title { font-size: 1.5rem; font-weight: 700; color: #0284c7; margin-bottom: 0.5rem; text-align: center; }
        .subtitle { font-size: 0.875rem; color: #64748b; text-align: center; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: #0284c7; }
        .btn-submit { width: 100%; background: #0284c7; color: white; border: none; padding: 0.85rem; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #0369a1; }
        .footer-text { margin-top: 1.5rem; font-size: 0.75rem; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 1rem;">
            <img src="/assets/img/logo.jpeg" alt="COSMOL Logo" style="max-width: 220px; height: auto; display: block; margin: 0 auto;">
        </div>
        <p class="subtitle">Gestión de consultas Chatbot</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.25rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ingresa tu usuario" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Ingresar al Sistema</button>
        </form>

        <p class="footer-text">COSMOL RL.</p>
    </div>
</body>
</html>

