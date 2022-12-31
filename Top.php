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
if(!$_SESSION['STAFF_ID'] && !$_SESSION['STUDENT_ID'] && (strpos($_SERVER['PHP_SELF'],'index.php'))===false)
	{
		header('Location: index.php');
		exit;
	}
require('Warehouse.php');
$url=validateQueryString(curPageURL());
if($url===FALSE){
 header('Location: index.php');
 }

echo "<HTML><HEAD><TITLE>"._openSisSchoolSoftware."</TITLE>
<script language=javascript>
function resizeImages()
{
	var width;
	if(self.innerWidth)
		width = self.innerWidth;
	else if(document.documentElement && document.documentElement.clientWidth)
		width = document.documentElement.clientWidth;
	else if(document.body)
		width = document.body.clientWidth;

	var ratio = width / old_width;
	
	if(ratio!=0 && ratio!=null)
	{
		for(i=0;i<document.images.length;i++)
			document.images[i].width = Math.round(document.images[i].width * ratio);
	}
	
	old_width = _width;
	return true;
}

function getSize()
{
	if(self.innerWidth)
		old_width = self.innerWidth;
	else if(document.documentElement && document.documentElement.clientWidth)
		old_width = document.documentElement.clientWidth;
	else if(document.body)
		old_width = document.body.clientWidth;

	return true;
}
</script>
</HEAD><BODY background=assets/themes/".Preferences('THEME')."/bg.jpg leftmargin=0 topmargin=4 onload='getSize();'>";
// System Information
echo '<TABLE cellpadding=0 cellspacing=0 border=0 width=100%>';
echo '<TR>';
echo '<TD valign=middle width=170><A HREF=index.php target=_top><IMG id=logo SRC="assets/themes/'.Preferences('THEME').'/logo.png" border=0></A></TD>';
echo '<TD width=15></TD><TD><TABLE width=100% border=0 cellpadding=2 cellspacing=0 style="border: 1px inset #999999"><TR bgcolor=#E8E8E9>';
require('Menu.php');
foreach($_openSIS['Menu'] as $modcat=>$value)
{
	if($value)
	{
		echo '<TD width=5></TD><TD align=center>';
		echo "<A HREF=Side.php?modcat=$modcat target=side onclick='javascript:parent.body.location=\"Modules.php?modname=$modcat/Search.php\";'>";
		echo "<DIV style='border:1px solid #E8E8E9;' onmouseover='this.style.border=\"1px outset #969696\";' onmouseout='this.style.border=\"1px solid #E8E8E9\";' onmousedown='this.style.border=\"1px inset #999999\";' onmouseup='this.style.border=\"1px outset #999999\";'>";
		echo "<IMG SRC=assets/icons/$modcat.png border=0><BR><small><b>".str_replace('_',' ',$modcat)."</b></small>";
		echo '</DIV>';
		echo '</A>';
		echo '</TD>';
	}
}
echo "</TR></TABLE></TD></TR></TABLE>";

echo '</BODY></HTML>';

?>