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
include('../../../RedirectIncludes.php');
include_once('modules/users/includes/FunctionsInc.php');
$fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED FROM people_fields WHERE CATEGORY_ID=\''.$_REQUEST[category_id].'\' ORDER BY SORT_ORDER,TITLE'));

if(UserStaffID())
{
	$custom_RET = DBGet(DBQuery('SELECT * FROM people WHERE STAFF_ID=\''.UserStaffID().'\''));
	$value = $custom_RET[1];
}


if(count($fields_RET))
	echo $separator;

$i = 1;
foreach($fields_RET as $field)
{

	switch($field['TYPE'])
	{
		case 'text':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeTextInput('CUSTOM_'.$field['ID'],'','size=25 class=cell_floating');
			echo '</TD>';

				echo '</TR>';

			break;

		case 'autos':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeAutoSelectInputParent('CUSTOM_'.$field['ID']);
			echo '</TD>';

				echo '</TR>';


			break;

		case 'edits':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeAutoSelectInputParent('CUSTOM_'.$field['ID']);
			echo '</TD>';

				echo '</TR>';

			break;

		case 'numeric':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeTextInput('CUSTOM_'.$field['ID'],'','size=5 maxlength=10 class=cell_floating');
			echo '</TD>';

				echo '</TR>';

			break;

		case 'date':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'.DateInputAY($value['CUSTOM_'.$field['ID']],'CUSTOM_'.$field['ID'],$field['ID']);
                        echo  '<input type=hidden name=custom_date_id[] value="'.$field['ID'].'" />';
                        echo '</TD>';

				echo '</TR>';

			break;

		case 'codeds':
		case 'select':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeSelectInput('CUSTOM_'.$field['ID'],'');
			echo '</TD>';

				echo '</TR>';

			break;

		case 'multiple':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD></TD><TD>'._makeMultipleInput('CUSTOM_'.$field['ID'],'','user_checkbox');
			echo '</TD>';

				echo '</TR>';

			break;

		case 'radio':

				echo '<TR>';
			echo '<TD style="width:100px">';

                        echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeCheckboxInput('CUSTOM_'.$field['ID'],'');
			echo '</TD>';

				echo '</TR>';

			break;
	}
}

$i = 1;
foreach($fields_RET as $field)
{
	if($field['TYPE']=='textarea')
	{

			echo '<TR>';
		echo '<TD style="width:100px">';

                echo ($field['REQUIRED']=='Y'?'<span class=red>*</span>':'').$field['TITLE'].'</TD><TD>:</TD><TD>'._makeTextareaInput('CUSTOM_'.$field['ID'],'');
		echo '</TD>';

			echo '</TR>';

	}
}


?>