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
$view = $_REQUEST['view'];
$mail_ids = explode(',',$_REQUEST['mail_ids']);
$userName=  UserWs('USERNAME');

if(count($mail_ids)!=0)
{
    if($view == 'inbox')
    {
        $to_arr=array();
        $arr=array();
        $qr="select to_user,istrash,to_cc,to_bcc from msg_inbox where mail_id IN(".$_REQUEST['mail_ids'].")";
        $fetch=DBGet(DBQuery($qr));
        foreach($fetch as $key =>$value)
        {
             $s=$value['TO_USER'];"<br>";
             $to_cc=$value['TO_CC'];
             $to_cc_arr=explode(',',$to_cc);
             $arr=explode(',',$s);
             $to_bcc=$value['TO_BCC'];
             $to_bcc_arr=explode(',',$to_bcc);


            if(($key = array_search($userName,$arr)) !== false) {
                unset($arr[$key]);
                $update_to_user=implode(',',$arr);
                if($value['ISTRASH']!='')
                {
                    $to_arr=explode(',',$value['ISTRASH']);

                        array_push($to_arr,$userName);

                        $trash_user=implode(',',$to_arr);

                }
                 else
                {
                   $trash_user=$userName;
                }

            //       $trash_user=$userName;
                   $query="update msg_inbox set to_user='$update_to_user',istrash='$trash_user' where mail_id IN (".$_REQUEST['mail_ids'].")";

                $fetch_ex=DBQuery($query);
         }
         if(($key = array_search($userName, $to_cc_arr)) !== false) {
            unset( $to_cc_arr[$key]);
            $update_to_user=implode(',', $to_cc_arr);
            if($value['ISTRASH']!='')
            {
                $to_arr=explode(',',$value['ISTRASH']);

                    array_push($to_arr,$userName);

                    $trash_user=implode(',',$to_arr);
            }
            else
            {
               $trash_user=$userName;
            }


            $query="update msg_inbox set to_cc='$update_to_user',istrash='$trash_user' where mail_id IN (".$_REQUEST['mail_ids'].")";

            $fetch_ex=DBQuery($query);

         }
            if(($key = array_search($userName,$to_bcc_arr)) !== false) {
                unset( $to_bcc_arr[$key]);
                $update_to_user=implode(',',$to_bcc_arr);
                if($value['ISTRASH']!='')
                {
                    $to_arr=explode(',',$value['ISTRASH']);

                        array_push($to_arr,$userName);

                        $trash_user=implode(',',$to_arr);
                }
                 else
                {
                   $trash_user=$userName;
                }
                $query="update msg_inbox set to_bcc='$update_to_user',istrash='$trash_user' where mail_id IN (".$_REQUEST['mail_ids'].")";
                $fetch_ex=DBQuery($query);
            }
        }
        $msg = "Message moved to trash";
    }
    elseif($view == 'sentmail')
    {
        $mail_delete="UPDATE msg_outbox SET ISTRASH=".UserWs('STAFF_ID')." WHERE MAIL_ID IN(".$_REQUEST['mail_ids'].")";
        $mail_delete_ex=DBQuery($mail_delete);
        $msg = "Message moved to trash";
    }
    elseif($view == 'trash') 
    {
        
//        $id=array_keys($_REQUEST['mail']);
        foreach($mail_ids as $idd)
        {
            $idd=explode("_",$idd);

            if($idd[1]=='SENT')
            $sent[]=$idd[0];
            else
            $recieved[]=$idd[0];    
        }
        if(count($sent)>0 || count($recieved)>0)
        {
            if(count($sent)>0)
            {
                $sent=implode(',',$sent);

                $arr=array();


               $qr='SELECT (SELECT username FROM login_authentication WHERE USER_ID=ISTRASH AND PROFILE_ID='.UserWs('PROFILE_ID').') AS ISTRASH FROM msg_outbox WHERE MAIL_ID IN ('.$sent.')';
                $fetch=DBGet(DBQuery($qr));

                foreach($fetch as $key =>$value)
                {
                    $s=$value['ISTRASH'];

                    $arr=explode(',',$s);


                    if(($key = array_search($userName,$arr)) !== false) {
                    unset($arr[$key]);
                    $update_to_user=implode(',',$arr);
                    $query='UPDATE msg_outbox SET FROM_USER=\'\' WHERE MAIL_ID IN ('.$sent.')';
                    $fetch_ex=DBQuery($query);
                    }
                }
            }
            if(count($recieved)>0)
            {
                $recieved=implode(',',$recieved);

                $arr=array();


                $qr="select istrash from msg_inbox where mail_id IN ($recieved)";
                $fetch=DBGet(DBQuery($qr));

                foreach($fetch as $key =>$value)
                {
                    $s=$value['ISTRASH'];"<br>";

                    $arr=explode(',',$s);


                    if(($key = array_search($userName,$arr)) !== false) {
                        unset($arr[$key]);
                        $update_to_user=implode(',',$arr);
                        $query="update msg_inbox set istrash='$update_to_user' where mail_id IN ($recieved)";
                        $fetch_ex=DBQuery($query);
                    }
                }
            }
            $msg = "Message deleted permanently";
        }
        else 
        {
            $msg = "Message deletion failed";
        }
    }
    
}
else
{
    $msg = "Please select atleast one message to delete";
}
$data = array('msg'=>$msg);
echo json_encode($data);
?>
