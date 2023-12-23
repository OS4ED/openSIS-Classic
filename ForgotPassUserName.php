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
include("functions/ParamLibFnc.php");
include("Data.php");
include("functions/DbGetFnc.php");
require_once("functions/PragRepFnc.php");
function db_start()
{
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
		db_show_error("", "" . _couldNotConnectToDatabase . ": $DatabaseServer", $errstring);
	}
	return $connection;
}


##### Connection help #####
$connection = mysqli_connect($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);

if (!$connection) {
	die('Could Not Connect: ' . mysqli_error($connection) . mysqli_errno($connection));
}


// This function connects, and does the passed query, then returns a connection identifier.
// Not receiving the return == unusable search.
//		ie, $processable_results = DBQuery("select * from students");
function DBQuery($sql)
{
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
function db_fetch_row($result)
{
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
	return @array_change_key_case($return, CASE_UPPER);
}

// returns code to go into SQL statement for accessing the next value of a sequence function db_seq_nextval($seqname)
function db_seq_nextval($seqname)
{
	global $DatabaseType;

	if ($DatabaseType == 'mysqli')
		$seq = "fn_" . strtolower($seqname) . "()";
	return $seq;
}

function db_case($array)
{
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

			$string .= "$value";
			if ($counter == ($array_count - 2) && $array_count % 2 == 0)
				$string .= " ELSE ";
			elseif ($counter == ($array_count - 1))
				$string .= " END ";
			elseif ($counter % 2 == 0)
				$string .= " WHEN $array[0]=";
			elseif ($counter % 2 == 1)
				$string .= " THEN ";

			$counter++;
		}
	}

	return $string;
}

function db_properties($table)
{
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

function db_show_error($sql, $failnote, $additional = '')
{
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
$used_for = $_GET['used_for'];
if ($used_for == 'username') {
	$username = mysqli_real_escape_string($connection, trim($_GET['u']));
	$usr_type = mysqli_real_escape_string($connection, trim($_GET['user_type']));
	$found = false;
	if ($usr_type == 'student') {
		$check_uname =  DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME = \'' . $username . '\'  AND PROFILE_ID IN (SELECT ID FROM user_profiles WHERE PROFILE=\'student\')'));
	} elseif ($usr_type == 'staff') {
		$check_uname =  DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME = \'' . $username . '\'  AND PROFILE_ID IN (SELECT ID FROM user_profiles WHERE ID NOT IN (0,3,4))'));
	} else {
		$check_uname =  DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME = \'' . $username . '\'  AND PROFILE_ID IN (SELECT ID FROM user_profiles WHERE PROFILE=\'parent\')'));
	}
	if ($check_uname[1]['USERNAME'] != '') {
		echo '1';
	} else
		echo '0';
} else {
	$email = mysqli_real_escape_string($connection, trim($_GET['u']));
	$usr_type = mysqli_real_escape_string($connection, trim($_GET['user_type']));
	$found = false;
	if ($usr_type == 'staff') {
		if ($_GET['username'] != '')
			$check_email =  DBGet(DBQuery('SELECT * FROM staff s,login_authentication la WHERE s.EMAIL = \'' . $email . '\' AND s.STAFF_ID=la.USER_ID AND la.username=\'' . mysqli_real_escape_string($connection, trim($_GET['username'])) . '\''));
		else
			$check_email =  DBGet(DBQuery('SELECT * FROM staff WHERE EMAIL = \'' . $email . '\''));
	} else {
		if ($_GET['username'] != '')
			$check_email =  DBGet(DBQuery('SELECT * FROM people p,login_authentication la WHERE p.EMAIL = \'' . $email . '\' AND p.STAFF_ID=la.USER_ID AND la.username=\'' . mysqli_real_escape_string($connection, trim($_GET['username'])) . '\''));
		else
			$check_email =  DBGet(DBQuery('SELECT * FROM people WHERE EMAIL = \'' . $email . '\''));
	}
	if ($check_email[1]['EMAIL'] != '') {
		echo '1~' . $_GET['form'];
	} else
		echo '0~' . $_GET['form'];
}
