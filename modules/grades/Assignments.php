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
include_once("fckeditor/fckeditor.php");
if ($_REQUEST['assignment_type_id'] == 'new' && isset($_REQUEST['tables'])) {
    if (empty($_REQUEST['tables']['new']['TITLE'])) {
        echo '<div class="alert alert-danger">' . _titleCannotBeBlank . '</div>';
    }
}
if (isset($_REQUEST['tables']['new']) && $_REQUEST['tables']['new']['TITLE'] == '' && $_REQUEST['table'] == 'gradebook_assignment_types') {
    unset($_REQUEST);
    $_REQUEST['modname'] = 'grades/Assignments.php';
    $_REQUEST['assignment_type_id'] = 'new';
}
$course_period_id = UserCoursePeriod();
$course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
$course_id = $course_id[1]['COURSE_ID'];
$_openSIS['allow_edit'] = true;
unset($_SESSION['_REQUEST_vars']['assignment_type_id']);
unset($_SESSION['_REQUEST_vars']['assignment_id']);

$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND school_id=\'' . UserSchool() . '\' AND PROGRAM=\'Gradebook\' AND value like "%_' . UserCoursePeriod() . '%"'), array(), array('TITLE'));
if (count($config_RET)) {
    foreach ($config_RET as $title => $value)
        if (substr($title, 0, 3) == 'SEM' || substr($title, 0, 2) == 'FY' || substr($title, 0, 1) == 'Q') {
            $value1 = explode("_", $value[1]['VALUE']);
            $programconfig[$title] = $value1[0];
        } else {
            $value1 = explode("_" . UserCoursePeriod(), $value[1]['VALUE']);
            if (count($value1) > 1)
                $programconfig[$title] = $value1[0];
            else
                $programconfig[$title] = $value[1]['VALUE'];
        }
}
if (clean_param($_REQUEST['day_tables'], PARAM_NOTAGS) && ($_POST['day_tables'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['day_tables'] as $id => $values) {
        if ($_REQUEST['day_tables'][$id]['DUE_DATE'] && $_REQUEST['month_tables'][$id]['DUE_DATE'] && $_REQUEST['year_tables'][$id]['DUE_DATE'])
            $_REQUEST['tables'][$id]['DUE_DATE'] = date("Y-m-d", strtotime($_REQUEST['day_tables'][$id]['DUE_DATE'] . '-' . $_REQUEST['month_tables'][$id]['DUE_DATE'] . '-' . $_REQUEST['year_tables'][$id]['DUE_DATE']));
        if ($_REQUEST['day_tables'][$id]['ASSIGNED_DATE'] && $_REQUEST['month_tables'][$id]['ASSIGNED_DATE'] && $_REQUEST['year_tables'][$id]['ASSIGNED_DATE'])
            $_REQUEST['tables'][$id]['ASSIGNED_DATE'] = date("Y-m-d", strtotime($_REQUEST['day_tables'][$id]['ASSIGNED_DATE'] . '-' . $_REQUEST['month_tables'][$id]['ASSIGNED_DATE'] . '-' . $_REQUEST['year_tables'][$id]['ASSIGNED_DATE']));
    }
    $_POST['tables'] = $_REQUEST['tables'];
}
if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax'])) {
    $redirect_now = 'n';
    $table = trim($_REQUEST['table']);
    $err = false;
    $f = 0;

    foreach ($_REQUEST['tables'] as $id => $columns) {
        if ($table == 'gradebook_assignment_types' && substr($programconfig['WEIGHT'], 0, 1) == 'Y' && $columns['FINAL_GRADE_PERCENT'] != '')
            $columns['FINAL_GRADE_PERCENT'] = par_rep('/[^0-9.]/', '', clean_param($columns['FINAL_GRADE_PERCENT'], PARAM_PERCENT)) / 100;

        if ($id != 'new') {

            $get_custom_ass = DBGet(DBQuery('SELECT ASSIGNMENT_TYPE_ID FROM gradebook_assignments WHERE assignment_id =' . $id));

            if ($get_custom_ass[1]['ASSIGNMENT_TYPE_ID']) {
                $get_custom_cp = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM gradebook_assignment_types WHERE assignment_type_id=' . $get_custom_ass[1]['ASSIGNMENT_TYPE_ID']));

                $get_custom_date = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE,MARKING_PERIOD_ID FROM course_details WHERE COURSE_PERIOD_ID=\'' . $get_custom_cp[1]['COURSE_PERIOD_ID'] . '\' '));
                $cud_sd = strtotime($get_custom_date[1]['BEGIN_DATE']);
                $cud_ed = strtotime($get_custom_date[1]['END_DATE']);
            }
            if (trim($columns['TITLE']) != "" || !isset($columns['TITLE'])) {
                if ($columns['ASSIGNMENT_TYPE_ID'] && $columns['ASSIGNMENT_TYPE_ID'] != $_REQUEST['assignment_type_id'])
                    $_REQUEST['assignment_type_id'] = $columns['ASSIGNMENT_TYPE_ID'];

                $sql = 'UPDATE ' . $table . ' SET ';
                if (isset($_REQUEST['tables'][$id]['COURSE_ID']) && $_REQUEST['tables'][$id]['COURSE_ID'] == '' && $table == 'gradebook_assignments')
                    $columns['COURSE_ID'] = 'N';
                $err_ck = 0;
                foreach ($columns as $column => $value) {
                    if ($column == 'DUE_DATE' || $column == 'ASSIGNED_DATE') {

                        $due_date_sql = DBGet(DBQuery('SELECT ASSIGNED_DATE,DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\''));
                        if ($columns['DUE_DATE'] && $columns['ASSIGNED_DATE']) {
                            if ($get_custom_date[1]['MARKING_PERIOD_ID'] == NULL) {
                                if (strtotime($columns['DUE_DATE']) < $cud_sd || strtotime($columns['DUE_DATE']) > $cud_ed) {
                                    $msg = '<Font color=red> ' . _dueDateMustBeWithinTheCurrentMarkingPeriodsStartDateAndEndDate . '.</FONT>';
                                    $err = true;
                                    $err_ck = 1;
                                    break;
                                } else if (strtotime($columns['DUE_DATE']) < strtotime($due_date_sql[1]['ASSIGNED_DATE']) && $due_date_sql[1]['ASSIGNED_DATE'] != '') {
                                    $err = true;
                                    continue;
                                } elseif (strtotime($columns['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE'])) {
                                    $err = true;
                                    continue;
                                }
                            } else {
                                if (strtotime($columns['DUE_DATE']) < strtotime($due_date_sql[1]['ASSIGNED_DATE']) && $due_date_sql[1]['ASSIGNED_DATE'] != '') {
                                    $err = true;

                                    continue;
                                }

                                if (strtotime($columns['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE'])) {
                                    $err = true;
                                    continue;
                                }
                            }
                        }
                        if ($columns['DUE_DATE'] && $columns['ASSIGNED_DATE']) {


                            if ($get_custom_date[1]['MARKING_PERIOD_ID'] == NULL) {
                                if (strtotime($columns['ASSIGNED_DATE']) < $cud_sd || strtotime($columns['ASSIGNED_DATE']) > $cud_ed) {
                                    $msg = '<Font color=red> ' . _assignedDateMustBeWithinTheCurrentMarkingPeriodsStartDateAndEndDate . '.</FONT>';
                                    $err = true;
                                    $err_ck = 1;
                                    break;
                                } else  if (strtotime($due_date_sql[1]['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE']) && $due_date_sql[1]['DUE_DATE'] != '') {
                                    $err = true;
                                    continue;
                                } elseif (strtotime($columns['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE'])) {
                                    $err = true;
                                    continue;
                                }
                            } else {
                                if (strtotime($due_date_sql[1]['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE']) && $due_date_sql[1]['DUE_DATE'] != '') {
                                    $err = true;
                                    continue;
                                } elseif (strtotime($columns['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE'])) {
                                    $err = true;
                                    continue;
                                }
                            }
                        }
                    }

                    if ((($column == 'ASSIGNED_DATE' && $value != '' && $due_date_sql[1]['ASSIGNED_DATE'] != $value) || ($column == 'DUE_DATE' && $value != '' && $due_date_sql[1]['DUE_DATE'] != $value)) && $table == 'gradebook_assignments') {

                        // $grade_assign_qr = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS TOT FROM   student_report_card_grades WHERE  COURSE_PERIOD_ID='.$course_period_id.' and marking_period_id='.UserMP().''));
                        $grade_assign_qr = DBGet(DBQuery("SELECT COUNT(STUDENT_ID) AS TOT FROM   gradebook_grades WHERE ASSIGNMENT_ID = '" . $_REQUEST['assignment_id'] . "'"));
                        if ($grade_assign_qr[1]['TOT'] > 0) {
                            $f = 1;
                            ShowErrPhp('' . _cannotModifyTheEndDateOrStartDateBecauseGradeHasBeenAssignedToTheStudentForThisAssignment . '.');
                        }
                    }


                    if ($column == 'POINTS' && $value != '' && $table == 'gradebook_assignments') {

                        // $grade_assign_qr = DBGet(DBQuery("SELECT COUNT(STUDENT_ID) AS TOT FROM   student_report_card_grades WHERE COURSE_PERIOD_ID='$course_period_id'"));
                        $grade_assign_qr = DBGet(DBQuery("SELECT COUNT(STUDENT_ID) AS TOT FROM   gradebook_grades WHERE ASSIGNMENT_ID = '" . $_REQUEST['assignment_id'] . "'"));
                        if ($grade_assign_qr[1]['TOT'] > 0) {
                            $f = 1;
                            ShowErrPhp('' . _cannotModifyThePointsBecauseGradeHasBeenAssignedToTheStudentForThisAssignment . '.');
                        }
                    }
                    if ($column == 'DESCRIPTION' && $value != '' && $table == 'gradebook_assignments') {
                        $value = htmlspecialchars($_SESSION['ASSIGNMENT_DESCRIPTION']);
                    } else
                                if ($column == 'COURSE_ID'  && $value == 'Y' && $table == 'gradebook_assignments') {
                        $value = $course_id;
                        $sql .= 'COURSE_PERIOD_ID=NULL,';
                    } elseif ($column == 'COURSE_ID' && $table == 'gradebook_assignments') {
                        $column = 'COURSE_PERIOD_ID';
                        $get_assignment_course_period = DBGet(DBQuery('SELECT gat.COURSE_PERIOD_ID FROM gradebook_assignment_types gat,gradebook_assignments ga WHERE ga.ASSIGNMENT_TYPE_ID=gat.ASSIGNMENT_TYPE_ID AND ga.ASSIGNMENT_ID=' . $id));
                        $value = ($get_assignment_course_period[1]['COURSE_PERIOD_ID'] != '' ? $get_assignment_course_period[1]['COURSE_PERIOD_ID'] : $course_period_id);
                        $sql .= 'COURSE_ID=NULL,';
                        if ($get_assignment_course_period[1]['COURSE_PERIOD_ID'] != $course_period_id)
                            $redirect_now = 'y';
                    }
                    if ($column != 'DESCRIPTION' && $table == 'gradebook_assignments') {
                        $value = paramlib_validation($column, $value);
                    }

                    $value = singleQuoteReplace('', '', $value);
                    $sql .= $column . '=\'' . $value . '\',';
                }
                $sql = substr($sql, 0, -1) . ' WHERE ' . substr($table, 10, -1) . '_ID=\'' . $id . '\'';

                $go = true;
            } else {
                ShowErrPhp('' . _titleCannotBeBlank . '');
            }
        } else {
            $sql = 'INSERT INTO ' . $table . ' ';

            if ($table == 'gradebook_assignments') {
                if ($columns['ASSIGNMENT_TYPE_ID']) {
                    $_REQUEST['assignment_type_id'] = $columns['ASSIGNMENT_TYPE_ID'];
                    unset($columns['ASSIGNMENT_TYPE_ID']);
                }


                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'gradebook_assignments\''));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];


                // $_REQUEST['assignment_id'] = $id;

                $check_cp_type = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods WHERE COURSE_PERIOD_ID=' . $course_period_id));
                if ($check_cp_type[1]['MARKING_PERIOD_ID'] != '') {
                    $fields = 'ASSIGNMENT_TYPE_ID,STAFF_ID,MARKING_PERIOD_ID,';
                    $values = "'" . $_REQUEST['assignment_type_id'] . "','" . User('STAFF_ID') . "','" . UserMP() . "',";
                } else {
                    $full_year_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
                    $full_year_mp = $full_year_mp[1]['MARKING_PERIOD_ID'];
                    $fields = 'ASSIGNMENT_TYPE_ID,STAFF_ID,MARKING_PERIOD_ID,';
                    $values = "'" . $_REQUEST['assignment_type_id'] . "','" . User('STAFF_ID') . "','" . $full_year_mp . "',";
                }
            } elseif ($table == 'gradebook_assignment_types') {

                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'gradebook_assignment_types\''));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                $fields = 'STAFF_ID,COURSE_ID,COURSE_PERIOD_ID,';
                $values = '\'' . User('STAFF_ID') . '\',\'' . $course_id . '\',\'' . $course_period_id . '\',';
                // $_REQUEST['assignment_type_id'] = $id;
            }

            $go = false;

            if (!$columns['COURSE_ID'] && $_REQUEST['table'] == 'gradebook_assignments')
                $columns['COURSE_ID'] = 'N';

            foreach ($columns as $column => $value) {

                if ($columns['DUE_DATE'] && $columns['ASSIGNED_DATE']) {
                    if (strtotime($columns['DUE_DATE']) < strtotime($columns['ASSIGNED_DATE'])) {
                        $err = true;
                        break 2;
                    }
                }
                if ($column == 'COURSE_ID') {
                    $column = 'COURSE_PERIOD_ID';
                    $value = $course_period_id;
                }
                if ($column == 'DESCRIPTION' && $value != '') {
                    $value = htmlspecialchars($_SESSION['ASSIGNMENT_DESCRIPTION']);
                }

                if ($value != '') {
                    if ($column != 'DESCRIPTION' && $table == 'gradebook_assignments') {
                        $value = paramlib_validation($column, $value);
                    }
                    $fields .= $column . ',';

                    $values .= '\'' . singleQuoteReplace("'", "''", $value) . '\',';

                    $go = true;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
        }


        if ((isset($columns['DUE_DATE']) || isset($columns['ASSIGNED_DATE'])) && $err == false) {
            $get_custom_cp = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM gradebook_assignment_types WHERE assignment_type_id=' . $_REQUEST['assignment_type_id']));

            $get_custom_date = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE,MARKING_PERIOD_ID FROM course_details WHERE COURSE_PERIOD_ID=\'' . $get_custom_cp[1]['COURSE_PERIOD_ID'] . '\' '));
            $cud_sd = strtotime($get_custom_date[1]['BEGIN_DATE']);
            $cud_ed = strtotime($get_custom_date[1]['END_DATE']);

            $get_dates = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM marking_periods WHERE marking_period_id=\'' . UserMP() . '\' '));
            $s_d = strtotime($get_dates[1]['START_DATE']);
            $e_d = strtotime($get_dates[1]['END_DATE']);

            if (isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE'])) {

                if ($get_custom_date[1]['MARKING_PERIOD_ID'] == NULL) {


                    if (strtotime($columns['DUE_DATE']) > $cud_sd && strtotime($columns['DUE_DATE']) <= $cud_ed && strtotime($columns['ASSIGNED_DATE']) >= $cud_sd && strtotime($columns['ASSIGNED_DATE']) < $cud_ed)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _assignedDateAndDueDateMustBeWithinCoursePeriodsStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                } else {


                    if (strtotime($columns['DUE_DATE']) >= $s_d && strtotime($columns['DUE_DATE']) <= $e_d && strtotime($columns['ASSIGNED_DATE']) >= $s_d && strtotime($columns['ASSIGNED_DATE']) < $e_d)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _assignedDateAndDueDateMustBeWithinTheCurrentMarkingPeriodsStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                }
            }
            if (isset($columns['DUE_DATE']) && !isset($columns['ASSIGNED_DATE'])) {

                if ($get_custom_date[1]['MARKING_PERIOD_ID'] == NULL) {

                    if (strtotime($columns['DUE_DATE']) > $cud_sd && strtotime($columns['DUE_DATE']) <= $cud_ed)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _dueDateMustBeWithinTheCoursePeriodStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                } else {
                    if (strtotime($columns['DUE_DATE']) > $s_d && strtotime($columns['DUE_DATE']) <= $e_d)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _dueDateMustBeWithinTheCurrentMarkingPeriodStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                }
            }
            if (!isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE'])) {
                if ($get_custom_date[1]['MARKING_PERIOD_ID'] == NULL) {

                    if (strtotime($columns['ASSIGNED_DATE']) >= $cud_sd && strtotime($columns['ASSIGNED_DATE']) < $cud_ed)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _assignedDateAndDueDateMustBeWithinTheCurrentMarkingPeriodStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                } else {
                    if (strtotime($columns['ASSIGNED_DATE']) >= $s_d && strtotime($columns['ASSIGNED_DATE']) < $e_d)
                        $go = true;
                    else {
                        $msg = '<Font color=red>' . _assignedDateMustBeWithinTheCurrentMarkingPeriodStartDateAndEndDate . '.</FONT>';
                        $go = false;
                        $_REQUEST['assignment_id'] = 'new';
                    }
                }
            }
        }

        if ($go && $f == 0) {
            if ($_REQUEST['assignment_id'] != '') {
                DBQuery_assignment($sql);
                if ($id == 'new' && $table == 'gradebook_assignments') {
                    $_REQUEST['assignment_id'] = mysqli_insert_id($connection);
                }
                if ($_REQUEST['type_id'] == 'new' && $_REQUEST['table'] == 'gradebook_assignments' && $_REQUEST['tables']['new']['COURSE_ID'] == 'Y') {
                    $assign_tyid = DBGet(DBQuery('SELECT ASSIGNMENT_TYPE_ID FROM gradebook_assignments WHERE ASSIGNMENT_ID=' . $_REQUEST['assignment_id']));
                    $assign_tyid = $assign_tyid[1]['ASSIGNMENT_TYPE_ID'];
                    DBQuery('UPDATE gradebook_assignments SET COURSE_ID=' . UserCourse() . ' WHERE ASSIGNMENT_ID=' . $_REQUEST['assignment_id']);
                    //                            $get_all_cps=DBGet(DBQuery("SELECT COURSE_PERIOD_ID FROM course_periods  WHERE SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' AND COURSE_ID='".UserCourse()."' AND (TEACHER_ID='".User('STAFF_ID')."' OR SECONDARY_TEACHER_ID='".User('STAFF_ID')."') AND (MARKING_PERIOD_ID IN (".GetAllMP($allMP,UserMP()).") OR (MARKING_PERIOD_ID IS NULL)) AND COURSE_PERIOD_ID!=".UserCoursePeriod()." group by (COURSE_PERIOD_ID)"));
                    //                            foreach($get_all_cps as $gci=>$gcd)
                    //                            {
                    //                                $assign_type_newid = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'gradebook_assignment_types\''));
                    //                                $assign_type_newid= $assign_type_newid[1]['AUTO_INCREMENT'];
                    //                                DBQuery('INSERT INTO  gradebook_assignment_types (ASSIGNMENT_TYPE_ID,STAFF_ID,COURSE_ID,TITLE,FINAL_GRADE_PERCENT,COURSE_PERIOD_ID) (SELECT '.$assign_type_newid.',STAFF_ID,COURSE_ID,TITLE,FINAL_GRADE_PERCENT,'.$gcd['COURSE_PERIOD_ID'].' FROM gradebook_assignment_types WHERE ASSIGNMENT_TYPE_ID='.$assign_tyid.')');
                    //                                DBQuery('INSERT INTO  gradebook_assignments (STAFF_ID,MARKING_PERIOD_ID,COURSE_PERIOD_ID,COURSE_ID,ASSIGNMENT_TYPE_ID,TITLE,ASSIGNED_DATE,DUE_DATE,POINTS,DESCRIPTION,UNGRADED) (SELECT STAFF_ID,MARKING_PERIOD_ID,'.$gcd['COURSE_PERIOD_ID'].',COURSE_ID,'.$assign_type_newid.',TITLE,ASSIGNED_DATE,DUE_DATE,POINTS,DESCRIPTION,UNGRADED FROM gradebook_assignments WHERE ASSIGNMENT_ID='.$_REQUEST['assignment_id'].')');
                    //                                
                    //                            }
                }
            } else {
                DBQuery($sql);
                if ($id == 'new' && $table == 'gradebook_assignment_types') {
                    $_REQUEST['assignment_type_id'] = mysqli_insert_id($connection);
                }
            }
            //            DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
        }
        if ($msg) {
            echo '<div class="alert alert-danger no-border">' . $msg . '</div>';
        }
    }
    unset($_REQUEST['tables']);
    unset($_SESSION['ASSIGNMENT_DESCRIPTION']);
    $_REQUEST['ajax'] = true;
    unset($_SESSION['_REQUEST_vars']['tables']);
    if ($redirect_now == 'y')
        echo '<script type="text/javascript">check_content("Ajax.php?modname=grades/Assignments.php");</script>';
}

if ($err) {

    unset($_REQUEST['tables']);
    $_REQUEST['assignment_id'] = 'new';
    $_REQUEST['ajax'] = true;
    if ($err_ck != 1) {
        echo '<Font color=red>' . _dueDateMustBeGreaterThanAssignedDate . '.</FONT>';
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {

    if ($_REQUEST['assignment_type_id'] || $_REQUEST['assignment_id']) {
        if ($_REQUEST['assignment_type_id'] && !$_REQUEST['assignment_id']) {
            $table = _assignmentType;

            //   $data=DBGet(DBQuery('select id from student_report_card_grades where course_period_id=(select course_period_id from  gradebook_assignment_types where assignment_type_id='.$_REQUEST['assignment_type_id'].')'));

            $data = DBGet(DBQuery('select id from student_report_card_grades where course_period_id=(select course_period_id from  gradebook_assignments where assignment_type_id=' . $_REQUEST['assignment_type_id'] . ')'));

            if (count($data) > 0)
                UnableDeletePromptMod('' . _gradebookAssignmentTypeCannotBeDeletedBecauseAssignmentsAreCreatedInThisAssignmentType . '.', '', 'modfunc=&assignment_type_id=' . $_REQUEST['assignment_type_id']);
            else {
                if (DeletePromptAssignment(ucfirst($table), $_REQUEST['assignment_type_id'])) {

                    if ($_REQUEST['assignment_type_id'] != 0 && $_REQUEST['assignment_type_id'] != '') {
                        $assignment_ids_to_del = DBGet(DBQuery('SELECT * FROM gradebook_assignments WHERE assignment_type_id=' . $_REQUEST['assignment_type_id']));
                        foreach ($assignment_ids_to_del as $ai_del) {
                            DBQuery('DELETE FROM gradebook_grades WHERE assignment_id=\'' . $ai_del['ASSIGNMENT_ID'] . '\'');
                        }
                    }


                    DBQuery('DELETE FROM gradebook_assignment_types  WHERE assignment_type_id=\'' . $_REQUEST['assignment_type_id'] . '\'');
                    DBQuery('DELETE FROM gradebook_assignments WHERE assignment_type_id=\'' . $_REQUEST['assignment_type_id'] . '\'');

                    unset($_REQUEST['assignment_type_id']);
                    unset($_REQUEST['modfunc']);
                }
            }
        } else {
            $table = 'assignment';

            $has_assigned = 0;
            $mp_id =  UserMP() . ",'E" . UserMP() . "'";

            $stmt = DBGet(DBQuery("SELECT id  AS TOTAL_ASSIGNED from student_report_card_grades WHERE course_period_id=" . UserCoursePeriod() . " and marking_period_id in($mp_id)"));
            $has_assigned = $stmt[1]['TOTAL_ASSIGNED'];
            if ($has_assigned > 0) {
                UnableDeletePromptMod('' . _gradebookAssignmentCannotBeDeletedBecauseGradeWasGivenForThisAssignment . '.', '', 'modfunc=&assignment_type_id=' . $_REQUEST['assignment_type_id'] . '&assignment_id=' . $_REQUEST['assignment_id']);
            } else {
                if (DeletePromptAssignment(ucfirst($table), $_REQUEST['assignment_type_id'])) {

                    DBQuery('DELETE FROM gradebook_grades WHERE assignment_id=\'' . $_REQUEST['assignment_id'] . '\'');
                    DBQuery('DELETE FROM gradebook_assignments WHERE assignment_id=\'' . $_REQUEST['assignment_id'] . '\'');
                    unset($_REQUEST['assignment_id']);
                    unset($_REQUEST['modfunc']);
                }
            }
        }
    }
    unset($_SESSION['_REQUEST_vars']['modfunc']);
}

if (!$_REQUEST['modfunc'] && $course_id) {

    ## ASSIGNMENT TYPES
    // $sql = ' SELECT ASSIGNMENT_TYPE_ID,TITLE 
    //              FROM (
    //                 ( select gat.ASSIGNMENT_TYPE_ID,gat.TITLE  FROM gradebook_assignment_types gat where gat.COURSE_PERIOD_ID=\''.$course_period_id.'\' )
    //               UNION  
    //                (SELECT gat.ASSIGNMENT_TYPE_ID as ASSIGNMENT_TYPE_ID,concat(gat.TITLE,\' (\',cp.title,\')\') as TITLE FROM gradebook_assignment_types gat , gradebook_assignments ga, course_periods cp
    //                 where cp.course_period_id =gat.course_period_id and gat.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
    //                 AND ga.COURSE_PERIOD_ID IS NULL AND ga.COURSE_ID=\''.UserCourse().'\' AND ga.STAFF_ID=\''.UserID().'\' ) 
    //               )as t
    //               GROUP BY ASSIGNMENT_TYPE_ID';

    $sql = ' SELECT ASSIGNMENT_TYPE_ID,TITLE 
                 FROM (
                    ( select gat.ASSIGNMENT_TYPE_ID,gat.TITLE  FROM gradebook_assignment_types gat where gat.COURSE_PERIOD_ID=\'' . $course_period_id . '\' )
                  UNION  
                   (SELECT gat.ASSIGNMENT_TYPE_ID as ASSIGNMENT_TYPE_ID,concat(gat.TITLE,\' (\',TRIM(cp.title),\')\') as TITLE FROM gradebook_assignment_types gat , gradebook_assignments ga, course_periods cp
                    where cp.course_period_id =gat.course_period_id and gat.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
                    AND ga.COURSE_ID=\'' . UserCourse() . '\' AND ga.STAFF_ID=\'' . UserID() . '\' ) 
                  )as t
                  GROUP BY ASSIGNMENT_TYPE_ID';

    $QI = DBQuery($sql);

    $types_RET = DBGet($QI);
    //        $counter_i=count($types_RET);
    //        $others=DBGet(DBQuery('SELECT * FROM gradebook_assignments WHERE COURSE_ID='.UserCourse().' AND COURSE_PERIOD_ID!='.UserCoursePeriod()));
    //        foreach($others as $o)
    //        {
    //            $counter_i++;
    //            $gat=DBGet(DBQuery('SELECT * FROM gradebook_assignment_types WHERE ASSIGNMENT_TYPE_ID='.$o['ASSIGNMENT_TYPE_ID']));
    //            $types_RET[$counter_i]['ASSIGNMENT_TYPE_ID']=$gat[1]['ASSIGNMENT_TYPE_ID'];
    //            $types_RET[$counter_i]['TITLE']=$gat[1]['TITLE'];
    //        }
    if ($_REQUEST['assignment_id'] != 'new' && $_REQUEST['assignment_type_id'] != 'new') {
        $delete_button = "<INPUT type=button value=" . _delete . " class='btn btn-danger' onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&assignment_type_id=$_REQUEST[assignment_type_id]&assignment_id=$_REQUEST[assignment_id]\"'> &nbsp;";
    }

    // ADDING & EDITING FORM
    if ($_REQUEST['assignment_id'] && $_REQUEST['assignment_id'] != 'new') {
        $sql = 'SELECT ASSIGNMENT_TYPE_ID,TITLE,ASSIGNED_DATE,DUE_DATE,POINTS,COURSE_ID,DESCRIPTION,
				CASE WHEN DUE_DATE<ASSIGNED_DATE THEN \'Y\' ELSE NULL END AS DATE_ERROR
				FROM gradebook_assignments
				WHERE ASSIGNMENT_ID=\'' . $_REQUEST['assignment_id'] . '\'';
        $QI = DBQuery($sql);
        $RET = DBGet($QI);
        $RET = $RET[1];
        $title = $RET['TITLE'];
    } elseif ($_REQUEST['assignment_type_id'] && $_REQUEST['assignment_type_id'] != 'new' && $_REQUEST['assignment_id'] != 'new') {
        // $sql = 'SELECT at.TITLE,at.FINAL_GRADE_PERCENT,
        // (SELECT sum(FINAL_GRADE_PERCENT) FROM gradebook_assignment_types WHERE assignment_type_id in(select assignment_type_id from gradebook_assignment_types  where COURSE_PERIOD_ID=\''.$course_period_id.'\')) AS TOTAL_PERCENT
        // FROM gradebook_assignment_types at
        // WHERE at.ASSIGNMENT_TYPE_ID=\''.$_REQUEST['assignment_type_id'].'\'';

        $sql = 'SELECT at.TITLE,at.FINAL_GRADE_PERCENT,
                (SELECT SUM(FINAL_GRADE_PERCENT) FROM gradebook_assignment_types WHERE assignment_type_id IN(SELECT gat1.assignment_type_id FROM gradebook_assignment_types gat1 WHERE gat1.COURSE_PERIOD_ID=\'' . $course_period_id . '\' 
                    UNION SELECT gat2.assignment_type_id FROM gradebook_assignment_types gat2, gradebook_assignments ga, course_periods cp
                    WHERE cp.course_period_id = gat2.course_period_id AND gat2.ASSIGNMENT_TYPE_ID = ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
                    AND ga.COURSE_ID=\'' . UserCourse() . '\' AND ga.STAFF_ID=\'' . UserID() . '\' )) AS TOTAL_PERCENT
                FROM gradebook_assignment_types at
                WHERE at.ASSIGNMENT_TYPE_ID=\'' . $_REQUEST['assignment_type_id'] . '\'';

        $QI = DBQuery($sql);
        $RET = DBGet($QI, array('FINAL_GRADE_PERCENT' => '_makePercent'));
        $RET = $RET[1];
        $title = $RET['TITLE'];
    } elseif ($_REQUEST['assignment_id'] == 'new') {
        $title = _newAssignment;
        $new = true;
    } elseif ($_REQUEST['assignment_type_id'] == 'new') {
        // $sql='SELECT sum(FINAL_GRADE_PERCENT) AS TOTAL_PERCENT FROM gradebook_assignment_types WHERE assignment_type_id in(select assignment_type_id from gradebook_assignment_types  where COURSE_PERIOD_ID=\''.$course_period_id.'\')';

        $sql = 'SELECT SUM(FINAL_GRADE_PERCENT) AS TOTAL_PERCENT FROM gradebook_assignment_types WHERE assignment_type_id IN(SELECT gat1.assignment_type_id FROM gradebook_assignment_types gat1 WHERE gat1.COURSE_PERIOD_ID=\'' . $course_period_id . '\' 
            UNION SELECT gat2.assignment_type_id FROM gradebook_assignment_types gat2, gradebook_assignments ga, course_periods cp
                    WHERE cp.course_period_id = gat2.course_period_id AND gat2.ASSIGNMENT_TYPE_ID = ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
                    AND ga.COURSE_ID=\'' . UserCourse() . '\' AND ga.STAFF_ID=\'' . UserID() . '\' )';

        $QI = DBQuery($sql);
        $RET = DBGet($QI, array('FINAL_GRADE_PERCENT' => '_makePercent'));
        $RET = $RET[1];
        $title = _newAssignmentType;
    }


    if ($_REQUEST['assignment_id']) {
        echo "<FORM class=form-horizontal name=F3 action=Modules.php?modname=$_REQUEST[modname]&assignment_type_id=$_REQUEST[assignment_type_id]";
        if ($_REQUEST['assignment_id'] != 'new')
            echo "&assignment_id=$_REQUEST[assignment_id]";
        else
            echo "&assignment_id=new";
        echo "&table=gradebook_assignments method=POST>";
        echo '<div class="panel panel-default">';
        DrawHeader($title, $delete_button . '<INPUT type=submit id="setupAssgnTypeBtnOne" class="btn btn-primary" value=' . _save . ' onclick="formcheck_assignments(this);">');
        echo '<div class="panel-body">';
        echo "<INPUT type=hidden name=type_id value='$_REQUEST[assignment_id]' id=type_id>";
        echo "<INPUT type=hidden value='$_REQUEST[assignment_type_id]' id=assignment_type_id>";
        $header .= '<div class="row">';
        $header .= '<div class="col-md-6"><div class="form-group">' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['assignment_id'] . '][TITLE]', '' . _title . ' *', 'size=36') . '</div></div>';

        if ($id == "new" || $_REQUEST['tab_id'] == "new" || $RET['POINTS'] == '')
            $extra = ' size=4 maxlength=5 onkeydown="return numberOnlyMod(event,this);" ';
        else
            $extra = ' size=4 maxlength=5 onkeydown=\"return numberOnlyMod(event,this);\"';
        $header .= '<div class="col-md-6"><div class="form-group">' . TextInput($RET['POINTS'], 'tables[' . $_REQUEST['assignment_id'] . '][POINTS]', '' . _points . ' *', $extra) . '</div></div>';
        $header .= '</div>';

        $header .= '<div class="row">';
        if ($_REQUEST['assignment_id'] == 'new')
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">&nbsp;</label><div class="col-lg-8">' . CheckboxInputSwitch($RET['COURSE_ID'], 'tables[' . $_REQUEST['assignment_id'] . '][COURSE_ID]', _applyToAllPeriodsForThisCourse, '', false, 'Yes', 'No', '', 'switch-success') . '</div></div></div>';
        foreach ($types_RET as $type)
            $assignment_type_options[$type['ASSIGNMENT_TYPE_ID']] = $type['TITLE'];

        $header .= '<div class="col-md-6"><div class="form-group">' . SelectInput($RET['ASSIGNMENT_TYPE_ID'] ? $RET['ASSIGNMENT_TYPE_ID'] : $_REQUEST['assignment_type_id'], 'tables[' . $_REQUEST['assignment_id'] . '][ASSIGNMENT_TYPE_ID]', _assignmentType, $assignment_type_options, false) . '</div></div>';
        $header .= '</div>';

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . ($_REQUEST['assignment_id'] == 'new' ? '' . _assigned . ' <span class="text-danger">*</span>' : '' . _assigned . '  <span class="text-danger">*</span>') . '</label><div class="col-lg-8">' . DateInputAY($new && Preferences('DEFAULT_ASSIGNED', 'Gradebook') == 'Y' ? date('Y-m-d') : $RET['ASSIGNED_DATE'], 'tables[' . $_REQUEST['assignment_id'] . '][ASSIGNED_DATE]', 1) . '</div></div></div>';
        $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . ($_REQUEST['assignment_id'] == 'new' ? '' . _due . '  <span class="text-danger">*</span>' : '' . _due . '  <span class="text-danger">*</span>') . '</label>' . DateInputAY($new && Preferences('DEFAULT_DUE', 'Gradebook') == 'Y' ? date('Y-m-d') : $RET['DUE_DATE'], 'tables[' . $_REQUEST['assignment_id'] . '][DUE_DATE]', 2) . '</div></div>';
        $header .= '</div>';
    } elseif ($_REQUEST['assignment_type_id']) {

        echo "<FORM class=form-horizontal name=F3 action=Modules.php?modname=$_REQUEST[modname]&table=gradebook_assignment_types";
        if ($_REQUEST['assignment_type_id'] != 'new')
            echo "&assignment_type_id=$_REQUEST[assignment_type_id]";
        echo " method=POST>";
        echo '<div class="panel panel-default">';
        DrawHeader($title, $delete_button . '<INPUT type=submit id="setupAssgnTypeBtnTwo" class="btn btn-primary" value=' . _save . ' onclick="return formcheck_assignments(this);">');
        echo '<div class="panel-body">';

        echo "<INPUT type=hidden name=type_id value='$_REQUEST[assignment_id]' id=type_id>";
        echo "<INPUT type=hidden name=assignment_type_id value='$_REQUEST[assignment_type_id]' id=assignment_type_id>";

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6"><div class="form-group">' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['assignment_type_id'] . '][TITLE]', _title, 'size=36') . '</div></div>';

        if ($programconfig['WEIGHT'] == 'Y') {
            $header .= '<div class="col-md-6"><div class="form-group">' . TextInput(($RET['FINAL_GRADE_PERCENT'] != 0 ? $RET['FINAL_GRADE_PERCENT'] : ''), 'tables[' . $_REQUEST['assignment_type_id'] . '][FINAL_GRADE_PERCENT]', ($RET['FINAL_GRADE_PERCENT'] != 0 ? '' : '<FONT color=red>') . _weightPercent . ($RET['FINAL_GRADE_PERCENT'] != 0 ? '' : '</FONT>')) . '</div></div>';
            $header .= '<div class="col-md-6"><div class="form-group">' . NoInput($RET['TOTAL_PERCENT'] == 1 ? '100%' : '<FONT COLOR=red>' . (100 * $RET['TOTAL_PERCENT']) . '%</FONT>', _percentTotal) . '</div></div>';
        }
        $header .= '</div>';
    } else
        $header = false;

    if ($header) {
        if ($_REQUEST['assignment_id']) {
            echo $header;
            echo '<div class="row">';
            echo '<div class="col-md-12"><div class="form-group">';
            echo '<label class="control-label col-xs-2 text-right">' . _description . '</label>';
            echo '<div class="col-xs-10">';

            //            $oFCKeditor = new FCKeditor('tables[' . $_REQUEST['assignment_id'] . '][DESCRIPTION]');
            //            $oFCKeditor->BasePath = 'modules/messaging/fckeditor/';
            //            $oFCKeditor->Value = html_entity_decode(html_entity_decode($RET['DESCRIPTION']));
            //            $oFCKeditor->Height = '200';
            //            $oFCKeditor->Width = '100%';
            //            $oFCKeditor->ToolbarSet = 'Mytoolbar ';
            //            echo $oFCKeditor->Create();


            echo '<textarea name="tables[' . $_REQUEST['assignment_id'] . '][DESCRIPTION]" id="txtBody" rows="4" cols="100">' . html_entity_decode(html_entity_decode($RET['DESCRIPTION'])) . '</textarea>';





            echo '<script type="text/javascript">$(function(){ CKEDITOR.replace(\'txtBody\', { height: \'400px\', extraPlugins: \'forms\'}); });</script>';



            echo '</div>';
            echo '</div></div>';
            echo '</div>';
        } else
            DrawHeader($header);
        echo '</div>'; //.panel-body
        echo '</FORM>';
        echo '</div>'; //.panel
    }
    // DISPLAY THE MENU
    $LO_options = array('save' => false, 'search' => false, 'add' => true);

    echo '<div class="row">';

    if (count($types_RET)) {
        if ($_REQUEST['assignment_type_id']) {
            foreach ($types_RET as $key => $value) {
                if ($value['ASSIGNMENT_TYPE_ID'] == $_REQUEST['assignment_type_id'])
                    $types_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    $columns = array('TITLE' => _assignmentType);
    $link = array();
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
    $link['TITLE']['variables'] = array('assignment_type_id' => 'ASSIGNMENT_TYPE_ID');
    $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&assignment_type_id=new";
    $link['add']['first'] = 50000; // number before add link moves to top

    ListOutput($types_RET, $columns,  _assignmentType, _assignmentTypes, $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
    // ASSIGNMENTS
    if ($_REQUEST['assignment_type_id'] && $_REQUEST['assignment_type_id'] != 'new' && count($types_RET)) {
        $sql = 'SELECT ASSIGNMENT_ID,TITLE FROM gradebook_assignments WHERE (COURSE_ID=\'' . $course_id . '\' OR COURSE_PERIOD_ID=\'' . $course_period_id . '\') AND ASSIGNMENT_TYPE_ID=\'' . $_REQUEST['assignment_type_id'] . '\' AND (MARKING_PERIOD_ID=\'' . (GetCpDet($course_period_id, 'MARKING_PERIOD_ID') != '' ? UserMP() : GetMPId('FY')) . '\' OR MARKING_PERIOD_ID=' . GetMPId('FY') . ' ) ORDER BY ' . Preferences('ASSIGNMENT_SORTING', 'Gradebook') . ' DESC';

        $QI = DBQuery($sql);
        $assn_RET = DBGet($QI);

        if (count($assn_RET)) {
            if ($_REQUEST['assignment_id'] && $_REQUEST['assignment_id'] != 'new') {
                foreach ($assn_RET as $key => $value) {
                    if ($value['ASSIGNMENT_ID'] == $_REQUEST['assignment_id'])
                        $assn_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }


        echo '<div class="col-md-6">';
        echo '<div class="panel panel-default">';
        $columns = array('TITLE' => _assignment);
        $link = array();
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&assignment_type_id=$_REQUEST[assignment_type_id]";
        $link['TITLE']['variables'] = array('assignment_id' => 'ASSIGNMENT_ID');
        $link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&assignment_type_id=$_REQUEST[assignment_type_id]&assignment_id=new";
        $link['add']['first'] = 50000; // number before add link moves to top

        ListOutput($assn_RET, $columns,  _assignment, _assignments, $link, array(), $LO_options);

        echo '</div>'; //.panel
        echo '</div>'; //.col-md-6
    }

    echo '</div>';
} elseif (!$course_id)
    echo '<BR>' . ErrorMessage(array('' . _youDonTHaveACourseThisPeriod . '.'), 'error');

function _makePercent($value, $column)
{
    return Percent($value, 2);
}
