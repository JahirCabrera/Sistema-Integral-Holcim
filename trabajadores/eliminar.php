<?php

$archivo = "data.json";

if(!isset($_GET['id'])){
header("Location: listar.php");
exit;
}

$id = $_GET['id'];

$trabajadores = json_decode(file_get_contents($archivo), true);

if(isset($trabajadores[$id])){
unset($trabajadores[$id]);
$trabajadores = array_values($trabajadores);
}

file_put_contents($archivo, json_encode($trabajadores, JSON_PRETTY_PRINT));

header("Location: listar.php");
exit;