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
include('../../../RedirectIncludes.php');
echo '<div class="row">';

$_SESSION['staff_selected'] = $staff['STAFF_ID'];
if (clean_param($_REQUEST['staff_id'], PARAM_ALPHANUM) != 'new' && $UserPicturesPath && (($file = @fopen($picture_path = $UserPicturesPath . UserSyear() . '/' . UserStaffID() . '.JPG', 'r')) || $staff['ROLLOVER_ID'] && ($file = @fopen($picture_path = $UserPicturesPath . (UserSyear() - 1) . '/' . $staff['ROLLOVER_ID'] . '.JPG', 'r')))) {
    fclose($file);
    echo '<div class="col-md-2"><IMG SRC="' . $picture_path . '" width=150></div><div class="col-md-10">';
} else
    echo '<div class="col-md-12">';
if ($_REQUEST['staff_id'] == 'new')
    $id_sent = 0;
else {
    if ($_REQUEST['staff_id'] != '')
        $id_sent = $_REQUEST['staff_id'];
    else
        $id_sent = UserStaffID();
}


if (clean_param($_REQUEST['staff_id'], PARAM_ALPHA) == 'new') {
    echo '<div class="row">';
    echo '<div class=col-md-2><div class="form-group">' . SelectInput($staff['TITLE'], 'people[TITLE]', 'Title', array('Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.', 'Miss' => 'Miss', 'Dr' => 'Dr', 'Rev' => 'Rev'), '') . '</div></div><div class="col-md-4"><div class="form-group">' . TextInput($staff['FIRST_NAME'], 'people[FIRST_NAME]', '<FONT class=red>First</FONT>', 'id=fname size="20" maxlength=50 class=cell_floating') . '</div></div><div class="col-md-3"><div class="form-group">' . TextInput($staff['MIDDLE_NAME'], 'people[MIDDLE_NAME]', 'Middle', 'size="18" maxlength=50 class=cell_floating') . '</div></div><div class="col-md-3"><div class="form-group">' . TextInput($staff['LAST_NAME'], 'people[LAST_NAME]', '<FONT color=red>Last</FONT>', 'id=lname size="20" maxlength=50 class=cell_floating') . '</div></div>';
    echo '</div>'; //.row
} else {
    echo '<div class="row" id="user_name">';
    echo '<div onclick=\'addHTML("<div class=col-md-2><div class=form-group>' . str_replace('"', '\"', SelectInput($staff['TITLE'], 'people[TITLE]', 'Title', array('Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.', 'Miss' => 'Miss', 'Dr' => 'Dr', 'Rev' => 'Rev'), '', '', false)) . '</div></div><div class=col-md-4><div class=form-group>' . str_replace('"', '\"', TextInput($staff['FIRST_NAME'], 'people[FIRST_NAME]', (!$staff['FIRST_NAME'] ? '<FONT color=red>' : '') . 'First' . (!$staff['FIRST_NAME'] ? '</FONT>' : ''), 'id=fname size=20 maxlength=50', false)) . '</div></div><div class=col-md-3><div class=form-group>' . str_replace('"', '\"', TextInput($staff['MIDDLE_NAME'], 'people[MIDDLE_NAME]', 'Middle', 'size=18 maxlength=50', false)) . '</div></div><div class=col-md-3><div class=form-group>' . str_replace('"', '\"', TextInput($staff['LAST_NAME'], 'people[LAST_NAME]', (!$staff['LAST_NAME'] ? '<FONT color=red>' : '') . 'Last' . (!$staff['LAST_NAME'] ? '</FONT>' : ''), 'id=lname size=20 maxlength=50', false)) . '</div></div>","user_name",true);\'><div class="col-md-12"><div class=form-group><label class="col-md-2 control-label text-right">'._name.' <span class="text-danger">*</span></label><div class="col-md-10"><div class="form-control" readonly>' . (!$staff['TITLE'] && !$staff['FIRST_NAME'] && !$staff['MIDDLE_NAME'] && !$staff['LAST_NAME'] ? '-' : $staff['TITLE'] . ' ' . $staff['FIRST_NAME'] . ' ' . $staff['MIDDLE_NAME'] . ' ' . $staff['LAST_NAME']) . '</div></div></div></div></div>';
    echo '</div>'; //.row
}

echo '<div class="row">';
if ($_REQUEST['staff_id'] != 'new') {
    echo '<div class="col-md-6">';
}
echo '<div class="form-group">';
echo '<label class="control-label text-right col-lg-4" for="people[EMAIL]">'._emailAddress.' </label>';
echo '<div class="col-lg-8">' . TextInput($staff['EMAIL'], 'people[EMAIL]', '', 'size=25 maxlength=100 id=email class=form-control onkeyup=check_email(this,' . $id_sent . ',4); onblur=check_email(this,' . $id_sent . ',4)') . '<p class="help-block" id="email_error"></p></div>';
echo '</div>';

if ($_REQUEST['staff_id'] != 'new') {

    echo '</div>'; //.col-md-6
    $det = DBGet(DBQuery('SELECT HOME_PHONE,WORK_PHONE,CELL_PHONE,EMAIL FROM people WHERE STAFF_ID=' . $staff['STAFF_ID']));
    $det = $det[1];

    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo TextInput($det['HOME_PHONE'], 'people[HOME_PHONE]', _homePhone, 'size=25 maxlength=100 class=form-control');
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo TextInput($det['WORK_PHONE'], 'people[WORK_PHONE]', _workPhone, 'size=25 maxlength=100  class=form-control');
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6

    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo TextInput($det['CELL_PHONE'], 'people[CELL_PHONE]', _cellPhone, 'size=25 maxlength=100 class=form-control');
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6
}

echo '</div>'; //.row


if ($_REQUEST['staff_id'] != 'new') {

    echo '<div class="row">';
    echo '<div class="col-md-6"><div class="form-group"><label class="control-label text-right col-lg-4">'._disableUser.'</label><div class="col-lg-8">' . CheckboxInput($staff['IS_DISABLE'], 'people[IS_DISABLE]', '', 'CHECKED', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') . '</div></div></div>';
    echo '<div class="col-md-6"><div class="form-group">' . NoInput(ProperDate(substr($staff['LAST_LOGIN'], 0, 10)) . substr($staff['LAST_LOGIN'], 10), _lastLogin) . '</div></div>';
    echo '</div>';
}

echo '<div class="row">';
if ($_REQUEST['staff_id'] != 'new') {
    echo '<div class="col-md-6"><div class="form-group">' . NoInput($staff['STAFF_ID'], _userId) . '</div></div>';
}
if (basename($_SERVER['PHP_SELF']) != 'index.php') {

    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo '<label class="control-label text-right col-lg-4">'._userProfile.'</label>';
    echo '<div class="col-lg-8">';
    unset($options);
    if ($staff['PROFILE'] == 'Parent') {
        $profiles_options = DBGet(DBQuery('SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID = 4 ORDER BY ID'));
    } else {
        if ($_REQUEST['modname'] == 'users/User.php')
            $profiles_options = DBGet(DBQuery('SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID = 4 ORDER BY ID'));
        else
            $profiles_options = DBGet(DBQuery('SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID NOT IN (4,3) ORDER BY ID'));
    }
    $i = 1;
    foreach ($profiles_options as $options) {
        $option[$options['ID']] = $options['TITLE'];
        $i++;
    }

    $user_profs = DBGet(DBQuery('SELECT * FROM user_profiles WHERE profile = \'' . 'parent' . '\''));

    foreach ($user_profs as $user_profs_value) {
        $user_prof_options[$user_profs_value['ID']] = $user_profs_value['TITLE'];
    }

    echo SelectInput($staff['PROFILE_ID'], 'people[PROFILE_ID]', (!$staff['PROFILE'] ? '<FONT color=red>' : '') . '' . (!$staff['PROFILE'] ? '</FONT>' : ''), $user_prof_options, FALSE);

    echo '</div>'; //.col-lg-8
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-4

    $schools_RET = DBGet(DBQuery('SELECT s.ID,s.TITLE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=' . UserSyear() . ' AND st.staff_id=' . User('STAFF_ID')));
    unset($options);
    if (count($schools_RET) && User('PROFILE') == 'admin') {
        $i = 0;
        $_SESSION['staff_school_chkbox_id'] = 0;
        if ($staff['STAFF_ID'])
            $schools = GetUserSchools($staff['STAFF_ID']);
    }
}
echo '</div>'; //.row

echo '<div class="row">';
if ($_REQUEST['profile'] != 'none') {

    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo '<label for="login_authentication[USERNAME]" class="control-label text-right col-lg-4">Username</label>';
    echo '<div class="col-lg-8">';
    if (User('PROFILE') == 'admin') {
        echo TextInput($staff['USERNAME'], 'login_authentication[USERNAME]', '', 'size=25 maxlength=100 class=form-control onkeyup=\"usercheck_init(this, ' . $staff['STAFF_ID'] . ', ' . $staff['PROFILE_ID'] . ')\"');
        echo '<div id="ajax_output"></div>';
    }
    else
        echo NoInput($staff['USERNAME']);
    echo '</div>'; //.col-md-8
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6

    echo '<div class="col-md-6">';
    echo '<div class="form-group">';
    echo '<label class="control-label text-right col-lg-4">'._password.'</label>';
    echo '<div class="col-lg-8">';
    if (!isset($staff['STAFF_ID'])) {
        //for adding new user
        echo TextInput(str_repeat('*', strlen($staff['PASSWORD'])), 'login_authentication[PASSWORD]', '', "size=25 maxlength=100 class=cell_floating AUTOCOMPLETE = off onkeyup=passwordStrength(this.value);validate_password(this.value);");
    } else {
        //for existing users while updating
        echo TextInput(str_repeat('*', strlen($staff['PASSWORD'])), 'login_authentication[PASSWORD]', '', "size=25 maxlength=100 class=cell_floating AUTOCOMPLETE = off onkeyup=passwordStrength(this.value);validate_password(this.value,$staff[STAFF_ID]);");
    }
    echo '<p id="passwordStrength" class="help-block"></p>';
    echo '</div>'; //.col-lg-8
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
} else {
    echo '<div class="col-md-4">';
    echo '<div class="form-group">';
    echo '<div class="checkbox"><label><input type="checkbox" onClick="toggle_div_visibility_mod(this,\'portal_users_div\');" />Portal User</label></div>';
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    echo '<div id="portal_users_div" style="display:none;">';
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="form-group">';
    echo TextInput('', 'FRESH_USERNAME', 'Username', 'size=25 maxlength=100  onkeyup="usercheck_init_noacess(this)"');
    echo '<div id="ajax_output"></div>';
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-4

    echo '<div class="col-md-4">';
    echo '<div class="form-group">';
    echo TextInput('', 'FRESH_PASSWORD', 'Password', "size=25 maxlength=100 AUTOCOMPLETE = off onkeyup=passwordStrength(this.value);validate_password(this.value);");
    echo "<span id='passwordStrength'></span>";
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row
    echo '</div>';
}

include('modules/users/includes/OtherInfoUserInc.php');

echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="col-md-12">';

$parent_prof = DBGet(DBQuery('SELECT title FROM user_profiles WHERE profile = \'' . 'parent' . '\''));
$parent_profs_arr = array();
foreach ($parent_prof as $k => $v) {
    $parent_profs_arr[] = $parent_prof[$k]['TITLE'];
}

echo '</div>';
echo '</div>'; //.row

if (in_array($staff['PROFILE'], $parent_profs_arr)) {
    echo '<hr class="no-margin"/>';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<h5>'._associatedStudents.' </h5>';
    $sql = 'SELECT s.STUDENT_ID,CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,gr.TITLE AS GRADE ,sc.TITLE AS SCHOOL FROM students s,student_enrollment ssm,school_gradelevels gr,schools sc,students_join_people sjp WHERE s.STUDENT_ID=ssm.STUDENT_ID AND s.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=' . $staff['STAFF_ID'] . ' AND ssm.SYEAR=' . UserSyear() . ' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND ssm.GRADE_ID=gr.ID AND ssm.SCHOOL_ID=sc.ID AND (ssm.END_DATE IS NULL OR ssm.END_DATE =  \'0000-00-00\' OR ssm.END_DATE >=  \'' . date('Y-m-d') . '\')';
    $students = DBGet(DBQuery($sql));
    foreach ($students as $sti => $std) {
        $get_relation = DBGet(DBQuery('SELECT RELATIONSHIP FROM students_join_people WHERE STUDENT_ID=' . $std['STUDENT_ID'] . ' AND PERSON_ID=' . $staff['STAFF_ID']));
        $students[$sti]['RELATIONSHIP'] = $get_relation[1]['RELATIONSHIP'];
    }
    $columns = array('FULL_NAME' =>_name,
     'RELATIONSHIP' =>_relationship,
     'GRADE' =>_gradeLevel,
     'SCHOOL' =>_schoolName,
    );


    if (User('PROFILE_ID') == 0 || User('PROFILE_ID') == 1) {
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&staff_id=$staff[STAFF_ID]&modfunc=remove_stu" . ($_REQUEST['profile'] == 'none' ? '&profile=none' : '');
        $link['remove']['variables'] = array('id' => 'STUDENT_ID');
    }
    ListOutput($students, $columns,  _student, _students, $link, array(), array('search' =>false));
    echo '</div>'; //.col-md-12
    echo '</div>'; //.row
}

echo '</div>'; //.row

$_REQUEST['category_id'] = 1;

function _makeStartInputDate($value, $column) {
    global $THIS_RET;

    if ($_REQUEST['staff_id'] == 'new') {
        $date_value = '';
    } else {

        $sql = 'SELECT ssr.START_DATE FROM staff s,staff_school_relationship ssr  WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        $user_exist_school = DBGet(DBQuery($sql));
        if ($user_exist_school[1]['START_DATE'] == '0000-00-00' || $user_exist_school[1]['START_DATE'] == '')
            $date_value = '';
        else
            $date_value = $user_exist_school[1]['START_DATE'];
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . DateInput2($date_value, 'values[START_DATE][' . $THIS_RET['ID'] . ']', '1' . $THIS_RET['ID'], '') . '</TD></TR></TABLE>';
}

function _makeUserProfile($value, $column) {
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new') {
        $profile_value = '';
    } else {

        $sql = 'SELECT up.TITLE FROM staff s,staff_school_relationship ssr,user_profiles up  WHERE ssr.STAFF_ID=s.STAFF_ID AND up.ID=s.PROFILE_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        $user_profile = DBGet(DBQuery($sql));
        $profile_value = $user_profile[1]['TITLE'];
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . $profile_value . '</TD></TR></TABLE>';
}

function _makeEndInputDate($value, $column) {
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new') {
        $date_value = '';
    } else {

        $sql = 'SELECT ssr.END_DATE FROM staff s,staff_school_relationship ssr  WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        $user_exist_school = DBGet(DBQuery($sql));
        if ($user_exist_school[1]['END_DATE'] == '0000-00-00' || $user_exist_school[1]['END_DATE'] == '')
            $date_value = '';
        else
            $date_value = $user_exist_school[1]['END_DATE'];
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . DateInput2($date_value, 'values[END_DATE][' . $THIS_RET['ID'] . ']', '2' . $THIS_RET['ID'] . '', '') . '</TD></TR></TABLE>';
}

function _makeCheckBoxInput_gen($value, $column) {
    global $THIS_RET;

    $_SESSION['staff_school_chkbox_id'] ++;
    $staff_school_chkbox_id = $_SESSION['staff_school_chkbox_id'];
    if ($_REQUEST['staff_id'] == 'new') {
        return '<TABLE class=LO_field><TR>' . '<TD>' . CheckboxInput('', 'values[SCHOOLS][' . $THIS_RET['ID'] . ']', '', '', true, '<IMG SRC=assets/check.gif width=15>', '<IMG SRC=assets/x.gif width=15>', true, 'id=staff_SCHOOLS' . $staff_school_chkbox_id) . '</TD></TR></TABLE>';
    } else {

        $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ') AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE=\'0000-00-00\')  ';

        $user_exist_school = DBGet(DBQuery($sql));
        if (!empty($user_exist_school))
            return '<TABLE class=LO_field><TR>' . '<TD>' . CheckboxInput('Y', 'values[SCHOOLS][' . $THIS_RET['ID'] . ']', '', '', true, '<IMG SRC=assets/check.gif width=15>', '<IMG SRC=assets/x.gif width=15>', true, 'id=staff_SCHOOLS' . $staff_school_chkbox_id) . '</TD></TR></TABLE>';
        else
            return '<TABLE class=LO_field><TR>' . '<TD>' . CheckboxInput('', 'values[SCHOOLS][' . $THIS_RET['ID'] . ']', '', '', true, '<IMG SRC=assets/check.gif width=15>', '<IMG SRC=assets/x.gif width=15>', true, 'id=staff_SCHOOLS' . $staff_school_chkbox_id) . '</TD></TR></TABLE>';
    }
}

function _makeStatus($value, $column) {
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new')
        $status_value = '';
    else {

        $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ') AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE=\'0000-00-00\') ';

        $user_exist_school = DBGet(DBQuery($sql));
        if (!empty($user_exist_school))
            $status_value = 'Active';
        else {
            $get_prev_schools = DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM staff_school_relationship WHERE STAFF_ID=\'' . $_SESSION['staff_selected'] . '\' AND  SCHOOL_ID=\'' . $THIS_RET['SCHOOL_ID'] . '\' '));
            if ($get_prev_schools[1]['TOTAL'] != 0)
                $status_value = 'Inactive';
            else
                $status_value = '';
        }
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . $status_value . '</TD></TR></TABLE>';
}

?>
