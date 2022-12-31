<?php
include '../Data.php';
include 'function/DbDateFnc.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
    
$staff_id = $_REQUEST['staff_id'];
$old_school_id = $_REQUEST['old_school_id'];
$cur_school_id = $_REQUEST['cur_school_id'];
$old_syear = $_REQUEST['old_syear'];    
$new_syear = $_REQUEST['new_syear'];     
$old_marking_period_id = $_REQUEST['old_marking_period_id'];   
$new_marking_period_id = $_REQUEST['new_marking_period_id'];
$old_subject = $_REQUEST['old_subject'];    
$new_subject = $_REQUEST['new_subject'];     
$old_course_id = $_REQUEST['old_course_id'];   
$new_course_id = $_REQUEST['new_course_id'];
$old_course_period_id = $_REQUEST['old_course_period_id'];
$new_course_period_id = $_REQUEST['new_course_period_id'];
//$is_authenticated = $_REQUEST['is_authenticated'];
$teacher_info=array();  

$teacher_info['SCHOOL_ID']=$cur_school_id;
$teacher_info['SYEAR']=$new_syear;
//$teacher_info['UserMP']=$marking_period_id;
//$teacher_info['UserSubject']=$subject;
//$teacher_info['UserCourse']=$course_id;
//$teacher_info['UserCoursePeriod']=$course_period_id;
//if($is_authenticated==1)
//{
DBQuery("UPDATE staff SET CURRENT_SCHOOL_ID='".$cur_school_id."' WHERE STAFF_ID='".$staff_id."'");
  		
		
$school_years_RET=DBGet(DBQuery("SELECT MAX(sy.SYEAR) AS SYEAR FROM school_years sy,staff s INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.school_id=sy.school_id AND sy.syear=ssr.syear AND sy.SCHOOL_ID=".$cur_school_id." AND  STAFF_ID='$staff_id'"));
$teacher_info['SYEAR']=$school_years_RET[1]['SYEAR'];

$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
if(!isset($teacher_info['UserMP']))
{
        $teacher_info['UserMP'] = GetCurrentMPWs('QTR',date('Y-m-d'),$new_syear,$cur_school_id);
        $allMP='QTR';
}
if(!$RET)
{
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . $cur_school_id . "' AND SYEAR='" . $new_syear . "' ORDER BY SORT_ORDER"));
    if (!isset($teacher_info['UserMP'])) {
        $teacher_info['UserMP'] = GetCurrentMPWs('SEM',date('Y-m-d'),$new_syear,$cur_school_id);
        $allMP='SEM';
    }
}

if(!$RET)
{
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
    if  (!isset($teacher_info['UserMP'])) {
        $teacher_info['UserMP'] = GetCurrentMPWs('FY',date('Y-m-d'),$new_syear,$cur_school_id);
        $allMP='FY';
    }	
}

//DBQuery("UPDATE staff SET CURRENT_SCHOOL_ID='".$cur_school_id."' WHERE STAFF_ID='".$staff_id."'");
$RET=  DBGet(DBQuery('SELECT s.ID,s.TITLE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=\''.$new_syear.'\' AND st.staff_id=\''.$staff_id.'\' AND (ssr.END_DATE>=curdate() OR ssr.END_DATE=\'0000-00-00\')'));
$i=0;
$data=array();
foreach($RET as $school){
    $data[$i]['id']=$school['ID'];
    $data[$i]['title']=$school['TITLE'];
    $i++;
}
$teacher_info['School_list'] = $data;

$i=0;
$data=array();
$school_years_RET=DBGet(DBQuery("SELECT YEAR(sy.START_DATE)AS START_DATE,YEAR(sy.END_DATE)AS END_DATE FROM school_years sy,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.SYEAR=sy.SYEAR AND sy.school_id=ssr.school_id AND sy.school_id=".$cur_school_id." AND st.staff_id=".$staff_id));

foreach($school_years_RET as $school_years)
{
    $data[$i]['start_date']=$school_years[START_DATE];
    $data[$i]['end_date']=$school_years[END_DATE];
    $data[$i]['title']=$school_years[START_DATE].($school_years[END_DATE]!=$school_years[START_DATE]? "-".$school_years['END_DATE']:'');

    $i++;
}
$teacher_info['Schoolyear_list'] = $data;

$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
if(!isset($teacher_info['UserMP']))
{
    $teacher_info['UserMP'] = GetCurrentMPWs('QTR',date('Y-m-d'),$staff_id,$cur_school_id);
    $allMP='QTR';
}	
if(!$RET)
{
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
    if(!isset($teacher_info['UserMP']))
    {
            $teacher_info['UserMP'] = GetCurrentMPWs('SEM',date('Y-m-d'),$staff_id,$cur_school_id);
            $allMP='SEM';
    }	
}

if(!$RET)
{
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".$cur_school_id."' AND SYEAR='".$new_syear."' ORDER BY SORT_ORDER"));
    if(!isset($teacher_info['UserMP']))
    {
            $teacher_info['UserMP'] = GetCurrentMPWs('FY',date('Y-m-d'),$staff_id,$cur_school_id);
            $allMP='FY';
    }	
}
if(count($RET))
{
    if(!$teacher_info['UserMP'])
            $teacher_info['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
    $i=0;
    $data=array();
    foreach($RET as $quarter)
    {
        $data[$i]['id']=$quarter['MARKING_PERIOD_ID'];
        $data[$i]['title']=$quarter['TITLE'];
        $i++;
    }
}
$teacher_info['marking_period_list'] = $data;

$sub = DBQuery("SELECT DISTINCT cs.TITLE, cs.SUBJECT_ID,cs.SCHOOL_ID FROM course_subjects as cs,course_details as cd WHERE cs.SUBJECT_ID=cd.SUBJECT_ID AND cd.SYEAR='".$new_syear."' AND (cd.TEACHER_ID='".$staff_id."' OR cd.SECONDARY_TEACHER_ID='".$staff_id."') AND cs.SCHOOL_ID='".$cur_school_id."' AND (cd.MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher_info['UserMP'],$new_syear,$cur_school_id).") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='".date('Y-m-d')."' AND cd.END_DATE>='".date('Y-m-d')."'))");
$RET = DBGet($sub);

if(!$teacher_info['UserSubject']){
    $teacher_info['UserSubject']=$RET[1]['SUBJECT_ID'];
}
$i=0;
$data=array();
if(count($RET)>0)
{
    foreach($RET as $subject){
        $data[$i]['id']=$subject['SUBJECT_ID'];
        $data[$i]['title']=$subject['TITLE'];
        $i++;
    }
}
$teacher_info['subject_list'] = $data;

$course = DBQuery("SELECT DISTINCT cd.COURSE_TITLE, cd.COURSE_ID,cd.SUBJECT_ID,cd.SCHOOL_ID FROM course_details cd WHERE (cd.TEACHER_ID='".$staff_id."' OR cd.SECONDARY_TEACHER_ID='".$staff_id."') AND cd.SYEAR='".$new_syear."' AND cd.SCHOOL_ID='".$cur_school_id."' AND cd.SUBJECT_ID='".$teacher_info['UserSubject']."' AND (cd.MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher_info['UserMP'],$new_syear,$cur_school_id).") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='".date('Y-m-d')."' AND cd.END_DATE>='".date('Y-m-d')."'))");					
$RET = DBGet($course);
if(!$teacher_info['UserCourse']){
    $teacher_info['UserCourse']=$RET[1]['COURSE_ID'];
}
$i=0;
$data=array();
if(count($RET)>0)
{
foreach($RET as $course){
    $data[$i]['id']=$course['COURSE_ID'];
    $data[$i]['title']=$course['COURSE_TITLE'];
    $i++;
}
$cpv_id = DBGet(DBQuery("SELECT cpv.ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='".$new_syear."' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='".$cur_school_id."' AND cp.COURSE_ID='".$teacher_info['UserCourse']."' AND (TEACHER_ID='".$staff_id."' OR SECONDARY_TEACHER_ID='".$staff_id."') AND (MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher_info['UserMP'],$new_syear,$cur_school_id).") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='".date('Y-m-d')."' AND END_DATE>='".date('Y-m-d')."')) LIMIT 0,1"));
}
$teacher_info['course_list'] = $data;

$QI = DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='".$new_syear."' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='".$cur_school_id."' AND cp.COURSE_ID='".$teacher_info['UserCourse']."' AND (TEACHER_ID='".$staff_id."' OR SECONDARY_TEACHER_ID='".$staff_id."') AND (MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$teacher_info['UserMP'],$new_syear,$cur_school_id).") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='".date('Y-m-d')."' AND END_DATE>='".date('Y-m-d')."'))");
$RET = DBGet($QI);

$fy_id = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR='".$new_syear."' AND SCHOOL_ID='".$cur_school_id."'"));
$fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

if(!$teacher_info['UserCoursePeriod']){
    $teacher_info['UserCoursePeriod'] = $RET[1]['COURSE_PERIOD_ID'];
}

$i=0;
$data=array();

if(count($RET)>0)
{
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
    $data[$i]['cpv_id'] = $period['COURSE_PERIOD_ID'];
    $data[$i]['period_id'] = $period['ID'];
    $data[$i]['title'] = $period['TITLE']." - ".$period_det['PERIOD_NAME']." - ".$period_det['DAYS'];
    $i++;
}
}
$teacher_info['course_period_list'] = $data;
$teacher_info['success'] = 1;
$teacher_info['err_msg'] = 'nil';
//}
//else 
//{
//    $teacher_info['success'] = 0;
//    $teacher_info['SCHOOL_ID']='';
//    $teacher_info['SYEAR']='';
//    $teacher_info['School_list']='';
//    $teacher_info['Schoolyear_list']='';
//    $teacher_info['UserMP']='';
//    $teacher_info['marking_period_list']='';
//    $teacher_info['UserSubject']='';
//    $teacher_info['subject_list']='';
//    $teacher_info['UserCourse']='';
//    $teacher_info['course_list']='';
//    $teacher_info['UserCoursePeriod']='';
//    $teacher_info['course_period_list']='';
//    $teacher_info['err_msg']='User is not authenticated';
//}
echo json_encode($teacher_info);
?>