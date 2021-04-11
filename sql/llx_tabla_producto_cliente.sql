CREATE TABLE `llx_tabla_producto_cliente` (
  `rowid` int(11) NOT NULL auto_increment,
  `entity` int(11) default 1 NOT NULL,
  `id_tabla_precio` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_cliente` int(11) default NULL,
  `fecha_creado` date NOT NULL,
  PRIMARY KEY  (`rowid`)
) ENGINE=InnoDB
