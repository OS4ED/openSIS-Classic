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
include 'ConfigInc.php';
include 'Warehouse.php';

if (isset($_REQUEST['ajax']))
    $_REQUEST['ajax'] = sqlSecurityFilter($_REQUEST['ajax']);

// $_REQUEST['USERINFO_FIRST_NAME']= _;
// $_REQUEST['USERINFO_LAST_NAME']= _;

// $_REQUEST['USERINFO_EMAIL']= _;
// $_REQUEST['USERINFO_MOBILE']= _;
// $_REQUEST['USERINFO_SADD']= _;
// $_REQUEST['USERINFO_CITY'] = _;
// $_REQUEST['USERINFO_STATE'] = _;
// $_REQUEST['USERINFO_ZIP']= _;

if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE'] || $_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
    $stf_ids = '';

    $sql = 'SELECT distinct stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf';
    $sql_where = 'WHERE stf.PROFILE_ID=4 AND s.STUDENT_ID!=' . UserStudentID() . ' ';

    if ($_REQUEST['USERINFO_FIRST_NAME'] || $_REQUEST['USERINFO_LAST_NAME'] || $_REQUEST['USERINFO_EMAIL'] || $_REQUEST['USERINFO_MOBILE']) {
        if ($_REQUEST['USERINFO_FIRST_NAME'] != '')
            $sql_where .= 'AND LOWER(stf.FIRST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_FIRST_NAME']))) . '%\' ';
        if ($_REQUEST['USERINFO_LAST_NAME'] != '')
            $sql_where .= 'AND LOWER(stf.LAST_NAME) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_LAST_NAME']))) . '%\' ';
        if ($_REQUEST['USERINFO_EMAIL'] != '')
            $sql_where .= 'AND LOWER(stf.EMAIL) = \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_EMAIL']))) . '\' ';
        if ($_REQUEST['USERINFO_MOBILE'] != '')
            $sql_where .= 'AND stf.CELL_PHONE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_MOBILE'])) . '\' ';
    }


    if ($_REQUEST['USERINFO_SADD'] || $_REQUEST['USERINFO_CITY'] || $_REQUEST['USERINFO_STATE'] || $_REQUEST['USERINFO_ZIP']) {
        $sql .= ' LEFT OUTER JOIN student_address sa on sa.PEOPLE_ID=stf.STAFF_ID';
        $sql_where .= '  AND sa.TYPE IN (\'Primary\',\'Secondary\',\'Other\') ';
        if ($_REQUEST['USERINFO_SADD'] != '')
            $sql_where .= ' AND LOWER(STREET_ADDRESS_1) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_SADD']))) . '%\' ';
        if ($_REQUEST['USERINFO_CITY'] != '')
            $sql_where .= ' AND LOWER(CITY) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_CITY']))) . '%\' ';
        if ($_REQUEST['USERINFO_STATE'] != '')
            $sql_where .= ' AND LOWER(STATE) LIKE \'' . str_replace("'", "''", strtolower(trim($_REQUEST['USERINFO_STATE']))) . '%\' ';
        if ($_REQUEST['USERINFO_ZIP'] != '')
            $sql_where .= ' AND ZIPCODE = \'' . str_replace("'", "''", trim($_REQUEST['USERINFO_ZIP'])) . '\' ';
    }

    $sql .= ' Left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID Left outer join students s on s.STUDENT_ID = sju.STUDENT_ID  ';
    $sql_where .= '  AND LOWER(stf.FIRST_NAME)<>\'\' AND LOWER(stf.LAST_NAME)<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ') GROUP BY sju.PERSON_ID';

    $searched_staffs = DBGet(DBQuery($sql . $sql_where), array('BUTTON' => 'makeChooseCheckbox'));

    foreach ($searched_staffs as $key => $value) {
        $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));
        $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
    }
} else {
    $sql = 'SELECT stf.STAFF_ID AS BUTTON , stf.STAFF_ID,CONCAT(stf.FIRST_NAME," ",stf.LAST_NAME) AS FULLNAME, CONCAT(s.FIRST_NAME," ",s.LAST_NAME) AS STUFULLNAME,stf.PROFILE,stf.EMAIL FROM people stf left outer join students_join_people sju on stf.STAFF_ID=sju.PERSON_ID left outer join students s on s.STUDENT_ID = sju.STUDENT_ID  WHERE  s.STUDENT_ID!=' . UserStudentID() . '  AND stf.FIRST_NAME<>\'\' AND stf.LAST_NAME<>\'\' AND sju.PERSON_ID NOT IN (SELECT PERSON_ID FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ') Group by stf.STAFF_ID';

    $searched_staffs = DBGet(DBQuery($sql), array('BUTTON' => 'makeChooseCheckbox'));

    foreach ($searched_staffs as $key => $value) {
        $stf_usrname = DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID=' . $value['STAFF_ID'] . ' AND PROFILE_ID=4'));

        $searched_staffs[$key]['USERNAME'] = $stf_usrname[1]['USERNAME'];
    }
}

$singular = _user;
$plural = _users;
$options['save'] = false;
$options['print'] = false;
$options['search'] = false;

$columns = array(
    'BUTTON' => _selectAnyOne,
    'FULLNAME' => _name,
    'USERNAME' => _username,
    'EMAIL' => _email,
    'STUFULLNAME' => _associatedStudentSName,
);


if ($_REQUEST['add_id'] == 'new')
    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST['modname'] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=new&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';
else
    echo '<FORM name=sel_staff id=sel_staff action="ForWindow.php?modname=' . $_REQUEST['modname'] . '&modfunc=lookup&type=' . $_REQUEST['type'] . '&func=search&nfunc=status&ajax=' . $_REQUEST['ajax'] . '&add_id=' . $_REQUEST['add_id'] . '&address_id=' . $_REQUEST['address_id'] . '" METHOD=POST>';

echo '<span id="sel_err" class="text-danger"></span>';

ListOutput($searched_staffs, $columns, $singular, $plural, false, $group = false, $options, 'ForWindow');
unset($_REQUEST['func']);

if (!empty($searched_staffs))
    echo '<div id="select-people-div"><br><input type="button" class="btn btn-primary" value="Select" name="button" onclick="SelectedParent(\'' . $_REQUEST['address_id'] . '\',\'' . $_REQUEST['p_type'] . '\',\'' . $_REQUEST['other_p_erson_id'] . '\')"></div>';

function makeChooseCheckbox($value, $title)
{
    global $THIS_RET;

    if ($THIS_RET['BUTTON']) {
        return "<INPUT type=radio name=staff value=" . $THIS_RET['BUTTON'] . ">";
    }
}
