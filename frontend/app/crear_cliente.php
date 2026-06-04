<?php
session_start();
if (!isset($_SESSION['token'])) { header("Location: index.php"); exit; }
require_once 'config/services.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = [];
    $username = trim($_POST['username'] ?? '');
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $celular  = trim($_POST['celular'] ?? '');

    if (empty($username)) $errores[] = "Username obligatorio";
    if (empty($nombre))   $errores[] = "Nombre obligatorio";
    if (empty($correo))   $errores[] = "Correo obligatorio";
    if (empty($celular))  $errores[] = "Celular obligatorio";

    if (empty($errores)) {
        $resp = api_request('POST', CLIENTS_SERVICE . '/clientes', [
            'username' => $username,
            'nombre'   => $nombre,
            'correo'   => $correo,
            'celular'  => $celular
        ], $_SESSION['token']);

        if ($resp['code'] === 201) {
            $_SESSION['flash_success'] = "Cliente creado exitosamente.";
            header("Location: lista_clientes.php");
            exit;
        } else {
            $errores[] = $resp['body']['detail'] ?? "Error al crear cliente";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Cliente | Novasoft</title>
    <link rel="stylesheet" href="estiloRegistro.css">
    <link rel="shortcut icon" href="img/undraw_male_avatar_323b.svg">
</head>
<body>
<header>
    <div class="contentheader">
        <img class="imagenh" src="img/data_maintenance_isometric.svg">
        <nav><ul class="menu">
            <li><a href="lista_clientes.php">← Volver</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul></nav>
    </div>
</header>

<div class="formulario">
    <form action="crear_cliente.php" method="POST">
        <h1>Nuevo Cliente</h1>

        <?php if (!empty($errores)): ?>
            <div style="color:red; background:#ffe0e0; padding:10px; margin-bottom:15px; border-radius:4px;">
                <?php foreach ($errores as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <label>Username*</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <label>Nombre completo*</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        <label>Correo electrónico*</label>
        <input type="email" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
        <label>Celular*</label>
        <input type="tel" name="celular" required value="<?= htmlspecialchars($_POST['celular'] ?? '') ?>">

        <div class="botones">
            <input type="submit" value="Crear Cliente" class="btn enviar">
            <a class="btn reg" href="lista_clientes.php">Cancelar</a>
        </div>
    </form>
</div>

<footer><div class="conteninfo"><h1>Novasoft</h1><p>Todos los derechos reservados</p></div></footer>
</body>
</html>
