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
include '../../RedirectModulesInc.php';
include 'lang/language.php';

if ($_REQUEST['month_date'] && $_REQUEST['day_date'] && $_REQUEST['year_date']) {
    $name = $_REQUEST['year_date'] . '-' . $_REQUEST['month_date'] . '-' . $_REQUEST['day_date'];
    $date = date('Y-m-d', strtotime($name));
} else {
    $date = DBDate('mysql');
    $_REQUEST['day_date'] = date('d');
    $_REQUEST['month_date'] = strtoupper(date('M'));
    $_REQUEST['year_date'] = date('y');
}
if ($_REQUEST['modname'] == 'scheduling/Scheduler.php' && !$_REQUEST['run']) {

    $function = 'Prompt_Home_Schedule';
    DrawBC("" . _scheduling . " > " . ProgramTitle());
} else {
    $function = '_returnTrue';
}

if ($function('' . _confirmSchedulerRun . '', '' . _confirmSchedulerRun . '', '
    <div class="form-group"><div class="checkbox checkbox-switch switch-xs switch-success"><label><INPUT type=checkbox name=test_mode   value=Y onclick=showhidediv("div1",this);><span></span>' . _scheduleUnscheduledRequests . '</label></div>
    <div id=div1 class="text-center" style=display:none><label>' . _selectDate . '</label><div class="form-inline m-b-20">' . PrepareDateSchedule($date, '_date', false, '') . '</div></div>
    <div class="form-group"><div class="checkbox checkbox-switch switch-xs switch-success"><label><INPUT type=checkbox name=delete_mode value=Y><span></span>' . _deleteCurrentSchedules . '</label></div></div>')) {

    PopTable('header', '' . _schedulerProgress . '');
    echo '<CENTER><TABLE cellpadding=0 cellspacing=0><TR><TD><TABLE cellspacing=0 border=0><TR>';
    for ($i = 1; $i <= 100; $i++) {
        echo '<TD id=cell' . $i . ' width=3 ></TD>';
    }

    echo '</TR></TABLE></TD></TR></TABLE><BR><DIV id=percentDIV><IMG SRC=assets/spinning.gif> ' . _processingRequests . ' ... </DIV></CENTER>';
    PopTable('footer');
    ob_flush();
    flush();
    ini_set('MAX_EXECUTION_TIME', 0);
    // get the fy marking period id, there should be exactly one fy marking period
    $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
    $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

    $sql = 'SELECT r.REQUEST_ID,r.STUDENT_ID,s.GENDER as GENDER,r.SUBJECT_ID,r.COURSE_ID,MARKING_PERIOD_ID,WITH_TEACHER_ID,NOT_TEACHER_ID,WITH_PERIOD_ID,NOT_PERIOD_ID,(SELECT COUNT(*) FROM course_periods cp2 WHERE cp2.COURSE_ID=r.COURSE_ID) AS SECTIONS
	FROM schedule_requests r,students s,student_enrollment ssm
	WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=r.SYEAR
	AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL)
	AND s.STUDENT_ID=r.STUDENT_ID AND r.SYEAR=\'' . UserSyear() . '\' AND r.SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SECTIONS';

    $requests_RET = DBGet(DBQuery($sql), array(), array('REQUEST_ID'));
    if ($_REQUEST['delete_mode'] == 'Y') {
        $not_delete = DBGet(DBQuery('SELECT DISTINCT SC.ID AS NOT_DEL FROM schedule SC,attendance_period AP WHERE (SC.STUDENT_ID=AP.STUDENT_ID AND SC.COURSE_PERIOD_ID=AP.COURSE_PERIOD_ID AND SC.SCHOOL_ID=\'' . UserSchool() . '\' AND SC.SYEAR=\'' . UserSyear() . '\') UNION SELECT DISTINCT SC.ID AS NOT_DEL FROM schedule SC,gradebook_grades SRCG WHERE (SC.STUDENT_ID=SRCG.STUDENT_ID AND SC.COURSE_PERIOD_ID=SRCG.COURSE_PERIOD_ID AND SC.SCHOOL_ID=\'' . UserSchool() . '\' AND SC.SYEAR=\'' . UserSyear() . '\')'));
        $notin = '';
        foreach ($not_delete as $value) {
            $notin .= $value['NOT_DEL'] . ",";
        }
        if ($notin != '') {
            $notin = substr($notin, 0, -1);

            DBQuery('DELETE FROM schedule WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND (SCHEDULER_LOCK!=\'Y\' OR SCHEDULER_LOCK IS NULL OR SCHEDULER_LOCK=\'\') AND ID NOT IN (' . $notin . ')');
        } else {

            DBQuery('DELETE FROM schedule WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND (SCHEDULER_LOCK!=\'' . 'Y' . '\' OR SCHEDULER_LOCK IS NULL OR SCHEDULER_LOCK=\'' . '' . '\')');
        }

        // FIX THIS
    }

    if ($_REQUEST['test_mode'] == 'Y') {

        $schedule = array();
        // $s_date = $_REQUEST['year__date'] . '-' . MonthFormatter($_REQUEST['month__date']) . '-' . $_REQUEST['day__date'];
        $s_date = $_REQUEST['year__date'] . '-' . $_REQUEST['month__date'] . '-' . $_REQUEST['day__date'];
        $seats_availabe = array();
        foreach ($requests_RET as $rid => $rd) {
            $parent_mps = array();
            $parent_mpq = DBGet(DBQuery('SELECT PARENT_ID,GRANDPARENT_ID FROM marking_periods WHERE MARKING_PERIOD_ID=' . $rd[1]['MARKING_PERIOD_ID']));

            if ($parent_mpq[1]['PARENT_ID'] != '-1') {
                $parent_mps[$parent_mpq[1]['PARENT_ID']] = $parent_mpq[1]['PARENT_ID'];
            }
            if ($parent_mpq[1]['GRANDPARENT_ID'] != '-1') {
                $parent_mps[$parent_mpq[1]['GRANDPARENT_ID']] = $parent_mpq[1]['GRANDPARENT_ID'];
            }
            $parent_mps[$rd[1]['MARKING_PERIOD_ID']] = $rd[1]['MARKING_PERIOD_ID'];
            $parent_mps = implode(',', $parent_mps);
            $cps_main = array();
            $cps = DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_ID=' . $rd[1]['COURSE_ID'] . ' AND (TOTAL_SEATS - FILLED_SEATS)>0 AND (MARKING_PERIOD_ID IN (' . $parent_mps . ') OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<=\'' . $s_date . '\' AND END_DATE>=\'' . $s_date . '\'))' . ($rd[1]['WITH_TEACHER_ID'] != '' ? ' AND (TEACHER_ID=' . $rd[1]['WITH_TEACHER_ID'] . ' OR SECONDARY_TEACHER_ID=' . $rd[1]['WITH_TEACHER_ID'] . ')  ' : '') . ($rd[1]['NOT_TEACHER_ID'] != 0 ? ' AND TEACHER_ID!=' . $rd[1]['NOT_TEACHER_ID'] . ' AND SECONDARY_TEACHER_ID!=' . $rd[1]['NOT_TEACHER_ID'] . '' : '')));

            echo '<br/><br/>';
            foreach ($cps as $ci => $cd) {
                if ($seats_availabe[$cd['COURSE_PERIOD_ID']] == '') {
                    $seats_availabe[$cd['COURSE_PERIOD_ID']] = $cd['TOTAL_SEATS'] - $cd['FILLED_SEATS'];
                }
                $total_p = DBGet(DBQuery('SELECT COUNT(1) as TOTAL_P FROM course_period_var WHERE COURSE_PERIOD_ID=' . $cd['COURSE_PERIOD_ID']));
                $total_p = $total_p[1]['TOTAL_P'];

                if ($rd[1]['WITH_PERIOD_ID'] != '' && $rd[1]['NOT_PERIOD_ID'] != '') {
                    $get_periods = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM course_period_var WHERE PERIOD_ID=' . $rd[1]['WITH_PERIOD_ID'] . ' AND PERIOD_ID!=' . $rd[1]['NOT_PERIOD_ID'] . ' AND COURSE_PERIOD_ID=' . $cd['COURSE_PERIOD_ID']));
                    if ($cd['GENDER_RESTRICTION'] == 'N' && $total_p == $get_periods[1]['REC_EX']) {
                        $cps_main[] = $cd;
                    } elseif ($rd[1]['GENDER'] != '' && $cd['GENDER_RESTRICTION'] == substr($rd[1]['GENDER'], 0, 1) && $total_p == $get_periods[1]['REC_EX']) {
                        $cps_main[] = $cd;
                    }

                } elseif ($rd[1]['WITH_PERIOD_ID'] == '' && $rd[1]['NOT_PERIOD_ID'] != '') {
                    $get_periods = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM course_period_var WHERE  PERIOD_ID!=' . $rd[1]['NOT_PERIOD_ID'] . ' AND COURSE_PERIOD_ID=' . $cd['COURSE_PERIOD_ID']));
                    if ($cd['GENDER_RESTRICTION'] == 'N' && $get_periods[1]['REC_EX'] == 0) {
                        $cps_main[] = $cd;
                    } elseif ($rd[1]['GENDER'] != '' && $cd['GENDER_RESTRICTION'] == substr($rd[1]['GENDER'], 0, 1) && $get_periods[1]['REC_EX'] == 0) {
                        $cps_main[] = $cd;
                    }

                } elseif ($rd[1]['WITH_PERIOD_ID'] != '' && $rd[1]['NOT_PERIOD_ID'] == '') {
                    $get_periods = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM course_period_var WHERE  PERIOD_ID=' . $rd[1]['WITH_PERIOD_ID'] . ' AND COURSE_PERIOD_ID=' . $cd['COURSE_PERIOD_ID']));
                    if ($cd['GENDER_RESTRICTION'] == 'N' && $get_periods[1]['REC_EX'] > 0) {
                        $cps_main[] = $cd;
                    } elseif ($rd[1]['GENDER'] != '' && $cd['GENDER_RESTRICTION'] == substr($rd[1]['GENDER'], 0, 1) && $get_periods[1]['REC_EX'] > 0) {
                        $cps_main[] = $cd;
                    }

                } else {
                    if ($cd['GENDER_RESTRICTION'] == 'N') {
                        $cps_main[] = $cd;
                    } elseif ($rd[1]['GENDER'] != '' && $cd['GENDER_RESTRICTION'] == substr($rd[1]['GENDER'], 0, 1)) {
                        $cps_main[] = $cd;
                    }

                }
            }

            foreach ($cps_main as $cpi => $cpd) {

                $same_cp = DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM schedule WHERE STUDENT_ID=' . $rd[1]['STUDENT_ID'] . ' AND (END_DATE>=\'' . $s_date . '\'  OR END_DATE IS NULL OR END_DATE=\'0000-00-00\') AND COURSE_PERIOD_ID=' . $cpd['COURSE_PERIOD_ID']));

                if ($same_cp[1]['REC_EX'] == 0) {
                    $flag = 0;
                    $student_cp_ids = DBGet(DBQuery('SELECT DISTINCT COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID=' . $rd[1]['STUDENT_ID'] . ' AND (END_DATE>=\'' . $s_date . '\'  OR END_DATE IS NULL OR END_DATE=\'0000-00-00\') '));
                    foreach ($student_cp_ids as $sd) {
                        $get_det = DBGet(DBQuery('SELECT * FROM course_period_var WHERE COURSE_PERIOD_ID=' . $sd['COURSE_PERIOD_ID']));
                        foreach ($get_det as $gi => $gd) {
                            $get_new = DBGet(DBQuery('SELECT * FROM course_period_var WHERE COURSE_PERIOD_ID=' . $cpd['COURSE_PERIOD_ID']));
                            foreach ($get_new as $gni => $gnd) {
                                if ($gd['PERIOD_ID'] == $gnd['PERIOD_ID'] && strpos($gd['DAYS'], $gnd['DAYS']) != '') {
                                    $flag++;
                                }

                                if (strtotime($gd['START_TIME']) == strtotime($gnd['START_TIME']) && strpos($gd['DAYS'], $gnd['DAYS']) != '') {
                                    $flag++;
                                }

                                if (strtotime($gd['END_TIME']) == strtotime($gnd['END_TIME']) && strpos($gd['DAYS'], $gnd['DAYS']) != '') {
                                    $flag++;
                                }

                                if (strtotime($gd['START_TIME']) >= strtotime($gnd['START_TIME']) && strtotime($gd['START_TIME']) <= strtotime($gnd['END_TIME']) && strpos($gd['DAYS'], $gnd['DAYS']) != '') {
                                    $flag++;
                                }

                                if (strtotime($gd['END_TIME']) >= strtotime($gnd['START_TIME']) && strtotime($gd['END_TIME']) <= strtotime($gnd['END_TIME']) && strpos($gd['DAYS'], $gnd['DAYS']) != '') {
                                    $flag++;
                                }

                            }
                        }
                    }
                    if ($flag == 0 && $seats_availabe[$cpd['COURSE_PERIOD_ID']] > 0) {
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['COURSE_PERIOD_ID'] = $cpd['COURSE_PERIOD_ID'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['COURSE_ID'] = $cpd['COURSE_ID'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['MP'] = $cpd['MP'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['MARKING_PERIOD_ID'] = $cpd['MARKING_PERIOD_ID'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['CALENDAR_ID'] = $cpd['CALENDAR_ID'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['TEACHER_ID'] = $cpd['TEACHER_ID'];
                        $schedule[$rd[1]['STUDENT_ID'] . '-' . $cpd['COURSE_PERIOD_ID']]['REQUEST_ID'] = $rd[1]['REQUEST_ID'];
                        $seats_availabe[$cpd['COURSE_PERIOD_ID']] = $cpd['TOTAL_SEATS'] - ($cpd['FILLED_SEATS'] + 1);
                        break 1;
                    }
                }
            }
        }

        foreach ($schedule as $stu_id_cp => $cp_id) {
            $stu_cp_arr = explode("-", $stu_id_cp);
            $stu_id = $stu_cp_arr[0];
            $cp_end_dt = DBGet(DBQuery('SELECT end_date FROM  course_periods WHERE COURSE_PERIOD_ID=' . $cp_id['COURSE_PERIOD_ID']));

            DBQuery('INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,START_DATE,END_DATE,MODIFIED_BY,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID,DROPPED) VALUES (' . UserSyear() . ',' . UserSchool() . ',' . $stu_id . ',\'' . $s_date . '\',\'' . $cp_end_dt[1]['END_DATE'] . '\',' . UserID() . ',\'' . $cp_id['COURSE_ID'] . '\',\'' . $cp_id['COURSE_PERIOD_ID'] . '\',\'' . ($cp_id['MARKING_PERIOD_ID'] != '' ? $cp_id['MP'] : 'FY') . '\',\'' . ($cp_id['MARKING_PERIOD_ID'] != '' ? $cp_id['MARKING_PERIOD_ID'] : GetMPId('FY')) . '\',\'N\')');
            DBQuery('DELETE FROM schedule_requests WHERE REQUEST_ID=' . $cp_id['REQUEST_ID']);
            if (strtotime($s_date) <= strtotime(date('Y-m-d'))) {
                $check_d_att = DBGet(DBQuery('SELECT * FROM course_period_var WHERE COURSE_PERIOD_ID=' . $cp_id['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\''));
                if (count($check_d_att) > 0) {
                    for ($j = strtotime($s_date); $j < strtotime(date('Y-m-d')); $j = $j + 86400) {
                        $chk_date = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM attendance_calendar WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_DATE=\'' . date('Y-m-d', $j) . '\' AND CALENDAR_ID=' . $cp_id['CALENDAR_ID']));
                        if ($chk_date[1]['REC_EX'] != 0) {
                            foreach ($check_d_att as $catt) {
                                $cpsMarkingPeriod = DBGet(DBQuery("select marking_period_id from course_periods where course_period_id = $cp_id[COURSE_PERIOD_ID];"))[1]['MARKING_PERIOD_ID'];
                                if (isDateInMarkingPeriodWorkingDates($cpsMarkingPeriod, $dates_all)) {
                                    DBQuery('INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID) VALUES (' . UserSchool() . ',' . UserSyear() . ',' . date('Y-m-d', $j) . ',' . $cp_id['COURSE_PERIOD_ID'] . ',' . $catt['PERIOD_ID'] . ',' . $cp_id['TEACHER_ID'] . ')');
                                }
                            }
                        }
                    }
                }
            }

            unset($cp_end_dt);
        }

        echo '<script language="javascript">' . "\r";
        echo 'addHTML("<IMG SRC=assets/spinning.gif> ' . _savingSchedules . ' ... ","percentDIV",true);' . "\r";
        echo '</script>';
        echo str_pad(' ', 4096);
        ob_flush();
        flush();
        $connection = db_start();
    }
    if ($_REQUEST['test_mode'] != 'Y' || $_REQUEST['delete_mode'] == 'Y') {
        echo '<script language="javascript">' . "\r";
        echo 'addHTML("<IMG SRC=assets/spinning.gif> ' . _optimizing . ' ... ","percentDIV",true);' . "\r";
        echo '</script>';
        echo str_pad(' ', 4096);
        ob_flush();
        flush();
    }

    $check_request = DBGet(DBQuery("SELECT REQUEST_ID FROM schedule_requests WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "'"));
    $check_request = $check_request[1]['REQUEST_ID'];
    if ($check_request > 0) {
        $warn = _followingStudentsCannotBeAccommodatedAsNoMoreSeatsAvailableOrPeriodsConflict;
    }
   

    if ($_REQUEST['delete_mode'] == 'Y' || $check_request == 0) {
        echo '<script language="javascript">' . "\r";
        echo 'addHTML("<IMG SRC=assets/check.gif> <B>' . _done . '.</B>","percentDIV",true);' . "\r";
        echo '</script>';
        ob_end_flush();
    } elseif ($warn) {
        echo '<script language="javascript">' . "\r";
        echo 'addHTML("<B><font color=red>' . _warning . '</font><br>' . $warn . '</B>","percentDIV",true);' . "\r";
        echo '</script>';
        ob_end_flush();
    } else {
        echo '<script language="javascript">' . "\r";
        echo 'addHTML("<B><font color=red>' . _error . '</font><br>' . _error . '</B>","percentDIV",true);' . "\r";
        echo '</script>';
        ob_end_flush();
    }
    $_REQUEST['modname'] = 'scheduling/UnfilledRequests.php';
    $_REQUEST['search_modfunc'] = 'list';
    include 'modules/scheduling/UnfilledRequests.php';
}

function _scheduleRequest($request, $not_parent_id = false)
{
    global $requests_RET, $cp_parent_RET, $cp_course_RET, $mps_RET, $schedule, $filled, $unfilled;
    $possible = array();
    if (count($cp_course_RET[$request['COURSE_ID']])) {
        foreach ($cp_course_RET[$request['COURSE_ID']] as $course_period) {
            foreach ($cp_parent_RET[$course_period['COURSE_PERIOD_ID']] as $slice) {
                // ALREADY SCHEDULED HERE
                if ($slice['PARENT_ID'] == $not_parent_id) {
                    continue 2;
                }

                // NO SEATS
                if ($slice['AVAILABLE_SEATS'] <= 0) {
                    continue 2;
                }
                // SLICE VIOLATES GENDER RESTRICTION
                if ($slice['GENDER_RESTRICTION'] != 'N' && $slice['GENDER_RESTRICTION'] != substr($request['GENDER'], 0, 1)) {
                    continue 2;
                }
                // PARENT VIOLATES TEACHER / PERIOD REQUESTS
                if ($slice['PARENT_ID'] == $slice['COURSE_PERIOD_ID'] && (($request['WITH_TEACHER_ID'] != '' && $slice['TEACHER_ID'] != $request['WITH_TEACHER_ID']) || ($request['WITH_PERIOD_ID'] && $slice['PERIOD_ID'] != $request['WITH_PERIOD_ID']) || ($request['NOT_TEACHER_ID'] && $slice['TEACHER_ID'] == $request['NOT_TEACHER_ID']) || ($request['NOT_PERIOD_ID'] && $slice['PERIOD_ID'] == $request['NOT_PERIOD_ID']))) {
                    continue 2;
                }
                if (count($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']])) {
                    // SHOULD LOOK FOR COMPATIBLE CP's IF NOT THE COMPLETE WEEK/YEAR
                    foreach ($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']] as $existing_slice) {
                        if ($existing_slice['PARENT_ID'] != $not_parent_id && _isConflict($existing_slice, $slice)) {
                            continue 3;
                        }
                    }
                }
            }
            // No conflict

            $possible[] = $course_period;
        }
    }
    if (count($possible)) {
        // IF THIS COURSE IS BEING SCHEDULED A SECOND TIME, DELETE THE ORIGINAL ONE
        if ($not_parent_id) {
            foreach ($cp_parent_RET[$not_parent_id] as $key => $slice) {
                foreach ($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']] as $key2 => $item) {
                    if ($item['COURSE_PERIOD_ID'] == $slice['COURSE_PERIOD_ID']) {
                        $filled[$schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']][$key2]['REQUEST_ID']] = false;
                        unset($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']][$key2]);
                        $cp_parent_RET[$not_parent_id][$key]['AVAILABLE_SEATS']++;
                    }
                }
            }
        }
        // CHOOSE THE BEST CP
        _scheduleBest($request, $possible);
        return true;
    } else {
        return false;
    }
    // if this point is reached, the request could not be scheduled
}

function _moveRequest($request, $not_request = false, $not_parent_id = false)
{
    global $requests_RET, $cp_parent_RET, $cp_course_RET, $mps_RET, $schedule, $filled, $unfilled;
    if (!$not_request && !is_array($not_request)) {
        $not_request = array();
    }

    if (count($cp_course_RET[$request['COURSE_ID']])) {
        foreach ($cp_course_RET[$request['COURSE_ID']] as $course_period) {
            // CLEAR OUT A SLOT FOR EACH $slice
            foreach ($cp_parent_RET[$course_period['PARENT_ID']] as $slice) {
                /* Don't bother to move courses around if request can't be scheduled here anyway. */
                // SEAT COUNTS
                if ($slice['AVAILABLE_SEATS'] <= 0) {
                    continue 2;
                }

                // SLICE VIOLATES GENDER RESTRICTION
                if ($slice['GENDER_RESTRICTION'] != 'N' && $slice['GENDER_RESTRICTION'] != substr($request['GENDER'], 0, 1)) {
                    continue 2;
                }

                // PARENT VIOLATES TEACHER / PERIOD REQUESTS
                if ($slice['PARENT_ID'] == $slice['COURSE_PERIOD_ID'] && (($request['WITH_TEACHER_ID'] != '' && $slice['TEACHER_ID'] != $request['WITH_TEACHER_ID']) || ($request['WITH_PERIOD_ID'] && $slice['PERIOD_ID'] != $request['WITH_PERIOD_ID']) || ($request['NOT_TEACHER_ID'] && $slice['TEACHER_ID'] == $request['NOT_TEACHER_ID']) || ($request['NOT_PERIOD_ID'] && $slice['PERIOD_ID'] == $request['NOT_PERIOD_ID']))) {
                    continue 2;
                }

                if (count($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']])) {
                    foreach ($schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']] as $existing_slice) {
                        if (in_array($existing_slice['REQUEST_ID'], $not_request)) {
                            continue 3;
                        }

                        if (true) {
                            $not_request_temp = $not_request;
                            $not_request_temp[] = $existing_slice['REQUEST_ID'];
                            if (!$scheduled = _scheduleRequest($requests_RET[$existing_slice['REQUEST_ID']][1], $existing_slice['PARENT_ID'])) {
                                if (!$moved = _moveRequest($requests_RET[$existing_slice['REQUEST_ID']][1], $not_request_temp, $existing_slice['PARENT_ID'])) {
                                    continue 3;
                                }

                            }
                        }
                    }
                } else {
                    // WTF???
                }
            }
            if (_scheduleRequest($request, $not_parent_id)) {
                return true;
            }

        }
    }
    return false; // if this point is reached, the request could not be scheduled
}

function _isConflict($existing_slice, $slice)
{
    global $requests_RET, $cp_parent_RET, $cp_course_RET, $mps_RET, $schedule, $filled, $unfilled, $fy_id;

    return false; // There is no conflict
}

function _scheduleBest($request, $possible)
{
    global $cp_parent_RET, $schedule, $filled;
    $best = $possible[0];
    if (count($possible) > 1) {
        foreach ($possible as $course_period) {
            if ($cp_parent_RET[$course_period['COURSE_PERIOD_ID']][1]['AVAILABLE_SEATS'] > $cp_parent_RET[$best['COURSE_PERIOD_ID']][1]['AVAILABLE_SEATS']) {
                $best = $course_period;
            }
        }
    }
    foreach ($cp_parent_RET[$best['COURSE_PERIOD_ID']] as $key => $slice) {
        $schedule[$request['STUDENT_ID']][$slice['PERIOD_ID']][] = $slice + array('REQUEST_ID' => $request['REQUEST_ID']);
        $cp_parent_RET[$best['COURSE_PERIOD_ID']][$key]['AVAILABLE_SEATS']--;
    }
}

function _returnTrue($arg1, $arg2 = '', $arg3 = '')
{
    return true;
}

function Prompt_Home_Schedule($title = 'Confirm', $question = '', $message = '', $pdf = '')
{
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true) {
        $tmp_REQUEST['_openSIS_PDF'] = true;
    }

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        PopTable('header', $title);

        echo "<CENTER><h4>$question</h4><FORM name=run_schedule action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST onSubmit='return confirmAction();'>$message<INPUT type=submit class=\"btn btn-primary\" value=" . _ok . "> &nbsp; <INPUT type=button class=\"btn btn-default\" name=delete_cancel value=" . _cancel . " onclick='window.location=\"Modules.php?modname=miscellaneous/Portal.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else {
        return true;
    }

}

function get_min($time)
{
    $org_tm = $time;
    $stage = substr($org_tm, -2);
    $main_tm = substr($org_tm, 0, 5);
    $main_tm = trim($main_tm);
    $sp_time = split(':', $main_tm);
    $hr = $sp_time[0];
    $min = $sp_time[1];
    if ($hr == 12) {
        $hr = $hr;
    } else {
        if ($stage == 'AM') {
            $hr = $hr;
        }

        if ($stage == 'PM') {
            $hr = $hr + 12;
        }

    }

    $time_min = (($hr * 60) + $min);
    return $time_min;
}

function con_date($date)
{
    $mother_date = $date;
    $year = substr($mother_date, 7, 4);
    $temp_month = substr($mother_date, 3, 3);

    if ($temp_month == 'JAN') {
        $month = '-01-';
    } elseif ($temp_month == 'FEB') {
        $month = '-02-';
    } elseif ($temp_month == 'MAR') {
        $month = '-03-';
    } elseif ($temp_month == 'APR') {
        $month = '-04-';
    } elseif ($temp_month == 'MAY') {
        $month = '-05-';
    } elseif ($temp_month == 'JUN') {
        $month = '-06-';
    } elseif ($temp_month == 'JUL') {
        $month = '-07-';
    } elseif ($temp_month == 'AUG') {
        $month = '-08-';
    } elseif ($temp_month == 'SEP') {
        $month = '-09-';
    } elseif ($temp_month == 'OCT') {
        $month = '-10-';
    } elseif ($temp_month == 'NOV') {
        $month = '-11-';
    } elseif ($temp_month == 'DEC') {
        $month = '-12-';
    }

    $day = substr($mother_date, 0, 2);

    $select_date = $year . $month . $day;
    return $select_date;
}
