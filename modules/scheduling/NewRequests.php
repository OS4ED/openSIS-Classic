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
DrawHeader(ProgramTitle());
Widgets('request');
if(!UserStudentID())
	echo '<BR>';
Search('student_id',$extra);
if(!$_REQUEST['modfunc'] && UserStudentID())
	$_REQUEST['modfunc'] = 'choose';
if($_REQUEST['modfunc']=='verify')
{
	$QI = DBQuery('SELECT TITLE,COURSE_ID,SUBJECT_ID FROM courses WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\'');
	$courses_RET = DBGet($QI,array(),array('COURSE_ID'));
	DBQuery('DELETE FROM schedule_requests WHERE STUDENT_ID=\''.UserStudentID().'\' AND SYEAR=\''.UserSyear().'\'');
	foreach($_REQUEST['courses'] as $subject=>$courses)
	{
		$courses_count = count($courses);
		for($i=0;$i<$courses_count;$i++)
		{
			$course = $courses[$i];
			if(!$course)
				continue;
			$sql = 'INSERT INTO schedule_requests (SYEAR,SCHOOL_ID,STUDENT_ID,SUBJECT_ID,COURSE_ID,MARKING_PERIOD_ID,WITH_TEACHER_ID,NOT_TEACHER_ID,WITH_PERIOD_ID,NOT_PERIOD_ID)
						values(\''.UserSyear().'\',\''.UserSchool().'\',\''.UserStudentID().'\',\''.$courses_RET[$course][1]['SUBJECT_ID'].'\',\''.$course.'\',NULL,\''.$_REQUEST['with_teacher'][$subject][$i].'\',\''.$_REQUEST['without_teacher'][$subject][$i].'\',\''.$_REQUEST['with_period'][$subject][$i].'\',\''.$_REQUEST['without_period'][$subject][$i].'\')';
			DBQuery($sql);
		}
	}
	echo ErrorMessage($error,'Error');
	$_SCHEDULER['student_id'] = UserStudentID();
	$_SCHEDULER['dont_run'] = true;
	include('modules/scheduling/Scheduler.php');
	$_REQUEST['modfunc'] = 'choose';
}
if($_REQUEST['modfunc']=='choose')
{
	$functions = array('WITH_PERIOD_ID'=>'_makeWithSelects','NOT_PERIOD_ID'=>'_makeWithoutSelects');
	$requests_RET = DBGet(DBQuery('SELECT sr.COURSE_ID,c.COURSE_TITLE,sr.WITH_PERIOD_ID,sr.NOT_PERIOD_ID,sr.WITH_TEACHER_ID,
										sr.NOT_TEACHER_ID FROM schedule_requests sr,courses c
									WHERE sr.SYEAR=\''.UserSyear().'\' AND sr.STUDENT_ID=\''.UserStudentID().'\' AND sr.COURSE_ID=c.COURSE_ID'),$functions);

	echo "<FORM name=vary id=vary action=Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."&modfunc=verify method=POST>";
	DrawHeader('',SubmitButton(_save,'','class="btn btn-primary" onclick=\'formload_ajax("vary");\''));

	$columns = array('');
	ListOutput($requests_RET,$columns,_request,_requests);

	echo '<CENTER>'.SubmitButton(_save,'','class="btn btn-primary" onclick=\'formload_ajax("vary");\'').'</CENTER></FORM>';
}
?>
