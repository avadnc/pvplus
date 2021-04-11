CREATE TABLE `llx_tabla_precio` (
  `rowid` int(11) NOT NULL auto_increment,
  `entity` int(11) default 1 NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` text,
  `tipo` varchar(10) NOT NULL,
  PRIMARY KEY  (`rowid`)
) ENGINE=InnoDB
