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
if ($_REQUEST['modfunc'] == 'attn') {
    header("Location:Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php");
}
if ($_REQUEST['From'] && $_REQUEST['to']) {
    $_SESSION['from_date'] = $_REQUEST['From'];
    $_SESSION['to_date'] = $_REQUEST['to'];
}
$From = $_SESSION['from_date'];
$to = $_SESSION['to_date'];

# ------------------------ Old Query It's Also Working Start ---------------------------------- #
# ------------------------ Old Query It's Also Working End ---------------------------------- #

if ($From && $to) {
    $RET = DBGET(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,cpv.ID AS CPV_ID,mi.SCHOOL_DATE,cp.TITLE, mi.COURSE_PERIOD_ID FROM missing_attendance mi,course_periods cp,schools s,course_period_var cpv WHERE mi.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID=mi.PERIOD_ID AND s.ID=mi.SCHOOL_ID AND mi.SCHOOL_ID=\'' . UserSchool() . '\' AND (mi.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR mi.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND mi.SCHOOL_DATE>=\'' . $From . '\' AND mi.SCHOOL_DATE<\'' . $to . '\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) ORDER BY cp.TITLE,mi.SCHOOL_DATE'), array('SCHOOL_DATE' => 'ProperDate'));
} else {
    unset($RET);
}

if ((!UserStudentID() || substr($_REQUEST['modname'], 0, 5) == 'users')) {
    $RET_Users = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\''));
    DrawHeader('Selected User: ' . $RET_Users[1]['FIRST_NAME'] . '&nbsp;' . $RET_Users[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Side.php?modname=' . $_REQUEST['modname'] . '&staff_id=new&From=' . $From . '&to=' . $to . ' ><i class="icon-square-left"></i> Back to User List</A></span>');
}

if (count($RET)) {
    echo '<div class="alert bg-danger alert-styled-left"><b>Warning!!</b> - Teachers have missing attendance data.</div>';

    $modname = "users/TeacherPrograms.php?include=attendance/TakeAttendance.php&miss_attn=1&From=$From&to=$to";
    $link['remove']['link'] = "Modules.php?modname=$modname&modfunc=attn&username=admin";
    $link['remove']['variables'] = array('date' => 'SCHOOL_DATE', 'cp_id' => 'COURSE_PERIOD_ID', 'cpv_id' => 'CPV_ID');
    $_SESSION['miss_attn'] = 1;
    echo '<div class="panel-body">';
    ListOutput_missing_attn($RET, array('SCHOOL_DATE' => 'Date', 'TITLE' => 'Period -Teacher', 'SCHOOL' => 'School'), 'Period', 'Periods', $link, array(), array('save' => false, 'search' => false));
    echo '</div>'; //.panel-body
} else {
    echo '<div class="panel-body">';
    echo '<div class="alert bg-danger alert-styled-left">attendance completed for this teacher.</div>';
    echo '</div>'; //.panel-body
}
?>
