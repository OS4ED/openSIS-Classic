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
function UpdatePullDown($form,$select,$values,$id,$text)
{
	$array = "new Array(";
		// PREPARE JS ARRAY OF TEXT VALUES
	foreach($values as $category_id=>$category)
	{
		$array .= "new Array(";
		foreach($category as $value)
			$array .= "'$value[$text]',";
		$array = substr($array,0,-1).'),';
	}
	$array = substr($array,0,-1).')';
		// PREPARE JS ARRAY OF THE IDS
	$ids_array = "new Array(";
	foreach($values as $category)
	{
		$ids_array .= "new Array(";
		foreach($category as $value)
			$ids_array .= "'$value[$id]',";
		$ids_array = substr($ids_array,0,-1).'),';
	}		
	$ids_array = substr($ids_array,0,-1).')';
	
	return "onChange=\"javascript:updatePullDown(window.document.$form.$select,this.selectedIndex,$array,$ids_array);\"";
}
?>