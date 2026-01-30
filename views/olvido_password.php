<?php session_start(); 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
    <title>Recuperar Contraseña - WindBnB</title>
</head>
<body>
    <div class="container">
        <div class="title_logo">
            <div class="auth-header">
                <img src="../public/img/LogoWind.png" alt="Logo de WindBnB">
                <h2><span class="brand-highlight">Wind</span>BnB</h2>
            </div>
        </div>
        
        <div class="auth-body">
            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    <svg viewBox="0 0 16 16" fill="currentColor" height="16" width="16" class="error-icon"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 10.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm.8-6.6-1.6 4.8H8.8l-1.6-4.8h1.6z"></path></svg>
                    <span>
                        <?php 
                        if ($_GET['error'] == 'campo_vacio') echo "Por favor ingresa tu correo electrónico.";
                        elseif ($_GET['error'] == 'email_no_encontrado') echo "El correo no está registrado.";
                        else echo "Ha ocurrido un error.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <h3>Recuperar contraseña</h3>
            <p style="text-align: left; margin-bottom: 20px; color: #717171;">Ingresa el correo electrónico asociado a tu cuenta.</p>

            <form action="../actions/user_actions.php?action=solicitar_recuperacion" method="POST">
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required placeholder="nombre@ejemplo.com">
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <button type="submit">Continuar</button>
            </form>

            <div class="divider">o</div>
            <p><a href="login.php">Volver al inicio de sesión</a></p>
        </div>
    </div>
</body>
</html>
