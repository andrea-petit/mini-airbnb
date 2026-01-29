<?php session_start(); 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AirBnB - Login</title>
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>

        <!-- FIX: Chequear esto pq creo que hice un arroz con mango -->

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'exito'): ?>
            <p style="color:green;">Registro exitoso</p>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'datos_invalidos'): ?>
            <p style="color:red;">Correo o contraseña incorrectos</p>
        <?php endif; ?>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'no_autenticado'): ?>
            <p style="color:red;">Inicia sesión para acceder a esta página</p>
        <?php endif; ?>

        <!-- faltaria uno general que diga "error desconocido" o algo asi pero lo pongo y me sale doble pq le puse un if y cae en los dos casos-->

        <!-- check hasta aqui -->


        <form action="../actions/user_actions.php?action=login" method="POST">
            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>

            <div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            </div>

            <button type="submit">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>