<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/function.php';
$action=$_REQUEST['action'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$_REQUEST['user_id'] && $auth_data['user_profile']==$_REQUEST['profile'])
    {
//$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//$path = explode('/',$_SERVER['SCRIPT_NAME']);
//
//$file_path = $path[1];
//$out=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/";
    
if($action == 'upload')
{
    $key=$_REQUEST['key'];
    $path = $_REQUEST['filename'];
    
    $temp=$_FILES['f']['tmp_name'];
    $file_path = "../assets/";
    $folder = $file_path . basename($path); 
	
    if(move_uploaded_file($temp,$folder))
    {
        $folder = substr($folder,1);
	$inbox_query=  DBQuery('INSERT INTO temp_message_filepath_ws (keyval,filepath) VALUES (\''.$key.'\',\''.$folder.'\')'); 
        $data = array('filepath'=>$folder); 
    }
    else 
    {
        $data = array('filepath'=>'file upload failed'); 
    }
}
if($action == 'remove')
{
    $key=$_REQUEST['key'];
    $path=$_REQUEST['filename'];
    $folder="./assets/".basename($path);
    if(file_exists('.'.$folder))
    {
        if(unlink('.'.$folder))
        {
            $query = DBQuery('DELETE FROM temp_message_filepath_ws WHERE keyval = \''.$key.'\' AND filepath = \''.$folder.'\'');
            $success = 1;
            $msg = 'nil';
        }
        else 
        {
            $success = 0;
            $msg = 'file delete failed';
        }

    }
    else 
    {
        $success = 0;
        $msg = 'file does not exist';
    }
    $data = array('success'=>$success, 'msg'=>$msg);
}
if($action == 'remove_all')
{
    $key=$_REQUEST['key'];
//    $path=$_REQUEST['filename'];
//    $folder="../assets/".basename($path);
    $queryall = DBGet(DBQuery('SELECT filepath FROM temp_message_filepath_ws WHERE keyval = \''.$key.'\''));
    $flag = array();
    if(count($queryall)>0)
    {
    foreach($queryall as $row)
    {
        if(file_exists('.'.$row['FILEPATH']))
        {
            if(unlink('.'.$row['FILEPATH']))
            {
                $query = DBQuery('DELETE FROM temp_message_filepath_ws WHERE keyval = \''.$key.'\' AND filepath = \''.$row['FILEPATH'].'\'');
                $flag[] = 0;
            }
            else 
            {
                $flag[] = 1;
            }
        }	
    }
    foreach($flag as $f)
    {
        if($f == 1)
        {
            $success = 0;
            $msg = 'file delete failed';
            break;
        }
        else 
        {
            $success = 1;
            $msg = 'nil';
        }
    }
    }
    else 
    {
        $success = 1;
        $msg = 'file does not exist';
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