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
						'students/Student.php'=>_studentInfo,
						'students/Student.php&include=GeneralInfoInc&student_id=new'=>_addAStudent,
						'students/AssignOtherInfo.php'=>_groupAssignStudentInfo,
						
                                                'students/StudentReenroll.php'=>_studentReEnroll,
						1=>_reports,
						'students/AdvancedReport.php'=>_advancedReport,
						'students/AddDrop.php'=>_addDropReport,
						'students/Letters.php'=>_printLetters,
						'students/MailingLabels.php'=>_printMailingLabels,
						'students/StudentLabels.php'=>_printStudentLabels,
						'students/PrintStudentInfo.php'=>_printStudentInfo,
                        'students/PrintStudentContactInfo.php'=>_printStudentContactInfo,
                        'students/GoalReport.php'=>_printGoalsProgresses,
                        'students/EnrollmentReport.php'=>_studentEnrollmentReport,
						2=>_setup,
						'students/StudentFields.php'=>_studentFields,
						'students/EnrollmentCodes.php'=>_enrollmentCodes,
						
					);

$menu['students']['teacher'] = array(
						'students/Student.php'=>_studentInfo,
						'students/AddUsers.php'=>_associatedParents,
						1=>_reports,
						'students/AdvancedReport.php'=>_advancedReport,
						'students/StudentLabels.php'=>_printStudentLabels,
					);

$menu['students']['parent'] = array(
						'students/Student.php'=>_studentInfo,
						'students/ChangePassword.php'=>_changePassword,
					);

$exceptions['students'] = array(
						'students/Student.php?include=GeneralInfoInc?student_id=new'=>true,
						'students/AssignOtherInfo.php'=>true
					);
?>
