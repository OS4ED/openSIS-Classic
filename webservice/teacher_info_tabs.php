<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$profile_id=$_REQUEST['profile_id'];
$tab = $_REQUEST['tab'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
if($_REQUEST['action_type']=='update_demographic_info')
{
    $title = $_REQUEST['title'];
    $fname = $_REQUEST['fname'];
    $lname = $_REQUEST['lname'];
    $mname = $_REQUEST['mname'];
    $suffix = $_REQUEST['suffix'];
    $alt_id = $_REQUEST['alt_id'];
    $gender = $_REQUEST['gender'];
    $dob = ($_REQUEST['dob']!='' && $_REQUEST['dob']!='0000-00-00')?$_REQUEST['dob']:NULL;
    $ethnicity = ($_REQUEST['ethnicity']!='')?$_REQUEST['ethnicity']:'NULL';
    $prim_lang = ($_REQUEST['prim_lang']!='')?$_REQUEST['prim_lang']:'NULL';
    $scnd_lang = ($_REQUEST['scnd_lang']!='')?$_REQUEST['scnd_lang']:'NULL';
    $trd_lang = ($_REQUEST['trd_lang']!='')?$_REQUEST['trd_lang']:'NULL';
    $phy_dis = $_REQUEST['phy_disabl'];
    $dis_desc = $_REQUEST['dis_desc'];
            $email = $_REQUEST['email'];
    $up_data = json_decode($_REQUEST['details']);

                if($title != '' || $fname != '' || $lname != '' || $mname != '' || $suffix != '' || $alt_id != '' || $gender != '' || $dob != '' || $ethnicity != '' || $prim_lang != '' || $scnd_lang != '' || $trd_lang != '' || $phy_dis != '' || $dis_desc != '' || $email!='' || count($up_data)>0)
    {
        $sql = 'UPDATE staff SET TITLE = \''.$title.'\', FIRST_NAME = \''.$fname.'\', last_name = \''.$lname.'\',MIDDLE_NAME = \''.$mname.'\',NAME_SUFFIX = \''.$suffix.'\',ALTERNATE_ID = \''.$alt_id.'\',GENDER = \''.$gender.'\'';
        if($_REQUEST['dob']!='' && $_REQUEST['dob']!='0000-00-00')        
        $sql .=  ',BIRTHDATE = \''.$dob.'\'';
        else 
            $sql .=  ',BIRTHDATE = NULL';
                    $sql .=  ',ETHNICITY_ID = '.$ethnicity.',PRIMARY_LANGUAGE_ID = '.$prim_lang.',second_language_id = '.$scnd_lang.',THIRD_LANGUAGE_ID = '.$trd_lang.',PHYSICAL_DISABILITY = \''.$phy_dis.'\',DISABILITY_DESC = \''.$dis_desc.'\',EMAIL = \''.$email.'\' WHERE STAFF_ID='.$teacher_id;
        $excute_qry = DBQuery($sql);
        if(count($up_data)>0)
        {
            $cond = array();
            foreach($up_data as $cus)
            {
                $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
            }
            if(count($cond)>0)
            {
                $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
            }
        }
        $success = 1;
                    $data_msg = 'Data updated successfully';
    }
    else 
    {
        $success = 0;
        $data_msg = 'Data updated failed';
    }
    $data['update_success'] = $success;
    $data['update_msg'] = $data_msg;
}
elseif($_REQUEST['action_type']=='update_school_info')
{
    
}
elseif($_REQUEST['action_type']=='update_addr')
{
    $addr_type = $_REQUEST['addr_type'];
    
    if($addr_type=='home')
    {
        $addr_id = $_REQUEST['addr_id'];
        $addr1 = urldecode($_REQUEST['addr1']);
        $addr2 = urldecode($_REQUEST['addr2']);
        $city = urldecode($_REQUEST['city']);
        $state = urldecode($_REQUEST['state']);
        $zip = urldecode($_REQUEST['zip']);
        $up_data = (isset($_REQUEST['details']) && $_REQUEST['details']!='')?json_decode($_REQUEST['details']):'';
        if($addr1 != '' || $addr2 != '' || $city != '' || $state != '' || $zip != '' || count($up_data)>0)
        {
            if($addr_id!='')
            {
                $sql = 'UPDATE staff_address SET STAFF_ADDRESS1_PRIMARY = \''.$addr1.'\',STAFF_ADDRESS2_PRIMARY = \''.$addr2.'\',STAFF_CITY_PRIMARY = \''.$city.'\',STAFF_STATE_PRIMARY = \''.$state.'\',STAFF_ZIP_PRIMARY = \''.$zip.'\' WHERE STAFF_ADDRESS_ID = '.$addr_id.' AND STAFF_ID='.$teacher_id;
                $excute_qry = DBQuery($sql);
            }
            else 
            {
                $sql = 'INSERT INTO staff_address (STAFF_ADDRESS1_PRIMARY,STAFF_ADDRESS2_PRIMARY,STAFF_CITY_PRIMARY,STAFF_STATE_PRIMARY,STAFF_ZIP_PRIMARY,STAFF_ID) VALUES(\''.$addr1.'\',\''.$addr2.'\', \''.$city.'\', \''.$state.'\', \''.$zip.'\','.$teacher_id.')';
                $excute_qry = DBQuery($sql);
            }
            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
                }
            }
            $success = 1;
                    $data_msg = 'Data updated successfully';
}
        else 
        {
            $success = 0;
            $data_msg = 'Data updated failed';
        }
        $data['update_success'] = $success;
        $data['update_msg'] = $data_msg;
    }
    elseif($addr_type=='mail')
    {
        $addr_id = $_REQUEST['addr_id'];
        $addr1 = urldecode($_REQUEST['addr1']);
        $addr2 = urldecode($_REQUEST['addr2']);
        $city = urldecode($_REQUEST['city']);
        $state = urldecode($_REQUEST['state']);
        $zip = urldecode($_REQUEST['zip']);
        if($addr1 != '' || $addr2 != '' || $city != '' || $state != '' || $zip != '')
        {
            if($addr_id!='')
            {
                $sql = 'UPDATE staff_address SET STAFF_ADDRESS1_MAIL = \''.$addr1.'\',STAFF_ADDRESS2_MAIL = \''.$addr2.'\',STAFF_CITY_MAIL = \''.$city.'\',STAFF_STATE_MAIL = \''.$state.'\',STAFF_ZIP_MAIL = \''.$zip.'\' WHERE STAFF_ADDRESS_ID = '.$addr_id.' AND STAFF_ID='.$teacher_id;
                $excute_qry = DBQuery($sql);
            }
            else 
            {
                $sql = 'INSERT INTO staff_address (STAFF_ADDRESS1_MAIL,STAFF_ADDRESS2_MAIL,STAFF_CITY_MAIL,STAFF_STATE_MAIL,STAFF_ZIP_MAIL,STAFF_ID) VALUES(\''.$addr1.'\',\''.$addr2.'\', \''.$city.'\', \''.$state.'\', \''.$zip.'\','.$teacher_id.')';
                $excute_qry = DBQuery($sql);
            }
            $success = 1;
                    $data_msg = 'Data updated successfully';
        }
        else 
        {
            $success = 0;
            $data_msg = 'Data updated failed';
        }
        $data['update_success'] = $success;
        $data['update_msg'] = $data_msg;
    }
    elseif($addr_type=='contact')
    {
        $phn_id = $_REQUEST['phn_id'];
        $hphn = urldecode($_REQUEST['hphn']);
        $mphn = urldecode($_REQUEST['mphn']);
        $wphn = urldecode($_REQUEST['wphn']);
        $wemail = urldecode($_REQUEST['wemail']);
        $pemail = urldecode($_REQUEST['pemail']);
        if($hphn!='' || $mphn !='' || $wphn !='' || $wemail !='' || $pemail !='')
        {
            if($phn_id!='')
            {
                $sql = 'UPDATE  staff_contact SET STAFF_HOME_PHONE = \''.$hphn.'\',STAFF_MOBILE_PHONE = \''.$mphn.'\',STAFF_WORK_PHONE = \''.$wphn.'\',STAFF_WORK_EMAIL = \''.$wemail.'\',STAFF_PERSONAL_EMAIL = \''.$pemail.'\' WHERE STAFF_PHONE_ID = '.$phn_id.' AND STAFF_ID='.$teacher_id;
                $excute_qry = DBQuery($sql);
            }
            else 
            {
                $sql = 'INSERT INTO staff_contact (STAFF_HOME_PHONE,STAFF_MOBILE_PHONE,STAFF_WORK_PHONE,STAFF_WORK_EMAIL,STAFF_PERSONAL_EMAIL,STAFF_ID) VALUES(\''.$hphn.'\',\''.$mphn.'\', \''.$wphn.'\', \''.$wemail.'\', \''.$pemail.'\','.$teacher_id.')';
                $excute_qry = DBQuery($sql);
            }
            $success = 1;
                    $data_msg = 'Data updated successfully';
        }
        else 
        {
            $success = 0;
            $data_msg = 'Data updated failed';
        }
        $data['update_success'] = $success;
        $data['update_msg'] = $data_msg;
    }
    elseif($addr_type=='emergency_contact')
    {
        $em_cont_id = $_REQUEST['em_cont_id'];
        $fname = urldecode($_REQUEST['fname']);
        $lname = urldecode($_REQUEST['lname']);
        $rel = urldecode($_REQUEST['relation']);
        $hphn = urldecode($_REQUEST['hphn']);
        $mphn = urldecode($_REQUEST['mphn']);
        $wphn = urldecode($_REQUEST['wphn']);
        $email = urldecode($_REQUEST['email']);
        $up_data = json_decode($_REQUEST['details']);
        if($fname!='' || $lname !='' || $rel !='' || $hphn !='' || $mphn !='' || $wphn !='' || $email !='' || count($up_data)>0)
        {
            if($em_cont_id!='')
            {
                    $sql = 'UPDATE staff_emergency_contact SET STAFF_EMERGENCY_FIRST_NAME = \''.$fname.'\',STAFF_EMERGENCY_LAST_NAME = \''.$lname.'\',STAFF_EMERGENCY_RELATIONSHIP = \''.$rel.'\', STAFF_EMERGENCY_HOME_PHONE = \''.$hphn.'\',STAFF_EMERGENCY_MOBILE_PHONE = \''.$mphn.'\',STAFF_EMERGENCY_WORK_PHONE = \''.$wphn.'\',STAFF_EMERGENCY_EMAIL = \''.$email.'\' WHERE STAFF_EMERGENCY_CONTACT_ID = '.$em_cont_id.' AND STAFF_ID='.$teacher_id;
                $excute_qry = DBQuery($sql);
            }
            else 
            {
                        $sql = 'INSERT INTO staff_emergency_contact (STAFF_EMERGENCY_FIRST_NAME,STAFF_EMERGENCY_LAST_NAME,STAFF_EMERGENCY_RELATIONSHIP,STAFF_EMERGENCY_HOME_PHONE,STAFF_EMERGENCY_MOBILE_PHONE,STAFF_EMERGENCY_WORK_PHONE,STAFF_EMERGENCY_EMAIL,STAFF_ID) VALUES(\''.$fname.'\',\''.$lname.'\', \''.$rel.'\', \''.$hphn.'\', \''.$mphn.'\',\''.$wphn.'\',\''.$email.'\','.$teacher_id.')';
                $excute_qry = DBQuery($sql);
            }
            if(count($up_data)>0)
            {
                $cond = array();
                foreach($up_data as $cus)
                {
                    $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
                }
                if(count($cond)>0)
                {
                    $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
                }
            }
            $success = 1;
                    $data_msg = 'Data updated successfully';
        }
        else 
        {
            $success = 0;
            $data_msg = 'Data updated failed';
        }
        $data['update_success'] = $success;
        $data['update_msg'] = $data_msg;
    }
}
elseif($_REQUEST['action_type']=='add_certification')
{
    $name = urldecode($_REQUEST['name']);
    $srt_nm =  urldecode($_REQUEST['srt_nm']);
    $code =  urldecode($_REQUEST['code']);
    $prim_ind =  urldecode($_REQUEST['prim_cert_indicator']);
    $date =  urldecode($_REQUEST['date']);
    $exp_date =  urldecode($_REQUEST['exp_date']);
    $desc =  urldecode($_REQUEST['desc']);
    $up_data = json_decode($_REQUEST['details']);
    if($name!='' || $srt_nm !='' || $code !='' || $prim_ind !='' || $date !='' || $exp_date !='' || $desc !='' || count($up_data)>0)
    {
    $sql = 'INSERT INTO  staff_certification (STAFF_ID,STAFF_CERTIFICATION_DATE,STAFF_CERTIFICATION_EXPIRY_DATE,STAFF_CERTIFICATION_CODE,STAFF_CERTIFICATION_SHORT_NAME,STAFF_CERTIFICATION_NAME,STAFF_PRIMARY_CERTIFICATION_INDICATOR,STAFF_CERTIFICATION_DESCRIPTION) VALUES ('.$teacher_id.',\''.$date.'\',\''.$exp_date.'\',\''.$code.'\',\''.$srt_nm.'\',\''.$name.'\',\''.$prim_ind.'\',\''.$desc.'\')';
    $excute_qry = DBQuery($sql);
        if(count($up_data)>0)
        {
            $cond = array();
            foreach($up_data as $cus)
            {
                $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
            }
            if(count($cond)>0)
            {
                $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
            }
        }
    $success = 1;
                $data_msg = 'Data updated successfully';
    }
    else 
    {
        $success = 0;
        $data_msg = 'Data updated failed';
    }
    $data['update_success'] = $success;
    $data['update_msg'] = $data_msg;
}
elseif($_REQUEST['action_type']=='update_certification')
{
    $cert_id = $_REQUEST['cert_id'];
    $name =  urldecode($_REQUEST['name']);
    $srt_nm =  urldecode($_REQUEST['srt_nm']);
    $code =  urldecode($_REQUEST['code']);
    $prim_ind =  urldecode($_REQUEST['prim_cert_indicator']);
    $date =  urldecode($_REQUEST['date']);
    $exp_date =  urldecode($_REQUEST['exp_date']);
    $desc =  urldecode($_REQUEST['desc']);
    $up_data = json_decode($_REQUEST['details']);
    if($name!='' || $srt_nm !='' || $code !='' || $prim_ind !='' || $date !='' || $exp_date !='' || $desc !='' || count($up_data)>0)
    {
    $sql = 'UPDATE staff_certification SET STAFF_CERTIFICATION_DATE = \''.$date.'\',STAFF_CERTIFICATION_EXPIRY_DATE = \''.$exp_date.'\',STAFF_CERTIFICATION_CODE = \''.$code.'\',STAFF_CERTIFICATION_SHORT_NAME = \''.$srt_nm.'\',STAFF_CERTIFICATION_NAME = \''.$name.'\',STAFF_PRIMARY_CERTIFICATION_INDICATOR = \''.$prim_ind.'\',STAFF_CERTIFICATION_DESCRIPTION = \''.$desc.'\' WHERE STAFF_CERTIFICATION_ID = '.$cert_id.' AND STAFF_ID = '.$teacher_id;
    $excute_qry = DBQuery($sql);
        if(count($up_data)>0)
        {
            $cond = array();
            foreach($up_data as $cus)
            {
                $cond[] = $cus->column.' = \''.urldecode($cus->value).'\'';
            }
            if(count($cond)>0)
            {
                $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
            }
        }
    $success = 1;
                $data_msg = 'Data updated successfully';
    }
    else 
    {
        $success = 0;
        $data_msg = 'Data updated failed';
    }
    $data['update_success'] = $success;
    $data['update_msg'] = $data_msg;
}
elseif($_REQUEST['action_type']=='delete_certification')
{
    $cert_id = $_REQUEST['cert_id'];
    $sql = 'DELETE FROM staff_certification WHERE STAFF_CERTIFICATION_ID = '.$cert_id.' AND STAFF_ID = '.$teacher_id;
    $excute_qry = DBQuery($sql);
    $success = 1;
            $data_msg = 'Data updated successfully';
    $data['update_success'] = $success;
    $data['update_msg'] = $data_msg;
}
//elseif($_REQUEST['action_type']=='update_schedule')
//{
//    
//}
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
            $sql = DBQuery('UPDATE staff SET '.implode(' , ',$cond).' WHERE staff_id = '.$teacher_id);
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
elseif($_REQUEST['action_type']=='delete_file')
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
else 
{
$tab_data = array();
$demographic_info = array();
$official_info = array();
$sch_info = array();
$addr_data = array();

//            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//            $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
//            $file_path = $scr_path[1];
//
//            $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/userphotos/";
//            $path ='../assets/userphotos/';
$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
$scr_path = explode('/webservice/',$_SERVER['SCRIPT_NAME']);
$file_path = $scr_path[0];

$htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
if($file_path!='')
$htpath=$htpath."/".$file_path;
$htpath=$htpath."/assets/userphotos/";
            
$path ='../assets/userphotos/';
    
$categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM staff_field_categories ORDER BY SORT_ORDER,TITLE")); //WHERE TEACHER = 'Y' 
$tab_sql = 'SELECT MODNAME,CAN_USE,CAN_EDIT FROM  profile_exceptions WHERE MODNAME LIKE \'users\/%category_id%\' AND CAN_USE=\'Y\' AND PROFILE_ID='.$profile_id;
$tab_data = DBGet(DBQuery($tab_sql));
$tab_ids = $can_use = $can_edit = array();
foreach($tab_data as $tab)
{
    $tab_no = explode('=',$tab['MODNAME']);
    $tab_ids[]=$tab_no[1];
    $can_use[$tab_no[1]]=$tab['CAN_USE'];
    $can_edit[$tab_no[1]]=$tab['CAN_EDIT'];
    if(!in_array($tab_no[1],array(1,2,3,4,5)))
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

$sql="SELECT STAFF_ID,TITLE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,PHONE,EMAIL,PROFILE,PROFILE_ID,GENDER,ETHNICITY_ID,BIRTHDATE,ALTERNATE_ID,NAME_SUFFIX,PRIMARY_LANGUAGE_ID,SECOND_LANGUAGE_ID,THIRD_LANGUAGE_ID,IS_DISABLE,PHYSICAL_DISABILITY,DISABILITY_DESC FROM staff  WHERE STAFF_ID='".$teacher_id."' AND current_school_id='".$_REQUEST['school_id']."'";
    $QI = DBQuery($sql);
    $staff = DBGet($QI);

if(count($staff)>0)
{
    foreach($staff as $field=>$value)
    {
        $stuPicPath=$path.$teacher_id.".JPG";
        if(file_exists($stuPicPath))
            $value['PHOTO']=$htpath.$teacher_id.".JPG";
        else 
            $value['PHOTO']="";
    $demographic_info[]=$value;
    }
    $GeneralInfosuccess = 1;
    $GeneralInfosuccessmsg = 'Nil';
}
else 
{
    $GeneralInfosuccess = 0;
    $GeneralInfosuccessmsg = 'No data found';
}


    $this_school_RET = DBGet(DBQuery("SELECT * FROM staff_school_info WHERE STAFF_ID='".$teacher_id."'"));

$i = 0;
foreach($this_school_RET as $index=>$value)
{
    $official_info[$i]=$value;
    $functions = array('PROFILE'=>'_makeUserProfile','STATUS'=>'_makeStatus');
    $sql='SELECT s.ID,ssr.SCHOOL_ID,s.TITLE,ssr.START_DATE,ssr.END_DATE,st.PROFILE,NULL as STATUS FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id  AND st.staff_id='.UserWs('STAFF_ID').' AND ssr.SYEAR='.UserSyear().' GROUP BY ssr.SCHOOL_ID';
    $school_admin=DBGet(DBQuery($sql),$functions);


    foreach($school_admin as $school_data)
    {
        $sch_info[]=$school_data;
    }
    $official_info[$i]['school_Details'] = $sch_info;
    if(count($sch_info)>0)
        $official_info[$i]['SchoolDetailssuccess'] = 1;
    else
        $official_info[$i]['SchoolDetailssuccess'] = 0;
    $i++;
}
    	

		$sql = "SELECT STAFF_CERTIFICATION_ID AS ID,STAFF_ID,STAFF_CERTIFICATION_DATE,STAFF_CERTIFICATION_EXPIRY_DATE,
            STAFF_CERTIFICATION_CODE,STAFF_CERTIFICATION_SHORT_NAME,STAFF_CERTIFICATION_NAME,
            STAFF_PRIMARY_CERTIFICATION_INDICATOR,STAFF_CERTIFICATION_DESCRIPTION FROM staff_certification
            WHERE  STAFF_ID=".$teacher_id;

            $QI = DBQuery($sql);


    $cert_RET = DBGet($QI);
    $counter_for_date=0;
$certification = array();
    foreach($cert_RET as $cert_data)
    {
    $certification[]=$cert_data;
    }

    $this_address_RET = DBGet(DBQuery("SELECT STAFF_ADDRESS_ID,STAFF_ADDRESS1_PRIMARY,STAFF_ADDRESS2_PRIMARY,STAFF_CITY_PRIMARY,STAFF_STATE_PRIMARY,STAFF_ZIP_PRIMARY FROM staff_address WHERE STAFF_ID=".$teacher_id));
$ad_data = array();
foreach($this_address_RET as $addr_data)
{
    $ad_data[]=$addr_data;
}
    $mail_address_RET = DBGet(DBQuery("SELECT STAFF_ADDRESS_ID,STAFF_ADDRESS1_MAIL,STAFF_ADDRESS2_MAIL,STAFF_CITY_MAIL,STAFF_STATE_MAIL,STAFF_ZIP_MAIL FROM staff_address WHERE STAFF_ID=".$teacher_id));
$mail_data = array();
foreach($mail_address_RET as $ml_data)
{
    $mail_data[]=$ml_data;
}
$this_contact_RET = DBGet(DBQuery("SELECT * FROM staff_contact WHERE STAFF_ID=".$teacher_id));
$c_data = array();
foreach($this_contact_RET as $cont_data)
{
    $c_data[]=$cont_data;
}
$this_emer_contact_RET = DBGet(DBQuery("SELECT * FROM staff_emergency_contact WHERE STAFF_ID=".$teacher_id));
$ec_data = array();
foreach($this_emer_contact_RET as $em_cont_data)
{
    $ec_data[]=$em_cont_data;
}
$addr_data = array_merge($ad_data,$c_data,$ec_data);
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
        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM staff_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=\''.$cat_id.'\' ORDER BY SORT_ORDER,TITLE'));
        if(Count($fields_RET)>0)
        {
            foreach($fields_RET as $fields)
            {
                $sql = "SELECT CUSTOM_".$fields['ID']." FROM staff WHERE staff_id='".$teacher_id."'";
                $QI = DBQuery($sql);
                $student = DBGet($QI);
                $custom[$title][]=array('tab_name'=>$title,'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
            }
        }
            $title='';
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
        if(count($demographic_info)>0)
        {
            $data['tab_data'][$i]['tab_success'] = 1;
        }
        else 
        {
            $data['tab_data'][$i]['tab_success'] = 0;
        }
        $data['tab_data'][$i]['tab_content']=$demographic_info;
        $custom_fields = array();
        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM staff_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=\'1\' ORDER BY SORT_ORDER,TITLE'));
        if($fields_RET!='' && Count($fields_RET)>0)
        {
            foreach($fields_RET as $fields)
            {
                $sql = "SELECT CUSTOM_".$fields['ID']." FROM staff WHERE STAFF_ID='".$teacher_id."'";
                $QI = DBQuery($sql);
                $student = DBGet($QI);
                $custom_fields[]=array('tab_name'=>$cat['TITLE'],'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
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
        if(count($this_address_RET)>0 || count($mail_address_RET)>0 || count($this_contact_RET) > 0 || count($this_emer_contact_RET) > 0)
{
            $data['tab_data'][$i]['tab_success'] = 1;
}
else 
{
            $data['tab_data'][$i]['tab_success'] = 0;
}
        $data['tab_data'][$i]['tab_content'][0]['HOME_ADDR']=$ad_data;
        $data['tab_data'][$i]['tab_content'][0]['MAIL_ADDR']=$mail_data;
        $data['tab_data'][$i]['tab_content'][0]['CONTACT_ADDR']=$c_data;
        $data['tab_data'][$i]['tab_content'][0]['EMERGENCY_CONTACT_ADDR']=$ec_data;
        $custom_fields = array();
        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM staff_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=\'2\' ORDER BY SORT_ORDER,TITLE'));
        if(Count($fields_RET)>0)
        {
            foreach($fields_RET as $fields)
            {
                $sql = "SELECT CUSTOM_".$fields['ID']." FROM staff WHERE STAFF_ID='".$teacher_id."'";
                $QI = DBQuery($sql);
                $student = DBGet($QI);
                $custom_fields[]=array('tab_name'=>$cat['TITLE'],'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
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
        if(count($official_info)>0)
        {
            $data['tab_data'][$i]['tab_success'] = 1;
        }
        else 
        {
            $data['tab_data'][$i]['tab_success'] = 0;
        }
        $data['tab_data'][$i]['tab_content']=$official_info;
        $custom_fields = array();
        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM staff_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=\'3\' ORDER BY SORT_ORDER,TITLE'));
        if(Count($fields_RET)>0)
        {
            foreach($fields_RET as $fields)
            {
                $sql = "SELECT CUSTOM_".$fields['ID']." FROM staff WHERE STAFF_ID='".$teacher_id."'";
                $QI = DBQuery($sql);
                $student = DBGet($QI);
                $custom_fields[]=array('tab_name'=>$cat['TITLE'],'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
            }
        }
        $data['tab_data'][$i]['custom_fields_content'] = $custom_fields;
        $i++;
    }
    elseif($cat['ID']==4)
    {
        $data['tab_data'][$i]['tab_name']=$cat['TITLE'];
        $data['tab_data'][$i]['tab_show']=(in_array($cat['ID'],$tab_ids))?'1':'0';
        $data['tab_data'][$i]['can_use']=($can_use[$cat['ID']]=='Y')?'Y':'N';
        $data['tab_data'][$i]['can_edit']=($can_edit[$cat['ID']]=='Y')?'Y':'N';
        if(count($certification)>0)
        {
            $data['tab_data'][$i]['tab_success'] = 1;
        }
        else 
        {
            $data['tab_data'][$i]['tab_success'] = 0;
        }
        $data['tab_data'][$i]['tab_content']=$certification;
        $custom_fields = array();
        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,SORT_ORDER FROM staff_fields WHERE (SYSTEM_FIELD=\'N\' OR SYSTEM_FIELD IS NULL) AND CATEGORY_ID=\'4\' ORDER BY SORT_ORDER,TITLE'));
        if(Count($fields_RET)>0)
        {
            foreach($fields_RET as $fields)
            {
                $sql = "SELECT CUSTOM_".$fields['ID']." FROM staff WHERE STAFF_ID='".$teacher_id."'";
                $QI = DBQuery($sql);
                $student = DBGet($QI);
                $custom_fields[]=array('tab_name'=>$cat['TITLE'],'ID'=>$fields['ID'],'COLUMN'=>'CUSTOM_'.$fields['ID'],'TITLE'=>$fields['TITLE'],'VALUE'=>($student[1]["CUSTOM_".$fields['ID']]!=null)?$student[1]["CUSTOM_".$fields['ID']]:'','TYPE'=>$fields['TYPE'],'SELECT_OPTIONS'=>$fields['SELECT_OPTIONS'],'DEFAULT_SELECTION'=>$fields['DEFAULT_SELECTION'],'REQUIRED'=>$fields['REQUIRED']);
            }
        }
        $data['tab_data'][$i]['custom_fields_content'] = $custom_fields;
        $i++;
    }
    else 
    {
        if($cat['ID']!=5)
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
            else 
            {
                $data['custom_tab_data'][$j]['tab_success'] = 0;
                $data['custom_tab_data'][$j]['tab_content']=array();
            }
            $j++;
        }
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

function _makeStatus($value,$column)
{
    global $THIS_RET;

      $dates=DBGet(DBQuery("SELECT ssr.START_DATE,ssr.END_DATE FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='".$THIS_RET['SCHOOL_ID']."' AND ssr.STAFF_ID='".$_SESSION['STAFF_ID']."' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='".$THIS_RET['SCHOOL_ID']."' AND STAFF_ID='".$_SESSION['STAFF_ID']."')"));
      
      if($dates[1]['START_DATE']=='0000-00-00' && ($dates[1]['END_DATE']=='0000-00-00' || $dates[1]['END_DATE'] == ''))
      {
       $sql='SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND ssr.STAFF_ID='.$_SESSION['STAFF_ID'].' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND STAFF_ID='.$_SESSION['STAFF_ID'].')';   
      }
   
      if($dates[1]['START_DATE']=='0000-00-00' && ($dates[1]['END_DATE']!='0000-00-00' || $dates[1]['END_DATE'] != ''))
      {
       $sql='SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND ssr.STAFF_ID='.$_SESSION['STAFF_ID'].' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND STAFF_ID='.$_SESSION['STAFF_ID'].') AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL)';   
      }
      if($dates[1]['START_DATE']!='0000-00-00' && ($dates[1]['END_DATE']=='0000-00-00' || $dates[1]['END_DATE'] == ''))
      {
       $sql='SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND ssr.STAFF_ID='.$_SESSION['STAFF_ID'].' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND STAFF_ID='.$_SESSION['STAFF_ID'].') ';   
      }
      if($dates[1]['START_DATE']!='0000-00-00' && ($dates[1]['END_DATE']!='0000-00-00' || $dates[1]['END_DATE'] != ''))
      {
       $sql='SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND ssr.STAFF_ID='.$_SESSION['STAFF_ID'].' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND STAFF_ID='.$_SESSION['STAFF_ID'].')  AND (ssr.END_DATE>=\''.date('Y-m-d').'\' OR ssr.END_DATE=\'0000-00-00\' OR ssr.END_DATE IS NULL)';   
      }

      $user_exist_school=DBGet(DBQuery($sql));
       if(!empty($user_exist_school))
         $status_value='Active';  
        else
        {
         if($dates[1]['START_DATE']!='0000-00-00' && ($dates[1]['END_DATE']!='0000-00-00' || $dates[1]['END_DATE'] != ''))
         $status_value='Inactive';
         else
         $status_value='';
        }
 
     return $status_value; 
}
function _makeUserProfile($value,$column)
{
   global $THIS_RET;
    $sql='SELECT up.TITLE FROM staff s,staff_school_relationship ssr,user_profiles up  WHERE ssr.STAFF_ID=s.STAFF_ID AND up.ID=s.PROFILE_ID AND ssr.SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND ssr.STAFF_ID='.$_SESSION['STAFF_ID'].' AND ssr.SYEAR=   (SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='.$THIS_RET['SCHOOL_ID'].' AND STAFF_ID='.$_SESSION['STAFF_ID'].')';
    $user_profile=DBGet(DBQuery($sql));
    $profile_value=  $user_profile[1]['TITLE'];  
    return $profile_value; 
}
?>
