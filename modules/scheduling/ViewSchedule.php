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
DrawBC("Scheduling > " . ProgramTitle());
unset($sql);

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '<div class="col-lg-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('request');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

if (!$_SESSION['student_id']) {
    Search('student_id', $extra);
}
if (isset($_REQUEST['student_id'])) {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));
    $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students'));
    if ($count_student_RET[1]['NUM'] > 1) {
        DrawHeaderHome('Selected Student: ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . ' (<A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . '><font color=red>Deselect</font></A>) | <A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=students/Student.php&ajax=true&bottom_back=true&return_session=true target=body>Back to Student List</A>');
    } else if ($count_student_RET[1]['NUM'] == 1) {
        DrawHeaderHome('Selected Student: ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . ' (<A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . '><font color=red>Deselect</font></A>) ');
    }
}
if ($_REQUEST['month__date'] && $_REQUEST['day__date'] && $_REQUEST['year__date']) {
    $month_names = array('JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04', 'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12');
    if (array_key_exists($_REQUEST['month__date'], $month_names))
        $date = $_REQUEST['year__date'] . '-' . $month_names[$_REQUEST['month__date']] . '-' . $_REQUEST['day__date'];
    elseif (in_array($_REQUEST['month__date'], $month_names))
        $date = $_REQUEST['year__date'] . '-' . $_REQUEST['month__date'] . '-' . $_REQUEST['day__date'];
    else
        $date = date('Y-m-d');
}

else {
    $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
    if ($min_date[1]['MIN_DATE'] && DBDate('postgres') < $min_date[1]['MIN_DATE']) {
        $date = $min_date[1]['MIN_DATE'];
        $_REQUEST['day__date'] = date('d', strtotime($date));
        $_REQUEST['month__date'] = strtoupper(date('m', strtotime($date)));
        $_REQUEST['year__date'] = date('Y', strtotime($date));
        $first_visit = 'yes';
    } else {

        $_REQUEST['day__date'] = date('d');

        $_REQUEST['month__date'] = date('m');
        $_REQUEST['year__date'] = date('Y');

        $date = $_REQUEST['year__date'] . '-' . $_REQUEST['month__date'] . '-' . $_REQUEST['day__date'];
        $first_visit = 'yes';
    }
}

if ($_REQUEST['month_schedule'] && ($_POST['month_schedule'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['month_schedule'] as $id => $start_dates)
        foreach ($start_dates as $start_date => $columns) {
            foreach ($columns as $column => $value) {
                $_REQUEST['schedule'][$id][$start_date][$column] = $_REQUEST['day_schedule'][$id][$start_date][$column] . '-' . $value . '-' . $_REQUEST['year_schedule'][$id][$start_date][$column];
                if ($_REQUEST['schedule'][$id][$start_date][$column] == '--') {
                    $_REQUEST['schedule'][$id][$start_date][$column] = '';
                }
            }
        }
    unset($_REQUEST['month_schedule']);
    unset($_REQUEST['day_schedule']);
    unset($_REQUEST['year_schedule']);
    unset($_SESSION['_REQUEST_vars']['month_schedule']);
    unset($_SESSION['_REQUEST_vars']['day_schedule']);
    unset($_SESSION['_REQUEST_vars']['year_schedule']);
    $_POST['schedule'] = $_REQUEST['schedule'];
}

if (UserStudentID()) {
    echo "<FORM name=modify class=no-padding id=modify action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " &modfunc=modify METHOD=POST>";

    $tmp_REQUEST = $_REQUEST;

    if (clean_param($_REQUEST['marking_period_id'], PARAM_INT)) {
        $mp_id = $_REQUEST['marking_period_id'];
    }

    if (!isset($_REQUEST['marking_period_id'])) {
        $mp_id = UserMP();
        $_REQUEST['marking_period_id'] = $mp_id;
    }
    if ($_REQUEST['modfunc'] != 'detail') {
        if (!isset($_REQUEST['view_mode'])) {
            $_REQUEST['view_mode'] = 'day_view';
        }
    }
    ##################################################################

    $mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,1 AS TBL FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,2 AS TBL FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,3 AS TBL FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY TBL,SORT_ORDER'));

    $mp = CreateSelect($mp_RET, 'marking_period_id', 'Modules.php?modname=' . $_REQUEST['modname'] . '&view_mode=' . $_REQUEST['view_mode'] . '&marking_period_id=', $_REQUEST['marking_period_id']);

    $view_mode = create_view_mode('Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=' . $_REQUEST['marking_period_id'] . '&view_mode=');
    ###################################################################3
    $mp_id1 = $_REQUEST['marking_period_id'];

    echo '<div class="panel panel-default">';
    switch ($_REQUEST['view_mode']) {
        case 'day_view':
            if ((User('PROFILE_ID') == 3 || User('PROFILE_ID') == 4) && $date == '')
                $date = date('Y-m-d');
            $mp_sql = 'SELECT MARKING_PERIOD_ID,START_DATE,END_DATE FROM marking_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND \'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN START_DATE AND END_DATE';
            $mp_data = DBGet(DBQuery($mp_sql));
            if (count($mp_data) == 0) {
                $mp_sql = 'SELECT MARKING_PERIOD_ID,START_DATE,END_DATE FROM marking_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'';
                $mp_data = DBGet(DBQuery($mp_sql));
            }
            DrawHeader('<div class="form-inline"><div class="input-group">' . PrepareDateSchedule($date, '_date', false, array('submit' => true)) . '<span class="input-group-btn"><INPUT type=submit class="btn btn-primary" value=Go></span></div></div>', '<div class="form-inline"><div class="input-group"><span class="input-group-addon" id="marking_period_id">Marking Period :</span>' . $mp . '</div><div class="input-group"><span class="input-group-addon" id="view_mode">Calendar View :</span>' . $view_mode . '</div></div>');
            echo '<hr class="no-margin"/>';
            $full_day = date('l', strtotime($date));
            $day = get_db_day($full_day);
            $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];



            for ($i = 1; $i <= count($mp_data); $i++) {
                $mp_ids_arr[] = $mp_data[$i]['MARKING_PERIOD_ID'];
            }
            $sql = 'SELECT
        s.COURSE_ID,
        s.COURSE_PERIOD_ID,
        s.MARKING_PERIOD_ID,
        s.START_DATE,
        s.END_DATE,
        UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,
        UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,
        sp.PERIOD_ID,CONCAT(sp.START_TIME,\'' . ' - ' . '\',sp.END_TIME) AS TIME_PERIOD,
        cpv.PERIOD_ID,
        cp.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,
        cp.MP,
        sp.SORT_ORDER,
        c.TITLE,
        cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
        s.STUDENT_ID,
        r.TITLE AS ROOM,
        cpv.DAYS,
        SCHEDULER_LOCK

        FROM schedule s,courses c,course_periods cp,school_periods sp,course_period_var cpv,rooms r

        WHERE s.COURSE_ID = c.COURSE_ID 
        AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
        AND r.ROOM_ID=cpv.ROOM_ID
        AND s.COURSE_ID = cp.COURSE_ID
        AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
        AND s.SCHOOL_ID = sp.SCHOOL_ID 
        AND s.SYEAR = c.SYEAR 
        AND sp.PERIOD_ID = cpv.PERIOD_ID
        AND (cp.MARKING_PERIOD_ID IN (' . implode(',', $mp_ids_arr) . ') OR (cp.MARKING_PERIOD_ID IS NULL AND cp.BEGIN_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\' AND cp.END_DATE>=\'' . date('Y-m-d', strtotime($date)) . '\'))
        AND POSITION(\'' . $day . '\' IN cpv.days)>0
        AND s.STUDENT_ID=\'' . UserStudentID() . '\'
        AND s.SYEAR=\'' . UserSyear() . '\' 
        AND s.SCHOOL_ID = \'' . UserSchool() . '\' 
        AND (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL)
        AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN cp.BEGIN_DATE AND cp.END_DATE) 
        AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND s.START_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\')) 
        
        AND (s.MARKING_PERIOD_ID IN (' . GetAllMP(GetMPTable(GetMP($mp_id1, 'TABLE')), $mp_id1) . ') OR s.MARKING_PERIOD_ID IS NULL)
GROUP BY cp.COURSE_PERIOD_ID
        ORDER BY sp.SORT_ORDER,s.MARKING_PERIOD_ID';
            $QI = DBQuery($sql);
            $schedule_RET = DBGet($QI, array('TIME_PERIOD' => '_makeTimePeriod', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect'));
            $columns = array('TIME_PERIOD' => 'Period', 'TITLE' => 'Course', 'PERIOD_PULLDOWN' => 'Period - Teacher', 'ROOM' => 'Room', 'DAYS' => 'Days of Week', 'COURSE_MARKING_PERIOD_ID' => 'Term');
            $days_RET = DBGet(DBQuery("SELECT DISTINCT DAYS FROM course_period_var"));
            if (count($days_RET) == 1)
                unset($columns['DAYS']);
            if ($_REQUEST['_openSIS_PDF'])
                unset($columns['SCHEDULER_LOCK']);
            break;

        case 'week_view':
            $cal_RET = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));

            $week_range = _makeWeeks($cal_RET[1]['START_DATE'], $cal_RET[1]['END_DATE'], 'Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=' . $_REQUEST['marking_period_id'] . '&view_mode=' . $_REQUEST['view_mode'] . '&week_range=');
            DrawHeader($week_range, '<div class="form-inline"><div class="input-group"><span class="input-group-addon" id="marking_period_id">Marking Period :</span>' . $mp . '</div><div class="input-group"><span class="input-group-addon" id="view_mode">Calendar View :</span>' . $view_mode . '</div></div>');

            $one_day = 60 * 60 * 24;
            $today = strtotime($_REQUEST['week_range']);
            $week_start = date('Y-m-d', $today);
            $week_end = date('Y-m-d', $today + $one_day * 6);

            $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

            $QI = ($sql);
            $wk_schedule_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,CONCAT(sp.START_TIME,\'' . ' - ' . '\',sp.END_TIME) AS TIME_PERIOD,sp.TITLE FROM school_periods sp WHERE sp.SYEAR=\'' . UserSyear() . '\' AND sp.SCHOOL_ID = \'' . UserSchool() . '\' ORDER BY sp.SORT_ORDER'), array('TIME_PERIOD' => '_makeTimePeriod'));

            $week_RET = DBGet(DBQuery('SELECT acc.SCHOOL_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID,cpv.PERIOD_ID
				FROM attendance_calendar acc
				INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.SCHOOL_ID=acc.SCHOOL_ID
				AND acc.SCHOOL_DATE BETWEEN mp.START_DATE AND mp.END_DATE
				INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID
                                INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
				INNER JOIN school_periods sp ON sp.SYEAR=acc.SYEAR AND sp.SCHOOL_ID=acc.SCHOOL_ID AND sp.PERIOD_ID=cpv.PERIOD_ID
                                                                        INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE) AND acc.SCHOOL_DATE BETWEEN \'' . $week_start . '\' AND \'' . $week_end . '\'
                                                                        AND sch.STUDENT_ID=\'' . UserStudentID() . '\''), array(), array('SCHOOL_DATE', 'PERIOD_ID'));
            $custom_schedule = DBGet(DBQuery('SELECT cp.COURSE_PERIOD_ID FROM course_periods cp,schedule s WHERE cp.MARKING_PERIOD_ID IS NULL AND cp.MARKING_PERIOD_ID IS NULL AND cp.BEGIN_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\' AND cp.END_DATE>=\'' . date('Y-m-d', strtotime($date)) . '\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND s.START_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\')) AND s.STUDENT_ID=' . UserStudentID() . ' AND s.SCHOOL_ID=' . UserSchool()));
            $custom_schedule_cpid = array();
            foreach ($custom_schedule as $csi => $csd)
                $custom_schedule_cpid[] = $csd['COURSE_PERIOD_ID'];
            if (count($custom_schedule_cpid) > 0)
                $custom_schedule_cpid = implode(',', $custom_schedule_cpid);
            $columns = array('TIME_PERIOD' => 'Period');

            $i = 0;
            if (count($week_RET)) {
                foreach ($wk_schedule_RET as $course) {
                    $i++;
                    $schedule_RET[$i]['TIME_PERIOD'] = '<span title="' . $course['TITLE'] . '">' . $course['TIME_PERIOD'] . '</span>';
                    for ($j = $today; $j <= $today + $one_day * 6; $j = $j + $one_day) {
                        if (in_array(date('Y-m-d', $j), $week_RET[date('Y-m-d', $j)][$course['PERIOD_ID']][1])) {
                            $day = date('l', strtotime($week_RET[date('Y-m-d', $j)][$course['PERIOD_ID']][1]['SCHOOL_DATE']));
                            $day_RET = DBGet(DBQuery('SELECT DISTINCT cp.COURSE_PERIOD_ID,cp.TITLE,cpv.DAYS,r.TITLE AS ROOM FROM course_periods cp,course_period_var cpv,rooms r,marking_periods mp,schedule sch WHERE cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID and sch.MARKING_PERIOD_ID IN (' . GetAllMP(GetMPTable(GetMP($mp_id1, 'TABLE')), $mp_id1) . ') AND cp.COURSE_PERIOD_ID=sch.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID AND
                            (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', $j) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL) AND sch.START_DATE<=  \'' . date('Y-m-d', $j) . '\' AND (sch.END_DATE>=\'' . date('Y-m-d', $j) . '\' OR sch.END_DATE IS NULL) AND \'' . date('Y-m-d', $j) . '\' BETWEEN mp.START_DATE AND mp.END_DATE AND  cpv.PERIOD_ID =\'' . $course[PERIOD_ID] . '\'  AND r.ROOM_ID=cpv.ROOM_ID AND sch.STUDENT_ID=\'' . UserStudentID() . '\' AND POSITION(\'' . get_db_day($day) . '\' IN cpv.days)>0'));
                            if (!$day_RET) {
                                if (count($custom_schedule) > 0 && count($custom_schedule_cpid) > 0)
                                    $day_RET_custom = DBGet(DBQuery('SELECT DISTINCT cp.COURSE_PERIOD_ID,cp.TITLE,cpv.DAYS,r.TITLE AS ROOM FROM course_periods cp,course_period_var cpv,rooms r WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID AND
                                (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', $j) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL) AND cpv.PERIOD_ID =\'' . $course[PERIOD_ID] . '\'  AND r.ROOM_ID=cpv.ROOM_ID AND POSITION(\'' . get_db_day($day) . '\' IN cpv.days)>0 AND cp.COURSE_PERIOD_ID IN (' . $custom_schedule_cpid . ')'));

                                if (count($day_RET_custom) > 0)
                                    $schedule_RET[$i][date('y-m-d', $j)] = (count($day_RET) > 1 ? '<font title="Conflict schedule (' . count($day_RET_custom) . ')" color="red">' . $day_RET_custom[1]['TITLE'] . '<br />Room :' . $day_RET_custom[1]['ROOM'] . '</font>' : '<spna title=' . date("l", $j) . '>' . $day_RET_custom[1]['TITLE'] . '<br />Room :' . $day_RET_custom[1]['ROOM'] . '</span>');
                                else
                                    $schedule_RET[$i][date('y-m-d', $j)] = '<div align=center title="Schedule not available">--</div>';
                            } else
                                $schedule_RET[$i][date('y-m-d', $j)] = (count($day_RET) > 1 ? '<font title="Conflict schedule (' . count($day_RET) . ')" color="red">' . $day_RET[1]['TITLE'] . '<br />Room :' . $day_RET[1]['ROOM'] . '</font>' : '<spna title=' . date("l", $j) . '>' . $day_RET[1]['TITLE'] . '<br />Room :' . $day_RET[1]['ROOM'] . '</span>');
                        }
                    }
                }
            }
            for ($i = $today; $i <= $today + $one_day * 6; $i = $i + $one_day)
                $columns[date('y-m-d', $i)] = weekDate(date('Y-m-d', $i)) . ' ' . ShortDate(date('Y-m-d', $i));
            break;

        case 'month_view':
            $month_str = _makeMonths('Modules.php?modname=' . $_REQUEST['modname'] . '&marking_period_id=' . $_REQUEST['marking_period_id'] . '&view_mode=' . $_REQUEST['view_mode'] . '&month=');
            DrawHeader($month_str, '<div class="form-inline"><div class="input-group"><span class="input-group-addon" id="marking_period_id">Marking Period :</span>' . $mp . '</div><div class="input-group"><span class="input-group-addon" id="view_mode">Calendar View :</span>' . $view_mode . '</div></div>');
            //echo '<hr class="no-margin"/>';
            $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

            $month = date('m', $_REQUEST['month']);
            $year = date('Y', $_REQUEST['month']);

//                  ++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $time = mktime(0, 0, 0, $month, 1, $year);
            $last = 31;
            while (!checkdate($month, $last, $year))
                $last--;

            $calendar_RET = DBGet(DBQuery('SELECT SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $time) . '\' AND \'' . date('Y-m-d', mktime(0, 0, 0, $month, $last, $year)) . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''), array(), array('SCHOOL_DATE'));

            $skip = date("N", $time) - 1;

            //echo '<div class="panel-body">';
            echo '<div class="table-responsive">';
            echo "<TABLE class=\"table table-bordered\" style=\"table-layout: fixed;\"><thead><TR align=center>";
            echo "<TD style=\"width: 14.25%;\">Monday</TD><TD style=\"width: 14.25%;\">Tuesday</TD><TD style=\"width: 14.25%;\">Wednesday</TD><TD style=\"width: 14.25%;\">Thursday</TD><TD style=\"width: 14.25%;\">Friday</TD><TD style=\"width: 14.25%;\">Saturday</TD><TD style=\"width: 14.25%;\">Sunday</TD>";
            echo "</TR></thead><tbody><TR>";

            if ($skip) {
                echo "<td colspan=" . $skip . "></td>";
                $return_counter = $skip;
            }
            for ($i = 1; $i <= $last; $i++) {
                $day_time = mktime(0, 0, 0, $month, $i, $year);
                $date = date('Y-m-d', $day_time);

                //------------------------------------------------------------------------------------------------------------------------------------------------------------
                $full_day = date('l', strtotime($date));
                $day = get_db_day($full_day);
                $sql = 'SELECT
				s.COURSE_ID,s.COURSE_PERIOD_ID,
				s.MARKING_PERIOD_ID,s.START_DATE,s.END_DATE,
				UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,sp.PERIOD_ID,CONCAT(sp.START_TIME,\'' . ' - ' . '\',sp.END_TIME) AS TIME_PERIOD,sp.START_TIME,
				cpv.PERIOD_ID,cp.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,cp.MP,sp.SORT_ORDER,
				c.TITLE,cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
				s.STUDENT_ID,r.TITLE AS ROOM,cpv.DAYS,SCHEDULER_LOCK
			FROM schedule s,courses c,course_periods cp,course_period_var cpv,school_periods sp,rooms r
			WHERE
				s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                                AND r.ROOM_ID=cpv.ROOM_ID
				AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
				AND s.SCHOOL_ID = sp.SCHOOL_ID AND s.SYEAR = c.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
                                                                        AND POSITION(\'' . $day . '\' IN cpv.days)>0
				AND s.STUDENT_ID=\'' . UserStudentID() . '\'
				AND s.SYEAR=\'' . UserSyear() . '\' AND s.SCHOOL_ID = \'' . UserSchool() . '\' AND (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL) 
                                                                        AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND s.START_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\'))
                                                                        AND s.MARKING_PERIOD_ID IN (' . GetAllMP(GetMPTable(GetMP($mp_id, 'TABLE')), $mp_id) . ') 
                                                                        ORDER BY sp.SORT_ORDER,s.MARKING_PERIOD_ID';


                $QI = DBQuery($sql);
                $schedule_RET = DBGet($QI, array('TIME_PERIOD' => '_makeTimePeriod'));
                //-------------------------------------------------------------------------------------------------------------------------------------------------------


                if ($calendar_RET[$date][1]['MINUTES']) {
                    $cssclass = "class=calendar-event";
                } else {
                    $cssclass = "class=calendar-holiday";
                }
                echo "<TD $cssclass valign=top><div class=\"text-right\">$i</div>";

                if ($calendar_RET[$date][1]['MINUTES']) {
                    if (count($schedule_RET) > 0) {
                        echo "<ul class=\"list list-square mt-10 no-margin-bottom\">";
                        foreach ($schedule_RET as $cp_link) {
                            $cp_link['START_TIME'] = date("g:i A", strtotime($cp_link[START_TIME]));
                            echo "<li><a class=\"text-primary\" HREF=# title=Details onclick='javascript:window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=detail&date=$date&marking_period_id=$_REQUEST[marking_period_id]&period=$cp_link[PERIOD_ID]\",\"blank\",\"width=600,height=450,scrollbars=1\"); return false;'>" . $cp_link[START_TIME] . ' - ' . $cp_link[TITLE] . "<a></li>";
                        }
                        echo '</ul>';
                    } else
                        echo '<div class="text-muted mt-10">Schedule not available</div>';
                } else
                    echo '<font class=text-danger>Holiday</font>';
                echo "</TD>";
                $return_counter++;

                if ($return_counter % 7 == 0)
                    echo "</TR><TR>";
            }
            echo "</TR></tbody></TABLE>";
            echo '</div>'; //.table-responsive
            //echo '</div>'; //.panel-body
//                  +++++++++++++++++++++++++++++++++++++++++++++++++++++++
            break;
    }
    $date1 = $_REQUEST['year__date'] . "-" . $_REQUEST['month__date'] . "-" . $_REQUEST['day__date'];

    $mp_id = $_REQUEST['marking_period_id'];
    if ($_REQUEST['modfunc'] == 'detail') {


        $date = $_REQUEST['date'];
        $mp_id = $_REQUEST['marking_period_id'];
        $full_day = date('l', strtotime($date));
        $day = get_db_day($full_day);
        $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

        $sql = 'SELECT
				s.COURSE_ID,s.COURSE_PERIOD_ID,
				s.MARKING_PERIOD_ID,s.START_DATE,s.END_DATE,
				UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,sp.PERIOD_ID,CONCAT(sp.START_TIME,\'' . ' - ' . '\',sp.END_TIME) AS TIME_PERIOD,
				cpv.PERIOD_ID,cp.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,cp.MP,sp.SORT_ORDER,
				c.TITLE ,cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
				s.STUDENT_ID,r.TITLE AS ROOM,cpv.DAYS,SCHEDULER_LOCK
			FROM schedule s,courses c,course_periods cp,school_periods sp,course_period_var cpv,rooms r
			WHERE
				s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
				AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
                                AND r.ROOM_ID=cpv.ROOM_ID
				AND s.SCHOOL_ID = sp.SCHOOL_ID AND s.SYEAR = c.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
                                                                        AND (POSITION(\'' . $day . '\' IN cpv.days)>0 or cpv.days IS NULL)
                                                                        AND sp.PERIOD_ID=\'' . $_REQUEST[period] . '\'
				AND s.STUDENT_ID=\'' . UserStudentID() . '\'
				AND s.SYEAR=\'' . UserSyear() . '\' AND s.SCHOOL_ID = \'' . UserSchool() . '\' AND (cpv.COURSE_PERIOD_DATE=\'' . date('Y-m-d', strtotime($date)) . '\' OR cpv.COURSE_PERIOD_DATE IS NULL) 
                                                                        AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND s.START_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\'))
                                                                        AND s.MARKING_PERIOD_ID IN (' . GetAllMP(GetMPTable(GetMP($mp_id, 'TABLE')), $mp_id) . ') 
                                                                        ORDER BY sp.SORT_ORDER,s.MARKING_PERIOD_ID';


        $QI = DBQuery($sql);
        $schedule_RET = DBGet($QI, array('TIME_PERIOD' => '_makeTimePeriod', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect'));

        $columns = array('TIME_PERIOD' => 'Period', 'TITLE' => 'Course', 'PERIOD_PULLDOWN' => 'Period - Teacher', 'ROOM' => 'Room', 'DAYS' => 'Days of Week', 'COURSE_MARKING_PERIOD_ID' => 'Term');
    }
    if ($_REQUEST['view_mode'] != 'month_view') {
        ListOutput($schedule_RET, $columns, 'Course', 'Courses', $link);
        if ($_REQUEST['modfunc'] == 'detail')
            echo '<div class="panel-footer"><input type="button" class="btn btn-primary" value="Close" onclick="window.close();"></div>';
    }

    if ($schedule_RET && $_REQUEST['view_mode'] == 'day_view') {
        echo '<hr class="no-margin"/><div class="panel-body pt-10 pb-10">';
        echo ProgramLinkforExport('scheduling/PrintSchedules.php', '<b><i class="icon-printer4"></i></b>Print Schedule', '&modfunc=save&st_arr[]=' . UserStudentID() . '&mp_id=' . $mp_id . '&include_inactive=' . $_REQUEST['include_inactive'] . '&date1=' . $date1 . '&_openSIS_PDF=true','target="_blank" class="btn btn-success btn-labeled"') . '</div>';
        echo '</div>'; //.panel-body
    }
    echo '</div>'; //.panel.panel-default
    echo '</FORM>';
    unset($_REQUEST['view_mode']);
}

//==============================================Function start============================================

function _makeTitle($value, $column = '') {
    global $_openSIS, $THIS_RET;
    return $value;
}

function _makeLock($value, $column) {
    global $THIS_RET;
    if ($value == 'Y')
        $img = 'locked';
    else
        $img = 'unlocked';

    return '<IMG SRC=assets/' . $img . '.gif ' . (AllowEdit() ? 'onclick="if(this.src.indexOf(\'assets/locked.gif\')!=-1) {this.src=\'assets/unlocked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'\';} else {this.src=\'assets/locked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'Y\';}"' : '') . '><INPUT type=hidden name=schedule[' . $THIS_RET['COURSE_PERIOD_ID'] . '][' . $THIS_RET['START_DATE'] . '][SCHEDULER_LOCK] id=lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . ' value=' . $value . '>';
}

function _makePeriodSelect($course_period_id, $column = '') {
    global $_openSIS, $THIS_RET, $fy_id;
    $sql = 'SELECT cp.COURSE_PERIOD_ID,cp.PARENT_ID,cp.TITLE,cp.MARKING_PERIOD_ID,COALESCE(cp.TOTAL_SEATS-cp.FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods cp,school_periods sp,course_period_var cpv WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID=\'' . $THIS_RET[COURSE_ID] . '\' ORDER BY sp.SORT_ORDER';
    $QI = DBQuery($sql);
    $orders_RET = DBGet($QI);

    foreach ($orders_RET as $value) {
        if ($value['COURSE_PERIOD_ID'] != $value['PARENT_ID']) {
            $parent = DBGet(DBQuery('SELECT SHORT_NAME FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $value['PARENT_ID'] . '\''));
            $parent = $parent[1]['SHORT_NAME'];
        }
        $periods[$value['COURSE_PERIOD_ID']] = $value['TITLE'] . (($value['MARKING_PERIOD_ID'] != $fy_id && $value['COURSE_PERIOD_ID'] != $course_period_id) ? ' (' . GetMP($value['MARKING_PERIOD_ID']) . ')' : '') . ($value['COURSE_PERIOD_ID'] != $course_period_id ? ' (' . $value['AVAILABLE_SEATS'] . ' seats)' : '') . (($value['COURSE_PERIOD_ID'] != $course_period_id && $parent) ? ' -> ' . $parent : '');
    }


    return SelectInput_Disonclick($course_period_id, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][COURSE_PERIOD_ID]", '', $periods, false);
}

function _makeMPSelect($mp_id, $name = '') {
    global $THIS_RET;
    if ($mp_id != '')
        return GetMP($mp_id);
    else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
//                 
}

function _makeDate($value, $column) {//not used
    global $THIS_RET;

    if ($column == 'START_DATE')
        $allow_na = false;
    else
        $allow_na = true;

    return DateInput($value, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", '', true, $allow_na);
}

function _str_split($str) {
    $ret = array();
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++)
        $ret [] = substr($str, $i, 1);
    return $ret;
}

function CreateSelect($val, $name, $link = '', $mpid) {


    if ($link != '') {
        $html .= "<select class=\"form-control\" title='Marking periods' name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    } else
        $html .= "<select name=" . $name . " id=" . $name . " >";

    foreach ($val as $key => $value) {


        if (!isset($mpid) && (UserMP() == $value[strtoupper($name)]))
            $html .= "<option selected value=" . UserMP() . ">" . $value['TITLE'] . "</option>";
        else {
            if ($value[strtoupper($name)] == $_REQUEST[$name])
                $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
            else
                $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
        }
    }



    $html .= "</select>";
    return $html;
}

function create_view_mode($link) {
    if ($link != '')
        $html .= "<select class=\"form-control\" title='View mode' name='view_mode' id='view_mode' onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    else
        $html .= "<select name='view_mode' id='view_mode'>";

    $html .= '<option value="day_view" ' . ($_REQUEST['view_mode'] == 'day_view' ? 'selected' : '') . ' >Day</option>';
    $html .= '<option value="week_view" ' . ($_REQUEST['view_mode'] == 'week_view' ? 'selected' : '') . '>Week</option>';
    $html .= '<option value="month_view" ' . ($_REQUEST['view_mode'] == 'month_view' ? 'selected' : '') . '>Month</option>';
    $html .= "</select>";
    return $html;
}

function get_db_day($day) {
    switch ($day) {
        case 'Sunday':
            $return = 'U';
            break;
        case 'Monday':
            $return = 'M';
            break;
        case 'Tuesday':
            $return = 'T';
            break;
        case 'Wednesday':
            $return = 'W';
            break;
        case 'Thursday':
            $return = 'H';
            break;
        case 'Friday':
            $return = 'F';
            break;
        case 'Saturday':
            $return = 'S';
            break;
    }
    return $return;
}

function weekDate($date) {
    return date('l', strtotime($date));
}

function _makeWeeks($start, $end, $link) {
    $one_day = 60 * 60 * 24;
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    if (!$_REQUEST['week_range']) {
        $start_time_cur = strtotime(date('Y-m-d'));
        while (date('N', $start_time_cur) != 1) {
            $start_time_cur = $start_time_cur - $one_day;
        }
        $_REQUEST['week_range'] = date('Y-m-d', $start_time_cur);
    }



    $prev = date('Y-m-d', strtotime($_REQUEST['week_range']) - $one_day * 7);
    $next = date('Y-m-d', strtotime($_REQUEST['week_range']) + $one_day * 7);
    $upper = date('Y-m-d', strtotime($_REQUEST['week_range']) + $one_day * 6);
    if ($link != '') {
        $html .= "<a href='javascript:void(0);' class=\"text-primary\" title=Previous onClick=\"window.location='" . $link . $prev . "';\"><i class=\"fa fa-angle-left\"></i> Prev</a> &nbsp; &nbsp; <span>" . properDate($_REQUEST[week_range]) . "&nbsp; - &nbsp;" . properDate($upper) . "</span> &nbsp; &nbsp; <a href='javascript:void(0);' title=Next onClick=\"window.location='" . $link . $next . "';\" class=\"text-primary\">Next <i class=\"fa fa-angle-right\"></i></a>";
    }

    return $html;
}

function _makeMonths($link) {
    $one_day = 60 * 60 * 24;
    if (!$_REQUEST['month']) {
        $_REQUEST['month'] = date(strtotime(date('Y-m-d')));
    }
    $prev = $_REQUEST['month'] - $one_day * 30;
    $next = $_REQUEST['month'] + $one_day * 30;
    if ($link != '') {
        $html .= "<a href='javascript:void(0);' class=\"btn btn-default btn-icon\" title=Previous onClick=\"window.location='" . $link . $prev . "';\"><i class=\"fa fa-chevron-left\"></i></a> &nbsp; &nbsp; <span class=\"calendar-title\">" . date('F', $_REQUEST['month']) . "&nbsp; - &nbsp;" . date('Y', $_REQUEST['month']) . "</span> &nbsp; &nbsp; <a href='javascript:void(0);' title=Next onClick=\"window.location='" . $link . $next . "';\" class=\"btn btn-default btn-icon\"><i class=\"fa fa-chevron-right\"></i></a>";
    }

    return $html;
}

function _makeTimePeriod($value) {
    $time = explode(' - ', $value);
    $time = date("g:i A", strtotime($time[0])) . ' - ' . date("g:i A", strtotime($time[1]));
    return $time;
}

?>
