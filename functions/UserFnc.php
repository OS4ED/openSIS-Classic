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
function User($item)
{
	global $_openSIS, $DefaultSyear;
	if (!$_SESSION['UserSyear'])
		$_SESSION['UserSyear'] = $DefaultSyear;

	if (!$_openSIS['User'] || $_SESSION['UserSyear'] != $_openSIS['User'][1]['SYEAR']) {
		if ($_SESSION['STAFF_ID']) {
			if ($_SESSION['PROFILE_ID'] != 4)
				$sql = 'SELECT STAFF_ID,USERNAME,CONCAT(FIRST_NAME,\' \',LAST_NAME) AS NAME,PROFILE,la.PROFILE_ID,CURRENT_SCHOOL_ID,EMAIL FROM staff s ,login_authentication la WHERE la.USER_ID=s.STAFF_ID AND la.PROFILE_ID <> 3 AND la.PROFILE_ID=s.PROFILE_ID AND STAFF_ID=' . $_SESSION['STAFF_ID'];
			if ($_SESSION['PROFILE_ID'] == 4 || $_SESSION['PROFILE'] == 'parent')
				$sql = 'SELECT p.STAFF_ID,la.USERNAME,CONCAT(p.FIRST_NAME,\' \',p.LAST_NAME) AS NAME,p.PROFILE,p.PROFILE_ID,p.CURRENT_SCHOOL_ID,p.EMAIL FROM people p ,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PROFILE_ID <> 3  AND la.PROFILE_ID=p.PROFILE_ID AND STAFF_ID=' . $_SESSION['STAFF_ID'];
			$_openSIS['User'] = DBGet(DBQuery($sql));
		} elseif ($_SESSION['STUDENT_ID']) {
			$sql = 'SELECT USERNAME,CONCAT(s.FIRST_NAME,\' \',s.LAST_NAME) AS NAME,\'student\' AS PROFILE,\'3\' AS PROFILE_ID,CONCAT(\',\',se.SCHOOL_ID,\',\') AS SCHOOLS,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se,login_authentication la WHERE la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID = 3 AND s.STUDENT_ID=' . $_SESSION['STUDENT_ID'] . ' AND se.SYEAR=\'' . $_SESSION['UserSyear'] . '\'  AND (se.END_DATE IS NULL OR se.END_DATE=\'0000-00-00\' OR se.END_DATE>=\'' . date('Y-m-d') . '\' ) AND se.STUDENT_ID=s.STUDENT_ID ORDER BY se.END_DATE DESC LIMIT 1';
			$_openSIS['User'] = DBGet(DBQuery($sql));
			if (count($_openSIS['User']) == 0) {
				$sql = 'SELECT USERNAME,CONCAT(s.FIRST_NAME,\' \',s.LAST_NAME) AS NAME,\'student\' AS PROFILE,\'3\' AS PROFILE_ID,CONCAT(\',\',se.SCHOOL_ID,\',\') AS SCHOOLS,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se,login_authentication la WHERE la.USER_ID=s.STUDENT_ID AND la.PROFILE_ID = 3 AND s.STUDENT_ID=' . $_SESSION['STUDENT_ID'] . ' AND se.SYEAR=\'' . $_SESSION['UserSyear'] . '\'   AND se.STUDENT_ID=s.STUDENT_ID ORDER BY se.END_DATE DESC LIMIT 1';
				$_openSIS['User'] = DBGet(DBQuery($sql));
				$_SESSION['UserSchool'] = $_openSIS['User'][1]['SCHOOL_ID'];
			} else {
				$_SESSION['UserSchool'] = $_openSIS['User'][1]['SCHOOL_ID'];
			}
		} else {
			exit('Error in User()');
		}
	}

	return $_openSIS['User'][1][$item];
}
function SelectedUserProfile($option)
{
	$prof = DBGet(DBQuery('SELECT ' . $option . ' FROM staff WHERE STAFF_ID=' . UserStaffID()));
	return $prof[1][$option];
}
function SelfStaffProfile($option)
{
	$prof = DBGet(DBQuery('SELECT ' . $option . ' FROM staff WHERE STAFF_ID=' . UserID()));
	return $prof[1][$option];
}
function Preferences($item, $program = 'Preferences')
{
	global $_openSIS;

	if ($_SESSION['STAFF_ID'] && !$_openSIS['Preferences'][$program]) {
		if ($program == 'Gradebook')
			$QI = DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=' . $_SESSION['STAFF_ID'] . ' AND PROGRAM=\'' . $program . '\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\'');
		else
			$QI = DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=' . $_SESSION['STAFF_ID'] . ' AND PROGRAM=\'' . $program . '\'');
		$_openSIS['Preferences'][$program] = DBGet($QI, array(), array('TITLE'));
	}

	$defaults = array(
		'NAME' => 'Common',
		'SORT' => 'Name',
		'SEARCH' => 'Y',
		'DELIMITER' => 'Tab',
		'COLOR' => '#FFFFCC',
		'HIGHLIGHT' => '#85E1FF',
		'TITLES' => 'gray',
		'THEME' => 'Brushed-Steel',
		'HIDDEN' => 'Y',
		'MONTH' => 'M',
		'DAY' => 'j',
		'YEAR' => 'Y',
		'DEFAULT_ALL_SCHOOLS' => 'N',
		'ASSIGNMENT_SORTING' => 'ASSIGNMENT_ID',
		'ANOMALOUS_MAX' => '100'
	);

	if (!isset($_openSIS['Preferences'][$program][$item][1]['VALUE']))
		$_openSIS['Preferences'][$program][$item][1]['VALUE'] = $defaults[$item];

	if ($_SESSION['STAFF_ID'] && User('PROFILE') == 'parent' || $_SESSION['STUDENT_ID'])
		$_openSIS['Preferences'][$program]['SEARCH'][1]['VALUE'] = 'N';
	if ($program == 'Gradebook') {
		if ($item == 'ANOMALOUS_MAX') {
			$arr = explode('_', $_openSIS['Preferences'][$program][$item][1]['VALUE']);
			return $arr[0];
		} else
			return rtrim($_openSIS['Preferences'][$program][$item][1]['VALUE'], '_' . UserCoursePeriod());
	} else
		return $_openSIS['Preferences'][$program][$item][1]['VALUE'];
}
function StaffCategory($staff_id)
{
	$category = DBGet(DBquery('SELECT CATEGORY FROM staff_school_info WHERE STAFF_ID=' . $staff_id));
	return ($category[1]['CATEGORY'] == '' ? 'N/A' : $category[1]['CATEGORY']);
}
