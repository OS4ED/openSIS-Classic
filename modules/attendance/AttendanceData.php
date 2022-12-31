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
} else {
    $start_date = date('Y-m') . '-01';
}
if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end']) {
    $end_date = $_REQUEST['year_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['day_end'];
    $end_date = ProperDateMAvr($end_date);
} else {
    $end_date = ProperDateMAvr();
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
    $btn = '<div class="p-l-20">' . Buttons(_submit,'','onclick="self_disable(this);"') . '</div>';
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
    echo '<div class="panel-heading">';
    echo "<FORM class=\"form-horizontal clearfix m-b-0\" action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&list_by_day=" . strip_tags(trim($_REQUEST['list_by_day'])) . " method=POST>";

    $advanced_link = "<A class=text-pink HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=search&list_by_day=$_REQUEST[list_by_day]&day_start=$_REQUEST[day_start]&day_end=$_REQUEST[day_end]&month_start=$_REQUEST[month_start]&month_end=$_REQUEST[month_end]&year_start=$_REQUEST[year_start]&year_end=$_REQUEST[year_end]><i class=\"icon-cog\"></i> "._advanced."</A>";
    ///////////////////////Old Date Picker///////////////////////////////
    ///////////////////////New Date Picker///////////////////////////////
    echo '<div class="form-inline"><div class="col-md-12"><div class="inline-block">' . DateInputAY($start_date, 'start', 1) . '</div> &nbsp; &nbsp; - &nbsp; &nbsp; <div class="inline-block">' . DateInputAY($end_date, 'end', 2) . '</div> &nbsp; &nbsp; <label class="checkbox-inline checkbox-switch switch-success switch-xs"><input type="checkbox" value="Y" name="all_school" id="all_school" /><span></span>'._allSchool.'</label> &nbsp; <INPUT type=submit value='._go.' class="btn btn-primary"> &nbsp;' . $advanced_link . '</div></div>';
    echo '</FORM>';
    echo '</div>';


    if ($_REQUEST['list_by_day'] == 'true') {
        $cal_days = 1;


        $student_days_absent = DBGet(DBQuery('SELECT ad.SCHOOL_DATE,ssm.GRADE_ID,COALESCE(sum(ad.STATE_VALUE-1)*-1,0) AS STATE_VALUE FROM attendance_day ad,student_enrollment ssm,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ad.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ad.SYEAR=ssm.SYEAR AND ad.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND (ad.SCHOOL_DATE BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND ssm.START_DATE <= ad.SCHOOL_DATE)) ' . $extra['WHERE'] . ' GROUP BY ad.SCHOOL_DATE,ssm.GRADE_ID'), array(''), array('SCHOOL_DATE', 'GRADE_ID'));




        $sql_school = DBGet(DBQuery('SELECT DISTINCT SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID=' . User('STAFF_ID')));


        foreach ($sql_school as $school_key => $school_value) {
            $in_schools_raw .= ',' . $school_value['SCHOOL_ID'];
        }
        $in_schools_raw = $in_schools_raw;


        $in_schools = substr($in_schools_raw, 1);

        $permited_schools = explode(",", $in_schools);
        $sch_count = count($permited_schools);





        if ($sch_count == 1) {
            $student_days_possible = DBGet(DBQuery('SELECT ap.SCHOOL_DATE, CONCAT(s.FIRST_NAME, \' \', s.LAST_NAME,\' \') as STUDENTS, sg.TITLE as GRADE, sp.TITLE as PERIOD, ac.TITLE as STATUS from attendance_period ap, students s, school_gradelevels sg, attendance_codes ac, student_enrollment ssm, school_periods sp where ssm.syear=\'' . UserSyear() . '\' and ap.attendance_code=ac.id and ssm.grade_id=sg.id and ap.period_id=sp.period_id and ap.student_id=s.student_id and ssm.student_id=ap.student_id AND ssm.school_id in (' . $in_schools . ') AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' ' . $extra['WHERE'] . ' and ssm.id in (select max(id) from student_enrollment where syear=\'' . UserSyear() . '\' group by student_id)'), array('SCHOOL_DATE' => 'ProperDate', 'GRADE' => 'GRADE', 'STUDENTS' => 'STUDENTS', 'PRESENT' => '_makeByDay', 'ABSENT' => '_makeByDay', 'ADA' => '_makeByDay'));

            $columns = array('SCHOOL_DATE' => _date, 'STUDENTS' => ''._studentName.'', 'GRADE' => _grade, 'PERIOD' => _periodName, 'STATUS' => _attendanceStatus);
        } else {

            if ($_REQUEST['all_school'] == 'Y') {
                $student_days_possible = DBGet(DBQuery('SELECT ap.SCHOOL_DATE, CONCAT(s.FIRST_NAME, \' \', s.LAST_NAME,\' \') as STUDENTS, sg.TITLE as GRADE, sp.TITLE as PERIOD, ac.TITLE as STATUS, sc.TITLE AS SCHOOL from attendance_period ap, students s, school_gradelevels sg, attendance_codes ac, student_enrollment ssm, school_periods sp, schools sc where ssm.syear=\'' . UserSyear() . '\' and ap.attendance_code=ac.id and ssm.grade_id=sg.id and ap.period_id=sp.period_id and ap.student_id=s.student_id and ssm.student_id=ap.student_id AND sc.id=ssm.school_id AND ssm.school_id in (' . $in_schools . ') AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' ' . $extra['WHERE'] . ' and ssm.id in (select max(id) from student_enrollment where syear=\'' . UserSyear() . '\' group by student_id)'), array('SCHOOL_DATE' => 'ProperDate', 'GRADE' => 'GRADE', 'STUDENTS' => 'STUDENTS', 'PRESENT' => '_makeByDay', 'ABSENT' => '_makeByDay', 'ADA' => '_makeByDay'));
            } else {
                $student_days_possible = DBGet(DBQuery('SELECT ap.SCHOOL_DATE, CONCAT(s.FIRST_NAME, \' \', s.LAST_NAME,\' \') as STUDENTS, sg.TITLE as GRADE, sp.TITLE as PERIOD, ac.TITLE as STATUS, sc.TITLE AS SCHOOL from attendance_period ap, students s, school_gradelevels sg, attendance_codes ac, student_enrollment ssm, school_periods sp, schools sc where ssm.syear=\'' . UserSyear() . '\' and sc.id=\'' . UserSchool() . '\' and ap.attendance_code=ac.id and ssm.grade_id=sg.id and ap.period_id=sp.period_id and ap.student_id=s.student_id and ssm.student_id=ap.student_id AND sc.id=ssm.school_id AND ssm.school_id in (' . $in_schools . ') AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' ' . $extra['WHERE'] . ' and ssm.id in (select max(id) from student_enrollment where syear=\'' . UserSyear() . '\' group by student_id)'), array('SCHOOL_DATE' => 'ProperDate', 'GRADE' => 'GRADE', 'STUDENTS' => 'STUDENTS', 'PRESENT' => '_makeByDay', 'ABSENT' => '_makeByDay', 'ADA' => '_makeByDay'));
            }

            $columns = array('SCHOOL_DATE' => _date, 'STUDENTS' => ''._studentName.'', 'GRADE' => _grade, 'PERIOD' => _periodName, 'STATUS' => _attendanceStatus, 'SCHOOL' => _school);
        }
        ListOutput($student_days_possible, $columns,  _attendanceRecord, _attendanceRecords, $link);
    } else {
        $cal_days = DBGet(DBQuery('SELECT count(*) AS COUNT,CALENDAR_ID FROM attendance_calendar WHERE ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' SYEAR=\'' . UserSyear() . '\' AND SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' GROUP BY CALENDAR_ID'), array(), array('CALENDAR_ID'));
        $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND SCHOOL_ID=\'' . UserSchool() . '\'' : '')), array(), array('CALENDAR_ID'));



        $extra['WHERE'] .= ' GROUP BY ssm.GRADE_ID,ssm.CALENDAR_ID';

        $student_days_absent = DBGet(DBQuery('SELECT ssm.GRADE_ID,ssm.CALENDAR_ID,COALESCE(sum(ad.STATE_VALUE-1)*-1,0) AS STATE_VALUE FROM attendance_day ad,student_enrollment ssm,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ad.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ad.SYEAR=ssm.SYEAR AND ad.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND (ad.SCHOOL_DATE BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND ssm.START_DATE <= ad.SCHOOL_DATE)) ' . $extra['WHERE']), array(''), array('GRADE_ID', 'CALENDAR_ID'));
        $student_days_possible = DBGet(DBQuery('SELECT ssm.GRADE_ID,ssm.CALENDAR_ID,\'\' AS DAYS_POSSIBLE,count(*) AS ATTENDANCE_POSSIBLE,count(*) AS STUDENTS,\'\' AS PRESENT,\'\' AS ABSENT,\'\' AS ADA,\'\' AS AVERAGE_ATTENDANCE,\'\' AS AVERAGE_ABSENT FROM student_enrollment ssm,attendance_calendar ac,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ac.SYEAR=ssm.SYEAR AND ac.CALENDAR_ID=ssm.CALENDAR_ID AND ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ' : '') . ' ssm.SCHOOL_ID=ac.SCHOOL_ID AND (ac.SCHOOL_DATE BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND ssm.START_DATE <= ac.SCHOOL_DATE)) AND ac.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' ' . $extra['WHERE']), array('GRADE_ID' => '_make', 'STUDENTS' => '_make', 'PRESENT' => '_make', 'ABSENT' => '_make', 'ADA' => '_make', 'AVERAGE_ATTENDANCE' => '_make', 'AVERAGE_ABSENT' => '_make', 'DAYS_POSSIBLE' => '_make'));

        $columns = array('GRADE_ID' => _grade,
         'STUDENTS' => _totalAttendance,
         'DAYS_POSSIBLE' => _daysPossible,
         'PRESENT' => _present,
         'ABSENT' => _absent,
         'ADA' => _ada,
         'AVERAGE_ATTENDANCE' => _averageAttendance,
         'AVERAGE_ABSENT' => _averageAbsent,
        );
        $link['add']['html'] = array('GRADE_ID' => '<b>'._total.'</b>', 'STUDENTS' => round($sum['STUDENTS'], 1), 'DAYS_POSSIBLE' => $cal_days[key($cal_days)][1]['COUNT'], 'PRESENT' => $sum['PRESENT'], 'ADA' => Percent(($sum['PRESENT']) / ($sum['PRESENT'] + $sum['ABSENT'])), 'ABSENT' => $sum['ABSENT'], 'AVERAGE_ATTENDANCE' => round($sum['AVERAGE_ATTENDANCE'], 1), 'AVERAGE_ABSENT' => round($sum['AVERAGE_ABSENT'], 1));

        ListOutput($student_days_possible, $columns, '', '', $link);
    }
    echo '</div>'; //.panel
}

function _make($value, $column) {
    global $THIS_RET, $student_days_absent, $cal_days, $sum, $calendars_RET;

    switch ($column) {
        case 'STUDENTS':
            $sum['STUDENTS'] += $value;
            return $value;
            break;

        case 'DAYS_POSSIBLE':
            return $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'];
            break;

        case 'PRESENT':
            $sum['PRESENT'] += ($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE']);
            return $THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'];
            break;

        case 'ABSENT':
            $sum['ABSENT'] += ($student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE']);
            return $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'];
            break;

        case 'ADA':
            return Percent((($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'])) / $THIS_RET['STUDENTS']);
            break;

        case 'AVERAGE_ATTENDANCE':
            $sum['AVERAGE_ATTENDANCE'] += (($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE']) / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT']);
            return round(($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE']) / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'], 1);
            break;

        case 'AVERAGE_ABSENT':
            $sum['AVERAGE_ABSENT'] += ($student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'] / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT']);
            return round($student_days_absent[$THIS_RET['GRADE_ID']][$THIS_RET['CALENDAR_ID']][1]['STATE_VALUE'] / $cal_days[$THIS_RET['CALENDAR_ID']][1]['COUNT'], 1);
            break;

        case 'GRADE_ID':
            return GetGrade($value) . (count($cal_days) > 1 ? ' - ' . $calendars_RET[$THIS_RET['CALENDAR_ID']][1]['TITLE'] : '');
    }
}

function _makeByDay($value, $column) {
    global $THIS_RET, $student_days_absent, $cal_days, $sum;

    switch ($column) {
        case 'STUDENTS':

            break;

        case 'DAYS_POSSIBLE':
            return $cal_days;
            break;

        case 'PRESENT':
            $sum['PRESENT'] += ($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']);
            return $THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'];
            break;

        case 'ABSENT':
            $sum['ABSENT'] += ($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']);
            return $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'];
            break;

        case 'ADA':
            return Percent((($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'])) / $THIS_RET['STUDENTS']);
            break;

        case 'AVERAGE_ATTENDANCE':
            $sum['AVERAGE_ATTENDANCE'] += (($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']) / $cal_days);
            return round(($THIS_RET['ATTENDANCE_POSSIBLE'] - $student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE']) / $cal_days, 1);
            break;

        case 'AVERAGE_ABSENT':
            $sum['AVERAGE_ABSENT'] += ($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'] / $cal_days);
            return round($student_days_absent[$THIS_RET['SCHOOL_DATE']][$THIS_RET['GRADE_ID']][1]['STATE_VALUE'] / $cal_days, 1);
            break;
    }
}

?>
