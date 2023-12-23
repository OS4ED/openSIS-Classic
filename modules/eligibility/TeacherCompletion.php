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
error_reporting(0);
include('../../RedirectModulesInc.php');
$start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\' AND TITLE IN (\'' . 'START_DAY' . '\',\'' . 'END_DAY' . '\')'));
if (count($start_end_RET)) {
    foreach ($start_end_RET as $value)
        $$value['TITLE'] = $value['VALUE'];
}
switch (date('D')) {
    case 'Mon':
        $today = 1;
        break;
    case 'Tue':
        $today = 2;
        break;
    case 'Wed':
        $today = 3;
        break;
    case 'Thu':
        $today = 4;
        break;
    case 'Fri':
        $today = 5;
        break;
    case 'Sat':
        $today = 6;
        break;
    case 'Sun':
        $today = 7;
        break;
}
//time();
$start = time() - ($today - $START_DAY) * 60 * 60 * 24;
$end = time();
if (!$_REQUEST['start_date']) {
    $start_time = $start;
    $start_date = strtoupper(date('d-M-y', $start_time));
    $end_date = strtoupper(date('d-M-y', $end));
} else {
    $start_time = $_REQUEST['start_date'];
    $start_date = strtoupper(date('d-M-y', $start_time));
    $end_date = strtoupper(date('d-M-y', $start_time + 60 * 60 * 24 * 7));
}
$QI = DBQuery('SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER ');
$periods_RET = DBGet($QI);
$period_select = "<SELECT class=\"form-control\" name=period><OPTION value=''>All</OPTION>";
foreach ($periods_RET as $period)
    $period_select .= "<OPTION value=$period[PERIOD_ID]" . (($_REQUEST['period'] == $period['PERIOD_ID']) ? ' SELECTED' : '') . ">" . $period['TITLE'] . "</OPTION>";
$period_select .= "</SELECT>";
DrawBC(""._extracurricular." > " . ProgramTitle());

echo '<div class="panel panel-default">';
echo "<FORM class=\"no-margin-bottom\" name=teach_comp id=teach_comp action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=POST>";

$begin_year = DBGet(DBQuery('SELECT min(unix_timestamp(SCHOOL_DATE)) as SCHOOL_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
$begin_year = $begin_year[1]['SCHOOL_DATE'];

if ($start && $begin_year) {
    $date_select = "<OPTION value=$start>" . date('M d, Y', $start) . ' - ' . date('M d, Y', $end) . '</OPTION>';
    for ($i = $start - (60 * 60 * 24 * 7); $i >= $begin_year; $i-=(60 * 60 * 24 * 7))
        $date_select .= "<OPTION value=$i" . (($i + 86400 >= $start_time && $i - 86400 <= $start_time) ? ' SELECTED' : '') . ">" . date('M d, Y', $i) . ' - ' . date('M d, Y', ($i + 1 + (($END_DAY - $START_DAY)) * 60 * 60 * 24)) . '</OPTION>';
}

echo '<div class="panel-heading">';
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div class="form-inline"><SELECT name=start_date class=form-control>' . $date_select . '</SELECT> &nbsp; ' . $period_select, ' &nbsp; <INPUT type=submit class="btn btn-primary" value='._go.' onclick=\'formload_ajax("teach_comp");\'></div>';
echo '</div>'; //.col-md-4
echo '</div>'; //.row
echo '</div>'; //.panel-heading
echo '</FORM>';
//+
$mp = GetAllMP('QTR', UserMP());

if (!isset($mp))
    $mp = GetAllMP('SEM', UserMP());

if (!isset($mp))
    $mp = GetAllMP('FY', UserMP());



$sql = 'SELECT CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME) AS FULL_NAME,sp.TITLE,cpv.PERIOD_ID,s.STAFF_ID 
		FROM staff s,course_periods cp,school_periods sp,course_period_var cpv
		WHERE 
			sp.PERIOD_ID = cpv.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
			AND cp.TEACHER_ID=s.STAFF_ID AND cp.MARKING_PERIOD_ID IN (' . $mp . ')
			AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND s.PROFILE=\'teacher\'
			' . ((optional_param('period', '', PARAM_SPCL)) ? ' AND cpv.PERIOD_ID=\'' . optional_param('period', '', PARAM_SPCL) . "'" : '') .
        'AND NOT EXISTS (SELECT \'\' FROM eligibility_completed ac WHERE ac.STAFF_ID=cp.TEACHER_ID AND ac.PERIOD_ID = sp.PERIOD_ID AND ac.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', $start_time) . '\' AND \'' . date('Y-m-d', $start_time + 60 * 60 * 24 * 7) . '\')';

$RET = DBGet(DBQuery($sql), array(), array('STAFF_ID', 'PERIOD_ID'));

$i = 0;
if (count($RET)) {
    foreach ($RET as $staff_id => $periods) {
        $i++;
        $staff_RET[$i]['FULL_NAME'] = $periods[key($periods)][1]['FULL_NAME'];
        foreach ($periods as $period_id => $period)
            $staff_RET[$i][$period_id] = '<i class="fa fa-times text-danger fa-lg"></i>';
    }
}
$columns = array('FULL_NAME' =>_teacher);
if (!$_REQUEST['period']) {
    foreach ($periods_RET as $period)
        $columns[$period['PERIOD_ID']] = $period['TITLE'];
}
echo '<hr class="no-margin"/>';
//echo '<div class="table-responsive">';
ListOutput($staff_RET, $columns, _teacherWhoHasnTEnteredEligibility, _teachersWhoHavenTEnteredEligibility);
//echo "</div>";

echo '</div>'; //.panel.panel-default
?>