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
if (User('PROFILE') == 'admin') {
    if (!$_REQUEST['student_id']) {
        if (!$_REQUEST['include']) {
            unset($_SESSION['student_id']);
            unset($_SESSION['_REQUEST_vars']['student_id']);
        }
    }
}
if (optional_param('modfunc', '', PARAM_NOTAGS) == 'save') {
    if ($_REQUEST['activity_id']) {
        if (count($_REQUEST['student']) != 0) {
            $current_RET = DBGet(DBQuery('SELECT STUDENT_ID FROM student_eligibility_activities WHERE ACTIVITY_ID=\'' . $_REQUEST['activity_id'] . '\' AND SYEAR=\'' . UserSyear() . '\''), array(), array('STUDENT_ID'));

            foreach ($_REQUEST['student'] as $student_id => $yes) {
                if (!$current_RET[$student_id]) {
                    $sql = 'INSERT INTO student_eligibility_activities (SYEAR,STUDENT_ID,ACTIVITY_ID)
							values(\'' . UserSyear() . '\',\'' . $student_id . '\',\'' . optional_param('activity_id', '', PARAM_SPCL) . '\')';
                    DBQuery($sql);
                }
            }
            unset($_REQUEST['modfunc']);
            $note = ""._thatActivityHasBeenAddedToTheSelectedStudents.".";
        } else {
            echo '<BR>';
            PopTable('header',  _alertMessage);
            echo "<h4>"._pleaseSelectAtleastOneStudent."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='load_link(\"Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "\");'></FORM>";
            PopTable('footer');
            return false;
        }
    } else {
        echo '<BR>';
        PopTable('header',  _alertMessage);
        echo "<h4>"._pleaseSelectAtleastOneActivity."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='load_link(\"Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "\");'></FORM>";
        PopTable('footer');
        return false;
    }
}
DrawBC(""._extracurricular." > " . ProgramTitle());
if ($note)
    echo '<div class="alert bg-success alert-styled-left">' . $note . '</div>';

if ($_REQUEST['search_modfunc'] == 'list') {
    echo "<FORM class='form-horizontal' name=addact id=addact action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save METHOD=POST>";

    echo '<div class="panel">';
    echo '<div class="panel-heading">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<div class="form-group"><label class="control-label col-md-1">'._activity.'</label>';
    echo '<div class="col-md-3">';
    $activities_RET = DBGet(DBQuery('SELECT ID,TITLE FROM eligibility_activities WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' order by TITLE'));
    echo '<SELECT name=activity_id class=form-control><OPTION value="">'._nA.'</OPTION>';
    if (count($activities_RET)) {
        foreach ($activities_RET as $activity)
            echo "<OPTION value=$activity[ID]>$activity[TITLE]</OPTION>";
    }
    echo '</SELECT>';
    echo '</div>'; //.col-md-3
    echo '</div>'; //.form-group
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row
    echo '</div>'; //.panel-heading
    echo '</div>'; //.panel

    $extra['link'] = array('FULL_NAME' =>false);
    $extra['SELECT'] = ",NULL AS CHECKBOX";
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'student\');"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'student\',\'Y\');"><A>');
    $extra['new'] = true;
}

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '<div class="col-md-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>'; //.row

if ($_REQUEST['search_modfunc'] == 'list') {
    $extra['footer'] = '<div class="panel-footer text-right p-r-20">' . SubmitButton(_addActivityToSelectedStudents, '', 'class="btn btn-primary" onclick="self_disable(this);"') . '</div>';
}
Search('student_id', $extra);
echo '<div id="modal_default" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h5 class="modal-title">'._chooseCourse.'</h5>
</div>

<div class="modal-body">';
echo '<center><div id="conf_div"></div></center>';
echo'<table id="resp_table"><tr><td valign="top">';
echo '<div>';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' found.<br>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><tr class="bg-grey-200"><th>'._subject.'</th></tr>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div></td>';
echo '<td valign="top"><div id="course_modal"></div></td>';
echo '<td valign="top"><div id="cp_modal"></div></td>';
echo '</tr></table>';
//         echo '<div id="coursem"><div id="cpem"></div></div>';
echo' </div>
</div>
</div>
</div>';
if ($_REQUEST['search_modfunc'] == 'list') {
    echo '</FORM>';
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
    // return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=$THIS_RET[STUDENT_ID]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID], \"Y\");' />";
}
// function _makeChooseCheckbox($value, $title) {
//     global $THIS_RET;

//     return "<INPUT type=checkbox name=student[" . $THIS_RET['STUDENT_ID'] . "] value=Y>";
// }

?>
