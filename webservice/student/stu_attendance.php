<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

include '../function/ProperDateFnc.php';

header('Content-Type: application/json');

$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$student_id && $auth_data['user_profile']=='student')
    {

        $mp_data = array();
        $calender_data = array();
        $student_attendance_data = array();
        $student_schedule_data = array();

        if($_REQUEST['start_date']!='' && $_REQUEST['end_date']!='')
        {
            $start_date = $_REQUEST['start_date'];
            $end_date = $_REQUEST['end_date'];
        }
        else 
        {
            $start_date = date('Y-m').'-01';
            $end_date = ProperDateMAvr();
        }

        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
        if(!isset($mp_id))
        {
            $mp_id = GetCurrentMPWs('QTR',date('Y-m-d'),UserSyear(),UserSchool());
            $allMP='QTR';
        }	
        if(!$RET)
        {
            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
            if(!isset($mp_id))
            {
                    $mp_id = GetCurrentMPWs('SEM',date('Y-m-d'),UserSyear(),UserSchool());
                    $allMP='SEM';
            }	
        }

        if(!$RET)
        {
            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
            if(!isset($mp_id))
            {
                    $mp_id = GetCurrentMPWs('FY',date('Y-m-d'),UserSyear(),UserSchool());
                    $allMP='FY';
            }	
        }
        if(count($RET))
        {
            if(!$mp_id)
                    $mp_id = $RET[1]['MARKING_PERIOD_ID'];
            $i=0;
            $data=array();
            foreach($RET as $quarter)
            {
                $mp_data[$i]['id']=$quarter['MARKING_PERIOD_ID'];
                $mp_data[$i]['title']=$quarter['TITLE'];
                $i++;
            }
        }
        if(Count($mp_data)>0)
        {
            $mp_success = 1;
            $mp_msg = 'Nil';
        }
        else 
        {
            $mp_success = 0;
            $mp_msg = 'No data found';
        }

        $cal_RET = DBGet(DBQuery('SELECT DISTINCT SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_calendar WHERE SCHOOL_ID=\''.UserSchool().'\' AND SCHOOL_DATE BETWEEN \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\' ORDER BY SCHOOL_DATE'));

        if(count($cal_RET)>0)
        {
            foreach($cal_RET as $value)
                $calender_data[]['SCHOOL_DATE'] = $value['SCHOOL_DATE'];
        }
        if(count($calender_data)>0)
        {
            $cal_success = 1;
            $cal_msg = 'Nil';
        }
        else 
        {
            $cal_success = 0;
            $cal_msg = 'No data found';
        }

        foreach($calender_data as $cal)
        {
            $sql ='SELECT STATE_VALUE FROM attendance_day WHERE SCHOOL_DATE = \''.$cal['SCHOOL_DATE'].'\' AND STUDENT_ID ='.$student_id; 
            //$sql = 'SELECT ap.STUDENT_ID,ap.PERIOD_ID,ap.SCHOOL_DATE,ac.SHORT_NAME,ad.STATE_VALUE,ad.COMMENT AS OFFICE_COMMENT,ap.COMMENT AS TEACHER_COMMENT,ac.STATE_CODE FROM attendance_period ap,attendance_day ad,attendance_codes ac WHERE ap.STUDENT_ID=ad.STUDENT_ID AND ap.SCHOOL_DATE=ad.SCHOOL_DATE AND ap.ATTENDANCE_CODE=ac.ID  AND ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\'';
            //$sql = 'SELECT ap.SCHOOL_DATE,ap.COURSE_PERIOD_ID,ac.SHORT_NAME,ac.STATE_CODE,ac.DEFAULT_CODE FROM attendance_period ap,attendance_codes ac WHERE ap.ATTENDANCE_CODE=ac.ID AND ap.SCHOOL_DATE BETWEEN \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\' AND ap.ATTENDANCE_CODE=ac.ID AND ap.STUDENT_ID=\''.$student_id.'\'';
            $attendance_RET = DBGet(DBQuery($sql));
            foreach($attendance_RET as $att_data)
            {
                    $att_data['SCHOOL_DATE'] = $cal['SCHOOL_DATE'];
                $att_data['PRESENT'] = _makeStateValue($att_data['STATE_VALUE']);
                $att_data['STATE_CODE'] = _makeStateCode($att_data['STATE_VALUE']);
                $student_attendance_data[] = $att_data;
            }
        }
        //print_r($student_attendance_data);
        $i = 0;
        for ($i=strtotime($start_date); $i<=strtotime($end_date); $i+=86400) {  
            $new_period[] = date("Y-m-d", $i);  
        }  
//        $begin = new DateTime( $start_date );
//        $end = (new DateTime( $end_date ))->modify('+24 hours');
//
//        $interval = DateInterval::createFromDateString('1 day');
//        $period = new DatePeriod($begin, $interval, $end);
        $i = 0;
        foreach ( $new_period as $dt )
        {
            $stu_att_all_data[$i]['DATE']=$dt; //$dt->format( "Y-m-d" )
            $stu_att_all_data[$i]['CONTENT']=0;
            foreach($student_attendance_data as $sad)
            {
                if(strtotime($sad['SCHOOL_DATE']) == strtotime($dt))    //$dt->format( "Y-m-d" )
                {
                    $stu_att_all_data[$i]['CONTENT']=1;
                    $stu_att_all_data[$i]['STATE_VALUE']=$sad['STATE_VALUE'];
                    $stu_att_all_data[$i]['SCHOOL_DATE']=$sad['SCHOOL_DATE'];
                    $stu_att_all_data[$i]['PRESENT']=$sad['PRESENT'];
                    $stu_att_all_data[$i]['STATE_CODE']=$sad['STATE_CODE'];
                }
            }
            $i++;
        }

        if(count($student_attendance_data)>0)
        {
            $success = 1;
            $msg = 'Nil';
        }
        else 
        {
            $success = 0;
            $msg = 'No data found';
        }
        if(count($stu_att_all_data)>0)
        {
            $attsuccess = 1;
            $attmsg = 'Nil';
        }
        else 
        {
            $attsuccess = 0;
            $attmsg = 'No data found';
        }
        $MP_TYPE_RET=DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=\''.UserMP().'\' LIMIT 1'));
        $MP_TYPE=$MP_TYPE_RET[1]['MP_TYPE'];
        if($MP_TYPE=='year')
        {
            $MP_TYPE='FY';
        }
        else if($MP_TYPE=='semester')
        {
            $MP_TYPE='SEM';
        }
        else if($MP_TYPE=='quarter')
        {
            $MP_TYPE='QTR';
        }
        else
        {
            $MP_TYPE='';
        }

        if($_REQUEST['selected_date']!='')
        {
           $sql = 'SELECT
                    cp.TITLE as COURSE_PERIOD,cp.SHORT_NAME as CP_SHORT_NAME,sp.TITLE as PERIOD,cpv.PERIOD_ID, cp.COURSE_PERIOD_ID,CONCAT(sp.START_TIME,\''.' - '.'\',sp.END_TIME) AS TIME_PERIOD
                FROM
                    schedule s,courses c,course_periods cp,course_period_var cpv,school_periods sp
                WHERE
                    s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                    AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID = sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\'
                    AND s.SYEAR = c.SYEAR AND (cp.MARKING_PERIOD_ID IN ('.GetAllMPWs($MP_TYPE,UserMP(),  UserSyear(),  UserSchool()).') OR cp.MARKING_PERIOD_ID IS NULL)
                    AND s.STUDENT_ID=\''.$student_id.'\' AND s.SYEAR=\''.UserSyear().'\'
                    AND (\''.date('Y-m-d',strtotime($_REQUEST['selected_date'])).'\' BETWEEN s.START_DATE AND s.END_DATE OR s.END_DATE IS NULL)
                GROUP BY cpv.COURSE_PERIOD_ID ORDER BY sp.SORT_ORDER
                '; 
            $schedule_RET = DBGet(DBQuery($sql));
            $sql = 'SELECT ap.SCHOOL_DATE,ap.COURSE_PERIOD_ID,ac.SHORT_NAME,ac.STATE_CODE,ac.DEFAULT_CODE FROM attendance_period ap,attendance_codes ac WHERE ap.SCHOOL_DATE BETWEEN \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\' AND ap.ATTENDANCE_CODE=ac.ID AND ap.STUDENT_ID=\''.$student_id.'\'';
            $attendance_RET = DBGet(DBQuery($sql),array(),array('SCHOOL_DATE','COURSE_PERIOD_ID'));
            
            foreach($schedule_RET as $sch_data)
            {
                $time=explode(' - ',$sch_data['TIME_PERIOD']);
                $sch_data['PERIOD_TIME_TEXT'] = date("g:i A", strtotime($time[0])).' - '.date("g:i A", strtotime($time[1]));
                $sch_data['SHORT_NAME'] = $attendance_RET[$_REQUEST['selected_date']][$sch_data['COURSE_PERIOD_ID']][1]['SHORT_NAME'];
                $sch_data['STATE_CODE'] = $attendance_RET[$_REQUEST['selected_date']][$sch_data['COURSE_PERIOD_ID']][1]['STATE_CODE'];
                $sch_data['DEFAULT_CODE'] = $attendance_RET[$_REQUEST['selected_date']][$sch_data['COURSE_PERIOD_ID']][1]['DEFAULT_CODE'];
                $student_schedule_data[] = $sch_data;
            }
        }
        if(count($student_schedule_data)>0)
        {
            $sch_success = 1;
            $sch_msg = 'Nil';
        }
        else 
        {
            $sch_success = 0;
            $sch_msg = 'No data found';
        }

        $data['mp_data'] = $mp_data;
        $data['mp_id'] = $mp_id;
        $data['mp_success'] = $mp_success;
        $data['mp_msg'] = $mp_msg;
        $data['calendar_data'] = $calender_data;
        $data['cal_success'] = $cal_success;
        $data['cal_msg'] = $cal_msg;
        $data['student_attendance_data'] = $student_attendance_data;
        $data['att_success'] = $success;
        $data['att_msg'] = $msg;
        $data['student_attendance_all_data'] = $stu_att_all_data;
        $data['att_all_success'] = $attsuccess;
        $data['att_all_msg'] = $attmsg;
        $data['student_schedule_data'] = $student_schedule_data;
        $data['sch_success'] = $sch_success;
        $data['sch_msg'] = $sch_msg;
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
function _makeStateValue($value)
{	
    global $THIS_RET,$date;

    if($value=='0.0')
    return 'None';
    elseif($value=='.5')
    return 'Half-Day';
    else
    return 'Full-Day';
}

function _makeStateCode($value)
{	
    global $THIS_RET,$date;

    if($value=='0.0')
    return 'A';
    elseif($value > 0 && $value < 1)
    return 'H';
    else
    return 'P';
}

echo json_encode($data);
?>
