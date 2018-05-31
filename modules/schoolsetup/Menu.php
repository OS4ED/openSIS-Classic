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
$menu['schoolsetup']['admin'] = array(
						'schoolsetup/PortalNotes.php'=>'Portal Notes',
						'schoolsetup/MarkingPeriods.php'=>'Marking Periods',
						'schoolsetup/Calendar.php'=>'Calendars',
						'schoolsetup/Periods.php'=>'Periods',
						'schoolsetup/GradeLevels.php'=>'Grade Levels',
                                                'schoolsetup/Sections.php'=>'Sections',
                                                'schoolsetup/Rooms.php'=>'Rooms',
                         1=>'School',
                        'schoolsetup/Schools.php'=>'School Information',
						'schoolsetup/Schools.php?new_school=true'=>'Add a School',
						'schoolsetup/CopySchool.php'=>'Copy School',
						'schoolsetup/SystemPreference.php'=>'System Preference',
                                                'schoolsetup/SchoolCustomFields.php'=>'School Custom Fields',
                         2=>'Courses',
                        'schoolsetup/Courses.php'=>'Course Manager',
                        'schoolsetup/CourseCatalog.php'=>'Course Catalog',
                        'schoolsetup/PrintCatalog.php'=>'Print Catalog by Term', 
                        'schoolsetup/PrintCatalogGradeLevel.php'=>'Print Catalog by Grade Level', 
                        'schoolsetup/PrintAllCourses.php'=>'Print all Courses',
                        'schoolsetup/TeacherReassignment.php'=>'Teacher Re-Assignment'
              );

$menu['schoolsetup']['teacher'] = array(
						'schoolsetup/Schools.php'=>'School Information',
						'schoolsetup/MarkingPeriods.php'=>'Marking Periods',
						'schoolsetup/Calendar.php'=>'Calendar',
						1=>'Courses',
                        'schoolsetup/Courses.php'=>'Course Manager',
                        'schoolsetup/CourseCatalog.php'=>'Course Catalog',
                        'schoolsetup/PrintCatalog.php'=>'Print Catalog by Term', 
                        'schoolsetup/PrintAllCourses.php'=>'Print all Courses'
					);

$menu['schoolsetup']['parent'] = array(
						'schoolsetup/Schools.php'=>'School Information',
						'schoolsetup/Calendar.php'=>'Calendar'
					);

$exceptions['schoolsetup'] = array(
						'schoolsetup/PortalNotes.php'=>true,
						'schoolsetup/Schools.php?new_school=true'=>true,
						'schoolsetup/Rollover.php'=>true
					);
?>
