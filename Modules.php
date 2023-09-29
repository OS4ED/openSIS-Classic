<?php

#**************************************************************************
#  openSIS is a free student information system for publirc and non-public 
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

include 'RedirectRootInc.php';
include 'functions/ParamLibFnc.php';
include 'functions/SqlSecurityFnc.php';

define('students', 'students');
define('users', 'users');
define('scheduling', 'scheduling');
define('grades', 'grades');
define('attendance', 'attendance');
define('messaging', 'messaging');
define('tools', 'tools');

if (isset($_REQUEST['year_id']))
    $_REQUEST['year_id'] = sqlSecurityFilter($_REQUEST['year_id'], 'no');

$url = validateQueryString(curPageURL());
if ($url === FALSE) {
    header('Location: index.php');
}
if ($_REQUEST['modname'] == 'grades/Assignments.php' && $_REQUEST['assignment_id'] != '' && isset($_REQUEST['tables'][$_REQUEST['assignment_id']]['DESCRIPTION'])) {
    $_SESSION['ASSIGNMENT_DESCRIPTION'] = $_REQUEST['tables'][$_REQUEST['assignment_id']]['DESCRIPTION'];
}
$isajax = "modules";
$btn = optional_param('btn', '', PARAM_ALPHA);
if ($btn == 'Update' || $btn == '') {
    $btn = 'old';
}
$nsc = optional_param('nsc', '', PARAM_SPCL);
if ($_REQUEST['new_school'] != 'true') {
    $ns = "NT";
} else {
    $ns = "TT";
}

$handle = opendir("js");
$filelst = '';
while ($file = readdir($handle)) {
    $filelst = "$filelst,$file";
}
closedir($handle);
$filelist = explode(",", $filelst);

if (count($filelist) > 3) {
    for ($count = 1; $count < count($filelist); $count++) {
        $filename = $filelist[$count];
        if (($filename != ".") && ($filename != "..") && ($filename != ""))
            echo "<script src='js/" . $filename . "'></script>";
    }
}
echo "<noscript><META http-equiv=REFRESH content='0;url=EnableJavascript.php' /></noscript>";
$module_commit_in   =   "";
$module_commit_out  =   "";

$start_time = time();
include 'Warehouse.php';
include('lang/language.php');
// echo _NAME;
$old_school = UserSchool();
$old_syear = UserSyear();
if ((!$_SESSION['UserMP'] || (optional_param('school', '', PARAM_SPCL) && optional_param('school', '', PARAM_SPCL) != $old_school) || (optional_param('syear', 0, PARAM_SPCL) && optional_param('syear', 0, PARAM_SPCL) != $old_syear)) && User('PROFILE') != 'parent')
    $_SESSION['UserMP'] = GetCurrentMP('QTR', DBDate());

array_rwalk($_REQUEST, 'strip_tags');

if (!isset($_REQUEST['_openSIS_PDF'])) {
    Warehouse('header');
    $css = trim(getCSS());

    /*
     * Include Stylesheets
     */
    echo '<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/core.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';
    echo '<link href="assets/js/plugins/pickers/bootstrap-datepicker/css/bootstrap-datepicker.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';
    echo '<link href="assets/js/plugins/pickers/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/components.css?v=1.2" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/colors.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/custom.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';
    echo '<link href="assets/css/extras/css-checkbox-switch.css?v=' . rand(0000, 99999) . '" rel="stylesheet" type="text/css">';

    /*
     * Include Javascript Core Files
     */
    echo '<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/core/libraries/jquery.mousewheel.js"></script>';
    echo '<script type="text/javascript" src="assets/js/core/libraries/jquery_ui/interactions.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/loaders/blockui.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/ui/prism.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/media/cropper.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/editors/ckeditor/ckeditor.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/tables/datatables/datatables.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/forms/selects/select2.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/ui/nicescroll.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/forms/styling/uniform.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/forms/styling/switchery.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/ui/moment/moment.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/pickers/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/pickers/clockpicker/bootstrap-clockpicker.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/extensions/cookie.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/notifications/jgrowl.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/notifications/noty.min.js"></script>';

    // JS for Schoolwide Schedule Report
    echo '<script type="text/javascript" src="assets/js/plugins/table2excel/table2excel.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/jspdf/jspdf.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/jspdf/autotable/jspdf.plugin.autotable.min.js"></script>';

    /* JS Initializers */
    echo '<script type="text/javascript" src="assets/js/core/app.js?v=' . rand(0000, 99999) . '"></script>';
    echo '<script type="text/javascript" src="assets/js/pages/components_popups.js"></script>';
    echo '<script type="text/javascript" src="assets/js/plugins/ui/ripple.min.js"></script>';
    echo '<script type="text/javascript" src="assets/js/pages/form_select2.js"></script>';
    echo '<script type="text/javascript" src="assets/js/pages/picker_date.js"></script>';
    echo '<script type="text/javascript" src="assets/js/pages/form_checkboxes_radios.js"></script>';
    echo '<script type="text/javascript" src="js/Custom.js?v=' . rand(0000, 99999) . '"></script>';
    echo '<script type="text/javascript">
        $(function () {
            $(\'#loading-image\').hide();
            $("body").on("click", "div.sidebar-overlay", function () {
                $("body").toggleClass("sidebar-mobile-main");
            });
            
            if($(".clockpicker").length>0){
                $(".clockpicker").clockpicker({ 
                    twelvehour: true,
                    donetext: \'Done\'
                }).find("input").change(function () {
                   //alert(this.value);
                });
            }
        });
    </script>';


    if (strpos($_REQUEST['modname'], 'miscellaneous/') === false)
        echo '<script language="JavaScript">if(window == top  && (!window.opener || window.opener.location.href.substring(0,(window.opener.location.href.indexOf("&")!=-1?window.opener.location.href.indexOf("&"):window.opener.location.href.replace("#","").length))!=window.location.href.substring(0,(window.location.href.indexOf("&")!=-1?window.location.href.indexOf("&"):window.location.href.replace("#","").length)))) window.location.href = "index.php";</script>';

    echo "<BODY>";
}

echo '<input id="cframe" type="hidden" value="">';

echo '<div id="loading-image"><i class="fa fa-cog fa-spin fa-lg fa-fw"></i> ' . _loading . '...</div>';

echo '<div class="navbar navbar-inverse bg-white">
            <div class="navbar-header">
                <a class="sidebar-control sidebar-main-toggle hidden-xs" data-popup="tooltip" data-placement="bottom" data-container="body" data-original-title="Collapse Menu"><i class="icon-paragraph-justify3"></i></a>
                <a class="navbar-brand" href="javascript:void(0)" onclick="check_content(\'Ajax.php?modname=miscellaneous/Portal.php\');" onmousedown="document.getElementById(\'header\').innerHTML = \'Home\'; document.getElementById(\'cframe\').src = \'Bottom.php?modcat=home\'"><img src="assets/opensis_logo.png" alt=""></a>

                <ul class="nav navbar-nav visible-xs-block">
                    <li><a data-toggle="collapse" data-target="#navbar-mobile" data-container="body"><i class="icon-grid2"></i></a></li>
                    <li><a class="sidebar-mobile-main-toggle" data-container="body"><i class="icon-paragraph-justify3"></i></a></li>
                </ul>
            </div>

            <div class="navbar-collapse collapse" id="navbar-mobile">
                <ul class="nav navbar-nav  hidden-xs">
                    <li></li>
                </ul>


                <ul class="nav navbar-nav navbar-right">';

if (User('PROFILE') == 'teacher') {
    echo "<li><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=school method=POST><div class=\"form-group\"><INPUT type=hidden name=modcat value='' id=modcat_input>";
    $RET = DBGet(DBQuery('SELECT s.ID,s.TITLE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=\'' . UserSyear() . '\' AND st.staff_id=\'' . $_SESSION['STAFF_ID'] . '\' AND (ssr.END_DATE>=curdate() OR ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL)'));
    echo "<SELECT class=\"select-search\" style=\"width: 200px;\" name=school onChange='this.form.submit();'>";
    foreach ($RET as $school) {
        echo "<OPTION value=$school[ID]" . ((UserSchool() == $school['ID']) ? ' SELECTED' : '') . ">" . $school['TITLE'] . "</OPTION>";
    }
    echo "</SELECT>";
    echo "</div></FORM></li>";

    //===================================================================================================

    echo "<li><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=syear method=POST><div class=\"form-group\"><INPUT type=hidden name=modcat value='' id=modcat_input>";
    $school_years_RET = DBGet(DBQuery("SELECT YEAR(sy.START_DATE)AS START_DATE,YEAR(sy.END_DATE)AS END_DATE FROM school_years sy,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.SYEAR=sy.SYEAR AND sy.school_id=ssr.school_id AND sy.school_id=" . UserSchool() . " AND st.staff_id=$_SESSION[STAFF_ID]"));
    echo "<SELECT class=\"select\" name=syear onChange='this.form.submit();' style='width:80;'>";
    foreach ($school_years_RET as $school_years) {
        echo "<OPTION value=$school_years[START_DATE]" . ((UserSyear() == $school_years['START_DATE']) ? ' SELECTED' : '') . ">$school_years[START_DATE]" . ($school_years['END_DATE'] != $school_years['START_DATE'] ? "-" . $school_years['END_DATE'] : '') . '</OPTION>';
    }
    echo '</SELECT>';
    echo "</div></FORM></li>";

    //===================================================================================================

    echo "<li><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=mp method=POST><div class=\"form-group\"><INPUT type=hidden name=modcat value='' id=modcat_input>";
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
    if (!isset($_SESSION['UserMP'])) {
        $_SESSION['UserMP'] = GetCurrentMP('QTR', DBDate());
        $allMP = 'QTR';
    }
    if (!$RET) {
        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (!isset($_SESSION['UserMP'])) {
            $_SESSION['UserMP'] = GetCurrentMP('SEM', DBDate());
            $allMP = 'SEM';
        }
    }
    if (!$RET) {
        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (!isset($_SESSION['UserMP'])) {
            $_SESSION['UserMP'] = GetCurrentMP('FY', DBDate());
            $allMP = 'FY';
        }
    }
    echo "<SELECT class=\"select\" name=mp onChange='this.form.submit();'>";
    if (count($RET)) {
        if (!UserMP())
            $_SESSION['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];

        foreach ($RET as $quarter) {
            echo "<OPTION value=$quarter[MARKING_PERIOD_ID]" . (UserMP() == $quarter['MARKING_PERIOD_ID'] ? ' SELECTED' : '') . ">" . $quarter['TITLE'] . "</OPTION>";
        }
    }
    echo "</SELECT>";
    echo '</div></FORM></li>';
}  ##################Only for Teacher End##################


if (User('PROFILE') != 'teacher') {
    echo "<li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns method=POST>
                        <INPUT type=hidden name=modcat value='' id=modcat_input>
                        ";

    if (User('PROFILE') == 'admin') {
        $RET = DBGet(DBQuery("SELECT DISTINCT s.ID,s.TITLE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND st.staff_id=$_SESSION[STAFF_ID] ORDER BY s.TITLE asc"));
        echo "<SELECT class=\"select-search\" name=school onChange='this.form.submit();'>";
        foreach ($RET as $school)
            echo "<OPTION  style='padding-right:8px;' value=$school[ID]" . ((UserSchool() == $school['ID']) ? ' SELECTED' : '') . ">" . $school['TITLE'] . "</OPTION>";
        echo "</SELECT>";
    }

    if (User('PROFILE') == 'parent') {
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID, se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . User('STAFF_ID') . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate() . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate() . "'>=se.START_DATE)"));
        foreach ($RET as $student)
            $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
    }

    if (User('PROFILE') == 'parent' || User('PROFILE') == 'teacher') {
        if (!$_SESSION['UserSchool']) {
            $sch_id = DBGet(DBQuery("SELECT CURRENT_SCHOOL_ID FROM staff WHERE STAFF_ID='" . User('STAFF_ID') . "'"));
            $sch_id = $sch_id[1]['CURRENT_SCHOOL_ID'];
            $_SESSION['UserSchool'] = $sch_id;
        }
    }
    echo '</FORM></div></li>';

    //===================================================================================================

    echo "<li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns method=POST>
                        <INPUT type=hidden name=modcat value='' id=modcat_input>";

    $school_years_RET1 = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=" . UserSchool()));
    $school_years_RET1 = $school_years_RET1[1];
    $school_years_RET1['START_DATE'] = explode("-", $school_years_RET1['START_DATE']);
    $school_years_RET1['START_DATE'] = $school_years_RET1['START_DATE'][0];
    $school_years_RET1['END_DATE'] = explode("-", $school_years_RET1['END_DATE']);
    $school_years_RET1['END_DATE'] = $school_years_RET1['END_DATE'][0];

    echo "<SELECT class=\"select\" name=syear onChange='this.form.submit();'>";

    if ($school_years_RET1['END_DATE'] > $school_years_RET1['START_DATE']) {
        if (User('PROFILE') == 'student') {
            $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID='$_SESSION[STUDENT_ID]' AND sy.SCHOOL_ID=" . UserSchool() . " "));
        } elseif (User('PROFILE') == 'parent') {
            if (UserStudentID() == '') {
                $stu_ID = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . User('STAFF_ID') . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate() . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate() . "'>=se.START_DATE)"));
                $stu_ID = $stu_ID[1]['STUDENT_ID'];
            } else
                $stu_ID = UserStudentID();
            $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=" . $stu_ID . " AND sy.SCHOOL_ID=" . UserSchool() . " "));
        } else {
            $school_years_RET = DBGet(DBQuery("SELECT sy.START_DATE,sy.END_DATE FROM school_years sy ,staff s INNER JOIN staff_school_relationship ssr ON s.staff_id=ssr.staff_id WHERE sy.school_id=ssr.school_id AND sy.syear=ssr.syear AND sy.SCHOOL_ID=" . UserSchool() . " AND s.staff_id='$_SESSION[STAFF_ID]'"));
        }
        foreach ($school_years_RET as $school_years) {
            $school_years['START_DATE'] = explode("-", $school_years['START_DATE']);
            $school_years['START_DATE'] = $school_years['START_DATE'][0];
            $school_years['END_DATE'] = explode("-", $school_years['END_DATE']);
            $school_years['END_DATE'] = $school_years['END_DATE'][0];
            echo "<OPTION value=$school_years[START_DATE]" . ((UserSyear() == $school_years['START_DATE']) ? ' SELECTED' : '') . "> $school_years[START_DATE]-" . ($school_years['END_DATE']) . "</OPTION>";
        }
    } else if ($school_years_RET1['END_DATE'] == $school_years_RET1['START_DATE']) {
        if (User('PROFILE') == 'student')
            $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID='$_SESSION[STUDENT_ID]' AND sy.SCHOOL_ID=" . UserSchool() . " "));
        elseif (User('PROFILE') == 'parent') {
            if (UserStudentID() == '') {
                $stu_ID = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . User('STAFF_ID') . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate() . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate() . "'>=se.START_DATE)"));
                $stu_ID = $stu_ID[1]['STUDENT_ID'];
            } else
                $stu_ID = UserStudentID();
            $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=" . $stu_ID . " AND sy.SCHOOL_ID=" . UserSchool() . " "));
        } else {
            if (UserSchool())
                $school_years_RET = DBGet(DBQuery("SELECT sy.START_DATE,sy.END_DATE FROM school_years sy ,staff s INNER JOIN staff_school_relationship ssr ON s.staff_id=ssr.staff_id WHERE sy.school_id=ssr.school_id AND sy.syear=ssr.syear AND sy.SCHOOL_ID=" . UserSchool() . " AND s.staff_id='$_SESSION[STAFF_ID]'"));
            else
                $school_years_RET = DBGet(DBQuery("SELECT sy.START_DATE,sy.END_DATE FROM school_years sy ,staff s WHERE s.SYEAR=sy.SYEAR  AND s.USERNAME=(SELECT USERNAME FROM staff  WHERE STAFF_ID='$_SESSION[STAFF_ID]')"));
        }
        foreach ($school_years_RET as $school_years) {
            $school_years['START_DATE'] = explode("-", $school_years['START_DATE']);
            $school_years_RET['START_DATE'] = $school_years['START_DATE'][0];
            echo "<OPTION value=$school_years_RET[START_DATE]" . ((UserSyear() == $school_years_RET['START_DATE']) ? ' SELECTED' : '') . ">$school_years_RET[START_DATE]</OPTION>";
        }
    }
    #}
    echo '</SELECT>';
    echo '</FORM></div></li>';

    //===================================================================================================

    if (User('PROFILE') == 'parent') {
        echo "<li><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns method=POST>";
        echo "<INPUT type=hidden name=modcat value='' id=modcat_input>";
        echo '<div class="form-group">';
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . User('STAFF_ID') . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate() . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate() . "'>=se.START_DATE)"));
        if (!UserStudentID())
            $_SESSION['student_id'] = $RET[1]['STUDENT_ID'];
        echo "<SELECT class=\"select\" name=student_id onChange='this.form.submit();'>";
        if (count($RET)) {
            foreach ($RET as $student) {
                echo "<OPTION value=$student[STUDENT_ID]" . ((UserStudentID() == $student['STUDENT_ID']) ? ' SELECTED' : '') . ">" . $student['FULL_NAME'] . "</OPTION>";
                if (UserStudentID() == $student['STUDENT_ID'])
                    $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
            }
        }
        echo "</SELECT>";

        if (!UserMP())
            $_SESSION['UserMP'] = GetCurrentMP('QTR', DBDate());
        echo '</div>';
        echo '</FORM></li>';
    }

    //===================================================================================================

    // For Marking Period
    echo "<li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns method=POST>
                        <INPUT type=hidden name=modcat value='' id=modcat_input>";

    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
    if (!isset($_SESSION['UserMP']))
        $_SESSION['UserMP'] = GetCurrentMP('QTR', DBDate());

    if (!$RET) {
        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (!isset($_SESSION['UserMP']))
            $_SESSION['UserMP'] = GetCurrentMP('SEM', DBDate());
    }

    if (!$RET) {
        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (!isset($_SESSION['UserMP']))
            $_SESSION['UserMP'] = GetCurrentMP('FY', DBDate());
    }

    echo "<SELECT class=\"select\" name=mp onChange='this.form.submit();'>";
    if (count($RET)) {
        if (!UserMP())
            $_SESSION['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
        foreach ($RET as $quarter)
            echo "<OPTION value=$quarter[MARKING_PERIOD_ID]" . (UserMP() == $quarter['MARKING_PERIOD_ID'] ? ' SELECTED' : '') . ">" . $quarter['TITLE'] . "</OPTION>";
    }
    echo "</SELECT>";
    // Marking Period

    echo '</FORM></div></li>';
} ################## Porfile Not Teacher End ##########################################

if (UserStudentID() && User('PROFILE') != 'parent' && User('PROFILE') != 'student') {
    $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"));
}
if (UserStaffID() && User('PROFILE') == 'admin') {
    if (UserStudentID())
        $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
}

echo '</ul>
            </div>
        </div>
        <!-- /main navbar -->

        <div class="sidebar-overlay"></div>
        <!-- Page header -->
        <div class="page-header">
            <div class="breadcrumb-line">
                <ul class="breadcrumb">
                    <li id="header"></li>
                </ul>';


echo '<div class="navbar-text pull-right">';
if (User('PROFILE') == 'teacher') {
    echo '<a href="https://support.os4ed.com/hc/en-us" class="text-white" target="_blank" data-popup="tooltip" data-placement="left" data-container="body" data-original-title="Support"><i class="fa fa-life-ring fa-lg"></i></a>';
} elseif (User('PROFILE') == 'student') {
    echo '<a href="https://support.os4ed.com/hc/en-us" class="text-white" target="_blank" data-popup="tooltip" data-placement="left" data-container="body" data-original-title="Support"><i class="fa fa-life-ring fa-lg"></i></a>';
} elseif (User('PROFILE') == 'parent') {
    echo '<a href="https://support.os4ed.com/hc/en-us" class="text-white" target="_blank" data-popup="tooltip" data-placement="left" data-container="body" data-original-title="Support"><i class="fa fa-life-ring fa-lg"></i></a>';
} else {
    echo '<a href="https://support.os4ed.com/hc/en-us" class="text-white" target="_blank" data-popup="tooltip" data-placement="left" data-container="body" data-original-title="Support"><i class="fa fa-life-ring fa-lg"></i></a>';
}
echo '</div>';
if (User('PROFILE') == 'teacher') {
    echo "<ul class=\"breadcrumb-elements\"><li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=subject method=POST><INPUT type=hidden name=modcat value='' id=modcat_input>";

    $sub = DBQuery("SELECT DISTINCT cs.TITLE, cs.SUBJECT_ID,cs.SCHOOL_ID FROM course_subjects as cs,course_details as cd WHERE cs.SUBJECT_ID=cd.SUBJECT_ID AND cd.SYEAR='" . UserSyear() . "' AND (cd.TEACHER_ID='" . User('STAFF_ID') . "' OR cd.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cs.SCHOOL_ID='" . UserSchool() . "' AND (cd.MARKING_PERIOD_ID IN (" . GetAllMP($allMP, UserMP()) . ") OR (cd.MARKING_PERIOD_ID IS NULL ))"); //AND cd.BEGIN_DATE<='".date('Y-m-d')."' AND cd.END_DATE>='".date('Y-m-d')."'))");
    $RET = DBGet($sub);

    if (!UserSubject()) {
        $_SESSION['UserSubject'] = $RET[1]['SUBJECT_ID'];
    }
    echo "<SELECT class=\"select\" name=subject onChange='this.form.submit();' style='width:100;'>";
    if (count($RET) > 0) {
        foreach ($RET as $subject) {
            echo "<OPTION id=$subject[SUBJECT_ID] value=$subject[SUBJECT_ID]" . ((UserSubject() == $subject['SUBJECT_ID']) ? ' SELECTED' : '') . ">" . $subject['TITLE'] . "</OPTION>";
        }
    } else {
        echo '<OPTION value="">n/a</OPTION>';
    }
    echo "</SELECT>";
    //===================================================================================================		
    echo "</FORM></div></li>";
    echo "<li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=course method=POST><INPUT type=hidden name=modcat value='' id=modcat_input>";
    $course = DBQuery("SELECT DISTINCT cd.COURSE_TITLE, cd.COURSE_ID,cd.SUBJECT_ID,cd.SCHOOL_ID FROM course_details cd WHERE (cd.TEACHER_ID='" . User('STAFF_ID') . "' OR cd.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cd.SYEAR='" . UserSyear() . "' AND cd.SCHOOL_ID='" . UserSchool() . "' AND cd.SUBJECT_ID='" . UserSubject() . "' AND (cd.MARKING_PERIOD_ID IN (" . GetAllMP($allMP, UserMP()) . ") OR (cd.MARKING_PERIOD_ID IS NULL ))"); //AND cd.BEGIN_DATE<='".date('Y-m-d')."' AND cd.END_DATE>='".date('Y-m-d')."'))");					
    $RET_temp = DBGet($course);
    $ret_increment = 1;
    $RET = array();
    foreach ($RET_temp as $ret_courses) {
        $get_cps = DBGet(DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.MARKING_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='" . UserSyear() . "' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='" . UserSchool() . "' AND cp.COURSE_ID='" . $ret_courses['COURSE_ID'] . "' AND (TEACHER_ID='" . User('STAFF_ID') . "' OR SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND (MARKING_PERIOD_ID IN (" . GetAllMP($allMP, UserMP()) . ") OR (MARKING_PERIOD_ID IS NULL)) group by (cp.COURSE_PERIOD_ID)"));
        if (count($get_cps) > 0) {
            $RET[$ret_increment] = $ret_courses;
            $ret_increment++;
        }
    }

    if (!UserCourse()) {
        $_SESSION['UserCourse'] = $RET[1]['COURSE_ID'];
    }
    echo "<SELECT class=\"select\" name=course onChange='this.form.submit();' style='width:100;'>";
    if (count($RET) > 0) {
        foreach ($RET as $course) {
            echo "<OPTION id=$course[COURSE_ID] value=$course[COURSE_ID]" . ((UserCourse() == $course['COURSE_ID']) ? ' SELECTED' : '') . ">" . $course['COURSE_TITLE'] . "</OPTION>";
        }
    } else {
        echo '<OPTION value="">n/a</OPTION>';
    }
    echo "</SELECT>";
    //===================================================================================================							     					     
    echo "</FORM></div></li>";

    echo "<li><div class=\"form-group\"><FORM name=head_frm id=head_frm action=Side.php?modfunc=update&btnn=$btn&nsc=$ns&act=period method=POST><INPUT type=hidden name=modcat value='' id=modcat_input>";


    $QI = DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.MARKING_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='" . UserSyear() . "' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='" . UserSchool() . "' AND cp.COURSE_ID='" . UserCourse() . "' AND (TEACHER_ID='" . User('STAFF_ID') . "' OR SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND (MARKING_PERIOD_ID IN (" . GetAllMP($allMP, UserMP()) . ") OR (MARKING_PERIOD_ID IS NULL)) group by (cp.COURSE_PERIOD_ID)");
    $RET = DBGet($QI);
    $user_profile_ret = DBGet(DBQuery(" SELECT PROFILE FROM staff WHERE STAFF_ID=" . UserID()));

    if (!CpvId())
        $_SESSION['CpvId'] = $RET[1]['ID'];

    $fy_id = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
    $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

    if (!UserCoursePeriod()) {
        $_SESSION['UserCoursePeriod'] = $RET[1]['COURSE_PERIOD_ID'];
    }

    echo "<SELECT class=\"select\" style=\"width: 200px;\" name=period onChange='this.form.submit();' >";
    if (count($RET) > 0) {
        $flag = 0;
        foreach ($RET as $period) {
            $x_sel = "";
            $period_det = DBGet(DBQuery('SELECT sp.TITLE as PERIOD_NAME,cpv.DAYS,cpv.COURSE_PERIOD_DATE FROM course_period_var cpv,school_periods sp WHERE cpv.ID=' . $period['ID'] . ' AND cpv.PERIOD_ID=sp.PERIOD_ID'));
            $period_det = $period_det[1];
            $days_arr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');
            if ($period_det['DAYS'] == '') {
                $period_det['DAYS'] = date('l', strtotime($period_det['COURSE_PERIOD_DATE']));
                $period_det['DAYS'] = $days_arr[$period_det['DAYS']];
            }


            if ($flag == 0) {
                $x_sel = " SELECTED=SELECTED";
                $flag = 1;
            }

            echo "<OPTION id=$period[COURSE_PERIOD_ID] value=$period[ID]" . ((CpvId() == $period['ID']) ? ' SELECTED' : '') . ">" . $period['TITLE'] . " - " . $period_det['PERIOD_NAME'] . " - " . $period_det['DAYS'] . "</OPTION>";
            $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
            if (CpvId() == $period['ID']) {

                $_SESSION['CpvId'] = $period['ID'];
                $_SESSION['UserCoursePeriod'] = $period['COURSE_PERIOD_ID'];
            }
        }
    } else {
        echo '<OPTION value="">n/a</OPTION>';
    }
    echo "</SELECT>";
    echo "</FORM></div></li></ul>";
}
$user_picture = '';
if (User('PROFILE') != 'parent') {

    if (User('PROFILE') == 'student') {
        $img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . UserStudentID() . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
        $img_info = $img_info[1]['CONTENT'];
    } else {
        $img_info = DBGet(DBQuery('SELECT * FROM staff WHERE STAFF_ID=' . UserID()));
        $img_info = $img_info[1]['IMG_CONTENT'];
    }
    if ($img_info != '')
        $user_picture = '<a href="javascript:void(0)"><IMG src="data:image/jpeg;base64,' . base64_encode($img_info) . '" class="img-circle img-responsive"></a>';
    else
        $user_picture = '<a href="javascript:void(0)"><IMG SRC="assets/no_avtar.png" class="img-circle img-responsive"></a>';
}


echo '</div>
        </div>
        <!-- /page header -->


        <!-- Page container -->
        <div class="page-container">

            <!-- Page content -->
            <div class="page-content">

                <!-- Main sidebar -->
                <div class="sidebar sidebar-main">
                    <div class="sidebar-fixed">
                        <div class="sidebar-content">

                            <!-- Main navigation -->
                            <div class="sidebar-category sidebar-category-visible">
                                <div class="sidebar-user-material">
                                    <div class="category-content">
                                        <div class="sidebar-user-material-content">
                                            ' . $user_picture . '
                                            <h6>' . User('NAME') . '</h6>
                                            <span class="text-size-small">' . ucwords(User('PROFILE')) . '</span>
                                        </div>

                                        <div class="sidebar-user-material-menu">
                                            <a href="#user-nav" data-toggle="collapse"><span>' . _myAccount . '</span> <i class="caret"></i></a>
                                        </div>
                                    </div>

                                    <div class="navigation-wrapper collapse" id="user-nav">
                                        <ul class="navigation">
                                
                                            <li><a href="javascript:void(0)" onclick="check_content(\'Ajax.php?modname=messaging/Inbox.php\');"><i class="icon-comment-discussion"></i> <span>' . _messages . '</span></a></li>';
if (User('PROFILE') != 'student')
    echo '<li><a href="javascript:void(0)" onclick="check_content(\'Ajax.php?modname=users/Preferences.php\');"><i class="icon-equalizer"></i> <span>' . _preferences . '</span></a></li>';

echo '<li><a href="index.php?modfunc=logout"><i class="icon-switch2"></i> <span>' . _logout . '</span></a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="category-content no-padding">
                                    <ul class="navigation navigation-main navigation-accordion">';
/*
 * Primary Navigation Start
 */
require('Menu.php');

$current_mod = substr($_REQUEST['modname'], 0, strrpos($_REQUEST['modname'], '/'));
$current_user_profile = strtolower(User('PROFILE'));
$current_mod_url = $_REQUEST['modname'];

$current_menu = $menu[$current_mod][$current_user_profile][$current_mod_url];
$menu_icons = array(
    "schoolsetup" => "icon-library2",
    "students" => "icon-man-woman",
    "users" => "icon-users",
    "scheduling" => "icon-calendar3",
    "grades" => "icon-chart",
    "attendance" => "icon-alarm-check",
    "eligibility" => "icon-checkmark3",
    "messaging" => "icon-envelop5",
    "tools" => "icon-hammer-wrench",
    "library" => "icon-book3",
    "billing" => "icon-calculator2",
    "discipline" => "icon-hammer2"
);

//echo "<li><a href='javascript:void(0)' onmouseup='check_content(\"Ajax.php?modname=miscellaneous/Portal.php\");' onmousedown='document.getElementById(\"header\").innerHTML = \"Home\";document.getElementById(\"cframe\").src = \"Bottom.php?modcat=home\"'><i class=\"icon-home4\"></i><span>" . "Home" . "</span></a></li>";
echo "<li><a href='#' onmouseup='check_content(\"Ajax.php?modname=miscellaneous/Portal.php\");' onmousedown='document.getElementById(\"header\").innerHTML = \"Home\";document.getElementById(\"cframe\").src = \"Bottom.php?modcat=home\"'><i class=\"icon-home4\"></i><span>" . _home . "</span></a></li>";
foreach ($_openSIS['Menu'] as $modcat => $programs) {
    if (count($_openSIS['Menu'][$modcat])) {
        $keys = array_keys($_openSIS['Menu'][$modcat]);
        $menu = false;
        foreach ($keys as $key_index => $file) {
            if (!is_numeric($file))
                $menu = true;
        }

        if (!$menu)
            continue;



        if (User('PROFILE') != 'admin' && $modcat == "schoolsetup") {
            echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _schoolInfo . "</span></a>";
        } elseif (User('PROFILE') != 'admin' && $modcat == "users") {
            echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _myInfo . "</span></a>";
        } elseif (User('PROFILE') == 'student' && $modcat == "students") {

            echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _myInfo . "</span></a>";
        } elseif (User('PROFILE') == 'student' && $modcat == "scheduling") {
            echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _schedule . "</span></a>";
        } elseif ($modcat == "messaging") {
            echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _messaging . "</span></a>";
        } else {

            if ($modcat == 'eligibility') {
                echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _extracurricular . "</span></a>";
            } elseif ($modcat == 'schoolsetup')
                echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . _schoolSetup . "</span></a>";
            else {
                echo "<li " . (($current_mod == $modcat) ? 'class="active"' : '') . "><a HREF=javascript:void(0)><i class=\"{$menu_icons[$modcat]}\"></i><span>" . ucfirst(str_replace('_', ' ', constant('_' . $modcat))) . "</span></a>";
            }
        }

        /*
         * Submenu
         */
        echo '<ul>';
        $int = 0;
        $mm = 0;
        $style = '';
        $child = 0;
        $child2 = 0;
        foreach ($keys as $key_index => $file) {

            $int = $int + 1;

            $title = $_openSIS['Menu'][$modcat][$file];
            if ($mm == 0) {
                if (substr($file, 0, 7) == 'http://')
                    echo "<li><A HREF=$file  >$title</A>";
                elseif (substr($file, 0, 7) == 'HTTP://')
                    echo "<li><A HREF=$file target=_blank>$title</A>";
                elseif (!is_numeric($file))
                    if (User('PROFILE') == 'student' && $title == "Student Info") {
                        echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ucwords(constant($modcat)) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">My Info</A>";
                    } elseif (User('PROFILE') == 'student' && $title == "Schedule") {
                        echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ucwords(constant($modcat)) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">My Schedule</A>";
                    } elseif (User('PROFILE') == 'student' && $title == "Student Requests") {
                        echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ucwords(constant($modcat)) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">My Requests</A>";
                    } else {

                        if ($modcat == 'eligibility')
                            echo "<li  " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"Extracurricular <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . str_replace('&', '?', $file) . "';\">$title</A>";
                        else {
                            if (User('PROFILE_ID') != 0 && User('PROFILE') == 'admin') {
                                if ($modcat == 'tools' && $title != 'Backup Database')
                                    echo "<li  " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ucwords(constant($modcat)) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . str_replace('&', '?', $file) . "';\">$title</A>";
                                if ($modcat != 'tools')
                                    echo "<li  " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ($modcat == 'schoolsetup' ? _schoolSetup : ucwords(constant($modcat))) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . str_replace('&', '?', $file) . "';\">$title</A>";
                            } else
                                echo "<li  " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=hm HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='$(\"#header\").html(\"" . ($modcat == 'schoolsetup' ? _schoolSetup : ucwords(str_replace('_', ' ', constant("_" . $modcat)))) . " <i class=icon-arrow-right5></i> " . $title . "\");' onmouseup=\"$('#cframe').attr('src','Bottom.php?modname=" . str_replace('&', '?', $file) . "');\">$title</A>";
                        }
                    }
                elseif ($keys[$key_index + 1] && !is_numeric($keys[$key_index + 1])) {
                    $mm = $mm + 1;
                    if (User('PROFILE_ID') != 0 && User('PROFILE') == 'admin') {
                        if ($modcat == 'tools' && $title != 'Reports') {
                            echo '<li><a href="">' . $title . '</a><ul>';
                            $child = 1;
                        }
                        if ($modcat != 'tools') {
                            echo '<li><a href="">' . $title . '</a><ul>';
                            $child = 1;
                        }
                    } else {
                        echo '<li><a href="">' . $title . '</a><ul>';
                        $child = 1;
                    }
                }
            } elseif ($mm > 0) {
                $menumm = $mm;
                if (substr($file, 0, 7) == 'http://')
                    echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=$file  >$title</A>";
                elseif (substr($file, 0, 7) == 'HTTP://')
                    echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=$file target=_blank>$title</A>";
                elseif (!is_numeric($file)) {
                    if ($modcat == 'eligibility')
                        echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"Extracurricular <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">$title</A>";
                    else {

                        if (User('PROFILE_ID') != 0 && User('PROFILE') == 'admin') {
                            if ($modcat == 'tools' && $title != 'At a Glance' && $title != 'Institute Reports' && $title != 'Institute Custom Field Reports')
                                echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ucwords(constant($modcat)) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">$title</A>";
                            //                                            
                            if ($modcat != 'tools') {

                                echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ($modcat == 'schoolsetup' ? _schoolSetup : ucwords(constant($modcat))) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">$title</A>";
                            }
                        } else
                            echo "<li " . (($current_menu == $title) ? 'class="current-submenu"' : '') . "><A id=dd HREF=javascript:void(0) onClick='check_content(\"Ajax.php?modname=" . $file . " \");'  onmousedown='document.getElementById(\"header\").innerHTML = \"" . ($modcat == 'schoolsetup' ? _schoolSetup : ucwords(constant($modcat))) . " <i class=\"icon-arrow-right5\"></i> " . "$title\"' onmouseup=\"document.getElementById('cframe').src='Bottom.php?modname=" . $file . "';\">$title</A>";
                    }
                } elseif ($keys[$key_index + 1] && !is_numeric($keys[$key_index + 1])) {
                    $mm = $mm + 1;
                    echo '</ul></li><li><a href="javascript:void(0)">' . $title . '</a><ul>';
                    $child2 = 1;
                    if ($child == 1) {
                        $child = 0;
                    }
                }
            }
            echo '</li>';
        }
        if ($child2 == 1 || $child == 1) {
            echo '</ul>';
            $child2 = 0;
        }
        $i = $i + 1;
        echo '</li>';
        echo '</ul>';
        echo '</li>';
    }
}

/*
 * Primary Navigation End
 */
$get_app_details = DBGet(DBQuery('SELECT * FROM app'));
echo '</ul>
                                </div>
                            </div>
                            <!-- /main navigation -->
                            
                        </div>
                    </div>
                </div>
                <!-- /main sidebar -->


                <!-- Main content -->
                <div class="content-wrapper">';


$append = '';
if ($_REQUEST['page_display'])
    $append = '?page_display=' . $_REQUEST['page_display'];
if ($_REQUEST['include'] && $_REQUEST['modname'] == 'students/Student.php')
    $append = '?include=' . $_REQUEST['include'];



echo "<div id='content' name='content' class='clearfix'>";

if (User('PROFILE') == 'admin') {

    $admin_COMMON_FROM = " FROM students s, student_address a,student_enrollment ssm ";
    $admin_COMMON_WHERE = " WHERE s.STUDENT_ID=ssm.STUDENT_ID  AND a.STUDENT_ID=s.STUDENT_ID AND a.TYPE='Home Address' AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID=" . UserSchool() . " ";

    if (optional_param('mp_comment', '', PARAM_NOTAGS) || $_SESSION['smc']) {
        $admin_COMMON_FROM .= " ,student_mp_comments smc";
        $admin_COMMON_WHERE .= " AND smc.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smc'] = '1';
    }

    if (optional_param('goal_description', '', PARAM_NOTAGS) || optional_param('goal_title', '', PARAM_NOTAGS) || $_SESSION['g']) {
        $admin_COMMON_FROM .= " ,student_goal g ";
        $admin_COMMON_WHERE .= " AND g.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['g'] = '1';
    }

    if (optional_param('progress_name', '', PARAM_NOTAGS) || optional_param('progress_description', '', PARAM_NOTAGS) || $_SESSION['p']) {
        $admin_COMMON_FROM .= " ,student_goal_progress p ";
        $admin_COMMON_WHERE .= " AND p.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['p'] = '1';
    }

    if (optional_param('doctors_note_comments', '', PARAM_NOTAGS) || optional_param('med_day', '', PARAM_NOTAGS) || optional_param('med_month', '', PARAM_NOTAGS) || optional_param('med_year', '', PARAM_NOTAGS) || $_SESSION['smn']) {
        $admin_COMMON_FROM .= " ,student_medical_notes smn ";
        $admin_COMMON_WHERE .= " AND smn.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smn'] = '1';
    }

    if (optional_param('type', '', PARAM_NOTAGS) || optional_param('imm_comments', '', PARAM_NOTAGS) || optional_param('imm_day', '', PARAM_NOTAGS) || optional_param('imm_month', '', PARAM_NOTAGS) || optional_param('imm_year', '', PARAM_NOTAGS) || $_SESSION['sm']) {

        $admin_COMMON_FROM .= " ,student_immunization sm ";
        $admin_COMMON_WHERE .= " AND sm.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['sm'] = '1';
    }


    if (optional_param('ma_day', '', PARAM_NOTAGS) || optional_param('ma_month', '', PARAM_NOTAGS) || optional_param('ma_year', '', PARAM_NOTAGS) || optional_param('med_alrt_title', '', PARAM_NOTAGS) || $_SESSION['sma']) {
        $admin_COMMON_FROM .= " ,student_medical_alerts sma  ";
        $admin_COMMON_WHERE .= " AND sma.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['sma'] = '1';
    }

    if (optional_param('nv_day', '', PARAM_NOTAGS) || optional_param('nv_month', '', PARAM_NOTAGS) || optional_param('nv_year', '', PARAM_NOTAGS) || optional_param('reason', '', PARAM_NOTAGS) || optional_param('result', '', PARAM_NOTAGS) || optional_param('med_vist_comments', '', PARAM_NOTAGS) || $_SESSION['smv']) {
        $admin_COMMON_FROM .= " ,student_medical_visits smv   ";
        $admin_COMMON_WHERE .= " AND smv.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smv'] = '1';
    }
    $admin_COMMON = $admin_COMMON_FROM . $admin_COMMON_WHERE;
}

if (User('PROFILE') == 'teacher') {

    $teacher_COMMON_FROM = " FROM students s, student_enrollment ssm, course_periods cp,
                                            schedule ss,student_address a ";
    $teacher_COMMON_WHERE = " WHERE a.STUDENT_ID=s.STUDENT_ID  AND a.TYPE='Home Address' AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.MARKING_PERIOD_ID IN (" . GetAllMP('', $queryMP) . ")
                                                                                    AND (cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "' AND (ssm.START_DATE IS NOT NULL AND ('" . DBDate() . "'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID=" . UserSchool() . " ";


    if (optional_param('mp_comment', '', PARAM_SPCL) || $_SESSION['smc']) {
        $teacher_COMMON_FROM .= " ,student_mp_comments smc";
        $teacher_COMMON_WHERE .= " AND smc.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smc'] = '1';
    }

    if (optional_param('goal_description', '', PARAM_SPCL) || optional_param('goal_title', '', PARAM_SPCL) || $_SESSION['g']) {
        $teacher_COMMON_FROM .= " ,student_goal g ";
        $teacher_COMMON_WHERE .= " AND g.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['g'] = '1';
    }

    if (optional_param('progress_name', '', PARAM_NOTAGS) || optional_param('progress_description', '', PARAM_NOTAGS) || $_SESSION['p']) {
        $teacher_COMMON_FROM .= " ,student_goal_progress p ";
        $teacher_COMMON_WHERE .= " AND p.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['p'] = '1';
    }

    if (optional_param('doctors_note_comments', '', PARAM_NOTAGS) || optional_param('med_day', '', PARAM_NOTAGS) || optional_param('med_month', '', PARAM_NOTAGS) || optional_param('med_year', '', PARAM_NOTAGS) || $_SESSION['smn']) {
        $teacher_COMMON_FROM .= " ,student_medical_notes smn ";
        $teacher_COMMON_WHERE .= " AND smn.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smn'] = '1';
    }

    if (optional_param('type', '', PARAM_NOTAGS) || optional_param('imm_comments', '', PARAM_NOTAGS) || optional_param('imm_day', '', PARAM_NOTAGS) || optional_param('imm_month', '', PARAM_NOTAGS) || optional_param('imm_year', '', PARAM_NOTAGS) || $_SESSION['sm']) {

        $teacher_COMMON_FROM .= " ,student_immunization sm ";
        $teacher_COMMON_WHERE .= " AND sm.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['sm'] = '1';
    }

    if (optional_param('ma_day', '', PARAM_NOTAGS) || optional_param('ma_month', '', PARAM_NOTAGS) || optional_param('ma_year', '', PARAM_NOTAGS) || optional_param('med_alrt_title', '', PARAM_NOTAGS) || $_SESSION['sma']) {
        $teacher_COMMON_FROM .= " ,student_medical_alerts sma  ";
        $teacher_COMMON_WHERE .= " AND sma.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['sma'] = '1';
    }

    if (optional_param('nv_day', '', PARAM_NOTAGS) || optional_param('nv_month', '', PARAM_NOTAGS) || optional_param('nv_year', '', PARAM_NOTAGS) || optional_param('reason', '', PARAM_NOTAGS) || optional_param('result', '', PARAM_NOTAGS) || optional_param('med_vist_comments', '', PARAM_NOTAGS) || $_SESSION['smv']) {
        $teacher_COMMON_FROM .= " ,student_medical_visits smv   ";
        $teacher_COMMON_WHERE .= " AND smv.STUDENT_ID=s.STUDENT_ID ";
        $_SESSION['smv'] = '1';
    }
    $teacher_COMMON = $teacher_COMMON_FROM . $teacher_COMMON_WHERE;
}

//===================== End =============================================
//

echo "<div id='update_panel'>";
echo "<div id='divErr' class=\"text-left text-danger\"></div>";


if (!isset($_REQUEST['_openSIS_PDF'])) {

    echo '<DIV id="Migoicons" style="visibility:hidden;position:absolute;z-index:1000;top:-100;"></DIV>';
    //echo "<TABLE width=100% border=0 cellpadding=0><TR><TD valign=top align=center>";
}

//print_r($_REQUEST['modname']);
if ($_REQUEST['modname'] || $_GET['modname']) {
    /*     * *****************back to list*************************** */
    if ($_REQUEST['bottom_back'] && $_SESSION['staff_id'])
        unset($_SESSION['staff_id']);
    if ($_REQUEST['bottom_back'] && $_SESSION['student_id'])
        unset($_SESSION['student_id']);
    /*     * ********************************************* */
    // if ($_REQUEST['_openSIS_PDF'] == 'true')
    //     ob_start();
    if (strpos($_REQUEST['modname'], '?') !== false) {

        $modname = substr(optional_param('modname', '', PARAM_NOTAGS), 0, strpos(optional_param('modname', '', PARAM_NOTAGS), '?'));

        $vars = substr(optional_param('modname', '', PARAM_NOTAGS), (strpos(optional_param('modname', '', PARAM_NOTAGS), '?') + 1));

        $vars = explode('?', $vars);
        foreach ($vars as $code) {
            $code = explode('=', $code);
            $_REQUEST[$code[0]] = $code[1];
        }
    } else
        $modname = optional_param('modname', '', PARAM_NOTAGS);


    if (optional_param('LO_save', '', PARAM_INT) != '1' && !isset($_REQUEST['_openSIS_PDF']) && (strpos($modname, 'miscellaneous/') === false || $modname == 'miscellaneous/Registration.php' || $modname == 'miscellaneous/Export.php' || $modname == 'miscellaneous/Portal.php'))
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


    ##### REMOVE FILES FROM ROOT - START #####

    $check_backups = DBGet(DBQuery("SELECT * FROM `program_config` WHERE `program` = 'DB_BACKUP'"));

    if (!empty($check_backups)) {
        foreach ($check_backups as $each_backups) {
            $filename = $each_backups['TITLE'] . '.sql';

            if (file_exists($filename)) {
                unlink($filename);

                DBQuery("DELETE FROM `program_config` WHERE `program` = 'DB_BACKUP' AND `value` = '" . $each_backups['VALUE'] . "'");
            }
        }
    }

    ##### REMOVE FILES FROM ROOT - END #####


    if (optional_param('modname', '', PARAM_NOTAGS) == 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'ParentLookup.php')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'schoolsetup/UploadLogo.php' && User('PROFILE') == 'admin')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'users/UploadUserPhoto.php')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'users/UploadUserPhoto.php?modfunc=edit')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'students/Upload.php')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'students/StudentFilters.php')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'students/Upload.php?modfunc=edit')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'scheduling/Schedule.php?modfunc=cp_insert')
        $allowed = true;
    if (substr(optional_param('modname', '', PARAM_NOTAGS), 0, 14) == 'miscellaneous/' || substr(optional_param('modname', '', PARAM_NOTAGS), 0, 7) == 'grades/')
        $allowed = true;
    if (optional_param('modname', '', PARAM_NOTAGS) == 'messaging/AddMember.php')
        $allowed = true;
    if ($allowed || $_SESSION['take_mssn_attn']) {

        if (Preferences('SEARCH') != 'Y' && substr(clean_param($modname, PARAM_NOTAGS), 0, 6) != 'users/')
            $_REQUEST['search_modfunc'] = 'list';

        if (preg_match('/\.\./', $modname) !== 1)
            include 'modules/' . $modname;
    } else {
        if (User('USERNAME')) {
            echo "" . _youReNotAllowedToUseThisProgram . "! " . _thisAttemptedViolationHasBeenLoggedAndYourIpAddressWasCaptured . ".";
            Warehouse('footer');

            if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            if ($openSISNotifyAddress)
                mail($openSISNotifyAddress, 'HACKING ATTEMPT', "INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','$_REQUEST[modname]','" . User('USERNAME') . "')");
            if (false && function_exists('query')) {

                if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }

                $connection = new mysqli('os4ed.com', 'openSIS_log', 'openSIS_log', 'openSIS_log');

                $connection->query("INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','" . optional_param('modname', '', PARAM_CLEAN) . "','" . User('USERNAME') . "')");
                mysqli_close($link);
            }
        }
        exit;
    }

    if ($_SESSION['unset_student']) {
        unset($_SESSION['unset_student']);
        unset($_SESSION['staff_id']);
    }
}


/*
 * Demo Chart
 */

if (!isset($_REQUEST['_openSIS_PDF'])) {

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


    echo "</div>";

    echo "<div id='cal' style='position:absolute;'></div>";
}

echo '</div>
    </div>
        </div>
        </div>
                <!-- /main content -->
                
            </div>
            <!-- /page content -->

        </div>
        <!-- /page container -->
        

        <!-- Footer -->
        <div class="navbar footer">
            <div class="navbar-collapse" id="footer">
                <div class="row">
                    <div class="col-md-9">
                        <div class="navbar-text">
                            ' . _footerText . '
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="version-info">
                            Version <b>' . $get_app_details[1]['VALUE'] . '</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /footer -->';

echo "</body>";
echo "</html>";
