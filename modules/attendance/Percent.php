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

DrawBC(""._attendance." > " . ProgramTitle());


//////////////////////////////For new date picker///////////////////////////////////////////////////////
if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start']) {
    $start_date = $_REQUEST['year_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['day_start'];
    $start_date = ProperDateMAvr($start_date);
    $start_date_mod = $start_date;
} else {
    $start_date = date('Y-m') . '-01';
    $start_date_mod = $start_date;
}
if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end']) {
    $end_date = $_REQUEST['year_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['day_end'];
    $end_date = ProperDateMAvr($end_date);
    $end_date_mod = $end_date;
} else {
    $end_date = ProperDateMAvr();
    $end_date_mod = $end_date;
}


$get_min_start_date = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE FROM attendance_calendar WHERE SYEAR=' . UserSyear() . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND SCHOOL_ID=\'' . UserSchool() . '\'' : '')));
if (strtotime($start_date_mod) < strtotime($get_min_start_date[1]['START_DATE']) && $get_min_start_date[1]['START_DATE'] != '') {
    $start_date_mod = $get_min_start_date[1]['START_DATE'];
    $start_date = $start_date_mod;
    if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start'])
        echo '<font style="color:red"><b>Start date cannot be before school\'s start date</b></font>';
}

if ($_REQUEST['modfunc'] == 'search') {
    echo "<FORM class=form-horizontal name=percentform action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&list_by_day=" . strip_tags(trim($_REQUEST['list_by_day'])) . "&day_start=" . strip_tags(trim($_REQUEST['day_start'])) . "&day_end=" . strip_tags(trim($_REQUEST['day_end'])) . "&month_start=" . strip_tags(trim($_REQUEST['month_start'])) . "&month_end=" . strip_tags(trim($_REQUEST['month_end'])) . "&year_start=" . strip_tags(trim($_REQUEST['year_start'])) . "&year_end=" . strip_tags(trim($_REQUEST['year_end'])) . " method=POST>";
    PopTable('header', _advanced);
    Search('general_info', $extra['grades']);
    if (!isset($extra))
        $extra = array();
    Widgets('user', $extra);
    if ($extra['search'])
        echo $extra['search'];
    Search('student_fields', is_array($extra['student_fields']) ? $extra['student_fields'] : array());
    if (User('PROFILE') == 'admin'){
        echo '<div class="text-center m-15"><div class="text-left display-inline-block"><label class="checkbox-inline checkbox-switch switch-success switch-xs"><INPUT type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '><span></span>'._searchAllSchools.'</label></div></div>';
    }
    $btn = '<div class="p-l-20">' . Buttons(_submit) . '</div>';
    PopTable('footer', $btn);
    echo '</FORM>';
}

if (!$_REQUEST['modfunc']) {

    if (!isset($extra))
        $extra = array();
    Widgets('user');
    if ($_REQUEST['advanced'] == 'Y')
        Widgets('all');
    $extra['WHERE'] .= appendSQL('', $extra);
    $extra['WHERE'] .= CustomFields('where');

    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading clearfix">';
    echo "<FORM  name=ada_from id=ada_from onSubmit='return formcheck_ada_dates();' action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&list_by_day=" . strip_tags(trim($_REQUEST['list_by_day'])) . " method=POST>";
    $advanced_link = " <A class=\"text-pink\" HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=search&list_by_day=$_REQUEST[list_by_day]&day_start=$_REQUEST[day_start]&day_end=$_REQUEST[day_end]&month_start=$_REQUEST[month_start]&month_end=$_REQUEST[month_end]&year_start=$_REQUEST[year_start]&year_end=$_REQUEST[year_end]><i class=\"icon-cog\"></i> "._advanced."</A>";

    echo '<div class="form-inline"><div class="col-md-12"><div class="inline-block">' . DateInputAY($start_date, 'start', 1) . '</div> &nbsp; &nbsp; - &nbsp; &nbsp; <div class="inline-block">' . DateInputAY($end_date, 'end', 2) . '</div> &nbsp; <INPUT type=submit value='._go.'  class="btn btn-primary"> &nbsp; ' . $advanced_link . '</div></div>';
    echo '</FORM>';
    echo '</div>'; //.panel-body

    if ($_REQUEST['list_by_day'] == 'true') {

        $cal_days = 1;
        $search_stu = 0;

        if ($_REQUEST['last'] != '' || $_REQUEST['first'] != '' || $_REQUEST['stuid'] != '' || $_REQUEST['altid'] != '') {
            $stu_q = 'SELECT GROUP_CONCAT(STUDENT_ID) as STUDENT_ID FROM students WHERE ';
            $stu_q.=($_REQUEST['last'] != '' ? ' LAST_NAME=\'' . $_REQUEST['last'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['first'] != '' ? ' FIRST_NAME=\'' . $_REQUEST['first'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['stuid'] != '' ? ' STUDENT_ID=\'' . $_REQUEST['stuid'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['altid'] != '' ? ' ALT_ID=\'' . $_REQUEST['altid'] . '\' OR ' : '');
            $stu_q = preg_replace('/ OR $/', '', $stu_q);
            $stu_q = DBGet(DBQuery($stu_q));
            $search_stu = $stu_q[1]['STUDENT_ID'];
        }
        if ($_REQUEST['addr'] != '') {
            $stu_q = DBGet(DBQuery('SELECT GROUP_CONAT(STUDENT_ID) as STUDENT_ID FROM student_address WHERE address like \'%' . $_REQUEST['addr'] . '%\''));
            $search_stu.=$stu_q[1]['STUDENT_ID'];
        }

        if ($_REQUEST['grade'] == '')
            $student_days_possible = DBGet(DBQuery('SELECT ac.SCHOOL_ID,ac.SCHOOL_DATE,ac.SCHOOL_DATE as SC_DATE,ssm.GRADE_ID as GRADE,ssm.GRADE_ID,\'0\' AS DAYS_POSSIBLE,0 AS ATTENDANCE_POSSIBLE,count(DISTINCT ssm.STUDENT_ID) AS STUDENTS,\'0\' AS PRESENT,\'0\' AS ABSENT,\'0\' AS OTHERS,\'0\' AS ADA,\'0\' AS AVERAGE_ATTENDANCE,\'0\' AS AVERAGE_ABSENT FROM student_enrollment ssm,attendance_calendar ac,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ac.SYEAR=ssm.SYEAR AND ssm.SCHOOL_ID=ac.SCHOOL_ID  AND ssm.SCHOOL_ID=ac.SCHOOL_ID AND (ssm.GRADE_ID IS NOT NULL OR ssm.GRADE_ID<>\'\') AND (ac.SCHOOL_DATE BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND ssm.START_DATE <= ac.SCHOOL_DATE)) AND ac.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND ac.SCHOOL_ID=\'' . UserSchool() . '\' ' : '') . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '') . ' GROUP BY ac.SCHOOL_DATE,ssm.GRADE_ID'), array('SCHOOL_DATE' => 'ProperDate', 'GRADE_ID' => 'GetGrade', 'DAYS_POSSIBLE' => '_makeByDay'));
        if ($_REQUEST['grade'] != '')
            $student_days_possible = DBGet(DBQuery('SELECT ac.SCHOOL_ID,ac.SCHOOL_DATE,ac.SCHOOL_DATE as SC_DATE,ssm.GRADE_ID as GRADE,ssm.GRADE_ID,\'0\' AS DAYS_POSSIBLE,0 AS ATTENDANCE_POSSIBLE,count(DISTINCT ssm.STUDENT_ID) AS STUDENTS,\'0\' AS PRESENT,\'0\' AS ABSENT,\'0\' AS OTHERS,\'0\' AS ADA,\'0\' AS AVERAGE_ATTENDANCE,\'0\' AS AVERAGE_ABSENT FROM student_enrollment ssm,attendance_calendar ac,students s WHERE ssm.GRADE_ID IN (SELECT ID FROM school_gradelevels WHERE TITLE=\'' . $_REQUEST['grade'] . '\') AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ac.SYEAR=ssm.SYEAR AND ssm.SCHOOL_ID=ac.SCHOOL_ID AND (ssm.GRADE_ID IS NOT NULL OR ssm.GRADE_ID<>\'\') AND ssm.SCHOOL_ID=ac.SCHOOL_ID AND (ac.SCHOOL_DATE BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND ssm.START_DATE <= ac.SCHOOL_DATE)) AND ac.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND ac.SCHOOL_ID=\'' . UserSchool() . '\' ' : '') . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '') . ' GROUP BY ac.SCHOOL_DATE'), array('SCHOOL_DATE' => 'ProperDate', 'GRADE_ID' => 'GetGrade', 'DAYS_POSSIBLE' => '_makeByDay'));

        foreach ($student_days_possible as $si => $sd) {
            $present = DBGet(DBQuery('SELECT COUNT(*) AS PRESENT_BY_GRADE FROM `attendance_period` AP,student_enrollment SE WHERE AP.ATTENDANCE_CODE IN (SELECT ID FROM attendance_codes WHERE STATE_CODE=\'P\') AND SE.SCHOOL_ID=\'' . $sd['SCHOOL_ID'] . '\' AND AP.SCHOOL_DATE=\'' . $sd['SC_DATE'] . '\' AND AP.STUDENT_ID=SE.STUDENT_ID AND SE.GRADE_ID=' . $sd['GRADE'] . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '')));
            $absent = DBGet(DBQuery('SELECT COUNT(*) AS ABSENT_BY_GRADE FROM `attendance_period` AP,student_enrollment SE WHERE AP.ATTENDANCE_CODE IN (SELECT ID FROM attendance_codes WHERE STATE_CODE=\'A\') AND SE.SCHOOL_ID=\'' . $sd['SCHOOL_ID'] . '\' AND AP.SCHOOL_DATE=\'' . $sd['SC_DATE'] . '\' AND AP.STUDENT_ID=SE.STUDENT_ID AND SE.GRADE_ID=' . $sd['GRADE'] . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '')));
            $others = DBGet(DBQuery('SELECT COUNT(*) AS OTHERS_BY_GRADE FROM `attendance_period` AP,student_enrollment SE WHERE AP.ATTENDANCE_CODE IN (SELECT ID FROM attendance_codes WHERE STATE_CODE=\'H\') AND SE.SCHOOL_ID=\'' . $sd['SCHOOL_ID'] . '\' AND AP.SCHOOL_DATE=\'' . $sd['SC_DATE'] . '\' AND AP.STUDENT_ID=SE.STUDENT_ID AND SE.GRADE_ID=' . $sd['GRADE'] . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '')));
            $student_days_possible[$si]['PRESENT'] = $present[1]['PRESENT_BY_GRADE'];
            $student_days_possible[$si]['ABSENT'] = $absent[1]['ABSENT_BY_GRADE'];
            $student_days_possible[$si]['OTHERS'] = $others[1]['OTHERS_BY_GRADE'];

            $get_cps = DBGet(DBQuery('SELECT DISTINCT s.COURSE_PERIOD_ID,cp.SCHEDULE_TYPE,cp.MARKING_PERIOD_ID,cp.BEGIN_DATE,cp.END_DATE  FROM schedule s,student_enrollment se,course_periods cp,course_period_var cpv WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                    s.START_DATE<=\'' . $sd['SC_DATE'] . '\' AND (s.END_DATE>=\'' . $sd['SC_DATE'] . '\' OR s.END_DATE IS NULL OR s.END_DATE=\'0000-00-00\')) AND se.GRADE_ID=' . $sd['GRADE'] . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' ' . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '')));

            foreach ($get_cps as $gi => $gd) {
                $get_stu_num = DBGet(DBQuery('SELECT COUNT(s.STUDENT_ID) as ENROLLED_STU  FROM schedule s,student_enrollment se WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                        s.START_DATE<=\'' . $sd['SC_DATE'] . '\' AND (s.END_DATE>=\'' . $sd['SC_DATE'] . '\' OR s.END_DATE IS NULL OR s.END_DATE=\'0000-00-00\')) AND se.GRADE_ID=' . $sd['GRADE'] . ' AND s.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ($search_stu != '0' ? ' AND ssm.STUDENT_ID IN (\'' . $search_stu . '\') ' : '')));


                if ($gd['SCHEDULE_TYPE'] == 'FIXED') {
                    $get_day = DBGet(DBQuery('SELECT DAYS FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\''));
                    $get_day = $get_day[1]['DAYS'];
                    if (stristr($get_day, DaySname(date('l', strtotime($sd['SC_DATE']))))) {
                        if ($gd['MARKING_PERIOD_ID'] != '')
                            $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                        elseif ($gd['MARKING_PERIOD_ID'] == '' && strtotime($gd['BEGIN_DATE']) <= strtotime($sd['SC_DATE']) && strtotime($gd['END_DATE']) >= strtotime($sd['SC_DATE']))
                            $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                    }
                }
                elseif ($gd['SCHEDULE_TYPE'] == 'VARIABLE') {
                    $get_day = DBGet(DBQuery('SELECT DAYS FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\''));
                    foreach ($get_day as $gtd) {
                        if (date('l', strtotime($sd['SC_DATE'])) == DaySname($gtd['DAYS'], 2)) {
                            if ($gd['MARKING_PERIOD_ID'] != '')
                                $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                            elseif ($gd['MARKING_PERIOD_ID'] == '' && strtotime($gd['BEGIN_DATE']) <= strtotime($sd['SC_DATE']) && strtotime($gd['END_DATE']) >= strtotime($sd['SC_DATE']))
                                $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                        }
                    }
                }
                else {
                    $get_day = DBGet(DBQuery('SELECT COURSE_PERIOD_DATE FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\''));
                    foreach ($get_day as $gtd) {
                        if ($sd['SC_DATE'] == $gtd['COURSE_PERIOD_DATE']) {
                            if ($gd['MARKING_PERIOD_ID'] != '')
                                $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                            elseif ($gd['MARKING_PERIOD_ID'] == '' && strtotime($gd['BEGIN_DATE']) <= strtotime($sd['SC_DATE']) && strtotime($gd['END_DATE']) >= strtotime($sd['SC_DATE']))
                                $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] = $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] + $get_stu_num[1]['ENROLLED_STU'];
                        }
                    }
                }
            }
            $student_days_possible[$si]['ADA'] = (($student_days_possible[$si]['PRESENT'] != 0 && $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] != 0) ? round(($student_days_possible[$si]['PRESENT'] / $student_days_possible[$si]['ATTENDANCE_POSSIBLE']) * 100, 2) : 0) . '%';
            $student_days_possible[$si]['AVERAGE_ABSENT'] = (($student_days_possible[$si]['ABSENT'] != 0 && $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] != 0) ? round(($student_days_possible[$si]['ABSENT'] / $student_days_possible[$si]['ATTENDANCE_POSSIBLE']), 2) : 0);
            $student_days_possible[$si]['AVERAGE_ATTENDANCE'] = (($student_days_possible[$si]['PRESENT'] != 0 && $student_days_possible[$si]['ATTENDANCE_POSSIBLE'] != 0) ? round(($student_days_possible[$si]['PRESENT'] / $student_days_possible[$si]['ATTENDANCE_POSSIBLE']), 2) : 0);
        }


        $columns = array('SCHOOL_DATE' => _date, 'GRADE_ID' => _grade, 'STUDENTS' => _students, 'DAYS_POSSIBLE' => _daysPossible, 'PRESENT' => _present, 'ABSENT' => _absent, 'OTHERS' => _others, 'ADA' => _ada, 'AVERAGE_ATTENDANCE' => _averageAttendance, 'AVERAGE_ABSENT' => _averageAbsent);

        ListOutput($student_days_possible, $columns,  _student, _students, $link);
    }
    else {

        $cal_days = DBGet(DBQuery('SELECT count(*) AS COUNT,CALENDAR_ID FROM attendance_calendar WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' SYEAR=\'' . UserSyear() . '\' AND SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' GROUP BY CALENDAR_ID'), array(), array('CALENDAR_ID'));
        $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND SCHOOL_ID=\'' . UserSchool() . '\'' : '')), array(), array('CALENDAR_ID'));
        $extra['WHERE'] .= ' GROUP BY ssm.GRADE_ID,ssm.CALENDAR_ID';
        $days_arr = array();
        $date_arr = array();
        $days_possible = array();
        $days_possible_day = array();
        $starting_date = strtotime($start_date_mod);
        $ending_date = strtotime($end_date_mod);

        for ($i = $starting_date; $i <= $ending_date; $i = $i + 86400) {
            foreach ($calendars_RET as $ci => $cd) {
                $check_day = DBGet(DBQuery('SELECT COUNT(1) as EX FROM attendance_calendar WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . '  SYEAR=' . UserSyear() . ' AND SCHOOL_DATE=\'' . date('Y-m-d', $i) . '\' '));
                if ($check_day[1]['EX'] > 0) {
                    $days_arr[$cd[1]['CALENDAR_ID']][] = DaySname(date('l', $i));
                    $date_arr[$cd[1]['CALENDAR_ID']][] = date('Y-m-d', $i);
                    $days_possible[date('Y-m-d', $i)] = date('Y-m-d', $i);
                    $days_possible_day[date('Y-m-d', $i)] = DaySname(date('l', $i));
                }
            }
        }
        $present_ids = DBGet(DBQuery('SELECT GROUP_CONCAT(ID) AS PRESENT FROM  attendance_codes WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' SYEAR=' . UserSyear() . ' AND STATE_CODE=\'P\' '));
        $present_ids = $present_ids[1]['PRESENT'];

        $absent_ids = DBGet(DBQuery('SELECT GROUP_CONCAT(ID) AS ABSENT FROM  attendance_codes WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' SYEAR=' . UserSyear() . ' AND STATE_CODE=\'A\' '));
        $absent_ids = $absent_ids[1]['ABSENT'];

        $others_ids = DBGet(DBQuery('SELECT GROUP_CONCAT(ID) AS ABSENT FROM  attendance_codes WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' SYEAR=' . UserSyear() . ' AND STATE_CODE=\'H\' '));
        $others_ids = $others_ids[1]['ABSENT'];

        $last_sum = array();
        $search_stu = '';

        if ($_REQUEST['last'] != '' || $_REQUEST['first'] != '' || $_REQUEST['stuid'] != '' || $_REQUEST['altid'] != '') {
            $stu_q = 'SELECT GROUP_CONCAT(STUDENT_ID) as STUDENT_ID FROM students WHERE ';
            $stu_q.=($_REQUEST['last'] != '' ? ' LAST_NAME=\'' . $_REQUEST['last'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['first'] != '' ? ' FIRST_NAME=\'' . $_REQUEST['first'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['stuid'] != '' ? ' STUDENT_ID=\'' . $_REQUEST['stuid'] . '\' OR ' : '');
            $stu_q.=($_REQUEST['altid'] != '' ? ' ALT_ID=\'' . $_REQUEST['altid'] . '\' OR ' : '');
            $stu_q = preg_replace('/ OR $/', '', $stu_q);
            $stu_q = DBGet(DBQuery($stu_q));
            $search_stu = $stu_q[1]['STUDENT_ID'];
        }
        if ($_REQUEST['addr'] != '') {
            $stu_q = DBGet(DBQuery('SELECT GROUP_CONAT(STUDENT_ID) as STUDENT_ID FROM student_address WHERE address like \'%' . $_REQUEST['addr'] . '%\''));
            $search_stu.=$stu_q[1]['STUDENT_ID'];
        }
        $columns = array('GRADE_ID' => _grade, 'STUDENTS' => _students, 'DAYS_POSSIBLE' => _daysPossible, 'ATTENDANCE_POSSIBLE' => _attendancePossible, 'PRESENT' => _present, 'ABSENT' => _absent, 'OTHERS' => _others, 'NOT_TAKEN' => _notTaken, 'ADA' => _ada, 'AVERAGE_ATTENDANCE' => _avgAttendance, 'AVERAGE_ABSENT' => _avgAbsent);
        if ($_REQUEST['grade'] == '')
            $ada = DBGet(DBQuery('SELECT ID as GRADE,TITLE as GRADE_ID,0 as STUDENTS,0 as DAYS_POSSIBLE,0 AS ATTENDANCE_POSSIBLE,0 AS PRESENT,0 AS ABSENT,0 AS NOT_TAKEN,0 AS ADA,0 AS AVERAGE_ATTENDANCE,0 AS AVERAGE_ABSENT,0 as OTHERS FROM school_gradelevels ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'WHERE SCHOOL_ID=\'' . UserSchool() . '\' ' : '')));
        if ($_REQUEST['grade'] != '')
            $ada = DBGet(DBQuery('SELECT ID as GRADE,TITLE as GRADE_ID,0 as STUDENTS,0 as DAYS_POSSIBLE,0 AS ATTENDANCE_POSSIBLE,0 AS PRESENT,0 AS ABSENT,0 AS NOT_TAKEN,0 AS ADA,0 AS AVERAGE_ATTENDANCE,0 AS AVERAGE_ABSENT,0 as OTHERS FROM school_gradelevels WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' TITLE=\'' . $_REQUEST['grade'] . '\' '));
        foreach ($ada as $ai => $ad) {

            if ($search_stu == '')
                $total_students = DBGet(DBQuery('SELECT count(STUDENT_ID) as STUDENTS FROM student_enrollment  WHERE GRADE_ID=' . $ad['GRADE'] . ' AND SYEAR=\'' . UserSyear() . '\' AND ( 
                    (START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (START_DATE <= \'' . $start_date_mod . '\') AND ((END_DATE IS NULL) OR (END_DATE >= \'' . $start_date_mod . '\'))))'));
            else
                $total_students = DBGet(DBQuery('SELECT count(STUDENT_ID) as STUDENTS FROM student_enrollment  WHERE GRADE_ID=' . $ad['GRADE'] . ' AND SYEAR=\'' . UserSyear() . '\' AND ( 
                    (START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (START_DATE <= \'' . $start_date_mod . '\') AND ((END_DATE IS NULL) OR (END_DATE >= \'' . $start_date_mod . '\')))) AND STUDENT_ID IN (' . $search_stu . ')'));

            $ada[$ai]['STUDENTS'] = $total_students[1]['STUDENTS'];
            $ada[$ai]['DAYS_POSSIBLE'] = count($days_possible);

            if ($search_stu == '')
                $get_cps = DBGet(DBQuery('SELECT DISTINCT s.COURSE_PERIOD_ID,cp.SCHEDULE_TYPE,cp.CALENDAR_ID,cp.MARKING_PERIOD_ID,cp.BEGIN_DATE,cp.END_DATE  FROM schedule s,student_enrollment se,course_periods cp,course_period_var cpv WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                    (s.START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (s.END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (s.START_DATE <= \'' . $start_date_mod . '\') AND ((s.END_DATE IS NULL) OR (s.END_DATE >= \'' . $start_date_mod . '\')))) AND se.GRADE_ID=' . $ad['GRADE'] . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' '));
            else
                $get_cps = DBGet(DBQuery('SELECT DISTINCT s.COURSE_PERIOD_ID,cp.SCHEDULE_TYPE,cp.CALENDAR_ID,cp.MARKING_PERIOD_ID,cp.BEGIN_DATE,cp.END_DATE  FROM schedule s,student_enrollment se,course_periods cp,course_period_var cpv WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                    (s.START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (s.END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (s.START_DATE <= \'' . $start_date_mod . '\') AND ((s.END_DATE IS NULL) OR (s.END_DATE >= \'' . $start_date_mod . '\')))) AND se.GRADE_ID=' . $ad['GRADE'] . ' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' AND s.STUDENT_ID IN (' . $search_stu . ')'));

            foreach ($get_cps as $gi => $gd) {
                if ($search_stu == '')
                    $get_stu_num = DBGet(DBQuery('SELECT s.STUDENT_ID as ENROLLED_STU,s.START_DATE  FROM schedule s,student_enrollment se WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                        (s.START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (s.END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (s.START_DATE <= \'' . $start_date_mod . '\') AND ((s.END_DATE IS NULL) OR (s.END_DATE >= \'' . $start_date_mod . '\')))) AND se.GRADE_ID=' . $ad['GRADE'] . ' AND s.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID']));
                else
                    $get_stu_num = DBGet(DBQuery('SELECT s.STUDENT_ID as ENROLLED_STU,s.START_DATE  FROM schedule s,student_enrollment se WHERE  se.SYEAR=' . UserSyear() . ' AND se.STUDENT_ID=s.STUDENT_ID AND ( 
                        (s.START_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR (s.END_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\') OR ( (s.START_DATE <= \'' . $start_date_mod . '\') AND ((s.END_DATE IS NULL) OR (s.END_DATE >= \'' . $start_date_mod . '\')))) AND se.GRADE_ID=' . $ad['GRADE'] . ' AND s.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND s.STUDENT_ID IN (' . $search_stu . ')'));

                if ($gd['SCHEDULE_TYPE'] == 'FIXED') {
                    $get_day = DBGet(DBQuery('SELECT DAYS FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\' '));
                    $get_day = $get_day[1]['DAYS'];
                    $temp_day = array();
                    $temp_day_possible = array();

                    for ($j = 0; $j < strlen($get_day); $j++) {
                        $temp_day[] = substr($get_day, $j, 1);
                    }
                    if ($gd['MARKING_PERIOD_ID'] == '') {
                        foreach ($days_possible_day as $dp => $dpd) {
                            if (strtotime($gd['BEGIN_DATE']) <= strtotime($dp) && strtotime($gd['END_DATE']) >= strtotime($dp))
                                $temp_day_possible[$dp] = $dpd;
                        }
                    }



                    foreach ($get_stu_num as $gsdi => $gsdt) {

                        $stu_days_possible_day = array();
                        $sch_start = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as SCHOOL_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $gd['CALENDAR_ID'] . ' AND SCHOOL_DATE>=\'' . $gsdt['START_DATE'] . '\''));
                        $sch_start = ($sch_start[1]['SCHOOL_DATE'] != '' ? $sch_start[1]['SCHOOL_DATE'] : $gsdt['START_DATE']);
                        if ($gd['MARKING_PERIOD_ID'] != '')
                            $stu_days_possible_day = array_slice($days_possible_day, array_search($sch_start, array_keys($days_possible_day)));
                        else
                            $stu_days_possible_day = array_slice($temp_day_possible, array_search($sch_start, array_keys($temp_day_possible)));

                        foreach ($temp_day as $td) {

                            $ada[$ai]['ATTENDANCE_POSSIBLE'] = $ada[$ai]['ATTENDANCE_POSSIBLE'] + count(array_keys($stu_days_possible_day, $td));
                        }
                    }
                } elseif ($gd['SCHEDULE_TYPE'] == 'VARIABLE') {
                    $get_day = DBGet(DBQuery('SELECT DAYS FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\' '));
                    $temp_day = array();
                    $temp_day_possible = array();

                    foreach ($get_day as $gtd) {
                        $temp_day[] = $gtd['DAYS'];
                    }
                    if ($gd['MARKING_PERIOD_ID'] == '') {
                        foreach ($days_possible_day as $dp => $dpd) {
                            if (strtotime($gd['BEGIN_DATE']) <= strtotime($dp) && strtotime($gd['END_DATE']) >= strtotime($dp))
                                $temp_day_possible[$dp] = $dpd;
                        }
                    }

                    foreach ($get_stu_num as $gsdi => $gsdt) {
                        $stu_days_possible_day = array();

                        $sch_start = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as SCHOOL_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $gd['CALENDAR_ID'] . ' AND SCHOOL_DATE>=\'' . $gsdt['START_DATE'] . '\''));
                        $sch_start = ($sch_start[1]['SCHOOL_DATE'] != '' ? $sch_start[1]['SCHOOL_DATE'] : $gsdt['START_DATE']);

                        if ($gd['MARKING_PERIOD_ID'] != '')
                            $stu_days_possible_day = array_slice($days_possible_day, array_search($sch_start, array_keys($days_possible_day)));
                        else
                            $stu_days_possible_day = array_slice($temp_day_possible, array_search($sch_start, array_keys($temp_day_possible)));

                        foreach ($temp_day as $td) {
                            $ada[$ai]['ATTENDANCE_POSSIBLE'] = $ada[$ai]['ATTENDANCE_POSSIBLE'] + count(array_keys($stu_days_possible_day, $td));
                        }
                    }
                } else {
                    $get_day = DBGet(DBQuery('SELECT COURSE_PERIOD_DATE FROM course_period_var WHERE COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND DOES_ATTENDANCE=\'Y\' '));
                    $temp_day = array();
                    $temp_day_possible = array();

                    foreach ($get_day as $gtd) {

                        $temp_day[] = $gtd['COURSE_PERIOD_DATE'];
                    }
                    if ($gd['MARKING_PERIOD_ID'] == '') {
                        foreach ($days_possible_day as $dp => $dpd) {
                            if (strtotime($gd['BEGIN_DATE']) <= strtotime($dp) && strtotime($gd['END_DATE']) >= strtotime($dp))
                                $temp_day_possible[$dp] = $dp;
                        }
                    }

                    foreach ($get_stu_num as $gsdi => $gsdt) {
                        $stu_days_possible_day = array();
                        $sch_start = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as SCHOOL_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $gd['CALENDAR_ID'] . ' AND SCHOOL_DATE>=\'' . $gsdt['START_DATE'] . '\''));
                        $sch_start = ($sch_start[1]['SCHOOL_DATE'] != '' ? $sch_start[1]['SCHOOL_DATE'] : $gsdt['START_DATE']);
                        if ($gd['MARKING_PERIOD_ID'] != '')
                            $stu_days_possible_day = array_slice($days_possible, array_search($sch_start, array_keys($days_possible)));
                        else
                            $stu_days_possible_day = array_slice($temp_day_possible, array_search($sch_start, array_keys($temp_day_possible)));

                        foreach ($temp_day as $td) {
                            $ada[$ai]['ATTENDANCE_POSSIBLE'] = $ada[$ai]['ATTENDANCE_POSSIBLE'] + count(array_keys($stu_days_possible_day, $td));
                        }
                    }
                }

                if ($search_stu == '') {
                    if ($present_ids != '')
                        $present_st = DBGet(DBQuery('SELECT COUNT(ap.STUDENT_ID) as PRESENT_STU FROM attendance_period ap,student_enrollment se WHERE ap.STUDENT_ID=se.STUDENT_ID and se.SYEAR=' . UserSyear() . ' and se.SCHOOL_ID=' . UserSchool() . ' and se.GRADE_ID=\'' . $ad['GRADE'] . '\' and ap.SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND ap.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ap.ATTENDANCE_CODE IN (' . $present_ids . ')'));
                    else
                        $present_st[1]['PRESENT_STU'] = 0;
                    if ($absent_ids != '')
                        $absent_st = DBGet(DBQuery('SELECT COUNT(ap.STUDENT_ID) as ABSENT_STU FROM attendance_period ap,student_enrollment se  WHERE ap.STUDENT_ID=se.STUDENT_ID and se.SYEAR=' . UserSyear() . ' and se.SCHOOL_ID=' . UserSchool() . ' AND se.GRADE_ID=\'' . $ad['GRADE'] . '\' and ap.SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND ap.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ap.ATTENDANCE_CODE IN (' . $absent_ids . ')'));
                    else
                        $absent_st[1]['ABSENT_STU'] = 0;
                    if ($others_ids != '')
                        $others_st = DBGet(DBQuery('SELECT COUNT(ap.STUDENT_ID) as OTHERS_STU FROM attendance_period ap,student_enrollment se WHERE ap.STUDENT_ID=se.STUDENT_ID and se.SYEAR=' . UserSyear() . ' and se.SCHOOL_ID=' . UserSchool() . ' AND se.GRADE_ID=\'' . $ad['GRADE'] . '\' AND ap.SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND ap.COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ap.ATTENDANCE_CODE IN (' . $others_ids . ')'));
                    else
                        $others_st[1]['OTHERS_STU'] = 0;
                }
                else {
                    if ($present_ids != '')
                        $present_st = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) as PRESENT_STU FROM attendance_period WHERE  SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ATTENDANCE_CODE IN (' . $present_ids . ') AND STUDENT_ID IN (' . $search_stu . ')'));
                    else
                        $present_st[1]['PRESENT_STU'] = 0;
                    if ($absent_ids != '')
                        $absent_st = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) as ABSENT_STU FROM attendance_period WHERE SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ATTENDANCE_CODE IN (' . $absent_ids . ') AND STUDENT_ID IN (' . $search_stu . ')'));
                    else
                        $absent_st[1]['ABSENT_STU'] = 0;
                    if ($others_ids != '')
                        $others_st = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) as OTHERS_STU FROM attendance_period WHERE SCHOOL_DATE BETWEEN \'' . $start_date_mod . '\' AND \'' . $end_date_mod . '\' AND COURSE_PERIOD_ID=' . $gd['COURSE_PERIOD_ID'] . ' AND ATTENDANCE_CODE IN (' . $others_ids . ')'));
                    else
                        $others_st[1]['OTHERS_STU'] = 0;
                }

                $ada[$ai]['PRESENT'] = $ada[$ai]['PRESENT'] + $present_st[1]['PRESENT_STU'];
                $ada[$ai]['ABSENT'] = $ada[$ai]['ABSENT'] + $absent_st[1]['ABSENT_STU'];
                $ada[$ai]['OTHERS'] = $ada[$ai]['OTHERS'] + $others_st[1]['OTHERS_STU'];
            }
            $ada[$ai]['NOT_TAKEN'] = $ada[$ai]['ATTENDANCE_POSSIBLE'] - ($ada[$ai]['PRESENT'] + $ada[$ai]['ABSENT'] + $ada[$ai]['OTHERS']);


            $ada[$ai]['ADA'] = (($ada[$ai]['PRESENT'] != 0 && $ada[$ai]['ATTENDANCE_POSSIBLE'] != 0) ? round(($ada[$ai]['PRESENT'] / $ada[$ai]['ATTENDANCE_POSSIBLE']) * 100, 2) : '0') . '%';
            $ada[$ai]['AVERAGE_ABSENT'] = (($ada[$ai]['ABSENT'] != 0 && $ada[$ai]['ATTENDANCE_POSSIBLE'] != 0) ? round(($ada[$ai]['ABSENT'] / $ada[$ai]['ATTENDANCE_POSSIBLE']), 2) : 0);
            $ada[$ai]['AVERAGE_ATTENDANCE'] = (($ada[$ai]['PRESENT'] != 0 && $ada[$ai]['ATTENDANCE_POSSIBLE'] != 0) ? round(($ada[$ai]['PRESENT'] / $ada[$ai]['ATTENDANCE_POSSIBLE']), 2) : '0');

            $last_sum['STUDENTS'] = $last_sum['STUDENTS'] + $ada[$ai]['STUDENTS'];
            $last_sum['DAYS_POSSIBLE'] = $last_sum['DAYS_POSSIBLE'] + $ada[$ai]['DAYS_POSSIBLE'];
            $last_sum['ATTENDANCE_POSSIBLE'] = $last_sum['ATTENDANCE_POSSIBLE'] + $ada[$ai]['ATTENDANCE_POSSIBLE'];
            $last_sum['PRESENT'] = $last_sum['PRESENT'] + $ada[$ai]['PRESENT'];
            $last_sum['ABSENT'] = $last_sum['ABSENT'] + $ada[$ai]['ABSENT'];
            $last_sum['OTHERS'] = $last_sum['OTHERS'] + $ada[$ai]['OTHERS'];
            $last_sum['NOT_TAKEN'] = $last_sum['NOT_TAKEN'] + $ada[$ai]['NOT_TAKEN'];


            $last_sum['ADA'] = (($last_sum['PRESENT'] != 0 && $last_sum['ATTENDANCE_POSSIBLE'] != 0) ? round(($last_sum['PRESENT'] / $last_sum['ATTENDANCE_POSSIBLE']) * 100, 2) : 0) . '%';
            $last_sum['AVERAGE_ABSENT'] = (($last_sum['ABSENT'] != 0 && $last_sum['ATTENDANCE_POSSIBLE'] != 0) ? round(($last_sum['ABSENT'] / $last_sum['ATTENDANCE_POSSIBLE']), 2) : 0);
            $last_sum['AVERAGE_ATTENDANCE'] = (($last_sum['PRESENT'] != 0 && $last_sum['ATTENDANCE_POSSIBLE'] != 0) ? round(($last_sum['PRESENT'] / $last_sum['ATTENDANCE_POSSIBLE']), 2) : 0);
        }

        if (count($ada) > 0) {
            $indexWillBe = count($ada) + 1;
            $ada[$indexWillBe]['GRADE_ID'] = '<b>' . 'Total' . '</b>';
            $ada[$indexWillBe]['STUDENTS'] = $last_sum['STUDENTS'];
            $ada[$indexWillBe]['DAYS_POSSIBLE'] = $last_sum['DAYS_POSSIBLE'];
            $ada[$indexWillBe]['ATTENDANCE_POSSIBLE'] = $last_sum['ATTENDANCE_POSSIBLE'];
            $ada[$indexWillBe]['PRESENT'] = $last_sum['PRESENT'];
            $ada[$indexWillBe]['ADA'] = $last_sum['ADA'];
            $ada[$indexWillBe]['ABSENT'] = $last_sum['ABSENT'];
            $ada[$indexWillBe]['OTHERS'] = $last_sum['OTHERS'];
            $ada[$indexWillBe]['NOT_TAKEN'] = $last_sum['NOT_TAKEN'];
            $ada[$indexWillBe]['AVERAGE_ATTENDANCE'] = $last_sum['AVERAGE_ATTENDANCE'];
            $ada[$indexWillBe]['AVERAGE_ABSENT'] = $last_sum['AVERAGE_ABSENT'];
        }
//                $link['add']['html'] = array('GRADE_ID'=>'<b>'.'Total'.'</b>','STUDENTS'=>$last_sum['STUDENTS'],'DAYS_POSSIBLE'=>$last_sum['DAYS_POSSIBLE'],'ATTENDANCE_POSSIBLE'=>$last_sum['ATTENDANCE_POSSIBLE'],'PRESENT'=>$last_sum['PRESENT'],'ADA'=>$last_sum['ADA'],'ABSENT'=>$last_sum['ABSENT'],'OTHERS'=>$last_sum['OTHERS'],'NOT_TAKEN'=>$last_sum['NOT_TAKEN'],'AVERAGE_ATTENDANCE'=>$last_sum['AVERAGE_ATTENDANCE'],'AVERAGE_ABSENT'=>$last_sum['AVERAGE_ABSENT']);
        ListOutput($ada, $columns,  _gradeLevel, _gradeLevels, $link);
    }


    echo '</div>'; //.panel.panel-default
}

function _make($value, $column) {
    global $THIS_RET, $student_days_absent, $student_not_taken, $cal_days, $sum, $calendars_RET, $student_days_present, $attpossible;

    switch ($column) {

        case 'STUDENTS':

            $sum['STUDENTS'] += $value;
            return $value;
            break;

        case 'DAYS_POSSIBLE':

            return $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'];
            break;

        case 'TOTAL_ATTENDANCE':

            $dayespossible = $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'];
            $students = $value;
            $total_attn = ($dayespossible * $students);
            $sum['TOTAL_ATTENDANCE'] += $total_attn;
            return $total_attn;
            break;

        case 'PRESENT':

            $present_by_gread = 0;

            $present_by_gread = $student_days_present[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['PRESENT_BY_GREAD'];
            $sum['PRESENT'] += $present_by_gread;
            return $present_by_gread;
            break;

        case 'ABSENT':

            $absent = 0;
            $absent = $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'];
            $absent = round($absent);
            $sum['ABSENT'] += $absent;
            return $absent;

            break;


        case 'NOT_TAKEN':

            $not_taken = 0;
            $not_taken = $student_not_taken[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['NOT_TAKEN'];
            $not_taken = round($not_taken);
            $sum['NOT_TAKEN'] += $not_taken;
            return $not_taken;

            break;

        case 'ATTENDANCE_POSSIBLE':

            $attpossible = $value;
            $sum['ATTENDANCE_POSSIBLE'] += $attpossible;
            return $attpossible;
            break;

        case 'ADA':

            $present_by_gread = $student_days_present[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['PRESENT_BY_GREAD'];
            $ada = round($present_by_gread * 100 / $attpossible, 2) . '%';
            return $ada;

            break;

        case 'AVERAGE_ATTENDANCE':

            $present_by_gread = 0;
            $present_by_gread = $student_days_present[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['PRESENT_BY_GREAD'];

            $present = $present_by_gread;
            $dayespossible = $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'];
            $avg_attn = ($present / $dayespossible);
            $sum['AVERAGE_ATTENDANCE'] += $avg_attn;
            return $avg_attn = round($avg_attn, 1);

            break;

        case 'AVERAGE_ABSENT':

            $sum['AVERAGE_ABSENT'] += ($student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'] / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT']);
            return round($student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'] / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'], 1);
            break;

        case 'GRADE_ID':

            return GetGrade($value) . (count($cal_days) > 1 ? ' - ' . $calendars_RET[$THIS_RET['CALENDAR_ID']][1]['TITLE'] : '');
            break;
    }
}

function _makeByDay($value, $column) {
    global $THIS_RET, $student_days_absent, $atten_possible, $cal_days, $sum;

    switch ($column) {
        case 'ATTENDANCE_POSSIBLE':

            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return 1;
            else
                return 0;
            break;

        case 'STUDENTS':
            $sum['STUDENTS'] += $value / $cal_days;
            return round($value / $cal_days, 1);
            break;

        case 'DAYS_POSSIBLE':
            return $cal_days;
            break;

        case 'TOTAL_ATTENDANCE':
            return $sum['TOTAL_ATTENDANCE'] += $total_attn;
            break;

        case 'PRESENT':

            $sum['PRESENT'] += ($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']);
            $PRESENT_STU = $THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'];
            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return $THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'];
            else
                return "";

            break;

        case 'ABSENT':
            $sum['ABSENT'] += ($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']);
            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return round($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']);
            else
                return "";
            break;

        case 'ADA':
            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return Percent((($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'])) / $THIS_RET['STUDENTS']);
            else
                return "";

            break;

        case 'AVERAGE_ATTENDANCE':
            $sum['AVERAGE_ATTENDANCE'] += (($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']) / $cal_days);
            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return round(($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']) / $cal_days, 1);
            else
                return "";

            break;

        case 'AVERAGE_ABSENT':
            $sum['AVERAGE_ABSENT'] += ($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'] / $cal_days);
            if ($atten_possible[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['ST_ID'])
                return round($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'] / $cal_days, 1);
            else
                return "";

            break;
    }
}

?>
