<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';;
include '../function/WidgetsFnc.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $_SESSION['PROFILE_ID'] =  $_REQUEST['profile_id'];
        $_SESSION['student_id']=$_SESSION['STUDENT_ID'] = $student_id = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
        $gpa_mp_id =  $_REQUEST['gpa_mp_id'];
        $pro_data = array();
        if($_REQUEST['view_type'] == 'list')
        {
                if(!$_REQUEST['mp_id'] && GetMP(UserMP(),'POST_START_DATE',UserSyear(),UserSchool()))
                        $_REQUEST['mp_id'] = UserMP();
                elseif(strpos(GetAllMP('QTR',UserMP()),str_replace('E','',$_REQUEST['mp_id']))===false && strpos(GetChildrenMPWs('PRO',UserMP(),UserSyear(),UserSchool()),"'".$_REQUEST['mp_id']."'")===false && GetMP(UserMP(),'POST_START_DATE',UserSyear(),UserSchool()))
                        $_REQUEST['mp_id'] = UserMP();

                if(!$_REQUEST['mp_id'] && GetMP(GetParentMPWs('SEM',UserMP(),UserSyear(),UserSchool()),'POST_START_DATE',UserSyear(),UserSchool()))
                        $_REQUEST['mp_id'] = GetParentMPWs('SEM',UserMP(),UserSyear(),UserSchool());	

                $sem = GetParentMPWs('SEM',UserMP(),UserSyear(),UserSchool());
                $pro = GetChildrenMPWs('PRO',UserMP());
                $pros = explode(',',str_replace("'",'',$pro));
                $pro_grading = false;
                $pro_select = '';

                foreach($pros as $pro)
                {
                    $pro_select_data = array();
                        if($pro!='' && GetMP($pro,'POST_START_DATE',UserSyear(),UserSchool()))
                        {
                                if(!$_REQUEST['mp_id'])
                                {
                                        $_REQUEST['mp_id'] = $pro;
                                        $current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_GRADE_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_grades g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID='.$course_period_id.' AND g.MARKING_PERIOD_ID=\''.$_REQUEST['mp_id'].'\''),array(),array('STUDENT_ID'));
                                }
                                $pro_grading = true;
                                $pro_select_data[0]['id']=$pro;
                                $pro_select_data[0]['title']=GetMP($pro,'TITLE',UserSyear(),UserSchool());
                                $pro_select_data[1]['id']=$sem.'E';
                                $pro_select_data[1]['title']=GetMP($sem,'TITLE',UserSyear(),UserSchool()).' Exam';
                        }
                        if(count($pro_select_data)>0)
                            $pro_data[]=$pro_select_data;
                }

                if(GetMP(UserMP(),'POST_START_DATE',UserSyear(),UserSchool()))
                {
                    $pro_select_data = array();
                    $pro_select_data[0]['id']=UserMP();
                    $pro_select_data[0]['title']=GetMP(UserMP(),'TITLE',UserSyear(),UserSchool());
                    $pro_data[]=$pro_select_data[0];
                }
                elseif($_REQUEST['mp_id']==UserMP())
                        $_REQUEST['mp_id'] = $sem;

                if(GetMP($sem,'POST_START_DATE',UserSyear(),UserSchool()))
                {
                    $pro_select_data = array();
                    $pro_select_data[0]['id']=$sem;
                    $pro_select_data[0]['title']=GetMP($sem,'TITLE',UserSyear(),UserSchool());
                    $pro_data[]=$pro_select_data[0];
                    $pro_select_data[0]['id']=$sem.'E';
                    $pro_select_data[0]['title']=GetMP($sem,'TITLE',UserSyear(),UserSchool()).' Exam';
                    $pro_data[]=$pro_select_data[0];
                }
        }

        //Widgets('course');

        //Widgets('letter_grade'); 


        if(!$_REQUEST['list_gpa'])
        {
            $extra['SELECT'] .= ',sgc.gpa,sgc.weighted_gpa, sgc.unweighted_gpa,sgc.class_rank';

            if(strpos($extra['FROM'],'student_mp_stats sms')===false)
            {
                    $extra['FROM'] .= ',student_gpa_calculated sgc';
                    $extra['WHERE'] .= ' AND sgc.STUDENT_ID=ssm.STUDENT_ID AND sgc.MARKING_PERIOD_ID=\''.$gpa_mp_id.'\'';
            }
        }

        $SCHOOL_RET = DBGet(DBQuery('SELECT * from schools where ID = \''.UserSchool().'\''));
        $students_RET = GetStuListWs($extra,'','gpa_ranklist');

//        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//        $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
//        $file_path = $scr_path[1];
//
//        $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/studentphotos/";
//        $path ='../../assets/studentphotos/';
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        $scr_path = explode('/webservice/',$_SERVER['SCRIPT_NAME']);
        $file_path = $scr_path[0];

        $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
        if($file_path!='')
        $htpath=$htpath."/".$file_path;
        $htpath=$htpath."/assets/studentphotos/";

        $path ='../../assets/studentphotos/';
        foreach($students_RET as $sr=>$sd)
        {
            $class_rank[$sd['STUDENT_ID']]=$sd['GPA'];
        }

        $new_class_rank=array_unique($class_rank);
        rsort($new_class_rank);

        $final_class_rank=array();
        unset($cr);
        unset($cd);
        foreach($class_rank as $ci=>$cr)
        {
            $array_key=array_keys($new_class_rank,$cr);
            $final_class_rank[$ci]= $array_key[0]+1;
        }
        unset($sr);
        unset($sd);
        foreach($students_RET as $sr=>$sd)
        {
            $students_RET[$sr]['CLASS_RANK']=$final_class_rank[$sd['STUDENT_ID']];
        }
        unset($class_rank);
        unset($new_class_rank);
        unset($final_class_rank);
        unset($array_key);
        unset($sr);
        unset($sd);
        
        $student_data = array();
        foreach($students_RET as $student)
        {
            if($student['STUDENT_ID']==$student_id)
            {
                $stuPicPath=$path.$student['STUDENT_ID'].".JPG";
                if(file_exists($stuPicPath))
                    $student['PHOTO']=$htpath.$student['STUDENT_ID'].".JPG";
                else 
                    $student['PHOTO']="";
                $student['RANK_SUFFIX']=  ordinal($student['CLASS_RANK']);
                $student_data[]=$student;
            }
        }
        if(count($pro_data)>0)
        {
            $mp_success = 1;
            $mp_msg = 'Nil';
        }
        else 
        {
            $mp_success = 0;
            $mp_msg = 'No data found';
        }
        if(count($student_data)>0)
        {
            $success = 1;
            $msg = 'Nil';
        }
        else 
        {
            $success = 0;
            $msg = 'No data found';
        }
        if($mp_success == 1 || $success == 1)
            $all_success = 1;
        else 
            $all_success = 0;
        $data = array('selected_student'=>$student_id,'outof'=>count($students_RET),'mp_data'=>$pro_data,'selected_mp'=>$gpa_mp_id,'mp_success'=>$mp_success,'mp_msg'=>$mp_msg,'student_data'=>$student_data,'student_success'=>$success,'success'=>$all_success,'student_msg'=>$msg);
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
function _roundGPA($gpa,$column)
{   GLOBAL $SCHOOL_RET;
	return round($gpa*$SCHOOL_RET[1]['REPORTING_GP_SCALE'],3);
}
function ordinal($number) 
{
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return 'th';
    else
        return $ends[$number % 10];
}
echo json_encode($data);
?>
