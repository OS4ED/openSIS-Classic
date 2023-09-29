<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

include '../function/ProperDateFnc.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$_SESSION['PROFILE_ID']= $_REQUEST['profile_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $student_data = array();
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID, se.SCHOOL_ID, se.SYEAR, CONCAT(s.LAST_NAME,',',s.FIRST_NAME)AS NAME,(SELECT TITLE FROM school_gradelevels WHERE id = se. grade_id ) AS grade_level FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID")); // AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)
        foreach($RET as $student)
        {
            $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
            $student_data[] = $student;
        }
        if(count($student_data)>0)
        {
            $success = 1;
            $msg = 'Nil';
        }
        else 
        {
            $success = 0;
            $msg = 'No student found';
        }
        $data = array('student_data' => $student_data, 'success' => $success, 'msg' => $msg);
    }
    else 
    {
       $data = array('student_data' => '', 'success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('student_data' => '', 'success' => 0, 'msg' => 'Not authenticated user');
}
echo json_encode($data);
?>
