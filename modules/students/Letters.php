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
$extra['action'] .= "&_openSIS_PDF=true";


$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('course');
$extra['search'] .= '</div><div class="col-md-6">';
Widgets('request');
$extra['search'] .= '</div>';
$extra['search'] .= '</div>';

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('activity');
$extra['search'] .= '</div><div class="col-md-6">';
Widgets('mailing_labels');
$extra['search'] .= '</div>';
$extra['search'] .= '</div>';

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
$extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
Widgets('gpa');
$extra['search'] .= '</div>'; //.well
$extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
Widgets('letter_grade');
$extra['search'] .= '</div>'; //.well
$extra['search'] .= '</div><div class="col-md-6">';
Widgets('eligibility');
$extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
Widgets('class_rank');
$extra['search'] .= '</div>'; //.well
$extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
Widgets('absences');
$extra['search'] .= '</div>'; //.well
$extra['search'] .= '<div><label class="control-label col-lg-4 text-right">'._letterText.'</label><div class="col-lg-8"><TEXTAREA name=letter_text rows=2 cols=40 class="form-control" placeholder="'._letterText.'"></TEXTAREA></div></div>';
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>';

$extra['force_search'] = true;
$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
$extra['search'] .= '</div>';
$extra['search'] .= '</div>';


$current_mp = GetCurrentMP('QTR', DBDate());
if (!$current_mp)
    $current_mp = GetCurrentMP('SEM', DBDate());
if (!$current_mp)
    $current_mp = GetCurrentMP('FY', DBDate());
if (!$_REQUEST['search_modfunc'] || $_openSIS['modules_search']) {
    DrawBC(""._students." -> " . ProgramTitle());

    $extra['new'] = true;
    $extra['pdf'] = 'true';
       

    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }

    Search('student_id', $extra);
       
    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        echo '<script>$("#search input[name=stuid]").val('.$_SESSION['student_id'].');</script>';
        echo '<script>document.getElementById("search").submit();</script>';
        echo '<script>$("#search input[name=stuid]").val("");</script>';
    }

    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-4"><div id="course_modal"></div></div>';
    echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal




    echo '<div id="modal_default_request" class="modal fade">';
    echo '<div class = "modal-dialog">';
    echo '<div class = "modal-content">';
    echo '<div class = "modal-header">';
    echo '<button type = "button" class = "close" data-dismiss = "modal">×</button>';
    echo '<h5 class = "modal-title">'._chooseCourse.'</h5>';
    echo '</div>';

    echo '<div class = "modal-body">';
    echo '<center><div id = "conf_div"></div></center>';

    echo '<div class = "row" id = "resp_table">';
    echo '<div class = "col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href = javascript:void(0); onclick = "chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
} else {
    
    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }

    $RET = GetStuList($extra);

    if (count($RET)) {
        $_REQUEST['letter_text'] = nl2br(str_replace("\'", "'", str_replace('  ', ' &nbsp;', $_REQUEST['letter_text'])));

        $handle = PDFStart();

        foreach ($RET as $student) {
            $student_points = $total_points = 0;
            unset($_openSIS['DrawHeader']);

            if ($_REQUEST['mailing_labels'] == 'Y') {
                echo "<tr><td colspan = 2 style = \"height:18px\"></td></tr>";
                echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">"._studentLetter."</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />"._studentLetter."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
                echo '<table border=0 style=\" font-family:Arial; font-size:12px;\">';
                echo '<tr>';
                echo '<td>' . $student['FULL_NAME'] . ', #' . $student['STUDENT_ID'] . '</td></tr>';
                echo '<tr>';
                echo '<td>' . $student['GRADE_ID'] . ' '._grade.'</td></tr>';
                echo '<tr>';
                echo '<td>'._course.': ' . $course_title . "" . GetMP($current_mp) . '</td></tr>';
                if ($student['MAILING_LABEL'] != '') {
                    echo '<tr>';
                    echo '<td >' . $student['MAILING_LABEL'] . '</td></tr>';
                }


                if ($_REQUEST['mailing_labels'] == 'Y')
                    $letter_text = $_REQUEST['letter_text'];
                foreach ($student as $column => $value)
                    $letter_text = str_replace('__' . $column . '__', $value, $letter_text);
                echo "<tr><td style=\"height:18px\"></td></tr>";
                echo '<tr><td>' . $letter_text . '</td></tr>';
                echo "<tr><td colspan=2 style=\"height:18px;\">&nbsp;</td></tr>";
                echo "</table>";
                echo "<div style=\"page-break-before: always;\"></div>";
            }
            else {
                unset($_openSIS['DrawHeader']);

                echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">"._studentLetter."</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br \>"._studentLetter."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
                echo '<table border=0 style=\" font-family:Arial; font-size:12px;\">';
                echo '<tr>';
                echo '<td>' . $student['FULL_NAME'] . ', #' . $student['STUDENT_ID'] . '</td></tr>';
                echo '<tr>';
                echo '<td>' . $student['GRADE_ID'] . ' '._grade.'</td></tr>';
                echo '<tr>';
                echo '<td>'._course.': ' . $course_title . "" . GetMP($current_mp) . '</td></tr>';
                echo '</table>';
                echo '<br>';
                $letter_text = $_REQUEST['letter_text'];
                foreach ($student as $column => $value)
                    $letter_text = str_replace('__' . $column . '__', $value, $letter_text);
                echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                echo '<tr><td>' . $letter_text . '</td></tr>';
                echo "<tr><td colspan=2 style=\"height:18px;\">&nbsp;</td></tr>";
                echo "</table>";
                echo "<div style=\"page-break-before: always;\"></div>";
            }
        }
        PDFStop($handle);
        
    } else
        BackPrompt(_noStudentsWereFound.'.');
}
?>