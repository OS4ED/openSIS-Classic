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
unset($_SESSION['student_id']);
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'save') {
    $start_date = $_REQUEST['day_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['year_start'];

    if (!VerifyDate($start_date)) {
        //        $start_date=date('Y-m-d',strtotime($start_date));
        BackPrompt(_theDateYouEnteredIsNotValid);
    }
    if ($_REQUEST['student']) {

        // $selecteds  =   explode(",", $_REQUEST['student']);

        $req_stu    =   array();

        // foreach($selecteds as $this_stu)
        foreach ($_REQUEST['student'] as $this_stu) {
            $req_stu[$this_stu] .= 'Y';
        }


        $count = 0;
        $start_date = date('Y-m-d', strtotime($start_date));
        $id_array = array();

        // foreach ($_REQUEST['student'] as $student_id => $yes) {
        foreach ($req_stu as $student_id => $yes) {
            $next_grade = DBGet(DBQuery('SELECT NEXT_GRADE_ID FROM school_gradelevels WHERE ID=\'' . $_REQUEST['grade_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            if ($next_grade[1]['NEXT_GRADE_ID'] != '')
                $rolling_ret = 1;
            else
                $rolling_ret = 0;
            $qr = DBGet(DBQuery('SELECT END_DATE FROM student_enrollment WHERE ID=(SELECT max(ID) FROM student_enrollment where STUDENT_ID=' . $student_id . ')'));
            $end_date = $qr[1]['END_DATE'];
            //echo $start_date; exit;
            if (strtotime($start_date) > strtotime($end_date)) {
                DBQuery('INSERT INTO student_enrollment (SYEAR,SCHOOL_ID,STUDENT_ID,GRADE_ID,START_DATE,ENROLLMENT_CODE,NEXT_SCHOOL,CALENDAR_ID) VALUES (\'' . UserSyear() . '\',\'' . UserSchool() . '\',' . $student_id . ',\'' . $_REQUEST['grade_id'] . '\',\'' . $start_date . '\',\'' . $_REQUEST['en_code'] . '\',\'' . $rolling_ret . '\',\'' . $_REQUEST['cal_id'] . '\')');

                $enroll_msg = "" . _selectedStudentsAreSuccessfullyReEnrolled . ".";
                $count = 1;
            } else {
                $name = DBGet(DBQuery('SELECT * FROM students WHERE STUDENT_ID=' . $student_id . ''));
                $title_nm = $name[1]['FIRST_NAME'] . " " . $name[1]['LAST_NAME'];
                $id_array[] = $title_nm;
            }
            if ($enroll_msg != '' && $enroll_msg == '' . _selectedStudentsAreSuccessfullyReEnrolled . '' && count($id_array) > 0) {
                $enroll_msg .= "&nbsp but &nbsp;" . implode(",", $id_array) . " &nbsp;" . _cannotBeReenrolledBecauseReenrollDateAndDropDateAreSameOrReenrollmentDateIsBeforeEndDate . " ";
            }
            if (count($id_array) > 0) {
                if (count($id_array) > 1)
                    $s = "Students";
                else {
                    $s = "Student";
                }
                $enroll_msg = $s . " " . implode(",", $id_array) . " &nbsp;" . _cannotBeReenrolledBecauseReenrollDateAndDropDateAreSameOrReenrollmentDateIsBeforeEndDate . " ";
            }
        }
    } else {
        $err = "<div class=\"alert bg-danger alert-styled-left\">" . _noStudentsAreSelected . "</div>";
    }
    unset($_REQUEST['modfunc']);
}

DrawBC("" . _students . " > " . ProgramTitle());
if ($_REQUEST['search_modfunc'] == 'list') {
    echo "<FORM name=sav class=\"form-horizontal\" id=sav action=Modules.php?modname=$_REQUEST[modname]&modfunc=save method=POST>";
    PopTable_wo_header('header');
    $calendar = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=\'' . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY DEFAULT_CALENDAR DESC LIMIT 0,1"));

    echo '<INPUT TYPE=hidden name=cal_id value=' . $calendar[1]["CALENDAR_ID"] . '>';

    echo '<input id="selected_students" name="student" type="hidden" val="">';

    echo '<div class="row">';
    echo '<div class="col-lg-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _startDate . ' <span class="text-danger">*</span></label><div class="col-lg-8">' . DateInputAY(DBDate('mysql'), 'start', 1) . '</div></div>';
    echo '</div><div class="col-lg-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _grade . ' <span class="text-danger">*</span></label><div class="col-lg-8">';

    $sel_grade = DBGet(DBQuery('SELECT TITLE,ID FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\''));
    echo '<SELECT class="form-control" name=grade_id id=grade_id><OPTION value="">' . _selectGrade . '</OPTION>';
    foreach ($sel_grade as $g_id)
        echo "<OPTION value=$g_id[ID]>" . $g_id['TITLE'] . '</OPTION>';
    echo '</SELECT></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-lg-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _enrollmentCode . ' <span class="text-danger">*</span></label><div class="col-lg-8">';
    $enroll_code = DBGet(DBQuery('SELECT TITLE,ID FROM student_enrollment_codes WHERE SYEAR=\'' . UserSyear() . '\' AND TYPE IN ( \'Add\' , \'TrnE\' ,\'  Roll \')'));
    echo '<SELECT class=form-control name=en_code id=en_code><OPTION value="">' . _selectEnrollCode . '</OPTION>';
    foreach ($enroll_code as $enr_code)
        echo "<OPTION value=$enr_code[ID]>" . $enr_code['TITLE'] . '</OPTION>';
    echo '</SELECT></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
    PopTable('footer');
}

if ($enroll_msg) {
    // DrawHeader('<IMG SRC=assets/check.gif> ' . $enroll_msg);
    echo '<div class="alert alert-success alert-styled-left alert-bordered">' . $enroll_msg . '</div>';
}
if ($err) {
    // DrawHeader('<IMG SRC=assets/warning_button.gif> ' . $err);
    echo '<div class="alert alert-danger alert-styled-left alert-bordered">' . $err . '</div>';
}

if (!$_REQUEST['modfunc']) {
    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ',Concat(NULL) AS CHECKBOX ';
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT id=re_enroll_toggle type=checkbox value=Y name=controller onchange="checkAllReEn();"><A>');
    $extra['new'] = true;
    $extra['GROUP'] = "STUDENT_ID";
    $extra['WHERE'] = ' AND  ssm.STUDENT_ID NOT IN (SELECT STUDENT_ID FROM student_enrollment WHERE SYEAR =\'' . UserSyear() . '\' AND END_DATE IS NULL)';

    Search('student_id', $extra);

    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0) {
            echo "<div class=\"text-center\">" . SubmitButton(_reEnrollSelectedStudents, '', 'class="btn btn-primary" onclick=\'return reenroll();\'') . "</div>";
        }
    }
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "</FORM>";
    }
}

function _makeChooseCheckbox($value, $title)
{
    global $THIS_RET;

    return "<input class=re_enroll name=student[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";
}


echo "<script language=\"JavaScript\" type=\"text/javascript\">
    function checkAllReEn()
    {
        var arrMarkMail =   document.getElementsByClassName('re_enroll');

        for (var i = 0; i < arrMarkMail.length; i++)
        {
            if(window.$('#re_enroll_toggle').is(':checked'))
            {
                arrMarkMail[i].checked = true;
            }
            else
            {
                arrMarkMail[i].checked = false;
            }
        }
    }
</script>";
