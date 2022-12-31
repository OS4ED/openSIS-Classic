<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/function.php';
include '../function/app_functions.php';
header('Content-Type: application/json');

$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
$cpv_id = $_REQUEST['cpv_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$student_id && $auth_data['user_profile']=='student')
    {
        $cp_data = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));
        $_SESSION['UserCoursePeriod'] = $course_period_id = $cp_data[1]['COURSE_PERIOD_ID'];
        $date = $_REQUEST['date'];
        $time = strtotime($date);
        $action_type = $_REQUEST['action_type'];
        $view_type = $_REQUEST['view_type'];
        $calender = array();
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

        $calendar_RET = DBGet(DBQuery('SELECT DATE_FORMAT(SCHOOL_DATE,\'%Y-%m-%d\') as SCHOOL_DATE,MINUTES,BLOCK FROM attendance_calendar WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND CALENDAR_ID=\''.$_REQUEST['calendar_id'].'\''),array(),array('SCHOOL_DATE'));

        $title_RET = DBGet(DBQuery('SELECT CALENDAR_ID,TITLE FROM school_calendars WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY DEFAULT_CALENDAR ASC'));
        $i=0;
        if(count($title_RET)>0)
        {
            foreach($title_RET as $title)
            {
                    $calender[$i]['ID']=$title['CALENDAR_ID'];
                    $calender[$i]['TITLE']=$title['TITLE'];
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
            if($view_type == 'list')
            {
                $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                if($min_date[1]['MIN_DATE'])
                    $start_date = $min_date[1]['MIN_DATE'];
                else
                    $start_date = '01-'.strtoupper(date('M-y'));

                if (strpos($start_date, 'JAN')!==false or strpos($start_date, 'FEB')!== false or strpos($start_date, 'MAR')!== false or strpos($start_date, 'APR')!== false or strpos($start_date, 'MAY')!== false or strpos($start_date, 'JUN')!== false or strpos($start_date, 'JUL')!== false or strpos($start_date, 'AUG')!== false or strpos($start_date, 'SEP')!== false or strpos($start_date, 'OCT')!== false or strpos($start_date, 'NOV')!== false or strpos($start_date, 'DEC')!== false) {
                { 
                    $sdateArr=  explode("-", $start_date);
                    $month=$sdateArr[1];
                    if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='JAN')$month='12';
                   $start_date=$sdateArr[2].'-'.$month.'-'.$sdateArr[0];
                }
                }
                $max_date = DBGet(DBQuery('SELECT max(SCHOOL_DATE) AS MAX_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                if($max_date[1]['MAX_DATE'])
                        $end_date = $max_date[1]['MAX_DATE'];
                else
                        $end_date = strtoupper(date('Y-m-d'));

                if (strpos($end_date, 'JAN')!==false or strpos($end_date, 'FEB')!== false or strpos($end_date, 'MAR')!== false or strpos($end_date, 'APR')!== false or strpos($end_date, 'MAY')!== false or strpos($end_date, 'JUN')!== false or strpos($end_date, 'JUL')!== false or strpos($end_date, 'AUG')!== false or strpos($end_date, 'SEP')!== false or strpos($end_date, 'OCT')!== false or strpos($end_date, 'NOV')!== false or strpos($end_date, 'DEC')!== false) 
                {             
                    $edateArr=  explode("-", $end_date);
                    $month=$edateArr[1];
                    if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='DEC')$month='12';
                    $end_date=$edateArr[2].'-'.$month.'-'.$edateArr[0];
                }

                $events_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND SYEAR=\''.UserSyear().'\'  AND (calendar_id=\''.$_REQUEST['calendar_id'].'\' OR calendar_id=\'0\') ORDER BY SCHOOL_DATE'));

                $cal_data1 = array();
            if(count($events_RET)>0)
            {
            foreach($events_RET as $event)
            {
                        $cal_data1[$event['SCHOOL_DATE']][]=$event;
                    }
                    $i = 0;
                    foreach($cal_data1 as $key=>$value)
                    {
                        $cal_data[$i]['SCHOOL_DATE'] = $key;
                        $cal_data[$i]['EVENTS'] = $value;
                        $i++;
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
            else 
            {
                if($view_type == 'month') 
                {
                    $event_sql = 'SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,ce.TITLE,ce.DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND ce.calendar_id=\''.$_REQUEST['calendar_id'].'\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID=3 UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND CALENDAR_ID=0 ORDER BY SCHOOL_DATE';

                    $events_RET = DBGet(DBQuery($event_sql),array(),array('SCHOOL_DATE'));
                }
                elseif($view_type == 'week') 
                {
                    $start_date = $_REQUEST['start_date'];
                    $start_time = strtotime($start_date);
                    $end_date = $_REQUEST['end_date'];
                    $end_time = strtotime($end_date);
                    $event_sql = 'SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,ce.TITLE,ce.DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND ce.calendar_id=\''.$_REQUEST['calendar_id'].'\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID=3 UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND CALENDAR_ID=0 ORDER BY SCHOOL_DATE';  //(SCHOOL_DATE >= \''.date('Y-m-d',$start_time).'\' AND SCHOOL_DATE <= \''.date('Y-m-d',$end_time).'\')    (SCHOOL_DATE >= \''.date('Y-m-d',$start_time).'\' AND SCHOOL_DATE <= \''.date('Y-m-d',$end_time).'\')

                    $events_RET = DBGet(DBQuery($event_sql),array(),array('SCHOOL_DATE'));
                }
                if(count($events_RET)>0)
                {
                    $i = 0;
                    foreach($events_RET as $school_date=>$events)
                    {
                        $cal_data[$i]['SCHOOL_DATE'] = $school_date;
                        foreach($events as $event)
                        {
                            $cal_data[$i]['EVENT'][]=$event;
                        }
                        $i++;
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
        }
        elseif($action_type=='assignment')
        {
            if($view_type == 'list')
            {
                $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE<=a.DUE_DATE) ';
            }
            elseif($view_type == 'month') 
            {
               $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND a.DUE_DATE = \''.date('Y-m-d',$time).'\'';
            }
            elseif($view_type == 'week') 
            {
                $start_date = $_REQUEST['start_date'];
                $start_time = strtotime($start_date);
                $end_date = $_REQUEST['end_date'];
                $end_time = strtotime($end_date);
                $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND a.DUE_DATE = \''.date('Y-m-d',$time).'\''; //(a.DUE_DATE >= \''.date('Y-m-d',$start_time).'\' AND a.DUE_DATE <= \''.date('Y-m-d',$end_time).'\')'
            }

            $assignments_RET = DBGet(DBQuery($assignment_sql),array(),array('DUE_DATE'));
            if(count($assignments_RET)>0)
            {
                $i = 0;
                foreach($assignments_RET as $school_date=>$events)
                {
                    $cal_data[$i]['SCHOOL_DATE'] = $school_date;
                    foreach($events as $event)
                    {
                    if($event['COURSE_PERIOD_ID']!='' || $event['COURSE_ID']!='')
                    {
                        $cp_sql = 'SELECT TITLE FROM course_periods WHERE ';
                        if($event['COURSE_PERIOD_ID']!='')
                        {
                            $cp_sql .= 'course_period_id ='.$event['COURSE_PERIOD_ID'];
                        }
                        elseif($event['COURSE_ID']!='')
                        {
                            $cp_sql .= 'course_period_id ='.$event['COURSE_ID'];
                        }

                        $cp_data = DBGet(DBQuery($cp_sql),array(),array());
                        $event['CP_TITLE'] = $cp_data[1]['TITLE'];
                    }
                    else 
                    {
                        $event['CP_TITLE'] = '';
                    }
                    $event['ASGMT_TITLE'] = $event['TITLE'].(($event['CP_TITLE']!='')?' - '.$event['CP_TITLE']:'');
                        $cal_data[$i]['ASSIGNMENT'][]=$event;
                    }
                    $i++;
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
        elseif($action_type=='compact')
        {
            if($view_type == 'list')
            {
                $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE<=a.DUE_DATE) ';
                        $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                        if($min_date[1]['MIN_DATE'])
                                $start_date = $min_date[1]['MIN_DATE'];
                        else
                                $start_date = '01-'.strtoupper(date('M-y'));

                if (strpos($start_date, 'JAN')!==false or strpos($start_date, 'FEB')!== false or strpos($start_date, 'MAR')!== false or strpos($start_date, 'APR')!== false or strpos($start_date, 'MAY')!== false or strpos($start_date, 'JUN')!== false or strpos($start_date, 'JUL')!== false or strpos($start_date, 'AUG')!== false or strpos($start_date, 'SEP')!== false or strpos($start_date, 'OCT')!== false or strpos($start_date, 'NOV')!== false or strpos($start_date, 'DEC')!== false) {
                { 
                    $sdateArr=  explode("-", $start_date);
                    $month=$sdateArr[1];
                    if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='JAN')$month='12';
                   $start_date=$sdateArr[2].'-'.$month.'-'.$sdateArr[0];
                }
                }
                $max_date = DBGet(DBQuery('SELECT max(SCHOOL_DATE) AS MAX_DATE FROM attendance_calendar WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
                if($max_date[1]['MAX_DATE'])
                        $end_date = $max_date[1]['MAX_DATE'];
                else
                        $end_date = strtoupper(date('Y-m-d'));

                if (strpos($end_date, 'JAN')!==false or strpos($end_date, 'FEB')!== false or strpos($end_date, 'MAR')!== false or strpos($end_date, 'APR')!== false or strpos($end_date, 'MAY')!== false or strpos($end_date, 'JUN')!== false or strpos($end_date, 'JUL')!== false or strpos($end_date, 'AUG')!== false or strpos($end_date, 'SEP')!== false or strpos($end_date, 'OCT')!== false or strpos($end_date, 'NOV')!== false or strpos($end_date, 'DEC')!== false) 
                {             
                    $edateArr=  explode("-", $end_date);
                    $month=$edateArr[1];
                    if($month=='JAN')$month='01'; if($month=='FEB')$month='02'; if($month=='MAR')$month='03'; if($month=='APR')$month='04'; if($month=='MAY')$month='05'; if($month=='JUN')$month='06'; if($month=='JUL')$month='07'; if($month=='AUG')$month='08'; if($month=='SEP')$month='09'; if($month=='OCT')$month='10'; if($month=='NOV')$month='11'; if($month=='DEC')$month='12';
                    $end_date=$edateArr[2].'-'.$month.'-'.$edateArr[0];
                }

                $events_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND SYEAR=\''.UserSyear().'\'  AND (calendar_id=\''.$_REQUEST['calendar_id'].'\' OR calendar_id=\'0\') ORDER BY SCHOOL_DATE'),array(),array('SCHOOL_DATE'));

        }
            elseif($view_type == 'month') 
            {
                $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND a.DUE_DATE = \''.date('Y-m-d',$time).'\'';
                $event_sql = 'SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,ce.TITLE,ce.DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND ce.calendar_id=\''.$_REQUEST['calendar_id'].'\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID=3 UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND CALENDAR_ID=0 ORDER BY SCHOOL_DATE';

                $events_RET = DBGet(DBQuery($event_sql),array(),array('SCHOOL_DATE'));
            }
            elseif($view_type == 'week') 
            {
                $start_date = $_REQUEST['start_date'];
                $start_time = strtotime($start_date);
                $end_date = $_REQUEST['end_date'];
                $end_time = strtotime($end_date);
                $assignment_sql = 'SELECT ASSIGNMENT_ID AS ID,DATE_FORMAT(a.DUE_DATE,\'%Y-%m-%d\') AS DUE_DATE,DATE_FORMAT(a.ASSIGNED_DATE,\'%Y-%m-%d\') AS ASSIGNED_DATE,a.TITLE,\'Y\' AS ASSIGNED,a.COURSE_PERIOD_ID,a.COURSE_ID,a.DESCRIPTION FROM gradebook_assignments a,schedule s WHERE (a.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID OR a.COURSE_ID=s.COURSE_ID) AND s.STUDENT_ID=\''.$student_id.'\' AND s.DROPPED!=\'Y\' AND (CURRENT_DATE>=a.ASSIGNED_DATE OR CURRENT_DATE<=a.ASSIGNED_DATE) AND a.DUE_DATE = \''.date('Y-m-d',$time).'\'';
                $event_sql = 'SELECT ce.ID,DATE_FORMAT(ce.SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,ce.TITLE,ce.DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND SYEAR=\''.UserSyear().'\' AND ce.calendar_id=\''.$_REQUEST['calendar_id'].'\'  AND ce.CALENDAR_ID=cev.CALENDAR_ID AND cev.PROFILE_ID=3 UNION SELECT ID,DATE_FORMAT(SCHOOL_DATE,\'%Y-%m-%d\') AS SCHOOL_DATE,TITLE,DESCRIPTION FROM calendar_events WHERE SCHOOL_DATE = \''.date('Y-m-d',$time).'\' AND CALENDAR_ID=0 ORDER BY SCHOOL_DATE';

                $events_RET = DBGet(DBQuery($event_sql),array(),array('SCHOOL_DATE'));
            }

            $assignments_RET = DBGet(DBQuery($assignment_sql),array(),array('DUE_DATE'));

            $date_array = array();
            foreach($assignments_RET as $date_as)
                {
                foreach($date_as as $as_data)
                    {
                    if(!in_array(strtotime($as_data['DUE_DATE']), $date_array))
                        $date_array[]=strtotime($as_data['DUE_DATE']);
                    }
            }
            foreach($events_RET as $ev_date)
            {
                foreach($ev_date as $ev_data)
                {
                    if(!in_array(strtotime($ev_data['SCHOOL_DATE']), $date_array))
                        $date_array[]=strtotime($ev_data['SCHOOL_DATE']);
                }
            }
                    $i = 0;
            foreach($date_array as $date)
                    {
                $cal_data[$i]['SCHOOL_DATE'] = date('Y-m-d',$date);
                if(count($events_RET[date('Y-m-d',$date)])>0)
                {
                foreach($events_RET[date('Y-m-d',$date)] as $evnt)
                    $cal_data[$i]['EVENTS'][] = $evnt;  
                }
                else 
                {
                    $cal_data[$i]['EVENTS'] = array();
                }
                if(count($assignments_RET[date('Y-m-d',$date)])>0)
                {
                foreach($assignments_RET[date('Y-m-d',$date)] as $asgmt)
                    $cal_data[$i]['ASSIGNMENTS'][] = $asgmt;
                }
                else 
                {
                    $cal_data[$i]['ASSIGNMENTS'] = array();
                }
                        $i++;
                    }
                if(count($cal_data)>0)
                {
                    $data_success = 1;
                    $data_success_msg ="Nil";
                }
                else 
                {
                    $data_success = 0;
                    $data_success_msg ="No data found";
                }
        }
        $data = array('school_calender'=>$calender,'cal_success'=>$cal_success,'cal_msg'=>$cal_msg,'cal_data'=>$cal_data,'data_success'=>$data_success,'data_success_msg'=>$data_success_msg,'selected_calender_id'=>$_REQUEST['calendar_id']);
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
