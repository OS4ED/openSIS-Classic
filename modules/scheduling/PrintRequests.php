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
include '../../RedirectModulesInc.php';
include 'lang/language.php';
unset($_SESSION['student_id']);

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('request');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '<div class="col-lg-6">';
Widgets('mailing_labels');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

$extra['force_search'] = true;
if (!$_REQUEST['search_modfunc'] || $_openSIS['modules_search']) {
    DrawBC("" . _scheduling . " > " . ProgramTitle());
    $extra['new'] = true;
    $extra['action'] .= "&_openSIS_PDF=true&head_html=Student+Print+Request";
    $extra['pdf'] = true;
    Search('student_id', $extra);
} else {
    $columns = array('COURSE_TITLE' => '' . _course . '', 'MARKING_PERIOD_ID' => '' . _markingPeriod . '', 'WITH_TEACHER_ID' => '' . _withTeacher . '', 'WITH_PERIOD_ID' => '' . _inPeriod . '', 'NOT_TEACHER_ID' => '' . _notWithTeacher . '', 'NOT_PERIOD_ID' => '' . _notInPeriod . '');
    $extra['SELECT'] .= ',c.TITLE AS COURSE_TITLE,c.COURSE_ID,srp.PRIORITY,srp.MARKING_PERIOD_ID,srp.WITH_TEACHER_ID,srp.NOT_TEACHER_ID,srp.WITH_PERIOD_ID,srp.NOT_PERIOD_ID';
    $extra['FROM'] .= ',courses c,schedule_requests srp';
    $extra['WHERE'] .= ' AND ssm.STUDENT_ID=srp.STUDENT_ID AND ssm.SYEAR=srp.SYEAR AND srp.COURSE_ID = c.COURSE_ID';

    $extra['functions'] += array('WITH_FULL_NAME' => '_makeExtra', 'MARKING_PERIOD_ID' => '_makeMpName');
    $extra['group'] = array('STUDENT_ID');
    if ($_REQUEST['mailing_labels'] == 'Y') {
        $extra['group'][] = 'ADDRESS_ID';
    }
    $RET = GetStuList($extra);

    if (count($RET)) {
        $__DBINC_NO_SQLSHOW = true;
        $handle = PDFStart();
        echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
        echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _studentPrintRequest . "</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br \>" . _poweredByOpenSis . " openSIS</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
        foreach ($RET as $student_id => $courses) {
            if ($_REQUEST['mailing_labels'] == 'Y') {
                foreach ($courses as $address) {

                    unset($_openSIS['DrawHeader']);

                    echo "</table >";
                    echo '<BR><BR>';
                    echo '<table border=0>';
                    echo "<tr><td>" . _studentID . ":</td>";
                    echo "<td>" . $address[1]['STUDENT_ID'] . "</td></tr>";
                    echo "<tr><td>" . _studentName . ":</td>";
                    echo "<td>" . $address[1]['FULL_NAME'] . "</td></tr>";
                    echo "<tr><td>" . _studentGrade . ":</td>";
                    echo "<td>" . $address[1]['GRADE_ID'] . "</td></tr>";
                    if ($address[1]['MAILING_LABEL'] != '') {
                        echo "<tr><td>" . _studentMaillingLabel . " :</td>";
                        echo "<td> " . $address[1]['MAILING_LABEL'] . "</td></tr>";
                    }
                    echo '</table>';

                    ListOutputPrint($address, $columns, _request, _requests, array(), array(), array('center' => false, 'print' => false));
                    echo '<!-- NEW PAGE -->';
                }
            } else {
                unset($_openSIS['DrawHeader']);

                echo "</table >";
                echo '<BR><BR>';
                echo '<table border=0>';
                echo "<tr><td>" . _studentId . ":</td>";
                echo "<td>" . $courses[1]['STUDENT_ID'] . "</td></tr>";
                echo "<tr><td>" . _studentName . ":</td>";
                echo "<td>" . $courses[1]['FULL_NAME'] . "</td></tr>";
                echo "<tr><td>" . _studentGrade . ":</td>";
                echo "<td>" . $courses[1]['GRADE_ID'] . "</td></tr>";
                if ($address[1]['MAILING_LABEL'] != '') {
                    echo "<tr><td>" . _studentMaillingLabel . " :</td>";
                    echo "<td> " . $courses[1]['MAILING_LABEL'] . "</td></tr>";
                }
                echo '</table>';

                foreach ($courses as $key => $value) {
                    // set MARKING_PERIOD_ID
                    if ($courses[$key]['WITH_TEACHER_ID']) {
                        $get_mp_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.COURSE_ID=\'' . $value['COURSE_ID'] . '\' AND cpv.PERIOD_ID=\'' . $value['WITH_PERIOD_ID'] . '\' '));
                        $stmt = DBGet(DBQuery('select title from marking_periods
									  where marking_period_id=\'' . $get_mp_id[1]['MARKING_PERIOD_ID'] . '\' limit 1'));

                        $marking_period_id = $courses[$key]['MARKING_PERIOD_ID'];

                        $title = '';

                        $courses[$key]['MARKING_PERIOD_ID'] = $stmt[1]['TITLE'];
                        unset($stmt);
                    }
                    // set WITH_TEACHER_ID
                    if ($courses[$key]['WITH_TEACHER_ID']) {
                        //
                        $stmt = DBGet(DBQuery('select CONCAT(first_name,\'' . ' ' . '\',last_name) as title from staff where staff_id=\'' . $courses[$key]['WITH_TEACHER_ID'] . '\' limit 1'));

                        $staff_id = $courses[$key]['WITH_TEACHER_ID'];
                        $title = '';
                        $courses[$key]['WITH_TEACHER_ID'] = $stmt[1]['TITLE'];
                        unset($stmt);
                    }
                    // set NOT_TEACHER_ID
                    if ($courses[$key]['NOT_TEACHER_ID']) {
                        $stmt = DBGet(DBQuery('select CONCAT(first_name,\'' . ' ' . '\',last_name) as title from staff where staff_id=\'' . $courses[$key]['NOT_TEACHER_ID'] . '\' limit 1'));
                        $staff_id = $courses[$key]['NOT_TEACHER_ID'];
                        $title = '';
                        $courses[$key]['NOT_TEACHER_ID'] = $stmt[1]['TITLE'];
                        unset($stmt);
                    }
                    // set WITH_PERIOD_ID
                    if ($courses[$key]['WITH_PERIOD_ID']) {
                        $stmt = DBGet(DBQuery('select title from school_periods where period_id=\'' . $courses[$key]['WITH_PERIOD_ID'] . '\' limit 1'));
                        $period_id = $courses[$key]['WITH_PERIOD_ID'];
                        $title = '';
                        $courses[$key]['WITH_PERIOD_ID'] = $stmt[1]['TITLE'];
                        unset($stmt);
                    }
                    // set NOT_PERIOD_ID
                    if ($courses[$key]['NOT_PERIOD_ID']) {
                        $stmt = DBGet(DBQuery('select title from school_periods where period_id=\'' . $courses[$key]['NOT_PERIOD_ID'] . '\' limit 1'));
                        $period_id = $courses[$key]['NOT_PERIOD_ID'];
                        $title = '';
                        $courses[$key]['NOT_PERIOD_ID'] = $stmt[1]['TITLE'];
                        unset($stmt);
                    }
                }

                ListOutputPrint($courses, $columns, _request, _requests, array(), array(), array('center' => false, 'print' => false));
                echo '<!-- NEW PAGE -->';
            }
        }
        PDFStop($handle);
    } else {
        BackPrompt('' . _noStudentsWereFound . '.');
    }

}
if (!$_REQUEST['search_modfunc'] || $_openSIS['modules_search']) {
    echo '<div id="modal_default_request" class="modal fade">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';

    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
    echo '</div>'; //.modal-header

    echo '<div class="modal-body">';
    echo '<div id="conf_div" class="text-center"></div>';
    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas . '' : ' ' . _subjectsWere . '') . ' ' . _found . '.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead>';
        echo '<tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    echo '</div>';
    echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
}

echo "<script>$(document).mousemove(function() {
    $('#searchStuBtn').prop('disabled', false);
  });</script>";

function _makeExtra($value, $title = '')
{
    global $THIS_RET;
    $return = "";
    if ($THIS_RET['WITH_TEACHER_ID']) {
        $return .= '' . _with . ':&nbsp;' . GetTeacher($THIS_RET['WITH_TEACHER_ID']) . '<BR>';
    }

    if ($THIS_RET['NOT_TEACHER_ID']) {
        $return .= '' . _notWith . ':&nbsp;' . GetTeacher($THIS_RET['NOT_TEACHER_ID']) . '<BR>';
    }

    if ($THIS_RET['WITH_PERIOD_ID']) {
        $return .= '' . _on . ':&nbsp;' . GetPeriod($THIS_RET['WITH_PERIOD_ID']) . '<BR>';
    }

    if ($THIS_RET['NOT_PERIOD_ID']) {
        $return .= '' . _notOn . ':&nbsp;' . GetPeriod($THIS_RET['NOT_PERIOD_ID']) . '<BR>';
    }

    if ($THIS_RET['PRIORITY']) {
        $return .= '' . _priority . ':&nbsp;' . $THIS_RET['PRIORITY'] . '<BR>';
    }

    if ($THIS_RET['MARKING_PERIOD_ID']) {
        $return .= '' . _markingPeriod . ':&nbsp;' . GetMP($THIS_RET['MARKING_PERIOD_ID']) . '<BR>';
    }

    return $return;
}

function _makeMpName($value)
{
    if ($value != '') {
        $get_name = DBGet(DBQuery('SELECT TITLE FROM marking_periods WHERE marking_period_id=' . $value));
        return $get_name[1]['TITLE'];
    } else {
        return '' . _customCoursePeriod . '';
    }

}
