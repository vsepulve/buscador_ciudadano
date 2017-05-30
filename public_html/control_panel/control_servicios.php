<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Servicios");
echo "<body>";
html_menu_superior(0);
html_inicio_mensajes();

$usuario="www-data";
$owner=$usuario;

$estructura=obtener_estructura($db);

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

$control_servicios="control_servicios.php";

$accion=addslashes(trim($_GET["accion"]));

$id_grupo=$_GET["id_grupo"];
if(!ctype_digit($id_grupo))
	$id_grupo=0;

$id_dominio=$_GET["id_dominio"];
if(!ctype_digit($id_dominio))
	$id_dominio=0;

if($accion=="iniciar_todo"){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		crear_iniciar_servicio($prefijo, "G", $grupos[$i]["id"], $grupos[$i]["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			crear_iniciar_servicio($prefijo, "D", $dominios[$j]["id"], $dominios[$j]["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
		}
	}
}
else if($accion=="detener_todo"){
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		terminar_servicio($prefijo."-G-".$grupos[$i]["id"]."_", $ruta_bin);
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			terminar_servicio($prefijo."-D-".$dominios[$j]["id"]."_", $ruta_bin);
		}
	}

}
else if($accion=="iniciar_todo_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "Iniciando Grupo \"".$grupo["nombre"]."\" y sus Dominios<br>";
		crear_iniciar_servicio($prefijo, "G", $grupo["id"], $grupo["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		for($i=0; $i<count($dominios); $i++){
			crear_iniciar_servicio($prefijo, "D", $dominios[$i]["id"], $dominios[$i]["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
		}
	}

}
else if($accion=="detener_todo_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "Deteniendo Grupo \"".$grupo["nombre"]."\" y sus Dominios<br>";
		terminar_servicio($prefijo."-G-".$id_grupo."_", $ruta_bin);
		$dominios=filtrar_dominios_grupo($estructura, $id_grupo);
		for($i=0; $i<count($dominios); $i++){
			terminar_servicio($prefijo."-D-".$dominios[$i]["id"]."_", $ruta_bin);
		}
	}

}
else if($accion=="iniciar_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "Iniciando Grupo \"".$grupo["nombre"]."\"<br>";
		crear_iniciar_servicio($prefijo, "G", $grupo["id"], $grupo["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
	}

}
else if($accion=="detener_grupo"){
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "Deteniendo Grupo \"".$grupo["nombre"]."\"<br>";
		terminar_servicio($prefijo."-G-".$id_grupo."_", $ruta_bin);
	}

}
else if($accion=="iniciar_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		echo "Iniciando Dominio \"".$dominio["nombre"]."\"<br>";
		crear_iniciar_servicio($prefijo, "D", $dominio["id"], $dominio["puerto"], $ruta_bin, $ruta_libs, $ruta_indice);
	}

}
else if($accion=="detener_dominio"){
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else{
		$dominio=filtrar_dominio($estructura, $id_dominio);
		echo "Deteniendo Dominio \"".$dominio["nombre"]."\"<br>";
		terminar_servicio($prefijo."-D-".$id_dominio."_", $ruta_bin);
	}
}

//---------- Verificacion de estado de indices ----------
$debug=false;
$indice_correcto_grupo=array();
$indice_correcto_dominio=array();
verificar_estado_indices($estructura, $prefijo, $ruta_indice, $extensiones_indice);
$debug=false;

//---------- Verificacion de indices completos ----------
$indice_completo_grupo=array();
$indice_completo_dominio=array();
$debug=false;
verificar_indices_completos($db, $estructura);
$debug=false;

//---------- Verificacion de Activos ----------
$servicio_activo_grupo=array();
$servicio_activo_dominio=array();
$debug=false;
$usuario="www-data";
verificar_servicios_activos($estructura, $prefijo, $usuario, $ruta_bin);
$debug=false;


mysql_close($db);
html_fin_mensajes();


$titulo="Servicios";
$opciones="Acciones Globales";
$elementos=array();

$elemento=array();
$elemento["imagen"]="iniciar_vacio.png";
$elemento["alto"]=$alto_iniciar_vacio;
$elemento["ancho"]=$ancho_iniciar_vacio;
$elemento["ruta"]="".$control_servicios."?accion=iniciar_todo";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento=array();
$elemento["imagen"]="detener_vacio.png";
$elemento["alto"]=$alto_detener_vacio;
$elemento["ancho"]=$ancho_detener_vacio;
$elemento["ruta"]="".$control_servicios."?accion=detener_todo";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
		<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
			<th style=\"width:30;\">&nbsp;</th>
			<th style=\"width:250;\">Servicio</th>
			<th style=\"width:80;\">Puerto</th>
			<th style=\"width:120;\">Estado</th>
			<th style=\"width:120;\">Indice</th>
			<th style=\"width:190;\">Acciones</th>
			<th style=\"width:10;\">&nbsp;</th>
		</tr>";

	$color_usado=0;
	$grupos=$estructura["grupos"];
	for($i=0; $i<count($grupos); $i++){
		$color_usado=($color_usado+1)%2;
	
		echo "<tr height=5><td colspan=7></td></tr>";
		
		echo "<tr>";
		echo "<td colspan=5>&nbsp;</td>";
		echo "<td align=center>";
		echo "<span class=texto_gris>(Acciones de Grupo)</span>";
		echo "</td>";
		echo "<td>&nbsp</td>";
		echo "</tr>";
		
		echo "<tr height=$alto_fila>";
		echo "<td colspan=5>&nbsp;</td>";
		
		echo "<td align=center style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold; border-left: medium solid #000000; border-top: medium solid #000000; border-right: medium solid #000000; \">";
		
		echo "<table align=center><tr valign=center>";
		
		echo "<td>";
		//echo "<a href=".$control_servicios."?iniciar_todo_grupo=".$grupos[$i]["id"].">";
		echo "<a href=".$control_servicios."?accion=iniciar_todo_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Iniciar";
		echo "<img border=0 src=$estilos/iniciar_vacio.png height=$alto_iniciar_vacio width=$ancho_iniciar_vacio>";
		echo "</a>";
		echo "</td>";
		
		echo "<td width=10>&nbsp;</td>";
		
		echo "<td>";
		echo "<a href=".$control_servicios."?accion=detener_todo_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Iniciar";
		echo "<img border=0 src=$estilos/detener_vacio.png height=$alto_detener_vacio width=$ancho_detener_vacio>";
		echo "</a>";
		echo "</td>";
		
		echo "</tr></table>";
		
		echo "</td>";
		
		echo "<td></td>";
		echo "</tr>";
		
		echo "<tr style=\"text-align:center; background-color:".$color_grupo[$color_usado]."; font-weight:bold;\">";
		
		echo "<td class=borde_grupo_01>&nbsp;</td>";
		
		echo "<td class=borde_grupo_02>";
		if(strlen($grupos[$i]["nombre"])>25)
			$texto=substr($grupos[$i]["nombre"], 0, 25)."...";
		else
			$texto=$grupos[$i]["nombre"];
		echo "".$texto."";
		echo "</td>";
		
		echo "<td class=borde_grupo_02>";
		echo $grupos[$i]["puerto"];	
		echo "</td>";
		
		echo "<td class=borde_grupo_02>";
		if($servicio_activo_grupo[$grupos[$i]["id"]]){
			echo "<span class=texto_verde>Activo</span>";
		}
		else{
			echo "<span class=texto_rojo>Inactivo</span>";
		}
		echo "</td>";
		
		echo "<td class=borde_grupo_02>";
		if($indice_completo_grupo[$grupos[$i]["id"]]){
			echo "<span>Correcto</span>";
		}
		else if($indice_correcto_grupo[$grupos[$i]["id"]]){
			echo "<span>Incompleto</span>";
		}
		else{
			echo "<span>Incorrecto</span>";
		}
		echo "</td>";
		
		echo "<td class=borde_grupo_02>";
		
		if($servicio_activo_grupo[$grupos[$i]["id"]]){
			//echo "<span>Correcto</span>";
			echo "<a href= ".$control_servicios."?accion=detener_grupo&id_grupo=".$grupos[$i]["id"].">";
			echo "<img border=0 src=$estilos/detener_vacio.png height=$alto_detener_vacio width=$ancho_detener_vacio>";
			echo "</a>";
		}
		else if($indice_completo_grupo[$grupos[$i]["id"]]
			|| $indice_correcto_grupo[$grupos[$i]["id"]]){
			//echo "<span>Incompleto</span>";
			echo "<a href= ".$control_servicios."?accion=iniciar_grupo&id_grupo=".$grupos[$i]["id"].">";
			echo "<img border=0 src=$estilos/iniciar_vacio.png height=$alto_iniciar_vacio width=$ancho_iniciar_vacio>";
			echo "</a>";
		}
		else{
			echo "No es posible";
		}
		/*
		if($grupos[$i]["accion"]===0){
			echo "<a href= ".$control_servicios."?iniciar_g=".$grupos[$i]["id"].">";
			echo "<img border=0 src=$estilos/iniciar_vacio.png height=$alto_iniciar_vacio width=$ancho_iniciar_vacio>";
			echo "</a>";
		}
		else if($grupos[$i]["accion"]===1){
			echo "<a href= ".$control_servicios."?detener_g=".$grupos[$i]["id"].">";
			echo "<img border=0 src=$estilos/detener_vacio.png height=$alto_detener_vacio width=$ancho_detener_vacio>";
			echo "</a>";
		}
		else{
			echo "No es posible";
		}
		*/
		echo "</td>";
		
		echo "<td class=borde_grupo_03> &nbsp;</td>";
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
			if(strlen($dominios[$j]["nombre"])>25)
				$texto=substr($dominios[$j]["nombre"], 0, 25)."...";
			else
				$texto=$dominios[$j]["nombre"];
			echo "".$texto."";
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			echo $dominios[$j]["puerto"];
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			if($servicio_activo_dominio[$dominios[$j]["id"]]){
				echo "<span class=texto_verde>Activo</span>";
			}
			else{
				echo "<span class=texto_rojo>Inactivo</span>";
			}
			echo "</td>";
			
			echo "<td class=borde_dominio_02>";
			if($indice_completo_dominio[$dominios[$j]["id"]]){
				echo "<span>Correcto</span>";
			}
			else if($indice_correcto_dominio[$dominios[$j]["id"]]){
				echo "<span>Incompleto</span>";
			}
			else{
				echo "<span>Incorrecto</span>";
			}
			echo "</td>";
			
			echo "<td class=borde_dominio_03>";
			if($servicio_activo_dominio[$dominios[$j]["id"]]){
				echo "<a href= ".$control_servicios."?accion=detener_dominio&id_dominio=".$dominios[$j]["id"].">";
				//echo "Iniciar";
				echo "<img border=0 src=$estilos/detener_vacio.png height=$alto_detener_vacio width=$ancho_detener_vacio>";
				echo "</a>";
			
			}
			else if($indice_completo_dominio[$dominios[$j]["id"]]
				|| $indice_correcto_dominio[$dominios[$j]["id"]]){
				echo "<a href= ".$control_servicios."?accion=iniciar_dominio&id_dominio=".$dominios[$j]["id"].">";
				//echo "Detener";
				echo "<img border=0 src=$estilos/iniciar_vacio.png height=$alto_iniciar_vacio width=$ancho_iniciar_vacio>";
				echo "</a>";
			
			}
			else{
				echo "No es posible";
			}
			echo "</td>";
			
			echo "<td width=10 bgcolor=white></td>";
			echo "</tr>";
		}
	}

echo "</table>";




echo "</body>";
echo "</html>";

?>

