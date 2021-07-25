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
// DRAWS A TABLE WITH A BLUE TAB, SURROUNDING SHADOW
// REQUIRES A TITLE
function PopTableCustom($action,$title='Search',$table_att='', $cell_padding='5')
{	global $_openSIS;
	if($action=='header')
	{
		echo "<CENTER>
			<TABLE cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><TD align=center colspan=3>";
			if(is_array($title))
				echo WrapTabs($title,$_openSIS['selected_tab']);
			else
				echo DrawTab($title);
			echo "</TD></TR>
			<TR><TD background=assets/left_shadow.gif width=4  rowspan=2>&nbsp;</TD><TD background=assets/bottom.gif height=7></TD><TD background=assets/right_shadow.gif width=4  rowspan=2></TD></TR><TR><TD bgcolor=white>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 width=100%><tr><td bgcolor=white>";
	}
	elseif($action=='footer')
	{
		// Close embeded table.
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD>
		</TR>
		<TR>
			<TD background=assets/left_corner_shadow.gif height=6 width=4></TD>
			<TD background=assets/bottom_shadow.gif height=6></TD>
			<TD height=6 width=4 background=assets/right_corner_shadow.gif></TD>
		</TR></TABLE></CENTER>";
	}
}
?>