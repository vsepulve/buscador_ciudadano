<?php

require_once("config.ini");

require_once("sesion.php");

$accion=trim($_POST["post_accion"]);

echo "<html>";
html_head("Actualizacion de Colectas");
echo "<body>";
html_menu_superior_01(4);
html_inicio_mensajes();

echo "Prueba de CSV<br>";

echo "<br>";

echo "<form id=form_01 action=? method=post enctype=multipart/form-data>";
echo "<table cellpadding=0 cellspacing=0 border=0>";

echo "<tr height=40 valign=center>";
echo "<td>";
echo "Archivo CSV&nbsp;&nbsp;";
echo "</td>";
echo "<td>";
echo "<input type=hidden name=post_accion value=cargar>";
echo "<input type=hidden name=max_file_size value=".$maximo_archivo.">";
echo "<input name=archivo type=file>";
echo "</td>";
echo "</tr>";

echo "<tr height=40 valign=center>";
echo "<td colspan=2 align=center>";
echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
echo "&nbsp;&nbsp;&nbsp;";
echo "<a href=?><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";

function csv_obtener_fila($linea){
	$charlist=" \"\t\n\r\0\x0B";
	$fila=explode(",", $linea);
	for($i=0; $i<count($fila); $i++){
		$fila[$i]=trim($fila[$i], $charlist);
	}
	return $fila;
}

function csv_fila_vacia($fila){
	for($i=0; $i<count($fila); $i++){
		if(strlen($fila[$i])){
			return false;
		}
	}
	return true;
}

if($accion=="cargar"){
	
	$debug=true;
	echo "Inicio Carga...<br>";
	$nombre_archivo=$HTTP_POST_FILES['archivo']['name'];
	print_debug("Nombre: \"$nombre_archivo\"");
	$ruta_archivo=nueva_ruta_archivo($ruta_respaldos, $nombre_archivo);
	$tipo_archivo=$HTTP_POST_FILES['archivo']['type'];
	print_debug("Tipo: \"$tipo_archivo\"");
	$tamaño_archivo=$HTTP_POST_FILES['archivo']['size'];
	print_debug("Tama&ntilde;o: \"$tamaño_archivo\"");
	$debug=true;
	
	if(!$HTTP_POST_FILES['archivo']['name']){
		
	}
	else if($tamaño_archivo>$maximo_archivo){
		echo "Tama&ntilde;o del archivo mayor que el permitido.<br>";
	}
	else if(strcasecmp($HTTP_POST_FILES['archivo']['type'], "text/csv")!=0
		&& strcasecmp($HTTP_POST_FILES['archivo']['type'], "csv")!=0){
		echo "Tipo de archivo no permitido.<br>";
	}
	else{
	
		$lector=fopen($HTTP_POST_FILES['archivo']['tmp_name'], "r");
		if($lector){
			$contador=0;
			//Revisar el header
			$linea=fgets($lector);
			$fila=csv_obtener_fila($linea);
			$contador++;
			if(count($fila)!=5
				|| strlen($fila[0])<1
				|| strlen($fila[1])<1
				|| strlen($fila[2])<1
				|| strlen($fila[3])<1
				|| strlen($fila[4])<1){
				echo "Fila $contador Incorrecta<br>";
			}
			$estructura=array();
			$estructura["grupos"][]=array("nombre"=>$fila[2]);
			$estructura["grupos"][]=array("nombre"=>$fila[3]);
			$dominios_agregados=-1;
			
			while(($linea=fgets($lector))!=null){
				$fila=csv_obtener_fila($linea);
				$contador++;
				if(csv_fila_vacia($fila)){
					print_debug("Fila $contador vacia.");
				}
				else if(strlen($fila[0])
					&& !strlen($fila[1])
					&& !strlen($fila[2])
					&& !strlen($fila[3])
					&& !strlen($fila[4])
					){
					print_debug("Fila $contador inutil.");
				}
				else if(strlen($fila[1])){
					//Inicio de Institucion
					$dominios_agregados++;
					$estructura["dominios"][0][]=array("nombre"=>$fila[1]);
					$estructura["dominios"][1][]=array("nombre"=>$fila[1]);
					
					if(strlen($fila[2])){
						$estructura["semillas"][0][$dominios_agregados][]=array("url"=>$fila[2]);
					}
					if(strlen($fila[3])){
						$estructura["semillas"][1][$dominios_agregados][]=array("url"=>$fila[3]);
					}
				}
				else{
					//Continua Institucion
					if(strlen($fila[2])){
						$estructura["semillas"][0][$dominios_agregados][]=array("url"=>$fila[2]);
					}
					if(strlen($fila[3])){
						$estructura["semillas"][1][$dominios_agregados][]=array("url"=>$fila[3]);
					}
					
				}
			}
			
			fclose($lector);
		}
	}
	
	
}

echo "<br>";

$semillas_nuevas=array();
$grupos=$estructura["grupos"];
if($accion=="cargar" || count($grupos)==2){

/*
$debug=true;
echo "<h1>Estructura Nueva</h1>";
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		print_debug("Grupo \"".$grupos[$i]["nombre"]."\"");
		//Crear Grupo
		$puerto=obtener_puerto_random($db);
		//ingresar_grupo($db, $grupos[$i]["nombre"], $puerto);
		
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			print_debug("->Dominio \"".$dominios[$j]["nombre"]."\"");
			//Crear Dominio
			$puerto=obtener_puerto_random($db);
			
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				$semillas_nuevas[]=$semillas[$k]["url"];
				print_debug("--->Semilla \"".$semillas[$k]["url"]."\"");
				//Crear Semilla
				
			}
		}
		print_debug("");
	}
	$debug=false;
*/
}
echo "<br>";
echo "Ingresando Estructura... (bloqueado)<br>";
//ingresar_estructura($db, $estructura);
echo "Estructura Ingresada<br>";

function obtener_semillas_nuevas($estructura, $nombre_grupo, $nombre_dominio){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if(strcmp($grupos[$i]["nombre"], $nombre_grupo)===0){
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				if(strcmp($dominios[$j]["nombre"], $nombre_dominio)===0){
					return $estructura["semillas"][$i][$j];
				}
			
			}
		}
	}
	return array();
}

$estructura_antigua=obtener_estructura($db);
//
$estructura_antigua["grupos"][0]["nombre"]="Sitio";
$estructura_antigua["grupos"][1]["nombre"]="Transparencia";
//

echo "<h1>Comparacion</h1>";
$debug=true;
$semillas_antiguas=array();
$grupos=$estructura_antigua["grupos"];
for($i=0; $i<count($grupos) && $i<2; $i++){
	echo "<h3>Grupo \"".$grupos[$i]["nombre"]."\"</h3>";
	$dominios=$estructura_antigua["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		//echo "->Dominio \"".$dominios[$j]["nombre"]."\"<br>";
		$mostrar="";
		$semillas=$estructura_antigua["semillas"][$i][$j];
		$urls=null;
		for($k=0; $k<count($semillas); $k++){
			$urls[]=$semillas[$k]["url"];
		}
		$semillas_nuevas=obtener_semillas_nuevas($estructura, $grupos[$i]["nombre"], $dominios[$j]["nombre"]);
		$urls_nuevas=null;
		for($k=0; $k<count($semillas_nuevas); $k++){
			$urls_nuevas[]=$semillas_nuevas[$k]["url"];
		}
		for($k=0; $k<count($semillas); $k++){
			if(!$urls_nuevas || !in_array($semillas[$k]["url"], $urls_nuevas)){
				$mostrar.="(old)--> \"".$semillas[$k]["url"]."\"<br>";
			}
		}
		for($k=0; $k<count($semillas_nuevas); $k++){
			if(!$urls || !in_array($semillas_nuevas[$k]["url"], $urls)){
				$mostrar.="(new)--> \"".$semillas_nuevas[$k]["url"]."\"<br>";
			}
		}
		if($mostrar){
			echo "->Dominio \"".$dominios[$j]["nombre"]."\"<br>";
			echo $mostrar;
		}
	}
}
$debug=false;

echo "<br>";
/*
$urls_antiguas=array_diff($semillas_antiguas, $semillas_nuevas);
echo "<h1>Semillas Antiguas adicionales</h1>";
for($i=0; $i<count($urls_antiguas); $i++){
	if(strlen($urls_antiguas[$i])>1){
		echo $urls_antiguas[$i]."<br>";
	}
}

*/

mysql_close($db);
html_fin_mensajes();


echo "</body>";

echo "</html>"

?>
