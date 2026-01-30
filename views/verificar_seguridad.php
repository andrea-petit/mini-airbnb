<?php
session_start();
require_once '../config/db.php';
require_once '../controllers/user_controller.php';

$email = $_GET['email'] ?? '';
if (empty($email)) { header("Location: ../actions/user_actions.php?action=solicitar_recuperacion"); exit(); }

$userCtrl = new UserController($pdo);
$datos_seguridad = $userCtrl->obtener_pregunta_por_email($email); 

if (!$datos_seguridad) {
    header("Location: ../actions/user_actions.php?action=solicitar_recuperacion&error=email_no_encontrado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../public/css/styles.css">
    <title>Verificar Identidad - WindBnB</title>
</head>
<body>
    <div class="container">
        <div class="auth-body">
            <h3>Verificación de seguridad</h3>
            <p>Responde a la siguiente pregunta para continuar:</p>
            
            <form action="../actions/user_actions.php?action=verificar_pregunta" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="id_pregunta" value="<?php echo $datos_seguridad['id_pregunta']; ?>">
                
                <div class="form-group">
                    <label style="font-weight: bold;"><?php echo htmlspecialchars($datos_seguridad['pregunta']); ?></label>
                    <input type="text" name="respuesta" required placeholder="Tu respuesta..." autocomplete="off">
                </div>
                
                <button type="submit">Verificar</button>
            </form>
        </div>
    </div>
</body>
</html>