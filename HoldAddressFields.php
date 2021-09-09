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

include 'RedirectRootInc.php';
include 'Warehouse.php';

$_POST['ADDR_CONT_USRN'] = sqlSecurityFilter($_POST['ADDR_CONT_USRN']);
$_POST['ADDR_CONT_PSWD'] = sqlSecurityFilter($_POST['ADDR_CONT_PSWD']);
$_POST['SECN_CONT_USRN'] = sqlSecurityFilter($_POST['SECN_CONT_USRN']);
$_POST['SECN_CONT_PSWD'] = sqlSecurityFilter($_POST['SECN_CONT_PSWD']);

$this_password = $secn_password = '';
$addressHoldSet = array();

// FOR PRIMARY CONTACTS
if ($_POST['ADDR_CONT_USRN'] != "" && $_POST['ADDR_CONT_PSWD'] != "") {
	$qry_one = DBGet(DBQuery('SELECT * FROM `login_authentication` WHERE `username` = "'.$_POST['ADDR_CONT_USRN'].'" AND `password` = "'.$_POST['ADDR_CONT_PSWD'].'"'));

	$counted = count($qry_one);

	if ($counted > 0) {
		$this_password = $qry_one[1]['PASSWORD'];
	}
	else {
		$this_password = md5($_POST['ADDR_CONT_PSWD']);
	}
}
else {
	$this_password = "";
}


// FOR SECONDARY CONTACTS
if ($_POST['SECN_CONT_USRN'] != "" && $_POST['SECN_CONT_PSWD'] != "") {
	$secn_qry_one =	DBGet(DBQuery('SELECT * FROM `login_authentication` WHERE `username` = "'.$_POST['SECN_CONT_USRN'].'" AND `password` = "'.$_POST['SECN_CONT_PSWD'].'"'));

	$secn_counted =	count($secn_qry_one);

	if ($secn_counted > 0) {
		$secn_password = $secn_qry_one[1]['PASSWORD'];
	}
	else {
		$secn_password = md5($_POST['ADDR_CONT_PSWD']);
	}
}
else {
	$secn_password = "";
}

$addressFields = array('ADDR_PRIM_L1', 'ADDR_PRIM_L2', 'ADDR_PRIM_CITY', 'ADDR_PRIM_STATE', 'ADDR_PRIM_ZIP', 'ADDR_PRIM_BUSNO', 'ADDR_PRIM_BPU', 'ADDR_PRIM_BDO', 'ADDR_SAME_HOME', 'ADDR_SAME_AS', 'ADDR_MAIL_L1', 'ADDR_MAIL_L2', 'ADDR_MAIL_CITY', 'ADDR_MAIL_STATE', 'ADDR_MAIL_ZIP', 'ADDR_CONT_RSHIP', 'ADDR_CONT_FIRST', 'ADDR_CONT_LAST', 'ADDR_CONT_HOME', 'ADDR_CONT_WORK', 'ADDR_CONT_CELL', 'ADDR_CONT_MAIL', 'ADDR_CONT_CUSTODY', 'ADDR_CONT_PORTAL', 'ADDR_CONT_USRN', 'ADDR_CONT_PSWD', 'ADDR_CONT_SAHA', 'ADDR_CONT_ADNA', 'ADDR_CONT_LIN1', 'ADDR_CONT_LIN2', 'ADDR_CONT_CITY', 'ADDR_CONT_STAT', 'ADDR_CONT_ZIP', 'CHK_HOME_ADDR_PRIM', 'SECN_CONT_RSHIP', 'SECN_CONT_FIRST', 'SECN_CONT_LAST', 'SECN_CONT_HOME', 'SECN_CONT_WORK', 'SECN_CONT_CELL', 'SECN_CONT_MAIL', 'SECN_CONT_CUSTODY', 'SECN_CONT_PORTAL', 'SECN_CONT_USRN', 'SECN_CONT_PSWD', 'SECN_CONT_LIN1', 'SECN_CONT_LIN2', 'SECN_CONT_CITY', 'SECN_CONT_STAT', 'SECN_CONT_ZIP', 'CHK_HOME_ADDR_SECN', 'SELECTED_PRIMARY', 'SELECTED_SECONDARY');

foreach ($_POST as $postKeys => $postVals) {
	if (in_array($postKeys, $addressFields)) {
		if ($postKeys == 'ADDR_CONT_PSWD')
			$postVals = $this_password;
		else if ($postKeys == 'SECN_CONT_PSWD')
			$postVals = $secn_password;
		else
			$postVals = sqlSecurityFilter($postVals);

		$addressHoldSet[$postKeys] = $postVals;
	}
}

$_SESSION["HOLD_ADDR_DATA"]	=	$addressHoldSet;

?>