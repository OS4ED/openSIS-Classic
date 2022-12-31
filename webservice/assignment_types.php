<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include '../functions/DbDateFnc.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/PercentFnc.php';

header('Content-Type: application/json');
$type = $_REQUEST['type'];
$teacher_id = $_REQUEST['staff_id'];
$cpv_id = $_REQUEST['cpv_id'];
$school_id = $_REQUEST['school_id'];

$auth_data = check_auth();
if (count($auth_data) > 0) {
    if ($auth_data['user_id'] == $teacher_id && $auth_data['user_profile'] == 'teacher') {

        $cp_RET = array();
        if ($cpv_id != '') {
            $cp_sql = 'SELECT cp.COURSE_ID,cp.COURSE_PERIOD_ID FROM course_period_var cpv LEFT JOIN course_periods cp ON cp.COURSE_PERIOD_ID = cpv.COURSE_PERIOD_ID WHERE cpv.ID =' . $cpv_id;
            $cp_RET = DBGet(DBQuery($cp_sql));
        }
        if (count($cp_RET) > 0) {
            $course_id = $_SESSION['UserCourse'] = $cp_RET[1]['COURSE_ID'];
            $course_period_id = $cp_RET[1]['COURSE_PERIOD_ID'];
        } else {
            $course_id = '';
            $course_period_id = '';
        }
        $_SESSION['STAFF_ID'] = $teacher_id;
        $table = 'gradebook_assignment_types';

        $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $teacher_id . '\' AND SCHOOL_ID=' . $school_id . ' AND PROGRAM=\'Gradebook\''));

        if (count($config_RET))
            foreach ($config_RET as $value) {
                $programconfig[$value['TITLE']] = $value['VALUE'];
            }
        else
            $programconfig = true;

        if ($course_period_id != '') {
            if ($type == 'view') {
                $sql = ' SELECT ASSIGNMENT_TYPE_ID,TITLE,(FINAL_GRADE_PERCENT*100) AS FINAL_GRADE_PERCENT
                     FROM (
                        ( select gat.ASSIGNMENT_TYPE_ID,gat.TITLE,gat.FINAL_GRADE_PERCENT  FROM gradebook_assignment_types gat where gat.COURSE_PERIOD_ID=\'' . $course_period_id . '\' )
                      UNION  
                       (SELECT gat.ASSIGNMENT_TYPE_ID as ASSIGNMENT_TYPE_ID,concat(gat.TITLE,\' (\',cp.title,\')\') as TITLE,gat.FINAL_GRADE_PERCENT as FINAL_GRADE_PERCENT FROM gradebook_assignment_types gat , gradebook_assignments ga, course_periods cp
                        where cp.course_period_id =gat.course_period_id and gat.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
                        AND ga.COURSE_PERIOD_ID IS NULL AND ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . $teacher_id . '\' ) 
                      )as t
                      GROUP BY ASSIGNMENT_TYPE_ID';

                $QI = DBQuery($sql);
                $types_RET = DBGet($QI);
                $assignment_type = array();
                $total = 0;
                foreach ($types_RET as $at) {
                    if ($at['FINAL_GRADE_PERCENT'] != 'NULL')
                        $total += $at['FINAL_GRADE_PERCENT'];
                    if ($at['FINAL_GRADE_PERCENT'] == 'NULL' || $at['FINAL_GRADE_PERCENT'] == '')
                        $at['FINAL_GRADE_PERCENT'] = 0;
                    $assignment_type[] = $at;
                }
                if (count($assignment_type) > 0) {
                    $data['assignment_types'] = $assignment_type;
                    $data['total_percentage'] = $total;
                    $data['weight'] = (isset($programconfig['WEIGHT']) && $programconfig['WEIGHT'] == 'Y') ? 'Y' : 'N';
                    $data['success'] = 1;
                    $data['err_msg'] = 'nil';
                } else {
                    $data['weight'] = (isset($programconfig['WEIGHT']) && $programconfig['WEIGHT'] == 'Y') ? 'Y' : 'N';
                    $data['success'] = 0;
                    $data['err_msg'] = 'No assignment type found.';
                }
            } elseif ($type == 'add_view') {
                $sql = 'SELECT sum(FINAL_GRADE_PERCENT) AS TOTAL_PERCENT FROM gradebook_assignment_types WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\'';
                $QI = DBQuery($sql);
                $RET = DBGet($QI, array('FINAL_GRADE_PERCENT' => '_makePercent'));
                $RET = $RET[1];
                $title = 'New Assignment Type';

                $data['grade_percent'] = $RET;
                $data['title'] = $title;
            } elseif ($type == 'add_submit') {
                $attendance_type = $_REQUEST["assignment_type"];
                $columns = json_decode($attendance_type, TRUE);
                $columns = $columns[0];


                if ($table == 'gradebook_assignment_types' && $programconfig['WEIGHT'] == 'Y' && $columns['FINAL_GRADE_PERCENT'] != '')
                    $columns['FINAL_GRADE_PERCENT'] = preg_replace('[^0-9.]', '', clean_param($columns['FINAL_GRADE_PERCENT'], PARAM_PERCENT)) / 100;

                $sql = 'INSERT INTO ' . $table . ' ';

                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'gradebook_assignment_types\''));
                // $id[1]['ID']= $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                $fields = 'STAFF_ID,COURSE_ID,COURSE_PERIOD_ID,';
                $values = '\'' . $teacher_id . '\',\'' . $course_id . '\',\'' . $course_period_id . '\',';
                // $_REQUEST['assignment_type_id'] = $id;

                $go = false;

                foreach ($columns as $column => $value) {
                    if ($value != '') {
                        $fields .= $column . ',';

                        $values .= '\'' . str_replace("'", "''",  htmlspecialchars_decode($value)) . '\',';
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                if ($go) {
                    DBQuery($sql);
                    if ($table == 'gradebook_assignment_types')
                        $_REQUEST['assignment_type_id'] = mysqli_insert_id($connection);
                    //            DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
                    $data['success'] = 1;
                    $data['err_msg'] = 'Nil';
                } else {
                    $data['success'] = 0;
                    $data['err_msg'] = 'Assignment type creation failed.';
                }
            } elseif ($type == 'edit_view') {
                $sql = 'SELECT at.TITLE,at.FINAL_GRADE_PERCENT,
                        (SELECT sum(FINAL_GRADE_PERCENT) FROM gradebook_assignment_types WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\') AS TOTAL_PERCENT
                        FROM gradebook_assignment_types at
                        WHERE at.ASSIGNMENT_TYPE_ID=\'' . $_REQUEST['assignment_type_id'] . '\'';
                $QI = DBQuery($sql);
                $RET = DBGet($QI, array('FINAL_GRADE_PERCENT' => '_makePercent'));
                if (count($RET) > 0) {
                    $RET = $RET[1];

                    $title = $RET['TITLE'];
                    $data['success'] = 1;
                    $data['err_msg'] = 'Nil';
                    $data['title'] = $RET['TITLE'];
                    $data['final_grade_percent'] = $RET['FINAL_GRADE_PERCENT'];
                } else {
                    $data['success'] = 0;
                    $data['err_msg'] = 'No data found';
                }
            } elseif ($type == 'edit_submit') {
                $attendance_type = $_REQUEST["assignment_type"];
                $columns = json_decode($attendance_type, TRUE);
                $columns = $columns[0];

                if (trim($columns['TITLE']) != "" || !isset($columns['TITLE'])) {
                    $columns['TITLE'] = urldecode($columns['TITLE']);
                    //            if($columns['ASSIGNMENT_TYPE_ID'] && $columns['ASSIGNMENT_TYPE_ID']!=$_REQUEST['assignment_type_id'])
                    //                $_REQUEST['assignment_type_id'] = $columns['ASSIGNMENT_TYPE_ID'];
                    if ($table == 'gradebook_assignment_types' && $programconfig['WEIGHT'] == 'Y' && $columns['FINAL_GRADE_PERCENT'] != '')
                        $columns['FINAL_GRADE_PERCENT'] = preg_replace('[^0-9.]', '', urldecode($columns['FINAL_GRADE_PERCENT'])) / 100;

                    $sql = 'UPDATE ' . $table . ' SET ';

                    foreach ($columns as $column => $value) {
                        $value = str_replace("'", "''", htmlspecialchars_decode($value));
                        $sql .= $column . '=\'' . $value . '\',';
                    }
                    $sql = substr($sql, 0, -1) . ' WHERE ' . substr($table, 10, -1) . '_ID=\'' . $_REQUEST['assignment_type_id'] . '\'';
                    $go = true;

                    if ($go) {
                        DBQuery($sql);
                        //                DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
                        $data['success'] = 1;
                        $data['err_msg'] = 'Nil';
                    } else {
                        $data['success'] = 0;
                        $data['err_msg'] = 'Data couldnot be updated';
                    }
                } else {
                    $data['success'] = 0;
                    $data['err_msg'] = 'Title cannot be left blank';
                }
            } elseif ($type == 'delete') {
                $asgmt_data = DBGet(DBQuery('select assignment_id from gradebook_assignments where assignment_type_id=' . $_REQUEST['assignment_type_id'] . ''));
                if (count($asgmt_data) > 0) {
                    $data['success'] = 0;
                    $data['err_msg'] = 'Gradebook Assignment Type cannot be deleted because assignments are created in this assignment type.';
                } else {
                    DBQuery('DELETE FROM gradebook_assignment_types  WHERE assignment_type_id=\'' . $_REQUEST['assignment_type_id'] . '\'');
                    DBQuery('DELETE FROM gradebook_assignments WHERE assignment_type_id=\'' . $_REQUEST['assignment_type_id'] . '\'');

                    DBQuery('DELETE FROM gradebook_grades WHERE assignment_id=\'' . $asgmt_data[1]['assignment_id'] . '\'');
                    $data['success'] = 1;
                    $data['err_msg'] = 'Nil';
                }
            }
        } else {
            $data['success'] = 0;
            $data['err_msg'] = 'You don\'t have a course this period.';
        }
    } else {
        $data = array('success' => 0, 'msg' => 'Not authenticated user');
    }
} else {
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}

function _makePercent($value, $column)
{
    return Percent($value, 2);
}
echo json_encode($data);
