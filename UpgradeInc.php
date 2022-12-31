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
error_reporting(0);

if (file_exists("Data.php"))
{
    include("Data.php");
}
if($DatabaseServer == '')
{
    // redirect user to the install procedure
    header('Location: install/index.php');
}
else {
    // Server Names and Paths
    db_start();

    $sql = DBQuery("select value from app where name='build'");
    $build = $sql->fetch_assoc();
    $month = substr($build['value'],0,2);
    $day = substr($build['value'],2,2);
    $year = substr($build['value'],4,4);
    $revision = substr($build['value'],8,3);

    $build_date = mktime(0,0,0,$month,$day,$year);
    if ($build_date < mktime(0,0,0,5,28,2009))
    {
        if($revision == '000') {
            // redirect user to the upgrade procedure
            header('Location: install/index.php?upreq=true');
        }
    }
}
?>
