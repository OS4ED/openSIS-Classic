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
include('RedirectRootInc.php'); 
include 'Warehouse.php';
include 'Data.php';

if(isset($_REQUEST['table_name']) && isset($_REQUEST['field_name']) && isset($_REQUEST['val']) && isset($_REQUEST['field_id']) && isset($_REQUEST['msg']))
{
  
   $check_query=DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM '.$_REQUEST['table_name'].' WHERE UPPER('.$_REQUEST['field_name'].')=UPPER(\''.singleQuoteReplace('','',trim($_REQUEST['val'])).'\') AND ID <>\''.$_REQUEST['id'].'\' '));
   echo $check_query[1]['REC_EXISTS'].'_'.$_REQUEST['field_id'].'_'.$_REQUEST['msg'];
}
?>
