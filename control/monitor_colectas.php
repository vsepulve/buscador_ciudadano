<?php

require_once("config.ini");

echo "<h1>Iniciando Monitoreo...</h1>\n";

$debug=false;
$estructura=obtener_estructura($db);
$debug=false;

//---------- Verificacion de Colectas activas ----------
$colecta_activa_grupo=array();
$colecta_activa_dominio=array();
$colecta_activa_semilla=array();
$debug=false;
verificar_colectas_activas($estructura, $prefijo, $ruta_bin, $minicow);
$debug=false;

$debug=true;
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["nombre"]);
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		print_debug("->Dominio ".$dominios[$j]["nombre"]);
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			print_debug("--->Semilla ".$semillas[$k]["url"]);
			if($colecta_activa_semilla[$semillas[$k]["id"]]){
				print_debug("<div style=\"color:red;\">Activa</div>");
				//aqui hay que desactivarla
				print_debug("iniciando desactivacion...");
				detener_colecta($prefijo, $semillas[$k], $minicow, $ruta_bin);
				print_debug("borrando cache...");
				while(file_exists($ruta_colecta."/".$prefijo."-".$semillas[$k]["id"]."_cache.db")){
					unlink($ruta_colecta."/".$prefijo."-".$semillas[$k]["id"]."_cache.db");
				}
				print_debug("continuando colecta...");
				$semilla=$semillas[$k];
				$grupo=filtrar_grupo_semilla($estructura, $id_semilla);
				$dominio=filtrar_dominio_semilla($estructura, $id_semilla);
				
				if(strlen($grupo["reject"])<1){
					if(strlen($dominio["reject"])<1)
						$reject_base="";
					else
						$reject_base=$dominio["reject"];
				}
				else{
					if(strlen($dominio["reject"])<1)
						$reject_base=$grupo["reject"];
					else
						$reject_base=$grupo["reject"]." , ".$dominio["reject"];
				}
				//sumar las listas de rechazo
				if(strlen($semilla["reject"])<1){
					$semilla["reject"]=$reject_base;
				}
				else if(strlen($reject_base)){
					$semilla["reject"]=$reject_base." , ".$semilla["reject"];
				}
				
				continuar_colecta($prefijo, $semilla, $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones);
			}
			else{
				print_debug("<div style=\"color:green;\">Inactiva</div>");
			}
		}
	}
}
$debug=false;

echo "<h1>Monitoreo Terminado</h1>\n";

?>
