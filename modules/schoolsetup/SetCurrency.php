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
include('lang/language.php');

if ($_REQUEST['modfunc'] == 'currenc') {

    if ($_REQUEST['values']['CURRENCY']) {
        $currency_info = DBGet(DBQuery('SELECT * FROM program_config WHERE PROGRAM=\'Currency\' AND VALUE =\'' . $_REQUEST['values']['CURRENCY'] . '\''));
        if (is_countable($currency_info[1]) && count($currency_info[1])) {
            $currency_info_exist = DBGet(DBQuery('SELECT * FROM program_config WHERE PROGRAM=\'Currency\' AND SYEAR =\'' . UserSyear() . '\' AND SCHOOL_ID =\'' . UserSchool() . '\''));
            if (is_countable($currency_info_exist[1]) && count($currency_info_exist[1])) {

                $currency_info_upd = DBQuery('UPDATE program_config SET TITLE=\'' . $currency_info[1]['TITLE'] . '\',VALUE=\'' . $_REQUEST['values']['CURRENCY'] . '\' WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'Currency\'');
                unset($_SESSION['_REQUEST_vars']['modfunc']);
                unset($_REQUEST['modfunc']);
            } else {

                $currency_info_ins = DBQuery('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE)VALUES (\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $currency_info[1]['PROGRAM'] . '\',\'' . $currency_info[1]['TITLE'] . '\',\'' . $_REQUEST['values']['CURRENCY'] . '\')');
                unset($_SESSION['_REQUEST_vars']['modfunc']);
                unset($_REQUEST['modfunc']);
            }
        }
    }
}
unset($_REQUEST['modfunc']);
$currency_info_exist = DBGet(DBQuery('SELECT * FROM program_config WHERE PROGRAM=\'Currency\' AND SYEAR =\'' . UserSyear() . '\' AND SCHOOL_ID =\'' . UserSchool() . '\''));
$val = $currency_info_exist[1]['VALUE'];

$values = DBGet(DBQuery('SELECT  VALUE AS ID,TITLE FROM program_config WHERE PROGRAM=\'Currency\' '));
foreach ($values as $symbol)
    $symbols[$symbol['ID']] = $symbol['TITLE'];


echo "<FORM name=failure class=no-margin id=failure action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=currenc&page_display=CURRENCY method=POST>";

echo '<div class="form-group"><label class="control-label text-uppercase"><b>'._currency.'</b></label>' . SelectInput($val, 'values[CURRENCY]', '', $symbols, 'N/A') . '</div>';
//if ($_REQUEST['page_display']) {
//    echo "<a href=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " class=\"btn btn-default\"><i class=\"fa fa-arrow-left\"></i>&nbsp; Back to System Preference</a>";
//}
echo SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"');

echo '</FORM>';
