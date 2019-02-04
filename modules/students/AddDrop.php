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
echo "<FORM name=add id=add action=".PreparePHP_SELF()." method=POST>";
DrawBC("Students > ".ProgramTitle());
if($_REQUEST['day__start'] && $_REQUEST['month__start'] && $_REQUEST['year__start'])
{
	while(!VerifyDate($start_date = $_REQUEST['day__start'].'-'.$_REQUEST['month__start'].'-'.$_REQUEST['year__start']))
		$_REQUEST['day__start']--;
        
        
}
else
	$start_date = date('Y-m').'-01';
if($_REQUEST['day__end'] && $_REQUEST['month__end'] && $_REQUEST['year__end'])
{
	while(!VerifyDate($end_date = $_REQUEST['day__end'].'-'.$_REQUEST['month__end'].'-'.$_REQUEST['year__end']))
		$_REQUEST['day__end']--;
}
else
	$end_date = DBDate('mysql');


$start_date=date('Y-m-d',strtotime($start_date));
$end_date=date('Y-m-d',strtotime($end_date));
echo '<div class="panel panel-default">';
echo '<div class="panel-body"><div class="form-inline"><div class="row"><div class="col-md-12">'.PrepareDateSchedule($start_date,'_start').' &nbsp; <label class="control-label"> &nbsp; - &nbsp; </label> &nbsp; '.PrepareDateSchedule($end_date,'_end'),' &nbsp; <INPUT type=submit class="btn btn-primary" value=Go></div></div></div></div>';
echo '</div>';
echo '</FORM>';

$enrollment_RET = DBGet(DBQuery('SELECT se.START_DATE,se.END_DATE,se.START_DATE AS DATE,se.SCHOOL_ID,se.STUDENT_ID,CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME) AS FULL_NAME,(SELECT TITLE FROM student_enrollment_codes seci WHERE se.enrollment_code=seci.id AND se.START_DATE>=\''.$start_date.'\') AS ENROLLMENT_CODE,(SELECT TITLE FROM student_enrollment_codes seci WHERE se.drop_code=seci.id) AS DROP_CODE FROM student_enrollment se, students s WHERE s.STUDENT_ID=se.STUDENT_ID AND ((se.START_DATE>=\''.$start_date.'\' AND se.END_DATE<=\''.$end_date.'\') OR (se.START_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\') OR (se.END_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\'))
								ORDER BY DATE DESC'),array('START_DATE'=>'ProperDate','END_DATE'=>'ProperDate','SCHOOL_ID'=>'GetSchool'));
$columns = array('FULL_NAME'=>'Student','STUDENT_ID'=>'Student ID','SCHOOL_ID'=>'School','START_DATE'=>'Enrolled','ENROLLMENT_CODE'=>'Enrollment Code','END_DATE'=>'Dropped','DROP_CODE'=>'Drop Code');

echo '<div class="panel panel-default">';
ListOutput($enrollment_RET,$columns,'Enrollment Record','Enrollment Records');
echo '</div>';
?>