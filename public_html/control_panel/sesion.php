<?php

require_once("config.ini");

//Verificar Sesion
$rol=2;
if($_SESSION['rol']<$rol){
	echo "<form name=form_login action=login.php method=post>";
	echo "<table cellSpacing=0 cellPadding=0 width=600 align=center border=0>";
	echo "<tr>";
	echo "<td width=100 align=center>";
	
	echo "Usuario";
	echo "</td>";
	echo "<td width=150 align=center>";
	
	echo "<input size=15 type=text name=nombre_usuario>";
	echo "</td>";
	echo "<td width=100 align=center>";
	
	echo "Contrase&ntilde;a";
	echo "</td>";
	echo "<td width=150 align=center>";
	
	echo "<input size=15 type=password name=clave>";
	echo "</td>";
	echo "<td width=100 align=center>";
	
	echo "<input value=Ingresar name=ingresar type=submit>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
	die();
}

//Cerrar Sesion
$salir=$_GET["salir"];
if(!ctype_digit($salir))
	$salir=0;
if($salir==1){
	session_destroy();
	header("Location: ?");
}

?>
