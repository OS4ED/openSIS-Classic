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

include 'RedirectRootInc.php';
include 'Warehouse.php';
include 'Data.php';

$table_name = sqlSecurityFilter($_REQUEST['table_name']);
$field_name = sqlSecurityFilter($_REQUEST['field_name']);
$val = sqlSecurityFilter($_REQUEST['val']);
$id = sqlSecurityFilter($_REQUEST['id']);
$msg = sqlSecurityFilter($_REQUEST['msg']);
$field_id = sqlSecurityFilter($_REQUEST['field_id']);

if(isset($table_name) && isset($field_name) && isset($val) && isset($field_id) && isset($msg))
{
   $check_query=DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM '.$table_name.' WHERE UPPER('.$field_name.')=UPPER(\''.singleQuoteReplace('','',trim($val)).'\') AND ID <>\''.$id.'\' '));

   echo $check_query[1]['REC_EXISTS'].'_'.$field_id.'_'.$msg;
}

?>