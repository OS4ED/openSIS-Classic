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
						'grades/ReportCards.php'=>'Report Cards',
						//'grades/CalcGPA.php'=>'Calculate GPA',
						'grades/Transcripts.php'=>'Transcripts',
						1=>'Reports',
						'grades/TeacherCompletion.php'=>'Teacher Completion',
						'grades/GradeBreakdown.php'=>'Grade Breakdown',
						'grades/FinalGrades.php'=>'Student Final Grades',
						'grades/GPARankList.php'=>'GPA / Class Rank List',
                                                'grades/AdminProgressReports.php'=>'Progress Reports',
                        'grades/HonorRoll.php'=>'Honor Roll',
						2=>'Setup',
						'grades/ReportCardGrades.php'=>'Report Card Grades',
						'grades/ReportCardComments.php'=>'Report Card Comments',
                                                'grades/HonorRollSetup.php'=>'Honor Roll Setup',
                        'grades/HonorRollSetup.php'=>'Honor Roll Setup',
						3=>'Utilities',
                                                'grades/EditReportCardGrades.php'=>'Edit Report Card Grades',
                                                'grades/EditHistoryMarkingPeriods.php'=>'Add/Edit Historical Marking Periods',
                                                'grades/HistoricalReportCardGrades.php'=>'Add/Edit Historical Report Card Grades'
					);

$menu['grades']['teacher'] = array(
						'grades/InputFinalGrades.php'=>'Input Final Grades',
						'grades/ReportCards.php'=>'Report Cards',
						1=>'Gradebook',
						'grades/Grades.php'=>'Grades',
						'grades/Assignments.php'=>'Assignments',
						'grades/AnomalousGrades.php'=>'Anomalous Grades',
						'grades/ProgressReports.php'=>'Progress Reports',
						2=>'Reports',
						'grades/StudentGrades.php'=>'Student Grades',
						'grades/FinalGrades.php'=>'Final Grades',
						3=>'Setup',
						'grades/Configuration.php'=>'Configuration',
						'grades/ReportCardGrades.php'=>'Report Card Grades',
						'grades/ReportCardComments.php'=>'Report Card Comments'
					);

$menu['grades']['parent'] = array(
						'grades/StudentGrades.php'=>'Gradebook Grades',
						'grades/FinalGrades.php'=>'Final Grades',
						'grades/ReportCards.php'=>'Report Cards',
                                                'grades/ParentProgressReports.php'=>'Progress Reports',
						'grades/Transcripts.php'=>'Transcripts',
						'grades/GPARankList.php'=>'GPA / Class Rank'
					);

$menu['users']['admin'] += array(
						'users/TeacherPrograms.php?include=grades/InputFinalGrades.php'=>'Input Final Grades',
						'users/TeacherPrograms.php?include=grades/Grades.php'=>'Gradebook Grades',
                                                'users/TeacherPrograms.php?include=grades/ProgressReports.php'=>'Progress Reports'
					);

$exceptions['grades'] = array(
						'grades/CalcGPA.php'=>true
					);
?>