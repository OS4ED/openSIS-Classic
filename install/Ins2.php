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
require_once "../functions/PragRepFnc.php";
error_reporting(0);
session_start();
if (clean_param($_REQUEST["db"], PARAM_DATA) == '') {
    header('Location: Step2.php?err=Database name cannot be blank');
    exit;
} else {
    $_SESSION['db'] = clean_param($_REQUEST["db"], PARAM_DATA);

    if (isset($_REQUEST["data_choice"])){
        if($_REQUEST["data_choice"] == 'purgedb')
            $purgedb = clean_param($_REQUEST["data_choice"], PARAM_ALPHA); // Added variable to check for removing existing data.
        else if($_REQUEST["data_choice"] == 'newdb')
            $newdb = clean_param($_REQUEST["data_choice"], PARAM_ALPHA);
    }

    // if (isset($_REQUEST["purgedb"]))
    //     $purgedb = clean_param($_REQUEST["purgedb"], PARAM_ALPHA); // Added variable to check for removing existing data.

    // $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
    $db = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password']);
    $database = $_SESSION['db'];
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $database);
    $stmt->execute();
    $stmt->bind_result($data);

    // if ($dbconn->connect_errno == 0) {
    if ($stmt->fetch()) {
        if (!empty($newdb)) {
            header('Location: Step2.php?err=Database Exists. Enter a different name to create a new database or use the remove data from existing database checkbox.');
            exit;
        }

        $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);

        if (empty($purgedb)) {
            $sql = "SHOW TABLES";
            $num_tables = $dbconn->query($sql);
            $rows = $num_tables->num_rows;
            if ($rows > 0) {
                header('Location: Step2.php?err=Selected database is not empty. Please select an empty database or use the remove data from existing database checkbox.');
                exit;
            } else {
                $myFile = "OpensisSchemaMysqlInc.sql";
                executeSQL($myFile);

                $myFile = "OpensisProcsMysqlInc.sql";
                executeSQL($myFile);

                $dbconn->close();

                header('Location: Step3.php');
            }
        } else {
            // Get tables, loop thru the tables and drop each table.
            $sql = "SHOW TABLES";
            $num_tables = $dbconn->query($sql);


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

            createUpdatedByTriggers();
            $dbconn->close();

            header('Location: Step3.php');
        }
    } else {
        if (empty($newdb)) {
            header('Location: Step2.php?err=Database does not exist. Enter a different database or use the create a new database checkbox.');
            exit;
        } else {
            $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], '', $_SESSION['port']);
            $sql = "CREATE DATABASE `" . $_SESSION['db'] . "` CHARACTER SET=utf8;";
            try {
                $result = $dbconn->query($sql);
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
                // exit($err);
            }
            if (!$result) {
                echo "<h2>" . $dbconn->error . "</h2>\n";
                exit;
            }


            $myFile = "OpensisSchemaMysqlInc.sql";
            executeSQL($myFile);

            $myFile = "OpensisProcsMysqlInc.sql";
            executeSQL($myFile);

            createUpdatedByTriggers();
            $dbconn->close();

            // edited installation
            header('Location: Step3.php');
        }
    }
    $stmt->close();
}

function executeSQL($myFile)
{
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
    $sql = file_get_contents($myFile);
    $sqllines = par_spt("/[\n]/", $sql);

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

function createUpdatedByTriggers()
{
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);

    if ($result = $dbconn->query("SELECT DISTINCT TABLE_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME IN ('updated_by')
                    AND TABLE_SCHEMA='" . $_SESSION['db'] . "';")) {
        while ($row = $result->fetch_array()) {
            $tableName = $row['TABLE_NAME'];
            // echo $tableName;

            if ($tableName == 'login_records') {
                $newValue = 'new.staff_id';
            } else {
                $newValue = '@userId';
            }
            $dbconn->query("DROP TRIGGER IF EXISTS `" . $tableName . "_updated_by_before_insert`;");
            $dbconn->query("CREATE TRIGGER `" . $tableName . "_updated_by_before_insert` BEFORE INSERT ON `" . $tableName . "` FOR EACH ROW BEGIN SET new.updated_by=" . $newValue . "; END;");
            $dbconn->query("DROP TRIGGER IF EXISTS `" . $tableName . "_updated_by_before_update`;");
            $dbconn->query("CREATE TRIGGER `" . $tableName . "_updated_by_before_update` BEFORE UPDATE ON `" . $tableName . "` FOR EACH ROW BEGIN SET new.updated_by=" . $newValue . "; END;");
        }
        // Free result set
        $result->free_result();
    }

    if ($result = $dbconn->query("SELECT DISTINCT TABLE_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE COLUMN_NAME IN ('last_updated')
                    AND TABLE_SCHEMA='" . $_SESSION['db'] . "';")) {
        while ($row = $result->fetch_array()) {
            $tableName = $row['TABLE_NAME'];
            $dbconn->query("ALTER TABLE `" . $tableName . "` CHANGE `last_updated` `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; ");
        }
        // Free result set
        $result->free_result();
    }
    $dbconn->close();
}
