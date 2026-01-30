<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/user_controller.php';

$controller = new UserController($pdo);

// Obtener id desde GET o desde sesión (flujo de registro guarda id en sesión)
$id_usuario = null;
if (isset($_GET['id_usuario'])) $id_usuario = (int)$_GET['id_usuario'];
elseif (isset($_SESSION['id_usuario'])) $id_usuario = (int)$_SESSION['id_usuario'];

if (!$id_usuario) {
    header("Location: registro.php");
    exit();
}

// Si ya configuró pregunta, evitar duplicados
if ($controller->tiene_pregunta_configurada($id_usuario)) {
    header("Location: ../views/login.php?msg=seguridad_configurada");
    exit();
}

$preguntas = $controller->cargar_formulario_seguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../public/css/styles.css">
    <title>Seguridad - AirBnB</title>
</head>
<body>
    <div class="container">
        <div class="auth-header-reg">
            <h2>Casi terminamos...</h2>
            <p>Configura una pregunta de seguridad para proteger tu cuenta.</p>
        </div>

        <div class="auth-body">
            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    <?php
                        if ($_GET['error'] == 'campos_vacios') echo "Por favor completa todos los campos.";
                        elseif ($_GET['error'] == 'error_db') echo "Ocurrió un error al guardar. Intenta de nuevo.";
                    ?>
                </div>
            <?php endif; ?>

            <form action="../actions/user_actions.php?action=guardar_seguridad" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label>Selecciona una pregunta</label>
                    <select name="id_pregunta" required>
                        <option value="">-- Elige una opción --</option>
                        <?php foreach($preguntas as $p): ?>
                            <option value="<?php echo $p['id_pregunta']; ?>">
                                <?php echo htmlspecialchars($p['pregunta']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tu respuesta</label>
                    <input type="text" name="respuesta" required placeholder="Escribe tu respuesta aquí..." autocomplete="off">
                </div>

                <button type="submit">Finalizar Registro</button>
            </form>
        </div>
    </div>
</body>
</html>