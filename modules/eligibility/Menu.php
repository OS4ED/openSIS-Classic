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
$menu['eligibility']['admin'] = array(
						'eligibility/Student.php'=>_studentScreen,
						'eligibility/AddActivity.php'=>_addActivity,
						1=>_reports,
						'eligibility/StudentList.php'=>_studentList,
						'eligibility/TeacherCompletion.php'=>_teacherCompletion,
						2=>_setup,
						'eligibility/Activities.php'=>_activities,
						'eligibility/EntryTimes.php'=>_entryTimes,
					);

$menu['eligibility']['teacher'] = array(
						'eligibility/EnterEligibility.php'=>_enterExtracurricular,
					);

$menu['eligibility']['parent'] = array(
						'eligibility/Student.php'=>_studentScreen,
						'eligibility/StudentList.php'=>_studentList,
					);

$menu['users']['admin'] += array(
						'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php'=>_enterExtracurricular,
					);

$exceptions['eligibility'] = array(
						'eligibility/AddActivity.php'=>true
					);

$exceptions['users'] += array(
						'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php'=>true
					);
?>