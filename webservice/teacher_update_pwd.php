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
$current_pwd = urldecode($_REQUEST['current']);
$new_pwd = urldecode($_REQUEST['new']);
$verify_pwd = urldecode($_REQUEST['verify']);
$column_name= PASSWORD;

$pass_current= paramlib_validation($column_name,$current_pwd);
$pass_new= paramlib_validation($column_name,$new_pwd);
$pass_verify= paramlib_validation($column_name,$verify_pwd);


$pass_new_after= md5($pass_new);

$profile_RET = DBGet(DBQuery('SELECT s.PROFILE FROM staff s , staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND s.STAFF_ID=\''.UserWs('STAFF_ID').'\' AND ssr.SYEAR=\''.UserSyear().'\''));



if(UserWs('PROFILE')=='parent')
$sql=DBQuery('SELECT l.PASSWORD FROM people p,login_authentication l WHERE l.USER_ID=\''.UserWs('STAFF_ID').'\' AND l.USER_ID=p.STAFF_ID AND l.password=\''.$pass_new_after.'\' AND l.PROFILE_ID=p.PROFILE_ID');       
else
$sql=DBQuery('SELECT l.PASSWORD FROM staff s , staff_school_relationship ssr,login_authentication l where l.USER_ID=\''.UserWs('STAFF_ID').'\' AND l.USER_ID=s.STAFF_ID AND l.password=\''.$pass_new_after.'\'  AND ssr.STAFF_ID=s.STAFF_ID AND ssr.SYEAR=\''.UserSyear().'\' AND l.PROFILE_ID=s.PROFILE_ID');
$number=mysql_num_rows($sql);            

    if($pass_new != $pass_verify)
    {
            $success = 0;
            $msg = 'Your new passwords did not match.';
    }
    elseif($number>0)
    {
            $success = 0;
            $msg = 'This password is alredy taken';
    }

    else
    {
            if(UserWs('PROFILE')=='parent')
            {
                    $password_RET = DBGet(DBQuery('SELECT l.PASSWORD FROM people p,login_authentication l WHERE l.USER_ID=\''.UserWs('STAFF_ID').'\' AND l.USER_ID=p.STAFF_ID AND l.PROFILE_ID=p.PROFILE_ID'));
            }
            else
            {
                    $password_RET = DBGet(DBQuery('SELECT l.PASSWORD FROM staff s , staff_school_relationship ssr,login_authentication l where l.USER_ID=\''.UserWs('STAFF_ID').'\' AND l.USER_ID=s.STAFF_ID AND ssr.STAFF_ID=s.STAFF_ID AND ssr.SYEAR=\''.UserSyear().'\' AND l.PROFILE_ID=s.PROFILE_ID'));
            }

            if($password_RET[1]['PASSWORD'] != md5($pass_current))
            {
                    $success = 0;
                    $msg = 'Your current password was incorrect.';
            }
            else
            {
                    DBQuery('UPDATE login_authentication SET PASSWORD=\''.md5($pass_new).'\' WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND PROFILE_ID=\''.UserWs('PROFILE_ID').'\' ');
                    $msg = 'Your new password was saved.';
                    $success = 1;
            }
    }
$data = array('success'=>$success,'msg'=>$msg);
echo json_encode($data);
?>
