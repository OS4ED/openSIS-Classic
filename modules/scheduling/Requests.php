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
#  See License.txt.
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

DrawBC(""._scheduling." > " . ProgramTitle());
$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('request');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

Search('student_id', $extra);
echo '<div class="panel panel-default">';
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'remove') {
    if (DeletePromptModRequest('request')) {
        DBQuery('DELETE FROM schedule_requests WHERE REQUEST_ID=\'' . paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['id']) . '\'');
        unset($_REQUEST['modfunc']);
        unset($_SESSION['_REQUEST_vars']['modfunc']);
        unset($_SESSION['_REQUEST_vars']['id']);
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'update') {
    $flg = 0;
    foreach ($_REQUEST['values'] as $request_id => $columns) {

        $chk_sql = DBGet(DBQuery('SELECT * FROM schedule_requests  WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND REQUEST_ID=\'' . $request_id . '\''));

        $sql = 'UPDATE schedule_requests SET ';

        foreach ($columns as $column => $value) {
            if (!isset($columns['WITH_TEACHER_ID']) && $columns['NOT_TEACHER_ID'] != '' && ($chk_sql[1]['WITH_TEACHER_ID'] == $columns['NOT_TEACHER_ID'])) {
                $flg = 1;
                break 2;
            } else if (!isset($columns['NOT_TEACHER_ID']) && $columns['WITH_TEACHER_ID'] != '' && ($chk_sql[1]['NOT_TEACHER_ID'] == $columns['WITH_TEACHER_ID'])) {
                $flg = 1;
                break 2;
            } else if (!isset($columns['WITH_PERIOD_ID']) && $columns['NOT_PERIOD_ID'] != '' && ($chk_sql[1]['WITH_PERIOD_ID'] == $columns['NOT_PERIOD_ID'])) {
                $flg = 2;
                break 2;
            } else if (!isset($columns['NOT_PERIOD_ID']) && $columns['WITH_PERIOD_ID'] != '' && ($chk_sql[1]['NOT_PERIOD_ID'] == $columns['WITH_PERIOD_ID'])) {
                $flg = 2;
                break 2;
            } else if ($columns['WITH_TEACHER_ID'] != '' && $columns['NOT_TEACHER_ID'] != '' && ($columns['WITH_TEACHER_ID'] == $columns['NOT_TEACHER_ID'])) {
                $flg = 1;
                break 2;
            } else if ($columns['WITH_PERIOD_ID'] != '' && $columns['NOT_PERIOD_ID'] != '' && ($columns['NOT_PERIOD_ID'] == $columns['WITH_PERIOD_ID'])) {
                $flg = 2;
                break 2;
            } else if (($columns['WITH_PERIOD_ID'] != '' && $columns['NOT_PERIOD_ID'] != '' && ($columns['NOT_PERIOD_ID'] == $columns['WITH_PERIOD_ID'])) && $columns['WITH_TEACHER_ID'] != '' && $columns['NOT_TEACHER_ID'] != '' && ($columns['WITH_TEACHER_ID'] == $columns['NOT_TEACHER_ID'])) {
                $flg = 3;
                break 2;
            } else {

                $value = paramlib_validation($column, $value);
                if (str_replace("\'", "''", $value) == '' || str_replace("\'", "''", $value) == 0)
                    $sql .=$column . '=NULL,';
                else
                    $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                
            }
        }
        $sql = substr($sql, 0, -1) . ' WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND REQUEST_ID=\'' . $request_id . '\'';

        if ($flg == 0)
            DBQuery($sql);
    }
    if ($flg == 1) {
        echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._teacherContradiction."" . "</div>";
        unset($_REQUEST['modfunc']);
    }
    if ($flg == 2) {
        echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._periodContradiction."" . "</div>";
        unset($_REQUEST['modfunc']);
    }
    if ($flg == 3) {
        echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._teacherPeriodContradiction."" . "</div>";
        unset($_REQUEST['modfunc']);
    }
    unset($_REQUEST['modfunc']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'add') {
    $flag = true;
    if ($_REQUEST['subject_id'] == 0) {
        echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._pleaseSelectASubject."" . "</div>";
        unset($_REQUEST['modfunc']);
    } else {
        if ($_REQUEST['course_id'] == 0) {
            echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._pleaseSelectACourse."" . "</div>";
            unset($_REQUEST['modfunc']);
        } else {
            $course_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['course_id']);
            $course_weight = substr($_REQUEST['course'], strpos($_REQUEST['course'], '-') + 1);
            $subject_id = $_REQUEST['subject_id'];
            $mp_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            $mp_id = UserMP();
            $same_course_check = DBGet(DBQuery('SELECT COURSE_ID FROM schedule_requests WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=\'' . UserSyear() . '\''));
            foreach ($same_course_check as $key => $same_course) {
                if ($same_course['COURSE_ID'] == $course_id)
                    $flag = false;
            }
            if ($flag)
                DBQuery('INSERT INTO schedule_requests (SYEAR,SCHOOL_ID,STUDENT_ID,SUBJECT_ID,COURSE_ID,MARKING_PERIOD_ID) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . UserStudentID() . '\',\'' . $subject_id . '\',\'' . $course_id . '\',\'' . $mp_id . '\')');
            else
                echo "<div class=\"alert bg-danger alert-styled-left\">" . ""._youHaveAlreadyRequestedForThisCourse."" . "</div>";
            unset($_REQUEST['modfunc']);
        }
    }
}

if (!$_REQUEST['modfunc'] && UserStudentID()) {


    if (User('PROFILE') != 'admin') {
        if (User('PROFILE') != 'student')
            if (User('PROFILE_ID') != '')
                $can_edit_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND MODNAME=\'Scheduling/Requests.php\' AND CAN_EDIT=\'Y\''));
            else
                $can_edit_RET = DBGet(DBQuery('SELECT MODNAME FROM staff_exceptions WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND MODNAME=\'Scheduling/Requests.php\' AND CAN_EDIT=\'Y\''), array(), array('MODNAME'));
        else
            $can_edit_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=3 AND MODNAME=\'Scheduling/Requests.php\' AND CAN_EDIT=\'Y\''));
        if ($can_edit_RET)
            $_openSIS['allow_edit'] = true;
    }

    $functions = array('COURSE' => '_makeCourse', 'WITH_TEACHER_ID' => '_makeTeacher', 'WITH_PERIOD_ID' => '_makePeriod');
    $requests_RET = DBGet(DBQuery('SELECT r.REQUEST_ID,c.TITLE as COURSE,r.COURSE_ID,r.COURSE_WEIGHT,r.MARKING_PERIOD_ID,r.WITH_TEACHER_ID,r.NOT_TEACHER_ID,r.WITH_PERIOD_ID,r.NOT_PERIOD_ID FROM schedule_requests r,courses c WHERE r.COURSE_ID=c.COURSE_ID AND r.SYEAR=\'' . UserSyear() . '\' AND r.STUDENT_ID=\'' . UserStudentID() . '\''), $functions);
    $columns = array('COURSE' =>_course,
     'WITH_TEACHER_ID' =>_teacher,
     'WITH_PERIOD_ID' =>_period,
    );

    $subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
    $subjects = CreateSelect($subjects_RET, 'subject_id', _selectSubject, 'Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=');

    if ($_REQUEST['subject_id']) {
        $courses_RET = DBGet(DBQuery('SELECT c.COURSE_ID,c.TITLE FROM courses c WHERE ' . ($_REQUEST['subject_id'] ? 'c.SUBJECT_ID=\'' . $_REQUEST['subject_id'] . '\' AND ' : '') . 'UPPER(c.TITLE) LIKE \'' . strtoupper($_REQUEST['course_title']) . '%' . '\' AND c.SYEAR=\'' . UserSyear() . '\' AND c.SCHOOL_ID=\'' . UserSchool() . '\''));
        $courses = CreateSelect($courses_RET, 'course_id', _selectCourse, 'Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $_REQUEST['subject_id'] . '&course_id=');
    }
    if ($_REQUEST['course_id']) {
        
    }
    if (User('PROFILE') == 'admin' || (User('PROFILE') == 'student' && AllowEdit()) || (User('PROFILE') == 'parent' && AllowEdit())) {
        echo '<FORM class=no-margin name=ad id=ad action=Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&modfunc=add method=POST>';
        DrawHeader('<div class="form-inline"><div class="input-group"><span class="input-group-addon" id="sizing-addon1">'._addARequest.'</span>' . $subjects .'</div> &nbsp;'. $courses . '</div>', SubmitButton(_add, '', 'class="btn btn-primary" onclick=\'formload_ajax("ad");\''));
        echo '<hr class="no-margin" />';
        echo '</FORM>';
        $link['remove'] = array('link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=remove', 'variables' => array('id' => 'REQUEST_ID'));

        echo '<FORM class=no-margin name=up id=up action=Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&modfunc=update method=POST>';
        echo '<div class="panel-body">';
        ListOutputWithStudentInfo($requests_RET, $columns,  _request, _requests, $link);
        if (!$requests_RET)
            echo '';
        else
            echo '<br/>' . SubmitButton(_update, '', 'class="btn btn-primary" onclick=\'formload_ajax("up");self_disable(this); \'');
        echo '</div>';
        echo '</FORM>';
    }
    else {
        $link['remove'] = array('link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=remove', 'variables' => array('id' => 'REQUEST_ID'));

        echo '<FORM class=no-margin name=up id=up action=Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&modfunc=update method=POST>';
        echo '<div class="panel-body">';
        ListOutputWithStudentInfo($requests_RET, $columns, _request , _requests, $link);
        
        echo '<br/>' . SubmitButton(_update, '', 'class="btn btn-primary" onclick=\'formload_ajax("up");\'');
        echo '</div>';
        echo '</FORM>';
    }
    $_openSIS['allow_edit'] = false;
}




function _makeCourse($value, $column) {
    global $THIS_RET;
    if ($THIS_RET['COURSE_WEIGHT'] != '')
        return $value . ' - ' . $THIS_RET['COURSE_WEIGHT'];
    else {
        return $value;
    }
}

function _makeTeacher($value, $column) {
    global $THIS_RET;

    $teachers_RET = DBGet(DBQuery('SELECT s.FIRST_NAME,s.LAST_NAME,s.STAFF_ID AS TEACHER_ID FROM staff s,course_periods cp WHERE s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_ID=\'' . $THIS_RET['COURSE_ID'] . '\''));
    foreach ($teachers_RET as $teacher)
        $options[$teacher['TEACHER_ID']] = $teacher['FIRST_NAME'] . ' ' . $teacher['LAST_NAME'];

    return ''._with.': ' . SelectInput($value, 'values[' . $THIS_RET['REQUEST_ID'] . '][WITH_TEACHER_ID]', '', $options) . ' '._without.': ' . SelectInput($THIS_RET['NOT_TEACHER_ID'], 'values[' . $THIS_RET['REQUEST_ID'] . '][NOT_TEACHER_ID]', '', $options);
}

function _makePeriod($value, $column) {
    global $THIS_RET;

    $periods_RET = DBGet(DBQuery('SELECT p.TITLE,p.PERIOD_ID FROM school_periods p,course_periods cp,course_period_var cpv WHERE p.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_ID=\'' . $THIS_RET['COURSE_ID'] . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
    foreach ($periods_RET as $period)
        $options[$period['PERIOD_ID']] = $period['TITLE'];

    return ''._on.': ' . SelectInput($value, 'values[' . $THIS_RET['REQUEST_ID'] . '][WITH_PERIOD_ID]', '', $options) . ' '._notOn.': ' . SelectInput($THIS_RET['NOT_PERIOD_ID'], 'values[' . $THIS_RET['REQUEST_ID'] . '][NOT_PERIOD_ID]', '', $options);
}

// DOESN'T SUPPORT MP REQUEST
function _makeMP($value, $column) {
    global $THIS_RET;

    return SelectInput($value, 'values[' . $THIS_RET['REQUEST_ID'] . '][MARKING_PERIOD_ID]', '', $options);
}

function CreateSelect($val, $name, $opt, $link = '') {
    if ($link != '')
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    else
        $html .= "<select name=" . $name . " id=" . $name . " >";
    $html .= "<option value=''>" . $opt . "</option>";

    foreach ($val as $key => $value) {
        if ($value[strtoupper($name)] == $_REQUEST[$name])
            $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
        else
            $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
    }



    $html .= "</select>";
    return $html;
}

?>
