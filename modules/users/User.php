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
//session_start();
!empty($_SESSION['USERNAME']) or die('Access denied!');
include('../../RedirectModulesInc.php');
if (isset($_REQUEST['custom_date_id']) && count($_REQUEST['custom_date_id']) > 0) {
    foreach ($_REQUEST['custom_date_id'] as $custom_id) {
        $_REQUEST['staff']['CUSTOM_' . $custom_id] = $_REQUEST['year_CUSTOM_' . $custom_id] . '-' . $_REQUEST['month_CUSTOM_' . $custom_id] . '-' . $_REQUEST['day_CUSTOM_' . $custom_id];
    }
}
if (isset($_REQUEST['user_checkbox']) && count($_REQUEST['user_checkbox']) > 0) {
    foreach ($_REQUEST['user_checkbox'] as $custom_id => $custom_arr) {
        $temp_arr = implode('||', $custom_arr);
        $_REQUEST['staff'][$custom_id] = '||' . $temp_arr . '||';
    }
}

$st_flag = false;
$error = false;
$error_school = '';
if ($_REQUEST['staff_id'] != 'new') {
    $profile = DBGet(DBQuery('SELECT id FROM user_profiles WHERE profile = \'' . 'parent' . '\''));
    $parent_ids_arr = array();
    foreach ($profile as $k => $v) {
        $parent_ids_arr[] = $profile[$k]['ID'];
    }

    if (UserID() && !$_REQUEST['staff_id'])
        $user_profile = DBGet(DBQuery("SELECT profile_id FROM people WHERE staff_id='" . UserID() . "'"));
    else
        $user_profile = DBGet(DBQuery("SELECT profile_id FROM people WHERE staff_id='" . $_REQUEST['staff_id'] . "'"));
    if (in_array($user_profile[1]['PROFILE_ID'], $parent_ids_arr)) {
        $_SESSION['fn'] = 'user';
    } else {
        $_SESSION['fn'] = 'staff';
    }
} else {
    $_SESSION['fn'] = '';
}
###########################################
#print_r($_REQUEST);
if (isset($_REQUEST['staff_id']) && $_REQUEST['staff_id'] != 'new') {
    if (User('PROFILE') == 'admin') {
        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM people WHERE STAFF_ID=\'' . $_REQUEST['staff_id'] . '\''));
        $count_staff_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM people'));
        if ($count_staff_RET[1]['NUM'] > 1) {
            echo '<div class="panel panel-default">';
            DrawHeader('' . _selectedUser . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true' . ($_REQUEST['profile'] == 'none' ? '&profile=none' : '') . ' target=body><i class="icon-square-left"></i> ' . _backToUserList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
            echo '</div>';
        } else {
            echo '<div class="panel panel-default">';
            DrawHeader('' . _selectedUser . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . $RET[1]['LAST_NAME'], '<div class="btn-group heading-btn"><A HREF=Side.php?staff_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
            echo '</div>';
        }
    }
}
#############################################
if (User('PROFILE') != 'admin' && User('PROFILE') != 'teacher' && $_REQUEST['staff_id'] && $_REQUEST['staff_id'] != 'new') {
    if (!AllowUse()) {
        if (User('USERNAME')) {
            HackingLog();
        }
        exit;
    }
}

if ($_REQUEST['modfunc'] == 'remove_stu') {
    $delete = DeletePromptMod('student', "include=GeneralInfoInc&category_id=1&staff_id=$_REQUEST[staff_id]" . ($_REQUEST['profile'] == 'none' ? '&profile=none' : ''));
    if ($delete == 1) {
        DBQuery('DELETE FROM students_join_people WHERE STUDENT_ID=' . $_REQUEST['id'] . ' AND PERSON_ID=' . $_REQUEST['staff_id']);
        echo "<script>window.location.href='Modules.php?modname=$_REQUEST[modname]&staff_id=$_REQUEST[staff_id]'</script>";
    }
} else {


    if (!$_REQUEST['include']) {
        $_REQUEST['include'] = 'GeneralInfoInc';
        $_REQUEST['category_id'] = '1';
    } elseif (!$_REQUEST['category_id'])
        if ($_REQUEST['include'] == 'GeneralInfoInc')
            $_REQUEST['category_id'] = '1';

        elseif ($_REQUEST['include'] == 'AddressInfoInc')
            $_REQUEST['category_id'] = '2';

        elseif ($_REQUEST['include'] != 'OtherInfoUserInc') {
            $include = DBGet(DBQuery('SELECT ID FROM people_field_categories WHERE INCLUDE=\'' . $_REQUEST['include'] . '\''));
            $_REQUEST['category_id'] = $include[1]['ID'];
        }




    if (User('PROFILE') != 'admin') {
        if (User('PROFILE_ID'))
            $can_edit_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND MODNAME=\'' . 'users/User.php&category_id=' . $_REQUEST['category_id'] . '\' AND CAN_EDIT=\'' . 'Y' . '\''));
        else {
            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
            if ($profile_id_mod != '')
                $can_edit_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . $profile_id_mod . '\' AND MODNAME=\'' . 'users/User.php&category_id=' . $_REQUEST['category_id'] . '\' AND CAN_EDIT=\'' . 'Y' . '\''), array(), array('MODNAME'));
        }
        if ($can_edit_RET)
            $_openSIS['allow_edit'] = true;
    }

    unset($schools);

    if ($_REQUEST['modfunc'] == 'update') {
        $up_go = 'n';

        if ($_REQUEST['category_id'] == 1) {



            if (is_countable($_REQUEST['staff']) && count($_REQUEST['staff']) > 0) {

                $disp_error = '';
                if ($_REQUEST['modfunc'] == 'update') {
                    // print_r($_REQUEST);
                    $flag = 0;
                    $qry = 'UPDATE people SET ';
                    // print_r($_REQUEST['people']);
                    foreach ($_REQUEST['staff'] as $in => $d) {

                        $field_id = explode('_', $in);
                        $field_id = $field_id[1];
                        $check_stat = DBGet(DBQuery('SELECT TITLE,REQUIRED FROM people_fields WHERE ID=\'' . $field_id . '\' '));
                        if ($check_stat[1]['REQUIRED'] == 'Y') {
                            if ($d != '') {
                                $qry .= ' ' . $in . '=\'' . str_replace("'", "''", str_replace("\'", "'", $d)) . '\',';
                                $flag++;
                            } else {
                                $disp_error = '<div class="alert alert-danger">' . $check_stat[1]['TITLE'] . ' is required.</div>';
                            }
                        } else {
                            if ($d != '')
                                $qry .= ' ' . $in . '=\'' . str_replace("'", "''", str_replace("\'", "'", $d)) . '\',';
                            else
                                $qry .= ' ' . $in . '=NULL,';

                            $flag++;
                        }
                    }
                    if ($flag > 0) {

                        $qry = substr($qry, 0, -1) . ' WHERE STAFF_ID=' . $_REQUEST['staff_id'];
                        DBQuery($qry);
                    }
                }
            }
            if (is_countable($_REQUEST['people']) && count($_REQUEST['people']) > 0) {
                $staff_info_sql = "SELECT PROFILE_ID FROM people WHERE STAFF_ID=" . $_REQUEST['staff_id'];
                $staff_info = DBGet(DBQuery($staff_info_sql));
                $staff_prof_id = $staff_info[1]['PROFILE_ID'];

                if ($_REQUEST['people']['PROFILE_ID'] != '') {
                    $update_sql = 'UPDATE login_authentication SET PROFILE_ID =\'' . $_REQUEST['people']['PROFILE_ID'] . '\' WHERE USER_ID = ' . $_REQUEST['staff_id'] . ' AND PROFILE_ID = ' . $staff_prof_id;
                    DBQuery($update_sql);
                }

                $up_sql = 'UPDATE people SET ';
                foreach ($_REQUEST['people'] as $pi => $pd) {


                    $up_sql .= $pi . "='" . str_replace("'", "''", str_replace("\'", "'", $pd)) . "',";
                    $up_go = 'y';
                }
                if ($up_go == 'y') {
                    $up_sql = substr($up_sql, 0, -1);
                    $up_sql .= " WHERE STAFF_ID=" . $_REQUEST['staff_id'];

                    DBQuery($up_sql);
                }
                unset($up_sql);
                unset($pi);
                unset($pd);
                unset($up_go);
            }
            $up_go = 'n';
            if ($_REQUEST['login_authentication']['USERNAME'] != '') {
                $usernameExists = DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME=\'' . $_REQUEST['login_authentication']['USERNAME'] . '\''));
                if ($staff_prof_id == '') {
                    $staff_info_sql = "SELECT PROFILE_ID FROM people WHERE STAFF_ID=" . $_REQUEST['staff_id'];
                    $staff_info = DBGet(DBQuery($staff_info_sql));
                    $staff_prof_id = $staff_info[1]['PROFILE_ID'];
                }
                if(count($usernameExists) == 0){
                    $up_sql = 'UPDATE login_authentication SET USERNAME=\'' . $_REQUEST['login_authentication']['USERNAME'] . '\' WHERE USER_ID=' . $_REQUEST['staff_id'] . ' AND PROFILE_ID = ' . $staff_prof_id;
                    DBQuery($up_sql);
                    unset($up_sql);
                } else {
                    if($usernameExists[1]['USER_ID'] != $_REQUEST['staff_id'] || $usernameExists[1]['PROFILE_ID'] != $staff_prof_id){
                        echo '<div class="alert alert-danger">Username already exists.</div>';
                    }
                }
            }
            if ($_REQUEST['login_authentication']['PASSWORD'] != '') {
                if ($staff_prof_id == '') {
                    $staff_info_sql = "SELECT PROFILE_ID FROM people WHERE STAFF_ID=" . $_REQUEST['staff_id'];
                    $staff_info = DBGet(DBQuery($staff_info_sql));
                    $staff_prof_id = $staff_info[1]['PROFILE_ID'];
                }
                $up_sql = 'UPDATE login_authentication SET PASSWORD=\'' . md5($_REQUEST['login_authentication']['PASSWORD']) . '\' WHERE USER_ID=' . $_REQUEST['staff_id'] . ' AND PROFILE_ID = ' . $staff_prof_id;
                DBQuery($up_sql);
                unset($up_sql);
            }
            if ($_REQUEST['profile'] == 'none' && $_REQUEST['FRESH_USERNAME'] != '' && $_REQUEST['FRESH_PASSWORD'] != '') {
                DBQuery('INSERT INTO login_authentication (USER_ID,PROFILE_ID,USERNAME,PASSWORD) VALUES (' . $_REQUEST['staff_id'] . ',4,\'' . singleQuoteReplace("", "", $_REQUEST['FRESH_USERNAME']) . '\',\'' . md5($_REQUEST['FRESH_PASSWORD']) . '\')');
                echo "<script>window.location.href='Modules.php?modname=users/User.php&staff_id=$_REQUEST[staff_id]';</script>";
            }
        } else if ($_REQUEST['category_id'] == 2) {

            if (is_countable($_REQUEST['people']) && count($_REQUEST['people']) > 0) {

                $up_sql = 'UPDATE people SET ';
                foreach ($_REQUEST['people'] as $pi => $pd) {


                    $up_sql .= $pi . "='" . str_replace("'", "''", str_replace("\'", "'", $pd)) . "',";
                    $up_go = 'y';
                }
                if ($up_go == 'y') {
                    $up_sql = substr($up_sql, 0, -1);
                    $up_sql .= " WHERE STAFF_ID=" . $_REQUEST['staff_id'];

                    DBQuery($up_sql);
                }
                unset($up_sql);
                unset($pi);
                unset($pd);
                unset($up_go);
            }





            if (is_countable($_REQUEST['student_addres']) && count($_REQUEST['student_addres']) > 0) {
                $up_sql = 'UPDATE student_address SET ';
                foreach ($_REQUEST['student_addres'] as $pi => $pd) {

                    $up_sql .= $pi . "='" . str_replace("'", "''", str_replace("\'", "'", $pd)) . "',";
                    $up_go = 'y';
                }
                if ($up_go == 'y') {
                    $up_sql = substr($up_sql, 0, -1);
                    $up_sql .= " WHERE PEOPLE_ID=" . $_REQUEST['staff_id'];

                    DBQuery($up_sql);
                }
                unset($up_sql);
                unset($pi);
                unset($pd);
                unset($up_go);
            }
        } else {

            $disp_error = '';
            if ($_REQUEST['modfunc'] == 'update') {
                // print_r($_REQUEST);
                $flag = 0;
                $qry = 'UPDATE people SET ';
                // print_r($_REQUEST['people']);
                foreach ($_REQUEST['staff'] as $in => $d) {

                    $field_id = explode('_', $in);
                    $field_id = $field_id[1];
                    $check_stat = DBGet(DBQuery('SELECT TITLE,REQUIRED FROM people_fields WHERE ID=\'' . $field_id . '\' '));

                    $m_custom_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE FROM people_fields WHERE ID='" . $field_id . "' AND TYPE='multiple'"));
                    if ($m_custom_RET) {
                        $str = "";

                        foreach ($d as $m_custom_val) {
                            if ($m_custom_val)
                                $str .= "||" . $m_custom_val;
                        }
                        if ($str)
                            $d = $str . "||";
                        else
                            $d = '';
                    }

                    if ($check_stat[1]['REQUIRED'] == 'Y') {
                        if ($d != '') {
                            $qry .= ' ' . $in . '=\'' . str_replace("'", "''", str_replace("\'", "'", $d)) . '\',';
                            $flag++;
                        } else {
                            $disp_error = '<div class="alert alert-danger">' . $check_stat[1]['TITLE'] . ' is required.</div>';
                        }
                    } else {
                        if ($d != '')
                            $qry .= ' ' . $in . '=\'' . str_replace("'", "''", str_replace("\'", "'", $d)) . '\',';
                        else
                            $qry .= ' ' . $in . '=NULL,';

                        $flag++;
                    }
                }
                if ($flag > 0) {

                    $qry = substr($qry, 0, -1) . ' WHERE STAFF_ID=' . $_REQUEST['staff_id'];
                    DBQuery($qry);
                }
            }
        }
    }
    if ($disp_error != '') {
        echo $disp_error;
        unset($disp_error);
    }





    $extra['SELECT'] = ',LAST_LOGIN';

    $extra['functions'] = array('LAST_LOGIN' => 'makeLogin');

    if (basename($_SERVER['PHP_SELF']) != 'index.php') {
        if ($_REQUEST['staff_id'] == 'new')
            DrawBC("" . _users . " > Add a User");
        else
            DrawBC("" . _users . " > " . ProgramTitle());
        unset($_SESSION['staff_id']);

        Search('staff_id', $extra);
    } else
        DrawHeader(_createAccount);

    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete' && basename($_SERVER['PHP_SELF']) != 'index.php' && AllowEdit()) {
        if (DeletePrompt('user')) {
            DBQuery('DELETE FROM program_user_config WHERE USER_ID=\'' . UserStaffID() . '\'');

            DBQuery('DELETE FROM students_join_people WHERE PERSON_ID=\'' . UserStaffID() . '\'');
            DBQuery('DELETE FROM staff WHERE STAFF_ID=\'' . UserStaffID() . '\'');
            unset($_SESSION['staff_id']);
            unset($_REQUEST['staff_id']);
            unset($_REQUEST['modfunc']);
            //            echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            Search('staff_id', $extra);
        }
    }
    if ((UserStaffID() || $_REQUEST['staff_id'] == 'new') && ((basename($_SERVER['PHP_SELF']) != 'index.php') || !$_REQUEST['staff']['USERNAME']) && $_REQUEST['modfunc'] != 'delete' && $_SESSION['fn'] != 'staff') {

        if ($_REQUEST['staff_id'] != 'new') {

            if ($_REQUEST['profile'] == 'none')
                $sql = 'SELECT s.TITLE,s.STAFF_ID,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,
                \'\' as USERNAME,\'\' as PASSWORD,up.TITLE AS PROFILE,s.PROFILE_ID,s.HOME_PHONE,s.EMAIL,\'\' as LAST_LOGIN,IS_DISABLE
                FROM people s,user_profiles up WHERE s.STAFF_ID=\'' . UserStaffID() . '\' AND s.PROFILE_ID=up.ID AND s.PROFILE_ID=4';
            else
                $sql = 'SELECT s.TITLE,s.STAFF_ID,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,
                USERNAME,PASSWORD,up.TITLE AS PROFILE,s.PROFILE_ID,s.HOME_PHONE,s.EMAIL,LAST_LOGIN,IS_DISABLE
                FROM people s,user_profiles up,login_authentication la WHERE s.STAFF_ID=la.USER_ID AND la.PROFILE_ID in (SELECT id FROM user_profiles WHERE profile = \'' . 'parent' . '\') AND s.STAFF_ID=\'' . UserStaffID() . '\' AND s.PROFILE_ID=up.ID';
            $QI = DBQuery($sql);
            $staff = DBGet($QI);

            $staff = $staff[1];
            echo "<FORM class=\"form-horizontal\" name=staff id=staff action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&staff_id=" . UserStaffID() . "&modfunc=update" . ($_REQUEST['profile'] == 'none' ? '&profile=none' : '') . " method=POST >";
        } elseif (basename($_SERVER['PHP_SELF']) != 'index.php') {
            $staff = array();
            echo "<FORM name=staff id=staff action=Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&category_id=$_REQUEST[category_id]&modfunc=update" . ($_REQUEST['profile'] == 'none' ? '&profile=none' : '') . " method=POST>";
        } else
            echo "<FORM name=F2 id=F2 action=index.php?modfunc=create_account METHOD=POST>";

        if (basename($_SERVER['PHP_SELF']) != 'index.php') {
            if (UserStaffID() && UserStaffID() != User('STAFF_ID') && UserStaffID() != $_SESSION['STAFF_ID'] && User('PROFILE') == 'admin')
                $delete_button = '<INPUT type=button class="btn btn-danger" value=' . _delete . ' onclick="window.location=\'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=delete\'">';
        }

        if (User('PROFILE_ID') != '')
            $can_use_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND CAN_USE=\'' . 'Y' . '\''), array(), array('MODNAME'));
        else {
            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
            if ($profile_id_mod != '')
                $can_use_RET = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . $profile_id_mod . '\' AND CAN_USE=\'' . 'Y' . '\''), array(), array('MODNAME'));
        }
        $profile = DBGet(DBQuery("SELECT PROFILE FROM people WHERE STAFF_ID='" . UserStaffID() . "'"));

        $profile = $profile[1]['PROFILE'];

        $categories_RET = DBGet(DBQuery('SELECT ID,TITLE,INCLUDE FROM people_field_categories WHERE ' . ($profile ? strtoupper($profile) . '=\'Y\'' : 'ID=\'1\'') . ' ORDER BY SORT_ORDER,TITLE'));

        foreach ($categories_RET as $category) {
            if ($can_use_RET['users/User.php&category_id=' . $category['ID']]) {
                if ($category['ID'] == '1')
                    $include = 'GeneralInfoInc';
                elseif ($category['ID'] == '2')
                    $include = 'AddressInfoInc';
                elseif ($category['INCLUDE'])
                    $include = $category['INCLUDE'];
                else
                    $include = 'OtherInfoUserInc';
                // echo "$category[TITLE]<br>";
                switch ($category['TITLE']) {
                    case 'General Info':
                        $categoryTitle = _generalInfo;
                        break;
                    case 'Address Info':
                        $categoryTitle = _addressInfo;
                        break;
                    default:
                        $categoryTitle = $category['TITLE'];
                        break;
                }

                if (User('PROFILE_ID') == 4)
                    $tabs[] = array('title' => $categoryTitle, 'link' => "Modules.php?modname=$_REQUEST[modname]&include=$include&category_id=" . $category['ID'] . ($_REQUEST['profile'] == 'none' ? '&profile=none' : ''));
                else
                    $tabs[] = array('title' => $categoryTitle, 'link' => "Modules.php?modname=$_REQUEST[modname]&include=$include&category_id=" . $category['ID'] . "&staff_id=" . UserStaffID() . ($_REQUEST['profile'] == 'none' ? '&profile=none' : ''));
            }
        }


        $_openSIS['selected_tab'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]";
        if ($_REQUEST['category_id'])
            $_openSIS['selected_tab'] .= '&category_id=' . $_REQUEST['category_id'];
        if (User('PROFILE_ID') != 4)
            $_openSIS['selected_tab'] .= '&staff_id=' . $_REQUEST['staff_id'];
        $_openSIS['selected_tab'] .= ($_REQUEST['profile'] == 'none' ? '&profile=none' : '');

        //echo '<div class="panel">';
        PopTable('header', $tabs);


        if (!strpos($_REQUEST['include'], '/')) {
            include('modules/users/includes/' . $_REQUEST['include'] . '.php');
        } else {
            include('modules/' . $_REQUEST['include'] . '.php');
            $separator = '<HR>';
            include('modules/users/includes/OtherInfoUserInc.php');
        }


        $sql = 'SELECT count(s.ID) as schools FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=' . UserSyear() . ' AND st.staff_id=' . User('STAFF_ID');
        $school_admin = DBGet(DBQuery($sql));
        $submit_btn = SubmitButton(_save, '', 'id="saveUserBtn" class="btn btn-primary pull-right" onclick="return formcheck_user_user_mod(' . $_SESSION['staff_school_chkbox_id'] . ', this);"');

        PopTable('footer', $submit_btn);
        echo '</FORM>';
    }
    unset($_SESSION['fn']);
}
