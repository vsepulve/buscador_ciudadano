<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Indexador");
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

$post_accion=trim($_POST["post_accion"]);

$post_id_grupo=$_POST["post_id_grupo"];
if(!ctype_digit($post_id_grupo))
	$post_id_grupo=0;

$post_id_dominio=$_POST["post_id_dominio"];
if(!ctype_digit($post_id_dominio))
	$post_id_dominio=0;
	
if($post_accion!=null){
	$accion=$post_accion;
	$id_grupo=$post_id_grupo;
	$id_dominio=$post_id_dominio;
}

//---------- Verificacion de colectas activos ----------
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


if($accion=="editar_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Invalidos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		$refinamiento=$_POST["refinamiento_grupo_".$id_grupo];
		$entradas=array(".", ",");
		$salidas=array(" ", " ");
		$refinamiento=str_replace($entradas, $salidas, $refinamiento);
		$arreglo=explode(" ", $refinamiento);
		$refinamiento="";
		for($k=0; $k<count($arreglo); $k++){
			if(strlen($arreglo[$k])>0){
				$refinamiento.=" ".$arreglo[$k];
			}
		}
		$refinamiento=trim($refinamiento);
		$puerto_indice=obtener_puerto_indice($db, "G", $id_grupo);
		if($refinamiento!=$puerto_indice["refinamiento"]){
			editar_puerto_indice($db, "G", $id_grupo, $puerto_indice["puerto"], $puerto_indice["id_maquina"], $refinamiento);
		}
		
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		for($i=0; $i<count($dominios); $i++){
			$refinamiento=$_POST["refinamiento_dominio_".$dominios[$i]["id"]];
			$entradas=array(".", ",");
			$salidas=array(" ", " ");
			$refinamiento=str_replace($entradas, $salidas, $refinamiento);
			$arreglo=explode(" ", $refinamiento);
			$refinamiento="";
			for($k=0; $k<count($arreglo); $k++){
				if(strlen($arreglo[$k])>0){
					$refinamiento.=" ".$arreglo[$k];
				}
			}
			$refinamiento=trim($refinamiento);
			$puerto_indice=obtener_puerto_indice($db, "D", $dominios[$i]["id"]);
			if($refinamiento!=$puerto_indice["refinamiento"]){
				editar_puerto_indice($db, "D", $dominios[$i]["id"], $puerto_indice["puerto"], $puerto_indice["id_maquina"], $refinamiento);
			}
		}
		
	}
	
}
else if($accion=="editar_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Invalidos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		$refinamiento=$_POST["refinamiento_dominio_".$id_dominio];
		$entradas=array(".", ",");
		$salidas=array(" ", " ");
		$refinamiento=str_replace($entradas, $salidas, $refinamiento);
		$arreglo=explode(" ", $refinamiento);
		$refinamiento="";
		for($k=1; $k<count($arreglo); $k++){
			if(strlen($arreglo[$k])>0){
				$refinamiento.=" ".$arreglo[$k];
			}
		}
		$refinamiento=trim($refinamiento);
		$puerto_indice=obtener_puerto_indice($db, "D", $id_dominio);
		if($refinamiento!=$puerto_indice["refinamiento"]){
			editar_puerto_indice($db, "D", $id_dominio, $puerto_indice["puerto"], $puerto_indice["id_maquina"], $refinamiento);
		}
	}
}
else if($accion=="cancelar"){
	$grupo=0;
	$dominio=0;
	$semilla=0;
}

//---------- Verificacion de estado de indices ----------
$debug=false;
$indice_correcto_grupo=array();
$indice_correcto_dominio=array();
verificar_estado_indices($estructura, $prefijo, $ruta_indice, $extensiones_indice);
$debug=false;

//---------- Verificacion de colectas de los hijos----------
$colecta_hijos_grupo=array();
$colecta_hijos_dominio=array();
$debug=false;
verificar_colectas_hijos($estructura, $colecta_correcta_semilla);
$debug=false;

//---------- Verificacion de indices completos ----------
$indice_completo_grupo=array();
$indice_completo_dominio=array();
$debug=false;
verificar_indices_completos($db, $estructura);
$debug=false;

//---------- Refinamiento de Indices ----------
$debug=false;
$refinamiento_grupo=array();
$refinamiento_dominio=array();
//$estructura
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$dominios=$estructura["dominios"][$i];
	$puerto_indice=obtener_puerto_indice($db, "G", $grupos[$i]["id"]);
	print_debug("grupo: ".$grupos[$i]["id"]." (puerto: ".$puerto_indice["puerto"].", refinamiento: ".$puerto_indice["refinamiento"].")");
	$refinamiento_grupo[$grupos[$i]["id"]]=$puerto_indice["refinamiento"];
	for($j=0; $j<count($dominios); $j++){
		$puerto_indice=obtener_puerto_indice($db, "D", $dominios[$j]["id"]);
		print_debug("dominio: ".$dominios[$j]["id"]." (puerto: ".$puerto_indice["puerto"].", refinamiento: ".$puerto_indice["refinamiento"].")");
		$refinamiento_dominio[$dominios[$j]["id"]]=$puerto_indice["refinamiento"];
	}
}
$debug=false;

mysql_close($db);
html_fin_mensajes();


$titulo="Refinamiento de Indices";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Detalles";
$elemento["ruta"]="control_indices_detalle.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento["nombre"]="Indices";
$elemento["ruta"]="control_indices.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<form id=form_01 action=? method=post>";

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:30;\">&nbsp;</th>
	<th style=\"width:250;\">Indices</th>
	<th style=\"width:130;\">Estado</th>
	<th style=\"width:220;\">Refinamiento</th>
	<th style=\"width:160;\">Acciones</th>
	<th style=\"width:10;\">&nbsp;</th>
</tr>";
	
$color_usado=0;
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	$color_usado=($color_usado+1)%2;
	
	echo "<tr height=$separacion_grupos><td colspan=7></td></tr>";
	
	echo "<tr height=$alto_fila style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
	
	echo "<td class=borde_grupo_01>&nbsp;</td>";
	
	echo "<td class=borde_grupo_02>";
	echo "<a name=grupo_".$grupos[$i]["id"]."></a>";
	if(strlen($grupos[$i]["nombre"])>25)
		$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
	else
		$texto=$grupos[$i]["nombre"];
	echo "".$texto."";
	echo "</td>";
	
	echo "<td class=borde_grupo_02>";
	if($indice_completo_grupo[$grupos[$i]["id"]]){
		//echo "Completo (".$semillas["numero_semillas"][$i][$j]."/".$semillas["numero_semillas"][$i][$j].")";
		echo "<span class=texto_verde>Correcto</span>";
	}
	else if($indice_correcto_grupo[$grupos[$i]["id"]]){
		//echo "Completo (".$semillas["numero_semillas"][$i][$j]."/".$semillas["numero_semillas"][$i][$j].")";
		echo "<span class=texto_amarillo>Incompleto</span>";
	}
	else{
		//echo "incorrecto (0/".$semillas["numero_semillas"][$i][$j].")";
		echo "<span class=texto_rojo>Incorrecto</span>";
	}
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center>";
	if($accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
		echo "<input type=text name=refinamiento_grupo_".$grupos[$i]["id"]." size=20 value=\"".$refinamiento_grupo[$grupos[$i]["id"]]."\" >";
	}
	else if($refinamiento_grupo[$grupos[$i]["id"]])
		echo "".$refinamiento_grupo[$grupos[$i]["id"]]."";
	else
		echo "--";
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center>";
	
	echo "<table width=150 cellpadding=0 cellspacing=0 border=0>";
	echo "<tr>";
	if($accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
	
		echo "<td align=center>";
		echo "<input type=hidden name=post_accion value=editar_grupo>";
		echo "<input type=hidden name=post_id_grupo value=".$grupos[$i]["id"].">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
		echo "</td>";
		
		echo "<td align=center>";
		echo "<a href=?#grupo_".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/cancelar.png height=18 width=58>";
		echo "</a>";
		echo "</td>";
	}
	else{
		echo "<td align=center>";
		echo "<a href=?accion=inicio_editar_grupo&id_grupo=".$grupos[$i]["id"]."#grupo_".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/editar_vacio.png height=$alto_editar_vacio width=$ancho_editar_vacio>";
		echo "</a>";
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";
	
	echo "</td>";
	
	echo "<td class=borde_grupo_03>&nbsp;</td>";
	
	echo "</tr>";
	
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		echo "<tr height=$separacion_dominios>";
		echo "<td><img src=$estilos/extension_03 height=$separacion_dominios width=$ancho_borde_izq></td>";
		echo "<td colspan=6></td>";
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
		
		echo "<td class=borde_dominio_01>";
		echo "<a name=dominio_".$dominios[$j]["id"]."></a>";
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
		
		echo "<td class=borde_dominio_02 align=center>";
		if($accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]
			|| $accion=="inicio_editar_dominio" && $id_dominio==$dominios[$j]["id"]){
			echo "<input type=text name=refinamiento_dominio_".$dominios[$j]["id"]." size=20 value=\"".$refinamiento_dominio[$dominios[$j]["id"]]."\" >";
		}
		else if($refinamiento_dominio[$dominios[$j]["id"]])
			echo "".$refinamiento_dominio[$dominios[$j]["id"]]."";
		else
			echo "--";
		echo "</td>";
		
		echo "<td class=borde_dominio_03 align=center>";
		
		echo "<table width=150 cellpadding=0 cellspacing=0 border=0>";
		echo "<tr>";
		if($accion=="inicio_editar_dominio" && $id_dominio==$dominios[$j]["id"]){
			
			echo "<td align=center>";
			echo "<input type=hidden name=post_accion value=editar_dominio>";
			echo "<input type=hidden name=post_id_dominio value=".$dominios[$j]["id"].">";
			echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=18 width=58>";
			echo "</td>";
			
			echo "<td align=center>";
			echo "<a href=?#dominio_".$dominios[$j]["id"].">";
			echo "<img border=0 src=$estilos/cancelar.png height=18 width=58>";
			echo "</a>";
			echo "</td>";
			
		}
		else if($accion=="inicio_editar_grupo" && $id_grupo==$grupos[$i]["id"]){
			echo "<td align=center>";
			echo "--";
			echo "</td>";
		}
		else{
			echo "<td align=center>";
			echo "<a href=?accion=inicio_editar_dominio&id_dominio=".$dominios[$j]["id"]."#dominio_".$dominios[$j]["id"].">";
			echo "<img border=0 src=$estilos/editar_vacio.png height=$alto_editar_vacio width=$ancho_editar_vacio>";
			echo "</a>";
			echo "</td>";
		}
		
		echo "</tr>";
		echo "</table>";
		
		echo "</td>";
		
		echo "<td width=10 bgcolor=white></td>";
		echo "</tr>";
	}
}

echo "</table>";

echo "</form>";

echo "<br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br>";
echo "<br><br><br><br><br><br><br><br><br><br>";

echo "</body>";
echo "</html>";

?>

