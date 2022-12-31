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
include_once('modules/students/includes/FunctionsInc.php');
$fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED,HIDE FROM custom_fields WHERE SYSTEM_FIELD = \'N\' AND CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\' ORDER BY SORT_ORDER,TITLE'));

if (UserStudentID()) {
    $custom_RET = DBGet(DBQuery('SELECT * FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    $value = $custom_RET[1];
}
$num_field_gen = true;
if (count($fields_RET)) {
    echo '<div class="row">';
    $row = 1;
    $i = 1;
    foreach ($fields_RET as $field) {
        if ($row == 3) {
            echo '</div><div class="row">';
            $row = 1;
        }
        if ($field['HIDE'] == 'Y')
            continue;
        if ($field['REQUIRED'] == 'Y') {
            $req = '<font color=red>*</font> ';
        } else {
            $req = '';
        }
        switch ($field['TYPE']) {
            case 'text':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</Label>';
                echo '<div class="col-lg-8">';
                echo _makeTextInput('CUSTOM_' . $field['ID'], '', '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'autos':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeAutoSelectInput('CUSTOM_' . $field['ID'], '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'edits':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeAutoSelectInput('CUSTOM_' . $field['ID'], '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'numeric':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeTextInput('CUSTOM_' . $field['ID'], '', 'maxlength=10 ' . ($value['CUSTOM_' . $field['ID']] != '' ? 'onkeydown=\"return numberOnly(event);\"' : 'onkeydown="return numberOnly(event);"'));
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'date':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo DateInputAY(($value['CUSTOM_' . $field['ID']] == '0000-00-00' ? '' : $value['CUSTOM_' . $field['ID']]), 'CUSTOM_' . $field['ID'], $field['ID']);
                echo '<input type=hidden name=custom_date_id[] value="' . $field['ID'] . '" />';
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'codeds':
            case 'select':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeSelectInput('CUSTOM_' . $field['ID'], 'class=form-control');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'multiple':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeMultipleInput('CUSTOM_' . $field['ID'], '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'radio':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeCheckboxInput('CUSTOM_' . $field['ID'], '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;

            case 'textarea':
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'] . '">' . $field['TITLE'] . ' ' . $req . '</label>';
                echo '<div class="col-lg-8">';
                echo _makeTextareaInput('CUSTOM_' . $field['ID'], '');
                echo '</div>'; //.col-lg-8
                echo '</div>'; //.form-group
                echo '</div>'; //.col-md-6
                break;
        }
        $row++;
    }
    echo '</div>';
}
