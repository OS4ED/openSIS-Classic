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
$subject_id=$_REQUEST['subject_id'];
$allMP = $_REQUEST['mp_type'];
$teacher['UserMP'] = $_REQUEST['mp_id'];

//$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
//    if(!isset($teacher['UserMP']))
//    {
//            $teacher['UserMP'] = GetCurrentMPWs('QTR',date('Y-m-d'),$new_syear,$cur_school_id);
//            $allMP='QTR';
//    }
//    if(!$RET)
//    {
//        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . $cur_school_id . "' AND SYEAR='" . $new_syear . "' ORDER BY SORT_ORDER"));
//        if (!isset($teacher['UserMP'])) {
//            $teacher['UserMP'] = GetCurrentMPWs('SEM',date('Y-m-d'),$new_syear,$cur_school_id);
//            $allMP='SEM';
//        }
//    }
//
//    if(!$RET)
//    {
//        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
//        if  (!isset($teacher['UserMP'])) {
//            $teacher['UserMP'] = GetCurrentMPWs('FY',date('Y-m-d'),$new_syear,$cur_school_id);
//            $allMP='FY';
//        }	
//    }
$course = DBQuery("SELECT DISTINCT cd.COURSE_TITLE, cd.COURSE_ID,cd.SUBJECT_ID,cd.SCHOOL_ID FROM course_details cd WHERE (cd.TEACHER_ID='".$staff_id."' OR cd.SECONDARY_TEACHER_ID='".$staff_id."') AND cd.SYEAR='".$new_syear."' AND cd.SCHOOL_ID='".$cur_school_id."' AND cd.SUBJECT_ID='".$subject_id."' AND (cd.MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher['UserMP'],$new_syear,$cur_school_id).") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='".date('Y-m-d')."'))"); // AND cd.END_DATE>='".date('Y-m-d')."'				
$RET = DBGet($course);
$i=0;
$data=array();
if(count($RET)>0)
{
    $teacher_info['UserCourse']=$RET[1]['COURSE_ID'];
    foreach($RET as $course){
        $data[$i]['id']=$course['COURSE_ID'];
        $data[$i]['title']=$course['COURSE_TITLE'];
        $i++;
    }
    $cpv_id = DBGet(DBQuery("SELECT cpv.ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='".$new_syear."' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='".$cur_school_id."' AND cp.COURSE_ID='".$teacher_info['UserCourse']."' AND (TEACHER_ID='".$staff_id."' OR SECONDARY_TEACHER_ID='".$staff_id."') AND (MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher['UserMP'],$new_syear,$cur_school_id).") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='".date('Y-m-d')."')) LIMIT 0,1")); // AND END_DATE>='".date('Y-m-d')."'
    
    $teacher_info['course_list'] = $data;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'nil';
}
else 
{
    $data[0]['id']='';
    $data[0]['title']='N/A';
    $teacher_info['UserCourse']='';
    $teacher_info['course_list'] = $data;
    $teacher_info['success'] = 1;
    $teacher_info['err_msg'] = 'no course found';
}

echo json_encode($teacher_info);
?>
