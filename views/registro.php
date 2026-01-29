<?php session_start(); 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AirBnB - Registro</title>
</head>
<body>
    <div class="container">
        <h2>Crea tu cuenta</h2>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'campos_vacios'): ?>
        <p style="color:red;">Por favor completa todos los campos</p>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'email_registrado'): ?>
        <p style="color:red;">El correo electrónico ya está registrado</p>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="../actions/user_actions.php?action=registrar" method="POST">
            <div>
                <label>Nombre completo:</label>
                <input type="text" name="username" required placeholder="Juan Pérez">
            </div>

            <div>
                <label>Correo electrónico:</label>
                <input type="email" name="email" required placeholder="ejemplo@correo.com">
            </div>

            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required minlength="6">
            </div>

            <div>
                <label>¿Cuál es tu objetivo?</label>
                <select name="rol">
                    <option value="huesped">Quiero buscar alojamientos</option>
                    <option value="anfitrion">Quiero publicar mi propiedad</option>
                </select>
            </div>
            <div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            </div>

            <button type="submit">Registrarme</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>
