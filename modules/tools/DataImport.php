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

echo "<div id='mapping'></div>";

include('../../RedirectModules.php');
include('Classes/PHPExcel.php');
echo '<link rel="stylesheet" type="text/css" href="modules/tools/assets/css/tools.css">';
DrawBC("School Setup > Data Import >" . ProgramTitle());


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
        echo '<form enctype="multipart/form-data" action="Modules.php?modname=' . $_REQUEST[modname] . '&action=insert&page_display=STUDENT_INFO" method="POST" onSubmit="return map_upload_validation();">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-body text-center">';

        echo '<h5 class="text-center">Click on the Browse button to navigate to the Excel file in your computer\'s hard drive that has your data and select it. <b>After selecting, click Upload.</b></h5>';
        echo '<div class="form-group">';
        echo '<input type="hidden"  name="MAX_FILE_SIZE" value="2000000" />';
        echo '<div class="text-center"><label id="select-file-input"><input type="file" class="upload" id="file_id" name="file" /><i class="icon-upload"></i><br/><span>Click here to select a file</span></label></div>';
        echo '<p class="help-block">Supported file types: xls, xlsx</p>';
        echo '</div>';

        echo '</div>'; //.panel-body
        echo '<div class="panel-footer text-center"><input type="submit" class="btn btn-primary" value="Upload" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
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
        echo '<form action="Modules.php?modname=' . $_REQUEST[modname] . '&action=display&page_display=STUDENT_INFO" name="student_form"  method="POST">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">Please create a one-to-one relationship between the fields in your spreadsheet and the fields in the openSIS database by selecting the appropriate fields from the right column. After you are done, click Map it.</h4>';
        echo '</div>'; //.panel-heading

        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
	echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">These fields are in your Excel spread sheet</td><td width="200">&nbsp;</td><td>These are available fields in openSIS</td></tr>';
	echo '</thead>';
        $inputFileName = $_FILES['file']['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);
        $total_sheets = $objPHPExcel->getSheetCount(); // here 4  
        $allSheetName = $objPHPExcel->getSheetNames(); // array ([0]=>'student',[1]=>'teacher',[2]=>'school',[3]=>'college')  
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0); // first sheet  
        $highestRow = $objWorksheet->getHighestRow(); // here 5  
        $highestColumn = $objWorksheet->getHighestColumn(); // here 'E'  
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);  // here 5  
        for ($row = 1; $row <= $highestRow; ++$row) {
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                if (is_array($arr_data)) {
                    $arr_data[$row - 1][$col] = $value;
                }
            }
        }

        $_SESSION['data'] = $arr_data;
        $options = array('FIRST_NAME' => 'First Name', 'LAST_NAME' => 'Last Name', 'MIDDLE_NAME' => 'Middle Name', 'NAME_SUFFIX' => 'Name Suffix', 'GENDER' => 'Gender', 'ETHNICITY' => 'Ethnicity', 'COMMON_NAME' => 'Common Name', 'SOCIAL_SECURITY' => 'Social Security', 'BIRTHDATE' => 'Birthdate', 'LANGUAGE' => 'Language', 'ESTIMATED_GRAD_DATE' => 'Estimated Graduation Date', 'ALT_ID' => 'Alternate Id', 'EMAIL' => 'Email (Student\'s)', 'PHONE' => 'Contact No (Student\'s)', 'IS_DISABLE' => 'Disabled');
        $options+=array('USERNAME' => 'Username', 'PASSWORD' => 'Password');
        $options+=array('GRADE_ID' => 'Grade', 'SECTION_ID' => 'Section', 'START_DATE' => 'Student Enrollment Date', 'END_DATE' => 'Student Enrollment End Date');



        $options+=array('STREET_ADDRESS_1' => 'Address Line 1 (Student\'s)', 'STREET_ADDRESS_2' => 'Address Line 2 (Student\'s)', 'CITY' => 'City (Student\'s)', 'STATE' => 'State (Student\'s)', 'ZIPCODE' => 'Zipcode (Student\'s)');
        $options+=array('PRIMARY_FIRST_NAME' => 'Primary First Name', 'PRIMARY_MIDDLE_NAME' => 'Primary Middle Name', 'PRIMARY_LAST_NAME' => 'Primary Last Name', 'PRIMARY_WORK_PHONE' => 'Work Phone (Primary Contact\'s)', 'PRIMARY_HOME_PHONE' => 'Home Phone (Primary Contact\'s)', 'PRIMARY_CELL_PHONE' => 'Cell Phone (Primary Contact\'s)', 'PRIMARY_EMAIL' => 'Email (Primary Contact\'s)', 'PRIMARY_RELATION' => 'Relationship (Primary Contact\'s)');
        $options+=array('SECONDARY_FIRST_NAME' => 'Secondary First Name', 'SECONDARY_MIDDLE_NAME' => 'Secondary Middle Name', 'SECONDARY_LAST_NAME' => 'Secondary Last Name', 'SECONDARY_WORK_PHONE' => 'Work Phone (Secondary Contact\'s)', 'SECONDARY_HOME_PHONE' => 'Home Phone (Secondary Contact\'s)', 'SECONDARY_CELL_PHONE' => 'Cell Phone (Secondary Contact\'s)', 'SECONDARY_EMAIL' => 'Email (Secondary Contact\'s)', 'SECONDARY_RELATION' => 'Relationship (Secondary Contact\'s)');

        $custom = DBGet(DBQuery('SELECT * FROM custom_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }
        $class = "odd";
        $i = 0;
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
        echo '<div class="panel-footer text-center"><input type="submit" value="Map it" class="btn btn-primary" onClick="return valid_mapping_student('.$i.');"  /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel

        echo "</form>";
    }
    elseif ($_REQUEST['action'] == 'display') {
        
        echo '<form action="Modules.php?modname='.$_REQUEST[modname].'&action=process&page_display=STUDENT_INFO" name="STUDENT_INFO_CONFIRM" method="POST">';
        echo '<div class="panel panel-default">';
        
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">Please create a one-to-one relationship between the fields in your spread sheet and the fields in the openSIS database by selecting the appropriate fields from the right column. After you are done, click Confirm.</h4>';
        echo '</div>'; //.panel-body
        
        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
	echo '<thead>';
        echo '<tr class="bg-grey-200"><th style="word-wrap: break-word;">These fields are in your Excel spread sheet</th><th width="200">&nbsp;</th><th>These are available fields in openSIS</th></tr>';
	echo '</thead>';
        echo '<tbody>';

        $options = array('FIRST_NAME' => 'First Name', 'LAST_NAME' => 'Last Name', 'MIDDLE_NAME' => 'Middle Name', 'NAME_SUFFIX' => 'Name Suffix', 'GENDER' => 'Gender', 'ETHNICITY' => 'Ethnicity', 'COMMON_NAME' => 'Common Name', 'SOCIAL_SECURITY' => 'Social Security', 'BIRTHDATE' => 'Birthdate', 'LANGUAGE' => 'Language', 'ESTIMATED_GRAD_DATE' => 'Estimated Graduation Date', 'ALT_ID' => 'Alternate Id', 'EMAIL' => 'Email (Student\'s)', 'PHONE' => 'Contact No (Student\'s)', 'IS_DISABLE' => 'Disabled');
        $options+=array('USERNAME' => 'Username', 'PASSWORD' => 'Password');
        $options+=array('GRADE_ID' => 'Grade', 'SECTION_ID' => 'Section', 'START_DATE' => 'Student Enrollment Date', 'END_DATE' => 'Student Enrollment End Date');


        $options+=array('STREET_ADDRESS_1' => 'Address Line 1 (Student\'s)', 'STREET_ADDRESS_2' => 'Address Line 2 (Student\'s)', 'CITY' => 'City (Student\'s)', 'STATE' => 'State (Student\'s)', 'ZIPCODE' => 'Zipcode (Student\'s)');
        $options+=array('PRIMARY_FIRST_NAME' => 'Primary First Name', 'PRIMARY_MIDDLE_NAME' => 'Primary Middle Name', 'PRIMARY_LAST_NAME' => 'Primary Last Name', 'PRIMARY_WORK_PHONE' => 'Work Phone (Primary Contact\'s)', 'PRIMARY_HOME_PHONE' => 'Home Phone (Primary Contact\'s)', 'PRIMARY_CELL_PHONE' => 'Cell Phone (Primary Contact\'s)', 'PRIMARY_EMAIL' => 'Email (Primary Contact\'s)', 'PRIMARY_RELATION' => 'Relationship (Primary Contact\'s)');
        $options+=array('SECONDARY_FIRST_NAME' => 'Secondary First Name', 'SECONDARY_MIDDLE_NAME' => 'Secondary Middle Name', 'SECONDARY_LAST_NAME' => 'Secondary Last Name', 'SECONDARY_WORK_PHONE' => 'Work Phone (Secondary Contact\'s)', 'SECONDARY_HOME_PHONE' => 'Home Phone (Secondary Contact\'s)', 'SECONDARY_CELL_PHONE' => 'Cell Phone (Secondary Contact\'s)', 'SECONDARY_EMAIL' => 'Email (Secondary Contact\'s)', 'SECONDARY_RELATION' => 'Relationship (Secondary Contact\'s)');
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
        echo '<div class="panel-footer text-center"><input type="submit" value="Confirm" class="btn btn-primary" onClick="return valid_mapping_student('.$i.');" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel
        echo '</form>';
        
    } elseif ($_REQUEST['action'] == 'process') {

        echo '<div class="row">';
        echo '<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">';
        echo '<div id="calculating" class="panel panel-default">';
        echo '<div class="panel-body text-center">';
        echo '<h3 class="text-center m-b-0">Importing data in to the database</h3>';
        echo '<h6 class="text-center text-danger m-t-0">Please do not interrupt this process.....</h6>';
        echo '<div class="p-t-35 p-b-35"><img src="modules/tools/assets/images/copy-to-database.gif" width="80%" /></div>';
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        $_SESSION['student'] = $_POST['student_map_value'];

        echo "<script>ajax_progress('student');</script>";
    }
}
//================================Student info Ends==============================================
elseif (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'STAFF_INFO') {
    if ($_REQUEST['action'] != 'insert' && $_REQUEST['action'] != 'display' && $_REQUEST['action'] != 'process') {
        
        
    echo '<div class="row">';
    echo '<div class="col-md-6 col-md-offset-3">';
    echo '<form enctype="multipart/form-data" action="Modules.php?modname='.$_REQUEST[modname].'&action=insert&page_display=STAFF_INFO" method="POST" onSubmit="return map_upload_validation();">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center">';

    echo '<h5 class="text-center">Click on the Browse button to navigate to the Excel file in your computer\'s hard drive that has your data and select it. <b>After selecting, click Upload.</b></h5>';
    echo '<div class="form-group">';
    echo '<input type="hidden"  name="MAX_FILE_SIZE" value="2000000" />';
    echo '<div class="text-center"><label id="select-file-input"><input type="file" class="upload" id="file_id" name="file" /><i class="icon-upload"></i><br/><span>Click here to select a file</span></label></div>';
    echo '<p class="help-block">Supported file types: xls, xlsx</p>';
    echo '</div>';

    echo '</div>'; //.panel-body
    echo '<div class="panel-footer text-center"><input type="submit" class="btn btn-primary" value="Upload" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
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
        echo '<form action="Modules.php?modname=' . $_REQUEST[modname] . '&action=display&page_display=STAFF_INFO" name="staff_form"  method="POST">';
        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">Please create a one-to-one relationship between the fields in your spread sheet and the fields in the openSIS database by selecting the appropriate fields from the right column. After you are done, click Map it.</h4>';
        echo '</div>'; //.panel-heading

        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
	echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">These fields are in your Excel spread sheet</td><td width="200">&nbsp;</td><td>These are available fields in openSIS</td></tr>';
	echo '</thead>';
        $inputFileName = $_FILES['file']['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);
        $total_sheets = $objPHPExcel->getSheetCount(); // here 4  
        $allSheetName = $objPHPExcel->getSheetNames(); // array ([0]=>'student',[1]=>'teacher',[2]=>'school',[3]=>'college')  
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0); // first sheet  
        $highestRow = $objWorksheet->getHighestRow(); // here 5  
        $highestColumn = $objWorksheet->getHighestColumn(); // here 'E'  
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);  // here 5  
        for ($row = 1; $row <= $highestRow; ++$row) {
            for ($col = 0; $col <= $highestColumnIndex; ++$col) {
                $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                if (is_array($arr_data)) {
                    $arr_data[$row - 1][$col] = $value;
                }
            }
        }
        $_SESSION['data'] = $arr_data;

        $options = array('TITLE' => 'Salutation', 'FIRST_NAME' => 'First Name', 'LAST_NAME' => 'Last Name', 'MIDDLE_NAME' => 'Middle Name', 'EMAIL' => 'Email', 'PHONE' => 'Phone', 'PROFILE' => 'Profile', 'HOMEROOM' => 'Homeroom', 'BIRTHDATE' => 'Birthdate', 'ETHNICITY_ID' => 'Ethnicity', 'ALTERNATE_ID' => 'Alternate ID', 'PRIMARY_LANGUAGE_ID' => 'Primary Language', 'GENDER' => 'Gender', 'SECOND_LANGUAGE_ID' => 'Secondary Language', 'THIRD_LANGUAGE_ID' => 'Third Language', 'IS_DISABLE' => 'Disabled');
        $options+=array('USERNAME' => 'Username', 'PASSWORD' => 'Password');
        $options+=array('START_DATE' => 'Start Date', 'END_DATE' => 'End Date');
        $options+=array('CATEGORY' => 'Category', 'JOB_TITLE' => 'Job Title', 'JOINING_DATE' => 'Joining Date');
        $custom = DBGet(DBQuery('SELECT * FROM staff_fields'));
        foreach ($custom as $c) {
            $options['CUSTOM_' . $c['ID']] = $c['TITLE'];
        }

        $class = "odd";
        //  print_r($arr_data);
        $i = 0;
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
        echo '<div class="panel-footer text-center"><input type="submit" value="Map it" class="btn btn-primary" onClick="return valid_mapping_staff('.$i.');"  /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
        echo "</form>";
    }
    elseif ($_REQUEST['action'] == 'display') {
        $staff_keys = array_keys($_REQUEST['staff']);
        $staff_keys_string = implode(',', $staff_keys);
        $staff_values = implode(',', $_REQUEST['staff']);
        echo "<script>ajax_mapping('" . $staff_keys_string . "','" . $staff_values . "','staff');</script>";
        echo '<form action="Modules.php?modname='.$_REQUEST[modname].'&action=process&page_display=STAFF_INFO" name="STAFF_INFO_CONFIRM" method="POST">';
        echo '<div class="panel panel-default">';
        
        echo '<div class="panel-heading">';
        echo '<h4 class="text-center">Please create a one-to-one relationship between the fields in your spread sheet and the fields in the openSIS database by selecting the appropriate fields from the right column. After you are done, click Confirm.</h4>';
        echo '</div>'; //.panel-body
        
        echo '<div class="panel-body p-0">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
	echo '<thead>';
        echo '<tr class="bg-grey-200"><th width="260">These fields are in your Excel spread sheet</th><th width="200">&nbsp;</th><th>These are available fields in openSIS (Click to change the field values)</th></tr>';
	echo '</thead>';
        echo '<tbody>';
        

        $options = array('TITLE' => 'Salutation', 'FIRST_NAME' => 'First Name', 'LAST_NAME' => 'Last Name', 'MIDDLE_NAME' => 'Middle Name', 'EMAIL' => 'Email', 'PHONE' => 'Phone', 'PROFILE' => 'Profile', 'HOMEROOM' => 'Homeroom', 'BIRTHDATE' => 'Birthdate', 'ETHNICITY_ID' => 'Ethnicity', 'ALTERNATE_ID' => 'Alternate ID', 'PRIMARY_LANGUAGE_ID' => 'Primary Language', 'GENDER' => 'Gender', 'SECOND_LANGUAGE_ID' => 'Secondary Language', 'THIRD_LANGUAGE_ID' => 'Third Language', 'IS_DISABLE' => 'Disabled');
        $options+=array('USERNAME' => 'Username', 'PASSWORD' => 'Password');
        $options+=array('START_DATE' => 'Start Date', 'END_DATE' => 'End Date');
        $options+=array('CATEGORY' => 'Category', 'JOB_TITLE' => 'Job Title', 'JOINING_DATE' => 'Joining Date');
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
        echo '<div class="panel-footer text-center"><input type="submit" value="Confirm" class="btn btn-primary" onClick="return valid_mapping_staff('.$i.');" /> &nbsp; <a href="Modules.php?modname=' . $_REQUEST[modname] . '" class="btn btn-default">Cancel</a></div>';
        echo '</div>'; //.panel
        echo '</form>';
        
    } elseif ($_REQUEST['action'] == 'process') {
        
        
        
         echo '<div class="row">';
        echo '<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">';
        echo '<div id="calculating" class="panel panel-default">';
        echo '<div class="panel-body text-center">';
        echo '<h3 class="text-center m-b-0">Importing data in to the database</h3>';
        echo '<h6 class="text-center text-danger m-t-0">Please do not interrupt this process.....</h6>';
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

    echo '<h1 class="text-center m-b-0">Data Import Utility</h1>';
    echo '<p class="text-center text-grey m-b-30">Please select a profile to import their relevant data.</p>';

    echo '<div class="row">';
    echo '<div class="col-md-3 col-md-offset-3">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center p-t-35">';
    echo '<a href=Modules.php?modname=' . $_REQUEST[modname] . '&page_display=STUDENT_INFO><img src="modules/tools/assets/images/student.svg" width="60%" /><h4>Import Student Data</h4></a>';
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-3
    echo '<div class="col-md-3">';
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body text-center p-t-35">';
    echo '<a href=Modules.php?modname=' . $_REQUEST[modname] . '&page_display=STAFF_INFO><img src="modules/tools/assets/images/faculty.svg" width="60%" /><h4>Import Staff Data</h4></a>';
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    echo '</div>';
    echo '</div>';
}
?>
