<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/function.php';

include 'function/ParamLib.php';
//include_once '../functions/MakeLetterGradeFnc.php';
//include_once '../functions/MakePercentGradeFnc.php';

header('Content-Type: application/json');

       
//$type = $_REQUEST['type']; 
$_SESSION['STAFF_ID'] = $teacher_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$cpv_id = $_REQUEST['cpv_id'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
//$asgnmnt_type_id = $_REQUEST['assignment_type_id'];
$asgnmnt_id = $_REQUEST['assignment_id'];
//$include_inactive = $_REQUEST['include_inactive'];

//        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
//        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
//        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
//        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
//        $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
//        $file_path = $scr_path[1];
//        
//        $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/studentphotos/";
//        $path ='../assets/studentphotos/';
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
$asgmnt_by_type = array();

$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$teacher_id.'\' AND PROGRAM=\'Gradebook\''));
if(count($config_RET))
{
    foreach($config_RET as $title=>$value)
    {
            $programconfig[$teacher_id][$value['TITLE']] = $value['VALUE'];
    }
    $max_allowed = $_openSIS['Preferences']['Gradebook']['ANOMALOUS_MAX'][1]['VALUE'];
}
else
    $programconfig[$teacher_id] = true;
 
if(!isset($_openSIS['allow_edit']))
    $_openSIS['allow_edit'] = true;

$cp_data = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\''.$cpv_id.'\''));
$_SESSION['UserCoursePeriod'] = $course_period_id = $cp_data[1]['COURSE_PERIOD_ID'];
$_SESSION['UserPeriod'] = $period_id = $cp_data[1]['PERIOD_ID'];

$course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
$course_id = $course_id[1]['COURSE_ID'];
$table = GetMP($mp_id,'TITLE',UserSyear(),UserSchool());
switch($table)
{
        case 'school_years':
                $sname = 'FY';
        break;

        case 'school_semesters':
                $sname = 'SEM';
        break;

        case 'school_quarters':
                $sname = 'QTR';
        break;

        case 'school_progress_periods':
                $sname = 'PRO';
        break;
}
$assignment_type_RET = DBGet(DBQuery('SELECT ASSIGNMENT_TYPE_ID,TITLE FROM gradebook_assignment_types WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
$assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.POINTS,ga.DUE_DATE,gt.TITLE AS TYPE_TITLE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignments ga,gradebook_assignment_types gt WHERE ((ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$teacher_id.'\') OR ga.COURSE_PERIOD_ID=\''.$course_period_id.'\') AND ga.MARKING_PERIOD_ID IN ('.GetAllMPWs($sname,$mp_id,UserSyear(),UserSchool()).') AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID ORDER BY ga.'.PreferencesWs('ASSIGNMENT_SORTING','Gradebook').' DESC'),array(),array('ASSIGNMENT_ID')); //$programconfig[$teacher_id]['ASSIGNMENT_SORTING']
//print_R($assignments_RET);exit;
// when changing course periods the assignment_id will be wrong except for '' (totals) and 'all'
if($_REQUEST['assignment_type_id'] && ($_REQUEST['assignment_type_id']!='0'))
{
    $assignment_by_type_RET = DBGet(DBQuery('SELECT ASSIGNMENT_ID,TITLE FROM gradebook_assignments WHERE MARKING_PERIOD_ID IN ('.GetAllMPWs($sname,$mp_id,UserSyear(),UserSchool()).') AND ASSIGNMENT_TYPE_ID=\''.$_REQUEST['assignment_type_id'].'\' ORDER BY '.PreferencesWs('ASSIGNMENT_SORTING','Gradebook').' DESC'));
    if(!isset($_REQUEST['assignment_id']))
            $_REQUEST['assignment_id'] = $assignment_by_type_RET[1]['ASSIGNMENT_ID'];
    foreach($assignment_by_type_RET as $a)
    {
        $asgmnt_by_type[] = $a;
    }
}
if($_REQUEST['assignment_id'] && $_REQUEST['assignment_id']!='all')
{
	foreach($assignments_RET as $id=>$assignment)
		if($_REQUEST['assignment_id']==$id)
		{
			$found = true;
			break;
		}
	if(!$found)
		unset($_REQUEST['assignment_id']);
}
####################
if($_REQUEST['search'])
{
    $search_students = DBGet(DBQuery('SELECT STUDENT_ID FROM students WHERE LAST_NAME LIKE \'%'.trim(strtolower($_REQUEST['search'])).'%\' OR FIRST_NAME LIKE \'%'.trim(strtolower($_REQUEST['search'])).'%\' OR STUDENT_ID LIKE \'%'.trim(strtolower($_REQUEST['search'])).'%\'')); 
    $stu = array();
    foreach($search_students as $student)
    {
        $stu[]=$student['STUDENT_ID'];
    }
}
if($_REQUEST['student_id'])
{
        $_SESSION['student_id'] = $_REQUEST['student_id'];
	$_REQUEST['stuid'] = $_REQUEST['student_id'];
//	$LO_columns = array('TYPE_TITLE'=>'Category','TITLE'=>'Assignment','POINTS'=>'Points','LETTER_GRADE'=>'Grade','COMMENT'=>'Comment');
//	$item = 'Assignment';
//	$items = 'Assignments';
	$current_RET[$_REQUEST['student_id']] = DBGet(DBQuery('SELECT g.ASSIGNMENT_ID FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\''.$mp_id.'\' AND g.STUDENT_ID=\''.$_REQUEST['student_id'].'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\''.($_REQUEST['assignment_id']=='all'?'':' AND g.ASSIGNMENT_ID=\''.$_REQUEST['assignment_id'].'\'')));
	if(count($assignments_RET))
	{
		foreach($assignments_RET as $id=>$assignment)
			$total_points[$id] = $assignment[1]['POINTS'];
	}
	$count_assignments = count($assignments_RET);
	$extra['SELECT'] = ',ga.ASSIGNMENT_ID,gt.TITLE AS TYPE_TITLE,ga.TITLE,ga.POINTS AS TOTAL_POINTS,\'\' AS LETTER_GRADE,CASE WHEN (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE';
	$extra['SELECT'] .= ',(SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) AS POINTS';
	$extra['SELECT'] .= ',(SELECT COMMENT FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) AS COMMENT';
	$extra['FROM'] = ',gradebook_assignments ga,gradebook_assignment_types gt';
	$extra['WHERE'] = 'AND (ga.due_date>=ssm.start_date OR ga.due_date IS NULL) AND ((ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.UserWs('STAFF_ID').'\') OR ga.COURSE_PERIOD_ID=\''.$course_period_id.'\') AND ga.MARKING_PERIOD_ID=\''.$mp_id.'\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID'.($_REQUEST['assignment_id']=='all'?'':' AND ga.ASSIGNMENT_ID=\''.$_REQUEST['assignment_id'].'\' ');
        $extra['ORDER_BY'] = PreferencesWs('ASSIGNMENT_SORTING','Gradebook')." DESC";
	$extra['functions'] = array('POINTS'=>'_makeExtraStuCols','LETTER_GRADE'=>'_makeExtraStuCols','COMMENT'=>'_makeExtraStuCols');
}
else
{
	$LO_columns = array('FULL_NAME'=>'Student');
	if($_REQUEST['assignment_id']!='all')
		$LO_columns += array('STUDENT_ID'=>'Student ID');
	if($_REQUEST['include_inactive']=='Y')
		$LO_columns += array('ACTIVE'=>'School Status','ACTIVE_SCHEDULE'=>'Course Status');
	$item = 'Student';
	$items = 'Students';
	if($_REQUEST['assignment_id']=='all')
	{
            
		$current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.ASSIGNMENT_ID,g.POINTS FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\''.$mp_id.'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\''));
		$count_extra = array('SELECT_ONLY'=>'ssm.STUDENT_ID');
		$count_students = GetStuListWs($count_extra,$teacher_id);
		$count_students = count($count_students);
                
                $extra['SELECT'] =',ssm.START_DATE';
                $extra['WHERE']=' AND \''.DBDate('mysql').'\'>=ssm.START_DATE';
                if($_REQUEST['search'])
                {
                    if(count($stu)>0)
                        $extra['WHERE'] .=' AND ssm.STUDENT_ID IN ('.implode(',',$stu).')';
                }
		$extra['functions'] = array();
		if(count($assignments_RET))
		{
			foreach($assignments_RET as $id=>$assignment)
			{
				$assignment = $assignment[1];
				$extra['SELECT'] .= ',\''.$id.'\' AS G'.$id.',\''.$assignment[DUE].'\' AS D'.$id.',\''.$assignment[DUE_DATE].'\' AS DUE_'.$id.'';
                                
				$extra['functions'] += array('G'.$id=>'_makeExtraCols');
				$LO_columns += array('G'.$id=>$assignment['TYPE_TITLE'].'<BR>'.$assignment['TITLE']);
				$total_points[$id] = $assignment['POINTS'];
			}
		}
	}
	elseif($_REQUEST['assignment_id'])
	{
            
		$id = $_REQUEST['assignment_id'];
		$extra['SELECT'] .= ',\''.$id.'\' AS POINTS,\''.$id.'\' AS LETTER_GRADE,\''.$id.'\' AS COMMENT,\''.$assignments_RET[$id][1]['DUE'].'\' AS DUE';
                $extra['WHERE'] .=' AND (((SELECT DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\''.$id.'\')>=ssm.START_DATE) OR ((SELECT DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\''.$id.'\') IS NULL))';
		if($_REQUEST['search'])
                {
                    if(count($stu)>0)
                        $extra['WHERE'] .=' AND ssm.STUDENT_ID IN ('.implode(',',$stu).')';
                }
		$extra['functions'] = array('POINTS'=>'_makeExtraAssnCols','LETTER_GRADE'=>'_makeExtraAssnCols','COMMENT'=>'_makeExtraAssnCols');
		$LO_columns += array('POINTS'=>'Points','LETTER_GRADE'=>'Grade','COMMENT'=>'Comment');
		$total_points = DBGet(DBQuery('SELECT POINTS FROM gradebook_assignments WHERE ASSIGNMENT_ID=\''.$id.'\''));
		$total_points[$id] = $total_points[1]['POINTS'];
                
		$current_RET = DBGet(DBQuery('SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID FROM gradebook_grades WHERE ASSIGNMENT_ID=\''.$id.'\' AND COURSE_PERIOD_ID=\''.$course_period_id.'\''),array(),array('STUDENT_ID','ASSIGNMENT_ID'));
	}
	else
	{
                    $_SESSION['ROUNDING']= $programconfig[UserWs('STAFF_ID')]['ROUNDING'];
			$extra['SELECT'] .= ',\'\' AS POINTS,\'\' AS LETTER_GRADE,\'\' AS COMMENT';
                        $extra['WHERE']=' AND \''.DBDate('mysql').'\'>=ssm.START_DATE';
                    if($_REQUEST['search'])
                    {
                        if(count($stu)>0)
                            $extra['WHERE'] .=' AND ssm.STUDENT_ID IN ('.implode(',',$stu).')';
                    }            
		if(count($assignments_RET))
		{
			$extra['functions'] = array('POINTS'=>'_makeExtraAssnCols','LETTER_GRADE'=>'_makeExtraAssnCols');
			// this will get the grades for all students ever enrolled in the class
			// the "group by start_date" and "distinct on" are needed in case a student is enrolled more than once (re-enrolled)
			if($programconfig[UserWs('STAFF_ID')]['WEIGHT']=='Y')
                        {
                                $sql = 'SELECT DISTINCT s.STUDENT_ID, gt.ASSIGNMENT_TYPE_ID, 
                                    sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,
                                        sum('.db_case(array('gg.POINTS','\'-1\' OR gg.POINTS IS NULL OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\''.UserSyear().'\' AND ssm.SCHOOL_ID='.UserSchool().' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1
)  ) ',"'0'",'ga.POINTS')).') AS PARTIAL_TOTAL, gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\''.$course_period_id.'\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.UserWs('STAFF_ID').'\') AND ga.MARKING_PERIOD_ID=\''.(GetCpDet($course_period_id,'MARKING_PERIOD_ID')!=''?UserMP():GetMPId('FY')).'\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                                     
                                        ,gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\''.$course_id.'\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL)';
                                if(isset($_REQUEST['assignment_type_id']) && $_REQUEST['assignment_type_id']!='')
                                        $sql .= ' AND ga.ASSIGNMENT_TYPE_ID = \''.$_REQUEST['assignment_type_id'].'\'';
                                $sql .= ' GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT';
				$points_RET = DBGet(DBQuery($sql),array(),array('STUDENT_ID'));
                        }
			else
                        {
                            $sql = 'SELECT DISTINCT s.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID, sum('.db_case(array('gg.POINTS',"'-1'","'0'",'gg.POINTS')).') AS PARTIAL_POINTS,
                                sum('.db_case(array('gg.POINTS','\'-1\' OR gg.POINTS IS NULL OR (ga.due_date < (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\''.UserSyear().'\' AND ssm.SCHOOL_ID='.UserSchool().' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1) ) ',"'0'",'ga.POINTS')).') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT 
                                    FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\''.$course_period_id.'\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.UserWs('STAFF_ID').'\') AND ga.MARKING_PERIOD_ID=\''.(GetCpDet($course_period_id,'MARKING_PERIOD_ID')!=''?UserMP():GetMPId('FY')).'\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                                    WHERE ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL)';
                            if(isset($_REQUEST['assignment_type_id']) && $_REQUEST['assignment_type_id']!='')
                                $sql .= ' AND ga.ASSIGNMENT_TYPE_ID = \''.$_REQUEST['assignment_type_id'].'\'';
                            $sql .= ' GROUP BY s.STUDENT_ID,ss.START_DATE';
                            $points_RET = DBGet(DBQuery($sql),array(),array('STUDENT_ID'));
                        }
                          
			foreach($assignments_RET as $id=>$assignment)
				$total_points[$id] = $assignment[1]['POINTS'];
		}
	}
}
if($_REQUEST['values'])
{
    $values=$_REQUEST["values"];
    $assignment_values = json_decode($values,TRUE);
    

    foreach($assignment_values as $item)
	{
	foreach($item as $student_id=>$assignments)
	{
		foreach($assignments as $assignment_id=>$columns)
		{
                    $current_RET = DBGet(DBQuery('SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID FROM gradebook_grades WHERE ASSIGNMENT_ID=\''.$assignment_id.'\' AND COURSE_PERIOD_ID=\''.$course_period_id.'\''),array(),array('STUDENT_ID','ASSIGNMENT_ID'));
			if($columns['POINTS'])
			{
				if($columns['POINTS']=='*')
					$columns['POINTS'] = '-1';
				else
				{
                            		$columns['POINTS']= $columns['POINTS'];
					if(substr($columns['POINTS'],-1)=='%')
						$columns['POINTS'] = substr($columns['POINTS'],0,-1) * $total_points[$assignment_id] / 100;
					elseif(!is_numeric($columns['POINTS']))
						$columns['POINTS'] = _makePercentGrade($columns['POINTS'],$course_period_id) * $total_points[$assignment_id] / 100 ;
					if($columns['POINTS']<0)
						$columns['POINTS'] = '0';
					elseif($columns['POINTS']>9999.99)
						$columns['POINTS'] = '9999.99';
				}
			}
			$sql = '';
			if($current_RET[$student_id][$assignment_id])
			{
				$sql = "UPDATE gradebook_grades SET ";
				foreach($columns as $column=>$value)
				{
                                    if($column=='COMMENT')
                                        $value=str_replace("'","\'",$value);
					if($value!='-1')
					{
						$value= paramlib_validation($column,$value);
					}
					
                                         if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux'))
                                     {
                                        $value =  mysql_real_escape_string($value);
        			     }
                                    $sql .= $column."='".$value." ',";
				}
				
				$sql = substr($sql,0,-1) . " WHERE STUDENT_ID='$student_id' AND ASSIGNMENT_ID='$assignment_id' AND COURSE_PERIOD_ID='$course_period_id'";
			}
			elseif($columns['POINTS']!='' || $columns['COMMENT'])
                        {
                                $columns['COMMENT']=str_replace("'","\'",$columns['COMMENT']);
				$sql = 'INSERT INTO gradebook_grades (STUDENT_ID,PERIOD_ID,COURSE_PERIOD_ID,ASSIGNMENT_ID,POINTS,COMMENT) values(\''.$student_id.'\',\''.UserPeriod().'\',\''.$course_period_id.'\',\''.$assignment_id.'\',\''.$columns['POINTS'].'\',\''.$columns['COMMENT'].'\')';
                        }
                        
			if($sql){
				DBQuery($sql);
                        
//                                DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
                        }
                        }
	}
}
	if($_REQUEST['student_id'])
		$current_RET[$_REQUEST['student_id']] = DBGet(DBQuery('SELECT g.ASSIGNMENT_ID FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\''.$mp_id.'\' AND g.STUDENT_ID=\''.$_REQUEST['student_id'].'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\''.($_REQUEST['assignment_id']=='all'?'':' AND g.ASSIGNMENT_ID=\''.$_REQUEST[assignment_id].'\'')),array(),array('ASSIGNMENT_ID'));
	elseif($_REQUEST['assignment_id']=='all')
		$current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.ASSIGNMENT_ID,g.POINTS FROM gradebook_grades g,gradebook_assignments a WHERE a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND a.MARKING_PERIOD_ID=\''.$mp_id.'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\''),array(),array('STUDENT_ID','ASSIGNMENT_ID'));
	else
		$current_RET = DBGet(DBQuery('SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID FROM gradebook_grades WHERE ASSIGNMENT_ID=\''.$_REQUEST[assignment_id].'\' AND COURSE_PERIOD_ID=\''.$course_period_id.'\''),array(),array('STUDENT_ID','ASSIGNMENT_ID'));

	unset($_REQUEST['values']);
	unset($_SESSION['_REQUEST_vars']['values']);
}
//print_r($extra);
$stu_RET = GetStuListWs($extra,$teacher_id);
//print_R($stu_RET);
$i=1;
foreach($stu_RET as $student)
{
    $stuPicPath=$path.$student['STUDENT_ID'].".JPG";
    if(file_exists($stuPicPath))
        $stu_RET[$i]['PHOTO']=$htpath.$student['STUDENT_ID'].".JPG";
    else 
        $stu_RET[$i]['PHOTO']="";
    $i++;
}
$student_data = array();
foreach($stu_RET as $stu)
{
    $student_data[]=$stu;
}
if(isset($_REQUEST['assignment_type_id']) && $_REQUEST['assignment_type_id']!='' && $_REQUEST['assignment_id']=='')
{
    $student_data = array();
}
$assignment_types[]=array('ASSIGNMENT_TYPE_ID'=>'0','TITLE'=>'Totals');
foreach($assignment_type_RET as $at)
{
    $assignment_types[]=$at;
}
$data = array('student_grades'=>$student_data,'assignment_types'=>$assignment_types,'selected_assignment_type_id'=>$_REQUEST['assignment_type_id'],'selected_assignment_id'=>$_REQUEST['assignment_id']);
//if(count($asgmnt_by_type)>0)
//{
    $abt = array('assignment_by_type'=>$asgmnt_by_type);
    $data = array_merge($data,$abt);
//}
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


function _makeExtraAssnCols($assignment_id,$column)
{	global $THIS_RET,$total_points,$current_RET,$points_RET,$tabindex,$max_allowed;
//print_r($THIS_RET);
            $rounding=DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
            if(count($rounding))
                $_SESSION['ROUNDING']=$rounding[1]['VALUE'];
        switch($column)
	{
		case 'POINTS':
			$tabindex++;
                        
			if($assignment_id=='' && !$_REQUEST['student_id'])
			{
//                            print_r();
//                            print_r($points_RET[$THIS_RET['STUDENT_ID']]);
				if(count($points_RET[$THIS_RET['STUDENT_ID']]))
				{
					$total = $total_points = 0;
					foreach($points_RET[$THIS_RET['STUDENT_ID']] as $partial_points)
						if($partial_points['PARTIAL_TOTAL']!=0)
						{
							$total += $partial_points['PARTIAL_POINTS'];
							$total_points += $partial_points['PARTIAL_TOTAL'];
						}
				}
				else
					$total = $total_points = 0;

				return $total.'/'.$total_points;
			}
			else
			{
                            if(count($current_RET)>0)
                            {
				if($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS']=='-1')
					$points = '*';
				elseif(strpos($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'],'.'))
					$points = rtrim(rtrim($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'],'0'),'.');
				else
					$points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];
                            }
                            else
                                    $points = '';
//				return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD>'.TextInput($points,'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'</TD><TD>&nbsp;/&nbsp;</TD><TD>'.$total_points[$assignment_id].'</TD></TR></TABLE>';
                                return $points.'/'.$total_points[$assignment_id];
                            
                               
			}
		break;
		case 'LETTER_GRADE':
			if($assignment_id=='' && !$_REQUEST['student_id'])
			{
				if(count($points_RET[$THIS_RET['STUDENT_ID']]))
				{

                                    
                                    
                                    
                                            $total = $total_percent = 0;
                                            foreach($points_RET[$THIS_RET['STUDENT_ID']] as $partial_points)
                                                    if($partial_points['PARTIAL_TOTAL']!=0)
                                                    {
                                                            $total += $partial_points['PARTIAL_POINTS'];
                                                            $total_percent += $partial_points['PARTIAL_TOTAL'];
                                                    }
                                            if($total_percent!=0)
						$total /= $total_percent;
                                    
                                    
				}
				else
					$total = 0;
                               
                                $ppercent= _makeLetterGrade($total,"",UserWs('STAFF_ID'),"%");
                                if($points_RET[$THIS_RET['STUDENT_ID']][1]['PARTIAL_POINTS']!='')
                                    return $ppercent.'%'._makeLetterGrade($total,"",UserWs('STAFF_ID'));
//                                    return ($total>$max_allowed?'<FONT color=red>':'').$ppercent.($total>$max_allowed?'</FONT>':'').'&nbsp;<B>'._makeLetterGrade($total,"",UserWs('STAFF_ID')).'</B>%';
                                else
					return 'Not Graded';
			}
			else
			{
				$points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];                                
                                 if($_SESSION['ROUNDING']=='UP')
                                        $points_m = ceil($points);
                                elseif($_SESSION['ROUNDING']=='DOWN')
                                        $points_m = floor($points);
                                elseif($_SESSION['ROUNDING']=='NORMAL')
                                        $points_m = round($points);
                                else 
                                        $points_m=$points;
                                
                                #return $points_m; 11.00
                                $make_grade_points=$points_m/100;
                                $tot_point = $total_points[$assignment_id];
                                #return $max_allowed; 1
                                if($total_points[$assignment_id]!=0)
                                {
					if($points!='-1')
                                        {
                                            if($points!='')
                                            {
                                                $rounding=DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
                                                $points_r=($points_m/$tot_point)*100;
                                                if($rounding[1]['ROUNDING']=='UP')
                                                        $points_r = ceil($points_r);
                                                elseif($rounding[1]['ROUNDING']=='DOWN')
                                                        $points_r = floor($points_r);
                                                elseif($rounding[1]['ROUNDING']=='NORMAL')
                                                        $points_r = round($points_r);
                                                else 
                                                        $points_r=round($points_r,2);
                                                return $points_r.'%'._makeLetterGrade(($points_m/$tot_point));
//                                                 return ($THIS_RET['DUE']||$points!=''?($points>$total_points[$assignment_id]*$max_allowed?'<FONT color=red>':''):'<FONT color=gray>').($points_r).'%'.($THIS_RET['DUE']||$points!=''?($points>$total_points[$assignment_id]*$max_allowed?'</FONT>':''):'').'<B>'. _makeLetterGrade(($points_m/$tot_point)).'</B>'.($THIS_RET['DUE']||$points!=''?'':'</FONT>');
                                            }
                                                 
                                            else
                                                return 'Not Graded';
                                        }
					else
						return 'N/A N/A';
                                }
				else
					return 'E/C';
			}
		break;
		case 'COMMENT':
//			return TextInput($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['COMMENT'],'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][COMMENT]','',' maxlength=100 tabindex='.(500+$tabindex));
                        $comment = ($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['COMMENT']!='')?$current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['COMMENT']:'';
                        return $comment;
		break;
	}
}
function _makeExtraStuCols($value,$column)
{	global $THIS_RET,$assignment_count,$count_assignments,$max_allowed;

	switch($column)
	{
		case 'POINTS':
			$assignment_count++;
			$tabindex = $assignment_count;
			if($value=='-1')
				$value = '*';
			elseif(strpos($value,'.'))
				$value = rtrim(rtrim($value,'0'),'.');
                        return $value.'/'.$THIS_RET['TOTAL_POINTS'];
//			return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD>'.TextInput($value,'values['.$THIS_RET['STUDENT_ID'].']['.$THIS_RET['ASSIGNMENT_ID'].'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'</TD><TD>&nbsp;/&nbsp;</TD><TD>'.$THIS_RET['TOTAL_POINTS'].'</TD></TR></TABLE>';
		break;
		case 'LETTER_GRADE':
                    
			if($THIS_RET['TOTAL_POINTS']!=0)
                        {
				if($THIS_RET['POINTS']!='-1')
                                {
                                    if($THIS_RET['POINTS']!='')
                                        return _makeLetterGrade(round($THIS_RET['POINTS']/$THIS_RET['TOTAL_POINTS'],2),"",UserWs('STAFF_ID'),"%").'%'. _makeLetterGrade($THIS_RET['POINTS']/$THIS_RET['TOTAL_POINTS']);
//					return ($THIS_RET['DUE']||$THIS_RET['POINTS']!=''?($THIS_RET['POINTS']>$THIS_RET['TOTAL_POINTS']*$max_allowed?'<FONT color=red>':''):'<FONT color=gray>')._makeLetterGrade(round($THIS_RET['POINTS']/$THIS_RET['TOTAL_POINTS'],2),"",UserWs('STAFF_ID'),"%").'%'.($THIS_RET['DUE']||$THIS_RET['POINTS']!=''?($THIS_RET['POINTS']>$THIS_RET['TOTAL_POINTS']*$max_allowed?'</FONT>':''):'').'<B>'. _makeLetterGrade($THIS_RET['POINTS']/$THIS_RET['TOTAL_POINTS']).'</B>'.($THIS_RET['DUE']||$THIS_RET['POINTS']!=''?'':'</FONT>');
                                    else
                                        return 'Not Graded';
                                }
				else
					return 'N/A N/A';
                        }
			else
				return 'E/C';
                        
		break;
		case 'COMMENT':
			$tabindex += $count_assignments;
			return $value;
		break;
	}
}
function _makeExtraCols($assignment_id,$column)
{	global $THIS_RET,$total_points,$current_RET,$old_student_id,$student_count,$tabindex,$count_students,$max_allowed;

        $rounding=DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
        if(count($rounding))
        $_SESSION['ROUNDING']=$rounding[1]['VALUE'];
        if(strtotime($THIS_RET['START_DATE'],0)==strtotime($THIS_RET['DUE_'.$assignment_id],0))
        $days_left=1;
        else
        $days_left= floor((strtotime($THIS_RET['DUE_'.$assignment_id],0)-strtotime($THIS_RET['START_DATE'],0))/86400);
        if($days_left>=1)
        {
        if($THIS_RET['STUDENT_ID']!=$old_student_id)
	{
		$student_count++;
		$tabindex=$student_count;
		$old_student_id = $THIS_RET['STUDENT_ID'];
	}
	else
		$tabindex += $count_students;
	if($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS']=='-1')
		$points = '*';
	elseif(strpos($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'],'.'))
		$points = rtrim(rtrim($current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'],'0'),'.');
	else
		$points = $current_RET[$THIS_RET['STUDENT_ID']][$assignment_id][1]['POINTS'];

            if($_SESSION['ROUNDING']=='UP')
                    $points_m = ceil($points);
            elseif($_SESSION['ROUNDING']=='DOWN')
                    $points_m = floor($points);
            elseif($_SESSION['ROUNDING']=='NORMAL')
                    $points_m = round($points);
            else 
                    $points_m=$points;
            $make_letter_points=$points_m/100;
            
            $tot_point = $total_points[$assignment_id];
	if($total_points[$assignment_id]!=0)
        {
		if($points!='*')
                {
                    if($points!='')
                    {
                        $rounding=DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
                        $points_r=($points_m/$tot_point)*100;
                        if($rounding[1]['ROUNDING']=='UP')
                                $points_r = ceil($points_r);
                        elseif($rounding[1]['ROUNDING']=='DOWN')
                                $points_r = floor($points_r);
                        elseif($rounding[1]['ROUNDING']=='NORMAL')
                                $points_r = round($points_r);
                        else 
                                $points_r=round($points_r,2);
                       return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>'.TextInput($points,'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'<HR>'.$total_points[$assignment_id].'</TD><TD>'.($THIS_RET['D'.$assignment_id]||$points!=''?($points>$total_points[$assignment_id]*$max_allowed?'<FONT color=red>':''):'<FONT color=gray>').($points_r).'%'.($THIS_RET['D'.$assignment_id]||$points!=''?($points>$total_points[$assignment_id]*$max_allowed?'</FONT>':''):'').'<BR><B>'. _makeLetterGrade(($points_m/$tot_point)).'</B>'.($THIS_RET['D'.$assignment_id]||$points!=''?'':'</FONT>').'</TD></TR></TABLE>'; 
                    }
                       
                    else
                        return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>'.TextInput($points,'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'<HR>'.$total_points[$assignment_id].'</TD><TD>'.($THIS_RET['D'.$assignment_id]||$points!=''?($points>$total_points[$assignment_id]*$max_allowed?'<FONT color=red>':''):'<FONT color=gray>').'Not Graded</TD></TR></TABLE>';
                }
		else
			return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>'.TextInput($points,'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'<HR>'.$total_points[$assignment_id].'</TD><TD>N/A<BR>N/A</TD></TR></TABLE>';
        }
	else
		return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR align=center><TD>'.TextInput($points,'values['.$THIS_RET['STUDENT_ID'].']['.$assignment_id.'][POINTS]','',' size=2 maxlength=7 tabindex='.$tabindex).'<HR>'.$total_points[$assignment_id].'</TD><TD>E/C</TD></TR></TABLE>';
        }
        return 'N/A';
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
function _makePercentGrade($grade_id,$course_period_id=0,$staff_id=0)
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

	if(!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
		$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND GRADE_SCALE_ID=\''.$grade_scale_id.'\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));
	

	foreach($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade)
	{
		$prev = $crnt;
		$crnt = ($does_breakoff=='Y' ? $programconfig[$staff_id][$course_period_id.'-'.$grade['ID']] : $grade['BREAK_OFF']);
		if(is_numeric($grade_id) ? $grade_id==$grade['ID'] : strtoupper($grade_id)==strtoupper($grade['TITLE']))
			return ($crnt + ($crnt>$prev ? 100 : $prev)) / 2;
	}
	return 0;
}

echo json_encode($data);
?>
