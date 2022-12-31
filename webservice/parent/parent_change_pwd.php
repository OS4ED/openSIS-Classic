<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$parent_id = $_REQUEST['parent_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {

        $stu_PASS=DBGet(DBQuery('SELECT la.PASSWORD FROM login_authentication la, people p WHERE p.STAFF_ID =\''.$parent_id.'\' AND la.USER_ID=p.STAFF_ID AND la.PROFILE_ID=4'));
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
                 $sql='UPDATE login_authentication SET PASSWORD=\''.$pass_new.'\' WHERE USER_ID=\''.$parent_id.'\' AND PROFILE_ID=4 ';
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

        $data = array('selected_student'=>$student_id,'success'=>$success,'msg'=>$msg);
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
