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
function WrapTabs($tabs,$selected='',$title='',$use_blue=false,$type='')
{
	if($color == '' || $color == '#FFFFFF')
		$color = "#FFFFCC";
	$row = 0;
     	$characters = 0;
	//$rows[0] = "<TABLE border=0 cellpadding=0 cellspacing=0 class=><TR>";
	if(count($tabs))
	{
            $rows[$row] .= '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom">';
		foreach($tabs as $tab)
		{
			if(substr($tab['title'],0,1)!='<')
				$tab_len = strlen($tab['title']);
			else
				$tab_len = 0;

			if($characters + $tab_len >= 180)
			{
				$rows[$row] .= "";
				$row++;
				$rows[$row] .= "";
				$characters = 0;
			}

			if($tab['link']==PreparePHP_SELF() || $tab['link']==$selected)
				$rows[$row] .= DrawTab($tab['title'],$tab['link'],'#333366','#436477',$type);
			elseif($use_blue!==true)
				$rows[$row] .= DrawinactiveTab($tab['title'],$tab['link'],'#DDDDDD','#000000',$type);
			else
				$rows[$row] .= DrawinactiveTab($tab['title'],$tab['link'],'#333366','#f2a30b',$type);

			$characters += $tab_len + 6;
		}
                $rows[$row] .= '</ul>';
	}
	//$rows[$row] .= "</TR>\n</TABLE>\n\n";

	$i = 0;
	$row_count = count($rows) - 1;
	/*if($use_blue===true)
		$table .= "<TABLE border=0 width=100% cellpadding=0 cellspacing=0 ><TR><TD width=100%></TD><TD align=right>";
	elseif($use_blue=='center')
		$table .= "<TABLE border=0 width=100% cellpadding=0 cellspacing=0  align=center><TR><TD align=center>";*/

	for($key=$row_count;$key>=0;$key--)
	{
		//if(!ereg("<!--BOTTOM-->",$rows[$key]))
            if(!preg_match("<!--BOTTOM-->",$rows[$key]))
		{
			//$table .= "<TABLE border=0 width=0 cellpadding=0 cellspacing=0 ><TR><TD>";
			//$table .= "<IMG SRC=assets/pixel_trans.gif width=" . (($row_count-$i)*6) . " height=1>";
			if($key != 0 || $bottom)
				$table .= $rows[$key];
			else
				$table .= $rows[$key];
			$i++;
		}
		else
			$bottom = $key;
	}
	$table .= $rows[$bottom];
	/*if($use_blue)
		$table .= "</TD></TR><TR><TD colspan=2>";*/

	if($title!='')
		$table .= $title;

	/*if($use_blue)
		$table .= "</TD></TR></TABLE>";*/

	return $table;
}
?>