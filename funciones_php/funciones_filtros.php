<?php

/*
Estas funciones son solo filtros para extraer informacion de la $estructura
Todas comienzan con el parametro $estructura
Todas retornan false si no encuentran resultados.
*/

function filtrar_grupo($estructura, $id_grupo){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo)
			return $grupos[$i];
	}
	return false;
}

function filtrar_grupo_auditable($estructura){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["auditable"]==1)
			return $grupos[$i];
	}
	return false;
}

function filtrar_dominio($estructura, $id_dominio){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			if($dominios[$j]["id"]==$id_dominio)
				return $dominios[$j];
		}
	}
	return false;
}

function filtrar_dominios_grupo($estructura, $id_grupo){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo){
			return $estructura["dominios"][$i];
		}
	}
	return false;
}

function filtrar_dominio_nombre_grupo($estructura, $nombre, $id_grupo){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo){
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				if(strcmp($dominios[$j]["nombre"], $nombre)==0){
					return $dominios[$j];
				}
			}
		}
	}
	return false;
}

function filtrar_semillas_dominio($estructura, $id_dominio){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++)
			if($dominios[$j]["id"]==$id_dominio){
				return $estructura["semillas"][$i][$j];
			}
	}
	return false;
}

function filtrar_semilla($estructura, $id_semilla){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				if($semillas[$k]["id"]==$id_semilla)
					return $semillas[$k];
			}
		}
	}
	return false;
}

function filtrar_id_semillas_grupo($estructura, $id_grupo){
	$id_semillas=array();
	$numero_semillas=0;
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo){
			//este el grupo correcto
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				$semillas=$estructura["semillas"][$i][$j];
				for($k=0; $k<count($semillas); $k++)
					$id_semillas[$numero_semillas++]=$semillas[$k]["id"];
			}
	
		}
	}
	if($numero_semillas){
		return $id_semillas;
	}
	return false;
}

function filtrar_semillas_grupo($estructura, $id_grupo){
	$semillas_retorno=array();
	$numero_semillas=0;
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo){
			//este el grupo correcto
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				$semillas=$estructura["semillas"][$i][$j];
				for($k=0; $k<count($semillas); $k++)
					$semillas_retorno[$numero_semillas++]=$semillas[$k];
			}
		}
	}
	if($numero_semillas){
		return $semillas_retorno;
	}
	return false;
}

function filtrar_id_semillas_dominio($estructura, $id_dominio){
	$id_semillas=array();
	$numero_semillas=0;
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			if($dominios[$j]["id"]==$id_dominio){
				//este el dominio correcto
				$semillas=$estructura["semillas"][$i][$j];
				for($k=0; $k<count($semillas); $k++)
					$id_semillas[$numero_semillas++]=$semillas[$k]["id"];
			}
		}
	}
	if($numero_semillas){
		return $id_semillas;
	}
	return false;
}

function filtrar_id_dominios_grupo($estructura, $id_grupo){
	$id_dominios=array();
	$numero_dominios=0;
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		if($grupos[$i]["id"]==$id_grupo){
			//este el grupo correcto
			$dominios=$estructura["dominios"][$i];
			for($j=0; $j<count($dominios); $j++){
				$id_dominios[$numero_dominios++]=$dominios[$j]["id"];
			}
		}
	}
	if($numero_dominios){
		return $id_dominios;
	}
	return false;
}

function filtrar_grupo_dominio($estructura, $id_dominio){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			if($dominios[$j]["id"]==$id_dominio){
				return $grupos[$i];
			}
		}
	}
	return false;
}

function filtrar_grupo_semilla($estructura, $id_semilla){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				if($semillas[$k]["id"]==$id_semilla){
					return $grupos[$i];
				}
			}
		}
	}
	return false;
}

function filtrar_dominio_semilla($estructura, $id_semilla){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				if($semillas[$k]["id"]==$id_semilla){
					return $dominios[$j];
				}
			}
		}
	}
	return false;
}


?>
