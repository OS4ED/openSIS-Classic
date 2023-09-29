<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';
include 'function/WidgetsFnc.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
$cpv_id = $_REQUEST['cpv_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$activity = Widgets('activity');
$course = Widgets('course');
$req = Widgets('request');

        $cp_data = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));
        $_SESSION['UserCoursePeriod'] = $course_period_id = $cp_data[1]['COURSE_PERIOD_ID'];
        $_SESSION['UserPeriod'] = $period_id = $cp_data[1]['PERIOD_ID'];

$list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER"));
$grade_arr[] = array('ID'=>'','TITLE'=>'Not Specified');
foreach($list as $value)
        $grade_arr[] = array('ID'=>$value['TITLE'],'TITLE'=>$value['TITLE']);

$data = array('activity_data'=>$activity,'grade_data'=>$grade_arr,'course_data'=>$course,'request_data'=>$req);

        if($_REQUEST['action_type']=='search')
        {
            if(isset($_REQUEST['med_date']) && $_REQUEST['med_date']!='')
            {
                // Date format should be 2015-11-02
                $med_dates = explode('-', $_REQUEST['med_date']);
                $_REQUEST['med_year'] = $med_dates[0];
                $_REQUEST['med_month'] = $med_dates[1];
                $_REQUEST['med_day'] = $med_dates[2];
            }
            if(isset($_REQUEST['imm_date']) && $_REQUEST['imm_date']!='')
            {
                // Date format should be 2015-11-02
                $imm_dates = explode('-', $_REQUEST['imm_date']);
                $_REQUEST['imm_year'] = $imm_dates[0];
                $_REQUEST['imm_month'] = $imm_dates[1];
                $_REQUEST['imm_day'] = $imm_dates[2];
            }
            if(isset($_REQUEST['ma_date']) && $_REQUEST['ma_date']!='')
            {
                // Date format should be 2015-11-02
                $ma_dates = explode('-', $_REQUEST['ma_date']);
                $_REQUEST['ma_year'] = $ma_dates[0];
                $_REQUEST['ma_month'] = $ma_dates[1];
                $_REQUEST['ma_day'] = $ma_dates[2];
            }
            if(isset($_REQUEST['nv_date']) && $_REQUEST['nv_date']!='')
            {
                // Date format should be 2015-11-02
                $nv_dates = explode('-', $_REQUEST['nv_date']);
                $_REQUEST['nv_year'] = $nv_dates[0];
                $_REQUEST['nv_month'] = $nv_dates[1];
                $_REQUEST['nv_day'] = $nv_dates[2];
            }
            $students_RET = GetStuListWs($extra,$teacher_id);

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
