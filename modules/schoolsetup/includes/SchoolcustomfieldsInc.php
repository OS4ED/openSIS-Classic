<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, Timetable, grade book, attendance, 
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
include_once('modules/schoolsetup/includes/Functions.php');

$fields_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE FROM school_custom_fields WHERE SYSTEM_FIELD = 'N' AND (SCHOOL_ID='".UserSchool()."' OR SCHOOL_ID=0) ORDER BY SORT_ORDER,TITLE"));

if(UserSchool())
{
	$custom_RET = DBGet(DBQuery("SELECT * FROM schools WHERE ID='".  UserSchool()."'"));
	$value = $custom_RET[1];
}
$num_field_gen=true;
if(count($fields_RET))
{

$i = 1;
foreach($fields_RET as $field)
{
    if($field['HIDE']=='Y')
        continue;

	switch($field['TYPE'])
	{
		case 'text':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
                        echo '<div class="col-md-8">';
                        echo _makeTextInputSchl('CUSTOM_'.$field['ID'],'','');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'autos':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeAutoSelectInputSchl('CUSTOM_'.$field['ID'],'','values');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'edits':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeAutoSelectInputSchl('CUSTOM_'.$field['ID'],'','values');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'numeric':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeTextInputSchl('CUSTOM_'.$field['ID'],'','size=5 maxlength=10 class=form-control');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'date':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeDateInput_modSchl('CUSTOM_'.$field['ID'],'',$field['ID']);
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'codeds':
		case 'select':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeSelectInputSchl('CUSTOM_'.$field['ID'],'','values');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'multiple':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeMultipleInputSchl('CUSTOM_'.$field['ID'],'','values');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

		case 'radio':
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
			echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
			echo '<div class="col-md-8">';
                        echo _makeCheckboxInputSchl('CUSTOM_'.$field['ID'],'');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
			break;

               case 'textarea':	
			echo '<div class="col-md-6">';
			echo '<div class="form-group">';
                        echo '<label class="col-md-4 control-label text-right">'.$req.$field['TITLE'].':'.($field['REQUIRED']=='Y'?'<span class="text-danger">*</span>':'') .'</label>';
                        echo '<div class="col-md-8">';
                        echo _makeTextareaInputSchl('CUSTOM_'.$field['ID'],'');
			echo '</div>'; //.col-md-8
			echo '</div>'; //.form-group
			echo '</div>'; //.col-md-6
                       break;
	}
}

}
?>
