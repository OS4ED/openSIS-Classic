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
$menu['users']['admin'] = array(

						'users/Preferences.php'=>_preferences,
                                                1=>_report,
                                                'users/UserAdvancedReport.php'=>_userAdvancedReport,
                                                'users/UserAdvancedReportStaff.php'=>_staffAdvancedReport,
                                                2=>_parent,
						'users/User.php'=>_parentInfo,
                                                3=>_staff,
                                                'users/Staff.php'=>_staffInfo,
                                                'users/Staff.php&staff_id=new'=>_addAStaff,
						4=>_setup,
						'users/Profiles.php'=>_profiles,
						'users/UserFields.php'=>_parentFields,
                                                'users/StaffFields.php'=>_staffFields,
						5=>_teacherPrograms,

					);

$menu['users']['teacher'] = array(
                                                'users/Staff.php'=>_myInfo,
						'users/Preferences.php'=>_preferences,
					);

$menu['users']['parent'] = array(
                                                'users/User.php'=>_myInfo,
						'users/Preferences.php'=>_preferences,
					);

$exceptions['users'] = array(
						'users/User.php?staff_id=new'=>true
					);
?>