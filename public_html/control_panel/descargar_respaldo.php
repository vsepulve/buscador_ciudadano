<?php

require_once("config.ini");

require_once("sesion.php");

$nombre_archivo=$_GET["nombre_archivo"];

$ruta_archivo=$ruta_respaldos."/".$nombre_archivo;
if(file_exists($ruta_archivo)){
	header("Content-Disposition: attachment; filename=".$nombre_archivo."\n\n");
	header("Content-Type: application/octet-stream");
	header("Content-Length: ".filesize($ruta_archivo));
	readfile($ruta_archivo);
}
else{
	header("Location: control_respaldos.php?error=1");
}

?>
