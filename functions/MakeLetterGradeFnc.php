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
function _makeLetterGrade($percent,$course_period_id=0,$staff_id=0,$ret='')
{	
    global $programconfig,$_openSIS;
	if(!$course_period_id)
		$course_period_id = UserCoursePeriod();

	if(!$staff_id)
		$staff_id = User('STAFF_ID');

	if(!$programconfig[$staff_id])
	{
		$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_'.$course_period_id.'\''),array(),array('TITLE'));
		if(count($config_RET))
			foreach($config_RET as $title=>$value)
                        {
                                $unused_var=explode('_',$value[1]['VALUE']);
                                $programconfig[$staff_id][$title] =$unused_var[0];
                                $programconfig[$staff_id]['current_course_period_id'] =$course_period_id;
//				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
                        }
		else
			$programconfig[$staff_id] = true;
	}
        else
        {
           if($programconfig[$staff_id]['current_course_period_id']!=$course_period_id)
           {
               $programconfig[$staff_id]=array();
               $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_'.$course_period_id.'\''),array(),array('TITLE'));
		if(count($config_RET))
			foreach($config_RET as $title=>$value)
                        {
                                $unused_var=explode('_',$value[1]['VALUE']);
                                $programconfig[$staff_id][$title] =$unused_var[0];
                                $programconfig[$staff_id]['current_course_period_id'] =$course_period_id;
//				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
                        }
		else
			$programconfig[$staff_id] = true;
               
           }
        }
	if(!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
		$_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
	$does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
	$grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];

	$percent = is_numeric($percent) ? $percent * 100 : 0;
       
		if($programconfig[$staff_id]['ROUNDING']=='UP')
                {
			$percent = ceil($percent);
                }
		elseif($programconfig[$staff_id]['ROUNDING']=='DOWN')
                {
			$percent = floor($percent);
                }
		elseif($programconfig[$staff_id]['ROUNDING']=='NORMAL')
                {
			$percent = round($percent,0);
                }
                else
                {
		$percent = round($percent,2); // school default
                }
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
?>
