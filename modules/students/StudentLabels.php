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
$max_cols = 3;
$max_rows = 10;

if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
{
    $_REQUEST['search_modfunc'] = 'list';
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    if (count($_REQUEST['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
        $qtr = GetAllMP('QTR', GetCurrentMP('QTR', DBDate(), false));
        $extra['SELECT'] .= ',coalesce(s.COMMON_NAME,s.FIRST_NAME) AS NICK_NAME';
        if (User('PROFILE') == 'admin') {
            if ($_REQUEST['w_course_period_id_which'] == 'course_period' && $_REQUEST['w_course_period_id']) {
                if ($_REQUEST['teacher'])
                    $extra['SELECT'] .= ',(SELECT CONCAT(st.FIRST_NAME,\'' . ' ' . '\',st.LAST_NAME) FROM staff st,course_periods cp WHERE st.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_PERIOD_ID=\'' . $_REQUEST[w_course_period_id] . '\') AS TEACHER';
                if ($_REQUEST['room'])
                    $extra['SELECT'] .= ',(SELECT GROUP_CONCAT(r.TITLE) AS ROOM  FROM course_period_var cpv,rooms r WHERE cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID=\'' . $_REQUEST[w_course_period_id] . '\') AS ROOM';
            }
            else {
                if ($_REQUEST['teacher'])
                    $extra['SELECT'] .= ',(SELECT CONCAT(st.FIRST_NAME,\'' . ' ' . '\',st.LAST_NAME) FROM staff st,course_periods cp,school_periods p,schedule ss,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND st.STAFF_ID=cp.TEACHER_ID AND cpv.PERIOD_ID=p.PERIOD_ID AND p.ATTENDANCE=\'' . 'Y' . '\' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND ss.MARKING_PERIOD_ID IN(' . $qtr . ') ' : '') . ' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER';
                if ($_REQUEST['room'])
                    $extra['SELECT'] .= ',(SELECT GROUP_CONCAT(r.TITLE) AS ROOM   FROM course_periods cp,school_periods p,schedule ss,course_period_var cpv,rooms r WHERE cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID=p.PERIOD_ID AND p.ATTENDANCE=\'' . 'Y' . '\' AND  cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? ' AND ss.MARKING_PERIOD_ID IN(' . $qtr . ') ' : '') . ' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM';
            }
        }
        else {
            if ($_REQUEST['teacher'])
                $extra['SELECT'] .= ',(SELECT CONCAT(st.FIRST_NAME,\'' . ' ' . '\',st.LAST_NAME) FROM staff st,course_periods cp WHERE st.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') AS TEACHER';
            if ($_REQUEST['room'])
                $extra['SELECT'] .= ',(SELECT GROUP_CONCAT(r.TITLE) AS ROOM FROM course_period_var cpv,rooms r WHERE cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\') AS ROOM';
        }
        $RET = GetStuList($extra);
        $school_wise = array();
        foreach ($RET as $ri => $rd) {
            $school_wise[$rd['SCHOOL_ID']][] = $rd;
        }

        if (count($school_wise)) {
            foreach ($school_wise as $si => $sd) {
//			$skipRET = array();
//			for($i=($_REQUEST['start_row']-1)*$max_cols+$_REQUEST['start_col']; $i>1; $i--)
//				$skipRET[-$i] = array('LAST_NAME'=>' ');
//                        print_r($sd);echo '<br><br>';
                $handle = PDFstart();

                $handle = PDFstart();

                $cols = 0;
                $rows = 0;

                echo "<table width=100%  border=0 style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogoParam($si) . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(($si != '' ? $si : UserSchool())) . "<div style=\"font-size:12px;\">"._studentLabels."</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br \>"._studentLabels."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
                echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" style=font-family:Arial; font-size:12px;>';
                foreach ($sd as $i => $student) {
                    if ($cols < 1)
                        echo '<tr>';
                    echo '<td width="33.3%" height="86" align="center" valign="middle">';
                    echo '<table border=0 align=center>';
                    echo '<tr>';
                    echo '<td align=center>' . $student['NICK_NAME'] . ' ' . $student['LAST_NAME'] . '</td></tr>';
                    if ($_REQUEST['teacher']) {
                        echo '<tr><td align=center>'._teacher.' :';
                        echo '' . $student['TEACHER'] . '</td></tr>';
                    }
                    if ($_REQUEST['room']) {
                        echo '<tr><td align=center>'._roomNo.' :';
                        echo '' . $student['ROOM'] . '</td></tr>';
                    }
                    echo '</table>';

                    $cols++;

                    if ($cols == $max_cols) {
                        echo '</tr>';
                        $rows++;
                        $cols = 0;
                    }

                    if ($rows == $max_rows) {
                        echo '</table><div style="page-break-before: always;">&nbsp;</div>';
                        echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0">';
                        $rows = 0;
                    }
                }

                if ($cols == 0 && $rows == 0) {
                    
                } else {
                    while ($cols != 0 && $cols < $max_cols) {
                        echo '<td width="33.3%" height="86" align="center" valign="middle">&nbsp;</td>';
                        $cols++;
                    }
                    if ($cols == $max_cols)
                        echo '</tr>';
                    echo '</table>';
                }
            }

            if ($cols == 0 && $rows == 0) {
                
            } else {
                while ($cols != 0 && $cols < $max_cols) {
                    echo '<td width="33.3%" height="86" align="center" valign="middle">&nbsp;</td>';
                    $cols++;
                }
                if ($cols == $max_cols)
                    echo '</tr>';
                echo '</table>';
            }

            PDFstop($handle);
        } else
            BackPrompt(_noStudentsWereFound.'.');
    }
}

if (!$_REQUEST['modfunc']) {
    DrawBC(""._students." > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools]" . (User('PROFILE') == 'admin' ? "&w_course_period_id_which=$_REQUEST[w_course_period_id_which]&w_course_period_id=$_REQUEST[w_course_period_id]" : '') . "&_openSIS_PDF=true method=POST target=_blank>";


        //$extra['extra_header_left'] = '<div class="row">';
        //$extra['extra_header_left'] .= '<div class="col-md-6">';
        $extra['extra_header_left'] = '<h6>'._includeOnLabels.':</h6>';
        if (User('PROFILE') == 'admin') {
            if ($_REQUEST['w_course_period_id_which'] == 'course_period' && $_REQUEST['w_course_period_id']) {
                $course_RET = DBGet(DBQuery('SELECT CONCAT(s.FIRST_NAME,' . ' ' . ',s.LAST_NAME) AS TEACHER,r.TITLE AS ROOM FROM staff s,course_periods cp,course_period_var cpv,rooms r WHERE r.ROOM_ID=cpv.ROOM_ID AND s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $_REQUEST[w_course_period_id] . '\''));
                $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=teacher value=Y><span></span>'._teacher.' (' . $course_RET[1]['TEACHER'] . ')</label>';
                $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=room value=Y><span></span>'._room.' (' . $course_RET[1]['ROOM'] . ')</label>';
            } else {
                $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=teacher value=Y><span></span>'._attendanceTeacher.'</label>';
                $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=room value=Y><span></span>'._attendanceRoom.'</label>';
            }
        } else {
            $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=teacher value=Y><span></span>'._teacher.'</label>';
            $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=room value=Y><span></span>'._room.'</label>';
        }
        //$extra['extra_header_left'] .= '</div>';

        $extra['extra_header_right'] .= '<div style="width:300px;">';
        $extra['extra_header_right'] .= '<div class="row">';
        $extra['extra_header_right'] .= '<div class="col-md-6">';
        $extra['extra_header_right'] .= '<div class="form-group"><label class="control-label">'._startingRow.'</label><SELECT class="form-control" name=start_row>';
        for ($row = 1; $row <= $max_rows; $row++) {
            $extra['extra_header_right'] .= '<OPTION value="' . $row . '">' . $row;
        }
        $extra['extra_header_right'] .= '</SELECT></div>';
        $extra['extra_header_right'] .= '</div>'; //.col-md-6
        $extra['extra_header_right'] .= '<div class="col-md-6">';
        $extra['extra_header_right'] .= '<div class="form-group">';
        $extra['extra_header_right'] .= '<label class="control-label">'._startingColumn.'</label><SELECT class="form-control" name=start_col>';
        for ($col = 1; $col <= $max_cols; $col++)
            $extra['extra_header_right'] .= '<OPTION value="' . $col . '">' . $col;
        $extra['extra_header_right'] .= '</SELECT></div>';

        $extra['extra_header_right'] .= '</div>'; //.col-md-6
        $extra['extra_header_right'] .= '</div>'; //.row
        $extra['extra_header_right'] .= '</div>';
    }


    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('course');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';

    $extra['link'] = array('FULL_NAME' =>false);
    $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onClick="checkAllDtMod(this,\'st_arr\')"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod2(this,\'st_arr\');"><A>');
    $extra['options']['search'] = false;
    $extra['new'] = true;

    Search('student_id', $extra);
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\''._printLabelsForSelectedStudents.'\'></div>';
        echo "</FORM>";
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
}

function _makeChooseCheckbox($value, $title) {
    //    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    global $THIS_RET;

    // return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET[STUDENT_ID] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);'/>";

    return "<input class='student_label_cbx' name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

?>