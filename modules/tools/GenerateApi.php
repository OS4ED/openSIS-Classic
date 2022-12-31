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

include('../../RedirectModulesInc.php');

if($_REQUEST['modfunc']=='generate')
{
    do {
    $api_key=generateAPI();
    $api_secret=generateSecret();
    $check=DBGet(DBQuery('SELECT COUNT(1) as REC_EX FROM api_info WHERE API_KEY=\''.$api_key.'\' AND API_SECRET=\''.$api_secret.'\' '));
    }while($check[1]['REC_EX']!=0);
       
    DBQuery('INSERT INTO api_info (API_KEY,API_SECRET) VALUES (\''.$api_key.'\',\''.$api_secret.'\')');
}    
if($_REQUEST['modfunc']=='remove')
{
    
    DBQuery('DELETE FROM api_info WHERE ID='.$_REQUEST['id']);
}    
PopTable('header',  _apiToken);
$get_token=DBGet(DBQuery('SELECT * FROM api_info'));
$columns=array('API_KEY'=>_key,'API_SECRET'=>_secret);
$link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
$link['remove']['variables'] = array('id' => 'ID');
ListOutput($get_token, $columns, _token, _tokens,$link);

echo '<br><br><div class="text-center"><a class="btn btn-primary" href="Modules.php?modname='.$_REQUEST['modname'].'&modfunc=generate" onclick="grabA(this); return false;">'._generate.'</a></div>';
function generateAPI() 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    
}
function generateSecret() 
{
   
    return md5(UserSchool().rand(10,9999).rand(88,88889));
    
}

?>