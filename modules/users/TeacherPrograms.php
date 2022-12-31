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
include('../../RedirectModulesInc.php');
$cp_id = $_REQUEST['cp_id'];
if (UserStaffID() || $_REQUEST['staff_id'])
    echo "<FORM class=\"form-horizontal\" name=\"search\" action=Modules.php?modname=$_REQUEST[modname]&dt=1&pr=1 method=POST>";
DrawBC(""._users." > Teacher Programs");
###########################################
if (UserStaffID() || $_REQUEST['staff_id']) {
    if ($_REQUEST['modfunc'] != 'save' && $_REQUEST[modname] != 'users/TeacherPrograms.php?include=attendance/MissingAttendance.php' && $_REQUEST[modname] != 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php') {

        if ($_REQUEST['staff_id'])
            $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID=\'' . $_REQUEST['staff_id'] . '\''));
        else
            $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\''));
        $count_staff_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM staff'));
        if ($count_staff_RET[1]['NUM'] > 1) {
            if (trim($_REQUEST['process']) == "")
                DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedUser.': ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> '._backToUserList.'</A></span><div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div></div>');
        }else {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedUser.': ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'] . '</h6> <div class="heading-elements"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div>');
        }
    }
}
#############################################
if ($_REQUEST['include'] != 'attendance/MissingAttendance.php') {

    if (!UserStaffID())
        Search('teacher_id', 'teachers_option_all');
    else {
        $profile = DBGet(DBQuery('SELECT PROFILE FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\''));
        if ($profile[1]['PROFILE'] != 'teacher') {
            unset($_SESSION['staff_id']);
//            echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            Search('teacher_id', 'teachers_option_all');
        }
    }
} else {
    Search_Miss_Attn('staff_id', 'teacher');
}

if (UserStaffID()) {
    $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,CONCAT(cp.SHORT_NAME,\'-\',cpv.DAYS) as SHOW_TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE,cp.TITLE as COURSE_PERIOD_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND (cp.MARKING_PERIOD_ID IN (' . GetAllMP_mod(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ') OR cp.MARKING_PERIOD_ID IS NULL) ORDER BY sp.SORT_ORDER ');
    $RET = DBGet($QI);
    // get the fy marking period id, there should be exactly one fy marking period
    $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
    $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

    if (isset($cp_id)) {
        $block_schedule_check = DBGet(DBQuery("SELECT COUNT(*) AS TOTAL FROM course_period_var WHERE COURSE_PERIOD_ID='" . $cp_id . "' 
                                                AND COURSE_PERIOD_DATE IS NULL "));

        if ($block_schedule_check[1]['TOTAL'] != 0) {

            $_REQUEST['period'] = $cp_id;
        } else {
            $_REQUEST['period'] = $RET[1]['ID'];
        }
    }
    if ($_REQUEST['period'] && !isset($_REQUEST['cpv_id']) && $_REQUEST['cpv_id'] == '') {
        if ($_REQUEST['period'] == 'na') {
            unset($_SESSION['CpvId']);
            unset($_SESSION['UserCoursePeriod']);
            unset($_SESSION['UserPeriod']);
            unset($_REQUEST['period']);
        } else
            $_SESSION['CpvId'] = $_REQUEST['period'];
    }
    elseif (isset($_REQUEST['cpv_id']) && $_REQUEST['cpv_id'] != '') {
        $_SESSION['CpvId'] = $_REQUEST['cpv_id'];
        $_REQUEST['period'] = $_REQUEST['cpv_id'];
    } else {
        if ($_REQUEST['modname'] != 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php' && $_REQUEST['modname'] != 'users/TeacherPrograms.php?include=grades/ProgressReports.php') {
            if (count($RET) > 0) {
                $_SESSION['CpvId'] = $RET[1]['ID'];
                $_REQUEST['period'] = $RET[1]['ID'];
            }
        }
    }
    if ($_REQUEST['p_id'])
        $_SESSION['UserPeriod'] = $_REQUEST['p_id'];

    foreach ($RET as $index => $val) {
        if ($val['ID'] == $_REQUEST['period']) {
            $_SESSION['UserCoursePeriod'] = $RET[$index]['COURSE_PERIOD_ID'];
            break;
        }
    }
    $incl_page = $_REQUEST['include'];
    if ($incl_page == 'grades/ProgressReports.php') {

        $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,cp.SHORT_NAME as SHOW_TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE,cp.TITLE as COURSE_PERIOD_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND (cp.MARKING_PERIOD_ID IN (' . GetAllMP_mod(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ') OR cp.MARKING_PERIOD_ID IS NULL) GROUP BY cp.COURSE_PERIOD_ID ORDER BY sp.SORT_ORDER ');
        $RET = DBGet($QI);
         if (isset($_REQUEST['include_inactive']) && $_REQUEST['include_inactive'] == 'Y')
            echo '<input type="hidden" name="include_inactive" value="Y">';
        if (!$_SESSION['take_mssn_attn']) {
            $period_select = "<div class=\"form-inline\"><div class=\"form-group\"><label class=\"control-label\">"._choosePeriod.":</label><SELECT class=\"form-control\" name=period onChange='this.form.submit();'>";
            $period_select .= "<OPTION value='na' selected>N/A</OPTION>";
        } else {
            $period_select = "<div class=\"form-inline\"><div class=\"form-group\"><SELECT name=period class=\"form-control\" onChange='document.forms[1].submit();' style='visibility:hidden;'>";
        }
        foreach ($RET as $period) {

            $period_select .= "<OPTION value=$period[ID]" . ((CpvId() == $period['ID']) ? ' SELECTED' : '') . ">" . $period['SHORT_NAME'] . ($period['MARKING_PERIOD_ID'] != $fy_id ? ' ' . GetMP($period['MARKING_PERIOD_ID'], 'SHORT_NAME') : '') . (strlen($period['DAYS']) < 5 ? ' ' . $period['DAYS'] : '') . ' - ' . $period['COURSE_TITLE'] . "</OPTION>";

            if (CpvId() == $period['ID']) {
                $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
            }
        }
        $period_select .= "</SELECT></div></div>";
        if (!$_REQUEST['modfunc'] && !$_REQUEST['staff_id'] && !($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) || $_REQUEST['pr'] == 1){
            echo '<div class="panel panel-default">';
            echo '<div class="panel-heading">';
            echo $period_select;
            echo '</div>';
            echo '</div>';
        }

        echo '</FORM>';
        unset($_openSIS['DrawHeader']);

        $_openSIS['allow_edit'] = AllowEdit($_REQUEST['modname']);
        $_openSIS['User'] = array(1 => array('STAFF_ID' => UserStaffID(), 'NAME' => GetTeacher(UserStaffID()), 'USERNAME' => GetTeacher(UserStaffID(), '', 'USERNAME'), 'PROFILE' => 'teacher', 'SCHOOLS' => ',' . UserSchool() . ',', 'SYEAR' => UserSyear()));

        include('modules/' . $_REQUEST['include']);
    }
    else {
        echo '<div class="panel panel-default">';
        if ($incl_page != 'attendance/MissingAttendance.php' && $incl_page == 'attendance/TakeAttendance.php') {

            if (!$_SESSION['take_mssn_attn']) {

                if (!isset($_REQUEST['process'])) {
                    $period_select = '<div class="clearfix">';

                    $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,cp.SHORT_NAME as CPSHORT, cpv.DAYS,cpv.COURSE_PERIOD_DATE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE,cp.TITLE as COURSE_PERIOD_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND (cp.MARKING_PERIOD_ID IN (' . GetAllMP_mod(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ') OR cp.MARKING_PERIOD_ID IS NULL) ORDER BY sp.SORT_ORDER ');
                    $RET = DBGet($QI);
                    $period_select .= "<div class=\"form-inline pull-right\"><div class=\"input-group\"><span class=\"input-group-addon\">"._choosePeriod." : </span><SELECT name=period class=\"form-control\" onChange='this.form.submit();'>";
                    $period_select .= "<OPTION value='na' selected>N/A</OPTION>";
                    foreach ($RET as $period) {
                        if ($period['DAYS'] != '')
                            $day = $period['DAYS'];
                        else {
                            $day = date("D", strtotime($period['COURSE_PERIOD_DATE']));
                            if ($day == 'Mon')
                                $day = 'M';
                            if ($day == 'Tue')
                                $day = 'T';
                            if ($day == 'Wed')
                                $day = 'W';
                            if ($day == 'Thu')
                                $day = 'H';
                            if ($day == 'Fri')
                                $day = 'F';
                            if ($day == 'Sat')
                                $day = 'S';
                            if ($day == 'Sun')
                                $day = 'U';
                        }
                        $period_select .= "<OPTION value=$period[ID]" . ((CpvId() == $period['ID']) ? ' SELECTED' : '') . ">" . $period['CPSHORT'] . "-" . $day . "</OPTION>";
                        if (CpvId() == $period['ID']) {
                            $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
                        }
                    }
                    $period_select .= "</SELECT></div></div>";

                    if ($_REQUEST['miss_attn'] != '1' && $_SESSION['miss_attn'] != '1' && $_REQUEST['attn'] != 'miss' && $_REQUEST['modname'] == 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php') {
                        $period_select .= '<div class="inline-block p-t-10"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-arrow-left8"></i> '._backToUserList.' </A></div>';
                }
                    $period_select .= '</div>';
            }
        }
        }

        if ($incl_page != 'attendance/MissingAttendance.php' && $incl_page != 'attendance/TakeAttendance.php') {

            if (!$_SESSION['take_mssn_attn']) {

                if (!isset($_REQUEST['process'])) {

                    $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,cp.SHORT_NAME as CPSHORT, cpv.DAYS,cpv.COURSE_PERIOD_DATE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE,cp.TITLE as COURSE_PERIOD_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND (cp.MARKING_PERIOD_ID IN (' . GetAllMP_mod(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ') OR cp.MARKING_PERIOD_ID IS NULL) GROUP BY cp.COURSE_PERIOD_ID ORDER BY sp.SORT_ORDER ');
                    $RET = DBGet($QI);
                    $period_select = "<div class=\"form-inline\"><label class=\"control-label\">"._choosePeriod." : </label> &nbsp;<SELECT name=period onChange='this.form.submit();' class=\"form-control\">";
                    $period_select .= "<OPTION value='na' selected>N/A</OPTION>";
                    foreach ($RET as $period) {
                        $period_select .= "<OPTION value=$period[ID]" . ((CpvId() == $period['ID']) ? ' SELECTED' : '') . ">" . $period['COURSE_PERIOD_TITLE'] . "</OPTION>";
                        if (CpvId() == $period['ID']) {
                            $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
                        }
                    }
                    $period_select .= "</SELECT></div>";
                }
            }
        }

        $profile = DBGet(DBQuery('SELECT PROFILE FROM staff WHERE STAFF_ID=' . UserID()));
        if ($profile[1]['PROFILE'] == "admin")
            DrawHeader($period_select);

        echo '</FORM>';
        unset($_openSIS['DrawHeader']);

        $_openSIS['allow_edit'] = AllowEdit($_REQUEST['modname']);
        $_openSIS['User'] = array(1 => array('STAFF_ID' => UserStaffID(), 'NAME' => GetTeacher(UserStaffID()), 'USERNAME' => GetTeacher(UserStaffID(), '', 'USERNAME'), 'PROFILE' => 'teacher', 'SCHOOLS' => ',' . UserSchool() . ',', 'SYEAR' => UserSyear()));


        //echo '<div class="panel-body">';
        include('modules/' . $_REQUEST['include']);
        //echo '</div>';

        echo '</div>'; //.panel
    }
}
?>
<script type="text/javascript">
    function close_window()
    {
        window.close();
    }
</script>
