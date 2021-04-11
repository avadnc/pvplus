<?php

/*   Copyright (C) 2015 - 2016 Alexis José Turruella Sánchez
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia a la cantidad de compra
Fichero index.php
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


include_once DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php";
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$langs->load("products");
$langs->load("bills");
$langs->load("categories");
$langs->load("preciovolumen@pvplus");

// Security check
if (!$user->rights->preciovolumen->admintablasdescuentos) {
    accessforbidden();
}

$mesg = '';
$error = 0;
$errors = array();

/*
 * Actions
 */
$objPrecioVol = new Precio_Volumen($db);

$html = new Form($db);
if (file_exists("/pvplus/js/preciovolumen.js")) {
    $morejs = array("/pvplus/js/preciovolumen.js");


} else {
   $morejs = array("/custom/pvplus/js/preciovolumen.js");

}

llxHeader('', $langs->trans('PVDESC'), $langs->trans("PVDESC"), '', '', '', $morejs, '', 0, 0);
//echo "esta es la entidad " .$conf->entity;
$aTablasPrecios = $objPrecioVol->ListaTablas();
$aProductosTablas = $objPrecioVol->ProductosTablas();
$lista_categorias_asociadas = $objPrecioVol->Tarifas_Por_Categoria();
$lista_categorias_productos_asociadas = $objPrecioVol->Tarifas_Por_Categoria(0);

load_fiche_titre($langs->trans("PVDESC"));

print '<table border="0" width="100%" class="notopnoleftnoright">';

print '<tr><td valign="top" width="30%" class="notopnoleft">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("PVGESTION") . '</td></tr>';
if (is_array($aTablasPrecios) && sizeof($aTablasPrecios) > 0) {
    foreach ($aTablasPrecios as $tabla) {
        $var = !$var;
        print "<tr $bc[$var]>";
        print '<td><b title="' . $tabla->descripcion . '"><label onclick="DetallesTabla(' . $tabla->rowid . ')">' . $tabla->nombre . '</label></b></td>';
        print '<td>';
        print '<label style="cursor:pointer" onclick="DetallesTabla(' . $tabla->rowid . ')">';
        print img_picto($langs->trans('PVTABLASTARIFAS'), 'detail');
        print '</label> ';
        print '</td>';
        print '</tr>';
    }
} else {
    print '<tr>';
    print '<td colspan="2">';
    print $langs->trans('NoRecordFound');
    print '</td>';
    print '</tr>';
}
print '</table>';

print '</td>';

//------------------------------------------------------------------
print '<td valign="top" width="60%" class="notopnoleft">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">
	<td colspan="3">' . $langs->trans("PVPRODUCTOSCONTABLAS") . '</td>
</tr>';
if (is_array($aProductosTablas) && sizeof($aProductosTablas) > 0) {
    foreach ($aProductosTablas as $prod) {
        $product_static = new Product($db);
        $product_static->fetch($prod->rowid);
        $var = !$var;
        print "<tr $bc[$var]>";
        print '<td>';
        print $product_static->getNomUrl(1);
        /*
        print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$prod->rowid.'">';
        print img_picto($langs->trans("view").' '.$prod->label,'object_product');
        print ' '.$prod->label;
        print '</a>';
         */
        print '</td>';
        print '<td>';
        print '<b><label style="cursor:pointer" onclick="DetallesTabla(' . $prod->id_tabla_precio . ')">' . $prod->nombre . '</label></b>';
        print '</td>';
        print '<td>';
        print $prod->fecha_creado;
        print '</td>';
        print '</tr>';
    }
} else {
    print '<tr>';
    print '<td colspan="3">';
    print $langs->trans('NoRecordFound');
    print '</td>';
    print '</tr>';
}
print '</table>';

print '</td>';
print '</tr>';

print '<tr>';
print '<td colspan="2" class="notopnoleft">';

if ($conf->categorie->enabled) {
    print '<br/>';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td>' . $langs->trans("CustomersCategoriesShort") . '</td>';
    print '<td>' . $langs->trans('PVGESTION') . '</td>';
    print '<td>' . $langs->trans('Action') . '</td>';
    print '</tr>';
    if (is_array($lista_categorias_asociadas) && sizeof($lista_categorias_asociadas) > 0) {
        foreach ($lista_categorias_asociadas as $asociacion) {
            $var = !$var;
            $cat_aux = new Categorie($db);
            $cat_aux->fetch($asociacion->id_categoria);
            $tarifa_aux = new Tabla_precios($db);
            $tarifa_aux->fetch($asociacion->id_tabla_precio);
            print "<tr $bc[$var]>";
            print '<td><b title="' . $cat_aux->description . '">' . $cat_aux->label . '</b></td>';
            print '<td><b title="' . $tarifa_aux->descripcion . '">';
            print $tarifa_aux->nombre;
            print '</b></td>';
            print '<td>';
            print '<label style="cursor:pointer" onclick="DetallesTabla(' . $asociacion->id_tabla_precio . ')">';
            print img_picto($langs->trans('PVTABLASTARIFAS'), 'detail');
            print '</label> ';
            print '</td>';
            print '</tr>';
        }
    } else {
        print '<tr>';
        print '<td colspan="3">';
        print $langs->trans('NoRecordFound');
        print '</td>';
        print '</tr>';
    }
    print '</table>';

    print '<br/>';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>' . $langs->trans("ProductsCategoriesShort") . '</td>';
    print '<td>' . $langs->trans('PVGESTION') . '</td>';
    print '<td>' . $langs->trans('Action') . '</td>';
    print '</tr>';
    if (is_array($lista_categorias_productos_asociadas) && sizeof($lista_categorias_productos_asociadas) > 0) {
        foreach ($lista_categorias_productos_asociadas as $asociacion) {
            $var = !$var;
            $cat_aux = new Categorie($db);
            $cat_aux->fetch($asociacion->id_categoria);
            $tarifa_aux = new Tabla_precios($db);
            $tarifa_aux->fetch($asociacion->id_tabla_precio);
            print "<tr $bc[$var]>";
            print '<td><b title="' . $cat_aux->description . '">' . $cat_aux->label . '</b></td>';
            print '<td><b title="' . $tarifa_aux->descripcion . '">';
            print $tarifa_aux->nombre;
            print '</b></td>';
            print '<td>';
            print '<label style="cursor:pointer" onclick="DetallesTabla(' . $asociacion->id_tabla_precio . ')">';
            print img_picto($langs->trans('PVTABLASTARIFAS'), 'detail');
            print '</label> ';
            print '</td>';
            print '</tr>';
        }
    } else {
        print '<tr>';
        print '<td colspan="3">';
        print $langs->trans('NoRecordFound');
        print '</td>';
        print '</tr>';
    }
    print '</table>';

} else {
    $langs->load("admin");
    print '<div class="info">' . $langs->trans('DisabledModules') . ': ' . $langs->trans('Category');
    print '<br/>' . $langs->trans('ToActivateModule');
    print ' <a href="' . DOL_URL_ROOT . '/admin/modules.php?mode=other">';
    print $langs->trans('Activate');
    print '</a>';
    print '</div>';
}

print '</td>';
print '</tr>';

print '</table>';

print '<div id="celda_detalles" title="' . $langs->trans("Tarifas") . '"></div>';

$db->close();

llxFooter('$Date: 2015/06/14 17:51:15 $ - $Revision: 1.0 $');
