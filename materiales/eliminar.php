<?php

$archivo="data.json";

$id=$_GET["id"];

$data=json_decode(file_get_contents($archivo),true);

unset($data[$id]);

$data=array_values($data);

file_put_contents($archivo,json_encode($data,JSON_PRETTY_PRINT));

header("Location: listar.php");

?>