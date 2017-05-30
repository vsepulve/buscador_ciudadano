<?php

//Temporalmente, solo se contesta un indice en forma local, y el otro en forma remota
$id_grupo_local=62;
$local="127 0 0 1";
$remoto="127 0 0 1";

//----- Inclusiones -----

require_once("config.default.ini");
require_once("$ruta_funciones/parser-xml-extendido.php");

//----- Definicion de Grupos y Refinamiento de Consulta -----

$estructura=obtener_estructura($db);

$grupos=obtener_grupos($db);
for($i=0; $i<count($grupos); $i++){
	$palabras_adicionales[$grupos[$i]["id"]]=obtener_refinamiento_grupo($db, $grupos[$i]["id"]);
}

//----- Metodo para el refinamiento de consulta -----

function limpiar_palabras_adicionales($html, $palabras){
	
	$inicio_tag="<span class=\"highlight\">";
	$largo_inicio=24;
	$fin_tag="</span>";
	$largo_fin=7;
	
	$arreglo=explode($inicio_tag, $html);
	$html_salida=$arreglo[0];
	
	for($i=1; $i<count($arreglo); $i++){
		$texto=substr($arreglo[$i], 0, strpos($arreglo[$i], $fin_tag));
		$palabra=trim($texto);
		//Si la palabra esta en $palabras, agregarla omitiendo el $fin_tag
		//Si no, agregar el $inicio_tag y el $arreglo[$i] sin cambios
		$descartar=false;
		for($j=0; $j<count($palabras); $j++){
			if(strcasecmp($palabra, $palabras[$j])===0){
				$descartar=true; 
				break;
			}
		}
		if($descartar){
			//echo "<h3>Descartar</h3>";
			$html_salida.=$texto;
			$html_salida.=substr($arreglo[$i], (strlen($texto)+$largo_fin));
		}
		else{
			//echo "<h3>Mantener</h3>";
			$html_salida.=$inicio_tag;
			$html_salida.=$arreglo[$i];
		}
	}
	
	return $html_salida;
}

//----- Parametros de Busqueda -----

$query=$_GET["query"];
//Primero las eliminadas, luego las reemplazadas por espacio
$entradas=array("'", "\\", "\"", ";", "<", ">", "/", "#", "@");
$salidas=array("", "", "", "", " ", " ", " ", " ", " ");
$textoQuery=trim(str_replace($entradas, $salidas, $query));
$queryLista=str_replace(" ", "+", $textoQuery);

$and_or=$_GET["and_or"];
if(!ctype_digit($and_or))
	$and_or=0;

$accents=$_GET["accents"];
if(!ctype_digit($accents))
	$accents=0;

$feedback=$_GET["feedback"];
if(!ctype_digit($feedback))
	$feedback=0;

$doc_id=$_GET["doc_id"];
if(!ctype_digit($doc_id))
	$doc_id=0;

$page=$_GET["page"];
if(!ctype_digit($page))
	$page=0;

$docs_page=$_GET["docs_page"];
if(!ctype_digit($docs_page))
	$docs_page=10;
else if($docs_page<10)
	$docs_page=10;
else if($docs_page>50)
	$docs_page=50;
/*
$id_grupo=$_GET["id_grupo"];
if(!ctype_digit($id_grupo))
	$id_grupo=0;
*/
$indice=$_GET["indice"];
$tipo_consulta=false;
$id_grupo=0;
$id_dominio=0;
if($indice[0]=="G"){
	$tipo_consulta="G";
	$id_grupo=substr($indice, 1, (strlen($indice)-1));
	if(!ctype_digit($id_grupo))
		$id_grupo=0;
}
else if($indice[0]=="D"){
	$tipo_consulta="D";
	$id_dominio=substr($indice, 1, (strlen($indice)-1));
	if(!ctype_digit($id_dominio))
		$id_dominio=0;
}
//echo "\$tipo_consulta: ".$tipo_consulta."<br>\n";
//echo "\$id_grupo: ".$id_grupo."<br>\n";
//echo "\$id_dominio: ".$id_dominio."<br>\n";

$puerto=0;
if($tipo_consulta=="G"){
	$puerto=obtener_puerto_grupo($db, $id_grupo);
}
else if($tipo_consulta=="D"){
	$puerto=obtener_puerto_dominio($db, $id_dominio);
}
//echo "\$puerto: $puerto<br>\n";
//----- Inicio de Consulta -----

if($textoQuery){
	
	//----- Preparar Consulta Adicional -----
	$queryAdicional="";
	for($i=0; $i<count($grupos); $i++){
		if($id_grupo==$grupos[$i]["id"]){
			for($j=0; $j<count($palabras_adicionales[$id_grupo]); $j++){
				$queryAdicional.="+".$palabras_adicionales[$id_grupo][$j];
			}
		}
	}
	
	//----- Realizar Consulta Original -----
	
	$result_original=array();
	$comando="$ruta_bin/$query_xml ".$queryLista." $and_or $accents $feedback $doc_id $page $docs_page $puerto";
	if($id_grupo==$id_grupo_local)
		$comando.=" $local";
	else
		$comando.=" $remoto";
	exec($comando, $result_original);

	$texto_original="";
	for($i=0; $i<count($result_original); $i++)
		$texto_original=$texto_original.$result_original[$i];
	
	//----- Realizar Consulta Adicional -----
	
	$result=array();
	$comando="$ruta_bin/$query_xml ".$queryLista.$queryAdicional." $and_or $accents $feedback $doc_id $page $docs_page $puerto";
	if($id_grupo==$id_grupo_local)
		$comando.=" $local";
	else
		$comando.=" $remoto";
	exec($comando, $result);
	$texto="";
	for($i=0; $i<count($result); $i++)
		$texto=$texto.$result[$i];
	
	//----- Preparacion Parser XML Extendido -----
	
	$error=array();
	$query=array();
	$suggestions=array();
	$countSuggestions=array();
	$results=array();
	$countResults=array();

	$state='';
	$inSuggestions=false;
	$inResults=false;

	//----- Parser XML 0 (Original) -----
	
	//echo "Creando Parser<br>";
	$consulta_actual=0;
	$countSuggestions[$consulta_actual]=0;
	$countResults[$consulta_actual]=0;
	if (!($xml_parser = xml_parser_create()))
		die("Couldn't create parser.");
	//echo "Parseando texto<br>";
	xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_set_element_handler($xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler($xml_parser, "characterDataHandler");
	xml_parse($xml_parser, utf8_encode($texto_original));
	//echo "Liberando Parser<br>";
	xml_parser_free($xml_parser);

	//----- Parser XML 1 (Adicional) -----

	//echo "Creando Parser<br>";
	$consulta_actual=1;
	$countSuggestions[$consulta_actual]=0;
	$countResults[$consulta_actual]=0;
	if (!($xml_parser = xml_parser_create()))
		die("Couldn't create parser.");
	//echo "Parseando texto<br>";
	xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_set_element_handler($xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler($xml_parser, "characterDataHandler");
	xml_parse($xml_parser, utf8_encode($texto));
	//echo "Liberando Parser<br>";
	xml_parser_free($xml_parser);
	
	//Si la consulta actual tiene muy pocos resultados, usar la original
	$minimo_resultados=5;
	if($results[1]["TOTAL"]<$minimo_resultados){
		$consulta_actual=0;
	}
	
	//----- Estimar Numero de Resultados -----
	if($tipo_consulta=="G" && $results[$consulta_actual]["TOTAL"]==200){
		$numero_resultados=numero_resultados($db, $queryLista, $id_grupo, $ruta_bin, $ruta_listas);
		if($numero_resultados<200)
			$numero_resultados=200;
	}
	else{
		$numero_resultados=$results[$consulta_actual]["TOTAL"];
	}
	echo "Numero de Resultados: $numero_resultados<br>";
	
	
	
}//if... hay consulta

echo "<html>\n";

echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\"/>\n";
echo "<title>Buscador Ligero</title>\n";

echo "<style type=\"text/css\">\n";
echo ".highlight{font-weight: bold;}\n";
/*
echo ".texto_rojo{color: red;}\n";
echo ".texto_pequeño{font-size: 11pt;}\n";
echo ".texto_pequeño_url{font-style: italic;color: #777777;font-size: 11pt;}\n";
echo ".texto_medio{font-size: 13pt;}\n";
echo ".margen_resultados{width: 20px;}\n";
*/
echo "</style>\n";

echo "</head>\n";

echo "<body>\n";

echo "<form name=form1 action=?>\n";
echo "<table border=0 cellspacing=0 cellpadding=0 width=800>\n";

echo "<tr height=50 align=center valign=center>";
echo "<td align=center valign=center>";
echo "<b>Buscador de ChileClic (versi&oacute;n ligera)</b>";
echo "</td>";
echo "</tr>";

echo "<tr align=center valign=center height=50>\n";

echo "<td align=center>\n";
echo "<input type=text name=query size=40 value=\"$textoQuery\">\n";
echo "&nbsp;&nbsp;";
echo "<input type=submit name=accion value=\"Buscar\">\n";
echo "</td>\n";

echo "</tr>\n";

$ancho_total=600;

//Modelo de Una sola fila (para 4 grupos)
$ancho=600/4;

$total_letras=0;
for($i=0; $i<count($grupos) && $i<4; $i++){
	$total_letras+=strlen($grupos[$i]["nombre"]);
}

echo "<tr align=center>\n";
/*
for($i=0; $i<count($grupos) && $i<4; $i++){
	echo "<td width=".number_format($ancho_total*strlen($grupos[$i]["nombre"])/$total_letras, 0)." align=center>\n";
	if($id_grupo==0 && $i==0){
		echo "<input type=radio name=id_grupo value=".$grupos[$i]["id"]." checked>".$grupos[$i]["nombre"]."\n";
	}
	else if($id_grupo==$grupos[$i]["id"]){
		echo "<input type=radio name=id_grupo value=".$grupos[$i]["id"]." checked>".$grupos[$i]["nombre"]."\n";
	}
	else{
		echo "<input type=radio name=id_grupo value=".$grupos[$i]["id"].">".$grupos[$i]["nombre"]."\n";
	}
	echo "</td>\n";
}
*/

echo "<td align=center>";
echo "<select name=indice>\n";

for($i=0; $i<count($grupos); $i++){
	if($tipo_consulta=="G" && $id_grupo==$grupos[$i]["id"])
		echo "<option value=\"G".$grupos[$i]["id"]."\" selected>  -- ".$grupos[$i]["nombre"]." (Todos los Dominios) --  \n";
	else
		echo "<option value=\"G".$grupos[$i]["id"]."\">  -- ".$grupos[$i]["nombre"]." (Todos los Dominios) --  \n";
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		if($tipo_consulta=="D" && $id_dominio==$dominios[$j]["id"])
			echo "<option value=\"D".$dominios[$j]["id"]."\"selected>".$grupos[$i]["nombre"]." - ".$dominios[$j]["nombre"]."\n";
		else
			echo "<option value=\"D".$dominios[$j]["id"]."\">".$grupos[$i]["nombre"]." - ".$dominios[$j]["nombre"]."\n";
		
	}
	
}

echo "</select>\n";
echo "</td>";

echo "</tr>\n";

/*
//Modelo de Cuartetos (para multiples grupos)
$cuartos=number_format(count($grupos)/4, 0);
if(4*$cuartos<count($grupos)){
	$cuartos++;
}
for($j=0; $j<$cuartos; $j++){
	if($j<$cuartos-1){
		$este_cuarto=4;
	}
	else{
		$este_cuarto=count($grupos)-4*$j;
	}
	
	echo "<tr align=center>\n";
	echo "<td align=center>\n";
	echo "<table border=0 cellspacing=0 cellpadding=0 width=$ancho_total>\n";
	echo "<tr>\n";
	$total_letras=0;
	for($i=0; $i<$este_cuarto; $i++){
		$total_letras+=strlen($grupos[4*$j+$i]["nombre"]);
	}
	for($i=0; $i<$este_cuarto; $i++){
		echo "<td width=".number_format($ancho_total*strlen($grupos[4*$j+$i]["nombre"])/$total_letras, 0)." align=center>\n";
		if($id_grupo==0 && $i==0){
			echo "<input type=radio name=id_grupo value=".$grupos[4*$j+$i]["id"]." checked>".$grupos[4*$j+$i]["nombre"]."\n";
		}
		else if($id_grupo==$grupos[4*$j+$i]["id"]){
			echo "<input type=radio name=id_grupo value=".$grupos[4*$j+$i]["id"]." checked>".$grupos[4*$j+$i]["nombre"]."\n";
		}
		else{
			echo "<input type=radio name=id_grupo value=".$grupos[4*$j+$i]["id"].">".$grupos[4*$j+$i]["nombre"]."\n";
		}
		echo "</td>\n";
	}
	echo "</tr>\n";
	echo "</table>";
	echo "</td>\n";
	echo "</tr>\n";
}
*/

echo "</table>\n";
echo "</form>\n";

echo "<br>\n";

echo "<table border=0 cellspacing=0 cellpadding=0 width=800>\n";

//Fila de control
echo "<tr height=5>";
echo "<td width=20></td>";
echo "<td width=50></td>";
echo "<td width=450></td>";
echo "<td width=160></td>";
echo "<td width=20></td>";
echo "</tr>";

if($countSuggestions[0]>0 ){
	echo "<tr>";
	echo "<td class=margen_resultados>&nbsp;</td>";
	echo "<td colspan=3 align=left valign=top class=texto_medio>";
	echo "<span class=texto_rojo>Sugerencias:&nbsp;</span>";
	for($i=0; $i<$suggestions[0]["TOTAL"]; $i++){
		if($i>0){
			echo ", &nbsp;";
		}
		echo "<a href=?query=".str_replace(" ", "+", $suggestions[0][$i]["NEW_QUERY"])."&and_or=$and_or&accents=$accents&feedback=$feedback&docs_page=$docs_page&indice=$indice >";
		echo trim($suggestions[0][$i]["NEW_QUERY"]);
		echo "</a>";
	}
	echo "</td>";
	echo "<td class=margen_resultados>&nbsp;</td>";
	echo "</tr>";
	echo "<tr height=5><td colspan=5></td><tr>";
}

echo "<tr>";
echo "<td class=margen_resultados>&nbsp;</td>";

echo "<td colspan=3 align=left valign=top class=texto_medio>";
if($textoQuery==null){
	echo "&nbsp;";
}
else if($error[$consulta_actual]>0){
	echo "<b>Sistema inactivo.</b>";
}
else if($countResults[$consulta_actual]>0){

	//numero de paginas
	$numeroPaginas=$results[$consulta_actual]["TOTAL"]/$docs_page;
	$numeroPaginas=number_format($numeroPaginas, 0);
	if( ($numeroPaginas*$docs_page) < $results[$consulta_actual]["TOTAL"] )
		$numeroPaginas++;
	
	echo "P&aacute;gina &nbsp;: &nbsp;";
	for($i=0; $i<$numeroPaginas; $i++){
		if($i==$page)
			echo "<b>".($i+1)."</b> &nbsp;";
		else
			echo "<a href=?query=".str_replace(" ", "+", $textoQuery)."&and_or=$and_or&accents=$accents&page=$i&feedback=$feedback&docs_page=$docs_page&indice=$indice >".($i+1)."</a> &nbsp;";
	}
	
}
else{
	echo "<b>No se encontraron resultados.</b>";
}
echo "</td>";

echo "<td class=margen_resultados>&nbsp;</td>";

echo "</tr>";


if($countResults[$consulta_actual]>0){

	echo "<tr><td colspan=5>&nbsp;</td></tr>";

	for($i=0; $i<$countResults[$consulta_actual]; $i++){

		echo "<tr>\n";
	
		echo "<td class=margen_resultados>&nbsp;</td>\n";
	
		echo "<td align=left valign=top class=texto_medio>\n";
		echo "<b>".$results[$consulta_actual][$i]["RANK"].".</b> &nbsp; ";
		echo "<br>";
		echo "</td>\n";

		echo "<td align=left valign=top colspan=2 class=texto_medio>\n";
		
		if($results[$consulta_actual][$i]["TYPE"] != "UNKNOWN"
			&& $results[$consulta_actual][$i]["TYPE"] != "HTML")
			echo "<span class=texto_pequeño>[".$results[$consulta_actual][$i]["TYPE"]."]</span>&nbsp;";
			
		echo "<a href=".$results[$consulta_actual][$i]["URL"].">";
		echo utf8_decode_seguro(limpiar_palabras_adicionales($results[$consulta_actual][$i]["TITLE"], $palabras_adicionales[$id_grupo]));
		//echo limpiar_palabras_adicionales($results[$consulta_actual][$i]["TITLE"], $palabras_adicionales[$id_grupo]);
		echo "</a>";
		if($results[$consulta_actual][$i]["LANGUAGE"]!="SPANISH")
			echo " &nbsp; <i>[".$results[$consulta_actual][$i]["LANGUAGE"]."]</i>";
		echo "</td>\n";
		
		echo "<td class=margen_resultados>&nbsp;</td>\n";
		
		echo "</tr>\n";
		
		echo "<tr>\n";
		
		echo "<td class=margen_resultados>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		
		echo "<td align=left valign=top colspan=2 class=texto_pequeño>\n";
		
		echo limpiar_palabras_adicionales($results[$consulta_actual][$i]["SUMMARY"], $palabras_adicionales[$id_grupo]);
	
		echo "</td>\n";
	
		echo "<td class=margen_resultados>&nbsp;</td>\n";

		echo "</tr>\n";
	
		echo "<tr>\n";
	
		echo "<td class=margen_resultados>&nbsp;</td>\n";
	
		echo "<td>&nbsp;</td>\n";
	
		echo "<td align=left class=texto_pequeño>\n";
		echo "<a href=".$results[$consulta_actual][$i]["URL"]." class=texto_pequeño_url>".$results[$consulta_actual][$i]["SHORT_URL"]."</a>";
		echo "&nbsp;&nbsp;";
		echo "<i>(".texto_tamaño($results[$consulta_actual][$i]["SIZE"]).")</i>";
		echo "</td>\n";
	
		echo "<td align=right class=texto_pequeño>\n";
		echo "<a href=?query=".$queryLista."&and_or=$and_or&accents=$accents&page=$page&feedback=1&docs_page=$docs_page&doc_id=".$results[$consulta_actual][$i]["DOC_ID"]."&indice=$indice  > [P&aacute;ginas similares]</a>";
		echo "</td>\n";
	
		echo "<td class=margen_resultados>&nbsp;</td>\n";
	
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td colspan=5>&nbsp;</td>\n";
		echo "</tr>\n";

	} //for each resut

	echo "<tr align=center valign=top>";

	echo "<td class=margen_resultados>&nbsp;</td>";

	echo "<td colspan=3 align=left valign=top class=texto_medio>";
	//numero de paginas
	$numeroPaginas=$results[$consulta_actual]["TOTAL"]/$docs_page;
	$numeroPaginas=number_format($numeroPaginas, 0);
	if( ($numeroPaginas*$docs_page) < $results[$consulta_actual]["TOTAL"] )
		$numeroPaginas++;
	
	echo "P&aacute;gina &nbsp;: &nbsp;";
	for($i=0; $i<$numeroPaginas; $i++){
		if($i==$page)
			echo "<b>".($i+1)."</b> &nbsp;";
		else
			echo "<a href=?query=".str_replace(" ", "+", $textoQuery)."&and_or=$and_or&accents=$accents&page=$i&feedback=$feedback&docs_page=$docs_page&indice=$indice >".($i+1)."</a> &nbsp;";
	}

	echo "</td>";

	echo "<td class=margen_resultados>&nbsp;</td>";

	echo "</tr>";


} //if results[total] > 0


echo "</table>\n";

echo "</body>\n";

echo "</html>\n";

?>
