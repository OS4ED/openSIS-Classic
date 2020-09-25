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
DrawBC(""._attendance." > ".ProgramTitle());
if($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start'])
	$start_date = $_REQUEST['day_start'].'-'.$_REQUEST['month_start'].'-'.substr($_REQUEST['year_start'],2,4);
else
	$start_date = '01-'.strtoupper(date('M-y'));

if($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end'])
	$end_date = $_REQUEST['day_end'].'-'.$_REQUEST['month_end'].'-'.substr($_REQUEST['year_end'],2,4);
else
	$end_date = DBDate();

DrawBC(""._attendance." > ".ProgramTitle());

if($_REQUEST['attendance'] && ($_POST['attendance'] || $_REQUEST['ajax']) && AllowEdit())
{
	foreach($_REQUEST['attendance'] as $student_id=>$values)
	{
		foreach($values as $school_date=>$columns)
		{
			$sql = 'UPDATE attendance_period SET ADMIN=\'Y\',';

			foreach($columns as $column=>$value)
				$sql .= $column.'=\''.str_replace("\'","''",$value).'\',';

			$sql = substr($sql,0,-1) . ' WHERE SCHOOL_DATE=\''.$school_date.'\' AND PERIOD_ID=\''.$_REQUEST['period_id'].'\' AND STUDENT_ID=\''.$student_id.'\'';
			DBQuery($sql);
			UpdateAttendanceDaily($student_id,$school_date);
		}
	}
	$current_RET = DBGet(DBQuery('SELECT ATTENDANCE_TEACHER_CODE,ATTENDANCE_CODE,ATTENDANCE_REASON,STUDENT_ID,ADMIN,COURSE_PERIOD_ID FROM attendance_period WHERE SCHOOL_DATE=\''.$date.'\''),array(),array('STUDENT_ID','COURSE_PERIOD_ID'));
	unset($_REQUEST['attendance']);
}

if($_REQUEST['search_modfunc'] || $_REQUEST['student_id'] || UserStudentID() || User('PROFILE')=='parent' || User('PROFILE')=='student')
{
	$PHP_tmp_SELF = PreparePHP_SELF();
	$extraM .= "";
	$period_select = "<SELECT name=period_id onchange='this.form.submit();'><OPTION value=\"\">Daily</OPTION>";
	if(!UserStudentID() && !$_REQUEST['student_id'])
	{
		$periods_RET = DBGet(DBQuery('SELECT PERIOD_ID,TITLE FROM school_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
		if(count($periods_RET)>1)
		{
			foreach($periods_RET as $period)
				$period_select .= "<OPTION value=".$period['PERIOD_ID'].(($_REQUEST['period_id']==$period['PERIOD_ID'])?' SELECTED':'').">".$period['TITLE'].'</OPTION>';
		}
		elseif(count($periods_RET)==1)
		{
			foreach($periods_RET as $period)
				$period_select .= "<OPTION value=".$period['PERIOD_ID'].(($_REQUEST['period_id']==$period['PERIOD_ID'] || !isset($_REQUEST['period_id']))?' SELECTED':'').">".$period['TITLE'].'</OPTION>';

			if(!isset($_REQUEST['period_id']))
				$_REQUEST['period_id'] = $period['PERIOD_ID'];
		}
	}
	else
		$period_select .= '<OPTION value="PERIOD"'.($_REQUEST['period_id']?' SELECTED':'').'>By Period</OPTION>';
	$period_select .= '</SELECT>';
	echo "<FORM action=$PHP_tmp_SELF method=POST>";
     
	echo "<div style='width:795px; overflow:scroll; overflow-y:hidden;'>";
	

	DrawHeaderHome('<table><tr><td>'.PrepareDate($start_date,'_start').'</td><td> - </td><td>'.PrepareDate($end_date,'_end').'</td><td> : </td><td>'.$period_select.' : <INPUT type=submit class="btn btn-primary" value='._go.'></td></tr></table>');
	
}

$cal_RET = DBGet(DBQuery('SELECT DISTINCT SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(SCHOOL_DATE,\'%y%b%d\')) AS SHORT_DATE FROM attendance_calendar WHERE SCHOOL_ID=\''.UserSchool().'\' AND SCHOOL_DATE BETWEEN \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\' ORDER BY SCHOOL_DATE'));

if(UserStudentID() || $_REQUEST['student_id'] || User('PROFILE')=='parent')
{
	// JUST TO SET USERSTUDENTID()
	Search('student_id');
	if($_REQUEST['period_id'])
	{
		$sql = 'SELECT
				cp.TITLE as COURSE_PERIOD,sp.TITLE as PERIOD,cpv.PERIOD_ID
			FROM
				schedule s,courses c,course_periods cp,course_period_var cpv,school_periods sp
			WHERE
				s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
				AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID = sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\'
				AND s.SYEAR = c.SYEAR AND cp.MARKING_PERIOD_ID IN ('.GetAllMP('QTR',UserMP()).')
				AND s.STUDENT_ID=\''.UserStudentID().'\' AND s.SYEAR=\''.UserSyear().'\'
				AND (\''.DBDate().'\' BETWEEN s.START_DATE AND s.END_DATE OR s.END_DATE IS NULL)
			ORDER BY sp.SORT_ORDER
			';
		$schedule_RET = DBGet(DBQuery($sql));

		$sql = 'SELECT ap.SCHOOL_DATE,ap.PERIOD_ID,ac.SHORT_NAME,ac.STATE_CODE,ac.DEFAULT_CODE FROM attendance_period ap,attendance_codes ac WHERE ap.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND ap.ATTENDANCE_CODE=ac.ID AND ap.STUDENT_ID=\''.UserStudentID().'\'';
		$attendance_RET = DBGet(DBQuery($sql),array(),array('SCHOOL_DATE','PERIOD_ID'));
	}
	else
	{
		$schedule_RET[1] = array('COURSE_PERIOD'=>'Daily Attendance','PERIOD_ID'=>'0');
		$attendance_RET = DBGet(DBQuery('SELECT ad.SCHOOL_DATE,\'0\' AS PERIOD_ID,ad.STATE_VALUE AS STATE_CODE,'.db_case(array('ad.STATE_VALUE',"'0.0'","'A'","'1.0'","'P'","'H'")).' AS SHORT_NAME FROM attendance_day ad WHERE ad.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND ad.STUDENT_ID=\''.UserStudentID().'\''),array(),array('SCHOOL_DATE','PERIOD_ID'));
	}
	
	$i = 0;
	if(count($schedule_RET))
	{
		foreach($schedule_RET as $course)
		{
			$i++;
			$student_RET[$i]['TITLE'] = $course['COURSE_PERIOD'];
			foreach($cal_RET as $value)
				$student_RET[$i][$value['SHORT_DATE']] = _makePeriodColor($attendance_RET[$value['SCHOOL_DATE']][$course['PERIOD_ID']][1]['SHORT_NAME'],$attendance_RET[$value['SCHOOL_DATE']][$course['PERIOD_ID']][1]['STATE_CODE'],$attendance_RET[$value['SCHOOL_DATE']][$course['PERIOD_ID']][1]['DEFAULT_CODE']);
		}
	}

	$columns = array('TITLE'=>'Course');
	if(count($cal_RET))
	{
		foreach($cal_RET as $value)
			$columns[$value['SHORT_DATE']] = ShortDate($value['SCHOOL_DATE']);
	}
	
	ListOutput($student_RET,$columns,_course,_courses);
echo '</div>';
}
else
{
	if(!$_REQUEST['period_id'])
	{
			$sql = 'SELECT ad.STATE_VALUE,ad.STUDENT_ID,SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ad.SCHOOL_DATE,\'%y%b%d\')) AS SHORT_DATE FROM attendance_day ad,student_enrollment ssm WHERE ad.STUDENT_ID=ssm.STUDENT_ID AND ((\''.DBDate().'\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL) AND \''.DBDate().'\'>=ssm.START_DATE) AND ssm.SCHOOL_ID=\''.UserSchool().'\' AND SCHOOL_DATE BETWEEN  \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\'';
		$RET = DBGet(DBQuery($sql),array(),array('STUDENT_ID','SHORT_DATE'));
	}
	else
	{
		$sql = 'SELECT ap.ATTENDANCE_CODE,ap.STUDENT_ID,ap.SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ap.SCHOOL_DATE,\'%y%b%d\')) AS SHORT_DATE FROM attendance_period ap,student_enrollment ssm,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ap.STUDENT_ID=ssm.STUDENT_ID AND ap.SCHOOL_DATE BETWEEN  \''.date('Y-m-d',strtotime($start_date)).'\' AND \''.date('Y-m-d',strtotime($end_date)).'\'';
		if($_REQUEST['include_inactive']!='Y')
			$sql .= ' AND ((\''.DBDate().'\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL) AND \''.DBDate().'\'>=ssm.START_DATE) ';
		if($_REQUEST['_search_all_schools']!='Y')
			$sql .= ' AND ssm.SCHOOL_ID=\''.UserSchool().'\' ';
		$sql = appendSQL($sql,$tmp_extra=array('NoSearchTerms'=>true)); // extra must be lvalue
		$RET = DBGet(DBQuery($sql),array(),array('STUDENT_ID','SHORT_DATE'));
		
	}

	if(count($cal_RET))
	{
		foreach($cal_RET as $value)
		{
			$extra['SELECT'] .= ',\'\' as _'.str_replace('-','',$value['SCHOOL_DATE']);
			$extra['columns_after']['_'.str_replace('-','',$value['SCHOOL_DATE'])] = ShortDate($value['SCHOOL_DATE']);
			$extra['functions']['_'.str_replace('-','',$value['SCHOOL_DATE'])] = '_makeColor';
			$extra['link']['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]&day_start=$_REQUEST[day_start]&day_end=$_REQUEST[day_end]&month_start=$_REQUEST[month_start]&month_end=$_REQUEST[month_end]&year_start=$_REQUEST[year_start]&year_end=$_REQUEST[year_end]&period_id=$_REQUEST[period_id]";
			$extra['link']['FULL_NAME']['variables'] = array('student_id'=>'STUDENT_ID');
			
		}
	}

	Widgets('course');
	Widgets('absences');

	$extra['new'] = true;
	
	Search('student_id',$extra);
	
	echo '</FORM>';
}

function _makeColor($value,$column)
{	global $THIS_RET,$RET,$attendance_codes;

	if($_REQUEST['period_id'])
	{
		if(!$attendance_codes)
			$attendance_codes = DBGet(DBQuery('SELECT ID,DEFAULT_CODE,STATE_CODE,SHORT_NAME FROM attendance_codes WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND TABLE_NAME=\'0\''),array(),array('ID'));

		if($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['DEFAULT_CODE']=='Y')
			return "<TABLE bgcolor=#00FF00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>".makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'],$THIS_RET['STUDENT_ID'],$column)."</TD></TR></TABLE>";
		elseif($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['STATE_CODE']=='P')
			return "<TABLE bgcolor=#FFCC00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>".makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'],$THIS_RET['STUDENT_ID'],$column)."</TD></TR></TABLE>";
		elseif($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['STATE_CODE']=='A')
			return "<TABLE bgcolor=#FF0000 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>".makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'],$THIS_RET['STUDENT_ID'],$column)."</TD></TR></TABLE>";
	}
	else
	{
		if($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE']=='0.0')
			return "<TABLE bgcolor=#FF0000 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>A</TD></TR></TABLE>";
		elseif($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] > 0 && $RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] < 1)
			return "<TABLE bgcolor=#FFCC00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>H</TD></TR></TABLE>";
		elseif($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] == 1)
			return "<TABLE bgcolor=#00FF00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>P</TD></TR></TABLE>";
	}
}

function _makePeriodColor($name,$state_code,$default_code)
{
	if($state_code=='A' || $state_code=='0.0')
		$color = '#FF0000';
	elseif($default_code=='Y' || $state_code=='1.0')
		$color='#00FF00';
	elseif($state_code=='P' || is_numeric($state_code))
		$color = '#FFCC00';

	if($color) 

		return "<TABLE bgcolor=$color cellpadding=0 cellspacing=0 width=10><TR><TD>$name</TD></TR></TABLE>";
	else
		return false;
}

function makeCodePulldown($value,$student_id,$date)
{	global $THIS_RET,$attendance_codes,$_openSIS;

	$date = substr($date,1,4).'-'.substr($date,5,2).'-'.substr($date,7);

	if(!$_openSIS['code_options'])
	{
		foreach($attendance_codes as $id=>$code)
			$_openSIS['code_options'][$id] = $code[1]['SHORT_NAME'];
	}

	return SelectInput($value,'attendance['.$student_id.']['.$date.'][ATTENDANCE_CODE]','',$_openSIS['code_options']);
}
?>