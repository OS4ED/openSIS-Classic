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
$_SESSION['UserSchool']=$cur_school_id = $_REQUEST['school_id'];
$_SESSION['UserSyear']=$new_syear = $_REQUEST['syear'];
$mp_id=$_REQUEST['mp_id'];
$mp_type=$_REQUEST['mp_type'];

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
$sub = DBQuery("SELECT DISTINCT cs.TITLE, cs.SUBJECT_ID,cs.SCHOOL_ID FROM course_subjects as cs,course_details as cd WHERE cs.SUBJECT_ID=cd.SUBJECT_ID AND cd.SYEAR='".$new_syear."' AND (cd.TEACHER_ID='".$staff_id."' OR cd.SECONDARY_TEACHER_ID='".$staff_id."') AND cs.SCHOOL_ID='".$cur_school_id."' AND (cd.MARKING_PERIOD_ID IN (".GetAllMPWs($mp_type,$mp_id,$new_syear,$cur_school_id).") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='".date('Y-m-d')."' AND cd.END_DATE>='".date('Y-m-d')."'))"); 
$RET = DBGet($sub);

    
$i=0;
$data=array();
if(count($RET)>0)
{
    $teacher_info['UserSubject']=$RET[1]['SUBJECT_ID'];
    foreach($RET as $subject){
        $data[$i]['id']=$subject['SUBJECT_ID'];
        $data[$i]['title']=$subject['TITLE'];
        $i++;
    }
    
    $teacher_info['subject_list'] = $data;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'nil';
}
else 
{
    $data[0]['id']='';
    $data[0]['title']='N/A';
    $teacher_info['UserSubject']=$data;
    $teacher_info['subject_list'] = $data;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'no subject found';
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
