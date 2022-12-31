<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
session_start();
//!empty($_SESSION['USERNAME']) or die('Access denied!');
include "functions/ParamLibFnc.php";

$url = validateQueryString(curPageURL());
if ($url === FALSE) {
    header('Location: index.php');
}

include 'RedirectRootInc.php';
$start_time = time();

include 'Warehouse.php';

array_rwalk($_REQUEST, 'strip_tags');

$css = getCSS();

//echo "<link rel='stylesheet' type='text/css' href='themes/".strtolower(trim($css))."/".  ucwords(trim($css)).".css'>";
echo '<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/core.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/components.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/colors.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/custom.css" rel="stylesheet" type="text/css">';
echo '<link href="assets/css/extras/css-checkbox-switch.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';
echo "<BODY>";
echo "<script src='js/Validation.js'></script>";
echo "<script src='js/Ajaxload.js'></script>";
echo '<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>';
echo '<script type="text/javascript" src="assets/js/plugins/forms/styling/switchery.min.js"></script>';
echo '<script type="text/javascript" src="assets/js/pages/form_checkboxes_radios.js"></script>';
if (!isset($_REQUEST['_openSIS_PDF'])) {
    Warehouse('header');
    if (strpos(optional_param('modname', '', PARAM_NOTAGS), 'miscellaneous/') === false)
        echo '<DIV id="Migoicons" style="visibility:hidden;position:absolute;z-index:1000;top:-100"></DIV>';
    //echo "<TABLE width=100% border=0 cellpadding=4 height=100%><TR><TD valign=middle height=100% >";
    PopTable_wo_header('header');
}

if (clean_param($_REQUEST['modname'], PARAM_NOTAGS)) {
    if ($_REQUEST['_openSIS_PDF'] == 'true') {
        ob_start();
    }

    if (strpos(optional_param('modname', '', PARAM_NOTAGS), '?') !== false) {

        $modname = substr(optional_param('modname', '', PARAM_NOTAGS), 0, strpos(optional_param('modname', '', PARAM_NOTAGS), '?'));

        $vars = substr(optional_param('modname', '', PARAM_NOTAGS), (strpos(optional_param('modname', '', PARAM_NOTAGS), '?') + 1));

        $vars = explode('?', $vars);
        foreach ($vars as $code) {
            $code = explode('=', $code);
            $_REQUEST[$code[0]] = $code[1];
        }
    } else
        $modname = optional_param('modname', '', PARAM_NOTAGS);


    if (optional_param('LO_save', '', PARAM_INT) != '1' && !isset($_REQUEST['_openSIS_PDF']) && (strpos(optional_param($modname, '', PARAM_NOTAGS), 'miscellaneous/') === false || $modname == 'miscellaneous/Registration.php' || $modname == 'miscellaneous/Export.php' || $modname == 'miscellaneous/Portal.php'))
        $_SESSION['_REQUEST_vars'] = $_REQUEST;

    $allowed = false;
    include 'Menu.php';
    foreach ($_openSIS['Menu'] as $modcat => $programs) {

        if (optional_param('modname', '', PARAM_NOTAGS) == $modcat . '/Search.php') {
            $allowed = true;
            break;
        }
        foreach ($programs as $program => $title) {

            if (optional_param('modname', '', PARAM_NOTAGS) == $program) {
                $allowed = true;
                break;
            }
        }
    }

    if (substr(optional_param('modname', '', PARAM_NOTAGS), 0, 14) == 'miscellaneous/')
        $allowed = true;

    if ($allowed) {
        if (Preferences('SEARCH') != 'Y')
            $_REQUEST['search_modfunc'] = 'list';

        if (preg_match('/\.\./', $modname) !== 1)
            include 'modules/' . $modname;
    }
    else {
        if (User('USERNAME')) {


            if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            echo ""._youReNotAllowedToUseThisProgram."! "._thisAttemptedViolationHasBeenLoggedAndYourIpAddressWasCaptured.".";

            DBQuery("INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','" . optional_param('modname', '', PARAM_NOTAGS) . "','" . User('USERNAME') . "')");
            Warehouse('footer');
            if ($openSISNotifyAddress)
                mail($openSISNotifyAddress, 'HACKING ATTEMPT', "INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','" . optional_param('modname', '', PARAM_NOTAGS) . "','" . User('USERNAME') . "')");
        }
        exit;
    }

    if ($_SESSION['unset_student']) {
        unset($_SESSION['unset_student']);
        unset($_SESSION['staff_id']);
    }
}


if (!isset($_REQUEST['_openSIS_PDF'])) {
    PopTable('footer');
    //echo '</TD></TR></TABLE>';
    for ($i = 1; $i <= $_openSIS['PrepareDate']; $i++) {
        echo '<script type="text/javascript">
    Calendar.setup({
        monthField     :    "monthSelect' . $i . '",
        dayField       :    "daySelect' . $i . '",
        yearField      :    "yearSelect' . $i . '",
        ifFormat       :    "%d-%b-%y",
        button         :    "trigger' . $i . '",
        align          :    "Tl",
        singleClick    :    true
    });
</script>';
    }

    echo '</BODY>';
    echo '</HTML>';
}
