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
$menu['attendance']['admin'] = array(
						'attendance/Administration.php'=>_administration,
						'attendance/AddAbsences.php'=>_addAbsences,
						1=>_reports,
						'attendance/AttendanceData.php?list_by_day=true'=>_attendanceReport,
						'attendance/Percent.php'=>_averageDailyAttendance,
						'attendance/Percent.php?list_by_day=true'=>_averageAttendanceByDay,
						'attendance/DailySummary.php'=>_attendanceChart,
						'attendance/StudentSummary.php'=>_absenceSummary,
						'attendance/TeacherCompletion.php'=>_teacherCompletion,
						2=>_utilities,
						'attendance/FixDailyAttendance.php'=>_recalculateDailyAttendance,
						'attendance/DuplicateAttendance.php'=>_deleteDuplicateAttendance,
						3=>_setup,
						'attendance/AttendanceCodes.php'=>_attendanceCodes,
					);

$menu['attendance']['teacher'] = array(
						'attendance/TakeAttendance.php'=>_takeAttendance,
						'attendance/DailySummary.php'=>_attendanceChart,
						'attendance/StudentSummary.php'=>_absenceSummary,
					);

$menu['attendance']['parent'] = array(
						'attendance/StudentSummary.php'=>_absences,
						'attendance/DailySummary.php'=>_dailySummary,
					);

$menu['users']['admin'] += array(
						'users/TeacherPrograms.php?include=attendance/TakeAttendance.php'=>_takeAttendance,
						'users/TeacherPrograms.php?include=attendance/MissingAttendance.php'=>_missingAttendance,
					);

$exceptions['attendance'] = array(
						'attendance/AddAbsences.php'=>true
					);
?>
