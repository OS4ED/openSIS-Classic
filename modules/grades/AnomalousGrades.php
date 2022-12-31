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
$tmp_REQUEST = $_REQUEST;
unset($tmp_REQUEST['include_inactive']);
echo '<div class="panel panl-default">';
echo '<div class="panel-heading clearfix">';
echo "<FORM action=Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))." method=POST class='m-t-10 m-b-0'>";
DrawHeaderHome('<label class="checkbox-inline">&nbsp;<INPUT class=styled type=checkbox name=include_inactive value=Y'.($_REQUEST['include_inactive']=='Y'?" CHECKED onclick='document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&include_inactive=\";'":" onclick='document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&include_inactive=Y\";'").'>'._includeInactiveStudents.'</label>');
echo '</FORM>';
echo '</div>';
echo '<hr class="no-margin">';
$course_period_id = UserCoursePeriod();
$course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
$course_id = $course_id[1]['COURSE_ID'];
$max_allowed = Preferences('ANOMALOUS_MAX','Gradebook')/100;
$full_year_mp=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
$full_year_mp=$full_year_mp[1]['MARKING_PERIOD_ID'];
$extra['SELECT'] = ',ga.ASSIGNMENT_ID,gt.TITLE AS TYPE_TITLE,ga.TITLE,ga.POINTS AS TOTAL_POINTS,\'\' AS LETTER_GRADE';
// $extra['SELECT'] .= ',(SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) AS POINTS';
// $extra['SELECT'] .= ',(SELECT COMMENT FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) AS COMMENT';
$extra['SELECT'] .= ',(SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=ga.COURSE_PERIOD_ID) AS POINTS';
$extra['SELECT'] .= ',(SELECT COMMENT FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=ga.COURSE_PERIOD_ID) AS COMMENT';
$extra['FROM'] = ',gradebook_assignments ga,gradebook_assignment_types gt';
// $extra['WHERE'] = 'AND ((SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) IS NULL AND (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR (SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID)<0 OR (SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID)>ga.POINTS*'.$max_allowed.') AND ((ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.User('STAFF_ID').'\') OR ga.COURSE_PERIOD_ID=\''.$course_period_id.'\') AND (ga.MARKING_PERIOD_ID=\''.UserMP().'\' OR ga.MARKING_PERIOD_ID=\''.$full_year_mp.'\') AND ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID';
$extra['WHERE'] = 'AND ((SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=ga.COURSE_PERIOD_ID) IS NULL AND (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR (SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=ga.COURSE_PERIOD_ID)<0 OR (SELECT POINTS FROM gradebook_grades WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND COURSE_PERIOD_ID=ga.COURSE_PERIOD_ID)>ga.POINTS*'.$max_allowed.') AND ((ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.User('STAFF_ID').'\') OR ga.COURSE_PERIOD_ID=\''.$course_period_id.'\') AND (ga.MARKING_PERIOD_ID=\''.UserMP().'\' OR ga.MARKING_PERIOD_ID=\''.$full_year_mp.'\') AND ga.COURSE_PERIOD_ID=\''.$course_period_id.'\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID';

$extra['functions'] = array('POINTS'=>'_makePoints');
$students_RET = GetStuList($extra);

if(AllowUse('grades/Grades.php'))
	$link = array('FULL_NAME'=>array('link'=>"Modules.php?modname=grades/Grades.php&include_ianctive=$_REQUEST[include_inactive]&assignment_id=all",'variables'=>array('student_id'=>'STUDENT_ID')),'TITLE'=>array('link'=>"Modules.php?modname=grades/Grades.php&include_inactive=$_REQUEST[include_inactive]",'variables'=>array('assignment_id'=>'ASSIGNMENT_ID','student_id'=>'STUDENT_ID')));
$columns = array('FULL_NAME'=>_name,
'STUDENT_ID'=>_studentId,
'POINTS'=>_problem,
'TYPE_TITLE'=>_category,
'TITLE'=>_assignment,
'COMMENT'=>_comment,
);

ListOutput($students_RET,$columns,_anomalousGrade,_anomalousGrades,$link,array(),array('center'=>false,'save'=>false,'search'=>false));
echo '</div>';

function _makePoints($value,$column)
{	global $THIS_RET;

	if($value=='')
		return '<span class=text-danger>'._missing.'</span>';
	elseif($value=='-1')
		return '<span style="color:#00a000">'._excused.'</span>';
	elseif($value<0)
		return '<span class=text-danger>'._negative.'!</span>';
	elseif($THIS_RET['TOTAL_POINTS']==0)
		return '<span style="color:#0000ff">'._extraCredit.'</span>';
	
        $rounding=DBGet(DBQuery('SELECT VALUE AS ROUNDING FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_'.UserCoursePeriod().'\''));
                        $points_r=($value/$THIS_RET['TOTAL_POINTS'])*100;
                        if(rtrim($rounding[1]['ROUNDING'],'_'.UserCoursePeriod())=='UP')
                                $points_r = ceil($points_r);
                        elseif(rtrim($rounding[1]['ROUNDING'],'_'.UserCoursePeriod())=='DOWN')
                                $points_r = floor($points_r);
                        elseif(rtrim($rounding[1]['ROUNDING'],'_'.UserCoursePeriod())=='NORMAL')
                        {
                                $points_r = round($points_r,0);
}
                        else 
                              $points_r=round($points_r,2);
                        return $points_r;
}
?>