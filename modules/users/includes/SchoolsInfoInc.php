
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

if ((isset($_REQUEST['teacher_view']) && ($_REQUEST['teacher_view'] != 'y')) || (!isset($_REQUEST['teacher_view']) && isset($_REQUEST['values']))) {
    $sql_school_admin = 'SELECT ssr.SCHOOL_ID FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=' . UserSyear() . ' AND st.staff_id=' . User('STAFF_ID');
    $school_admin = DBGet(DBQuery($sql_school_admin));

    foreach ($school_admin as $index => $school) {
        if ($_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']]) {
            $start_date = $_REQUEST['year_values']['START_DATE'][$school['SCHOOL_ID']] . "-" . $_REQUEST['month_values']['START_DATE'][$school['SCHOOL_ID']] . "-" . $_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']];
            $check_start_date = $_REQUEST['year_values']['START_DATE'][$school['SCHOOL_ID']] . '-' . $_REQUEST['month_values']['START_DATE'][$school['SCHOOL_ID']] . '-' . $_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']];
        } else {
            $start_date = '';
            $check_start_date = '';
        }
        if ($_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']]) {
            $end_month = array("01" => "JAN", "02" => "FEB", "03" => "MAR", "04" => "APR", "05" => "MAY", "06" => "JUN", "07" => "JUL", "08" => "AUG", "09" => "SEP", "10" => "OCT", "11" => "NOV", "12" => "DEC");
            foreach ($end_month as $ei => $ed) {
                if ($ed == $_REQUEST['month_values']['END_DATE'][$school['SCHOOL_ID']])
                    $_REQUEST['month_values']['END_DATE'][$school['SCHOOL_ID']] = $ei;
            }
            $end_date = $_REQUEST['year_values']['END_DATE'][$school['SCHOOL_ID']] . "-" . $_REQUEST['month_values']['END_DATE'][$school['SCHOOL_ID']] . "-" . $_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']];
        } else {
            $end_date = '';
        }

        if (($start_date != '' && VerifyDate(date('d-M-Y', strtotime($start_date)))) || ($end_date != '' && VerifyDate(date('d-M-Y', strtotime($end_date)))) || ($start_date == '' && $end_date == '')) {
            // if (is_array($school) && in_array(UserSchool(),$school)) {
            if ((is_array($school) && in_array(UserSchool(),$school)) || (isset($_REQUEST['values']['SCHOOLS'][$school['SCHOOL_ID']]) && $_REQUEST['values']['SCHOOLS'][$school['SCHOOL_ID']] == 'Y' && $_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']])) {
                $schools_each_staff = DBGet(DBQuery('SELECT SCHOOL_ID,START_DATE,END_DATE FROM staff_school_relationship WHERE staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=' . $school['SCHOOL_ID']));
                if ($schools_each_staff[1]['START_DATE'] == '')
                    DBQuery('UPDATE staff_school_relationship SET START_DATE=\'0000-00-00\' WHERE staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=' . $school['SCHOOL_ID']);

                $schools_each_staff = DBGet(DBQuery('SELECT SCHOOL_ID,START_DATE,END_DATE FROM staff_school_relationship WHERE staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=' . $school['SCHOOL_ID']));
                $start = $schools_each_staff[1]['START_DATE'];

                $schools_start_date = DBGet(DBQuery('SELECT START_DATE FROM school_years WHERE SCHOOL_ID=' . $school['SCHOOL_ID'] . ' AND SYEAR=' . UserSyear()));
                $schools_start_date = $schools_start_date[1]['START_DATE'];
                if ($schools_each_staff[1]['START_DATE'] > $end_date && $end_date != '') {
                    $error = 'end_date';
                }
                
                if (!empty($schools_each_staff) && $start != '') {
                    $update = 'false';
                    unset($sql_up);
                    
                    foreach ($_REQUEST['values']['SCHOOLS'] as $index => $value) {
                        if ($value != 'Y' && $value != 'N' && $value != '')
                            $value = 'Y';
                        if ($index == $school['SCHOOL_ID'] && $value == 'Y') {
                            $update = 'go';
                        }
                    }
                    
                    if ($update == 'go') {
                        if ($start_date != '' && $end_date != '' && $end_date != NULL) {
                            if (strtotime($start_date) <= strtotime($end_date))
                                $sql_up = 'UPDATE staff_school_relationship SET START_DATE=\'' . date('Y-m-d', strtotime($start_date)) . '\', END_DATE=\'' . date('Y-m-d', strtotime($end_date)) . '\' where staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . $school['SCHOOL_ID'] . '\'';
                            else
                                $error = 'end_date';
                        } elseif ($start_date == '' && $end_date != '') {
                            if (isset($_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']]) && $_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']] == '') {
                                $error1 = 'start_date';
                            } else {
                                if (strtotime($schools_each_staff[1]['START_DATE']) <= strtotime($end_date))
                                    $sql_up = 'UPDATE staff_school_relationship SET END_DATE=\'' . date('Y-m-d', strtotime($end_date)) . '\' where staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . $school['SCHOOL_ID'] . '\'';
                                else
                                    $error = 'end_date';
                            }
                        } elseif ($start_date != '' && ($end_date == '' || $end_date == NULL) && strtotime($start) != strtotime($start_date)) {
                            if (strtotime($schools_each_staff[1]['END_DATE']) >= strtotime($start_date) || $schools_each_staff[1]['END_DATE'] == '0000-00-00' || $schools_each_staff[1]['END_DATE'] == NULl) {
                                $cp_check = DBGet(DBQuery('SELECT * FROM course_periods WHERE SYEAR=' . UserSyear() . ' AND BEGIN_DATE <\'' . date('Y-m-d', strtotime($start_date)) . '\' AND (TEACHER_ID=' . $_REQUEST['staff_id'] . ' OR SECONDARY_TEACHER_ID=' . $_REQUEST['staff_id'] . ') AND SCHOOL_ID=\'' . $school['SCHOOL_ID'] . '\' '));

                                if ($cp_check[1]['COURSE_PERIOD_ID'] == '') {
                                    $sql_up = 'UPDATE staff_school_relationship SET START_DATE=\'' . date('Y-m-d', strtotime($start_date)) . '\' where staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . $school['SCHOOL_ID'] . '\'';
                                } else {
                                    $error = 'cp_association';
                                }
                            } else
                                $error = 'end_date';
                        } elseif (isset($_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']]) && isset($_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']]) && $_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']] == '' && $_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']] == '') {
                            $sql_up = 'UPDATE staff_school_relationship SET START_DATE=NULL, END_DATE=NULL where staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . $school['SCHOOL_ID'] . '\'';
                        } elseif (isset($_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']]) && $_REQUEST['day_values']['END_DATE'][$school['SCHOOL_ID']] == '') {
                            $sql_up = 'UPDATE staff_school_relationship SET end_date=NULL where staff_id=\'' . $_REQUEST['staff_id'] . '\' AND syear=\'' . UserSyear() . '\' AND school_id=\'' . $school['SCHOOL_ID'] . '\'';
                        }
                        
                        if (!$error && !$error1 && $sql_up != '') {
                            DBQuery($sql_up);
                        }
                    }   
                } else {

                    $sql_up = 'INSERT INTO staff_school_relationship(staff_id,syear,school_id';
                    $sql_up_data = 'VALUES(\'' . $_REQUEST['staff_id'] . '\',\'' . UserSyear() . '\',\'' . $school['SCHOOL_ID'] . '\'';

                    if ($start_date != '') {
                        $sql_up .= ',start_date';
                    }
                    if ($end_date != '') {
                        if ($_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']] != '') {

                            $sql_up .= ',end_date';
                        }
                    }
                    if ($start_date != '') {
                        $sql_up_data .= ',\'' . date('Y-m-d', strtotime($start_date)) . '\'';
                    }
                    if ($end_date != '') {
                        if ($_REQUEST['day_values']['START_DATE'][$school['SCHOOL_ID']] != '')
                            $sql_up_data .= ',\'' . date('Y-m-d', strtotime($end_date)) . '\'';
                    }
                    $sql_up .= ')' . $sql_up_data . ')';

                    if ($start_date != '' && $end_date != '' && $end_date != NULL) {
                        if (strtotime($start_date) > strtotime($end_date))
                            $error = 'end_date';
                    }


                    if (!$error)
                        DBQuery($sql_up);
                }
            } else {
                $user_profile = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE STAFF_ID='" . $_REQUEST['staff_id'] . "'"));
                if ($user_profile[1]['PROFILE_ID'] != '' && is_countable($cur_school) && count($cur_school) > 0) {
                    $school_selected = '';
                    if (isset($_REQUEST['values']['SCHOOLS']))
                        $school_selected = implode(',', array_unique(array_keys($_REQUEST['values']['SCHOOLS'])));

                    $del_qry .= "DELETE FROM staff_school_relationship WHERE STAFF_ID='" . $_REQUEST['staff_id'] . "' AND SYEAR='" . UserSyear() . "'";
                    if ($school_selected != '')
                        $del_qry .= " AND SCHOOL_ID NOT IN (" . $school_selected . ")";

                    DBQuery($del_qry);

                    $del_qry = '';
                }
            }
        
        } else {
            $err = "<div class=\"alert bg-danger alert-styled-left\">" . _theInvalidDateCouldNotBeSaved . "</div>";
        }
    }
    
    if ($error == 'end_date') {
        echo '<script type=text/javascript>document.getElementById(\'sh_err\').innerHTML=\'<b><font color=red>Start date can not be greater than end date</font></b>\';</script>';

        unset($error);
    }
    
    if ($error == 'cp_association') {
        echo '<script type=text/javascript>document.getElementById(\'sh_err\').innerHTML=\'<b><font color=red>Can not change the staff start date because it has association</font></b>\';</script>';

        unset($error);
    }
    if ($error1 == 'start_date') {
        echo '<script type=text/javascript>document.getElementById(\'sh_err\').innerHTML=\'<font color=red><b>Start date can not be blank</b></font>\';</script>';
        unset($error1);
    }
}

if ($_REQUEST['month_values']['JOINING_DATE'] && $_REQUEST['day_values']['JOINING_DATE'] && $_REQUEST['year_values']['JOINING_DATE']) {
    $_REQUEST['values']['SCHOOL']['JOINING_DATE'] = $_REQUEST['year_values']['JOINING_DATE'] . '-' . $_REQUEST['month_values']['JOINING_DATE'] . '-' . $_REQUEST['day_values']['JOINING_DATE'];
    $_REQUEST['values']['SCHOOL']['JOINING_DATE'] = date("Y-m-d", strtotime($_REQUEST['values']['SCHOOL']['JOINING_DATE']));
} elseif (isset($_REQUEST['month_values']['JOINING_DATE']) && isset($_REQUEST['day_values']['JOINING_DATE']) && isset($_REQUEST['year_values']['JOINING_DATE']))
    $_REQUEST['values']['SCHOOL']['JOINING_DATE'] = '';


if ($_REQUEST['month_values']['ENDING_DATE'] && $_REQUEST['day_values']['ENDING_DATE'] && $_REQUEST['year_values']['ENDING_DATE']) {
    $_REQUEST['values']['SCHOOL']['ENDING_DATE'] = $_REQUEST['year_values']['ENDING_DATE'] . '-' . $_REQUEST['month_values']['ENDING_DATE'] . '-' . $_REQUEST['day_values']['ENDING_DATE'];
    $_REQUEST['values']['SCHOOL']['ENDING_DATE'] = date("Y-m-d", strtotime($_REQUEST['values']['SCHOOL']['ENDING_DATE']));
} elseif (isset($_REQUEST['month_values']['ENDING_DATE']) && isset($_REQUEST['day_values']['ENDING_DATE']) && isset($_REQUEST['year_values']['ENDING_DATE']))
    $_REQUEST['values']['SCHOOL']['ENDING_DATE'] = '';

$end_date = $_REQUEST['values']['SCHOOL']['ENDING_DATE'];
unset($_REQUEST['values']['SCHOOL']['ENDING_DATE']);
$_REQUEST['values']['SCHOOL']['END_DATE'] = $end_date;

if ($_REQUEST['values']['SCHOOL_IDS']) {
    $_REQUEST['values']['SCHOOL']['SCHOOL_ACCESS'] = ',';
    foreach ($_REQUEST['values']['SCHOOL_IDS'] as $key => $val) {
        $_REQUEST['values']['SCHOOL']['SCHOOL_ACCESS'] .= $key . ",";
    }
}

$select_RET = DBGet(DBQuery("SELECT STAFF_ID FROM staff_school_info where STAFF_ID='" . UserStaffID() . "'"));
$select = $select_RET[1]['STAFF_ID'];

//$_REQUEST['staff_school']['PASSWORD'];
if (isset($_REQUEST['staff_school']['PASSWORD']))
    $password = md5($_REQUEST['staff_school']['PASSWORD']);

if ($_REQUEST['values']['SCHOOL']['OPENSIS_PROFILE'] == '1') {
    $school_id1 = DBGet(DBQuery("SELECT ID FROM schools"));

    foreach ($school_id1 as $index => $val) {
        $schools[] = $val['ID'];
    }

    $schools = implode(",", $schools);
    $_REQUEST['values']['SCHOOL']['SCHOOL_ACCESS'] = "," . $schools . ",";
} else {
    foreach ($_REQUEST['values']['SCHOOLS'] as $school => $val) {
        if ($val == 'Y') {
            $schools[] = $school;
        }
    }
    $schools = is_array($schools) ? implode(",", $schools) : $schools;
    $_REQUEST['values']['SCHOOL']['SCHOOL_ACCESS'] = "," . $schools . ",";
}

if ($select == '') {
    //    print_r($_REQUEST);exit;
    if ($_REQUEST['values']['SCHOOL']['OPENSIS_ACCESS'] == 'Y') {
        $sql = "INSERT INTO staff_school_info ";
        $fields = 'STAFF_ID,';
        $values = "'" . UserStaffID() . "',";
        foreach ($_REQUEST['values']['SCHOOL'] as $column => $value) {


            if ($column == 'SCHOOL_ACCESS' && $value == ',,')
                $value = ',' . UserSchool() . ',';
            if ($value) {

                $fields .= $column . ',';
                //                                      if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
                //                                                 $values .= "'".str_replace("'","\'",$value)."',";
                //                                        }else
                $values .= "'" . singleQuoteReplace('', '', $value) . "',";
            }
            if ($column == 'OPENSIS_PROFILE' && $value == 0) {
                $fields .= $column . ',';
                //                                      if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
                //                                                 $values .= "'".str_replace("'","\'",$value)."',";
                //                                        }else
                $values .= "'" . singleQuoteReplace('', '', $value) . "',";
            }
        }
        $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

        DBQuery($sql);
        $update_staff_RET = DBGet(DBQuery("SELECT  * FROM staff_school_info where STAFF_ID='" . UserStaffID() . "'"));
        $update_staff = $update_staff_RET[1];
        $profile_name_RET = DBGet(DBQuery("SELECT PROFILE from user_profiles WHERE id=" . $update_staff['OPENSIS_PROFILE']));
        $profile = $profile_name_RET[1]['PROFILE'];
        $staff_CHECK = DBGet(DBQuery("SELECT  s.*,la.*  FROM staff s,login_authentication la where s.STAFF_ID='" . UserStaffID() . "' AND la.PROFILE_ID NOT IN (3,4) AND la.USER_ID=s.STAFF_ID"));
        $staff = $staff_CHECK[1];
        $sql_staff = "UPDATE staff SET ";

        if ($_REQUEST['staff_school']['CURRENT_SCHOOL_ID'])
            $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',PROFILE='" . $profile . "',CURRENT_SCHOOL_ID='" . $_REQUEST['staff_school']['CURRENT_SCHOOL_ID'] . "',";
        else
            $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',PROFILE='" . $profile . "',";

        foreach ($_REQUEST['staff_school'] as $field => $value) {
            if ($field == 'IS_DISABLE') {
                if ($value) {
                    $sql_staff .= $field . "='" . singleQuoteReplace('', '', $value) . "',";
                }
            } elseif ($field == 'PASSWORD') {
                $password = ($value);
                /*
                    $sql = DBQuery('SELECT PASSWORD FROM login_authentication  WHERE PASSWORD=\'' . $password . '\'');
                    $number = $sql->num_rows;
                */

                //code for match password in login table
                $number = 0;
                $sqlquery = DBQuery('SELECT PASSWORD FROM login_authentication');
                foreach ($sqlquery as $val) {
                    $sqloldpass = $val['PASSWORD'];
                    $login_status = VerifyHash($password, $sqloldpass);
                    if ($login_status == 1) {
                        $number = $number + 1;
                    }
                }
                //end

                if ($number == 0) {
                    if ((!$staff['USERNAME']) && (!$staff['PASSWORD'])) {
                        $sql_staff_pwd = $field . "=NULL";
                    } else {
                        $value = singleQuoteReplace('', '', ($value));
                        $new_password = GenerateNewHash($value);
                        $sql_staff_pwd = $field . "='" . $new_password . "'";
                    }
                }
            }
        }
        $sql_staff = substr($sql_staff, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
        if ($sql_staff_pwd != '') {
            $sql_staff_pwd = 'Update login_authentication SET ' . $sql_staff_pwd . ' WHERE USER_ID=' . UserStaffID();


            if (SelectedUserProfile('PROFILE_ID') != '')
                $sql_staff_pwd .= ' AND PROFILE_ID=' . SelectedUserProfile('PROFILE_ID');
        }

        if ($update_staff['OPENSIS_PROFILE'] != '') {
            $check_rec = DBGet(DBQuery('SELECT COUNT(1) AS REC_EXISTS FROM login_authentication WHERE USER_ID=' . UserStaffID() . ' AND PROFILE_ID NOT IN (3,4) '));
            if ($check_rec[1]['REC_EXISTS'] == 0)
                $sql_staff_prf = 'INSERT INTO login_authentication (PROFILE_ID,USER_ID) VALUES (\'' . $update_staff['OPENSIS_PROFILE'] . '\',\'' . UserStaffID() . '\') ';
            else
                $sql_staff_prf = 'Update login_authentication SET  PROFILE_ID=\'' . $update_staff['OPENSIS_PROFILE'] . '\' WHERE PROFILE_ID NOT IN (3,4) AND USER_ID=' . UserStaffID();
        }

        DBQuery($sql_staff);
        if ($sql_staff_pwd != '') {
            DBQuery($sql_staff_pwd);
        }
        if ($update_staff['OPENSIS_PROFILE'] != '')
            DBQuery($sql_staff_prf);
        if ((!$staff['USERNAME']) && (!$staff['PASSWORD']) && $_REQUEST['USERNAME'] != '' && $_REQUEST['PASSWORD'] != '') {

            $new_password_hash = GenerateNewHash($_REQUEST['PASSWORD']);
            $sql_staff_algo = "UPDATE login_authentication l,staff s, staff_school_info ssi SET
                                l.username = '" . $_REQUEST['USERNAME'] . "',
                               l.password ='" . $new_password_hash . "' 
                                WHERE s.staff_id = ssi.staff_id AND l.user_id=s.staff_id AND l.profile_id NOT IN (3,4) AND s.staff_id = " . UserStaffID();

            DBQuery($sql_staff_algo);
        }
        if ($update_staff['OPENSIS_PROFILE'] == '1') {

            $school_id3 = DBGet(DBQuery("SELECT ID FROM schools WHERE ID NOT IN (SELECT school_id FROM staff_school_relationship WHERE
                                      STAFF_ID='" . $_REQUEST['staff_id'] . "' AND SYEAR='" . UserSyear() . "')"));
            foreach ($school_id3 as $index => $val) {

                $sql_up = 'INSERT INTO staff_school_relationship(staff_id,syear,school_id';
                $sql_up .= ')VALUES(\'' . $_REQUEST['staff_id'] . '\',\'' . UserSyear() . '\',\'' . $val['ID'] . '\'';


                $sql_up .= ')';
            }
        }
    } elseif ($_REQUEST['values']['SCHOOL']['OPENSIS_ACCESS'] == 'N') {
        $sql = "INSERT INTO staff_school_info ";
        $fields = 'STAFF_ID,';
        $values = "'" . UserStaffID() . "',";
        foreach ($_REQUEST['values']['SCHOOL'] as $column => $value) {

            //            if ($column == 'OPENSIS_PROFILE') {
            //                $fields .= $column . ',';
            //                $values .= "NULL,";
            //            } else {
            if ($value) {
                $fields .= $column . ',';
                //                                    if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux'))
                //                                      {
                //                                        $values .= "'".str_replace("'","\'",$value)."',";
                //                                    }
                //                                    else
                $values .= "'" . singleQuoteReplace('', '', $value) . "',";
            }
            //            }
        }
        $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

        DBQuery($sql);
        $update_staff_RET = DBGet(DBQuery("SELECT  * FROM staff_school_info where STAFF_ID='" . UserStaffID() . "'"));
        $update_staff = $update_staff_RET[1];
        $staff_CHECK = DBGet(DBQuery("SELECT  *  FROM staff where STAFF_ID='" . UserStaffID() . "'"));
        $staff = $staff_CHECK[1];

        if ($update_staff['OPENSIS_PROFILE'] != '') {
            $profile_det = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $update_staff['OPENSIS_PROFILE']));

            $sql_staff = "UPDATE staff SET ";
            $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',PROFILE='" . $profile_det[1]['PROFILE'] . "' ";
        } else {
            $sql_staff = "UPDATE staff SET ";
            $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',";
        }
        $sql_staff = substr($sql_staff, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
        DBQuery($sql_staff);


        if ($update_staff['OPENSIS_PROFILE'] != '') {
            $check_rec = DBGet(DBQuery('SELECT COUNT(1) AS REC_EXISTS FROM login_authentication WHERE USER_ID=' . UserStaffID() . ' AND PROFILE_ID NOT IN (3,4) '));
            if ($check_rec[1]['REC_EXISTS'] == 0)
                $sql_staff_prf = 'INSERT INTO login_authentication (PROFILE_ID,USER_ID) VALUES (\'' . $update_staff['OPENSIS_PROFILE'] . '\',\'' . UserStaffID() . '\') ';
            else
                $sql_staff_prf = 'Update login_authentication SET  PROFILE_ID=\'' . $update_staff['OPENSIS_PROFILE'] . '\' WHERE PROFILE_ID NOT IN (3,4) AND USER_ID=' . UserStaffID();
        }


        if ($update_staff['OPENSIS_PROFILE'] != '')
            DBQuery($sql_staff_prf);

        if ($update_staff['OPENSIS_PROFILE'] == '1') {

            $school_id3 = DBGet(DBQuery("SELECT ID FROM schools WHERE ID NOT IN (SELECT school_id FROM staff_school_relationship WHERE
                                      STAFF_ID='" . $_REQUEST['staff_id'] . "' AND SYEAR='" . UserSyear() . "')"));
            foreach ($school_id3 as $index => $val) {

                $sql_up = 'INSERT INTO staff_school_relationship(staff_id,syear,school_id';
                $sql_up .= ')VALUES(\'' . $_REQUEST['staff_id'] . '\',\'' . UserSyear() . '\',\'' . $val['ID'] . '\'';


                $sql_up .= ')';
            }
        }
    }
} else {
    $STAFF_SCHOOL_COUNT = 0;
    if (isset($_REQUEST['values']['SCHOOLS']) && is_countable($_REQUEST['values']['SCHOOLS']))
        $STAFF_SCHOOL_COUNT = count($_REQUEST['values']['SCHOOLS']);

    if ($_REQUEST['values']['SCHOOL']['OPENSIS_ACCESS'] == 'Y') {
        if ($STAFF_SCHOOL_COUNT == 0) {
            $sch_err = "<div class=\"alert bg-danger alert-styled-left\">" . _pleaseSelectAtleastOneSchool . "</div>";
        }
        $sql = "UPDATE staff_school_info  SET ";
        foreach ($_REQUEST['values']['SCHOOL'] as $column => $value) {

            if (strtoupper($column) == 'OPENSIS_PROFILE' || strtoupper($column) == 'CATEGORY') {
                $check_prof = DBGet(DBQuery('SELECT * FROM staff_school_info WHERE STAFF_ID=' . UserStaffID()));
                if (strtoupper($column) == 'OPENSIS_PROFILE' && $value != $check_prof[1]['OPENSIS_PROFILE']) {
                    if ($value != '') {
                        $check_staff_cp = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM course_periods WHERE TEACHER_ID=' . UserStaffID() . ' OR SECONDARY_TEACHER_ID=' . UserStaffID() . ''));
                    }
                    if ($check_staff_cp[1]['TOTAL_ASSIGNED'] == 0 && $value != '') {
                        $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                    }
                    if ($check_staff_cp[1]['TOTAL_ASSIGNED'] > 0 && $value != '') {
                        $get_staff_prof = DBGet(DBQuery('SELECT PROFILE FROM user_profiles WHERE ID=' . $value));
                        if ($get_staff_prof[1]['PROFILE'] == 'teacher') {
                            DBQuery('UPDATE staff SET PROFILE_ID=' . $value . ',PROFILE=\'teacher\' WHERE STAFF_ID=' . UserStaffID());
                            DBQuery('UPDATE staff_school_info SET OPENSIS_PROFILE=' . $value . ' WHERE STAFF_ID=' . UserStaffID());
                        } else {
                            if (strtoupper($column) == 'OPENSIS_PROFILE')
                                echo '<script type=text/javascript>document.getElementById(\'prof_err\').innerHTML=\'<font color=red><b>Cannot change the profile as this staff has one or more course periods.</b></font>\';</script>';
                        }
                    }
                }
                if (strtoupper($column) == 'CATEGORY' && $value != $check_prof[1]['CATEGORY']) {
                    if ($value != '') {
                        $check_staff_cp = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM course_periods WHERE TEACHER_ID=' . UserStaffID() . ' OR SECONDARY_TEACHER_ID=' . UserStaffID() . ''));
                    }
                    if ($check_staff_cp[1]['TOTAL_ASSIGNED'] == 0 && $value != '') {
                        $go = true;

                        $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                    }
                    if ($check_staff_cp[1]['TOTAL_ASSIGNED'] > 0 && $value != '') {
                        if (strtoupper($column) == 'CATEGORY')
                            echo '<script type=text/javascript>document.getElementById(\'cat_err\').innerHTML=\'<font color=red><b>Cannot change the category as this staff has one or more course periods.</b></font>\';</script>';
                    }
                }
            } else
                $sql .= "$column='" . singleQuoteReplace('', '', $value) . "',";
        }
        $sql = substr($sql, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
        DBQuery($sql);
        $update_staff_RET = DBGet(DBQuery("SELECT  * FROM staff_school_info where STAFF_ID='" . UserStaffID() . "'"));
        $update_staff = $update_staff_RET[1];
        $profile_name_RET = DBGet(DBQuery("SELECT PROFILE from user_profiles WHERE id=" . $update_staff['OPENSIS_PROFILE']));
        $profile = $profile_name_RET[1]['PROFILE'];
        $staff_CHECK = DBGet(DBQuery("SELECT  s.*,l.*  FROM staff s,login_authentication l where s.STAFF_ID='" . UserStaffID() . "' AND l.USER_ID=s.STAFF_ID AND l.PROFILE_ID NOT IN (3,4) "));
        $staff = $staff_CHECK[1];

        $sql_staff = "UPDATE staff SET ";

        $sql_staff .= " PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',
                                       PROFILE='" . $profile . "',CURRENT_SCHOOL_ID='" . $_REQUEST['staff_school']['CURRENT_SCHOOL_ID'] . "',";

        foreach ($_REQUEST['staff_school'] as $field => $value) {
            if ($field == 'IS_DISABLE') {
                if ($value) {
                    $sql_staff .= $field . "='" . singleQuoteReplace('', '', $value) . "',";
                }
            } elseif ($field == 'PASSWORD') {
                $password = ($value);
                /*$sql = DBQuery('SELECT PASSWORD FROM login_authentication WHERE PASSWORD=\'' . $password . '\'');
                $number = $sql->num_rows;*/

                //code for match password in login table
                $number = 0;
                $sqlquery = DBQuery('SELECT PASSWORD FROM login_authentication');
                foreach ($sqlquery as $val) {
                    $sqloldpass = $val['PASSWORD'];
                    $login_status = VerifyHash($password, $sqloldpass);
                    if ($login_status == 1) {
                        $number = $number + 1;
                    }
                }
                //end

                if ($number == 0) {
                    if ((!$staff['USERNAME']) && (!$staff['PASSWORD'])) {
                        $sql_staff_pwd = $field . "=NULL";
                    } else {
                        $value = singleQuoteReplace('', '', ($value));
                        $new_password = GenerateNewHash($value);
                        $sql_staff_pwd = $field . "='" . $new_password . "'";
                    }
                }
            }
        }
        $sql_staff = substr($sql_staff, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
        if ($sql_staff_pwd != '')
            $sql_staff_pwd = 'Update login_authentication SET ' . $sql_staff_pwd . ' WHERE USER_ID=' . UserStaffID() . ' AND PROFILE_ID=' . SelectedUserProfile('PROFILE_ID');

        if ($update_staff['OPENSIS_PROFILE'] != '') {
            $check_rec = DBGet(DBQuery('SELECT COUNT(1) AS REC_EXISTS FROM login_authentication WHERE USER_ID=' . UserStaffID() . ' AND PROFILE_ID NOT IN (3,4) '));
            if ($check_rec[1]['REC_EXISTS'] == 0)
                $sql_staff_prf = 'INSERT INTO login_authentication (PROFILE_ID,USER_ID) VALUES (\'' . $update_staff['OPENSIS_PROFILE'] . '\',\'' . UserStaffID() . '\') ';
            else
                $sql_staff_prf = 'Update login_authentication SET  PROFILE_ID=\'' . $update_staff['OPENSIS_PROFILE'] . '\' WHERE PROFILE_ID NOT IN (3,4) AND USER_ID=' . UserStaffID();
        }

        DBQuery($sql_staff);
        if ($sql_staff_pwd != '')
            DBQuery($sql_staff_pwd);

        if ($update_staff['OPENSIS_PROFILE'] != '')
            DBQuery($sql_staff_prf);

        if ($_REQUEST['USERNAME'] != '') {
            $usernameExists = DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME=\'' . $_REQUEST['USERNAME'] . '\''));
            if ($staff_prof_id == '') {
                $staff_info_sql = "SELECT PROFILE_ID FROM staff WHERE STAFF_ID=" . $_REQUEST['staff_id'];
                $staff_info = DBGet(DBQuery($staff_info_sql));
                $staff_prof_id = $staff_info[1]['PROFILE_ID'];
            }
            $sql_staff_username = "UPDATE login_authentication l,staff s, staff_school_info ssi SET
                                l.username = '" . $_REQUEST['USERNAME'] . "'
                                WHERE s.staff_id = ssi.staff_id AND l.user_id=s.staff_id AND l.profile_id NOT IN (3,4) AND s.staff_id = " . UserStaffID();
            if(count($usernameExists) == 0){
                DBQuery($sql_staff_username);
            } else {
                if($usernameExists[1]['USER_ID'] != $_REQUEST['staff_id'] || $usernameExists[1]['PROFILE_ID'] != $staff_prof_id){
                    echo '<font color=red><b>Username already exists.</b></font>';
                }
            }
        }
        if ((!$staff['USERNAME']) && (!$staff['PASSWORD']) && $_REQUEST['USERNAME'] != '' && $_REQUEST['PASSWORD'] != '') {

            $new_password_hash = GenerateNewHash($_REQUEST['PASSWORD']);
            $sql_staff_algo = "UPDATE login_authentication l,staff s, staff_school_info ssi SET
                                l.username = '" . $_REQUEST['USERNAME'] . "',
                               l.password ='" . $new_password_hash . "' 
                                WHERE s.staff_id = ssi.staff_id AND l.user_id=s.staff_id AND l.profile_id NOT IN (3,4) AND s.staff_id = " . UserStaffID();



            DBQuery($sql_staff_algo);
        }
        if ($update_staff['OPENSIS_PROFILE'] == '1') {

            $school_id3 = DBGet(DBQuery("SELECT ID FROM schools WHERE ID NOT IN (SELECT school_id FROM staff_school_relationship WHERE
                                      STAFF_ID='" . $_REQUEST['staff_id'] . "' AND SYEAR='" . UserSyear() . "')"));
            foreach ($school_id3 as $index => $val) {

                $sql_up = 'INSERT INTO staff_school_relationship(staff_id,syear,school_id';
                $sql_up .= ')VALUES(\'' . $_REQUEST['staff_id'] . '\',\'' . UserSyear() . '\',\'' . $val['ID'] . '\'';


                $sql_up .= ')';
            }
        }
    } elseif ($_REQUEST['values']['SCHOOL']['OPENSIS_ACCESS'] == 'N') {
        if ($STAFF_SCHOOL_COUNT == 0) {
            $sch_err = "<div class=\"alert bg-danger alert-styled-left\">" . _pleaseSelectAtleastOneSchool . "</div>";
        }

        $sql = "UPDATE staff_school_info  SET ";

        foreach ($_REQUEST['values']['SCHOOL'] as $column => $value) {
            //                                                 if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
            //                                                        $sql .= "$column='".str_replace("'","\'",str_replace("`","''",$value))."',";
            //                                                        }else
            $sql .= "$column='" . singleQuoteReplace('', '', $value) . "',";
        }
        $sql = substr($sql, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
        DBQuery($sql);

        if (isset($_REQUEST['values']['SCHOOL']['OPENSIS_PROFILE']) && $_REQUEST['values']['SCHOOL']['OPENSIS_PROFILE'] != '') {

            $update_staff_RET = DBGet(DBQuery("SELECT  * FROM staff_school_info where STAFF_ID='" . UserStaffID() . "'"));
            $update_staff = $update_staff_RET[1];
            $staff_CHECK = DBGet(DBQuery("SELECT  *  FROM staff where STAFF_ID='" . UserStaffID() . "'"));
            $staff = $staff_CHECK[1];

            if ($update_staff['OPENSIS_PROFILE'] != '') {
                $profile_det = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $update_staff['OPENSIS_PROFILE']));

                $sql_staff = "UPDATE staff SET ";
                $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',PROFILE='" . $profile_det[1]['PROFILE'] . "' ";
            } else {
                $sql_staff = "UPDATE staff SET ";
                $sql_staff .= "PROFILE_ID='" . $update_staff['OPENSIS_PROFILE'] . "',";
            }
            $sql_staff = substr($sql_staff, 0, -1) . " WHERE STAFF_ID='" . UserStaffID() . "'";
            DBQuery($sql_staff);


            if ($update_staff['OPENSIS_PROFILE'] != '') {
                $check_rec = DBGet(DBQuery('SELECT COUNT(1) AS REC_EXISTS FROM login_authentication WHERE USER_ID=' . UserStaffID() . ' AND PROFILE_ID NOT IN (3,4) '));
                if ($check_rec[1]['REC_EXISTS'] == 0)
                    $sql_staff_prf = 'INSERT INTO login_authentication (PROFILE_ID,USER_ID) VALUES (\'' . $update_staff['OPENSIS_PROFILE'] . '\',\'' . UserStaffID() . '\') ';
                else
                    $sql_staff_prf = 'Update login_authentication SET  PROFILE_ID=\'' . $update_staff['OPENSIS_PROFILE'] . '\' WHERE PROFILE_ID NOT IN (3,4) AND USER_ID=' . UserStaffID();
            }


            if ($update_staff['OPENSIS_PROFILE'] != '')
                DBQuery($sql_staff_prf);

            if ($update_staff['OPENSIS_PROFILE'] == '1') {

                $school_id3 = DBGet(DBQuery("SELECT ID FROM schools WHERE ID NOT IN (SELECT school_id FROM staff_school_relationship WHERE
                                      STAFF_ID='" . $_REQUEST['staff_id'] . "' AND SYEAR='" . UserSyear() . "')"));
                foreach ($school_id3 as $index => $val) {

                    $sql_up = 'INSERT INTO staff_school_relationship(staff_id,syear,school_id';
                    $sql_up .= ')VALUES(\'' . $_REQUEST['staff_id'] . '\',\'' . UserSyear() . '\',\'' . $val['ID'] . '\'';


                    $sql_up .= ')';

                    DBQuery($sql_up);
                }
            }
        }

        unset($_REQUEST['values']['SCHOOL']['SCHOOL_ACCESS']);
        unset($_REQUEST['values']['SCHOOL']['OPENSIS_PROFILE']);
    }
}
if ($sch_err != '') {
    echo $sch_err;
    unset($sch_err);
}
if (!$_REQUEST['modfunc']) {
    # FIX: If in any case the profile_id from the `staff` table is missing,
    # but present in the `staff_school_info` table.
    $get_staff_profile_info = DBGet(DBQuery('SELECT st.STAFF_ID, st.PROFILE, st.PROFILE_ID, sci.OPENSIS_PROFILE FROM staff st LEFT JOIN staff_school_info sci ON st.STAFF_ID = sci.STAFF_ID WHERE st.STAFF_ID = ' . UserStaffID()));
    if (!empty($get_staff_profile_info)) {
        if ($get_staff_profile_info[1]['PROFILE_ID'] == '' && trim($get_staff_profile_info[1]['OPENSIS_PROFILE']) != '') {
            $potential_profile = substr($get_staff_profile_info[1]['OPENSIS_PROFILE'], 0, 1);
            DBQuery('UPDATE staff SET PROFILE_ID = \'' . $potential_profile . '\' WHERE STAFF_ID = ' . UserStaffID());
        }
    }

    $this_school_RET = DBGet(DBQuery("SELECT * FROM staff_school_info   WHERE   STAFF_ID=" . UserStaffID()));
    $this_school = $this_school_RET[1];

    $this_school_RET_mod = DBGet(DBQuery("SELECT s.*,l.* FROM staff s,login_authentication l  WHERE l.USER_ID=s.STAFF_ID AND l.PROFILE_ID NOT IN (3,4) AND s.STAFF_ID=" . UserStaffID()));

    $this_school_mod = $this_school_RET_mod[1];


    if (User('PROFILE') == 'admin')
        $profiles_options = DBGet(DBQuery("SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID <> 3 AND PROFILE <> 'parent' AND ID<>0 ORDER BY ID"));

    $prof_check = DBGet(DBQuery('SELECT PROFILE_ID FROM staff WHERE STAFF_ID=' . UserStaffID()));
    if (User('PROFILE_ID') == 0 && $prof_check[1]['PROFILE_ID'] == 0)
        $profiles_options = DBGet(DBQuery("SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID <> 3  AND PROFILE <> 'parent' ORDER BY ID"));
    if (User('PROFILE_ID') == 0 && $prof_check[1]['PROFILE_ID'] != 0)
        $profiles_options = DBGet(DBQuery("SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE ID <> 0  AND PROFILE <> 'parent' AND ID<>'4' ORDER BY ID"));

    if (User('PROFILE_ID') == 2)
        $profiles_options = DBGet(DBQuery("SELECT PROFILE ,TITLE, ID FROM user_profiles WHERE  PROFILE ='teacher' ORDER BY ID"));
    $i = 1;
    foreach ($profiles_options as $options) {
        if ($options['PROFILE'] != 'student')
            $option[$options['ID']] = $options['TITLE'];
        $i++;
    }
    if (is_countable($option) && count($option) == 0 && User('PROFILE') != 'admin') {
        $profiles_options = DBGet(DBQuery('SELECT TITLE, ID FROM user_profiles WHERE ID=' . User('PROFILE_ID')));
        $option[$profiles_options[1]['ID']] = $profiles_options[1]['TITLE'];
    }
    $_REQUEST['category_id'] = 3;
    $_REQUEST['custom'] = 'staff';
    include('modules/users/includes/OtherInfoInc.inc.php');


    $style = '';


    if (isset($_REQUEST['school_info_id'])) {
        $get_end_date = DBGet(DBQuery('SELECT MAX(END_DATE) AS END_DATE FROM school_years WHERE  SYEAR=' . UserSyear()));
        $get_end_date = $get_end_date[1]['END_DATE'];


        echo "<INPUT type=hidden name=school_info_id value=$_REQUEST[school_info_id]>";

        if ($_REQUEST['school_info_id'] != '0' && $_REQUEST['school_info_id'] !== 'old') {

            echo '<h5 class="text-primary">' . _officialInformation . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if (User('PROFILE_ID') == 0 && $prof_check[1]['PROFILE_ID'] == 0 && User('STAFF_ID') == UserStaffID())
                echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _category . ' <span class=text-danger>*</span></label><div class="col-lg-8">' . SelectInput($this_school['CATEGORY'], 'values[SCHOOL][CATEGORY]', '', array(
                    'Super Administrator' => _superAdministrator,
                    'Administrator' => _administrator,
                    'Teacher' => _teacher,
                    'Non Teaching Staff' => _nonTeachingStaff,
                    'Custodian' => _custodian,
                    'Principal' => _principal,
                    'Clerk' => _clerk,
                ), false) . '</div></div>';
            else
                echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _category . ' <span class=text-danger>*</span></label><div class="col-lg-8">' . SelectInput($this_school['CATEGORY'], 'values[SCHOOL][CATEGORY]', '', array(
                    'Administrator' => _administrator,
                    'Teacher' => _teacher,
                    'Non Teaching Staff' => _nonTeachingStaff,
                    'Custodian' => _custodian,
                    'Principal' => _principal,
                    'Clerk' => _clerk,
                ), false) . '</div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_school['JOB_TITLE'], 'values[SCHOOL][JOB_TITLE]', _jobTitle, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _joiningDate . ' <span class=text-danger>*</span></label><div class="col-lg-8">' . DateInputAY(isset($this_school['JOINING_DATE']) && $this_school['JOINING_DATE'] != "" ? $this_school['JOINING_DATE'] : "", 'values[JOINING_DATE]', 1, '') . '</div></div>';
            echo '<input type=hidden id=end_date_school value="' . $get_end_date . '" >';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _endDate . '</label><div class="col-lg-8">' . DateInputAY($this_school['END_DATE'] != "" ? $this_school['END_DATE'] : "", 'values[ENDING_DATE]', 2, '') . '</div></div>';
            echo "<INPUT type=hidden name=values[SCHOOL][HOME_SCHOOL] value=" . UserSchool() . ">";
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            $staff_profile = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _profile . '</label><div class="col-lg-8">' . SelectInput($this_school['OPENSIS_PROFILE'], 'values[SCHOOL][OPENSIS_PROFILE]', '', $option, false, 'id=values[SCHOOL][OPENSIS_PROFILE]') . '</div></div>';
            echo '</div>'; //.col-lg-6            
            echo '</div>'; //.row

            echo '';

            if ($this_school_mod['USERNAME'] && (!$this_school['OPENSIS_ACCESS'] == 'Y')) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';
                echo '<h5 class="text-primary inline-block">' . _openSisAccessInformation . '</h5><div class="inline-block p-l-15"><label class="radio-inline p-t-0"><input type="radio" id="noaccs" name="values[SCHOOL][OPENSIS_ACCESS]" value="N" onClick="hidediv();">' . _noAccess . '</label><label class="radio-inline p-t-0"><input type="radio" id="r4" name="values[SCHOOL][OPENSIS_ACCESS]" value="Y" onClick="showdiv();" checked>' . _access . '</label></div>';
                echo '</div>'; //.col-md-6
                echo '</div>'; //.row
                echo '<div id="hideShow" class="mt-15">';
            } elseif ($this_school_mod['USERNAME'] && $this_school_mod['PASSWORD'] && $this_school['OPENSIS_ACCESS']) {
                if ($this_school['OPENSIS_ACCESS'] == 'N') {
                    echo '<div class="row">';
                    echo '<div class="col-md-12">';
                    echo '<h5 class="text-primary inline-block">' . _openSisAccessInformation . '</h5><div class="inline-block p-l-15"><label class="radio-inline p-t-0"><input type="radio" id="noaccs" name="values[SCHOOL][OPENSIS_ACCESS]" value="N" checked>' . _noAccess . '</label><label class="radio-inline p-t-0"><input type="radio" id="r4" name="values[SCHOOL][OPENSIS_ACCESS]" value="Y" >' . _access . '</label></div>';
                    echo '</div>'; //.col-md-6
                    echo '</div>'; //.row
                } elseif ($this_school['OPENSIS_ACCESS'] == 'Y') {
                    echo '<div class="row">';
                    echo '<div class="col-md-12">';
                    echo '<h5 class="text-primary inline-block">' . _openSisAccessInformation . '</h5><div class="inline-block p-l-15"><label class="radio-inline p-t-0"><input type="radio" id="noaccs" name="values[SCHOOL][OPENSIS_ACCESS]" value="N">' . _noAccess . '</label><label class="radio-inline p-t-0"><input type="radio" id="r4" name="values[SCHOOL][OPENSIS_ACCESS]" value="Y"  checked>&nbsp;' . _access . '</label></div>';
                    echo '</div>'; //.col-md-6
                    echo '</div>'; //.row
                }
                echo '<div id="hideShow" class="mt-15">';
            } elseif (!$this_school_mod['USERNAME'] || $this_school['OPENSIS_ACCESS'] == 'N') {
                echo '<div class="row">';
                echo '<div class="col-md-12">';
                echo '<h5 class="text-primary inline-block">' . _openSisAccessInformation . '</h5><div class="inline-block p-l-15"><label class="radio-inline p-t-0"><input type="radio" id="noaccs" name="values[SCHOOL][OPENSIS_ACCESS]" value="N" onClick="hidediv();" checked>' . _noAccess . '</label><label class="radio-inline p-t-0"><input type="radio" id="r4" name="values[SCHOOL][OPENSIS_ACCESS]" value="Y" onClick="showdiv();">&nbsp;' . _access . '</label></div>';
                echo '</div>'; //.col-md-6
                echo '</div>'; //.row
                echo '<div id="hideShow" class="mt-15" style="display:none">';
            }


            //            $staff_profile = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
            //            echo '<div class="row">';
            //            echo '<div class="col-lg-6">';
            //            echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._profile.'</label><div class="col-lg-8">' . SelectInput($this_school['OPENSIS_PROFILE'], 'values[SCHOOL][OPENSIS_PROFILE]', '', $option, false, 'id=values[SCHOOL][OPENSIS_PROFILE]') . '</div></div>';
            //            echo '</div>'; //.col-lg-6            
            //            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _username . ' <span class=text-danger>*</span></label><div class="col-lg-8">';
            if (!$this_school_mod['USERNAME']) {
                echo TextInput('', 'USERNAME', '', 'id=USERNAME size=20 maxlength=50 onkeyup="usercheck_init_staff(this, \'' . $this_school_mod['STAFF_ID'] . '\', \'' . $this_school_mod['PROFILE_ID'] . '\')" onblur="usercheck_init_staff(this, \'' . $this_school_mod['STAFF_ID'] . '\', \'' . $this_school_mod['PROFILE_ID'] . '\')"');
                echo '<span id="ajax_output_st"></span><input type=hidden id=usr_err_check value=0>';
            } else {
                echo '<input id="USERNAME" type="text" name="USERNAME" value="'.$this_school_mod['USERNAME'].'" onkeyup="usercheck_init_staff(this, ' . $this_school_mod['STAFF_ID'] . ', ' . $this_school_mod['PROFILE_ID'] . ')" onblur="usercheck_init_staff(this, ' . $this_school_mod['STAFF_ID'] . ', ' . $this_school_mod['PROFILE_ID'] . ')" class="form-control">';
                echo '<span id="ajax_output_st"></span><input type=hidden id=usr_err_check value=0>';
            }
            echo '</div></div>';
            echo '</div>'; //.col-lg-6
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _password . ' <span class=text-danger>*</span></label><div class="col-lg-8">';
            if (!$this_school_mod['PASSWORD']) {
                echo TextInputModHidden('', 'PASSWORD', '', 'size=20 maxlength=100 AUTOCOMPLETE = off onblur=passwordStrength(this.value);validate_password_staff(this.value);');

                echo '<span id="ajax_output_st"></span>';
            } else {
                echo TextInputModHidden(array($this_school_mod['PASSWORD'], str_repeat('*', strlen($this_school_mod['PASSWORD']))), 'staff_school[PASSWORD]', '', 'size=20 maxlength=100 AUTOCOMPLETE = off onkeyup=passwordStrength(this.value);validate_password(this.value);');
            }
            echo "<span id='passwordStrength'></span></div></div>";
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            if ($this_school_mod['USERNAME'] && $this_school_mod['USERNAME'] != '') {
                echo '<input id="staff_username_flag" type="hidden" value="1">';
            } else {
                echo '<input id="staff_username_flag" type="hidden" value="0">';
            }

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _disableUser . '</label><div class="col-lg-8">';
            if ($this_school_mod['IS_DISABLE'] == 'Y')
                $dis_val = 'Y';
            else
                $dis_val = 'N';
            echo CheckboxInput_No($dis_val, 'staff_school[IS_DISABLE]', '', 'CHECKED', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>');
            echo '</div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '</div>'; //#hideShow

            if ($this_school['SCHOOL_ACCESS']) {

                $pieces = explode(",", $this_school['SCHOOL_ACCESS']);
            }


            $profile_return = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
            if ($profile_return[1]['PROFILE_ID'] != '') {
                echo '<h5 class="text-primary">' . _schoolInformation . '</h5>';
                echo '<hr class="m-b-0" />';
                $functions = array('START_DATE' => '_makeStartInputDate', 'PROFILE' => '_makeUserProfile', 'END_DATE' => '_makeEndInputDate','SCHOOL_ID' => '_makeCheckBoxInput_gen', 'ID' => '_makeStatus');

                $sql = 'SELECT s.ID,ssr.SCHOOL_ID as SCH_ID,ssr.SCHOOL_ID,s.TITLE,ssr.START_DATE,ssr.END_DATE,st.PROFILE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id  AND st.staff_id=\'' . User('STAFF_ID') . '\' AND ssr.SYEAR=\'' . UserSyear() . '\' GROUP BY ssr.SCHOOL_ID';
                $school_admin = DBGet(DBQuery($sql), $functions);
                //print_r($school_admin);
                //                $columns = array('SCHOOL_ID' => '<a><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'unused\');" /></a>', 'TITLE' => 'School', 'PROFILE' => 'Profile', 'START_DATE' => 'Start Date', 'END_DATE' => 'Drop Date', 'ID' => 'Status');

                $columns = array(
                    'SCHOOL_ID' => '<a><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'values[SCHOOLS]\');" /></a>',
                    'TITLE' => _school,
                    'PROFILE' => _profile,
                    'START_DATE' => _startDate,
                    'END_DATE' => _dropDate,
                    'ID' => _status,
                );
                
                $school_ids_for_hidden = array();
                echo '<div id="hidden_checkboxes">';
                foreach ($school_admin as $sai => $sad) {
                    //                    echo '<pre>';
                    //                    print_r($sad);
                    $school_ids_for_hidden[] = $sad['SCH_ID'];
                    if (strip_tags($sad['ID']) == 'Active')
                        echo '<input type=hidden name="values[SCHOOLS][' . $sad['SCH_ID'] . ']" value="Y" data-checkbox-hidden-id="' . $sad['SCH_ID'] . '" />';
                }
                echo '</div>';
                $school_ids_for_hidden = implode(',', $school_ids_for_hidden);
                echo '<input type=hidden id=school_ids_hidden value="' . $school_ids_for_hidden . '" />';

                $check_all_arr = array();
                foreach ($school_admin as $xy) {

                    $check_all_arr[] = $xy['SCH_ID'];
                }
                $check_all_stu_list = implode(',', $check_all_arr);
                echo '<input type=hidden name=res_length id=res_length value=\'' . count($check_all_arr) . '\'>';
                echo '<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';

                ListOutputStaffPrintSchoolInfo($school_admin, $columns, _schoolRecord, _schoolRecords, array(), array(), array('search' => false, 'sort' => false));
            }
        }
    } else
        echo '';
    $separator = '<HR>';
}

function CheckboxInput_No($value, $name, $title = '', $checked = '', $new = false, $yes = 'yes', $no = 'no', $div = true, $extra = '')
{
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false) {
            return "<INPUT type=checkbox name=$name value=Y  $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        } else {
            if ($value == '' || $value == 'N')
                return "<DIV id='div$name' class=\"form-control\" readonly=\"readonly\"><INPUT type=checkbox name=$name " . (($value == 'Y') ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></DIV>";
            else
                return "<DIV id='div$name' class=\"form-control\" readonly=\"readonly\"><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name value=\\\"N\\\"><INPUT type=checkbox name=$name " . (($value == 'Y') ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . ">" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . (($value != 'N') ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>";
        }
    } else
        return (($value != 'N') ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function _makeStartInputDate($value, $column)
{
    global $THIS_RET;
    
    if ($_REQUEST['staff_id'] == 'new') {
        $date_value = '';
    } else {
        $sql = 'SELECT ssr.START_DATE FROM staff s,staff_school_relationship ssr  WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=' . UserSyear();
        $user_exist_school = DBGet(DBQuery($sql));
        if ($user_exist_school[1]['START_DATE'] == '0000-00-00' || $user_exist_school[1]['START_DATE'] == '')
            $date_value = '';
        else
            $date_value = $user_exist_school[1]['START_DATE'];
    }
    
    return '<TABLE class=LO_field><TR>' . '<TD nowrap="nowrap">' . DateInputAY($date_value != '' ? $date_value : $date_value, 'values[START_DATE][' . $THIS_RET['ID'] . ']', '1' . $THIS_RET['ID']) . '</TD></TR></TABLE>';
}

function _makeUserProfile($value, $column)
{
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new') {
        $profile_value = '';
    } else {
        $sql = 'SELECT up.TITLE FROM staff s,staff_school_relationship ssr,user_profiles up  WHERE ssr.STAFF_ID=s.STAFF_ID AND up.ID=s.PROFILE_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=   (SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        $user_profile = DBGet(DBQuery($sql));
        $profile_value = $user_profile[1]['TITLE'];
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . $profile_value . '</TD></TR></TABLE>';
}

function _makeEndInputDate($value, $column)
{
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new') {
        $date_value = '';
    } else {

        $sql = 'SELECT ssr.END_DATE FROM staff s,staff_school_relationship ssr  WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=   (SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        $user_exist_school = DBGet(DBQuery($sql));
        if ($user_exist_school[1]['END_DATE'] == '0000-00-00' || $user_exist_school[1]['END_DATE'] == '')
            $date_value = '';
        else
            $date_value = $user_exist_school[1]['END_DATE'];
    }
    if (SelectedUserProfile('PROFILE_ID') == 0)
        return '<TABLE class=LO_field><TR>' . '<TD nowrap="nowrap">' . ProperDateAY($date_value) . '</TD></TR></TABLE>';
    else
        return '<TABLE class=LO_field><TR>' . '<TD nowrap="nowrap">' . DateInputAY($date_value, 'values[END_DATE][' . $THIS_RET['ID'] . ']', '2' . $THIS_RET['ID']) . '</TD></TR></TABLE>';
}

function _makeCheckBoxInput_gen($value, $column)
{
    global $THIS_RET;

    $_SESSION['staff_school_chkbox_id']++;
    $staff_school_chkbox_id = $_SESSION['staff_school_chkbox_id'];
    if ($_REQUEST['staff_id'] == 'new') {
        return '<TABLE class=LO_field><TR>' . '<TD>' . "<input name=unused[$THIS_RET[ID]]  type='checkbox' id=$staff_school_chkbox_id onClick='setHiddenCheckbox(\"values[SCHOOLS][$THIS_RET[ID]]\",this,$THIS_RET[ID]);' />" . '</TD></TR></TABLE>';
    } else {
        $sql = '';
        $staff_infor_qr = DBGet(DBQuery('select * from staff_school_relationship where STAFF_ID=\'' . $_SESSION['staff_selected'] . '\' AND SYEAR=' . UserSyear()));
        if (count($staff_infor_qr) > 0) {
            $i = 0;
            foreach ($staff_infor_qr as $skey => $sval) {
                $sch_li[$i] = $sval['SCHOOL_ID'];
                $i++;
            }
        }
        //$sch_li = explode(',', trim($staff_infor_qr[1]['SCHOOL_ACCESS']));
        $dates = DBGet(DBQuery("SELECT ssr.START_DATE,ssr.END_DATE FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='" . $THIS_RET['SCHOOL_ID'] . "' AND ssr.STAFF_ID='" . $_SESSION['staff_selected'] . "' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='" . $THIS_RET['SCHOOL_ID'] . "' AND STAFF_ID='" . $_SESSION['staff_selected'] . "')"));
        if ($dates[1]['START_DATE'] == '0000-00-00' && $dates[1]['END_DATE'] == '0000-00-00' && in_array($THIS_RET['SCHOOL_ID'], $sch_li)) {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        }
        if ($dates[1]['START_DATE'] == '0000-00-00' && $dates[1]['END_DATE'] != '0000-00-00' && in_array($THIS_RET['SCHOOL_ID'], $sch_li)) {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ') AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE<\'0000-01-01\' OR ssr.END_DATE IS NULL)';
        }
        if ($dates[1]['START_DATE'] != '0000-00-00' && in_array($THIS_RET['SCHOOL_ID'], $sch_li)) {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')  AND (ssr.START_DATE>=ssr.END_DATE OR ssr.START_DATE<\'0000-01-01\' OR ssr.END_DATE>=CURDATE() OR ssr.END_DATE IS NULL)';
        }
        if ($sql != '')
            $user_exist_school = DBGet(DBQuery($sql));
        else
            $user_exist_school = array();
        if (!empty($user_exist_school)) {
            if (SelectedUserProfile('PROFILE_ID') == 0)
                return '<TABLE class=LO_field><TR>' . '<TD>' . "<input checked name=unused[$THIS_RET[ID]] type='checkbox'  id=$THIS_RET[ID] onClick='setHiddenCheckbox(\"values[SCHOOLS][$THIS_RET[ID]]\",this,$THIS_RET[ID]);'  />" . '</TD></TR></TABLE>';
            else
                return '<TABLE class=LO_field><TR>' . '<TD>' . "<input checked name=unused[$THIS_RET[ID]]  type='checkbox' id=$THIS_RET[ID] onClick='setHiddenCheckbox(\"values[SCHOOLS][$THIS_RET[ID]]\",this,$THIS_RET[ID]);' />" . '</TD></TR></TABLE>';
        } else {
            if (SelectedUserProfile('PROFILE_ID') == 0)
                return '<TABLE class=LO_field><TR>' . '<TD>' . "<input name=unused[$THIS_RET[ID]]  type='checkbox' id=$THIS_RET[ID] onClick='setHiddenCheckbox(\"values[SCHOOLS][$THIS_RET[ID]]\",this,$THIS_RET[ID]);' />" . '</TD></TR></TABLE>';
            else
                return '<TABLE class=LO_field><TR>' . '<TD>' . "<input name=unused[$THIS_RET[ID]]  type='checkbox' id=$THIS_RET[ID] onClick='setHiddenCheckbox(\"values[SCHOOLS][$THIS_RET[ID]]\",this,$THIS_RET[ID]);' />" . '</TD></TR></TABLE>';
        }
    }
}

function _makeStatus($value, $column)
{
    global $THIS_RET;
    if ($_REQUEST['staff_id'] == 'new')
        $status_value = '';
    else {

        $dates = DBGet(DBQuery("SELECT ssr.START_DATE,ssr.END_DATE FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID='" . $THIS_RET['SCHOOL_ID'] . "' AND ssr.STAFF_ID='" . $_SESSION['staff_selected'] . "' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID='" . $THIS_RET['SCHOOL_ID'] . "' AND STAFF_ID='" . $_SESSION['staff_selected'] . "')"));
        if ($dates[1]['START_DATE'] == '0000-00-00' && $dates[1]['END_DATE'] == '0000-00-00') {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')';
        }

        if ($dates[1]['START_DATE'] == '0000-00-00' && $dates[1]['END_DATE'] != '0000-00-00') {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ') AND (ssr.END_DATE>=CURDATE() OR ssr.END_DATE<\'0000-01-01\' OR ssr.END_DATE IS NULL)';
        }
        if ($dates[1]['START_DATE'] != '0000-00-00' && $dates[1]['END_DATE'] == '0000-00-00') {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ') ';
        }
        if ($dates[1]['START_DATE'] != '0000-00-00' && $dates[1]['END_DATE'] != '0000-00-00') {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')  AND ssr.END_DATE>=\'' . date('Y-m-d') . '\' ';
        }
        if ($dates[1]['START_DATE'] != '0000-00-00') {
            $sql = 'SELECT SCHOOL_ID FROM staff s,staff_school_relationship ssr WHERE ssr.STAFF_ID=s.STAFF_ID AND ssr.SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND ssr.STAFF_ID=' . $_SESSION['staff_selected'] . ' AND ssr.SYEAR=(SELECT MAX(SYEAR) FROM  staff_school_relationship WHERE SCHOOL_ID=' . $THIS_RET['SCHOOL_ID'] . ' AND STAFF_ID=' . $_SESSION['staff_selected'] . ')  AND (ssr.END_DATE>=\'' . date('Y-m-d') . '\' OR ssr.END_DATE IS NULL OR ssr.END_DATE<\'0000-01-01\')';
        }
        $user_exist_school = DBGet(DBQuery($sql));
        if (!empty($user_exist_school))
            $status_value = 'Active';
        else {
            if ($dates[1]['START_DATE'] != '0000-00-00' && $dates[1]['END_DATE'] != '0000-00-00')
                $status_value = 'Inactive';
            else
                $status_value = '';
        }
    }
    return '<TABLE class=LO_field><TR>' . '<TD>' . $status_value . '</TD></TR></TABLE>';
}

?>
