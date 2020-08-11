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
#  See License.txt.
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
	include('RedirectRootInc.php'); 
	include 'Warehouse.php';
	include 'Data.php';
	$v_year = $_SESSION['UserSyear'];

	$flag = $_GET['u'];
	$usr = substr($flag, -4);
	$un = substr($flag, 0, -4);
	if($usr == 'stid')
	{
		$result = DBGet(DBQuery("select s.student_id from students s, student_enrollment se where s.student_id = se.student_id and se.syear = $v_year"));
		
		$xyz = 0;
		foreach ($result as $row)  
		{
		  $unames[$xyz] = $row[0];
		  $xyz++;
		}
	
		if ($un != '') 
		{
			if (in_array ($un, $unames)) 
			{
				echo '0';
			} 
			else 
			{
				echo '1';
			}
			exit;
		}
	}
?>