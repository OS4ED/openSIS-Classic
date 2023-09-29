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
DrawBC("" . _gradebook . " > " . ProgramTitle());

include_once 'functions/MakeLetterGradeFnc.php';
include_once 'functions/MakePercentGradeFnc.php';

$max_allowed = Preferences('ANOMALOUS_MAX', 'Gradebook') / 100;
// if running as a teacher program then openSIS[allow_edit] will already be set according to admin permissions
if (!isset($_openSIS['allow_edit']))
    $_openSIS['allow_edit'] = true;

$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\''), array(), array('TITLE'));
if (is_countable($config_RET) && count($config_RET))
    foreach ($config_RET as $title => $value) {
        $unused_var = explode('_', $value[1]['VALUE']);
        $programconfig[User('STAFF_ID')][$title] = $unused_var[0];
        //		$programconfig[User('STAFF_ID')][$title] = rtrim($value[1]['VALUE'],'_'.UserCoursePeriod());
    }
else
    $programconfig[User('STAFF_ID')] = true;
$course_period_id = UserCoursePeriod();
$course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
$course_id = $course_id[1]['COURSE_ID'];
//echo 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.POINTS,ga.DUE_DATE,gt.TITLE AS TYPE_TITLE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignments ga,gradebook_assignment_types gt WHERE ((ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') OR ga.COURSE_PERIOD_ID=\'' . $course_period_id . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID ORDER BY ga.' . Preferences('ASSIGNMENT_SORTING', 'Gradebook') . ' DESC';
$assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.POINTS,ga.DUE_DATE,gt.TITLE AS TYPE_TITLE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignments ga,gradebook_assignment_types gt WHERE ((ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') OR ga.COURSE_PERIOD_ID=\'' . $course_period_id . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID ORDER BY ga.' . Preferences('ASSIGNMENT_SORTING', 'Gradebook') . ' DESC'), array(), array('ASSIGNMENT_ID'));
//$assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.POINTS,ga.DUE_DATE,gt.TITLE AS TYPE_TITLE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignments ga,gradebook_assignment_types gt WHERE ((ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') OR ga.COURSE_PERIOD_ID=\'' . $course_period_id . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID ORDER BY ga.ASSIGNED_DATE DESC'), array(), array('ASSIGNMENT_ID'));
// when changing course periods the assignment_id will be wrong except for '' (totals) and 'all'
if ($_REQUEST['assignment_id'] && $_REQUEST['assignment_id'] != 'all') {
    foreach ($assignments_RET as $id => $assignment)
        if ($_REQUEST['assignment_id'] == $id) {
            $found = true;
            break;
        }
    if (!$found)
        unset($_REQUEST['assignment_id']);
}
####################
if (clean_param($_REQUEST['student_id'], PARAM_INT)) {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));

    $count_student_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM students"));

    echo '<div class="panel ' . (UserStaffId() != '' ? 'm-b-0' : '') . '">';
    echo '<div class="panel-heading">';

    if ($count_student_RET[1]['NUM'] > 1) {
        if (UserStaffId() != '' && SelfStaffProfile('PROFILE') == 'admin')
            DrawHeaderHome('<h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6>  <div class="heading-elements clearfix"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=' . $_REQUEST['modname'] . '&ajax=true&bottom_back=true&return_session=true&staff_id=' . UserStaffId() . ($_REQUEST['period'] != '' ? '&period=' . $_REQUEST['period'] : '') . ' target=body><i class="icon-square-left"></i> ' . _backToStudentList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div>');
        else
            DrawHeaderHome('<h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements clearfix"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=' . $_REQUEST['modname'] . '&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToStudentList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div>');
    } else if ($count_student_RET[1]['NUM'] == 1) {
        DrawHeaderHome('<h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements clearfix"><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div>');
    }

    echo '</div>'; //.panel-heading
    echo '</div>'; //.panel
}

echo '<div class="panel panel-default">';
// echo '<div class="panel-heading">';

// echo '</div>'; //.panel-heading


####################
if (clean_param($_REQUEST['student_id'], PARAM_INT)) {
    if ($_REQUEST['student_id'] != $_SESSION['student_id']) {
        $_SESSION['student_id'] = $_REQUEST['student_id'];
        //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }
    $_REQUEST['stuid'] = $_REQUEST['student_id'];
    $LO_columns = array(
        'TYPE_TITLE' => _category,
        'TITLE' => _assignment,
        'POINTS' => _points,
        'LETTER_GRADE' => _grade,
        'COMMENT' => _comment,
    );
    $item = 'Assignment';
    $items = 'Assignments';
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&include_inactive=$_REQUEST[include_inactive]";
    $link['TITLE']['variables'] = array('assignment_id' => 'ASSIGNMENT_ID');
    $current_RET[$_REQUEST['student_id']] = DBGet(DBQuery('SELECT g.ASSIGNMENT_ID FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND g.STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\'' . ($_REQUEST['assignment_id'] == 'all' ? '' : ' AND g.ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\'')), array(), array('ASSIGNMENT_ID'));
    if (is_countable($assignments_RET) && count($assignments_RET)) {
        foreach ($assignments_RET as $id => $assignment)
            $total_points[$id] = $assignment[1]['POINTS'];
    }
    $count_assignments = count($assignments_RET);
    $extra['SELECT'] = ',ga.ASSIGNMENT_ID,gt.TITLE AS TYPE_TITLE,ga.TITLE,ga.POINTS AS TOTAL_POINTS,\'\' AS LETTER_GRADE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE';
    $extra['SELECT'] .= ',(SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) AS POINTS';
    $extra['SELECT'] .= ',(SELECT COMMENT FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) AS COMMENT';
    $extra['FROM'] = ',gradebook_assignments ga,gradebook_assignment_types gt';
    $extra['WHERE'] = 'AND (ga.due_date>=ssm.start_date OR ga.due_date IS NULL) AND ((ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') OR ga.COURSE_PERIOD_ID=\'' . $course_period_id . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID' . ($_REQUEST['assignment_id'] == 'all' ? '' : ' AND ga.ASSIGNMENT_ID=\'' . $_REQUEST[assignment_id] . '\' ');
    $extra['GROUP'] = Preferences('ASSIGNMENT_SORTING', 'Gradebook');
    $extra['ORDER_BY'] = Preferences('ASSIGNMENT_SORTING', 'Gradebook') . " DESC";
    $extra['functions'] = array('POINTS' => '_makeExtraStuCols', 'LETTER_GRADE' => '_makeExtraStuCols', 'COMMENT' => '_makeExtraStuCols');
} else {
    $LO_columns = array('FULL_NAME' => _student);
    if ($_REQUEST['assignment_id'] != 'all')
        $LO_columns += array('STUDENT_ID' => _studentId);
    if ($_REQUEST['include_inactive'] == 'Y')
        $LO_columns += array(
            'ACTIVE' => _schoolStatus,
            'ACTIVE_SCHEDULE' => _courseStatus,
        );
    $item = 'Student';
    $items = 'Students';
    $link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[modname]&include_inactive=$_REQUEST[include_inactive]&assignment_id=all" . ($_REQUEST['period'] != '' ? '&period=' . $_REQUEST['period'] : '');
    $link['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
    if ($_SESSION['student_id']) {
        unset($_SESSION['student_id']);
        //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }
    if (clean_param($_REQUEST['assignment_id'], PARAM_ALPHA) == 'all') {

        $current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.ASSIGNMENT_ID,g.POINTS FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\''), array(), array('STUDENT_ID', 'ASSIGNMENT_ID'));

        $count_extra = array('SELECT_ONLY' => 'ssm.STUDENT_ID');
        $count_students = GetStuList($count_extra);
        $count_students = count($count_students);

        $extra['SELECT'] = ',ssm.START_DATE';
        $extra['WHERE'] = ' AND \'' . DBDate('mysql') . '\'>=ssm.START_DATE';
        $extra['functions'] = array();
        if (is_countable($assignments_RET) && count($assignments_RET)) {
            foreach ($assignments_RET as $id => $assignment) {
                $assignment = $assignment[1];
                $extra['SELECT'] .= ',\'' . $id . '\' AS G' . $id . ',\'' . $assignment['DUE'] . '\' AS D' . $id . ',\'' . $assignment['DUE_DATE'] . '\' AS DUE_' . $id . '';

                $extra['functions'] += array('G' . $id => '_makeExtraCols');
                $LO_columns += array('G' . $id => $assignment['TYPE_TITLE'] . '<BR>' . $assignment['TITLE']);
                $total_points[$id] = $assignment['POINTS'];
            }
        }
    } elseif (clean_param($_REQUEST['assignment_id'], PARAM_INT)) {

        $id = $_REQUEST['assignment_id'];
        $extra['SELECT'] .= ',\'' . $id . '\' AS POINTS,\'' . $id . '\' AS LETTER_GRADE,\'' . $id . '\' AS COMMENT,\'' . $assignments_RET[$id][1]['DUE'] . '\' AS DUE';
        $extra['WHERE'] .= ' AND (((SELECT DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $id . '\')>=ssm.START_DATE) OR ((SELECT DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $id . '\') IS NULL))';
        $extra['functions'] = array('POINTS' => '_makeExtraAssnCols', 'LETTER_GRADE' => '_makeExtraAssnCols', 'COMMENT' => '_makeExtraAssnCols');
        $LO_columns += array('POINTS' => 'Points', 'LETTER_GRADE' => 'Grade', 'COMMENT' => 'Comment');
        $total_points = DBGet(DBQuery('SELECT POINTS FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $id . '\''));
        $total_points[$id] = $total_points[1]['POINTS'];


        $current_RET = DBGet(DBQuery('SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID FROM gradebook_grades WHERE ASSIGNMENT_ID=\'' . $id . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\''), array(), array('STUDENT_ID', 'ASSIGNMENT_ID'));
    } else {
        if (is_countable($assignments_RET) && count($assignments_RET)) {
            if ($programconfig[User('STAFF_ID')]['WEIGHT'] != 'Y') {
                $_SESSION['ROUNDING'] = $programconfig[User('STAFF_ID')]['ROUNDING'];
                $extra['SELECT'] .= ',\'\' AS POINTS,\'\' AS LETTER_GRADE,\'\' AS COMMENT';
                $extra['WHERE'] = ' AND \'' . DBDate('mysql') . '\'>=ssm.START_DATE';
                $extra['functions'] = array('POINTS' => '_makeExtraAssnCols', 'LETTER_GRADE' => '_makeExtraAssnCols');
                $LO_columns += array('POINTS' => 'Points', 'LETTER_GRADE' => 'Grade');
            } else {
                $_SESSION['ROUNDING'] = $programconfig[User('STAFF_ID')]['ROUNDING'];
                $extra['SELECT'] .= ',\'\' AS POINTS,\'\' AS LETTER_GRADE,\'\' AS WEIGHT_GRADE,\'\' AS COMMENT';
                $extra['WHERE'] = ' AND \'' . DBDate('mysql') . '\'>=ssm.START_DATE';
                //			$extra['functions'] = array('POINTS'=>'_makeExtraAssnCols','LETTER_GRADE'=>'_makeExtraAssnCols','WEIGHT_GRADE'=>'_makeWtg');
                //			$LO_columns += array('POINTS'=>'Points','LETTER_GRADE'=>'Grade','WEIGHT_GRADE'=>'WEIGHT GRADE');
                $extra['functions'] = array('POINTS' => '_makeExtraAssnCols', 'WEIGHT_GRADE' => '_makeWtg');
                $LO_columns += array('POINTS' => 'Points', 'WEIGHT_GRADE' => 'Grade');
            }
            // this will get the grades for all students ever enrolled in the class
            // the "group by start_date" and "distinct on" are needed in case a student is enrolled more than once (re-enrolled)
            if ($programconfig[User('STAFF_ID')]['WEIGHT'] == 'Y') {

                $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID, gt.ASSIGNMENT_TYPE_ID, 
                                    sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                        sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1
                )  ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL, gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                                     
                                        ,gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\'' . $course_id . '\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
            } else {
                $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID, sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL OR (ga.due_date < (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1) ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT 
                                    FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . User('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                                    WHERE ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE'), array(), array('STUDENT_ID'));
            }
            if ($programconfig[User('STAFF_ID')]['WEIGHT'] == 'Y') {
                $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . UserCoursePeriod()));
                if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                    $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                    $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];
                    $sql = 'SELECT g.STUDENT_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE, t.ASSIGNMENT_TYPE_ID, t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,  t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID  AND g.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . User('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                } else {
                    $sql = 'SELECT g.STUDENT_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,  t.ASSIGNMENT_TYPE_ID,   t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  , t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID  AND g.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . User('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . UserMP() . '\'';
                }
            } else {
                $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . UserCoursePeriod()));
                if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                    $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                    $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];
                    $sql = 'SELECT g.STUDENT_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,\'-1\' AS ASSIGNMENT_TYPE_ID,\'1\' AS FINAL_GRADE_PERCENT,\'N/A\' as WEIGHT_GRADE,\'N/A\' as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID  AND g.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') WHERE  a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . User('STAFF_ID') . '\')  AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                } else {
                    $sql = 'SELECT g.STUDENT_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,\'-1\' AS ASSIGNMENT_TYPE_ID,\'1\' AS FINAL_GRADE_PERCENT,\'N/A\' as WEIGHT_GRADE,\'N/A\' as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID  AND g.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') WHERE  a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . User('STAFF_ID') . '\')  AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . UserMP() . '\'';
                }
            }
            $sql .= ' AND (a.POINTS!=\'0\' OR g.POINTS IS NOT NULL AND g.POINTS!=\'-1\')';
            $sql .= ' ORDER BY a.ASSIGNMENT_ID';
            $current_RET = DBGet(DBQuery($sql), array('ASSIGN_TYP_WG' => '_makeAssnWG',  'LETTER_GRADE' => '_makeExtra',), array('STUDENT_ID'));
            // print_r($current_RET);
            foreach ($assignments_RET as $id => $assignment)
                $total_points[$id] = $assignment[1]['POINTS'];
        }
    }
}
### Need to work clean_param($_REQUEST['assignment_id'], PARAM_INT) ###
if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax']) && $_SESSION['assignment_id'] == $_REQUEST['assignment_id']) {
    foreach ($_REQUEST['values'] as $student_id => $assignments) {
        foreach ($assignments as $assignment_id => $columns) {
            if ($columns['POINTS']) {
                if ($columns['POINTS'] == '*')
                    $columns['POINTS'] = '-1';
                else {
                    $columns['POINTS'] = clean_param($columns['POINTS'], PARAM_PERCENT);
                    if (substr($columns['POINTS'], -1) == '%')
                        $columns['POINTS'] = substr($columns['POINTS'], 0, -1) * $total_points[$assignment_id] / 100;
                    elseif (!is_numeric($columns['POINTS']))
                        $columns['POINTS'] = _makePercentGrade($columns['POINTS'], $course_period_id) * $total_points[$assignment_id] / 100;
                    if ($columns['POINTS'] < 0)
                        $columns['POINTS'] = '0';
                    elseif ($columns['POINTS'] > 9999.99)
                        $columns['POINTS'] = '9999.99';
                }
            }
            $sql = '';
            if ($current_RET[$student_id][$assignment_id]) {
                $sql = "UPDATE gradebook_grades SET ";
                foreach ($columns as $column => $value) {
                    if ($column == 'COMMENT')
                        $value = singleQuoteReplace("", "", $value);
                    if ($value != '-1') {
                        $value = paramlib_validation($column, $value);
                    }

                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                        $value = mysql_real_escape_string($value);
                    }
                    $sql .= $column . "='" . $value . " ',";
                }

                $sql = substr($sql, 0, -1) . " WHERE STUDENT_ID='$student_id' AND ASSIGNMENT_ID='$assignment_id' AND COURSE_PERIOD_ID='$course_period_id'";
            } elseif ($columns['POINTS'] != '' || $columns['COMMENT']) {
                $columns['COMMENT'] = singleQuoteReplace("", "", $columns['COMMENT']);
                $sql = 'INSERT INTO gradebook_grades (STUDENT_ID,PERIOD_ID,COURSE_PERIOD_ID,ASSIGNMENT_ID,POINTS,COMMENT) values(\'' . clean_param($student_id, PARAM_INT) . '\',\'' . clean_param(UserPeriod(), PARAM_INT) . '\',\'' . clean_param($course_period_id, PARAM_INT) . '\',\'' . clean_param($assignment_id, PARAM_INT) . '\',\'' . $columns['POINTS'] . '\',\'' . clean_param($columns['COMMENT'], PARAM_NOTAGS) . '\')';
            }
            if ($sql) {
                DBQuery($sql);

                if (isset($columns['POINTS']) && $columns['POINTS'] == '')
                    DBQuery("UPDATE gradebook_grades SET points=null WHERE STUDENT_ID='$student_id' AND ASSIGNMENT_ID='$assignment_id' AND COURSE_PERIOD_ID='$course_period_id'");

                //                DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
            }
        }
    }
    if ($_REQUEST['student_id'])
        $current_RET[$_REQUEST['student_id']] = DBGet(DBQuery('SELECT g.ASSIGNMENT_ID FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND g.STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\'' . ($_REQUEST['assignment_id'] == 'all' ? '' : ' AND g.ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\'')), array(), array('ASSIGNMENT_ID'));
    elseif ($_REQUEST['assignment_id'] == 'all')
        $current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.ASSIGNMENT_ID,g.POINTS FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\''), array(), array('STUDENT_ID', 'ASSIGNMENT_ID'));
    else
        $current_RET = DBGet(DBQuery('SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID FROM gradebook_grades WHERE ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\''), array(), array('STUDENT_ID', 'ASSIGNMENT_ID'));

    unset($_REQUEST['values']);
    unset($_SESSION['_REQUEST_vars']['values']);
}
$_SESSION['assignment_id'] = $_REQUEST['assignment_id'];
if (!clean_param($_REQUEST['student_id'], PARAM_INT))
    $extra['GROUP'] = 's.STUDENT_ID';
// echo "<pre>";
// print_r($extra);
$stu_RET = GetStuList($extra);
$assignment_select = '<SELECT name=assignment_id class="form-control m-20" onchange="document.location.href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&include_inactive=' . $_REQUEST['include_inactive'] . '&cpv_id=' . CpvId() . '&assignment_id=\'+this.options[selectedIndex].value"><OPTION value="">' . _totals . '</OPTION><OPTION value="all"' . (($_REQUEST['assignment_id'] == 'all' && !$_REQUEST['student_id']) ? 'SELECTED' : '') . '>' . _all . '</OPTION>';
if ($_REQUEST['student_id'])
    $assignment_select .= '<OPTION value=' . $_REQUEST['assignment_id'] . ' SELECTED>' . $stu_RET[1]['FULL_NAME'] . '</OPTION>';
foreach ($assignments_RET as $id => $assignment)
    $assignment_select .= '<OPTION value=' . $id . (($_REQUEST['assignment_id'] == $id && !$_REQUEST['student_id']) ? ' SELECTED' : '') . '>' . $assignment[1]['TYPE_TITLE'] . ' - ' . $assignment[1]['TITLE'] . '</OPTION>';
$assignment_select .= '</SELECT>';
echo '<div class="">';
echo "<FORM class='m-b-0' action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&student_id=" . strip_tags(trim($_REQUEST['student_id'])) . "&cpv_id=" . CpvId() . "&include_inactive=" . $_REQUEST['include_inactive'] . " method=POST>";
$tmp_REQUEST = $_REQUEST;
unset($tmp_REQUEST['include_inactive']);

if (is_countable($stu_RET) && count($stu_RET) == 0 && !$_REQUEST['student_id'])
    echo '<div class="form-inline">' . $assignment_select . ' &nbsp; <label class="checkbox checkbox-inline checkbox-switch switch-success switch-xs"><INPUT type=checkbox name=include_inactive value=Y' . ($_REQUEST['include_inactive'] == 'Y' ? " CHECKED onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=\";'" : " onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=Y\";'") . '><span></span> ' . _includeInactiveStudents . ':</label></div>';
else {
    if (!$_REQUEST['student_id']) {
        echo '<div class="form-inline">' . $assignment_select . ' &nbsp; &nbsp; <label class="checkbox checkbox-inline checkbox-switch switch-success switch-xs"><INPUT type=checkbox name=include_inactive value=Y' . ($_REQUEST['include_inactive'] == 'Y' ? " CHECKED onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=\";'" : " onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=Y\";'") . '><span></span> ' . _includeInactiveStudents . '</label> &nbsp; ' . ($_REQUEST['assignment_id'] ? SubmitButton(_save, '', 'class="btn btn-primary pull-right m-20" onclick="self_disable(this);"') : '') . '</div>';
    } else {
        echo '<div class="form-inline">' . $assignment_select . ' &nbsp; ' . ($_REQUEST['assignment_id'] ? SubmitButton(_save, '', 'class="btn btn-primary pull-right m-20" onclick="self_disable(this);"') : '') . '</div>';
    }
}

echo '<hr class="no-margin" />';

if (!$_REQUEST['student_id'] && $_REQUEST['assignment_id'] == 'all')
    $options = array('yscroll' => true);

// echo '<hr class="no-margin-bottom"/>';
ListOutput($stu_RET, $LO_columns, $item, $items, $link, array(), $options);

if (is_countable($assignments_RET) && count($assignments_RET) != 0)
    echo $_REQUEST['assignment_id'] ? '<div class="panel-footer text-center">' . SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"') . '</div>' : '';
echo '</FORM>';

echo '</div>'; //.panel-body
echo '</div>'; //.panel.panel-default

function _makeExtra($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;

    if ($column == 'POINTS') {
        if ($THIS_RET['TOTAL_POINTS'] != '0')
            if ($value != '-1') {
                if (($THIS_RET['DUE'] || $value != '') && $value != '') {
                    $student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
                    $total_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $THIS_RET['TOTAL_POINTS'];
                    $percent_weights[$THIS_RET['ASSIGNMENT_TYPE_ID']] = $THIS_RET['FINAL_GRADE_PERCENT'];
                }
                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . (rtrim(rtrim($value, '0'), '.') + 0) . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
            } else
                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . _excluded . '</font></TD><TD></TD><TD></TD></TR></TABLE>';
        else {
            $student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
            return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . (rtrim(rtrim($value, '0'), '.') + 0) . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
        }
    } elseif ($column == 'LETTER_GRADE') {
        if ($THIS_RET['TOTAL_POINTS'] != '0')
            if ($value != '-1')
                if ($THIS_RET['DUE'] && $value == '')
                    return 'Not Graded';
                else if ($THIS_RET['DUE'] || $value != '') {

                    $per = $value / $THIS_RET['TOTAL_POINTS'];

                    return _makeLetterGrade($per, "", User('STAFF_ID'), "%") . '%&nbsp;' . _makeLetterGrade($value / $THIS_RET['TOTAL_POINTS'], "", User('STAFF_ID'));
                } else
                    return 'Due';
            else
                return 'N/A';
        else
            return 'E/C';
    }
}

function _makeExtraAssnCols($assignment_id, $column)
{
    global $THIS_RET, $total_points, $current_RET, $points_RET, $tabindex, $max_allowed;
    $rounding = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
    if (is_countable($rounding) && count($rounding))
        $_SESSION['ROUNDING'] = $rounding[1]['VALUE'];
    switch ($column) {
        case 'POINTS':
            $tabindex++;

            if ($assignment_id == '' && !$_REQUEST['student_id']) {
                if (is_countable($points_RET[$THIS_RET['STUDENT_ID']]) && count($points_RET[$THIS_RET['STUDENT_ID']])) {
                    $total = $total_points = 0;
                    foreach ($points_RET[$THIS_RET['STUDENT_ID']] as $partial_points)
                        if ($partial_points['PARTIAL_TOTAL'] != 0) {
                            $total += $partial_points['PARTIAL_POINTS'];
                            $total_points += $partial_points['PARTIAL_TOTAL'];
                        }
                } else
                    $total = $total_points = 0;

                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD>' . $total . '</TD><TD>&nbsp;/&nbsp;</TD><TD>' . $total_points . '</TD></TR></TABLE>';
            } else {
                if ($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'] == '-1')
                    $points = '*';
                elseif (strpos($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'], '.'))
                    $points = rtrim(rtrim($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'], '0'), '.');
                else
                    $points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];

                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '</TD><TD>&nbsp;/&nbsp;</TD><TD>' . $total_points[$assignment_id] . '</TD></TR></TABLE>';
            }
            break;
        case 'LETTER_GRADE':
            if ($assignment_id == '' && !$_REQUEST['student_id']) {
                if (is_countable($points_RET[$THIS_RET['STUDENT_ID']]) && count($points_RET[$THIS_RET['STUDENT_ID']])) {




                    $total = $total_percent = 0;
                    foreach ($points_RET[$THIS_RET['STUDENT_ID']] as $partial_points)
                        if ($partial_points['PARTIAL_TOTAL'] != 0) {
                            $total += $partial_points['PARTIAL_POINTS'];
                            $total_percent += $partial_points['PARTIAL_TOTAL'];
                        }
                    if ($total_percent != 0)
                        $total /= $total_percent;
                } else
                    $total = 0;

                $ppercent = _makeLetterGrade($total, "", User('STAFF_ID'), "%");
                if ($points_RET[$THIS_RET['STUDENT_ID']][1]['PARTIAL_POINTS'] != '')
                    return ($total > $max_allowed ? '<FONT color=red>' : '') . $ppercent . ($total > $max_allowed ? '</FONT>' : '') . '% &nbsp;<B>' . _makeLetterGrade($total, "", User('STAFF_ID')) . '</B>';
                else
                    return _notGraded;
            } else {
                $points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];
                if ($_SESSION['ROUNDING'] == 'UP')
                    $points_m = ceil($points);
                elseif ($_SESSION['ROUNDING'] == 'DOWN')
                    $points_m = floor($points);
                elseif ($_SESSION['ROUNDING'] == 'NORMAL')
                    $points_m = round($points);
                else
                    $points_m = $points;

                #return $points_m; 11.00
                $make_grade_points = $points_m / 100;
                $tot_point = $total_points[$assignment_id];
                #return $max_allowed; 1
                if ($total_points[$assignment_id] != 0) {
                    if ($points != '-1') {
                        if ($points != '') {
                            $rounding = DBGet(DBQuery('SELECT VALUE AS ROUNDING  FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
                            $points_r = ($points_m / $tot_point) * 100;
                            if ($rounding[1]['ROUNDING'] == 'UP')
                                $points_r = ceil($points_r);
                            elseif ($rounding[1]['ROUNDING'] == 'DOWN')
                                $points_r = floor($points_r);
                            elseif ($rounding[1]['ROUNDING'] == 'NORMAL')
                                $points_r = round($points_r);
                            else
                                $points_r = round($points_r, 2);
                            return ($THIS_RET['DUE'] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . ($points_r) . '%' . ($THIS_RET['DUE'] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '</FONT>' : '') : '') . '&nbsp;<B>' . _makeLetterGrade(($points_m / $tot_point)) . '</B>' . ($THIS_RET['DUE'] || $points != '' ? '' : '</FONT>');
                        } else
                            return _notGraded;
                    } else
                        return _excluded;
                } else
                    return _eC;
            }
            break;
        case 'COMMENT':
            return TextInput($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['COMMENT'], 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][COMMENT]', '', '  size=11 maxlength=100 tabindex=' . (500 + $tabindex));
            break;
    }
}

function _makeExtraStuCols($value, $column)
{
    global $THIS_RET, $assignment_count, $count_assignments, $max_allowed;
    switch ($column) {
        case 'POINTS':
            $assignment_count++;
            $tabindex = $assignment_count;
            if ($value == '-1')
                $value = '*';
            elseif (strpos($value, '.'))
                $value = rtrim(rtrim($value, '0'), '.');
            return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD>' . TextInput($value, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $THIS_RET['ASSIGNMENT_ID'] . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '</TD><TD>&nbsp;/&nbsp;</TD><TD>' . $THIS_RET['TOTAL_POINTS'] . '</TD></TR></TABLE>';
            break;
        case 'LETTER_GRADE':

            if ($THIS_RET['TOTAL_POINTS'] != 0) {
                if ($THIS_RET['POINTS'] != '-1') {
                    if ($THIS_RET['POINTS'] != '')
                        return ($THIS_RET['DUE'] || $THIS_RET['POINTS'] != '' ? ($THIS_RET['POINTS'] > $THIS_RET['TOTAL_POINTS'] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . _makeLetterGrade($THIS_RET['POINTS'] / $THIS_RET['TOTAL_POINTS'], "", User('STAFF_ID'), "%") . '%' . ($THIS_RET['DUE'] || $THIS_RET['POINTS'] != '' ? ($THIS_RET['POINTS'] > $THIS_RET['TOTAL_POINTS'] * $max_allowed ? '</FONT>' : '') : '') . '&nbsp;<B>' . _makeLetterGrade($THIS_RET['POINTS'] / $THIS_RET['TOTAL_POINTS'], "", User('STAFF_ID'), "") . '</B>' . ($THIS_RET['DUE'] || $THIS_RET['POINTS'] != '' ? '' : '</FONT>');
                    else
                        return _notGraded;
                } else
                    return _excluded;
            } else
                return _eC;

            break;
        case 'COMMENT':
            $tabindex += $count_assignments;
            return TextInput($value, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $THIS_RET['ASSIGNMENT_ID'] . '][COMMENT]', '', ' size=11 maxlength=100 tabindex=' . $tabindex);
            break;
    }
}

function _makeExtraCols($assignment_id, $column)
{
    global $THIS_RET, $total_points, $current_RET, $old_student_id, $student_count, $tabindex, $count_students, $max_allowed;

    $rounding = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));

    if (is_countable($rounding) && count($rounding))
        $_SESSION['ROUNDING'] = $rounding[1]['VALUE'];
    if (strtotime($THIS_RET['START_DATE'], 0) == strtotime($THIS_RET['DUE_' . $assignment_id], 0))
        $days_left = 1;
    else
        $days_left = floor((strtotime($THIS_RET['DUE_' . $assignment_id], 0) - strtotime($THIS_RET['START_DATE'], 0)) / 86400);
    if ($days_left >= 1) {
        if ($THIS_RET['STUDENT_ID'] != $old_student_id) {
            $student_count++;
            $tabindex = $student_count;
            $old_student_id = $THIS_RET['STUDENT_ID'];
        } else
            $tabindex += $count_students;
        if ($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'] == '-1')
            $points = '*';
        elseif (strpos($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'], '.'))
            $points = rtrim(rtrim($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'], '0'), '.');
        else
            $points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];

        if ($_SESSION['ROUNDING'] == 'UP')
            $points_m = ceil($points);
        elseif ($_SESSION['ROUNDING'] == 'DOWN')
            $points_m = floor($points);
        elseif ($_SESSION['ROUNDING'] == 'NORMAL') {
            $points_m = round($points, 0);
        } else
            $points_m = $points;
        $make_letter_points = (($points_m != '*') ? ($points_m / 100) : 0);
        $tot_point = $total_points[$assignment_id];
        if ($total_points[$assignment_id] != 0) {
            if ($points != '*') {
                if ($points != '') {
                    $rounding = DBGet(DBQuery('SELECT VALUE AS ROUNDING FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
                    $points_r = ($points_m / $tot_point) * 100;
                    if ($rounding[1]['ROUNDING'] == 'UP')
                        $points_r = ceil($points_r);
                    elseif ($rounding[1]['ROUNDING'] == 'DOWN')
                        $points_r = floor($points_r);
                    elseif ($rounding[1]['ROUNDING'] == 'NORMAL') {
                        $points_r = round($points_r, 0);
                    } else
                        $points_r = round($points_r, 2);
                    // return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '<HR>' . $total_points[$assignment_id] . '</TD><TD>&nbsp;' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . ($points_r) . '%' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '</FONT>' : '') : '') . '<BR>&nbsp;<B>' . _makeLetterGrade(($points_m / $tot_point)) . '</B>' . ($THIS_RET['D' . $assignment_id] || $points != '' ? '' : '</FONT>') . '</TD></TR></TABLE>';
                    return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . $total_points[$assignment_id] . '</TD><TD>&nbsp;' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . ($points_r) . '%' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '</FONT>' : '') : '') . '<BR>&nbsp;<B>' . _makeLetterGrade(($points_m / $tot_point)) . '</B>' . ($THIS_RET['D' . $assignment_id] || $points != '' ? '' : '</FONT>') . '</TD></TR></TABLE>';
                } else
                    //return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '<HR>' . $total_points[$assignment_id] . '</TD><TD>&nbsp;' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . '&nbsp;&nbsp;Not Graded</TD></TR></TABLE>';
                    return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . $total_points[$assignment_id] . '</TD><TD>&nbsp;' . ($THIS_RET['D' . $assignment_id] || $points != '' ? ($points > $total_points[$assignment_id] * $max_allowed ? '<FONT color=red>' : '') : '<FONT color=gray>') . '&nbsp;&nbsp;' . _notGraded . '</TD></TR></TABLE>';
            } else
                //return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '<HR>' . $total_points[$assignment_id] . '</TD><TD>&nbsp;N/A<BR>&nbsp;N/A</TD></TR></TABLE>';
                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . $total_points[$assignment_id] . '</TD><TD>' . _excluded . '</TD></TR></TABLE>';
        } else
            //return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) . '<HR>' . $total_points[$assignment_id] . '</TD><TD>&nbsp;E/C</TD></TR></TABLE>';
            return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>' . TextInput($points, 'values[' . $THIS_RET['STUDENT_ID'] . '][' . $assignment_id . '][POINTS]', '', ' size=2 maxlength=7 tabindex=' . $tabindex) .  $total_points[$assignment_id] . '</TD><TD>&nbsp;' . _eC . '</TD></TR></TABLE>';
    }
    return 'N/A';
}

function _makeWtg($assignment_id, $column)
{
    global $THIS_RET, $total_points, $current_RET, $points_RET, $tabindex, $max_allowed;

    if ((clean_param($_REQUEST['assignment_id'], PARAM_INT) == '' || clean_param($_REQUEST['assignment_id'], PARAM_INT) == '0') && substr($_REQUEST['assignment_id'], 0, 1) != 't' && !$_REQUEST['student_id']) {
        if (is_countable($current_RET[$THIS_RET['STUDENT_ID']]) && count($current_RET[$THIS_RET['STUDENT_ID']])) {
            $total = $total_percent = 0;
            $assign_typ_wg = array();
            $tot_weight_grade = '';
            $tot_weighted_percent = array();
            $assignment_type_count = array();

            foreach ($current_RET[$THIS_RET['STUDENT_ID']] as $partial_points) {
                // $THIS_RET['STUDENT_ID']['flag']=0;
                $THIS_RET_STUDENT_ID_flag = 0;

                if ($partial_points['LETTERWTD_GRADE'] != -1.00 && $partial_points['LETTERWTD_GRADE'] != '') {
                    $wper = explode('%', $partial_points['LETTER_GRADE']);

                    if ($tot_weighted_percent[$partial_points['ASSIGNMENT_TYPE_ID']] != '')
                        $tot_weighted_percent[$partial_points['ASSIGNMENT_TYPE_ID']] = $tot_weighted_percent[$partial_points['ASSIGNMENT_TYPE_ID']] + $wper[0];
                    else
                        $tot_weighted_percent[$partial_points['ASSIGNMENT_TYPE_ID']] = $wper[0];
                    if ($assignment_type_count[$partial_points['ASSIGNMENT_TYPE_ID']] != '')
                        $assignment_type_count[$partial_points['ASSIGNMENT_TYPE_ID']] = $assignment_type_count[$partial_points['ASSIGNMENT_TYPE_ID']] + 1;
                    else
                        $assignment_type_count[$partial_points['ASSIGNMENT_TYPE_ID']] = 1;
                    if ($partial_points['ASSIGN_TYP_WG'] != '')
                        $assign_typ_wg[$partial_points['ASSIGNMENT_TYPE_ID']] = substr($partial_points['ASSIGN_TYP_WG'], 0, -2);

                    // $total += (($partial_points['PARTIAL_POINTS']/$partial_points['PARTIAL_TOTAL']))*$partial_points['FINAL_GRADE_PERCENT'];
                    // $total_percent += $partial_points['PARTIAL_TOTAL'];

                    // $THIS_RET['STUDENT_ID']['flag']=1;
                    $THIS_RET_STUDENT_ID_flag = 1;
                }
            }

            $total_weightage = 0;
            // print_r($assignment_type_count);
            foreach ($assignment_type_count as $assign_key => $value) {
                $total_weightage = $total_weightage + $assign_typ_wg[$assign_key];
                if ($tot_weight_grade == '') {
                    $tot_weight_grade = round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2);
                    //echo $tot_weight_grade .'= round((round(('.$tot_weighted_percent[$assign_key].' / '.$value.'), 2) * '.$assign_typ_wg[$assign_key].') / 100, 2)----new<br/><br/>';
                } else {
                    $tot_weight_grade = $tot_weight_grade + (round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2));
                    //echo $tot_weight_grade .'= '.$tot_weight_grade.' + (round((round(('.$tot_weighted_percent[$assign_key].' / '.$value.'), 2) * '.$assign_typ_wg[$assign_key].') / 100, 2))----old<br/><br/>';
                }
            }
            $tot_weight_grade = $tot_weight_grade / 100;

            //$tot_weight_grade=$tot_weight_grade/100;
            // if($total_percent!=0)
            //     $total = ($total/$total_percent)*$partial_points;
        } else
            $total = 0;

        if ($tot_weight_grade != '' && $total_weightage != '')
            $tot_weight_grade = ($tot_weight_grade / $total_weightage) * 100;
        else
            $tot_weight_grade = 0;

        // return (($current_RET[$THIS_RET['STUDENT_ID']][1]['LETTERWTD_GRADE']!=-1.00 && $current_RET[$THIS_RET['STUDENT_ID']][1]['LETTERWTD_GRADE']!='' && $current_RET[$THIS_RET['STUDENT_ID']][1]['ASSIGN_TYP_WG']!='N/A') ?_makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'%').'% <B>'._makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'').'</B>':'N/A');
        // return (($THIS_RET['STUDENT_ID']['flag']==1) ?_makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'%').'% <B>'._makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'').'</B>':'N/A');

        // return (($THIS_RET_STUDENT_ID_flag==1) ?_makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'%').'% <B>'._makeLetterGrade($tot_weight_grade,"",User('STAFF_ID'),'').'</B>':'N/A');

        return (_makeLetterGrade($tot_weight_grade, "", User('STAFF_ID'), '%') . '% <B>' . _makeLetterGrade($tot_weight_grade, "", User('STAFF_ID'), '') . '</B>');

        // $ppercent= _makeLetterGrade($total,"",User('STAFF_ID'),"%");
        // if($points_RET[$THIS_RET['STUDENT_ID']][1]['PARTIAL_POINTS']!='')
        //                                    return ($total>$max_allowed?'<FONT color=red>':'').$ppercent.($total>$max_allowed?'</FONT>':'').'% &nbsp;<B>'._makeLetterGrade($total,"",User('STAFF_ID')).'</B>';
        //                                else
        //					return 'Not Graded';
    } else if (clean_param($_REQUEST['assignment_id'], PARAM_INT) != '' && substr($_REQUEST['assignment_id'], 0, 1) != 't' && !$_REQUEST['student_id']) {
        $points = $points_RET[$THIS_RET['STUDENT_ID']][1]['POINTS'];

        if ($points != '-1') {
            if ($points != '') {
                foreach ($points_RET[$THIS_RET['STUDENT_ID']] as $partial_points)
                    if ($partial_points['TOTAL_POINTS'] != 0 && $partial_points['POINTS'] != -1.00 && $partial_points['POINTS'] != '') {
                        $pnt = (($partial_points['POINTS'] / $partial_points['TOTAL_POINTS']) * 100) * $partial_points['FINAL_GRADE_PERCENT'];
                        $pnt = $pnt / 100;
                        return (($points_RET[$THIS_RET['STUDENT_ID']][1]['LETTERWTD_GRADE'] != -1.00 && $points_RET[$THIS_RET['STUDENT_ID']][1]['LETTERWTD_GRADE'] != '' && $points_RET[$THIS_RET['STUDENT_ID']][1]['ASSIGN_TYP_WG'] != 'N/A') ? _makeLetterGrade($pnt, "", User('STAFF_ID'), '%') . '% <B>' . _makeLetterGrade($pnt, "", User('STAFF_ID'), '') . '</B>' : 'N/A');
                        //                                                
                    } else
                        return 'N/A';
            } else
                return 'N/A';
        }
        //                                                 
        else
            return 'N/A';
        //                                        }
        //					else
        //						return 'N/A&nbsp;N/A';
        //                                }
        //				else
        //					return 'E/C';
    } else {
        $wtdper = ($THIS_RET['POINTS'] / $THIS_RET['TOTAL_POINTS']) * $THIS_RET['FINAL_GRADE_PERCENT'];
        return (($THIS_RET['LETTERWTD_GRADE'] != -1.00 && $THIS_RET['LETTERWTD_GRADE'] != '' && $THIS_RET['ASSIGN_TYP_WG'] != 'N/A') ? _makeLetterGrade($wtdper, "", User('STAFF_ID'), '%') . '% <B>' . _makeLetterGrade($wtdper, "", User('STAFF_ID'), '') . '</B>' : 'N/A');
    }
}

function _makeAssnWG($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;
    return ($THIS_RET['ASSIGN_TYP_WG'] != 'N/A' ? ($value * 100) . ' %' : $THIS_RET['ASSIGN_TYP_WG']);
}
