-- phpMyAdmin SQL Dump
-- version 3.1.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 03-12-2009 a las 14:10:40
-- Versión del servidor: 5.0.51
-- Versión de PHP: 5.2.4-2ubuntu5.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `control_panel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_consultas`
--

CREATE TABLE IF NOT EXISTS `cache_consultas` (
  `consulta` varchar(100) NOT NULL default '',
  `id_grupo` int(11) NOT NULL default '0',
  `numero_resultados` int(11) default NULL,
  PRIMARY KEY  (`consulta`,`id_grupo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_enlaces`
--

CREATE TABLE IF NOT EXISTS `cache_enlaces` (
  `id` int(11) NOT NULL auto_increment,
  `id_semilla` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10975 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dominios`
--

CREATE TABLE IF NOT EXISTS `dominios` (
  `id` int(11) NOT NULL auto_increment,
  `nombre` varchar(50) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `borrado` int(1) NOT NULL default '0',
  `reject` varchar(100) default NULL,
  `monitoreable` int(1) NOT NULL default '0' COMMENT 'Marca los indices que se monitorean',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Uno de los dominios, por ejemplo "Ministerio de Salud".' AUTO_INCREMENT=928 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dominios_enlaces`
--

CREATE TABLE IF NOT EXISTS `dominios_enlaces` (
  `id` int(11) NOT NULL auto_increment,
  `id_dominio` int(11) NOT NULL,
  `id_enlace` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=847 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enlaces`
--

CREATE TABLE IF NOT EXISTS `enlaces` (
  `id` int(11) NOT NULL auto_increment,
  `nombre` varchar(100) NOT NULL,
  `publico` int(1) NOT NULL default '1',
  `acronimo` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=546 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadisticas`
--

CREATE TABLE IF NOT EXISTS `estadisticas` (
  `id_grupo` int(11) NOT NULL,
  `fecha_colecta` timestamp NULL default NULL,
  `bytes_colecta` bigint(20) default NULL,
  `fecha_indice` timestamp NULL default NULL,
  `numero_documentos` int(11) default NULL,
  PRIMARY KEY  (`id_grupo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

CREATE TABLE IF NOT EXISTS `grupos` (
  `id` int(11) NOT NULL auto_increment,
  `nombre` varchar(50) NOT NULL,
  `borrado` int(1) NOT NULL default '0',
  `auditable` int(1) NOT NULL default '0',
  `reject` varchar(100) default NULL,
  `monitoreable` int(1) NOT NULL default '0' COMMENT 'Marca los indices que se monitorean',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Uno de los grupos, como por ejemplo "Sitios de Gobierno".' AUTO_INCREMENT=72 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_creacion_indice`
--

CREATE TABLE IF NOT EXISTS `log_creacion_indice` (
  `id` int(11) NOT NULL auto_increment,
  `tipo_indice` varchar(1) NOT NULL,
  `id_indice` int(11) NOT NULL,
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=188 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_inicio_colecta`
--

CREATE TABLE IF NOT EXISTS `log_inicio_colecta` (
  `id` int(11) NOT NULL auto_increment,
  `tipo_colecta` varchar(1) NOT NULL COMMENT 'S/D/G',
  `id_colecta` int(11) NOT NULL COMMENT 'id de Semilla/Dominio/Grupo',
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='log de la accion de inicio de colecta' AUTO_INCREMENT=287 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_semillas_agrupadas`
--

CREATE TABLE IF NOT EXISTS `log_semillas_agrupadas` (
  `id` int(11) NOT NULL auto_increment,
  `id_semilla` int(11) NOT NULL,
  `id_creacion_indice` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10568 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_semillas_colectadas`
--

CREATE TABLE IF NOT EXISTS `log_semillas_colectadas` (
  `id` int(11) NOT NULL auto_increment,
  `id_semilla` int(11) NOT NULL,
  `id_inicio_colecta` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11006 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina`
--

CREATE TABLE IF NOT EXISTS `maquina` (
  `id` int(11) NOT NULL auto_increment,
  `ip1` int(3) NOT NULL,
  `ip2` int(3) NOT NULL,
  `ip3` int(3) NOT NULL,
  `ip4` int(3) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puertos_indice`
--

CREATE TABLE IF NOT EXISTS `puertos_indice` (
  `tipo` char(1) NOT NULL default 'D' COMMENT 'G (grupo) / D (dominio)',
  `id` int(11) NOT NULL COMMENT 'id_grupo / id_dominio',
  `puerto` int(5) NOT NULL,
  `id_maquina` int(11) NOT NULL default '1',
  `refinamiento` varchar(100) default NULL,
  PRIMARY KEY  (`tipo`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `semillas`
--

CREATE TABLE IF NOT EXISTS `semillas` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) NOT NULL,
  `reject` varchar(100) default NULL,
  `id_dominio` int(11) NOT NULL,
  `borrado` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Semilla de un dominio, por ejemplo "http://www.minsal.cl/"' AUTO_INCREMENT=1890 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `nombre_usuario` varchar(20) NOT NULL,
  `clave` varchar(40) NOT NULL,
  `rol` int(11) NOT NULL,
  `ultimo_login` timestamp NULL default NULL,
  PRIMARY KEY  (`nombre_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

