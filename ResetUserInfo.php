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

session_start();
include "functions/ParamLibFnc.php";
include "Data.php";
include "functions/DbGetFnc.php";
require_once "functions/PragRepFnc.php";
include "AuthCryp.php";
include 'functions/SqlSecurityFnc.php';
include_once("functions/PasswordHashFnc.php");
include "functions/CSRFSecurityFnc.php";

function db_start() {
    global $DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName, $DatabasePort, $DatabaseType;

    switch ($DatabaseType) {
        case 'mysqli':
            $connection = new mysqli($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);
            break;
    }

    // Error code for both.
    if ($connection === false) {
        switch ($DatabaseType) {
            case 'mysqli':
                $errormessage = mysqli_error($connection);
                break;
        }
        db_show_error("", ""._couldNotConnectToDatabase.": $DatabaseServer", $errstring);
    }
    return $connection;
}


##### Connection help #####
$connection = mysqli_connect($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);

if (!$connection)
{
	die('Could Not Connect: ' . mysqli_error($connection) . mysqli_errno($connection));
}


// This function connects, and does the passed query, then returns a connection identifier.
// Not receiving the return == unusable search.
//		ie, $processable_results = DBQuery("select * from students");
function DBQuery($sql) {
    global $DatabaseType, $_openSIS, $connection;

    // $connection = db_start();

    switch ($DatabaseType) {
        case 'mysqli':
            $sql = str_replace('&amp;', "", $sql);
            $sql = str_replace('&quot', "", $sql);
            $sql = str_replace('&#039;', "", $sql);
            $sql = str_replace('&lt;', "", $sql);
            $sql = str_replace('&gt;', "", $sql);
            $sql = par_rep("/([,\(=])[\r\n\t ]*''/", '\\1NULL', $sql);
            if (preg_match_all("/'(\d\d-[A-Za-z]{3}-\d{2,4})'/", $sql, $matches)) {
                foreach ($matches[1] as $match) {
                    $dt = date('Y-m-d', strtotime($match));
                    $sql = par_rep("/'$match'/", "'$dt'", $sql);
                }
            }
            if (substr($sql, 0, 6) == "BEGIN;") {
                $array = explode(";", $sql);
                foreach ($array as $value) {
                    if ($value != "") {
                        $result = $connection->query($value);
                        if (!$result) {
                            $connection->query("ROLLBACK");
                            die(db_show_error($sql, _dbExecuteFailed, mysql_error()));
                        }
                    }
                }
            } else {
                $result = $connection->query($sql) or die(db_show_error($sql, _dbExecuteFailed, mysql_error()));
            }
            break;
    }
    return $result;
}

// return next row.
function db_fetch_row($result) {
    global $DatabaseType;

    switch ($DatabaseType) {
        case 'mysqli':
            $return = $result->fetch_assoc();
            if (is_array($return)) {
                foreach ($return as $key => $value) {
                    if (is_int($key))
                        unset($return[$key]);
                }
            }
            break;
    }
    return (is_array($return)) ? array_change_key_case($return, CASE_UPPER) : $return;
}

// returns code to go into SQL statement for accessing the next value of a sequence function db_seq_nextval($seqname)
function db_seq_nextval($seqname) {
    global $DatabaseType;

    if ($DatabaseType == 'mysqli')
        $seq = "fn_" . strtolower($seqname) . "()";

    return $seq;
}

function db_case($array) {
    global $DatabaseType;

    $counter = 0;
    if ($DatabaseType == 'mysqli') {
        $array_count = count($array);
        $string = " CASE WHEN $array[0] =";
        $counter++;
        $arr_count = count($array);
        for ($i = 1; $i < $arr_count; $i++) {
            $value = $array[$i];

            if ($value == "''" && substr($string, -1) == '=') {
                $value = ' IS NULL';
                $string = substr($string, 0, -1);
            }

            $string.="$value";
            if ($counter == ($array_count - 2) && $array_count % 2 == 0)
                $string.=" ELSE ";
            elseif ($counter == ($array_count - 1))
                $string.=" END ";
            elseif ($counter % 2 == 0)
                $string.=" WHEN $array[0]=";
            elseif ($counter % 2 == 1)
                $string.=" THEN ";

            $counter++;
        }
    }

    return $string;
}

function db_properties($table) {
    global $DatabaseType, $DatabaseUsername;

    switch ($DatabaseType) {
        case 'mysqli':
            $result = DBQuery("SHOW COLUMNS FROM $table");
            while ($row = db_fetch_row($result)) {
                $properties[strtoupper($row['FIELD'])]['TYPE'] = strtoupper($row['TYPE'], strpos($row['TYPE'], '('));
                if (!$pos = strpos($row['TYPE'], ','))
                    $pos = strpos($row['TYPE'], ')');
                else
                    $properties[strtoupper($row['FIELD'])]['SCALE'] = substr($row['TYPE'], $pos + 1);

                $properties[strtoupper($row['FIELD'])]['SIZE'] = substr($row['TYPE'], strpos($row['TYPE'], '(') + 1, $pos);

                if ($row['NULL'] != '')
                    $properties[strtoupper($row['FIELD'])]['NULL'] = "Y";
                else
                    $properties[strtoupper($row['FIELD'])]['NULL'] = "N";
            }
            break;
    }
    return $properties;
}

function db_show_error($sql, $failnote, $additional = '') {
    global $openSISTitle, $openSISVersion, $openSISNotifyAddress, $openSISMode;


    $tb = debug_backtrace();
    $error = $tb[1]['file'] . " at " . $tb[1]['line'];

    echo "
                    <TABLE CELLSPACING=10 BORDER=0>
                            <TD align=right><b>Date:</TD>
                            <TD><pre>" . date("m/d/Y h:i:s") . "</pre></TD>
                    </TR><TR>
                            <TD align=right><b>Failure Notice:</b></TD>
                            <TD><pre> $failnote </pre></TD>
                    </TR><TR>
                            <TD align=right><b>SQL:</b></TD>
                            <TD>$sql</TD>
                    </TR>
                    </TR><TR>
                            <TD align=right><b>Traceback:</b></TD>
                            <TD>$error</TD>
                    </TR>
                    </TR><TR>
                            <TD align=right><b>Additional Information:</b></TD>
                            <TD>$additional</TD>
                    </TR>
                    </TABLE>";

    echo "
		<TABLE CELLSPACING=10 BORDER=0>
			<TR><TD align=right><b>Date:</TD>
			<TD><pre>" . date("m/d/Y h:i:s") . "</pre></TD>
		</TR><TR>
			<TD align=right></TD>
			<TD>openSIS has encountered an error that could have resulted from any of the following:
			<br/>
			<ul>
			<li>Invalid data input</li>
			<li>Database SQL error</li>
			<li>Program error</li>
			</ul>
			
			Please take this screen shot and send it to your openSIS representative for debugging and resolution.
			</TD>
		</TR>
		
		</TABLE>";

    echo "<!-- SQL STATEMENT: \n\n $sql \n\n -->";

    if ($openSISNotifyAddress) {
        $message = "System: $openSISTitle \n";
        $message .= "Date: " . date("m/d/Y h:i:s") . "\n";
        $message .= "Page: " . $_SERVER['PHP_SELF'] . ' ' . ProgramTitle() . " \n\n";
        $message .= "Failure Notice:  $failnote \n";
        $message .= "Additional Info: $additional \n";
        $message .= "\n $sql \n";
        $message .= "Request Array: \n" . ShowVar($_REQUEST, 'Y', 'N');
        $message .= "\n\nSession Array: \n" . ShowVar($_SESSION, 'Y', 'N');
        mail($openSISNotifyAddress, 'openSIS Database Error', $message);
    }

    die();
}

$user_info = sqlSecurityFilter($_REQUEST['user_info']);
$uname = sqlSecurityFilter($_REQUEST['uname']);
$password_stn_id = sqlSecurityFilter($_REQUEST['password_stn_id']);
$password_stf_email = sqlSecurityFilter($_REQUEST['password_stf_email']);
$pass = sqlSecurityFilter($_REQUEST['pass']);
$username_stn_id = sqlSecurityFilter($_REQUEST['username_stn_id']);
$username_stf_email = sqlSecurityFilter($_REQUEST['username_stf_email']);

$log_msg = DBGet(DBQuery("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));
if ($_REQUEST['pass_type_form'] == 'password') {
    if ($_REQUEST['pass_user_type'] == 'pass_student') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['password_stn_id'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Student Id.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['uname'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Username.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['month_password_dob'] == '' || $_REQUEST['day_password_dob'] == '' || $_REQUEST['year_password_dob'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Birthday Properly.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }

        if ($_REQUEST['password_stn_id'] != '' && $_REQUEST['uname'] != '' && $_REQUEST['month_password_dob'] != '' && $_REQUEST['day_password_dob'] != '' && $_REQUEST['year_password_dob'] != '') {
            $stu_dob = $_REQUEST['year_password_dob'] . '-' . $_REQUEST['month_password_dob'] . '-' . $_REQUEST['day_password_dob'];
            $stu_info = DBGet(DBQuery('SELECT s.* FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID AND la.USERNAME=\'' . $uname . '\' AND s.BIRTHDATE=\'' . date('Y-m-d', strtotime($stu_dob)) . '\' AND s.STUDENT_ID=' . $password_stn_id . ' AND la.PROFILE_ID=3'));

            if ($stu_info[1]['STUDENT_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';

                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $flag = 'stu_pass';
                $_SESSION['PageAccess'] = $flag;
            }
        }
    }
    if ($_REQUEST['pass_user_type'] == 'pass_staff') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['uname'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Username.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['password_stf_email'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Email Address.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }


        if ($_REQUEST['password_stf_email'] != '' && $_REQUEST['uname'] != '') {

            $stf_info = DBGet(DBQuery('SELECT s.* FROM staff s,login_authentication la  WHERE la.USER_ID=s.STAFF_ID AND la.USERNAME=\'' . $uname . '\' AND s.EMAIL=\'' . $password_stf_email . '\' AND la.PROFILE_ID IN (SELECT ID FROM user_profiles WHERE ID NOT IN (0,3,4))'));

            if ($stf_info[1]['STAFF_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $flag = 'stf_pass';
                $_SESSION['PageAccess'] = $flag;
            }
        }
    }
    if ($_REQUEST['pass_user_type'] == 'pass_parent') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['uname'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Username.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['password_stf_email'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Email Address.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }

        if ($_REQUEST['password_stf_email'] != '' && $_REQUEST['uname'] != '') {

            $par_info = DBGet(DBQuery('SELECT p.* FROM people p,login_authentication la  WHERE la.USER_ID=p.STAFF_ID AND la.USERNAME=\'' . $uname . '\' AND p.EMAIL=\'' . $password_stf_email . '\' AND la.PROFILE_ID = 4'));

            if ($par_info[1]['STAFF_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $flag = 'par_pass';
                $_SESSION['PageAccess'] = $flag;
            }
        }
    }
}

if ($_REQUEST['user_type_form'] == 'username') {
    if ($_REQUEST['uname_user_type'] == 'uname_student') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['username_stn_id'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Student Id.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['pass'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Password.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['month_username_dob'] == '' || $_REQUEST['day_username_dob'] == '' || $_REQUEST['year_username_dob'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Birthday Properly.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }

        if ($_REQUEST['username_stn_id'] != '' && $_REQUEST['pass'] != '' && $_REQUEST['month_username_dob'] != '' && $_REQUEST['day_username_dob'] != '' && $_REQUEST['year_username_dob'] != '') {
            $stu_dob = $_REQUEST['year_username_dob'] . '-' . $_REQUEST['month_username_dob'] . '-' . $_REQUEST['day_username_dob'];
            /*$stu_info = DBGet(DBQuery('SELECT s.* FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID AND la.PASSWORD=\'' . md5($_REQUEST['pass']) . '\' AND s.BIRTHDATE=\'' . date('Y-m-d', strtotime($stu_dob)) . '\' AND s.STUDENT_ID=' . $username_stn_id . ''));*/

            //code started for match password & birthdate & student id 
            $get_stu_info = DBGet(DBQuery('SELECT la.PASSWORD FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID  AND s.BIRTHDATE=\'' . date('Y-m-d', strtotime($stu_dob)) . '\' AND la.PROFILE_ID=3 AND s.STUDENT_ID=' . $username_stn_id . ''));
            $student_old_password = $get_stu_info[1]['PASSWORD'];
            $entered_password =  $_REQUEST['pass'];
            $password_match_status = VerifyHash($entered_password,$student_old_password);
            
            if($password_match_status==1)
            {
                $stu_info = DBGet(DBQuery('SELECT s.* FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID AND s.BIRTHDATE=\'' . date('Y-m-d', strtotime($stu_dob)) . '\' AND la.PROFILE_ID=3 AND s.STUDENT_ID=' . $username_stn_id . ''));
            }
            else
            {
                $stu_info = [];
            }
            //end 

            if ($stu_info[1]['STUDENT_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $get_uname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $username_stn_id . ' AND PROFILE_ID=3'));
                $_SESSION['fill_username'] = $get_uname[1]['USERNAME'];
                echo'<script>window.location.href="index.php"</script>';
            }
        }
    }
    if ($_REQUEST['uname_user_type'] == 'uname_staff') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['pass'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Password.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['username_stf_email'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Email Address.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }

        if ($_REQUEST['username_stf_email'] != '' && $_REQUEST['pass'] != '') {
            /*$stf_info = DBGet(DBQuery('SELECT s.* FROM staff s,login_authentication la WHERE la.USER_ID=s.STAFF_ID AND la.PASSWORD=\'' . md5($_REQUEST['pass']) . '\' AND s.EMAIL=\'' . $username_stf_email . '\''));*/

            //code started for match password & EMAIL
            $get_stf_info = DBGet(DBQuery('SELECT la.PASSWORD FROM staff s,login_authentication la WHERE la.USER_ID=s.STAFF_ID AND la.PROFILE_ID IN ("0","1","2","5") AND s.EMAIL=\'' . $username_stf_email . '\''));
            
            $stf_old_password = $get_stf_info[1]['PASSWORD'];
            $stf_entered_password =  $_REQUEST['pass'];
            $stf_password_match_status = VerifyHash($stf_entered_password,$stf_old_password);
            
            if($stf_password_match_status==1)
            {
                $stf_info = DBGet(DBQuery('SELECT s.* FROM staff s,login_authentication la WHERE la.USER_ID=s.STAFF_ID  AND la.PROFILE_ID IN ("0","1","2","5") AND s.EMAIL=\'' . $username_stf_email . '\''));
            }
            else
            {
                $stf_info = [];
            }
            //end

            if ($stf_info[1]['STAFF_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $get_uname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $stf_info[1]['STAFF_ID'] . ' AND PROFILE_ID=' . $stf_info[1]['PROFILE_ID']));
                $_SESSION['fill_username'] = $get_uname[1]['USERNAME'];
                echo'<script>window.location.href="index.php"</script>';
            }
        }
    }
    if ($_REQUEST['uname_user_type'] == 'uname_parent') {
        if (CSRFSecure::ValidateToken($_REQUEST['TOKEN'])) {
            if ($_REQUEST['pass'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Password.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
            if ($_REQUEST['username_stf_email'] == '') {
                $_SESSION['err_msg'] = 'Please Enter Email Address.';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            }
        }
        else {
            $_SESSION['err_msg'] = 'Invalid attempt! Please try again.';
            echo'<script>window.location.href="ForgotPass.php"</script>';
        }

        if ($_REQUEST['username_stf_email'] != '' && $_REQUEST['pass'] != '') {
            /*$par_info = DBGet(DBQuery('SELECT p.* FROM people p,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PASSWORD=\'' . md5($_REQUEST['pass']) . '\' AND p.EMAIL=\'' . $username_stf_email . '\' '));*/

            //code started for match password & EMAIL
            $get_par_info = DBGet(DBQuery('SELECT la.PASSWORD FROM people p,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PROFILE_ID=4 AND p.EMAIL=\'' . $username_stf_email . '\' '));
            $par_old_password = $get_par_info[1]['PASSWORD'];
            $par_entered_password =  $_REQUEST['pass'];
            $par_password_match_status = VerifyHash($par_entered_password,$par_old_password);

            if($par_password_match_status==1)
            {
                $par_info = DBGet(DBQuery('SELECT p.* FROM people p,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PROFILE_ID=4 AND p.EMAIL=\'' . $username_stf_email . '\' '));
            }
            else
            {
                $par_info = [];
            }
            //end

            if ($par_info[1]['STAFF_ID'] == '') {
                $_SESSION['err_msg'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
                echo'<script>window.location.href="ForgotPass.php"</script>';
            } else {
                $get_uname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $par_info[1]['STAFF_ID'] . ' AND PROFILE_ID=4'));
                $_SESSION['fill_username'] = $get_uname[1]['USERNAME'];
                echo'<script>window.location.href="index.php"</script>';
            }
        }
    }
}
if ($_REQUEST['new_pass'] != '' && $_REQUEST['ver_pass'] != '') {
    $get_vals = explode(",", $user_info);
    $flag = 'submited_value';

    $get_vals[0] = cryptor($get_vals[0], 'DEC', '');
    $get_vals[1] = cryptor($get_vals[1], 'DEC', '');

    /*$get_info = DBGet(DBQuery('SELECT COUNT(*) AS EX_REC FROM login_authentication WHERE user_id!=\'' . $get_vals[0] . '\' AND profile_id!=\'' . $get_vals[1] . '\' AND password=\'' . md5($_REQUEST['ver_pass']) . '\' '));*/

    //code started for match password 
    $total_password = 0;
    $all_users = DBGet(DBQuery('SELECT * FROM login_authentication WHERE user_id!=\'' . $get_vals[0] . '\' AND profile_id!=\'' . $get_vals[1] . '\' '));
        foreach($all_users as $val)
            {
                $user_ex_password = $val['PASSWORD'];
                $user_new_password = $_REQUEST['ver_pass'];
                $user_pass_status = VerifyHash($user_new_password,$user_ex_password);
                if($user_pass_status==1) 
                    { 
                        $total_password = $total_password+1;
                    }
            }
    //end
    
    /*if ($get_info[1]['EX_REC'] > 0)*/ 
    if($total_password!=0) {
        $_SESSION['err_msg_mod'] = '<font color="red" ><b>Incorrect login credential.</b></font>';
    } else { 
        DBQuery('UPDATE login_authentication SET password=\'' . GenerateNewHash($_REQUEST['ver_pass']) . '\' WHERE user_id=\'' . $get_vals[0] . '\' AND profile_id=\'' . $get_vals[1] . '\' ');
        $_SESSION['conf_msg'] = '<font color="red" ><b>Password updated successfully.</b></font>';
         unset($_SESSION['PageAccess']);
        echo'<script>window.location.href="index.php"</script>';
    }
}

//page access validation code start
if ($_SESSION['PageAccess']!= 'stu_pass' && $_SESSION['PageAccess']!= 'stf_pass' && $_SESSION['PageAccess']!= 'par_pass')
    {
        echo'<script>window.location.href="ForgotPass.php"</script>';
    }   
//end
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>openSIS Student Information System</title>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="styles/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/login.css">
        <script src='js/Ajaxload.js'></script>
        <script src='js/Validation.js'></script>
        <script src='js/Validator.js'></script>
        <script src='js/ForgotPass.js'></script>
        <script type='text/javascript'>
            function init(param, param2) {
                calendar.set('date_' + param);
                document.getElementById('date_' + param).click();
            }
        </script>

        <script src="assets/js/core/libraries/jquery.min.js"></script>
        <script src="assets/js/core/libraries/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {

                var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]


                var newDate = new Date();

                newDate.setDate(newDate.getDate());

                $('#Date').html(dayNames[newDate.getDay()] + ", " + monthNames[newDate.getMonth()] + ' ' + newDate.getDate() + ', ' + newDate.getFullYear());



                setInterval(function () {
                    // Create a newDate() object and extract the minutes of the current time on the visitor's
                    var minutes = new Date().getMinutes();
                    // Add a leading zero to the minutes value
                    $("#min").html((minutes < 10 ? "0" : "") + minutes);
                }, 1000);

                setInterval(function () {
                    // Create a newDate() object and extract the hours of the current time on the visitor's
                    var hours = new Date().getHours();
                    // Add a leading zero to the hours value
                    $("#hours").html((hours < 10 ? "0" : "") + hours);
                }, 1000);
            });
        </script>
        <!--custom script-->
        <script src="js/custom.js"></script>
    </head>
    <body>

        <div class="clock">
            <ul>
                <li id="hours"></li>
                <li id="point">:</li>
                <li id="min"></li>
            </ul>
            <div id="Date"></div>
        </div>
        <section class="login">
            <div class="login-wrapper">

                <div class="panel">

                    <div class="panel-heading">
                        <div class="logo">
                            <img src="assets/images/opensis_logo.png" alt="openSIS" />
                        </div>                    
                        <h3>Forgot Password</h3>
                    </div>
                    <div class="panel-body">
                        <form name="f1" method="post" class="text-left" action="">

                            <?php if ($flag == 'stu_pass') { ?>
                                <input type="hidden" name="user_info" value="<?php echo cryptor($stu_info[1]['STUDENT_ID'], 'ENC', '') . ',' . cryptor('3', 'ENC', '') . ',' . $_REQUEST['uname']; ?>"/>
                                <?php
                            }
                            if ($flag == 'stf_pass') {
                                ?>
                                <input type="hidden" name="user_info" value="<?php echo cryptor($stf_info[1]['STAFF_ID'], 'ENC', '') . ',' . cryptor($stf_info[1]['PROFILE_ID'], 'ENC', '') . ',' . $_REQUEST['uname']; ?>"/>
                                <?php
                            }
                            if ($flag == 'par_pass') {
                                ?>
                                <input type="hidden" name="user_info" value="<?php echo cryptor($par_info[1]['STAFF_ID'], 'ENC', '') . ',' . cryptor($par_info[1]['PROFILE_ID'], 'ENC', '') . ',' . $_REQUEST['uname']; ?>"/>
                                <?php
                            }
                            if ($flag == 'submited_value') {
                                ?>
                                <input type="hidden" name="user_info" value="<?php echo $user_info; ?>"/>
                                <?php
                            }
                            ?>

                            <div id="divErr">
                                <?php
                                if ($_SESSION['err_msg_mod'] != '')
                                    echo $_SESSION['err_msg_mod'];
                                unset($_SESSION['err_msg_mod']);
                                ?>
                            </div>
                            <p>Password must be minimum 8 characters long with at least one capital, one numeric and one special character. Example: S@mple123</p>
                            <div class="form-group">
                                <!--                                <label class="control-label">Enter new password</label>-->
                                <input type="password" name="new_pass" id="new_pass" class="form-control" placeholder="Enter new password" AUTOCOMPLETE="off" onkeyup="forgotpasswordStrength(this.value);
                passwordMatch();
                forgotpassvalidate_password(this.value, '<?php echo $_REQUEST['uname']; ?>',<?php
                                if ($flag == 'stu_pass')
                                    echo 3;
                                else if ($flag == 'stf_pass')
                                    echo $stf_info[1]['PROFILE_ID'];
                                else
                                    echo $par_info[1]['PROFILE_ID'];
                                ?>);" />
                                <p id="passwordStrength" class="p-5"></p>
                            </div>

                            <div class="form-group">
                                <!--                                <label class="control-label">Re-enter new password</label>-->
                                <input type="password" name="ver_pass" id="ver_pass" class="form-control" placeholder="Re-enter new password" AUTOCOMPLETE = "off" onkeyup="passwordMatch();"/>
                                <p id=passwordMatch></p>
                            </div>

                            <div class="text-center">
                                <input type="submit" name="save" class="btn btn-primary" value="Update" onClick="return pass_check();"/>
                                <a class="btn btn-default" href="ForgotPass.php" style="text-decoration:none;color:black;font-weight:bold">Cancel</a>
                            </div>

                        </form>
                    </div>
                </div>

                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4Ed</a>).
                </footer>
            </div>
        </section>
    </body>
</html>

