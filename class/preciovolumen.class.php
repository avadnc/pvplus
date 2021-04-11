<?php
/*   Copyright (C) 2015 Alexis José Turruella Sánchez
Desarrollado en el mes de junio de 2015
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia al volumen aplicando descuento
Fichero preciovolumen.class.php
 */

dol_include_once("/pvplus/class/tabla_precios.class.php");
dol_include_once("/pvplus/class/tarifa.class.php");
dol_include_once("/pvplus/class/tabla_producto.class.php");
dol_include_once("/pvplus/class/tabla_producto_cliente.class.php");
dol_include_once("/pvplus/class/tabla_categoria_cliente.class.php");
dol_include_once("/pvplus/class/tabla_categoria_producto.class.php");



/**
 *      \class      Skeleton_class
 *      \brief      Put here description of your class
 *        \remarks    Put here some comments
 */
class Precio_Volumen// extends CommonObject

{
    public $db; //!< To store db handler

    public function Precio_Volumen($DB)
    {
        $this->db = $DB;
        return 1;
    }
    public function Tarifas_Un_Cliente($cliente)
    {
		global $conf;

        $tarifas = array();
        $sql = "SELECT";
        $sql .= " t.rowid";

        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_producto_cliente as t";
		$sql .= " WHERE t.id_cliente=" . $cliente;
		$sql .= " AND t.entity = ". $conf->entity;

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $tarifa = new tabla_producto_cliente($this->db);
                    $tarifa->fetch($obj->rowid);
                    $tarifas[] = $tarifa;
                }
            }
            $this->db->free($resql);

            return $tarifas;
        } else {
            return false;
        }

    }
    /*
    tipo = 1 => Cliente
    tipo = 2 => Producto
     */
    public function Tarifas_Por_Categoria($tipo = 1)
    {
		global $conf;

        $relacion_tabla_categoria = array(0 => 'tabla_categoria_producto', 1 => 'tabla_categoria_cliente');
        $tarifas_asociadas = array();
        $sql = "SELECT";
        $sql .= " t.rowid";
		$sql .= " FROM " . MAIN_DB_PREFIX . $relacion_tabla_categoria[$tipo] . " as t";
		$sql .= " WHERE t.entity = ". $conf->entity;
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    if ($tipo == 1) {
                        $tarifa = new tabla_categoria_cliente($this->db);
                    } else {
                        $tarifa = new tabla_categoria_producto($this->db);
                    }
                    $tarifa->fetch($obj->rowid);
                    $tarifas_asociadas[] = $tarifa;
                }
            }
            $this->db->free($resql);

            return $tarifas_asociadas;
        } else {
            return false;
        }

    }
    public function Categoria_Cliente_Tiene_Tabla_Tarifa_Producto($categoria)
    {
		global $conf;

        $sql = "SELECT";
        $sql .= " t.rowid,t.id_tabla_precio";

        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_categoria_cliente as t";
		$sql .= " WHERE t.id_categoria=" . $categoria;
		$sql .= " AND t.entity = ". $conf->entity;

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $tarifa = new tabla_categoria_cliente($this->db);
                    $tarifa->fetch($obj->rowid);
                    $this->db->free($resql);

                    return $tarifa;
                }
            }
            $this->db->free($resql);

            return false;
        } else {
            return false;
        }

    }
    public function Categoria_Producto_Tiene_Tabla_Tarifa_Producto($categoria)
    {
		global $conf;

        $sql = "SELECT";
        $sql .= " t.rowid,t.id_tabla_precio";

        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_categoria_producto as t";
		$sql .= " WHERE t.id_categoria=" . $categoria;
		$sql .= " AND t.entity = " .$conf->entity;

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $tarifa = new tabla_categoria_producto($this->db);
                    $tarifa->fetch($obj->rowid);
                    $this->db->free($resql);

                    return $tarifa;
                }
            }
            $this->db->free($resql);

            return false;
        } else {
            return false;
        }

    }
    public function Cliente_Tiene_Tabla_Tarifa_Producto($cliente, $id_producto)
    {
        $lista = $this->Tarifas_Un_Cliente($cliente);
        for ($i = 0; $i < sizeof($lista); $i++) {
            if ($lista[$i]->id_producto == $id_producto) {
                return $lista[$i];
            }

        }
        return false;
    }
    public function Obtener_Precio_Cantidad($cliente, $id_producto, $cantidad)
    {

        $oferta = $this->Cliente_Tiene_Tabla_Tarifa_Producto($cliente, $id_producto);

        if ($oferta == false) {
            return false;
        }

        $precios = $this->Detalles_Tarifas($oferta->id_tabla_precio);
        for ($i = 0; $i < sizeof($precios); $i++) {
            if ($precios[$i]->cantidad_inferior <= $cantidad && $precios[$i]->cantidad_superior >= $cantidad) {
                $objTablaPrecio = new tabla_precios($this->db);
                $objTablaPrecio->fetch($oferta->id_tabla_precio);
                return array('descuento' => $precios[$i]->descuento, 'tipo' => $objTablaPrecio->tipo);
            }
        }
        return false;
    }
    //Obtener tabal de precio por categoria de cliente
    public function Obtener_Precio_Cantidad2($cliente, $cantidad)
    {
        require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
        $obj_categorias = new Categorie($this->db);
        $resultado_categorias_cliente = $obj_categorias->containing($cliente, 2);
        $oferta = false;
        foreach ($resultado_categorias_cliente as $cat) {
            $posible_oferta = $this->Categoria_Cliente_Tiene_Tabla_Tarifa_Producto($cat->id);
            if ($posible_oferta != false) {
                $oferta = $posible_oferta;
            }

        }

        if ($oferta == false) {
            return false;
        }

        $precios = $this->Detalles_Tarifas($oferta->id_tabla_precio);
        for ($i = 0; $i < sizeof($precios); $i++) {
            if ($precios[$i]->cantidad_inferior <= $cantidad && $precios[$i]->cantidad_superior >= $cantidad) {
                $objTablaPrecio = new tabla_precios($this->db);
                $objTablaPrecio->fetch($oferta->id_tabla_precio);
                return array('descuento' => $precios[$i]->descuento, 'tipo' => $objTablaPrecio->tipo);
            }
        }
        return false;
    }
    //Obtener tabal de precio por categoria de producto
    public function Obtener_Precio_Cantidad3($producto, $cantidad)
    {
        require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
        $obj_categorias = new Categorie($this->db);
        $resultado_categorias_producto = $obj_categorias->containing($producto, 0);
        $oferta = false;
        foreach ($resultado_categorias_producto as $cat) {
            $posible_oferta = $this->Categoria_Producto_Tiene_Tabla_Tarifa_Producto($cat->id);
            if ($posible_oferta != false) {
                $oferta = $posible_oferta;
            }
        }

        if ($oferta == false) {
            return false;
        }

        $precios = $this->Detalles_Tarifas($oferta->id_tabla_precio);
        for ($i = 0; $i < sizeof($precios); $i++) {
            if ($precios[$i]->cantidad_inferior <= $cantidad && $precios[$i]->cantidad_superior >= $cantidad) {
                $objTablaPrecio = new tabla_precios($this->db);
                $objTablaPrecio->fetch($oferta->id_tabla_precio);
                return array('descuento' => $precios[$i]->descuento, 'tipo' => $objTablaPrecio->tipo);
            }
        }
        return false;
    }
    //---------------------------------------------------------------
    public function Tarifas_Un_Producto($producto)
    {
		global $conf;

        $sql = "SELECT";
        $sql .= " t.rowid,";
        $sql .= " t.id_tabla_precio";

        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_producto_cliente as t";
		$sql .= " WHERE t.id_producto=" . $producto . " AND t.id_cliente is NULL";
		$sql .= " AND t.entity = ". $conf->entity;

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $tarifa = new tabla_producto($this->db);
                $tarifa->fetch($obj->id_tabla_precio, $producto);
                $this->db->free($resql);
                return $tarifa;
            }
            $this->db->free($resql);
            return false;
        } else {
            return false;
        }

    }
    public function Producto_Tiene_Tabla_Tarifa($id_producto)
    {
        $tabla = $this->Tarifas_Un_Producto($id_producto);
        if ($tabla) {
            return $tabla;
        }
        return false;
    }
    public function Obtener_Precio_Cantidad1($id_producto, $cantidad)
    {
        $oferta = $this->Producto_Tiene_Tabla_Tarifa($id_producto);

        if ($oferta == false) {
            return false;
        }

        $precios = $this->Detalles_Tarifas($oferta->id_tabla_precio);
        for ($i = 0; $i < sizeof($precios); $i++) {
            if ($precios[$i]->cantidad_inferior <= $cantidad && $precios[$i]->cantidad_superior >= $cantidad) {
                $objTablaPrecio = new tabla_precios($this->db);
                $objTablaPrecio->fetch($oferta->id_tabla_precio);
                return array('descuento' => $precios[$i]->descuento, 'tipo' => $objTablaPrecio->tipo);
            }
        }
        return false;
    }
    //-----------------------------------------------------------------
    public function Detalles_Tarifas($id_precio_tabla)
    {
		global $conf;

        $detalles = array();
        $sql = "SELECT";
        $sql .= " t.rowid";

        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_rango as t";
		$sql .= " WHERE t.id_tabla_precio=" . $id_precio_tabla;
		$sql .= " AND t.entity = ". $conf->entity;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {

                while ($obj = $this->db->fetch_object($resql)) {
                    $detalle = new Tarifa($this->db);
                    $detalle->fetch($obj->rowid);
                    $detalles[] = $detalle;
                }
            }
            $this->db->free($resql);
            return $detalles;
        }
    }
    public function Tarifas_De_Producto($producto)
    {
		global $conf;

        $tarifas = array();
        $sql = "SELECT";
        $sql .= " t.rowid";

        $sql .= " FROM " . MAIN_DB_PREFIX . "precio_tabla as t";
		$sql .= " WHERE t.id_producto=" . $producto;
        $sql .= " AND t.entity = " . $conf->entity;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {

                while ($obj = $this->db->fetch_object($resql)) {
                    $tarifa = new Tabla_precios($this->db);
                    $tarifa->fetch($obj->rowid);
                    $aux = array();
                    $aux[0] = $tarifa->id;
                    $aux[1] = $tarifa->nombre_tabla;
                    $aux[2] = $tarifa->descripcion;
                    $aux[3] = $tarifa->nombre_producto;
                    $tarifas[] = $aux;
                }
            }
            $this->db->free($resql);

            return $tarifas;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
            return -1;
        }
    }
    public function ProductosSerivicios()
    {
		global $conf;

        $productos = array();
        $sql = "SELECT ";
        $sql .= " t.rowid, ";
        $sql .= " t.label, ";
        $sql .= " t.ref, ";
        $sql .= " t.description ";

		$sql .= " FROM " . MAIN_DB_PREFIX . "product as t";
		$sql .= " AND t.entity = " . $conf->entity;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {

                while ($obj = $this->db->fetch_object($resql)) {
                    $producto = array();
                    $obj->rowid;
                    $producto[0] = $obj->rowid;
                    $producto[1] = $obj->label;
                    $producto[2] = $obj->ref;
                    $producto[3] = $obj->description;
                    $productos[] = $producto;
                }
            }
            $this->db->free($resql);

            return $productos;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
            return -1;
        }
    }
    public function ListaTablas($value = null)
    {
        global $conf;
        $sql = "SELECT";
        $sql .= " t.rowid,";
        $sql .= " t.nombre,";
        $sql .= " t.descripcion,";
        $sql .= " t.tipo";
        $sql .= " FROM " . MAIN_DB_PREFIX . "tabla_precio as t";
		$sql .= " WHERE t.entity = " . $conf->entity;
        if($value != null){
            $sql .= " AND t.nombre = '".$value."'";
        }

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        $aTablas = array();
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $aTablas[] = $obj;
                }
            }
            $this->db->free($resql);
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
        }
        return $aTablas;
    }
    public function ProductosTablas()
    {
		global $conf;

        $productos = array();
        $sql = "SELECT ";
        $sql .= " t.rowid, ";
        $sql .= " t.label, ";
        $sql .= " tpc.id_tabla_precio, ";
        $sql .= " tpc.fecha_creado, ";
        $sql .= " tp.nombre ";

        $sql .= " FROM " . MAIN_DB_PREFIX . "product as t";
        $sql .= " JOIN " . MAIN_DB_PREFIX . "tabla_producto_cliente as tpc ON t.rowid=tpc.id_producto AND tpc.id_cliente is NULL";
		$sql .= " JOIN " . MAIN_DB_PREFIX . "tabla_precio as tp ON tpc.id_tabla_precio=tp.rowid";
		$sql .= " WHERE tpc.entity = ". $conf->entity;
		$sql .= " AND tp.entity = ". $conf->entity;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $producto = array();
                    $obj->rowid;
                    $productos[] = $obj;
                }
            }
            $this->db->free($resql);

            return $productos;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
            return -1;
        }
    }

}
