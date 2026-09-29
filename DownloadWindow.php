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

// Prevent Directory Traversal by sanitizing filename parameters
if (User('PROFILE') == 'student')
    $user_id = (int) UserStudentID();
else
    $user_id = (int) UserID();

function sanitize_filename($filename) {
    // Fully decode URL encoding to handle double/triple encoding attacks (e.g., %252e%252e%252f)
    // Loop until no more decoding occurs
    $prev = '';
    while ($prev !== $filename) {
        $prev = $filename;
        $filename = urldecode($filename);
    }
    // Remove null bytes
    $filename = str_replace(chr(0), '', $filename);
    // Extract only the base filename - strips ALL path components including ../
    $filename = basename($filename);
    // Remove any remaining unwanted characters, allow only safe chars
    $filename = preg_replace('/[^A-Za-z0-9_\.\-\s]/', '', $filename);
    return $filename;
}

// You can use both functions for $_REQUEST['filename'].
// First, apply sqlSecurityFilter, then sanitize_filename for maximum safety.
if (isset($_REQUEST['filename'])) {
    $_REQUEST['filename'] = sqlSecurityFilter($_REQUEST['filename']);
    $_REQUEST['filename'] = sanitize_filename($_REQUEST['filename']);
}
if (isset($_REQUEST['name'])) {
    $_REQUEST['name'] = sanitize_filename($_REQUEST['name']);
}


if (isset($_REQUEST['down_id']))
    $_REQUEST['down_id'] = sqlSecurityFilter($_REQUEST['down_id']);
// if (isset($_REQUEST['filename']))
//     $_REQUEST['filename'] = sqlSecurityFilter($_REQUEST['filename']);

if(isset($_REQUEST['down_id']) && $_REQUEST['down_id']!='')
{
    if (isset($_REQUEST['down_id']) && $_REQUEST['down_id'] != ''){
        $downfile_info = 'SELECT * FROM user_file_upload WHERE download_id=\'' . $_REQUEST['down_id'] . '\'';
    if(User('PROFILE')!='admin'){
        $downfile_info .= ' AND user_id='.$user_id.'';
    }
    $downfile_info = DBGet(DBQuery($downfile_info));
    }
    else{
        $downfile_info = 'SELECT * FROM user_file_upload WHERE id=\'' . $_REQUEST['down_id'] . '\'';
        if(User('PROFILE')!='admin'){
        $downfile_info .= ' AND user_id='.$user_id.'';
    }
    $downfile_info = DBGet(DBQuery($downfile_info));
    }
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
     // header('Content-Disposition: attachment; filename="'.$_REQUEST['name'].'" ');
    // readfile('assets/'.urldecode($_REQUEST['filename']));
    
    // basename() extracts just the filename component, effectively preventing directory traversal,
    // while urldecode() translates URL-encoded characters back to their original representation.
    // Yes, using $filename = basename(urldecode($_REQUEST['filename'])); is correct security practice.
    // urldecode decodes any encoded characters such as %2e or %2f,
    // then basename strips any remaining path information, preventing directory traversal.
    // This way, even if an attacker tries to bypass checks with encoded input, the final filename is safe to use.
    $filename = $_REQUEST['filename'];
    $filepath = 'assets/' . $filename;

    if (!file_exists($filepath)) {
        http_response_code(404);
        exit('File not found or invalid filename.');
    }
    // Also sanitize the output filename for header, just in case
    $output_name = basename($_REQUEST['name']);
    header('Content-Disposition: attachment; filename="' . $output_name . '"');
    readfile($filepath);
}
?>
