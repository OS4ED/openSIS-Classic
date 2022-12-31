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
include 'ConfigInc.php';
include 'Warehouse.php';

$flag = $_GET['u'];
$usr = substr($flag, -4);
$userid = $_GET['userid'];
$profileid = $_GET['profileid'];

// ------------------------ For Unique Checking ---------------------------------- //
$un = substr($flag, 0, -4);
$un = strtoupper($un);
// ------------------------ For Unique Checking ---------------------------------- //

switch ($_GET['validate'])
{
    case 'pass':
		$res_pass_chk = DBQuery("SELECT * FROM login_authentication WHERE password = '".md5($_GET['password'])."' AND user_id!='".$_GET['stfid']."' AND profile_id=0");
		$num_pass = $res_pass_chk->num_rows;
        if($num_pass==0)
        {
            echo 1;
            
        }
        break;
        
	case 'pass_o':
        $res_pass_chk = DBQuery("SELECT * FROM login_authentication WHERE password = '".md5($_GET['password'])."'  AND profile_id=0");
        $num_pass = $res_pass_chk->num_rows;
        if($num_pass==0)
        {
            echo '1_'.$_GET['opt'];
            
        }
        else
        {
            echo '0_'.$_GET['opt'];
        }
		break;
        
	default :    
		if($usr == 'user')
		{
			if (trim($userid) != '')
				$result = DBGet(DBQuery("SELECT username FROM login_authentication WHERE NOT(user_id = '" . $userid . "' AND profile_id = '".$profileid."')"));
			else
				$result = DBGet(DBQuery("SELECT username FROM login_authentication"));
			 
			$xyz = 0;
			foreach ($result as $k => $v) 
			{
			  	$unames[$xyz] = strtoupper($v['USERNAME']); // For Unique Checking.
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
		else
		{
			if (trim($userid) != '')
				$result = DBGet(DBQuery("SELECT username FROM login_authentication WHERE NOT(user_id = '" . $userid . "' AND profile_id = '".$profileid."')"));
			else
				$result = DBGet(DBQuery("SELECT username FROM login_authentication"));
			
			$xyz = 0;
			foreach ($result as $k => $v) 
			{
			  	$unames[$xyz] = strtoupper($v['USERNAME']); // For Unique Checking.
			  	$xyz++;
			}
		
			if ($un != '') 
			{
				if(is_array($unames))
				{
					if (in_array ($un, $unames)) 
					{
						echo '0';
					} 
					else 
					{
						echo '1';
					}
				} else {
					echo '1';
				}

				exit;
			}
		}
		break;
}

?>
