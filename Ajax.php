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
//!empty($_SESSION['PROFILE_ID']) or die('Access denied!');

include "functions/ParamLibFnc.php";
echo '<script type="text/javascript" src="assets/js/pages/components_popups.js"></script>';
echo '<script type="text/javascript" src="assets/js/pages/picker_date.js"></script>';
echo '<script type="text/javascript" src="assets/js/pages/form_checkboxes_radios.js"></script>';
// echo '<script type="text/javascript" src="assets/js/plugins/forms/inputs/jquery.creditCardValidator.js"></script>';
echo '<script>';
echo '$(document).ready(function() {        
        // Animate loader off screen
        $("#loading-image").hide();
        
        if($(".clockpicker").length>0){
            $(".clockpicker").clockpicker({ 
                twelvehour: true,
                donetext: \'Done\'
            }).find("input").change(function () {
               //alert(this.value);
            });
        }
        
        if($(".switch-fake-title").length > 0){
            $(".switch-fake-title").each(function(){
                var check = $(this).closest("label").children("input[type=checkbox]");
                if(check.is(":checked")){
                    $(this).text("Yes");
                }else{
                    $(this).text("No");
                }
            });
        }
        
        /*if(hasScrollBar(".table-responsive", "horizontal")){
            $(".table-responsive").mousewheel(function (e, delta) {
                this.scrollLeft -= (delta * 40);
                e.preventDefault();
            });
        }*/

        $(".switch-fake-title").closest("label").children("input[type=checkbox]").change(function(){
            if($(this).is(":checked")){
                $(this).closest("label").children(".switch-fake-title").text("Yes");
            }else{
                $(this).closest("label").children(".switch-fake-title").text("No");
            }
        });
        
        $("body").removeClass("sidebar-mobile-main");
        
        // Scroll page to top after ajax call
        //$("html, body").animate({ scrollTop: 0 }, "200");
        
        $(\'body\').on(\'click\', \'div.sidebar-overlay\', function () {
            $(\'body\').toggleClass(\'sidebar-mobile-main\');
        });
        
        $(\'body\').removeClass(\'sidebar-mobile-main\');
        
        // Initializing Tooltips & Popovers after ajax call
        $(\'[data-toggle="tooltip"], [data-popup="tooltip"]\').tooltip();
        $(\'[data-popup="popover"]\').popover();
        
      });';
echo '</script>';

$url = validateQueryString(curPageURL());
if ($url === FALSE) {
    header('Location: index.php');
}
$isajax = "ajax";
$start_time = time();
include 'Warehouse.php';
array_rwalk($_REQUEST, 'strip_tags');
$title_set = '';

$_REQUEST['modname'] = sqlSecurityFilter($_REQUEST['modname']);

if (UserStudentID() && User('PROFILE') != 'parent' && User('PROFILE') != 'student' && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 5) != 'Atten' && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 5) != 'users' && clean_param($_REQUEST['modname'], PARAM_NOTAGS) != 'students/AddUsers.php' && $_REQUEST['modname'] != 'tools/Backup.php' && (substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 10) != 'attendance' || clean_param($_REQUEST['modname'], PARAM_NOTAGS) == 'attendance/StudentSummary.php' || clean_param($_REQUEST['modname'], PARAM_NOTAGS) == 'attendance/DailySummary.php' || clean_param($_REQUEST['modname'], PARAM_NOTAGS) == 'attendance/AddAbsences.php')) {
    $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"));
    $count_student_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM students"));

    $allow_buffer_list = array(
        // For Students
        'students/Student.php',
        // 'students/AssignOtherInfo.php',
        // 'students/StudentReenroll.php',
        'students/AdvancedReport.php',
        'students/Letters.php',
        'students/MailingLabels.php',
        'students/StudentLabels.php',
        'students/PrintStudentInfo.php',
        'students/PrintStudentContactInfo.php',
        'students/GoalReport.php',
        'students/EnrollmentReport.php',
        // For Scheduling
        // 'scheduling/Schedule.php', 
        'scheduling/ViewSchedule.php',
        'scheduling/Requests.php',
        // 'scheduling/MassSchedule.php',
        // 'scheduling/MassRequests.php',
        'scheduling/PrintSchedules.php',
        // 'scheduling/PrintRequests.php',
        // 'scheduling/UnfilledRequests.php',
        // 'scheduling/IncompleteSchedules.php',
        // For Grades
        'grades/ReportCards.php',
        'grades/Transcripts.php',
        'grades/FinalGrades.php',
        'grades/GPARankList.php',
        'grades/AdminProgressReports.php',
        'grades/ProgressReports.php',
        // 'grades/HonorRoll.php',
        'grades/EditReportCardGrades.php',
        // 'grades/GraduationProgress.php', 
        'grades/HistoricalReportCardGrades.php',
        // For Attendance
        'attendance/AddAbsences.php',
        // 'attendance/DailySummary.php',
        // 'attendance/StudentSummary.php',
        // 'attendance/DuplicateAttendance.php',
        // For Eligibility
        'eligibility/Student.php',
        // 'eligibility/AddActivity.php',
        // 'eligibility/StudentList.php'
    );

    $allow_back_to_student_list = array(
        // For Students
        'students/Student.php',
        // For Scheduling
        // 'scheduling/Schedule.php', 
        'scheduling/ViewSchedule.php',
        'scheduling/Requests.php',
        // For Grades
        'grades/EditReportCardGrades.php',
        // For Eligibility
        'eligibility/Student.php'
    );

    if ($count_student_RET[1]['NUM'] > 1) {
        $title_set = 'y';

        if (in_array($_REQUEST['modname'], $allow_buffer_list)) {
            if (in_array($_REQUEST['modname'],  $allow_back_to_student_list)) {
                DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements clearfix"><span class="heading-text"><A HREF=Modules.php?modname=' . clean_param($_REQUEST['modname'], PARAM_NOTAGS) . '&search_modfunc=list&next_modname=Students/Student.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToStudentList . '</A></span><div class="btn-group heading-btn"><A HREF=SideForStudent.php?student_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . '&modname=' . $_REQUEST['modname'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div></div>');
            } else {
                DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements clearfix"><div class="btn-group heading-btn"><A HREF=SideForStudent.php?student_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . '&modname=' . $_REQUEST['modname'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div></div>');
            }
        }
    } else if ($count_student_RET[1]['NUM'] == 1) {
        $title_set = 'y';

        if (in_array($_REQUEST['modname'], $allow_buffer_list)) {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements clearfix"><A HREF=SideForStudent.php?student_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . '&modname=' . $_REQUEST['modname'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div>');
        }
    }
}
$title_set_staff = '';
if (UserStaffID() && User('PROFILE') == 'admin' && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 6) != 'grades' && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 8) != 'students' && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 10) != 'attendance') {
    $Modname_Attn = 'users/TeacherPrograms.php';
    $Modname_Pro = 'users/TeacherPrograms.php?include=grades/ProgressReports.php';
    if ((!UserStudentID() || substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 5) == 'users') && substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 25) != $Modname_Attn && clean_param($_REQUEST['modname'], PARAM_NOTAGS) != 'users/AddStudents.php' && !clean_param($_REQUEST['miss_attn'], PARAM_NOTAGS)) {
        $title_set_staff = 'y';
        if ($_REQUEST['modname'] != 'users/User.php') {
            $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
            echo '<div class="panel panel-default">';
            DrawHeader('' . _selectedStaff . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Modules.php?modname=' . clean_param($_REQUEST['modname'], PARAM_NOTAGS) . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToUserList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
            echo '</div>';
        }
    }
}
echo "<div id=\"divErr\"></div>";
if (!isset($_REQUEST['_openSIS_PDF'])) {
    Warehouse('header');

    if (strpos(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 'miscellaneous/') === false)
        echo '<script language="JavaScript">if(window == top  && (!window.opener || window.opener.location.href.substring(0,(window.opener.location.href.indexOf("&")!=-1?window.opener.location.href.indexOf("&"):window.opener.location.href.replace("#","").length))!=window.location.href.substring(0,(window.location.href.indexOf("&")!=-1?window.location.href.indexOf("&"):window.location.href.replace("#","").length)))) window.location.href = "index.php";</script>';
    // echo "<BODY marginwidth=0 leftmargin=0 border=0 onload='doOnload();' background=assets/bg.gif>";
    // echo '<DIV id="Migoicons" style="visibility:hidden;position:absolute;z-index:1000;top:-100"></DIV>';
}

$ajax_to_sign_in    = "";
$ajax_to_sign_out   = "";

if (clean_param($_REQUEST['modname'], PARAM_NOTAGS)) {
    if ($_REQUEST['_openSIS_PDF'] == 'true')
        ob_start();
    if (strpos($_REQUEST['modname'], '?') !== false) {
        $vars = substr($_REQUEST['modname'], (strpos($_REQUEST['modname'], '?') + 1));
        $modname = substr($_REQUEST['modname'], 0, strpos($_REQUEST['modname'], '?'));

        $vars = explode('?', $vars);
        foreach ($vars as $code) {
            $arr_ind = substr($code, 0, strpos($code, '='));
            $arr_val = substr($code, (strpos($code, '=') + 1));
            $_REQUEST[$arr_ind] = $arr_val;
        }
    } else
        $modname = $_REQUEST['modname'];

    if ($_REQUEST['LO_save'] != '1' && !isset($_REQUEST['_openSIS_PDF']) && (strpos($modname, 'miscellaneous/') === false || $modname == 'misc/Registration.php' || $modname == 'miscellaneous/Export.php' || $modname == 'miscellaneous/Portal.php'))
        $_SESSION['_REQUEST_vars'] = $_REQUEST;

    $allowed = false;
    include 'Menu.php';
    foreach ($_openSIS['Menu'] as $modcat => $programs) {

        if (clean_param($_REQUEST['modname'], PARAM_NOTAGS) == $modcat . '/Search.php') {
            $allowed = true;
            break;
        }
        foreach ($programs as $program => $title) {

            if (clean_param($_REQUEST['modname'], PARAM_NOTAGS) == $program) {
                $allowed = true;
                break;
            }
        }
    }
    if (substr(clean_param($_REQUEST['modname'], PARAM_NOTAGS), 0, 14) == 'miscellaneous/')
        $allowed = true;

    if ($allowed || $_SESSION['take_mssn_attn']) {
        if (Preferences('SEARCH') != 'Y' && substr(clean_param($modname, PARAM_NOTAGS), 0, 6) != 'users/')
            $_REQUEST['search_modfunc'] = 'list';

        if (preg_match('/\.\./', $modname) !== 1)
            include 'modules/' . $modname;
    } else {
        if (User('USERNAME')) {


            if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            $ip = sqlSecurityFilter($ip);

            echo "" . _youReNotAllowedToUseThisProgram . "! " . _thisAttemptedViolationHasBeenLoggedAndYourIpAddressWasCaptured . ".";
            DBQuery("INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','$_REQUEST[modname]','" . User('USERNAME') . "')");
            Warehouse('footer');
            if ($openSISNotifyAddress)
                mail($openSISNotifyAddress, 'HACKING ATTEMPT', "INSERT INTO hacking_log (HOST_NAME,IP_ADDRESS,LOGIN_DATE,VERSION,PHP_SELF,DOCUMENT_ROOT,SCRIPT_NAME,MODNAME,USERNAME) values('$_SERVER[SERVER_NAME]','$ip','" . date('Y-m-d') . "','$openSISVersion','$_SERVER[PHP_SELF]','$_SERVER[DOCUMENT_ROOT]','$_SERVER[SCRIPT_NAME]','$_REQUEST[modname]','" . User('USERNAME') . "')");
        }
        exit;
    }

    if ($_SESSION['unset_student']) {
        unset($_SESSION['unset_student']);
        unset($_SESSION['staff_id']);
    }
}

echo "<div id='cal' class='divcal'> </div>";



if (!isset($_REQUEST['_openSIS_PDF'])) {
    for ($i = 1; $i <= $_openSIS['PrepareDate']; $i++) {
        echo '<script type="text/javascript">
    
</script>';
    }
    echo '</BODY>';
    echo '</HTML>';
}

function decode_unicode_url($str)
{
    $res = '';

    $i = 0;
    $max = strlen($str) - 6;
    while ($i <= $max) {
        $character = $str[$i];
        if ($character == '%' && $str[$i + 1] == 'u') {
            $value = hexdec(substr($str, $i + 2, 4));
            $i += 6;

            if ($value < 0x0080) // 1 byte: 0xxxxxxx
                $character = chr($value);
            else if ($value < 0x0800) // 2 bytes: 110xxxxx 10xxxxxx
                $character = chr((($value & 0x07c0) >> 6) | 0xc0)
                    . chr(($value & 0x3f) | 0x80);
            else // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
                $character = chr((($value & 0xf000) >> 12) | 0xe0)
                    . chr((($value & 0x0fc0) >> 6) | 0x80)
                    . chr(($value & 0x3f) | 0x80);
        } else
            $i++;

        $res .= $character;
    }

    return $res . substr($str, $i);
}

function code2utf($num)
{
    if ($num < 128)
        return chr($num);
    if ($num < 1024)
        return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 32768)
        return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128)
            . chr(($num & 63) + 128);
    if ($num < 2097152)
        return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128)
            . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}

function unescape($strIn, $iconv_to = 'UTF-8')
{
    $strOut = '';
    $iPos = 0;
    $len = strlen($strIn);
    while ($iPos < $len) {
        $charAt = substr($strIn, $iPos, 1);
        if ($charAt == '%') {
            $iPos++;
            $charAt = substr($strIn, $iPos, 1);
            if ($charAt == 'u') {
                // Unicode character
                $iPos++;
                $unicodeHexVal = substr($strIn, $iPos, 4);
                $unicode = hexdec($unicodeHexVal);
                $strOut .= code2utf($unicode);
                $iPos += 4;
            } else {
                // Escaped ascii character
                $hexVal = substr($strIn, $iPos, 2);
                if (hexdec($hexVal) > 127) {
                    // Convert to Unicode 
                    $strOut .= code2utf(hexdec($hexVal));
                } else {
                    $strOut .= chr(hexdec($hexVal));
                }
                $iPos += 2;
            }
        } else {
            $strOut .= $charAt;
            $iPos++;
        }
    }
    if ($iconv_to != "UTF-8") {
        $strOut = iconv("UTF-8", $iconv_to, $strOut);
    }
    return $strOut;
}
