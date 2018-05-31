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
$menu['students']['admin'] = array(
						'students/Student.php'=>'Student Info',
						'students/Student.php&include=GeneralInfoInc&student_id=new'=>'Add a Student',
						'students/AssignOtherInfo.php'=>'Group Assign Student Info',
						
                                                'students/StudentReenroll.php'=>'Student Re Enroll',
						1=>'Reports',
						'students/AdvancedReport.php'=>'Advanced Report',
						'students/AddDrop.php'=>'Add / Drop Report',
						'students/Letters.php'=>'Print Letters',
						'students/MailingLabels.php'=>'Print Mailing Labels',
						'students/StudentLabels.php'=>'Print Student Labels',
						'students/PrintStudentInfo.php'=>'Print Student Info',
                        'students/PrintStudentContactInfo.php'=>'Print Student Contact Info',
                        'students/GoalReport.php'=>'Print Goals & Progresses',
                        'students/EnrollmentReport.php'=>'Student Enrollment Report',
						2=>'Setup',
						'students/StudentFields.php'=>'Student Fields',
						'students/EnrollmentCodes.php'=>'Enrollment Codes',
						
					);

$menu['students']['teacher'] = array(
						'students/Student.php'=>'Student Info',
						'students/AddUsers.php'=>'Associated Parents',
						1=>'Reports',
						'students/AdvancedReport.php'=>'Advanced Report',
						'students/StudentLabels.php'=>'Print Student Labels'
					);

$menu['students']['parent'] = array(
						'students/Student.php'=>'Student Info',
						'students/ChangePassword.php'=>'Change Password'
					);

$exceptions['students'] = array(
						'students/Student.php?include=GeneralInfoInc?student_id=new'=>true,
						'students/AssignOtherInfo.php'=>true
					);
?>
