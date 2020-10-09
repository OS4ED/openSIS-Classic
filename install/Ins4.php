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
error_reporting(0);
session_start();
$_SESSION['admin_name'] = $_POST['auname'];
$_SESSION['admin_pwd'] = md5($_POST['apassword']);


require_once("../functions/PragRepFnc.php");
//mysql_select_db($_SESSION['db']);
$dbconn = new mysqli($_SESSION['server'],$_SESSION['username'],$_SESSION['password'],$_SESSION['db'],$_SESSION['port']);
if($dbconn->connect_errno!=0)
        {
            echo "<h2>" . $dbconn->error . "</h2>\n";
            exit;
        }
$sql="update staff set first_name='$_POST[fname]',last_name='$_POST[lname]',middle_name='$_POST[mname]', profile_id=0 where staff_id=1 ";
$result = $dbconn->query($sql);
$sql="update login_authentication set username='".$_SESSION['admin_name']."', password='".$_SESSION['admin_pwd']."' WHERE user_id=1 AND profile_id=0";
$dbconn->query($sql);
$dbconn->close();
//mysqli_close($dbconn);


header('Location: Step5.php');
?>
