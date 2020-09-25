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
// config variables for InputFinalGrades.php
include('../../RedirectModulesInc.php');
if(1)
$commentsA_select = array('+'=>array('+','<img src=assets/plus.gif>',_exceptional),
			  ' '=>array('&middot;','<img src=assets/dot.gif>',_satisfactory),
			  '-'=>array('-','<img src=assets/minus.gif>',_needsImprovement));
else
$commentsA_select = array('1'=>array('1 - '._excellent.'',_excellent,_excellent),
			  '2'=>array('2 - '._veryGood.'',_veryGood,_veryGood),
			  '3'=>array('3 - '._good.'',_good,_good),
			  '4'=>array('4 - '._fair.'',_fair,_fair),
			  '5'=>array('5 - '._poor.'',_poor,_poor),
			  '6'=>array('6 - '._failure.'...',''._failure.'...',f_ailureDueToPoorAttendance));

// config variables for StudentGrades.php
// set this to false to disable anonamous grade statistics for parents and students
$do_stats = true;
// remove this line if you don't want teachers to have 'em either
$do_stats |= User('PROFILE')=='teacher';
?>