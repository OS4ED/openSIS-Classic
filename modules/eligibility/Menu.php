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
						'eligibility/Student.php'=>'Student Screen',
						'eligibility/AddActivity.php'=>'Add Activity',
						1=>'Reports',
						'eligibility/StudentList.php'=>'Student List',
						'eligibility/TeacherCompletion.php'=>'Teacher Completion',
						2=>'Setup',
						'eligibility/Activities.php'=>'Activities',
						'eligibility/EntryTimes.php'=>'Entry Times'
					);

$menu['eligibility']['teacher'] = array(
						'eligibility/EnterEligibility.php'=>'Enter Extracurricular'
					);

$menu['eligibility']['parent'] = array(
						'eligibility/Student.php'=>'Student Screen',
						'eligibility/StudentList.php'=>'Student List'
					);

$menu['users']['admin'] += array(
						'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php'=>'Enter Extracurricular'
					);

$exceptions['eligibility'] = array(
						'eligibility/AddActivity.php'=>true
					);

$exceptions['users'] += array(
						'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php'=>true
					);
?>