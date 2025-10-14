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
$student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM student_field_categories ORDER BY SORT_ORDER,TITLE"));
$tab_sql = 'SELECT MODNAME,CAN_USE,CAN_EDIT FROM  profile_exceptions WHERE MODNAME LIKE \'students\/%category_id%\' AND CAN_USE=\'Y\' AND PROFILE_ID='.$_REQUEST['profile_id'];
$tab_data = DBGet(DBQuery($tab_sql));
$tab_ids = $can_use = $can_edit = $custom_tab_ids = array();
foreach($tab_data as $tab)
{
    $tab_no = explode('=',$tab['MODNAME']);
    $tab_ids[]=$tab_no[1];
    $can_use[$tab_no[1]]=$tab['CAN_USE'];
    $can_edit[$tab_no[1]]=$tab['CAN_EDIT'];
    if(!in_array($tab_no[1],array(1,2,3,4,5,6,7)))
    {
        $custom_tab_ids[] = $tab_no[1];
    }
}
$i=0;
foreach($categories_RET as $category)
{
    $tabs_data[$i]['tab_title']=$category['TITLE'];
    $tabs_data[$i]['tab_show']=(in_array($category['ID'],$tab_ids))?'1':'0';
    $tabs_data[$i]['can_use']=($can_use[$category['ID']]=='Y')?'Y':'N';
    $tabs_data[$i]['can_edit']=($can_edit[$category['ID']]=='Y')?'Y':'N';
    $i++;
}
        if($_REQUEST['action_type']=='upload_file')
        {
            $path = $_REQUEST['filename'];

            $temp=$student_id.'-'.$_FILES['file']['tmp_name'];
            $file_path = "../assets/studentfiles/";
            $folder = $file_path . basename($path); 

            if(move_uploaded_file($temp,$folder))
            {
                $success = 1;
                $msg = 'nil';
            }
            else 
            {
                $success = 0;
                $msg = 'File upload failed';
            }
            $data = array('file_upload_success'=>$success,'file_upload_msg'=>$msg);
        }
        if($_REQUEST['action_type']=='delete_file')
        {
            $file = $_REQUEST['file'];
            $path = explode('assets/',$file);
            $file_path = str_replace('webservice','',getcwd()).'assets/'.$path[1];
            if(file_exists($file_path))
            {
                if(unlink($file_path))
                {
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
            $data = array('file_del_success'=>$success,'file_del_msg'=>$msg);
        }
        elseif($_REQUEST['action_type']=='update_custom')
        {
            $up_data = json_decode($_REQUEST['details']);

            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    $cond[] = $cus->column.' = \''.$cus->value.'\'';
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE students SET '.implode(' , ',$cond).' WHERE student_id = '.$student_id);
                    $success = 1;
                    $msg = 'nil';
                }
                else 
                {
                    $success = 0;
                    $msg = 'data Update failed';
                }
                $data = array('custom_update_success'=>$success,'custom_update_msg'=>$msg);
            }
        }
        elseif($_REQUEST['action_type']=='upload_file')
        {
        //    $path = $_REQUEST['filename'];

            $temp = $student_id.'-'.$_FILES['filename']['tmp_name'];
            $file_path = "../assets/studentfiles/";
            $folder = $file_path . basename($student_id.'-'.$_FILES['filename']['tmp_name']); 

            if(move_uploaded_file($temp,$folder))
            {
                $folder = substr($folder,1);
                $data = array('success'=>1,'msg'=>'success'); 
            }
            else 
            {
                $data = array('success'=>0,'msg'=>'file upload failed'); 
            }
        }
        elseif($_REQUEST['action_type']=='submit_comment')
        {
            $comment=$_REQUEST["comment_details"];
            $columns = json_decode($comment,TRUE);
            $columns = $columns[0];
            $sql ='';
            if($columns['cm_date']!='' || $columns['cm_text']!='')
            {
                if(isset($_REQUEST['comment_id']) && $_REQUEST['comment_id']!='')
                {
                    $sql = DBQuery('UPDATE student_mp_comments SET COMMENT = \''.str_replace('\'','\\\'',urldecode($columns['cm_text'])).'\',COMMENT_DATE = \''.$columns['cm_date'].'\' WHERE ID = '.$_REQUEST['comment_id']);
                }
                else 
                {
                    $sql = DBQuery('INSERT INTO student_mp_comments(STUDENT_ID,SYEAR,MARKING_PERIOD_ID,STAFF_ID,COMMENT,COMMENT_DATE) VALUES ('.$_REQUEST['student_id'].','.$_REQUEST['syear'].','.$_REQUEST['mp_id'].','.$_REQUEST['staff_id'].',\''.str_replace('\'','\\\'',urldecode($columns['cm_text'])).'\',\''.$columns['cm_date'].'\')');
                }
            }
            if($sql==1)
                $data['success']=1;
            else 
                $data['success']=0;
        }
        elseif($_REQUEST['action_type']=='delete_comment')
        {
            $sql = '';
            $sql = DBQuery('DELETE FROM student_mp_comments WHERE ID = '.$_REQUEST['comment_id']);
            if($sql==1)
                $data['success']=1;
            else 
                $data['success']=0;
        }
        elseif($_REQUEST['action_type']=='submit_goal')
        {
            $goal=$_REQUEST["goal_details"];
            $columns = json_decode($goal,TRUE);
            $columns = $columns[0];
            $sql ='';
            if($columns['goal_title']!='')
            {
                if($columns['goal_begin_date']!='')
                {
                    if($columns['goal_end_date']!='')
                    {
                        if($columns['goal_desc']!='')
                        {
                            if(isset($_REQUEST['goal_id']) && $_REQUEST['goal_id']!='')
                            {
                                $sql = DBQuery('UPDATE student_goal SET GOAL_TITLE = \''.str_replace('\'','\\\'',urldecode($columns['goal_title'])).'\',START_DATE = \''.$columns['goal_begin_date'].'\',END_DATE = \''.$columns['goal_end_date'].'\',GOAL_DESCRIPTION = \''.str_replace('\'','\\\'',urldecode($columns['goal_desc'])).'\' WHERE GOAL_ID = '.$_REQUEST['goal_id']);
                            }
                        else 
                        {
                                $sql = DBQuery('INSERT INTO student_goal(STUDENT_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION,SCHOOL_ID,SYEAR) VALUES ('.$_REQUEST['student_id'].',\''.str_replace('\'','\\\'',urldecode($columns['goal_title'])).'\',\''.$columns['goal_begin_date'].'\',\''.$columns['goal_end_date'].'\',\''.str_replace('\'','\\\'',urldecode($columns['goal_desc'])).'\','.$_REQUEST['school_id'].','.$_REQUEST['syear'].')');
                            }
                            if($sql==1)
                            {
                                $data['success']=1;
                                $data['msg'] = 'Nil';
                            }
                            else 
                            {
                                $data['success']=0;
                                $data['msg'] = 'Failed';
                            }
                        }
                        else 
                        {
                            $data['success'] = 0;
                            $data['msg'] = 'Please enter goal description';
                        }
                    }
                    else 
                    {
                        $data['success'] = 0;
                        $data['msg'] = 'Please enter end date';
                    }
                }
                else 
                {
                    $data['success'] = 0;
                    $data['msg'] = 'Please enter begin date';
                }
            }
            else 
            {
                $data['success'] = 0;
                $data['msg'] = 'Please enter goal title';
            }
        }
        elseif($_REQUEST['action_type']=='delete_goal')
        {
            $sql = '';
            $prog_sql = DBGet(DBQuery('SELECT COUNT(*) AS PROG_COUNT FROM student_goal_progress WHERE GOAL_ID = '.$_REQUEST['goal_id']));
            $prog_sql = $prog_sql[1];
            if($prog_sql['PROG_COUNT']!=0)
            {
                $data['success'] = 0;
                $data['msg'] = 'Unable to delete Goal. Please delete Progresses first.';
            }
            else 
            {
                $sql = DBQuery('DELETE FROM student_goal WHERE GOAL_ID = '.$_REQUEST['goal_id']);
                if($sql==1)
                {
                    $data['success']=1;
                    $data['msg'] = 'Nil';
                }
                else 
                {
                    $data['success']=0;
                    $data['msg'] = 'Failed';
                }
            }
        }
        elseif($_REQUEST['action_type']=='add_progress_view')
        {
            $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE FROM student_goal WHERE SCHOOL_ID=\''.  UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND STUDENT_ID=\''.$student_id.'\' ORDER BY START_DATE DESC';		
            $QI_goal = DBQuery($sql_goal);
            $goal_RET = DBGet($QI_goal);
            $goal_data = array();
            $goal_data[0]['GOAL_ID']='';
            $goal_data[0]['GOAL_TITLE']='N/A';
            foreach($goal_RET as $goal)
            {
                $goal_data[]=$goal;
            }
            $data['goal_data'] = $goal_data;
            if(count($goal_data)>0)
            {
                $data['goal_success'] = 1;
            }
            else 
            {
                $data['goal_success'] = 0;
            }
            $options = array(''=>'N/A','0'=>'0-10%','1'=>'11-20%','2'=>'21-30%','3'=>'31-40%','4'=>'41-50%','5'=>'51-60%','6'=>'61-70%','7'=>'71-80%','8'=>'81-90%','9'=>'91-100%');
            $i = 0;
            foreach($options as $val)
            {
                $prof_data[$i]['VALUE'] = $val;
                $prof_data[$i]['TITLE'] = $val;
                $i++;
            }
            $sql_cp = 'SELECT cp.COURSE_PERIOD_ID AS COURSE_PERIOD, cp.TITLE AS COURSE_PERIOD_NAME FROM course_periods cp, schedule s WHERE s.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND s.STUDENT_ID=\''.$student_id.'\'';		
            $QI_cp = DBQuery($sql_cp);
            $cp_RET = DBGet($QI_cp);
            $cp_data = array();
            $cp_data[0]['COURSE_PERIOD']='';
            $cp_data[0]['COURSE_PERIOD_NAME']='N/A';
            foreach($cp_RET as $cp)
            {
                $cp_data[]=$cp;
            }
            $data['cp_data'] = $cp_data;
            $data['prof_data'] = $prof_data;
            if(count($cp_data)>0)
            {
                $data['cp_success'] = 1;
            }
            else 
            {
                $data['cp_success'] = 0;
            }
        }
        elseif($_REQUEST['action_type']=='submit_progress')
        {
            $progress=$_REQUEST["progress_details"];
            $columns = json_decode($progress,TRUE);
            $columns = $columns[0];
            $sql ='';

            if($columns['cp_id']!='')
            {
                if($columns['prog_begin_date']!='')
                {
                    if($columns['prog_name']!='')
                    {
                        if($columns['prog_prof_scale']!='')
                        {
                            if($columns['prog_desc']!='')
                            {
                                if(isset($_REQUEST['progress_id']) && $_REQUEST['progress_id']!='')
                                {
                                    $sql = DBQuery('UPDATE student_goal_progress SET START_DATE = \''.$columns['prog_begin_date'].'\',PROGRESS_NAME = \''.urldecode($columns['prog_name']).'\',PROFICIENCY = \''.$columns['prog_prof_scale'].'\',PROGRESS_DESCRIPTION = \''.urldecode($columns['prog_desc']).'\',COURSE_PERIOD_ID = \''.$columns['cp_id'].'\' WHERE PROGRESS_ID = '.$_REQUEST['progress_id']);
                                }
                                else 
                                {
                                    $sql = DBQuery('INSERT INTO student_goal_progress(GOAL_ID,STUDENT_ID,START_DATE,PROGRESS_NAME,PROFICIENCY,PROGRESS_DESCRIPTION,COURSE_PERIOD_ID) VALUES ('.$_REQUEST['goal_id'].','.$_REQUEST['student_id'].',\''.$columns['prog_begin_date'].'\',\''.urldecode($columns['prog_name']).'\',\''.$columns['prog_prof_scale'].'\',\''.urldecode($columns['prog_desc']).'\','.$columns['cp_id'].')');
                                }
                                if($sql==1)
                                {
                                    $data['success']=1;
                                    $data['msg'] = 'Nil';
                                }
                                else 
                                {
                                    $data['success']=0;
                                    $data['msg'] = 'Failed';
                                }
                            }
                            else 
                            {
                                $data['success'] = 0;
                                $data['msg'] = 'Please enter progress assessment';
                            }

                        }
                        else 
                        {
                            $data['success'] = 0;
                            $data['msg'] = 'Please enter proficiency scale';
                        }
                    }
                    else 
                    {
                        $data['success'] = 0;
                        $data['msg'] = 'Please enter progress period name';
                    }
                }
                else 
                {
                    $data['success'] = 0;
                    $data['msg'] = 'Please enter begin date';
                }
            }
            else 
            {
                $data['success'] = 0;
                $data['msg'] = 'Please enter course period';
            }
        }
        elseif($_REQUEST['action_type']=='delete_progress')
        {
            $sql = DBQuery('DELETE FROM student_goal_progress WHERE PROGRESS_ID = '.$_REQUEST['progress_id']);
            if($sql==1)
            {
                $data['success']=1;
                $data['msg'] = 'Nil';
            }
            else 
            {
                $data['success']=0;
                $data['msg'] = 'Failed';
            }
        }
        else 
        {
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
            
            $path ='../assets/studentphotos/';
            
            $general_info = array();
            $enrollment_info = array();
            $comments = array();
            $goals = array();
            $parent_data = array();
            $medical_info = array();
            $files_info = array();
            $custom = array();
            $sql = "SELECT s.STUDENT_ID,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,s.NAME_SUFFIX,la.USERNAME,la.PASSWORD,la.LAST_LOGIN,s.IS_DISABLE,s.ESTIMATED_GRAD_DATE,s.GENDER,e.ETHNICITY_NAME AS ETHNICITY,s.COMMON_NAME,s.BIRTHDATE,l.LANGUAGE_NAME AS LANGUAGE,s.ALT_ID,s.EMAIL,s.PHONE,(SELECT SCHOOL_ID FROM student_enrollment WHERE SYEAR='".UserSyear()."' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS SCHOOL_ID,
                    (SELECT GRADE_ID FROM student_enrollment WHERE SYEAR='".UserSyear()."' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS GRADE_ID,
                        (SELECT NEXT_SCHOOL FROM student_enrollment WHERE SYEAR='".UserSyear()."' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS NEXT_SCHOOL,
                        (SELECT CALENDAR_ID FROM student_enrollment WHERE SYEAR='".UserSyear()."' AND STUDENT_ID=s.STUDENT_ID ORDER BY START_DATE DESC,END_DATE DESC LIMIT 1) AS CALENDAR_ID
                    FROM students s , login_authentication la, ethnicity e, language l
                    WHERE s.STUDENT_ID='".$student_id."' AND s.STUDENT_ID=la.USER_ID AND la.PROFILE_ID=3 AND s.ethnicity_id = e.ethnicity_id AND s.language_id = l.language_id";
            $QI = DBQuery($sql);
            $student = DBGet($QI);
            $schools_RET = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID!=\''.UserSchool().'\''));
            $next_school_options = array(UserSchool()=>'Next grade at current school','0'=>'Retain','-1'=>'Do not enroll after this school year');
            if(count($schools_RET))
            {
                    foreach($schools_RET as $school)
                            $next_school_options[$school['ID']] = $school['TITLE'];
            }
            
            $i = 0;
            foreach($student as $stu)
            {
                $stuPicPath=$path.$student_id.".JPG";
                if(file_exists($stuPicPath))
                    $general_info[$i]['PHOTO']=$htpath.$student_id.".JPG";
                else 
                    $general_info[$i]['PHOTO']="";
                $profile_pic=DBGet(DBQuery('SELECT CONTENT FROM user_file_upload WHERE USER_ID=\''.$student_id.'\' AND PROFILE_ID=3 AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\''));
                if(!empty($profile_pic) && isset($profile_pic[1]['CONTENT']))
                $general_info[$i]['PHOTO']=base64_encode($profile_pic[1]['CONTENT']);
                $general_info[$i]['FIRST_NAME']=$stu['FIRST_NAME'];
                $general_info[$i]['LAST_NAME']=$stu['LAST_NAME'];
                $general_info[$i]['MIDDLE_NAME']=$stu['MIDDLE_NAME'];
                $general_info[$i]['STUDENT_ID']=$stu['STUDENT_ID'];
                $general_info[$i]['COMMON_NAME']=$stu['COMMON_NAME'];
                $general_info[$i]['BIRTHDATE']=$stu['BIRTHDATE'];
                $general_info[$i]['EMAIL']=$stu['EMAIL'];
                $general_info[$i]['GENDER']=$stu['GENDER'];
                $general_info[$i]['ETHNICITY']=$stu['ETHNICITY'];
                $general_info[$i]['PRIMARY_LANGUAGE']=$stu['LANGUAGE'];
                $general_info[$i]['PHONE']=$stu['PHONE'];
                $general_info[$i]['ALT_ID']=$stu['ALT_ID'];
                $general_info[$i]['GRADE_ID']=$stu['GRADE_ID'];

                if($stu['GRADE_ID']!='')
                {
                    $grade_data = DBGet(DBQuery("SELECT TITLE FROM school_gradelevels WHERE SCHOOL_ID='".  UserSchool()."' AND ID=".$stu['GRADE_ID']));
                    $general_info[$i]['GRADE_NAME']=$grade_data[1]['TITLE'];
                }
                else 
                {
                    $general_info[$i]['GRADE_NAME']='';
                }

                $cal_data = DBGet(DBQuery("SELECT TITLE FROM school_calendars WHERE SCHOOL_ID='".  UserSchool()."' AND CALENDAR_ID=".$stu['CALENDAR_ID']));
                $enrollment_info[$i]['CALENDAR'] = $cal_data[1]['TITLE'];

                foreach($next_school_options as $val=>$title)
                {
                    if($val == $stu['NEXT_SCHOOL'])
                        $enrollment_info[$i]['NEXT_SCHOOL'] = $title;
                }
                $enroll_data = array();
                $RET = DBGet(DBQuery('SELECT e.ENROLLMENT_CODE,e.START_DATE,e.DROP_CODE,e.END_DATE,e.END_DATE AS END,e.SCHOOL_ID,sc.TITLE AS SCHOOL_NAME FROM student_enrollment e,students s,schools sc WHERE e.STUDENT_ID=\''.$stu['STUDENT_ID'].'\' AND e.SYEAR<=\''.UserSyear().'\' AND e.SCHOOL_ID=\''.UserSchool().'\' AND e.STUDENT_ID=s.STUDENT_ID AND e.SCHOOL_ID=sc.ID ORDER BY e.START_DATE'));

                if(count($RET)>0)
                {
                        $date_counter=$date_counter+1;
                        foreach($RET as $in=>$value)
                        {
                            if($value['ENROLLMENT_CODE']!='')
                            {
                                $enroll_code_data = DBGet(DBQuery('SELECT TITLE FROM student_enrollment_codes WHERE ID = '.$value['ENROLLMENT_CODE']));
                                $value['ENROLLMENT_CODE_NAME']=$enroll_code_data[1]['TITLE'];
                            }
                            else 
                            {
                                $value['ENROLLMENT_CODE_NAME']='N/A';
                            }
                            if($value['DROP_CODE']!='')
                            {
                                $drop_code_data = DBGet(DBQuery('SELECT TITLE FROM student_enrollment_codes WHERE ID = '.$value['DROP_CODE']));
                                $value['DROP_CODE_NAME']=$drop_code_data[1]['TITLE'];
                            }
                            else 
                            {
                                $value['DROP_CODE_NAME']='N/A';
                            }
                            $enroll_data[]=$value;

                        }
                }
                $enrollment_info[$i]['SCHOOL_INFO'] = $enroll_data;
                $home_addr = array();
                $h_addr=DBGet(DBQuery(' SELECT sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from student_address sa WHERE 
                                               sa.TYPE=\'Home Address\' AND sa.STUDENT_ID=\''.  $student_id.'\' AND sa.SCHOOL_ID=\''.  UserSchool().'\' '));
                
                foreach($h_addr as $hdd)
                {
                    $home_addr[]=$hdd;
                }
                $address[$i]['HOME_ADDRESS'] = $home_addr;        
                $pri_par_id=  DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID='.$student_id.' AND EMERGENCY_TYPE=\'Primary\''));
                if(count($pri_par_id)>0)
                {
                   $p_addr=DBGet(DBQuery('SELECT p.STAFF_ID as CONTACT_ID,p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,p.HOME_PHONE,p.WORK_PHONE,p.CELL_PHONE,p.EMAIL,p.CUSTODY,
                                      sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$pri_par_id[1]['PERSON_ID'].'\'  AND sa.PEOPLE_ID IS NOT NULL '));
                   $p_addr[1]['RELATIONSHIP']=$pri_par_id[1]['RELATIONSHIP'];
                   $p_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$pri_par_id[1]['PERSON_ID'].'\' AND PROFILE_ID=4'));
                   $p_addr[1]['USER_NAME']=$p_log_addr[1]['USER_NAME'];
                   $p_addr[1]['PASSWORD']=$p_log_addr[1]['PASSWORD'];
                   $p_addr[1]['STRING'] = strtolower($p_addr[1]['LAST_NAME'].' '.$p_addr[1]['FIRST_NAME'].','.$p_log_addr[1]['USER_NAME']);
                }
                else 
                {
                    $p_addr = array();
                }
                
                $mail_addr = array();
                $m_addr=DBGet(DBQuery(' SELECT sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from student_address sa WHERE 
                                       sa.TYPE=\'Mail\' AND sa.STUDENT_ID=\''.  $student_id.'\'  AND sa.SYEAR=\''.UserSyear().'\' AND sa.SCHOOL_ID=\''.  UserSchool().'\' '));
                $m_checked = '';
                if($m_addr[1]['ADDRESS_ID']!='' && $h_addr[1]['ADDRESS_ID']!='')
                {    
                $s_mail_address=DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM student_address WHERE ID!=\''.$m_addr[1]['ADDRESS_ID'].'\' AND STREET_ADDRESS_1=\''.str_replace("'","\'",$m_addr[1]['ADDRESS']).'\' AND CITY=\''.str_replace("'","\'",$m_addr[1]['CITY']).'\' AND STATE=\''.str_replace("'","\'",$m_addr[1]['STATE']).'\' AND ZIPCODE=\''.$m_addr[1]['ZIPCODE'].'\' AND TYPE=\'Home Address\' '));
                if($s_mail_address[1]['TOTAL']!=0)
                   $m_checked="Y";
                else
                    $m_checked="N";
                }
                foreach($m_addr as $mdd)
                {
                    $mdd['SAME_AS_HOME_ADDR']=$m_checked;
                    $mail_addr[]=$mdd;
                }
                $address[$i]['MAIL_ADDRESS'] = $mail_addr;

                if($p_addr[1]['USER_NAME']=='')
                {    
                    $portal_check='N';    
                }
                else
                {
                    $portal_check='Y';
                }
                if(count($p_addr)>0)
                {
                    foreach($p_addr as $padd)
                    {
                        $padd['SAME_AS_HOME_ADDR'] = $m_checked;
                        $padd['PORTAL_USER'] = $portal_check;
                        $address[$i]['PRIMARY_EMERGENCY_CONTACT'][] = $padd;
                    }
                }
                else 
                {
                    $address[$i]['PRIMARY_EMERGENCY_CONTACT'] = array();
                }
                $sec_par_id=  DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID='.$student_id.' AND EMERGENCY_TYPE=\'Secondary\''));

                if(count($sec_par_id)>0)
                {  
                    $s_addr=DBGet(DBQuery('SELECT p.STAFF_ID as CONTACT_ID,p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,p.HOME_PHONE,p.WORK_PHONE,p.CELL_PHONE,p.EMAIL,p.CUSTODY,
                                      sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$sec_par_id[1]['PERSON_ID'].'\'  AND sa.PEOPLE_ID IS NOT NULL '));                 


                    $s_addr[1]['RELATIONSHIP']=$sec_par_id[1]['RELATIONSHIP'];
                    $p_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$sec_par_id[1]['PERSON_ID'].'\' AND PROFILE_ID=4'));
                   $s_addr[1]['USER_NAME']=$p_log_addr[1]['USER_NAME'];
                   $s_addr[1]['PASSWORD']=$p_log_addr[1]['PASSWORD'];
                   $s_addr[1]['STRING'] = strtolower($s_addr[1]['LAST_NAME'].' '.$s_addr[1]['FIRST_NAME'].','.$p_log_addr[1]['USER_NAME']);

                }
                else
                {
                    $s_addr=DBGet(DBQuery('SELECT ID AS ADDRESS_ID from student_address WHERE STUDENT_ID='.$student_id.' AND TYPE=\'Secondary\' '));                 
                }

                if($h_addr[1]['ADDRESS_ID']!='' && $p_addr[1]['ADDRESS_ID']!='')
                {
                    $s_prim_address=DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM student_address WHERE ID!=\''.$p_addr[1]['ADDRESS_ID'].'\' AND STREET_ADDRESS_1=\''.str_replace("'","\'",$p_addr[1]['ADDRESS']).'\' AND CITY=\''.str_replace("'","\'",$p_addr[1]['CITY']).'\' AND STATE=\''.str_replace("'","\'",$p_addr[1]['STATE']).'\' AND ZIPCODE=\''.$p_addr[1]['ZIPCODE'].'\' AND TYPE=\'Home Address\' '));
                    if($s_prim_address[1]['TOTAL']!=0)
                       $p_checked="Y";
                    else
                        $p_checked="N";
                }
                if($s_addr[1]['USER_NAME']=='')
                {    
                $portal_check='N';    
                }
                else
                {
                $portal_check='Y';
                }

                if(count($s_addr)>0)
                {
                    foreach($s_addr as $sadd)
                    {
                        $sadd['SAME_AS_HOME_ADDR'] = $m_checked;
                        $sadd['PORTAL_USER'] = $portal_check;
                        $address[$i]['SECONDARY_EMERGENCY_CONTACT'][] = $sadd;
                    }
                }
                else 
                {
                    $address[$i]['SECONDARY_EMERGENCY_CONTACT'] = array();
                }
                // for other users associated with student
                $otr_par_id=  DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID='.$student_id.' AND EMERGENCY_TYPE=\'Other\''));
                $other_users = array();
                
                if(count($otr_par_id)>0)
                {
                    foreach($otr_par_id as $otr_data)
                    {
                        $o_addr=DBGet(DBQuery('SELECT p.STAFF_ID as CONTACT_ID,p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,p.HOME_PHONE,p.WORK_PHONE,p.CELL_PHONE,p.EMAIL,p.CUSTODY,
                                    sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$otr_data['PERSON_ID'].'\'  AND sa.PEOPLE_ID IS NOT NULL '));                 

                        $o_addr[1]['RELATIONSHIP']=$otr_data['RELATIONSHIP'];
                        $o_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$otr_data['PERSON_ID'].'\' AND PROFILE_ID=4'));
                        $o_addr[1]['USER_NAME']=$o_log_addr[1]['USER_NAME'];
                        $o_addr[1]['PASSWORD']=$o_log_addr[1]['PASSWORD'];
                        $o_addr[1]['STRING'] = strtolower($o_addr[1]['LAST_NAME'].' '.$o_addr[1]['FIRST_NAME'].','.$o_log_addr[1]['USER_NAME']);
                        if($o_addr[1]['USER_NAME']=='')
                        {    
                            $o_addr[1]['PORTAL_USER']='N';    
                        }
                        else
                        {
                            $o_addr[1]['PORTAL_USER']='Y';
                        }
                        $other_users[]=$o_addr[1];
                    }
                }
                $address[$i]['OTHER_CONTACT'] = $other_users;
                $table = 'student_mp_comments';

                $comments = array();
        //        if(UserWs('PROFILE')=='admin' || UserWs('PROFILE')=='teacher'|| UserWs('PROFILE')=='parent' || UserWs('PROFILE')=='student')
                $comments_RET = DBGet(DBQuery("SELECT ID,COMMENT_DATE,COMMENT,CONCAT(s.FIRST_NAME,' ',s.LAST_NAME)AS USER_NAME,student_mp_comments.STAFF_ID FROM student_mp_comments,staff s WHERE STUDENT_ID='".$student_id."'  AND s.STAFF_ID=student_mp_comments.STAFF_ID ORDER BY ID DESC"));

                foreach($comments_RET as $mi=>$md)
                {
                    $comments[]=$md;
                }

                $sql = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE SCHOOL_ID=\''.  UserSchool().'\' AND SYEAR=\''.UserSyear().'\' AND STUDENT_ID=\''.$student_id.'\' ORDER BY START_DATE DESC';

                $QI = DBQuery($sql);
                $goals_RET = DBGet($QI);
                $goals = array();
                foreach($goals_RET as $goal)
                {
                    $sql = 'SELECT sgp.GOAL_ID,sgp.PROGRESS_ID,sgp.START_DATE,sgp.PROGRESS_NAME,sgp.PROFICIENCY,sgp.PROGRESS_DESCRIPTION,sgp.COURSE_PERIOD_ID,(SELECT TITLE FROM course_periods WHERE course_period_id = sgp.course_period_id) AS COURSE_PERIOD FROM student_goal_progress sgp
                            WHERE sgp.GOAL_ID=\''.$goal['GOAL_ID'].'\'';
                    $QI = DBQuery($sql);
                    $progressRET = DBGet($QI);
                    $progress_period = array();
                    foreach($progressRET as $prog)
                    {
                        $progress_period[]=$prog;
                    }
                    $goal['PROGRESS_DATA'] = $progress_period;
                    $goals[]=$goal;
                }
                $sql = 'SELECT DISTINCT u.PERSON_ID AS STAFF_ID,CONCAT(p.FIRST_NAME,\' \',p.LAST_NAME) AS FULL_NAME,p.HOME_PHONE,p.EMAIL,u.RELATIONSHIP FROM students_join_people u,people p,staff_school_relationship ssr WHERE p.STAFF_ID=u.PERSON_ID AND u.STUDENT_ID=\''.$student_id.'\' AND ssr.SYEAR=\''.UserSyear().'\' AND ssr.SCHOOL_ID=\''.UserSchool().'\' ';
                $current_RET = DBGet(DBQuery($sql));
                foreach($current_RET as $parent)
                {
                    $parent_data[] = $parent;
                }

                $stu_Medical_info=  DBGet(DBQuery('SELECT PHYSICIAN,PHYSICIAN_PHONE,PREFERRED_HOSPITAL FROM medical_info WHERE STUDENT_ID='.$student_id.' AND SYEAR='.UserSyear().' AND SCHOOL_ID='.$stu['SCHOOL_ID'].''));

                if(count($stu_Medical_info)>0)
                    $medical_info[0]['doctor_detail'][0] = $stu_Medical_info[1];
                else 
                    $medical_info[0]['doctor_detail'] = array();

                $table = 'student_medical_notes';

                $functions = array('DOCTORS_NOTE_COMMENTS'=>'_makeAlertComments');
                $med_RET = DBGet(DBQuery('SELECT ID,STUDENT_ID,DOCTORS_NOTE_DATE,DOCTORS_NOTE_COMMENTS
                            FROM student_medical_notes
                            WHERE STUDENT_ID=\''.$student_id.'\''),$functions);
                foreach($med_RET as $mi=>$md)
                {
                    $medical_notes[]=$md;
            }
                if(count($med_RET)>0)
                    $medical_info[0]['medical_notes'] = $medical_notes;
                else 
                    $medical_info[0]['medical_notes'] = array();
                $table = 'student_immunization';

                $functions = array('TYPE'=>'_makeType','COMMENTS'=>'_makeAlertComments');
                $med_RET = DBGet(DBQuery('SELECT ID,TYPE,MEDICAL_DATE,COMMENTS FROM student_immunization WHERE STUDENT_ID=\''.$student_id.'\' ORDER BY MEDICAL_DATE,TYPE'),$functions);
                foreach($med_RET as $mi=>$md)
                {
                    $immunization[]=$md;
                }
                if(count($med_RET)>0)
                    $medical_info[0]['immunization'] = $immunization;
                else 
                    $medical_info[0]['immunization'] = array();
                $table = 'student_medical_alerts';

                $functions = array('TITLE'=>'_makeAlertComments');
                $med_RET = DBGet(DBQuery('SELECT ID,TITLE,ALERT_DATE FROM student_medical_alerts WHERE STUDENT_ID=\''.$student_id.'\' ORDER BY ID'),$functions);
                $columns = array('ALERT_DATE'=>'Alert Date','TITLE'=>'Medical Alert');
                foreach($med_RET as $mi=>$md)
                {
                    $medical_alerts[]=$md;
                }
                if(count($med_RET)>0)
                    $medical_info[0]['medical_alerts'] = $medical_alerts;
                else 
                    $medical_info[0]['medical_alerts'] = array();
                $table = 'student_medical_visits';

                $functions = array('TIME_IN'=>'_makeComments','TIME_OUT'=>'_makeComments','REASON'=>'_makeComments','RESULT'=>'_makeComments','COMMENTS'=>'_makeLongComments');
                $med_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TIME_IN,TIME_OUT,REASON,RESULT,COMMENTS FROM student_medical_visits WHERE STUDENT_ID=\''.$student_id.'\' ORDER BY SCHOOL_DATE'),$functions);
                foreach($med_RET as $mi=>$md)
                {
                    $nurse_visits[]=$md;
                }
                if(count($med_RET)>0)
                    $medical_info[0]['nurse_visits'] = $nurse_visits;
                else 
                    $medical_info[0]['nurse_visits'] = array();
                $filepath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/studentfiles/";
                $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
                if($file_path!='')
                $htpath=$htpath."/".$file_path;
                $htpath=$htpath."/assets/studentphotos/";
                $dir=$_SERVER['DOCUMENT_ROOT'];
                if($file_path!='')
                $dir=$dir.'/'.$file_path;
                $dir=$dir.'/assets/studentfiles/';
                $dir=dir($dir);
                if($dir)
                {
                while($filename=$dir->read()) 
                {
                    if($filename!='')
                    {
                        if($filename=='.' || $filename=='..')
                        continue;

                        $student_id_up = explode('-',$filename);

                        if($student_id_up[0]==$student_id)
                        {
                            $found=true;
                            $files_info[]=array('PATH'=>$filepath.$filename,'NAME'=>substr($filename,strpos($filename,'-')+1));
                        }
                    }
                }
                $dir->close();
                }
                if(count($custom_tab_ids)>0)
                {
                    foreach($custom_tab_ids as $cat_id)
                    {
                        foreach($categories_RET as $cat)
                        {
                            if($cat['ID']==$cat_id)
                            {
                                $title = $cat['TITLE']; 
                            }
                        }
                        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE,SORT_ORDER FROM custom_fields WHERE SYSTEM_FIELD=\'N\' AND CATEGORY_ID=\''.$cat_id.'\' ORDER BY SORT_ORDER,TITLE'));
                        if(Count($fields_RET)>0)
                        {
                            foreach($fields_RET as $fields)
                            {
                                $sql = "SELECT CUSTOM_".$fields['ID']." FROM students WHERE STUDENT_ID='".$student_id."'";
                                $QI = DBQuery($sql);
                                $student = DBGet($QI);
                                $custom[$title][]=array('tab_name'=>$title,'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
                            }
                            $title='';
                        }
                    }
                }
            }
            $i=$j=0;
            foreach($categories_RET as $cat)
            {
                if($cat['ID']==1)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                    if(count($general_info)>0)
                    {
                        $data['tab_data'][$i]['tab_success'] = 1;
                    }
                    else 
                    {
                        $data['tab_data'][$i]['tab_success'] = 0;
                    }
                    $data['tab_data'][$i]['tab_content']=$general_info;
                    $custom_fields = array();
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE,SORT_ORDER FROM custom_fields WHERE SYSTEM_FIELD=\'N\' AND CATEGORY_ID=\'1\' ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM students WHERE STUDENT_ID='".$student_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);
                            $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'');
                        }
                    }
                    $data['tab_data'][$i]['custom_fields_content'] = $custom_fields;
                    $i++;
                }
                elseif($cat['ID']==2)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                        if(count($medical_info)>0)
                        {
                            $data['tab_data'][$i]['tab_success'] = 1;
                }
                else 
                {
                        $data['tab_data'][$i]['tab_success'] = 0;
                }
                    $data['tab_data'][$i]['tab_content']=$medical_info;
                    $custom_fields = array();
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE,SORT_ORDER FROM custom_fields WHERE SYSTEM_FIELD=\'N\' AND CATEGORY_ID=\'2\' ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM students WHERE STUDENT_ID='".$student_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);
                            $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'');
            //                $custom[$title][]=array('tab_name'=>$title,'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
                        }
                    }
                    $data['tab_data'][$i]['custom_fields_content'] = $custom_fields;
                    $i++;
                }
                elseif($cat['ID']==3)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                if(count($address[0]['HOME_ADDRESS'])>0||count($address[0]['MAIL_ADDRESS'])>0||count($address[0]['PRIMARY_EMERGENCY_CONTACT'])>0||count($address[0]['SECONDARY_EMERGENCY_CONTACT'])>0)
                {
                    $data['tab_data'][$i]['tab_success'] = 1;
                }
                else 
                {
                    $data['tab_data'][$i]['tab_success'] = 0;
                }
                    $data['tab_data'][$i]['tab_content']=$address;
                    $i++;
                }
                elseif($cat['ID']==4)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                if(count($comments)>0)
                {
                    $data['tab_data'][$i]['tab_success'] = 1;
                }
                else 
                {
                    $data['tab_data'][$i]['tab_success'] = 0;
                }    
                    $data['tab_data'][$i]['tab_content']=$comments;
                    $i++;
                }
                elseif($cat['ID']==5)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                if(count($goals)>0)
                {
                    $data['tab_data'][$i]['tab_success'] = 1;
                }
                else 
                {
                    $data['tab_data'][$i]['tab_success'] = 0;
                }
                    $data['tab_data'][$i]['tab_content']=$goals;
                    $i++;
                }
                elseif($cat['ID']==6)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                    if(count($enrollment_info)>0)
                    {
                        $data['tab_data'][$i]['tab_success'] = 1;
                    }
                    else 
                    {
                        $data['tab_data'][$i]['tab_success'] = 0;
                    }
                    $data['tab_data'][$i]['tab_content']=$enrollment_info;
                    $i++;
                }
                elseif($cat['ID']==7)
                {
                    $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
                    $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                    if(count($files_info)>0)
                    {
                        $data['tab_data'][$i]['tab_success'] = 1;
                    }
                    else 
                    {
                            $data['tab_data'][$i]['tab_success'] = 0;
                    }
                    $data['tab_data'][$i]['tab_content']=$files_info;
                    $custom_fields = array();
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE,SORT_ORDER FROM custom_fields WHERE SYSTEM_FIELD=\'N\' AND CATEGORY_ID=\'7\' ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM students WHERE STUDENT_ID='".$student_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);
                            $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
            //                $custom[$title][]=array('tab_name'=>$title,'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
                        }
                    }
                    $data['tab_data'][$i]['custom_fields_content'] = $custom_fields;
                $i++;
                }
                else 
                {
                    $data['custom_tab_data'][$j]['tab_name']=$cat['TITLE'];
                    $data['custom_tab_data'][$j]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
                    $data['custom_tab_data'][$j]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
                    $data['custom_tab_data'][$j]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
                    if(count($custom)>0)
                    {
                        if(array_key_exists($cat['TITLE'], $custom))
                        {
                            foreach($custom as $tab=>$val)
                            {
                                if($cat['TITLE'] == $tab)
                                {
                                    $data['custom_tab_data'][$j]['tab_success'] = 1;
                                    $data['custom_tab_data'][$j]['tab_content']=$val;
                                }
                            }
                        }
                        else 
                        {
                            $data['custom_tab_data'][$j]['tab_success'] = 0;
                            $data['custom_tab_data'][$j]['tab_content']=array();
                        }
                    }
                    $j++;
                }
            }
            //parent data
            $data['tab_data'][$i]['tab_name']='Parent Info';
            $data['tab_data'][$i]['tab_show']='1';
            $data['tab_data'][$i]['can_use']='Y';
            $data['tab_data'][$i]['can_edit']='N';
            $data['tab_data'][$i]['tab_content'] = $parent_data;
            if(count($parent_data)>0)
            {
                $data['tab_data'][$i]['tab_success'] = 1;
            }
            else 
            {
                $data['tab_data'][$i]['tab_success'] = 0;
            }
            $i++;

            if(count($data['tab_data'])>0)
            {
                $data['tab_data_success'] = 1;
            }
            else 
            {
                $data['tab_data_success'] = 0;
            }
            if(count($data['custom_tab_data'])>0)
            {
                $data['custom_tab_data_success'] = 1;
            }
            else 
            {
                $data['custom_tab_data_success'] = 0;
            }
            $ethnicity_RET = DBGet(DBQuery("SELECT ETHNICITY_ID,ETHNICITY_NAME FROM ethnicity ORDER BY ETHNICITY_ID")); 
            $ethnicity = array();
            $ethnicity[]=array('ETHNICITY_ID'=>'','ETHNICITY_NAME'=>'N/A');
            foreach($ethnicity_RET as $eth)
            {
                $ethnicity[]=$eth;
        }
            $data['ethnicity_data']=$ethnicity;
            if(count($ethnicity)>0)
            {
                $data['ethnicity_data_success'] = 1;
            }
            else 
            {
                $data['ethnicity_data_success'] = 0;
            }
            $lang_RET = DBGet(DBQuery("SELECT LANGUAGE_ID,LANGUAGE_NAME FROM language ORDER BY LANGUAGE_ID")); 
            $lang = array();
            $lang[]=array('LANGUAGE_ID'=>'','LANGUAGE_NAME'=>'N/A');
            foreach($lang_RET as $lng)
            {
            $lang[]=$lng;
            }
            $data['language_data']=$lang;
            if(count($lang)>0)
            {
                $data['language_data_success'] = 1;
            }
            else 
            {
                $data['language_data_success'] = 0;
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

function _makeAlertComments($value,$column)
{	global $THIS_RET,$table;

	if(!$THIS_RET['ID'])
		$THIS_RET['ID'] = 'new';
        return $value;
	//return TextInput($value,'values['.$table.']['.$THIS_RET['ID'].']['.$column.']','','size=40');

}

function _makeDate($value,$column)//not used
{
    global $THIS_RET;

	if($column=='START_DATE')
		$allow_na = false;
	else
		$allow_na = true;

	return DateInput($value,"schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]",'',true,$allow_na);

}
?>
