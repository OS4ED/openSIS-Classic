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
$userName=  UserWs('USERNAME');
$action_type = $_REQUEST['action_type'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
if($action_type == 'view')
{
    $grp_data = array();
    $select="SELECT mg.*,(SELECT COUNT(*) FROM mail_groupmembers WHERE GROUP_ID = mg.GROUP_ID) AS MEMBER_COUNT  from mail_group mg  WHERE USER_NAME ='$userName'";
    $list = DBGet(DBQuery($select));
    
    foreach($list as $lst)
    {
        $grp_data[]=$lst;
    }
    
    if(count($grp_data)>0)
    {
        $success = 1;
        $msg = 'nil';
    }
    else 
    {
        $success = 0;
        $msg = 'No Data Found';
    }
    $data = array('group_info'=>$grp_data,'success'=>$success,'msg'=>$msg);
}
elseif($action_type == 'add_grp_submit')
{
     $grp_id=''; 
     $flag = 0;
     $exist_group=DBGet(DBQuery("SELECT * FROM mail_group WHERE USER_NAME='$userName'"));
    foreach($exist_group as $id=>$value)
    {
        if(strtolower($exist_group[$id]['GROUP_NAME'])==strtolower($_REQUEST['txtGrpName']))
            
        {
            $flag = 1;
            break;
        }
    }
    if($_REQUEST['txtGrpName'])
    {
        if($flag == 0)
        {
            $description=$_REQUEST['txtGrpDesc'];
            if($description=="")
                $description='N';

            $group='INSERT INTO mail_group(GROUP_NAME,DESCRIPTION,USER_NAME,CREATION_DATE) VALUES(\''. str_replace("'", "\\'",$_REQUEST['txtGrpName']).'\',\''.str_replace("'", "\\'",$description).'\',\''.$userName.'\',now())';  
            $group_info=DBQuery($group);
            $grp_id =mysqli_insert_id($connection);
            if($grp_id!='') 
            {
                $success = 1;
                $msg = "nil";
            }
            else 
            {
                $success = 0;
                $msg = "Group insertion failed";
            }
            }
        else 
        {

                $success = 0;
                $msg = "groupname already exist for $userName";
        }
    }
    else 
    {
        $success = 0;
        $msg = "Group name cannot be left blank";
    }
    $data = array('group_id'=>$grp_id,'success'=>$success,'msg'=>$msg);
}
elseif($action_type == 'selected_grp_view')
{
    $group="select GROUP_NAME,DESCRIPTION from mail_group where GROUP_ID=".$_REQUEST['grp_id'];
    $groupDetails=DBGet(DBQuery($group));
    $groupname=$groupDetails[1]['GROUP_NAME'];
    $groupdesc=($groupDetails[1]['DESCRIPTION']=='N'?'':$groupDetails[1]['DESCRIPTION']);

    $member="select * from mail_groupmembers where GROUP_ID='".$_REQUEST['grp_id']."'";
    $member_list=DBGet(DBQuery($member));
    $group_members = array();
    foreach($member_list as $key=>$value)
    {
//        $member_list[$key]['PROFILE'];
        $select="SELECT * FROM user_profiles WHERE ID='".$member_list[$key]['PROFILE']."'";
        $profile=DBGet(DBQuery($select));
        $value['PROFILE']=$profile[1]['PROFILE'];
        switch($member_list[$key]['PROFILE'])
        {
            case 0:
            case 1:
            case 2:
            case 5:
                $select="SELECT * FROM staff WHERE STAFF_ID=(SELECT USER_ID FROM login_authentication WHERE USERNAME ='".$member_list[$key]['USER_NAME']."' AND PROFILE_ID='".$member_list[$key]['PROFILE']."')";
                $usrs=DBGet(DBQuery($select));
                $value['NAME']=$usrs[1]['LAST_NAME'].', '.$usrs[1]['FIRST_NAME'];
            break;
            
            case 3: 
                $select="SELECT * FROM students WHERE STUDENT_ID=(SELECT USER_ID FROM login_authentication WHERE USERNAME ='".$member_list[$key]['USER_NAME']."' AND PROFILE_ID='".$member_list[$key]['PROFILE']."')";
                $usrs=DBGet(DBQuery($select));
                $value['NAME']=$usrs[1]['LAST_NAME'].', '.$usrs[1]['FIRST_NAME'];
            break;
        
            case 4:
                $select="SELECT * FROM people WHERE STAFF_ID=(SELECT USER_ID FROM login_authentication WHERE USERNAME ='".$member_list[$key]['USER_NAME']."' AND PROFILE_ID='".$member_list[$key]['PROFILE']."')";
                $usrs=DBGet(DBQuery($select));
                $value['NAME']=$usrs[1]['LAST_NAME'].', '.$usrs[1]['FIRST_NAME'];
            break;    
        
        }
        
        $group_members[]=$value;
    }
    $data = array('GROUP_NAME'=>$groupname,'DESCRIPTION'=>$groupdesc,'MEMBERS'=>$group_members, 'MEMBER_COUNT'=>count($group_members));
}
elseif($action_type == 'delete_group')
{
    $group_id= $_REQUEST['grp_id'];
    $members=  DBGet (DBQuery ("select count(*) as countmember from mail_groupmembers where group_id=".$group_id.""));
    $count_members=$members[1]['COUNTMEMBER'];
    if($count_members>0)
    {
        $member_del="delete from mail_groupmembers where group_id=".$group_id."";
        $member_del_execute=  DBQuery($member_del);
    }
    $mail_delete="delete from mail_group where group_id =".$group_id."";
    $mail_delete_ex=DBQuery($mail_delete);
    if($mail_delete_ex)
    {
        $success = 1;
        $msg = 'nil';
    }
    else 
    {
        $success = 0;
        $msg = 'Group delete failed';
    }
    $data = array('success'=>$success,'msg'=>$msg);
}
elseif($action_type == 'edit_grp')
{
    if(isset($_REQUEST['txtGrpName']))
    {
        $gid=$_REQUEST['grp_id'];
        $flag = 0;
        $exist_group=DBGet(DBQuery("SELECT * FROM mail_group WHERE USER_NAME='$userName' and group_id!='$gid'"));
        foreach($exist_group as $id=>$value)
        {
            if($exist_group[$id]['GROUP_NAME']==$_REQUEST['txtGrpName'])
            {
                $flag = 1;
                break;
            }
        }
       if($flag == 0) 
       {
            $update="UPDATE mail_group SET GROUP_NAME='".str_replace("'", "\\'",$_REQUEST[txtGrpName])."' WHERE GROUP_ID=$gid";
                    $update_group=DBQuery($update);
            if(isset($_REQUEST['txtGrpDesc']))
            {
                if(trim($_REQUEST['txtGrpDesc'])!="")
                   $update="UPDATE mail_group SET DESCRIPTION='".str_replace("'", "\\'",$_REQUEST[txtGrpDesc])."' WHERE GROUP_ID=$gid";
                else
                    $update="UPDATE mail_group SET DESCRIPTION='N' WHERE GROUP_ID=$gid";
                        $update_group=DBQuery($update);
            }
            $success =1;
            $msg = 'nil';
       }
       else 
       {
            $success =0;
            $msg = 'groupname already exists for '.$userName;
       }
    }
    else 
    {
        $success = 0;
        $msg = 'Groupname cannot be left blank';
    }
//    if(isset($_REQUEST['group']))
//    {
//        if(implode(',',$_REQUEST['group'])=='')
//        {
//            $select="select * from mail_groupmembers where group_id=$_REQUEST[groupid]";
//             $list=DBGet(DBQuery($select));
//              foreach($list as $m=>$n)
//             {
//                  if($list[$m]['ID'])
//                     $del_id[]=$list[$m]['ID'];
//             }
//
//            $id=implode(',',$del_id);
//            $select="DELETE FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID IN($id)";
//            $not_in_group=DBGet(DBQuery($select));
//
//            echo "<script>window.location='Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."'</script>";
//
//        }     
//        else
//        {
//            $not_select="select * from mail_groupmembers where GROUP_ID=$_REQUEST[groupid]";
//             $list1=DBGet(DBQuery($not_select));
//             foreach($list1 as $i=>$j)
//             {
//                 $id_list[]=$j['ID'];
//             }
//             $id3=implode(',',$id_list);
//            $id1=array_keys($_REQUEST['group']);
//            $id2= implode(',',$id1);
//            if($id2==$id3)
//                echo "<script>window.location='Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."'</script>";
//             else   
//             {
//            $select="SELECT * FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID NOT IN($id2)";
//                 $list=DBGet(DBQuery($select));
//            foreach($list as $i=>$j)
//            {
//                $del_id1[]=$list[$i]['ID'];
//            } 
//             $id=implode(',',$del_id1);
//             $select="DELETE FROM mail_groupmembers WHERE GROUP_ID=$_REQUEST[groupid] AND ID IN($id)";
//             $not_in_group=DBGet(DBQuery($select));
//             echo "<script>window.location='Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."'</script>";
//
//             }
//        }
//
//    }
    $data = array('success'=>$success,'msg'=>$msg);
}
elseif($action_type == 'add_grp_member_view')
{
    $groupname=$_REQUEST['grp_id'];
    $lastName=$_REQUEST['last'];
    $firstName=$_REQUEST['first'];
    $userName=$_REQUEST['username'];
    $profile=$_REQUEST['profile'];
    $disable=($_REQUEST['_dis_user']=='N')?'':$_REQUEST['_dis_user'];
    $allschools=$_REQUEST['_search_all_schools'];
    if(isset($_REQUEST['grp_id']))  
    {
        $select1="select * from mail_group where GROUP_ID='".$_REQUEST['grp_id']."'";
        $groupselect=DBGet(DBQuery($select1));
            
        $member="select * from mail_groupmembers where GROUP_ID=".$groupselect[1]['GROUP_ID']."";
        $existuser=DBGet(DBQuery($member));
         
        foreach($existuser as $id=>$value)
        {
            $usernames[]=array('PROFILE_ID'=>$existuser[$id]['PROFILE'],'USERNAME'=>$existuser[$id]['USER_NAME']);                           
        }
          
        foreach($usernames as $id=>$value)
        {
            if($value['PROFILE_ID']!=3 ||$value['PROFILE_ID']!=4)
            {
                 $staff="select * from login_authentication,staff where login_authentication.user_id=staff.staff_id and USERNAME='$value[USERNAME]' and login_authentication.profile_id not in(3)";
                 $stafflist=DBGet(DBQuery($staff));
                 $staff_id[]=$stafflist[1]['STAFF_ID'];
            }
            if($value['PROFILE_ID']==3)
            {                               
                 $stu="select * from login_authentication,students where login_authentication.user_id=students.student_id and profile_id=3 and USERNAME='$value[USERNAME]'";                              
                 $stulist=DBGet(DBQuery($stu));
                 $stu_id[]=$stulist[1]['STUDENT_ID'];    
            }
        }  
        $staff_id=  array_filter($staff_id);
        $stu_id= array_filter($stu_id);
        
        if($profile!='')//search by profile
        {
            $prof="select PROFILE from user_profiles where ID='".$profile."'";
            $prof_type=DBGet(DBQuery($prof));
            
            if($prof_type[1]['PROFILE']=='student')//students
            {
                if(UserWs('PROFILE')=='teacher')
                    $user="SELECT * FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING ( course_period_id ) WHERE course_periods.teacher_id = ".$teacher_id.")";
                elseif(UserWs('PROFILE')=='parent')
                {
                    $parent_id=$teacher_id;
                    $qr= DBGet(DBQuery('Select STUDENT_ID from students_join_people where person_id=\''.$parent_id.'\''));
                    $student_id=$qr[1]['STUDENT_ID'];
                    $user="SELECT * FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id  and students.student_id in (Select STUDENT_ID from students_join_people where person_id=".$parent_id.") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].")";
                }
                elseif(UserWs('PROFILE_ID')==1 || UserWs('PROFILE_ID')==5)
                {
                    $user= "select * from students,login_authentication,student_enrollment WHERE profile_id=3 and login_authentication.user_id=students.student_id and students.student_id=student_enrollment.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where staff_id=".  $teacher_id.") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].")";   
                }
                else  
                {
                    $user="select * from students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].")";                
                }
            }
            if($prof_type[1]['PROFILE']=='teacher')//teachers
            {
                if(UserWs('PROFILE')=='parent')
                {
                    $parent_id=$teacher_id;
                    $qr= DBGet(DBQuery('Select STUDENT_ID from students_join_people where person_id=\''.$parent_id.'\''));
                    $student_id=$qr[1]['STUDENT_ID'];
                    $user="SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND staff_id  IN (SELECT distinct(course_periods.teacher_id) FROM course_periods,schedule where schedule.course_period_id=course_periods.course_period_id and schedule.student_id in (Select STUDENT_ID from students_join_people where person_id=".$parent_id."))";
                }
                else if(UserWs('PROFILE')=='student')
                {
                    $studentId=$teacher_id;
                    $user="SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND staff_id IN(Select distinct teacher_id from course_periods INNER JOIN schedule using(course_period_id) where schedule.student_id=".$studentId.")";
                }
                else if(UserWs('PROFILE_ID')==1 || UserWs('PROFILE_ID')==5)
                {
                    $user="SELECT * FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id  and staff_school_relationship.staff_id=staff.staff_id  and staff_school_relationship.school_id in (select school_id from staff_school_relationship where staff_id=".  $teacher_id.")  and login_authentication.profile_id=2 and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID']." )";
                }
                else
                {
                    $user="SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=$profile and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID']." )";
                }
            }
            if($prof_type[1]['PROFILE']=='parent')//parents
            {
              if (UserWs('PROFILE')=='teacher')
              {
                $teacher_id= $teacher_id;
                $user='SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id='.$profile.' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id='.$groupselect[1]['GROUP_ID'].' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (SELECT student_id FROM students WHERE student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING (course_period_id ) WHERE course_periods.teacher_id = \''.$teacher_id.'\')))';
              }
              else if(UserWs('PROFILE')=='admin')
              {
                  $user='SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id='.$profile.' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id='.$groupselect[1]['GROUP_ID'].' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' ';        
              }
             else if(UserWs('PROFILE')=='student')
              {$student_id=  $teacher_id;
                  $user='SELECT * FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4 and people.profile_id='.$profile.' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id='.$groupselect[1]['GROUP_ID'].' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id='.$student_id.' )';        
              }
            }
            if($profile==0 ||$profile==1 ||$profile==5)//all types of admin
            {
                $user="SELECT * FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=$profile and staff.PROFILE_ID=$profile AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].")";
            }
            if($lastName!="")
            {
                $user=$user." AND LAST_NAME LIKE '$lastName%' ";
            }
            if($firstName!="")
            {
                $user=$user." AND FIRST_NAME LIKE '$firstName%' ";
            }
            if($userName!="")
            {
                $user=$user." AND USERNAME LIKE '$userName%' ";
            }
            if($disable=='' && ($profile==3 || $profile==4))//only enabled students 
            {
                $user=$user." AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
            }
            if($disable=='' && $profile!=3 && $profile!=4)//only enabled users
            {
                $user=$user." AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
            }
            if($disable=='Y')//with disabled users
            {
                $user=$user." ";
            }
           
          }
          
          else 
          {    
               if(UserWs('PROFILE')=='admin'  && UserWs('PROFILE_ID')==0)//all types of admin
               {
                    $user1="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id  AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id not in(3,4)";
                    $user2="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students WHERE login_authentication.user_id=students.student_id AND login_authentication.profile_id=3 AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id=3";
                    $user3="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id=4";
               }
                if(UserWs('PROFILE_ID')==1 || UserWs('PROFILE_ID')==5)//all types of admin
               {
                      $user1="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id and staff_school_relationship.staff_id=staff.staff_id  and staff_school_relationship.school_id in (select school_id from staff_school_relationship where staff_id=".  $teacher_id.") AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id not in(3,4)";
                    $user2="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students,student_enrollment WHERE login_authentication.user_id=students.student_id and students.student_id=student_enrollment.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where staff_id=".  $teacher_id.")AND login_authentication.profile_id=3 AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id=3";
                      $user3="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,students_join_people,people  WHERE login_authentication.user_id=people.staff_id AND students_join_people.person_id in(select school_id from students,student_enrollment,students_join_people  where students.student_id=student_enrollment.student_id and students_join_people.student_id=students.student_id and student_enrollment.school_id in(select school_id from staff_school_relationship where  staff_id=".  $teacher_id.")) and TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id=4";
//              $user3="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id AND people.staff_id in (select staff_id from staff_school_relationship where staff_id=".  UserID().") and TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id=4";
                    }
               if(UserWs('PROFILE')=='teacher')//teachers
               {
                   $user1="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff,staff_school_relationship WHERE login_authentication.user_id=staff.staff_id and staff_school_relationship.staff_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id in(0,1,5) and school_id in(select school_id from staff_school_relationship where staff_id=".$teacher_id.")";//all types of admin
                   $user2="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING ( course_period_id ) WHERE course_periods.teacher_id = ".$teacher_id.")";//scheduled students
                   $user3='SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id='.$groupselect[1]['GROUP_ID'].' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id IN (SELECT student_id FROM students WHERE student_id IN (SELECT DISTINCT student_id FROM course_periods INNER JOIN schedule USING (course_period_id ) WHERE course_periods.teacher_id = \''.$teacher_id.'\')))';//parents                  
               }
               if(UserWs('PROFILE')=='parent')//parents
               {
                    $user1="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id in(0,1,5)";//all types of admin
                    $parent_id=$teacher_id;
//                     
                       $user2="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2  AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND staff_id  IN (SELECT distinct(course_periods.teacher_id) FROM course_periods,schedule where schedule.course_period_id=course_periods.course_period_id and schedule.student_id in (Select STUDENT_ID from students_join_people where person_id=".$parent_id."))";
//              $user3="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id"
                $user3="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM students,login_authentication WHERE profile_id=3 and login_authentication.user_id=students.student_id  and students.student_id in (Select STUDENT_ID from students_join_people where person_id=".$parent_id.") and  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> ''  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].")";   
               }
               if(UserWs('PROFILE')=='student')//students
               {
                   $user1="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id AND TRIM( IFNULL( USERNAME, '' ) ) <> '' AND TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") and login_authentication.profile_id in(0,1,5)";//all types of admin
                   $user2="SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,staff WHERE login_authentication.user_id=staff.staff_id and login_authentication.profile_id=2  AND  TRIM( IFNULL( USERNAME, '' ) ) <> '' AND  TRIM( IFNULL( PASSWORD, '' ) ) <> '' AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id=".$groupselect[1]['GROUP_ID'].") AND staff_id IN(Select distinct teacher_id from course_periods INNER JOIN schedule using(course_period_id) where schedule.student_id=".UserStudentIDWs().")";//teachers                 
                   $student_id=  UserStudentIDWs();
                   $user3='SELECT username,LAST_NAME,first_name,login_authentication.profile_id,is_disable,login_authentication.user_id FROM login_authentication,people WHERE login_authentication.user_id=people.staff_id and login_authentication.profile_id=4  AND USERNAME NOT IN(SELECT USER_NAME from mail_groupmembers where group_id='.$groupselect[1]['GROUP_ID'].' ) and  TRIM( IFNULL( USERNAME, \'\' ) ) <> \'\' AND user_id IN (SELECT DISTINCT person_id FROM students_join_people WHERE student_id='.$student_id.' )'; 
               }
              if($lastName!="")
              {
                  $user1=$user1." AND LAST_NAME LIKE '$lastName%' ";
                  $user2=$user2." AND LAST_NAME LIKE '$lastName%' ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                    $user3=$user3." AND LAST_NAME LIKE '$lastName%' ";
              }
              if($firstName!="")
              {
                  $user1=$user1." AND FIRST_NAME LIKE '$firstName%' ";
                  $user2=$user2." AND FIRST_NAME LIKE '$firstName%' ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                    $user3=$user3." AND FIRST_NAME LIKE '$firstName%' ";
              }
              if($userName!="")
              {
                  $user1=$user1." AND USERNAME LIKE '$userName%' ";
                  $user2=$user2." AND USERNAME LIKE '$userName%' ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                    $user3=$user3." AND USERNAME LIKE '$userName%' ";
              }

              if($disable=='' && ($profile==3 || $profile==4))//only enabled students 
              {
                  $user1=$user1." AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
                  $user2=$user2." AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                      $user3=$user3." AND TRIM( IFNULL( is_disable, 'NULL' ) ) = 'NULL' ";
              }
              if($disable=='' && $profile!=3 && $profile!=4)//only enabled users
              {
                  $user1=$user1." AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
                  $user2=$user2." AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                      $user3=$user3." AND TRIM( IFNULL( is_disable, '' ) ) <> 'Y' ";
              }
              if($disable=='Y')//with disabled users
              {
                  $user1=$user1." ";
                  $user2=$user2." ";
                  if(UserWs('PROFILE')=='admin'||UserWs('PROFILE')=='teacher')
                      $user2=$user2." ";
              }
              if(UserWs('PROFILE')=='admin'|| UserWs('PROFILE')=='teacher' || UserWs('PROFILE')=='parent' || UserWs('PROFILE')=='student')
                 $user=$user1." UNION ALL ".$user2." UNION ALL ".$user3;
             else 
                 $user=$user1." UNION ALL ".$user2;
          }
          
              $userlist=DBGet(DBQueryMod($user)); 
               
              if($_REQUEST['_search_all_schools']!='Y')
              {
                  $final_arr = array();
                    foreach($userlist as $key=>$value)
                {
                    if($userlist[$key]['PROFILE_ID']==3){
                     $select="SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=".$userlist[$key]['PROFILE_ID']." and se.school_id=".UserSchool()." AND se.student_id='".$userlist[$key]['USER_ID']."'";
                    $profile=DBGet(DBQuery($select));
                    foreach($profile as $k=>$v){
                    $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                    $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                    $_arr['USER_ID'] = $profile[$k]['STUDENT_ID'];
                    $_arr['FIRST_NAME']= $userlist[$key]['LAST_NAME'].' '.$userlist[$key]['FIRST_NAME'];
                    $_arr['PROFILE_ID']= $profile[$k]['PROFILE'];
                    $_arr['IS_DISABLE']= $userlist[$key]['IS_DISABLE'];
                    array_push($final_arr,$_arr);
                    
                    }
                    }
                    
                    else if($userlist[$key]['PROFILE_ID']==4){
                     
//                        $sql = "select student_id from  students_join_people where person_id=".$userlist[$key]['USER_ID'];
//                        $fetch = DBGet(DBQuery($sql));
//                        foreach($fetch as $k1=>$v1){
                       if(UserWs('PROFILE')=='student')
                        $select="SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=".$userlist[$key]['PROFILE_ID']." and se.school_id=".UserSchool()." AND se.student_id=".  UserStudentIDWs()."";
                       if(UserWs('PROFILE')=='teacher')
                            $select="SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=".$userlist[$key]['PROFILE_ID']." and se.school_id=".UserSchool()." AND se.student_id in (select schedule.student_id from  schedule,course_periods,students_join_people where course_periods.course_period_id=schedule.course_period_id  and  schedule.student_id=students_join_people.student_id and students_join_people.person_id=".$userlist[$key]['USER_ID']." and teacher_id=".$teacher_id.")";
                       else
                         $select="SELECT se.*,up.* FROM student_enrollment se,user_profiles up WHERE up.ID=".$userlist[$key]['PROFILE_ID']." and se.school_id=".UserSchool()." AND se.student_id in (select student_id from  students_join_people where person_id=".$userlist[$key]['USER_ID'].")";
                    $profile=DBGet(DBQuery($select));
                    
                    
                    foreach($profile as $k=>$v){
                        $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                        $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                        $_arr['USER_ID'] = $userlist[$key]['USER_ID'];
                        $_arr['FIRST_NAME']= $userlist[$key]['LAST_NAME'].' '.$userlist[$key]['FIRST_NAME'];
                        $_arr['PROFILE_ID']= $profile[$k]['PROFILE'];
                        $_arr['IS_DISABLE']= $userlist[$key]['IS_DISABLE'];
                        array_push($final_arr,$_arr);
                  
                    }
                }
                else{ 
                    $select="SELECT se.*,up.* FROM staff_school_relationship se,user_profiles up WHERE up.ID=".$userlist[$key]['PROFILE_ID']." and se.school_id=".UserSchool()." AND se.staff_id='".$userlist[$key]['USER_ID']."'";
                    $profile=DBGet(DBQuery($select));
                    foreach($profile as $k=>$v){
                    $_arr['USERNAME'] = $userlist[$key]['USERNAME'];
                    $_arr['LAST_NAME'] = $userlist[$key]['LAST_NAME'];
                    $_arr['USER_ID'] = $profile[$k]['STAFF_ID'];
                    $_arr['FIRST_NAME']= $userlist[$key]['LAST_NAME'].' '.$userlist[$key]['FIRST_NAME'];
                    $_arr['PROFILE_ID']= $profile[$k]['PROFILE'];
                    $_arr['IS_DISABLE']= $userlist[$key]['IS_DISABLE'];
                    array_push($final_arr,$_arr);
                    }
                    }
                }
                   
                array_unshift($final_arr,"");
                    unset($final_arr[0]);
                    $userlist = $final_arr;
                
              }
              else{
            
              foreach($userlist as $key=>$value)
              {
                    $select="SELECT * FROM user_profiles WHERE ID='".$userlist[$key]['PROFILE_ID']."'";
                    $profile=DBGet(DBQuery($select));
                    $userlist[$key]['FIRST_NAME']=$userlist[$key]['LAST_NAME'].' '.$userlist[$key]['FIRST_NAME'];
                    $userlist[$key]['PROFILE_ID']=$profile[1]['PROFILE'];
              }
              }
              
             
//              exit;
              
//              if($_REQUEST['_dis_user']=='Y')
//              $columns=array('FIRST_NAME'=>'Member','USERNAME'=>'User Name','PROFILE_ID'=>'Profile','STATUS'=>'Status');
//              else
//              $columns=array('FIRST_NAME'=>'Member','USERNAME'=>'User Name','PROFILE_ID'=>'Profile');
//              $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
//              $extra['LO_group'] = array('STAFF_ID');
//              $extra['columns_before']= array('CHECKBOX'=>'</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'groups\');"><A>');
//              $extra['new'] = true;
//              if(is_array($extra['columns_before']))
//              {
//                    $LO_columns = $extra['columns_before'] + $columns;
//                    $columns = $LO_columns;
//              }
//              foreach($userlist as $id=>$value)
//              {
//                    $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=groups[".$value['USER_ID'].",".$value['PROFILE_ID']."] value=Y>";
//                    $userlist[$id]=$extra['columns_before']+$value;
//              }
              if($_REQUEST['_dis_user']=='Y')
              {
                  foreach($userlist as $ui=>$ud)
                  {
                      
                      if($ud['PROFILE_ID']=='student')      
                      $chck_status=DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM students s,student_enrollment se WHERE se.STUDENT_ID=s.STUDENT_ID AND s.STUDENT_ID='.$ud['USER_ID'].' AND se.SYEAR='.UserSyear().' AND (s.IS_DISABLE=\'Y\' OR (se.END_DATE<\''.date('Y-m-d').'\'  AND se.END_DATE IS NOT NULL AND se.END_DATE<>\'0000-00-00\' ))'));
                      elseif($ud['PROFILE_ID']=='parent')
                      $chck_status=DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM people WHERE STAFF_ID='.$ud['USER_ID'].' AND IS_DISABLE=\'Y\' '));   
                      else
                      $chck_status=DBGet(DBQuery('SELECT COUNT(1) as DISABLED FROM staff s,staff_school_relationship se WHERE se.STAFF_ID=s.STAFF_ID AND s.STAFF_ID='.$ud['USER_ID'].' AND se.SYEAR='.UserSyear().' AND (s.IS_DISABLE=\'Y\' OR (se.END_DATE<\''.date('Y-m-d').'\'  AND se.END_DATE IS NOT NULL AND se.END_DATE<>\'0000-00-00\' ))'));
                      
                      
                        if($chck_status[1]['DISABLED']!=0)
                        $userlist[$ui]['STATUS']="Inactive";   
                        else
                        $userlist[$ui]['STATUS']="Active";
                  }
              }
      }
      $member_list = array();
      foreach($userlist as $val)
      {
          $member_list[]=$val;
      }
      if(count($userlist)>0)
      {
          $success = 1;
          $msg = 'nil';
      }
      else 
      {
          $success = 0;
          $msg = 'No data found';
      }
      $data = array('member_list'=>$member_list,'group_id'=>$_REQUEST['grp_id'],'success'=>$success,'msg'=>$msg);
}
elseif($action_type == 'member_insert')
{
    $members=$_REQUEST["members"];
    $json_result=json_decode($members,TRUE);
     if(count($json_result)>0)
     {
           
        $select="select * from mail_group where group_id='".$_REQUEST['grp_id']."'";
        $grp_select=DBGet(DBQuery($select));
        foreach($json_result as $j)
        {
            
            $member_select=  DBGet(DBQuery("Select * from login_authentication,user_profiles where login_authentication.profile_id=user_profiles.id and user_profiles.profile='".$j['profile']."' and login_authentication.user_id='".$j['id']."'  "));
           
            $grp_members='INSERT INTO mail_groupmembers(GROUP_ID,USER_NAME,profile) VALUES(\''.$grp_select[1]['GROUP_ID'].'\',\''.$member_select[1]['USERNAME'].'\',\''.$member_select[1]['PROFILE_ID'].'\')';
                    $members=DBQuery($grp_members);
        }
        
        $success = 1;
        $msg = "nil";
        
    } 
    else
    {
        $success = 0;
        $msg = "Please select atleast one member to add";
    }
$data = array('success'=>$success,'msg' => $msg);        
}
elseif($action_type == 'edit_grp_members')
{
            $members=  urldecode($_REQUEST["members"]);
    
    if(isset($_REQUEST['members']))
    {
        if($members=='')
        {
//            $select="select * from mail_groupmembers where group_id=".$_REQUEST['grp_id'];
//             $list=DBGet(DBQuery($select));
//              foreach($list as $m=>$n)
//             {
//                  if($list[$m]['ID'])
//                     $del_id[]=$list[$m]['ID'];
//             }
//
//            $id=implode(',',$del_id);
//            $select="DELETE FROM mail_groupmembers WHERE GROUP_ID=".$_REQUEST['grp_id']." AND ID IN($id)";
//            $not_in_group=DBGet(DBQuery($select));

            $success = 0;
            $msg = 'Please select at least 1 member to delete';

        }     
        else
        {
//            $not_select="select * from mail_groupmembers where GROUP_ID=".$_REQUEST['grp_id'];
//             $list1=DBGet(DBQuery($not_select));
//             foreach($list1 as $i=>$j)
//             {
//                 $id_list[]=$j['ID'];
//             }
//             $id3=implode(',',$id_list);
//             $id2= $members;
//             $diff_result = array_diff($id_list,explode(',',$members));
             
//             if(count($diff_result)>0)
//             {
//                 $id=implode(',',$diff_result);
                 $select="DELETE FROM mail_groupmembers WHERE GROUP_ID=".$_REQUEST['grp_id']." AND ID IN($members)"; //$id
                         $not_in_group=DBQuery($select);
                 $success = 1;
                 $msg = 'nil';
//             }
//             else 
//             {
//                 $success = 0;
//                 $msg = 'No change in group members';
//             }
             }
        }
    $data = array('success'=>$success,'msg'=>$msg);
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
echo json_encode($data);
?>
