CREATE TABLE `llx_tabla_rango` (
  `rowid` int(11) NOT NULL auto_increment,
  `entity` int(11) default 1 NOT NULL,
  `id_tabla_precio` int(11) NOT NULL,
  `descuento` float(9,4) default NULL,
  `limite_inferior` int(11) NOT NULL,
  `limite_superior` int(11) NOT NULL,
  PRIMARY KEY  (`rowid`)
) ENGINE=InnoDB
