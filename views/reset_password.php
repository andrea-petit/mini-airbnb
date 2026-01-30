<?php session_start(); 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$email = $_GET['email'] ?? '';
if (empty($email)) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
    <title>Restablecer Contraseña - WindBnB</title>
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
                        if ($_GET['error'] == 'campo_vacio') echo "Por favor ingresa la nueva contraseña.";
                        else echo "Ha ocurrido un error inesperado.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <h3>Restablecer contraseña</h3>
            <p style="text-align: left; margin-bottom: 20px; color: #717171;">Introduce una nueva contraseña para <strong><?php echo htmlspecialchars($email); ?></strong></p>

            <form action="../actions/user_actions.php?action=reset_password" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" required placeholder="Mínimo 6 caracteres" minlength="6">
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <button type="submit">Actualizar contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
