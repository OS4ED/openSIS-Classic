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
include("UploadClassFnc.php");
include_once("../../functions/PasswordHashFnc.php");
ini_set('memory_limit', '1200000000M');
ini_set('max_execution_time', '500000');
unset($flag);

if ($_REQUEST['student_enable'] == 'N' && $_REQUEST['student_id'] != 'new') {
    DBQuery('UPDATE students SET IS_DISABLE=NULL WHERE student_id=' . $_REQUEST['student_id']);
}
if (isset($_REQUEST['custom_date_id']) && count($_REQUEST['custom_date_id']) > 0) {
    foreach ($_REQUEST['custom_date_id'] as $custom_id) {
        //$_REQUEST['students']['CUSTOM_' . $custom_id] = $_REQUEST['year_CUSTOM_' . $custom_id] . '-' . MonthFormatter($_REQUEST['month_CUSTOM_' . $custom_id]) . '-' . $_REQUEST['day_CUSTOM_' . $custom_id];

        $_REQUEST['students']['CUSTOM_' . $custom_id] = $_REQUEST['year_CUSTOM_' . $custom_id] . '-' . $_REQUEST['month_CUSTOM_' . $custom_id] . '-' . $_REQUEST['day_CUSTOM_' . $custom_id];
    }
}
if ($_REQUEST['modname'] && $_REQUEST['stu_id'] && $_REQUEST['include_a'] == 'EnrollmentInfoInc') {


    $res = DBGet(DBQuery('SELECT * FROM student_enrollment WHERE student_id=' . $_REQUEST['stu_id'] . ''));

    if ($res[1]['CALENDAR_ID'] == '' || $res[1]['CALENDAR_ID'] == NULL) {
        $sid = $_REQUEST['stu_id'];
        DBQuery('DELETE FROM students WHERE STUDENT_ID=' . $sid);
        DBQuery('DELETE FROM student_enrollment WHERE STUDENT_ID=' . $sid);
        $_REQUEST['modname'] = 'students/Student.php';
        $_REQUEST['include'] = 'GeneralInfoInc';
        $_REQUEST['category_id'] = 1;
        $_REQUEST['student_id'] = 'new';
    } else {
        $_REQUEST['modname'] = 'students/Student.php';
        $_REQUEST['include'] = 'GeneralInfoInc';
        $_REQUEST['category_id'] = 1;
        $_REQUEST['student_id'] = $_REQUEST['stu_id'];
    }
}

if ($_REQUEST['address_error']) {
    echo $_REQUEST['address_error'];
    unset($_REQUEST['address_error']);
}

if ($_REQUEST['month_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE'] != '' && $_REQUEST['day_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE'] != '' && $_REQUEST['year_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE'] != '') {
    $months_arr = array("JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC" => "12");
    $s_date = $_REQUEST['year_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE'] . '-' . $months_arr[$_REQUEST['month_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE']] . '-' . $_REQUEST['day_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE'];

    if ($_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['CALENDAR_ID'] != '') {
        $cal_id = $_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['CALENDAR_ID'];
    } else {
        $cal_id = DBGet(DBQuery('SELECT CALENDAR_ID FROM student_enrollment WHERE STUDENT_ID=' . $_REQUEST['student_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' ORDER BY ID DESC LIMIT 0,1'));
        $cal_id = $cal_id[1]['CALENDAR_ID'];
    }


    $get_c_dates = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) as START_DATE,MAX(SCHOOL_DATE) as END_DATE FROM attendance_calendar WHERE CALENDAR_ID=' . $cal_id . ' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
    if (count($get_c_dates) > 0) {
        $get_sch = DBGet(DBQuery('SELECT * FROM schedule WHERE STUDENT_ID=' . $_REQUEST['student_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND (END_DATE IS NULL OR END_DATE>=\'' . date('Y-m-d') . '\') ORDER BY START_DATE ASC '));
        if (count($get_sch) > 0) {
            foreach ($get_sch as $gsi => $gsd) {
                if (strtotime($s_date) > strtotime($gsd['START_DATE'])) {
                    unset($_REQUEST['month_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE']);
                    unset($_REQUEST['day_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE']);
                    unset($_REQUEST['year_values']['student_enrollment'][$_REQUEST['student_id']]['START_DATE']);
                    unset($_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['CALENDAR_ID']);
                    $err = '' . _cannotChangeStartDateAsStudentHasAssociationFrom . ' ' . date('F j, Y', strtotime($gsd['START_DATE']));
                    break;
                }
            }
        }
        //        }
    }

    if ($err != '')
        echo '<div class="alert bg-danger alert-styled-left">' . $err . '</div>';
    unset($err);
    unset($s_date);
    unset($get_c_dates);
    unset($cal_id);
}

if ($_REQUEST['month_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] != '' && $_REQUEST['day_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] != '' && $_REQUEST['year_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] != '' && $_REQUEST['values']['student_enrollment'][$_REQUEST['enrollment_id']]['DROP_CODE'] != '') {
    $months_arr = array("JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC" => "12");
    $s_date = strtotime($_REQUEST['year_values']['student_enrollment'][$_REQUEST['enrollment_id']]['START_DATE'] . '-' . $months_arr[$_REQUEST['month_values']['student_enrollment'][$_REQUEST['enrollment_id']]['START_DATE']] . '-' . $_REQUEST['day_values']['student_enrollment'][$_REQUEST['enrollment_id']]['START_DATE']);
    $e_date = strtotime($_REQUEST['year_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] . '-' . $months_arr[$_REQUEST['month_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE']] . '-' . $_REQUEST['day_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE']);

    if ($e_date < $s_date) {
        unset($_REQUEST['values']['student_enrollment'][$_REQUEST['enrollment_id']]['DROP_CODE']);
    }
}

if ($_REQUEST['values']['student_enrollment'][$_REQUEST['enrollment_id']]['DROP_CODE'] != '') {

    $drp_code = DBGet(DBQuery('SELECT * FROM student_enrollment_codes WHERE TYPE=\'TrnD\' AND SYEAR=' . UserSyear()));
    if ($_REQUEST['month_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] == '' && $_REQUEST['day_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] == '' && $_REQUEST['year_values']['student_enrollment'][$_REQUEST['enrollment_id']]['END_DATE'] == '' && $_REQUEST['values']['student_enrollment'][$_REQUEST['enrollment_id']]['DROP_CODE'] != $drp_code[1]['ID']) {

        echo "<p align='center'><b style='color:red'>" . _pleaseEnterProperDropDateCannotDropStudentWithoutDropDate . "</b></p>";
        unset($_REQUEST['values']['student_enrollment'][$_REQUEST['enrollment_id']]['DROP_CODE']);
    }
}
# ----------------------------- DELETE GoalInc & Progress -------------------------------------------- #

if ($_REQUEST['action'] == 'delete_goal' || $_REQUEST['action'] == 'delete_goal_can' || $_REQUEST['action'] == 'delete_goal_ok') {
    $goal_id = $_REQUEST['gid'];

    if (!isset($_REQUEST['ans'])) {

        PopTable('header',  _deleteConfirmation);
        echo "<CENTER class='m-b-15'><h4>" . _areYouSureWantToDeleteThisGoal . "</h4><BR/>
        <a href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete_goal_ok&gid=" . $goal_id . "&ans=yes' style='text-decoration:none;' class=\"btn btn-danger\"><strong>" . _ok . "</strong></a> 
		
		<a href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete_goal_can&gid=" . $goal_id . "&ans=no' style='text-decoration:none;' class=\"btn btn-primary\"><strong>" . _cancel . "</strong></a></CENTER>";

        PopTable('footer');
    } elseif (isset($_REQUEST['ans']) && $_REQUEST['ans'] == 'yes') {
        $sql_pro = 'SELECT progress_id FROM student_goal_progress WHERE goal_id=' . $goal_id;
        $row_pro_id = DBGet(DBQuery($sql_pro));

        $pro_final = $row_pro_id[1];
        if (!$pro_final) {
            DBQuery("DELETE FROM student_goal WHERE GOAL_ID = '" . $goal_id . "'");
            $_REQUEST['action'] = 'delete';
            $_REQUEST['goal_id'] = 'new';
            $_REQUEST['action'] = 'delete_goal_ok';
            unset($_REQUEST['modfunc']);
        } else {
            $_REQUEST['action'] = 'delete';
            $_REQUEST['goal_id'] = $goal_id;
            $_REQUEST['action'] = 'delete_goal_can';
            echo '<div class="alert bg-danger alert-styled-left">' . _unableToDeleteGoalPleaseDeleteProgressesFirst . '</div>';
            unset($_REQUEST['modfunc']);
        }
    } else {
        $_REQUEST['action'] = 'delete';
        $_REQUEST['goal_id'] = $goal_id;
        $_REQUEST['action'] = 'delete_goal_can';
        unset($_REQUEST['modfunc']);
    }
}

if ($_REQUEST['action'] == 'delete' || $_REQUEST['action'] == 'delete_can' || $_REQUEST['action'] == 'delete_ok') {

    $goal_id = $_REQUEST['gid'];
    $progress_id = $_REQUEST['pid'];

    if (!isset($_REQUEST['ans'])) {
        $_REQUEST['goal_id'] = $_REQUEST['gid'];

        PopTable('header',  _deleteConfirmation);
        echo "<CENTER class='m-b-15'><h4>" . _areYouSureWantToDeleteThisStudentGoalProgress . "</h4><BR/>
        <a href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete_ok&gid=" . $goal_id . "&pid=" . $progress_id . "&ans=yes' style='text-decoration:none;' class=\"btn btn-danger\"><strong>" . _ok . "</strong></a> 
		
		<a href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete_can&gid=" . $goal_id . "&pid=" . $progress_id . "&ans=no' style='text-decoration:none;' class=\"btn btn-primary\"><strong>" . _cancel . "</strong></a></CENTER>";

        PopTable('footer');
    } elseif (isset($_REQUEST['ans']) && $_REQUEST['ans'] == 'yes') {
        DBQuery('DELETE FROM student_goal_progress WHERE PROGRESS_ID = \'' . $_REQUEST['pid'] . '\'');
        $_REQUEST['action'] = 'delete';
        $_REQUEST['goal_id'] = $goal_id;
        $_REQUEST['action'] = 'delete_ok';
        unset($_REQUEST['modfunc']);
    } else {
        $_REQUEST['action'] = 'delete';
        $_REQUEST['goal_id'] = $goal_id;
        $_REQUEST['progress_id'] = $progress_id;
        $_REQUEST['action'] = 'delete_can';
        unset($_REQUEST['modfunc']);
    }
}


# ----------------------------------------------------------------------------------------------- #


if ($_REQUEST['action'] != 'delete' && $_REQUEST['action'] != 'delete_goal') {

    if (UserStudentID() != '' && $_REQUEST['student_id'] != 'new')
        $_REQUEST['student_id'] = UserStudentID();
    if (isset($_REQUEST['student_id']) && $_REQUEST['student_id'] != 'new' && $title_set != 'y' && clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) != 'detail' && clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) != 'lookup') {

        $NAVIGATED_STUDENT = '';

        if ($_REQUEST['v'] && isset($_REQUEST['student_id'])) {
            $NAV_val = optional_param('v', 0, PARAM_INT);
            if ($NAV_val == 1) {
                $NAVIGATED_STUDENT = $_SESSION['students_order'][1];
            }
            if ($NAV_val == 2) {
                $NAV_final_pos = array_search($_SESSION['student_id'], $_SESSION['students_order']);
                $NAV_final_pos = $NAV_final_pos - 1;
                $NAVIGATED_STUDENT = $_SESSION['students_order'][$NAV_final_pos];
            }
            if ($NAV_val == 3) {
                $NAV_final_pos = array_search($_SESSION['student_id'], $_SESSION['students_order']);
                $NAV_final_pos = $NAV_final_pos + 1;
                $NAVIGATED_STUDENT = $_SESSION['students_order'][$NAV_final_pos];
            }
            if ($NAV_val == 4) {
                unset($_SESSION['student_id']);
                $NAV_final_pos = count($_SESSION['students_order']);
                $NAVIGATED_STUDENT = $_SESSION['students_order'][$NAV_final_pos];
            }
        }

        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . ($NAVIGATED_STUDENT != '' ? $NAVIGATED_STUDENT : $_REQUEST['student_id']) . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));
        
        if (User('PROFILE') == 'parent')
            $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students s,students_join_people sj where s.student_id=sj.student_id and sj.person_id=' . UserID() . ''));
        else {
            $count_student_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM students"));
        }

        if ($count_student_RET[1]['NUM'] > 1 && User('PROFILE') != 'student' && User('PROFILE') != 'parent') {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=Students/Student.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToStudentList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div></div>');
        } else if (User('PROFILE') == 'student') {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _studentName . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6></div></div>');
        } else if ($count_student_RET[1]['NUM'] == 1) {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><A HREF=SideForStudent.php?student_id=new&modcat=' . $_REQUEST['modcat'] . '&modname=' . $_REQUEST['modname'] . '  class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div>');
        }
    }
    if ($title_set == 'y')
        $title_set = '';
    if (User('PROFILE') == 'admin') {
        if ($_REQUEST['student_id'] == 'new') {
            if (!$_REQUEST['include']) {
                unset($_SESSION['student_id']);
                unset($_SESSION['_REQUEST_vars']['student_id']);
            }
        }
    }
    //////////////////////////////////////////////////////////////////////////////////
    if ($_REQUEST['err_msg'] == true)
        echo "<center><font color=red><b>" . _birthdateIsInvalidDataCouldNotBeSaved . "</b><font></center>";

    if (clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) == 'update' && $_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new' && $_POST['button'] == 'Save') {
        //        if ($_POST['button'] == 'Save') { #&& AllowEdit()
        $transfer_flag = 0;

        if ($_REQUEST['TRANSFER']['SCHOOL'] != '' && $_REQUEST['TRANSFER']['Grade_Level'] != '') {
            $drop_code = $_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['DROP_CODE'];

            $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] = date("Y-m-d", strtotime($_REQUEST['year_TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '-' . $_REQUEST['month_TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '-' . $_REQUEST['day_TRANSFER']['STUDENT_ENROLLMENT_END_DATE']));

            $gread_exists = DBGet(DBQuery('SELECT COUNT(TITLE) AS PRESENT,ID FROM school_gradelevels WHERE SCHOOL_ID=\'' . $_REQUEST['TRANSFER']['SCHOOL'] . '\' AND TITLE=(SELECT TITLE FROM
                            school_gradelevels WHERE ID=(SELECT GRADE_ID FROM student_enrollment WHERE
                            STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\'  ORDER BY ID DESC LIMIT 1))'));  //pinki

            $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] = date("Y-m-d", strtotime($_REQUEST['year_TRANSFER']['STUDENT_ENROLLMENT_START'] . '-' . $_REQUEST['month_TRANSFER']['STUDENT_ENROLLMENT_START'] . '-' . $_REQUEST['day_TRANSFER']['STUDENT_ENROLLMENT_START']));




            if (strtotime($_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START']) >= strtotime($_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'])) {
                $check_asociation = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) as REC_EX FROM student_enrollment WHERE STUDENT_ID=' . $_REQUEST['student_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND START_DATE<=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' AND END_DATE<=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\') ORDER BY ID DESC LIMIT 0,1'));
                $end_date_old = $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'];
                $start_date_new = $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'];
                if ($start_date_new == $end_date_old) {
                    $_SESSION['ERR_TRANS'] = "<div class=\"alert bg-warning alert-styled-left\">" . _endDateAndStartDateCannotBeSame . "</div>";
                } else {
                    unset($_SESSION['ERR_TRANS']);
                    if ($check_asociation[1]['REC_EX'] != 0) {

                        $se_id = DBGet(DBQuery('SELECT MAX(id) AS ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\''));
                        DBQuery('UPDATE student_enrollment SET DROP_CODE=\'' . $drop_code . '\',END_DATE=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\' AND ID=\'' . $se_id[1]['ID'] . '\'');  //pinki  

                        $syear_RET = DBGet(DBQuery("SELECT MAX(SYEAR) AS SYEAR,TITLE FROM school_years WHERE SCHOOL_ID=" . $_REQUEST['TRANSFER']['SCHOOL']));
                        $syear = $syear_RET[1]['SYEAR'];
                        $enroll_code = DBGet(DBQuery('SELECT id FROM student_enrollment_codes WHERE syear=\'' . $syear . '\' AND type=\'TrnE\''));  //pinki
                        $last_school_RET = DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\'')); //pinki
                        $last_school = $last_school_RET[1]['SCHOOL_ID'];
                        $sch_id = $_REQUEST['TRANSFER']['SCHOOL'];
                        $num_default_cal = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ' AND DEFAULT_CALENDAR=\'Y\' '));
                        if (empty($num_default_cal)) {
                            $qr = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ' LIMIT 0,1'));

                            $calender_id = $qr[1]['CALENDAR_ID'];
                        }
                        if (count($num_default_cal) == 1) {
                            $calender_id = $num_default_cal[1]['CALENDAR_ID'];
                        } else {
                            $calender_id = 'NULL';
                        }
                        if ($gread_exists[1]['PRESENT'] == 1 && $gread_exists[1]['ID']) {
                            DBQuery("INSERT INTO student_enrollment (SYEAR ,SCHOOL_ID ,STUDENT_ID ,GRADE_ID ,START_DATE ,END_DATE ,ENROLLMENT_CODE ,DROP_CODE ,NEXT_SCHOOL ,CALENDAR_ID ,LAST_SCHOOL) VALUES (" . $syear . "," . $_REQUEST['TRANSFER']['SCHOOL'] . "," . $_REQUEST['student_id'] . "," . $_REQUEST['TRANSFER']['Grade_Level'] . ",'" . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] . "',''," . $enroll_code[1]['ID'] . ",'','" . $_REQUEST['TRANSFER']['SCHOOL'] . "',$calender_id,$last_school)");

                            $se_id = DBGet(DBQuery('SELECT MAX(id) AS ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\''));
                            DBQuery('UPDATE student_enrollment SET DROP_CODE=\'' . $drop_code . '\',END_DATE=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\' AND ID=\'' . $se_id[1]['ID'] . '\'');  //pinki    

                        } else {
                            DBQuery("INSERT INTO student_enrollment (SYEAR ,SCHOOL_ID ,STUDENT_ID ,GRADE_ID ,START_DATE ,END_DATE ,ENROLLMENT_CODE ,DROP_CODE ,NEXT_SCHOOL ,CALENDAR_ID ,LAST_SCHOOL) VALUES (" . $syear . "," . $_REQUEST['TRANSFER']['SCHOOL'] . "," . $_REQUEST['student_id'] . "," . $_REQUEST['TRANSFER']['Grade_Level'] . ",'" . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] . "',''," . $enroll_code[1]['ID'] . ",'','" . $_REQUEST['TRANSFER']['SCHOOL'] . "',$calender_id,$last_school)");

                            $se_id = DBGet(DBQuery('SELECT MAX(id) AS ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\''));
                            DBQuery('UPDATE student_enrollment SET DROP_CODE=\'' . $drop_code . '\',END_DATE=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\' AND ID=\'' . $se_id[1]['ID'] . '\'');  //pinki    

                        }
                        $trans_school = $syear_RET[1]['TITLE'];

                        $trans_student_RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID='" . $_REQUEST['student_id'] . "'"));

                        $trans_student = $trans_student_RET[1]['LAST_NAME'] . ' ' . $trans_student_RET[1]['FIRST_NAME'];
                        DBQuery('UPDATE medical_info SET SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ', SYEAR=' . $syear . ' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'');
                        unset($_REQUEST['modfunc']);
                        unset($_SESSION['_REQUEST_vars']['student_id']);
                        $transfer_flag = 1;
                    } else {
                        unset($_REQUEST['modfunc']);
                        unset($_SESSION['_REQUEST_vars']['student_id']);
                    }
                }
            } else {
                unset($_REQUEST['modfunc']);
                unset($_SESSION['_REQUEST_vars']['student_id']);
            }
        } else {

            if ($_REQUEST['TRANSFER']['SCHOOL'] == '' && $_REQUEST['TRANSFER']['Grade_Level'] != '')
                echo '<SCRIPT language=javascript>alert("' . _pleaseSelectSchool . '");window.close();</script>';
            if ($_REQUEST['TRANSFER']['SCHOOL'] != '' && $_REQUEST['TRANSFER']['Grade_Level'] == '')
                echo '<SCRIPT language=javascript>alert("' . _pleaseSelectGradeLevel . '");window.close();</script>';
            if ($_REQUEST['TRANSFER']['SCHOOL'] == '' && $_REQUEST['TRANSFER']['Grade_Level'] == '')
                unset($_REQUEST['modfunc']);
        }
        echo "<script language=javascript>window.location.href='Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]';</script>";
    } elseif (clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) == 'lookup') {
        if (clean_param($_REQUEST['func'], PARAM_NOTAGS) == 'search') {

            if ($_REQUEST['button'] == 'Find') {
                if ($_REQUEST['nfunc'] == 'status') {
                } else {
                    if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE'] || $_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
                        $stf_ids = '';
                        $sql = 'SELECT distinct stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf';
                        $sql_where = 'WHERE stf.PROFILE_ID=4 AND s.STUDENT_ID!=' . UserStudentID() . ' ';
                        if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE']) {
                            if ($_REQUEST['USERINFO_FIRST_NAME'])
                                $sql_where .= 'AND LOWER(stf.FIRST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_FIRST_NAME']))) . '%\' ';
                            if ($_REQUEST['USERINFO_LAST_NAME'])
                                $sql_where .= 'AND LOWER(stf.LAST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_LAST_NAME']))) . '%\' ';
                            if ($_REQUEST['USERINFO_EMAIL'])
                                $sql_where .= 'AND LOWER(stf.EMAIL) = \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_EMAIL']))) . '\' ';
                            if ($_REQUEST['USERINFO_MOBILE'])
                                $sql_where .= 'AND stf.CELL_PHONE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_MOBILE'])) . '\' ';
                        }
                        if ($_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
                            $sql .= ' LEFT OUTER JOIN student_address sa on sa.PEOPLE_ID=stf.STAFF_ID';
                            $sql_where .= '  AND sa.TYPE IN (\'Primary\',\'Secondary\',\'Other\') ';
                            if ($_REQUEST['USERINFO_SADD'])
                                $sql_where .= ' AND LOWER(STREET_ADDRESS_1) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_SADD']))) . '%\' ';
                            if ($_REQUEST['USERINFO_CITY'])
                                $sql_where .= ' AND LOWER(CITY) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_CITY']))) . '%\' ';
                            if ($_REQUEST['USERINFO_STATE'])
                                $sql_where .= ' AND LOWER(STATE) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_STATE']))) . '%\' ';
                            if ($_REQUEST['USERINFO_ZIP'])
                                $sql_where .= ' AND ZIPCODE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_ZIP'])) . '\' ';
                        }

                        $sql .= ' Left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID Left outer join students s on s.STUDENT_ID = sju.STUDENT_ID  ';
                        $sql_where .= '  AND LOWER(stf.FIRST_NAME)<>\'\' AND LOWER(stf.LAST_NAME)<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ') GROUP BY sju.PERSON_ID';

                        $searched_staffs = DBGet(DBQuery($sql . $sql_where), array('BUTTON' => 'makeChooseCheckbox'));
                        foreach ($searched_staffs as $key => $value) {
                            $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));
                            $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
                        }
                    } else {

                        $sql = 'SELECT stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID left outer join students s on s.STUDENT_ID = sju.STUDENT_ID  WHERE  s.STUDENT_ID!=' . UserStudentID() . '  AND stf.FIRST_NAME<>\'\' AND stf.LAST_NAME<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ') Group by stf.STAFF_ID';

                        $searched_staffs = DBGet(DBQuery($sql), array('BUTTON' => 'makeChooseCheckbox'));
                        foreach ($searched_staffs as $key => $value) {
                            $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));
                            $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
                        }
                    }
                }

                $singular = _user;
                $plural = _users;
                $options['save'] = false;
                $options['print'] = false;
                $options['search'] = false;

                $columns = array(
                    'BUTTON' => _selectAnyOne,
                    'FULLNAME' => _name,
                    'USERNAME' => _username,
                    'EMAIL' => _email,
                    'STUFULLNAME' => _associatedStudentSName,
                );
                if ($_REQUEST['add_id'] == 'new')
                    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST['modname'] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=new&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';
                else
                    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST['modname'] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=' . $_REQUEST['add_id'] . '&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';
                echo '<span id="sel_err" class="text-danger"></span>';
                ListOutput($searched_staffs, $columns, $singular, $plural, false, $group = false, $options, 'ForWindow');
                echo '<br>';
                echo '<center>';
                if (count($searched_staffs) > 0)
                    echo '<INPUT type=submit name=button value=' . _selectAnyOne . ' class="btn btn-primary" onclick="return sel_staff_val();">&nbsp;&nbsp;';
                echo '<INPUT type=submit name=button value="' . _cancel . '" class="btn btn-default">';
                //echo '</center>';
                echo '</form>';
            } else
                echo '<SCRIPT language=javascript>window.close();</script>';
        } else {
            //echo '<BR>';
            PopTableforWindow('header', '');

            echo '<h3 class="text-center">' . _searchForAnExistingPortalUserParentGuardian . ' <br/> ' . _toAssociateWithThisStudent . '</h3>';
            echo '<h5 class="text-danger text-center">' . _fillOutOneOrMoreFieldsToLookUpAnIndividual . '</h5>';
            if ($_REQUEST['add_id'] == 'new')
                echo "<FORM class=\"form-horizontal\" name=popform id=popform action=ForWindow.php?modname=$_REQUEST[modname]&modfunc=lookup&type=" . $_REQUEST['type'] . "&func=search&ajax=" . $_REQUEST['ajax'] . "&add_id=new&address_id=" . $_REQUEST['address_id'] . " METHOD=POST>";
            else
                echo "<FORM class=\"form-horizontal\" name=popform id=popform action=ForWindow.php?modname=$_REQUEST[modname]&modfunc=lookup&type=" . $_REQUEST['type'] . "&func=search&ajax=" . $_REQUEST['ajax'] . "&add_id=" . $_REQUEST['add_id'] . "&address_id=" . $_REQUEST['address_id'] . " METHOD=POST>";
            //echo '<div class=>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _firstName . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_FIRST_NAME', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _lastName . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_LAST_NAME', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _email . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_EMAIL', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _mobilePhone . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_MOBILE', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _streetAddress . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_SADD', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _city . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_CITY', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _state . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_STATE', '', 'class=form-control', true) . '</div></div>';
            echo '<div class="form-group"><label class="control-label col-xs-4">' . _zip . '</label><div class="col-xs-8">' . TextInput('', 'USERINFO_ZIP', '', 'class=form-control', true) . '</div></div>';

            echo '<div class="text-center"><INPUT type=submit class="btn btn-primary" name=button value=' . _find . ' onclick="formload_ajax(\'popform\');">&nbsp; &nbsp;<INPUT type=submit class="btn btn-default" name=button value="' . _cancel . '"></div>';

            echo '</FORM>';
            PopTableWindow('footer');
        }
    } else {

        /////////////////////////////////////////////////////////////////////////////////


        if (!$_REQUEST['include']) {
            $_REQUEST['include'] = 'GeneralInfoInc';
            $_REQUEST['category_id'] = '1';
        } elseif ($_REQUEST['include'] == 'GeneralInfoInc')
            $_REQUEST['category_id'] = '1';
        elseif ($_REQUEST['include'] == 'AddressInc')
            $_REQUEST['category_id'] = '3';
        elseif ($_REQUEST['include'] == 'MedicalInc')
            $_REQUEST['category_id'] = '2';
        elseif (trim($_REQUEST['include']) == 'CommentsInc') {
            $_REQUEST['category_id'] = '4';
        } elseif ($_REQUEST['include'] == 'GoalInc')
            $_REQUEST['category_id'] = '5';
        elseif ($_REQUEST['include'] == 'EnrollmentInfoInc')
            $_REQUEST['category_id'] = '6';
        elseif ($_REQUEST['include'] == 'FilesInc')
            $_REQUEST['category_id'] = '7';

        elseif ($_REQUEST['include'] != 'OtherInfoInc') {
            $include = DBGet(DBQuery("SELECT ID FROM student_field_categories WHERE INCLUDE='$_REQUEST[include]'"));
            $_REQUEST['category_id'] = $include[1]['ID'];
        }
        if (!$_REQUEST['category_id']) {
            if ($_REQUEST['include'] == 'GeneralInfoInc')
                $_REQUEST['category_id'] = '1';
        }
        if ($_REQUEST['category_id'] == 3 && !isset($_REQUEST['address_id'])) {


            $address_id = DBGet(DBQuery("SELECT ID as ADDRESS_ID FROM student_address WHERE STUDENT_ID='" . UserStudentID() . "' AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND TYPE='Home Address' "));
            $address_id = $address_id[1]['ADDRESS_ID'];
            if (!empty($address_id))
                $_REQUEST['address_id'] = $address_id;
            else
                $_REQUEST['address_id'] = 'new';
        }

        if ($_REQUEST['category_id'] == 5 && !isset($_REQUEST['goal_id'])) {
            $goal_id = DBGet(DBQuery("SELECT GOAL_ID,START_DATE,END_DATE FROM student_goal WHERE STUDENT_ID='" . UserStudentID() . "' AND SYEAR='" . UserSyear() . "'"));
            $goal_id = $goal_id[1]['GOAL_ID'];
            if (!empty($goal_id))
                $_REQUEST['goal_id'] = $goal_id;
            else
                $_REQUEST['goal_id'] = 'new';
        }

        if (User('PROFILE') != 'admin') {
            if (User('PROFILE') != 'student')
                if (User('PROFILE_ID'))
                    $can_edit_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND MODNAME='students/Student.php&category_id=$_REQUEST[category_id]' AND CAN_EDIT='Y'"));
                else {
                    $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
                    $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                    if ($profile_id_mod != '')
                        $can_edit_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND MODNAME='students/Student.php&category_id=$_REQUEST[category_id]' AND CAN_EDIT='Y'"), array(), array('MODNAME'));
                }
            else
                $can_edit_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='3' AND MODNAME='students/Student.php&category_id=$_REQUEST[category_id]' AND CAN_EDIT='Y'"));
            if ($can_edit_RET)
                $_openSIS['allow_edit'] = true;
        }

        if (clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) == 'update' && AllowEdit()) {
            if (is_countable($_REQUEST['month_students']) && count($_REQUEST['month_students'])) {
                foreach ($_REQUEST['month_students'] as $column => $value) {
                    $_REQUEST['students'][$column] = $_REQUEST['day_students'][$column] . '-' . $_REQUEST['month_students'][$column] . '-' . $_REQUEST['year_students'][$column];

                    if ($_REQUEST['students'][$column] == '--') {
                        $_REQUEST['students'][$column] = '';
                        $day_valid = true;
                    } else {
                        $day_valid = true;
                        if (substr($column, 0, 6) == 'CUSTOM' && $_REQUEST['students'][$column] != '--')
                            $_REQUEST['students'][$column] = date('Y-m-d', strtotime($_REQUEST['students'][$column]));
                    }
                }
            }
            unset($_REQUEST['day_students']);
            unset($_REQUEST['month_students']);
            unset($_REQUEST['year_students']);
            if ($_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new') {
                $_SESSION['student_id'] = $_REQUEST['student_id'];
                $stud_rec = DBGet(DBQuery("SELECT BIRTHDATE,FIRST_NAME,MIDDLE_NAME,LAST_NAME FROM students WHERE 
                            STUDENT_ID=" . UserStudentID()));
                if (isset($_REQUEST['students']['BIRTHDATE'])) {
                    $stud_rec[1]['BIRTHDATE'] = date('Y-m-d', strtotime($_REQUEST['students']['BIRTHDATE']));
                }
                if (isset($_REQUEST['students']['FIRST_NAME'])) {
                    $stud_rec[1]['FIRST_NAME'] = str_replace("'", "''", str_replace("\'", "'", $_REQUEST['students']['FIRST_NAME']));
                }
                if (isset($_REQUEST['students']['LAST_NAME'])) {
                    $stud_rec[1]['LAST_NAME'] = str_replace("'", "''", str_replace("\'", "'", $_REQUEST['students']['LAST_NAME']));
                }
                $qry = "SELECT COUNT(1) AS COUNT FROM students s,student_enrollment se WHERE s.BIRTHDATE='" . $stud_rec[1]['BIRTHDATE'] . "'
        AND s.FIRST_NAME='" . str_replace("'", "\'", $stud_rec[1]['FIRST_NAME']) . "'
        AND s.LAST_NAME='" . str_replace("'", "\'", $stud_rec[1]['LAST_NAME']) . "' AND s.STUDENT_ID!='" . UserStudentID() . "' 
        AND se.GRADE_ID=(SELECT GRADE_ID FROM student_enrollment WHERE STUDENT_ID='" . UserStudentID() . "'
        AND SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' AND ID=(SELECT MAX(ID) FROM student_enrollment WHERE STUDENT_ID='" . UserStudentID() . "')) AND se.SYEAR='" . UserSyear() . "' AND s.STUDENT_ID=se.STUDENT_ID";
                if (isset($_REQUEST['students']['MIDDLE_NAME'])) {
                    $stud_rec[1]['MIDDLE_NAME'] = str_replace("'", "''", str_replace("\'", "'", $_REQUEST['students']['MIDDLE_NAME']));
                    if ($_REQUEST['students']['MIDDLE_NAME'] != '')
                        $qry .= " AND s.MIDDLE_NAME='" . str_replace("'", "\'", $stud_rec[1]['MIDDLE_NAME']) . "'";
                }

                $qry_exec = DBGet(DBQuery($qry));
                unset($qry);
                if ($qry_exec[1]['COUNT'] > 0 && ($_REQUEST['category_id'] == 1 || $_REQUEST['category_id'] == 6)) {
                    $flag = true;

                    $n = DuplicateStudent(_duplicateDataFoundRequestAlreadyExists, _update);
                    if ($n == 1) {
                        unset($_REQUEST['delete_ok']);
                        $_REQUEST['ajax'] = 1;
                    }
                } else {
                    $flag = false;
                    $n = 1;
                }
            } else {
                $n = 1;
                $flag = true;
            }
            if ($n == 1) {
                $flag = false;

                if (((is_countable($_REQUEST['students']) && count($_REQUEST['students'])) || (is_countable($_REQUEST['values']) && count($_REQUEST['values'])) || (is_countable($_REQUEST['month_values']) && count($_REQUEST['month_values']))) && AllowEdit()) {

                    //print_r($content1);
                    //                    print_r($_REQUEST);
                    //echo '<br/><br/>';
                    // print_r($_FILES);
                    //exit;
                    if ($_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new') {

                        if (is_countable($_REQUEST['students']) && count($_REQUEST['students'])) {
                            $log_go = false;
                            $sql = "UPDATE students SET ";
                            $log_sql = 'UPDATE login_authentication SET ';
                            foreach ($_REQUEST['students'] as $column_name => $value) {

                                if (substr($column_name, 0, 6) == 'CUSTOM') {

                                    $custom_id = str_replace("CUSTOM_", "", $column_name);
                                    $custom_RET = DBGet(DBQuery("SELECT TITLE,TYPE FROM custom_fields WHERE ID=" . $custom_id));

                                    if ($custom_RET[1]['TYPE'] == 'multiple') {
                                        $valueSize = count($value);
                                        if ($valueSize == 0) {
                                            $valueSize = '';
                                        }
                                    } else {
                                        $valueSize = trim($value);
                                    }

                                    $custom = DBGet(DBQuery("SHOW COLUMNS FROM students WHERE FIELD='" . $column_name . "'"));
                                    $custom = $custom[1];
                                    if ($custom['NULL'] == 'NO' && trim($valueSize) == '' && $custom['DEFAULT']) {
                                        $value = $custom['DEFAULT'];
                                    } else if ($custom['NULL'] == 'NO' && $valueSize == '') {
                                        $custom_TITLE = $custom_RET[1]['TITLE'];
                                        echo "<div class='alert alert-danger'>" . ucfirst(strtolower(_unableToSaveDataBecause)) . " " . $custom_TITLE . ' ' . _isRequired . '</div>';
                                        $error = true;
                                    } else if ($custom_RET[1]['TYPE'] == 'numeric' && (!is_numeric($value) && $value != '')) {
                                        $custom_TITLE = $custom_RET[1]['TITLE'];
                                        echo "<div class='alert alert-danger'>" . ucfirst(strtolower(_unableToSaveDataBecause)) . " " . $custom_TITLE . ' ' . _isNumericType . '</div>';
                                        $error = true;
                                    } else {
                                        $m_custom_RET = DBGet(DBQuery("select ID,TITLE,TYPE from custom_fields WHERE ID='" . $custom_id . "' AND TYPE='multiple'"));
                                        if ($m_custom_RET) {
                                            $str = "";
                                            foreach ($value as $m_custom_val) {
                                                if ($m_custom_val)
                                                    $str .= "||" . $m_custom_val;
                                            }
                                            if ($str)
                                                $value = $str . "||";
                                            else {
                                                $value = '';
                                            }
                                        }
                                    }  ###Myelse ends#####
                                }  ###Custom Ends#####

                                if ($column_name != 'FIRST_NAME' && $column_name != 'MIDDLE_NAME' && $column_name != 'LAST_NAME') {
                                    if ($column_name == 'ALT_ID' && $value != '') {

                                        $alt_check = DBGet(DBQuery("SELECT * FROM students WHERE ALT_ID='" . $value . "' AND STUDENT_ID!=" . $_REQUEST['student_id']));
                                        if (count($alt_check) == 0) {
                                            $value = paramlib_validation($column_name, trim($value));
                                        } else {
                                            echo "<font color=red><b>" . _unableToSaveDataBecauseDuplicateAlternateIdFound . "</b></font><br/>";
                                            $error = true;
                                        }
                                    } else
                                        $value = paramlib_validation($column_name, trim($value));
                                }

                                if ($column_name == 'BIRTHDATE' || $column_name == 'ESTIMATED_GRAD_DATE') {
                                    if ($value != "")
                                        $value = date("Y-m-d", strtotime($value));
                                }

                                if ($column_name == 'PASSWORD' && $value != '') {
                                    $log_go = true;
                                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                        $password = str_replace("'", "''", str_replace("`", "''", (trim($value))));
                                        $new_password = GenerateNewHash($password);
                                        $log_sql .= "$column_name='" . $new_password . "',";
                                    } else {
                                        $password = str_replace("'", "''", str_replace("`", "''", (trim($value))));
                                        $new_password = GenerateNewHash($password);
                                        $log_sql .= "$column_name='" . $new_password . "',";
                                    }}
                                  elseif ($column_name == 'USERNAME' && $value != '') {
                                    $usernameExists = DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME=\'' . $value . '\' AND NOT(USER_ID = \'' . $_REQUEST['student_id'] . '\' AND PROFILE_ID = 3)'));
                                    if(count($usernameExists) == 0){
                                        $log_go = true;
                                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                            $log_sql .= "$column_name='" . str_replace("'", "''", str_replace("`", "''", trim($value))) . "',";
                                        } else
                                            $log_sql .= "$column_name='" . str_replace("'", "''", str_replace("`", "''", trim($value))) . "',";
                                    } else {
                                        echo "<div class='alert alert-danger'>Username already exists!</div>";
                                        $error = true;
                                    }
                                }
                                else {
                                    if ($column_name != 'USERNAME') {
                                        if ($column_name != 'PASSWORD') {
                                            if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                                $sql .= "$column_name=" . "'" . str_replace("'", "''", str_replace("\'", "'", trim($value))) . "',";
                                            } else
                                                $sql .= "$column_name=" . "'" . str_replace("'", "''", str_replace("\'", "'", trim($value))) . "',";
                                        }
                                    }
                                }
                                if ($column_name == 'IS_DISABLE' && $value != 'Y') {
                                    DBQuery("UPDATE login_authentication SET FAILED_LOGIN=NULL,LAST_LOGIN=NOW() WHERE USER_ID=" . $_REQUEST['student_id'] . " AND PROFILE_ID=0");
                                }
                            }

                            $sql = substr($sql, 0, -1) . " WHERE STUDENT_ID='$_REQUEST[student_id]'";
                            $log_sql = substr($log_sql, 0, -1) . " WHERE USER_ID='$_REQUEST[student_id]' AND PROFILE_ID=3";
                            if (!$error) {
                                DBQuery($sql);
                                $last_student_id = $_REQUEST['student_id'];

                                if ($_FILES['file']['name']) {

                                    //                                    $target_path = $StudentPicturesPath . '/' . $last_student_id . '.JPG';
                                    //                                    $destination_path = $StudentPicturesPath;
                                    //                                    $upload = new upload();
                                    //                                    $upload->target_path = $target_path;
                                    //                                    $upload->deleteOldImage();
                                    //                                    $upload->destination_path = $destination_path;
                                    //                                    $upload->name = $_FILES["file"]["name"];
                                    //                                    $upload->setFileExtension();
                                    //                                    $upload->fileExtension;
                                    //                                    $upload->validateImage();
                                    //                                    if ($upload->wrongFormat == 1) {
                                    //                                        $_FILES["file"]["error"] = 1;
                                    //                                    }
                                    //
                                    //                                    if ($_FILES["file"]["error"] > 0) {
                                    //                                        echo "cannot upload file";
                                    //                                    } else {
                                    //                                        move_uploaded_file($_FILES["file"]["tmp_name"], $upload->target_path);
                                    //                                        @fopen($upload->target_path, 'r');
                                    //                                        fclose($upload->target_path);
                                    //                                        $filename = $upload->target_path;
                                    //                                    }


                                    $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . $_REQUEST['student_id'] . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
                                    $fileName = $_FILES['file']['name'];
                                    $tmpName = $_FILES['file']['tmp_name'];
                                    $fileSize = $_FILES['file']['size'];
                                    $fileType = $_FILES['file']['type'];
                                    //                                        $target_path=$StudentPicturesPath.'/'.$last_student_id.'.JPG';
                                    //	$destination_path = $StudentPicturesPath;	   
                                    $upload = new upload();
                                    //	$upload->target_path=$target_path;
                                    if (count($stu_img_info) > 0)
                                        $upload->deleteOldImage($stu_img_info[1]['ID']);
                                    //	$upload->destination_path=$destination_path;
                                    $upload->name = $_FILES["file"]["name"];
                                    $upload->setFileExtension();
                                    $upload->fileExtension;
                                    $upload->validateImage();
                                    if ($upload->wrongFormat == 1) {
                                        $_FILES["file"]["error"] = 1;
                                    }
                                    if ($fileSize > 10000000) {
                                        echo "<font style='color:red'><b>" . _FileExceedsTheAllowableSizeTryAgainWithAFileLessThen10Mb . "</b></font><br>";
                                    }

                                    if ($_FILES["file"]["error"] > 0) {
                                        echo "<font style='color:red'><b>" . _unsupportedFileFormatCannotUploadFile . "</b></font><br>";
                                    } else {
                                        $content = base64_decode($_REQUEST['imgblob']);
                                        $content = addslashes($content);
                                        $fileName = addslashes($fileName);
                                        DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . $_REQUEST['student_id'] . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'stuimg\')');
                                    }
                                }
                                /////////////////  for update those students pic who have not upload pic before////                  
                            }
                            if ($log_go) {
                                DBQuery($log_sql);
                            }
                            $enrollment_info = DBGet(DBQuery("select enrollment_code from student_enrollment where STUDENT_ID=$_REQUEST[student_id]"));
                            $enrollment_code = $enrollment_info[1]['ENROLLMENT_CODE'];
                            if ($enrollment_code == NULL)
                                echo "<script>window.location.href='Modules.php?modname=students/Student.php&include=EnrollmentInfoInc&category_id=6'</script>";
                        }
                        if (is_countable($_REQUEST['medical_info']) && count($_REQUEST['medical_info'])) {
                            $get_medical_info = DBGet(DBQuery('SELECT * FROM medical_info WHERE STUDENT_ID=' . $_REQUEST['student_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool()));
                            if (count($get_medical_info) > 0) {
                                $sql = "UPDATE medical_info SET ";
                                foreach ($_REQUEST['medical_info'] as $column_name => $value) {
                                    $value = paramlib_validation($column_name, trim($value));
                                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                        $sql .= "$column_name='" . str_replace("'", "\'", str_replace("`", "''", trim($value))) . "',";
                                    } else
                                        $sql .= "$column_name='" . str_replace("'", "''", str_replace("'`", "''", trim($value))) . "',";
                                }
                                $sql = substr($sql, 0, -1) . " WHERE STUDENT_ID='$_REQUEST[student_id]' AND SYEAR=" . UserSyear() . " AND SCHOOL_ID=" . UserSchool() . "";
                            } else {
                                $sql = "INSERT INTO medical_info  ";
                                $columns_medical = 'STUDENT_ID,SYEAR,SCHOOL_ID,';
                                $values_medical = $_REQUEST['student_id'] . ',' . UserSyear() . ',' . UserSchool() . ',';
                                foreach ($_REQUEST['medical_info'] as $column_name => $value) {
                                    $value = paramlib_validation($column_name, trim($value));
                                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                        $columns_medical .= $column_name . ',';
                                        $values_medical .= "'" . str_replace("'", "\'", str_replace("`", "''", trim($value))) . "',";
                                    } else {
                                        $columns_medical .= $column_name . ',';
                                        $values_medical .= "'" . str_replace("'", "\'", str_replace("`", "''", trim($value))) . "',";
                                    }
                                }

                                $sql = "INSERT INTO medical_info  (" . substr($columns_medical, 0, -1) . ") VALUES (" . substr($values_medical, 0, -1) . ")";
                                unset($columns_medical);
                                unset($values_medical);
                            }


                            if (!$error) {
                                DBQuery($sql);
                            }
                        }
                        $stu_enroll_id = DBGet(DBQuery('SELECT MAX(ID) AS M_ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                        if (!$_REQUEST['enrollment_id'])
                            $e_id = $stu_enroll_id[1]['M_ID'];
                        else
                            $e_id = $_REQUEST['enrollment_id'];

                        if ($_REQUEST['month_values']['student_enrollment'][$e_id]['START_DATE'] != '' && $_REQUEST['day_values']['student_enrollment'][$e_id]['START_DATE'] != '' && $_REQUEST['year_values']['student_enrollment'][$e_id]['START_DATE'] != '') {
                            $mon = $_REQUEST['month_values']['student_enrollment'][$e_id]['START_DATE'];
                            $day = $_REQUEST['day_values']['student_enrollment'][$e_id]['START_DATE'];
                            $year = $_REQUEST['year_values']['student_enrollment'][$e_id]['START_DATE'];
                            if ($mon == 'JAN')
                                $mon = '01';
                            if ($mon == 'FEB')
                                $mon = '02';
                            if ($mon == 'MAR')
                                $mon = '03';
                            if ($mon == 'APR')
                                $mon = '04';
                            if ($mon == 'MAY')
                                $mon = '05';
                            if ($mon == 'JUN')
                                $mon = '06';
                            if ($mon == 'JUL')
                                $mon = '07';
                            if ($mon == 'AUG')
                                $mon = '08';
                            if ($mon == 'SEP')
                                $mon = '09';
                            if ($mon == 'OCT')
                                $mon = '10';
                            if ($mon == 'NOV')
                                $mon = '11';
                            if ($mon == 'DEC')
                                $mon = '12';
                            $_REQUEST['values']['student_enrollment'][$e_id]['START_DATE'] = $year . '-' . $mon . '-' . $day;
                            unset($mon);
                            unset($year);
                            unset($day);
                        }
                        if ($_REQUEST['month_values']['student_enrollment'][$e_id]['END_DATE'] != '' && $_REQUEST['day_values']['student_enrollment'][$e_id]['END_DATE'] != '' && $_REQUEST['year_values']['student_enrollment'][$e_id]['END_DATE'] != '') {
                            $mon = $_REQUEST['month_values']['student_enrollment'][$e_id]['END_DATE'];
                            $day = $_REQUEST['day_values']['student_enrollment'][$e_id]['END_DATE'];
                            $year = $_REQUEST['year_values']['student_enrollment'][$e_id]['END_DATE'];
                            if ($mon == 'JAN')
                                $mon = '01';
                            if ($mon == 'FEB')
                                $mon = '02';
                            if ($mon == 'MAR')
                                $mon = '03';
                            if ($mon == 'APR')
                                $mon = '04';
                            if ($mon == 'MAY')
                                $mon = '05';
                            if ($mon == 'JUN')
                                $mon = '06';
                            if ($mon == 'JUL')
                                $mon = '07';
                            if ($mon == 'AUG')
                                $mon = '08';
                            if ($mon == 'SEP')
                                $mon = '09';
                            if ($mon == 'OCT')
                                $mon = '10';
                            if ($mon == 'NOV')
                                $mon = '11';
                            if ($mon == 'DEC')
                                $mon = '12';
                            $_REQUEST['values']['student_enrollment'][$e_id]['END_DATE'] = $year . '-' . $mon . '-' . $day;
                            unset($mon);
                            unset($year);
                            unset($day);
                        }
                        if ($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE'] != '') {
                            if ($_REQUEST['values']['student_enrollment'][$e_id]['START_DATE'] != '' && $_REQUEST['values']['student_enrollment'][$e_id]['START_DATE'] > $_REQUEST['values']['student_enrollment'][$e_id]['END_DATE']) {
                                unset($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE']);
                                unset($_REQUEST['values']['student_enrollment'][$e_id]['START_DATE']);
                            }
                            if ($_REQUEST['values']['student_enrollment'][$e_id]['START_DATE'] == '') {
                                $get_sd = DBGet(DBQuery('SELECT START_DATE FROM student_enrollment WHERE ID=' . $e_id));
                                if ($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE'] <= $get_sd[1]['START_DATE'])
                                    unset($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE']);
                            }
                        }

                        if ($_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['GRADE_ID'] != '') {
                            if ($e_id != '')
                                DBQuery('UPDATE student_enrollment SET grade_id=' . $_REQUEST['values']['student_enrollment'][$_REQUEST['student_id']]['GRADE_ID'] . ' WHERE ID=' . $e_id);
                        }
                        if (is_countable($_REQUEST['values']['student_enrollment'][$e_id]) && count($_REQUEST['values']['student_enrollment'][$e_id])) {
                            $sql = 'SELECT ID,COURSE_ID,COURSE_PERIOD_ID,MARKING_PERIOD_ID FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'';
                            $schedules = DBGet(DBQuery($sql));
                            $c = count($schedules);
                            if ($c > 0) {
                                for ($i = 1; $i <= count($schedules); $i++) {
                                    $cp_id[$i] = $schedules[$i]['COURSE_PERIOD_ID'];
                                }
                                $st_cp_id = implode(',', $cp_id);
                                $sql = 'SELECT MAX(SCHOOL_DATE) AS SCHOOL_DATE FROM attendance_period WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID IN (' . $st_cp_id . ')';
                                $attendence = DBGet(DBQuery($sql));
                                $max_at_dt = $attendence[1]['SCHOOL_DATE'];
                            }
                            $sql = "UPDATE student_enrollment SET ";
                            foreach ($_REQUEST['values']['student_enrollment'][$e_id] as $column_name => $value) {


                                if ($column_name == 'END_DATE') {


                                    $sql .= "$column_name='" . str_replace("\'", "''", date('Y-m-d', strtotime($value))) . "',";

                                    $error = false;
                                }
                                if ($column_name == 'START_DATE') {

                                    $sql1 = 'SELECT LAST_SCHOOL,END_DATE AS END_DATE FROM student_enrollment WHERE ID=\'' . $e_id . '\'';
                                    $end_date = DBGet(DBQuery($sql1));
                                    $last_school = $end_date[1]['LAST_SCHOOL'];
                                    $end_date = $end_date[1]['END_DATE'];
                                    if ($last_school != '') {
                                        $get_ls_drop_date = DBGet(DBQuery('SELECT END_DATE FROM student_enrollment WHERE STUDENT_ID=' . UserStudentID() . ' AND SCHOOL_ID=' . $last_school . ' AND ID <> \'' . $e_id . '\' ORDER BY ID DESC LIMIT 0,1'));
                                        $get_ls_drop_date = $get_ls_drop_date[1]['END_DATE'];
                                    }
                                    if ($end_date != '') {
                                        if ($value <= $end_date) {

                                            if ($value >= $get_ls_drop_date) {
                                                $sql .= "$column_name='" . str_replace("\'", "''", date('Y-m-d', strtotime($value))) . "',";
                                                $error = false;
                                            } else
                                                $msg = _startDateCannotBeBeforeStudentsPreviousSchoolDropDate;
                                        } else {
                                            $msg = _studentDropDateCannotBeBeforeStudentEnrollmentDate;
                                        }
                                    } elseif ($value != '') {
                                        if ($value >= $get_ls_drop_date) {
                                            $sql .= "$column_name='" . str_replace("\'", "''", date('Y-m-d', strtotime($value))) . "',";
                                            $error = false;
                                        } else
                                            $msg = _startDateCannotBeBeforeStudentsPreviousSchoolDropDate;
                                    }
                                } elseif ($column_name == 'GRADE_ID') {
                                    if ($value != '') {
                                        $sql .= "$column_name='" . str_replace("\'", "''", str_replace('&#39;', "''", $value)) . "',";
                                        $error = false;
                                    } else {
                                        $msg = _gradeCannotBeBlank;
                                        $error = true;
                                    }
                                } else {
                                    $sql .= "$column_name='" . str_replace("\'", "''", str_replace('&#39;', "''", $value)) . "',";
                                    $error = false;
                                }
                            }
                            $sql = substr($sql, 0, -1) . " WHERE STUDENT_ID='$_REQUEST[student_id]' AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND iD='" . $e_id . "'";
                            //                            print_r($_REQUEST);
                            //                            echo $_REQUEST['values']['student_enrollment'][$e_id]['DROP_CODE'];
                            if (isset($_REQUEST['values']['student_enrollment'][$e_id]['DROP_CODE']) && $_REQUEST['values']['student_enrollment'][$e_id]['DROP_CODE'] != '') {
                                $sql_drop_type_qr = DBGet(DBQuery('SELECT type FROM student_enrollment_codes WHERE id=' . $_REQUEST['values']['student_enrollment'][$e_id]['DROP_CODE']));
                                //    print_r($sql_drop_type_qr);
                                if ($sql_drop_type_qr[1]['TYPE'] == 'TrnD') {
                                    if ($transfer_flag == 1) {
                                        $error = false;
                                    } else {
                                        $msg = _pleaseProvideAllDetailsToTransferInNewSchool;
                                        $error = true;
                                    }
                                }
                                //                                $error = false;
                                //                            else {
                                //                              $msg = _pleaseSelectTransferSchool;
                                //                                        $error = true;  

                            }
                            if (!$error) {
                                if ($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE'] != '') {
                                    if (strtotime($_REQUEST['values']['student_enrollment'][$e_id]['END_DATE']) >= strtotime($max_at_dt)) {

                                        DBQuery($sql);
                                        if (!empty($schedule_id)) {
                                            DBQuery($sql_schedule);
                                        }
                                    }
                                } elseif (!$_REQUEST['values']['student_enrollment'][$e_id]['END_DATE'] && $_REQUEST['values']['student_enrollment'][$e_id]['DROP_CODE'] != '') {
                                    if (strtotime(date('Y-m-d')) >= strtotime($max_at_dt)) {
                                        DBQuery($sql);
                                    }
                                } else {

                                    DBQuery($sql);
                                }
                            }
                        }
                        if ($msg)
                            echo '<font style="color:red"><b>' . $msg . '</b></font>';
                    } else {
                        if ($_REQUEST['assign_student_id']) {
                            $student_id = $_REQUEST['assign_student_id'];
                            if (count(DBGet(DBQuery("SELECT STUDENT_ID FROM students WHERE STUDENT_ID='$student_id'"))))
                                BackPrompt(_thatStudentIdIsAlreadyTakenPleaseSelectADifferentOne);
                        } else {
                            $get_student_id = DBGet(DBQuery("SELECT STUDENT_ID FROM students WHERE STUDENT_ID='$student_id'"));
                            do {
                                $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'students'"));
                                $student_id[1]['STUDENT_ID'] = $id[1]['AUTO_INCREMENT'];
                                $student_id = $student_id[1]['STUDENT_ID'];
                            } while (is_countable($get_student_id) && count($get_student_id));
                        }
                        $sql = "INSERT INTO students ";
                        $log_sql = 'INSERT INTO login_authentication ';
                        $fields = '';
                        $values = "";
                        $log_fields = 'PROFILE_ID,USER_ID,';
                        $log_values = '3,' . $student_id . ',';
                        foreach ($_REQUEST['students'] as $column => $value) {

                            if (substr($column, 0, 6) == 'CUSTOM') {
                                $custom_id = str_replace("CUSTOM_", "", $column);
                                $custom_RET = DBGet(DBQuery("SELECT TITLE,TYPE FROM custom_fields WHERE ID=" . $custom_id));

                                $custom = DBGet(DBQuery("SHOW COLUMNS FROM students WHERE FIELD='" . $column . "'"));
                                $custom = $custom[1];
                                if ($custom['NULL'] == 'NO' && $value == '' && !$custom['DEFAULT']) {
                                    $custom_TITLE = $custom_RET[1]['TITLE'];
                                    $required_faild_error = true;
                                    echo "<font color=red><b>" . _unableToSaveDataBecause . " " . $custom_TITLE . ' ' . _isRequired . '</b></font><br/>';
                                    $error = true;
                                } elseif ($custom_RET[1]['TYPE'] == 'numeric' && (!is_numeric($value) && $value != '')) {
                                    $type_faild_error = true;
                                    $custom_TITLE = $custom_RET[1]['TITLE'];
                                    echo "<font color=red><b>" . _unableToSaveDataBecause . " " . $custom_TITLE . ' ' . _isNumericType . '</b></font><br/>';
                                    $error = true;
                                } else {
                                    $m_custom_RET = DBGet(DBQuery("select ID,TITLE,TYPE from custom_fields WHERE ID='" . $custom_id . "' AND TYPE='multiple'"));
                                    if ($m_custom_RET) {
                                        $str = "";
                                        foreach ($value as $m_custom_val) {
                                            if ($m_custom_val)
                                                $str .= "||" . $m_custom_val;
                                        }
                                        if ($str)
                                            $value = $str . "||";
                                        else
                                            $value = '';
                                    }
                                }
                            }
                            if ($value) {
                                if ($column != 'FIRST_NAME' && $column != 'MIDDLE_NAME' && $column != 'LAST_NAME') {
                                    if ($column == 'ALT_ID' && $value != '') {
                                        $alt_check = DBGet(DBQuery("SELECT * FROM students WHERE ALT_ID='" . $value . "'"));
                                        if (count($alt_check) == 0) {
                                            $value = paramlib_validation($column, trim($value));
                                        } else {
                                            echo "<font color=red><b>" . _unableToSaveDataBecauseDuplicateAlternateIdFound . "</b></font><br/>";
                                            $error = true;
                                        }
                                    } else
                                        $value = paramlib_validation($column, trim($value));
                                }

                                if ($column == 'BIRTHDATE' || $column == 'ESTIMATED_GRAD_DATE') {
                                    if ($value != "")
                                        $value = date("Y-m-d", strtotime($value));
                                }

                                if (strtoupper($column) != 'PASSWORD' && strtoupper($column) != 'USERNAME')
                                    $fields .= $column . ',';
                                if (strtoupper($column) == 'PASSWORD' || strtoupper($column) == 'USERNAME')
                                    $log_fields .= $column . ',';
                                if ($column == 'PASSWORD') {

                                    if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                        $password = str_replace("'", "''", (trim(str_replace("\'", "'", $value))));
                                        $new_password = GenerateNewHash($password);
                                        $log_values .= "'" . $new_password . "',";
                                    } else {
                                        $password = str_replace("'", "''", (trim(str_replace("\'", "'", $value))));
                                        $new_password = GenerateNewHash($password);
                                        $log_values .= "'" . $new_password . "',";
                                    }
                                }
                                if (strtoupper($column) == 'USERNAME')
                                    $log_values .= "'" . str_replace("'", "''", str_replace("\'", "'", $value)) . "',";
                                else {
                                    if ($column != 'PASSWORD') {
                                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                            $values .= "'" . str_replace("'", "''", str_replace("\'", "'", trim($value))) . "',";
                                        } else
                                            $values .= "'" . str_replace("'", "''", str_replace("\'", "'", trim($value))) . "',";
                                    }
                                }
                            }
                            if ($column == 'FIRST_NAME' || $column == 'LAST_NAME') {
                                if ($value == '')
                                    $error = true;
                            }
                        }
                        $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                        // $log_sql .= '(' . substr($log_fields, 0, -1) . ') values(' . substr($log_values, 0, -1) . ')';
                        $log_sql .= '(' . substr($log_fields, 0, -1) . ') ';
                        $un = $_REQUEST['students']['USERNAME'];
                        $pass = md5($_REQUEST['students']['PASSWORD']);


                        if ($un != '') {
                            $un_chk = "SELECT COUNT(*) as TOTAL FROM login_authentication WHERE username = '$un'";
                            $res_chk = DBGet(DBQuery($un_chk));
                        }
                        if ($res_chk[1]['TOTAL'] > 0) {
                            $un_chl_res = 'exist';
                        }

                        if ($res_chk_pass[1]['TOTAL'] > 0) {
                            $pass_chl_res = 'exist';
                        }
                        if (!$error) {

                            if ($un_chl_res != 'exist' && $pass_chl_res != 'exist' && $day_valid != false) {
                                DBQuery($sql);
                                $last_student_id = mysqli_insert_id($connection);

                                if ($_FILES['file']['name']) {

                                    $fileName = $_FILES['file']['name'];
                                    $tmpName = $_FILES['file']['tmp_name'];
                                    $fileSize = $_FILES['file']['size'];
                                    $fileType = $_FILES['file']['type'];
                                    $upload = new upload();
                                    $upload->name = $_FILES["file"]["name"];
                                    $upload->setFileExtension();
                                    $upload->fileExtension;
                                    $upload->validateImage();
                                    if ($upload->wrongFormat == 1) {
                                        $_FILES["file"]["error"] = 1;
                                    }

                                    if ($_FILES["file"]["error"] > 0) {
                                        echo _cannotUploadFile;
                                    } else {
                                        $content = base64_decode($_REQUEST['imgblob']);
                                        $content = addslashes($content);
                                        $fileName = addslashes($fileName);
                                        DBQuery('INSERT INTO user_file_upload (USER_ID,PROFILE_ID,SCHOOL_ID,SYEAR,NAME, SIZE, TYPE, CONTENT,FILE_INFO) VALUES (' . $last_student_id . ',\'3\',' . UserSchool() . ',' . UserSyear() . ',\'' . $fileName . '\', \'' . $fileSize . '\', \'' . $fileType . '\', \'' . $content . '\',\'stuimg\')');
                                        PopTable('footer');
                                    }
                                }

                                $stu_id_length = strlen($student_id);
                                $log_sql .= 'values(' . substr(substr_replace($log_values, $last_student_id, 2, $stu_id_length), 0, -1) . ')';
                                DBQuery($log_sql);
                                $max_stId = DBGet(DBQuery('SELECT MAX(STUDENT_ID) AS STU_ID FROM students'));

                                DBQuery('INSERT INTO medical_info (STUDENT_ID,SYEAR,SCHOOL_ID) VALUES (' . $max_stId[1]['STU_ID'] . ',' . UserSyear() . ',' . UserSchool() . ')');
                                $_SESSION['total_stu'] = $_SESSION['total_stu'] + 1;
                                echo "<script>window.location.href='Modules.php?modname=students/Student.php&include=EnrollmentInfoInc&category_id=6'</script>";
                            }
                        } elseif ($error == true) {
                            $error_new_student = true;

                            echo '<p style=color:red>Invalid student Name</p>';
                        }
                        // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'student_enrollment'"));
                        // $studentemrollment_id[1]['STUDENTENROLL_ID'] = $id[1]['AUTO_INCREMENT'];
                        // $studentemrollment_id = $studentemrollment_id[1]['STUDENTENROLL_ID'];


                        $sql = "INSERT INTO student_enrollment ";
                        $fields = 'STUDENT_ID,SYEAR,SCHOOL_ID,';
                        $values = "'$last_student_id','" . UserSyear() . "','" . UserSchool() . "',";

                        if ($_REQUEST['day_values'])
                            $_REQUEST['values']['student_enrollment']['new']['START_DATE'] = $_REQUEST['day_values']['student_enrollment']['new']['START_DATE'] . '-' . $_REQUEST['month_values']['student_enrollment']['new']['START_DATE'] . '-' . $_REQUEST['year_values']['student_enrollment']['new']['START_DATE'];
                        else
                            $_REQUEST['values']['student_enrollment']['new']['START_DATE'] = '';


                        ##### Student Enrollment modifications - starts #####
                        if (isset($_REQUEST['values']['student_enrollment']['new']['START_DATE']) && $_REQUEST['values']['student_enrollment']['new']['START_DATE'] == '') {
                            $this_school_dates = DBGet(DBQuery("SELECT `start_date` FROM `school_years` WHERE `syear` = '" . UserSyear() . "' AND `school_id` = '" . UserSchool() . "'"));

                            if ($this_school_dates[1]['START_DATE'] > date('Y-m-d')) {
                                $_REQUEST['values']['student_enrollment']['new']['START_DATE'] = date('d-m-Y', strtotime($this_school_dates[1]['START_DATE']));
                            } else {
                                $_REQUEST['values']['student_enrollment']['new']['START_DATE'] = date('d-m-Y');
                            }
                        }
                        ##### Student Enrollment modifications - ends #####

                        foreach ($_REQUEST['values']['student_enrollment']['new'] as $column => $value) {
                            if ($value) {
                                $value = paramlib_validation($column, $value);

                                if ($column == 'START_DATE' || $column == 'END_DATE') {
                                    if (VerifyDate($value))
                                        $values .= "'" . date('Y-m-d', strtotime($value)) . "',";
                                    else {
                                        $err = _invalidEnrollmentDateCouldNotBeSaved;
                                        continue;
                                    }
                                } else
                                    $values .= "'" . str_replace("\'", "''", str_replace('&#39;', "''", $value)) . "',";
                                $fields .= $column . ',';
                            }
                        }

                        $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                        if (!$error) {
                            if ($un_chl_res != 'exist' && $pass_chl_res != 'exist' && $day_valid != false) {

                                DBQuery($sql);
                            }
                        }



                        if ($required_faild_error == true || $type_faild_error == true) {
                            $_REQUEST['student_id'] = 'new';
                            unset($value);
                        }
                        if ($openSISModules['Food_Service']) {
                        }
                        if (!$error_new_student) {
                            if ($un_chl_res != 'exist' && $pass_chl_res != 'exist') {
                                // $_SESSION['student_id'] = $_REQUEST['student_id'] = $student_id;
                                $_SESSION['student_id'] = $_REQUEST['student_id'] = $last_student_id;
                            } else {
                                $_REQUEST['student_id'] = "new";
                                unset($value);
                                if ($un_chl_res == 'exist' && $pass_chl_res != 'exist')
                                    echo "<font color=red><b>" . _userNameAlreadyExistPleaseTryWithADifferentUserName . "</b></font>";
                                if ($un_chl_res != 'exist' && $pass_chl_res == 'exist')
                                    echo "<font color=red><b>" . _passwordAlreadyExistPleaseTryWithADifferentPassword . "</b></font>";
                                if ($un_chl_res == 'exist' && $pass_chl_res == 'exist')
                                    echo "<font color=red><b>" . _usernameAndPasswordAlreadyExistPleaseTryWithADifferentUserNameAndPassword . "</b></font>";
                            }
                        }
                        $new_student = true;
                    }
                }

                if ($_REQUEST['values'] && $_REQUEST['include'] == 'MedicalInc')
                    SaveData(array('student_medical_notes' => "ID='__ID__'", 'student_medical_alerts' => "ID='__ID__'", 'student_immunization' => "ID='__ID__'", 'student_medical_visits' => "ID='__ID__'", 'fields' => array('student_medical_notes' => 'STUDENT_ID,', 'student_immunization' => 'STUDENT_ID,', 'student_medical_alerts' => 'STUDENT_ID,', 'student_medical_visits' => 'STUDENT_ID,'), 'values' => array('student_medical_notes' => "'" . UserStudentID() . "',", 'student_immunization' => "'" . UserStudentID() . "',", 'student_medical_alerts' => "'" . UserStudentID() . "',", 'student_medical_visits' => "'" . UserStudentID() . "',")));
                if ($_REQUEST['values'] && $_REQUEST['include'] == 'CommentsInc')
                    SaveData(array('student_mp_comments' => "ID='__ID__'", 'fields' => array('student_mp_comments' => 'STUDENT_ID,SYEAR,MARKING_PERIOD_ID,STAFF_ID,'), 'values' => array('student_mp_comments' => "'" . UserStudentID() . "','" . UserSyear() . "','" . UserMP() . "','" . User('STAFF_ID') . "',")));

                if ($_REQUEST['include'] != 'GeneralInfoInc' && $_REQUEST['include'] != 'AddressInc' && $_REQUEST['include'] != 'MedicalInc' && $_REQUEST['include'] != 'GoalInc' && $_REQUEST['include'] != 'OtherInfoInc' && $_REQUEST['include'] != 'EnrollmentInfoInc' && $_REQUEST['include'] != 'FilesInc')
                    if (!strpos($_REQUEST['include'], '/'))
                        include('modules/students/includes/' . $_REQUEST['include'] . '.php');
                    else
                        include('modules/' . $_REQUEST['include'] . '.php');

                unset($_REQUEST['modfunc']);
                // SHOULD THIS BE HERE???
                if (!UserStudentID())
                    unset($_REQUEST['values']);
                unset($_SESSION['_REQUEST_vars']['modfunc']);
                unset($_SESSION['_REQUEST_vars']['values']);
            }
        }
        if ($flag != true) {
            if ($err)
                echo '<center><font color=red><b>' . $err . '</b></font></center>';
            if ($_REQUEST['student_id'] == 'new')
                DrawBC('' . _students . ' > ' . _addAStudent . '');
            else
                DrawBC("" . _students . " > " . ProgramTitle());

            Search('student_id_from_student');

            $select = ''; //Initializing this variable before concat

            if ($_REQUEST['stuid']) {
                $select .= " AND ssm.STUDENT_ID = '" . str_replace("'", "''", $_REQUEST['stuid']) . "' ";
            }
            if ($_REQUEST['altid']) {
                $select .= " AND s.ALT_ID = '" . str_replace("'", "''", $_REQUEST['altid']) . "' ";
            }
            if ($_REQUEST['last']) {
                $select .= " AND LOWER(s.LAST_NAME) LIKE '" . addslashes(strtolower(trim($_REQUEST['last']))) . "%' ";
            }
            if ($_REQUEST['first']) {
                $select .= " AND LOWER(s.FIRST_NAME) LIKE '" . addslashes(strtolower(trim($_REQUEST['first']))) . "%' ";
            }
            if ($_REQUEST['grade']) {
                $select .= " AND ssm.GRADE_ID IN(SELECT id FROM school_gradelevels WHERE id= '" . str_replace("'", "''", $_REQUEST['grade']) . "') ";
            }
            if ($_REQUEST['addr']) {
                $select .= " AND (LOWER(a.STREET_ADDRESS_1) LIKE '%" . str_replace("'", "''", strtolower(trim($_REQUEST['addr']))) . "%' OR LOWER(a.CITY) LIKE '" . str_replace("'", "''", strtolower(trim($_REQUEST['addr']))) . "%' OR LOWER(a.STATE)='" . str_replace("'", "''", strtolower(trim($_REQUEST['addr']))) . "' OR ZIPCODE LIKE '" . trim(str_replace("'", "''", $_REQUEST['addr'])) . "%')";
            }


            if ($_REQUEST['mp_comment']) {
                $select .= " AND LOWER(smc.COMMENT) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['mp_comment'])) . "%' AND s.STUDENT_ID=smc.STUDENT_ID ";
            }
            if ($_REQUEST['goal_title']) {
                $select .= " AND LOWER(g.GOAL_TITLE) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['goal_title'])) . "%' AND s.STUDENT_ID=g.STUDENT_ID ";
            }
            if ($_REQUEST['goal_description']) {
                $select .= " AND LOWER(g.GOAL_DESCRIPTION) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['goal_description'])) . "%' AND s.STUDENT_ID=g.STUDENT_ID ";
            }
            if ($_REQUEST['progress_name']) {
                $select .= " AND LOWER(p.PROGRESS_NAME) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['progress_name'])) . "%' AND s.STUDENT_ID=p.STUDENT_ID ";
            }
            if ($_REQUEST['progress_description']) {
                $select .= " AND LOWER(p.PROGRESS_DESCRIPTION) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['progress_description'])) . "%' AND s.STUDENT_ID=p.STUDENT_ID ";
            }
            if ($_REQUEST['doctors_note_comments']) {
                $select .= " AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['doctors_note_comments'])) . "%' AND s.STUDENT_ID=smn.STUDENT_ID ";
            }
            if ($_REQUEST['type']) {
                $select .= " AND LOWER(sm.TYPE) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['type'])) . "%' AND s.STUDENT_ID=sm.STUDENT_ID ";
            }
            if ($_REQUEST['imm_comments']) {
                $select .= " AND LOWER(sm.COMMENTS) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['imm_comments'])) . "%' AND s.STUDENT_ID=sm.STUDENT_ID ";
            }
            if ($_REQUEST['imm_day'] && $_REQUEST['imm_month'] && $_REQUEST['imm_year']) {
                $imm_date = $_REQUEST['imm_year'] . '-' . $_REQUEST['imm_month'] . '-' . $_REQUEST['imm_day'];
                $select .= " AND sm.MEDICAL_DATE ='" . date('Y-m-d', strtotime($imm_date)) . "' AND s.STUDENT_ID=sm.STUDENT_ID ";
            } elseif ($_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
                if ($_REQUEST['imm_day']) {
                    $select .= " AND SUBSTR(sm.MEDICAL_DATE,9,2) ='" . $_REQUEST['imm_day'] . "' AND s.STUDENT_ID=sm.STUDENT_ID ";
                    $imm_date .= " Day :" . $_REQUEST['imm_day'];
                }
                if ($_REQUEST['imm_month']) {
                    $select .= " AND SUBSTR(sm.MEDICAL_DATE,6,2) ='" . $_REQUEST['imm_month'] . "' AND s.STUDENT_ID=sm.STUDENT_ID ";
                    $imm_date .= " Month :" . $_REQUEST['imm_month'];
                }
                if ($_REQUEST['imm_year']) {
                    $select .= " AND SUBSTR(sm.MEDICAL_DATE,1,4) ='" . $_REQUEST['imm_year'] . "' AND s.STUDENT_ID=sm.STUDENT_ID ";
                    $imm_date .= " Year :" . $_REQUEST['imm_year'];
                }
            }
            if ($_REQUEST['med_day'] && $_REQUEST['med_month'] && $_REQUEST['med_year']) {
                $med_date = $_REQUEST['med_year'] . '-' . $_REQUEST['med_month'] . '-' . $_REQUEST['med_day'];
                $select .= " AND smn.DOCTORS_NOTE_DATE ='" . date('Y-m-d', strtotime($med_date)) . "' AND s.STUDENT_ID=smn.STUDENT_ID ";
            } elseif ($_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
                if ($_REQUEST['med_day']) {
                    $select .= " AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) ='" . $_REQUEST['med_day'] . "' AND s.STUDENT_ID=smn.STUDENT_ID ";
                    $med_date .= " Day :" . $_REQUEST['med_day'];
                }
                if ($_REQUEST['med_month']) {
                    $select .= " AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) ='" . $_REQUEST['med_month'] . "' AND s.STUDENT_ID=smn.STUDENT_ID ";
                    $med_date .= " Month :" . $_REQUEST['med_month'];
                }
                if ($_REQUEST['med_year']) {
                    $select .= " AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) ='" . $_REQUEST['med_year'] . "' AND s.STUDENT_ID=smn.STUDENT_ID ";
                    $med_date .= " Year :" . $_REQUEST['med_year'];
                }
            }
            if ($_REQUEST['ma_day'] && $_REQUEST['ma_month'] && $_REQUEST['ma_year']) {
                $ma_date = $_REQUEST['ma_year'] . '-' . $_REQUEST['ma_month'] . '-' . $_REQUEST['ma_day'];
                $select .= " AND sma.ALERT_DATE ='" . date('Y-m-d', strtotime($ma_date)) . "' AND s.STUDENT_ID=sma.STUDENT_ID ";
            } elseif ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
                if ($_REQUEST['ma_day']) {
                    $select .= " AND SUBSTR(sma.ALERT_DATE,9,2) ='" . $_REQUEST['ma_day'] . "' AND s.STUDENT_ID=sma.STUDENT_ID ";
                    $ma_date .= " Day :" . $_REQUEST['ma_day'];
                }
                if ($_REQUEST['ma_month']) {
                    $select .= " AND SUBSTR(sma.ALERT_DATE,6,2) ='" . $_REQUEST['ma_month'] . "' AND s.STUDENT_ID=sma.STUDENT_ID ";
                    $ma_date .= " Month :" . $_REQUEST['ma_month'];
                }
                if ($_REQUEST['ma_year']) {
                    $select .= " AND SUBSTR(sma.ALERT_DATE,1,4) ='" . $_REQUEST['ma_year'] . "' AND s.STUDENT_ID=sma.STUDENT_ID ";
                    $ma_date .= " Year :" . $_REQUEST['ma_year'];
                }
            }
            if ($_REQUEST['nv_day'] && $_REQUEST['nv_month'] && $_REQUEST['nv_year']) {
                $nv_date = $_REQUEST['nv_year'] . '-' . $_REQUEST['nv_month'] . '-' . $_REQUEST['nv_day'];
                $select .= " AND smv.SCHOOL_DATE ='" . date('Y-m-d', strtotime($nv_date)) . "' AND s.STUDENT_ID=smv.STUDENT_ID ";
            } elseif ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
                if ($_REQUEST['nv_day']) {
                    $select .= " AND SUBSTR(smv.SCHOOL_DATE,9,2) ='" . $_REQUEST['nv_day'] . "' AND s.STUDENT_ID=smv.STUDENT_ID ";
                    $nv_date .= " Day :" . $_REQUEST['nv_day'];
                }
                if ($_REQUEST['nv_month']) {
                    $select .= " AND SUBSTR(smv.SCHOOL_DATE,6,2) ='" . $_REQUEST['nv_month'] . "' AND s.STUDENT_ID=smv.STUDENT_ID ";
                    $nv_date .= " Month :" . $_REQUEST['nv_month'];
                }
                if ($_REQUEST['nv_year']) {
                    $select .= " AND SUBSTR(smv.SCHOOL_DATE,1,4) ='" . $_REQUEST['nv_year'] . "' AND s.STUDENT_ID=smv.STUDENT_ID ";
                    $nv_date .= " Year :" . $_REQUEST['nv_year'];
                }
            }


            if ($_REQUEST['med_alrt_title']) {
                $select .= " AND LOWER(sma.TITLE) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['med_alrt_title'])) . "%' AND s.STUDENT_ID=sma.STUDENT_ID ";
            }
            if ($_REQUEST['reason']) {
                $select .= " AND LOWER(smv.REASON) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['reason'])) . "%' AND s.STUDENT_ID=smv.STUDENT_ID ";
            }
            if ($_REQUEST['result']) {
                $select .= " AND LOWER(smv.RESULT) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['result'])) . "%' AND s.STUDENT_ID=smv.STUDENT_ID ";
            }
            if ($_REQUEST['med_vist_comments']) {
                $select .= " AND LOWER(smv.COMMENTS) LIKE '" . str_replace("'", "''", strtolower($_REQUEST['med_vist_comments'])) . "%' AND s.STUDENT_ID=smv.STUDENT_ID ";
            }
            if ($_REQUEST['day_to_birthdate'] && $_REQUEST['month_to_birthdate'] && $_REQUEST['day_from_birthdate'] && $_REQUEST['month_from_birthdate']) {
                $date_to = $_REQUEST['month_to_birthdate'] . '-' . $_REQUEST['day_to_birthdate'];
                $date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];
                $select .= " AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN " . $_REQUEST['month_from_birthdate'] . " AND " . $_REQUEST['month_to_birthdate'] . ") ";
                $select .= " AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN " . $_REQUEST['day_from_birthdate'] . " AND " . $_REQUEST['day_to_birthdate'] . ") ";
            }

            if (User('PROFILE') == 'admin') {
                $admin_COMMON_FROM = " FROM students s, student_address a,student_enrollment ssm ";
                if ($_REQUEST['_search_all_schools'] == 'Y' || $_SESSION['_search_all'] == 1) {

                    $admin_COMMON_WHERE = " WHERE s.STUDENT_ID=ssm.STUDENT_ID  AND a.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID IN (" . GetUserSchools(UserID(), true) . ") ";
                    $_SESSION['_search_all'] = 1;
                } else {
                    $admin_COMMON_WHERE = " WHERE s.STUDENT_ID=ssm.STUDENT_ID  AND a.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID=" . UserSchool() . " ";
                }
                if ($_REQUEST['mp_comment'] || $_SESSION['smc']) {
                    $admin_COMMON_FROM .= " ,student_mp_comments smc";
                    $admin_COMMON_WHERE .= " AND smc.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smc'] = '1';
                }
                if ($_REQUEST['goal_description'] || $_REQUEST['goal_title'] || $_SESSION['g']) {
                    $admin_COMMON_FROM .= " ,student_goal g ";
                    $admin_COMMON_WHERE .= " AND g.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['g'] = '1';
                }
                if ($_REQUEST['progress_name'] || $_REQUEST['progress_description'] || $_SESSION['p']) {
                    $admin_COMMON_FROM .= " ,student_goal_progress p ";
                    $admin_COMMON_WHERE .= " AND p.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['p'] = '1';
                }
                if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year'] || $_SESSION['smn']) {
                    $admin_COMMON_FROM .= " ,student_medical_notes smn ";
                    $admin_COMMON_WHERE .= " AND smn.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smn'] = '1';
                }
                if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year'] || $_SESSION['sm']) {
                    $admin_COMMON_FROM .= " ,student_immunization sm ";
                    $admin_COMMON_WHERE .= " AND sm.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['sm'] = '1';
                }
                if ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year'] || $_REQUEST['med_alrt_title'] || $_SESSION['sma']) {
                    $admin_COMMON_FROM .= " ,student_medical_alerts sma  ";
                    $admin_COMMON_WHERE .= " AND sma.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['sma'] = '1';
                }
                if ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year'] || $_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_SESSION['smv']) {
                    $admin_COMMON_FROM .= " ,student_medical_visits smv   ";
                    $admin_COMMON_WHERE .= " AND smv.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smv'] = '1';
                }
                $admin_COMMON = $admin_COMMON_FROM . $admin_COMMON_WHERE;
            }
            /////////////////////////////////// Teacher section ///////////////////////////////////
            if (User('PROFILE') == 'teacher') {
                $teacher_COMMON_FROM = " FROM students s, student_enrollment ssm, course_periods cp,
	schedule ss,student_address a ";
                $teacher_COMMON_WHERE = " WHERE a.STUDENT_ID=s.STUDENT_ID AND a.TYPE='Home Address' AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.MARKING_PERIOD_ID IN (" . GetAllMP('', $queryMP) . ")
						AND (cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "' AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID=" . UserSchool() . " ";

                if ($_REQUEST['_search_all_schools'] == 'Y' || $_SESSION['_search_all'] == 1) {
                    $teacher_COMMON_WHERE = " WHERE a.STUDENT_ID=s.STUDENT_ID AND a.TYPE='Home Address'  AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.MARKING_PERIOD_ID IN (" . GetAllMP('', $queryMP) . ")
						AND (cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "' AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID IN (" . GetUserSchools(UserID(), true) . ") ";
                    $_SESSION['_search_all'] = 1;
                } else {
                    $teacher_COMMON_WHERE = " WHERE a.STUDENT_ID=s.STUDENT_ID AND a.TYPE='Home Address' AND s.STUDENT_ID=ssm.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.MARKING_PERIOD_ID IN (" . GetAllMP('', $queryMP) . ")
						AND (cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "') AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "' AND ssm.SYEAR=" . UserSyear() . " AND ssm.SCHOOL_ID=" . UserSchool() . " ";
                }
                if ($_REQUEST['mp_comment'] || $_SESSION['smc']) {
                    $teacher_COMMON_FROM .= " ,student_mp_comments smc";
                    $teacher_COMMON_WHERE .= " AND smc.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smc'] = '1';
                }
                if ($_REQUEST['goal_description'] || $_REQUEST['goal_title'] || $_SESSION['g']) {
                    $teacher_COMMON_FROM .= " ,student_goal g ";
                    $teacher_COMMON_WHERE .= " AND g.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['g'] = '1';
                }
                if ($_REQUEST['progress_name'] || $_REQUEST['progress_description'] || $_SESSION['p']) {
                    $teacher_COMMON_FROM .= " ,student_goal_progress p ";
                    $teacher_COMMON_WHERE .= " AND p.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['p'] = '1';
                }
                if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year'] || $_SESSION['smn']) {
                    $teacher_COMMON_FROM .= " ,student_medical_notes smn ";
                    $teacher_COMMON_WHERE .= " AND smn.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smn'] = '1';
                }
                if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year'] || $_SESSION['sm']) {
                    $teacher_COMMON_FROM .= " ,student_immunization sm ";
                    $teacher_COMMON_WHERE .= " AND sm.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['sm'] = '1';
                }
                if ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year'] || $_REQUEST['med_alrt_title'] || $_SESSION['sma']) {
                    $teacher_COMMON_FROM .= " ,student_medical_alerts sma  ";
                    $teacher_COMMON_WHERE .= " AND sma.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['sma'] = '1';
                }
                if ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year'] || $_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_SESSION['smv']) {
                    $teacher_COMMON_FROM .= " ,student_medical_visits smv   ";
                    $teacher_COMMON_WHERE .= " AND smv.STUDENT_ID=s.STUDENT_ID ";
                    $_SESSION['smv'] = '1';
                }
                $teacher_COMMON = $teacher_COMMON_FROM . $teacher_COMMON_WHERE;
            }

            ////////////////////////////////// End Of Teacher Section /////////////////////////////





            if (!UserStudentID()) {
                if (User('PROFILE') == 'admin') {
                    $sql = "SELECT COUNT(s.STUDENT_ID) AS STUDENT_ID " . $admin_COMMON_FROM . $admin_COMMON_WHERE . $select;
                } elseif (User('PROFILE') == 'teacher') {
                    $sql = "SELECT COUNT(s.STUDENT_ID) AS STUDENT_ID " . $teacher_COMMON_FROM . $teacher_COMMON_WHERE . $select;
                }

                $val = DBGet(DBQuery($sql));

                if ($val[1]['STUDENT_ID'] > 1 && !$_SESSION['stu_search']['sql']) {
                    unset($_SESSION['s']);
                    unset($_SESSION['custom_count_sql']);
                    unset($_SESSION['inactive_stu_filter']);
                }
            }
            if (!$_SESSION['s']) {
                $_SESSION['s'] = $select;
            }

            if ($_SESSION['inactive_stu_filter']) {
                if (User('PROFILE') == 'admin') {
                    $get_rollover_id = DBGet(DBQuery('SELECT ID FROM student_enrollment_codes WHERE SYEAR=' . UserSyear() . ' AND TYPE=\'Roll\' '));
                    $get_rollover_id = $get_rollover_id[1]['ID'];
                    $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)  OR ssm.DROP_CODE=\'' . $get_rollover_id . '\' ) ';
                }
                $_SESSION['s'] .= $_SESSION['inactive_stu_filter'];
            }

            if ($_REQUEST['v'] && isset($_REQUEST['student_id'])) {



                $val = optional_param('v', 0, PARAM_INT);
                if ($val == 1) {
                    unset($_SESSION['student_id']);
                    $_SESSION['student_id'] = $_SESSION['students_order'][1];
                }
                if ($val == 2) {
                    $final_pos = array_search($_SESSION['student_id'], $_SESSION['students_order']);
                    $final_pos = $final_pos - 1;
                    unset($_SESSION['student_id']);
                    $_SESSION['student_id'] = $_SESSION['students_order'][$final_pos];
                }
                if ($val == 3) {
                    $final_pos = array_search($_SESSION['student_id'], $_SESSION['students_order']);
                    $final_pos = $final_pos + 1;
                    unset($_SESSION['student_id']);
                    $_SESSION['student_id'] = $_SESSION['students_order'][$final_pos];
                }
                if ($val == 4) {
                    unset($_SESSION['student_id']);
                    $final_pos = count($_SESSION['students_order']);
                    $_SESSION['student_id'] = $_SESSION['students_order'][$final_pos];
                }
            }


            if (UserStudentID() || $_REQUEST['student_id'] == 'new') {
                if ($_REQUEST['student_id'] != 'new') {
                    if (User('PROFILE') == 'admin') {
                        $s_ln = DBGet(DBQuery("SELECT LAST_NAME,FIRST_NAME,s.STUDENT_ID " . $admin_COMMON . " AND s.STUDENT_ID =" . UserStudentID() . "  " . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));

                        $ln = $s_ln[1]['LAST_NAME'] . $s_ln[1]['FIRST_NAME'] . $s_ln[1]['STUDENT_ID'];
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $ln = str_replace("'", "\'", $ln);
                        } else {
                            $ln = str_replace("'", "\'", $ln);
                        }

                        $s1_id = DBGet(DBQuery("SELECT s.STUDENT_ID " . $admin_COMMON . $_SESSION['s'] . " " . $_SESSION['custom_count_sql'] . " ORDER BY CONCAT(s.LAST_NAME, s.FIRST_NAME,s.STUDENT_ID) ASC LIMIT 1"));
                        $s2_id = DBGet(DBQuery("SELECT s.STUDENT_ID " . $admin_COMMON . $_SESSION['s'] . " " . $_SESSION['custom_count_sql'] . " ORDER BY CONCAT(s.LAST_NAME, s.FIRST_NAME,s.STUDENT_ID) DESC LIMIT 1"));
                        $count_STU = DBGet(DBQuery("SELECT COUNT(LAST_NAME) AS STUDENT " . $admin_COMMON . " AND CONCAT(LAST_NAME,FIRST_NAME,s.STUDENT_ID)<'" . $ln . "' AND LAST_NAME LIKE '" . strtolower($_REQUEST['last']) . "%'" . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));
                        $count = $count_STU[1]['STUDENT'] + 1;
                        $total = DBGet(DBQuery("SELECT COUNT(s.STUDENT_ID) AS STUDENT_ID " . $admin_COMMON . " " . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));
                    } elseif (User('PROFILE') == 'teacher') {

                        $s_ln = DBGet(DBQuery("SELECT LAST_NAME,FIRST_NAME,s.STUDENT_ID " . $teacher_COMMON . " AND s.STUDENT_ID ='" . UserStudentID() . "'  " . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));

                        $ln = $s_ln[1]['LAST_NAME'] . $s_ln[1]['FIRST_NAME'] . $s_ln[1]['STUDENT_ID'];
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $ln = str_replace("'", "\'", $ln);
                        } else {
                            $ln = str_replace("'", "\'", $ln);
                        }

                        $s1_id = DBGet(DBQuery("SELECT s.STUDENT_ID " . $teacher_COMMON . $_SESSION['s'] . " " . $_SESSION['custom_count_sql'] . " ORDER BY CONCAT(s.LAST_NAME, s.FIRST_NAME,s.STUDENT_ID) ASC LIMIT 1"));

                        $s2_id = DBGet(DBQuery("SELECT s.STUDENT_ID " . $teacher_COMMON . $_SESSION['s'] . " " . $_SESSION['custom_count_sql'] . " ORDER BY CONCAT(s.LAST_NAME, s.FIRST_NAME,s.STUDENT_ID) DESC LIMIT 1"));

                        $count_STU = DBGet(DBQuery("SELECT COUNT(LAST_NAME) AS STUDENT " . $teacher_COMMON . " AND CONCAT(LAST_NAME,FIRST_NAME,s.STUDENT_ID)<'" . $ln . "' AND LAST_NAME LIKE '" . strtolower($_REQUEST['last']) . "%'" . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));
                        $count = $count_STU[1]['STUDENT'] + 1;

                        $total = DBGet(DBQuery("SELECT COUNT(s.STUDENT_ID) AS STUDENT_ID " . $teacher_COMMON . " " . $_SESSION['s'] . " " . $_SESSION['custom_count_sql']));
                    }

                    if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') {
                        $val = $_REQUEST['v'];

                        if (is_array($_SESSION['students_order'])) {
                            $count = array_search($_SESSION['student_id'], $_SESSION['students_order']);
                        }

                        $_SESSION['count'] = $count;
                        $_SESSION['total_stu'] = (is_countable($_SESSION['students_order'])) ? count($_SESSION['students_order']) : 0;
                        //$_SESSION['total_stu'] = count($_SESSION['students_order']);
                        $last_stu = (is_countable($_SESSION['students_order'])) ? count($_SESSION['students_order']) : 0;
                        //$last_stu = count($_SESSION['students_order']);
                        $last_stu = $_SESSION['students_order'][$last_stu];

                        echo '<div class="row">';
                        echo '<div class="col-md-12 text-right">';
                        echo "<p>" . _showing . " " . (is_countable($_SESSION['students_order']) && count($_SESSION['students_order']) > 1 ? $_SESSION['count'] : '1') . " " . _of . " " . (is_countable($_SESSION['students_order']) && count($_SESSION['students_order']) > 1 ? $_SESSION['total_stu'] : '1') . " &nbsp; ";

                        if (is_countable($_SESSION['students_order']) && count($_SESSION['students_order']) > 1) {
                            if (UserStudentID() != $_SESSION['students_order'][1]) {
                                echo "<span class='pg-prev' style='margin-right:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=students/Student.php&v=1&student_id=" . UserStudentID() . " ><i class=\"icon-first\"></i> " . _first . "</A></span>";

                                echo "<span class='pg-prev' style='margin-right:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=students/Student.php&v=2&student_id=" . UserStudentID() . " > <i class=\"icon-backward2\"></i> " . _previous . "</A></span>";
                            }
                            if (UserStudentID() != $last_stu) {

                                echo "<span class='pg-nxt' style='margin-left:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=students/Student.php&v=3&student_id=" . UserStudentID() . " >" . _next . " <i class=\"icon-forward3\"></i></A></span>";

                                echo "<span class='pg-nxt' style='margin-left:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=students/Student.php&v=4&student_id=" . UserStudentID() . " >" . _last . " <i class=\"icon-last\"></i></A></span>";
                            }
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                }

                if (clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) != 'delete' || $_REQUEST['delete_ok'] == '1') {

                    if ($_REQUEST['student_id'] != 'new') {

                        $sql = "SELECT s.STUDENT_ID,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,s.NAME_SUFFIX,la.USERNAME,la.PASSWORD,la.LAST_LOGIN,s.IS_DISABLE,s.ESTIMATED_GRAD_DATE,s.GENDER,s.ETHNICITY_ID,s.COMMON_NAME,s.BIRTHDATE,s.LANGUAGE_ID,s.ALT_ID,s.EMAIL,s.PHONE,(SELECT SCHOOL_ID FROM student_enrollment WHERE SYEAR='" . UserSyear() . "' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS SCHOOL_ID,
                        (SELECT GRADE_ID FROM student_enrollment WHERE SYEAR='" . UserSyear() . "' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS GRADE_ID,
                        (SELECT SECTION_ID FROM student_enrollment WHERE SYEAR='" . UserSyear() . "' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS SECTION_ID,
                        (SELECT ID FROM student_enrollment WHERE SYEAR='" . UserSyear() . "' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS ENROLLMENT_ID
                    FROM students s , login_authentication la
                    WHERE s.STUDENT_ID='" . UserStudentID() . "' AND s.STUDENT_ID=la.USER_ID AND la.PROFILE_ID=3";
                        $QI = DBQuery($sql);
                        $student = DBGet($QI);
                        $student = $student[1];

                        $stu_Medical_info = DBGet(DBQuery('SELECT PHYSICIAN,PHYSICIAN_PHONE,PREFERRED_HOSPITAL FROM medical_info WHERE STUDENT_ID=' . UserStudentID() . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . $student['SCHOOL_ID'] . ''));
                        if (count($stu_Medical_info) > 0)
                            $student += $stu_Medical_info[1];
                        $school = DBGet(DBQuery("SELECT SCHOOL_ID,GRADE_ID FROM student_enrollment WHERE STUDENT_ID='" . UserStudentID() . "' AND SYEAR='" . UserSyear() . "' AND ('" . DBDate() . "' BETWEEN START_DATE AND END_DATE OR END_DATE IS NULL)"));
                        $_REQUEST['modname'] = str_replace('?student_id=new', '', $_REQUEST['modname']);
                        echo "<FORM name=student class=\"form-horizontal\" enctype='multipart/form-data' action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&student_id=" . UserStudentID() . "&modfunc=update method=POST>";
                    } else
                        echo "<FORM id=student_isertion enctype='multipart/form-data' name=student id=frmstu action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=update method=POST>";

                    $name = $student['FIRST_NAME'] . ' ' . $student['MIDDLE_NAME'] . ' ' . $student['LAST_NAME'] . ' ' . $student['NAME_SUFFIX'];

                    if ($_REQUEST['student_id'] != 'new')
                        $name .= ' - ' . $student['STUDENT_ID'];

                    if (User('PROFILE') != 'student')
                        if (User('PROFILE_ID') != '')
                            $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
                        else {
                            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID') . "'"));
                            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                            if ($profile_id_mod != '')
                                $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
                        }
                    else
                        $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='3' AND CAN_USE='Y'"), array(), array('MODNAME'));
                    $categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM student_field_categories ORDER BY SORT_ORDER,TITLE"));

                    foreach ($categories_RET as $category) {
                        if ($can_use_RET['students/Student.php&category_id=' . $category['ID']]) {
                            if ($category['ID'] == '1')
                                $include = 'GeneralInfoInc';
                            elseif ($category['ID'] == '3')
                                $include = 'AddressInc';
                            elseif ($category['ID'] == '2')
                                $include = 'MedicalInc';
                            elseif ($category['ID'] == '4')
                                $include = 'CommentsInc';
                            elseif ($category['ID'] == '5')
                                $include = 'GoalInc';
                            elseif ($category['ID'] == '6')
                                $include = 'EnrollmentInfoInc';
                            elseif ($category['ID'] == '7')
                                $include = 'FilesInc';
                            elseif ($category['INCLUDE'])
                                $include = $category['INCLUDE'];
                            else
                                $include = 'OtherInfoInc';

                            switch ($category['TITLE']) {
                                case 'General Info':
                                    $categoryTitle = _generalInfo;
                                    break;
                                case 'Addresses & Contacts':
                                    $categoryTitle = _addressesContacts;
                                    break;
                                case 'Medical':
                                    $categoryTitle = _medical;
                                    break;
                                case 'Comments':
                                    $categoryTitle = _comments;
                                    break;
                                case 'Goals':
                                    $categoryTitle = _goals;
                                    break;
                                case 'Enrollment Info':
                                    $categoryTitle = _enrollmentInfo;
                                    break;
                                case 'Files':
                                    $categoryTitle = _files;
                                    break;
                                default:
                                    $categoryTitle = $category['TITLE'];
                                    break;
                                    // case 'Demographic Info':
                                    //     $categoryTitle = _demographicInfo;
                                    //     break;
                                    // case 'Addresses &amp; Contacts':
                                    //     $categoryTitle = _addressesContacts;
                                    //     break;
                                    // case 'School Information':
                                    //     $categoryTitle = _schoolInformation;
                                    //     break;
                                    // case 'Certification Information':
                                    //     $categoryTitle = _certificationInformation;
                                    //     break;
                                    // case 'Schedule':
                                    //     $categoryTitle = _schedule;
                                    //     break;
                            }

                            // print_r($category);
                            // echo "<br>";
                            // echo "<"
                            // echo "$categoryTitle<br>";

                            //$tabs[] = array('title' => $category['TITLE'], 'link' => "Modules.php?modname=students/Student.php&include=EnrollmentInfoInc&category_id=6");
                            $tabs[] = array('title' => $categoryTitle, 'link' => "Modules.php?modname=students/Student.php&include=$include&category_id=$category[ID]");
                        }
                    }
                    unset($new_tabs);
                    unset($ti);
                    unset($td);
                    $swap_tabs = 'n';
                    foreach ($tabs as $ti => $td) {
                        if ($td['title'] == _enrollmentInfo)
                            $swap_tabs = 'y';
                    }
                    if ($swap_tabs == 'y') {
                        foreach ($tabs as $ti => $td) {
                            if ($td['title'] == _generalInfo)
                                $new_tabs[0] = $td;
                            elseif ($td['title'] == _enrollmentInfo)
                                $new_tabs[1] = $td;
                            else
                                $new_tabs[$ti + 1] = $td;
                        }
                    }

                    if (count($new_tabs)) {
                        unset($tabs);
                        $tabs = $new_tabs;
                        ksort($tabs);
                    }


                    unset($new_tabs);
                    unset($ti);
                    unset($td);
                    $swap_tabs = 'n';
                    $_openSIS['selected_tab'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]";
                    if ($_REQUEST['category_id'])
                        $_openSIS['selected_tab'] .= '&category_id=' . $_REQUEST['category_id'];


                    //echo '</div>'; //force breaking non ended div

                    echo '<div class="panel panel-default">';
                    echo PopTable('header', $tabs, '');

                    if (!strpos($_REQUEST['include'], '/'))
                        include('modules/students/includes/' . $_REQUEST['include'] . '.php');
                    else {
                        include('modules/' . $_REQUEST['include'] . '.php');
                        //$separator = '<HR>';
                        include('modules/students/includes/OtherInfoInc.php');
                    }

                    if (isset($_REQUEST['goal_id']) && $_REQUEST['goal_id'] != 'new' && !isset($_REQUEST['progress_id']))
                        $buttons = SubmitButton(_save, '', 'id="mod_student_btn" class="btn btn-primary pull-right" onclick="formcheck_student_student(this);"');
                    else {
                        if ($_REQUEST['student_id'] != 'new') {

                            $student_id = explode(" - ", trim($name));
                            $student_id = $student_id[count($student_id) - 1];
                            $enrollment_info = DBGet(DBQuery('SELECT ENROLLMENT_CODE FROM student_enrollment WHERE STUDENT_ID=' . $student_id));
                            $enrollment_code = $enrollment_info[1]['ENROLLMENT_CODE'];
                            if ($_REQUEST['category_id'] == 1 && $enrollment_code == NULL)
                                $buttons = SubmitButton(_save, '', 'id="mod_student_btn" class="btn btn-primary" onclick="formcheck_student_student(this);"');
                            else
                                $buttons = SubmitButton(_save, '', 'id="mod_student_btn" class="btn btn-primary" onclick="formcheck_student_student(this);"');
                        } else {
                            if ($_REQUEST['category_id'] == 1)
                                $buttons = SubmitButton(_saveNext, '', 'id="mod_student_btn" class="btn btn-primary" onclick="formcheck_student_student(this);"');
                            else
                                $buttons = SubmitButton(_save, '', 'id="mod_student_btn" class="btn btn-primary" onclick="formcheck_student_student(this);"');
                        }
                    }
                    echo PopTable('footer', $buttons);
                    echo '</div>';
                    echo '</FORM>';
                } else
                if (!strpos($_REQUEST['include'], '/'))
                    include('modules/students/includes/' . $_REQUEST['include'] . '.php');
                else {

                    include('modules/' . $_REQUEST['include'] . '.php');
                    //$separator = '<div class=break></div>';
                    include('modules/students/includes/OtherInfoInc.php');
                }
            }
        }
    }
}


echo '<div id="modal_default_transferred_out" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo "<FORM class=m-b-0 name=student enctype='multipart/form-data' action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&student_id=" . UserStudentID() . "&modfunc=update method=POST>";
echo '<div id="modal-res"></div>';
echo '</FORM>';
echo '</div>';
echo '</div>';
echo '</div>';

function makeChooseCheckbox($value, $title)
{
    global $THIS_RET;
    if ($THIS_RET['BUTTON']) {
        return "<INPUT type=radio name=staff value=" . $THIS_RET['BUTTON'] . ">";
    }
}

