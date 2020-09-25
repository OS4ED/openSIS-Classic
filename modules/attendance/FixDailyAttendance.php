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
$message = '<div class="form-inline"><div class="col-md-12"><div class="form-group"><label class="control-label">'._from.'</label></div><div class="inline-block">' . DateInputAY(DBDate('mysql'), 'min', 1) . '</div><div class="form-group"><label class="control-label">'._to.'</label></div><div class="inline-block">' . DateInputAY(DBDate('mysql'), 'max', 2) . '</div></div></div><br/>';
if (Prompt_Home('Confirm', ''._whenDoYouWantToRecalculateTheDailyAttendance.'?', $message)) {
    $current_RET = DBGet(DBQuery('SELECT DISTINCT DATE_FORMAT(SCHOOL_DATE,\'%d-%m-%Y\') as SCHOOL_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''), array(), array('SCHOOL_DATE'));
    $extra = array();
    $students_RET = GetStuList($extra);
//            print_r($current_RET);
    $begin = mktime(0, 0, 0, MonthNWSwitch($_REQUEST['month_min'], 'to_num'), $_REQUEST['day_min'] * 1, $_REQUEST['year_min']) + 43200;
    $end = mktime(0, 0, 0, MonthNWSwitch($_REQUEST['month_max'], 'to_num'), $_REQUEST['day_max'] * 1, $_REQUEST['year_max']) + 43200;

    for ($i = $begin; $i <= $end; $i += 86400) {
        if ($current_RET[date('d-m-Y', $i)]) {
            foreach ($students_RET as $student) {
                UpdateAttendanceDaily($student['STUDENT_ID'], date('Y-m-d', $i));
            }
        }
    }

    unset($_REQUEST['modfunc']);
    echo '<div class="alert bg-success alert-styled-left">The Daily Attendance for that timeframe has been recalculated.</div>';
}
?>