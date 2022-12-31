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
session_start();
!empty($_SESSION['USERNAME']) or die('Access denied!');
include('../../RedirectModulesInc.php');

DrawBC("" . _messaging . " > " . ProgramTitle());

//echo'<div class="alert bg-danger alert-styled-left">Message body cannot be empty</div>';
if (isset($_REQUEST['del']) && $_REQUEST['del'] == 'true') {
    echo '<div class="alert bg-success alert-styled-left">' . _messageDeletedSucessfully . '</div>';
}
if ($_REQUEST['failed_user'] == 'Y')
    echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSentAsNoUsersWereFound . '.</div>';
if ($_REQUEST['button'] == 'Send') {
    if (User('PROFILE') == 'teacher' && $_REQUEST['cp_id'] != '') {
        if ($_REQUEST['list_gpa_student'] == 'Y') {
            $sch_stu = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,schedule s WHERE  s.COURSE_PERIOD_ID =' . $_REQUEST['cp_id'] . ' AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3 AND la.USERNAME IS NOT NULL '));
            foreach ($sch_stu as $sch_stua)
                $sch_stu_arr[] = $sch_stua['USERNAME'];
        }

        if ($_REQUEST['list_gpa_parent'] == 'Y') {
            $sch_p = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,students_join_people sjp,schedule s WHERE sjp.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID =' . $_REQUEST['cp_id'] . '  AND la.USER_ID=sjp.PERSON_ID AND la.PROFILE_ID=4 AND la.USERNAME IS NOT NULL '));
            foreach ($sch_p as $sch_pa)
                $sch_p_arr[] = $sch_pa['USERNAME'];
        }

        if (count($sch_stu_arr) > 0 || count($sch_p_arr) > 0) {
            if (count($sch_stu_arr) > 0)
                $_REQUEST['txtToUser'] = implode(',', $sch_stu_arr);
            if (count($sch_stu_arr) > 0 && count($sch_p_arr) > 0)
                $_REQUEST['txtToUser'] = $_REQUEST['txtToUser'] . ',' . implode(',', $sch_p_arr);
            elseif (count($sch_stu_arr) == 0 && count($sch_p_arr) > 0)
                $_REQUEST['txtToUser'] = implode(',', $sch_p_arr);
        } else {
            echo "<script type='text/javascript'>load_link('Modules.php?modname=messaging/Inbox.php&failed_user=Y');</script>";
        }
    }
    if (User('PROFILE') == 'student')
        $user_id = UserStudentID();
    else
        $user_id = UserID();

    $username_user = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $user_id . ' AND PROFILE_ID=' . User('PROFILE_ID')));
    $username_user = $username_user[1]['USERNAME'];

    $to_array = $_REQUEST['txtToUser'];
    $to_cc_array = $_REQUEST['txtToCCUser'];
    $to_bcc_array = $_REQUEST['txtToBCCUser'];

    if ($to_array != '')
        $to_array = explode(',', $to_array);
    if ($to_cc_array != '')
        $to_cc_array = explode(',', $to_cc_array);
    if ($to_bcc_array != '')
        $to_bcc_array = explode(',', $to_bcc_array);
    if (count($to_array) > 0) {
        foreach ($to_array as $ta) {
            $temp_to = array();

            if ($_REQUEST['groups'] == '') {

                $temp_to[] = $ta;
            } else {
                $group_check = DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\'' . $ta . '\' AND mg.USER_NAME=\'' . $username_user . '\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if (count($group_check) > 0) {
                    foreach ($group_check as $gq)
                        $temp_to[] = $gq['USER_NAME'];
                }
            }
            if (count($temp_to) > 0) {
                $replace = implode(',', $temp_to);
                $_REQUEST['txtToUser'] = str_replace($ta, $replace, $_REQUEST['txtToUser']);
            }
        }
    }
    if (count($to_cc_array) > 0) {
        foreach ($to_cc_array as $ta) {
            $temp_cc = array();
            $group_check = DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\'' . $ta . '\' AND mg.USER_NAME=\'' . $username_user . '\' AND mg.GROUP_ID=mgm.GROUP_ID'));
            if (count($group_check) > 0) {
                foreach ($group_check as $gq)
                    $temp_cc[] = $gq['USER_NAME'];
            }
            if (count($temp_cc) > 0) {
                $replace = implode(',', $temp_cc);
                $_REQUEST['txtToCCUser'] = str_replace($ta, $replace, $_REQUEST['txtToCCUser']);
            }
        }
    }
    if (count($to_bcc_array) > 0) {
        foreach ($to_bcc_array as $ta) {
            $temp_bcc = array();
            $group_check = DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\'' . $ta . '\' AND mg.USER_NAME=\'' . $username_user . '\' AND mg.GROUP_ID=mgm.GROUP_ID'));
            if (count($group_check) > 0) {
                foreach ($group_check as $gq)
                    $temp_bcc[] = $gq['USER_NAME'];
            }
            if (count($temp_bcc) > 0) {
                $replace = implode(',', $temp_bcc);
                $_REQUEST['txtToBCCUser'] = str_replace($ta, $replace, $_REQUEST['txtToBCCUser']);
            }
        }
    }
    $to_array = $_REQUEST['txtToUser'];
    $to_cc_array = $_REQUEST['txtToCCUser'];
    $to_bcc_array = $_REQUEST['txtToBCCUser'];
    if ($to_array != '')
        $to_array = explode(',', $to_array);
    if ($to_cc_array != '')
        $to_cc_array = explode(',', $to_cc_array);
    if ($to_bcc_array != '')
        $to_bcc_array = explode(',', $to_bcc_array);

    if (User('PROFILE_ID') != 0 && User('PROFILE') == 'admin') {
        $schools = DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID=' . $user_id . ' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
        $schools = $schools[1]['SCHOOL_ID'];


        $tmp_q = '';
        $tmp_a = array();
        $tmp_arr = array();

        $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se WHERE se.STUDENT_ID=la.USER_ID AND la.PROFILE_ID=3 AND se.SCHOOL_ID IN (' . $schools . ') AND (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND la.USERNAME IS NOT NULL'));
        foreach ($tmp_q as $tmp_a)
            $tmp_arr[] = $tmp_a['USERNAME'];


        $tmp_q = '';
        $tmp_a = array();
        $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME  FROM login_authentication la,staff_school_relationship ssr,user_profiles up WHERE ssr.SCHOOL_ID IN (' . $schools . ') AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\'' . date('Y-m-d') . '\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\'' . date('Y-m-d') . '\') AND ssr.STAFF_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=up.ID AND up.PROFILE NOT IN (\'student\',\'parent\')'));
        foreach ($tmp_q as $tmp_a)
            $tmp_arr[] = $tmp_a['USERNAME'];


        $tmp_q = '';
        $tmp_a = array();
        $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME  FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE se.SCHOOL_ID IN (' . $schools . ') AND (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=4'));
        foreach ($tmp_q as $tmp_a)
            $tmp_arr[] = $tmp_a['USERNAME'];
    } elseif (User('PROFILE') == 'parent' || User('PROFILE') == 'student') {
        $course_periods = DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID=' . UserStudentID()));
        $course_periods = $course_periods[1]['COURSE_PERIOD_ID'];


        $tmp_q = '';
        $tmp_a = array();
        $tmp_q = array();


        if (User('PROFILE') == 'parent') {
            $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=' . $user_id . ' AND sjp.STUDENT_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=3 '));
            foreach ($tmp_q as $tmp_a)
                $tmp_arr[] = $tmp_a['USERNAME'];

            $tmp_q1 = DBGet(DBQuery('SELECT DISTINCT se.STUDENT_ID FROM student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=' . $user_id));
            foreach ($tmp_q1 as $tmp_a) {
                $stu_arr[] = $tmp_a['STUDENT_ID'];
            }
            $student_id = implode(',', $stu_arr);

            $asso_people = DBGet(DBQuery('select la.USERNAME from students_join_people sjp ,login_authentication la where sjp.person_id=la.user_id and la.profile_id=4 and sjp.student_id in(' . $student_id . ')'));

            foreach ($asso_people as $asso_v) {
                $tmp_arr[] = $asso_v['USERNAME'];
            }
        }
        if (User('PROFILE') == 'student') {
            $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.STUDENT_ID=' . $user_id . ' AND sjp.PERSON_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=4 '));
            foreach ($tmp_q as $tmp_a)
                $tmp_arr[] = $tmp_a['USERNAME'];
        }


        if ($course_periods != '') {
            $tmp_q = '';
            $tmp_a = array();
            $tmp_q = DBGet(DBQuery('SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID IN (' . $course_periods . ') '));
            foreach ($tmp_q as $tmp_a) {
                $get_la = DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,user_profiles up WHERE la.USER_ID=' . $tmp_a['TEACHER_ID'] . ' AND la.PROFILE_ID=up.ID AND up.PROFILE=\'teacher\' AND la.USERNAME IS NOT NULL'));
                $tmp_arr[] = $get_la[1]['USERNAME'];
                if ($tmp_a['SECONDARY_TEACHER_ID'] != '') {
                    $get_la = DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,user_profiles up WHERE la.USER_ID=' . $tmp_a['SECONDARY_TEACHER_ID'] . ' AND la.PROFILE_ID=up.ID AND up.PROFILE=\'teacher\' AND la.USERNAME IS NOT NULL'));
                    $tmp_arr[] = $get_la[1]['USERNAME'];
                }
            }
        }

        $tmp_q = '';
        $tmp_a = array();

        $tmp_q = DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,staff s,staff_school_relationship ssr,user_profiles up WHERE s.PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\'' . date('Y-m-d') . '\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\'' . date('Y-m-d') . '\') AND ssr.SCHOOL_ID=' . UserSchool() . '  AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=up.ID AND up.PROFILE=s.PROFILE AND la.USERNAME IS NOT NULL '));
        foreach ($tmp_q as $tmp_a)
            $tmp_arr[] = $tmp_a['USERNAME'];
    } elseif (User('PROFILE') == 'teacher') {
        $schools = DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID=' . $user_id . ' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
        $schools = $schools[1]['SCHOOL_ID'];

        $course_periods = DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM course_periods WHERE TEACHER_ID=' . $user_id . ' OR SECONDARY_TEACHER_ID=' . $user_id));
        $course_periods = $course_periods[1]['COURSE_PERIOD_ID'];


        $tmp_q = '';
        $tmp_a = array();
        $tmp_arr = array();
        if ($course_periods != '') {
            $tmp_q = DBGet(DBQuery('SELECT DISTINCT la.USERNAME,se.STUDENT_ID FROM login_authentication la,student_enrollment se,schedule s WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID IN (' . $course_periods . ') AND la.USER_ID=se.STUDENT_ID AND la.PROFILE_ID=3 AND la.USERNAME IS NOT NULL '));
            foreach ($tmp_q as $tmp_a) {
                $tmp_arr[] = $tmp_a['USERNAME'];
                $tmp_qa = DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,students_join_people sjp WHERE sjp.STUDENT_ID=' . $tmp_a['STUDENT_ID'] . ' AND la.USER_ID=sjp.PERSON_ID AND la.PROFILE_ID=4 AND la.USERNAME IS NOT NULL '));
                foreach ($tmp_qa as $tmp_qaa) {
                    $tmp_arr[] = $tmp_qaa['USERNAME'];
                }
            }
        }

        $tmp_q = '';
        $tmp_a = array();
        $tmp_q = DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,staff s,staff_school_relationship ssr,user_profiles up WHERE  ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\'' . date('Y-m-d') . '\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\'' . date('Y-m-d') . '\') AND ssr.SCHOOL_ID IN (' . $schools . ')  AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=up.ID AND up.PROFILE=s.PROFILE AND la.USERNAME IS NOT NULL '));
        foreach ($tmp_q as $tmp_a)
            $tmp_arr[] = $tmp_a['USERNAME'];
    }
    if (User('PROFILE_ID') != 0) {
        foreach ($to_array as $data) {

            if (in_array($data, $tmp_arr))
                $final_arr[] = $data;
            else
                $cannot_send[] = $data;
        }
        foreach ($to_cc_array as $data) {
            if (in_array($data, $tmp_arr))
                $final_cc_arr[] = $data;
            else
                $cannot_send[] = $data;
        }
        foreach ($to_bcc_array as $data) {
            if (in_array($data, $tmp_arr))
                $final_bcc_arr[] = $data;
            else
                $cannot_send[] = $data;
        }
        $_REQUEST['txtToUser'] = implode(',', $final_arr);
        $_REQUEST['txtToCCUser'] = implode(',', $final_cc_arr);
        $_REQUEST['txtToBCCUser'] = implode(',', $final_bcc_arr);
        if (count($cannot_send) > 0)
            echo '<div class="alert bg-danger alert-styled-left">Message not sent to ' . implode(',', $cannot_send) . '</div>';
    }
}
$userName = User('USERNAME');
$toProfile = '';
$toArray = array();
$toArray = explode(',', $_REQUEST["txtToUser"]);
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'trash') {
    if (is_countable($_REQUEST['mail']) && count($_REQUEST['mail']) != 0) {
        $count = count($_REQUEST['mail']);
        if ($count != 1)
            $row = "messages";
        else
            $row = "message";
        if (DeleteMail($count . ' ' . $row, 'delete', $_REQUEST['modname'])) {
            $id = array_keys($_REQUEST['mail']);
            $mail_id = implode(',', $id);
            unset($_REQUEST['modfunc']);



            $to_arr = array();
            $arr = array();
            $qr = "select to_user,istrash,to_cc,to_bcc from msg_inbox where mail_id IN($mail_id)";
            $fetch = DBGet(DBQuery($qr));
            foreach ($fetch as $key => $value) {
                $s = $value['TO_USER'];
                //"<br>";
                $to_cc = $value['TO_CC'];
                $to_cc_arr = explode(',', $to_cc);
                $arr = explode(',', $s);
                $to_bcc = $value['TO_BCC'];
                $to_bcc_arr = explode(',', $to_bcc);

                $update_to_user = "";
                if (($key = array_search($userName, $arr)) !== false) {
                    unset($arr[$key]);
                    $update_to_user = implode(',', $arr);
                    if ($value['ISTRASH'] != '') {
                        $to_arr = explode(',', $value['ISTRASH']);

                        array_push($to_arr, $userName);

                        $trash_user = implode(',', $to_arr);
                    } else {
                        $trash_user = $userName;
                    }
                    $query = 'update msg_inbox set to_user="' . $update_to_user . '",istrash="' . $trash_user . '" where mail_id IN ("' . $mail_id . '")';

                    $fetch_ex = DBQuery($query);
                }
                if (($key = array_search($userName, $to_cc_arr)) !== false) {
                    unset($to_cc_arr[$key]);
                    $update_to_user = implode(',', $to_cc_arr);
                    if ($value['ISTRASH'] != '') {
                        $to_arr = explode(',', $value['ISTRASH']);

                        array_push($to_arr, $userName);

                        $trash_user = implode(',', $to_arr);
                    } else {
                        $trash_user = $userName;
                    }


                    $query = "update msg_inbox set to_cc='$update_to_user',istrash='$trash_user' where mail_id IN ($mail_id)";

                    $fetch_ex = DBQuery($query);
                }
                if (($key = array_search($userName, $to_bcc_arr)) !== false) {
                    unset($to_bcc_arr[$key]);
                    $update_to_user = implode(',', $to_bcc_arr);
                    if ($value['ISTRASH'] != '') {
                        $to_arr = explode(',', $value['ISTRASH']);

                        array_push($to_arr, $userName);

                        $trash_user = implode(',', $to_arr);
                    } else {
                        $trash_user = $userName;
                    }
                    $query = "update msg_inbox set to_bcc='$update_to_user',istrash='$trash_user' where mail_id IN ($mail_id)";

                    $fetch_ex = DBQuery($query);
                }
            }
            echo "<script>load_link('Modules.php?modname=messaging/Inbox.php&del=true')</script>";
        }
    } else {
        echo '<BR>';
        PopTable('header', _alertMessage);
        echo "<h4 class=\"text-danger\">" . _pleaseSelectAtleastOneMessageToDelete . "</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value=" . _ok . " onclick='window.location=\"Modules.php?modname=messaging/Inbox.php\"'></FORM>";
        PopTable('footer');
        return false;
    }
}

if (count($toArray) > 1) {
    $txtToUser = sqlSecurityFilter($_REQUEST["txtToUser"]);
    CheckAuthenticMail($userName, $txtToUser, $_REQUEST["txtToCCUser"], $_REQUEST["txtToBCCUser"]);
} else {
    if (count($toArray) == 1) {
        if ($_SESSION['course_period_id'] != '') {
            if (User('PROFILE') == 'teacher') {
                $chkParent = $_POST['list_gpa_parent'];
                $chkStudent = $_POST['list_gpa_student'];
                $course_period_id = $_SESSION['course_period_id'];
                if ($chkStudent == 'Y')
                    $stuList_forCourseArr = DBGet(DBQuery("SELECT la.username,student_id from students s ,login_authentication la where student_id in(Select distinct student_id from course_periods INNER JOIN schedule using(course_period_id) where course_periods.course_period_id=" . $course_period_id . ") AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3 AND username IS NOT NULL"));

                if ($chkParent == 'Y') {
                    $parentList_forCourseArr = DBGet(DBQuery("SELECT username FROM login_authentication WHERE username IS NOT NULL AND PROFILE_ID=4 AND USER_ID IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (Select student_id from students where student_id in(Select distinct student_id from course_periods INNER JOIN schedule using(course_period_id) where course_periods.course_period_id=" . $course_period_id . ")))"));
                }
                $stuList_forCourse = '';
                foreach ($stuList_forCourseArr as $stu) {
                    $stuList_forCourse .= $stu["USERNAME"] . ",";
                }
                $parentList_forCourse = '';
                foreach ($parentList_forCourseArr as $parent) {
                    $parentList_forCourse .= $parent["USERNAME"] . ",";
                }
                if ($chkStudent == 'Y' && $chkParent == 'Y') {
                    $finalList = $stuList_forCourse . "," . $parentList_forCourse;
                }
                if ($chkStudent == 'Y' && $chkParent != 'Y') {
                    $finalList = $stuList_forCourse;
                }
                if ($chkStudent != 'Y' && $chkParent == 'Y') {
                    $finalList = $parentList_forCourse;
                }
                $finalList = rtrim($finalList, ",");
                if ($finalList != "")
                    CheckAuthenticMail($userName, $finalList, $_REQUEST["txtToCCUser"], $_REQUEST["txtToBCCUser"]);
            }
        } else {
            $to = sqlSecurityFilter(str_replace("'", "\'", trim($_REQUEST["txtToUser"])));
            $q = "SELECT mail_group.*, GROUP_CONCAT(gm.user_name) AS members FROM mail_group INNER JOIN mail_groupmembers gm ON(mail_group.group_id = gm.group_id) where mail_group.user_name='$userName' AND group_name ='$to' GROUP BY gm.group_id";
            $group_list = DBGet(DBQuery($q));
            if (count($group_list) != 0) {
                foreach ($group_list as $groupId => $groupmembers) {
                    $groupName = $group_list[$groupId]['GROUP_NAME'];
                    if ($groupName == $_REQUEST["txtToUser"]) {
                        $members = $group_list[$groupId]['MEMBERS'];
                        CheckAuthenticMail($userName, $members, $_REQUEST["txtToCCUser"], $_REQUEST["txtToBCCUser"], $groupName);
                    }
                }
            } else {
                if (trim($_REQUEST["txtToUser"]) != "") {
                    $txtToUser = sqlSecurityFilter($_REQUEST["txtToUser"]);
                    CheckAuthenticMail($userName, $txtToUser, $_REQUEST["txtToCCUser"], $_REQUEST["txtToBCCUser"]);
                }
            }
        }
    }
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'body') {
    //PopTable('header', _messageDetails);
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    $mail_id = $_REQUEST['mail_id'];
    $mail_body = "select mail_body,mail_attachment,mail_Subject,from_user,mail_datetime,to_cc_multiple,to_multiple_users,to_bcc_multiple,mail_read_unread from msg_inbox where mail_id='$mail_id'";

    $mail_body_info = DBGet(DBQuery($mail_body));
    $sub = $mail_body_info[1]['MAIL_SUBJECT'];
    if ($mail_body_info[1]['MAIL_READ_UNREAD'] == "")
        $user_name = $userName;
    else {
        $read_unread_Arr = explode(",", $mail_body_info[1]['MAIL_READ_UNREAD']);
        if (in_array($userName, $read_unread_Arr)) {
            $user_name = $mail_body_info[1]['MAIL_READ_UNREAD'];
        } else {
            $mail_body_info[1]['MAIL_READ_UNREAD'] .= ',' . $userName;
            $user_name = $mail_body_info[1]['MAIL_READ_UNREAD'];
        }
    }
    $mail_read_unread = "update msg_inbox set mail_read_unread='$user_name' where mail_id='$mail_id'";
    $mail_read_unread_ex = DBQuery($mail_read_unread);

    foreach ($mail_body_info as $k => $v) {
        $fromUser = $v['FROM_USER'];
        if ($fromUser != '')
            $login_authentication = DBGet(DBQuery('SELECT * FROM login_authentication WHERE username=\'' . $fromUser . '\' '));
        $profile = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $login_authentication[1]['PROFILE_ID']));
        if ($profile[1]['PROFILE'] != 'parent') {
            if ($profile[1]['PROFILE'] == 'student') {
                $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . $login_authentication[1]['USER_ID'] . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
            } else {
                $staff = DBGet(DBQuery('SELECT * FROM staff WHERE STAFF_ID=' . $login_authentication[1]['USER_ID']));
            }
        }
        echo '<h3 class="no-margin-top"><a href="Modules.php?modname=messaging/Inbox.php" class="btn btn-icon"><i class="icon-square-left"></i></a> &nbsp; &nbsp;' . $v['MAIL_SUBJECT'] . '</h3>';
        echo '<hr class="no-margin-top"/>';

        echo '<div class="media">';
        if ($stu_img_info[1]['CONTENT'] != '')
            echo '<div class="media-left"><img class="img-circle" src="data:image/jpeg;base64,' . base64_encode($stu_img_info[1]['CONTENT']) . '" alt="" /></div>';
        elseif ($staff[1]['IMG_NAME'] != '')
            echo '<div class="media-left"><img class="img-circle" src="data:image/jpeg;base64,' . base64_encode($staff[1]['IMG_CONTENT']) . '" alt="" /></div>';
        else
            echo '<div class="media-left"><img class="img-circle" src="assets/images/placeholder.jpg" alt="" /></div>';
        echo '<div class="media-body">';
        echo '<div class="pull-right"><div class="input-group-btn">';
        echo '<a href="javascript:void(0);" class="btn btn-default btn-xs" disabled="disabled"><i class="icon-calendar3"></i> ' . $v['MAIL_DATETIME'] . '</a>';
        echo '<a href="Modules.php?modname=messaging/Compose.php&modto=' . $fromUser . '&m=reply&sub=' . base64_encode($sub) . '" class="btn btn-primary btn-icon" data-toggle="tooltip" data-original-title="Reply"><i class="icon-undo2"></i></a>';
        echo '</div></div>';
        //echo '<i class="icon-calendar3"></i> '.$v['MAIL_DATETIME'].' <a href="" class="btn btn-default btn-xs btn-icon"><i class="icon-undo2"></i></a>';
        echo '<h6 class="media-heading text-bold">' . GetNameFromUserName($v['FROM_USER']) . '</h6>';
        if ($v['TO_CC_MULTIPLE'] != '' || $v['TO_BCC_MULTIPLE'] != '') {
            echo '<div class="media-annotation dropdown">';
            echo 'To: Me <a href="javascript:void(0)" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-down"></i></a>';
            echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">';
            if ($v['TO_CC_MULTIPLE'] != '') {
                echo "<li><b>CC:</b> " . $v['TO_CC_MULTIPLE'] . "</li>";
            }
            if ($v['TO_BCC_MULTIPLE'] != '') {
                $to_bcc_arr = explode(',', $v['TO_BCC_MULTIPLE']);
                if (in_array($userName, $to_bcc_arr)) {
                    echo "<li><b>BCC:</b> " . $userName . "</li>";
                }
            }
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="media-annotation">To: Me</div>';
        }

        echo '<div class="mt-20">' . str_replace('<a href=', '<a target="_blank" href=', $v['MAIL_BODY']) . '</div>';
        if ($v['MAIL_ATTACHMENT'] != '') {
            echo "
                  " . _attachment . ": ";
            //          $attach=explode(',',$v['MAIL_ATTACHMENT']);
            $attach = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE ID IN (' . substr($v['MAIL_ATTACHMENT'], 0, -1) . ')'));
            foreach ($attach as $user => $img) {
                //              $img_pos=strrpos($img,'/');
                //              $img_name[]=substr($img,$img_pos+1,strlen($img));
                //              $pos=strpos($img,'_');
                //              
                //              $img_src[]=substr($img,$pos+1,strlen($img));
                //              for($i=0;$i<(count($img_src));$i++)
                //              {
                //              $img1=$img_src[$i];
                //              $m=array_keys(str_word_count($img1, 2));
                //              $a=$m[0];
                //              $img3[$i]=substr($img1,$a,strlen($img1));
                //              }
                //              
                //          }
                //         for($i=0;$i<(count($attach));$i++)
                //         {
                echo "<a href='DownloadWindow.php?down_id=" . $img['ID'] . "'>" . $img['NAME'] . "</a>";

                echo '<br>&nbsp;&nbsp;&nbsp;<br>';
            }
            echo "</td></tr>";
        }

        //        if ($v['MAIL_ATTACHMENT'] != '') {
        //            echo '<hr/>';
        //            echo '<h6 class="text-bold"><i class="icon-attachment2"></i> Attachments</h6>';
        //            $attach = explode(',', $v['MAIL_ATTACHMENT']);
        //            foreach ($attach as $user => $img) {
        //                $img_pos = strrpos($img, '/');
        //                $img_name[] = substr($img, $img_pos + 1, strlen($img));
        //                $pos = strpos($img, '_');
        //
        //                $img_src[] = substr($img, $pos + 1, strlen($img));
        //                for ($i = 0; $i < (count($img_src)); $i++) {
        //                    $img1 = $img_src[$i];
        //                    $m = array_keys(str_word_count($img1, 2));
        //                    $a = $m[0];
        //                    $img3[$i] = substr($img1, $a, strlen($img1));
        //                }
        //            }
        //            for ($i = 0; $i < (count($attach)); $i++) {
        //
        //                $img_name[$i] = urlencode($img_name[$i]);
        //                $img4[$i] = urlencode($img3[$i]);
        //                echo (($i>0)?', ':'')."<a href='DownloadWindow.php?filename=$img_name[$i]&name=$img4[$i]' target='new' >" . $img3[$i] . "</a>";
        //            }
        //        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    //PopTable('footer');
}

if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'save') {
    $mod_file = $_REQUEST['name'];
    $_REQUEST['filename'] = str_replace("\\", " ", $_REQUEST['filename']);
    $mod_file = str_replace("\\", " ", $mod_file);

    if (isset($_REQUEST['filename'])) {
        set_time_limit(0);
        $file_path = './assets/' . $_REQUEST['filename'];
        output_file($file_path, '' . $_REQUEST['filename'] . '', 'text/plain', $mod_file);
    }
}


if (!isset($_REQUEST['modfunc'])) {
    $link = array();
    $id = array();
    $arr = array();
    $qr = "select to_user,mail_id,to_cc,to_bcc from msg_inbox";
    $fetch = DBGet(DBQuery($qr));

    foreach ($fetch as $key => $value) {
        $s = $value['TO_USER'];
        "<br>";
        $cc = $value['TO_CC'];
        $bcc = $value['TO_BCC'];

        $arr = explode(',', $s);
        $arr_cc = explode(',', $cc);
        $arr_bcc = explode(',', $bcc);

        if (in_array($userName, $arr) || in_array($userName, $arr_cc) || in_array($userName, $arr_bcc)) {
            array_push($id, $value['MAIL_ID']);
        } else {
        }
    }
    $count = count($id);
    if ($count > 0)
        $to_user_id = implode(',', $id);
    else
        $to_user_id = 'null';

    echo "<FORM class=\"no-margin\" name=sav id=sav action=Modules.php?modname=$_REQUEST[modname]&modfunc=trash method=POST>";
    $inbox = "select * from msg_inbox where mail_id in($to_user_id) order by(mail_id)desc";
    $inbox_info = DBGet(DBQuery($inbox));

    foreach ($inbox_info as $key => $value) {
        if ($value['MAIL_READ_UNREAD'] == '') {
            $inbox_info[$key]['MAIL_SUBJECT'] = '<div style="color:red;"><b>' . $inbox_info[$key]['MAIL_SUBJECT'] . '</b></div>';
        }
        if ($value['MAIL_READ_UNREAD'] != '') {
            $read_user = explode(',', $value['MAIL_READ_UNREAD']);
            if (!in_array($userName, $read_user)) {
                // array_push($key, $value['MAIL_ID']);
                $inbox_info[$key]['MAIL_SUBJECT'] = '<div style="color:red;"><b>' . $inbox_info[$key]['MAIL_SUBJECT'] . '</b></div>';
            }
        }
        if ($value['MAIL_ATTACHMENT'] != '') {
            $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'] . "<img align='right' src='./assets/attachment.png'>";
        }
        $inbox_info[$key]['FROM_USER'] = GetNameFromUserName($value['FROM_USER']);
    }

    echo '<div id="students" class="panel panel-default">';
    $columns = array(
        'FROM_USER' => _from,
        'MAIL_SUBJECT' => _subject,
        'MAIL_DATETIME' => _dateTime,
    );
    $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
    $extra['LO_group'] = array('MAIL_ID');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'mail\');"><A>');
    $extra['new'] = true;
    if (is_array($extra['columns_before'])) {
        $LO_columns = $extra['columns_before'] + $columns;
        $columns = $LO_columns;
    }
    $link['MAIL_SUBJECT']['link'] = "Modules.php?modname=messaging/Inbox.php&modfunc=body";
    $link['MAIL_SUBJECT']['variables'] = array('mail_id' => 'MAIL_ID');
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=trash";
    $options['search'] = 'asdad';

    //PopTable('header', 'Inbox');
    foreach ($inbox_info as $id => $value) {
        $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=mail[" . $value['MAIL_ID'] . "] value=Y>";
        $inbox_info[$id] = $extra['columns_before'] + $value;
    }
    if (count($inbox_info) != 0) {
        $custom_header = '<h6 class="panel-title text-pink">' . _inbox . '</h6><div class="heading-elements"><button type=submit class="btn btn-default heading-btn" onclick=\'formload_ajax("sav");\' ><i class="fa fa-trash-o"></i> ' . _delete . '</button></div>';
    }

    ListOutput($inbox_info, $columns, '', '', $link, array(), array('search' => false), TRUE, $custom_header);

    echo "</div>";
    //PopTable('footer');

    echo '</FORM>';
}

function SendMail($to, $userName, $subject, $mailBody, $attachment, $toCC, $toBCCs, $grpName)
{
    $mailBody = singleQuoteReplace('', '', $mailBody);
    $mailBody = ($mailBody);

    $subject = singleQuoteReplace('', '', $subject);
    $grpName = str_replace("'", "\'", $grpName);
    $attachment = str_replace("'", "\'", $attachment);

    if ($mailBody == "") {
        $_SESSION['BODY_EMPTY'] = '1';
        echo '<script>window.location="Modules.php?modname=messaging/Compose.php"
        </script>';
        return false;
    } else if ($mailBody != "") {
        $inbox_query = DBQuery('INSERT INTO msg_inbox(to_user,from_user,mail_Subject,mail_body,isdraft,mail_attachment,to_multiple_users,to_cc_multiple,to_cc,to_bcc,to_bcc_multiple,mail_datetime) VALUES(\'' . $to . '\',\'' . $userName . '\',\'' . $subject . '\',\'' . $mailBody . '\',\'' . $isdraft . '\',\'' . $attachment . '\',\'' . $to . '\',\'' . $toCC . '\',\'' . $toCC . '\',\'' . $toBCCs . '\',\'' . $toBCCs . '\',now())');
    }
    if ($grpName == 'false')
        $outbox_query = DBQuery('INSERT INTO msg_outbox(to_user,from_user,mail_Subject,mail_body,mail_attachment,to_cc,to_bcc,mail_datetime) VALUES(\'' . $to . '\',\'' . $userName . '\',\'' . $subject . '\',\'' . $mailBody . '\',\'' . $attachment . '\',\'' . $toCC . '\',\'' . $toBCCs . '\',NOW())');
    else {
        $q = 'INSERT INTO msg_outbox(to_user,from_user,mail_Subject,mail_body,mail_attachment,to_cc,to_bcc,mail_datetime,to_grpName) VALUES(\'' . $to . '\',\'' . $userName . '\',\'' . $subject . '\',\'' . $mailBody . '\',\'' . $attachment . '\',\'' . $toCC . '\',\'' . $toBCCs . '\',NOW(),\'' . $grpName . '\')';

        $outbox_query = DBQuery($q);
    }
    echo '<div class="alert alert-success alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>' . _yourMessageHasBeenSent . '.</div>';
}

function array_push_assoc($array, $key, $value)
{
    $array[$key] = $value;
    return $array;
}

function CheckAuthenticMail($userName, $toUsers, $toCCUsers, $toBCCUsers, $grpName = 'false')
{

    if ($toUsers != '')
        $to_array = explode(',', $toUsers);
    if ($toCCUsers != '')
        $to_cc_array = explode(',', $toCCUsers);
    if ($toBCCUsers != '')
        $to_bcc_array = explode(',', $toBCCUsers);

    $toUserstemp = array();
    $toCctemp = array();
    $toBcctemp = array();

    foreach ($to_array as $ta)
        $toUserstemp[] = "'" . $ta . "'";
    foreach ($to_cc_array as $ta)
        $toCctemp[] = "'" . $ta . "'";
    foreach ($to_bcc_array as $ta)
        $toBcctemp[] = "'" . $ta . "'";

    if (count($toUserstemp) > 0)
        $toUserstemp = implode(',', $toUserstemp);
    if (count($toCctemp) > 0)
        $toCctemp = implode(',', $toCctemp);
    if (count($toBcctemp) > 0)
        $toBcctemp = implode(',', $toBcctemp);

    $to_av_user = array();
    $to_uav_user = array();

    $to_av_cc = array();
    $to_uav_cc = array();

    $to_av_bcc = array();
    $to_uav_bcc = array();

    if (is_countable($to_array) && count($to_array) > 0){
        $check_qa = array();

        $check_q = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN (' . $toUserstemp . ')'));
        foreach ($check_q as $cq)
            $check_qa[] = $cq['USERNAME'];

        foreach ($to_array as $to_i => $un) {
            if (in_array($un, $check_qa))
                $to_av_user[] = $un;
            else {
                $group_check = DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\'' . $un . '\' AND mg.USER_NAME=\'' . $userName . '\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if (count($group_check) > 0) {
                    foreach ($group_check as $gq)
                        $to_av_user[] = $gq['USER_NAME'];
                } else
                    $to_uav_user[] = $un;
            }
        }
        unset($un);
        unset($check_q);
    }
    if (is_countable($to_cc_array) && count($to_cc_array) > 0){
        $check_qa = array();

        $check_q = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN (' . $toCctemp . ')'));
        foreach ($check_q as $cq)
            $check_qa[] = $cq['USERNAME'];

        foreach ($to_cc_array as $un) {
            if (in_array($un, $check_qa))
                $to_av_cc[] = $un;
            else
                $to_uav_cc[] = $un;
        }

        unset($un);
        unset($check_q);
    }
    if (is_countable($to_bcc_array) && count($to_bcc_array) > 0){
        $check_qa = array();

        $check_q = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN (' . $toBcctemp . ')'));
        foreach ($check_q as $cq)
            $check_qa[] = $cq['USERNAME'];

        foreach ($to_bcc_array as $un) {
            if (in_array($un, $check_qa))
                $to_av_bcc[] = $un;
            else
                $to_uav_bcc[] = $un;
        }
        unset($un);
        unset($check_q);
    }
    if (is_countable($to_av_user) && count($to_av_user) > 0){
        $subject = $_REQUEST['txtSubj'];

        if ($subject == '')
            $subject = 'No Subject';
        $mailBody = sqlSecurityFilter($_POST['txtBody']);
        $uploaded_file_count = count($_FILES['f']['name']);
        for ($i = 0; $i < $uploaded_file_count; $i++) {

            if ($_FILES['f']['name'][$i] != '') {

                //            $name = $_FILES['f']['name'][$i];
                //            if ($name) {
                //                $path = $userName . '_' . time() . rand(00, 99) . $name;
                //                $folder = "./assets/" . $path;
                //                $temp = $_FILES['f']['tmp_name'][$i];
                //                move_uploaded_file($temp, $folder);
                //                $arr[$i] = $folder;
                //            } else
                //                $attachment = "";
                $fileName = $_FILES['f']['name'][$i];
                $tmpName  = $_FILES['f']['tmp_name'][$i];
                $fileSize = $_FILES['f']['size'][$i];
                $fileType = $_FILES['f']['type'][$i];
                $fp      = fopen($tmpName, 'r');
                $content = fread($fp, filesize($tmpName));
                $content = addslashes($content);
                fclose($fp);

                $fileName = addslashes($fileName);
                if (User('PROFILE') == 'student')
                    DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . UserStudentID() . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'intmsg\')');
                else
                    DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . User('STAFF_ID') . ',' . User('PROFILE_ID') . ',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'intmsg\')');

                $file_id = DBGet(DBQuery('SELECT MAX(ID) AS ID FROM user_file_upload WHERE FILE_INFO =\'intmsg\''));
                if (count($file_id) > 0)
                    $attachment .= $file_id[1]['ID'] . ',';
            }
        }

        //        $attachment = implode(',', $arr);

        $multipleUser = implode(",", $to_av_user);

        if (count($to_av_cc) > 0)
            $multipleCCUser = implode(",", $to_av_cc);
        else
            $multipleCCUser = '';

        if (count($to_av_bcc) > 0)
            $multipleBCCUser = implode(",", $to_av_bcc);
        else
            $multipleBCCUser = '';

        SendMail($multipleUser, $userName, $subject, $mailBody, $attachment, $multipleCCUser, $multipleBCCUser, $grpName);

        if (count($to_uav_user) > 0)
            echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSentTo . ' ' . implode(',', $to_uav_user) . ' ' . _asTheyDonTExist . '.</div><br>';
        if (count($to_uav_cc) > 0)
            echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSentTo . ' ' . implode(',', $to_uav_cc) . ' ' . _asTheyDonTExist . '.</div><br>';
        if (count($to_uav_bcc) > 0)
            echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSentTo . ' ' . implode(',', $to_uav_bcc) . ' ' . _asTheyDonTExist . '.</div><br>';
    } else {
        if (count($to_uav_user) > 0)
            echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSentAs . ' ' . implode(',', $to_uav_user) . ' ' . _doesnTExist . '.</div><br>';
        elseif ($toUsers == '')
            echo '<div class="alert bg-danger alert-styled-left">' . _messageNotSent . '.</div><br>';
    }
}

function output_file($file, $name, $mime_type = '', $mod_file)
{
    if (!is_readable($file))
        die('' . _fileNotFoundOrInaccessible . '!');

    $size = filesize($file);
    $name = rawurldecode($name);
    $known_mime_types = array(
        "pdf" => "application/pdf",
        "txt" => "text/plain",
        "html" => "text/html",
        "htm" => "text/html",
        "exe" => "application/octet-stream",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xls" => "application/vnd.ms-excel",
        "ppt" => "application/vnd.ms-powerpoint", //application/vnd.ms-powerpoint",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation", //application/vnd.ms-powerpoint",
        "gif" => "image/gif",
        "png" => "image/png",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpg",
        "php" => "text/plain"
    );
    if ($mime_type == '') {
        $file_extension = strtolower(substr(strrchr($file, "."), 1));
        if (array_key_exists($file_extension, $known_mime_types)) {
            $mime_type = $known_mime_types[$file_extension];
        } else {
            $mime_type = "application/force-download";
        };
    };

    @ob_end_clean();


    if (ini_get('zlib.output_compression'))
        ini_set('zlib.output_compression', 'Off');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $mod_file . '"');
    header("Content-Transfer-Encoding: binary");
    header('Accept-Ranges: bytes');
    header("Cache-control: private");
    header('Pragma: private');
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    if (isset($_SERVER['HTTP_RANGE'])) {
        list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
        list($range) = explode(",", $range, 2);
        list($range, $range_end) = explode("-", $range);
        $range = intval($range);
        if (!$range_end) {
            $range_end = $size - 1;
        } else {
            $range_end = intval($range_end);
        }
        $new_length = $range_end - $range + 1;
        header("HTTP/1.1 206 Partial Content");
        header("Content-Length: $new_length");
        header("Content-Range: bytes $range-$range_end/$size");
    } else {
        $new_length = $size;
        header("Content-Length: " . $size);
    }
    $chunksize = 1 * (1024 * 1024);
    $bytes_send = 0;
    if ($file = fopen($file, 'r')) {
        if (isset($_SERVER['HTTP_RANGE']))
            fseek($file, $range);

        while (
            !feof($file) &&
            (!connection_aborted()) &&
            ($bytes_send < $new_length)
        ) {
            $buffer = fread($file, $chunksize);
            print($buffer);
            flush();
            $bytes_send += strlen($buffer);
        }
        fclose($file);
    } else
        die('Error - can not open file.');
    die();
}
