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
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
// include('functions/SqlSecurityFnc.php');

if($_REQUEST['title'])
{
    $cp_id = sqlSecurityFilter($_REQUEST['course_period_id']);

    if($_REQUEST['course_period_id'])
    {
    $_SESSION['MassDrops.php']['course_period_id']=$_REQUEST['course_period_id'];
    $gender_res = DBGet(DBQuery('SELECT GENDER_RESTRICTION FROM course_periods WHERE COURSE_PERIOD_ID='.$cp_id));
    $_SESSION['MassDrops.php']['gender'] = $gender_res[1]['GENDER_RESTRICTION'];
//        $_REQUEST['title'] = str_replace('"', '\"', $_REQUEST['title']);
        if ($gender_res[1]['GENDER_RESTRICTION'] != 'N')
        $_REQUEST['title']=$_REQUEST['title'].' - Gender : '.($gender_res == 'M' ? 'Male' : 'Female');
    }
    if($_REQUEST['course_id'])
    $_SESSION['MassDrops.php']['course_id']=$_REQUEST['course_id'];
    if($_REQUEST['subject_id'])
    $_SESSION['MassDrops.php']['subject_id']=$_REQUEST['subject_id'];
    
    echo $_REQUEST['title'];
    
}
?>
