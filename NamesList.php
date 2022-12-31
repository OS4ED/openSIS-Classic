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
error_reporting(0);

include("Data.php");
include("Warehouse.php");
$keyword = $_REQUEST['str'];
$block_id = $_REQUEST['block_id'];
if (User('PROFILE') == 'student')
    $user_id = UserStudentID();
else
    $user_id = UserID();
$username_user = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $user_id . ' AND PROFILE_ID=' . User('PROFILE_ID')));
$username_user = $username_user[1]['USERNAME'];
if (User('PROFILE_ID') == 0) {
    $tmp_q = '';
    $tmp_a = array();
    $tmp_stu_arr = array();
    $tmp_stf_arr = array();
    $tmp_p_arr = array();

    $tmp_q = DBGet(DBQuery('SELECT STUDENT_ID FROM students'));
    foreach ($tmp_q as $tmp_a) {
        $tmp_stu_arr[] = $tmp_a['STUDENT_ID'];
    }

    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT STAFF_ID FROM staff'));
    foreach ($tmp_q as $tmp_a) {
        $tmp_stf_arr[] = $tmp_a['STAFF_ID'];
    }
    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT STAFF_ID FROM people'));
    foreach ($tmp_q as $tmp_a) {
        $tmp_p_arr[] = $tmp_a['STAFF_ID'];
    }
} elseif (User('PROFILE_ID') != 0 && User('PROFILE') == 'admin') {
    $schools = DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID=' . $user_id . ' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
    $schools = $schools[1]['SCHOOL_ID'];


    $tmp_q = '';
    $tmp_a = array();
    $tmp_stu_arr = array();
    $tmp_stf_arr = array();
    $tmp_p_arr = array();

    $tmp_q = DBGet(DBQuery('SELECT DISTINCT STUDENT_ID FROM student_enrollment WHERE SCHOOL_ID IN (' . $schools . ') AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
    foreach ($tmp_q as $tmp_a) {
        $tmp_stu_arr[] = $tmp_a['STUDENT_ID'];
    }

    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT DISTINCT STAFF_ID FROM staff_school_relationship WHERE SCHOOL_ID IN (' . $schools . ') AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
    foreach ($tmp_q as $tmp_a) {
        $tmp_stf_arr[] = $tmp_a['STAFF_ID'];
    }

    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT DISTINCT sjp.PERSON_ID FROM student_enrollment se,students_join_people sjp WHERE se.SCHOOL_ID IN (' . $schools . ') AND (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID '));
    foreach ($tmp_q as $tmp_a) {
        $tmp_p_arr[] = $tmp_a['PERSON_ID'];
    }
} elseif (User('PROFILE') == 'parent' || User('PROFILE') == 'student') {
    $course_periods = DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID=' . UserStudentID()));
    $course_periods = $course_periods[1]['COURSE_PERIOD_ID'];
    $tmp_q = '';
    $tmp_a = array();
    $tmp_stu_arr = array();
    $tmp_stf_arr = array();
    $tmp_p_arr = array();

    if (User('PROFILE') == 'parent') {
        $tmp_q = DBGet(DBQuery('SELECT DISTINCT se.STUDENT_ID FROM student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=' . $user_id));
        foreach ($tmp_q as $tmp_a) {
            $stu_arr[] = $tmp_a['STUDENT_ID'];
            $tmp_stu_arr[] = $tmp_a['STUDENT_ID'];
        }
        $student_id = implode(',', $stu_arr);

        $asso_people = DBGet(DBQuery('select person_id from  students_join_people where student_id in(' . $student_id . ')'));

        foreach ($asso_people as $asso_v) {
            $tmp_p_arr[] = $asso_v['PERSON_ID'];
        }
    }
    if (User('PROFILE') == 'student') {
        $tmp_q = DBGet(DBQuery('SELECT DISTINCT sjp.PERSON_ID FROM student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.STUDENT_ID=' . $user_id));
        foreach ($tmp_q as $tmp_a) {
            $tmp_p_arr[] = $tmp_a['PERSON_ID'];
        }
    }

    if ($course_periods != '') {

        $tmp_q = '';
        $tmp_a = array();
        $tmp_q = DBGet(DBQuery('SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID IN (' . $course_periods . ') '));
        foreach ($tmp_q as $tmp_a) {
            $tmp_stf_arr[] = $tmp_a['TEACHER_ID'];
            if ($tmp_a['SECONDARY_TEACHER_ID'] != '')
                $tmp_stf_arr[] = $tmp_a['SECONDARY_TEACHER_ID'];
        }
    }

    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT s.STAFF_ID FROM staff s,staff_school_relationship ssr WHERE PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\'' . date('Y-m-d') . '\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\'' . date('Y-m-d') . '\') AND ssr.SCHOOL_ID=' . UserSchool()));
    foreach ($tmp_q as $tmp_a) {
        $tmp_stf_arr[] = $tmp_a['STAFF_ID'];
    }
} elseif (User('PROFILE') == 'teacher') {
    $schools = DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID=' . $user_id . ' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\'' . date('Y-m-d') . '\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') '));
    $schools = $schools[1]['SCHOOL_ID'];

    $course_periods = DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM course_periods WHERE TEACHER_ID=' . $user_id . ' OR SECONDARY_TEACHER_ID=' . $user_id));
    $course_periods = $course_periods[1]['COURSE_PERIOD_ID'];


    ////////////////   new for associate teacher ////////////



    $course_periods_ass = DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM course_periods WHERE  BEGIN_DATE <=\'' . date('Y-m-d') . '\' and END_DATE>=\'' . date('Y-m-d') . '\' AND  SCHOOL_ID=\'' . $schools . '\'  AND  (TEACHER_ID=' . $user_id . ' OR SECONDARY_TEACHER_ID=' . $user_id . ')'));
    $course_periods_ass = $course_periods_ass[1]['COURSE_PERIOD_ID'];


    $ass_tec = array();
    $course_periods_ass_tec = DBGet(DBQuery(' SELECT TEACHER_ID, SECONDARY_TEACHER_ID  FROM course_periods WHERE COURSE_PERIOD_ID in (' . $course_periods_ass . ')'));


    foreach ($course_periods_ass_tec as $ass_tec_k => $ass_tec_v) {
        if ($ass_tec_v['TEACHER_ID'] != $user_id)
            $ass_tec[] = $ass_tec_v['TEACHER_ID'];
        if (($ass_tec_v['SECONDARY_TEACHER_ID'] != $user_id) && !empty($ass_tec_v['SECONDARY_TEACHER_ID']))
            $ass_tec[] = $ass_tec_v['SECONDARY_TEACHER_ID'];
    }

    $ass_tec = array_unique($ass_tec);
    ////////////////   end for associate teacher ////////////
    $tmp_q = '';
    $tmp_a = array();
    $tmp_stu_arr = array();
    $tmp_stf_arr = array();
    $tmp_p_arr = array();
    if ($course_periods != '') {
        $tmp_q = DBGet(DBQuery('SELECT DISTINCT se.STUDENT_ID FROM student_enrollment se,schedule s WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\'' . date('Y-m-d') . '\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\'' . date('Y-m-d') . '\') AND se.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID IN (' . $course_periods . ')'));
        foreach ($tmp_q as $tmp_a) {
            $tmp_stu_arr[] = $tmp_a['STUDENT_ID'];
            $tmp_qa = DBGet(DBQuery('SELECT DISTINCT PERSON_ID FROM students_join_people WHERE STUDENT_ID=' . $tmp_a['STUDENT_ID']));
            foreach ($tmp_qa as $tmp_aa) {
                $tmp_p_arr[] = $tmp_aa['PERSON_ID'];
            }
        }
    }

    $tmp_q = '';
    $tmp_a = array();
    $tmp_q = DBGet(DBQuery('SELECT s.STAFF_ID FROM staff s,staff_school_relationship ssr WHERE PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\'' . date('Y-m-d') . '\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\'' . date('Y-m-d') . '\') AND ssr.SCHOOL_ID IN (' . $schools . ')'));

    foreach ($tmp_q as $tmp_a) {
        $tmp_stf_arr[] = $tmp_a['STAFF_ID'];
    }

    /////////////  new for ass tec ////

    if (count($ass_tec) > 0) {
        foreach ($ass_tec as $ass_v) {
            $tmp_stf_arr[] = $ass_v;
        }
    }

    /////////////  new for ass tec ////
}

if ($keyword == "")
    echo "";
else {

    $sql_staff = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and first_name LIKE '$keyword%' and username IS NOT NULL and login_authentication.profile_id NOT IN(3,4) AND staff.staff_id in (" . implode(',', $tmp_stf_arr) . ") ORDER BY last_name";
    $sql_student = "SELECT * FROM login_authentication,students WHERE login_authentication.user_id=students.student_id and first_name LIKE '$keyword%' and username IS NOT NULL and login_authentication.profile_id=3 " . (count($tmp_stu_arr) > 0 ? " AND students.student_id IN (" . implode(',', $tmp_stu_arr) . ")" : "") . " ORDER BY last_name";
    $sql_people = "SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and first_name LIKE '$keyword%' and username IS NOT NULL and login_authentication.profile_id=4 " . (count($tmp_p_arr) > 0 ? " AND people.staff_id IN (" . implode(',', $tmp_p_arr) . ")" : "") . " ORDER BY last_name";

    $result_staff = DBGet(DBQuery($sql_staff));
    $result_student = DBGet(DBQuery($sql_student));
    $result_people = DBGet(DBQuery($sql_people));


    if (count($result_staff) > 0) {
        foreach ($result_staff as $row) {
            $str = ucfirst(trim($row['LAST_NAME'])) . ', ' . ucfirst(trim($row['FIRST_NAME'])) . ' - ' . ucfirst(trim($row['USERNAME']));
            if (trim($row['USERNAME'] != ""))
                echo '<li><a id="search' . $row['STAFF_ID'] . '" onclick="a(\'' . $row['USERNAME'] . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
        }
    } else
        echo "";


    if (count($result_student) > 0 && count($tmp_stu_arr) > 0) {
        foreach ($result_student as $row_student) {
            $str = ucfirst(trim($row_student['LAST_NAME'])) . ', ' . ucfirst(trim($row_student['FIRST_NAME'])) . ' - ' . ucfirst(trim($row_student['USERNAME']));
            if (trim($row_student['USERNAME'] != ""))
                echo '<li><a id="search' . $row_student['STUDENT_ID'] . '" onclick="a(\'' . $row_student['USERNAME'] . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
        }
    } else
        echo "";

    if (count($result_people) > 0 && count($tmp_p_arr) > 0) {
        foreach ($result_people as $row_people) {
            $str = ucfirst(trim($row_people['LAST_NAME'])) . ', ' . ucfirst(trim($row_people['FIRST_NAME'])) . ' - ' . ucfirst(trim($row_people['USERNAME']));
            if (trim($row_people['USERNAME'] != ""))
                echo '<li><a id="search' . $row_people['STAFF_ID'] . '" onclick="a(\'' . $row_people['USERNAME'] . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
        }
    } else
        echo "";
}



$pos = strpos($keyword, ',');
$lastpos = strrpos($keyword, ',');
$str1 = substr($keyword, $pos + 1, strlen($keyword));
$str2 = substr($keyword, $lastpos + 1, strlen($keyword));
if ($str2 != "") {
    if ($pos != 0 || $lastpos != 0) {

        $sql_staff = "SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and (first_name LIKE '$str1%' or first_name LIKE '$str2%') and username IS NOT NULL and login_authentication.profile_id NOT IN(3,4) AND staff.staff_id in (" . implode(',', $tmp_stf_arr) . ") ORDER BY last_name";
        $sql_student = "SELECT * FROM login_authentication,students WHERE login_authentication.user_id=students.student_id and (first_name LIKE '$str1%'  or first_name LIKE '$str2%') and username IS NOT NULL and login_authentication.profile_id=3 " . (count($tmp_stu_arr) > 0 ? " AND students.student_id IN (" . implode(',', $tmp_stu_arr) . ")" : "") . " ORDER BY last_name";
        $sql_people = "SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and (first_name LIKE '$str1%'  or first_name LIKE '$str2%') and username IS NOT NULL and login_authentication.profile_id=4 " . (count($tmp_p_arr) > 0 ? " AND people.staff_id IN (" . implode(',', $tmp_p_arr) . ")" : "") . " ORDER BY last_name";

        $result_staff = DBGet(DBQuery($sql_staff));
        $result_student = DBGet(DBQuery($sql_student));
        $result_people = DBGet(DBQuery($sql_people));

        if (count($result_staff) > 0) {
            foreach ($result_staff as $row) {
                $str = ucfirst(trim($row['LAST_NAME'])) . ', ' . ucfirst(trim($row['FIRST_NAME'])) . ' - ' . ucfirst(trim($row['USERNAME']));
                $newpos = $lastpos + 1;
                if (trim($row['USERNAME'] != ""))
                    echo '<li><a id="search' . $row['STAFF_ID'] . '" onclick="b(\'' . $newpos . '\',\'' . $row['USERNAME'] . '\',\'' . $block_id . '\');">' . $str . '</a></li>';
            }
        } else
            echo "";


        if (count($result_student) > 0 && count($tmp_stu_arr) > 0) {
            foreach ($result_student as $row_student) {
                $str = ucfirst(trim($row_student['LAST_NAME'])) . ', ' . ucfirst(trim($row_student['FIRST_NAME'])) . ' - ' . ucfirst(trim($row_student['USERNAME']));
                $newpos = $lastpos + 1;
                if (trim($row_student['USERNAME'] != ""))
                    echo '<li><a id="search' . $row_student['STUDENT_ID'] . '" onclick="b(\'' . $newpos . '\',\'' . $row_student['USERNAME'] . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
            }
        } else
            echo "";

        if (count($result_people) > 0 && count($tmp_p_arr) > 0) {
            foreach ($result_people as $row_people) {
                $str = ucfirst(trim($row_people['LAST_NAME'])) . ', ' . ucfirst(trim($row_people['FIRST_NAME'])) . ' - ' . ucfirst(trim($row_people['USERNAME']));
                $newpos = $lastpos + 1;
                if (trim($row_people['USERNAME'] != ""))
                    echo '<li><a id="search' . $row_people['STUDENT_ID'] . '" onclick="b(\'' . $newpos . '\',\'' . $row_people['USERNAME'] . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
            }
        } else
            echo "";
    }
} else
    echo "";

$group_id = DBGet(DBQuery("select distinct group_id,group_name from mail_group where group_name LIKE '$keyword%' AND user_name='" . $username_user . "' "));

if (count($group_id) > 0) {
    foreach ($group_id as $row) {
        $str = strtolower($row['GROUP_NAME']);
        $id = $row['GROUP_ID'];
        $group = DBGet(DBQuery("select * from mail_groupmembers where group_id=$id"));
        foreach ($group as $r) {
            $name[] = $r['USER_NAME'];
        }
        if (!empty($name) && count($name) > 0)
            $username = implode(',', $name);
        echo '<li><a id="search' . $row['GROUP_ID'] . '" onclick="a(\'' . $str . '\',\'' . $block_id . '\')">' . $str . '</a></li>';
    }
}
else {
    echo "";
}
?>
