<?php
/* Copyright (C) 2015 Alexis José Turruella Sánchez  <alexturruella@gmail.com>

 */

class tabla_categoria_producto // extends CommonObject
{
	var $db;
	var $error;
	var $errors=array();

    var $id;

	var $id_tabla_precio;
	var $id_categoria;



    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }
    function create($user, $notrigger=0)
    {
		if($this->existe_asociada_categoria()==true)
		  return false;
    	global $conf, $langs;
		$error=0;

		if (isset($this->id_tabla_precio)) $this->id_tabla_precio=trim($this->id_tabla_precio);
		if (isset($this->id_categoria)) $this->id_categoria=trim($this->id_categoria);


		$sql = "INSERT INTO ".MAIN_DB_PREFIX."tabla_categoria_producto(";

		$sql.= "entity,";
		$sql.= "id_tabla_precio,";
		$sql.= "id_categoria";

		$sql.= ") VALUES (";

        $sql.= " ". $conf->entity.",";
		$sql.= " ".(! isset($this->id_tabla_precio)?'NULL':"'".$this->id_tabla_precio."'").",";
		$sql.= " ".(! isset($this->id_categoria)?'NULL':"'".$this->id_categoria."'")."";


		$sql.= ")";

		$this->db->begin();


        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."tabla_categoria_cliente");

			if (! $notrigger)
			{

			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{

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

    function fetch($id)
    {
    	global $conf;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.id_tabla_precio,";
		$sql.= " t.id_categoria";


        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_categoria_producto as t";
		$sql.= " WHERE t.rowid = ".$id;
		$sql.= " AND t.entity = " . $conf->entity;


        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->id_tabla_precio = $obj->id_tabla_precio;
				$this->id_categoria = $obj->id_categoria;
                $this->db->free($resql);
                return true;
            }
            $this->db->free($resql);
            return false;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            return false;
        }
    }
	function existe_asociada_categoria()
    {
    	global $conf;
        $sql = "SELECT";
		$sql.= " t.rowid";
        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_categoria_producto as t";
		$sql.= " WHERE t.id_categoria = ".$this->id_categoria;
		$sql .= " AND t.entity = " . $conf->entity;



        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                return $obj->rowid;
            }
            $this->db->free($resql);
            return false;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            return false;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modify
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf;
		$error=0;

		// Clean parameters

		if (isset($this->id_tabla_precio)) $this->id_tabla_precio=trim($this->id_tabla_precio);
		if (isset($this->id_categoria)) $this->id_categoria=trim($this->id_categoria);



		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."tabla_categoria_producto SET";

		$sql.= " id_tabla_precio=".(isset($this->id_tabla_precio)?$this->id_tabla_precio:"null").",";
		$sql.= " id_categoria=".(isset($this->id_categoria)?$this->id_categoria:"null")."";


		$sql.= " WHERE rowid=".$this->id;


		$this->db->begin();

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
	 *  Delete object in database
	 *
     *	@param  User	$user        User that delete
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{

			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."tabla_categoria_producto";
    		$sql.= " WHERE rowid=".$this->id;

    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{

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

	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Tablacategoriacliente($this->db);

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

	function initAsSpecimen()
	{
		$this->id=0;

		$this->id_tabla_precio='';
		$this->id_categoria='';


	}
}
?>
