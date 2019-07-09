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
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    $date = $_REQUEST['day'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['year'];
    if (count($_REQUEST['month_values'])) {
        foreach ($_REQUEST['month_values'] as $field_name => $month) {
            $_REQUEST['values'][$field_name] = $_REQUEST['year_values'][$field_name] . '-' . $month . '-' . $_REQUEST['day_values'][$field_name];
//            if (!VerifyDate($_REQUEST['values'][$field_name])) {
//                if ($_REQUEST['values'][$field_name] != '--')
//                    $note = '<IMG SRC=assets/warning_button.gif>The date you specified is not valid, so was not used.  The other data was saved.';
//                unset($_REQUEST['values'][$field_name]);
//            }
        }
    }
    if (count($_REQUEST['values']) && count($_REQUEST['student'])) {
        if ($_REQUEST['values']['NEXT_SCHOOL'] != '') {
            $next_school = $_REQUEST['values']['NEXT_SCHOOL'];
            unset($_REQUEST['values']['NEXT_SCHOOL']);
        }
        if ($_REQUEST['values']['SECTION_ID'] != '') {
            $sec_id = $_REQUEST['values']['SECTION_ID'];
            unset($_REQUEST['values']['SECTION_ID']);
        }
        if ($_REQUEST['values']['CALENDAR_ID']) {
            $calendar = clean_param($_REQUEST['values']['CALENDAR_ID'], PARAM_INT);
            unset($_REQUEST['values']['CALENDAR_ID']);
        }
        if ($_REQUEST['values']['GRADE_ID']) {
            $grade = clean_param($_REQUEST['values']['GRADE_ID'], PARAM_INT);
            unset($_REQUEST['values']['GRADE_ID']);
        }
        if ($_REQUEST['values']['start_date'] != '') {
            $str_date = $_REQUEST['values']['start_date'];
            unset($_REQUEST['values']['start_date']);
        }
        if ($_REQUEST['values']['drop_date'] != '') {
            $end_date = $_REQUEST['values']['drop_date'];
            unset($_REQUEST['values']['drop_date']);
        }
        
        if ($_REQUEST['values']['COMMENT'] != '') {
            $comment = $_REQUEST['values']['COMMENT'];
            unset($_REQUEST['values']['COMMENT']);
        }
        
        if ($_REQUEST['values']['COMMENT_DATE'] != '') {
            $com_date = $_REQUEST['values']['COMMENT_DATE'];
            unset($_REQUEST['values']['COMMENT_DATE']);
        }
        
        foreach ($_REQUEST['student'] as $student_id => $yes) {
            if ($yes == 'Y') {
                $students .= ",'$student_id'";
                $students_count++;
            }
        }
        
        foreach ($_REQUEST['values'] as $field => $value) {

            $arr = explode('[', $field);
            if ($arr[0] == 'language_id') {
                if (isset($value) && trim($value) != '') {
                    $value = paramlib_validation($field, $value);
                    $lng_id=DBGet(DBQuery('SELECT * FROM `language` WHERE language_name LIKE \'%'.$value.'%\''));
                    $lng_id=$lng_id[1]['LANGUAGE_ID'];
                    $update .= ',' . $field . "='$lng_id'";  
                }
            }
           elseif (($arr[0] != 'medical_info')&&($arr[0] != 'language_id')) {
                if (isset($value) && trim($value) != '') {
                    $value = paramlib_validation($field, $value);
                    $update .= ',' . $field . "='$value'";
                    $values_count++;
                }
            } else {
                $value = paramlib_validation($field, $value);

                $fields.=',' . $arr[1];
                $values.=',' . $value;
                $values_count++;
            }
        }
        
        $fields = explode(',', $fields);
        $values = explode(',', $values);
        $medical_student_id = explode(',', $students);
        $check_student_avail = DBGet(DBQuery('SELECT student_id from medical_info where SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool()));

        foreach ($check_student_avail as $stu_key => $stu_id) {
            foreach ($stu_id as $stu_id_k => $stu_id_v)
                $med_stu_id[] = $stu_id_v;
        }
        foreach ($_REQUEST['student'] as $student_id => $yes) {
            if ($yes == 'Y') {
                if($com_date!='' && $comment!='')
                {
                    DBQuery('INSERT INTO student_mp_comments (student_id,syear,marking_period_id,staff_id,comment,comment_date) Values ('.$student_id.','. UserSyear().','. UserMP().','.User('STAFF_ID').',\''.$comment.'\',\''.$com_date.'\')');
                    $note = '<div class="alert bg-success alert-styled-left">The specified information was applied to the selected students.</div>';   
                }
                $students_m_id[] = $student_id;
            }
        }

        if ($values_count && $students_count)
            if (trim($update) != '')
                DBQuery('UPDATE students SET ' . substr($update, 1) . ' WHERE STUDENT_ID IN (' . substr($students, 1) . ')');
            else {
                foreach ($students_m_id as $stu_k => $stu_id) {

                    if (in_array($stu_id, $med_stu_id)) {
                        if ($values[1] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[1] . '=\'' . $values[1] . '\' WHERE STUDENT_ID =' . $stu_id);
                        if ($values[2] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[2] . '=\'' . $values[2] . '\' WHERE STUDENT_ID  =' . $stu_id);
                        if ($values[3] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[3] . '=\'' . $values[3] . '\' WHERE STUDENT_ID =' . $stu_id);
                    }
                    else {
                        DBQuery('INSERT INTO medical_info (STUDENT_ID,SYEAR,SCHOOL_ID) VALUES (' . $stu_id . ',' . UserSyear() . ',' . UserSchool() . ')');

                        if ($values[1] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[1] . '=\'' . $values[1] . '\' WHERE STUDENT_ID =' . $stu_id);
                        if ($values[2] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[2] . '=\'' . $values[2] . '\' WHERE STUDENT_ID  =' . $stu_id);
                        if ($values[3] . trim() != '')
                            DBQuery('UPDATE medical_info SET ' . $fields[3] . '=\'' . $values[3] . '\' WHERE STUDENT_ID =' . $stu_id);
                    }
                }
            }
        elseif ($note)
            $note = substr($note, 0, strpos($note, '. '));
        elseif ($next_school == '' && !$calendar && $str_date=='' && $end_date=='')
            $note = '<div class="alert bg-danger alert-styled-left">No data was entered.</div>';
        if ($sec_id != '')
            DBQuery('UPDATE student_enrollment SET SECTION_ID=' . $sec_id . ' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        if ($next_school != '')
            DBQuery('UPDATE student_enrollment SET NEXT_SCHOOL=' . $next_school . ' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        if ($calendar)
            DBQuery('UPDATE student_enrollment SET CALENDAR_ID=' . $calendar . ' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        if ($grade)
            DBQuery('UPDATE student_enrollment SET GRADE_ID=' . $grade . ' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        if($str_date)
        {
            $enroll_code= DBGet(DBQuery('SELECT * FROM student_enrollment_codes WHERE SYEAR=' . UserSyear() . ' AND TYPE=\'Add\''));
           DBQuery('UPDATE student_enrollment SET START_DATE=\''.$str_date.'\',ENROLLMENT_CODE='.$enroll_code[1]['ID'].' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        }
        if($end_date)
        {
            $drop_code= DBGet(DBQuery('SELECT * FROM student_enrollment_codes WHERE SYEAR=' . UserSyear() . ' AND TYPE=\'Drop\''));
           DBQuery('UPDATE student_enrollment SET END_DATE=\''.$end_date.'\',DROP_CODE='.$drop_code[1]['ID'].' WHERE SYEAR=' . UserSyear() . ' AND STUDENT_ID IN (' . substr($students, 1) . ') ');
        }
        
        if (!$note)
            $note = '<div class="alert bg-success alert-styled-left">The specified information was applied to the selected students.</div>';
        unset($_REQUEST['modfunc']);
        unset($_REQUEST['values']);
        unset($_SESSION['_REQUEST_vars']['modfunc']);
        unset($_SESSION['_REQUEST_vars']['values']);
    }
    else {
        ShowErr('<div class="alert bg-warning alert-styled-left">You must choose at least one field and one student.</div>');
        for_error();
    }
}

DrawBC("Students > " . ProgramTitle());

if (!$_REQUEST['modfunc']) {
    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ',CAST(NULL AS CHAR(1)) AS CHECKBOX';

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM class=\"form-horizontal\" action=Modules.php?modname=$_REQUEST[modname]&modfunc=save METHOD=POST>";

//        if ($_REQUEST['category_id']) {
//            $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields WHERE CATEGORY_ID=\'' . $_REQUEST[category_id] . '\''), array(), array('TYPE'));
//        } else {
//            $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields'), array(), array('TYPE'));
//        }
        $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM student_field_categories WHERE ID NOT IN (3,5,7)'));
        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['category_id']);

        echo '<div class="row">';
        echo '<div class="col-md-12">';

        $panel_header = '<span class="heading-text">Category</span><div class="btn-group">';
        $panel_header .= '<SELECT name=category_id class="form-control" onchange="document.location.href=\'' . PreparePHP_SELF($tmp_REQUEST) . '&amp;category_id=\'+this.form.category_id.value;">';
        foreach ($categories_RET as $category) {
            $panel_header .= '<OPTION value=' . $category['ID'] . ($_REQUEST['category_id'] == $category['ID'] ? ' SELECTED' : '') . '>' . $category['TITLE'] . '</OPTION>';
        }
        $panel_header .= '</SELECT>';
        $panel_header .= '</div>';

        PopTable_wo_header('header', 'Fields to Assign', 'class="panel panel-default"', $panel_header);
        $fields = array();

        $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields WHERE CATEGORY_ID=\'' . $_REQUEST[category_id] . '\''), array(), array('TYPE'));
//        if ($_REQUEST[category_id] == 'all') {
//
//            $arr_g = array('First Name','Middle Name','Last Name','Estimated Grad. Date','Ethnicity', 'Common Name', 'Gender', 'Language', 'Email', 'Phone');
//            foreach ($arr_g as $v_g) {
//                if ($v_g == 'Common Name') {
//                    $v_g = 'common_name';
//                }
//                
//                if($v_g=='Estimated Grad. Date' || $v_g=='Date of Birth')
//                    array_push($fields, '<div class=form-group>' .$v_g.' '. _makeDateInput($v_g) . '</div>');
//            else
//                array_push($fields, '<div class=form-group>' . _makeTextInput($v_g) . '</div>');
//            }
//
//
////        echo '<div class="col-md-12">';  
////        echo '<span class="heading-text">Section</span><div class="btn-group">';
////        
////        
////        $school_id = UserSchool();
////        $sql = 'SELECT * FROM school_gradelevel_sections WHERE SCHOOL_ID=\''.$school_id.'\' ORDER BY SORT_ORDER';
////        $QI = DBQuery($sql);
////        $sec_RET = DBGet($QI);
////        unset($options);
////        if(count($sec_RET))
////        {
////                foreach($sec_RET as $value)
////                        $options[$value['ID']] = $value['NAME'];
////        }
////
////        echo _makeSelectInput('SECTION_ID',$options);
////        echo'</div>';  
//
//
//
//            $arr_m = array('Physician', 'Physician\'s Phone', 'Preferred Hospital');
//
//            foreach ($arr_m as $v_m) {
//
//                if (trim($v_m) == 'Physician') {
//                    $v_m_n = 'medical_info[PHYSICIAN]';
//                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
//                }if (trim($v_m) == 'Physician\'s Phone') {
//                    $v_m_n = 'medical_info[PHYSICIAN_PHONE]';
//                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
//                } else if (trim($v_m) == 'Preferred Hospital') {
//                    $v_m_n = 'medical_info[PREFERRED_HOSPITAL]';
//                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
//                }
//            }
//        }
       
        if ($_REQUEST[category_id] == 2) {

            $arr = array(' Physician', 'Physician\'s Phone', 'Preferred Hospital');
            foreach ($arr as $v_m) {
                if (trim($v_m) == 'Physician') {
                    $v_m_n = 'medical_info[PHYSICIAN]';
                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
                }if (trim($v_m) == 'Physician\'s Phone') {
                    $v_m_n = 'medical_info[PHYSICIAN_PHONE]';
                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
                } else if (trim($v_m) == 'Preferred Hospital') {
                    $v_m_n = 'medical_info[PREFERRED_HOSPITAL]';
                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_m_n) . '</div>');
                }
            }
        }
        
        if ($_REQUEST[category_id] == 1 || $_REQUEST[category_id] == '') {
            $arr = array('First Name','Middle Name','Last Name','Estimated Grad. Date','Ethnicity', 'Common Name','Date of Birth', 'Gender', 'Language', 'Email', 'Phone');

            foreach ($arr as $v_g) {
                if ($v_g == 'Common Name' || $v_g == 'First Name' || $v_g == 'Middle Name' || $v_g == 'Last Name') {
                    $v_g = str_replace(' ','_',strtolower($v_g));
                }
                 if($v_g=='Estimated Grad. Date' || $v_g=='Date of Birth')
                 {
                     if($v_g=='Estimated Grad. Date')
                     {
                         $nm='estimated_grad_date';
                         $cn=1;
                     }
                     if($v_g=='Date of Birth')
                     {
                         $nm='birthdate';
                         $cn=2;
                     }
                    array_push($fields, '<div class=form-group><label class="control-label col-lg-4 text-right">' .$v_g.'</label><div class="col-lg-8">'. _makeDateInput($nm,$cn) . '</div></div>');
                 }
            else
                if($v_g == 'Gender')
                     array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">Gender</label><div class="col-lg-8">' . _makeSelectInput($v_g,array('Male' => 'Male', 'Female' => 'Female')) . '</div></div>');
                else
                    array_push($fields, '<div class="form-group">' . _makeTextInput($v_g) . '</div>');
            }
            $school_id = UserSchool();
            $sql = 'SELECT * FROM school_gradelevel_sections WHERE SCHOOL_ID=\'' . $school_id . '\' ORDER BY SORT_ORDER';
            $QI = DBQuery($sql);
            $sec_RET = DBGet($QI);
            unset($options);
            if (count($sec_RET)) {
                foreach ($sec_RET as $value)
                    $options[$value['ID']] = $value['NAME'];
            }

//        echo _makeSelectInput('SECTION_ID',$options);
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">Section</label><div class="col-lg-8">' . _makeSelectInput('SECTION_ID', $options) . '</div></div>');
//        echo'</div>'; 


            $grade_level_RET = DBGet(DBQuery('SELECT * FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER ASC'));
            $options = array();
            if (count($grade_level_RET)) {
                foreach ($grade_level_RET as $grade)
                    $options[$grade['ID']] = $grade['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">School Gradelevel</label><div class="col-lg-8">' . _makeSelectInput('GRADE_ID', $options) . '</div></div>');
             $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields WHERE CATEGORY_ID=1'), array(), array('TYPE'));
            }
        
         if ($_REQUEST[category_id] == 6) {
            $arr = array('Start Date','Drop Date');

            foreach ($arr as $v_g) { 
                 if($v_g=='Start Date' || $v_g=='Drop Date')
                 {
                     if($v_g=='Start Date')
                     {
                         $nm='start_date';
                         $cn=1;
                     }
                     if($v_g=='Drop Date')
                     {
                         $nm='drop_date';
                         $cn=2;
                     }
                         
                    array_push($fields, '<div class=form-group><label class="control-label text-right col-lg-4">' .$v_g.'</label><div class="col-lg-8">'. _makeDateInput($nm,$cn) . '</div></div>');
                 }
            else
                array_push($fields, '<div class="form-group">' . _makeTextInput($v_g) . '</div>');
            }
            $schools_RET = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID!=\'' . UserSchool() . '\''));
            $options = array(UserSchool() => 'Next grade at current school', '0' => 'Retain', '-1' => 'Do not enroll after this school year');
            if (count($schools_RET)) {
                foreach ($schools_RET as $school)
                    $options[$school['ID']] = $school['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4" for="CUSTOM_' . $field['ID'].'">Rolling Retention / Options</label><div class="col-lg-8">' . _makeSelectInput('NEXT_SCHOOL', $options) . '</div></div>');

            $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,DEFAULT_CALENDAR,TITLE FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY DEFAULT_CALENDAR ASC'));
            $options = array();
            if (count($calendars_RET)) {
                foreach ($calendars_RET as $calendar)
                    $options[$calendar['CALENDAR_ID']] = $calendar['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4" for="CUSTOM_' . $field['ID'].'">Calendar</label><div class="col-lg-8">' . _makeSelectInput('CALENDAR_ID', $options) . '</div></div>');
             $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields WHERE CATEGORY_ID=\'' . $_REQUEST[category_id] . '\''), array(), array('TYPE'));
        }
        
        if ($_REQUEST[category_id] == 4) {
            $arr = array('Date','Comments');

            foreach ($arr as $v_g) { 
                if ($v_g == 'Comments' ) {
                    $nm = 'COMMENT';
                }
                 if($v_g=='Date')
                 {
                         $nm='COMMENT_DATE';
                     
                    array_push($fields, '<div class=form-group><label class="control-label text-right col-lg-4">' .$v_g.'</label><div class="col-lg-8">'. _makeDateInput($nm) . '</div></div>');
                 }
            else
                array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">' .$v_g.'</label><div class="col-lg-8">' . _makeTextareaInput($nm) . '</div></div>');
            }
            
             $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields WHERE CATEGORY_ID=\'' . $_REQUEST[category_id] . '\''), array(), array('TYPE'));
        }

        if (count($fields_RET['text'])) {
            foreach ($fields_RET['text'] as $field) {
                $title = strtolower(trim($field['TITLE']));
                if (strpos(trim($field['TITLE']), ' ') != 0) {
                    $p1 = substr(trim($field['TITLE']), 0, strpos(trim($field['TITLE']), ' '));
                    $p2 = substr(trim($field['TITLE']), strpos(trim($field['TITLE']), ' ') + 1);
                    $title = strtolower($p1 . '_' . $p2);
                }

                $query = DBGet(DBQuery('SELECT * FROM students LIMIT 0,1'));
                $query = $query[1];
                $f = 0;
                foreach ($query as $k => $v) {
                    if (strtolower($k) == strtolower($title))
                        $f = 1;
                }
                if ($f == 0) {
                    if (trim($title) == 'physician')
                        $title = 'medical_info[PHYSICIAN]';
                    if (trim($title) == 'physician_phone')
                        $title = 'medical_info[PHYSICIAN_PHONE]';
                    else if (trim($title) == 'preferred_hospital')
                        $title = 'medical_info[PREFERRED_HOSPITAL]';
                }
                array_push($fields, '<div class="form-group">' . _makeTextInput($title) . '</div>');
            }
        }
        if (count($fields_RET['numeric'])) {
            foreach ($fields_RET['numeric'] as $field)
                array_push($fields, '<div class="form-group">' . _makeTextInput('CUSTOM_' . $field['ID'], true) . '</div>');
        }
        if (count($fields_RET['date'])) {
            $i=3;
            foreach ($fields_RET['date'] as $field)
            {
                array_push($fields, '<div class="form-group"><label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'].'">' . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeDateInput('CUSTOM_' . $field['ID'],$i) . '</div></div>');
            $i++;
            }
        }
        if (count($fields_RET['select'])) {
            foreach ($fields_RET['select'] as $field) {
                if ($field[TITLE] == 'Ethnicity' || $field[TITLE] == 'Gender' || $field[TITLE] == 'Language') {
                    $select_options = array();
                    $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
                    $options = explode("\r", $field['SELECT_OPTIONS']);
                    if (count($options)) {
                        foreach ($options as $option)
                            $select_options[$option] = $option;
                    }

                    array_push($fields, "<div class=\"form-group\"><label class=\"control-label col-lg-4 text-right\" for=\"CUSTOM_" . $field['ID']."\">$field[TITLE]</label><div class=\"col-lg-8\">" . _makeSelectInput($field[TITLE], $select_options) . '</div></div>');
                } else {
                    $select_options = array();
                    $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
                    $options = explode("\r", $field['SELECT_OPTIONS']);
                    if (count($options)) {
                        foreach ($options as $option)
                            $select_options[$option] = $option;
                    }

                    array_push($fields, "<div class=\"form-group\"><label class=\"control-label col-lg-4 text-right\" for=\"CUSTOM_" . $field['ID']."\">$field[TITLE]</label><div class=\"col-lg-8\">" . _makeSelectInput('CUSTOM_' . $field['ID'], $select_options) . '</div></div>');
                }
            }
        }
        if (count($fields_RET['textarea'])) {
            foreach ($fields_RET['textarea'] as $field) {
                array_push($fields, '<div class="form-group"><label class="control-label col-lg-4 text-right" for="CUSTOM_' . $field['ID'].'">' . $field['TITLE'] . '</label><div class="col-lg-8">' . _makeTextareaInput('CUSTOM_' . $field['ID']) . '</div></div>');
            }
        }
        /*if (!$_REQUEST['category_id'] || $_REQUEST['category_id'] == '1') {

            $schools_RET = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID!=\'' . UserSchool() . '\''));
            $options = array(UserSchool() => 'Next grade at current school', '0' => 'Retain', '-1' => 'Do not enroll after this school year');
            if (count($schools_RET)) {
                foreach ($schools_RET as $school)
                    $options[$school['ID']] = $school['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4" for="CUSTOM_' . $field['ID'].'">Rolling Retention / Options</label><div class="col-lg-8">' . _makeSelectInput('NEXT_SCHOOL', $options) . '</div></div>');

            $calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,DEFAULT_CALENDAR,TITLE FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY DEFAULT_CALENDAR ASC'));
            $options = array();
            if (count($calendars_RET)) {
                foreach ($calendars_RET as $calendar)
                    $options[$calendar['CALENDAR_ID']] = $calendar['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4" for="CUSTOM_' . $field['ID'].'">Calendar</label><div class="col-lg-8">' . _makeSelectInput('CALENDAR_ID', $options) . '</div></div>');
        }*/

        /*if ($_REQUEST['category_id'] == '') {
//         echo '<div class="col-md-12">';  
//        echo '<span class="heading-text">Section</span><div class="btn-group">';
//        

            $school_id = UserSchool();
            $sql = 'SELECT * FROM school_gradelevel_sections WHERE SCHOOL_ID=\'' . $school_id . '\' ORDER BY SORT_ORDER';
            $QI = DBQuery($sql);
            $sec_RET = DBGet($QI);
            unset($options);
            if (count($sec_RET)) {
                foreach ($sec_RET as $value)
                    $options[$value['ID']] = $value['NAME'];
            }

//        echo _makeSelectInput('SECTION_ID',$options);
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">Section</label><div class="col-lg-8">' . _makeSelectInput('SECTION_ID', $options) . '</div></div>');
//        echo'</div>'; 


            $grade_level_RET = DBGet(DBQuery('SELECT * FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER ASC'));
            $options = array();
            if (count($grade_level_RET)) {
                foreach ($grade_level_RET as $grade)
                    $options[$grade['ID']] = $grade['TITLE'];
            }
            array_push($fields, '<div class="form-group"><label class="control-label text-right col-lg-4">School Gradelevel</label><div class="col-lg-8">' . _makeSelectInput('GRADE_ID', $options) . '</div></div>');
        }*/


        $radio_count = count($fields_RET['radio']);
        if ($radio_count) {
            //echo '<TABLE cellpadding=5>';
            //echo '<TR>';
            for ($i = 1; $i <= $radio_count; $i++) {
                array_push($fields, '<label class="col-lg-4">&nbsp;</label><div class="col-lg-8">' . _makeCheckboxInput('CUSTOM_' . $fields_RET['radio'][$i]['ID'], $fields_RET['radio'][$i]['TITLE']) . '</div>');
                //if ($i % 5 == 0 && $i != $radio_count)
                //echo '</TR><TR>';
            }
            //echo '</TD></TR>';
            //echo '</TABLE>';
        }

        //$test = array_map('htmlspecialchars', $fields);

        $col1html = '<div class="col-md-6">';
        $col2html = '<div class="col-md-6">';
        $item = '';

        $col1 = 1;
        $col2 = 0;

        //$i = 0;
        foreach ($fields as $field) {

            if (strpos($field, '<h5') === false) {
                if ($col1 == 1) {
                    $col1html .= $field;
                    $col1 = 0;
                    $col2 = 1;
                } else {
                    $col2html .= $field;
                    $col1 = 1;
                    $col2 = 0;
                }
            } else {
                $item .= '<div class="row">' . $col1html . '</div>' . $col2html . '</div></div>';
                $col1html = '<div class="col-md-6">';
                $col2html = '<div class="col-md-6">';
                $col1 = 1;
                $col2 = 0;
                $item .= $field;
            }
        }
        $item .= '<div class="row">' . $col1html . '</div>' . $col2html . '</div></div>';
        echo $item;

        PopTable_wo_header('footer');

        echo '</div>'; //.col-md-6
        echo '</div>'; //.row
    } elseif ($note)
        echo $note;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('activity');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-md-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('absences');
    $extra['search'] .= '</div>'; //.col-md-6
    $extra['search'] .= '</div>'; //.row

    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');">');
    $extra['new'] = true;

    Search('student_id', $extra);
    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">Choose course</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo count($subjects_RET) . ((count($subjects_RET) == 1) ? ' Subject was' : ' Subjects were') . ' found.<br>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>Subject</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-4"><div id="course_modal"></div></div>';
    echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal



    if ($_REQUEST['search_modfunc'] == 'list' && $_SESSION['count_stu'] != '0') {
        unset($_SESSION['count_stu']);
        echo "<div class=\"text-center m-b-20\">" . SubmitButton('Assign Info to Selected Students', '', 'class="btn btn-primary"') . "</div>";
    }
    echo '</FORM>';
}

function _makeChooseCheckbox($value, $title = '') {
    global $THIS_RET;

    return "<INPUT type=checkbox name=student[" . $THIS_RET['STUDENT_ID'] . "] value=Y id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);'>";
}

function _makeTextInput($column, $numeric = false) {
    if ($numeric === true)
        $options = 'size=3 maxlength=11';
    else
        $options = 'size=25';

    $title = str_replace('medical_info[', '', $column);
    $title = str_replace(']', '', $title);
    $title = str_replace('_', ' ', $title);
    $title = ucwords(strtolower($title));
    if($column=='Language')
        $column='language_id';
    if ($column == 'physician' || $column == 'physician_phone' || $column == 'preferred_hospital')
        return TextInput('', $column, $title, $options);
    else
        return TextInput('', 'values[' . $column . ']', $title, $options);
}

function _makeTextareaInput($column, $numeric = false) {
    return TextAreaInput('', 'values[' . $column . ']');
}

function _makeDateInput($column,$counter=1) {
    return DateInputAY('', 'values[' . $column . ']', $counter, false, 'MM/DD/YYYY');
//    return DateInput('', 'values[' . $column . ']', '');
}

function _makeSelectInput($column, $options) {
    return SelectInput('', 'values[' . $column . ']', '', $options, 'N/A');
}

function _makeCheckboxInput($column, $name) {
    return CheckboxInputSwitch('', 'values[' . $column . ']', $name, '', true, 'Yes', 'No', '', 'switch-success');
}

?>