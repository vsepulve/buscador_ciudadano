<?php

require_once("config.ini");

require_once("sesion.php");

$accion=trim($_POST["post_accion"]);
$id_dominio=trim($_POST["id_dominio"]);

echo "<html>";
html_head("Administracion de Configuracion");
echo "<body>";
html_menu_superior(-1);
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


if($accion=="cargar"){
	
	$dominio=filtrar_dominio($estructura, $id_dominio);
	echo "$id_dominio, ".$dominio["nombre"]."<br>";
	
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
	
	if(!$dominio){
		echo "El Dominio No Existe.<br>";
	}
	else if(!$HTTP_POST_FILES['archivo']['name']){
		
	}
	else if($tamaño_archivo>$maximo_archivo){
		echo "Tama&ntilde;o del archivo mayor que el permitido.<br>";
	}
	else if(strcasecmp($HTTP_POST_FILES['archivo']['type'], "text/plain")!=0
		&& strcasecmp($HTTP_POST_FILES['archivo']['type'], "txt")!=0){
		echo "Tipo de archivo no permitido.<br>";
	}
	else{
		
	
		$lector=fopen($HTTP_POST_FILES['archivo']['tmp_name'], "r");
		if($lector){
			
			$contador=1;
			while(($linea=fgets($lector))!=null){
				if(strlen($linea)>4){
					echo ($contador++)." ";
					echo "$linea<br>";
					ingresar_semilla($db, $linea, "", $id_dominio);
				}
			}
			
			
			fclose($lector);
		}
		
	}
	
	
	
	
	
	
	
	
}


echo "<br>";

echo "Carga Especial<br>";

echo "<br>";

echo "<form id=form_01 action=? method=post enctype=multipart/form-data>";
echo "<table cellpadding=0 cellspacing=0 border=0>";

echo "<tr height=40 valign=center>";
echo "<td>";
echo "Dominio&nbsp;/&nbsp;Archivo&nbsp;&nbsp;";
echo "</td>";
echo "<td>";
echo "<input type=text size=4 name=id_dominio>";
echo "</td>";
echo "<td>";
echo "<input type=hidden name=post_accion value=cargar>";
echo "<input type=hidden name=max_file_size value=".$maximo_archivo.">";
echo "<input name=archivo type=file>";
echo "</td>";
echo "</tr>";

echo "<tr height=40 valign=center>";
echo "<td colspan=3 align=center>";
echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
echo "&nbsp;&nbsp;&nbsp;";
echo "<a href=?><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";


mysql_close($db);
html_fin_mensajes();







?>
