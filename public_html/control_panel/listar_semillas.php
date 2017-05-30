<?php

require_once("config.ini");

require_once("sesion.php");

echo "<html>";
html_head("Administracion de Usuarios");
echo "<body>";
html_menu_superior(4);
html_inicio_mensajes();

$debug=false;
$estructura=obtener_estructura($db);
agregar_puertos($db, $estructura);
$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos); $i++){
	print_debug("Grupo ".$grupos[$i]["id"].": ".$grupos[$i]["nombre"]." (".$grupos[$i]["puerto"].")");
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		print_debug("--> Dominio ".$dominios[$j]["id"].": ".$dominios[$j]["nombre"]." (".$dominios[$j]["puerto"].")");
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			print_debug("---- Semilla ".$semillas[$k]["id"].": ".$semillas[$k]["url"]);
		}
	}
}//for... cada grupo
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

//cuando no colecta nada, deja cerca de 16.2 kilos...
$tamaño_vacio=17*1024;

mysql_close($db);
html_fin_mensajes();

$titulo="Listar Semillas";
$opciones="Opciones Adicionales";
$elementos=array();

$elemento["nombre"]="Usuarios";
$elemento["ruta"]="control_usuarios.php";
$elemento["principal"]=true;
$elementos[]=$elemento;

$elemento["nombre"]="Verificar Semillas";
$elemento["ruta"]="verificar_semillas.php";
$elemento["principal"]=false;
$elementos[]=$elemento;

html_menu_izquierdo($titulo, $opciones, $elementos);


$grupos=$estructura["grupos"];
for($i=0; $i<count($grupos) ; $i++){
	echo "<h3>Grupo \"".$grupos[$i]["nombre"]."\"</h3>\n";
	$dominios=$estructura["dominios"][$i];
	for($j=0; $j<count($dominios); $j++){
		echo "<b>".$dominios[$j]["nombre"]."</b><br>\n";
		$semillas=$estructura["semillas"][$i][$j];
		for($k=0; $k<count($semillas); $k++){
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<a href=".$semillas[$k]["url"].">";
			echo $semillas[$k]["url"];
			echo "</a>";
			if($tamaño_colecta_semilla[$semillas[$k]["id"]]<$tamaño_vacio){
				echo "&nbsp;<span class=texto_rojo>";
				echo "(colecta vacia)";
				echo "</span>";
			}
			else if($tamaño_colecta_semilla[$semillas[$k]["id"]]<$tamaño_minimo){
				echo "&nbsp;<span class=texto_amarillo>";
				echo "(colecta sospechosa, ".texto_tamaño($tamaño_colecta_semilla[$semillas[$k]["id"]]).")";
				echo "</span>";
			}
			else{
			}
			echo "<br>\n";
		}
		echo "<br>\n";
	}
	echo "<hr>\n";
}//for... cada grupo



echo "</body>\n";

echo "</html>\n";

?>
