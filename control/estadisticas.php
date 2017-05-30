<?php

require_once("config.ini");

echo "<h1>Actualizando Estadisticas</h1>\n";

$debug=false;
$estructura=obtener_estructura($db);
$debug=false;








$grupos=$estructura["grupos"];
for($k=0; $k<count($grupos); $k++){

$id_grupo=$grupos[$k]["id"];

echo "Obteniendo estadisticas de Grupo $id_grupo<br>\n";
$estisticas=estadisticas_grupo($db, $id_grupo, $prefijo, $ruta_colecta, $extensiones_colecta);

$semillas=obtener_semillas_grupo($db, $id_grupo);
$numero_documentos=0;
//>./numero_documentos prefijo ruta_textos id_semillestructuraa minimo_palabras

for($i=0; $i<count($semillas); $i++){
	$comando="$ruta_bin/numero_documentos $prefijo $ruta_colecta ".$semillas[$i]["id"]." 10";
	echo "Ejecutando \"".$comando."\"<br>\n";
	$salida=array();
	exec($comando, $salida);
	//La unica linea es el numero
	echo $salida[0]." documentos validos<br>\n";
	$numero_documentos+=$salida[0];
	echo "<br>\n";
	
}

echo $numero_documentos." documentos en total<br>\n";

//Maquina 0
echo "Guardando en Maquina 00<br>\n";
$consulta="select * from estadisticas where id_grupo=\"".$id_grupo."\"";
$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Select</h3>\n");
if(($fila=mysql_fetch_row($resultado)) != NULL ){
	//Hay al menos uno, actualizar
	$consulta="update estadisticas set fecha_colecta=\"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", bytes_colecta=\"".$estisticas["tamaño_colecta"]."\", fecha_indice=\"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", numero_documentos=\"".$numero_documentos."\" where id_grupo=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die(mysql_error());
}
else{
	//insertar
	$consulta="insert into estadisticas (id_grupo, fecha_colecta, bytes_colecta, fecha_indice, numero_documentos) values (\"".$id_grupo."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", \"".$estisticas["tamaño_colecta"]."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", \"".$numero_documentos."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Insert</h3>\n");
	
}
mysql_close($db);

//Maquina 1
echo "Guardando en Maquina 01<br>\n";
$host="10.20.1.19";
$user="buscador_entrada";
$pass="buscador";
$database="control_panel";
$db=mysql_connect($host, $user, $pass) or die ("No se puede conectar al servidor");
mysql_select_db($database,$db) or die ("La base de datos no puede ser seleccionada");

$consulta="select * from estadisticas where id_grupo=\"".$id_grupo."\"";
$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Select</h3>\n");
if(($fila=mysql_fetch_row($resultado)) != NULL ){
	//Hay al menos uno, actualizar
	$consulta="update estadisticas set fecha_colecta=\"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", bytes_colecta=\"".$estisticas["tamaño_colecta"]."\", fecha_indice=\"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", numero_documentos=\"".$numero_documentos."\" where id_grupo=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die(mysql_error());
}
else{
	//insertar
	$consulta="insert into estadisticas (id_grupo, fecha_colecta, bytes_colecta, fecha_indice, numero_documentos) values (\"".$id_grupo."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", \"".$estisticas["tamaño_colecta"]."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", \"".$numero_documentos."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Insert</h3>\n");
	
}
mysql_close($db);

//Maquina 2
echo "Guardando en Maquina 02<br>\n";
$host="10.20.1.20";
$user="buscador_entrada";
$pass="buscador";
$database="control_panel";
$db=mysql_connect($host, $user, $pass) or die ("No se puede conectar al servidor");
mysql_select_db($database,$db) or die ("La base de datos no puede ser seleccionada");

$consulta="select * from estadisticas where id_grupo=\"".$id_grupo."\"";
$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Select</h3>\n");
if(($fila=mysql_fetch_row($resultado)) != NULL ){
	//Hay al menos uno, actualizar
	$consulta="update estadisticas set fecha_colecta=\"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", bytes_colecta=\"".$estisticas["tamaño_colecta"]."\", fecha_indice=\"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", numero_documentos=\"".$numero_documentos."\" where id_grupo=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die(mysql_error());
}
else{
	//insertar
	$consulta="insert into estadisticas (id_grupo, fecha_colecta, bytes_colecta, fecha_indice, numero_documentos) values (\"".$id_grupo."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_colecta"])."\", \"".$estisticas["tamaño_colecta"]."\", \"".date("Y-m-d H:i:s", $estisticas["tiempo_indice"])."\", \"".$numero_documentos."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>Fallo en Insert</h3>\n");
	
}
mysql_close($db);


}

?>


