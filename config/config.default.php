<?php

require_once("config.estilos.php");

//-------------------- Rutas --------------------
$ruta_buscador="/home/usuario/buscador";
$ruta_libs="$ruta_buscador/libs";
$ruta_bin="$ruta_buscador/bin";
$ruta_crawler="$ruta_buscador/crawler-gobierno";
$ruta_colecta="$ruta_crawler/colecta";
$ruta_indice="$ruta_crawler/indice";
$ruta_logs="$ruta_crawler/logs";
$ruta_respaldos="$ruta_crawler/respaldos";
$ruta_funciones="$ruta_buscador/funciones_php";
$ruta_listas="$ruta_buscador/listas";

//-------------------- Archivos --------------------
$archivo_extensiones="$ruta_crawler/extensiones/lista_extensiones";

//-------------------- Definiciones --------------------
$query_xml="query-xml-1.2";
$minicow="minicow-1.3.82";
$prefijo="gob";

//-------------------- Limites --------------------
$maximo_archivo=1000000;
$maximo_lineas=1000;
$maximo_largo_linea=75;
$min_puerto=3000;
$max_puerto=65000;
$dias_margen=3;

//-------------------- Paginas --------------------
//$home="192.80.24.196:5001";
$home="localhost";
$control_panel="http://$home/control_panel";
$control_servicios="$control_panel/control_servicios.php";
$control_indices="$control_panel/control_indices.php";
$control_colectas="$control_panel/control_colectas.php";
$control_historial="$control_panel/historial.php";
//$control_indices_detalles="$control_panel/control_indices_detalles.php";
$control_indices_detalles="$control_panel/control_indices_detalle.php";
$control_colectas_detalles="$control_panel/control_colectas_detalles.php";
//$control_servicios_detalles="$control_panel/control_servicios_detalles.php";
$control_servicios_detalles="$control_panel/control_servicios.php";
$control_configuracion="$control_panel/control_configuracion.php";
//$control_adicional="$control_panel/control_auditorias.php";
$control_usuarios="$control_panel/control_usuarios.php";
$estilos="http://$home/control_panel_estilos";

//-------------------- Arreglos --------------------
$nombres_mes=array(1=>"Enero",
	2=>"Febrero",
	3=>"Marzo",
	4=>"Abril",
	5=>"Mayo",
	6=>"Junio",
	7=>"Julio",
	8=>"Agosto",
	9=>"Septiembre",
	10=>"Octubre",
	11=>"Noviembre",
	12=>"Diciembre");

$extensiones_colecta=array(".crf0",
	"date",
	".db",
	"geral0",
	"geral.idx",
	"html0",
	"html.idx",
	"id",
	"linkcontent0",
	"linkcontent.idx",
	".log",
	"md5.db",
	"meta0",
	"meta.idx",
	"prio",
	"text0",
	"text.idx");

$extensiones_indice=array("date",
	"feedback",
	"fmax",
	"geral0",
	"geral.idx",
	"html0",
	"html.idx",
	"id",
	"idf",
	"invlist0",
	"invlist.idx",
	"invlistpos0",
	"invlistpos.idx",
	"invlistrespos0",
	"invlistrespos.idx",
	"linkcontent0",
	"linkcontent.idx",
	"mdoclist",
	"meta0",
	"meta.idx",
	"norm",
	"posruninfo",
	"prio",
	"text0",
	"textcomp0",
	"textcomp.idx",
	"text.idx",
	"voc",
	"vocaux");

//-------------------- Base de Datos --------------------
$user="usuario";
$pass="clave";
$host="localhost";
$database="control_panel";
$db = mysql_connect($host, $user, $pass) or die ("No se puede conectar al servidor");
mysql_select_db($database,$db) or die ("La base de datos no puede ser seleccionada");

session_start();
	
?>
