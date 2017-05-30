<?php

/*
Aqui se almacenan todas las funciones relacionadas con generar codigo html
*/

function html_inicio_mensajes(){
	echo "<p>";
	echo "<div style=\"border-bottom: thin solid #000000; border-left: thin solid #000000; border-right: thin solid #000000; border-top: thin solid #000000;\"><div align=left>";
}

function html_fin_mensajes(){
	echo "</div></div></p>";
}

function html_head($titulo){
	global $estilos;
	
	echo
	"<head>
	<link rel=stylesheet type=text/css href=$estilos/control_panel.css>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
	<title>$titulo</title>
	</head>";

}

function html_menu_superior($origen){
	global $control_servicios, $control_indices, $control_colectas, $control_configuracion, $control_historial, $control_usuarios;
	echo "<table cellpadding=0 cellspacing=0 border=0 align=left width=700>";
	echo "<tr>";
	echo "<td width=110 align=center>";
	if($origen===0){
		echo "<div style=\"font-weight: bold; text-decoration: none;\">Servicios</div>";
	}
	else{
		echo "<a href=$control_servicios style=\"font-weight: bold; \">Servicios</a>";
	}
	echo "</td>";
	
	echo "<td width=110 align=center>";
	if($origen===1){
		echo "<div style=\"font-weight: bold; text-decoration: none;\">Indices</div>";
	}
	else{
		echo "<a href=$control_indices style=\"font-weight: bold;\">Indices</a>";
	}
	echo "</td>";
	
	echo "<td width=110 align=center>";
	if($origen===2){
		echo "<div style=\"font-weight: bold; text-decoration: none;\">Colectas</div>";
	}
	else{
		echo "<a href=$control_colectas style=\"font-weight: bold; \">Colectas</a>";
	}
	echo "</td>";
	
	echo "<td width=170 align=center>";
	if($origen===3){
		echo "<div style=\"font-weight: bold; text-decoration: none;\">Configuracion</div>";
	}
	else{
		echo "<a href=$control_configuracion style=\"font-weight: bold;\">Configuracion</a>";
	}
	echo "</td>";
	
	echo "<td width=100 align=center>";
	if($origen===4){
		echo "<div style=\"font-weight: bold; text-decoration: none;\">Usuarios</div>";
	}
	else{
		echo "<a href=$control_usuarios style=\"font-weight: bold;\">Usuarios</a>";
	}
	echo "</td>";
	
	echo "<td width=100 align=center><a href=sesion.php?salir=1 style=\"font-weight: bold;\">Salir</a></td>";
	
	echo "</tr>";
	echo "</table>";
	echo "<br>";
	echo "<br>";
}

function html_menu_izquierdo($titulo, $opciones, $elementos){
	global $estilos;
	
	$ancho_total=800;
	$ancho_opciones=300;
	$numero_elementos=count($elementos);
	if($numero_elementos)
		$ancho_opcion=$ancho_opciones/$numero_elementos;
	else
		$ancho_opcion=$ancho_opciones;
	
	echo "<table cellpadding=0 cellspacing=0 border=0 width=$ancho_total height=60>";

	echo "<tr height=60 valing=top>";

	echo "<td align=left width=".($ancho_total-$ancho_opciones)." valign=top>";
	echo "<h1>$titulo</h1>";
	echo "</td>";

	echo "<td align=center valign=top>";
	
	echo "<table cellpadding=0 cellspacing=0 border=0 width=$ancho_opciones>";
	echo "<tr>";
	if($numero_elementos)
		echo "<td colspan=".$numero_elementos." align=center>";
	else
		echo "<td align=center>";
	echo "<span class=texto_gris>";
	if($opciones){
		echo "".$opciones."";
	}
	else{
		echo "&nbsp;";
	}
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	if(!$numero_elementos)
		echo "<td width=$ancho_opciones>&nbsp;</td>";
	for($i=0; $i<$numero_elementos; $i++){
		echo "<td align=center width=$ancho_opcion>";
		if($elementos[$i]["principal"])
			echo "<a href=".$elementos[$i]["ruta"]." style=\"font-weight: bold;\">";
		else
			echo "<a href=".$elementos[$i]["ruta"].">";
		if($elementos[$i]["imagen"])
			echo "<img style=\"border-style: none;\" src=$estilos/".$elementos[$i]["imagen"]." height=".$elementos[$i]["alto"]." width=".$elementos[$i]["ancho"].">";
		else
			echo "".$elementos[$i]["nombre"];
		echo "</a>";
		echo "</td>";
	}

	echo "<tr>";
	echo "</table>";

	echo "</td>";

	echo "</tr>";
	echo "</table>";
}

?>
