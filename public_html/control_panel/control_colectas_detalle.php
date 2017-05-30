<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Detalles de Colectas");
echo "<body>";
html_menu_superior(2);
html_inicio_mensajes();

$debug=false;
$estructura=obtener_estructura($db);
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["id"].": ".$grupos[$i]["nombre"]);
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		print_debug("--> Dominio ".$dominios[$j]["id"].": ".$dominios[$j]["nombre"]);
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			print_debug("---- Semilla ".$semillas[$k]["id"].": ".$semillas[$k]["url"]);
		}
	}
}//for... cada grupo

$accion=addslashes(trim($_GET["accion"]));

$id_grupo=$_GET["id_grupo"];
if(!ctype_digit($id_grupo))
	$id_grupo=0;

$id_dominio=$_GET["id_dominio"];
if(!ctype_digit($id_dominio))
	$id_dominio=0;

$id_semilla=$_GET["id_semilla"];
if(!ctype_digit($id_semilla))
	$id_semilla=0;

$debug=true;
//Acciones Aqui


$debug=false;


//---------- Verificacion de Colectas activas ----------
$colecta_activa_grupo=array();
$colecta_activa_dominio=array();
$colecta_activa_semilla=array();
$debug=false;
verificar_colectas_activas($estructura, $prefijo, $ruta_bin, $minicow);
$debug=false;

//---------- Verificacion de estado de colectas ----------
$debug=false;
$colecta_correcta_grupo=array();
$colecta_correcta_dominio=array();
$colecta_correcta_semilla=array();
verificar_estado_colectas($estructura, $prefijo, $ruta_colecta, $extensiones_colecta);
$debug=false;

//---------- Verificacion de colectas de los hijos----------
$colecta_hijos_grupo=array();
$colecta_hijos_dominio=array();
$debug=false;
verificar_colectas_hijos($estructura, $colecta_correcta_semilla);
$debug=false;

//---------- Verificacion de numero de semillas----------
$numero_semillas_grupo=array();
$numero_semillas_dominio=array();
$debug=false;
verificar_numero_semillas($estructura);
$debug=false;

//---------- Verificacion de dias de colecta ----------
$dias_colecta_grupo=array();
$dias_colecta_dominio=array();
$dias_colecta_semilla=array();
$debug=false;
verificar_dias_colecta($db, $estructura);
$debug=false;

//---------- Verificacion de tamaños de Coelctas ----------
$tamaño_colecta_grupo=array();
$tamaño_colecta_dominio=array();
$tamaño_colecta_semilla=array();
$debug=false;
verificar_tamaño_colectas($estructura, $prefijo, $ruta_colecta, $extensiones_colecta);
$debug=false;

//unos 50 kilos...
$tamaño_minimo=50*1024;

mysql_close($db);
html_fin_mensajes();







$titulo="Detalles de Colectas";
$opciones="Analisis de Informacion";
$elementos=array();

$elemento["nombre"]="Busquedas";
$elemento["ruta"]="control_busquedas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Logs";
$elemento["ruta"]="control_logs.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Colectas";
$elemento["ruta"]="control_colectas.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:60;\" colspan=2>&nbsp;</th>
	<th style=\"width:250;\">Semilla</th>
	<th style=\"width:170;\">Estado</th>
	<th style=\"width:150;\">Actualizado</th>
	<th style=\"width:150;\">Tama&ntilde;o</th>
	<th style=\"width:20;\" colspan=2>&nbsp;</th>
</tr>";
$color_usado=0;
$color=array("rgb(200, 200, 200)", "rgb(230, 230, 230)");
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$color_usado=($color_usado+1)%2;
	
	echo "<tr height=$separacion_grupos><td colspan=8></td></tr>";
	
	echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
	
	echo "<td colspan=2 class=borde_grupo_01>&nbsp;</td>";
	
	echo "<td class=borde_grupo_02>";
	if(strlen($grupos[$i]["nombre"])>25)
		$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
	else
		$texto=$grupos[$i]["nombre"];
	echo "".$texto."";
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	if($colecta_activa_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_amarillo>Colectando</span>";
	}
	else if($colecta_correcta_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_verde>Correcta</span>";
	}
	else if($colecta_hijos_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_amarillo>Incompleta</span>";
	}
	else{
		echo "<span class=texto_rojo>Vacia</span>";
	}
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	//echo "&nbsp;";
	if($dias_colecta_grupo[$grupos[$i]["id"]]<0){
		echo "Nunca";
	}
	else{
		echo "Hace ".$dias_colecta_grupo[$grupos[$i]["id"]]." dias";
	}
	
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	if($tamaño_colecta_grupo[$grupos[$i]["id"]]<0
		|| $dias_colecta_grupo[$grupos[$i]["id"]]<0){
		echo "No colectado";
	}
	else if($tamaño_colecta_grupo[$grupos[$i]["id"]]<$tamaño_minimo){
		echo "<span class=texto_rojo>".texto_tamaño($tamaño_colecta_grupo[$grupos[$i]["id"]])."</span>";
	}
	else{
		echo "".texto_tamaño($tamaño_colecta_grupo[$grupos[$i]["id"]]);
	}
	echo "</td>";
	
	echo "<td colspan=2 class=borde_grupo_03>&nbsp;</td>";
	
	echo "</tr>";
	
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		
		echo "<tr height=$separacion_dominios>";
		echo "<td>";
		echo "<img src=$estilos/extension_03.png height=$separacion_dominios width=$ancho_borde_izq></img>";
		echo "</td>";
		echo "<td colspan=7></td>";
		echo "</tr>";
		
		echo "<tr style=\"text-align:center; background-color:".$color_dominio[$color_usado].";\">";
		
		echo "<td align=left>";
		if($j==count($dominios)-1){
			echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		else{
			echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_01>&nbsp;</td>";
		
		echo "<td class=borde_dominio_02>";
		if(strlen($dominios[$j]["nombre"])>25)
			$texto=substr($dominios[$j]["nombre"], 0, 25)."...";
		else
			$texto=$dominios[$j]["nombre"];
		echo "".$texto."";
		echo "</td>";
		
		echo "<td class=borde_dominio_02>";
		if($colecta_activa_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_amarillo>Colectando</span>";
		}
		else if($colecta_correcta_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_verde>Correcta</span>";
		}
		else if($colecta_hijos_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_amarillo>Incompleta</span>";
		}
		else{
			echo "<span class=texto_rojo>Vacia</span>";
		}
		echo "</td>";
		
		
		echo "<td class=borde_dominio_02>";
		if($dias_colecta_dominio[$dominios[$j]["id"]]<0){
			echo "Nunca";
		}
		else{
			echo "Hace ".$dias_colecta_dominio[$dominios[$j]["id"]]." dias";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_02>";
		if($tamaño_colecta_dominio[$dominios[$j]["id"]]<0
			|| $dias_colecta_dominio[$dominios[$j]["id"]]<0){
			echo "No colectado";
		}
		else if($tamaño_colecta_dominio[$dominios[$j]["id"]]<$tamaño_minimo){
			echo "<span class=texto_rojo>".texto_tamaño($tamaño_colecta_dominio[$dominios[$j]["id"]])."</span>";
		}
		else{
			echo "".texto_tamaño($tamaño_colecta_dominio[$dominios[$j]["id"]]);
		}
			
		echo "</td>";
		
		echo "<td class=borde_dominio_03>&nbsp;</td>";
		echo "<td width=$ancho_borde_der bgcolor=white></td>";
		echo "</tr>";
		
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			
			echo "<tr style=\"text-align:center; background-color:".$color_semilla[$color_usado].";\">";
			
			echo "<td align=left>";
			if($j==count($dominios)-1){
				echo "<img src=$estilos/extension_04.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_03.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			echo "</td>";
			
			echo "<td align=left>";
			if($k==count($semillas)-1){
				echo "<img src=$estilos/extension_02.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			else{
				echo "<img src=$estilos/extension_01.png height=$alto_fila width=$ancho_borde_izq></img>";
			}
			echo "</td>";
			
			echo "<td>";
			//El substring es para direcciones muy largas que pueden verse raros
			if(strlen($semillas[$k]["url"])>25){
				$texto=substr($semillas[$k]["url"], 0, 25)."...";
			}
			else{
				$texto=$semillas[$k]["url"];
			}
			echo "".$texto."";
			echo "</td>";
			
			echo "<td>";
			if($colecta_activa_semilla[$semillas[$k]["id"]]){
				echo "<span class=texto_amarillo>Colectando</span>";
			}
			else if($colecta_correcta_semilla[$semillas[$k]["id"]]){
				echo "<span class=texto_verde>Correcta</span>";
			}
			else{
				echo "<span class=texto_rojo>Vacia</span>";
			}
			echo "</td>";
			
			echo "<td>";
			if($dias_colecta_semilla[$semillas[$k]["id"]]<0){
				echo "Nunca";
			}
			else{
				echo "Hace ".$dias_colecta_semilla[$semillas[$k]["id"]]." dias";
			}
			echo "</td>";
			
			echo "<td>";
			if($tamaño_colecta_semilla[$semillas[$k]["id"]]<0){
				echo "No colectada";
			}
			else if($tamaño_colecta_semilla[$semillas[$k]["id"]]<$tamaño_minimo){
				echo "<span class=texto_rojo>".texto_tamaño($tamaño_colecta_semilla[$semillas[$k]["id"]])."</span>";
			}
			else{
				echo "".texto_tamaño($tamaño_colecta_semilla[$semillas[$k]["id"]]);
			}
			
			echo "</td>";
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			
			echo "</tr>";
		
		
		}
		
	}
}

?>

</table>

</body>
</html>
