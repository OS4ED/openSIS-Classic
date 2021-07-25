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
function DrawBlock($title,$content,$tabcolor='#333366',$textcolor='#FFFFFF')
{	global $user_id,$wstation,$DatabaseType,$block_table,$global_block_id;
	$block_table = "";
	$block_table .= "<center><TABLE border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR><TD>";

	$block_table .= DrawTab($title);
	$block_table .= "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"1\" class=\"Box\">";
	$block_table .= "  <tr>";
	$block_table .= "    <td><table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" class=\"BoxContents\">";
	$block_table .= "  <tr>";
	$block_table .= "    <td><img src=\"assets/pixel_trans.gif\" border=\"0\" alt=\"\" width=\"100%\" height=\"1\"></td>";
	$block_table .= "  </tr>";
	$block_table .= "  <tr>";
	$block_table .= "    <td class=\"boxText\" align=left>";
	$block_table .= $content;
	$block_table .= "</td>";
	$block_table .= "  </tr>";
	$block_table .= "  <tr>";
	$block_table .= "    <td><img src=\"assets/pixel_trans.gif\" border=\"0\" alt=\"\" width=\"100%\" height=\"1\"></td>";
	$block_table .= "  </tr>";
	$block_table .= "</table>";
	$block_table .= "</td>";
	$block_table .= "  </tr>";
	$block_table .= "</table>\n\n";
	$block_table .= "</TD></TR></TABLE></centers>";
	echo $block_table;
}

?>