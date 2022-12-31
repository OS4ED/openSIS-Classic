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
include('../../RedirectModulesInc.php');
include('lang/language.php');
if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['values'] as $vi => $vd) {
        if ($vi == 'new') {
            if ($vd['NAME'] != '') {
                $check_name = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM school_gradelevel_sections WHERE NAME=\'' . str_replace("'", "''", $vd['NAME']) . '\' AND SCHOOL_ID=' . UserSchool()));
                if ($check_name[1]['REC_EX'] > 0) {
                    $err_msg = _sectionAlreadyExists . '.';
                    break;
                }
            }
            if ($vd['NAME'] != '' && $vd['SORT_ORDER'] != '')
                DBQuery('INSERT INTO school_gradelevel_sections (SCHOOL_ID,NAME,SORT_ORDER) VALUES (' . UserSchool() . ',\'' . str_replace("'", "''", $vd['NAME']) . '\',\'' . $vd['SORT_ORDER'] . '\')');
            elseif ($vd['NAME'] != '' && $vd['SORT_ORDER'] == '')
                DBQuery('INSERT INTO school_gradelevel_sections (SCHOOL_ID,NAME) VALUES (' . UserSchool() . ',\'' . str_replace("'", "''", $vd['NAME']) . '\')');
        } elseif ($vi != 'new') {
            $go = 1;
            if ($vd['NAME'] != '') {
                $check_name = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM school_gradelevel_sections WHERE NAME=\'' . str_replace("'", "''", $vd['NAME']) . '\' AND SCHOOL_ID=' . UserSchool() . ' AND ID!=' . $vi));
                if ($check_name[1]['REC_EX'] > 0) {
                    $err_msg = _sectionAlreadyExists . '.';
                    break;
                } else
                    $go++;
            }
            $qry = 'UPDATE school_gradelevel_sections SET ';

            if ($vd['NAME'] != '')
                $qry .= ' NAME=\'' . str_replace("'", "''", $vd['NAME']) . '\',';

            if ($vd['SORT_ORDER'] != '')
                $qry .= ' SORT_ORDER=\'' . $vd['SORT_ORDER'] . '\',';

            if ($vd['SORT_ORDER'] == '')
                $qry .= ' SORT_ORDER=\'' . $vd['SORT_ORDER'] . '\',';

            $qry = substr($qry, 0, -1);
            $qry .= ' WHERE ID=' . $vi;
            if ($go = 1)
                DBQuery($qry);
        }
    }
}
DrawBC("" . _schoolSetup . " > " . ProgramTitle());

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
    $sec_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['id']);
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_enrollment WHERE SECTION_ID=\'' . $sec_id . '\''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    if ($has_assigned > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseSectionsAreAssociated . '.');
    } else {
        if (DeletePrompt_Sections('section')) {
            DBQuery('DELETE FROM school_gradelevel_sections WHERE ID=' . $sec_id);
            unset($_REQUEST['modfunc']);
        }
    }
}

if ($_REQUEST['modfunc'] != 'remove') {
    $sql = 'SELECT * FROM school_gradelevel_sections WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER';
    $sec_RET = DBGet(DBQuery($sql), array('NAME' => 'makeTextInput', 'SORT_ORDER' => 'makeTextInput'));

    $columns = array('NAME' => _section, 'SORT_ORDER' => _sortOrder);
    $link['add']['html'] = array('NAME' => makeTextInput('', 'NAME'), 'SORT_ORDER' => makeTextInputMod2('', 'SORT_ORDER'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
    $link['remove']['variables'] = array('id' => 'ID');
    if ($err_msg) {
        echo "<b style='color:red'>" . $err_msg . "</b>";

        unset($err_msg);
    }
    echo "<FORM name=F1 id=F1 action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update  onSubmit='return formcheck_school_sections();'  method=POST>";

    $section_ids = array('new');
    foreach ($sec_RET as $li => $ld)
        $section_ids[] = $ld['ID'];

    echo '<div class="panel panel-default">';
    ListOutput($sec_RET, $columns, _section, _sections, $link, true, array('search' => false));
    echo '<hr class="no-margin"/>';
    echo '<div class="panel-body text-right">';
    echo '<input type=hidden value="' . implode('_', $section_ids) . '" id="get_ids" />';
    echo '<INPUT class="btn btn-primary" type=submit value=' . _save . ' onclick="self_disable(this)">';
    echo '</div>'; //.panel-footer
    echo '</div>'; //.panel
    echo '</FORM>';
}

function makeTextInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';


    $extra = 'class=cell_floating size=25';

    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}


function makeTextInputMod2($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($id == 'new')
        $extra = 'size=25 maxlength=5 class=cell_floating onKeyDown="return numberOnly(event);"';
    else
        $extra = 'size=25 maxlength=5 class=cell_floating onKeyDown=\"return numberOnly(event);\"';



    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}
