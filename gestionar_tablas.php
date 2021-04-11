<?php
/*   Copyright (C) 2015 - 2016 Alexis José Turruella Sánchez
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia a la cantidad de compra
Fichero gestionar_tablas.php
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

require_once DOL_DOCUMENT_ROOT . "/core/lib/product.lib.php";
require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/html.formfile.class.php";
dol_include_once("/pvplus/class/preciovolumen.class.php");

include_once DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php";

$langs->load("products");
$langs->load("bills");
$langs->load("preciovolumen@pvplus");

// Security check
if (!$user->rights->preciovolumen->admintablasdescuentos) {
    accessforbidden();
}
if (isset($_GET["id"]) || isset($_GET["ref"])) {
    $id = isset($_GET["id"]) ? $_GET["id"] : (isset($_GET["ref"]) ? $_GET["ref"] : '');
}
if ($user->societe_id) {
    $socid = $user->societe_id;
}

$mesg = '';
$error = 0;
$errors = array();

/*
 * Actions
 */
$objPrecioVol = new Precio_Volumen($db);
$objTablaPrecio = new tabla_precios($db);
$objTarifa = new Tarifa($db);
extract($_GET);

if (isset($eliminar) && isset($del)) {
    $objTablaPrecio->id = $del;
    $res = $objTablaPrecio->delete($user);

    if ($res > 0) {$mesg .= '<div class="succes">' . $langs->trans("PVMENSDELCORRECTTABLAPRECIO") . '</div>';} else if ($res == 0) {
        $error = 0;
        $errores[] = $langs->trans("PVMENSDELINCORRECTTABLAPRECIOBLOCK");
    } else {
        $error = 0;
        $errores[] = $langs->trans("PVMENSDELINCORRECTTABLAPRECIO");
    }
    unset($eliminar);
}

if ($_POST['action'] == "editar") {
    $tabla = $_POST["tablaEditar"];
    if ($_POST["nombre_tabla_edit"] != "" && $_POST["descripcion_edit"] != "") {

        $objTablaPrecio->id = $tabla;
        $objTablaPrecio->nombre = $_POST["nombre_tabla_edit"];
        $objTablaPrecio->descripcion = $_POST["descripcion_edit"];
        $objTablaPrecio->tipo = $_POST["tipo_edit"];

        $result = $objTablaPrecio->update($user);
        if ($result > 0) {
            $mesg = '<div class="succes">' . $langs->trans("PVMENSEDITCORRECTTABLAPRECIO") . '</div>';

            $cantTarifas = $_POST['cantidad_tarifa_edit'];

            $tarifas = array();
            for ($i = 0; $i < $cantTarifas; $i++) {
                $tarifas[$i]["inferior"] = $_POST["limite_inferior_edit" . $i];
                $tarifas[$i]["superior"] = $_POST["limite_superior_edit" . $i];
            }
            for ($i = 0; $i < $cantTarifas; $i++) {

                if ($_POST["limite_inferior_edit" . $i] != "" && $_POST["limite_superior_edit" . $i] && $_POST["precio_edit" . $i]) {
                    if ($objTarifa->VerificaVariasTarifas($tarifas)) {
                        $objTarifa->id = $_POST["id_tarifa_edit" . $i];
                        $objTarifa->cantidad_inferior = $_POST["limite_inferior_edit" . $i];
                        $objTarifa->cantidad_superior = $_POST["limite_superior_edit" . $i];
                        $objTarifa->descuento = price2num($_POST["precio_edit" . $i]);

                        $res = $objTarifa->update($user);
                    } else {
                        $error = 0;
                        $errores[] = $langs->trans("PVMENSEDITINCORRECTTABLAPRECIODETALLE");
                        break;
                    }
                }
            }

        } else {
            $mesg = $myobject->error;
        }
        $eliminar = "";
        if (isset($eliminar)) {unset($eliminar);}

    } else {
        $error = 0;
        $errores[] = $langs->trans("DATOSINCORRECTOS");
    }
}

//--------------------ADICIONAR UNA NUEVA TABLA----------------------------------------
if ($_GET["action"] == 'add' || $_POST["action"] == 'add') {
    if ($_POST["nombre_tabla"] != "" && $_POST["descripcion"] != "") {
        $objTablaPrecio->nombre = $_POST["nombre_tabla"];
        $objTablaPrecio->descripcion = $_POST["descripcion"];
        $objTablaPrecio->tipo = $_POST["tipo_tabla"];
        $result = $objTablaPrecio->create($user);
        if ($result > 0) {
            $mesg = '<div class="succes">' . $langs->trans("PVMENSADDCORRECTTABLAPRECIO") . '</div>';
        } else {
            $error = 0;
            $errores[] = $objTablaPrecio->error;
        }
        $eliminar = "";
        if (isset($eliminar)) {unset($eliminar);}
        $cargo_editar = "";
        if (isset($cargo_editar)) {unset($cargo_editar);}
        $ver = "";
        if (isset($ver)) {unset($ver);}

    } else {
        $error = 0;
        $errores[] = $langs->trans("DATOSINCORRECTOS");
    }
}
//--------------------FIN ADICIONAR UNA NUEVA TABLA----------------------------------------
if ($_POST['action'] == "edit_tarifa") {
    if ($_POST["inferior_editar"] != "" && $_POST["superior_editar"] != "" && $_POST["precio_editar"] != "") {
        $objTarifa->id = $_POST["idTarifa"];
        $objTarifa->cantidad_inferior = $_POST["inferior_editar"];
        $objTarifa->cantidad_superior = $_POST["superior_editar"];
        $objTarifa->descuento = price2num($_POST["precio_editar"]);

        $idTabla = $_POST["tablaAgregarEdit"];

        if ($objTarifa->VerificaLimites($objTarifa->cantidad_inferior, $objTarifa->cantidad_superior)) {
            $aTarifasOrdenadas = $objTarifa->TarifasOrdenadas($idTabla);
            if ($objTarifa->VerificaRangoLimites($aTarifasOrdenadas, $objTarifa->cantidad_inferior, $objTarifa->cantidad_superior, "edicion", $objTarifa->id)) {
                $result = $objTarifa->update($user);
                if ($result > 0) {
                    $mesg = '<div class="succes">' . $langs->trans("PVMENSEDITCORRECTTARIFA") . '</div>';
                    $editar_tarifa = "";
                    if (isset($editar_tarifa)) {unset($editar_tarifa);}
                } else { // Creation KO
                    $mesg = $objTarifa->error;
                }
            } else {
                $error = 0;
                $errores[] = $langs->trans("PVMENSADDINCORRECTLIMITECRUZADO");
            }
        } else {
            $error = 0;
            $errores[] = $langs->trans("PVMENSADDINCORRECTLIMITE");
        }

    } else {
        $error = 0;
        $errores[] = $langs->trans("DATOSINCORRECTOS");
    }
}
//--------------------
if ($_POST['action'] == "add_tarifa") {
    if ($_POST["limite_inferior"] != "" && $_POST["limite_superior"] != "" && $_POST["descuento"] != "") {

        $objTarifa->id_tabla_precio = $_POST["tablaAgregar"];
        $objTarifa->cantidad_inferior = $_POST["limite_inferior"];
        $objTarifa->cantidad_superior = $_POST["limite_superior"];
        $objTarifa->descuento = price2num($_POST["descuento"]);

        if ($objTarifa->VerificaLimites($objTarifa->cantidad_inferior, $objTarifa->cantidad_superior)) {
            $aTarifasOrdenadas = $objTarifa->TarifasOrdenadas($objTarifa->id_tabla_precio);
            if ($objTarifa->VerificaRangoLimites($aTarifasOrdenadas, $objTarifa->cantidad_inferior, $objTarifa->cantidad_superior)) {
                $result = $objTarifa->create($user);
                if ($result > 0) {$mesg = '<div class="succes">' . $langs->trans("PVMENSADDCORRECTTARIFA") . '</div>';} else { // Creation KO
                    $mesg = $objTarifa->error;
                }

            } else {
                $error = 0;
                $errores[] = $langs->trans("PVMENSADDINCORRECTLIMITECRUZADO");
            }
        } else {
            $error = 0;
            $errores[] = $langs->trans("PVMENSADDINCORRECTLIMITE");
        }

    } else {
        $error = 0;
        $errores[] = $langs->trans("DATOSINCORRECTOS");
    }

    $eliminar = "";
    if (isset($eliminar)) {unset($eliminar);}
    $cargo_editar = "";
    if (isset($cargo_editar)) {unset($cargo_editar);}
    $editar_tarifa = "";
    if (isset($editar_tarifa)) {unset($editar_tarifa);}
}
if (isset($agregar)) {
    $idtabla = $agregar;
    $objTablaPrecio->fetch($idtabla);
    if (isset($eliminar_tarifa) && $eliminar_tarifa == 'si') {
        //$objTarifa->fetch($idtarifa);
        $objTarifa->id = $idtarifa;
        $res = $objTarifa->delete($user);

        if ($res > 0) {$mesg = '<div class="succes">' . $langs->trans("PVMENSDELCORRECTTARIFA") . '</div>';} else { // Creation KO
            $error++;
            $errores[] = $langs->trans("PVMENSDELINCORRECT");
        }
        unset($eliminar_tarifa);
        if ($_POST['action'] == "add_tarifa") {$mess = '';}
    }
}
/*fin de acciones .....*/

/*
 *    View
 */

$html = new Form($db);

$product = new Product($db);

if ($_GET["ref"]) {
    $result = $product->fetch('', $_GET["ref"]);
}

if ($_GET["id"]) {
    $result = $product->fetch($_GET["id"]);
}

llxHeader("", $langs->trans("PVTABLASTARIFAS"), $langs->trans("PVTABLASTARIFAS"));

/*
 *  En mode visu
 */

load_fiche_titre($langs->trans("PVGESTION"));
/*CARGO LAS LISTAS */
//$product->id,!$user->admin?$user->societe_id:NULL
$aTablasPrecios = array();
$aTablasPrecios = $objPrecioVol->ListaTablas();

dol_htmloutput_errors($error, $errores);
dol_htmloutput_mesg($mesg);
print('<table class="notopnoleftnoright" width="100%">');
print('<tr class="liste_titre">');
print('<td colspan="3">' . $langs->trans("GTABLASPRECXVOL") . '</td>');
print('</tr>');
print('<tr ' . $bc[1] . '>');
print('<td width="40%"  align="left" valign="top">');

//>>>>>   nueva tabla y listados ---------------

print('<form action="" method="post" name="tabla_precios">');
print('<table class="noborder" width="100%">');
print('<tr  class="liste_titre">');
print('<td colspan="2">' . img_picto($langs->trans("New"), "filenew") . ' ' . $langs->trans("New") . '</td>');
print('</tr>');

print('<tr>');
print('<td width="50%"><span class="fieldrequired">' . $langs->trans("Name") . '</span></td>');
print('<td><input type="text" name="nombre_tabla" value="' . GETPOST('nombre_tabla') . '"/><input type="hidden" name="id_producto" value="' . $product->id . '"/></td>');
print('</tr>');
print('<tr>');
print('<td><span class="fieldrequired">' . $langs->trans("Description") . '</span></td>');
print('<td><textarea name="descripcion" cols="40" rows="3">' . GETPOST('descripcion') . '</textarea></td>');
print('</tr>');
print('<tr>');
print('<td><span class="fieldrequired">' . $langs->trans("Type") . '</span></td>');
print '<td>';
print '<input type="radio" name="tipo_tabla" id="tipo_tabla" value="descuento" ' . (GETPOST('tipo_tabla') == 'descuento' ? 'checked="checked"' : (GETPOST('tipo_tabla') == 'precio') ? '' : 'checked="checked"') . '/>';
print '<label for="tipo_tabla">';
print $langs->trans("PVTABLADESCUENTO");
print '</label>';
print '<br/>';
print '<input type="radio" name="tipo_tabla"  id="tipo_tabla" value="precio" ' . (GETPOST('tipo_tabla') == 'precio' ? 'checked="checked"' : '') . '/>';
print '<label for="tipo_tabla">';
print $langs->trans("PVTABLAPRECIO");
print '</label>';
print '</td>';
print('</tr>');

print('<tr>');
print('<td colspan="2"><center><input type="hidden" value="add" name="action"  id="action" />');
print('<input type="submit" class="button" value="' . $langs->trans('Add') . '"> <input type="reset" class="button" value="' . $langs->trans('Cancel') . '"></center></td>');
print('</tr>');
print('</table>');
print('</form>');

print('<br/>');


//-----fin de nueva tabla y listados------------------
print('</td>');

print('<td width="5%" align="left" valign="top">');
print('</td>');

print('<td width="55%" align="left" valign="top">');

//>>>>>   agregar tarifas  ---------------

if (isset($agregar)) {
    $idtabla = $agregar;
    $objTablaPrecio->fetch($idtabla);

    $objTablaPrecio->ListaTarifas();
    //print($mess);
    print('<form action="" method="post" name="agregar_tarifas_tabla">');
    print('<table class="noborder" width="100%">');
    print('<tr  class="liste_titre">');
    print('<td colspan="2">' . img_picto($langs->trans("PVADDTARIFA"), "filenew") . ' ' . $langs->trans("PVADDTARIFA") . ' <b>(' . $objTablaPrecio->nombre . ')</b></td>');
    print('</tr>');

    print('<tr>');
    print('<td><span class="fieldrequired">' . $langs->trans("PVLIMINFERIOR") . '</span></td>');
    print('<td><input type="text" name="limite_inferior" value="' . GETPOST('limite_inferior') . '" /></td>');
    print('</tr>');
    print('<tr>');
    print('<td><span class="fieldrequired">' . $langs->trans("PVLIMSUPERIOR") . '</span></td>');
    print('<td><input type="text" name="limite_superior" value="' . GETPOST('limite_superior') . '"/></td>');
    print('</tr>');
    print('<tr>');
    print('<td><span class="fieldrequired">' . $objTablaPrecio->label_tipo_table() . '</span></td>');
    print('<td><input type="text" name="descuento" value="' . GETPOST('descuento') . '"/></td>');
    print('</tr>');
    print('<tr>');
    print('<td><input type="hidden" value="add_tarifa" name="action"  id="action" /><input type="hidden" value="' . $idtabla . '" name="tablaAgregar" /></td>');
    print('<td><input type="submit" class="button" value="' . $langs->trans('Add') . '"> <input type="reset" class="button" value="' . $langs->trans('Cancel') . '"></td>');
    print('</tr>');
    print('</table>');
    print('</form>');
    if (sizeof($objTablaPrecio->tarifas) > 0) {
        print '<br/>';
        print('<form action="" method="post" name="editar_tarifas_tabla">');
        print('<table class="noborder" width="100%">');
        print('<tr class="liste_titre">');
        print('<td colspan="4">' . img_picto($langs->trans("PVLISTATARIFASTABLA"), "grip") . ' ' . $langs->trans("PVLISTATARIFASTABLA") . ' ' . $objTablaPrecio->nombre_tabla . '</td>');
        print('</tr>');
        print('<tr class="liste_titre">');
        print('<td width="25%">' . $langs->trans("PVLIMINFERIOR") . '</td>');
        print('<td width="25%">' . $langs->trans("PVLIMSUPERIOR") . '</td>');
        print('<td width="25%">' . $objTablaPrecio->label_tipo_table() . '</td>');
        print('<td width="25%">' . $langs->trans("Action") . '</td>');
        print('</tr>');
        for ($i = 0; $i < sizeof($objTablaPrecio->tarifas); $i++) {
            $var = !$var;
            print "<tr $bc[$var]>";
            if (isset($editar_tarifa) && $editar_tarifa == 'si' && $objTablaPrecio->tarifas[$i]->rowid == $idtarifa) {
                print('<td ><input type="text" name="inferior_editar" value="' . $objTablaPrecio->tarifas[$i]->limite_inferior . '" /></td>');
            } else {
                print('<td >' . $objTablaPrecio->tarifas[$i]->limite_inferior . '</td>');
            }

            if (isset($editar_tarifa) && $editar_tarifa == 'si' && $objTablaPrecio->tarifas[$i]->rowid == $idtarifa) {
                print('<td ><input type="text" name="superior_editar" value="' . $objTablaPrecio->tarifas[$i]->limite_superior . '" /></td>');
            } else {
                print('<td >' . $objTablaPrecio->tarifas[$i]->limite_superior . '</td>');
            }

            if (isset($editar_tarifa) && $editar_tarifa == 'si' && $objTablaPrecio->tarifas[$i]->rowid == $idtarifa) {
                print('<td ><input type="text" name="precio_editar" value="' . price($objTablaPrecio->tarifas[$i]->descuento) . '" /></td>');
            } else {
                print('<td >' . price($objTablaPrecio->tarifas[$i]->descuento) . '</td>');
            }

            if (isset($editar_tarifa) && $editar_tarifa == 'si' && $objTablaPrecio->tarifas[$i]->rowid == $idtarifa) {
                print('<td><input type="hidden" value="edit_tarifa" name="action"  id="action" /><input type="hidden" value="' . $idtarifa . '" name="idTarifa" /><input type="hidden" value="' . $idtabla . '" name="tablaAgregarEdit" />');
                print('<input type="submit" class="button" value="' . $langs->trans('Save') . '"></td>');
                // <input type="reset" class="button" value="'.$langs->trans('Cancel').'"></td>');
            } else {
                print('<td ><a href="gestionar_tablas.php?agregar=' . $idtabla . '&editar_tarifa=si&idtarifa=' . $objTablaPrecio->tarifas[$i]->rowid . '">' . img_picto($langs->trans("Edit"), "edit") . ' <a href="gestionar_tablas.php?agregar=' . $idtabla . '&eliminar_tarifa=si&idtarifa=' . $objTablaPrecio->tarifas[$i]->rowid . '">' . img_picto($langs->trans("Delete"), "delete") . '</td>');
            }

            print('</tr>');
        }
        print('</table>');
        print('</form>');
    }
}
//-----fin agregar tarifas ------------------

//>>>>>    --------------, editar tabla,  ---------------

if (isset($cargo_editar)) {
    $idtabla = $cargo_editar;
    $objTablaPrecio->fetch($idtabla);
    $objTablaPrecio->ListaTarifas();
    print('<form action="" method="post" name="tabla_precios_edit">');
    print('<table class="noborder" width="100%">');
    print('<tr  class="liste_titre">');
    print('<td colspan="4">' . img_picto($langs->trans("PVEDITTABLA"), "edit") . ' ' . $langs->trans("PVTABLAPRECIO") . ' <b>(' . $objTablaPrecio->nombre . ')</b></td>');
    print('</tr>');
    print('<tr>');
    print('<td width="21%">' . $langs->trans("Name") . '</td>');
    print('<td colspan="3"><input type="text" name="nombre_tabla_edit" value="' . $objTablaPrecio->nombre . '" /><input type="hidden" name="tablaEditar" value="' . $idtabla . '" /></td>');
    print('</tr>');
    print('<tr>');
    print('<td>' . $langs->trans("Description") . '</td>');
    print('<td colspan="3"><textarea name="descripcion_edit" cols="40" rows="3">' . $objTablaPrecio->descripcion . '</textarea></td>');
    print('</tr>');
    print('<tr>');
    print('<td>' . $langs->trans("Type") . '</td>');
    print '<td colspan="3">';
    print '<input type="radio" name="tipo_edit" id="tipo_descuento_edit" value="descuento" ' . ($objTablaPrecio->tipo == 'descuento' ? 'checked="checked"' : '') . '/>';
    print '<label for="tipo_descuento_edit">';
    print $langs->trans("PVTABLADESCUENTO");
    print '</label>';
    print '<br/>';
    print '<input type="radio" name="tipo_edit"  value="precio" ' . ($objTablaPrecio->tipo == 'precio' ? 'checked="checked"' : '') . '/>';
    print '<label for="tipo_precio_edit">';
    print $langs->trans("PVTABLAPRECIO");
    print '</label>';
    print '</td>';
    print('</tr>');

    if (sizeof($objTablaPrecio->tarifas) > 0) {
        print '<br/>';
        print '<td colspan="4">';
        print('<table class="noborder" width="100%">');
        print '<tr class="liste_titre">';
        print '<td colspan="4">';
        print img_picto($langs->trans("PVLISTATARIFASTABLA"), "edit") . ' ' . $langs->trans("PVLISTATARIFASTABLA");
        print '</td>';
        print '</tr>';
        print('<tr class="liste_titre">');
        print '<td></td>';
        print('<td width="23%">' . $langs->trans("PVLIMINFERIOR") . '</td>');
        print('<td width="23%">' . $langs->trans("PVLIMSUPERIOR") . '</td>');
        print('<td width="23%">' . $objTablaPrecio->label_tipo_table() . '</td>');
        print('</tr>');
        for ($i = 0; $i < sizeof($objTablaPrecio->tarifas); $i++) {
            $var = !$var;
            print "<tr $bc[$var]>";
            print '<td>';
            print '<input type="hidden" name="id_tarifa_edit' . $i . '" value="' . $objTablaPrecio->tarifas[$i]->rowid . '" />';
            print '<input type="hidden" name="cantidad_tarifa_edit" value="' . sizeof($objTablaPrecio->tarifas) . '" />';
            print '</td>';
            print '<td >';
            print '<input type="text" name="limite_inferior_edit' . $i . '" value="' . $objTablaPrecio->tarifas[$i]->limite_inferior . '" />';
            print '</td>';
            print '<td >';
            print '<input type="text" name="limite_superior_edit' . $i . '" value="' . $objTablaPrecio->tarifas[$i]->limite_superior . '" />';
            print '</td>';
            print '<td >';
            print '<input type="text" name="precio_edit' . $i . '" value="' . price($objTablaPrecio->tarifas[$i]->descuento) . '" />';
            print '</td>';
            print('</tr>');
        }
        print('</table>');
        print '</td>';
    }
    print('<tr>');
    print('<td><input type="hidden" name="action" value="editar" /></td>');
    print('<td colspan="3"><input type="submit" class="button" value="' . $langs->trans('Save') . '"> <input type="reset" class="button" value="' . $langs->trans('Cancel') . '"></td>');
    print('</tr>');

    print('</table>');
    print('</form>');
}

//>>>>>   -------------  ver tabla  ---------------

if (isset($ver)) {
    $idtabla = $ver;
    $objTablaPrecio->fetch($idtabla);
    $objTablaPrecio->ListaTarifas();
    print('<table class="noborder" width="100%">');
    print('<tr  class="liste_titre">');
    print('<td colspan="4">' . img_picto($langs->trans("PVTABLAPRECIO"), "file") . ' ' . $langs->trans("PVTABLAPRECIO") . ' <b>(' . $objTablaPrecio->nombre . ')</b></td>');
    print('</tr>');
    print('<tr>');
    print('<td width="21%">' . $langs->trans("Name") . '</td>');
    print('<td colspan="3">' . $langs->trans($objTablaPrecio->nombre) . '</td>');
    print('</tr>');
    print('<tr>');
    print('<td>' . $langs->trans("Description") . '</td>');
    print('<td colspan="3">' . $langs->trans($objTablaPrecio->descripcion) . '</td>');
    print('</tr>');
    print('<tr>');
    print('<td>' . $langs->trans("Type") . '</td>');
    print('<td colspan="3">' . $langs->trans($objTablaPrecio->label_tipo_table()) . '</td>');
    print('</tr>');
    print('<tr>');
    print('</table>');
    if (sizeof($objTablaPrecio->tarifas) > 0) {
        print '<br/>';
        print '<table class="noborder" width="100%">';
        print('<tr class="liste_titre">');
        print('<td colspan="4">' . img_picto($langs->trans("PVLISTATARIFASTABLA"), "grip") . ' ' . $langs->trans("PVLISTATARIFASTABLA") . '</td>');
        print('</tr>');
        print('<tr class="liste_titre">');
        print('<td></td>');
        print('<td width="23%">' . $langs->trans("PVLIMINFERIOR") . '</td>');
        print('<td width="23%">' . $langs->trans("PVLIMSUPERIOR") . '</td>');
        print('<td width="23%">' . $langs->trans("Descuento") . '(%)</td>');
        print('</tr>');
        for ($i = 0; $i < sizeof($objTablaPrecio->tarifas); $i++) {
            $var = !$var;
            print "<tr $bc[$var]>";
            print('<td><input type="hidden" name="id_tarifa_edit' . $i . '" value="' . $objTablaPrecio->tarifas[$i]->rowid . '" /><input type="hidden" name="cantidad_tarifa_edit" value="' . sizeof($objTablaPrecio->tarifas) . '" /></td>');
            print('<td >' . $objTablaPrecio->tarifas[$i]->limite_inferior . '</td>');
            print('<td >' . $objTablaPrecio->tarifas[$i]->limite_superior . '</td>');
            print('<td >' . price($objTablaPrecio->tarifas[$i]->descuento) . '</td>');
            print('</tr>');
        }
        print '</table>';
    }

}
//----------------------------------------------

print('</td>');
print('</tr>');
print('</table>');

//------------------tabla para paginado -----//
$cant_tablas = sizeof($aTablasPrecios);
if ($cant_tablas > 0) {

    print('<table id="tablaPrecios" class="noborder centpercent tagtable" width="100%">');
    
    print('<tr>');
    print('<td colspan="3">' . $langs->trans("List") . '</td>');
    print('</tr>');
    print('<tr class="liste_titre">');
    print('<td width="50%">' . $langs->trans("Name") . '</td>');
    print('<td width="30%">' . $langs->trans("Type") . '</td>');
    print('<td width="20%">' . $langs->trans("Action") . '</td>');
    print('</tr>');
    for ($i = 0; $i < $cant_tablas; $i++) {
        $product = new Product($db);
        $product->fetch(null,$aTablasPrecios[$i]->nombre);
        $var = !$var;
        print "<tr $bc[$var]>";
        if(empty($product)){
        print('<td>' . img_picto($langs->trans("PVTABLAPRECIO"), "1rightarrow") . ' ' . $aTablasPrecios[$i]->nombre . ' </td>');
        } else {
            echo '<td>' . img_picto($langs->trans("PVTABLAPRECIO"), "1rightarrow") . ' <a href="/custom/pvplus/tabla_producto.php?id='.$product->id.'">' . $aTablasPrecios[$i]->nombre . ' </a></td>';
        }
        print '<td>';
        switch ($aTablasPrecios[$i]->tipo) {
            case "descuento":
                {
                    print $langs->trans('PVTABLATIPODESCUENTO');
                    break;
                }
            case 'precio':
                {
                    print $langs->trans('PVTABLATIPOPRECIO');
                    break;
                }
        }
        print '</td>';
        print('<td>');

        print('<a href="gestionar_tablas.php?agregar=' . $aTablasPrecios[$i]->rowid . '" >' . img_picto($langs->trans("PVADDTARIFA"), "addfile") . '</a> ');

        print(' <a href="gestionar_tablas.php?cargo_editar=' . $aTablasPrecios[$i]->rowid . '" style="padding-left:1px">' . img_picto($langs->trans("PVEDITTABLA"), "edit") . '</a> ');

        print(' <a href="gestionar_tablas.php?ver=' . $aTablasPrecios[$i]->rowid . '" style="padding-left:1px">' . img_picto($langs->trans("PVVERTABLA"), "detail") . '</a> ');

        print(' <a href="gestionar_tablas.php?eliminar=si&del=' . $aTablasPrecios[$i]->rowid . '" style="padding-left:1px" >' . img_picto($langs->trans("PVDELTABLA"), "delete") . '</a> ');
        print('</td>');
        print('</tr>');
    }
    print('</table>');
}
?>
<script>
$(document).ready(function(){
    $("#tablaPrecios").dataTables();
});
</script>
<?php
//--------------fin gestion de tarifas --------------------------------------------------------

$db->close();

llxFooter('$Date: ' . date('d/m/Y H:i', strtotime('now')) . ' $ - $Revision: 1.0 $');
