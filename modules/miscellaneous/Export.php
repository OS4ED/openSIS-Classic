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
        $fields_list = array('FULL_NAME' => (Preferences('NAME') == 'Common' ? 'Last, Common' : 'Last, First M'), 'FIRST_NAME' => 'First', 'FIRST_INIT' => 'First Initial', 'LAST_NAME' => 'Last', 'MIDDLE_NAME' => 'Middle', 'NAME_SUFFIX' => 'Suffix', 'GENDER'=>'Gender','STUDENT_ID' => 'Student ID', 'GRADE_ID' => 'Grade','SECTION_ID' => 'Section', 'SCHOOL_ID' => 'School', 'NEXT_SCHOOL' => 'Rolling / Retention Options', 'CALENDAR_ID' => 'Calendar', 'USERNAME' => 'Username', 'PASSWORD' => 'Password', 'ALT_ID' => 'Alternate ID', 'BIRTHDATE' => 'DOB', 'EMAIL' => 'Email ID', 'ADDRESS' => 'Address', 'CITY' => 'City', 'STATE' => 'State', 'ZIPCODE' => 'Zip Code', 'PHONE' => 'Phone', 'MAIL_ADDRESS' => 'Mailing Address', 'MAIL_CITY' => 'Mailing City', 'MAIL_STATE' => 'Mailing State', 'MAIL_ZIPCODE' => 'Mailing Zipcode', 'PARENTS' => 'Contacts');
        if ($extra['field_names'])
            $fields_list += $extra['field_names'];

        $periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

        foreach ($periods_RET as $period)
            $fields_list['PERIOD_' . $period['PERIOD_ID']] = $period['TITLE'] . ' Teacher - Room';
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
    if ($_REQUEST['fields']['USERNAME'] == 'Y')
    {
$extra['SELECT'] .= ',la.username';
$extra['FROM'].=', login_authentication la';
$extra['WHERE'].=' AND la.USER_ID=s.STUDENT_ID AND la.profile_id=3  ';
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

        $list_attr = DBGet(DBQuery("SHOW COLUMNS FROM `students` "));
        foreach ($list_attr as $data) {

            $list_attr_val[] = strtoupper($data['FIELD']);
        }
        
        
        foreach($columns as $stu_indx=>$stu_data)
                    {
            $f=0;
                      
                    if(!in_array($stu_indx,$list_attr_val))
                    {
                      $f=1;

                    }
 else {
     $f=0;
     break;
 }
                   } 

        if ($_REQUEST['ADDRESS_ID'] || $_REQUEST['fields']['ADDRESS'] || $_REQUEST['fields']['CITY'] || $_REQUEST['fields']['STATE'] || $_REQUEST['fields']['ZIPCODE'] || $_REQUEST['fields']['PHONE'] || $_REQUEST['fields']['MAIL_ADDRESS'] || $_REQUEST['fields']['MAIL_CITY'] || $_REQUEST['fields']['MAIL_STATE'] || $_REQUEST['fields']['MAIL_ZIPCODE'] || $_REQUEST['fields']['PARENTS']) {


            foreach ($RET as $stu_key => $stu_val) {

               $add_reslt = "SELECT CONCAT(sa.STREET_ADDRESS_1,' ,',sa.STREET_ADDRESS_2)  as ADDRESS ,sa.CITY,sa.STATE,sa.ZIPCODE,COALESCE((SELECT STREET_ADDRESS_1 FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' limit 0,1),sa.STREET_ADDRESS_1) AS 

                            MAIL_ADDRESS,COALESCE((SELECT CITY FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' limit 0,1),sa.CITY) AS MAIL_CITY,COALESCE((SELECT STATE FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' limit 0,1),sa.STATE) AS MAIL_STATE,

                            COALESCE((SELECT ZIPCODE FROM student_address WHERE student_id=" . $stu_val['STUDENT_ID'] . " AND TYPE='MAIL' limit 0,1),sa.ZIPCODE) AS MAIL_ZIPCODE  from student_address sa   WHERE  sa.TYPE='HOME ADDRESS' AND sa.STUDENT_ID=" . $stu_val['STUDENT_ID'];
               
                $res = DBGet(DBQuery($add_reslt));

                    foreach ($res[1] as $add_key => $add_val) {
                        $RET[$stu_key][$add_key] = $add_val;
                       
                    }
                     
                        if(empty($res[1]) && $f==1)
                            unset($RET[$stu_key]);
               
            }
        } echo "<br><br>";

        if ($extra['array_function'] && function_exists($extra['array_function']))
            $extra['array_function']($RET);
        echo "<html><link rel='stylesheet' type='text/css' href='styles/Export.css'><body style=\" font-family:Arial; font-size:12px;\">";
       
        ListOutputPrint_Report($RET, $columns, $extra['singular'] ? $extra['singular'] : 'Student', $extra['plural'] ? $extra['plural'] : 'Students', array(), $extra['LO_group'], $extra['LO_options']);

        echo "</body></html>";
    }
}
else {
    if (!$fields_list) {
        if (AllowUse('students/Student.php&category_id=1'))
            $fields_list['General'] = array('FULL_NAME' => (Preferences('NAME') == 'Common' ? 'Last, Common' : 'Last, First M'), 'FIRST_NAME' => 'First', 'FIRST_INIT' => 'First Initial', 'LAST_NAME' => 'Last', 'MIDDLE_NAME' => 'Middle', 'NAME_SUFFIX' => 'Suffix','GENDER' => 'Gender', 'STUDENT_ID' => 'Student ID', 'GRADE_ID' => 'Grade', 'SCHOOL_ID' => 'School', 'NEXT_SCHOOL' => 'Rolling / Retention Options', 'CALENDAR_ID' => 'Calendar', 'USERNAME' => 'Username', 'ALT_ID' => 'Alternate ID', 'BIRTHDATE' => 'DOB', 'EMAIL' => 'Email ID', 'PHONE' => 'Phone');
        if (AllowUse('students/Student.php&category_id=3')) {
            $fields_list['Address'] = array('ADDRESS' => 'Address', 'CITY' => 'City', 'STATE' => 'State', 'ZIPCODE' => 'Zip Code', 'MAIL_ADDRESS' => 'Mailing Address', 'MAIL_CITY' => 'Mailing City', 'MAIL_STATE' => 'Mailing State', 'MAIL_ZIPCODE' => 'Mailing Zipcode');
        }
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
            }
        }
    }

    $periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''.($_REQUEST['period_id']!=''?" AND PERIOD_ID=".$_REQUEST['period_id']."":"").' ORDER BY SORT_ORDER'));
    foreach ($periods_RET as $period)
        $fields_list['Schedule']['PERIOD_' . $period['PERIOD_ID']] = $period['TITLE'] . ' Teacher - Room';

    if ($openSISModules['Food_Service'])
        $fields_list['Food_Service'] = array('FS_ACCOUNT_ID' => 'Account ID', 'FS_DISCOUNT' => 'Discount', 'FS_STATUS' => 'Status', 'FS_BARCODE' => 'Barcode', 'FS_BALANCE' => 'Balance');

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    //DrawHeader("<div></div>", $extra['header_right']);
    PopTable_wo_header('header', '<i class="glyphicon glyphicon-tasks"></i> &nbsp;Select Fields To Generate Report');
    //echo '<ul class="list-group">';
    foreach ($fields_list as $category => $fields) {
        $i = 1;
        $j = 1;
        echo '<h4>' . $category . '</h4>';
        foreach ($fields as $field => $title) {
            if ($i == 1 && $j == 1) {
                echo '<div class="row">';
            }elseif($i == 1 && $j > 1){
                echo '</div><div class="row">';
            }
            echo '<div class="col-md-6"><div class="checkbox"><label><INPUT type=checkbox onclick="addHTML(\'<li class=col-lg-6>' . $title . '</li>\',\'names_div\',false);addHTML(\'<INPUT type=hidden name=fields[' . $field . '] value=Y>\',\'fields_div\',false);addHTML(\'\',\'names_div_none\',true);this.disabled=true">' . $title . '</label></div>' . ($field == 'PARENTS' ? '<BR>(<small>Relation: </small><input type=text id=relation name=relation size=8>)' : '') . '</div>';
            //if ($i % 2 == 0)
            //echo '</TR><TR>';
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
    echo '<h6 class="panel-title text-pink text-uppercase"><i class="glyphicon glyphicon-saved"></i> &nbsp;Selected Fields</h6>';
    echo '</div>'; //.panel-heading
    echo '<div class="panel-body">';
    //DrawHeader("<div><a class=big_font><i class=\"glyphicon glyphicon-saved\"></i> &nbsp;Selected Fields</a></div>", $extra['header_right']);
    echo '<div class="well"><div id="names_div_none" class="error_msg" style="padding:6px 0px 0px 6px;">No fields selected</div><ol id=names_div class=row></ol></div>';

    if ($Search && function_exists($Search))
        $Search($extra);

    echo '</div>'; //.panel-body

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
}
function _makeSectionVal($value)
{
  if($value!='')
  {
  $section=DBGet(DBQuery('SELECT * FROM school_gradelevel_sections WHERE ID='.$value));
  $section=$section[1]['NAME'];
  }
  else
      $section='';
    return $section;
}
?>
