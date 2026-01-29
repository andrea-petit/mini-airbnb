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
    <title>Regístrate - AirBnB</title>
</head>
<body>
    <div class="container">
        <div class="auth-header-reg">
            <h2>Crea tu cuenta</h2>
        </div>
        
        <div class="auth-body">
            
            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    <svg viewBox="0 0 16 16" fill="currentColor" height="16" width="16" style="flex-shrink: 0;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 10.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm.8-6.6-1.6 4.8H8.8l-1.6-4.8h1.6z"></path></svg>
                    <span>
                        <?php 
                        if ($_GET['error'] == 'campos_vacios') echo "Por favor completa todos los campos.";
                        elseif ($_GET['error'] == 'email_registrado') echo "El correo electrónico ya está registrado.";
                        else echo htmlspecialchars($_GET['error']);
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <form action="../actions/user_actions.php?action=registrar" method="POST">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="username" required placeholder="Juan Pérez">
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required placeholder="ejemplo@correo.com">
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label>¿Cuál es tu objetivo?</label>
                    <select name="rol">
                        <option value="huesped">Quiero buscar alojamientos</option>
                        <option value="anfitrion">Quiero publicar mi propiedad</option>
                    </select>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <p style="font-size: 12px; color: #717171; text-align: left; margin-bottom: 16px;">
                    Al seleccionar <strong>Registrarme</strong>, aceptas los <a style="font-size: 12px; color: #717171; text-align: left; margin-bottom: 16px;">Términos de servicio de WindBnB.</a>
                </p>

                <button type="submit">Registrarme</button>
            </form>

            <div class="divider">o</div>

            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
