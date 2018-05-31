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
class custom{
var $customQuery=array();
var $customQueryString=array();
function __construct($mysql_database){
//mysql_connect($_SESSION['server'],$_SESSION['username'],$_SESSION['password']) or die() ;
//mysql_select_db($mysql_database);
    $dbconncus = new mysqli($_SESSION['server'],$_SESSION['username'],$_SESSION['password'],$mysql_database,$_SESSION['port']);    
}
function set($res,$table,$mysql_database){
        $dbconncus = new mysqli($_SESSION['server'],$_SESSION['username'],$_SESSION['password'],$mysql_database,$_SESSION['port']);    
    
	$res=$dbconncus->query($res)  or die($dbconncus->error.' at line CustomClass 6 12');

	while($row=$res->fetch_assoc())
	{	
	 $this->customQuery[]=$row ;
	}
	
	foreach($this->customQuery as $value){
	$str="ALTER TABLE $table ADD $value[Field] $value[Type]";
	if($value['Null']=='YES'){
	$str.=" NULL ";
	}else if($value['Null']=='NO'){
	$str.=" NOT NULL ";
	}
	if($value['Default']){
	$str.=" DEFAULT '".$value['Default']."' ";
	}
	$this->customQueryString[]=$str;
	}
	
}
 function __destruct() {
      $dbconncus->close();
   }
}

 ?>