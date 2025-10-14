<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

include '../function/ProperDateFnc.php';

header('Content-Type: application/json');

$_SESSION['PROFILE_ID'] = 3;
$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$student_id && $auth_data['user_profile']=='student')
    {

        $school_years_RET1=DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=".UserSchool()." AND SYEAR=".UserSyear()));
        $school_years_RET1=$school_years_RET1[1];
        $school_years_RET1['START_DATE']=explode("-",$school_years_RET1['START_DATE']);
        $school_years_RET1['START_DATE']=$school_years_RET1['START_DATE'][0];
        $school_years_RET1['END_DATE']=explode("-",$school_years_RET1['END_DATE']);
        $school_years_RET1['END_DATE']=$school_years_RET1['END_DATE'][0];
        $i=$s=$m=0;
        if($school_years_RET1['END_DATE']>$school_years_RET1['START_DATE'])
        {
            $stu_ID=$student_id;    
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
            $stu_ID=$student_id;    
            $school_years_RET=DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=".$stu_ID." AND sy.SCHOOL_ID=".UserSchool()." "));
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
       $data = array('success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}

echo json_encode($data);