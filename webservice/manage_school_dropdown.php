<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';

header('Content-Type: application/json');

$teacher_info=array();
$teacher=array();
$staff_id = $_REQUEST['staff_id'];
$school_id = $_REQUEST['school_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$staff_id && $auth_data['user_profile']=='teacher')
    {
DBQuery("UPDATE staff SET CURRENT_SCHOOL_ID='".$school_id."' WHERE STAFF_ID='".$staff_id."'");

$i=0;
$data=array();
$school_years_RET=DBGet(DBQuery("SELECT YEAR(sy.START_DATE)AS START_DATE,YEAR(sy.END_DATE)AS END_DATE FROM school_years sy,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.SYEAR=sy.SYEAR AND sy.school_id=ssr.school_id AND sy.school_id=".$school_id." AND st.staff_id=".$staff_id));

if(count($school_years_RET)>0)
{
    $school_years1_RET=DBGet(DBQuery("SELECT MAX(sy.SYEAR) AS SYEAR FROM school_years sy,staff s INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.school_id=sy.school_id AND sy.syear=ssr.syear AND sy.SCHOOL_ID=".$school_id." AND  STAFF_ID='$staff_id'"));
    $teacher_info['SYEAR']=$school_years1_RET[1]['SYEAR'];
    foreach($school_years_RET as $school_years)
    {
        $data[$i]['start_date']=$school_years[START_DATE];
        $data[$i]['end_date']=$school_years[END_DATE];
        $data[$i]['title']=$school_years[START_DATE].($school_years[END_DATE]!=$school_years[START_DATE]? "-".$school_years['END_DATE']:'');

        $i++;
    }
    $teacher_info['Schoolyear_list'] = $data;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'nil';
}
else 
{
    $teacher_info['SYEAR']= '';
    $teacher_info['Schoolyear_list'] = $data;
    $teacher_info['success'] = 0;
    $teacher_info['err_msg'] = 'no school year found';
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
echo json_encode($teacher_info);
?>
