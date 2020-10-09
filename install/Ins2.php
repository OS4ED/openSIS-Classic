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
include '../functions/ParamLibFnc.php';
require_once("../functions/PragRepFnc.php");
error_reporting(0);
session_start();
$_SESSION['db'] = clean_param($_REQUEST["db"], PARAM_DATA);
$purgedb = clean_param($_REQUEST["purgedb"], PARAM_ALPHA); // Added variable to check for removing existing data.
//$dbconn = mysql_connect($_SESSION['host'],$_SESSION['username'],$_SESSION['password']);
$dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
//$sql="select count(*) from information_schema.SCHEMATA where schema_name = '".$_SESSION['db']."'" ;
//$res =$dbconn->query($sql);
//
////$res = mysql_query($sql);
//
//while ($row = $res->fetch_row()) {
//    $exists =  $row[0];
//}
if ($dbconn->connect_errno == 0) {
    if (empty($purgedb)) {
        header('Location: Step2.php?err=Database Exists. Enter a different name');
        exit;
    } else {
//        $result = mysql_select_db($_SESSION['db']);
//        if(!$result)
//        {
//            echo "<h2>" . mysql_error() . "</h2>\n";
//            exit;
//        }
        // Get tables, loop thru the tables and drop each table.
        $sql = "SHOW TABLES";
        $num_tables = $dbconn->query($sql);
//        die(mysqli_error());
//        $num_tables = mysqli_l($_SESSION['db']);
        while ($row = $num_tables->fetch_row()) {
            // Drop all tables.
            $delete_table = $dbconn->query("DROP TABLE IF EXISTS $row[0]");

            // Separate Drop for VIEWs is needed due to mysql syntax for views.
            $delete_view = $dbconn->query("DROP VIEW IF EXISTS $row[0]");

            // There is currently no way to drop functions without knowing
            // the functions name and doing a DROP FUNCTION name
            // so we have to modify the mysql file to remove functions first
            // before trying to add them or else an error will occur.

            if (!$delete_table) {
                echo 'Unable to remove ' . $row[0] . '<br>';
            }
        }
        // Free result set to clear memory
//        mysql_free_result($num_tables);
        //This begins the add portion

        $myFile = "OpensisSchemaMysqlInc.sql";
        executeSQL($myFile);

        $myFile = "OpensisProcsMysqlInc.sql";
        executeSQL($myFile);

        $dbconn->close();

        header('Location: Step3.php');
    }
} else {
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], '', $_SESSION['port']);
    $sql = "CREATE DATABASE `" . $_SESSION['db'] . "` CHARACTER SET=utf8;";
    $result = $dbconn->query($sql);
    if (!$result) {
        echo "<h2>" . $dbconn->error . "</h2>\n";
        exit;
    }
//    $result = mysql_select_db($_SESSION['db']);
//    if(!$result)
//    {
//        echo "<h2>" . mysql_error() . "</h2>\n";
//        exit;
//    }


    $myFile = "OpensisSchemaMysqlInc.sql";
    executeSQL($myFile);


    $myFile = "OpensisProcsMysqlInc.sql";
    executeSQL($myFile);

    //mysqli_close($dbconn);
    $dbconn->close();

// edited installation
    header('Location: Step3.php');
}

function executeSQL($myFile) {
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
    $sql = file_get_contents($myFile);
    $sqllines = par_spt("/[\n]/", $sql);
//    print_r($sqllines);exit;
    $cmd = '';
    $delim = false;
    foreach ($sqllines as $l) {
        if (par_rep_mt('/^\s*--/', $l) == 0) {
            if (par_rep_mt('/DELIMITER \$\$/', $l) != 0) {
                $delim = true;
            } else {
                if (par_rep_mt('/DELIMITER ;/', $l) != 0) {
                    $delim = false;
                } else {
                    if (par_rep_mt('/END\$\$/', $l) != 0) {
                        $cmd .= ' END';
                    } else {
                        $cmd .= ' ' . $l . "\n";
                    }
                }
                if (par_rep_mt('/.+;/', $l) != 0 && !$delim) {
                    $result = $dbconn->query($cmd) or die($dbconn->error);
                    $cmd = '';
                }
            }
        }
    }
}

?>
