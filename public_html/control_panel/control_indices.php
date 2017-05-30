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

$id_semilla=$_GET["id_semilla"];
if(!ctype_digit($id_semilla))
	$id_semilla=0;

$post_accion=trim($_POST["post_accion"]);

$post_id_grupo=$_POST["post_id_grupo"];
if(!ctype_digit($post_id_grupo))
	$post_id_grupo=0;
	
if($post_accion!=null){
	$accion=$post_accion;
	$id_grupo=$post_id_grupo;
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

if($accion=="construir_indice_grupo"){
	//verificar que el grupo sea valido
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_grupo[$id_grupo]){
		echo "Existen colectas activas de este grupo, accion cancelada<br>";
	}
	else if(!$colecta_correcta_grupo[$id_grupo]){
		echo "Las colectas de este grupo presentan fallas, accion cancelada<br>";
	}
	else{
		//se creara el indice del grupo completo
		$grupo=filtrar_grupo($estructura, $id_grupo);
		//detener monitoreo del grupo
		desmarcar_grupo_monitoreable($db, $id_grupo);
		$id_semillas=filtrar_id_semillas_grupo($estructura, $id_grupo);
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Construyendo indice de grupo \"".$grupo["nombre"]."\"<br>";
		echo "Agrupando...<br>";
		agrupar_colectas($prefijo, "G", $id_grupo, $id_semillas, $ruta_bin, $ruta_colecta, $ruta_indice);
		echo "Indexando...<br>";
		crear_indice($prefijo, "G", $id_grupo, $ruta_bin, $ruta_indice);
		$id_creacion_indice=ingresar_log_creacion_indice($db, "G", $id_grupo);
		for($i=0; $i<count($id_semillas); $i++){
			ingresar_log_semillas_agrupadas($db, $id_semillas[$i], $id_creacion_indice);
		}
		//echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?#grupo_$id_grupo>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		//iniciar monitoreo del grupo
		marcar_grupo_monitoreable($db, $id_grupo);
		
		die();
	}
}
else if($accion=="construir_indice_dominio"){
	//verificar que el dominio sea valido
	if(!existe_dominio($db, $id_dominio)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa_dominio[$id_dominio]){
		echo "Existen colectas activas de este dominio, accion cancelada<br>";
	}
	else if(!$colecta_correcta_dominio[$id_dominio]){
		echo "Las colectas id_grupo=".$grupos[$i]["id"]."de este dominio presentan fallas, accion cancelada<br>";
	}
	else{
		//se creara el indice del dominio completo
		$dominio=filtrar_dominio($estructura, $id_dominio);
		//detener monitoreo del dominio
		desmarcar_dominio_monitoreable($db, $id_dominio);
		
		$id_semillas=filtrar_id_semillas_dominio($estructura, $id_dominio);
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Construyendo indice de dominio \"".$dominio["nombre"]."\"<br>";
		echo "Agrupando...<br>";
		agrupar_colectas($prefijo, "D", $id_dominio, $id_semillas, $ruta_bin, $ruta_colecta, $ruta_indice);
		echo "Indexando...<br>";
		crear_indice($prefijo, "D", $id_dominio, $ruta_bin, $ruta_indice);
		$id_creacion_indice=ingresar_log_creacion_indice($db, "D", $id_dominio);
		for($i=0; $i<count($id_semillas); $i++){
			ingresar_log_semillas_agrupadas($db, $id_semillas[$i], $id_creacion_indice);
		}
		//echo "<br>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		echo "<a href=?#dominio_$id_dominio>";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		//iniciar monitoreo del dominio
		marcar_dominio_monitoreable($db, $id_dominio);
		
		die();
	}
}
else if($accion=="inicio_indice_total"){
	
		echo "<form id=form_01 action=? method=post>";
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		
		echo "<td align=center width=600>";
		echo "Esta seguro que desea que el indice de este grupo<br>";
		echo "incluya a <b>todos los sitios del sistema</b> ? <br>";
		echo "<br>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "<tr align=center>";
		
		echo "<td>";
		echo "<input type=hidden name=post_accion value=construir_indice_total>";
		echo "<input type=hidden name=post_id_grupo value=".$id_grupo.">";
		echo "<input type=image src=$estilos/aceptar.png name=aceptar value=Aceptar height=25 width=85>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<a href=?#grupo_$id_grupo><img border=0 src=$estilos/cancelar.png height=25 width=85></a>";
		echo "</td>";
		
		echo "</tr>";
		
		echo "</table>";
		echo "</form>";
		die();
}
else if($accion=="construir_indice_total"){

	$grupos=$estructura["grupos"];
	$colecta_activa=false;
	for($i=0; $i<count($grupos); $i++){
		$dominios=$estructura["dominios"][$i];
		for($j=0; $j<count($dominios); $j++){
			$semillas=$estructura["semillas"][$i][$j];
			for($k=0; $k<count($semillas); $k++){
				if($colecta_activa_semilla[$semillas[$k]["id"]]){
					$colecta_activa=true;
					break;
				}
			}
			if($colecta_activa)
				break;
			if($colecta_activa_dominio[$dominios[$j]["id"]]){
				$colecta_activa=true;
				break;
			}
		}
		if($colecta_activa)
			break;
		if($colecta_activa_grupo[$grupos[$i]["id"]]){
			$colecta_activa=true;
			break;
		}
	}
	
	//verificar que el grupo sea valido
	if(!existe_grupo($db, $id_grupo)){
		echo "Valores Incorrectos<br>";
	}
	else if($colecta_activa){
		echo "Existe alguna colecta activa, accion cancelada<br>";
	}
	else{
	
		//detener monitoreo del grupo
		desmarcar_grupo_monitoreable($db, $id_grupo);
	
		//se reunen todas las semillas con colecta correcta
		$id_semillas=array();
		$grupos=$estructura["grupos"];
		for($i=0; $i<count($grupos); $i++){
			
			//Por ahora, se filtra solo para los grupos 62 (Sitios), 67 (Chileclic)
			if($grupos[$i]["id"]==62 || $grupos[$i]["id"]==67){
				
				echo "Agregando semillas de Grupo \"".$grupos[$i]["nombre"]."\"...<br>";
				
				$dominios=$estructura["dominios"][$i];
				for($j=0; $j<count($dominios); $j++){
					$semillas=$estructura["semillas"][$i][$j];
					for($k=0; $k<count($semillas); $k++){
						if($colecta_correcta_semilla[$semillas[$k]["id"]]){
							$id_semillas[]=$semillas[$k]["id"];
						}
					}
				}
				
			
			}//Grupos aceptados
			
		}
		
		$grupo=filtrar_grupo($estructura, $id_grupo);
		echo "<table cellpadding=0 cellspacing=0 border=0>";
		echo "<tr valign=center align=center>";
		echo "<td align=center width=600>";
		echo "Construyendo indice total para el grupo \"".$grupo["nombre"]."\"<br>";
		echo "Agrupando...<br>";
		agrupar_colectas($prefijo, "G", $id_grupo, $id_semillas, $ruta_bin, $ruta_colecta, $ruta_indice);
		echo "Indexando...<br>";
		crear_indice($prefijo, "G", $id_grupo, $ruta_bin, $ruta_indice);
		
		//Para fines de Logs, solo agregare las semillas del grupo
		$id_semillas=filtrar_id_semillas_grupo($estructura, $id_grupo);
		$id_creacion_indice=ingresar_log_creacion_indice($db, "G", $id_grupo);
		for($i=0; $i<count($id_semillas); $i++){
			ingresar_log_semillas_agrupadas($db, $id_semillas[$i], $id_creacion_indice);
		}
		
		echo "</td>";
		echo "</tr>";
		
		echo "<tr align=center>";
		echo "<td align=center>";
		//echo "<a href=?#grupo_$id_grupo>";
		echo "<a href=\"?\">";
		//echo "Volver";
		echo "<img border=0 src=$estilos/volver.png height=$alto_volver width=$ancho_volver>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<br>";
		
		//iniciar monitoreo del grupo
		marcar_grupo_monitoreable($db, $id_grupo);
		
		die();
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

mysql_close($db);
html_fin_mensajes();


$titulo="Creacion de Indices";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento=array();
$elemento["nombre"]="Detalles";
$elemento["ruta"]="control_indices_detalle.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

$elemento=array();
$elemento["nombre"]="Refinamiento";
$elemento["ruta"]="control_indices_refinamiento.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);

echo "<table cellpadding=0 cellspacing=0 border=0>
<tr height=$alto_fila style=\"background-color: rgb(180, 180, 180);\" >
	<th style=\"width:30;\">&nbsp;</th>
	<th style=\"width:250;\">Indices</th>
	<th style=\"width:130;\">Estado</th>
	<th style=\"width:130;\">Colecta</th>
	<th style=\"width:250;\">Acciones</th>
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
	
	echo "<td class=borde_grupo_02>";
	if($colecta_activa_grupo[$grupos[$i]["id"]]){
		//echo "En Proceso (<a href=control_colectas_detalle.php?id_grupo=".$grupos[$i]["id"]." >Ir a</a>)";
		echo "En Proceso";
	}
	else if($colecta_correcta_grupo[$grupos[$i]["id"]]){
		//echo "Correcta (<a href=control_colectas_detalle.php?id_grupo=".$grupos[$i]["id"]." >Ir a</a>)";
		echo "Correcta";
	}
	else if($colecta_hijos_grupo[$grupos[$i]["id"]]){
		//echo "Incompleta (<a href=control_colectas_detalle.php?id_grupo=".$grupos[$i]["id"]." >Ir a</a>)";
		echo "Incompleta";
	}
	else{
		//echo "Vacia (<a href=control_colectas_detalle.php?id_grupo=".$grupos[$i]["id"]." >Ir a</a>)";
		echo "Vacia";
	}
	echo "</td>";
	
	echo "<td class=borde_grupo_02 align=center>";
	if(!$colecta_activa_grupo[$grupos[$i]["id"]] 
		&& $colecta_correcta_grupo[$grupos[$i]["id"]]){
		echo "<table width=240 cellpadding=0 cellspacing=0 border=0>";
		echo "<tr>";
		echo "<td align=center>";
		echo "<a href=?accion=construir_indice_grupo&id_grupo=".$grupos[$i]["id"].">";
		//echo "Construir";
		//echo "<img border=0 src=$estilos/construir_indice.png height=$alto_construir_indice width=$ancho_construir_indice>";
		echo "<img border=0 src=$estilos/crear_indice.png height=$alto_crear_indice width=$ancho_crear_indice>";
		echo "</a>";
		echo "</td>";
		echo "<td align=center>";
		echo "<a href=?accion=inicio_indice_total&id_grupo=".$grupos[$i]["id"].">";
		echo "<img border=0 src=$estilos/indice_total.png height=$alto_indice_total width=$ancho_indice_total>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
	}
	else{
		echo "No es posible";
	}
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
		
		echo "<td class=borde_dominio_02>";
		if($colecta_activa_dominio[$dominios[$j]["id"]]){
			//echo "En Proceso (<a href=control_colectas_detalle.php?id_dominio=".$dominios[$j]["id"]." >Ir a</a>)";
			echo "En Proceso";
		}
		else if($colecta_correcta_dominio[$dominios[$j]["id"]]){
			//echo "Correcta (<a href=control_colectas_detalle.php?id_dominio=".$dominios[$j]["id"]." >Ir a</a>)";
			echo "Correcta";
		}
		else if($colecta_hijos_dominio[$dominios[$j]["id"]]){
			//echo "Incompleta (<a href=control_colectas_detalle.php?id_dominio=".$dominios[$j]["id"]." >Ir a</a>)";
			echo "Incompleta";
		}
		else{
			//echo "Vacia (<a href=control_colectas_detalle.php?id_dominio=".$dominios[$j]["id"]." >Ir a</a>)";
			echo "Vacia";
		}
		echo "</td>";
		
		echo "<td class=borde_dominio_03>";
		if(!$colecta_activa_dominio[$dominios[$j]["id"]] 
			&& $colecta_correcta_dominio[$dominios[$j]["id"]]){
			echo "<a href=?accion=construir_indice_dominio&id_dominio=".$dominios[$j]["id"].">";
			//echo "Construir";
			//echo "<img border=0 src=$estilos/construir_indice.png height=$alto_construir_indice width=$ancho_construir_indice>";
			echo "<img border=0 src=$estilos/crear_indice.png height=$alto_crear_indice width=$ancho_crear_indice>";
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

?>

</table>

</body>

</html>
