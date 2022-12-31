<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/function.php';
include 'function/app_functions.php';
header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
if($_REQUEST['view_type']=='details')
{
    $mp_id = $_REQUEST['mp_id'];
    $mp_type = $_REQUEST['mp_type'];
$marking_period_data = array();
    if($mp_type == 'school_years')
    {
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$mp_id.'\' ORDER BY SORT_ORDER';
        $QI = DBQuery($sql);
        $fy_RET = DBGet($QI);
        
        if(count($fy_RET)>0)
        {
            foreach($fy_RET as $fy)
            {
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND YEAR_ID=\''.$fy['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $sem_RET = DBGet($QI);
                if(count($sem_RET)>0)
                {
                    foreach($sem_RET as $sem)
                    {
                        $sem['mp_type']='school_semesters';
                        $fy['child'][]=$sem;
                    }
                    $fy['child_success']=1;
                    $fy['child_msg']='Nil';
                }
                else 
                {
                    $fy['child'] = array();
                    $fy['child_success']=0;
                    $fy['child_msg']='No school semester found';
                }

            }
            $fy['mp_type']='school_years';
            $marking_period_data[]=$fy;
        }
        $data['parent_info']='';
        $data['parent_success']=0;
        $data['marking_period_data'] = $marking_period_data;
        if(count($marking_period_data)>0)
        {
            $data['success']=1;
            $data['msg'] = 'Nil';
        }
        else 
        {
            $data['success']=0;
            $data['msg'] = 'No data found';
        }
    }
    elseif($mp_type == 'school_semesters')
    {
        $marking_period_data = array();
//        $parent_info = array();
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,YEAR_ID FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$mp_id.'\' ORDER BY SORT_ORDER';
        $QI = DBQuery($sql);
        $sem_RET = DBGet($QI);
        if(count($sem_RET)>0)
        {
            foreach($sem_RET as $sem)
            {
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND SEMESTER_ID=\''.$sem['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $qtr_RET = DBGet($QI);
                if(count($qtr_RET)>0)
                {
                    foreach($qtr_RET as $qtr)
                    {
                        $qtr['mp_type']='school_quarters';
                        $sem['child'][]=$qtr;
                    }
                    $sem['child_success']=1;
                    $sem['child_msg']='Nil';
                }
                else 
                {
                    $sem['child'] = '';
                    $sem['child_success']=0;
                    $sem['child_msg']='No school quarter found'; 
                }
                $sem['mp_type']='school_semesters';
                $marking_period_data[]=$sem;
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$sem['YEAR_ID'].'\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $fy_RET = DBGet($QI);
                if(count($fy_RET)>0)
                {
                foreach($fy_RET as $fy)
                {
                        $fy['mp_type']='school_years';
                    $data['parent_info'][]=$fy;
                }
                    $data['parent_success']=1;
            }
        }
        }
        $data['marking_period_data'] = $marking_period_data;
        if(count($marking_period_data)>0)
        {
            $data['success']=1;
            $data['msg'] = 'Nil';
        }
        else 
        {
            $data['success']=0;
            $data['msg'] = 'No data found';
        }
    }
    elseif($mp_type == 'school_quarters')
    {
        $marking_period_data = array();
        $parent_info = array();
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,SEMESTER_ID FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$mp_id.'\' ORDER BY SORT_ORDER';
        $QI = DBQuery($sql);
        $qtr_RET = DBGet($QI);
        if(count($qtr_RET)>0)
        {
            foreach($qtr_RET as $qtr)
            {
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND QUARTER_ID=\''.$qtr['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $pro_RET = DBGet($QI);
                if(count($pro_RET)>0)
                {
                    foreach($pro_RET as $pro)
                    {
                        $pro['mp_type']='school_progress_periods';
                        $qtr['child'][]=$pro;
                    }
                    $qtr['child_success']=1;
                    $qtr['child_msg']='Nil';
                }
                else 
                {
                    $qtr['child']=array();
                    $qtr['child_success']=0;
                    $qtr['child_msg']='No school progress period found';
                }
                $qtr['mp_type']='school_quarters';
                $marking_period_data[]=$qtr;
                $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$qtr['SEMESTER_ID'].'\' ORDER BY SORT_ORDER';
                $QI = DBQuery($sql);
                $fy_RET = DBGet($QI);
                if(count($fy_RET)>0)
                {
                foreach($fy_RET as $fy)
                {
                        $fy['mp_type']='school_semesters';
                    $data['parent_info'][]=$fy;
                }
                    $data['parent_success']=1;
            }
        }
        }
//        $data['parent_info']=$parent_info;
        $data['marking_period_data'] = $marking_period_data;
        if(count($marking_period_data)>0)
        {
            $data['success']=1;
            $data['msg'] = 'Nil';
        }
        else 
        {
            $data['success']=0;
            $data['msg'] = 'No data found';
        }
    }
    elseif($mp_type == 'school_progress_periods')
    {
        $marking_period_data = array();
        $parent_info = array();
        $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,QUARTER_ID FROM school_progress_periods WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$mp_id.'\' ORDER BY SORT_ORDER';
        $QI = DBQuery($sql);
        $prog_RET = DBGet($QI);
        foreach($prog_RET as $prog)
        {
            $prog['child']=array();
            $prog['child_success']=0;
            $prog['child_msg']='No school progress period found';
            $marking_period_data[]=$prog;
            $sql = 'SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND MARKING_PERIOD_ID=\''.$prog['QUARTER_ID'].'\' ORDER BY SORT_ORDER';
            $QI = DBQuery($sql);
            $fy_RET = DBGet($QI);
            if(count($fy_RET)>0)
            {
            foreach($fy_RET as $fy)
            {
                    $fy['mp_type']='school_quarters';
                $data['parent_info'][]=$fy;
            }
                $data['parent_success']=1;
        }
    }
    }
    $data['marking_period_data'] = $marking_period_data;
    if(count($marking_period_data)>0)
    {
        $data['success']=1;
        $data['msg'] = 'Nil';
    }
    else 
    {
        $data['success']=0;
        $data['msg'] = 'No data found';
    }
}
else 
{
    $marking_period_data = array();
    $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY SORT_ORDER';
    $QI = DBQuery($sql);
    $fy_RET = DBGet($QI);

    if(count($fy_RET)>0)
    {
        foreach($fy_RET as $fy)
        {
            $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND YEAR_ID=\''.$fy['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
            $QI = DBQuery($sql);
            $sem_RET = DBGet($QI);
            if(count($sem_RET)>0)
            {
                foreach($sem_RET as $sem)
                {
                    $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND SEMESTER_ID=\''.$sem['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
                    $QI = DBQuery($sql);
                    $qtr_RET = DBGet($QI);
                    if(count($qtr_RET)>0)
                    {
                        foreach($qtr_RET as $qtr)
                        {
                            $sql = 'SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND QUARTER_ID=\''.$qtr['MARKING_PERIOD_ID'].'\' ORDER BY SORT_ORDER';
                            $QI = DBQuery($sql);
                            $pro_RET = DBGet($QI);
                            if(count($pro_RET)>0)
                            {
                                foreach($pro_RET as $pro)
                                {
                                        $pro['mp_type']='school_progress_periods';
                                    $qtr['progress_periods'][]=$pro;
                                }
                                $qtr['prog_success']=1;
                                $qtr['prog_msg']='Nil';
                            }
                            else 
                            {
                                $qtr['progress_periods'] = array();
                                $qtr['prog_success']=0;
                                $qtr['prog_msg']='No school progress period found';
                            }
                                $qtr['mp_type']='school_quarters';
                            $sem['quarter'][]=$qtr;
                        }
                        $sem['quat_success']=1;
                        $sem['quat_msg']='Nil';
                    }
                    else 
                    {
                        $sem['quarter'] = array();
                        $sem['quat_success']=0;
                        $sem['quat_msg']='No school quarter found'; 
                    }
                        $sem['mp_type']='school_semesters';
                    $fy['semester'][]=$sem;
                }
                $fy['sem_success']=1;
                $fy['sem_msg']='Nil';
            }
            else 
            {
                $fy['semester'] = array();
                $fy['sem_success']=0;
                $fy['sem_msg']='No school semester found';
            }

        }
            $fy['mp_type']='school_years';
        $marking_period_data[]=$fy;
    //    $marking_period_data['prog_success']=1;
    //    $marking_period_data['prog_msg']='Nil';
    }
    else 
    {
        $marking_period_data['prog_success']=0;
        $marking_period_data['prog_msg']='No school year found';
    }

    $data['marking_periods']=$marking_period_data;
    if(count($marking_period_data)>0)
    {
        $data['success']=1;
        $data['msg']='Nil';
    }
    else 
    {
        $data['success']=0;
        $data['msg']='No data found';
    }
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
