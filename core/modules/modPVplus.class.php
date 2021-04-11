<?php
/*   Copyright (C) 2015 Alexis José Turruella Sánchez
     Desarrollado en el 2015
     Correo electrónico: alexturruella@gmail.com 
     Módulo para la gestión del precios del producto en correspondencia al volumen
	 Cuarta versión del módulo compatible con la versión 3.6 y 3.7 de dolibarr
	 Fichero modPrecioVolumen.class.php

	Update 2021 José Armando Machuca 
	Correo electrónico: sistemas@machfree.com
	web: machfee.com

 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");

/**
 * 		\class      modMyModule
 *      \brief      Description and activation class for module MyModule
 */
class modPVplus extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function modPVplus($DB)
	{
		$this->db = $DB;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 10012;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'preciovolumen';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "products";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Permite la gesti&oacute;n de descuentos por volumen de compra de los clientes";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '11.0.1';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='pvplus.png@pvplus';

		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		
		$this->module_parts = array(
			'triggers' => 1,
			'js'=>'/pvplus/js/jquery.dataTables.min.js',
			'css' => '/pvplus/css/jquery.dataTables.min.css'
		);
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
		//$this->style_sheet = '/mymodule/mymodule.css.php';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array('acercade.php@pvplus');

		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array("modSociete","modProduct","modCategorie");
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,6);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("preciovolumen@pvplus","categories");

		// Constants
		
		$this->const = array();			// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 0 or 'allentities')

		// Array to add new pages in new tabs
		$this->tabs = array('product:+PrecioVolumen:PrecioVolumen:@pvplus:/pvplus/tabla_producto.php?id=__ID__','thirdparty:+PrecioVolumenCliente:PrecioVolumenCliente:@pvplus:/pvplus/tabla_producto_cliente.php?socid=__ID__');


		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

	    $this->rights[$r][0] = 12901;		
		$this->rights[$r][1] = 'Admin tablas descuentos';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'admintablasdescuentos';
		$r++;
		$this->rights[$r][0] = 12902;		
		$this->rights[$r][1] = 'Aplicar tabla a producto';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'aplicartablaproducto';
		$r++;
		$this->rights[$r][0] = 12903;		
		$this->rights[$r][1] = 'Aplicar tabla a cliente';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'aplicartablasprodclientes';
		$r++;
		$this->rights[$r][0] = 12904;		
		$this->rights[$r][1] = 'Aplicar tabla a categoría de clientes';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'aplicartablascategoriacliente';
		$r++;
		$this->rights[$r][0] = 12905;		
		$this->rights[$r][1] = 'Aplicar tabla a categoría de productos';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'aplicartablascategoriaproducto';

		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;

		$this->menu[$r]=array('fk_menu'=>0,
													'type'=>'top',
													'titre'=>'PV',
													'mainmenu'=>'pvplus',
													'leftmenu'=>'0',
													'url'=>'/pvplus/index.php',
													'langs'=>'preciovolumen@pvplus',
													'position'=>100,
													'perms'=>'$user->rights->preciovolumen->admintablasdescuentos',
													'enabled'=>'$conf->pvplus->enabled',
													'target'=>'',
													'user'=>0
													);
			$r++;
			$this->menu[$r]=array(	'fk_menu'=>'r=0',		// Use r=value where r is index key used for the parent menu entry (higher parent must be a top menu entry)
			'type'=>'left',			// This is a Left menu entry
			'titre'=>'PVGESTION',
			'mainmenu'=>'pvplus',
			'url'=>'/pvplus/gestionar_tablas.php',
			'langs'=>'preciovolumen@pvplus',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->pvplus->enabled',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>0);				// 0=Menu for internal users,1=external users, 2=both
			$r++;
			$this->menu[$r]=array(	'fk_menu'=>'r=1',		// Use r=value where r is index key used for the parent menu entry (higher parent must be a top menu entry)
			'type'=>'left',			// This is a Left menu entry
			'titre'=>'AddRemise',
			'mainmenu'=>'pvplus',
			'url'=>'/pvplus/gestionar_tablas.php',
			'langs'=>'categories',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->pvplus->enabled',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->preciovolumen->aplicartablascategoriacliente',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>0);				// 0=Menu for internal users,1=external users, 2=both
			$r++;
			$this->menu[$r]=array(	'fk_menu'=>'r=1',		// Use r=value where r is index key used for the parent menu entry (higher parent must be a top menu entry)
			'type'=>'left',			// This is a Left menu entry
			'titre'=>'CustomersCategoriesShort',
			'mainmenu'=>'pvplus',
			'url'=>'/pvplus/tabla_categoria_cliente.php',
			'langs'=>'categories',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->pvplus->enabled',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->preciovolumen->aplicartablascategoriacliente',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>0);				// 0=Menu for internal users,1=external users, 2=both
			$r++;
			$this->menu[$r]=array(	'fk_menu'=>'r=1',		// Use r=value where r is index key used for the parent menu entry (higher parent must be a top menu entry)
			'type'=>'left',			// This is a Left menu entry
			'titre'=>'ProductsCategoryShort',
			'mainmenu'=>'pvplus',
			'url'=>'/pvplus/tabla_categoria_producto.php',
			'langs'=>'categories',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->pvplus->enabled',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->preciovolumen->aplicartablascategoriaproducto',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>0);				// 0=Menu for internal users,1=external users, 2=both
		
	}


	/**
	 *		\brief      Function called when module is enabled.
	 *					The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *					It also creates data directories.
	 *      \return     int             1 if OK, 0 if KO
	 */
	function init($options = '')
	{
		$sql = array();

		$result=$this->load_tables();

		return $this->_init($sql);
	}

	/**
	 *		\brief		Function called when module is disabled.
	 *              	Remove from database constants, boxes and permissions from Dolibarr database.
	 *					Data directories are not deleted.
	 *      \return     int             1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables($options = '')
	{
		return $this->_load_tables('/pvplus/sql/');
	}
}
?>