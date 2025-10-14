<?php
include 'Current.php';
include 'DbDateFnc.php';
include_once('JWT.php');
//use \Firebase\JWT\JWT;

//include '..functions/GetMpFnc.php';

function GetCurrentMPWs($mp,$date,$syear,$usrschool)
{	global $_openSIS;

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}

	if(!$_openSIS['GetCurrentMP'][$date][$mp])
	 	$_openSIS['GetCurrentMP'][$date][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE \''.$date.'\' BETWEEN START_DATE AND END_DATE AND SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));

	if($_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'])
		return $_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'];
}
function GetAllMPWs($mp,$marking_period_id='0',$syear,$usrschool)
{	global $_openSIS;

	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp) 
		 $mp = GetMPTable(GetMP($marking_period_id,'TABLE',$syear,$usrschool));
        
     // echo $marking_period_id;
	if(!$_openSIS['GetAllMP'][$mp])
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
				{
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMP($mp,$value['MARKING_PERIOD_ID']);
					if(substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$sem] = "'$fy','$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[MARKING_PERIOD_ID]'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''),array(),array('SEMESTER_ID'));
				$_openSIS['GetAllMP'][$mp][$marking_period_id] = "'$marking_period_id'";
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$value[MARKING_PERIOD_ID]'";
			break;
                        
		}
	}

	return $_openSIS['GetAllMP'][$mp][$marking_period_id];
} 
function GetAllMP_ModWs($mp,$marking_period_id='0',$syear,$usrschool)
{	global $_openSIS;

	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp) 
		 $mp = GetMPTable(GetMP($marking_period_id,'TABLE',$syear,$usrschool));
        
     
	if(!$_openSIS['GetAllMP'][$mp])
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
				{
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMPWs($mp,$value['MARKING_PERIOD_ID']);
					if(substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$sem] = "'$fy','$sem'";
//					foreach($value as $qtr)
//						$_openSIS['GetAllMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[MARKING_PERIOD_ID]'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
				$_openSIS['GetAllMP'][$mp][$marking_period_id] = "'$marking_period_id'";
				foreach($RET as $sem=>$value)
				{
//					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$sem'";
//					foreach($value as $qtr)
//						$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''));
//				foreach($RET as $value)
//					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$value[MARKING_PERIOD_ID]'";
			break;
                        
		}
	}

	return $_openSIS['GetAllMP'][$mp][$marking_period_id];
}

function GetParentMPWs($mp,$marking_period_id='0',$syear,$usrschool)
{	global $_openSIS;

	if(!$_openSIS['GetParentMP'][$mp])
	{
		switch($mp)
		{
			case 'QTR':

			break;

			case 'SEM':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID AS PARENT_ID FROM school_quarters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''),array(),array('MARKING_PERIOD_ID'));
			break;

			case 'FY':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,YEAR_ID AS PARENT_ID FROM school_semesters WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''),array(),array('MARKING_PERIOD_ID'));
			break;
		}
	}

	return $_openSIS['GetParentMP'][$mp][$marking_period_id][1]['PARENT_ID'];
}

function GetUserSchoolsWs($staff_id,$syear,$profile_id,$str=false,$profile='')
{
      if($profile_id!=4)
      {
          if(isset($profile) && $profile == 'parent')
          {
                $schools=DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID='.UserStudentIDWs().' AND SYEAR='.UserSyear().' ORDER BY ID DESC LIMIT 0,1'));
                return $schools[1]['SCHOOL_ID'];
          }
          else 
          {
            $str_return='';
            $schools=DBGet(DBQuery('SELECT SCHOOL_ID FROM staff_school_relationship WHERE staff_id='.$staff_id.' AND syear='.  $syear));
            foreach($schools as $school)
            {
                $return[]=$school['SCHOOL_ID'];
                $str_return .=$school['SCHOOL_ID'].',';
            }
            if($str==true)
                return substr($str_return,0,-1);
            else
                return $return;
          }
      }
      else if ($profile_id==4)
      {
          $schools=DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID='.UserStudentIDWs().' AND SYEAR='.UserSyear().' ORDER BY ID DESC LIMIT 0,1'));
          return $schools[1]['SCHOOL_ID'];
      }
}
function GetCpDetWs($cp_id,$key)
{	
    if($key!='' && $cp_id!='')
    $get_det=DBGet(DBQuery('SELECT '.strtoupper($key).' FROM course_periods WHERE COURSE_PERIOD_ID='.$cp_id));
    return $get_det[1][strtoupper($key)];
}
function GetMPIdWs($mp)
{	

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}
	$get_mp_id=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
        if($get_mp_id[1]['MARKING_PERIOD_ID']!='')
            return $get_mp_id[1]['MARKING_PERIOD_ID'];
        else
           return UserMP();
}
function PreferencesWs($item,$program='Preferences')
{	global $_openSIS;

	if($_SESSION['STAFF_ID'] && !$_openSIS['Preferences'][$program])
	{
		$QI=DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID='.$_SESSION['STAFF_ID'].' AND PROGRAM=\''.$program.'\'');
		$_openSIS['Preferences'][$program] = DBGet($QI);
	}

	$defaults = array('NAME'=>'Common',
				'SORT'=>'Name',
				'SEARCH'=>'Y',
				'DELIMITER'=>'Tab',
				'COLOR'=>'#FFFFCC',
				'HIGHLIGHT'=>'#85E1FF',
				'TITLES'=>'gray',
				'THEME'=>'Brushed-Steel',
				'HIDDEN'=>'Y',
				'MONTH'=>'M',
				'DAY'=>'j',
				'YEAR'=>'Y',
				'DEFAULT_ALL_SCHOOLS'=>'N',
				'ASSIGNMENT_SORTING'=>'ASSIGNMENT_ID',
				'ANOMALOUS_MAX'=>'100'
				);

	if(!isset($_openSIS['Preferences'][$program][$item][1]['VALUE']))
		$_openSIS['Preferences'][$program][$item][1]['VALUE'] = $defaults[$item];

//	if($_SESSION['STAFF_ID'] && User('PROFILE')=='parent' || $_SESSION['STUDENT_ID'])
//		$_openSIS['Preferences'][$program]['SEARCH'][1]['VALUE'] = 'N';

	return $_openSIS['Preferences'][$program][$item][1]['VALUE'];
}
//function DBQuery_assignment($sql)
//{	global $DatabaseType,$_openSIS;
//
//	$connection = db_start();
//
//	switch($DatabaseType)
//	{
//		case 'mysql':
//                    
////			$sql = str_replace('&amp;', "", $sql);
////			$sql = str_replace('&quot', "", $sql);
////			$sql = str_replace('&#039;', "", $sql);
////			$sql = str_replace('&lt;', "", $sql);
////			$sql = str_replace('&gt;', "", $sql);
//		  	$sql = ereg_replace("([,\(=])[\r\n\t ]*''",'\\1NULL',$sql);
//			if(preg_match_all("/'(\d\d-[A-Za-z]{3}-\d{2,4})'/",$sql,$matches))
//				{
//					foreach($matches[1] as $match)
//					{
//                                                $date_cheker_mod=explode('-',$match);
//                                                if(strlen($date_cheker_mod[2])==4 && $date_cheker_mod[2]<1970)
//                                                {
//                                                 $month_names=array('JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12');
//                                                 $date_cheker_mod[1]=$month_names[$date_cheker_mod[1]];
//                                                 $dt =$date_cheker_mod[2].'-'.$date_cheker_mod[1].'-'.$date_cheker_mod[0] ;
//                                                }
//                                                else
//						$dt = date('Y-m-d',strtotime($match));
//
//						$sql = preg_replace("/'$match'/","'$dt'",$sql);
//					}
//				}
//			if(substr($sql,0,6)=="BEGIN;")
//			{
//				$array = explode( ";", $sql );
//				foreach( $array as $value )
//				{
//					if($value!="")
//					{
//						$result = mysql_query($value);
//						if(!$result)
//						{
//							mysql_query("ROLLBACK");
//							die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
//						}
//					}
//				}
//			}
//			else
//			{
//				$result = mysql_query($sql) or die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
//			}
//		break;
//	}
//	return $result;
//}
function GetStuListWs(& $extra,$staff_id='',$from='')
{	
    global $contacts_RET,$view_other_RET,$_openSIS;
	$offset='GRADE_ID';
	$get_rollover_id=DBGet(DBQuery('SELECT ID FROM student_enrollment_codes WHERE SYEAR='.UserSyear().' AND TYPE=\'Roll\' '));
        $get_rollover_id=$get_rollover_id[1]['ID'];
	if((!$extra['SELECT_ONLY'] || strpos($extra['SELECT_ONLY'],$offset)!==false) && !$extra['functions']['GRADE_ID'])
		$functions = array('GRADE_ID'=>'GetGrade');
	else
		$functions = array();
        
	if($extra['functions'])
		$functions +=$extra['functions'];

	if(!$extra['DATE'])
	{
		$queryMP = UserMP();
		$extra['DATE'] = DBDate('mysql');
	}
	else
        {
		$queryMP = GetCurrentMPWs('QTR',$extra['DATE'],UserSyear(),UserSchool());
        }
        
	/*if($_REQUEST['expanded_view']=='true')
	{
		if(!$extra['columns_after'])
			$extra['columns_after'] = array();
#############################################################################################
//Commented as it crashing for Linux due to  Blank Database tables

		$view_fields_RET = DBGet(DBQuery('SELECT cf.ID,cf.TYPE,cf.TITLE FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM=\'StudentFieldsView\' AND puc.USER_ID=\''.$staff_id.'\' AND puc.VALUE=\'Y\''));
                
#############################################################################################
		$view_address_RET = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE=\'ADDRESS\' AND USER_ID=\''.UserWs('STAFF_ID').'\''));
                $view_address_RET = $view_address_RET[1]['VALUE'];
		$view_other_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE IN (\'PHONE\',\'HOME_PHONE\',\'GUARDIANS\',\'ALL_CONTACTS\') AND USER_ID=\''.UserWs('STAFF_ID').'\''));

		if(!count($view_fields_RET) && !isset($view_address_RET) && !isset($view_other_RET['CONTACT_INFO']))
		{
			$extra['columns_after'] = array('PHONE'=>'Phone','GENDER'=>'Gender','ETHNICITY'=>'Ethnicity','ADDRESS'=>'Mailing Address','CITY'=>'City','STATE'=>'State','ZIPCODE'=>'Zipcode') + $extra['columns_after'];

                        $select = ',s.PHONE,s.GENDER,s.ETHNICITY,COALESCE((SELECT STREET_ADDRESS_1 FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail"),sa.STREET_ADDRESS_1) AS ADDRESS,COALESCE((SELECT CITY FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail"),sa.CITY) AS CITY,COALESCE((SELECT STATE FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail"),sa.STATE) AS STATE,COALESCE((SELECT ZIPCODE FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail"),sa.ZIPCODE) AS ZIPCODE ';

			$extra['FROM'] = ' LEFT OUTER JOIN student_address sa ON (ssm.STUDENT_ID=sa.STUDENT_ID AND sa.TYPE=\'Home Address\' ) '.$extra['FROM'];
                        $functions['CONTACT_INFO'] = 'makeContactInfo';
			// if gender is converted to codeds type
			
			$extra['singular'] = 'Student Address';
			$extra['plural'] = 'Student Addresses';

			$extra2['NoSearchTerms'] = true;

                        $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,s.PHONE,sa.ID AS ADDRESS_ID ';
			$extra2['FROM'] .= ',student_address sa,students_join_people sjp,people p  ';
			$extra2['WHERE'] .= ' AND sa.STUDENT_ID=sjp.STUDENT_ID AND sa.STUDENT_ID=sjp.STUDENT_ID AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\') AND p.STAFF_ID=sjp.PERSON_ID  AND sa.STUDENT_ID=ssm.STUDENT_ID ';
			$extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';
			$extra2['group'] = array('STUDENT_ID','PERSON_ID');

			// EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
			if(!$_REQUEST['_openSIS_PDF'])
			{
				$expanded_view = $_REQUEST['expanded_view'];
				$_REQUEST['expanded_view'] = false;
				$addr = $_REQUEST['addr'];
				unset($_REQUEST['addr']);
				$contacts_RET = GetStuList($extra2);
				$_REQUEST['expanded_view'] = $expanded_view;
				$_REQUEST['addr'] = $addr;
			}
			else
				unset($extra2['columns_after']['CONTACT_INFO']);
		}
		else
		{
			if($view_other_RET['CONTACT_INFO'][1]['VALUE']=='Y' && !$_REQUEST['_openSIS_PDF'])
			{
				$select .= ',NULL AS CONTACT_INFO ';
				$extra['columns_after']['CONTACT_INFO'] = '<IMG SRC=assets/down_phone_button.gif border=0>';
				$functions['CONTACT_INFO'] = 'makeContactInfo';

				$extra2 = $extra;
				$extra2['NoSearchTerms'] = true;
				$extra2['SELECT'] = '';

				
                                
                                $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,a.PHONE';
				$extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (a.STUDENT_ID=sjp.STUDENT_ID AND sjp.IS_EMERGENCY=\'Y\') LEFT OUTER JOIN people p ON (p.STAFF=sjp.PERSON_ID) ';
				$extra2['WHERE'] .= ' AND a.STUDENT_ID=sjp.a.STUDENT_ID AND sjp.STUDENT_ID=ssm.STUDENT_ID ';
				$extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';
                                
                                $extra2['group'] = array('STUDENT_ID','PERSON_ID');
				$extra2['functions'] = array();
				$extra2['link'] = array();

				// EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
				$expanded_view = $_REQUEST['expanded_view'];
				$_REQUEST['expanded_view'] = false;
				$addr = $_REQUEST['addr'];
				unset($_REQUEST['addr']);
				$contacts_RET = GetStuList($extra2);
				$_REQUEST['expanded_view'] = $expanded_view;
				$_REQUEST['addr'] = $addr;
			}
			foreach($view_fields_RET as $field)
			{
                           $custom=DBGet(DBQuery('SHOW COLUMNS FROM students WHERE FIELD=\'CUSTOM_'.$field['ID'].'\''));
                           $custom=$custom[1];
                           if($custom)
                           {
				$extra['columns_after']['CUSTOM_'.$field['ID']] = $field['TITLE'];
				if($field['TYPE']=='date')
					$functions['CUSTOM_'.$field['ID']] = 'ProperDate';
				elseif($field['TYPE']=='numeric')
					$functions['CUSTOM_'.$field['ID']] = 'removeDot00';
				elseif($field['TYPE']=='codeds')
					$functions['CUSTOM_'.$field['ID']] = 'DeCodeds';
				$select .= ',s.CUSTOM_'.$field['ID'];
                           }
                           else
                           {
                               $custom_stu=DBGet(DBQuery('SELECT TYPE,TITLE FROM custom_fields WHERE ID=\''.$field['ID'].'\''));
                               $custom_stu=$custom_stu[1];
                               if($custom_stu['TYPE']=='date')
					$functions[strtolower(str_replace (" ", "_", $custom_stu['TITLE']))] = 'ProperDate';
				elseif($custom_stu['TYPE']=='numeric')
					$functions[strtolower(str_replace (" ", "_", $custom_stu['TITLE']))] = 'removeDot00';
				elseif($custom_stu['TYPE']=='codeds')
					$functions[strtolower(str_replace (" ", "_", $custom_stu['TITLE']))] = 'DeCodeds';
				$select .= ',s.'.strtoupper(str_replace (" ", "_", $custom_stu['TITLE']));
                               
                                $extra['columns_after'] += array(strtoupper (str_replace (" ", "_", $custom_stu['TITLE']))=>$custom_stu['TITLE']);
                           }
			}
			if($view_address_RET)
			{
//				
				if($view_address_RET=='RESIDENCE')
                                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\')  '.$extra['FROM'];
                                elseif($view_address_RET=='MAILING')
                                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Mail\') '.$extra['FROM'];
                                elseif($view_address_RET=='BUS_PICKUP')
                                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_PICKUP=\'Y\') '.$extra['FROM'];
                                else
                                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_DROPOFF=\'Y\') '.$extra['FROM'];
                               
				$extra['columns_after'] += array('ADDRESS'=>ucwords(strtolower(str_replace('_',' ',$view_address_RET))).' Address','CITY'=>'City','STATE'=>'State','ZIPCODE'=>'Zipcode');
				
                                $select .= ',sam.ID as ADDRESS_ID,sam.STREET_ADDRESS_1 as ADDRESS,sam.CITY,sam.STATE,sam.ZIPCODE,s.PHONE,ssm.STUDENT_ID AS PARENTS';
				
                                $extra['singular'] = 'Student Address';
				$extra['plural'] = 'Student Addresses';
                                 
				if($view_other_RET['HOME_PHONE'][1]['VALUE']=='Y')
				{
					$functions['PHONE'] = 'makePhone';
					$extra['columns_after']['PHONE'] = 'Home Phone';
				}
				if($view_other_RET['GUARDIANS'][1]['VALUE']=='Y' || $view_other_RET['ALL_CONTACTS'][1]['VALUE']=='Y')
				{
					$functions['PARENTS'] = 'makeParents';
					if($view_other_RET['ALL_CONTACTS'][1]['VALUE']=='Y')
						$extra['columns_after']['PARENTS'] = 'Contacts';
					else
						$extra['columns_after']['PARENTS'] = 'Guardians';
				}
			}
			elseif($_REQUEST['addr'] || $extra['addr'])
			{
				$extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\' ) '.$extra['FROM'];
				$distinct = 'DISTINCT ';
			}
		}
                
		$extra['SELECT'] .= $select;
	}
        */
	if($_REQUEST['addr']||$_REQUEST['city']||$_REQUEST['state']||$_REQUEST['zip'] || $extra['addr'])
	{
		$extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\' ) '.$extra['FROM'];
		$distinct = 'DISTINCT ';
	}
	switch(UserWs('PROFILE'))
	{
		case 'admin':
		
                    $sql = 'SELECT ';
                                                    if($extra['DISTINCT'])
                                                        $sql .='DISTINCT ';
			if($extra['SELECT_ONLY'])
				$sql .= $extra['SELECT_ONLY'];
			else
			{
				
                            if(PreferencesWs('NAME')=='Common')
					$sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
				else
					$sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
				$sql .='s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,ssm.SCHOOL_ID,s.ALT_ID,ssm.SCHOOL_ID AS LIST_SCHOOL_ID,ssm.GRADE_ID'.$extra['SELECT'];
				
				if($_REQUEST['include_inactive']=='Y')
				$sql .= ','.db_case(array('(ssm.SYEAR=\''.UserSyear().'\' AND (ssm.START_DATE IS NOT NULL AND \''.date('Y-m-d',strtotime($extra['DATE'])).'\'>=ssm.START_DATE AND ((\''.date('Y-m-d',strtotime($extra['DATE'])).'\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) AND s.IS_DISABLE IS NULL ) OR ssm.DROP_CODE='.$get_rollover_id.') )','true',"'<FONT color=green>Active</FONT>'","'<FONT color=red>Inactive</FONT>'")).' AS ACTIVE ';
				
			}
			
			$sql .= ' FROM students s ';
			if($_REQUEST['mp_comment']){
			$sql .= ',student_mp_comments smc ';
			}
			if($_REQUEST['goal_title'] || $_REQUEST['goal_description']){
			$sql .= ',student_goal g ';
			}
			if($_REQUEST['progress_name'] || $_REQUEST['progress_description']){
			$sql .= ',student_goal_progress p ';
			}
			if($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']){
			$sql .= ',student_medical_notes smn ';
			}
			if($_REQUEST['type']||$_REQUEST['imm_comments'] || $_REQUEST['imm_day']|| $_REQUEST['imm_month'] || $_REQUEST['imm_year']){
			$sql .= ',student_immunization sm ';
			}
			if($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']){
			$sql .= ',student_medical_alerts sma ';
			}
if($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments']||  $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']){
			$sql .= ',student_medical_visits smv ';
			}
			$sql .=',student_enrollment ssm ';
		$sql.=$extra['FROM'].' WHERE ssm.STUDENT_ID=s.STUDENT_ID ';
                if($_REQUEST['modname']!='students/StudentReenroll.php')
                {
			if($_REQUEST['include_inactive']=='Y' && $_REQUEST['_search_all_schools']!='Y')
				$sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY START_DATE DESC LIMIT 1)';

                            if(!$_REQUEST['include_inactive'])
				 $sql .= $_SESSION['inactive_stu_filter'] =' AND ssm.SYEAR=\''.UserSyear().'\' AND ((ssm.START_DATE IS NOT NULL AND (\''.date('Y-m-d',strtotime($extra['DATE'])).'\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) AND \''.date('Y-m-d',strtotime($extra['DATE'])).'\'>=ssm.START_DATE) OR ssm.DROP_CODE='.$get_rollover_id.') ';

                                                        if($_REQUEST['address_group'])
                                                            $extra['columns_after']['CHILD'] = 'Parent';
			if(UserSchool() && $_REQUEST['_search_all_schools']!='Y')
				$sql .= ' AND ssm.SYEAR=\''.UserSyear().'\' AND ssm.SCHOOL_ID=\''.UserSchool().'\'';
			else
			{

					$sql .= ' AND ssm.SCHOOL_ID IN ('.GetUserSchools(UserID(),true).') ';
				$extra['columns_after']['LIST_SCHOOL_ID'] = 'School';
				$functions['LIST_SCHOOL_ID'] = 'GetSchool';
			}

			if(!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive']=='Y')
				$extra['columns_after']['ACTIVE'] = 'Status';
                }
                else 
                {
                   if($_REQUEST['_search_all_schools']=='Y')
                   {

					$sql .= ' AND ssm.SCHOOL_ID IN ('.GetUserSchools(UserID(),true).') ';
                   }
                   else
                   {
                       $sql .= ' AND ssm.SCHOOL_ID=\''.UserSchool().'\'';
                   }
                }
				
		break;

		case 'teacher':
                   
			$sql = 'SELECT ';
			if($extra['SELECT_ONLY'])
				$sql .= $extra['SELECT_ONLY'];
			else
			{
				if(PreferencesWs('NAME')=='Common')
					$sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
				else
					$sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
				$sql .='s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID '.$extra['SELECT'];
				
                                if($_REQUEST['include_inactive']=='Y')
				{
					$sql .= ','.db_case(array('(ssm.START_DATE IS NOT NULL AND (\''.$extra['DATE'].'\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) AND s.IS_DISABLE IS NULL)','true',"'Active'","'Inactive'")).' AS ACTIVE';
					$sql .= ','.db_case(array('(ssm.START_DATE IS NOT NULL AND (\''.$extra['DATE'].'\'<=ss.END_DATE ) AND s.IS_DISABLE IS NULL)','true',"'Active'","'Inactive'")).' AS ACTIVE_SCHEDULE';
				}
			}

			$sql .= ' FROM students s,course_periods cp,schedule ss ';
			if($_REQUEST['mp_comment']){
			$sql .= ',student_mp_comments smc ';
			}
			if($_REQUEST['goal_title'] || $_REQUEST['goal_description']){
			$sql .= ',student_goal g ';
			}
			if($_REQUEST['progress_name'] || $_REQUEST['progress_description']){
			$sql .= ',student_goal_progress p ';
			}
			if($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']){
			$sql .= ',student_medical_notes smn ';
			}
			if($_REQUEST['type']||$_REQUEST['imm_comments'] || $_REQUEST['imm_day']|| $_REQUEST['imm_month'] || $_REQUEST['imm_year']){
			$sql .= ',student_immunization sm ';
			}
			if($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']){
			$sql .= ',student_medical_alerts sma ';
			}
if($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments']||  $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']){
			$sql .= ',student_medical_visits smv ';
			}
			$sql.=' ,student_enrollment ssm ';
			$sql.=$extra['FROM'].' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID
					AND ssm.SCHOOL_ID=\''.UserSchool().'\' AND ssm.SYEAR=\''.UserSyear().'\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR
					AND (ss.MARKING_PERIOD_ID IN ('.  GetAllMP_ModWs('',$queryMP,UserSyear(),  UserSchool()).')   OR (ss.START_DATE<=\''.date('Y-m-d').'\'  AND (ss.END_DATE>=\''.date('Y-m-d').'\'  OR ss.END_DATE IS NULL)))
					AND (cp.TEACHER_ID=\''.UserWs('STAFF_ID').'\' OR cp.SECONDARY_TEACHER_ID=\''.UserWs('STAFF_ID').'\') AND cp.COURSE_PERIOD_ID=\''.UserCoursePeriod().'\'
					AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';

			if($_REQUEST['include_inactive']=='Y' && $_REQUEST['_search_all_schools']!='Y')
			{
				$sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY START_DATE DESC LIMIT 1)';
				$sql .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND MARKING_PERIOD_ID IN ('.GetAllMPWs('',$queryMP,UserSyear(),  UserSchool()).') AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
			}
			else
			{
				$sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\''.$extra['DATE'].'\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
				$sql .= $_SESSION['inactive_stu_filter'] =' AND (ssm.START_DATE IS NOT NULL AND (\''.$extra['DATE'].'\'<=ss.END_DATE OR ss.END_DATE IS NULL))';
                        
			}

			if(!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive']=='Y')
			{
				$extra['columns_after']['ACTIVE'] = 'School Status';
				$extra['columns_after']['ACTIVE_SCHEDULE'] = 'Course Status';
			}
		break;

		case 'parent':
		case 'student':
                    
			$sql = 'SELECT ';
			if($extra['SELECT_ONLY'])
				$sql .= $extra['SELECT_ONLY'];
			else
			{
				if(PreferencesWs('NAME')=='Common')
					$sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
				else
					$sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
				$sql .='s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.ALT_ID,s.GENDER,ssm.SCHOOL_ID,ssm.GRADE_ID '.$extra['SELECT'];
			}
			if($from=='gpa_ranklist')
                        $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate('mysql') . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate('mysql') . '\'>=ssm.START_DATE))';
                        else
                         $sql .= ' FROM students s,student_enrollment ssm '.$extra['FROM'].'
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\''.UserSyear().'\' AND ssm.SCHOOL_ID=\''.UserSchool().'\' AND (\''.DBDate('mysql').'\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \''.DBDate('mysql').'\'>=ssm.START_DATE)) AND ssm.STUDENT_ID'.($extra['ASSOCIATED']?' IN (SELECT STUDENT_ID FROM students_join_people WHERE PERSON_ID=\''.$extra['ASSOCIATED'].'\')':'=\''.UserStudentIDWs().'\'');
		break;
		default:
			exit('Error');
	}
        if($expanded_view==true)
        {
            $custom_str=CustomFieldsWs('where','',1);
            if($custom_str!='')
                $_SESSION['custom_count_sql']=$custom_str;
            
            $sql .= $custom_str;
        }
        elseif($expanded_view==false)
        {
            $custom_str=CustomFieldsWs('where','',2);
            if($custom_str!='')
                $_SESSION['custom_count_sql']=$custom_str;

            $sql .= $custom_str;
        }
        else {
            $custom_str = CustomFieldsWs('where');
            if($custom_str!='')
                $_SESSION['custom_count_sql']=$custom_str;
            
             $sql .= $custom_str;
        }

        $sql .= $extra['WHERE'].' ';
        if ($_REQUEST['include_inactive'] != 'Y') {
        $sql.= 'AND s.IS_DISABLE IS NULL';
        }
	$sql = appendSQLWs($sql,$extra);

//        TODO               Modification Required


	if($extra['GROUP'])
		$sql .= ' GROUP BY '.$extra['GROUP'];

	if(!$extra['ORDER_BY'] && !$extra['SELECT_ONLY'])
	{
		if(PreferencesWs('SORT')=='Grade')
			$sql .= ' ORDER BY (SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID),FULL_NAME';
		else
			$sql .= ' ORDER BY FULL_NAME';
		$sql .= $extra['ORDER'];
	}
	elseif($extra['ORDER_BY'] && !($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']))
		$sql .= ' ORDER BY '.$extra['ORDER_BY'];

	if($extra['DEBUG']===true)
		echo '<!--'.$sql.'-->';
        //echo $sql;
	$return = DBGet(DBQuery($sql),$functions,$extra['group']);
        
                  $_SESSION['count_stu'] =  count($return);
//                  if($_REQUEST['modname'] == 'students/Student.php' && $_REQUEST['search_modfunc']=='list')
//                    $_SESSION['total_stu']=$_SESSION['count_stu'];
                  return $return;
}
function appendSQLWs($sql,& $extra)
{	global $_openSIS;

	if($_REQUEST['stuid'])
	{
		$sql .= ' AND ssm.STUDENT_ID = \''.str_replace("'","\'",$_REQUEST[stuid]).'\' ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>'.$_REQUEST['stuid'].'<BR>';
	}
         if($_REQUEST['altid'])
	{
		
		$sql .= ' AND LOWER(s.ALT_ID) LIKE \''.str_replace("'","\'",strtolower(trim($_REQUEST['altid']))).'%\' ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>'.$_REQUEST['stuid'].'<BR>';
	}
	if($_REQUEST['last'])
	{
		$sql .= ' AND LOWER(s.LAST_NAME) LIKE \''.str_replace("'","\'",strtolower(trim($_REQUEST['last']))).'%\' ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Last Name starts with: </b></font>'.trim($_REQUEST['last']).'<BR>';
	}
	if($_REQUEST['first'])
	{
		$sql .= ' AND LOWER(s.FIRST_NAME) LIKE \''.str_replace("'","\'",strtolower(trim($_REQUEST['first']))).'%\' ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>First Name starts with: </b></font>'.trim($_REQUEST['first']).'<BR>';
	}
	if($_REQUEST['grade'])
	{
		$sql .= ' AND ssm.GRADE_ID IN(SELECT id FROM school_gradelevels WHERE title= \''.str_replace("'","\'",$_REQUEST[grade]).'\')';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Grade: </b></font>'.$_REQUEST['grade'].'<BR>';
	}
	if($_REQUEST['addr']||$_REQUEST['city']||$_REQUEST['state']||$_REQUEST['zip'])
	{
	if($_REQUEST['addr'])
                $sql_chk[] = 'LOWER(sam.STREET_ADDRESS_1) LIKE \'%'.str_replace("'","\'",strtolower(trim($_REQUEST['addr']))).'%\'';
            if($_REQUEST['city'])
                $sql_chk[] = 'LOWER(sam.CITY) LIKE \''.str_replace("'","\'",strtolower(trim($_REQUEST['city']))).'%\'';
            if($_REQUEST['state'])
                $sql_chk[] = 'LOWER(sam.STATE)=\''.str_replace("'","\'",strtolower(trim($_REQUEST['state']))).'\'';
            if($_REQUEST['zip'])
                $sql_chk[] = 'ZIPCODE LIKE \''.trim(str_replace("'","\'",$_REQUEST['zip'])).'%\'';
            
            $sql .= ' AND ('.implode(' OR ',$sql_chk).')';
//		$sql .= ' AND (LOWER(sam.STREET_ADDRESS_1) LIKE \'%'.str_replace("'","\'",strtolower(trim($_REQUEST['addr']))).'%\' OR LOWER(sam.CITY) LIKE \''.str_replace("'","\'",strtolower(trim($_REQUEST['city']))).'%\' OR LOWER(sam.STATE)=\''.str_replace("'","\'",strtolower(trim($_REQUEST['state']))).'\' OR ZIPCODE LIKE \''.trim(str_replace("'","\'",$_REQUEST['zip'])).'%\')';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Address contains: </b></font>'.trim($_REQUEST['addr']).trim($_REQUEST['city']).trim($_REQUEST['state']).trim($_REQUEST['zip']).'<BR>';
	}
	if($_REQUEST['preferred_hospital'])
	{
		$sql .= ' AND LOWER(s.PREFERRED_HOSPITAL) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['preferred_hospital'])).'%\' ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Preferred Medical Facility starts with: </b></font>'.$_REQUEST['preferred_hospital'].'<BR>';
	}
	if($_REQUEST['mp_comment'])
	{
		$sql .= ' AND LOWER(smc.COMMENT) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['mp_comment'])).'%\' AND s.STUDENT_ID=smc.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>'.$_REQUEST['mp_comment'].'<BR>';
	}
	if($_REQUEST['goal_title'])
	{
		$sql .= ' AND LOWER(g.GOAL_TITLE) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['goal_title'])).'%\' AND s.STUDENT_ID=g.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Title starts with: </b></font>'.$_REQUEST['goal_title'].'<BR>';
	}
		if($_REQUEST['goal_description'])
	{
		$sql .= ' AND LOWER(g.GOAL_DESCRIPTION) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['goal_description'])).'%\' AND s.STUDENT_ID=g.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Description starts with: </b></font>'.$_REQUEST['goal_description'].'<BR>';
	}
		if($_REQUEST['progress_name'])
	{
		$sql .= ' AND LOWER(p.PROGRESS_NAME) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['progress_name'])).'%\' AND s.STUDENT_ID=p.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Period Name starts with: </b></font>'.$_REQUEST['progress_name'].'<BR>';
	}
	if($_REQUEST['progress_description'])
	{
		$sql .= ' AND LOWER(p.PROGRESS_DESCRIPTION) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['progress_description'])).'%\' AND s.STUDENT_ID=p.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Assessment starts with: </b></font>'.$_REQUEST['progress_description'].'<BR>';
	}
	if($_REQUEST['doctors_note_comments'])
	{
		$sql .= ' AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['doctors_note_comments'])).'%\' AND s.STUDENT_ID=smn.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Doctor\'s Note starts with: </b></font>'.$_REQUEST['doctors_note_comments'].'<BR>';
	}
	if($_REQUEST['type'])
	{
		$sql .= ' AND LOWER(sm.TYPE) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['type'])).'%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Type starts with: </b></font>'.$_REQUEST['type'].'<BR>';
	}
	if($_REQUEST['imm_comments'])
	{
		$sql .= ' AND LOWER(sm.COMMENTS) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['imm_comments'])).'%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>'.$_REQUEST['imm_comments'].'<BR>';
	}
	if($_REQUEST['imm_day']&& $_REQUEST['imm_month']&& $_REQUEST['imm_year'])
	{
$imm_date=$_REQUEST['imm_year'].'-'.$_REQUEST['imm_month'].'-'.$_REQUEST['imm_day'];
		$sql .= ' AND sm.MEDICAL_DATE =\''.date('Y-m-d',strtotime($imm_date)).'\' AND s.STUDENT_ID=sm.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>'.$imm_date.'<BR>';
	}elseif($_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']){
	if($_REQUEST['imm_day']){
	$sql .= ' AND SUBSTR(sm.MEDICAL_DATE,9,2) =\''.$_REQUEST['imm_day'].'\' AND s.STUDENT_ID=sm.STUDENT_ID ';
	$imm_date.=" Day :".$_REQUEST['imm_day'];
	}
	if($_REQUEST['imm_month']){
	$sql .= ' AND SUBSTR(sm.MEDICAL_DATE,6,2) =\''.$_REQUEST['imm_month'].'\' AND s.STUDENT_ID=sm.STUDENT_ID ';
	$imm_date.=" Month :".$_REQUEST['imm_month'];
	}
	if($_REQUEST['imm_year']){
	$sql .= ' AND SUBSTR(sm.MEDICAL_DATE,1,4) =\''.$_REQUEST['imm_year'].'\' AND s.STUDENT_ID=sm.STUDENT_ID ';
	$imm_date.=" Year :".$_REQUEST['imm_year'];
	}
	if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>'.$imm_date.'<BR>';
	}
	if($_REQUEST['med_day']&&$_REQUEST['med_month']&&$_REQUEST['med_year'])
	{
$med_date=$_REQUEST['med_year'].'-'.$_REQUEST['med_month'].'-'.$_REQUEST['med_day'];
		$sql .= ' AND smn.DOCTORS_NOTE_DATE =\''.date('Y-m-d',strtotime($med_date)).'\' AND s.STUDENT_ID=smn.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>'.$med_date.'<BR>';
	}elseif($_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']){
	if($_REQUEST['med_day']){
	$sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) =\''.$_REQUEST['med_day'].'\' AND s.STUDENT_ID=smn.STUDENT_ID ';
	$med_date.=" Day :".$_REQUEST['med_day'];
	}
	if($_REQUEST['med_month']){
	$sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) =\''.$_REQUEST['med_month'].'\' AND s.STUDENT_ID=smn.STUDENT_ID ';
	$med_date.=" Month :".$_REQUEST['med_month'];
	}
	if($_REQUEST['med_year']){
	$sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) =\''.$_REQUEST['med_year'].'\' AND s.STUDENT_ID=smn.STUDENT_ID ';
	$med_date.=" Year :".$_REQUEST['med_year'];
	}
	if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>'.$med_date.'<BR>';
	}
	if($_REQUEST['ma_day']&&$_REQUEST['ma_month']&&$_REQUEST['ma_year'])
	{
$ma_date=$_REQUEST['ma_year'].'-'.$_REQUEST['ma_month'].'-'.$_REQUEST['ma_day'];
		$sql .= ' AND sma.ALERT_DATE =\''.date('Y-m-d',strtotime($ma_date)).'\' AND s.STUDENT_ID=sma.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>'.$ma_date.'<BR>';
	}elseif($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']){
	if($_REQUEST['ma_day']){
	$sql .= ' AND SUBSTR(sma.ALERT_DATE,9,2) =\''.$_REQUEST['ma_day'].'\' AND s.STUDENT_ID=sma.STUDENT_ID ';
	$ma_date.=" Day :".$_REQUEST['ma_day'];
	}
	if($_REQUEST['ma_month']){
	$sql .= ' AND SUBSTR(sma.ALERT_DATE,6,2) =\''.$_REQUEST['ma_month'].'\' AND s.STUDENT_ID=sma.STUDENT_ID ';
	$ma_date.=" Month :".$_REQUEST['ma_month'];
	}
	if($_REQUEST['ma_year']){
	$sql .= ' AND SUBSTR(sma.ALERT_DATE,1,4) =\''.$_REQUEST['ma_year'].'\' AND s.STUDENT_ID=sma.STUDENT_ID ';
	$ma_date.=" Year :".$_REQUEST['ma_year'];
	}
	if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>'.$ma_date.'<BR>';
	}
	if($_REQUEST['nv_day']&&$_REQUEST['nv_month']&&$_REQUEST['nv_year'])
	{
$nv_date=$_REQUEST['nv_year'].'-'.$_REQUEST['nv_month'].'-'.$_REQUEST['nv_day'];
		$sql .= ' AND smv.SCHOOL_DATE =\''.date('Y-m-d',strtotime($nv_date)).'\' AND s.STUDENT_ID=smv.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>'.$nv_date.'<BR>';
	}elseif($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']){
	if($_REQUEST['nv_day']){
	$sql .= ' AND SUBSTR(smv.SCHOOL_DATE,9,2) =\''.$_REQUEST['nv_day'].'\' AND s.STUDENT_ID=smv.STUDENT_ID ';
	$nv_date.=" Day :".$_REQUEST['nv_day'];
	}
	if($_REQUEST['nv_month']){
	$sql .= ' AND SUBSTR(smv.SCHOOL_DATE,6,2) =\''.$_REQUEST['nv_month'].'\' AND s.STUDENT_ID=smv.STUDENT_ID ';
	$nv_date.=" Month :".$_REQUEST['nv_month'];
	}
	if($_REQUEST['nv_year']){
	$sql .= ' AND SUBSTR(smv.SCHOOL_DATE,1,4) =\''.$_REQUEST['nv_year'].'\' AND s.STUDENT_ID=smv.STUDENT_ID ';
	$nv_date.=" Year :".$_REQUEST['nv_year'];
	}
	if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>'.$nv_date.'<BR>';
	}
	
	
	if($_REQUEST['med_alrt_title'])
	{
		$sql .= ' AND LOWER(sma.TITLE) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['med_alrt_title'])).'%\' AND s.STUDENT_ID=sma.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Alert starts with: </b></font>'.$_REQUEST['med_alrt_title'].'<BR>';
	}
	if($_REQUEST['reason'])
	{
		$sql .= ' AND LOWER(smv.REASON) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['reason'])).'%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Reason starts with: </b></font>'.$_REQUEST['reason'].'<BR>';
	}
	if($_REQUEST['result'])
	{
		$sql .= ' AND LOWER(smv.RESULT) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['result'])).'%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Result starts with: </b></font>'.$_REQUEST['result'].'<BR>';
	}
	if($_REQUEST['med_vist_comments'])
	{
		$sql .= ' AND LOWER(smv.COMMENTS) LIKE \''.str_replace("'","\'",strtolower($_REQUEST['med_vist_comments'])).'%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Comments starts with: </b></font>'.$_REQUEST['med_vist_comments'].'<BR>';
	}
	if($_REQUEST['day_to_birthdate']&&$_REQUEST['month_to_birthdate']&&$_REQUEST['day_from_birthdate']&&$_REQUEST['month_from_birthdate'])
	{
	$date_to=$_REQUEST['month_to_birthdate'].'-'.$_REQUEST['day_to_birthdate'];
	$date_from=$_REQUEST['month_from_birthdate'].'-'.$_REQUEST['day_from_birthdate'];
		$sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \''.$_REQUEST['month_from_birthdate'].'\' AND \''.$_REQUEST['month_to_birthdate'].'\') ';
		$sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \''.$_REQUEST['day_from_birthdate'].'\' AND \''.$_REQUEST['day_to_birthdate'].'\') ';
		if(!$extra['NoSearchTerms'])
			$_openSIS['SearchTerms'] .= '<font color=gray><b>Birthday Starts from '.$date_from.' to '.$date_to.'</b></font>';
	}	
	// test cases start
	
	
	
	// test cases end
	if($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']){
            unset($_SESSION['inactive_stu_filter']);
            return $_SESSION['stu_search']['sql'];
	}else{
            if($_REQUEST['sql_save_session'] && !$_SESSION['stu_search']['search_from_grade']){
                $_SESSION['stu_search']['sql']=$sql;
            }else if($_SESSION['stu_search']['search_from_grade']){
                unset($_SESSION['stu_search']['search_from_grade']);
            }
	return $sql;
	}
}
function UserWs($item)
{	//global $_openSIS,$DefaultSyear;
    
	if(!$_SESSION['UserSyear'])
		$_SESSION['UserSyear'] = $DefaultSyear;

	if(!$_openSIS['User'] || $_SESSION['UserSyear']!=$_openSIS['User'][1]['SYEAR'])
	{
		if($_SESSION['STAFF_ID'])
		{
                    $profile=DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE STAFF_ID=".$_SESSION['STAFF_ID']));
                    if($_SESSION['PROFILE_ID']=='')
                    $_SESSION['PROFILE_ID'] = $profile[1]['PROFILE_ID'];
                    if($_SESSION['PROFILE_ID']!=4)
                    $sql = 'SELECT STAFF_ID,USERNAME,CONCAT(FIRST_NAME,\' \',LAST_NAME) AS NAME,PROFILE,la.PROFILE_ID,CURRENT_SCHOOL_ID,EMAIL FROM staff s ,login_authentication la WHERE la.USER_ID=s.STAFF_ID AND la.PROFILE_ID <> 3 AND la.PROFILE_ID=s.PROFILE_ID AND STAFF_ID='.$_SESSION[STAFF_ID];
                    if($_SESSION['PROFILE_ID']==4)
                    $sql = 'SELECT p.STAFF_ID,la.USERNAME,CONCAT(p.FIRST_NAME,\' \',p.LAST_NAME) AS NAME,p.PROFILE,p.PROFILE_ID,p.CURRENT_SCHOOL_ID,p.EMAIL FROM people p ,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PROFILE_ID <> 3  AND la.PROFILE_ID=p.PROFILE_ID AND STAFF_ID='.$_SESSION[STAFF_ID];
                    $_openSIS['User'] = DBGet(DBQuery($sql));
		}
		elseif($_SESSION['STUDENT_ID'])
		{
			$sql = 'SELECT USERNAME,CONCAT(s.FIRST_NAME,\' \',s.LAST_NAME) AS NAME,\'student\' AS PROFILE,\'3\' AS PROFILE_ID,CONCAT(\',\',se.SCHOOL_ID,\',\') AS SCHOOLS,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se,login_authentication la WHERE la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID = 3 AND s.STUDENT_ID='.$_SESSION[STUDENT_ID].' AND se.SYEAR=\''.$_SESSION[UserSyear].'\'  AND (se.END_DATE IS NULL OR se.END_DATE=\'0000-00-00\' OR se.END_DATE>=\''.date('Y-m-d').'\' ) AND se.STUDENT_ID=s.STUDENT_ID ORDER BY se.END_DATE DESC LIMIT 1';
                        $_openSIS['User'] = DBGet(DBQuery($sql));
			$_SESSION['UserSchool'] = $_openSIS['User'][1]['SCHOOL_ID'];
		}
		else
			exit('Error in User()');
	}

	return $_openSIS['User'][1][$item];
}
function CustomFieldsWs($location,$table_arr='',$exp=0)
{	global $_openSIS;
	if(count($_REQUEST['month_cust_begin']))
	{
		foreach($_REQUEST['month_cust_begin'] as $field_name=>$month)
		{
			$_REQUEST['cust_begin'][$field_name] = $_REQUEST['day_cust_begin'][$field_name].'-'.$_REQUEST['month_cust_begin'][$field_name].'-'.$_REQUEST['year_cust_begin'][$field_name];
			$_REQUEST['cust_end'][$field_name] = $_REQUEST['day_cust_end'][$field_name].'-'.$_REQUEST['month_cust_end'][$field_name].'-'.$_REQUEST['year_cust_end'][$field_name];
			if(!VerifyDate($_REQUEST['cust_begin'][$field_name]) || !VerifyDate($_REQUEST['cust_end'][$field_name]))
			{
				unset($_REQUEST['cust_begin'][$field_name]);
				unset($_REQUEST['cust_end'][$field_name]);
			}
		}
		unset($_REQUEST['month_cust_begin']);unset($_REQUEST['year_cust_begin']);unset($_REQUEST['day_cust_begin']);
		unset($_REQUEST['month_cust_end']);unset($_REQUEST['year_cust_end']);unset($_REQUEST['day_cust_end']);
	}
	if(count($_REQUEST['cust']))
	{
		foreach($_REQUEST['cust'] as $key=>$value)
		{
			if($value=='')
				unset($_REQUEST['cust'][$key]);
		}
	}
	switch($location)
	{
		case 'from':
		break;

		case 'where':
		if(count($_REQUEST['cust']) || count($_REQUEST['cust_begin']))
			$fields = DBGet(DBQuery('SELECT TITLE,ID,TYPE,SYSTEM_FIELD FROM custom_fields'));

		if(count($_REQUEST['cust']))
		{
			foreach($_REQUEST['cust'] as $id => $value)
			{
				$field_name = $id;
				$id = substr($id,7);
				if($fields[$id][1]['SYSTEM_FIELD'] == 'Y')
					$field_name = strtoupper(str_replace(' ','_',$fields[$id][1]['TITLE']));
				if($value!='')
				{
					switch($fields[$id][1]['TYPE'])
					{
						case 'radio':
							$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>';
							if($value=='Y')
							{
								$string .= ' and s.'.$field_name.'=\''.$value.'\' ';
								$_openSIS['SearchTerms'] .= 'Yes';
							}
							elseif($value=='N')
							{
								$string .= ' and (s.'.$field_name.'!=\'Y\' OR s.'.$field_name.' IS NULL) ';
								$_openSIS['SearchTerms'] .= 'No';
							}
							$_openSIS['SearchTerms'] .= '<BR>';
						break;

						case 'codeds':
							$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>';
							if($value=='!')
							{
								$string .= ' and (s.'.$field_name.'=\'\' OR s.'.$field_name.' IS NULL) ';
								$_openSIS['SearchTerms'] .= 'No Value';
							}
							else
							{
								$string .= ' and s.'.$field_name.'=\''.$value.'\' ';
								$_openSIS['SearchTerms'] .= $value;
							}
							$_openSIS['SearchTerms'] .= '<BR>';
							break;

						case 'select':
							$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>';
							if($value=='!')
							{
								$string .= ' and (s.'.$field_name.'=\'\' OR s.'.$field_name.' IS NULL) ';
								$_openSIS['SearchTerms'] .= 'No Value';
							}
							else
							{
								$string .= ' and s.'.$field_name.'=\''.$value.'\' ';
								$_openSIS['SearchTerms'] .= $value;
							}
							$_openSIS['SearchTerms'] .= '<BR>';
							break;

						case 'autos':
							$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>';
							if($value=='!')
							{
								$string .= ' and (s.'.$field_name.'=\'\' OR s.'.$field_name.' IS NULL) ';
								$_openSIS['SearchTerms'] .= 'No Value';
							}
							else
							{
								$string .= ' and s.'.$field_name.'=\''.$value.'\' ';
								$_openSIS['SearchTerms'] .= $value;
							}
							$_openSIS['SearchTerms'] .= '<BR>';
							break;

						case 'edits':
							$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>';
							if($value=='!')
							{
								$string .= ' and (s.'.$field_name.'=\'\' OR s.'.$field_name.' IS NULL) ';
								$_openSIS['SearchTerms'] .= 'No Value';
							}
							elseif($value=='~')
							{
								$string .= " and position('\n'||s.$field_name||'\r' IN '\n'||(SELECT SELECT_OPTIONS FROM custom_fields WHERE ID='".$id."')||'\r')=0 ";
								$_openSIS['SearchTerms'] .= 'Other';
							}
							else
							{
								$string .= ' and s.'.$field_name.'=\''.$value.'\' ';
								$_openSIS['SearchTerms'] .= $value;
							}
							$_openSIS['SearchTerms'] .= '<BR>';
							break;

						case 'text':
							if(substr($value,0,2)=='\"' && substr($value,-2)=='\"')
							{
								$string .= ' and s.'.$field_name.'=\''.substr($value,2,-2).'\' ';
								$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].': </b></font>'.substr($value,2,-2).'<BR>';
							}
							else
							{
								$string .= ' and LOWER(s.'.$field_name.') LIKE \''.strtolower($value).'%\' ';
                                                                if($exp==1)
								$_openSIS['Search'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].' starts with: </b></font>'.$value.'<BR>';
                                                            elseif($exp==2){
                                                                $_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].' starts with: </b></font>'.$value.'<BR>';
                                                            }
                                                             else {
                                                                          $_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].' starts with: </b></font>'.$value.'<BR>';
                                                            }
							}
						break;
					}
				}
			}
		}
		if(count($_REQUEST['cust_begin']))
		{
			foreach($_REQUEST['cust_begin'] as $id => $value)
			{
				$field_name = $id;
				$id = substr($id,7);
				$column_name = $field_name;
				if($fields[$id][1]['SYSTEM_FIELD'] == 'Y')
					$column_name = strtoupper(str_replace(' ','_',$fields[$id][1]['TITLE']));
				if($fields[$id][1]['TYPE']=='numeric')
				{
					$_REQUEST['cust_end'][$field_name] = preg_replace('[^0-9.-]+','',$_REQUEST['cust_end'][$field_name]);
					$value = preg_replace('[^0-9.-]+','',$value);
				}

				if($_REQUEST['cust_begin'][$field_name]!='' && $_REQUEST['cust_end'][$field_name]!='')
				{
					if($fields[$id][1]['TYPE']=='numeric' && $_REQUEST['cust_begin'][$field_name]>$_REQUEST['cust_end'][$field_name])
					{
						$temp = $_REQUEST['cust_end'][$field_name];
						$_REQUEST['cust_end'][$field_name] = $value;
						$value = $temp;
					}
					$string .= ' and s.'.$column_name.' BETWEEN '.$value.' AND \''.$_REQUEST['cust_end'][$field_name].'\' ';
					if($fields[$id][1]['TYPE']=='date')
						$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].' between: </b></font>'.ProperDate($value).' &amp; '.ProperDate($_REQUEST['cust_end'][$field_name]).'<BR>';
					else
						$_openSIS['SearchTerms'] .= '<font color=gray><b>'.$fields[$id][1]['TITLE'].' between: </b></font>'.$value.' &amp; '.$_REQUEST['cust_end'][$field_name].'<BR>';
				}
			}
		}

		break;
	}
		return $string;
}
function GetGrade($grade,$column='TITLE')
{	global $_openSIS;
	if($column!='TITLE' && $column!='SHORT_NAME' && $column!='SORT_ORDER')
		$column = 'TITLE';

	if(!$_openSIS['GetGrade'])
	{
		$QI=DBQuery('SELECT ID,TITLE,SORT_ORDER,SHORT_NAME FROM school_gradelevels');
		$_openSIS['GetGrade'] = DBGet($QI,array(),array('ID'));
	}
	if($column=='TITLE')
		$extra = '<!-- '.$_openSIS['GetGrade'][$grade][1]['SORT_ORDER'].' -->';

	return $_openSIS['GetGrade'][$grade][1][$column];//$extra.
}
function AllowEdit($modname=false)
{	
    global $_openSIS;
    
	if(!$modname)
		$modname = $_REQUEST['modname'];

	if($modname=='students/Student.php' && $_REQUEST['category_id'])
		$modname = $modname.'&category_id='.$_REQUEST['category_id'];

	if(UserWs('PROFILE')=='admin')
	{
		if(!$_openSIS['AllowEdit'])
		{
			if(UserWs('PROFILE_ID')!='')
				$_openSIS['AllowEdit'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\''.UserWs('PROFILE_ID').'\' AND CAN_EDIT=\'Y\''),array(),array('MODNAME'));
			else
                        {
                        $profile_id_mod=DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='".UserWs('STAFF_ID')));
			$profile_id_mod=$profile_id_mod[1]['PROFILE_ID'];
                        if($profile_id_mod!='')
                        $_openSIS['AllowEdit'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\''.$profile_id_mod.'\' AND CAN_EDIT=\'Y\''),array(),array('MODNAME'));
                        }

		}
		if(!$_openSIS['AllowEdit'])
			$_openSIS['AllowEdit'] = array(true);

		if(count($_openSIS['AllowEdit'][$modname]))
			return true;
		else
			return false;
	}
	else
        {
                if(UserWs('PROFILE_ID')==3 || UserWs('PROFILE_ID')==4)
                {
                    if($modname=='attendance/StudentSummary.php')
                        return true;
                    elseif($modname=='schoolsetup/Calendar.php')
                        return true;        
                    elseif($modname=='attendance/DailySummary.php')
                        return true;
                    elseif($modname=='scheduling/ViewSchedule.php')
                        return true;
                   elseif($modname=='messaging/Group.php')
                        return true;
                    else
                        return $_openSIS['allow_edit'];
                }
                elseif(UserWs('PROFILE')=='teacher')
                {
                    if(!isset($_openSIS['allow_edit']))
                    {
                        $config_data = DBGet(DBQuery('SELECT CAN_USE FROM profile_exceptions WHERE MODNAME=\''.$modname.'\' AND PROFILE_ID='.UserWs('PROFILE_ID')));
                        $_openSIS['allow_edit'] = ($config_data[1]['CAN_USE']=='Y')?1:0;
                    }
                    
                    if($modname=='attendance/StudentSummary.php')
                        return true;
                    
                     elseif($modname=='scheduling/ViewSchedule.php')
                      return true;
                    elseif($modname=='attendance/DailySummary.php')
                        return true;
                    elseif($modname=='schoolsetup/Calendar.php')
                        return true;
                    elseif($modname=='scheduling/PrintSchedules.php')
                        return true; 
                    elseif($modname=='messaging/Group.php')
                        return true; 
                    else
                        return $_openSIS['allow_edit'];
                }
                else
		return $_openSIS['allow_edit'];
        }
}
function GetNameFromUserName($userName)
{	

    $q="Select * from login_authentication where username='$userName'";

       $userProfile=  DBGet(DBQuery($q));

       $userProfileId=$userProfile[1]['PROFILE_ID'];
       $UserId=$userProfile[1]['USER_ID'];
       if($userProfileId!=3 ||$userProfileId!=4)
       {
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from staff where profile_id=$userProfileId and staff_id=$UserId  ";
       }
       if($userProfileId==3)
       {
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from students where student_id=$UserId  ";
       }
       if($userProfileId==4)
       {
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from people where profile_id=$userProfileId and staff_id=$UserId  ";
       }
       $name=  DBGet(DBQuery($nameQuery));
       $name=$name[1]['NAME'];	
    return $name;
}
function create_token($token)
{
    $secret_key = "openSIS601K0lkata";
    return $jwt = JWT::encode($token, $secret_key);
}
function check_auth()
{
    $result = array();
    $secret_key = "openSIS601K0lkata";
    $header = apache_request_headers();
    $jwt = '';
    foreach ($header as $head_key=>$value) {
        $res[$head_key] = $value;
    }
    if(isset($res['Authorization']) &&  $res['Authorization']!='' )
        $jwt = $res['Authorization'];
//    $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9vcGVuc2lzLmNvbSIsInVzZXJfaWQiOiIxNTciLCJ1c2VyX3Byb2ZpbGUiOiJzdHVkZW50In0.6coKWyqu2UAr5ZO60s40XeFRuAUQOA8oloXmO5PhEos';
    if($jwt!='')
    {
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        $result['user_id']=$decoded->user_id;
        $result['user_profile']=$decoded->user_profile;
    }
//    print_r($result);exit;
    return $result;
}
function check_permission($profile_id)
{
    $tab_sql = 'SELECT MODNAME,CAN_USE,CAN_EDIT FROM  profile_exceptions WHERE CAN_USE=\'Y\' AND PROFILE_ID='.$profile_id;
    $tab_data = DBGet(DBQuery($tab_sql));
    $atten = $grade = $stu = $schedule = $school = array();
    $grd = $sch = $schl = 0;
    $grade['submenu']=array();
    $schedule['submenu']=array();
    $school['submenu']=array();
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    $scr_path = explode('/webservice/',$_SERVER['SCRIPT_NAME']);
    $file_path = $scr_path[0];

    $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
    if($file_path!='')
    $htpath=$htpath."/".$file_path;
    $htpath=$htpath."/webservice/assets/app_menu/";

//    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//        $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
//        $file_path = $scr_path[1];

//        $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/webservice/assets/app_menu/";
        $path ='../assets/app_menu/';
    foreach($tab_data as $tab)
    {
        $tab_nm = explode('/',$tab['MODNAME']);
        if($tab_nm[0]=='attendance' && in_array($tab_nm[1],array('TakeAttendance.php','StudentSummary.php','DailySummary.php')))
        {
            $atten['name']='Attendance';
            $atten['can_view']=$tab['CAN_USE'];
            $atten['can_edit']=$tab['CAN_EDIT'];
            $icoPath=$path."attendance.png";
            $atten['icon']=$htpath."attendance.png";
            $atten['submenu']=array();
        }
        if($tab_nm[0]=='grades')
        {
            $grade['name']='Gradebook';
            $icoPath=$path."grade.png";
            $grade['icon']=$htpath."grade.png";
            if(in_array($tab_nm[1],array('InputFinalGrades.php','Grades.php','Assignments.php','Configuration.php','StudentGrades.php','FinalGrades.php','GPARankList.php')))
            {
                if($tab_nm[1]=='InputFinalGrades.php')
                    $grade['submenu'][$grd]['name']='Post Final grade';
                elseif($tab_nm[1]=='Grades.php')
                    $grade['submenu'][$grd]['name']='Grades';
                elseif($tab_nm[1]=='Assignments.php')
                    $grade['submenu'][$grd]['name']='Assignments';
                elseif($tab_nm[1]=='Configuration.php')
                    $grade['submenu'][$grd]['name']='Gradebook Configuration';
                elseif($tab_nm[1]=='StudentGrades.php')
                    $grade['submenu'][$grd]['name']='Gradebook Grades';
                elseif($tab_nm[1]=='FinalGrades.php')
                    $grade['submenu'][$grd]['name']='Final Grades';
                elseif($tab_nm[1]=='GPARankList.php')
                    $grade['submenu'][$grd]['name']='GPA/Class Rank List';
                $grade['submenu'][$grd]['can_view']=$tab['CAN_USE'];
                $grade['submenu'][$grd]['can_edit']=$tab['CAN_EDIT'];
                $grd++;
            }
        }
        if($tab_nm[0]=='students' && $tab_nm[1]=='Student.php')
        {
            $stu['name']='Students';
            $stu['can_view']=$tab['CAN_USE'];
            $stu['can_edit']=$tab['CAN_EDIT'];
            $icoPath=$path."my_student.png";
            $stu['icon']=$htpath."my_student.png";
            $stu['submenu']=array();
        }
        if($tab_nm[0]=='scheduling')
        {
            $schedule['name']='Schedule';
            $icoPath=$path."schedule.png";
            if(file_exists($icoPath))
                $schedule['icon']=$htpath.$icoPath;
            else 
                $schedule['icon']="";
            if(in_array($tab_nm[1],array('ViewSchedule.php','Schedule.php','Requests.php')))
            {
                if($tab_nm[1]=='ViewSchedule.php')
                    $schedule['submenu'][$sch]['name']='My Schedule';
                elseif($tab_nm[1]=='Schedule.php')
                    $schedule['submenu'][$sch]['name']='Student Schedule';
                elseif($tab_nm[1]=='Requests.php')
                    $schedule['submenu'][$sch]['name']='Requests';
                $schedule['submenu'][$sch]['can_view']=$tab['CAN_USE'];
                $schedule['submenu'][$sch]['can_edit']=$tab['CAN_EDIT'];
                $sch++;
            }
        }
        if($tab_nm[0]=='schoolsetup')
        {
            $school['name']='School Information';
            $icoPath=$path."school_info.png";
            $school['icon']=$htpath."school_info.png";
            if(in_array($tab_nm[1],array('Schools.php','MarkingPeriods.php','Calendar.php','Courses.php','CourseCatalog.php')))
            {
                if($tab_nm[1]=='Schools.php')
                    $school['submenu'][$schl]['name']='School Information';
                elseif($tab_nm[1]=='MarkingPeriods.php')
                    $school['submenu'][$schl]['name']='Marking Period';
                elseif($tab_nm[1]=='Calendar.php')
                    $school['submenu'][$schl]['name']='Calendar';
                elseif($tab_nm[1]=='Courses.php')
                    $school['submenu'][$schl]['name']='Course Manager';
                elseif($tab_nm[1]=='CourseCatalog.php')
                    $school['submenu'][$schl]['name']='Course Catalog';
                $school['submenu'][$schl]['can_view']=$tab['CAN_USE'];
                $school['submenu'][$schl]['can_edit']=$tab['CAN_EDIT'];
                $schl++;
            }
        }
    }
    if(count($grade['submenu'])>0)
    {
        $grade['can_view']='Y';
        $grade['can_edit']='Y';
    }
    else 
    {
        $grade['can_view']='N';
        $grade['can_edit']='N';
    }
    if(count($schedule['submenu'])>0)
    {
        $schedule['can_view']='Y';
        $schedule['can_edit']='Y';
    }
    else 
    {
        $schedule['can_view']='N';
        $schedule['can_edit']='N';
    }
    if(count($school['submenu'])>0)
    {
        $school['can_view']='Y';
        $school['can_edit']='Y';
    }
    else 
    {
        $school['can_view']='N';
        $school['can_edit']='N';
    }
    if(count($atten)>0)
        $res[]=$atten;
    if(count($grade)>0)
        $res[]=$grade;
    if(count($stu)>0)
        $res[]=$stu;
    if(count($schedule)>0)
        $res[]=$schedule;
    if(count($school)>0)
        $res[]=$school;
    
    return $res;
}

function send_push_notification($device_type,$device_token,$from,$msg,$parameters)
{
    if($device_type=='iphone')
    {
//        echo $device_token;

//        $device_token='3a5a8e21bbb9eb14403c46d797419913c9bb1d01541bb2aca62caaf9eafe9f39';
        $passphrase='123456';
        // Create the payload body
        $body['aps'] = array(
                'alert' => array(
                    'title' => "OpenSIS",
                    'body' => $msg,
                    'from_loc' => $from,
                    'params' => $parameters,
                 ),
                'sound' => 'default'
        );

        // Encode the payload as JSON
        $payload = json_encode($body);

        //$apnsHost = 'gateway.sandbox.push.apple.com'; //development
        $apnsHost = 'gateway.push.apple.com'; //production
        $apnsPort = 2195;
        //$apnsCert = 'opensis_push.pem';//development
        $apnsCert = $_SERVER["DOCUMENT_ROOT"].'/webservice/function/tanay_hit.pem';//development
//        echo $apnsCert = $_SERVER["DOCUMENT_ROOT"].'/webservice/function/opensis_push.pem';

        $streamContext = stream_context_create();
        stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
        stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

        $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $streamContext);
        stream_set_blocking ($apns, 0); 
//        if (!$apns) {
//            //ERROR
//            echo "Failed to connect (stream_socket_client): $error $errorString";
//        }
         // Keep push alive (waiting for delivery) for 90 days
        $apple_expiry = time() + (90 * 24 * 60 * 60); 

        $apple_identifier = 1;
        $payload = json_encode($body);

        // Enhanced Notification
        $msg = pack("C", 1) . pack("N", $apple_identifier) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $device_token)) . pack("n", strlen($payload)) . $payload; 

        // SEND PUSH
        $result = fwrite($apns, $msg);
        
        // Workaround to check if there were any errors during the last seconds of sending.
        // Pause for half a second. 
        // Note I tested this with up to a 5 minute pause, and the error message was still available to be retrieved
        usleep(500000); 

        fclose($apns);
        
    }
    elseif($device_type=='android')
    {
//        /* Code for Android*/
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAApm5D8_U:APA91bEUIhSaRav1UyM5uXb-gEdgABDM9Qx09Fx3_uvPWDYcnxN5pBPj0dFsgiiDnYlSzGozey43UzVFVG-Yj2ppm-HvO9f-VsAempM7Z8vAqvRqFnutM_DbMYw-4GNpIFRaF06-T_yE'; //android server key
        $target = $device_token; 
        $data['title']="OpenSIS";
        $data['text']=$msg;
        $data['from_loc']=$from;
        foreach($parameters as $key => $val)
        {
            $data[$key]=$val;
        }
        $body = array(
                'data' =>$data,
                'to' => $target
        );
        //header with content_type api key
        $headers = array(
                'Content-Type:application/json',
          'Authorization:key='.$server_key
        );
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $header=array('Content-Type: application/json',
        "Authorization: key=".$server_key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($body));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"data\": {    \"title\": \"OpenSIS\",    \"text\": \"".$msg."\",    \"from_loc\": \"".$from."\",  \"params\": \"".json_encode($parameters)."\"  },    \"to\" : \"$target\"}");

        $result = curl_exec($ch);
        //print_r($result);
        curl_close($ch);
    }
}
?>