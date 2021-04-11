<?php
/*   Copyright (C) 2013 Alexis José Turruella Sánchez
Desarrollado en el mes de mayo de 2013
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia al volumen
Fichero tabla_producto_cliente.php
 */
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

require_once DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php";
dol_include_once("/pvplus/class/preciovolumen.class.php");
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$action = isset($_GET["action"]) ? $_GET["action"] : $_POST["action"];

$langs->load("products");
$langs->load("categories");
$langs->load("preciovolumen@pvplus");
$error = '';
// Security check
$socid = isset($_GET["socid"]) ? $_GET["socid"] : $_POST["socid"];
if ($user->societe_id) {
    $socid = $user->societe_id;
}

$result = restrictedArea($user, 'societe', $socid);

/*
 * Actions
 */
$obj_tarifa = new Precio_Volumen($db);
if ($_POST['accion'] == 'asignar_tarifa_cliente') {
    if ($_POST['token']) {
        if ($obj_tarifa->Cliente_Tiene_Tabla_Tarifa_Producto($socid, $_POST['id_producto']) == false) {
            $nueva_asignacion = new tabla_producto_cliente($db);
            $nueva_asignacion->id_tabla_precio = $_POST['tabla_precio'];
            $nueva_asignacion->id_producto = $_POST['id_producto'];
            $nueva_asignacion->id_cliente = $socid;
            $nueva_asignacion->fecha_creado = date("Y/m/d h:i", strtotime('now'));

            if ($nueva_asignacion->create($user)) {
                header("Location: tabla_producto_cliente.php?socid=" . $socid);
            } else {
                $error = 0;
                $errores[] = $langs->trans("PVERRORDELETETABLA");
            }
        } else {
            $error = 0;
            $errores[] = $langs->trans("PVERRORCLIENTAPLICADOTARIFAPRODUCTO");
        }
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVOPERACIONINVALIDA");
    }
}
if ($_GET['accion'] == 'delasig') {
    if ($_GET['token'] == $_SESSION['token']) {
        $asignacion_delete = new tabla_producto_cliente($db);
        $asignacion_delete->fetch($_GET['id_asig']);
        $asignacion_delete->delete($user);

        header("Location: tabla_producto_cliente.php?socid=" . $socid);
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVOPERACIONINVALIDA");
    }

}

/*
 *    View
 */

$form = new Form($db);
$morejs = array("/pvplus/js/preciovolumen.js");
llxHeader('', $langs->trans('TarifaCliente'), '', '', '', '', $morejs, '', 0, 0);

if ($socid > 0) {
    $societe = new Societe($db, $socid);
    $societe->fetch($socid);

    /*
     * Affichage onglets
     */

    $head = societe_prepare_head($societe);
    dol_fiche_head($head, 'PrecioVolumenCliente', $langs->trans("ThirdParty"), 0, 'company');

    print '<table class="border" width="100%">';

    print '<tr><td width="20%">' . $langs->trans('ThirdPartyName') . '</td>';
    print '<td colspan="3">';
    print $form->showrefnav($societe, 'socid', '', ($user->societe_id ? 0 : 1), 'rowid', 'nom');
    //print $form->showrefnav($societe,'socid','',($user->societe_id?0:1),'rowid','nom');
    print '</td></tr>';

    if ($societe->client) {
        print '<tr><td>';

        print '<input type="hidden" id="dir_url" value="' . DOL_URL_ROOT . '"/>';
        print $langs->trans('CustomerCode') . '</td><td colspan="3">';
        print $societe->code_client;
        if ($societe->check_codeclient() != 0) {
            print ' <font class="error">(' . $langs->trans("WrongCustomerCode") . ')</font>';
        }

        print '</td></tr>';
    }
    // Status
    print '<tr><td>' . $langs->trans("Status") . '</td>';
    print '<td colspan="' . (2 + (($showlogo || $showbarcode) ? 0 : 1)) . '">';
    print $societe->getLibStatut(2);
    print '</td>';
    print $htmllogobar;
    $htmllogobar = '';
    print '</tr>';

    // Zip / Town
    print '<tr><td width="25%">' . $langs->trans('Zip') . ' / ' . $langs->trans("Town") . '</td><td colspan="' . (2 + (($showlogo || $showbarcode) ? 0 : 1)) . '">';
    print $societe->zip . ($societe->zip && $societe->town ? " / " : "") . $societe->town;
    print "</td>";
    print '</tr>';

    // Country
    print '<tr><td>' . $langs->trans("Country") . '</td><td colspan="' . (2 + (($showlogo || $showbarcode) ? 0 : 1)) . '" nowrap="nowrap">';
    $img = picto_from_langcode($societe->country_code);
    if ($societe->isInEEC()) {
        print $form->textwithpicto(($img ? $img . ' ' : '') . $societe->country, $langs->trans("CountryIsInEEC"), 1, 0);
    } else {
        print($img ? $img . ' ' : '') . $societe->country;
    }

    print '</td></tr>';

    print "</table>";

    if ($conf->categorie->enabled) {
        $obj_categorias = new Categorie($db);
        $resultado_categorias_cliente = $obj_categorias->containing($socid, 2);
        $categorias_cliente_asociadas_tabla = array();
        if (sizeof($resultado_categorias_cliente) > 0) {
            $obj_categoria_tabla = new tabla_categoria_cliente($db);

            foreach ($resultado_categorias_cliente as $cat) {
                $obj_categoria_tabla->id_categoria = $cat->id;
                if ($id_relacion = $obj_categoria_tabla->existe_asociada_categoria()) {

                    $obj_categoria_tabla->fetch($id_relacion);
                    $tabla = new tabla_precios($db);

                    $tabla->fetch($obj_categoria_tabla->id_tabla_precio);
                    $info = array('cat' => $cat, 'tabla' => $tabla, 'relacion' => $obj_categoria_tabla);
                    $categorias_cliente_asociadas_tabla[] = $info;
                }
            }
        }
        if (sizeof($categorias_cliente_asociadas_tabla) > 0) {
            print '<br/>';
            print '<table class="noborder" width="100%">';
            print '<tr class="liste_titre">';
            print '<td colspan="2">' . $langs->trans('CompanyIsInCustomersCategories') . '</td>';
            print '<td></td>';
            print '</tr>';
            foreach ($categorias_cliente_asociadas_tabla as $cat) {
                $var = !$var;
                print "<tr $bc[$var]>";
                print '<td>';
                print $langs->trans('Category') . ': <b>' . $cat['cat']->label . '</b>';
                print '</td>';
                print '<td>';
                print '<b>' . $cat['tabla']->nombre . '</b>';
                print '</td>';
                print '<td>';
                print '<label style="cursor:pointer" onclick="DetallesTabla(' . $cat['tabla']->id . ')">';
                print img_picto($langs->trans('PVTABLASTARIFAS'), 'detail');
                print '</label>';
                print '</td>';
                print '</tr>';
            }
            print '</table>';
        }
    }

    //validacion de que tiene el permiso de ver la tarificacion

    $tarifas = $obj_tarifa->Tarifas_Un_Cliente($socid);

    if (sizeof($tarifas) > 0) {
        //aqui se pone el listado de las tarifas de los productos que tiene el cliente que se esta mostrando
        //en cada items del listado debe darse la posibilidad de modificar la tarifa y de eliminar
        print '<br/>' . $langs->trans('PVTABLAPRECIOAPLCLIENTE');
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print '<td width="20%">';
        print $langs->trans('Name');
        print '</td>';
        print '<td width="10%">';
        print $langs->trans('PVFECHACREADOTARIFA');
        print '</td>';
        print '<td width="20%">';
        print $langs->trans('Product');
        print '</td>';
        print '<td width="24%">';
        print $langs->trans('Description');
        print '</td>';
        print '<td width="7%">';
        print $form->textwithpicto($langs->trans('PVCAL'), $langs->trans('PVMENCALC'));
        print '</td>';
        print '<td width="7%">';
        print $langs->trans('Action');
        print '</td>';
        print '</tr>';
        for ($i = 0; $i < sizeof($tarifas); $i++) {
            print '<tr ' . $bc[$var = !$var] . '>';
            print '<td>';
            if ($_GET['accion'] == 'editasig' && $_GET['id_asig'] == $tarifas[$i]->id) {
                print "<form method='post' action='' name='nuevo' onsubmit=''>";
                print '<input type="hidden" name="accion" value="editarasignacion"/>';
                print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '"/>';
                print '<input type="hidden" name="id_asignacion" value="' . $_GET['id_asig'] . '"/>';
                $tarifas_prod = $obj_tarifa->Tarifas_De_Producto($tarifas[$i]->id_producto);

                print '<select name="nueva_tarifa">';
                for ($j = 0; $j < sizeof($tarifas_prod); $j++) {
                    if ($_GET['lasttabla'] == $tarifas_prod[$j][0]) {
                        print '<option selected="selected" value=' . $tarifas_prod[$j][0] . '>' . $tarifas_prod[$j][1] . '</option>';
                    } else {
                        print '<option value=' . $tarifas_prod[$j][0] . '>' . $tarifas_prod[$j][1] . '</option>';
                    }

                }
                print '</select> ';
                print ' <input class="button" type="submit" value="' . $langs->trans('Save') . '"/>';
                print "</form>";
            } else {
                print $form->textwithpicto($tarifas[$i]->nombre_tabla, $langs->trans('PVMENSDETALLECLIENTETRIFA'));
            }

            print '</td>';
            print '<td>';
            print $tarifas[$i]->fecha_creado;
            print '</td>';
            print '<td>';
            print '<a href="' . DOL_URL_ROOT . '/product/card.php?id=' . $tarifas[$i]->id_producto . '">';
            print img_picto($langs->trans("view") . $tarifas[$i]->nombre_producto, 'object_product');
            print $tarifas[$i]->nombre_producto;
            print '</a>';
            print '</td>';
            print '<td>';
            print $tarifas[$i]->descripcion;
            print '</td>';
            print '<td >';
            print '<input type="text" style="width:40px;" value="0" id="calc' . $tarifas[$i]->id . '"/>';
            print '<label style="cursor:pointer" onclick="CalcularCantidadTarifa(' . $tarifas[$i]->id_tabla_precio . ',' . $tarifas[$i]->id . ',' . $tarifas[$i]->id_producto . ',' . $socid . ')">';
            print img_picto($langs->trans('PVCAL'), 'calc');
            print '</label>';
            print '</td>';
            print '<td>';
            print '<label style="cursor:pointer" onclick="DetallesTabla(' . $tarifas[$i]->id_tabla_precio . ')">';
            print img_picto($langs->trans('PVVERTARIFACLIENTE'), 'detail');
            print '</label>';
            if ($user->rights->preciovolumen->aplicartablasprodclientes) {
                print ' ';
                print ' <a href="tabla_producto_cliente.php?accion=delasig&id_asig=' . $tarifas[$i]->id . '&token=' . $_SESSION['newtoken'] . '&socid=' . $socid . '">';
                print img_picto($langs->trans('PVDELTARIFACLIENTE'), 'delete');
                print '</a>';
            }
            print '</td>';
            print '</tr>';
        }
        print '</table>';
        //fin del listado

        //formulario de nueva asignacion de tarifa producto cliente

        //fin del formulario de nuevo
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVMENSNOTARIFAAPLICADACLIENTE");
    }

} else {
    $error = 0;
    $errores[] = $langs->trans("PVNOPRIVILEGIOSVERTARIFASCLIENTE");
}

if ($user->rights->preciovolumen->aplicartablasprodclientes) {
    print '<br/>';
    print "<form method='post' action='' name='nuevo' onsubmit='return Validar_Tarifa_Cliente(this)'>";
    print '<input type="hidden" name="token" value="' . $_SESSION['token'] . '"/>';
    print '<input type="hidden" name="accion" value="asignar_tarifa_cliente"/>';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td colspan="3">';
    print $form->textwithpicto($langs->trans('PVAPLICARNUEVATARIFACLIENTE'), $langs->trans('PVERRORCLIENTESOLATABLAPRODUCTO'));
    print '</td>';
    print '</tr>';
    print '<tr ' . $bc[1] . ' height="20px" ' . $bc[0] . '>';
    print '<td width="20%">';
    print $langs->trans('Product');
    print '</td>';
    print '<td>';
    $productos = $obj_tarifa->ProductosSerivicios();
    print '<select id="id_producto" name="id_producto">';
    print '<option value="-1">.::' . $langs->trans('PVSELLPRODUCTO') . '::.</option>';
    for ($i = 0; $i < sizeof($productos); $i++) {
        print '<option value=' . $productos[$i][0] . '>' . $productos[$i][2] . '</option>';
    }

    print '</select>';
    print '</td>';
    print '<td width="40%" rowspan="3">';
    print '<div class="info">' . $langs->trans("PVMENSEXPLITARIFASCLIENTEPRODUCTO") . ' <br/> </div>';
    print '</td>';
    print '</tr>';
    print '<tr ' . $bc[1] . ' height="20px" ' . $bc[0] . '>';
    print '<td>';
    print $langs->trans('PVGESTION');
    print '</td>';
    print '<td ><div style="display:inline" id="celda_tarifas">';
    print '<select name="tabla_precio" id="tabla_precio">';
    print '<option value="-1">.::' . $langs->trans('PVSELLTABLA') . '::.</option>';
    $aTablasPrecios = $obj_tarifa->ListaTablas();
    foreach ($aTablasPrecios as $tabla) {
        print '<option value="' . $tabla->rowid . '">' . $tabla->nombre . '</option>';
    }
    print '</select>';
    print '</div>';
    print ' <label style="cursor:pointer" onclick="Ver_Detalles_Tarifa()"> ' . img_picto($langs->trans('PVVERDETALLESTARIFA'), 'detail') . '</label>';
    print '</td>';
    print '</tr>';
    print '<tr ' . $bc[1] . '>';
    print '<td valign="top" colspan="2">';
    print '<input type="submit" value="' . $langs->trans('PVAPLICARNUEVATARIFACLIENTE') . '" class="button" />';
    print '</td>';
    print '</tr>';
    print '</table>';
    print '</form>';
}
print '<div id="celda_detalles" title="' . $langs->trans("Tarifas") . '"></div>';
print '</div>';
dol_htmloutput_errors($error, $errores);
$db->close();
llxFooter('$Date: 2012/10/19 17:51:15 $ - $Revision: 1.0 $');
