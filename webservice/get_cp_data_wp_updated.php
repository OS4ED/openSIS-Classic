<?php
$DatabaseType = 'mysql'; 
$DatabaseServer = 'localhost'; 
$DatabaseUsername = 'root'; 
$DatabasePassword = 'Ge0rg1a'; 
$DatabaseName = 'opensis6';
$DatabasePort = '3306';

$con = mysqli_connect($DatabaseServer,$DatabaseUsername,$DatabasePassword,$DatabaseName);
$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
$scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
$file_path = $scr_path[1];

$htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/userphotos/";
$path ='../assets/userphotos/';
$cp_data_sql = 'SELECT cp.course_period_id,cp.course_id,(SELECT title FROM courses WHERE course_id = cp.course_id) AS course_name,cp.title,cp.short_name,cp.total_seats,cp.total_seats,cp.filled_seats,(cp.total_seats - cp.filled_seats) as available_seats,(SELECT CONCAT(first_name,\' \',last_name) AS staff_name FROM staff WHERE staff_id = cp.teacher_id) AS teacher_name,cp.teacher_id FROM  course_periods cp WHERE cp.syear=\'2016\' ORDER BY cp.course_id';
$res=mysqli_query($con,$cp_data_sql);
$row_count=mysqli_num_rows($res);
$result = array();
if($row_count>0)
{
    $i=0;
    while($a = mysqli_fetch_assoc($res))
    {
        $PicPath=$path.$a['teacher_id'].".JPG";
        if(file_exists($PicPath))
            $a['teacher_photo']=$htpath.$a['teacher_id'].".JPG";
        else 
            $a['teacher_photo']="";
        $cp_days_sql = 'SELECT cpv.days,sp.title FROM course_period_var cpv LEFT JOIN  school_periods sp ON sp.period_id = cpv.period_id WHERE cpv.course_period_id = '.$a['course_period_id'];
        $res1=mysqli_query($con,$cp_days_sql);
        while($data = mysqli_fetch_assoc($res1))
        {
            $a['occuring_days'][]=array('day'=>$data['days'],'period_name'=>$data['title']);
        }
        $result[$i] = $a;
        $i++;
    }
}
if(count($result)>0)
{
    $op=array();
    foreach($result as $re)
    {
        $op[$re['course_name']]['values'][]=$re;
        $op[$re['course_name']]['count']=$op[$re['course_name']]['count']+1;
    }
    $success = 1;
}
else 
{
    $success = 0;
}

echo json_encode(array('success'=>$success,'result'=>$op));
