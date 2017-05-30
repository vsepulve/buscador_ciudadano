<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Usuarios");
echo "<body>";
html_menu_superior(4);
html_inicio_mensajes();


$accion=addslashes(trim($_GET["accion"]));

$nombre_usuario=trim($_POST["nombre_usuario"]);
$clave=trim($_POST["clave"]);
$clave_repetida=trim($_POST["clave_repetida"]);

$usuario_eliminar=addslashes(trim($_GET["usuario_eliminar"]));

if($accion=="ingresar_usuario" && $nombre_usuario && $clave && $clave_repetida){

	$consulta="select * from usuarios where nombre_usuario=\"".$nombre_usuario."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Select</h3>");
	if($usuario=mysql_fetch_row($resultado)){
		echo "El usuario ya existe<br>";
	}
	else if(!(strcmp($clave, $clave_repetida)===0)){
		echo "Las claves ingresadas son distintas<br>";
	}
	else{
		$consulta="insert into usuarios (nombre_usuario, clave, rol) values (\"".$nombre_usuario."\", \"".sha1($clave)."\", \"2\") ";
		//$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Insert</h3>");
		$resultado=mysql_query($consulta, $db) or die(mysql_error());
		echo "Usuario \"$nombre_usuario\" Ingresado<br>";
	}
	
}
else if($accion=="eliminar_usuario" && $usuario_eliminar){
	$consulta="delete from usuarios where nombre_usuario=\"".$usuario_eliminar."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Delete</h3>");
	echo "Usuario \"$usuario_eliminar\" eliminado<br>";
}


$usuarios=obtener_usuarios($db);

mysql_close($db);
html_fin_mensajes();


$titulo="Administracion de Usuarios";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Listar Semillas";
$elemento["ruta"]="listar_semillas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Verificar Semillas";
$elemento["ruta"]="verificar_semillas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);


echo "<form name=form_login action=?accion=ingresar_usuario method=post>";
echo "<table cellspacing=0 cellpadding=0 border=0 width=400>";

echo "<tr>";
echo "<td colspan=2 align=center height=30 valign=top>";
echo "Ingrese nuevo usuario";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td width=200 align=right>";
echo "Nombre de Usuario&nbsp;:&nbsp;";
echo "</td>";
echo "<td width=200 align=center>";
echo "<input size=15 type=text name=nombre_usuario>";
echo "</td>";
echo "<tr>";

echo "<tr>";
echo "<td width=200 align=right>";
echo "Contrase&ntilde;a&nbsp;:&nbsp;";
echo "</td>";
echo "<td width=200 align=center>";
echo "<input size=15 type=password name=clave>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td width=200 align=right>";
echo "Repita Contrase&ntilde;a&nbsp;:&nbsp;";
echo "</td>";
echo "<td width=200 align=center>";
echo "<input size=15 type=password name=clave_repetida>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan=2 align=center>";
echo "<input value=Ingresar name=ingresar type=submit>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";

echo "<br>";


echo "<table cellSpacing=0 cellPadding=0 border=1 width=600>";
echo "<tr>";
echo "<th width=150>Usuario</th>";
echo "<th width=200>Ultimo Login</th>";
echo "<th width=250>Acciones</th>";
echo "</tr>";

for($i=0; $i<count($usuarios); $i++){
	echo "<tr>";
	
	echo "<td>".$usuarios[$i]["nombre_usuario"]."</td>";
	if($usuarios[$i]["ultimo_login"]==null)
		echo "<td>Nunca</td>";
	else
		echo "<td>".$usuarios[$i]["ultimo_login"]."</td>";
	
	echo "<td align=center>";
	echo "<a href=?accion=eliminar_usuario&usuario_eliminar=".$usuarios[$i]["nombre_usuario"].">Eliminar</a>";
	echo "</td>";
	echo "</tr>";
}
echo "</table>";

echo "</body>";

echo "</html>";

?>
