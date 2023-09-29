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

include 'RedirectRootInc.php';
include 'Warehouse.php';
include 'Data.php';

if (isset($_REQUEST['down_id']))
    $_REQUEST['down_id'] = sqlSecurityFilter($_REQUEST['down_id']);
if (isset($_REQUEST['filename']))
    $_REQUEST['filename'] = sqlSecurityFilter($_REQUEST['filename']);

if(isset($_REQUEST['down_id']) && $_REQUEST['down_id']!='')
{
    if ((isset($_REQUEST['studentfile']) && $_REQUEST['studentfile'] == 'Y') || (isset($_REQUEST['userfile']) && $_REQUEST['userfile'] == 'Y'))
        $downfile_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE id=\'' . $_REQUEST['down_id'] . '\''));
    else
        $downfile_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE download_id=\'' . $_REQUEST['down_id'] . '\''));
    header("Cache-Control: public");
    header("Pragma: ");
    header("Expires: 0"); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Cache-Control: private",false); // required for certain browsers 
    header("Content-Length: ".$downfile_info[1]['SIZE']."");
    header("Content-Type: ".$downfile_info[1]['TYPE']."");
    // header("Content-Disposition: attachment; filename=\"".str_replace(' ','_',$downfile_info[1]['NAME'])."\";");
    // header("Content-Disposition: attachment; filename=\"".$downfile_info[1]['NAME']."\";");
    header("Content-Disposition: attachment; filename=\"".str_replace("opensis_space_here", " ", str_replace($downfile_info[1]['USER_ID']."-","",$downfile_info[1]['NAME']))."\";");
    header("Content-Transfer-Encoding: binary");
    ob_clean();
    flush();

    if(isset($_REQUEST['studentfile']) && $_REQUEST['studentfile']=='Y')
    {
        $filedata = @file_get_contents('assets/studentfiles/'.$downfile_info[1]['NAME']);
        echo $filedata;
    }
    else if(isset($_REQUEST['userfile']) && $_REQUEST['userfile']=='Y')
    {
        $filedata = @file_get_contents('assets/stafffiles/'.$downfile_info[1]['NAME']);
        echo $filedata;
    }
    else
    {
        echo $downfile_info[1]['CONTENT'];
    }
    
    exit;
}
else
{
    header('Content-Disposition: attachment; filename="'.urldecode($_REQUEST['name']).'" ');
    readfile('assets/'.urldecode($_REQUEST['filename']));
}
?>
