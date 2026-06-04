<?php
session_start();
if (!isset($_SESSION['token'])) { header("Location: index.php"); exit; }
require_once 'config/services.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: lista_clientes.php"); exit; }

$resp = api_request('DELETE', CLIENTS_SERVICE . "/clientes/$id", [], $_SESSION['token']);

if ($resp['code'] === 200) {
    $_SESSION['flash_success'] = "Cliente eliminado.";
} else {
    $_SESSION['flash_error'] = $resp['body']['detail'] ?? "Error al eliminar";
}

header("Location: lista_clientes.php");
exit;
