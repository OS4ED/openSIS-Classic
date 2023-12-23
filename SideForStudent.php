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
session_start();
unset($_SESSION['student_id']);
unset($_SESSION['students_order']);
unset($_SESSION['_REQUEST_vars']);
unset($_SESSION['_REQUEST_vars']);
$request_modname = explode("/",$_REQUEST['modname']);
foreach ($request_modname as $mod_key => $mod_val) {
	$request_modname[$mod_key] = strip_tags(urlencode(trim($mod_val)));
}
$filtered_modname = implode("/", $request_modname);

echo "<script>window.location.href='Modules.php?modname=".$filtered_modname."&ajax=true';</script>";


?>
