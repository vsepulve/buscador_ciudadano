# buscador_ciudadano
Repositorio para el codigo base y binarios del proyecto Buscador Ciudadano.

Buscador - Readme

En este archivo se explica como se debe instalar y configurar el sistema. En el archivo "componentes.txt" se describen los distintos directorios incluidos en este paquete.

Requisitos:

- Servidor LAMP (Linux, Apache, Mysql, y PHP 5) 
- El Crawler requiere versiones actualizadas de:
	- pdftotext (de xpdf-utils)
	- catdoc
	- xls2csv
	- ppthtml

Instalacion

1.- Preparar Mysql creando una base de datos con el esquema de "sql/estructura.sql".

2.- Ubicar en un mismo directorio, los siguientes componentes:
	- bin/
	- config/
	- crawler-ciudadano/
	- funciones_php/
	- libs/
	- public_html/
	
3.- Editar el archivo "config/config.default.php" para ajustar los siguientes datos:
	- ruta_buscador: Contiene la ruta donde se ubican los directorios anteriores.
	- home: la URL del buscador.
	- $user, $pass, $host, $database: Datos necesarios para establecer una conexion con la base de datos creada en el punto 1.

4.- Editar los archivos "config.ini" de los distintos modulos (como "light" o "control_panel") para que la ruta a "config.default.php" sea correcta.

5.- Configurar Apache para permitir el acceso a "public_html" como home. De este modo, los componentes "control_panel" y "light" son accesibles y el sistema puede ser utilizado por primera vez.

6.- Antes de usar el panel de control, debe crearse un usuario inicial. Para ello, ingresar por linea de comando a mysql y en la base de datos creada para el panel de control, ejecutar la siguiente instruccion:
	mysql> insert into usuarios (nombre_usuario, clave, rol) values ('administrador', '9dbf7c1488382487931d10235fc84a74bff5d2f4', 2);
	Con esto, se crea un usuario inicial con nombre de usuario y clave "administrador". Posteriormente, pueden crearse y eliminarse usuarios desde (url del home)/control_panel/control_usuarios.php
	
7.- Por ultimo, el usuario de apache (normalemente "www-data") debe poder acceder para lectura y escritura a los siguientes directorios:
	- bin/ 
	- crawler-ciudadano/ y sus dependencias


