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
function GetNameFromUserName($userName)
{	
    
       $q="Select * from login_authentication where username='".$userName."'";

       $userProfile=  DBGet(DBQuery($q));

       $userProfileId=$userProfile[1]['PROFILE_ID'];
       $UserId=$userProfile[1]['USER_ID'];
       if($userProfileId!=3 ||$userProfileId!=4)
       {
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from staff where profile_id='".$userProfileId."' and staff_id='".$UserId."'";
       }
       if($userProfileId==3)
       {
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from students where student_id='".$UserId."'";
       }
       if($userProfileId==4)
       {
          
           $nameQuery="Select CONCAT(first_name,' ', last_name) name from people where profile_id='".$userProfileId."' and staff_id='".$UserId."'";
       }
       $name=  DBGet(DBQuery($nameQuery));
       $name=$name[1]['NAME'];	
    return $name;
}
?>

