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
include('../../../RedirectIncludes.php');
include_once('modules/students/includes/FunctionsInc.php');

if(($_REQUEST['month_values'] && ($_POST['month_values'] || $_REQUEST['ajax'])) || ($_REQUEST['values']['student_enrollment'] && ($_POST['values']['student_enrollment'] || $_REQUEST['ajax'])))
{
	if(!$_REQUEST['values']['student_enrollment']['new']['ENROLLMENT_CODE'] && !$_REQUEST['month_values']['student_enrollment']['new']['START_DATE'])
	{
		unset($_REQUEST['values']['student_enrollment']['new']);
		unset($_REQUEST['day_values']['student_enrollment']['new']);
		unset($_REQUEST['month_values']['student_enrollment']['new']);
		unset($_REQUEST['year_values']['student_enrollment']['new']);
	}
	else
	{
		$date = $_REQUEST['day_values']['student_enrollment']['new']['START_DATE'].'-'.$_REQUEST['month_values']['student_enrollment']['new']['START_DATE'].'-'.$_REQUEST['year_values']['student_enrollment']['new']['START_DATE'];
		$found_RET = DBGet(DBQuery("SELECT ID FROM student_enrollment WHERE STUDENT_ID='".UserStudentID()."' AND SYEAR='".UserSyear()."' AND '" . date("Y-m-d",strtotime($date))."' BETWEEN START_DATE AND END_DATE"));
		if(count($found_RET))
		{
			unset($_REQUEST['values']['student_enrollment']['new']);
			unset($_REQUEST['day_values']['student_enrollment']['new']);
			unset($_REQUEST['month_values']['student_enrollment']['new']);
			unset($_REQUEST['year_values']['student_enrollment']['new']);
			echo ErrorMessage(array(_theStudentIsAlreadyEnrolledOnThatDateAndCouldNotBeEnrolledASecondTimeOnTheDateYouSpecifiedPleaseFixAndTryEnrollingTheStudentAgain));
		}
	}

	$iu_extra['student_enrollment'] = "STUDENT_ID='".UserStudentID()."' AND ID='__ID__'";
	$iu_extra['fields']['student_enrollment'] = 'SYEAR,STUDENT_ID,';
	$iu_extra['values']['student_enrollment'] = "'".UserSyear()."','".UserStudentID()."',";
	if(!$new_student)
		SaveData($iu_extra,'',$field_names);
}

$functions = array('START_DATE'=>'_makeStartInput','END_DATE'=>'_makeEndInput','SCHOOL_ID'=>'_makeSchoolInput');
unset($THIS_RET);
$RET = DBGet(DBQuery('SELECT e.ID,e.ENROLLMENT_CODE,e.START_DATE,e.DROP_CODE,e.END_DATE,e.END_DATE AS END,e.SCHOOL_ID,e.NEXT_SCHOOL,e.CALENDAR_ID FROM student_enrollment e WHERE e.STUDENT_ID=\''.UserStudentID().'\' AND e.SYEAR=\''.UserSyear().'\' ORDER BY e.START_DATE'),$functions);

$add = true;
if(count($RET))
{
	foreach($RET as $value)
	{
		if($value['DROP_CODE']=='' || !$value['DROP_CODE'])
			$add = false;
	}
}
if($add)
	$link['add']['html'] = array('START_DATE'=>_makeStartInput('','START_DATE'),'SCHOOL_ID'=>_makeSchoolInput('','SCHOOL_ID'));

$columns = array('START_DATE'=>'Attendance Start Date this School Year','END_DATE'=>'Dropped','SCHOOL_ID'=>'School');

$schools_RET = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID!=\''.UserSchool().'\''));
$next_school_options = array(UserSchool()=>_nextGradeAtCurrentSchool,
'0'=>_retain,
'-1'=>_doNotEnrollAfterThisSchoolYear,
);
if(count($schools_RET))
{
	foreach($schools_RET as $school)
		$next_school_options[$school['ID']] = $school['TITLE'];
}

$calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,DEFAULT_CALENDAR,TITLE FROM school_calendars WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY DEFAULT_CALENDAR ASC'));
if(count($calendars_RET))
{
	foreach($calendars_RET as $calendar)
		$calendar_options[$calendar['CALENDAR_ID']] = $calendar['TITLE'];
}
if($_REQUEST['student_id']!='new')
{
	if(count($RET))
		$id = $RET[count($RET)]['ID'];
	else
		$id = 'new';

	ListOutput($RET,$columns,_enrollmentRecord,_enrollmentRecords,$link);
	if($id!='new')
		$next_school = $RET[count($RET)]['NEXT_SCHOOL'];
	if($id!='new')
		$calendar = $RET[count($RET)]['CALENDAR_ID'];
	$div = true;
}
else
{
 	$id = 'new';
	ListOutputMod($RET,$columns,enrollmentRecord,enrollmentRecords,$link,array(),array('count'=>false));
	$next_school = UserSchool();
	$calendar = $calendars_RET[1]['CALENDAR_ID'];
	$div = false;
}
echo '<CENTER><TABLE><TR><TD>'.SelectInput($calendar,"values[student_enrollment][$id][CALENDAR_ID]",(!$calendar||!$div?'<FONT color=red>':'').'Calendar'.(!$calendar||!$div?'</FONT>':''),$calendar_options,false,'',$div).'</TD><TD width=30></TD><TD>'.SelectInput($next_school,"values[student_enrollment][$id][NEXT_SCHOOL]",(!$next_school||!$div?'<FONT color=red>':'').'Rolling / Retention Options'.(!$next_school||!$div?'</FONT>':''),$next_school_options,false,'',$div).'</TD></TR></TABLE></CENTER>';
 
?>