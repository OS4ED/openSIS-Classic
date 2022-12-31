<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$_SESSION['student_id'] = $_SESSION['STUDENT_ID'] = $student_id = $_REQUEST['student_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$action = $_REQUEST['action'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$student_id && $auth_data['user_profile']=='student')
    {
        $schedule_data = array();
        if($action=='add_view')
        {
//            $subject_data[0]=array('SUBJECT_ID'=>'','TITLE'=>'Select Subject');
            $sub_success = 1;
//            $course_data[0]=array('COURSE_ID'=>'','TITLE'=>'Select Course');
            $course_success = 1;
            $teacher_data[0]=array('TEACHER_ID'=>'','NAME'=>'N/A');
            $teacher_success = 1;
            $period_data = array();
            $period_data[0]=array('PERIOD_ID'=>'','TITLE'=>'N/A');
            $period_success = 1;
            $selected_subject = '';
            $selected_course = '';
            $subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
            $subject_data = array();
            $course_data = array();
            foreach($subjects_RET as $sub)
            {
                $subject_data[]=$sub;
            }
            if(count($subject_data)>0)
            {
                $sub_success = 1;
            }
            else 
            {
                $sub_success = 0;
            }

            if(isset($_REQUEST['subject_id']) && $_REQUEST['subject_id'] != '')
            {
                $selected_subject = $_REQUEST['subject_id'];
                $course_data = array();
                $courses_RET = DBGet(DBQuery('SELECT c.COURSE_ID,c.TITLE FROM courses c WHERE '.($_REQUEST['subject_id']?'c.SUBJECT_ID=\''.$_REQUEST['subject_id'].'\' AND ':'').'UPPER(c.TITLE) LIKE \''.strtoupper($_REQUEST['course_title']).'%'.'\' AND c.SYEAR=\''.UserSyear().'\' AND c.SCHOOL_ID=\''.UserSchool().'\''));        
                foreach($courses_RET as $course)
                {
                    $course_data[]=$course;
                }
                if(count($course_data)>0)
                {
                    $course_success = 1;
                }
                else 
                {
                    $course_success = 0;
                }
            }

            if(isset($_REQUEST['course_id']) && $_REQUEST['course_id'] != '')
            {
                $selected_course = $_REQUEST['course_id'];
                $teachers_RET = DBGet(DBQuery('SELECT DISTINCT s.FIRST_NAME,s.LAST_NAME,s.STAFF_ID AS TEACHER_ID FROM staff s,course_periods cp WHERE s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_ID=\''.$_REQUEST['course_id'].'\''));
                $teacher_data=array();
                $teacher_data[0]=array('TEACHER_ID'=>'','NAME'=>'N/A');
                foreach($teachers_RET as $teacher)
                {
                    $teacher_data[] = array('TEACHER_ID'=>$teacher['TEACHER_ID'],'NAME'=>$teacher['FIRST_NAME'].' '.$teacher['LAST_NAME']);
                }
                if(count($teacher_data)>0)
                {
                    $teacher_success = 1;
                }
                else 
                {
                    $teacher_success = 0;
                }
                $periods_RET = DBGet(DBQuery('SELECT DISTINCT p.TITLE,p.PERIOD_ID FROM school_periods p,course_periods cp,course_period_var cpv WHERE p.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_ID=\''.$_REQUEST['course_id'].'\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
                foreach($periods_RET as $period)
                {
                    $period_data[] = array('PERIOD_ID'=>$period['PERIOD_ID'],'TITLE'=> $period['TITLE']);
                }
                if(count($period_data)>0)
                {
                    $period_success = 1;
                }
                else 
                {
                    $period_success = 0;
                }
            }
            if($sub_success ==1 || $course_success == 1 || $teacher_success == 1 || $period_success == 1)
                $success = 1;
            else 
                $success = 0;
            $data = array('selected_subject'=>$selected_subject,'selected_course'=>$selected_course,'subject_data'=>$subject_data,'Subject_success'=>$sub_success,'course_data'=>$course_data,'course_success'=>$course_success,'teacher_data'=>$teacher_data,'teacher_success'=>$teacher_success,'period_data'=>$period_data,'period_success'=>$period_success,'success'=>$success);	
        }
        if($action=='edit_view')
        {
            $subject_data = array();
//            $subject_data[0]=array('SUBJECT_ID'=>'','TITLE'=>'Select Subject');
            $sub_success = 0;
            $course_data = array();
//            $course_data[0]=array('COURSE_ID'=>'','TITLE'=>'Select Course');
            $course_success = 0;
            $teacher_data = array();
            $teacher_data[0]=array('TEACHER_ID'=>'','NAME'=>'N/A');
            $teacher_success = 0;
            $period_data = array();
            $period_data[0]=array('PERIOD_ID'=>'','TITLE'=>'N/A');
            $period_success = 0;
            
            $requests_RET = DBGet(DBQuery('SELECT REQUEST_ID,SUBJECT_ID,COURSE_ID,WITH_TEACHER_ID,NOT_TEACHER_ID,WITH_PERIOD_ID,NOT_PERIOD_ID FROM schedule_requests WHERE REQUEST_ID=\''.$_REQUEST['req_id'].'\' AND SYEAR=\''.UserSyear().'\' AND STUDENT_ID=\''.UserStudentIDWs().'\''));//,$functions
            $requests_RET = $requests_RET[1];
            
            $selected_subject = $requests_RET['SUBJECT_ID'];
            if(!isset($_REQUEST['subject_id']))
                $_REQUEST['subject_id'] = $selected_subject;
            $selected_course = $requests_RET['COURSE_ID'];
            $selected_with_teacher = $requests_RET['WITH_TEACHER_ID'];
            $selected_not_teacher = $requests_RET['NOT_TEACHER_ID'];
            $selected_with_period = $requests_RET['WITH_PERIOD_ID'];
            $selected_not_period = $requests_RET['NOT_PERIOD_ID'];

            $subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
            foreach($subjects_RET as $sub)
            {
                $subject_data[]=$sub;
            }
            if(count($subject_data)>0)
            {
                $sub_success = 1;
            }
            else 
            {
                $sub_success = 0;
            }

            if($selected_subject != '')
            {
                $courses_RET = DBGet(DBQuery('SELECT c.COURSE_ID,c.TITLE FROM courses c WHERE '.($selected_subject?'c.SUBJECT_ID=\''.$selected_subject.'\' ':'').' AND c.SYEAR=\''.UserSyear().'\' AND c.SCHOOL_ID=\''.UserSchool().'\'')); // AND 'UPPER(c.TITLE) LIKE \''.strtoupper($_REQUEST['course_title']).'%'.'\       
                foreach($courses_RET as $course)
                {
                    $course_data[]=$course;
                }
                if(count($course_data)>0)
                {
                    $course_success = 1;
                }
                else 
                {
                    $course_success = 0;
                }
            }

            if($selected_course != '')
            {
                $teachers_RET = DBGet(DBQuery('SELECT DISTINCT s.FIRST_NAME,s.LAST_NAME,s.STAFF_ID AS TEACHER_ID FROM staff s,course_periods cp WHERE s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_ID=\''.$selected_course.'\''));
                foreach($teachers_RET as $teacher)
                {
                    $teacher_data[] = array('TEACHER_ID'=>$teacher['TEACHER_ID'],'NAME'=>$teacher['FIRST_NAME'].' '.$teacher['LAST_NAME']);
                }
                if(count($teacher_data)>0)
                {
                    $teacher_success = 1;
                }
                else 
                {
                    $teacher_success = 0;
                }
                $periods_RET = DBGet(DBQuery('SELECT DISTINCT p.TITLE,p.PERIOD_ID FROM school_periods p,course_periods cp,course_period_var cpv WHERE p.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_ID=\''.$selected_course.'\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));
                foreach($periods_RET as $period)
                {
                    $period_data[] = array('PERIOD_ID'=>$period['PERIOD_ID'],'TITLE'=> $period['TITLE']);
                }
                if(count($period_data)>0)
                {
                    $period_success = 1;
                }
                else 
                {
                    $period_success = 0;
                }
            }
            if($sub_success ==1 || $course_success == 1 || $teacher_success == 1 || $period_success == 1)
                $success = 1;
            else 
                $success = 0;
            
            $data = array('selected_subject'=>$selected_subject,'selected_course'=>$selected_course,'selected_with_teacher'=>$selected_with_teacher,'selected_not_teacher'=>$selected_not_teacher,'selected_with_period'=>$selected_with_period,'selected_not_period'=>$selected_not_period,'subject_data'=>$subject_data,'Subject_success'=>$sub_success,'course_data'=>$course_data,'course_success'=>$course_success,'teacher_data'=>$teacher_data,'teacher_success'=>$teacher_success,'period_data'=>$period_data,'period_success'=>$period_success,'success'=>$success);	
        }
        if($action=='view' || $action=='delete' || $action=='add_submit' || $action=='edit_submit')
        {
            if($action=='add_submit')
            {
                $subject_id = $_REQUEST['subject_id'];
                $course_id = $_REQUEST['course_id'];
                $with_teacher_id = (isset($_REQUEST['with_teacher_id']) && $_REQUEST['with_teacher_id']!='')?$_REQUEST['with_teacher_id']:'NULL';
                $not_teacher_id = (isset($_REQUEST['not_teacher_id']) && $_REQUEST['not_teacher_id']!='')?$_REQUEST['not_teacher_id']:'NULL';
                $with_period_id = (isset($_REQUEST['with_period_id']) && $_REQUEST['with_period_id']!='')?$_REQUEST['with_period_id']:'NULL';
                $not_period_id = (isset($_REQUEST['not_period_id']) && $_REQUEST['not_period_id']!='')?$_REQUEST['not_period_id']:'NULL';
                $mp_id = $_REQUEST['mp_id'];
                $same_course_check=DBGet(DBQuery('SELECT COURSE_ID FROM schedule_requests WHERE STUDENT_ID=\''.UserStudentIDWs().'\' AND SYEAR=\''.UserSyear().'\''));
                $flag = 0;
                foreach($same_course_check as $key=>$same_course)
                {
                    if($same_course['COURSE_ID']==$course_id)
                    {
                        $flag=1;
                        $msg = "You have already requested for this course";
                        break;
                    }
                }
                if($with_teacher_id!='NULL' && $not_teacher_id!='NULL' && $with_teacher_id==$not_teacher_id)
                {
                    $flag=1;
                    $msg = 'Teacher Contradiction.';
                }
                if($with_period_id!='NULL' && $not_period_id!='NULL' && $with_period_id==$not_period_id)
                {
                    $flag=1;
                    $msg = 'Period Contradiction.';
                }
                if($flag==0)
                {
                    $ins_success = DBQuery('INSERT INTO schedule_requests (SYEAR,SCHOOL_ID,STUDENT_ID,SUBJECT_ID,COURSE_ID,MARKING_PERIOD_ID,WITH_TEACHER_ID,NOT_TEACHER_ID,WITH_PERIOD_ID,NOT_PERIOD_ID) values('.UserSyear().','.UserSchool().','.UserStudentIDWs().','.$subject_id.','.$course_id.','.$mp_id.','.$with_teacher_id.','.$not_teacher_id.','.$with_period_id.','.$not_period_id.')');
                    if($ins_success)
                        $add_success = 1;
                    else 
                        $add_success = 0;
                    $msg = "";
                }
                else
                {
                    $add_success = 0;
                }
                $data['add_success']=$add_success;
                $data['add_msg']=$msg;
            }
            if($action=='edit_submit')
            {
                $req_id = $_REQUEST['req_id'];
                $subject_id = $_REQUEST['subject_id'];
                $course_id = $_REQUEST['course_id'];
                $with_teacher_id = (isset($_REQUEST['with_teacher_id']) && $_REQUEST['with_teacher_id']!='')?$_REQUEST['with_teacher_id']:'NULL';
                $not_teacher_id = (isset($_REQUEST['not_teacher_id']) && $_REQUEST['not_teacher_id']!='')?$_REQUEST['not_teacher_id']:'NULL';
                $with_period_id = (isset($_REQUEST['with_period_id']) && $_REQUEST['with_period_id']!='')?$_REQUEST['with_period_id']:'NULL';
                $not_period_id = (isset($_REQUEST['not_period_id']) && $_REQUEST['not_period_id']!='')?$_REQUEST['not_period_id']:'NULL';
                $mp_id = $_REQUEST['mp_id'];
                $flag = 0;
                if($with_teacher_id!='NULL' && $not_teacher_id!='NULL' && $with_teacher_id==$not_teacher_id)
                {
                    $flag=1;
                    $msg = 'Teacher Contradiction.';
                }
                if($with_period_id!='NULL' && $not_period_id!='NULL' && $with_period_id==$not_period_id)
                {
                    $flag=1;
                    $msg = 'Period Contradiction.';
                }
                if($flag==0)
                {
                $up_success = DBQuery('UPDATE schedule_requests SET SUBJECT_ID = '.$subject_id.',COURSE_ID = '.$course_id.',WITH_TEACHER_ID = '.$with_teacher_id.',NOT_TEACHER_ID = '.$not_teacher_id.',WITH_PERIOD_ID = '.$with_period_id.',NOT_PERIOD_ID = '.$not_period_id.' WHERE REQUEST_ID = '.$req_id.'');
                    $msg = '';
                }
                if($up_success)
                {
                    $updt_success = 1;
                }
                else 
                {
                    $updt_success = 0;
                }
                $data['update_success']=$updt_success;
                $data['update_msg']=$msg;
            }
            if($action=='delete')
            {
                $req_id = $_REQUEST['req_id'];
                $del = DBQuery('DELETE FROM schedule_requests WHERE REQUEST_ID=\''.paramlib_validation($colmn=PERIOD_ID,$_REQUEST['req_id']).'\'');
                if($del)
                    $data['delete_success']=1;
                else 
                    $data['delete_success']=0;
            }
            $functions = array('COURSE'=>'_makeCourse','WITH_TEACHER_ID'=>'_makeTeacher','NOT_TEACHER_ID'=>'_makeTeacher','WITH_PERIOD_ID'=>'_makePeriod','NOT_PERIOD_ID'=>'_makePeriod');
            $requests_RET = DBGet(DBQuery('SELECT r.REQUEST_ID,c.TITLE as COURSE,r.COURSE_ID,r.COURSE_WEIGHT,r.MARKING_PERIOD_ID,r.WITH_TEACHER_ID,r.NOT_TEACHER_ID,r.WITH_PERIOD_ID,r.NOT_PERIOD_ID FROM schedule_requests r,courses c WHERE r.COURSE_ID=c.COURSE_ID AND r.SYEAR=\''.UserSyear().'\' AND r.STUDENT_ID=\''.UserStudentIDWs().'\''));//,$functions
            $req_data = array();
            foreach($requests_RET as $stu_req)
            {
                $stu_req['WITH_TEACHER_NAME'] = _makeTeacher($stu_req['WITH_TEACHER_ID']);
                $stu_req['WITHOUT_TEACHER_NAME'] = _makeTeacher($stu_req['NOT_TEACHER_ID']);
                $stu_req['WITH_PERIOD_NAME'] = _makePeriod($stu_req['WITH_PERIOD_ID']);
                $stu_req['WITHOUT_PERIOD_NAME'] = _makePeriod($stu_req['NOT_PERIOD_ID']);
                $req_data[]=$stu_req;
            }

            if(count($req_data)>0)
            {
                $success =1;
                $msg = 'Nil';
            }
            else 
            {
                $success =0;
                $msg = 'No data found';
            }
            $data['request_data']=$req_data;
            $data['success']=$success;
            $data['msg']=$msg;
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
        
function _makeCourse($value,$column)
{	global $THIS_RET;
    if($THIS_RET['COURSE_WEIGHT']!='')
	return $value.' - '.$THIS_RET['COURSE_WEIGHT'];	
    else {
        return $value;
    }

}

function _makeTeacher($value,$column)
{	
        global $THIS_RET;
        if($value!='')
        {
            $sel_staff = DBGet(DBQuery('SELECT CONCAT(FIRST_NAME,\' \',LAST_NAME) AS TEACHER_NAME,STAFF_ID AS TEACHER_ID FROM staff WHERE STAFF_ID =\''.$value.'\''));
            return $sel_staff[1]['TEACHER_NAME'];
        }
        else 
            return '';
}

function _makePeriod($value,$column)
{	
        global $THIS_RET;
        if($value!='')
        {
            $sel_staff = DBGet(DBQuery('SELECT TITLE FROM school_periods WHERE PERIOD_ID =\''.$value.'\''));
            return $sel_staff[1]['TITLE'];
        }
        else 
            return '';
}

echo json_encode($data);
?>
