<?php
include '../Data.php';

include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$allMP = $_REQUEST['mp_type'];
$teacher['UserMP'] = $mp_id = $_REQUEST['mp_id'];

$user_id=$teacher_id;
$username_user=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID='.$user_id.' AND PROFILE_ID='.UserWs('PROFILE_ID')));
$username_user=$username_user[1]['USERNAME'];

$schools=DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID='.$user_id.' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\''.date('Y-m-d').'\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\''.date('Y-m-d').'\') '));
$schools=$schools[1]['SCHOOL_ID'];

$course_periods=DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM course_periods WHERE TEACHER_ID='.$user_id.' OR SECONDARY_TEACHER_ID='.$user_id));
$course_periods=$course_periods[1]['COURSE_PERIOD_ID'];


$tmp_q='';
$tmp_a=array();
$tmp_stu_arr=array();
$tmp_stf_arr=array();
$tmp_p_arr=array();
$tmp_stf_arr[]=$teacher_id;
if($course_periods!='')
{
    $tmp_q=DBGet(DBQuery('SELECT DISTINCT se.STUDENT_ID FROM student_enrollment se,schedule s WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND se.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID IN ('.$course_periods.')'));
    foreach($tmp_q as $tmp_a)
    {
        $tmp_stu_arr[]=$tmp_a['STUDENT_ID'];
        $tmp_qa=DBGet(DBQuery('SELECT DISTINCT PERSON_ID FROM students_join_people WHERE STUDENT_ID='.$tmp_a['STUDENT_ID']));
        foreach($tmp_qa as $tmp_aa)
        {
            $tmp_p_arr[]=$tmp_aa['PERSON_ID'];
        }

    }

}

$tmp_q='';
$tmp_a=array();
$tmp_q=DBGet(DBQuery('SELECT s.STAFF_ID FROM staff s,staff_school_relationship ssr WHERE PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\''.date('Y-m-d').'\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\''.date('Y-m-d').'\') AND ssr.SCHOOL_ID IN ('.$schools.')'));
foreach($tmp_q as $tmp_a)
{
    $tmp_stf_arr[]=$tmp_a['STAFF_ID'];

}
    
            $sql_staff="SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and username IS NOT NULL and login_authentication.profile_id NOT IN(3,4) AND staff.staff_id in (".implode(',',$tmp_stf_arr).") ORDER BY last_name";
            $sql_student="SELECT * FROM login_authentication,students WHERE login_authentication.user_id=students.student_id and username IS NOT NULL and login_authentication.profile_id=3 ".(count($tmp_stu_arr)>0?" AND students.student_id IN (".implode(',',$tmp_stu_arr).")":"")." ORDER BY last_name";
            $sql_people="SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and username IS NOT NULL and login_authentication.profile_id=4 ".(count($tmp_p_arr)>0?" AND people.staff_id IN (".implode(',',$tmp_p_arr).")":"")." ORDER BY last_name";
           
           $result_staff = DBGet(DBQuery($sql_staff));
           $result_student = DBGet(DBQuery($sql_student));
           $result_people = DBGet(DBQuery($sql_people));

        $i = 0;
	if(count($result_staff)>0)
	{
		foreach($result_staff as $row)
		{
			$str = strtolower($row['LAST_NAME'].' '.$row['FIRST_NAME'].','.$row['USERNAME']);
                        if(trim($row['USERNAME']!=""))
                        {
                            $data['users'][$i]['id'] = 'search'.$row['STAFF_ID'];
                            $data['users'][$i]['username'] = $row['USERNAME'];
                            $data['users'][$i]['first_name'] = $row['FIRST_NAME'];
                            $data['users'][$i]['string'] = $str;
                            $i++;
                        }
                       
		}
	}
        
        
        if(count($result_student)>0 && count($tmp_stu_arr)>0)
	{
		foreach($result_student as $row_student)
		{
			$str = strtolower($row_student['LAST_NAME'].' '.$row_student['FIRST_NAME'].','.$row_student['USERNAME']);
                        if(trim($row_student['USERNAME']!=""))
                        {
                            $data['users'][$i]['id'] = 'search'.$row_student['STUDENT_ID'];
                            $data['users'][$i]['username'] = $row_student['USERNAME'];
                            $data['users'][$i]['first_name'] = $row_student['FIRST_NAME'];
                            $data['users'][$i]['string'] = $str;
                            $i++;
                        }
                        
                     
		}
	}
        
        if(count($result_people)>0 && count($tmp_p_arr)>0)
	{
                foreach($result_people as $row_people)
		{
			$str = strtolower($row_people['LAST_NAME'].' '.$row_people['FIRST_NAME'].','.$row_people['USERNAME']);
                        if(trim($row_people['USERNAME']!=""))
                        {
                            $data['users'][$i]['id'] = 'search'.$row_people['STAFF_ID'];
                            $data['users'][$i]['username'] = $row_people['USERNAME'];
                            $data['users'][$i]['first_name'] = $row_people['FIRST_NAME'];
                            $data['users'][$i]['string'] = $str;
                            $i++;
                        }
                     
		}
	}
       
$group_id=DBGet(DBQuery("select distinct group_id,group_name from mail_group where user_name='".$username_user."' "));

if(count($group_id)>0)
        {
            foreach($group_id as $row)    
            {
                $str=strtolower($row['GROUP_NAME']);
                $id=$row['GROUP_ID'];
                $group=DBGet(DBQuery("select * from mail_groupmembers where group_id=$id"));
                foreach($group as $r)
                {
                    $name[]=$r['USER_NAME'];
                }
                if(!empty($name) && count($name)>0)
                $username=implode(',',$name);
        $data['users'][$i]['id'] = 'search'.$row['GROUP_ID'];
        $data['users'][$i]['username'] = $row['GROUP_NAME'];
        $data['users'][$i]['first_name'] = $row['GROUP_NAME'];
        $data['users'][$i]['string'] = $str;
                $i++;
            }
        }
$grp_lst = array();
$grp_lst[0]['GROUP_ID'] = '';
$grp_lst[0]['GROUP_NAME'] = 'Select Group';

$groupList = DBGet(DBQuery("SELECT GROUP_ID,GROUP_NAME FROM mail_group where user_name='".UserWs('USERNAME')."'"));
foreach($groupList as $gl)
{
    $grp_lst[] = $gl;
}
$data['group_info'] = $grp_lst;

$RET = DBGet(DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='".UserSyear()."' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='".UserSchool()."' AND (TEACHER_ID='".$teacher_id."' OR SECONDARY_TEACHER_ID='".$teacher_id."') AND (MARKING_PERIOD_ID IN (".GetAllMPWs($allMP,$mp_id,UserSyear(),UserSchool()).") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='".date('Y-m-d')."' AND END_DATE>='".date('Y-m-d')."')) group by (cp.COURSE_PERIOD_ID)"));

$course_preriods = array();
foreach($RET as $dt)
{
    $course_preriods[] = $dt;
}
$data['course_period_info'] = $course_preriods;

$mail_data = array();
if(isset($_REQUEST['action_type']) && $_REQUEST['action_type']=='reply')
{
    $mail_id=$_REQUEST['mail_id'];
    $mail_body="select mail_Subject,from_user from msg_inbox where mail_id='$mail_id'";

    $mail_body_info=DBGet(DBQuery($mail_body));
    $fromUser = $mail_body_info[1]['FROM_USER'];
    $sub=$mail_body_info[1]['MAIL_SUBJECT'];
    $mail_data['from_user'] = $fromUser;
    $mail_data['subject'] = $sub;
    
}
$data['selected_mail_data'] = $mail_data;
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
