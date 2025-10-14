<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$parent_id = $_REQUEST['parent_id'];
$profile_id = $_REQUEST['profile_id'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $student_id=$_SESSION['student_id'] = $_REQUEST['student_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];

        $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
        $school_RET = DBGet(DBQuery($school_sql));
        $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];

        $categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM people_field_categories ORDER BY SORT_ORDER,TITLE"));
        $tab_sql = 'SELECT MODNAME,CAN_USE,CAN_EDIT FROM  profile_exceptions WHERE MODNAME LIKE \'users\/%category_id%\' AND CAN_USE=\'Y\' AND PROFILE_ID='.$profile_id;
        $tab_data = DBGet(DBQuery($tab_sql));
        $tab_ids = $can_use = $can_edit = array();
        foreach($tab_data as $tab)
        {
            $tab_no = explode('=',$tab['MODNAME']);
            $tab_ids[]=$tab_no[1];
            $can_use[$tab_no[1]]=$tab['CAN_USE'];
            $can_edit[$tab_no[1]]=$tab['CAN_EDIT'];
            if(!in_array($tab_no[1],array(1,2)))
            {
                $custom_tab_ids[] = $tab_no[1];
            }
        }

        if($_REQUEST['action_type']=='update_dynamic_info')
        {
            $title = urldecode($_REQUEST['title']);
            $fname = urldecode($_REQUEST['fname']);
            $mname = urldecode($_REQUEST['mname']);
            $lname = urldecode($_REQUEST['lname']);
            $email = urldecode($_REQUEST['email']);
            $hphone = urldecode($_REQUEST['hphone']);
            $wphone = urldecode($_REQUEST['wphone']);
            $cell = urldecode($_REQUEST['cell']);
            $is_disable = urldecode($_REQUEST['is_disable']);
            $profile = urldecode($_REQUEST['profile']);
            $password = urldecode($_REQUEST['password']);
            $username = urldecode($_REQUEST['username']);
            $up_data = json_decode($_REQUEST['details']);

            $profile_title=DBGet(DBQuery('SELECT PROFILE FROM user_profiles WHERE ID ='.$profile));
            $result=DBGet(DBQuery('SELECT STAFF_ID FROM people WHERE EMAIL=\''.$_REQUEST['email'].'\' AND STAFF_ID!='.$parent_id));    
            $res_stf=DBGet(DBQuery('SELECT STAFF_ID FROM staff WHERE EMAIL=\''.$_REQUEST['email'].'\''));
            $res_stu=DBGet(DBQuery('SELECT STUDENT_ID FROM students WHERE EMAIL=\''.$_REQUEST['email'].'\''));
            $res_pwd=DBGet(DBQuery('SELECT PASSWORD FROM login_authentication WHERE USER_ID='.$parent_id.' AND USERNAME = \''.$_REQUEST['username'].'\''));
            if(count($result)>0 || count($res_stf)>0 ||  count($res_stu)>0)
            {
                $flag = 1;
        }
            else
            {
                $flag = 0;
            }
            if($flag==0)
            {
                $sql = 'UPDATE people SET TITLE = \''.$title.'\',FIRST_NAME = \''.$fname.'\',LAST_NAME = \''.$lname.'\',MIDDLE_NAME = \''.$mname.'\',HOME_PHONE = \''.$hphone.'\',WORK_PHONE = \''.$wphone.'\',CELL_PHONE = \''.$cell.'\',EMAIL = \''.$email.'\',PROFILE = \''.$profile_title[1]['PROFILE'].'\',PROFILE_ID = \''.$profile.'\',IS_DISABLE = \''.$is_disable.'\' WHERE STAFF_ID = '.$parent_id;
                $ppl_qry =DBQuery($sql); 
                if(count($up_data)>0)
                {
                    $cond = array();
                    foreach($up_data as $cus)
                    {
                        $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
            }
                    if(count($cond)>0)
                    {
                        $sql = DBQuery('UPDATE people SET '.implode(' , ',$cond).' WHERE STAFF_ID = '.$parent_id);
        //                $success = 1;
        //                $data_msg = 'Data updated successfully';
                    }
        //            else 
        //            {
        //                $success = 0;
        //                $data_msg = 'Data update failed'; 
        //            }
                }
                if(count($res_pwd)>0)
                {
                    if($password != $res_pwd[1]['PASSWORD'])
                      $pwd = md5($password);
                    else 
                        $pwd = $password;
                }
                else 
                {
                    $pwd = md5($res_pwd);
                }
                $pwd_sql = 'UPDATE login_authentication SET PASSWORD = \''.$pwd.'\',PROFILE_ID ='.$profile.' WHERE USER_ID='.$parent_id.' AND USERNAME = \''.$_REQUEST['username'].'\'';
                $pwd_qry = DBQuery($pwd_sql); 
                $success = 1;
                $msg = 'Data successfully updated';
            }
            else 
            {
                $success = 0;
                $msg = 'Email is already taken';
            }
            $data['update_success'] = $success;
            $data['update_msg'] = $msg;
        }
        elseif($_REQUEST['action_type']=='update_address')
        {
            $addr = urldecode($_REQUEST['addr']);
            $strt = urldecode($_REQUEST['strt']);
            $city = urldecode($_REQUEST['city']);
            $state = urldecode($_REQUEST['state']);
            $zip = urldecode($_REQUEST['zip']); 
            $up_data = json_decode($_REQUEST['details']);

            $addr=DBQuery('UPDATE student_address SET STREET_ADDRESS_1 = \''.$addr.'\',STREET_ADDRESS_2  = \''.$strt.'\',CITY = \''.$city.'\',STATE = \''.$state.'\',ZIPCODE = \''.$zip.'\' WHERE STUDENT_ID = '.$student_id.' AND PEOPLE_ID='.$parent_id);
            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE people SET '.implode(' , ',$cond).' WHERE STAFF_ID = '.$parent_id);
        //                $success = 1;
        //                $data_msg = 'Data updated successfully';
                }
        //            else 
        //            {
        //                $success = 0;
        //                $data_msg = 'Data update failed'; 
        //            }
            }
            $success = 1;
            $msg = 'Data successfully updated';
            $data['update_success'] = $success;
            $data['update_msg'] = $msg;
        }
        elseif($_REQUEST['action_type']=='update_custom')
        {
            $up_data = json_decode($_REQUEST['details']);

            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE people SET '.implode(' , ',$cond).' WHERE STAFF_ID = '.$parent_id);
                    $success = 1;
                    $data_msg = 'Data updated successfully';
                }
                else 
                {
                   $success = 0;
                    $data_msg = 'Data update failed'; 
                }
                $data['update_success'] = $success;
                $data['update_msg'] = $data_msg;
            }
        }
        else 
        {
            $sql = 'SELECT s.*,USERNAME,PASSWORD,up.TITLE AS PROFILE,LAST_LOGIN,IS_DISABLE FROM people s,user_profiles up,login_authentication la WHERE s.STAFF_ID=la.USER_ID AND la.PROFILE_ID ='.$profile_id.' AND s.STAFF_ID=\''.$parent_id.'\' AND s.PROFILE_ID=up.ID';
        $QI = DBQuery($sql);
        $staff = DBGet($QI);
        //$staff = $staff[1];
        if(count($staff)>0)
        {
            $i=0;
            foreach($staff as $stu)
            {
                $general_info[$i]['TITLE']=$stu['TITLE'];
                $general_info[$i]['FIRST_NAME']=$stu['FIRST_NAME'];
                $general_info[$i]['MIDDLE_NAME']=$stu['MIDDLE_NAME'];
                $general_info[$i]['LAST_NAME']=$stu['LAST_NAME'];
                $general_info[$i]['EMAIL']=$stu['EMAIL'];
                $general_info[$i]['DISABLE_USER']=$stu['IS_DISABLE'];
                $general_info[$i]['LAST_LOGIN']=$stu['LAST_LOGIN'];
                $general_info[$i]['USER_ID']=$stu['STAFF_ID'];
                $general_info[$i]['HOME_PHONE']=$stu['HOME_PHONE'];
                $general_info[$i]['WORK_PHONE']=$stu['WORK_PHONE'];
                $general_info[$i]['CELL_PHONE']=$stu['CELL_PHONE'];
                $general_info[$i]['PROFILE']=$stu['PROFILE'];
                $general_info[$i]['PROFILE_ID']=$stu['PROFILE_ID'];
                $general_info[$i]['USERNAME']=$stu['USERNAME'];
                $general_info[$i]['PASSWORD']=$stu['PASSWORD'];
                $i++;
            }
        }
        else 
        {
            $general_info = array();
        }

        $_SESSION['staff_selected']=$staff[1]['STAFF_ID'];
            $addr=DBGet(DBQuery('SELECT STREET_ADDRESS_1 as ADDRESS,STREET_ADDRESS_2 as STREET,CITY,STATE,ZIPCODE FROM student_address WHERE STUDENT_ID = '.$student_id.' AND PEOPLE_ID='.$parent_id));
        //$addr=$addr[1];
        if(count($addr)>0)
        {
            $i=0;
            foreach($addr as $ad)
            {
                $address[$i]['ADDRESS']=$ad['ADDRESS'];
                $address[$i]['STREET']=$ad['STREET'];
                $address[$i]['CITY']=$ad['CITY'];
                $address[$i]['STATE']=$ad['STATE'];
                $address[$i]['ZIPCODE']=$ad['ZIPCODE'];
                $i++;
            }
        }
        else 
        {
            $address = array();
        }

        $sql='SELECT s.STUDENT_ID,CONCAT(s.FIRST_NAME, \' \' ,s.LAST_NAME) AS FULL_NAME,s.GENDER,gr.TITLE AS GRADE ,sc.TITLE AS SCHOOL FROM students s,student_enrollment ssm,school_gradelevels gr,schools sc,students_join_people sjp WHERE s.STUDENT_ID=ssm.STUDENT_ID AND s.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID='.$parent_id.' AND ssm.SYEAR='.UserSyear().' AND ssm.SCHOOL_ID='.UserSchool().' AND ssm.GRADE_ID=gr.ID AND ssm.SCHOOL_ID=sc.ID'; // AND (ssm.END_DATE IS NULL OR ssm.END_DATE =  \'0000-00-00\' OR ssm.END_DATE >=  \''.date('Y-m-d').'\')
        $students=DBGet(DBQuery($sql));
        if(count($students)>0)
        {
            $i = 0;
            foreach($students as $sti=>$std)
            {
                $get_relation=DBGet(DBQuery('SELECT RELATIONSHIP FROM students_join_people WHERE STUDENT_ID='.$std['STUDENT_ID'].' AND PERSON_ID='.$parent_id));
                $stu_data[$i]['FULL_NAME']=$std['FULL_NAME'];
                $stu_data[$i]['GRADE']=$std['GRADE'];
                $stu_data[$i]['SCHOOL']=$std['SCHOOL'];
                $stu_data[$i]['RELATIONSHIP']=$get_relation[1]['RELATIONSHIP'];
                $i++;
            }
        }
        else 
        {
            $stu_data = array();
        }
            $custom = array();
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
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM people_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID='.$cat_id.' ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM people WHERE STAFF_ID='".$parent_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);
                            if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                            {
                                $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                            }
//                            if($fields['SELECT_OPTIONS']!='')
//                            {
                                $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                                if($student[1]["CUSTOM_".$fields['ID']]!=null)
                                {
                                    if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                       $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                                }
                            }
                            $custom[$title][]=array('tab_name'=>$title,'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
                        }
                        $title='';
                    }
                }
            }
            $i=0;$j=0;
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
        //            $data['tab_data'][$i]['tab_content'][0]['student_info']=$stu_data;
                    $custom_fields = array();
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM people_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=1 ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM people WHERE STAFF_ID='".$parent_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);;
                            if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                            {
                                $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                            }
//                            if($fields['SELECT_OPTIONS']!='')
//                            {
                                $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                                if($student[1]["CUSTOM_".$fields['ID']]!=null)
                                {
                                    if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                       $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                                }
                            }
                            $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
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
                    if(count($address)>0)
                    {
                        $data['tab_data'][$i]['tab_success'] = 1;
                    }
                    else 
                    {
                        $data['tab_data'][$i]['tab_success'] = 0;
                    }
                    $data['tab_data'][$i]['tab_content']=$address;
                    $custom_fields = array();
                    $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM people_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=2 ORDER BY SORT_ORDER,TITLE'));
                    if(Count($fields_RET)>0)
                    {
                        foreach($fields_RET as $fields)
                        {
                            $sql = "SELECT CUSTOM_".$fields['ID']." FROM people WHERE STAFF_ID='".$parent_id."'";
                            $QI = DBQuery($sql);
                            $student = DBGet($QI);
                            if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                            {
                                $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                            }
//                            if($fields['SELECT_OPTIONS']!='')
//                            {
                                $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                                if($student[1]["CUSTOM_".$fields['ID']]!=null)
                                {
                                    if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                       $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                                }
                            }
                            $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
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
            $data['tab_data'][$i]['tab_name']='Student Info';
            $data['tab_data'][$i]['tab_show']='1';
            $data['tab_data'][$i]['can_use']='Y';
            $data['tab_data'][$i]['can_edit']='Y';
            if(count($general_info)>0)
            {
                $data['tab_data'][$i]['tab_success'] = 1;
            }
            else 
            {
                $data['tab_data'][$i]['tab_success'] = 0;
            }
            $data['tab_data'][$i]['tab_content']=$stu_data;
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
            $options_RET = DBGet(DBQuery('SELECT ID,TITLE FROM user_profiles WHERE PROFILE = \'parent\''));
            $profile = array();
            foreach($options_RET as $rel)
            {
                if($rel['TITLE']!='' && !in_array($rel['TITLE'],$profile))
                    $profile[]= array('ID'=>$rel['ID'],'VALUE'=>$rel['TITLE']);  
            }
            $data['profiles'] = $profile;
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
        /*if(count($general_info)>0)
        {
            $general_info_success = 1;
        }
        else 
        {
            $general_info_success = 0;
        }
        if(count($address)>0)
        {
            $address_success = 1;
        }
        else 
        {
            $address_success = 0;
        }
        if(count($stu_data)>0)
        {
            $stu_data_success = 1;
        }
        else 
        {
            $stu_data_success = 0;
        }
        if($general_info_success == 1 || $address_success == 1 || $stu_data_success == 1)
        {
            $success = 1;
        }
        else 
        {
            $success = 0;
        }
        $data = array('selected_student'=>$student_id,
            'success'=> $success,
            'general_info'=>$general_info,
            'general_info_success' => $general_info_success,
            'address'=>$address,
            'address_success'=>$address_success,
            'stu_data'=>$stu_data,
            'stu_data_success'=>$stu_data_success
                );*/
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