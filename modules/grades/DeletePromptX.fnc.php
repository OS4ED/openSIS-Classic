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
function DeletePromptX($title,$action='Delete')
{
	$tmp_REQUEST = $_REQUEST;
	unset($tmp_REQUEST['delete_ok']);
	unset($tmp_REQUEST['delete_cancel']);

	$PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

	if(!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel'])
	{
		echo '<BR>';
		PopTable('header','Confirm'.(!substr(' ',' '.$action)?$action:''));
		echo "<CENTER><h4>Are You Sure You Want to $action that $title?</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=submit name=delete_ok class=\"btn btn-danger\" value=OK> <INPUT type=submit class=\"btn btn-primary\" name=delete_cancel value=Cancel></FORM></CENTER>";
		PopTable('footer');
		return '';
	}
	if($_REQUEST['delete_ok'])
	{
		unset($_REQUEST['delete_ok']);
		unset($_REQUEST['modfunc']);
		return true;
	}
	unset($_REQUEST['delete_cancel']);
	unset($_REQUEST['modfunc']);
	return false;
}
function UnableDeletePromptX($title)
{
	$tmp_REQUEST = $_REQUEST;	
	$PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);
	if(!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel'])
	{
		echo '<BR>';
		PopTable('header','Unable to Delete');
		echo "<CENTER><h4>$title</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=submit class=\"btn btn-primary\" name=delete_cancel value=Cancel></FORM></CENTER>";
		PopTable('footer');
		return '';
	}
	if($_REQUEST['delete_ok'])
	{
		unset($_REQUEST['delete_ok']);
		unset($_REQUEST['modfunc']);
		return true;
	}
	unset($_REQUEST['delete_cancel']);
	unset($_REQUEST['modfunc']);
	return false;
}
?>