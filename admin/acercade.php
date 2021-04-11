<?php
/*   Copyright (C) 2015 - 2016 Alexis José Turruella Sánchez
Correo electrónico: alexturruella@gmail.com
Módulo para la gestión del precios del producto en correspondencia a la cantidad de compra
Fichero admin/acercade.php
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

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

dol_include_once('/pvplus/lib/pvplus.lib.php');



$langs->load("admin");
$langs->load("install");
$langs->load("errors");
$langs->load("preciovolumen@pvplus");

if (!$user->admin) {
    accessforbidden();
}

$value = GETPOST('value', 'alpha');
$action = GETPOST('action', 'alpha');

$title = $langs->trans("PVPLUS");
llxHeader("", $title);

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
load_fiche_titre($title, $linkback, 'setup');
$head = pvplus_admin_prepare_head();

dol_fiche_head($head, 'acercade', $langs->trans('PVPLUS'), 0, 'preciovolumen@pvplus');
print '<br/>';
print $langs->trans('acerca_del_modulo');
print '<br/>';
print '<br/>';
$form = new Form($db);

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
print '<td align="right">' . $langs->trans("Value") . '</td>' . "\n";
print '</tr>' . "\n";

$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>';
print img_object($langs->trans('pvplus'), 'pvplus.png@pvplus');
print '&nbsp;';
print $langs->trans("Version");
print '</td>';
print '<td width="60%" align="right">';
print '5.0';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("PHPVersion") . '</td>';
print '<td align="right">';
print '5.3 o superior';
print '</td>';
print '</tr>';
$var = !$var;

print '<tr ' . $bc[$var] . '>';
print '<td>';
print 'Dolibarr';
print '</td>';
print '<td align="right">';
print '10 o Superior';
print '</td>';
print '</tr>';
print '</table>';

print '<br/>';
print '<br/>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="3"><h2>Desarrolladores</h2></td>' . "\n";
print '</tr>' . "\n";
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td rowspan="6" width="10%"><img src="../img/perfilvives.jpg" width="100"/></td>';
print '<td width="20%">' . $langs->trans("Name") . '</td>';
print '<td width="70%" align="left">';
print 'Alex Vives Alcazar';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Phone") . '</td>';
print '<td align="left">';
print '+52 (331) 702 5248';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Email") . '</td>';
print '<td align="left">';
print 'gerencia@vivescloud.com';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Web") . '</td>';
print '<td align="left">';
print '<a href="https://www.vivescloud.com/">www.vivescloud.com</a>';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>Linkedin</td>';
print '<td align="left">';
print '<a href="https: //www.linkedin.com/in/alex-vives-alcazar-696b1855/" target="_blank"><img src="../img/link.png" width="20"/></a>';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>YouTube</td>';
print '<td align="left">';
print '<a href="https: //www.youtube.com/channel/UCQ0vBJv7M1eB0tAxdBvwboQ" target="_blank"><img src="../img/youtube.png" width="20"></a>';
print '</td>';
print '</tr>';
print '<tr><td colspan="3"><hr></td></tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td rowspan="6" width="10%"><img src="../img/perfil.jpg" width="100"/></td>';
print '<td width="20%">' . $langs->trans("Name") . '</td>';
print '<td width="70%" align="left">';
print 'Alexis José Turruella Sánchez';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Phone") . '</td>';
print '<td align="left">';
print '(593)  0995290847';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Email") . '</td>';
print '<td align="left">';
print 'alexturruella@gmail.com';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>' . $langs->trans("Web") . '</td>';
print '<td align="left">';
print 'www.valecloud.com';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>Linkedin</td>';
print '<td align="left">';
print '<a href="https://ec.linkedin.com/in/alexisturruella" target="_blank"><img src="../img/link.png" width="20"/></a>';
print '</td>';
print '</tr>';
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>Google+</td>';
print '<td align="left">';
print '<a href="https://plus.google.com/u/0/+AlexisJoseTurruellaSánchez/" target="_blank"><img src="../img/google+.png" width="20"></a>';
print '</td>';
print '</tr>';
print '</table>';

dol_fiche_end();
$db->close();
llxFooter();
