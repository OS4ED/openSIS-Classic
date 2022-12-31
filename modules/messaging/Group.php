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
include('lang/language.php');

DrawBC(""._messaging." > " . ProgramTitle());

$curProfile = User('PROFILE');
$userName = User('USERNAME');

if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 4) {
    echo "<FONT style=color:green>"._groupIsSuccessfullyDeleted.". </FONT>";
}
if (!isset($_REQUEST['modfunc'])) {

    echo "<FORM name=Group id=Group action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=group method=POST >";
    //PopTable('header', 'Group');
    echo "<div id='students' class='panel panel-default'>";
    $custom_header = "<h6 class=\"panel-title text-pink\">"._groups."</h6><div class=\"heading-elements\"><a href='#' class=\"btn btn-default heading-btn\" onclick='load_link(\"Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=add_group\");'> "._addGroup."</a></div>";

    $select = "SELECT *  from mail_group  WHERE USER_NAME ='$userName' AND SCHOOL_ID= '".UserSchool()."'";
    $link['GROUP_NAME']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=groupmember";
    $link['GROUP_NAME']['variables'] = array('group_id' => 'GROUP_ID');
    $columns = array('GROUP_NAME' => _groupName, 'DESCRIPTION' => _description, 'CREATION_DATE' => _createDate, 'MEMBERS' => _members , 'action' => _action);
    $list = DBGet(DBQuery($select), array('CREATION_DATE' => 'ProperDate'));

    foreach ($list as $id => $value) {

        $qr = DBGet(DBQuery('SELECT COUNT(*) AS MEMBERS FROM mail_groupmembers where group_id=' . $list[$id]['GROUP_ID'] . ''));

        $list[$id]['MEMBERS'] = $qr[1]['MEMBERS'];
        if ($list[$id]['DESCRIPTION'] == "N")
            $list[$id]['DESCRIPTION'] = '';
        if ($list[$id]['action'] == "") {
            $list[$id]['action'] = "<a href='Modules.php?modname=$_REQUEST[modname]&modfunc=groupmember&group_id=$value[GROUP_ID]'>" . button('edit') . "</a>&nbsp;&nbsp;<a href='Modules.php?modname=$_REQUEST[modname]&modfunc=delete&group_id=$value[GROUP_ID]'>" . button('remove', '', '', '', 'text-danger') . "</a>";
        }
    }
    //ListOutput($list, $columns, 'Group', 'Groups', $link, array(), array('search' => false), '');

    ListOutputMessagingGroups($list, $columns, '', '', $link, array(), array('search' => false), '', $custom_header);
    //PopTable('footer');
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'delete') {
    $group_id = $_REQUEST['group_id'];
    $members = DBGet(DBQuery("select count(*) as countmember from mail_groupmembers where group_id=" . $group_id . ""));
    $count_members = $members[1]['COUNTMEMBER'];
    if ($count_members > 0) {
        if (DeleteMail(""._groupWith." " . $count_members . " "._groupMembers."", ''.strtolower(_delete).'', $_REQUEST['modname'])) {
            $member_del = "delete from mail_groupmembers where group_id=" . $group_id . "";
            $member_del_execute = DBQuery($member_del);
            $mail_delete = "delete from mail_group where group_id =" . $group_id . "";
            $mail_delete_ex = DBQuery($mail_delete);
            unset($_REQUEST['modfunc']);
            echo "<script>load_link('Modules.php?modname=messaging/Group.php&msg=4')</script>";
        }
    } else {
        if (DeleteMail(''._group.'', ''.strtolower(_delete).'', $_REQUEST['modname'])) {
            $mail_delete = "delete from mail_group where group_id =" . $group_id . "";
            $mail_delete_ex = DBQuery($mail_delete);
            unset($_REQUEST['modfunc']);
            echo "<script>load_link('Modules.php?modname=messaging/Group.php&msg=4')</script>";
        }
    }
    unset($_REQUEST['modfunc']);
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'groupmember') {
    echo "<FORM name=sav class=\"form-horizontal\" id=sav action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=members&groupid=" . strip_tags(trim($_REQUEST['group_id'])) . " method=POST>";

    //PopTable('header', 'Group Members');
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading">';
    echo '<h6 class="panel-title text-pink">'._groupMembers.'</h6>';
    echo '</div>';
    echo '<div class="panel-body">';

    $member = "select * from mail_groupmembers where GROUP_ID='" . $_REQUEST['group_id'] . "'";
    $member_list = DBGet(DBQuery($member));
    foreach ($member_list as $key => $value) {
        $member_list[$key]['PROFILE'];
        $select = "SELECT * FROM user_profiles WHERE ID='" . $member_list[$key]['PROFILE'] . "'";
        $profile = DBGet(DBQuery($select));
        $member_list[$key]['PROFILE'] = $profile[1]['PROFILE'];
    }
    $columns = array('USER_NAME' => _userName, 'PROFILE' => _profile);
    $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
    $extra['LO_group'] = array('GROUP_ID');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'group\');" checked><A>');
    $extra['new'] = true;
    if (is_array($extra['columns_before'])) {
        $LO_columns = $extra['columns_before'] + $columns;
        $columns = $LO_columns;
    }
    foreach ($member_list as $id => $value) {
        $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=group[" . $value['ID'] . "] value=Y CHECKED>";
        $member_list[$id] = $extra['columns_before'] + $value;
    }
    $group = "select GROUP_NAME,DESCRIPTION from mail_group where GROUP_ID=$_REQUEST[group_id]";
    $groupDetails = DBGet(DBQuery($group));
    $groupname = $groupDetails[1]['GROUP_NAME'];
    $groupdesc = ($groupDetails[1]['DESCRIPTION'] == 'N' ? '' : $groupDetails[1]['DESCRIPTION']);

    echo '<div class="row">';
    echo '<div class="col-md-4">';

    echo '<div class="row"><label class="col-md-4 control-label text-right">'._groupName.': </label>';
    echo '<div class="col-md-8">';
    echo TextInput($groupname, 'groupname', '', 'maxlength=50', false);
    echo '</div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-4
    echo '<div class="col-md-8">';

    echo '<div class="row"><label class="col-md-2 control-label text-right">'._description.': </label>';
    echo '<div class="col-md-10">';
    echo TextInput($groupdesc, 'groupdesc', '', 'maxlength=50', false);
    echo '</div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    echo '<input type=hidden name="gid" value="' . strip_tags(trim($_REQUEST['group_id'])) . '">';


    for ($i = 0; $i < strlen($groupname); $i++) {
        if ($groupname[$i] == " ")
            $groupname[$i] = str_replace(" ", "_", $groupname[$i]);
        else if ($groupname[$i] == "'")
            $groupname[$i] = str_replace("'", "\\", $groupname[$i]);
    }
    $grp = $groupname;

    if ($groupdesc == 'N')
        $groupdesc = 'N';
    else {
        for ($i = 0; $i < strlen($groupdesc); $i++) {
            if ($groupdesc[$i] == " ")
                $groupdesc[$i] = str_replace(" ", "_", $groupdesc[$i]);
            else if ($groupdesc[$i] == "'")
                $groupdesc[$i] = str_replace("'", "\\", $groupdesc[$i]);
        }
    }
    $gid = $_REQUEST['group_id'];
    echo '</div>';
    echo '<hr class="m-0" />';
    echo '<div id="members">';
    $custom_header = '<h6 class="panel-title">' . count($member_list) . ' '._member.'' . (((count($member_list)) > 1) ? "s" : '') . '</h6><div class="heading-elements"><a class="btn btn-default heading-btn" href="Modules.php?modname=' . $_REQUEST[modname] . '&modfunc=exist_group&group_name=' . $grp . '&desc=' . $groupdesc . '&grp_id=' . $gid . '"> '._addMember.'</a></div>';
    //ListOutput($member_list, $columns, 'Member', 'Members', '', array(), array('search' => false, 'save' => false), '', $custom_header);
    ListOutputMessagingGroups($member_list, $columns, _member, _members, '', array(), array('search' => false, 'save' => false), '', $custom_header);
    echo '</div>';
    if (isset($userName)) {
        echo '<div class="panel-footer text-right p-r-20"><INPUT type=submit class="btn btn-primary" value='._save.' onclick="self_disable(this);" ></div>';
    }
    echo '</div>'; //.panel
    //PopTable('footer', $btn);
    echo '</FORM>';
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'exist_group') {

    $grp_name = $_REQUEST['group_name'];

    echo "<FORM class=\"form-horizontal\" name=search action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=add_group_member&search=true&group_id=$grp_name&desc=" . strip_tags(trim($_REQUEST['desc'])) . "&grp_id=" . strip_tags(trim($_REQUEST['grp_id'])) . " method=POST>";
    
    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading">';
    echo '<h6 class="panel-title text-pink">'._addGroupMember.'</h6>';
    echo '</div>';
    echo '<div class="panel-body">';
    //PopTable('header', '');
    echo '<div class="row">';
    echo '<div class="col-md-6">';

    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._lastName.'</label>';
    echo '<div class="col-md-8"><INPUT type=text class="form-control" placeholder="'._lastName.'" name=last></div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-6
    echo '<div class="col-md-6">';

    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._firstName.'</label>';
    echo '<div class="col-md-8"><INPUT type=text class="form-control" placeholder="'._firstName.'" name=first></div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row    
    echo '<div class="row">';
    echo '<div class="col-md-6">';

    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._username.'</label>';
    echo '<div class="col-md-8"><INPUT type=text class="form-control" placeholder="'._username.'" name=username></div>'; //.col-md-8
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-6 

    if (User('PROFILE') == 'teacher') {
        $profiles = DBGet(DBQuery('SELECT * FROM user_profiles where id!=2'));
    } else if (User('PROFILE') == 'parent') {
        $profiles = DBGet(DBQuery('SELECT * FROM user_profiles where id!=4'));
    } else if (User('PROFILE') == 'student') {
        $profiles = DBGet(DBQuery('SELECT * FROM user_profiles where id!=3'));
    } else
        $profiles = DBGet(DBQuery('SELECT * FROM user_profiles'));
    $options[-1] = 'N/A';


    foreach ($profiles as $key => $value) {
        $options[$value['ID']] = $value['TITLE'];
    }
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._profile.'</label>';
    echo '<div class="col-md-8"><SELECT name=profile class="form-control">';
    foreach ($options as $key => $val)
        echo '<OPTION value="' . $key . '">' . $val;
    echo '</SELECT></div>';
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row m-t-15">';
    echo '<div class="col-md-12">';
    if ($extra['search'])
        echo $extra['search'];
    echo '<div class="form-group m-b-0"><label class="col-md-4 control-label text-right">&nbsp;</label>';
    echo '<div class="col-md-8">';
    if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher' || User('PROFILE') == 'parent')
        echo '<label class="checkbox-inline checkbox-switch switch-xs switch-success"><INPUT type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '><span></span> '._searchAllSchools.'</label>';
    echo '<label class="checkbox-inline checkbox-switch switch-xs switch-success"><INPUT type=checkbox name=_dis_user value=Y><span></span> '._includeDisabledUser.'</label>';
    echo '</div>'; //.col-md-8
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-12
    echo '</div>'; //.row

    echo '</div>'; //.panel-body    
    echo "<div class=\"panel-footer text-right p-r-20\"><INPUT type=SUBMIT class='btn btn-primary' value='"._submit."' onclick='self_disable(this);' > &nbsp; <INPUT type=RESET class='btn btn-default' value='"._reset."'></div>";

    echo '</div>'; //.panel
    /*     * ******************for Back to user************************** */
    echo '<input type=hidden name=sql_save_session_staf value=true />';
    //PopTable('footer', $btn);
    /*     * ********************************************* */
    echo '</FORM>';
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'add_group_member') {

    $groupname = $_REQUEST['group_id'];

    $grp_id = $_REQUEST['grp_id'];
    $group_details = DBGet(DBQuery('SELECT * from mail_group WHERE group_id=\'' . $grp_id . '\''));
    $groupname = $group_details[1]['GROUP_NAME'];
    $desc = $group_details[1]['DESCRIPTION'];
    $_REQUEST['group_id'] = $groupname;

    if ($desc == 'No')
        $desc = "";
    else {
        
    }

    echo "<FORM name=Group class=\"form-horizontal\" id=Compose action=Modules.php?modname=messaging/Group.php&modfunc=member_insert&grp_id=" . strip_tags(trim($_REQUEST['grp_id'])) . " method=POST >";

    PopTable('header', _group);

    echo '<div class="row">';
    echo '<div class="col-md-4">';

    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._groupName.'</label>';
    echo '<div class="col-md-8">' . TextInput_mail($_REQUEST['group_id'], 'txtExistGrpName', '', 'class="form-control" readonly') . '</div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-4
    echo '<div class="col-md-4">';

    echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._description.'</label>';
    echo '<div class="col-md-8">' . TextInput_mail($desc, 'txtExistGrpDesc', '', 'class="form-control" readonly') . '</div>'; //.col-md-8
    echo '</div>'; //.form-group

    echo '</div>'; //.col-md-4
    echo '</div>'; //.row


    $lastName = $_REQUEST['last'];
    $firstName = $_REQUEST['first'];
    $userName = $_REQUEST['username'];
    $profile = $_REQUEST['profile'];
    $disable = $_REQUEST['_dis_user'];
    $allschools = $_REQUEST['_search_all_schools'];

    echo '<input type=hidden value=' . $profile . ' name=profile>';
    if (isset($_REQUEST['group_id'])) {
        $select1 = "select * from mail_group where GROUP_ID='" . $_REQUEST['grp_id'] . "'";
        $groupselect = DBGet(DBQuery($select1));

        $member = "select * from mail_groupmembers where GROUP_ID=" . $groupselect[1]['GROUP_ID'] . "";
        $existuser = DBGet(DBQuery($member));
        $existuser = DBGet(DBQuery($member));
        foreach ($existuser as $id => $value) {
            $usernames[] = array('PROFILE_ID' => $existuser[$id]['PROFILE'], 'USERNAME' => $existuser[$id]['USER_NAME']);
        }

        foreach ($usernames as $id => $value) {
            if ($value['PROFILE_ID'] != 3 || $value['PROFILE_ID'] != 4) {
                $staff = "select * from login_authentication,staff where login_authentication.user_id=staff.staff_id and USERNAME='$value[USERNAME]' and login_authentication.profile_id not in(3)";
                $stafflist = DBGet(DBQuery($staff));
                $staff_id[] = $stafflist[1]['STAFF_ID'];
            }
            if ($value['PROFILE_ID'] == 3) {
                $stu = "select * from login_authentication,students where login_authentication.user_id=students.student_id and profile_id=3 and USERNAME='$value[USERNAME]'";
                $stulist = DBGet(DBQuery($stu));
                $stu_id[] = $stulist[1]['STUDENT_ID'];
            }
        }
        $staff_id = is_array($staff_id) ? array_filter($staff_id) : '';
        $stu_id = is_array($stu_id) ? array_filter($stu_id) : '';

        if ($profile != -1) {//search by profile  
            if ($profile == 3) {//students
                if (User('PROFILE') == 'teacher')
                    $user = "SELECT * FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING ( course_period_id ) WHERE course_periods.teacher_id = " . UserID() . ")";
                elseif (User('PROFILE') == 'parent') {
                    $parent_id = UserID();
                    $qr = DBGet(DBQuery('Select STUDENT_ID from students_join_people where person_id=\'' . $parent_id . '\''));
                    $student_id = $qr[1]['STUDENT_ID'];
                    $user = "SELECT * FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id  and students.student_id in (Select STUDENT_ID from students_join_people where person_id=" . $parent_id . ") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ")";
                } elseif (UserProfileID() == 1 || UserProfileID() == 5) {
                    $user = "select * from students,login_authentication,student_enrollment WHERE profile_id=3 and login_authentication.user_id=students.student_id and students.student_id=student_enrollment.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where staff_id=" . UserID() . ") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ")";
                } else {

                    $user = "select * from students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ")";
                }
            }


            if ($profile == 2) {//teachers
                if (User('PROFILE') == 'parent') {
                    $parent_id = UserID();
                    $qr = DBGet(DBQuery('Select STUDENT_ID from students_join_people where person_id=\'' . $parent_id . '\''));
                    $student_id = $qr[1]['STUDENT_ID'];

                    $user = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND staff_id  IN (SELECT distinct(course_periods.teacher_id) FROM course_periods,schedule where schedule.course_period_id=course_periods.course_period_id and schedule.student_id in (Select STUDENT_ID from students_join_people where person_id=" . $parent_id . "))";
                } else if (User('PROFILE') == 'student') {
                    $studentId = UserStudentID();
                    $user = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND staff_id IN(Select distinct teacher_id from course_periods INNER JOIN schedule using(course_period_id) where schedule.student_id=" . $studentId . ")";
                } else if (UserProfileID() == 1 || UserProfileID() == 5) {
                    $user = "SELECT * FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id  and staff_school_relationship.staff_id=staff.staff_id  and staff_school_relationship.school_id in (select school_id from staff_school_relationship where staff_id=" . UserID() . ")  and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . " )";
                } else {
                    $user = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . " )";
                }
            }
            if ($profile == 4) {//parents
                if (User('PROFILE') == 'teacher') {
                    $teacher_id = UserID();
                    $user = 'SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id=' . $profile . ' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=' . $groupselect[1]['GROUP_ID'] . ' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (SELECT student_id FROM students WHERE student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING (course_period_id ) WHERE course_periods.teacher_id = \'' . $teacher_id . '\')))';
                } else if (User('PROFILE') == 'admin') {
                    $user = 'SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id=' . $profile . ' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=' . $groupselect[1]['GROUP_ID'] . ' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' ';
                } else if (User('PROFILE') == 'student') {
                    $student_id = UserStudentID();
                    $user = 'SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id=' . $profile . ' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=' . $groupselect[1]['GROUP_ID'] . ' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id=' . $student_id . ' )';
                }
            }
            if ($profile == 0 || $profile == 1 || $profile == 5) {//all types of admin
                $user = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=$profile and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ")";
            }
            if ($lastName != "") {
                $user = $user . " AND LAST_NAME LIKE '$lastName%' ";
            }
            if ($firstName != "") {
                $user = $user . " AND FIRST_NAME LIKE '$firstName%' ";
            }
            if ($userName != "") {
                $user = $user . " AND USERNAME LIKE '$userName%' ";
            }
            if ($disable == '' && ($profile == 3 || $profile == 4)) {//only enabled students 
                $user = $user . " AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
            }
            if ($disable == '' && $profile != 3 && $profile != 4) {//only enabled users
                $user = $user . " AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
                $user = $user . "  GROUP BY staff.staff_id ";
            }
            if ($disable == 'Y') {//with disabled users
                $user = $user . " ";
                if ($profile != 3 && $profile != 4)
                    $user = $user . "  GROUP BY staff.staff_id ";
            }
        } else {
            if (User('PROFILE') == 'admin' && UserProfileID() == 0) {//all types of admin
                $user1 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id  AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id not in(3,4)";
                $user2 = "SELECT  DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students WHERE login_authentication.user_id=students.student_id AND login_authentication.profile_id=3 AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id=3";
                $user3 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id=4";
            }
            if (UserProfileID() == 1 || UserProfileID() == 5) {//all types of admin
                $user1 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id and staff_school_relationship.staff_id=staff.staff_id  and staff_school_relationship.school_id in (select school_id from staff_school_relationship where staff_id=" . UserID() . ") AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id not in(3,4)";
                $user2 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students,student_enrollment WHERE login_authentication.user_id=students.student_id and students.student_id=student_enrollment.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where staff_id=" . UserID() . ")AND login_authentication.profile_id=3 AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id=3";
                $user3 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students_join_people,people  WHERE login_authentication.user_id=people.staff_id AND students_join_people.person_id in(select school_id from students,student_enrollment,students_join_people  where students.student_id=student_enrollment.student_id and students_join_people.student_id=students.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where  staff_id=" . UserID() . ")) and TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id=4";
            }
            if (User('PROFILE') == 'teacher') {//teachers
                $user1 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id and staff_school_relationship.staff_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id in(0,1,5) and school_id in(select school_id from staff_school_relationship where staff_id=" . UserID() . ")"; //all types of admin
                $user2 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING ( course_period_id ) WHERE course_periods.teacher_id = " . UserID() . ")"; //scheduled students
                $user3 = 'SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=' . $groupselect[1]['GROUP_ID'] . ' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (SELECT student_id FROM students WHERE student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING (course_period_id ) WHERE course_periods.teacher_id = \'' . UserID() . '\')))'; //parents                  
            }
            if (User('PROFILE') == 'parent') {//parents
                $user1 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id in(0,1,5)"; //all types of admin
                $parent_id = UserID();
                $user2 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2  AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND staff_id  IN (SELECT distinct(course_periods.teacher_id) FROM course_periods,schedule where schedule.course_period_id=course_periods.course_period_id and schedule.student_id in (Select STUDENT_ID from students_join_people where person_id=" . $parent_id . "))";
                $user3 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id  and students.student_id in (Select STUDENT_ID from students_join_people where person_id=" . $parent_id . ") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ")";
            }
            if (User('PROFILE') == 'student') {//students
                $user1 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") and login_authentication.profile_id in(0,1,5)"; //all types of admin

                $user2 = "SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2  AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=" . $groupselect[1]['GROUP_ID'] . ") AND staff_id IN(Select distinct teacher_id from course_periods INNER JOIN schedule using(course_period_id) where schedule.student_id=" . UserStudentID() . ")"; //teachers                 

                $student_id = UserStudentID();
                $user3 = 'SELECT DISTINCT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=' . $groupselect[1]['GROUP_ID'] . ' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id=' . $student_id . ' )';
            }
            if ($lastName != "") {
                $user1 = $user1 . " AND LAST_NAME LIKE '$lastName%' ";
                $user2 = $user2 . " AND LAST_NAME LIKE '$lastName%' ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user3 = $user3 . " AND LAST_NAME LIKE '$lastName%' ";
            }
            if ($firstName != "") {
                $user1 = $user1 . " AND FIRST_NAME LIKE '$firstName%' ";
                $user2 = $user2 . " AND FIRST_NAME LIKE '$firstName%' ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user3 = $user3 . " AND FIRST_NAME LIKE '$firstName%' ";
            }
            if ($userName != "") {
                $user1 = $user1 . " AND USERNAME LIKE '$userName%' ";
                $user2 = $user2 . " AND USERNAME LIKE '$userName%' ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user3 = $user3 . " AND USERNAME LIKE '$userName%' ";
            }
            if ($disable == '' && ($profile == 3 || $profile == 4)) {//only enabled students 
                $user1 = $user1 . " AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
                $user2 = $user2 . " AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user3 = $user3 . " AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
            }
            if ($disable == '' && $profile != 3 && $profile != 4) {//only enabled users
                $user1 = $user1 . " AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
                $user2 = $user2 . " AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user3 = $user3 . " AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
            }
            if ($disable == 'Y') {//with disabled users
                $user1 = $user1 . " ";
                $user2 = $user2 . " ";
                if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
                    $user2 = $user2 . " ";
            }
            if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher' || User('PROFILE') == 'parent' || User('PROFILE') == 'student')
                $user = $user1 . " UNION ALL " . $user2 . " UNION ALL " . $user3;
            else
                $user = $user1 . " UNION ALL " . $user2;
        }
        $userlist = DBGet(DBQueryMod($user));

        if ($_REQUEST['_search_all_schools'] != 'Y') {
            $final_arr = $_arr = $exist_username_arr = array();

            foreach ($userlist as $key => $value) {

                if ($userlist[$key]['PROFILE_ID'] == 3) {
                    $select = "SELECT  se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=" . $userlist[$key]['PROFILE_ID'] . " and se.school_id=" . UserSchool() . " AND se.student_id='" . $userlist[$key]['USER_ID'] . "' group by student_id";
                    $profile = DBGet(DBQuery($select));
                    foreach ($profile as $k => $v) {
                        // print_r($final_arr);
                        if (!in_array($userlist[$key]['USERNAME'], $exist_username_arr)) {

                            $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                            $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                            $_arr['USER_ID'] = $profile[$k]['STUDENT_ID'];
                            $_arr['FIRST_NAME'] = $userlist[$key]['LAST_NAME'] . ' ' . $userlist[$key]['FIRST_NAME'];
                            $_arr['PROFILE_ID'] = $profile[$k]['PROFILE'];
                            $_arr['IS_DISABLE'] = $userlist[$key]['IS_DISABLE'];
                            array_push($final_arr, $_arr);
                            $exist_username_arr[] = $userlist[$key]['USERNAME'];
                        }
                    }
                } else if ($userlist[$key]['PROFILE_ID'] == 4) {
                    if (User('PROFILE') == 'student')
                        $select = "SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=" . $userlist[$key]['PROFILE_ID'] . " and se.school_id=" . UserSchool() . " AND se.student_id=" . UserStudentID() . "";
                    if (User('PROFILE') == 'teacher')
                        $select = "SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=" . $userlist[$key]['PROFILE_ID'] . " and se.school_id=" . UserSchool() . " AND se.student_id in (select schedule.student_id from  schedule,course_periods,students_join_people where course_periods.course_period_id=schedule.course_period_id  and  schedule.student_id=students_join_people.student_id and students_join_people.person_id=" . $userlist[$key]['USER_ID'] . " and teacher_id=" . UserID() . ")";
                    else
                        $select = "SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=" . $userlist[$key]['PROFILE_ID'] . " and se.school_id=" . UserSchool() . " AND se.student_id in (select student_id from  students_join_people where person_id=" . $userlist[$key]['USER_ID'] . ") ";
                    $profile = DBGet(DBQuery($select));


                    foreach ($profile as $k => $v) {
                        if (!in_array($userlist[$key]['USERNAME'], $exist_username_arr)) {

                            $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                            $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                            $_arr['USER_ID'] = $userlist[$key]['USER_ID'];
                            $_arr['FIRST_NAME'] = $userlist[$key]['LAST_NAME'] . ' ' . $userlist[$key]['FIRST_NAME'];
                            $_arr['PROFILE_ID'] = $profile[$k]['PROFILE'];
                            $_arr['IS_DISABLE'] = $userlist[$key]['IS_DISABLE'];
                            array_push($final_arr, $_arr);
                            $exist_username_arr[] = $userlist[$key]['USERNAME'];
                        }
                    }
                } else {
                    $select = "SELECT se.*,up.* FROM staff_school_relationship se,user_profiles up WHERE up.ID=" . $userlist[$key]['PROFILE_ID'] . " and se.school_id=" . UserSchool() . " AND se.staff_id='" . $userlist[$key]['USER_ID'] . "' group  by staff_id";
                    $profile = DBGet(DBQuery($select));
                    foreach ($profile as $k => $v) {
                        if (!in_array($userlist[$key]['USERNAME'], $exist_username_arr)) {

                            $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                            $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                            $_arr['USER_ID'] = $profile[$k]['STAFF_ID'];
                            $_arr['FIRST_NAME'] = $userlist[$key]['LAST_NAME'] . ' ' . $userlist[$key]['FIRST_NAME'];
                            $_arr['PROFILE_ID'] = $profile[$k]['PROFILE'];
                            $_arr['IS_DISABLE'] = $userlist[$key]['IS_DISABLE'];
                            array_push($final_arr, $_arr);
                            $exist_username_arr[] = $userlist[$key]['USERNAME'];
                        }
                    }
                }
            }

//            array_unshift($final_arr, "");
//            unset($final_arr[0]);
//            echo'<br><br>final array-----------------------------<br><br>';
//            print_r($final_arr);
            $userlist = $final_arr;
        } else {

            foreach ($userlist as $key => $value) {
                $select = "SELECT * FROM user_profiles WHERE ID='" . $userlist[$key]['PROFILE_ID'] . "'";
                $profile = DBGet(DBQuery($select));
                $userlist[$key]['FIRST_NAME'] = $userlist[$key]['LAST_NAME'] . ' ' . $userlist[$key]['FIRST_NAME'];
                $userlist[$key]['PROFILE_ID'] = $profile[1]['PROFILE'];
            }
        }

        if ($_REQUEST['_dis_user'] == 'Y')
            $columns = array('FIRST_NAME' => _member, 'USERNAME' => _userName, 'PROFILE_ID' => _profile, 'STATUS' => _status);
        else
            $columns = array('FIRST_NAME' => _member, 'USERNAME' => _userName, 'PROFILE_ID' => _profile);
        $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
        $extra['LO_group'] = array('STAFF_ID');
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'groups\');"><A>');
        $extra['new'] = true;
        if (is_array($extra['columns_before'])) {
            $LO_columns = $extra['columns_before'] + $columns;
            $columns = $LO_columns;
        }
        foreach ($userlist as $id => $value) {
            $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=groups[" . $value['USER_ID'] . "," . $value['PROFILE_ID'] . "] value=Y>";
            $userlist[$id] = $extra['columns_before'] + $value;
        }

        if ($_REQUEST['_dis_user'] == 'Y') {
            foreach ($userlist as $ui => $ud) {

                if ($ud['PROFILE_ID'] == 'student')
                    $chck_status = DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM students s,student_enrollment se WHERE se.STUDENT_ID=s.STUDENT_ID AND s.STUDENT_ID=' . $ud['USER_ID'] . ' AND se.SYEAR=' . UserSyear() . ' AND (s.IS_DISABLE=\'Y\' OR (se.END_DATE<\'' . date('Y-m-d') . '\'  AND se.END_DATE IS NOT NULL AND se.END_DATE<>\'0000-00-00\' ))'));
                elseif ($ud['PROFILE_ID'] == 'parent')
                    $chck_status = DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM people WHERE STAFF_ID=' . $ud['USER_ID'] . ' AND IS_DISABLE=\'Y\' '));
                else
                    $chck_status = DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM staff s,staff_school_relationship se WHERE se.STAFF_ID=s.STAFF_ID AND s.STAFF_ID=' . $ud['USER_ID'] . ' AND se.SYEAR=' . UserSyear() . ' AND (s.IS_DISABLE=\'Y\' OR (se.END_DATE<\'' . date('Y-m-d') . '\'  AND se.END_DATE IS NOT NULL AND se.END_DATE<>\'0000-00-00\' ))'));


                if ($chck_status[1]['DISABLED'] != 0)
                    $userlist[$ui]['STATUS'] = "<font style='color:red'>".ucfirst(_inactive)."</font>";
                else
                    $userlist[$ui]['STATUS'] = "<font style='color:green'>".ucfirst(_active)."</font>";

                $userlist[$ui]['PROFILE_ID'] = ucfirst($ud['PROFILE_ID']);
            }
        }

        $newUserList = array();
        foreach($userlist as $id => $user){
            $newUserList[$id+1] = $user;
        }

        ListOutputExcel($newUserList, $columns, _member, _members, '', array(), array('search' => false), '');
    }

    echo '<br/>';
    echo "<INPUT TYPE=SUBMIT name=button id=button class='btn btn-primary' VALUE='"._addMembers."' onclick='return mail_group_chk(this);'/>";
    echo "</FORM>";
    PopTable('footer');
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'add_group') {
    if (!isset($_REQUEST['search'])) {
        echo "<FORM name=Group class=\"form-horizontal\" id=Compose action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=group_insert method=POST >";
        PopTable('header', _addGroup);
        echo '<div class="row">';
        echo '<div class="col-md-4">';

        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._groupName.': </label>';
        echo '<div class="col-md-8">';
        echo TextInput_mail('', 'txtGrpName', '', 'onkeyup=groups(this.value) class=cell_medium');
        echo '</div>'; //.col-md-8
        echo '</div>'; //.form-group

        echo '</div>'; //.col-md-4
        echo '<div class="col-md-8">';

        echo '<div class="form-group"><label class="col-md-2 control-label text-right">'._description.': </label>';
        echo '<div class="col-md-10">';
        echo TextInput_mail('', 'txtGrpDesc', '', 'onkeyup=desc(this.value) class=cell_medium');
        echo '</div>'; //.col-md-8
        echo '</div>'; //.form-group

        echo '</div>'; //.col-md-4
        echo '</div>'; //.row

        echo '</div>'; //.tab-content
        echo '</div>'; //.panel-body

        echo '<div class="panel-footer">';
        echo '<INPUT TYPE=SUBMIT name=button id=button class="btn btn-primary pull-right m-r-20" VALUE="'._addGroup.'" onclick="return mail_group_chk(this);"/>';

        echo "</FORM>";

        if ($_SESSION['staff_id']) {
            unset($_SESSION['staff_id']);
            //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
        }
    }
    if (isset($_REQUEST['search']) && $_REQUEST['search'] == 'true' && $_REQUEST['modfunc'] == 'add_group') {
        echo "hello";
        echo "<FORM name=Group class=\"form-horizontal\" id=Compose action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=group_insert method=POST >";
        PopTable('header', _group);

        echo '<div class="row">';
        echo '<div class="col-md-4">';

        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._groupName.': </label>';
        echo '<div class="col-md-8">';
        echo TextInput_mail($_REQUEST['groupname'], 'txtGrpName', '', 'class=form-control');
        echo '</div>'; //.col-md-8
        echo '</div>'; //.form-group

        echo '</div>'; //.col-md-4
        echo '<div class="col-md-4">';

        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._description.': </label>';
        echo '<div class="col-md-8">';
        echo TextInput_mail($_REQUEST['groupdescription'], 'txtGrpDesc', '', 'class=cell_medium');
        echo '</div>'; //.col-md-8
        echo '</div>'; //.form-group

        echo '</div>'; //.col-md-4
        echo '</div>'; //.row

        echo '<hr/>';

        echo '<INPUT TYPE=SUBMIT name=button id=button class="btn btn-primary" VALUE="'._addGroup.'" onclick="return mail_group_chk(this);" />';

        $lastName = $_REQUEST['last'];
        $firstName = $_REQUEST['first'];
        $userName = $_REQUEST['username'];
        $profile = $_REQUEST['profile'];
        $disable = $_REQUEST['_dis_user'];
        $allschools = $_REQUEST['_search_all_schools'];

        $userlist = DBGet(DBQueryMd($user));

        foreach ($userlist as $key => $value) {
            $select = "SELECT * FROM user_profiles WHERE ID='" . $userlist[$key]['PROFILE_ID'] . "'";
            $profile = DBGet(DBQuery($select));
            $userlist[$key]['FIRST_NAME'] = $userlist[$key]['LAST_NAME'] . ' ' . $userlist[$key]['FIRST_NAME'];
            $userlist[$key]['PROFILE_ID'] = $profile[1]['PROFILE'];
        }
        $columns = array('FIRST_NAME' => _member, 'USERNAME' => _userName, 'PROFILE_ID' => _profile);
        $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
        $extra['LO_group'] = array('STAFF_ID');
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'groups\');"><A>');
        $extra['new'] = true;
        if (is_array($extra['columns_before'])) {
            $LO_columns = $extra['columns_before'] + $columns;
            $columns = $LO_columns;
        }
        foreach ($userlist as $id => $value) {

            $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=groups[" . $value['USER_ID'] . "," . $value['PROFILE_ID'] . "] value=Y>";
            $userlist[$id] = $extra['columns_before'] + $value;
        }
        ListOutput($userlist, $columns, _member, _members, '', array(), array('search' => false), '');

        echo '</FORM>';
    }

    PopTable('footer');
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'member_insert') {

    if ($_REQUEST['groups']) {

        $grp = array_keys($_REQUEST['groups']);
        $select = "select * from mail_group where group_id='" . $_REQUEST['grp_id'] . "'";

        $grp_select = DBGet(DBQuery($select));
        $grp_select[1]['GROUP_ID'];
        $grp_select['group_name'] = $grp_select[1]['GROUP_NAME'];
        $grp_select['description'] = $grp_select[1]['DESCRIPTION'];
        $mem_ins_msg = '';
        foreach ($grp as $i => $j) {
            $idProfile = explode(",", $j);
            $member_select = DBGet(DBQuery("Select * from login_authentication,user_profiles where login_authentication.profile_id=user_profiles.id and user_profiles.profile='" . $idProfile[1] . "' and login_authentication.user_id='$idProfile[0]'  "));

            // $grp_members = 'INSERT INTO mail_groupmembers(GROUP_ID,USER_NAME,profile) VALUES(\'' . $grp_select[1]['GROUP_ID'] . '\',\'' . $member_select[1]['USERNAME'] . '\',\'' . $member_select[1]['PROFILE_ID'] . '\')';

            $grp_members = 'INSERT INTO mail_groupmembers(GROUP_ID,USER_NAME,profile,SCHOOL_ID) VALUES(\'' . $grp_select[1]['GROUP_ID'] . '\',\'' . $member_select[1]['USERNAME'] . '\',\'' . $member_select[1]['PROFILE_ID'] . '\',\'' . UserSchool(). '\')';
            
            $members = DBQuery($grp_members);
            $mem_ins_msg = 'ins';
        }
        unset($_REQUEST['modfunc']);
        echo "<script>load_link_group('Modules.php?modname=messaging/Group.php','1')</script>";
    } else {
        PopTable('header', _alertMessage);
        echo "<CENTER><h4>"._pleaseSelectAtleastOneMemberToAdd."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value=".ok." onclick='window.location=\"Modules.php?modname=messaging/Group.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    }

    unset($_REQUEST['modfunc']);
    echo "<script>load_link_group('Modules.php?modname=messaging/Group.php','1')</script>";
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'group_insert') {

    $exist_group = DBGet(DBQuery("SELECT * FROM mail_group WHERE USER_NAME='$userName'"));
    foreach ($exist_group as $id => $value) {
        if (strtolower($exist_group[$id]['GROUP_NAME']) == strtolower($_REQUEST['txtGrpName'])) {
            PopTable('header', _alertMessage);
            echo "<CENTER><h4>"._groupnameAlreadyExistFor." $userName</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value=".ok." onclick='window.location=\"Modules.php?modname=messaging/Group.php\"'></FORM></CENTER>";
            PopTable('footer');
            return false;
        }
    }
    $description = $_REQUEST['txtGrpDesc'];
    if ($description == "")
        $description = 'N';

    if ($_REQUEST['txtGrpName']) {
        // $group = 'INSERT INTO mail_group(GROUP_NAME,DESCRIPTION,USER_NAME,CREATION_DATE) VALUES(\'' . str_replace("'", "''", str_replace("\'", "'", $_REQUEST['txtGrpName'])) . '\',\'' . str_replace("'", "''", str_replace("\\'", "'", $description)) . '\',\'' . $userName . '\',now())';

        $group = 'INSERT INTO mail_group(GROUP_NAME,DESCRIPTION,USER_NAME,SCHOOL_ID,CREATION_DATE) VALUES(\'' . str_replace("'", "''", str_replace("\'", "'", $_REQUEST['txtGrpName'])) . '\',\'' . str_replace("'", "''", str_replace("\\'", "'", $description)) . '\',\'' . $userName . '\',\'' . UserSchool(). '\',now())';

        $group_info = DBQuery($group);

        if ($_REQUEST['groups']) {
            $grp = array_keys($_REQUEST['groups']);
            $select = "select group_id from mail_group where group_name='" . str_replace("'", "''", str_replace("\'", "'", $_REQUEST['txtGrpName'])) . "'";
            $grp_select = DBGet(DBQuery($select));
            $grp_select[1]['GROUP_ID'];
            foreach ($grp as $i => $j) {
                $idProfile = explode(",", $j);

                $member_select = DBGet(DBQuery("Select * from login_authentication,user_profiles where login_authentication.profile_id=user_profiles.id and user_profiles.profile='" . $idProfile[1] . "' and login_authentication.user_id='$idProfile[0]'  "));

                // $grp_members = 'INSERT INTO mail_groupmembers(GROUP_ID,USER_NAME,profile) VALUES(\'' . $grp_select[1]['GROUP_ID'] . '\',\'' . $member_select[1]['USERNAME'] . '\',\'' . $member_select[1]['PROFILE_ID'] . '\')';
                
                $group = 'INSERT INTO mail_group(GROUP_NAME,DESCRIPTION,USER_NAME,SCHOOL_ID,CREATION_DATE) VALUES(\'' . str_replace("'", "''", str_replace("\'", "'", $_REQUEST['txtGrpName'])) . '\',\'' . str_replace("'", "''", str_replace("\\'", "'", $description)) . '\',\'' . $userName . '\',\'' . UserSchool(). '\',now())';
                
                $members = DBGet(DBQuery($grp_members));
            }
        }

        unset($_REQUEST['modfunc']);
        echo "<script>load_link('Modules.php?modname=messaging/Group.php')</script>";
    } else {
        unset($_REQUEST['modfunc']);
        echo "<script>load_link('Modules.php?modname=messaging/Group.php&modfunc=add_group')</script>";
    }
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'members' && $_REQUEST['groupid']) {


    if (isset($_REQUEST['groupname'])) {
        $gid = $_REQUEST['groupid'];
        $exist_group = DBGet(DBQuery("SELECT * FROM mail_group WHERE USER_NAME='$userName' and group_id!='$gid'"));
        foreach ($exist_group as $id => $value) {
            if ($exist_group[$id]['GROUP_NAME'] == $_REQUEST['groupname']) {
                PopTable('header', _alertMessage);
                echo "<CENTER><h4>"._groupnameAlreadyExistFor." $userName</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value=".ok." onclick='window.location=\"Modules.php?modname=messaging/Group.php\"'></FORM></CENTER>";
                PopTable('footer');
                exit;
            }
        }
        $update = "UPDATE mail_group SET GROUP_NAME='" . str_replace("'", "\\'", $_REQUEST['groupname']) . "' WHERE GROUP_ID=$_REQUEST[groupid]";

        $update_group = DBQuery($update);
    }
    if (isset($_REQUEST['groupdesc']) && $_REQUEST['groupdesc'] != '') {
        if (trim($_REQUEST['groupdesc']) != "")
            $update = "UPDATE mail_group SET DESCRIPTION='" . str_replace("'", "\\'", $_REQUEST['groupdesc']) . "' WHERE GROUP_ID=$_REQUEST[groupid]";
        else
            $update = "UPDATE mail_group SET DESCRIPTION='N' WHERE GROUP_ID=$_REQUEST[groupid]";
        $update_group = DBQuery($update);
    }


    if (isset($_REQUEST['group'])) {
        if (implode(',', $_REQUEST['group']) == '') {
            $select = "select * from mail_groupmembers where group_id=" . $_REQUEST['groupid'];
            $list = DBGet(DBQuery($select));
            foreach ($list as $m => $n) {
                if ($list[$m]['ID'])
                    $del_id[] = $list[$m]['ID'];
            }

            $id = implode(',', $del_id);
            $mem_del = '';
            $select = "DELETE FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID IN($id)";
            $not_in_group = DBQuery($select);
            $mem_del = 'del';
            unset($_REQUEST['modfunc']);
            echo "<script>load_link_group('Modules.php?modname=messaging/Group.php','2')</script>";
        }
        else {
            $mem_del = '';
            $not_select = "select * from mail_groupmembers where GROUP_ID=$_REQUEST[groupid]";
            $list1 = DBGet(DBQuery($not_select));
            foreach ($list1 as $i => $j) {
                $id_list[] = $j['ID'];
            }
            $id3 = implode(',', $id_list);
            $id1 = array_keys($_REQUEST['group']);
            $id2 = implode(',', $id1);
            if ($id2 == $id3)
                echo "<script>load_link('Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "')</script>";
            else {
                $select = "SELECT * FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID NOT IN($id2)";
                $list = DBGet(DBQuery($select));
                foreach ($list as $i => $j) {
                    $del_id1[] = $list[$i]['ID'];
                }
                $id = implode(',', $del_id1);
                $select = "DELETE FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID IN($id)";
                $not_in_group = DBQuery($select);
                $mem_del = 'del';
                unset($_REQUEST['modfunc']);

                echo "<script>load_link_group('Modules.php?modname=messaging/Group.php','2')</script>";
            }
        }
    } else {

    $no_of_member = DBGet(DBQuery('SELECT * FROM mail_groupmembers WHERE GROUP_ID=' . $_REQUEST['groupid']));
        if (count($no_of_member) == 0)
            echo "<script>load_link('Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "')</script>";
        else {
            $mem_del = '';
            $delect_member = "delete from mail_groupmembers where GROUP_ID=$_REQUEST[groupid]";
            $delect_member_qry = DBQuery($delect_member);
            $mem_del = 'del';
            unset($_REQUEST['modfunc']);

            echo "<script>load_link_group('Modules.php?modname=messaging/Group.php','2')</script>";
        }
    }

    unset($_REQUEST['modfunc']);
}
?>
