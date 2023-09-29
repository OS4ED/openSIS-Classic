<?php
include '../../Data.php';
include '../function/DbGetFnc.php';
include '../function/ParamLib.php';
include '../function/app_functions.php';
include '../function/function.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $parent_id = $_REQUEST['parent_id'];
$profile_id=$_REQUEST['profile_id'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$parent_id && $auth_data['user_profile']=='parent')
    {
        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $student_id = $_SESSION['student_id'] = $_REQUEST['student_id'];
        $categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM student_field_categories ORDER BY SORT_ORDER,TITLE"));
        $tab_sql = 'SELECT MODNAME,CAN_USE,CAN_EDIT FROM  profile_exceptions WHERE MODNAME LIKE \'students\/%category_id%\' AND CAN_USE=\'Y\' AND PROFILE_ID='.$profile_id;
        $tab_data = DBGet(DBQuery($tab_sql));
        $tab_ids = $can_use = $can_edit = array();
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
        //$i=0;
        //foreach($categories_RET as $category)
        //{
        //    $tabs_data[$i]['tab_title']=$category['TITLE'];
        //    $tabs_data[$i]['tab_show']=(in_array($category['ID'],$tab_ids))?'1':'0';
        //    $tabs_data[$i]['can_use']=($can_use[$category['ID']]=='Y')?'Y':'N';
        //    $tabs_data[$i]['can_edit']=($can_edit[$category['ID']]=='Y')?'Y':'N';
        //    $i++;
        //}

        //$data['tab_names_data'] = $tabs_data;
        /*
        //$res=  DBGet(DBQuery('SELECT * FROM student_enrollment WHERE student_id='.$_REQUEST['student_id'].''));
        //if($res[1]['CALENDAR_ID']=='' || $res[1]['CALENDAR_ID']==NULL)
        //{
        //    $sid=$_REQUEST['stu_id'];   
        //    DBQuery('DELETE FROM students WHERE STUDENT_ID='.$sid);
        //    DBQuery('DELETE FROM student_enrollment WHERE STUDENT_ID='.$sid);
        //    $_REQUEST['student_id']='new';
        //}
        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\''.$_REQUEST['student_id'].'\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));

        $count_student_RET=DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM students"));
        */
        if($_REQUEST['action_type']=='upload_file')
        {
            $path = $_REQUEST['filename'];

            $temp=$student_id.'-'.$_FILES['file']['tmp_name'];
            $file_path = "../../assets/studentfiles/";
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
            $file_path = str_replace('webservice/parent','',getcwd()).'assets/'.$path[1];
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
        elseif($_REQUEST['action_type']=='update_general_info')
        {
            $up_data = json_decode($_REQUEST['details']);

            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    if($cus->column == 'PRIMARY_LANGUAGE')
                    {
                        $cond[] = 'LANGUAGE = \''.$cus->value.'\'';
                    }
                    else 
                    {
                        $cond[] = $cus->column.' = \''.$cus->value.'\'';
                    }
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE students SET '.implode(' , ',$cond).' WHERE student_id = '.$student_id);
                    $success = 1;
                    $data_msg = 'Data updated successfully';
                }
            }
            else 
            {
                $success = 0;
                $data_msg = 'Data update failed';
            }
            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='update_home_addr')
        {
            $home_addr_id = $_REQUEST['home_addr_id'];
            $home_cond[] = 'street_address_1 = \''.urldecode($_REQUEST['addr']).'\'';
            $home_cond[] = 'street_address_2 = \''.urldecode($_REQUEST['street']).'\'';
            $home_cond[] = 'city = \''.$_REQUEST['city'].'\'';
            $home_cond[] = 'state = \''.$_REQUEST['state'].'\'';
            $home_cond[] = 'zipcode = \''.$_REQUEST['zip'].'\'';
            $home_cond[] = 'bus_pickup = \''.$_REQUEST['bus_pickup'].'\'';
            $home_cond[] = 'bus_dropoff = \''.$_REQUEST['bus_drop'].'\'';
            $home_cond[] = 'bus_no = \''.$_REQUEST['bus_no'].'\'';
            if($home_addr_id!='')
            $sql = DBQuery('UPDATE student_address SET '.implode(' , ',$home_cond).' WHERE TYPE=\'Home Address\' AND ID = '.$home_addr_id.' AND STUDENT_ID=\''.$student_id.'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' ');
            else 
                $sql = DBQuery('INSERT INTO student_address SET '.implode(' , ',$home_cond).' ,TYPE=\'Home Address\' , STUDENT_ID=\''.$student_id.'\' , SCHOOL_ID=\''.UserSchool().'\',SYEAR=\''.  UserSyear().'\' ');
                        $success = 1;
            $data_msg = 'Data updated successfully';

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
                    }
        elseif($_REQUEST['action_type']=='update_mail_addr')
                    {
            $mail_addr_id = $_REQUEST['mail_addr_id'];
            $mail_cond[] = 'street_address_1 = \''.urldecode($_REQUEST['addr']).'\'';
            $mail_cond[] = 'street_address_2 = \''.urldecode($_REQUEST['street']).'\'';
            $mail_cond[] = 'city = \''.$_REQUEST['city'].'\'';
            $mail_cond[] = 'state = \''.$_REQUEST['state'].'\'';
            $mail_cond[] = 'zipcode = \''.$_REQUEST['zip'].'\'';
            $mail_cond[] = 'bus_pickup = \''.$_REQUEST['bus_pickup'].'\'';
            $mail_cond[] = 'bus_dropoff = \''.$_REQUEST['bus_drop'].'\'';
            $mail_cond[] = 'bus_no = \''.$_REQUEST['bus_no'].'\'';
            if($mail_addr_id!='')
            $sql = DBQuery('UPDATE student_address SET '.implode(' , ',$mail_cond).' WHERE TYPE=\'Mail\' AND ID = '.$mail_addr_id.' AND STUDENT_ID=\''.$student_id.'\' AND SCHOOL_ID=\''.  UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' ');
            else 
            $sql = DBQuery('INSERT INTO student_address SET '.implode(' , ',$mail_cond).' ,TYPE=\'Mail\' , STUDENT_ID=\''.$student_id.'\' , SCHOOL_ID=\''.  UserSchool().'\',SYEAR=\''.  UserSyear().'\' ');
                        $success = 1;
            $data_msg = 'Data updated successfully';

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
                    }
        elseif($_REQUEST['action_type']=='update_primary_emergecy_contact')
        {
            $pr_em_cont_id = $_REQUEST['pr_em_cont_id'];
            $pr_em_cond[] = 'first_name = \''.$_REQUEST['fname'].'\'';
            $pr_em_cond[] = 'last_name = \''.$_REQUEST['lname'].'\'';
            $pr_em_cond[] = 'middle_name = \''.$_REQUEST['mname'].'\'';
            $pr_em_cond[] = 'home_phone = \''.$_REQUEST['hphone'].'\'';
            $pr_em_cond[] = 'work_phone = \''.$_REQUEST['wphone'].'\'';
            $pr_em_cond[] = 'cell_phone = \''.$_REQUEST['cell'].'\'';
            $pr_em_cond[] = 'email = \''.$_REQUEST['email'].'\'';
            $pr_em_cond[] = 'custody = \''.$_REQUEST['custody'].'\'';
            $prim_addr_id = $_REQUEST['pr_em_addr_id'];
            $pr_addr_cond[] = 'street_address_1 = \''.urldecode($_REQUEST['addr']).'\'';
            $pr_addr_cond[] = 'street_address_2 = \''.urldecode($_REQUEST['street']).'\'';
            $pr_addr_cond[] = 'city = \''.$_REQUEST['city'].'\'';
            $pr_addr_cond[] = 'state = \''.$_REQUEST['state'].'\'';
            $pr_addr_cond[] = 'zipcode = \''.$_REQUEST['zip'].'\'';
        //    $pr_addr_cond[] = 'bus_pickup = \''.$_REQUEST['bus_pickup'].'\'';
        //    $pr_addr_cond[] = 'bus_dropoff = \''.$_REQUEST['bus_drop'].'\'';
        //    $pr_addr_cond[] = 'bus_no = \''.$_REQUEST['bus_no'].'\'';
            $relation = $_REQUEST['relation'];
                    if(count($pr_em_cond)>0 && $pr_em_cont_id!='')
                    {
                        $sql = DBQuery('UPDATE people SET '.implode(' , ',$pr_em_cond).' WHERE STAFF_ID = '.$pr_em_cont_id.' AND CURRENT_SCHOOL_ID=\''.  UserSchool().'\' ');
                $cont_success = 1;
                        $cont_data_msg = 'Data updated successfully';
                    }
                    else 
                    {
                        $sql = DBQuery('INSERT INTO people SET '.implode(' , ',$pr_em_cond).' , CURRENT_SCHOOL_ID=\''.  UserSchool().'\' ');
                        $pr_em_cont_id = mysqli_insert_id($connection);
                        $cont_success = 1;
                        $cont_data_msg = 'Data updated successfully';
                    }

            if(count($pr_addr_cond)>0 && $prim_addr_id!='')
            {
                $sql = DBQuery('UPDATE student_address SET '.implode(' , ',$pr_addr_cond).' WHERE TYPE=\'Primary\' AND ID = '.$prim_addr_id.' AND PEOPLE_ID = \''.$pr_em_cont_id.'\' AND STUDENT_ID=\''.$student_id.'\' AND SCHOOL_ID=\''.  UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' ');
                $sql = DBQuery('UPDATE  students_join_people SET RELATIONSHIP = \''.$relation.'\' WHERE EMERGENCY_TYPE =\'Primary\' AND STUDENT_ID = '.$student_id.' AND PERSON_ID = \''.$pr_em_cont_id.'\'');
                $addr_success = 1;
                $addr_data_msg = 'Data updated successfully';
            }
            else 
            {
                $sql = DBQuery('INSERT INTO student_address SET '.implode(' , ',$pr_addr_cond).' , TYPE=\'Primary\' , PEOPLE_ID = \''.$pr_em_cont_id.'\' , STUDENT_ID=\''.$student_id.'\' , SCHOOL_ID=\''.  UserSchool().'\',SYEAR=\''.  UserSyear().'\' ');
                $sql = DBQuery('INSERT INTO students_join_people SET RELATIONSHIP = \''.$relation.'\' , EMERGENCY_TYPE =\'Primary\' , STUDENT_ID = '.$student_id.' , PERSON_ID = \''.$pr_em_cont_id.'\'');
                $addr_success = 1;
                $addr_data_msg = 'Data updated successfully';
            }
            $data['cont_update_success'] = $cont_success;
            $data['cont_update_msg'] = $cont_data_msg;
            $data['addr_update_success'] = $addr_success;
            $data['addr_update_msg'] = $addr_data_msg;
        }
        elseif($_REQUEST['action_type']=='update_secondary_emergecy_contact')
        {
            $sec_em_cont_id = $_REQUEST['sec_em_cont_id'];
            $sec_em_cond[] = 'first_name = \''.$_REQUEST['fname'].'\'';
            $sec_em_cond[] = 'last_name = \''.$_REQUEST['lname'].'\'';
            $sec_em_cond[] = 'middle_name = \''.$_REQUEST['mname'].'\'';
            $sec_em_cond[] = 'home_phone = \''.$_REQUEST['hphone'].'\'';
            $sec_em_cond[] = 'work_phone = \''.$_REQUEST['wphone'].'\'';
            $sec_em_cond[] = 'cell_phone = \''.$_REQUEST['cell'].'\'';
            $sec_em_cond[] = 'email = \''.$_REQUEST['email'].'\'';
            $sec_em_cond[] = 'custody = \''.$_REQUEST['custody'].'\'';
            $sec_addr_id = $_REQUEST['sec_em_addr_id'];
            $sec_addr_cond[] = 'street_address_1 = \''.urldecode($_REQUEST['addr']).'\'';
            $sec_addr_cond[] = 'street_address_2 = \''.urldecode($_REQUEST['street']).'\'';
            $sec_addr_cond[] = 'city = \''.$_REQUEST['city'].'\'';
            $sec_addr_cond[] = 'state = \''.$_REQUEST['state'].'\'';
            $sec_addr_cond[] = 'zipcode = \''.$_REQUEST['zip'].'\'';
            $sec_addr_cond[] = 'bus_pickup = \''.$_REQUEST['bus_pickup'].'\'';
            $sec_addr_cond[] = 'bus_dropoff = \''.$_REQUEST['bus_drop'].'\'';
            $sec_addr_cond[] = 'bus_no = \''.$_REQUEST['bus_no'].'\'';
            $relation = $_REQUEST['relation'];
                    if(count($sec_em_cond)>0 && $sec_em_cont_id!='')
                    {
                        $sql = DBQuery('UPDATE people SET '.implode(' , ',$sec_em_cond).' WHERE STAFF_ID = '.$sec_em_cont_id.' AND CURRENT_SCHOOL_ID=\''.  UserSchool().'\' ');
                $cont_success = 1;
                    $cont_data_msg = 'Data updated successfully';
                    }
                else 
                    {
                    $sql = DBQuery('INSERT INTO people SET '.implode(' , ',$sec_em_cond).', CURRENT_SCHOOL_ID=\''.  UserSchool().'\' ');
                    $sec_em_cont_id = mysqli_insert_id($connection);
                    $cont_success = 1;
                    $cont_data_msg = 'Data updated successfully';
                }
                if(count($sec_addr_cond)>0 && $sec_addr_id!='')
                {
                        $sql = DBQuery('UPDATE student_address SET '.implode(' , ',$sec_addr_cond).' WHERE TYPE=\'Secondary\' AND ID = '.$sec_addr_id.' AND PEOPLE_ID = \''.$sec_em_cont_id.'\' AND STUDENT_ID=\''.$student_id.'\' AND SCHOOL_ID=\''.  UserSchool().'\' ');
                $sql = DBQuery('UPDATE  students_join_people SET RELATIONSHIP = \''.$relation.'\' WHERE EMERGENCY_TYPE =\'Secondary\' AND STUDENT_ID = '.$student_id.' AND PERSON_ID = \''.$sec_em_cont_id.'\'');
                $addr_success = 1;
                    $addr_data_msg = 'Data updated successfully';
                    }
                else 
                {
                    $sql = DBQuery('INSERT INTO student_address SET '.implode(' , ',$sec_addr_cond).' , TYPE=\'Secondary\' , PEOPLE_ID = \''.$sec_em_cont_id.'\', STUDENT_ID=\''.$student_id.'\', SCHOOL_ID=\''.  UserSchool().'\', SYEAR=\''.  UserSyear().'\' ');
                    $sql = DBQuery('INSERT INTO students_join_people SET RELATIONSHIP = \''.$relation.'\' , EMERGENCY_TYPE =\'Secondary\', STUDENT_ID = '.$student_id.', PERSON_ID = \''.$sec_em_cont_id.'\'');
                    $addr_success = 1;
                    $addr_data_msg = 'Data updated successfully';
                }
            $data['cont_update_success'] = $cont_success;
            $data['cont_update_msg'] = $cont_data_msg;
            $data['addr_update_success'] = $addr_success;
            $data['addr_update_msg'] = $addr_data_msg;
                }
        elseif($_REQUEST['action_type']=='update_other_cont')
        {
            $otr_cont_id = $_REQUEST['otr_cont_id'];
            $otr_cont_addr_id = $_REQUEST['otr_cont_addr_id'];
            $title = $_REQUEST['title'];
            $fname = $_REQUEST['fname'];
            $lname = $_REQUEST['lname'];
            $mname = $_REQUEST['mname'];
            $hphn = $_REQUEST['hphone'];
            $wphn = $_REQUEST['wphone'];
            $cphn = $_REQUEST['cell'];
            $email = urldecode($_REQUEST['email']);
            $custody = $_REQUEST['custody'];
            $addr = urldecode($_REQUEST['addr']);
            $street = urldecode($_REQUEST['street']);
            $city = urldecode($_REQUEST['city']);
            $state = urldecode($_REQUEST['state']);
            $zip = $_REQUEST['zip'];
            $bus_pickup = $_REQUEST['bus_pickup'];
            $bus_drop = $_REQUEST['bus_drop'];
            $bus_no = $_REQUEST['bus_no'];
            $relation = $_REQUEST['relation'];
            $is_em = $_REQUEST['is_em'];

            $sql_peo='UPDATE people SET TITLE = \''.$title.'\',FIRST_NAME = \''.$fname.'\',LAST_NAME = \''.$lname.'\',MIDDLE_NAME = \''.$mname.'\',HOME_PHONE = \''.$hphn.'\',WORK_PHONE = \''.$wphn.'\',CELL_PHONE = \''.$cphn.'\',EMAIL = \''.$email.'\',CUSTODY = \''.$custody.'\' WHERE STAFF_ID = '.$otr_cont_id;
            $peo_ins =  DBQuery($sql_peo);

            $sql_peo_add='UPDATE student_address SET STREET_ADDRESS_1 = \''.$addr.'\',STREET_ADDRESS_2 = \''.$street.'\',CITY = \''.$city.'\',STATE = \''.$state.'\',ZIPCODE = \''.$zip.'\',BUS_PICKUP = \''.$bus_pickup.'\',BUS_DROPOFF = \''.$bus_drop.'\',BUS_NO = \''.$bus_no.'\',TYPE = \''.$em_type.'\' WHERE PEOPLE_ID = '.$otr_cont_id.' AND STUDENT_ID = '.$student_id.' AND ID = '.$otr_cont_addr_id;
            $peo_add_ins =  DBQuery($sql_peo_add);

            $sql_sjp='UPDATE students_join_people SET IS_EMERGENCY = \''.$is_em.'\',RELATIONSHIP = \''.$relation.'\' WHERE STUDENT_ID = '.$student_id.' AND PERSON_ID ='.$otr_cont_id;
            $sjp_ins = DBQuery($sql_sjp);

            $data['update_success'] = 1; // this was initially written as ins_success but changed to update_success as requested by the Android Team on 28.11.2016 
            $data['update_msg'] = 'nil';
        }
        elseif($_REQUEST['action_type']=='add_other_cont')
        {
                $title = $_REQUEST['title'];
                $fname = $_REQUEST['fname'];
                $lname = $_REQUEST['lname'];
                $mname = $_REQUEST['mname'];
                $hphn = $_REQUEST['hphone'];
                $wphn = $_REQUEST['wphone'];
                $cphn = $_REQUEST['cell'];
                $email = urldecode($_REQUEST['email']);
                $custody = $_REQUEST['custody'];
                $addr = urldecode($_REQUEST['addr']);
                $street = urldecode($_REQUEST['street']);
                $city = urldecode($_REQUEST['city']);
                $state = urldecode($_REQUEST['state']);
                $zip = $_REQUEST['zip'];
                $bus_pickup = $_REQUEST['bus_pickup'];
                $bus_drop = $_REQUEST['bus_drop'];
                $bus_no = $_REQUEST['bus_no'];
                $relation = $_REQUEST['relation'];
                $is_em = $_REQUEST['is_em'];
                $em_type = 'Other';
            $profile_id_otr = 4;
            $profile='parent';

                $sql_peo='INSERT INTO people (CURRENT_SCHOOL_ID,TITLE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,HOME_PHONE,WORK_PHONE,CELL_PHONE,EMAIL,CUSTODY,PROFILE,PROFILE_ID,IS_DISABLE) '
                    . 'VALUES ('.UserSchool().',\''.$title.'\',\''.$fname.'\',\''.$lname.'\',\''.$mname.'\',\''.$hphn.'\',\''.$wphn.'\',\''.$cphn.'\',\''.$email.'\',\''.$custody.'\',\''.$profile.'\','.$profile_id_otr.',\''.$is_disable.'\')';
                $peo_ins =  DBQuery($sql_peo);
                $people_id = mysqli_insert_id($connection);
                $sql_peo_add='INSERT INTO student_address (STUDENT_ID,SYEAR,SCHOOL_ID,STREET_ADDRESS_1,STREET_ADDRESS_2,CITY,STATE,ZIPCODE,BUS_PICKUP,BUS_DROPOFF,BUS_NO,TYPE,PEOPLE_ID) '
                    . 'VALUES ('.$student_id.',\''.UserSyear().'\',\''.UserSchool().'\',\''.$addr.'\',\''.$street.'\',\''.$city.'\',\''.$state.'\',\''.$zip.'\',\''.$bus_pickup.'\',\''.$bus_drop.'\',\''.$bus_no.'\',\''.$em_type.'\',\''.$people_id.'\')';
                $peo_add_ins =  DBQuery($sql_peo_add);
                $sql_sjp='INSERT INTO students_join_people (STUDENT_ID,EMERGENCY_TYPE,PERSON_ID,IS_EMERGENCY,RELATIONSHIP) '
                        . 'VALUES ('.$student_id.',\''.$em_type.'\','.$people_id.',\''.$is_em.'\',\''.$relation.'\')';
                $sjp_ins = DBQuery($sql_sjp);

            $data['update_success'] = 1; // this was initially written as ins_success but changed to update_success as requested by the Android Team on 28.11.2016 
            $data['update_msg'] = 'nil';
        }
        elseif($_REQUEST['action_type']=='del_other_cont')
        {
            $otr_cont_id = $_REQUEST['otr_cont_id'];
            $otr_cont_addr_id = $_REQUEST['otr_cont_addr_id']; 
            $sql_peo='DELETE FROM people WHERE STAFF_ID = '.$otr_cont_id;
            $peo_ins =  DBQuery($sql_peo);

            $sql_peo_add='DELETE FROM student_address WHERE PEOPLE_ID = '.$otr_cont_id.' AND STUDENT_ID = '.$student_id.' AND ID = '.$otr_cont_addr_id;
            $peo_add_ins =  DBQuery($sql_peo_add);

            $sql_sjp='DELETE FROM students_join_people WHERE STUDENT_ID = '.$student_id.' AND PERSON_ID ='.$otr_cont_id;
            $sjp_ins = DBQuery($sql_sjp);

            $data['update_success'] = 1; 
            $data['update_msg'] = 'nil';
        }
        elseif($_REQUEST['action_type']=='update_medical')
        {
            $physician = urldecode($_REQUEST['physician']);
            $phy_phn = urldecode($_REQUEST['phy_phn']);
            $pref_hospital = urldecode($_REQUEST['pref_hospital']);
            $stu_Medical_info=  DBQuery('UPDATE medical_info SET PHYSICIAN = \''.$physician.'\',PHYSICIAN_PHONE = \''.$phy_phn.'\',PREFERRED_HOSPITAL = \''.$pref_hospital.'\' WHERE STUDENT_ID='.$student_id.' AND SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool().'');
            $success = 1;
            $data_msg = 'Data updated successfully';    
            if($_REQUEST['details']!='')
            {
                $custom_data = json_decode($_REQUEST['details']);
                if(count($custom_data)>0)
                {
                    $cond = array();
                    foreach($custom_data as $cus)
                    {
                        $cond[] = $cus->column.' = \''.$cus->value.'\'';
                    }
                    if(count($cond)>0)
                    {
                        $sql = DBQuery('UPDATE students SET '.implode(' , ',$cond).' WHERE student_id = '.$student_id);
                        $success = 1;
                        $data_msg = 'Data updated successfully';
                    }
                }
            }
            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='add_medical_notes')
        {
            $date = urldecode($_REQUEST['comm_date']);
            $comment = urldecode($_REQUEST['comment']);
            $stu_Medical_info=  DBQuery('INSERT INTO student_medical_notes (STUDENT_ID,DOCTORS_NOTE_DATE,DOCTORS_NOTE_COMMENTS) VALUES ('.$student_id.',\''.$date.'\',\''.$comment.'\')');
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='update_medical_notes')
        {
            $note_id = $_REQUEST['id'];
            $date = urldecode($_REQUEST['comm_date']);
            $comment = urldecode($_REQUEST['comment']);
            $stu_Medical_info=  DBQuery('UPDATE student_medical_notes SET DOCTORS_NOTE_DATE = \''.$date.'\',DOCTORS_NOTE_COMMENTS = \''.$comment.'\' WHERE ID = '.$note_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='delete_medical_notes')
        {
            $note_id = $_REQUEST['id'];
            $stu_Medical_info=  DBQuery('DELETE FROM student_medical_notes WHERE ID = '.$note_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data deleted successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='add_immunization')
        {
            $type = urldecode($_REQUEST['type']);
            $date = urldecode($_REQUEST['imm_date']);
            $comment = urldecode($_REQUEST['comment']);
                $stu_Medical_info=  DBQuery('INSERT INTO student_immunization (STUDENT_ID,TYPE,MEDICAL_DATE,COMMENTS) VALUES ('.$student_id.',\''.$type.'\',\''.$date.'\',\''.$comment.'\')');
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='update_immunization')
        {
            $imm_id = $_REQUEST['id'];
            $type = urldecode($_REQUEST['type']);
            $date = urldecode($_REQUEST['imm_date']);
            $comment = urldecode($_REQUEST['comment']);
            $stu_Medical_info=  DBQuery('UPDATE student_immunization SET TYPE = \''.$type.'\',MEDICAL_DATE = \''.$date.'\',COMMENTS = \''.$comment.'\' WHERE ID = '.$imm_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='delete_immunization')
        {
            $imm_id = $_REQUEST['id'];
            $stu_Medical_info=  DBQuery('DELETE FROM student_immunization WHERE ID = '.$imm_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data deleted successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='add_medical_alerts')
        {
            $date = urldecode($_REQUEST['alert_date']);
            $title = urldecode($_REQUEST['title']);
            $stu_Medical_info=  DBQuery('INSERT INTO student_medical_alerts (STUDENT_ID,ALERT_DATE,TITLE) VALUES ('.$student_id.',\''.$date.'\',\''.$title.'\')');
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='update_medical_alerts')
        {
            $alrt_id = $_REQUEST['id'];
            $date = urldecode($_REQUEST['alert_date']);
            $title = urldecode($_REQUEST['title']);
            $stu_Medical_info=  DBQuery('UPDATE student_medical_alerts SET ALERT_DATE = \''.$date.'\',TITLE = \''.$title.'\' WHERE ID = '.$alrt_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='delete_medical_alerts')
        {
            $alrt_id = $_REQUEST['id'];
            $stu_Medical_info=  DBQuery('DELETE FROM student_medical_alerts WHERE ID = '.$alrt_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data deleted successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='add_nurse_visit')
        {
            $sch_date = urldecode($_REQUEST['sch_date']);
            $time_in = urldecode($_REQUEST['time_in']);
            $time_out = urldecode($_REQUEST['time_out']);
            $reason = urldecode($_REQUEST['reason']);
            $result = urldecode($_REQUEST['result']);
            $comment = urldecode($_REQUEST['comment']);
            $stu_Medical_info=  DBQuery('INSERT INTO student_medical_visits (STUDENT_ID,SCHOOL_DATE,TIME_IN,TIME_OUT,REASON,RESULT,COMMENTS) VALUES ('.$student_id.',\''.$sch_date.'\',\''.$time_in.'\',\''.$time_out.'\',\''.$reason.'\',\''.$result.'\',\''.$comment.'\')');
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }
        elseif($_REQUEST['action_type']=='update_nurse_visit')
        {
            $visit_id = $_REQUEST['id'];
            $sch_date = urldecode($_REQUEST['sch_date']);
            $time_in = urldecode($_REQUEST['time_in']);
            $time_out = urldecode($_REQUEST['time_out']);
            $reason = urldecode($_REQUEST['reason']);
            $result = urldecode($_REQUEST['result']);
            $comment = urldecode($_REQUEST['comment']);
            $stu_Medical_info=  DBQuery('UPDATE student_medical_visits SET SCHOOL_DATE = \''.$sch_date.'\',TIME_IN = \''.$time_in.'\',TIME_OUT = \''.$time_out.'\',REASON = \''.$reason.'\',RESULT = \''.$result.'\',COMMENTS = \''.$comment.'\' WHERE ID = '.$visit_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data updated successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
        }   
        elseif($_REQUEST['action_type']=='delete_nurse_visit')
        {
            $visit_id = $_REQUEST['id'];
            $stu_Medical_info=  DBQuery('DELETE FROM student_medical_visits WHERE ID = '.$visit_id.' AND STUDENT_ID = '.$student_id);
            $success = 1;
            $data_msg = 'Data deleted successfully';    

            $data['update_success'] = $success;
            $data['update_msg'] = $data_msg;
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
        
        $path ='../../assets/studentphotos/';

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
        //$options_RET = DBGet(DBQuery('SELECT DISTINCT RELATIONSHIP FROM students_join_people'));
        $relation_options_arr = array('Father','Mother','Step Mother','Step Father','Grandmother','Grandfather','Legal Guardian','Other Family Member','---');
        //foreach($options_RET as $rel)
        //{
        //    if($rel['RELATIONSHIP']!='' && !in_array($rel['RELATIONSHIP'],$relation_options_arr))
        //        $relation_options_arr[]= $rel['RELATIONSHIP'];  
        //}
        //$relation_options = implode(',',$relation_options_arr);
        $i = 0;
        foreach($student as $stu)
        {
            $stuPicPath=$path.$student_id.".JPG";
            if(file_exists($stuPicPath))
                $general_info[$i]['PHOTO']=$htpath.$student_id.".JPG";
            else 
                $general_info[$i]['PHOTO']="";
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

            $grade_data = DBGet(DBQuery("SELECT TITLE FROM school_gradelevels WHERE SCHOOL_ID='".  UserSchool()."' AND ID=".$stu['GRADE_ID']));
            $general_info[$i]['GRADE_NAME']=$grade_data[1]['TITLE'];

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
                                           sa.TYPE=\'Home Address\' AND sa.STUDENT_ID=\''.$student_id.'\' AND sa.SCHOOL_ID=\''.  UserSchool().'\' '));
            if(count($h_addr)<1)
            {
                $h_addr[1]['ADDRESS_ID']='';
                $h_addr[1]['ADDRESS']='';
                $h_addr[1]['STREET']='';
                $h_addr[1]['CITY']='';
                $h_addr[1]['STATE']='';
                $h_addr[1]['ZIPCODE']='';
                $h_addr[1]['BUS_PICKUP']='';
                $h_addr[1]['BUS_DROPOFF']='';
                $h_addr[1]['BUS_NO']='';
            }
            foreach($h_addr as $hdd)
            {
                $home_addr[]=$hdd;
            }
            $address[$i]['HOME_ADDRESS'] = $home_addr;        
            $pri_par_id=  DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID='.$student_id.' AND EMERGENCY_TYPE=\'Primary\''));
            if(count($pri_par_id)>0)
            {
               $p_addr=DBGet(DBQuery('SELECT p.STAFF_ID as CONTACT_ID,p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,p.HOME_PHONE,p.WORK_PHONE,p.CELL_PHONE,p.EMAIL,p.CUSTODY,
                                  sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$pri_par_id[1]['PERSON_ID'].'\' AND sa.STUDENT_ID =\''.$student_id.'\'  AND sa.PEOPLE_ID IS NOT NULL AND sa.type=\'Primary\''));
               if(count($p_addr)<1)
               {
                   $p_addr[1]['CONTACT_ID']='';
                   $p_addr[1]['FIRST_NAME']='';
                   $p_addr[1]['MIDDLE_NAME']='';
                   $p_addr[1]['LAST_NAME']='';
                   $p_addr[1]['HOME_PHONE']='';
                   $p_addr[1]['WORK_PHONE']='';
                   $p_addr[1]['CELL_PHONE']='';
                   $p_addr[1]['EMAIL']='';
                   $p_addr[1]['CUSTODY']='';
                   $p_addr[1]['ADDRESS_ID']='';
                   $p_addr[1]['ADDRESS']='';
                   $p_addr[1]['STREET']='';
                   $p_addr[1]['CITY']='';
                   $p_addr[1]['STATE']='';
                   $p_addr[1]['ZIPCODE']='';
                   $p_addr[1]['BUS_PICKUP']='';
                   $p_addr[1]['BUS_DROPOFF']='';
                   $p_addr[1]['BUS_NO']='';
               }
               $p_addr[1]['RELATIONSHIP']=$pri_par_id[1]['RELATIONSHIP'];
               $p_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$pri_par_id[1]['PERSON_ID'].'\' AND PROFILE_ID='.$profile_id));
               $p_addr[1]['USER_NAME']=$p_log_addr[1]['USER_NAME'];
               $p_addr[1]['PASSWORD']=$p_log_addr[1]['PASSWORD'];
            }
            else 
            {
                $p_addr[1]['CONTACT_ID']='';
                $p_addr[1]['FIRST_NAME']='';
                $p_addr[1]['MIDDLE_NAME']='';
                $p_addr[1]['LAST_NAME']='';
                $p_addr[1]['HOME_PHONE']='';
                $p_addr[1]['WORK_PHONE']='';
                $p_addr[1]['CELL_PHONE']='';
                $p_addr[1]['EMAIL']='';
                $p_addr[1]['CUSTODY']='';
                $p_addr[1]['ADDRESS_ID']='';
                $p_addr[1]['ADDRESS']='';
                $p_addr[1]['STREET']='';
                $p_addr[1]['CITY']='';
                $p_addr[1]['STATE']='';
                $p_addr[1]['ZIPCODE']='';
                $p_addr[1]['BUS_PICKUP']='';
                $p_addr[1]['BUS_DROPOFF']='';
                $p_addr[1]['BUS_NO']='';
                $p_addr[1]['RELATIONSHIP']='';
                $p_addr[1]['USER_NAME']='';
                $p_addr[1]['PASSWORD']='';
            }
            $mail_addr = array();
            $m_addr=DBGet(DBQuery(' SELECT sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from student_address sa WHERE 
                                   sa.TYPE=\'Mail\' AND sa.STUDENT_ID=\''.$student_id.'\'  AND sa.SYEAR=\''.UserSyear().'\' AND sa.SCHOOL_ID=\''.  UserSchool().'\' '));
            
            if(count($m_addr)<1)
            {
                $m_addr[1]['ADDRESS_ID']='';
                $m_addr[1]['ADDRESS']='';
                $m_addr[1]['STREET']='';
                $m_addr[1]['CITY']='';
                $m_addr[1]['STATE']='';
                $m_addr[1]['ZIPCODE']='';
                $m_addr[1]['BUS_PICKUP']='';
                $m_addr[1]['BUS_DROPOFF']='';
                $m_addr[1]['BUS_NO']=''; 
            }
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
                                  sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$sec_par_id[1]['PERSON_ID'].'\' AND sa.STUDENT_ID =\''.$student_id.'\' AND sa.PEOPLE_ID IS NOT NULL AND sa.type=\'Secondary\''));                 

                if(count($s_addr)<1)
               {
                   $s_addr[1]['CONTACT_ID']='';
                   $s_addr[1]['FIRST_NAME']='';
                   $s_addr[1]['MIDDLE_NAME']='';
                   $s_addr[1]['LAST_NAME']='';
                   $s_addr[1]['HOME_PHONE']='';
                   $s_addr[1]['WORK_PHONE']='';
                   $s_addr[1]['CELL_PHONE']='';
                   $s_addr[1]['EMAIL']='';
                   $s_addr[1]['CUSTODY']='';
                    $s_addr[1]['ADDRESS_ID']='';
                   $s_addr[1]['ADDRESS']='';
                   $s_addr[1]['STREET']='';
                   $s_addr[1]['CITY']='';
                   $s_addr[1]['STATE']='';
                   $s_addr[1]['ZIPCODE']='';
                   $s_addr[1]['BUS_PICKUP']='';
                   $s_addr[1]['BUS_DROPOFF']='';
                   $s_addr[1]['BUS_NO']='';
                   
               }
                $s_addr[1]['RELATIONSHIP']=$sec_par_id[1]['RELATIONSHIP'];
                $p_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$sec_par_id[1]['PERSON_ID'].'\' AND PROFILE_ID='.$profile_id));
               $s_addr[1]['USER_NAME']=$p_log_addr[1]['USER_NAME'];
               $s_addr[1]['PASSWORD']=$p_log_addr[1]['PASSWORD'];

            }
            else
            {
                $s_addr=DBGet(DBQuery('SELECT ID AS ADDRESS_ID from student_address WHERE STUDENT_ID='.$student_id.' AND TYPE=\'Secondary\' '));                 
                if(count($s_addr)<1)
                {
                   $s_addr[1]['CONTACT_ID']='';
                   $s_addr[1]['FIRST_NAME']='';
                   $s_addr[1]['MIDDLE_NAME']='';
                   $s_addr[1]['LAST_NAME']='';
                   $s_addr[1]['HOME_PHONE']='';
                   $s_addr[1]['WORK_PHONE']='';
                   $s_addr[1]['CELL_PHONE']='';
                   $s_addr[1]['EMAIL']='';
                   $s_addr[1]['CUSTODY']='';
                    $s_addr[1]['ADDRESS_ID']='';
                   $s_addr[1]['ADDRESS']='';
                   $s_addr[1]['STREET']='';
                   $s_addr[1]['CITY']='';
                   $s_addr[1]['STATE']='';
                   $s_addr[1]['ZIPCODE']='';
                   $s_addr[1]['BUS_PICKUP']='';
                   $s_addr[1]['BUS_DROPOFF']='';
                   $s_addr[1]['BUS_NO']='';
                   $s_addr[1]['RELATIONSHIP']='';
                   $s_addr[1]['USER_NAME']='';
                   $s_addr[1]['PASSWORD']='';
                }

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
                                sa.ID AS ADDRESS_ID,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,sa.BUS_PICKUP,sa.BUS_DROPOFF,sa.BUS_NO from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\''.$otr_data['PERSON_ID'].'\'  AND sa.PEOPLE_ID IS NOT NULL AND sa.type=\'Other\''));                 

                    $o_addr[1]['RELATIONSHIP']=$otr_data['RELATIONSHIP'];
                    $o_addr[1]['IS_EMERGENCY']=$otr_data['IS_EMERGENCY'];
                    $o_log_addr=DBGet(DBQuery('SELECT USERNAME AS USER_NAME ,PASSWORD FROM login_authentication WHERE USER_ID=\''.$otr_data['PERSON_ID'].'\' AND PROFILE_ID='.$profile_id));
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
        //    if(UserWs('PROFILE')=='admin' || UserWs('PROFILE')=='teacher'|| UserWs('PROFILE')=='parent' || UserWs('PROFILE')=='student')
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

                $stu_Medical_info=  DBGet(DBQuery('SELECT PHYSICIAN,PHYSICIAN_PHONE,PREFERRED_HOSPITAL FROM medical_info WHERE STUDENT_ID='.$student_id.' AND SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool().''));

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


                $filepath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
                if($file_path!='')
                $filepath=$filepath."/".$file_path;
                $filepath=$filepath."/assets/studentphotos/";
                $dir=$_SERVER['DOCUMENT_ROOT'];
                if($file_path!='')
                $dir=$dir.'/'.$file_path;
                $dir=$dir.'/assets/studentfiles/';
                $dir=dir($dir);
                $dir=$_SERVER['DOCUMENT_ROOT'].'/'.$file_path.'/assets/studentfiles';
                $dir=dir($dir);
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
                                if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                                {
                                    $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                                }
//                                if($fields['SELECT_OPTIONS']!='')
//                                {
                                    $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                                    if($student[1]["CUSTOM_".$fields['ID']]!=null)
                                    {
                                        if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                           $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                                    }
                                }
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
                        if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                        {
                            $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                        }
//                        if($fields['SELECT_OPTIONS']!='')
//                        {
                            $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                            if($student[1]["CUSTOM_".$fields['ID']]!=null)
                            {
                                if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                   $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                            }
                        }
                        $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
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
                        if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                        {
                            $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                        }
//                        if($fields['SELECT_OPTIONS']!='')
//                        {
                            $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                            if($student[1]["CUSTOM_".$fields['ID']]!=null)
                            {
                                if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                   $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                            }
                        }
                        $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
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
                        if($fields['TYPE']=='autos' || $fields['TYPE']=='edits')
                        {
                            $fields['SELECT_OPTIONS'] = 'N/A\r\n'.$fields['SELECT_OPTIONS'].'\r\n---';
//                        }
//                        if($fields['SELECT_OPTIONS']!='')
//                        {
                            $options = explode('\r\n',$fields['SELECT_OPTIONS']);
                            if($student[1]["CUSTOM_".$fields['ID']]!=null)
                            {
                                if(!in_array($student[1]["CUSTOM_".$fields['ID']],$options))
                                   $fields['SELECT_OPTIONS']=$fields['SELECT_OPTIONS'].'\r\n'.$student[1]["CUSTOM_".$fields['ID']];     
                            }
                        }
                        $custom_fields[]=array('title'=>$fields['TITLE'],'field_title'=>'CUSTOM_'.$fields['ID'],'value'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED'],'HIDE'=>$fields['HIDE']);
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
        $prelation_options = _makeAutoSelect('RELATIONSHIP','students_join_people','PRIMARY','',$relation_options_arr);
        $srelation_options = _makeAutoSelect('RELATIONSHIP','students_join_people','SECONDARY','',$relation_options_arr);
        $orelation_options = _makeAutoSelect('RELATIONSHIP','students_join_people','OTHER','',$relation_options_arr);
        $data['primary_relation_options'] = $prelation_options;
        $data['secondary_relation_options'] = $srelation_options;
        $data['other_relation_options'] = $orelation_options;
        $ethnicity_RET = DBGet(DBQuery("SELECT ETHNICITY_ID,ETHNICITY_NAME FROM ethnicity ORDER BY ETHNICITY_ID")); 
        $ethnicity = array();
        $ethnicity[]=array('ETHNICITY_ID' => '','ETHNICITY_NAME' =>'N/A');
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
        /*
        $data['general_info'] = $general_info;
        if(count($general_info)>0)
        {
            $data['general_info_success'] = 1;
        }
        else 
        {
            $data['general_info_success'] = 0;
        }
        $data['enrollment_info'] = $enrollment_info;
        if(Count($enrollment_info)>0)
        {
            $data['enrollment_info_success'] = 1;
        }
        else 
        {
            $data['enrollment_info_success'] = 0;
        }
        $data['address_contact'] = $address;
        if(count($address[0]['HOME_ADDRESS'])>0||count($address[0]['MAIL_ADDRESS'])>0||count($address[0]['PRIMARY_EMERGENCY_CONTACT'])>0||count($address[0]['SECONDARY_EMERGENCY_CONTACT'])>0)
        {
            $data['address_contact_success'] = 1;
        }
        else 
        {
            $data['address_contact_success'] = 0;
        }
        $data['comments'] = $comments;
        if(count($comments)>0)
        {
            $data['comments_success'] = 1;
        }
        else 
        {
            $data['comments_success'] = 0;
        }    
        $data['goals'] = $goals;
        if(count($goals)>0)
        {
            $data['goals_success'] = 1;
        }
        else 
        {
            $data['goals_success'] = 0;
        }
        $data['parent_data'] = $parent_data;
        if(count($parent_data)>0)
        {
            $data['parent_success'] = 1;
        }
        else 
        {
            $data['parent_success'] = 0;
        }
        $data['medical_info'] = $medical_info;
        if(count($medical_info)>0)
        {
            $data['medical_info_success'] = 1;
        }
        else 
        {
            $data['medical_info_success'] = 0;
        }
        $data['files_info'] = $files_info;
        if(count($files_info)>0)
        {
            $data['files_info_success'] = 1;
        }
        else 
        {
            $data['files_info_success'] = 0;
        }
        */
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
function _makeAutoSelect($column,$table,$opt,$values='',$options=array())
{
        if($opt!='')
            $where=' WHERE EMERGENCY_TYPE=\''.$opt.'\' ';
        else
            $where='';
	$options_RET = DBGet(DBQuery('SELECT DISTINCT '.$column.',upper('.$column.') AS `KEY` FROM '.$table.' '.$where.' ORDER BY `KEY`'));

	// add the 'new' option, is also the separator
//	$options['---'] = '---';
	// add values already in table
	if(count($options_RET))
		foreach($options_RET as $option)
			if($option[$column]!='' && !in_array($option[$column],$options))
				$options[] = $option[$column];
	// make sure values are in the list
	/*if(is_array($values))
	{
		foreach($values as $value)
			if($value[$column]!='' && !$options[$value[$column]])
				$options[$value[$column]] = array($value[$column],'<FONT color=blue>'.$value[$column].'</FONT>');
	}
	else
		if($values!='' && !$options[$values])
			$options[$values] = array($values,'<FONT color=blue>'.$values.'</FONT>');*/

	return $options;
}
?>