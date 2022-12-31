<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';

header('Content-Type: application/json');

$_SESSION['UserSchool'] = $_REQUEST['school_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$_REQUEST['user_id'] && $auth_data['user_profile']==$_REQUEST['profile'])
    {
$schooldata = DBGet(DBQuery('SELECT * FROM schools WHERE ID=\''.UserSchool().'\''));
$schooldata = $schooldata[1];

$uploaded_sql=DBGet(DBQuery("SELECT VALUE FROM program_config WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR IS NULL AND TITLE='PATH'"));

//        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//        $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
//        $file_path = $scr_path[1];
//        $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/".$uploaded_sql[1]['VALUE'];
$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
$scr_path = explode('/webservice/',$_SERVER['SCRIPT_NAME']);
$file_path = $scr_path[0];

$htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
if($file_path!='')
$htpath=$htpath."/".$file_path;
$htpath=$htpath."/".$uploaded_sql[1]['VALUE'];

        
$_SESSION['logo_path']=$uploaded_sql[1]['VALUE'];
if($uploaded_sql[1]['VALUE']!='')
    $schooldata['SCHOOL_LOGO'] = $htpath;
else
    $schooldata['SCHOOL_LOGO'] = '';

if(count($schooldata)>0)
{
    $success = 1;
    $msg = 'nil';
}
else 
{
    $success = 0;
    $msg = 'no data found';
}

$data['school_data'] = $schooldata;
$data['success'] = $success;
$data['msg'] = $msg;
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
