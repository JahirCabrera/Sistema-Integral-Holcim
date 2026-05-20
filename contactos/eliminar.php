<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$archivo = __DIR__ . "/data.json";

if (!isset($_GET['id'])) {
    header("Location: listar.php?msg=error");
    exit;
}

$id = intval($_GET['id']);

if (!file_exists($archivo)) {
    header("Location: listar.php?msg=error");
    exit;
}

$data = json_decode(file_get_contents($archivo), true);

if (!is_array($data)) {
    header("Location: listar.php?msg=error");
    exit;
}

if (isset($data[$id])) {
    array_splice($data, $id, 1);
    $data = array_values($data);
    file_put_contents($archivo, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: listar.php?msg=deleted");
} else {
    header("Location: listar.php?msg=error");
}
exit;
?>