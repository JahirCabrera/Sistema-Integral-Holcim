<?php

$archivo="data.json";

$id=$_GET["id"];

$materiales=json_decode(file_get_contents($archivo),true);

$material=$materiales[$id];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$materiales[$id]["nombre"]=$_POST["nombre"];
$materiales[$id]["tipo"]=$_POST["tipo"];
$materiales[$id]["unidad"]=$_POST["unidad"];
$materiales[$id]["cantidad"]=$_POST["cantidad"];
$materiales[$id]["estado"]=$_POST["estado"];
$materiales[$id]["obs"]=$_POST["obs"];

file_put_contents($archivo,json_encode($materiales,JSON_PRETTY_PRINT));

header("Location: listar.php");
exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Editar Material</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
background:#eef2f6;
font-family:'Inter',sans-serif;
margin:0;
}

.container{
max-width:900px;
margin:auto;
padding:30px;
}

.page-header{
background:#f4f7fa;
padding:20px;
border-radius:16px;
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.page-header h2{
display:flex;
gap:10px;
align-items:center;
color:#003B5C;
}

.page-header i{
color:#00D4AA;
}

.btn-volver{
background:#f1f5f9;
border:1px solid #cbd5e1;
color:#003B5C;
padding:8px 18px;
border-radius:30px;
text-decoration:none;
display:flex;
gap:6px;
align-items:center;
}

.card{
background:white;
padding:30px;
border-radius:16px;
box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

input,select{
width:100%;
padding:12px;
border:1px solid #ddd;
border-radius:8px;
}

.actions{
display:flex;
gap:10px;
margin-top:20px;
}

button{
background:#003B5C;
color:white;
border:none;
padding:12px;
border-radius:30px;
flex:1;
}

.btn-cancel{
border:1px solid #ddd;
padding:12px;
border-radius:30px;
text-align:center;
flex:1;
text-decoration:none;
color:#003B5C;
}

</style>

</head>

<body>

<div class="container">

<div class="page-header">

<h2>
<i class="fas fa-box"></i>
Editar Material
</h2>

<a href="listar.php" class="btn-volver">
<i class="fas fa-home"></i> Volver
</a>

</div>

<div class="card">

<form method="POST">

<div class="grid">

<input name="nombre" value="<?php echo $material["nombre"] ?>" required>

<input name="tipo" value="<?php echo $material["tipo"] ?>">

<input name="unidad" value="<?php echo $material["unidad"] ?>" required>

<input name="cantidad" value="<?php echo $material["cantidad"] ?>" required>

<select name="estado">

<option <?php if($material["estado"]=="Disponible") echo "selected"; ?>>Disponible</option>
<option <?php if($material["estado"]=="Bajo stock") echo "selected"; ?>>Bajo stock</option>
<option <?php if($material["estado"]=="Agotado") echo "selected"; ?>>Agotado</option>

</select>

<input name="obs" value="<?php echo $material["obs"] ?>">

</div>

<div class="actions">

<button>
<i class="fas fa-save"></i> Guardar Cambios
</button>

<a href="listar.php" class="btn-cancel">
Cancelar
</a>

</div>

</form>

</div>

</div>

</body>
</html>