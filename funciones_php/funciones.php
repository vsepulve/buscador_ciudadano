<?php

/*
Aqui estan todas las funciones sin clasificar, es decir, todas las ademas.
*/

//Debe existir el programa inicial llamado $prefijo
function crear_demonio($prefijo, $tipo, $id, $ruta_bin){
	//Demonio de $tipo , 	$id
	if(!file_exists($ruta_bin."/".$prefijo."-".$tipo."-".$id."_")){
		copy($ruta_bin."/".$prefijo, $ruta_bin."/".$prefijo."-".$tipo."-".$id."_");
		chmod($ruta_bin."/".$prefijo."-".$tipo."-".$id."_", 0775);
	}
}

function crear_iniciar_servicio($prefijo, $tipo, $id, $puerto, $ruta_bin, $ruta_libs, $ruta_indice){
	crear_demonio($prefijo, $tipo, $id, $ruta_bin);
	iniciar_servicio($prefijo."-".$tipo."-".$id."_", $puerto, $ruta_bin, $ruta_libs, $ruta_indice);
	return existe_proceso($ruta_bin."/".$prefijo."-".$tipo."-".$id."_");

}

//Debe existir el programa llamado $demonio
function iniciar_servicio($demonio, $puerto, $ruta_bin, $ruta_libs, $ruta_indice){
	$comando="export LD_LIBRARY_PATH=$ruta_libs";
	$comando.="; killall $ruta_bin/$demonio";
	$comando.="; nohup $ruta_bin/$demonio 0 $puerto $ruta_indice/$demonio 127.0.0.1 > /dev/null &";
	$comando.=" 2>&1";
	//echo $comando."<br>";
	system($comando);
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";

}

function terminar_servicio($demonio, $ruta_bin){
	$usuario="www-data";
	$procesos=informacion_procesos("$ruta_bin/$demonio");
	for($i=0; $i<count($procesos); $i++){
		if(strcmp($procesos[$i]["UID"], $usuario)===0){
			while(existe_proceso_pid($procesos[$i]["PID"])){
				$comando="kill -9 ".$procesos[0]["PID"]."";
				exec($comando, $result);
				//for($k=0; $k<count($result); $k++)
				//	print_debug($result[$k]."");
			}
		}
		else{
			print_debug("Proceso de usuario \"".$procesos[$i]["UID"]."\" no puede ser terminado");
		}
	}
}

function existe_proceso_pid($pid){
	$comando="ps -p $pid";
	exec($comando, $output);
	print_debug("Procesos por pid \"$pid\": ".count($output)."");
	if(count($output)>1){
		return true;
	}
	else{
		return false;
	}
}

function existe_proceso($programa){
	$comando="ps -fea | grep \"[0-9][0-9]:[0-9][0-9]:[0-9][0-9] ".$programa."[ ]*\"";
	exec($comando, $output);
	if(count($output)==1){
		return true;
	}		
	else if(count($output)==0){
		return false;
	}
	else{
		return count($output);
	}
}

//Retorna un arreglo con la siguiente estructura, para cada proceso de un comando dado
//UID PID PPID C STIME TTY TIME CMD ARG[]
function informacion_procesos($programa){
	$categorias=array("UID", "PID", "PPID", "C", "STIME", "TTY", "TIME", "CMD");
	$numero_categorias=count($categorias);
	$procesos=array();
	$comando="ps -fea | grep \"[0-9][0-9]:[0-9][0-9]:[0-9][0-9] ".$programa."[ ]*\"";
	exec($comando, $salida);
	for($i=0;$i<count($salida);$i++){
		//echo "\"".$salida[$i]."\"<br>";
		$lineas=split(' ', $salida[$i]);
		$categorias_agregadas=0;
		$argumentos_agregados=0;
		$argumentos=array();
		for($j=0; $j<count($lineas); $j++){
			if($lineas[$j]!=null){
				//echo "Linea $j: \"$lineas[$j]\" ";
				if($categorias_agregadas<$numero_categorias){
					//Categorias
					//echo "(categoria $categorias[$categorias_agregadas]) ";
					$procesos[$i][$categorias[$categorias_agregadas]]=$lineas[$j];
					$categorias_agregadas++;
					
				}
				else{
					//Es argumento
					//echo "(argumento $argumentos_agregados) ";
					$argumentos[$argumentos_agregados]=$lineas[$j];
					$argumentos_agregados++;
				}
				//echo "<br>";
			}
		}
		$procesos[$i]["ARG"]=$argumentos;
	}
	return $procesos;
}

function print_debug($texto){
	global $debug;
	if($debug)
		print "$texto<br>\n";
}

function esta_todo_ok($tipo, $id, $prefijo, $ruta_indice, $extensiones_indice){
	$minimo=4;
	$este_prefijo=$ruta_indice."/".$prefijo."-".$tipo."-".$id."_";
	$numero_extensiones=count($extensiones_indice);
	for($i=0; $i<$numero_extensiones; $i++){
		if(file_exists($este_prefijo.$extensiones_indice[$i])){
			if($minimo>filesize($este_prefijo.$extensiones_indice[$i])){
				//print "[".filesize($este_prefijo.$extensiones_indice[$i])."]<br>";
				return false;
			}
		}
		else{
			return false;
		}
	}
	return true;
}

//devuelve un arreglo de boolean indicando cuales archivos de $extensiones_colecta existen
function existe_colecta($prefijo, $id_semilla, $ruta_colecta, $extensiones_colecta){
	$arreglo_existe=array();
	$numero_extensiones=count($extensiones_colecta);
	print_debug("existe_colecta \"$prefijo-$id_semilla"."_\" ($numero_extensiones extensiones)");
	$este_prefijo="$ruta_indice/$prefijo-$id_semilla"."_";
	for($i=0; $i<$numero_extensiones; $i++){
		$arreglo_existe[$i]=file_exists($este_prefijo.$extensiones_colecta[$i]);
	}
	return $arreglo_existe;
}

//devuelve un arreglo indicando el tamaño de los archivos de $extensiones_colecta, -1 si no existen
function tamaño_colecta($prefijo, $id_semilla, $ruta_colecta, $extensiones_colecta){
	$arreglo_tamaño=array();
	$numero_extensiones=count($extensiones_colecta);
	print_debug("tama&ntilde;o_colecta \"$prefijo-$id_semilla"."_\" ($numero_extensiones extensiones)");
	$este_prefijo="$ruta_colecta/$prefijo-$id_semilla"."_";
	for($i=0; $i<$numero_extensiones; $i++){
		if(file_exists($este_prefijo.$extensiones_colecta[$i])){
			//print_debug("Archivo \"".$este_prefijo.$extensiones_colecta[$i]."\" existe");
			$arreglo_tamaño[$i]=filesize($este_prefijo.$extensiones_colecta[$i]);
		}
		else
			$arreglo_tamaño[$i]=-1;
	}
	return $arreglo_tamaño;
}

//devuelve un arreglo de boolean indicando cuales archivos de $extensiones_indice existen
function existe_indice($prefijo, $tipo, $id, $ruta_indice, $extensiones_indice){
	print_debug("existe_indice \"$prefijo-$tipo-$id\" (".count($extensiones_indice)." extensiones)");
	$arreglo_existe=array();
	$numero_extensiones=count($extensiones_indice);
	$este_prefijo="$ruta_indice/$prefijo-$tipo-$id"."_";
	for($i=0; $i<$numero_extensiones; $i++){
		//print_debug(" - ".$este_prefijo.$extensiones_indice[$i]."");
		$arreglo_existe[$i]=file_exists($este_prefijo.$extensiones_indice[$i]);
	}
	return $arreglo_existe;
}

//devuelve un arreglo indicando el tamaño de los archivos de $extensiones_indice, -1 si no existen
function tamaño_indice($prefijo, $tipo, $id, $ruta_indice, $extensiones_indice){
	print_debug("tama&ntilde;o_indice \"$prefijo-$tipo-$id\" (".count($extensiones_indice)." extensiones)");
	$arreglo_tamaño=array();
	$numero_extensiones=count($extensiones_indice);
	$este_prefijo="$ruta_indice/$prefijo-$tipo-$id"."_";
	for($i=0; $i<$numero_extensiones; $i++){
		//print_dabug("Archivo[$i]: ".filesize($este_prefijo.$extensiones_indice[$i])."");
		if(file_exists($este_prefijo.$extensiones_indice[$i]))
			$arreglo_tamaño[$i]=filesize($este_prefijo.$extensiones_indice[$i]);
		else
			$arreglo_tamaño[$i]=-1;
	}
	return $arreglo_tamaño;
}

//Inicia la colecta de una semilla particular.
//No deben haber minicows equivalentes activos
function iniciar_colecta($prefijo, $semilla, $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones){
	print_debug("preparando $minicow para \"".$semilla["url"]."\"...");
	$este_prefijo=$prefijo."-".$semilla["id"]."_";
	$comando="export LD_LIBRARY_PATH=$ruta_libs";
	$comando.="; nohup $ruta_bin/$minicow -o $este_prefijo.crf0 -p $ruta_colecta -b $este_prefijo -e $archivo_extensiones -R \"".$semilla["reject"]."\" -h -w 1 -i 1 -u ".$semilla["url"];
	print_debug("<br>$comando<br>");
	exec("($comando) > $ruta_logs/$este_prefijo &");
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";
}

//Detiene la colecta de una semilla particular.
function detener_colecta($prefijo, $semilla, $minicow, $ruta_bin){
	$este_prefijo=$prefijo."-".$semilla["id"]."_";
	$procesos=informacion_procesos("$ruta_bin/$minicow");
	print_debug("terminando $minicow \"$este_prefijo\" (".count($procesos)." procesos)");
	for($i=0; $i<count($procesos); $i++){
		for($j=0; $j<count($procesos[$i]["ARG"]); $j++){
			if($procesos[$i]["ARG"][$j]=="-b"){
					//echo strpos($procesos[$j]["ARG"][1+$k], "$este_prefijo")."<br>";
					if(strpos($procesos[$i]["ARG"][1+$j], $este_prefijo)===0){
						print_debug("Terminando proceso $i (pid: ".$procesos[$i]["PID"].")");
						$comando="kill -9 ".$procesos[$i]["PID"]."";
						$comando.=" 2>&1";
						exec($comando, $result);
						for($k=0; $k<count($result); $k++)
							echo $result[$k]."<br>";
					}//if... hay conflicto
					break;
				}//if.. argumento correcto
		}//for... cada argumento
		
	}//for... cada proceso
}

//Inicia la colecta de una semilla particular.
//No deben haber minicows equivalentes activos
function continuar_colecta($prefijo, $semilla, $minicow, $ruta_bin, $ruta_libs, $ruta_colecta, $ruta_logs, $archivo_extensiones){
	print_debug("preparando $minicow para \"".$semilla["url"]."\"...");
	$este_prefijo=$prefijo."-".$semilla["id"]."_";
	$comando="export LD_LIBRARY_PATH=$ruta_libs";
	$comando.="; nohup $ruta_bin/$minicow -o $este_prefijo.crf0 -p $ruta_colecta -b $este_prefijo -e $archivo_extensiones -R \"".$semilla["reject"]."\" -c -h -w 1 -i 1 -u ".$semilla["url"];
	print_debug("<br>$comando<br>");
	exec("($comando) > $ruta_logs/$este_prefijo &");
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";
}

function verificar_estado_colectas($estructura, $prefijo, $ruta_colecta, $extensiones_colecta){
	global $colecta_correcta_grupo;
	global $colecta_correcta_dominio;
	global $colecta_correcta_semilla;
	$minimo=0;
	$numero_extensiones=count($extensiones_colecta);
	print_debug("<br>Revisando estado de colectas");
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		$colecta_correcta_grupo[$grupos[$i]["id"]]=false;
		/*
		if(count($dominios)>0){
			print_debug("grupo ".$grupos[$i]["id"]." tiene algo");
			$colecta_correcta_grupo[$grupos[$i]["id"]]=true;
			}
		else{
			print_debug("grupo ".$grupos[$i]["id"]." vacio");
			$colecta_correcta_grupo[$grupos[$i]["id"]]=false;
			}
		*/
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			$colecta_correcta_dominio[$dominios[$j]["id"]]=false;
			/*
			if(count($semillas)>0){
				print_debug("dominio ".$dominios[$j]["id"]." tiene algo");
				$colecta_correcta_dominio[$dominios[$j]["id"]]=true;
				}
			else{
				print_debug("dominio ".$dominios[$j]["id"]." vacio");
				$colecta_correcta_dominio[$dominios[$j]["id"]]=false;
				$colecta_correcta_grupo[$grupos[$i]["id"]]=false;
				}
			*/
			for($k=0; $k<count($semillas); $k++){
				$colecta_correcta_semilla[$semillas[$k]["id"]]=true;
				$este_prefijo=$prefijo."-".$semillas[$k]["id"];
				$archivos_esta_colecta=tamaño_colecta($prefijo, $semillas[$k]["id"], $ruta_colecta, $extensiones_colecta);
				
				for($l=0; $l<$numero_extensiones; $l++){
					if($archivos_esta_colecta[$l]<$minimo){
						print_debug($archivos_esta_colecta[$l]." menor que minimo");
						$colecta_correcta_semilla[$semillas[$k]["id"]]=false;
						//$colecta_correcta_dominio[$dominios[$j]["id"]]=false;
						//$colecta_correcta_grupo[$grupos[$i]["id"]]=false;
						break;
					}
					
				}//for... cada extension
			}//for... cada semilla
			
			//Si hay alguna semilla correcta, la colecta de dominio es valida, y tambien la de grupo
			for($k=0; $k<count($semillas); $k++){
				if($colecta_correcta_semilla[$semillas[$k]["id"]]){
					$colecta_correcta_grupo[$grupos[$i]["id"]]=true;
					$colecta_correcta_dominio[$dominios[$j]["id"]]=true;
					break;
				}
			}
			
		}//for... cada dominio
	}//for... cada grupo
	
}

function verificar_indices_completos($db, $estructura){
	
	global $indice_completo_grupo;
	global $indice_completo_dominio;
	
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$logs=obtener_log_creacion_indice($db, "G", $grupos[$i]["id"]);
		if($logs){
			$log=$logs[0];
			$semillas_agrupadas=$log["semillas_agrupadas"];
			$numero_semillas_agrupadas=count($semillas_agrupadas);
			$semillas=filtrar_semillas_grupo($estructura, $grupos[$i]["id"]);
			$numero_semillas=count($semillas);
			print_debug("$numero_semillas_agrupadas semillas agrupadas");
			$indice_completo_grupo[$grupos[$i]["id"]]=true;
			for($j=0; $j<$numero_semillas; $j++){
				$encontrada=false;
				for($k=0; $k<$numero_semillas_agrupadas; $k++){
					if($semillas[$j]["id"]==$semillas_agrupadas[$k]["id_semilla"]){
						$encontrada=true;
						break;
					}
				}
				if($encontrada){
					print_debug("semilla ".$semillas[$j]["id"]." encontrada");
				}
				else{
					print_debug("semilla ".$semillas[$j]["id"]." no encontrada");
					$indice_completo_grupo[$grupos[$i]["id"]]=false;
					break;
				}
			}
		}
		else{
			print_debug("Grupo ".$grupos[$i]["nombre"]." - sin log");
			$indice_completo_grupo[$grupos[$i]["id"]]=false;
		}
	
		$dominios=$estructura["dominios"][$i];
		for($l=0; $l<count($dominios); $l++){
			$logs=obtener_log_creacion_indice($db, "D", $dominios[$l]["id"]);
			if($logs){
				$log=$logs[0];
				$semillas_agrupadas=$log["semillas_agrupadas"];
				$numero_semillas_agrupadas=count($semillas_agrupadas);
				$semillas=filtrar_semillas_dominio($estructura, $dominios[$l]["id"]);
				$numero_semillas=count($semillas);
				print_debug("$numero_semillas_agrupadas semillas agrupadas");
				$indice_completo_dominio[$dominios[$l]["id"]]=true;
				for($j=0; $j<$numero_semillas; $j++){
					$encontrada=false;
					for($k=0; $k<$numero_semillas_agrupadas; $k++){
						if($semillas[$j]["id"]==$semillas_agrupadas[$k]["id_semilla"]){
							$encontrada=true;
							break;
						}
					}
					if($encontrada){
						print_debug("semilla ".$semillas[$j]["id"]." encontrada");
					}
					else{
						print_debug("semilla ".$semillas[$j]["id"]." no encontrada");
						$indice_completo_dominio[$dominios[$l]["id"]]=false;
						break;
					}
				}
			}
			else{
				print_debug("Dominio ".$dominios[$l]["nombre"]." - sin log");
				$indice_completo_dominio[$dominios[$l]["id"]]=false;
			}
		}//for... cada dominio
	
	}//for... cada grupo
}

function verificar_dias_indice($db, $estructura){
	
	global $dias_indice_grupo;
	global $dias_indice_dominio;
	global $semilla_incluida_grupo;
	global $semilla_incluida_dominio;

	$tiempo_actual=mktime();
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$logs=obtener_log_creacion_indice($db, "G", $grupos[$i]["id"]);
		if($logs){
			$log=$logs[0];
			$tiempo=segundos_fecha($log["fecha"]);
			$diferencia=$tiempo_actual-$tiempo;
			$dias=number_format(($diferencia/(24*3600)), 0);
			print_debug("Grupo ".$grupos[$i]["nombre"]." - $dias dias");
			$dias_indice_grupo[$grupos[$i]["id"]]=$dias;
			$semillas_agrupadas=$log["semillas_agrupadas"];
			$numero_semillas=count($semillas_agrupadas);
			print_debug("$numero_semillas semillas agrupadas");
			for($j=0; $j<$numero_semillas; $j++){
				$semilla_incluida_grupo[$semillas_agrupadas[$j]["id_semilla"]]=true;
			}
		}
		else{
			print_debug("Grupo ".$grupos[$i]["nombre"]." - sin log");
			$dias_indice_grupo[$grupos[$i]["id"]]=-1;
		}
	
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$logs=obtener_log_creacion_indice($db, "D", $dominios[$j]["id"]);
			if($logs){
				$log=$logs[0];
				$tiempo=segundos_fecha($log["fecha"]);
				$diferencia=$tiempo_actual-$tiempo;
				$dias=number_format(($diferencia/(24*3600)), 0);
				print_debug("Dominio ".$dominios[$j]["nombre"]." - $dias dias");
				$dias_indice_dominio[$dominios[$j]["id"]]=$dias;
				$semillas_agrupadas=$log["semillas_agrupadas"];
				$numero_semillas=count($semillas_agrupadas);
				print_debug("$numero_semillas semillas agrupadas");
				for($k=0; $k<$numero_semillas; $k++){
					$semilla_incluida_dominio[$semillas_agrupadas[$k]["id_semilla"]]=true;
				}
			}
			else{
				print_debug("Dominio ".$dominios[$j]["nombre"]." - sin log");
				$dias_indice_dominio[$dominios[$j]["id"]]=-1;
			}
		}//for... cada dominio
		
	}//for... cada grupo

}

function verificar_dias_colecta($db, $estructura){
	global $dias_colecta_grupo;
	global $dias_colecta_dominio;
	global $dias_colecta_semilla;
	$tiempo_actual=mktime();
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$logs=obtener_log_inicio_colecta($db, "G", $grupos[$i]["id"]);
		if($logs){
			$log=$logs[0];
			$tiempo=segundos_fecha($log["fecha"]);
			$diferencia=$tiempo_actual-$tiempo;
			$dias=number_format(($diferencia/(24*3600)), 0);
			print_debug("Grupo ".$grupos[$i]["nombre"]." - $dias dias");
			$dias_colecta_grupo[$grupos[$i]["id"]]=$dias;
		}
		else{
			print_debug("Grupo ".$grupos[$i]["nombre"]." - sin log");
			$dias_colecta_grupo[$grupos[$i]["id"]]=-1;
		}
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$logs=obtener_log_inicio_colecta($db, "D", $dominios[$j]["id"]);
			if($logs){
				$log=$logs[0];
				$tiempo=segundos_fecha($log["fecha"]);
				$diferencia=$tiempo_actual-$tiempo;
				$dias=number_format(($diferencia/(24*3600)), 0);
				print_debug("Dominio ".$dominios[$j]["nombre"]." - $dias dias");
				if($dias_colecta_grupo[$grupos[$i]["id"]]<0
					|| $dias<$dias_colecta_grupo[$grupos[$i]["id"]]){
					print_debug("menor que que el de grupo");
					$dias_colecta_dominio[$dominios[$j]["id"]]=$dias;
				}
				else{
					print_debug("mayor que el de grupo");
					$dias_colecta_dominio[$dominios[$j]["id"]]=$dias_colecta_grupo[$grupos[$i]["id"]];
				}
			}
			else{
				print_debug("Dominio ".$dominios[$j]["nombre"]." - sin log");
				$dias_colecta_dominio[$dominios[$j]["id"]]=$dias_colecta_grupo[$grupos[$i]["id"]];
			}
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				$log=obtener_ultimo_log_semilla($db, $semillas[$k]["id"]);
				if($log){
					$tiempo=segundos_fecha($log["fecha"]);
					$diferencia=$tiempo_actual-$tiempo;
					$dias=number_format(($diferencia/(24*3600)), 0);
					if($dias_colecta_dominio[$dominios[$j]["id"]]<0
						|| $dias<$dias_colecta_dominio[$dominios[$j]["id"]])
						$dias_colecta_semilla[$semillas[$k]["id"]]=$dias;
					else
						$dias_colecta_semilla[$semillas[$k]["id"]]=$dias_colecta_dominio[$dominios[$j]["id"]];
				}
				else{
					$dias_colecta_semilla[$semillas[$k]["id"]]=$dias_colecta_dominio[$dominios[$j]["id"]];
				}
			}
		}
	}
}

function verificar_tamaño_colectas($estructura, $prefijo, $ruta_colecta, $extensiones_colecta){
	global $tamaño_colecta_grupo;
	global $tamaño_colecta_dominio;
	global $tamaño_colecta_semilla;
	
	//Limipiar Extensiones
	$extensiones_limpias=array();
	$extensiones_rechazadas=array(".log");
	for($i=0; $i<count($extensiones_colecta); $i++){
		$aceptada=true;
		for($j=0; $j<count($extensiones_rechazadas); $j++){
			if(strcmp($extensiones_colecta[$i], $extensiones_rechazadas[$j])===0){
				$aceptada=false;
			}
		}
		if($aceptada){
			$extensiones_limpias[]=$extensiones_colecta[$i];
		}
	}
	$extensiones_colecta=$extensiones_limpias;
	
	$numero_extensiones=count($extensiones_colecta);
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$tamaño_colecta_grupo[$grupos[$i]["id"]]=0;
		$sin_colecta_grupo=true;
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$tamaño_colecta_dominio[$dominios[$j]["id"]]=0;
			$sin_colecta_dominio=true;
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas);$k++){
				$tamaño_colecta_semilla[$semillas[$k]["id"]]=0;
				$sin_colecta_semilla=true;
				$colecta=tamaño_colecta($prefijo, $semillas[$k]["id"], $ruta_colecta, $extensiones_colecta);
				for($l=0; $l<$numero_extensiones; $l++){
					if($colecta>=0){
						$sin_colecta_semilla=false;
						$tamaño_colecta_semilla[$semillas[$k]["id"]]+=$colecta[$l];
					}
				}
				if($sin_colecta_semilla){
					$tamaño_colecta_semilla[$semillas[$k]["id"]]=-1;
				}
				else{
					$sin_colecta_dominio=false;
					$tamaño_colecta_dominio[$dominios[$j]["id"]]+=$tamaño_colecta_semilla[$semillas[$k]["id"]];
				}
			}
			if($sin_colecta_dominio){
				$tamaño_colecta_dominio[$dominios[$j]["id"]]=-1;
			}
			else{
				$sin_colecta_grupo=false;
				$tamaño_colecta_grupo[$grupos[$i]["id"]]+=$tamaño_colecta_dominio[$dominios[$j]["id"]];
			}
		}
		if($sin_colecta_grupo){
			$tamaño_colecta_grupo[$grupos[$i]["id"]]=-1;
		}
	}
}

function verificar_colectas_activas($estructura, $prefijo, $ruta_bin, $minicow){
	global $colecta_activa_semilla;
	global $colecta_activa_dominio;
	global $colecta_activa_grupo;
	$procesos=informacion_procesos("$ruta_bin/$minicow");
	print_debug("<br>Revisando ".count($procesos)." procesos");
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		$colecta_activa_grupo[$grupos[$i]["id"]]=false;
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			$colecta_activa_dominio[$dominios[$j]["id"]]=false;
			for($k=0; $k<count($semillas); $k++){
				$colecta_activa_semilla[$semillas[$k]["id"]]=false;
				$este_prefijo=$prefijo."-".$semillas[$k]["id"]."_";
				for($l=0; $l<count($procesos); $l++){
					print_debug("$este_prefijo, proceso $l");
					for($m=0; $m<count($procesos[$l]["ARG"]); $m++){
						if($procesos[$l]["ARG"][$m]=="-b"){
							//print_debug("Posicion: ".strpos($procesos[$l]["ARG"][1+$m], $este_prefijo)."");
							if(strcmp($procesos[$l]["ARG"][1+$m], $este_prefijo)===0){
							//if(strpos($procesos[$l]["ARG"][1+$m], $este_prefijo)===0){
								$colecta_activa_semilla[$semillas[$k]["id"]]=true;
								$colecta_activa_dominio[$dominios[$j]["id"]]=true;
								$colecta_activa_grupo[$grupos[$i]["id"]]=true;
							}//if... hay conflicto
							break;
						}//if.. argumento correcto
					}//for... cada argumento
				}//for... cada proceso
			}
		}//for... cada dominio
	}//for... cada grupo
}

function colecta_terminada($prefijo, $id_semilla, $ruta_colecta){
	$nombre_log=$ruta_colecta."/".$prefijo."-".$id_semilla."_.log";
	print_debug("revisando colecta_completa de \"$nombre_log\"");
	if(file_exists($nombre_log)){
		$log=fopen($nombre_log, "r");
		$largo=filesize($nombre_log);
		print_debug("$largo bytes (deben haber mas de 16)");
		if($largo<17)
			return false;
		fseek($log, $largo-17);
		$linea=(fread($log, 16));
		print_debug("\"$linea\"");
		fclose($log);
		if(strcmp($linea, "End of execution")===0)
			return true;
		else
			return false;
	}
	else{
		print_debug("No existe colecta");
		return false;
	}
}

function verificar_colectas_terminadas($estructura, $prefijo, $ruta_colecta, $numero_semillas_grupo, $numero_semillas_dominio){
	
	global $colecta_terminada_grupo;
	global $colecta_terminada_dominio;
	global $colecta_terminada_semilla;
	
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($numero_semillas_grupo[$grupos[$i]["id"]])
			$colecta_terminada_grupo[$grupos[$i]["id"]]=true;
		else
			$colecta_terminada_grupo[$grupos[$i]["id"]]=false;
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			if($numero_semillas_dominio[$dominios[$j]["id"]])
				$colecta_terminada_dominio[$dominios[$j]["id"]]=true;
			else
				$colecta_terminada_dominio[$dominios[$j]["id"]]=false;
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				$completa=colecta_terminada($prefijo, $semillas[$k]["id"], $ruta_colecta);
				$colecta_terminada_semilla[$semillas[$k]["id"]]=$completa;
				if(!$completa){
					$colecta_terminada_grupo[$grupos[$i]["id"]]=false;
					$colecta_terminada_dominio[$dominios[$j]["id"]]=false;
				}
			}
		}
	}
}

function verificar_estado_indices($estructura, $prefijo, $ruta_indice, $extensiones_indice){

	global $indice_correcto_grupo;
	global $indice_correcto_dominio;

	$minimo=4;
	$numero_extensiones=count($extensiones_indice);
	print_debug("<br>Revisando estado de indices");
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){

		$indice_correcto_grupo[$grupos[$i]["id"]]=true;
		$archivos_este_indice=tamaño_indice($prefijo, "G", $grupos[$i]["id"], $ruta_indice, $extensiones_indice);
		for($k=0; $k<$numero_extensiones; $k++){
			if($archivos_este_indice[$k]<$minimo){
				$indice_correcto_grupo[$grupos[$i]["id"]]=false;
				break;
			}
		}//for... cada extension
	
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$archivos_este_indice=tamaño_indice($prefijo, "D", $dominios[$j]["id"], $ruta_indice, $extensiones_indice);
			$indice_correcto_dominio[$dominios[$j]["id"]]=true;
			for($k=0; $k<$numero_extensiones; $k++){
				if($archivos_este_indice[$k]<$minimo){
					$indice_correcto_dominio[$dominios[$j]["id"]]=false;
					break;
				}
			}//for... cada extension
		}//for... cada dominio
	}//for... cada grupo
	
}

function verificar_colectas_hijos($estructura, $colecta_correcta_semilla){

	global $colecta_hijos_grupo;
	global $colecta_hijos_dominio;

	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		print_debug("Colecta Hijos - Grupo ".$grupos[$i]["id"]);
		$colecta_hijos_grupo[$grupos[$i]["id"]]=false;
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			print_debug("Colecta Hijos - Dominio ".$dominio[$j]["id"]);
			$colecta_hijos_dominio[$dominios[$j]["id"]]=false;
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				if($colecta_correcta_semilla[$semillas[$k]["id"]]){
					print_debug("Colecta correcta");
					$colecta_hijos_grupo[$grupos[$i]["id"]]=true;
					$colecta_hijos_dominio[$dominios[$j]["id"]]=true;
					break;
				}
			}
		}
	
	}
}

function verificar_numero_semillas($estructura){
	
	global $numero_semillas_grupo;
	global $numero_semillas_dominio;

	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$numero_semillas_grupo[$grupos[$i]["id"]]=0;
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			$numero_semillas_dominio[$dominios[$j]["id"]]=count($semillas);
			$numero_semillas_grupo[$grupos[$i]["id"]]+=$numero_semillas_dominio[$dominios[$j]["id"]];
		}
	}
}

function verificar_servicios_activos($estructura, $prefijo, $usuario, $ruta_bin){
	
	global $servicio_activo_grupo;
	global $servicio_activo_dominio;
	
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$proceso=$ruta_bin."/".$prefijo."-G-".$grupos[$i]["id"]."_";
		$procesos_activos=informacion_procesos($proceso);
		print_debug(count($procesos_activos)." procesos (".$proceso.")");
		if(count($procesos_activos)==1 && $procesos_activos[0]["UID"]==$usuario){
			//Proceso ok
			$servicio_activo_grupo[$grupos[$i]["id"]]=true;
			print_debug("Ok");
		}
		else{
			//0 procesos, mas de 1, o no es del usuario correcto
			$servicio_activo_grupo[$grupos[$i]["id"]]=false;
			print_debug("Problema");
		}
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$proceso=$ruta_bin."/".$prefijo."-D-".$dominios[$j]["id"]."_";
			$procesos_activos=informacion_procesos($proceso);
			print_debug(count($procesos_activos)." procesos (".$proceso.")");
			if(count($procesos_activos)==1 && $procesos_activos[0]["UID"]==$usuario){
				//Proceso ok
				$servicio_activo_dominio[$dominios[$j]["id"]]=true;
				print_debug("Ok");
			}
			else{
				//0 procesos, mas de 1, o no es del usuario correcto
				$servicio_activo_dominio[$dominios[$j]["id"]]=false;
				print_debug("Problema");
			}
		}//for... cada dominio del grupo
	
	}//for... cada grupo
}

function agrupar_colectas($prefijo, $tipo, $id, $id_semillas, $ruta_bin, $ruta_colecta, $ruta_indice){
	
	$nombre_indice=$prefijo."-$tipo-".$id."_";
	print_debug("<b>agrupar_colectas</b> ($nombre_indice)");
	
	$comando="rm $ruta_indice/$nombre_indice*";
	$comando.="; rm $ruta_indice/index0/$nombre_indice*";
	$comando.="; rm $ruta_indice/tempindex*/$nombre_indice*";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";

	for($i=0; $i<count($id_semillas); $i++){
		$comando="cd $ruta_indice";
		//print_debug("$ruta_bin/grouper.pl $ruta_colecta/$prefijo-".$id_semillas[$i]."_ $nombre_indice . .");
		$comando.="; $ruta_bin/grouper.pl $ruta_colecta/$prefijo-".$id_semillas[$i]."_ $nombre_indice . .";
		$comando.=" 2>&1";
		system($comando, $result);
		for($j=0; $j<count($result); $j++)
			echo $result[$j]."<br>";
	}
	
	print_debug("Colectas de \"$nombre_indice\" agrupadas");
	
}

function crear_indice($prefijo, $tipo, $id, $ruta_bin, $ruta_indice){
	
	$nombre_indice=$prefijo."-$tipo-".$id."_";
	print_debug("<b>crear_indice</b> ($nombre_indice)");
	
	print_debug("todoindex -C...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/todoindex -C $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("todoindex -A...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/todoindex -A $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("todoindex -M...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/todoindex -M $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("todoindex -Z...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/todoindex -Z $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("terminando indice...");
	$comando="cd $ruta_indice";
	$comando.="; cp index0/$nombre_indice* .";
	$comando.=" 2>&1";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";

	print_debug("converte_lista...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/converte_lista $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("gidf...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/gidf $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("norma...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/norma $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	print_debug("calcula_largo...");
	$comando="cd $ruta_indice";
	$comando.="; $ruta_bin/calcula_largo $nombre_indice";
	$comando.=" 2>&1";
	system($comando, $result);
	for($i=0; $i<count($result); $i++)
		echo $result[$i]."<br>";

	$comando="mv $ruta_indice/newmeta.idx $ruta_indice/$nombre_indice"."meta.idx";
	$comando.="; mv $ruta_indice/newmeta0 $ruta_indice/$nombre_indice"."meta0";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";
	
	print_debug("Indice \"$nombre_indice\" Terminado");
	
}

function verificar_nombre($nombre){
	$caracteres=array("<", ">", "'", "\\", "\"", "@");
	if(strlen($nombre)>0){
		$largo=count($caracteres);
		for($i=0; $i<$largo; $i++){
			$posicion=strpos($nombre, $caracteres[$i]);
			if(! ($posicion===false)){
				print_debug("Caracter [".$caracteres[$i]."] encontrado ($nombre)");
				return false;
			}
		}
		return true;
	}
	return false;
}

function verificar_url($url){

	if(@fopen($url, "r")){
		return true;
	}
	else{
		print_debug("la direccion \"$url\" no es accesible");
		return false;
	}
	/*
	//Tiene que haber http(s)://
	//Luego tiene que seguir una secuencia alfanumerica, seguioda de un punto
	//Luego puede venir otra secuencia alfanumerica con algunos caracteres permitidos
	$patron="^(http(s?):\/\/)([[:alpha:]|[:alnum:]]+\.[[:alpha:]|[:alnum:]|_|-|.|/|?|&|=|#]+)$";
	if(eregi($patron, $url)){
		return true;
	}
	else{
		print_debug("\"$url\" ... No es URL");
		return false;
	}
	*/
}

function verificar_puerto_valido($puerto){
	global $min_puerto, $max_puerto;
	if(!ctype_digit($puerto)){
		return false;
	}
	if($puerto<$min_puerto || $puerto>$max_puerto){
		return false;
	}
	return true;
}

function eliminar_colecta($prefijo, $id_semilla, $ruta_colecta, $ruta_logs){
	//eliminar colecta
	$este_prefijo=$prefijo."-".$id_semilla."_";
	$comando="rm $ruta_colecta/$este_prefijo*";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	print_debug($result[$i]."");
	
	$comando="rm $ruta_colecta/.$este_prefijo*";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	print_debug($result[$i]."");
	
	//eliminar log adicional
	$comando="rm $ruta_logs/$este_prefijo*";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	print_debug($result[$i]."");
}

function eliminar_indice($prefijo, $tipo, $id, $ruta_indice){
	$este_prefijo=$prefijo."-".$tipo."-".$id."_";
	$comando="rm $ruta_indice/$este_prefijo*";
	$comando.="; rm $ruta_indice/index0/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex0/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex1/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex2/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex3/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex4/$este_prefijo*";
	$comando.="; rm $ruta_indice/tempindex5/$este_prefijo*";
	system($comando, $result);
	//for($i=0; $i<count($result); $i++)
	//	echo $result[$i]."<br>";
}

function eliminar_demonio($prefijo, $tipo, $id, $ruta_bin){
	$este_prefijo=$prefijo."-".$tipo."-".$id."_";
	terminar_servicio($este_prefijo, $ruta_bin);
	if(file_exists($ruta_bin."/".$este_prefijo))
		unlink($ruta_bin."/".$este_prefijo);
}

function sumar_fecha($fecha, $suma){
	$tiempo=segundos_fecha($fecha);
	$tiempo_sumado=$tiempo+$suma;
	$fecha_sumada=date("Y-m-d H:i:s", $tiempo_sumado);
	return $fecha_sumada;
}

function segundos_fecha($fecha){
	$arreglo=split(" ", $fecha);
	$arreglo_izq=split("-", $arreglo[0]);
	$aaaa=$arreglo_izq[0];
	$mm=$arreglo_izq[1];
	$dd=$arreglo_izq[2];
	$arreglo_der=split(":", $arreglo[1]);
	$hh=$arreglo_der[0];
	$ii=$arreglo_der[1];
	$ss=$arreglo_der[2];
	//echo "Original: $aaaa-$mm-$dd $hh-$ii-$ss<br>";
	$tiempo=mktime($hh, $ii, $ss, $mm, $dd, $aaaa);
	return $tiempo;
}

function texto_tamaño($tamaño){
	if($tamaño<1024){
		return $tamaño." B";
	}
	else if($tamaño<(1024*1024)){
		return number_format(($tamaño/1024), 2)." KB";
	}
	else if($tamaño<(1024*1024*1024)){
		return number_format(($tamaño/(1024*1024)), 2)." MB";
	}
	else{
		return number_format(($tamaño/(1024*1024*1024)), 2)." GB";
	}
}

function nueva_ruta_archivo($directorio, $archivo){
	if(!file_exists($directorio."/".$archivo)){
		//No hay conflicto
		return $directorio."/".$archivo;
	}
	else{
		//Ya existe uno (pueden haber copias)
		$n=strlen($archivo)-1;
		while($n>0){
			if(substr($archivo, $n--, 1)==".")
				break;
		}
		if($n>0){
			//habia punto
			$extension=substr($archivo, $n+1);
		}
		else{
			//No habia punto
			$extension="";
		}
		$posicion=strpos($archivo, "_copia_");
		if($posicion){
			//ya era copia
			$base=substr($archivo, 0, $posicion);
		}
		else if($n>0){
			//No es copia
			$base=substr($archivo, 0, strpos($archivo, "."));
		}
		else{
			$base=$archivo;
		}
		$contador=1;
		while(file_exists($directorio."/".$base."_copia_$contador".$extension))
			$contador++;
		echo "Base: \"$base\"<br>";
		echo "Contador: \"$contador\"<br>";
		echo "Extension: \"$extension\"<br>";
		return $directorio."/".$base."_copia_$contador".$extension;
	}
}

function string_random($largo, $simbolos){
	if($simbolos)
		$abc="abscdefghijklmnñopqrstuvwxyz!\"#$%&/()=?¡";
	else
		$abc="abscdefghijklmnopqrstuvwxyz";
	$largo_abc=strlen($abc);
	$salida="";
	for($i=0; $i<$largo; $i++){
		$salida.=$abc[rand(0, ($largo_abc-1))];
	}
	return $salida;
}

function respaldar_log($prefijo, $id_semilla, $ruta_logs){
	$ruta_log=$ruta_logs."/".$prefijo."-".$id_semilla."_";
	$ruta_log_respaldo=$ruta_logs."/".$prefijo."-".$id_semilla."_old";
	if(file_exists($ruta_log)){
		if(file_exists($ruta_log_respaldo)){
			agregar_lineas($ruta_log, $ruta_log_respaldo);
		}
		else{
			copy($ruta_log, $ruta_log_respaldo);
		}
	}
}
	
function ordenar_archivo($ruta_entrada, $ruta_salida, $cortar){
	//echo "Leyendo \"$ruta_entrada\"<br>";
	$arreglo=array();
	$contador=0;
	if($entrada=fopen($ruta_entrada, "r")){
		while($linea=trim(fgets($entrada))){
			if($cortar){
				$palabras=split(" ", $linea);
				$linea=$palabras[0];
			}
			$arreglo[$contador++]=$linea;
		}
		fclose($entrada);
	}
	//echo "Ordenando<br>";
	sort($arreglo);
	//echo "Escribiendo<br>";
	if($salida=fopen($ruta_salida, "w")){
		for($i=0; $i<$contador; $i++){
			fwrite($salida, $arreglo[$i]."\n");
		}
		fclose($salida);	
	}
	unset($arreglo);
}
	
function agregar_lineas($ruta_new, $ruta_old){
	
	print_debug("agregar_lineas - $ruta_new => $ruta_old");
	//Ordenar el archivo antiguo
	$ruta_old_ordenado=$ruta_old."_ordenado";
	//ordenar_archivo($ruta_old, $ruta_old_ordenado, false);
	ordenar_archivo_partes($ruta_old, $ruta_old_ordenado, false, 1000);

	//Ordenar el archivo nuevo
	$ruta_new_ordenado=$ruta_new."_ordenado";
	ordenar_archivo($ruta_new, $ruta_new_ordenado, false);

	//comparar_cadenas nuevo_ordenado antiguo_ordenado
	exec("/home/buscador/proyecto/comparar_cadenas $ruta_new_ordenado $ruta_old_ordenado 100000", $lineas_nuevas);

	//echo "Lineas distintas: <br>";
	//for($i=0; $i<count($lineas_nuevas); $i++)
	//	echo $lineas_nuevas[$i]."<br>";

	//Abrir el nuevo_ordenado para leer y el antiguo para continuar escritura
	$lector=fopen($ruta_new_ordenado, "r");
	$escritor=fopen($ruta_old, "a");
	
	//leer las lineas adicionales de nuevo_ordenado y agregarlas a antiguo
	$contador=0;
	$revisadas=0;
	while($linea=trim(fgets($lector))){
		if(++$contador==$lineas_nuevas[$revisadas]){
			print_debug($linea);
			fwrite($escritor, $linea."\n");
			$revisadas++;
		}
	}

	fclose($lector);
	fclose($escritor);

	//borrar los ordenados
	unlink($ruta_old_ordenado);
	unlink($ruta_new_ordenado);
	
	print_debug("agregar_lineas - Fin");
	
}

function obtener_directorio($ruta){
	$posicion=strrpos($ruta, "/");
	if($posicion===false)
		return ".";
	else
		return substr($ruta, 0, $posicion);
}
	
function ordenar_archivo_partes($ruta_entrada, $ruta_salida, $cortar, $maximo_lineas){

	$ruta_temp=obtener_directorio($ruta_salida);
	$palabra_temp=string_random(10, false);
	
	print_debug("Leyendo \"$ruta_entrada\"");
	$arreglo=array();
	$contador=0;
	$archivos_creados=0;
	if($entrada=fopen($ruta_entrada, "r")){
		while($linea=trim(fgets($entrada))){
			if($cortar){
				$palabras=split(" ", $linea);
				$linea=$palabras[0];
			}
			$arreglo[$contador++]=$linea;
			//Cuando se alcanza el limite, hay que ordenar y guardar
			if($contador>=$maximo_lineas){
				print_debug("Limite de lineas");
				sort($arreglo, SORT_STRING);
				$nombre_temp=$ruta_temp."/".$palabra_temp."-".($archivos_creados++);
				if($salida=fopen($nombre_temp, "w")){
					print_debug("Guardando en \"$nombre_temp\"");
					for($i=0; $i<$contador; $i++){
						fwrite($salida, $arreglo[$i]."\n");
					}
					fclose($salida);	
				}
				unset($arreglo);
				$contador=0;
			}
			
		}
		fclose($entrada);
	}
	//Guardo el residuo en un ultimo archivo temporal
	if($contador){
		sort($arreglo, SORT_STRING);
		$nombre_temp=$ruta_temp."/".$palabra_temp."-".($archivos_creados++);
		if($salida=fopen($nombre_temp, "w")){
			for($i=0; $i<$contador; $i++){
				fwrite($salida, $arreglo[$i]."\n");
			}
			fclose($salida);	
		}
		unset($arreglo);
	}
	
	print_debug("Preparando Lectores");
	//Leer de todos los archivos temporales e ir guardando ordenado
	$lectores=array();
	$lineas=array();
	$lectores_activos=0;
	for($i=0; $i<$archivos_creados; $i++){
		$nombre_temp=$ruta_temp."/".$palabra_temp."-".$i;
		if($lectores[$i]=fopen($nombre_temp, "r"))
			$lectores_activos++;
		$lineas[$i]=false;
	}
	
	$salida=fopen($ruta_salida, "w");
	while($lectores_activos){
		print_debug("Quedan $lectores_activos lectores");
		//aseguro la primera linea de cada lector
		for($i=0; $i<$archivos_creados; $i++){
			if($lectores[$i] && !$lineas[$i]){
				$lineas[$i]=trim(fgets($lectores[$i]));
				if(!$lineas[$i]){
					$lectores_activos--;
					fclose($lectores[$i]);
					$lectores[$i]=false;
				}
			}
		}
		$linea_menor=false;
		for($i=0; $i<$archivos_creados; $i++){
			if($lectores[$i]){
				print_debug("candidato $i: \"".$lineas[$i]."\"");
				if(!$linea_menor || strcmp($lineas[$i], $linea_menor)<0){
					$linea_menor=$lineas[$i];
					$eliminar=$i;
				}
			}
		}
		$lineas[$eliminar]=false;
		print_debug("linea menor: \"$linea_menor\"");
		if($linea_menor){
			fwrite($salida, $linea_menor."\n");
			$linea_menor=false;
		}
		
	}
	fclose($salida);
	//Borrar archivos temporales
	for($i=0; $i<$archivos_creados; $i++){
		$nombre_temp=$ruta_temp."/".$palabra_temp."-".$i;
		unlink($nombre_temp);
	}
}

function cmp_dominios_nombre($d1, $d2){
	return strcmp($d1["nombre"], $d2["nombre"] );
}

function cmp_dominios_id($d1, $d2){
	if($d1["id"]==$d2["id"])
		return 0;
	return ($d1["id"]<$d2["id"])?-1:1;
}

function cmp_dominios_puerto($d1, $d2){
	if($d1["puerto"]==$d2["puerto"])
		return 0;
	return ($d1["puerto"]<$d2["puerto"])?-1:1;
}

function es_imagen($url){
	print_debug("es_imagen - inicio");
	$extensiones[]="jpg";
	$extensiones[]="jpeg";
	$extensiones[]="gif";
	$extensiones[]="png";
	$extensiones[]="bmp";
	for($i=0; $i<count($extensiones); $i++){
		if(strpos($url, ".".$extensiones[$i])){
			print_debug("es_imagen - fin");
			return true;
		}
	}
	
	if($archivo=@fopen($url, "r")){
		$datos=stream_get_meta_data($archivo);
		$wrapper_data=$datos["wrapper_data"];
		for($i=0; $i<count($wrapper_data); $i++){
			print_debug((1+$i)." \"".$wrapper_data[$i]."\"");
			if(strpos(strtolower($wrapper_data[$i]), "content-type")===0){
				print_debug("Content-Type");
				for($j=0; $j<count($extensiones); $j++){
					if(strpos($wrapper_data[$i], $extensiones[$j])){
						print_debug("es_imagen - fin");
						return true;
					}
				}
			}
			
		}
		fclose($archivo);
	}
	print_debug("es_imagen - fin");
	return false;
}

function convertir_lineas($linea, $largo){
	$salida="";
	while(true){
		$largo_linea=strlen($linea);
		if($largo_linea>$largo){
			$salida.=substr($linea, 0, $largo)."<br>\n";
			$linea=substr($linea, $largo);
		}
		else if($largo_linea>0){
			$salida.=$linea."<br>\n";
			break;
		}
		else{
			break;
		}
	}
	return $salida;
}

function escribir_lineas($linea, $largo){
	while(true){
		$largo_linea=strlen($linea);
		if($largo_linea>$largo){
			echo substr($linea, 0, $largo)."<br>\n";
			$linea=substr($linea, $largo);
		}
		else if($largo_linea>0){
			echo $linea."<br>\n";
			break;
		}
		else{
			break;
		}
	}
}

define("UTF_8", 1);
define("ASCII", 2);
define("ISO_8859_1", 3);
function codificacion($texto){
	$c=0;
	$ascii=true;
	for($i=0; $i<strlen($texto); $i++){
		$byte=ord($texto[$i]);
		if($c>0){
			if(($byte>>6) != 0x2){
				return ISO_8859_1;
			}
			else{
				$c--;
			}
		}
		else if($byte&0x80){
			$ascii=false;
			if(($byte>>5) == 0x6){
				$c=1;
			}
			else if(($byte>>4) == 0xE){
				$c=2;
			}
			else if(($byte>>3) == 0x14){
				$c=3;
			}
			else{
				return ISO_8859_1;
			}
		}
	}
	return ($ascii) ? ASCII : UTF_8;
}

function utf8_decode_seguro($texto){
	return (codificacion($texto)==ISO_8859_1) ? $texto : utf8_decode($texto);
}

function preparar_reject_list($reject){
	//echo "\"$reject\"<br>";
	//echo "Separando por comas<br>";
	$arreglo=split(",", $reject);
	$reject_arreglo=array();
	for($i=0; $i<count($arreglo); $i++){
		$corte=$arreglo[$i];
		$corte=trim($corte);
		//echo "----Corte $i: \"$corte\"<br>";
		//echo "----Separando por espacios<br>";
		$arreglo2=split(" ", $corte);
		for($j=0; $j<count($arreglo2); $j++){
			$corte=$arreglo2[$j];
			$corte=trim($corte);
			//echo "--------Corte $i-$j: \"$corte\"<br>";
			if(strlen($corte)>0 && !in_array($corte, $reject_arreglo))
				$reject_arreglo[]=$corte;
		}
	}
	$reject_final=$reject_arreglo[0];
	for($i=1; $i<count($reject_arreglo); $i++)
		$reject_final.=" , ".$reject_arreglo[$i];
	//echo "Reject Final: \"$reject_final\"<br>";
	return $reject_final;
}

function calcular_numero_resultados($ruta_bin, $ruta_listas, $consulta, $id_grupo){
	$resultado=array();
	$comando="$ruta_bin/numero_resultados $consulta $id_grupo $ruta_listas";
	//echo "Ejecutando \"$comando\"<br>";
	exec($comando, $resultado);
	//for($i=0; $i<count($resultado); $i++)
	//	echo $resultado[$i]."<br>";
	
	return $resultado[0];
}

function numero_resultados($db, $consulta, $id_grupo, $ruta_bin, $ruta_listas){
	//Esto es solo para mantener los ingresos como "palabra_1+palabra_2...+palabra_n"
	$consulta=trim($consulta, " +");
	//Pregunto a la BD
	$numero=obtener_numero_resultados($db, $consulta, $id_grupo);
	if($numero<0){
		//Se marca con -1 los que no se han ingresado
		//Ejecuto el programa
		$numero=calcular_numero_resultados($ruta_bin, $ruta_listas, $consulta, $id_grupo);
		//Ingreso a la BD
		ingresar_numero_resultados($db, $consulta, $id_grupo, $numero);
		return $numero;
	}
	return $numero;
}



function estadisticas_grupo($db, $id_grupo, $prefijo, $ruta_colecta, $extensiones_colecta){
	
	$salida=array();
	
	$tiempo_actual=mktime();
	
	//echo "Indice<br>";
	$logs=obtener_log_creacion_indice($db, "G", $id_grupo);
	$log=$logs[0];
	$tiempo=segundos_fecha($log["fecha"]);
	//echo "tiempo: $tiempo<br>";
	$salida["tiempo_indice"]=$tiempo;
	$diferencia=$tiempo_actual-$tiempo;
	$dias=number_format(($diferencia/(24*3600)), 0);
	//echo "dias: $dias<br>";
	$salida["dias_indice"]=$dias;
	
	//echo "Colecta<br>";
	$logs=obtener_log_inicio_colecta($db, "G", $id_grupo);
	$log=$logs[0];
	$tiempo=segundos_fecha($log["fecha"]);
	//echo "tiempo: $tiempo<br>";
	$salida["tiempo_colecta"]=$tiempo;
	$diferencia=$tiempo_actual-$tiempo;
	$dias=number_format(($diferencia/(24*3600)), 0);
	//echo "dias: $dias<br>";
	$salida["dias_colecta"]=$dias;
	
	//echo "Largo Colecta<br>";
	//Limipiar Extensiones
	$extensiones_limpias=array();
	$extensiones_rechazadas=array(".log");
	for($i=0; $i<count($extensiones_colecta); $i++){
		$aceptada=true;
		for($j=0; $j<count($extensiones_rechazadas); $j++){
			if(strcmp($extensiones_colecta[$i], $extensiones_rechazadas[$j])===0){
				$aceptada=false;
			}
		}
		if($aceptada){
			$extensiones_limpias[]=$extensiones_colecta[$i];
		}
	}
	
	$semillas=obtener_semillas_grupo($db, $id_grupo);
	$numero_extensiones=count($extensiones_limpias);
	$tamaño_colecta=0;
	for($i=0; $i<count($semillas); $i++){
		$colecta=tamaño_colecta($prefijo, $semillas[$i]["id"], $ruta_colecta, $extensiones_limpias);
		for($j=0; $j<$numero_extensiones; $j++){
			if($colecta[$j]>0){
				$tamaño_colecta+=$colecta[$j];
			}
		}
		//echo " Subtotal $i: ".texto_tamaño($tamaño_colecta)."<br>";
		
	}
	//echo "Total: ".texto_tamaño($tamaño_colecta)."<br>";
	$salida["tamaño_colecta"]=$tamaño_colecta;
	
	return $salida;
	
}

?>
