<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Detalles de Indices");
echo "<body>";
html_menu_superior(1);
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

//---------- Verificacion de estado de indices ----------
$debug=false;
$indice_correcto_grupo=array();
$indice_correcto_dominio=array();
verificar_estado_indices($estructura, $prefijo, $ruta_indice, $extensiones_indice);
$debug=false;

//---------- Verificacion de dias de Indices ----------
$dias_indice_grupo=array();
$dias_indice_dominio=array();
$semilla_incluida_grupo=array();
$semilla_incluida_dominio=array();
$debug=false;
verificar_dias_indice($db, $estructura);
$debug=false;

//---------- Verificacion de indices completos ----------
$indice_completo_grupo=array();
$indice_completo_dominio=array();
$debug=false;
verificar_indices_completos($db, $estructura);
$debug=false;

mysql_close($db);
html_fin_mensajes();


$titulo="Detalles de Indices";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Indices";
$elemento["ruta"]="control_indices.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

$elemento["nombre"]="Refinamiento";
$elemento["ruta"]="control_indices_refinamiento.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:60;\" colspan=2>&nbsp;</th>
	<th style=\"width:250;\">Semilla</th>
	<th style=\"width:170;\">Estado</th>
	<th style=\"width:150;\">Construido</th>
	<th style=\"width:150;\">Incluida en</th>
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
	if($indice_completo_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_verde>Correcto</span>";
	}
	else if($indice_correcto_grupo[$grupos[$i]["id"]]){
		echo "<span class=texto_amarillo>Incompleto</span>";
	}
	else{
		echo "<span class=texto_rojo>Incorrecto</span>";
	}
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	//echo "&nbsp;";
	if($dias_indice_grupo[$grupos[$i]["id"]]<0){
		echo "Nunca";
	}
	else{
		echo "Hace ".$dias_indice_grupo[$grupos[$i]["id"]]." dias";
	}
	
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center>";
	echo "--";
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
		if($indice_completo_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_verde>Correcto</span>";
		}
		else if($indice_correcto_dominio[$dominios[$j]["id"]]){
			echo "<span class=texto_amarillo>Incompleto</span>";
		}
		else{
			echo "<span class=texto_rojo>incorrecto</span>";
		}
		echo "</td>";
		
		
		echo "<td class=borde_dominio_02>";
		if($dias_indice_dominio[$dominios[$j]["id"]]<0){
			echo "Nunca";
		}
		else{
			echo "Hace ".$dias_indice_dominio[$dominios[$j]["id"]]." dias";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_02 align=center>";
		echo "--";
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
			echo "--";
			echo "</td>";
			
			echo "<td>";
			echo "--";
			echo "</td>";
			
			echo "<td align=center>";
			if($semilla_incluida_grupo[$semillas[$k]["id"]]
				&& $semilla_incluida_dominio[$semillas[$k]["id"]]){
				echo "Grupo y Dominio";
			}
			else if($semilla_incluida_grupo[$semillas[$k]["id"]]){
				echo "Grupo";
			}
			else if($semilla_incluida_dominio[$semillas[$k]["id"]]){
				echo "Dominio";
			}
			else{
				echo "Ninguno";
			}
			echo "</td>";
			
			
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			echo "<td width=$ancho_borde_der bgcolor=white></td>";
			
			echo "</tr>";
		
		
		}
		
	}
}


echo "</table>";


echo "</body>";

echo "</html>";

?>
