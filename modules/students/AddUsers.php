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
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save' && AllowEdit()) {
    $current_RET = DBGet(DBQuery('SELECT STAFF_ID FROM students_join_users WHERE STUDENT_ID=\'' . UserStudentID() . '\''), array(), array('STAFF_ID'));
    foreach ($_REQUEST['staff'] as $staff_id => $yes) {
        if (!$current_RET[$staff_id]) {
            $sql = 'INSERT INTO students_join_users (STAFF_ID,STUDENT_ID) values(\'' . $staff_id . '\',\'' . UserStudentID() . '\')';
            DBQuery($sql);
        }
    }
    unset($_REQUEST['modfunc']);
    unset($_SESSION['_REQUEST_vars']['modfunc']);
    if ($_REQUEST['staff'])
        $note = ""._theSelectedUserSProfileNowIncludesAccessToTheSelectedStudents.".";
}
DrawBC(""._students." > " . ProgramTitle());
if (isset($_REQUEST['student_id']) && $_REQUEST['student_id'] != 'new' || UserStudentID()) {
    if ($_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new')
        $stu_id = $_REQUEST['student_id'];
    else
        $stu_id = UserStudentID();
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $stu_id . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));

    $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students'));
    if ($count_student_RET[1]['NUM'] > 1) {
        echo '<div class="panel panel-default">';
        DrawHeader(''._selectedStudent.': : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'], '<span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=students/Student.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> '._backToStudentList.'</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div>');
        echo '</div>';
    } else if ($count_student_RET[1]['NUM'] == 1) {
        echo '<div class="panel panel-default">';
        DrawHeader(''._selectedStudent.': : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'], '<div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div>');
        echo '</div>';
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete' && AllowEdit()) {
    if (DeletePromptCommon(_studentFromThatUser, _removeAccessTo)) {
        DBQuery('DELETE FROM students_join_users WHERE STAFF_ID=\'' . $_REQUEST[staff_id] . '\' AND STUDENT_ID=\'' . UserStudentID() . '\'');
        unset($_REQUEST['modfunc']);
    }
}
if ($note)
    DrawHeader('<IMG SRC=assets/check.gif>' . $note);
if ($_REQUEST['modfunc'] != 'delete') {
    $extra['SELECT'] = ',(SELECT count(distinct u.PERSON_ID) FROM students_join_people u,people p WHERE u.STUDENT_ID=s.STUDENT_ID AND p.STAFF_ID=u.PERSON_ID) AS ASSOCIATED';
    $extra['columns_after'] = array('ASSOCIATED' => '# '._associated);
    Search('student_id', $extra);
    if (UserStudentID()) {

        if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'list') {
            echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save method=POST>";
        }

        echo '<div class="row">';
        echo '<div class="col-md-8">';

        $current_RET = DBGet(DBQuery('SELECT DISTINCT u.PERSON_ID AS STAFF_ID,CONCAT(p.LAST_NAME,\', \',p.FIRST_NAME) AS FULL_NAME,la.LAST_LOGIN FROM people p INNER JOIN students_join_people u ON ( p.STAFF_ID=u.PERSON_ID ) LEFT JOIN login_authentication la ON (la.PROFILE_ID=p.PROFILE_ID AND p.STAFF_ID=la.USER_ID) WHERE u.STUDENT_ID=\'' . UserStudentID() . '\' group by (p.staff_id) order by la.LAST_LOGIN desc'), array('LAST_LOGIN' => '_makeLogin'));
        $link['remove'] = array('link' => "Modules.php?modname=$_REQUEST[modname]&modfunc=delete", 'variables' => array('staff_id' => 'STAFF_ID'));
        echo '<div class="panel panel-default">';
        ListOutput($current_RET, array('FULL_NAME' => 'Parents', 'LAST_LOGIN' =>_lastLogin), '', '', $link, array(), array('search' =>_lastLogin));
        echo '</div>'; //.panel.panel-default
        echo '</div><div class="col-md-4">';

        if (AllowEdit()) {
            unset($extra);
            $extra['link'] = array('FULL_NAME' =>false);
            $extra['SELECT'] = ',CAST(NULL AS CHAR(1)) AS CHECKBOX';
            $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
            $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'staff\');"><A>');
            $extra['new'] = true;
            $extra['options']['search'] = false;
            $extra['profile'] = 'parent';
            $_openSIS['DrawHeader'] = 'bgcolor=#ff8040';

            Search('staff_id', $extra);
        }

        echo '</div>';//.col-md-8
        echo '</div>';//.row

        if ($_REQUEST['modfunc'] == 'list' && $_SESSION['count_stf'])
            echo SubmitButton(_addSelectedParents, '', 'class="btn btn-primary"') . "</FORM>";
    }
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;

    return "<INPUT type=checkbox name=staff[" . $THIS_RET['STAFF_ID'] . "] value=Y>";
}

function _makeLogin($value) {
    if ($value)
        return ProperDate(substr($value, 0, 10)) . substr($value, 10);
    else
        return '-';
}

?>