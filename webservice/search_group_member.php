<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include '../functions/DbDateFnc.php';
//include 'function/Current.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']==$_REQUEST['profile'])
    {
if(UserWs('PROFILE')=='teacher')
{
    $profiles=  DBGet(DBQuery('SELECT * FROM user_profiles where id!=2'));
}
else if(UserWs('PROFILE')=='parent')
{
    $profiles=  DBGet(DBQuery('SELECT * FROM user_profiles where id!=4'));
} 
else if(UserWs('PROFILE')=='student')
{
    $profiles=  DBGet(DBQuery('SELECT * FROM user_profiles where id!=3'));
} 
else
    $profiles=  DBGet(DBQuery('SELECT * FROM user_profiles'));
//$options[-1]='N/A';
$options[0]['TITLE']='N/A';
    $options[0]['ID']='';
$i=1;
foreach($profiles as $key=>$value)
{
    $options[$i]['TITLE']=$value['TITLE'];
    $options[$i]['ID']=$value['ID'];
    $i++;
}

$data['profile'] = $options;
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
