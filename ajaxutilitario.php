<?php
/*   Copyright (C) 2015 - 2016 Alexis José Turruella Sánchez
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia a la cantidad de compra
Fichero ajaxutilitario.php
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

dol_include_once("/pvplus/lib/JSON.php");
dol_include_once("/pvplus/class/preciovolumen.class.php");

$langs->load("products");
$langs->load("preciovolumen@pvplus");
$opcion = $_POST['opcion'];
if ($conf->pvplus->enabled) {
    $obj_pv = new Precio_Volumen($db);
    switch ($opcion) {
        case 0:
            {
                $json = new JSON();
                $tarifas = $obj_pv->Tarifas_De_Producto($_POST['producto']);
                if ($tarifas) {
                    print '<select name="producto" id="producto">';
                    for ($i = 0; $i < sizeof($tarifas); $i++) {
                        print '<option value="' . $tarifas[$i][0] . '">' . $tarifas[$i][1] . '</option>';
                    }

                    print '</select>';
                } else {
                    print -1;
                }

                break;
            }
        case 1:
            {
                $json = new JSON();
                $objTablaPrecio = new tabla_precios($db);
                $objTablaPrecio->fetch($_POST['id']);
                $detalles = $obj_pv->Detalles_Tarifas($_POST['id']);
                $i = 0;
                print "<div class='info'>";
                print "<ol style='padding-left:10px;'>";
                print '<legend>';
                print $langs->trans("Type") . ': ' . $objTablaPrecio->label_tipo_table();
                print '</legend>';
                foreach ($detalles as $d) {
                    print "<li>";
                    print " " . $langs->trans("DE");
                    print " " . $d->cantidad_inferior;
                    print " " . $langs->trans("HASTA");
                    print " " . $d->cantidad_superior;
                    print ". " . $objTablaPrecio->label_tipo_table();
                    print ": <b>" . $d->descuento . "</b>";
                    print "</li>";
                }
                print "</ol>";
                print "</div>";

                break;
            }
        case 2:
            {
                $json = new JSON();
                $detalles = $obj_pv->Detalles_Tarifas($_POST['tabla']);
                $objTablaPrecio = new tabla_precios($db);
                $objTablaPrecio->fetch($_POST['tabla']);
                $arreglo_datos = array();
                $i = 0;
                $cantidad = $_POST['cant'];
                //-------------------------------------------------
                require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
                require_once DOL_DOCUMENT_ROOT . "/societe/class/societe.class.php";
                $product = new Product($db);
                $product->fetch($_POST["id_producto"]);
                $societe = new Societe($db);
                $societe->fetch($_POST['socid']);
                $precio = 0;
                $precio_level_cliente = $societe->price_level;

                if (empty($object->multiprices_base_type[1])) $object->multiprices_base_type[1] = "HT";
                if ($conf->global->PRODUIT_MULTIPRICES){
                 $price_base = $product->multiprices_base_type[1];
                }else{
                  $price_base =$product->price_base_type;
                }
                if ($price_base == 'TTC') {
                    if ($conf->global->PRODUIT_MULTIPRICES) {
                        $precio = $product->multiprices_ttc[1];
                       $precio = price($precio);
                    } else {
                       $precio = price($product->price_ttc);
                    }

                } else {
                    if ($conf->global->PRODUIT_MULTIPRICES) {
                        $precio = $product->multiprices[1];
                        $precio = price($precio);
                    } else {
                        $precio = price($product->price);
                    }
                }
                //-------------------------------------------------
                foreach ($detalles as $d) {
                    if ($d->cantidad_inferior <= $cantidad && $d->cantidad_superior >= $cantidad) {
                        print $langs->trans("SellingPrice");
                        print ": $precio <br/>";
                        print $langs->trans("Quantity");
                        print ": $cantidad <br/>";
                        if ($objTablaPrecio->tipo == 'descuento') {
                            print $langs->trans("Descuento");
                            print ": " . $d->descuento . "%<br/>";
                            $precio = price2num($precio);
                            $descuento = price2num($d->descuento);
                            $costonormal = ($precio * $descuento) / 100;

                            $nuevo_precio = $precio - $costonormal;
                            $nuevo_precio = ($nuevo_precio);
                        } else {

                            $nuevo_precio = $d->descuento;
                        }
                        print $langs->trans("NuevoPrecio");
                        print ": " . $nuevo_precio . " <br/>";
                        print $langs->trans("PVCOSTOTOTAL");

                        $desc = ($cantidad * $nuevo_precio);

                        print ": $" . $desc;
                        /*
                        $costonormal=$cantidad*$precio;
                        $desc=($d->descuento*$costonormal)/100;
                        $desc=($costonormal-$desc);
                        print ": $".$desc;
                         */
                        return;
                    }
                    $i++;
                }
                print $langs->trans("PVNOEXISTERANGO") . ' ' . $cantidad;
                break;
            }
        case 3:
            {
                $json = new JSON();

                $detalles = $obj_pv->Detalles_Tarifas($_POST['id']);
                $i = 0;
                foreach ($detalles as $d) {
                    $arreglo[$i]['id'] = $d->id;
                    $arreglo[$i]['nombre'] = $d->nombre;
                    $arreglo[$i]['descuento'] = $d->descuento;
                    $arreglo[$i]['cantidad_inferior'] = $d->cantidad_inferior;
                    $arreglo[$i]['cantidad_superior'] = $d->cantidad_superior;
                    $i++;
                }
                print $json->encode($arreglo);

                break;
            }
        case 4:
            {

                $json = new JSON();
                $detalles = $obj_pv->Detalles_Tarifas($_POST['tabla']);
                $objTablaPrecio = new tabla_precios($db);
                $objTablaPrecio->fetch($_POST['tabla']);
                $arreglo_datos = array();
                $i = 0;
                $cantidad = $_POST['cant'];
                //-------------------------------------------------
                require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
                $product = new Product($db);
                $product->fetch($_POST["id_producto"]);
                $precio = 0;

                if (empty($object->multiprices_base_type[1])) $object->multiprices_base_type[1] = "HT";
                if ($conf->global->PRODUIT_MULTIPRICES){
                 $price_base = $product->multiprices_base_type[1];
                }else{
                  $price_base =$product->price_base_type;
                }
                if ($price_base == 'TTC') {
                    if ($conf->global->PRODUIT_MULTIPRICES) {
                        $precio = $product->multiprices_ttc[1];
                       $precio = price($precio);
                    } else {
                       $precio = price($product->price_ttc);
                    }

                } else {
                    if ($conf->global->PRODUIT_MULTIPRICES) {
                        $precio = $product->multiprices[1];
                        $precio = price($precio);
                    } else {
                        $precio = price($product->price);
                    }
                }

                //-------------------------------------------------
                foreach ($detalles as $d) {
                    if ($d->cantidad_inferior <= $cantidad && $d->cantidad_superior >= $cantidad) {
                        print $langs->trans("SellingPrice");
                        print ": $precio \n";
                        print $langs->trans("Quantity");
                        print ": $cantidad \n";
                        if ($objTablaPrecio->tipo == 'descuento') {
                            print $langs->trans("Descuento");
                            print ": " . $d->descuento . "%\n";
                            print $langs->trans("NuevoPrecio");

                            $precio = price2num($precio);
                            $descuento = price2num($d->descuento);
                            $costonormal = ($precio * $descuento) / 100;

                            $nuevo_precio = $precio - $costonormal;
                            $nuevo_precio = ($nuevo_precio);
                            print ": " . price2num($nuevo_precio);
                            $desc = ($cantidad * $nuevo_precio);
                        } else {
                            print $langs->trans("NuevoPrecio");
                            print ': ' . price2num($precio - $d->descuento);
                            $desc = price2num($cantidad *($precio - $d->descuento));
                        }
                        print " \n";
                        print $langs->trans("PVCOSTOTOTAL");
                        print ": " . $desc;
                        /*
                        $costonormal=$cantidad*$precio;
                        $desc=($d->descuento*$costonormal)/100;
                        $desc=($costonormal-$desc);
                        print ": $".$desc;
                         */
                        return;
                    }
                    $i++;
                }
                print $langs->trans("PVNOEXISTERANGO") . ' ' . $cantidad;
                break;
            }
    }
}
