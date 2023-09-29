<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$parent_id = $_REQUEST['parent_id'];
$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$profile_id =  $_REQUEST['profile_id'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $view = $_REQUEST['view'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];

        $usrnm_sql = 'SELECT USERNAME FROM  login_authentication WHERE PROFILE_ID = '.$profile_id.' AND USER_ID = '.$parent_id;
        $userName_data =  DBGet(DBQuery($usrnm_sql));
        $userName = $userName_data[1]['USERNAME'];
        $toProfile='';
        if($view=='inbox')
        {
            $link=array();
            $id=array();
            $arr=array();
            $qr="select to_user,mail_id,to_cc,to_bcc from msg_inbox";
            $fetch=DBGet(DBQuery($qr));
            foreach($fetch as $key =>$value)
            {
                 $s=$value['TO_USER'];
                 $cc=$value['TO_CC'];
                 $bcc=$value['TO_BCC'];

                $arr=explode(',',$s);
                 $arr_cc=explode(',',$cc);
                 $arr_bcc=explode(',',$bcc);

                if(in_array($userName,$arr) || in_array($userName,$arr_cc) || in_array($userName,$arr_bcc))
                {
                    array_push($id,$value['MAIL_ID']);
                }
                else
                {

                }
            }
             $count=count($id);
            if($count>0)
             $to_user_id=implode(',',$id);
            else
                $to_user_id='null';

            $inbox="select * from msg_inbox where mail_id in($to_user_id) order by(mail_id)desc";
            $inbox_info=DBGet(DBQuery($inbox));
            $mail_inbox_data = array();
            foreach($inbox_info as $key=>$value)
            {
                if($value['MAIL_DATETIME']!='')
               {
                    $inbox_info[$key]['MAIL_DATE'] = date('Y-m-d',strtotime($value['MAIL_DATETIME']));
                    $inbox_info[$key]['MAIL_TIME'] = date('H:i a',strtotime($value['MAIL_DATETIME']));
               }
               else 
               {
                    $inbox_info[$key]['MAIL_DATE'] = '';
                    $inbox_info[$key]['MAIL_TIME'] = '';
               }
               if($value['MAIL_READ_UNREAD']=='')
               {
                    $inbox_info[$key]['MAIL_READ_STATUS'] = 'U';
               }
               if($value['MAIL_READ_UNREAD']!='')
               {
                   $read_user=explode(',',$value['MAIL_READ_UNREAD']);
                   if(!in_array($userName,$read_user))
                   {
                       array_push($key,$value['MAIL_ID']);
                       $inbox_info[$key]['MAIL_READ_STATUS'] = 'U';
                   }
                   else 
                   {
                       $inbox_info[$key]['MAIL_READ_STATUS'] = 'R';
                   }
               }
               if($value['MAIL_ATTACHMENT']!='')
               {
                   $inbox_info[$key]['MAIL_SUBJECT']=$inbox_info[$key]['MAIL_SUBJECT'];
               }
                $inbox_info[$key]['FROM_USER']=GetNameFromUserName($value['FROM_USER']);

                $mail_inbox_data[]=$inbox_info[$key];
            }
            if(count($inbox_info)>0)
            {
                $success = 1;
                $msg = '';
            }
            else 
            {
                $success = 0;
                $msg = 'No data found';
            }

        $data = array('selected_student'=>$student_id,'mail_data'=>$mail_inbox_data,'success'=>$success,'msg'=>$msg);
        }
        elseif($view=='sentmail')
        {
            $link=array();
            $extra=array();
            $mail_outbox_data = array();
            $outbox="SELECT CASE WHEN to_cc is null THEN to_user ELSE concat( to_user, ' ', to_cc ) END AS TO1, msg_outbox.* FROM msg_outbox where from_user='$userName' AND istrash is NULL order by(mail_id) desc";
            $outbox_info=DBGet(DBQuery($outbox));
              foreach($outbox_info as $id=>$value)
              {
                  if(trim($outbox_info[$id]['TO_GRPNAME'])!="")
                  {
                      if(trim($outbox_info[$id]['TO_CC'])=="")
                        $outbox_info[$id]['TO1']=$outbox_info[$id]['TO_GRPNAME'];
                      else
                          $outbox_info[$id]['TO1']=$outbox_info[$id]['TO_GRPNAME'].','.$outbox_info[$id]['TO_CC'];
                  }
                  else 
                  {
                     $user=explode(' ',$outbox_info[$id]['TO1']);
                     if($user[1])
                     {
                        $outbox_info[$id]['TO1']=$user[0].','.$user[1];
                     } 
                  }
                  $TOMULTIPLE="";
                  $TOARR=  explode(",", $outbox_info[$id]['TO1']);
                  foreach($TOARR as $key=>$v)
                  {
                      $add;
                      if($v==$outbox_info[$id]['TO_GRPNAME'])
                      {
                          $add=$v;
                      }
                      else 
                      {
                          $add=  GetNameFromUserName($v);
                      }
                      if($TOMULTIPLE=="")
                          $TOMULTIPLE=$add;
                      else 
                          $TOMULTIPLE.= " ,".$add;

                  }
                  $outbox_info[$id]['TO1']=$TOMULTIPLE;

                    if($value['MAIL_DATETIME']!='')
                    {
                         $outbox_info[$id]['MAIL_DATE'] = date('Y-m-d',strtotime($value['MAIL_DATETIME']));
                         $outbox_info[$id]['MAIL_TIME'] = date('H:i a',strtotime($value['MAIL_DATETIME']));
                    }
                    else 
                    {
                         $outbox_info[$id]['MAIL_DATE'] = '';
                         $outbox_info[$id]['MAIL_TIME'] = '';
                    }
                  if($value['MAIL_ATTACHMENT']!='')
                   {
                       $outbox_info[$id]['MAIL_SUBJECT']=$outbox_info[$id]['MAIL_SUBJECT'];
                   }
                   $mail_outbox_data[]=$outbox_info[$id];
              }
                if(count($outbox_info)>0)
                {
                    $success = 1;
                    $msg = '';
                }
                else 
                {
                    $success = 0;
                    $msg = 'No data found';
                }

        $data = array('selected_student'=>$student_id,'mail_data'=>$mail_outbox_data,'success'=>$success,'msg'=>$msg);
        }
        elseif($view=='trash')
        {
            $link=array();
            $id=array();
            $arr=array();
            $mail_trash_info=array();
            $qr='SELECT ISTRASH,MAIL_ID FROM msg_inbox';
            $fetch=DBGet(DBQuery($qr));

            foreach($fetch as $key =>$value)
            {
                 $s=$value['ISTRASH'];

                $arr=explode(',',$s);


                if(in_array($userName,$arr))
                {
                    array_push($id,$value['MAIL_ID']);
                }
            }
             $count=count($id);
            if($count>0)
             $to_user_id=implode(',',$id);
            else
                $to_user_id='null';
                 $trash='SELECT *,\'RECIEVED\' AS STATUS FROM msg_inbox WHERE MAIL_ID IN ('.$to_user_id.') ORDER BY MAIL_ID DESC';
             $trash_info=DBGet(DBQuery($trash));

             foreach($trash_info as $key=>$value)
             {
                    if($value['MAIL_DATETIME']!='')
                    {
                         $trash_info[$key]['MAIL_DATE'] = date('Y-m-d',strtotime($value['MAIL_DATETIME']));
                         $trash_info[$key]['MAIL_TIME'] = date('H:i a',strtotime($value['MAIL_DATETIME']));
                    }
                    else 
                    {
                         $trash_info[$key]['MAIL_DATE'] = '';
                         $trash_info[$key]['MAIL_TIME'] = '';
                    }
                  if($value['MAIL_ATTACHMENT']!='')
                   {
                       $trash_info[$key]['MAIL_SUBJECT']=$trash_info[$key]['MAIL_SUBJECT'];
                   }
                   $from_User=$value['FROM_USER'];
                   $fromProfile=  DBGet(DBQuery("Select * from login_authentication where username='$from_User'"));
                   $fromProfileId=$fromProfile[1]['PROFILE_ID'];
                   $fromUserId=$fromProfile[1]['USER_ID'];
                   if($fromProfileId!=3 ||$fromProfileId!=4)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM staff WHERE PROFILE_ID='.$fromProfileId.' AND STAFF_ID='.$fromUserId;
                   }
                   if($fromProfileId==3)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM students WHERE STUDENT_ID='.$fromUserId;
                   }
                   if($fromProfileId==4)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM people WHERE PROFILE_ID='.$fromProfileId.' and staff_id='.$fromUserId;
                   }
                   $name=  DBGet(DBQuery($nameQuery));
                   $name=$name[1]['NAME'];
                    $trash_info[$key]['FROM_USER']=$name;

             }

            //////////////////////////////////
           //          For Outbox          //
          ////////////////////////////////// 
            $qr1='SELECT (SELECT username FROM login_authentication WHERE USER_ID=ISTRASH AND PROFILE_ID=\'3\') as ISTRASH,MAIL_ID FROM msg_outbox WHERE FROM_USER=\''.$userName.'\' ';
            $fetch1=DBGet(DBQuery($qr1));

            $id='';
            foreach($fetch1 as $key1 =>$value1)
            {
                 $s1=$value1['ISTRASH'];

                $arr1=explode(',',$s1);


                if(in_array($userName,$arr1))
                {
                    $id.=$value1['MAIL_ID'].',';
                }
            }
            if($id!='')
             $to_user_id=substr($id,0,-1);
            else
                $to_user_id='null';

            $trash1='SELECT *,\'SENT\' AS STATUS FROM msg_outbox WHERE MAIL_ID IN ('.$to_user_id.') ORDER BY MAIL_ID DESC';
            $trash_info1=DBGet(DBQuery($trash1));

             foreach($trash_info1 as $key1=>$value1)
             {
                    if($value1['MAIL_DATETIME']!='')
                    {
                         $trash_info1[$key1]['MAIL_DATE'] = date('Y-m-d',strtotime($value1['MAIL_DATETIME']));
                         $trash_info1[$key1]['MAIL_TIME'] = date('H:i a',strtotime($value1['MAIL_DATETIME']));
                    }
                    else 
                    {
                         $trash_info1[$key1]['MAIL_DATE'] = '';
                         $trash_info1[$key1]['MAIL_TIME'] = '';
                    }
                  if($value1['MAIL_ATTACHMENT']!='')
                   {
                       $trash_info1[$key1]['MAIL_SUBJECT']=$trash_info1[$key1]['MAIL_SUBJECT'];
                   }
                   $fromProfile=  DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME=\''.$value1['FROM_USER'].'\' '));
                   $fromProfileId=$fromProfile[1]['PROFILE_ID'];
                   if($fromProfileId!=3 ||$fromProfileId!=4)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM staff WHERE PROFILE_ID=\''.$fromProfileId.'\' AND STAFF_ID=\''.$fromProfile[1]['USER_ID'].'\'  ';
                   }
                   if($fromProfileId==3)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM students WHERE STUDENT_ID=\''.$fromProfile[1]['USER_ID'].'\'  ';
                   }
                   if($fromProfileId==4)
                   {
                       $nameQuery='SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM people WHERE PROFILE_ID=\''.$fromProfileId.'\' AND STAFF_ID=\''.$fromProfile[1]['USER_ID'].'\'  ';
                   }
                   $name=  DBGet(DBQuery($nameQuery));
                   $name=$name[1]['NAME'];
                   $trash_info1[$key1]['FROM_USER']=$name;

             }
             unset($key1);
             unset($value1);

             foreach($trash_info1 as $key1=>$value1)
             {
        //     if(count($trash_info)>0)    
             $trash_info[]=$value1;
        //     else
        //     $trash_info[1]=$value1;
             }
             foreach($trash_info as $value1)
             {
                $mail_trash_info[]=$value1;
             }
            if(count($mail_trash_info)>0)
            {
                $success = 1;
                $msg = '';
            }
            else 
            {
                $success = 0;
                $msg = 'No data found';
            }

        $data = array('selected_student'=>$student_id,'mail_data'=>$mail_trash_info,'success'=>$success,'msg'=>$msg);

        }  
        elseif($view=='group')
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
            $data = array('selected_student'=>$student_id,'group_info'=>$grp_data,'success'=>$success,'msg'=>$msg);
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
