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
include('RedirectRootInc.php');
include('ConfigInc.php');
include('Warehouse.php');

$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
if (!$RET) {
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
}
if (!$RET) {
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
}

$html = '<select class="select" name="mp" id="head_frm_mp_id" onchange="this.form.submit();">';
foreach ($RET as $mp) {
    $selected = ($mp['MARKING_PERIOD_ID'] == ($_SESSION['UserMP'] ?? '')) ? ' selected' : '';
    $html .= '<option value="' . $mp['MARKING_PERIOD_ID'] . '"' . $selected . '>' . $mp['TITLE'] . '</option>';
}
$html .= '</select>';

echo $html;
?>