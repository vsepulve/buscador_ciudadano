<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Historial");
echo "<body>";
html_menu_superior(3);
html_inicio_mensajes();

$debug=false;
$estructura=obtener_estructura_completa($db);
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["id"].": ".$grupos[$i]["nombre"]);
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		print_debug("--> Dominio ".$dominios[$j]["id"].": ".$dominios[$j]["nombre"]);
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			print_debug("---- Semilla ".$semillas[$k]["id"].": ".$semillas[$k]["url"]);
		}
	}
}//for... cada grupo

$accion=addslashes(trim($_GET["accion"]));

$id_grupo=$_GET["id_grupo"];
if(!ctype_digit($id_grupo))
	$id_grupo=0;

$id_dominio=$_GET["id_dominio"];
if(!ctype_digit($id_dominio))
	$id_dominio=0;

$id_semilla=$_GET["id_semilla"];
if(!ctype_digit($id_semilla))
	$id_semilla=0;

$debug=true;
if($accion=="borrar_grupo"){
	if(!existe_grupo_opcional($db, $id_grupo, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		echo "Borrando en forma permanente el grupo \"".$grupo["nombre"]."\"<br>";
		eliminar_grupo_verdadero($db, $id_grupo);
		for($i=0; $i<count($dominios); $i++){
			eliminar_dominio_verdadero($db, $dominios[$i]["id"]);
		}
		for($i=0; $i<count($semillas); $i++){
			eliminar_semilla_verdadero($db, $semillas[$i]["id"]);
		}
		
	}
}
else if($accion=="borrar_dominio"){
	if(!existe_dominio_opcional($db, $id_dominio, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		echo "Borrando en forma permanente el dominio \"".$dominio["nombre"]."\"<br>";
		eliminar_dominio_verdadero($db, $id_dominio);
		for($i=0; $i<count($semillas); $i++){
			eliminar_semilla_verdadero($db, $semillas[$i]["id"]);
		}
		
	}
}
else if($accion=="borrar_semilla"){
	if(!existe_semilla_opcional($db, $id_semilla, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
		echo "Borrando en forma permanente la semilla \"".$semilla["url"]."\"<br>";
		eliminar_semilla_verdadero($db, $id_semilla);
		
	}
}
else if($accion=="recuperar_grupo"){
	if(!existe_grupo_opcional($db, $id_grupo, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "Recuperando grupo \"".$grupo["nombre"]."\"<br>";
		recuperar_grupo($db, $id_grupo);
		editar_grupo($db, $id_grupo, $grupo["nombre"], obtener_puerto_random($db));
		echo "Recuperando dominios y sus semillas<br>";
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		for($i=0; $i<count($dominios); $i++){
			if($dominios[$i]["borrado"]){
				recuperar_dominio($db, $dominios[$i]["id"]);
				editar_dominio($db, $dominios[$i]["id"], $dominios[$i]["nombre"], $id_grupo, obtener_puerto_random($db));
			}
			$semillas=filtrar_semillas_dominio($estructura, $dominios[$i]["id"]);
			for($j=0; $j<count($semillas); $j++){
				if($semillas[$j]["borrado"]){
					recuperar_semilla($db, $semillas[$j]["id"]);
				}
			}
		}
		
	}
}
else if($accion=="recuperar_dominio"){
	if(!existe_dominio_opcional($db, $id_dominio, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo_dominio($estructura, $id_dominio);
		if(!existe_grupo($db, $grupo["id"])){
			echo "No hay grupo para recuperar el dominio<br>";
		}
		else{
			$dominio=filtrar_dominio($estructura, $id_dominio);
			echo "Recuperando dominio \"".$dominio["nombre"]."\"<br>";
			recuperar_dominio($db, $id_dominio);
			editar_dominio($db, $id_dominio, $dominio["nombre"], $dominio["id_grupo"], obtener_puerto_random($db));
			$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
			echo count($semillas)." semillas en total<br>";
			for($j=0; $j<count($semillas); $j++){
				if($semillas[$j]["borrado"]){
					recuperar_semilla($db, $semillas[$j]["id"]);
				}
			}
		}
		
	}
}
else if($accion=="recuperar_semilla"){
	if(!existe_semilla_opcional($db, $id_semilla, 1)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$dominio=filtrar_dominio_semilla($estructura, $id_semilla);
		if(!existe_dominio($db, $dominio["id"])){
			echo "No hay dominio para recuperar la semilla<br>";
		}
		else{
			$semilla=filtrar_semilla($estructura, $id_semilla);
			echo "Recuperando semilla \"".$semilla["url"]."\"<br>";
			recuperar_semilla($db, $id_semilla);
		}
		
	}
}
$debug=false;

$estructura=obtener_estructura_completa($db);

mysql_close($db);
html_fin_mensajes();







$titulo="Historial";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Config.";
$elemento["ruta"]="control_configuracion.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

$elemento["nombre"]="Respaldos";
$elemento["ruta"]="control_respaldos.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:$ancho_borde_izq;\">&nbsp;</th>
	<th style=\"width:$ancho_borde_izq;\">&nbsp;</th>
	<th style=\"width:250;\">Nombre/URL</th>
	<th style=\"width:465;\">Acciones</th>
	<th style=\"width:$ancho_borde_der;\">&nbsp;</th>
	<th style=\"width:$ancho_borde_der;\">&nbsp;</th>
</tr>";
	
$color_usado=0;
$color=array("rgb(200, 200, 200)", "rgb(230, 230, 230)");
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$color_usado=($color_usado+1)%2;
	
	echo "<tr height=$separacion_grupos><td colspan=6></td></tr>";
	
	echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
	
	echo "<td class=borde_grupo_01 width=$ancho_borde_izq>&nbsp;</td>";
	echo "<td class=borde_grupo_02 width=$ancho_borde_izq>&nbsp;</td>";
	
	echo "<td class=borde_grupo_02 width=250>";
	if(strlen($grupos[$i]["nombre"])>25)
		$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
	else
		$texto=$grupos[$i]["nombre"];
	echo "".$texto."";
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center width=465>";
	echo "<table cellpadding=0 cellspacing=0 border=0 width=400>";
	echo "<tr>";
	if($grupos[$i]["borrado"]){
		echo "<td align=center>";
		echo "<a href=?accion=recuperar_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Recuperar Grupo";
		echo "<img border=0 src=$estilos/recuperar_grupo.png height=$alto_recuperar width=$ancho_recuperar></img>";
		echo "</a>";
		echo "</td>";
		echo "<td align=center>";
		echo "<a href=?accion=borrar_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Borrar Grupo";
		echo "<img border=0 src=$estilos/borrar_grupo.png height=$alto_borrar width=$ancho_borrar></img>";
		echo "</a>";
		echo "</td>";
	}
	else{
		echo "<td>&nbsp;</td>";
	}
	echo "</tr>";
	echo "</table>";
	echo "</td>";
	
	echo "<td class=borde_grupo_02 width=$ancho_borde_der>&nbsp;</td>";
	echo "<td class=borde_grupo_03 width=$ancho_borde_der>&nbsp;</td>";
	
	echo "</tr>";
	
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		
		echo "<tr height=$separacion_dominios>";
		echo "<td width=$ancho_borde_izq><img src=$estilos/extension_03.png height=$separacion_dominios width=$ancho_borde_izq></img></td>";
		echo "<td colspan=5></td>";
		echo "</tr>";
		
		echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
		
		echo "<td align=left width=$ancho_borde_izq>";
		if($j==count($dominios)-1){
			echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		else{
			echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_01 width=$ancho_borde_izq>&nbsp;</td>";
		
		echo "<td class=borde_dominio_02 width=250>";
		if(strlen($dominios[$j]["nombre"])>25)
			$texto=substr($dominios[$j]["nombre"], 0, 25)."...";
		else
			$texto=$dominios[$j]["nombre"];
		echo "".$texto."";
		echo "</td>";
		
		echo "<td class=borde_dominio_02 align=center width=465>";
		
		echo "<table cellpadding=0 cellspacing=0 border=0 width=400>";
		echo "<tr>";
		if($dominios[$j]["borrado"] && !$grupos[$i]["borrado"]){
			echo "<td align=center>";
			echo "<a href=?accion=recuperar_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Recuperar Dominio";
			echo "<img border=0 src=$estilos/recuperar_dominio.png height=$alto_recuperar width=$ancho_recuperar></img>";
			echo "</a>";
			echo "</td>";
			echo "<td align=center>";
			echo "<a href=?accion=borrar_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Borrar Dominio";
			echo "<img border=0 src=$estilos/borrar_dominio.png height=$alto_borrar width=$ancho_borrar></img>";
			echo "</a>";
			echo "</td>";
		}
		else{
			echo "&nbsp;";
		}
		echo "</tr>";
		echo "</table>";
		echo "</td>";
		
		echo "<td class=borde_dominio_03 width=$ancho_borde_der>&nbsp;</td>";
		echo "<td width=$ancho_borde_der style=\"background-color: white;\">&nbsp;</td>";
		echo "</tr>";
		
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			
			echo "<tr style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
			
			echo "<td align=left width=$ancho_borde_izq>";
			if($j==count($dominios)-1){
				echo "<img src=$estilos/extension_04.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			echo "</td>";
			
			echo "<td align=left width=$ancho_borde_izq>";
			if($k==count($semillas)-1){
				echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			echo "</td>";
			
			echo "<td width=250>";
			//El substring es para direcciones muy largas que pueden verse raros
			if(strlen($semillas[$k]["url"])>25){
				$texto=substr($semillas[$k]["url"], 0, 25)."...";
			}
			else{
				$texto=$semillas[$k]["url"];
			}
			echo "".$texto."";
			echo "</td>";
			
			echo "<td align=center width=465>";
			echo "<table cellpadding=0 cellspacing=0 border=0 width=400>";
			echo "<tr>";
			if($semillas[$k]["borrado"] && !$dominios[$j]["borrado"]){
				echo "<td align=center>";
				echo "<a href=?accion=recuperar_semilla&id_semilla=".$semillas[$k]["id"].">";
				//echo "Recuperar Semilla";
				echo "<img border=0 src=$estilos/recuperar_semilla.png height=$alto_recuperar width=$ancho_recuperar></img>";
				echo "</a>";
				echo "</td>";
				echo "<td align=center>";
				echo "<a href=?accion=borrar_semilla&id_semilla=".$semillas[$k]["id"].">";
				//echo "Borrar Semilla";
				echo "<img border=0 src=$estilos/borrar_semilla.png height=$alto_borrar width=$ancho_borrar></img>";
				echo "</a>";
				echo "</td>";
			}
			else{
				echo "&nbsp;";
			}
			echo "</tr>";
			echo "</table>";
			echo "</td>";
			
			echo "<td width=$ancho_borde_der style=\"background-color: white;\">&nbsp;</td>";
			echo "<td width=$ancho_borde_der style=\"background-color: white;\">&nbsp;</td>";
			
			echo "</tr>";
		
		
		}
		
	}
}

?>

</table>

</body>
</html>
