<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$parent_id = $_REQUEST['parent_id'];
$profile_id =  $_REQUEST['profile_id'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $usrnm_sql = 'SELECT USERNAME FROM  login_authentication WHERE PROFILE_ID = '.$profile_id.' AND USER_ID = '.$parent_id;
        $userName_data =  DBGet(DBQuery($usrnm_sql));
        $userName = $userName_data[1]['USERNAME'];

            $mail_id=$_REQUEST['mail_id'];
            $mail_body="select mail_body,mail_attachment,mail_Subject,from_user,mail_datetime,to_cc_multiple,to_multiple_users,to_bcc_multiple,mail_read_unread from msg_inbox where mail_id='$mail_id'";

            $mail_body_info=DBGet(DBQuery($mail_body));
            $sub=$mail_body_info[1]['MAIL_SUBJECT'];
            if($mail_body_info[1]['MAIL_READ_UNREAD']=="")
                $user_name=$userName;
            else 
            {
                $read_unread_Arr=  explode(",", $mail_body_info[1]['MAIL_READ_UNREAD']);
                if(in_array($userName, $read_unread_Arr))
                {
                    $user_name=$mail_body_info[1]['MAIL_READ_UNREAD'];
                }
                else
                {
                    $mail_body_info[1]['MAIL_READ_UNREAD'].=','.$userName;
                    $user_name=$mail_body_info[1]['MAIL_READ_UNREAD'];
                }
            }
            $mail_read_unread="update msg_inbox set mail_read_unread='$user_name' where mail_id='$mail_id'";
            $mail_read_unread_ex=DBQuery($mail_read_unread);

            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
            $path = explode('/',$_SERVER['SCRIPT_NAME']);

            $file_path = $path[1];
            $out=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/";
            $attachment = array();
            foreach($mail_body_info as $k => $v)
            {
                 $fromUser=$v['FROM_USER'];
                 $mail_body_info[$k]['FROM'] = GetNameFromUserName($v['FROM_USER']);
                 $mail_body_info[$k]['DATE'] = date('Y-m-d',strtotime($v['MAIL_DATETIME']));
                 $mail_body_info[$k]['TIME'] = date('H:i a',strtotime($v['MAIL_DATETIME']));
                 if($v['MAIL_ATTACHMENT']!='')
                 {
                    $attach=explode(',',$v['MAIL_ATTACHMENT']);
                    foreach($attach as $user=>$img)
                    {
                        $img_pos=strrpos($img,'/');
                        $img_name[]=substr($img,$img_pos+1,strlen($img));
                        $pos=strpos($img,'_');

                        $img_src[]=substr($img,$pos+1,strlen($img));
                        for($i=0;$i<(count($img_src));$i++)
                        {
                          $img1=$img_src[$i];
                          $m=array_keys(str_word_count($img1, 2));
                          $a=$m[0];
                          $img3[$i]=substr($img1,$a,strlen($img1));
                        }

                    }
                    for($i=0;$i<(count($attach));$i++)
                    {

                               $img_name[$i]=urlencode($img_name[$i]);
                               $img4[$i]=urlencode($img3[$i]);

                               $filepath = $out . str_replace('./','',$v['MAIL_ATTACHMENT']);

                               $attachment[$i]['file_path'] = $filepath;


                    }
                 }
            }
            if(count($mail_body_info)>0)
            {
                $mail_body_info[1]['success'] = 1;
                $mail_body_info[1]['msg'] = 'nil';
                $mail_body_info[1]['selected_student'] = $student_id;
            }
            else 
            {
                $mail_body_info[1]['success'] = 0;
                $mail_body_info[1]['msg'] = 'No data found';
                $mail_body_info[1]['selected_student'] = $student_id;
            }

            if(count($attachment)>0)
            {
                $att_data = array('ATTACHMENTS'=>$attachment,'attachment_success'=>1,'attachment_msg'=>'nil');
            }
            else 
            {
                $att_data = array('ATTACHMENTS'=>$attachment,'attachment_success'=>0,'attachment_msg'=>'No attachment found');
            }
            $data = array_merge($mail_body_info[1],$att_data);
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
