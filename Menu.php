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
if (!$_SESSION['STAFF_ID'] && !$_SESSION['STUDENT_ID'] && (strpos($_SERVER['PHP_SELF'], 'index.php')) === false) {
	header('Location: index.php');
	exit();
}
if (!$_openSIS['Menu']) {
	foreach ($openSISModules as $module => $include)
		if ($include) {
			include "modules/$module/Menu.php";
		}

	$profile = User('PROFILE');

	if ($profile != 'student')
		if (User('PROFILE_ID') != '') {

			$can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
		} else {
			$profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
			$profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
			if ($profile_id_mod != '')
				$can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
		}
	else {
		$can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='3' AND CAN_USE='Y'"), array(), array('MODNAME'));
		$profile = 'parent';
	}

	foreach ($menu as $modcat => $profiles) {
		$menuprof = $menu;
		$programs = $profiles[$profile];
		foreach ($programs as $program => $title) {
			if (!is_numeric($program)) {
				if ($can_use_RET[$program] && ($profile != 'admin' || !$exceptions[$modcat][$program] || AllowEdit($program)))
					$_openSIS['Menu'][$modcat][$program] = $title;
			} else {
				$_openSIS['Menu'][$modcat][$program] = $title;
			}
		}
	}

	if (User('PROFILE') == 'student')
		unset($_openSIS['Menu']['users']);
}
