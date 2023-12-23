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
include('lang/language.php');


//if(isset($_REQUEST['dup_msg']) && $_REQUEST['dup_msg']=='modl_cal')
//{
//    unset($_REQUEST);
//    echo '<script>window.location.href=Modules.php?modname=schoolsetup/Calendar.php</script>';
//}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'print') {
    echo '<style type="text/css">.print_wrapper{font-family:arial;font-size:12px;}.print_wrapper table table{border-right:1px solid #666;border-bottom:1px solid #666;}.print_wrapper table td{font-size:12px;}</style>';
    echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
    echo "<tr><td  style=\"font-size:15px; font-weight:bold; padding-top:10px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _listOfEvents . "</div></td><td align=right style=\"padding-top:10px;\">" . ProperDate(DBDate()) . "<br />" . _listOfEvents . " openSIS</td></tr><tr><td colspan=2 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
    echo "<div class=print_wrapper>";
    ListOutputFloat($_SESSION['events_RET'], array('SCHOOL_DATE' => _date, 'TITLE' => _date, 'DESCRIPTION' => _description), _event, _events, '', '', array('search' => _date, 'count' => _description));
    echo "</div>";
}
if (!$_REQUEST['month'])
    $_REQUEST['month'] = date("n");
else
    $_REQUEST['month'] = MonthNWSwitch($_REQUEST['month'], 'tonum') * 1;
if (!$_REQUEST['year'])
    $_REQUEST['year'] = date("Y");

$time = mktime(0, 0, 0, $_REQUEST['month'], 1, $_REQUEST['year']);
if (User('PROFILE') != 'student')
    DrawBC("" . _schoolSetup . " > " . ProgramTitle());
else
    DrawBC("School Info > " . ProgramTitle());
$cal_found_qr = DBGet(DBQuery('SELECT count(*) as TOT from school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
//if ((clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'create' || !$_REQUEST['calendar_id']) && $_REQUEST['modfunc']!='detail' && USER('PROFILE')=='admin') 
if ((clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'create' || $cal_found_qr[1]['TOT'] == 0) && User('PROFILE') == 'admin') {
    $fy_RET = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
    $fy_RET = $fy_RET[1];

    if ($_REQUEST['month__min'] == '' && $_REQUEST['modfunc'] != 'create')
        echo '<div class="alert alert-danger no-border">' . _noCalendarsWereFound . '</div>';

    $message = '<div class="row">';
    $message .= '<div class="col-md-12">';
    $message .= '<div class="row">';

    $message .= '<div class="col-md-8">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-2 control-label text-right">' . _title . '</label>';
    $message .= '<div class="col-md-10">';
    $message .= '<INPUT type=text name=title class=form-control id=title>';
    $message .= '<div class="checkbox checkbox-switch switch-success"><label><INPUT type=checkbox name=default value=Y><span></span>' . _defaultCalendarForThisSchool . '</label></div>';
    $message .= '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row
    $message .= '<div class="row">';

    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _from . '</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($fy_RET['START_DATE'], '_min', 1) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _to . '</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($fy_RET['END_DATE'], '_max', 2) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row 
    $message .= '<div class="row">';

    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _weekdays . '</label>';
    $message .= '<div class="col-md-8"><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[0]>' . _sunday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[1] CHECKED>' . _monday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[2] CHECKED>' . _tuesday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[3] CHECKED>' . _wednesday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[4] CHECKED>' . _thursday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[5] CHECKED>' . _friday . '</label></div> <div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[6]>' . _saturday . '</label></div></div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= calendarEventsVisibility();
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row

    $message .= '</div>'; //.col-md-12
    $message .= '</div>'; //.row




    if (Prompt_Calender('' . _createANewCalendar . '', '', $message)) {
        $begin = mktime(0, 0, 0, MonthNWSwitch($_REQUEST['month__min'], 'to_num'), $_REQUEST['day__min'] * 1, $_REQUEST['year__min']) + 43200;
        $end = mktime(0, 0, 0, MonthNWSwitch($_REQUEST['month__max'], 'to_num'), $_REQUEST['day__max'] * 1, $_REQUEST['year__max']) + 43200;

        $weekday = date('w', $begin);

        $col = 'Calender_Title';
        $cal_title = singleQuoteReplace("'", "''", $_REQUEST['title']);
        $dup_cal_title = DBGet(DBQuery('SELECT count(*) AS NO FROM school_calendars WHERE TITLE=\'' . $cal_title . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));

        // $fetch_calendar_id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'school_calendars\''));
        // $calendar_id[1]['CALENDAR_ID'] = $fetch_calendar_id[1]['AUTO_INCREMENT'];
        // $calendar_id = $calendar_id[1]['CALENDAR_ID'];

        if ($dup_cal_title[1]['NO'] == 0)
            DBQuery('INSERT INTO school_calendars (SYEAR,SCHOOL_ID,TITLE,DEFAULT_CALENDAR,DAYS) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $cal_title . '\',\'' . $_REQUEST['default'] . '\',\'' . conv_day($_REQUEST['weekdays']) . '\')');
       
        $calendar_id = mysqli_insert_id($connection);

        if ($dup_cal_title[1]['NO'] == 0) {
            for ($i = $begin; $i <= $end; $i += 86400) {
                if ($_REQUEST['weekdays'][$weekday] == 'Y') {
                    $sql = 'INSERT INTO attendance_calendar (SYEAR,SCHOOL_ID,SCHOOL_DATE,MINUTES,CALENDAR_ID) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . date('Y-m-d', $i) . '\',\'999\',\'' . $calendar_id . '\')';

                    DBQuery($sql);
                }
                $weekday++;
                if ($weekday == 7)
                    $weekday = 0;
            }
        }
        if ($_REQUEST['default'] && $dup_cal_title[1]['NO'] == 0)
            DBQuery('Update school_calendars SET DEFAULT_CALENDAR=NULL WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'');
        // if ($dup_cal_title[1]['NO'] == 0)
        //     DBQuery('INSERT INTO school_calendars (SYEAR,SCHOOL_ID,TITLE,DEFAULT_CALENDAR,DAYS) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $cal_title . '\',\'' . $_REQUEST['default'] . '\',\'' . conv_day($_REQUEST['weekdays']) . '\')');
        if (is_countable($_REQUEST['profiles']) && count($_REQUEST['profiles'])) {
            $profile_sql = 'INSERT INTO calendar_events_visibility(calendar_id,profile_id,profile) VALUES';
            foreach ($_REQUEST['profiles'] as $key => $profile) {
                if (is_numeric($key)) {
                    $profile_sql .= '(\'' . $calendar_id . '\',\'' . $key . '\',NULL),';
                } else {
                    $profile_sql .= '(\'' . $calendar_id . '\',NULL,\'' . $key . '\'),';
                }
            }
            $profile_sql = substr($profile_sql, 0, -1);
            if ($dup_cal_title[1]['NO'] == 0) {
                DBQuery($profile_sql);
            } else {
                echo '<div class="alert alert-danger">' . _calenderTitleAlreadyExists . '</div>';
            }
        }
        if ($dup_cal_title[1]['NO'] == 0)
            $_REQUEST['calendar_id'] = $calendar_id;
        unset($_REQUEST['modfunc']);
        unset($_SESSION['_REQUEST_vars']['modfunc']);
        unset($_SESSION['_REQUEST_vars']['weekdays']);
        unset($_SESSION['_REQUEST_vars']['title']);
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete_calendar') {

    $colmn = 'Calender_Id';
    $cal_title = paramlib_validation($colmn, $_REQUEST['calendar_id']);
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_enrollment WHERE CALENDAR_ID=' . $cal_title . ''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    if ($has_assigned == 0) {
        $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM course_periods WHERE CALENDAR_ID=' . $cal_title . ''));
        $has_assigned_cp = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    }
    if ($has_assigned > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseStudentsAreEnrolledInThisCalendar);
    } elseif ($has_assigned_cp > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseCoursePeriodsAreCreatedOnThisCalendar);
    } else {
        if (DeletePromptCommon('calendar')) {
            DBQuery('DELETE FROM attendance_calendar WHERE CALENDAR_ID=' . $cal_title . '');
            DBQuery('DELETE FROM school_calendars WHERE CALENDAR_ID=' . $cal_title . '');
            $default_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND DEFAULT_CALENDAR=\'Y\''));
            if (count($default_RET))
                $_REQUEST['calendar_id'] = $default_RET[1]['CALENDAR_ID'];
            else {
                $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                if (count($calendars_RET))
                    $_REQUEST['calendar_id'] = $calendars_RET[1]['CALENDAR_ID'];
                else
                    $error = array('' . _thereAreNoCalendarsYetSetup . '.');
            }
            unset($_REQUEST['modfunc']);
            unset($_SESSION['_REQUEST_vars']['modfunc']);
            unset($_REQUEST['calendar_id']);
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '"; window.close();</script>';
        }
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'edit_calendar') {

    $colmn = 'Calender_Id';
    $cal_id = paramlib_validation($colmn, $_REQUEST['calendar_id']);
    $acs_RET = DBGet(DBQuery('SELECT TITLE, DEFAULT_CALENDAR FROM school_calendars WHERE CALENDAR_ID=\'' . $cal_id . '\''));
    $acs_RET = $acs_RET[1];
    $ac_RET = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) AS START_DATE,MAX(SCHOOL_DATE) AS END_DATE FROM attendance_calendar WHERE CALENDAR_ID=\'' . $cal_id . '\''));
    $ac_RET = $ac_RET[1];

    $day_RET = DBGet(DBQuery('SELECT days FROM school_calendars WHERE CALENDAR_ID=\'' . $cal_id . '\''));
    $day_RET1 = str_split($day_RET[1]['DAYS']);
    $i = 0;
    foreach ($day_RET1 as $day) {

        $weekdays[$i] = $day;
        $i++;
    }
    //$message = '<div class="row">';
    //$message .= '<div class="col-md-12">';

    $message = '<div class="row">';

    $message .= '<div class="col-md-8">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-2 control-label text-right">' . _title . '</label>';
    $message .= '<div class="col-md-10">';
    $message .= '<INPUT type=text name=title class=form-control id=title value="' . $acs_RET['TITLE'] . '">';
    $message .= '<div class="checkbox checkbox-switch switch-success switch-sm"><label><INPUT type=checkbox name=default value=Y ' . (($acs_RET['DEFAULT_CALENDAR'] == 'Y') ? 'checked' : '') . '><span></span>' . _defaultCalendarForThisSchool . '</label></div>';
    $message .= '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-8
    $message .= '</div>'; //.row    

    $message .= '<div class="row">';
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _from . '</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($ac_RET['START_DATE'], '_min', 1) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _to . '</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($ac_RET['END_DATE'], '_max', 2) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '</div>'; //.row 

    $message .= '<div class="row">';
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">' . _weekdays . '</label>';
    $message .= '<div class="col-md-8"><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[0] ' . ((in_array('U', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> ' . _sunday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[1] ' . ((in_array('M', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED>' . _monday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[2] ' . ((in_array('T', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED>' . _tuesday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[3] ' . ((in_array('W', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED>' . _wednesday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[4] ' . ((in_array('H', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED>' . _thursday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[5] ' . ((in_array('F', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED>' . _friday . '</label></div><div class="checkbox"><label><INPUT class="styled" type=checkbox value=Y name=weekdays[6] ' . ((in_array('S', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> ' . _saturday . '</label></div></div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= calendarEventsVisibility();
    $message .= '</div>'; //.col-md-4
    $message .= '</div>'; //.row
    //$message .= '</div>'; //.col-md-12
    //$message .= '</div>'; //.row

    if (Prompt_Calender(_editThisCalendar, '', $message)) {
        $col = _editThisCalendar;
        $cal_title = singleQuoteReplace("'", "''", $_REQUEST['title']);

        $dup_cal_title = DBGet(DBQuery('SELECT count(*) AS NO FROM school_calendars WHERE TITLE=\'' . $cal_title . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND CALENDAR_ID NOT IN(' . $cal_id . ')'));

        if (isset($_REQUEST['default']) && $dup_cal_title[1]['NO'] == 0)
            DBQuery('UPDATE school_calendars SET DEFAULT_CALENDAR = NULL WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'');
        if ($dup_cal_title[1]['NO'] == 0) {
            DBQuery('UPDATE school_calendars SET TITLE = \'' . $cal_title . '\', DEFAULT_CALENDAR = \'' . $_REQUEST['default'] . '\' WHERE CALENDAR_ID=\'' . $cal_id . '\'');
            DBQuery('DELETE FROM calendar_events_visibility WHERE calendar_id=\'' . $cal_id . '\'');
        }
        $end_date_cal = $_REQUEST['year__max'] . '-' . $_REQUEST['month__max'] . '-' . $_REQUEST['day__max'];
        $start_date_cal = $_REQUEST['year__min'] . '-' . $_REQUEST['month__min'] . '-' . $_REQUEST['day__min'];

        $min_date = DBGet(DBquery('SELECT MIN(SCHOOL_DATE) as SCHOOL_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $cal_id));
        $max_date = DBGet(DBquery('SELECT MAX(SCHOOL_DATE) as SCHOOL_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $cal_id));
        $cal_days = DBGet(DBquery('SELECT DAYS FROM school_calendars WHERE CALENDAR_ID=' . $cal_id));
        $days_conv = array('Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 'Thu' => 'H', 'Fri' => 'F', 'Sat' => 'S', 'Sun' => 'U');
        if (strtotime($start_date_cal) < strtotime($min_date[1]['SCHOOL_DATE']) && $start_date_cal != '' && $min_date[1]['SCHOOL_DATE'] != '') {
            $date1_ts = strtotime($start_date_cal);
            $date2_ts = strtotime($min_date[1]['SCHOOL_DATE']);
            $diff = $date2_ts - $date1_ts;
            for ($d = 0; $d < round($diff / 86400); $d++) {
                $mk_date = strtotime($start_date_cal) + (86400 * $d);
                if (strpos($cal_days[1]['DAYS'], $days_conv[date('D', $mk_date)]) !== false) {
                    $ins_date = date('Y-m-d', $mk_date);
                    DBQuery('INSERT INTO attendance_calendar (SYEAR,SCHOOL_ID,SCHOOL_DATE,MINUTES,CALENDAR_ID) VALUES (\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $ins_date . '\',999,\'' . $cal_id . '\')');
                }
            }
        }
        if (strtotime($end_date_cal) > strtotime($max_date[1]['SCHOOL_DATE'])) {
            $date2_ts = strtotime($end_date_cal);
            $date1_ts = strtotime($max_date[1]['SCHOOL_DATE']);
            $diff = $date2_ts - $date1_ts;
            for ($d = 1; $d <= round($diff / 86400); $d++) {
                $mk_date = strtotime($max_date[1]['SCHOOL_DATE']) + (86400 * $d);
                if (strpos($cal_days[1]['DAYS'], $days_conv[date('D', $mk_date)]) !== false) {
                    $ins_date = date('Y-m-d', $mk_date);
                    DBQuery('INSERT INTO attendance_calendar (SYEAR,SCHOOL_ID,SCHOOL_DATE,MINUTES,CALENDAR_ID) VALUES (\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $ins_date . '\',999,\'' . $cal_id . '\')');
                }
            }
        }
        if (count($_REQUEST['profiles'])) {
            $profile_sql = 'INSERT INTO calendar_events_visibility(calendar_id,profile_id,profile) VALUES';
            foreach ($_REQUEST['profiles'] as $key => $profile) {
                if (is_numeric($key)) {
                    $profile_sql .= '(\'' . $cal_id . '\',\'' . $key . '\',NULL),';
                } else {
                    $profile_sql .= '(\'' . $cal_id . '\',NULL,\'' . $key . '\'),';
                }
            }
            $profile_sql = substr($profile_sql, 0, -1);
            if ($dup_cal_title[1]['NO'] == 0) {
                DBQuery($profile_sql);
            } else {
                echo '<p style=color:red>' . _calenderTitleAlreadyExists . '.</p>';
            }
        }

        $_REQUEST['calendar_id'] = $cal_id;
        unset($_REQUEST['modfunc']);
        unset($_SESSION['_REQUEST_vars']['modfunc']);
        unset($_SESSION['_REQUEST_vars']['weekdays']);
        unset($_SESSION['_REQUEST_vars']['title']);
    }
}

if (User('PROFILE') != 'admin') {
    if (!$_REQUEST['calendar_id']) {

        $course_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));

        if ($course_RET[1]['CALENDAR_ID'])
            $_REQUEST['calendar_id'] = $course_RET[1]['CALENDAR_ID'];
        else {
            $default_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND DEFAULT_CALENDAR=\'Y\''));

            if (!empty($default_RET))
                $_REQUEST['calendar_id'] = $default_RET[1]['CALENDAR_ID'];
            else {
                $qr = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'ORDER BY CALENDAR_ID LIMIT 0,1'));
                $_REQUEST['calendar_id'] = $qr[1]['CALENDAR_ID'];
            }
        }
    }
} elseif (!$_REQUEST['calendar_id']) {
    $default_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND DEFAULT_CALENDAR=\'Y\''));
    if (count($default_RET))
        $_REQUEST['calendar_id'] = $default_RET[1]['CALENDAR_ID'];
    else {
        $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if (count($calendars_RET))
            $_REQUEST['calendar_id'] = $calendars_RET[1]['CALENDAR_ID'];
        else
            $error = array('There are no calendars yet setup.');
    }
}
unset($_SESSION['_REQUEST_vars']['calendar_id']);

if ($_REQUEST['modfunc'] == 'detail') {
    if ($_REQUEST['month_values'] && $_REQUEST['day_values'] && $_REQUEST['year_values']) {
        $_REQUEST['values']['SCHOOL_DATE'] = $_REQUEST['day_values']['SCHOOL_DATE'] . '-' . $_REQUEST['month_values']['SCHOOL_DATE'] . '-' . $_REQUEST['year_values']['SCHOOL_DATE'];
        if (!VerifyDate($_REQUEST['values']['SCHOOL_DATE']))
            unset($_REQUEST['values']['SCHOOL_DATE']);
    }

    if ($_POST['button'] == _save && AllowEdit()) {
        if (!(isset($_REQUEST['values']['TITLE']) && trim($_REQUEST['values']['TITLE']) == '')) {
            $go = false;
            if ($_REQUEST['event_id'] != 'new') {
                $sql = 'UPDATE calendar_events SET ';

                foreach ($_REQUEST['values'] as $column => $value) {
                    $value = paramlib_validation($column, $value);
                    if ($column == "SCHOOL_DATE") {
                        $value = date('Y-m-d', strtotime($value));
                    }
                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                        $value = mysqli_real_escape_string($value, db_start());
                        $value = str_replace('%u201D', "\"", $value);
                    }
                    $sql .= $column . '=\'' . singleQuoteReplace("'", "''", trim($value)) . '\',';
                    $go = true;
                }
                $sql = substr($sql, 0, -1);
                if (!$_REQUEST['values']) {
                    if ($_REQUEST['new'] == 'Y') {
                        $sql .= ' CALENDAR_ID=\'0\'';
                        $go = true;
                    }
                    if (isset($_REQUEST['new']) && $_REQUEST['new'] != 'Y') {
                        $sql .= ' CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'';
                        $go = true;
                    }
                } else {
                    if ($_REQUEST['new'] == 'Y') {
                        $sql .= ',CALENDAR_ID=\'0\'';
                    }
                    if (isset($_REQUEST['new']) && $_REQUEST['new'] != 'Y') {
                        $sql .= ' ,CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'';
                    }
                }
                $sql .= ' WHERE ID=\'' . $_REQUEST['event_id'] . '\'';

                if ($go)
                    DBQuery($sql);
            } else {
                if (!$_REQUEST['values']['SCHOOL_DATE'])
                    $_REQUEST['values']['SCHOOL_DATE'] = $_REQUEST['dd'];

                $sql = 'INSERT INTO calendar_events ';
                if ($_REQUEST['new'] == 'Y')
                    $cal_id = '0';
                else
                    $cal_id = $_REQUEST['calendar_id'];
                $fields = 'SYEAR,SCHOOL_ID,CALENDAR_ID,';
                $values = '\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $cal_id . '\',';

                foreach ($_REQUEST['values'] as $column => $value) {
                    if (trim($value)) {
                        $value = paramlib_validation($column, $value);
                        $fields .= $column . ',';
                        if ($column == "SCHOOL_DATE")
                            $values .= '\'' . date('Y-m-d', strtotime($value)) . '\',';
                        else {
                            if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                $value = mysqli_real_escape_string($value, db_start());
                            }
                            $values .= '\'' . singleQuoteReplace("'", "''", trim($value)) . '\',';
                        }
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                if ($go) {

                    DBQuery($sql);
                }
            }

            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&calendar_id=' . $_REQUEST['calendar_id'] . '&year=' . $_REQUEST['year'] . '&month=' . MonthNWSwitch($_REQUEST['month'], 'tochar') . '"; window.close();</script>';

            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
        }

        echo '<SCRIPT language=javascript> window.close();</script>';
    } elseif (clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == _delete) {
        if (DeletePromptCommon(_event, 'delete', 'y')) {

            DBQuery("DELETE FROM calendar_events WHERE ID='" . paramlib_validation($column = 'EVENT_ID', $_REQUEST['event_id']) . "'");
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&calendar_id=' . $_REQUEST['calendar_id'] . '&year=' . $_REQUEST['year'] . '&month=' . MonthNWSwitch($_REQUEST['month'], 'tochar') . '"; window.close();</script>';

            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
        }
    } else {
        if ($_REQUEST['event_id']) {
            if ($_REQUEST['event_id'] != 'new') {
                $RET = DBGet(DBQuery("SELECT TITLE,DESCRIPTION,SCHOOL_DATE,CALENDAR_ID FROM calendar_events WHERE ID='$_REQUEST[event_id]'"));
                $title = $RET[1]['TITLE'];
                $calendar_id = $RET[1]['CALENDAR_ID'];
            } else {
                $title = _newEvent;
                $RET[1]['SCHOOL_DATE'] = date('Y-m-d', strtotime($_REQUEST['school_date']));
                $RET[1]['CALENDAR_ID'] = '';
                $calendar_id = $_REQUEST['calendar_id'];
            }
            echo "<FORM name=popform class=\"form-horizontal\" id=popform action=ForWindow.php?modname=$_REQUEST[modname]&dd=$_REQUEST[school_date]&modfunc=detail&event_id=$_REQUEST[event_id]&calendar_id=$calendar_id&month=$_REQUEST[month]&year=$_REQUEST[year] METHOD=POST>";
        } else {
            $RET = DBGet(DBQuery('SELECT TITLE,STAFF_ID,DATE_FORMAT(DUE_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,ASSIGNED_DATE,DUE_DATE,DESCRIPTION FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\''));
            $title = $RET[1]['TITLE'];
            $RET[1]['STAFF_ID'] = GetTeacher($RET[1]['STAFF_ID']);
        }

        PopTable('header', $title);
        echo '<div id=err_message ></div><br/>';

        echo '<div class="form-group"><label class="control-label text-right col-md-4">' . _date . '</label><div class="col-md-8">' . date("Y/M/d", strtotime($RET[1]['SCHOOL_DATE'])) . '</div></div>';

        if ($RET[1]['TITLE'] == '') {
            echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextInput($RET[1]['TITLE'], 'values[TITLE]', 'Title', 'id=title') : $RET[1]['TITLE']) . '</div>';
        } else {
            echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextInputCusId($RET[1]['TITLE'], 'values[TITLE]', 'Title', '', true, 'title') : $RET[1]['TITLE']) . '</div>';
        }

        if ($RET[1]['STAFF_ID']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">' . _teacher . '</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['STAFF_ID'], 'values[STAFF_ID]') : $RET[1]['STAFF_ID']) . '</div></div>';
        }

        if ($RET[1]['ASSIGNED_DATE']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">' . _assignedDate . '</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['ASSIGNED_DATE'], 'values[ASSIGNED_DATE]') : $RET[1]['ASSIGNED_DATE']) . '</div></div>';
        }

        if ($RET[1]['DUE_DATE']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">' . _dueDate . '</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['DUE_DATE'], 'values[DUE_DATE]') : $RET[1]['DUE_DATE']) . '</div></div>';
        }
        echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextAreaInput(html_entity_decode($RET[1]['DESCRIPTION']), 'values[DESCRIPTION]', 'Notes', 'style=height:200px;') : html_entity_decode($RET[1]['DESCRIPTION'])) . '</div>';

        if (AllowEdit()) {
            if (User('PROFILE') == 'admin')
                echo '<div class="form-group"><div class="col-xs-12">' . CheckboxInputSwitch($RET[1]['CALENDAR_ID'], $_REQUEST['event_id'], _showEventsSystemWide) . '</div></div>';
            else
                echo '<div class="form-group"><div class="col-xs-12">' . ($RET[1]['CALENDAR_ID'] == '' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . '</div></div>';

            if (User('PROFILE') == 'admin')
                echo '<INPUT type=submit class="btn btn-primary" name=button value=' . _save . ' onclick="return formcheck_calendar_event();">';
            echo '&nbsp;';
            if ($_REQUEST['event_id'] != 'new' && User('PROFILE') == 'admin')
                echo '<INPUT type=submit name=button class="btn btn-white" value=' . _delete . ' onclick="formload_ajax(\'popform\');">';
        } else {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">' . _showEventsSystemWide . '</label><div class="col-md-8">' . ($RET[1]['CALENDAR_ID'] == '' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . '</div></div>';
        }

        PopTable('footer');
        echo '</FORM>';

        unset($_REQUEST['values']);
        unset($_SESSION['_REQUEST_vars']['values']);
        unset($_REQUEST['button']);
        unset($_SESSION['_REQUEST_vars']['button']);
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'list_events') {


    if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start']) {
        while (!VerifyDate($start_date = $_REQUEST['day_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['year_start']))
            $_REQUEST['day_start']--;
    } else {
        $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if ($min_date[1]['MIN_DATE'])
            $start_date = $min_date[1]['MIN_DATE'];
        else
            $start_date = '01-' . strtoupper(date('M-y'));
    }

    if (strpos($start_date, 'JAN') !== false or strpos($start_date, 'FEB') !== false or strpos($start_date, 'MAR') !== false or strpos($start_date, 'APR') !== false or strpos($start_date, 'MAY') !== false or strpos($start_date, 'JUN') !== false or strpos($start_date, 'JUL') !== false or strpos($start_date, 'AUG') !== false or strpos($start_date, 'SEP') !== false or strpos($start_date, 'OCT') !== false or strpos($start_date, 'NOV') !== false or strpos($start_date, 'DEC') !== false) { {
            $sdateArr = explode("-", $start_date);
            $month = $sdateArr[1];
            if ($month == 'JAN')
                $month = '01';
            if ($month == 'FEB')
                $month = '02';
            if ($month == 'MAR')
                $month = '03';
            if ($month == 'APR')
                $month = '04';
            if ($month == 'MAY')
                $month = '05';
            if ($month == 'JUN')
                $month = '06';
            if ($month == 'JUL')
                $month = '07';
            if ($month == 'AUG')
                $month = '08';
            if ($month == 'SEP')
                $month = '09';
            if ($month == 'OCT')
                $month = '10';
            if ($month == 'NOV')
                $month = '11';
            if ($month == 'JAN')
                $month = '12';
            $start_date = $sdateArr[2] . '-' . $month . '-' . $sdateArr[0];
        }
    }
    if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end']) {
        while (!VerifyDate($end_date = $_REQUEST['day_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['year_end']))
            $_REQUEST['day_end']--;
    } else {
        $max_date = DBGet(DBQuery('SELECT max(SCHOOL_DATE) AS MAX_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if ($max_date[1]['MAX_DATE'])
            $end_date = $max_date[1]['MAX_DATE'];
        else
            $end_date = strtoupper(date('Y-m-d'));
    }

    if (strpos($end_date, 'JAN') !== false or strpos($end_date, 'FEB') !== false or strpos($end_date, 'MAR') !== false or strpos($end_date, 'APR') !== false or strpos($end_date, 'MAY') !== false or strpos($end_date, 'JUN') !== false or strpos($end_date, 'JUL') !== false or strpos($end_date, 'AUG') !== false or strpos($end_date, 'SEP') !== false or strpos($end_date, 'OCT') !== false or strpos($end_date, 'NOV') !== false or strpos($end_date, 'DEC') !== false) { {
            $edateArr = explode("-", $end_date);
            $month = $edateArr[1];
            if ($month == 'JAN')
                $month = '01';
            if ($month == 'FEB')
                $month = '02';
            if ($month == 'MAR')
                $month = '03';
            if ($month == 'APR')
                $month = '04';
            if ($month == 'MAY')
                $month = '05';
            if ($month == 'JUN')
                $month = '06';
            if ($month == 'JUL')
                $month = '07';
            if ($month == 'AUG')
                $month = '08';
            if ($month == 'SEP')
                $month = '09';
            if ($month == 'OCT')
                $month = '10';
            if ($month == 'NOV')
                $month = '11';
            if ($month == 'DEC')
                $month = '12';
            $end_date = $edateArr[2] . '-' . $month . '-' . $edateArr[0];
        }
    }

    if (User('PROFILE') != 'student')
        DrawBC("" . _schoolSetup . " > " . ProgramTitle());
    else
        DrawBC("School Info > " . ProgramTitle());
    echo '<FORM action=Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=' . $_REQUEST['modfunc'] . '&month=' . $_REQUEST['month'] . '&year=' . $_REQUEST['year'] . ' METHOD=POST>';

    if ($end_date <= $start_date) {
        $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if ($min_date[1]['MIN_DATE'])
            $start_date = $min_date[1]['MIN_DATE'];
        else
            $start_date = '01-' . strtoupper(date('M-y'));

        $max_date = DBGet(DBQuery('SELECT max(SCHOOL_DATE) AS MAX_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if ($max_date[1]['MAX_DATE'])
            $end_date = $max_date[1]['MAX_DATE'];
        else
            $end_date = strtoupper(date('Y-m-d'));


        echo '<div class="alert alert-danger alert-styled-left alert-bordered"><span class="text-bold">' . _alert . '!!</span> - ' . _alert . '.</span></div>';
        echo '<font style="color:red"><b></b></font>';
    }
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading">';
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<h6 class="panel-title"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&month=' . $_REQUEST['month'] . '&year=' . $_REQUEST['year'] . ' class="text-primary"><i class="icon-square-left"></i>' . _backToCalendar . '</A></h6>';
    echo '</div>'; //.col-md-6
    echo '<div class="col-md-8 text-md-right text-lg-right">';
    echo '<div class="form-inline inline-block"><div class="inline-block">' . PrepareDateSchedule($start_date, 'start') . '</div><label>&nbsp; &nbsp; - &nbsp; &nbsp;</label><div class="inline-block">' . PrepareDateSchedule($end_date, 'end') . '</div> &nbsp; &nbsp;<INPUT type=submit class="btn btn-primary" value=' . _go . '></div>';
    echo '</div>'; //.col-md-6
    echo '</div>';

    echo '</div>'; //.panel-body
    echo '</div>'; //.panel.panel-default



    $functions = array('SCHOOL_DATE' => 'ProperDate');         // <A HREF=Modules.php?modname='.$_REQUEST["modname"].'&month='.$_REQUEST["month"].'&year='.$_REQUEST["year"].'>
    $events_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND SYEAR=\'' . UserSyear() . '\'  AND (calendar_id=\'' . $_REQUEST['calendar_id'] . '\' OR calendar_id=\'0\') ORDER BY SCHOOL_DATE'), $functions);
    $_SESSION['events_RET'] = $events_RET;


    echo '<div id="students" class="panel panel-default">';
    ListOutput($events_RET, array('SCHOOL_DATE' => '' . _date . '', 'TITLE' => '' . _event . '', 'DESCRIPTION' => '' . _description . ''), _event, _events);
    echo '</div></FORM>';
}

if (!$_REQUEST['modfunc']) {

    if (User('PROFILE') != 'student')
        DrawBC("" . _schoolSetup . " > " . ProgramTitle());
    else
        DrawBC("" . _schoolInfo . " > " . ProgramTitle());
    $last = 31;
    while (!checkdate($_REQUEST['month'], $last, $_REQUEST['year']))
        $last--;

    $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\''), array(), array('SCHOOL_DATE'));
    if ($_REQUEST['minutes']) {
        foreach ($_REQUEST['minutes'] as $date => $minutes) {
            if ($calendar_RET[$date]) {
                if ($minutes != '0' && $minutes != '')
                    DBQuery('UPDATE attendance_calendar SET MINUTES=\'' . $minutes . '\' WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'');
                else {
                    DBQuery('DELETE FROM attendance_calendar WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'');
                }
            } elseif ($minutes != '0' && $minutes != '') {
                DBQuery('INSERT INTO attendance_calendar (SYEAR,SCHOOL_ID,SCHOOL_DATE,CALENDAR_ID,MINUTES) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $date . '\',\'' . $_REQUEST['calendar_id'] . '\',\'' . $minutes . '\')');
            }
        }
        $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\''), array(), array('SCHOOL_DATE'));
        unset($_REQUEST['minutes']);
        unset($_SESSION['_REQUEST_vars']['minutes']);
    }

    if ($_REQUEST['all_day']) {
        foreach ($_REQUEST['all_day'] as $date => $yes) {
            if ($yes == 'Y') {
                if ($calendar_RET[$date])
                    DBQuery('UPDATE attendance_calendar SET MINUTES=\'999\' WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'');
                else {
                    DBQuery('INSERT INTO attendance_calendar (SYEAR,SCHOOL_ID,SCHOOL_DATE,CALENDAR_ID,MINUTES) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $date . '\',\'' . $_REQUEST['calendar_id'] . '\',\'999\')');
                }
            } else {
                $per_id = DBGet(DBQuery('SELECT PERIOD_ID FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
                foreach ($per_id as $key => $value) {
                    $period .= $value['PERIOD_ID'] . ',';
                }
                $period = substr($period, 0, -1);

                if ($period != '')
                    $get_date = DBGet(DBQuery('SELECT COUNT(ap.SCHOOL_DATE) AS SCHOOL_DATE FROM attendance_period ap,course_periods cp,school_calendars ac WHERE ap.SCHOOL_DATE=\'' . $date . '\' AND ap.PERIOD_ID IN(' . $period . ') AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.CALENDAR_ID=ac.CALENDAR_ID AND ac.SCHOOL_ID=cp.SCHOOL_ID AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND ac.CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\' '));
                else
                    $get_date = DBGet(DBQuery('SELECT COUNT(ap.SCHOOL_DATE) AS SCHOOL_DATE FROM attendance_period ap,course_periods cp,school_calendars ac WHERE ap.SCHOOL_DATE=\'' . $date . '\'  AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.CALENDAR_ID=ac.CALENDAR_ID  AND ac.SCHOOL_ID=cp.SCHOOL_ID AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND ac.CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'  '));
                if ($_REQUEST['show_all'][$date] == 'Y') {
                    if ($get_date[1]['SCHOOL_DATE'] == 0)
                        DBQuery('DELETE FROM attendance_calendar WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\'');
                    else
                        echo '<font color=red><b>selected Day HasAssociation</b></font>';
                } else {
                    if ($get_date[1]['SCHOOL_DATE'] == 0)
                        DBQuery('DELETE FROM attendance_calendar WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'');
                    else
                        echo '<font color=red><b>selected Day Has Association</b></font>';
                }
            }
        }
        $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\''), array(), array('SCHOOL_DATE'));
        unset($_REQUEST['all_day']);
        unset($_SESSION['_REQUEST_vars']['all_day']);
    }
    if ($_REQUEST['blocks']) {
        foreach ($_REQUEST['blocks'] as $date => $block) {
            if ($calendar_RET[$date]) {
                DBQuery('UPDATE attendance_calendar SET BLOCK=\'' . $block . '\' WHERE SCHOOL_DATE=\'' . $date . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\'');
            }
        }
        $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND CALENDAR_ID=\'' . $_REQUEST['calendar_id'] . '\''), array(), array('SCHOOL_DATE'));
        unset($_REQUEST['blocks']);
        unset($_SESSION['_REQUEST_vars']['blocks']);
    }

    echo "<FORM action=Modules.php?modname=$_REQUEST[modname] METHOD=POST>";
    $link = '';
    $title_RET = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY DEFAULT_CALENDAR ASC'));
    foreach ($title_RET as $title) {
        $options[$title['CALENDAR_ID']] = $title['TITLE'];
    }
    //echo date('M Y',strtotime('first day of +1 month'));
    if (AllowEdit()) {

        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['calendar_id']);

        if ($_REQUEST['calendar_id']) {
            $link .= '<div class="row">';
            $link .= '<div class="col-md-3">' . SelectInput($_REQUEST['calendar_id'], 'calendar_id', '', $options, false, " onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . '&amp;calendar_id="+this.form.calendar_id.value;\' ', false) . '</div>';
            $link .= '<div class="col-md-6"><h3 class="text-center m-0"><a href="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=' . $_REQUEST['modfunc'] . '&month=' . date('m', strtotime('first day of -1 month', $time)) . '&year=' . date('Y', strtotime('first day of -1 month', $time)) . '&calendar_id=' . $_REQUEST['calendar_id'] . '" class="btn btn-icon"><i class="fa fa-chevron-left fa-lg"></i></a> <span class="inline-block p-l-20 p-r-20">' . date("F Y", $time) . '</span> <a href="Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=' . $_REQUEST['modfunc'] . '&month=' . date('m', strtotime('first day of +1 month', $time)) . '&year=' . date('Y', strtotime('first day of +1 month', $time)) . '&calendar_id=' . $_REQUEST['calendar_id'] . '" class="btn btn-icon"><i class="fa fa-chevron-right fa-lg"></i></a></h3></div>';
            if (User('PROFILE') == 'admin') {
                $link .= '<div class="col-md-3">';
                $link .= "<div class=\"btn-group pull-right\"><a href='#' onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&modfunc=create\");' class=\"btn btn-primary btn-icon btn-lg\" data-popup=\"tooltip\" data-placement=\"top\" data-original-title=\"" . _createANewCalendar . "\"><i class=\"fa fa-plus\"></i></a>";
                $link .= "<a href='#' onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete_calendar&calendar_id=$_REQUEST[calendar_id]\");' class=\"btn btn-primary btn-lg btn-icon\" data-popup=\"tooltip\" data-placement=\"top\" data-original-title=\"" . _deleteThisCalendar . "\"><i class=\"fa fa-times\"></i></a><a href='#' onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]&modfunc=edit_calendar&calendar_id=$_REQUEST[calendar_id]\");' class=\"btn btn-primary btn-lg btn-icon\" data-popup=\"tooltip\" data-placement=\"top\" data-original-title=\"" . _editThisCalendar . "\"><i class=\"fa fa-pencil\"></i></a></div>";
                $link .= '</div>';
            }
            $link .= '</div>'; //.row
        }
    } else {

        if (User('PROFILE_ID') != '') {
            $get_perm = DBGet(DBQuery('SELECT COUNT(1) as ACC FROM profile_exceptions WHERE modname=\'schoolsetup/Calendar.php \' AND CAN_USE=\'Y\' AND PROFILE_ID=\'' . User('PROFILE_ID') . '\' '));
            if ($get_perm[1]['ACC'] > 0) {
                $title_RET_mod = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY DEFAULT_CALENDAR ASC'));
                foreach ($title_RET_mod as $title) {
                    $options_mod[$title['CALENDAR_ID']] = $title['TITLE'];
                }

                $tmp_REQUEST = $_REQUEST;

                unset($tmp_REQUEST['calendar_id']);
                $link .= '<table><tr>';

                if ($_REQUEST['calendar_id'])
                    $link .= '<td>' . SelectInputForCal($_REQUEST['calendar_id'], 'calendar_id', '', $options_mod, false, " onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . '&amp;calendar_id="+this.form.calendar_id.value;\' ', false) . '</td>';
                $link .= '</tr></table>';
            }
        }
    }

    if ($_REQUEST['calendar_id']) {
        echo '<div class="panel panel-default">';
        echo '<div class="panel-body">';
        echo '<div class="form-inline">';
        DrawHeaderHome('<div class="inline-block">' . PrepareDate(strtoupper(date("d-M-y", $time)), '', false, array('M' => date("m", $time), 'Y' => date("y", $time), 'submit' => true, 'view' => 'month')) . '</div>' . ' <A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=list_events&calendar_id=' . $_REQUEST['calendar_id'] . '&month=' . $_REQUEST['month'] . '&year=' . $_REQUEST['year'] . ' class="btn btn-default m-l-5">' . _listEvents . '</A>', (User('PROFILE') == 'admin' ? SubmitButton(_save, '', 'class="btn btn-primary pull-right"  onclick="self_disable(this);"') : ''));
        echo '</div>'; //.form-inline
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
    }



    if (is_countable($error) && count($error)) {
        if ($isajax != "ajax")
            echo ErrorMessage($error, 'fatal');
        else
            echo ErrorMessage1($error, 'fatal');
    } else {


        echo "<div class=\"panel panel-default\">";
        echo '<div class="panel-heading">';
        DrawHeaderHome($link);
        echo '</div>'; //.panel-heading
        $events_RET = DBGet(DBQuery('SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,ce.TITLE FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND ce.calendar_id=\'' . $_REQUEST['calendar_id'] . '\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID=' . User('PROFILE_ID') . ' UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,TITLE FROM calendar_events WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\' AND SYEAR=\'' . UserSyear() . '\' AND CALENDAR_ID=0'), array(), array('SCHOOL_DATE'));

        if (User('PROFILE') == 'parent' || User('PROFILE') == 'student')
            $assignments_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,a.TITLE,\'Y\' AS ASSIGNED FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\'' . UserStudentID() . '\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE<=a.DUE_DATE) '), array(), array('SCHOOL_DATE'));
        elseif (User('PROFILE') == 'teacher')
            $assignments_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,a.TITLE,CASE WHEN a.ASSIGNED_DATE<=CURRENT_DATE OR a.ASSIGNED_DATE IS NULL THEN \'Y\' ELSE NULL END AS ASSIGNED FROM gradebook_assignments a WHERE a.STAFF_ID=\'' . User('STAFF_ID') . '\' AND a.DUE_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'])) . '\''), array(), array('SCHOOL_DATE'));

        $skip = date("w", $time);
        echo '<div class="table-responsive">';
        echo "<TABLE class=\"table table-bordered table-calendar\" border=0 cellpadding=3 cellspacing=1><thead><TR class=calendar_header align=center>";
        echo "<th>" . _sunday . "</th><th>" . _monday . "</th><th>" . _tuesday . "</th><th>" . _wednesday . "</th><th>" . _thursday . "</th><th>" . _friday . "</th><th width=99>" . _saturday . "</th>";
        echo "</TR></thead><TR>";

        if ($skip) {
            echo "<td colspan=" . $skip . "></td>";
            $return_counter = $skip;
        }
        $blocks_RET = DBGet(DBQuery('SELECT DISTINCT BLOCK FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND BLOCK IS NOT NULL ORDER BY BLOCK'));
        for ($i = 1; $i <= $last; $i++) {
            $day_time = mktime(0, 0, 0, $_REQUEST['month'], $i, $_REQUEST['year']);
            $date = date('d-M-y', $day_time);
            echo "<TD width=100 class=" . ($calendar_RET[$date][1]['MINUTES'] ? $calendar_RET[$date][1]['MINUTES'] == '999' ? 'calendar-active' : 'calendar-extra' : 'calendar-holiday') . " valign=top><table width='100%'><tr><td width=5 valign=top>$i</td>";
            if (AllowEdit()) {
                if (User('PROFILE') == 'admin') {
                    if ($calendar_RET[$date][1]['MINUTES'] == '999') {
                        echo '<TD class="text-right">' . CheckboxInput_Calendar($calendar_RET[$date], "all_day[$date]", '', '', false, '<i class="icon-checkbox-checked"></i> ', '', true, 'id=all_day_' . $i . ' onclick="return system_wide(' . $i . ');"') . '</TD>';
                        echo '</TR>';
                    } else {
                        echo "<TD class=\"text-right\"><INPUT type=checkbox name=all_day[$date] value=Y id=all_day_$i onclick='return system_wide($i);'></TD>";
                        echo '</TR>';
                        echo '<tr><td colspan=2>';
                        //echo "<div id=syswide_holi_$i style=display:none><span>System Wide </span><INPUT type=checkbox name=show_all[$date] value=Y></div>";
                        echo TextInput($calendar_RET[$date][1]['MINUTES'], "minutes[$date]", '', 'size=3 class=cell_small onkeydown="return numberOnly(event);"');
                        echo '</TD></TR>';
                    }
                }
            }
            if (count($blocks_RET) > 0) {
                unset($options);
                foreach ($blocks_RET as $block)
                    $options[$block['BLOCK']] = $block['BLOCK'];

                echo SelectInput($calendar_RET[$date][1]['BLOCK'], "blocks[$date]", '', $options);
            }
            echo "</td></tr>";

            if (AllowEdit()) {
                if (User('PROFILE') == 'admin') {
                    if ($calendar_RET[$date][1]['MINUTES'] == '999') {
                        echo '<tr><td colspan="2"><TABLE cellpadding=0 cellspacing=0 >';
                        echo '<TR><TD><div id=syswide_holi_' . $i . ' style="display:none; padding-top: 10px;"><label class="checkbox-inline"><INPUT type=checkbox name=show_all[' . $date . '] value=Y> ' . _systemWide . '</label></div></TD></TR>';
                        echo '</TABLE></td></tr>';
                    } else {
                        echo "<tr><td colspan='2'><TABLE cellpadding=0 cellspacing=0 >";
                        echo "<div id=syswide_holi_$i style='display:none; padding-top: 10px;'><label class='checkbox-inline'><INPUT type=checkbox name=show_all[$date] value=Y>" . _systemWide . "</label></div>";
                        echo "</TABLE></td></tr>";
                    }
                }
            }

            echo "<tr><TD colspan=2 height=50 valign=top>";

            if (is_countable($events_RET[$date]) && count($events_RET[$date])) {
                echo '<TABLE cellpadding=2 cellspacing=2 border=0>';
                foreach ($events_RET[$date] as $event) {

                    if (strlen($event['TITLE']) < 8)
                        $e_title = $event['TITLE'];
                    else
                        $e_title = substr($event['TITLE'], 0, 8) . '....';

                    echo '<TR><TD>' . button("dot", "0000FF", "", "6") . '</TD><TD> <A class=\"event\" HREF=# onclick="CalendarModal(\'' . $event['ID'] . '\',' . $_REQUEST['calendar_id'] . ',\'' . $date . '\',\'' . $_REQUEST['year'] . '\',\'' . MonthNWSwitch($_REQUEST['month']) . '\',\'tochar\')"; return false;>' . ($event['TITLE'] ? $e_title : '***') . '</b></A></TD></TR>';
                }
                if (is_countable($assignments_RET[$date]) && count($assignments_RET[$date])) {
                    foreach ($assignments_RET[$date] as $event)
                        echo "<TR><TD>" . button('dot', $event['ASSIGNED'] == 'Y' ? '00FF00' : 'FF0000', '', 6) . "</TD><TD><A HREF=# data-toggle=modal  onclick='CalendarModalAssignment($event[ID])'>" . $event['TITLE'] . "</A></TD></TR>";
                }
                echo '</TABLE>';
            } elseif (is_countable($assignments_RET[$date]) && count($assignments_RET[$date])) {
                echo '<TABLE cellpadding=0 cellspacing=0 border=0>';
                foreach ($assignments_RET[$date] as $event) {
                    echo "<TR><TD>" . button('dot', $event['ASSIGNED'] == 'Y' ? '00FF00' : 'FF0000', '', 6) . "</TD><TD><A HREF=# data-toggle=modal  onclick='CalendarModalAssignment($event[ID])'>" . $event['TITLE'] . "</A></TD></TR>";
                }
                echo '</TABLE>';
            }

            echo "</td></tr>";
            if (AllowEdit()) {
                if (User('PROFILE') == 'admin') {
                    echo '<tr><td valign=bottom align=left><button type="button" class="btn btn-primary btn-icon btn-xs" data-toggle="modal"  data-event-id=new onclick="CalendarModal(\'new\',' . $_REQUEST['calendar_id'] . ',\'' . $date . '\',\'' . $_REQUEST['year'] . '\',\'' . MonthNWSwitch($_REQUEST['month']) . '\',\'tochar\');"><i class=icon-plus3 ></i></button></td></tr>';
                }
            }
            echo "</table></TD>";
            $return_counter++;

            if ($return_counter % 7 == 0)
                echo "</TR><TR>";
        }
        echo "</TR></TABLE>";
        echo "</div>"; //.table-responsive

        if (User('PROFILE') == 'admin') {
            echo '<div class="panel-footer text-right p-r-20">' . SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"') . '</div>';
        }
        echo "</div>";
    }
    echo '</FORM>';
}

echo '<div id="modal_default_calendar" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _eventDetails . '</h5>';
echo '</div>';

echo '<div id="modal-res"></div>';
echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal
//----------------------- modal for event end---------------------//   

function calendarEventsVisibility()
{
    $return = '';
    $id = $_REQUEST['calendar_id'];
    $profiles_RET = DBGet(DBQuery('SELECT ID,TITLE FROM user_profiles ORDER BY ID'));
    $visibility_RET = DBGet(DBQuery('SELECT PROFILE_ID,PROFILE FROM calendar_events_visibility WHERE calendar_id=\'' . $id . '\''));
    foreach ($visibility_RET as $visibility) {
        if ($visibility['PROFILE_ID'] != '')
            $visible_profile[] = $visibility['PROFILE_ID'];
        else
            $visible_profile[] = $visibility['PROFILE'];
    }

//print_r($visible_profile);
//exit;

    $return .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _eventsVisibleTo . '</label>';
    $return .= '<div class="col-md-8">';
    foreach (array('admin' => '' . _administratorWCustom . '', 'teacher' => '' . _teacherWCustom . '', 'parent' => '' . _parentWCustom . '') as $profile_id => $profile)
        $return .= "<div class=\"checkbox\"><label><INPUT class=\"styled\" type=checkbox name=profiles[$profile_id] value=Y" . (is_array($visible_profile) && in_array($profile_id, $visible_profile) ? ' CHECKED' : '') . "> $profile</label></div>";
    $i = 3;
    foreach ($profiles_RET as $profile) {
        $i++;
        if ($profile['TITLE'] == "Super Administrator") {
            $profile['TITLE'] = _superAdministrator;
        } elseif ($profile['TITLE'] == "Administrator") {
            $profile['TITLE'] = _administrator;
        } elseif ($profile['TITLE'] == "Teacher") {
            $profile['TITLE'] = _teacher;
        } elseif ($profile['TITLE'] == "Student") {
            $profile['TITLE'] = _student;
        } elseif ($profile['TITLE'] == "Parent") {
            $profile['TITLE'] = _parent;
        } elseif ($profile['TITLE'] == "Admin Asst") {
            $profile['TITLE'] = _adminAsst;
        }
        $return .= '<div class="checkbox"><label><INPUT class="styled" type=checkbox name=profiles[' . $profile['ID'] . '] value=Y' . (is_array($visible_profile) && in_array($profile['ID'], $visible_profile) ? ' CHECKED' : '') . "> $profile[TITLE]</label></div>";
    }
    $return .= '</div>';
    $return .= '</div>'; //.form-group
    return $return;
}

function _makeCheckBoxInput($value, $eve_stat)
{
    if ($value)
        $val = '';
    else {
        if ($eve_stat == 'new')
            $val = '';
        else
            $val = 1;
    }
    return CheckboxInput($val, "show_all", '', '', false, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>');
}

function conv_day($days_arr)
{
    $cal_days = '';
    foreach ($days_arr as $day => $value) {
        switch ($day) {
            case 0:
                $cal_days .= 'U';
                break;
            case 1:
                $cal_days .= 'M';
                break;
            case 2:
                $cal_days .= 'T';
                break;
            case 3:
                $cal_days .= 'W';
                break;
            case 4:
                $cal_days .= 'H';
                break;
            case 5:
                $cal_days .= 'F';
                break;
            case 6:
                $cal_days .= 'S';
                break;
        }
    }
    return $cal_days;
}
