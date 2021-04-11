<?php
/*   Copyright (C) 2015 - 2016 Alexis José Turruella Sánchez
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia a la cantidad de compra
Fichero tabla_categoria_cliente.php.php
 */
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}

// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {$i--;
    $j--;}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}

if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}

// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
    $res = @include "../main.inc.php";
}

if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}

if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}

if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . "/core/lib/product.lib.php";
require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/html.formfile.class.php";
dol_include_once("/pvplus/class/preciovolumen.class.php");

require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
include_once DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php";

$langs->load("products");
$langs->load("categories");
$langs->load("errors");
$langs->load("preciovolumen@pvplus");

// Security check
if (!$user->rights->preciovolumen->aplicartablascategoriacliente) 
{
    accessforbidden();
}

$mesg = '';
$error = 0;
$errors = array();
/*
 * Actions
 */

if ($_POST['accion'] == 'aplicartablaacategoriacliente') 
{
    $nueva_asociacion = new tabla_categoria_cliente($db);
    $nueva_asociacion->id_categoria = $_POST['categoria'];
    $nueva_asociacion->id_tabla_precio = $_POST['tabla_precio'];
    if ($nueva_asociacion->create($user)) 
    {
        $mesg = '<div class="ok">' . $langs->trans("WasAddedSuccessfully") . '</div>';
    } 
    else 
    {
        $error = 0;
        $errores[] = $langs->trans("ErrorRecordAlreadyExists");
    }
}
if ($_GET['accion'] == 'del') 
{
    $del_asociacion = new tabla_categoria_cliente($db);
    if ($del_asociacion->fetch($_GET['id_asociacion'])) 
    {
        if ($del_asociacion->delete($user)) {
            $mesg = '<div class="ok">' . $langs->trans("RecordModifiedSuccessfully") . '</div>';
        } else {
            $error = 0;
            $errores[] = $langs->trans("ErrorBadParameters") . '<br/>' . $langs->trans('ErrorRecordNotFound');
        }
    } else {
        $error = 0;
        $errores[] = $langs->trans("ErrorBadParameters") . '<br/>' . $langs->trans('ErrorRecordNotFound');
    }
}

$objPrecioVol = new Precio_Volumen($db);
$lista_tablas_precios = $objPrecioVol->ListaTablas();
$lista_categorias_asociadas = $objPrecioVol->Tarifas_Por_Categoria();

$obj_categorias = new Categorie($db);
$lista_categorias_clientes = $obj_categorias->get_main_categories(2);

$html = new Form($db);
$html = new Form($db);
if (file_exists("/pvplus/js/preciovolumen.js")) {
    $morejs = array("/pvplus/js/preciovolumen.js");

} else {
    $morejs = array("/custom/pvplus/js/preciovolumen.js");

}

llxHeader('', $langs->trans('PVCategoriaCliente'), $langs->trans("PVCategoriaCliente"), '', '', '', $morejs, '', 0, 0);

$aTablasPrecios = $objPrecioVol->ListaTablas();
$aProductosTablas = $objPrecioVol->ProductosTablas();

load_fiche_titre($langs->trans("CustomersCategoriesArea"));

if ($conf->categorie->enabled) {
    print '<table border="0" width="100%" class="notopnoleftnoright">';

    print '<tr><td valign="top" class="notopnoleft">';
    if (sizeof($lista_categorias_asociadas) > 0) {
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre"><td>' . $langs->trans("Category") . '</td>';
        print '<td>' . $langs->trans('PVGESTION') . '</td>';
        print '<td>' . $langs->trans('Action') . '</td>';
        print '</tr>';
        foreach ($lista_categorias_asociadas as $asociacion) {
            $var = !$var;
            $cat_aux = new Categorie($db);
            $cat_aux->fetch($asociacion->id_categoria);
            $tarifa_aux = new Tabla_precios($db);
            $tarifa_aux->fetch($asociacion->id_tabla_precio);
            print "<tr $bc[$var]>";
            print '<td><b title="' . $cat_aux->description . '">' . $cat_aux->label . '</b></td>';
            print '<td>';
            print $tarifa_aux->nombre;
            print '</td>';
            print '<td>';
            print '<label style="cursor:pointer" onclick="DetallesTabla(' . $asociacion->id_tabla_precio . ')">';
            print img_picto($langs->trans('PVTABLASTARIFAS'), 'detail');
            print '</label> ';
            print '<a href="tabla_categoria_cliente.php?accion=del&id_asociacion=' . $asociacion->id . '" style="padding-left:1px" >' . img_picto($langs->trans("PVDELTABLA"), "delete") . '</a>';
            print '</td>';
            print '</tr>';
        }
        print '</table>';
    }

    //El formulario de categorais y clientes para asociar
    print '<br/>';
    print "<form method='post' action='tabla_categoria_cliente.php' name='nuevo'>";
    print '<input type="hidden" name="accion" value="aplicartablaacategoriacliente"/>';
    print '<table  width="50%" class="noborder">';
    print '<tr class="liste_titre"><td colspan="3">' . img_picto($langs->trans("New"), "filenew") . ' ' . $langs->trans("New") . '</td></tr>';
    print '<tr>';
    print '<td>';
    print $langs->trans('CustomersCategoryShort');
    print ' <select name="categoria">';
    foreach ($lista_categorias_clientes as $cat) {
        print '<option value="' . $cat->id . '">';
        print $cat->label;
        print '</option>';
    }
    print '</select>';
    print '</td>';
    print '<td>';
    print $langs->trans('PVTABLASTARIFAS');
    print ' <select id="tabla_precio" name="tabla_precio">';
    foreach ($lista_tablas_precios as $tab) {
        print '<option value="' . $tab->rowid . '">';
        print $tab->nombre;
        print '</option>';
    }
    print '</select>';
    print ' <label style="cursor:pointer" onclick="Ver_Detalles_Tarifa()">';
    print img_picto($langs->trans('PVVERTARIFACLIENTE'), 'detail');
    print '</label> ';
    print '</td>';
    print '<td>';
    print ' <input class="button" type="submit" value="' . $langs->trans('Add') . '"/>';
    print '</td>';
    print '</tr>';
    print '</table>';
    print '</form>';

    print '</td></tr>';
    print '</table>';
} else {
    $langs->load("main");
    print '<div class="error">';
    print $langs->trans('DisabledModules');
    print '<br/>';
    print '<br/>';
    print $langs->trans('Category');
    print ': ';
    print $langs->trans('ErrorGoToModuleSetup') . '.';
    print '<br/>';
    print '<br/>';
    print '<a href="' . DOL_URL_ROOT . '/admin/modules.php?mode=other">';
    print $langs->trans('Activate');
    print '</a>';
    print '</div>';
}

print '<div id="celda_detalles" title="' . $langs->trans("Tarifas") . '"></div>';
dol_htmloutput_mesg($mesg);
dol_htmloutput_errors($error, $errores);
$db->close();
llxFooter('$Date: 2012/10/19 17:51:15 $ - $Revision: 1.0 $');
