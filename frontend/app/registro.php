<?php
session_start();
header('X-Pod-Name: ' . gethostname());
require_once 'config/services.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $celular  = trim($_POST['cel'] ?? '');
    $contra   = $_POST['contra'] ?? '';
    $errores  = [];

    if (empty($nombre))   $errores[] = "Nombre obligatorio";
    if (empty($correo))   $errores[] = "Correo obligatorio";
    if (empty($username)) $errores[] = "Username obligatorio";
    if (empty($celular))  $errores[] = "Celular obligatorio";
    if (strlen($contra) < 6) $errores[] = "Contraseña mínimo 6 caracteres";

    if (empty($errores)) {
        $resp = api_request('POST', AUTH_SERVICE . '/auth/register', [
            'username'   => $username,
            'nombre'     => $nombre,
            'correo'     => $correo,
            'contrasena' => $contra,
            'celular'    => $celular
        ]);

        if ($resp['code'] === 201) {
            $_SESSION['register_success'] = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: index.php");
            exit;
        } else {
            $errores[] = $resp['body']['detail'] ?? "Error al registrar";
        }
    }
    $_SESSION['register_errors'] = $errores;
    header("Location: registro.php");
    exit;
}

$errores = $_SESSION['register_errors'] ?? [];
unset($_SESSION['register_errors']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Novasoft | Registro</title>
    <link rel="stylesheet" href="estiloRegistro.css">
    <link rel="shortcut icon" href="img/undraw_male_avatar_323b.svg">
</head>
<body>
<header>
    <div class="contentheader">
        <img class="imagenh" src="img/data_maintenance_isometric.svg">
        <nav><ul class="menu">
            <li><a href="index.php">Inicio</a></li>
        </ul></nav>
    </div>
    <div class="wave">
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#3A4B90" fill-opacity="1" d="M0,288L48,288C96,288,288,256C384,224,480,160,576,133.3C672,107,768,117,864,138.7C960,160,1056,192,1152,170.7C1248,149,1344,75,1392,37.3L1440,0L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
        </svg>
    </div>
</header>

<div class="formulario">
    <img src="img/undraw_sign_in_e6hj.svg" alt="Registro">
    <form action="registro.php" method="POST">
        <h1>Crear cuenta</h1>

        <?php if (!empty($errores)): ?>
            <div style="color:red; background:#ffe0e0; padding:10px; margin-bottom:15px; border-radius:4px;">
                <?php foreach ($errores as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <label>Nombre completo*</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        <label>Correo electrónico*</label>
        <input type="email" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
        <label>Username*</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <label>Celular*</label>
        <input type="tel" name="cel" required value="<?= htmlspecialchars($_POST['cel'] ?? '') ?>">
        <label>Contraseña* (mín. 6 caracteres)</label>
        <input type="password" name="contra" required>

        <div class="botones">
            <input type="submit" value="Registrar" class="btn enviar">
            <a class="btn reg" href="index.php">Volver</a>
        </div>
    </form>
</div>

<footer>
    <div class="conteninfo"><h1>Novasoft</h1><p>Todos los derechos reservados</p></div>
</footer>
</body>
</html>
