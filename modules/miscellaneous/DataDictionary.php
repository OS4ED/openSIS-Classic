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
$sql = 'SELECT a.attnum,a.attname AS field,t.typname AS type,
        a.attlen AS length,a.atttypmod AS lengthvar,
        a.attnotnull AS notnull,c.relname
        FROM pg_class c, pg_attribute a, pg_type t 
        WHERE
        a.attnum > 0 and a.attrelid = c.oid 
        and c.relkind=\'r\' and c.relname not like \'pg\_%\' and a.attname not like \'...%\'
        and a.atttypid = t.oid ORDER BY c.relname';
$RET = DBGet(DBQuery($sql),array(),array('RELNAME'));
$PDF = PDFStart();
echo '<TABLE>';
foreach($RET as $table=>$columns)
{
	if($i%2==0)
        {
        echo '<TR><TD valign=top>';
        }
	echo '<b>'.$table.'</b>';
	echo '<TABLE>';
	foreach($columns as $column)
		echo '<TR><TD width=15>&nbsp; &nbsp; </TD><TD>'.$column['FIELD'].'</TD><TD>'.$column['TYPE'].'</TD></TR>';
	echo '</TABLE>';
	if($i%2==0)
		echo '</TD><TD valign=top>';
	else
		echo '</TD></TR>';
	$i++;
}
echo '</TABLE>';
PDFStop($PDF);
?>