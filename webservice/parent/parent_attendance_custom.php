<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

include '../function/ProperDateFnc.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];
$data['selected_student']=$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
$school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
$school_RET = DBGet(DBQuery($school_sql));
$_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];

$period_data = array();
$days_RET = array();

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

$periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.SHORT_NAME FROM school_periods sp,schedule s,course_periods cp,course_period_var cpv WHERE sp.SCHOOL_ID=\''.UserSchool().'\' AND sp.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND s.STUDENT_ID=\''.$student_id.'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' ORDER BY sp.SORT_ORDER'));        

$i = 0;
foreach($periods_RET as $period)
{
    $period_data[$i]['PERIOD_ID'] = $period['PERIOD_ID'];
    $period_data[$i]['SHORT_NAME'] = $period['SHORT_NAME'];
    $i++;
}
$absences_RET = DBGet(DBQuery('SELECT ap.STUDENT_ID,ap.PERIOD_ID,ap.SCHOOL_DATE,ac.SHORT_NAME,ad.STATE_VALUE,ad.COMMENT AS OFFICE_COMMENT,ap.COMMENT AS TEACHER_COMMENT,ac.STATE_CODE FROM attendance_period ap,attendance_day ad,attendance_codes ac WHERE ap.STUDENT_ID=ad.STUDENT_ID AND ap.SCHOOL_DATE=ad.SCHOOL_DATE AND ap.ATTENDANCE_CODE=ac.ID  AND ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\''),array(),array('SCHOOL_DATE','PERIOD_ID'));        

$i = 0;
foreach($absences_RET as $school_date=>$absences)
{
    $school_date_data[$i]['SCHOOL_DATE']=$school_date;
    $days_RET[$i]['SCHOOL_DATE'] = $school_date;
    $days_RET[$i]['DAILY'] = _makeStateValue($absences[key($absences)][1]['STATE_VALUE']);
    $days_RET[$i]['OFFICE_COMMENT'] = $absences[key($absences)][1]['OFFICE_COMMENT'];
    $days_RET[$i]['TEACHER_COMMENT'] = $absences[key($absences)][1]['TEACHER_COMMENT'];
    $days_RET[$i]['PERIOD_INFO']=array();
    foreach($period_data as $pdata)
    {
        $day_period_data = array();
        $period_data_RET = DBGet(DBQuery('SELECT ap.STUDENT_ID,ap.PERIOD_ID,ap.SCHOOL_DATE,ac.SHORT_NAME,ac.STATE_CODE,ap.ATTENDANCE_CODE,ap.COMMENT AS TEACHER_COMMENT,ac.STATE_CODE FROM attendance_period ap,attendance_codes ac WHERE ap.ATTENDANCE_CODE=ac.ID AND ap.PERIOD_ID=\''.$pdata['PERIOD_ID'].'\' AND ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\''),array(),array('SCHOOL_DATE','PERIOD_ID'));  
        $day_period_data['PERIOD_ID']=$pdata['PERIOD_ID'];
        $day_period_data['SHORT_NAME']=$pdata['SHORT_NAME'];
        $day_period_data['STATE_CODE']=((isset($period_data_RET[$school_date][$pdata['PERIOD_ID']][1]['ATTENDANCE_CODE']))?$period_data_RET[$school_date][$pdata['PERIOD_ID']][1]['SHORT_NAME']:'');
        $days_RET[$i]['PERIOD_INFO'][]=$day_period_data;
    }
    $i++;
}
$data['student_data'] = $days_RET;
if(Count($days_RET)>0)
{
    $data['student_data_success'] = 1;
}
else 
{
    $data['student_data_success'] = 0;
}

$data['school_date_data'] = $school_date_data;
if(Count($school_date_data)>0)
{
    $data['school_date_data_success'] = 1;
}
else 
{
    $data['school_date_data_success'] = 0;
}

$data['period_data'] = $period_data;
if(Count($period_data)>0)
{
    $data['period_data_success'] = 1;
}
else 
{
    $data['period_data_success'] = 0;
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

echo json_encode($data);
?>
