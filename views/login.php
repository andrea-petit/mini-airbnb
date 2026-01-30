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
    <link rel="stylesheet" href="../public/css/styles.css?v=1.0">
    <title>Iniciar Sesión - WindBnB</title>
</head>
<body>
    <div class="container">
        <div class="title_logo">
            <div class="auth-header">
                <img src="../public/img/LogoWind.png" alt="Logo de WindBnB">
                <h2>
                    <span style="color: #1C726E;">Wind</span>BnB</h2>
            </div>
        </div>
        
        <div class="auth-body">
            <!-- Mensajes de Estado -->
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'exito'): ?>
                <div class="success-msg">Registro exitoso. ¡Bienvenido!</div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    <svg viewBox="0 0 16 16" fill="currentColor" height="16" width="16" style="flex-shrink: 0;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 10.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm.8-6.6-1.6 4.8H8.8l-1.6-4.8h1.6z"></path></svg>
                    <span>
                        <?php 
                        if ($_GET['error'] == 'datos_invalidos') echo "Correo o contraseña incorrectos.";
                        elseif ($_GET['error'] == 'no_autenticado') echo "Inicia sesión para continuar.";
                        else echo "Ha ocurrido un error inesperado.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <h3>Iniciar sesión</h3>

            <form action="../actions/user_actions.php?action=login" method="POST">
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required placeholder="nombre@ejemplo.com">
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required placeholder="Tu contraseña">
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <button type="submit">Continúa</button>
            </form>

            <div class="divider">o</div>

            <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</body>
</html>