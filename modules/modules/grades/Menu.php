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
$menu['grades']['admin'] = array(
						'grades/ReportCards.php'=>_reportCards,
						//'grades/CalcGPA.php'=>_calculateGpa,
						'grades/Transcripts.php'=>_transcripts,
						1=>_reports,
						'grades/TeacherCompletion.php'=>_teacherCompletion,
						'grades/GradeBreakdown.php'=>_gradeBreakdown,
						'grades/FinalGrades.php'=>_studentFinalGrades,
						'grades/GPARankList.php'=>_gpaClassRankList,
                                                'grades/AdminProgressReports.php'=>_progressReports,
                        'grades/HonorRoll.php'=>_honorRoll,
						2=>_setup,
						'grades/ReportCardGrades.php'=>_reportCardGrades,
						'grades/ReportCardComments.php'=>_reportCardComments,
                                                'grades/HonorRollSetup.php'=>_honorRollSetup,
                        'grades/HonorRollSetup.php'=>_honorRollSetup,
						3=>_utilities,
                                                'grades/EditReportCardGrades.php'=>_editReportCardGrades,
                                                'grades/EditHistoryMarkingPeriods.php'=>_addEditHistoricalMarkingPeriods,
                                                'grades/HistoricalReportCardGrades.php'=>_addEditHistoricalReportCardGrades,
					);

$menu['grades']['teacher'] = array(
						'grades/InputFinalGrades.php'=>_inputFinalGrades,
						'grades/ReportCards.php'=>_reportCards,
						1=>_gradebook,
						'grades/Grades.php'=>_grades,
						'grades/Assignments.php'=>_assignments,
						'grades/AnomalousGrades.php'=>_anomalousGrades,
						'grades/ProgressReports.php'=>_progressReports,
						2=>_reports,
						'grades/StudentGrades.php'=>_studentGrades,
						'grades/FinalGrades.php'=>_finalGrades,
						3=>_setup,
						'grades/Configuration.php'=>_configuration,
						'grades/ReportCardGrades.php'=>_reportCardGrades,
						'grades/ReportCardComments.php'=>_reportCardComments,
					);

$menu['grades']['parent'] = array(
						'grades/StudentGrades.php'=>_gradebookGrades,
						'grades/FinalGrades.php'=>_finalGrades,
						'grades/ReportCards.php'=>_reportCards,
                                                'grades/ParentProgressReports.php'=>_progressReports,
						'grades/Transcripts.php'=>_transcripts,
						'grades/GPARankList.php'=>_gpaClassRank,
					);

$menu['users']['admin'] += array(
						'users/TeacherPrograms.php?include=grades/InputFinalGrades.php'=>_inputFinalGrades,
						'users/TeacherPrograms.php?include=grades/Grades.php'=>_gradebookGrades,
                                                'users/TeacherPrograms.php?include=grades/ProgressReports.php'=>_progressReports,
					);

$exceptions['grades'] = array(
						'grades/CalcGPA.php'=>true
					);
?>