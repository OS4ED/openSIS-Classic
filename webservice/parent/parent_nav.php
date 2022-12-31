<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

include '../function/ProperDateFnc.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];
$_SESSION['PROFILE_ID'] =  $_REQUEST['profile_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $data['selected_student']=$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
        $changed_value = $_REQUEST['changed_value'];
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID, se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));
                foreach($RET as $student)
                {
                    $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
                }
                $school_years_RET1=DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=".UserSchool()." AND SYEAR=".UserSyear()));
                $school_years_RET1=$school_years_RET1[1];
                $school_years_RET1['START_DATE']=explode("-",$school_years_RET1['START_DATE']);
                $school_years_RET1['START_DATE']=$school_years_RET1['START_DATE'][0];
                $school_years_RET1['END_DATE']=explode("-",$school_years_RET1['END_DATE']);
                $school_years_RET1['END_DATE']=$school_years_RET1['END_DATE'][0];
                $i=$s=$m=0;
                if($school_years_RET1['END_DATE']>$school_years_RET1['START_DATE'])
                {
                    if(UserStudentIDWs()=='')
                    {
                        $stu_ID=DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".$login_RET[1]['STAFF_ID']."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));    
                        $stu_ID=$stu_ID[1]['STUDENT_ID'];
                    }
                    else
                    $stu_ID=UserStudentIDWs();    
                    $school_years_RET=DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=".$stu_ID." AND sy.SCHOOL_ID=".UserSchool()." "));

                    foreach($school_years_RET as $school_years)
                    {
                        $st_date=explode("-",$school_years['START_DATE']);
                        $school_years['START_DATE']=$st_date[0];
                        $end_date=explode("-",$school_years['END_DATE']);
                        $school_years['END_DATE']=$end_date[0];
                        $school_years_data[$i]['ID']=$school_years['START_DATE'];
                        $school_years_data[$i]['VALUE']=$school_years['START_DATE']."-".$school_years['END_DATE'];
                        $i++;
//                        echo "<OPTION value=$school_years[START_DATE]".((UserSyear()==$school_years['START_DATE'])?' SELECTED':'')."> $school_years[START_DATE]-".($school_years['END_DATE'])."</OPTION>";
                    }
                }
                else if($school_years_RET1['END_DATE']==$school_years_RET1['START_DATE'])
                {
                    if(UserStudentIDWs()=='')
                    {
                    $stu_ID=DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".$login_RET[1]['STAFF_ID']."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));    
                    $stu_ID=$stu_ID[1]['STUDENT_ID'];
                    }
                    else
                    $stu_ID=UserStudentIDWs();    
                    $school_years_RET=DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=".$stu_ID." AND sy.SCHOOL_ID=".UserSchool()." "));

                    foreach($school_years_RET as $school_years)
                    {
                        $st_date=explode("-",$school_years['START_DATE']);
                        $school_years['START_DATE']=$st_date[0];
                        $end_date=explode("-",$school_years['END_DATE']);
                        $school_years['END_DATE']=$end_date[0];
                        $school_years_data[$i]['ID']=$school_years['START_DATE'];
                        $school_years_data[$i]['VALUE']=$school_years['START_DATE'];
                        $i++;
//                        echo "<OPTION value=$school_years[START_DATE]".((UserSyear()==$school_years['START_DATE'])?' SELECTED':'')."> $school_years[START_DATE]-".($school_years['END_DATE'])."</OPTION>";
                    }
                }
/*
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID, se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));
        foreach($RET as $student)
        {
            $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
        }
        $school_years_RET1=DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=".UserSchool()." AND SYEAR=".UserSyear()));
        $school_years_RET1=$school_years_RET1[1];
        $school_years_RET1['START_DATE']=explode("-",$school_years_RET1['START_DATE']);
        $school_years_RET1['START_DATE']=$school_years_RET1['START_DATE'][0];
        $school_years_RET1['END_DATE']=explode("-",$school_years_RET1['END_DATE']);
        $school_years_RET1['END_DATE']=$school_years_RET1['END_DATE'][0];
        $i=$s=$m=0;
        if($school_years_RET1['END_DATE']>$school_years_RET1['START_DATE'])
        {
            if(UserStudentIDWs()=='')
            {
                $stu_ID=DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));    
                $stu_ID=$stu_ID[1]['STUDENT_ID'];
            }
            else
            $stu_ID=UserStudentIDWs();    
            $school_years_RET=DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=".$stu_ID." AND sy.SCHOOL_ID=".UserSchool()." "));

            foreach($school_years_RET as $school_years)
            {
                $school_years['START_DATE']=explode("-",$school_years['START_DATE']);
                $school_years['START_DATE']=$school_years['START_DATE'][0];
                $school_years['END_DATE']=explode("-",$school_years['END_DATE']);
                $school_years['END_DATE']=$school_years['END_DATE'][0];
                $school_years_data[$i]['ID']=$school_years[START_DATE];
                $school_years_data[$i]['VALUE']=$school_years[START_DATE]."-".$school_years['END_DATE'];
                $i++;
            }
        }
        else if($school_years_RET1['END_DATE']==$school_years_RET1['START_DATE'])
        {
            if(UserStudentIDWs()=='')
            {
            $stu_ID=DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));    
            $stu_ID=$stu_ID[1]['STUDENT_ID'];
            }
            else
            $stu_ID=UserStudentIDWs();    
            $school_years_RET=DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=".$stu_ID." AND sy.SCHOOL_ID=".UserSchool()." "));

        }
*/
        $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='".UserWs('STAFF_ID')."' AND se.SYEAR=".UserSyear()." AND se.STUDENT_ID=sju.STUDENT_ID AND (('".DBDate('mysql')."' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '".DBDate('mysql')."'>=se.START_DATE)"));
        if(!UserStudentIDWs())
            $_SESSION['student_id'] = $RET[1]['STUDENT_ID'];
        if(count($RET)>0)
        {
            foreach($RET as $student)
            {

                $student_data[$s]['ID']=$student['STUDENT_ID'];
                $student_data[$s]['VALUE']=$student['FULL_NAME'];
                $s++;
                if(UserStudentIDWs()==$student['STUDENT_ID'])
                    $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
            }
        }

        if(!UserMP())
            $_SESSION['UserMP'] = GetCurrentMPWs('QTR',DBDate(), UserSyear(), UserSchool());

        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
        if(!isset($_SESSION['UserMP']))
            $_SESSION['UserMP'] = GetCurrentMPWs('QTR',DBDate(), UserSyear(), UserSchool());

        if(!$RET)
        {
            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
            if(!isset($_SESSION['UserMP']))
                $_SESSION['UserMP'] = GetCurrentMPWs('SEM',DBDate(), UserSyear(), UserSchool());
        }

        if(!$RET)
        {
            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));
            if(!isset($_SESSION['UserMP']))
                $_SESSION['UserMP'] = GetCurrentMPWs('FY',DBDate(), UserSyear(), UserSchool());
        }

        if(count($RET)>0)
        {
            if(!UserMP())
                $_SESSION['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
            foreach($RET as $quarter)
            {
                $mp_data[$m]['ID']=$quarter['MARKING_PERIOD_ID'];
                $mp_data[$m]['TITLE']=$quarter['TITLE'];
                $m++;
            }
            $allMP='QTR';
        }

        $data['SCHOOL_ID']=UserSchool();
        $data['SYEAR']=UserSyear();
        $data['Schoolyear_list']=$school_years_data; 
        $data['UserMP']=UserMP();           
        $data['marking_period_list'] = $mp_data;
        $data['marking_period_type'] = $allMP;
        if(count($school_years_data)>0 || count($mp_data) > 0 || count($allMP) > 0 || UserSchool() != '' || UserSyear() != '' || UserMP() != '')
            $data['success'] = 1;
        else 
            $data['success'] = 0;
    }
    else 
    {
       $data = array('student_data' => '', 'success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('student_data' => '', 'success' => 0, 'msg' => 'Not authenticated user');
}

echo json_encode($data);