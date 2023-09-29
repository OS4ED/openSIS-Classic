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
include_once('functions/MiscExportFnc.php');
unset($extra['DATE']);
$extra['search'] .= '<TR><TD align=center colspan=2><TABLE><TR><TD><DIV id=fields_div></DIV></TD></TR></TABLE></TD></TR>';
$extra['new'] = true;
$_openSIS['CustomFields'] = true;
if ($_REQUEST['fields']['PARENTS']) {
    $extra['SELECT'] .= ',ssm.STUDENT_ID AS PARENTS';
    $view_other_RET['ALL_CONTACTS'][1]['VALUE'] = 'Y';
    if ($_REQUEST['relation'] != '') {
        $_openSIS['makeParents'] = $_REQUEST['relation'];
        $extra['students_join_address'] .= ' AND EXISTS (SELECT \'\' FROM students_join_people sjp WHERE sjp.STUDENT_ID=sa.STUDENT_ID AND LOWER(sjp.RELATIONSHIP) LIKE \'' . strtolower($_REQUEST['relation']) . '%\') ';
    }
}
$extra['SELECT'] .= ',ssm.NEXT_SCHOOL,ssm.CALENDAR_ID,ssm.SECTION_ID,ssm.SYEAR,s.*';
if ($_REQUEST['fields']['FIRST_INIT'])
    $extra['SELECT'] .= ',substr(s.FIRST_NAME,1,1) AS FIRST_INIT';
if (!$extra['functions'])
    $extra['functions'] = array('NEXT_SCHOOL' => '_makeNextSchool', 'CALENDAR_ID' => '_makeCalendar', 'SCHOOL_ID' => 'GetSchool', 'PARENTS' => 'makeParents', 'BIRTHDATE' => 'ProperDate', 'SECTION_ID' => '_makeSectionVal');
if ($_REQUEST['search_modfunc'] == 'list') {
    if (!$fields_list) {
        $fields_list = array(
            'FULL_NAME' => (Preferences('NAME') == 'Common' ? _lastCommon : _lastFirstM),
            'FIRST_NAME' => _first,
            'FIRST_INIT' => _firstInitial,
            'LAST_NAME' => _last,
            'MIDDLE_NAME' => _middle,
            'ETHNICITY_ID' => _ethnicity,
            'LANGUAGE_ID' => _language,
            'NAME_SUFFIX' => _suffix,
            'GENDER' => _gender,
            'STUDENT_ID' => _studentId,
            'GRADE_ID' => _grade,
            'SECTION_ID' => _section,
            'SCHOOL_ID' => _school,
            'NEXT_SCHOOL' => _rollingRetentionOptions,
            'CALENDAR_ID' => _calendar,
            'USERNAME' => _username,
            'PASSWORD' => _password,
            'ALT_ID' => _alternateId,
            'BIRTHDATE' => _dob,
            'EMAIL' => _emailId,
            'ADDRESS' => _address,
            'CITY' => _city,
            'STATE' => _state,
            'ZIPCODE' => _zipCode,
            'PHONE' => _phone,
            'MAIL_ADDRESS' => _mailingAddress,
            'MAIL_CITY' => _mailingCity,
            'MAIL_STATE' => _mailingState,
            'MAIL_ZIPCODE' => _mailingZipcode,
            'PARENTS' => 'contacts'
        );
        if ($extra['field_names'])
            $fields_list += $extra['field_names'];

        $periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

        foreach ($periods_RET as $period)
            $fields_list['PERIOD_' . $period['PERIOD_ID']] = $period['TITLE'] . ' Teacher - Room';

        $fields_list = array_merge($fields_list, array('PRIM_STUDENT_RELATION' => 'Primary Relationship', 'PRI_FIRST_NAME' => 'Primary First Name', 'PRI_LAST_NAME' => 'Primary Last Name', 'PRIM_HOME_PHONE' => 'Primary Home Phone', 'PRIM_WORK_PHONE' => 'Primary Work Phone', 'PRIM_CELL_PHONE' => 'Primary Cell/Mobile Phone', 'PRIM_EMAIL' => 'Primary Email', 'PRIM_CUSTODY' => 'Primary Custody of Student', 'PRIM_ADDRESS' => 'Primary Address', 'PRIM_STREET' => 'Primary Street', 'PRIM_CITY' => 'Primary City', 'PRIM_STATE' => 'Primary State', 'PRIM_ZIPCODE' => 'Primary Zip/Postal Code'));

        $fields_list = array_merge($fields_list,  array('SEC_STUDENT_RELATION' => 'Secondary Relationship', 'SEC_FIRST_NAME' => 'Secondary First Name', 'SEC_LAST_NAME' => 'Secondary Last Name', 'SEC_HOME_PHONE' => 'Secondary Home Phone', 'SEC_WORK_PHONE' => 'Secondary Work Phone', 'SEC_CELL_PHONE' => 'Secondary Cell/Mobile Phone', 'SEC_EMAIL' => 'Secondary Email', 'SEC_CUSTODY' => 'Secondary Custody of Student', 'SEC_ADDRESS' => 'Secondary Address', 'SEC_STREET' => 'Secondary Street', 'SEC_CITY' => 'Secondary City', 'SEC_STATE' => 'Secondary State', 'SEC_ZIPCODE' => 'Secondary Zip/Postal Code'));
    }
    $custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE FROM custom_fields WHERE SYSTEM_FIELD !=\'Y\' ORDER BY SORT_ORDER'));
    foreach ($custom_RET as $field) {
        if (!$fields_list[$field['TITLE']]) {
            $title = strtolower(trim($field['TITLE']));
            if (strpos(trim($field['TITLE']), ' ') != 0) {
                $p1 = substr(trim($field['TITLE']), 0, strpos(trim($field['TITLE']), ' '));
                $p2 = substr(trim($field['TITLE']), strpos(trim($field['TITLE']), ' ') + 1);
                $title = strtolower($p1 . '_' . $p2);
            }
            $fields_list[$title] = $field['TITLE'];
            $extra['SELECT'] .= ',REPLACE(s.CUSTOM_' . $field['ID'] . ',"||",",") AS CUSTOM_' . $field['ID'];
        }
    }
    foreach ($periods_RET as $period) {
        if ($_REQUEST['month_include_active_date'])
            $date = $_REQUEST['day_include_active_date'] . '-' . $_REQUEST['month_include_active_date'] . '-' . $_REQUEST['year_include_active_date'];
        else
            $date = DBDate();

        if ($_REQUEST['fields']['PERIOD_' . $period['PERIOD_ID']] == 'Y') {

            $extra['SELECT'] .= ',(SELECT GROUP_CONCAT(DISTINCT CONCAT(COALESCE(st.FIRST_NAME,\' \'),\' \',COALESCE(st.LAST_NAME,\' \'),\' - \',COALESCE(r.TITLE,\' \'))) FROM staff st,schedule ss,course_periods cp,course_period_var cpv,rooms r WHERE ss.STUDENT_ID=ssm.STUDENT_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND cp.TEACHER_ID=st.STAFF_ID AND cpv.PERIOD_ID=\'' . $period['PERIOD_ID'] . '\' AND (\'' . $date . '\' BETWEEN ss.START_DATE AND ss.END_DATE OR \'' . $date . '\'>=ss.START_DATE AND ss.END_DATE IS NULL) LIMIT 1) AS PERIOD_' . $period['PERIOD_ID'];
        }
    }

    if ($openSISModules['Food_Service'] && ($_REQUEST['fields']['FS_ACCOUNT_ID'] == 'Y' || $_REQUEST['fields']['FS_DISCOUNT'] == 'Y' || $_REQUEST['fields']['FS_STATUS'] == 'Y' || $_REQUEST['fields']['FS_BARCODE'] == 'Y' || $_REQUEST['fields']['FS_BALANCE'] == 'Y')) {
        $extra['FROM'] = ',FOOD_SERVICE_STUDENT_ACCOUNTS fssa';
        $extra['WHERE'] = ' AND fssa.STUDENT_ID=ssm.STUDENT_ID';
        if ($_REQUEST['fields']['FS_ACCOUNT_ID'] == 'Y')
            $extra['SELECT'] .= ',fssa.ACCOUNT_ID AS FS_ACCOUNT_ID';
        if ($_REQUEST['fields']['FS_DISCOUNT'] == 'Y')
            $extra['SELECT'] .= ',coalesce(fssa.DISCOUNT,\'Full\') AS FS_DISCOUNT';
        if ($_REQUEST['fields']['FS_STATUS'] == 'Y')
            $extra['SELECT'] .= ',coalesce(fssa.STATUS,\'Active\') AS FS_STATUS';
        if ($_REQUEST['fields']['FS_BARCODE'] == 'Y')
            $extra['SELECT'] .= ',fssa.BARCODE AS FS_BARCODE';
        if ($_REQUEST['fields']['FS_BALANCE'] == 'Y')
            $extra['SELECT'] .= ',(SELECT fsa.BALANCE FROM FOOD_SERVICE_ACCOUNTS fsa WHERE fsa.ACCOUNT_ID=fssa.ACCOUNT_ID) AS FS_BALANCE';
        $fields_list += array('FS_ACCOUNT_ID' => 'F/S Account ID', 'FS_DISCOUNT' => 'F/S Discount', 'FS_STATUS' => 'F/S Status', 'FS_BARCODE' => 'F/S Barcode', 'FS_BALANCE' => 'F/S Balance',);
    }
    if ($_REQUEST['fields']['USERNAME'] == 'Y') {
        $extra['SELECT'] .= ',la.username';
        $extra['FROM'] .= ', login_authentication la';
        $extra['WHERE'] .= ' AND la.USER_ID=s.STUDENT_ID AND la.profile_id=3  ';
    }
    if ($_REQUEST['fields']) {
        foreach ($_REQUEST['fields'] as $field => $on) {
            $columns[strtoupper($field)] = $fields_list[$field];
            if (!$fields_list[$field]) {
                $get_column = DBGet(DBQuery('SELECT ID,TITLE FROM custom_fields  ORDER BY SORT_ORDER'));
                foreach ($get_column as $COLUMN_NAME) {
                    if ('CUSTOM_' . $COLUMN_NAME['ID'] == $field)
                        $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                    else if (str_replace(" ", "_", strtoupper($COLUMN_NAME['TITLE'])) == strtoupper($field))
                        $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                }
                if (strpos($field, 'CUSTOM') === 0) {
                    $custom_id = str_replace("CUSTOM_", "", $field);
                    $custom_RET = DBGet(DBQuery('SELECT TYPE FROM custom_fields WHERE ID=' . $custom_id));
                    if ($custom_RET[1]['TYPE'] == 'date' && !$extra['functions'][$field]) {
                        $extra['functions'][$field] = 'ProperDate';
                    } elseif ($custom_RET[1]['TYPE'] == 'codeds' && !$extra['functions'][$field]) {
                        $extra['functions'][$field] = 'DeCodeds';
                    }
                }
            }
        }
        $RET = GetStuList($extra);
        //        echo '<pre>';
        //        print_r($RET);
        //        echo $RET[1]['ETHNICITY_ID'];
        //        exit;
        $i = 1;
        foreach ($RET as $value) {
            if ($RET[$i]['LANGUAGE_ID'] != '') {
                $sql_language = DBGet(DBQuery("SELECT language_name FROM language WHERE language_id=" . $RET[$i]['LANGUAGE_ID']));
                $RET[$i]['LANGUAGE_ID'] = $sql_language[1]['LANGUAGE_NAME'];
            }
            if ($RET[$i]['ETHNICITY_ID'] != '') {
                $sql_ethinicity = DBGet(DBQuery("SELECT ethnicity_name FROM ethnicity WHERE ethnicity_id=" . $RET[$i]['ETHNICITY_ID']));
                $RET[$i]['ETHNICITY_ID'] = $sql_ethinicity[1]['ETHNICITY_NAME'];
            }
            $i = $i + 1;
        }
        $list_attr = DBGet(DBQuery("SHOW COLUMNS FROM `students` "));
        foreach ($list_attr as $data) {

            $list_attr_val[] = strtoupper($data['FIELD']);
        }


        foreach ($columns as $stu_indx => $stu_data) {
            $f = 0;

            if (!in_array($stu_indx, $list_attr_val)) {
                $f = 1;
            } else {
                $f = 0;
                break;
            }
        }

        if ($_REQUEST['ADDRESS_ID'] || $_REQUEST['fields']['ADDRESS'] || $_REQUEST['fields']['CITY'] || $_REQUEST['fields']['STATE'] || $_REQUEST['fields']['ZIPCODE'] || $_REQUEST['fields']['PHONE'] || $_REQUEST['fields']['MAIL_ADDRESS'] || $_REQUEST['fields']['MAIL_CITY'] || $_REQUEST['fields']['MAIL_STATE'] || $_REQUEST['fields']['MAIL_ZIPCODE'] || $_REQUEST['fields']['PARENTS']) {


            foreach ($RET as $stu_key => $stu_val) {

                $add_reslt = "SELECT CASE WHEN (sa.STREET_ADDRESS_1 IS NOT NULL && sa.STREET_ADDRESS_2 IS NOT NULL) THEN CONCAT(sa.STREET_ADDRESS_1,' ,',sa.STREET_ADDRESS_2) ELSE sa.STREET_ADDRESS_1 END as ADDRESS, sa.CITY,sa.STATE,sa.ZIPCODE,COALESCE((SELECT STREET_ADDRESS_1 FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' ORDER BY syear DESC LIMIT 0,1),sa.STREET_ADDRESS_1) AS MAIL_ADDRESS,COALESCE((SELECT CITY FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' ORDER BY syear DESC LIMIT 0,1),sa.CITY) AS MAIL_CITY,COALESCE((SELECT STATE FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' ORDER BY syear DESC LIMIT 0,1),sa.STATE) AS MAIL_STATE,COALESCE((SELECT ZIPCODE FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' ORDER BY syear DESC LIMIT 0,1),sa.ZIPCODE) AS MAIL_ZIPCODE  from student_address sa WHERE sa.TYPE='HOME ADDRESS' AND sa.STUDENT_ID = '" . $stu_val['STUDENT_ID'] . "' ORDER BY syear DESC";

                $res = DBGet(DBQuery($add_reslt));

                foreach ($res[1] as $add_key => $add_val) {
                    $RET[$stu_key][$add_key] = $add_val;
                }

                if (empty($res[1]) && $f == 1)
                    unset($RET[$stu_key]);
            }
        }

        if ($_REQUEST['fields']['PRI_FIRST_NAME'] || $_REQUEST['fields']['PRI_LAST_NAME'] || $_REQUEST['fields']['PRIM_ADDRESS'] || $_REQUEST['fields']['PRIM_STREET'] || $_REQUEST['fields']['PRIM_CITY'] || $_REQUEST['fields']['PRIM_STATE'] || $_REQUEST['fields']['PRIM_ZIPCODE'] || $_REQUEST['fields']['PRIM_STUDENT_RELATION'] || $_REQUEST['fields']['PRIM_HOME_PHONE'] || $_REQUEST['fields']['PRIM_WORK_PHONE'] || $_REQUEST['fields']['PRIM_CELL_PHONE'] || $_REQUEST['fields']['PRIM_EMAIL'] || $_REQUEST['fields']['PRIM_CUSTODY'] || $_REQUEST['fields']['SEC_FIRST_NAME'] || $_REQUEST['fields']['SEC_LAST_NAME'] || $_REQUEST['fields']['SEC_ADDRESS'] || $_REQUEST['fields']['SEC_STREET'] || $_REQUEST['fields']['SEC_CITY'] || $_REQUEST['fields']['SEC_STATE'] || $_REQUEST['fields']['SEC_ZIPCODE'] || $_REQUEST['fields']['SEC_STUDENT_RELATION'] || $_REQUEST['fields']['SEC_HOME_PHONE'] || $_REQUEST['fields']['SEC_WORK_PHONE'] || $_REQUEST['fields']['SEC_CELL_PHONE'] || $_REQUEST['fields']['PRIM_EMAIL'] || $_REQUEST['fields']['PRIM_CUSTODY']) {

            foreach ($RET as $stu_key => $stu_val) {
                $pri_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=' . $stu_val['STUDENT_ID'] . ' AND EMERGENCY_TYPE=\'Primary\''));
                $sec_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=' . $stu_val['STUDENT_ID'] . ' AND EMERGENCY_TYPE=\'Secondary\''));

                if (!empty($pri_par_id)) {
                    $Stu_prim_address = DBGet(DBQuery('SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE,sjp.RELATIONSHIP as PRIM_STUDENT_RELATION,p.home_phone as PRIM_HOME_PHONE,p.work_phone as PRIM_WORK_PHONE,p.cell_phone as PRIM_CELL_PHONE, p.email as PRIM_EMAIL, p.custody as PRIM_CUSTODY FROM  student_address sa,people p,students_join_people sjp WHERE  sa.PEOPLE_ID=p.STAFF_ID  AND p.STAFF_ID=\'' . $pri_par_id[1]['PERSON_ID'] . '\' AND sjp.PERSON_ID=p.STAFF_ID LIMIT 1'));
                    $contacts_RET = $Stu_prim_address[1];
                    if ($contacts_RET['PRIM_CUSTODY'] == 'Y')
                        $contacts_RET['PRIM_CUSTODY'] = 'Yes';
                    else
                        $contacts_RET['PRIM_CUSTODY'] = 'No';
                }

                if (!empty($sec_par_id)) {
                    $Stu_sec_address = DBGet(DBQuery('SELECT p.FIRST_NAME as SEC_FIRST_NAME,p.LAST_NAME as SEC_LAST_NAME,sa.STREET_ADDRESS_1 as SEC_ADDRESS,sa.STREET_ADDRESS_2 as SEC_STREET,sa.type as SA_TYPE,sa.CITY as SEC_CITY,sa.STATE as SEC_STATE,sa.ZIPCODE as SEC_ZIPCODE,sjp.RELATIONSHIP as SEC_STUDENT_RELATION,sjp.EMERGENCY_TYPE,p.home_phone as SEC_HOME_PHONE,p.work_phone as SEC_WORK_PHONE,p.cell_phone as SEC_CELL_PHONE, p.email as SEC_EMAIL, p.custody as SEC_CUSTODY  FROM student_address sa,people p,students_join_people sjp WHERE p.STAFF_ID=\'' . $sec_par_id[1]['PERSON_ID'] . '\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Secondary\' AND sjp.PERSON_ID=p.STAFF_ID LIMIT 1'));
                    foreach ($Stu_sec_address[1] as $ind => $col)
                        $contacts_RET[$ind] = $col;
                    if ($contacts_RET['SEC_CUSTODY'] == 'Y')
                        $contacts_RET['SEC_CUSTODY'] = 'Yes';
                    else
                        $contacts_RET['SEC_CUSTODY'] = 'No';
                }

                // $contacts_RET[1] = $Stu_prim_address[1];
                // foreach ($Stu_sec_address[1] as $ind => $col)
                //     $contacts_RET[1][$ind] = $col;

                if (!empty($contacts_RET)) {
                    foreach ($contacts_RET as $add_key => $add_val) {
                        $RET[$stu_key][$add_key] = $add_val;
                    }
                } else if (empty($contacts_RET) && $f == 1)
                    unset($RET[$stu_key]);
            }
        }
        echo "<br><br>";

        if ($extra['array_function'] && function_exists($extra['array_function']))
            $extra['array_function']($RET);
        if ($_REQUEST['excelReport'] != 'Y') {
            echo "<html><link rel='stylesheet' type='text/css' href='styles/Export.css'><body style=\" font-family:Arial; font-size:12px;\">";

            ListOutputPrint_Report($RET, $columns, $extra['singular'] ? $extra['singular'] : _student, $extra['plural'] ? $extra['plural'] : students, array(), $extra['LO_group'], $extra['LO_options']);

            echo "</body></html>";
        } else {
            $program_title = $_REQUEST['head_html'];

            $_REQUEST['LO_save'] = '1';
            $options = array();
            $column_names = $_SESSION['PEGI_COLS'];
            $column_names = $columns;

            // HANDLE SAVING THE LIST ---

            if ($_REQUEST['LO_save'] == '1') {
                if (!$options['save_delimiter'] && Preferences('DELIMITER') == 'CSV')
                    $options['save_delimiter'] = 'comma';
                switch ($options['save_delimiter']) {
                    case 'comma':
                        $extension = 'csv';
                        break;
                    case 'xml':
                        $extension = 'xml';
                        break;
                    default:
                        $extension = 'xls';
                        break;
                }
                ob_end_clean();

                if ($options['save_delimiter'] != 'xml') {
                    $output .= '<table border=\'1\'><tr>';
                    foreach ($column_names as $key => $value)
                        if ($key != 'CHECKBOX')
                            $output .= '<td>' . str_replace('&nbsp;', ' ', par_rep_cb('/<BR>/', ' ', par_rep_cb('/<!--.*-->/', '', $value))) . '</td>';
                    $output .= '</tr>';
                    foreach ($RET as $item) {
                        $output .= '<tr>';
                        foreach ($column_names as $key => $value) {
                            if ($key != 'CHECKBOX') {
                                if ($key == 'ATTENDANCE' || $key == 'IGNORE_SCHEDULING')
                                    $item[$key] = ($item[$key] == '<IMG SRC=assets/check.gif height=15>' ? 'Yes' : 'No');
                                $output .= '<td>' . par_rep_cb('/<[^>]+>/', '', par_rep_cb("/<div onclick='[^']+'>/", '', par_rep_cb('/ +/', ' ', par_rep_cb('/&[^;]+;/', '', str_replace('<BR>&middot;', ' : ', str_replace('&nbsp;', ' ', $item[$key])))))) . '</td>';
                            }
                        }
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                }

                if ($options['save_delimiter'] == 'xml') {
                    foreach ($RET as $item) {
                        foreach ($column_names as $key => $value) {
                            if ($options['save_delimiter'] == 'comma' && !$options['save_quotes'])
                                $item[$key] = str_replace(',', ';', $item[$key]);
                            $item[$key] = par_rep_cb('/<SELECT.*SELECTED\>([^<]+)<.*</SELECT\>/', '\\1', $item[$key]);
                            $item[$key] = par_rep_cb('/<SELECT.*</SELECT\>/', '', $item[$key]);
                            $output .= ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'xml' ? '<' . str_replace(' ', '', $value) . '>' : '') . par_rep_cb('/<[^>]+>/', '', par_rep_cb("/<div onclick='[^']+'>/", '', par_rep_cb('/ +/', ' ', par_rep_cb('/&[^;]+;/', '', str_replace('<BR>&middot;', ' : ', str_replace('&nbsp;', ' ', $item[$key])))))) . ($options['save_delimiter'] == 'xml' ? '</' . str_replace(' ', '', $value) . '>' . "\n" : '') . ($options['save_quotes'] ? '"' : '') . ($options['save_delimiter'] == 'comma' ? ',' : "\t");
                        }
                        $output .= "\n";
                    }
                }
                header("Cache-Control: public");
                header("Pragma: ");
                header("Content-Type: application/$extension");
                header("Content-Disposition: inline; filename=\"" . $program_title . ".$extension\"\n");
                if ($options['save_eval'])
                    eval($options['save_eval']);
                echo $output;
                exit();
            }
        }
    }
} else {
    if (!$fields_list) {
        if (AllowUse('students/Student.php&category_id=1'))
            $fields_list['General'] = array(
                'FULL_NAME' => (Preferences('NAME') == 'Common' ? _lastCommon : _lastFirstM),
                'FIRST_NAME' => _first,
                'FIRST_INIT' => _firstInitial,
                'LAST_NAME' => _last,
                'MIDDLE_NAME' => _middle,
                'ETHNICITY_ID' => _ethnicity,
                'LANGUAGE_ID' => _language,
                'NAME_SUFFIX' => _suffix,
                'GENDER' => _gender,
                'STUDENT_ID' => _studentId,
                'GRADE_ID' => _grade,
                'SECTION_ID' => _section,
                'SCHOOL_ID' => _school,
                'NEXT_SCHOOL' => _rollingRetentionOptions,
                'CALENDAR_ID' => _calendar,
                'USERNAME' => _username,
                'ALT_ID' => _alternateId,
                'BIRTHDATE' => _dob,
                'EMAIL' => _emailId,
                'PHONE' => _phone,
            );
        if (AllowUse('students/Student.php&category_id=3')) {
            $fields_list['Address'] = array(
                'ADDRESS' => _address,
                'CITY' => _city,
                'STATE' => _state,
                'ZIPCODE' => _zipCode,
                'MAIL_ADDRESS' => _mailingAddress,
                'MAIL_CITY' => _mailingCity,
                'MAIL_STATE' => _mailingState,
                'MAIL_ZIPCODE' => _mailingZipcode,
            );
        }

        $fields_list['Primary Contact'] = array('PRIM_STUDENT_RELATION' => 'Relationship', 'PRI_FIRST_NAME' => 'First Name', 'PRI_LAST_NAME' => 'Last Name', 'PRIM_HOME_PHONE' => 'Home Phone', 'PRIM_WORK_PHONE' => 'Work Phone', 'PRIM_CELL_PHONE' => 'Cell/Mobile Phone', 'PRIM_EMAIL' => 'Email', 'PRIM_CUSTODY' => 'Custody of Student', 'PRIM_ADDRESS' => 'Address', 'PRIM_STREET' => 'Street', 'PRIM_CITY' => 'City', 'PRIM_STATE' => 'State', 'PRIM_ZIPCODE' => 'Zip/Postal Code');

        $fields_list['Secondary Contact'] = array('SEC_STUDENT_RELATION' => 'Relationship', 'SEC_FIRST_NAME' => 'First Name', 'SEC_LAST_NAME' => 'Last Name', 'SEC_HOME_PHONE' => 'Home Phone', 'SEC_WORK_PHONE' => 'Work Phone', 'SEC_CELL_PHONE' => 'Cell/Mobile Phone', 'SEC_EMAIL' => 'Email', 'SEC_CUSTODY' => 'Custody of Student', 'SEC_ADDRESS' => 'Address', 'SEC_STREET' => 'Street', 'SEC_CITY' => 'City', 'SEC_STATE' => 'State', 'SEC_ZIPCODE' => 'Zip/Postal Code');

        if ($extra['field_names'])
            $fields_list['General'] += $extra['field_names'];
    }
    /*     * **************************************************************************** */
    $categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM student_field_categories ORDER BY SORT_ORDER'));
    $custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM custom_fields where system_field=\'Y\' ORDER BY SORT_ORDER'), array(), array('CATEGORY_ID'));
    $custom_RET1 = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM custom_fields where system_field=\'N\' ORDER BY SORT_ORDER'), array(), array('CATEGORY_ID'));
    foreach ($categories_RET as $category) {
        if (AllowUse('students/Student.php&category_id=' . $category['ID'])) {
            foreach ($custom_RET[$category['ID']] as $field) {

                $title = strtolower(trim($field['TITLE']));
                if (strpos(trim($field['TITLE']), ' ') != 0) {
                    $p1 = substr(trim($field['TITLE']), 0, strpos(trim($field['TITLE']), ' '));
                    $p2 = substr(trim($field['TITLE']), strpos(trim($field['TITLE']), ' ') + 1);
                    $title = strtolower($p1 . '_' . $p2);
                }

                $fields_list[$category['TITLE']][$title] = str_replace("'", '&#39;', $field['TITLE']);
            }
            foreach ($custom_RET1[$category['ID']] as $field) {
                $fields_list[$category['TITLE']]['CUSTOM_' . $field['ID']] = $field['TITLE'];
                if ($fields_list[generalInfo] != '') {
                    $fields_list['General'] += $fields_list[generalInfo];
                }
            }
        }
    }
    unset($fields_list[generalInfo]);
    $periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'' . ($_REQUEST['period_id'] != '' ? " AND PERIOD_ID=" . $_REQUEST['period_id'] . "" : "") . ' ORDER BY SORT_ORDER'));
    foreach ($periods_RET as $period)
        $fields_list['Schedule']['PERIOD_' . $period['PERIOD_ID']] = $period['TITLE'] . ' Teacher - Room';

    if ($openSISModules['Food_Service'])
        $fields_list['Food_Service'] = array('FS_ACCOUNT_ID' => '' . _accountID . '', 'FS_DISCOUNT' => '' . _discount . '', 'FS_STATUS' => '' . _status . '', 'FS_BARCODE' => '' . _barcode . '', 'FS_BALANCE' => '' . _balance . '');

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    //DrawHeader("<div></div>", $extra['header_right']);
    PopTable_wo_header('header', '<i class="glyphicon glyphicon-tasks"></i> &nbsp;' . _selectFieldsToGenerateReport . '');
    //echo '<ul class="list-group">';
    foreach ($fields_list as $category => $fields) {
        $i = 1;
        $j = 1;
        switch ($category) {
            case 'General':
                $categoryTitle = _general;
                break;
            case 'Address':
                $categoryTitle = _address;
                break;
            case 'Schedule':
                $categoryTitle = _schedule;
                break;
            case 'General Info':
                $categoryTitle = _generalInfo;
                break;
            case 'Addresses &amp; Contacts':
                $categoryTitle = _addressesContacts;
                break;
            case 'Medical':
                $categoryTitle = _medical;
                break;
            case 'Comments':
                $categoryTitle = _comments;
                break;
            case 'Goals':
                $categoryTitle = _goals;
                break;
            case 'Enrollment Info':
                $categoryTitle = _enrollmentInfo;
                break;
            case 'Files':
                $categoryTitle = _files;
                break;
            default:
                $categoryTitle = $category;
                break;
        }
        echo '<h4>' . $categoryTitle . '</h4>';
        foreach ($fields as $field => $title) {
            if ($i == 1 && $j == 1) {
                echo '<div class="row">';
            } elseif ($i == 1 && $j > 1) {
                echo '</div><div class="row">';
            }
            echo '<div class="col-md-6"><div class="checkbox"><label><INPUT type=checkbox onclick="addHTML(\'<li class=col-lg-6>' . $title . '</li>\',\'names_div\',false);addHTML(\'<INPUT type=hidden name=fields[' . $field . '] value=Y>\',\'fields_div\',false);addHTML(\'\',\'names_div_none\',true);this.disabled=true">' . $title . '</label></div>' . ($field == 'PARENTS' ? '<BR>(<small>' . _relation . ': </small><input type=text id=relation name=relation size=8>)' : '') . '</div>';
            $i++;
            $j++;
            if ($i == 3) {
                //echo '</div>';
                $i = 1;
            }
        }
        echo '</div>';
        /* if ($i % 2 != 0) {
          echo '<TD></TD></TR><TR>';
          $i++;
          } */
    }
    //echo '</ul>';

    PopTable_wo_header('footer');
    echo '</div>'; //.col-md-6
    echo '<div class="col-md-6">';

    echo '<div class="panel">';
    echo '<div class="panel-heading">';
    echo '<h6 class="panel-title text-pink text-uppercase"><i class="glyphicon glyphicon-saved"></i> &nbsp;' . _selectedFields . '</h6>';
    echo '</div>'; //.panel-heading
    echo '<div class="panel-body">';
    //DrawHeader("<div><a class=big_font><i class=\"glyphicon glyphicon-saved\"></i> &nbsp;'._selectedFields.'</a></div>", $extra['header_right']);
    echo '<div class="well"><div id="names_div_none" class="error_msg" style="padding:6px 0px 0px 6px;">' . _noFieldsSelected . '</div><ol id=names_div class=row></ol></div>';

    if ($Search && function_exists($Search))
        $Search($extra);

    echo '</div>'; //.panel-body

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
}
function _makeSectionVal($value)
{
    if ($value != '') {
        $section = DBGet(DBQuery('SELECT * FROM school_gradelevel_sections WHERE ID=' . $value));
        $section = $section[1]['NAME'];
    } else
        $section = '';
    return $section;
}
