<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Usuarios");
echo "<body>";
html_menu_superior(4);
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
$debug=false;

mysql_close($db);
html_fin_mensajes();

$titulo="Verificar Semillas";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Listar Semillas";
$elemento["ruta"]="listar_semillas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Usuarios";
$elemento["ruta"]="control_usuarios.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<h3>".date("Y-m-d H:i:s")."</h3>";

$semillas_fallidas=array();

$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	echo "<h3>Grupo \"".$grupos[$i]["nombre"]."\"</h3>";
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		echo "<h5>Dominio \"".$dominios[$j]["nombre"]."\"</h5>";
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			echo $semillas[$k]["url"]." (id: ".$semillas[$k]["id"].")... ";
			if(@fopen($semillas[$k]["url"], "r")){
				echo "<span style=\"color: green;\">Ok</span><br>";
			}
			else{
				echo "<span style=\"color: red;\">Inaccesible</span><br>";
				$semillas_fallidas[]=$semillas[$k]["url"];
			}
		}
	}
	echo "<br>";
}//for... cada grupo
/*
echo "-----<br>";
for($i=0; $i<count($semillas_fallidas); $i++){
	echo "\$semillas_fallidas[]=\"".$semillas_fallidas[$i]."\";<br>";
	
}
*/
echo "-----<br>";


echo "</body>";

echo "</html>";

?>
