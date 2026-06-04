<?php
session_start();
header('X-Pod-Name: ' . gethostname());
require_once 'config/services.php';

// Si ya está logueado redirigir al perfil
if (isset($_SESSION['token'])) {
    header("Location: perfil.php");
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['nombre'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $errores  = [];

    if (empty($username)) $errores[] = "Usuario requerido";
    if (empty($password)) $errores[] = "Contraseña requerida";

    if (empty($errores)) {
        $resp = api_request('POST', AUTH_SERVICE . '/auth/login', [
            'username' => $username,
            'password' => $password
        ]);

        if ($resp['code'] === 200) {
            $_SESSION['token']    = $resp['body']['token'];
            $_SESSION['user_id']  = $resp['body']['user']['id'];
            $_SESSION['username'] = $resp['body']['user']['username'];
            $_SESSION['nombre']   = $resp['body']['user']['nombre'];
            $_SESSION['correo']   = $resp['body']['user']['correo'];
            header("Location: perfil.php");
            exit;
        } else {
            $errores[] = $resp['body']['detail'] ?? "Credenciales incorrectas";
        }
    }
    $_SESSION['login_errors'] = $errores;
    header("Location: index.php");
    exit;
}

$errores = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Novasoft | Iniciar sesión</title>
    <link rel="stylesheet" href="estiloIndex.css">
    <link rel="shortcut icon" href="img/undraw_male_avatar_323b.svg">
</head>
<body>
<header>
    <div class="contentheader">
        <img class="imagenh" src="img/data_maintenance_isometric.svg">
        <nav><ul class="menu">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="registro.php">Registrarse</a></li>
        </ul></nav>
    </div>
    <div class="wave">
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#3A4B90" fill-opacity="1" d="M0,288L48,288C96,288,192,288,288,256C384,224,480,160,576,133.3C672,107,768,117,864,138.7C960,160,1056,192,1152,170.7C1248,149,1344,75,1392,37.3L1440,0L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
        </svg>
    </div>
</header>

<div class="formulario">
    <?php if (!empty($errores)): ?>
        <div style="color:red; background:#ffe0e0; padding:10px; border-radius:4px; margin-bottom:10px;">
            <?php foreach ($errores as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['register_success'])): ?>
        <div style="color:green; background:#e0ffe0; padding:10px; border-radius:4px; margin-bottom:10px;">
            <?= htmlspecialchars($_SESSION['register_success']); unset($_SESSION['register_success']); ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <h1>Iniciar sesión</h1>
        <img src="img/undraw_male_avatar_323b.svg">
        <label>UserName</label>
        <input name="nombre" type="text" required>
        <label>Contraseña</label>
        <input name="password" type="password" required>
        <div class="botones">
            <input type="submit" value="Ingresar" class="btn enviar">
            <a class="btn reg" href="registro.php">Registrar</a>
        </div>
    </form>
</div>

<footer>
    <div class="conteninfo">
        <h1>Novasoft</h1>
        <p>Página diseñada por Novasoft | Todos los derechos reservados</p>
    </div>
</footer>
</body>
</html>
