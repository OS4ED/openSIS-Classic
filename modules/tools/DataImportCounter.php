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

session_start();


include('../../RedirectRootInc.php');
include '../../Warehouse.php';
include_once("../../functions/PasswordHashFnc.php");

$category = $_REQUEST['cat'];

echo '<div class="panel-body">';

if ($category == 'student') {

    if (count($_SESSION['data']) > 1) {
        $total_records = 0;
        $inserted_records = 0;
        $duplicate_records = 0;
        $arr_data = $_SESSION['data'];

        $temp_array_index = array();
        foreach ($arr_data[0] as $key => $value) {
            if ($value != '') {
                $temp_array_index[$value] = $key;
            }
        }

        foreach ($_SESSION['student'] as $key => $value) {
            if ($value == 'LANGUAGE')
                $value = 'LANGUAGE_ID';
            if ($value == 'ETHNICITY')
                $value = 'ETHNICITY_ID';
            if (!is_array($value) && $value != '') {
                $array_index[$value] = $temp_array_index[$key];
            }
        }
        $students = array('FIRST_NAME', 'LAST_NAME', 'MIDDLE_NAME', 'NAME_SUFFIX', 'GENDER', 'ETHNICITY_ID', 'COMMON_NAME', 'SOCIAL_SECURITY', 'BIRTHDATE', 'LANGUAGE_ID', 'ESTIMATED_GRAD_DATE', 'ALT_ID', 'EMAIL', 'PHONE', 'IS_DISABLE');
        $login_authentication = array('USERNAME', 'PASSWORD');
        $student_enrollments = array('GRADE_ID', 'SECTION_ID', 'START_DATE', 'END_DATE');
        $custom = DBGet(DBQuery('SELECT * FROM custom_fields'));
        foreach ($custom as $c) {
            $students[] = 'CUSTOM_' . $c['ID'];
        }


        $student_address = array('STREET_ADDRESS_1', 'STREET_ADDRESS_2', 'CITY', 'STATE', 'ZIPCODE');
        $primary = array('PRIMARY_FIRST_NAME', 'PRIMARY_MIDDLE_NAME', 'PRIMARY_LAST_NAME', 'PRIMARY_WORK_PHONE', 'PRIMARY_HOME_PHONE', 'PRIMARY_CELL_PHONE', 'PRIMARY_EMAIL', 'PRIMARY_RELATION');
        $secondary = array('SECONDARY_FIRST_NAME', 'SECONDARY_MIDDLE_NAME', 'SECONDARY_LAST_NAME', 'SECONDARY_WORK_PHONE', 'SECONDARY_HOME_PHONE', 'SECONDARY_CELL_PHONE', 'SECONDARY_EMAIL', 'SECONDARY_RELATION');

        // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'students'"));
        // $student_id[1]['STUDENT_ID'] = $id[1]['AUTO_INCREMENT'];
        // $student_id = $student_id[1]['STUDENT_ID'];
        $accepted = 0;
        $rejected = 0;
        $records = 0;
        $err_msg = array();
        foreach ($arr_data as $arr_i => $arr_v) {

            ///////////////////////////For Students////////////////////////////////////////////////////////////
            $student_columns = array();
            $student_values = array();
            if ($arr_i > 0) {


                // $student_columns = array('STUDENT_ID');
                // $student_values = array($student_id);
                $check_query = array();
                $check_query_alt_id = array();
                $check_query_username = array();
                $check_exist = 0;

                foreach ($students as $students_v) {

                    if (isset($array_index[$students_v]) && $arr_v[$array_index[$students_v]] != '') {
                        $student_columns[] = $students_v;
                        if ($students_v == 'BIRTHDATE' || $students_v == 'ESTIMATED_GRAD_DATE') {
                            $student_values[] = "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$students_v]])) . "'";
                        } elseif ($students_v == 'LANGUAGE_ID') {
                            if (is_numeric($arr_v[$array_index[$students_v]])) {
                                $student_values[] = "'" . $arr_v[$array_index[$students_v]] . "'";
                            } else {
                                $lang_id = DBGet(DBQuery('SELECT language_id FROM `language` WHERE LANGUAGE_NAME =\'' . $arr_v[$array_index[$students_v]] . '\''));
                                $student_values[] = "'" . $lang_id[1]['LANGUAGE_ID'] . "'";
                            }
                        } elseif ($students_v == 'ETHNICITY_ID') {
                            if (is_numeric($arr_v[$array_index[$students_v]])) {
                                $student_values[] = "'" . $arr_v[$array_index[$students_v]] . "'";
                            } else {
                                $lang_id = DBGet(DBQuery('SELECT ethnicity_id FROM `ethnicity` WHERE ethnicity_name =\'' . $arr_v[$array_index[$students_v]] . '\''));
                                $student_values[] = "'" . $lang_id[1]['ETHNICITY_ID'] . "'";
                            }
                        } else {
                            $student_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$students_v]]) . "'";
                        }
                        if ($students_v == 'FIRST_NAME' || $students_v == 'LAST_NAME' || $students_v == 'EMAIL' || $students_v == 'BIRTHDATE') {
                            $check_query[] = $students_v . '=' . "'" . ($students_v == 'BIRTHDATE' ? fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$students_v]])) : singleQuoteReplace("", "", $arr_v[$array_index[$students_v]])) . "'";
                    }
                        if ($students_v == 'ALT_ID')
                            $check_query_alt_id[] = $students_v . '=' . "'" . (singleQuoteReplace("", "", $arr_v[$array_index[$students_v]])) . "'";
                    }
                }
                foreach ($login_authentication as $username) {
                    if ($arr_v[$array_index[$username]] != '') {
                        if ($username == 'USERNAME')
                            $check_query_username[] = $username . '=' . "'" . (singleQuoteReplace("", "", $arr_v[$array_index[$username]])) . "'";
                    }
                }


                if (count($check_query) > 0) {
                    $check_exist = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM students WHERE ' . implode(" AND ", $check_query)));
                    $check_exist = $check_exist[1]['REC_EXISTS'];

                    if ($check_exist != 0) {
                        $err_msg[0] = 'duplicate student';
                    }
                }

                if (count($check_query_alt_id) > 0) {
                    $check_exist_al = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM students WHERE ' . implode(" ", $check_query_alt_id)));
                    $check_exist_alt = $check_exist_al[1]['REC_EXISTS'];

                    if ($check_exist_alt != 0) {
                        $err_msg[1] = 'duplicate alternet id';
                    }
                }

                if (count($check_query_username) > 0) {
                    $check_exist_al_username = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM login_authentication WHERE ' . implode(" ", $check_query_username)));
                    $check_exist_alt_username = $check_exist_al_username[1]['REC_EXISTS'];

                    if ($check_exist_alt_username != 0) {
                        $err_msg[2] = 'duplicate username';
                    }
                }

                if ($check_exist == 0 && $check_exist_alt == 0 && $check_exist_alt_username == 0) {
                    DBQuery('INSERT INTO students (' . implode(',', $student_columns) . ') VALUES (' . implode(',', $student_values) . ')');
                    $student_id = mysqli_insert_id($connection);
                    unset($student_columns);
                    unset($student_values);
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////For Student Enrollment////////////////////////////////////////////////////////////
                    $enrollment_code = DBGet(DBQuery('SELECT ID FROM  student_enrollment_codes WHERE SYEAR=' . UserSyear() . '  AND TITLE=\'New\''));
                    $enrollment_columns = array('SYEAR', 'SCHOOL_ID', 'STUDENT_ID', 'ENROLLMENT_CODE');
                    $enrollment_values = array(UserSyear(), UserSchool(), $student_id, $enrollment_code[1]['ID']);
                    $calendar_id = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars  WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND DEFAULT_CALENDAR=\'Y\' '));
                    if ($calendar_id[1]['CALENDAR_ID'] != '') {
                        $enrollment_columns += array('CALENDAR_ID');
                        $enrollment_values += array($calendar_id[1]['CALENDAR_ID']);
                    }
                    foreach ($student_enrollments as $student_enrollments_v) {
                        if ($arr_v[$array_index[$student_enrollments_v]] != '') {
                            $enrollment_columns[] = $student_enrollments_v;
                            if ($student_enrollments_v == 'GRADE_ID') {
                                $enr_value = DBGet(DBQuery('SELECT ID FROM school_gradelevels WHERE SHORT_NAME=\'' . singleQuoteReplace("", "", trim($arr_v[$array_index[$student_enrollments_v]])) . '\' and school_id=\'' . UserSchool() . '\''));
                                $enr_value = $enr_value[1]['ID'];
                            } elseif ($student_enrollments_v == 'SECTION_ID') {
                                $enr_value = DBGet(DBQuery('SELECT ID FROM school_gradelevel_sections WHERE NAME=\'' . singleQuoteReplace("", "", $arr_v[$array_index[$student_enrollments_v]]) . '\' and school_id=\'' . UserSchool() . '\''));
                                $enr_value = $enr_value[1]['ID'];
                            } elseif ($student_enrollments_v == 'START_DATE') {
                                $enr_value = fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$student_enrollments_v]]));
                            } elseif ($student_enrollments_v == 'END_DATE') {
                                $enr_value = fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$student_enrollments_v]]));
                            } else
                                $enr_value = singleQuoteReplace("", "", $arr_v[$array_index[$student_enrollments_v]]);
                            $enrollment_values[] = "'" . $enr_value . "'";
                        }
                    }
                    DBQuery('INSERT INTO student_enrollment (' . implode(',', $enrollment_columns) . ') VALUES (' . implode(',', $enrollment_values) . ')');
                    unset($enrollment_columns);
                    unset($enrollment_values);
                    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////For Student Login Authentication////////////////////////////////////////////////////////////
                    $la_columns = array('USER_ID', 'PROFILE_ID');
                    $la_values = array($student_id, 3);
                    if ($arr_v[$array_index['USERNAME']] != '') {
                        $la_columns[] = 'USERNAME';
                        $la_values[] = "'" . str_replace("'", "", $arr_v[$array_index['USERNAME']]) . "'";
                    } else {
                        $la_columns[] = 'USERNAME';
                        $la_values[] = "' '";
                    }

                    if ($arr_v[$array_index['PASSWORD']] != '') {
                        $la_columns[] = 'PASSWORD';
                        $la_values[] = "'" . GenerateNewHash(str_replace("'", "", $arr_v[$array_index['PASSWORD']])) . "'";
                    } else {
                        $la_columns[] = 'PASSWORD';
                        $la_values[] = "' '";
                    }

                    DBQuery('INSERT INTO login_authentication (' . implode(',', $la_columns) . ') VALUES (' . implode(',', $la_values) . ')');

                    unset($la_columns);
                    unset($la_values);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    /////////////////////////For Student Address////////////////////////////////////////////////////////////
                    $sa_columns = array('STUDENT_ID', 'SYEAR', 'SCHOOL_ID');
                    $sa_values = array($student_id, UserSyear(), UserSchool());

                    foreach ($student_address as $student_address_v) {

                        if ($arr_v[$array_index[$student_address_v]] != '') {
                            $sa_columns[] = $student_address_v;
                            $sa_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$student_address_v]]) . "'";
                        }
                    }
                    DBQuery('INSERT INTO student_address (' . implode(',', $sa_columns) . ',TYPE) VALUES (' . implode(',', $sa_values) . ',\'Home Address\')');
                    DBQuery('INSERT INTO student_address (' . implode(',', $sa_columns) . ',TYPE) VALUES (' . implode(',', $sa_values) . ',\'Mail\')');
                    DBQuery('INSERT INTO student_address (' . implode(',', $sa_columns) . ',TYPE) VALUES (' . implode(',', $sa_values) . ',\'Primary\')');
                    DBQuery('INSERT INTO student_address (' . implode(',', $sa_columns) . ',TYPE) VALUES (' . implode(',', $sa_values) . ',\'Secondary\')');
                    unset($sa_columns);
                    unset($sa_values);
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    /////////////////////////For Primary////////////////////////////////////////////////////////////
                    $primary_columns = array('CURRENT_SCHOOL_ID', 'PROFILE', 'PROFILE_ID');
                    $primary_values = array(UserSchool(), "'parent'", '4');
                    $relationship = '';
                    foreach ($primary as $primary_v) {

                        if ($primary_v != 'PRIMARY_RELATION') {
                            if ($arr_v[$array_index[$primary_v]] != '') {
                                $primary_columns[] = str_replace("PRIMARY_", "", $primary_v);
                                $primary_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$primary_v]]) . "'";
                            }
                        } else
                            $relationship = ($arr_v[$array_index[$primary_v]] != '' ? singleQuoteReplace("", "", $arr_v[$array_index[$primary_v]]) : 'Legal Guardian');
                    }
                    if (count($primary_columns) > 3) {
                        DBQuery('INSERT INTO people (' . implode(',', $primary_columns) . ') VALUES (' . implode(',', $primary_values) . ')');
                        $people_id = DBGet(DBQuery('SELECT MAX(STAFF_ID) as PEOPLE_ID FROM people'));
                        $people_id = $people_id[1]['PEOPLE_ID'];
                        DBQuery('UPDATE student_address SET PEOPLE_ID=' . $people_id . ' WHERE STUDENT_ID=' . $student_id . ' AND TYPE=\'Primary\' ');
                        DBQuery('INSERT INTO students_join_people (STUDENT_ID,PERSON_ID,EMERGENCY_TYPE,RELATIONSHIP) VALUES (' . $student_id . ',' . $people_id . ',\'Primary\',\'' . $relationship . '\')');
                    }
                    unset($primary_columns);
                    unset($primary_values);

                    //////////////////////////////////////////////////////////////////////////////////////////////
                    /////////////////////////For Secondary////////////////////////////////////////////////////////////
                    $secondary_columns = array('CURRENT_SCHOOL_ID', 'PROFILE', 'PROFILE_ID');
                    $secondary_values = array(UserSchool(), "'parent'", '4');
                    $relationship = '';
                    foreach ($secondary as $secondary_v) {

                        if ($secondary_v != 'SECONDARY_RELATION') {
                            if ($arr_v[$array_index[$secondary_v]] != '') {
                                $secondary_columns[] = str_replace("SECONDARY_", "", $secondary_v);
                                $secondary_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$secondary_v]]) . "'";
                            }
                        } else
                            $relationship = ($arr_v[$array_index[$secondary_v]] != '' ? singleQuoteReplace("", "", $arr_v[$array_index[$secondary_v]]) : 'Legal Guardian');
                    }
                    if (count($secondary_columns) > 3) {
                        DBQuery('INSERT INTO people (' . implode(',', $secondary_columns) . ') VALUES (' . implode(',', $secondary_values) . ')');
                        $people_id = DBGet(DBQuery('SELECT MAX(STAFF_ID) as PEOPLE_ID FROM people'));
                        $people_id = $people_id[1]['PEOPLE_ID'];
                        DBQuery('UPDATE student_address SET PEOPLE_ID=' . $people_id . ' WHERE STUDENT_ID=' . $student_id . ' AND TYPE=\'Secondary\' ');
                        DBQuery('INSERT INTO students_join_people (STUDENT_ID,PERSON_ID,EMERGENCY_TYPE,RELATIONSHIP) VALUES (' . $student_id . ',' . $people_id . ',\'Secondary\',\'' . $relationship . '\')');
                    }
                    unset($secondary_columns);
                    unset($secondary_values);

                    //////////////////////////////////////////////////////////////////////////////////////////////
                    $student_id++;
                    $accepted++;
                } else
                    $rejected++;
                $records++;
            }
        }


        if ($records > 0) {
            if ($records == $accepted) {
                echo '<div class="text-center"><img src="assets/images/check-clipart-animated.gif"></div>';
                echo '<h2 class="text-center text-success m-b-0">Congratulations !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">The data import has successfully concluded.</h5>';
            } elseif ($accepted > 0 && $rejected > 0) {
                echo '<div class="text-center"><img src="assets/images/info-icon-animated.gif" width="90"></div>';
                echo '<h2 class="text-center text-warning m-b-0">Partial Import !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">Some data couldn\'t be processed.</h5>';
            } elseif ($accepted == 0) {
                echo '<div class="text-center"><img src="assets/images/error-icon-animated.gif" width="100"></div>';
                echo '<h2 class="text-center text-danger m-b-0 m-t-0">Oops !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">The data import was rejected by the system.</h5>';
            }

            echo '<div class="row m-b-10">';
            echo '<div class="col-xs-10">Number of input records:</div><div class="col-xs-2">' . $records . '</div>';
            echo '</div>';
            echo '<div class="row m-b-10">';
            echo '<div class="col-xs-10">Number of records loaded into the database:</div><div class="col-xs-2">' . $accepted . '</div>';
            echo '</div>';
            echo '<div class="row">';
            echo '<div class="col-xs-10">Number or records rejected:</div><div class="col-xs-2">' . $rejected . '</div>';
            echo '</div>';




            if (count($err_msg) == 1) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg = $val;
                }
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible cause for rejection is ' . $msg . ' found.</div>';
                echo '</div>';
            }
            if (count($err_msg) == 2) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg .= $val . ' and ';
                }
                $msg = substr($val, 0, -5);
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible causes for rejection are ' . $msg . ' found.</div>';
                echo '</div>';
            }
            if (count($err_msg) == 3) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg .= $val . ' and ';
                }
                $msg = substr($val, 0, -5);
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible causes for rejection are ' . $msg . ' found.</div>';
                echo '</div>';
            }
        }


        unset($arr_data);
        unset($_SESSION['data']);
        unset($_SESSION['student']);
        unset($array_index);
        unset($temp_array_index);
    }
}



if ($category == 'staff') {

    if (count($_SESSION['data']) > 1) {
        $total_records = 0;
        $inserted_records = 0;
        $duplicate_records = 0;
        $arr_data = $_SESSION['data'];

        $temp_array_index = array();
        foreach ($arr_data[0] as $key => $value) {

            if ($value != '') {
                $temp_array_index[$value] = $key;
            }
        }


        foreach ($_SESSION['staff'] as $key => $value) {

            if (!is_array($value) && $value != '') {
                $array_index[$value] = $temp_array_index[$key];
            }
        }

        $staff = array('TITLE', 'FIRST_NAME', 'LAST_NAME', 'MIDDLE_NAME', 'EMAIL', 'PHONE', 'PROFILE', 'HOMEROOM', 'BIRTHDATE', 'ETHNICITY_ID', 'ALTERNATE_ID', 'PRIMARY_LANGUAGE_ID', 'GENDER', 'SECOND_LANGUAGE_ID', 'THIRD_LANGUAGE_ID', 'IS_DISABLE');
        $login_authentication = array('USERNAME', 'PASSWORD');
        $staff_school_relationship = array('START_DATE', 'END_DATE');
        $staff_school_info = array('CATEGORY', 'JOB_TITLE', 'JOINING_DATE');
        $custom = DBGet(DBQuery('SELECT * FROM staff_fields'));
        foreach ($custom as $c) {
            $staff[] = 'CUSTOM_' . $c['ID'];
        }

        // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'staff'"));
        // $staff_id[1]['STAFF_ID'] = $id[1]['AUTO_INCREMENT'];
        // $staff_id = $staff_id[1]['STAFF_ID'];
        $accepted = 0;
        $rejected = 0;
        $records = 0;
        $err_msg = array();
        foreach ($arr_data as $arr_i => $arr_v) {

            ///////////////////////////For Students////////////////////////////////////////////////////////////
            $staff_columns = array();
            $staff_values = array();
            if ($arr_i > 0) {


                $staff_columns = array('CURRENT_SCHOOL_ID');
                $staff_values = array(UserSchool());
                $check_query = array();
                $check_query_alt_id = array();
                $check_query_username = array();
                $check_exist = 0;
                foreach ($staff as $staff_v) {
                    
                    if ($staff_v == 'PROFILE') {
                        $arr_v[$array_index[$staff_v]] = strtolower($arr_v[$array_index[$staff_v]]);
                        $profile = DBGet(DBQuery('SELECT * FROM user_profiles WHERE title=\'' . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . '\' '));
                        if ($profile[1]['ID'] == '') {
                            $profile_id = '2';
                            $arr_v[$array_index[$staff_v]] = 'teacher';
                        } else
                            $profile_id = $profile[1]['ID'];
                        $staff_columns[] = 'PROFILE_ID';
                        $staff_values[] = $profile_id;
                    }
                    if ($staff_v == 'ETHNICITY_ID') {
                        $ethnicity = DBGet(DBQuery('SELECT * FROM ethnicity WHERE ethnicity_name=\'' . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . '\' '));
                        if ($ethnicity[1]['ETHNICITY_ID'] != '')
                            $arr_v[$array_index[$staff_v]] = $ethnicity[1]['ETHNICITY_ID'];
                        else
                            $arr_v[$array_index[$staff_v]] = '';
                    }
                    if ($staff_v == 'PRIMARY_LANGUAGE_ID') {
                        $language = DBGet(DBQuery('SELECT * FROM language WHERE language_name=\'' . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . '\' '));
                        if ($language[1]['LANGUAGE_ID'] != '')
                            $arr_v[$array_index[$staff_v]] = $language[1]['LANGUAGE_ID'];
                        else
                            $arr_v[$array_index[$staff_v]] = '';
                    }
                    if ($staff_v == 'SECOND_LANGUAGE_ID') {
                        $language = DBGet(DBQuery('SELECT * FROM language WHERE language_name=\'' . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . '\' '));
                        if ($language[1]['LANGUAGE_ID'] != '')
                            $arr_v[$array_index[$staff_v]] = $language[1]['LANGUAGE_ID'];
                        else
                            $arr_v[$array_index[$staff_v]] = '';
                    }
                    if ($staff_v == 'THIRD_LANGUAGE_ID') {
                        $language = DBGet(DBQuery('SELECT * FROM language WHERE language_name=\'' . singleQuoteReplace("", "", $arr_v[$array_index[strtolower($staff_v)]]) . '\' '));
                        if ($language[1]['LANGUAGE_ID'] != '')
                            $arr_v[$array_index[$staff_v]] = $language[1]['LANGUAGE_ID'];
                        else
                            $arr_v[$array_index[$staff_v]] = '';
                    }
                    if ($arr_v[$array_index[$staff_v]] != '') {

                        $staff_columns[] = $staff_v;
                        if ($staff_v == 'BIRTHDATE') {
                            $staff_values[] = "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]])) . "'";
                        } else {
                            $staff_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . "'";
                        }
                        if ($staff_v == 'FIRST_NAME' || $staff_v == 'LAST_NAME' || $staff_v == 'EMAIL')
                            $check_query[] = $staff_v . '=' . "'" . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . "'";

                        if ($staff_v == 'BIRTHDATE')
                            $check_query[] = $staff_v . '=' . "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]])) . "'";

                        if ($staff_v == 'ALTERNATE_ID')

                            $check_query_alt_id[] = $staff_v . '=' . "'" . singleQuoteReplace("", "", $arr_v[$array_index[$staff_v]]) . "'";
                    }
                }


                foreach ($login_authentication as $username) {

                    if ($arr_v[$array_index[$username]] != '') {

                        if ($username == 'USERNAME')
                            $check_query_username[] = $username . '=' . "'" . (singleQuoteReplace("", "", $arr_v[$array_index[$username]])) . "'";
                    }
                }

                if (count($check_query) > 0) {

                    $check_exist = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM staff WHERE ' . implode(" AND ", $check_query)));

                    $check_exist = $check_exist[1]['REC_EXISTS'];
                    if ($check_exist != 0) {
                        $err_msg[0] = 'duplicate staff';
                    }
                }

                if (count($check_query_alt_id) > 0) {
                    $check_exist_al = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM staff WHERE ' . implode(" ", $check_query_alt_id)));

                    $check_exist_alt = $check_exist_al[1]['REC_EXISTS'];
                    if ($check_exist_alt != 0) {
                        $err_msg[1] = 'duplicate alternet id';
                    }
                }

                if (count($check_query_username) > 0) {

                    $check_exist_al_username = DBGet(DBQuery('SELECT COUNT(*) as REC_EXISTS FROM login_authentication WHERE ' . implode(" ", $check_query_username)));
                    $check_exist_alt_username = $check_exist_al_username[1]['REC_EXISTS'];

                    if ($check_exist_alt_username != 0) {
                        $err_msg[2] = 'duplicate username';
                    }
                }

                if ($check_exist == 0 && $check_exist_alt == 0 && $check_exist_alt_username == 0) {
                    DBQuery('INSERT INTO staff (' . implode(',', $staff_columns) . ') VALUES (' . implode(',', $staff_values) . ')');
                    $staff_id = mysqli_insert_id($connection);
                    unset($staff_columns);
                    unset($staff_values);
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////For Staff Enrollment////////////////////////////////////////////////////////////
                    $ssr_columns = array('STAFF_ID', 'SYEAR', 'SCHOOL_ID');
                    $ssr_values = array($staff_id, UserSyear(), UserSchool());
                    $start_date_i = 0;
                    foreach ($staff_school_relationship as $ssr_v) {

                        if ($arr_v[$array_index[$ssr_v]] != '') {
                            $ssr_columns[] = $ssr_v;
                            if ($ssr_v == 'START_DATE') {
                                $start_date_i = 1;
                                if ($arr_v[$array_index[$ssr_v]] == '') {
                                    $start_date = DBGet(DBQuery('SELECT START_DATE FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));

                                    $ssr_values[] = "'" . $start_date[1]['START_DATE'] . "'";
                                } else
                                    $ssr_values[] = "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$ssr_v]])) . "'";
                            } elseif ($ssr_v == 'END_DATE') {
                                $ssr_values[] = "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index[$ssr_v]])) . "'";
                            } else
                                $ssr_values[] = "'" . singleQuoteReplace("", "", $arr_v[$array_index[$ssr_v]]) . "'";
                        }
                    }
                    if ($start_date_i == 0) {
                        $start_date = DBGet(DBQuery('SELECT START_DATE FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
                        $ssr_columns[] = 'START_DATE';
                        $ssr_values[] = "'" . $start_date[1]['START_DATE'] . "'";
                    }

                    DBQuery('INSERT INTO staff_school_relationship (' . implode(',', $ssr_columns) . ') VALUES (' . implode(',', $ssr_values) . ')');
                    unset($ssr_columns);
                    unset($ssr_values);
                    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////For Staff Login Authentication////////////////////////////////////////////////////////////
                    $la_columns = array('USER_ID', 'PROFILE_ID');
                    $la_values = array($staff_id, $profile_id);
                    if ($arr_v[$array_index['USERNAME']] != '') {
                        $la_columns[] = 'USERNAME';
                        $la_values[] = "'" . str_replace("'", "", $arr_v[$array_index['USERNAME']]) . "'";
                    } else {
                        $la_columns[] = 'USERNAME';
                        $la_values[] = "'" . trim(strtolower(str_replace("'", "", str_replace(" ", "", $arr_v[$array_index['FIRST_NAME']]))) . $staff_id) . "'";
                    }

                    if ($arr_v[$array_index['PASSWORD']] != '') {
                        $la_columns[] = 'PASSWORD';
                        $la_values[] = "'" . GenerateNewHash(str_replace("'", "", $arr_v[$array_index['PASSWORD']])) . "'";
                    } else {
                        $la_columns[] = 'PASSWORD';
                        $la_values[] = "'" . GenerateNewHash(trim(strtolower(str_replace("'", "", str_replace(" ", "", $arr_v[$array_index['FIRST_NAME']]))) . $staff_id)) . "'";
                    }
                    DBQuery('INSERT INTO login_authentication (' . implode(',', $la_columns) . ') VALUES (' . implode(',', $la_values) . ')');
                    unset($la_columns);
                    unset($la_values);
                    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////For Staff School Info////////////////////////////////////////////////////////////
                    $ssi_columns = array('STAFF_ID', 'HOME_SCHOOL', 'OPENSIS_ACCESS', 'OPENSIS_PROFILE', 'SCHOOL_ACCESS');
                    $ssi_values = array($staff_id, UserSchool(), '"Y"', $profile_id, '",' . UserSchool() . ',"');
                    if ($arr_v[$array_index['CATEGORY']] != '') {
                        $ssi_columns[] = 'CATEGORY';
                        $ssi_values[] = "'" . str_replace("'", "", $arr_v[$array_index['CATEGORY']]) . "'";
                    } else {
                        $ssi_columns[] = 'CATEGORY';
                        $ssi_values[] = "'Teacher'";
                    }

                    if ($arr_v[$array_index['JOB_TITLE']] != '') {
                        $ssi_columns[] = 'JOB_TITLE';
                        $ssi_values[] = "'" . str_replace("'", "", $arr_v[$array_index['JOB_TITLE']]) . "'";
                    } else {
                        $ssi_columns[] = 'JOB_TITLE';
                        $ssi_values[] = "'Teacher'";
                    }

                    if ($arr_v[$array_index['JOINING_DATE']] != '') {
                        $ssi_columns[] = 'JOINING_DATE';
                        $ssi_values[] = "'" . fromExcelToLinux(singleQuoteReplace("", "", $arr_v[$array_index['JOINING_DATE']])) . "'";
                    }

                    DBQuery('INSERT INTO staff_school_info (' . implode(',', $ssi_columns) . ') VALUES (' . implode(',', $ssi_values) . ')');
                    unset($ssi_columns);
                    unset($ssi_values);
                    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    $staff_id++;
                    $accepted++;
                } else
                    $rejected++;
                $records++;
            }
        }


        if ($records > 0) {
            if ($records == $accepted) {
                echo '<div class="text-center"><img src="assets/images/check-clipart-animated.gif"></div>';
                echo '<h2 class="text-center text-success m-b-0">Congratulations !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">The data import has successfully concluded.</h5>';
            } elseif ($accepted > 0 && $rejected > 0) {
                echo '<div class="text-center"><img src="assets/images/info-icon-animated.gif" width="90"></div>';
                echo '<h2 class="text-center text-warning m-b-0">Partial Import !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">Some data could not be processed.</h5>';
            } elseif ($accepted == 0) {
                echo '<div class="text-center"><img src="assets/images/error-icon-animated.gif" width="100"></div>';
                echo '<h2 class="text-center text-danger m-b-0 m-t-0">Oops !!!</h2>';
                echo '<h5 class="text-center m-t-0 m-b-35 text-grey">The data import was rejected by the system.</h5>';
            }

            echo '<div class="row m-b-10">';
            echo '<div class="col-xs-10">Number of input records:</div><div class="col-xs-2">' . $records . '</div>';
            echo '</div>';
            echo '<div class="row m-b-10">';
            echo '<div class="col-xs-10">Number of records loaded into the database:</div><div class="col-xs-2">' . $accepted . '</div>';
            echo '</div>';
            echo '<div class="row m-b-10">';
            echo '<div class="col-xs-10">Number or records rejected:</div><div class="col-xs-2">' . (($rejected > 0) ? '<span class="text-danger">' . $rejected . '</span>' : $rejected) . '</div>';
            echo '</div>';




            if (count($err_msg) == 1) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg = $val;
                }
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible cause for rejection is ' . $msg . ' found.</div>';
                echo '</div>';
            }
            if (count($err_msg) == 2) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg .= $val . ' and ';
                }
                $msg = substr($val, 0, -5);
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible causes for rejection are ' . $msg . ' found.</div>';
                echo '</div>';
            }
            if (count($err_msg) == 3) {
                $msg = '';
                foreach ($err_msg as $key => $val) {
                    $msg .= $val . ' and ';
                }
                $msg = substr($val, 0, -5);
                echo '<div class="row m-t-10">';
                echo '<div class="col-xs-12 text-danger"><i class="icon-info22"></i> Possible causes for rejection are ' . $msg . ' found.</div>';
                echo '</div>';
            }
        }
        unset($err_msg);
        unset($arr_data);
        unset($_SESSION['data']);
        unset($_SESSION['staff']);
        unset($array_index);
        unset($temp_array_index);
    }
}

echo '</div>'; //.panel-body
echo '<div class="panel-footer text-center"><a href="Modules.php?modname=tools/DataImport.php" class="btn btn-default"><i class="icon-arrow-left8"></i> Back to Data Import Tool</a></div>';

function fromExcelToLinux($excel_time)
{
    if (is_numeric($excel_time)) {
    $ex_date = ($excel_time - 25569) * 86400;
    return gmdate("Y-m-d", $ex_date);
} else{
        $newDateString = date_format(date_create_from_format('M/j/Y', $excel_time), 'Y-m-d');
        return $newDateString;
        // return date("Y-m-d", strtotime($excel_time));
    }
}
