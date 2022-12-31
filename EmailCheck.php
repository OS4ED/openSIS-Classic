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
include 'Data.php';
// include('functions/SqlSecurityFnc.php');

$email = sqlSecurityFilter($_REQUEST['email']);
$p_id = sqlSecurityFilter($_REQUEST['p_id']);

if (isset($_REQUEST['email']) && $_REQUEST['email'] != '') {
    if ($_REQUEST['p_id'] == 0) {
        $result = DBGet(DBQuery('SELECT STAFF_ID FROM people WHERE EMAIL=\'' . $email . '\''));
        $res_stf = DBGet(DBQuery('SELECT STAFF_ID FROM staff WHERE EMAIL=\'' . $email . '\''));
        $res_stu = DBGet(DBQuery('SELECT STUDENT_ID FROM students WHERE EMAIL=\'' . $email . '\''));
    } else {
        $result = DBGet(DBQuery('SELECT STAFF_ID FROM people WHERE EMAIL=\'' . $email . '\' AND STAFF_ID!=' . $p_id));
        $res_stf = DBGet(DBQuery('SELECT STAFF_ID FROM staff WHERE EMAIL=\'' . $email . '\''));
        $res_stu = DBGet(DBQuery('SELECT STUDENT_ID FROM students WHERE EMAIL=\'' . $email . '\''));
    }
    if (count($result) > 0 || count($res_stf) > 0 || count($res_stu) > 0) {
        echo '0_' . urlencode($_REQUEST['opt'])[0];
    } else {
        echo '1_' . urlencode($_REQUEST['opt'])[0];
    }
    exit;
}
