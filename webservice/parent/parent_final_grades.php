<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/function.php';
include '../function/app_functions.php';
header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];
$_SESSION['PROFILE_ID'] = $_REQUEST['profile_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $data['selected_student']=$_SESSION['STUDENT_ID'] =$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];

        $mps_RET = DBGet(DBQuery('SELECT SEMESTER_ID,MARKING_PERIOD_ID,SHORT_NAME,TITLE FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'),array(),array('SEMESTER_ID'));
        $MP_TYPE='QTR';
        if(!$mps_RET)
        {
            $MP_TYPE='SEM';
            $mps_RET = DBGet(DBQuery('SELECT YEAR_ID,MARKING_PERIOD_ID,SHORT_NAME,TITLE FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'),array(),array('YEAR_ID'));
        }

        if(!$mps_RET)
        {
            $MP_TYPE='FY';
            $mps_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SHORT_NAME,TITLE FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'),array(),array('MARKING_PERIOD_ID'));
        }
        $m = 0;
        foreach($mps_RET as $sem=>$quarters)
        {
                foreach($quarters as $qtr)
                {
                    $pro = GetChildrenMPWs('PRO',$qtr['MARKING_PERIOD_ID']);
                    if($pro)
                    {
                        $pros = explode(',',str_replace("'",'',$pro));
                        foreach($pros as $pro)
                        {
                            if(GetMP($pro,'DOES_GRADES',$_REQUEST['syear'],$_REQUEST['school_id'])=='Y')
                            {
                                $mp_data[$m]['TITLE'] = GetMP($pro,'TITLE',$_REQUEST['syear'],$_REQUEST['school_id']);
                                $mp_data[$m]['VALUE'] = $pro;
                                $m++;
                            }
                        }
                    }
                    $mp_data[$m]['TITLE'] = $qtr['TITLE'];
                    $mp_data[$m]['VALUE'] = $qtr['MARKING_PERIOD_ID'];
                    $m++;
                }

                if(GetMP($sem,'DOES_EXAM',$_REQUEST['syear'],$_REQUEST['school_id'])=='Y')
                {
                    $mp_data[$m]['TITLE'] = GetMP($sem,'TITLE',$_REQUEST['syear'],$_REQUEST['school_id']).' Exam';
                    $mp_data[$m]['VALUE'] = 'E'.$sem;
                    $m++;
                }
                if(GetMP($sem,'DOES_GRADES',$_REQUEST['syear'],$_REQUEST['school_id'])=='Y' && $sem != $quarters[1]['MARKING_PERIOD_ID'])
                {
                    $mp_data[$m]['TITLE'] = GetMP($sem,'TITLE',$_REQUEST['syear'],$_REQUEST['school_id']);
                    $mp_data[$m]['VALUE'] = $sem;
                    $m++;
                }

        }

        if($sem)
        {
                $fy = GetParentMP('FY',$sem);
                if(GetMP($fy,'DOES_EXAM',$_REQUEST['syear'],$_REQUEST['school_id'])=='Y')
                {
                    $mp_data[$m]['TITLE'] = GetMP($fy,'TITLE',$_REQUEST['syear'],$_REQUEST['school_id']).' Exam';
                    $mp_data[$m]['VALUE'] = 'E'.$fy;
                    $m++;
                }
                if(GetMP($fy,'DOES_GRADES',$_REQUEST['syear'],$_REQUEST['school_id'])=='Y')
                {
                    $mp_data[$m]['TITLE'] = GetMP($fy,'TITLE',$_REQUEST['syear'],$_REQUEST['school_id']);
                    $mp_data[$m]['VALUE'] = $fy;
                    $m++;
                }
        }
        $grades_RET = array();
        $mp_list = '\''.$_REQUEST['mp_id'].'\'';
        $last_mp = $_REQUEST['mp_id'];
        $st_list = '\''.$student_id.'\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN ('.$st_list.')';

        $extra['SELECT'] .= ',rpg.TITLE as GRADE_TITLE,sg1.GRADE_PERCENT,sg1.COMMENT as COMMENT_TITLE,sg1.STUDENT_ID,sg1.COURSE_PERIOD_ID,sg1.MARKING_PERIOD_ID,c.TITLE as COURSE_TITLE,rc_cp.TITLE AS TEACHER,sp.SORT_ORDER';
        if($_REQUEST['elements']['period_absences']=='Y')
                $extra['SELECT'] .= ',cpv.DOES_ATTENDANCE,
                                (SELECT count(*) FROM attendance_period ap,attendance_codes ac
                                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'A\' AND ap.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND ap.STUDENT_ID=ssm.STUDENT_ID) AS YTD_ABSENCES,
                                (SELECT count(*) FROM attendance_period ap,attendance_codes ac
                                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'A\' AND ap.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND sg1.MARKING_PERIOD_ID=ap.MARKING_PERIOD_ID AND ap.STUDENT_ID=ssm.STUDENT_ID) AS MP_ABSENCES';
        if($_REQUEST['elements']['comments']=='Y')
                $extra['SELECT'] .= ',sg1.MARKING_PERIOD_ID AS COMMENTS_RET';
        $extra['FROM'] .= ',student_report_card_grades sg1 LEFT OUTER JOIN report_card_grades rpg ON (rpg.ID=sg1.REPORT_CARD_GRADE_ID),
                                        course_periods rc_cp,course_period_var cpv,courses c,school_periods sp';
        $extra['WHERE'] .= ' AND sg1.MARKING_PERIOD_ID IN ('.$mp_list.')
                                        AND rc_cp.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID=rc_cp.COURSE_PERIOD_ID AND c.COURSE_ID = rc_cp.COURSE_ID AND sg1.STUDENT_ID=ssm.STUDENT_ID AND sp.PERIOD_ID=cpv.PERIOD_ID';
        $extra['ORDER'] .= ',sp.SORT_ORDER,c.TITLE';
        $extra['functions']['TEACHER'] = '_makeTeacher';
        $extra['functions']['COMMENTS_RET'] = '_makeComments';
        $extra['group']	= array('STUDENT_ID');


        $extra['group'][] = 'COURSE_PERIOD_ID';
        $extra['group'][] = 'MARKING_PERIOD_ID';

        $RET = GetStuListWs($extra);

        if(count($RET)>0)
        {
            $i = 0;
            foreach($RET as $student_id=>$course_periods)
            {
                $course_period_id = key($course_periods);
                foreach($course_periods as $course_period_id=>$mps)
                {
                    $grades_RET[$i]['COURSE_TITLE'] = $mps[key($mps)][1]['COURSE_TITLE'];
                    $grades_RET[$i]['TEACHER'] = $mps[$last_mp][1]['TEACHER'];
                    $grades_RET[$i]['GRADE_TITLE'] = $mps[$last_mp][1]['GRADE_TITLE'];
                    $grades_RET[$i]['GRADE_PERCENT'] = $mps[$last_mp][1]['GRADE_PERCENT'];
                    $sep = '';
                    foreach($mps[$last_mp][1]['COMMENTS_RET'] as $comment)
                    {
                        $grades_RET[$i]['COMMENT'] .= $sep.$comments_RET[$comment['REPORT_CARD_COMMENT_ID']][1]['SORT_ORDER'];
                        if($comment['COMMENT'])
                            $grades_RET[$i]['COMMENT'] .= '('.($comment['COMMENT']!=' '?$comment['COMMENT']:'&middot;').')';
                        $sep = ', ';
                    }
                    if($mps[$last_mp][1]['COMMENT_TITLE'])
                        $grades_RET[$i]['COMMENT'] .= $sep.$mps[$last_mp][1]['COMMENT_TITLE'];
                $i++;
                }
            }
        }

        if(count($grades_RET)>0)
        {
            $data_success = 1;
            $data_msg = "Nil";
        }
        else 
        {
            $data_success = 0;
            $data_msg = "No Students were found.";
        }
        if(count($mp_data)>0)
        {
            $mp_success = 1;
        }
        else 
        {
            $mp_success = 0;
        }
        function _makeTeacher($teacher,$column)
        {
            return substr($teacher,strrpos(str_replace(' - ',' ^ ',$teacher),'^')+2);
        }

        function _makeComments($value,$column)
        {
            global $THIS_RET;

            return DBGet(DBQuery('SELECT COURSE_PERIOD_ID,REPORT_CARD_COMMENT_ID,COMMENT,(SELECT SORT_ORDER FROM report_card_comments WHERE REPORT_CARD_COMMENT_ID=ID) AS SORT_ORDER FROM student_report_card_comments WHERE STUDENT_ID=\''.$THIS_RET['STUDENT_ID'].'\' AND COURSE_PERIOD_ID=\''.$THIS_RET['COURSE_PERIOD_ID'].'\' AND MARKING_PERIOD_ID=\''.$value.'\' ORDER BY SORT_ORDER'));
        }

        $data['mp_data'] = $mp_data;
        $data['mp_success'] = $mp_success;
        $data['selected_mp'] = $last_mp;
        $data['final_grades'] = $grades_RET;
        $data['final_grades_success'] = $data_success;
        $data['final_grades_msg'] = $data_msg;
        $data['success'] = ($data_success!=0 || $mp_success!=0)?1:0;
        $data['msg'] = ($data_success!=0 || $mp_success!=0)?"Nil":"No data found";
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

