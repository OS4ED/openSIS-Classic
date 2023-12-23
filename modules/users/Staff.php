<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that
#  include staff demographic info, scheduling, grade book, attendance,
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

!empty($_SESSION['USERNAME']) or die('Access denied!');
include('../../RedirectModulesInc.php');
include_once("../../functions/PasswordHashFnc.php");

if ($_SESSION['staff_id'] == '' && $_REQUEST['staff_id'] != 'new')
    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
if (isset($_REQUEST['custom_date_id']) && count($_REQUEST['custom_date_id']) > 0) {
    foreach ($_REQUEST['custom_date_id'] as $custom_id) {
        $_REQUEST['staff']['CUSTOM_' . $custom_id] = $_REQUEST['year_CUSTOM_' . $custom_id] . '-' . MonthFormatter($_REQUEST['month_CUSTOM_' . $custom_id]) . '-' . $_REQUEST['day_CUSTOM_' . $custom_id];
        $_POST['staff']['CUSTOM_' . $custom_id] = $_REQUEST['year_CUSTOM_' . $custom_id] . '-' . MonthFormatter($_REQUEST['month_CUSTOM_' . $custom_id]) . '-' . $_REQUEST['day_CUSTOM_' . $custom_id];
    }
    unset($_REQUEST['custom_date_id']);
}
if ($_REQUEST['staff_id'] != 'new') {
    if ($_SESSION['fn'] == '' && $_REQUEST['staff_id'] == '') {
        $_SESSION['fn'] = 'user';
    }
} else {
    $_SESSION['fn'] = '';
}
###########################################
if ($_REQUEST['month_values']['JOINING_DATE'] != '' && $_REQUEST['day_values']['JOINING_DATE'] != '' && $_REQUEST['year_values']['JOINING_DATE'] != '' && $_REQUEST['month_values']['ENDING_DATE'] != '' && $_REQUEST['day_values']['ENDING_DATE'] != '' && $_REQUEST['year_values']['ENDING_DATE'] != '') {
    $months_arr = array("JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC" => "12");
    $s_date = strtotime($_REQUEST['year_values']['JOINING_DATE'] . '-' . $_REQUEST['month_values']['JOINING_DATE'] . '-' . $_REQUEST['day_values']['JOINING_DATE']);
    $e_date = strtotime($_REQUEST['year_values']['ENDING_DATE'] . '-' . $_REQUEST['month_values']['ENDING_DATE'] . '-' . $_REQUEST['day_values']['ENDING_DATE']);

    if ($e_date < $s_date) {
        unset($_REQUEST['year_values']['ENDING_DATE']);
        unset($_REQUEST['month_values']['ENDING_DATE']);
        unset($_REQUEST['day_values']['ENDING_DATE']);
        echo "<div class='alert bg-danger alert-styled-left'>End date of joining cannot be before start date.</div>";
    }
}

$show_title = '';
if (UserStaffID() != '') {


    if ($_REQUEST['v'] && isset($_REQUEST['staff_id'])) {



        $val = optional_param('v', 0, PARAM_INT);

        if ($val == 1) {
            unset($_SESSION['staff_id']);
            $_SESSION['staff_id'] = $_SESSION['staff_order'][1];
        }
        if ($val == 2) {
            $final_pos = array_search($_SESSION['staff_id'], $_SESSION['staff_order']);
            $final_pos = $final_pos - 1;
            unset($_SESSION['staff_id']);
            $_SESSION['staff_id'] = $_SESSION['staff_order'][$final_pos];
        }
        if ($val == 3) {
            $final_pos = array_search($_SESSION['staff_id'], $_SESSION['staff_order']);
            $final_pos = $final_pos + 1;
            unset($_SESSION['staff_id']);
            $_SESSION['staff_id'] = $_SESSION['staff_order'][$final_pos];
        }
        if ($val == 4) {
            unset($_SESSION['staff_id']);
            $final_pos = count($_SESSION['staff_order']);
            $_SESSION['staff_id'] = $_SESSION['staff_order'][$final_pos];
        }
    }
    $val = $_REQUEST['v'];

    if (is_array($_SESSION['staff_order'])) {
        $count = array_search($_SESSION['staff_id'], $_SESSION['staff_order']);
    }

    $_SESSION['count_staff'] = $count;
    $_SESSION['total_staff'] = (is_countable($_SESSION['staff_order'])) ? count($_SESSION['staff_order']) : 0;
    $last_stu = (is_countable($_SESSION['staff_order'])) ? count($_SESSION['staff_order']) : 0;
    $last_stu = $_SESSION['staff_order'][$last_stu];
    echo '<div class="row">';
    echo '<div class="col-md-12 text-right">';
    echo "<p>" . _showing . " " . ((is_countable($_SESSION['staff_order']) ? count($_SESSION['staff_order']) : 0) > 1 ? $_SESSION['count_staff'] : '1') . " " . _of . " " . ((is_countable($_SESSION['staff_order']) ? count($_SESSION['staff_order']) : 0) > 1 ? $_SESSION['total_staff'] : '1') . " &nbsp; ";

    if (is_countable($_SESSION['staff_order']) && count($_SESSION['staff_order']) > 1) {
        if (UserStaffID() != $_SESSION['staff_order'][1]) {
            echo "<span class='pg-prev' style='margin-right:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=users/Staff.php&v=1&staff_id=" . UserStaffID() . " ><i class=\"icon-first\"></i> " . _first . "</A></span>";

            echo "<span class='pg-prev' style='margin-right:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=users/Staff.php&v=2&staff_id=" . UserStaffID() . " > <i class=\"icon-backward2\"></i> " . _previous . "</A></span>";
        }
        if (UserStaffID() != $last_stu) {

            echo "<span class='pg-nxt' style='margin-left:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=users/Staff.php&v=3&staff_id=" . UserStaffID() . " >" . _next . " <i class=\"icon-forward3\"></i></A></span>";

            echo "<span class='pg-nxt' style='margin-left:10px; font-size:14px; font-weight:normal;'><A HREF=Modules.php?modname=users/Staff.php&v=4&staff_id=" . UserStaffID() . " >" . _last . " <i class=\"icon-last\"></i></A></span>";
        }
    }

    echo '</div>';
    echo '</div>';
}


if (isset($_REQUEST['staff_id']) && $_REQUEST['staff_id'] != 'new') {
    $show_title = 'y';
    $_REQUEST['staff_id'] = $_SESSION['staff_id'];
    $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID='" . $_REQUEST['staff_id'] . "'"));
    $count_staff_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM staff"));
    if ($count_staff_RET[1]['NUM'] > 1) {
        echo '<div class="panel panel-default">';
        DrawHeader('' . _selectedStaff . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToUserList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
        echo '</div>';
    } else {
        echo '<div class="panel panel-default">';
        DrawHeader('' . _selectedStaff . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
        echo '</div>';
    }
}
if (UserStaffID() != '' && $show_title != 'y' && $title_set_staff != 'y') {
    if (User('PROFILE') == 'admin') {
        $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID='" . UserStaffID() . "'"));
        $count_staff_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM staff"));
        if ($count_staff_RET[1]['NUM'] > 1) {
            echo '<div class="panel panel-default">';
            DrawHeader('' . _selectedStaff . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToUserList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
            echo '</div>';
        } else {
            echo '<div class="panel panel-default">';
            DrawHeader('' . _selectedStaff . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
            echo '</div>';
        }
    }
}
#############################################
if (User('PROFILE') != 'admin' && User('PROFILE') != 'teacher' && $_REQUEST['staff_id'] && $_REQUEST['staff_id'] != 'new') {
    if (User('USERNAME')) {
        HackingLog();
    }
    exit;
}
if ($_REQUEST['s_err'] == 'y') {
    echo '<font style="color:red"><b>Start date can not be before school\'s start date</b></font>';
    unset($_REQUEST['s_err']);
}

if (!$_REQUEST['include']) {
    $_REQUEST['include'] = 'DemographicInfoInc';
    $_REQUEST['category_id'] = '1';
} elseif (!$_REQUEST['category_id'])
    if ($_REQUEST['include'] == 'DemographicInfoInc')
        $_REQUEST['category_id'] = '1';
    elseif ($_REQUEST['include'] == 'AddressInc')
        $_REQUEST['category_id'] = '2';
    elseif ($_REQUEST['include'] == 'SchoolsInfoInc')
        $_REQUEST['category_id'] = '3';
    elseif ($_REQUEST['include'] == 'CertificationInfoInc')
        $_REQUEST['category_id'] = '4';
    elseif ($_REQUEST['include'] == 'FilesInc')
        $_REQUEST['category_id'] = '6';

    elseif ($_REQUEST['include'] != 'OtherInfoInc') {
        $include = DBGet(DBQuery("SELECT ID FROM staff_field_categories WHERE INCLUDE='$_REQUEST[include]'"));
        $_REQUEST['category_id'] = $include[1]['ID'];
    }


if (User('PROFILE') != 'admin') {
    if (User('PROFILE_ID'))
        $can_edit_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND MODNAME='users/User.php&category_id=$_REQUEST[category_id]' AND CAN_EDIT='Y'"));
    else {
        $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID') . "'"));
        $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
        if ($profile_id_mod != '')
            $can_edit_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND MODNAME='users/User.php&category_id=$_REQUEST[category_id]' AND CAN_EDIT='Y'"), array(), array('MODNAME'));
    }
    if ($can_edit_RET)
        $_openSIS['allow_edit'] = true;
}

unset($schools);

if ($_REQUEST['category_id'] == 2 && !isset($_REQUEST['address_id'])) {
    $address_id = DBGet(DBQuery("SELECT STAFF_ADDRESS_ID AS ADDRESS_ID FROM staff_address WHERE STAFF_ID='" . UserStaffID() . "'"));
    $address_id = $address_id[1]['ADDRESS_ID'];
    if (!empty($address_id))
        $_REQUEST['address_id'] = $address_id;
    else
        $_REQUEST['address_id'] = 'new';
}


if ($_REQUEST['category_id'] == 3 && !isset($_REQUEST['school_info_id'])) {
    $school_info_id_RET = DBGet(DBQuery("SELECT STAFF_SCHOOL_INFO_ID AS SCHOOL_INFO_ID
        FROM staff_school_info
         WHERE STAFF_ID='" . UserStaffID() . "'"));
    $school_info_id = $school_info_id_RET[1]['SCHOOL_INFO_ID'];
    if ($school_info_id && $school_info_id > 0)
        $_REQUEST['school_info_id'] = $school_info_id;
    else
        $_REQUEST['school_info_id'] = 'new';
}

if ($_REQUEST['category_id'] == 4 && !isset($_REQUEST['certification_id'])) {
    $certification_id_RET = DBGet(DBQuery("SELECT STAFF_CERTIFICATION_ID AS CERTIFICATION_ID
        FROM staff_certification
         WHERE STAFF_ID='" . UserStaffID() . "'"));
    $certification_id = $certification_id_RET[1]['CERTIFICATION_ID'];
    if ($certification_id && $certification_id > 0)
        $_REQUEST['certification_id'] = $certification_id;
    else
        $_REQUEST['certification_id'] = 'new';
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update') {

    if (is_countable($_REQUEST['values']['SCHOOLS']) && count($_REQUEST['values']['SCHOOLS']) > 0) {
        $school_array = $_REQUEST['values']['SCHOOLS'];
        $cur_school = array_keys($school_array);
        if ($_REQUEST['staff_id'] == 'new')
            $_REQUEST['staff']['CURRENT_SCHOOL_ID'] = $cur_school[0];
        else {
            if ($cur_school[0])
                $_REQUEST['staff_school']['CURRENT_SCHOOL_ID'] = $cur_school[0];
        }
    }

    $password = md5($_REQUEST['staff']['PASSWORD']);
    $ins_profile = $_REQUEST['staff']['PROFILE'];
    $res_pass_chk = DBQuery('SELECT * FROM login_authentication WHERE PASSWORD = \'' . $password . '\'');
    $num_pass = $res_pass_chk->num_rows;

    $day_valid = true;
    if (is_countable($_POST['staff']) && count($_POST['staff']) && (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher' || basename($_SERVER['PHP_SELF']) == 'index.php') || $_REQUEST['ajax']) {
        if ($_REQUEST['staff_id'] && $_REQUEST['staff_id'] != 'new') {
            $profile_RET = DBGet(DBQuery("SELECT s.PROFILE,la.PROFILE_ID,la.USERNAME FROM login_authentication la,staff s WHERE la.USER_ID='$_REQUEST[staff_id]' and la.USER_ID=s.STAFF_ID AND la.PROFILE_ID NOT IN (0,3) "));
            $this_school_RET_mod = DBGet(DBQuery("SELECT s.*,l.* FROM staff s,login_authentication l  WHERE l.USER_ID=s.STAFF_ID AND l.PROFILE_ID NOT IN (3,4) AND s.STAFF_ID=" . $_REQUEST['staff_id']));

            $this_school_mod = $this_school_RET_mod[1];

            $username = $this_school_mod['USERNAME'];
            $password = $this_school_mod['PASSWORD'];
            $this_school_RET = DBGet(DBQuery("SELECT * FROM staff_school_info   WHERE   STAFF_ID=" . $_REQUEST['staff_id']));
            $this_school = $this_school_RET[1];
            if (isset($_REQUEST['staff']['PROFILE']) && $_REQUEST['staff']['PROFILE'] != $profile_RET[1]['PROFILE_ID']) {
                if ($_REQUEST['staff']['PROFILE'] == 'admin')
                    $_REQUEST['staff']['PROFILE_ID'] = '1';
                elseif ($_REQUEST['staff']['PROFILE'] == 'teacher')
                    $_REQUEST['staff']['PROFILE_ID'] = '2';
                elseif ($_REQUEST['staff']['PROFILE'] == 'parent')
                    $_REQUEST['staff']['PROFILE_ID'] = '4';
            }

            if ($_REQUEST['staff']['USERNAME'] && $_REQUEST['staff']['USERNAME'] != $profile_RET[1]['USERNAME']) {
                $existing_staff = DBGet(DBQuery('SELECT ssr.SYEAR FROM staff s,staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND s.USERNAME=\'' . $_REQUEST['staff']['USERNAME'] . '\' AND ssr.SYEAR=(SELECT SYEAR FROM staff_school_relationship WHERE STAFF_ID=\'' . $_REQUEST['staff_id'] . '\')'));
                if (count($existing_staff))
                    BackPrompt('A user with that username already exists for the ' . $existing_staff[1]['SYEAR'] . ' school year. Choose a different username and try again.');
            }

            if (count($_REQUEST['month_staff'])) {
                foreach ($_REQUEST['month_staff'] as $column => $value) {
                    $_REQUEST['staff'][$column] = $_REQUEST['day_staff'][$column] . '-' . $_REQUEST['month_staff'][$column] . '-' . $_REQUEST['year_staff'][$column];
                    if ($_REQUEST['staff'][$column] == '--')
                        $_REQUEST['staff'][$column] = '';
                    elseif (!VerifyDate($_REQUEST['staff'][$column])) {
                        unset($_REQUEST['staff'][$column]);
                        $note = "The invalid date could not be saved. ";
                    }
                }
            }
            unset($_REQUEST['day_staff']);
            unset($_REQUEST['month_staff']);
            unset($_REQUEST['year_staff']);

            if ($_REQUEST['staff']) {

                $sql = "UPDATE staff SET ";

                if ($_REQUEST['staff']['PHYSICAL_DISABILITY'] == 'N' && !isset($_REQUEST['staff']['DISABILITY_DESC']))
                    DBQuery('UPDATE staff SET DISABILITY_DESC=Null WHERE STAFF_ID=' . $_REQUEST['staff_id']);

                if ($username == '' || $password == '' || $this_school['JOINING_DATE'] == '') {
                    echo "<script>window.location.href='Modules.php?modname=users/Staff.php&include=SchoolsInfoInc&category_id=3'</script>";
                }
                foreach ($_REQUEST['staff'] as $column_name => $value) {
                    if ($column_name == 'BIRTHDATE' && $value != '')
                        $value = date("Y-m-d", strtotime($value));
                    if ($column_name == 'SCHOOLS')
                        continue;
                    if (strpos($column_name, "CUSTOM") == 0) {
                        $go = true;
                        $custom = DBGet(DBQuery("SHOW COLUMNS FROM staff WHERE FIELD='" . $column_name . "'"));
                        $custom = $custom[1];

                        if (substr($column_name, 0, 6) == 'CUSTOM') {
                            $custom_id = str_replace("CUSTOM_", "", $column_name);
                            $custom_RET = DBGet(DBQuery("SELECT TITLE,TYPE,REQUIRED FROM staff_fields WHERE ID=" . $custom_id));

                            if ($custom_RET[1]['TYPE'] == 'multiple') {
                                $valueSize = count($value);
                                if ($valueSize == 0) {
                                    $valueSize = '';
                                }
                            } else {
                                $valueSize = trim($value);
                            }

                            if ($custom_RET[1]['TYPE'] == 'date') {
                                if ($value != '') {
                                    $dateValue = explode('-', $value);
                                    $value = $dateValue[2] . '-' . $dateValue[1] . '-' . $dateValue[0];
                                }
                            }

                            if ($custom['NULL'] == 'NO' && trim($valueSize) == '' && $custom['DEFAULT']) {
                                $value = $custom['DEFAULT'];
                            } elseif ($custom['NULL'] == 'NO' && (is_array($value) ? count($value) == 0 : trim($value) == '')) {
                                $custom_TITLE = DBGet(DBQuery("SELECT TITLE FROM staff_fields WHERE ID=" . $custom_id));
                                $custom_TITLE = $custom_TITLE[1]['TITLE'];
                                if ($custom_TITLE != '')
                                    echo "<div class='alert alert-danger'>Unable to save data, because " . $custom_TITLE . ' is required.</div>';
                                else
                                    echo "<div class='alert alert-danger'>Unable to save data, because " . $custom_TITLE . ' is required.</div>';
                                //$error= true;
                            } else {
                                $custom_id = str_replace("CUSTOM_", "", $column_name);
                                $m_custom_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE from staff_fields WHERE ID='" . $custom_id . "' AND TYPE='multiple'"));
                                if ($m_custom_RET) {
                                    $str = "";

                                    foreach ($value as $m_custom_val) {
                                        if ($m_custom_val)
                                            $str .= "||" . $m_custom_val;
                                    }
                                    if ($str)
                                        $value = $str . "||";
                                    else
                                        $value = '';
                                }
                            }
                        }
                    }

                    if ($column_name == 'PASSWORD') {
                        if ($value != "") {
                            $password = str_replace("\'", "''", str_replace("`", "''", ($value)));
                            $new_password = GenerateNewHash($password);
                            $sql .= "$column_name='" . $new_password . "',";
                            $execute = 'yes';
                        }
                    }

                    if ($column_name == 'FIRST_NAME' || $column_name == 'LAST_NAME') {
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $sql .= "$column_name='" . $value . " ',";
                        } else
                            $sql .= "$column_name='" . $value . " ',";


                        $flg_cp = 1;
                    } else {
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $sql .= "$column_name='" . str_replace("'", "\'", str_replace("`", "''", $value)) . "',";
                        } else
                            $sql .= "$column_name='" . str_replace("'", "''", str_replace("'`", "''", $value)) . "',";
                    }
                    $execute = 'yes';
                }

                $sql = substr($sql, 0, -1) . " WHERE STAFF_ID='$_REQUEST[staff_id]'";
                if ((User('PROFILE') == 'admin' || User('PROFILE') == 'teacher') && $execute == 'yes' && $error != true) {
                    DBQuery($sql);

                    if (isset($flg_cp) && $flg_cp == 1) {
                        $chk_assoc_cp = DBGet(DBQuery('SELECT course_period_id,title,short_name from course_periods WHERE school_id=' . UserSchool() . ' AND syear=' . UserSyear() . ' and teacher_id=' . $_REQUEST['staff_id']));

                        $new_tec_name = DBGet(DBQuery("SELECT concat(first_name,' ',last_name)  as name FROM  staff  WHERE STAFF_ID=" . $_REQUEST['staff_id']));
                        $new_tec_name = $new_tec_name[1]['NAME'];
                        foreach ($chk_assoc_cp as $chk_assoc_cp_k => $chk_assoc_cp_v) {

                            $new_title = $chk_assoc_cp_v['SHORT_NAME'] . '-' . $new_tec_name;
                            $update_cp_title = DBQuery('UPDATE  course_periods SET TITLE=\'' . singleQuoteReplace('', '', $new_title) . '\' WHERE  course_period_id=' . $chk_assoc_cp_v['COURSE_PERIOD_ID']);

                            unset($new_title);
                        }
                    }


                    if ($_FILES['file']['name']) {
                        $stf_img_info = DBGet(DBQuery('SELECT * FROM staff WHERE STAFF_ID=' . $_REQUEST['staff_id'] . ' AND IMG_NAME IS NOT NULL'));
                        $fileName = $_FILES['file']['name'];
                        $tmpName = $_FILES['file']['tmp_name'];
                        $fileSize = $_FILES['file']['size'];
                        $fileType = $_FILES['file']['type'];
                        $upload = new upload();
                        if (count($stf_img_info) > 0)
                            $upload->deleteOldImage($stf_img_info[1]['STAFF_ID']);
                        $upload->name = $_FILES["file"]["name"];
                        $upload->setFileExtension();
                        $upload->fileExtension;
                        $upload->validateImage();
                        if ($upload->wrongFormat == 1) {
                            $_FILES["file"]["error"] = 1;
                        }
                        if ($fileSize > 10000000) {
                            echo "<font style='color:red'><b>" . _FileExceedsTheAllowableSizeTryAgainWithAFileLessThen10Mb . "</b></font><br>";
                        }
                        if ($_FILES["file"]["error"] > 0) {
                            echo "cannot upload file";
                        } else {
                            if ($fp) {
                                $content = fread($fp, filesize($tmpName));
                            }
                            $content = base64_decode($_REQUEST['imgblob']);
                            $content = addslashes($content);
                            DBQuery('UPDATE staff SET IMG_NAME=\'' . $fileName . '\',IMG_CONTENT=\'' . $content . '\' WHERE STAFF_ID=' . $_REQUEST['staff_id']);
                            //PopTable ('footer');
                        }
                    }
                    echo $err;
                }
            }
        } else {
            unset($error);
            if ($_REQUEST['staff']['PROFILE'] == 'admin')
                $_REQUEST['staff']['PROFILE_ID'] = '1';
            elseif ($_REQUEST['staff']['PROFILE'] == 'teacher')
                $_REQUEST['staff']['PROFILE_ID'] = '2';
            elseif ($_REQUEST['staff']['PROFILE'] == 'parent')
                $_REQUEST['staff']['PROFILE_ID'] = '4';

            $existing_staff = DBGet(DBQuery("SELECT 'exists' FROM login_authentication WHERE USERNAME='" . $_REQUEST['staff']['USERNAME'] . "'"));


            $sql = "INSERT INTO staff ";


            $fields = 'CURRENT_SCHOOL_ID,';
            $values = UserSchool() . ',';

            if (count($_REQUEST['month_staff'])) {
                foreach ($_REQUEST['month_staff'] as $column => $value) {
                    $_REQUEST['staff'][$column] = $_REQUEST['day_staff'][$column] . '-' . $_REQUEST['month_staff'][$column] . '-' . $_REQUEST['year_staff'][$column];
                    if ($_REQUEST['staff'][$column] == '--')
                        $_REQUEST['staff'][$column] = '';
                    elseif (!VerifyDate($_REQUEST['staff'][$column])) {
                        unset($_REQUEST['staff'][$column]);
                        $note = "The invalid date could not be saved.";
                    }
                }
            }
            unset($_REQUEST['day_staff']);
            unset($_REQUEST['month_staff']);
            unset($_REQUEST['year_staff']);

            $staff_arr = array();
            $staff_arr['FIRST_NAME'] = $_REQUEST['staff']['FIRST_NAME'];
            $staff_arr['LAST_NAME'] = $_REQUEST['staff']['LAST_NAME'];
            $result = array_merge(sqlSecurityFilter($_REQUEST['staff']),$staff_arr);

            foreach ($result as $column => $value) {
                if ($column == 'BIRTHDATE' && $value != '') {
                    $value = date("Y-m-d", strtotime($value));
                }
                if ($column == 'SCHOOLS')
                    continue;
                if (strpos($column, "CUSTOM") == 0) {
                    $custom = DBGet(DBQuery("SHOW COLUMNS FROM staff WHERE FIELD='" . $column . "'"));
                    $custom = $custom[1];
                    if ($custom['NULL'] == 'NO' && trim($value) == '' && !$custom['DEFAULT']) {
                        $custom_id = str_replace("CUSTOM_", "", $column);
                        $custom_TITLE = DBGet(DBQuery("SELECT TITLE FROM staff_fields WHERE ID='" . $custom_id . "' "));
                        $custom_TITLE = $custom_TITLE[1]['TITLE'];

                        $error = true;
                    } else {
                        $custom_id = str_replace("CUSTOM_", "", $column);
                        $m_custom_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE from staff_fields WHERE ID='" . $custom_id . "' AND TYPE='multiple'"));
                        if ($m_custom_RET) {
                            $str = "";
                            foreach ($value as $m_custom_val) {
                                if ($m_custom_val)
                                    $str .= "||" . $m_custom_val;
                            }
                            if ($str)
                                $value = $str . "||";
                            else
                                $value = '';
                        }
                    }
                }
                if ($value) {
                    if ($column == 'FN')
                        $column = 'FIRST_NAME';
                    elseif ($column == 'LN')
                        $column = 'LAST_NAME';
                    elseif ($column == 'MN')
                        $column = 'MIDDLE_NAME';
                    $fields .= $column . ',';
                    if ($column == 'FIRST_NAME' || $column == 'LAST_NAME') {
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $values .= "'" . trim($value) . "',";
                        } else
                            $values .= "'" . trim($value) . "',";
                    } else {
                        if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                            $values .= "'" . str_replace("'", "\'", $value) . "',";
                        } else
                            $values .= "'" . str_replace("'", "''", $value) . "',";
                        #	$values .= "'".str_replace("\'","''",$value)."',";
                    }
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

            if ($error != true) {
                //echo $sql;exit;
                DBQuery($sql);
                // possible modification start
                $staff_id = DBGet(DBQuery("select max(staff_id) as id from staff"));
                $staff_id = $staff_id[1]['ID'];
                // possible modification end

                if ($_FILES['file']['name']) {
                    //       $UserPicturesPath='assets/userphotos/';
                    //        $target_path=$UserPicturesPath.'/'.$staff_id.'.JPG';
                    //	$destination_path = $UserPicturesPath;	 
                    $fileName = $_FILES['file']['name'];
                    $tmpName = $_FILES['file']['tmp_name'];
                    $fileSize = $_FILES['file']['size'];
                    $fileType = $_FILES['file']['type'];
                    $upload = new upload();
                    //	$upload->target_path=$target_path;
                    //	$upload->deleteOldImage();
                    //	$upload->destination_path=$destination_path;
                    $upload->name = $_FILES["file"]["name"];
                    $upload->setFileExtension();
                    $upload->fileExtension;
                    $upload->validateImage();
                    if ($upload->wrongFormat == 1) {
                        $_FILES["file"]["error"] = 1;
                    }

                    if ($_FILES["file"]["error"] > 0) {
                        echo "cannot upload file";
                    } else {
                        //                        $fp = fopen($tmpName, 'r');
                        //                        $content = fread($fp, filesize($tmpName));
                        $content = base64_decode($_REQUEST['imgblob']);
                        $content = addslashes($content);
                        // fclose($fp);
                        DBQuery('UPDATE staff SET IMG_NAME=\'' . $fileName . '\',IMG_CONTENT=\'' . $content . '\' WHERE STAFF_ID=' . $staff_id);
                        //PopTable('footer');
                    }
                }

                $_SESSION['staff_id'] = $_REQUEST['staff_id'] = $staff_id;
                echo "<script>window.location.href='Modules.php?modname=users/Staff.php&include=SchoolsInfoInc&category_id=3&modfunc='</script>";
                if ($school_array) {
                    $rel_value = '';
                    foreach ($school_array as $school_id => $yes) {
                        if ($_REQUEST['day_values']['START_DATE'][$school_id]) {
                            $start_date = $_REQUEST['day_values']['START_DATE'][$school_id] . "-" . $_REQUEST['month_values']['START_DATE'][$school_id] . "-" . $_REQUEST['year_values']['START_DATE'][$school_id];
                        } else {
                            $start_date = '';
                        }
                        if ($_REQUEST['day_values']['END_DATE'][$school_id]) {
                            $end_date = $_REQUEST['day_values']['END_DATE'][$school_id] . "-" . $_REQUEST['month_values']['END_DATE'][$school_id] . "-" . $_REQUEST['year_values']['END_DATE'][$school_id];
                        } else {
                            $end_date = '';
                        }
                        if ($end_date != '') {
                            $end_date = date('Y-m-d', strtotime($end_date));
                        } else {
                            $end_date = '0000-00-00';
                        }

                        if (($start_date != '' && VerifyDate($start_date)) || ($end_date != '' && VerifyDate($end_date)) || ($start_date == '' && $end_date == '')) {
                            $day_valid = true;

                            $user_syear_RET = DBGet(DBQuery('SELECT MAX(syear) AS USERSYEAR FROM school_years WHERE school_id=\'' . $school_id . '\''));
                            $usersyear = $user_syear_RET[1]['USERSYEAR'];
                            $rel_value .= "($staff_id,$school_id,$usersyear,'" . date('Y-m-d', strtotime($start_date)) . "','" . $end_date . "'),";
                        } else {
                            $day_valid = true;
                            $user_syear_RET = DBGet(DBQuery('SELECT MAX(syear) AS USERSYEAR FROM school_years WHERE school_id=\'' . $school_id . '\''));
                            $usersyear = $user_syear_RET[1]['USERSYEAR'];
                            $rel_value .= "($staff_id,$school_id,$usersyear,'0000-00-00','" . $end_date . "'),";
                        }
                    }
                    $rel_value = substr($rel_value, 0, -1);
                    DBQuery("INSERT INTO staff_school_relationship(staff_id,school_id,syear,start_date,end_date)VALUES ($rel_value)");
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'] = $staff_id;
                } else {
                    $val = DBGet(DBQuery("SELECT syear FROM school_years WHERE school_id='" . UserSchool() . "'"));
                    // DBQuery("INSERT INTO staff_school_relationship(staff_id,school_id,syear,start_date) values ('" . $staff_id . "','" . UserSchool() . "','" . $val[1]['SYEAR'] . "','" . date('Y-m-d') . "')");

                    DBQuery("INSERT INTO staff_school_relationship(staff_id,school_id,syear,start_date) values ('" . $staff_id . "','" . UserSchool() . "','" . UserSyear() . "','" . date('Y-m-d') . "')");
                }
            } else {
                echo "<div class='alert bg-danger alert-styled-left'>Unable to save data, because " . $custom_TITLE . ' is required.</div>';

                $_REQUEST['staff_id'] = 'new';
                $staff = array();
                $value = '';
            }
        }
    }
    if ($day_valid == false) {
        echo "<div class='alert bg-danger alert-styled-left'>Invalid date could not be saved.</div>";
    }
    if ($error == 'end_date') {
        echo "<div class='alert bg-danger alert-styled-left'>Start date can not be greater than End date</div>";
        unset($error);
        if ($_REQUEST['staff_id'] == 'new') {
            header("location:modules/users/Staff.php&staff_id=new");
        }
    }
    if ($error == 'start_date') {
        echo "<div class='alert bg-danger alert-styled-left'>Start date can not be blank</div>";
        unset($error);
    }
    unset($_REQUEST['staff']);
    unset($_REQUEST['modfunc']);
    unset($_SESSION['_REQUEST_vars']['staff']);
    unset($_SESSION['_REQUEST_vars']['modfunc']);


    if (User('STAFF_ID') == $_REQUEST['staff_id']) {
        unset($_openSIS['User']);
        //        echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }
}

$extra['SELECT'] = ',LAST_LOGIN';
$extra['columns_after'] = array('LAST_LOGIN' => _lastLogin);
$extra['functions'] = array('LAST_LOGIN' => 'makeLogin');

if (basename($_SERVER['PHP_SELF']) != 'index.php') {
    if ($_REQUEST['staff_id'] == 'new')
        DrawBC("" . _users . " > Add a User");
    else
        DrawBC("" . _users . " > " . ProgramTitle());
    SearchStaff('staff_id', $extra);
} else
    DrawHeader(_createAccount);
if ($_REQUEST['modfunc'] == 'delete' && $_REQUEST['include'] != 'FilesInc' && basename($_SERVER['PHP_SELF']) != 'index.php' && AllowEdit()) {

    # ------------------------------------  For Certification Start ------------------------------------------- #

    if (DeletePrompt('certification')) {
        DBQuery("DELETE FROM staff_certification WHERE STAFF_CERTIFICATION_ID='$_REQUEST[certification_id]'");

        unset($_REQUEST['modfunc']);


        $_REQUEST['certification_id'] = 'new';
    }
}
if ($_REQUEST['modfunc'] == 'delete' && $_REQUEST['include'] == 'FilesInc' && (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')) {
    if (DeletePromptFilesEncoded($_REQUEST['title'], '&include=FilesInc&category_id=7')) {
        unlink('assets/stafffiles/' . base64_decode($_REQUEST['removefile']));

        DBQuery('DELETE FROM user_file_upload WHERE ID=' . $_REQUEST['del']);

        unset($_REQUEST['modfunc']);
    }
}
if ((UserStaffID() || $_REQUEST['staff_id'] == 'new') && ((basename($_SERVER['PHP_SELF']) != 'index.php') || !$_REQUEST['staff']['USERNAME']) && $_REQUEST['modfunc'] != 'delete' && $_SESSION['fn'] != 'user' && $_REQUEST['modfunc'] != 'remove') {

    echo '<div class="panel panel-default">';
    if ($_REQUEST['modfunc'] != 'delete' || $_REQUEST['delete_ok'] == '1') {
        if ($_REQUEST['staff_id'] != 'new') {


            $sql = "SELECT * FROM staff  WHERE STAFF_ID='" . UserStaffID() . "'";
            $QI = DBQuery($sql);
            $staff = DBGet($QI);
            $staff = $staff[1];


            $email = $staff['EMAIL'];


            echo "<FORM name=staff class=\"form-horizontal\" id=staff action=Modules.php?modname=$_REQUEST[modname]&custom=staff&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&staff_id=" . UserStaffID() . "&modfunc=update method=POST enctype='multipart/form-data'>";
        } elseif (basename($_SERVER['PHP_SELF']) != 'index.php')
            echo "<FORM name=staff class=\"form-horizontal\" id=staff action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&modfunc=update method=POST enctype='multipart/form-data'> ";
        else
            echo "<FORM name=F2 class=\"form-horizontal\" id=F2 action=index.php?modfunc=create_account METHOD=POST>";

        if (User('PROFILE') != 'student') {
            if (User('PROFILE_ID') != '') {
                $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
            } else {
                $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID') . "'"));
                $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                if ($profile_id_mod != '') {
                    $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
                }
            }
        } else
            $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='3' AND CAN_USE='Y'"), array(), array('MODNAME'));
        $categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM staff_field_categories ORDER BY SORT_ORDER,TITLE"));

        $tabs = array();
        foreach ($categories_RET as $category) {
            // print_r($categories_RET);
            if ($can_use_RET['users/Staff.php&category_id=' . $category['ID']]) {
                if ($category['ID'] == '1')
                    $include = 'DemographicInfoInc';
                elseif ($category['ID'] == '2')
                    $include = 'AddressInc';
                elseif ($category['ID'] == '3') {
                    $include = 'SchoolsInfoInc';
                    if (User('PROFILE') == 'teacher')
                        $_REQUEST['teacher_view'] = 'y';
                } elseif ($category['ID'] == '4')
                    $include = 'CertificationInfoInc';
                elseif ($category['ID'] == '5')
                    $include = 'ScheduleInc';
                elseif ($category['ID'] == '6')
                    $include = 'FilesInc';
                elseif ($category['INCLUDE'])
                    $include = $category['INCLUDE'];
                else
                    $include = 'OtherInfoInc';
                switch ($category['TITLE']) {
                    case 'Demographic Info':
                        $categoryTitle = _demographicInfo;
                        break;
                    case 'Addresses &amp; Contacts':
                        $categoryTitle = _addressesContacts;
                        break;
                    case 'School Information':
                        $categoryTitle = _schoolInformation;
                        break;
                    case 'Certification Information':
                        $categoryTitle = _certificationInformation;
                        break;
                    case 'Schedule':
                        $categoryTitle = _schedule;
                        break;
                    default:
                        $categoryTitle = $category['TITLE'];
                }
                // echo "$category[TITLE]<br>";
                // // echo "<"
                // echo "$categoryTitle<br>";
                if ($category['ID'] == '5') {
                    $tabs[] = array('title' => $categoryTitle, 'link' => "Modules.php?modname=$_REQUEST[modname]&include=$include&custom=staff&category_id=" . $category['ID'] . "&LO_sort=DURATION&LO_direction=1");
                } else {
                    $tabs[] = array('title' => $categoryTitle, 'link' => "Modules.php?modname=$_REQUEST[modname]&include=$include&custom=staff&category_id=" . $category['ID']);
                }
            }
        }
        unset($new_tabs);
        unset($ti);
        unset($td);
        $swap_tabs = 'n';
        foreach ($tabs as $ti => $td) {
            if ($td['title'] == _schoolInformation)
                $swap_tabs = 'y';
        }

        if ($swap_tabs == 'y') {
            foreach ($tabs as $ti => $td) {
                if ($td['title'] == _demographicInfo)
                    $new_tabs[0] = $td;
                elseif ($td['title'] == _schoolInformation)
                    $new_tabs[1] = $td;
                else
                    $new_tabs[$ti + 1] = $td;
            }
        }

        if (count($new_tabs)) {
            unset($tabs);
            $tabs = $new_tabs;
            ksort($tabs);
        }


        unset($new_tabs);
        unset($ti);
        unset($td);

        $swap_tabs = 'n';
        $_openSIS['selected_tab'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&custom=staff";
        if ($_REQUEST['category_id'])
            $_openSIS['selected_tab'] .= '&category_id=' . $_REQUEST['category_id'];


        //echo '<div class="panel panel-default">';
        echo '<div id=sh_err></div>';
        echo '<div id=prof_err></div>';
        echo '<div id=cat_err></div>';
        if (Count($tabs) == 0)
            echo PopTable('header',  _demographicInfo);
        else
            echo PopTable('header', $tabs, '');
        echo '<div id=sh_err></div>';
        if (!strpos($_REQUEST['include'], '/'))
            include('modules/users/includes/' . $_REQUEST['include'] . '.php');
        else {
            include('modules/' . $_REQUEST['include'] . '.php');
            $separator = '<HR>';
            include('modules/users/includes/OtherInfoInc.php');
        }
        echo PopTable('footer');
        if (User('PROFILE') == 'admin') {

            if (isset($_SESSION['staff_id'])) {
                $this_school_RET_mod = DBGet(DBQuery("SELECT s.*,l.* FROM staff s,login_authentication l  WHERE l.USER_ID=s.STAFF_ID AND l.PROFILE_ID NOT IN (3,4) AND s.STAFF_ID=" . UserStaffID()));

                $this_school_mod = $this_school_RET_mod[1];

                $username = $this_school_mod['USERNAME'];
                $password = $this_school_mod['PASSWORD'];
                $this_school_RET = DBGet(DBQuery("SELECT * FROM staff_school_info   WHERE   STAFF_ID=" . UserStaffID()));
                $this_school = $this_school_RET[1];
            } else {
                $username = '';
                $password = '';
                $this_school['JOINING_DATE'] = '';
            }

            if ($_REQUEST['staff_id'] != 'new') {
                if ($_REQUEST['category_id'] == 1 && ($username == '' || $password == '' || $this_school['JOINING_DATE'] == '')) {
                    echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_saveNext, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
                }
                if ($_REQUEST['category_id'] == 1 && $username != '' && $password != '' && $this_school['JOINING_DATE'] != '') {
                    echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
                }
                if ($_REQUEST['category_id'] != 1) {
                    $btn_flag = 1;
                    if (User('PROFILE') == 'admin' && $_REQUEST['modname'] == 'users/Staff.php' && isset($_REQUEST['include']) && $_REQUEST['include'] == 'ScheduleInc')
                        $btn_flag = 0;
                    if ($btn_flag == 1)
                        echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
                }
            } else {
                if ($_REQUEST['category_id'] != 3) {
                    echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_saveNext, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
                } else {
                    if ($_SESSION['staff_school_chkbox_id'] != '') {
                        echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(' . $_SESSION['staff_school_chkbox_id'] . ', this);"') . '</div></div>';
                    } else
                        echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'id="mod_staff_btn" class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
                    unset($_SESSION['staff_school_chkbox_id']);
                }
            }
        } elseif (User('PROFILE') == 'teacher') {
            if ($_REQUEST['include'] != 'ScheduleInc' && $_REQUEST['include'] != 'SchoolsInfoInc')
                echo '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'class="btn btn-primary pull-right" onClick="return formcheck_add_staff(0, this);"') . '</div></div>';
        }
        echo '</FORM>';
    } else
    if (!strpos($_REQUEST['include'], '/'))
        include('modules/users/includes/' . $_REQUEST['include'] . '.php');
    else {
        include('modules/' . $_REQUEST['include'] . '.php');
        $separator = '<div class=break></div>';
        include('modules/users/includes/OtherInfoInc.php');
    }
    echo '</div>'; //.panel.panel-default
}
if ($_REQUEST['modfunc'] == 'remove') {
    include('modules/users/includes/CertificationInfoInc.php');
}

class upload
{

    var $target_path;
    var $destination_path;
    var $name;
    var $fileExtension;
    var $allowExtension = array("jpg", "jpeg", "png", "gif", "bmp");
    var $wrongFormat = 0;
    var $wrongSize = 0;
    function deleteOldImage($id = '')
    {
        //if(file_exists($this->target_path))
        //	unlink($this->target_path);
        if ($id != '') {
            DBQuery('UPDATE staff SET IMG_NAME=NULL,IMG_CONTENT=NULL WHERE STAFF_ID=' . $id);
        }
    }

    function setFileExtension()
    {
        $this->fileExtension = strtolower(substr($this->name, strrpos($this->name, ".") + 1));
    }

    function validateImage()
    {
        if (!in_array($this->fileExtension, $this->allowExtension)) {
            $this->wrongFormat = 1;
        }
    }
    function validateImageSize()
    {
        if ($this->fileSize > 10485760) {
            $this->wrongSize = 1;
        }
    }

    function get_file_extension($file_name)
    {
        return end(explode('.', $file_name));
    }
}
