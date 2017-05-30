<?php

require_once("config.ini");
$estructura=obtener_estructura($db);

$archivo="lista_agregar.txt";
$id_grupo=63;
$grupo=filtrar_grupo($estructura, $id_grupo);

echo "<h1>Agregando Textualmente \"".$archivo."\" al grupo \"".$grupo["nombre"]."\"</h1>\n";

if($lector=fopen($archivo, "r")){

	while(!feof($lector)){
		$nombre=trim(fgets($lector));
		
		if(strlen($nombre)>1){
			$puerto=obtener_puerto_random($db);
			$reject="";
			echo "Dominio \"".$nombre."\", Puerto \"".$puerto."\"<br>\n";
			$id_dominio=ingresar_dominio($db, $nombre, $id_grupo, $puerto, $reject);
			if($id_dominio>0){
				$url=trim(fgets($lector));
				if(strlen($url)){
					echo "Url ".$url."<br>\n";
					ingresar_semilla($db, $url, $reject, $id_dominio);
				}
				else{
					echo "Dominio sin semilla<br>\n";
				}
				
			}
		}
		else{
			echo "Linea Vacia<br>\n";
		}
		
		echo "<br>\n";
	}
	
	fclose($lector);
}

mysql_close($db);


?>
