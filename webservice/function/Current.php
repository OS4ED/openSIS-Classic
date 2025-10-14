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
function UserSchool()
{
	return $_SESSION['UserSchool'];
}

function UserSyear()
{
	return $_SESSION['UserSyear'];
}

function UserMP()
{
	return $_SESSION['UserMP'];
}

// DEPRECATED
function UserPeriod()
{
	return $_SESSION['UserPeriod'];
}
function CpvId()
{
	return $_SESSION['CpvId'];
}
function UserCoursePeriod()
{
	return $_SESSION['UserCoursePeriod'];
}

function UserSubject()
{
	return $_SESSION['UserSubject'];
}
function UserCourse()
{
	return $_SESSION['UserCourse'];
}


function UserStudentIDWs()
{
	if(UserWs('PROFILE')=='student')
	return $_SESSION['STUDENT_ID'];
	else
	return $_SESSION['student_id'];
}

function UserStaffID()
{
	return $_SESSION['staff_id'];
}

function UserID()
{
	return $_SESSION['STAFF_ID'];
}

function GetMPId($mp)
{	

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}

	$get_mp_id=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
        if($get_mp_id[1]['MARKING_PERIOD_ID']!='')
            return $get_mp_id[1]['MARKING_PERIOD_ID'];
        else
           return UserMP ();
}

function GetCpDet($cp_id,$key)
{	
    if($key!='' && $cp_id!='')
    $get_det=DBGet(DBQuery('SELECT '.strtoupper($key).' FROM course_periods WHERE COURSE_PERIOD_ID='.$cp_id));
    return $get_det[1][strtoupper($key)];
}

?>