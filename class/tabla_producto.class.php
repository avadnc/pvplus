<?php
/*   Copyright (C) 2012 Alexis José Turruella Sánchez
     Desarrollado en el mes de octubre de 2012
     Correo electrónico: alexturruella@gmail.com
     Módulo para la gestión del precios del producto en correspondencia al volumen
	 Fichero tabla_producto.class.php
 */

class tabla_producto
{
	var $db;

    var $id;
    var $id_tabla_precio;
	var $id_producto;
	var $fecha_creado;

	var $tarifas;

    function __construct($DB)
    {
        $this->db = $DB;
    }
    function create($user)
    {
		global $conf;

		$error=0;

        if (isset($this->id_tabla_precio)) $this->id_tabla_precio=trim($this->id_tabla_precio);
        if (isset($this->id_producto)) $this->id_producto=trim($this->id_producto);
		if (isset($this->fecha_creado)) $this->fecha_creado=trim($this->fecha_creado);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."tabla_producto_cliente(";
		$sql.= " entity,";
		$sql.= " id_tabla_precio,";
		$sql.= " id_producto,";
		$sql.= " fecha_creado";
		//...
		$sql.= ") VALUES (";
		$sql.= $conf->entity.",";
        $sql.= " '".$this->id_tabla_precio."',";
		$sql.= " '".$this->id_producto."',";
        $sql.= " '".$this->fecha_creado."'";
		$sql.= ")";


		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."tabla_producto_cliente");
        }
        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }

    /**
     *    \brief      Load object in memory from database
     *    \param      id          id object
     *    \return     int         <0 if KO, >0 if OK
     */
    function fetch($id_tabla,$id_producto)
    {
    	global $conf;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.id_tabla_precio,";
		$sql.= " t.id_producto,";
		$sql.= " t.fecha_creado";

        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_producto_cliente as t";

		$sql.= " WHERE t.id_tabla_precio = ".$id_tabla." AND t.id_producto=".$id_producto." AND t.id_cliente is NULL";
		$sql .= " AND t.entity = " . $conf->entity;

        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                $this->id_tabla_precio = $obj->id_tabla_precio;
                $this->id_producto = $obj->id_producto;
				$this->fecha_creado = $obj->fecha_creado;
				$this->db->free($resql);
				return true;
            }
            $this->db->free($resql);
            return false;
        }
        else
        {
            return false;
        }
    }

	function delete($user)
	{
		$error=0;

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."tabla_producto_cliente";
		$sql.= " WHERE id_producto=".$this->id_producto." AND id_cliente is NULL";

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}
}
?>
