CREATE TABLE `llx_tabla_categoria_cliente` (
  `rowid` int(11) NOT NULL auto_increment,
  `entity` int(11) default 1 NOT NULL
  `id_tabla_precio` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  PRIMARY KEY  (`rowid`)
) ENGINE=InnoDB
