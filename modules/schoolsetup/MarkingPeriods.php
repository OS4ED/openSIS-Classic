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

DrawBC("" . _schoolSetup . " > " . ProgramTitle());

if (!$_REQUEST['marking_period_id'] && count($fy_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER'))) == 1 && !$_REQUEST['ajax']) {
    $_REQUEST['marking_period_id'] = $fy_RET[1]['MARKING_PERIOD_ID'];
    $_REQUEST['mp_term'] = 'FY';
}

unset($_SESSION['_REQUEST_vars']['marking_period_id']);
unset($_SESSION['_REQUEST_vars']['mp_term']);

switch ($_REQUEST['mp_term']) {
    case 'FY':
        $table = 'school_years';
        if ($_REQUEST['marking_period_id'] == 'new')
            $title = _newYear;
        break;

    case 'SEM':
        $table = 'school_semesters';
        if ($_REQUEST['marking_period_id'] == 'new')
            $title = _newSemester;
        break;

    case 'QTR':
        $table = 'school_quarters';
        if ($_REQUEST['marking_period_id'] == 'new')
            $title = _newQuarter;
        break;

    case 'PRO':
        $table = 'school_progress_periods';
        if ($_REQUEST['marking_period_id'] == 'new')
            $title = _newProgressPeriod;
        break;
}
$syear = '';
// UPDATING
if ($_REQUEST['day_tables'] && ($_POST['day_tables'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['day_tables'] as $id => $values) {
        $syear = $_REQUEST['year_tables'][$id]['START_DATE'];
        if ($_REQUEST['day_tables'][$id]['START_DATE'] && $_REQUEST['month_tables'][$id]['START_DATE'] && $_REQUEST['year_tables'][$id]['START_DATE'])
            $_REQUEST['tables'][$id]['START_DATE'] = $_REQUEST['day_tables'][$id]['START_DATE'] . '-' . $_REQUEST['month_tables'][$id]['START_DATE'] . '-' . $_REQUEST['year_tables'][$id]['START_DATE'];

        elseif (isset($_REQUEST['day_tables'][$id]['START_DATE']) && isset($_REQUEST['month_tables'][$id]['START_DATE']) && isset($_REQUEST['year_tables'][$id]['START_DATE']))
            $_REQUEST['tables'][$id]['START_DATE'] = '';

        if ($_REQUEST['day_tables'][$id]['END_DATE'] && $_REQUEST['month_tables'][$id]['END_DATE'] && $_REQUEST['year_tables'][$id]['END_DATE'])
            $_REQUEST['tables'][$id]['END_DATE'] = $_REQUEST['day_tables'][$id]['END_DATE'] . '-' . $_REQUEST['month_tables'][$id]['END_DATE'] . '-' . $_REQUEST['year_tables'][$id]['END_DATE'];
        elseif (isset($_REQUEST['day_tables'][$id]['END_DATE']) && isset($_REQUEST['month_tables'][$id]['END_DATE']) && isset($_REQUEST['year_tables'][$id]['END_DATE']))
            $_REQUEST['tables'][$id]['END_DATE'] = '';

        if ($_REQUEST['day_tables'][$id]['POST_START_DATE'] && $_REQUEST['month_tables'][$id]['POST_START_DATE'] && $_REQUEST['year_tables'][$id]['POST_START_DATE'])
            $_REQUEST['tables'][$id]['POST_START_DATE'] = $_REQUEST['day_tables'][$id]['POST_START_DATE'] . '-' . $_REQUEST['month_tables'][$id]['POST_START_DATE'] . '-' . $_REQUEST['year_tables'][$id]['POST_START_DATE'];
        elseif (isset($_REQUEST['day_tables'][$id]['POST_START_DATE']) && isset($_REQUEST['month_tables'][$id]['POST_START_DATE']) && isset($_REQUEST['year_tables'][$id]['POST_START_DATE']))
            $_REQUEST['tables'][$id]['POST_START_DATE'] = '';

        if ($_REQUEST['day_tables'][$id]['POST_END_DATE'] && $_REQUEST['month_tables'][$id]['POST_END_DATE'] && $_REQUEST['year_tables'][$id]['POST_END_DATE'])
            $_REQUEST['tables'][$id]['POST_END_DATE'] = $_REQUEST['day_tables'][$id]['POST_END_DATE'] . '-' . $_REQUEST['month_tables'][$id]['POST_END_DATE'] . '-' . $_REQUEST['year_tables'][$id]['POST_END_DATE'];
        elseif (isset($_REQUEST['day_tables'][$id]['POST_END_DATE']) && isset($_REQUEST['month_tables'][$id]['POST_END_DATE']) && isset($_REQUEST['year_tables'][$id]['POST_END_DATE']))
            $_REQUEST['tables'][$id]['POST_END_DATE'] = '';
    }
    if (!$_POST['tables'])
        $_POST['tables'] = $_REQUEST['tables'];
}

if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax']) && AllowEdit()) {
    $sql_ex = '';
    // ---------------------- Insert & Update Start ------------------------------ //

    if ($_REQUEST['marking_period_id'] !== 'new') {
        $marking_period_sql = "SELECT START_DATE,END_DATE FROM marking_periods WHERE marking_period_id=$_REQUEST[marking_period_id]";
        $sql = DBGet(DBQuery($marking_period_sql));
        foreach ($sql as $markingcolumns) {
            $endDate = date('Y-m-d', strtotime($_REQUEST['tables'][$_REQUEST['marking_period_id']]['END_DATE']));
            $startdate = date('Y-m-d', strtotime($_REQUEST['tables'][$_REQUEST['marking_period_id']]['START_DATE']));
            if ($startdate == $markingcolumns['START_DATE']) {
                unset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['START_DATE']);
                
            }
            if ($endDate == $markingcolumns['END_DATE']) {
                unset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['END_DATE']);
            }
        }
    }
   

    foreach ($_REQUEST['tables'] as $id => $columns) {
        if ($table == 'school_years') {
            $chk_tbl = 'school_semesters';
            $nm = "semester";
            $date_sql = 'SELECT MIN(START_DATE) AS START_DATE,MAX(END_DATE) AS END_DATE FROM ' . $chk_tbl . ' WHERE YEAR_ID = \'' . $_REQUEST['marking_period_id'] . '\' AND SCHOOL_ID =\'' .  UserSchool() . '\' AND SYEAR = \'' .  UserSyear() . '\'';
            $dates =  DBGet(DBQuery($date_sql));
            $dates = $dates[1];
            $value = date('Y-m-d', strtotime($columns['START_DATE']));
            $prev_fy_sql = 'SELECT END_DATE FROM school_years WHERE SCHOOL_ID =\'' .  UserSchool() . '\' AND SYEAR < \'' . UserSyear() . '\'';
            $prev_fy_dates = DBGet(DBQuery($prev_fy_sql));
            $prev_fy_dates = $prev_fy_dates[1];
        }
        if ($table == 'school_semesters') {
            $chk_tbl = 'school_years';
            $nm = "full year";
            $date_sql = 'SELECT START_DATE,END_DATE FROM ' . $chk_tbl . ' WHERE MARKING_PERIOD_ID = ' . $_REQUEST['year_id'] . ' AND SCHOOL_ID =\'' .  UserSchool() . '\' AND SYEAR = \'' .  UserSyear() . '\'';
            $dates =  DBGet(DBQuery($date_sql));
            $dates = $dates[1];
        }
        if ($table == 'school_quarters') {
            $chk_tbl = 'school_semesters';
            $nm = "semester";
            $date_sql = 'SELECT START_DATE,END_DATE FROM ' . $chk_tbl . ' WHERE MARKING_PERIOD_ID = ' . $_REQUEST['semester_id'] . ' AND SCHOOL_ID =\'' .  UserSchool() . '\' AND SYEAR = \'' .  UserSyear() . '\'';
            $dates =  DBGet(DBQuery($date_sql));
            $dates = $dates[1];
        }
        if ($table == 'school_progress_periods') {
            $chk_tbl = 'school_quarters';
            $nm = "quarter";
            $date_sql = 'SELECT START_DATE,END_DATE FROM ' . $chk_tbl . ' WHERE MARKING_PERIOD_ID = ' . $_REQUEST['quarter_id'] . ' AND SCHOOL_ID =\'' .  UserSchool() . '\' AND SYEAR = \'' .  UserSyear() . '\'';
            $dates =  DBGet(DBQuery($date_sql));
            $dates = $dates[1];
        }
        if ($id != 'new') {
            $asso_err = false;
            if (isset($columns['START_DATE'])) {
                $check = $columns['START_DATE'];
                $check_start = $check;
            } else {
                $check_date = DBGet(DBQuery('SELECT START_DATE FROM ' . $table . ' WHERE marking_period_id=\'' . $id . '\''));
                $check_date = $check_date[1];
                $check = $check_date['START_DATE'];
                $check_start = $check;
            }
            if (isset($columns['END_DATE'])) {
                $check1 = $columns['END_DATE'];
                $check_end = $check1;
            } else {
                $check_date1 = DBGet(DBQuery('SELECT END_DATE FROM ' . $table . ' WHERE marking_period_id=\'' . $id . '\''));
                $check_date1 = $check_date1[1];
                $check1 = $check_date1['END_DATE'];
                $check_end = $check1;
            }
            $days = floor((strtotime($check1, 0) - strtotime($check, 0)) / 86400);
            $mp_id = $_REQUEST['marking_period_id'];
            $title_mp = $_REQUEST['tables'][$mp_id]['TITLE'];
            if (strlen($title_mp) > 25) {
                $err_msg = '' . _titleCannnotBeGreaterThan_25Characters . '';
                $error = true;
            }
            if (array_key_exists('TITLE', $columns) && trim($columns['TITLE'] == '')) {
                $err_msg = '' . _dataNotSavedBecauseTitleCannotBeBlank . '';
                $error = true;
            }
            if (array_key_exists('SHORT_NAME', $columns) && trim($columns['SHORT_NAME'] == '')) {
                $err_msg = '' . _dataNotSavedBecauseShortNameCannotBeBlank . '';
                $error = true;
            }
            if (array_key_exists('SORT_ORDER', $columns) && trim($columns['SORT_ORDER'] != '')) {
                if (!is_number($columns['SORT_ORDER'])) {
                    $err_msg = '' . _dataNotSavedBecauseSortOrderAllowsOnlyNumericValue . '';
                    $error = true;
                }
            }
            if ($days <= 0) {
                $err_msg = '' . _dataNotSavedBecauseStartAndEndDateIsNotValid . '';
            } else {
                if (isset($columns['POST_START_DATE'])) {
                    $check = $columns['POST_START_DATE'];
                } else {
                    $check_date = DBGet(DBQuery('SELECT POST_START_DATE FROM ' . $table . ' WHERE marking_period_id=\'' . $id . '\''));
                    $check_date = $check_date[1];
                    $check = $check_date['POST_START_DATE'];
                }
                if (isset($columns['POST_END_DATE'])) {
                    $check1 = $columns['POST_END_DATE'];
                } else {
                    $check_date1 = DBGet(DBQuery('SELECT POST_END_DATE FROM ' . $table . ' WHERE marking_period_id=\'' . $id . '\''));
                    $check_date1 = $check_date1[1];
                    $check1 = $check_date1['POST_END_DATE'];
                }
                if (strtotime($check, 0) == '') {
                    $days = 0;
                }
                if (strtotime($check, 0) != '') {
                    $days = floor((strtotime($check1, 0) - strtotime($check, 0)) / 86400);
                }


                if (isset($columns['DOES_GRADES']) && $columns['DOES_GRADES'] != ''  && $days <= 0) {
                    if ($days == 0) {
                        if ($check == '')
                            $err_msg = '' . _pleaseGiveAGradePostingBeginDate . '';
                        else
                            $err_msg = '';
                    } else {
                        if ($check1 == '')
                            $err_msg = '' . _pleaseGiveAGradePostingBeginsAndEndDate . '';
                        elseif ($check == '')
                            $err_msg = '' . _pleaseGiveAGradePostingStartDate . '';
                        else
                            $err_msg = '' . _dataNotSavedBecauseGradePostingDateIsNotValid . '';
                    }
                    $error = true;
                }

                if (!(isset($columns['DOES_GRADES'])) && $days <= 0) {
                    if ($days == 0) {
                        if ($check == '' && $check1 != '') {
                            $err_msg = '' . _pleaseGiveAGradePostingBeginDate . '';
                            $error = true;
                        }
                    } else {
                        if ($check1 == '')
                            $err_msg = '' . _pleaseGiveAGradePostingBeginsAndEndDate . '';
                        elseif ($check == '')
                            $err_msg = '' . _pleaseGiveAGradePostingStartDate . '';
                        else
                            $err_msg = '' . _dataNotSavedBecauseGradePostingDateIsNotValid . '';
                        $error = true;
                    }
                }
                $graded =  DBGet(DBQuery('SELECT DOES_GRADES FROM ' . $table . ' WHERE marking_period_id=\'' . $id . '\''));

                if ($graded[1]['DOES_GRADES'] == 'Y' && !isset($columns['DOES_GRADES']) && $days <= 0) {
                    if ($days == 0)
                        $err_msg = '' . _pleaseGiveAGradePostingBeginsAndEndDate . '';
                    else {
                        if ($check1 == '')
                            $err_msg = '' . _pleaseGiveAGradePostingBeginsAndEndDate . '';
                        elseif ($check == '')
                            $err_msg = '' . _pleaseGiveAGradePostingStartDate . '';
                        else
                            $err_msg = '' . _dataNotSavedBecauseGradePostingDateIsNotValid . '';
                    }
                    $error = true;
                }
                if (array_key_exists('TITLE', $columns) && trim($columns['TITLE'] == '')) {
                    $err_msg = '' . _dataNotSavedBecauseTitleCannotBeBlank . '';
                    $error = true;
                }
                if ($error != true) {
                    $sql = 'UPDATE ' . $table . ' SET ';
                    foreach ($columns as $column => $value) {
                        $value = paramlib_validation($column, trim($value));
                        if ($column == 'DOES_GRADES' && $value == '') {
                            $sql_ex = 'update ' . $table . ' set DOES_EXAM=\'\' where marking_period_id=\'' . $_REQUEST['marking_period_id'] . '\'';
                        }
                        if ($column == 'TITLE' && $columns['TITLE'] != '') {
                            $TITLE_COUNT =  DBGet(DBQuery('SELECT * FROM marking_periods WHERE upper(TITLE)=\'' . strtoupper(singleQuoteReplace('', '', $value)) . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'AND MARKING_PERIOD_ID NOT IN(\'' . $_REQUEST['marking_period_id'] . '\')'));

                            if (is_countable($TITLE_COUNT) && count($TITLE_COUNT) > 0) {
                                $err_msg = _titleAlreadyExists;
                                break 2;
                            } else {
                                $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                                $go = true;
                            }
                        }

                        if ($column == 'START_DATE' && $columns['START_DATE'] != '') {
                            if ($_REQUEST['mp_term'] != 'FY') {
                                if (strtotime($dates['START_DATE']) <= strtotime($columns['START_DATE'])) {
                                    if ($value != '') {
                                        while (!VerifyDate($value)) {
                                            $value = date('Y-m-d', strtotime($value) - 86400);
                                        }
                                        $sql .= $column . '=\'' . singleQuoteReplace('', '', trim(date('Y-m-d', strtotime($value)))) . '\',';
                                        $go = true;
                                    }
                                } else {
                                    $err_msg = "" . _startDateCannotBeEarlierThan . " $nm " . _startDate . "";
                                    break 2;
                                }
                            } else {
                                if ($dates['START_DATE'] != '') {
                                    if (strtotime($dates['START_DATE']) >= strtotime($columns['START_DATE'])) {
                                        if (strtotime($columns['START_DATE']) <= strtotime($prev_fy_dates['END_DATE'])) {
                                            $err_msg = "" . _startDateCannotBeEarlierThanPreviousYearEndDate . "";
                                            break 2;
                                        } else {
                                            $cal_sql = 'SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                            $calender =  DBGet(DBQuery($cal_sql));
                                            $calender = $calender[1];
                                            $attendance_calendar = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE,MAX(SCHOOL_DATE) as END_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));

                                            $calender = $calender[1];


                                            if (strtotime($columns['START_DATE']) > strtotime($attendance_calendar[1]['START_DATE']) && $attendance_calendar[1]['START_DATE'] != '') {

                                                $err_msg = "" . _startDateCannotBeChangedBecauseTheCalenderHasAlreadyBeenCreated . "";
                                                break 2;
                                            } else {
                                                $stu_sql = 'SELECT COUNT(s.STUDENT_ID) AS TOTAL_REC FROM student_enrollment se,students s WHERE se.SCHOOL_ID=\'' . UserSchool() . '\' AND se.SYEAR=\'' . UserSyear() . '\' AND se.END_DATE IS NULL AND s.IS_DISABLE IS NULL';
                                                $students =  DBGet(DBQuery($stu_sql));
                                                $students = $students[1]['TOTAL_REC'];
                                                if ($students > 0 && $syear != UserSyear()) {
                                                    $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithStudents . "";
                                                    break 2;
                                                } else {
                                                    $stf_sql = 'SELECT ssr.STAFF_ID FROM staff s,staff_school_relationship ssr WHERE s.PROFILE_ID =\'2\' AND ssr.SCHOOL_ID=\'' . UserSchool() . '\' AND ssr.SYEAR=\'' . UserSyear() . '\'';
                                                    $staffs =  DBGet(DBQuery($stf_sql));
                                                    $staffs = $staffs[1];

                                                    if (is_countable($staffs) && count($staffs) > 0 && $syear != UserSyear()) {
                                                        $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithStaff . "";
                                                        break 2;
                                                    } else {
                                                        $subj_sql = 'SELECT SUBJECT_ID FROM course_subjects WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                        $subjects =  DBGet(DBQuery($subj_sql));
                                                        $subjects = $subjects[1];

                                                        if (is_countable($subjects) && count($subjects) > 0 && $syear != UserSyear()) {
                                                            $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithSubjects . "";
                                                            break 2;
                                                        } else {
                                                            $att_codes_sql = 'SELECT ID FROM attendance_codes WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                            $att_codes =  DBGet(DBQuery($att_codes_sql));
                                                            $att_codes = $att_codes[1];

                                                            if (is_countable($att_codes) && count($att_codes) > 0 && $syear != UserSyear()) {
                                                                $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithAttendanceCodes . "";
                                                                break 2;
                                                            } else {
                                                                $sp_sql = 'SELECT PERIOD_ID FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                $sp =  DBGet(DBQuery($sp_sql));
                                                                $sp = $sp[1];

                                                                if (is_countable($sp) && count($sp) > 0 && $syear != UserSyear()) {
                                                                    $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithSchoolPeriods . "";
                                                                    break 2;
                                                                } else {
                                                                    $fy_sql = 'SELECT SYEAR FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . $syear . '\'';
                                                                    $fy =  DBGet(DBQuery($fy_sql));
                                                                    $fy = $fy[1];
                                                                    if (is_countable($fy) && count($fy) > 0 && $syear != UserSyear()) {
                                                                        $err_msg = "" . _startDateCannotBeChangedBecauseTheSyearAlreadyExistsInPreviousYear . "";
                                                                        break 2;
                                                                    } else {
                                                                        if ($value != '') {
                                                                            while (!VerifyDate($value)) {
                                                                                $value = date('Y-m-d', strtotime($value) - 86400);
                                                                            }
                                                                            $sql .= $column . '=\'' . singleQuoteReplace('', '', trim(date('Y-m-d', strtotime($value)))) . '\',';
                                                                            $go = true;

                                                                            $fy_sql = 'UPDATE school_years SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                            DBQuery($fy_sql);
                                                                            $sem_sql = 'UPDATE  school_semesters SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                            DBQuery($sem_sql);
                                                                            $qtr_sql = 'UPDATE  school_quarters SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                            DBQuery($qtr_sql);
                                                                            $progp_sql = 'UPDATE school_progress_periods SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                            DBQuery($progp_sql);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $err_msg = _startDateCannotBeAfter . " $nm " . _startDate;
                                        break 2;
                                    }
                                } else {
                                    if (strtotime($columns['START_DATE']) <= strtotime($prev_fy_dates['END_DATE'])) {
                                        $err_msg = "" . _startDateCannotBeEarlierThanPreviousYearEndDate . "";
                                        break 2;
                                    } else {
                                        $cal_sql = 'SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                        $calender =  DBGet(DBQuery($cal_sql));
                                        $attendance_calendar = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE,MAX(SCHOOL_DATE) as END_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
                                        //                                                                        
                                        $calender = $calender[1];

                                        if ($attendance_calendar[1]['START_DATE'] != '' && strtotime($columns['START_DATE']) > strtotime($attendance_calendar[1]['START_DATE'])) {

                                            $err_msg = "" . _startDateCannotBeChangedBecauseTheCalenderHasAlreadyBeenCreated . "";
                                            break 2;
                                        } else {
                                            $stu_sql = 'SELECT s.STUDENT_ID FROM student_enrollment se,students s WHERE se.SCHOOL_ID=\'' . UserSchool() . '\' AND se.SYEAR=\'' . UserSyear() . '\' AND se.END_DATE IS NULL AND s.IS_DISABLE IS NULL';
                                            $students =  DBGet(DBQuery($stu_sql));
                                            $students = $students[1];

                                            if (is_countable($students) && count($students) > 0 && $syear != UserSyear()) {
                                                $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithStudents . "";
                                                break 2;
                                            } else {
                                                $stf_sql = 'SELECT ssr.STAFF_ID FROM staff s,staff_school_relationship ssr WHERE s.PROFILE_ID in (\'0\',\'1\',\'2\') AND ssr.SCHOOL_ID=\'' . UserSchool() . '\' AND ssr.SYEAR=\'' . UserSyear() . '\'';
                                                $staffs =  DBGet(DBQuery($stf_sql));
                                                $staffs = $staffs[1];

                                                if (is_countable($staffs) && count($staffs) > 0 && $syear != UserSyear()) {
                                                    $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithStaff . "";
                                                    break 2;
                                                } else {
                                                    $subj_sql = 'SELECT SUBJECT_ID FROM course_subjects WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                    $subjects =  DBGet(DBQuery($subj_sql));
                                                    $subjects = $subjects[1];

                                                    if (is_countable($subjects) && count($subjects) > 0 && $syear != UserSyear()) {
                                                        $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithSubjects . "";
                                                        break 2;
                                                    } else {
                                                        $att_codes_sql = 'SELECT ID FROM attendance_codes WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                        $att_codes =  DBGet(DBQuery($att_codes_sql));
                                                        $att_codes = $att_codes[1];

                                                        if (is_countable($att_codes) && count($att_codes) > 0 && $syear != UserSyear()) {
                                                            $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithAttendanceCodes . "";
                                                            break 2;
                                                        } else {
                                                            $sp_sql = 'SELECT PERIOD_ID FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                            $sp =  DBGet(DBQuery($sp_sql));
                                                            $sp = $sp[1];

                                                            if (is_countable($sp) && count($sp) > 0 && $syear != UserSyear()) {
                                                                $err_msg = "" . _startDateCannotBeChangedBecauseTheFullYearWithSchoolPeriods . "";
                                                                break 2;
                                                            } else {
                                                                $fy_sql = 'SELECT SYEAR FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . $syear . '\'';
                                                                $fy =  DBGet(DBQuery($fy_sql));
                                                                $fy = $fy[1];
                                                                if (is_countable($fy) && count($fy) > 0 && $syear != UserSyear()) {
                                                                    $err_msg = "" . _startDateCannotBeChangedBecauseTheSyearAlreadyExistsInPreviousYear . "";
                                                                    break 2;
                                                                } else {
                                                                    if ($value != '') {
                                                                        while (!VerifyDate($value)) {
                                                                            $value = date('Y-m-d', strtotime($value) - 86400);
                                                                        }
                                                                        $sql .= $column . '=\'' . singleQuoteReplace('', '', trim(date('Y-m-d', strtotime($value)))) . '\',';
                                                                        $go = true;

                                                                        $fy_sql = 'UPDATE school_years SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                        DBQuery($fy_sql);
                                                                        $sem_sql = 'UPDATE  school_semesters SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                        DBQuery($sem_sql);
                                                                        $qtr_sql = 'UPDATE  school_quarters SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                        DBQuery($qtr_sql);
                                                                        $progp_sql = 'UPDATE school_progress_periods SET SYEAR=\'' . $syear . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                                                        DBQuery($progp_sql);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $st_date = DBGet(DBQuery('SELECT START_DATE FROM marking_periods WHERE MARKING_PERIOD_ID=\'' . $id . '\''));
                            if (strtotime($columns['START_DATE']) != strtotime($st_date[1]['START_DATE'])) {
                                $scheduleassociation = DBGet(DBQuery('SELECT * FROM schedule WHERE MARKING_PERIOD_ID=\'' . $id . '\''));

                                if (is_countable($scheduleassociation) && count($scheduleassociation) > 0) {

                                    $asso_err = true;
                                }
                            }
                        }
                        if ($column == 'END_DATE' && $columns['END_DATE'] != '') {

                            if ($_REQUEST['mp_term'] != 'FY') {
                                if (strtotime($dates['END_DATE']) >= strtotime($columns['END_DATE'])) {
                                    if ($value != '') {
                                        while (!VerifyDate($value)) {
                                            $value = date('Y-m-d', strtotime($value) - 86400);
                                        }
                                        $sql .= $column . '=\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                                        $go = true;
                                    }
                                }
                                //                                                                else
                                //                                                                { 
                                //                                                                    $err_msg="End date cannot be after $nm end date";
                                //                                                                    break 2;
                                //                                                                }
                            } else {
                                if ($dates['END_DATE'] != '') {
                                    if (strtotime($dates['END_DATE']) <= strtotime($columns['END_DATE'])) {

                                        $cal_sql = 'SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                        $calender =  DBGet(DBQuery($cal_sql));
                                        $calender = $calender[1];
                                        $attendance_calendar = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE,MAX(SCHOOL_DATE) as END_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
                                            //                                                                 
                                        ;
                                        //                                                                         $vdate = explode("-", $columns['END_DATE']);                                                                            $calender=$calender[1];
                                        //                                                                           $m=MonthNWSwitch($vdate[1],'tonum');
                                        //$end_m = date('Y-m-d', $columns['END_DATE']);
                                        $end_m = date('Y-m-d', strtotime($columns['END_DATE']));

                                        // if ($attendance_calendar[1]['END_DATE'] != '' && strtotime($end_m) > strtotime($attendance_calendar[1]['END_DATE'])) {
                                        if ($attendance_calendar[1]['END_DATE'] != '' && strtotime($end_m) > strtotime($attendance_calendar[1]['END_DATE'])) {

                                            $err_msg = "" . _endDateCannotBeChangedBecauseTheCalendarHasAlreadyBeenCreated . "";
                                            break 2;
                                        }
                                        if ($value != '') {
                                            while (!VerifyDate($value)) {
                                                $value = date('Y-m-d', strtotime($value) - 86400);
                                            }
                                            $sql .= $column . '=\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                                            $go = true;
                                        }
                                    }
                                    //                                                                    else
                                    //                                                                    { 
                                    //                                                                        $err_msg="End date cannot be before $nm end date";
                                    //                                                                        break 2;
                                    //                                                                    }
                                } else {
                                    if ($value != '') {
                                        $cal_sql = 'SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
                                        $calender =  DBGet(DBQuery($cal_sql));
                                        $calender = $calender[1];
                                        $attendance_calendar = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE,MAX(SCHOOL_DATE) as END_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
                                        //                                                                       
                                        $calender = $calender[1];


                                        if (strtotime($columns['END_DATE']) < strtotime($attendance_calendar[1]['END_DATE'])) {

                                            $err_msg = "" . _endDateCannotBeChangedBecauseTheCalendarHasAlreadyBeenCreated . "";
                                            break 2;
                                        } else {
                                            while (!VerifyDate($value)) {
                                                $value = date('Y-m-d', strtotime($value) - 86400);
                                            }
                                            $sql .= $column . '=\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';

                                            $go = true;
                                        }
                                    }
                                }
                            }
                            $st_date = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID=\'' . $id . '\''));
                            if (strtotime($columns['END_DATE']) != strtotime($st_date[1]['END_DATE'])) {
                                $scheduleassociation = DBGet(DBQuery('SELECT * FROM schedule WHERE MARKING_PERIOD_ID=\'' . $id . '\''));

                                if (is_countable($scheduleassociation) && count($scheduleassociation) > 0) {

                                    $asso_err = true;
                                }
                            }
                        }
                        if ($column == 'POST_START_DATE') {
                            //                                                           
                            if ($value != '') {
                                while (!VerifyDate($value)) {
                                    $value = date('Y-m-d', strtotime($value) - 86400);
                                }

                                if (strtotime($value) >= strtotime($check_end)) {
                                    $err_msg = "" . _gradePostingBeginsDateCannotOccurAfterTheMarkingPeriodEndDate . "";
                                    break 2;
                                }

                                if (strtotime($value) >= strtotime($check_start)) {
                                    $sql .= $column . '=\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                                    $go = true;
                                } else {
                                    $err_msg = "" . _gradePostingBeginsDateCannotOccurBeforeTheMarkingPeriodBeginsDate . "";
                                    break 2;
                                }
                            } else {
                                $sql .= $column . '=\'\',';
                                $column = 'POST_END_DATE';
                                $sql .= $column . '=\'\',';
                                $go = true;
                            }
                            //                                                     
                        }
                        if ($column == 'POST_END_DATE') {
                            //                                                            
                            if ($value != '') {
                                while (!VerifyDate($value)) {
                                    $value = date('Y-m-d', strtotime($value) - 86400);
                                }

                                $sql .= $column . '=\'' . singleQuoteReplace('', '', trim(date('Y-m-d', strtotime($value)))) . '\',';
                                $go = true;
                            } else {
                                $sql .= $column . '=\'\',';
                                $go = true;
                            }
                            //                                                     
                        }
                        if ($column != 'START_DATE' && $column != 'END_DATE' && $column != 'POST_START_DATE' && $column != 'POST_END_DATE') {
                            $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                            $go = true;
                        }
                        if ($column == 'SORT_ORDER') {
                            if ($value == '') {
                                $sql .= $column . '=NULL,';
                                $go = true;
                            }
                        }
                    }

                    $sql = substr($sql, 0, -1) . ' WHERE MARKING_PERIOD_ID=\'' . $id . '\'';
                    //                                            

                    if ($asso_err)
                        $go = false;
                }
            }
        } else {

            DBQuery('INSERT INTO marking_period_id_generator (id)VALUES (NULL)');

            $id_RET = DBGet(DBQuery('SELECT  max(id) AS ID from marking_period_id_generator'));


            $sql = 'INSERT INTO ' . $table . ' ';
            $fields = 'MARKING_PERIOD_ID,SYEAR,SCHOOL_ID,';
            $values = '\'' . $id_RET[1]['ID'] . '\',\'' . UserSyear() . '\',\'' . UserSchool() . '\',';

            $_REQUEST['marking_period_id'] = $id_RET[1]['ID'];

            switch ($_REQUEST['mp_term']) {
                case 'SEM':
                    $fields .= 'YEAR_ID,';
                    $values .= '\'' . $_REQUEST['year_id'] . '\',';
                    break;

                case 'QTR':
                    $fields .= 'SEMESTER_ID,';
                    $values .= '\'' . $_REQUEST['semester_id'] . '\',';
                    break;

                case 'PRO':
                    $fields .= 'QUARTER_ID,';
                    $values .= '\'' . $_REQUEST['quarter_id'] . '\',';
                    break;
            }

            $go = false;
            foreach ($columns as $column => $value) {
                $value = paramlib_validation($column, trim($value));
                if ($column == 'START_DATE' || $column == 'END_DATE' || $column == 'POST_START_DATE' || $column == 'POST_END_DATE') {
                    if (!VerifyDate($value) && $value != '')
                        BackPrompt('' . _notAllOfTheDatesWereEnteredCorrectly . '.');
                }
                if ($value) {
                    if ($column == 'TITLE' && $columns['TITLE'] != '') {
                        $TITLE_COUNT =  DBGet(DBQuery('SELECT * FROM marking_periods WHERE upper(TITLE)=\'' . strtoupper(singleQuoteReplace('', '', $value)) . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
                        if (is_countable($TITLE_COUNT) && count($TITLE_COUNT) > 0) {
                            $err_msg = 'Title alreday exsits';
                            $_REQUEST['marking_period_id'] = 'new';
                            break 2;
                        }
                    }
                    if ($column == 'START_DATE' && $columns['START_DATE'] != '') {
                        if (strtotime($dates['START_DATE']) <= strtotime($columns['START_DATE'])) {
                            $fields .= $column . ',';
                            $values .= '\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';

                            $go = true;
                        } else {
                            $err_msg = "" . _startDateCannotBeEarlierThan . " $nm " . _startDate . "";
                            $_REQUEST['marking_period_id'] = 'new';
                            break 2;
                        }
                    }
                    if ($column == 'END_DATE' && $columns['END_DATE'] != '') {
                        //                                                if(strtotime($dates['END_DATE'])>=strtotime($columns['END_DATE']))
                        //                                                {
                        $fields .= $column . ',';
                        $values .= '\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                        $go = true;
                        //                                                }
                        //                                                else
                        //                                                {
                        //                                                    $err_msg="End date cannot be after $nm end date";
                        //                                                    $_REQUEST['marking_period_id']='new';
                        //                                                    break 2;
                        //                                                }
                    }
                    if (($column == 'POST_START_DATE' && $columns['POST_START_DATE'] != '') || ($column == 'POST_END_DATE' && $columns['POST_END_DATE'] != '')) {
                        if ($column == 'POST_START_DATE') {
                            if ($value != '') {

                                if (strtotime($value) >= strtotime($columns['END_DATE'])) {
                                    $err_msg = "" . _gradePostingBeginsDateCannotOccurAfterTheMarkingPeriodEndDate . "";
                                    $_REQUEST['marking_period_id'] = 'new';
                                    break 2;
                                }
                                //                                                   
                                $fields .= $column . ',';
                                $values .= '\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                                $go = true;
                            }
                            //                                              
                        }

                        if ($column == 'POST_END_DATE') {
                            //                                                  
                            $fields .= $column . ',';
                            $values .= '\'' . singleQuoteReplace('', '', date('Y-m-d', strtotime($value))) . '\',';
                            $go = true;
                            //                                                  
                        }
                    }
                    if ($column != 'START_DATE' && $column != 'END_DATE' && $column != 'POST_START_DATE' && $column != 'POST_END_DATE') {
                        $fields .= $column . ',';
                        $values .= '\'' . singleQuoteReplace('', '', $value) . '\',';
                        $go = true;
                    }
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
        }

        // CHECK TO MAKE SURE ONLY ONE MP & ONE GRADING PERIOD IS OPEN AT ANY GIVEN TIME
        $columns['START_DATE'] = date("Y-m-d", strtotime($columns['START_DATE']));
        $columns['END_DATE'] = date("Y-m-d", strtotime($columns['END_DATE']));
        $dates_RET = DBGet(DBQuery(
            'SELECT MARKING_PERIOD_ID FROM ' . $table . ' WHERE (true=false'
                . (($columns['START_DATE']) ? ' OR \'' . $columns['START_DATE'] . '\' BETWEEN START_DATE AND END_DATE' : '')
                . (($columns['END_DATE']) ? ' OR \'' . $columns['END_DATE'] . '\' BETWEEN START_DATE AND END_DATE' : '')
                . (($columns['START_DATE'] && $columns['END_DATE']) ? ' OR START_DATE BETWEEN \'' . $columns['START_DATE'] . '\' AND \'' . $columns['END_DATE'] . '\'
				OR END_DATE BETWEEN \'' . $columns['START_DATE'] . '\' AND \'' . $columns['END_DATE'] . '\'' : '')
                . ') AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'' . (($id != 'new') ? ' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND MARKING_PERIOD_ID!=\'' . $id . '\'' : '')
        ));
        $posting_RET = DBGet(DBQuery(
            'SELECT MARKING_PERIOD_ID FROM ' . $table . ' WHERE (true=false'
                . (($columns['POST_START_DATE']) ? ' OR \'' . $columns['POST_START_DATE'] . '\' BETWEEN POST_START_DATE AND POST_END_DATE' : '')
                . (($columns['POST_END_DATE']) ? ' OR \'' . $columns['POST_END_DATE'] . '\' BETWEEN POST_START_DATE AND POST_END_DATE' : '')
                . (($columns['POST_START_DATE'] && $columns['POST_END_DATE']) ? ' OR POST_START_DATE BETWEEN \'' . $columns['POST_START_DATE'] . '\' AND \'' . $columns['POST_END_DATE'] . '\'
				OR POST_END_DATE BETWEEN \'' . $columns['POST_START_DATE'] . '\' AND \'' . $columns['POST_END_DATE'] . '\'' : '')
                . ') AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'' . (($id != 'new') ? ' AND MARKING_PERIOD_ID!=\'' . $id . '\'' : '')
        ));

        if ($go) {
            $comment_stat =  DBGet(DBQuery('SELECT DOES_COMMENTS FROM ' . $table . ' WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']));
            $comment_stat = $comment_stat[1]['DOES_COMMENTS'];
            $check_assoc = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM student_report_card_grades WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']));
            $check_assoc = $check_assoc[1]['REC_EX'];
            //                            if( (isset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['DOES_GRADES']) || isset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['POST_START_DATE']) || isset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['POST_END_DATE']) ) && $check_assoc>0)
            //                            $err_msg='Cannot modify marking period as students have been graded';
            //                            else
            //                            {

            DBQuery($sql);
            if ($sql_ex != '')
                DBQuery($sql_ex);
            $id_RET = DBGet(DBQuery('SELECT  max(marking_period_id) AS ID from marking_periods'));
            $new_mp_id = $id_RET[1]['ID'];
            $mp_qr =  DBGet(DBQuery('SELECT PARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . $new_mp_id . ''));

            $parent_id = $mp_qr[1]['PARENT_ID'];
            if ($_REQUEST['marking_period_id'] != 'new')

                $check_type = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']));

            $parent_qr =  DBGet(DBQuery('select start_date,end_date from marking_periods where marking_period_id="' . $parent_id . '"'));
            $child_qr =  DBGet(DBQuery('select min(start_date) as start_date,max(end_date) as end_date from marking_periods where parent_id="' . $parent_id . '"'));
            //          if($check_type[1]['MP_TYPE']!='year' && strtotime($parent_qr[1]['START_DATE'])==strtotime($child_qr[1]['START_DATE']) && strtotime($parent_qr[1]['END_DATE'])==strtotime($child_qr[1]['END_DATE']))
            //          {
            //           
            //            echo '<span><FONT color=green>Exam option is enabled for parent marking period.</FONT></span>';
            //          }

            //                            }

            if ($err_msg != '' && isset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['DOES_COMMENTS']) && $_REQUEST['tables'][$_REQUEST['marking_period_id']]['DOES_COMMENTS'] == 'Y') {
                unset($err_msg);
                DBQuery('UPDATE ' . $table . ' SET DOES_COMMENTS=\'Y\' WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']);
            }
            if ($err_msg != '' && $comment_stat == 'Y' && isset($_REQUEST['tables'][$_REQUEST['marking_period_id']]['DOES_COMMENTS']) && $_REQUEST['tables'][$_REQUEST['marking_period_id']]['DOES_COMMENTS'] != 'Y') {
                unset($err_msg);
                DBQuery('UPDATE ' . $table . ' SET DOES_COMMENTS=NULL WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']);
            }
        }
        if ($asso_err)
            $err_msg = '' . _startDateOrEndDateCannotBeChangedBecauseMarkingPeriodHasAssociation . '';
        //----------------------------------------------------------------------------------------------------------------------		
        if ($go) {
            $UserMp = GetCurrentMP('QTR', DBDate());
            $_SESSION['UserMP'] = $UserMp;
            if (!$UserMp) {
                $UserMp = GetCurrentMP('SEM', DBDate());
                $_SESSION['UserMP'] = $UserMp;
            }
            if (!$UserMp) {
                $UserMp = GetCurrentMP('FY', DBDate());
                $_SESSION['UserMP'] = $UserMp;
            }
        }
        //---------------------------------------------------------------------------------------------------------------------------
    }
    // ---------------------- Insert & Update End ------------------------------ //

    unset($_REQUEST['tables']);
    unset($_SESSION['_REQUEST_vars']['tables']);
}


if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {
    $extra = array();
    switch ($table) {
        case 'school_years':
            $name = 'year';
            $parent_term = '';
            $parent_id = '';
            $parent_table = '';
            $year_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']);
            $extra[] = 'DELETE FROM school_progress_periods WHERE QUARTER_ID IN (SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SEMESTER_ID IN (SELECT MARKING_PERIOD_ID FROM school_semesters WHERE YEAR_ID=\'' . $year_id . '\'))';
            $extra[] = 'DELETE FROM school_quarters WHERE SEMESTER_ID IN (SELECT MARKING_PERIOD_ID FROM school_semesters WHERE YEAR_ID=\'' . $year_id . '\')';
            $extra[] = 'DELETE FROM school_semesters WHERE YEAR_ID=\'' . $year_id . '\'';
            break;

        case 'school_semesters':
            $name = 'semester';
            $parent_term = 'FY';
            $parent_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['year_id']);
            $parent_table = 'school_years';
            $sems_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']);
            $extra[] = 'DELETE FROM school_progress_periods WHERE QUARTER_ID IN (SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SEMESTER_ID=\'' . $sems_id . '\')';
            $extra[] = 'DELETE FROM school_quarters WHERE SEMESTER_ID=\'' . $sems_id . '\'';
            break;

        case 'school_quarters':
            $name = 'quarter';
            $parent_term = 'SEM';
            $parent_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['semester_id']);
            $parent_table = 'school_semesters';
            $qrt_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']);
            $extra[] = 'DELETE FROM school_progress_periods WHERE QUARTER_ID=\'' . $qrt_id . '\'';
            break;

        case 'school_progress_periods':
            $name = 'progress period';
            $parent_term = 'QTR';
            $parent_id = paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['quarter_id']);
            $parent_table = 'school_quarters';
            break;
    }
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM course_details WHERE MARKING_PERIOD_ID=\'' . paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']) . '\' OR MARKING_PERIOD_ID IN(SELECT MARKING_PERIOD_ID FROM marking_periods WHERE PARENT_ID=\'' . paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']) . '\')'));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    $queryString = "mp_term=$_REQUEST[mp_term]&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]&marking_period_id=$_REQUEST[marking_period_id]";
    if ($has_assigned > 0) {
        UnableDeletePromptMod('Marking period cannot be deleted because course periods are created on this marking period.', '', $queryString);
    } else {

        if ($table == 'school_quarters')
            $check_other_assocs = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM school_progress_periods WHERE QUARTER_ID=' . $_REQUEST['marking_period_id']));
        else
            $check_other_assocs = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM marking_periods WHERE PARENT_ID=' . $_REQUEST['marking_period_id']));
        $check_other_assocs = $check_other_assocs[1]['REC_EX'];

        if ($check_other_assocs != 0) {
            if ($table == 'school_quarters') {
                if ($check_other_assocs > 1)
                    $assoc_t = 'progress periods ';
                else
                    $assoc_t = 'progress period';
            } else {
                $get_assoc_rltns = DBGet(DBQuery('SELECT distinct MP_TYPE,COUNT(1) as TYPE_COUNT FROM marking_periods WHERE PARENT_ID=' . $_REQUEST['marking_period_id']));
                foreach ($get_assoc_rltns as $gai => $gad) {
                    if ($gad['TYPE_COUNT'] > 1)
                        $assoc_t[] = $gad['MP_TYPE'] . 's';
                    else
                        $assoc_t[] = $gad['MP_TYPE'];
                }
                $assoc_t = implode(',', $assoc_t);
            }
            UnableDeletePromptMod('' . _markingPeriodCannotBeDeletedBecauseItHasAssocitationWith . ' ' . $assoc_t . '.');
        } else {
            if (DeletePromptMod($name, $queryString)) {
                foreach ($extra as $sql)
                    DBQuery($sql);
                DBQuery('DELETE FROM ' . $table . ' WHERE MARKING_PERIOD_ID=\'' . paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']) . '\'');
                unset($_REQUEST['modfunc']);
                $_REQUEST['mp_term'] = $parent_term;
                $_REQUEST['marking_period_id'] = $parent_id;
                $table = $parent_table;
            }
        }
    }
    unset($_SESSION['_REQUEST_vars']['modfunc']);
}

if (!$_REQUEST['modfunc']) {
    if ($_REQUEST['marking_period_id'] != 'new')
        $delete_button = "<INPUT type=button class=\"btn btn-danger\" value=" . _delete . " onClick='load_link(\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&mp_term=$_REQUEST[mp_term]&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]&quarter_id=$_REQUEST[quarter_id]&marking_period_id=$_REQUEST[marking_period_id]\")'>";

    // ADDING & EDITING FORM
    if ($_REQUEST['marking_period_id'] && $_REQUEST['marking_period_id'] != 'new') {
        $sql = 'SELECT TITLE,SHORT_NAME,SORT_ORDER,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,
						START_DATE,END_DATE,POST_START_DATE,POST_END_DATE
				FROM ' . $table . '
				WHERE MARKING_PERIOD_ID=\'' . paramlib_validation($column = 'MARKING_PERIOD_ID', $_REQUEST['marking_period_id']) . '\'';
        $QI = DBQuery($sql);
        $RET = DBGet($QI);
        $RET = $RET[1];
        $title = $RET['TITLE'];
    }

    if (clean_param($_REQUEST['marking_period_id'], PARAM_ALPHANUM)) {
        $f = 0;
        if ($_REQUEST['marking_period_id'] != 'new') {
            $parent_qr = DBGet(DBQuery('select start_date,end_date from marking_periods where marking_period_id=' . $_REQUEST['marking_period_id'] . ''));
            $child_qr = DBGet(DBQuery('select min(start_date) as start_date,max(end_date) as end_date from marking_periods where parent_id=' . $_REQUEST['marking_period_id'] . ''));
            if (strtotime($parent_qr[1]['START_DATE']) == strtotime($child_qr[1]['START_DATE']) && strtotime($parent_qr[1]['END_DATE']) == strtotime($child_qr[1]['END_DATE'])) {

                $f = 1;
            }
        }
        if ($err_msg) {
            echo "<div class=\"alert alert-danger alert-bordered\">" . $err_msg . "</div>";

            unset($err_msg);
        }
        echo "<FORM name=marking_period class=\"form-horizontal\" id=marking_period action=Modules.php?modname=$_REQUEST[modname]&mp_term=$_REQUEST[mp_term]&marking_period_id=$_REQUEST[marking_period_id]&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]&quarter_id=$_REQUEST[quarter_id] method=POST>";
        PopTable('header', $title);
        $header .= '<div class="row"><div class="col-md-12">';

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _title . '</label><div class="col-md-8">' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['marking_period_id'] . '][TITLE]', '', 'class=form-control') . '</div></div>';
        $header .= '</div><div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _shortName . '</label><div class="col-md-8">' . TextInput($RET['SHORT_NAME'], 'tables[' . $_REQUEST['marking_period_id'] . '][SHORT_NAME]', '', 'class=form-control') . '</div></div>';
        $header .= '</div>';
        $header .= '</div>'; //.row

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _comments . '</label><div class="col-md-8">' . CheckboxInput_comments($RET['DOES_COMMENTS'], 'tables[' . $_REQUEST['marking_period_id'] . '][DOES_COMMENTS]', '', $checked, $_REQUEST['marking_period_id'] == 'new', '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') . '</div></div>';
        $header .= '</div><div class="col-md-6">';
        if ($RET['DOES_GRADES'] != '')
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _graded . '</label><div class="col-md-8">' . CheckboxInput_grade($RET['DOES_GRADES'], 'tables[' . $_REQUEST['marking_period_id'] . '][DOES_GRADES]', '', $checked, $_REQUEST['marking_period_id'] == 'new', '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', 'true', 'onclick=show_div("tables[' . $_REQUEST['marking_period_id'] . '][DOES_GRADES]",' . $_REQUEST['marking_period_id'] . ');') . '</div></div>';
        else
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">'._graded.'</label><div class="col-md-8">' . CheckboxInput_grade($RET['DOES_GRADES'], 'tables[' . $_REQUEST['marking_period_id'] . '][DOES_GRADES]', '', $checked, $_REQUEST['marking_period_id'] == 'new', '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', 'true', 'onclick=show_div(\'tables[' . $_REQUEST['marking_period_id'] . '][DOES_GRADES]\',\'' . $_REQUEST['marking_period_id'] . '\');') . '</div></div>';
        $header .= '</div>';
        $header .= '</div>'; //.row

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        if (clean_param($_REQUEST['marking_period_id'], PARAM_ALPHANUM) == 'new')
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _sortOrder . '</label><div class="col-md-8">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['marking_period_id'] . '][SORT_ORDER]', '', 'class=form-control onKeyDown="return numberOnlyMod(event,this);"') . '</div></div>';
        else
            $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _sortOrder . ' </label><div class="col-md-8">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['marking_period_id'] . '][SORT_ORDER]', '', 'class=form-control onKeyDown=\"return numberOnlyMod(event,this);\"') . '</div></div>';
        $header .= '</div><div class="col-md-6">';
        //        if ($f == 1){
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _exam . '</label><div class="col-md-8">' . CheckboxInput_exam($RET['DOES_EXAM'], 'tables[' . $_REQUEST['marking_period_id'] . '][DOES_EXAM]', '', $checked, $_REQUEST['marking_period_id'] == 'new', '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>', true, '' . ($RET['DOES_GRADES'] == "Y" ? '' : 'disabled') . '') . '</div></div>';
        //        }
        $header .= '</div>'; //.col-md-6
        $header .= '</div>'; //.row

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _begins . '</label><div class="col-md-8">' . DateInputAY($RET['START_DATE'], 'tables[' . $_REQUEST['marking_period_id'] . '][START_DATE]', 1) . '</div></div>';
        $header .= '</div><div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _ends . '</label><div class="col-md-8">' . DateInputAY($RET['END_DATE'], 'tables[' . $_REQUEST['marking_period_id'] . '][END_DATE]', 2) . '</div></div>';
        $header .= '</div>'; //.col-md-6
        $header .= '</div>'; //.row

        $header .= '<div id=grade_div style="' . ($RET['DOES_GRADES'] == 'Y' ? "display:block" : "display:none") . '">';
        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _gradePostingBegins . '</label><div class="col-md-8">' . DateInputAY($RET['POST_START_DATE'], 'tables[' . $_REQUEST['marking_period_id'] . '][POST_START_DATE]', 3) . '</div></div>';
        $header .= '</div><div class="col-md-6">';
        $header .= '<div class="form-group"><label class="col-md-4 control-label text-right">' . _gradePostingEnds . '</label><div class="col-md-8">' . DateInputAY($RET['POST_END_DATE'], 'tables[' . $_REQUEST['marking_period_id'] . '][POST_END_DATE]', 4) . '</div></div>';
        $header .= '</div>'; //.col-md-6
        $header .= '</div>'; //.row
        $header .= '</div>'; //#grade_div

        $str_srch = '<div class="form-group"><label class="col-md-4 control-label text-right">' . _comments . '</label><div class="col-md-8">' . CheckboxInput($RET['DOES_COMMENTS'], 'tables[' . $_REQUEST['marking_period_id'] . '][DOES_COMMENTS]', '', $checked, $_REQUEST['marking_period_id'] == 'new', '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</div></div>';

        $header .= '</div></div>'; //.row /.col-md-6
        DrawHeader($header);


        echo '<div>';
        if (clean_param($_REQUEST['marking_period_id'], PARAM_ALPHANUM) == 'new') {
            echo AllowEdit() ? '<hr/><div class="text-right"><INPUT type=submit value=' . _save . ' id="setupMPBtn" class="btn btn-primary" onclick="return formcheck_school_setup_marking(this);"></div>' : '';
        } elseif ($_REQUEST['mp_term'] != 'FY') {
            echo AllowEdit() ? '<hr/><div class="text-right">' . $delete_button . '&nbsp;&nbsp;<INPUT type=submit name=btn_save id=btn_save value=' . _save . ' class="btn btn-primary" onclick="self_disable(this);"></div>' : '';
        } else {
            echo AllowEdit() ? '<hr/><div class="text-right"><INPUT type=submit name=btn_save id=btn_save value=' . _save . ' class="btn btn-primary" onclick="self_disable(this);" /></div>' : '';
        }
        echo '</div>';

        PopTable('footer');

        echo '</FORM>';
        unset($_SESSION['_REQUEST_vars']['marking_period_id']);
        unset($_SESSION['_REQUEST_vars']['mp_term']);
    }

    // DISPLAY THE MENU
    $LO_options = array('save' => false, 'search' => false);

    echo '<div class="row">';

    // FY
    $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER';
    $QI = DBQuery($sql);
    $fy_RET = DBGet($QI);

    if (is_countable($fy_RET) && count($fy_RET)) {
        if ($_REQUEST['mp_term']) {
            if ($_REQUEST['mp_term'] == 'FY')
                $_REQUEST['year_id'] = $_REQUEST['marking_period_id'];

            foreach ($fy_RET as $key => $value) {
                if ($value['MARKING_PERIOD_ID'] == $_REQUEST['year_id'])
                    $fy_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="col-md-4">';
    echo '<div class="panel panel-white">';
    $columns = array('TITLE' => _year);
    $link = array();
    $link['TITLE']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&mp_term=FY\");'";
    $link['TITLE']['variables'] = array('marking_period_id' => 'MARKING_PERIOD_ID');
    if (!count($fy_RET))
        $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&mp_term=FY&marking_period_id=new";

    ListOutput($fy_RET, $columns,  _year, _years, $link, array(), $LO_options);
    echo '</div>';
    echo '</div>';

    // SEMESTERS
    if (($_REQUEST['mp_term'] == 'FY' && $_REQUEST['marking_period_id'] != 'new') || $_REQUEST['mp_term'] == 'SEM' || $_REQUEST['mp_term'] == 'QTR' || $_REQUEST['mp_term'] == 'PRO') {
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND YEAR_ID=\'' . $_REQUEST['year_id'] . '\' ORDER BY SORT_ORDER';
        $QI = DBQuery($sql);
        $sem_RET = DBGet($QI);

        if (is_countable($sem_RET) && count($sem_RET)) {
            if ($_REQUEST['mp_term']) {
                if ($_REQUEST['mp_term'] == 'SEM')
                    $_REQUEST['semester_id'] = $_REQUEST['marking_period_id'];

                foreach ($sem_RET as $key => $value) {
                    if ($value['MARKING_PERIOD_ID'] == $_REQUEST['semester_id'])
                        $sem_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }

        echo '<div class="col-md-4">';
        echo '<div class="panel panel-white">';
        $columns = array('TITLE' => _semester);
        $link = array();

        $sem_edate = DBGet(DBQuery('SELECT MAX(END_DATE) AS END_DATE,MIN(START_DATE) as START_DATE FROM school_semesters WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
        $sem_sdate = $sem_edate[1]['START_DATE'];
        $sem_edate = $sem_edate[1]['END_DATE'];
        $fy_edate = DBGet(DBQuery('SELECT END_DATE,START_DATE FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
        $fy_sdate = $fy_edate[1]['START_DATE'];
        $fy_edate = $fy_edate[1]['END_DATE'];


        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&mp_term=SEM&year_id=$_REQUEST[year_id]"; //." onclick='grabA(this); return false;'";
        $link['TITLE']['variables'] = array('marking_period_id' => 'MARKING_PERIOD_ID');
        if ($sem_edate == '' || $sem_edate < $fy_edate || $sem_sdate != $fy_sdate) {
            $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&mp_term=SEM&marking_period_id=new&year_id=$_REQUEST[year_id]";
        } else {
            $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&mp_term=SEM&marking_period_id=new&year_id=$_REQUEST[year_id]";
        }
        ListOutput($sem_RET, $columns,  _semester, _semesters, $link, array(), $LO_options);
        echo '</div>';
        echo '</div>';

        // QUARTERS
        if (($_REQUEST['mp_term'] == 'SEM' && $_REQUEST['marking_period_id'] != 'new') || $_REQUEST['mp_term'] == 'QTR' || $_REQUEST['mp_term'] == 'PRO') {
            $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND SEMESTER_ID=\'' . $_REQUEST['semester_id'] . '\' ORDER BY SORT_ORDER';
            $QI = DBQuery($sql);
            $qtr_RET = DBGet($QI);

            if (is_countable($qtr_RET) && count($qtr_RET)) {
                if (($_REQUEST['mp_term'] == 'QTR' && $_REQUEST['marking_period_id'] != 'new') || $_REQUEST['mp_term'] == 'PRO') {
                    if ($_REQUEST['mp_term'] == 'QTR')
                        $_REQUEST['quarter_id'] = $_REQUEST['marking_period_id'];

                    foreach ($qtr_RET as $key => $value) {
                        if ($value['MARKING_PERIOD_ID'] == $_REQUEST['quarter_id'])
                            $qtr_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                    }
                }
            }

            echo '<div class="col-md-4">';
            echo '<div class="panel panel-white">';
            $columns = array('TITLE' => _quarter);
            $link = array();
            $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&mp_term=QTR&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]";
            $link['TITLE']['variables'] = array('marking_period_id' => 'MARKING_PERIOD_ID');

            $sem_edate = DBGet(DBQuery('SELECT END_DATE,START_DATE FROM school_semesters WHERE MARKING_PERIOD_ID=' . $_REQUEST['semester_id']));
            $sem_sdate = $sem_edate[1]['START_DATE'];
            $sem_edate = $sem_edate[1]['END_DATE'];
            $qtr_edate = DBGet(DBQuery('SELECT MAX(END_DATE) AS END_DATE,MIN(START_DATE) as START_DATE FROM school_quarters WHERE SEMESTER_ID=' . $_REQUEST['semester_id']));
            $qtr_sdate = $qtr_edate[1]['START_DATE'];
            $qtr_edate = $qtr_edate[1]['END_DATE'];
            if ($qtr_edate == '' || $qtr_edate < $sem_edate || $sem_sdate != $qtr_sdate)
                $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&mp_term=QTR&marking_period_id=new&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]";

            ListOutput($qtr_RET, $columns,  _quarter, _quarters, $link, array(), $LO_options);
            echo '</div>';
            echo '</div>';

            // PROGRESS PERIODS
            if (($_REQUEST['mp_term'] == 'QTR' && $_REQUEST['marking_period_id'] != 'new') || $_REQUEST['mp_term'] == 'PRO') {
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_progress_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND QUARTER_ID=\'' . $_REQUEST['quarter_id'] . '\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $pro_RET = DBGet($QI);

                if (is_countable($pro_RET) && count($pro_RET)) {
                    if (($_REQUEST['mp_term'] == 'PRO' && $_REQUEST['marking_period_id'] != 'new')) {
                        $_REQUEST['progress_period_id'] = $_REQUEST['marking_period_id'];

                        foreach ($pro_RET as $key => $value) {
                            if ($value['MARKING_PERIOD_ID'] == $_REQUEST['marking_period_id'])
                                $pro_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                        }
                    }
                }

                //echo '<TD valign=top>';
                $columns = array('TITLE' => _progressPeriod);
                $link = array();
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&mp_term=PRO&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]&quarter_id=$_REQUEST[quarter_id]";
                $link['TITLE']['variables'] = array('marking_period_id' => 'MARKING_PERIOD_ID');
                $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&mp_term=PRO&marking_period_id=new&year_id=$_REQUEST[year_id]&semester_id=$_REQUEST[semester_id]&quarter_id=$_REQUEST[quarter_id]";
                $sql_mp_id = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_progress_periods';
            }
        }
    }

    echo '</div>';
}
