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

if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
{
    $_REQUEST['search_modfunc'] = 'list';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////New Datepicker/////////////////////////////////////////////////////////////
if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start']) {
    $start_date = $_REQUEST['year_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['day_start'];
    $st_dt = $start_date;
//    $st_dt = ProperDateMAvr($start_date);
}
if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end']) {
    $end_date = $_REQUEST['year_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['day_end'];
    $end_dt = $end_date;
//    $end_dt = ProperDateMAvr($end_date);
}
if ($_REQUEST['chk_pro']) {
    $progress = $_REQUEST['chk_pro'];
}
if ($_REQUEST['modfunc'] == 'save') {
    if (count($_REQUEST['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';


        if ($_REQUEST['mailing_labels'] == 'Y')
            Widgets('mailing_labels');

        $RET = GetStuList($extra);

        if (count($RET)) {
            include('modules/students/includes/FunctionsInc.php');
            //------------Comment Heading -----------------------------------------------------

            $people_categories_RET = DBGet(DBQuery('SELECT c.ID AS CATEGORY_ID,c.TITLE AS CATEGORY_TITLE,f.ID,f.TITLE,f.TYPE,f.SELECT_OPTIONS,f.DEFAULT_SELECTION,f.REQUIRED FROM people_field_categories c,people_fields f WHERE f.CATEGORY_ID=c.ID ORDER BY c.SORT_ORDER,c.TITLE,f.SORT_ORDER,f.TITLE'), array(), array('CATEGORY_ID'));

            explodeCustom($people_categories_RET, $people_custom, 'p');

            unset($_REQUEST['modfunc']);
            $handle = PDFStart();
            $error = 'Y';
            foreach ($RET as $student) {
                $_SESSION['student_id'] = $student['STUDENT_ID'];



                $sql_student = DBGet(DBQuery('SELECT gender AS GENDER, ethnicity_id AS ETHNICITY, common_name AS COM_NAME, social_security AS SOCIAL_SEC, language_id AS LANG, birthdate AS BDATE  FROM students WHERE STUDENT_ID=\'' . $_SESSION['student_id'] . '\''), array('BDATE' => 'ProperDate'));

                $sql_student = $sql_student[1];

                $bir_dt = $sql_student['BDATE'];
                unset($_openSIS['DrawHeader']);

                if (!isset($st_dt) && !isset($end_dt)) {
                    $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\'' . $_SESSION['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'AND  SCHOOL_ID=' . UserSchool() : '') . ' ORDER BY GOAL_TITLE';
//        $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\''.$_SESSION['student_id'].'\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID='.UserSchool().' ORDER BY GOAL_TITLE';
                }
                if (isset($st_dt) && !isset($end_dt)) {
//	$sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\''.$_SESSION['student_id'].'\' AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND START_DATE>=\''.$st_dt.'\' ORDER BY GOAL_TITLE';
                    $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\'' . $_SESSION['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'AND  SCHOOL_ID=' . UserSchool() : '') . ' AND START_DATE>=\'' . $st_dt . '\' ORDER BY GOAL_TITLE';
                }
                if (!isset($st_dt) && isset($end_dt)) {
                    $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\'' . $_SESSION['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'AND  SCHOOL_ID=' . UserSchool() : '') . ' AND START_DATE<=\'' . $end_dt . '\' ORDER BY GOAL_TITLE';
                }
                if (isset($st_dt) && isset($end_dt)) {
                    $sql_goal = 'SELECT GOAL_ID,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE STUDENT_ID=\'' . $_SESSION['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' ' . ($_REQUEST['_search_all_schools'] != 'Y' ? 'AND  SCHOOL_ID=' . UserSchool() : '') . ' AND START_DATE>=\'' . $st_dt . '\' AND START_DATE<=\'' . $end_dt . '\' ORDER BY GOAL_TITLE';
                }

                $res_goal = DBGet(DBQuery($sql_goal), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));



                //----------------------------------------------

                if (count($res_goal) != 0) {
                    $error = 'N';
                    echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                    echo "<tr><td width=105>" . DrawLogoParam($student['SCHOOL_ID']) . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool($student['SCHOOL_ID']) . "</font></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />"._poweredByOpenSis."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
                    echo "<table width=100% cellspacing=0 style=\"border-collapse:collapse\">";


                    echo "<tr><td width=15%>"._studentName.":</td>";
                    echo "<td>" . $student['FULL_NAME'] . "</td></tr>";

                    echo "<tr><td>"._grade.":</td>";
                    echo "<td>" . $student['GRADE_ID'] . " </td></tr>";
                    echo "<tr><td>"._gender.":</td>";
                    echo "<td>" . $sql_student['GENDER'] . "</td></tr>";
                    echo "<tr><td>"._ethnicity.":</td>";
                    $sql_ethinicity= DBGet(DBQuery("SELECT ethnicity_name FROM ethnicity WHERE ethnicity_id='".$sql_student['ETHNICITY']."'"));
                    echo "<td>" . $sql_ethinicity[1]['ETHNICITY_NAME'] . "</td></tr>";
                    if ($sql_student['COM_NAME'] != '') {
                        echo "<tr><td>"._commonName.":</td>";
                        echo "<td>" . $sql_student['COM_NAME'] . "</td></tr>";
                    }
                    if ($sql_student['SOCIAL_SEC'] != '') {
                        echo "<tr><td>"._socialSecurity.":</td>";
                        echo "<td>" . $sql_student['SOCIAL_SEC'] . "</td></tr>";
                    }
                    echo "<tr><td>"._dateOfBirth.":</td>";
                    echo "<td>" . $bir_dt . "</td></tr>";
                    if ($sql_student['LANG'] != '') {
                        echo "<tr><td>"._languageSpoken.":</td>";
                        $sql_language= DBGet(DBQuery("SELECT language_name FROM language WHERE language_id='".$sql_student['LANG']."'"));
                        echo "<td>" . $sql_language[1]['LANGUAGE_NAME'] . "</td></tr>";
                        echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                    }

                    echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
                    echo '<tr><td><b><u>'._goalDetails.'</u></b></td><td>&nbsp;</td></tr>';
                    echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                    foreach ($res_goal as $row_goal) {
                        echo '<tr><td><b>'._goalTitle.': </b></td><td>' . $row_goal['GOAL_TITLE'] . '</td></tr>';
                        echo '<tr><td><b>'._beginDate.': </b></td><td>' . $row_goal['START_DATE'] . '</td></tr>';
                        echo '<tr><td><b>'._endDate.': </b></td><td>' . $row_goal['END_DATE'] . '</td></tr>';
                        echo '<tr><td valign=top><b>'._goalDescription.': </b></td><td>' . $row_goal['GOAL_DESCRIPTION'] . '</td></tr>';
                        echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";

                        if ($progress == 'Y') {
                            $goal_id = $row_goal['GOAL_ID'];
                            $res_pro = DBGet(DBQuery("SELECT START_DATE,PROGRESS_NAME ,PROFICIENCY,PROGRESS_DESCRIPTION,(SELECT TITLE FROM course_periods cp WHERE cp.COURSE_PERIOD_ID=student_goal_progress.COURSE_PERIOD_ID) AS CP_TITLE FROM student_goal_progress WHERE STUDENT_ID='" . $_SESSION['student_id'] . "' AND GOAL_ID='" . $goal_id . "' ORDER BY PROGRESS_NAME"), array('START_DATE' => 'ProperDate'));
                            echo '<tr><td><b><u>'._progressDetails.'</u></b></td><td>&nbsp;</td></tr>';
                            echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                            foreach ($res_pro as $row_pro) {
                                echo '<tr><td><b>'._dateOfEntry.': </b></td><td>' . $row_pro['START_DATE'] . '</td></tr>';
                                # ----------------------------- CP ------------------------------------------------- #	
                                echo '<tr><td><b>'._coursePeriod.': </b></td><td>' . $row_pro['CP_TITLE'] . '</td></tr>';
                                # ----------------------------- CP ------------------------------------------------- #		
                                echo '<tr><td><b>'._progressPeriodName.': </b></td><td>' . $row_pro['PROGRESS_NAME'] . '</td></tr>';
                                echo '<tr><td><b>'._proficiency.': </b></td><td>' . $row_pro['PROFICIENCY'] . '</td></tr>';
                                echo '<tr><td><b>'._progressAssessment.': </b></td><td>' . $row_pro['PROGRESS_DESCRIPTION'] . '</td></tr>';
                                echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                            }
                        }

                        echo "<tr><td colspan=2 style=\"height:18px; border-top:1px solid #333;\"></td></tr>";
                    }


                    echo '</td><td></td><td></td></tr></table></TABLE><div style="page-break-before: always;">&nbsp;</div>';
                    foreach ($categories_RET as $id => $category) {
                        if ($id != '1' && $id != '3' && $id != '2' && $id != '4' && $_REQUEST['category'][$id]) {
                            $_REQUEST['category_id'] = $id;

                            $separator = '';
                            if (!$category[1]['INCLUDE'])
                                include('modules/students/includes/OtherInfoInc.php');
                            elseif (!strpos($category[1]['INCLUDE'], '/'))
                                include('modules/students/includes/' . $category[1]['INCLUDE'] . '.php');
                            else {
                                include('modules/' . $category[1]['INCLUDE'] . '.php');
                                $separator = '<HR>';
                            }
                        }
                    }
                }
            }
            PDFStop($handle);
        } else
            BackPrompt(_noStudentsWereFound.'.');
    } else
        BackPrompt(_youMustChooseAtLeastOneStudent.'.');

    if ($error == 'Y')
        BackPrompt(_noGoalsAndProgressWereFound);
    // unset($_SESSION['student_id']);
    $_REQUEST['modfunc'] = true;
}

if (!$_REQUEST['modfunc']) {
    DrawBC(""._students." > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools]&_openSIS_PDF=true method=POST target=_blank>";
        echo '<div class="panel panel-default">';
        if ($_REQUEST['_search_all_schools'] == 'Y')
            echo '<input type="hidden" name="_search_all_schools" value="Y" />';
        echo '<div class="panel-body form-inline">';
        echo '<div class="form-group">';
        echo '<label>'._pleaseSelectTheDateRange.' :</label>';
        echo '<div class="input-group">';
        $date = '';
        echo '<span class="input-group-addon">'._from.' : </span>';
        DrawHeader(DateInputAY($start_date, 'start', 1));
        echo '<span class="input-group-addon">'._to.' :</span>';
        DrawHeader(DateInputAY($end_date, 'end', 2));
        echo '</div>';
        echo '</div>'; //.form-group
        echo '<div class="form-group">';
        echo '<label class="checkbox-inline checkbox-switch switch-success"><input type="checkbox" name="chk_pro" id="chk_pro" value="Y" /><span></span>'._withProgress.'</label>';
        echo '</div>';
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
    }

    $extra['link'] = array('FULL_NAME' =>false);
    $extra['SELECT'] = ',s.STUDENT_ID AS CHECKBOX';
    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    $extra['options']['search'] = false;
    $extra['new'] = true;


    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('course');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('mailing_labels');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('request');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('activity');
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
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('absences');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('class_rank');
    $extra['search'] .= '</div>'; //.well
    Widgets('eligibility');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';


    Search('student_id', $extra);
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\''._printInfoForSelectedStudents.'\'></div>';
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
    echo '</div></td>';
    echo '<div class="col-md-4"><div id="course_modal"></div></div>';
    echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal




    echo '<div id="modal_default_request" class="modal fade">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
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
}

// GetStuList by default translates the grade_id to the grade title which we don't want here.
// One way to avoid this is to provide a translation function for the grade_id so here we
// provide a passthru function just to avoid the translation.
function _grade_id($value) {
    return $value;
}

function _makeChooseCheckbox($value, $title) {
//    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    global $THIS_RET;
    return "<input name=st_arr[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

function explodeCustom(&$categories_RET, &$custom, $prefix) {
    foreach ($categories_RET as $id => $category)
        foreach ($category as $i => $field) {
            $custom .= ',' . $prefix . '.CUSTOM_' . $field['ID'];
            if ($field['TYPE'] == 'select' || $field['TYPE'] == 'codeds') {
                $select_options = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
                $select_options = explode("\r", $select_options);
                $options = array();
                foreach ($select_options as $option) {
                    if ($field['TYPE'] == 'codeds') {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            $options[$option[0]] = $option[1];
                    } else
                        $options[$option] = $option;
                }
                $categories_RET[$id][$i]['SELECT_OPTIONS'] = $options;
            }
        }
}

function printCustom(&$categories, &$values) {
    echo "<table width=100%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-weight:bold;\">" . $categories[1]['CATEGORY_TITLE'] . "</td></tr>";
    foreach ($categories as $field) {
        echo '<TR>';
        echo '<TD>' . ($field['REQUIRED'] && $values['CUSTOM_' . $field['ID']] == '' ? '<FONT color=red>' : '') . $field['TITLE'] . ($field['REQUIRED'] && $values['CUSTOM_' . $field['ID']] == '' ? '</FONT>' : '') . '</TD>';
        if ($field['TYPE'] == 'select')
            echo '<TD>' . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? '' : '<FONT color=red>') . $values['CUSTOM_' . $field['ID']] . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? '' : '</FONT>') . '</TD>';
        elseif ($field['TYPE'] == 'codeds')
            echo '<TD>' . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? $field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] : '<FONT color=red>' . $values['CUSTOM_' . $field['ID']] . '</FONT>') . '</TD>';
        else
            echo '<TD>' . $values['CUSTOM_' . $field['ID']] . '</TD>';
        echo '</TR>';
    }
    echo '</table>';
}

function con_date($date) {
    $mother_date = $date;
    $year = substr($mother_date, 7);
    $temp_month = substr($mother_date, 3, 3);

    if ($temp_month == 'JAN')
        $month = '01';
    elseif ($temp_month == 'FEB')
        $month = '02';
    elseif ($temp_month == 'MAR')
        $month = '03';
    elseif ($temp_month == 'APR')
        $month = '04';
    elseif ($temp_month == 'MAY')
        $month = '05';
    elseif ($temp_month == 'JUN')
        $month = '06';
    elseif ($temp_month == 'JUL')
        $month = '07';
    elseif ($temp_month == 'AUG')
        $month = '08';
    elseif ($temp_month == 'SEP')
        $month = '09';
    elseif ($temp_month == 'OCT')
        $month = '10';
    elseif ($temp_month == 'NOV')
        $month = '11';
    elseif ($temp_month == 'DEC')
        $month = '12';

    $day = substr($mother_date, 0, 2);

    $select_date = $year . '-' . $month . '-' . $day;
    return $select_date;
}

?>
