<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$stu_PASS=DBGet(DBQuery('SELECT la.PASSWORD FROM login_authentication la, students s WHERE s.STUDENT_ID=\''.$student_id.'\' AND la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID=3'));
$pass_old=$_REQUEST['current'];
if($pass_old=="")
 {
   $msg = "Please Type The Password";
   $success = 0;
 }
 else
 {
    $column_name= PASSWORD;
    $pass_old= paramlib_validation($column_name,$_REQUEST['current']);
    $pass_new= paramlib_validation($column_name,$_REQUEST['new']);
    $pass_retype= paramlib_validation($column_name,$_REQUEST['verify']);
    
    $pass_old = str_replace("\'","''",md5($pass_old));
    $pass_new = str_replace("\'","''",md5($pass_new));
    $pass_retype = str_replace("\'","''",md5($pass_retype));
    if($stu_PASS[1]['PASSWORD']==$pass_old)
    {
        if($pass_new==$pass_retype)
        {
         $sql='UPDATE login_authentication SET PASSWORD=\''.$pass_new.'\' WHERE USER_ID=\''.$student_id.'\' AND PROFILE_ID=3 ';
         DBQuery($sql);
         $msg  = "Password Sucessfully Changed";
         $success = 1;
        }
        else
        {
           $msg = "Please Retype Password";
           $success = 0;
        }
    }
    else
    {
        $msg = "Old password is incorrect";
        $success = 0;
    }
 }

$data = array('success'=>$success,'msg'=>$msg);
echo json_encode($data);
?>
