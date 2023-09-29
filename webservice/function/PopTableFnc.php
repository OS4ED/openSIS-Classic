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

function PopTable($action,$title='Search',$table_att='', $cell_padding='5')
{	global $_openSIS;

	if($action=='header')
	{
		echo "
			<TABLE cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><td width=9px></td><TD align=left class=block_stroke >";
			if(is_array($title))
				echo WrapTabs($title,$_openSIS['selected_tab']);
			else
				echo DrawTab($title);
				echo " </td><td></td></tr><TR>
			<TD class=block_topleft_corner></TD>
			<TD class=block_topmiddle></TD>
			<TD class=block_topright_corner></TD>
		</TR>";
			echo "
			<TR><TD class=block_left rowspan=2></TD><TD class=block_bg></TD><TD class=block_right rowspan=2></TD></TR><TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 width=100% class=block_bg><tr><td class=block_bg>";
	}
	elseif($action=='footer')
	{
		// Close embeded table.
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD>
		</TR>
		<TR>
			<TD class=block_left_corner></TD>
			<TD class=block_middle></TD>
			<TD class=block_right_corner></TD>
		</TR>
		<tr><td colspan=3 class=clear></td></tr>
		</TABLE>";
	}
}



function PopTable_wo_header($action,$title='Search',$table_att='', $cell_padding='5')
{	global $_openSIS;

	if($action=='header')
	{
		echo "
			<TABLE cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><td width=9px></td><TD align=left class=block_stroke >";
			if(is_array($title))
				echo '';
			else
				echo '';
				echo " </td><td></td></tr><TR>
			<TD class=block_topleft_corner></TD>
			<TD class=block_topmiddle></TD>
			<TD class=block_topright_corner></TD>
		</TR>";
			echo "
			<TR><TD class=block_left rowspan=2></TD><TD class=block_bg></TD><TD class=block_right rowspan=2></TD></TR><TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 width=100%><tr><td class=block_bg>";
	}
	elseif($action=='footer')
	{
		// Close embeded table.
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD>
		</TR>
		<TR>
			<TD class=block_left_corner></TD>
			<TD class=block_middle></TD>
			<TD class=block_right_corner></TD>
		</TR>
		<tr><td colspan=3 class=clear></td></tr>
		</TABLE>";
	}
}

function PopTable_wo_header_attn_code($action,$title='Search',$table_att='', $cell_padding='5')
{	global $_openSIS;

	if($action=='header')
	{
		echo "
			<TABLE cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><td width=9px></td><TD align=left class=block_stroke >";
			if(is_array($title))
				echo WrapTabs($title,"Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."&table=".strip_tags(trim($_REQUEST[table]))."");
			else
				echo '';
				echo " </td><td></td></tr><TR>
			<TD class=block_topleft_corner></TD>
			<TD class=block_topmiddle></TD>
			<TD class=block_topright_corner></TD>
		</TR>";
			echo "
			<TR><TD class=block_left rowspan=2></TD><TD class=block_bg></TD><TD class=block_right rowspan=2></TD></TR><TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 width=100%><tr><td class=block_bg>";
	}
	elseif($action=='footer')
	{
		// Close embeded table.
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD>
		</TR>
		<TR>
			<TD class=block_left_corner></TD>
			<TD class=block_middle></TD>
			<TD class=block_right_corner></TD>
		</TR>
		<tr><td colspan=3 class=clear></td></tr>
		</TABLE>";
	}
}

function PopTable_grade_header($action,$title='Search',$table_att='', $cell_padding='5')
{	global $_openSIS;

	if($action=='header')
	{
		echo "
			<TABLE width='370px' cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><td width=9px></td><TD align=left class=block_stroke >";
			if(is_array($title))
				echo '';
			else
				echo '';
				echo " </td><td></td></tr><TR>
			<TD class=block_topleft_corner></TD>
			<TD class=block_topmiddle></TD>
			<TD class=block_topright_corner></TD>
		</TR>";
			echo "
			<TR><TD class=block_left rowspan=2></TD><TD class=block_bg></TD><TD class=block_right rowspan=2></TD></TR><TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 width=100%><tr><td class=block_bg>";
	}
	elseif($action=='footer')
	{
		// Close embeded table.
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD>
		</TR>
		<TR>
			<TD class=block_left_corner></TD>
			<TD class=block_middle></TD>
			<TD class=block_right_corner></TD>
		</TR>
		<tr><td colspan=3 class=clear></td></tr>
		</TABLE>";
	}
}



function PopTableMod($action,$title='Search',$table_att='', $cell_padding='0')
{	global $_openSIS;

	if($action=='header')
	{
		echo "
			<TABLE cellpadding=0 cellspacing=0 width=786px align=center border=0 $table_att>";

			echo "<TR><TD width=786px>";
			
			echo "</TD></TR>
			<TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 ><tr><td >
		<div class=inside_block_top_closed></div>		
        <div class='content_block'>";

	}
	elseif($action=='footer')
	{
		// Close embeded table. 
		
		echo "</div><div class='content_bottom'></div>";
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD></TR></TABLE></CENTER>";
	}
}





function PopTableWindow($action,$title='Search',$table_att='', $cell_padding='0')
{	global $_openSIS;

	if($action=='header')
	{
		echo "<CENTER>
			<TABLE align=left cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><TD >";
			if(is_array($title))
				echo WrapTabs($title,$_openSIS['selected_tab']);
			else
				echo DrawTab($title);
			echo "</TD></TR>
			<TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 ><tr><td width=10></td><td >
		<div class='inside_block_top'></div>
        <div class='content_block'>";

	}
	elseif($action=='footer')
	{
		// Close embeded table.
		
		echo "</div><div class='content_bottom'></div>";
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD></TR></TABLE></CENTER>";
	}
}



function PopTableforWindow($action,$title='Search',$table_att='', $cell_padding='0')
{	global $_openSIS;

	if($action=='header')
	{
		echo "<CENTER>
			<TABLE align=left cellpadding=0 cellspacing=0 $table_att>";

			echo "<TR><TD >";
			if(is_array($title))
				echo WrapTabs($title,$_openSIS['selected_tab']);
			else
				
			echo "</TD></TR>
			<TR><TD>";

		// Start content table.
		echo "<TABLE cellpadding=".$cell_padding." cellspacing=0 ><tr><td width=10></td><td >
		<div class='inside_block_top'></div>
        <div class='content_block'>";

	}
	elseif($action=='footer')
	{
		// Close embeded table.
		
		echo "</div><div class='content_bottom'></div>";
		echo "</td></tr></TABLE>";

		// 2nd cell is for shadow.....
		echo "</TD></TR></TABLE></CENTER>";
	}
}




?>