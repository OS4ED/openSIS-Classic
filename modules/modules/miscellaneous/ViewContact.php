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
$person_RET = DBGet(DBQuery('SELECT FIRST_NAME,MIDDLE_NAME,LAST_NAME FROM people WHERE PERSON_ID=\''.$_REQUEST[person_id].'\''));
$contacts_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM people_join_contacts WHERE PERSON_ID=\''.$_REQUEST[person_id].'\''));
echo '<BR>';
PopTable('header',$person_RET[1]['FIRST_NAME'].' '.$person_RET[1]['MIDDLE_NAME'].' '.$person_RET[1]['LAST_NAME'],'width=75%');
if(count($contacts_RET))
{
	foreach($contacts_RET as $info)
		echo '<B>'.$info['TITLE'].'</B>: '.$info['VALUE'].'<BR>';
}
else
	echo 'This person has no information in the system.';
PopTable('footer');
?>