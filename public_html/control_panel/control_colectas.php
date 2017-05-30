<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Colectas");
echo "<body>";
html_menu_superior(2);
html_inicio_mensajes();

$debug=false;
$estructura=obtener_estructura($db);
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

//---------- Verificacion de Colectas activas ----------
$colecta_activa_grupo=array();
$colecta_activa_dominio=array();
$colecta_activa_semilla=array();
$debug=false;
verificar_colectas_activas($estructura, $prefijo, $ruta_bin, $minicow);
$debug=false;

if($accion=="eliminar_colecta_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		//echo "Eliminando colecta de \"".$grupo["nombre"]."\"<br>";
		for($i=0; $i<count($semillas); $i++){
			//echo "Eliminando colecta de \"".$semillas[$i]["url"]."\"<br>";
			eliminar_colecta($prefijo, $semillas[$i]["id"], $ruta_colecta, $ruta_logs);
		}
	}
}
else if($accion=="eliminar_colecta_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		echo "Eliminando colecta de \"".$dominio["nombre"]."\"<br>";
		for($i=0; $i<count($semillas); $i++){
			//echo "Eliminando colecta de \"".$semillas[$i]["url"]."\"<br>";
			eliminar_colecta($prefijo, $semillas[$i]["id"], $ruta_colecta, $ruta_logs);
		}
	}
}
else if($accion=="eliminar_colecta_semilla"){
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
		//echo "Eliminando colecta de \"".$semilla["url"]."\"<br>";
		eliminar_colecta($prefijo, $id_semilla, $ruta_colecta, $ruta_logs);
	}
}
else if($accion=="iniciar_colecta_grupo"){
	//solo se necesita el id_grupo
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_grupo[$id_grupo]){
		echo "Existen colectas activas de este grupo, accion cancelada.<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		//$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Iniciando colectas del grupo \"".$grupo["nombre"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "G", $id_grupo);
		$grupos=$estructura["grupos"];
		for($i=0; $i<count($grupos); $i++){
			if($grupos[$i]["id"]==$grupo["id"]){
				$dominios=$estructura["dominios"][$i];
				for($j=0; $j<count($dominios); $j++){
					if(strlen($grupo["reject"])<1){
						if(strlen($dominios[$j]["reject"])<1)
							$reject_base="";
						else
							$reject_base=$dominios[$j]["reject"];
					}
					else{
						if(strlen($dominios[$j]["reject"])<1)
							$reject_base=$grupo["reject"];
						else
							$reject_base=$grupo["reject"]." , ".$dominios[$j]["reject"];
					}
					$semillas=$estructura["semillas"][$i][$j];
					for($k=0; $k<count($semillas); $k++){
						//echo "Iniciando colecta de \"".$semillas[$i]["url"]."\"<br>";
						ingresar_log_semillas_colectadas($db, $semillas[$k]["id"], $id_inicio_colecta);
						//respaldar el log de la colecta
						respaldar_log($prefijo, $semillas[$k]["id"], $ruta_logs);
						//sumar las listas de rechazo
						if(strlen($semillas[$k]["reject"])<1){
							$semillas[$k]["reject"]=$reject_base;
						}
						else if(strlen($reject_base)){
							$semillas[$k]["reject"]=$reject_base." , ".$semillas[$k]["reject"];
						}
						iniciar_colecta($prefijo, $semillas[$k], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
					}
				}
				
				
			}
		}
		/*
		for($i=0; $i<count($semillas); $i++){
			//echo "Iniciando colecta de \"".$semillas[$i]["url"]."\"<br>";
			ingresar_log_semillas_colectadas($db, $semillas[$i]["id"], $id_inicio_colecta);
			//respaldar el log de la colecta
			respaldar_log($prefijo, $semillas[$i]["id"], $ruta_logs);
			iniciar_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		}
		*/
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="iniciar_colecta_dominio"){
	//solo se necesita el id_dominio
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_dominio[$id_dominio]){
		echo "Existen colectas activas de este dominio, accion cancelada.<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$grupo=filtrar_grupo_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Iniciando colectas del dominio \"".$dominio["nombre"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "D", $id_dominio);
		if(strlen($grupo["reject"])<1){
			if(strlen($dominio["reject"])<1)
				$reject_base="";
			else
				$reject_base=$dominio["reject"];
		}
		else{
			if(strlen($dominio["reject"])<1)
				$reject_base=$grupo["reject"];
			else
				$reject_base=$grupo["reject"]." , ".$dominio["reject"];
		}
		
		for($i=0; $i<count($semillas); $i++){
			//echo "Iniciando colecta de \"".$semillas[$i]["url"]."\"<br>";
			ingresar_log_semillas_colectadas($db, $semillas[$i]["id"], $id_inicio_colecta);
			respaldar_log($prefijo, $semillas[$i]["id"], $ruta_logs);
			
			//sumar las listas de rechazo
			if(strlen($semillas[$i]["reject"])<1){
				$semillas[$i]["reject"]=$reject_base;
			}
			else if(strlen($reject_base)){
				$semillas[$i]["reject"]=$reject_base." , ".$semillas[$i]["reject"];
			}
			
			iniciar_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		}
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="iniciar_colecta_semilla"){
	//solo se necesita el id_semilla
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_semilla[$id_semilla]){
		echo "La colecta de esta semilla esta activa, accion cancelada.<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
		$grupo=filtrar_grupo_semilla($estructura, $id_semilla);
		$dominio=filtrar_dominio_semilla($estructura, $id_semilla);
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Iniciando colecta de \"".$semilla["url"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "S", $id_semilla);
		ingresar_log_semillas_colectadas($db, $semilla["id"], $id_inicio_colecta);
		respaldar_log($prefijo, $semilla["id"], $ruta_logs);
		if(strlen($grupo["reject"])<1){
			if(strlen($dominio["reject"])<1)
				$reject_base="";
			else
				$reject_base=$dominio["reject"];
		}
		else{
			if(strlen($dominio["reject"])<1)
				$reject_base=$grupo["reject"];
			else
				$reject_base=$grupo["reject"]." , ".$dominio["reject"];
		}
		//sumar las listas de rechazo
		if(strlen($semilla["reject"])<1){
			$semilla["reject"]=$reject_base;
		}
		else if(strlen($reject_base)){
			$semilla["reject"]=$reject_base." , ".$semilla["reject"];
		}
		iniciar_colecta($prefijo, $semilla, $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="continuar_colecta_grupo"){
	//solo se necesita el id_grupo
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_grupo[$id_grupo]){
		echo "Existen colectas activas de este grupo, accion cancelada.<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		//$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Continuando colectas del grupo \"".$grupo["nombre"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "G", $id_grupo);
		
		$grupos=$estructura["grupos"];
		for($i=0; $i<count($grupos); $i++){
			if($grupos[$i]["id"]==$grupo["id"]){
				$dominios=$estructura["dominios"][$i];
				for($j=0; $j<count($dominios); $j++){
					if(strlen($grupo["reject"])<1){
						if(strlen($dominios[$j]["reject"])<1)
							$reject_base="";
						else
							$reject_base=$dominios[$j]["reject"];
					}
					else{
						if(strlen($dominios[$j]["reject"])<1)
							$reject_base=$grupo["reject"];
						else
							$reject_base=$grupo["reject"]." , ".$dominios[$j]["reject"];
					}
					$semillas=$estructura["semillas"][$i][$j];
					for($k=0; $k<count($semillas); $k++){
						
						//echo "Continuando colecta de \"".$semillas[$k]["url"]."\"<br>";
						ingresar_log_semillas_colectadas($db, $semillas[$k]["id"], $id_inicio_colecta);
						//respaldar el log de la colecta
						respaldar_log($prefijo, $semillas[$k]["id"], $ruta_logs);
						//sumar las listas de rechazo
						if(strlen($semillas[$k]["reject"])<1){
							$semillas[$k]["reject"]=$reject_base;
						}
						else if(strlen($reject_base)){
							$semillas[$k]["reject"]=$reject_base." , ".$semillas[$k]["reject"];
						}
						continuar_colecta($prefijo, $semillas[$k], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
					}
				}
				
			}
		}
		
		/*
		for($i=0; $i<count($semillas); $i++){
			//echo "Continuando colecta de \"".$semillas[$i]["url"]."\"<br>";
			ingresar_log_semillas_colectadas($db, $semillas[$i]["id"], $id_inicio_colecta);
			respaldar_log($prefijo, $semillas[$i]["id"], $ruta_logs);
			continuar_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		}
		*/
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="continuar_colecta_dominio"){
	//solo se necesita el id_dominio
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_dominio[$id_dominio]){
		echo "Existen colectas activas de este dominio, accion cancelada.<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		$grupo=filtrar_grupo_dominio($estructura, $id_dominio);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Continuando colectas del dominio \"".$dominio["nombre"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "D", $id_dominio);
		
		if(strlen($grupo["reject"])<1){
			if(strlen($dominio["reject"])<1)
				$reject_base="";
			else
				$reject_base=$dominio["reject"];
		}
		else{
			if(strlen($dominio["reject"])<1)
				$reject_base=$grupo["reject"];
			else
				$reject_base=$grupo["reject"]." , ".$dominio["reject"];
		}
		
		for($i=0; $i<count($semillas); $i++){
			//echo "Continuando colecta de \"".$semillas[$i]["url"]."\"<br>";
			ingresar_log_semillas_colectadas($db, $semillas[$i]["id"], $id_inicio_colecta);
			respaldar_log($prefijo, $semillas[$i]["id"], $ruta_logs);
			
			//sumar las listas de rechazo
			if(strlen($semillas[$i]["reject"])<1){
				$semillas[$i]["reject"]=$reject_base;
			}
			else if(strlen($reject_base)){
				$semillas[$i]["reject"]=$reject_base." , ".$semillas[$i]["reject"];
			}
			
			continuar_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		}
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="continuar_colecta_semilla"){
	//solo se necesita el id_semilla
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_semilla[$id_semilla]){
		echo "La colecta de esta semilla esta activa, accion cancelada.<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
		$grupo=filtrar_grupo_semilla($estructura, $id_semilla);
		$dominio=filtrar_dominio_semilla($estructura, $id_semilla);
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Continuando colecta de \"".$semilla["url"]."\"<br>";
		$id_inicio_colecta=ingresar_log_inicio_colecta($db, "S", $id_semilla);
		ingresar_log_semillas_colectadas($db, $semilla["id"], $id_inicio_colecta);
		respaldar_log($prefijo, $semilla["id"], $ruta_logs);
		
		if(strlen($grupo["reject"])<1){
			if(strlen($dominio["reject"])<1)
				$reject_base="";
			else
				$reject_base=$dominio["reject"];
		}
		else{
			if(strlen($dominio["reject"])<1)
				$reject_base=$grupo["reject"];
			else
				$reject_base=$grupo["reject"]." , ".$dominio["reject"];
		}
		//sumar las listas de rechazo
		if(strlen($semilla["reject"])<1){
			$semilla["reject"]=$reject_base;
		}
		else if(strlen($reject_base)){
			$semilla["reject"]=$reject_base." , ".$semilla["reject"];
		}
		
		continuar_colecta($prefijo, $semilla, $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="detener_colecta_grupo"){
	//solo se necesita el id_grupo
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else if(!$colecta_activa_grupo[$id_grupo]){
		echo "No existen colectas activas de este grupo.<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$semillas=filtrar_semillas_grupo($estructura, $id_grupo);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Deteniendo colectas del grupo \"".$grupo["nombre"]."\"<br>";
		for($i=0; $i<count($semillas); $i++){
			//echo "Deteniendo colecta de \"".$semillas[$i]["url"]."\"<br>";
			detener_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin);
		}
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="detener_colecta_dominio"){
	//solo se necesita el id_dominio
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else if(!$colecta_activa_dominio[$id_dominio]){
		echo "No existen colectas activas de este dominio.<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$semillas=filtrar_semillas_dominio($estructura, $id_dominio);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Deteniendo colectas del dominio \"".$dominio["nombre"]."\"<br>";
		for($i=0; $i<count($semillas); $i++){
			//echo "Deteniendo colecta de \"".$semillas[$i]["url"]."\"<br>";
			detener_colecta($prefijo, $semillas[$i], $minicow, $ruta_bin);
		}
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="detener_colecta_semilla"){
	//solo se necesita el id_semilla
	if(!existe_semilla($db, $id_semilla)){
		echo "Valores Incorrectos<br>";
	}
	else if(!$colecta_activa_semilla[$id_semilla]){
		echo "La colecta de esta semilla no esta activa.<br>";
	}
	else{
		$semilla=filtrar_semilla($estructura, $id_semilla);
		
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Deteniendo colectas de \"".$semilla["url"]."\"<br>";
		detener_colecta($prefijo, $semilla, $minicow, $ruta_bin);
		echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		die();
	}
}
else if($accion=="cancelar"){
	$super_tag=0;
	$tag=0;
}

//---------- Verificacion de Colectas activas ----------
$colecta_activa_grupo=array();
$colecta_activa_dominio=array();
$colecta_activa_semilla=array();
$debug=false;
verificar_colectas_activas($estructura, $prefijo, $ruta_bin, $minicow);
$debug=false;

//---------- Verificacion de estado de colectas ----------
$debug=false;
$colecta_correcta_grupo=array();
$colecta_correcta_dominio=array();
$colecta_correcta_semilla=array();
verificar_estado_colectas($estructura, $prefijo, $ruta_colecta, $extensiones_colecta);
$debug=false;

//---------- Verificacion de colectas de los hijos----------
$colecta_hijos_grupo=array();
$colecta_hijos_dominio=array();
$debug=false;
verificar_colectas_hijos($estructura, $colecta_correcta_semilla);
$debug=false;

//---------- Verificacion de numero de semillas----------
$numero_semillas_grupo=array();
$numero_semillas_dominio=array();
$debug=false;
verificar_numero_semillas($estructura);
$debug=false;

//---------- Verificacion de colectas terminadas----------
$colecta_terminada_grupo=array();
$colecta_terminada_dominio=array();
$colecta_terminada_semilla=array();
$debug=false;
verificar_colectas_terminadas($estructura, $prefijo, $ruta_colecta, $numero_semillas_grupo, $numero_semillas_dominio);
$debug=false;

mysql_close($db);
html_fin_mensajes();


$titulo="Control de Colectas";
$opciones="Analisis de Informacion";
$elementos=array();

$elemento["nombre"]="Busquedas";
$elemento["ruta"]="control_busquedas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Logs";
$elemento["ruta"]="control_logs.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Detalles";
$elemento["ruta"]="control_colectas_detalle.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:60;\" colspan=2>&nbsp;</th>
	<th style=\"width:250;\">Semilla</th>
	<th style=\"width:110;\">Estado</th>
	<th style=\"width:360;\">Acciones</th>
	<th style=\"width:20;\" colspan=2>&nbsp;</th>
</tr>";
	
$color_usado=0;
$color=array("rgb(200, 200, 200)", "rgb(230, 230, 230)");
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$color_usado=($color_usado+1)%2;
	
	echo "<tr height=$separacion_grupos><td colspan=7></td></tr>";
	
	echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
	
	echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";
	
	echo "<td class=borde_grupo_02>";
	if(strlen($grupos[$i]["nombre"])>25)
		$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
	else
		$texto=$grupos[$i]["nombre"];
	echo "".$texto."";
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	if($colecta_activa_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_amarillo>Colectando</span>";
	}
	else if($colecta_correcta_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_verde>Correcta</span>";
	}
	else if($colecta_hijos_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_amarillo>Incompleta</span>";
	}
	else{
		echo "<span class=texto_rojo>Vacia</span>";
	}
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center>";
	
	echo "<table  cellpadding=0 cellspacing=0 border=0 width=360>";
	echo "<tr align=center valign=center>";
	echo "<td width=150 style=\"font-size:12px; font-weight: bold;\">Colecta de Grupo :</td>";
	if($colecta_activa_grupo[$grupos[$i]["id"]]){
		//Grupo Activo (al menos una semilla)
		echo "<td align=center width=210>";
		echo "<a href=?accion=detener_colecta_grupo&id_grupo=".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/detener_grupo.png height=$alto_detener width=$ancho_detener>";
		echo "</a>";
		echo "</td>";
	}
	else if($colecta_correcta_grupo[$grupos[$i]["id"]]
		|| $colecta_hijos_grupo[$grupos[$i]["id"]]){
		//Todo Ok o Algunas semillas Vacias
		
		echo "<td align=center width=70>";
		echo "<a href=?accion=iniciar_colecta_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Continuar";
		echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
		echo "</a>";
		echo "</td>";
		
		echo "<td align=center width=70>";
		if($colecta_terminada_grupo[$grupos[$i]["id"]]){
			echo "<span style=\"font-size: 12px;\">Terminada</span>";
		}
		else{
			echo "<a href=?accion=continuar_colecta_grupo&id_grupo=".$grupos[$i]["id"].">";
			echo "<img border=0 src=$estilos/continuar.png height=$alto_continuar width=$ancho_continuar>";
			echo "</a>";
		}
		echo "</td>";
		
		echo "<td align=center width=70>";
		echo "<a href=?accion=eliminar_colecta_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Reiniciar";
		echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
		echo "</a>";
		echo "</td>";
	}
	else if($numero_semillas_grupo[$grupos[$i]["id"]]){
		//Colecta Vacia
		echo "<td align=center width=210>";
		echo "<a href=?accion=iniciar_colecta_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Iniciar Grupo";
		echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
		echo "</a>";
		echo "</td>";
	}
	else{
		//Grupo Vacio
		echo "<td align=center width=210>";
		echo "No es Posible";
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";
	
	echo "</td>";
	
	echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
	
	echo "</tr>";
	
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		
		echo "<tr height=$separacion_dominios>";
		echo "<td>";
		echo "<img src=$estilos/extension_03.png height=$separacion_dominios width=$ancho_borde_izq></img>";
		echo "</td>";
		echo "<td colspan=7></td>";
		echo "</tr>";
		
		echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
		
		echo "<td align=left>";
		if($j==count($dominios)-1){
			echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		else{
			echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_01>&nbsp;</td>";
		
		echo "<td class=borde_dominio_02>";
		if(strlen($dominios[$j]["nombre"])>25)
			$texto=substr($dominios[$j]["nombre"], 0, 25)."...";
		else
			$texto=$dominios[$j]["nombre"];
		echo "".$texto."";
		echo "</td>";
		
		echo "<td class=borde_dominio_02>";
		if($colecta_activa_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_amarillo>Colectando</span>";
		}
		else if($colecta_correcta_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_verde>Correcta</span>";
		}
		else if($colecta_hijos_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_amarillo>Incompleta</span>";
		}
		else{
			echo "<span class=texto_rojo>Vacia</span>";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_02 align=center>";
		
		echo "<table  cellpadding=0 cellspacing=0 border=0 width=360>";
		echo "<tr align=center valign=center>";
		echo "<td width=150 style=\"font-size:12px; font-weight: bold;\">Colecta de Dominio :</td>";
		if($colecta_activa_dominio[$dominios[$j]["id"]]){
			//Grupo Activo (al menos una semilla)
			echo "<td align=center width=210>";
			echo "<a href=?accion=detener_colecta_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Detener Dominio";
			echo "<img border=0 src=$estilos/detener_dominio.png height=$alto_detener width=$ancho_detener>";
			echo "</a>";
			echo "</td>";
		}
		else if($colecta_correcta_dominio[$dominios[$j]["id"]]
			|| $colecta_hijos_dominio[$dominios[$j]["id"]]){
			//Todo Ok o Algunas semillas Vacias
			
			echo "<td align=center width=70>";
			echo "<a href=?accion=iniciar_colecta_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Continuar";
			echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
			echo "</a>";
			echo "</td>";
			
			echo "<td align=center width=70>";
			if($colecta_terminada_dominio[$dominios[$j]["id"]]){
				echo "<span style=\"font-size: 12px;\">Terminada</span>";
			}
			else{
				echo "<a href=?accion=continuar_colecta_dominio&id_dominio=".$dominios[$j]["id"].">";
				//echo "Continuar";
				echo "<img border=0 src=$estilos/continuar.png height=$alto_continuar width=$ancho_continuar>";
				echo "</a>";
			}
			echo "</td>";
		
			echo "<td align=center width=70>";
			echo "<a href=?accion=eliminar_colecta_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Reiniciar";
			echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
			echo "</a>";
			echo "</td>";
		}
		else if($numero_semillas_dominio[$dominios[$j]["id"]]){
			//Colecta Vacia
			echo "<td align=center width=210>";
			echo "<a href=?accion=iniciar_colecta_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Iniciar Dominio";
			echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
			echo "</a>";
			echo "</td>";
		}
		else{
			//Grupo Vacio
			echo "<td align=center width=210>";
			echo "No es Posible";
			echo "</td>";
		}
		echo "</tr>";
		echo "</table>";
		
		echo "</td>";
		
		echo "<td class=borde_dominio_03>&nbsp;</td>";
		echo "<td width=$ancho_borde_der bgcolor=white></td>";
		echo "</tr>";
		
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			
			echo "<tr style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
			
			echo "<td align=left>";
			if($j==count($dominios)-1){
				echo "<img src=$estilos/extension_04.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			echo "</td>";
			
			echo "<td align=left>";
			if($k==count($semillas)-1){
				echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
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
			if($colecta_activa_semilla[$semillas[$k]["id"]]){
				echo "<span class=texto_amarillo>Colectando</span>";
			}
			else if($colecta_correcta_semilla[$semillas[$k]["id"]]){
				echo "<span class=texto_verde>Correcta</span>";
			}
			else{
				echo "<span class=texto_rojo>Vacia</span>";
			}
			echo "</td>";
			
			echo "<td align=center>";
			
			echo "<table  cellpadding=0 cellspacing=0 border=0 width=360>";
			echo "<tr align=center valign=center>";
			echo "<td width=150 style=\"font-size:12px; font-weight: bold;\">Colecta de Semilla :</td>";
			if($colecta_activa_semilla[$semillas[$k]["id"]]){
				//Activa
				echo "<td align=center width=210>";
				echo "<a href=?accion=detener_colecta_semilla&id_semilla=".$semillas[$k]["id"].">";
				//echo "Detener Semilla";
				echo "<img border=0 src=$estilos/detener_semilla.png height=$alto_detener width=$ancho_detener>";
				echo "</a>";
				echo "</td>";
				
			}
			else if($colecta_correcta_semilla[$semillas[$k]["id"]]){
				//Todo Ok
				
				echo "<td align=center width=70>";
				echo "<a href=?accion=iniciar_colecta_semilla&id_semilla=".$semillas[$k]["id"].">";
				//echo "Reiniciar";
				echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
				echo "</a>";
				echo "</td>";
				
				echo "<td align=center width=70>";
				if($colecta_terminada_semilla[$semillas[$k]["id"]]){
					echo "<span style=\"font-size: 12px;\">Terminada</span>";
				}
				else{
					echo "<a href=?accion=continuar_colecta_semilla&id_semilla=".$semillas[$k]["id"].">";
					//echo "Continuar";
					echo "<img border=0 src=$estilos/continuar.png height=$alto_continuar width=$ancho_continuar>";
					echo "</a>";
				}
				echo "</td>";
				
				echo "<td align=center width=70>";
				echo "<a href=?accion=eliminar_colecta_semilla&id_semilla=".$semillas[$k]["id"].">";
				echo "<img border=0 src=$estilos/eliminar.png height=$alto_eliminar_vacio width=$ancho_eliminar_vacio>";
				echo "</a>";
				echo "</td>";
				
			}
			else{
				//Vacia
				echo "<td align=center width=210>";
				echo "<a href=?accion=iniciar_colecta_semilla&id_semilla=".$semillas[$k]["id"].">";
				//echo "Colectar Semilla";
				echo "<img border=0 src=$estilos/iniciar.png height=$alto_iniciar width=$ancho_iniciar>";
				echo "</a>";
				echo "</td>";
			}
			echo "</tr>";
			echo "</table>";
			
			echo "</td>";
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			
			echo "</tr>";
		
		
		}
		
	}
}

?>

</table>

</body>

</html>
