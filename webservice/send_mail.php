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
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']==$_REQUEST['profile'])
    {
$mail_details=$_REQUEST["mail_details"];
$columns = json_decode($mail_details,TRUE);

$_REQUEST['txtToUser'] = $columns[0]['txtToUser'];
$to_arr_data = explode(',',$_REQUEST['txtToUser']);
$to_arr = array();
foreach($to_arr_data as $tarr)
{
    $to_arr[]=trim($tarr);
}
$_REQUEST['txtToUser'] = implode(',',$to_arr);
$_REQUEST['groups'] = $columns[0]['groups'];
$_REQUEST['cp_id'] = $columns[0]['cp_id'];
$_REQUEST['list_gpa_student'] = $columns[0]['list_gpa_student'];
$_REQUEST['list_gpa_parent'] = $columns[0]['list_gpa_parent'];
$_REQUEST['txtToCCUser'] = $columns[0]['txtToCCUser'];
$_REQUEST['txtToBCCUser'] = $columns[0]['txtToBCCUser'];
$_REQUEST['txtSubj'] = $columns[0]['txtSubj'];
$_REQUEST['txtBody'] = $columns[0]['txtBody'];
$_REQUEST['key'] = $columns[0]['key'];
$_REQUEST['attachments'] = $columns[0]['attachments'];


//if($_REQUEST['button']=='Send')
//{
        if(UserWs('PROFILE')=='teacher' && $_REQUEST['cp_id']!='')
        {
            if($_REQUEST['list_gpa_student']=='Y')
            {
            $sch_stu=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,schedule s WHERE  s.COURSE_PERIOD_ID ='.$_REQUEST['cp_id'].' AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3 AND la.USERNAME IS NOT NULL '));
            foreach($sch_stu as $sch_stua)
            $sch_stu_arr[]=$sch_stua['USERNAME'];

            }
         
            if($_REQUEST['list_gpa_parent']=='Y')
            {
             $sch_p=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,students_join_people sjp,schedule s WHERE sjp.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID ='.$_REQUEST['cp_id'].'  AND la.USER_ID=sjp.PERSON_ID AND la.PROFILE_ID=4 AND la.USERNAME IS NOT NULL '));
             foreach($sch_p as $sch_pa)
             $sch_p_arr[]=$sch_pa['USERNAME'];  
            }   
            
            if(count($sch_stu_arr)>0 || count($sch_p_arr)>0)
            {
            if(count($sch_stu_arr)>0)
            $_REQUEST['txtToUser']=implode(',',$sch_stu_arr);
            if(count($sch_stu_arr)>0 && count($sch_p_arr)>0)
            $_REQUEST['txtToUser']=$_REQUEST['txtToUser'].','.implode(',',$sch_p_arr);
            elseif(count($sch_stu_arr)==0 && count($sch_p_arr)>0)
            $_REQUEST['txtToUser']=implode(',',$sch_p_arr);
            }
//            else
//            {
//                echo "<script type='text/javascript'>load_link('Modules.php?modname=messaging/Inbox.php&failed_user=Y');</script>";
//            }
            }
        
        if(UserWs('PROFILE')=='student')
            $user_id=UserStudentIDWs();
        else
            $user_id=UserWs('STAFF_ID');
       
        $username_user=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID='.$user_id.' AND PROFILE_ID='.UserWs('PROFILE_ID')));
        $username_user=$username_user[1]['USERNAME'];
        
        $to_array=$_REQUEST['txtToUser'];
        $to_cc_array=$_REQUEST['txtToCCUser'];
        $to_bcc_array=$_REQUEST['txtToBCCUser'];
        
        if($to_array!='')
        $to_array=explode(',',$to_array);
        if($to_cc_array!='')
        $to_cc_array=explode(',',$to_cc_array);
        if($to_bcc_array!='')
        $to_bcc_array=explode(',',$to_bcc_array);
        
        if(count($to_array)>0)
        {
            foreach($to_array as $ta)
            {
                $temp_to=array();
                $group_check=DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\''.$ta.'\' AND mg.USER_NAME=\''.$username_user.'\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if(count($group_check)>0)
                {
                    foreach($group_check as $gq)
                     $temp_to[]=$gq['USER_NAME'];
                }
                if(count($temp_to)>0)
                {
                    $replace=implode(',',$temp_to);
//                    echo " str_replace($ta,$replace,$_REQUEST[txtToUser])";
                    $_REQUEST['txtToUser']=  str_replace($ta,$replace,$_REQUEST['txtToUser']);
                }
                
            }
            
        }
        if(count($to_cc_array)>0)
        {
            foreach($to_cc_array as $ta)
            {
                $temp_cc=array();
                $group_check=DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\''.$ta.'\' AND mg.USER_NAME=\''.$username_user.'\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if(count($group_check)>0)
                {
                    foreach($group_check as $gq)
                     $temp_cc[]=$gq['USER_NAME'];
                }
                if(count($temp_cc)>0)
                {
                    $replace=implode(',',$temp_cc);
                    $_REQUEST['txtToCCUser']=  str_replace($ta,$replace,$_REQUEST['txtToCCUser']);
                }
                
            }
        }
        if(count($to_bcc_array)>0)
        {
            foreach($to_bcc_array as $ta)
            {
                $temp_bcc=array();
                $group_check=DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\''.$ta.'\' AND mg.USER_NAME=\''.$username_user.'\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if(count($group_check)>0)
                {
                    foreach($group_check as $gq)
                     $temp_bcc[]=$gq['USER_NAME'];
                }
                if(count($temp_bcc)>0)
                {
                    $replace=implode(',',$temp_bcc);
                    $_REQUEST['txtToBCCUser']=  str_replace($ta,$replace,$_REQUEST['txtToBCCUser']);
                }
            }
        }
        
        $to_array=$_REQUEST['txtToUser'];
        $to_cc_array=$_REQUEST['txtToCCUser'];
        $to_bcc_array=$_REQUEST['txtToBCCUser'];
        if($to_array!='')
        $to_array=explode(',',$to_array);
        if($to_cc_array!='')
        $to_cc_array=explode(',',$to_cc_array);
        if($to_bcc_array!='')
        $to_bcc_array=explode(',',$to_bcc_array);
              
        if(UserWs('PROFILE_ID')!=0 && UserWs('PROFILE')=='admin')
        {
        $schools=DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID='.$user_id.' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\''.date('Y-m-d').'\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\''.date('Y-m-d').'\') '));
        $schools=$schools[1]['SCHOOL_ID'];


        $tmp_q='';
        $tmp_a=array();
        $tmp_arr=array();
        
        $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se WHERE se.STUDENT_ID=la.USER_ID AND la.PROFILE_ID=3 AND se.SCHOOL_ID IN ('.$schools.') AND (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND la.USERNAME IS NOT NULL'));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        

        $tmp_q='';
        $tmp_a=array();
        $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME  FROM login_authentication la,staff_school_relationship ssr,user_profiles up WHERE ssr.SCHOOL_ID IN ('.$schools.') AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\''.date('Y-m-d').'\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\''.date('Y-m-d').'\') AND ssr.STAFF_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=up.ID AND up.PROFILE NOT IN (\'student\',\'parent\')'));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        

        $tmp_q='';
        $tmp_a=array();
        $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME  FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE se.SCHOOL_ID IN ('.$schools.') AND (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=4'));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        
        }    
        elseif(UserWs('PROFILE')=='parent' || UserWs('PROFILE')=='student')
        {
        $course_periods=DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID='.UserStudentIDWs()));
        $course_periods=$course_periods[1]['COURSE_PERIOD_ID'];


        $tmp_q='';
        $tmp_a=array();
        $tmp_q=array();
        
        
        if(UserWs('PROFILE')=='parent')
        {
        $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID='.$user_id.' AND sjp.STUDENT_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=3 '));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        
        }
        if(UserWs('PROFILE')=='student')
        {
        $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,student_enrollment se,students_join_people sjp WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND se.STUDENT_ID=sjp.STUDENT_ID AND sjp.STUDENT_ID='.$user_id.' AND sjp.PERSON_ID=la.USER_ID AND la.USERNAME IS NOT NULL AND la.PROFILE_ID=4 '));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        
        }
        
        
        if($course_periods!='')
        {
            $tmp_q='';
            $tmp_a=array();
            $tmp_q=DBGet(DBQuery('SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID IN ('.$course_periods.') '));
            foreach($tmp_q as $tmp_a)
            {
                $get_la=DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,user_profiles up WHERE la.USER_ID='.$tmp_a['TEACHER_ID'].' AND la.PROFILE_ID=up.ID AND up.PROFILE=\'teacher\' AND la.USERNAME IS NOT NULL'));
                $tmp_arr[]=$get_la[1]['USERNAME'];
                if($tmp_a['SECONDARY_TEACHER_ID']!='')
                {
                $get_la=DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,user_profiles up WHERE la.USER_ID='.$tmp_a['SECONDARY_TEACHER_ID'].' AND la.PROFILE_ID=up.ID AND up.PROFILE=\'teacher\' AND la.USERNAME IS NOT NULL'));
                $tmp_arr[]=$get_la[1]['USERNAME'];
                }
            }

        }

        $tmp_q='';
        $tmp_a=array();
        
        $tmp_q=DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,staff s,staff_school_relationship ssr,user_profiles up WHERE s.PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\''.date('Y-m-d').'\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\''.date('Y-m-d').'\') AND ssr.SCHOOL_ID='.UserSchool().'  AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=up.ID AND up.PROFILE=s.PROFILE AND la.USERNAME IS NOT NULL '));
        foreach($tmp_q as $tmp_a)
        $tmp_arr[]=$tmp_a['USERNAME'];
        
        }
        elseif(UserWs('PROFILE')=='teacher')
        {
            $schools=DBGet(DBQuery('SELECT GROUP_CONCAT(SCHOOL_ID) as SCHOOL_ID FROM staff_school_relationship WHERE STAFF_ID='.$user_id.' AND (START_DATE=\'0000-00-00\' OR START_DATE<=\''.date('Y-m-d').'\') AND (END_DATE=\'0000-00-00\' OR END_DATE IS NULL OR END_DATE>=\''.date('Y-m-d').'\') '));
            $schools=$schools[1]['SCHOOL_ID'];

            $course_periods=DBGet(DBQuery('SELECT GROUP_CONCAT(course_period_id) as COURSE_PERIOD_ID FROM course_periods WHERE TEACHER_ID='.$user_id.' OR SECONDARY_TEACHER_ID='.$user_id));
            $course_periods=$course_periods[1]['COURSE_PERIOD_ID'];


            $tmp_q='';
            $tmp_a=array();
            $tmp_arr=array();
            if($course_periods!='')
            {
                $tmp_q=DBGet(DBQuery('SELECT DISTINCT la.USERNAME,se.STUDENT_ID FROM login_authentication la,student_enrollment se,schedule s WHERE (se.START_DATE=\'0000-00-00\' OR se.START_DATE<=\''.date('Y-m-d').'\') AND (se.END_DATE=\'0000-00-00\' OR se.END_DATE IS NULL OR se.END_DATE>=\''.date('Y-m-d').'\') AND se.STUDENT_ID=s.STUDENT_ID AND s.COURSE_PERIOD_ID IN ('.$course_periods.') AND la.USER_ID=se.STUDENT_ID AND la.PROFILE_ID=3 AND la.USERNAME IS NOT NULL '));
                foreach($tmp_q as $tmp_a)
                {
                    $tmp_arr[]=$tmp_a['USERNAME'];
                    $tmp_qa=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la,students_join_people sjp WHERE sjp.STUDENT_ID='.$tmp_a['STUDENT_ID'].' AND la.USER_ID=sjp.PERSON_ID AND la.PROFILE_ID=4 AND la.USERNAME IS NOT NULL '));
                    foreach($tmp_qa as $tmp_qaa)
                    {
                         $tmp_arr[]=$tmp_qaa['USERNAME'];
                    }

                }

            }

            $tmp_q='';
            $tmp_a=array();
            $tmp_q=DBGet(DBQuery('SELECT la.USERNAME FROM login_authentication la,staff s,staff_school_relationship ssr,user_profiles up WHERE s.PROFILE=\'admin\' AND ssr.STAFF_ID=s.STAFF_ID AND (ssr.START_DATE=\'0000-00-00\' OR ssr.START_DATE<=\''.date('Y-m-d').'\') AND (ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL OR ssr.END_DATE>=\''.date('Y-m-d').'\') AND ssr.SCHOOL_ID IN ('.$schools.')  AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=up.ID AND up.PROFILE=s.PROFILE AND la.USERNAME IS NOT NULL '));
            foreach($tmp_q as $tmp_a)
            $tmp_arr[]=$tmp_a['USERNAME'];

        }
        $tmp_own=DBGet(DBQuery('SELECT DISTINCT la.USERNAME FROM login_authentication la WHERE la.PROFILE_ID = 2 AND la.USER_ID = '.$user_id.' AND la.USERNAME IS NOT NULL'));
        $tmp_arr[]=$tmp_own[1]['USERNAME'];
        
        if(UserWs('PROFILE_ID')!=0)
        {
        foreach($to_array as $data)
        {
            
            if(in_array($data,$tmp_arr))
            $final_arr[]=$data;
            else
            $cannot_send[]=$data;    
        }
        foreach($to_cc_array as $data)
        {
            if(in_array($data,$tmp_arr))
            $final_cc_arr[]=$data;
            else
            $cannot_send[]=$data;
        }
        foreach($to_bcc_array as $data)
        {
            if(in_array($data,$tmp_arr))
            $final_bcc_arr[]=$data;
            else
            $cannot_send[]=$data;
        }
        $_REQUEST['txtToUser']=implode(',',$final_arr);
        $_REQUEST['txtToCCUser']=implode(',',$final_cc_arr);
        $_REQUEST['txtToBCCUser']=implode(',',$final_bcc_arr);
        
        if(count($cannot_send)>0)
        $msg = 'Message not sent to '.  implode(',',$cannot_send).'';
        }
//}
$userName=  UserWs('USERNAME');
$toProfile='';
$toArray=array();
$toArray=  explode(',',$_REQUEST["txtToUser"]);
if(count($toArray)>1)
    $msg = CheckAuthenticMail($userName,$_REQUEST["txtToUser"],$_REQUEST["txtToCCUser"],$_REQUEST["txtToBCCUser"]);
else 
{
  if(count($toArray)==1)
  {
   if($_REQUEST['cp_id']!='')
   { 
    if(UserWs('PROFILE')=='teacher')
     {
        $chkParent=$_POST['list_gpa_parent'];
        $chkStudent=$_POST['list_gpa_student'];
        $course_period_id=$_REQUEST['cp_id'];
        if($chkStudent=='Y')
            $stuList_forCourseArr=  DBGet(DBQuery("SELECT la.username,student_id from students s ,login_authentication la where student_id in(Select distinct student_id from course_periods INNER JOIN schedule using(course_period_id) where course_periods.course_period_id=".$course_period_id.") AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3 AND username IS NOT NULL"));
        //if($chkTeacher=='Y' )
        //    $teacherList_forCourse=DBGet(DBQuery("Select distinct teacher_id,secondary_teacher_id from course_periods INNER JOIN schedule using(course_period_id) where course_periods.course_period_id=".$course_period_id));
        if($chkParent=='Y')
        {
            $parentList_forCourseArr=DBGet(DBQuery("SELECT username FROM login_authentication WHERE username IS NOT NULL AND PROFILE_ID=4 AND USER_ID IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (Select student_id from students where student_id in(Select distinct student_id from course_periods INNER JOIN schedule using(course_period_id) where course_periods.course_period_id=".$course_period_id.")))"));   
        }
        //echo "<br><br>studentlist:<br>";
        //print_r($stuList_forCourseArr);
        //echo "<br><br>parentlist:<br>";
        //print_r($parentList_forCourseArr);exit;
        $stuList_forCourse='';
         foreach ($stuList_forCourseArr as $stu) {
             $stuList_forCourse .= $stu["USERNAME"] . ",";
         }
         $parentList_forCourse='';
         foreach ($parentList_forCourseArr as $parent) {
             $parentList_forCourse .= $parent["USERNAME"] . ",";
         }
         if($chkStudent=='Y' && $chkParent=='Y')
         {
         $finalList=$stuList_forCourse.",".$parentList_forCourse;
         }
         if($chkStudent=='Y' && $chkParent!='Y')
         {
         $finalList=$stuList_forCourse;

         }
          if($chkStudent!='Y' && $chkParent=='Y')
         {
         $finalList=$parentList_forCourse;

         }
         $finalList=rtrim($finalList, ",");
         if($finalList!="")
        $msg = CheckAuthenticMail($userName,$finalList,$_REQUEST["txtToCCUser"],$_REQUEST["txtToBCCUser"]);
        }
   }
   else 
   {
       $to=str_replace("'","\'",trim($_REQUEST["txtToUser"]));
       $q="SELECT mail_group.*, GROUP_CONCAT(gm.user_name) AS members FROM mail_group INNER JOIN mail_groupmembers gm ON(mail_group.group_id = gm.group_id) where mail_group.user_name='$userName' AND group_name ='$to' GROUP BY gm.group_id";
       $group_list=  DBGet(DBQuery($q));
       if(count($group_list)!=0)
       {
       foreach ($group_list as $groupId=>$groupmembers)
       {
          $groupName=$group_list[$groupId]['GROUP_NAME'];
          if($groupName==$_REQUEST["txtToUser"])
          {
          $members=$group_list[$groupId]['MEMBERS'];
          $msg = CheckAuthenticMail($userName,$members,$_REQUEST["txtToCCUser"],$_REQUEST["txtToBCCUser"],$groupName);
          }
       }
       }
       else 
       {
           if(trim($_REQUEST["txtToUser"])!="")
           {
            $msg = CheckAuthenticMail($userName,$_REQUEST["txtToUser"],$_REQUEST["txtToCCUser"],$_REQUEST["txtToBCCUser"]);
            
           }
       }
   }
  }
}
$data = array('msg'=>$msg);
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

function SendMail($to,$userName,$subject,$mailBody,$attachment,$toCC,$toBCCs,$grpName,$key)
 {
    $mailBody=str_replace("'","''",$mailBody);
    $subject=str_replace("'","''",$subject);
    $grpName=  str_replace("'", "\'", $grpName);
    
    $file_data = DBGet(DBQuery('SELECT filepath FROM temp_message_filepath_ws WHERE keyval = \''.$key.'\''));
    $att_link = array();
    if(count($file_data)>0)
    {
        foreach($file_data as $fd)
        {
            $att_link[]=$fd['FILEPATH'];
        }
    }
    $attachment=implode(',',$att_link);
//    echo 'INSERT INTO msg_inbox(to_user,from_user,mail_Subject,mail_body,isdraft,mail_attachment,to_multiple_users,to_cc_multiple,to_cc,to_bcc,to_bcc_multiple,mail_datetime) VALUES(\''.$to.'\',\''.$userName.'\',\''.$subject.'\',\''.$mailBody.'\',\''.$isdraft.'\',\''.$attachment.'\',\''.$to.'\',\''.$toCC.'\',\''.$toCC.'\',\''.$toBCCs.'\',\''.$toBCCs.'\',now())';
     $inbox_query=DBQuery('INSERT INTO msg_inbox(to_user,from_user,mail_Subject,mail_body,isdraft,mail_attachment,to_multiple_users,to_cc_multiple,to_cc,to_bcc,to_bcc_multiple,mail_datetime) VALUES(\''.$to.'\',\''.$userName.'\',\''.$subject.'\',\''.$mailBody.'\',\''.$isdraft.'\',\''.$attachment.'\',\''.$to.'\',\''.$toCC.'\',\''.$toCC.'\',\''.$toBCCs.'\',\''.$toBCCs.'\',now())');  
     $max_mail_id =DBGet(DBQuery('SELECT MAX(MAIL_ID) AS MAIL_ID FROM msg_inbox WHERE FROM_USER = \''.$userName.'\' AND to_user = \''.$to.'\' AND mail_Subject  = \''.$subject.'\'')); 
     
     $mail_id=$max_mail_id[1]['MAIL_ID'];
     if($grpName=='false')
       $outbox_query=DBQuery('INSERT INTO msg_outbox(to_user,from_user,mail_Subject,mail_body,mail_attachment,to_cc,to_bcc,mail_datetime) VALUES(\''.$to.'\',\''.$userName.'\',\''.$subject.'\',\''.$mailBody.'\',\''.$attachment.'\',\''.$toCC.'\',\''.$toBCCs.'\',NOW())'); 
     else
     {
         $q='INSERT INTO msg_outbox(to_user,from_user,mail_Subject,mail_body,mail_attachment,to_cc,to_bcc,mail_datetime,to_grpName) VALUES(\''.$to.'\',\''.$userName.'\',\''.$subject.'\',\''.$mailBody.'\',\''.$attachment.'\',\''.$toCC.'\',\''.$toBCCs.'\',NOW(),\''.$grpName.'\')';
        // echo "<br> ".$q;
         $outbox_query=DBQuery($q) ; 
     }
     if(count($file_data)>0)
        $file_data = DBQuery('DELETE FROM temp_message_filepath_ws WHERE keyval = \''.$key.'\'');
     
     $usernames = explode(',',$to); 
     if($toCC!='')
        $usernames=array_merge($usernames,explode(',',$toCC));
     if($toBCCs!='')
        $usernames=array_merge($usernames,explode(',',$toBCCs));
     $device_data = $parameters = array();
     $parameters['mail_id'] = $mail_id;
//     $parameters['school_id'] = ;
//     $parameters['syear'] = ;
//     $parameters['mp_id'] = ;
     foreach($usernames as $username)
     {
        $sql_usr = 'SELECT USER_ID,PROFILE_ID,(SELECT PROFILE FROM user_profiles WHERE ID = login_authentication.profile_id) AS PROFILE FROM login_authentication WHERE username=\''.$username.'\'';
        $usr_data = DBGet(DBQuery($sql_usr));
        if(count($usr_data)>0)
        {
            $parameters['user_id'] = $usr_data[1]['USER_ID'];
            $parameters['profile'] =  $usr_data[1]['PROFILE'];;
            $parameters['profile_id'] = $usr_data[1]['PROFILE_ID'];
            $i=0;
            foreach ($usr_data as $udata)
            {
                $usql = 'SELECT DEVICE_TYPE,DEVICE_TOKEN FROM device_info WHERE USER_ID =\''.$udata['USER_ID'].'\' AND PROFILE_ID =\''.$udata['PROFILE_ID'].'\'';
                $usr_device_data = DBGet(DBQuery($usql));
                
                if(count($usr_device_data)>0)
                {
                    foreach($usr_device_data as $udd)
                    {
                        $device_data[$i]['device_type']=$udd['DEVICE_TYPE'];
                        $device_data[$i]['device_token']=$udd['DEVICE_TOKEN'];
                        $device_data[$i]['device_id']=$udd['DEVICE_ID'];
                        $i++;
                    }
                }
            }
        }
     }
     if(count($device_data)>0)
     {
         foreach($device_data as $dd)
         {
            send_push_notification($dd['device_type'],$dd['device_token'],'messaging','Hi! You have received a new message.',$parameters);
         }
     }

     return 'Your message has been sent';  
 }
 
function array_push_assoc($array, $key, $value){
$array[$key] = $value;
return $array;
}

function CheckAuthenticMail($userName,$toUsers,$toCCUsers,$toBCCUsers,$grpName='false')
 {
   
    if($toUsers!='')
    $to_array=explode(',',$toUsers);
    if($toCCUsers!='')
    $to_cc_array=explode(',',$toCCUsers);
    if($toBCCUsers!='')
    $to_bcc_array=explode(',',$toBCCUsers);
    
//    echo '$toUsers='.$toUsers.'<br><br>';
//    echo '$toCCUsers='.$toCCUsers.'<br><br>';
//    echo '$toBCCUsers='.$toBCCUsers.'<br><br>';
//    echo '$to_array=';print_r($to_array);echo '<br><br>';
//    echo '$to_cc_array=';print_r($to_cc_array);echo '<br><br>';
//    echo '$to_bcc_array=';print_r($to_bcc_array);echo '<br><br>';
//    
    $toUserstemp=array();
    $toCctemp=array();
    $toBcctemp=array();
   
    foreach($to_array as $ta)
    $toUserstemp[]="'".$ta."'";
    foreach($to_cc_array as $ta)
    $toCctemp[]="'".$ta."'";
    foreach($to_bcc_array as $ta)
    $toBcctemp[]="'".$ta."'";
    
    if(count($toUserstemp)>0)
        $toUserstemp=implode(',',$toUserstemp);
    if(count($toCctemp)>0)
        $toCctemp=implode(',',$toCctemp);
    if(count($toBcctemp)>0)
       $toBcctemp=implode(',',$toBcctemp);
    
    $to_av_user=array();
    $to_uav_user=array();
    
    $to_av_cc=array();
    $to_uav_cc=array();
    
    $to_av_bcc=array();
    $to_uav_bcc=array();
    
    if(count($to_array)>0)
    {
        $check_qa=array();
        
        $check_q=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN ('.$toUserstemp.')'));    
        foreach($check_q as $cq)
        $check_qa[]=$cq['USERNAME'];
        
        foreach($to_array as $to_i=>$un)
        {
            if(in_array($un,$check_qa))
            $to_av_user[]=$un;
            else
            {
            $group_check=DBGet(DBQuery('SELECT DISTINCT mgm.USER_NAME FROM mail_group mg,mail_groupmembers mgm WHERE mg.GROUP_NAME=\''.$un.'\' AND mg.USER_NAME=\''.$userName.'\' AND mg.GROUP_ID=mgm.GROUP_ID'));
                if(count($group_check)>0)
                {
                    foreach($group_check as $gq)
                     $to_av_user[]=$gq['USER_NAME'];
                }
                else
                $to_uav_user[]=$un;
            }
        }
        unset($un);
        unset($check_q);
    }   
    if(count($to_cc_array)>0)
    {
      $check_qa=array();  
      
      $check_q=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN ('.$toCctemp.')'));
      foreach($check_q as $cq)
      $check_qa[]=$cq['USERNAME'];
      
      foreach($to_cc_array as $un)
      {
          if(in_array($un,$check_qa))
          $to_av_cc[]=$un;
          else
          $to_uav_cc[]=$un;
      }
      
      unset($un);
      unset($check_q);
     }
       
    if(count($to_bcc_array)>0)
    {
      $check_qa=array();
      
      $check_q=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USERNAME IN ('.$toBcctemp.')'));    
      foreach($check_q as $cq)
      $check_qa[]=$cq['USERNAME'];
      
      foreach($to_bcc_array as $un)
      {
          if(in_array($un,$check_qa))
          $to_av_bcc[]=$un;
          else
          $to_uav_bcc[]=$un;
      }
      unset($un);
      unset($check_q);
    }
       
    if(count($to_av_user)>0)
    {
    $subject=$_REQUEST['txtSubj'];

    if($subject=='')
        $subject='No Subject';
 
    $mailBody=$_REQUEST['txtBody'];
   
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    $path = explode('/',$_SERVER['SCRIPT_NAME']);
    
    $file_path = $path[1];
    $out=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/";
    
    $file_data = $_REQUEST['attachments'];
    
    if(count($file_data)>0)
    {
        foreach($file_data as $fl_dt)
        {
            if($fl_dt['name']!='')
            {
                $path=$userName.'_'.time().rand(00,99).$fl_dt['name'];
                $fullpath = $out.'assets/'.$path;
                file_put_contents($fullpath, base64_decode($fl_dt['content']));
                $arr[$i]=$folder;
            }
            else
            {
                $attachment="";
            }
        }
    }
    
//    $uploaded_file_count=count($data);
//    $uploaded_file_count=count($_FILES['f']['name']);
    //$images=implode(",",$_FILES['f']['name']);
//    for($i=0;$i<$uploaded_file_count;$i++)
//    {
//        $name=$_FILES['f']['name'][$i];
//        if($name)
//        {
//        $path=$userName.'_'.time().rand(00,99).$name;
//        $folder="./assets/".$path;
//        $temp=$_FILES['f']['tmp_name'][$i];
//        move_uploaded_file($temp,$folder);
//        $arr[$i]=$folder;
//        }
//        else
//            $attachment="";
//    }
    
    $attachment=implode(',',$arr); 
     
    $multipleUser=  implode(",", $to_av_user);
    
    if(count($to_av_cc)>0)
    $multipleCCUser=  implode(",", $to_av_cc);
    else
    $multipleCCUser= '';
    
    if(count($to_av_bcc)>0)
    $multipleBCCUser=  implode(",", $to_av_bcc);
    else
    $multipleBCCUser= '';
    $key = $_REQUEST['key'];
//    $mailBody = htmlspecialchars($mailBody) ;
//    echo "SendMail(user->$multipleUser, $userName, $subject, $mailBody, $attachment,cc->$multipleCCUser,bcc->$multipleBCCUser,$grpName,$key);";
    $msg = SendMail($multipleUser, $userName, $subject, $mailBody, $attachment,$multipleCCUser,$multipleBCCUser,$grpName,$key);
    
    if(count($to_uav_user)>0)
        $msg = 'Message not sent to '.  implode(',',$to_uav_user).' as they don\'t exist.';
    if(count($to_uav_cc)>0)
        $msg = 'Message not sent to '.  implode(',',$to_uav_cc).' as they don\'t exist.';
    if(count($to_uav_bcc)>0)
        $msg = 'Message not sent to '.  implode(',',$to_uav_bcc).' as they don\'t exist.';
    
    }
    else
    {
        if(count($to_uav_user)>0)
        $msg = 'Message not sent as '.  implode(',',$to_uav_user).' doesn\'t exist.';
        elseif($toUsers=='')
        $msg = 'Message not sent.';
    }
//    if(count($cannot_send)>0)
//    echo '<font style="color:red"><b>Message not sent to '.  implode(',',$cannot_send).'</b></font><br><br>';
    
    return $msg;
 }
 // FUNCTION to check if there is an error response from Apple
// Returns TRUE if there was and FALSE if there was not
function checkAppleErrorResponse($fp) {

//byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). 
// Should return nothing if OK.

//NOTE: Make sure you set stream_set_blocking($fp, 0) or else fread will pause your script and wait 
// forever when there is no response to be sent.

$apple_error_response = fread($fp, 6);

if ($apple_error_response) {

// unpack the error response (first byte 'command" should always be 8)
$error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response); 

if ($error_response['status_code'] == '0') {
$error_response['status_code'] = '0-No errors encountered';

} else if ($error_response['status_code'] == '1') {
$error_response['status_code'] = '1-Processing error';

} else if ($error_response['status_code'] == '2') {
$error_response['status_code'] = '2-Missing device token';

} else if ($error_response['status_code'] == '3') {
$error_response['status_code'] = '3-Missing topic';

} else if ($error_response['status_code'] == '4') {
$error_response['status_code'] = '4-Missing payload';

} else if ($error_response['status_code'] == '5') {
$error_response['status_code'] = '5-Invalid token size';

} else if ($error_response['status_code'] == '6') {
$error_response['status_code'] = '6-Invalid topic size';

} else if ($error_response['status_code'] == '7') {
$error_response['status_code'] = '7-Invalid payload size';

} else if ($error_response['status_code'] == '8') {
$error_response['status_code'] = '8-Invalid token';

} else if ($error_response['status_code'] == '255') {
$error_response['status_code'] = '255-None (unknown)';

} else {
$error_response['status_code'] = $error_response['status_code'].'-Not listed';

}

echo '<br><b>+ + + + + + ERROR</b> Response Command:<b>' . $error_response['command'] . '</b>&nbsp;&nbsp;&nbsp;Identifier:<b>' . $error_response['identifier'] . '</b>&nbsp;&nbsp;&nbsp;Status:<b>' . $error_response['status_code'] . '</b><br>';

echo 'Identifier is the rowID (index) in the database that caused the problem, and Apple will disconnect you from server. To continue sending Push Notifications, just start at the next rowID after this Identifier.<br>';

return true;
}
else 
{
    echo 'no error msg from apple';
}
       
return false;
}
?>
