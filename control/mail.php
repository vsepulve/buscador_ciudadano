<?php

require_once("config.ini");

require_once("$ruta_funciones/parser-xml.php");

//echo "Prueba Mail<br>";

$tiempo=date("Y-m-d H:i:s");

$dest="control.buscador@dcc.uchile.cl";

$head="From: Buscador <control.buscador@buscador.cl>\r\n";
$head.="To: $dest\r\n";

$head.="MIME-Version: 1.0\r\n";
$head.="Content-type: text/html; charset=iso-8859-1\r\n";

$textoQuery=array();
$textoQuery[]="senen";
$textoQuery[]="senen";
$textoQuery[]="senen";
$textoQuery[]="becas";

$puerto=array();
$puertos[]="30686";
$puertos[]="45754";
$puertos[]="33411";
$puertos[]="41983";

$nombre_puerto=array();
$nombre_puerto[]="Sitios";
$nombre_puerto[]="Transparencia";
$nombre_puerto[]="Municipios";
$nombre_puerto[]="Chileclic";

$resultados_esperados=array();
$resultados_esperados[]=58;
$resultados_esperados[]=83;
$resultados_esperados[]=5;
$resultados_esperados[]=85;

$ok=true;

$mensaje="<html>\n";
$mensaje.="<head>\n";
$mensaje.="<title>Control</title>\n";
$mensaje.="</head>\n";

$mensaje.="<body style=\"font-family:Arial,Helvetica,sans-serif; font-size:14px;\">\n";

$mensaje.="<h3>Mail de Control - $tiempo</h3>\n";
$mensaje.="<br>\n";

$maquina="10 20 1 11";
$nombre_maquina="Desarrollo";
//echo "Probando Maquina \"$maquina\"<br>";
$mensaje.="Probando Maquina $nombre_maquina ($maquina)<br>\n";
for($k=0; $k<count($puertos); $k++){
	//echo "Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$mensaje.="Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$comando="$ruta_bin/$query_xml ".$textoQuery[$k]." 0 0 0 0 0 0 ".$puertos[$k]." $maquina";
	
	$error=0;
	
	$resultado=array();
	exec($comando, $resultado);
	
	$texto="";
	for($i=0; $i<count($resultado); $i++)
		$texto=$texto.$resultado[$i];

	//echo "Creando Parser<br>";
	if (!($xml_parser=xml_parser_create()))
		die("Couldn't create parser.");
	//echo "Parseando texto<br>";
	xml_parser_set_option( $xml_parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler( $xml_parser, "characterDataHandler");
	xml_parse($xml_parser, utf8_encode($texto));
	//echo "Liberando Parser<br>";
	xml_parser_free($xml_parser);
	
	//echo "Resultados: \"".$results["TOTAL"]."\"<br>";
	if($results["TOTAL"]==$resultados_esperados[$k]){
		//echo "<div style=\"color:greeen;\">Todo Bien</div><br>\n";
		$mensaje.="<span style=\"color:green;\">Todo Bien</span><br>\n";
	}
	else{
		//echo "<div style=\"color:red;\">Problemas</div><br>\n";
		$mensaje.="<span style=\"color:red;\">Problemas</span><br>\n";
		$ok=false;
	}
}
$mensaje.="<br>\n";

$maquina="10 20 1 19";
$nombre_maquina="Produccion 01";
//echo "Probando Maquina \"$maquina\"<br>";
$mensaje.="Probando Maquina $nombre_maquina ($maquina)<br>\n";
for($k=0; $k<count($puertos); $k++){
	//echo "Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$mensaje.="Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$comando="$ruta_bin/$query_xml ".$textoQuery[$k]." 0 0 0 0 0 0 ".$puertos[$k]." $maquina";
	
	$error=0;
	
	$resultado=array();
	exec($comando, $resultado);
	
	$texto="";
	for($i=0; $i<count($resultado); $i++)
		$texto=$texto.$resultado[$i];

	//echo "Creando Parser<br>";
	if (!($xml_parser=xml_parser_create()))
		die("Couldn't create parser.");
	//echo "Parseando texto<br>";
	xml_parser_set_option( $xml_parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler( $xml_parser, "characterDataHandler");
	xml_parse($xml_parser, utf8_encode($texto));
	//echo "Liberando Parser<br>";
	xml_parser_free($xml_parser);
	
	//echo "Resultados: \"".$results["TOTAL"]."\"<br>";
	if($results["TOTAL"]==$resultados_esperados[$k]){
		//echo "<div style=\"color:greeen;\">Todo Bien</div><br>\n";
		$mensaje.="<span style=\"color:green;\">Todo Bien</span><br>\n";
	}
	else{
		//echo "<div style=\"color:red;\">Problemas</div><br>\n";
		$mensaje.="<span style=\"color:red;\">Problemas</span><br>\n";
		$ok=false;
	}
}
$mensaje.="<br>\n";

$maquina="10 20 1 20";
$nombre_maquina="Produccion 02";
//echo "Probando Maquina \"$maquina\"<br>";
$mensaje.="Probando Maquina $nombre_maquina ($maquina)<br>\n";
for($k=0; $k<count($puertos); $k++){
	//echo "Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$mensaje.="Probando Puerto ".$nombre_puerto[$k]." (".$puertos[$k].")... &nbsp; ";
	$comando="$ruta_bin/$query_xml ".$textoQuery[$k]." 0 0 0 0 0 0 ".$puertos[$k]." $maquina";
	
	$error=0;
	
	$resultado=array();
	exec($comando, $resultado);
	
	$texto="";
	for($i=0; $i<count($resultado); $i++)
		$texto=$texto.$resultado[$i];

	//echo "Creando Parser<br>";
	if (!($xml_parser=xml_parser_create()))
		die("Couldn't create parser.");
	//echo "Parseando texto<br>";
	xml_parser_set_option( $xml_parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler( $xml_parser, "characterDataHandler");
	xml_parse($xml_parser, utf8_encode($texto));
	//echo "Liberando Parser<br>";
	xml_parser_free($xml_parser);
	
	//echo "Resultados: \"".$results["TOTAL"]."\"<br>";
	if($results["TOTAL"]==$resultados_esperados[$k]){
		//echo "<div style=\"color:greeen;\">Todo Bien</div><br>\n";
		$mensaje.="<span style=\"color:green;\">Todo Bien</span><br>\n";
	}
	else{
		//echo "<div style=\"color:red;\">Problemas</div><br>\n";
		$mensaje.="<span style=\"color:red;\">Problemas</span><br>\n";
		$ok=false;
	}
}
$mensaje.="<br>\n";



$mensaje.="</body>\n";
$mensaje.="</html>\n";

echo "".$mensaje."";

$tema="Control $tiempo";
if($ok){
	$tema.=" (Todo Bien)";
}
else{
	$tema.=" (Problemas)";
}
if(mail($dest, $tema, $mensaje, $head)) {
	echo "Mensaje Enviado a las $tiempo ($dest).<br>\n";
}
else{
	echo "Error de envío a las $tiempo ($dest).<br>\n";
}



?>
