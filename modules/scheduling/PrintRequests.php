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
Widgets('request');
Widgets('mailing_labels');
$extra['force_search'] = true;
if(!$_REQUEST['search_modfunc'] || $_openSIS['modules_search'])
{
	DrawBC("Scheduling > ".ProgramTitle());
	$extra['new'] = true;
	$extra['action'] .= "&_openSIS_PDF=true&head_html=Student+Print+Request";
	$extra['pdf'] = true;
	Search('student_id',$extra);
}
else
{
	$columns = array('COURSE_TITLE'=>'Course','MARKING_PERIOD_ID'=>'Marking Period','WITH_TEACHER_ID'=>'With Teacher','WITH_PERIOD_ID'=>'In Period','NOT_TEACHER_ID'=>'Not with Teacher','NOT_PERIOD_ID'=>'Not in Period');
	$extra['SELECT'] .= ',c.TITLE AS COURSE_TITLE,c.COURSE_ID,srp.PRIORITY,srp.MARKING_PERIOD_ID,srp.WITH_TEACHER_ID,srp.NOT_TEACHER_ID,srp.WITH_PERIOD_ID,srp.NOT_PERIOD_ID';
	$extra['FROM'] .= ',courses c,schedule_requests srp';
	$extra['WHERE'] .= ' AND ssm.STUDENT_ID=srp.STUDENT_ID AND ssm.SYEAR=srp.SYEAR AND srp.COURSE_ID = c.COURSE_ID';
	
	$extra['functions'] += array('WITH_FULL_NAME'=>'_makeExtra','MARKING_PERIOD_ID'=>'_makeMpName');
	$extra['group'] = array('STUDENT_ID');
	if($_REQUEST['mailing_labels']=='Y')
        {
		$extra['group'][] = 'ADDRESS_ID';	
        }
	$RET = GetStuList($extra);

	if(count($RET))
	{
		$__DBINC_NO_SQLSHOW = true;
		$handle = PDFStart();
        	echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
			echo "<tr><td width=105>".DrawLogo()."</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">Student Print Request</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br \>Powered by openSIS</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
		foreach($RET as $student_id=>$courses)
		{
			if($_REQUEST['mailing_labels']=='Y')
			{
				foreach($courses as $address)
				{
					
					unset($_openSIS['DrawHeader']);
					
					echo "</table >";
			echo '<BR><BR>';
    		echo '<table border=0>';
    		echo "<tr><td>Student ID:</td>";
			echo "<td>".$address[1]['STUDENT_ID']."</td></tr>";
    		echo "<tr><td>Student Name:</td>";
			echo "<td>".$address[1]['FULL_NAME']."</td></tr>";
			echo "<tr><td>Student Grade:</td>";
			echo "<td>".$address[1]['GRADE_ID']."</td></tr>";
			if($address[1]['MAILING_LABEL'] !='')
			{
			echo "<tr><td>Student Mailling Label :</td>";
			echo "<td> ".$address[1]['MAILING_LABEL']."</td></tr>";
			}
			echo'</table>';
				
					ListOutputPrint($address,$columns,'Request','Requests',array(),array(),array('center'=>false,'print'=>false));
					echo '<!-- NEW PAGE -->';				
				}
			}
			else
			{
				unset($_openSIS['DrawHeader']);

				
			
			echo "</table >";
			echo '<BR><BR>';
    		echo '<table border=0>';
    		echo "<tr><td>Student ID:</td>";
			echo "<td>".$courses[1]['STUDENT_ID']."</td></tr>";
    		echo "<tr><td>Student Name:</td>";
			echo "<td>".$courses[1]['FULL_NAME']."</td></tr>";
			echo "<tr><td>Student Grade:</td>";
			echo "<td>".$courses[1]['GRADE_ID']."</td></tr>";
			if($address[1]['MAILING_LABEL'] !='')
			{
			echo "<tr><td>Student Mailling Label :</td>";
			echo "<td> ".$courses[1]['MAILING_LABEL']."</td></tr>";
			}
			echo'</table>';
		
			foreach($courses as $key=>$value){
				// set MARKING_PERIOD_ID
				if($courses[$key]['WITH_TEACHER_ID']){
                                    $get_mp_id=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.COURSE_ID=\''.$value['COURSE_ID'].'\' AND cpv.PERIOD_ID=\''.$value['WITH_PERIOD_ID'].'\' '));
                                    $stmt = DBGet(DBQuery('select title from marking_periods
									  where marking_period_id=\''.$get_mp_id[1]['MARKING_PERIOD_ID'].'\' limit 1'));
                                    
				$marking_period_id = $courses[$key]['MARKING_PERIOD_ID'];
				
				$title='';
				
				$courses[$key]['MARKING_PERIOD_ID']=$stmt[1]['TITLE'];
				unset($stmt);
				}
				// set WITH_TEACHER_ID
				if($courses[$key]['WITH_TEACHER_ID']){
//                                   
				$stmt = DBGet(DBQuery('select CONCAT(first_name,\''.' '.'\',last_name) as title from staff where staff_id=\''.$courses[$key]['WITH_TEACHER_ID'].'\' limit 1'));
				
                                $staff_id = $courses[$key]['WITH_TEACHER_ID'];
				$title='';
				$courses[$key]['WITH_TEACHER_ID']=$stmt[1]['TITLE'];
				unset($stmt);
				}
				// set NOT_TEACHER_ID
				if($courses[$key]['NOT_TEACHER_ID']){
				$stmt = DBGet(DBQuery('select CONCAT(first_name,\''.' '.'\',last_name) as title from staff where staff_id=\''.$courses[$key]['NOT_TEACHER_ID'].'\' limit 1'));
				$staff_id = $courses[$key]['NOT_TEACHER_ID'];
				$title='';
				$courses[$key]['NOT_TEACHER_ID']=$stmt[1]['TITLE'];
				unset($stmt);
				}
				// set WITH_PERIOD_ID
				if($courses[$key]['WITH_PERIOD_ID']){
				$stmt = DBGet(DBQuery('select title from school_periods where period_id=\''.$courses[$key]['WITH_PERIOD_ID'].'\' limit 1'));
				$period_id = $courses[$key]['WITH_PERIOD_ID'];
				$title='';
				$courses[$key]['WITH_PERIOD_ID']=$stmt[1]['TITLE'];
				unset($stmt);
				}
				// set NOT_PERIOD_ID
				if($courses[$key]['NOT_PERIOD_ID']){
				$stmt = DBGet(DBQuery('select title from school_periods where period_id=\''.$courses[$key]['NOT_PERIOD_ID'].'\' limit 1'));
				$period_id = $courses[$key]['NOT_PERIOD_ID'];
				$title='';
				$courses[$key]['NOT_PERIOD_ID']=$stmt[1]['TITLE'];
				unset($stmt);
				}
			}
				
				ListOutputPrint($courses,$columns,'Request','Requests',array(),array(),array('center'=>false,'print'=>false));
				echo '<!-- NEW PAGE -->';
			}
		}
		PDFStop($handle);
	}
	else
		BackPrompt('No Students were found.');
}

function _makeExtra($value,$title='')
{	global $THIS_RET;

	if($THIS_RET['WITH_TEACHER_ID'])
		$return .= 'With:&nbsp;'.GetTeacher($THIS_RET['WITH_TEACHER_ID']).'<BR>';
	if($THIS_RET['NOT_TEACHER_ID'])
		$return .= 'Not With:&nbsp;'.GetTeacher($THIS_RET['NOT_TEACHER_ID']).'<BR>';
	if($THIS_RET['WITH_PERIOD_ID'])
		$return .= 'On:&nbsp;'.GetPeriod($THIS_RET['WITH_PERIOD_ID']).'<BR>';
	if($THIS_RET['NOT_PERIOD_ID'])
		$return .= 'Not On:&nbsp;'.GetPeriod($THIS_RET['NOT_PERIOD_ID']).'<BR>';
	if($THIS_RET['PRIORITY'])
		$return .= 'Priority:&nbsp;'.$THIS_RET['PRIORITY'].'<BR>';
	if($THIS_RET['MARKING_PERIOD_ID'])
		$return .= 'Marking Period:&nbsp;'.GetMP($THIS_RET['MARKING_PERIOD_ID']).'<BR>';

	return $return;
}
function _makeMpName($value)
{
    if($value!='')
    {
    $get_name=DBGet(DBQuery('SELECT TITLE FROM marking_periods WHERE marking_period_id='.$value));
    return $get_name[1]['TITLE'];
    }
    else
    return 'Custom Course Period';
}
?>