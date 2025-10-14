<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];

$sql = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE SCHOOL_ID=\''.  UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND STUDENT_ID=\''.UserStudentIDWs().'\' AND GOAL_ID=\''.$_REQUEST['goal_id'].'\' ORDER BY START_DATE DESC';

$QI = DBQuery($sql);
$goals_RET = DBGet($QI);
foreach($goals_RET as $goal)
{
    $sql = 'SELECT sgp.GOAL_ID,sgp.START_DATE,sgp.PROGRESS_ID,sgp.PROGRESS_NAME,sgp.PROFICIENCY,sgp.PROGRESS_DESCRIPTION,sgp.COURSE_PERIOD_ID,(SELECT TITLE FROM course_periods WHERE course_period_id = sgp.course_period_id) AS COURSE_PERIOD FROM student_goal_progress sgp
            WHERE sgp.GOAL_ID=\''.$goal['GOAL_ID'].'\'';
    $QI = DBQuery($sql);
    $progressRET = DBGet($QI);
    $progress_period = array();
    foreach($progressRET as $prog)
    {
        $progress_period[]=$prog;
    }
    $goal['PROGRESS_DATA'] = $progress_period;
    $goals[]=$goal;
}

$data['goals'] = $goals;
if(count($goals)>0)
{
    $data['goals_success'] = 1;
}
else 
{
    $data['goals_success'] = 0;
}
    }
    else 
    {
       $data = array('success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}
echo json_encode($data);    
?>
