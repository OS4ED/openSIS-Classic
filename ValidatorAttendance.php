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

	include('RedirectRootInc.php');
	include'ConfigInc.php';
        include("Warehouse.php");
       $atten_chk = strip_tags(trim($_GET['u']));
       $period_id = strip_tags(trim($_GET['p_id']));
    $cp_id=strip_tags(trim($_REQUEST['cp_id']));
    if($period_id==0 && $cp_id!='new')
    {

        $chk_attendance  =DBGet(DBQuery("SELECT PERIOD_ID FROM course_period_var cpv WHERE COURSE_PERIOD_ID='". $cp_id."'"));
        $period_id=$chk_attendance[1]['PERIOD_ID'];
    }
   if($period_id!='')
        {
    $chk_attendance = DBGet(DBQuery("SELECT ATTENDANCE FROM school_periods WHERE PERIOD_ID='". $period_id."'"));
    $chk_atten_cp=DBGet(DBQuery("SELECT DOES_ATTENDANCE FROM course_period_var WHERE COURSE_PERIOD_ID='". $cp_id."'"));

    $attendance = $chk_attendance[1]['ATTENDANCE'];
    if(($attendance!='Y' && $atten_chk=='Y') || ($attendance!='Y' && $chk_atten_cp['DOES_ATTENDANCE']=='Y' && $atten_chk=='N'))
    {   if(strip_tags(trim($_GET['ids']))=='')
       echo '0';
        else
        echo '0/'.$_GET['ids'];
       }
    else
        {
        echo '1';
    }
    exit;
    }
	
?>