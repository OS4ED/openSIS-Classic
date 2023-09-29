<?php
if(isset($_REQUEST['url']) && $_REQUEST['url']!='')
{
    $DatabaseType = 'mysql'; 
    $DatabaseServer = 'localhost'; 
    $DatabaseUsername = 'root'; 
    $DatabasePassword = ''; 
    $DatabaseName = 'opensis_ce_7_demo'; 
    $DatabasePort = '3306'; 
    include 'function/DbGetFnc.php';
    include 'function/function.php';
    
    header('Content-Type: application/json');
    $url_check_data = array();
    $url=$_REQUEST['url'];
    $school_info = DBGet_Mod(DBQuery('SELECT * FROM school_urls  WHERE MAINURL=\''.$url.'\''),array(),array());
//    $log_msg=  DBGet(DBQuery("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));
    if(count($school_info) > 0)
    {
        $c=mysql_connect($school_info[1]['DBSERVER'],$school_info[1]['DBUSERNAME'],$school_info[1]['DBPASSWORD']);
        $c=mysql_select_db($school_info[1]['DBNAME'],$c);
        $log_msg=  DBGet_Mod(mysql_query("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));
        $url_check_data['success'] = 1;
        $url_check_data['msg'] = 'correct url';
        $url_check_data['host'] = $school_info[1]['MAINHOST'];
        $url_check_data['log_msg'] = $log_msg[1]['MESSAGE'];
    }
    else 
    {
        $url_check_data['success'] = 0;
        $url_check_data['msg'] = 'incorrect url';
        $url_check_data['host'] = '';
        $url_check_data['log_msg'] = '';
    }
    echo json_encode($url_check_data);
}

?>
