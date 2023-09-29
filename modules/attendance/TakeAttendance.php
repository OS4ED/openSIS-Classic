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
include 'modules/attendance/ConfigInc.php';
if ($_REQUEST['period']) {
    $_SESSION['CpvId'] = $_REQUEST['period'];
}
$qr_att = DBGet(DBQuery("select count(course_period_id) as tot from course_period_var where course_period_id='" . UserCoursePeriod() . "'"));
if ($qr_att[1]['TOT'] > 1) {
    $qr_att1 = DBGet(DBQuery("select ID from course_period_var where course_period_id='" . UserCoursePeriod() . "' group by(COURSE_PERIOD_ID)"));

    $_SESSION['CpvId_attn'] = $qr_att1[1]['ID'];
}
$temp_date = $_REQUEST['date'];
$From = $_REQUEST['From'];
$to = $_REQUEST['to'];
# --------------------------------------------- Date Convertion Start ----------------------------------------------- #

function con_date($date) {
    $dt_arr = explode('/', $date);
    $temp_month = $dt_arr[0];
    if ($temp_month == 'Jan' || $temp_month == 'January')
        $dt_arr[0] = 1;
    elseif ($temp_month == 'Feb' || $temp_month == 'February')
        $dt_arr[0] = 2;
    elseif ($temp_month == 'Mar' || $temp_month == 'March')
        $dt_arr[0] = 3;
    elseif ($temp_month == 'Apr' || $temp_month == 'April')
        $dt_arr[0] = 4;
    elseif ($temp_month == 'May' || $temp_month == 'May')
        $dt_arr[0] = 5;
    elseif ($temp_month == 'Jun' || $temp_month == 'June')
        $dt_arr[0] = 6;
    elseif ($temp_month == 'Jul' || $temp_month == 'July')
        $dt_arr[0] = 7;
    elseif ($temp_month == 'Aug' || $temp_month == 'August')
        $dt_arr[0] = 8;
    elseif ($temp_month == 'Sep' || $temp_month == 'September')
        $dt_arr[0] = 9;
    elseif ($temp_month == 'Oct' || $temp_month == 'October')
        $dt_arr[0] = 10;
    elseif ($temp_month == 'Nov' || $temp_month == 'November')
        $dt_arr[0] = 11;
    elseif ($temp_month == 'Dec' || $temp_month == 'December')
        $dt_arr[0] = 12;
    return implode('/', $dt_arr);
}

# --------------------------------------------- date Convertion End ------------------------------------------------- #            
$final_date = con_date($temp_date);
if ($_REQUEST['dt'] == 1) {
    $final_date = $_SESSION['date_attn'];
}
$_REQUEST['dt'] = 0;
if ($_REQUEST['month_date'] && $_REQUEST['day_date'] && $_REQUEST['year_date']) {
    while (!VerifyDate($date = $_REQUEST['day_date'] . '-' . $_REQUEST['month_date'] . '-' . $_REQUEST['year_date']))
        $_REQUEST['day_date'] --;

    $posted_date = ucfirst(strtolower($_REQUEST['month_date'])) . '/' . $_REQUEST['day_date'] . '/' . $_REQUEST['year_date'];
    $final_date = con_date($posted_date);
    unset($_SESSION['date_attn']);
    $_SESSION['date_attn'] = $final_date;
} else {
    if (!$temp_date) {
        $final_date = date('n/j/Y');
        $_REQUEST['month_date'] = strtoupper(date('M'));
        $_REQUEST['day_date'] = date('j');
        $_REQUEST['year_date'] = date('y');
    } else {
        $temp_arr = explode('/', $temp_date);
        $_REQUEST['month_date'] = $temp_arr[0];
        $_REQUEST['day_date'] = $temp_arr[1];
        $_REQUEST['year_date'] = $temp_arr[2];
    }
}
DrawBC(""._attendance." > " . ProgramTitle());
if (!isset($_REQUEST['table'])) {
    $_REQUEST['table'] = '0';
}

if ($_REQUEST['table'] == '0') {
    $table = 'attendance_period';
} else {
    $table = 'lunch_period';
}



$date = date('Y-m-d', strtotime($final_date));
$sc_years_sql = 'SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND \'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN START_DATE AND END_DATE';
$sc_years_data = DBGet(DBQuery($sc_years_sql));
$sc_years_data = $sc_years_data[1]['MARKING_PERIOD_ID'];

$sc_sem_sql = 'SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND \'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN START_DATE AND 
END_DATE';
$sc_sem_data = DBGet(DBQuery($sc_sem_sql));
$sc_sem_data = $sc_sem_data[1]['MARKING_PERIOD_ID'];

$sc_qtr_sql = 'SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND \'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN START_DATE AND 
END_DATE';
$sc_qtr_data = DBGet(DBQuery($sc_qtr_sql));
$sc_qtr_data = $sc_qtr_data[1]['MARKING_PERIOD_ID'];

$i = 0;
if ($sc_years_data != '') {
    $mps = '\'' . $sc_years_data . '\',';
}

if ($sc_sem_data != '') {
    $mps.='\'' . $sc_sem_data . '\',';
}

if ($sc_qtr_data != '') {
    $mps.='\'' . $sc_qtr_data . '\',';
}
$mps = substr($mps, 0, -1);

$sql = 'SELECT cp.HALF_DAY 
FROM attendance_calendar acc,course_periods cp,course_period_var cpv,school_periods sp 
WHERE acc.SYEAR=\'' . UserSyear() . '\' 
AND cp.SCHOOL_ID=acc.SCHOOL_ID 
AND cp.SYEAR=acc.SYEAR 
AND acc.SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' 
AND cp.CALENDAR_ID=acc.CALENDAR_ID 
AND cp.COURSE_PERIOD_ID=\'' . (isset($_REQUEST['cp_id_miss_attn']) ? $_REQUEST['cp_id_miss_attn'] : UserCoursePeriod()) . '\'
AND ( cp.MARKING_PERIOD_ID IN (' . $mps . ') OR (cp.BEGIN_DATE<=\'' . date('Y-m-d') . '\'))
AND sp.PERIOD_ID=cpv.PERIOD_ID 
AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
AND ((sp.BLOCK IS NULL AND position(substring(\'UMTWHFS\' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0
OR sp.BLOCK IS NOT NULL 
AND acc.BLOCK IS NOT NULL 
AND sp.BLOCK=acc.BLOCK) or cpv.course_period_date=acc.school_date)
' . ($_REQUEST['table'] != '' ? 'AND cpv.DOES_ATTENDANCE=\'Y\'' : '');
if ($mps != '') {
    $course_RET = DBGET(DBQuery($sql));
}


$mp_id = GetCurrentMP('QTR', $date, false);
if (!$mp_id)
    $mp_id = GetCurrentMP('SEM', $date, false);
if (!$mp_id)
    $mp_id = GetCurrentMP('FY', $date, false);
// if running as a teacher program then openSIS[allow_edit] will already be set according to admin permissions

if (!isset($_openSIS['allow_edit'])) {
    // allow teacher edit if selected date is in the current quarter or in the corresponding grade posting period


    $current_qtr_id = $mp_id;
    $time = strtotime(DBDate('postgres'));


    if (($current_qtr_id || GetMP($mp_id, 'POST_START_DATE') && ($time <= strtotime(GetMP($mp_id, 'POST_END_DATE')))) && ($edit_days_before == '' || strtotime($date) <= $time + $edit_days_before * 86400) && ($edit_days_after == '' || strtotime($date) >= $time - $edit_days_after * 86400)) {
        $_openSIS['allow_edit'] = true;
    }
}

if ($_SESSION['PROFILE'] == 'teacher') {
    if ($_REQUEST['cpv_id'] != '') {
        $_SESSION['CpvId'] = $_REQUEST['cpv_id'];
    }
}

if (CpvId() != '') {
    $period_id_Mod = DBGet(DBQuery('SELECT PERIOD_ID FROM course_period_var WHERE ID=' . CpvId()));
    $_SESSION['UserPeriod'] = $period_id_Mod[1]['PERIOD_ID'];
}
//echo UserCoursePeriod().'----'.$_REQUEST['cp_id_miss_attn'];
$tabl = optional_param('table', '', PARAM_ALPHANUM);
$cq_cpid = ($_REQUEST['cp_id_miss_attn'] != '' ? $_REQUEST['cp_id_miss_attn'] : UserCoursePeriod());
$current_Q = 'SELECT ATTENDANCE_TEACHER_CODE,STUDENT_ID,ADMIN,COMMENT FROM ' . $table . ' WHERE SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\'AND PERIOD_ID=\'' . UserPeriod() . '\' AND COURSE_PERIOD_ID=\'' . $cq_cpid . '\'' . ($table == 'lunch_period' ? ' AND TABLE_NAME=\'' . $tabl . '\'' : '');
$current_RET = DBGet(DBQuery($current_Q), array(), array('STUDENT_ID'));

if ($_REQUEST['attendance'] && ($_POST['attendance'] || $_REQUEST['ajax'])) {

    $already_attn_flag = 0;

    $attendanceIgnoreScheduling = DBGet(DBQuery('SELECT IGNORE_SCHEDULING FROM `school_periods` WHERE  PERIOD_ID=\'' . UserPeriod() . '\' '));

    $cpClauseAddOn = '';

    if ($attendanceIgnoreScheduling[1]['IGNORE_SCHEDULING'] == 'Y') {
        $cpClauseAddOn = " AND COURSE_PERIOD_ID= '" . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . "'";
    }

    $attendanceAlreadyTaken = count(DBGet(DBQuery("SELECT * FROM attendance_period WHERE STUDENT_ID in (" . implode(', ', array_keys($_REQUEST['attendance'])) . ") AND SCHOOL_DATE = '$date' " . $cpClauseAddOn . " AND PERIOD_ID='" . UserPeriod() . "'"))) == count($_REQUEST['attendance']) ? true : false;

    $attendanceAlreadyTakenTwo = DBGet(DBQuery('SELECT * FROM `attendance_completed` WHERE SCHOOL_DATE = \'' . $date . '\' ' . $cpClauseAddOn . ' AND PERIOD_ID=\'' . UserPeriod() . '\' AND STAFF_ID = \'' . User('STAFF_ID') . '\''));


    if ($attendanceAlreadyTaken && count($attendanceAlreadyTakenTwo) == 0) {
        // print_r(ErrorMessage(array('Attendance is already taken for the students on selected day and period.'), 'note'));
        // die;
        $already_attn_flag++;
    }
    if($already_attn_flag == 0)
    {
    foreach ($_REQUEST['attendance'] as $student_id => $value) {
        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
            if (isset($_REQUEST['comment'][$student_id])) {
                $c = str_replace("'", "\'", $_REQUEST['comment'][$student_id]);
                $_REQUEST['comment'][$student_id] = clean_param($c, PARAM_SPCL);
            }
        }
        if ($current_RET[$student_id]) {

            $sql = 'UPDATE ' . $table . ' SET ATTENDANCE_TEACHER_CODE=\'' . substr($value, 5) . '\' ';

            $sql .= ',ATTENDANCE_CODE=\'' . substr($value, 5) . '\'';
            if (isset($_REQUEST['comment'][$student_id])) {
                $cmnt = trim($_REQUEST['comment'][$student_id]);
                $cmnt = clean_param($cmnt, PARAM_SPCL);
                $sql .= ',COMMENT=\'' . str_replace("'", "\'", $cmnt) . '\'';
            }
            $sql .= ' WHERE SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' AND COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND PERIOD_ID=\'' . UserPeriod() . '\'  AND STUDENT_ID=\'' . $student_id . '\'';
        } else {
            $cmnt = trim($_REQUEST['comment'][$student_id]);
            $cmnt = clean_param($cmnt, PARAM_SPCL);

            $sql = "INSERT INTO " . $table . " (STUDENT_ID,SCHOOL_DATE,MARKING_PERIOD_ID,PERIOD_ID,COURSE_PERIOD_ID,ATTENDANCE_CODE,ATTENDANCE_TEACHER_CODE,COMMENT" . ($table == 'lunch_period' ? ',TABLE_NAME' : '') . ") values('$student_id','$date','$mp_id','" . UserPeriod() . "','" . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . "','" . substr($value, 5) . "','" . substr($value, 5) . "','" . str_replace("'", "\'", $cmnt) . "'" . ($table == 'lunch_period' ? ",'" . optional_param('table', '', PARAM_ALPHANUM) . "'" : '') . ")";
        }
        DBQuery($sql);
        if ($_REQUEST['table'] == '0')
            UpdateAttendanceDaily($student_id, $date);
    }
    if ($_REQUEST['table'] == '0') {
        // echo 'SELECT \'completed\' AS COMPLETED FROM attendance_completed WHERE (STAFF_ID=\''.User('STAFF_ID').'\' OR SUBSTITUTE_STAFF_ID=\''.  User('STAFF_ID').'\') AND SCHOOL_DATE=\''.date('Y-m-d',strtotime($date)).'\' AND PERIOD_ID=\''.UserPeriod().'\' ';
        $RET = DBGet(DBQuery('SELECT \'completed\' AS COMPLETED FROM attendance_completed WHERE (STAFF_ID=\'' . User('STAFF_ID') . '\' OR SUBSTITUTE_STAFF_ID=\'' . User('STAFF_ID') . '\') AND SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' AND PERIOD_ID=\'' . UserPeriod() . '\' and course_period_id=\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\''));
        if (!count($RET)) {
            $teacher_type = DBGet(DBQuery('SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\''));
            $secondary_teacher_id = $teacher_type[1]['SECONDARY_TEACHER_ID'];
            $teacher_id = $teacher_type[1]['TEACHER_ID'];
            if ($secondary_teacher_id == User('STAFF_ID'))
                DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID,IS_TAKEN_BY_SUBSTITUTE_STAFF) values(\'' . $teacher_type[1]['TEACHER_ID'] . '\',\'' . $date . '\',\'' . UserPeriod() . '\',\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\',\'' . CpvId() . '\',\'' . $secondary_teacher_id . '\',\'Y\')');
            elseif ($teacher_id == User('STAFF_ID'))
                DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID) values(\'' . $teacher_type[1]['TEACHER_ID'] . '\',\'' . $date . '\',\'' . UserPeriod() . '\',\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\',\'' . CpvId() . '\',\'' . $secondary_teacher_id . '\')');
            else
                DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID) values(\'' . User('STAFF_ID') . '\',\'' . $date . '\',\'' . UserPeriod() . '\',\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\',\'' . CpvId() . '\',\'' . $secondary_teacher_id . '\')');
        }
        DBQuery('DELETE FROM missing_attendance WHERE  SCHOOL_DATE=\'' . $date . '\' AND COURSE_PERIOD_ID=\'' . ($cq_cpid != '' ? $cq_cpid : UserCoursePeriod()) . '\'  AND(TEACHER_ID=\'' . User('STAFF_ID') . '\' OR SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')  AND PERIOD_ID=\'' . UserPeriod() . '\'');
    }

    $current_RET = DBGet(DBQuery($current_Q), array(), array('STUDENT_ID'));
    unset($_SESSION['_REQUEST_vars']['attendance']);
}
   else
    {
        echo '<div class="alert alert-danger alert-styled-left m-b-0">'._attendanceIsAlreadyTakenForTheStudentsOnSelectedDayAndPeriod.'</div>';
    }
}


if ($_SESSION['PROFILE'] == 'teacher')
    $codes_RET_count = DBGet(DBQuery('SELECT COUNT(*) AS CODES FROM attendance_codes WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'  AND TYPE=\'teacher\' AND TABLE_NAME=\'' . $_REQUEST['table'] . '\'' . ($course_RET[1]['HALF_DAY'] ? ' AND STATE_CODE!=\'H\'' : '') . ' ORDER BY SORT_ORDER'));

$codes_RET = DBGet(DBQuery('SELECT ID,TITLE,DEFAULT_CODE,STATE_CODE FROM attendance_codes WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'  AND TABLE_NAME=\'' . $_REQUEST['table'] . '\'' . ($course_RET[1]['HALF_DAY'] ? ' AND STATE_CODE!=\'H\'' : '') . ' ORDER BY SORT_ORDER'));
if (is_countable($codes_RET) && count($codes_RET)) {
    foreach ($codes_RET as $code) {
        $extra['SELECT'] .= ",'$code[STATE_CODE]' AS CODE_" . $code['ID'];
        if ($code['DEFAULT_CODE'] == 'Y')
            $extra['functions']['CODE_' . $code['ID']] = '_makeRadioSelected';
        else
            $extra['functions']['CODE_' . $code['ID']] = '_makeRadio';
        $columns['CODE_' . $code['ID']] = '<div style="width: 50px; max-width:50px; white-space:normal;  **overflow: auto;**" class="text-center">'.$code['TITLE'].'</div>';
    }
} else
    $columns = array();
$extra['SELECT'] .= ',s.STUDENT_ID AS COMMENT';
$columns += array('COMMENT' => _comment);
if (!is_array($extra['functions']))
    $extra['functions'] = array();
$extra['functions'] += array('FULL_NAME' => '_makeTipMessage', 'COMMENT' => 'makeCommentInput');
$extra['DATE'] = date("Y-m-d", strtotime($date));

if (isset($_REQUEST['cpv_id_miss_attn'])) {
    $extra['ID'] = $_REQUEST['cpv_id_miss_attn'];
} else {
    $extra['ID'] = CpvId();
}

if (isset($_REQUEST['cpv_id_miss_attn'])) {
    $ms_cp_id = $_REQUEST['cpv_id_miss_attn'];
} else {
    $ms_cp_id = CpvId();
}
$schedule_type_check = DBGet(DBQuery("SELECT SCHEDULE_TYPE FROM course_periods cp,course_period_var cpv 
                            WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.ID='" . $ms_cp_id . "'"));
if ($schedule_type_check[1]['SCHEDULE_TYPE'] == 'BLOCKED') {
    $course_period_date_check = DBGet(DBQuery("SELECT COURSE_PERIOD_DATE FROM course_period_var WHERE ID='" . CpvId() . "'"));
    if ($course_period_date_check[1]['COURSE_PERIOD_DATE'] != '') {
        $extra['cpvdate'] = " AND cpv.COURSE_PERIOD_DATE='" . $extra['DATE'] . "'";
    }
}
if ($schedule_type_check[1]['SCHEDULE_TYPE'] == 'VARIABLE') {
    $day_check = date("D", strtotime($date));
    if ($day_check == 'Sun')
        $day_check = 'U';
    elseif ($day_check == 'Thu')
        $day_check = 'H';
    else
        $day_check = substr($day_check, 0, 1);

    $extra['cpvdate'] = " AND cpv.DAYS='" . $day_check . "'";
}
$extra['GROUP']=' s.STUDENT_ID ';

if ($_SESSION['PROFILE'] == 'teacher' && $codes_RET_count[1]['CODES'] == 0)
    $stu_RET = array();
else if ($_SESSION['PROFILE'] == 'teacher' && $codes_RET_count[1]['CODES'] != 0)
{
    unset($_REQUEST['username']);
    $stu_RET = GetStuListAttn($extra);
}
else
{
    unset($_REQUEST['username']);
    $stu_RET = GetStuListAttn($extra);
}
$date_note = $date != date('Y-m-d') ? ' <span class="text-danger m-l-10"><i class="icon-info22"></i> '._theSelectedDateIsNotToday.'</span>' : '';


# commented as requested


if ($_REQUEST['table'] == '0') {
    $completed_RET = DBGet(DBQuery('SELECT \'Y\' as COMPLETED,STAFF_ID,SUBSTITUTE_STAFF_ID,IS_TAKEN_BY_SUBSTITUTE_STAFF FROM attendance_completed WHERE (STAFF_ID=\'' . User('STAFF_ID') . '\' OR SUBSTITUTE_STAFF_ID=\'' . User('STAFF_ID') . '\') AND SCHOOL_DATE=\'' . $date . '\' AND PERIOD_ID=\'' . UserPeriod() . '\' AND CPV_ID=\'' . CpvId() . '\''));
    if ($completed_RET) {
        if ($completed_RET[1]['IS_TAKEN_BY_SUBSTITUTE_STAFF'] != 'Y' && User('STAFF_ID') == $completed_RET[1]['SUBSTITUTE_STAFF_ID'])
            $note = ErrorMessage(array('<IMG SRC=assets/check.gif>'._primaryTeacherHasTakenAttendanceTodayForThisPeriod.'.'), 'note');
        elseif ($completed_RET[1]['IS_TAKEN_BY_SUBSTITUTE_STAFF'] == 'Y' && User('STAFF_ID') == $completed_RET[1]['STAFF_ID'])
            $note = ErrorMessage(array('<i class="icon-checkmark4"></i> '._secondaryTeacherHasTakenAttendanceTodayForThisPeriod.'.'), 'note');
        else
            $note = ErrorMessage(array('<i class="icon-checkmark4"></i> '._youHaveTakenAttendanceTodayForThisPeriod.'.'), 'note', 'style="margin-bottom: 0;"');
    }

    if ($_SESSION['miss_attn'] == 1) {
        $get_profile_type = DBGet(DBQuery('SELECT PROFILE FROM user_profiles WHERE ID=' . UserProfileID()));
        if (($_REQUEST['username'] == 'admin' || $get_profile_type[1]['PROFILE'] == 'admin'))  //&& $_REQUEST['modfunc']=='attn'
            $note1 = '<a href=Modules.php?modname=users/TeacherPrograms.php?include=attendance/MissingAttendance.php&From=' . $From . '&to=' . $to . ' class="text-primary"><i class="icon-square-left"></i> '._backToMissingAttendanceList.' </a>';
    }
}




//if($_REQUEST['attn']=='miss')
//{
//DrawHeaderHome('<A HREF="Modules.php?modname=miscellaneous/Portal.php">Back to Missing Attendance List</A>');
//}
if (isset($_REQUEST['cp_id_miss_attn']))
    echo "<FORM ACTION=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&table=" . strip_tags(trim($_REQUEST['table'])) . "&username=" . strip_tags(trim($_REQUEST['username'])) . "&From=$From&to=$to&cp_id_miss_attn=" . $_REQUEST['cp_id_miss_attn'] . "&attn=" . strip_tags(trim($_REQUEST['attn'])) . " method=POST>";
else
    echo "<FORM ACTION=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&table=" . strip_tags(trim($_REQUEST['table'])) . "&username=" . strip_tags(trim($_REQUEST['username'])) . "&From=$From&to=$to&attn=" . strip_tags(trim($_REQUEST['attn'])) . " method=POST>";

echo '<div class="panel panel-default">';

$profile = DBGet(DBQuery('SELECT PROFILE FROM staff WHERE STAFF_ID=' . UserID()));
if ($profile[1]['PROFILE'] != "admin" && UserCoursePeriod() != '') {
    if (isset($_REQUEST['cp_id_miss_attn'])) {
        $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID=' . $_REQUEST['cp_id_miss_attn'] . ' AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') ORDER BY sp.SORT_ORDER ');
        $RET = DBGet($QI);
        $period_select = '<label class="control-label">'._choosePeriod.':</label>
        <SELECT class="form-control" name=period onchange="document.location.href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&period=\'+this.options[selectedIndex].value">';
        $period_select .= "<OPTION value='' selected>N/A</OPTION>";
        $fi = array();
        foreach ($RET as $period) {
            $date1 = ucfirst(date("l", strtotime($redate)));


            $fi = str_split($period['DAYS']);

            $days_arr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');
            $d = $days_arr[$date1];
            $period_select .= "<OPTION value=$period[ID]" . (($_REQUEST['cpv_id_miss_attn'] == $period['ID']) ? ' SELECTED' : '') . ">" . $period['SHORT_NAME'] . ($period['MARKING_PERIOD_ID'] != $fy_id ? ' ' . GetMP($period['MARKING_PERIOD_ID'], 'SHORT_NAME') : '') . (strlen($period['DAYS']) < 5 ? ' ' . $period['DAYS'] : '') . ' - ' . $period['COURSE_TITLE'] . "</OPTION>";
        }
        $period_select .= "</SELECT>";
    } else {

        $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID=' . UserCoursePeriod() . ' AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') ORDER BY sp.SORT_ORDER ');
        $RET = DBGet($QI);

        $period_select = '<label class="control-label">'._choosePeriod.':</label> 
        <SELECT class="form-control" name=period onchange="document.location.href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&period=\'+this.options[selectedIndex].value">';
        $period_select .= "<OPTION value='' selected>N/A</OPTION>";
        $fi = array();
        foreach ($RET as $period) {
            $date1 = ucfirst(date("l", strtotime($redate)));


            $fi = str_split($period['DAYS']);

            $days_arr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');
            $d = $days_arr[$date1];
            $period_select .= "<OPTION value=$period[ID]" . ((CpvId() == $period['ID']) ? ' SELECTED' : '') . ">" . $period['SHORT_NAME'] . ($period['MARKING_PERIOD_ID'] != $fy_id ? ' ' . GetMP($period['MARKING_PERIOD_ID'], 'SHORT_NAME') : '') . (strlen($period['DAYS']) < 5 ? ' ' . $period['DAYS'] : '') . ' - ' . $period['COURSE_TITLE'] . "</OPTION>";
            if (CpvId() == $period['ID']) {
                $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
            }
        }
        $period_select .= "</SELECT>";
    }
    if ($_REQUEST['attn'] == 'miss') {
        $backBtn = '<A HREF="Modules.php?modname=miscellaneous/Portal.php" class="btn btn-default"><i class="icon-arrow-left8"></i> '._backToMissingAttendanceList.'</a>';
        DrawHeader($backBtn, '<div class="form-inline">' . $period_select . '</div>');
    } else {
        DrawHeader('<div class="form-inline">' . $period_select . '</div>');
    }
}
$profile_check = DBGet(DBQuery("SELECT PROFILE FROM staff WHERE STAFF_ID=" . UserID()));
$profile_check = $profile_check[1]['PROFILE'];

$btnGo = '';
if (isset($_REQUEST['cp_id_miss_attn'])) {
    $btnGo = "<input type='button' value='"._go."' class='btn btn-primary m-l-10 m-r-10' onClick='document.location.href=\"Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php&amp;period=" . strip_tags(trim($_REQUEST['cpv_id_miss_attn'])) . "&amp;include=attendance/TakeAttendance.php&amp;day_date=\"+this.form.day_date.value+\"&amp;year_date=\"+this.form.year_date.value+\"&amp;table=0&amp;month_date=\"+this.form.month_date.value;' />";
} else
    $btnGo = "<input type='button' value='"._go."' class='btn btn-primary m-l-10 m-r-10' onClick='document.location.href=\"Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php&amp;period=" . strip_tags(trim($_REQUEST['period'])) . "&amp;include=attendance/TakeAttendance.php&amp;day_date=\"+this.form.day_date.value+\"&amp;year_date=\"+this.form.year_date.value+\"&amp;table=0&amp;month_date=\"+this.form.month_date.value;' />";


if ($profile_check == 'admin') {

    if ((is_countable($stu_RET) && count($stu_RET) != 0) && (is_countable($course_RET) && count($course_RET) != 0)) {

        DrawHeader(SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"') . '<div class="form-inline"><div class="inline-block">' . DateInputAY($date, 'date', 1) . '</div>' . $btnGo . $date_note . '</div>');
    } else {
        echo '<div class="panel-body">';
        echo '<div class="form-inline">';
        echo '<div class="inline-block">' . DateInputAY($date, 'date', 2) . '</div>' . $btnGo . $date_note;
        echo '</div>'; //.form-inline
        echo '</div>';
        //DrawHeader('<div class="form-inline"></div>');
    }
} else {

    if ((is_countable($stu_RET) && count($stu_RET) != 0) && (is_countable($course_RET) && count($course_RET) != 0)) {

        DrawHeader(SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"') . '<div class="form-inline"><div class="inline-block">' . DateInputAY($date, 'date', 3) . '</div>' . $btnGo . $date_note . '</div>');
    } else {
        DrawHeader('<div class="form-inline"><div class="inline-block">' . DateInputAY($date, 'date', 4) . '</div>' . $btnGo . $date_note . '</div>');
    }
}
//if (isset($_REQUEST['cp_id_miss_attn'])) {
//    echo "<div style='padding-left:10px; padding-top:8px; float:left;'><input type='button' value='Go' class='btn_medium' onClick='document.location.href=\"Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php&amp;period=" . strip_tags(trim($_REQUEST[cpv_id_miss_attn])) . "&amp;include=attendance/TakeAttendance.php&amp;day_date=\"+this.form.day_date.value+\"&amp;year_date=\"+this.form.year_date.value+\"&amp;table=0&amp;month_date=\"+this.form.month_date.value;' /></div><div style='clear:both;'></div>";
//} else
//    echo "<div style='padding-left:10px; padding-top:8px; float:left;'><input type='button' value='Go' class='btn_medium' onClick='document.location.href=\"Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php&amp;period=" . strip_tags(trim($_REQUEST[period])) . "&amp;include=attendance/TakeAttendance.php&amp;day_date=\"+this.form.day_date.value+\"&amp;year_date=\"+this.form.year_date.value+\"&amp;table=0&amp;month_date=\"+this.form.month_date.value;' /></div><div style='clear:both;'></div>";


DrawHeader($note);
DrawHeader($note1);

$LO_columns = array('FULL_NAME' => _student,
 'STUDENT_ID' => _studentId,
 'GRADE_ID' => _grade,
 ) + $columns;

$tabs[] = array('title' => 'Attendance', 'link' => "Modules.php?modname=$_REQUEST[modname]&table=0&month_date=$_REQUEST[month_date]&day_date=$_REQUEST[day_date]&year_date=$_REQUEST[year_date]");
$categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM attendance_code_categories WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
foreach ($categories_RET as $category)
    $tabs[] = array('title' => $category['TITLE'], 'link' => "Modules.php?modname=$_REQUEST[modname]&table=$category[ID]&month_date=$_REQUEST[month_date]&day_date=$_REQUEST[day_date]&year_date=$_REQUEST[year_date]");


if (is_countable($categories_RET) && count($categories_RET)) {
    if (is_countable($course_RET) && count($course_RET) != 0)
        echo '<CENTER>' . WrapTabs($tabs, "Modules.php?modname=$_REQUEST[modname]&table=$_REQUEST[table]&month_date=$_REQUEST[month_date]&day_date=$_REQUEST[day_date]&year_date=$_REQUEST[year_date]") . '</CENTER>';
    $extra = array('download' =>true, 'search' =>true);
    $singular = _student;
    $plural = _students;
}
else {
    $extra = array();
    $singular = _student;
    $plural = _students;
}
if (!$mp_id) {
    echo '<div class="panel-body p-t-0 p-b-0"><div class="alert alert-danger alert-bordered">'._theSelectedDateIsNotInASchoolQuarter.'.</div></div>';
} else {
    if (is_countable($course_RET) && count($course_RET) != 0) {
        $posted_date2 = ucfirst(strtolower($_REQUEST['month_date'])) . '-' . $_REQUEST['day_date'] . '-' . $_REQUEST['year_date'];
        if ($_REQUEST['month_date'] && $_REQUEST['day_date'] && $_REQUEST['year_date']) {
            $cur_date = date('Y-m-d', strtotime($posted_date2));
        } else {
            $cur_date = date('Y-m-d');
        }
        if ($_REQUEST['period'] || ( $profile_check == 'admin' && $_SESSION['CpvId'] != '') || $_SESSION['CpvId'] != '')
            ListOutput($stu_RET, $LO_columns, $singular, $plural, array(), array(), $extra);
        //echo '</br>';
        echo '<div class="panel-footer">';
        echo '<div class="heading-elements text-right p-r-20">';
        if ((is_countable($stu_RET) && count($stu_RET) > 0) && (is_countable($course_RET) && count($course_RET) > 0)) {
            echo SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"');
        }
        echo '</div>'; //.heading-elements
        echo '</div>'; //.panel-footer
    } else {
        if ($_REQUEST['period']) {
            $extra_sql = 'SELECT COUNT(*) AS day_exist FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\'';
            $extra_ret = DBGET(DBQuery($extra_sql));
            if (isset($extra_ret[1]['DAY_EXIST']) && $extra_ret[1]['DAY_EXIST'] == 0)
                echo '<div class="panel-body p-b-0"><div class="alert alert-danger alert-bordered">You cannot take attendance on this day, it is a school holiday</div>';
            else
                echo '<div class="panel-body p-b-0"><div class="alert alert-danger alert-bordered">You cannot take attendance for this period on this day</div>';
        }
    }
}
echo '</div>'; //.panel
echo '</FORM>';

function _makeRadio($value, $title) {
    global $THIS_RET, $current_RET;
    $flag = false;
    if ($current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']) {
        $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . $current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']));
        if ($attn_code_type[1]['TYPE'] == 'official')
            $flag = true;
    }
    $colors = array('P' => 'control-present', 'A' => 'control-absent', 'H' => 'control-late', 'T' => 'control-tardy');
    if ($current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE'] == substr($title, 5)) {

        if ($_SESSION['PROFILE'] == 'admin')
            return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' CHECKED><span></span></label></TD></TR></TABLE>";
        else {
            if ($flag == true)
                return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' disabled=disabled CHECKED><span></span></label></TD></TR></TABLE>";
            else {
                $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . $current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']));
                if ($attn_code_type[1]['TYPE'] == 'official')
                    return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' disabled=disabled CHECKED><span></span></label></TD></TR></TABLE>";
                else
                    return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' CHECKED><span></span></label></TD></TR></TABLE>";
            }
        }
    }
    else {
        if ($_SESSION['PROFILE'] == 'admin')
            return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title'" . (AllowEdit() ? '' : ' ') . " /><span></span></label></TD></TR></TABLE>";
        else {

            if ($flag == true)
                return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]]  disabled=disabled value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
            else {
                $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . substr($title, 5)));
                if ($attn_code_type[1]['TYPE'] == 'official')
                    return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]]  disabled=disabled value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
                else
                    return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]]  value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
            }
        }
    }
}

function _makeRadioSelected($value, $title) {
    global $THIS_RET, $current_RET;
    $flag = false;
    $colors = array('P' => 'control-present', 'A' => 'control-absent', 'H' => 'control-late', 'T' => 'control-tardy');
    $colors1 = array('P' => 'control-present', 'A' => 'control-absent', 'H' => 'control-late', 'T' => 'control-tardy');
    if ($current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE'] != '') {
        $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . $current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']));
        if ($attn_code_type[1]['TYPE'] == 'official')
            $flag = true;
        if ($current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE'] != substr($title, 5)) {
            if ($_SESSION['PROFILE'] == 'admin')
                return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
            else {
                if ($flag == true)
                    return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] disabled=disabled value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
                else {
                    $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . $current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']));
                    if ($attn_code_type[1]['TYPE'] == 'official') {
                        return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] disabled=disabled value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
                    } else {
                        return "<TABLE align=center><TR><TD><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]]  value='$title'" . (AllowEdit() ? '' : ' ') . "><span></span></label></TD></TR></TABLE>";
                    }
                }
            }
        } else {
            if ($_SESSION['PROFILE'] == 'admin')
                return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' CHECKED><span></span></label></TD></TR></TABLE>";
            else {
                if ($flag == true)
                    return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' disabled=disabled CHECKED><span></span></label></TD></TR></TABLE>";
                else {
                    $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . $current_RET[$THIS_RET['STUDENT_ID']][1]['ATTENDANCE_TEACHER_CODE']));
                    if ($attn_code_type[1]['TYPE'] == 'official')
                        return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' disabled=disabled CHECKED><span></span></label></TD></TR></TABLE>";
                    else
                        return "<TABLE align=center><TR><TD " . ($colors[$value] ? ' style=background-color:' . $colors[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title'  CHECKED><span></span></label></TD></TR></TABLE>";
                }
            }
        }
    }
    else {
        $attn_code_type = DBGet(DBQuery('SELECT TYPE FROM attendance_codes WHERE ID=' . substr($title, 5)));
        if ($attn_code_type[1]['TYPE'] == 'official' && $_SESSION['PROFILE'] != 'admin')
            return "<TABLE align=center><TR><TD " . ($colors1[$value] ? ' style=background-color:' . $colors1[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' disabled=disabled CHECKED><span></span></label></TD></TR></TABLE>";
        else
            return "<TABLE align=center><TR><TD " . ($colors1[$value] ? ' style=background-color:' . $colors1[$value] : '') . "><label class=\"radio-inline m-0\"><INPUT class=\"" . $colors[$value] . "\" type=radio name=attendance[$THIS_RET[STUDENT_ID]] value='$title' CHECKED><span></span></label></TD></TR></TABLE>";
    }
}

function _makeTipMessage($value, $title) {
    global $THIS_RET, $StudentPicturesPath;

    if ($StudentPicturesPath && ($file = @fopen($picture_path = $StudentPicturesPath . '/' . $THIS_RET['STUDENT_ID'] . '.JPG', 'r') || $file = @fopen($picture_path = $StudentPicturesPath . '/' . $THIS_RET['STUDENT_ID'] . '.JPG', 'r')))
        return '<DIV onMouseOver=\'stm(["' . str_replace("'", '&#39;', $THIS_RET['FULL_NAME']) . '","<IMG SRC=' . str_replace('\\', '\\\\', $picture_path) . '>"],["white","#333366","","","",,"black","#e8e8ff","","","",,,,2,"#333366",2,,,,,"",,,,]);\' onMouseOut=\'htm()\'>' . $value . '</DIV>';
    else
        return $value;
}

function makeCommentInput($student_id, $column) {
    global $current_RET;

    return TextInput($current_RET[$student_id][1]['COMMENT'], 'comment[' . $student_id . ']', '', 'maxlength=80', true, true);
}

?>
