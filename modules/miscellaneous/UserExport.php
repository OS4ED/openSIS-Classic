<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
include('../../RedirectModulesInc.php');
$extra['search'] .= '<TR><TD align=center colspan=2><TABLE><TR><TD><DIV id=fields_div></DIV></TD></TR></TABLE></TD></TR>';
$extra['new'] = true;
$_openSIS['CustomFields'] = true;
if($_REQUEST['fields']['TITLE'] || $_REQUEST['fields']['FIRST_NAME'] || $_REQUEST['fields']['LAST_NAME'] || $_REQUEST['fields']['MIDDLE_NAME'] || $_REQUEST['fields']['GENDER'] || $_REQUEST['fields']['BIRTHDATE'] || $_REQUEST['fields']['CELL_PHONE'] || $_REQUEST['fields']['WORK_PHONE'] || $_REQUEST['fields']['HOME_PHONE'] || $_REQUEST['fields']['PRIMARY_LANGUAGE_ID'] || $_REQUEST['fields']['SECOND_LANGUAGE_ID'] || $_REQUEST['fields']['THIRD_LANGUAGE_ID'] || $_REQUEST['fields']['LAST_YEAR_ID'] || $_REQUEST['fields']['PHONE'] || $_REQUEST['fields']['USERNAME'] || $_REQUEST['fields']['IS_DISABLE'] || $_REQUEST['fields']['EMAIL'] || $_REQUEST['fields']['LAST_LOGIN'] || $_REQUEST['fields']['PROFILE'])
{
    
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
    if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
    {
        $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,s.birthdate,s.primary_language_id,s.second_language_id,s.third_language_id,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssr.SCHOOL_ID as SCHOOLS ';
    }
    else {
         $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,s.GENDER,s.birthdate,s.primary_language_id,s.second_language_id,s.third_language_id,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.PHONE,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssr.SCHOOL_ID as SCHOOLS ';
    }
    }
    else
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
        $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.IS_DISABLE,s.HOME_PHONE,s.WORK_PHONE,s.CELL_PHONE,s.PROFILE,s.CURRENT_SCHOOL_ID,ssm.SCHOOL_ID as SCHOOLS ';
        }
        else {
         $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,s.GENDER,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.PHONE,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssm.SCHOOL_ID as SCHOOLS ';
        }
    }
         
         
         $extra['WHERE'] .=' AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=s.PROFILE_ID ';
	
}

if($_REQUEST['fields']['STREET_ADDRESS_1'] || $_REQUEST['fields']['STREET_ADDRESS_2']  || $_REQUEST['fields']['STATE']  || $_REQUEST['fields']['CITY'] || $_REQUEST['fields']['ZIPCODE'])
{
   if($_REQUEST['modname']=='users/UserAdvancedReport.php')
    {
     if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        { 
       $extra['SELECT'] .=' ,sa.STREET_ADDRESS_1,sa.STREET_ADDRESS_2,sa.STATE,sa.CITY,sa.ZIPCODE ';  
     }else{
       $extra['SELECT'] .=' ,sa.STREET_ADDRESS_1,sa.STREET_ADDRESS_2,sa.STATE,sa.CITY,sa.ZIPCODE';  
   }
   }
     $extra['FROM'] .= ' ,student_address sa';
     $extra['WHERE'] .=' AND sa.PEOPLE_ID=s.STAFF_ID';
}
if($_REQUEST['fields']['STAFF_ADDRESS1_PRIMARY'] || $_REQUEST['fields']['STAFF_ADDRESS2_PRIMARY'] || $_REQUEST['fields']['STAFF_CITY_PRIMARY'] || $_REQUEST['fields']['STAFF_STATE_PRIMARY'] || $_REQUEST['fields']['STAFF_ZIP_PRIMARY'] || $_REQUEST['fields']['STAFF_ADDRESS1_MAIL'] || $_REQUEST['fields']['STAFF_ADDRESS2_MAIL'] || $_REQUEST['fields']['STAFF_CITY_MAIL'] || $_REQUEST['fields']['STAFF_STATE_MAIL'] || $_REQUEST['fields']['STAFF_ZIP_MAIL'])
{
    
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
            $extra['SELECT'] .= ',sta.staff_address1_primary,sta.staff_address2_primary,sta.staff_city_primary,sta.staff_state_primary,sta.staff_zip_primary,sta.staff_address1_mail,sta.staff_address2_mail,sta.staff_city_mail,sta.staff_state_mail,sta.staff_zip_mail ';
        }
        else {
             $extra['SELECT'] .= ',sta.staff_address1_primary,sta.staff_address2_primary,sta.staff_city_primary,sta.staff_state_primary,sta.staff_zip_primary,sta.staff_address1_mail,sta.staff_address2_mail,sta.staff_city_mail,sta.staff_state_mail,sta.staff_zip_mail';
        }
        
//        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
//        {
//            $extra['SELECT'] .= ',sta.staff_address2_primary ';
//        }
//        else {
//             $extra['SELECT'] .= ',sta.staff_address2_primary';
//        }
    }
    $extra['FROM'] .= ' LEFT JOIN staff_address sta ON sta.STAFF_ID=s.STAFF_ID';
    // $extra['WHERE'] .=' AND sta.STAFF_ID=s.STAFF_ID ';
}
if($_REQUEST['fields']['STAFF_HOME_PHONE'] || $_REQUEST['fields']['STAFF_WORK_PHONE'] || $_REQUEST['fields']['STAFF_MOBILE_PHONE'] || $_REQUEST['fields']['STAFF_WORK_EMAIL'] || $_REQUEST['fields']['STAFF_ZIP_PRIMARY'] || $_REQUEST['fields']['STAFF_PERSONAL_EMAIL'])
{
    
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
            $extra['SELECT'] .= ',st_cont.staff_home_phone,st_cont.staff_mobile_phone,st_cont.staff_work_phone,st_cont.staff_work_email,st_cont.staff_personal_email ';
        }
        else {
             $extra['SELECT'] .= ',st_cont.staff_home_phone,st_cont.staff_mobile_phone,st_cont.staff_work_phone,st_cont.staff_work_email,st_cont.staff_personal_email';
        }
        
//        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
//        {
//            $extra['SELECT'] .= ',sta.staff_address2_primary ';
//        }
//        else {
//             $extra['SELECT'] .= ',sta.staff_address2_primary';
//        }
    }
    $extra['FROM'] .= ' LEFT JOIN staff_contact st_cont ON st_cont.STAFF_ID=s.STAFF_ID';
    // $extra['WHERE'] .=' AND st_cont.STAFF_ID=s.STAFF_ID ';
}

if($_REQUEST['fields']['STAFF_EMERGENCY_FIRST_NAME'] || $_REQUEST['fields']['STAFF_EMERGENCY_LAST_NAME'] || $_REQUEST['fields']['STAFF_EMERGENCY_RELATIONSHIP'] || $_REQUEST['fields']['STAFF_EMERGENCY_HOME_PHONE'] || $_REQUEST['fields']['STAFF_EMERGENCY_MOBILE_PHONE'] || $_REQUEST['fields']['STAFF_EMERGENCY_WORK_PHONE'] || $_REQUEST['fields']['STAFF_EMERGENCY_EMAIL'])
{
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
            $extra['SELECT'] .= ',ste.staff_emergency_first_name,ste.staff_emergency_last_name,ste.staff_emergency_relationship,ste.staff_emergency_home_phone,ste.staff_emergency_mobile_phone,ste.staff_emergency_work_phone,ste.staff_emergency_email ';
        }
        else
        {
            $extra['SELECT'] .= ',ste.staff_emergency_first_name,ste.staff_emergency_last_name,ste.staff_emergency_relationship,ste.staff_emergency_home_phone,ste.staff_emergency_mobile_phone,ste.staff_emergency_work_phone,ste.staff_emergency_email';
        }
    }

    $extra['FROM'] .= ' LEFT JOIN staff_emergency_contact ste ON ste.STAFF_ID=s.STAFF_ID';
    // $extra['WHERE'] .= ' AND ste.STAFF_ID=s.STAFF_ID';
}

if($_REQUEST['fields']['STAFF_CERTIFICATION_DATE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_EXPIRY_DATE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_CODE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_SHORT_NAME'] || $_REQUEST['fields']['STAFF_CERTIFICATION_NAME'] || $_REQUEST['fields']['STAFF_PRIMARY_CERTIFICATION_INDICATOR'])
{
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
            $extra_cert['SELECT'] .= ',stc.staff_certification_date,stc.staff_certification_expiry_date,stc.staff_certification_name,stc.staff_certification_short_name,stc.staff_primary_certification_indicator,staff_certification_code ';
        }
        else
        {
            $extra_cert['SELECT'] .= ',stc.staff_certification_date,stc.staff_certification_expiry_date,stc.staff_certification_name,stc.staff_certification_short_name,stc.staff_primary_certification_indicator,staff_certification_code';
        }
    }

    $extra_cert['FROM'] .= ' LEFT JOIN staff_certification stc ON stc.STAFF_ID=s.STAFF_ID';
    // $extra_cert['WHERE'] .= ' AND stc.STAFF_ID=s.STAFF_ID';
}

if($_REQUEST['fields']['CATEGORY'] || $_REQUEST['fields']['JOB_TITLE'] || $_REQUEST['fields']['JOINING_DATE'] || $_REQUEST['fields']['END_DATE'])
{
    if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
    {
        $extra['SELECT'] .= ',ssi.category,ssi.job_title,ssi.joining_date,ssi.end_date ';
    }
    else
    {
        $extra['SELECT'] .= ',ssi.category,ssi.job_title,ssi.joining_date,ssi.end_date';  
    }
    $extra['FROM'] .= ' ,staff_school_info ssi';
    $extra['WHERE'] .= ' AND ssi.STAFF_ID=s.STAFF_ID';
}


if(!$extra['functions'])
	$extra['functions'] = array('LAST_LOGIN'=>'ProperDate','SCHOOLS'=>'Tot_School');

if($_REQUEST['search_modfunc']=='list')
{
    
	if(!$fields_list)
	{
                if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php'){
        $fields_list = array('FULL_NAME'=>_lastFirst,
        'FIRST_NAME'=>_first,
        'TITLE'=>_title,
        'LAST_NAME'=>_last,
        'MIDDLE_NAME'=>_middle,
        'GENDER'=>_gender,
        'BIRTHDATE'=>_dateOfBirth,
        'Ethnicity'=>_ethnicity,
        'PRIMARY_LANGUAGE_ID'=>_primaryLanguage,
        'SECOND_LANGUAGE_ID'=>_secondLanguage,
        'THIRD_LANGUAGE_ID'=>_thirdLanguage,
        'STAFF_ADDRESS1_PRIMARY'=>_streetAddress_1,
        'STAFF_ADDRESS2_PRIMARY'=>_streetAddress_2,
        'STAFF_CITY_PRIMARY'=>_city,
        'STAFF_STATE_PRIMARY'=>_state,
        'STAFF_ZIP_PRIMARY'=>_zipPostalCode,
        'STAFF_ADDRESS1_MAIL'=>_mailingStreetAddress_1,
        'STAFF_ADDRESS2_MAIL'=>_mailingStreetAddress_2,
        'STAFF_CITY_MAIL'=>_mailingCity,
        'STAFF_STATE_MAIL'=>_mailingState,
        'STAFF_ZIP_MAIL'=>_mailingZipPostalCode,
        'STAFF_HOME_PHONE'=>_homePhone,
        'STAFF_MOBILE_PHONE'=>_mobilePhone,
        'STAFF_WORK_PHONE'=>_workPhone,
        'STAFF_WORK_EMAIL'=>_workEmail,
        'STAFF_PERSONAL_EMAIL'=>_personalEmail,
        'STAFF_EMERGENCY_FIRST_NAME'=>_emergencyContactFirstName,
        'STAFF_EMERGENCY_LAST_NAME'=>_emergencyContactLastName,
        'STAFF_EMERGENCY_RELATIONSHIP'=>_emergencyContactRelationship,
        'STAFF_EMERGENCY_HOME_PHONE'=>_emergencyContactHomePhone,
        'STAFF_EMERGENCY_MOBILE_PHONE'=>_emergencyContactMobilePhone,
        'STAFF_EMERGENCY_WORK_PHONE'=>_emergencyContactWorkPhone,
        'STAFF_EMERGENCY_EMAIL'=>_emergencyContactEmail,
        'CATEGORY'=>_staffCategory,
        'JOB_TITLE'=>_jobTitle,
        'JOINING_DATE'=>_joiningDate,
        'END_DATE'=>_endDate,
        'STAFF_ID'=>_staffId,
        'ROLLOVER_ID'=>_lastYearId,
        'SCHOOLS'=>_schools,
        'USERNAME'=>_username,
        'IS_DISABLE'=>_disable,
        'EMAIL'=>_emailId,
        'PHONE'=>_phone,
        'LAST_LOGIN'=>_lastLogin,
        'PROFILE'=>_userProfile,
    );
                if($extra['field_names'])
                {
                    $fields_list += $extra['field_names'];
                    
                }
			
                }else{
                    $fields_list = array('FULL_NAME'=>_lastFirst,
                    'FIRST_NAME'=>_first,
                    'TITLE'=>_title,
                    'LAST_NAME'=>_last,
                    'MIDDLE_NAME'=>_middle,
                    'STREET_ADDRESS_1'=>_streetAddress_1,
                    'STREET_ADDRESS_2'=>_streetAddress_2,
                    'CITY'=>_city,
                    'STATE'=>_state,
                    'ZIPCODE'=>_zipPostalCode,
                    'HOME_PHONE'=>_homePhone,
                    'CELL_PHONE'=>_mobilePhone,
                    'WORK_PHONE'=>_workPhone,
                    'SCHOOLS'=>_schools,
                    'USERNAME'=>_username,
                    'IS_DISABLE'=>_disable,
                    'EMAIL'=>_emailId,
                    'LAST_LOGIN'=>_lastLogin,
                    'PROFILE'=>_userProfile,
                );
                if($extra['field_names'])
                {
                    $fields_list += $extra['field_names'];
                    
                }
                }
	}

        
        if(!$fields_list_cert)
        {
                            if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php'){
            $fields_list_cert = array('STAFF_CERTIFICATION_DATE'=>_staffCertificationDate,
            'STAFF_CERTIFICATION_EXPIRY_DATE'=>_staffCertificationExpiryDate,
            'STAFF_CERTIFICATION_CODE'=>_certificationCode,
            'STAFF_CERTIFICATION_SHORT_NAME'=>_certificationShortName,
            'STAFF_CERTIFICATION_NAME'=>_certificationName,
            'STAFF_PRIMARY_CERTIFICATION_INDICATOR'=>_primaryCertificationIndicator,
        );
            if($extra_cert['field_names'])
            {
                $fields_list_cert += $extra_cert['field_names'];
            }
        }
        }
        
        if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
        
	$custom_RET = DBGet(DBQuery("SELECT TITLE,ID,TYPE FROM staff_fields ORDER BY SORT_ORDER"));
        else
            $custom_RET = DBGet(DBQuery("SELECT TITLE,ID,TYPE FROM people_fields ORDER BY SORT_ORDER"));
	foreach($custom_RET as $field)
	{
                
                        if(!$fields_list[$field['TITLE']])
                        {
                                $title=strtolower(trim($field['TITLE']));
                                if(strpos(trim($field['TITLE']),' ')!=0)
                                {
                                     $p1=substr(trim($field['TITLE']),0,strpos(trim($field['TITLE']),' '));
                                     $p2=substr(trim($field['TITLE']),strpos(trim($field['TITLE']),' ')+1);
                                     $title=strtolower($p1.'_'.$p2);
                                }
                                $fields_list[$title] = $field['TITLE'];
                         

                        }
	}


                if($_REQUEST['fields'])
                {
                    foreach($_REQUEST['fields'] as $field => $on)
                    {
//                        print_r($field);
                        if(strtoupper($field)=='PHONE')
                        {
                            $field='HOME_PHONE';
                            $columns[$field]='Phone';
                        }
                        elseif(strtoupper($field)=='SCHOOLS')
                        {
                            $field='SCHOOLS';
                            $columns[$field]='Schools';
                        }
                        else
                        {
                            foreach($fields_list as $in => $val)
                            {
                                if($field == $in)
                                {
                                    $columns[strtoupper($field)] = $fields_list[$field];
                                }
                            }
                            
                            foreach($fields_list_cert as $in_cert => $val_cert)
                            {
                                if($field == $in_cert)
                                {
                                    $columns_cert[strtoupper($field)] = $fields_list_cert[$field];
                                }
                            }
                            
                            
                        }
                            
                        if(!$fields_list[$field])
                        {
                           
                            if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
        
                                 $get_column = DBGet(DBQuery("SELECT ID,TITLE FROM staff_fields  ORDER BY SORT_ORDER"));
                                else
                                    $get_column = DBGet(DBQuery("SELECT TITLE,ID,TYPE FROM people_fields ORDER BY SORT_ORDER"));
                            foreach($get_column as $COLUMN_NAME)
                            {
                                if('CUSTOM_'.$COLUMN_NAME['ID']==$field)
                                    $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                                else if(str_replace (" ", "_",strtoupper($COLUMN_NAME['TITLE']))==strtoupper($field))
                                    $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                            }
                            if(strpos($field,'CUSTOM')===0)
                            {
                                    $custom_id=str_replace("CUSTOM_","",$field);
                                    $custom_RET=DBGet(DBQuery("SELECT TYPE FROM staff_fields WHERE ID=$custom_id"));
                                    if($custom_RET[1]['TYPE']=='date' && !$extra['functions'][$field]){
                                        $extra['functions'][$field] = 'ProperDate';
                                    }
                                    elseif($custom_RET[1]['TYPE']=='codeds' && !$extra['functions'][$field]){
                                        $extra['functions'][$field] = 'DeCodeds';
                                    }
                                    else{
                                        
                                        $extra['SELECT'] .= ',s.'.$field;  
                            }
                        }
                }
                }

               $extra['functions']['BIRTHDATE'] = 'ProperDate';
               $extra['functions']['PRIMARY_LANGUAGE_ID'] ='_makeLanguage';
               $extra['functions']['SECOND_LANGUAGE_ID'] ='_makeLanguage';
               $extra['functions']['THIRD_LANGUAGE_ID'] ='_makeLanguage';
               $extra_cert['functions']['STAFF_CERTIFICATION_DATE'] = 'ProperDate';
                if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
                {
                    $RET = GetStaffList($extra);
                    //$RET_CERT = GetStaffList($extra_cert);
                }
                else
                {
                    $RET = GetUserStaffList($extra);
                    // $RET_CERT = GetUserStaffList($extra_cert);
                }
                     
                    if($extra['array_function'] && function_exists($extra['array_function']))
                    {
                        $extra['array_function']($RET);
                    }
//                    if($extra_cert['array_function'] && function_exists($extra_cert['array_function']))
//                    {
//                        $extra_cert['array_function']($RET_CERT);
//                    }
//                    echo "<pre>";
                    echo "<html><link rel='stylesheet' type='text/css' href='styles/Export.css'><body style=\" font-family:Arial; font-size:12px;\">";
                  ListOutputPrint_Report($RET,$columns,$extra['singular']?$extra['singular']:'User',$extra['plural']?$extra['plural']:'users',array(),$extra['LO_group'],$extra['LO_options']);

                    echo "<table>";
                    echo '<tr style="height: 10px;"><td></td></tr>';
                    echo '</table>';
                    
                  if($_REQUEST['fields']['STAFF_CERTIFICATION_DATE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_EXPIRY_DATE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_CODE'] || $_REQUEST['fields']['STAFF_CERTIFICATION_SHORT_NAME'] || $_REQUEST['fields']['STAFF_CERTIFICATION_NAME'] || $_REQUEST['fields']['STAFF_PRIMARY_CERTIFICATION_INDICATOR'])
                  {
                     if (count($RET) > 0) {
                        foreach ($RET as $one_staff) {
                            $extra_cert['WHERE'] .= ' AND s.STAFF_ID = ' . $one_staff['STAFF_ID'];
                            $RET_CERT = GetUserStaffList($extra_cert);

                            echo "<table>";
                            echo '<tr><td colspan="2"><b>Certificate Information</b></td></tr>';
                            echo '<tr><td colspan="2"></td></tr>';
                            echo '<tr><td>' . _staff . ':</td>';
                            echo '<td>' . $one_staff['FULL_NAME'] . '</td></tr>';
                            echo '<tr><td colspan="2"></td></tr>';
                            echo '</table>';

                            if (count($RET_CERT) == 0) {
                                echo '<div style=text-align:left><table cellpadding="6" width="100%" cellspacing="1" border="1 " style="border-collapse:collapse" align="center"><tr><td style="text-align: center;font-style: italic;accent-color: ;">No certifications were found.</td></tr></table></div>';
                            } else {
                                ListOutputPrint_Report($RET_CERT, $columns_cert, $extra_cert['singular'] ? $extra_cert['singular'] : 'User', $extra_cert['plural'] ? $extra_cert['plural'] : 'users', array(), $extra_cert['LO_group'], $extra_cert['LO_options']);
                            }
                            echo "<table>";
                            echo '<tr style="height: 10px;"><td></td></tr>';
                            echo '</table>';
                        }
                    }
                  }
                  
                    echo "</body></html>";
                }
}
else
{
	if(!$fields_list)
	{
           if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php'){
		if(AllowUse('users/User.php&category_id=1'))
            $fields_list['General'] = array('FULL_NAME'=>_lastFirst,
            'FIRST_NAME'=>_first,
            'TITLE'=>_title,
            'LAST_NAME'=>_last,
            'MIDDLE_NAME'=>_middle,
            'GENDER'=>_gender,
            'BIRTHDATE'=>_dateOfBirth,
            'Ethnicity'=>_ethnicity,
            'PRIMARY_LANGUAGE_ID'=>_primaryLanguage,
            'SECOND_LANGUAGE_ID'=>_secondLanguage,
            'THIRD_LANGUAGE_ID'=>_thirdLanguage,
            'STAFF_ADDRESS1_PRIMARY'=>_streetAddress_1,
            'STAFF_ADDRESS2_PRIMARY'=>_streetAddress_2,
            'STAFF_CITY_PRIMARY'=>_city,
            'STAFF_STATE_PRIMARY'=>_state,
            'STAFF_ZIP_PRIMARY'=>_zipPostalCode,
            'STAFF_ADDRESS1_MAIL'=>_mailingStreetAddress_1,
            'STAFF_ADDRESS2_MAIL'=>_mailingStreetAddress_2,
            'STAFF_CITY_MAIL'=>_mailingCity,
            'STAFF_STATE_MAIL'=>_mailingState,
            'STAFF_ZIP_MAIL'=>_mailingZipPostalCode,
            'STAFF_HOME_PHONE'=>_homePhone,
            'STAFF_MOBILE_PHONE'=>_mobilePhone,
            'STAFF_WORK_PHONE'=>_workPhone,
            'STAFF_WORK_EMAIL'=>_workEmail,
            'STAFF_PERSONAL_EMAIL'=>_personalEmail,
            'STAFF_EMERGENCY_FIRST_NAME'=>_emergencyContactFirstName,
            'STAFF_EMERGENCY_LAST_NAME'=>_emergencyContactLastName,
            'STAFF_EMERGENCY_RELATIONSHIP'=>_emergencyContactRelationship,
            'STAFF_EMERGENCY_HOME_PHONE'=>_emergencyContactHomePhone,
            'STAFF_EMERGENCY_MOBILE_PHONE'=>_emergencyContactMobilePhone,
            'STAFF_EMERGENCY_WORK_PHONE'=>_emergencyContactWorkPhone,
            'STAFF_EMERGENCY_EMAIL'=>_emergencyContactEmail,
            'CATEGORY'=>_staffCategory,
            'JOB_TITLE'=>_jobTitle,
            'JOINING_DATE'=>_joiningDate,
            'END_DATE'=>_endDate,
            'STAFF_ID'=>_staffId,
            'ROLLOVER_ID'=>_lastYearId,
            'SCHOOLS'=>_schools,
            'USERNAME'=>_username,
            'IS_DISABLE'=>_disable,
            'EMAIL'=>_emailId,
            'PHONE'=>_phone,
            'LAST_LOGIN'=>_lastLogin,
            'PROFILE'=>_userProfile,
        );
//                        $fields_list['Certificate'] = array('STAFF_CERTIFICATION_DATE'=>'Staff Certification Date','STAFF_CERTIFICATION_EXPIRY_DATE'=>'Staff Certification Expiry Date');
		if($extra['field_names'])
			$fields_list['General'] += $extra['field_names'];
//                        $field_list_cert['Certificate'] +=$extra_cert['field_names'];
            }else{
                    $fields_list['General'] = array('FULL_NAME'=>_lastFirst,
                    'FIRST_NAME'=>_first,
                    'TITLE'=>_title,
                    'LAST_NAME'=>_last,
                    'MIDDLE_NAME'=>_middle,
                    'STREET_ADDRESS_1'=>_streetAddress_1,
                    'STREET_ADDRESS_2'=>_streetAddress_2,
                    'CITY'=>_city,
                    'STATE'=>_state,
                    'ZIPCODE'=>_zipPostalCode,
                    'HOME_PHONE'=>_homePhone,
                    'CELL_PHONE'=>_mobilePhone,
                    'WORK_PHONE'=>_workPhone,
                    'SCHOOLS'=>_schools,
                    'USERNAME'=>_username,
                    'IS_DISABLE'=>_disable,
                    'EMAIL'=>_emailId,
                    'LAST_LOGIN'=>_lastLogin,
                    'PROFILE'=>_userProfile,
                );
//                        $fields_list['Certificate'] = array('STAFF_CERTIFICATION_DATE'=>'Staff Certification Date','STAFF_CERTIFICATION_EXPIRY_DATE'=>'Staff Certification Expiry Date');
		if($extra['field_names'])
			$fields_list['General'] += $extra['field_names'];
//                        $field_list_cert['Certificate'] +=$extra_cert['field_names'];
            }

            }
        
        if(!$fields_list_cert)
        {
           if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php'){
            if(AllowUse('users/User.php&category_id=1'))
            {
                $fields_list['Certificate'] = array('STAFF_CERTIFICATION_DATE'=>_certificationDate,
                'STAFF_CERTIFICATION_EXPIRY_DATE'=>_certificationExpiryDate,
                'STAFF_CERTIFICATION_CODE'=>_certificationCode,
                'STAFF_CERTIFICATION_NAME'=>_certificationName,
                'STAFF_CERTIFICATION_SHORT_NAME'=>_certificationShortName,
                'STAFF_PRIMARY_CERTIFICATION_INDICATOR'=>_primaryCertificationIndicator,
            );
            }
                
            if($extra_cert['field_names'])
            {
                $fields_list['Certificate'] += $extra_cert['field_names'];
            }
           }
        }
/*******************************************************************************/
        if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
        {
            $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM staff_field_categories ORDER BY SORT_ORDER'));
            $custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM staff_fields ORDER BY SORT_ORDER'),array(),array('CATEGORY_ID'));
        }
        else
        {
            $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM people_field_categories ORDER BY SORT_ORDER'));
            $custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM people_fields ORDER BY SORT_ORDER'),array(),array('CATEGORY_ID'));
        }
        foreach($categories_RET as $category)
	{
//		if(AllowUse('users/User.php&category_id='.$category['ID']))
//		{
			foreach($custom_RET[$category['ID']] as $field)
			{
				 $title=strtolower(trim($field['TITLE']));
			 	if(strpos(trim($field['TITLE']),' ')!=0)
				{
				 $p1=substr(trim($field['TITLE']),0,strpos(trim($field['TITLE']),' '));
				 $p2=substr(trim($field['TITLE']),strpos(trim($field['TITLE']),' ')+1);
				 $title=strtolower($p1.'_'.$p2);
				}
				$fields_list[$category['TITLE']]['CUSTOM_'.$field['ID']] = $field['TITLE'];
			}	
		//}
	}

	
	echo '<div class="row">';
        echo '<div class="col-sm-6 col-md-9">';
	//DrawHeader("<div><a class=big_font><img src=\"themes/blue/expanded_view.png\" />'._selectFieldsToGenerateReport.'</a></div>",$extra['header_right']);
	echo '<h2 class="no-margin-top">'._selectFieldsToGenerateReport.'</h2>';

	foreach($fields_list as $category=>$fields)
	{
		
		echo '<h5>'.$category.'</h5>';
                echo '<div class="row">';
		foreach($fields as $field=>$title)
		{
			$i++;
			echo '<div class="col-sm-6 col-md-4"><div class="checkbox checkbox-switch switch-success"><label><INPUT type=checkbox onclick="addHTML(\'<LI>'.$title.'</LI>\',\'names_div\',false);addHTML(\'<INPUT type=hidden name=fields['.$field.'] value=Y>\',\'fields_div\',false);$(\'#names_div_none\').remove();this.disabled=true"><span></span>'.$title.'</label>'.($field=='PARENTS'?'<p class="help-block">(<label>Relation:</label> <input type=text id=relation name=relation size=8 class="form-control">)</p>':'').'</label></div></div>';
			//if($i%2==0)
			//echo '</TR><TR>';
		}
                echo '</div>';
		/*if($i%2!=0)
		{
			echo '<TD></TD></TR><TR>';
			$i++;
		}*/
	}
	//PopTable('footer');
	echo '</div><div class="col-sm-6 col-md-3">';
        
	echo '<h2 class="no-margin-top">'._selectedFields.'</h2>';
	echo '<div id="names_div_none" class="p-10 text-white bg-danger">'._noFieldsSelected.'</div><ol id=names_div class="selected_report_list"></ol>';
	

	echo '</div>';
	echo '</div>';
}

function Tot_School($value,$column)
{	

        $school=$value;
        $sql = "SELECT ID,TITLE FROM schools WHERE ID IN($school)";
	$QI = DBQuery($sql);
	$schools_RET = DBGet($QI);
        foreach($schools_RET as $key=>$s_name)
        $Schools[]=$s_name['TITLE'];
        $Schools=implode(",",$Schools);
        return $Schools;
        
}

function _makeLanguage($value,$column)
{
//    echo $value;
    $LANGUAGE_RET = DBGet(DBQuery("SELECT LANGUAGE_ID,LANGUAGE_NAME FROM language ORDER BY SORT_ORDER"));
foreach ($LANGUAGE_RET as $language_array) {
    $language[$language_array['LANGUAGE_ID']] = $language_array['LANGUAGE_NAME'];
}
return $language[$value];
}
