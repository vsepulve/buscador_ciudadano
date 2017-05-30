<?php

/*
Aqui se almacenan todas las funciones que hacen uso de la base de datos.
Todas parten con el parametro $db que representa una conexion a la base de datos.
*/

function obtener_estadisticas($db, $id_grupo){
	$estadisticas=array();
	$consulta = "select * from estadisticas where id_grupo=\"".$id_grupo."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_estadisticas) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$estadisticas["id_grupo"]=$fila[0];
		$estadisticas["fecha_colecta"]=$fila[1];
		$estadisticas["bytes_colecta"]=$fila[2];
		$estadisticas["fecha_indice"]=$fila[3];
		$estadisticas["numero_documentos"]=$fila[4];
	}
	return $estadisticas;
}

function obtener_semillas_grupo($db, $id_grupo){
	$semillas=array();
	$consulta = "select semillas.id, semillas.url, semillas.reject, semillas.id_dominio, semillas.borrado from dominios, semillas where dominios.id_grupo=\"".$id_grupo."\" and dominios.id=semillas.id_dominio and semillas.borrado=0";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_semillas_grupo) Fallo en Select</h3>");
	$contador=0;
	while( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$semillas[$contador]["id"]=$fila[0];
		$semillas[$contador]["url"]=$fila[1];
		$semillas[$contador]["reject"]=$fila[2];
		$semillas[$contador]["id_dominio"]=$fila[3];
		$semillas[$contador]["borrado"]=$fila[4];
		$contador++;
	}
	return $semillas;
}

function obtener_puerto_grupo($db, $id){
	$consulta = "select puerto FROM puertos_indice WHERE id=\"".$id."\" and tipo=\"G\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_puerto_grupo) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		return $fila[0];
	}
	return -1;
}

function obtener_puerto_dominio($db, $id){
	$consulta = "select puerto FROM puertos_indice WHERE id=\"".$id."\" and tipo=\"D\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_puerto_dominio) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		return $fila[0];
	}
	return -1;
}

function obtener_puerto_indice($db, $tipo, $id){
	$puerto_indice=array();
	$consulta = "select * from puertos_indice where tipo=\"".$tipo."\" and id=\"".$id."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_puertos_indice) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		$puerto_indice["tipo"]=$fila[0];
		$puerto_indice["id"]=$fila[1];
		$puerto_indice["puerto"]=$fila[2];
		$puerto_indice["maquina"]=$fila[3];
		$puerto_indice["refinamiento"]=$fila[4];
		return $puerto_indice;
	}
	return false;
}

function editar_puerto_indice($db, $tipo, $id, $puerto, $id_maquina, $refinamiento){
	$consulta="update puertos_indice set puerto=\"".$puerto."\", id_maquina=\"".$id_maquina."\", refinamiento=\"".$refinamiento."\" where tipo=\"".$tipo."\" and id=\"".$id."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(editar_puertos_indice) Fallo en Update</h3>");
}

function eliminar_puerto_indice($db, $tipo, $id){
	$consulta="delte from puertos_indice where tipo=\"".$tipo."\" and id=\"".$id."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_puertos_indice) Fallo en Delete</h3>");
}
	
function obtener_grupos($db){
	return obtener_grupos_opcional($db, 0);
}
	
function obtener_dominios($db, $id_grupo){
	return obtener_dominios_opcional($db, $id_grupo, 0);
}

function obtener_semillas($db, $id_dominio){
	return obtener_semillas_opcional($db, $id_dominio, 0);
}

function obtener_semilla_borrada($db, $id_semilla){
	$consulta = "select * from semillas where id=\"".$id_semilla."\" and borrado=1";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_semilla_borrada) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$semilla["id"]=$fila[0];
		$semilla["url"]=$fila[1];
		$semilla["reject"]=$fila[2];
		$semilla["id_dominio"]=$fila[3];
		return $semilla;
	}
	return false;
}

//En esta version de bajo nivel, $borrados puede ser:
//0 => Omitir, 1 => Solo borrados, de otro modo => Ambos
function obtener_grupos_opcional($db, $borrados){
	$grupos=array();
	if($borrados===0)
		$consulta = "select * from grupos where borrado=0 order by id";
	else if($borrados===1)
		$consulta = "select * from grupos where borrado=1 order by id";
	else
		$consulta = "select * from grupos order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_grupos_opcional) Fallo en Select</h3>");
	$contador=0;
	while ( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$grupos[$contador]["id"]=$fila[0];
		$grupos[$contador]["nombre"]=$fila[1];
		$grupos[$contador]["borrado"]=$fila[2];
		$grupos[$contador]["auditable"]=$fila[3];
		$grupos[$contador]["reject"]=$fila[4];
		$grupos[$contador]["monitoreable"]=$fila[5];
		$contador++;
	}
	return $grupos;
}

//En esta version de bajo nivel, $borrados puede ser:
//0 => Omitir, 1 => Solo borrados, de otro modo => Ambos
function obtener_dominios_opcional($db, $id_grupo, $borrados){
	$dominios=array();
	if($borrados===0)
		$consulta = "select * from dominios where borrado=0 and id_grupo=\"".$id_grupo."\" order by id";
	else if($borrados===1)
		$consulta = "select * from dominios where borrado=1 and id_grupo=\"".$id_grupo."\" order by id";
	else
		$consulta = "select * from dominios where id_grupo=\"".$id_grupo."\" order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_dominios_opcional) Fallo en Select</h3>");
	$contador=0;
	while ( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$dominios[$contador]["id"]=$fila[0];
		$dominios[$contador]["nombre"]=$fila[1];
		$dominios[$contador]["id_grupo"]=$fila[2];
		$dominios[$contador]["borrado"]=$fila[3];
		$dominios[$contador]["reject"]=$fila[4];
		$dominios[$contador]["monitoreable"]=$fila[5];
		$contador++;
	}
	return $dominios;
}

//En esta version de bajo nivel, $borrados puede ser:
//0 => Omitir, 1 => Solo borrados, de otro modo => Ambos
function obtener_semillas_opcional($db, $id_dominio, $borrados){
	$semillas=array();
	if($borrados===0)
		$consulta = "select * from semillas where borrado=0 and id_dominio=\"".$id_dominio."\" order by id";
	else if($borrados===1)
		$consulta = "select * from semillas where borrado=1 and id_dominio=\"".$id_dominio."\" order by id";
	else 
		$consulta = "select * from semillas where id_dominio=\"".$id_dominio."\" order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_semillas_opcional) Fallo en Select</h3>");
	$contador=0;
	while ( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$semillas[$contador]["id"]=$fila[0];
		$semillas[$contador]["url"]=$fila[1];
		$semillas[$contador]["reject"]=$fila[2];
		$semillas[$contador]["id_dominio"]=$fila[3];
		$semillas[$contador]["borrado"]=$fila[4];
		$contador++;
	}
	return $semillas;
}

function obtener_estructura($db){
	//$debug=false;
	$estructura=array();
	//print_debug("llamando obtener_grupos");
	$grupos=obtener_grupos($db);
	$estructura["grupos"]=$grupos;
	//print_debug(count($grupos)." grupos");
	for($i=0; $i<count($grupos); $i++){
		//print_debug("llamando obtener_dominios");
		$dominios=obtener_dominios($db, $grupos[$i]["id"]);
		$estructura["dominios"][$i]=$dominios;
		//print_debug(count($dominios)." dominios");
		for($j=0; $j<count($dominios); $j++){
			//print_debug("llamando obtener_semillas (obtener_semillas($db, ".$dominios[$j]["id"]."))");
			$semillas=obtener_semillas($db, $dominios[$j]["id"]);
			$estructura["semillas"][$i][$j]=$semillas;
			//print_debug(count($semillas)." semillas");
		}//for... cada dominio
	}//for... cada grupo
	return $estructura;
}

function agregar_puertos($db, &$estructura){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$puerto_grupo=obtener_puerto_grupo($db,$grupos[$i]["id"]);
		$estructura["grupos"][$i]["puerto"]=$puerto_grupo;
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$puerto_dominio=obtener_puerto_dominio($db,$dominios[$j]["id"]);	
			$estructura["dominios"][$i][$j]["puerto"]=$puerto_dominio;
		}
	}
}

function obtener_estructura_completa($db){
	//$debug=false;
	$estructura=array();
	//print_debug("llamando obtener_grupos");
	$grupos=obtener_grupos_opcional($db, 2);
	$estructura["grupos"]=$grupos;
	///print_debug(count($grupos)." grupos");
	for($i=0; $i<count($grupos); $i++){
		//print_debug("llamando obtener_dominios");
		$dominios=obtener_dominios_opcional($db, $grupos[$i]["id"], 2);
		$estructura["dominios"][$i]=$dominios;
		//print_debug(count($dominios)." dominios");
		for($j=0; $j<count($dominios); $j++){
			//print_debug("llamando obtener_semillas (obtener_semillas($db, ".$dominios[$j]["id"]."))");
			$semillas=obtener_semillas_opcional($db, $dominios[$j]["id"], 2);
			$estructura["semillas"][$i][$j]=$semillas;
			//print_debug(count($semillas)." semillas");
		}//for... cada dominio
	}//for... cada grupo
	return $estructura;
}

function existe_grupo($db, $id_grupo){
	return existe_grupo_opcional($db, $id_grupo, 0);
}

function existe_dominio($db, $id_dominio){
	return existe_dominio_opcional($db, $id_dominio, 0);
}

function existe_semilla($db, $id_semilla){
	return existe_semilla_opcional($db, $id_semilla, 0);
}

function existe_grupo_opcional($db, $id_grupo, $borrados){
	$dominios=array();
	if($borrados===0)
		$consulta = "select * from grupos where borrado=0 and id=\"".$id_grupo."\"";
	else if($borrados===1)
		$consulta = "select * from grupos where borrado=1 and id=\"".$id_grupo."\"";
	else
		$consulta = "select * from grupos where id=\"".$id_grupo."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(existe_grupo) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		return true;
	}
	return false;
}

function existe_dominio_opcional($db, $id_dominio, $borrados){
	$dominios=array();
	if($borrados===0)
		$consulta = "select * from dominios where borrado=0 and id = \"".$id_dominio."\"";
	else if($borrados===1)
		$consulta = "select * from dominios where borrado=1 and id = \"".$id_dominio."\"";
	else
		$consulta = "select * from dominios where id = \"".$id_dominio."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(existe_dominio) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		return true;
	}
	return false;
}

function existe_semilla_opcional($db, $id_semilla, $borrados){
	$dominios=array();
	if($borrados===0)
		$consulta = "select * from semillas where borrado=0 and id = \"".$id_semilla."\"";
	else if($borrados===1)
		$consulta = "select * from semillas where borrado=1 and id = \"".$id_semilla."\"";
	else
		$consulta = "select * from semillas where id = \"".$id_semilla."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(existe_semilla) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		return true;
	}
	return false;
}

function verificar_puerto_libre($db, $puerto){
	$comando="netstat -a |grep ".$puerto;
	exec($comando,$output);
	if(count($output)>0){
		return false;
	}
	else{
		$consulta = "select id from puertos_indice where puerto=\"".$puerto."\"";
		$resultado = mysql_query($consulta, $db) or die("<h3>(verificar_puerto_libre) Fallo en Select</h3>");
		if(($fila=mysql_fetch_row($resultado)) != NULL){
			return false;
		}
	}
	return true;
}

function verificar_puerto($db, $puerto){
	if(verificar_puerto_valido($puerto)
		&& verificar_puerto_libre($db, $puerto) ){
		return true;
		}
	else{
		return false;
	}
}

function obtener_puerto_random($db){
	global $min_puerto, $max_puerto;
	//print_debug("Puertos [$min_puerto, $max_puerto]");
	$puerto=rand($min_puerto, $max_puerto);
	while(!verificar_puerto($db, $puerto)){
		$puerto=rand($min_puerto, $max_puerto);
	}
	return $puerto;
}

function ingresar_grupo($db, $nombre, $puerto){
	//Ingreso el grupo
	$consulta="insert into grupos (nombre) values (\"".$nombre."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>(ingresar_grupo) Fallo en Insert</h3>");
	//Pido el id para retornarlo
	$consulta="select id from grupos where borrado=0 and nombre=\"".$nombre."\" order by id desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_grupo) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		//Si se ingreso, agrego el puerto
		$id_grupo=$fila[0];
		$consulta="insert into puertos_indice (tipo, id, puerto) values (\"G\", \"".$id_grupo."\", \"".$puerto."\")";
		//$resultado=mysql_query($consulta, $db) or die(mysql_error($db));
		$resultado=mysql_query($consulta, $db) or die("<h3>(ingresar_grupo) Fallo en Insert de Puerto</h3>");
		return $id_grupo;
	}
	return -1;
}

function ingresar_dominio($db, $nombre, $id_grupo, $puerto, $reject){
	//Ingreso el grupo
	$consulta="insert into dominios (nombre, id_grupo, reject) values (\"".$nombre."\", \"".$id_grupo."\", \"".$reject."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>(ingresar_dominio) Fallo en Insert</h3>");
	//Pido el id para retornarlo
	$consulta="select id from dominios where borrado=0 and id_grupo=\"".$id_grupo."\" and nombre=\"".$nombre."\" order by id desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_dominio) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		//Si se ingreso, agrego el puerto
		$id_dominio=$fila[0];
		$consulta="insert into puertos_indice (tipo, id, puerto) values (\"D\", \"".$id_dominio."\", \"".$puerto."\")";
		$resultado=mysql_query($consulta, $db) or die("<h3>(ingresar_dominio) Fallo en Insert de Puerto</h3>");
		return $id_dominio;
	}
	return -1;
}

function ingresar_semilla($db, $url, $reject, $id_dominio){
	//Ingreso el grupo
	$consulta="insert into semillas (url, reject, id_dominio) values (\"".$url."\", \"".$reject."\", \"".$id_dominio."\")";
	$resultado=mysql_query($consulta, $db) or die("<h3>(ingresar_semilla) Fallo en Insert</h3>");
	//Pido el id para retornarlo
	$consulta="select id from semillas where borrado=0 and id_dominio=\"".$id_dominio."\" and url=\"".$url."\" order by id desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_semilla) Fallo en Select</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		return $fila[0];
	}
	return -1;
}

function editar_grupo($db, $id_grupo, $nombre, $puerto, $reject){
	//Edito el grupo
	$consulta="update grupos set nombre=\"".$nombre."\", reject=\"".$reject."\" where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(editar_grupo) Fallo en Update</h3>");
	//Verifico si existe puerto
	$consulta="select puerto from puertos_indice where tipo=\"G\" and id=\"".$id_grupo."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(editar_grupo) Fallo en Select de Puerto</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		//Si existe, lo edito
		$consulta="update puertos_indice set puerto=\"".$puerto."\" where tipo=\"G\" and id=\"".$id_grupo."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(editar_grupo) Fallo en Update de Puerto</h3>");
	}
	else{
		//Si no, lo ingreso
		$consulta="insert into puertos_indice (tipo, id, puerto) values (\"G\", \"".$id_grupo."\", \"".$puerto."\")";
		$resultado=mysql_query($consulta, $db) or die("<h3>(editar_grupo) Fallo en Insert de Puerto</h3>");
	}
	//Modificaciones Adicionales
	
}

function marcar_grupo_auditable($db, $id_grupo){
	$consulta="update grupos set auditable=\"0\" where auditable=\"1\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(marcar_grupo_auditable) Fallo en Update</h3>");
	$consulta="update grupos set auditable=\"1\" where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(marcar_grupo_auditable) Fallo en Update</h3>");
}

function editar_dominio($db, $id_dominio, $nombre, $id_grupo, $puerto, $reject){
	//Edito el grupo
	$consulta="update dominios set nombre=\"".$nombre."\", id_grupo=\"".$id_grupo."\", reject=\"".$reject."\" where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(editar_dominio) Fallo en Update</h3>");
	//Verifico si existe puerto
	$consulta="select puerto from puertos_indice where tipo=\"D\" and id=\"".$id_dominio."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(editar_dominio) Fallo en Select de Puerto</h3>");
	if(($fila=mysql_fetch_row($resultado)) != NULL ){
		//Si existe, lo edito
		$consulta="update puertos_indice set puerto=\"".$puerto."\" where tipo=\"D\" and id=\"".$id_dominio."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(editar_dominio) Fallo en Update de Puerto</h3>");
	}
	else{
		//Si no, lo ingreso
		$consulta="insert into puertos_indice (tipo, id, puerto) values (\"D\", \"".$id_dominio."\", \"".$puerto."\")";
		$resultado=mysql_query($consulta, $db) or die("<h3>(editar_dominio) Fallo en Insert de Puerto</h3>");
	}
	//Modificaciones Adicionales
	
}

function editar_semilla($db, $id_semilla, $url, $reject, $id_dominio){
	//Edito el grupo
	$consulta="update semillas set url=\"".$url."\", reject=\"".$reject."\", id_dominio=\"".$id_dominio."\" where id=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(editar_semilla) Fallo en Update</h3>");
	//Modificaciones Adicionales
	
}

function recuperar_grupo($db, $id_grupo){
	//Recuperar el grupo
	$consulta="update grupos set borrado=0 where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(recuperar_grupo) Fallo en Update</h3>");
	
}

function recuperar_dominio($db, $id_dominio){
	//Recuperar el dominio
	$consulta="update dominios set borrado=0 where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(recuperar_dominio) Fallo en Update</h3>");
	
}

function recuperar_semilla($db, $id_semilla){
	//Recuperar el dominio
	$consulta="update semillas set borrado=0 where id=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(recuperar_semilla) Fallo en Update</h3>");
	
}

function eliminar_grupo($db, $id_grupo){
	//Elimino al grupo
	//$consulta="delete from grupos where id=\"".$id_grupo."\"";
	$consulta="update grupos set borrado=1 where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo) Fallo en Update</h3>");
	//Elimino al puerto
	$consulta="delete from puertos_indice where tipo=\"G\" and id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo) Fallo en Delete de Puerto</h3>");
	//Eliminaciones Adicionales
	
}

function eliminar_dominio($db, $id_dominio){
	//Elimino al grupo
	//$consulta="delete from dominios where id=\"".$id_dominio."\"";
	$consulta="update dominios set borrado=1 where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio) Fallo en Update</h3>");
	//Elimino al puerto
	$consulta="delete from puertos_indice where tipo=\"D\" and id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio) Fallo en Delete de Puerto</h3>");
	//Eliminaciones Adicionales
	
}

function eliminar_semilla($db, $id_semilla){
	//Elimino al grupo
	//$consulta="delete from semillas where id=\"".$id_semilla."\"";
	$consulta="update semillas set borrado=1 where id=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_semilla) Fallo en Update</h3>");
	//Eliminaciones Adicionales
	
}

function eliminar_grupo_verdadero($db, $id_grupo){
	//Elimino al grupo
	$consulta="delete from grupos where id=\"".$id_grupo."\"";
	//$consulta="update grupos set borrado=1 where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete</h3>");
	//Elimino al puerto
	$consulta="delete from puertos_indice where tipo=\"G\" and id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete de Puerto</h3>");
	//Eliminar Logs
	$logs=obtener_log_inicio_colecta($db, "G", $id_grupo);
	for($i=0; $i<count($logs); $i++){
		$consulta="delete from log_semillas_colectadas where id_inicio_colecta=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete de Log</h3>");
		$consulta="delete from log_inicio_colecta where id=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete de Log</h3>");
	}
	$logs=obtener_log_creacion_indice($db, "G", $id_grupo);
	for($i=0; $i<count($logs); $i++){
		$consulta="delete from log_semillas_agrupadas where id_creacion_indice=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete de Log</h3>");
		$consulta="delete from log_creacion_indice where id=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_grupo_verdadero) Fallo en Delete de Log</h3>");
	}
	//Eliminaciones Adicionales
	
}

function eliminar_dominio_verdadero($db, $id_dominio){
	//Elimino al grupo
	$consulta="delete from dominios where id=\"".$id_dominio."\"";
	//$consulta="update dominios set borrado=1 where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete</h3>");
	//Elimino al puerto
	$consulta="delete from puertos_indice where tipo=\"D\" and id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete de Puerto</h3>");
	//Eliminar Logs
	$logs=obtener_log_inicio_colecta($db, "D", $id_dominio);
	for($i=0; $i<count($logs); $i++){
		$consulta="delete from log_semillas_colectadas where id_inicio_colecta=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete de Log</h3>");
		$consulta="delete from log_inicio_colecta where id=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete de Log</h3>");
	}
	$logs=obtener_log_creacion_indice($db, "D", $id_dominio);
	for($i=0; $i<count($logs); $i++){
		$consulta="delete from log_semillas_agrupadas where id_creacion_indice=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete de Log</h3>");
		$consulta="delete from log_creacion_indice where id=\"".$logs[$i]["id"]."\"";
		$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_dominio_verdadero) Fallo en Delete de Log</h3>");
	}
	//Eliminaciones Adicionales
	
}

function eliminar_semilla_verdadero($db, $id_semilla){
	//Elimino al grupo
	$consulta="delete from semillas where id=\"".$id_semilla."\"";
	//$consulta="update semillas set borrado=1 where id=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_semilla_verdadero) Fallo en Delete</h3>");
	//Eliminar Logs
	$consulta="delete from log_semillas_colectadas where id_semilla=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_semilla_verdadero) Fallo en Delete de Log</h3>");
	$consulta="delete from log_inicio_colecta where tipo_colecta=\"S\" and id_colecta=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_semilla_verdadero) Fallo en Delete de Log</h3>");
	$consulta="delete from log_semillas_agrupadas where id_semilla=\"".$id_semilla."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(eliminar_semilla_verdadero) Fallo en Delete de Log</h3>");
	//Eliminaciones Adicionales
	
}

function ingresar_log_creacion_indice($db, $tipo_indice, $id_indice){
	$consulta = "insert into log_creacion_indice (tipo_indice, id_indice) values (\"".$tipo_indice."\", \"".$id_indice."\") ";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_log_creacion_indice) Fallo en Insert</h3>");
	//Si se ingreso (y entonces no murio el proceso) => puedo elegir la ultima
	$logs=obtener_log_creacion_indice($db, $tipo_indice, $id_indice);
	if(count($logs)>0){
		return $logs[0]["id"];
	}
	return -1;
}

function obtener_log_creacion_indice($db, $tipo_indice, $id_indice){
	$consulta = "select * from log_creacion_indice where tipo_indice=\"".$tipo_indice."\" and id_indice=\"".$id_indice."\" order by id desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_log_creacion_indice) Fallo en Select</h3>");
	$logs=array();
	while( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$log_semillas_agrupadas=obtener_log_semillas_agrupadas($db, $fila[0]);
		$logs[]=array("id"=>$fila[0], "tipo_indice"=>$fila[1], "id_indice"=>$fila[2], "fecha"=>$fila[3], "semillas_agrupadas"=>$log_semillas_agrupadas);
	}
	return $logs;
}

function ingresar_log_semillas_agrupadas($db, $id_semilla, $id_creacion_indice){
	$consulta = "insert into log_semillas_agrupadas (id_semilla, id_creacion_indice) values (\"".$id_semilla."\", \"".$id_creacion_indice."\") ";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_log_semillas_agrupadas) Fallo en Insert</h3>");
}

function obtener_log_semillas_agrupadas($db, $id_creacion_indice){
	$consulta = "select * from log_semillas_agrupadas where id_creacion_indice=\"".$id_creacion_indice."\" order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_log_semillas_agrupadas) Fallo en Select</h3>");
	$logs=array();
	while( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$logs[]=array("id"=>$fila[0], "id_semilla"=>$fila[1], "id_creacion_indice"=>$fila[2]);
	}
	return $logs;
}

function ingresar_log_inicio_colecta($db, $tipo_colecta, $id_colecta){
	$consulta = "insert into log_inicio_colecta (tipo_colecta, id_colecta) values (\"".$tipo_colecta."\", \"".$id_colecta."\") ";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_log_inicio_colecta) Fallo en Insert</h3>");
	//Si se ingreso (y entonces no murio el proceso) => puedo elegir la ultima
	$logs=obtener_log_inicio_colecta($db, $tipo_colecta, $id_colecta);
	if(count($logs)>0){
		return $logs[0]["id"];
	}
	return -1;
}

function obtener_log_inicio_colecta($db, $tipo_colecta, $id_colecta){
	$consulta = "select * from log_inicio_colecta where tipo_colecta=\"".$tipo_colecta."\" and id_colecta=\"".$id_colecta."\" order by id desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_log_inicio_colecta) Fallo en Select</h3>");
	$logs=array();
	while( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$log_semillas_colectadas=obtener_log_semillas_colectadas($db, $fila[0]);
		$logs[]=array("id"=>$fila[0], "tipo_colecta"=>$fila[1], "id_colecta"=>$fila[2], "fecha"=>$fila[3], "semillas_colectadas"=>$log_semillas_colectadas);
	}
	return $logs;
}

function ingresar_log_semillas_colectadas($db, $id_semilla, $id_inicio_colecta){
	$consulta = "insert into log_semillas_colectadas (id_semilla, id_inicio_colecta) values (\"".$id_semilla."\", \"".$id_inicio_colecta."\") ";
	$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_log_semillas_colectadas) Fallo en Insert</h3>");
}

function obtener_log_semillas_colectadas($db, $id_inicio_colecta){
	$consulta = "select * from log_semillas_colectadas where id_inicio_colecta=\"".$id_inicio_colecta."\" order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_log_semillas_colectadas) Fallo en Select</h3>");
	$logs=array();
	while( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$logs[]=array("id"=>$fila[0], "id_semilla"=>$fila[1], "id_inicio_colecta"=>$fila[2]);
	}
	return $logs;
}

//extrae la informacion de log_inicio_colecta, partiendo de una semilla (por su log_semillas_colectadas)
function obtener_ultimo_log_semilla($db, $id_semilla){
	$consulta = "select log_inicio_colecta.id, tipo_colecta, id_colecta, fecha from log_semillas_colectadas, log_inicio_colecta where id_semilla=\"".$id_semilla."\" and id_inicio_colecta=log_inicio_colecta.id order by fecha desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_ultimo_log_semilla) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$ultimo_log=array("id"=>$fila[0], "tipo_colecta"=>$fila[1], "id_colecta"=>$fila[2], "fecha"=>$fila[3]);
		return $ultimo_log;
	}
	return false;
}

//Retorna los ultimos $n log de colecta de una semilla, inversos por fecha
function obtener_log_colecta_semilla($db, $id_semilla, $n){
	$consulta = "select log_inicio_colecta.id, tipo_colecta, id_colecta, fecha from log_semillas_colectadas, log_inicio_colecta where id_semilla=\"".$id_semilla."\" and id_inicio_colecta=log_inicio_colecta.id order by fecha desc";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_ultimo_log_semilla) Fallo en Select</h3>");
	$log=array();
	$agregados=0;
	for($i=0; $i<$n; $i++){
		if(($fila=mysql_fetch_row($resultado)) == NULL){
			break;
		}
		$log[$agregados++]=array("id"=>$fila[0], "tipo_colecta"=>$fila[1], "id_colecta"=>$fila[2], "fecha"=>$fila[3]);
	}
	if($agregados)
		return $log;
	return false;
	
}

function semilla_colectada_inicio($db, $id_semilla, $id_inicio){
	$consulta = "select * from log_semillas_colectadas where id_inicio_colecta=\"".$id_inicio."\" and id_semilla=\"".$id_semilla."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(semilla_colectada_inicio) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		return true;
	}
	return false;	
}

function guardar_estructura($db, $ruta_archivo){

	if($salida=fopen($ruta_archivo, "w")){
		$estructura=obtener_estructura($db);
		$grupos=$estructura["grupos"];
		for($i=0; $i<count($grupos); $i++){
			fwrite($salida, "Grupo\n");
			fwrite($salida, $grupos[$i]["nombre"]."\n");
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				fwrite($salida, "Dominio\n");
				fwrite($salida, $dominios[$j]["nombre"]."\n");
				$semillas=$estructura["semillas"][$i][$j];
				for($k=0; $k<count($semillas); $k++){
					fwrite($salida, "Semilla\n");
					fwrite($salida, $semillas[$k]["url"]."\n");
					fwrite($salida, $semillas[$k]["reject"]."\n");
				}
			}
		}
		
		fclose($salida);
		return true;
	}
	return false;
}

function cargar_estructura($db, $entrada){
	$estructura=array();
	$grupo_actual=-1;
	$dominio_actual=-1;
	while($linea=trim(fgets($entrada))){
		if(strcmp($linea, "Grupo")===0){
			$linea=trim(fgets($entrada));
			$grupo=array();
			$grupo["nombre"]=$linea;
			$estructura["grupos"][]=$grupo;
			$grupo_actual++;
			$dominio_actual=-1;
		}
		else if(strcmp($linea, "Dominio")===0){
			$linea=trim(fgets($entrada));
			$dominio=array();
			$dominio["nombre"]=$linea;
			$estructura["dominios"][$grupo_actual][]=$dominio;
			$dominio_actual++;
		}
		else if(strcmp($linea, "Semilla")===0){
			$linea=trim(fgets($entrada));
			$semilla=array();
			$semilla["url"]=$linea;
			$linea=trim(fgets($entrada));
			$semilla["reject"]=$linea;
			$estructura["semillas"][$grupo_actual][$dominio_actual][]=$semilla;
		}
	}
	fclose($entrada);
	return $estructura;
}

function ingresar_estructura($db, $estructura){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		//ingresar grupo
		$puerto=obtener_puerto_random($db);
		print_debug("ingresar_grupo($db, ".$grupos[$i]["nombre"].", $puerto)");
		$id_grupo=ingresar_grupo($db, $grupos[$i]["nombre"], $puerto, $grupos[$i]["reject"]);
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			//ingresar dominio
			$puerto=obtener_puerto_random($db);
			$id_dominio=ingresar_dominio($db, $dominios[$j]["nombre"], $id_grupo, $puerto, $dominios[$j]["reject"]);
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				//ingresar semilla
				ingresar_semilla($db, $semillas[$k]["url"], $semillas[$k]["reject"], $id_dominio);
			}
		}
	}

}

function obtener_usuarios($db){
	$usuarios=array();
	$consulta="select * from usuarios";
	$resultado=mysql_query($consulta, $db) or die("<h3>(obtener_usuarios) Fallo en Select</h3>");
	while($fila=mysql_fetch_row($resultado)){
		$usuarios[]=array("nombre_usuario"=>$fila[0], "rol"=>$fila[2], "ultimo_login"=>$fila[3]);
	}
	return $usuarios;
}

function obtener_grupo_auditable($db){
	$grupo=array();
	$consulta="select * from grupos where auditable=1";
	$resultado=mysql_query($consulta, $db) or die("<h3>(obtener_grupo_auditable) Fallo en Select</h3>");
	if($fila=mysql_fetch_row($resultado)){
		$grupo["id"]=$fila[0];
		$grupo["nombre"]=$fila[1];
		$grupo["borrado"]=$fila[2];
		$grupo["auditable"]=$fila[3];
		$grupo["reject"]=$fila[4];
		$grupo["monitoreable"]=$fila[5];
		return $grupo;
	}
	return false;
}

function obtener_refinamiento_grupo($db, $id_grupo){
	$arreglo_refinamiento=array();
	$puerto_indice=obtener_puerto_indice($db, "G", $id_grupo);
	$refinamiento=$puerto_indice["refinamiento"];
	$arreglo=explode(" ", $refinamiento);
	for($k=0; $k<count($arreglo); $k++){
		if(strlen($arreglo[$k])>0){
			$arreglo_refinamiento[]=$arreglo[$k];
		}
	}
	return $arreglo_refinamiento;
}

function obtener_refinamiento_dominio($db, $id_dominio){
	$arreglo_refinamiento=array();
	$puerto_indice=obtener_puerto_indice($db, "D", $id_dominio);
	$refinamiento=$puerto_indice["refinamiento"];
	$arreglo=explode(" ", $refinamiento);
	for($k=0; $k<count($arreglo); $k++){
		if(strlen($arreglo[$k])>0){
			$arreglo_refinamiento[]=$arreglo[$k];
		}
	}
	return $arreglo_refinamiento;
}

function obtener_numero_resultados($db, $query, $id_grupo){
	$consulta = "select numero_resultados from cache_consultas where consulta=\"".$query."\" and id_grupo=\"".$id_grupo."\"";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_numero_resultados) Fallo en Select</h3>");
	if( ($fila=mysql_fetch_row($resultado)) != NULL ){
		return $fila[0];
	}
	return -1;
}

function ingresar_numero_resultados($db, $query, $id_grupo, $numero_resultados){
	if(obtener_numero_resultados($db, $query, $id_grupo)==-1){
		//Si es cierto que no existe, lo ingreso
		$consulta = "insert into cache_consultas (consulta, id_grupo, numero_resultados) values (\"".$query."\", \"".$id_grupo."\", \"".$numero_resultados."\") ";
		$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_numero_resultados) Fallo en Insert</h3>");
	}
	else{
		//Si existe, lo edito
		$consulta = "update cache_consultas set numero_resultados=\"".$numero_resultados."\" where consulta=\"".$query."\" and id_grupo=\"".$id_grupo."\"";
		$resultado = mysql_query($consulta, $db) or die("<h3>(ingresar_numero_resultados) Fallo en Update</h3>");
	}
}

function marcar_grupo_monitoreable($db, $id_grupo){
	$consulta="update grupos set monitoreable=\"1\" where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(marcar_grupo_monitoreable) Fallo en Update</h3>");
}

function desmarcar_grupo_monitoreable($db, $id_grupo){
	$consulta="update grupos set monitoreable=\"0\" where id=\"".$id_grupo."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(desmarcar_grupo_monitoreable) Fallo en Update</h3>");
}

function marcar_dominio_monitoreable($db, $id_dominio){
	$consulta="update dominios set monitoreable=\"1\" where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(marcar_dominio_monitoreable) Fallo en Update</h3>");
}

function desmarcar_dominio_monitoreable($db, $id_dominio){
	$consulta="update dominios set monitoreable=\"0\" where id=\"".$id_dominio."\"";
	$resultado=mysql_query($consulta, $db) or die("<h3>(desmarcar_dominio_monitoreable) Fallo en Update</h3>");
}

function obtener_grupos_monitoreables($db){
	$grupos=array();
	$consulta = "select * from grupos where borrado=0 and monitoreable=1 order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_grupos_monitoreables) Fallo en Select</h3>");
	$contador=0;
	while ( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$grupos[$contador]["id"]=$fila[0];
		$grupos[$contador]["nombre"]=$fila[1];
		$grupos[$contador]["borrado"]=$fila[2];
		$grupos[$contador]["auditable"]=$fila[3];
		$grupos[$contador]["reject"]=$fila[4];
		$grupos[$contador]["monitoreable"]=$fila[5];
		$contador++;
	}
	return $grupos;
}

function obtener_dominios_monitoreables($db){
	$dominios=array();
	$consulta = "select * from dominios where borrado=0 and monitoreable=1 order by id";
	$resultado = mysql_query($consulta, $db) or die("<h3>(obtener_dominios_monitoreables) Fallo en Select</h3>");
	$contador=0;
	while ( ($fila=mysql_fetch_row($resultado)) != NULL ){
		$dominios[$contador]["id"]=$fila[0];
		$dominios[$contador]["nombre"]=$fila[1];
		$dominios[$contador]["id_grupo"]=$fila[2];
		$dominios[$contador]["borrado"]=$fila[3];
		$dominios[$contador]["reject"]=$fila[4];
		$dominios[$contador]["monitoreable"]=$fila[5];
		$contador++;
	}
	return $dominios;
}










?>
