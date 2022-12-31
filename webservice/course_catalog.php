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
//$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
//$cpv_id = $_REQUEST['cpv_id'];
//$cp_data = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));
//$_SESSION['UserCoursePeriod'] = $course_period_id = $cp_data[1]['COURSE_PERIOD_ID'];

$mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\' FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\' FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY 3,4'));
unset($options);
$mp_data = array();
$sub_data = array();
$course_data = array();
$schedule_data = array();
$mp_data[0]['MARKING_PERIOD_ID']='';
$mp_data[0]['TITLE']='All';
$mp_data[0]['SHORT_NAME']='';
if(count($mp_RET))
{
    foreach($mp_RET as $key=>$value)
    {
        if($value['MARKING_PERIOD_ID']==$_REQUEST['mp_id'])
            $value['row_color'] = PreferencesWs('HIGHLIGHT');
        $mp_data[]=$value;
    }
    if(count($mp_data)>0)
    {
        $data['mp_data'] = $mp_data;
        $data['mp_success'] = 1;
        $data['mp_success_msg'] = 'Nil';
    }
}
else 
{
    $data['mp_data'] = $mp_data;
    $data['mp_success'] = 0;
    $data['mp_success_msg'] = 'No Class List Found';
}

if($_REQUEST['mp_id'] && $_REQUEST['mp_id']!='')
{
    $sql = 'SELECT subject_id,TITLE FROM course_subjects WHERE SCHOOL_ID=\''.UserSchool().'\' ORDER BY TITLE';
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);
    $sub_data[0]['SUBJECT_ID']='';
    $sub_data[0]['TITLE']='All';
    if(count($subjects_RET)>0)
    {
//        if($_REQUEST['subject_id'])
//        {
            foreach($subjects_RET as $key=>$value)
            {
                if($value['SUBJECT_ID']==$_REQUEST['subject_id'])
                    $value['row_color'] = PreferencesWs('HIGHLIGHT');
                $sub_data[] = $value;
            }
//        }
        if(count($sub_data)>0)
        {
            $data['sub_data'] = $sub_data;
            $data['sub_success'] = 1;
            $data['sub_success_msg'] = 'Nil';
        }
    }
    else 
    {
        $data['sub_data'] = $sub_data;
        $data['sub_success'] = 0;
        $data['sub_success_msg'] = 'No Subject Found';
    }
}
else 
{
    $data['sub_data'] = $sub_data;
    $data['sub_success'] = 0;
    $data['sub_success_msg'] = 'No Subject Found';
}

if($_REQUEST['subject_id'] && $_REQUEST['subject_id']!='' )
{
    $sql = 'SELECT COURSE_ID,TITLE FROM courses WHERE SUBJECT_ID=\''.$_REQUEST['subject_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY TITLE';
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    $course_data[0]['COURSE_ID']='';
    $course_data[0]['TITLE']='All';
    if(count($courses_RET))
    {
//        if($_REQUEST['course_id'] && $_REQUEST['course_id']!='')
//        {
            foreach($courses_RET as $key=>$value)
            {
                if($value['COURSE_ID']==$_REQUEST['course_id'])
                    $value['row_color'] = PreferencesWs('HIGHLIGHT');
                $course_data[] = $value;
            }
//        }
        if(count($course_data)>0)
        {
            $data['course_data'] = $course_data;
            $data['course_success'] = 1;
            $data['course_success_msg'] = 'Nil';
        }

    }
    else 
    {
        $data['course_data'] = $course_data;
        $data['course_success'] = 0;
        $data['course_success_msg'] = 'No course Found';
    }
} 
else 
{
    $data['course_data'] = $course_data;
    $data['course_success'] = 0;
    $data['course_success_msg'] = 'No course Found';
}

$dli = $_REQUEST['degree_level_id'];
$pli = $_REQUEST['program_level_id'];
$sli = $_REQUEST['subject_id'];
$cli = $_REQUEST['course_id'];
$mp  = $_REQUEST['mp_id'];
$mp_name = $_REQUEST['mp_name'];


    if($sli!='')
    $s_ret = DBGet(DBQuery("select title from course_subjects where subject_id='".$sli."'"));

   if($cli!='')
     $c_ret = DBGet(DBQuery("select title from courses where course_id='".$cli."'"));

   if($mp!='')
    {
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' and marking_period_id=\''.$mp.'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\' FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' and marking_period_id=\''.$mp.'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\'  FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' and marking_period_id=\''.$mp.'\' ORDER BY 3,4';
        $mp_ret1 = DBGet(DBQuery($sql));
        $mp_name = $mp_ret1[1]['TITLE'];
    }

    if($mp=='')
    {
        $where = '';
        $heading= "All available classes";
    }
    else
    {
        if($sli==''){
            $where = 'and marking_period_id=\''.$mp.'\' and course_id in (select course_id from  courses where subject_id in (select subject_id from course_subjects))';
            $heading ="All available classes for <font color='black'>".$mp_name." -> ".$d_ret[1]['TITLE']." -> ".$p_ret[1]['TITLE']."</font>";
        }
        else{
            if($cli=='')
            {
                $where = 'and marking_period_id=\''.$mp.'\' and course_id in (select Course_Id from courses where subject_id = \''.$_REQUEST['subject_id'].'\' and School_Id=\''.UserSchool().'\')';
              
                $heading ="All available classes for <font color='black'>".$mp_name." -> ".$d_ret[1]['TITLE']." -> ".$p_ret[1]['TITLE']." -> ".$s_ret[1]['TITLE']."</font>";
            }
            else
            {
                $where = 'and marking_period_id=\''.$mp.'\' and course_id=\''.$cli.'\'';
                $heading ="All available classes for <font color='black'>".$mp_name." -> ".$d_ret[1]['TITLE']." -> ".$p_ret[1]['TITLE']." -> ".$s_ret[1]['TITLE']." -> ".$c_ret[1]['TITLE']."</font>";
            }
        }
    }


				
	$sql = 'select
                (select title from courses where course_id=course_periods.course_id) as course,
                (select title from course_subjects where subject_id=(select subject_id from courses where course_id=course_periods.course_id)) as subject,
                short_name,(select CONCAT(START_TIME,\' - \',END_TIME,\' \') from school_periods where period_id=course_period_var.period_id) as period_time, (select title from school_periods where period_id=course_period_var.period_id) as period, marking_period_id, (select title from marking_periods where marking_period_id=course_periods.marking_period_id) as mp,
                (select CONCAT(LAST_NAME,\' \',FIRST_NAME,\' \') from staff where staff_id=course_periods.teacher_id) as teacher, rooms.title as location,days,course_periods.course_period_id,course_periods.begin_date,course_periods.end_date,course_periods.SCHEDULE_TYPE,course_period_var.ID AS CPV_ID,course_period_var.COURSE_PERIOD_DATE from course_periods,course_period_var,rooms where course_periods.school_id=\''.UserSchool().'\' and course_period_var.room_id=rooms.room_id and course_periods.course_period_id=course_period_var.course_period_id and course_periods.syear=\''.UserSyear().'\' '.$where.'  GROUP BY course_period_var.COURSE_PERIOD_ID ORDER BY course_period_var.ID';


	
	$ret_temp = DBGet(DBQuery($sql));
        $ret=array();
        $i=1;
        $days_arr=array("Monday"=>'M',"Tuesday"=>'T',"Wednesday"=>'W',"Thursday"=>'H',"Friday"=>'F',"Saturday"=>'S',"Sunday"=>'U');
        
        
        foreach($ret_temp as $ri=>$rd)
        {
            $time=explode(' - ',$rd['PERIOD_TIME']);
            $rd['PERIOD_TIME']=date("g:i A", strtotime($time[0])).' - '.date("g:i A", strtotime($time[1]));
            unset($time);
            if($rd['SCHEDULE_TYPE']=='FIXED')
            $ret[$i]=$rd;
            else
            {
                $get_det=DBGet(DBQuery('SELECT cpv.*,CONCAT(sp.START_TIME,\' - \',sp.END_TIME,\' \') as PERIOD_TIME,sp.TITLE as PERIOD,r.TITLE AS LOCATION FROM course_period_var cpv,school_periods sp,rooms r WHERE cpv.COURSE_PERIOD_ID='.$rd['COURSE_PERIOD_ID'].' AND cpv.PERIOD_ID=sp.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID'));
                $ret[$i]=$rd;    
                if(count($get_det)>1)
                {
                    foreach($get_det as $gi=>$gd)
                    { 
                        if($rd['CPV_ID']!=$gd['ID'])
                        {
                        $time=explode(' - ',$gd['PERIOD_TIME']);
                        $gd['PERIOD_TIME']=date("g:i A", strtotime($time[0])).' - '.date("g:i A", strtotime($time[1]));
                        unset($time);    
                        $ret[$i]['PERIOD_TIME']=$ret[$i]['PERIOD_TIME'].' , '.$gd['PERIOD_TIME'];  
                        $ret[$i]['PERIOD']=$ret[$i]['PERIOD'].' , '.$gd['PERIOD'];   
                        $ret[$i]['LOCATION']=$ret[$i]['LOCATION'].' , '.$gd['LOCATION'];  
                        if($rd['SCHEDULE_TYPE']=='VARIABLE')
                        $ret[$i]['DAYS']=$ret[$i]['DAYS'].' , '.$gd['DAYS'];   
                        else
                        $ret[$i]['DAYS']=$ret[$i]['DAYS'].' , '.$days_arr[$gd['DAYS']];   
                        }
                    }
                    $final_days=explode(' , ',$ret[$i]['DAYS']);
                    $final_days=  array_unique($final_days);
                    $final_days=implode(' , ',$final_days);
                    $ret[$i]['DAYS']=$final_days;
                }   
            }
           
            $i++;
        }
        
$temp_days=array();
foreach($ret as $rt_data)
{
    if($rt_data['SCHEDULE_TYPE']=='FIXED')
        $rt_data['DAYS_OF_WEEK'] = _makeDays($rt_data['DAYS'],'');
    elseif($rt_data['SCHEDULE_TYPE']=='VARIABLE')
    {
        $temp_days[$rt_data['DAYS']]=$rt_data['DAYS'];
        $rt_data['DAYS_OF_WEEK'] = _makeDays(implode('',$temp_days),'');
                   
    }
    elseif($rt_data['SCHEDULE_TYPE']=='BLOCKED')
    {
        $rt_data['DAYS_OF_WEEK'] = _makeDays($rt_data['DAYS'],'');
    }
    $schedule_data[] = $rt_data;
}

if(count($schedule_data)>0)
{
    $data['schedule_data'] = $schedule_data;
    $data['schedule_success'] = 1;
    $data['schedule_success_msg'] = 'Nil';
}   
else 
{
    $data['schedule_data'] = $schedule_data;
    $data['schedule_success'] = 0;
    $data['schedule_success_msg'] = 'No data Found';
}

$data['selected_mp'] = $_REQUEST['mp_id'];
$data['selected_subject'] = $_REQUEST['subject_id'];
$data['selected_course'] = $_REQUEST['course_id'];
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

function _makeDays($value,$column)
{
    $days = array();
    $i=0;
    foreach(array('U','M','T','W','H','F','S') as $day)
    {
        if($day=='U')
            $days[$i]['day']= 1;
        elseif($day=='M')
            $days[$i]['day']= 2;
        elseif($day=='T')
            $days[$i]['day']= 3;
        elseif($day=='W')
            $days[$i]['day']= 4;
        elseif($day=='H')
            $days[$i]['day']= 5;
        elseif($day=='F')
            $days[$i]['day']= 6;
        elseif($day=='S')
            $days[$i]['day']= 7;
        if(strpos($value,$day)!==false)
        {
            $days[$i]['status']= 1;
        }
        else 
        {
            $days[$i]['status']= 0;
        }
        $i++;
//		if(strpos($value,$day)!==false)
//			$return .= $day;
//		else
//			$return .= '-';
    }
//	return '<div style="white-space: nowrap">'.$return.'</div>';
        return $days;
}        
echo json_encode($data);
?>
