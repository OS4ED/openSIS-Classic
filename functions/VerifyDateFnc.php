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

function VerifyDate($date)
{
	$vdate = explode("-", $date);
	if (count($vdate)) {
		$day = $vdate[0];
		$month = MonthNWSwitch($vdate[1], 'tonum');
		$year = $vdate[2];
		$e_date = '01-' . $month . '-' . $year;
		$num_days = date('t', strtotime($e_date));
		if ($num_days < $day) {
			return false;
		}
	} else {
		return false;
	}

	// in the < 8 php if you pass a string value for the int argument but string value can be converted in int that will not give you an error but in php 8 you have to pass int type value for int argument
	// note - default variable type in php is " string " type 
	return checkdate(intval($month), intval($day), intval($year));
}
