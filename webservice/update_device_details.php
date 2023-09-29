<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
header('Content-Type: application/json');

$user_id = $_REQUEST['user_id'];
$profile_id = $_REQUEST['profile_id'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$user_id && $auth_data['user_profile']==$_REQUEST['profile'])
    {
        if(isset($_REQUEST['action']) && $_REQUEST['action']=='logout')
        {
            $sql = 'DELETE FROM device_info WHERE user_id=\''.$user_id.'\' AND profile_id = \''.$profile_id.'\' AND device_type = \''.$_REQUEST['device_type'].'\' AND device_id = \''.$_REQUEST['device_id'].'\'';
            $del = DBQuery($sql);
            if($del==true)
                {
                    $success = 1;
                    $msg = 'data delete successful';
                }
                else 
                {
                    $success = 0;
                    $msg = 'data delete failed';
                }
        }
        else 
        {
            $sql = 'SELECT id FROM device_info WHERE user_id=\''.$user_id.'\' AND profile_id = \''.$profile_id.'\' AND device_type = \''.$_REQUEST['device_type'].'\' AND device_id = \''.$_REQUEST['device_id'].'\'';
            $user = DBGet(DBQuery($sql));
            if(count($user)>0)
            {
                $up_sql = 'UPDATE device_info SET device_token = \''.$_REQUEST['device_token'].'\' WHERE user_id=\''.$user_id.'\' AND profile_id = \''.$profile_id.'\' AND device_type = \''.$_REQUEST['device_type'].'\' AND device_id = \''.$_REQUEST['device_id'].'\'';
                $up = DBQuery($up_sql);
                if($up==true)
                {
                    $success = 1;
                    $msg = 'data update successful';
                }
                else 
                {
                    $success = 0;
                    $msg = 'data update failed';
                }
            }
            else 
            {
                $ins_sql = 'INSERT INTO device_info (user_id,profile_id,device_type,device_token,device_id) values (\''.$user_id.'\',\''.$profile_id.'\',\''.$_REQUEST['device_type'].'\',\''.$_REQUEST['device_token'].'\',\''.$_REQUEST['device_id'].'\')';
                $ins = DBQuery($ins_sql);
                if($ins==true)
                {
                    $success = 1;
                    $msg = 'data update successful';
                }
                else 
                {
                    $success = 0;
                    $msg = 'data update failed';
                }
            }
        }
        $data = array('success' => $success, 'msg' => $msg); 
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