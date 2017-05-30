<?php

require_once("config.ini");

$nombre_usuario=mysql_escape_string(trim($_POST["nombre_usuario"]));
$clave=mysql_escape_string(trim($_POST["clave"]));

$consulta="select * from usuarios where nombre_usuario=\"".$nombre_usuario."\" and clave=\"".sha1($clave)."\"";
$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en SELECT</h3>");

$usuario=null;
if( ($usuario=mysql_fetch_row($resultado)) == NULL ){
	//echo "<h3>Nombre de usuario o contraseña incorrecta</h3>";
	
}
else{
	//echo "<h3>Correcto</h3>";
	$_SESSION['logueado']=true;
	$_SESSION['usuario']=$nombre_usuario;
	$_SESSION['rol']=$usuario[2];
	
	$consulta="update usuarios set ultimo_login=current_timestamp() where nombre_usuario=\"".$nombre_usuario."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Update</h3>");
}
header("Location: index.html");
//header("Location: control_configuracion.php");

?>
