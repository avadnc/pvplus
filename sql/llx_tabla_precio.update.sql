ALTER TABLE llx_tabla_precio ADD tipo varchar(10) DEFAULT 'descuento' AFTER descripcion;
ALTER TABLE llx_tabla_categoria_cliente ADD entity int(11) DEFAULT 1 AFTER rowid;
ALTER TABLE llx_tabla_categoria_producto ADD entity int(11) DEFAULT 1 AFTER rowid;
ALTER TABLE llx_tabla_precio ADD entity int(11) DEFAULT 1 AFTER rowid;
ALTER TABLE llx_tabla_producto_cliente ADD entity int(11) DEFAULT 1 AFTER rowid;
ALTER TABLE llx_tabla_rango ADD entity int(11) DEFAULT 1 AFTER rowid;

