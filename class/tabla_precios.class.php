<?php
/*   Copyright (C) 2012 Alexis José Turruella Sánchez
     Desarrollado en el mes de octubre de 2012
     Correo electrónico: alexturruella@gmail.com
     Módulo para la gestión del precios del producto en correspondencia al volumen
	 Fichero tabla_precios.class.php
 */

require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
class tabla_precios
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)

    var $id;
    var $nombre;
    var $descripcion;
	var $tipo;

    /**
     *      \brief      Constructor
     *      \param      DB      Database handler
     */
    function tabla_precios($DB)
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

		// Clean parameters
        if (isset($this->nombre)) $this->nombre=trim($this->nombre);
        if (isset($this->descripcion)) $this->descripcion=trim($this->descripcion);
		if (isset($this->tipo)) $this->tipo=trim($this->tipo);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."tabla_precio(";
		$sql.= " entity,";
		$sql.= " nombre,";
		$sql.= " descripcion,";
		$sql.= " tipo";

		$sql.= ") VALUES (";
		$sql.= $conf->entity .",";
        $sql.= " '".$this->nombre."',";
		$sql.= " '".$this->descripcion."',";
		$sql.= " '".$this->tipo."'";

		$sql.= ")";
		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."tabla_precio");

			if (! $notrigger)
			{

			}
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
		$sql.= " t.nombre,";
		$sql.= " t.descripcion,";
		$sql.= " t.tipo";

        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_precio as t";
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
                $this->nombre = $obj->nombre;
                $this->descripcion = $obj->descripcion;
				$this->tipo = $obj->tipo;
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
        if (isset($this->nombre)) $this->nombre=trim($this->nombre);
		if (isset($this->descripcion)) $this->descripcion=trim($this->descripcion);

        $sql = "UPDATE ".MAIN_DB_PREFIX."tabla_precio SET";
        $sql.= " nombre=".(isset($this->nombre)?"'".addslashes($this->nombre)."'":"null").",";
        $sql.= " descripcion=".(isset($this->descripcion)?"'".addslashes($this->descripcion)."'":"null").",";
		$sql.= " tipo=".(isset($this->tipo)?"'".addslashes($this->tipo)."'":"null")."";
		//...
		$sql.= " WHERE rowid=".$this->id;


		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{

	    	}
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
		//verifica si esta siendo usada la tabla en algun cliente
		$sql = "SELECT tpc.rowid FROM ".MAIN_DB_PREFIX."tabla_producto_cliente AS tpc WHERE tpc.id_tabla_precio = ".$this->id;
		$res = $this->db->query($sql);
		if($this->db->num_rows($res) == 0 )
		{

	        $sql1 = "DELETE FROM ".MAIN_DB_PREFIX."tabla_rango";
			$sql1.= " WHERE id_tabla_precio=".$this->id;

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."tabla_precio";
			$sql.= " WHERE rowid=".$this->id;

			$this->db->begin();

			$resql = $this->db->query($sql1);
			if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

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
		else
		{
			return 0;
		}

	}

	function ListaTarifas()
    {
		global $conf;
        $sql = "SELECT";
		$sql.= " tr.rowid,";
		$sql.= " tr.id_tabla_precio,";
		$sql.= " tr.descuento,";
		$sql.= " tr.limite_inferior,";
		$sql.= " tr.limite_superior";
        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_rango as tr";
		$sql.= " WHERE tr.id_tabla_precio='".$this->id."'";
		$sql .= " AND tr.entity = " . $conf->entity;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        $aTarifas= array();
	    if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                while($obj = $this->db->fetch_object($resql))
    			{
                $aTarifas[] = $obj;
				}
            }
            $this->db->free($resql);
            $this->tarifas = $aTarifas;

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }
	function label_tipo_table()
	{
		global $langs;
		switch($this->tipo)
		{
			case "descuento":
			{
				return $langs->trans('PVTABLATIPODESCUENTO');
				break;
			}
			case 'precio':
			{
				return $langs->trans('PVTABLATIPOPRECIO');
				break;
			}
		}
	}
}
?>
