<?php

require_once("config.ini");

echo "<h1>Iniciando Monitoreo...</h1>\n";

$debug=true;
$grupos=obtener_grupos_monitoreables($db);
for($i=0; $i<count($grupos); $i++){
	$puerto=obtener_puerto_grupo($db, $grupos[$i]["id"]);
	$grupos[$i]["puerto"]=$puerto;
}
$dominios=obtener_dominios_monitoreables($db);
for($i=0; $i<count($dominios); $i++){
	$puerto=obtener_puerto_grupo($db, $dominios[$i]["id"]);
	$dominios[$i]["puerto"]=$puerto;
}
$debug=false;

$debug=true;
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["nombre"]);
	$proceso=$ruta_bin."/".$prefijo."-G-".$grupos[$i]["id"]."_";
	$procesos_activos=informacion_procesos($proceso);
	if(count($procesos_activos)==0){
		crear_iniciar_servicio($prefijo, "G", $grupos[$i]["id"], $grupos[$i]["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
	}
}
$debug=false;

$debug=true;
for($i=0; $i<count($dominios); $i++){
	print_debug("Dominio ".$dominios[$i]["nombre"]);
	$proceso=$ruta_bin."/".$prefijo."-D-".$dominios[$i]["id"]."_";
	$procesos_activos=informacion_procesos($proceso);
	if(count($procesos_activos)==0){
		crear_iniciar_servicio($prefijo, "D", $dominios[$i]["id"], $dominios[$i]["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
	}
}
$debug=false;

echo "<h1>Monitoreo Terminado</h1>\n";

?>
