<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $data['selected_student']=$_SESSION['student_id'] = $student_id = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
        $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
        $action_type = $_REQUEST['action_type'];
                //$MP_CHK_RET=DBGet(DBQuery('SELECT MARKING_PERIOD_ID,MP_TYPE FROM marking_periods WHERE SYEAR='.$_REQUEST['syear'].' AND SCHOOL_ID = '.$_REQUEST['school_id']),array(),array('MARKING_PERIOD_ID'));
                //print_r($MP_CHK_RET);
                //foreach($MP_CHK_RET AS $sy_mp_id=>$mpdata)
                //{
                //    $mp_arr[]=$sy_mp_id;
                //    $mp_data_arr[]=$mpdata[1];
                //}
                //if(!in_array($mp_id,$mp_arr))
                //{
                //    foreach($mp_data_arr AS $mpinfo)
                //    {
                //        if($mpinfo['MP_TYPE']=='year')
                //        {
                //            $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id']=$mpinfo['MARKING_PERIOD_ID'];
                //        }
                //    }
                //}
        $do_stats = true;
        $courses_success = '';
        $LO_ret = array();
        $MP_TYPE_RET=DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID='.UserMP().' LIMIT 1'));
        $MP_TYPE=$MP_TYPE_RET[1]['MP_TYPE'];
        if($MP_TYPE=='year'){
        $MP_TYPE='FY';
        }else if($MP_TYPE=='semester'){$MP_TYPE='SEM';
        }else if($MP_TYPE=='quarter'){$MP_TYPE='QTR';
        }else{$MP_TYPE='';
        }

        if($action_type == 'totals')
        {
            $rank_RET=DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\''.  UserSchool().'\' AND program=\'class_rank\' AND title=\'display\' LIMIT 0, 1'));
            $rank=$rank_RET[1];
            $display_rank=$rank['VALUE'];

            $courses_RET = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,courses c WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND ((cp.MARKING_PERIOD_ID IN ('.GetAllMPWs($MP_TYPE,UserMP(),UserSyear(),UserSchool()).') OR cp.MARKING_PERIOD_ID IS NULL) AND ((\''.date('Y-m-d',strtotime(DBDate())).'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.date('Y-m-d',strtotime(DBDate())).'\'>=s.START_DATE AND s.END_DATE IS NULL))) AND s.STUDENT_ID=\''.$student_id.'\'  AND c.COURSE_ID=cp.COURSE_ID ORDER BY cp.COURSE_ID'),array(),array('COURSE_PERIOD_ID'));

            if(count($courses_RET)>0)
            {
            //    $LO_ret = array(0=>array());

                foreach($courses_RET as $course)
                {

                    $mp = GetAllMP('QTR',UserMP());

                    if(!isset($mp))
                      $mp = GetAllMP('SEM',UserMP());

                    if(!isset($mp))
                      $mp = GetAllMP('FY',UserMP());


                    $course = $course[1];
                    $_SESSION['STAFF_ID'] =$staff_id = $course['STAFF_ID'];
                    $course_id = $course['COURSE_ID'];
                    $course_period_id = $course['COURSE_PERIOD_ID'];
                    $course_title = $course['TITLE'];


                    $assignments_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID,TITLE,POINTS FROM gradebook_assignments WHERE STAFF_ID=\''.$staff_id.'\' AND (COURSE_ID=\''.$course_id.'\' OR COURSE_PERIOD_ID=\''.$course_period_id.'\') AND MARKING_PERIOD_ID IN ('.$mp.') ORDER BY DUE_DATE DESC,ASSIGNMENT_ID'));

                    if(!$programconfig[$staff_id])
                    {
                            $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\''),array(),array('TITLE'));
                            if(count($config_RET))
                                    foreach($config_RET as $title=>$value)
                                            $programconfig[$staff_id][$title] = $value[1]['VALUE'];
                            else
                                    $programconfig[$staff_id] = true;
                    }

                    //$programconfig[$staff_id]['WEIGHT'];
                    if($programconfig[$staff_id]['WEIGHT'] == 'Y')
                    {

                            $mp = GetAllMP('QTR',UserMP());

                            if(!isset($mp))
                              $mp = GetAllMP('SEM',UserMP());

                            if(!isset($mp))
                              $mp = GetAllMP('FY',UserMP());

                            $points_RET1 = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID, gt.ASSIGNMENT_TYPE_ID, sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL, gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\''.$course_period_id.'\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.')) LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID),gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'),array(),array('STUDENT_ID'));
                            $points_RET = DBGet(DBQuery('SELECT gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  and gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));

                            $points_RET_all1 = DBGet(DBQuery('SELECT gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  or gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));
        //                    $points_RET1 = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID, gt.ASSIGNMENT_TYPE_ID, sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL, gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\''.$course_period_id.'\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.')) LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID),gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'),array(),array('STUDENT_ID'));
        //                    $points_RET = DBGet(DBQuery('SELECT gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  and gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));
        //                    $points_RET_all1 = DBGet(DBQuery('SELECT gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  or gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));
                            if($do_stats)
                                $all_RET = DBGet(DBQuery('SELECT gg.STUDENT_ID, gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT FROM gradebook_grades gg,gradebook_assignments ga LEFT OUTER JOIN gradebook_grades g ON (g.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND g.STUDENT_ID=\''.$student_id.'\' AND g.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ga.ASSIGNMENT_ID=gg.ASSIGNMENT_ID AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND gt.COURSE_ID=\''.$course_id.'\' AND (ga.ASSIGNED_DATE IS NOT NULL   OR gg.POINTS IS NOT NULL) GROUP BY gg.STUDENT_ID,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'),array(),array('STUDENT_ID'));
                    }
                    else
                    {   

                        $mp = GetAllMP('QTR',UserMP());

                        if(!isset($mp))
                          $mp = GetAllMP('SEM',UserMP());

                        if(!isset($mp))
                          $mp = GetAllMP('FY',UserMP());
                        $points_RET = DBGet(DBQuery('SELECT \'-1\' AS ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID) WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND (ga.ASSIGNED_DATE IS NOT NULL ) GROUP BY  FINAL_GRADE_PERCENT'));
                        $points_RET_all1 = DBGet(DBQuery('SELECT      gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  or gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));
                                     if($do_stats)

                                      $all_RET = DBGet(DBQuery('SELECT gg.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT FROM gradebook_grades gg,gradebook_assignments ga LEFT OUTER JOIN gradebook_grades g ON (g.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND g.STUDENT_ID=\''.$student_id.'\' AND g.ASSIGNMENT_ID=ga.ASSIGNMENT_ID)
                                                    WHERE  ga.ASSIGNMENT_ID=gg.ASSIGNMENT_ID AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND (ga.ASSIGNED_DATE IS NOT NULL OR gg.POINTS IS NOT NULL) GROUP BY gg.STUDENT_ID, FINAL_GRADE_PERCENT'),array(),array('STUDENT_ID'));
                        //$points_RET = DBGet(DBQuery('SELECT \'-1\' AS ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID) WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND (ga.ASSIGNED_DATE IS NOT NULL) GROUP BY  FINAL_GRADE_PERCENT'));
                        //$points_RET_all1 = DBGet(DBQuery('SELECT gt.ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,    gt.FINAL_GRADE_PERCENT,sum('.db_case(array('gg.POINTS',"''","1","0")).') AS UNGRADED FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg ON (gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gg.STUDENT_ID=\''.$student_id.'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID),gradebook_assignment_types gt WHERE (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NOT NULL )  or gg.POINTS IS NOT NULL) GROUP BY gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'));
                        //if($do_stats)
                        //    $all_RET = DBGet(DBQuery('SELECT gg.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,sum('.db_case(array('gg.POINTS',"'-1'","'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT FROM gradebook_grades gg,gradebook_assignments ga LEFT OUTER JOIN gradebook_grades g ON (g.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND g.STUDENT_ID=\''.$student_id.'\' AND g.ASSIGNMENT_ID=ga.ASSIGNMENT_ID)
                        //                            WHERE  ga.ASSIGNMENT_ID=gg.ASSIGNMENT_ID AND ga.MARKING_PERIOD_ID IN ('.$mp.') AND gg.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND (ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND (ga.ASSIGNED_DATE IS NOT NULL OR gg.POINTS IS NOT NULL) GROUP BY gg.STUDENT_ID, FINAL_GRADE_PERCENT'),array(),array('STUDENT_ID'));
                    }


                    $Class_Rank = DBGet(DBQuery('SELECT  COUNT(ga.STUDENT_ID) AS TOTAL_STUDENT FROM gradebook_grades ga WHERE ga.COURSE_PERIOD_ID=\''.$course_period_id.'\'   GROUP BY ga.STUDENT_ID'));
                    $Class_Rank = DBGet(DBQuery(' SELECT FOUND_ROWS() as TOTAL_STUDENT')) ;

        //            if(isset($points_RET) && count($points_RET)>0)
        //            {
                            $total = $total_percent = 0;
                            $ungraded = 0;
                            if(empty($points_RET))
                            {
                               $total ='Not graded';
                               $total_percent=0;
                            }
                            else {
                            foreach($points_RET as $partial_points)
                            {
                                if($partial_points['PARTIAL_TOTAL']!=0)
                                {
                                                                        $total += $partial_points['PARTIAL_POINTS'];
                                                                                    $total_percent += $partial_points['PARTIAL_TOTAL'];

                                }
                                                else
                                                {
                                                   $total ='Not graded';
                                                    $total_percent=0; 
                                                }
                                        }
                                }

                            foreach($points_RET_all1 as $partial_points1)
                            {
                                $ungraded += $partial_points1['UNGRADED'];
                            }

                            if($total_percent!=0)
                                    $total /= $total_percent;
                            $percent = $total;


                            if($do_stats)
                            {
                                unset($bargraph1);
                                unset($bargraph2);
                                $min_percent = $max_percent = $percent;
                                $avg_percent = 0;
                                $lower = $higher = 0;
                                foreach($all_RET as $xstudent_id=>$student)
                                {
                                        if($student['STUDENT_ID'])
                                        $count++;
                                        $total = $total_percent = 0;
                                        foreach($student as $partial_points)
                                                if($partial_points['PARTIAL_TOTAL']!=0)
                                                {
                                                    $total += $partial_points['PARTIAL_POINTS'];
                                                    $total_percent += $partial_points['PARTIAL_TOTAL'];

                                                }

                                        if($total_percent!=0)
                                                 $total /= $total_percent;
                                        $Rank_Pos[] = number_format(100*$total,1) ;

                                             }
                                if($total<$min_percent)
                                        $min_percent = $total;
                                if($total>$max_percent)
                                        $max_percent = $total;
                                $avg_percent += $total;
                                if($xstudent_id!==$student_id)
                                    if($total>$percent)
                                        $higher++;
                                    else
                                        $lower++;
                            }


                            $avg_percent /= count($all_RET);

                            $scale = $max_percent>1?$max_percent:1;
                            $w1 = round(100*$min_percent/$scale);
                            if($percent<$avg_percent)
                            {
                                    $w2 = round(100*($percent-$min_percent)/$scale); $c2 = '#ff0000';
                                    $w4 = round(100*($max_percent-$avg_percent)/$scale); $c4 = '#00ff00';
                            }
                            else
                            {
                                    $w2 = round(100*($avg_percent-$min_percent)/$scale); $c2 = '#00ff00';
                                    $w4 = round(100*($max_percent-$percent)/$scale); $c4 = '#ff0000';
                            }
                             $w5 = round(100*(1.0-$max_percent/$scale));

                            $w3 = 100-$w1-$w2-$w4-$w5;


                            rsort($Rank_Pos);
                            foreach ($Rank_Pos as $key => $val) {
                            {      
                               if (number_format(100*$percent,1)==$val)
                              $rank = $key+1;

                            }

                            $highrange = max($Rank_Pos);
                            $lowrange = min($Rank_Pos);
                            $bargraph1 =$lowrange." - ".$highrange;

                            $scale = $lower+$higher+1;
                            $w1 = round(100*$lower/$scale);
                            $w3 = round(100*$higher/$scale);
                            $w2 = 100-$w1-$w3;

                          if ($rank)
                          $bargraph2 = $rank . " out of " .$Class_Rank[1]['TOTAL_STUDENT'];

                        }

                        if ($percent=='Not graded') 
                          $LO_ret[] = array('ID'=>$course_period_id,'TITLE'=>$course['COURSE_TITLE'],'TEACHER'=>substr($course_title,strrpos(str_replace(' - ',' ^ ',$course_title),'^')+2),'PERCENT'=>_makeLetterGrade($percent,$course_period_id,$staff_id,"%").'%','GRADE' =>'Not graded','UNGRADED'=>$ungraded)+($do_stats?array('BAR1'=>$bargraph1,'BAR2'=>$bargraph2):array());
                        else 
                          $LO_ret[] = array('ID'=>$course_period_id,'TITLE'=>$course['COURSE_TITLE'],'TEACHER'=>substr($course_title,strrpos(str_replace(' - ',' ^ ',$course_title),'^')+2),'PERCENT'=>_makeLetterGrade($percent,$course_period_id,$staff_id,"%").'%','GRADE' =>_makeLetterGrade($percent,$course_period_id,$staff_id),'UNGRADED'=>$ungraded)+($do_stats?array('BAR1'=>$bargraph1,'BAR2'=>$bargraph2):array());

                      unset($Rank_Pos);
        //            }

        //            }

                }
                $data['gradebook_grade_data'] = $LO_ret;
                if(count($LO_ret)>0)
                {
                    $data['success'] = 1;
                    $data['msg'] = 'Nil';
                }
                else
                {
                    $data['success'] = 0;
                    $data['msg'] = 'No data found';
                }
            }
            else
            {
                $LO_ret = array();
                $data['msg'] ='There are no grades available for this student.';
                $data['success'] = 0;
            }
        }
        elseif($action_type == 'expanded')
        {
            $grade_data = array();
            if($_REQUEST['modfun']=='assgn_detail')
            {
                $assignment_data = array();
                $assignments_RET = DBGet(DBQuery('SELECT ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS ,gt.title as assignment_type
                                                           FROM gradebook_assignments ga, gradebook_assignment_types gt
                                              where assignment_id =\''.$_REQUEST['assignment_id'].'\' and gt.assignment_type_id=ga.assignment_type_id'));
                foreach($assignments_RET as $asgmnt)
                {
                    $assignment_data[]=$asgmnt;
                }

                if(count($assignment_data)>0)
                {
                    $asgmnt_success = 1;
                    $asgmt_msg = 'Nil';
                }
                else
                {
                    $asgmnt_success = 0;
                    $asgmt_msg = 'No data found';
                }
                $data['assignment_data'] = $assignment_data;
                $data['asgmnt_success'] = $asgmnt_success;
                $data['asgmt_msg'] = $asgmt_msg;
        //        $val1 = '<div>';
        //        $val1 .= '<center > <strong>Assignment Details</center><br><br>';
        //        $val1 .= '<table width="95%" cellpadding="2" cellspacing="2" border="0" align="center">';
        //        $val1 .= '<tr><td valign=top > <strong>Title</strong></td><td valign=top>:</td><td valign=top >'.$assignments_RET[1]['TITLE'].'</td>';
        //        $val1 .= '<td valign=top > <strong>Description</strong></td><td valign=top >:</td><td valign=top width=55% >'.$assignments_RET[1]['DESCRIPTION'].'</td></tr>';
        //        $val1 .= '<tr><td valign=top > <strong>Assignement Type</strong></td><td valign=top >:</td><td valign=top>'.$assignments_RET[1]['ASSIGNMENT_TYPE'].'</td>';
        //        $val1 .= '<td valign=top> <strong>Points</strong></td><td valign=top>:</td><td valign=top>'.$assignments_RET[1]['POINTS'].'</td></tr>';
        //        $val1 .= '<tr><td valign=top>';
        //        $val1 .= '<strong>Assigned Date</strong>';
        //        $val1 .= '</td><td valign=top>:</td><td valign=top>'.$assignments_RET[1]['ASSIGNED_DATE'].'</td>';
        //        $val1 .= '<td valign=top> <strong>Due Date</strong></td><td valign=top>:</td><td valign=top>'.$assignments_RET[1]['DUE_DATE'].'</td></tr>';
        //        $val1 .= '</table>';
        //        $val1 .= '</div>';

            }

            $mp = GetAllMP('QTR',UserMP());
            if(!isset($mp))
              $mp = GetAllMP('SEM',UserMP());
            if(!isset($mp))
              $mp = GetAllMP('FY',UserMP());
            if($_REQUEST['id']=='all')
            {
                $courses_RET = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,courses c WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND s.MARKING_PERIOD_ID IN ('.$mp.') AND (\''.DBDate('mysql').'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.DBDate('mysql').'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\''.$student_id.'\' AND cp.GRADE_SCALE_ID IS NOT NULL AND c.COURSE_ID=cp.COURSE_ID ORDER BY cp.COURSE_ID'));
            }
            else 
            {
                $all_courses = array();
                $all_courses_RET = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,courses c WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND s.MARKING_PERIOD_ID IN ('.$mp.') AND (\''.DBDate('mysql').'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.DBDate('mysql').'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\''.$student_id.'\' AND cp.GRADE_SCALE_ID IS NOT NULL AND c.COURSE_ID=cp.COURSE_ID ORDER BY cp.COURSE_ID'));
                $courses_RET = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM course_periods cp,courses c WHERE cp.COURSE_PERIOD_ID=\''.$_REQUEST['id'].'\' AND c.COURSE_ID=cp.COURSE_ID'));
                foreach($all_courses_RET as $all_course)
                {
                    $all_courses[]=$all_course;
                }
                if(count($all_courses)>0)
                    $courses_success = 1;
                else 
                    $courses_success = 0;
                 $data['all_courses'] = $all_courses;
                 $data['courses_success'] = $courses_success;
                 $data['selected_cp'] = $_REQUEST['id'];
            }
            $i = 0;
            foreach($courses_RET as $course)
            {
                $staff_id = $course['STAFF_ID'];
                if(!$programconfig[$staff_id])
                {
                    $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\''),array(),array('TITLE'));
                    if(count($config_RET))
                            foreach($config_RET as $title=>$value)
                                    $programconfig[$staff_id][$title] = $value[1]['VALUE'];
                    else
                            $programconfig[$staff_id] = true;
                }

                $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.DESCRIPTION,gg.POINTS,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.DUE_DATE AS DUE ,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                       FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                      ON (gg.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\''.$student_id.'\'),gradebook_assignment_types at
                                                      WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\'
                                                       AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ((ga.ASSIGNED_DATE IS NOT NULL )
                                                      or gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'),array('TITLE'=>'_makeTipTitle','ASSIGNED_DATE'=>'ProperDate','DUE_DATE'=>'ProperDate'));

               $stu_enroll_date=DBGet(DBQuery( 'SELECT * FROM student_enrollment ssm WHERE STUDENT_ID=\''.$student_id.'\'  AND ssm.SYEAR=\''.UserSyear().'\' AND ((ssm.START_DATE IS NOT NULL AND \''.date('Y-m-d').'\'>=ssm.START_DATE) AND (\''.date('Y-m-d').'\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) '));
               $stu_enroll_date=$stu_enroll_date[1][START_DATE];
                   if(count($assignments_RET)>0)
                    {
        //                  if($_REQUEST['id']=='all')
        //                    {
        //
        //                      DrawHeader('<br><B>'.$course['COURSE_TITLE'].'</B> - '.substr($course['TITLE'],strrpos(str_replace(' - ',' ^ ',$course['TITLE']),'^')+2),"<A HREF=Modules.php?modname=$_REQUEST[modname]>Back to Totals</A>");
        //                    }
                           if($do_stats)
                            $all_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,gg.POINTS,min('.db_case(array('gg.POINTS',"'-1'",'ga.POINTS','gg.POINTS')).') AS MIN,max('.db_case(array('gg.POINTS',"'-1'",'0','gg.POINTS')).') AS MAX,'.db_case(array("sum(".db_case(array('gg.POINTS',"'-1'",'0','1')).")","'0'","'0'","sum(".db_case(array('gg.POINTS',"'-1'",'0','gg.POINTS')).') / sum('.db_case(array('gg.POINTS',"'-1'",'0','1')).")")).' AS AVG,sum(CASE WHEN gg.POINTS<=g.POINTS AND gg.STUDENT_ID!=g.STUDENT_ID THEN 1 ELSE 0 END) AS LOWER,sum(CASE WHEN gg.POINTS>g.POINTS THEN 1 ELSE 0 END) AS HIGHER FROM gradebook_grades gg,gradebook_assignments ga LEFT OUTER JOIN gradebook_grades g ON (g.COURSE_PERIOD_ID=\''.$course['COURSE_PERIOD_ID'].'\' AND g.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND g.STUDENT_ID=\''.$student_id.'\'),gradebook_assignment_types at WHERE (ga.COURSE_PERIOD_ID=\''.$course['COURSE_PERIOD_ID'].'\' OR ga.COURSE_ID=\''.$course['COURSE_ID'].'\' AND ga.STAFF_ID=\''.$staff_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ((ga.ASSIGNED_DATE IS NOT NULL )  OR g.POINTS IS NOT NULL) AND ga.POINTS!=\'0\' GROUP BY ga.ASSIGNMENT_ID'),array(),array('ASSIGNMENT_ID'));

        //                    if($display_rank=='Y')
        //                        $LO_columns = array('TITLE'=>'Title','CATEGORY'=>'Category','POINTS'=>'Points / Possible','PERCENT'=>'Percent','LETTER'=>'Letter','ASSIGNED_DATE'=>'Assigned Date','DUE_DATE'=>'Due Date')+($do_stats?array('BAR1'=>'Grade Range','BAR2'=>'Class Rank'):array());
        //                    else
        //                        $LO_columns = array('TITLE'=>'Title','CATEGORY'=>'Category','POINTS'=>'Points / Possible','PERCENT'=>'Percent','LETTER'=>'Letter','ASSIGNED_DATE'=>'Assigned Date','DUE_DATE'=>'Due Date')+($do_stats?array('BAR1'=>'Grade Range'):array());

                            $LO_ret = array();

                            foreach($assignments_RET as $assignment)
                            {

                              $days_left= floor((strtotime($assignment[DUE],0)-strtotime($stu_enroll_date,0))/86400);
                                if($days_left>=1)
                                {
                               if($do_stats)
                                    {
                                            unset($bargraph1);
                                            unset($bargraph2);
                                            if($all_RET[$assignment['ASSIGNMENT_ID']])
                                            {
                                                 $all = $all_RET[$assignment['ASSIGNMENT_ID']][1];
                                                    $all_RET1 = DBGet(DBQuery('SELECT g.ASSIGNMENT_ID,g.POINTS  FROM gradebook_grades g where g.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' '));
                                                $count_tot =0;
                                                foreach($all_RET1 as $all1)
                                                {
                                                   if($assignment['ASSIGNMENT_ID']==$all1['ASSIGNMENT_ID'])
                                                   { 
                                                    $assg_tot[]= $all1['POINTS'];
                                                    $count_tot++;
                                                     }
                                                }
                                                   rsort($assg_tot);
                                                   unset($ranknew);
                                                   unset($prev_val);
                                                   $k=0;
                                                   foreach ($assg_tot as $key => $val)
                                                    {if($prev_val!=$val) $k++;
                                                       "RankNew[" . $key . "] = " . $val . "\n";
                                                       if ($assignment['POINTS']==$val)
                                                       if($prev_val!=$val) $ranknew = $k; ;
                                                       $prev_val = $val;}
                                                   #}
                                                  unset($assg_tot);

                                                    $scale = $all['MAX']>$assignment['POINTS_POSSIBLE']?$all['MAX']:$assignment['POINTS_POSSIBLE'];
                                                    if ($ranknew && $assignment['POINTS']>0 )
                                                   $bargraph2 =$ranknew ." out of ". $count_tot;
                                                    if($assignment['POINTS']!='-1' && $assignment['POINTS']!='')
                                                    {

                                                            $w1 = round(100*$all['MIN']/$scale);
                                                            if($assignment['POINTS']<$all['AVG'])
                                                            {
                                                                    $w2 = round(100*($assignment['POINTS']-$all['MIN'])/$scale); $c2 = '#ff0000';
                                                                    $w4 = round(100*($all['MAX']-$all['AVG'])/$scale); $c4 = '#00ff00';
                                                            }
                                                            else
                                                            {
                                                                    $w2 = round(100*($all['AVG']-$all['MIN'])/$scale); $c2 = '#00ff00';
                                                                    $w4 = round(100*($all['MAX']-$assignment['POINTS'])/$scale); $c4 = '#ff0000';
                                                            }
                                                            $w5 = round(100*(1.0-$all['MAX']/$scale));
                                                            $w3 = 100-$w1-$w2-$w4-$w5;

                                                            $bargraph1 = $all['MIN'] ." - ". $all['MAX'];
                                                            $scale = $all['LOWER']+$all['HIGHER']+1;
                                                            $w1 = round(100*$all['LOWER']/$scale);
                                                            $w3 = round(100*$all['HIGHER']/$scale);
                                                            $w2 = 100-$w1-$w3;

                                                    }

                                            }

                                    }
                                   $assignment['DESCRIPTION']=html_entity_decode(html_entity_decode($assignment['DESCRIPTION']));
                                   $assignment['DESCRIPTION']=strip_tags($assignment['DESCRIPTION']);
                                   $LO_ret[] = array('TITLE'=>$assignment['TITLE'],'DESCRIPTION'=>$assignment['DESCRIPTION'],'CATEGORY'=>$assignment['CATEGORY'],'POINTS'=>($assignment['POINTS']=='-1'?'*':($assignment['POINTS']==''?'*':rtrim(rtrim(number_format($assignment['POINTS'],2),'0'),'.'))).' / '.$assignment['POINTS_POSSIBLE'],'PERCENT'=>($assignment['POINTS_POSSIBLE']=='0'?'':($assignment['POINTS']=='-1' || $assignment['POINTS']==''?'*':number_format(100*$assignment['POINTS']/$assignment['POINTS_POSSIBLE'],2).'%')),'LETTER'=>($assignment['POINTS_POSSIBLE']=='0'?'e/c':($assignment['POINTS']=='-1'  || $assignment['POINTS']==''?'Not Graded':'<b>'._makeLetterGrade($assignment['POINTS']/$assignment['POINTS_POSSIBLE'],$course['COURSE_PERIOD_ID'],$staff_id).'</b>')),'ASSIGNED_DATE'=>$assignment['ASSIGNED_DATE'],'DUE_DATE'=>$assignment['DUE_DATE'])+($do_stats?array('BAR1'=>$bargraph1,'BAR2'=>$bargraph2):array());
                                }
                            }
                            $grade_data[$i]['course_title'] = $course['COURSE_TITLE'].' - '.substr($course['TITLE'],strrpos(str_replace(' - ',' ^ ',$course['TITLE']),'^')+2);
                            $grade_data[$i]['gradebook_grade_data'] = $LO_ret;
                            $grade_data[$i]['gradebook_success'] = 1;
                            $grade_data[$i]['gradebook_msg'] = 'Nil';
        //                    unset($LO_ret[0]);
        //                    ListOutput($LO_ret,$LO_columns,'Assignment','Assignments',array(),array(),array('center'=>false,'save'=>$_REQUEST['id']!='all','search'=>false));
                    }
        //            else
        //            {
        //                $data[$i]['course_title'] = $course['COURSE_TITLE'];
        //                $LO_ret = array();
        //                $data[$i]['gradebook_grade_data'] = $LO_ret;
        //                $data[$i]['gradebook_msg'] ='There are no grades available for this student.';
        //                $data[$i]['gradebook_success'] = 0;
        //            }

                    $i++;
        //                    if($_REQUEST['id']!='all')
        //                            DrawHeader('There are no grades available for this student.');
            }
            $gd_data = array();
            foreach($grade_data as $gd)
            {
                $gd_data[]=$gd;
            }
            if(count($gd_data)>0)
            {
                $gd_success = 1;
                $msg = 'Nil';
            }
            else 
            {
                $gd_success = 0;
                $msg = 'There are no grades available for this student.';
            }
            if($gd_success==1 || $courses_success == 1)
                $success = 1;
            else 
                $success = 0;
            $data['detailed_grade'] = $gd_data;
            $data['detailed_grade_success'] = $gd_success;
            $data['success'] = $success;
            $data['msg'] = $msg;
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
function _makeLetterGrade($percent,$course_period_id=0,$staff_id=0,$ret='')
{	global $programconfig,$_openSIS;

	if(!$course_period_id)
		$course_period_id = UserCoursePeriod();

	if(!$staff_id)
		$staff_id = UserWs('STAFF_ID');

	if(!$programconfig[$staff_id])
	{
		$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\''));
		if(count($config_RET))
			foreach($config_RET as $title=>$value)
				$programconfig[$staff_id][$title] = $value[1]['VALUE'];
		else
			$programconfig[$staff_id] = true;
	}
	if(!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
		$_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
	$does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
	$grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];

	$percent *= 100;

		if($programconfig[$staff_id]['ROUNDING']=='UP')
			$percent = ceil($percent);
		elseif($programconfig[$staff_id]['ROUNDING']=='DOWN')
			$percent = floor($percent);
		elseif($programconfig[$staff_id]['ROUNDING']=='NORMAL')
			$percent = round($percent,2);
                
	
	else
		$percent = round($percent,2); // school default

	if($ret=='%')
		return $percent;

	if(!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
		$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND GRADE_SCALE_ID=\''.$grade_scale_id.'\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));
	

	foreach($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade)
	{
		if($does_breakoff=='Y' ? $percent>=$programconfig[$staff_id][$course_period_id.'-'.$grade['ID']] && is_numeric($programconfig[$staff_id][$course_period_id.'-'.$grade['ID']]) : $percent>=$grade['BREAK_OFF'])
			return $ret=='ID' ? $grade['ID'] : $grade['TITLE'];
	}
}
echo json_encode($data);
?>
