<?php
/*   Copyright (C) 2012 Alexis José Turruella Sánchez
     Desarrollado en el mes de octubre de 2012
     Correo electrónico: alexturruella@gmail.com
     Módulo para la gestión del precios del producto en correspondencia al volumen
	 Fichero tarifa.class.php
 */
// Load Dolibarr environment

require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
class Tarifa
{
    var $db;
    var $id;
    var $id_tabla_precio;
    var $descuento;
    var $cantidad_inferior;
	var $cantidad_superior;

    function Tarifa($DB)
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
        if (isset($this->id_tabla_precio)) $this->id_tabla_precio=trim($this->id_tabla_precio);
        if (isset($this->descuento)) $this->descuento=trim($this->descuento);
		if (isset($this->cantidad_inferior)) $this->cantidad_inferior=trim($this->cantidad_inferior);
		if (isset($this->cantidad_superior)) $this->descripcion=trim($this->cantidad_superior);
		//...

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."tabla_rango(";
		$sql.= " entity, ";
		$sql.= " id_tabla_precio,";
		$sql.= " descuento,";
		$sql.= " limite_inferior,";
		$sql.= " limite_superior";

		$sql.= ") VALUES (";
		$sql.= $conf->entity.",";
        $sql.= " '".$this->id_tabla_precio."',";
		$sql.= " '".$this->descuento."',";
        $sql.= " '".$this->cantidad_inferior."',";
	    $sql.= " '".$this->cantidad_superior."'";

		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."tabla_rango");

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
		$sql.= " pi.rowid,";
		$sql.= " pi.id_tabla_precio,";
		$sql.= " pi.descuento,";
		$sql.= " pi.limite_inferior,";
		$sql.= " pi.limite_superior";
        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_rango as pi";
		$sql.= " WHERE pi.rowid = ".$id;
		$sql .= " AND pi.entity = " . $conf->entity;

        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                $this->id_tabla_precio = $obj->id_tabla_precio;
				$this->descuento = price($obj->descuento);
                $this->cantidad_inferior = $obj->limite_inferior;
				$this->cantidad_superior = $obj->limite_superior;

            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
            return -1;
        }
    }

    function update($user=0, $notrigger=0)
    {

    	global $conf, $langs;
		$error=0;

		// Clean parameters
        if (isset($this->descuento)) $this->descuento=trim($this->descuento);
		if (isset($this->cantidad_inferior)) $this->cantidad_inferior=trim($this->cantidad_inferior);
		if (isset($this->cantidad_superior)) $this->cantidad_superior=trim($this->cantidad_superior);

        $sql = "UPDATE ".MAIN_DB_PREFIX."tabla_rango SET";
        $sql.= " descuento=".(isset($this->descuento)?"'".addslashes($this->descuento)."'":"null").",";
		$sql.= " limite_inferior=".(isset($this->cantidad_inferior)?"'".addslashes($this->cantidad_inferior)."'":"null").",";
        $sql.= " limite_superior=".(isset($this->cantidad_superior)?"'".addslashes($this->cantidad_superior)."'":"null")."";
		//...
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

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

	function delete($user, $notrigger=0)
	{
		$error=0;

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."tabla_rango";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

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
	function VerificaLimites($limite_inferior,$limite_superior)
	{
		if($limite_inferior >= $limite_superior)
			return 0;
		else
			return 1;
	}

	function TarifasOrdenadas($id_tabla)
	{
		global $conf;
        $sql = "SELECT";
		$sql.= " tr.rowid, ";
		$sql.= " tr.limite_inferior, ";
		$sql.= " tr.limite_superior ";
        $sql.= " FROM ".MAIN_DB_PREFIX."tabla_rango as tr ";
		$sql.= " WHERE tr.id_tabla_precio='".$id_tabla."' ";
		$sql .= " AND t.entity = " . $conf->entity;
		$sql.= " ORDER BY tr.limite_inferior ASC ";

		$aTarifasLimites = array();
    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
	    if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                while($obj = $this->db->fetch_object($resql))
    			{
                    $aTarifasLimites[] = $obj;
				}
            }
			$this->db->free($resql);
		}
		return $aTarifasLimites;
	}


	function VerificaRangoLimites($aTarifasLimites,$limite_inferior,$limite_superior,$caso=NULL,$idTarifa=NULL)
	{
	$flag = false;
	      	if(	sizeof($aTarifasLimites) > 0)
			{
				if( $limite_inferior >  $aTarifasLimites[sizeof($aTarifasLimites)-1]->limite_superior )/*elnuevo rango se agrega al final*/
				{		$flag = true;	}
				else if( $limite_superior <  $aTarifasLimites[0]->limite_inferior )/*elnuevo rango se agrega al inicio*/
				{		$flag = true;}
				else if( $idTarifa == $aTarifasLimites[0]->rowid && $limite_superior < $aTarifasLimites[1]->limite_inferior)/*estoy editando el 1ro*/
				{		$flag = true;}
				else if( $idTarifa == $aTarifasLimites[sizeof($aTarifasLimites)-1]->rowid && $limite_inferior > $aTarifasLimites[sizeof($aTarifasLimites)-2]->limite_superior)/*last one*/
				{		$flag = true;}
				else
				{
					$flag = false;
					for( $i = 0 ; $i < sizeof($aTarifasLimites)-1 ; $i++ )
					{
						if($caso == "edicion" && $idTarifa == $aTarifasLimites[$i+1]->rowid)// el que viene es el que estoy editando
						{
							 if( $limite_inferior  > $aTarifasLimites[$i]->limite_superior && $aTarifasLimites[$i+2]->limite_inferior != NULL)
						     {
								if($limite_superior < $aTarifasLimites[$i+2]->limite_inferior)
							     { $flag = true;break;}

						     }
						}
						if( $limite_inferior  > $aTarifasLimites[$i]->limite_superior && $limite_superior < $aTarifasLimites[$i+1]->cantidad_inferior)/*solo ocurre una vez*/
						{
							$flag = true;
							break;
						}
					}
				}
			}
			else
			{
			$flag = true;
			//no hay tarifas devuelvo true
			}
		return $flag;
	}

	function VerificaVariasTarifas($tarifasOrdenadas)
	{
		$flag =  true;
		//----- ordenamiento
		for( $i=0  ; $i<sizeof($tarifasOrdenadas)-1 ; $i++ )
		for( $j=$i  ; $j<sizeof($tarifasOrdenadas) ; $j++ )
		{
		if($tarifasOrdenadas[$i]["inferior"] > $tarifasOrdenadas[$j]["inferior"] )
		{
			$temp = $tarifasOrdenadas[$i] ;
			$tarifasOrdenadas[$i] = $tarifasOrdenadas[$j];
			$tarifasOrdenadas[$j] = $temp;
		}
		}//-------------------------------
		for( $i=0  ; $i<sizeof($tarifasOrdenadas)-1 ; $i++ )
		{
			if( $tarifasOrdenadas[$i]["inferior"] > $tarifasOrdenadas[$i]["superior"] || $tarifasOrdenadas[$i]["superior"] >= $tarifasOrdenadas[$i+1]["inferior"])
			{
				$flag =  false;break;
			}
		}
		return $flag;
	}
}
?>
