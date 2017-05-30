<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Respaldos");
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

$accion=addslashes(trim($_GET["accion"]));

$post_accion=trim($_POST["post_accion"]);
if($post_accion!=null){
	$accion=$post_accion;
}

$respaldo_guardado=trim($_POST["respaldo_guardado"]);

$error=$_GET["error"];
if(!ctype_digit($error))
	$error=0;
	
if($error==1){
	//Archivo no encontrado
	echo "<h3>Respaldo no encontrado</h3>";
}

$maximo_archivo=1000000;

if($accion=="inicio_respaldar"){
	
	echo "<table cellpadding=0 cellspacing=0 border=0>";
	echo "<tr valign=center align=center>";
	
	echo "<td align=center width=600>";
	echo "Iniciando Respaldo...<br>";
	$fecha=date("Y-m-d_H-i-s", mktime());
	$nombre_archivo="respaldo_$fecha.txt";
	$ruta_archivo=$ruta_respaldos."/".$nombre_archivo;
	//echo "Guardando en \"$ruta_archivo\"<br>";
	guardar_estructura($db, $ruta_archivo);
	
	//Compresion y limpieza
	require_once("zipfile.inc.php");
	$zip=new zipfile();
	$zip->add_file(implode("",file($ruta_archivo)), $nombre_archivo);
	$nombre_respaldo=substr($nombre_archivo, 0, -4).".res";
	$salida=fopen($ruta_respaldos."/".$nombre_respaldo, "wb");
	if($salida){
		fwrite($salida, $zip->file());
		fclose($salida);
	}
	unlink($ruta_archivo);
	
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr align=center>";
	
	echo "<td align=center>";
	echo "<a href=descargar_respaldo?nombre_archivo=$nombre_respaldo>Descargar Respaldo</a>";
	echo "</td>";
	
	echo "</tr>";
	
	echo "</table>";
		
}
else if($accion=="inicio_cargar"){

	echo "<form id=form_01 action=? method=post enctype=multipart/form-data>";
	echo "<table cellpadding=0 cellspacing=0 border=0>";
	echo "<tr valign=center align=center>";
	
	echo "<td align=center width=600>";
	echo "Este procedimiento eliminara la base de datos totalmente,<br>";
	echo "para reconstruirla en base a un archivo de respaldo.<br>";
	echo "<b>Todos los servicios seran desactivados,<br>";
	echo "y todas las colectas e indices seran borrados.</b><br>";
	echo "<br> &iquest; Est&aacute; seguro que desea continuar ? <br><br>";
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr align=center>";
	echo "<td align=center>";
	echo "<input type=hidden name=MAX_FILE_SIZE value=".$maximo_archivo.">";
	echo "Suba el Respaldo &nbsp;&nbsp;";
	echo "<input name=archivo type=file>";
	echo "<br>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr align=center>";
	echo "<td align=center>";
	echo "o Escogalo de la lista &nbsp;&nbsp;";
	echo "<select name=respaldo_guardado>";
	echo "<option value=0 selected>-- Escoga Respaldo --";
	
	$respaldos=scandir($ruta_respaldos);
	//for($i=0; $i<count($respaldos); $i++){
	for($i=count($respaldos)-1; $i>=0; $i--){
		if($respaldos[$i] && strpos($respaldos[$i], ".res")
			&& substr($respaldos[$i], 0, 1)!="."){
			echo "<option value=".($respaldos[$i]).">".$respaldos[$i];
		}
	}
	
	echo "</select>";
	echo "<br>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr align=center>";
	echo "<td align=center>";
	echo "<input type=hidden name=post_accion value=cargar>";
	echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href=?><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
	echo "</td>";
	
	echo "</tr>";
	
	echo "</table>";
	echo "</form>";
	die();
}
else if($accion=="cargar"){
	if($respaldo_guardado){
		//Usar respaldo guardado
		$ruta_respaldo=$ruta_respaldos."/".$respaldo_guardado;
		if(file_exists($ruta_respaldo)){
			echo "Cargando estructura de $respaldo_guardado<br>";
			$zip=new ZipArchive;
			if($zip->open($ruta_respaldo)===TRUE){
				$nombre_archivo=$zip->getNameIndex(0);
				$archivo=$zip->getStream($nombre_archivo);
				if($archivo){
					$nueva_estructura=cargar_estructura($db, $archivo);
				}
			}
		}
	}
	else{
		//usar Archivo subido
		$debug=false;
		//datos del arhivo
		$nombre_archivo=$HTTP_POST_FILES['archivo']['name'];
		print_debug("Nombre: \"$nombre_archivo\"");
		$ruta_archivo=nueva_ruta_archivo($ruta_respaldos, $nombre_archivo);
		$tipo_archivo=$HTTP_POST_FILES['archivo']['type'];
		print_debug("Tipo: \"$tipo_archivo\"");
		$tamaño_archivo=$HTTP_POST_FILES['archivo']['size'];
		print_debug("Tama&ntilde;o: \"$tamaño_archivo\"");
		$debug=false;
	
		//compruebo si las características del archivo son las que deseo
		if($tamaño_archivo>$maximo_archivo){
			echo "Tamaño del archivo mayor que el permitido.<br>";
		}
		else if(!(strpos($tipo_archivo, "application/zip")===0)
			&& !(strpos($tipo_archivo, "application/octet-stream")===0) ){
			echo "Tipo de archivo incorrecto.<br>";
		}
		else{
			if (move_uploaded_file($HTTP_POST_FILES['archivo']['tmp_name'], $ruta_archivo)){
				chmod($ruta_archivo, 0644);
				echo "Archivo guardado correctamente.<br>";
				//Revisar Archivo y Cargar estructura
				$zip=new ZipArchive;
				if($zip->open($ruta_archivo)===TRUE){
					$nombre_archivo=$zip->getNameIndex(0);
					$archivo=$zip->getStream($nombre_archivo);
					if($archivo){
						$nueva_estructura=cargar_estructura($db, $archivo);
					}
				}
				if(!$nueva_estructura){
					echo "No se pudo cargar la estructura<br>";
					//si el archivo existe, lo borro
					if(file_exists($ruta_archivo)){
						unlink($ruta_archivo);
					}
				}
			
			}
			else{
				echo "Errores al guardar el archivo.<br>";
			}
		}
	}
	
	if($nueva_estructura){
	$debug=false;
	$grupos=$nueva_estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		print_debug("G: ".$grupos[$i]["nombre"]."");
		$dominios=$nueva_estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			print_debug("D: ".$dominios[$j]["nombre"]."");
			$semillas=$nueva_estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				print_debug("S: ".$semillas[$k]["url"]." (".$semillas[$k]["reject"].")");
			}
		}
		print_debug("");
	}
	$debug=false;
	
	//Borrar Base de Datos
	$debug=true;
	echo "Borrando Base de Datos<br>";
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		//Eliminar el grupo
		print_debug("eliminar_grupo($db, ".$grupos[$i]["id"].")");
		eliminar_grupo($db, $grupos[$i]["id"]);
		print_debug("eliminar_indice($prefijo, \"G\", ".$grupos[$i]["id"].", $ruta_indice)");
		eliminar_indice($prefijo, "G", $grupos[$i]["id"], $ruta_indice);
		print_debug("eliminar_demonio($prefijo, \"G\", ".$grupos[$i]["id"].", $ruta_bin)");
		eliminar_demonio($prefijo, "G", $grupos[$i]["id"], $ruta_bin);
		
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			//Eliminar dominios
			print_debug("eliminar_dominio($db, ".$dominios[$j]["id"].")");
			eliminar_dominio($db, $dominios[$j]["id"]);
			print_debug("eliminar_indice($prefijo, \"D\", ".$dominios[$j]["id"].", $ruta_indice)");
			eliminar_indice($prefijo, "D", $dominios[$j]["id"], $ruta_indice);
			print_debug("eliminar_demonio($prefijo, \"D\", ".$dominios[$j]["id"].", $ruta_bin)");
			eliminar_demonio($prefijo, "D", $dominios[$j]["id"], $ruta_bin);
			
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				//Eliminar semillas
				print_debug("eliminar_semilla($db, ".$semillas[$k]["id"].")");
				eliminar_semilla($db, $semillas[$k]["id"]);
				print_debug("eliminar_colecta($prefijo, ".$semillas[$k]["id"].", $ruta_colecta)");
				eliminar_colecta($prefijo, $semillas[$k]["id"], $ruta_colecta, $ruta_logs);
			
			}
		}
		echo "<br>";
	}
	$debug=false;
	
	//Ingresar Nueva Estructura
	$debug=true;
	ingresar_estructura($db, $nueva_estructura);
	$debug=false;
	
	}
				
			
			
	
	
}











mysql_close($db);
html_fin_mensajes();


$titulo="Control de Respaldos";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Historial";
$elemento["ruta"]="control_historial.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Config.";
$elemento["ruta"]="control_configuracion.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:$ancho_borde_izq;\">&nbsp;</th>
	<th style=\"width:$ancho_borde_izq;\">&nbsp;</th>";
	
	echo "<th style=\"width:720;\" align=left>";
	
	echo "<table cellpadding=0 cellspacing=0 border=0 widht=450>";
	echo "<tr>";
	
	echo "<th width=150 align=center>Acciones</th>";

	echo "<th width=150 align=center>";
	echo "<a href=?accion=inicio_respaldar>";
	//echo "Respaldar";
	echo "<img border=0 src=$estilos/respaldar.png height=$alto_respaldar width=$ancho_respaldar></img>";
	echo "</a>";
	echo "</th>";

	echo "<th width=150 align=center>";
	echo "<a href=?accion=inicio_cargar>";
	//echo "Cargar";
	echo "<img border=0 src=$estilos/cargar_respaldo.png height=$alto_respaldar width=$ancho_respaldar></img>";
	echo "</a>";
	echo "</th>";

	echo "</tr>";
	echo "</table>";

	echo "</th>";
	
	echo "<th style=\"width:$ancho_borde_der;\">&nbsp;</th>
	<th style=\"width:$ancho_borde_der;\">&nbsp;</th>
</tr>";
echo "</table>";
/*
echo "<table cellpadding=0 cellspacing=0 border=0 widht=400>";
echo "<tr>";

echo "<td width=200 align=center>";
echo "<a href=?accion=inicio_respaldar>Respaldar</a>";
echo "</td>";

echo "<td width=200 align=center>";
echo "<a href=?accion=inicio_cargar>Cargar</a>";
echo "</td>";

echo "</tr>";
echo "</table>";
*/

echo "</body>";

echo "</html>";

?>
