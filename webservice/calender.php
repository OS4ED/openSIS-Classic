<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
header('Content-Type: application/json');

include 'function/ProperDateFnc.php';
$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
$cpv_id = $_REQUEST['cpv_id'];
$cp_data = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));
$_SESSION['UserCoursePeriod'] = $course_period_id = $cp_data[1]['COURSE_PERIOD_ID'];
$date = $_REQUEST['date'];
$time = strtotime($date);
$action_type = $_REQUEST['action_type'];
$calender = array();
if(UserWs('PROFILE')!='admin')
{
    if(!$_REQUEST['calendar_id'])
    {
	
        $course_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.UserCoursePeriod().'\''));
	
        if($course_RET[1]['CALENDAR_ID'])
        $_REQUEST['calendar_id'] = $course_RET[1]['CALENDAR_ID'];
	else
	{
		$default_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND DEFAULT_CALENDAR=\'Y\''));
		
                if(!empty($default_RET))
                $_REQUEST['calendar_id'] = $default_RET[1]['CALENDAR_ID'];
            else {
                $qr=DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'ORDER BY CALENDAR_ID LIMIT 0,1'));
            $_REQUEST['calendar_id'] = $qr[1]['CALENDAR_ID'];
                
            }
	}
    }
}
elseif(!$_REQUEST['calendar_id'])
{
	$default_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND DEFAULT_CALENDAR=\'Y\''));
	if(count($default_RET))
		$_REQUEST['calendar_id'] = $default_RET[1]['CALENDAR_ID'];
	else
	{
		$calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		if(count($calendars_RET))
			$_REQUEST['calendar_id'] = $calendars_RET[1]['CALENDAR_ID'];
		else
			$msg = 'There are no calendars yet setup.';
	}
}

$calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND CALENDAR_ID=\''.$_REQUEST['calendar_id'].'\''),array(),array('SCHOOL_DATE'));

$title_RET = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY DEFAULT_CALENDAR ASC'));
$i=0;
if(count($title_RET)>0)
{
    foreach($title_RET as $title)
    {
                    $date_RET = DBGet(DBQuery('SELECT MAX(SCHOOL_DATE) AS END_DATE,MIN(SCHOOL_DATE) AS START_DATE FROM `attendance_calendar` WHERE `calendar_id`=\''.$title['CALENDAR_ID'].'\' AND `syear`=\''.UserSyear().'\''));
            $calender[$i]['ID']=$title['CALENDAR_ID'];
            $calender[$i]['TITLE']=$title['TITLE'];
                    $calender[$i]['START_DATE']=$date_RET[1]['START_DATE'];
                    $calender[$i]['END_DATE']=$date_RET[1]['END_DATE'];
            $i++;
    }
    $cal_success = 1;
    $cal_msg ="nil";
}
else 
{
    $cal_success = 0;
    $cal_msg ="No data found";
}


$cal_data = array();

$blocks_RET = DBGet(DBQuery('SELECT DISTINCT BLOCK FROM school_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND BLOCK IS NOT NULL ORDER BY BLOCK'));
if($action_type=='event')
{
    $events_RET = DBGet(DBQuery('SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,ce.TITLE,ce.DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND ce.calendar_id=\''.$_REQUEST['calendar_id'].'\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID='.UserWs('PROFILE_ID').' UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND CALENDAR_ID=0'),array(),array());
    
    if(count($events_RET)>0)
    {
    foreach($events_RET as $event)
    {
        $cal_data[]=$event;
    }
        $data_success = 1;
        $data_success_msg ="Nil";
    }
    else 
    {
        $data_success = 0;
        $data_success_msg ="No data found";
    }
}
elseif($action_type=='assignment')
{
    if(UserWs('PROFILE')=='parent' || UserWs('PROFILE')=='student')
        $assignments_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%d-%b-%y\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%d-%b-%y\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.UserStudentIDWs().'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE<=a.DUE_DATE) '),array(),array());
    elseif(UserWs('PROFILE')=='teacher')
            $assignments_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%d-%b-%y\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%d-%b-%y\') AS ASSIGNED_DATE,a.TITLE,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION,CASE WHEN a.ASSIGNED_DATE<=CURRENT_DATE OR a.ASSIGNED_DATE IS NULL THEN \'Y\' ELSE NULL END AS ASSIGNED FROM gradebook_assignments a WHERE a.STAFF_ID=\''.UserWs('STAFF_ID').'\' AND a.DUE_DATE = \''.date('Y-m-d',$time).'\''),array(),array());
    
    if(count($assignments_RET)>0)
    {
        foreach($assignments_RET as $event)
        {
            if($event['COURSE_PERIOD_ID']!='' || $event['COURSE_ID']!='')
            {
                $cp_sql = 'SELECT TITLE FROM course_periods WHERE SCHOOL_ID = '.$_REQUEST['school_id'];
                if($event['COURSE_PERIOD_ID']!='')
                {
                    $cp_sql .= ' AND course_period_id ='.$event['COURSE_PERIOD_ID'];
                }
                elseif($event['COURSE_ID']!='')
                {
                    $cp_sql .= ' AND course_period_id ='.$event['COURSE_ID'];
                }
                
                $cp_data = DBGet(DBQuery($cp_sql),array(),array());
                $event['CP_TITLE'] = $cp_data[1]['TITLE'];
            }
            else 
            {
                $event['CP_TITLE'] = '';
            }
            $event['ASGMT_TITLE'] = $event['TITLE'].(($event['CP_TITLE']!='')?' - '.$event['CP_TITLE']:'');
            $cal_data[]=$event;
        }
        $data_success = 1;
        $data_success_msg ="Nil";
    }
    else 
    {
        
        $data_success = 0;
        $data_success_msg ="No Data Found";
    }
}   
elseif($action_type=='list_events')
{
//    	if($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start'])
//	{
//		while(!VerifyDate($start_date = $_REQUEST['day_start'].'-'.$_REQUEST['month_start'].'-'.$_REQUEST['year_start']))
//			$_REQUEST['day_start']--;
//	}
//	else
//	{
		$min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		if($min_date[1]['MIN_DATE'])
			$start_date = $min_date[1]['MIN_DATE'];
		else
			$start_date = '01-'.strtoupper(date('M-y'));
//	}

        if (strpos($start_date, 'JAN')!==false or strpos($start_date, 'FEB')!== false or strpos($start_date, 'MAR')!== false or strpos($start_date, 'APR')!== false or strpos($start_date, 'MAY')!== false or strpos($start_date, 'JUN')!== false or strpos($start_date, 'JUL')!== false or strpos($start_date, 'AUG')!== false or strpos($start_date, 'SEP')!== false or strpos($start_date, 'OCT')!== false or strpos($start_date, 'NOV')!== false or strpos($start_date, 'DEC')!== false) {
        { 
            $sdateArr=  explode("-", $start_date);
            $month=$sdateArr[1];
            if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='JAN')$month='12';
           $start_date=$sdateArr[2].'-'.$month.'-'.$sdateArr[0];
        }
        }
//	if($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end'])
//	{
//		while(!VerifyDate($end_date = $_REQUEST['day_end'].'-'.$_REQUEST['month_end'].'-'.$_REQUEST['year_end']))
//			$_REQUEST['day_end']--;
//	}
//	else
//	{ 
		$max_date = DBGet(DBQuery('SELECT max(SCHOOL_DATE) AS MAX_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		if($max_date[1]['MAX_DATE'])
			$end_date = $max_date[1]['MAX_DATE'];
		else
			$end_date = strtoupper(date('Y-m-d'));
//	}

        if (strpos($end_date, 'JAN')!==false or strpos($end_date, 'FEB')!== false or strpos($end_date, 'MAR')!== false or strpos($end_date, 'APR')!== false or strpos($end_date, 'MAY')!== false or strpos($end_date, 'JUN')!== false or strpos($end_date, 'JUL')!== false or strpos($end_date, 'AUG')!== false or strpos($end_date, 'SEP')!== false or strpos($end_date, 'OCT')!== false or strpos($end_date, 'NOV')!== false or strpos($end_date, 'DEC')!== false) {
        {             
            $edateArr=  explode("-", $end_date);
            $month=$edateArr[1];
            if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='DEC')$month='12';
            $end_date=$edateArr[2].'-'.$month.'-'.$edateArr[0];
        }
}
       
										// <A HREF=Modules.php?modname='.$_REQUEST["modname"].'&month='.$_REQUEST["month"].'&year='.$_REQUEST["year"].'>
	$events_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND SYEAR=\''.UserSyear().'\'  AND (calendar_id=\''.$_REQUEST['calendar_id'].'\' OR calendar_id=\'0\') ORDER BY SCHOOL_DATE'));

        
        if(count($events_RET)>0)
        {
            foreach($events_RET as $event)
            {
                $cal_data[]=$event;
            }
            $data_success = 1;
            $data_success_msg ="Nil";
        }
        else 
        {
            $data_success = 0;
            $data_success_msg ="No Data Found";
        }
}
$data = array('school_calender'=>$calender,'cal_success'=>$cal_success,'cal_msg'=>$cal_msg,'cal_data'=>$cal_data,'data_success'=>$data_success,'data_success_msg'=>$data_success_msg);
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
