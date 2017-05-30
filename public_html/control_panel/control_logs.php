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

$id_dominio=$_POST["id_dominio"];
if(!ctype_digit($id_dominio))
	$id_dominio=0;

$limite=$_POST["limite"];
if(!ctype_digit($limite))
	$limite=0;

$patrones_descartados[]=".gif";
$patrones_descartados[]=".png";
$patrones_descartados[]=".jpg";
$patrones_descartados[]=".jpeg";

$lineas_correctas=array();
$tamaño_log=array();
$lineas_omitidas=array();
$semillas=null;

if($accion=="mostrar_log" && $limite>0){

	$dominio=filtrar_dominio($estructura, $id_dominio);
	//echo "Dominio: ".$dominio["nombre"]."<br>";
	//echo "<br>";
	$semillas=filtrar_semillas_dominio($estructura, $dominio["id"]);
	$contador=0;
	for($i=0; $i<count($semillas); $i++){
		//echo "Log de: \"".$semillas[$i]["url"]."\"";
		$nombre_log=$prefijo."-".$semillas[$i]["id"]."_.log";
		if(file_exists($ruta_colecta."/".$nombre_log)){
			$bytes=filesize($ruta_colecta."/".$nombre_log);
			$tamaño_log[$semillas[$i]["id"]]=$bytes;
			//echo "<br>";
			$log=fopen($ruta_colecta."/".$nombre_log, "r");
			$numero_lineas=0;
			while(fgets($log)){
				$numero_lineas++;
			}
			fseek($log, 0);
			$omitir=0;
			if($numero_lineas>$limite)
				$omitir=$numero_lineas-$limite;
			//echo "Omitir : $omitir<br>";
			$lineas_omitidas[$semillas[$i]["id"]]=$omitir;
			for($j=0; $j<$omitir; $j++){
				fgets($log);
			}
			while($linea=fgets($log)){
				$linea_guardar="";
				$arreglo=split(" ", $linea);
				for($j=0; $j<count($arreglo); $j++){
					if(strpos($arreglo[$j], "http")===0){
						//es direccion
						$omitir=false;
						for($k=0; $k<count($patrones_descartados); $k++){
							$posicion=strpos($linea, $patrones_descartados[$k]);
							if($posicion){
								$omitir=true;
								break;
							}
						}
						if($omitir){
							//omitir por patrones descartados
							//echo " ".$arreglo[$j];
							$linea_guardar.=" ".$arreglo[$j];
						}
						else{
							//Link aceptable
							//echo " ";
							//echo "<a href=".$arreglo[$j]." target=ventana_".($contador++).">";
							//echo "".$arreglo[$j];
							//echo "</a>";
							$linea_guardar.=" ";
							$linea_guardar.="<a href=".$arreglo[$j]." target=ventana_".($contador++).">";
							$linea_guardar.="".convertir_lineas($arreglo[$j], $maximo_largo_linea);
							$linea_guardar.="</a>";
						}
					}
					else{
						//no es direccion
						//echo " ".$arreglo[$j];
						$linea_guardar.=" ".$arreglo[$j];
					}
				}
				//echo "<br>";
				$lineas_correctas[$semillas[$i]["id"]][]=$linea_guardar;
			}
			fclose($log);
		}
		else{
			//echo "<br>No existe el log, colecta vac&iacute;a.<br>";
		}
		//echo "<br>";
	}
	
}
//echo "<br>";

mysql_close($db);
html_fin_mensajes();

$titulo="Analisis de Logs";
$opciones="Analisis de Informacion";
$elementos=array();

$elemento["nombre"]="Busquedas";
$elemento["ruta"]="control_busquedas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Colectas";
$elemento["ruta"]="control_colectas.php";
$elemento["principal"]=true;
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

echo "<th width=400>";
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














/*
if($id_enlace==0)
	echo "<option value=0 selected>-- Escoga un Dominio --";
else
	echo "<option value=0>-- Escoga un Dominio --";
for($i=0; $i<count($enlaces); $i++){
	if($id_enlace==$enlaces[$i]["id"]){
		echo "<option value=".$enlaces[$i]["id"]." selected>";
		if(strlen($enlaces[$i]["nombre"])>25)
			$texto=substr($enlaces[$i]["nombre"], 0, 25)."...";
		else
			$texto=$enlaces[$i]["nombre"];
		echo "".$texto."";
	}
	else{
		echo "<option value=".$enlaces[$i]["id"].">";
		if(strlen($enlaces[$i]["nombre"])>25)
			$texto=substr($enlaces[$i]["nombre"], 0, 25)."...";
		else
			$texto=$enlaces[$i]["nombre"];
		echo "".$texto."";
	}
}


echo "</select>";
echo "</th>";

echo "<th width=200>";
echo "<select name=id_grupo>";
if($id_grupo==0)
	echo "<option value=0 selected>-- Escoga un Grupo --";
else
	echo "<option value=0>-- Escoga un Grupo --";
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	if($id_grupo==$grupos[$i]["id"]){
		echo "<option value=".$grupos[$i]["id"]." selected>";
		if(strlen($grupos[$i]["nombre"])>25)
			$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
		else
			$texto=$grupos[$i]["nombre"];
		echo "".$texto."";
	}
	else{
		echo "<option value=".$grupos[$i]["id"].">";
		if(strlen($grupos[$i]["nombre"])>25)
			$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
		else
			$texto=$grupos[$i]["nombre"];
		echo "".$texto."";
	}
}
echo "</select>";
echo "</th>";

*/











echo "<th width=200>";
echo "<select name=limite>";
if($limite==0)
	echo "<option value=0 selected>-- Numero de Lineas --";
else
	echo "<option value=0 >-- Numero de Lineas --";
$limites=array(10, 50, 100, 500, 1000, 5000, 10000);
for($i=0; $i<count($limites); $i++){
	if($limite==$limites[$i])
		echo "<option value=".$limites[$i]." selected>".$limites[$i];
	else
		echo "<option value=".$limites[$i].">".$limites[$i];
}
echo "</select>";
echo "</th>";

echo "<th width=60>";
echo "<input type=hidden name=accion value=mostrar_log>";
echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
echo "</th>";

echo "<th width=60>";
echo "<a href=?><img border=0 src=$estilos/cancelar.png height=18 width=58></a>";
echo "</th>";

echo "</tr>";
echo "</table>";

echo "</th>";
echo "<th style=\"width:10;\">&nbsp;</th>";
echo "<th style=\"width:10;\">&nbsp;</th>";
echo "</tr>";

if($semillas && $limite>0){
	for($i=0; $i<count($semillas); $i++){
		$color_usado=($color_usado+1)%2;
	
		echo "<tr height=$separacion_grupos><td colspan=5></td></tr>";
	
		echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
		echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";
	
		echo "<td class=borde_grupo_02 align=left>";
		echo $semillas[$i]["url"]."";
		echo "</td>";
	
		echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
	
		echo "</tr>";
	
		echo "<tr height=$separacion_dominios>";
		echo "<td colspan=5></td>";
		echo "</tr>";
	
		$lineas=$lineas_correctas[$semillas[$i]["id"]];
		//echo count($lineas)." lineas<br>";
		for($j=0; $j<count($lineas); $j++){
			echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
			echo "<td align=left width=$ancho_borde_izq bgcolor=white>&nbsp;</td>";
		
			echo "<td colspan=3 align=left>";
			echo (1+$j+$lineas_omitidas[$semillas[$i]["id"]])."&nbsp;&nbsp;".$lineas[$j];
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

}







echo "</table>";
echo "</form>";

echo "<br><br><br><br><br><br><br><br><br><br>";

echo "</body>";
echo "</html>";

?>
