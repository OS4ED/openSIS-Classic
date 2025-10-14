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
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
$view_type = $_REQUEST['view_type'];
if($view_type == 'subject')
{
    $subject_data = array();
    $sql = "SELECT SUBJECT_ID,TITLE,(SELECT COUNT(*) FROM courses WHERE courses.SUBJECT_ID = course_subjects.SUBJECT_ID) AS COURSE_COUNT FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    if(count($subjects_RET)>0)
    {
        foreach($subjects_RET as $sub)
        {
            $subject_data[] = $sub;
        }    
        $data['subjects'] = $subject_data;
        $data['success'] = 1;
        $data['msg'] = 'Nil';
    }
    else 
    {
        $data['subjects'] = $subject_data;
        $data['success'] = 0;
        $data['msg'] = 'No subject found';
    }
}
elseif($view_type == 'course')
{
    $course_data = array();
    $sql = "SELECT COURSE_ID,c.TITLE,c.SHORT_NAME,c.GRADE_LEVEL, CONCAT_WS(' - ',c.title,sg.title) AS GRADE_COURSE,(SELECT COUNT(*) FROM course_periods cp WHERE cp.COURSE_ID = c.COURSE_ID AND (cp.marking_period_id IN(" . GetAllMPWs(GetMPTable(GetMP(UserMP(), 'TABLE',$_REQUEST['syear'],$_REQUEST['school_id'])), UserMP(),$_REQUEST['syear'],$_REQUEST['school_id']) . ") OR (MARKING_PERIOD_ID IS NULL))) AS PERIOD_COUNT FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='".$_REQUEST['subject_id']."' ORDER BY c.TITLE";
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    
    $subject_data = array();
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    if(count($subjects_RET)>0)
    {
        foreach($subjects_RET as $sub)
        {
            $subject_data[] = $sub;
        }    
        $data['subjects'] = $subject_data;
        $data['sub_success'] = 1;
        $data['sub_msg'] = 'Nil';
    }
    if(count($courses_RET)>0)
    {
        foreach($courses_RET as $crs)
        {
            $course_data[] = $crs;
        } 
        $data['selected_subject'] = $_REQUEST['subject_id'];
        $data['courses'] = $course_data;
        $data['success'] = 1;
        $data['msg'] = 'Nil';
    }
    else 
    {
        $data['selected_subject'] = $_REQUEST['subject_id'];
        $data['courses'] = $course_data;
        $data['success'] = 0;
        $data['msg'] = 'No Course found';
    }
}
elseif($view_type == 'period')
{
    $course_data = array();
    $sql = "SELECT c.COURSE_ID,c.TITLE FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='".$_REQUEST['subject_id']."' ORDER BY c.TITLE";
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    if(count($courses_RET)>0)
    {
        foreach($courses_RET as $crs)
        {
            $course_data[] = $crs;
        } 
        $data['selected_subject'] = $_REQUEST['subject_id'];
        $data['courses'] = $course_data;
        $data['course_success'] = 1;
        $data['course_msg'] = 'Nil';
    }
    
    $sql = "SELECT COURSE_PERIOD_ID,TITLE,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS,CREDITS FROM course_periods WHERE COURSE_ID='".$_REQUEST['course_id']."' AND (marking_period_id IN(" . GetAllMPWs(GetMPTable(GetMP(UserMP(), 'TABLE',$_REQUEST['syear'],$_REQUEST['school_id'])), UserMP(),$_REQUEST['syear'],$_REQUEST['school_id']) . ") OR (MARKING_PERIOD_ID IS NULL)) ORDER BY TITLE";
    $QI = DBQuery($sql);
    $periods_RET = DBGet($QI);
    if(count($periods_RET)>0)
    {
        foreach($periods_RET as $per)
        {
            $period_data[] = $per;
        } 
        $data['selected_subject'] = $_REQUEST['subject_id'];
        $data['selected_course'] = $_REQUEST['course_id'];
        $data['periods'] = $period_data;
        $data['success'] = 1;
        $data['msg'] = 'Nil';
    }
    else 
    {
        $data['selected_subject'] = $_REQUEST['subject_id'];
        $data['selected_course'] = $_REQUEST['course_id'];
        $data['periods'] = $period_data;
        $data['success'] = 0;
        $data['msg'] = 'No Period found';
    }
}
elseif($view_type == 'details')
{
    $sql = "SELECT COURSE_PERIOD_ID,TITLE,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS,CREDITS FROM course_periods WHERE COURSE_ID='".$_REQUEST['course_id']."' AND (marking_period_id IN(" . GetAllMPWs(GetMPTable(GetMP(UserMP(), 'TABLE',$_REQUEST['syear'],$_REQUEST['school_id'])), UserMP(),$_REQUEST['syear'],$_REQUEST['school_id']) . ") OR (MARKING_PERIOD_ID IS NULL)) ORDER BY TITLE";
    $QI = DBQuery($sql);
    $periods_RET = DBGet($QI);
    if(count($periods_RET)>0)
    {
        foreach($periods_RET as $per)
        {
            $period_data[] = $per;
        } 
        $data['periods'] = $period_data;
        $data['periods_success'] = 1;
        $data['periods_msg'] = 'Nil';
    }
    else 
    {
        $data['periods'] = $period_data;
        $data['periods_success'] = 0;
        $data['periods_msg'] = 'No Period found';
    }
    
    $period_details = array();
    $details = array();
    $sql = "SELECT PARENT_ID,TITLE,SHORT_NAME,MP,MARKING_PERIOD_ID,TEACHER_ID,(SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) FROM staff WHERE STAFF_ID=cp.TEACHER_ID) AS PRIMARY_TEACHER,SECONDARY_TEACHER_ID,(SELECT TITLE FROM school_calendars WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND school_calendars.CALENDAR_ID=cp.CALENDAR_ID) AS CALENDAR_NAME,CALENDAR_ID,IF(MARKING_PERIOD_ID IS NULL,BEGIN_DATE,NULL) AS BEGIN_DATE,
            IF(MARKING_PERIOD_ID IS NULL,END_DATE,NULL) AS END_DATE,
            TOTAL_SEATS,(TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS,
            GRADE_SCALE_ID,(SELECT TITLE FROM report_card_grade_scales WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND ID = cp.GRADE_SCALE_ID) AS GRADE_SCALE_NAME,DOES_HONOR_ROLL,DOES_CLASS_RANK,
            GENDER_RESTRICTION,HOUSE_RESTRICTION,CREDITS,
            HALF_DAY,DOES_BREAKOFF,COURSE_WEIGHT,SCHEDULE_TYPE
            FROM course_periods cp 
            WHERE cp.COURSE_PERIOD_ID='".$_REQUEST['cp_id']."'";
//    $sql = "SELECT PARENT_ID,TITLE,SHORT_NAME,MP,MARKING_PERIOD_ID,TEACHER_ID,(SELECT CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) FROM staff WHERE STAFF_ID=cp.TEACHER_ID) AS PRIMARY_TEACHER,SECONDARY_TEACHER_ID,(SELECT TITLE FROM school_calendars WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND school_calendars.CALENDAR_ID=cp.CALENDAR_ID) AS CALENDAR_NAME,CALENDAR_ID,IF(MARKING_PERIOD_ID IS NULL,BEGIN_DATE,NULL) AS BEGIN_DATE,
//            IF(MARKING_PERIOD_ID IS NULL,END_DATE,NULL) AS END_DATE,
//            TOTAL_SEATS,(TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS,
//            GRADE_SCALE_ID,(SELECT TITLE FROM report_card_grade_scales WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND ID = cp.GRADE_SCALE_ID) AS GRADE_SCALE_NAME,DOES_HONOR_ROLL,DOES_CLASS_RANK,
//            GENDER_RESTRICTION,HOUSE_RESTRICTION,CREDITS,
//            HALF_DAY,DOES_BREAKOFF,COURSE_WEIGHT,DAYS,PERIOD_ID,(SELECT TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' AND PERIOD_ID = cpv.PERIOD_ID) AS PERIOD_NAME,ROOM_ID,(SELECT TITLE FROM rooms WHERE SCHOOL_ID='" . UserSchool() . "' AND ROOM_ID = cpv.ROOM_ID) AS ROOM_NAME,DOES_ATTENDANCE,SCHEDULE_TYPE
//            FROM course_periods cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id)
//            WHERE cp.COURSE_PERIOD_ID='".$_REQUEST['cp_id']."'";
            $QI = DBQuery($sql);
            $RET = DBGet($QI);
            if(count($RET)>0)
            {
                foreach($RET as $value)
                {
                    $value['MP_TITLE'] = GetMP($value['MARKING_PERIOD_ID'],'TITLE',UserSyear(),UserSchool());
                    
                    $periodSql = 'SELECT DAYS,PERIOD_ID,(SELECT TITLE FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND PERIOD_ID = cpv.PERIOD_ID) AS PERIOD_NAME,ROOM_ID,(SELECT TITLE FROM rooms WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND ROOM_ID = cpv.ROOM_ID) AS ROOM_NAME,DOES_ATTENDANCE FROM course_period_var cpv WHERE cpv.COURSE_PERIOD_ID ='.$value['PARENT_ID'];
                    $QI = DBQuery($periodSql);
                    $RET1 = DBGet($QI);
                    if(Count($RET1)>0)
                    {
                        foreach($RET1 as $val)
                        {
                            $val['DAYS_OF_WEEK'] = _makeDays($val['DAYS'],'');
                            $value['PERIOD_DETAILS'][] = $val;
                        }
                        $value['PERIOD_SUCCESS'] = 1;
                    }
                    else 
                    {
                        $value['PERIOD_DETAILS'] = array();
                        $value['PERIOD_SUCCESS'] = 0;
                    }
                    $details[]=$value;
                }
                 
                $data['selected_subject'] = $_REQUEST['subject_id'];
                $data['selected_course'] = $_REQUEST['course_id'];
                $data['selected_period'] = $_REQUEST['cp_id'];
                $data['details'] = $details;
                $data['success'] = 1;
                $data['msg'] = 'Nil';
            }
            else 
            {
                $data['selected_subject'] = $_REQUEST['subject_id'];
                $data['selected_course'] = $_REQUEST['course_id'];
                $data['selected_period'] = $_REQUEST['cp_id'];
                $data['details'] = $details;
                $data['success'] = 0;
                $data['msg'] = 'No Data found';
            }
            
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
