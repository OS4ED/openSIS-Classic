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

DrawBC(_extracurricular . " > " . ProgramTitle());

echo '<div class="panel">';
echo '<div class="panel-default">';
echo '<div class="">';
$start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\''));
if (is_countable($start_end_RET) && count($start_end_RET)) {
    foreach ($start_end_RET as $value) {
        if ($value['TITLE'] == 'START_DAY')
            $START_DAY = $value['VALUE'];
        if ($value['TITLE'] == 'START_HOUR')
            $START_HOUR = $value['VALUE'];
        if ($value['TITLE'] == 'START_MINUTE')
            $START_MINUTE = $value['VALUE'];
        if ($value['TITLE'] == 'START_M')
            $START_M = $value['VALUE'];
        if ($value['TITLE'] == 'END_DAY')
            $END_DAY = $value['VALUE'];
        if ($value['TITLE'] == 'END_HOUR')
            $END_HOUR = $value['VALUE'];
        if ($value['TITLE'] == 'END_MINUTE')
            $END_MINUTE = $value['VALUE'];
        if ($value['TITLE'] == 'END_M')
            $END_M = $value['VALUE'];
//		$$value['TITLE'] = $value['VALUE'];
    }
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
$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
if (strlen($START_MINUTE) == 1)
    $START_MINUTE = '0' . $START_MINUTE;
if (strlen($END_MINUTE) == 1)
    $END_MINUTE = '0' . $END_MINUTE;
$start_date = strtoupper(date('d-M-y', mktime(0) - ($today - $START_DAY) * 60 * 60 * 24));
$end_date = strtoupper(date('d-M-y', mktime(0) + ($END_DAY - $today) * 60 * 60 * 24));
$current_RET = DBGet(DBQuery('SELECT ELIGIBILITY_CODE,STUDENT_ID FROM eligibility WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''), array(), array('STUDENT_ID'));
if (optional_param('modfunc', '', PARAM_NOTAGS) == 'gradebook') {

    $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\''), array(), array('TITLE'));
    if (is_countable($config_RET) && count($config_RET))
        foreach ($config_RET as $title => $value)
            $programconfig[User('STAFF_ID')][$title] = rtrim($value[1]['VALUE'], '_' . UserCoursePeriod());
    else
        $programconfig[User('STAFF_ID')] = true;
    include_once 'functions/MakeLetterGradeFnc.php';

    $course_period_id = UserCoursePeriod();
    $course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
    $course_id = $course_id[1]['COURSE_ID'];

    $grades_RET = DBGet(DBQuery('SELECT ID,TITLE,GPA_VALUE,UNWEIGHTED_GP FROM report_card_grades WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''), array(), array('ID'));

    $has_quarters = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM school_quarters WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool()));
    if ($programconfig[User('STAFF_ID')]['WEIGHT'] == 'Y')
        $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID,gt.ASSIGNMENT_TYPE_ID,     gt.ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID' . ($programconfig[User('STAFF_ID')]['ELIGIBILITY_CUMULITIVE'] == 'Y' && $has_quarters[1]['REC_EX'] > 0 ? ' IN (' . GetChildrenMP('SEM', UserMP()) . ')' : '=\'' . UserMP() . '\'') . ') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID),gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\'' . $course_id . '\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
    else
        $points_RET = DBGet(DBQuery('SELECT s.STUDENT_ID,\'' . '-1' . '\' AS ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,\'' . '1' . '\' AS FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID' . ($programconfig[User('STAFF_ID')]['ELIGIBILITY_CUMULITIVE'] == 'Y' && $has_quarters[1]['REC_EX'] > 0 ? ' IN (' . GetChildrenMP('SEM', UserMP()) . ')' : '=\'' . UserMP() . '\'') . ') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID) WHERE ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE'), array(), array('STUDENT_ID'));

    if (is_countable($points_RET) && count($points_RET)) {
        foreach ($points_RET as $student_id => $student) {
            $total = $total_percent = 0;
            foreach ($student as $partial_points)
                if ($partial_points['PARTIAL_TOTAL'] != 0) {
                    $total += $partial_points['PARTIAL_POINTS'] * $partial_points['FINAL_GRADE_PERCENT'] / $partial_points['PARTIAL_TOTAL'];
                    $total_percent += $partial_points['FINAL_GRADE_PERCENT'];
                }
            if ($total_percent != 0)
                $total /= $total_percent;

            $grade = $grades_RET[_makeLetterGrade($total, 0, 0, 'ID')][1];
            if ($programconfig[User('STAFF_ID')]['WEIGHT'] == 'Y') {
                if ($grade['UNWEIGHTED_GP'] == '0' || !$grade['UNWEIGHTED_GP'])
                    $code = 'FAILING';
                elseif (strpos($grade['TITLE'], 'D') !== false || $grade['UNWEIGHTED_GP'] < 2)
                    $code = 'BORDERLINE';
                else
                    $code = 'PASSING';
            }
            else {
                if ($grade['GPA_VALUE'] == '0' || !$grade['GPA_VALUE'])
                    $code = 'FAILING';
                elseif (strpos($grade['TITLE'], 'D') !== false || $grade['GPA_VALUE'] < 2)
                    $code = 'BORDERLINE';
                else
                    $code = 'PASSING';
            }

            if ($current_RET[$student_id])
                $sql = 'UPDATE eligibility SET ELIGIBILITY_CODE=\'' . $code . '\' WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND STUDENT_ID=\'' . $student_id . '\'';
            else
                $sql = 'INSERT INTO eligibility (STUDENT_ID,SCHOOL_DATE,SYEAR,PERIOD_ID,COURSE_PERIOD_ID,ELIGIBILITY_CODE) values(\'' . $student_id . '\',\'' . DBDate() . '\',\'' . UserSyear() . '\',\'' . UserPeriod() . '\',\'' . $course_period_id . '\',\'' . $code . '\')';
            DBQuery($sql);
        }
        $current_RET = DBGet(DBQuery('SELECT ELIGIBILITY_CODE,STUDENT_ID FROM eligibility WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''), array(), array('STUDENT_ID'));
    }
}

if ($_REQUEST['values'] && ($_POST['values'] || $_REQUEST['ajax'])) {

    $course_period_id = UserCoursePeriod();
    foreach ($_REQUEST['values'] as $student_id => $value) {

        if ($current_RET[$student_id])
            $sql = 'UPDATE eligibility SET ELIGIBILITY_CODE=\'' . $value . '\' WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND PERIOD_ID=\'' . UserPeriod() . '\' AND STUDENT_ID=\'' . $student_id . '\'';
        else
            $sql = 'INSERT INTO eligibility (STUDENT_ID,SCHOOL_DATE,SYEAR,PERIOD_ID,COURSE_PERIOD_ID,ELIGIBILITY_CODE) values(\'' . $student_id . '\',\'' . DBDate() . '\',\'' . UserSyear() . '\',\'' . UserPeriod() . '\',\'' . $course_period_id . '\',\'' . $value . '\')';

        DBQuery($sql);
    }

    $RET = DBGet(DBQuery('SELECT \'completed\' AS COMPLETED FROM eligibility_completed WHERE STAFF_ID=\'' . User('STAFF_ID') . '\' AND SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND PERIOD_ID=\'' . UserPeriod() . '\''));
    if (!count($RET)) {

        DBQuery('INSERT INTO eligibility_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID) values(\'' . User('STAFF_ID') . '\',\'' . DBDate() . '\',\'' . UserPeriod() . '\')');
    }
    $current_RET = DBGet(DBQuery('SELECT ELIGIBILITY_CODE,STUDENT_ID FROM eligibility WHERE SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' AND PERIOD_ID=\'' . UserPeriod() . '\''), array(), array('STUDENT_ID'));

    $_SESSION['CpvId'] = $_REQUEST['cpv_id'];
}
$extra['SELECT'] .= ",'' AS PASSING,'' AS BORDERLINE,'' AS FAILING,'' AS INCOMPLETE";
$extra['functions'] = array('PASSING' => 'makeRadio', 'BORDERLINE' => 'makeRadio', 'FAILING' => 'makeRadio', 'INCOMPLETE' => 'makeRadio');
$columns = array('PASSING' =>_passing,
 'BORDERLINE' =>_borderline,
 'FAILING' =>_failing,
 'INCOMPLETE' =>_incomplete,
);

$stu_RET = GetStuList($extra);

echo "<FORM ACTION=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=POST>";

echo'<input type=hidden name=cpv_id value="' . CpvId() . '">';
if ($today > $END_DAY || $today < $START_DAY || ($today == $START_DAY && date('Gi') < ($START_HOUR . $START_MINUTE)) || ($today == $END_DAY && date('Gi') > ($END_HOUR . $END_MINUTE))) {
    //echo '<h4 class="m-t-0 text-primary">'.ProgramTitle().'</h4>';
    if ($START_HOUR > 12) {
        $START_HOUR-=12;
        $START_M = 'PM';
    } else
        $START_M = 'AM';

    if ($END_HOUR > 12) {
        $END_HOUR-=12;
        $END_M = 'PM';
    } else
        $END_M = 'AM';

    echo '<div class="alert alert-primary alert-styled-left">'._youCanOnlyEnterEligibilityFrom.' ' . $days[$START_DAY] . ' ' . $START_HOUR . ':' . $START_MINUTE . ' ' . $START_M . ' to ' . $days[$END_DAY] . ' ' . $END_HOUR . ':' . $END_MINUTE . ' ' . $END_M . '</div>';
}
else {
    if (count($stu_RET) != 0) {
        echo '<div class="panel-heading">';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h5 class="text-pink m-b-0">' . ProgramTitle() . '</h5>';
        echo '</div>';
        echo '<div class="col-md-6 text-right"><A class="btn btn-default" href="Modules.php?modname=' . $_REQUEST[modname] . '&modfunc=gradebook">'._useGradebookGrades.'</A> &nbsp; <INPUT type=submit class="btn btn-primary" value='._save.' onclick="self_disable(this);"></div>';
        echo '</div>';
        echo '</div>';
        echo '<hr class="no-margin" />';
    }

    $LO_columns = array('FULL_NAME' =>_student,
     'STUDENT_ID' =>_studentId,
     'GRADE_ID' =>_grade,
     ) + $columns;
    ListOutput($stu_RET, $LO_columns, _student, _students);
    if (count($stu_RET) != 0)
        echo '<div class="panel-footer text-center"><INPUT type=submit class="btn btn-primary" value='._save.' onclick="self_disable(this);"></div>';
}
echo "</FORM>";
echo '</div>'; //.panel-body
echo '</div>';
echo '</div>';

function makeRadio($value, $title) {

    $colors = array('PASSING' => '#00FF00', 'BORDERLINE' => '#FF0000', 'FAILING' => '#FFCC00', 'INCOMPLETE' => '#0000FF');
    global $THIS_RET, $current_RET;

    if ((isset($current_RET[$THIS_RET['STUDENT_ID']][1]['ELIGIBILITY_CODE']) && $current_RET[$THIS_RET['STUDENT_ID']][1]['ELIGIBILITY_CODE'] == $title))
        return "<TABLE align=center" . ($colors[$title] ? ' bgcolor=' . $colors[$title] : '') . "><TR><TD><INPUT type=radio name=values[" . $THIS_RET['STUDENT_ID'] . "] value='$title' CHECKED></TD></TR></TABLE>";
    else if ($title == 'PASSING' && !$current_RET[$THIS_RET['STUDENT_ID']][1]['ELIGIBILITY_CODE'])
        return "<TABLE align=center bgcolor=#DDFFDD><TR><TD><INPUT type=radio name=values[" . $THIS_RET['STUDENT_ID'] . "] value='$title' CHECKED></TD></TR></TABLE>";
    else
        return "<TABLE align=center><TR><TD><INPUT type=radio name=values[" . $THIS_RET['STUDENT_ID'] . "] value='$title'></TD></TR></TABLE>";
}

?>
