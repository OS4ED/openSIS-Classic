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
include 'modules/grades/DeletePromptX.fnc.php';
DrawBC("Gradebook > " . ProgramTitle());
Search('student_id');
echo '<style type="text/css">#div_margin { margin-top:-20px; _margin-top:-1px; }</style>';

if (isset($_REQUEST['student_id'])) {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));

    $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students'));
    if ($count_student_RET[1]['NUM'] > 1) {
        echo '<div class="panel panel-default">';
        DrawHeader('Selected Student: ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'], ' (<A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . '><font color=red>Deselect</font></A>) | <A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=Students/Student.php&ajax=true&bottom_back=true&return_session=true target=body>Back to Student List</A>');
        echo '</div>';
    } else if ($count_student_RET[1]['NUM'] == 1) {
        echo '<div class="panel panel-default">';
        DrawHeader('Selected Student: ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'], ' (<A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . '><font color=red>Deselect</font></A>) ');
        echo '</div>';
    }
}
####################
if (UserStudentID()) {
    $student_id = UserStudentID();
    $mp_id = $_REQUEST['mp_id'];
    $tab_id = ($_REQUEST['tab_id'] ? $_REQUEST['tab_id'] : 'grades');

    if ($_REQUEST['modfunc'] == 'update' && $_REQUEST['removemp'] && $mp_id && DeletePromptX('Marking Period')) {

        DBQuery('UPDATE student_gpa_calculated SET  cum_unweighted_factor=NULL WHERE student_id = ' . $student_id . ' and marking_period_id = ' . $mp_id . '');
        unset($mp_id);
    }

    if ($_REQUEST['modfunc'] == 'update' && !$_REQUEST['removemp']) {

        if ($_REQUEST['new_sms']) {

            // ------------------------ Start -------------------------- //

            $res = DBGet(DBQuery('SELECT * FROM student_gpa_calculated WHERE student_id=' . $student_id . ' AND marking_period_id=' . $_REQUEST['new_sms']));
//	    $rows = mysql_num_rows($res);
            $rows = count($res);

            if ($rows == 0) {
                DBQuery('INSERT INTO student_gpa_calculated (student_id, marking_period_id) VALUES (' . $student_id . ', ' . $_REQUEST['new_sms'] . ')');
            } elseif ($rows != 0) {
                echo "<b>This Marking Periods has been updated.</b>";
            }
            // ------------------------- End --------------------------- //
            $mp_id = $_REQUEST['new_sms'];
        }

        if ($_REQUEST['SMS_GRADE_LEVEL'] && $mp_id) {


            $updatestats = 'UPDATE student_gpa_calculated SET grade_level_short = \'' . $_REQUEST['SMS_GRADE_LEVEL'] . '\'
                            WHERE marking_period_id = ' . $mp_id . '     
                            AND student_id = ' . $student_id;
            DBQuery($updatestats);
        }
        foreach ($_REQUEST['values'] as $id => $columns) {
//            print_r($_REQUEST);
            if ($id != 'new') {
                $sql = 'UPDATE student_report_card_grades SET ';
                if ($columns['UNWEIGHTED_GP']) {
                    $gp = $columns['UNWEIGHTED_GP'];
                } else {
                    $gp_RET = DBGet(DBQuery('SELECT IF(ISNULL(UNWEIGHTED_GP),  WEIGHTED_GP,UNWEIGHTED_GP ) AS GP FROM student_report_card_grades WHERE id=\'' . $id . '\''));
                    $gp = $gp_RET[1];
                    $gp = $gp['GP'];
                }

                $go = false;
                if ($columns['WEIGHTED_GP'] == 'Y' && $tab_id == 'grades') {
                    $sql .= 'WEIGHTED_GP' . '=\'' . $gp . '\',UNWEIGHTED_GP=NULL,';
                    $go = true;
                } elseif ($tab_id == 'grades') {
                    $sql .= 'UNWEIGHTED_GP' . '=\'' . $gp . '\',WEIGHTED_GP=NULL,';
                    $go = true;
                }
                foreach ($columns as $column => $value) {
                    if ($column == 'UNWEIGHTED_GP' || $column == 'WEIGHTED_GP')
                        continue;
                    $go = true;
                    $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                }

                $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';

                if ($go)
                    DBQuery($sql);
            }
            else {
                $sql = 'INSERT INTO student_report_card_grades ';
                $fields = 'SCHOOL_ID, SYEAR, STUDENT_ID, MARKING_PERIOD_ID, ';
                $values = UserSchool() . ", " . UserSyear() . ", $student_id, $mp_id, ";

                $go = false;

                if ($columns['WEIGHTED_GP'] == 'Y' && $tab_id == 'grades') {
                    $fields .= 'WEIGHTED_GP,';
                    $values .= $columns['UNWEIGHTED_GP'] . ',';
                } elseif ($tab_id == 'grades') {
                    $fields .= 'UNWEIGHTED_GP,';
                    $values .= $columns['UNWEIGHTED_GP'] . ',';
                }

                foreach ($columns as $column => $value) {
                    if ($column == 'UNWEIGHTED_GP' || $column == 'WEIGHTED_GP')
                        continue;
                    if (trim($value)) {

                        $fields .= $column . ',';
                        $values .= '\'' . str_replace("\'", "''", $value) . '\',';
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                if ($go && $mp_id && $student_id)
                    DBQuery($sql);
            }
        }
        unset($_REQUEST['modfunc']);
    }

    if ($_REQUEST['modfunc'] == 'remove') {

        if (DeletePromptX('Student Grade')) {
            //echo 'DELETE FROM student_report_card_grades WHERE ID=\'' . $_REQUEST['id'] . '\'';
            DBQuery('DELETE FROM student_report_card_grades WHERE ID=\'' . $_REQUEST['id'] . '\'');
        }
    }
    if (!$_REQUEST['modfunc']) {
        $stuRET = DBGet(DBQuery('SELECT LAST_NAME, FIRST_NAME, MIDDLE_NAME, NAME_SUFFIX from students where STUDENT_ID = ' . $student_id . ''));
        $stuRET = $stuRET[1];
        $displayname = $stuRET['LAST_NAME'] . (($stuRET['NAME_SUFFIX']) ? $stuRET['suffix'] . ' ' : '') . ', ' . $stuRET['FIRST_NAME'] . ' ' . $stuRET['MIDDLE_NAME'];



        $gquery = 'SELECT mp.syear, mp.marking_period_id as mp_id, mp.title as mp_name, mp.post_end_date as posted, sgc.grade_level_short as grade_level, 
       sgc.weighted_gpa, sgc.unweighted_gpa
       FROM marking_periods mp, student_gpa_calculated sgc, schools s
       WHERE sgc.marking_period_id = mp.marking_period_id and
             s.id = mp.school_id and sgc.student_id = ' . $student_id . ' 
       AND mp.school_id = \'' . UserSchool() . '\' order by mp.post_end_date';

        $GRET = DBGet(DBQuery($gquery));

        $last_posted = null;
        $gmp = array(); //grade marking_periods
        $grecs = array();  //grade records
        if ($GRET) {
            foreach ($GRET as $rec) {
                if ($mp_id == null || $mp_id == $rec['MP_ID']) {
                    $mp_id = $rec['MP_ID'];
                    $gmp[$rec['MP_ID']] = array('schoolyear' => formatSyear($rec['SYEAR']),
                        'mp_name' => $rec['MP_NAME'],
                        'grade_level' => $rec['GRADE_LEVEL'],
                        'weighted_cum' => $rec['WEIGHTED_CUM'],
                        'unweighted_cum' => $rec['UNWEIGHTED_CUM'],
                        'weighted_gpa' => $rec['WEIGHTED_GPA'],
                        'unweighted_gpa' => $rec['UNWEIGHTED_GPA'],
                        'gpa' => $rec['GPA']);
                }
                if ($mp_id != $rec['MP_ID']) {
                    $gmp[$rec['MP_ID']] = array('schoolyear' => formatSyear($rec['SYEAR']),
                        'mp_name' => $rec['MP_NAME'],
                        'grade_level' => $rec['GRADE_LEVEL'],
                        'weighted_cum' => $rec['WEIGHTED_CUM'],
                        'unweighted_cum' => $rec['UNWEIGHTED_CUM'],
                        'weighted_gpa' => $rec['WEIGHTED_GPA'],
                        'unweighted_gpa' => $rec['UNWEIGHTED_GPA'],
                        'gpa' => $rec['GPA']);
                }
            }
        } else {
            $mp_id = "0";
        }
        $mpselect = "<FORM action=Modules.php?modname=$_REQUEST[modname]&tab_id=" . $_REQUEST['tab_id'] . " method=POST>";
        $mpselect .= "<SELECT class=\"form-control\" name=mp_id onchange='this.form.submit();'>";
        foreach ($gmp as $id => $mparray) {
            $mpselect .= "<OPTION value=" . $id . (($id == $mp_id) ? ' SELECTED' : '') . ">" . $mparray['schoolyear'] . ' ' . $mparray['mp_name'] . ', Grade ' . $mparray['grade_level'] . "</OPTION>";
        }
        $mpselect .= "<OPTION value=0 " . (($mp_id == '0') ? ' SELECTED' : '') . ">Add another marking period</OPTION>";
        $mpselect .= '</SELECT>';

        $mpselect .= '</FORM>';



        echo '<div class="panel panel-default">';
        DrawHeader('Edit Report Card Grades', '<div class="form-group">' . $mpselect . '</div>');

        echo "<FORM class=\"form-horizontal m-b-0\" action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=update&tab_id=" . strip_tags(trim($_REQUEST[tab_id])) . "&mp_id=" . $mp_id . " method=POST>";

        echo '<div class="panel-body alpha-grey">';
        echo '<div class="media">';
        echo '<div class="media-left"><div class="profile-thumb"><img src="assets/images/placeholder.jpg" class="img-circle" alt=""></div></div>';
        echo '<div class="media-body">';
        echo '<h1 class="no-margin-top">' . $displayname . '</h1>';
        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<div class="row">';
        echo '<label class="control-label col-md-6 text-right">Weighted GPA :</label><label class="control-label col-md-6 text-primary">' . sprintf('%0.2f', $gmp[$mp_id]['weighted_gpa']) . '</label>';
        echo '</div>'; //.row
        echo '</div>'; //.col-md-4
        echo '<div class="col-md-4">';
        echo '<div class="row">';
        echo '<label class="control-label col-md-6 text-right">Unweighted GPA :</label><label class="control-label col-md-6 text-primary">' . sprintf('%0.2f', $gmp[$mp_id]['unweighted_gpa']) . '</label>';
        echo '</div>'; //.row
        echo '</div>'; //.col-md-4

        $sms_grade_level = TextInput($gmp[$mp_id]['grade_level'], "SMS_GRADE_LEVEL", "", 'size=15 maxlength=3 class=form-control');

        if ($mp_id == "0") {
            $syear = UserSyear();
            $sql = 'SELECT MARKING_PERIOD_ID, SYEAR, TITLE, POST_END_DATE FROM marking_periods WHERE SCHOOL_ID = \'' . UserSchool() .
                    '\' ORDER BY POST_END_DATE';
            $MPRET = DBGet(DBQuery($sql));
            if ($MPRET) {
                $mpoptions = array();
                foreach ($MPRET as $id => $mp) {
                    $mpoptions[$mp['MARKING_PERIOD_ID']] = formatSyear($mp['SYEAR']) . ' ' . $mp['TITLE'];
                }

                //PopTable_grade_header('header');
                echo '<div class="col-md-4">';
                echo '<div class="row">';
                echo '<label class="control-label col-md-5 text-right">Grade Level</label>';
                echo '<div class="col-md-7">';
                echo $sms_grade_level;
                echo '</div>'; //.col-md-7
                echo '</div>'; //form-group
                echo '</div>'; //.col-md-4
                echo '</div>'; //.row

                echo '<div class="row">';
                echo '<div class="col-md-6">';
                echo '<div class="form-group">';
                echo '<label class="control-label col-md-4 text-right">New Marking Period :</label>';
                echo '<div class="col-md-8">';
                echo SelectInput(null, 'new_sms', '', $mpoptions, false, null);
                echo '</div>'; //.col-md-8
                echo '</div>'; //form-group
                echo '</div>'; //.col-md-6
                //PopTable('footer');
            }
        } else {
            echo '<div class="col-md-4">';
            echo '<div class="form-group">';
            echo '<label class="control-label col-md-5 text-right">Grade Level</label>';
            echo '<div class="col-md-7">';
            echo $sms_grade_level;
            echo '</div>'; //.col-md-7
            echo '</div>'; //form-group
            echo '</div>'; //.col-md-4
        }

        echo '</div>'; //.row
        echo '</div>'; //.media-body
        echo '</div>'; //.media

        echo '</div>'; //.panel-body.alpha-grey
        echo '<hr class="no-margin" />';


        if ($mp_id != "0") {

            $tabs = array();
            $tabs[] = array('title' => 'grades', 'link' => "Modules.php?modname=$_REQUEST[modname]&tab_id=grades&mp_id=$mp_id");
            $tabs[] = array('title' => 'Credits', 'link' => "Modules.php?modname=$_REQUEST[modname]&tab_id=credits&mp_id=$mp_id");
            echo WrapTabs($tabs, "Modules.php?modname=$_REQUEST[modname]&tab_id=$tab_id&mp_id=$mp_id");

            $sql = 'SELECT ID,COURSE_TITLE,GRADE_PERCENT,GRADE_LETTER,
                    IF(ISNULL(UNWEIGHTED_GP),  WEIGHTED_GP,UNWEIGHTED_GP ) AS GP,WEIGHTED_GP as WEIGHTED_GP,
                    GP_SCALE,CREDIT_ATTEMPTED,CREDIT_EARNED,CREDIT_CATEGORY
                       FROM student_report_card_grades WHERE STUDENT_ID = ' . $student_id . ' AND MARKING_PERIOD_ID = ' . $mp_id . ' ORDER BY ID';

            //build forms based on tab selected
            if ($_REQUEST['tab_id'] == 'grades' || $_REQUEST['tab_id'] == '') {
                $functions = array('COURSE_TITLE' => 'makeTextInput',
                    'GRADE_PERCENT' => 'makeTextInput',
                    'GRADE_LETTER' => 'makeTextInput',
                    'GP' => 'makeTextInput',
                    'WEIGHTED_GP' => 'makeCheckboxInput',
                    'GP_SCALE' => 'makeTextInput',
                );
                $LO_columns = array('COURSE_TITLE' => 'Course Name',
                    'GRADE_PERCENT' => 'Percentage',
                    'GRADE_LETTER' => 'Letter Grade',
                    'GP' => 'GP Value',
                    'WEIGHTED_GP' => 'Weighted GP',
                    'GP_SCALE' => 'Grade Scale',
                );
                $link['add']['html'] = array('COURSE_TITLE' => makeTextInput('', 'COURSE_TITLE'),
                    'GRADE_PERCENT' => makeTextInput('', 'GRADE_PERCENT'),
                    'GRADE_LETTER' => makeTextInput('', 'GRADE_LETTER'),
                    'GP' => makeTextInput('', 'GP'),
                    'WEIGHTED_GP' => makeCheckboxInput('', 'WEIGHTED_GP'),
                    'GP_SCALE' => makeTextInput('', 'GP_SCALE'),
                );
            } else {
                $functions = array('COURSE_TITLE' => 'makeTextInput',
                    'CREDIT_ATTEMPTED' => 'makeTextInput',
                    'CREDIT_EARNED' => 'makeTextInput',
                    'CREDIT_CATEGORY' => 'makeTextInput'
                );
                $LO_columns = array('COURSE_TITLE' => 'Course Name',
                    'CREDIT_ATTEMPTED' => 'Credit Attempted',
                    'CREDIT_EARNED' => 'Credit Earned',
                    'CREDIT_CATEGORY' => 'Credit Category'
                );
                $link['add']['html'] = array('COURSE_TITLE' => makeTextInput('', 'COURSE_TITLE'),
                    'CREDIT_ATTEMPTED' => makeTextInput('', 'CREDIT_ATTEMPTED'),
                    'CREDIT_EARNED' => makeTextInput('', 'CREDIT_EARNED'),
                    'CREDIT_CATEGORY' => makeTextInput('', 'CREDIT_CATEGORY')
                );
            }
            $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&mp_id=$mp_id";
            $link['remove']['variables'] = array('id' => 'ID');
            $link['add']['html']['remove'] = button('add');
            $LO_ret = DBGet(DBQuery($sql), $functions);

            //PopTable_wo_header('header');

            ListOutput($LO_ret, $LO_columns, '', '', $link, array(), array('count' => true, 'download' => true, 'search' => true));
            //PopTable('footer');
        }


        echo '<div class="panel-footer text-right p-r-20">';
        if (!$LO_ret) {
            echo SubmitButton('Remove Marking Period', 'removemp', 'class="btn btn-primary"');
            echo '&nbsp;';
        }
        echo SubmitButton('Save', '', 'class="btn btn-primary"');

        echo '</div>';

        echo '</div>'; //.panel
        echo '</FORM>';
    }
}

function makeTextInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';
    if ($name == 'COURSE_TITLE')
        $extra = 'size=25 maxlength=25 class=form-control';
    elseif ($name == 'GRADE_PERCENT')
        $extra = 'size=6 maxlength=6 class=form-control';
    elseif ($name == 'GRADE_LETTER')
        $extra = 'size=5 maxlength=5 class=form-control';
    elseif ($name == 'GP') {
        $name = 'UNWEIGHTED_GP';
        $extra = 'size=5 maxlength=5 class=form-control';
    } else
        $extra = 'size=10 maxlength=10 class=form-control';

    return TextInput($value, "values[$id][$name]", '', $extra);
}

function formatSyear($value) {
    return substr($value, 2) . '-' . substr($value + 1, 2);
}

function makeCheckboxInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($THIS_RET['WEIGHTED_GP'] != NULL)
        $yes = 'Yes';
    else
        $no = 'No';

    return '<input type=hidden name=values[' . $id . '][' . $name . '] value="' . $value . '" />' . CheckboxInput($value, 'values[' . $id . '][' . $name . ']', '', '', ($id == 'new' ? true : false), $yes, $no, false);
}

?>
