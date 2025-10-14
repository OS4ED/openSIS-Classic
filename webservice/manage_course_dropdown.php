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
$cur_school_id = $_REQUEST['school_id'];
$new_syear = $_REQUEST['syear'];
$course_id=$_REQUEST['course_id'];
$allMP = $_REQUEST['mp_type'];
$teacher['UserMP'] = $_REQUEST['mp_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$staff_id && $auth_data['user_profile']=='teacher')
    {
//$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
//if(!isset($teacher['UserMP']))
//{
//        $teacher['UserMP'] = GetCurrentMPWs('QTR',date('Y-m-d'),$new_syear,$cur_school_id);
//        $allMP='QTR';
//}
//if(!$RET)
//{
//    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . $cur_school_id . "' AND SYEAR='" . $new_syear . "' ORDER BY SORT_ORDER"));
//    if (!isset($teacher['UserMP'])) {
//        $teacher['UserMP'] = GetCurrentMPWs('SEM',date('Y-m-d'),$new_syear,$cur_school_id);
//        $allMP='SEM';
//    }
//}
//
//if(!$RET)
//{
//    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
//    if  (!isset($teacher['UserMP'])) {
//        $teacher['UserMP'] = GetCurrentMPWs('FY',date('Y-m-d'),$new_syear,$cur_school_id);
//        $allMP='FY';
//    }	
//}

$QI = DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='".$new_syear."' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='".$cur_school_id."' AND cp.COURSE_ID='".$course_id."' AND (TEACHER_ID='".$staff_id."' OR SECONDARY_TEACHER_ID='".$staff_id."') AND (MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher['UserMP'],$new_syear,$cur_school_id).") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='".date('Y-m-d')."')) group by (cp.COURSE_PERIOD_ID)"); // AND END_DATE>='".date('Y-m-d')."'
$RET = DBGet($QI);

$fy_id = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR='".$new_syear."' AND SCHOOL_ID='".$cur_school_id."'"));
$fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

    

$i=0;
$datacp=array();

if(count($RET)>0)
{
    $teacher_info['UserCoursePeriod'] = $RET[1]['COURSE_PERIOD_ID'];
    $teacher_info['UserCoursePeriodVar'] = $RET[1]['ID'];
    
    foreach($RET as $period)
    {

        $period_det=DBGet(DBQuery('SELECT sp.TITLE as PERIOD_NAME,cpv.DAYS,cpv.COURSE_PERIOD_DATE FROM course_period_var cpv,school_periods sp WHERE cpv.ID='.$period['ID'].' AND cpv.PERIOD_ID=sp.PERIOD_ID'));
        $period_det=$period_det[1];
        $days_arr=array("Monday"=>'M',"Tuesday"=>'T',"Wednesday"=>'W',"Thursday"=>'H',"Friday"=>'F',"Saturday"=>'S',"Sunday"=>'U');
        if($period_det['DAYS']=='')
        {
            $period_det['DAYS']=date('l',strtotime($period_det['COURSE_PERIOD_DATE']));
            $period_det['DAYS']=$days_arr[$period_det['DAYS']];
        }
        $datacp[$i]['cp_id'] = $period['COURSE_PERIOD_ID'];
        $datacp[$i]['cpv_id'] = $period['ID'];
        $datacp[$i]['period_id'] = $period['PERIOD_ID'];
        $datacp[$i]['title'] = $period['TITLE']." - ".$period_det['PERIOD_NAME'];
        $i++;
    }
    
$teacher_info['course_period_list'] = $datacp;
$teacher_info['success'] = 1;
$teacher_info['err_msg'] = 'nil';
}
else 
{
    $datacp[0]['cp_id'] = '';
    $datacp[0]['cpv_id'] = '';
    $datacp[0]['period_id'] = '';
    $datacp[0]['title'] = 'N/A';
    $teacher_info['UserCoursePeriod'] = '';
    $teacher_info['UserCoursePeriodVar'] = '';    
    $teacher_info['course_period_list'] = $datacp;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'no course period found';
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
