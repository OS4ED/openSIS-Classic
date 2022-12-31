<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/app_functions.php';
include 'function/function.php';

include 'function/ParamLib.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$cpv_id = $_REQUEST['cpv_id'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];

$cp_id = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));

$_SESSION['UserCoursePeriod'] = $course_period_id = $cp_id[1]['COURSE_PERIOD_ID'];
$_SESSION['UserPeriod'] = $period_id = $cp_id[1]['PERIOD_ID'];

$current_completed = count(DBGet(DBQuery('SELECT \'\' FROM grades_completed WHERE STAFF_ID=\''.UserWs('STAFF_ID').'\' AND MARKING_PERIOD_ID=\''.$_REQUEST['mp_id'].'\' AND PERIOD_ID=\''.UserPeriod().'\'')));
$grade_start_date=DBGet(DBQuery('SELECT `POST_START_DATE` FROM `marking_periods` WHERE `marking_period_id`='.$mp_id.' AND does_grades=\'Y\''));
$grade_end_date=DBGet(DBQuery('SELECT `POST_END_DATE` FROM `marking_periods` WHERE `marking_period_id`='.$mp_id.' AND does_grades=\'Y\''));
$grade_start_time=strtotime( $grade_start_date[1]['POST_START_DATE']);
$grade_end_time=strtotime( $grade_end_date[1]['POST_END_DATE']);
$current_time= strtotime(date("Y-m-d"));
$grade_status='';
if($current_time >=$grade_start_time && $current_time<=$grade_end_time && $grade_start_time!='' && $grade_end_time!='' ){
    $grade_status='open';
}else if($current_time >= $grade_end_time && $grade_end_time!=''){
    $grade_status='closed';
}else if($current_time <= $grade_start_time){
    $grade_status='not open yet';
}else{
    $grade_status='not set yet';
}

if(!isset($_openSIS['allow_edit']))
{
	// allow teacher edit if selected date is in the current quarter or in the corresponding grade posting period
	
	$edit_days_before = '';
        $edit_days_after = '';
	$current_qtr_id = $mp_id;
	$time = strtotime(DBDate('postgres'));
	
	if((($time>=strtotime(GetMP($mp_id,'POST_START_DATE',$_REQUEST['syear'],$_REQUEST['school_id'])) && ($time<=strtotime(GetMP($mp_id,'POST_END_DATE',$_REQUEST['syear'],$_REQUEST['school_id'])))) && ($edit_days_before=='' || strtotime($date)<=$time+$edit_days_before*86400) && ($edit_days_after=='' || strtotime($date)>=$time-$edit_days_after*86400)))
	{
		$_openSIS['allow_edit'] = true;
	}
        else 
{
                $_openSIS['allow_edit'] = false;
}
}

if(AllowEdit('grades/InputFinalGrades.php')){
        $msg =($current_completed?'These grades are complete':'Grade reporting is open for this marking period').(AllowEdit('grades/InputFinalGrades.php')?' | You can edit these grades':' | Grade reporting begins on : '.date("M d, Y ",strtotime( $grade_start_date[1]['POST_START_DATE'])));
        if(AllowEdit('grades/InputFinalGrades.php'))
            $success = 1;
        else 
            $success = 0;
}
else if($grade_status =='not open yet')
{
        $msg =($current_completed?'These grades are complete':'Grade reporting is not open for this marking period').(AllowEdit('grades/InputFinalGrades.php')?' | You can edit these grades':' | Grade reporting starts on: '.date("M d, Y ",strtotime( $grade_start_date[1]['POST_START_DATE'])).' and ends on : '.date("M d, Y ",strtotime( $grade_end_date[1]['POST_END_DATE'])));
        if($current_completed && AllowEdit('grades/InputFinalGrades.php'))
            $success = 1;
        else 
            $success = 0;
}else if($grade_status =='closed'){
        $msg =($current_completed?'These grades are complete':'These grades are complete').(AllowEdit('grades/InputFinalGrades.php')?' | You can edit these grades':' | Grade reporting ended for this marking period on : '.date("M d, Y ",strtotime( $grade_end_date[1]['POST_END_DATE'])));
        if($current_completed && AllowEdit('grades/InputFinalGrades.php'))
            $success = 1;
        else 
            $success = 0;
}else if($grade_status=='not set yet'){
        $msg ='Grade reporting date has not set for this marking period';
        $success = 0;
}

$data = array('success'=>$success,'msg'=>$msg);
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
