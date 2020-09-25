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
	include('RedirectRootInc.php'); 
	include'ConfigInc.php';
        include("Warehouse.php");
// include('functions/SqlSecurityFnc.php');

$marking_period = sqlSecurityFilter($_GET['u']);
                $get_schoolname = DBGet(DBQuery("SELECT school_name FROM  history_marking_periods  WHERE marking_period_id = $marking_period"));
        if($get_schoolname[1]['school_name'])
            echo $get_schoolname[1]['school_name'];
        else
        {
             $get_schoolid = DBGet(DBQuery("SELECT school_id FROM  marking_periods  WHERE marking_period_id = $marking_period"));
             if($get_schoolid[1]['school_id'])
             {
                $get_schoolid = DBGet(DBQuery("SELECT title FROM  schools  WHERE id = $get_schoolid[1][school_id]")); 
                 echo $get_schoolid[1]['title'];
             }
        }

?>
