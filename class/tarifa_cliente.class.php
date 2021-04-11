<?php
/*   Copyright (C) 2012 Alexis José Turruella Sánchez
     Desarrollado en el mes de octubre de 2012
     Correo electrónico: alexturruella@gmail.com
     Módulo para la gestión del precios del producto en correspondencia al volumen
	 Fichero tarifa_cliente.class.php
 */

class Tarifa_Cliente // extends CommonObject
{
	var $db;

    var $id;
    var $id_cliente;
	var $id_precio_tabla;
    var $fecha_creado;
	var $nombre_tabla;
	var $descripcion;
	var $label;
	var $price;
	var $id_producto;

	var $tarifas;

    function Tarifa_Cliente($DB)
    {
        $this->db = $DB;
        return 1;
    }


    /**
     *      \brief      Create in database
     *      \param      user        	User that create
     *      \param      notrigger	    0=launch triggers after, 1=disable triggers
     *      \return     int         	<0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

        if (isset($this->id_cliente)) $this->id_cliente=trim($this->id_cliente);
        if (isset($this->id_precio_tabla)) $this->id_precio_tabla=trim($this->id_precio_tabla);
		if (isset($this->fecha_creado)) $this->fecha_creado=trim($this->fecha_creado);

		//Aqui voy a chequear que no debe existir para un mismo cliente ya un id_precio_tabla

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."precio_tabla_cliente(";
		$sql.= " entity, ";
		$sql.= " id_cliente,";
		$sql.= " id_precio_tabla,";
		$sql.= " fecha_creado";
		//...
		$sql.= ") VALUES (";
		$sql.= $conf->entity.",";
        $sql.= " '".$this->id_cliente."',";
		$sql.= " '".$this->id_precio_tabla."',";
        $sql.= " '".$this->fecha_creado."'";
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."precio_tabla_cliente");
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
    function fetch($id)
    {
    	global $conf;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.id_cliente,";
		$sql.= " t.id_precio_tabla,";
		$sql.= " t.fecha_creado,";
		$sql.= " pt.nombre_tabla,";
		$sql.= " pt.descripcion,";
		$sql.= " p.label, ";
		$sql.= " p.price, ";
		$sql.= " pt.id_producto";
		//...
        $sql.= " FROM ".MAIN_DB_PREFIX."precio_tabla_cliente as t";
		$sql.= " JOIN ".MAIN_DB_PREFIX."precio_tabla as pt ON t.id_precio_tabla=pt.rowid ";
		$sql.= " JOIN ".MAIN_DB_PREFIX."product as p ON pt.id_producto=p.rowid ";
		$sql.= " WHERE t.rowid = ".$id;
		$sql .= " AND t.entity = " . $conf->entity;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                $this->id_cliente = $obj->id_cliente;
                $this->id_precio_tabla = $obj->id_precio_tabla;
				$this->fecha_creado = $obj->fecha_creado;
				$this->nombre_tabla = $obj->nombre_tabla;
				$this->descripcion = $obj->descripcion;
				$this->label = $obj->label;
				$this->price = $obj->price;
				$this->id_producto = $obj->id_producto;
				//...
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *      \brief      Update database
     *      \param      user        	User that modify
     *      \param      notrigger	    0=launch triggers after, 1=disable triggers
     *      \return     int         	<0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        if (isset($this->id_precio_tabla)) $this->id_precio_tabla=trim($this->id_precio_tabla);
        if (isset($this->fecha_creado)) $this->fecha_creado=trim($this->fecha_creado);

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."precio_tabla_cliente SET";
        $sql.= " id_precio_tabla=".(isset($this->id_precio_tabla)?"'".addslashes($this->id_precio_tabla)."'":"null").",";
        $sql.= " fecha_creado=".(isset($this->fecha_creado)?"'".addslashes($this->fecha_creado)."'":"null")."";
		//...
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{

		}
        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
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


 	/**
	 *   \brief      Delete object in database
     *	\param      user        	User that delete
     *   \param      notrigger	    0=launch triggers after, 1=disable triggers
	 *	\return		int				<0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."precio_tabla_cliente";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{

		}

        // Commit or rollback
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



	/**
	 *		\brief      Load an object from its id and create a new one in database
	 *		\param      fromid     		Id of object to clone
	 * 	 	\return		int				New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Skeleton_class($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{

		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *		\brief		Initialise object with example values
	 *		\remarks	id must be 0 if object instance is a specimen.
	 */
	function initAsSpecimen()
	{
		$this->id=0;
	}

}
?>
