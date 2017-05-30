<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Configuracion");
echo "<body>";
html_menu_superior(3);
html_inicio_mensajes();

$debug=false;
$estructura=obtener_estructura($db);
agregar_puertos($db, $estructura);
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["id"].": ".$grupos[$i]["nombre"]." (".$grupos[$i]["puerto"].")");
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		print_debug("--> Dominio ".$dominios[$j]["id"].": ".$dominios[$j]["nombre"]." (".$dominios[$j]["puerto"].")");
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			print_debug("---- Semilla ".$semillas[$k]["id"].": ".$semillas[$k]["url"]);
		}
	}
}//for... cada grupo

/*
echo "Probando Estadisticas<br>";
$estadisticas=obtener_estadisticas($db, 62);
echo "id_grupo: ".$estadisticas["id_grupo"]."<br>";
echo "fecha_colecta: ".$estadisticas["fecha_colecta"]."<br>";
echo "bytes_colecta: ".texto_tamaño($estadisticas["bytes_colecta"])."<br>";
echo "fecha_indice: ".$estadisticas["fecha_indice"]."<br>";
echo "numero_documentos: ".$estadisticas["numero_documentos"]." documentos<br>";
*/

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

$numero_agregar=$_GET["numero_agregar"];
if(!ctype_digit($numero_agregar))
	$numero_agregar=0;

$post_accion=trim($_POST["post_accion"]);

$post_id_grupo=$_POST["post_id_grupo"];
if(!ctype_digit($post_id_grupo))
	$post_id_grupo=0;

$post_id_dominio=$_POST["post_id_dominio"];
if(!ctype_digit($post_id_dominio))
	$post_id_dominio=0;

$post_id_semilla=$_POST["post_id_semilla"];
if(!ctype_digit($post_id_semilla))
	$post_id_semilla=0;

$post_numero_agregar=$_POST["post_numero_agregar"];
if(!ctype_digit($post_numero_agregar))
	$post_numero_agregar=0;

$post_agregar_grupo=trim($_POST["post_agregar_grupo"]);

if($post_accion!=null){
	$accion=$post_accion;
	$id_grupo=$post_id_grupo;
	$id_dominio=$post_id_dominio;
	$id_semilla=$post_id_semilla;
	$numero_agregar=$post_numero_agregar;
}

$debug=false;
$nuevo_puerto_grupo=obtener_puerto_random($db);
if($accion=="inicio_agregar_dominios"){
	//obtengo o genero
	$nuevo_puerto=array();
	for($i=0; $i<$numero_agregar; $i++){
		$nuevo_puerto[$i]=obtener_puerto_random($db);
	}
}
else if($accion=="agregar_dominios"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		//echo "Agregando $numero_agregar dominios a grupo $id_grupo...<br>";
		for($i=0; $i<$numero_agregar; $i++){
			$nombre=trim($_POST["nombre_".$i]);
			$puerto=$_POST["puerto_".$i];
			$reject=trim($_POST["reject_".$i]);
			$reject=preparar_reject_list($reject);
			//echo "(\"$nombre\", \"$puerto\")<br>";
			echo "Agregando \"$nombre\" a Grupo \"".$grupo["nombre"]."\"<br>";
			if(verificar_nombre($nombre) && verificar_puerto($db, $puerto)){
				//echo "Nuevo Dominio [$i] (\"".$nombre."\", \"".$puerto."\")<br>";
				//ingresar_dominio
				ingresar_dominio($db, $nombre, $id_grupo, $puerto, $reject);
				
			}
			else{
				echo "Nombre o Puerto Invalidos<br>";
			}
			
		}
		$id_grupo=0;
		$numero_agregar=0;
	}
}
else if($accion=="agregar_semillas"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Invalidos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		//echo "Agregando Semillas a dominio $id_dominio...<br>";
		for($i=0; $i<$numero_agregar; $i++){
			$url=trim($_POST["url_".$i]);
			if(! (strpos($url, "http")===0))
				$url="http://$url";
			$reject=trim($_POST["reject_".$i]);
			$reject=preparar_reject_list($reject);
			if(verificar_url($url)
				&& (strlen($reject)==0 || verificar_nombre($reject))){
				//echo "Nueva Semilla [$i] (\"".$url."\", \"".$reject."\")<br>";
				echo "Agregando \"$url\" a dominio \"".$dominio["nombre"]."\"<br>";
				//ingresar_semilla
				ingresar_semilla($db, $url, $reject, $id_dominio);
			}
			else{
				echo "URL o Lista Invalidos<br>";
			}
		}
		$id_dominio=0;
		$numero_agregar=0;
	}
	
}
else if($accion=="editar_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Invalidos<br>";
	}
	else{
		//Editar Grupo
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$nombre=trim($_POST["nombre_grupo_".$id_grupo]);
		$puerto=$_POST["puerto_grupo_".$id_grupo];
		$reject=trim($_POST["reject_grupo_".$id_grupo]);
		$reject=preparar_reject_list($reject);
		if($nombre!=$grupo["nombre"] || $puerto!=$grupo["puerto"] || $reject!=$grupo["reject"]){
			//Cambiar Esto por dos verificaciones separadas !!!
			if(verificar_nombre($nombre) 
				&& ( $puerto==$grupo["puerto"] || $puerto!=$grupo["puerto"] && verificar_puerto($db, $puerto)) ){
				print_debug("editar_grupo($db, $id_grupo, $nombre, $puerto, $reject)");
				editar_grupo($db, $id_grupo, $nombre, $puerto, $reject); 
			}
			else{
				echo "Valores invalidos para grupo $id_grupo<br>";
			}
		}//if... hay que editar
		
		//Editar Dominios
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		$numero=count($dominios);
		for($i=0; $i<$numero; $i++){
			$nombre=trim($_POST["nombre_dominio_".$dominios[$i]["id"]]);
			$puerto=$_POST["puerto_dominio_".$dominios[$i]["id"]];
			$reject=trim($_POST["reject_dominio_".$dominios[$i]["id"]]);
			$reject=preparar_reject_list($reject);
			if($nombre!=$dominios[$i]["nombre"] 
				|| $puerto!=$dominios[$i]["puerto"] || $reject!=$dominios[$i]["reject"]){
				if(verificar_nombre($nombre) 
					&& ( $puerto==$dominios[$i]["puerto"] 
					|| $puerto!=$dominios[$i]["puerto"] && verificar_puerto($db, $puerto) ) ){
					print_debug("editar_dominio($db, ".$dominios[$i]["id"].", $nombre, $id_grupo, $puerto, $reject)"); 
					editar_dominio($db, $dominios[$i]["id"], $nombre, $id_grupo, $puerto, $reject); 
				}
				else{
					echo "Valores invalidos para dominio ".$dominios[$i]["id"]."<br>";
				}
			}//if... hay que editar
		}//for... cada dominio agregado
		
		//Editar Semillas
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		$numero=count($semillas);
		for($i=0; $i<$numero; $i++){
			$url_semilla=trim($_POST["url_semilla_".$semillas[$i]["id"]]);
			if(! (strpos($url_semilla, "http")===0))
				$url_semilla="http://$url_semilla";
			$reject=trim($_POST["reject_semilla_".$semillas[$i]["id"]]);
			$reject=preparar_reject_list($reject);
			if($url_semilla!=$semillas[$i]["url"] || $reject!=$semillas[$i]["reject"]){
				if(verificar_url($url_semilla) && 
					(strlen($reject)==0 || verificar_nombre($reject))){
					print_debug("editar_semilla($db, ".$semillas[$i]["id"].", $url_semilla, $reject, ".$semillas[$i]["id_dominio"].")");
					editar_semilla($db, $semillas[$i]["id"], $url_semilla, $reject, $semillas[$i]["id_dominio"]); 
				}
				else{
					echo "Valores invalidos para semilla ".$semillas[$i]["id"]."<br>";
				}
			}//if... hay que editar
		}//for... cada semilla agregada
		
	}
}
else if($accion=="editar_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Invalidos<br>";
	}
	else{
		//Editar Dominio
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$nombre=trim($_POST["nombre_dominio_".$id_dominio]);
		$puerto=trim($_POST["puerto_dominio_".$id_dominio]);
		$reject=trim($_POST["reject_dominio_".$id_dominio]);
		$reject=preparar_reject_list($reject);
		if($nombre!=$dominio["nombre"] || $puerto!=$dominio["puerto"] || $reject!=$dominio["reject"]){
			if(verificar_nombre($nombre) 
				&& ( $puerto==$dominio["puerto"] 
				|| $puerto!=$dominio["puerto"] && verificar_puerto($db, $puerto) ) ){
				print_debug("editar_dominio($db, $id_dominio, $nombre, ".$dominio["id_grupo"].", $puerto, $reject)");
				editar_dominio($db, $id_dominio, $nombre, $dominio["id_grupo"], $puerto, $reject); 
			}
			else{
				echo "Valores invalidos para dominio \"".$dominio["nombre"]."\"<br>";
			}
		}//if... hay que editar
		
		//Editar Semillas
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		$numero=count($semillas);
		for($i=0; $i<$numero; $i++){
			$url_semilla=trim($_POST["url_semilla_".$semillas[$i]["id"]]);
			if(! (strpos($url_semilla, "http")===0))
				$url_semilla="http://$url_semilla";
			$reject=trim($_POST["reject_semilla_".$semillas[$i]["id"]]);
			$reject=preparar_reject_list($reject);
			if($url_semilla!=$semillas[$i]["url"] 
				|| $reject!=$semillas[$i]["reject"]){
				if(verificar_url($url_semilla) && 
					(strlen($reject)==0 || verificar_nombre($reject))){
					print_debug("editar_semilla($db, ".$semillas[$i]["id"].", $url_semilla, $reject, $id_dominio)");
					editar_semilla($db, $semillas[$i]["id"], $url_semilla, $reject, $id_dominio); 
				}
				else{
					echo "Valores invalidos para semilla ".$semillas[$i]["id"]."<br>";
				}
			}//if... hay que editar
		}//for... cada semilla agregada

	}
}
else if($accion=="editar_semilla"){
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Invalidos<br>";
	}
	else{
		//Editar Semilla
		$semilla=filtrar_semilla($estructura, $id_semilla);
		$url_semilla=trim($_POST["url_semilla_".$id_semilla]);
		if(! (strpos($url_semilla, "http")===0))
			$url_semilla="http://$url_semilla";
		$reject=trim($_POST["reject_semilla_".$id_semilla]);
		$reject=preparar_reject_list($reject);
		if($url_semilla!=$semilla["url"] || $reject!=$semilla["reject"]){
			if(verificar_url($url_semilla) && 
				(strlen($reject)==0 || verificar_nombre($reject))){
				print_debug("editar_semilla($db, $id_semilla, $url_semilla, $reject, ".$semilla["id_dominio"].")");
				editar_semilla($db, $id_semilla, $url_semilla, $reject, $semilla["id_dominio"]);
			}
			else{
				echo "Valores invalidos para semilla $id_semilla<br>";
			}
		}//if... hay que editar
		
	}
}
else if($accion=="inicio_eliminar_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		$numero_dominios=count($dominios);
		$numero_semillas=count($semillas);
		
		echo "<form id=form_01 action=? method=post>";
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		
		echo "<td align=center width=600>";
		echo "Esta a punto de eliminar el Grupo \"".$grupo["nombre"]."\"<br>";
		echo "que posee $numero_dominios dominios y un total de $numero_semillas semillas.<br>";
		echo "<br> &iquest; Est&aacute; seguro que desea continuar ? <br><br>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "<tr align=center>";
		
		echo "<td>";
		echo "<input type=hidden name=post_accion value=eliminar_grupo>";
		echo "<input type=hidden name=post_id_grupo value=".$id_grupo.">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<a href=?#grupo_$id_grupo><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "</table>";
		echo "</form>";
		die();
	}
}
else if($accion=="inicio_eliminar_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo_dominio($estructura, $id_dominio);
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		$numero_semillas=count($semillas);
		
		echo "<form id=form_01 action=?#grupo_".$grupo["id"]." method=post>";
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		
		echo "<td align=center width=600>";
		echo "Esta a punto de eliminar el Dominio \"".$dominio["nombre"]."\"<br>";
		echo "que posee $numero_semillas semillas.<br>";
		echo "<br> &iquest; Est&aacute; seguro que desea continuar ? <br><br>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "<tr align=center>";
		
		echo "<td>";
		echo "<input type=hidden name=post_accion value=eliminar_dominio>";
		echo "<input type=hidden name=post_id_dominio value=".$id_dominio.">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<a href=?#grupo_".$grupo["id"]."><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "</table>";
		echo "</form>";
		die();
	}
}
else if($accion=="inicio_eliminar_semilla"){
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo_semilla($estructura, $id_semilla);
		$semilla=filtrar_semilla($estructura, $id_semilla);
		
		echo "<form id=form_01 action=?#grupo_".$grupo["id"]." method=post>";
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		
		echo "<td align=center width=600>";
		echo "Esta a punto de eliminar la Semilla <br>";
		echo "".$semilla["url"]."<br>";
		echo "<br> &iquest; Est&aacute; seguro que desea continuar ? <br><br>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "<tr align=center>";
		
		echo "<td>";
		echo "<input type=hidden name=post_accion value=eliminar_semilla>";
		echo "<input type=hidden name=post_id_semilla value=".$id_semilla.">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<a href=?#grupo_".$grupo["id"]."><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "</table>";
		echo "</form>";
		die();
	}
}
else if($accion=="eliminar_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		$numero_dominios=count($dominios);
		$numero_semillas=count($semillas);
	
		//Eliminar el grupo
		print_debug("eliminar_grupo($db, $id_grupo)");
		eliminar_grupo($db, $id_grupo);
		print_debug("eliminar_indice($prefijo, \"G\", ".$id_grupo.", $ruta_indice)");
		eliminar_indice($prefijo, "G", $id_grupo, $ruta_indice);
		print_debug("eliminar_demonio($prefijo, \"G\", $id_grupo, $ruta_bin)");
		//terminar_servicio($prefijo."-G-".$id_grupo, $ruta_bin);
		eliminar_demonio($prefijo, "G", $id_grupo, $ruta_bin);
		
		//Eliminar dominios
		for($i=0; $i<$numero_dominios; $i++){
			print_debug("eliminar_dominio($db, ".$dominios[$i]["id"].")");
			eliminar_dominio($db, $dominios[$i]["id"]);
			print_debug("eliminar_indice($prefijo, \"D\", ".$dominios[$i]["id"].", $ruta_indice)");
			eliminar_indice($prefijo, "D", $dominios[$i]["id"], $ruta_indice);
			print_debug("eliminar_demonio($prefijo, \"D\", ".$dominios[$i]["id"].", $ruta_bin)");
			//terminar_servicio($prefijo."-D-".$dominios[$i]["id"], $ruta_bin);
			eliminar_demonio($prefijo, "D", $dominios[$i]["id"], $ruta_bin);
		}
		
		//Eliminar semillas
		for($i=0; $i<$numero_semillas; $i++){
			print_debug("eliminar_semilla($db, ".$semillas[$i]["id"].")");
			eliminar_semilla($db, $semillas[$i]["id"]);
			print_debug("eliminar_colecta($prefijo, ".$semillas[$i]["id"].", $ruta_colecta)");
			eliminar_colecta($prefijo, $semillas[$i]["id"], $ruta_colecta, $ruta_logs);
		}
	}

}
else if($accion=="eliminar_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Invalidos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		$numero_semillas=count($semillas);
		
		//Eliminar el dominio
		print_debug("eliminar_dominio($db, $id_dominio)");
		eliminar_dominio($db, $id_dominio);
		print_debug("eliminar_indice($prefijo, \"D\", $id_dominio, $ruta_indice)");
		eliminar_indice($prefijo, "D", $id_dominio, $ruta_indice);
		print_debug("eliminar_demonio($prefijo, \"D\", $id_dominio, $ruta_bin)");
		//terminar_servicio($prefijo."-D-".$id_dominio, $ruta_bin);
		eliminar_demonio($prefijo, "D", $id_dominio, $ruta_bin);
		
		//Eliminar semillas
		for($i=0; $i<$numero_semillas; $i++){
			print_debug("eliminar_semilla($db, ".$semillas[$i]["id"].")");
			eliminar_semilla($db, $semillas[$i]["id"]);
			print_debug("eliminar_colecta($prefijo, ".$semillas[$i]["id"].", $ruta_colecta)");
			eliminar_colecta($prefijo, $semillas[$i]["id"], $ruta_colecta, $ruta_logs);
		}
	}

}
else if($accion=="eliminar_semilla"){
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Invalidos<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
	
		//Eliminar la semilla
		print_debug("eliminar_semilla($db, $id_semilla)");
		eliminar_semilla($db, $id_semilla);
		print_debug("eliminar_colecta($prefijo, $id_semilla, $ruta_colecta)");
		eliminar_colecta($prefijo, $id_semilla, $ruta_colecta, $ruta_logs);
		
	}

}
else if($post_agregar_grupo=="agregar_grupo"){
	//Por ahora, esto tiene que estar al final
	//echo "Agregando grupo<br>";
	
	$nombre=trim($_POST["nombre_grupo"]);
	$puerto=$_POST["puerto_grupo"];
	$reject=trim($_POST["reject_grupo"]);
	$reject=preparar_reject_list($reject);
	if(verificar_nombre($nombre) && verificar_puerto($db, $puerto)){
		echo "Agregando grupo \"$nombre\"<br>";
		//echo "Nuevo Grupo (\"".$nombre."\", \"".$puerto."\")<br>";
		ingresar_grupo($db, $nombre, $puerto, $reject);
	}
	else{
		echo "Nombre o Puerto Invalidos<br>";
	}
}

$debug=false;

//Actualizo la estructura
$estructura=obtener_estructura($db);
agregar_puertos($db, $estructura);

mysql_close($db);
html_fin_mensajes();








$titulo="Control de Configuracion";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Historial";
$elemento["ruta"]="control_historial.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Respaldos";
$elemento["ruta"]="control_respaldos.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<form id=form_01 action=? method=post>";

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\">
	<th style=\"width:60;\" colspan=2>&nbsp;</th>
	<th style=\"width:250;\" >Nombre/URL</th>
	<th style=\"width:150;\">Lista Rechazo</th>
	<th style=\"width:80;\">Puerto</th>
	<th style=\"width:140;\">Editar</th>
	<th style=\"width:100;\">Eliminar</th>
	<th style=\"width:20;\" colspan=2>&nbsp;</th>
</tr>";


$color_usado=0;
$color=array("rgb(200, 200, 200)", "rgb(230, 230, 230)");
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$color_usado=($color_usado+1)%2;
	
	//Si se esta editango el grupo, se muestra en version editable
	if($accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
		echo "<tr height=$separacion_grupos><td colspan=9>";
		echo "<a name=grupo_".$grupos[$i]["id"]."></a>";
		echo "</td></tr>";
		
		echo "<tr height=$alto_fila valign=center style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
		
		echo "<td colspan=2 class=borde_grupo_01 >&nbsp;</td>";
		
		echo "<td class=borde_grupo_02 >";
		echo "<input type=text name=nombre_grupo_".$grupos[$i]["id"]." size=20 value=\"".$grupos[$i]["nombre"]."\" >";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		echo "<input type=text name=reject_grupo_".$grupos[$i]["id"]." size=10 value=\"".$grupos[$i]["reject"]."\" >";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		echo "<input type=text name=puerto_grupo_".$grupos[$i]["id"]." size=5 value=\"".$grupos[$i]["puerto"]."\" >";
		echo "</td>";
		
		echo "<td align=center class=borde_grupo_02>";
		echo "<table><tr valign=center>";
		
		echo "<td>";
		echo "<input type=hidden name=post_accion value=editar_grupo>";
		echo "<input type=hidden name=post_id_grupo value=".$grupos[$i]["id"].">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
		echo "</td>";
		echo "<td>";
		echo "<a href=?#grupo_".$grupos[$i]["id"]."><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
		echo "</td>";
		
		echo "</tr></table>";
		echo "</td>";
		
		echo "<td class=borde_grupo_02>--</td>";
		
		echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
		
		echo "</tr>";
	}
	else{
		echo "<tr height=$separacion_grupos><td colspan=9></td></tr>";
		
		echo "<tr height=$alto_fila valign=center style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
		
		echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";
		
		echo "<td class=borde_grupo_02>";
		echo "<a name=grupo_".$grupos[$i]["id"]."></a>";
		if(strlen($grupos[$i]["nombre"])>25)
			$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
		else
			$texto=$grupos[$i]["nombre"];
		echo "".$texto."";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		if(strlen($grupos[$i]["reject"])<1){
			$texto="&nbsp;";
		}
		else if(strlen($grupos[$i]["reject"])>10){
			$texto=substr($grupos[$i]["reject"], 0, 10)."...";
		}
		else{
			$texto=$grupos[$i]["reject"];
		}
		echo "".$texto."";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		echo $grupos[$i]["puerto"]."";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		echo "<a href=?accion=inicio_editar_grupo&id_grupo=".$grupos[$i]["id"]."#grupo_".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/editar_grupo.png height=$alto_editar width=$ancho_editar>";
		//echo "Editar Grupo";
		echo "</a>";
		echo "</td>";
	
		echo "<td class=borde_grupo_02>";
		echo "<a href=?accion=inicio_eliminar_grupo&id_grupo=".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
		//echo "Editar Grupo";
		echo "</a>";
		
		echo "</td>";
		
		echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
		
		echo "</tr>";
	}
	
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		//Si hay que editar este dominio o este grupo, lo escribo en version editable
		if($accion=="inicio_editar_dominio" && $id_dominio==$dominios[$j]["id"]
			|| $accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
			
			echo "<tr height=$separacion_dominios>";
			echo "<td><img src=$estilos/extension_03.png height=$separacion_dominios width=$ancho_borde_izq></td>";
			echo "<td colspan=8></td>";
			echo "</tr>";
			
			echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
			
			echo "<td align=left >";
			echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq>";
			echo "</td>";
			
			echo "<td class=borde_dominio_01>&nbsp;</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "<a name=dominio_".$dominios[$j]["id"]."></a>";
			echo "<input type=text name=nombre_dominio_".$dominios[$j]["id"]." size=20 value=\"".$dominios[$j]["nombre"]."\" >";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			echo "<input type=text name=reject_dominio_".$dominios[$j]["id"]." size=10 value=\"".$dominios[$j]["reject"]."\" >";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			echo "<input type=text name=puerto_dominio_".$dominios[$j]["id"]." size=5 value=\"".$dominios[$j]["puerto"]."\" >";
			echo "</td>";
		
			echo "<td align=center class=borde_dominio_02>";
			if($accion=="inicio_editar_dominio"){
				
				echo "<table><tr valign=center>";
				
				echo "<td>";
				echo "<input type=hidden name=post_accion value=editar_dominio>";
				echo "<input type=hidden name=post_id_dominio value=".$dominios[$j]["id"].">";
				echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
				echo "</td>";
				echo "<td>";
				echo "<a href=?#dominio_".$dominios[$j]["id"]."><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
				echo "</td>";
				
				echo "</tr></table>";
				
			}
			else{
				echo "--";
			}
			echo "</td>";
			
			echo "<td class=borde_dominio_02>--</td>";
			
			echo "<td class=borde_dominio_03>&nbsp;</td>";
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			echo "</tr>";
			
		}
		else{
			echo "<tr height=$separacion_dominios>";
			echo "<td ><img src=$estilos/extension_03.png height=$separacion_dominios width=$ancho_borde_izq></td>";
			echo "<td colspan=8></td>";
			echo "</tr>";
			
			echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
			
			echo "<td align=left width=$ancho_borde_izq>";
			echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq>";
			echo "</td>";
			
			echo "<td class=borde_dominio_01 width=$ancho_borde_izq>&nbsp;</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "<a name=dominio_".$dominios[$j]["id"]."></a>";
			if(strlen($dominios[$j]["nombre"])>25)
				$texto=substr($dominios[$j]["nombre"], 0, 25)."...";
			else
				$texto=$dominios[$j]["nombre"];
			echo "".$texto."";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			if(strlen($dominios[$j]["reject"])<1){
				$texto="&nbsp;";
			}
			else if(strlen($dominios[$j]["reject"])>15){
				$texto=substr($dominios[$j]["reject"], 0, 15)."...";
			}
			else{
				$texto=$dominios[$j]["reject"];
			}
			echo "".$texto."";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			echo "".$dominios[$j]["puerto"]."";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			echo "<a href=?accion=inicio_editar_dominio&id_dominio=".$dominios[$j]["id"]."#dominio_".$dominios[$j]["id"]." >";
			echo "<img border=0 src=$estilos/editar_dominio.png height=$alto_editar width=$ancho_editar>";
			echo "</a>";
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "<a href=?accion=inicio_eliminar_dominio&id_dominio=".$dominios[$j]["id"].">";
			echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
			echo "</a>";
			echo "</td>";
			
			echo "<td class=borde_dominio_03>&nbsp;</td>";
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			echo "</tr>";
		
		}
		
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
		
			//Si se esta editando el grupo, dominio o semilla, se escribe en version editable
			if($accion=="inicio_editar_semilla" && $id_semilla==$semillas[$k]["id"]
				|| $accion=="inicio_editar_dominio" && $id_dominio==$dominios[$j]["id"]
				|| $accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
				
				echo "<tr valign=center style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
				
				echo "<td align=left >";
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq>";
				echo "</td>";
			
				echo "<td align=left >";
				echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq>";
				echo "</td>";
			
				echo "<td>";
				echo "<input type=text name=url_semilla_".$semillas[$k]["id"]." size=20 value=\"".$semillas[$k]["url"]."\" >";
				echo "</td>";
			
				echo "<td>";
				echo "<input type=text name=reject_semilla_".$semillas[$k]["id"]." size=10 value=\"".$semillas[$k]["reject"]."\" >";
				echo "</td>";
			
				echo "<td>";
				echo "--";
				echo "</td>";
			
				echo "<td align=center>";
				if($accion=="inicio_editar_semilla"){
					
					echo "<table><tr valign=center>";
					echo "<td>";
					echo "<input type=hidden name=post_accion value=editar_semilla>";
					echo "<input type=hidden name=post_id_semilla value=".$semillas[$k]["id"].">";
					echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
					echo "</td>";
					echo "<td>";
					echo "<a href=?#dominio_".$dominios[$j]["id"]."><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
					echo "</td>";
					echo "</tr></table>";
					
				}
				else
					echo "--";
				echo "</td>";
				
				echo "<td>--</td>";
				
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				
				echo "</tr>";
			}
			else{
				echo "<tr style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
				
				echo "<td align=left >";
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq>";
				echo "</td>";
			
				echo "<td align=left >";
				echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq>";
				echo "</td>";
			
				echo "<td>";
				//El substring es para direcciones muy largas que pueden verse raros
				if(strlen($semillas[$k]["url"])>25){
					$texto=substr($semillas[$k]["url"], 0, 25)."...";
				}
				else{
					$texto=$semillas[$k]["url"];
				}
				echo "".$texto."";
				echo "</td>";
			
				echo "<td>";
				if(strlen($semillas[$k]["reject"])<1){
					$texto="&nbsp;";
				}
				else if(strlen($semillas[$k]["reject"])>15){
					$texto=substr($semillas[$k]["reject"], 0, 15)."...";
				}
				else{
					$texto=$semillas[$k]["reject"];
				}
				echo "".$texto."";
				echo "</td>";
			
				echo "<td>";
				echo "--";
				echo "</td>";
			
				echo "<td >";
				echo "<a href=?accion=inicio_editar_semilla&id_semilla=".$semillas[$k]["id"]."#dominio_".$dominios[$j]["id"].">";
				echo "<img border=0 src=$estilos/editar_semilla.png height=$alto_editar width=$ancho_editar>";
				echo "</a>";
				echo "</td>";
				
				echo "<td>";
				echo "<a href=?accion=inicio_eliminar_semilla&id_semilla=".$semillas[$k]["id"].">";
				echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
				echo "</a>";
				echo "</td>";
				
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				
				echo "</tr>";
			}
			
		}
		
		//Si id_dominio existe y es id del dominio $j, entonces aqui van los campos
		//De otro modo, pongo el boton "+"
		if($id_dominio==$dominios[$j]["id"] && $accion=="inicio_agregar_semillas"){
			for($k=0; $k<$numero_agregar; $k++){
				//Campos
				echo "<tr style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
			
				echo "<td>";
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq>";
				echo "</td>";
			
				echo "<td>";
				if($k==0){
					echo "<a href=?accion=inicio_agregar_semillas&id_dominio=".$dominios[$j]["id"]."&numero_agregar=".(1+$numero_agregar)."#dominio_".$dominios[$j]["id"].">";
					if($numero_agregar==1)
						echo "<img src=$estilos/agregar_08.png height=$alto_fila width=$ancho_borde_izq border=0>";
					else
						echo "<img src=$estilos/agregar_09.png height=$alto_fila width=$ancho_borde_izq border=0>";
				
					echo "</a>";
				}
				else if($k==$numero_agregar-1){
					echo "<img src=$estilos/extension_02_gris.png height=$alto_fila width=$ancho_borde_izq>";
				}
				else{
					echo "<img src=$estilos/extension_01_gris.png height=$alto_fila width=$ancho_borde_izq>";
				}
				echo "</td>";
			
				echo "<td>";
				echo "<input type=text name=url_".$k." size=20>";
				echo "</td>";
			
				echo "<td>";
				echo "<input type=text name=reject_".$k." size=10>";
				echo "</td>";
			
				echo "<td>";
				echo "--";
				echo "</td>";
			
				echo "<td>";
				echo "--";
				echo "</td>";
				
				echo "<td>--</td>";
				
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				echo "<td width=$ancho_borde_der bgcolor=white></td>";
				echo "</tr>";
				
			}
			//Aceptar / Cancelar
			
			echo "<tr>";
			
			echo "<td>";
			echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq>";
			echo "</td>";
			
			echo "<td>";
			echo "<img src=$estilos/extension_04.png height=$alto_fila width=$ancho_borde_izq>";
			echo "</td>";
			
			echo "<td align=center>";
			
			echo "<table ><tr valign=center>";
			echo "<td>";
			//Campos adicionales
			echo "<input type=hidden name=post_accion value=agregar_semillas>";
			echo "<input type=hidden name=post_id_dominio value=".$dominios[$j]["id"].">";
			echo "<input type=hidden name=post_numero_agregar value=".$numero_agregar.">";
			echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
			echo "</td>";
			echo "<td>";
			echo "<a href=?><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
			echo "</td>";
			echo "</tr></table>";
			
			echo "</td>";
			
			echo "<td colspan=6></td>";
			
			echo "</tr>";
			
		}
		else{
			//Boton "+"
			echo "<tr>";
			
			echo "<td>";
			echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq>";
			echo "</td>";
		
			echo "<td align=left>";
			echo "<a href=?accion=inicio_agregar_semillas&id_dominio=".$dominios[$j]["id"]."&numero_agregar=1#dominio_".$dominios[$j]["id"].">";
			echo "<img src=$estilos/agregar_05.png height=$alto_fila width=$ancho_borde_izq border=0>";
			echo "</a>";
			echo "</td>";
		
			echo "<td colspan=7>&nbsp;</td>";
			echo "</tr>";
			
		}
		
	}
	
	//Si id_grupo existe y es id del grupo $i, entonces aqui van los campos
	//De otro modo, pongo el boton "+"
	if($id_grupo==$grupos[$i]["id"] && $accion=="inicio_agregar_dominios"){
		for($j=0; $j<$numero_agregar; $j++){
			echo "<tr height=$separacion_dominios>";
			echo "<td width=$ancho_borde_izq>";
			if($j==0)
				echo "<img src=$estilos/extension_03.png  height=$separacion_dominios width=$ancho_borde_izq>";
			else
				echo "<img src=$estilos/extension_03_gris.png  height=$separacion_dominios width=$ancho_borde_izq>";
			echo "</td>";
			echo "<td colspan=8></td>";
			echo "</tr>";
			
			echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
			
			echo "<td align=left width=$ancho_borde_izq>";
			if($j==0){
				//echo "<a href=?accion=inicio_agregar_dominios&id_grupo=".$grupos[$i]["id"]."&numero_agregar=".(1+$numero_agregar)."#grupo_".$grupos[$i]["id"].">";
				echo "<a name=nuevo_dominio></a><a href=?accion=inicio_agregar_dominios&id_grupo=".$grupos[$i]["id"]."&numero_agregar=".(1+$numero_agregar)."&#nuevo_dominio>";
				if($numero_agregar==1)
					echo "<img src=$estilos/agregar_08.png height=$alto_fila width=$ancho_borde_izq border=0>";
				else
					echo "<img src=$estilos/agregar_09.png height=$alto_fila width=$ancho_borde_izq border=0>";
				echo "</a>";
			}
			else if($j==$numero_agregar-1){
				echo "<img src=$estilos/extension_02_gris.png height=$alto_fila width=$ancho_borde_izq>";
			}
			else{
				echo "<img src=$estilos/extension_01_gris.png height=$alto_fila width=$ancho_borde_izq>";
			}
			echo "</td>";
			
			echo "<td class=borde_dominio_01>&nbsp;</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "<input type=text name=nombre_".$j." size=20>";
			echo "</td>";
		
			echo "<td class=borde_dominio_02>";
			echo "<input type=text name=reject_".$j." size=10>";
			//echo "--";
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "<input type=text name=puerto_".$j." size=5 value=".$nuevo_puerto[$j].">";
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			echo "--";
			echo "</td>";
			
			echo "<td class=borde_dominio_02>--</td>";
			
			echo "<td class=borde_dominio_03>&nbsp;</td>";
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
		
			echo "</tr>";
			
		}
		//Aceptar / Cancelar
		echo "<tr>";
			
		echo "<td>";
		echo "<img src=$estilos/extension_04.png height=$alto_fila width=$ancho_borde_izq>";
		echo "</td>";
		
		echo "<td>&nbsp;</td>";
		
		echo "<td align=center>";

		echo "<table><tr valign=center>";
		echo "<td>";
		//Campos adicionales
		echo "<input type=hidden name=post_accion value=agregar_dominios>";
		echo "<input type=hidden name=post_id_grupo value=".$grupos[$i]["id"].">";
		echo "<input type=hidden name=post_numero_agregar value=".$numero_agregar.">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=23 width=80>";
		echo "</td>";
		echo "<td>";
		echo "<a href=?><img border=0 src=$estilos/cancelar.png height=23 width=80></a>";
		echo "</td>";
		echo "</tr></table>";
		
		echo "</td>";
		
		echo "<td colspan=6></td>";
		
		echo "</tr>";
		
	}
	else{
		//Boton "+"
		echo "<tr>";
		echo "<td>";
		//echo "<a href=?accion=inicio_agregar_dominios&id_grupo=".$grupos[$i]["id"]."&numero_agregar=1#grupo_".$grupos[$i]["id"].">";
		echo "<a href=?accion=inicio_agregar_dominios&id_grupo=".$grupos[$i]["id"]."&numero_agregar=1#nuevo_dominio>";
		echo "<img src=$estilos/agregar_05.png height=$alto_fila width=$ancho_borde_izq border=0>";
		echo "</a>";
		echo "</td>";
		echo "<td colspan=8> &nbsp;</td>";
		echo "</tr>";
	}
	
	
}

$color_usado=($color_usado+1)%2;

echo "<tr height=$separacion_grupos><td colspan=9></td></tr>";
echo "<tr height=$separacion_grupos><td colspan=9></td></tr>";

echo "<tr style=\"text-align:center; background-color:".$color_grupo[$color_usado].";\">";

echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";

echo "<td align=center height=$alto_fila class=borde_grupo_02>";
echo "<input type=hidden name=post_agregar_grupo value=agregar_grupo>";
echo "<input type=text name=nombre_grupo size=20>";
echo "</td>";

echo "<td align=center height=$alto_fila class=borde_grupo_02>";
echo "<input type=text name=reject_grupo size=10>";
echo "</td>";

echo "<td class=borde_grupo_02>";
echo "<input type=text name=puerto_grupo size=5 value=$nuevo_puerto_grupo>";
echo "</td>";

echo "<td colspan=2 align=center class=borde_grupo_02>";
echo "<table><tr valign=center align=center>";
echo "<td>";
echo "<input type=image src=$estilos/agregar_grupo.png name=aceptar value=Aceptar height=20 width=115>";
//echo "<input type=submit name=aceptar value=\"Agregar Grupo\">";
echo "</td>";
echo "</tr></table>";
echo "</td>";

echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";

echo "</tr>";
	
echo "</table>";
echo "</form>";
echo "<br>";

echo "<br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br>";
?>


</body>

</html>
