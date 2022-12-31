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
$fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\' ORDER BY SORT_ORDER,TITLE'));

if (UserStaffID()) {
    $custom_RET = DBGet(DBQuery('SELECT * FROM people WHERE STAFF_ID=\'' . UserStaffID() . '\''));
    $value = $custom_RET[1];
}


if (count($fields_RET))
    echo $separator;

$j = 1;
$i = 1;

// echo "<pre>"; print_r($fields_RET);

foreach ($fields_RET as $key => $field) {
    //    echo ($key)."--<br>";
    //    print_r($field);

    if ($i == 1) {
        echo '<div class="row">';
    }

    switch ($field['TYPE']) {
        case 'text':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';

            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeTextInput('CUSTOM_' . $field['ID'], '', 'size=25 class=cell_floating') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeTextInput('CUSTOM_' . $field['ID'], '', 'size=25 class=cell_floating') . '</div>';

            echo '</div>';
            echo '</div>';

            break;

        case 'autos':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';

            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeAutoSelectInputParent('CUSTOM_' . $field['ID'], '') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeAutoSelectInputParent('CUSTOM_' . $field['ID'], '') . '</div>';

            echo '</div>';
            echo '</div>';


            break;

        case 'edits':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeAutoSelectInputParent('CUSTOM_' . $field['ID'], '') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeAutoSelectInputParent('CUSTOM_' . $field['ID'], '') . '</div>';
            echo '</div>';
            echo '</div>';

            break;

        case 'numeric':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class="text-danger">*</span>' : '') . '</label><div class="col-lg-8">' . _makeTextInput('CUSTOM_' . $field['ID'], '', 'size=5 maxlength=10 ' . ($value['CUSTOM_'.$field['ID']] != '' ? 'onkeydown=\"return numberOnly(event);\"' : 'onkeydown="return numberOnly(event);"')) . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class="text-danger">*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeTextInput('CUSTOM_' . $field['ID'], '', 'size=5 maxlength=10 class=cell_floating') . '</div>';
            echo '</div>';
            echo '</div>';

            break;

        case 'date':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . DateInputAY(($value['CUSTOM_' . $field['ID']] != '' && $value['CUSTOM_' . $field['ID']] != '0000-00-00' ? date('Y-m-d',strtotime($value['CUSTOM_' . $field['ID']])) : ''), 'CUSTOM_' . $field['ID'], $field['ID']) . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . DateInputAY($value['CUSTOM_' . $field['ID']], 'CUSTOM_' . $field['ID'], $field['ID']) . '</div>';
            echo '<input type=hidden name=custom_date_id[] value="' . $field['ID'] . '" />';
            echo '</div>';
            echo '</div>';

            break;

        case 'codeds':
        case 'select':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeSelectInput('CUSTOM_' . $field['ID'], '') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeSelectInput('CUSTOM_' . $field['ID'], '') . '</div>';
            echo '</div>';
            echo '</div>';

            break;

        case 'multiple':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeMultipleInput('CUSTOM_' . $field['ID'], '', 'staff') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeMultipleInput('CUSTOM_' . $field['ID'], '', 'user_checkbox') . '</div>';
            echo '</div>';
            echo '</div>';

            break;

        case 'radio':

            echo '<div class="col-lg-6">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeCheckboxInput('CUSTOM_' . $field['ID'], '') . '</div>';
            // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeCheckboxInput('CUSTOM_' . $field['ID'], '') . '</div>';
            echo '</div>';
            echo '</div>';

            break;
    }
    $i++;
    if ($i == 3 || count($fields_RET) == $j) {
        echo '</div>'; //.row
        $i = 1;
    }
    $j++;
}


$j = 1;
$i = 1;
foreach ($fields_RET as $field) {

    if ($i == 1) {
        echo '<div class="row">';
    }

    if ($field['TYPE'] == 'textarea') {
        echo '<div class="col-lg-6">';
        echo '<div class="form-group">';
        echo '<label class="control-label col-lg-4 text-right">' . $field['TITLE'] . ($field['REQUIRED'] == 'Y' ? ' <span class=text-danger>*</span>' : '')  . '</label><div class="col-lg-8">' . _makeTextareaInput('CUSTOM_' . $field['ID'], '') . '</div>';
        // echo '<label class="control-label col-lg-4 text-right">' . ($field['REQUIRED'] == 'Y' ? '<span class=red>*</span>' : '') . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeTextareaInput('CUSTOM_' . $field['ID'], '') . '</div>';
        echo '</div>';
        echo '</div>';
    }

    $i++;
    if ($i == 3 || count($fields_RET) == $j) {
        echo '</div>'; //.row
        $i = 1;
    }
    $j++;
}
?>
