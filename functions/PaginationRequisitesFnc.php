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

function setPaginationRequisites($modname, $searchModfunc, $nextModname, $columns, $singular, $plural, $link, $LOGroup, $options, $ListOutputFunction, $ProgramTitle = false) {

	if($ListOutputFunction != '') {
		$pagi_mods = array();

		if($modname != '') {
			$pagi_mods['modname'] = $modname;
		}

		if($searchModfunc != '') {
			$pagi_mods['search_modfunc'] = $searchModfunc;
		}

		if($nextModname != '') {
			$pagi_mods['next_modname'] = $nextModname;
		}

		unset($_SESSION['PEGI_MODS']);
		$_SESSION['PEGI_MODS'] = $pagi_mods;

		unset($_SESSION['PEGI_COLS']);
		$_SESSION['PEGI_COLS'] = $columns;

		unset($_SESSION['PEGI_SINGULAR']);
		$_SESSION['PEGI_SINGULAR'] = $singular;

		unset($_SESSION['PEGI_PLURAL']);
		$_SESSION['PEGI_PLURAL'] = $plural;

		unset($_SESSION['PEGI_LINK']);
		$_SESSION['PEGI_LINK'] = $link;

		unset($_SESSION['PEGI_LOGRP']);
        $_SESSION['PEGI_LOGRP'] = $LOGroup;

        unset($_SESSION['PEGI_OPTION']);
        $_SESSION['PEGI_OPTION'] = $options;

        unset($_SESSION['LISTOUTPUT_FUNC']);
        $_SESSION['LISTOUTPUT_FUNC'] = $ListOutputFunction;

        unset($_SESSION['PROGRAM_TITLE']);
        if($ProgramTitle != '') {
        	$_SESSION['PROGRAM_TITLE'] = $ProgramTitle;
        } else {
        	$_SESSION['PROGRAM_TITLE'] = 'openSIS';
        }
	}
}

function keepRequestParams($requests) {
	unset($_SESSION['PEGI_REQUESTS']);

	$_SESSION['PEGI_REQUESTS'] = array();
	
	$_SESSION['PEGI_REQUESTS'] = $requests;
}

function keepExtraParams($extra) {
	unset($_SESSION['PEGI_EXTRA']);
	
	$_SESSION['PEGI_EXTRA'] = $extra;
}

function checkPagesForPrint($modname) {
	if($modname != '') {
		$pagesForPrint = array(
	        // Students
	        'students/MailingLabels.php', 
	        'students/StudentLabels.php', 
	        'students/PrintStudentInfo.php', 
	        'students/PrintStudentContactInfo.php', 
	        'students/GoalReport.php', 
	        // Scheduling
	        'scheduling/PrintSchedules.php', 
	        // Grades
	        'grades/ReportCards.php', 
	        'grades/AdminProgressReports.php', 
	        'grades/ProgressReports.php', 
	        'grades/Transcripts.php'
	    );

		if(in_array($modname, $pagesForPrint)) {
			return true;
		} else {
			return false;
		}

	} else {
		return false;
	}
}

function checkNoNeedPaging($modname) {
	if($modname != '') {
		$noNeedPaging = array(
	        // Teacher Programs
	        'users/TeacherPrograms.php?include=grades/InputFinalGrades.php', 
	        'users/TeacherPrograms.php?include=grades/Grades.php', 
	        'users/TeacherPrograms.php?include=grades/ProgressReports.php', 
	        // Grades
	        'grades/ProgressReports.php', 
	        'grades/Transcripts.php', 
	        // Users
	        'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php', 
	        // Eligibility
	        'eligibility/EnterEligibility.php', 
	        // Attendance
	        'attendance/DailySummary.php'
	    );

		if(in_array($modname, $noNeedPaging)) {
			return true;
		} else {
			return false;
		}

	} else {
		return false;
	}
}

?>