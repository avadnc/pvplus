<?php
/* Copyright (C) 2020 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modBasculasTor_BasculasTorTriggers.class.php
 * \ingroup basculastor
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modBasculasTor_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';
dol_include_once('/pvplus/class/preciovolumen.class.php');

/**
 *  Class of triggers for BasculasTor module
 */
class InterfacePrecioTriggers extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "pvplus";
        $this->description = "Precio triggers.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'dolibarr';
        $this->picto = 'pvplus.png@pvplus';
    }

    /**
     * Trigger name
     *
     * @return string Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * @return string Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "runTrigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param string         $action     Event action code
     * @param CommonObject     $object     Object
     * @param User             $user         Object user
     * @param Translate     $langs         Object langs
     * @param Conf             $conf         Object conf
     * @return int                      <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        if (empty($conf->pvplus->enabled)) {
            return 0;
        }
        // If module is not enabled, we do nothing

        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action

        switch ($action) {
            // Users
            //case 'USER_CREATE':
            //case 'USER_MODIFY':
            //case 'USER_NEW_PASSWORD':
            //case 'USER_ENABLEDISABLE':
            //case 'USER_DELETE':
            //case 'USER_SETINGROUP':
            //case 'USER_REMOVEFROMGROUP':

            // Actions
            //case 'ACTION_MODIFY':
            //case 'ACTION_CREATE':
            //case 'ACTION_DELETE':

            // Groups
            //case 'GROUP_CREATE':
            //case 'GROUP_MODIFY':
            //case 'GROUP_DELETE':

            // Companies
            //case 'COMPANY_CREATE':
            //case 'COMPANY_MODIFY':
            //case 'COMPANY_DELETE':

            // Contacts
            //case 'CONTACT_CREATE':
            //case 'CONTACT_MODIFY':
            //case 'CONTACT_DELETE':
            //case 'CONTACT_ENABLEDISABLE':

            // Products
            //case 'PRODUCT_CREATE':
            //case 'PRODUCT_MODIFY':
            //case 'PRODUCT_DELETE':
            //case 'PRODUCT_PRICE_MODIFY':
            //case 'PRODUCT_SET_MULTILANGS':
            //case 'PRODUCT_DEL_MULTILANGS':

            //Stock mouvement
            //case 'STOCK_MOVEMENT':

            //MYECMDIR
            //case 'MYECMDIR_CREATE':
            //case 'MYECMDIR_MODIFY':
            //case 'MYECMDIR_DELETE':

            // Customer orders
            //case 'ORDER_CREATE':
            //case 'ORDER_MODIFY':
            //case 'ORDER_VALIDATE':
            //case 'ORDER_DELETE':
            //case 'ORDER_CANCEL':
            //case 'ORDER_SENTBYMAIL':
            //case 'ORDER_CLASSIFY_BILLED':
            //case 'ORDER_SETDRAFT':
            //case 'LINEORDER_INSERT':
            //case 'LINEORDER_UPDATE':
            //case 'LINEORDER_DELETE':

            // Supplier orders
            //case 'ORDER_SUPPLIER_CREATE':
            //case 'ORDER_SUPPLIER_MODIFY':
            //case 'ORDER_SUPPLIER_VALIDATE':
            //case 'ORDER_SUPPLIER_DELETE':
            //case 'ORDER_SUPPLIER_APPROVE':
            //case 'ORDER_SUPPLIER_REFUSE':
            //case 'ORDER_SUPPLIER_CANCEL':
            //case 'ORDER_SUPPLIER_SENTBYMAIL':
            //case 'ORDER_SUPPLIER_DISPATCH':
            //case 'LINEORDER_SUPPLIER_DISPATCH':
            //case 'LINEORDER_SUPPLIER_CREATE':
            //case 'LINEORDER_SUPPLIER_UPDATE':
            //case 'LINEORDER_SUPPLIER_DELETE':

            // Proposals
            //case 'PROPAL_CREATE':
            //case 'PROPAL_MODIFY':
            //case 'PROPAL_VALIDATE':
            //case 'PROPAL_SENTBYMAIL':
            //case 'PROPAL_CLOSE_SIGNED':
            //case 'PROPAL_CLOSE_REFUSED':
            //case 'PROPAL_DELETE':
            //case 'LINEPROPAL_INSERT':
            //case 'LINEPROPAL_UPDATE':
            //case 'LINEPROPAL_DELETE':

            // SupplierProposal
            //case 'SUPPLIER_PROPOSAL_CREATE':
            //case 'SUPPLIER_PROPOSAL_MODIFY':
            //case 'SUPPLIER_PROPOSAL_VALIDATE':
            //case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
            //case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
            //case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
            //case 'SUPPLIER_PROPOSAL_DELETE':
            //case 'LINESUPPLIER_PROPOSAL_INSERT':
            //case 'LINESUPPLIER_PROPOSAL_UPDATE':
            //case 'LINESUPPLIER_PROPOSAL_DELETE':

            // Contracts
            //case 'CONTRACT_CREATE':
            //case 'CONTRACT_MODIFY':
            //case 'CONTRACT_ACTIVATE':
            //case 'CONTRACT_CANCEL':
            //case 'CONTRACT_CLOSE':
            //case 'CONTRACT_DELETE':
            //case 'LINECONTRACT_INSERT':
            //case 'LINECONTRACT_UPDATE':
            //case 'LINECONTRACT_DELETE':

            // Bills
            //case 'BILL_CREATE':
            //case 'BILL_MODIFY':
            //case 'BILL_VALIDATE':
            //case 'BILL_UNVALIDATE':
            //case 'BILL_SENTBYMAIL':
            //case 'BILL_CANCEL':
            //case 'BILL_DELETE':
            //case 'BILL_PAYED':
            case 'LINEBILL_INSERT':

                dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);

                /* Validamos si ya trae descuento */
                if($object->remise_percent != 0) {

                break;
                }
                $factura = new Facture($this->db);
                $factura->fetch($object->fk_facture);

                $PRV = new Precio_Volumen($this->db);
                $nuevo_descuento = $PRV->Obtener_Precio_Cantidad($factura->socid, $object->fk_product, $object->qty);
                if ($nuevo_descuento == false && $conf->categorie->enabled) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad2($factura->socid, $object->qty);
                }

                if ($nuevo_descuento == false) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad1($object->fk_product, $object->qty);
                }

                if ($nuevo_descuento == false && $conf->categorie->enabled) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad3($object->fk_product, $object->qty);
                }

                if (!$nuevo_descuento == false) {
                    $product = new Product($this->db);
                    $product->fetch($object->fk_product);
                    $societe = new Societe($this->db);
                    $societe->fetch($factura->socid);
                    $precio = 0;
                    $precio_level_cliente = $societe->price_level;

                    if ($product->price_base_type == 'TTC') {
                        if (isset($precio_level_cliente) && $conf->global->PRODUIT_MULTIPRICES) {
                            $precio = $product->multiprices_ttc[$precio_level_cliente];
                            $precio_unit = $product->multiprices[$precio_level_cliente];
                            
                        }elseif(!empty($conf->global->PRODUIT_CUSTOMER_PRICES)){
                                                                                   
                                require_once DOL_DOCUMENT_ROOT.'/product/class/productcustomerprice.class.php';

                                $prodcustprice = new Productcustomerprice($this->db);

                                $filter = array('t.fk_product' => $product->id, 't.fk_soc' => $factura->socid);

                                $result = $prodcustprice->fetch_all('', '', 0, 0, $filter);
                                if ($result) {
                                    if (count($prodcustprice->lines) > 0) {
                                        $found = true;
                                        $precio_unit = price($prodcustprice->lines [0]->price);
                                        $precio = price($prodcustprice->lines [0]->price_ttc);
                                        
                                    }
                                }
                            }else {
                                $precio = price($product->price_ttc);
                                $precio_unit = price($product->price);
                                
                            }
                        
                        $price_base_type = 'TTC';

                    } else {
                        if (isset($precio_level_cliente) && $conf->global->PRODUIT_MULTIPRICES) {
                            
                            $precio = $product->multiprices[$precio_level_cliente];
                            $precio_unit = price($precio);

                        }elseif(!empty($conf->global->PRODUIT_CUSTOMER_PRICES)){
                                                                                   
                                require_once DOL_DOCUMENT_ROOT.'/product/class/productcustomerprice.class.php';

                                $prodcustprice = new Productcustomerprice($this->db);

                                $filter = array('t.fk_product' => $product->id, 't.fk_soc' => $factura->socid);

                                $result = $prodcustprice->fetch_all('', '', 0, 0, $filter);
                                if ($result) {
                                    if (count($prodcustprice->lines) > 0) {
                                        $found = true;
                                        $precio_unit = price($prodcustprice->lines [0]->price);
                                        $precio = price($prodcustprice->lines [0]->price);
                                        
                                    }
                                }
                        } else {

                            $precio = price($product->price);
                            $precio_unit = price($product->price);

                        }
                        $price_base_type = 'HT';
                    }
                    //------------------------------------
                    if ($nuevo_descuento['tipo'] == 'descuento') {
                        $precio = price2num($precio);
                        $descuento = price2num($nuevo_descuento['descuento']);
                        $costonormal = ($precio * $descuento) / 100;
                        $nuevo_precio = $precio - $costonormal;
                        // $precio_unit = $precio;
                    } else {
                        $nuevo_precio = $precio - $nuevo_descuento['descuento'];
                    }

                    $pu = price2num($nuevo_precio);
                    //------------------------------------


                    $tabprice = calcul_price_total($object->qty, $pu, ($object->remise_percent), $object->tva_tx, $object->localtax1_tx, $object->localtax2, 0, $price_base_type, $object->info_bits, ($product->isproduct() ? 0 : 1));


                    $total_ht = $tabprice[0];
                    $total_tva = $tabprice[1];
                    $total_ttc = $tabprice[2];
                    $total_localtax1 = $tabprice[9];
                    $total_localtax2 = $tabprice[10];
                    $pu_ht = $precio_unit? $precio_unit:$tabprice[3];
                    $pu_tva = $tabprice[4];
                    $pu_ttc = $tabprice[5];

                    $price = $pu;
                    $remise = 0;
                    $remise_percent = $object->remise_percent;
                    if ($remise_percent > 0) {
                        $remise = round(($pu * $remise_percent / 100), 2);
                        $price = ($pu - $remise);
                    } else {
                        //calcular el porcentaje de descuento para las facturas fiscales
                        // $descuento = ($total_ht * 100 / $object->total_ht) - 100;
                        $descuento = round(abs($descuento), 2);
                    }

                    $price = price2num($price);

                    $object->tva_tx = $object->tva_tx;
                    if ($remise_percent != 0) {
                        $object->remise_percent = $remise_percent;
                    } else {
                        $object->remise_percent = $descuento;
                    }
                    $object->subprice = ($factura->type == 2 ? -1 : 1) * abs($pu_ht);

                    $object->total_ht = ($factura->type == 2 ? -1 : 1) * abs($total_ht);
                    $object->total_tva = ($factura->type == 2 ? -1 : 1) * abs($total_tva);
                    $object->total_localtax1 = ($factura->type == 2 ? -1 : 1) * abs($total_localtax1);
                    $object->total_localtax2 = ($factura->type == 2 ? -1 : 1) * abs($total_localtax2);
                    $object->total_ttc = ($factura->type == 2 ? -1 : 1) * abs($total_ttc);


                    $object->multicurrency_subprice = ($factura->type == 2 ? -1 : 1) * abs($pu_ht);
                    $object->multicurrency_total_ht = ($factura->type == 2 ? -1 : 1) * abs($total_ht);
                    $object->multicurrency_total_tva = ($factura->type == 2 ? -1 : 1) * abs($total_tva);
                    $object->multicurrency_total_ttc = ($factura->type == 2 ? -1 : 1) * abs($total_ttc);

                    // A ne plus utiliser
                    //$object->price = $price;
                    $object->remise = $remise;

                    $resultado = $object->update();
                    if ($resultado > 0) {
                        $factura->update_price();

                    }
                }


                break;

            case 'LINEBILL_UPDATE':
                dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);

                /* Validamos si ya trae descuento */

                $factura = new Facture($this->db);
                $factura->fetch($object->fk_facture);

                $PRV = new Precio_Volumen($this->db);
                $nuevo_descuento = $PRV->Obtener_Precio_Cantidad($factura->socid, $object->fk_product, $object->qty);
                if ($nuevo_descuento == false && $conf->categorie->enabled) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad2($factura->socid, $object->qty);
                }

                if ($nuevo_descuento == false) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad1($object->fk_product, $object->qty);
                }

                if ($nuevo_descuento == false && $conf->categorie->enabled) {
                    $nuevo_descuento = $PRV->Obtener_Precio_Cantidad3($object->fk_product, $object->qty);
                }

                if($object->remise_percent == $nuevo_descuento['descuento']) {
                    
                     break;

                }

                if ($nuevo_descuento == true) {
                    $product = new Product($this->db);
                    $product->fetch($object->fk_product);
                    $societe = new Societe($this->db);
                    $societe->fetch($factura->socid);
                    $precio = 0;
                    $precio_level_cliente = $societe->price_level;

                    if ($product->price_base_type == 'TTC') {
                        if (isset($precio_level_cliente) && $conf->global->PRODUIT_MULTIPRICES) {
                            $precio = $product->multiprices_ttc[$precio_level_cliente];
                            $precio_unit = $product->multiprices[$precio_level_cliente];
                        
                        }elseif(!empty($conf->global->PRODUIT_CUSTOMER_PRICES)){
                                                                                   
                                require_once DOL_DOCUMENT_ROOT.'/product/class/productcustomerprice.class.php';

                                $prodcustprice = new Productcustomerprice($this->db);

                                $filter = array('t.fk_product' => $product->id, 't.fk_soc' => $factura->socid);

                                $result = $prodcustprice->fetch_all('', '', 0, 0, $filter);
                                if ($result) {
                                    if (count($prodcustprice->lines) > 0) {
                                        $found = true;
                                        $precio_unit = price($prodcustprice->lines [0]->price);
                                        $precio = price($prodcustprice->lines [0]->price_ttc);
                                        
                                    }
                                }

                        } else {
                            $precio = price($product->price_ttc);
                            $precio_unit = price($product->price);
                            
                        }
                        $price_base_type = 'TTC';
                        
                    } else {
                        if (isset($precio_level_cliente) && $conf->global->PRODUIT_MULTIPRICES) {
                            $precio = $product->multiprices[$precio_level_cliente];
                            $precio_unit = price($precio);

                        }elseif(!empty($conf->global->PRODUIT_CUSTOMER_PRICES)){
                                                                                   
                                require_once DOL_DOCUMENT_ROOT.'/product/class/productcustomerprice.class.php';

                                $prodcustprice = new Productcustomerprice($this->db);

                                $filter = array('t.fk_product' => $product->id, 't.fk_soc' => $factura->socid);

                                $result = $prodcustprice->fetch_all('', '', 0, 0, $filter);
                                if ($result) {
                                    if (count($prodcustprice->lines) > 0) {
                                        $found = true;
                                        $precio_unit = price($prodcustprice->lines [0]->price);
                                        $precio = price($prodcustprice->lines [0]->price);
                                        
                                    }
                                }
                                
                        } else {
                            $precio = price($product->price);
                            $precio_unit = price($product->price);
                        }
                        $price_base_type = 'HT';
                    }
                    //------------------------------------
                    if ($nuevo_descuento['tipo'] == 'descuento') {
                        $precio = price2num($precio);
                        $descuento = price2num($nuevo_descuento['descuento']);
                        $costonormal = ($precio * $descuento) / 100;
                        $nuevo_precio = $precio - $costonormal;
                        // $precio_unit = $precio;
                    } else {
                        $nuevo_precio = $precio - $nuevo_descuento['descuento'];
                    }

                    $pu = price2num($nuevo_precio);
                    //------------------------------------

                   
                    $tabprice = calcul_price_total($object->qty, $pu, 0, $object->tva_tx, $object->localtax1_tx, $object->localtax2, 0, $price_base_type, $object->info_bits, ($product->isproduct() ? 0 : 1));

                    $total_ht = $tabprice[0];
                    $total_tva = $tabprice[1];
                    $total_ttc = $tabprice[2];
                    $total_localtax1 = $tabprice[9];
                    $total_localtax2 = $tabprice[10];
                    $pu_ht = $precio_unit? $precio_unit:$tabprice[3];
                    $pu_tva = $tabprice[4];
                    $pu_ttc = $tabprice[5];


                    if($object->subprice == $pu_ht) {
                    
                     //break;

                    }

                    $price = $pu;
                    $remise = 0;
                    $remise_percent = $descuento;
                    if ($remise_percent > 0) {
                       $descuento = round(abs($descuento), 2);
                      
                    } else {

                        $remise = round(($pu * $remise_percent / 100), 2);
                        $price = ($pu - $remise);
                    }

                    $price = price2num($price);

                    $object->tva_tx = $object->tva_tx;
                    if ($remise_percent != 0) {
                        $object->remise_percent = $descuento;
                    } else {
                        $object->remise_percent = $remise_percent;
                    }
                    $object->subprice = ($factura->type == 2 ? -1 : 1) * abs($pu_ht);

                    $object->total_ht = ($factura->type == 2 ? -1 : 1) * abs($total_ht);
                    $object->total_tva = ($factura->type == 2 ? -1 : 1) * abs($total_tva);
                    $object->total_localtax1 = ($factura->type == 2 ? -1 : 1) * abs($total_localtax1);
                    $object->total_localtax2 = ($factura->type == 2 ? -1 : 1) * abs($total_localtax2);
                    $object->total_ttc = ($factura->type == 2 ? -1 : 1) * abs($total_ttc);


                    $object->multicurrency_subprice = ($factura->type == 2 ? -1 : 1) * abs($pu_ht);
                    $object->multicurrency_total_ht = ($factura->type == 2 ? -1 : 1) * abs($total_ht);
                    $object->multicurrency_total_tva = ($factura->type == 2 ? -1 : 1) * abs($total_tva);
                    $object->multicurrency_total_ttc = ($factura->type == 2 ? -1 : 1) * abs($total_ttc);


                    // A ne plus utiliser
                    //$object->price = $price;
                    $object->remise = $remise;

                    $resultado = $object->update();
                    if ($resultado > 0 && $resultado < 2) {
                        $factura->update_price();

                    }
                }

                 break;

            //case 'LINEBILL_DELETE':

            //Supplier Bill
            //case 'BILL_SUPPLIER_CREATE':
            //case 'BILL_SUPPLIER_UPDATE':
            //case 'BILL_SUPPLIER_DELETE':
            //case 'BILL_SUPPLIER_PAYED':
            //case 'BILL_SUPPLIER_UNPAYED':
            //case 'BILL_SUPPLIER_VALIDATE':
            //case 'BILL_SUPPLIER_UNVALIDATE':
            //case 'LINEBILL_SUPPLIER_CREATE':
            //case 'LINEBILL_SUPPLIER_UPDATE':
            //case 'LINEBILL_SUPPLIER_DELETE':

            // Payments
            //case 'PAYMENT_CUSTOMER_CREATE':
            //case 'PAYMENT_SUPPLIER_CREATE':
            //case 'PAYMENT_ADD_TO_BANK':
            //case 'PAYMENT_DELETE':

            // Online
            //case 'PAYMENT_PAYBOX_OK':
            //case 'PAYMENT_PAYPAL_OK':
            //case 'PAYMENT_STRIPE_OK':

            // Donation
            //case 'DON_CREATE':
            //case 'DON_UPDATE':
            //case 'DON_DELETE':

            // Interventions
            //case 'FICHINTER_CREATE':
            //case 'FICHINTER_MODIFY':
            //case 'FICHINTER_VALIDATE':
            //case 'FICHINTER_DELETE':
            //case 'LINEFICHINTER_CREATE':
            //case 'LINEFICHINTER_UPDATE':
            //case 'LINEFICHINTER_DELETE':

            // Members
            //case 'MEMBER_CREATE':
            //case 'MEMBER_VALIDATE':
            //case 'MEMBER_SUBSCRIPTION':
            //case 'MEMBER_MODIFY':
            //case 'MEMBER_NEW_PASSWORD':
            //case 'MEMBER_RESILIATE':
            //case 'MEMBER_DELETE':

            // Categories
            //case 'CATEGORY_CREATE':
            //case 'CATEGORY_MODIFY':
            //case 'CATEGORY_DELETE':
            //case 'CATEGORY_SET_MULTILANGS':

            // Projects
            //case 'PROJECT_CREATE':
            //case 'PROJECT_MODIFY':
            //case 'PROJECT_DELETE':

            // Project tasks
            //case 'TASK_CREATE':
            //case 'TASK_MODIFY':
            //case 'TASK_DELETE':

            // Task time spent
            //case 'TASK_TIMESPENT_CREATE':
            //case 'TASK_TIMESPENT_MODIFY':
            //case 'TASK_TIMESPENT_DELETE':
            //case 'PROJECT_ADD_CONTACT':
            //case 'PROJECT_DELETE_CONTACT':
            //case 'PROJECT_DELETE_RESOURCE':

            // Shipping
            //case 'SHIPPING_CREATE':
            //case 'SHIPPING_MODIFY':
            //case 'SHIPPING_VALIDATE':
            //case 'SHIPPING_SENTBYMAIL':
            //case 'SHIPPING_BILLED':
            //case 'SHIPPING_CLOSED':
            //case 'SHIPPING_REOPEN':
            //case 'SHIPPING_DELETE':

            // and more...

            default:
                dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);
                break;
        }

        return 0;
    }
}
