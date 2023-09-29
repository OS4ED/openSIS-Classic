<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include '../functions/DbDateFnc.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/function.php';

include 'function/ParamLib.php';

header('Content-Type: application/json');
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

$teacher_id = $_REQUEST['staff_id'];
$mp_id = $_REQUEST['mp_id'];
$syear = $_REQUEST['syear'];
$usr_school = $_REQUEST['school_id'];
$_SESSION['UserSchool']=$usr_school;
$_SESSION['UserSyear']=$syear;

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$mp_select_RET = DBGet(DBQuery('SELECT DISTINCT cp.MARKING_PERIOD_ID, (SELECT TITLE FROM marking_periods WHERE MARKING_PERIOD_ID=cp.MARKING_PERIOD_ID) AS TITLE FROM course_periods cp,courses c, school_periods sp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND (cp.TEACHER_ID=\''.$teacher_id.'\' OR cp.SECONDARY_TEACHER_ID=\''.$teacher_id.'\') AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.MARKING_PERIOD_ID IS NOT NULL AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID=\''.UserSchool().'\''));

$mp_data[0] = array('MARKING_PERIOD_ID'=>'','TITLE'=>'Show All');
foreach($mp_select_RET as $mpdt)
{
    $mps[]=$mpdt;
}
if(count($mps)>0)
    $mp_data = array_merge($mp_data,$mps);

  if(!$_REQUEST['mp_id'])
  {
      
      $schedule_RET = DBGet(DBQuery('SELECT cp.SCHEDULE_TYPE,cp.course_period_id,\'\' as ROOM,c.TITLE AS COURSE,\'\' as PERIOD,cp.COURSE_WEIGHT,IF(cp.MARKING_PERIOD_ID IS NULL ,\'Custom\',cp.MARKING_PERIOD_ID) AS MARKING_PERIOD_ID, \'\' as DAYS,\'\' AS DURATION from
course_periods cp , courses c  WHERE cp.course_id=c.COURSE_ID AND (cp.TEACHER_ID=\''.$teacher_id.'\' OR cp.SECONDARY_TEACHER_ID=\''.$teacher_id.'\')  AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID='.UserSchool()));
      
  }
  else if($_REQUEST['mp_id'])
  {
	$sel_mp_info=  DBGet(DBQuery('SELECT * FROM marking_periods WHERE MARKING_PERIOD_ID='.$_REQUEST['mp_id']));
        $sel_mp_info=$sel_mp_info[1];

        $schedule_RET = DBGet(DBQuery('SELECT cp.SCHEDULE_TYPE,cp.course_period_id,\'\' as ROOM,\'\' as PERIOD,c.TITLE AS COURSE,cp.COURSE_WEIGHT,IF(cp.MARKING_PERIOD_ID IS NULL ,\'Custom\',cp.MARKING_PERIOD_ID) AS MARKING_PERIOD_ID, \'\' as DAYS,\'\' AS DURATION from
course_periods cp , courses c WHERE cp.course_id=c.COURSE_ID   AND (cp.MARKING_PERIOD_ID IN ('.GetAllMP(GetMPTable(GetMP($_REQUEST['mp_id'],'TABLE',$syear,$usr_school)),$_REQUEST['mp_id']).') OR (cp.MARKING_PERIOD_ID IS NULL AND (cp.BEGIN_DATE BETWEEN \''.$sel_mp_info['START_DATE'].'\' AND \''.$sel_mp_info['END_DATE'].'\'))) AND (cp.TEACHER_ID=\''.$teacher_id.'\' OR cp.SECONDARY_TEACHER_ID=\''.$teacher_id.'\') AND cp.SCHOOL_ID=\''.$usr_school.'\' AND cp.SYEAR='.$syear));
  }

    foreach($schedule_RET as $rdi=>$rdd)
    {
            $get_det=DBGet(DBQuery('SELECT cpv.DAYS,cpv.COURSE_PERIOD_DATE,CONCAT(sp.START_TIME,\''. ' to '.'\', sp.END_TIME) AS DURATION,r.TITLE as ROOM,sp.TITLE AS PERIOD FROM course_period_var cpv,school_periods sp,rooms r WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID='.$rdd['COURSE_PERIOD_ID']));
            $cp_info=  DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='.$rdd['COURSE_PERIOD_ID']));
            if($rdd['SCHEDULE_TYPE']=='FIXED')
            {
                $schedule_RET[$rdi]['DAYS']=_makeDays($get_det[1]['DAYS'],'');
                $schedule_RET[$rdi]['DURATION']=$get_det[1]['DURATION'];
                $schedule_RET[$rdi]['ROOM']=$get_det[1]['ROOM'];
                $schedule_RET[$rdi]['PERIOD']=$get_det[1]['PERIOD'];
                if($schedule_RET[$rdi]['MARKING_PERIOD_ID']=='Custom')
                {
                    $schedule_RET[$rdi]['MARKING_PERIOD_ID']=date('M/d/Y',strtotime($cp_info[1]['BEGIN_DATE'])).' to '.date('M/d/Y',strtotime($cp_info[1]['END_DATE']));
                }
            }
            else
            {  
                $temp_days=array();
                $temp_duration=array();
                $temp_room=array();
                $temp_period=array();

                foreach($get_det as $gi=>$gd)
                {
                   if($rdd['SCHEDULE_TYPE']=='VARIABLE')
                   $temp_days[$gd['DAYS']]=$gd['DAYS'];
                   elseif($rdd['SCHEDULE_TYPE']=='BLOCKED')
                   $temp_days[$gd['DAYS']]=DaySname(date('l',$gd['COURSE_PERIOD_DATE']));

                   $temp_period[$gd['PERIOD']]=$gd['PERIOD'];
                   $temp_duration[$gd['DURATION']]=$gd['DURATION'];
                   $temp_room[$gd['ROOM']]=$gd['ROOM'];

                }
                $schedule_RET[$rdi]['DAYS']=_makeDays(implode('',$temp_days),'');
                $schedule_RET[$rdi]['DURATION']=implode(',',$temp_duration);
                $schedule_RET[$rdi]['ROOM']=implode(',',$temp_room);
                $schedule_RET[$rdi]['PERIOD']=implode(',',$temp_period);
                if($schedule_RET[$rdi]['MARKING_PERIOD_ID']=='Custom')
                {
                    $schedule_RET[$rdi]['MARKING_PERIOD_ID']=date('M/d/Y',strtotime($cp_info[1]['BEGIN_DATE'])).' to '.date('M/d/Y',strtotime($cp_info[1]['END_DATE']));
                }
            }
            
            if($rdd['MARKING_PERIOD_ID']=='Custom')
            {
                $schedule_RET[$rdi]['TERM']=date('M/d/Y',strtotime($cp_info[1]['BEGIN_DATE'])).' to '.date('M/d/Y',strtotime($cp_info[1]['END_DATE']));
            }
            else 
            {
                $term_data = DBGet(DBQuery('SELECT TITLE FROM marking_periods WHERE MARKING_PERIOD_ID='.$rdd['MARKING_PERIOD_ID']));
                $schedule_RET[$rdi]['TERM']=$term_data[1]['TITLE'];
            }
    }
    foreach($schedule_RET as $sch_dt)
    {
        $schedule_data[]=$sch_dt;
    }
    if(count($schedule_RET)>0)
        $success = 1;
    else 
        $success = 0;
    $data = array('schedule'=>$schedule_data,'success'=>$success,'marking_periods_list'=>$mp_data,'selected_mp'=>$_REQUEST['mp_id']);
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