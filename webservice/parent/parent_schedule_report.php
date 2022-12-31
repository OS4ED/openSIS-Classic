<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$data['selected_student']=$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
//$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];$parent_id = $_REQUEST['parent_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $schedule_data = array();

        $RET1 = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM marking_periods WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY MARKING_PERIOD_ID"));

        if($_REQUEST['sel_mp'])
            $mp=$_REQUEST['sel_mp'];
        else
            $mp=  UserMP();

        $i=0;
        foreach($RET1 as $quarter)
        {
            $mp_data[$i]['MARKING_PERIOD_ID']=$quarter['MARKING_PERIOD_ID'];
            $mp_data[$i]['TITLE']=$quarter['TITLE'];
            $i++;
        }
        $data['selected_mp'] = $mp;
        $data['mp_data'] = $mp_data;
        if(count($mp_data)>0)
        {
            $mp_success = 1;
        }
        else 
        {
            $mp_success = 0;
        }
        $data['mp_success'] = $mp_success;
        $sql="SELECT CONCAT(s.LAST_NAME,', ',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ,c.TITLE AS COURSE_TITLE,p_cp.TITLE AS PERIOD_TITLE,sr.MARKING_PERIOD_ID,cpv.DAYS, CONCAT(sp.START_TIME, ' to ', sp.END_TIME) AS DURATION,r.TITLE AS ROOM FROM students s,student_enrollment ssm LEFT OUTER JOIN schedule sr ON (sr.STUDENT_ID=ssm.STUDENT_ID),courses c,course_periods p_cp,course_period_var cpv,rooms r,school_periods sp WHERE ssm.STUDENT_ID=s.STUDENT_ID AND p_cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID AND ssm.SYEAR='".UserSyear()."' AND ssm.SCHOOL_ID='".UserSchool()."' AND ('".DBDate('mysql')."' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND '".DBDate('mysql')."'>ssm.START_DATE)) AND ssm.STUDENT_ID='".$student_id."' AND s.STUDENT_ID = '".$student_id."' AND cpv.PERIOD_ID=sp.PERIOD_ID AND ssm.SYEAR=sr.SYEAR AND sr.COURSE_ID=c.COURSE_ID AND sr.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND ('".DBDate('mysql')."' BETWEEN sr.START_DATE AND sr.END_DATE OR sr.END_DATE IS NULL) ORDER BY FULL_NAME,sp.SORT_ORDER";

        $stu_id=$student_id;
        $RET_show[$stu_id]=DBGet(DBQuery($sql));
        $date=date(Y."-".m."-".d);
         if(!$_REQUEST['sel_mp'])
         {  
            $sel_mp =GetCurrentMP('QTR',$date);
            if(!$sel_mp)
            {
                $sel_mp =GetCurrentMP('SEM',$date);
                if(!$sel_mp)
                {
                    $sel_mp =GetCurrentMP('FY',$date);
                }
            }
         }
         else
            $sel_mp =sqlSecurityFilter($_REQUEST['sel_mp']);
        $sql_mp_detail = 'SELECT title, start_date, end_date, parent_id, grandparent_id from marking_periods WHERE marking_period_id = \''.$sel_mp.'\'';
        $res_mp_detail = mysql_query($sql_mp_detail);
        $row_mp_detail = mysql_fetch_array($res_mp_detail);

        $mp_string = '(s.marking_period_id='.$sel_mp.'';
        if($row_mp_detail['parent_id'] != -1)
            $mp_string.=' or s.marking_period_id='.$row_mp_detail['parent_id'].'';
        if($row_mp_detail['grandparent_id'] != -1)
            $mp_string.=' or s.marking_period_id='.$row_mp_detail['grandparent_id'].'';


        if(count($RET_show)>0)
        {
            foreach($RET_show as $student_id=>$courses)
            {       
                if(count($RET_show[$stu_id])>0)     
                $sch_exist= DBGet(DBQuery('SELECT COUNT(s.id) AS SCH_COUNT FROM schedule s WHERE s.syear=\''.UserSyear().'\'
                AND s.student_id=\''.$courses[1]['STUDENT_ID'].'\'
                AND s.school_id=\''.UserSchool().'\'
                AND '.$mp_string.' )'));
                $sch_exist_yn = $sch_exist[1]['SCH_COUNT'];
                if($sch_exist_yn != 0)
                {
                    $counter=0;
                    $ar=array('Sunday'=>'U','Monday'=>'M','Tuesday'=>'T','Wednesday'=>'W','Thursday'=>'H','Friday'=>'F','Saturday'=>'S');
                    foreach($ar as $day=>$value)
                    {
                        $r_ch= DBGet(DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time, mp.title,sp.sort_order
                        FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
                        WHERE cp.syear=\''.UserSyear().'\'
                        AND s.syear=\''.UserSyear().'\'
                        AND s.student_id=\''.$courses[1]['STUDENT_ID'].'\'
                        AND s.course_period_id=cp.course_period_id
                        AND sp.period_id=cpv.period_id
                        AND cp.course_period_id=cpv.course_period_id
                        AND r.room_id=cpv.room_id
                        AND s.start_date<=\''.date('Y-m-d').'\'
                        AND (s.end_date IS NULL OR s.end_date>=\''.date('Y-m-d').'\')
                        AND cpv.days like \''.'%'.$value.'%'.'\'
                        AND s.school_id=\''.UserSchool().'\'
                        AND s.marking_period_id=mp.marking_period_id
                        AND '. $mp_string.') order by sp.sort_order'));

                        $rs=DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time, mp.title,sp.sort_order
                        FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
                        WHERE cp.syear=\''.UserSyear().'\'
                        AND s.syear=\''.UserSyear().'\'
                        AND s.student_id=\''.$courses[1]['STUDENT_ID'].'\'
                        AND s.course_period_id=cp.course_period_id
                        AND sp.period_id=cpv.period_id
                        AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                        AND r.room_id=cpv.room_id
                        AND s.start_date<=\''.date('Y-m-d').'\'
                        AND (s.end_date IS NULL OR s.end_date>=\''.date('Y-m-d').'\')
                        AND cpv.days like \''.'%'.$value.'%'.'\'
                        AND s.school_id=\''.UserSchool().'\'
                        AND s.marking_period_id=mp.marking_period_id
                        AND '. $mp_string.') order by sp.sort_order');

                        $no_record=mysql_num_rows($rs);

                        $schedule_data[$counter]['DAY']=$day;
                        if(count($r_ch)>0)
                        {
                            $report_data = array();
                            $cnt = 0;
                            foreach($r_ch as $sch)
                            {
                                $report_data[$cnt]['START_TIME']=$sch['START_TIME'];
                                $report_data[$cnt]['END_TIME']=$sch['END_TIME'];
                                $report_data[$cnt]['CP_TITLE']=$sch['CP_TITLE'];
                                $report_data[$cnt]['TITLE']=$sch['TITLE'];
                                $report_data[$cnt]['ROOM']=$sch['ROOM'];
        //                        $report_data[]=array('START_TIME'=>$sch['START_TIME'],'END_TIME'=>$sch['END_TIME'],'CP_TITLE'=>$sch['CP_TITLE'],'TITLE'=>$sch['TITLE'],'ROOM'=>$sch['ROOM']);
                                $cnt++;
                            }
                        }
                        else 
                        {
                            $report_data = array();
                        }

                        $schedule_data[$counter]['SCHEDULE'] = $report_data;
                        $counter++;
                    }
                    $err_msg = 'Nil';
                }
                else
                {
                        $err_msg = 'No Schedule Found';
                }

            }
        }
        else
        {
            $err_msg = 'No Students were found.';
        }

        if(count($schedule_data)>0)
        {
            $schedule_success = 1;
        }
        else 
        {
            $schedule_success = 0;
        }
        $data['selected_day'] = date('l');
        $data['schedule_data'] = $schedule_data;
        $data['schedule_success'] = $schedule_success;
        $data['err_msg'] = $err_msg;
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
