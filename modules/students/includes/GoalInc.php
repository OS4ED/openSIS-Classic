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

$sql_urg_sch_id = 'SELECT school_id FROM student_goal WHERE goal_id = ' . clean_param($_REQUEST['goal_id'], PARAM_INT);
$res_urg_sch_id = DBQuery($sql_urg_sch_id);
$row_urg_sch_id = DBGet($res_urg_sch_id);

$urg_sch_id = $row_urg_sch_id[1];
if (UserSchool() != '')
    $school_id = UserSchool();
else
    $school_id = $urg_sch_id['SCHOOL_ID'];

$i = 0;
$gid = $_REQUEST['goal_id'];
$tabl = $_REQUEST['tabl'];

if (($_REQUEST['day_tables'] || $_REQUEST['tables']) && ($_POST['day_tables'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['day_tables'] as $id => $values) {
        if ($_REQUEST['day_tables'][$id]['START_DATE'] && $_REQUEST['month_tables'][$id]['START_DATE'] && $_REQUEST['year_tables'][$id]['START_DATE']) {
            // $_REQUEST['tables'][$id]['START_DATE'] = $_REQUEST['day_tables'][$id]['START_DATE'] . '-' . $_REQUEST['month_tables'][$id]['START_DATE'] . '-' . $_REQUEST['year_tables'][$id]['START_DATE'];
            $_REQUEST['tables'][$id]['START_DATE'] = $_REQUEST['year_tables'][$id]['START_DATE'] . '-' . $_REQUEST['month_tables'][$id]['START_DATE'] . '-' . $_REQUEST['day_tables'][$id]['START_DATE'];
            $start_date = $_REQUEST['tables'][$id]['START_DATE'];
        } elseif (isset($_REQUEST['day_tables'][$id]['START_DATE']) && isset($_REQUEST['month_tables'][$id]['START_DATE']) && isset($_REQUEST['year_tables'][$id]['START_DATE']))
            $_REQUEST['tables'][$id]['START_DATE'] = '';


        if ($_REQUEST['day_tables'][$id]['END_DATE'] && $_REQUEST['month_tables'][$id]['END_DATE'] && $_REQUEST['year_tables'][$id]['END_DATE']) {
            //            $_REQUEST['tables'][$id]['END_DATE'] = $_REQUEST['day_tables'][$id]['END_DATE'] . '-' . $_REQUEST['month_tables'][$id]['END_DATE'] . '-' . $_REQUEST['year_tables'][$id]['END_DATE'];
            $_REQUEST['tables'][$id]['END_DATE'] = $_REQUEST['year_tables'][$id]['END_DATE'] . '-' . $_REQUEST['month_tables'][$id]['END_DATE'] . '-' . $_REQUEST['day_tables'][$id]['END_DATE'];
            $end_date = $_REQUEST['tables'][$id]['END_DATE'];
        } elseif (isset($_REQUEST['day_tables'][$id]['END_DATE']) && isset($_REQUEST['month_tables'][$id]['END_DATE']) && isset($_REQUEST['year_tables'][$id]['END_DATE']))
            $_REQUEST['tables'][$id]['END_DATE'] = '';
    }
    if (!$_POST['tables'])
        $_POST['tables'] = $_REQUEST['tables'];
}

unset($_SESSION['_REQUEST_vars']['goal_id']);
unset($_SESSION['_REQUEST_vars']['course_id']);
unset($_SESSION['_REQUEST_vars']['course_period_id']);

// if only one subject, select it automatically -- works for Course Setup and Choose a Course
if ($_REQUEST['modfunc'] != 'delete' && !$_REQUEST['goal_id']) {
    $subjects_RET = DBGet(DBQuery('SELECT GOAL_ID,GOAL_TITLE FROM student_goal WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND STUDENT_ID=\'' . UserStudentID() . '\''));

    if (count($subjects_RET) == 1)
        $_REQUEST['goal_id'] = $subjects_RET[1]['GOAL_ID'];
}




if (clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'delete') {
    $_REQUEST['goal_id'] = $_REQUEST['gid'];
    $sql_pro_del = 'DELETE FROM student_goal_progress WHERE progress_id = ' . $_REQUEST['pid'];
    $res_pro_del = DBQuery($sql_pro_del);
}

if (clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'delete_goal') {
    $_REQUEST['goal_id'] = $_REQUEST['gid'];
    $sql_pro_del = 'DELETE FROM student_goal_progress WHERE progress_id = ' . $_REQUEST['pid'];
    $res_pro_del = DBQuery($sql_pro_del);
}


// UPDATING
if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax']) && AllowEdit()) {
    $where = array(
        'student_goal' => 'GOAL_ID',
        'student_goal_progress' => 'PROGRESS_ID',
        'course_periods' => 'COURSE_PERIOD_ID'
    );
    foreach ($_REQUEST['tables'] as $table_name => $tables) {
        foreach ($tables as $id => $columns) {
            if ($id != 'new' && $i == 0) {
                if (is_numeric($table_name)) {

                    if ($tabl == 'student_goal') {
                        $id = $table_name;
                        $table_name = 'student_goal';
                        $select = ' START_DATE,END_DATE ';
                    }

                    if ($tabl == 'student_goal_progress') {

                        $id = $table_name;
                        $table_name = 'student_goal_progress';
                        $select = ' START_DATE ';
                    }
                }
                if (!isset($start_date) || !isset($end_date) && $tabl == 'student_goal') {

                    $sql_s_date = 'SELECT' . $select . 'FROM ' . $table_name . ' WHERE GOAL_ID=\'' . $id . '\'';

                    $res_s_date = DBQuery($sql_s_date);
                    $row_s_date = DBGet($res_s_date);
                }
                $school_syear_date = DBGet(DBQuery('SELECT * from school_years where syear=\'' . UserSyear() . '\' and school_id=\'' . UserSchool() . '\''));

                $school_start_dt = $school_syear_date[1]['START_DATE'];
                $school_end_dt = $school_syear_date[1]['END_DATE'];

                if ($table_name != 'student_goal_progress' && ((strtotime($start_date) > strtotime($end_date) && $start_date != "" && $end_date != "") || (strtotime($row_s_date[1]['START_DATE']) > strtotime($end_date) && $row_s_date[1]['START_DATE'] != "" && $end_date != "") || (strtotime($start_date) > strtotime($row_s_date[1]['END_DATE']) && $start_date != "" && $row_s_date[1]['END_DATE'] != "") || (strtotime($row_s_date[1]['START_DATE']) > strtotime($row_s_date[1]['END_DATE']) && $row_s_date[1]['START_DATE'] != "" && $row_s_date[1]['END_DATE'] != "") || (strtotime($start_date) < strtotime($school_start_dt) || strtotime($start_date) > strtotime($school_end_dt)) || (strtotime($end_date) < strtotime($school_start_dt) || strtotime($end_date) > strtotime($school_end_dt)))) { {
                        ShowErr(_dataNotSavedBecauseStartAndEndDateIsNotValid);
                    }
                } else {

                    if (!is_numeric($table_name)) {
                        $sql = 'UPDATE ' . $table_name . ' SET ';
                    }
                    if ($_REQUEST['tables'][$id]['START_DATE'] != '') {

                        if ($table_name == 'student_goal_progress') {
                            //echo 'SELECT START_DATE,END_DATE from student_goal WHERE goal_id = ' . clean_param($_REQUEST['goal_id'], PARAM_INT);
                            $chk_dt = DBGet(DBQuery('SELECT START_DATE,END_DATE from student_goal WHERE goal_id = ' . clean_param($_REQUEST['goal_id'], PARAM_INT)));


                            if ((date('Y-m-d', strtotime($_REQUEST['tables'][$id]['START_DATE'])) < date('Y-m-d', strtotime($chk_dt[1]['START_DATE']))) || (date('Y-m-d', strtotime($_REQUEST['tables'][$id]['START_DATE'])) > date('Y-m-d', strtotime($chk_dt[1]['END_DATE'])))) {
                                echo '<p style=color:red> ' . _progressEntryDateShouldBeBetweenGoalsStartDate . ',' . _endDate . '</p>';

                                break;
                            } else
                                $sql .= 'START_DATE=\'' . str_replace("'", "\'", $_REQUEST['tables'][$id]['START_DATE']) . '\',';
                        } else
                            $sql .= 'START_DATE=\'' . str_replace("'", "\'", $_REQUEST['tables'][$id]['START_DATE']) . '\',';
                    }
                    if (!is_numeric($table_name)) {
                        foreach ($columns as $column => $value) {
                            if (trim($value) != '') {
                                $value = paramlib_validation($column, $value);
                                $sql .= $column . '=\'' . singleQuoteReplace('', '', $value) . '\',';  // linux
                            }
                        }
                    }

                    ############################### Date Update Start #################################

                    if ((!isset($start_date) && strtotime($row_s_date['START_DATE']) < strtotime($end_date)) || (!isset($start_date) && strtotime($row_s_date['START_DATE']) < strtotime($row_s_date['END_DATE'])))
                        $sql .= 'START_DATE=\'' . $row_s_date['START_DATE'] . '\',';
                    if ((!isset($end_date) && strtotime($start_date) < strtotime($row_s_date['END_DATE'])) || (!isset($end_date) && strtotime($row_s_date['START_DATE']) < strtotime($row_s_date['END_DATE'])))
                        $sql .= 'END_DATE=\'' . $row_s_date['END_DATE'] . '\',';


                    if ((isset($start_date) && strtotime($start_date) < strtotime($end_date)) || (isset($start_date) && strtotime($start_date) < strtotime($row_s_date['END_DATE'])))
                        $sql .= 'START_DATE=\'' . $start_date . '\',';
                    if ((isset($end_date) && strtotime($row_s_date['START_DATE']) < strtotime($end_date)) || (isset($start_date) && strtotime($start_date) < strtotime($end_date)))
                        $sql .= 'END_DATE=\'' . $end_date . '\',';

                    ################################ Date Update End ##################################

                    if (!is_numeric($table_name) && is_numeric($id)) {
                        $sql = substr($sql, 0, -1) . ' WHERE ' . $where[$table_name] . '=\'' . $id . '\'';
                        DBQuery($sql);

                        # ----------------------------------------------------------------- #

                        if ($tabl == 'student_goal') {
                            $_REQUEST['goal_id'] = $id;
                        }

                        if ($tabl == 'student_goal_progress') {
                            $sql_goal_id = 'SELECT goal_id FROM student_goal_progress WHERE progress_id=' . $id;
                            $res_goal_id = DBQuery($sql_goal_id);
                            $row_goal_id = DBGet($res_goal_id);
                            $_REQUEST['progress_id'] = $id;
                            $_REQUEST['goal_id'] = $row_goal_id[1]['GOAL_ID'];
                        }

                        # ----------------------------------------------------------------- #
                    }
                }
            } else {
                $sql = "INSERT INTO $table_name ";

                if ($table_name == 'student_goal') {

                    // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'student_goal\''));
                    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    $fields = 'STUDENT_ID,SCHOOL_ID,SYEAR,START_DATE,END_DATE,';
                    $values = '\'' . UserStudentID() . '\',\'' . UserSchool() . '\',\'' . UserSyear() . '\',\'' . $start_date . '\',\'' . $end_date . '\',';
                    // $_REQUEST['goal_id'] = $id[1]['ID'];
                } elseif ($table_name == 'student_goal_progress') {
                    // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'student_goal_progress\''));
                    // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    $fields = 'GOAL_ID,STUDENT_ID,START_DATE,';
                    $values = '\'' . $_REQUEST['hgoal'] . '\',\'' . UserStudentID() . '\',\'' . $start_date . '\',';
                    // $_REQUEST['progress_id'] = $id[1]['ID'];


                    $chk_dt = DBGet(DBQuery('SELECT START_DATE,END_DATE from student_goal WHERE goal_id = ' . clean_param($_REQUEST['hgoal'], PARAM_INT)));

                    if ((date('Y-m-d', strtotime($_REQUEST['tables']['new']['START_DATE'])) < date('Y-m-d', strtotime($chk_dt[1]['START_DATE']))) || (date('Y-m-d', strtotime($_REQUEST['tables'][$id]['START_DATE'])) > date('Y-m-d', strtotime($chk_dt[1]['END_DATE'])))) {
                        $flag = 1;
                    }
                }

                $go = 0;
                foreach ($columns as $column => $value) {

                    $value = paramlib_validation($column, $value);
                    if (isset($value)) {
                        $fields .= $column . ',';

                        $values .= '\'' . str_replace("'", "''", singleQuoteReplace('', '', $value)) . '\','; // linux

                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
                if ($go) {
                    if ($table_name == 'student_goal_progress') {
                        if ($flag == 1)
                            ShowErr('' . _progressEntryDateShouldBeBetweenGoalsStartDate . '' . _endDate . '');
                        else {
                            DBQuery($sql);
                            if ($table_name == 'student_goal')
                                $_REQUEST['goal_id'] = mysqli_insert_id($connection);
                            else if ($table_name == 'student_goal_progress')
                                $_REQUEST['progress_id'] = mysqli_insert_id($connection);
                        }
                    } else {

                        $school_syear_date = DBGet(DBQuery('SELECT * from school_years where syear=\'' . UserSyear() . '\' and school_id=\'' . UserSchool() . '\''));

                        $school_start_dt = $school_syear_date[1]['START_DATE'];
                        $school_end_dt = $school_syear_date[1]['END_DATE'];
                        if ((isset($start_date) && isset($end_date) && strtotime($end_date) < strtotime($start_date)) || (strtotime($start_date) < strtotime($school_start_dt) || strtotime($start_date) > strtotime($school_end_dt)) || (strtotime($end_date) < strtotime($school_start_dt) || strtotime($end_date) > strtotime($school_end_dt))) {
                            ShowErr(_dataNotSavedBecauseStartAndEndDateIsNotValid);
                        } else {

                            DBQuery($sql);
                            if ($table_name == 'student_goal')
                                $_REQUEST['goal_id'] = mysqli_insert_id($connection);
                            else if ($table_name == 'student_goal_progress')
                                $_REQUEST['progress_id'] = mysqli_insert_id($connection);
                        }
                    }
                }


                # ---------------------------------------------------------------- #

                if ($tabl == 'student_goal_progress' && $flag != 1) {
                    $sql_p_max = 'select max(progress_id) as p_id from student_goal_progress where student_id=' . UserStudentID();
                    $res_p_max = DBQuery($sql_p_max);
                    $row_p_max = DBGet($res_p_max);

                    $sql_goal_id = 'select goal_id from student_goal_progress where progress_id=' . $row_p_max[1]['P_ID'];
                    $res_goal_id = DBQuery($sql_goal_id);
                    $row_goal_id = DBGet($res_goal_id);
                    $_REQUEST['progress_id'] = $row_p_max[1]['P_ID'];
                    $_REQUEST['goal_id'] = $row_goal_id[1]['GOAL_ID'];
                }

                # ---------------------------------------------------------------- #


                $i++;
            }
        }
    }
    unset($_REQUEST['tables']);
}

if ((!clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) || clean_param($_REQUEST['modfunc'], PARAM_NOTAGS) == 'choose_course') && !clean_param($_REQUEST['course_modfunc'], PARAM_NOTAGS)) {
    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) != 'choose_course')
        DrawBC("" . _students . " > " . ProgramTitle());

    $sql = 'SELECT GOAL_ID,GOAL_TITLE FROM student_goal WHERE SCHOOL_ID=\'' . $school_id . '\' AND SYEAR=\'' . UserSyear() . '\' AND STUDENT_ID=\'' . UserStudentID() . '\' ORDER BY START_DATE DESC';

    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    # -------------------------------------- CP_ID ------------------------------#

    $sql_cp = 'SELECT cp.COURSE_PERIOD_ID AS COURSE_PERIOD, cp.TITLE AS COURSE_PERIOD_NAME FROM course_periods cp, schedule s WHERE s.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND s.STUDENT_ID=\'' . UserStudentID() . '\'';
    $QI_cp = DBQuery($sql_cp);
    $cp_RET = DBGet($QI_cp);

    # ----------------------------------------------------------------------------------------------------------#

    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) != 'choose_course') {
        if (AllowEdit())
            $delete_button = "<INPUT type=button class=\"btn btn-primary\" value='._delete.' onClick='javascript:window.location=\"Modules.php?modname=students/Student.php&include=GoalInc&modfunc=delete&goal_id=$_REQUEST[goal_id]&progress_id=$_REQUEST[progress_id]&course_period_id=$_REQUEST[course_period_id]\"'> ";
        // ADDING & EDITING FORM


        if ($_REQUEST['progress_id']) {
            if ($_REQUEST['progress_id'] != 'new') {
                $sql = 'SELECT sg.START_DATE as GOAL_START_DATE,sg.END_DATE as GOAL_END_DATE,sgp.START_DATE,sgp.PROGRESS_NAME,sgp.PROFICIENCY,sgp.PROGRESS_DESCRIPTION FROM student_goal_progress sgp,student_goal sg
						WHERE sgp.GOAL_ID=sg.GOAL_ID AND sgp.PROGRESS_ID=\'' . $_REQUEST['progress_id'] . '\'';
                $QI = DBQuery($sql);
                $RET = DBGet($QI);
                $RET = $RET[1];
                $goal_start_date = $RET['GOAL_START_DATE'];
                $goal_end_date = $RET['GOAL_END_DATE'];
                $title = $RET['PROGRESS_NAME'];

                # -------------------------- CPID Start ---------------------------------- #

                $sql_sel_cp = 'SELECT COURSE_PERIOD_ID
						FROM student_goal_progress
						WHERE PROGRESS_ID=\'' . $_REQUEST['progress_id'] . '\'';
                $QI_sel_cp = DBQuery($sql_sel_cp);
                $RET_sel_cp = DBGet($QI_sel_cp);
                $RET_sel = $RET_sel_cp[1];
                $title_sel_cp = $RET_sel_cp[1]['COURSE_PERIOD_ID'];

                # -------------------------- CPID End ---------------------------------- #
            } else {
                $sql = 'SELECT START_DATE as GOAL_START_DATE,END_DATE as GOAL_END_DATE,GOAL_TITLE
						FROM student_goal
						WHERE GOAL_ID=\'' . $_REQUEST['goal_id'] . '\' ORDER BY GOAL_TITLE';
                $QI = DBQuery($sql);
                $RET = DBGet($QI);

                $title = $RET[1]['GOAL_TITLE'];
                $goal_start_date = $RET[1]['GOAL_START_DATE'];
                $goal_end_date = $RET[1]['GOAL_END_DATE'];
                unset($RET);
            }

            if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) != 'choose_course') {
                foreach ($subjects_RET as $type)
                    $options[$type['GOAL_ID']] = $type['GOAL_TITLE'];

                # -------------------------- For CP Option ------------------------------------------------ #

                foreach ($cp_RET as $type_cp)
                    $options_cp[$type_cp['COURSE_PERIOD']] = $type_cp['COURSE_PERIOD_NAME'];

                # -------------------------- For CP Option ------------------------------------------------ #



                $sql_gid = 'SELECT GOAL_ID FROM student_goal_progress WHERE PROGRESS_ID=\'' . $_REQUEST['progress_id'] . '\'';
                $res_gid = DBQuery($sql_gid);
                $row_gid = DBGet($res_gid);
            }

            $edit_per_stu = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=3'));
            if (User('PROFILE_ID') == 1)
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=1'));
            else if (User('PROFILE_ID') == 0)
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=0'));
            else
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=1'));

            $edit_per_teach = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=2'));

            $edit_per_prnt = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=4'));




            # ---------------------------------------------------------------------------- #
            # ----------------------------- Delete ---------------------------------------- #




            # ------------------------------ Delete ---------------------------------------- #
            $header .= '<div class="form-horizontal well">';
            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group">' . TextInput($RET['PROGRESS_NAME'], 'tables[student_goal_progress][' . $_REQUEST['progress_id'] . '][PROGRESS_NAME]', _progressPeriodName, 'size=60 maxlength=50') . '</div></div>';
            if (($_REQUEST['progress_id'] != 'new')) {
                if (((User('PROFILE') == 'admin') && isset($edit_per_adm[1]['CAN_EDIT'])) || ((User('PROFILE') == 'teacher') && isset($edit_per_teach[1]['CAN_EDIT'])) || ((User('PROFILE') == 'parent') && isset($edit_per_prnt[1]['CAN_EDIT']))) {
                    $header .= "<div class=\"col-md-6 text-right\"><a class=\"btn btn-danger btn-labeled btn-sm\" href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete&gid=" . $row_gid[1]["GOAL_ID"] . "&pid=" . $_REQUEST['progress_id'] . "'><b><i class=\"icon-cross\"></i></b> " . _deleteThisProgress . "</a></div>"; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
                }
            }
            $header .= '</div>'; //.row

            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group">' . SelectInput($RET['GOAL_ID'] ? $RET['GOAL_ID'] : $_REQUEST['goal_id'], 'tables[student_goal_progress][' . $_REQUEST['progress_id'] . '][GOAL_ID]', _goalTitle, $options, false) . '</div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
            $header .= '<div class="col-md-6"><div class="form-group">' . SelectInput($RET_sel['COURSE_PERIOD_ID'], 'tables[student_goal_progress][' . $_REQUEST['progress_id'] . '][COURSE_PERIOD_ID]', _coursePeriod, $options_cp) . '</div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
            $header .= '</div>'; //.row
            $header .= '<input type="hidden" name="req_progress_id" id="req_progress_id" value="' . $_REQUEST['progress_id'] . '" />';
            $header .= '<input type="hidden" name="hgoal" value="' . $_REQUEST['goal_id'] . '" />';


            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label text-right col-lg-4">' . _dateOfEntry . '</label><div class="col-lg-8">' . DateInputAY($RET['START_DATE'] != "" ? $RET['START_DATE'] : "", 'tables[' . $_REQUEST['progress_id'] . '][START_DATE]', 1) . '</div></div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
            $options = array(
                '0-10%' => '0-10%',
                '11-20%' => '11-20%',
                '21-30%' => '21-30%',
                '31-40%' => '31-40%',
                '41-50%' => '41-50%',
                '51-60%' => '51-60%',
                '61-70%' => '61-70%',
                '71-80%' => '71-80%',
                '81-90%' => '81-90%',
                '91-100%' => '91-100%',
            );
            $header .= '<div class="col-md-6"><div class="form-group">' . SelectInput($RET['PROFICIENCY'], 'tables[student_goal_progress][' . $_REQUEST['progress_id'] . '][PROFICIENCY]', _proficiencyScale, $options) . '</div></div>';
            $header .= '</div>'; //.row

            $header .= '<div class="row">';
            $header .= '<div class="col-md-12"><div class="form-group"><label class="control-label text-right col-lg-2">' . _progressAssessment . '</label><div class="col-lg-10">' . TextAreaInput($RET['PROGRESS_DESCRIPTION'], 'tables[student_goal_progress][' . $_REQUEST['progress_id'] . '][PROGRESS_DESCRIPTION]', '', 'rows=10 cols=57', 'true', '200px') . '<input type="hidden" name="tabl" id="tabl" value="student_goal_progress"></div></div></div>';
            $header .= '</div>'; //.row
            $header .= '</div>'; //.row

            echo $header;
            // DrawHeader($header);
        } elseif ($_REQUEST['goal_id']) {
            if ($_REQUEST['goal_id'] != 'new') {
                $sql = 'SELECT GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION
						FROM student_goal
						WHERE GOAL_ID=\'' . $_REQUEST['goal_id'] . '\' and SYEAR=\'' . UserSyear() . '\'';
                $QI = DBQuery($sql);
                $RET = DBGet($QI);
                $RET = $RET[1];
                $title = $RET['GOAL_TITLE'];
            } else {
                $title = 'newSubject';
                unset($delete_button);
            }




            $edit_per_stu = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=0'));

            if (User('PROFILE_ID') == 1)
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=1'));
            else if (User('PROFILE_ID') == 0)
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=0'));
            else
                $edit_per_adm = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=1'));


            $edit_per_teach = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=2'));

            $edit_per_prnt = DBGet(DBQuery('SELECT CAN_EDIT FROM profile_exceptions WHERE MODNAME=\'students/Student.php&category_id=5\' AND PROFILE_ID=3'));







            # ----------------------------- Delete ---------------------------------------- #


            $header .= '<div class="form-horizontal well">';
            $header .= '<div class="row">';
            $header .= '<div class="col-md-6"><div class="form-group">' . TextInput($RET['GOAL_TITLE'], 'tables[student_goal][' . $_REQUEST['goal_id'] . '][GOAL_TITLE]', _goalTitle, 'size=75 maxlength=50') . '</div></div>';

            if ($_REQUEST['goal_id'] != 'new') {
                if (((User('PROFILE') == 'admin') && isset($edit_per_adm[1]['CAN_EDIT'])) || ((User('PROFILE') == 'teacher') && isset($edit_per_teach[1]['CAN_EDIT'])) || ((User('PROFILE') == 'parent') && isset($edit_per_prnt[1]['CAN_EDIT']))) {
                    $header .= "<div class=\"col-md-6 text-right\"><a class=\"btn btn-danger btn-labeled btn-sm\" href='Modules.php?modname=students/Student.php&include=GoalInc&category_id=5&action=delete_goal&gid=" . $_REQUEST['goal_id'] . "'><b><i class=\"icon-cross\"></i></b> " . _deleteThisGoal . "</a></div>"; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
                }
            }
            $header .= '</div>'; //.row

            $header .= '<div class="row">';
            if ($_REQUEST['goal_id'] != '')
                $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _beginDate . '</label><div class="col-lg-8"><input type="hidden" name="goalId" id="goalId" value="' . $_REQUEST['goal_id'] . '" />' . DateInputAY($RET['START_DATE'] != "" ? $RET['START_DATE'] : "", 'tables[' . $_REQUEST['goal_id'] . '][START_DATE]', 2) . '</div></div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295
            else
                $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _beginDate . '</label><div class="col-lg-8"><input type="hidden" name="goalId" id="goalId" value="new" />' . DateInputAY($RET['START_DATE'] != "" ? $RET['START_DATE'] : "", 'tables[' . $_REQUEST['goal_id'] . '][START_DATE]', 2) . '</div></div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 295

            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _endDate . '</label><div class="col-lg-8">' . DateInputAY($RET['END_DATE'] != "" ? $RET['END_DATE'] : "", 'tables[' . $_REQUEST['goal_id'] . '][END_DATE]', 3) . '</div></div></div>'; // DateInput is copied from schoolsetup/MarkingPeriods.php line 296
            $header .= '</div>'; //.row

            $header .= '<div class="row">';
            $header .= '<div class="col-md-12"><div class="form-group"><label class="control-label col-lg-2 text-right">' . _goalDescription . '</label><div class="col-lg-10">' . TextAreaInput($RET['GOAL_DESCRIPTION'], 'tables[student_goal][' . $_REQUEST['goal_id'] . '][GOAL_DESCRIPTION]', '', 'rows=10 cols=70', 'true', '200px') . '<input type="hidden" name="tabl" id="tabl" value="student_goal"></div></div></div>';
            $header .= '</div>';

            $header .= '</div>'; //.form-horizontal

            echo $header;
        }
    }

    // DISPLAY THE MENU
    $LO_options = array('save' => false, 'search' => false);

    if (!$_REQUEST['goal_id'] || $_REQUEST['modfunc'] == 'choose_course')
        DrawHeaderHome('Goals', "<A HREF=ForWindow.php?modname=students/Student.php&include=GoalInc&modfunc=$_REQUEST[modfunc]&course_modfunc=search>Search</A>");

    echo '<div class="pt-20">';
    echo '<div class="row">';

    if (count($subjects_RET)) {
        if ($_REQUEST['goal_id']) {
            foreach ($subjects_RET as $key => $value) {
                if ($value['GOAL_ID'] == $_REQUEST['goal_id'])
                    $subjects_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="col-md-6">';
    $columns = array('GOAL_TITLE' => _goals);
    $link = array();
    $link['GOAL_TITLE']['link'] = "Modules.php?modname=students/Student.php&include=GoalInc";
    $link['GOAL_TITLE']['variables'] = array('goal_id' => 'GOAL_ID');
    if ($_REQUEST['modfunc'] != 'choose_course')
        $link['add']['link'] = "Modules.php?modname=students/Student.php&include=GoalInc&goal_id=new";
    else
        $link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

    ListOutput($subjects_RET, $columns, _goal, _goals, $link, array(), $LO_options);
    echo '</div>';

    if ($_REQUEST['goal_id'] && $_REQUEST['goal_id'] != 'new') {
        $sql_goal = DBQuery('SELECT GOAL_ID FROM student_goal WHERE GOAL_ID=\'' . $_REQUEST['goal_id'] . '\' and SYEAR=\'' . UserSyear() . '\'');
        $sql_goal_fetch = DBGet($sql_goal);

        $sql = "SELECT PROGRESS_ID,PROGRESS_NAME FROM student_goal_progress WHERE GOAL_ID='" . $sql_goal_fetch[1]['GOAL_ID'] . "' AND STUDENT_ID=" . UserStudentID() . " ORDER BY START_DATE DESC";
        $QI = DBQuery($sql);
        $courses_RET = DBGet($QI);

        if (count($courses_RET)) {
            if ($_REQUEST['progress_id']) {
                foreach ($courses_RET as $key => $value) {
                    if ($value['PROGRESS_ID'] == $_REQUEST['progress_id'])
                        $courses_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }

        echo '<div class="col-md-6">';
        $columns = array('PROGRESS_NAME' => _progresses);
        $link = array();
        $link['PROGRESS_NAME']['link'] = "Modules.php?modname=students/Student.php&include=GoalInc&goal_id=$_REQUEST[goal_id]";
        $link['PROGRESS_NAME']['variables'] = array('progress_id' => 'PROGRESS_ID');
        if ($_REQUEST['modfunc'] != 'choose_course')
            $link['add']['link'] = "Modules.php?modname=students/Student.php&include=GoalInc&goal_id=$_REQUEST[goal_id]&progress_id=new";
        else
            $link['PROGRESS_NAME']['link'] .= "&modfunc=$_REQUEST[modfunc]";




        ListOutput($courses_RET, $columns, _progress, _progresses, $link, array(), $LO_options);
        echo '</div>';
    }

    echo '</div>'; //.row
    echo '</div>'; //.pt-20
}

if ($_REQUEST['modname'] == 'scheduling/Courses.php' && $_REQUEST['modfunc'] == 'choose_course' && $_REQUEST['course_period_id']) {
    $course_title = DBGet(DBQuery("SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
    $course_title = $course_title[1]['TITLE'] . '<INPUT type=hidden name=tables[parent_id] value=' . $_REQUEST['course_period_id'] . '>';

    echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"$course_title</small>\"; window.close();</script>";
}
