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
include("functions/ParamLibFnc.php");
require_once("Data.php");
include "./Warehouse.php";
// include('functions/SqlSecurityFnc.php');

$url=validateQueryString(curPageURL());
if($url===FALSE)
 {
 header('Location: index.php');
 }

if(clean_param($_REQUEST['modfunc'],PARAM_ALPHA)=='print')
{
    // $connection = new mysqli($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);
	$_REQUEST = $_SESSION['_REQUEST_vars'];
	$_REQUEST['_openSIS_PDF'] = true;
        $_REQUEST['_openSIS_PDF'] = mysqli_real_escape_string($connection,optional_param('_openSIS_PDF', '', PARAM_RAW));
        $_REQUEST['modname'] = mysqli_real_escape_string($connection,optional_param('modname', '', PARAM_RAW));
        $_REQUEST['failed_login'] = mysqli_real_escape_string($connection,optional_param('failed_login', '', PARAM_RAW));
	if(strpos($_REQUEST['modname'],'?')!==false)
		$modname = substr(mysqli_real_escape_string($connection,optional_param('modname', '', PARAM_RAW)),0,strpos(mysqli_real_escape_string($connection,optional_param('modname', '', PARAM_RAW)),'?'));
	else
		$modname = mysqli_real_escape_string($connection,optional_param('modname', '', PARAM_RAW));
	ob_start();
	include('modules/'.sqlSecurityFilter($modname));
	if($htmldocPath)
	{
		if($htmldocAssetsPath)
			$html = par_rep('/</?CENTER>/','',str_replace('assets/',$htmldocAssetsPath,ob_get_contents()));
		else
			$html = par_rep('/</?CENTER>/','',ob_get_contents());
		ob_end_clean();

		// get a temp filename, and then change its extension from .tmp to .html to make htmldoc happy.
		$temphtml=tempnam('','html');
		$temphtml_tmp=substr($temphtml, 0, strrpos($temphtml, ".")).'html';
		rename($temphtml_tmp, $temphtml);

		$fp=@fopen($temphtml,"w+");
		if (!$fp)
			die(""._canTOpen." $temphtml");
		fputs($fp,'<HTML><BODY>'.$html.'</BODY></HTML>');
		@fclose($fp);

		header("Cache-Control: public");
		header("Pragma: ");
		header("Content-Type: application/pdf");
		header("Content-Disposition: inline; filename=\"".ProgramTitle().".pdf\"\n");

		$orientation = 'portrait';
		if($_REQUEST['expanded_view'] || $_SESSION['orientation'] == 'landscape')
		{
			$orientation = 'landscape';
			unset($_SESSION['orientation']);
		}
		passthru("$htmldocPath --webpage --quiet -t pdf12 --jpeg --no-links --$orientation --footer t --header . --left 0.5in --top 0.5in \"$temphtml\"");
		@unlink($temphtml);
	}
	else
	{
		$html = par_rep('/</?CENTER>/','',ob_get_contents());
		ob_end_clean();
		echo '<HTML><BODY>'.$html.'</BODY></HTML>';
	}
}
else
{
echo "
	<HTML>
		<HEAD><TITLE>"._openSisSchoolSoftware."</TITLE>
		<SCRIPT>
		size = 30;
		function expandFrame()
		{
			if(size==30)
			{
				parent.document.getElementById('mainframeset').rows=\"*,200\";
				size = 200;
			}
			else
			{
				parent.document.getElementById('mainframeset').rows=\"*,30\";
				size = 30;
			}
		}
		</SCRIPT>";
//		<link rel=stylesheet type=text/css href=styles/Help.css>
		echo "</HEAD>
		<BODY><table width=100%; cellspacing=0 cellpadding=0><tr><td>";
	
	include 'Help.php';
	include 'Menu.php';
	$profile = User('PROFILE');
	echo '<div style=" width:470px; height:188px; background-color:transparent; overflow-x:hidden; overflow-y:scroll; text-align:left"><div style="padding:0px 12px 0px 12px;">';
                  if($_REQUEST['modcat'] && $_REQUEST['modname'])
	{
		echo '<b>'.str_replace('_',' ',$_REQUEST['modcat']);
		echo ' : '.$_openSIS['Menu'][$_REQUEST['modcat']][$_REQUEST['modname']];
		echo '</b>';
	}
                  if($help[$_REQUEST['modcat']] && !$_REQUEST['modname'])
	{
		if($student==true)
			$help[$_REQUEST['modcat']] = str_replace('your child','yourself',str_replace('your child\'s','your',$help[$_REQUEST['modcat']]));
                                    echo $help[$_REQUEST['modcat']];
                  }
	elseif($help[$_REQUEST['modname']])
	{
		if($student==true)
			$help[$_REQUEST['modname']] = str_replace('your child','yourself',str_replace('your child\'s','your',$help[$_REQUEST['modname']]));

		echo $help[$_REQUEST['modname']];
	}
	else
		echo $help['default'];
	echo '</div></DIV>';
	echo '</td></tr></table></BODY>';
	echo '</HTML>';
}
?>
