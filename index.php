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
//declare(strict_types=1);
error_reporting(0);

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

include_once "functions/DelDirectoryFnc.php";
include_once "functions/ParamLibFnc.php";
require_once "functions/PragRepFnc.php";
include_once "RemoveBackup.php";
include_once 'lang/language.php';
include_once "functions/PasswordHashFnc.php";

session_start();

$index_commit_in    =   "";
$index_commit_out   =   "";

$url = validateQueryString(curPageURL());
if ($url === FALSE) {
    header('Location: index.php');
}

if (!defined("_eitherYourAccountIsInactiveOrYourAccessPermissionHasBeenRevoked")) {
    define("_eitherYourAccountIsInactiveOrYourAccessPermissionHasBeenRevoked", "Either your account is inactive or your access permission has been revoked");
}

if (!defined("_pleaseContactTheSchoolAdministration")) {
    define("_pleaseContactTheSchoolAdministration", "Please contact the school administration");
}

if (optional_param('dis', '', PARAM_ALPHAEXT) == 'fl_count') {
    $error[] = _eitherYourAccountIsInactiveOrYourAccessPermissionHasBeenRevoked . "." . _pleaseContactTheSchoolAdministration . ".";
}

if (optional_param('dis', '', PARAM_ALPHAEXT) == 'assoc_mis') {
    $error[] = "No student is associated with the parent. Please contact the school administration.";
}

if (isset($_GET['ins']))
    $install = optional_param('ins', '', PARAM_ALPHAEXT);

if ($install == 'comp') {
    if (is_dir('install')) {
        $dir = 'install/'; // IMPORTANT: with '/' at the end
        $remove_directory = delete_directory($dir);
    }
}

require_once('Warehouse.php');
# CHECKING ES STARTS: IF ES (EVENT SCHEDULER) IS FOUND OFF, IT SHOULD BE TURNED ON
$check_ES_status = DBGet(DBQuery("SHOW VARIABLES WHERE VARIABLE_NAME = 'event_scheduler'"));
if ($check_ES_status) {
    if ($check_ES_status[1]['VARIABLE_NAME'] == 'event_scheduler' && $check_ES_status[1]['VALUE'] != 'ON') {
        DBQuery('SET GLOBAL event_scheduler = ON');
    }
}
# CHECKING ES ENDS
$check_sql_mode = DBGet(DBQuery("SELECT @@GLOBAL.sql_mode"));
if ($check_sql_mode) {
    if ($check_sql_mode[1]['@@GLOBAL.SQL_MODE'] != 'NO_ENGINE_SUBSTITUTION') {
        DBQuery('SET @@GLOBAL.SQL_MODE = "NO_ENGINE_SUBSTITUTION"');
    }
}

$check_LBTFC = DBGet(DBQuery("SELECT @@GLOBAL.log_bin_trust_function_creators"));
if ($check_LBTFC) {
    if ($check_LBTFC[1]['@@GLOBAL.log_bin_trust_function_creators'] != 1) {
        DBQuery('SET @@GLOBAL.log_bin_trust_function_creators = 1');
    }
}
if (optional_param('modfunc', '', PARAM_ALPHAEXT) == 'logout') {
    if ($_SESSION) {
        DBQuery("DELETE FROM log_maintain WHERE SESSION_ID = '" . $_SESSION['X'] . "'");
        // header("Location: $_SERVER[PHP_SELF]?modfunc=logout" . (($_REQUEST['reason']) ? '&reason=' . $_REQUEST['reason'] : ''));
    }
    session_destroy();
}

if (optional_param('register', '', PARAM_NOTAGS)) {
    if (optional_param('R1', '', PARAM_ALPHA) == 'register')
        header("Location:register.php");
}

if (optional_param('USERNAME', '', PARAM_RAW) && optional_param('PASSWORD', '', PARAM_RAW) && CSRFSecure::ValidateToken(optional_param('TOKEN', '', PARAM_RAW))) {
    db_start();

    $username = mysqli_real_escape_string($connection, trim(optional_param('USERNAME', '', PARAM_RAW)));

    $arr_cookie_options = array(
        'expires' => time() + 60 * 60 * 24 * 100,
        'path' => '/',
        'secure' => true,       // or false
        'httponly' => true,     // or false
        'samesite' => 'Strict'  // None || Lax || Strict
    );

    if ($_REQUEST['remember']) {
        include_once "./AuthCryp.php";

        $cName = 'remember_me_name';
        $cPwd = 'remember_me_pwd';
        $cLang = 'remember_me_lang';

        setcookie($cName, cryptor($username, 'ENC'), $arr_cookie_options);
        setcookie($cPwd, cryptor(optional_param('PASSWORD', '', PARAM_RAW), 'ENC'), $arr_cookie_options);
        setcookie($cLang, optional_param('language', '', PARAM_RAW), $arr_cookie_options);
    } else {
        setcookie('remember_me_name', 'gone', time() - 60 * 60 * 24 * 100, "/");
        setcookie('remember_me_pwd', 'gone', time() - 60 * 60 * 24 * 100, "/");
        setcookie('remember_me_lang', 'gone', time() - 60 * 60 * 24 * 100, "/");
    }

    if ($password == optional_param('PASSWORD', '', PARAM_RAW))
        $password = str_replace("\'", "", (mysqli_real_escape_string($connection, optional_param('PASSWORD', '', PARAM_RAW))));
    $password = str_replace("&", "", (mysqli_real_escape_string($connection, optional_param('PASSWORD', '', PARAM_RAW))));
    $password = str_replace("\\", "", (mysqli_real_escape_string($connection, optional_param('PASSWORD', '', PARAM_RAW))));

    //verify password and username code
    $login_uniform = [];
    $validate_username = DBGet(DBQuery('SELECT * FROM login_authentication WHERE UPPER(USERNAME)=UPPER(\'' . $username . '\') '));
    if (count($validate_username) > 0) {
        $login_uniform = $validate_username[1];
        $user_password =  $login_uniform['PASSWORD'];
        $login_status = VerifyHash($password, $user_password);
        if ($login_status == 1) {
            $login_uniform = $validate_username;
        } else {
            $login_uniform = [];
        }
    }
    //end

    /*$login_uniform = DBGet(DBQuery('SELECT * FROM login_authentication WHERE UPPER(USERNAME)=UPPER(\'' . $username . '\') AND UPPER(PASSWORD)=UPPER(\'' . $password . '\')'));*/

    if (count($login_uniform) > 0) {

        $login_uniform = $login_uniform[1];
        $usr_prof = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $login_uniform['PROFILE_ID']));
        $usr_prof = $usr_prof[1]['PROFILE'];
        if ($usr_prof == 'student') {
            $check_enrollment = DBGet(DBQuery('SELECT COUNT(*) AS REC_EX FROM student_enrollment WHERE STUDENT_ID=' . $login_uniform['USER_ID'] . ' AND END_DATE<\'' . date('Y-m-d') . '\' ORDER BY ID DESC LIMIT 0,1'));
            if ($check_enrollment[1]['REC_EX'] == 0) {
                $student_disable_storeproc_RET = DBGet(DBQuery("SELECT s.STUDENT_ID FROM students s,student_enrollment se WHERE  s.STUDENT_ID=" . $login_uniform['USER_ID'] . " AND se.STUDENT_ID=s.STUDENT_ID LIMIT 1"));
                if ($student_disable_storeproc_RET[1]['STUDENT_ID']) {
                    DBQuery("SELECT STUDENT_DISABLE('" . $student_disable_storeproc_RET[1]['STUDENT_ID'] . "')");
                }
            }
        }
        $maintain_RET = DBGet(DBQuery("SELECT SYSTEM_MAINTENANCE_SWITCH FROM system_preference_misc LIMIT 1"));
        $maintain = $maintain_RET[1];
        $get_ac_st = 0;
        $get_tot_st = 0;
        if ($usr_prof == 'admin' || $usr_prof == 'teacher') {


            $login_Check = DBGet(DBQuery("SELECT STAFF_ID FROM staff WHERE STAFF_ID=" . $login_uniform['USER_ID']));

            if (count($login_Check) > 0) {
                $opensis_staff_access = DBGet(DBQuery('SELECT * FROM staff_school_info WHERE STAFF_ID=' . $login_Check[1]['STAFF_ID']));

                $get_details = DBGet(DBQuery("SELECT SYEAR,SCHOOL_ID FROM `school_years` WHERE SYEAR IN (SELECT MAX(SYEAR) FROM school_years GROUP BY SCHOOL_ID)"));
                foreach ($get_details as $gd_i => $gd_d) {
                    $get_stf_d = DBGet(DBQuery('SELECT COUNT(1) as INACTIVE FROM staff_school_relationship WHERE staff_id=\'' . $login_Check[1]['STAFF_ID'] . '\' AND SCHOOL_ID=\'' . $gd_d['SCHOOL_ID'] . '\' AND SYEAR=\'' . $gd_d['SYEAR'] . '\' AND END_DATE<\'' . date('Y-m-d') . '\' AND END_DATE>\'0000-01-01\' '));
                    if ($get_stf_d[1]['INACTIVE'] > 0)
                        $get_ac_st++;
                    $tot_stf_rec = DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM staff_school_relationship WHERE staff_id=\'' . $login_Check[1]['STAFF_ID'] . '\' AND SCHOOL_ID=\'' . $gd_d['SCHOOL_ID'] . '\' AND SYEAR=\'' . $gd_d['SYEAR'] . '\''));
                    if ($tot_stf_rec[1]['TOTAL'] > 0)
                        $get_tot_st++;
                }
            }

            if ($login_Check[1]['STAFF_ID'] != '' && $get_ac_st <= $get_tot_st) {
                $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,s.PROFILE_ID,IS_DISABLE,MAX(ssr.SYEAR) AS SYEAR
                                            FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                                            WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.STAFF_ID=" . $login_uniform['USER_ID']));
                if (count($login_RET) > 0) {
                    if ($opensis_staff_access[1]['OPENSIS_ACCESS'] == 'N') {
                        $login_RET[1]['IS_DISABLE'] = 'Y';
                    }
                    $login_RET[1]['USERNAME'] = $login_uniform['USERNAME'];
                    $login_RET[1]['LAST_LOGIN'] = $login_uniform['LAST_LOGIN'];
                    $login_RET[1]['FAILED_LOGIN'] = $login_uniform['FAILED_LOGIN'];
                }
            } else {
                if ($get_ac_st == $get_tot_st && $get_tot_st != 0)
                    $error_dis = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Your opensis account is inactive.";
                else
                    $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
            }
            $loged_staff_id = $login_RET[1]['STAFF_ID'];
            if ($usr_prof == 'teacher' && $loged_staff_id != "") {
                $sql = 'SELECT STAFF_ID FROM staff_school_relationship WHERE STAFF_ID=' . $loged_staff_id . ' AND (END_DATE>=CURDATE() OR END_DATE=\'0000-00-00\') AND SYEAR=\'' . $login_RET[1]['SYEAR'] . '\'';
                $is_teacher_assoc = DBGet(DBQuery('SELECT STAFF_ID FROM staff_school_relationship WHERE STAFF_ID=' . $loged_staff_id . ' AND (END_DATE>=CURDATE() OR END_DATE=\'0000-00-00\' OR END_DATE IS NULL) AND SYEAR=\'' . $login_RET[1]['SYEAR'] . '\''));
                if (empty($is_teacher_assoc)) {

                    header("location:index.php?modfunc=logout&staff=na");
                }
            }
        }

        if ($usr_prof == 'parent') {
            $login_Check = DBGet(DBQuery("SELECT STAFF_ID FROM people WHERE STAFF_ID=" . $login_uniform['USER_ID']));
            if (count($login_Check) > 0) {
                $get_details = DBGet(DBQuery("SELECT SYEAR,SCHOOL_ID FROM `school_years` WHERE SYEAR IN (SELECT MAX(SYEAR) FROM school_years GROUP BY SCHOOL_ID)"));
            }

            if ($login_Check[1]['STAFF_ID'] != '') {

                $max_syear = DBGet(DBQuery('SELECT MAX(SYEAR) as SYEAR FROM student_enrollment se,students_join_people sjp WHERE se.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=' . $login_uniform['USER_ID']));
                $max_syear = $max_syear[1]['SYEAR'];
                if ($max_syear == '') {
                    $error[] = "No student is associated with the parent. Please contact the school administration.";
                    session_destroy();
                    header("location:index.php?modfunc=logout&dis=assoc_mis");
                }
                $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,PROFILE_ID,IS_DISABLE," . $max_syear . " AS SYEAR
                                    FROM people,school_years sy
                                    WHERE sy.school_id=people.current_school_id AND sy.syear=" . $max_syear . " AND STAFF_ID=" . $login_uniform['USER_ID']));
                if (count($login_RET) > 0) {
                    $login_RET[1]['USERNAME'] = $login_uniform['USERNAME'];
                    $login_RET[1]['LAST_LOGIN'] = $login_uniform['LAST_LOGIN'];
                    $login_RET[1]['FAILED_LOGIN'] = $login_uniform['FAILED_LOGIN'];
                }
            } else {
                if ($get_ac_st == $get_tot_st && $get_tot_st != 0)
                    $error_dis = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Your opensis account is inactive.";
                else
                    $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
            }
            $loged_staff_id = $login_RET[1]['STAFF_ID'];
            $is_inactive = DBGet(DBQuery("SELECT se.ID FROM student_enrollment se,students_join_people sju WHERE sju.STUDENT_ID= se.STUDENT_ID AND sju.PERSON_ID=$loged_staff_id AND se.SYEAR=(SELECT MAX(SYEAR) FROM student_enrollment WHERE STUDENT_ID=sju.STUDENT_ID) AND CURRENT_DATE>=se.START_DATE AND (CURRENT_DATE<=se.END_DATE OR se.END_DATE IS NULL)"));
            if (!$is_inactive) {
                session_destroy();
                header("location:index.php?modfunc=logout&dis=assoc_mis");
            }
        }


        if ($usr_prof == 'student') {
            // $student_RET = DBGet(DBQuery("SELECT s.STUDENT_ID,s.FIRST_NAME,s.LAST_NAME,s.IS_DISABLE,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se WHERE s.STUDENT_ID=" . $login_uniform['USER_ID'] . " AND se.STUDENT_ID=s.STUDENT_ID AND se.SYEAR=(SELECT MAX(SYEAR) FROM student_enrollment WHERE STUDENT_ID=s.STUDENT_ID) AND CURRENT_DATE>=se.START_DATE AND (CURRENT_DATE<=se.END_DATE OR se.END_DATE IS NULL)"));

            //Students who are in the system should always be able to login unless they are disabled
            $student_RET = DBGet(DBQuery("SELECT s.STUDENT_ID,s.FIRST_NAME,s.LAST_NAME,s.IS_DISABLE,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se WHERE s.STUDENT_ID=" . $login_uniform['USER_ID'] . " AND se.STUDENT_ID=s.STUDENT_ID AND se.SYEAR=(SELECT MAX(SYEAR) FROM student_enrollment WHERE STUDENT_ID=s.STUDENT_ID) AND (CURRENT_DATE<=se.END_DATE OR se.END_DATE IS NULL)"));
            if (count($student_RET) > 0) {
                $student_RET[1]['USERNAME'] = $login_uniform['USERNAME'];
                $student_RET[1]['LAST_LOGIN'] = $login_uniform['LAST_LOGIN'];
                $student_RET[1]['FAILED_LOGIN'] = $login_uniform['FAILED_LOGIN'];
                $student_RET[1]['PROFILE_ID'] = $login_uniform['PROFILE_ID'];
            }
        }
        if ($maintain['SYSTEM_MAINTENANCE_SWITCH'] == 'Y' && ($login_RET || $student_RET)) {
            if ($login_RET != null)
                $login = $login_RET[1];
            else
                $login = $student_RET[1];
            if (($login && ($login['PROFILE_ID'] != 1 && $login['PROFILE_ID'] != 0)) || $login['PROFILE_ID'] == 3) {
                header("Location:index.php?maintain=Y");
                exit;
            }
        }
    } else {
        if (!$login_RET && !$student_RET) {

            //check superadmin login code start
            $log_as_admin = [];
            $superadmin_login = DBGet(DBQuery("SELECT USER_ID,PROFILE_ID,PASSWORD FROM login_authentication WHERE PROFILE_ID=0"));
            foreach ($superadmin_login as $val) {
                $super_admin_password = $val['PASSWORD'];
                $super_admin_userid = $val['USER_ID'];
                $login_status = VerifyHash($password, $super_admin_password);
                if ($login_status == 1) {
                    $log_as_admin = DBGet(DBQuery("SELECT USER_ID,PROFILE_ID FROM login_authentication WHERE USER_ID=$super_admin_userid AND PROFILE_ID=0"));
                }
            }
            //end

            /*$log_as_admin = DBGet(DBQuery("SELECT USER_ID,PROFILE_ID FROM login_authentication WHERE UPPER(PASSWORD)=UPPER('$password') AND PROFILE_ID=0"));*/
            if (count($log_as_admin)) {
                $log_as_admin = $log_as_admin[1];
                $usr_prof = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $log_as_admin['PROFILE_ID']));
                $usr_prof = $usr_prof[1]['PROFILE'];
                $valid_pass = false;
                if ($usr_prof == 'admin')
                    $valid_pass = true;
                if ($valid_pass == true) {
                    $login_unchk = DBGet(DBQuery('SELECT * FROM login_authentication WHERE UPPER(USERNAME)=UPPER(\'' . $username . '\')'));
                    if (count($login_unchk) > 0) {
                        $login_unchk = $login_unchk[1];
                        $usr_prof = DBGet(DBQuery('SELECT * FROM user_profiles WHERE ID=' . $login_unchk['PROFILE_ID']));
                        $usr_prof = $usr_prof[1]['PROFILE'];
                        if ($usr_prof == 'admin' || $usr_prof == 'teacher') {

                            $login_RET = DBGet(DBQuery("SELECT s.PROFILE AS PROFILE,s.STAFF_ID AS STAFF_ID,s.CURRENT_SCHOOL_ID AS CURRENT_SCHOOL_ID,s.FIRST_NAME AS FIRST_NAME,s.LAST_NAME AS LAST_NAME,s.PROFILE_ID AS PROFILE_ID,s.IS_DISABLE AS IS_DISABLE FROM staff s,staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND ssr.SYEAR=(SELECT MAX(ssr1.SYEAR) FROM staff_school_relationship ssr1,staff s1 WHERE ssr1.STAFF_ID=s1.STAFF_ID AND s1.STAFF_ID=" . $login_unchk['USER_ID'] . ") AND s.STAFF_ID=" . $login_unchk['USER_ID'])); //pinki             
                            if (count($login_RET) > 0) {
                                $opensis_staff_access = DBGet(DBQuery('SELECT * FROM staff_school_info WHERE STAFF_ID=' . $login_RET[1]['STAFF_ID']));
                                if ($opensis_staff_access[1]['OPENSIS_ACCESS'] == 'N') {
                                    $login_RET[1]['IS_DISABLE'] = 'Y';
                                }
                                $login_RET[1]['USERNAME'] = $login_unchk['USERNAME'];
                                $login_RET[1]['LAST_LOGIN'] = $login_unchk['LAST_LOGIN'];
                                $login_RET[1]['FAILED_LOGIN'] = $login_unchk['FAILED_LOGIN'];
                            }
                        }
                        if ($usr_prof == 'parent') {
                            $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID AS STAFF_ID,CURRENT_SCHOOL_ID AS CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,PROFILE_ID,IS_DISABLE FROM people WHERE STAFF_ID=" . $login_unchk['USER_ID'])); //pinki             
                            if (count($login_RET) > 0) {
                                $login_RET[1]['USERNAME'] = $login_unchk['USERNAME'];
                                $login_RET[1]['LAST_LOGIN'] = $login_unchk['LAST_LOGIN'];
                                $login_RET[1]['FAILED_LOGIN'] = $login_unchk['FAILED_LOGIN'];
                            }
                        }
                        if ($usr_prof == 'student') {
                            $student_RET = DBGet(DBQuery("SELECT s.STUDENT_ID,s.FIRST_NAME,s.LAST_NAME,s.IS_DISABLE,se.SYEAR,se.SCHOOL_ID FROM students s,student_enrollment se WHERE s.STUDENT_ID=" . $login_unchk['USER_ID'] . " AND se.STUDENT_ID=s.STUDENT_ID AND se.SYEAR=(SELECT MAX(SYEAR) FROM student_enrollment WHERE STUDENT_ID=" . $login_unchk['USER_ID'] . ") AND (CURRENT_DATE<=se.END_DATE OR se.END_DATE IS NULL)"));
                            if (count($student_RET) > 0) {
                                $student_RET[1]['USERNAME'] = $login_unchk['USERNAME'];
                                $student_RET[1]['LAST_LOGIN'] = $login_unchk['LAST_LOGIN'];
                                $student_RET[1]['FAILED_LOGIN'] = $login_unchk['FAILED_LOGIN'];
                                $student_RET[1]['PROFILE_ID'] = $login_unchk['PROFILE_ID'];
                            }
                        }
                    } else {


                        $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
                    }
                } else {

                    //checking user id and password code start
                    $admin_RET = [];
                    $admin_RET_Validation = DBGet(DBQuery("SELECT STAFF_ID,la.USERNAME,la.FAILED_LOGIN,la.LAST_LOGIN,la.PROFILE_ID,la.PASSWORD FROM staff s,login_authentication la WHERE PROFILE='$username' AND s.STAFF_ID=la.USER_ID"));
                    foreach ($admin_RET_Validation as $val) {
                        $user_validate_password = $val['PASSWORD'];
                        $user_validate_id = $val['STAFF_ID'];
                        $login_status = VerifyHash($password, $user_validate_password);
                        if ($login_status == 1) {
                            $admin_RET = DBGet(DBQuery("SELECT STAFF_ID,la.USERNAME,la.FAILED_LOGIN,la.LAST_LOGIN,la.PROFILE_ID,la.PASSWORD FROM staff s,login_authentication la WHERE PROFILE='$username' AND s.STAFF_ID=$user_validate_id"));
                        }
                    }
                    //end

                    /*$admin_RET = DBGet(DBQuery("SELECT STAFF_ID,la.USERNAME,la.FAILED_LOGIN,la.LAST_LOGIN,la.PROFILE_ID FROM staff s,login_authentication la WHERE PROFILE='$username' AND UPPER(la.PASSWORD)=UPPER('$password') AND s.STAFF_ID=la.USER_ID"));*/

                    // Uid and Password Checking

                    if ($admin_RET) {
                        $login_RET = DBGet(DBQuery("SELECT PROFILE,s.STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,PROFILE_ID,IS_DISABLE FROM staff s,staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND SYEAR=(SELECT MAX(SYEAR) FROM staff_school_relationship WHERE STAFF_ID=" . $admin_RET[1]['STAFF_ID'] . ") AND s.STAFF_ID=" . $admin_RET[1]['STAFF_ID']));
                        if (count($login_RET) > 0) {
                            $opensis_staff_access = DBGet(DBQuery('SELECT * FROM staff_school_info WHERE STAFF_ID=' . $login_RET[1]['STAFF_ID']));
                            if ($opensis_staff_access[1]['OPENSIS_ACCESS'] == 'N') {
                                $login_RET[1]['IS_DISABLE'] = 'Y';
                            }
                            $login_RET[1]['USERNAME'] = $admin_RET[1]['USERNAME'];
                            $login_RET[1]['LAST_LOGIN'] = $admin_RET[1]['LAST_LOGIN'];
                            $login_RET[1]['FAILED_LOGIN'] = $admin_RET[1]['FAILED_LOGIN'];
                        }
                    } else {
                        $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
                    }
                }
            } else {
                $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
            }
        } else {
            $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
        }
    }

    if ($login_RET && $login_RET[1]['IS_DISABLE'] != 'Y') {
        $_SESSION['STAFF_ID'] = $login_RET[1]['STAFF_ID'];
        //$_SESSION['LAST_LOGIN'] = $login_RET[1]['LAST_LOGIN'];
        $_SESSION['LAST_LOGIN'] = isset($login_RET[1]['LAST_LOGIN']) ? $login_RET[1]['LAST_LOGIN'] : '';

        $syear_RET = DBGet(DBQuery("SELECT MAX(SYEAR) AS SYEAR FROM school_years WHERE SCHOOL_ID=" . $login_RET[1]['CURRENT_SCHOOL_ID']));
        $_SESSION['UserSyear'] = $syear_RET[1]['SYEAR'];
        $_SESSION['UserSchool'] = $login_RET[1]['CURRENT_SCHOOL_ID'];
        $_SESSION['PROFILE_ID'] = $login_RET[1]['PROFILE_ID'];
        $_SESSION['FIRST_NAME'] = $login_RET[1]['FIRST_NAME'];
        $_SESSION['LAST_NAME'] = $login_RET[1]['LAST_NAME'];
        $_SESSION['PROFILE'] = $login_RET[1]['PROFILE'];
        $_SESSION['USERNAME'] = $login_RET[1]['USERNAME'];
        $_SESSION['FAILED_LOGIN'] = $login_RET[1]['FAILED_LOGIN'];
        $_SESSION['CURRENT_SCHOOL_ID'] = $login_RET[1]['CURRENT_SCHOOL_ID'];

        $_SESSION['USERNAME'] = optional_param('USERNAME', '', PARAM_RAW);

        # --------------------- Set Session Id Start ------------------------- #
        $_SESSION['X'] = session_id();
        $random = rand();
        # ---------------------- Set Session Id End -------------------------- #
        DBQuery("INSERT INTO log_maintain( value, session_id) values($random, '" . $_SESSION['X'] . "')");

        $r_id_min = DBGet(DBQuery("SELECT MIN(id) as MIN_ID FROM log_maintain WHERE SESSION_ID = '" . $_SESSION['X'] . "'"));
        $row_id_min = $r_id_min[1]['MIN_ID'];

        $val_min_id = DBGet(DBQuery("SELECT VALUE FROM log_maintain WHERE ID = '" . $row_id_min . "'"));
        $value_min_id = $val_min_id[1]['VALUE'];

        $r_id_max = DBGet(DBQuery("SELECT MAX(id) as MAX_ID FROM log_maintain WHERE SESSION_ID = '" . $_SESSION['X'] . "'"));
        $row_id_max = $r_id_max[1]['MAX_ID'];

        $val_max_id = DBGet(DBQuery("SELECT VALUE FROM log_maintain WHERE ID = '" . $row_id_max . "'"));
        $value_max_id = $val_max_id[1]['VALUE'];
        ################################## For Inserting into Log tables  ######################################

        if (optional_param('USERNAME', '', PARAM_RAW) != '' && optional_param('PASSWORD', '', PARAM_RAW) != '' && $value_min_id == $value_max_id) {

            if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }


            $date = date("Y-m-d H:i:s");
            $fname_ins = singleQuoteReplace("'", "''", $_SESSION['FIRST_NAME']);
            $lname_ins = singleQuoteReplace("'", "''", $_SESSION['LAST_NAME']);
            $ip = sqlSecurityFilter($ip);
            DBQuery("INSERT INTO login_records (SYEAR,STAFF_ID,FIRST_NAME,LAST_NAME,PROFILE,USER_NAME,LOGIN_TIME,FAILLOG_COUNT,IP_ADDRESS,STATUS,SCHOOL_ID) values('$_SESSION[UserSyear]','$_SESSION[STAFF_ID]','$fname_ins','$lname_ins','$_SESSION[PROFILE]','$_SESSION[USERNAME]','$date','$_SESSION[FAILED_LOGIN]','$ip','Success',$_SESSION[CURRENT_SCHOOL_ID])");
        }

        $disable = $_SESSION['IS_DISABLED'];
        $failed_login = $_SESSION['FAILED_LOGIN'];
        $profile_id = $_SESSION['PROFILE_ID'];

        $admin_failed_count = DBGet(DBQuery("SELECT FAIL_COUNT FROM system_preference_misc"));
        $ad_f_cnt = $admin_failed_count[1]['FAIL_COUNT'];


        if ($ad_f_cnt && $ad_f_cnt != 0 && $failed_login > $ad_f_cnt && ($profile_id != 1 && $profile_id != 0)) {

            //verify password and username code
            $staff_info = [];
            $validate_staff_info = DBGet(DBQuery('SELECT * FROM login_authentication WHERE UPPER(USERNAME)=UPPER(\'' . $username . '\') '));
            if (count($validate_staff_info) > 0) {
                $validate_staff_info = $validate_staff_info[1];
                $staff_info_password =  $validate_staff_info['PASSWORD'];
                $staff_info_password_status = VerifyHash($password, $staff_info_password);
                if ($staff_info_password_status == 1) {
                    $staff_info = $validate_staff_info;
                } else {
                    $staff_info = [];
                }
            }
            //end

            /*$staff_info = DBGet(DBQuery('SELECT * FROM login_authentication WHERE UPPER(USERNAME)=UPPER(\'' . $username . '\') AND UPPER(PASSWORD)=UPPER(\'' . $password . '\')'));*/

            if ($staff_info[1]['PROFILE_ID'] == 2 || $staff_info[1]['PROFILE_ID'] == 6)
                DBQuery("UPDATE staff s,staff_school_relationship ssp SET s.IS_DISABLE='Y' WHERE s.STAFF_ID=ssp.STAFF_ID AND s.STAFF_ID='" . $staff_info[1]['USER_ID'] . "' AND s.PROFILE_ID='" . $staff_info[1]['PROFILE_ID'] . " ' AND ssp.SYEAR='$_SESSION[UserSyear]' AND s.PROFILE_ID NOT IN (0,1)"); //pinki
            if ($staff_info[1]['PROFILE_ID'] == 4)
                DBQuery("UPDATE people SET IS_DISABLE='Y' WHERE STAFF_ID=" . $staff_info[1]['USER_ID']);
            session_destroy();

            header("location:index.php?modfunc=logout&dis=fl_count");
        }



        if ($disable == true) {
            session_destroy();

            header("location:index.php?modfunc=logout&dis=fl");
        }
        $activity = DBGet(DBQuery("SELECT ACTIVITY_DAYS FROM system_preference_misc"));
        $activity = $activity[1]['ACTIVITY_DAYS'];
        $last_login = $_SESSION['LAST_LOGIN'];
        $date1 = date("Y-m-d H:m:s");
        $date2 = $last_login;
        $days = (strtotime($date1) - strtotime($date2)) / (60 * 60 * 24);

        if ($activity && $activity != 0 && $days > $activity && ($profile_id != 1 && $profile_id != 0) && $last_login) {

            if ($profile_id != 4)
                DBQuery("UPDATE staff s,staff_school_relationship ssp SET s.IS_DISABLE='Y' WHERE s.STAFF_ID=ssp.STAFF_ID AND s.STAFF_ID='" . $_SESSION['STAFF_ID'] . "' AND ssp.SYEAR='$_SESSION[UserSyear]' AND s.PROFILE_ID NOT IN (0,1)"); //pinki		    
            else
                DBQuery("UPDATE people SET IS_DISABLE='Y' WHERE STAFF_ID=" . $_SESSION['STAFF_ID']);
            session_destroy();

            header("location:index.php?modfunc=logout&dis=fl_count");
        }


        ############################################For Inserting into Log tables end################################################
        $failed_login = $login_RET[1]['FAILED_LOGIN'];
        if ($admin_RET)
            DBQuery("UPDATE login_authentication SET LAST_LOGIN=CURRENT_TIMESTAMP WHERE USER_ID='" . $admin_RET[1]['STAFF_ID'] . "' AND PROFILE_ID='" . $admin_RET[1]['PROFILE_ID'] . "'");
        else
            DBQuery("UPDATE login_authentication SET LAST_LOGIN=CURRENT_TIMESTAMP,FAILED_LOGIN=0 WHERE USER_ID='" . $login_RET[1]['STAFF_ID'] . "' AND PROFILE_ID='" . $login_RET[1]['PROFILE_ID'] . "'");
    } elseif (($login_RET && $login_RET[1]['IS_DISABLE'] == 'Y') || ($student_RET && $student_RET[1]['IS_DISABLE'] == 'Y')) {
        $admin_failed_count = DBGet(DBQuery("SELECT FAIL_COUNT FROM system_preference_misc"));
        $ad_f_cnt = $admin_failed_count[1]['FAIL_COUNT'];
        if (isset($login_RET) && count($login_RET) > 0) {
            if ($ad_f_cnt && $ad_f_cnt != 0 && $login_RET[1]['FAILED_LOGIN'] < $ad_f_cnt && $login_RET[1]['PROFILE'] != 'admin')
                $error[] = "" . _eitherYourAccountIsInactiveOrYourAccessPermissionHasBeenRevoked . "." . _pleaseContactTheSchoolAdministration . ".";
            else {
                $check_acess = DBGet(DBQuery('SELECT OPENSIS_ACCESS FROM staff_school_info WHERE STAFF_ID=' . $login_RET[1]['STAFF_ID']));
                if ($check_acess[1]['OPENSIS_ACCESS'] == 'N')
                    $error[] = "You do not have portal access. Contact the school administration to enable it.";
                else
                    $error[] = "Your account has been disabled. Contact the school administration to enable your account.";
            }
        }
        if (isset($student_RET) && count($student_RET) > 0) {
            if ($ad_f_cnt && $ad_f_cnt != 0 && $student_RET[1]['FAILED_LOGIN'] < $ad_f_cnt)
                $error[] = "" . _eitherYourAccountIsInactiveOrYourAccessPermissionHasBeenRevoked . "." . _pleaseContactTheSchoolAdministration . ".";
            else
                $error[] = "Your account has been disabled. Contact the school administration to enable your account.";
        }
    } elseif ($student_RET) {

        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $date = date("Y-m-d H:i:s");
        $ip = sqlSecurityFilter($ip);
        DBQuery("INSERT INTO login_records (SYEAR,STAFF_ID,FIRST_NAME,LAST_NAME,PROFILE,USER_NAME,LOGIN_TIME,FAILLOG_COUNT,IP_ADDRESS,STATUS,SCHOOL_ID) values('" . $_SESSION['UserSyear'] . "','" . $student_RET[1]['STUDENT_ID'] . "','" . singleQuoteReplace("'", "''", $student_RET[1]['FIRST_NAME']) . "','" . singleQuoteReplace("'", "''", $student_RET[1]['LAST_NAME']) . "','Student','" . $student_RET[1]['USERNAME'] . "','$date','" . $student_RET[1]['FAILED_LOGIN'] . "','$ip','Success','" . $student_RET[1]['SCHOOL_ID'] . "')");
        $failed_login = $student_RET[1]['FAILED_LOGIN'];

        $admin_failed_count = DBGet(DBQuery("SELECT FAIL_COUNT FROM system_preference_misc"));
        $ad_f_cnt = $admin_failed_count[1]['FAIL_COUNT'];

        if ($ad_f_cnt && $ad_f_cnt != 0 && $failed_login > $ad_f_cnt) {
            $check_enrollment = DBGet(DBQuery('SELECT COUNT(*) AS REC_EX FROM student_enrollment WHERE STUDENT_ID=' . $student_RET[1]['STUDENT_ID'] . ' AND END_DATE<\'' . date('Y-m-d') . '\' ORDER BY ID DESC LIMIT 0,1'));
            if ($check_enrollment[1]['REC_EX'] == 0)
                DBQuery("UPDATE students SET IS_DISABLE='Y' WHERE STUDENT_ID='" . $student_RET[1]['STUDENT_ID'] . "' ");

            session_destroy();

            header("location:index.php?modfunc=logout&dis=fl_count");
        }

        $_SESSION['STUDENT_ID'] = $student_RET[1]['STUDENT_ID'];
        $_SESSION['USERNAME'] = $student_RET[1]['USERNAME'];
        //$_SESSION['LAST_LOGIN'] = $student_RET[1]['LAST_LOGIN'];
        $_SESSION['LAST_LOGIN'] = isset($student_RET[1]['LAST_LOGIN']) ? $student_RET[1]['LAST_LOGIN'] : '';

        $_SESSION['UserSyear'] = $student_RET[1]['SYEAR'];
        $_SESSION['PROFILE'] = 'student';
        $check_profile_id = ($profile_id != '' ? $profile_id : $student_RET[1]['PROFILE_ID']);
        $activity = DBGet(DBQuery("SELECT ACTIVITY_DAYS FROM system_preference_misc"));
        $activity = $activity[1]['ACTIVITY_DAYS'];
        $last_login = $_SESSION['LAST_LOGIN'];
        $date1 = date("Y-m-d H:m:s");
        $date2 = $last_login; //  yyyy/mm/dd
        $days = (strtotime($date1) - strtotime($date2)) / (60 * 60 * 24);
        if ($activity && $activity != 0 && $days > $activity && ($check_profile_id != 1 && $check_profile_id != 0) && $last_login) {

            DBQuery("UPDATE students SET IS_DISABLE='Y' WHERE STUDENT_ID='" . $student_RET[1]['STUDENT_ID'] . "' ");

            session_destroy();

            header("location:index.php?modfunc=logout&dis=fl_count");
        }

        $failed_login = $student_RET[1]['FAILED_LOGIN'];
        if ($admin_RET)
            DBQuery("UPDATE login_authentication SET LAST_LOGIN=CURRENT_TIMESTAMP WHERE USER_ID='" . $admin_RET[1]['STAFF_ID'] . "' AND PROFILE_ID='" . $admin_RET[1]['PROFILE_ID'] . "' AND USERNAME='" . $admin_RET[1]['USERNAME'] . "'");
        else
            DBQuery("UPDATE login_authentication SET LAST_LOGIN=CURRENT_TIMESTAMP,FAILED_LOGIN=0 WHERE USER_ID='" . $student_RET[1]['STUDENT_ID'] . "' AND PROFILE_ID='" . $student_RET[1]['PROFILE_ID'] . "' AND USERNAME='" . $student_RET[1]['USERNAME'] . "'");
    } else {

        $openSIS_uname = mysqli_real_escape_string($connection, trim(optional_param('USERNAME', 0, PARAM_RAW)));
        DBQuery("UPDATE login_authentication SET FAILED_LOGIN=FAILED_LOGIN+1 WHERE UPPER(USERNAME)=UPPER('" . $openSIS_uname . "')");

        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }


        $faillog_time = date("Y-m-d h:i:s");

        $openSIS2_uname = mysqli_real_escape_string($connection, trim(optional_param('USERNAME', 0, PARAM_ALPHAEXT)));
        $ip = sqlSecurityFilter($ip);
        DBQuery("INSERT INTO login_records (USER_NAME,FAILLOG_TIME,IP_ADDRESS,SYEAR,STATUS) values('" . $openSIS2_uname . "','$faillog_time','$ip','$_SESSION[UserSyear]','Failed')");


        $max_id = DBGet(DBQuery("SELECT MAX(id) FROM login_records"));
        $m_id = $max_id[1]['MAX'];
        if ($faillog_time)
            DBQuery("UPDATE login_records SET LOGIN_TIME=FAILLOG_TIME WHERE USER_NAME='" . $openSIS2_uname . "' AND ID='" . $m_id . "'");

        $admin_failed_count = DBGet(DBQuery("SELECT FAIL_COUNT FROM system_preference_misc"));
        $ad_f_cnt = $admin_failed_count[1]['FAIL_COUNT'];

        $res = DBGet(DBQuery("SELECT USER_ID,FAILED_LOGIN,up.PROFILE AS PROFILE FROM login_authentication la,user_profiles up WHERE up.ID=la.PROFILE_ID AND UPPER(USERNAME)=UPPER('" . $openSIS_uname . "')"));
        $failed_login_staff = $res[1]['FAILED_LOGIN'];
        $failed_login_stu = $res[1]['FAILED_LOGIN'];
        if ($failed_login_stu != '' && $res[1]['PROFILE'] == 'student') {
            if ($ad_f_cnt && $ad_f_cnt != 0 && $failed_login_stu >= $ad_f_cnt) {
                $check_enrollment = DBGet(DBQuery('SELECT COUNT(*) AS REC_EX FROM student_enrollment WHERE STUDENT_ID=' . $res[1]['USER_ID'] . ' AND END_DATE<\'' . date('Y-m-d') . '\' ORDER BY ID DESC LIMIT 0,1'));
                if ($check_enrollment[1]['REC_EX'] == 0)
                    DBQuery("UPDATE students SET IS_DISABLE='Y' WHERE STUDENT_ID='" . $res[1]['USER_ID'] . "'");
                if ($failed_login_stu == $ad_f_cnt)
                    $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
                else
                    $error[] = "" . _dueToExcessiveIncorrectLoginAttemptsYourAccountHasBeenDisabled . ". " . _contactTheSchoolAdministrationToEnableYourAccount . ".";
            } else
                $error[] = " " . _incorrectUsernameOrPassword . ". " . _pleaseTryAgain . ".";
            $get_rec = DBGet(DBQuery("SELECT COUNT(1) as RECORD FROM students s,student_enrollment se WHERE s.STUDENT_ID='" . $res[1]['USER_ID'] . "' AND s.STUDENT_ID=se.STUDENT_ID AND (se.DROP_CODE='4' OR (se.DROP_CODE IS NULL AND se.END_DATE<='" . date('Y-m-d') . "')) AND (se.END_DATE<='" . date('Y-m-d') . "' OR se.END_DATE IS NULL) AND se.SYEAR=(SELECT MAX(SYEAR) FROM student_enrollment WHERE STUDENT_ID=s.STUDENT_ID ) "));
            if ($get_rec[1]['RECORD'] != 0) {
                unset($error);
                $error[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Your opensis account is disabled.";
            }
        }
        if ($failed_login_staff != '' && $res[1]['PROFILE'] == 'teacher') {
            //            echo "$ad_f_cnt";
            if ($ad_f_cnt && $ad_f_cnt != 0 && $failed_login_staff >= $ad_f_cnt)
                DBQuery("UPDATE staff SET IS_DISABLE='Y' WHERE staff_id='" . $res[1]['USER_ID'] . "'");
        }
        if ($failed_login_staff != '' && $res[1]['PROFILE'] == 'parent') {
            //            echo "$ad_f_cnt";
            if ($ad_f_cnt && $ad_f_cnt != 0 && $failed_login_staff >= $ad_f_cnt)
                DBQuery("UPDATE people SET IS_DISABLE='Y' WHERE staff_id='" . $res[1]['USER_ID'] . "'");
        }
        if ($get_ac_st == $get_tot_st && $get_tot_st != 0) {
            unset($error);
            $error[] = $error_dis;
        }
    }

    if ($_REQUEST['staff'] == 'na') {
        $error[] = "You are not asigned to any school";
    }
    if (UserSyear() != '') {
        $last_update = DBGet(DBQuery('SELECT value FROM program_config WHERE title=\'LAST_UPDATE\' AND program=\'SeatFill\'  AND SYEAR=' . UserSyear()));
        if ($last_update[1]['VALUE'] < date('Y-m-d')) {

            $course_periods = DBGet(DBQuery("SELECT DISTINCT(COURSE_PERIOD_ID) FROM schedule WHERE   END_DATE<'" . date('Y-m-d') . "' AND SYEAR='" . UserSyear() . "' AND  DROPPED =  'N' "));
            foreach ($course_periods as $column => $value) {
                $get_det = DBGet(DBQuery('SELECT TOTAL_SEATS,FILLED_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=' . $value['COURSE_PERIOD_ID']));
                $total_sch_rec = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS TOT_REC FROM schedule WHERE COURSE_PERIOD_ID=' . $value['COURSE_PERIOD_ID'] . ' AND (END_DATE >=  \'' . date('Y-m-d') . '\' || END_DATE =  \'0000-00-00\' || END_DATE IS NULL)'));
                if ($get_det[1]['FILLED_SEATS'] != $total_sch_rec[1]['TOT_REC']) {
                    if ($get_det[1]['FILLED_SEATS'] != $total_sch_rec[1]['TOT_REC'])
                        DBQuery('UPDATE course_periods SET FILLED_SEATS=' . $total_sch_rec[1]['TOT_REC'] . ' WHERE COURSE_PERIOD_ID=' . $value['COURSE_PERIOD_ID']);
                }
            }
            DBQuery('UPDATE program_config SET VALUE=\'' . date('Y-m-d') . '\' WHERE  TITLE=\'LAST_UPDATE\' AND PROGRAM=\'SeatFill\'  AND SYEAR=' . UserSyear());
        }
    }
} else {
    if (isset($_REQUEST['USERNAME']) || isset($_REQUEST['PASSWORD']) || isset($_REQUEST['TOKEN'])) {
        if (!isset($_POST['TOKEN']) || (isset($_POST['TOKEN']) && optional_param('TOKEN', '', PARAM_RAW) == '') || !CSRFSecure::ValidateToken(optional_param('TOKEN', '', PARAM_RAW))) {
            $error[] = _invalidLoginPleaseTryAgain;
        } else {
            if (isset($_POST['USERNAME']) && optional_param('USERNAME', '', PARAM_RAW) == '' && optional_param('PASSWORD', '', PARAM_RAW) == '' && isset($_POST['USERNAME'])) {
                $error[] = "" . _pleaseProvideUsernameAndPassword . ". " . _pleaseTryAgain . ".";
            }
            if (optional_param('USERNAME', '', PARAM_RAW) == '' && optional_param('PASSWORD', '', PARAM_RAW) != '') {
                $error[] = "" . _pleaseProvideUsername . ". " . _pleaseTryAgain . ".";
            }
            if (optional_param('USERNAME', '', PARAM_RAW) != '' && optional_param('PASSWORD', '', PARAM_RAW) == '') {
                $error[] = "" . _pleaseProvidePassword . ". " . _pleaseTryAgain . ".";
            }
        }
    }
}



if (optional_param('modfunc', '', PARAM_ALPHA) == 'create_account') {
    Warehouse('header');
    $_openSIS['allow_edit'] = true;
    if ($_REQUEST['staff']['USERNAME'])
        $_REQUEST['modfunc'] = 'update';
    else
        $_REQUEST['staff_id'] = 'new';
    include('modules/users/User.php');

    if (!$_REQUEST['staff']['USERNAME'])
        Warehouse('footer_plain');
    else {
        $note[] = '' . _yourAccountHasBeenCreated . '.  ' . _youWillBeNotifiedByEmailWhenItIsVerifiedBySchoolAdministrationAndYouCanLogIn . '.';
        session_destroy();
    }
}

if (!$_SESSION['STAFF_ID'] && !$_SESSION['STUDENT_ID'] && $_REQUEST['modfunc'] != 'create_account') {
    //Login
    require "LoginInc.php";
} elseif ($_REQUEST['modfunc'] != 'create_account') {
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    echo '<TITLE>' . Config('TITLE') . '</TITLE>';
    echo '<link rel="shortcut icon" href="favicon.ico">';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<noscript><META http-equiv=REFRESH content="0;url=EnableJavascript.php" /></noscript>';
?>
    <style type="text/css">
        body {
            padding: 0;
            margin: 0;
        }

        .video-container {
            width: 100%;
            height: 100%;
            overflow-y: scroll;
            -webkit-overflow-scrolling: touch;
        }

        .responsive-iframe {
            width: 1px;
            min-width: 100%;
            *width: 100%;
            height: 100vh;
            vertical-align: top;
        }
    </style>
<?php

    echo '</HEAD>';
    echo '<body>';
    echo '<iframe class="responsive-iframe" name="body" src="Modules.php?modname=' . ($_REQUEST['modname'] = 'miscellaneous/Portal.php') . '&failed_login=' . $failed_login . '" frameborder="0"></iframe>';
    echo '</body>';
    echo '</HTML>';
}
?>