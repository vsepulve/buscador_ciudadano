<?php
echo "Cambiando Acentos<br>\n";

$id_grupo=62;
$numero_archivos=2;

if($numero_archivos==1){

	$origen="/home/buscador/listas_originales/lista_texto_".$id_grupo.".txt";
	$destino="/home/buscador/listas/lista_texto_".$id_grupo.".txt";

	echo "lista_texto_".$id_grupo.".txt<br>\n";

	$lector=fopen($origen, "r");
	$escritor=fopen($destino, "w");
	//$contador=0;
	
	$acentos=array("á", "é", "í", "ó", "ú", "ñ", "Á", "É", "Í", "Ó", "Ú", "Ñ");
	$letras=array("a", "e", "i", "o", "u", "n", "a", "e", "i", "o", "u", "n");
	if($lector && $escritor){
		while(($linea=fgets($lector))!=null){
			$posicion=strpos($linea, " ");
			if($posicion){
				$palabra=substr($linea, 0, $posicion);
				$resto=substr($linea, $posicion);
				$nueva_linea=str_replace($acentos, $letras, $palabra).$resto;
				fwrite($escritor, $nueva_linea);
				//echo ">\"$nueva_linea\"<br>\n";
			}
			//$contador++;
			//if($contador==10)
			//	break;
		}
		
		fclose($lector);
		fclose($escritor);
	}
}
else{
	for($i=1; $i<=$numero_archivos; $i++){
	
		$origen="/home/buscador/listas_originales/lista_texto_".$id_grupo."_".$i.".txt";
		$destino="/home/buscador/listas/lista_texto_".$id_grupo."_".$i.".txt";
	
		echo "lista_texto_".$id_grupo."_".$i.".txt<br>\n";
	
		$lector=fopen($origen, "r");
		$escritor=fopen($destino, "w");
		//$contador=0;

		$acentos=array("á", "é", "í", "ó", "ú", "ñ");
		$letras=array("a", "e", "i", "o", "u", "n");

		if($lector && $escritor){
	
			while(($linea=fgets($lector))!=null){
				$posicion=strpos($linea, " ");
				if($posicion){
					$palabra=substr($linea, 0, $posicion);
					$resto=substr($linea, $posicion);
					$nueva_linea=str_replace($acentos, $letras, $palabra).$resto;
					fwrite($escritor, $nueva_linea);
					//echo ">\"$nueva_linea\"<br>\n";
				}
		
				//$contador++;
				//if($contador==10)
				//	break;
			}
	
			fclose($lector);
			fclose($escritor);
		}

	}//for... cada archivo
}//if... son varios archivos
?>
