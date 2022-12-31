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

$stu_missing_atten = DBGet(DBQuery('SELECT * FROM missing_attendance WHERE syear=\'' . UserSyear() . '\''));

foreach ($stu_missing_atten as $k => $f) {

    $pr_id = $f['PERIOD_ID'];
    $sch_date = $f['SCHOOL_DATE'];
    $staff_id = $f['TEACHER_ID'];
    $c_id = $f['COURSE_PERIOD_ID'];
    $sch_qr = DBGet(DBQuery('SELECT distinct(student_id) FROM schedule  WHERE  (END_DATE IS NULL OR END_DATE>=\'' . $sch_date . '\') AND START_DATE<=\'' . $sch_date . '\' AND course_period_id=' . $c_id));
    $att_qr = DBGet(DBQuery('SELECT distinct(student_id) FROM attendance_period  where SCHOOL_DATE=\'' . $sch_date . '\' AND PERIOD_ID=' . $pr_id . ' AND course_period_id=' . $c_id));

    if (count($sch_qr) == count($att_qr)) {

        DBQuery('DELETE FROM missing_attendance WHERE  TEACHER_ID=' . $staff_id . ' AND SCHOOL_DATE=\'' . $sch_date . '\' AND PERIOD_ID=' . $pr_id);
    }
}

if ($From && $to) {
    $RET = DBGET(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,cpv.ID AS CPV_ID,mi.SCHOOL_DATE,cp.TITLE, mi.COURSE_PERIOD_ID FROM missing_attendance mi,course_periods cp,schools s,course_period_var cpv WHERE mi.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID=mi.PERIOD_ID AND s.ID=mi.SCHOOL_ID AND mi.SCHOOL_ID=\'' . UserSchool() . '\' AND mi.SYEAR=\'' . UserSyear() . '\' AND (mi.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR mi.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND mi.SCHOOL_DATE>=\'' . $From . '\' AND mi.SCHOOL_DATE<\'' . $to . '\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) ORDER BY cp.TITLE,mi.SCHOOL_DATE'), array('SCHOOL_DATE' => 'ProperDate'));
} else {
    unset($RET);
}

if ((!UserStudentID() || substr($_REQUEST['modname'], 0, 5) == 'users')) {
    $RET_Users = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\''));
    DrawHeader(''._selectedUser.': ' . $RET_Users[1]['FIRST_NAME'] . '&nbsp;' . $RET_Users[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Side.php?modname=' . $_REQUEST['modname'] . '&staff_id=new&From=' . $From . '&to=' . $to . ' ><i class="icon-square-left"></i> '._selectedUser.'</A></span>');
}

if (is_countable($RET) && count($RET)) {
    echo '<div class="panel-body p-b-0"><div class="alert alert-warning alert-styled-left m-b-0"><b>'._warning.'!!</b> - '._teachersHaveMissingAttendanceData.'.</div></div>';

    $modname = "users/TeacherPrograms.php?include=attendance/TakeAttendance.php&miss_attn=1&From=$From&to=$to";
    $link['remove']['link'] = "Modules.php?modname=$modname&modfunc=attn&username=admin";
    $link['remove']['variables'] = array('date' => 'SCHOOL_DATE', 'cp_id' => 'COURSE_PERIOD_ID', 'cpv_id' => 'CPV_ID');
    $_SESSION['miss_attn'] = 1;
    echo '<div class="panel-body">';
    ListOutput_missing_attn($RET, array('SCHOOL_DATE' => 'Date', 'TITLE' => 'Period -Teacher', 'SCHOOL' => 'School'), _period, _periods, $link, array(), array('save' =>false, 'search' =>false));
    echo '</div>'; //.panel-body
} else {
    echo '<div class="panel-body">';
    echo '<div class="alert alert-danger no-border"><i class="fa fa-info-circle"></i> '._attendanceCompletedForThisTeacher.'.</div>';
    echo '</div>'; //.panel-body
}
?>
