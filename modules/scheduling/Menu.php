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
$menu['scheduling']['admin'] = array(
						'scheduling/Schedule.php'=>_studentSchedule,
                                                'scheduling/ViewSchedule.php'=>_viewSchedule,
						'scheduling/Requests.php'=>_studentRequests,
						'scheduling/MassSchedule.php'=>_groupSchedule,
						'scheduling/MassRequests.php'=>_groupRequests,
						'scheduling/MassDrops.php'=>_groupDrops,
						'scheduling/MassDelete.php'=>_groupDelete,
						1=>_reports,
						'scheduling/SchoolwideScheduleReport.php'=>_schoolwideScheduleReport,
						'scheduling/PrintSchedules.php'=>_printSchedules,
						'scheduling/PrintClassLists.php'=>_printClassLists,
						'scheduling/PrintClassPictures.php'=>_printClassPictures,
						'scheduling/PrintRequests.php'=>_printRequests,
						'scheduling/ScheduleReport.php'=>_scheduleReport,
						'scheduling/RequestsReport.php'=>_requestsReport,
						'scheduling/UnfilledRequests.php'=>_unfilledRequests,
						'scheduling/IncompleteSchedules.php'=>_incompleteSchedules,
						'scheduling/AddDrop.php'=>_addDropReport,
						2=>_setup,
						
						'scheduling/Scheduler.php'=>_runScheduler,
					);

$menu['scheduling']['teacher'] = array(
						// 'scheduling/Schedule.php'=>_schedule,
                        'scheduling/ViewSchedule.php'=>_viewSchedule,
						1=>_reports,
						'scheduling/PrintSchedules.php'=>_printSchedules,
						'scheduling/PrintClassLists.php'=>_printClassLists,
						'scheduling/PrintClassPictures.php'=>_printClassPictures,
					);

$menu['scheduling']['parent'] = array(
						'scheduling/ViewSchedule.php'=>_schedule,
						'scheduling/PrintClassPictures.php'=>_classPictures,
						'scheduling/Requests.php'=>_studentRequests,
                        'scheduling/StudentScheduleReport.php'=>_scheduleReport,
					);

$exceptions['scheduling'] = array(
						'scheduling/Requests.php'=>true,
						'scheduling/MassRequests.php'=>true,
						'scheduling/Scheduler.php'=>true,
                        'scheduling/StudentScheduleReport.php'=>_scheduleReport,
					);
?>
