<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/function.php';

header('Content-Type: application/json');

$type = $_REQUEST['type']; // view or submit
$teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
        $programconfig = array();

        if($type!='')
        {
            if($type=='view')
            {
                $config_RET = DBGet_Mod(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$teacher_id.'\' AND school_id=\''.UserSchool().'\' AND PROGRAM=\'Gradebook\''));
                $general = array('ASSIGNMENT_SORTING','WEIGHT','ANOMALOUS_MAX','LATENCY','COMMENT_A','ELIGIBILITY_CUMULITIVE','DEFAULT_DUE','DEFAULT_ASSIGNED','ROUNDING');
                $programconfig['general']['ASSIGNMENT_SORTING']='';
                $programconfig['general']['WEIGHT']='';
                $programconfig['general']['ANOMALOUS_MAX']='';
                $programconfig['general']['LATENCY']='';
                $programconfig['general']['COMMENT_A']='';
                $programconfig['general']['ELIGIBILITY_CUMULITIVE']='';
                $programconfig['general']['DEFAULT_DUE']='';
                $programconfig['general']['DEFAULT_ASSIGNED']='';
                $programconfig['general']['ROUNDING']='';
                $programconfig['custom_grades'] = array();
                if(count($config_RET)>0)
                {
                        foreach($config_RET as $title=>$value)
                        {
                            if(in_array($value['TITLE'],$general))
                            {
                                $programconfig['general'][$value['TITLE']] = $value['VALUE'];
                            }
                            else 
                            {
                                $programconfig['custom_grades'][$value['TITLE']] = $value['VALUE'];
                            }
                        }
                }

        //        $data['config'] = $programconfig;
                if(count($programconfig['general'])>0)
                {
                    $data['config']['general']['success'] = 1;
                }
                else 
                {
                    $data['config']['general']['success'] = 0;
                }

                $grades = DBGet(DBQuery('SELECT cp.TITLE AS CP_TITLE,c.TITLE AS COURSE_TITLE,cp.COURSE_PERIOD_ID,rcg.TITLE,rcg.ID FROM report_card_grades rcg,course_periods cp,course_period_var cpv,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND cp.TEACHER_ID=\''.$teacher_id.'\' AND cp.SCHOOL_ID=rcg.SCHOOL_ID AND cp.SYEAR=rcg.SYEAR AND cp.SYEAR=\''.UserSyear().'\' AND rcg.GRADE_SCALE_ID=cp.GRADE_SCALE_ID AND cp.GRADE_SCALE_ID IS NOT NULL AND DOES_BREAKOFF=\'Y\' GROUP BY cp.COURSE_PERIOD_ID,rcg.ID ORDER BY rcg.BREAK_OFF IS NOT NULL DESC,rcg.BREAK_OFF DESC,rcg.SORT_ORDER DESC'),array(),array('COURSE_PERIOD_ID'));

                $quarters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,SEMESTER_ID,DOES_GRADES,DOES_EXAM FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                if($quarters)
                    $semesters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                else
                    $semesters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID, DOES_GRADES, NULL  AS DOES_EXAM FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                if($semesters)
                    $year = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                else
                    $year = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,NULL AS DOES_GRADES,NULL AS DOES_EXAM FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                $sem_total = 0;

                $i=0;$final_grade_percentage = array();
                if($quarters)
                {
                    foreach($quarters as $qtr)
                    {
                        $final_grade_percentage[$i]['HEADER']=$qtr['TITLE'];
                        
                            if($qtr['DOES_GRADES']=='Y')
                            {
                                $val = 0;
                                foreach($config_RET as $value)
                                {
                                    if($value['TITLE'] == 'Q-'.$qtr['MARKING_PERIOD_ID'])
                                    {
                                        $val = $value['VALUE'];
                                    }
                                }
                                $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'Q-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                $sem_total += $val;

                                if($qtr['DOES_EXAM']=='Y')
                                {
                                    $val = 0;
                                    foreach($config_RET as $value)
                                    {
                                        if($value['TITLE'] == 'Q-E'.$qtr['MARKING_PERIOD_ID'])
                                        {
                                            $val = $value['VALUE'];
                                        }
                                    }
                                    $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'].' Exam','name'=>'Q-E'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                    $sem_total += $val;
                                }
                            }
                            $i++;
                    }
                    foreach($semesters as $sem)
                    {
                        $final_grade_percentage[$i]['HEADER']=$sem['TITLE'];
                            if($sem['DOES_GRADES']=='Y')
                            {
                                    foreach($quarters as $qtr)
                                    {
                                        if($sem['MARKING_PERIOD_ID']==$qtr['SEMESTER_ID'])
                                        {
                                            $val = 0;
                                            foreach($config_RET as $value)
                                            {
                                                if($value['TITLE'] == 'SEM-'.$qtr['MARKING_PERIOD_ID'])
                                                {
                                                    $val = $value['VALUE'];
                                                }
                                            }
                                            $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'SEM-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
                                            $sem_total += $val;
                                        }
                                    }

                                    if($sem['DOES_EXAM']=='Y')
                                    {
                                            $val = 0;
                                            foreach($config_RET as $value)
                                            {
                                                if($value['TITLE'] == 'SEM-E'.$sem['MARKING_PERIOD_ID'])
                                                {
                                                    $val = $value['VALUE'];
                                                }
                                            }
                                            $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'].' Exam','name'=>'SEM-E'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
                                            $sem_total += $val;
                                    }
                            }
                            $i++;
                    }
                }

                $fy_total = 0;
                if($year[1]['DOES_GRADES']=='Y')
                {
                        $final_grade_percentage[$i]['HEADER'] = 'Full Year';
                        foreach($semesters as $sem)
                        {
//                                foreach($quarters as $qtr)
//                                {
//                                    if($sem['MARKING_PERIOD_ID']==$qtr['SEMESTER_ID'])
//                                    {
//                                        $val = 0;
//                                        foreach($config_RET as $value)
//                                        {
//                                            if($value['TITLE'] == 'FY-'.$qtr['MARKING_PERIOD_ID'])
//                                            {
//                                                $val = $value['VALUE'];
//                                            }
//                                        }
//                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'FY-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                        $fy_total += $val;
//                                    }
//                                }
                                if($sem['DOES_GRADES']=='Y')
                                {
                                        $val = 0;
                                        foreach($config_RET as $value)
                                        {
                                            if($value['TITLE'] == 'FY-'.$sem['MARKING_PERIOD_ID'])
                                            {
                                                $val = $value['VALUE'];
                                            }
                                        }
                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'],'name'=>'FY-'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
                                        $fy_total += $val;
                                }
//                                if($sem['DOES_EXAM']=='Y')
//                                {
//                                        $val = 0;
//                                        foreach($config_RET as $value)
//                                        {
//                                            if($value['TITLE'] == 'FY-E'.$sem['MARKING_PERIOD_ID'])
//                                            {
//                                                $val = $value['VALUE'];
//                                            }
//                                        }
//                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'].' Exam','name'=>'FY-E'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
//                                        $fy_total += $val;
//                                }
                        }
                        if($year[1]['DOES_EXAM']=='Y')
                        {
                                $val = 0;
                                foreach($config_RET as $value)
                                {
                                    if($value['TITLE'] == 'FY-E'.$year[1]['MARKING_PERIOD_ID'])
                                    {
                                        $val = $value['VALUE'];
                                    }
                                }
                                $final_grade_percentage[$i]['SEM'][] = array('title'=>$year[1]['TITLE'].' Exam','name'=>'FY-E'.$year[1]['MARKING_PERIOD_ID'].'','value'=>$val);
                                $fy_total += $val;
                        }
                }


                if(count($final_grade_percentage)>0)
                {
                    $success=array("success"=>1);

                    $data = array("final_grade_percentage"=>$final_grade_percentage);
                    $data=  array_merge($data,$success);
                }
                else 
                {
                    $success=array("success"=>1);
                    $data = array("final_grade_percentage"=>array());
                    $data=  array_merge($data,$success);
                }
        //        $data['sem_total'] = $sem_total;
        //        $data['fy_total'] = $fy_total;
        //        $data['config'] = $programconfig;
                $data=  array_merge($data,$programconfig);
                $assignment_sorting = array();
                $grades_rounding = array();

                $grades_rounding[] = array('value'=>'UP','title'=>'Up');
                $grades_rounding[] = array('value'=>'DOWN','title'=>'Down');
                $grades_rounding[] = array('value'=>'NORMAL','title'=>'Normal');
                $grades_rounding[] = array('value'=>'','title'=>'None');

                $gr = array('grades_rounding'=>$grades_rounding);

                $assignment_sorting[] = array('value'=>'ASSIGNMENT_ID','title'=>'Newest First');
                $assignment_sorting[] = array('value'=>'DUE_DATE','title'=>'Due Date');
                $assignment_sorting[] = array('value'=>'ASSIGNED_DATE','title'=>'Assigned Date');
                $assignment_sorting[] = array('value'=>'UNGRADED','title'=>'Ungraded');

                $as = array('assignment_sorting'=>$assignment_sorting);
        //        $data['grades_rounding'] = $grades_rounding;
        //        $data['assignment_sorting'] = $assignment_sorting;
                $data = array_merge($data,$gr);
                $data = array_merge($data,$as);

                $custom_grade_data = array();
                if(count($grades)>0)
                {
                    $i=0;
                    foreach($grades as $course_period_id=>$cp_grades)
                    {
                        $custom_grade_data[$i]['COURSE_TITLE']=$cp_grades[1]['COURSE_TITLE'].' - '.substr($cp_grades[1]['CP_TITLE'],0,strrpos(str_replace(' - ',' ^ ',$cp_grades[1]['CP_TITLE']),'^'));
                        $j=0;
                        $cp_grade_data = array();
                        foreach($cp_grades as $grade)
                        {
                                $cp_grade_data[$j]['TITLE'] = $grade['TITLE'];
                                $cp_grade_data[$j]['NAME'] = $course_period_id.'-'.$grade['ID'];
                                $cp_grade_data[$j]['VALUE'] = $programconfig['custom_grades'][$course_period_id.'-'.$grade['ID']];
                                $j++;
                        }
                        $custom_grade_data[$i]['CUSTOM_VALUE']=$cp_grade_data;
                        $i++;
                    }
                }
                if(count($custom_grade_data)>0)
                {
                    $cg_success = 1;
                }
                else 
                {
                    $cg_success = 0;
                }

                $gc = array('grades_count'=>count($grades),'custom_grade_data'=>$custom_grade_data,'custom_grade_success'=>$cg_success,'success'=>1,'err_msg'=>'nil');
                $data = array_merge($data,$gc);
        //        $data['success'] = 1;
        //        $data['err_msg'] = 'nil';
            }
            elseif($type=='submit')
            {
        //        $config=$_REQUEST["config"];
        //        $columns = json_decode($config,TRUE);
        //        $columns = $columns[0];
                $fgp=$_REQUEST["final_grade_percentage"];
                $final_grade_percentage=json_decode($fgp,TRUE);
                foreach($final_grade_percentage as $f)
                {
                    foreach($f as $key=>$val)
                    {
                        $fgp_data[$key]=$val;
                    }
                }

                $gen=$_REQUEST["general"];
                $general=json_decode($gen,TRUE);
                $general=$general[0];

                DBQuery('DELETE FROM program_user_config WHERE USER_ID=\''.$teacher_id.'\' AND school_id=\''.UserSchool().'\' AND PROGRAM=\'Gradebook\'');
                foreach($fgp_data as $title=>$value)
                {
                    DBQuery('INSERT INTO program_user_config (USER_ID,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\''.$teacher_id.'\',\''.UserSchool().'\',\'Gradebook\',\''.$title.'\',\''.str_replace("\'","''",str_replace('%','',$value)).'\')');
                }
                foreach($general as $title=>$value)
                {
                    DBQuery('INSERT INTO program_user_config (USER_ID,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\''.$teacher_id.'\',\''.UserSchool().'\',\'Gradebook\',\''.$title.'\',\''.str_replace("\'","''",str_replace('%','',$value)).'\')');
                }
                $custom=$_REQUEST["custom_data"];
                $custom=json_decode($custom,TRUE);
                if(count($custom)>0)
                {
                    foreach($custom as $custom1)
                    {
                        foreach($custom1 as $title=>$value)
                        {
                            DBQuery('INSERT INTO program_user_config (USER_ID,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\''.$teacher_id.'\',\''.UserSchool().'\',\'Gradebook\',\''.$title.'\',\''.str_replace("\'","''",str_replace('%','',$value)).'\')');
                        }
                    }

                }
        //        foreach($columns as $title=>$value)
        //        {
        //            DBQuery('INSERT INTO program_user_config (USER_ID,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\''.$teacher_id.'\',\''.UserSchool().'\',\'Gradebook\',\''.$title.'\',\''.str_replace("\'","''",str_replace('%','',$value)).'\')');
        //        }
        //        
                $config_RET = DBGet_Mod(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$teacher_id.'\' AND school_id=\''.UserSchool().'\' AND PROGRAM=\'Gradebook\''));
                $general = array('ASSIGNMENT_SORTING','WEIGHT','ANOMALOUS_MAX','LATENCY','COMMENT_A','ELIGIBILITY_CUMULITIVE','DEFAULT_DUE','DEFAULT_ASSIGNED','ROUNDING');
                $programconfig['general']['ASSIGNMENT_SORTING']='';
                $programconfig['general']['WEIGHT']='';
                $programconfig['general']['ANOMALOUS_MAX']='';
                $programconfig['general']['LATENCY']='';
                $programconfig['general']['COMMENT_A']='';
                $programconfig['general']['ELIGIBILITY_CUMULITIVE']='';
                $programconfig['general']['DEFAULT_DUE']='';
                $programconfig['general']['DEFAULT_ASSIGNED']='';
                $programconfig['general']['ROUNDING']='';
                $programconfig['custom_grades'] = array();
                if(count($config_RET)>0)
                {
                        foreach($config_RET as $title=>$value)
                        {
                            if(in_array($value['TITLE'],$general))
                            {
                                $programconfig['general'][$value['TITLE']] = $value['VALUE'];
                            }
                            else 
                            {
                                $programconfig['custom_grades'][$value['TITLE']] = $value['VALUE'];
                            }
                        }
                }
        //        $data['config'] = $programconfig;
                if(count($programconfig['general'])>0)
                {
                    $data['config']['general']['success'] = 1;
                }
                else 
                {
                    $data['config']['general']['success'] = 0;
                }

                $grades = DBGet(DBQuery('SELECT cp.TITLE AS CP_TITLE,c.TITLE AS COURSE_TITLE,cp.COURSE_PERIOD_ID,rcg.TITLE,rcg.ID FROM report_card_grades rcg,course_periods cp,course_period_var cpv,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND cp.TEACHER_ID=\''.$teacher_id.'\' AND cp.SCHOOL_ID=rcg.SCHOOL_ID AND cp.SYEAR=rcg.SYEAR AND cp.SYEAR=\''.UserSyear().'\' AND rcg.GRADE_SCALE_ID=cp.GRADE_SCALE_ID AND cp.GRADE_SCALE_ID IS NOT NULL AND DOES_BREAKOFF=\'Y\' GROUP BY cp.COURSE_PERIOD_ID,rcg.ID ORDER BY rcg.BREAK_OFF IS NOT NULL DESC,rcg.BREAK_OFF DESC,rcg.SORT_ORDER DESC'),array(),array('COURSE_PERIOD_ID'));

                $quarters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,SEMESTER_ID,DOES_GRADES,DOES_EXAM FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                if($quarters)
                    $semesters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                else
                    $semesters = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID, DOES_GRADES, NULL  AS DOES_EXAM FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                if($semesters)
                    $year = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                else
                    $year = DBGet_Mod(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,NULL AS DOES_GRADES,NULL AS DOES_EXAM FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
                $sem_total = 0;

                $i=0;$final_grade_percentage = array();
                if($quarters)
                {
                    foreach($quarters as $qtr)
                    {
                        $final_grade_percentage[$i]['HEADER']=$qtr['TITLE'];
                        
                            if($qtr['DOES_GRADES']=='Y')
                            {
                                $val = 0;
                                foreach($config_RET as $value)
                                {
                                    if($value['TITLE'] == 'Q-'.$qtr['MARKING_PERIOD_ID'])
                                    {
                                        $val = $value['VALUE'];
                                    }
                                }
                                $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'Q-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                $sem_total += $val;

                                if($qtr['DOES_EXAM']=='Y')
                                {
                                    $val = 0;
                                    foreach($config_RET as $value)
                                    {
                                        if($value['TITLE'] == 'Q-E'.$qtr['MARKING_PERIOD_ID'])
                                        {
                                            $val = $value['VALUE'];
                                        }
                                    }
                                    $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'].' Exam','name'=>'Q-E'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                    $sem_total += $val;
                                }
                            }
                            $i++;
                    }
                    foreach($semesters as $sem)
                    {
                        $final_grade_percentage[$i]['HEADER']=$sem['TITLE'];
                            if($sem['DOES_GRADES']=='Y')
                            {
                                    foreach($quarters as $qtr)
                                    {
                                        if($sem['MARKING_PERIOD_ID']==$qtr['SEMESTER_ID'])
                                        {
                                            $val = 0;
                                            foreach($config_RET as $value)
                                            {
                                                if($value['TITLE'] == 'SEM-'.$qtr['MARKING_PERIOD_ID'])
                                                {
                                                    $val = $value['VALUE'];
                                                }
                                            }
                                            $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'SEM-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
                                            $sem_total += $val;
                                        }
                                    }

                                    if($sem['DOES_EXAM']=='Y')
                                    {
                                            $val = 0;
                                            foreach($config_RET as $value)
                                            {
                                                if($value['TITLE'] == 'SEM-E'.$sem['MARKING_PERIOD_ID'])
                                                {
                                                    $val = $value['VALUE'];
                                                }
                                            }
                                            $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'].' Exam','name'=>'SEM-E'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
                                            $sem_total += $val;
                                    }
                            }
                            $i++;
                    }
                }

                $fy_total = 0;
                if($year[1]['DOES_GRADES']=='Y')
                {
                        $final_grade_percentage[$i]['HEADER'] = 'Full Year';
                        foreach($semesters as $sem)
                        {
//                                foreach($quarters as $qtr)
//                                {
//                                    if($sem['MARKING_PERIOD_ID']==$qtr['SEMESTER_ID'])
//                                    {
//                                        $val = 0;
//                                        foreach($config_RET as $value)
//                                        {
//                                            if($value['TITLE'] == 'FY-'.$qtr['MARKING_PERIOD_ID'])
//                                            {
//                                                $val = $value['VALUE'];
//                                            }
//                                        }
//                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$qtr['TITLE'],'name'=>'FY-'.$qtr['MARKING_PERIOD_ID'].'','value'=>$val);
//                                        $fy_total += $val;
//                                    }
//                                }
                                if($sem['DOES_GRADES']=='Y')
                                {
                                        $val = 0;
                                        foreach($config_RET as $value)
                                        {
                                            if($value['TITLE'] == 'FY-'.$sem['MARKING_PERIOD_ID'])
                                            {
                                                $val = $value['VALUE'];
                                            }
                                        }
                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'],'name'=>'FY-'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
                                        $fy_total += $val;
                                }
//                                if($sem['DOES_EXAM']=='Y')
//                                {
//                                        $val = 0;
//                                        foreach($config_RET as $value)
//                                        {
//                                            if($value['TITLE'] == 'FY-E'.$sem['MARKING_PERIOD_ID'])
//                                            {
//                                                $val = $value['VALUE'];
//                                            }
//                                        }
//                                        $final_grade_percentage[$i]['SEM'][] = array('title'=>$sem['TITLE'].' Exam','name'=>'FY-E'.$sem['MARKING_PERIOD_ID'].'','value'=>$val);
//                                        $fy_total += $val;
//                                }
                        }
                        if($year[1]['DOES_EXAM']=='Y')
                        {
                                $val = 0;
                                foreach($config_RET as $value)
                                {
                                    if($value['TITLE'] == 'FY-E'.$year[1]['MARKING_PERIOD_ID'])
                                    {
                                        $val = $value['VALUE'];
                                    }
                                }
                                $final_grade_percentage[$i]['SEM'][] = array('title'=>$year[1]['TITLE'].' Exam','name'=>'FY-E'.$year[1]['MARKING_PERIOD_ID'].'','value'=>$val);
                                $fy_total += $val;
                        }
                }


                if(count($final_grade_percentage)>0)
                {
                    $success=array("success"=>1);

                    $data = array("final_grade_percentage"=>$final_grade_percentage);
                    $data=  array_merge($data,$success);
                }
                else 
                {
                    $success=array("success"=>1);
                    $data = array("final_grade_percentage"=>array());
                    $data=  array_merge($data,$success);
                }
        //        $data['sem_total'] = $sem_total;
        //        $data['fy_total'] = $fy_total;
        //        $data['config'] = $programconfig;
                $data=  array_merge($data,$programconfig);
                $assignment_sorting = array();
                $grades_rounding = array();

                $grades_rounding[] = array('value'=>'UP','title'=>'Up');
                $grades_rounding[] = array('value'=>'DOWN','title'=>'Down');
                $grades_rounding[] = array('value'=>'NORMAL','title'=>'Normal');
                $grades_rounding[] = array('value'=>'','title'=>'None');

                $gr = array('grades_rounding'=>$grades_rounding);

                $assignment_sorting[] = array('value'=>'ASSIGNMENT_ID','title'=>'Newest First');
                $assignment_sorting[] = array('value'=>'DUE_DATE','title'=>'Due Date');
                $assignment_sorting[] = array('value'=>'ASSIGNED_DATE','title'=>'Assigned Date');
                $assignment_sorting[] = array('value'=>'UNGRADED','title'=>'Ungraded');

                $as = array('assignment_sorting'=>$assignment_sorting);
        //        $data['grades_rounding'] = $grades_rounding;
        //        $data['assignment_sorting'] = $assignment_sorting;
                $data = array_merge($data,$gr);
                $data = array_merge($data,$as);
                $custom_grade_data = array();
                if(count($grades)>0)
                {
                    $i=0;
                    foreach($grades as $course_period_id=>$cp_grades)
                    {
                        $custom_grade_data[$i]['COURSE_TITLE']=$cp_grades[1]['COURSE_TITLE'].' - '.substr($cp_grades[1]['CP_TITLE'],0,strrpos(str_replace(' - ',' ^ ',$cp_grades[1]['CP_TITLE']),'^'));
                        $j=0;
                        $cp_grade_data = array();
                        foreach($cp_grades as $grade)
                        {
                                $cp_grade_data[$j]['TITLE'] = $grade['TITLE'];
                                $cp_grade_data[$j]['NAME'] = $course_period_id.'-'.$grade['ID'];
                                $cp_grade_data[$j]['VALUE'] = $programconfig['custom_grades'][$course_period_id.'-'.$grade['ID']];
                                $j++;
                        }
                        $custom_grade_data[$i]['CUSTOM_VALUE']=$cp_grade_data;
                        $i++;
                    }
                }
                if(count($custom_grade_data)>0)
                {
                    $cg_success = 1;
                }
                else 
                {
                    $cg_success = 0;
                }

                $gc = array('grades_count'=>count($grades),'custom_grade_data'=>$custom_grade_data,'custom_grade_success'=>$cg_success,'success'=>1,'err_msg'=>'nil');
                $data = array_merge($data,$gc);
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
