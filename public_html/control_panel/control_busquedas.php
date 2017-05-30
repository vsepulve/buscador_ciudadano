<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Busqueda de Transparencia");
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

$accion=trim($_POST["accion"]);
/*
$id_grupo=$_POST["id_grupo"];
if(!ctype_digit($id_grupo))
	$id_grupo=0;

$id_enlace=$_POST["id_enlace"];
if(!ctype_digit($id_enlace))
	$id_enlace=0;
*/
$id_dominio=$_POST["id_dominio"];
if(!ctype_digit($id_dominio))
	$id_dominio=0;

$patron=trim($_POST["patron"]);

//$patron="transp";
$patrones_descartados[]=".gif";
$patrones_descartados[]=".png";
$patrones_descartados[]=".jpg";
$patrones_descartados[]=".jpeg";
$lineas_correctas=array();

if($accion=="buscar" && existe_dominio($db, $id_dominio) && strlen($patron)>3){

	$dominio=filtrar_dominio($estructura, $id_dominio);
	$debug=false;
	print_debug("Busco en: \"".$dominio["nombre"]."\"");
	
	$lineas_agregadas=array();
	$semillas=filtrar_semillas_dominio($estructura, $dominio["id"]);
	for($j=0; $j<count($semillas); $j++){
		$nombre_log=$prefijo."-".$semillas[$j]["id"]."_.log";
		//echo "Log: $nombre_log<br>";
		if(file_exists($ruta_colecta."/".$nombre_log)){
			$log=fopen($ruta_colecta."/".$nombre_log, "r");
			$contador=0;
			while($linea=fgets($log)){
				$contador++;
				$omitir=false;
				for($k=0; $k<count($patrones_descartados); $k++){
					$posicion=strpos($linea, $patrones_descartados[$k]);
					if($posicion){
						$omitir=true;
						break;
					}
				}
				if(!$omitir){
					$arreglo=split(" ", $linea);
					for($k=0; $k<count($arreglo); $k++){
						$posicion=strpos($arreglo[$k], $patron);
						if($posicion===0 || $posicion>0){
							//echo "buscando \"".$arreglo[$k]."\" en agregadas...";
							if(!in_array($arreglo[$k], $lineas_agregadas)){
								//echo " gregada<br>";
								$lineas_agregadas[]=$arreglo[$k];
						}
							else{
								//echo " ya estaba<br>";
							}
						}
					}
				}
			}
			print_debug("$contador lineas revisadas");
			fclose($log);
			
		}
	}
	for($k=0; $k<count($lineas_agregadas); $k++){
		print_debug($lineas_agregadas[$k]);
	}
	$lineas_correctas[$dominio["id"]]=$lineas_agregadas;
	
}

		if(strlen($enlaces[$i]["nombre"])>25)
			$texto=substr($enlaces[$i]["nombre"], 0, 25)."...";
		else
			$texto=$enlaces[$i]["nombre"];

mysql_close($db);
html_fin_mensajes();

$titulo="Busqueda de Patrones";
$opciones="Analisis de Informacion";
$elementos=array();

$elemento["nombre"]="Colectas";
$elemento["ruta"]="control_colectas.php";
$elemento["principal"]=true;
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
	
echo "<form id=form_01 action=? method=post>";
echo "<table cellpadding=0 cellspacing=0 border=0>";
echo "<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >";

echo "<th style=\"width:30;\">&nbsp;</th>";
echo "<th style=\"width:30;\">&nbsp;</th>";
echo "<th style=\"width:720;\">";

echo "<table cellpadding=0 cellspacing=0 border=0 width=720>";
echo "<tr>";

echo "<th width=400 align=center>";
echo "<select name=id_dominio>";
if($id_dominio==0)
	echo "<option value=0 selected>-- Escoga un Dominio --";
else
	echo "<option value=0>-- Escoga un Dominio --";

$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		//preparo el nombre "grupo - dominio"
		if(strlen($grupos[$i]["nombre"])>15)
			$texto=substr($grupos[$i]["nombre"], 0, 15)."...";
		else
			$texto=$grupos[$i]["nombre"];
		if(strlen($dominios[$j]["nombre"])>25)
			$texto=$texto." - ".substr($dominios[$j]["nombre"], 0, 25)."...";
		else
			$texto=$texto." - ".$dominios[$j]["nombre"];
		//reviso la marca
		if($id_dominio==$dominios[$j]["id"]){
			echo "<option value=".$dominios[$j]["id"]." selected>";
		}
		else{
			echo "<option value=".$dominios[$j]["id"].">";
		}
		echo "".$texto."";
	}
}

echo "</select>";
echo "</th>";

echo "<th width=100 align=center>";
echo "Patron: ";
echo "</th>";

echo "<th width=100 align=center>";
echo "<input type=text size=10 name=patron value=$patron>";
echo "</th>";

echo "<th width=60>";
echo "<input type=hidden name=accion value=buscar>";
echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
echo "</th>";

echo "<th width=60>";
echo "<a href=? ><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
echo "</th>";

echo "</tr>";
echo "</table>";

echo "</th>";
echo "<th style=\"width:10;\">&nbsp;</th>";
echo "<th style=\"width:10;\">&nbsp;</th>";
echo "</tr>";


$contador=0;
$dominio=filtrar_dominio($estructura, $id_dominio);
	if($dominio){
		//echo "Desplegando...<br>";
		
		$color_usado=($color_usado+1)%2;
	
		echo "<tr height=$separacion_grupos><td colspan=5></td></tr>";
	
		echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
		echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";
	
		echo "<td class=borde_grupo_02 align=left>";
		echo $dominio["nombre"]."";
		echo "</td>";
	
		echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
	
		echo "</tr>";
	
	
		$lineas=$lineas_correctas[$dominio["id"]];
	
		echo "<tr height=$separacion_dominios>";
		echo "<td colspan=5></td>";
		echo "</tr>";
	
		for($j=0; $j<count($lineas); $j++){
			echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
			echo "<td align=left width=$ancho_borde_izq bgcolor=white>&nbsp;</td>";
		
			echo "<td colspan=3 align=left>";
			echo (1+$j)."&nbsp;&nbsp;";
			echo "<a href=".$lineas[$j]." target=ventana_".($contador++).">".convertir_lineas($lineas[$j], $maximo_largo_linea)."</a>";
			echo "</td>";
		
			echo "<td width=$ancho_borde_der bgcolor=white>&nbsp;</td>";
		
			echo "</tr>";
				
		}
	
		echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
		echo "<td align=left width=$ancho_borde_izq bgcolor=white>&nbsp;</td>";
		echo "<td colspan=3 align=left>&nbsp;</td>";
		echo "<td width=$ancho_borde_der bgcolor=white>&nbsp;</td>";
		echo "</tr>";
	
	
	}

		

echo "</table>";
echo "</form>";

echo "<br><br><br><br><br><br><br><br><br><br>";


echo "</body>";
echo "</html>";

?>
