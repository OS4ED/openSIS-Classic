<?php

#**************************************************************************
#  openSIS is a free student information system for publirc and non-public 
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
if ($_REQUEST['modfunc'] == 'detail' && $_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new') {
    if ($_POST['button'] == 'Save') {

        if ($_REQUEST['TRANSFER']['SCHOOL'] != '' && $_REQUEST['TRANSFER']['Grade_Level'] != '') {
            $drop_code = $_REQUEST['drop_code'];

            $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] = date("Y-m-d", strtotime($_REQUEST['year_TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '-' . $_REQUEST['month_TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '-' . $_REQUEST['day_TRANSFER']['STUDENT_ENROLLMENT_END_DATE']));

            $gread_exists = DBGet(DBQuery('SELECT COUNT(TITLE) AS PRESENT,ID FROM school_gradelevels WHERE SCHOOL_ID=\'' . $_REQUEST['TRANSFER']['SCHOOL'] . '\' AND TITLE=(SELECT TITLE FROM
                            school_gradelevels WHERE ID=(SELECT GRADE_ID FROM student_enrollment WHERE
                            STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\'  ORDER BY ID DESC LIMIT 1))'));  //pinki

            $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] = date("Y-m-d", strtotime($_REQUEST['year_TRANSFER']['STUDENT_ENROLLMENT_START'] . '-' . $_REQUEST['month_TRANSFER']['STUDENT_ENROLLMENT_START'] . '-' . $_REQUEST['day_TRANSFER']['STUDENT_ENROLLMENT_START']));




            if (strtotime($_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START']) >= strtotime($_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'])) {
                $check_asociation = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) as REC_EX FROM student_enrollment WHERE STUDENT_ID=' . $_REQUEST['student_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND START_DATE<=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' AND END_DATE<=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\') ORDER BY ID DESC LIMIT 0,1'));
                if ($check_asociation[1]['REC_EX'] != 0) {
                    DBQuery('UPDATE student_enrollment SET DROP_CODE=\'' . $drop_code . '\',END_DATE=\'' . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_END_DATE'] . '\' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'  AND SYEAR=\'' . UserSyear() . '\'');  //pinki    
                    $syear_RET = DBGet(DBQuery("SELECT MAX(SYEAR) AS SYEAR,TITLE FROM school_years WHERE SCHOOL_ID=" . $_REQUEST['TRANSFER']['SCHOOL']));
                    $syear = $syear_RET[1]['SYEAR'];
                    $enroll_code = DBGet(DBQuery('SELECT id FROM student_enrollment_codes WHERE syear=\'' . $syear . '\' AND type=\'TrnE\''));  //pinki
                    $last_school_RET = DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\'')); //pinki
                    $last_school = $last_school_RET[1]['SCHOOL_ID'];
                    $sch_id = $_REQUEST['TRANSFER']['SCHOOL'];
                    $num_default_cal = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ' AND DEFAULT_CALENDAR=\'Y\' '));
                    if (empty($num_default_cal)) {
                        $qr = DBGet(DBQuery('SELECT CALENDAR_ID FROM school_calendars WHERE SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ' LIMIT 0,1'));

                        $calender_id = $qr[1]['CALENDAR_ID'];
                    }
                    if (count($num_default_cal) == 1) {
                        $calender_id = $num_default_cal[1]['CALENDAR_ID'];
                    } else {
                        $calender_id = 'NULL';
                    }
                    if ($gread_exists[1]['PRESENT'] == 1 && $gread_exists[1]['ID']) {
                        DBQuery("INSERT INTO student_enrollment (SYEAR ,SCHOOL_ID ,STUDENT_ID ,GRADE_ID ,START_DATE ,END_DATE ,ENROLLMENT_CODE ,DROP_CODE ,NEXT_SCHOOL ,CALENDAR_ID ,LAST_SCHOOL) VALUES (" . $syear . "," . $_REQUEST['TRANSFER']['SCHOOL'] . "," . $_REQUEST['student_id'] . "," . $_REQUEST['TRANSFER']['Grade_Level'] . ",'" . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] . "',''," . $enroll_code[1]['ID'] . ",'','" . $_REQUEST['TRANSFER']['SCHOOL'] . "',$calender_id,$last_school)");
                    } else {
                        DBQuery("INSERT INTO student_enrollment (SYEAR ,SCHOOL_ID ,STUDENT_ID ,GRADE_ID ,START_DATE ,END_DATE ,ENROLLMENT_CODE ,DROP_CODE ,NEXT_SCHOOL ,CALENDAR_ID ,LAST_SCHOOL) VALUES (" . $syear . "," . $_REQUEST['TRANSFER']['SCHOOL'] . "," . $_REQUEST['student_id'] . "," . $_REQUEST['TRANSFER']['Grade_Level'] . ",'" . $_REQUEST['TRANSFER']['STUDENT_ENROLLMENT_START'] . "',''," . $enroll_code[1]['ID'] . ",'','" . $_REQUEST['TRANSFER']['SCHOOL'] . "',$calender_id,$last_school)");
                    }
                    $trans_school = $syear_RET[1]['TITLE'];

                    $trans_student_RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID='" . $_REQUEST['student_id'] . "'"));

                    $trans_student = $trans_student_RET[1]['LAST_NAME'] . ' ' . $trans_student_RET[1]['FIRST_NAME'];
                    DBQuery('UPDATE medical_info SET SCHOOL_ID=' . $_REQUEST['TRANSFER']['SCHOOL'] . ', SYEAR=' . $syear . ' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'');
                    unset($_REQUEST['modfunc']);
                    unset($_SESSION['_REQUEST_vars']['student_id']);
                    echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=students/Student.php&modfunc=&search_modfunc=list&next_modname=students/Student.php&stuid=' . $_REQUEST['student_id'] . '"; window.close();</script>';
                } else {
                    unset($_REQUEST['modfunc']);
                    unset($_SESSION['_REQUEST_vars']['student_id']);
                    echo '<SCRIPT language=javascript>alert("Please provide valid date");window.close();</script>';
                }
            } else {
                unset($_REQUEST['modfunc']);
                unset($_SESSION['_REQUEST_vars']['student_id']);
                echo '<SCRIPT language=javascript>alert("Please provide valid date");window.close();</script>';
            }
        } else {

            if ($_REQUEST['TRANSFER']['SCHOOL'] == '' && $_REQUEST['TRANSFER']['Grade_Level'] != '')
                echo '<SCRIPT language=javascript>alert("Please select School");window.close();</script>';
            if ($_REQUEST['TRANSFER']['SCHOOL'] != '' && $_REQUEST['TRANSFER']['Grade_Level'] == '')
                echo '<SCRIPT language=javascript>alert("Please select Grade Level");window.close();</script>';
            if ($_REQUEST['TRANSFER']['SCHOOL'] == '' && $_REQUEST['TRANSFER']['Grade_Level'] == '')
                unset($_REQUEST['modfunc']);
            echo '<SCRIPT language=javascript>alert("Please select School and Grade Level");window.close();</script>';
        }
    }
    else {

        $sql = "SELECT ID,TITLE FROM schools WHERE ID !=" . UserSchool();
        $sql2 = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID !=' . UserSchool() . '  LIMIT 0,1'));
        $sch_id = $sql2[1]['ID'];
        if ($sch_id != '') {
            $QI = DBQuery($sql);
            $schools_RET = DBGet($QI);
            foreach ($schools_RET as $school_array) {
                $options[$school_array['ID']] = $school_array['TITLE'];
            }
            $res = DBGet(DBQuery('SELECT * FROM school_gradelevels WHERE school_id=' . $sch_id . ''));
            $options1 = array();
            foreach ($res as $res1) {
                $options1[$res1['ID']] = $res1['TITLE'];
            }

            $extraM .= 'onchange=grab_GradeLevel(this.value)';
            $exg = 'id="grab_grade"';
            
            echo '<div class="modal-header">';
            echo '<button type="button" class="close" data-dismiss="modal">×</button>';
            echo '<h5 class="modal-title">'._transferredOut.'</h5>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo '<input type="hidden" name="values[student_enrollment]['.$_REQUEST['student_id'].'][DROP_CODE]" value="'.$_REQUEST['drop_code'].'" />';
            echo '<div class="form-group datepicker-group">';
            echo '<label class="control-label">'._currentSchoolDropDate.'</label>';
            //echo DateInput_for_EndInputModal('', 'TRANSFER[STUDENT_ENROLLMENT_END_DATE]', '', $div, true);
            echo custom_datepicker('222', 'TRANSFER[STUDENT_ENROLLMENT_END_DATE]');

            echo '</div>';

            echo '<div class="form-group">';
            echo '<label class="control-label">'._transferringTo.'</label>';
            echo SelectInputModal('', 'TRANSFER[SCHOOL]', '', $options, false, $extraM, 'class=cell_medium');
            echo '</div>';

            echo '<div class="form-group">';
            echo '<label class="control-label">'._gradeLevel.'</label>';
            echo SelectInputModal('', 'TRANSFER[Grade_Level]', '', $options1, false, $exg, 'class=cell_medium');
            echo '</div>';

            echo '<div class="form-group">';
            echo '<label class="control-label">'._newSchoolSEnrollmentDate.'</label>';
            //echo DateInput_for_EndInputModal('', 'TRANSFER[STUDENT_ENROLLMENT_START]', '', $div, true);
            echo custom_datepicker('223', 'TRANSFER[STUDENT_ENROLLMENT_START]');
            echo '</div>';
            echo '</div>'; //.modal-body

            echo '<div class="modal-footer">';
            echo '<INPUT type=submit class="btn btn-primary" name=button value='._save.'>';
            echo '</div>';

            //echo '</FORM>';

            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
            unset($_REQUEST['button']);
            unset($_SESSION['_REQUEST_vars']['button']);
        } else {
            echo '<div align=center class="m-15">There is only one school in the system so the student cannot be transferred to any other school<br /><br>
                   <input type=button class="btn btn-default" value=Close onclick=\'closeThisModal("modal_default_transferred_out");\'></div>
                    </form>';
//            PopTableWindow('footer');


            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
            unset($_REQUEST['button']);
            unset($_SESSION['_REQUEST_vars']['button']);
        }
    }
}

function custom_datepicker($id, $name) {
    $dt.= '<div class="input-group datepicker-group" id="original_date_' . $id . '" value="" style="">';
    $dt.= '<span class="input-group-addon"><i class="icon-calendar22"></i></span>';
    $dt.= '<input id="date_' . $id . '" placeholder="Select Date" value="" class="form-control daterange-single" type="text">';
    $dt.= '</div>';
    $dt.= '<input value="" id="monthSelect_date_' . $id . '" name="month_' . $name . '" type="hidden">';
    $dt.= '<input value="" id="daySelect_date_' . $id . '" name="day_' . $name . '" type="hidden">';
    $dt.= '<input value="" id="yearSelect_date_' . $id . '" name="year_' . $name . '" type="hidden">';
    echo $dt;
}

echo '<script type="text/javascript" src="assets/js/pages/picker_date.js"></script>';
