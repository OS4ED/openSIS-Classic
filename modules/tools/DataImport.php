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

$extension_error = '';

if (!extension_loaded('mbstring'))
    $extension_error .= '<div class="alert alert-danger alert-styled-left">You need to have the <b><code>php-mbstring</code></b> extension installed and enabled to use the Data Import Utility. </div>';

if (!extension_loaded('zip'))
    $extension_error .= '<div class="alert alert-danger alert-styled-left">You need to have the <b><code>php-zip</code></b> extension installed and enabled to use the Data Import Utility. </div>';

if ($extension_error != '') {
    echo '<div class="panel">';
    echo '<div class="panel-body">';
    echo $extension_error;
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel

    exit();
}

echo "<div id='mapping'></div>";

include '../../RedirectModulesInc.php';
include 'libraries/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
//$spreadsheet = new Spreadsheet();

echo '<link rel="stylesheet" type="text/css" href="modules/tools/assets/css/tools.css">';

DrawBC("" . _schoolSetup . " > " .  _dataImport . " >" . ProgramTitle());

function add_person($first, $middle, $last, $email) {
    global $data;

    $data [] = array(
        'first' => $first,
        'middle' => $middle,
        'last' => $last,
        'email' => $email
    );
}

if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'STUDENT_INFO') {

    if ($_REQUEST['action'] != 'insert' && $_REQUEST['action'] != 'display' && $_REQUEST['action'] != 'process') {
        echo '<div class="row">';
        echo '<div class="col-md-6 col-md-offset-3">';
        echo '<form enctype="multipart/form-data" action="Modules.php?modname=' . $_REQUEST['modname'] . '&action=insert&page_display=STUDENT_INFO" method="POST" onSubmit="return map_upload_validation();">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-body text-center">';

        echo '<h5 class="text-center">'._clickOnTheBrowseButtonToNavigateToTheExcelFileInYourComputerSHardDriveThatHasYourDataAndSelectIt.'. <b>'._afterSelectingClickUpload.'.</b></h5>';
        echo '<div class="form-group">';
        echo '<input type="hidden"  name="MAX_FILE_SIZE" value="2000000" />';
        echo '<div class="text-center"><label id="select-file-input"><input type="file" class="upload" id="file_id" name="file" /><i class="icon-upload"></i><br/><span>'._clickHereToSelectAFile.'</span></label></div>';
        echo '<p class="help-block">'._supportedFileTypesXlsXlsx.'</p>';
        echo '<p class="help-block">'. _note . ': ' . _theFirstRowMustContainColumnNames .'</p>';
        echo '</div>';

        echo '</div>'; //.panel-body
        echo '<div class="panel-footer text-center"><input type="submit" class="btn btn-primary" value="'._upload.'" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">'._cancel.'</a></div>';
        echo '</div>'; //.panel
        echo '</form>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row
        ?>
        <script>
            $(function () {
                $('#file_id').change(function (e) {
                    var fileName = e.target.files[0].name;
                    $('#select-file-input span').html('<b>Selected File: </b><br/>' + fileName + '<br/>(click to change)');
                });

            });
        </script>
        <?php

    } elseif ($_REQUEST['action'] == 'insert') {
        $arr_data = array();
        echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&action=display&page_display=STUDENT_INFO" name="student_form"  method="POST">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">'._pleaseCreateAOneToOneRelationshipBetweenTheFieldsInYourSpreadsheetAndTheFieldsInTheOpenSisDatabaseBySelectingTheAppropriateFieldsFromTheRightColumn.'. '._afterYouAreDoneClickMapIt.'.</h4>';
        echo '</div>'; //.panel-heading

        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">'._theseFieldsAreInYourExcelSpreadSheet.'</td><td width="200">&nbsp;</td><td>'._theseAreAvailableFieldsInOpenSis.'</td></tr>';
        echo '</thead>';
        $inputFileName = $_FILES['file']['tmp_name'];
        //        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        //        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
               $objReader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);
        $total_sheets = $objPHPExcel->getSheetCount(); // here 4  
        $allSheetName = $objPHPExcel->getSheetNames(); // array ([0]=>'student',[1]=>'teacher',[2]=>'school',[3]=>'college')  
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0); // first sheet  
        $highestRow = $objWorksheet->getHighestRow(); // here 5  
        $highestColumn = $objWorksheet->getHighestColumn(); // here 'E'  
         //        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);  // here 5  
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); 
        for ($row = 1; $row <= $highestRow; ++$row) {
            $arr_data_row = array();
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                $arr_data_row[$col] = $value;
            }

            if (isEmptyArray($arr_data_row)) {
                continue;
            }

            if (is_array($arr_data)) {
                $arr_data[$row - 1] = $arr_data_row;
            }
        }

        $_SESSION['data'] = $arr_data;
        $options = array('FIRST_NAME' =>_firstName,
         'LAST_NAME' => _lastName,
         'MIDDLE_NAME' =>_middleName,
         'NAME_SUFFIX' => _nameSuffix,
         'GENDER' => _gender,
         'ETHNICITY' => _ethnicity,
         'COMMON_NAME' => _commonName,
         'SOCIAL_SECURITY' => _socialSecurity,
         'BIRTHDATE' => _birthdate,
         'LANGUAGE' => _language,
         'ESTIMATED_GRAD_DATE' => _estimatedGraduationDate,
         'ALT_ID' => _alternateId,
         'EMAIL' => _emailStudentS,
         'PHONE' => _contactNoStudentS,
         'IS_DISABLE' => _disabled,
        );
        $options+=array('USERNAME' => _username,
         'PASSWORD' => _password,
        );
        $options+=array('GRADE_ID' => _grade,
         'SECTION_ID' => _section,
         'START_DATE' => _studentEnrollmentDate,
         'END_DATE' => _studentEnrollmentEndDate,
        );



        $options+=array('STREET_ADDRESS_1' => _addressLine_1StudentS,
         'STREET_ADDRESS_2' => _addressLine_2StudentS,
         'CITY' => _cityStudentS,
         'STATE' => _stateStudentS,
         'ZIPCODE' => _zipcodeStudentS,
        );
        $options+=array('PRIMARY_FIRST_NAME' => _primaryFirstName,
         'PRIMARY_MIDDLE_NAME' => _primaryMiddleName,
         'PRIMARY_LAST_NAME' => _primaryLastName,
         'PRIMARY_WORK_PHONE' => _workPhonePrimaryContactS,
         'PRIMARY_HOME_PHONE' => _homePhonePrimaryContactS,
         'PRIMARY_CELL_PHONE' => _cellPhonePrimaryContactS,
         'PRIMARY_EMAIL' => _emailPrimaryContactS,
         'PRIMARY_RELATION' => _relationshipPrimaryContactS,
        );
        $options+=array('SECONDARY_FIRST_NAME' => _secondaryFirstName,
         'SECONDARY_MIDDLE_NAME' => _secondaryMiddleName,
         'SECONDARY_LAST_NAME' => _secondaryLastName,
         'SECONDARY_WORK_PHONE' => _workPhoneSecondaryContactS,
         'SECONDARY_HOME_PHONE' => _homePhoneSecondaryContactS,
         'SECONDARY_CELL_PHONE' => _cellPhoneSecondaryContactS,
         'SECONDARY_EMAIL' => _emailSecondaryContactS,
         'SECONDARY_RELATION' => _relationshipSecondaryContactS,
        );

        $custom = DBGet(DBQuery('SELECT * FROM custom_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }
        $class = "odd";
        $i = 0;
        
        foreach ($arr_data[0] as $key => $value) {
            $arr_data[0][$key] = str_replace(
                ' ',
                '_',
                trim($value)
            );
        }
        foreach ($arr_data[0] as $key => $value) {

            if ($class == "odd")
                $class = "even";
            else
                $class = "odd";
            $i++;
            if ($value)
                echo "<tr class=" . $class . "><td class='" . $class . " p-t-20'>" . $value . "</td><td><div id='" . preg_replace('/[()\/]/', '', $value) . "' class='text-center p-t-15'></div></td><td class=" . $class . ">" . SelectInput($valuee, 'stu[' . $value . ']', '', $options, 'N/A', ' onchange=drawmapping(this.value,' . 'k' . $i . ',' . preg_replace('/[()\/]/', '', $value) . ');') . "</td></tr>";
            echo "<input type='hidden' name='student_map_value[]' id=k$i>";
        }

        echo '</table>';
        echo '</div>'; //.table-responsive
        echo '</div>'; //.panel-body
        echo '<input type=hidden name="filename"  value='.$inputFileName.'/>';
        echo '<div class="panel-footer text-center"><input id="mapItStuBtnOne" type="submit" value="Map it" class="btn btn-primary" onClick="return valid_mapping_student('.$i.', this);"  /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel

        echo "</form>";
    }
    elseif ($_REQUEST['action'] == 'display') {
        
        echo '<form action="Modules.php?modname='.$_REQUEST['modname'].'&action=process&page_display=STUDENT_INFO" name="STUDENT_INFO_CONFIRM" method="POST">';
        echo '<div class="panel panel-default">';
        
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">'._pleaseCreateAOneToOneRelationshipBetweenTheFieldsInYourSpreadsheetAndTheFieldsInTheOpenSisDatabaseBySelectingTheAppropriateFieldsFromTheRightColumn.'. '._afterYouAreDoneClickConfirm.'.</h4>';
        echo '</div>'; //.panel-body
        
        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
    echo '<thead>';
        echo '<tr class="bg-grey-200"><th style="word-wrap: break-word;">'._theseFieldsAreInYourExcelSpreadSheet.'</th><th width="200">&nbsp;</th><th>'._theseAreAvailableFieldsInOpenSis.'</th></tr>';
    echo '</thead>';
        echo '<tbody>';

        $options = array('FIRST_NAME' =>_firstName,
         'LAST_NAME' => _lastName,
         'MIDDLE_NAME' =>_middleName,
         'NAME_SUFFIX' => _nameSuffix,
         'GENDER' => _gender,
         'ETHNICITY' => _ethnicity,
         'COMMON_NAME' => _commonName,
         'SOCIAL_SECURITY' => _socialSecurity,
         'BIRTHDATE' => _birthdate,
         'LANGUAGE' => _language,
         'ESTIMATED_GRAD_DATE' => _estimatedGraduationDate,
         'ALT_ID' => _alternateId,
         'EMAIL' => _emailStudentS,
         'PHONE' => _contactNoStudentS,
         'IS_DISABLE' => _disabled,
        );
        $options+=array('USERNAME' => _username,
         'PASSWORD' => _password,
        );
        $options+=array('GRADE_ID' => _grade,
         'SECTION_ID' => _section,
         'START_DATE' => _studentEnrollmentDate,
         'END_DATE' => _studentEnrollmentEndDate,
        );


        $options+=array('STREET_ADDRESS_1' => _addressLine_1StudentS,
         'STREET_ADDRESS_2' => _addressLine_2StudentS,
         'CITY' => _cityStudentS,
         'STATE' => _stateStudentS,
         'ZIPCODE' => _zipcodeStudentS,
        );
        $options+=array('PRIMARY_FIRST_NAME' => _primaryFirstName,
         'PRIMARY_MIDDLE_NAME' => _primaryMiddleName,
         'PRIMARY_LAST_NAME' => _primaryLastName,
         'PRIMARY_WORK_PHONE' => _workPhonePrimaryContactS,
         'PRIMARY_HOME_PHONE' => _homePhonePrimaryContactS,
         'PRIMARY_CELL_PHONE' => _cellPhonePrimaryContactS,
         'PRIMARY_EMAIL' => _emailPrimaryContactS,
         'PRIMARY_RELATION' => _relationshipPrimaryContactS,
        );
        $options+=array('SECONDARY_FIRST_NAME' => _secondaryFirstName,
         'SECONDARY_MIDDLE_NAME' => _secondaryMiddleName,
         'SECONDARY_LAST_NAME' => _secondaryLastName,
         'SECONDARY_WORK_PHONE' => _workPhoneSecondaryContactS,
         'SECONDARY_HOME_PHONE' => _homePhoneSecondaryContactS,
         'SECONDARY_CELL_PHONE' => _cellPhoneSecondaryContactS,
         'SECONDARY_EMAIL' => _emailSecondaryContactS,
         'SECONDARY_RELATION' => _relationshipSecondaryContactS,
        );
        $custom = DBGet(DBQuery('SELECT * FROM custom_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }
        $class = "odd";

        $i = 0;
        foreach ($_REQUEST['stu'] as $key_stu => $value_stu) {

            if ($class == "odd")
                $class = "even";
            else
                $class = "odd";
            $i++;
            
            echo '<tr class="' . $class . '"><td class="' . $class . ' p-t-20">' . $key_stu . '</td>';
            if ($value_stu) {
                echo "<td class='" . $class . "'><div id='" . preg_replace('/[()\/]/', '', $key_stu) . "' class='text-center p-t-15'><img src=modules/tools/assets/images/arrow_mapping.png /></div></td>";
            } else {
                echo "<td class='" . $class . "'><div id='" . preg_replace('/[()\/]/', '', $key_stu) . "' class='text-center p-t-15'></div></td>";
            }

            echo "<td class=" . $class . "><input type=hidden name=student[$key_stu] value=$value_stu>" . SelectInput($value_stu, 'student[' . $key_stu . '][' . $value_stu . ']', '', $options, 'N/A', ' onchange=drawmapping_full(this.value,' . 'k' . $i . ',' . preg_replace('/[()\/]/', '', $key_stu) . ');');
            echo "<input type='hidden' name='student_map_value[$key_stu]' id=k$i value=$value_stu>";
            echo "</td></tr>";
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>'; //.table-responsive
        echo '</div>'; //.panel-body
        echo '<div class="panel-footer text-center"><input id="mapItStuBtnTwo" type="submit" value="'._confirm.'" class="btn btn-primary" onClick="return valid_mapping_student('.$i.', this);" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel
        echo '</form>';      
    } elseif ($_REQUEST['action'] == 'process') {

        echo '<div class="row">';
        echo '<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">';
        echo '<div id="calculating" class="panel panel-default">';
        echo '<div class="panel-body text-center">';
        echo '<h3 class="text-center m-b-0">Importing data in to the database</h3>';
        echo '<h6 class="text-center text-danger m-t-0">'._pleaseDoNotInterruptThisProcess.'.....</h6>';
        echo '<div class="p-t-35 p-b-35"><img src="modules/tools/assets/images/copy-to-database.gif" width="80%" /></div>';
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        $_SESSION['student'] = $_POST['student_map_value'];
  ?>
        <script type="text/javascript">
            ajax_progress('student');
        </script>
    <?php
    }
}
//================================Student info Ends==============================================
elseif (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'STAFF_INFO') {
    if ($_REQUEST['action'] != 'insert' && $_REQUEST['action'] != 'display' && $_REQUEST['action'] != 'process') {
        
        
    echo '<div class="row">';
    echo '<div class="col-md-6 col-md-offset-3">';
    echo '<form enctype="multipart/form-data" action="Modules.php?modname='.$_REQUEST['modname'].'&action=insert&page_display=STAFF_INFO" method="POST" onSubmit="return map_upload_validation();">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center">';

    echo '<h5 class="text-center">'._clickOnTheBrowseButtonToNavigateToTheExcelFileInYourComputerSHardDriveThatHasYourDataAndSelectIt.'. <b>'._afterSelectingClickUpload.'.</b></h5>';
    echo '<div class="form-group">';
    echo '<input type="hidden"  name="MAX_FILE_SIZE" value="2000000" />';
    echo '<div class="text-center"><label id="select-file-input"><input type="file" class="upload" id="file_id" name="file" /><i class="icon-upload"></i><br/><span>'._clickHereToSelectAFile.'</span></label></div>';
    echo '<p class="help-block">'._supportedFileTypesXlsXlsx.'</p>';
    echo '</div>';

    echo '</div>'; //.panel-body
    echo '<div class="panel-footer text-center"><input type="submit" class="btn btn-primary" value="'._upload.'" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">'._cancel.'</a></div>';
    echo '</div>'; //.panel
    echo '</form>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
    ?>
    <script>
        $(function () {
            $('#file_id').change(function (e) {
                var fileName = e.target.files[0].name;
                $('#select-file-input span').html('<b>Selected File: </b><br/>' + fileName + '<br/>(click to change)');
            });

        });
    </script>
    <?php 
        
    } elseif ($_REQUEST['action'] == 'insert') {
        $arr_data = array();
        echo '<form action="Modules.php?modname=' . $_REQUEST['modname'] . '&action=display&page_display=STAFF_INFO" name="staff_form"  method="POST">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">'._pleaseCreateAOneToOneRelationshipBetweenTheFieldsInYourSpreadsheetAndTheFieldsInTheOpenSisDatabaseBySelectingTheAppropriateFieldsFromTheRightColumn.'. '._afterYouAreDoneClickMapIt.'.</h4>';
        echo '</div>'; //.panel-heading

        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">'._theseFieldsAreInYourExcelSpreadSheet.'</td><td width="200">&nbsp;</td><td>'._theseAreAvailableFieldsInOpenSis.'</td></tr>';
        echo '</thead>';
        $inputFileName = $_FILES['file']['tmp_name'];
        // $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        // $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);
        $total_sheets = $objPHPExcel->getSheetCount(); // here 4  
        $allSheetName = $objPHPExcel->getSheetNames(); // array ([0]=>'student',[1]=>'teacher',[2]=>'school',[3]=>'college')  
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0); // first sheet  
        $highestRow = $objWorksheet->getHighestRow(); // here 5  
        $highestColumn = $objWorksheet->getHighestColumn(); // here 'E'  
        // $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);  // here 5  
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); 
        for ($row = 1; $row <= $highestRow; ++$row) {
            $arr_data_row = array();
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                $arr_data_row[$col] = $value;
            }

            if (isEmptyArray($arr_data_row)) {
                continue;
            }

            if (is_array($arr_data)) {
                $arr_data[$row - 1] = $arr_data_row;
            }
        }
        $_SESSION['data'] = $arr_data;

        $options = array('TITLE' => _salutation,
         'FIRST_NAME' =>_firstName,
         'LAST_NAME' => _lastName,
         'MIDDLE_NAME' =>_middleName,
         'EMAIL' => _email,
         'PHONE' => _phone,
         'PROFILE' => _profile,
         'HOMEROOM' => _homeroom,
         'BIRTHDATE' => _birthdate,
         'ETHNICITY_ID' => _ethnicity,
         'ALTERNATE_ID' =>_alternateId,
         'PRIMARY_LANGUAGE_ID' => _primaryLanguage,
         'GENDER' => _gender,
         'SECOND_LANGUAGE_ID' => _secondaryLanguage,
         'THIRD_LANGUAGE_ID' => _thirdLanguage,
         'IS_DISABLE' => _disabled,
        );
        $options+=array('USERNAME' => _username,
         'PASSWORD' => _password,
        );
        $options+=array('START_DATE' => _startDate,
         'END_DATE' => _endDate,
        );
        $options+=array('CATEGORY' => _category,
         'JOB_TITLE' =>_jobTitle,
         'JOINING_DATE' => _joiningDate,
        );
        $custom = DBGet(DBQuery('SELECT * FROM staff_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }

        $class = "odd";
        //  print_r($arr_data);
        $i = 0;
        
        foreach ($arr_data[0] as $key => $value) {
            $arr_data[0][$key] = str_replace(
                ' ',
                '_',
                trim($value)
            );
        }
        foreach ($arr_data[0] as $key => $value) {
            if ($class == "odd")
                $class = "even";
            else
                $class = "odd";
            $i++;
            if ($value)
                echo "<tr class=" . $class . "><td class=" . $class . ">" . $value . "</td><td><div id='" . preg_replace('/[()\/]/', '', $value) . "'></div></td><td class=" . $class . ">" . SelectInput($valuee, 'staff[' . $value . ']', '', $options, 'N/A', ' onchange=drawmapping(this.value,' . 'k' . $i . ',' . preg_replace('/[()\/]/', '', $value) . ');') . "</td></tr>";
            echo "<input type='hidden' name='student_map_value[]' id=k$i>";
        }
        
        echo '</table>';
        echo '</div>'; //.table-responsive
        echo '</div>'; //.panel-body
        echo '<input type=hidden name="filename"  value='.$inputFileName.'/>';
        echo '<div class="panel-footer text-center"><input id="mapItStaBtnOne" type="submit" value="'._mapIt.'" class="btn btn-primary" onClick="return valid_mapping_staff('.$i.', this);"  /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">'._cancel.'</a></div>';
        echo "</form>";
    }
    elseif ($_REQUEST['action'] == 'display') {
        $staff_keys = array_keys($_REQUEST['staff']);
        $staff_keys_string = implode(',', $staff_keys);
        $staff_values = implode(',', $_REQUEST['staff']);
        echo "<script>ajax_mapping('" . $staff_keys_string . "','" . $staff_values . "','staff');</script>";
        echo '<form action="Modules.php?modname='.$_REQUEST['modname'].'&action=process&page_display=STAFF_INFO" name="STAFF_INFO_CONFIRM" method="POST">';
        echo '<div class="panel panel-default">';
        
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">'._pleaseCreateAOneToOneRelationshipBetweenTheFieldsInYourSpreadsheetAndTheFieldsInTheOpenSisDatabaseBySelectingTheAppropriateFieldsFromTheRightColumn.'. '._afterYouAreDoneClickConfirm.'.</h4>';
        echo '</div>'; //.panel-body
        
        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
    echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">'._theseFieldsAreInYourExcelSpreadSheet.'</th><th width="200">&nbsp;</th><th>'._theseAreAvailableFieldsInOpenSis.' (Click to change the field values)</th></tr>';
    echo '</thead>';
        echo '<tbody>';
        

        $options = array('TITLE' => _salutation,
         'FIRST_NAME' =>_firstName,
         'LAST_NAME' => _lastName,
         'MIDDLE_NAME' =>_middleName,
         'EMAIL' => _email,
         'PHONE' => _phone,
         'PROFILE' => _profile,
         'HOMEROOM' => _homeroom,
         'BIRTHDATE' => _birthdate,
         'ETHNICITY_ID' => _ethnicity,
         'ALTERNATE_ID' =>_alternateId,
         'PRIMARY_LANGUAGE_ID' => _primaryLanguage,
         'GENDER' => _gender,
         'SECOND_LANGUAGE_ID' => _secondaryLanguage,
         'THIRD_LANGUAGE_ID' => _thirdLanguage,
         'IS_DISABLE' => _disabled,
        );
        $options+=array('USERNAME' => _username,
         'PASSWORD' => _password,
        );
        $options+=array('START_DATE' => _startDate,
         'END_DATE' => _endDate,
        );
        $options+=array('CATEGORY' => _category,
         'JOB_TITLE' =>_jobTitle,
         'JOINING_DATE' => _joiningDate,
        );
        $class = "odd";
        $custom = DBGet(DBQuery('SELECT * FROM staff_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }

        $i = 0;
        foreach ($_REQUEST['staff'] as $key_stu => $value_stu) {
            if ($class == "odd")
                $class = "even";
            else
                $class = "odd";
            echo "<tr class=" . $class . "><td class=" . $class . ">" . $key_stu . "</td>";
            if ($value_stu) {
                echo "<td><div id='" . preg_replace('/[()\/]/', '', $key_stu) . "'><img src=modules/tools/assets/images/arrow_mapping.png /></div></td>  ";
            } else {
                echo "<td><div id='" . preg_replace('/[()\/]/', '', $key_stu) . "'></div></td>  ";
            }
            $i++;
            echo "<td class=" . $class . "><input type=hidden name=staff[$key_stu] value=$value_stu>" . SelectInput($value_stu, 'staff[' . $key_stu . '][' . $value_stu . ']', '', $options, 'N/A', 'onchange=drawmapping_full(this.value,' . 'k' . $i . ',' . preg_replace('/[()\/]/', '', $key_stu) . ');') . "</td></tr>";
            echo "<input type='hidden' name='staff_map_value[$key_stu]' id=k$i value=$value_stu>";
        }
        
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; //.table-responsive
        echo '</div>'; //.panel-body
        echo '<div class="panel-footer text-center"><input id="mapItStaBtnTwo" type="submit" value="'._confirm.'" class="btn btn-primary" onClick="return valid_mapping_staff('.$i.', this);" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST['modname'] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel
        echo '</form>';       
    } elseif ($_REQUEST['action'] == 'process') {
        
        
        
         echo '<div class="row">';
        echo '<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">';
        echo '<div id="calculating" class="panel panel-default">';
        echo '<div class="panel-body text-center">';
        echo '<h3 class="text-center m-b-0">'._importingDataInToTheDatabase.'</h3>';
        echo '<h6 class="text-center text-danger m-t-0">'._pleaseDoNotInterruptThisProcess.'.....</h6>';
        echo '<div class="p-t-35 p-b-35"><img src="modules/tools/assets/images/copy-to-database.gif" width="80%" /></div>';
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        $_SESSION['staff'] = $_POST['staff_map_value'];

        echo "<script>ajax_progress('staff');</script>";
    }
}
else {

    echo '<h1 class="text-center m-b-0">'._dataImportUtility.'</h1>';
    echo '<p class="text-center text-grey m-b-30">'._pleaseSelectAProfileToImportTheirRelevantData.'.</p>';

    echo '<div class="row">';
    echo '<div class="col-md-3 col-md-offset-3">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center p-t-35">';
    echo '<a href=Modules.php?modname=' . $_REQUEST['modname'] . '&page_display=STUDENT_INFO><img src="modules/tools/assets/images/student.svg" width="60%" /><h4>'._importStudentData.'</h4></a>';
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-3
    echo '<div class="col-md-3">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center p-t-35">';
    echo '<a href=Modules.php?modname=' . $_REQUEST['modname'] . '&page_display=STAFF_INFO><img src="modules/tools/assets/images/faculty.svg" width="60%" /><h4>'._importStaffData.'</h4></a>';
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    echo '</div>';
    echo '</div>';
}

function isEmptyArray($array)
{
    return implode($array) === '';
}
?>
