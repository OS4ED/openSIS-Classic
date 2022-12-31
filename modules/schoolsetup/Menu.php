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
#**************************************************************************************
include('../../RedirectModulesInc.php');
$menu['schoolsetup']['admin'] = array(
						'schoolsetup/PortalNotes.php'=>_portalNotes,
						'schoolsetup/MarkingPeriods.php'=>_markingPeriods,
						'schoolsetup/Calendar.php'=>_calendars,
						'schoolsetup/Periods.php'=>_periods,
						'schoolsetup/GradeLevels.php'=>_gradeLevels,
                                                'schoolsetup/Sections.php'=>_sections,
                                                'schoolsetup/Rooms.php'=>_rooms,
                         1=>_school,
                        'schoolsetup/Schools.php'=>_schoolInformation,
						'schoolsetup/Schools.php?new_school=true'=>_addASchool,
						'schoolsetup/CopySchool.php'=>_copySchool,
						'schoolsetup/SystemPreference.php'=>_systemPreference,
                                                'schoolsetup/SchoolCustomFields.php'=>_schoolCustomFields,
                         2=>_courses,
                        'schoolsetup/Courses.php'=>_courseManager,
                        'schoolsetup/CourseCatalog.php'=>_courseCatalog,
						'schoolsetup/PrintCatalog.php'=>_printCatalogByTerm,
						'schoolsetup/PrintCatalogGradeLevel.php'=>_printCatalogByGradeLevel,
						'schoolsetup/PrintAllCourses.php'=>_printAllCourses,
                        'schoolsetup/TeacherReassignment.php'=>_teacherReAssignment
              );

$menu['schoolsetup']['teacher'] = array(
						'schoolsetup/Schools.php'=>_schoolInformation,
						'schoolsetup/MarkingPeriods.php'=>_markingPeriods,
						'schoolsetup/Calendar.php'=>_calendar,
						1=>_courses,
                        'schoolsetup/Courses.php'=>_courseManager,
                        'schoolsetup/CourseCatalog.php'=>_courseCatalog,
						'schoolsetup/PrintCatalog.php'=>_printCatalogByTerm,
						'schoolsetup/PrintAllCourses.php'=>_printAllCourses
					);

$menu['schoolsetup']['parent'] = array(
						'schoolsetup/Schools.php'=>_schoolInformation,
						'schoolsetup/Calendar.php'=>_calendar
					);

$exceptions['schoolsetup'] = array(
						'schoolsetup/PortalNotes.php'=>true,
						'schoolsetup/Schools.php?new_school=true'=>true,
						'schoolsetup/Rollover.php'=>'tru'
					);
