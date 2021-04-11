<?php
/*   Copyright (C) 2012 Alexis José Turruella Sánchez
Desarrollado en el mes de octubre de 2012
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia al volumen
Fichero tabla_producto.php
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

$langs->load("products");
$langs->load("bills");
$langs->load("preciovolumen@pvplus");

// Security check
if (isset($_GET["id"]) || isset($_GET["ref"])) {
    $id = isset($_GET["id"]) ? $_GET["id"] : (isset($_GET["ref"]) ? $_GET["ref"] : '');
}
$fieldid = isset($_GET["ref"]) ? 'ref' : 'rowid';

if ($user->societe_id) {
    $socid = $user->societe_id;
}

$result = restrictedArea($user, 'produit|service', $id, 'product', '', '', $fieldid);

$mesg = '';
/*
 * Actions
 */
//------------------------------------------------------------
if ($_GET['accion'] == 'aplicar') {
    $tp = new tabla_producto($db);

    $tp->id_producto = $id;
    $tp->delete($user);

    $tp->id_tabla_precio = $_GET['id_tabla'];
    $tp->id_producto = $id;
    $tp->fecha_creado = date("Y/m/d h:i", strtotime('now'));
    if ($tp->create($user)) {
        header("Location: tabla_producto.php?id=" . $id);
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVERRORAPLICARTABLA");
    }
}
//------------------------------------------------------------
if ($_GET['accion'] == 'eliminar') {
    $tp = new tabla_producto($db);
    $tp->id_tabla_precio = $_GET['id_tabla'];
    $tp->id_producto = $id;
    if ($tp->delete($user)) {
        header("Location: tabla_producto.php?id=" . $id);
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVERRORDELETETABLA");
    }
}

//--------------------ADICIONAR UNA NUEVA TABLA----------------------------------------
if ($_GET["action"] == 'add' || $_POST["action"] == 'add') {

    $product = new Product($db);
    $product->fetch($_GET["id"]);
    $objTablaPrecio = new tabla_precios($db);
    $objTablaPrecio->nombre = $product->ref;
    $objTablaPrecio->descripcion = $product->label;
    $objTablaPrecio->tipo = "descuento";
    $result = $objTablaPrecio->create($user);
    header("Location:" . $_SERVER['PHP_SELF'] . "?id=" . $_GET["id"]);

}

//------------------------------------------------------------

$objPrecioVol = new Precio_Volumen($db);
/*fin de acciones .....*/

/*
 *    View
 */

$html = new Form($db);

if ($_GET["id"] || $_GET["ref"]) {
    $product = new Product($db);

    if ($_GET["id"]) {
        $result = $product->fetch($_GET["id"]);
    }

    $morejs = array("/pvplus/js/preciovolumen.js");
    llxHeader('', $langs->trans('PVTABLASTARIFAS'), $langs->trans("PVTABLASTARIFAS"), '', '', '', $morejs, '', 0, 0);

    if ($result) {
        $head = product_prepare_head($product, $user);
        $titre = $langs->trans("CardProduct" . $product->type);
        $picto = ($product->type == 1 ? 'service' : 'product');
        dol_fiche_head($head, 'PrecioVolumen', $titre, 0, $picto);

        dol_htmloutput_errors($error, $errores);
        /*CARGO LAS LISTAS */
        $aTablasPrecios = array();

        print($mesg);

        print '<table class="border" width="100%">';

        print '<tr>';
        print '<td width="25%">' . $langs->trans("Ref") . '</td><td colspan="2">';
        print $html->showrefnav($product, 'ref', '', 1, 'ref');
        print '</td>';
        print '</tr>';

        // Libelle
        print '<tr><td>' . $langs->trans("Label") . '</td><td colspan="2">' . $product->libelle . '</td>';
        print '</tr>';
        print '</tr>';

        // Description
        print '<tr><td>' . $langs->trans("Description") . '</td><td colspan="2">' . nl2br($product->description) . '</td>';
        print '</tr>';

        // Price
        print '<tr><td>' . $langs->trans("SellingPrice") . '</td><td>';
        if (empty($object->multiprices_base_type[1])) {
            $object->multiprices_base_type[1] = "HT";
        }

        if ($conf->global->PRODUIT_MULTIPRICES) {
            $price_base = $product->multiprices_base_type[1];
        } else {
            $price_base = $product->price_base_type;
        }
        if ($price_base == 'TTC') {
            if ($conf->global->PRODUIT_MULTIPRICES) {
                $precio = $product->multiprices_ttc[1];
                print price($precio) . ' ' . $langs->trans($product->multiprices_base_type[1]);
            } else {
                print price($product->price_ttc) . ' ' . $langs->trans($product->price_base_type);
            }

        } else {
            if ($conf->global->PRODUIT_MULTIPRICES) {
                $precio = $product->multiprices[1];
                print price($precio) . ' ' . $langs->trans($product->multiprices_base_type[1]);
            } else {
                print price($product->price) . ' ' . $langs->trans($product->price_base_type);
            }
        }
        print '</td></tr>';
        // Price minimum
        print '<tr><td>' . $langs->trans("MinPrice") . '</td><td>';
        if (empty($object->multiprices_base_type[1])) {
            $object->multiprices_base_type[1] = "HT";
        }

        if ($conf->global->PRODUIT_MULTIPRICES) {
            $price_base = $product->multiprices_base_type[1];
        } else {
            $price_base = $product->price_base_type;
        }
        if ($price_base == 'TTC') {
            if ($conf->global->PRODUIT_MULTIPRICES) {
                $precio = $product->multiprices_min_ttc[1];
                print price($precio) . ' ' . $langs->trans($product->multiprices_base_type[1]);
            } else {
                print price($product->price_min_ttc) . ' ' . $langs->trans($product->price_base_type);
            }

        } else {
            if ($conf->global->PRODUIT_MULTIPRICES) {
                $precio = $product->multiprices_min[1];
                print price($precio) . ' ' . $langs->trans($product->multiprices_base_type[1]);
            } else {
                print price($product->price_min) . ' ' . $langs->trans($product->price_base_type);
            }
        }

        print '</td></tr>';
        // Status (to sell)
        print '<tr><td>' . $langs->trans("Status") . ' (' . $langs->trans("Sell") . ')</td><td>';
        print $product->getLibStatut(2, 0);
        print '</td></tr>';

        print "</table>\n";
        //-------Validamos si el producto existe o no
        if (empty($objPrecioVol->ListaTablas($product->ref))) {

            $aTablasPrecios = $objPrecioVol->ListaTablas();
            echo '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=add&product=' . $product->ref . '&id=' . $product->id . '">Crear Descuento</a>';
            echo '<br>';

        } else {


			$aTablasPrecios = $objPrecioVol->ListaTablas($product->ref);

			echo '<table class="border" width="100%">';
			echo '<tr><td  width="25%">Acciones</td><td>';
            print('<a href="gestionar_tablas.php?agregar=' . $aTablasPrecios[0]->rowid . '" >' . img_picto($langs->trans("PVADDTARIFA"), "addfile") . '</a> ');

            print(' <a href="gestionar_tablas.php?cargo_editar=' . $aTablasPrecios[0]->rowid . '" style="padding-left:1px">' . img_picto($langs->trans("PVEDITTABLA"), "edit") . '</a> ');

            print(' <a href="gestionar_tablas.php?ver=' . $aTablasPrecios[0]->rowid . '" style="padding-left:1px">' . img_picto($langs->trans("PVVERTABLA"), "detail") . '</a> ');

            print(' <a href="gestionar_tablas.php?eliminar=si&del=' . $aTablasPrecios[0]->rowid . '" style="padding-left:1px" >' . img_picto($langs->trans("PVDELTABLA"), "delete") . '</a> ');
			echo '</td></tr></table>';

        }

        print '<br/>';
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print '<td colspan="4">';
        if ($user->rights->preciovolumen->aplicartablaproducto) {
            print $html->textwithpicto($langs->trans('PVLISTATABPRECXVOL'), $langs->trans('PVAPLICARTABLAPRODUCTO'));
        } else {
            print $langs->trans('PVTABLAAPLICADAPRODUCTO');
        }

        print '</td>';
        print '</tr>';
        foreach ($aTablasPrecios as $tabla) {
            $var = !$var;
            $tp = new tabla_producto($db);
            if (!$user->rights->preciovolumen->aplicartablaproducto && $tp->fetch($tabla->rowid, $id) == true) {
                print "<tr $bc[$var] >";
                print '<td>';
                print '</td>';
                print '<td>';
                print '<b><label style="cursor:pointer" onclick="DetallesTabla(' . $tabla->rowid . ')">';
                print $tabla->nombre;
                print '</b></label>';
                print " ($tabla->descripcion)";
                print '</td>';

                print '<td style="width:10%">';
                if ($tp->fetch($tabla->rowid, $id) == true) {
                    print '<input type="text" style="width:40px;" value="0" id="cantidad"/>';
                    print '<label style="cursor:pointer;" onclick="CalcularCantidadProducto(' . $tabla->rowid . ',' . $id . ')">';
                    print ' ' . img_picto($langs->trans('PVCAL'), 'calc');
                    print '</label>';
                }
                print '</td>';

                print '<td style="width:5%">';

                print '<label style="cursor:pointer" onclick="DetallesTabla(' . $tabla->rowid . ')">';
                print img_picto($langs->trans('Show'), 'detail');
                print '</label> ';
                print '</td>';
                print '</tr>';
            } else if ($user->rights->preciovolumen->aplicartablaproducto) {
                print "<tr $bc[$var] >";
                print '<td>';
                print '</td>';
                print '<td>';
                print '<b><label style="cursor:pointer" onclick="DetallesTabla(' . $tabla->rowid . ')">';
                print $tabla->nombre;
                print '</b></label>';
                print " ($tabla->descripcion)";
                print '</td>';

                print '<td>';
                if ($tp->fetch($tabla->rowid, $id) == true) {
                    print '<input type="text" style="width:40px;" value="0" id="cantidad"/>';
                    print '<label style="cursor:pointer" onclick="CalcularCantidadProducto(' . $tabla->rowid . ',' . $id . ')">';
                    print img_picto($langs->trans('PVCAL'), 'calc');
                    print '</label>';
                }
                print '</td>';

                print '<td>';
                print '<label style="cursor:pointer" onclick="DetallesTabla(' . $tabla->rowid . ')">';
                print img_picto($langs->trans('Show'), 'detail');
                print '</label> ';

                if ($tp->fetch($tabla->rowid, $id) == false) {
                    print '<a href="tabla_producto.php?id=' . $id . '&accion=aplicar&id_tabla=' . $tabla->rowid . '">';
                    print img_picto($langs->trans('PVAPLICARTABLA'), 'switch_off');
                    print '</a>';
                } else {
                    print '<a href="tabla_producto.php?id=' . $id . '&accion=eliminar&id_tabla=' . $tabla->rowid . '">';
                    print img_picto($langs->trans('PVNOAPLICARTABLA'), 'switch_on');
                    print '</a>';
                }

                print '</td>';
                print '</tr>';
            }
        }
        print "</table>\n";
        //este div encierra el contenido d ela pagina dentro de la pestanna

        print '<div id="celda_detalles" title="' . $langs->trans("Tarifas") . '"></div>';
    }
} else {
    print $langs->trans("ErrorUnknown");
}

$db->close();

llxFooter('$Date: 2012/10/19 17:51:15 $ - $Revision: 1.0 $');
